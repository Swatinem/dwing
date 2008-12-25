<?php
$title = $object->title;
include($this->template('header.tpl.php'));

$post = $object;
	include($this->template('post.tpl.php'));
?>
<hr />
<div class="area">
	<h1><?php echo l10n::_('Comments'); ?></h1>
</div>
<?php
foreach($object->comments as $post):
	include($this->template('post.tpl.php'));
endforeach;
// TODO: better post style
?>
<hr />
<div class="area">
	<h1><?php echo l10n::_('Write comment'); ?></h1>
	<?php if(Core::$user->authed): ?>
	<form action="news/<?php echo $object->fancyurl; ?>/comment" method="post" id="commentform">
		<textarea id="commenttext"></textarea>
		<input type="submit" />
	</form>
	<?php else: ?>
	<p><?php echo l10n::_('You need to <a href="login?returnto=1">sign in</a> first.'); ?></p>
	<?php endif; ?>
</div>
<?php include($this->template('footer.tpl.php'));?>
