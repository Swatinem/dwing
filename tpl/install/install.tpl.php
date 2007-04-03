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

<div id="formbox">
	<div id="formbg">
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="opensubmitbutton">Zur&uuml;ck</div>
	</div>
</div>
<script type="text/javascript"><![CDATA[
window.addEventListener('load', function () {
	addNotice('Installer broken. Needs to be rewritten.');
}, false);
]]></script>

		</div>
		<div id="footer">
			&copy; 2006 dWing CMS<br />
			Valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a>
			and <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
		</div>
	</body>
</html>
<?php
exit;
/*
<?php if(!empty($_GET['action']) && $_GET['action'] == 'checkdb'):
	header('Content-Type: application/xml');
	echo '<?xml version="1.0" encoding="utf-8"?>';
	?>
	<results>
	<?php if($install->trydb()): ?>
	<?php $install->writeconfig(); ?>
		<result success="1"></result>
	<?php else: ?>
		<result success="0"></result>
	<?php endif; ?>
	</results>
<?php elseif(!empty($_GET['action']) && $_GET['action'] == 'addadmin'):
	header('Content-Type: application/xml');
	echo '<?xml version="1.0" encoding="utf-8"?>';
	?>
	<results>
	<?php if($install->mkadmin()): ?>
		<result success="1"></result>
	<?php else: ?>
		<result success="0"><?php echo $this->error; ?></result>
	<?php endif; ?>
	</results>
<?php else: ?>
<?php
header('Content-Type: application/xhtml+xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title>dWing CMS Install</title>
		<link rel="stylesheet" type="text/css" href="dwingcms.css" />
		<script type="text/javascript" src="js/prototype.js"></script>
		<script type="text/javascript" src="js/moo.fx.js"></script>
		<script type="text/javascript" src="js/moo.fx.pack.js"></script>
		<script type="text/javascript" src="js/interface.js"></script>
	</head>
	<body>
		<ul id="header">
			<li class="selected"><a>Install</a></li>
			<li id="throbber"><span id="throbbertext">L&auml;dt...</span></li>
		</ul>
		<div id="headershadow"></div>
		<div id="content">

<?php if(empty($_GET['step'])): ?>

<div id="formbox">
	<div id="formbg">
		<?php $reqs = $install->check_requirements(); ?>
		<p>
			Vor der Installation m&uuml;ssen einige Schreibrechte auf dem Server eingestellt werden.<br />
			Folgende Ordner m&uuml;ssen mit Schreibrecht (CHMOD 777) versehen werden.
		</p>
		<p>
			Schreibrechte in <strong>./inc</strong> sind nur f&uuml;r die Dauer der Installation zu
			vergeben. Danach kann dem Ordner bis auf die <strong>./inc/settings.php</strong> das Schreibrecht
			wieder entzogen werden (CHMOD 644).
		</p>
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<?php if($reqs['all']): ?>
			<div id="opensubmitbutton">Weiter</div>
		<?php else: ?>
			<div id="opensubmitbutton">Nochmals pr&uuml;fen</div>
		<?php endif; ?>
	</div>
</div>
<script type="text/javascript"><![CDATA[
window.addEventListener('load', function () {
<?php if($reqs['inc']): ?>
	addNotice('Schreibrechte in "./inc" vorhanden.');
<?php else: ?>
	addWarning('Es fehlen die Schreibrechte in "./inc".');
<?php endif; ?>

<?php if($reqs['pictures']): ?>
	addNotice('Schreibrechte in "./images/pictures" vorhanden.');
<?php else: ?>
	addWarning('Es fehlen die Schreibrechte in "./images/pictures".');
<?php endif; ?>

<?php if($reqs['thumbs']): ?>
	addNotice('Schreibrechte in "./images/thumbs" vorhanden.');
<?php else: ?>
	addWarning('Es fehlen die Schreibrechte in "./images/thumbs".');
<?php endif; ?>

<?php if($reqs['mysqli']): ?>
	addNotice('MySQLi (Version <?php echo $reqs['mysqli']; ?>) vorhanden.');
<?php else: ?>
	addWarning('Es wird MySQLi (>= 5.0.6) vorrausgesetzt.');
<?php endif; ?>

<?php if($reqs['all']): ?>
	$('opensubmitbutton').addEventListener('click', function () { document.location.href = 'install.php?step=1'; }, false);
<?php else: ?>
	$('opensubmitbutton').addEventListener('click', function () { document.location.href = 'install.php'; }, false);
<?php endif; ?>
}, false);
]]></script>

<?php elseif($_GET['step'] == 1): ?>

<div id="formbox">
	<div id="formbg">
		<p>
			Im ersten Schritt geben Sie bitte die Zugangsdaten zu ihrem MySQL Server ein.
		</p>
		<div id="formcontent">
			<form action="" method="post" id="install">
				<fieldset>
					<legend>MySQL Zugangsdaten</legend>
					<label><input type="text" name="server" id="server" value="localhost" />Server</label>
					<label><input type="text" name="user" id="user" value="root" />Benutzer</label>
					<label><input type="text" name="password" id="password" value="" />Passwort</label>
					<label><input type="text" name="database" id="database" value="dwingcms" />Datenbank</label>
					<label><input type="text" name="prefix" id="prefix" value="dw_" />Prefix</label>
					<input type="submit" value="Datenbankverbindung pr&uuml;fen" class="fake" />
				</fieldset>
			</form>
		</div>
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="opensubmitbutton">Datenbank pr&uuml;fen</div>
	</div>
</div>
<script type="text/javascript"><![CDATA[
function checkDB()
{
	postData = Array();
	postData['server'] = $F('server');
	postData['user'] = $F('user');
	postData['password'] = $F('password');
	postData['database'] = $F('database');
	postData['prefix'] = $F('prefix');
	throbberOn();
	clearMessages();
	new Ajax.Request('install.php?action=checkdb', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		if(result.getAttribute('success') > 0)
		{
			addNotice('Datenbank Prüfung erfolgreich');
			SubmitButtonTextFade.toggle();
			window.setTimeout(function() {
				$('opensubmitbutton').innerHTML = 'Weiter';
				SubmitButtonTextFade.toggle();
				$('opensubmitbutton').removeEventListener('click', checkDB, false);
				$('opensubmitbutton').addEventListener('click', function() { document.location.href = 'install.php?step=2'; }, false);
			}, 215);
		}
		else
		{
			addWarning('Datenbank Prüfung fehlgeschlagen');
			SubmitButtonTextFade.toggle();
			window.setTimeout(function() {
				$('opensubmitbutton').innerHTML = 'Erneut prüfen';
				SubmitButtonTextFade.toggle();
			}, 215);
		}
		throbberOff();
	}});
}
window.addEventListener('load', function () {
	FormContentEffect.toggle('height');
	$('opensubmitbutton').addEventListener('click', checkDB, false);
	$('install').addEventListener('submit', function () { e.preventDefault(); checkDB(); }, false);
}, false);
]]></script>

<?php elseif($_GET['step'] == 2): ?>

<div id="formbox">
	<div id="formbg">
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="opensubmitbutton">Weiter</div>
	</div>
</div>
<script type="text/javascript"><![CDATA[
window.addEventListener('load', function () {
<?php if($install->installdb()): ?>
	addNotice('Die Datenbank wurde erfolgreich installiert.');
	$('opensubmitbutton').addEventListener('click', function () { document.location.href='install.php?step=3'; }, false);
<?php else: ?>
	addWarning('Es trat folgender Fehler auf: <?php echo $error; ?>');
<?php endif; ?>
}, false);
]]></script>

<?php elseif($_GET['step'] == 3): ?>

<div id="formbox">
	<div id="formbg">
		<p>
			Sie m&uuml;ssen nun einen Hauptadmin erstellen.
		</p>
		<div id="formcontent">
			<form action="" method="post" id="addadmin">
				<fieldset>
					<legend>Userdaten</legend>
					<label><input type="text" name="nick" id="nick" />Nickname</label>
					<label><input type="text" name="email" id="email" />E-Mail Adresse</label>
					<label><input type="password" name="password1" id="password1" />Passwort</label>
					<label><input type="password" name="password2" id="password2" />Passwort Best&auml;tigung</label>
					<input type="submit" value="Admin erstellen" class="fake" />
				</fieldset>
			</form>
		</div>
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="opensubmitbutton">Admin erstellen</div>
	</div>
</div>
<script type="text/javascript"><![CDATA[
function addAdmin()
{
	postData = Array();
	postData['nick'] = $F('nick');
	postData['email'] = $F('email');
	postData['password1'] = $F('password1');
	postData['password2'] = $F('password2');
	throbberOn();
	clearMessages();
	new Ajax.Request('install.php?action=addadmin', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		if(result.getAttribute('success') > 0)
		{
			document.location.href = 'install.php?step=4';
		}
		else
		{
			addWarning('Das Admin-Konto konnte nicht eingerichtet werden, folgender Fehler ist aufgetreten: '+result.firstChild.data);
		}
		throbberOff();
	}});
}
window.addEventListener('load', function () {
	FormContentEffect.toggle('height');
	$('opensubmitbutton').addEventListener('click', addAdmin, false);
	$('install').addEventListener('submit', function () { e.preventDefault(); addAdmin(); }, false);
}, false);
]]></script>

<?php elseif($_GET['step'] == 4): ?>

<div id="formbox">
	<div id="formbg">
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="opensubmitbutton">Zur Startseite</div>
	</div>
</div>
<script type="text/javascript"><![CDATA[
window.addEventListener('load', function () {
<?php $install->updatecfg(); ?>
	addNotice('dWing CMS (Version <?php echo $version; ?>) ist nun erfolgreich installiert.');
	addNotice('Bitte entfernen Sie nun das Schreibrecht auf "./inc" (CHMOD 644). Nach der\
		Installation ist nur noch ein Schreibzugriff auf "./inc/settings.php" zu gew&auml;hrleisten.');
	$('opensubmitbutton').addEventListener('click', function() { document.location.href = 'index.php'; }, false);
}, false);
]]></script>

<?php endif; ?>

		</div>
		<div id="footer">
			&copy; 2006 dWing CMS<br />
			Valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a>
			and <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
		</div>
	</body>
</html>
<?php endif; ?>
*/ ?>