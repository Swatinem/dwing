function submitPictures()
{
	postData = Array();
	postData['directory'] = $F('directory');
	postData['title'] = $F('title');
	if($('delold').checked)
		postData['delold'] = true;
	postData['tags'] = $F('tags');
	Throbber.on();
	Messages.clear();
	new Ajax.Request('index.php?site=ajax_importpictures', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			Messages.addNotice(result.firstChild.data);
			window.setTimeout(function () {
				window.location.href = window.location.href; // refresh
			}, 2000);
		}
		else
		{
			Messages.addWarning(result.firstChild.data);
		}
	}});
}
function openForm()
{
	DynamicForm.open();
	$('opensubmitbutton').removeEventListener('click', openForm, false);
	$('opensubmitbutton').addEventListener('click', submitPictures, false);
}
function closeForm()
{
	DynamicForm.close();
	$('opensubmitbutton').removeEventListener('click', submitPictures, false);
	$('opensubmitbutton').addEventListener('click', openForm, false);
}
window.addEventListener('load', function () {
	$('opensubmitbutton').addEventListener('click', openForm, false);
	$('closeform').addEventListener('click', function() { closeForm(); Messages.clear(); }, false);
	$('form').addEventListener('submit', function(e) { e.preventDefault(); submitPictures(); }, false);
}, false);