<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/message.php';
require './app/Lib/deal.php';
require './app/Lib/side.php'; 
require './app/Lib/page.php';

if($_REQUEST['act'] == 'add')
{
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST']);
	}
	if($_REQUEST['content']=='')
	{
		showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY']);
	}
	if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
	{
		showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST']);
	}
	
	$rel_table = $_REQUEST['rel_table'];
	$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
	if(!$message_type)
	{
		showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE']);
	}
	
	$message_group = $_REQUEST['message_group'];

	//添加留言
	$message['title'] = htmlspecialchars(addslashes($_REQUEST['content']));
	$message['content'] = htmlspecialchars(addslashes($_REQUEST['content']));
	if($message_group)
	{
		$message['title']="[".$message_group."]:".$message['title'];
		$message['content']="[".$message_group."]:".$message['content'];
	}
	
	$message['create_time'] = get_gmtime();
	$message['rel_table'] = $rel_table;
	$message['rel_id'] = $_REQUEST['rel_id'];
	$message['user_id'] = intval($GLOBALS['user_info']['id']);
	$message['city_id'] = $deal_city['id'];
	if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
	{
		$message_effect = 0;
	}
	else
	{
		$message_effect = $message_type['is_effect'];
	}
	$message['is_effect'] = $message_effect;
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);
	showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS']);
	
}
else
{
	$rel_table = $_REQUEST['act'];
	$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
	if(!$message_type||$message_type['is_fix']==0)
	{
		$message_type_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."message_type where is_fix = 0 order by sort desc");
		if(!$message_type_list)
		{
			showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE']);
		}
		else
		{
			if(!$message_type)
			$message_type = $message_type_list[0];
			foreach($message_type_list as $k=>$v)
			{
				if($v['type_name'] == $message_type['type_name'])
				{
					$message_type_list[$k]['current'] = 1;
				}
				else
				{
					$message_type_list[$k]['current'] = 0;
				}
			}
			$GLOBALS['tmpl']->assign("message_type_list",$message_type_list);
		}
	}
	$rel_table = $message_type['type_name'];
	$condition = '';	
	$id = intval($_REQUEST['id']);
	if($id > 0 && $rel_table == 'deal')
	{
		$deal_info = $GLOBALS['cache']->get("CACHE_DEAL_".$id);
		if($deal_info === false)
		{
				$deal_info = get_deal($id);
				$GLOBALS['cache']->set("CACHE_DEAL_".$id,$deal_info);
		}
		if($deal_info['buy_type']==0)
		$GLOBALS['tmpl']->assign("deal",$deal_info);
	}
	if($id>0)
	$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;
	else
	$condition = "rel_table = '".$rel_table."'";

	if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
	{
		$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
	}
	else 
	{
		if($message_type['is_effect']==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
	}
	//message_form 变量输出
	$GLOBALS['tmpl']->assign("post_title",$message_type['show_name']);
	$GLOBALS['tmpl']->assign("page_title",$message_type['show_name']);
	$GLOBALS['tmpl']->assign('rel_id',$id);
	$GLOBALS['tmpl']->assign('rel_table',$rel_table);
	
	if(!$GLOBALS['user_info'])
	{
		$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url_pack("user#login"),url_pack("user#register")));
	}
	
	//分页
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

	$message = get_message_list($limit,$condition);
	
	$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	$GLOBALS['tmpl']->assign("message_list",$message['list']);
	$GLOBALS['tmpl']->display("message.html");
}
?>