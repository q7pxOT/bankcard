<?php
//exit('系统维护中...');
/*
if($_SERVER['REQUEST_SCHEME']!='https'){
	$surl='https://'.$_SERVER['HTTP_HOST'].'/';
	header("Location:{$surl}");
	exit;
}*/
define('APP_NAME','home');
define('APP_DEBUG',true);
include './global/global.ini.php';
include GLOBAL_PATH.'route.ini.php';//简单路由
?>