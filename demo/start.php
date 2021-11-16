<?php
require_once __DIR__ . '/../vendor/autoload.php';
use php6140\demo\Index;
use Workerman\Worker;

new Index();

Worker::runAll();