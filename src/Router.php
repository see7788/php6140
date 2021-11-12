<?php

namespace jiankong0;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;
class Router
{
    public Worker $worker;
    private string $httpAddress;

    function __construct(string $ip, int $Port)
    {
        $this->httpAddress = "http://$ip:$Port";
        $this->worker = new Worker($this->httpAddress);
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
    function  index(TcpConnection $connection, Request $request){
        $connection->send('index');
    }

    function is404(TcpConnection $connection, Request $request)
    {
        $connection->send(new Response(404, [], 'is 404'));
    }
}