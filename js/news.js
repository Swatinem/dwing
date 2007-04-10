function submitNews()
{
	postData = {text: $('newstext').value, tags: $('newstags').value,
		title: $('newstitle').value};
	if(window.FCKeditorAPI)
	{
		var oEditor = FCKeditorAPI.GetInstance('newstext') ;
		if(oEditor)
			postData.text = oEditor.GetXHTML();
	}
	Throbber.on();
	$('newswarningbox').style.display = 'none';
	new Ajax.Request('index.php?site=ajax_news_add', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
	new XHR({onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			importedNode = document.importNode(result.childNodes[1], true);
			$('newsform').parentNode.insertBefore(importedNode, $('newsform').nextSibling);
			
			if(window.FCKeditorAPI)
			{
				var oEditor = FCKeditorAPI.GetInstance('newstext');
				if(oEditor)
					oEditor.SetHTML('');
			}
			else
			{
				$('newstext').value = '';
			}
			$('newspreviewtext').innerHTML = '';
			$('newstitle').value = _('Title');
			$('newstags').value = _('tags');
			$('newstitlepreview').textContent = '';
			$('newstagspreview').firstChild.textContent = '';
			if(inNewsPreview)
				previewNews();
		}
		else
		{
			$('newswarningbox').style.display = 'block';
			$('newswarning').textContent = result.textContent;
		}
	}}).send('index.php?site=ajax_news_add', Object.toQueryString(postData));
}

function previewNews()
{
	if(inNewsPreview)
	{
		inNewsPreview = false;

		$('newspreview').textContent = _('Preview');
		(newsPreview = $('newspreviewtext')).style.display = 'none';
		newsPreview.nextSibling.nextSibling.style.display = 'block';
		$('newstitle').style.display = 'block';
		$('newstitlepreview').style.display = 'none';
		$('newstags').style.display = 'block';
		$('newstagspreview').style.display = 'none';
	}
	else
	{
		inNewsPreview = true;

		$('newspreview').textContent = _('Edit');
		newsPreview = $('newspreviewtext');
		if(window.FCKeditorAPI)
		{
			var oEditor = FCKeditorAPI.GetInstance('newstext');
			if(oEditor)
				newsPreview.innerHTML = oEditor.GetXHTML();
		}
		else
		{
			newsPreview.innerHTML = $('newstext').value;
		}
		newsPreview.style.display = 'block';
		newsPreview.nextSibling.nextSibling.style.display = 'none';
		$('newstitle').style.display = 'none';
		$('newstitlepreview').textContent = $('newstitle').value;
		$('newstitlepreview').style.display = 'inline';
		$('newstags').style.display = 'none';
		$('newstagspreview').firstChild.textContent = $('newstags').value;
		$('newstagspreview').style.display = 'inline';
	}
}

window.addEventListener('load', function () {
	if(!$('newsform'))
		return; 

	if(!window.FCKeditor)
		return;
	var oFCKeditor = new FCKeditor('newstext') ;
	oFCKeditor.BasePath = './FCKeditor/';
	oFCKeditor.ReplaceTextarea() ;

	inNewsPreview = false;

	$('newsform').addEventListener('submit', function(e) { e.preventDefault(); submitNews(); }, false);
	$('newspreview').addEventListener('click', function(e) { e.preventDefault(); previewNews(); }, false);
	$('newstitle').addEventListener('focus', function(e) { if(e.target.value == _('Title')) e.target.value = ''; }, false);
	$('newstags').addEventListener('focus', function(e) { if(e.target.value == _('tags')) e.target.value = ''; }, false);
	//$('newstitle').addEventListener('blur', function(e) { if(e.target.value == '') e.target.value = _('Title'); }, false);
	//$('newstags').addEventListener('blur', function(e) { if(e.target.value == '') e.target.value = _('tags'); }, false);
}, false);