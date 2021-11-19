<?php

namespace php6140\demo;

use php6140\tool\Index as Tool;
use php6140\tool\redis\Index as RedisCli;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

class JKHttpRouter
{
    public RedisCli $redis;

    function __construct(RedisCli $redis)
    {
        $this->redis = $redis;
    }

    function set(TcpConnection $connection, $data)
    {
        $connection->send($data);
        var_dump($data);
    }

    function onMessage(TcpConnection $connection, Request $request)
    {
        $type = Tool::httpReq($connection, $request)->nowRouter('sse');
        call_user_func([$this, $type], [$connection, $request]);
    }

    function onConnect(TcpConnection $connection)
    {
        $ips = $this->ips();
        $ip = $connection->getRemoteIp();
        if (!in_array($ip, $ips)) {
            $connection->close();
        }
    }

    function ip_bool(TcpConnection $connection)
    {
        $ips = $this->ips();
        $ip = $connection->getRemoteIp();
        $db = in_array($ip, $ips) ? 1 : 0;
        $connection->send($db);
    }

    function __call($method, $args)
    {
        Tool::demoHttp404(...$args);
    }

    function ips()
    {
        return $this->redis->keyHashApi('sheBeiIps')->hGetAll();
    }


    function minSbNames_get( )
    {
        return  $this->redis->keyApi('minSb*');
    }
     function subscribe_minSb_Rennumberjson_encode(RedisCli$classObj, $funName, string ...$minSbName)
    {
        $this->redis->eventApi()->psubscribe($classObj, $funName, ...$minSbName);
    }

     function psubscribe_minSb_Rennumber_json_encode($classObj, $funName)
    {
        $this->redis->eventApi()->psubscribe($classObj, $funName, 'minSb*');
    }

    function publish_minSb_Rennumber(string $sbId, int $in, int $out)
    {
        $minSbName='minSb' . $sbId;
        $this->redis->keyHashApi($minSbName)->hSet('in', $in);
        $this->redis->keyHashApi($minSbName)->hSet('out', $out);
        $db = json_encode(['minSbName' => $minSbName, 'in' => $in, 'out' => $out]);
        $this->redis->eventApi()->publish('minSb' . $sbId, $db);
    }

    function minSbName_Info_get($minSbName): array
    {
       //  ['minSbName' => 'minSb1','坑位数'=>8];
       return $this->redis->keyHashApi($minSbName)->hGetAll();
    }

}