<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'网银在线',
	'chinabank_account'	=>	'商户编号',
	'chinabank_key'	=>	'商户密钥',
	'VALID_ERROR'	=>	'支付验证失败',
	'PAY_FAILED'	=>	'支付失败',
	'GO_TO_PAY'	=>	'前往网银在线支付',
);
$config = array(
	'chinabank_account'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //商户编号
	'chinabank_key'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //商户密钥

);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Chinabank';

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
class Chinabank_payment implements payment {

	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order_sn = $GLOBALS['db']->getOneCached("select order_sn from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);

		
		$data_vid           = trim($payment_info['config']['chinabank_account']);
        $data_orderid       = $payment_notice['id'];
        $data_vamount       = $money;
        $data_vmoneytype    = 'CNY';
        $data_vpaykey       = trim($payment_info['config']['chinabank_key']);
		$data_vreturnurl = get_domain().APP_ROOT.'/payment.php?act=return&class_name=Chinabank';

        $MD5KEY =$data_vamount.$data_vmoneytype.$data_orderid.$data_vid.$data_vreturnurl.$data_vpaykey;
        $MD5KEY = strtoupper(md5($MD5KEY));

        $payLinks  = '<form style="text-align:center;" method=post action="https://pay3.chinabank.com.cn/PayGate" target="_blank">';
        $payLinks .= "<input type=HIDDEN name='v_mid' value='".$data_vid."'>";
        $payLinks .= "<input type=HIDDEN name='v_oid' value='".$data_orderid."'>";
        $payLinks .= "<input type=HIDDEN name='v_amount' value='".$data_vamount."'>";
        $payLinks .= "<input type=HIDDEN name='v_moneytype'  value='".$data_vmoneytype."'>";
        $payLinks .= "<input type=HIDDEN name='v_url'  value='".$data_vreturnurl."'>";
        $payLinks .= "<input type=HIDDEN name='v_md5info' value='".$MD5KEY."'>";
        $payLinks .= "<input type=HIDDEN name='remark1' value=''>";
		if(!empty($payment_info['logo']))
			$payLinks .= "<input type='image' src='".APP_ROOT.$payment_info['logo']."' style='border:solid 1px #ccc;'><div class='blank'></div>";
			
        $payLinks .= "<input type='submit' class='paybutton' value='".$GLOBALS['payment_lang']['GO_TO_PAY']."'>";
        $payLinks .= "</form>";
        $code = '<div style="text-align:center">'.$payLinks.'</div>';
		$code.="<br /><div style='text-align:center' class='red'>".$GLOBALS['lang']['PAY_TOTAL_PRICE'].":".format_price($money)."</div>";
        return $code;       
        
        
	}
	
	public function response($request)
	{

		$return_res = array(
			'info'=>'',
			'status'=>false,
		);
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Chinabank'");  
    	$payment['config'] = unserialize($payment['config']);
    	
    	
        
    	$v_oid          = trim($request['v_oid']);
        $v_pmode        = trim($request['v_pmode']);
        $v_pstatus      = trim($request['v_pstatus']);
        $v_pstring      = trim($request['v_pstring']);
        $v_amount       = trim($request['v_amount']);
        $v_moneytype    = trim($request['v_moneytype']);
        $remark1        = trim($request['remark1' ]);
        $remark2        = trim($request['remark2' ]);
        $v_md5str       = trim($request['v_md5str' ]);

        /**
         * 重新计算md5的值
         */
        $key            = $payment['config']['chinabank_key'];

        $md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));
		
        //开始初始化参数
        $payment_notice_id = $v_oid;
    	$money = $v_amount;
    	$payment_id = $payment['id'];   
    	

        if ($v_md5str==$md5string&&$v_pstatus == '20')
		{			
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = '".$payment_notice_id."'");
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			require_once APP_ROOT_PATH."system/libs/cart.php";
			$rs = payment_paid($payment_notice['id']);						
			if($rs)
			{
				$rs = order_paid($payment_notice['order_id']);
				if($rs)
				{
					if($order_info['type']==0)
					app_redirect(url_pack("payment#done",$payment_notice['order_id'])); //支付成功
					else
					app_redirect(url_pack("payment#incharge_done",$payment_notice['order_id'])); //支付成功
				}
				else 
				{
					if($order_info['pay_status'] == 2)
					{
						if($order_info['type']==0)
						app_redirect(url_pack("payment#done",$payment_notice['order_id'])); //支付成功
						else
						app_redirect(url_pack("payment#incharge_done",$payment_notice['order_id'])); //支付成功
					}
					else
					app_redirect(url_pack("payment#pay",$payment_notice['id'])); 
				}
			}
			else
			{
				app_redirect(url_pack("payment#pay",$payment_notice['id'])); 
			}
		}else{
		    showErr($GLOBALS['payment_lang']["PAY_FAILED"]);
		}   
	}
	
	public function notify($request)
	{
		return false;
	}
	
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where class_name='Chinabank'");
		if($payment_item)
		{
			$html = "<div style='float:left;'>".
					"<input type='radio' name='payment' value='".$payment_item['id']."' />&nbsp;".
					$payment_item['name'].
					"：</div>";
			if($payment_item['logo']!='')
			{
				$html .= "<div style='float:left; padding-left:10px;'><img src='".APP_ROOT.$payment_item['logo']."' /></div>";
			}
			$html .= "<div style='float:left; padding-left:10px;'>".nl2br($payment_item['description'])."</div>";
			return $html;
		}
		else
		{
			return '';
		}
	}
}
?>