var imagestripInner = null;
var tagId = null;
var currentImage = null;
var newImage = null;
var imagestripScroll = null;
var outerWidth = null;
var imageData = null;
var selectedIndex = -1;
var PrevNextOffset = 0;
var preLoadCounter = 0;
var mouseOverPrevNext = false;
var ImageInfoEffect = null;
var lastLocation = null;
var ajaxImageTags = null;
var hideAnimCompleted = false;

function selectImage(screenNum)
{
	if(selectedIndex == screenNum)
		return;

	newImage = document.createElementNS(xhtmlNS, 'img');
	newImage.setAttributeNS(null, 'src', 'images/pictures/picture'+imageData[screenNum].id+'.jpg');
	var oldFade = new fx.Opacity(currentImage, {duration: 300});
	oldFade.toggle();
	window.setTimeout(function () {
		currentImage.parentNode.insertBefore(newImage, currentImage);
		currentImage.parentNode.removeChild(currentImage);
		currentImage = newImage;
		newImage = null;
		var newFade = new fx.Opacity(currentImage, {duration: 300});
		newFade.hide();
		newFade.toggle();

		//initDragScroll();
	}, 310);
	if(selectedIndex != -1) // on first selection
	{
		imageData[selectedIndex].aElem.className = '';
		ImageInfoEffect.toggle('height');
	}
	new Ajax.Request('index.php', {method: 'get', parameters: 'site=ajax_registerimageview&pic_id='+imageData[screenNum].id, onComplete: function (req) {
		xml = req.responseXML;
		ajaxImageTags = xml.evaluate('//result', xml, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null);
		if(hideAnimCompleted = true)
			updateImageInfo();
	}});
	imageData[screenNum].views++;

	window.setTimeout(function () {
		hideAnimCompleted = true;
		if(ajaxImageTags)
			updateImageInfo();
	}, 310);
	imageData[screenNum].aElem.className = 'selected';
	imagestripScroll.stop();
	imagestripScroll.start(-125*screenNum+outerWidth/2-62);
	selectedIndex = screenNum;
	loadVisiblePics(selectedIndex);
}

function updateImageInfo()
{
	$('imagetitle').innerHTML = imageData[selectedIndex].title;
	$('imageviews').innerHTML = imageData[selectedIndex].views;
	$('link').href = document.location.href;
	// update the tags:
	var imageTags = $('imagetags');
	while(imageTags.firstChild)
		imageTags.removeChild(imageTags.firstChild);
	while(imageTag = ajaxImageTags.iterateNext())
	{
		var aElem = document.createElementNS(xhtmlNS, 'a');
		aElem.setAttributeNS(null, 'href', 'imagestrip/'+imageTag.getAttribute('tag_id'));
		if(tagId != imageTag.getAttribute('tag_id'))
			aElem.appendChild(document.createTextNode(imageTag.getAttribute('name')));
		else
		{
			var strongElem = document.createElementNS(xhtmlNS, 'strong');
			strongElem.appendChild(document.createTextNode(imageTag.getAttribute('name')));
			aElem.appendChild(strongElem);
		}
		imageTags.appendChild(aElem);
		imageTags.appendChild(document.createTextNode(', '));
	}
	imageTags.removeChild(imageTags.lastChild); // last child is a ', ' textnode
	ImageInfoEffect.toggle('height');

	ajaxImageTags = null;
	hideAnimCompleted = false;
}

/*function initDragScroll()
{
	var imageContainer = $('imagecontainer');
	if(imageContainer.clientWidth < imageContainer.scrollWidth || imageContainer.clientHeight < imageContainer.scrollHeight)
	{
		imageContainer.className = 'drag';
	}
	currentImage.addEventListener('mousedown', function() {

	}, false);
}*/

function scrollLeft()
{
	imagestripScroll.set((imagestripScroll.now+(100-PrevNextOffset)/2) > (outerWidth/2-62) ? (outerWidth/2-62) : (imagestripScroll.now+(100-PrevNextOffset)/2));
	if(preLoadCounter++ >= 10)
	{
		loadVisiblePics();
		preLoadCounter = 0;
	}
	if(mouseOverPrevNext)
		window.setTimeout(scrollLeft, 25);
}

function scrollRight()
{
	imagestripScroll.set((imagestripScroll.now-PrevNextOffset/2) < (-125*(imageData.length-1)+outerWidth/2-62) ? (-125*(imageData.length-1)+outerWidth/2-62) : (imagestripScroll.now-PrevNextOffset/2));
	if(preLoadCounter++ >= 10)
	{
		loadVisiblePics();
		preLoadCounter = 0;
	}
	if(mouseOverPrevNext)
		window.setTimeout(scrollRight, 25);
}

