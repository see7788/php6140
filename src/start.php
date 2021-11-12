<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Index.php';
use jinkong0\Index;
use jinkong0\SheBei;
use Workerman\Worker;
$ip='192.168.10.100';
$redisPort=6379;
new Index(
    $ip,
    8088,
    $redisPort,
    0
);
new SheBei($ip,'','');
Worker::runAll();