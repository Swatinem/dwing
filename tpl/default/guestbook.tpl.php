<?php
$title = l10n::_('Guestbook');
include($this->template('header.tpl.php'));
?>
<div id="formbox">
	<div id="formbg">
		<div id="formcontent">
			<form action="" id="form">
			<fieldset>
				<legend><?php echo l10n::_('new Entry'); ?></legend>
				<label><input type="text" id="nick" /><?php echo l10n::_('Name'); ?></label>
				<label><input type="text" id="email" /><?php echo l10n::_('E-Mail'); ?></label>
				<script type="text/javascript" src="./FCKeditor/fckeditor.js"></script>
				<script type="text/javascript">
				window.addEventListener('load', function () {
					if(!window.FCKeditor)
						return;
					var oFCKeditor = new FCKeditor('text') ;
					oFCKeditor.BasePath = './FCKeditor/';
					oFCKeditor.ReplaceTextarea() ;
				}, false);
				</script>
				<textarea id="text" rows="10" cols="30"></textarea>
				<input type="submit" class="fake" />
			</fieldset>
			</form>
		</div>
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="closeform"></div>
		<div id="opensubmitbutton"><?php echo l10n::_('new Entry'); ?></div>
	</div>
</div>
<script type="text/javascript" src="js/guestbook.js"></script>
<h1><?php echo l10n::_('Guestbook'); ?></h1>
<?php
$pages = Utils::pages(Guestbook::getTotal(), 10, 'guestbook?page=');
if(!empty($pages)):
?>
<div class="outertabbar flex">
	<div class="tabbar">
		<?php echo $pages; ?>
	</div>
</div>
<?php
endif;
$entries = Guestbook::getEntries(10);
foreach($entries as $entry):
	if($user->hasright('guestbook')):
?>
<div id="entry<?php echo $entry['gb_id']; ?>" class="admin">
<?php endif; ?>
	<div class="post">
		<div class="postinfo">
			<span class="month"><?php echo utf8_encode(strftime('%B', $entry['time'])); ?></span>
			<span class="day"><?php echo strftime('%d', $entry['time']); ?></span>
			<span class="year"><?php echo strftime('%Y', $entry['time']); ?></span>
			<!--<span class="time"><?php echo strftime('%H:%M', $entry['time']); ?></span>-->
			<?php if($user->hasright('guestbook')): ?>
			<span class="admincontrol">
				<a href="admin.php?site=guestbook&amp;edit=<?php echo $entry['gb_id']; ?>"><img src="images/tango-edit.png" alt="edit" /></a>
				<a href="javascript:deleteEntry(<?php echo $entry['gb_id']; ?>);"><img src="images/tango-delete.png" alt="delete" /></a>
			</span>
			<?php endif; ?>
		</div>
		<?php if($user->hasright('admin')): ?>
		<div class="ip"><?php echo $entry['ip']; ?></div>
		<?php endif; ?>
		<h2><?php echo htmlspecialchars($entry['nick']); ?></h2>
		<div class="postbody">
<?php echo Smilies::replace($entry['text']); ?>
		</div>
	</div>
<?php
	if($user->hasright('admin')):
?>
</div>
<?php
	endif;
endforeach;
if(!empty($pages)):
?>
<div class="outertabbar flex">
	<div class="tabbar">
		<?php echo $pages; ?>
	</div>
</div>
<?php
endif;
include($this->template('footer.tpl.php'));
?>