function loadVisiblePics(forFocus)
{
	var currentFocus = forFocus ? forFocus : Math.floor((outerWidth/2-62-imagestripScroll.now)/125);
	var preLoadEachSide = Math.ceil(outerWidth/125);
	var from = Math.max(0, currentFocus-preLoadEachSide);
	var to = Math.min(imageData.length, currentFocus+preLoadEachSide);
	for(var i = from; i < to; i++)
	{
		if(!imageData[i].aElem.firstChild.getAttributeNS(null, 'src'))
		{
			imageData[i].aElem.firstChild.setAttributeNS(null, 'src', 'images/thumbs/picture'+imageData[i].id+'.jpg')
		}
	}
}

function locationListener()
{
	if(document.location.href != lastLocation)
	{
		var selectNow = document.location.href.split('#')[1] ? parseInt(document.location.href.split('#')[1]) : 0;
		selectImage(selectNow);
		lastLocation = document.location.href;
	}
	window.setTimeout(locationListener, 100);
}

window.addEventListener('load', function() {
	document.addEventListener('keypress', function(ev) {
		if(ev.keyCode == ev.DOM_VK_RIGHT && selectedIndex < imageData.length-1)
			selectImage(selectedIndex+1);
		else if (ev.keyCode == ev.DOM_VK_LEFT && selectedIndex > 0)
			selectImage(selectedIndex-1);
	}, false);

	$('imagestrip_previous_button').addEventListener('click', function () { selectImage(Math.max(selectedIndex-20, 0)); }, false);
	$('imagestrip_next_button').addEventListener('click', function () { selectImage(Math.min(selectedIndex+20, imageData.length-1)); }, false);

	$('imagestrip_previous').addEventListener('mouseover', function (ev) {
		if(ev.currentTarget != ev.target) return;
		mouseOverPrevNext = true;
		PrevNextOffset = ev.layerX ? ev.layerX : ev.pageX-ev.currentTarget.offsetLeft+1;
		scrollLeft();
	}, false);
	$('imagestrip_previous').addEventListener('mousemove', function (ev) {
		PrevNextOffset = ev.layerX ? ev.layerX : ev.pageX-ev.currentTarget.offsetLeft+1;
	}, false);
	$('imagestrip_previous').addEventListener('mouseout', function (ev) {
		mouseOverPrevNext = false;
	}, false);

	$('imagestrip_next').addEventListener('mouseover', function (ev) {
		if(ev.currentTarget != ev.target) return;
		mouseOverPrevNext = true;
		PrevNextOffset = ev.layerX ? ev.layerX : ev.pageX-ev.currentTarget.offsetLeft+1;
		scrollRight();
	}, false);
	$('imagestrip_next').addEventListener('mousemove', function (ev) {
		PrevNextOffset = ev.layerX ? ev.layerX : ev.pageX-ev.currentTarget.offsetLeft+1;
	}, false);
	$('imagestrip_next').addEventListener('mouseout', function (ev) {
		mouseOverPrevNext = false;
	}, false);

	window.addEventListener('resize', function() {
		var outerWidthOld = outerWidth;
		outerWidth = $('imagestrip_outer').offsetWidth;
		imagestripScroll.set(imagestripScroll.now+(outerWidth-outerWidthOld)/2);
	}, false);

	ImageInfoEffect = new fx.FadeSize('imageinfoouter', {duration: 300});
	ImageInfoEffect.hide('height');

	outerWidth = $('imagestrip_outer').offsetWidth;
	currentImage = $('imagestrip_image');
	(imagestripInner = $('imagestrip_inner')).style.width = imageData.length*125+'px';
	
	imagestripScroll = imagestripInner.effect('margin-left', {duration: 600});
	for(var i = 0; i < imageData.length; i++)
	{
		var aElem = document.createElementNS(xhtmlNS, 'a');
		imageData[i].aElem = aElem;
		aElem.screenNum = i;
		aElem.setAttributeNS(null, 'href', document.location.href.split('#')[0]+'#'+i)
		var imgElem = document.createElementNS(xhtmlNS, 'img');
		imgElem.setAttributeNS(null, 'alt', imageData[i].title)
		imgElem.setAttributeNS(null, 'title', imageData[i].title)
		aElem.appendChild(imgElem);
		imagestripInner.appendChild(aElem);
	}
	var firstselect = document.location.href.split('#')[1] ? parseInt(document.location.href.split('#')[1]) : 0;
	selectImage(firstselect);
	lastLocation = document.location.href;
	window.setTimeout(locationListener, 100);
}, false);