<?php

namespace php6140\demo;

use php6140\tool\Index as Tool;
use php6140\tool\redis\Index as RedisCli;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

class UserWebSocketRouter
{
    public RedisCli $redis;
    public JKHttpRouter $jKHttpRouter;

    function __construct(RedisCli $redis,JKHttpRouter $jKHttpRouter)
    {
        $this->redis = $redis;
    }

    function onConnect(TcpConnection $connection)
    {
    }
    function onMessage(TcpConnection $connection, $db)
    {
        var_dump($db);
    }


    function __call($method, $args)
    {
        Tool::demoHttp404(...$args);
    }
}