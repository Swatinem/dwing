function submitEntry()
{
	postData = Array();
	postData['nick'] = $F('nick');
	postData['email'] = $F('email');
	postData['text'] = $F('text');
	if(window.FCKeditorAPI)
	{
		var oEditor = FCKeditorAPI.GetInstance('text') ;
		if(oEditor)
			postData['text'] = oEditor.GetXHTML();
	}
	Throbber.on();
	Messages.clear();
	new Ajax.Request('index.php?site=ajax_guestbook', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			Messages.addNotice(_('Entry added'));
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
function deleteEntry(gb_id)
{
	if(!confirm(_('Delete this Entry?')))
		return;
	postData = Array();
	postData['gb_id'] = gb_id;
	Throbber.on();
	Messages.clear();
	new Ajax.Request('index.php?site=ajax_guestbook_delete', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			var fadeaway = new fx.FadeSize('entry'+gb_id, {duration: 400});
			fadeaway.toggle('height');
			Messages.addNotice(_('Entry deleted'));
			window.setTimeout(function () {
				Messages.clear();
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
	DynamicForm.open(_('send Entry'));
	window.setTimeout(function() {
		$('opensubmitbutton').removeEventListener('click', openForm, false);
		$('opensubmitbutton').addEventListener('click', submitEntry, false);
	}, 200);
}
function closeForm()
{
	DynamicForm.close(_('new Entry'));
	window.setTimeout(function() {
		$('opensubmitbutton').removeEventListener('click', submitEntry, false);
		$('opensubmitbutton').addEventListener('click', openForm, false);
	}, 200);
}
window.addEventListener('load', function () {
	$('opensubmitbutton').addEventListener('click', openForm, false);
	$('closeform').addEventListener('click', function() { closeForm(); Messages.clear(); }, false);
	$('form').addEventListener('submit', function(e) { e.preventDefault(); submitEntry(); }, false);
}, false);