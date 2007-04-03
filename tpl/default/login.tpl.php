<?php
if(isset($_GET['logout']))
	$user->logout();
$title = l10n::_('Login');
include($this->template('header.tpl.php'));
?>
<?php
if(!empty($_GET['returnto']) && !empty($_SERVER['HTTP_REFERER']))
	$_SESSION['returnto'] = $_SERVER['HTTP_REFERER'];
?>
<?php
/* Todo: Translation... */
?>
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
<?php if($user->authed): ?>
<ul class="warning">
	<li>Du bist schon angemeldet. <a href="user/<?php echo $user->user_id; ?>">Zum Profil</a></li>
</ul>
<?php endif; ?>
<h2>Was ist OpenID?</h2>
<p>
OpenID ist ein dezentrales Identitätssystem, durch das ihr euch mit einem
einzigen Benutzernamen auf vielen verschiedenen Seiten anmelden könnt, ohne
euch extra registrieren zu müssen.
</p>
<h2>Wo bekomme ich eine OpenID?</h2>
<p>
Zum Beispiel bei <a href="https://www.myopenid.com">MyOpenID.com</a>.
OpenID ist ein dezentrales System und es gibt viele verschiedene OpenID
Provider.
<a href="http://openid.net/wiki/index.php/Public_OpenID_providers">Hier</a>
gibt es eine Liste von weiteren OpenID Providern.
</p>
<?php include($this->template('footer.tpl.php')); ?>