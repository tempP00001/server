<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';


function emptyTag($string)
{
		if(empty($string))
			return "";
			
		$string = strip_tags(trim($string));
		$string = preg_replace("|&.+?;|",'',$string);
		
		return $string;
}
function convertUrl($url)
{
		$url = str_replace("&","&amp;",$url);
		return $url;
}
if($_REQUEST['act']=='citys')
{
	header('Content-type: text/xml; charset=utf-8');
	$now = get_gmtime();
	$sql = 'SELECT id,name from '.DB_PREFIX.'deal_city where is_effect = 1 and is_delete = 0 and is_open = 1';


	$list = $GLOBALS['db']->getAll($sql);
		
	$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
	$xml.="<response date=\"".to_date($now,"r")."\">\r\n";
	$xml.="<citys>\r\n";
		
	foreach($list as $item)
	{
		$xml.="<city><id>".$item['id']."</id><name>".$item['name']."</name></city>\r\n";
	}
	$xml.="</citys>\r\n";
	$xml.="</response>\r\n";
	echo $xml;
}

if($_REQUEST['act']=='deals')
{
	header('Content-type: text/xml; charset=utf-8');
	$cityID = intval($_REQUEST['id']);
	$now = get_gmtime();
	if($cityID > 0)
			$where = " and d.city_id = $cityID";
	else
			$where = "";
		
	$sql = "SELECT d.id,d.discount,d.city_id,d.name as goods_name,d.img,d.icon,d.current_price,d.origin_price,d.begin_time,d.end_time,d.brief as goodsbrief,dc.name as city_name,s.name as supplier_name,d.buy_count ".
					'FROM '.DB_PREFIX.'deal as d '.
					'left join '.DB_PREFIX.'deal_city as dc on dc.id = d.city_id '.
					'left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id '.
					"where d.is_effect = 1 and d.is_delete = 0 and d.time_status = 1 and d.buy_status < 2  $where group by d.id order by d.sort desc,d.id desc";
		

	$list = $GLOBALS['db']->getAll($sql);
		
	$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
	$xml.="<response date=\"".to_date($now,"r")."\">\r\n";

	foreach($list as $item)
	{
			$url = url_pack("deal",$item['id']);	
			
			$xml.="<goods>\r\n";
			$xml.="<cityid>".$item['city_id']."</cityid>\r\n";
			$xml.="<cityname>".$item['city_name']."</cityname>\r\n";
			$xml.="<id>".$item['id']."</id>\r\n";
			$xml.="<title>".emptyTag($item['goods_name'])."</title>\r\n";
			$xml.="<brief><![CDATA[".$item['goodsbrief']."]]></brief>\r\n";
			$xml.="<url>".convertUrl(get_domain().$url)."</url>\r\n";
			$xml.="<groupprice>".floatval($item['current_price'])."</groupprice>\r\n";
			$xml.="<marketprice>".floatval($item['origin_price'])."</marketprice>\r\n";
			$xml.="<begintime>".to_date($item['begin_time'],"r")."</begintime>\r\n";
			$xml.="<endtime>".to_date($item['end_time'],"r")."</endtime>\r\n";
			
			//对图片路径的修复
			$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	        $icon = str_replace(APP_ROOT."./public/",$domain."/public/",$item['icon']);	
	        $icon = str_replace("./public/",$domain."/public/",$item['icon']);
	        $img = str_replace(APP_ROOT."./public/",$domain."/public/",$item['img']);	
	        $img = str_replace("./public/",$domain."/public/",$item['img']);	
			
			$xml.="<smallimg>".$icon."</smallimg>\r\n";
			$xml.="<bigimg>".$img."</bigimg>\r\n";
			$xml.="<suppliers>".emptyTag($item['supplier_name'])."</suppliers>\r\n";
			$xml.="<buycount>".$item['buy_count']."</buycount>\r\n";
			$xml.="</goods>\r\n";
	}
	$xml.="</response>\r\n";
	echo $xml;
}


