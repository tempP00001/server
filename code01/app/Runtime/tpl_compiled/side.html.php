<div id="sidebar">
<?php if ($this->_var['deal_city']['notice']): ?>

<?php echo $this->fetch('inc/side_notice.html'); ?>
<div class="blank"></div>
<?php endif; ?>
<?php if ($this->_var['side_deal_list']): ?>

<?php echo $this->fetch('inc/side_deal.html'); ?>
<div class="blank"></div>
<?php endif; ?>
<adv adv_id="right_adv" />
<?php if ($this->_var['deal']['is_referral'] == 1): ?>

<?php echo $this->fetch('inc/side_referrals.html'); ?>
<div class="blank"></div>
<?php endif; ?>

<?php echo $this->fetch('inc/side_message.html'); ?>
<div class="blank"></div>

<?php echo $this->fetch('inc/side_submit.html'); ?>
<?php if ($this->_var['vote']): ?>
<div class=blank></div>

<?php echo $this->fetch('inc/side_vote.html'); ?>
<?php endif; ?>
<div class=blank></div>

<?php echo $this->fetch('inc/side_supplier.html'); ?>
</div>