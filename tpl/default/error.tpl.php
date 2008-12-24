<?php
$code = !empty($error->httpCode) ? $error->httpCode : 500;
header(' ', true, $code);
$title = sprintf(l10n::_('Error %s'), $code);
include($this->template('header.tpl.php'));
?>
<div class="area">
	<h1><?php echo $title; ?></h1>
	<p><?php echo htmlspecialchars($error->getMessage()); ?></p>
</div>
<?php
include($this->template('footer.tpl.php'));
?>
