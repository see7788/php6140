<?php

namespace see7788\php6140\tool;


use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

class HttpRes extends Response
{
    public TcpConnection $connection;

    function __construct(TcpConnection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    function httpReq(Request $request): HttpReq
    {
        return new HttpReq($this->connection, $request);
    }

    function location(string $location)
    {
        $this->withStatus(302);
        $this->withHeader('Location', $location);
        $this->connection->send($this);
    }

    function chunk(string $str): HttpResChunk
    {
        return new HttpResChunk($this->connection, $str);
    }

    function sse(string $event, string $infoStr): HttpResSse
    {
        return new HttpResSse($this->connection,$event,$infoStr);
    }

    function file(string $filePath): self
    {
        $this->withFile($filePath);
        $this->connection->send($this);
        return $this;
    }
}