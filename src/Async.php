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

	//redis
	public $redis = null;


	public function __construct($redis,$log){
		if(self::$instance instanceof self){
			return self::$instance;
		}
		$this->redis = $redis;
		$this->log = $log;
	}

	/**
	 * @param Closure $task 闭包，这个闭包为异步的任务进程内容
	 * @param string $task_name 异步任务的名称，@WARNING 每个任务的名称必须唯一，否则会有冲突
	 * @param bool $persist 是否持久化，持久化则意味着进程常驻
	 * @param bool $isQueued 是否队列化，队列化则意味着任务采用队列投递的方式
	 * @param mixed $taskData 任务数据，进程会根据不同参数通过不同渠道将数据给$task任务
	 * @param int $tick $task任务在持久化非阻塞的情况下为轮询执行，由tick指定执行时间
	 */
	public function task(Closure $task, $task_name, $persist = false, $isQueued = false, $taskData = null, $tick = 0){
		$manager = new Manager($this->redis);
		$exist = $manager->isTaskExist($task_name);
        if($exist){
            if($persist){
                return $manager->sendData($task_name,$taskData);
            }else{
                throw new \Exception('task already exists!');
            }
        }
		$task = TaskFactory::init($manager,$task,$task_name,$taskData,$persist,$isQueued,$tick);
		new Process($manager,$task,$this->log);
	}
}
