//MooTools, My Object Oriented Javascript Tools. Copyright (c) 2006 Valerio Proietti, <http://mad4milk.net>, MIT Style License.

var MooTools = {
	'version': '1.2dev',
	'build': '766'
};

function $extend(){
	var args = arguments;
	if (!args[1]) args = [this, args[0]];
	for (var property in args[1]) args[0][property] = args[1][property];
	return args[0];
};

var Native = function(){
	for (var i = 0, l = arguments.length; i < l; i++){
		arguments[i].extend = function(props){
			for (var prop in props){
				if (!this.prototype[prop]) this.prototype[prop] = props[prop];
				if (!this[prop]) this[prop] = Native.generic(prop);
			}
		};
	}
};

Native.generic = function(prop){
	return function(bind){
		return this.prototype[prop].apply(bind, Array.prototype.slice.call(arguments, 1));
	};
};

Native.setFamily = function(natives){
	for (var type in natives) natives[type].prototype.$family = type;
};

Native(Array, Function, String, RegExp, Number);
Native.setFamily({'array': Array, 'function': Function, 'string': String, 'regexp': RegExp});

function $A(iterable, start, length){
	start = start || 0;
	if (start < 0) start = iterable.length + start;
	length = length || (iterable.length - start);
	var newArray = [];
	for (var i = 0; i < length; i++) newArray[i] = iterable[start++];
	return newArray;
};

function $chk(obj){
	return !!(obj || obj === 0);
};

function $clear(timer){
	clearTimeout(timer);
	clearInterval(timer);
	return null;
};

function $defined(obj){
	return (obj != undefined);
};

function $empty(){};

function $merge(){
	var mix = {};
	for (var i = 0; i < arguments.length; i++){
		for (var property in arguments[i]){
			var ap = arguments[i][property];
			var mp = mix[property];
			if (mp && $type(ap) == 'object' && $type(mp) == 'object') mix[property] = $merge(mp, ap);
			else mix[property] = ap;
		}
	}
	return mix;
};

function $pick(){
	for (var i = 0, l = arguments.length; i < l; i++){
		if ($defined(arguments[i])) return arguments[i];
	}
	return null;
};

function $random(min, max){
	return Math.floor(Math.random() * (max - min + 1) + min);
};

function $splat(obj){
	var type = $type(obj);
	if (type && type != 'array') obj = [obj];
	return obj;
};

function $time(){
	return new Date().getTime();
};

function $try(fn, bind, args){
	try {
		return fn.apply(bind || fn, $splat(args) || []);
	} catch(e){
		return false;
	}
};

function $type(obj){
	if (obj == undefined) return false;
	if (obj.$family) return obj.$family;
	if (obj.htmlElement) return 'element';
	var type = typeof obj;
	if (obj.nodeName){
		switch (obj.nodeType){
			case 1: return 'element';
			case 3: return (/\S/).test(obj.nodeValue) ? 'textnode' : 'whitespace';
		}
	} else if (typeof obj.length == 'number'){
		if (obj.item) return 'collection';
		if (obj.callee) return 'arguments';
	}
	if (type == 'number' && !isFinite(obj)) return false;
	return type;
};
window.extend = document.extend = $extend;
window.$family = 'window';
document.$family = 'document';
document.head = document.getElementsByTagName('head')[0];

var Client = {
	Engine: {'name': 'unknown', 'version': ''},
	Platform: {},
	Features: {}
};
Client.Features.xhr = !!(window.XMLHttpRequest);
Client.Features.xpath = !!(document.evaluate);
if (window.opera) Client.Engine.name = 'opera';
else if (window.ActiveXObject) Client.Engine = {'name': 'ie', 'version': (Client.Features.xhr) ? 7 : 6};
else if (!navigator.taintEnabled) Client.Engine = {'name': 'webkit', 'version': (Client.Features.xpath) ? 420 : 419};
else if (document.getBoxObjectFor != null) Client.Engine.name = 'gecko';
Client.Engine[Client.Engine.name] = Client.Engine[Client.Engine.name + Client.Engine.version] = true;
Client.Platform.name = navigator.platform.match(/(mac)|(win)|(linux)|(nix)/i) || ['Other'];
Client.Platform.name = Client.Platform.name[0].toLowerCase();
Client.Platform[Client.Platform.name] = true;
if (typeof HTMLElement == 'undefined'){
	var HTMLElement = $empty;
	if (Client.Engine.webkit) document.createElement("iframe");
	HTMLElement.prototype = (Client.Engine.webkit) ? window["[[DOMElement.prototype]]"] : {};
}
HTMLElement.prototype.htmlElement = $empty;
HTMLElement.prototype.$family = 'element';
if (Client.Engine.ie6) $try(function(){
	document.execCommand("BackgroundImageCache", false, true);
});

