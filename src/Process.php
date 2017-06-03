<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-27
 */
namespace Async;

use Exception;
class Process{
	//@TODO 实现进程错误日志输出,重定向标准输入输出
	private $log = '/dev/null';
	private $task = null;
	private $manager = null;
	static private  $CAN_NOT_FORK_PROCESS = '';// new Exception('can not fork process');
	static private  $CAN_NOT_SET_SESSION = '';//new Exception('can not set session');
	static private  $SEND_DATA_ERROR = '';//new Exception('task name not exists! send data error.');

	public function __construct($manager,$task, $log){
		self::$CAN_NOT_FORK_PROCESS =  new Exception('can not fork process');
		self::$CAN_NOT_SET_SESSION = new Exception('can not set session');
		self::$SEND_DATA_ERROR = new Exception('task name not exists! send data error.');
		$this->manager = $manager;
		$this->task = $task;
		$this->log = $log;
		$this->fork(); 
	}

	public function fork(){
		if($pid = pcntl_fork() < 0){
			throw self::$CAN_NOT_FORK_PROCESS;
		}
		if($pid > 0){
			return;
		}
		//重定向错误输出
		ini_set('error_log',$this->log);
		//关闭标准输入
		fclose(STDIN);
		$log = $this->log;
		//重定向标准输出
		ob_start(function($buffer)use($log){
			$fd = fopen($log,'a');
			fwrite($fd,$buffer);
		});
		if(posix_setsid() < 0){
			throw self::$CAN_NOT_SET_SESSION;
		}
		//防止僵尸进程
		if($pid = pcntl_fork() < 0){
			throw self::$CAN_NOT_FORK_PROCESS;
		}
		if($pid > 0){
			return;
		}
		//进程结束前，通知对应的模块
		register_shutdown_function(array($this,'shutdown'));
		$this->task->run(posix_getppid());
	}

	//进程结束前，通知对应的模块
	public function shutdown(){
		$this->manager->processWillEnd($this->task_name);
	}

	//
}
