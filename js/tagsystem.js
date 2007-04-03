var tagInfo = new Array();

function addTag()
{
	postData = Array();
	postData['name'] = $F('newtag');
	throbberOn();
	clearMessages();
	new Ajax.Request('index.php?site=ajax_addtag', {method: 'post', parameters: urlEncode(postData), onComplete: function (req) {
		xml = req.responseXML;
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		throbberOff();
		if(result.getAttribute('success') > 0)
		{
			addNotice(_('Tag added'));
			window.setTimeout(function () {
				clearMessages();
			}, 2000);

			tagId = result.firstChild.data;
			var labelElem = document.createElementNS(xhtmlNS, 'label');
			var checkElem = document.createElementNS(xhtmlNS, 'input');
			checkElem.type = 'checkbox';
			checkElem.addEventListener('change', function (ev) {
				ev.currentTarget.parentNode.className = ev.currentTarget.checked ? 'selected' : '';
			}, false);
			labelElem.appendChild(checkElem);
			labelElem.appendChild(document.createTextNode(' '+$F('newtag')));
			$('taglist').appendChild(labelElem);
			$('newtag').value = _('new tag...');
			checkElem.click();
			tagInfo.push({tagId: tagId, labelElem: labelElem});
		}
		else
		{
			addWarning(result.firstChild.data);
		}
	}});
}

function getSelectedTags()
{
	var selectedTags = new Array();
	for(var i = 0; i < tagInfo.length; i++)
	{
		if(tagInfo[i].labelElem.firstChild.checked)
			selectedTags.push(tagInfo[i].tagId);
	}
	return selectedTags;
}

function getTags()
{
	throbberOn();
	new Ajax.Request('index.php', {method: 'get', parameters: 'site=ajax_gettags', onComplete: function (req) {
		throbberOff();
		xml = req.responseXML;
		results = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null);
		while(result = results.iterateNext())
		{
			var labelElem = document.createElementNS(xhtmlNS, 'label');
			var checkElem = document.createElementNS(xhtmlNS, 'input');
			checkElem.type = 'checkbox';
			checkElem.addEventListener('change', function (ev) {
				ev.currentTarget.parentNode.className = ev.currentTarget.checked ? 'selected' : '';
			}, false);
			labelElem.appendChild(checkElem);
			labelElem.appendChild(document.createTextNode(' '+result.getAttribute('name')));
			$('taglist').appendChild(labelElem);
			tagInfo.push({tagId: result.getAttribute('tag_id'), labelElem: labelElem});
		}
	}});
}

window.addEventListener('load', function () {
	getTags();

	$('addtag').addEventListener('click', function () { addTag(); }, false);
	$('tagform').addEventListener('submit', function (e) { e.preventDefault(); addTag(); }, false);

	$('taglabel').addEventListener('click', function (ev) {
		$('tagpopup').style.display = 'block';
		$('tagpopup').style.top = ev.currentTarget.offsetTop+'px';
		$('tagpopup').style.left = ev.currentTarget.offsetLeft+'px';
		$('tagtext').blur();
	}, false);
	$('newtag').addEventListener('focus', function () {
		$('newtag').value = $('newtag').value == _('new tag...') ? '' : $('newtag').value;
	}, false);
	$('newtag').addEventListener('blur', function () {
		$('newtag').value = $('newtag').value == '' ? _('new tag...') : $('newtag').value;
	}, false);
	$('closetagpopup').addEventListener('click', function () {
		$('tagtext').value = getSelectedTags().length+_(' tags selected');
		$('tagpopup').style.display = 'none';
	}, false);
}, false);
