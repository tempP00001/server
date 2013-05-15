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
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	$result = get_invite_list($limit,$user_info['id']);
	
	$GLOBALS['tmpl']->assign("list",$result['list']);
	$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	$total_referral_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."referrals where user_id = ".$user_info['id']." and pay_time > 0");
	$total_referral_score = $GLOBALS['db']->getOne("select sum(score) from ".DB_PREFIX."referrals where user_id = ".$user_info['id']." and pay_time > 0");
	
	$GLOBALS['tmpl']->assign("total_referral_money",$total_referral_money);
	$GLOBALS['tmpl']->assign("total_referral_score",$total_referral_score);
	
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_INVITE']);
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_index.html");
	$GLOBALS['tmpl']->display("uc.html");
}

?>