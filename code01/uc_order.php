<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/uc.php';
require './app/Lib/message.php';
require './app/Lib/side.php'; 

if($_REQUEST['act']=='index')
{

	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

	$result = get_user_order($limit,$user_info['id']);
	
	$GLOBALS['tmpl']->assign("list",$result['list']);
	$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	

	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_ORDER']);
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_order_index.html");
	$GLOBALS['tmpl']->display("uc.html");	
}
elseif($_REQUEST['act']=='view')
{
	$id = intval($_REQUEST['id']);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and is_delete = 0");
	if(!$order_info)
	{
		showErr($GLOBALS['lang']['INVALID_ORDER_DATA']);
	}
	else
	{
		$order_info['region_lv1'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."delivery_region where id = ".$order_info['region_lv1']);
		$order_info['region_lv2'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."delivery_region where id = ".$order_info['region_lv2']);
		$order_info['region_lv3'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."delivery_region where id = ".$order_info['region_lv3']);
		$order_info['region_lv4'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."delivery_region where id = ".$order_info['region_lv4']);		
		$order_info['deal_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_info['id']);
		$order_info['payment'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where id = ".$order_info['payment_id']);
	}
	$GLOBALS['tmpl']->assign("order_info",$order_info);
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_ORDER_VIEW']);
	
	//以下关于订单留言的输出
	$rel_table = 'deal_order';
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
	
	$message_group = array($GLOBALS['lang']['ORDER_TYPE_1'],$GLOBALS['lang']['ORDER_TYPE_2'],$GLOBALS['lang']['ORDER_TYPE_3']);
	$GLOBALS['tmpl']->assign("message_group",$message_group);
	//end订单留言
	
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_order_view.html");
	$GLOBALS['tmpl']->display("uc.html");	
}
elseif($_REQUEST['act']=='modify')
{
	$id = intval($_REQUEST['id']);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and is_delete = 0 and pay_status <> 2 and order_status <> 1");
	if(!$order_info)
	{
		showErr($GLOBALS['lang']['INVALID_ORDER_DATA']);
	}	
	if($order_info['type']==1)
	{
		app_redirect(url_pack("uc_money#incharge"));
	}
	$GLOBALS['tmpl']->assign('order_info',$order_info);	
	$cart_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_info['id']);
	
			//输出购物车内容
			$GLOBALS['tmpl']->assign("cart_list",$cart_list);
			$GLOBALS['tmpl']->assign('total_price',$order_info['deal_total_price']);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_ORDER_MODIFY']);		

			$is_delivery = 0;
			foreach($cart_list as $k=>$v)
			{
				if($GLOBALS['db']->getOneCached("select is_delivery from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
				{
					$is_delivery = 1;
					break;
				}
			}
			
			if($is_delivery)
			{
				//输出配送方式
				$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$user_info['id']);
				$GLOBALS['tmpl']->assign("consignee_id",intval($consignee_id));
			}
			
			//配送方式由ajax由 consignee 中的地区动态获取
			
			//输出支付方式
			$payment_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."payment where is_effect = 1 order by sort desc");
			
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
				if($v['class_name']=='Voucher') //禁用代金券支付
				{
					unset($payment_list[$k]);
				}
			}
			$GLOBALS['tmpl']->assign("payment_list",$payment_list);
			
			$GLOBALS['tmpl']->assign("is_delivery",$is_delivery);
			//购物车检测页
			$GLOBALS['tmpl']->display("cart_check.html");
}
elseif($_REQUEST['act']=='arrival')
{
	$delivery_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where id = ".intval($_REQUEST['id']));
	if(!$delivery_notice)
	{
		showErr($GLOBALS['lang']['INVALID_DELIVERY_DATA']);
	}
	else
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."delivery_notice set is_arrival=1,arrival_time=".get_gmtime()." where id = ".intval($_REQUEST['id']));
		showSuccess($GLOBALS['lang']['CONFIRM_ARRIVAL_SUCCESS']);
	}
}
elseif($_REQUEST['act']=='del')
{
	$id = intval($_REQUEST['id']);
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set is_delete = 1,order_status = 1 where id = ".$id." and user_id = ".intval($user_info['id'])." and pay_status = 0");
	$rs = $GLOBALS['db']->affected_rows();
	if($rs)
	{
		showSuccess($GLOBALS['lang']['CANCEL_ORDER_SUCCESS']);
	}
	else
	{
		showErr($GLOBALS['lang']['CANCEL_ORDER_FAILED']);
	}
	
}

?>