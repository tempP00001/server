<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';

define('BASE_PATH','./');
define('THINK_PATH', './admin/ThinkPHP');
//定义项目名称和路径
define('APP_NAME', 'admin');
define('APP_PATH', './admin');

// 加载框架入口文件 
require(THINK_PATH."/ThinkPHP.php");

//实例化一个网站应用实例
$AppWeb = new App(); 
//应用程序初始化
$AppWeb->run();

?>