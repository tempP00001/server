<? include handler('template')->file('@admin/header'); ?>
 <form action="admin.php?mod=catalog&code=add&op=save" method="post" >
<input type="hidden" name="FORMHASH" value='<?=FORMHASH?>'/> <table cellspacing="1" cellpadding="4" width="360" align="center" class="tableborder"> <tr> <td width="20%">名称：</td> <td><? echo $master ? $master['name'].' / ' : ''; ?><input type="text" name="name" /> </td> </tr> <tr> <td>短标记：</td> <td> <input type="text" name="flag" /> (请尽量使用纯英文字符)
</td> </tr> <tr> <td class="tr_center" colspan="2"> <input type="hidden" name="parent" value="<?=$parent?>" /> <input type="submit" value=" 保 存 " /> </td> </tr> </table> </form> 
<? include handler('template')->file('@admin/footer'); ?>