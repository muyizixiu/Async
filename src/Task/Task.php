<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-27
 */
namespace Async\Task;

use Async\Manager;
abstract class Task{
	private $task_name = '';
	private $task = null;
	private $manager = null;
	private $persist = false;
	private $process_id = 0;
	//任务初始化动作
	protected function init(){
		return $this->manager->taskRegister($this->task_name,$this->process_id);
	}
	//任务结束动作
	protected function deinit(){
		return $this->manager->taskLogout($this->task_name,$this->process_id);
	}
	//运行
	public function run($pid){
		$this->process_id = $pid;
	}
}
