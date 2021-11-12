<?php

namespace jinkong0;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

class SheBei extends Router
{
    private string $httpAddress;

    function __construct(string $ip, int $httpPort, int $redisPort, int $redisSelectId)
    {
        parent::__construct($ip, $redisPort, $redisSelectId);
        $this->httpAddress = "http://$ip:$httpPort";
        $this->worker = new Worker($this->httpAddress);
        $this->routers();
    }

    private function routers()
    {
        $this->worker->onMessage = function (TcpConnection $connection, Request $request) {
            $fun = substr($request->path(), 1);
            $fun = strtolower($fun);
            //是否方法
            if ($fun === '') {
                $this->index($connection, $request);
            } else if (is_callable(array($this, $fun))) {
                $this->$fun($connection, $request);
            } else {
                $this->is404($connection, $request);
            }
        };
    }

    function is404(TcpConnection $connection, Request $request)
    {
        $connection->send(new Response(404, [], 'is 404'));
    }

    function set($sbId, $in, $out)
    {
        $this->redis->hset('in', $sbId, $in);
        $this->redis->hset('out', $sbId, $out);
        $this->redis->publish(self::msgName, $sbId);
    }

    function index(TcpConnection $connection, Request $request)
    {
        $sbId = $request->get('id');
        $in = $request->get('in');
        $out = $request->get('out');
        $this->set($sbId, $in, $out);
    }

}