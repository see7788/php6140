<?php

namespace php6140\demo;

use Exception;
use php6140\tool\Index as Tool;

class Index
{
    //  public RedisCli $redis;
    public string $redisIp = '127.0.0.1';
    public int $redisPort = 6379;
    public int $redisSelectId = 0;
    public int $userHttpPort = 6001;
    public int $userWebSocketPort = 6002;
    public int $jKHttpPort = 6003;


    function __construct()
    {

        $this->jKHttpStart();
        $this->userHttpStart();
        $this->userWebSocketStart();
    }


    function jKHttpStart()
    {
        $c = Tool::socketHttpNew("http://0.0.0.0:" . $this->jKHttpPort);
        $c->name = 'jKHttpServe';
        $c->count = 10;
        $c->onWorkerStart = function ($c) {
            try {
                $redis = Tool::redisNew($this->redisSelectId, $this->redisIp, $this->redisPort);
                $jKHttpRouter = new JKHttpRouter($redis);
                $c->onConnect = array($jKHttpRouter, 'onConnect');
                $c->onMessage = array($jKHttpRouter, 'onMessage');
                var_dump($redis->keyApi('*')->keys());
            } catch (Exception $e) {
                var_dump($e);
            }
        };
    }

    function userHttpStart()
    {
        $c = Tool::socketHttpNew("http://0.0.0.0:" . $this->userHttpPort);
        $c->name = 'userHttpServe';
        $c->count = 10;
        $c->onWorkerStart = function ($c) {
            try {
                $redis = Tool::redisNew($this->redisSelectId, $this->redisIp, $this->redisPort);
                $userHttpRouter = new UserHttpRouter($redis);
                var_dump($redis->keyApi('*')->keys());
                $c->onConnect = array($userHttpRouter, 'onConnect');
                $c->onMessage = array($userHttpRouter, 'onMessage');
            } catch (Exception $e) {
                var_dump($e);
            }
        };
    }

    function userWebSocketStart()
    {

        $c = Tool::socketHttpNew("websocket://0.0.0.0:" . $this->userWebSocketPort);
        $c->name = 'userWebSocketServe';
        $c->count = 10;
        $c->onWorkerStart = function ($c) {
            try {
                $redis = Tool::redisNew($this->redisSelectId, $this->redisIp, $this->redisPort);
                $jKHttpRouter = new JKHttpRouter($redis);
                $userWebSocketRouter = new UserWebSocketRouter($redis, $jKHttpRouter);
                var_dump($redis->keyApi('*')->keys());
                $c->onConnect = array($userWebSocketRouter, 'onConnect');
                $c->onMessage = array($userWebSocketRouter, 'onMessage');
            } catch (Exception $e) {
                var_dump($e);
            }
        };
    }


    function __call($method, $args)
    {
        var_dump('__call:error', $method, $args);
    }
}
