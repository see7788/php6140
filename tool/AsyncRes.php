<?php

namespace see7788\php6140\tool;

use ErrorException;
use Exception;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

class AsyncRes
{
    public TcpConnection $connection;

    function __construct(TcpConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $url
     * @return AsyncTcpConnection
     * @throws ErrorException
     */
    function httpPipe(string $url='tcp://www.baidu.com:80'): AsyncTcpConnection
    {
        try {
            $c= new AsyncTcpConnection($url);
            // 设置将当前客户端连接的数据导向80端口的连接
            $c->onConnect = function(AsyncTcpConnection $c)
            {
                echo "httpPipe connect success\n";
                $c->send("GET / HTTP/1.1\r\nHost: www.baidu.com\r\nConnection: keep-alive\r\n\r\n");
            };
            $c->onMessage = function(AsyncTcpConnection $c, $http_buffer)
            {
                 $this->connection->send($http_buffer);
            };
            $c->onClose = function(AsyncTcpConnection $c)
            {
                echo "httpPipe connection closed\n";
            };
            $c->onError = function(AsyncTcpConnection $c, $code, $msg)
            {
                echo "httpPipe Error code:$code msg:$msg\n";
                $c->close();
            };
            $this->connection->pipe($c);
            // 设置80端口连接返回的数据导向客户端连接
            $c->pipe($this->connection);
            // 执行异步连接
            $c->connect();
            return $c;
        } catch (Exception $e) {
            throw new ErrorException('错误，不存在的$this->usersConnection[$userKey]');
        }
    }

    /**
     * @throws ErrorException
     * $socket_name 'tcp://www.baidu.com:443'
     * 支持 tcp、ssl、ws、frame、text
     */
    function httpProxy($socket_name,$headStr)
    {
        try {
            $c = new AsyncTcpConnection($socket_name);
            $c->transport = 'ssl';
            $c->onError = function(AsyncTcpConnection $c, $code, $msg)
            {
                echo "httpPipe Error code:$code msg:$msg\n";
            };
            $c->onConnect = function(AsyncTcpConnection $c)use($headStr)
            {
                $c->send($headStr);
               // $c->send("GET / HTTP/1.1\r\nHost: www.baidu.com\r\nConnection: keep-alive\r\n\r\n");
            };
            $c->onMessage = function (AsyncTcpConnection $c, $data) {
                $this->connection->send($data);
                $c->close();
            };
        } catch (Exception $e) {
            throw new ErrorException('错误，不存在的$this->usersConnection[$userKey]');
        }
    }

    /**
     * @throws ErrorException
     * $myIpPortOn '114.215.84.87:2333'//本机用于访问的ip端口
     * $toIpPort 'ws://echo.websocket.org:80' //目标ip端口
     * $onConnectStr 成功连接时发送的文本
     */
    function webSocketPipe($myIpPortOn,$toIpPort,$onConnectStr){
        try {
            $c = new AsyncTcpConnection(
                $toIpPort, //'ws://echo.websocket.org:80'
                array(
                    'socket' => array(
                        // ip必须是本机网卡ip，并且能访问对方主机，否则无效
                        'bindto' =>$myIpPortOn// '114.215.84.87:2333',
                    ),
                )
            );

            $c->onConnect = function(AsyncTcpConnection $c)use($onConnectStr) {
                $c->send($onConnectStr);
            };

            $c->onMessage = function(AsyncTcpConnection $c, $data) {
                echo $data;
            };
            $c->connect();
        } catch (Exception $e) {
            throw new ErrorException('错误，不存在的$this->usersConnection[$userKey]');
        }

    }
}