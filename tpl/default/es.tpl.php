<?php
header('Content-Type: text/javascript; charset=utf-8');
// jQuery first, after that is our own JS code
?>
// jQuery...

/*
    http://www.JSON.org/json2.js
    2008-11-19

    Public Domain.

    NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.

    See http://www.JSON.org/js.html

    This file creates a global JSON object containing two methods: stringify
    and parse.
*/

if(!this.JSON){JSON={};}(function(){function f(n){return n<10?'0'+n:n;}if(typeof Date.prototype.toJSON!=='function'){Date.prototype.toJSON=function(key){return this.getUTCFullYear()+'-'+f(this.getUTCMonth()+1)+'-'+f(this.getUTCDate())+'T'+f(this.getUTCHours())+':'+f(this.getUTCMinutes())+':'+f(this.getUTCSeconds())+'Z';};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(key){return this.valueOf();};}var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},rep;function quote(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);})+'"':'"'+string+'"';}function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key);}if(typeof rep==='function'){value=rep.call(holder,key,value);}switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null';}gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null';}v=partial.length===0?'[]':gap?'[\n'+gap+partial.join(',\n'+gap)+'\n'+mind+']':'['+partial.join(',')+']';gap=mind;return v;}if(rep&&typeof rep==='object'){length=rep.length;for(i=0;i<length;i+=1){k=rep[i];if(typeof k==='string'){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}else{for(k in value){if(Object.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+mind+'}':'{'+partial.join(',')+'}';gap=mind;return v;}}if(typeof JSON.stringify!=='function'){JSON.stringify=function(value,replacer,space){var i;gap='';indent='';if(typeof space==='number'){for(i=0;i<space;i+=1){indent+=' ';}}else if(typeof space==='string'){indent=space;}rep=replacer;if(replacer&&typeof replacer!=='function'&&(typeof replacer!=='object'||typeof replacer.length!=='number')){throw new Error('JSON.stringify');}return str('',{'':value});};}if(typeof JSON.parse!=='function'){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==='object'){for(k in value){if(Object.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v;}else{delete value[k];}}}}return reviver.call(holder,key,value);}cx.lastIndex=0;if(cx.test(text)){text=text.replace(cx,function(a){return'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);});}if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,''))){j=eval('('+text+')');return typeof reviver==='function'?walk({'':j},''):j;}throw new SyntaxError('JSON.parse');};}})();


_ = function(str)
{
	if(!langTable[str])
		return str;
	else
		return langTable[str];
};
langTable = {
<?php
$i = 0;
$langTable = l10n::getLangTable();
$max = count($langTable);
foreach($langTable as $from => $to):
$i++;
?>
	'<?php echo $from; ?>': '<?php echo $to; ?>'<?php if($i != $max) echo ','; ?>

<?php endforeach; ?>
};

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
// TODO: hook this up with a real JS Library
// TODO: progress using cursor: wait; style

function vote(aElem, aValue)
{
	ratingElem = document.getElementById(aElem);
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open('POST', aElem, true);
	xmlHttp.onreadystatechange = function () {
		if (xmlHttp.readyState == 4) {
			if(xmlHttp.status == 401)
			{
				alert(_('Not logged in.'));
				return;
			}
			if(xmlHttp.status != 200)
				return;
			var rating = JSON.parse(xmlHttp.responseText);
			ratingElem.className = 'rating score'+round(rating.average);
			ratingElem.lastChild.previousSibling.textContent =
				printf(_('%s ratings / %s average'), rating.ratings, round(rating.average, 1));
		}
	};
	xmlHttp.send(aValue);
}

function submitComment(e)
{
	data = {'text': document.getElementById('commenttext').value};

	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open('POST', e.target.action, true);
	xmlHttp.onreadystatechange = function () {
		if (xmlHttp.readyState == 4) {
			// TODO: add this comment to the DOM
			//alert(xmlHttp.status + "\n" + xmlHttp.responseText);
			return;
			if(xmlHttp.status == 401)
			{
				alert(_('Not logged in.'));
				return;
			}
			if(xmlHttp.status != 200)
				return;
			var comment = JSON.parse(xmlHttp.responseText);
		}
	};
	xmlHttp.send(JSON.stringify(data));
}
// TODO: hook up FCKeditor
window.addEventListener('load', function () {
	if(commentForm = document.getElementById('commentform'))
	{
		commentForm.addEventListener('submit', function(e) { e.preventDefault(); submitComment(e); }, false); 
	}
	// TODO: rating using js events, not hardlinked href="javascript" links
}, false);
