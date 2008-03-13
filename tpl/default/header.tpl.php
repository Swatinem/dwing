<?php
$webroot = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).(dirname($_SERVER['PHP_SELF']) != '/' ? '/' : '');

header('Content-Type: application/xhtml+xml');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
$site = !empty($_GET['site']) ? $_GET['site'] : 'index';
if(empty($title))
	$title = l10n::_('Index');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title><?php echo htmlspecialchars($title); ?> - dWing CMS</title>
		<base href="<?php echo $webroot; ?>" />
		<link rel="stylesheet" type="text/css" href="dwingcms.css" />
		<script type="text/javascript" src="js/mootools.js"></script>
		<script type="text/javascript" src="js/interface.js"></script>
		<script type="text/javascript" src="index.php?site=l10n"></script>
		<link rel="alternate" type="application/atom+xml" title="dWing Atom 1.0" href="atom" />
		<link rel="alternate" type="application/rss+xml" title="dWing RSS 2.0" href="rss" />
		<link rel="alternate" type="application/xml" title="dWing RDF 1.0" href="rdf" />
	</head>
	<body>
		<ul id="header">
			<?php if(!$user->authed): ?>
			<li><a href="login"><?php echo l10n::_('Sign in'); ?></a></li>
			<?php else: ?>
			<li><a href="user/<?php echo $user->user_id; ?>"><?php echo l10n::_('My Account'); ?></a></li>
			<li><a href="login?logout=1"><?php echo l10n::_('Sign out'); ?></a></li>
			<?php endif; ?>
			<li<?php if($site == 'index') echo ' class="selected"'; ?>><a href="./"><?php echo l10n::_('News'); ?></a></li>
			<?php /*
			<li<?php if($site == 'article') echo ' class="selected"'; ?>><a><?php echo l10n::_('Articles'); ?></a>
				<ul>
<?php
$articles = Article::getArticles();
foreach($articles as $article):
?>
					<li><a href="index.php?site=article&amp;art_id=<?php echo $article['art_id']; ?>"><?php echo $article['title']; ?></a></li>
<?php
endforeach;
unset($articles);
unset($article);
?>
				</ul>
			</li>
			*/ ?>
			<li<?php if($site == 'gallery') echo ' class="selected"'; ?>><a href="gallery"><?php echo l10n::_('Pictures'); ?></a></li>
			<?php /*<li<?php if($site == 'guestbook') echo ' class="selected"'; ?>><a href="guestbook"><?php echo l10n::_('Guestbook'); ?></a></li>*/ ?>
			<li id="throbber" class="active"><span id="throbbertext"><?php echo l10n::_('loading...'); ?></span></li>
		</ul>
		<div id="headershadow"></div>
		<div id="content">
