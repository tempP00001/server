<? include handler('template')->file('@admin/header'); ?>
 <form method="post"  action="<?=$action?>">
<input type="hidden" name="FORMHASH" value='<?=FORMHASH?>'/> <table cellspacing="1" cellpadding="8" width="100%" align="center" class="tableborder"> <tr class="header"> <td colspan="8"><b>返利管理</b> </td> </tr> <tr class="tr_nav"> <td width="15%">购买人/登陆IP</td> <td width="13%">购买时间</td> <td width="30%">产品名称</td> <td width="15%">邀请人/登陆IP</td> <td width="13%">邀请时间</td> <td align="center">管理</td> </tr> 
<? if(empty($finder)) { ?>
 <tr><td colspan="8">暂时没有任何返利信息，只有用户在被邀请后且在72小时内购买团购产品成功时才能出现在这个地方哦!</td></tr> 
<? } ?>
 
<? if(is_array($finder)) { foreach($finder as $i => $value) { ?>
 <tr onmouseover="this.className='tr_hover'" onmouseout="this.className='tr_normal'"> <td><a href="admin.php?mod=member&code=modify&id=<?=$value['buyid']?>"><?=$newuser[$value['buyid']]['username']?></a> <BR><?=$newuser[$value['buyid']]['lastip']?></td> <td>
<? echo date('Y-m-d H:i',$value['buytime']) ?>
</td> <td><?=$value['flag']?></td> <td><a href="admin.php?mod=member&code=modify&id=<?=$value['finderid']?>"><?=$newuser[$value['finderid']]['username']?></a><BR><?=$newuser[$value['finderid']]['lastip']?></td> <td>
<? echo date('Y-m-d H:i',$value['findtime']) ?>
</td> <td align="center">
<? if($value['status']==1) { ?>
<a href="?mod=tttuangou&code=yesfinder&id=<?=$value['id']?>">返利</a> - <a href="?mod=tttuangou&code=nofinder&id=<?=$value['id']?>">取消</a> - <? } elseif($value['status']==2) { ?>已返利 - 
<? } else { ?>已取消 - 
<? } ?>
<a href="?mod=tttuangou&code=deletefinder&id=<?=$value['id']?>">删除</a> </td> </tr> 
<? } } ?>
 </table>
<?=$page_arr?>
<br> <center>
&gt;&gt;&gt; <a href="<?=ihelper('tg.help.invite')?>" target="_blank">邀请返利规则说明</a> &lt;&lt;&lt;
</center> </form>
<? include handler('template')->file('@admin/footer'); ?>