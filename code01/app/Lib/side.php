<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

function get_side_deal($deal_id)
{	
	$city = get_current_deal_city();
	$city_id = $city['id'];
	$side_deal_list = $GLOBALS['cache']->get("SIDE_DEAL_LIST_".$deal_id."_".$city_id);
	if($side_deal_list === false)
	{
		$side_deal_list = get_deal_list(app_conf("SIDE_DEAL_COUNT"),0,$city_id,array(DEAL_ONLINE),"id<>".$deal_id." and buy_type = 0 ");
		$GLOBALS['cache']->set("SIDE_DEAL_LIST_".$deal_id."_".$city_id,$side_deal_list);
	}
	return $side_deal_list['list'];
}
function get_side_message($deal_id)
{
	$where = "rel_table = 'deal'";
	if($deal_id>0)
	{
		$where = "rel_table='deal' and rel_id=".$deal_id;
	}
	$side_message = get_message_list(app_conf("SIDE_MESSAGE_COUNT"),$where);
	foreach($side_message['list'] as $k=>$v)
	{
		$side_message['list'][$k]['url'] = url_pack("message#deal",$v['rel_id'])."#consult-entry-".$v['id'];
	}
	return $side_message;
}

function get_side_vote()
{
	$now = get_gmtime();
	$vote = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."vote where is_effect = 1 and begin_time < ".$now." and (end_time = 0 or end_time > ".$now.") order by sort desc limit 1");
	return $vote;
}



if($deal)
{
	$side_deal_list = get_side_deal(intval($deal['id']));
	$GLOBALS['tmpl']->assign("side_deal_list",$side_deal_list);
}

//输出返利
if(app_conf("INVITE_REFERRALS_TYPE") == 0)
$referral_tip = sprintf($GLOBALS['lang']['INVITE_REFERRALS_TIP'],format_price(app_conf("INVITE_REFERRALS")));
else
$referral_tip = sprintf($GLOBALS['lang']['INVITE_REFERRALS_TIP'],format_score(app_conf("INVITE_REFERRALS")));
$GLOBALS['tmpl']->assign("invite_referrals_tip",$referral_tip);

//输出在线客服与时间
$qq = explode("|",app_conf("ONLINE_QQ"));
$msn = explode("|",app_conf("ONLINE_MSN"));
$GLOBALS['tmpl']->assign("online_qq",$qq);
$GLOBALS['tmpl']->assign("online_msn",$msn);


//输出留言
$side_message = get_side_message(intval($deal['id']));
$GLOBALS['tmpl']->assign("side_message",$side_message);

//商务合作
$deal_cooperation_tip = sprintf($GLOBALS['lang']['DEAL_COOPERATION_TIP'],url_pack("message#seller"));
$GLOBALS['tmpl']->assign("deal_cooperation_tip",$deal_cooperation_tip);

//输出调查
$vote = get_side_vote();
$GLOBALS['tmpl']->assign("vote",$vote);

?>