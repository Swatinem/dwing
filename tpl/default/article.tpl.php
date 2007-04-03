<?php
$article = Article::getArticle($_GET['art_id']);
if(!empty($article))
	$title = $article['title'];
$articletemp = $article;
include($this->template('header.tpl.php'));
$article = $articletemp;
if(!$article):
	$_error = l10n::_('Article not found.');
	include($this->template('error.tpl.php'));
else:
?>
<h1><?php echo $article['title']; ?></h1>
<?php
if(count($article['pagedetails']) > 1):
?>
<div class="outertabbar flex">
	<div class="tabbar">
		<?php
		$i = 0;
		foreach($article['pagedetails'] as $page):
		$i++;
		?>
		<a href="index.php?site=article&amp;art_id=<?php echo $_GET['art_id']; ?>&amp;page=<?php echo $i; ?>"<?php if($article['curpage'] == $i-1) echo ' class="selected"'; ?>><span><?php echo $page['subtitle']; ?></span></a>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>
<?php echo Smilies::replace($article['text']); ?>
<?php endif; ?>
<?php include($this->template('footer.tpl.php')); ?>