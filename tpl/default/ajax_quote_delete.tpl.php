<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Admin::checkRight('quotes');
Quote::deleteQuote($_POST['quote_id']);
?>
<results>
	<result success="1"></result>
</results>