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
?>
function round(aNumber, aDecimal)
{
	if(aDecimal == undefined)
		aDecimal = 0;
	var factor = Math.pow(10, aDecimal);
	return Math.round(aNumber*factor)/factor;
}
function printf()
{
	var aFormat = arguments[0];
	for(var i = 1; i < arguments.length; i++)
	{
		aFormat = aFormat.replace(/%s/, arguments[i]);
	}
	return aFormat;
}
// TODO: hook this up with a real JS Library
function vote(aElem, aValue)
{
	ratingElem = document.getElementById(aElem);
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open('POST', aElem, true);
	xmlHttp.onreadystatechange = function () {
		if (xmlHttp.readyState == 4) {
			if(xmlHttp.status == 401)
			{
				alert(_('Not logged in.'));
				return;
			}
			if(xmlHttp.status != 200)
				return;
			eval('var rating = '+xmlHttp.responseText);
			ratingElem.className = 'rating score'+round(rating.average);
			ratingElem.lastChild.previousSibling.textContent =
				printf(_('%s ratings / %s average'), rating.ratings, round(rating.average, 1));
		}
	};
	xmlHttp.send(aValue);
}
