<?php

namespace see7788\php6140\demo;

use Exception;
use Redis;
use see7788\php6140\tool\UsersConnection;
use Workerman\Timer;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use see7788\php6140\tool\Base;
use Workerman\Worker;

class Index extends Base
{
    public Redis $redis;
    public array $httpFiles;
    /**
     * @var UsersConnection
     */
    public $users;
    public Worker $httpServe;
    public Worker $websocketServe;

    function __construct()
    {
        parent::__construct();
        $this->worker->count = 1;
        $this->worker->onWorkerStart = function (Worker $worker) {
            $redisPort = 6379;
            $redisSelectId = 0;
            $this->redis = $this->redisInit($redisPort, $redisSelectId);
            $this->users = $this->usersConnection();
            $this->httpServeInit();
            $this->webSocketInit();
        };
    }

    function httpServeInit()
    {
        $this->httpFiles = $this->readDir()->add(__DIR__, 'index')->files;
        $c = $this->httpServe = new Worker("http://0.0.0.0:6001");
        $c->onWorkerStart = function (Worker $worker) {
            echo "Worker starting...\n";
            //$client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
        };
        $c->onError = array($this, 'onError');
        $c->onConnect = function (TcpConnection $connection) {

        };
        $c->onClose = function (TcpConnection $connection) {

        };
        $c->onMessage = function (TcpConnection $connection, Request $request) {
            $type = $this->httpReq($connection, $request)->nowRouter('sse');
            var_dump($type);
            if (method_exists($this,$type)&&in_array($type, array('sse', 'sseget', 'set'))) {
                call_user_func([$this,$type],[$connection, $request]);
                $this->$type($connection, $request);
            } else if (strpos($type, '.') && !empty($this->httpFiles[$type])) {
                $this->httpRes($connection)->file($this->httpFiles[$type]);
            } else {
                $this->router404($connection, $request);
            }
        };
        /*  try {
              $c->listen();
          } catch (Exception $e) {
              var_dump(__FUNCTION__.'error:'.$e);
          }*/
    }

    function webSocketInit()
    {
        $c = $this->websocketServe = new Worker("websocket://0.0.0.0:6002");
        $c->onError = array($this, 'onError');
        $c->onWorkerStart = function (Worker $worker) {
        };
        $c->onConnect = function (TcpConnection $connection) {

        };
        $c->onClose = function (TcpConnection $connection) {

        };
        $c->onMessage = function (...$c) {
        };
    }

    function user($openId)
    {
    }

    function onConnect(TcpConnection $connection)
    {
        $cb = function ($channel, $message) use ($connection) {
            $connection->send($message);
        };
        //$this->redis->subscribe($this->sbIds, 'cb');
        echo "new connection from ip " . $connection->getRemoteIp() . "\n";
    }

    function onClose(TcpConnection $connection)
    {
        $connection->close();
        echo "connection closed\n";
    }

    function onError(TcpConnection $connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    function router404(TcpConnection $connection, Request $request)
    {
        $this->httpRes($connection)
            ->chunk($request->path())
            ->send($request->queryString() ?: '没有参数')
            ->close();
    }

    function sseget(TcpConnection $connection, Request $request)
    {
        $initDb = '初始数据';
        $res = $this->httpRes($connection)->sse('init', $initDb);
        $req = $this->httpReq($connection, $request);
        if ($req->sseBool()) {
            $timer_id = Timer::add(2, function () use ($req, $res, $connection, &$timer_id) {
                //var_dump(date("Y-m-d H:i:s"));
                $res->send('on', '持续推送新数据' . date("Y-m-d H:i:s"));
            });
        } else {
            $connection->close();
        }
    }

    function sse(TcpConnection $connection, Request $request)
    {
        $port = $connection->getLocalPort();
        $connection->send(
            "
                <script crossorigin='anonymous' src='https://lib.baomitu.com/vue/2.6.14/vue.js'></script>
                <div id='app'>{{message}}</div>
                <script>
                       /* if(!window.localStorage){
                             alert('浏览器支持localstorage');
                        }else{
                            let storage=window.localStorage;
                            //写入a字段
                            storage['a']=1;
                            //写入b字段
                            storage.b=1;
                            //写入c字段
                            storage.setItem('c',3);
                            console.log(storage.a,typeof storage['a']);
                            console.log(storage.b,typeof storage['b']);
                            console.log(storage.c,typeof storage['c']);
                        }*/
                       let h=window.location.href;
                       let queryint=h.indexOf('?');
                       let url =queryint>-1? h.substring(0,h.indexOf('?')):h;
                      // console.log(h,h.indexOf('?'),url)
                       let source = new EventSource('$port/sseget');
                       new Vue({
                                el: '#app',
                                data: {
                                    message: '0',
                                },
                                methods: {
                                    init(e){
                                        console.log(e)
                                        this.message = e.data
                                    }
                                },
                                created: function () {
                                    source.onmessage=function(e) {
                                        console.log(e)
                                    }
                                   source.addEventListener('init', this.init); 
                                   source.addEventListener('on', this.init);
                                }
                             })
                </script>
                "
        );
    }

    function set(TcpConnection $connection, Request $request)
    {
        //验证合法连接，$connection->close();
        $sbId = $request->get('sbId', 0);
        $in = $request->get('in', 0);
        $out = $request->get('out', 0);
        $db = ['in' => $in, 'sbId' => $sbId, 'out' => $out];
        $this->redis->hMSet($sbId, $db);
        $en = json_encode($db);
        $this->redis->publish($sbId, json_encode($en));
        $connection->send(json_encode($this->redis->hGetAll($sbId)));
    }

}
