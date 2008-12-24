<?php
if(empty($post))
	throw new Exception('This template needs the $post variable.');
?>
<div class="post">
	<div class="postheader">
		<?php if(!empty($post->fancyurl) && !empty($post->title)): ?>
		<h1><a href="news/<?php echo $post->fancyurl; ?>"><?php echo htmlspecialchars($post->title); ?></a></h1>
		<?php endif; ?>
		<div class="postinfo">
			<?php if(($user = $post->user) != null): ?>
			<span class="userinfo"><a href="user/<?php echo $user->user_id; ?>">
				<?php echo htmlspecialchars($user->nick); ?>
			</a></span>
			<?php endif; ?>
			<?php if(!empty($post->time)): ?>
			<span class="dateinfo"><?php echo Utils::relativeTime($post->time); ?></span>
			<?php endif; ?>
			<?php
			// TODO: $news->tags, etc. accessors
			$thisRating = Ratings::getRating($post->id, News::ContentType);
			$idStr = 'rating-'.$post->id.'-'.News::ContentType;
			$jsParams = '\''.$idStr.'\', '.$post->id.', '.News::ContentType;
			$tags = $post->tags;
			if(!empty($tags)):
			?>
			<span class="tagsinfo">
				<?php foreach($tags as $tag): ?>
				<a href="news/tags/<?php echo $tag; ?>"><?php echo $tag; ?></a>
				<?php endforeach; ?>
			</span>
			<?php endif; ?>
			<?php if(($commentNum = count($post->comments)) > 0): ?>
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
<?php echo Smilies::replace($post->text); ?>
	</div>
</div>
