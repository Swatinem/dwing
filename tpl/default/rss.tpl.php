<?php
header('Content-Type: application/rss+xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$webroot = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).(dirname($_SERVER['PHP_SELF']) != '/') ? '/' : '';
$newsall = News::getNews(10, !empty($_GET['tag']) ? $_GET['tag'] : null);
/* do we need the date? <?php echo strftime('%Y/%m/%d', $news['start']); ?>: */
?>
<rss version="2.0">
	<channel>
		<title>dWing News</title><?php /* l10n/customization?!? */ ?>
		<link><?php echo $webroot; ?></link>
		<description>Die Neuigkeiten von dWing.</description><?php /* l10n/customization?!? */ ?>
<?php foreach($newsall as $news): ?>
		<item>
			<title><?php echo htmlspecialchars($news['title']); ?></title>
			<link><?php echo $webroot.'news/'.$news['fancyurl']; ?></link>
			<guid><?php echo $webroot.'news/'.$news['fancyurl']; ?></guid>
			<description><?php echo htmlspecialchars(nl2br($news['text'])); ?></description>
		</item>
<?php endforeach; ?>
	</channel>
</rss>
