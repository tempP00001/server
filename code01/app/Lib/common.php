<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

//app项目用到的函数库

/**
 *  获取团购城市列表
 */
function get_deal_citys()
{
	$city_list = $GLOBALS['cache']->get("CACHE_DEAL_CITY_LIST");
	if($city_list===false)
	{
		$city_list = $GLOBALS['db']->getAll("select id,name,is_open from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0");
		foreach($city_list as $k=>$v)
		{
			
			$city_list[$k]['url'] = url_pack("deal_city",$v['id']);
		}
		$GLOBALS['cache']->set("CACHE_DEAL_CITY_LIST",$city_list);
	}
	return $city_list;	
}

/**
 * 获取当前团购城市
 */
function get_current_deal_city()
{		
	if(es_cookie::is_set("deal_city"))
	{	
		$deal_city_id = es_cookie::get("deal_city");
		$deal_city = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0 and id = ".intval($deal_city_id));
	}
	else
	{
		//设置如存在的IP订位
		if(file_exists(APP_ROOT_PATH."system/extend/ip.php"))
		{			
			require_once APP_ROOT_PATH."system/extend/ip.php";
			$ip =  get_client_ip();
			$iplocation = new iplocate();
			$address=$iplocation->getaddress($ip);
			$city_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0");
			foreach ($city_list as $city)
			{
				if(strpos($address['area1'],$city['name']))
				{
					$deal_city = $city;
					break;
				}
			}
		}
		if(!$deal_city)
		$deal_city = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_city where is_default = 1 and is_effect = 1 and is_delete = 0");
	}
	return $deal_city;
}

/**
 * 获取页面的标题，关键词与描述
 */
function get_shop_info()
{
	if($GLOBALS['city_name'])
	{
		$shop_info['SHOP_TITLE']	=	$GLOBALS['deal_city']['seo_title']==''?app_conf('SHOP_TITLE'):$GLOBALS['deal_city']['seo_title'];
		$shop_info['SHOP_KEYWORD']	=	$GLOBALS['deal_city']['seo_keyword']==''?app_conf('SHOP_KEYWORD'):$GLOBALS['deal_city']['seo_keyword'];
		$shop_info['SHOP_DESCRIPTION']	= $GLOBALS['deal_city']['seo_description']==''?app_conf('SHOP_DESCRIPTION'):$GLOBALS['deal_city']['seo_description'];
	}
	else
	{
		$shop_info['SHOP_TITLE']	=	app_conf('SHOP_TITLE');
		$shop_info['SHOP_KEYWORD']	=	app_conf('SHOP_KEYWORD');
		$shop_info['SHOP_DESCRIPTION']	=	app_conf('SHOP_DESCRIPTION');
	}

	return $shop_info;
}

/**
 * 获取导航菜单
 */
function get_nav_list()
{
	$nav_list = $GLOBALS['cache']->get("CACHE_NAV_LIST");
	if($nav_list === false)
	{
		$nav_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."nav where is_effect = 1 order by sort desc");
		$GLOBALS['cache']->set("CACHE_NAV_LIST",$nav_list);
	}
	return $nav_list;
}