var Class = function(properties){
	properties = properties || {};
	var klass = function(){
		var self = (arguments[0] !== $empty && this.initialize && $type(this.initialize) == 'function') ? this.initialize.apply(this, arguments) : this;
		if (this.options && this.options.initialize) this.options.initialize.call(this);
		return self;
	};

	if (properties.Implements){
		$extend(properties, Class.implement($splat(properties.Implements)));
		delete properties.Implements;
	}

	if (properties.Extends){
		properties = Class.extend(properties.Extends, properties);
		delete properties.Extends;
	}

	$extend(klass, this);
	klass.prototype = properties;
	klass.prototype.constructor = klass;
	klass.$family = 'class';
	return klass;
};

Class.empty = $empty;

Class.prototype = {

	constructor: Class,

	extend: function(properties){
		return new Class(Class.extend(this, properties));
	},

	implement: function(){
		$extend(this.prototype, Class.implement($A(arguments)));
		return this;
	}

};

Class.implement = function(sets){
	var all = {};
	for (var i = 0, l = sets.length; i < l; i++) $extend(all, ($type(sets[i]) == 'class') ? new sets[i]($empty) : sets[i]);
	return all;
};

Class.extend = function(klass, properties){
	var proto = new klass($empty);
	for (var property in properties){
		var pp = proto[property];
		proto[property] = Class.merge(pp, properties[property]);
	}
	return proto;
};

Class.merge = function(previous, current){
	if ($defined(previous) && previous != current){
		var type = $type(current);
		if (type != $type(previous)) return current;
		switch (type){
			case 'function':
				var merged = function(){
					this.parent = arguments.callee.parent;
					return current.apply(this, arguments);
				};
				merged.parent = previous;
				return merged;
			case 'object': return $merge(previous, current);
		}
	}
	return current;
};

var Abstract = function(obj){
	return $extend(this, obj || {});
};

Native(Abstract);

Abstract.extend({

	each: function(fn, bind){
		for (var property in this){
			if (this.hasOwnProperty(property)) fn.call(this || bind, this[property], property);
		}
	},

	remove: function(property){
		delete this[property];
		return this;
	},

	extend: $extend

});

Client = new Abstract(Client);

Class.Chain = new Class({

	chain: function(fn){
		this.$chain = this.$chain || [];
		this.$chain.push(fn);
		return this;
	},

	callChain: function(){
		if (this.$chain && this.$chain.length) this.$chain.shift().delay(10, this);
	},

	clearChain: function(){
		if (this.$chain) this.$chain.empty();
	}

});

Class.Events = new Class({

	addEvent: function(type, fn, internal){
		if (fn != $empty){
			this.$events = this.$events || {};
			this.$events[type] = this.$events[type] || [];
			this.$events[type].include(fn);
			if (internal) fn.internal = true;
		}
		return this;
	},

	addEvents: function(events){
		for (var type in events) this.addEvent(type, events[type]);
		return this;
	},

	fireEvent: function(type, args, delay){
		if (this.$events && this.$events[type]){
			this.$events[type].each(function(fn){
				fn.create({'bind': this, 'delay': delay, 'arguments': args})();
			}, this);
		}
		return this;
	},

	removeEvent: function(type, fn){
		if (this.$events && this.$events[type]){
			if (!fn.internal) this.$events[type].remove(fn);
		}
		return this;
	},

	removeEvents: function(type){
		for (var e in this.$events){
			if (type && type != e) continue;
			this.$events[e].each(function(fn){
				this.removeEvent(e, fn);
			}, this);
		}
		return this;
	}

});

Class.Options = new Class({

	setOptions: function(options){
		this.options = $merge(this.options, options);
		if (this.addEvent){
			for (var option in this.options){
				if ((/^on[A-Z]/).test(option) && $type(this.options[option] == 'function')) this.addEvent(option, this.options[option]);
			}
		}
		return this;
	}

});

