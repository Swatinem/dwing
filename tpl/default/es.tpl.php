<?php
header('Content-Type: text/javascript; charset=utf-8');
?>
_ = function(str)
{
	if(!langTable[str])
		return str;
	else
		return langTable[str];
};
langTable = {
<?php
$i = 0;
$langTable = l10n::getLangTable();
$max = count($langTable);
foreach($langTable as $from => $to):
$i++;
?>
	'<?php echo $from; ?>': '<?php echo $to; ?>'<?php if($i != $max) echo ','; ?>

<?php endforeach; ?>
};
<?php
// TODO: integrate all JS into a single file
exit;
?>
