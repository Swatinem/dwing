<?php
include($this->template('header.tpl.php'));
$_error = sprintf(l10n::_('The page you requested (%s) does not exist.'), htmlspecialchars($_GET['site']));
include($this->template('error.tpl.php'));
include($this->template('footer.tpl.php'));
?>