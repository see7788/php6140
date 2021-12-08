<?php

namespace php6140\tool\redis;

use Redis;

class RedisKeyHashApi
{
    /**
     * @var Redis
     */
    protected Redis $cli;
    protected string $key;

    function __construct(Redis $cli, string $key)
    {
        $this->cli = $cli;
        $this->key = $key;
    }


    function hDel(string ...$k)
    {
        return $this->cli->hDel($this->key, ...$k);
    }

    /*v*/
    function hGet(string $k)
    {
        return $this->cli->hGet($this->key, $k);
    }

    /*bool*/
    function hExists(string $k): bool
    {
        return $this->cli->hExists($this->key, $k);
    }

    /* [k=>v] */
    function hGetAll()//: array
    {
       // var_dump($this->key);
        return $this->cli->hGetAll($this->key);
    }

    /*[v]*/
    function hVals()
    {
        return $this->cli->hVals($this->key,);
    }

    /*[v]*/
    function hMGet(array $k)
    {
        return $this->cli->hMGet($this->key, $k);
    }

    /** [$groups]*/
    function hKeys(): array
    {
        return $this->cli->hKeys($this->key,);
    }

    /** int */
    function hLen()
    {
        return $this->cli->hLen($this->key,);
    }

    /** int 长度*/
    function hStrLen($k)
    {
        return $this->cli->hStrLen($this->key, $k);
    }

    /*[k=>v]*/
    function hMSet(array $kv): bool
    {
        return $this->cli->hMSet($this->key, $kv);
    }

    function hSet($k, $v)
    {
        $this->cli->hset($this->key, $k, $v);
    }
}