<div class="workshops view">
<h2><?php  echo __('Workshop');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($workshop['Workshop']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($workshop['Workshop']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($workshop['User']['email'], array('controller' => 'users', 'action' => 'view', $workshop['User']['id'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Workshop'), array('action' => 'edit', $workshop['Workshop']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Workshop'), array('action' => 'delete', $workshop['Workshop']['id']), null, __('Are you sure you want to delete # %s?', $workshop['Workshop']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Workshops'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Workshop'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
