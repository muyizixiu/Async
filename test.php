<?php
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



$redis = new Redis('10.23.170.144',6379);
$a = new Async($redis,'/tmp/async');
$a->task(function(){
	echo 'hello Async';
},'hello');
