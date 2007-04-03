function submitComment()
{
	postData = Array();
	postData['text'] = $F('commenttext');
	postData['content_id'] = $F('content_id');
	postData['content_type'] = $F('content_type');
	if(window.FCKeditorAPI)
	{
		var oEditor = FCKeditorAPI.GetInstance('commenttext');
		if(oEditor)
			postData['text'] = oEditor.GetXHTML();
	}
	throbberOn();
	new Ajax.Request('index.php?site=ajax_comment_add', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		throbberOff();
		if(result.getAttribute('success') > 0)
		{
			importedNode = document.importNode(result.childNodes[1], true);
			$('commentform').parentNode.insertBefore(importedNode, $('commentform'));
			
			if(window.FCKeditorAPI)
			{
				var oEditor = FCKeditorAPI.GetInstance('commenttext');
				if(oEditor)
					oEditor.SetHTML('');
			}
			else
			{
				$('commenttext').value = '';
			}
			$('commentpreviewtext').innerHTML = '';
			if(inPreview)
				previewComment();
		}
	}});
}
function previewComment()
{
	if(!inPreview)
	{
		inPreview = true;
		$('commentpreview').textContent = _('Edit');
		(commentPreview = $('commentpreviewtext')).style.display = 'block';
		if(window.FCKeditorAPI)
		{
			var oEditor = FCKeditorAPI.GetInstance('commenttext');
			if(oEditor)
				commentPreview.innerHTML = oEditor.GetXHTML();
		}
		else
		{
			commentPreview.innerHTML = $('commenttext').value;
		}
		commentPreview.nextSibling.nextSibling.style.display = 'none';
	}
	else
	{
		inPreview = false;
		$('commentpreview').textContent = _('Preview');
		(commentPreview = $('commentpreviewtext')).style.display = 'none';
		commentPreview.nextSibling.nextSibling.style.display = 'block';
	}
}
window.addEventListener('load', function () {
	if(!window.FCKeditor)
		return;
	var oFCKeditor = new FCKeditor('commenttext') ;
	oFCKeditor.BasePath = './FCKeditor/';
	oFCKeditor.ReplaceTextarea() ;

	inPreview = false;

	$('commentform').addEventListener('submit', function(e) { e.preventDefault(); submitComment(); }, false);
	$('commentpreview').addEventListener('click', function(e) { e.preventDefault(); previewComment(); }, false);
}, false);