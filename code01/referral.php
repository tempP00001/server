<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/side.php'; 

$id = intval($_REQUEST['id']);
$deal = get_deal($id);
if(!$deal||$deal['buy_type']==1||$deal['is_referral']==0)
{
	app_redirect(APP_ROOT."/");
}

$_SESSION['before_login'] = $_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:url_pack("deal");
$GLOBALS['tmpl']->assign("deal",$deal);
$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['REFERRAL_PAGE']);
$GLOBALS['tmpl']->display("referral.html");
?>