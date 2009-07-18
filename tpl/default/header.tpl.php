<?php
$webroot = Core::$webRoot;

// do User sign in only in the templates that need the user to be signed in
try
{
	Core::$user->init();
}
catch(Exception $loginerror)
{
	// Can't do redirect in the header file
}

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

		<script type="text/javascript" src="es"></script>
		<script type="text/javascript" src="fckeditor/fckeditor.js"></script>
	</head>
	<body id="grid">
		<div class="area">
			<div class="col2">
				<h1><a href="./">dWing</a></h1>
			</div>
			<div class="col2 small right">
				<?php if(Core::$user->authed): ?>
					<a href="user/<?php echo Core::$user->id; ?>"><?php echo htmlspecialchars(Core::$user->nick); ?></a>
				<?php else: ?>
					<a href="login?returnto=1"><?php echo l10n::_('sign in'); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<hr />
