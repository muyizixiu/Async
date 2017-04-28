<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-27
 */
namespace Task;

use Async\Errors;

class Manager{
	//异步任务标识符,用于统一redis 的key值
	const TASK_IDENTIFIER = 'Asynchronous task maters';
	const MAX_PROCESS_NUM = 10;
	const FAIL_TO_BLOCK = 'fail to block';
	const USR_EXIT = 'user exit signal';
	//异步进程列表@TODO 是否改为实时从redis获取 @WARNING 可能存在过期的风险，导致部分进程启动不了
	private $processList = [];
	//进程间通信
	//使用redis 1：实现任务锁 2：实现常驻进程的阻塞
	private $redis = null;

	static $instance = null;

	public function __construct($redis){
		if($this->instance instanceof self){
			return $this->instance;
		}
		$this->redis = $redis;
		$this->instance = $this;
		$this->initProcessList();
	}


	//task注册
	public function taskRegister($task_name, $process_id){
		if(!empty($this->processList[$task_name])){
			throw Errors::TASK_REGISTERED;
		}
		$this->processList[$task_name] = array('pid'=>$process_id, 'stat'=>'registering');
		$redis->hset(self::TASK_IDENTIFIER,$task_name,serialize($this->processList[$task_name]));
	}

	//task运行后
	public function taskFinished($task_name){
	}	

	//task运行前
	public function taskStarted($task_name){
	}

	//task注销
	public function taskLogout($task_name, $process_id){
	}

	/**
	 * 返回当前所有的异步任务
	 */
	public function currentTask(){
		return $this->processList;
	}

	/**
	 * 使用redis获取当前异步任务信息
	 */
	private function initProcessList(){
		$result = $this->redis->hgetall(self::TASK_IDENTIFIER);
		foreach($result as $task_name => $data){
			$this->processList[$task_name] = unserialize($data);
		}
	}

	/**
	 * 获取用户向进程发送的数据
	 * 任务根据返回值决定是否退出 @TODO 实现进程重启
	 */
	public function readTaskData($task_name, $process_id, $block = false){
		$popKey = $this->taskRedisListKey($task_name, $process_id);
		if($block){
			//阻塞
			$data = $this->redis->blpop($popKey);
			if(empty($data)){
				return self::FAIL_TO_BLOCK;
			}
		}else{
			//非阻塞
			$data = $this->redis->lpop($popKey);
		}
		$data = unserialize($data);
		switch($data){
		case self::USR_EXIT:
			return self::USR_EXIT;
		default:
			return $data;
		}
	}

	/**
	 * 向进程发送数据
	 */
	public function sendData($task_name,$task_data){
		$process_data = $this->process_id[$task_name];
		if(empty($process_data)){
			throw Errors::SEND_DATA_ERROR;
		}
		$task_data = serialize($task_data);
		$this->redis->rpush($this->taskRedisListKey($task_name,$process_data['pid']),$task_data);
	}

	private function taskRedisListKey($task_name, $process_id){
		return $task_name.'-'.$process_id;
	}
}
