<?php
$displayUser = Users::getUser(!empty($_GET['user_id']) ? $_GET['user_id'] : 0);
$title = $displayUser->nick;
include($this->template('header.tpl.php'));
?>
<h1><?php echo htmlspecialchars($displayUser->nick); ?></h1>
<ul class="openid">
	<li><strong><?php echo l10n::_('OpenID'); ?></strong> <a href="<?php echo htmlspecialchars($displayUser->openid); ?>"><?php echo htmlspecialchars($displayUser->openid); ?></a></li>
</ul>
<p>
<?php $group = UserGroup::getGroup($displayUser->ugroup_id); echo $group['name']; ?><br />
<?php echo utf8_encode(strftime(l10n::_('User since: %B %d %Y'), $displayUser->registered)); ?>
</p>
<?php include($this->template('footer.tpl.php')); ?>