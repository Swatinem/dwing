<?php
if(isset($_GET['logout']))
	Core::$user->logout();

if(!empty($_GET['returnto']) && !empty($_SERVER['HTTP_REFERER']))
	$_SESSION['returnto'] = $_SERVER['HTTP_REFERER'];

$title = l10n::_('Sign in');
include($this->template('header.tpl.php'));
?>
<div class="area">
	<h1><?php echo l10n::_('Sign in with your OpenID'); ?></h1>
	<form action="login" method="post" class="openid">
		<input type="text" name="openid_url" /><input type="submit" value="<?php echo l10n::_('Sign in'); ?>"/>
	</form>
	<!-- TODO: buttons to log in with well known OpenID Providers + a little explanation -->
<?php if(!empty($loginerror)): ?>
<h2><?php echo l10n::_('Error'); ?></h2>
<p><?php echo htmlspecialchars($loginerror->getMessage()); ?></p>
<?php endif; ?>
<?php if(Core::$user->authed): ?>
<h2><?php echo l10n::_('Already signed in'); ?></h2>
<p><a href="user/<?php echo Core::$user->id; ?>"><?php echo l10n::_('Visit your profile for more details'); ?></a></p>
<?php endif; ?>
</div>
<?php include($this->template('footer.tpl.php')); ?>
