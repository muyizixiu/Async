<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-28
 */
namespace Async\Task;

use Async\Manager;
use Async\Manager;
class BlockTask{
	//队列非阻塞时，启用轮询
	private $pollingInterval = Manager::POLLING_INTERVAL;
	private $manager = null;

	public function __construct(Closure $task, $task_name, $task_data, $manager){
		$this->task = $task;
		$this->task_name = $task_name;
		$this->$task_data  $task_data;
		$this->manager = $manager;
	}

	public function run($pid){
		$parent::run($pid);
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
		$this->deinit();
		$data = $this->manager->readTaskData($this->task_name, $this->process_id, true);
		switch($data){
		case Manager:USR_EXIT:
			return;
		case Manager::FAIL_TO_BLOCK:
			//阻塞失败，启用休眠
			sleep($this->pollingInterval);
		default:
			$this->task_data = $data;
		}
	}
}
