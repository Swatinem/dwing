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
	<?php if(Core::$user->authed && Core::$user->id == $displayUser->id): ?>
	<h1><?php echo l10n::_('Your profile'); ?></h1>
	<!-- TODO: edit your own profile settings -->
	<h2><?php echo l10n::_('Register another OpenID'); ?></h2>
	<form action="login?returnto=1" method="post" class="openid">
		<input type="text" name="openid_url" /><input type="submit" value="<?php echo l10n::_('add'); ?>"/>
	</form>
	<?php endif; ?>
</div>
<?php include($this->template('footer.tpl.php')); ?>
