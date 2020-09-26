<?php
@header("content-Type: text/html; charset=utf-8");
if(!defined("ROOT_PATH")) define("ROOT_PATH",dirname(__FILE__).'/../');
define('GLOBAL_PATH',ROOT_PATH.'global/');
define('NOW_TIME',time());
define('NOW_DATE',date('Y-m-d H:i:s',NOW_TIME));

$isWindows=false;
//db配置
include GLOBAL_PATH.'db.conf.php';
include GLOBAL_PATH.'global.func.php';

//基本类库
include GLOBAL_PATH.'library/Mysql.class.php';
include GLOBAL_PATH.'library/MyMemcache.class.php';



?>