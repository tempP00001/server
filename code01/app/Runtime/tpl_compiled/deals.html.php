<?php echo $this->fetch('inc/header.html'); ?> 
<div id="bdw" class="bdw">
	<div id="bd" class="cf">
		<div id="deal-default">
			<?php if ($this->_var['deal_cate_list']): ?>
			<div id="dashboard" class="dashboard cf">
					<ul>
						<?php $_from = $this->_var['deal_cate_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cate');if (count($_from)):
    foreach ($_from AS $this->_var['cate']):
?>
						<li <?php if ($this->_var['cate']['current'] == 1): ?>class="current"<?php endif; ?>><a href="<?php echo $this->_var['cate']['url']; ?>"><?php echo $this->_var['cate']['name']; ?></a>
						<span></span>
						</li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>													
					</ul>
			</div>
			<?php endif; ?>
			<div id="content" class="cf">
					
			
	
			<div class="box" id="g_recent">
				
				<div class="box">
					<div class="box-top"></div>
					<div class="box-content">
						<div class="head">
							<h2><?php echo $this->_var['page_title']; ?></h2>
						</div>
						<div class="sect">
							<ul>
								<?php $_from = $this->_var['deals']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'deal');if (count($_from)):
    foreach ($_from AS $this->_var['deal']):
?>
								<li>
									
									<div class="date">
									
									<?php if ($this->_var['deal']['time_status'] == 0): ?>
											<div class="d_y"><?php 
$k = array (
  'name' => 'to_date',
  'value' => $this->_var['deal']['begin_time'],
  'format' => 'Y-m-d',
);
echo $k['name']($k['value'],$k['format']);
?></div>
											<div class="d_d"><?php 
$k = array (
  'name' => 'to_date',
  'value' => $this->_var['deal']['begin_time'],
  'format' => 'd',
);
echo $k['name']($k['value'],$k['format']);
?></div>
											<div class="d_r"><?php echo $this->_var['LANG']['WEEK_'.to_date($this->_var['deal']['begin_time'],'w')];?></div>
									<?php else: ?>
										<?php if ($this->_var['deal']['end_time'] > 0): ?>
										<div class="d_y"><?php 
$k = array (
  'name' => 'to_date',
  'value' => $this->_var['deal']['end_time'],
  'format' => 'Y-m-d',
);
echo $k['name']($k['value'],$k['format']);
?></div>
										<div class="d_d"><?php 
$k = array (
  'name' => 'to_date',
  'value' => $this->_var['deal']['end_time'],
  'format' => 'd',
);
echo $k['name']($k['value'],$k['format']);
?></div>
										<div class="d_r"><?php echo $this->_var['LANG']['WEEK_'.to_date($this->_var['deal']['end_time'],'w')];?></div>
										<?php else: ?>
										<div class="d_y">&nbsp;</div>
										<div class="d_d"></div>
										<div class="d_r"><?php echo $this->_var['LANG']['NO_END_TIME']; ?></div>
										<?php endif; ?>
									<?php endif; ?>
									</div>
									
									<div class="picture">
										<div class="p-box">
										<?php if ($this->_var['deal']['buy_status'] == 2): ?>
											<div class="soldout"></div>										
										<?php elseif ($this->_var['deal']['time_status'] == 1): ?>
											<div class="insale"></div>
										<?php endif; ?>
										
										<a target="_blank" title="<?php echo $this->_var['deal']['name']; ?>" href="<?php echo $this->_var['deal']['url']; ?>" class="soldoutlink"><?php echo $this->_var['deal']['name']; ?></a>
										<a target="_blank" title="<?php echo $this->_var['deal']['name']; ?>" href="<?php echo $this->_var['deal']['url']; ?>">
											<img height="121" width="200" src="<?php echo $this->_var['deal']['icon']; ?>" alt="<?php echo $this->_var['deal']['name']; ?>">
										</a>
										</div>
										<div class="p-button">
											<a href="<?php echo $this->_var['deal']['url']; ?>"><img src="<?php echo $this->_var['TMPL']; ?>/images/show.gif"></a>
										</div>
										<div class="p-comment">
											<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'message#deal',
  'id' => $this->_var['deal']['id'],
);
echo $k['name']($k['value'],$k['id']);
?>"><?php echo $this->_var['LANG']['ADD_FIRST_MESSAGE']; ?></a>
										</div>
									</div>
									<div class="info">
										<div class="tit"><a href="<?php echo $this->_var['deal']['url']; ?>"><?php echo $this->_var['deal']['name']; ?></a></div>
										<div class="gmsll">
											<p><?php echo $this->_var['deal']['deal_success_num']; ?></p>
											<div class="blank1"></div>
											<p>
												
												<?php echo $this->_var['LANG']['ORIGIN_PRICE']; ?>：<span class="bod"><?php echo $this->_var['deal']['origin_price_format']; ?></span>&nbsp;&nbsp;&nbsp;
												<?php echo $this->_var['LANG']['CURRENT_PRICE']; ?>：<span class="bod"><?php echo $this->_var['deal']['current_price_format']; ?></span>
												<span class="red">&nbsp;</span><?php echo $this->_var['LANG']['DISCOUNT']; ?>：<span class="bod"><?php echo $this->_var['deal']['discount']; ?><?php echo $this->_var['LANG']['DISCOUNT_OFF']; ?></span>
											</p>
											<div class="clear"></div>
										</div>
										<div class="jiesheng"><?php echo $this->_var['LANG']['SAVE_PRICE_TOTAL']; ?><span class="red"><?php echo format_price($this->_var['deal']['save_price']*$this->_var['deal']['buy_count']);?></span></div>
										<div class="miaoshu"><p>
										<?php echo $this->_var['deal']['brief']; ?>
										</p></div>
									</div>
									<div class="blank"></div>
								</li>	
							<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
							<?php unset($this->_var['deal']);?>
							</ul>
							<div class="clear"></div>
							
							<div class="pages"><?php echo $this->_var['pages']; ?></div>
							
						</div>
					</div>
					<div class="box-bottom"></div>
				</div>
			</div></div>
			<?php echo $this->fetch('inc/side.html'); ?> 
	</div>
	<!-- bd end -->
</div>

<?php echo $this->fetch('inc/footer.html'); ?>