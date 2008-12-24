<?php
$displayUser = $object;
$title = $displayUser->nick;
include($this->template('header.tpl.php'));
?>
<div class="area">
	<h1><?php echo htmlspecialchars($displayUser->nick); ?></h1>
	<div class="col2">
		<h2><?php echo l10n::_('User details'); ?></h2>
		<p>
			<?php $group = Usergroup::getGroup($displayUser->ugroup_id); echo $group['name']; ?><br />
			<?php echo strftime(l10n::_('User since: %B %d %Y'), $displayUser->registered); ?>
		</p>
	</div>
	<div class="col2">
		<h2><?php echo l10n::_('OpenIDs'); ?></h2>
		<ul>
			<?php
			$openIDs = $displayUser->openids;
			foreach($openIDs as $openID):
			?>
			<li><a href="<?php echo htmlspecialchars($openID); ?>"><?php echo htmlspecialchars($openID); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php
/* TODO: resurrect the openid login
if($displayUser->user_id == $user->user_id):
	$_SESSION['returnto'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
?>
<form action="./" method="post">
	<h2>Weitere OpenID eintragen:</h2>
	<div class="openid box">
		<input type="text" name="openid_url" /><input type="submit" value="hinzufügen"/>
		<h3>Beispiele:</h3>
		<ul>
			<li>http://deineadresse.de</li>
			<li>http://deinname.myopenid.com</li>
			<li>http://deinname.livejournal.com</li>
			<li>http://openid.aol.com/deinAIMname</li>
		</ul>
	</div>
</form>
<?php
endif;*/
include($this->template('footer.tpl.php'));
?>
