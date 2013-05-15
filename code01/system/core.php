<?php 
//系统初始化
@session_start();

error_reporting(0); //屏敝通知级错误
//定义APP_ROOT常量
 if(!defined('IS_CGI'))
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
 if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER["PHP_SELF"]);
            define('_PHP_FILE_',  rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',  rtrim($_SERVER["SCRIPT_NAME"],'/'));
        }
    }
if(!defined('APP_ROOT')) {
        // 网站URL根目录
        $_root = dirname(_PHP_FILE_);
        $_root = (($_root=='/' || $_root=='\\')?'':$_root);
        $_root = str_replace("/system","",$_root);
        define('APP_ROOT', $_root  );
    }
//定义物理根目录
define('APP_ROOT_PATH', str_replace('system/core.php', '', str_replace('\\', '/', __FILE__)));

//关于安装的检测
if(!file_exists(APP_ROOT_PATH."public/install.lock"))
{
	app_redirect(APP_ROOT."/install/");
}

//引入数据库的系统配置及定义配置函数
$sys_config = require 'config.php';
function app_conf($name)
{
	return stripslashes($GLOBALS['sys_config'][$name]);
}
//end 引入数据库的系统配置及定义配置函数

//引入时区配置及定义时间函数
if(function_exists('date_default_timezone_set'))
	date_default_timezone_set(app_conf('DEFAULT_TIMEZONE'));
//end 引入时区配置及定义时间函数

//定义缓存
require('cache/Cache.php');
$cache = CacheService::getInstance("File");
//end 定义缓存

//定义DB
require('db/db.php');
define('DB_PREFIX', app_conf('DB_PREFIX')); 
if(!file_exists(APP_ROOT_PATH.'app/Runtime/db_caches/'))
	mkdir(APP_ROOT_PATH.'app/Runtime/db_caches/');
$pconnect = false;
$db = new mysql_db(app_conf('DB_HOST').":".app_conf('DB_PORT'), app_conf('DB_USER'),app_conf('DB_PWD'),app_conf('DB_NAME'),'utf8',$pconnect);
//end 定义DB


//定义模板引擎
require('template/template.php');
if(!file_exists(APP_ROOT_PATH.'app/Runtime/tpl_caches/'))
	mkdir(APP_ROOT_PATH.'app/Runtime/tpl_caches/');
	
if(!file_exists(APP_ROOT_PATH.'app/Runtime/tpl_compiled/'))
	mkdir(APP_ROOT_PATH.'app/Runtime/tpl_compiled/');
$tmpl = new AppTemplate;
$tmpl->template_dir   = APP_ROOT_PATH . 'app/Tpl/' . app_conf("TEMPLATE");
$tmpl->cache_dir      = APP_ROOT_PATH . 'app/Runtime/tpl_caches';
$tmpl->compile_dir    = APP_ROOT_PATH . 'app/Runtime/tpl_compiled';
//end 定义模板引擎

$_REQUEST = array_merge($_GET,$_POST);
filter_request($_REQUEST);
require('utils/es_cookie.php');

$lang = require APP_ROOT_PATH.'/app/Lang/'.app_conf("SHOP_LANG").'/lang.php';
?>