<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
$thisquote = Quote::getQuote($_GET['quote_id']);
?>
<results>
	<result quote="<?php echo htmlspecialchars($thisquote['quote']); ?>" source="<?php echo htmlspecialchars($thisquote['source']); ?>"></result>
</results>