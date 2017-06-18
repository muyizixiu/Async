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
include "src/Redis.php";

use Async\Async;
use Async\Redis;



$a = new Async('localhost',6379,'','/tmp/async');
$task_name = 'queued task';
$a->task(function($data){
	echo 'hello Async'."$data\n";
},$task_name,true,true,'123');

for($i = 0;$i < 10;$i ++){
    sleep(2);
    $a->sendData($task_name,$i.'-');
}

$ab = new Async('localhost',6379,'','/tmp/async');
$ab->task(function($data){
    echo 'i`m a new task. and i am the type that when i finish my job i just go away!';
    sleep(10);
},'commontask job');
echo time() - $start;
