# Async
php 异步任务库
一个快速响应的web应用必然有很多繁重的异步任务去做，Async利用pnctl_fork创建异步进程，同时管理异步任务。

## Install
```
composer install muyizixiu/async
```

## Usage
普通异步任务
```
$async = new Async($redis,'/tmp/async);
$a->task(function($data){
	echo 'hello Async'."$data\n";
  doSomething();
},'common task');
```
该任务执行完后便结束异步进程。异步进程的标准输出和标准错误均重定向到指定的log位置

常驻队列异步任务
```
$a = new Async($redis,'/tmp/async');
for(;;){
  sleep(10);
  $a->task(function($data){
	  echo 'hello Async'."$data\n";
    doSomething();
  },'queued task',true,true,'123');
)
```
以上代码模拟定时任务。
该异步进程一旦启动则常驻，不会每隔10秒启动一个异步进程，而是在同一个进程里面，不停的接受投递过来的参数。
