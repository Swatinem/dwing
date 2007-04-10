var editQuoteId = null;
function submitQuote()
{
	if(editQuoteId)
	{
		submitEditQuote();
		return;
	}
	postData = {quote: $('quote').value, source: $('source').value};
	Throbber.on();
	Messages.clear();
	new XHR({onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			Messages.addNotice(_('Quote added'));
			window.setTimeout(function () {
				window.location.href = window.location.href; // refresh
			}, 2000);
		}
		else
		{
			Messages.addWarning(result.firstChild.data);
		}
	}}).send('index.php?site=ajax_quote_add', Object.toQueryString(postData));
}
function submitEditQuote()
{
	postData = {quote: $('quote').value, source: $('source').value,
		quote_id: editQuoteId};
	Throbber.on();
	Messages.clear();
	new XHR({onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			Messages.addNotice(_('Quote altered'));
			window.setTimeout(function () {
				window.location.href = window.location.href; // refresh
			}, 2000);
		}
		else
		{
			Messages.addWarning(result.firstChild.data);
		}
	}}).send('index.php?site=ajax_quote_edit', Object.toQueryString(postData));
}
function deleteQuote(quote_id)
{
	if(!confirm(_('Delete this Quote?')))
		return;
	Throbber.on();
	Messages.clear();
	new XHR({onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			$('quote'+quote_id).effects({duration: 400}).start({opacity: 0, height: 0});
			Messages.addNotice(_('Quote deleted'));
			window.setTimeout(function () {
				Messages.clear();
			}, 2000);
		}
		else
		{
			Messages.addWarning(result.firstChild.data);
		}
	}}).send('index.php?site=ajax_quote_delete', Object.toQueryString({quote_id: quote_id}));
}
function editQuote(quote_id)
{
	editQuoteId = quote_id;
	new XHR({method: 'get', onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		$('quote').value = result.getAttribute('quote');
		$('source').value = result.getAttribute('source');
		$('legend').innerHTML = _('edit Quote');
		
		DynamicForm.open(_('save Quote'));
		window.setTimeout(function() {
			$('opensubmitbutton').removeEventListener('click', openForm, false);
			$('opensubmitbutton').addEventListener('click', submitQuote, false);
		}, 200);
	}}).send('index.php', 'site=ajax_getquote&quote_id='+quote_id);
}
function openForm()
{
	$('legend').innerHTML = _('new Quote');

	DynamicForm.open(_('save Quote'));
	window.setTimeout(function() {
		$('opensubmitbutton').removeEventListener('click', openForm, false);
		$('opensubmitbutton').addEventListener('click', submitQuote, false);
	}, 200);
}
function closeForm()
{
	DynamicForm.close(_('new Quote'));
	window.setTimeout(function() {
		$('opensubmitbutton').removeEventListener('click', submitQuote, false);
		$('opensubmitbutton').addEventListener('click', openForm, false);
		editQuoteId = null;
	}, 200);
}
window.addEventListener('load', function () {
	$('opensubmitbutton').addEventListener('click', openForm, false);
	$('closeform').addEventListener('click', function() { closeForm(); Messages.clear(); }, false);
	$('form').addEventListener('submit', function(e) { e.preventDefault(); submitQuote(); }, false);
}, false);