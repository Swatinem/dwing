<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Admin::checkRight('quotes');
?>
<results>
<?php
if(!(($quote_id = Quote::addQuote()) instanceof Exception)):
?>
	<result success="1"><?php echo $quote_id; ?></result>
<?php
else:
?>
	<result success="0"><?php echo $quote_id->getMessage(); ?></result>
<?php
endif;
?>
</results>