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

	public function __construct($manager,$task, $log){
		self::$CAN_NOT_FORK_PROCESS =  new Exception('can not fork process');
		self::$CAN_NOT_SET_SESSION = new Exception('can not set session');
		$this->manager = $manager;
		$this->task = $task;
		$this->log = $log;
		$this->fork(); 
	}

	public function fork(){
        $pid = pcntl_fork();
		if(  $pid < 0){
			throw self::$CAN_NOT_FORK_PROCESS;
		}
		if($pid > 0){
			return;
		}
		if(  posix_setsid() < 0){
			throw self::$CAN_NOT_SET_SESSION;
		}
		//重定向错误输出
		ini_set('error_log',$this->log);
		//关闭标准输入和错误
		fclose(STDIN);
        fclose(STDERR);

		$log = $this->log;
		//重定向标准输出
        ob_start(function($buffer)use($log){
            $fd = fopen($log,'a');
            fwrite($fd,$buffer);
            fflush($fd);
        },1);
		//防止僵尸进程
        $pid = pcntl_fork();
		if($pid < 0){
			throw self::$CAN_NOT_FORK_PROCESS;
		}
		if($pid > 0){
            exit();
		}
        //重新打开redis
        $this->manager->reOpenRedis();
		//进程结束前，通知对应的模块
		register_shutdown_function(array($this,'shutdown'));
		$this->task->run(posix_getppid());
	}

	//进程结束前，通知对应的模块
	public function shutdown(){
		$this->manager->processWillEnd($this->task->task_name);
	}

	//
}
