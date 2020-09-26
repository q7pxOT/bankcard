<?php
class Mysql{
	public $db=null;
	public $tableName='';
	private $loadFile='';	//日志路径 暂时不打日志
	public $safeCheck=true;
	public $lastSql='';
	
	//构造函数
	public function __construct($dataSourceIndex=0){
		$db_config=$_ENV['DB'][$dataSourceIndex];				
		$this->db=new Mysqli($db_config['HOST'],$db_config['USER'],$db_config['PASSWORD'],$db_config['NAME'],$db_config['PORT']);
		if($this->db->errno){
			exit('error');//$this->db->error
		}
		$this->db->set_charset('utf8');
		//$charset = $this->db->character_set_name();
		//printf ("Current character set is %s\n", $charset);
	}
	
	//析构函数
	public function __destruct(){
		$this->close();
	}
	
	public function close(){
		if($this->db){
			@$this->db->close();
		}
	}
	
	//执行sql
	public function query($sql){
		if($this->safeCheck){
			$sql=$this->checkSql($sql,'select');
		}
		$this->lastSql=$sql;//保存最后执行的sql
		$reault=$this->db->query($sql);
		return $reault;
	}
	
	public function query2($sql){
		if($this->safeCheck){
			$sql=$this->checkSql($sql,'insert');
		}
		$this->lastSql=$sql;//保存最后执行的sql
		$this->db->query($sql);
		return $this->db->insert_id;
	}
	
	//获取一条记录
	public function fetchRow($sql){
		if(strpos($sql,'limit')<0){
			$sql.=' limit 1 ';
		}
		$reault=$this->query($sql);
		$row=array();
		if($reault){
			$row=$reault->fetch_assoc();
		}
		return $row;		
	}
	
	//一条记录的第一个值
	public function fetchResult($sql){		
		$result=$this->query($sql);		
		if($result){
			$row=$result->fetch_assoc();			
			if($row&&reset($row)){
				return current($row);
			}else{
				return '';
			}			
		}else{
			return '';
		}
	}
	
	//统计记录数
	public function rowCount($table='',$where=''){
		$tb=empty($table)?$this->tableName:$table;
		$wh=empty($where)?'':" WHERE {$where} ";
		$sql="SELECT COUNT(1) from {$tb} {$wh}";	
		$res=$this->fetchResult($sql);
		if(empty($res)){
			$num=0;
		}else{
			$num=intval($res);
		}
		return $num;
	}
	
	//获取记录
	public function fetchRows($sql,$page=1,$page_size=1000){		
		$page=empty($page)?1:$page;
		if($page&&$page_size){
			$offset=($page-1)*$page_size;
			$tmp_sql=" LIMIT {$offset},{$page_size}";
			$sql.=$tmp_sql;
		}
		//$page=empty($page)?1:$page;
		//$page_size=empty($page_size)?10:$page_size;	
		$result=$this->query($sql);
		$rows=array();
		if(is_object($result)){
			while($row=$result->fetch_assoc()){
				$rows[]=$row;
			}
		}
		return $rows;
	}
	
	//更新
	public function update($arr,$where,$table){
		$field=$this->sqlUpdate($arr);
		$sql="UPDATE {$table} SET {$field} WHERE {$where}";
		return $this->query($sql);
	}
	
	//新增
	public function insert($arr,$table){
		$sql=$this->sqlInsert($arr,$table);
		return $this->query2($sql);
	}
	
	//删除
	public function delete($where,$table){
		$table=empty($table)?$this->tableName:$table;
		$sql="DELETE FROM {$table} WHERE {$where}";
		return $this->query($sql);
	}
	
	
	//事务相关
	public function startTrans(){
		$this->db->query('END');//防止有未结束的事务
		$this->db->query('BEGIN');//开始事务
	}
	
	public function commit(){
		$this->db->query('COMMIT');//提交事务
		$this->db->query('END');
	}
	
	public function rollback(){
		$this->db->query('ROLLBACK');//回滚事务
		$this->db->query('END');
	}
	
	//数组sql组装
	private function sqlUpdate($arr){
		if(empty($arr)){
			return ;
		}
		if(is_array($arr)){
			$str='';
			foreach($arr as $key=>$val){
				$tmp_k=trim($key);
				$tmp_v=trim($val);
				$str.="`{$tmp_k}`='{$tmp_v}',";
			}
			$str=trim($str,',');
			return $str;
		}else{
			return $arr;
		}
	}
	
	//in
	private function sqlInsert($arr,$table){
		if(empty($arr)){
			return ;
		}	
		if(is_array($arr)){
			$field=' (';
			$value=' VALUES (';
			foreach($arr as $key=>$val){
				$tmp_k=trim($key);
				$tmp_v=trim($val);
				$field.="`{$tmp_k}`,";
				$value.="'{$tmp_v}',";
			}
			
			$field=trim($field,',').')';
			$value=trim($value,',').')';
			$str=$field.$value;
			$sql="INSERT INTO {$table} {$str}";
			return $sql;
		}else{
			return $arr;
		}
	}
	
