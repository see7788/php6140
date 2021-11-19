<?php

namespace php6140\demo;

use php6140\tool\Index as Tool;
use php6140\tool\redis\Index as RedisCli;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

class UserHttpRouter
{
    public RedisCli $redis;
    public array $userHttpFiles;

    function __construct(RedisCli $redis){
        $this->redis = $redis;
        //var_dump(implode(DIRECTORY_SEPARATOR, [__DIR__, 'userHttp']));
        $this->userHttpFiles = Tool::readDirNew()->add(__DIR__, 'userHttp')->files;
        //var_dump( $this->userHttpFiles);
    }
    function sseget(TcpConnection $connection, Request $request)
    {
        Tool::demoSse($connection, $request);
    }

    function sse(TcpConnection $connection, Request $request)
    {
        $port = $connection->getLocalPort();
        $connection->send(
            "
                <script crossorigin='anonymous' src='vue.js'></script>
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


    function onConnect(TcpConnection $connection){
        var_dump($connection);
    }

    function onMessage(TcpConnection $connection, Request $request){
        $type = Tool::httpReq($connection, $request)->nowRouter('sse');
        if (strpos($type, '.') && !empty($this->userHttpFiles[$type])) {
            Tool::httpRes($connection)->file($this->userHttpFiles[$type]);
        } else  {//if (method_exists($this, $type))
            call_user_func([$this, $type], [$connection, $request]);
        }
    }
    function __call($method, $args)
    {
        Tool::demoHttp404(...$args);
    }
}