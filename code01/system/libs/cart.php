<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

// 读取 指定配送地区的  配送方式
// region_id 配送地区ID
function load_support_delivery($region_id)
{
	$region_id =intval($region_id);
	$support_delivery_list = $GLOBALS['cache']->get("SUPPORT_DELIVERY_".$region_id);
	if($support_delivery_list===false)
	{
		$support_delivery_list = array();
		$delivery_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery where is_effect = 1 order by sort desc");
		require_once APP_ROOT_PATH."system/utils/child.php";
		$child = new child("delivery_region");
		
		foreach($delivery_list as $k=>$v)
		{
			//读取相应的支持地区
			$support_ids = array();
			$delivery_items = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_fee where delivery_id = ".$v['id']);
			foreach($delivery_items as $kk=>$vv)
			{
				$sp_ids = $vv['region_ids']; //每条支持地区值
				$sp_ids = explode(",",$sp_ids);
				foreach($sp_ids as $sp_id)
				{
					$tmp_ids = $child->getChildIds($sp_id);
					$tmp_ids[] = $sp_id;
					$support_ids = array_merge($support_ids,$tmp_ids);
				}
			}
			
			if(in_array($region_id,$support_ids)||$v['allow_default'] == 1)
			{				
				$support_delivery_list[] = $v;
			}		
		}
		
		$GLOBALS['cache']->set("SUPPORT_DELIVERY_".$region_id,$support_delivery_list);
	}	
	return $support_delivery_list;
}

//计算购买价格
/**
 * region_id      //配送最终地区
 * delivery_id    //配送方式
 * payment        //支付ID
 * account_money  //支付余额
 * all_account_money  //是否全额支付
 * ecvsn  //代金券帐号
 * ecvpassword  //代金券密码
 * goods_list   //统计的商品列表
 * $paid_account_money 已支付过的余额
 * $paid_ecv_money 已支付过的代金券
 * 
 * 返回 array(
		'total_price'	=>	$total_price,	商品总价
		'pay_price'		=>	$pay_price,     支付费用
		'pay_total_price'		=>	$total_price+$delivery_fee+$payment_fee-$user_discount,  应付总费用
		'delivery_fee'	=>	$delivery_fee,  运费
		'delivery_info' =>  $delivery_info, 配送方式
		'payment_fee'	=>	$payment_fee,   支付手续费
		'payment_info'  =>	$payment_info,  支付方式
		'user_discount'	=>	$user_discount, 会员折扣
		'account_money'	=>	$account_money, 余额支付	
		'ecv_money'		=>	$ecv_money,		代金券金额
		'ecv_data'		=>	$ecv_data,      代金券数据
		'region_info'	=>	$region_info,	地区数据
		'is_delivery'	=>	$is_delivery,   是否要配送
		'return_total_score'	=>	$return_total_score,   购买返积分
		'return_total_money'	=>	$return_total_money    购买返现
		
 */
