<?php

namespace jinkong0;

use Redis;
use RedisException;
class Index2
{

    public Redis $redis;

    function __construct(string $ip,int $port)
    {
        
        $this->redis = new Redis();
        $this->redis->connect($ip,$port);
        try {
            $this->redis->ping();
            $this->redis->select(0);
        } catch (RedisException  $e) {
            echo '服务器内存数据库故障';
            die;
        }
    }

    function index(){
        echo 1;
        ob_start();
        for ($j = 1; $j <= 3; $j++) {
            echo $j . " <br/>";
          //  flush(); // 这一步会使cache新增的内容被挤出去，显示到浏览器上
            sleep(1); // 让程序"睡"一秒钟，会让你把效果看得更清楚
        }
        ob_end_clean();
    }
    /**
     *订阅,在终端执行，就会处在监听状态,等待发布者发布消息进行处理。
     */
    public function subscribe()
    {
      /*  $this->redis->psubscribe(['test'],function ($redis, $pattern, $chan, $msg){
           echo  1;
        });*/
    }

    /**
     * 发布
     */
    public function publish()
    {
        $this->redis->publish('test', '');
    }

    /**
     * 给hash表中某个key设置value
     *如果没有则设置成功,返回1,如果存在会替换原有的值,返回0,失败返回0
     * @return bool|int
     */
    function hset()
    {
        return $this->redis->hset('hash', 'cat', 'cat');
    }

    /**
     * 获取hash中某个key的值
     */
    function hget()
    {
        $this->redis->hGet('hash', 'cat');
    }

    /**
     * 删除hash中一个key 如果表不存在或key返回false
     * @return bool|int
     */
    function hdel()
    {
        return $this->redis->hdel('hash', 'dog');
    }

    /**
     * 获取hash中所有的keys
     * @return array
     */
    function hkeys()
    {
        return $this->redis->hkeys('hash');
    }

    /**
     * 获取hash中所有的值 顺序是随机的
     * @return array
     */
    function hvals()
    {
        return $this->redis->hvals('hash');
    }

    /**
     * 获取一个hash中所有的key和value 顺序是随机的
     * @return array
     */
    function hgetall()
    {
        return $this->redis->hgetall('hash');
    }

    /**
     * 获取hash中key的数量长度
     * @return false|int
     */
    function hlen()
    {
        return $this->redis->hlen('hash');
    }

}


