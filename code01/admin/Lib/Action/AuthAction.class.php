<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

//后台验证的基础类

class AuthAction extends BaseAction{
	public function __construct()
	{
		parent::__construct();
		$this->check_auth();		
	}
	
	/**
	 * 验证检限
	 * 已登录时验证用户权限, Index模块下的所有函数无需权限验证
	 * 未登录时跳转登录
	 */
	private function check_auth()
	{
		if(intval(app_conf("EXPIRED_TIME"))>0&&Session::isExpired())
		{
			Session::set(md5(conf("AUTH_KEY")),NULL);
		}
		if(intval(app_conf("EXPIRED_TIME"))>0)
		{
			$_SESSION['__HTTP_Session_Expire_TS'] = time()+(intval(app_conf("EXPIRED_TIME"))*60);
		}	
		
		//管理员的SESSION
		$adm_session = Session::get(md5(conf("AUTH_KEY")));
		$adm_name = $adm_session['adm_name'];
		$adm_id = intval($adm_session['adm_id']);
		$ajax = intval($_REQUEST['ajax']);
		if($adm_id == 0)
		{
			if($ajax == 0)
			$this->redirect("Public/login");
			else
			$this->error(L("NO_LOGIN"),$ajax);	
		}
		
		//开始验证权限，当管理员名称不为默认管理员时	
		
		if($adm_name != conf("DEFAULT_ADMIN")&&MODULE_NAME!='Index'&&MODULE_NAME!='Lang')
		{
			//除IndexAction外需验证的权限列表
			$sql = "select count(*) as c from ".conf("DB_PREFIX")."role_node as role_node left join ".
				   conf("DB_PREFIX")."role_access as role_access on role_node.id=role_access.node_id left join ".
				   conf("DB_PREFIX")."role as role on role_access.role_id = role.id left join ".
				   conf("DB_PREFIX")."role_module as role_module on role_module.id = role_node.module_id left join ".
				   conf("DB_PREFIX")."admin as admin on admin.role_id = role.id ".
				   " where admin.id = ".$adm_id." and role_node.action ='".ACTION_NAME."' and role_module.module = '".MODULE_NAME."' ".
				   " and role_node.is_effect = 1 and role_node.is_delete = 0 and role_module.is_effect = 1 and role_module.is_delete = 0 and role.is_effect = 1 and role.is_delete = 0";
			$count = M()->query($sql);
			$count = $count[0]['c'];
			if($count == 0)
			{
				//节点授权不足，开始判断是否有模块授权
				$module_sql = "select count(*) as c from ".conf("DB_PREFIX")."role_access as role_access left join ".							
							   conf("DB_PREFIX")."role as role on role_access.role_id = role.id left join ".
							   conf("DB_PREFIX")."role_module as role_module on role_module.id = role_access.module_id left join ".
							   conf("DB_PREFIX")."admin as admin on admin.role_id = role.id ".
							   " where admin.id = ".$adm_id." and role_module.module = '".MODULE_NAME."' ".
							   " and role_access.node_id = 0".
							   " and role_module.is_effect = 1 and role_module.is_delete = 0 and role.is_effect = 1 and role.is_delete = 0";
				$module_count = M()->query($module_sql);
				$module_count = $module_count[0]['c'];
				if($module_count == 0)
				{
					if((MODULE_NAME=='File'&&ACTION_NAME=='do_upload')||(MODULE_NAME=='File'&&ACTION_NAME=='do_upload_img'))
					{
						echo "<script>alert('".L("NO_AUTH")."');</script>";
						exit;
					}
					else
					$this->error(L("NO_AUTH"),$ajax);
				}
			}
		}
	}
	
	//index列表的前置通知,输出页面标题
	public function _before_index()
	{
		$this->assign("main_title",L(MODULE_NAME."_INDEX"));
	}
	public function _before_trash()
	{
		$this->assign("main_title",L(MODULE_NAME."_INDEX"));
	}
}
?>