<?php
include($this->template('header.tpl.php'));

// TODO: resurrect browsing using newer posts / older posts links
// TODO: better tag cloud?
// TODO: resurrect posting
// TODO: implement voting as first RESTful service
// TODO: ressurect comment posting

$iter = new NewsRange();

foreach($iter as $news):
?>
<div class="post">
	<div class="postheader">
		<h1><a href="news/<?php echo $news->fancyurl; ?>"><?php echo htmlspecialchars($news->title); ?></a></h1>
		<div class="postinfo">
			<span class="userinfo"><a href="user/<?php echo $news->user->user_id; ?>">
				<?php echo htmlspecialchars($news->user->nick); ?>
			</a></span>
			<span class="dateinfo"><?php echo Utils::relativeTime($news->time); ?></span>
			<?php
			// TODO: $news->tags, etc. accessors
			$commentNum = Comments::getCommentNum($news->id, News::ContentType);

			$thisRating = Ratings::getRating($news->id, News::ContentType);
			$idStr = 'rating-'.$news->id.'-'.News::ContentType;
			$jsParams = '\''.$idStr.'\', '.$news->id.', '.News::ContentType;
			?>
			<span class="tagsinfo">
				<?php foreach($news->tags as $tag): ?>
				<a href="news/tags/<?php echo $tag; ?>"><?php echo $tag; ?></a>
				<?php endforeach; ?>
			</span>
			<?php if($commentNum > 0): ?>
			<span class="commentinfo"><?php printf(l10n::_('%d comments'), $commentNum); ?></span>
			<?php endif; ?>
			<span class="rating score<?php echo round($thisRating['average']); ?>" id="<?php echo $idStr; ?>">
				<a href="javascript:doRating(<?php echo $jsParams; ?>, 1);">1</a>
				<a href="javascript:doRating(<?php echo $jsParams; ?>, 2);">2</a>
				<a href="javascript:doRating(<?php echo $jsParams; ?>, 3);">3</a>
				<a href="javascript:doRating(<?php echo $jsParams; ?>, 4);">4</a>
				<a href="javascript:doRating(<?php echo $jsParams; ?>, 5);">5</a>
				<span class="ratingcaption"><?php printf(l10n::_('%s ratings / %s average'), $thisRating['ratings'], round($thisRating['average'], 1)); ?></span>
			</span>
		</div>
	</div>
	<div class="postbody">
<?php echo Smilies::replace($news->text); ?>
	</div>
</div>
<?php
endforeach;
include($this->template('footer.tpl.php'));
?>