function count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$paid_account_money = 0,$paid_ecv_money = 0)
{
	//获取商品总价
	//计算运费
	$pay_price = 0;   //支付总价
	$total_price = 0;
	$total_weight = 0;
	$return_total_score = 0;
	$return_total_money = 0;
	$is_delivery = 0;
	foreach($goods_list as $k=>$v)
	{
		$total_price += $v['total_price'];
		
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$v['deal_id']);
		
		if($deal_info['is_delivery'] == 1) //需要配送叠加重量
		{
			$deal_weight = floatval($deal_info['weight']); //团购商品的单位重量
			
			$deal_weight_unit = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."weight_unit where id = ".$deal_info['weight_id']);  //团购的重单单价
			
			$deal_weight = $deal_weight * $deal_weight_unit['rate'];  //转换为为1的重量
			
			$total_weight += ($deal_weight*$v['number']);
			
			$is_delivery = 1;
		}
		
		$return_total_money = $return_total_money + $deal_info['return_money'] * $v['number'];
		$return_total_score = $return_total_score + $deal_info['return_score'] * $v['number'];
	}
	
	$region_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."delivery_region where id = ".intval($region_id));
	$delivery_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."delivery where id = ".intval($delivery_id));
	$delivery_fee = count_delivery_fee($total_weight,$region_id, intval($delivery_info['id']));
	$pay_price = $total_price + $delivery_fee; //加上运费
	
	$pay_price = $pay_price - $paid_account_money - $paid_ecv_money;
	
	//先计算用户等级折扣
	$user_id = intval($GLOBALS['user_info']['id']);
	$group_info = $GLOBALS['db']->getRow("select g.* from ".DB_PREFIX."user as u left join ".DB_PREFIX."user_group as g on u.group_id = g.id where u.id = ".$user_id);
	if($group_info&&$total_price>0)
	$user_discount = $total_price * (1-floatval($group_info['discount']));	
	else
	$user_discount = 0;
	$pay_price = $pay_price - $user_discount; //扣除用户折扣
	
	//余额支付
	$user_money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."user where id = ".$user_id);
	if($all_account_money == 1)
	{
		$account_money = $user_money;
	}

	if($account_money>$user_money)
	$account_money = $user_money;  //余额支付量不能超过帐户余额
	
	//开始计算代金券
	$now = get_gmtime();
	$ecv_sql = "select e.* from ".DB_PREFIX."ecv as e left join ".
				DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
				$ecvsn."' and e.password = '".
				$ecvpassword."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
				"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
				"and (e.user_id = ".$user_id." or e.user_id = 0)";
	$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
	$ecv_money = $ecv_data['money'];
	
	// 当余额 + 代金券 > 支付总额时优先用代金券付款  ,代金券不够付，余额为扣除代金券后的余额
	if($ecv_money + $account_money > $pay_price)
	{
		if($ecv_money >= $pay_price)
		{
			$ecv_use_money = $pay_price;
			$account_money = 0;
		}
		else
		{
			$ecv_use_money = $ecv_money;
			$account_money = $pay_price - $ecv_use_money;
		}
	}
	else
	{
		$ecv_use_money = $ecv_money;
	}

		
    $pay_price = $pay_price - $ecv_use_money - $account_money;

	//支付手续费
	if($payment!=0)
	{
		if($pay_price>0)
		{
			$payment_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where id = ".$payment);
			$payment_fee = $payment_info['fee_amount'];		
			$pay_price = $pay_price + $payment_fee;
		}
	}
	else
	{
		$payment_fee = 0;
	}
	
	//if($account_money<0)$account_money = 0;
	
	$result = array(
		'total_price'	=>	$total_price,
		'pay_price'		=>	$pay_price,
		'pay_total_price'		=>	$total_price+$delivery_fee+$payment_fee-$user_discount,
		'delivery_fee'	=>	$delivery_fee,
		'delivery_info' =>  $delivery_info,
		'payment_fee'	=>	$payment_fee,
		'payment_info'  =>	$payment_info,
		'user_discount'	=>	$user_discount,
		'account_money'	=>	$account_money,
		'ecv_money'		=>	$ecv_money,
		'ecv_data'		=>	$ecv_data,
		'region_info'	=>	$region_info,
		'is_delivery'	=>	$is_delivery,
		'return_total_score'	=>	$return_total_score,
		'return_total_money'	=>	$return_total_money,
		'paid_account_money'	=>	$paid_account_money,
		'paid_ecv_money'	=>	$paid_ecv_money
	);
	
	//以下对促销接口进行实现
	
	$allow_promote = 1; //默认为支持促销接口
		foreach($goods_list as $k=>$v)
		{
			$allow_promote = $GLOBALS['db']->getOneCached("select allow_promote from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			if($allow_promote == 0)
			{
				break;
			}
		}
	if($allow_promote==1)
	{
		$promote_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."promote order by sort desc");
				
		foreach($promote_list as $k=>$v)
		{
					$directory = APP_ROOT_PATH."system/promote/";
					$file = $directory. '/' .$v['class_name']."_promote.php";
					if(file_exists($file))
					{
						require_once($file);
						$promote_class = $v['class_name']."_promote";
						$promote_object = new $promote_class();
						$result = $promote_object->count_buy_total($region_id,
										$delivery_id,
										$payment,
										$account_money,
										$all_account_money,
										$ecvsn,
										$ecvpassword,
										$goods_list,
										$result,
										$paid_account_money,
										$paid_ecv_money);
						
					}
	
		}
	}

			
	return $result;
}

