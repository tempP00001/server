<?php
return array(
'DEFAULT_ADMIN'=>'admin',
'URL_MODEL'=>'0',
'AUTH_KEY'=>'easethink',
'TIME_ZONE'=>'8',
'ADMIN_LOG'=>'1',
'DB_VERSION'=>'1.4',
'DB_VOL_MAXSIZE'=>'8000000',
'WATER_MARK'=>'./public/attachment/201011/4cdde85a27105.gif',
'CURRENCY_UNIT'=>'￥',
'BIG_WIDTH'=>'500',
'BIG_HEIGHT'=>'350',
'SMALL_WIDTH'=>'200',
'SMALL_HEIGHT'=>'120',
'WATER_ALPHA'=>'75',
'WATER_POSITION'=>'4',
'MAX_IMAGE_SIZE'=>'300000',
'ALLOW_IMAGE_EXT'=>'jpg,gif,png',
'MAX_FILE_SIZE'=>'1000000',
'ALLOW_FILE_EXT'=>'jpg,gif,png,rar,zip,doc',
'BG_COLOR'=>'#ffffff',
'IS_WATER_MARK'=>'1',
'TEMPLATE'=>'default',
'GOOGLE_MAP_API_KEY'=>'',
'SCORE_UNIT'=>'积分',
'USER_VERIFY'=>'1',
'SHOP_LOGO'=>'./public/attachment/201011/4cdd501dc023b.png',
'SHOP_LANG'=>'zh-cn',
'SHOP_TITLE'=>'易想团购',
'SHOP_KEYWORD'=>'易想团购关键词',
'SHOP_DESCRIPTION'=>'易想团购描述',
'SHOP_TEL'=>'400-800-8888',
'SIDE_DEAL_COUNT'=>'3',
'SIDE_MESSAGE_COUNT'=>'3',
'INVITE_REFERRALS'=>'20',
'INVITE_REFERRALS_TYPE'=>'0',
'ONLINE_MSN'=>'msn@easethink.com|msn2@easethink.com',
'ONLINE_QQ'=>'88888888|9999999',
'ONLINE_TIME'=>'周一至周六 9:00-18:00',
'DEAL_PAGE_SIZE'=>'6',
'PAGE_SIZE'=>'6',
'HELP_CATE_LIMIT'=>'4',
'HELP_ITEM_LIMIT'=>'4',
'SHOP_FOOTER'=>'<div style=\"text-align:center;\">[易想团购] <a target=\"_blank\" href=\"http://www.easethink.com\">http://www.easethink.com</a><br />
</div>',
'USER_MESSAGE_AUTO_EFFECT'=>'1',
'SHOP_REFERRAL_HELP'=>'当好友接受您的邀请，在 [易想网] 上首次成功购买，系统会在 1 小时内返还 ¥20 到您的 [易想网] 电子账户，下次团购时可直接用于支付。没有数量限制，邀请越多，返利越多。<br />
<br />
<span style=\"color:#f10b00;\">友情接示：购买部份团购将不会产生返利或返利特定金额，请查看相关团购的具体说明							</span>',
'SHOP_REFERRAL_SIDE_HELP'=>'<div class=\"side-tip referrals-side\">							<h3 class=\"first\">在哪里可以看到我的返利？</h3>
							<p>如果邀请成功，在本页面会看到成功邀请列表。在\"账户余额\"页，可看到您目前电子账户的余额。返利金额不返现，可在下次团购时用于支付。</p>
							<h3>我邀请好友了，什么时候收到返利？</h3>
							<p>返利会在 24 小时内返还到您的帐户，并会发邮件通知您。</p>
							<h3>哪些情况会导致邀请返利失效？</h3>
							<ul class=\"invalid\">								<li>好友点击邀请链接后超过 72 小时才购买</li>
								<li>好友购买之前点击了其他人的邀请链接</li>
								<li>好友的本次购买不是首次购买</li>
								<li>由于最终团购人数没有达到人数下限，本次团购取消</li>
							</ul>
							<h3>自己邀请自己也能获得返利吗？</h3>
							<p>不可以。我们会人工核查，对于查实的作弊行为，扣除一切返利，并取消邀请返利的资格。</p>
						</div>',
'MAIL_SEND_COUPON'=>'0',
'SMS_SEND_COUPON'=>'0',
'MAIL_SEND_PAYMENT'=>'0',
'SMS_SEND_PAYMENT'=>'0',
'REPLY_ADDRESS'=>'info@easethink.com',
'MAIL_SEND_DELIVERY'=>'0',
'SMS_SEND_DELIVERY'=>'0',
'MAIL_ON'=>'0',
'SMS_ON'=>'0',
'REFERRAL_LIMIT'=>'1',
'SMS_COUPON_LIMIT'=>'3',
'MAIL_COUPON_LIMIT'=>'3',
'COUPON_NAME'=>'易想券',
'BATCH_PAGE_SIZE'=>'500',
'COUPON_PRINT_TPL'=>'<div style=\"border:1px solid #000000;padding:10px;margin:0px auto;width:600px;font-size:14px;\"><table class=\"dataEdit\" cellpadding=\"0\" cellspacing=\"0\">	<tbody><tr>    <td width=\"400\">    	<img src=\"./public/attachment/201011/4cdd505195d40.gif\" alt=\"\" border=\"0\" />     </td>
  <td style=\"font-weight:bolder;font-size:22px;font-family:verdana;\" width=\"43%\">    序列号：{$bond.sn}<br />
    密码：{$bond.password}    </td>
</tr>
<tr><td colspan=\"2\" height=\"1\">  <div style=\"width:100%;border-bottom:1px solid #000000;\">&nbsp;</div>
  </td>
</tr>
<tr><td colspan=\"2\" height=\"8\"><br />
</td>
</tr>
<tr><td style=\"font-weight:bolder;font-size:28px;height:50px;padding:5px;font-family:微软雅黑;\" colspan=\"2\">{$bond.name}</td>
</tr>
<tr><td style=\"line-height:22px;padding-right:20px;\" width=\"400\">{$bond.user_name}<br />
  生效时间:{$bond.begin_time_format}<br />
  过期时间:{$bond.end_time_format}<br />
  商家电话：<br />
  {$bond.tel}<br />
  商家地址:<br />
  {$bond.address}<br />
  交通路线:<br />
  {$bond.route}<br />
  营业时间：<br />
  {$bond.open_time}<br />
  </td>
  <td><div id=\"map_canvas\" style=\"width:255px;height:255px;\"></div>
  <br />
  </td>
</tr>
</tbody>
</table>
</div>',
'PUBLIC_DOMAIN_ROOT'=>'',
'SHOW_DEAL_CATE'=>'1',
'REFERRAL_IP_LIMIT'=>'0',
'UNSUBSCRIBE_MAIL_TIP'=>'您收到此邮件是因为您订阅了%s每日推荐更新。如果您不想继续接收此类邮件，可随时%s',
'CART_ON'=>'1',
'REFERRALS_DELAY'=>'1',
'SUBMIT_DELAY'=>'1',
'APP_MSG_SENDER_OPEN'=>'1',
'ADMIN_MSG_SENDER_OPEN'=>'1',
'SHOP_OPEN'=>'1',
'SHOP_CLOSE_HTML'=>'',
'FOOTER_LOGO'=>'./public/attachment/201011/4cdd50ed013ec.png',
'GZIP_ON'=>'0',
'INTEGRATE_CODE'=>'',
'INTEGRATE_CFG'=>'',
'SHOP_SEO_TITLE'=>'易想团购系统,国内最优秀的PHP开源团购系统',
'CACHE_ON'=>'1',
'EXPIRED_TIME'=>'0',
'CACHE_TYPE'=>'File',
'MEMCACHE_HOST'=>'127.0.0.1:11211',
'MOBILE_MUST'=>'0',
'DEAL_MSG_LOCK'=>'0',
);
 ?>