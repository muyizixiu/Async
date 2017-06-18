# Async
php 异步任务库
一个快速响应的web应用必然有很多繁重的异步任务去做，Async利用pnctl_fork创建异步进程，同时管理异步任务。

## Install
```
composer install yizixiumu/async
```

## Usage
对象
```
Async
public __construct(string $redis_host, string $redis_user, string $redis_password, string $log)
/**
 * @param Closure $task 闭包，这个闭包为异步的任务进程内容
 * @param string $task_name 异步任务的名称，@WARNING 每个任务的名称必须唯一，否则会有冲突
 * @param bool $persist 是否持久化，持久化则意味着进程常驻
 * @param bool $isQueued 是否队列化，队列化则意味着任务采用队列投递的方式
 * @param mixed $taskData 任务数据，进程会根据不同参数通过不同渠道将数据给$task任务
 * @param int $tick $task任务在持久化非阻塞的情况下为轮询执行，由tick指定执行时间
 */
public __task(closure $task, string $task_name, bool $persist = false, bool $isQueued = false, mixed $task_data = null, int $tick = 0){
}
public isTaskExists(string $task_name)
public sendData(string $task_name, mixed $task_data)
```
普通异步任务
```
use Async;
$redis_host = "localhost:6379";
$redis_user = "async";
$redis_password = "";
$async = new Async($redis_host,$redis_user,$redis_password,'/tmp/async);
$a->task(function($data){
    echo 'hello Async'."$data\n";
    doSomething();
},'common task');
```
该任务执行完后便结束异步进程。异步进程的标准输出和标准错误均重定向到指定的log位置

常驻队列异步任务
```
use Async;
$redis_host = "localhost:6379";
$redis_user = "async";
$redis_password = "";
$task_name = 'queued task';
$a = new Async($redis_host,$redis_user,$redis_password,'/tmp/async');
//当任务不存在时创建任务
if(!$a->isTaskExists($task_name)){
    $a->task(function($data){
        echo 'hello Async'."$data\n";
        doSomething();
    },$task_name,true,true,'123');
}
$a->task(function($data){
    echo 'hello Async'."$data\n";
    doSomething();
},$task_name,true,true,'123');

for($i = 0;$i < 10;$i ++){
    sleep(10);
    $a->sendData($task_name,"这是我第$i个数据");
}
```
以上代码模拟定时任务。
该异步进程一旦启动则常驻，不会每隔10秒启动一个异步进程，而是在同一个进程里面，不停的接受投递过来的参数。

## 依赖
本库依赖redis实现进程管理,需安装redis扩展，并提供账号和密码。
