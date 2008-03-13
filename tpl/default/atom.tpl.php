<?php
header('Content-Type: application/atom+xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$webroot = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).(dirname($_SERVER['PHP_SELF']) != '/') ? '/' : '';
$newsall = News::getNews(10, !empty($_GET['tag']) ? $_GET['tag'] : null);
?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>dWing News</title><?php /* l10n/customization?!? */ ?>
	<link href="<?php echo $webroot; ?>" />
	<link rel="self" href="<?php echo $webroot; ?>index.php?site=atom" />
	<subtitle>Die Neuigkeiten von dWing.</subtitle><?php /* l10n/customization?!? */ ?>
	<id><?php echo $webroot; ?></id>
	<updated><?php echo strftime('%Y-%m-%dT%H:%M:%SZ', $newsall[0]['time']); ?></updated>
	<?php foreach($newsall as $news): ?>
	<entry>
		<title><?php echo htmlspecialchars($news['title']); ?></title>
		<author>
			<name><?php echo htmlspecialchars($news['user']->nick); ?></name>
		</author>
		<link href="<?php echo $webroot.'news/'.$news['fancyurl']; ?>" />
		<updated><?php echo strftime('%Y-%m-%dT%H:%M:%SZ', $news['time']); ?></updated>
		<content type="html"><?php echo htmlspecialchars(nl2br($news['text'])); ?></content>
		<id><?php echo $webroot.'news/'.$news['fancyurl']; ?></id>
	</entry>
	<?php endforeach; ?>
</feed>
