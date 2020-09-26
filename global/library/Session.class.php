<?php
//memcache驱动
class SessMemcacheHandler{
    protected $config  = array();
	protected $handler=null;

    public function __construct($config = array()){
        $this->config = array_merge($_ENV['CONFIG']['SESSION'], $config);
    }
	
    public function open($savePath, $sessName){
		$this->handler=new MyMemcache(0);
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close(){
		$this->handler->close();
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param string $sessID
     */
    public function read($sessID){
		$this->handler=new MyMemcache(0);
		$session_id=$this->config['PREFIX'].$sessID;
		return (string) $this->handler->get($session_id);
    }

    /**
     * 写入Session
     * @access public
     * @param string    $sessID
     * @param String    $sessData
     * @return bool
     */
    public function write($sessID, $sessData){
		$this->handler=new MyMemcache(0);
		$session_id=$this->config['PREFIX'].$sessID;
		return $this->handler->set($session_id,$sessData,$this->config['EXPIRE']);
    }

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID){
		$this->handler=new MyMemcache(0);
		$session_id=$this->config['PREFIX'].$sessID;
		return $this->handler->delete($session_id);
    }

    /**
     * Session 垃圾回收
     * @access public
     * @param string $sessMaxLifeTime
     * @return true
     */
    public function gc($sessMaxLifeTime){
		$handler=new Mysql(0);
		$handler->delete(NOW_TIME.">update_time+".$this->config['EXPIRE'],'sys_session');
		unset($handler);
		return true;
    }
	
}

//mysql驱动
class SessMysqlHandler{
    protected $config  = array();
	protected $handler=null;

    public function __construct($config = array()){
        $this->config = array_merge($_ENV['CONFIG']['SESSION'], $config);
    }
	
    public function open($savePath, $sessName){
		$this->handler=new Mysql(0);
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close(){
		$this->gc($this->config['EXPIRE']);
        $this->handler->close();
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param string $sessID
     */
    public function read($sessID){
		$this->handler=new Mysql(0);
		$session_id=$this->config['PREFIX'].$sessID;
		$session_item=$this->handler->fetchRow("select * from sys_session where session_id='{$session_id}'");
		if(time()-$session_item['update_time']>$this->config['EXPIRE']){
			return false;
		}
		return (string) $session_item['session_data'];
    }

    /**
     * 写入Session
     * @access public
     * @param string    $sessID
     * @param String    $sessData
     * @return bool
     */
    public function write($sessID, $sessData){
		$this->handler=new Mysql(0);
		$session_id=$this->config['PREFIX'].$sessID;
		$session_data=array(
			'session_id'=>$session_id,
			'session_data'=>$sessData,
			'create_time'=>NOW_TIME,
			'create_ip'=>CLIENT_IP,
			'update_time'=>NOW_TIME,
			'update_ip'=>CLIENT_IP
		);
		$res=$this->handler->insert($session_data,'sys_session');
		if(!$res){
			unset($session_data['create_time'],$session_data['create_ip']);
			$res=$this->handler->update($session_data,"session_id='{$session_id}'",'sys_session');
		}
		return $res;
    }

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID){
		$this->handler=new Mysql(0);
		$session_id=$this->config['PREFIX'].$sessID;
		return $this->handler->delete("session_id='{$session_id}'",'sys_session');
    }

    /**
     * Session 垃圾回收
     * @access public
     * @param string $sessMaxLifeTime
     * @return true
     */
    public function gc($sessMaxLifeTime){
		$this->handler=new Mysql(0);
		$this->handler->delete(NOW_TIME.">update_time+".$this->config['EXPIRE'],'sys_session');
		return true;
    }
	
}

?>