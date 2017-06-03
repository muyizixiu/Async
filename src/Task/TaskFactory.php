<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-27
 */
namespace Async\Task;

use Async\Task\BlockTask;
use Async\Task\CommonTask;

class TaskFactory{
	//是否成为常驻进程 private $persist = false;
	//redis阻塞失败时，常驻进程启用轮询，设置轮询时间
	const POLLING_INTERVAL = 3600;
	//成为常驻进程，但轮询时间无效时，启用该默认值
	const TICK = 3600;
	//异步进程最大数量限制
	static public function init($manager,$task,$task_name,$taskData,$persist = true,$isQueued = true,$tick){
		if($isQueued){
			return new BlockTask($task, $task_name, $taskData, $manager,self::POLLING_INTERVAL);
		}else{
			return new CommonTask($task, $task_name, $taskData, $manager, $persist, $tick);
		}
	}
}
