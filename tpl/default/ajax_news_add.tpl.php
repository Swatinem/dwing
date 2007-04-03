<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Admin::checkRight('news');
?>
<results>
<?php
if(!is_a($newsId = News::addNews(), 'Exception')):
?>
	<result success="1">
	<?php $news = News::getNewsAllDetails($newsId); ?>
	<div xmlns="http://www.w3.org/1999/xhtml" class="post">
		<div class="dateinfo">
			<span class="month"><?php echo utf8_encode(strftime('%B', $news['time'])); ?></span>
			<span class="day"><?php echo strftime('%d', $news['time']); ?></span>
			<span class="year"><?php echo strftime('%Y', $news['time']); ?></span>
		</div>
		<?php
		//$thisRating = Ratings::getRating($news['news_id'], ContentType::NEWS);
		$idStr = 'rating-'.$news['news_id'].'-'.ContentType::NEWS;
		$jsParams = '\''.$idStr.'\', '.$news['news_id'].', '.ContentType::NEWS;
		?>
		<div class="rating" id="<?php echo $idStr; ?>">
			<div style="width: 0px;"></div>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 1);">1</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 2);">2</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 3);">3</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 4);">4</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 5);">5</a>
		</div>
		<span class="ratingcaption"><?php printf(l10n::_('%s ratings / %s average'), 0, 0); ?></span>
		<h2><a href="news/<?php echo $news['fancyurl']; ?>"><?php echo htmlspecialchars($news['title']); ?></a></h2>
		<div class="postinfo">
		<?php
		$tags = $news['tags'];
		foreach($tags as $tag):
		?>
		<a href="news/tags/<?php echo $tag['tag_id']; ?>"><?php echo $tag['name']; ?></a>
		<?php
		endforeach;
		/*$commentNum = Comments::getCommentNum($news['news_id'], ContentType::NEWS);
		if($commentNum > 0):
			printf(l10n::_('? %d comments'), $commentNum);
		endif;*/
		?>
		</div>
		<div class="postbody">
<?php echo Smilies::replace($news['text']); ?>
		</div>
	</div>
	</result>
<?php
else:
?>
	<result success="0"><?php echo $newsId->getMessage(); ?></result>
<?php
endif;
?>
</results>