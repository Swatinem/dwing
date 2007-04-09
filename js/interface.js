/*
 * dWing uses the following mootools components:
 * Core.Moo, Core.Utility, Core.Commom
 * Native.Array, Native.String, Native.Function, Native.Element
 * Fx.Base, Fx.CSS, Fx.Style, Fx.Styles
 * Remote.XHR
 * No Documentation Compression Type
 */

var xhtmlNS = 'http://www.w3.org/1999/xhtml';

var Throbber = {
	isOn: true,
	icon: null,
	textEffect: null,
	init: function()
	{
		this.icon = $('throbber');
		this.textEffect = $('throbbertext').effect('opacity', {duration: 200});
		this.off();
	},
	on: function()
	{
		if(!this.icon) return;
		this.textEffect.stop();
		this.textEffect.start(1);
		this.icon.addClass('active');
		this.isOn = true;
	},
	off: function()
	{
		if(!this.icon) return;
		this.textEffect.stop();
		this.textEffect.start(0);
		this.icon.removeClass('active');
		this.isOn = false;
	},
	toggle: function()
	{
		if(this.isOn)
			this.off();
		else
			this.on();
	}
};
window.addEventListener('load', function () { Throbber.init(); }, false);

var Messages = {
	isOn: false,
	effect: null,
	messageBox: null,
	noticeBox: null,
	warningBox: null,
	init: function()
	{
		this.noticeBox = $('noticebox');
		this.warningBox = $('warningbox');
		this.messageBox = $('messagebox');
		if(this.messageBox)
		{
			this.messageBox.style.display = 'block';
			this.effect = this.messageBox.effects({duration: 200});
			this.effect.set({'height': 0, 'opacity': 0});
			this.clear();
		}
	},
	show: function()
	{
		this.noticeBox.style.display = (this.noticeBox.childNodes.length == 0) ? 'none' : 'block';
		this.warningBox.style.display = (this.warningBox.childNodes.length == 0) ? 'none' : 'block';

		this.effect.stop();
		this.effect.start({'height': this.messageBox.scrollHeight, 'opacity': 1});
		this.isOn = true;
	},
	clear: function()
	{
		this.effect.stop();
		while(this.noticeBox.firstChild)
			this.noticeBox.removeChild(this.noticeBox.firstChild);
		while(this.warningBox.firstChild)
			this.warningBox.removeChild(this.warningBox.firstChild);
		this.effect.start({'height': 0, 'opacity': 0});
	},
	addNotice: function(aText)
	{
		var liElem = document.createElementNS(xhtmlNS, 'li');
		liElem.appendChild(document.createTextNode(aText));
		this.noticeBox.appendChild(liElem);
		this.show();
	},
	addWarning: function(aText)
	{
		var liElem = document.createElementNS(xhtmlNS, 'li');
		liElem.appendChild(document.createTextNode(aText));
		this.warningBox.appendChild(liElem);
		this.show();
	}
}
window.addEventListener('load', function () { Messages.init(); }, false);

function initInterface()
{
	if($('formcontent'))
	{
		$('formcontent').style.display = 'block';
		FormContentEffect = new fx.FadeSize('formcontent', {duration: 400});
		FormContentEffect.hide('height');
	}
	if($('opensubmitbutton'))
	{
		SubmitButtonTextFade = new fx.Opacity('opensubmitbutton', {duration: 200});
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