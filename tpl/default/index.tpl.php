<?php
$title = l10n::_('News');
include($this->template('header.tpl.php'));

// TODO: better tag cloud?
// TODO: better post style, even without FCKeditor
if(Core::$user->hasRight('news')):
?>
<div class="area">
	<h1><a href="" id="writenews"><?php echo l10n::_('Write news'); ?></a></h1>
	<form action="news" method="post" id="newsform">
		<div class="post">
			<div class="postheader">
				<input type="text" id="newstitle" placeholder="<?php echo l10n::_('Title'); ?>" /><br />
				<input type="test" id="newstags" placeholder="<?php echo l10n::_('tags'); ?>" />
			</div>
		</div>
		<textarea id="newstext"></textarea>
		<input id="newsformsubmit" type="submit" value="<?php echo l10n::_('Publish'); ?>" />
	</form>
</div>
<hr />
<div id="newnews"></div>
<?php
endif;
$displayPerPage = 5;
$page = Utils::getPage();
$offset = ($page-1)*$displayPerPage;

if(!empty($requestTag))
	$iter = new NewsWithTag($requestTag, $offset, $displayPerPage);
else
	$iter = new NewsRange($offset, $displayPerPage);

$showDelete = Core::$user->hasRight('news');
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
