<?php
/*
if($_SERVER['REQUEST_SCHEME']!='https'){
	$surl='https://'.$_SERVER['HTTP_HOST'].'/ht.php';
	header("Location:{$surl}");
	exit;
}
*/
define('APP_NAME','admin');
define('APP_DEBUG',false);
include './global/global.ini.php';
include GLOBAL_PATH.'route.ini.php';//简单路由
?>