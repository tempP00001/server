<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';

// 加载子地区option
if($_REQUEST['act'] == 'load_region')
{
	$pid = intval($_REQUEST['id']);
	$region_html = "<option value ='0'>=".$GLOBALS['lang']['SELECT_PLEASE']."=</option>";
	if($pid != 0)
	{
		$regions = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$pid); 

		foreach($regions as $k=>$v)
		{
			$region_html .= "<option value ='".$v['id']."'>".$v['name']."</option>";
		} 
	}
	header("Content-Type:text/html; charset=utf-8");
	echo $region_html;
}

// 加载指定的收货人
if($_REQUEST['act']=='load_consignee')
{
	$consignee_id = intval($_REQUEST['id']);
	if($consignee_id>0)
	{
		$consignee_info = $GLOBALS['cache']->get("CONSIGNEE_INFO_".$consignee_id);
		if($consignee_info === false)
		{
			$consignee_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where id = ".$consignee_id);
			$GLOBALS['cache']->set("CONSIGNEE_INFO_".$consignee_id,$consignee_info);
		}
		
		
		$region_lv1 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
		foreach($region_lv1 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv1'])
			{
				$region_lv1[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
		
		$region_lv2 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv1']);  //二级地址
		foreach($region_lv2 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv2'])
			{
				$region_lv2[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
		
		$region_lv3 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv2']);  //三级地址
		foreach($region_lv3 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv3'])
			{
				$region_lv3[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
		
		$region_lv4 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv3']);  //四级地址
		foreach($region_lv4 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv4'])
			{
				$region_lv4[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv4",$region_lv4);
		
		$GLOBALS['tmpl']->assign("consignee_info",$consignee_info);
	}
	else
	{
		$region_lv1 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
	}
	
	$GLOBALS['tmpl']->display("inc/cart/cart_consignee.html");
}

// 加载针对配送地区相对应的配送方式
if($_REQUEST['act']=='load_delivery')
{
	$region_id = intval($_REQUEST['id']);
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$delivery_list = load_support_delivery($region_id);
	$GLOBALS['tmpl']->assign("delivery_list",$delivery_list);
	$GLOBALS['tmpl']->display("inc/cart/cart_delivery.html");
}

// ajax动态载入购买总计
if($_REQUEST['act']=='count_buy_total')
{
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$region_id = intval($_REQUEST['region_id']); //配送地区
	$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
	$account_money =  floatval($_REQUEST['account_money']); //余额
	$ecvsn = $_REQUEST['ecvsn']?$_REQUEST['ecvsn']:'';
	$ecvpassword = $_REQUEST['ecvpassword']?$_REQUEST['ecvpassword']:'';
	$payment = intval($_REQUEST['payment']);
	$all_account_money = intval($_REQUEST['all_account_money']);
	
	$user_id = intval($GLOBALS['user_info']['id']);
	$session_id = session_id();
	$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cart where session_id='".$session_id."' and user_id=".$user_id);
	
	$result = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list);
	

	$GLOBALS['tmpl']->assign("result",$result);
	$html = $GLOBALS['tmpl']->fetch("inc/cart/cart_total.html");
	$data = $result;
	$data['html'] = $html;
	
	ajax_return($data);
	
}


// ajax动态载入订单购买总计
if($_REQUEST['act']=='count_order_total')
{
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$order_id = intval($_REQUEST['id']);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	
	
	$region_id = intval($_REQUEST['region_id']); //配送地区
	$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
	$account_money =  floatval($_REQUEST['account_money']); //余额

	$ecvsn = $_REQUEST['ecvsn']?$_REQUEST['ecvsn']:'';
	$ecvpassword = $_REQUEST['ecvpassword']?$_REQUEST['ecvpassword']:'';
	
	$payment = intval($_REQUEST['payment']);
	$all_account_money = intval($_REQUEST['all_account_money']);
	
	
	$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
	
	$result = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$order_info['account_money'],$order_info['ecv_money']);
		
	$GLOBALS['tmpl']->assign("result",$result);
	$html = $GLOBALS['tmpl']->fetch("inc/cart/cart_total.html");
	$data = $result;
	$data['html'] = $html;
	
	ajax_return($data);
	
}

if($_REQUEST['act']=='get_supplier_location')
{
	$id = intval($_REQUEST['id']);
	$supplier_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier_location where id = ".$id);
	$GLOBALS['tmpl']->assign("supplier_address_info",$supplier_info);
	$html = $GLOBALS['tmpl']->fetch("inc/sp_location.html");
	header("Content-Type:text/html; charset=utf-8");
	echo $html;
}

if($_REQUEST['act']=='verify_ecv')
{
	$ecvsn = trim($_REQUEST['ecvsn']);
	$ecvpassword = trim($_REQUEST['ecvpassword']);
	$user_id = intval($GLOBALS['user_info']['id']);
	$now = get_gmtime();
	$ecv_sql = "select e.*,et.name from ".DB_PREFIX."ecv as e left join ".
				DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
				$ecvsn."' and e.password = '".
				$ecvpassword."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
				"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
				"and (e.user_id = ".$user_id." or e.user_id = 0)";
	$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
	header("Content-Type:text/html; charset=utf-8");
	if($ecv_data)
	echo "[".$ecv_data['name']."] ".$GLOBALS['lang']['IS_VALID'];
	else
	echo $GLOBALS['lang']['IS_INVALID_ECV'];
}

if($_REQUEST['act']=='check_field')
{
	$field_name = $_REQUEST['field_name'];
	$field_data = $_REQUEST['field_data'];
	require_once APP_ROOT_PATH."system/libs/user.php";
	$res = check_user($field_name,$field_data);
	$result = array("status"=>1,"info"=>'');
	if($res['status'])
	{
		ajax_return($result);
	}
	else
	{
		$error = $res['data'];		
		if(!$error['field_show_name'])
		{
				$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
		}
		if($error['error']==EMPTY_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
		}
		if($error['error']==FORMAT_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
		}
		if($error['error']==EXIST_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
		}
		$result['status'] = 0;
		$result['info'] = $error_msg;
		ajax_return($result);
	}
}
?>