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
		<div id="opensubmitbutton">Zurück</div>
	</div>
</div>
<script type="text/javascript"><![CDATA[
window.addEventListener('load', function () {
	<?php if($updater->update()): ?>
		addNotice('Ihre dWing Installation wurde automatisch von Version <?php echo $updater->oldversion; ?> auf <?php echo $updater->version; ?> gebracht.');
	<?php else: ?>
		addWarning('Es trat ein Fehler auf. Update abgebrochen.');
	<?php endif; ?>
	$('opensubmitbutton').addEventListener('click', function () { document.location.href = 'index.php'; }, false);
}, false);
]]></script>

		</div>
		<div id="footer">
			© 2006-2007 dWing CMS<br />
			Valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a>
			and <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
		</div>
	</body>
</html>