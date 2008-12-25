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
			<span class="userinfo"><a href="user/<?php echo $user->id; ?>">
				<?php echo htmlspecialchars($user->nick); ?>
			</a></span>
			<?php
			endif;
			if(!empty($post->time)):
			?>
			<span class="dateinfo"><?php echo Utils::relativeTime($post->time); ?></span>
			<?php
			endif;
			$tags = $post->tags;
			if(!empty($tags)):
			?>
			<span class="tagsinfo">
				<?php foreach($tags as $tag): ?>
				<a href="news/tags/<?php echo $tag; ?>"><?php echo $tag; ?></a>
				<?php endforeach; ?>
			</span>
			<?php
			endif;
			if(($commentNum = count($post->comments)) > 0):
			?>
			<span class="commentinfo"><?php printf(l10n::_('%d comments'), $commentNum); ?></span>
			<?php
			endif;
			if(($rating = $post->rating) != null):
			$resource = strtolower(get_class($post)).'/'.$post->id.'/rating';
			?>
			<span class="rating score<?php echo round($rating['average']); ?>" id="<?php echo $resource; ?>">
				<a href="javascript:vote('<?php echo $resource; ?>', 1);">1</a>
				<a href="javascript:vote('<?php echo $resource; ?>', 2);">2</a>
				<a href="javascript:vote('<?php echo $resource; ?>', 3);">3</a>
				<a href="javascript:vote('<?php echo $resource; ?>', 4);">4</a>
				<a href="javascript:vote('<?php echo $resource; ?>', 5);">5</a>
				<span class="ratingcaption"><?php printf(l10n::_('%s ratings / %s average'), $rating['ratings'], round($rating['average'], 1)); ?></span>
			</span>
			<?php endif; ?>
		</div>
	</div>
	<div class="postbody">
<?php echo Smilies::replace($post->text); ?>
	</div>
</div>
