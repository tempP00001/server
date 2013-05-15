<? include handler('template')->file('@inizd/install/header'); ?>
<div class="setup step1">
<h2>开始安装</h2>
<p>环境以及文件目录权限检查</p>
</div>
<div class="stepstat">
<ul>
<li class="current">1</li>
<li class="unactivated">2</li>
<li class="unactivated">3</li>
<li class="unactivated last">4</li>
</ul>
<div class="stepstatbg stepstat1"></div>
</div>
</div> 
<div class="main">
<h2 class="title">环境检查</h2>
<table class="tb" style="margin:20px 0 20px 55px;">
<tr> 
<th>项目</th>
<th class="padleft">天天所需配置</th>
<th class="padleft">天天最佳配置</th>
<th class="padleft">当前服务器</th>
</tr>
<tr>
<td>操作系统</td>
<td class="padleft">不限制</td>
<td class="padleft">类UNIX</td>
<td class="<? echo $env['phpv']['sp'] ? 'w' : 'nw'; ?> pdleft1"><?=$env['os']['val']?></td>
</tr>
<tr>
<td>PHP版本</td>
<td class="padleft">5.x</td>
<td class="padleft">5.2+</td>
<td class="<? echo $env['phpv']['sp'] ? 'w' : 'nw'; ?> pdleft1"><?=$env['phpv']['val']?></td>
</tr>
<tr>
<td>附件上传</td>
<td class="padleft">不限制</td>
<td class="padleft">2M</td>
<td class="<? echo $env['upload']['sp'] ? 'w' : 'nw'; ?> pdleft1"><?=$env['upload']['val']?></td>
</tr>
<tr>
<td>GD 库</td>
<td class="padleft">不限制</td>
<td class="padleft">2.0</td>
<td class="<? echo $env['gd']['sp'] ? 'w' : 'nw'; ?> pdleft1"><?=$env['gd']['val']?></td>
</tr>
<tr>
<td>磁盘空间</td>
<td class="padleft">10M</td>
<td class="padleft">不限制</td>
<td class="<? echo $env['space']['sp'] ? 'w' : 'nw'; ?> pdleft1"><?=$env['space']['val']?></td>
</tr>
</table>
<h2 class="title">目录、文件权限检查</h2>
<table class="tb" style="margin:20px 0 20px 55px;width:90%;">
<tr>
<th>目录文件</th>
<th class="padleft">所需状态</th>
<th class="padleft">当前状态</th>
</tr>
<? if(is_array($permissions)) { foreach($permissions as $i => $one) { ?>
<tr>
<td><?=$one['path']?></td>
<td class="w pdleft1">可写</td>
<td class="<? echo $one['rw'] ? 'w' : 'nw'; ?> pdleft1"><? echo $one['rw'] ? '可写' : '不可写'; ?></td>
</tr>
<? } } ?>
</table>
<h2 class="title">所需函数支持</h2>
<table class="tb" style="margin:20px 0 20px 55px;width:90%;">
<tr>
<th>函数名称</th>
<th class="padleft">检查结果</th>
</tr>
<? if(is_array($function)) { foreach($function as $i => $one) { ?>
<tr>
<td><?=$one['name']?></td>
<td class="<? echo $one['sp'] ? 'w' : 'nw'; ?> pdleft1"><? echo $one['sp'] ? '支持' : '不支持'; ?></td>
</tr>
<? } } ?>
</table>
<div class="btnbox marginbot">
<input type="button" onclick="history.back();" value="上一步" />
<input type="submit" value="下一步" onclick="javascript:sub2next();" />
</div>
</div>
<?=ui('loader')->js('#inizd/install/js/step.env')?>
<? include handler('template')->file('@inizd/install/footer'); ?>