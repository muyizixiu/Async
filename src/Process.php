<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-27
 */
namespace Async;

class Process{
	//@TODO 实现进程错误日志输出,重定向标准输入输出
	private $log = '/dev/null';
	private $task = null;

	public function __construct($task, $log){
		$this->task = $task;
		$this->log = $log;
		$this->fork();
	}

	public function fork(){
		if($pid = pcntl_fork() < 0){
			throw Errors::CAN_NOT_FORK_PROCESS;
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
			ob_clean();
		});
		if(posix_setsid() < 0){
			throw Errors::CAN_NOT_SET_SESSION;
		}
		//防止僵尸进程
		if($pid = pcntl_fork() < 0){
			throw Errors::CAN_NOT_FORK_PROCESS;
		}
		if($pid > 0){
			return;
		}
		$this->task->run(posix_getppid());
	}
}
