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
	clearMessages();
	new Ajax.Request('index.php?site=ajax_quote_add', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			addNotice(_('Quote added'));
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
function submitEditQuote()
{
	postData = Array();
	postData['quote_id'] = editQuoteId;
	postData['quote'] = $F('quote');
	postData['source'] = $F('source');
	Throbber.on();
	clearMessages();
	new Ajax.Request('index.php?site=ajax_quote_edit', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			addNotice(_('Quote altered'));
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
function deleteQuote(quote_id)
{
	if(!confirm(_('Delete this Quote?')))
		return;
	postData = Array();
	postData['quote_id'] = quote_id;
	Throbber.on();
	clearMessages();
	new Ajax.Request('index.php?site=ajax_quote_delete', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			var fadeaway = new fx.FadeSize('quote'+quote_id, {duration: 400});
			fadeaway.toggle('height');
			addNotice(_('Quote deleted'));
			window.setTimeout(function () {
				clearMessages();
			}, 2000);
		}
		else
		{
			addWarning(result.firstChild.data);
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
		CloseFormButtonFade.toggle();
		FormContentEffect.toggle('height');
		SubmitButtonTextFade.toggle();
		window.setTimeout(function() {
			$('opensubmitbutton').innerHTML = _('save Quote');
			SubmitButtonTextFade.toggle();
			$('opensubmitbutton').removeEventListener('click', openForm, false);
			$('opensubmitbutton').addEventListener('click', submitQuote, false);
		}, 215);
	}});
}
function openForm()
{
	$('legend').innerHTML = _('new Quote');
	CloseFormButtonFade.toggle();
	FormContentEffect.toggle('height');
	SubmitButtonTextFade.toggle();
	window.setTimeout(function() {
		$('opensubmitbutton').innerHTML = _('save Quote');
		SubmitButtonTextFade.toggle();
		$('opensubmitbutton').removeEventListener('click', openForm, false);
		$('opensubmitbutton').addEventListener('click', submitQuote, false);
	}, 215);
}
function closeForm()
{
	CloseFormButtonFade.toggle();
	FormContentEffect.toggle('height');
	SubmitButtonTextFade.toggle();
	window.setTimeout(function() {
		$('opensubmitbutton').innerHTML = _('new Quote');
		SubmitButtonTextFade.toggle();
		$('opensubmitbutton').removeEventListener('click', submitQuote, false);
		$('opensubmitbutton').addEventListener('click', openForm, false);
		editQuoteId = null;
	}, 215);
}
window.addEventListener('load', function () {
	$('opensubmitbutton').addEventListener('click', openForm, false);
	$('closeform').addEventListener('click', function() { closeForm(); clearMessages(); }, false);
	$('form').addEventListener('submit', function(e) { e.preventDefault(); submitQuote(); }, false);
}, false);