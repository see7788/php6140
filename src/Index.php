<?php

namespace php6140\tool;

use Exception;
use php6140\tool\http\HttpReq;
use php6140\tool\http\HttpRes;
use php6140\tool\Index as Tool;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Timer;
use Workerman\Worker;
use php6140\tool\redis\Index as RedisTool;

class Index
{
    /**
     * @param string $socket_name
     * @param array $context_option
     * @return Worker
     */
    static function socketBaseNew(string $socket_name = '', array $context_option = []): Worker
    {
        $c = new Worker($socket_name, $context_option);

        $c->onWorkerStart = function (Worker $worker) {
            echo "getname:{$worker->getSocketName()},id:{$worker->id},name:{$worker->name},workerId:$worker->workerId,group:{$worker->group},user:{$worker->user},系统启动...\n";
        };
        $c->onWorkerReload = function (Worker $worker) {
            echo "{$worker->getSocketName()},id:{$worker->id},name:{$worker->name},group:{$worker->group},user:{$worker->user},系统重启...\n";
        };
        $c->onConnect = function (TcpConnection $connection) {

            echo "{$connection->worker->getSocketName()},id:{$connection->worker->id},name:{$connection->worker->name},group:{$connection->worker->group},user:{$connection->worker->user},用户连接\n";
        };
        $c->onMessage = function (TcpConnection $connection, $db) {
            $connection->send('123456');
            echo "{$connection->worker->getSocketName()},id={$connection->id}}，用户消息\n";
        };
        $c->onClose = function (TcpConnection $connection) {
            echo "{$connection->worker->getSocketName()},id:{$connection->worker->id},name:{$connection->worker->name},group:{$connection->worker->group},user:{$connection->worker->user},用户断开\n";
        };
        $c->onError = function ($connection, $code, $msg) {
            echo "{$connection->worker->getSocketName()},id:{$connection->worker->id},name:{$connection->worker->name},group:{$connection->worker->group},user:{$connection->worker->user},错误//code：$code,msg：$msg\n";
        };
        return $c;
    }

    static function socketHttpNew(string $socket_name = '', array $context_option = []): Worker
    {
        $c = self::socketBaseNew($socket_name, $context_option);
        $c->onMessage = function (TcpConnection $connection, Request $request) {
            $connection->send('123456');
            echo "{$connection->worker->getSocketName()},id={$connection->id},消息：{$request->queryString()}，用户消息\n";
        };
        return $c;
    }

    static function demoHttp404(TcpConnection $connection, Request $request)
    {
        Tool::httpRes($connection)
            ->chunk('404:')
            ->send('\n path' . $request->path())
            ->send('\n query' . $request->queryString())
            ->send('\n post' . json_encode($request->post()))
            ->send('\n header' . json_encode($request->header()))
            ->close();
    }

    /**
     * 监听用户消息
     */
    static function demoSse(TcpConnection $connection, Request $request)
    {
        $initDb = '开始长时通知web';
        $res = self::httpRes($connection)->sse('init', $initDb);
        $req = self::httpReq($connection, $request);
        if ($req->sseBool()) {
            $timer_id = Timer::add(2, function () use ($req, $res, &$timer_id) {
                //var_dump(date("Y-m-d H:i:s"));
                $res->send('on', '持续推送新数据' . date("Y-m-d H:i:s"));
            });
        } else {
            $connection->close();
        }
    }

    static function httpReq(TcpConnection $connection, Request $request): HttpReq
    {
        return new HttpReq($connection, $request);
    }

    static function httpRes(TcpConnection $connection): HttpRes
    {
        return new HttpRes($connection);
    }

    static function usersNew(): UsersConnection
    {
        return new UsersConnection();
    }

    static function readDirNew(): ReadDir
    {
        return new ReadDir();
    }

    /**
     * @param int $selectId
     * @param string $ip
     * @param int $port
     * @return RedisTool
     * @throws Exception
     */
    static function redisNew(int $selectId,string $ip,int $port ): RedisTool
    {
        try {
            $cli = RedisTool::reactNew($selectId, $ip,$port);
            return new RedisTool($cli);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * 用户心跳方法，单位是秒
     * $connection->lastMessageTime = time()
     * add方法:轮训间隔,回调函数，回调参数
     */
    /*$time_now = time();
    $id= Timer::add(900, function ($time_now) {
        foreach ($this->worker->connections as $connection) {
            if (empty($connection->lastMessageTime)) {
                // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                $connection->lastMessageTime = $time_now;
            }else if ($time_now - $connection->lastMessageTime > 1000) {
                // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                $connection->close();
            }
        }
    },array($time_now));
    Timer::del($id);*/

    /**
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
}
