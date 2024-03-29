<?php
header('Content-Type: application/atom+xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$webroot = Core::$webRoot;
$newsall = new NewsRange();
?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>dWing News</title><?php /* TODO: l10n/customization?!? */ ?>
	<link href="<?php echo $webroot; ?>" />
	<link rel="self" href="<?php echo $webroot; ?>atom" />
	<subtitle>Die Neuigkeiten von dWing.</subtitle><?php /* TODO: l10n/customization?!? */ ?>
	<id><?php echo $webroot; ?></id>
	<updated><?php echo strftime('%Y-%m-%dT%H:%M:%SZ', $newsall->current()->time); ?></updated>
	<?php foreach($newsall as $news): ?>
	<entry>
		<title><?php echo htmlspecialchars($news->title); ?></title>
		<author>
			<name><?php echo htmlspecialchars($news->user->nick); ?></name>
		</author>
		<link href="<?php echo $webroot.'news/'.$news->fancyurl; ?>" />
		<updated><?php echo strftime('%Y-%m-%dT%H:%M:%SZ', $news->time); ?></updated>
		<content type="html"><?php echo htmlspecialchars($news->text); ?></content>
		<id><?php echo $webroot.'news/'.$news->fancyurl; ?></id>
	</entry>
	<?php endforeach; ?>
</feed>
