<?php

namespace  redis;

use Redis;

class RedisOtherApi
{
    /**
     * @var Redis
     */
    protected Redis $cli;

    function __construct(Redis $cli)
    {
        $this->cli = $cli;

    }

    /*$key数量*/
    function dbSize(): int
    {
        return $this->cli->dbSize();
    }

    /*密码*/
    function auth(string $password): bool
    {
        return $this->cli->auth($password);
    }

    /*持久化到磁盘，同步*/
    function save(): bool
    {
        return $this->cli->save();
    }

    /*持久化到磁盘，异步*/
    function bgSave(): bool
    {
        return $this->cli->bgSave();
    }

    /*最后一次数据磁盘持久化的时间戳，异步*/
    function lastSave(): int
    {
        return $this->cli->lastSave();
    }

    /*清除当前DB中的key*/
    function flushDB(): bool
    {
        return $this->cli->flushDB();
    }

    /*清除所有DB中的key*/
    function flushAll(): bool
    {
        return $this->cli->flushAll();
    }
}