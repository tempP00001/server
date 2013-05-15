<? include handler('template')->file('@admin/header'); ?>
 <script language="javascript">
function getxy(i)
{
$('#update').html('商家地图位置已经修改，请提交保存');
$('#map').val(i);
}
function userIDchangeMonitor()
{
var cuid = $('#userid').val();
if (cuid == '-1')
{
$('.userRegisterArea').show();
}
else
{
$('.userRegisterArea').hide();
}
}
function addSellerHelper()
{
art.dialog({
title: '帮助手册',
icon: 'question',
lock: true,
content: document.getElementById('helper_of_addSeller'),
yesText: '知道了',
yesFn: true
});
}
function showMapAPI()
{
var url = "?mod=tttuangou&code=addmap&id=<?=$seller['sellermap']?>";
art.dialog({
title: '您只需要点击地图上的标签到指定的地方，关闭该窗口即可，系统会自己收集您的坐标！',
content: '<iframe src="'+url+'" width="600" height="500"></iframe>',
padding: '0',
fixed: true,
resize: false,
drag: false
});
}
function map_location_translate()
{
art.dialog({
title: '确认转换？',
icon: 'question',
lock: true,
content: '转换前请确认您的商家坐标存在偏差（坐标转换会破坏原有坐标）',
yesText: '开始转换',
yesFn: map_location_translate_begin,
noFn: true
});
}
function map_location_translate_begin()
{
$.notify.loading('正在转换...');
$.getJSON('?mod=lgc&code=get&path=<?=$seller["id"]?>~maplocation4google2sogou@seller', function(data){
if (data == 'ok')
{
$.notify.success('转换成功！（2秒后自动刷新此页面）');
setTimeout(function(){window.location = window.location;}, 2000);
}
else if(data == 'false')
{
$.notify.failed('转换失败！（您已经转换过，或者您的服务器无法连接到外部网络）');
}
else
{
$.notify.alert(data);
}
$.notify.loading(false);
});
}
</script> <form action="<?=$action?>" method="post"  enctype="multipart/form-data">
<input type="hidden" name="FORMHASH" value='<?=FORMHASH?>'/> <table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"> <tr class="header"> <td colspan="2">修改商家</td> </tr> <tr> <td width="23%" bgcolor="#F4F8FC">当前商家登录用户：</td> <td width="77%" align="right"> <select name="userid" id="userid" onchange="userIDchangeMonitor()">
<? if(is_array($user)) { foreach($user as $i => $value) { ?>
<option value="<?=$value['uid']?>" 
<? if($seller['userid']==$value['uid']) { ?>
selected
<? } ?>
><?=$value['username']?></option>
<? } } ?>
<option value="-1">=新建登录用户=</option> </select> </td> </tr> <tr class="userRegisterArea" style="display: none;"> <td width="18%" bgcolor="#F4F8FC">新建商家登录用户：</td> <td align="right"> <input name="username" type="text" />
&nbsp;&nbsp;&nbsp;<a href="#helper" onclick="javascript:addSellerHelper();return false;">帮助</a> <div id="helper_of_addSeller" style="display: none;">
1. 商家用户指的是商户登录本系统时使用的帐号，属于注册会员<br/>
2. 商家用户是商家产品团购券的管理者，<font color=red>可前台登陆、进入商家管理、查看团购券使用情况、核对和消费</font><br/>
3. 如果您输入一个不存在的用户，系统会自动注册<br/>
4. 如果您输入的是一个已经存在的用户名，则此用户必须身份为合作商家，并且没有绑定到其他商家<br/>
5. 对于已经存在的用户，登录密码依然为原密码，并不会修改成您输入的密码
</div> </td> </tr> <tr class="userRegisterArea" style="display: none;"> <td width="18%" bgcolor="#F4F8FC">新建商家登录密码：</td> <td align="right"> <input name="password" type="text" /> </td> </tr> <tr><td>所在城市：</td><td><select name="area" id="area">
<? if(is_array($city)) { foreach($city as $i => $value) { ?>
  
<option value="<?=$value['cityid']?>" 
<? if($value['cityid']==$seller['area']) { ?>
selected
<? } ?>
><?=$value['cityname']?></option>
<? } } ?>
</select></td></tr> <tr> <td width="23%" bgcolor="#F4F8FC">商家名称:</td> <td width="77%" align="right"> <input name="sellername" type="text" value="<?=$seller['sellername']?>" id="sellername" size="40"></td> </tr> <tr> <td width="23%" bgcolor="#F4F8FC">商家地址:</td> <td width="77%" align="right"> <input name="selleraddress" type="text" value="<?=$seller['selleraddress']?>" id="selleraddress" size="90"></td> </tr> <tr> <td bgcolor="#F4F8FC">商家电话:</td> <td align="right"><input name="sellerphone" value="<?=$seller['sellerphone']?>" type="text" id="sellerphone" size="50" /></td> </tr> <tr> <td bgcolor="#F4F8FC">商家网站:</td> <td align="right"><input name="sellerurl" value="<?=$seller['sellerurl']?>" type="text" id="sellerurl" size="50" /></td> </tr> <tr> <td bgcolor="#F4F8FC">地图位置:</td> <td align="right"> <a href="#" onclick="showMapAPI();return false;"><span id='update'>更新(将标记放到具体的位置关闭弹出窗口，然后提交保存)</span></a> <br/>如果您发现商家坐标不准确，请先<a href="#location_translate" onclick="map_location_translate();return false;">点击这里进行坐标转换</a>（Google到Sogou）
</td> </tr> </table> <br> <center><input type="hidden" id="id" name="id" value="<?=$seller['id']?>" /><input type="hidden" id="map" name="map" /><input type="submit" class="button" name="addsubmit" value="提 交"></center> </form>
<? include handler('template')->file('@admin/footer'); ?>