//hao123接口
if($_REQUEST['act'] == 'hao123')
	{
		header('Content-type: text/xml; charset=utf-8');
		$now = get_gmtime();
		$sql = "SELECT d.id,d.discount,d.city_id,c.name as cate_name,d.name as goods_name,d.img,d.icon,d.current_price,d.origin_price,d.begin_time,d.end_time,d.brief as goodsbrief,dc.name as city_name,s.name as supplier_name,d.buy_count,sl.tel as sp_tel,sl.address as sp_address,sl.xpoint,sl.ypoint   ".
					'FROM '.DB_PREFIX.'deal as d '.
					'left join '.DB_PREFIX.'deal_city as dc on dc.id = d.city_id '.
					'left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id '.
					'left join '.DB_PREFIX.'supplier_location as sl on sl.supplier_id = s.id '.
					'left join '.DB_PREFIX.'deal_cate as c on c.id = d.cate_id '.
					"where d.is_effect = 1 and d.is_delete = 0 and d.time_status = 1 and d.buy_status < 2  group by d.id order by d.sort desc,d.id desc";
		

	$list = $GLOBALS['db']->getAll($sql);
	
	$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
	$xml.="<urlset>\r\n";
		
		foreach($list as $item)
		{
			$xml.="<url>\r\n";
		
			$url = url_pack("deal",$item['id']);	
			//商品折扣
			if($item['discount']>0)
			{
				$rebate = number_format($item['discount'],1);
			}
			if ($item['origin_price'] > 0)
				$rebate = number_format($item['current_price']/$item['origin_price'] * 10, 1);
			else
				$rebate = 0;
				
			
			$begin_time = intval($item['begin_time'])>0?(intval($item['begin_time'])+(8*3600)):0; 
			$end_tiime = intval($item['end_time'])>0?(intval($item['end_time'])+(8*3600)):0; 
			
			$xml.="<loc>".convertUrl(get_domain().$url)."</loc>\r\n";
			$xml.="<data>\r\n";
			$xml.="<display>\r\n";
			$xml.="<website>".app_conf("SHOP_TITLE")."</website>\r\n";
			$xml.="<siteurl>".get_domain().APP_ROOT."</siteurl>\r\n";
			$xml.="<city>".$item['city_name']."</city>\r\n";
			$gcatename=$item['cate_name'];
			if(!preg_match('/^((?!餐|美食|饮).)*$/is',$gcatename))
			{
				$class = 1;
			}
			else if(!preg_match('/^((?!休闲|娱乐).)*$/is',$gcatename))
			{
				$class = 2;
			}
			else if(!preg_match('/^((?!美容|化妆).)*$/is',$gcatename))
			{
				$class = 3;
			}
			else if(!preg_match('/^((?!网上|购物).)*$/is',$gcatename))
			{
				$class = 4;
			}
			else if(!preg_match('/^((?!运动|健身 ).)*$/is',$gcatename))
			{
				$class = 5;
			}
			
			$xml.="<category>".$class."</category>\r\n";
			$xml.="<dpshopid>".$item['xpoint'].",".$item['ypoint']."</dpshopid>\r\n";
			$xml.="<range>".$item['sp_address']."</range>\r\n";
			$xml.="<address>".$item['sp_address']."</address>\r\n";
			$xml.="<major>1</major>\r\n";
			$xml.="<title>".emptyTag($item['goods_name'])."</title>\r\n";

			$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	        $img = str_replace(APP_ROOT."./public/",$domain."/public/",$item['img']);	
	        $img = str_replace("./public/",$domain."/public/",$item['img']);
			
			$xml.="<image>".$img."</image>\r\n";
			$xml.="<startTime>".$begin_time."</startTime>\r\n";
			$xml.="<endTime>".$end_tiime."</endTime>\r\n";
			$xml.="<value>".round($item['origin_price'],2)."</value>\r\n";
			$xml.="<price>".round($item['current_price'],2)."</price>\r\n";
			$xml.="<rebate>".$rebate."</rebate>\r\n";
			$xml.="<bought>".$item['buy_count']."</bought>\r\n";
			
			
			$xml.="</display>\r\n";
			$xml.="</data>\r\n";
			$xml.="</url>\r\n";
		}
		
		$xml.="</urlset>\r\n";
		echo $xml;
	}
	
// hao360
if($_REQUEST['act']=='hao360city')
{
		header("Content-Type:text/html; charset=utf-8");
		$sql = 'SELECT id,name from '.DB_PREFIX.'deal_city where is_effect = 1 and is_delete = 0 and is_open = 1';
			
		$list = $GLOBALS['db']->getAll($sql);
		
		$txt="";
		
		foreach($list as $item)
		{
			$txt.=$item['name']."\n";
		}

		echo $txt;
}

