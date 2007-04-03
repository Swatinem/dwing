<?php
$title = l10n::_('Links');
include($this->template('header.tpl.php'));
?>
<h1><?php echo l10n::_('Links'); ?></h1>
<dl>
<?php
$links = Affiliate::getTextLinks();
foreach($links as $link):
?>
	<dt>
		<a href="index.php?site=out&amp;site_id=<?php echo $link['site_id']; ?>"><?php echo $link['name']; ?></a>
		<?php if($user->hasright('admin')): ?>
		<span><?php echo sprintf(l10n::_('(%d clicks)'), $link['clicks_out']); ?></span>
		<?php endif; ?>
	</dt>
	<dd><?php echo $link['text']; ?></dd>
<?php endforeach; ?>
</dl>
<?php include($this->template('footer.tpl.php')); ?>