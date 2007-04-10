function submitPictures()
{
	postData = {directory: $('directory').value, title: $('title').value,
		tags: $('tags').value};
	if($('delold').checked)
		postData.delold = true;

	Throbber.on();
	Messages.clear();
	new XHR({onSuccess: function(text, xml) {
		result = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null).iterateNext();
		Throbber.off();
		if(result.getAttribute('success') > 0)
		{
			Messages.addNotice(result.firstChild.data);
			window.setTimeout(function () {
				window.location.href = window.location.href; // refresh
			}, 2000);
		}
		else
		{
			Messages.addWarning(result.firstChild.data);
		}
	}}).send('index.php?site=ajax_importpictures', Object.toQueryString(postData));
}
function openForm()
{
	DynamicForm.open();
	$('opensubmitbutton').removeEventListener('click', openForm, false);
	$('opensubmitbutton').addEventListener('click', submitPictures, false);
}
function closeForm()
{
	DynamicForm.close();
	$('opensubmitbutton').removeEventListener('click', submitPictures, false);
	$('opensubmitbutton').addEventListener('click', openForm, false);
}
window.addEventListener('load', function () {
	$('opensubmitbutton').addEventListener('click', openForm, false);
	$('closeform').addEventListener('click', function() { closeForm(); Messages.clear(); }, false);
	$('form').addEventListener('submit', function(e) { e.preventDefault(); submitPictures(); }, false);
}, false);