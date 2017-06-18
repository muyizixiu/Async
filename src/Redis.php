<?php
namespace Async;

class Redis{
    private $redis = null;
    private $host = '';
    private $port = '';
    private $passwd = '';

    public function __construct($host,$port,$passwd = ''){
        $this->redis = new \Redis();
        $this->host = $host;
        $this->port = $port;
        $this->passwd = $passwd;
    }

    public function connect(){
        $this->redis->connect($this->host,$this->port);
        if(!empty($this->passwd)){
            $this->redis->auth($passwd);
        }
    }

    public function pconnect(){
        $this->redis->pconnect($this->host,$this->port);
        if(!empty($this->passwd)){
            $this->redis->auth($passwd);
        }
    }

    public function __call($name,$args){
        try{
            if(!$this->redis->ping()){
                $this->connect();
            }
        }catch(\RedisException $e){
            $this->connect();
            $this->redis->ping();
        }
        $arg_num = count($args);
        try{
            switch($arg_num){
            case 0:
                return $this->redis->$name();
                break;
            case 1:
                return $this->redis->$name($args[0]);
                break;
            case 2:
                return $this->redis->$name($args[0],$args[1]);
                break;
            case 3: 
                return $this->redis->$name($args[0],$args[1],$args[2]);
            default:
                $reflect_redis = \ReflectionClass($this->redis);
                $method = $reflect_redis->getMethod($name);
                $method->invokeArgs($this->redis,$args);
            }
        }catch(\RedisException $e){
            $this->connect();
            return $this->__call($name,$args);
        }
        throw new \Exception("damn , there r no reason u get here!");
    }
}