function get_help()
{

		$ids_util = new ChildIds("article_cate");
		$help_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."article_cate where type_id = 1 and is_delete = 0 order by sort desc limit ".intval(app_conf("HELP_CATE_LIMIT")));
		foreach($help_list as $k=>$v)
		{
			$ids = $GLOBALS['cache']->get("CACHE_HELP_ARTICLE_CATE_".$v['id']);
			if($ids===false)
			{
				$ids = $ids_util->getChildIds($v['id']);
				$ids[] = $v['id'];
				$GLOBALS['cache']->set("CACHE_HELP_ARTICLE_CATE_".$v['id'],$ids);
			}
			$help_cate_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."article where cate_id in (".implode(",",$ids).") and is_delete = 0 order by sort desc limit ".intval(app_conf("HELP_ITEM_LIMIT")));
			foreach($help_cate_list as $kk=>$vv)
			{
				if($vv['rel_url']!='')
				{
					if(!preg_match ("/http:\/\//i", $vv['rel_url']))
					{
						if(substr($vv['rel_url'],0,2)=='u:')
						{
							$help_cate_list[$kk]['url'] = url_pack(substr($vv['rel_url'],2));
						}
						else
						$help_cate_list[$kk]['url'] = APP_ROOT."/".$vv['rel_url'];
					}
					else
					$help_cate_list[$kk]['url'] = $vv['rel_url'];
					
					$help_cate_list[$kk]['new'] = 1;
				}
				else
				$help_cate_list[$kk]['url'] = url_pack("article",$vv['id']);
			}
			$help_list[$k]['help_list'] = $help_cate_list;
		}

	return $help_list;
}
//封装url
function url_pack($url,$id = 0)
{
	$citys = $GLOBALS['db']->getAll("select id,name,is_open from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0");
	$show_city = count($citys)>1?true:false;  //有多个城市时显示城市名称到url
	
	$arr = explode("#",$url);
	$module = trim($arr[0]);
	$action = trim($arr[1]);
	if($module == 'deal_city')
	{
		$deal_city = $GLOBALS['db']->getRowCached("select id,name,uname from ".DB_PREFIX."deal_city where id=".$id." and is_delete = 0 and is_effect = 1");
		if(app_conf("URL_MODEL")==0)
		{
			//原始
			$url = APP_ROOT."/index.php?city=".$deal_city['uname'];
		}
		else
		{
			//重写
			$url = APP_ROOT."/".$deal_city['uname'];
		}
		return $url;
	}	
	else
	{
		if(app_conf("URL_MODEL")==0)
		{
			//原始			
			$url = APP_ROOT."/".$module.".php?";
			if($show_city)
			{
				$city_uname = $GLOBALS['deal_city']['uname'];
				$url = $url."city=".$city_uname."&";
			}			
			
			if($action&&$action!='')
			$url .= "act=".$action."&";
			if(intval($id)!=0)
			$url .= "id=".$id."&";
		}
		else
		{
			//重写
			if($show_city)
			{
				$city_uname = $GLOBALS['deal_city']['uname'];
				$url = APP_ROOT."/".$city_uname;
			}
			else
			{
				$url = APP_ROOT;
			}
			if($module!='index')
			$url = $url."/".$module;
			else
			{
				if(!$show_city)
				$url = $url."/";
			}
			if($action&&$action!=''&&$action!='index')
			$url .= "/".$action;
			if(intval($id)!=0)
			$url .= "/".$id;
		}
		return $url;
	}
}


//获取所有子集的类
class ChildIds
{
	public function __construct($tb_name)
	{
		$this->tb_name = $tb_name;	
	}
	private $tb_name;
	private $childIds;
	private function _getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$childItem_arr = $GLOBALS['db']->getAllCached("select id from ".DB_PREFIX.$this->tb_name." where ".$pid_str."=".intval($pid));
		if($childItem_arr)
		{
			foreach($childItem_arr as $childItem)
			{
				$this->childIds[] = $childItem[$pk_str];
				$this->_getChildIds($childItem[$pk_str],$pk_str,$pid_str);
			}
		}
	}
	public function getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$this->childIds = array();
		$this->_getChildIds($pid,$pk_str,$pid_str);
		return $this->childIds;
	}
}

//显示错误
function showErr($msg,$ajax=0,$jump='')
{
	if($ajax==1)
	{
		$result['status'] = 0;
		$result['info'] = $msg;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else
	{
		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['ERROR_TITLE']." - ".$msg);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = $_SERVER['HTTP_REFERER'];
		}
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->display("error.html");
		exit;
	}
}

//显示成功
function showSuccess($msg,$ajax=0,$jump='')
{
	if($ajax==1)
	{
		$result['status'] = 1;
		$result['info'] = $msg;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else
	{
		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['SUCCESS_TITLE']." - ".$msg);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = $_SERVER['HTTP_REFERER'];
		}
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->display("success.html");
		exit;
	}
}

/*ajax返回*/
function ajax_return($data)
{
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($data));
        exit;	
}


function get_user_name($id)
{
	return $GLOBALS['db']->getOneCached("select user_name from ".DB_PREFIX."user where id = ".intval($id));
}


function get_message_rel_data($message,$field='name')
{
	return $GLOBALS['db']->getOneCached("select ".$field." from ".DB_PREFIX.$message['rel_table']." where id = ".intval($message['rel_id']));
}
function get_delivery_sn($id)
{
	$is_delivery = $GLOBALS['db']->getOne("select d.is_delivery from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.id = ".intval($id));
	if($is_delivery==0)
	return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_5'];
	else
	{
		$delivery_notice =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".intval($id));
		if($delivery_notice)
		{
			$str = $delivery_notice['notice_sn'];
			if($delivery_notice['is_arrival']==0)
			{
				$str.="<br /><a href='".url_pack("uc_order#arrival",$delivery_notice['id'])."'>".$GLOBALS['lang']['CONFIRM_ARRIVAL']."</a>";  
			}
			else
			{
				$str.="<br />".$GLOBALS['lang']['ARRIVALED'];
			}
			return $str;
		}
		else
		return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_0'];
	}
}

function get_order_item_list($order_id)
{
	$deal_order_item = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
	$str = '';
	foreach($deal_order_item as $k=>$v)
	{
		$str .="<br /><span title='".$v['name']."'>".msubstr($v['name'])."</span>[".$v['number']."]";	
	}
	return $str;
}
?>