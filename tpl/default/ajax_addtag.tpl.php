<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Admin::checkRight('admin');
?>
<results>
<?php
if($tagId = Tags::addTag()):
?>
	<result success="1"><?php echo $tagId; ?></result>
<?php
else:
?>
	<result success="0"><?php echo $this->error; ?></result>
<?php
endif;
?>
</results>