/**
 * 
 * @param $weight  重量
 * @param $region_id  配送地区ID
 * @param $delivery_id  配送方式ID
 * 
 * return delivery_fee  运费
 */
function count_delivery_fee($weight,$region_id,$delivery_id)
{
		$region_id = intval($region_id);
		$delivery_id = intval($delivery_id);
		$delivery_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."delivery where id = ".$delivery_id);
		$delivery_weight_unit = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."weight_unit where id = ".intval($delivery_info['weight_id']));  //配送方式的重单单价
		
		//开始读取相应地区对该配送方式的重量支持
		require_once APP_ROOT_PATH."system/utils/child.php";
		$child = new child("delivery_region");
		
		$delivery_items = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_fee where delivery_id = ".intval($delivery_info['id'])." order by id desc");
		foreach($delivery_items as $k=>$v)
		{
					$support_ids = array();
					$sp_ids = $v['region_ids']; //每条支持地区值
					$sp_ids = explode(",",$sp_ids);
					foreach($sp_ids as $sp_id)
					{
						$tmp_ids = $child->getChildIds($sp_id);
						$tmp_ids[] = $sp_id;
						$support_ids = array_merge($support_ids,$tmp_ids);
					}
					if(in_array($region_id,$support_ids))
					{				
						//支持该配送地区
						$delivery_weight_conf = $v;
						break;
					}
		}
		
		//当没有子地区支持时，查看是否支持配送
		if(!$delivery_weight_conf)
		{
			if($delivery_info['allow_default'] == 1)
			{
				$delivery_weight_conf = $delivery_info;
			}	
		}
		
		if($delivery_weight_conf)
		{
			
			$delivery_weight_conf['first_weight'] = $delivery_weight_conf['first_weight'] * $delivery_weight_unit['rate'];
			$delivery_weight_conf['continue_weight'] = $delivery_weight_conf['continue_weight'] * $delivery_weight_unit['rate'];
			
			if($weight <= $delivery_weight_conf['first_weight']) //未超过首重
			{
				$delivery_fee = $delivery_weight_conf['first_fee'];
			}
			else
			{
				//超重
				if($delivery_weight_conf['continue_weight']!=0)
				$continue_fee = (($weight - $delivery_weight_conf['first_weight']) / $delivery_weight_conf['continue_weight']) * $delivery_weight_conf['continue_fee'];
				else
				$continue_fee = 0;
				$delivery_fee = $delivery_weight_conf['first_fee'] + $continue_fee;
			}
		}
		else
		{
			$delivery_fee = 0;
		}
	return $delivery_fee;
	
}

/**
 * 
 * 创建付款单号
 * @param $money 付款金额
 * @param $order_id 订单ID
 * @param $payment_id 付款方式ID
 * @param $memo 付款单备注
 * return payment_notice_id 付款单ID
 * 
 */
function make_payment_notice($money,$order_id,$payment_id,$memo='')
{
	$notice['create_time'] = get_gmtime();
	$notice['order_id'] = $order_id;
	$notice['user_id'] = $GLOBALS['db']->getOneCached("select user_id from ".DB_PREFIX."deal_order where id = ".$order_id);
	$notice['payment_id'] = $payment_id;
	$notice['memo'] = $memo;
	$notice['money'] = $money;
	do{
		$notice['notice_sn'] = to_date(get_gmtime(),"Ymdhis").rand(10,99);
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$notice,'INSERT','','SILENT'); 
		$notice_id = intval($GLOBALS['db']->insert_id());
	}while($notice_id==0);
	return $notice_id;
}

/**
 * 付款单的支付
 * @param unknown_type $payment_notice_id
 * 当超额付款时在此进行退款处理
 */
