<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

if (!defined('THINK_PATH')) exit();

//过滤请求
filter_request($_REQUEST);
filter_request($_GET);
filter_request($_POST);
define("AUTH_NOT_LOGIN", 1); //未登录的常量
define("AUTH_NOT_AUTH", 2);  //未授权常量

// 全站公共函数库
// 更改系统配置, 当更改数据库配置时为永久性修改， 修改配置文档中配置为临时修改
function conf($name,$value = false)
{
	if($value === false)
	{
		return C($name);
	}
	else
	{
		if(M("Conf")->where("is_effect=1 and name='".$name."'")->count()>0)
		{
			M("Conf")->where("is_effect=1 and name='".$name."'")->setField("value",$value);
		}
		C($name,$value);
	}
}



function write_timezone($zone='')
{
	if($zone=='')
	$zone = conf('TIME_ZONE');
		$var = array(
			'0'	=>	'UTC',
			'8'	=>	'PRC',
		);
		
		//开始将$db_config写入配置
	    $timezone_config_str 	 = 	"<?php\r\n";
	    $timezone_config_str	.=	"return array(\r\n";
	    $timezone_config_str.="'DEFAULT_TIMEZONE'=>'".$var[$zone]."',\r\n";
	    
	    $timezone_config_str.=");\r\n";
	    $timezone_config_str.="?>";
	   
	    @file_put_contents(get_real_path()."public/timezone_config.php",$timezone_config_str);
}



//后台日志记录
function save_log($msg,$status)
{
	if(conf("ADMIN_LOG")==1)
	{
		$adm_session = Session::get(md5(conf("AUTH_KEY")));
		$log_data['log_info'] = $msg;
		$log_data['log_time'] = get_gmtime();
		$log_data['log_admin'] = intval($adm_session['adm_id']);
		$log_data['log_ip']	= get_client_ip();
		$log_data['log_status'] = $status;	
		$log_data['module']	=	MODULE_NAME;
		$log_data['action'] = 	ACTION_NAME;
		M("Log")->add($log_data);
	}
}

//状态的显示
function get_is_effect($tag,$id)
{
	if($tag)
	{
		return "<span class='is_effect' onclick='set_effect(".$id.",this);'>".l("IS_EFFECT_1")."</span>";
	}
	else
	{
		return "<span class='is_effect' onclick='set_effect(".$id.",this);'>".l("IS_EFFECT_0")."</span>";
	}
}


//排序显示
function get_sort($sort,$id)
{
	if($tag)
	{
		return "<span class='sort_span' onclick='set_sort(".$id.",".$sort.",this);'>".$sort."</span>";
	}
	else
	{
		return "<span class='sort_span' onclick='set_sort(".$id.",".$sort.",this);'>".$sort."</span>";
	}
}
function get_nav($nav_id)
{
	return M("RoleNav")->where("id=".$nav_id)->getField("name");	
}
function get_module($module_id)
{
	return M("RoleModule")->where("id=".$module_id)->getField("module");
}
function get_group($group_id)
{
	if($group_data = M("RoleGroup")->where("id=".$group_id)->find())
	$group_name = $group_data['name'];
	else
	$group_name = L("SYSTEM_NODE");
	return $group_name;
}
function get_role_name($role_id)
{
	return M("Role")->where("id=".$role_id)->getField("name");
}
function get_admin_name($admin_id)
{
	$adm_name = M("Admin")->where("id=".$admin_id)->getField("adm_name");
	if($adm_name)
	return $adm_name;
	else
	return l("NONE_ADMIN_NAME");
}
function get_log_status($status)
{
	return l("LOG_STATUS_".$status);
}
//验证相关的函数
//验证排序字段
function check_sort($sort)
{
	if(!is_numeric($sort))
	{
		return false;
	}
	if(intval($sort)<=0)
	{
		return false;
	}
	return true;
}
function check_empty($data)
{
	if(trim($data)=='')
	{
		return false;
	}
	return true;
}

