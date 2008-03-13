<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$webroot = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).(dirname($_SERVER['PHP_SELF']) != '/' ? '/' : '');
$newsall = News::getNews(10, !empty($_GET['tag']) ? $_GET['tag'] : null);
/* do we need the date? <?php echo strftime('%Y/%m/%d', $news['start']); ?>: */
?>
<rdf:RDF
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns="http://purl.org/rss/1.0/">
	<channel rdf:about="<?php echo $webroot; ?>">
		<title>dWing News</title><?php /* l10n/customization?!? */ ?>
		<link><?php echo $webroot; ?></link>
		<description>Die Neuigkeiten von dWing.</description><?php /* l10n/customization?!? */ ?>
		<items>
			<rdf:Seq>
			<?php foreach($newsall as $news): ?>
				<rdf:li rdf:resource="<?php echo $webroot.'news/'.$news['fancyurl']; ?>" />
			<?php endforeach; ?>
			</rdf:Seq>
		</items>
	</channel>
	<?php foreach($newsall as $news): ?>
	<item rdf:about="<?php echo $webroot.'news/'.$news['fancyurl']; ?>">
		<title><?php echo htmlspecialchars($news['title']); ?></title>
		<link><?php echo $webroot.'news/'.$news['fancyurl']; ?></link>
		<description><?php echo htmlspecialchars(nl2br($news['text'])); ?></description>
	</item>
	<?php endforeach; ?>
</rdf:RDF>
