<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'支付宝支付',
	'alipay_partner'	=>	'合作者身份ID',
	'alipay_account'	=>	'支付宝帐号',
	'alipay_key'		=>	'校验码',
	'alipay_service'	=>	'接口方式',
	'alipay_service_0'	=>	'使用标准双接口',
	'alipay_service_1'	=>	'担保交易接口',
	'alipay_service_2'	=>	'即时到帐接口',
	'GO_TO_PAY'	=>	'前往支付宝在线支付',
	'VALID_ERROR'	=>	'支付验证失败',
	'PAY_FAILED'	=>	'支付失败',
);
$config = array(
	'alipay_partner'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //合作者身份ID
	'alipay_account'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //支付宝帐号: 
	'alipay_key'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //校验码
	'alipay_service'	=>	array(
		'INPUT_TYPE'	=>	'1',
		'VALUES'	=> 	array(0,1,2)
	),
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Alipay';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    $module['reg_url'] = 'https://www.alipay.com/himalayas/practicality_customer.htm?customer_external_id=C4393319516691172112&market_type=from_agent_contract&pro_codes=61F99645EC0DC4380ADE569DD132AD7A';
    return $module;
}

// 余额支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Alipay_payment implements payment {

	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order_sn = $GLOBALS['db']->getOneCached("select order_sn from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
		$agent = 'C4335319945672464113';
		
		$data_return_url = get_domain().APP_ROOT.'/payment.php?act=return&class_name=Alipay';
		$data_notify_url = get_domain().APP_ROOT.'/payment.php?act=notify&class_name=Alipay';

		$real_method = $payment_info['config']['alipay_service'];

        switch ($real_method){
            case '0':
                $service = 'trade_create_by_buyer';
                break;
            case '1':
                $service = 'create_partner_trade_by_buyer';
                break;
            case '2':
                $service = 'create_direct_pay_by_user';
                break;
        }	
		
		
        $parameter = array(
            'agent'             => $agent,
            'service'           => $service,
            'partner'           => $payment_info['config']['alipay_partner'],
            //'partner'           => ALIPAY_ID,
            '_input_charset'    => 'utf-8',
            'notify_url'        => $data_notify_url,
            'return_url'        => $data_return_url,
            /* 业务参数 */
            'subject'           => $order_sn,
            'out_trade_no'      => $payment_notice['notice_sn'], 
            'price'             => $money,
            'quantity'          => 1,
            'payment_type'      => 1,
            /* 物流参数 */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
            /* 买卖双方信息 */
            'seller_email'      => $payment_info['config']['alipay_account']
        );
        
        ksort($parameter);
        reset($parameter);

        $param = '';
        $sign  = '';

        foreach ($parameter AS $key => $val)
        {
        	$param .= "$key=" .urlencode($val). "&";
            $sign  .= "$key=$val&";
        }

        $param = substr($param, 0, -1);
        $sign  = substr($sign, 0, -1). $payment_info['config']['alipay_key'];
        $sign_md5 = md5($sign);

		
		$payLinks = '<a onclick="window.open(\'https://www.alipay.com/cooperate/gateway.do?'.$param. '&sign='.$sign_md5.'&sign_type=MD5\')" href="javascript:;"><input type="submit" class="paybutton" name="buy" value="'.$GLOBALS['payment_lang']['GO_TO_PAY'].'"/></a>';

    	if(!empty($payment_info['logo']))
		{
			$payLinks = '<a href="https://www.alipay.com/cooperate/gateway.do?'.$param. '&sign='.$sign_md5.'&sign_type=MD5" target="_blank" class="payLink"><img src='.APP_ROOT.$payment_info['logo'].' style="border:solid 1px #ccc;" /></a><div class="blank"></div>'.$payLinks;
		}
		
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
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Alipay'");  
    	$payment['config'] = unserialize($payment['config']);
    	
    	
        /* 检查数字签名是否正确 */
        ksort($request);
        reset($request);
	
        foreach ($request AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code' && $key!='class_name' && $key!='act' )
            {
                $sign .= "$key=$val&";
            }
        }

        $sign = substr($sign, 0, -1) . $payment['config']['alipay_key'];

		if (md5($sign) != $request['sign'])
        {
            showErr($GLOBALS['payment_lang']["VALID_ERROR"]);
        }
		
        $payment_notice_sn = $request['out_trade_no'];
        
    	$money = $request['total_fee'];


		if ($request['trade_status'] == 'TRADE_SUCCESS' || $request['trade_status'] == 'TRADE_FINISHED' || $request['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
			
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
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
		$return_res = array(
			'info'=>'',
			'status'=>false,
		);
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Alipay'");  
    	$payment['config'] = unserialize($payment['config']);
    	
    	
        /* 检查数字签名是否正确 */
        ksort($request);
        reset($request);
	
        foreach ($request AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code' && $key!='class_name' && $key!='act' )
            {
                $sign .= "$key=$val&";
            }
        }

        $sign = substr($sign, 0, -1) . $payment['config']['alipay_key'];

		if (md5($sign) != $request['sign'])
        {
            echo '0';
        }
		
        $payment_notice_sn = $request['out_trade_no'];
        
    	$money = $request['total_fee'];


		if ($request['trade_status'] == 'TRADE_SUCCESS' || $request['trade_status'] == 'TRADE_FINISHED' || $request['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
			require_once APP_ROOT_PATH."system/libs/cart.php";
			$rs = payment_paid($payment_notice['id']);						
			if($rs)
			{				
				order_paid($payment_notice['order_id']);
				echo '1';
			}
			else
			{
				echo '0';
			}
			
		}else{
		   echo '0';
		}   
	}
	
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where class_name='Alipay'");
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