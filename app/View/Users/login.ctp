<div class="users form">
<?php echo $this->Form->create('User', array('action' => 'login'));?>
	<fieldset>
		<legend><?php echo __('Login User'); ?></legend>
	<?php
		echo $this->Form->input('email');
		echo $this->Form->input('password');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
