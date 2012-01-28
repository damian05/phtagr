<h1><?php echo __("Path settings"); ?></h1>

<?php echo $this->Session->flash(); ?>

<?php if (count($missing)): ?>
<div class="error">
<?php echo __("Following paths are missing. Please create these paths!"); ?>
</div>

<ul>
<?php foreach($missing as $path): ?>
  <li><?php echo $path; ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if (count($readonly)): ?>
<div class="error">
<?php echo __("Following paths are not writeable. Please change the permissions!"); ?>
</div>

<ul>
<?php foreach($readonly as $path): ?>
  <li><?php echo $path; ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php echo $this->Html->link(__('Retry', true), 'path', array('class' => 'button')); ?>
<?php
  $script = <<<'JS'
(function($) {
  $(document).ready(function() {
    $('.button').button();
  });
})(jQuery);
JS;
  echo $this->Html->scriptBlock($script, array('inline' => false));
?>