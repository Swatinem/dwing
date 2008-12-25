<?php
$title = l10n::_('News');
include($this->template('header.tpl.php'));

// TODO: better tag cloud?
// TODO: resurrect posting

$displayPerPage = 5;
$page = Utils::getPage();
$offset = ($page-1)*$displayPerPage;

if(!empty($requestTag))
	$iter = new NewsWithTag($requestTag, $offset, $displayPerPage);
else
	$iter = new NewsRange($offset, $displayPerPage);

foreach($iter as $post):
	include($this->template('post.tpl.php'));
endforeach;

$base = !empty($requestTag) ? 'news/tags/'.$requestTag : '';
if(($count = count($iter)) > $displayPerPage):
?>
<hr />
<div class="area">
	<div class="col2">
		<?php if($page > 1): ?>
		<a href="<?php echo $base; ?>?page=<?php echo $page-1; ?>"><?php echo l10n::_('newer posts'); ?></a>
		<?php endif; ?>
	</div>
	<div class="col2 right">
		<?php if($count > $page*$displayPerPage): ?>
		<a href="<?php echo $base; ?>?page=<?php echo $page+1; ?>"><?php echo l10n::_('older posts'); ?></a>
		<?php endif; ?>
	</div>
</div>
<?php
endif;
include($this->template('footer.tpl.php'));
?>
