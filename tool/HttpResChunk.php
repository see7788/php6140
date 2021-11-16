<?php

namespace php6140\tool;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Chunk;
use Workerman\Protocols\Http\Response;

class HttpResChunk
{

    public TcpConnection $connection;

    function __construct(TcpConnection $connection, string $str)
    {
        $this->connection = $connection;
        $response = new Response(200, array('Transfer-Encoding' => 'chunked'), $str);
        $connection->send($response);
    }

    function send( $str='没有参数'):self
    {
        $this->connection->send(new Chunk($str));
        return $this;
    }
    function close():self
    {
        $this->connection->send(new Chunk(''));
        return $this;
    }

}