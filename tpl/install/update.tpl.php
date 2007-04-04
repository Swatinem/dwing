<?php
header('Content-Type: application/xhtml+xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title>dWing CMS Update</title>
		<link rel="stylesheet" type="text/css" href="dwingcms.css" />
		<script type="text/javascript" src="js/prototype.js"></script>
		<script type="text/javascript" src="js/moo.fx.js"></script>
		<script type="text/javascript" src="js/moo.fx.pack.js"></script>
		<script type="text/javascript" src="js/interface.js"></script>
	</head>
	<body>
		<ul id="header">
			<li class="selected"><a>Update</a></li>
		</ul>
		<div id="headershadow"></div>
		<div id="content">

<?php if(!$user->authed || !$user->hasRight('admin')): ?>
<div class="openid">
<h3>Admin benötigt</h3>
Es ist ein Update von der derzeit benutzen Version <?php echo $updater->oldversion; ?>
auf die Version <?php echo $updater->version; ?> verfügbar. Es werden allerdings
Adminrechte benötigt um das Update zu starten.
</div>
<?php if($user->authed && !$user->hasRight('admin')): ?>
<ul class="warning">
	<li>Du bist bereits angemeldet, hast aber keine Adminrechte.</li>
</ul>
<?php endif; ?>
<form action="./" method="post">
<h2>Anmeldung mit deiner OpenID</h2>
<div class="openid">
<input type="text" name="openid_url" /><input type="submit" value="Login"/>
<h3>Beispiele:</h3>
<ul>
	<li>http://deineadresse.de</li>
	<li>http://deinname.myopenid.com</li>
	<li>http://deinname.livejournal.com</li>
	<li>http://openid.aol.com/deinAIMname</li>
</ul>
</div>
</form>
<?php if(!empty($loginerror)): ?>
<ul class="warning">
	<li><?php echo htmlspecialchars($loginerror); ?></li>
</ul>
<?php endif; ?>
<?php else: ?>
<?php if(empty($_GET['doupdate'])): ?>
<div class="openid">
<h3>Update verfügbar</h3>
Es ist ein Update von der derzeit benutzen Version <?php echo $updater->oldversion; ?> 
auf die Version <?php echo $updater->version; ?> verfügbar.<br />
<a href="?doupdate=1">Update jetzt durchführen</a>
</div>
<?php else: ?>
<?php if($updater->update()): ?>
<div class="openid">
<h3>Update erfolgreich</h3>
Das Update von Version <?php echo $updater->oldversion; ?> 
auf die Version <?php echo $updater->version; ?> wurde erfolgreich durchgeführt.
<br /><a href="./">Zurück zur Startseite</a>
</div>
<?php endif; // update successful? ?>
<?php endif; // doupdate? ?>
<?php endif; // user signed in? ?>

		</div>
		<div id="footer">
			© 2006-2007 dWing CMS<br />
			Valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a>
			and <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
		</div>
	</body>
</html>