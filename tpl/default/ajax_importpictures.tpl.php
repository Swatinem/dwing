<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Admin::checkRight('screenshots');
?>
<results>
<?php
if(!(($importedPics = Screenshot::massAdd()) instanceof Exception)):
?>
	<result success="1"><?php echo sprintf(l10n::_('%d pictures imported'),$importedPics); ?></result>
<?php
else:
?>
	<result success="0"><?php echo $importedPics->getMessage(); ?></result>
<?php
endif;
?>
</results>