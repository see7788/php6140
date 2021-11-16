<?php

namespace see7788\php6140\tool;

use ErrorException;
use Workerman\Connection\TcpConnection;

class UsersConnection
{
    /**
     * @var TcpConnection[]
     */
    private array $usersConnection;

    function __construct_(){

    }
    function setUser(string $userKey, TcpConnection $connection): self
    {
        $this->usersConnection[$userKey] = $connection;
        return $this;
    }

    function delUser(string $userKey): self
    {
        unset ($this->usersConnection[$userKey]);
        return $this;
    }

    function sendUsers(string $msg = '没有消息', ...$userKeys): self
    {
        foreach ($userKeys as $key) {
            $this->usersConnection[$key]->send($msg);
        }
        return $this;
    }

    /**
     * @param string $userKey
     * @return HttpRes
     * @throws ErrorException
     */
    function httpRes(string $userKey): HttpRes
    {
        if (empty($this->usersConnection[$userKey])) {
            return new HttpRes($this->usersConnection[$userKey]);
        } else {
            throw new ErrorException('错误，不存在的$this->usersConnection[$userKey]');
        }
    }

    /**
     * @param string $userKey
     * @param string $str
     * @return HttpResChunk
     * @throws ErrorException
     */
    function httpResChunk(string $userKey, string $str): HttpResChunk
    {

        if (empty($this->usersConnection[$userKey])) {
            return new HttpResChunk($this->usersConnection[$userKey], $str);
        } else {
            throw new ErrorException('错误，不存在的$this->usersConnection[$userKey]');
        }
    }

    /**
     * @param string $userKey
     * @param string $event
     * @param string $infoStr
     * @return HttpResSse
     * @throws ErrorException
     */
    function httpResSse(string $userKey,string $event, string $infoStr): HttpResSse
    {
        if (empty($this->usersConnection[$userKey])) {
            return new  HttpResSse($this->usersConnection[$userKey],$event,$infoStr);
        } else {
            throw new ErrorException('错误，不存在的$this->usersConnection[$userKey]');
        }
    }
}