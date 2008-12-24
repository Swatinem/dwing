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
<hr />
<?php
foreach($object->comments as $post):
	include($this->template('post.tpl.php'));
endforeach;

// TODO: resurrect comment posting

include($this->template('footer.tpl.php'));
?>
