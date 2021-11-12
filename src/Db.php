<?php

namespace jiankong0;
use Workerman\Redis\Client;
class Db
{
    private Client $redis;
    function __construct(string $ip, int $redisPort, int $redisSelectId)
    {
        $redis = new Client("redis://127.0.0.1:$redisPort");
        $redis->connect($ip, $redisPort);
        try {
            $redis->ping();
        } catch (RedisException $e) {
            echo 'redis 问题';
            die;
        }
        $redis->select($redisSelectId);
        $this->redis=$redis;
    }

    function hgetall($msgName): array
    {
        return  $this->redis->hgetall(self::msgName);
    }
}