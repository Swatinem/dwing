<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$webroot = $GLOBALS['webRoot'];
$newsall = new NewsRange();
?>
<rdf:RDF
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns="http://purl.org/rss/1.0/">
	<channel rdf:about="<?php echo $webroot; ?>">
		<title>dWing News</title><?php /* TODO: l10n/customization?!? */ ?>
		<link><?php echo $webroot; ?></link>
		<description>Die Neuigkeiten von dWing.</description><?php /* TODO: l10n/customization?!? */ ?>
		<items>
			<rdf:Seq>
			<?php foreach($newsall as $news): ?>
				<rdf:li rdf:resource="<?php echo $webroot.'news/'.$news->fancyurl; ?>" />
			<?php endforeach; ?>
			</rdf:Seq>
		</items>
	</channel>
	<?php foreach($newsall as $news): ?>
	<item rdf:about="<?php echo $webroot.'news/'.$news->fancyurl; ?>">
		<title><?php echo htmlspecialchars($news->title); ?></title>
		<link><?php echo $webroot.'news/'.$news->fancyurl; ?></link>
		<description><?php echo htmlspecialchars($news->text); ?></description>
	</item>
	<?php endforeach; ?>
</rdf:RDF>