function payment_paid($payment_notice_id)
{
	$payment_notice_id = intval($payment_notice_id);
	$now = get_gmtime();
	$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set pay_time = ".$now.",is_paid = 1 where id = ".$payment_notice_id." and is_paid = 0");	
	$rs = $GLOBALS['db']->affected_rows();
	if($rs)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$payment_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where id = ".$payment_notice['payment_id']);
		if($payment_info['class_name'] == 'Voucher')
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_amount = pay_amount + ".$payment_notice['money'].",ecv_money = ".$payment_notice['money']." where id = ".$payment_notice['order_id']." and ((pay_amount + ".$payment_notice['money']." <= total_price) or ".$payment_notice['money'].">=total_price)");
			$order_incharge_rs = $GLOBALS['db']->affected_rows();
		}
		elseif($payment_info['class_name'] == 'Account')
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_amount = pay_amount + ".$payment_notice['money'].",account_money = account_money + ".$payment_notice['money']." where id = ".$payment_notice['order_id']." and pay_amount + ".$payment_notice['money']." <= total_price");
			$order_incharge_rs = $GLOBALS['db']->affected_rows();
		}
		else
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_amount = pay_amount + ".$payment_notice['money']." where id = ".$payment_notice['order_id']." and pay_amount + ".$payment_notice['money']." <= total_price");
			$order_incharge_rs = $GLOBALS['db']->affected_rows();
			
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."payment set total_amount = total_amount + ".$payment_notice['money']." where class_name = '".$payment_info['class_name']."'");									
		if(!$order_incharge_rs)
		{

			//超出充值
			require_once APP_ROOT_PATH."system/libs/user.php";
			if($order_info['is_delete']==1)
			$msg = sprintf($GLOBALS['lang']['DELETE_INCHARGE'],$payment_notice['notice_sn']);
			else
			$msg = sprintf($GLOBALS['lang']['PAYMENT_INCHARGE'],$payment_notice['notice_sn']);			
			modify_account(array('money'=>$payment_notice['money'],'score'=>0),$payment_notice['user_id'],$msg);
			//更新订单的extra_status为1
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set extra_status = 1 where id = ".intval($payment_notice['order_id']));
		}
		
		//在此处开始生成付款的短信及邮件
		send_payment_sms($payment_notice_id);
		send_payment_mail($payment_notice_id);
	}
	return $rs;
}

//同步订单支付状态
function order_paid($order_id)
{
		$order_id  = intval($order_id);
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		if($order['pay_amount']>=$order['total_price'])
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_status = 2 where id =".$order_id." and pay_status <> 2");
			$rs = $GLOBALS['db']->affected_rows();
			if($rs)
			{
				//支付完成
				order_paid_done($order_id);
				$result = true;
			}
		}
		elseif($order['pay_amount']<$order['total_price']&&$order['pay_amount']!=0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_status = 1 where id =".$order_id);
			$result = false;  //订单未支付成功
		}
		elseif($order['pay_amount']==0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_status = 0 where id =".$order_id);
			$result = false;  //订单未支付成功
		}		
		return $result;
}

