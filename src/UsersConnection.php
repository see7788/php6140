<?php

namespace php6140\tool;

use Exception;
use php6140\tool\http\HttpRes;
use Workerman\Connection\TcpConnection;

class UsersConnection
{
    /**
     * @var TcpConnection[]
     */
    private array $usersConnection;

    function __construct_(){

    }
    function set(string $userKey, TcpConnection $connection): self
    {
        $this->usersConnection[$userKey] = $connection;
        return $this;
    }

    function del(string $userKey): self
    {
        unset ($this->usersConnection[$userKey]);
        return $this;
    }

    function send(string $msg = '没有消息', ...$userKeys): self
    {
        foreach ($userKeys as $key) {
            $this->usersConnection[$key]->send($msg);
        }
        return $this;
    }

    /**
     * @param string $userKey
     * @return HttpRes
     * @throws Exception
     */
    function httpRes(string $userKey): HttpRes
    {
        if (empty($this->usersConnection[$userKey])) {
            return new HttpRes($this->usersConnection[$userKey]);
        } else {
            throw new Exception('错误，不存在的$this->usersConnection[$userKey]');
        }
    }
}