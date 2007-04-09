<?php
header('Content-Type: application/xhtml+xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title><?php echo htmlspecialchars(Tags::tagName($_GET['tag_id'])) ?> - dWing CMS</title>
		<base href="<?php echo 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/'; ?>" />
		<link rel="stylesheet" type="text/css" href="dwingcms.css" />
		<script type="text/javascript" src="js/prototype.js"></script>
		<script type="text/javascript" src="js/moo.fx.js"></script>
		<script type="text/javascript" src="js/moo.fx.pack.js"></script>
		<script type="text/javascript" src="js/mootools.js"></script>
		<script type="text/javascript" src="js/interface.js"></script>
		<script type="text/javascript" src="js/imagestrip.js"></script>
		<script type="text/javascript" src="index.php?site=l10n"></script>
		<script type="text/javascript"><![CDATA[
		<?php
		$screens = Screenshot::getScreenshots($_GET['tag_id']);
		$imageData = array();
		$selected_index = 0;
		foreach($screens as $screen)
		{
			$imageData[] = '{id: '.$screen['pic_id'].', views: '.$screen['views'].', title: "'.htmlspecialchars($screen['title']).'"}';
		}
		?>
		tagId = <?php echo $_GET['tag_id']; ?>;
		imageData = [<?php echo implode(",\n", $imageData); ?>];
		]]>
		</script>
	</head>
	<body>
		<div id="imagecontainer">
			<img src="" id="imagestrip_image" alt="" />
		</div>
		<div id="imageinfoouter">
			<div id="imageinfo">
				<div><span id="imageviews"></span>
					<?php echo l10n::_('views'); ?>
				</div>
				<a id="link" title="<?php echo l10n::_('link to this page'); ?>"></a>
				<h1 id="imagetitle"></h1>
				<a href="gallery"><?php echo l10n::_('tags:'); ?></a> <span id="imagetags"></span>
			</div>
		</div>
		<div id="imagestrip_previous">
			<div id="imagestrip_previous_button">-20</div>
		</div>
		<div id="imagestrip_outer">
			<div id="imagestrip_inner"></div>
		</div>
		<div id="imagestrip_next">
			<div id="imagestrip_next_button">+20</div>
		</div>
	</body>
</html>