Array.extend({

	every: function(fn, bind){
		for (var i = 0, l = this.length; i < l; i++){
			if (!fn.call(bind, this[i], i, this)) return false;
		}
		return true;
	},

	filter: function(fn, bind){
		var results = [];
		for (var i = 0, l = this.length; i < l; i++){
			if (fn.call(bind, this[i], i, this)) results.push(this[i]);
		}
		return results;
	},

	forEach: function(fn, bind){
		for (var i = 0, l = this.length; i < l; i++) fn.call(bind, this[i], i, this);
	},

	indexOf: function(item, from){
		var len = this.length;
		for (var i = (from < 0) ? Math.max(0, len + from) : from || 0; i < len; i++){
			if (this[i] === item) return i;
		}
		return -1;
	},

	map: function(fn, bind){
		var results = [];
		for (var i = 0, l = this.length; i < l; i++) results[i] = fn.call(bind, this[i], i, this);
		return results;
	},

	some: function(fn, bind){
		for (var i = 0, l = this.length; i < l; i++){
			if (fn.call(bind, this[i], i, this)) return true;
		}
		return false;
	},

	reduce: function(fn, value){
		var i = 0;
		if (arguments.length < 2 && this.length) value = this[i++];
		for (l = this.length; i < l; i++) value = fn.call(null, value, this[i], i, this);
		return value;
	},

	associate: function(obj){
		var routed = {};
		var objtype = $type(obj);
		if (objtype == 'array'){
			var temp = {};
			for (var i = 0, j = obj.length; i < j; i++) temp[obj[i]] = true;
			obj = temp;
		}
		for (var oname in obj) routed[oname] = null;
		for (var k = 0, l = this.length; k < l; k++){
			var res = (objtype == 'array') ? $defined(this[k]) : $type(this[k]);
			for (var name in obj){
				if (!$defined(routed[name]) && ((res && obj[name] === true) || obj[name].contains(res))){
					routed[name] = this[k];
					break;
				}
			}
		}
		return routed;
	},

	contains: function(item, from){
		return this.indexOf(item, from) != -1;
	},

	copy: function(start, length){
		return $A(this, start, length);
	},

	extend: function(array){
		for (var i = 0, j = array.length; i < j; i++) this.push(array[i]);
		return this;
	},

	getLast: function(){
		return (this.length) ? this[this.length - 1] : null;
	},

	getRandom: function(){
		return (this.length) ? this[$random(0, this.length - 1)] : null;
	},

	include: function(item){
		if (!this.contains(item)) this.push(item);
		return this;
	},

	merge: function(array){
		for (var i = 0, l = array.length; i < l; i++) this.include(array[i]);
		return this;
	},

	remove: function(item){
		var i = 0;
		var len = this.length;
		while (i < len){
			if (this[i] === item){
				this.splice(i, 1);
				len--;
			} else {
				i++;
			}
		}
		return this;
	},

	empty: function(){
		this.length = 0;
		return this;
	}

});
Array.prototype.each = Array.prototype.forEach;
Array.each = Array.forEach;

function $each(iterable, fn, bind){
	if (iterable && typeof iterable.length == 'number' && $type(iterable) != 'object'){
		Array.forEach(iterable, fn, bind);
	} else {
		 for (var name in iterable){
			if (iterable.hasOwnProperty(name)) fn.call(bind || iterable, iterable[name], name);
		}
	}
};

