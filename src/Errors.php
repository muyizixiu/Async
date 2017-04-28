<?php
/**
 * author: muyizixiu@outlook.com
 * date:  2017-04-28
 */
namespace Async;

use Exception;

class Errors{
	const PROCESS_NUM_EXCEEDED = new Exception('running processes  Exceed the maximum');
	const TASK_REGISTERED = new Exception('task name already exists');
	const CAN_NOT_FORK_PROCESS = new Exception('can not fork process');
	const CAN_NOT_SET_SESSION = new Exception('can not set session');
	const SEND_DATA_ERROR = new Exception('task name not exists! send data error.');
}
