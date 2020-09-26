<?php
class MyMemcached extends Memcached{
	
	//memcahce配置
	protected $hosts=array(
		array('ip'=>'127.0.0.1','port'=>'11211')
	);
	
	protected $master_host = '';
	protected $master_port ='';
	
	protected $bakup_host = '';
	protected $bakup_port = '';

	public function __construct($i=0,$hosts=array()){     
		parent::__construct();
		$this->setOption(Memcached::OPT_COMPRESSION,false);//不进行压缩
		$i=intval($i);
		if($hosts){
			$this->hosts=$hosts;
		}
		$this->addServer($this->hosts[$i]['ip'],$this->hosts[$i]['port']); 
    }

	//添加服务
	public function addServer($host,$port){
		$this->master_host = $host;
		$this->master_port = $port;
		parent::addServer($host,$port);
	}
	
	//添加备份服务
	function addBakServer($host,$port){
		$this->bakup_host = $host;
		$this->bakup_port = $port;		
	}
	
	//重新连接服务
	public function _reConnect(){
		parent::__construct();        
        $this->setOption(Memcached::OPT_COMPRESSION,false);
        if(!empty($this->bakup_host)){
        	parent::addServer($this->bakup_host,$this->bakup_port);
        	$this->master_host = $this->bakup_host; 
			$this->master_host = $this->bakup_port;
        }else{
        	parent::addServer($this->master_host,$this->master_port);
        }
	}
	
	public function set($key,$val,$expiration=0){
		$res = parent::set($key,$val,$expiration);
		if($res === false){
			$this->_reConnect();
			$res = parent::set($key,$val,$expiration);
		}		
		return $res;
	}
	
	public function get($key){
		$res = parent::get($key);
		$op_res = parent::getResultCode();
		if($op_res!= Memcached::RES_SUCCESS && $op_res!= Memcached::RES_NOTFOUND){			
   			$this->_reConnect();
			$res = parent::get($key);
		}			
		return $res;
	}
	
	//自增或自减
	public function increment($key,$offset=1){
		$res = parent::increment($key,$offset);
		if($res === false){
			$this->_reConnect();	
			$res = parent::increment($key,$offset);
		}
		return $res;
	}
	
	//分布式算法
	protected function _crc32($key){
		$i=sprintf('%u',crc32($key)) % count($this->hosts); //分布算法
		return $i;
	}
	
}


?>