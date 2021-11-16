<?php

namespace php6140\tool;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\ServerSentEvents;

class HttpResSse
{
    public TcpConnection $connection;

    function __construct(TcpConnection $connection, string $event, string $infoStr)
    {
        $this->connection = $connection;
        $connection->send(new Response(200, array('Transfer-Encoding' => 'chunked')));
        $this->send($event, $infoStr);
    }

    function send(string $event, string $infoStr): self
    {
        $this->connection->send(new ServerSentEvents([
            'event' => $event,
            'data' => $infoStr,
            'id' => 1,
        ]));
        return $this;
    }

}