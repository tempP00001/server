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

//获取当前页的团购商品
$deal = get_deal(0);
if($city_name)
$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE")." - ".$deal_city['name'].$GLOBALS['lang']['SITE']);
else
$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE"));

$GLOBALS['tmpl']->assign("hide_end_title",true);

$GLOBALS['tmpl']->assign("page_keyword",$deal['name']);
$GLOBALS['tmpl']->assign("page_description",$deal['name']);
$GLOBALS['tmpl']->assign("deal",$deal);

//供应商的地址列表
$locations = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." order by is_main desc");
$GLOBALS['tmpl']->assign("locations",$locations);

require_once './app/Lib/side.php';  //读取边栏信息,需放在deal数据的分配之后

$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where deal_id = ".intval($deal['id'])." and is_new = 0 and is_valid = 1 and user_id = ".intval($user_info['id']));
$tmpl->assign("coupon_data",$coupon_data);

if($deal)
$tmpl->display("deal.html");
else
$tmpl->display("no_deal.html");
?>