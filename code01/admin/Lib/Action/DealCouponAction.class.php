<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

class DealCouponAction extends CommonAction{
	public function index()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$deal_info = M("Deal")->getById($deal_id);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		if($deal_info['is_coupon']==0)
		{
			$this->error(l("DEAL_NO_COUPON"));
		}

		$this->assign("deal_info",$deal_info);
		
		
		//处理-1情况的select
		if(!isset($_REQUEST['is_valid']))
		{
			$_REQUEST['is_valid'] = -1;
		}
		if(!isset($_REQUEST['is_confirm']))
		{
			$_REQUEST['is_confirm'] = -1;
		}
		
		//定义条件
		$map['is_delete'] = 0;
		$map['deal_id'] = $deal_id;
		if(trim($_REQUEST['sn'])!='')
		{
			$map['sn'] = array('like','%'.trim($_REQUEST['sn']).'%');
		}
		if(trim($_REQUEST['user_id'])!='')
		{
			$map['user_id'] = intval(trim($_REQUEST['user_id']));
		}
		if(intval($_REQUEST['is_valid'])>=0)
		{
			$map['is_valid'] = intval($_REQUEST['is_valid']);
		}
		if(intval($_REQUEST['is_confirm'])>=0)
		{
			if(intval($_REQUEST['is_confirm'])==0)
			$map['confirm_time'] = 0;
			else
			$map['confirm_time'] = array('gt',0);
		}
	
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	public function add()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$deal_info = M("Deal")->getById($deal_id);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		$this->assign("deal_info",$deal_info);
		$this->display();
	}
	
	public function insert() {
		require_once APP_ROOT_PATH."/system/libs/deal.php";
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add",array("deal_id"=>$data['deal_id'])));
		if(M("DealCoupon")->where("deal_id=".$data['deal_id']." and sn='".$data['sn']."'")->count()>0)
		{
			$this->error(L("DEAL_COUPON_SN_EXIST"));
		}
		if(intval($data['user_id'])>0&&M("User")->where("id=".intval($data['user_id']))->count()==0)
		{
			$this->error(L("USER_NOT_EXIST"));
		}
		
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		
		// 更新数据
		$log_info = $data['sn'];
		$res = add_coupon($data['deal_id'],$data['user_id'],$data['is_valid'],$data['sn'],$data['password'],$data['begin_time'],$data['end_time']);
		$status= $res['status'];
		if (false != $status) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$deal_info = M("Deal")->getById($vo['deal_id']);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		$this->assign("deal_info",$deal_info);
		
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("account_name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(M("DealCoupon")->where("deal_id=".$data['deal_id']." and sn='".$data['sn']."'")->count()>0)
		{
			$this->error(L("DEAL_COUPON_SN_EXIST"));
		}
		if(intval($data['user_id'])>0&&M("User")->where("id=".intval($data['user_id']))->count()==0)
		{
			$this->error(L("USER_NOT_EXIST"));
		}
		
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
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
					$info[] = $data['sn'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();		
				if ($list!==false) {
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
	
	
	public function import()
	{
		$this->error(l("NO_SUPPORT_THIS_FUNC"));
	}
	

	public function sample()
	{
		$this->error(l("NO_SUPPORT_THIS_FUNC"));
	}
	
	
	/*导入csv*/	
	public function importInsert()
	{
		$this->error(l("NO_SUPPORT_THIS_FUNC"));
	}
	
	
	
	public function sms()
	{
		if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_COUPON")==1)
		{
			$id = intval($_REQUEST['id']);
			send_deal_coupon_sms($id);
			save_log("ID:".$id.L("SEND_COUPON_SMS_SUCCESS"),1);
			$this->success(L("SEND_COUPON_SMS_SUCCESS"));
		}
		else
		{
			$this->error(L("SEND_COUPON_SMS_FAILED"));
		}
	}
	
	public function mail()
	{
		
		if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_COUPON")==1)
		{
			$id = intval($_REQUEST['id']);
			send_deal_coupon_mail($id);
			save_log("ID:".$id.L("SEND_COUPON_MAIL_SUCCESS"),1);
			$this->success(L("SEND_COUPON_MAIL_SUCCESS"));
		}
		else
		{
			$this->error(L("SEND_COUPON_MAIL_FAILED"));
		}
	}
	
	
	public function export_csv($page = 1)
	{
		$this->error(l("NO_SUPPORT_THIS_FUNC"));		
	}

}
?>