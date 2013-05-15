<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require_once 'common.php';
$IMG_APP_ROOT = APP_ROOT;

//定义当前语言包
$GLOBALS['tmpl']->assign("LANG",$lang);
//定义模板路径
$GLOBALS['tmpl']->assign("TMPL",APP_ROOT."/app/Tpl/".app_conf("TEMPLATE"));
//输出根路径
$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);



//处理城市
$city_name = trim(addslashes($_REQUEST['city']));
$deal_city = '';
if($city_name)
{
	$deal_city = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_city where uname='".$city_name."' and is_effect = 1 and is_delete = 0");
}
if(!$deal_city)
$deal_city = get_current_deal_city();
es_cookie::set("deal_city",$deal_city['id']);

//输出城市
$deal_city_list = get_deal_citys();
$GLOBALS['tmpl']->assign("deal_city_list",$deal_city_list);
$GLOBALS['tmpl']->assign("deal_city",$deal_city);

//输出页面的标题关键词与描述
$GLOBALS['tmpl']->assign("shop_info",get_shop_info());
if($city_name)
{
	$GLOBALS['tmpl']->assign("city_title",$deal_city['name']);
}
//输出导航菜单
$nav_list = get_nav_list();

//获取当前的url相关数据
//1. 获取 module 即文件名
$scriptName = $_SERVER["PHP_SELF"];
$scriptName = str_replace(".php",'',$scriptName);
$scriptName = explode("/",$scriptName);
$current_module = $scriptName[count($scriptName)-1];

//2. 获取当前 act
if($_REQUEST['act']&&$_REQUEST['act']!='index')
{
	$current_act = trim($_REQUEST['act']);
}
else 
$current_act= '';
//3. 获取当前 ID
$current_id = intval($_REQUEST['id']);
//end 获取当前的url相关数据


foreach($nav_list as $k=>$v)
{
	if($v['url']!='')
	{
		if(substr($v['url'],0,7)!="http://")
		{		
			//开始分析url
			$nav_list[$k]['url'] = APP_ROOT."/".$v['url'];
		}
	}
	else
	{
		$route = $v['u_module'];
		if($v['u_action']!='')
		$route.="#".$v['u_action'];
		if($v['u_param']!='')
		$route.=$v['u_param'];
		$nav_list[$k]['url'] = url_pack($route,$v['u_id']);
		if($current_module==$v['u_module'])
		{
			if($v['u_action']=='index')$v['u_action'] = '';		
			if($current_act==$v['u_action'])
			{
				if($current_id==$v['u_id'])
				{
					$nav_list[$k]['current'] = 1;
				}
			}
		}
	}
}
$GLOBALS['tmpl']->assign("nav_list",$nav_list);

//输出帮助
$deal_help = get_help();
$GLOBALS['tmpl']->assign("deal_help",$deal_help);


//输出语言包的js
if(!file_exists(get_real_path()."app/Runtime/lang.js"))
{			
		$str = "var LANG = {";
		foreach($lang as $k=>$lang_row)
		{
			$str .= "\"".$k."\":\"".str_replace("nbr","\\n",addslashes($lang_row))."\",";
		}
		$str = substr($str,0,-1);
		$str .="};";
		@file_put_contents(get_real_path()."app/Runtime/lang.js",$str);
}

if(app_conf("SHOP_OPEN")==0)
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SHOP_CLOSE']);
	$GLOBALS['tmpl']->assign("html",app_conf("SHOP_CLOSE_HTML"));
	$GLOBALS['tmpl']->display("shop_close.html");
	exit;
}


//会员自动登录及输出
$cookie_uname = es_cookie::get("user_name")?es_cookie::get("user_name"):'';
$cookie_upwd = es_cookie::get("user_name")?es_cookie::get("user_pwd"):'';
if($cookie_uname!=''&&$cookie_upwd!=''&&!$_SESSION['user_info'])
{
	require_once APP_ROOT_PATH."system/libs/user.php";
	auto_do_login_user($cookie_uname,$cookie_upwd);
}
$user_info = $_SESSION['user_info'];


if($user_info)
{
	$GLOBALS['tmpl']->assign("user_info",$user_info);
	//输出会员菜单
	$user_menu = array(
		'uc_coupon'	=>	array('name'=>sprintf($GLOBALS['lang']['UC_COUPON'],app_conf("COUPON_NAME")),'url'=>url_pack("uc_coupon#index")),
		'uc_order'	=>	array('name'=>$GLOBALS['lang']['UC_ORDER'],'url'=>url_pack("uc_order#index")),
		'uc_money'	=>	array('name'=>$GLOBALS['lang']['UC_MONEY'],'url'=>url_pack("uc_money#index")),
		'uc_account'	=>	array('name'=>$GLOBALS['lang']['UC_ACCOUNT'],'url'=>url_pack("uc_account#index")),
		'uc_invite'	=>	array('name'=>$GLOBALS['lang']['UC_INVITE'],'url'=>url_pack("uc_invite#index"))
	);
	$uc_file = basename($_SERVER['PHP_SELF']);
	$key = explode(".php",$uc_file);
	$key = $key[0];
	foreach($user_menu as $k=>$v)
	{
		if($key == $k)
		{
			$user_menu[$k]['act'] = 1;
		}
		else
		{
			$user_menu[$k]['act'] = 0;
		}
	}
	
	$GLOBALS['tmpl']->assign("user_menu",$user_menu);
}



//保存返利的cookie
if($_REQUEST['r'])
{
	$rid = intval(base64_decode($_REQUEST['r']));
	$ref_uid = intval($GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."user where id = ".intval($rid)));
	es_cookie::set("REFERRAL_USER",intval($ref_uid));
}
else
{
	//获取存在的推荐人ID
	$ref_uid = intval($GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."user where id = ".intval(es_cookie::get("REFERRAL_USER"))));
}

//开始输出友情链接

$f_link_group = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."link_group where is_effect = 1 order by sort desc");
foreach($f_link_group as $k=>$v)
{
	$g_links = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."link where is_effect = 1 and show_index = 1 and group_id = ".$v['id']." order by sort desc");
	if($g_links)
	{
		foreach($g_links as $kk=>$vv)
		{
			if(substr($vv['url'],0,7)=='http://')
			{
				$g_links[$kk]['url'] = str_replace("http://","",$vv['url']);
			}
		}
		$f_link_group[$k]['links'] = $g_links;
	}
	else
	unset($f_link_group[$k]);
}
$GLOBALS['tmpl']->assign("f_link_data",$f_link_group);

//每小时清空一次购物车
$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where ".get_gmtime()." - update_time > 3600");

?>