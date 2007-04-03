<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<results>
	<result success="<?php
	if($user->authed):
		Ratings::addRating($_POST['content_id'], $_POST['content_type'], $_POST['rating']);
		echo 1;
	else:
		echo 0;
	endif;
	$thisRating = Ratings::getRating($_POST['content_id'], $_POST['content_type']);
	?>" average="<?php echo $thisRating['average']; ?>"><?php printf(l10n::_('%s ratings / %s average'), $thisRating['ratings'], round($thisRating['average'], 1)); ?></result>
</results>