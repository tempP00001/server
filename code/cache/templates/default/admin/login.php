<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html xmlns="http://www.w3.org/1999/xhtml"> <head> <meta http-equiv="Content-Type" content="text/html; charset=<?=ini("settings.charset")?>" /> <title>登录系统</title> <style type="text/css"> 
body{padding:0;margin:0px;font-size:12px;color:#333;font-family:Verdana; background:#fbfbfb;}
select{margin-left:1.5em;vertical-align:middle;border:1px solid #b4cceb;height:22px;font-size:12px;}
#main{width:600px;margin:auto;}
*{padding:0;margin:0}
input{font-size:12px;font-family:Verdana;}
#wrap{height:100px;}
#wrapc{height:315px; border:#c9dff5 1px solid; background:#edf4fa;}
#logo{background:url(./templates/admin/images/logo.gif) center top no-repeat;height:64px;width:210px;}
.logo{padding:40px 0 0 175px;}
.login{margin:0 0 0 30px;padding-top:10px;}
.login th{height:33px;line-height:33px;list-style:none;text-align:right;font-weight:normal;width:200px;font-size:12px;}
.login td{text-align:left;font-size:12px;}
.input{font-size:12px;vertical-align:middle;height:17px;margin-top:2px;border:1px solid #fff;color:#666;}
.logo-icon{border: 1px solid; border-color: #8cb7e1 #e3f0fc #e3f0fc #8cb7e1;float:left;margin-left:1.5em;_margin-left:.7em;background:#fff;padding-right:.5em;}
.logo-icon div{background:#fff url(./templates/admin/images/login/login-icon.gif);width:18px;float:left;margin-top:3px;margin-left:.5em;}
.logo-icon .pw{background-position:1px 1px;width:18px;height:20px;}
.logo-icon .pwpd{background-position:0 -49px;width:18px;height:20px;}
.logo-icon .yan{background-position:0 -99px;width:18px;height:20px;}
.logo-icon .daan{background-position:1px -148px;width:18px;height:20px;}
.logo-icon .pw2{width:9em;}
.logo-icon .pwpd2{width:9em;}
.logo-icon .yan2{width:6em;}
.getpwd{ line-height:25px; padding-left:5px;}
.getpwd a{ color:#666}
.bottom{text-align:center;margin:auto;padding-top:1em;color:#888;}
</style> <?=ui('loader')->js('@jquery')?> </head> <body> <div id="main"> <div id="wrap">&nbsp;</div> <div id="wrapc"> <div class="logo"> <div id="logo"></div> </div> <div class="login"> <form method="post"  name="login" action="<?=$action?>">
<input type="hidden" name="FORMHASH" value='<?=FORMHASH?>'/> <table cellpadding="0" cellspacing="0" width="100%"> <tr> <th>管理员账号</th> <td><div class="logo-icon"> <div class="pw"></div> <input class="input pw2" id="username" name="username" value="<?=MEMBER_NAME?>" type="text" readonly="readonly"/> </div></td> </tr> <tr> <th>管理员密码</th> <td><div class="logo-icon"> <div class="pwpd"></div> <input class="input pwpd2" type="password" name="password" /> </div> <span class="getpwd"><a onclick="if(document.getElementById('username').value.length > 0) {window.location.href='admin.php?mod=login&code=get_password&username=' + document.getElementById('username').value;} else {alert('请输入用户名');}" href="#">找回密码</a></span> </td> </tr> <tr> <th></th> <td><input name="submit" type="image" src="./templates/admin/images/login/login.gif" style="margin-left:1.5em;margin-top:.5em;width:80px;height:30px;" /> </td> </tr> <tr> <td id="no_ie" colspan="2" style="text-align: center;padding-top: 20px;color: #C90101;display: none;">
为了获得最佳的使用体验，强烈建议您使用其他浏览器进入后台！<br/><br/> <a href="<?=ihelper('tg.cmd.browser')?>" target="_blank">点此查看</a> </td> </tr> </table> </form> </div> </div> </div> <div class="bottom">
Powered by <a href="http://www.tttuangou.net" target="_blank" style="color:#999;"><b>天天团购</b></a> &nbsp;&copy; 2005 - 2011 <a href="http://cenwor.com" target="_blank" style="color:#999;"><b>Cenwor Inc.</b></a> </div> <script type="text/javascript">
/* <![CDATA[ */
$(document).ready(function(){
if ($.browser.msie)
{
$('#no_ie').show();
}
});
/* ]]> */
</script> </body> </html>