<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<results>
<?php
if(!is_a($entry_id = Guestbook::addEntry(), 'Exception')):
?>
	<result success="1"><?php echo $entry_id; ?></result>
<?php
else:
?>
	<result success="0"><?php echo $entry_id->getMessage(); ?></result>
<?php
endif;
?>
</results>