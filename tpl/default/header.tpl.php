<?php
$webroot = $GLOBALS['webRoot'];

header('Content-Type: application/xhtml+xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$site = !empty($_GET['site']) ? $_GET['site'] : 'index';
if(empty($title))
	$title = l10n::_('Index');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title><?php echo htmlspecialchars($title); ?> - dWing</title>
		<base href="<?php echo $webroot; ?>" />
		<link href="css" type="text/css" rel="stylesheet" />

		<link rel="alternate" type="application/atom+xml" title="dWing Atom 1.0" href="atom" />
		<link rel="alternate" type="application/rss+xml" title="dWing RSS 2.0" href="rss" />
		<link rel="alternate" type="application/xml" title="dWing RDF 1.0" href="rdf" />

		<!--<script type="application/javascript" src="js/mootools.js"></script>-->
		<script type="application/javascript" src="es"></script>
	</head>
	<body id="grid">
		<!-- TODO: a new header. maybe a little SVG animation?
		<div id="header">
			<a href="#fake">Grid off</a>
			<a href="#grid">Grid on</a>
		</div>-->
