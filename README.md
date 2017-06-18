# Async
php 异步任务库
一个快速响应的web应用必然有很多繁重的异步任务去做，Async利用pnctl_fork创建异步进程，同时管理异步任务。

## Install
```
composer install yizixiumu/async
```

## Usage
普通异步任务
```
$async = new Async($redis_host,$redis_user,$redis_password,'/tmp/async);
$a->task(function($data){
	echo 'hello Async'."$data\n";
  doSomething();
},'common task');
```
该任务执行完后便结束异步进程。异步进程的标准输出和标准错误均重定向到指定的log位置

常驻队列异步任务
```
$a = new Async($redis_host,$redis_user,$redis_password,'/tmp/async');
$a->task(function($data){
  echo 'hello Async'."$data\n";
  doSomething();
},'queued task',true,true,'123');

for($i = 0;$i < 10;$i ++){
    sleep(2);
    $a->sendData($task_name,"这是我第$i个数据");
}
```
以上代码模拟定时任务。
该异步进程一旦启动则常驻，不会每隔10秒启动一个异步进程，而是在同一个进程里面，不停的接受投递过来的参数。

## 依赖
本库依赖redis实现进程管理,需安装redis扩展，并提供账号和密码。
