<?php
if(empty($post))
	throw new Exception('This template needs the $post variable.');
$resource = strtolower(get_class($post)).'/'.$post->id;
?>
<div class="post" id="<?php echo $resource; ?>">
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
			?>
			<span class="rating score<?php echo round($rating->average); ?>">
				<a>1</a>
				<a>2</a>
				<a>3</a>
				<a>4</a>
				<a>5</a>
				<span class="ratingcaption"><?php printf(l10n::_('%s ratings / %s average'), $rating->ratings, round($rating->average, 1)); ?></span>
			</span>
			<?php endif; ?>
			<?php if(!empty($showDelete) || false): ?>
			<span class="controls">
				<?php if(!empty($showDelete)): ?>
				<a class="delete">delete</a>
				<?php endif; ?>
			</span>
			<?php endif; ?>
		</div>
	</div>
	<div class="postbody">
<?php echo Smilies::replace($post->text); ?>
	</div>
</div>
