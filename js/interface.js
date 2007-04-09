/*
 * dWing uses the following mootools components:
 * Core.Moo, Core.Utility, Core.Commom
 * Native.Array, Native.String, Native.Function, Native.Element
 * Fx.Base, Fx.CSS, Fx.Style, Fx.Styles
 * Remote.XHR
 * No Documentation Compression Type
 */

var xhtmlNS = 'http://www.w3.org/1999/xhtml';


function include(src)
{
	var scriptElem = document.createElementNS(xhtmlNS, 'script');
	scriptElem.setAttributeNS(null, 'type', 'text/javascript');
	scriptElem.setAttributeNS(null, 'src', src);
	document.getElementsByTagName('head')[0].appendChild(scriptElem);
}
function initInterface()
{
	if($('formcontent'))
	{
		$('formcontent').style.display = 'block';
		FormContentEffect = new fx.FadeSize('formcontent', {duration: 400});
		FormContentEffect.hide('height');
	}
	if($('throbber'))
	{
		ThrobberTextFade = new fx.Opacity('throbbertext', {duration: 200});
		throbberIsOn = true;
		throbberOff();
	}
	if($('opensubmitbutton'))
	{
		SubmitButtonTextFade = new fx.Opacity('opensubmitbutton', {duration: 200});
	}
	if($('messagebox'))
	{
		$('messagebox').style.display = 'block';
		MessageBoxFade = new fx.FadeSize('messagebox', {duration: 200});
		MessageBoxFade.hide('height');
		messageIsOn = false;
		clearMessages();
	}
	if($('closeform'))
	{
		$('closeform').style.display = 'block';
		CloseFormButtonFade = new fx.Opacity('closeform', {duration: 400});
		CloseFormButtonFade.hide();
	}
}
function toggleForm()
{
	FormContentEffect.toggle('height');
}
function showMessages()
{
	// we need to do this the hard way until browsers support :empty correctly
	if($('noticebox').childNodes.length == 0)
		$('noticebox').style.display = 'none';
	else
		$('noticebox').style.display = 'block';
	if($('warningbox').childNodes.length == 0)
		$('warningbox').style.display = 'none';
	else
		$('warningbox').style.display = 'block';

	if(messageIsOn) return;
	MessageBoxFade.toggle('height');
	messageIsOn = true;
}
function clearMessages()
{
	while((noticebox = $('noticebox')).childNodes.length > 0)
		noticebox.removeChild(noticebox.childNodes[0]);
	while((warningbox = $('warningbox')).childNodes.length > 0)
		warningbox.removeChild(warningbox.childNodes[0]);
	if(!messageIsOn) return;
	MessageBoxFade.toggle('height');
	messageIsOn = false;
}
function addNotice(noticeText)
{
	var liElem = document.createElementNS(xhtmlNS, 'li');
	liElem.appendChild(document.createTextNode(noticeText));
	$('noticebox').appendChild(liElem);
	showMessages();
}
function addWarning(warningText)
{
	var liElem = document.createElementNS(xhtmlNS, 'li');
	liElem.appendChild(document.createTextNode(warningText));
	$('warningbox').appendChild(liElem);
	showMessages();
}
// keeping these two functions for now
function messageOn(type, text)
{
	if(type == 'error')
	{
		addWarning(text);
	}
	else
	{
		addNotice(text);
	}
	showMessages();
}
function messageOff()
{
	clearMessages();
}
function throbberOn()
{
	if(throbberIsOn) return;
	throbberIsOn = true;
	ThrobberTextFade.toggle();
	$('throbber').className = 'active';
}
function throbberOff()
{
	if(!throbberIsOn) return;
	throbberIsOn = false;
	ThrobberTextFade.toggle();
	$('throbber').className = '';
}
function throbberToggle()
{
	if(throbberIsOn)
		throbberOff();
	else
		throbberOn();
}
function urlEncode(dataArray)
{
	var encodedData = String();
	for(key in dataArray)
	{
		if(key == 'extend') continue; // prototype adds this function to arrays
		encodedData+= encodeURIComponent(key)+'='+encodeURIComponent(dataArray[key])+'&'; // encodeURIComponent does treat UTF-8 correctly
	}
	return encodedData.substr(0,encodedData.length-1); // cut off the last &
}
window.addEventListener('load', initInterface, false);