<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Admin::checkRight('quotes');
?>
<results>
<?php
if(!is_a($return = Quote::editQuote($_POST['quote_id'], 'Exception'))):
?>
	<result success="1"></result>
<?php
else:
?>
	<result success="0"><?php echo $return->getMessage(); ?></result>
<?php
endif;
?>
</results>