<?php
header('Content-Type: text/javascript; charset=utf-8');
Utils::allowCache(72);
// libraries first, after that is our own JS code

include($this->template('jquery.js'));
include($this->template('json2.js'));
include($this->template('jstemplates.js'));
?>

_ = function(str)
{
	return langTable[str] || str;
};
var langTable = <?php echo json_encode(l10n::$langTable); ?>;

function round(aNumber, aDecimal)
{
	if(aDecimal == undefined)
		aDecimal = 0;
	var factor = Math.pow(10, aDecimal);
	return Math.round(aNumber*factor)/factor;
}
function printf()
{
	var aFormat = arguments[0];
	for(var i = 1; i < arguments.length; i++)
	{
		aFormat = aFormat.replace(/%s/, arguments[i]);
	}
	return aFormat;
}

REST = {};
REST.POST = function RESTpost(aUrl, aData, aCallback, aCallbackParam)
{
	$($('body')[0]).addClass('progress');
	var req = new XMLHttpRequest();
	req.open('POST', aUrl, true);
	req.onreadystatechange = function () {
		if(req.readyState == 4)
		{
			$($('body')[0]).removeClass('progress');
			if(req.status == 401 || req.status == 403) // opera sets status to 403
			// even when the server sends a 401. :(
			{
				alert(_('Not logged in.'));
				return;
			}
			aCallback(req, aCallbackParam);
		}
	};
	req.send(aData);
}
REST.DELETE = function RESTdelete(aUrl, aCallback, aCallbackParam)
{
	$($('body')[0]).addClass('progress');
	var req = new XMLHttpRequest();
	req.open('DELETE', aUrl, true);
	req.onreadystatechange = function () {
		if(req.readyState == 4)
		{
			$($('body')[0]).removeClass('progress');
			if(req.status == 401 || req.status == 403) // opera sets status to 403
			// even when the server sends a 401. :(
			{
				alert(_('Not logged in.'));
				return;
			}
			aCallback(req, aCallbackParam);
		}
	};
	req.send(null);
}
function postStyle(aPost)
{
	if(!aPost.type)
		return;
	var str = '\
<div class="post" id="'+ aPost.type +'/'+ aPost.id +'">\
	<div class="postheader">';
	if(aPost.fancyurl && aPost.title)
	{
		str+= '<h1><a href="news/'+ aPost.fancyurl +'">'+ aPost.title +'</a></h1>';
	}
	str+= '<div class="postinfo">';
	if(aPost.user)
	{
		str+= '<span class="userinfo"><a href="user/'+ aPost.user.id +'">'+
			aPost.user.nick +'</a></span>'; // TODO: escape the user nick?!?
	}
	if(aPost.time)
	{
		str+= '<span class="dateinfo">'+ _('right now') +'</span>';
	}
	if(aPost.tags)
	{
		str+= '<span class="tagsinfo">';
		for(i in aPost.tags)
		{
			var tag = aPost.tags[i];
			str+= '<a href="news/tags/'+ tag +'">'+ tag +'</a> ';
		}
		str+= '</span>';
	}
	// TODO: comments?
			<?php /*
			if(($commentNum = count($post->comments)) > 0):
			?>
			<span class="commentinfo"><?php printf(l10n::_('%d comments'), $commentNum); ?></span>
			<?php
			endif;
			if(($rating = $post->rating) != null):
			?>
			<?php endif;*/ ?>
	// TODO: real rating?
	str+= '\
			<span class="rating score0">\
				<a>1</a><a>2</a><a>3</a><a>4</a><a>5</a>\
				<span class="ratingcaption">'+ printf(_('%s ratings / %s average'), 0, 0) +'</span>\
			</span>';
	var showDelete = <?php echo Core::$user->hasRight('news') ? 1 : 0; /* TODO: better rights management */ ?>;
	if(showDelete || false)
	{
		str+= '<span class="controls">';
		if(showDelete)
			str+= '<a class="delete">delete</a>';
		str+= '</span>';
	}
	str+= '</div>\
	</div>\
	<div class="postbody">\
	'+ aPost.text +'\
	</div>\
</div>';
	return str;
}
function getParentPost(aElem)
{
	var parent = aElem;
	while(!$(parent = parent.parentNode).hasClass('post'))
	{}
	return parent;
}
function registerMagicHandlers()
{
	registerRatingHandlers();
	registerDeleteHandlers();
}
function registerRatingHandlers()
{
	// hook up all the rating Widgets
	var ratingWidgets = document.getElementsByClassName('rating');
	for(var i = 0; i < ratingWidgets.length; i++)
	{
		var elem = ratingWidgets[i];
		var links = elem.getElementsByTagName('a');
		for(var j = 0; j < links.length; j++)
		{
			var link = links[j];
			// in case we call this method more than once:
			link.removeEventListener('click', ratingEventHandler, false);
			link.addEventListener('click', ratingEventHandler, false);
		}
	}
}
function ratingEventHandler(e)
{
	e.preventDefault();
	REST.POST(getParentPost(e.target).id+'/rating', e.target.textContent, function(req, e) {
		if(req.status != 200)
			return;
		var rating = JSON.parse(req.responseText);
		var ratingElem = e.target.parentNode;
		ratingElem.className = 'rating score'+round(rating.average);
		ratingElem.getElementsByClassName('ratingcaption')[0].textContent =
			printf(_('%s ratings / %s average'), rating.ratings, round(rating.average, 1));
	}, e);
}
function registerDeleteHandlers()
{
	// hook up all the delete Widgets
	var deleteWidgets = document.getElementsByClassName('delete');
	for(var i = 0; i < deleteWidgets.length; i++)
	{
		var elem = deleteWidgets[i];
		elem.removeEventListener('click', deleteEventHandler, false);
		elem.addEventListener('click', deleteEventHandler, false);
	}
}
function deleteEventHandler(e)
{
	e.preventDefault();
	if(!confirm(_('Are you sure?')))
		return;
	REST.DELETE(getParentPost(e.target).id, function(req, e) {
		var post = getParentPost(e.target);
		post.parentNode.removeChild(post); // remove the post from the DOM
	}, e);
}
function submitComment()
{
	var textArea = document.getElementById('commenttext');
	data = {'text': textArea.value};

	REST.POST(textArea.parentNode.action, JSON.stringify(data), function (req) {
		if(req.status != 200)
			return;
		var comment = JSON.parse(req.responseText);
		comment.type = 'comment';
		$(postStyle(comment)).appendTo('#newcomments'); // append this comment to
		// the DOM using jQuery
		registerMagicHandlers(); // so we can immediately rate the new comment
		if(window.FCKeditor)
		{
			var oEditor = FCKeditorAPI.GetInstance('commenttext');
			if(oEditor)
				oEditor.SetHTML('');
		}
		document.getElementById('commenttext').value = '';
	});
}
function submitNews()
{
	var textArea = document.getElementById('newstext');
	var title = document.getElementById('newstitle');
	var tags = document.getElementById('newstags');
	data = {'text': textArea.value, 'title': title.value, 'tags': tags.value};

	REST.POST(textArea.parentNode.action, JSON.stringify(data), function (req) {
		if(req.status != 200)
			return;
		var news = JSON.parse(req.responseText);
		news.type = 'news';
		$(postStyle(news)).prependTo('#newnews');
		registerMagicHandlers(); // so we can immediately rate the new news
		$('#newsform').hide();
		if(window.FCKeditor)
		{
			var oEditor = FCKeditorAPI.GetInstance('newstext');
			if(oEditor)
				oEditor.SetHTML('');
		}
		document.getElementById('newstext').value = '';
		document.getElementById('newstitle').value = '';
		document.getElementById('newstags').value = '';
	});
}
/*
 * https://bugzilla.mozilla.org/show_bug.cgi?id=45190
 * What the hell do they think they are doing?!?
 * So now that I spent hours to discover that submit EventListeners are
 * broken intentionally, I need to do this the quirky way :(
 *
 * FCKeditor overrides the submit method and pushes its own content into
 * the textarea and then calls our originally defined submit method
 *
 * Wouldn't it be good if we had EventListeners and bubbling for that?!?
 *
 * If the submit event is not initiated by scripts calling submit() but by the
 * user submitting the form via a <input type="submit" /> element then we
 * suddenly have the comfort of working EventListeners
 */
