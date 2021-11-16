<?php

namespace php6140\tool;

use Redis;
use RedisException;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Timer;
use Workerman\Worker;

class Base
{
    public Worker $worker;

    /**
     * @param string $type
     * @param int $port
     * @param array $context
     * //php start.php start -d进入的是daemon守护进程模式，终端关闭不会影响Workerman
     * //重启 平滑重启 查看状态 查看连接状态http://doc3.workerman.net/315117
     * //连接数超过1000要安装插件 http://doc3.workerman.net/315116
     * 作为客户端 http://doc3.workerman.net/315299
     * //证书最好是申请的证书$context = array(
     * 'ssl' => array(
     * 'local_cert'  => '/etc/nginx/conf.d/ssl/server.pem', // 也可以是crt文件
     * 'local_pk'    => '/etc/nginx/conf.d/ssl/server.key',
     * 'verify_peer' => false,
     * )
     * );
     * 当收到数据后用函数bin2hex($data)可以将数据转换成16进制。
     * 发送数据前用hex2bin($data)将16进制数据转换成二进制发送
     * "$type://0.0.0.0:$port"
     */
    function __construct(string $socket_name = '', array $context = [])
    {
        $this->worker = new Worker($socket_name, $context);
        if (count($context) && $context['ssl']) {
            $this->worker->transport = 'ssl';
        }
    }

    function __call($method, $args)
    {
        var_dump('__call', $method, $args);
    }

    function usersConnection(){
        return  new UsersConnection();
    }
    /**
     * 心跳
     * $connection->lastMessageTime = time()
     */
    function pongPong(int $name_forTime = 60, bool $del = false)
    {
        if ($del) {
            Timer::del($name_forTime);
        } else {
            Timer::add($name_forTime, function () use ($name_forTime) {
                $time_now = time();
                foreach ($this->worker->connections as $connection) {
                    // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                    if (empty($connection->lastMessageTime)) {
                        $connection->lastMessageTime = $time_now;
                        continue;
                    }
                    // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                    if ($time_now - $connection->lastMessageTime > $name_forTime) {
                        $connection->close();
                    }
                }
            });
        }
    }


    function httpReq(TcpConnection $connection, Request $request): HttpReq
    {
        return new HttpReq($connection, $request);
    }

    function httpRes(TcpConnection $connection, int $status = 200): HttpRes
    {
        return new HttpRes($connection, $status);
    }

    function readDir(): ReadDir
    {
        return new ReadDir();
    }

    function redisInit(int $port, int $selectId): Redis
    {
        $redis = new Redis();
        //https://blog.csdn.net/weixin_30483697/article/details/98541849?spm=1001.2101.3001.6650.2&utm_medium=distribute.pc_relevant.none-task-blog-2%7Edefault%7ECTRLIST%7Edefault-2.no_search_link&depth_1-utm_source=distribute.pc_relevant.none-task-blog-2%7Edefault%7ECTRLIST%7Edefault-2.no_search_link
        //$redis->connect('127.0.0.1',$redisPort,1);//短链接，本地host，端口为6379，超过1秒放弃链接
        $redis->pconnect('127.0.0.1', $port, 1);//长链接，本地host，端口为6379，超过1秒放弃链接
        //$redis->select($redisSelectId);//选择redis库,0~15 共16个库
        try {
            $redis->ping();
        } catch (RedisException $e) {
            var_dump($e);
        }
        $redis->select($selectId);
        return $redis;
    }
}