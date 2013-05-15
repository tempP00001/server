<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'余额支付',
	'account_credit'	=>	'帐户余额',
	'use_user_money' =>	'使用余额支付',
	'use_all_money'	=>	'全额支付',
	'USER_ORDER_PAID'	=>	'%s订单付款,付款单号%s'
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Account';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    $module['reg_url'] = '';
    return $module;
}

// 余额支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Account_payment implements payment {
	public function get_payment_code($payment_notice_id)
	{
		$rs = payment_paid($payment_notice_id);
		if($rs)
		{
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
			$order_sn = $GLOBALS['db']->getOneCached("select order_sn from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			require_once APP_ROOT_PATH."system/libs/user.php";
			$msg = sprintf($GLOBALS['payment_lang']['USER_ORDER_PAID'],$order_sn,$payment_notice['notice_sn']);			
			modify_account(array('money'=>"-".$payment_notice['money'],'score'=>0),$payment_notice['user_id'],$msg);
		}
	}
	
	/**
	 * 直接处理付款单
	 * @param unknown_type $payment_notice
	 */
	public function response($payment_notice)
	{
		return false;	
	}
	
	public function notify($request)
	{
		return false;
	}
	
	public function get_display_code()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id." and is_effect = 1 and is_delete = 0");
		
		if($user_info&&$user_info['money']>0)
		{
						
			$html = "<p>".$GLOBALS['payment_lang']['account_credit']."：<strong>".format_price($user_info['money'])."</strong>，".
					$GLOBALS['payment_lang']['use_user_money'].
					" <input type='text' style='width: 50px;' value='' name='account_money' class='f-input' id='account_money'>，".
					"<label><input type='checkbox' checked='checked' id='check-all-money' name='all_account_money'>".
					$GLOBALS['payment_lang']['use_all_money']."</label></p>";
			return $html;
		}
		else
		{
			return '';
		}
	}
}
?>