//订单付款完毕后执行的操作,充值单也在这处理，未实现
function order_paid_done($order_id)
{
	//处理支付成功后的操作
	/**
	 * 1. 发货
	 * 2. 超量发货的存到会员中心
	 * 3. 发券
	 */
	require_once APP_ROOT_PATH."system/libs/deal.php";
	$order_id = intval($order_id);
	$stock_status = true;  //团购状态
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	if($order_info['type'] == 0)
	{
	$goods_list = $GLOBALS['db']->getAll("select deal_id,sum(number) as num from ".DB_PREFIX."deal_order_item where order_id = ".$order_id." group by deal_id");	
	foreach($goods_list as $k=>$v)
	{
		$sql = "update ".DB_PREFIX."deal set buy_count = buy_count + ".$v['num'].
			   ",user_count = user_count + 1 where id=".$v['deal_id'].
			   " and ((buy_count + ".$v['num']."<= max_bought) or max_bought = 0) ".
			   " and time_status = 1 and buy_status <> 2";

		$GLOBALS['db']->query($sql); //增加商品的发货量
		$rs = $GLOBALS['db']->affected_rows();
		
		if($rs)
		{
			$affect_list[] = $v;  //记录下更新成功的团购商品，用于回滚
		}
		else
		{
			//失败成功，即过期支付，超量支付
			$stock_status = false;
			break;
		}
	}

	if($stock_status)
	{
		//发货成功，发券
		foreach($goods_list as $k=>$v)
		{
			//为相应团购发券
				$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($v['deal_id']));
				if($deal_info['is_coupon'] == 1)
				{
					if($deal_info['deal_type'] == 1) //按单发券
					{
						$deal_order_item_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_info['id']." and deal_id = ".$v['deal_id']);
						foreach($deal_order_item_list as $item)
						{
//							for($i=0;$i<$item['number'];$i++) //按单
//							{
								//需要发券
								/**
								 * 1. 先从已有团购券中发送
								 * 2. 无有未发送的券，自动发送
								 * 3. 发送状态的is_valid 都是 0, 该状态的激活在syn_deal_status中处理
								 */
								$sql = "update ".DB_PREFIX."deal_coupon set user_id=".$order_info['user_id'].
									   ",order_id = ".$order_info['id'].
									   ",order_deal_id = ".$item['id'].
									   " where deal_id = ".$v['deal_id'].
									   " and user_id = 0 ".
									   " and is_delete = 0";
								$GLOBALS['db']->query($sql);
								$exist_coupon = $GLOBALS['db']->affected_rows();
								if(!$exist_coupon)
								{
									//未发送成功，即无可发放的预设团购券
									add_coupon($v['deal_id'],$order_info['user_id'],0,'','',0,0,$item['id'],$order_info['id']);
								}
//							}
						}
					}
					else
					{
						$deal_order_item_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_info['id']." and deal_id = ".$v['deal_id']);
						foreach($deal_order_item_list as $item)
						{
							for($i=0;$i<$item['number'];$i++) //按件
							{
								//需要发券
								/**
								 * 1. 先从已有团购券中发送
								 * 2. 无有未发送的券，自动发送
								 * 3. 发送状态的is_valid 都是 0, 该状态的激活在syn_deal_status中处理
								 */
								$sql = "update ".DB_PREFIX."deal_coupon set user_id=".$order_info['user_id'].
									   ",order_id = ".$order_info['id'].
									   ",order_deal_id = ".$item['id'].
									   " where deal_id = ".$v['deal_id'].
									   " and user_id = 0 ".
									   " and is_delete = 0 limit 1";
								$GLOBALS['db']->query($sql);
								$exist_coupon = $GLOBALS['db']->affected_rows();
								if(!$exist_coupon)
								{
									//未发送成功，即无可发放的预设团购券
									add_coupon($v['deal_id'],$order_info['user_id'],0,'','',0,0,$item['id'],$order_info['id']);
								}
							}
						}
					}
				}
				//发券结束						
		}
		//开始处理返还的积分或现金
		require_once APP_ROOT_PATH."system/libs/user.php";
		if($order_info['return_total_money']!=0)
		{
			$msg = sprintf($GLOBALS['lang']['ORDER_RETURN_MONEY'],$order_info['order_sn']);
			modify_account(array('money'=>$order_info['return_total_money'],'score'=>0),$order_info['user_id'],$msg);	
		}
		
		if($order_info['return_total_score']!=0)
		{
			$msg = sprintf($GLOBALS['lang']['ORDER_RETURN_SCORE'],$order_info['order_sn']);
			modify_account(array('money'=>0,'score'=>$order_info['return_total_score']),$order_info['user_id'],$msg);	
		}
		
		//开始处理返利，只创建返利， 发放将与msg_list的自动运行一起执行
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
		//开始查询所购买的列表中支不支持促销
		$is_referrals = 1; //默认为返利
		foreach($goods_list as $k=>$v)
		{
			$is_referrals = $GLOBALS['db']->getOneCached("select is_referral from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			if($is_referrals == 0)
			{
				break;
			}
		}
		if($user_info['referral_count']<app_conf("REFERRAL_LIMIT")&&$is_referrals == 1)
		{
			//开始返利给推荐人
			$parent_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_info['pid']);
			if($parent_info)
			{
				if((app_conf("REFERRAL_IP_LIMIT")==1&&$parent_info['login_ip']!=get_client_ip())||app_conf("REFERRAL_IP_LIMIT")==0) //IP限制
				{
					if(app_conf("INVITE_REFERRALS_TYPE")==0) //现金返利
					{
						$referral_data['user_id'] = $parent_info['id']; //初返利的会员ID
						$referral_data['rel_user_id'] = $user_info['id'];	 //被推荐且发生购买的会员ID
						$referral_data['create_time'] = get_gmtime();
						$referral_data['money']	=	app_conf("INVITE_REFERRALS");
						$referral_data['order_id']	=	$order_info['id'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."referrals",$referral_data); //插入
					}
					else
					{
						$referral_data['user_id'] = $parent_info['id']; //初返利的会员ID
						$referral_data['rel_user_id'] = $user_info['id'];	 //被推荐且发生购买的会员ID
						$referral_data['create_time'] = get_gmtime();
						$referral_data['score']	=	app_conf("INVITE_REFERRALS");
						$referral_data['order_id']	=	$order_info['id'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."referrals",$referral_data); //插入
					}
					$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count = referral_count + 1 where id = ".$user_info['id']);
				}				
				
			}
		}
		
		
		//超出充值
		if($order_info['pay_amount']>$order_info['total_price'])
		{
			require_once APP_ROOT_PATH."system/libs/user.php";
			if($order_info['total_price']<0)
			$msg = sprintf($GLOBALS['lang']['MONEYORDER_INCHARGE'],$order_info['order_sn']);
			else
			$msg = sprintf($GLOBALS['lang']['OUTOFMONEY_INCHARGE'],$order_info['order_sn']);
			$refund_money = $order_info['pay_amount']-$order_info['total_price'];
			
			if($order_info['account_money']>$refund_money)$account_money_now = $order_info['account_money'] - $refund_money; else $account_money_now = 0;		
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set account_money = ".$account_money_now." where id = ".$order_info['id']);	
			
			if($order_info['ecv_money']>$refund_money)$ecv_money_now = $order_info['ecv_money'] - $refund_money; else $ecv_money_now = 0;		
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set ecv_money = ".$ecv_money_now." where id = ".$order_info['id']);	
			
			modify_account(array('money'=>($order_info['pay_amount']-$order_info['total_price']),'score'=>0),$order_info['user_id'],$msg);
		}
	}
	else
	{
		//开始模拟事务回滚
		foreach($affect_list as $k=>$v)
		{
			$sql = "update ".DB_PREFIX."deal set buy_count = buy_count - ".$v['num'].
			   	   ",user_count = user_count - 1 where id=".$v['deal_id'];
			$GLOBALS['db']->query($sql); //回滚已发的货量
		}
		
		//超出充值
		require_once APP_ROOT_PATH."system/libs/user.php";
		$msg = sprintf($GLOBALS['lang']['OUTOFSTOCK_INCHARGE'],$order_info['order_sn']);			
		modify_account(array('money'=>$order_info['total_price'],'score'=>0),$order_info['user_id'],$msg);	
		//将订单的extra_status 状态更新为2，并自动退款，结单
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set extra_status = 2, after_sale = 1, refund_money = pay_amount, order_status = 1 where id = ".intval($order_info['id']));
		
		//记录退款的订单日志		
		$log['log_info'] = $msg;
		$log['log_time'] = get_gmtime();
		$log['order_id'] = intval($order_info['id']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_log",$log);
	}
	
		//同步所有未过期的团购状态
		syn_dealing();
	}//end 普通团购
	else
	{  
		//订单充值
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set order_status = 1 where id = ".$order_info['id']); //充值单自动结单
		require_once APP_ROOT_PATH."system/libs/user.php";
		$msg = sprintf($GLOBALS['lang']['USER_INCHARGE_DONE'],$order_info['order_sn']);			
		modify_account(array('money'=>$order_info['total_price']-$order_info['payment_fee'],'score'=>0),$order_info['user_id'],$msg);	
	}
}

?>