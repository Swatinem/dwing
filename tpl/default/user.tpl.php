<?php
$displayUser = Users::getUser(!empty($_GET['user_id']) ? $_GET['user_id'] : 0);
$title = $displayUser->nick;
include($this->template('header.tpl.php'));
?>
<h1><?php echo htmlspecialchars($displayUser->nick); ?></h1>
<ul class="openid">
	<li><strong><?php echo l10n::_('OpenID'); ?></strong></li>
	<?php
		$openIDs = $displayUser->openids;
		foreach($openIDs as $openID):
	?>
	<li><a href="<?php echo htmlspecialchars($openID); ?>"><?php echo htmlspecialchars($openID); ?></a></li>
	<?php endforeach; ?>
</ul>
<p>
<?php $group = UserGroup::getGroup($displayUser->ugroup_id); echo $group['name']; ?><br />
<?php echo strftime(l10n::_('User since: %B %d %Y'), $displayUser->registered); ?>
</p>
<?php
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
endif;
include($this->template('footer.tpl.php'));
?>
