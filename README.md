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

## 依赖
本库依赖redis实现进程管理，当向Async类注入redis对象时，要保证其为非持久化连接（否则多进程共享同一个redis连接会导致未知错误）。且redis对象应该实现不带参数的close和pconnect方法
```
function close();//断开redis连接
function connect();//连接到redis-server
function pconnect();//持久化连接到redis-server
```
同时注入的redis对象应该负责所有redis的异常处理和重新连接。
