var editQuoteId = null;
function submitQuote()
{
	if(editQuoteId)
	{
		submitEditQuote();
		return;
	}
	postData = Array();
	postData['quote'] = $F('quote');
	postData['source'] = $F('source');
	Throbber.on();
	Messages.clear();
	new Ajax.Request('index.php?site=ajax_quote_add', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
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
	}});
}
function submitEditQuote()
{
	postData = Array();
	postData['quote_id'] = editQuoteId;
	postData['quote'] = $F('quote');
	postData['source'] = $F('source');
	Throbber.on();
	Messages.clear();
	new Ajax.Request('index.php?site=ajax_quote_edit', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
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
	}});
}
function deleteQuote(quote_id)
{
	if(!confirm(_('Delete this Quote?')))
		return;
	postData = Array();
	postData['quote_id'] = quote_id;
	Throbber.on();
	Messages.clear();
	new Ajax.Request('index.php?site=ajax_quote_delete', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			var fadeaway = new fx.FadeSize('quote'+quote_id, {duration: 400});
			fadeaway.toggle('height');
			Messages.addNotice(_('Quote deleted'));
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
function editQuote(quote_id)
{
	editQuoteId = quote_id;
	new Ajax.Request('index.php', {method: 'get', parameters: 'site=ajax_getquote&quote_id='+quote_id, onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		$('quote').value = result.getAttribute('quote');
		$('source').value = result.getAttribute('source');
		$('legend').innerHTML = _('edit Quote');
		
		DynamicForm.open(_('save Quote'));
		window.setTimeout(function() {
			$('opensubmitbutton').removeEventListener('click', openForm, false);
			$('opensubmitbutton').addEventListener('click', submitQuote, false);
		}, 200);
	}});
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