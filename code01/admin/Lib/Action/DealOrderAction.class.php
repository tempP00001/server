<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

class DealOrderAction extends CommonAction{
	public function incharge_index()
	{
		$condition['is_delete'] = 0;
		$condition['type'] = 1;
		if(trim($_REQUEST['user_name'])!='')
		{		
			$ids = M("User")->where(array("user_name"=>array('like','%'.trim($_REQUEST['user_name']).'%')))->field("id")->findAll();
			$ids_arr = array();
			foreach($ids as $k=>$v)
			{
				array_push($ids_arr,$v['id']);
			}	
			$condition['user_id'] = array("in",$ids_arr);
		}
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function incharge_trash()
	{
		$condition['is_delete'] = 1;
		$condition['type'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function deal_index()
	{
		//处理-1情况的select
		if(!isset($_REQUEST['pay_status']))
		{
			$_REQUEST['pay_status'] = -1;
		}
		if(!isset($_REQUEST['delivery_status']))
		{
			$_REQUEST['delivery_status'] = -1;
		}
		if(!isset($_REQUEST['extra_status']))
		{
			$_REQUEST['extra_status'] = -1;
		}
		if(!isset($_REQUEST['after_sale']))
		{
			$_REQUEST['after_sale'] = -1;
		}
		
		//定义条件
		$map[DB_PREFIX.'deal_order.is_delete'] = 0;
		$map[DB_PREFIX.'deal_order.type'] = 0;
		if(trim($_REQUEST['order_sn'])!='')
		{
			$map[DB_PREFIX.'deal_order.order_sn'] = array('like','%'.trim($_REQUEST['order_sn']).'%');
		}
		if(trim($_REQUEST['user_name'])!='')
		{
			$map[DB_PREFIX.'user.user_name'] = array('like','%'.trim($_REQUEST['user_name']).'%');
		}
		if(intval($_REQUEST['pay_status'])>=0)
		{
			$map[DB_PREFIX.'deal_order.pay_status'] = intval($_REQUEST['pay_status']);
		}
		if(intval($_REQUEST['delivery_status'])>=0)
		{
			$map[DB_PREFIX.'deal_order.delivery_status'] = intval($_REQUEST['delivery_status']);
		}
		if(intval($_REQUEST['extra_status'])>=0)
		{
			$map[DB_PREFIX.'deal_order.extra_status'] = intval($_REQUEST['extra_status']);
		}
		if(intval($_REQUEST['after_sale'])>=0)
		{
			$map[DB_PREFIX.'deal_order.after_sale'] = intval($_REQUEST['after_sale']);
		}
		if(intval($_REQUEST['deal_id'])>0)
		{
			$map[DB_PREFIX."deal_order_item.deal_id"] = intval($_REQUEST['deal_id']);
		}
	
		
		//关于列表数据的输出
		if (isset ( $_REQUEST ['_order'] )) {
			$order = DB_PREFIX.'deal_order.'.$_REQUEST ['_order'];
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : DB_PREFIX.'deal_order.id';
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		

		
		$count = M("DealOrderItem")
				->where($map)
				->join(DB_PREFIX.'deal_order ON '.DB_PREFIX.'deal_order.id = '.DB_PREFIX.'deal_order_item.order_id')	
				->join(DB_PREFIX.'user ON '.DB_PREFIX.'deal_order.user_id = '.DB_PREFIX.'user.id')					
				->count('distinct('.DB_PREFIX.'deal_order.id)');
		
		if ($count > 0) {
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据

			$voList = M("DealOrderItem")
				->where($map)
				->join(DB_PREFIX.'deal_order ON '.DB_PREFIX.'deal_order.id = '.DB_PREFIX.'deal_order_item.order_id')
				->join(DB_PREFIX.'user ON '.DB_PREFIX.'deal_order.user_id = '.DB_PREFIX.'user.id')	
				->group(DB_PREFIX.'deal_order.id')				
				->field(DB_PREFIX.'deal_order.*')
				->order( $order ." ". $sort)
				->limit($p->firstRow . ',' . $p->listRows)->findAll ( );

			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			//分页显示

			$page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
			$this->assign ( 'list', $voList );
			$this->assign ( 'sort', $sort );
			$this->assign ( 'order', $_REQUEST ['_order']?$_REQUEST ['_order']:'id' );
			$this->assign ( 'sortImg', $sortImg );
			$this->assign ( 'sortType', $sortAlt );
			$this->assign ( "page", $page );
			$this->assign ( "nowPage",$p->nowPage);
		}
		//end 
		$this->display ();
		return;
	}
	
	
	public function export_csv($page = 1)
	{
		$this->error(l("NO_SUPPORT_THIS_FUNC"));
	}
	
	public function deal_trash()
	{
		$condition['is_delete'] = 1;
		$condition['type'] = 0;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function pay_incharge()
	{
		$id = intval($_REQUEST['id']);
		//开始由管理员手动收款
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id);
		if($order_info['pay_status'] != 2)
		{
			require_once APP_ROOT_PATH."system/libs/cart.php";
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_info['id']." and payment_id = ".$order_info['payment_id']." and is_paid = 0");
			if(!$payment_notice)
			{
				make_payment_notice($order_info['total_price'],$order_info['id'],$order_info['payment_id']);
				$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_info['id']." and payment_id = ".$order_info['payment_id']." and is_paid = 0");
			}
			
			payment_paid(intval($payment_notice['id']));	//对其中一条款支付的付款单付款					
			$msg = sprintf(l("ADMIN_PAYMENT_PAID"),$payment_notice['notice_sn']);
			save_log($msg,1);
			$rs = order_paid($order_info['id']);
			
			if($rs)
			{
				$msg = sprintf(l("ADMIN_ORDER_PAID"),$order_info['order_sn']);
				save_log($msg,1);
				$this->success(l("ORDER_PAID_SUCCESS"));
			}
			else
			{
				$msg = sprintf(l("ADMIN_ORDER_PAID"),$order_info['order_sn']);
				save_log($msg,0);
				$this->error(l("ORDER_PAID_FAILED"));
			}
		}
		else 
		{
			$this->error(l("ORDER_PAID_ALREADY"));
		}
	}	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['order_sn'];	
					if($data['order_status']==0&&$data['type']==0)
					{
						$this->error (l("ORDER_DELETE_FAILED"),$ajax);						
					}
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function restore() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['order_sn'];						
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					save_log($info.l("RESTORE_SUCCESS"),1);
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['order_sn'];
					if($data['order_status']==0&&$data['type']==0)
					{
						$this->error (l("ORDER_DELETE_FAILED"),$ajax);						
					}	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
				if ($list!==false) {
					//删除关联数据
					M("PaymentNotice")->where(array ('order_id' => array ('in', explode ( ',', $id ) ) ))->delete(); //删除相关收款单
					M("DealOrderLog")->where(array ('order_id' => array ('in', explode ( ',', $id ) ) ))->delete(); //删除相关日志
					M("DealCoupon")->where(array ('order_id' => array ('in', explode ( ',', $id ) ) ))->delete(); //删除相关团购券
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function view_order()
	{
		$id = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$id." and is_delete = 0 and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		$order_deal_items = M("DealOrderItem")->where("order_id=".$order_info['id'])->findAll();
		foreach($order_deal_items as $k=>$v)
		{
			$order_deal_items[$k]['is_delivery'] = M("Deal")->where("id=".$v['deal_id'])->getField("is_delivery");
		}
		$this->assign("order_deals",$order_deal_items);
		$this->assign("order_info",$order_info);
		
		$payment_notice = M("PaymentNotice")->where("order_id = ".$order_info['id']." and is_paid = 1")->order("pay_time desc")->findAll();
		$this->assign("payment_notice",$payment_notice);
		
		
		
		//输出订单留言
		$map['rel_table'] = 'deal_order';
		$map['rel_id'] = $order_info['id'];
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name= "Message"; 
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		
		//输出订单相关的团购券
		$coupon_list = M("DealCoupon")->where("order_id = ".$order_info['id']." and is_delete = 0")->findAll();
		$this->assign("coupon_list",$coupon_list);
		
		//输出订单日志
		$log_list = M("DealOrderLog")->where("order_id=".$order_info['id'])->order("log_time desc")->findAll();
		$this->assign("log_list",$log_list);
		
		$this->display();
	}
	
	public function delivery()
	{
		$id = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$id." and is_delete = 0 and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		$order_deal_items = M("DealOrderItem")->where("order_id=".$order_info['id']." and delivery_status = 0")->findAll();
		foreach($order_deal_items as $k=>$v)
		{
			if(M("Deal")->where("id=".$v['deal_id'])->getField("is_delivery")==0) //无需发货的商品
			{
				unset($order_deal_items[$k]);
			}
		}

		$this->assign("order_deals",$order_deal_items);
		$this->assign("order_info",$order_info);
		$this->display();
	}
	
	public function do_delivery()
	{
		$order_id = intval($_REQUEST['order_id']);
		$order_deals = $_REQUEST['order_deals'];
		$delivery_sn = $_REQUEST['delivery_sn'];
		$memo = $_REQUEST['memo'];
		if(!$order_deals)
		{
			$this->error(l("PLEASE_SELECT_DELIVERY_ITEM"));
		}
		else
		{
			$delivery_sn = $delivery_sn  == ''?to_date(get_gmtime(),"Ymdhis".rand(111,999)):$delivery_sn;
			$deal_names = array();
			foreach($order_deals as $order_deal_id)
			{
				$deal_name =$GLOBALS['db']->getOneCached("select d.sub_name from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_order_item as doi on doi.deal_id = d.id where doi.id = ".$order_deal_id);
				array_push($deal_names,$deal_name);
				$rs = make_delivery_notice($order_id,$order_deal_id,$delivery_sn,$memo);
				if($rs)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set delivery_status = 1 where id = ".$order_deal_id);
				}
			}
			$deal_names = implode(",",$deal_names);
			
			send_delivery_mail($delivery_sn,$deal_names);
			send_delivery_sms($delivery_sn,$deal_names);
			//开始同步订单的发货状态
			$order_deal_items = M("DealOrderItem")->where("order_id=".$order_id)->findAll();
			foreach($order_deal_items as $k=>$v)
			{
				if(M("Deal")->where("id=".$v['deal_id'])->getField("is_delivery")==0) //无需发货的商品
				{
					unset($order_deal_items[$k]);
				}				
			}
			$delivery_deal_items = $order_deal_items;
			foreach($delivery_deal_items as $k=>$v)
			{
				if($v['delivery_status']==0) //未发货去除
				{
					unset($delivery_deal_items[$k]);
				}				 
			}
			

			if(count($delivery_deal_items)==0&&count($order_deal_items)!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 0 where id = ".$order_id); //未发货
			}
			elseif(count($delivery_deal_items)>0&&count($order_deal_items)!=0&&count($delivery_deal_items)<count($order_deal_items))
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 1 where id = ".$order_id); //部分发
			}
			else
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 2 where id = ".$order_id); //全部发
			}		
			M("DealOrder")->where("id=".$order_id)->setField("update_time",get_gmtime());
			$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));
			
