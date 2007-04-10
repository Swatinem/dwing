function doRating(aWidgetId, aContentId, aContentType, aRating)
{
	postData = {rating: aRating, content_id: aContentId,
		content_type: aContentType};

	Throbber.on();
	new XHR({onSuccess: function(text, xml) {
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
	}}).send('index.php?site=ajax_rating', Object.toQueryString(postData));
}