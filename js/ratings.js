function doRating(aWidgetId, aContentId, aContentType, aRating)
{
	postData = Array();
	postData['content_id'] = aContentId;
	postData['content_type'] = aContentType;
	postData['rating'] = aRating;
	Throbber.on();
	new Ajax.Request('index.php?site=ajax_rating', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			// I hate those damn textnodes in the DOM
			$(aWidgetId).childNodes[1].style.width = Math.round(parseInt(result.getAttribute('average'))/5*100)+'px';
			$(aWidgetId).nextSibling.nextSibling.textContent = result.textContent;
		}
		else
		{
			alert(_('Not logged in.'));
		}
	}});
}