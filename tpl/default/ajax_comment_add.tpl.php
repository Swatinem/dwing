<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<results>
	<result success="<?php
	if($user->authed):
		$commentId = Comments::addComment($_POST['content_id'], $_POST['content_type']);
		if($commentId)
			echo 1;
		else
			echo 0;
	else:
		echo 0;
	endif;
	?>">
<?php if(!empty($commentId)): ?>
	<div xmlns="http://www.w3.org/1999/xhtml" class="post">
		<div class="dateinfo">
			<span class="month"><?php echo strftime('%B', time()); ?></span>
			<span class="day"><?php echo strftime('%d', time()); ?></span>
			<span class="year"><?php echo strftime('%Y', time()); ?></span>
		</div>
		<?php
		//$thisRating = Ratings::getRating($commentId, ContentType::COMMENT);
		$idStr = 'rating-'.$commentId.'-'.ContentType::COMMENT;
		$jsParams = '\''.$idStr.'\', '.$commentId.', '.ContentType::COMMENT;
		?>
		<div class="rating" id="<?php echo $idStr; ?>">
			<div style="width: 0px;"></div>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 1);">1</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 2);">2</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 3);">3</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 4);">4</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 5);">5</a>
		</div>
		<span class="ratingcaption"><?php printf(l10n::_('%s ratings / %s average'), 0, round(0, 1)); ?></span>
		<h2><a href="user/<?php echo $user->user_id; ?>"><?php echo htmlspecialchars($user->nick); ?></a></h2>
		<div class="postbody">
<?php echo Smilies::replace(Utils::purify($_POST['text'])); ?>
		</div>
	</div>
<?php endif; ?>
</result>
</results>
