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

if($_REQUEST['act']=='pay')
{
	$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
	if($payment_notice)
	{
		if($payment_notice['is_paid'] == 0)
		{
			$payment_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where id = ".$payment_notice['payment_id']);
			$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			if($order['pay_status']==2)
			{
				app_redirect(url_pack("payment#done",$order['id']));
				exit;
			}
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			$payment_code = $payment_object->get_payment_code($payment_notice['id']);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_NOW']);
			$GLOBALS['tmpl']->assign("payment_code",$payment_code);
			$GLOBALS['tmpl']->assign("order",$order);
			$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
			if(intval($_REQUEST['check'])==1)
			{
				$GLOBALS['tmpl']->assign("error",$GLOBALS['lang']['PAYMENT_NOT_PAID_RENOTICE']);
			}
			$GLOBALS['tmpl']->display("payment_pay.html");
		}
		else
		{
			$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			if($order['pay_status']==2)
			{
				app_redirect(url_pack("payment#done",$order['id']));
			}
			else
			showSuccess($GLOBALS['lang']['NOTICE_PAY_SUCCESS'],0,APP_ROOT."/");
		}
	}
	else
	{
		showErr($GLOBALS['lang']['NOTICE_SN_NOT_EXIST'],0,APP_ROOT."/");
	}
}
elseif($_REQUEST['act']=='done')
{
	$order_id = intval($_REQUEST['id']);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (select distinct deal_id from ".DB_PREFIX."deal_order_item where order_id = ".$order_id.")");
	$GLOBALS['tmpl']->assign("order_info",$order_info);
	$GLOBALS['tmpl']->assign("order_deals",$order_deals);
	$is_coupon = 0;
	foreach($order_deals as $k=>$v)
	{
		if($v['is_coupon'] == 1&&$v['buy_status']>0)
		{
			$is_coupon = 1;
			break;
		}
	}
	$GLOBALS['tmpl']->assign("is_coupon",$is_coupon);
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
	$GLOBALS['tmpl']->display("payment_done.html");
}
elseif($_REQUEST['act']=='incharge_done')
{
	$order_id = intval($_REQUEST['id']);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	//$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (select distinct deal_id from ".DB_PREFIX."deal_order_item where order_id = ".$order_id.")");
	$GLOBALS['tmpl']->assign("order_info",$order_info);
	//$GLOBALS['tmpl']->assign("order_deals",$order_deals);

	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
	$GLOBALS['tmpl']->display("payment_done.html");
}
elseif($_REQUEST['act']=='return')
{
	//支付跳转返回页
	$class_name = $_REQUEST['class_name'];
	$payment_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
	if($payment_info)
	{
		require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
		$payment_class = $payment_info['class_name']."_payment";
		$payment_object = new $payment_class();
		$payment_code = $payment_object->response($_REQUEST);
	}
	else
	{
		showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);
	}
}
elseif($_REQUEST['act']=='notify')
{
	//支付跳转返回页
	$class_name = $_REQUEST['class_name'];
	$payment_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
	if($payment_info)
	{
		require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
		$payment_class = $payment_info['class_name']."_payment";
		$payment_object = new $payment_class();
		$payment_code = $payment_object->notify($_REQUEST);
	}
	else
	{
		showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);
	}
}
elseif($_REQUEST['act']=='tip')
{
	$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
	$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
	$GLOBALS['tmpl']->display("payment_tip.html");
}
?>