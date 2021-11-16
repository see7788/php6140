<?php
require_once __DIR__ . '/../vendor/autoload.php';
use see7788\php6140\demo\Index;
use Workerman\Worker;

new Index();

Worker::runAll();