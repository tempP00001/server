<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

class NavAction extends CommonAction{
	private $navs;

	public function __construct()
	{
		parent::__construct();
		$this->navs = array(
			'index' => array(
				'name'	=>	l('NAV_INDEX_MODULE')
			),
			'article' => array(
				'name'	=>	l('NAV_ARTICLE_MODULE')
			),
			'deal' => array(
				'name'	=>	l('NAV_DEAL_MODULE')
			),
			'deals' => array(
				'name'	=>	l('NAV_DEALS_MODULE'),
				'acts'	=> array(
					'history'	=>	l('NAV_DEALS_MODULE_HISTORY'),
					'notice'	=>	l('NAV_DEALS_MODULE_NOTICE'),
					'index'		=>	l('NAV_DEALS_MODULE_INDEX')
				),
			),
			'message'	=>	array(
				'name'	=>	l('NAV_MESSAGE_MODULE')
			),
			
		);
	}
	public function index()
	{
		parent::index();
	}

	public function edit() {		
		//定义菜单的部份可选项		
		$id = intval($_REQUEST ['id']);		
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		$this->assign("navs",$this->navs);
		$this->display ();
	}
	
	public function load_module()
	{
		$id = intval($_REQUEST['id']);
		$module = trim($_REQUEST['module']);
		$act = M(MODULE_NAME)->where("id=".$id)->getField("u_action");
		$this->ajaxReturn($this->navs[$module]['acts'],$act);
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("NAV_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['url'])&&$_REQUEST['u_module']=='')
		{
			$this->error(L("NAV_URL_EMPTY_TIP"));
		}		
		
		if($_REQUEST['u_module']!='')
		{
			$data['url'] = '';
		}
		if($data['url']!='')
		{
			$data['u_module'] = '';
			$data['u_action'] = '';
			$data['u_id'] = '';
			$data['u_param'] = '';
		}
		if(!isset($_REQUEST['u_action']))
		$data['u_action'] = '';
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
			$this->error(L("UPDATE_FAILED").$dbErr);
		}
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M("Nav")->where("id=".$id)->getField("name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M("Nav")->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
}
?>