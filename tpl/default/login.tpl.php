<?php
if(isset($_GET['logout']))
	Core::$user->logout();

if(!empty($_GET['returnto']) && !empty($_SERVER['HTTP_REFERER']))
	$_SESSION['returnto'] = $_SERVER['HTTP_REFERER'];

$title = l10n::_('Sign in');
include($this->template('header.tpl.php'));
?>
<div class="area">
	<form action="login" method="post" class="openid">
		<h1><?php echo l10n::_('Sign in with your OpenID'); ?></h1>
		<input type="text" name="openid_url" id="openid_url" placeholder="<?php echo l10n::_('OpenID'); ?>" /><input type="submit" value="<?php echo l10n::_('Sign in'); ?>"/>
		<h1><?php echo l10n::_('Or sign in using your'); ?></h1>
		<button id="googleid"><?php echo l10n::_('Account'); /* https://www.google.com/accounts/o8/id */ ?></button>
		<button id="yahooid"><?php echo l10n::_('Account'); /* http://yahoo.com */ ?></button>
	</form>
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
