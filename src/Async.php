<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-26
 */
namespace Async;

use Closure;
use Async\Task\TaskFactory;
use Async\Manager;
use Async\Process;

class Async{
	//@TODO 实现进程错误日志的输出
	private $log = null;

	//实现单例
	private static $instance = null;


	public function __construct($redis){
		if(self::$instance instanceof self){
			return self::$instance;
		}
	}

	public function task(Closure $task, $task_name, $persist = false, $isQueued = true, $taskData, $tick = 0){
		new Manager($this->redis);
		$task = TaskFactory::init($manager,$task,$task_name,$taskData,$persist,$isQueued,$tick);
		new Process($task,$this->log);
	}
}
