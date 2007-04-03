function submitPictures()
{
	postData = Array();
	postData['directory'] = $F('directory');
	postData['title'] = $F('title');
	if($('delold').checked)
		postData['delold'] = true;
	selectedTags = getSelectedTags();
	for(var i = 0; i < selectedTags.length; i++)
	{
		postData['tag_ids['+i+']'] = selectedTags[i];
	}
	throbberOn();
	clearMessages();
	new Ajax.Request('index.php?site=ajax_importpictures', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		throbberOff();
		if(result.getAttribute('success') > 0)
		{
			addNotice(result.firstChild.data);
			window.setTimeout(function () {
				window.location.href = window.location.href; // refresh
			}, 2000);
		}
		else
		{
			addWarning(result.firstChild.data);
		}
	}});
}
function openForm()
{
	CloseFormButtonFade.toggle();
	FormContentEffect.toggle('height');
	$('opensubmitbutton').removeEventListener('click', openForm, false);
	$('opensubmitbutton').addEventListener('click', submitPictures, false);
}
function closeForm()
{
	CloseFormButtonFade.toggle();
	FormContentEffect.toggle('height');
	$('opensubmitbutton').removeEventListener('click', submitPictures, false);
	$('opensubmitbutton').addEventListener('click', openForm, false);
}
window.addEventListener('load', function () {
	$('opensubmitbutton').addEventListener('click', openForm, false);
	$('closeform').addEventListener('click', function() { closeForm(); clearMessages(); }, false);
	$('form').addEventListener('submit', function(e) { e.preventDefault(); submitPictures(); }, false);
}, false);