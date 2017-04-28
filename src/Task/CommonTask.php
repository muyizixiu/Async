<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-26
 */
namespace Async\Task;

use Closure;
use Async\Task\Task;

class CommonTask extends Task{
	private $task_data;
	private $tick = 3;
	public function __construct(Closure $task, $task_name, $task_data, $manager, $persist, $tick){
		$this->task = $task;
		$this->task_name = $task_name;
		$this->$persist;
		$this->manager = $manager;
		$this->task_data = $task_data;
		$this->tick = $tick;
	}

	public function run($pid){
		parent::run($pid);
		// 任务执行前的初始化 @TODO 是否应该迁移到进程初始化中?
		if(!$this->init()){
			return;
		}
		while($this->persist){
			if(!$this->manager->taskStarted($this->task_name))
				break;
			$this->task($this->task_data);
			if(!this->manager->taskFinished($this->task_name))
				break;
		}
		//任务结束，准备退出常驻进程
		$this->deinit();
		$data = $this->manager->readTaskData($this->task_name, $this->process_id);
		switch($data){
		case Manager:USR_EXIT:
			return;
		default:
			//休眠时间，因此该任务非定时任务 @TODO 减去运行时间，实现定时任务
			sleep($this->tick);
		}
	}

	protected function init(){
		return parent::init();
	}

	protected function deinit(){
		return parent::deinit();
	}


}
