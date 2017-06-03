<?php
namespace Async;

class Redis{
    private $redis = null;
    private $host = '';
    private $port = '';

    public function __construct($host,$port){
        $this->redis = new \Redis();
        $this->host = $host;
        $this->port = $port;
    }

    private function connect(){
        $this->redis->pconnect($this->host,$this->port);
    }

    public function __call($name,$args){
        try{
            if(!$this->redis->ping()){
                $this->connect();
            }
        }catch(\RedisException $e){
            $this->connect();
        }
        $arg_num = count($args);
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
        }
        throw new Exception("damn , u should use reflection instead of enumeration");
    }
}
