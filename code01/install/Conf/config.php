<?php
if (!defined('THINK_PATH')) exit();
$array=array(
	'APP_AUTOLOAD_PATH'     => 'Think.Util.',// __autoLoad 机制额外检测路径设置,注意搜索顺序	
	'URL_MODEL'	=>	0,	
	'DIRS_CHECK'	=> array(
		//该系统需要检测的文件夹权限
		'/public/',  		//公共目录
		'/app/Runtime/',  //前台编译缓存目录 
		'/admin/Runtime/',   //后台编译缓存目录
	),
);
return $array;
?>