			order_log(l("DELIVERY_SUCCESS").$delivery_sn.$_REQUEST['memo'],$order_id);
			$this->success(l("DELIVERY_SUCCESS"));
		}
	}
	
	public function over_order()
	{
		$order_id  = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0 and order_status = 0 and (pay_status = 2 and ((delivery_status = 2 or delivery_status = 5)) or (pay_amount = refund_money))")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0 and order_status = 0 and (pay_status = 2 and ((delivery_status = 2 or delivery_status = 5)) or (pay_amount = refund_money))")->setField("order_status",1);
		M("DealOrder")->where("id=".$order_id)->setField("update_time",get_gmtime());
		save_log($order_info['order_sn'].l("OVER_ORDER_SUCCESS"),1);
		order_log($order_info['order_sn'].l("OVER_ORDER_SUCCESS"),$order_id);
		$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));
		$this->success(l("OVER_ORDER_SUCCESS"));
	}

	public function open_order()
	{
		$order_id  = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0 and order_status = 1 and (pay_status = 2 and ((delivery_status = 2 or delivery_status = 5)) or (pay_amount = refund_money))")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0 and order_status = 1 and (pay_status = 2 and ((delivery_status = 2 or delivery_status = 5)) or (pay_amount = refund_money))")->setField("order_status",0);
		M("DealOrder")->where("id=".$order_id)->setField("update_time",get_gmtime());
		save_log($order_info['order_sn'].l("OPEN_ORDER_SUCCESS"),1);
		order_log($order_info['order_sn'].l("OPEN_ORDER_SUCCESS"),$order_id);
		
		$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));
		$this->success(l("OPEN_ORDER_SUCCESS"));
	}
	
	public function admin_memo()
	{
		$order_id  = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		if($order_info['order_status'] == 1)
		{
			$this->error(l("ORDER_OVERED"));
		}
		$admin_memo = $_REQUEST['admin_memo'];
		$after_sale_r = $_REQUEST['after_sale'];
		$after_sale = 0;
		foreach($after_sale_r as $k=>$v)
		{
			$after_sale+=intval($v);
		}
		$refund_money = floatval($_REQUEST['refund_money']);
		if($refund_money == $order_info['refund_money'])
		{
			$log_info = $admin_memo;
		}
		else
		{
			$log_info = sprintf(L("CHANGE_REFUND_AMOUNT"),format_price($refund_money)).$admin_memo;
		}
		order_log($log_info,$order_id);
		M("DealOrder")->where("id=".$order_id)->setField("refund_money",$refund_money);
		M("DealOrder")->where("id=".$order_id)->setField("admin_memo",$admin_memo);
		M("DealOrder")->where("id=".$order_id)->setField("update_time",get_gmtime());
		M("DealOrder")->where("id=".$order_id)->setField("after_sale",$after_sale);
		save_log($order_info['order_sn'].l("ORDER_MEMO_MODIFY").l("AFTER_SALE").":".l("AFTER_SALE_".$after_sale),1);
		$this->success(l("SAVE_SUCCESS"));
	}
	
	public function order_incharge()
	{
		$order_id  = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		$payment_list = M("Payment")->where("is_effect = 1 and class_name <> 'Voucher'")->findAll();
		$this->assign("payment_list",$payment_list);
		$this->assign("user_money",M("User")->where("id=".$order_info['user_id'])->getField("money"));
		$this->assign("order_info",$order_info);
		$this->display();
	}
	
	public function do_incharge()
	{
		$order_id  = intval($_REQUEST['order_id']);
		$payment_id = intval($_REQUEST['payment_id']);
		$payment_info = M("Payment")->getById($payment_id);
		$memo = $_REQUEST['memo'];
		$order_info = M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0")->find();		
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		$user_money = M("User")->where("id=".$order_info['user_id'])->getField("money");
		$pay_amount = $order_info['deal_total_price']+ $order_info['delivery_fee']-$order_info['account_money']-$order_info['ecv_money']+$payment_info['fee_amount'];
		
		if($payment_info['class_name']=='Account'&&$user_money<$pay_amount)
		$this->error(l("ACCOUNT_NOT_ENOUGH"));
		
		require_once APP_ROOT_PATH."system/libs/cart.php";
		$notice_id = make_payment_notice($pay_amount,$order_id,$payment_id,$memo);
		
		$order_info['total_price'] = $order_info['deal_total_price']+ $order_info['delivery_fee']+$payment_info['fee_amount'];
		$order_info['payment_fee'] = $payment_info['fee_amount'];  
		$order_info['payment_id'] = $payment_info['id'];
		$order_info['update_time'] = get_gmtime();
		M("DealOrder")->save($order_info);
		
		$payment_notice = M("PaymentNotice")->getById($notice_id);
		$rs = payment_paid($payment_notice['id']);	
		if($rs&&$payment_info['class_name']=='Account')
		{
			//余额支付
			require_once APP_ROOT_PATH."system/payment/Account_payment.php";				
			require_once APP_ROOT_PATH."system/libs/user.php";
			$msg = sprintf($payment_lang['USER_ORDER_PAID'],$order_info['order_sn'],$payment_notice['notice_sn']);			
			modify_account(array('money'=>"-".$payment_notice['money'],'score'=>0),$payment_notice['user_id'],$msg);
		}

		
		if($rs)
		{	
			order_paid($order_id);
			$msg = sprintf(l("MAKE_PAYMENT_NOTICE_LOG"),$order_info['order_sn'],$payment_notice['notice_sn']);
			save_log($msg,1);
			order_log($msg.$_REQUEST['memo'],$order_id);
			$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));
			$this->success(l("ORDER_INCHARGE_SUCCESS"));
		}
		else
		{
			$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));
			$this->success(l("ORDER_INCHARGE_FAILED"));
		}
	}
}
?>