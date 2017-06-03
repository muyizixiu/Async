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
		parent::__construct($task_name,$task,$manager,$persist);
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
			//任务开始执行
			if(!$this->manager->taskStarted($this->task_name))
				break;
			$this->task($this->task_data);
			//任务结束,如果接受到manager处退出的安排，则不就行下一次的任务，退循环
			if(!$this->manager->taskFinished($this->task_name))
				break;
			//获取下一次执行的任务数据,因为是普通常驻任务，所以只能从manager处读到退出等数据，任务数据为原有的数据
			$data = $this->manager->readTaskData($this->task_name, $this->process_id);
			switch($data){
			case Manager:USR_EXIT:
				return;
			default:
				//休眠时间，因此该任务非定时任务 @TODO 减去运行时间，实现定时任务
				sleep($this->tick);
			}
		}
		//任务结束，准备退出常驻进程
		$this->deinit();
	}



}