window.addEventListener('load', function () {
	var commentForm;
	var newsForm;
	if(commentForm = document.getElementById('commentform'))
	{
		if(window.FCKeditor)
		{
			commentForm.submit = function() {
				submitComment();
				return false;
			};
			var oFCKeditor = new FCKeditor('commenttext');
			oFCKeditor.Config['CustomConfigurationsPath'] = '/fckconfig';
			oFCKeditor.ToolbarSet = 'dWing';
			oFCKeditor.ReplaceTextarea();
			$('#commentformsubmit').hide();
		}
		else
		{
			commentForm.addEventListener('submit', function(e) {
				submitComment();
				e.preventDefault();
			}, true);
		}
	}
	if(newsForm = document.getElementById('newsform'))
	{
		document.getElementById('writenews').addEventListener('click', function (e) {
			$('#newsform').show();
			e.preventDefault();
		}, false);
		if(window.FCKeditor)
		{
			newsForm.submit = function() {
				submitNews();
				return false;
			};
			var oFCKeditor = new FCKeditor('newstext');
			oFCKeditor.Config['CustomConfigurationsPath'] = '/fckconfig';
			oFCKeditor.ToolbarSet = 'dWing';
			oFCKeditor.ReplaceTextarea();
			$('#newsformsubmit').hide();
		}
		else
		{
			newsForm.addEventListener('submit', function(e) {
				submitNews();
				e.preventDefault();
			}, true);
		}
	}
	/* OpenID Login special cases */
	var googleId;
	if(googleId = document.getElementById('googleid'))
	{
		googleId.addEventListener('click', function() {
			document.getElementById('openid_url').value = 'https://www.google.com/accounts/o8/id';
			// do not preventDefault since we want to submit the form
		}, false);
	}
	var yahooId;
	if(yahooId = document.getElementById('yahooid'))
	{
		yahooId.addEventListener('click', function() {
			document.getElementById('openid_url').value = 'http://yahoo.com';
			// do not preventDefault since we want to submit the form
		}, false);
	}

	var nickField;
	if(nickField = document.getElementById('nick'))
	{
		nickField.form.addEventListener('submit', function(ev) {
			REST.POST(nickField.form.action, JSON.stringify(nickField.value), function (req) {
				if(req.status != 200 || req.responseText != 'true')
				{
					alert(_('Error'));
					return;
				}
				// not the best solution, but I don't want to mess with the DOM right now
				document.location.reload();
			});
			ev.preventDefault();
		}, false);
	}

	registerMagicHandlers();
}, false);
