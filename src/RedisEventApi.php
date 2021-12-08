<?php

namespace php6140\tool\redis;

use Redis;

class RedisEventApi
{
    /**
     * @var Redis
     */
    protected Redis $cli;

    function __construct(Redis $cli)
    {
        $this->cli = $cli;
    }


    /**
     * 发布
     */
    function publish(string $event, $str)
    {
        $this->cli->publish($event, $str);
    }

    /**
     * 订阅
     * @param string ...$event
     */
    function subscribe($classObj, $funName, string ...$event)
    {
        $this->cli->subscribe($event, [$classObj, $funName]);

    }

    /**
     * 订阅匹配
     * @param string ...$event
     */
    function psubscribe($classObj, $funName, string ...$event)
    {
        $this->cli->psubscribe($event, [$classObj, $funName]);
    }
    /**
     * 订阅x销毁
     * @param string ...$event
     */
    function unsubscribe(string ...$event)
    {
        $this->cli->unsubscribe($event);
    }
}