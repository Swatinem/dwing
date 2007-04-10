function submitEntry()
{
	postData = {text: $('text').value, email: $('email').value,
		nick: $('nick').value};
	if(window.FCKeditorAPI)
	{
		var oEditor = FCKeditorAPI.GetInstance('text') ;
		if(oEditor)
			postData.text = oEditor.GetXHTML();
	}
	Throbber.on();
	Messages.clear();
	new XHR({onSuccess: function(text, xml) {
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
	}}).send('index.php?site=ajax_guestbook', Object.toQueryString(postData));
}
function deleteEntry(gb_id)
{
	if(!confirm(_('Delete this Entry?')))
		return;
	Throbber.on();
	Messages.clear();
	new XHR({onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			$('entry'+gb_id).effects({duration: 400}).start({opacity: 0, height: 0});
			Messages.addNotice(_('Entry deleted'));
			window.setTimeout(function () {
				Messages.clear();
			}, 2000);
		}
		else
		{
			Messages.addWarning(result.firstChild.data);
		}
	}}).send('index.php?site=ajax_guestbook_delete', Object.toQueryString({gb_id: gb_id}));
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