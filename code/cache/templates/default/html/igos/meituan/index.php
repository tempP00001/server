<div class="m960">
<div class="t_l List">
<? if(is_array($product)) { foreach($product as $item) { ?>
<? $icc++ ?>
<div class="t_area_out item m_item" >
<div class="rc_t m_rc_t" ></div>
<div class="t_area_in m_t_area_in" >
<table class="at_jrat">
<tbody>
<tr>
<td>
<div class="at_jrat_title"><a href="?view=<?=$item['id']?>"><?=$item['name']?></a></div>
</td>
</tr>
</tbody>
</table>
<div class="t_deal_r">
<div class="t_deal_r_img">
<? ui('iimager')->single($item['id'], $item['imgs']['0']) ?>
</div>
</div>
<div style="position: relative;" class="t_deal">
<div class="t_deal_l">
<div class="at_buy">
<div class="price">￥<?=$item['nowprice']?> </div>
<div class="deal_g"><a href="?view=<?=$item['id']?>" ></a></div>
</div>
<div class="at_shuzi">
<ul>
<li><span>原价</span><b class="prime_cost ">￥<?=$item['price']?></b></li>
<? if($item['presell']) { ?>
<li><span>团购价</span><b>￥<?=$item['presell']['price_full']?></b></li>
<li><span><?=$item['presell']['text']?></span><b style="color:#C00; font-size:18px"><?=$item['nowprice']?></b></li>
<? } else { ?><li><span>节省</span><b>￥<?=$item['price']-$item['nowprice']?></b></li>
<li><span>折扣</span><b style="color:#C00; font-size:18px"><?=$item['discount']?>折</b></li>
<? } ?>
</ul>
</div>
</div>
<div class="mb_0624"> 
<div class="deal_djs" id="remainTime_<?=$item['id']?>"></div>
<script language="javascript">
addTimeLesser(<?=$item['id']?>, <?=$item['time_remain']?>);
</script>
</div>
<div id="tuanState" class="mb_0626"><b><?=$item['succ_buyers']?></b>人已购买</div>
</div>
<div style="clear: both; height: 0px; overflow:hidden;">&nbsp;</div>
</div>
<div class="rc_t1 m_rc_t1"></div>
</div>
<? } } ?>
<div class="product_list_pager"><?=page_moyo()?></div>
</div>
<div class="t_r">
<?=ui('widget')->load('index_home')?>
</div>
</div>