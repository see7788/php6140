<?php

namespace php6140\tool\redis;

use Redis;

class RedisKeyApi
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
        $this->cli->watch($key);
        $this->cli->unwatch();
    }

    /**
     * *全部
     * xxx* xxx开头的全部
     */
    function keys()//: array
    {
        return $this->cli->keys($this->key);
    }

    /*KEY所指向的VALUE的数据类型*/
    function type(): int
    {
        return $this->cli->type($this->key);
    }

    /*筛选 https://www.daixiaorui.com/manual/redis-sort.html
    */
    function sort(): array
    {
        return $this->cli->sort($this->key);
    }

    /*KEY已经存在的时间*/
    function ttl(): int
    {
        return $this->cli->ttl($this->key);
    }

    /*KEY已经存在的时间*/
    function pttl(): int
    {
        return $this->cli->pttl($this->key);
    }

    /*拷贝到其他库*/
    function move(int $setTo_selectId): bool
    {
        return $this->cli->move($this->key, $setTo_selectId);
    }


    /*改名*/
    function rename(string $setTo_newKey): bool
    {
        return $this->cli->rename($this->key, $setTo_newKey);
    }

    /*拷贝*/
    function renameNx(string $copyTo_newKey): bool
    {
        return $this->cli->renameNx($this->key, $copyTo_newKey);
    }

    /*定时秒销毁*/
    function expire(string $sTime): bool
    {
        return $this->cli->expire($this->key, $sTime);
    }

    /*定时毫秒销毁*/
    function pexpire(string $msTime): bool
    {
        return $this->cli->pexpire($this->key, $msTime);
    }

    /*定时UNIX时间戳秒销毁*/
    function expireAt(string $unixSTime): bool
    {
        return $this->cli->expireAt($this->key, $unixSTime);
    }

    /*定时UNIX时间戳毫秒销毁*/
    function pexpireAt(string $unixMsTime): bool
    {
        return $this->cli->pexpireAt($this->key, $unixMsTime);
    }
}