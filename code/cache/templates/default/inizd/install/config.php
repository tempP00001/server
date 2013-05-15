<? include handler('template')->file('@inizd/install/header'); ?>
<div class="setup step3">
<h2>基本设置</h2>
<p>配置网站基本信息</p>
</div>
<div class="stepstat">
<ul>
<li class="unactivated">1</li>
<li class="unactivated">2</li>
<li class="current">3</li>
<li class="unactivated last">4</li>
</ul>
<div class="stepstatbg stepstat3"></div>
</div>
</div> 
<form action="?mod=install&code=config&op=save" method="post" >
<input type="hidden" name="FORMHASH" value='<?=FORMHASH?>'/>
<div class="main">
<h2 class="title">基本设置</h2>
<table class="tb" style="margin:20px 0 20px 55px;">
<tr> 
<td>站点名称</td>
<td class="padleft">
<input name="c[sitename]" type="text" value="天天团购系统" />
</td>
</tr>
</table>
<h2 class="title">管理员帐号</h2>
<table class="tb" style="margin:20px 0 20px 55px;">
<tr> 
<td>帐号</td>
<td class="padleft">
<input name="c[username]" type="text" value="admin" />
</td>
</tr>
<tr> 
<td>密码</td>
<td class="padleft">
<input name="c[password]" type="password" value="" />
</td>
</tr>
<tr> 
<td>重复密码</td>
<td class="padleft">
<input name="c[repassword]" type="password" value="" />
</td>
</tr>
<tr> 
<td>邮箱</td>
<td class="padleft">
<input name="c[email]" type="text" value="" />
</td>
</tr>
</table>
<h2 class="title">附加选项</h2>
<table class="tb" style="margin:20px 0 20px 55px;">
<tr> 
<td>测试数据</td>
<td class="padleft">
<input name="c[test]" type="checkbox" value="yes" checked="checked" />
安装测试数据，方便体验（后台可一键清理测试数据）
</td>
</tr>
</table>
<div class="btnbox marginbot">
<input type="button" onclick="history.back();" value="上一步" />
<input type="submit" value="下一步" />
</div>
</div>
</form>
<?=ui('loader')->js('#inizd/install/js/step.config')?>
<? include handler('template')->file('@inizd/install/footer'); ?>