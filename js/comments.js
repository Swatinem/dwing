function submitComment()
{
	postData = {text: $('commenttext').value, content_id: $('content_id').value,
		content_type: $('content_type').value};
	if(window.FCKeditorAPI)
	{
		var oEditor = FCKeditorAPI.GetInstance('commenttext');
		if(oEditor)
			postData.text = oEditor.GetXHTML();
	}
	Throbber.on();
	new XHR({onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
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
	}}).send('index.php?site=ajax_comment_add', Object.toQueryString(postData));
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