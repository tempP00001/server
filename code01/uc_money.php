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
	$result = get_user_log($limit,$user_info['id']);
	
	$GLOBALS['tmpl']->assign("list",$result['list']);
	$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MONEY']);
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_index.html");
	$GLOBALS['tmpl']->display("uc.html");
}
elseif($_REQUEST['act']=='incharge')
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MONEY_INCHARGE']);
	
	//输出支付方式
	$payment_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."payment where is_effect = 1 and class_name <> 'Account' and class_name <> 'Voucher' and online_pay = 1 order by sort desc");			
	foreach($payment_list as $k=>$v)
	{
		$directory = APP_ROOT_PATH."system/payment/";
		$file = $directory. '/' .$v['class_name']."_payment.php";
		if(file_exists($file))
		{
			require_once($file);
			$payment_class = $v['class_name']."_payment";
			$payment_object = new $payment_class();
			$payment_list[$k]['display_code'] = $payment_object->get_display_code();
					
		}
		else
		{
			unset($payment_list[$k]);
		}
	}
	$GLOBALS['tmpl']->assign("payment_list",$payment_list);
			
	//输出充值订单
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

	$result = get_user_incharge($limit,$user_info['id']);
	
	$GLOBALS['tmpl']->assign("list",$result['list']);
	$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_incharge.html");
	$GLOBALS['tmpl']->display("uc.html");
}
elseif($_REQUEST['act']=='incharge_done')
{
	$payment_id = intval($_REQUEST['payment']);
	$money = floatval($_REQUEST['money']);
	if($money<=0)
	{
		showErr($GLOBALS['lang']['PLEASE_INPUT_CORRECT_INCHARGE']);
	}
	
	$payment_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where id = ".$payment_id);
	if(!$payment_info)
	{
		showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT']);
	}
	//开始生成订单
	$now = get_gmtime();
	$order['type'] = 1; //充值单
	$order['user_id'] = $user_info['id'];
	$order['create_time'] = $now;
	$order['total_price'] = $money + $payment_info['fee_amount'];
	$order['deal_total_price'] = $money;
	$order['pay_amount'] = 0;  
	$order['pay_status'] = 0;  
	$order['delivery_status'] = 5;  
	$order['order_status'] = 0; 
	$order['payment_id'] = $payment_id;
	$order['payment_fee'] = $payment_info['fee_amount'];
	

	do
	{
		$order['order_sn'] = to_date(get_gmtime(),"Ymdhis").rand(100,999);
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT'); 
		$order_id = intval($GLOBALS['db']->insert_id());
	}while($order_id==0);
	
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$payment_notice_id = make_payment_notice($order['total_price'],$order_id,$payment_info['id']);
	//创建支付接口的付款单

	$rs = order_paid($order_id);  
	if($rs)
	{
		app_redirect(url_pack("payment#incharge_done",$order_id)); //充值支付成功
	}
	else
	{
		app_redirect(url_pack("payment#pay",$payment_notice_id)); 
	}

}
elseif($_REQUEST['act']=='carry')
{
	require './app/Lib/message.php';
	//以下关于提现留言的输出
	$rel_table = 'tx';
	$id = 0;
	$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
	$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;

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
	$GLOBALS['tmpl']->assign('rel_id',$id);
	$GLOBALS['tmpl']->assign('rel_table',$rel_table);

	
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
	//end订单留言
	
	$GLOBALS['tmpl']->assign("page_title",$message_type['show_name']);
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_carry.html");
	$GLOBALS['tmpl']->display("uc.html");
}


?>