	//SQL语句过滤程序
	private function checkSql($db_string,$querytype='select'){
		$log_file = $this->loadFile;//暂时不写日志数据
		$clean = '';
		$error='';
		$old_pos = 0;
		$pos = -1;
		//$userIP = $this->getIp();
		//$getUrl = this->getUrl();

		//如果是普通查询语句，直接过滤一些特殊语法
		if($querytype=='select'){
			$notallow1 = "/[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}/";
			//$notallow2 = "--|/\*";
			if(preg_match($notallow1,$db_string)){
				//fputs(fopen($log_file,'a+'),date('Y-m-d H:i:s')."||$userIP||$getUrl||$db_string||SelectBreak\r\n");
				exit("<font size='5' color='red'>Safe Alert: Request Error step 1 !</font>");
			}
		}

		//完整的SQL检查
		while(true){
			$pos = strpos($db_string, '\'', $pos + 1);
			if ($pos === false){
				break;
			}
			$clean .= substr($db_string, $old_pos, $pos - $old_pos);
			while(true){
				$pos1 = strpos($db_string, '\'', $pos + 1);
				$pos2 = strpos($db_string, '\\', $pos + 1);
				if ($pos1 === false){
					break;
				}elseif ($pos2 == false || $pos2 > $pos1){
					$pos = $pos1;
					break;
				}
				$pos = $pos2 + 1;
			}
			$clean .= '$s$';
			$old_pos = $pos + 1;
		}
		$clean .= substr($db_string, $old_pos);
		$clean = trim(strtolower(preg_replace(array('~\s+~s' ), array(' '), $clean)));

		$fail='';
		//老版本的Mysql并不支持union，常用的程序里也不使用union，但是一些黑客使用它，所以检查它
		if(strpos($clean, 'union') !== false && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0){
			$fail = true;
			$error="union detect";
		}elseif(strpos($clean, '/*') > 2 || strpos($clean, '--') !== false || strpos($clean, '#') !== false){
			//发布版本的程序可能比较少包括--,#这样的注释，但是黑客经常使用它们
			$fail = true;
			$error="comment detect";
		}elseif(strpos($clean, 'sleep') !== false && preg_match('~(^|[^a-z])sleep($|[^[a-z])~s', $clean) != 0){
			//这些函数不会被使用，但是黑客会用它来操作文件，down掉数据库
			$fail = true;
			$error="slown down detect";
		}elseif(strpos($clean, 'benchmark') !== false && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0){
			$fail = true;
			$error="slown down detect";
		}elseif (strpos($clean, 'load_file') !== false && preg_match('~(^|[^a-z])load_file($|[^[a-z])~s', $clean) != 0){
			$fail = true;
			$error="file fun detect";
		}elseif(strpos($clean, 'into outfile') !== false && preg_match('~(^|[^a-z])into\s+outfile($|[^[a-z])~s', $clean) != 0){
			$fail = true;
			$error="file fun detect";
		}elseif(preg_match('~\([^)]*?select~s', $clean) != 0){
			//老版本的MYSQL不支持子查询，我们的程序里可能也用得少，但是黑客可以使用它来查询数据库敏感信息
			//$fail = true;
			//$error="sub select detect";
		}
		if($fail){
			//fputs(fopen($log_file,'a+'),date('Y-m-d H:i:s')."||$userIP||$getUrl||$db_string||$error\r\n");
			exit("<font size='5' color='red'>Safe Alert: Request Error step 2!{$error}</font>");
		}else{
			return $db_string;
		}
	}
	
	//获取访问ip地址
	private function getIp(){
		$type=$type? 1 : 0;
		static $ip=NULL;
		if($ip !==NULL) return $ip[$type];
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr=explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos=array_search('unknown',$arr);
			if(false!==$pos) unset($arr[$pos]);
			$ip=trim($arr[0]);
		}elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}elseif(isset($_SERVER['REMOTE_ADDR'])) {
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		//IP地址合法验证
		$long=ip2long($ip);
		$ip=$long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
	
	//获取访问的url
	private	function getUrl(){
		if(!empty($_SERVER["REQUEST_URI"])){
			$scriptName = $_SERVER["REQUEST_URI"];
			$nowurl = $scriptName;
		}else{
			$scriptName = $_SERVER["PHP_SELF"];
			if(empty($_SERVER["QUERY_STRING"])){
				$nowurl = $scriptName;
			}else{
				$nowurl = $scriptName."?".$_SERVER["QUERY_STRING"];
			}
		}
		return $nowurl;
	}
	
	//日志
	public function log($type,$word) {
		$fp = fopen("log_{$type}_".date(Ymd).".txt","a");    
		flock($fp, LOCK_EX) ;
		fwrite($fp,$word."：执行日期：".strftime("%Y%m%d%H%M%S",time())."\r\n");
		flock($fp, LOCK_UN); 
		fclose($fp);
	}
	
}


?>