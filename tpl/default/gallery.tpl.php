<?php
if(!empty($_GET['tag_id']))
	$title = l10n::_('Pictures').': '.Tags::tagName($_GET['tag_id']);
else
	$title = l10n::_('Pictures');
include($this->template('header.tpl.php'));
?>
<?php if($user->hasright('screenshots')): ?>
<div id="formbox">
	<div id="formbg">
		<div id="formcontent">
			<form action="" id="form">
			<fieldset>
				<legend id="legend"><?php echo l10n::_('import pictures'); ?></legend>
				<label><input type="text" id="directory" value="<?php echo $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']); ?>" /><?php echo l10n::_('Source directory'); ?></label>
				<label><input type="text" id="title" /><?php echo l10n::_('Title'); ?></label>
				<label class="check"><input type="checkbox" id="delold" /><?php echo l10n::_('delete source files'); ?></label>
				<label id="taglabel"><input type="text" id="tagtext" value="0<?php echo l10n::_(' tags selected'); ?>" /><?php echo l10n::_('Tags'); ?></label>
				<input type="submit" class="fake" />
			</fieldset>
			</form>
			<form action="" id="tagform">
				<div id="tagpopup">
					<input type="text" id="newtag" value="<?php echo l10n::_('new tag...'); ?>" />
					<div id="addtag"></div>
					<div id="closetagpopup"></div>
					<div id="taglist"></div>
				</div>
				<input type="submit" class="fake" />
			</form>
		</div>
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="closeform"></div>
		<div id="opensubmitbutton"><?php echo l10n::_('import pictures'); ?></div>
	</div>
</div>
<script type="text/javascript" src="js/tagsystem.js"></script>
<script type="text/javascript" src="js/pictures.js"></script>
<?php endif; ?>
<?php
if(empty($_GET['tag_id'])):
	// todo: images with no tags?!?
	$imageTags = Tags::getTagsWithContentOfType(ContentType::IMAGE);
	$max = 0;
	foreach($imageTags as $imageTag)
	{
		if($imageTag['content'] > $max)
			$max = $imageTag['content'];
	}
?>
<h1><?php echo l10n::_('Image tags'); ?></h1>
<p>
	<?php
	echo l10n::_('In this tag-cloud every tag is listed which has pictures assigned to it.').' '; // space...
	echo l10n::_('The larger the font the more pictures are assigned to that tag.');
	?>
</p>
<div id="tagcloud">
<?php
	foreach($imageTags as $imageTag):
?>
	<a href="imagestrip/<?php echo $imageTag['tag_id']; ?>" class="p<?php echo 10*ceil(10*$imageTag['content']/$max) ?>"><?php echo htmlspecialchars($imageTag['name']); ?></a>
<?php
	endforeach;
?>
</div>
<?php
else:
?>
<h1><?php echo Tags::tagName($_GET['tag_id']); ?></h1>
<?php
$pages = Utils::pages(Tags::getContentCount($_GET['tag_id'], ContentType::IMAGE), 20, 'index.php?site=gallery&amp;tag_id='.$_GET['tag_id'].'&amp;page=');
if(!empty($pages)):
?>
<div class="outertabbar flex">
	<div class="tabbar">
		<?php echo $pages ?>
	</div>
</div>
<?php endif; ?>
<div class="center clearfix">
<?php
$screens = Screenshot::getScreenshots($_GET['tag_id'], 20);
foreach($screens as $screen):
?>
	<a class="thumb" href="index.php?site=imagestrip&amp;tag_id=<?php echo $_GET['tag_id']; ?>&amp;pic_id=<?php echo $screen['pic_id']; ?>"><img src="images/thumbs/picture<?php echo $screen['pic_id']; ?>.jpg" alt="<?php echo $screen['title']; ?>" title="<?php echo $screen['title']; ?>" /></a>
<?php endforeach; ?>
</div>
<?php
endif;
include($this->template('footer.tpl.php'));
?>