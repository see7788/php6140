<?php

namespace jinkong0;

use Workerman\Protocols\Http\Chunk;
use Workerman\Protocols\Http\Response;
use Workerman\Timer;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Connection\AsyncTcpConnection;

class Index extends  Router
{
    public Worker $worker;
    private string $httpAddress;

    function __construct(string $ip, int $httpPort, int $redisPort, int $redisSelectId)
    {
        parent::__construct($ip,$redisPort,$redisSelectId);
        $this->httpAddress = "http://$ip:$httpPort";
        $this->worker = new Worker($this->httpAddress);
        $this->routers();
    }

    private function routers()
    {
        $this->worker->onMessage = function (TcpConnection $connection, Request $request) {
            $fun = substr($request->path(), 1);
            $fun = strtolower($fun);
            //是否方法
            if ($fun === '') {
                $this->index($connection, $request);
            } else if (in_array($fun, ['test', 'test2'])) {
                $this->is400($connection, $request);
            } else if (is_callable(array($this, $fun))) {
                $this->$fun($connection, $request);
            } else {
                $this->is404($connection, $request);
            }
        };
    }

    private function proxyip(TcpConnection $connection, Request $request)
    {
        // 建立本地80端口的异步连接
        $connection_to_80 = new AsyncTcpConnection('tcp://www.baidu.com:80');
        var_dump($connection_to_80);
        // 设置将当前客户端连接的数据导向80端口的连接
        $connection->pipe($connection_to_80);
        // 设置80端口连接返回的数据导向客户端连接
        $connection_to_80->pipe($connection);
        // 执行异步连接
        $connection_to_80->connect();
    }

    function is400(TcpConnection $connection, Request $request)
    {
        $connection->send(new Response(404, [], 'is 400'));
    }

    function is404(TcpConnection $connection, Request $request)
    {
        //  $u=$request->path().$request->queryString()?:'没有参数';
        //  $connection->send(new Response(404, [], 'is 404：'.$u));
        $connection->send(new Response(200, array('Transfer-Encoding' => 'chunked'), '404'));
        $connection->send(new Chunk('<br>'));
        $connection->send(new Chunk($request->path()));
        $connection->send(new Chunk('<br>'));
        $connection->send(new Chunk($request->queryString()));
        $connection->send(new Chunk('<br>'));
        $connection->send(new Chunk(''));
    }

    function __call($method, $args)
    {
        $this->is404(...$args);
    }

    private function forResDb(TcpConnection $connection, Request $request): bool
    {
        // 如果Accept头是text/event-stream则说明是SSE请求
        if ($request->header('accept') === 'text/event-stream') {
            // 首先发送一个 Content-Type: text/event-stream 头的响应
            $connection->send(new Response(200, ['Content-Type' => 'text/event-stream']));
            $cb = function ($redis, $pattern, $chan, $msg) use ($connection) {
                var_dump($redis, $pattern, $chan, $msg);
                $connection->send(new ServerSentEvents([
                    'event' => 'message',
                    'data' => $this->get(),
                    'id' => 1000,
                    'retry' => 2
                ]));
            };
            $this->redis->psubscribe([self::msgName], 'cb');
            /* $timer_id = Timer::add(2, function () use ($connection, &$timer_id) {
                 // 连接关闭的时候要将定时器删除，避免定时器不断累积导致内存泄漏
                 if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                     Timer::del($timer_id);
                     return;
                 }
                 $connection->send(new ServerSentEvents(['event' => 'message', 'data' => '持续推送新数据' . date("Y-m-d H:i:s"), 'id' => 1000, 'retry' => 2]));
             });*/
            return true;
        }
        return false;
    }

    private function index(TcpConnection $connection, Request $request)
    {
        $this->forResDb($connection, $request) ?: $connection->send(
            "
                <script crossorigin='anonymous' src='https://lib.baomitu.com/vue/2.6.14/vue.js'></script>
                <div id='app'>{{message}}</div>
                <script>
                       let source = new EventSource('$this->httpAddress');
                       new Vue({
                                el: '#app',
                                data: {
                                    message: '0',
                                },
                                methods: {
                                    init(e){
                                        this.message = e.data
                                    }
                                },
                                created: function () {
                                    source.addEventListener('message', this.init, false);
                                }
                             })
                </script>
                "
        );
    }

    private function index2(TcpConnection $connection, Request $request)
    {
        $path = implode(DIRECTORY_SEPARATOR, [__DIR__, 'index.html']);
        $response = (new Response())->withFile($path);
        $connection->send($response);
    }

    private function proxyReq(TcpConnection $connection, Request $request)
    {
        $baidu = new AsyncTcpConnection('tcp://www.baidu.com:443');//支持text，tcp、ws
        $baidu->onMessage = function ($baidu, $data) use ($connection) {
            $connection->send($data);
        };
        $connection->onMessage = function ($connection, $data) use ($baidu) {
            $baidu->send($data);
        };
        $baidu->connect();
    }

}
