<?php
header('Content-Type: application/rss+xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$webroot = Core::$webRoot;
$newsall = new NewsRange();
?>
<rss version="2.0">
	<channel>
		<title>dWing News</title><?php /* TODO: l10n/customization?!? */ ?>
		<link><?php echo $webroot; ?></link>
		<description>Die Neuigkeiten von dWing.</description><?php /* TODO: l10n/customization?!? */ ?>
<?php foreach($newsall as $news): ?>
		<item>
			<title><?php echo htmlspecialchars($news->title); ?></title>
			<link><?php echo $webroot.'news/'.$news->fancyurl; ?></link>
			<guid><?php echo $webroot.'news/'.$news->fancyurl; ?></guid>
			<description><?php echo htmlspecialchars($news->text); ?></description>
		</item>
<?php endforeach; ?>
	</channel>
</rss>
