<?php

namespace redis;

use Exception;
use Redis;

class Index
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
     * @param string $ip
     * @param int $port
     * @param int $selectId
     * @return Redis
     * @throws Exception
     */
    static function reactNew(int $selectId=0,string  $ip='127.0.0.1',int $port=6379 ): Redis
    {
        $cli = new Redis();
        //https://blog.csdn.net/weixin_30483697/article/details/98541849?spm=1001.2101.3001.6650.2&utm_medium=distribute.pc_relevant.none-task-blog-2%7Edefault%7ECTRLIST%7Edefault-2.no_search_link&depth_1-utm_source=distribute.pc_relevant.none-task-blog-2%7Edefault%7ECTRLIST%7Edefault-2.no_search_link
        $res = $cli->connect($ip, $port, 1);//短链接，本地host，端口为6379，超过1秒放弃链接
        if (!$res) {
            throw new Exception('redis连接失败');
        }
        $res2 = $cli->select($selectId);//选择redis库,0~15 共16个库
        if (!$res2) {
            throw new Exception('redis连接失败');
        }
        //$cli->pconnect('127.0.0.1', $port);//长链接，本地host，端口为6379，超过1秒放弃链接
        return $cli;
    }


    function eventApi(): RedisEventApi
    {
        return new RedisEventApi($this->cli);
    }

    function otherApi(): RedisOtherApi
    {
        return new RedisOtherApi($this->cli);
    }

    function keyApi(string $key): RedisKeyApi
    {
        return new RedisKeyApi($this->cli, $key);
    }

    /**
     * [$groups=>[k=>v]]
     */
    function keyHashApi(string $key): RedisKeyHashApi
    {
        return new RedisKeyHashApi($this->cli, $key);
    }
}