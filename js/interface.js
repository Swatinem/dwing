/*
 * dWing uses the following mootools components:
 * Core
 * Class, Class.Extras
 * Native.Array, Native.String, Native.Function, Native.Number, Native.Element
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
}

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
			this.effect.set({height: 0, opacity: 0});
			this.clear();
		}
	},
	show: function()
	{
		this.noticeBox.style.display = (this.noticeBox.childNodes.length == 0) ? 'none' : 'block';
		this.warningBox.style.display = (this.warningBox.childNodes.length == 0) ? 'none' : 'block';

		this.effect.stop();
		this.effect.start({height: this.messageBox.scrollHeight, opacity: 1});
		this.isOn = true;
	},
	clear: function()
	{
		this.effect.stop();
		while(this.noticeBox.firstChild)
			this.noticeBox.removeChild(this.noticeBox.firstChild);
		while(this.warningBox.firstChild)
			this.warningBox.removeChild(this.warningBox.firstChild);
		this.effect.start({height: 0, opacity: 0});
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

var DynamicForm = {
	content: null,
	submitButton: null,
	contentEffect: null,
	closeButtonEffect: null,
	submitButtonEffect: null,
	init: function()
	{
		this.content = $('formcontent');
		if(this.content)
		{
			this.content.style.display = 'block';
			this.contentEffect = this.content.effects({duration: 400});
			this.contentEffect.set({opacity: 0, height: 0});
		}
		
		closeButton = $('closeform');
		if(closeButton)
		{
			closeButton.style.display = 'block';
			this.closeButtonEffect = closeButton.effect('opacity', {duration: 400});
			this.closeButtonEffect.set(0);
		}
		this.submitButton = $('opensubmitbutton');
		if(this.submitButton)
		{
			this.submitButtonEffect = this.submitButton.effect('opacity', {duration: 200});
		}
	},
	open: function(aSubmitButtonText)
	{
		if(this.closeButtonEffect)
		{
			this.closeButtonEffect.stop();
			this.closeButtonEffect.start(1);
		}
		this.contentEffect.stop();
		this.contentEffect.start({opacity: 1, height: this.content.scrollHeight});

		this.submitButtonEffect.stop();
		this.submitButtonEffect.start(0);
		window.setTimeout(function(){
			if(aSubmitButtonText)
				DynamicForm.submitButton.innerHTML = aSubmitButtonText;
			DynamicForm.submitButtonEffect.stop();
			DynamicForm.submitButtonEffect.start(1);
		}, 200);
	},
	close: function(aSubmitButtonText)
	{
		if(this.closeButtonEffect)
		{
			this.closeButtonEffect.stop();
			this.closeButtonEffect.start(0);
		}
		this.contentEffect.stop();
		this.contentEffect.start({opacity: 0, height: 0});

		this.submitButtonEffect.stop();
		this.submitButtonEffect.start(0);
		window.setTimeout(function(){
			if(aSubmitButtonText)
				DynamicForm.submitButton.innerHTML = aSubmitButtonText;
			DynamicForm.submitButtonEffect.stop();
			DynamicForm.submitButtonEffect.start(1);
		}, 200);
	}
}

window.addEventListener('load', function () {
	Throbber.init();
	Messages.init();
	DynamicForm.init();
}, false);

/*
 * This function comes from Mootools Remote.Ajax
 */
urlEncode = Object.toQueryString = function(source){
	var queryString = [];
	for (var property in source) queryString.push(encodeURIComponent(property) + '=' + encodeURIComponent(source[property]));
	return queryString.join('&');
};