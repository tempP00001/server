<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';

if($_REQUEST['act']=='verify')
{
	$account_id = intval($_SESSION['account_info']['id']);
	$account_data = $GLOBALS['db']->getRowCached("select s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
	$GLOBALS['tmpl']->assign("account_data",$account_data);
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['VERIFY_COUPON']);
	$GLOBALS['tmpl']->display("coupon_verify.html");
}
elseif($_REQUEST['act']=='check_coupon')
{
	$now = get_gmtime();
	$sn = htmlspecialchars(addslashes($_REQUEST['coupon_sn']));
	$coupon_data = $GLOBALS['db']->getRow("select doi.name as name,c.sn as sn from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.sn = '".$sn."' and c.is_valid = 1 and c.is_delete = 0 and c.confirm_time = 0 and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>".$now.")");
	header("Content-Type:text/html; charset=utf-8");
	if($coupon_data)
	{
		echo sprintf($GLOBALS['lang']['COUPON_IS_VALID'],$coupon_data['name'],$coupon_data['sn']);
	}
	else
	{
		echo $GLOBALS['lang']['COUPON_INVALID'];
	}
}
elseif($_REQUEST['act']=='use_coupon')
{
	if(intval($_SESSION['account_info']['id'])==0)
	{
		$result['status'] = 2;
		ajax_return($result);
	}
	else
	{
		$now = get_gmtime();
		$sn = htmlspecialchars(addslashes($_REQUEST['coupon_sn']));
		$pwd = htmlspecialchars(addslashes($_REQUEST['coupon_pwd']));
		$supplier_id = intval($_SESSION['account_info']['supplier_id']);
		$coupon_data = $GLOBALS['db']->getRow("select c.id as id,doi.name as name,c.sn as sn,c.supplier_id as supplier_id,c.confirm_time as confirm_time from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.sn = '".$sn."' and c.password = '".$pwd."' and c.is_valid = 1 and c.is_delete = 0  and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>".$now.")"); 
		if($coupon_data)
		{
			if($coupon_data['supplier_id']!=$supplier_id)
			{
				$result['status'] = 0;
				$result['msg'] = $GLOBALS['lang']['COUPON_INVALID_SUPPLIER'];
				ajax_return($result);
			}
			elseif($coupon_data['confirm_time'] > 0)
			{
				$result['status'] = 0;
				$result['msg'] = sprintf($GLOBALS['lang']['COUPON_INVALID_USED'],to_date($coupon_data['confirm_time']));
				ajax_return($result);
			}
			else
			{
				//开始确认
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set confirm_account = ".intval($_SESSION['account_info']['id']).",confirm_time=".$now." where id = ".intval($coupon_data['id']));
				$result['status'] = 1;
				$result['msg'] = sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));
				ajax_return($result);
			}
		}
		else
		{				
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['COUPON_INVALID'];
			ajax_return($result);
		}
	}
}
elseif($_REQUEST['act']=='supplier_login')
{
		$tmpl->assign("page_title",$GLOBALS['lang']['SUPPLIER_LOGIN']);
		$tmpl->display("supplier_login.html");
}
elseif($_REQUEST['act']=='ajax_supplier_login')
{
	$tmpl->display("inc/ajax_supplier_login.html");
}
elseif($_REQUEST['act']=='loginout')
{
	unset($_SESSION['account_info']);
	app_redirect(url_pack("coupon#verify"));
}
elseif($_REQUEST['act'] == 'supplier_dologin')
{
	if(check_ipop_limit(get_client_ip(),"supplier_dologin",intval(app_conf("SUBMIT_DELAY"))))
	{
		$account_name = htmlspecialchars(addslashes($_REQUEST['account_name']));
		$account_password = htmlspecialchars(addslashes($_REQUEST['account_password']));
		$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and account_password = '".md5($account_password)."' and is_effect = 1 and is_delete = 0");
		if($account)
		{
			$_SESSION['account_info'] = $account;
			$result['status'] = 1;
			ajax_return($result);
		}
		else
		{
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['SUPPLIER_LOGIN_FAILED'];
			ajax_return($result);
		}
	}
	else
	{
		$result['status'] = 0;
		$result['msg'] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
		ajax_return($result);
	}
}
elseif($_REQUEST['act']=='modify_pwd')
{
	if(intval($_SESSION['account_info']['id'])==0)
	{
		showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#verify"));		
	}
	
	$tmpl->assign("page_title",$GLOBALS['lang']['SUPPLIER_MODIFY_PWD']);
	$tmpl->display("supplier_password.html");
}
elseif($_REQUEST['act']=='do_modify_password')
{
	if(intval($_SESSION['account_info']['id'])==0)
	{
		showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#verify"));		
	}
	$new_pwd = htmlspecialchars(addslashes(trim($_REQUEST['account_new_password'])));
	$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set account_password = '".md5($new_pwd)."' where id = ".intval($_SESSION['account_info']['id']));
	showSuccess($GLOBALS['lang']['PASSWORD_MODIFY_SUCCESS'],0,url_pack("coupon#verify"));	
}
else
showErr($GLOBALS['lang']['INVALID_ACCESS'],0,APP_ROOT);

?>