<?php

namespace see7788\php6140\tool;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

class HttpReq
{
    private TcpConnection $connection;
    public Request $request;

    function __construct(TcpConnection $connection, Request $request)
    {
        $this->request = $request;
        $this->connection = $connection;
    }

    function sseBool(): bool
    {
        return $this->request->header('accept') === 'text/event-stream';
    }

    function onLineBool(): bool
    {
        return $this->connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED;
    }

    function nowRouter(string $def): string
    {
        $type = $this->request->get('api');
        if (!$type) {
            $arr = explode('/',  $this->request->path());
            $arr = array_filter($arr);
            $type = end($arr);
        }
        if (!$type || $type == $this->connection->getLocalPort()) {
            $type = $def;
        }
        return strtolower($type);
    }

}