String.extend({

	test: function(regex, params){
		return (($type(regex) == 'string') ? new RegExp(regex, params) : regex).test(this);
	},

	contains: function(string, separator){
		return (separator) ? (separator + this + separator).indexOf(separator + string + separator) > -1 : this.indexOf(string) > -1;
	},

	trim: function(){
		return this.replace(/^\s+|\s+$/g, '');
	},

	clean: function(){
		return this.replace(/\s{2,}/g, ' ').trim();
	},

	camelCase: function(){
		return this.replace(/-\D/g, function(match){
			return match.charAt(1).toUpperCase();
		});
	},

	hyphenate: function(){
		return this.replace(/[A-Z]/g, function(match){
			return ('-' + match.charAt(0).toLowerCase());
		});
	},

	capitalize: function(){
		return this.replace(/\b[a-z]/g, function(match){
			return match.toUpperCase();
		});
	},

	escapeRegExp: function(){
		return this.replace(/([.*+?^${}()|[\]\/\\])/g, '\\$1');
	},
	toInt: function(base){
		return parseInt(this, base || 10);
	},

	toFloat: function(){
		return parseFloat(this);
	},

	hexToRgb: function(array){
		var hex = this.match(/^#?(\w{1,2})(\w{1,2})(\w{1,2})$/);
		return (hex) ? hex.slice(1).hexToRgb(array) : false;
	},

	rgbToHex: function(array){
		var rgb = this.match(/\d{1,3}/g);
		return (rgb) ? rgb.rgbToHex(array) : false;
	}

});

Array.extend({

	hexToRgb: function(array){
		if (this.length != 3) return false;
		var rgb = [];
		for (var i = 0; i < 3; i++){
			rgb.push(((this[i].length == 1) ? this[i] + this[i] : this[i]).toInt(16));
		}
		return array ? rgb : 'rgb(' + rgb.join(',') + ')';
	},

	rgbToHex: function(array){
		if (this.length < 3) return false;
		if (this.length == 4 && this[3] == 0 && !array) return 'transparent';
		var hex = [];
		for (var i = 0; i < 3; i++){
			var bit = (this[i] - 0).toString(16);
			hex.push((bit.length == 1) ? '0' + bit : bit);
		}
		return array ? hex : '#' + hex.join('');
	}

});

Function.extend({

	extend: function(properties){
		for (var property in properties) this[property] = properties[property];
	},

	create: function(options){
		var self = this;
		options = $merge({
			'bind': self,
			'arguments': null,
			'delay': false,
			'periodical': false,
			'attempt': false,
			'event': false
		}, options);
		return function(event){
			var args = $splat(options.arguments) || arguments;
			if (options.event) args = [event || window.event].extend(args);
			var returns = function(){
				return self.apply($pick(options.bind, self), args);
			};
			if (options.delay) return setTimeout(returns, options.delay);
			if (options.periodical) return setInterval(returns, options.periodical);
			if (options.attempt) return $try(returns);
			return returns();
		};
	},

	pass: function(args, bind){
		return this.create({'arguments': args, 'bind': bind});
	},

	attempt: function(args, bind){
		return this.create({'arguments': args, 'bind': bind, 'attempt': true})();
	},

	bind: function(bind, args, evt){
		return this.create({'bind': bind, 'arguments': args, 'event': evt});
	},

	delay: function(delay, bind, args){
		return this.create({'delay': delay, 'bind': bind, 'arguments': args})();
	},

	periodical: function(interval, bind, args){
		return this.create({'periodical': interval, 'bind': bind, 'arguments': args})();
	}

});

Function.empty = $empty;

Number.extend({

	toInt: function(base){
		return parseInt(this, base || 10);
	},

	toFloat: function(){
		return parseFloat(this);
	},

	limit: function(min, max){
		return Math.min(max, Math.max(min, this));
	},

	round: function(precision){
		precision = Math.pow(10, precision || 0);
		return Math.round(this * precision) / precision;
	},

	times: function(fn, bind){
		for (var i = 0; i < this; i++) fn.call(bind, i, this);
	}

});

var Element = function(el, props){

	if ($type(el) == 'string'){
		if (Client.Engine.ie && props && (props.name || props.type)){
			var name = (props.name) ? ' name="' + props.name + '"' : '';
			var type = (props.type) ? ' type="' + props.type + '"' : '';
			delete props.name;
			delete props.type;
			el = '<' + el + name + type + '>';
		}
		el = document.createElement(el);
	}
	el = $(el);
	return (!props || !el) ? el : el.set(props);
};

var Elements = function(elements, nocheck){
	elements = elements || [];
	if (nocheck) return $extend(elements, this);
	var uniques = {};
	var returned = [];
	for (var i = 0, j = elements.length; i < j; i++){
		var el = $(elements[i]);
		if (!el || uniques[el.$attributes.uid]) continue;
		uniques[el.$attributes.uid] = true;
		returned.push(el);
	}
	return $extend(returned, this);
};

function $(el){
	if (!el) return null;
	if (el.htmlElement) return Garbage.collect(el);
	var type = $type(el);
	if (type == 'string'){
		el = document.getElementById(el);
		type = (el) ? 'element' : false;
	}
	if (type != 'element') return (['window', 'document'].contains(type)) ? el : null;
	if (el.htmlElement) return Garbage.collect(el);
	if (Element.$badTags.contains(el.tagName.toLowerCase())) return el;
	$extend(el, Element.prototype);
	el.htmlElement = $empty;
	return Garbage.collect(el);
};

document.getElementsBySelector = document.getElementsByTagName;

function $$(){
	var elements = [];
	for (var i = 0, j = arguments.length; i < j; i++){
		var selector = arguments[i];
		switch ($type(selector)){
			case 'element': elements.push(selector); break;
			case false: break;
			case 'string': selector = document.getElementsBySelector(selector, true);
			default: elements.extend(selector);
		}
	}
	return new Elements(elements);
};

Element.extend = function(properties){
	for (var property in properties){
		HTMLElement.prototype[property] = Element.prototype[property] = properties[property];
		Element[property] = Native.generic(property);
		Elements.prototype[(Array.prototype[property]) ? property + 'Elements' : property] = Elements.$multiply(property);
	}
};

Elements.extend = function(properties){
	for (var property in properties){
		Elements.prototype[property] = properties[property];
		Elements[property] = Native.generic(property);
	}
};

Elements.$multiply = function(property){
	return function(){
		var args = arguments;
		var items = [];
		var elements = true;
		this.each(function(element){
			var returns = element[property].apply(element, args);
			items.push(returns);
			if (elements) elements = ($type(returns) == 'element');
		});
		return (elements) ? new Elements(items) : items;
	};
};

Element.extend({

	getElement: function(tag){
		return $(this.getElementsByTagName(tag)[0] || null);
	},

	getElements: function(tag){
		return $$(this.getElementsByTagName(tag));
	},

	set: function(props){
		for (var prop in props){
			var val = props[prop];
			switch (prop){
				case 'styles': this.setStyles(val); break;
				case 'events': if (this.addEvents) this.addEvents(val); break;
				case 'properties': this.setProperties(val); break;
				default: this.setProperty(prop, val);
			}
		}
		return this;
	},

	inject: function(el, where){
		el = $(el);
		switch (where){
			case 'before': el.parentNode.insertBefore(this, el); break;
			case 'after':
				var next = el.getNext();
				if (!next) el.parentNode.appendChild(this);
				else el.parentNode.insertBefore(this, next);
				break;
			case 'top':
				var first = el.firstChild;
				if (first){
					el.insertBefore(this, first);
					break;
				}
			default: el.appendChild(this);
		}
		return this;
	},

	injectBefore: function(el){
		return this.inject(el, 'before');
	},

	injectAfter: function(el){
		return this.inject(el, 'after');
	},

	injectInside: function(el){
		return this.inject(el, 'bottom');
	},

	injectTop: function(el){
		return this.inject(el, 'top');
	},

	adopt: function(){
		var elements = [];
		$each(arguments, function(argument){
			elements = elements.concat(argument);
		});
		$$(elements).inject(this);
		return this;
	},

	remove: function(){
		return this.parentNode.removeChild(this);
	},

	clone: function(contents){
		var el = $(this.cloneNode(contents !== false));
		if (!el.$events) return el;
		el.$events = {};
		for (var type in this.$events) el.$events[type] = {
			'keys': $A(this.$events[type].keys),
			'values': $A(this.$events[type].values)
		};
		return el.removeEvents();
	},

	replaceWith: function(el){
		el = $(el);
		this.parentNode.replaceChild(el, this);
		return el;
	},

	appendText: function(text){
		this.appendChild(document.createTextNode(text));
		return this;
	},

	hasClass: function(className){
		return this.className.contains(className, ' ');
	},

	addClass: function(className){
		if (!this.hasClass(className)) this.className = (this.className + ' ' + className).clean();
		return this;
	},

	removeClass: function(className){
		this.className = this.className.replace(new RegExp('(^|\\s)' + className + '(?:\\s|$)'), '$1').clean();
		return this;
	},

	toggleClass: function(className){
		return this.hasClass(className) ? this.removeClass(className) : this.addClass(className);
	},

	setStyle: function(property, value){
		switch (property){
			case 'opacity': return this.setOpacity(parseFloat(value));
			case 'float': property = (Client.Engine.ie) ? 'styleFloat' : 'cssFloat';
		}
		property = property.camelCase();
		switch ($type(value)){
			case 'number': if (!['zIndex', 'zoom', 'fontWeight'].contains(property)) value += 'px'; break;
			case 'array': value = 'rgb(' + value.join(',') + ')';
		}
		this.style[property] = value;
		return this;
	},

	setStyles: function(source){
		switch ($type(source)){
			case 'object': Element.$setMany(this, 'setStyle', source); break;
			case 'string': this.style.cssText = source;
		}
		return this;
	},

	setOpacity: function(opacity){
		if (opacity == 0){
			if (this.style.visibility != "hidden") this.style.visibility = "hidden";
		} else {
			if (this.style.visibility != "visible") this.style.visibility = "visible";
		}
		if (!this.currentStyle || !this.currentStyle.hasLayout) this.style.zoom = 1;
		if (Client.Engine.ie) this.style.filter = (opacity == 1) ? '' : "alpha(opacity=" + opacity * 100 + ")";
		this.style.opacity = this.$attributes.opacity = opacity;
		return this;
	},

	getStyle: function(property){
		property = property.camelCase();
		var result = this.style[property];
		if (!$chk(result)){
			if (property == 'opacity') return this.$attributes.opacity;
			result = [];
			for (var style in Element.$styles){
				if (property == style){
					Element.$styles[style].each(function(s){
						var style = this.getStyle(s);
						result.push(parseInt(style) ? style : '0px');
					}, this);
					if (property == 'border'){
						var every = result.every(function(bit){
							return (bit == result[0]);
						});
						return (every) ? result[0] : false;
					}
					return result.join(' ');
				}
			}
			if (property.contains('border')){
				if (Element.$styles.border.contains(property)){
					return ['Width', 'Style', 'Color'].map(function(p){
						return this.getStyle(property + p);
					}, this).join(' ');
				} else if (Element.$borderShort.contains(property)){
					return ['Top', 'Right', 'Bottom', 'Left'].map(function(p){
						return this.getStyle('border' + p + property.replace('border', ''));
					}, this).join(' ');
				}
			}
			if (document.defaultView) result = document.defaultView.getComputedStyle(this, null).getPropertyValue(property.hyphenate());
			else if (this.currentStyle) result = this.currentStyle[property];
		}
		if (Client.Engine.ie) result = Element.$fixStyle(property, result, this);
		if (result && property.test(/color/i) && result.contains('rgb')){
			return result.split('rgb').splice(1,4).map(function(color){
				return color.rgbToHex();
			}).join(' ');
		}
		return result;
	},

	getStyles: function(){
		return Element.$getMany(this, 'getStyle', arguments);
	},

	walk: function(brother, start){
		brother += 'Sibling';
		var el = (start) ? this[start] : this[brother];
		while (el && $type(el) != 'element') el = el[brother];
		return $(el);
	},

	getPrevious: function(){
		return this.walk('previous');
	},

	getNext: function(){
		return this.walk('next');
	},

	getFirst: function(){
		return this.walk('next', 'firstChild');
	},

	getLast: function(){
		return this.walk('previous', 'lastChild');
	},

	getParent: function(){
		return $(this.parentNode);
	},

	getChildren: function(){
		return $$(this.childNodes);
	},

	hasChild: function(el){
		return !!$A(this.getElementsByTagName('*')).contains(el);
	},

	getProperty: function(property){
		var index = Element.$properties[property];
		if (index) return this[index];
		var flag = Element.$propertiesIFlag[property] || 0;
		if (!Client.Engine.ie || flag) return this.getAttribute(property, flag);
		var node = this.attributes[property];
		return (node) ? node.nodeValue : null;
	},

	removeProperty: function(property){
		var index = Element.$properties[property];
		if (index) this[index] = '';
		else this.removeAttribute(property);
		return this;
	},

	getProperties: function(){
		return Element.$getMany(this, 'getProperty', arguments);
	},

	setProperty: function(property, value){
		var index = Element.$properties[property];
		if (index) this[index] = value;
		else this.setAttribute(property, value);
		return this;
	},

	setProperties: function(source){
		return Element.$setMany(this, 'setProperty', source);
	},

	setHTML: function(){
		this.innerHTML = $A(arguments).join('');
		return this;
	},

	setText: function(text){
		var tag = this.getTag();
		if (['style', 'script'].contains(tag)){
			if (Client.Engine.ie){
				if (tag == 'style') this.styleSheet.cssText = text;
				else if (tag ==  'script') this.setProperty('text', text);
				return this;
			} else {
				if (this.firstChild) this.removeChild(this.firstChild);
				return this.appendText(text);
			}
		}
		this[$defined(this.innerText) ? 'innerText' : 'textContent'] = text;
		return this;
	},

	getText: function(){
		var tag = this.getTag();
		if (['style', 'script'].contains(tag)){
			if (Client.Engine.ie){
				if (tag == 'style') return this.styleSheet.cssText;
				else if (tag ==  'script') return this.getProperty('text');
			} else {
				return this.innerHTML;
			}
		}
		return ($pick(this.innerText, this.textContent));
	},

	getTag: function(){
		return this.tagName.toLowerCase();
	},

	empty: function(){
		Garbage.trash(this.getElementsByTagName('*'));
		return this.setHTML('');
	},

	destroy: function(){
		Garbage.trash([this.empty().remove()]);
		return null;
	}

});

Element.$fixStyle = function(property, result, element){
	if ($chk(parseInt(result))) return result;
	if (['height', 'width'].contains(property)){
		var values = (property == 'width') ? ['left', 'right'] : ['top', 'bottom'];
		var size = 0;
		values.each(function(value){
			size += element.getStyle('border-' + value + '-width').toInt() + element.getStyle('padding-' + value).toInt();
		});
		return element['offset' + property.capitalize()] - size + 'px';
	} else if (property.test(/border(.+)Width|margin|padding/)){
		return '0px';
	}
	return result;
};

Element.$badTags = ['object', 'embed'];

Element.$styles = {'border': [], 'padding': [], 'margin': []};
['Top', 'Right', 'Bottom', 'Left'].each(function(direction){
	for (var style in Element.$styles) Element.$styles[style].push(style + direction);
});

Element.$borderShort = ['borderWidth', 'borderStyle', 'borderColor'];

Element.$getMany = function(el, method, keys){
	var result = {};
	$each(keys, function(key){
		result[key] = el[method](key);
	});
	return result;
};

Element.$setMany = function(el, method, pairs){
	for (var key in pairs) el[method](key, pairs[key]);
	return el;
};

Element.$properties = {
	'class': 'className', 'for': 'htmlFor', 'colspan': 'colSpan', 'rowspan': 'rowSpan',
	'accesskey': 'accessKey', 'tabindex': 'tabIndex', 'maxlength': 'maxLength',
	'readonly': 'readOnly', 'frameborder': 'frameBorder', 'value': 'value',
	'disabled': 'disabled', 'checked': 'checked', 'multiple': 'multiple', 'selected': 'selected'
};

Element.$propertiesIFlag = {
	'href': 2, 'src': 2
};

Element.$listenerMethods = {

	addListener: function(type, fn){
		if (this.addEventListener) this.addEventListener(type, fn, false);
		else this.attachEvent('on' + type, fn);
		return this;
	},

	removeListener: function(type, fn){
		if (this.removeEventListener) this.removeEventListener(type, fn, false);
		else this.detachEvent('on' + type, fn);
		return this;
	}

};

window.extend(Element.$listenerMethods);
document.extend(Element.$listenerMethods);
Element.extend(Element.$listenerMethods);

Element.UID = 0;

var Garbage = {

	elements: {},

	collect: function(el){
		if (!el.$attributes){
			el.$attributes = {'opacity': 1, 'uid': Element.UID++};
			Garbage.elements[el.$attributes.uid] = el;
		}
		return el;
	},

	trash: function(elements){
		for (var i = 0, j = elements.length, el; i < j; i++){
			if (!(el = elements[i]) || !el.$attributes) continue;
			if (el.tagName && Element.$badTags.contains(el.tagName.toLowerCase())) continue;
			Garbage.kill(el);
		}
	},

	kill: function(el, isUnload){
		delete Garbage.elements[String(el.$attributes.uid)];
		if (el.$events){
			el.fireEvent('trash', [isUnload]);
			el.removeEvents();
		}
		for (var p in el.$attributes) el.$attributes[p] = null;
		for (var d in Element.prototype) el[d] = null;
		el.htmlElement = el.$attributes = el = null;
	},

	empty: function(){
		Garbage.collect(window);
		Garbage.collect(document);
		for (var uid in Garbage.elements) Garbage.kill(Garbage.elements[uid], true);
	}

};

window.addListener('beforeunload', function(){
	window.addListener('unload', Garbage.empty);
	if (Client.Engine.ie) window.addListener('unload', CollectGarbage);
});

var Fx = {};

Fx.Base = new Class({

	Implements: [Class.Chain, Class.Events, Class.Options],

	options: {
		onStart: $empty,
		onComplete: $empty,
		onCancel: $empty,
		transition: function(p){
			return -(Math.cos(Math.PI * p) - 1) / 2;
		},
		duration: 500,
		unit: 'px',
		wait: true,
		fps: 50
	},

	initialize: function(){
		var params = $A(arguments).associate({'options': 'object', 'element': true});
		this.element = this.element || params.element;
		this.setOptions(params.options);
	},

	step: function(){
		var time = $time();
		if (time < this.time + this.options.duration){
			this.delta = this.options.transition((time - this.time) / this.options.duration);
			this.setNow();
			this.increase();
		} else {
			this.stop(true);
			this.set(this.to);
			this.fireEvent('onComplete', this.element, 10);
			this.callChain();
		}
	},

	set: function(to){
		this.now = to;
		this.increase();
		return this;
	},

	setNow: function(){
		this.now = this.compute(this.from, this.to);
	},

	compute: function(from, to){
		return (to - from) * this.delta + from;
	},

	start: function(from, to){
		if (!this.options.wait) this.stop();
		else if (this.timer) return this;
		this.from = from;
		this.to = to;
		this.change = this.to - this.from;
		this.time = $time();
		this.timer = this.step.periodical(Math.round(1000 / this.options.fps), this);
		this.fireEvent('onStart', this.element);
		return this;
	},

	stop: function(end){
		if (!this.timer) return this;
		this.timer = $clear(this.timer);
		if (!end) this.fireEvent('onCancel', this.element);
		return this;
	}

});

Fx.CSS = {

	select: function(property, to){
		if (property.test(/color/i)) return this.Color;
		var type = $type(to);
		if ((type == 'array') || (type == 'string' && to.contains(' '))) return this.Multi;
		return this.Single;
	},

	parse: function(el, property, fromTo){
		if (!fromTo.push) fromTo = [fromTo];
		var from = fromTo[0], to = fromTo[1];
		if (!$chk(to)){
			to = from;
			from = el.getStyle(property);
		}
		var css = this.select(property, to);
		return {'from': css.parse(from), 'to': css.parse(to), 'css': css};
	}

};

Fx.CSS.Single = {

	parse: function(value){
		return parseFloat(value);
	},

	getNow: function(from, to, fx){
		return fx.compute(from, to);
	},

	getValue: function(value, unit, property){
		if (unit == 'px' && property != 'opacity') value = Math.round(value);
		return value + unit;
	}

};

Fx.CSS.Multi = {

	parse: function(value){
		return value.push ? value : value.split(' ').map(function(v){
			return parseFloat(v);
		});
	},

	getNow: function(from, to, fx){
		var now = [];
		for (var i = 0; i < from.length; i++) now[i] = fx.compute(from[i], to[i]);
		return now;
	},

	getValue: function(value, unit, property){
		if (unit == 'px' && property != 'opacity') value = value.map(Math.round);
		return value.join(unit + ' ') + unit;
	}

};

Fx.CSS.Color = {

	parse: function(value){
		return value.push ? value : value.hexToRgb(true);
	},

	getNow: function(from, to, fx){
		var now = [];
		for (var i = 0; i < from.length; i++) now[i] = Math.round(fx.compute(from[i], to[i]));
		return now;
	},

	getValue: function(value){
		return 'rgb(' + value.join(',') + ')';
	}

};

Fx.Style = new Class({

	Extends: Fx.Base,

	initialize: function(element, property, options){
		this.parent($(element), options);
		this.property = property;
	},

	hide: function(){
		return this.set(0);
	},

	setNow: function(){
		this.now = this.css.getNow(this.from, this.to, this);
	},

	set: function(to){
		this.css = Fx.CSS.select(this.property, to);
		return this.parent(this.css.parse(to));
	},

	start: function(from, to){
		if (this.timer && this.options.wait) return this;
		var parsed = Fx.CSS.parse(this.element, this.property, [from, to]);
		this.css = parsed.css;
		return this.parent(parsed.from, parsed.to);
	},

	increase: function(){
		this.element.setStyle(this.property, this.css.getValue(this.now, this.options.unit, this.property));
	}

});

Element.extend({

	effect: function(property, options){
		return new Fx.Style(this, property, options);
	}

});

Fx.Styles = new Class({

	Extends: Fx.Base,

	initialize: function(element, options){
		this.parent($(element), options);
	},

	setNow: function(){
		for (var p in this.from) this.now[p] = this.css[p].getNow(this.from[p], this.to[p], this);
	},

	set: function(to){
		var parsed = {};
		this.css = {};
		for (var p in to){
			this.css[p] = Fx.CSS.select(p, to[p]);
			parsed[p] = this.css[p].parse(to[p]);
		}
		return this.parent(parsed);
	},

	start: function(obj){
		if (this.timer && this.options.wait) return this;
		this.now = {};
		this.css = {};
		var from = {}, to = {};
		for (var p in obj){
			var parsed = Fx.CSS.parse(this.element, p, obj[p]);
			from[p] = parsed.from;
			to[p] = parsed.to;
			this.css[p] = parsed.css;
		}
		return this.parent(from, to);
	},

	increase: function(){
		for (var p in this.now) this.element.setStyle(p, this.css[p].getValue(this.now[p], this.options.unit, p));
	}

});

Element.extend({

	effects: function(options){
		return new Fx.Styles(this, options);
	}

});

var XHR = new Class({

	Implements: [Class.Chain, Class.Events, Class.Options],

	options: {
		method: 'post',
		async: true,
		data: null,
		onRequest: $empty,
		onSuccess: $empty,
		onFailure: $empty,
		onException: $empty,
		urlEncoded: true,
		encoding: 'utf-8',
		autoCancel: false,
		headers: {},
		isSuccess: null
	},

	setTransport: function(){
		this.transport = (window.XMLHttpRequest) ? new XMLHttpRequest() : (Client.Engine.ie ? new ActiveXObject('Microsoft.XMLHTTP') : false);
	},

	initialize: function(){
		var params = $A(arguments).associate({'url': 'string', 'options': 'object'});
		this.url = params.url;
		this.setTransport();
		this.setOptions(params.options);
		this.options.isSuccess = this.options.isSuccess || this.isSuccess;
		this.headers = $merge(this.options.headers);
		if (this.options.urlEncoded && this.options.method != 'get'){
			var encoding = (this.options.encoding) ? '; charset=' + this.options.encoding : '';
			this.setHeader('Content-type', 'application/x-www-form-urlencoded' + encoding);
		}
		this.setHeader('X-Requested-With', 'XMLHttpRequest');
	},

	onStateChange: function(){
		if (this.transport.readyState != 4 || !this.running) return;
		this.running = false;
		this.status = 0;
		$try(function(){
			this.status = this.transport.status;
		}, this);
		if (this.options.isSuccess.call(this, this.status)) this.onSuccess();
		else this.onFailure();
		this.transport.onreadystatechange = $empty;
	},

	isSuccess: function(){
		return ((this.status >= 200) && (this.status < 300));
	},

	onSuccess: function(){
		this.response = {
			text: this.transport.responseText,
			xml: this.transport.responseXML
		};
		this.fireEvent('onSuccess', [this.response.text, this.response.xml]);
		this.callChain();
	},

	onFailure: function(){
		this.fireEvent('onFailure', this.transport);
	},

	setHeader: function(name, value){
		this.headers[name] = value;
		return this;
	},

	getHeader: function(name){
		return $try(function(name){
			return this.getResponseHeader(name);
		}, this.transport, name) || null;
	},

	send: function(url, data){
		if (this.options.autoCancel) this.cancel();
		else if (this.running) return this;
		this.running = true;
		if (data && this.options.method == 'get'){
			url = url + (url.contains('?') ? '&' : '?') + data;
			data = null;
		}
		this.transport.open(this.options.method.toUpperCase(), url, this.options.async);
		this.transport.onreadystatechange = this.onStateChange.bind(this);
		if ((this.options.method == 'post') && this.transport.overrideMimeType) this.setHeader('Connection', 'close');
		for (var type in this.headers){
			try{
				this.transport.setRequestHeader(type, this.headers[type]);
			} catch(e){
				this.fireEvent('onException', [e, type, this.headers[type]]);
			}
		}
		this.fireEvent('onRequest');
		this.transport.send($pick(data, null));
		if (!this.options.async) this.onStateChange();
		return this;
	},

	request: function(data){
		return this.send(this.url, data || this.options.data);
	},

	cancel: function(){
		if (!this.running) return this;
		this.running = false;
		this.transport.abort();
		this.transport.onreadystatechange = $empty;
		this.setTransport();
		this.fireEvent('onCancel');
		return this;
	}

});