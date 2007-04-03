<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Admin::checkRight('guestbook');
?>
<results>
<?php
if(Guestbook::deleteEntry($_POST['gb_id'])):
?>
	<result success="1"></result>
<?php
else:
?>
	<result success="0"><?php echo $this->error; ?></result>
<?php
endif;
?>
</results>