function set_default($null,$adm_id)
{

	$admin_name = M("Admin")->where("id=".$adm_id)->getField("adm_name");
	if($admin_name == conf("DEFAULT_ADMIN"))
	{
		return "<span style='color:#f30;'>".l("DEFAULT_ADMIN")."</span>";
	}
	else
	{
		return "<a href='".u("Admin/set_default",array("id"=>$adm_id))."'>".l("SET_DEFAULT_ADMIN")."</a>";
	}
}
function get_order_sn($order_id)
{
	return M("DealOrder")->where("id=".$order_id)->getField("order_sn");
}
function get_order_sn_with_link($order_id)
{
	$order_info = M("DealOrder")->where("id=".$order_id)->find();
	if($order_info['type']==0)
	$str = l("DEAL_ORDER_TYPE_0")."：<a href='".u("DealOrder/deal_index",array("order_sn"=>$order_info['order_sn']))."'>".$order_info['order_sn']."</a>";
	else
	$str = l("DEAL_ORDER_TYPE_1")."：<a href='".u("DealOrder/incharge_index",array("order_sn"=>$order_info['order_sn']))."'>".$order_info['order_sn']."</a>";
	
	if($order_info['is_delete']==1)
	$str ="<span style='text-decoration:line-through;'>".$str."</span>";
	return $str;
}
function get_user_name($user_id)
{
	$user_name =  M("User")->where("id=".$user_id." and is_delete = 0")->getField("user_name");
	
	if(!$user_name)
	return l("NO_USER");
	else
	return "<a href='".u("User/index",array("user_name"=>$user_name))."'>".$user_name."</a>";
	
	
}
function get_user_name_js($user_id)
{
	$user_name =  M("User")->where("id=".$user_id." and is_delete = 0")->getField("user_name");
	
	if(!$user_name)
	return l("NO_USER");
	else
	return "<a href='javascript:void(0);' onclick='account(".$user_id.")'>".$user_name."</a>";
	
	
}
function get_pay_status($status)
{
	return L("PAY_STATUS_".$status);
}
function get_delivery_status($status)
{
	return L("ORDER_DELIVERY_STATUS_".$status);
}
function get_payment_name($payment_id)
{
	return M("Payment")->where("id=".$payment_id)->getField("name");
}
function get_delivery_name($delivery_id)
{
	return M("Delivery")->where("id=".$delivery_id)->getField("name");
}
function get_region_name($region_id)
{
	return M("DeliveryRegion")->where("id=".$region_id)->getField("name");
}
function get_city_name($id)
{
	return M("DealCity")->where("id=".$id)->getField("name");
}
function get_message_is_effect($status)
{
	return $status==1?l("NO"):l("YES");
}
function get_message_type($type_name,$rel_id)
{
	$show_name = M("MessageType")->where("type_name='".$type_name."'")->getField("show_name");
	if($type_name=='deal_order')
	{
		$order_sn = M("DealOrder")->where("id=".$rel_id)->getField("order_sn");
		if($order_sn)
		return "[".$order_sn."] <a href='".u("DealOrder/deal_index",array("id"=>$rel_id))."'>".$show_name."</a>";
		else
		return $show_name;
	}
	elseif($type_name=='deal')
	{
		$sub_name = M("Deal")->where("id=".$rel_id)->getField("sub_name");
		if($sub_name)
		return "[".$sub_name."] <a href='".u("Deal/index",array("id"=>$rel_id))."'>".$show_name."</a>";
		else
		return $show_name;
	}
	else
	{
		return $show_name;
	}
}

function get_send_status($status)
{
	return L("SEND_STATUS_".$status);
}
function get_send_mail_type($deal_id)
{
	if($deal_id>0)
	return l("DEAL_NOTICE");
	else 
	return l("COMMON_NOTICE");
}
function get_send_type($send_type)
{
	return l("SEND_TYPE_".$send_type);
}

function get_all_files( $path )
{
		$list = array();
		$dir = @opendir($path);
	    while (false !== ($file = @readdir($dir)))
	    {
	    	if($file!='.'&&$file!='..')
	    	if( is_dir( $path.$file."/" ) ){
	         	$list = array_merge( $list , get_all_files( $path.$file."/" ) );
	        }
	        else 
	        {
	        	$list[] = $path.$file;
	        }
	    }
	    @closedir($dir);
	    return $list;
}
function get_order_item_name($id)
{
	return M("DealOrderItem")->where("id=".$id)->getField("name");
}
function get_supplier_name($id)
{
	return M("Supplier")->where("id=".$id)->getField("name");
}

function get_send_type_msg($status)
{
	if($status==0)
	{
		return l("SMS_SEND");
	}
	else
	{
		return l("MAIL_SEND");
	}
}
function show_content($content,$id)
{
	return "<a title='".l("VIEW")."' href='javascript:void(0);' onclick='show_content(".$id.")'>".l("VIEW")."</a>";
}



function get_is_send($is_send)
{
	if($is_send==0)
	return L("NO");
	else
	return L("YES");
}
function get_send_result($result)
{
	if($result==0)
	{
		return L("FAILED");
	}
	else
	{
		return L("SUCCESS");
	}
}

function order_log($log_info,$order_id)
{
	$data['log_info'] = $log_info;
	$data['log_time'] = get_gmtime();
	$data['order_id'] = $order_id;
	M("DealOrderLog")->add($data);
}
?>