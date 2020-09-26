<?php
class MyMemcache extends Memcache{
	private $index;
	public function __construct($index=0){
		$this->index=intval($index);
		$this->_reConnect();
	}
	
	public function __destruct(){
		parent::close();
	}
	
	//重新尝试连接服务
	public function _reConnect(){
		$server_config=array(
			array('host'=>'127.0.0.1','port'=>11211)
		);
		$server=$server_config[$this->index];
		parent::addServer($server['host'],$server['port']);
	}
	
	//设置
	public function set($key,$val,$expiration=0){
		$res=parent::set($key,$val,0,$expiration);//不进行压缩
		if($res===false){
			$this->_reConnect();//重新连接
			$res=parent::set($key,$val,0,$expiration);
		}
		return $res;
	}
	
	//获取
	public function get($key){
		$res=parent::get($key);
		if($res===false){			
   			$this->_reConnect();
			$res=parent::get($key);
		}   			
		return $res;
	}
	
	//递增
	public function increment($key,$offset=1){
		$offset=abs($offset);//取绝对值
		$res=parent::increment($key,$offset);
		if($res===false){
			$this->_reConnect();
			$res=parent::increment($key,$offset);
		}
		return $res;
	}
	
	//递减
	public function decrement($key,$offset=1){
		$offset=abs($offset);//取绝对值
		$res=parent::decrement($key,$offset);
		if($res===false){
			$this->_reConnect();
			$res=parent::decrement($key,$offset);
		}
		return $res;
	}
	
}

?>