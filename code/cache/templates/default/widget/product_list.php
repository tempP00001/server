<div class="r_m_rc_t"></div>
<div class="t_area_out rc_t_area_out">
<? $cpid = isset($_GET['view']) ? $_GET['view'] : -1;
 ?>
<h1>其他精彩团购</h1>
<div class="t_area_in">
<ul class="product_list">
<? if(is_array(logic('product')->GetList(logic('misc')->City('id'),PRO_ACV_Yes))) { foreach(logic('product')->GetList(logic('misc')->City('id'),PRO_ACV_Yes) as $i => $product) { ?>
<? if ($product['id'] == $cpid) continue ; $ic++;  ?>
<li>
<p class="name"><b><?=$ic?>、</b><a href="?view=<?=$product['id']?>"><?=$product['name']?></a></p>
<p class="image"><a href="?view=<?=$product['id']?>"><img src="<? echo imager($product['imgs']['0'], IMG_Small);; ?>" width="200" height="121"/></a></p>
<p class="shop">
原价：<font class="markprice">￥<?=$product['price']?></font><br/>
现价：<font class="price">￥<?=$product['nowprice']?></font><br/>
<p class="agio_float">优惠 <font class="agio"><?=$product['discount']?></font> 折
<br/>
已有 <font class="buys"><?=$product['succ_buyers']?></font> 人购买
</p>
</p>
</li>
<? } } ?>
<? if($ic == 0) { ?>
<li>
<p class="name">
暂时没有其他团购了哦！
</p>
</li>
<? } ?>
</ul>
</div>
</div>
<div class="r_m_rc_b"></div>