if($_REQUEST['act']=='hao360product')
{
		header("Content-Type:text/html; charset=utf-8");
		$now = get_gmtime();
		
		$sql = "SELECT d.id,d.discount,d.city_id,d.name as goods_name,d.img,d.current_price,d.origin_price,d.begin_time,d.end_time,d.brief as goodsbrief,dc.name as city_name,s.name as supplier_name,d.buy_count ".
					'FROM '.DB_PREFIX.'deal as d '.
					'left join '.DB_PREFIX.'deal_city as dc on dc.id = d.city_id '.
					'left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id '.
					"where d.is_effect = 1 and d.is_delete = 0 and d.time_status = 1 and d.buy_status < 2  group by d.id order by d.sort desc,d.id desc";

		$list = $GLOBALS['db']->getAll($sql);
		$txt = "";
		
		foreach($list as $item)
		{
			if($txt != "")
				$txt .= "\n";
				
			$url = url_pack("deal",$item['id']);	
				
			if($item['discount']>0)
			{
				$rebate = number_format($item['discount'],1);
			}
			if ($item['origin_price'] > 0)
				$rebate = number_format($item['current_price']/$item['origin_price'] * 10, 1);
			else
				$rebate = 0;
			
			$begin_time = intval($item['begin_time'])>0?(intval($item['begin_time'])+(8*3600)):0; 
			$end_tiime = intval($item['end_time'])>0?(intval($item['end_time'])+(8*3600)):0; 
			
			$txt.=$item['city_name']."\t";
			
			$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	        $img = str_replace(APP_ROOT."./public/",$domain."/public/",$item['img']);	
	        $img = str_replace("./public/",$domain."/public/",$item['img']);
	        
			$txt.=urlencode($img)."\t";
			$txt.=$item['goods_name']."\t";
			$txt.=intval(floatval($item['origin_price']) * 100)."\t";
			$txt.=intval(floatval($item['current_price']) * 100)."\t";
			$txt.=$rebate."\t";
			$txt.=to_date($begin_time)."\t";
			$txt.=to_date($end_tiime)."\t";
			$txt.=urlencode(get_domain().$url)."\t";
			$txt.=$item['buy_count'];
		}
		
		echo $txt;
}
	
	
if($_REQUEST['act']=='hao360')
{
		header('Content-type: text/xml; charset=utf-8');
		$sql = "SELECT d.id,d.supplier_id,d.discount,c.name as cate_name,d.icon,d.city_id,d.name as goods_name,d.img,d.current_price,d.origin_price,d.begin_time,d.end_time,d.brief as goodsbrief,dc.name as city_name,s.name as supplier_name,d.buy_count ".
					'FROM '.DB_PREFIX.'deal as d '.
					'left join '.DB_PREFIX.'deal_city as dc on dc.id = d.city_id '.
					'left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id '.
					'left join '.DB_PREFIX.'deal_cate as c on c.id = d.cate_id '.
					"where d.is_effect = 1 and d.is_delete = 0 and d.time_status = 1 and d.buy_status < 2  group by d.id order by d.sort desc,d.id desc";
		
		$list = $GLOBALS['db']->getAll($sql);

		$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
		$xml.="<data>\r\n";
		$xml.="<site_name>".app_conf("SHOP_TITLE")."</site_name> \r\n";
		$xml.="<goodsdata>\r\n";
		$index = 0;
		
		foreach($list as $item)
		{
			$index++;
			
			$xml.="<goods id=\"$index\">\r\n";
				
			$url = url_pack("deal",$item['id']);	
				
			if($item['discount']>0)
			{
				$rebate = number_format($item['discount'],1);
			}
			if ($item['origin_price'] > 0)
				$rebate = number_format($item['current_price']/$item['origin_price'] * 10, 1);
			else
				$rebate = 0;
			
			$begin_time = intval($item['begin_time'])>0?(intval($item['begin_time'])+(8*3600)):0; 
			$end_tiime = intval($item['end_time'])>0?(intval($item['end_time'])+(8*3600)):0; 
				
			$supplier = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$item['supplier_id']." and is_main = 1");
			
			$address = "";
			if($supplier)
			{
				$address = emptyTag($supplier['address']);
				$map = convertUrl("http://ditu.google.cn/maps?f=q&source=s_q&hl=zh-CN&geocode=&q=".$supplier['api_address']);
			}
			
			$xml.="<city_name>".$item['city_name']."</city_name>\r\n";
			$xml.="<site_url>".get_domain().APP_ROOT."</site_url>\r\n";
			$xml.="<goods_url>".convertUrl(get_domain().$url)."</goods_url>\r\n";
			$xml.="<desc>".emptyTag($item['goods_name'])."</desc>\r\n";
			$xml.="<class>".$item['cate_name']."</class>\r\n";
			
			$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	        $img = str_replace(APP_ROOT."./public/",$domain."/public/",$item['img']);	
	        $img = str_replace("./public/",$domain."/public/",$item['img']);
	        
			$xml.="<img_url>".$img."</img_url>\r\n";
			$xml.="<original_price>".number_format(round($item['origin_price'],2), 2, '.', '')."</original_price>\r\n";
			$xml.="<sale_price>".number_format(round($item['current_price'],2), 2, '.', '')."</sale_price>\r\n";
			$xml.="<sale_rate>".$rebate."</sale_rate>\r\n";
			$xml.="<sales_num>".$item['buy_count']."</sales_num>\r\n";
			$xml.="<start_time>".to_date($begin_time,"YmdHis")."</start_time>\r\n";
			$xml.="<close_time>".to_date($end_tiime,"YmdHis")."</close_time>\r\n";
			$xml.="<address>$address</address>\r\n";
			$xml.="<map>$map</map>\r\n";
			$xml.="<coupon_start_time></coupon_start_time>\r\n";
			$xml.="<coupon_close_time></coupon_close_time>\r\n";
			$xml.="</goods>\r\n";
		}
		
		$xml.="</goodsdata>\r\n";
		$xml.="</data>\r\n";
		echo $xml;
	}
	
	
	if($_REQUEST['act']=='tuanp')
	{
		header('Content-type: text/xml; charset=utf-8');
		$sql = "SELECT d.id,d.supplier_id,d.discount,c.name as cate_name,d.city_id,d.name as goods_name,d.img,d.current_price,d.origin_price,d.begin_time,d.end_time,d.brief as goodsbrief,dc.name as city_name,s.name as supplier_name,d.buy_count,s.content,sl.tel as sp_tel,sl.address as sp_address  ".
					'FROM '.DB_PREFIX.'deal as d '.
					'left join '.DB_PREFIX.'deal_city as dc on dc.id = d.city_id '.
					'left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id '.
					'left join '.DB_PREFIX.'deal_cate as c on c.id = d.cate_id '.
					'left join '.DB_PREFIX.'supplier_location as sl on sl.supplier_id = s.id '.
					"where d.is_effect = 1 and d.is_delete = 0 and d.time_status = 1 and d.buy_status < 2  group by d.id order by d.sort desc,d.id desc";
		
		$list = $GLOBALS['db']->getAll($sql);
		$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
		$xml.="<urlset>\r\n";
		
		foreach($list as $item)
		{
			$xml.="<url>\r\n";
						
			$url = url_pack("deal",$item['id']);	
				
			if($item['discount']>0)
			{
				$rebate = number_format($item['discount'],1);
			}
			if ($item['origin_price'] > 0)
				$rebate = number_format($item['current_price']/$item['origin_price'] * 10, 1);
			else
				$rebate = 0;
			
			$begin_time = intval($item['begin_time'])>0?(intval($item['begin_time'])+(8*3600)):0; 
			$end_tiime = intval($item['end_time'])>0?(intval($item['end_time'])+(8*3600)):0; 
				
			$item_brief = $item['goodsbrief']==''?$item['goods_name']:$item['goodsbrief'];

			$xml.="<loc>".convertUrl(get_domain().$url)."</loc>\r\n";
			$xml.="<data>\r\n";
			$xml.="<display>\r\n";
			$xml.="<website>".app_conf("SHOP_TITLE")."</website>\r\n";
			$xml.="<siteurl>".get_domain().APP_ROOT."</siteurl>\r\n";
			$xml.="<city>".$item[city_name]."</city>\r\n";
			$xml.="<title>".emptyTag($item['goods_name'])."</title>\r\n";
			
			$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	        $img = str_replace(APP_ROOT."./public/",$domain."/public/",$item['img']);	
	        $img = str_replace("./public/",$domain."/public/",$item['img']);			
			$xml.="<image>".$img."</image>\r\n";
			$xml.="<startTime>".$begin_time."</startTime>\r\n";
			$xml.="<endTime>".$end_tiime."</endTime>\r\n";
			$xml.="<value>".round($item['origin_price'],2)."</value>\r\n";
			$xml.="<price>".round($item['current_price'],2)."</price>\r\n";
			$xml.="<description><![CDATA[".$item_brief."]]></description>\r\n";
			$xml.="<bought>".$item['buy_count']."</bought>\r\n";
			$xml.="<merchantName>".emptyTag($item['supplier_name'])."</merchantName>\r\n";	
			$xml.="<merchantPhone>".$item['sp_tel']."</merchantPhone>\r\n";	
			$xml.="<merchantAddr>".$item['sp_address']."</merchantAddr>\r\n";	
			$xml.="<detail><![CDATA[".$item['content']."]]></detail>\r\n";			
			
			$xml.="</display>\r\n";
			$xml.="</data>\r\n";
			$xml.="</url>\r\n";
		}
		
		$xml.="</urlset>\r\n";
		echo $xml;
	}
?>