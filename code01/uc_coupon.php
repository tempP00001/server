<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/uc.php';

if($_REQUEST['act']=='index')
{
	$status = intval($_REQUEST['id']); //状态过滤 0:全有 1:未使用 2:已使用 3:已过期
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

	$result = get_user_coupon($limit,$user_info['id'],$status);
	
	$GLOBALS['tmpl']->assign("list",$result['list']);
	$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	$GLOBALS['tmpl']->assign("status",$status);
	$GLOBALS['tmpl']->assign("page_title",sprintf($GLOBALS['lang']['UC_COUPON'],app_conf("COUPON_NAME")));
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_coupon_index.html");
	$GLOBALS['tmpl']->display("uc.html");	
}

if($_REQUEST['act'] == 'sms')
{
	if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_COUPON")==1)
	{
		$id = intval($_REQUEST['id']);
		$sms_count = $GLOBALS['db']->getOne("select sms_count from ".DB_PREFIX."deal_coupon where id = ".$id);
		if(app_conf("SMS_COUPON_LIMIT")>$sms_count)
		{
			send_deal_coupon_sms($id);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set sms_count = sms_count + 1 where id = ".$id);
			showSuccess($GLOBALS['lang']['SEND_SUCCESS']);
		}
		else
		showErr($GLOBALS['lang']['SEND_EXCEED_LIMIT']);
	}
	else
	{
		showErr($GLOBALS['lang']['SMS_NOT_ALLOW']);
	}
}

if($_REQUEST['act'] == 'mail')
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_COUPON")==1)
	{
		$id = intval($_REQUEST['id']);
		$mail_count = $GLOBALS['db']->getOne("select mail_count from ".DB_PREFIX."deal_coupon where id = ".$id);
		if(app_conf("MAIL_COUPON_LIMIT")>$mail_count)
		{
			send_deal_coupon_mail($id);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set mail_count = mail_count + 1 where id = ".$id);
			showSuccess($GLOBALS['lang']['SEND_SUCCESS']);
		}
		else
		showErr($GLOBALS['lang']['SEND_EXCEED_LIMIT']);
	}
	else
	{
		showErr($GLOBALS['lang']['MAIL_NOT_ALLOW']);
	}
}

if($_REQUEST['act']=='view')
{
	$coupon_id = intval($_REQUEST['id']);
	$location_id = intval($_REQUEST['location_id']);
	$coupon_data = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id." and is_valid = 1 and is_delete = 0 and user_id = ".intval($GLOBALS['user_info']['id']));
	if($coupon_data)
	{
		$coupon_data['is_new'] = 1;
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_coupon",$coupon_data,'UPDATE','id='.$coupon_data['id']);
		$deal_data = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
		$locations = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($coupon_data['supplier_id']));
		if($location_id==0)
		{
			$location_id = intval($locations[0]['id']);
		}
		if($location_id==0||$GLOBALS['db']->getOneCached("select count(*) from ".DB_PREFIX."supplier_location where id = ".$location_id)==0)
		{
			showErr($GLOBALS['lang']['NO_SUPPLIER_LOCATION']);
		}
		else
		{
			$location_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier_location where id=".$location_id);
		}
		
		$GLOBALS['tmpl']->assign("coupon",$coupon_data);
		$GLOBALS['tmpl']->assign("location",$location_info);
		$tmpl_content = get_coupon_content($coupon_id,$location_id);
		$GLOBALS['tmpl']->assign("html",$tmpl_content);
		$GLOBALS['tmpl']->assign("supplier_location",$locations);
		$GLOBALS['tmpl']->assign("page_title",$deal_data['name'].$GLOBALS['DEAL_COUPON_PRINT']);
		$GLOBALS['tmpl']->display("coupon_print.html");
	}
	else
	{
		showErr($GLOBALS['lang']['INVALID_ACCESS']);
	}
}

?>