<?php
$start = time();
echo posix_getpid();
/**
 * author: muyizixiu@outlook.com
 * date:  2017-05-15
 */
include "src/Async.php";
include "src/Errors.php";
include "src/Manager.php";
include "src/Process.php";
include "src/Task/Task.php";
include "src/Task/BlockTask.php";
include "src/Task/CommonTask.php";
include "src/Task/TaskFactory.php";
include "Redis.php";

use Async\Async;
use Async\Redis;



$redis = new Redis('localhost',6379);
$a = new Async($redis,'/tmp/async');
$a->task(function($data){
	echo 'hello Async'."$data\n";
},'queued task',true,true,'123');

$newredis = new Redis('localhost',6379);
$ab = new Async($newredis,'/tmp/async');
$ab->task(function($data){
    echo 'i`m a new task. and i am the type that when i finish my job i just go away!';
    sleep(10);
},'commontask job');
echo time() - $start;
