<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-27
 */
namespace Async;

use Exception;

class Manager{
	//异步任务标识符,用于统一redis 的key值
	const TASK_IDENTIFIER = 'Asynchronous task maters';
	const MAX_PROCESS_NUM = 10;
	const FAIL_TO_BLOCK = 'fail to block';
	const USR_EXIT = 'user exit signal';
	static private $PROCESS_NUM_EXCEEDED = '';//new Exception('running processes  Exceed the maximum');
	static private $TASK_REGISTERED = '';//new Exception('task name already exists');
	static private  $SEND_DATA_ERROR = '';//new Exception('task name not exists! send data error.');
	//异步进程列表@TODO 是否改为实时从redis获取 @WARNING 可能存在过期的风险，导致部分进程启动不了
	private $processList = [];
	//进程间通信
	//使用redis 1：实现任务锁 2：实现常驻进程的阻塞
	public $redis = null;


	public function __construct($redis){
		self::$PROCESS_NUM_EXCEEDED = new Exception('running processes  Exceed the maximum');
		self::$TASK_REGISTERED = new Exception('task name already exists');
		self::$SEND_DATA_ERROR = new Exception('task name not exists! send data error.');
		$this->redis = $redis;
		$this->initProcessList();
	}


	//task注册
	public function taskRegister($task_name, $process_id){
		if(!empty($this->processList[$task_name])){
			throw self::$TASK_REGISTERED;
		}
		$this->processList[$task_name] = array('pid'=>$process_id, 'stat'=>'registering');
		$this->redis->hset(self::TASK_IDENTIFIER,$task_name,serialize($this->processList[$task_name]));
        return true;
	}

    //redis 端口复用会导致多个进程读取错乱,所以要重新打开redis端口
    public function reOpenRedis(){
        $this->redis->close();
        $this->redis->pconnect();
    }

	//检测task_name 是否已经在运行中
	//@TODO 是否同时检测进程状态
	public function isTaskExist($task_name){
        return !empty($this->processList[$task_name]);
	}

	//task运行后
	public function taskFinished($task_name){
		//任务完成次数统计
        if(isset($this->processList[$task_name]['times'])){
            $this->processList[$task_name]['times'] += 1;
        }else{
            $this->processList[$task_name]['times'] = 1;
        }
		//将状态变更为已完成
		$this->processList[$task_name]['stat'] = 'finished';
		$this->syncTaskInfoToRedis($task_name);
        return true;
	}	

	//task运行前
	public function taskStarted($task_name){
		$this->processList[$task_name]['stat'] = 'running';
		$this->syncTaskInfoToRedis($task_name);
        return true;
	}

	//task注销
	public function taskLogout($task_name, $process_id){
		//@TODO 是否要将注销后的进程信息保留？
		$this->processList[$task_name] = null;
        return true;
	}

	/**
	 * 返回当前所有的异步任务 @WARNING 非实时的
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
	 * 将进程状态同步到redis上
	 */
	private function syncTaskInfoToRedis($task_name){
		if(empty($this->processList[$task_name])){
			$this->redis->hdel(self::TASK_IDENTIFIER,$task_name);
			return;
		}
		$this->redis->hset(self::TASK_IDENTIFIER,$task_name,serialize($this->processList[$task_name]));
	}

	/**
	 * 获取用户向进程发送的数据
	 * 任务根据返回值决定是否退出 @TODO 实现进程重启
	 */
	public function readTaskData($task_name, $process_id, $block = false){
		$popKey = $this->taskRedisListKey($task_name, $process_id);
		if($block){
			//阻塞
			$data = $this->redis->blpop($popKey,0);
			if(empty($data)){
				return self::FAIL_TO_BLOCK;
			}
		}else{
			//非阻塞
			$data = $this->redis->lpop($popKey);
		}
        isset($data[1]) && $data = unserialize($data[1]);
        if($data === self::USR_EXIT){
			return self::USR_EXIT;
        }else{
            return $data;
        }
	}

	/**
	 * 向进程发送数据
	 */
	public function sendData($task_name,$task_data){
		$process_data = $this->processList[$task_name];
		if(empty($process_data)){
			throw self::SEND_DATA_ERROR;
		}
		$task_data = serialize($task_data);
		$this->redis->rpush($this->taskRedisListKey($task_name,$process_data['pid']),$task_data);
	}

	private function taskRedisListKey($task_name, $process_id){
		return $task_name.'-'.$process_id;
	}

	/**
	 * 进程结束前，调用manager processWillEnd保证处理好任务的注册信息
	 */
	public function processWillEnd($task_name){
		//@TODO 是否要判断来自异常的退出
		$this->processList[$task_name] = null;
		$this->syncTaskInfoToRedis($task_name);
	}
}
