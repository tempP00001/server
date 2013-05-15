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
require './app/Lib/page.php';

//获取当前页的团购商品列表
//分页
$page = intval($_REQUEST['p']);
if($page==0)
$page = 1;
$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
//分类
$cate_id = intval($_REQUEST['id']);


$act = trim($_REQUEST['act']);
//显示的类型
if($act == 'history')
{
	$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['DEAL_HISTORY_LIST']);
	$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['DEAL_HISTORY_LIST']);
	$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['DEAL_HISTORY_LIST']);
	$type = array(DEAL_HISTORY);
}
elseif($act == 'notice')
{
	$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['DEAL_NOTICE_LIST']);
	$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['DEAL_NOTICE_LIST']);
	$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['DEAL_NOTICE_LIST']);
	$type = array(DEAL_NOTICE);
}
else
{
	$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['DEAL_LIST']);
	$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['DEAL_LIST']);
	$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['DEAL_LIST']);
	$type = array(DEAL_ONLINE,DEAL_HISTORY);
}

if(app_conf("SHOW_DEAL_CATE")==1)
{
	//输出分类
	$deal_cates_db = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
	$deal_cates = array();
	if($act=='history'||$act=='notice')
		$url = url_pack("deals#".$act);
		else 
		$url = url_pack("deals");
	$deal_cates[] = array('id'=>0,'name'=>$GLOBALS['lang']['ALL'],'current'=>$cate_id==0?1:0,'url'=>$url);	
	foreach($deal_cates_db as $k=>$v)
	{		
		if($cate_id==$v['id'])
		$v['current'] = 1;
		if($act=='history'||$act=='notice')
		$v['url'] = url_pack("deals#".$act,$v['id']);
		else 
		$v['url'] = url_pack("deals",$v['id']);
		$deal_cates[] = $v;
	}

	$GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
}

$deals = get_deal_list($limit,$cate_id,0,$type,' buy_type=0 ');


$GLOBALS['tmpl']->assign("deals",$deals['list']);


$page = new Page($deals['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
$p  =  $page->show();
$GLOBALS['tmpl']->assign('pages',$p);

require './app/Lib/side.php';  //读取边栏信息,需放在deal数据的分配之后

if($deals['list'])
$GLOBALS['tmpl']->display("deals.html");
else
$GLOBALS['tmpl']->display("no_deal.html");
?>