<?php
namespace php6140\demo;
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

new Index();

Worker::runAll();