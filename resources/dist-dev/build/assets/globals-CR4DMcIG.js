import { n as __exportAll } from "./chunk-B-1-B7_t.js";
//#region node_modules/dompurify/dist/purify.es.mjs
/*! @license DOMPurify 3.4.11 | (c) Cure53 and other contributors | Released under the Apache license 2.0 and Mozilla Public License 2.0 | github.com/cure53/DOMPurify/blob/3.4.11/LICENSE */
function _arrayLikeToArray(r, a) {
	(null == a || a > r.length) && (a = r.length);
	for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
	return n;
}
function _arrayWithHoles(r) {
	if (Array.isArray(r)) return r;
}
function _iterableToArrayLimit(r, l) {
	var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
	if (null != t) {
		var e, n, i, u, a = [], f = true, o = false;
		try {
			if (i = (t = t.call(r)).next, 0 === l);
			else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0);
		} catch (r) {
			o = true, n = r;
		} finally {
			try {
				if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return;
			} finally {
				if (o) throw n;
			}
		}
		return a;
	}
}
function _nonIterableRest() {
	throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}
function _slicedToArray(r, e) {
	return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
}
function _unsupportedIterableToArray(r, a) {
	if (r) {
		if ("string" == typeof r) return _arrayLikeToArray(r, a);
		var t = {}.toString.call(r).slice(8, -1);
		return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
	}
}
var entries = Object.entries, setPrototypeOf = Object.setPrototypeOf, isFrozen = Object.isFrozen, getPrototypeOf = Object.getPrototypeOf, getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
var freeze = Object.freeze, seal = Object.seal, create = Object.create;
var _ref = typeof Reflect !== "undefined" && Reflect, apply = _ref.apply, construct = _ref.construct;
if (!freeze) freeze = function freeze(x) {
	return x;
};
if (!seal) seal = function seal(x) {
	return x;
};
if (!apply) apply = function apply(func, thisArg) {
	for (var _len = arguments.length, args = new Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) args[_key - 2] = arguments[_key];
	return func.apply(thisArg, args);
};
if (!construct) construct = function construct(Func) {
	for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) args[_key2 - 1] = arguments[_key2];
	return new Func(...args);
};
var arrayForEach = unapply(Array.prototype.forEach);
var arrayLastIndexOf = unapply(Array.prototype.lastIndexOf);
var arrayPop = unapply(Array.prototype.pop);
var arrayPush = unapply(Array.prototype.push);
var arraySplice = unapply(Array.prototype.splice);
var arrayIsArray = Array.isArray;
var stringToLowerCase = unapply(String.prototype.toLowerCase);
var stringToString = unapply(String.prototype.toString);
var stringMatch = unapply(String.prototype.match);
var stringReplace = unapply(String.prototype.replace);
var stringIndexOf = unapply(String.prototype.indexOf);
var stringTrim = unapply(String.prototype.trim);
var numberToString = unapply(Number.prototype.toString);
var booleanToString = unapply(Boolean.prototype.toString);
var bigintToString = typeof BigInt === "undefined" ? null : unapply(BigInt.prototype.toString);
var symbolToString = typeof Symbol === "undefined" ? null : unapply(Symbol.prototype.toString);
var objectHasOwnProperty = unapply(Object.prototype.hasOwnProperty);
var objectToString = unapply(Object.prototype.toString);
var regExpTest = unapply(RegExp.prototype.test);
var typeErrorCreate = unconstruct(TypeError);
/**
* Creates a new function that calls the given function with a specified thisArg and arguments.
*
* @param func - The function to be wrapped and called.
* @returns A new function that calls the given function with a specified thisArg and arguments.
*/
function unapply(func) {
	return function(thisArg) {
		if (thisArg instanceof RegExp) thisArg.lastIndex = 0;
		for (var _len3 = arguments.length, args = new Array(_len3 > 1 ? _len3 - 1 : 0), _key3 = 1; _key3 < _len3; _key3++) args[_key3 - 1] = arguments[_key3];
		return apply(func, thisArg, args);
	};
}
/**
* Creates a new function that constructs an instance of the given constructor function with the provided arguments.
*
* @param func - The constructor function to be wrapped and called.
* @returns A new function that constructs an instance of the given constructor function with the provided arguments.
*/
function unconstruct(Func) {
	return function() {
		for (var _len4 = arguments.length, args = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) args[_key4] = arguments[_key4];
		return construct(Func, args);
	};
}
/**
* Add properties to a lookup table
*
* @param set - The set to which elements will be added.
* @param array - The array containing elements to be added to the set.
* @param transformCaseFunc - An optional function to transform the case of each element before adding to the set.
* @returns The modified set with added elements.
*/
function addToSet(set, array) {
	let transformCaseFunc = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : stringToLowerCase;
	if (setPrototypeOf) setPrototypeOf(set, null);
	if (!arrayIsArray(array)) return set;
	let l = array.length;
	while (l--) {
		let element = array[l];
		if (typeof element === "string") {
			const lcElement = transformCaseFunc(element);
			if (lcElement !== element) {
				if (!isFrozen(array)) array[l] = lcElement;
				element = lcElement;
			}
		}
		set[element] = true;
	}
	return set;
}
/**
* Clean up an array to harden against CSPP
*
* @param array - The array to be cleaned.
* @returns The cleaned version of the array
*/
function cleanArray(array) {
	for (let index = 0; index < array.length; index++) if (!objectHasOwnProperty(array, index)) array[index] = null;
	return array;
}
/**
* Shallow clone an object
*
* @param object - The object to be cloned.
* @returns A new object that copies the original.
*/
function clone$1(object) {
	const newObject = create(null);
	for (const _ref2 of entries(object)) {
		var _ref3 = _slicedToArray(_ref2, 2);
		const property = _ref3[0];
		const value = _ref3[1];
		if (objectHasOwnProperty(object, property)) if (arrayIsArray(value)) newObject[property] = cleanArray(value);
		else if (value && typeof value === "object" && value.constructor === Object) newObject[property] = clone$1(value);
		else newObject[property] = value;
	}
	return newObject;
}
/**
* Convert non-node values into strings without depending on direct property access.
*
* @param value - The value to stringify.
* @returns A string representation of the provided value.
*/
function stringifyValue(value) {
	switch (typeof value) {
		case "string": return value;
		case "number": return numberToString(value);
		case "boolean": return booleanToString(value);
		case "bigint": return bigintToString ? bigintToString(value) : "0";
		case "symbol": return symbolToString ? symbolToString(value) : "Symbol()";
		case "undefined": return objectToString(value);
		case "function":
		case "object": {
			if (value === null) return objectToString(value);
			const valueAsRecord = value;
			const valueToString = lookupGetter(valueAsRecord, "toString");
			if (typeof valueToString === "function") {
				const stringified = valueToString(valueAsRecord);
				return typeof stringified === "string" ? stringified : objectToString(stringified);
			}
			return objectToString(value);
		}
		default: return objectToString(value);
	}
}
/**
* This method automatically checks if the prop is function or getter and behaves accordingly.
*
* @param object - The object to look up the getter function in its prototype chain.
* @param prop - The property name for which to find the getter function.
* @returns The getter function found in the prototype chain or a fallback function.
*/
function lookupGetter(object, prop) {
	while (object !== null) {
		const desc = getOwnPropertyDescriptor(object, prop);
		if (desc) {
			if (desc.get) return unapply(desc.get);
			if (typeof desc.value === "function") return unapply(desc.value);
		}
		object = getPrototypeOf(object);
	}
	function fallbackValue() {
		return null;
	}
	return fallbackValue;
}
function isRegex(value) {
	try {
		regExpTest(value, "");
		return true;
	} catch (_unused) {
		return false;
	}
}
var html$1 = freeze([
	"a",
	"abbr",
	"acronym",
	"address",
	"area",
	"article",
	"aside",
	"audio",
	"b",
	"bdi",
	"bdo",
	"big",
	"blink",
	"blockquote",
	"body",
	"br",
	"button",
	"canvas",
	"caption",
	"center",
	"cite",
	"code",
	"col",
	"colgroup",
	"content",
	"data",
	"datalist",
	"dd",
	"decorator",
	"del",
	"details",
	"dfn",
	"dialog",
	"dir",
	"div",
	"dl",
	"dt",
	"element",
	"em",
	"fieldset",
	"figcaption",
	"figure",
	"font",
	"footer",
	"form",
	"h1",
	"h2",
	"h3",
	"h4",
	"h5",
	"h6",
	"head",
	"header",
	"hgroup",
	"hr",
	"html",
	"i",
	"img",
	"input",
	"ins",
	"kbd",
	"label",
	"legend",
	"li",
	"main",
	"map",
	"mark",
	"marquee",
	"menu",
	"menuitem",
	"meter",
	"nav",
	"nobr",
	"ol",
	"optgroup",
	"option",
	"output",
	"p",
	"picture",
	"pre",
	"progress",
	"q",
	"rp",
	"rt",
	"ruby",
	"s",
	"samp",
	"search",
	"section",
	"select",
	"shadow",
	"slot",
	"small",
	"source",
	"spacer",
	"span",
	"strike",
	"strong",
	"style",
	"sub",
	"summary",
	"sup",
	"table",
	"tbody",
	"td",
	"template",
	"textarea",
	"tfoot",
	"th",
	"thead",
	"time",
	"tr",
	"track",
	"tt",
	"u",
	"ul",
	"var",
	"video",
	"wbr"
]);
var svg$1 = freeze([
	"svg",
	"a",
	"altglyph",
	"altglyphdef",
	"altglyphitem",
	"animatecolor",
	"animatemotion",
	"animatetransform",
	"circle",
	"clippath",
	"defs",
	"desc",
	"ellipse",
	"enterkeyhint",
	"exportparts",
	"filter",
	"font",
	"g",
	"glyph",
	"glyphref",
	"hkern",
	"image",
	"inputmode",
	"line",
	"lineargradient",
	"marker",
	"mask",
	"metadata",
	"mpath",
	"part",
	"path",
	"pattern",
	"polygon",
	"polyline",
	"radialgradient",
	"rect",
	"stop",
	"style",
	"switch",
	"symbol",
	"text",
	"textpath",
	"title",
	"tref",
	"tspan",
	"view",
	"vkern"
]);
var svgFilters = freeze([
	"feBlend",
	"feColorMatrix",
	"feComponentTransfer",
	"feComposite",
	"feConvolveMatrix",
	"feDiffuseLighting",
	"feDisplacementMap",
	"feDistantLight",
	"feDropShadow",
	"feFlood",
	"feFuncA",
	"feFuncB",
	"feFuncG",
	"feFuncR",
	"feGaussianBlur",
	"feImage",
	"feMerge",
	"feMergeNode",
	"feMorphology",
	"feOffset",
	"fePointLight",
	"feSpecularLighting",
	"feSpotLight",
	"feTile",
	"feTurbulence"
]);
var svgDisallowed = freeze([
	"animate",
	"color-profile",
	"cursor",
	"discard",
	"font-face",
	"font-face-format",
	"font-face-name",
	"font-face-src",
	"font-face-uri",
	"foreignobject",
	"hatch",
	"hatchpath",
	"mesh",
	"meshgradient",
	"meshpatch",
	"meshrow",
	"missing-glyph",
	"script",
	"set",
	"solidcolor",
	"unknown",
	"use"
]);
var mathMl$1 = freeze([
	"math",
	"menclose",
	"merror",
	"mfenced",
	"mfrac",
	"mglyph",
	"mi",
	"mlabeledtr",
	"mmultiscripts",
	"mn",
	"mo",
	"mover",
	"mpadded",
	"mphantom",
	"mroot",
	"mrow",
	"ms",
	"mspace",
	"msqrt",
	"mstyle",
	"msub",
	"msup",
	"msubsup",
	"mtable",
	"mtd",
	"mtext",
	"mtr",
	"munder",
	"munderover",
	"mprescripts"
]);
var mathMlDisallowed = freeze([
	"maction",
	"maligngroup",
	"malignmark",
	"mlongdiv",
	"mscarries",
	"mscarry",
	"msgroup",
	"mstack",
	"msline",
	"msrow",
	"semantics",
	"annotation",
	"annotation-xml",
	"mprescripts",
	"none"
]);
var text = freeze(["#text"]);
var html$2 = freeze([
	"accept",
	"action",
	"align",
	"alt",
	"autocapitalize",
	"autocomplete",
	"autopictureinpicture",
	"autoplay",
	"background",
	"bgcolor",
	"border",
	"capture",
	"cellpadding",
	"cellspacing",
	"checked",
	"cite",
	"class",
	"clear",
	"color",
	"cols",
	"colspan",
	"command",
	"commandfor",
	"controls",
	"controlslist",
	"coords",
	"crossorigin",
	"datetime",
	"decoding",
	"default",
	"dir",
	"disabled",
	"disablepictureinpicture",
	"disableremoteplayback",
	"download",
	"draggable",
	"enctype",
	"enterkeyhint",
	"exportparts",
	"face",
	"for",
	"headers",
	"height",
	"hidden",
	"high",
	"href",
	"hreflang",
	"id",
	"inert",
	"inputmode",
	"integrity",
	"ismap",
	"kind",
	"label",
	"lang",
	"list",
	"loading",
	"loop",
	"low",
	"max",
	"maxlength",
	"media",
	"method",
	"min",
	"minlength",
	"multiple",
	"muted",
	"name",
	"nonce",
	"noshade",
	"novalidate",
	"nowrap",
	"open",
	"optimum",
	"part",
	"pattern",
	"placeholder",
	"playsinline",
	"popover",
	"popovertarget",
	"popovertargetaction",
	"poster",
	"preload",
	"pubdate",
	"radiogroup",
	"readonly",
	"rel",
	"required",
	"rev",
	"reversed",
	"role",
	"rows",
	"rowspan",
	"spellcheck",
	"scope",
	"selected",
	"shape",
	"size",
	"sizes",
	"slot",
	"span",
	"srclang",
	"start",
	"src",
	"srcset",
	"step",
	"style",
	"summary",
	"tabindex",
	"title",
	"translate",
	"type",
	"usemap",
	"valign",
	"value",
	"width",
	"wrap",
	"xmlns"
]);
var svg = freeze([
	"accent-height",
	"accumulate",
	"additive",
	"alignment-baseline",
	"amplitude",
	"ascent",
	"attributename",
	"attributetype",
	"azimuth",
	"basefrequency",
	"baseline-shift",
	"begin",
	"bias",
	"by",
	"class",
	"clip",
	"clippathunits",
	"clip-path",
	"clip-rule",
	"color",
	"color-interpolation",
	"color-interpolation-filters",
	"color-profile",
	"color-rendering",
	"cx",
	"cy",
	"d",
	"dx",
	"dy",
	"diffuseconstant",
	"direction",
	"display",
	"divisor",
	"dur",
	"edgemode",
	"elevation",
	"end",
	"exponent",
	"fill",
	"fill-opacity",
	"fill-rule",
	"filter",
	"filterunits",
	"flood-color",
	"flood-opacity",
	"font-family",
	"font-size",
	"font-size-adjust",
	"font-stretch",
	"font-style",
	"font-variant",
	"font-weight",
	"fx",
	"fy",
	"g1",
	"g2",
	"glyph-name",
	"glyphref",
	"gradientunits",
	"gradienttransform",
	"height",
	"href",
	"id",
	"image-rendering",
	"in",
	"in2",
	"intercept",
	"k",
	"k1",
	"k2",
	"k3",
	"k4",
	"kerning",
	"keypoints",
	"keysplines",
	"keytimes",
	"lang",
	"lengthadjust",
	"letter-spacing",
	"kernelmatrix",
	"kernelunitlength",
	"lighting-color",
	"local",
	"marker-end",
	"marker-mid",
	"marker-start",
	"markerheight",
	"markerunits",
	"markerwidth",
	"maskcontentunits",
	"maskunits",
	"max",
	"mask",
	"mask-type",
	"media",
	"method",
	"mode",
	"min",
	"name",
	"numoctaves",
	"offset",
	"operator",
	"opacity",
	"order",
	"orient",
	"orientation",
	"origin",
	"overflow",
	"paint-order",
	"path",
	"pathlength",
	"patterncontentunits",
	"patterntransform",
	"patternunits",
	"points",
	"preservealpha",
	"preserveaspectratio",
	"primitiveunits",
	"r",
	"rx",
	"ry",
	"radius",
	"refx",
	"refy",
	"repeatcount",
	"repeatdur",
	"restart",
	"result",
	"rotate",
	"scale",
	"seed",
	"shape-rendering",
	"slope",
	"specularconstant",
	"specularexponent",
	"spreadmethod",
	"startoffset",
	"stddeviation",
	"stitchtiles",
	"stop-color",
	"stop-opacity",
	"stroke-dasharray",
	"stroke-dashoffset",
	"stroke-linecap",
	"stroke-linejoin",
	"stroke-miterlimit",
	"stroke-opacity",
	"stroke",
	"stroke-width",
	"style",
	"surfacescale",
	"systemlanguage",
	"tabindex",
	"tablevalues",
	"targetx",
	"targety",
	"transform",
	"transform-origin",
	"text-anchor",
	"text-decoration",
	"text-rendering",
	"textlength",
	"type",
	"u1",
	"u2",
	"unicode",
	"values",
	"viewbox",
	"visibility",
	"version",
	"vert-adv-y",
	"vert-origin-x",
	"vert-origin-y",
	"width",
	"word-spacing",
	"wrap",
	"writing-mode",
	"xchannelselector",
	"ychannelselector",
	"x",
	"x1",
	"x2",
	"xmlns",
	"y",
	"y1",
	"y2",
	"z",
	"zoomandpan"
]);
var mathMl = freeze([
	"accent",
	"accentunder",
	"align",
	"bevelled",
	"close",
	"columnalign",
	"columnlines",
	"columnspacing",
	"columnspan",
	"denomalign",
	"depth",
	"dir",
	"display",
	"displaystyle",
	"encoding",
	"fence",
	"frame",
	"height",
	"href",
	"id",
	"largeop",
	"length",
	"linethickness",
	"lquote",
	"lspace",
	"mathbackground",
	"mathcolor",
	"mathsize",
	"mathvariant",
	"maxsize",
	"minsize",
	"movablelimits",
	"notation",
	"numalign",
	"open",
	"rowalign",
	"rowlines",
	"rowspacing",
	"rowspan",
	"rspace",
	"rquote",
	"scriptlevel",
	"scriptminsize",
	"scriptsizemultiplier",
	"selection",
	"separator",
	"separators",
	"stretchy",
	"subscriptshift",
	"supscriptshift",
	"symmetric",
	"voffset",
	"width",
	"xmlns"
]);
var xml = freeze([
	"xlink:href",
	"xml:id",
	"xlink:title",
	"xml:space",
	"xmlns:xlink"
]);
var MUSTACHE_EXPR = seal(/{{[\w\W]*|^[\w\W]*}}/g);
var ERB_EXPR = seal(/<%[\w\W]*|^[\w\W]*%>/g);
var TMPLIT_EXPR = seal(/\${[\w\W]*/g);
var DATA_ATTR = seal(/^data-[\-\w.\u00B7-\uFFFF]+$/);
var ARIA_ATTR = seal(/^aria-[\-\w]+$/);
var IS_ALLOWED_URI = seal(/^(?:(?:(?:f|ht)tps?|mailto|tel|callto|sms|cid|xmpp|matrix):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i);
var IS_SCRIPT_OR_DATA = seal(/^(?:\w+script|data):/i);
var ATTR_WHITESPACE = seal(/[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205F\u3000]/g);
var DOCTYPE_NAME = seal(/^html$/i);
var CUSTOM_ELEMENT = seal(/^[a-z][.\w]*(-[.\w]+)+$/i);
var ELEMENT_MARKUP_PROBE = seal(/<[/\w!]/g);
var COMMENT_MARKUP_PROBE = seal(/<[/\w]/g);
var FALLBACK_TAG_CLOSE = seal(/<\/no(script|embed|frames)/i);
var SELF_CLOSING_TAG = seal(/\/>/i);
var NODE_TYPE = {
	element: 1,
	attribute: 2,
	text: 3,
	cdataSection: 4,
	entityReference: 5,
	entityNode: 6,
	processingInstruction: 7,
	comment: 8,
	document: 9,
	documentType: 10,
	documentFragment: 11,
	notation: 12
};
var getGlobal = function getGlobal() {
	return typeof window === "undefined" ? null : window;
};
/**
* Creates a no-op policy for internal use only.
* Don't export this function outside this module!
* @param trustedTypes The policy factory.
* @param purifyHostElement The Script element used to load DOMPurify (to determine policy name suffix).
* @return The policy created (or null, if Trusted Types
* are not supported or creating the policy failed).
*/
var _createTrustedTypesPolicy = function _createTrustedTypesPolicy(trustedTypes, purifyHostElement) {
	if (typeof trustedTypes !== "object" || typeof trustedTypes.createPolicy !== "function") return null;
	let suffix = null;
	const ATTR_NAME = "data-tt-policy-suffix";
	if (purifyHostElement && purifyHostElement.hasAttribute(ATTR_NAME)) suffix = purifyHostElement.getAttribute(ATTR_NAME);
	const policyName = "dompurify" + (suffix ? "#" + suffix : "");
	try {
		return trustedTypes.createPolicy(policyName, {
			createHTML(html) {
				return html;
			},
			createScriptURL(scriptUrl) {
				return scriptUrl;
			}
		});
	} catch (_) {
		console.warn("TrustedTypes policy " + policyName + " could not be created.");
		return null;
	}
};
var _createHooksMap = function _createHooksMap() {
	return {
		afterSanitizeAttributes: [],
		afterSanitizeElements: [],
		afterSanitizeShadowDOM: [],
		beforeSanitizeAttributes: [],
		beforeSanitizeElements: [],
		beforeSanitizeShadowDOM: [],
		uponSanitizeAttribute: [],
		uponSanitizeElement: [],
		uponSanitizeShadowNode: []
	};
};
/**
* Resolve a set-valued configuration option: a fresh set built from
* cfg[key] when it is an own array property (seeded with a clone of
* options.base when given, case-normalized via options.transform),
* the fallback set otherwise.
*
* @param cfg the cloned, prototype-free configuration object
* @param key the configuration property to read
* @param fallback the set to use when the option is absent or not an array
* @param options transform and optional base set to merge into
* @returns the resolved set
*/
var _resolveSetOption = function _resolveSetOption(cfg, key, fallback, options) {
	return objectHasOwnProperty(cfg, key) && arrayIsArray(cfg[key]) ? addToSet(options.base ? clone$1(options.base) : {}, cfg[key], options.transform) : fallback;
};
function createDOMPurify() {
	let window = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : getGlobal();
	const DOMPurify = (root) => createDOMPurify(root);
	DOMPurify.version = "3.4.11";
	DOMPurify.removed = [];
	if (!window || !window.document || window.document.nodeType !== NODE_TYPE.document || !window.Element) {
		DOMPurify.isSupported = false;
		return DOMPurify;
	}
	let document = window.document;
	const originalDocument = document;
	const currentScript = originalDocument.currentScript;
	window.DocumentFragment;
	const HTMLTemplateElement = window.HTMLTemplateElement, Node = window.Node, Element = window.Element, NodeFilter = window.NodeFilter;
	window.NamedNodeMap === void 0 && (window.NamedNodeMap || window.MozNamedAttrMap);
	window.HTMLFormElement;
	const DOMParser = window.DOMParser, trustedTypes = window.trustedTypes;
	const ElementPrototype = Element.prototype;
	const cloneNode = lookupGetter(ElementPrototype, "cloneNode");
	const remove = lookupGetter(ElementPrototype, "remove");
	const getNextSibling = lookupGetter(ElementPrototype, "nextSibling");
	const getChildNodes = lookupGetter(ElementPrototype, "childNodes");
	const getParentNode = lookupGetter(ElementPrototype, "parentNode");
	const getShadowRoot = lookupGetter(ElementPrototype, "shadowRoot");
	const getAttributes = lookupGetter(ElementPrototype, "attributes");
	const getNodeType = Node && Node.prototype ? lookupGetter(Node.prototype, "nodeType") : null;
	const getNodeName = Node && Node.prototype ? lookupGetter(Node.prototype, "nodeName") : null;
	if (typeof HTMLTemplateElement === "function") {
		const template = document.createElement("template");
		if (template.content && template.content.ownerDocument) document = template.content.ownerDocument;
	}
	let trustedTypesPolicy;
	let emptyHTML = "";
	let defaultTrustedTypesPolicy;
	let defaultTrustedTypesPolicyResolved = false;
	let IN_TRUSTED_TYPES_POLICY = 0;
	const _assertNotInTrustedTypesPolicy = function _assertNotInTrustedTypesPolicy() {
		if (IN_TRUSTED_TYPES_POLICY > 0) throw typeErrorCreate("A configured TRUSTED_TYPES_POLICY callback (createHTML or createScriptURL) must not call DOMPurify.sanitize, as that causes infinite recursion. Do not pass a policy whose callbacks wrap DOMPurify as TRUSTED_TYPES_POLICY; see the \"DOMPurify and Trusted Types\" section of the README.");
	};
	const _createTrustedHTML = function _createTrustedHTML(html) {
		_assertNotInTrustedTypesPolicy();
		IN_TRUSTED_TYPES_POLICY++;
		try {
			return trustedTypesPolicy.createHTML(html);
		} finally {
			IN_TRUSTED_TYPES_POLICY--;
		}
	};
	const _createTrustedScriptURL = function _createTrustedScriptURL(scriptUrl) {
		_assertNotInTrustedTypesPolicy();
		IN_TRUSTED_TYPES_POLICY++;
		try {
			return trustedTypesPolicy.createScriptURL(scriptUrl);
		} finally {
			IN_TRUSTED_TYPES_POLICY--;
		}
	};
	const _getDefaultTrustedTypesPolicy = function _getDefaultTrustedTypesPolicy() {
		if (!defaultTrustedTypesPolicyResolved) {
			defaultTrustedTypesPolicy = _createTrustedTypesPolicy(trustedTypes, currentScript);
			defaultTrustedTypesPolicyResolved = true;
		}
		return defaultTrustedTypesPolicy;
	};
	const _document = document, implementation = _document.implementation, createNodeIterator = _document.createNodeIterator, createDocumentFragment = _document.createDocumentFragment, getElementsByTagName = _document.getElementsByTagName;
	const importNode = originalDocument.importNode;
	let hooks = _createHooksMap();
	/**
	* Expose whether this browser supports running the full DOMPurify.
	*/
	DOMPurify.isSupported = typeof entries === "function" && typeof getParentNode === "function" && implementation && implementation.createHTMLDocument !== void 0;
	const MUSTACHE_EXPR$1 = MUSTACHE_EXPR, ERB_EXPR$1 = ERB_EXPR, TMPLIT_EXPR$1 = TMPLIT_EXPR, DATA_ATTR$1 = DATA_ATTR, ARIA_ATTR$1 = ARIA_ATTR, IS_SCRIPT_OR_DATA$1 = IS_SCRIPT_OR_DATA, ATTR_WHITESPACE$1 = ATTR_WHITESPACE, CUSTOM_ELEMENT$1 = CUSTOM_ELEMENT;
	let IS_ALLOWED_URI$1 = IS_ALLOWED_URI;
	/**
	* We consider the elements and attributes below to be safe. Ideally
	* don't add any new ones but feel free to remove unwanted ones.
	*/
	let ALLOWED_TAGS = null;
	const DEFAULT_ALLOWED_TAGS = addToSet({}, [
		...html$1,
		...svg$1,
		...svgFilters,
		...mathMl$1,
		...text
	]);
	let ALLOWED_ATTR = null;
	const DEFAULT_ALLOWED_ATTR = addToSet({}, [
		...html$2,
		...svg,
		...mathMl,
		...xml
	]);
	let CUSTOM_ELEMENT_HANDLING = Object.seal(create(null, {
		tagNameCheck: {
			writable: true,
			configurable: false,
			enumerable: true,
			value: null
		},
		attributeNameCheck: {
			writable: true,
			configurable: false,
			enumerable: true,
			value: null
		},
		allowCustomizedBuiltInElements: {
			writable: true,
			configurable: false,
			enumerable: true,
			value: false
		}
	}));
	let FORBID_TAGS = null;
	let FORBID_ATTR = null;
	const EXTRA_ELEMENT_HANDLING = Object.seal(create(null, {
		tagCheck: {
			writable: true,
			configurable: false,
			enumerable: true,
			value: null
		},
		attributeCheck: {
			writable: true,
			configurable: false,
			enumerable: true,
			value: null
		}
	}));
	let ALLOW_ARIA_ATTR = true;
	let ALLOW_DATA_ATTR = true;
	let ALLOW_UNKNOWN_PROTOCOLS = false;
	let ALLOW_SELF_CLOSE_IN_ATTR = true;
	let SAFE_FOR_TEMPLATES = false;
	let SAFE_FOR_XML = true;
	let WHOLE_DOCUMENT = false;
	let SET_CONFIG = false;
	let SET_CONFIG_ALLOWED_TAGS = null;
	let SET_CONFIG_ALLOWED_ATTR = null;
	let FORCE_BODY = false;
	let RETURN_DOM = false;
	let RETURN_DOM_FRAGMENT = false;
	let RETURN_TRUSTED_TYPE = false;
	let SANITIZE_DOM = true;
	let SANITIZE_NAMED_PROPS = false;
	const SANITIZE_NAMED_PROPS_PREFIX = "user-content-";
	let KEEP_CONTENT = true;
	let IN_PLACE = false;
	let USE_PROFILES = {};
	let FORBID_CONTENTS = null;
	const DEFAULT_FORBID_CONTENTS = addToSet({}, [
		"annotation-xml",
		"audio",
		"colgroup",
		"desc",
		"foreignobject",
		"head",
		"iframe",
		"math",
		"mi",
		"mn",
		"mo",
		"ms",
		"mtext",
		"noembed",
		"noframes",
		"noscript",
		"plaintext",
		"script",
		"selectedcontent",
		"style",
		"svg",
		"template",
		"thead",
		"title",
		"video",
		"xmp"
	]);
	let DATA_URI_TAGS = null;
	const DEFAULT_DATA_URI_TAGS = addToSet({}, [
		"audio",
		"video",
		"img",
		"source",
		"image",
		"track"
	]);
	let URI_SAFE_ATTRIBUTES = null;
	const DEFAULT_URI_SAFE_ATTRIBUTES = addToSet({}, [
		"alt",
		"class",
		"for",
		"id",
		"label",
		"name",
		"pattern",
		"placeholder",
		"role",
		"summary",
		"title",
		"value",
		"style",
		"xmlns"
	]);
	const MATHML_NAMESPACE = "http://www.w3.org/1998/Math/MathML";
	const SVG_NAMESPACE = "http://www.w3.org/2000/svg";
	const HTML_NAMESPACE = "http://www.w3.org/1999/xhtml";
	let NAMESPACE = HTML_NAMESPACE;
	let IS_EMPTY_INPUT = false;
	let ALLOWED_NAMESPACES = null;
	const DEFAULT_ALLOWED_NAMESPACES = addToSet({}, [
		MATHML_NAMESPACE,
		SVG_NAMESPACE,
		HTML_NAMESPACE
	], stringToString);
	const DEFAULT_MATHML_TEXT_INTEGRATION_POINTS = freeze([
		"mi",
		"mo",
		"mn",
		"ms",
		"mtext"
	]);
	let MATHML_TEXT_INTEGRATION_POINTS = addToSet({}, DEFAULT_MATHML_TEXT_INTEGRATION_POINTS);
	const DEFAULT_HTML_INTEGRATION_POINTS = freeze(["annotation-xml"]);
	let HTML_INTEGRATION_POINTS = addToSet({}, DEFAULT_HTML_INTEGRATION_POINTS);
	const COMMON_SVG_AND_HTML_ELEMENTS = addToSet({}, [
		"title",
		"style",
		"font",
		"a",
		"script"
	]);
	let PARSER_MEDIA_TYPE = null;
	const SUPPORTED_PARSER_MEDIA_TYPES = ["application/xhtml+xml", "text/html"];
	const DEFAULT_PARSER_MEDIA_TYPE = "text/html";
	let transformCaseFunc = null;
	let CONFIG = null;
	const formElement = document.createElement("form");
	const isRegexOrFunction = function isRegexOrFunction(testValue) {
		return testValue instanceof RegExp || testValue instanceof Function;
	};
	/**
	* _parseConfig
	*
	* @param cfg optional config literal
	*/
	const _parseConfig = function _parseConfig() {
		let cfg = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
		if (CONFIG && CONFIG === cfg) return;
		if (!cfg || typeof cfg !== "object") cfg = {};
		cfg = clone$1(cfg);
		PARSER_MEDIA_TYPE = SUPPORTED_PARSER_MEDIA_TYPES.indexOf(cfg.PARSER_MEDIA_TYPE) === -1 ? DEFAULT_PARSER_MEDIA_TYPE : cfg.PARSER_MEDIA_TYPE;
		transformCaseFunc = PARSER_MEDIA_TYPE === "application/xhtml+xml" ? stringToString : stringToLowerCase;
		ALLOWED_TAGS = _resolveSetOption(cfg, "ALLOWED_TAGS", DEFAULT_ALLOWED_TAGS, { transform: transformCaseFunc });
		ALLOWED_ATTR = _resolveSetOption(cfg, "ALLOWED_ATTR", DEFAULT_ALLOWED_ATTR, { transform: transformCaseFunc });
		ALLOWED_NAMESPACES = _resolveSetOption(cfg, "ALLOWED_NAMESPACES", DEFAULT_ALLOWED_NAMESPACES, { transform: stringToString });
		URI_SAFE_ATTRIBUTES = _resolveSetOption(cfg, "ADD_URI_SAFE_ATTR", DEFAULT_URI_SAFE_ATTRIBUTES, {
			transform: transformCaseFunc,
			base: DEFAULT_URI_SAFE_ATTRIBUTES
		});
		DATA_URI_TAGS = _resolveSetOption(cfg, "ADD_DATA_URI_TAGS", DEFAULT_DATA_URI_TAGS, {
			transform: transformCaseFunc,
			base: DEFAULT_DATA_URI_TAGS
		});
		FORBID_CONTENTS = _resolveSetOption(cfg, "FORBID_CONTENTS", DEFAULT_FORBID_CONTENTS, { transform: transformCaseFunc });
		FORBID_TAGS = _resolveSetOption(cfg, "FORBID_TAGS", clone$1({}), { transform: transformCaseFunc });
		FORBID_ATTR = _resolveSetOption(cfg, "FORBID_ATTR", clone$1({}), { transform: transformCaseFunc });
		USE_PROFILES = objectHasOwnProperty(cfg, "USE_PROFILES") ? cfg.USE_PROFILES && typeof cfg.USE_PROFILES === "object" ? clone$1(cfg.USE_PROFILES) : cfg.USE_PROFILES : false;
		ALLOW_ARIA_ATTR = cfg.ALLOW_ARIA_ATTR !== false;
		ALLOW_DATA_ATTR = cfg.ALLOW_DATA_ATTR !== false;
		ALLOW_UNKNOWN_PROTOCOLS = cfg.ALLOW_UNKNOWN_PROTOCOLS || false;
		ALLOW_SELF_CLOSE_IN_ATTR = cfg.ALLOW_SELF_CLOSE_IN_ATTR !== false;
		SAFE_FOR_TEMPLATES = cfg.SAFE_FOR_TEMPLATES || false;
		SAFE_FOR_XML = cfg.SAFE_FOR_XML !== false;
		WHOLE_DOCUMENT = cfg.WHOLE_DOCUMENT || false;
		RETURN_DOM = cfg.RETURN_DOM || false;
		RETURN_DOM_FRAGMENT = cfg.RETURN_DOM_FRAGMENT || false;
		RETURN_TRUSTED_TYPE = cfg.RETURN_TRUSTED_TYPE || false;
		FORCE_BODY = cfg.FORCE_BODY || false;
		SANITIZE_DOM = cfg.SANITIZE_DOM !== false;
		SANITIZE_NAMED_PROPS = cfg.SANITIZE_NAMED_PROPS || false;
		KEEP_CONTENT = cfg.KEEP_CONTENT !== false;
		IN_PLACE = cfg.IN_PLACE || false;
		IS_ALLOWED_URI$1 = isRegex(cfg.ALLOWED_URI_REGEXP) ? cfg.ALLOWED_URI_REGEXP : IS_ALLOWED_URI;
		NAMESPACE = typeof cfg.NAMESPACE === "string" ? cfg.NAMESPACE : HTML_NAMESPACE;
		MATHML_TEXT_INTEGRATION_POINTS = objectHasOwnProperty(cfg, "MATHML_TEXT_INTEGRATION_POINTS") && cfg.MATHML_TEXT_INTEGRATION_POINTS && typeof cfg.MATHML_TEXT_INTEGRATION_POINTS === "object" ? clone$1(cfg.MATHML_TEXT_INTEGRATION_POINTS) : addToSet({}, DEFAULT_MATHML_TEXT_INTEGRATION_POINTS);
		HTML_INTEGRATION_POINTS = objectHasOwnProperty(cfg, "HTML_INTEGRATION_POINTS") && cfg.HTML_INTEGRATION_POINTS && typeof cfg.HTML_INTEGRATION_POINTS === "object" ? clone$1(cfg.HTML_INTEGRATION_POINTS) : addToSet({}, DEFAULT_HTML_INTEGRATION_POINTS);
		const customElementHandling = objectHasOwnProperty(cfg, "CUSTOM_ELEMENT_HANDLING") && cfg.CUSTOM_ELEMENT_HANDLING && typeof cfg.CUSTOM_ELEMENT_HANDLING === "object" ? clone$1(cfg.CUSTOM_ELEMENT_HANDLING) : create(null);
		CUSTOM_ELEMENT_HANDLING = create(null);
		if (objectHasOwnProperty(customElementHandling, "tagNameCheck") && isRegexOrFunction(customElementHandling.tagNameCheck)) CUSTOM_ELEMENT_HANDLING.tagNameCheck = customElementHandling.tagNameCheck;
		if (objectHasOwnProperty(customElementHandling, "attributeNameCheck") && isRegexOrFunction(customElementHandling.attributeNameCheck)) CUSTOM_ELEMENT_HANDLING.attributeNameCheck = customElementHandling.attributeNameCheck;
		if (objectHasOwnProperty(customElementHandling, "allowCustomizedBuiltInElements") && typeof customElementHandling.allowCustomizedBuiltInElements === "boolean") CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements = customElementHandling.allowCustomizedBuiltInElements;
		seal(CUSTOM_ELEMENT_HANDLING);
		if (SAFE_FOR_TEMPLATES) ALLOW_DATA_ATTR = false;
		if (RETURN_DOM_FRAGMENT) RETURN_DOM = true;
		if (USE_PROFILES) {
			ALLOWED_TAGS = addToSet({}, text);
			ALLOWED_ATTR = create(null);
			if (USE_PROFILES.html === true) {
				addToSet(ALLOWED_TAGS, html$1);
				addToSet(ALLOWED_ATTR, html$2);
			}
			if (USE_PROFILES.svg === true) {
				addToSet(ALLOWED_TAGS, svg$1);
				addToSet(ALLOWED_ATTR, svg);
				addToSet(ALLOWED_ATTR, xml);
			}
			if (USE_PROFILES.svgFilters === true) {
				addToSet(ALLOWED_TAGS, svgFilters);
				addToSet(ALLOWED_ATTR, svg);
				addToSet(ALLOWED_ATTR, xml);
			}
			if (USE_PROFILES.mathMl === true) {
				addToSet(ALLOWED_TAGS, mathMl$1);
				addToSet(ALLOWED_ATTR, mathMl);
				addToSet(ALLOWED_ATTR, xml);
			}
		}
		EXTRA_ELEMENT_HANDLING.tagCheck = null;
		EXTRA_ELEMENT_HANDLING.attributeCheck = null;
		if (objectHasOwnProperty(cfg, "ADD_TAGS")) {
			if (typeof cfg.ADD_TAGS === "function") EXTRA_ELEMENT_HANDLING.tagCheck = cfg.ADD_TAGS;
			else if (arrayIsArray(cfg.ADD_TAGS)) {
				if (ALLOWED_TAGS === DEFAULT_ALLOWED_TAGS) ALLOWED_TAGS = clone$1(ALLOWED_TAGS);
				addToSet(ALLOWED_TAGS, cfg.ADD_TAGS, transformCaseFunc);
			}
		}
		if (objectHasOwnProperty(cfg, "ADD_ATTR")) {
			if (typeof cfg.ADD_ATTR === "function") EXTRA_ELEMENT_HANDLING.attributeCheck = cfg.ADD_ATTR;
			else if (arrayIsArray(cfg.ADD_ATTR)) {
				if (ALLOWED_ATTR === DEFAULT_ALLOWED_ATTR) ALLOWED_ATTR = clone$1(ALLOWED_ATTR);
				addToSet(ALLOWED_ATTR, cfg.ADD_ATTR, transformCaseFunc);
			}
		}
		if (objectHasOwnProperty(cfg, "ADD_URI_SAFE_ATTR") && arrayIsArray(cfg.ADD_URI_SAFE_ATTR)) addToSet(URI_SAFE_ATTRIBUTES, cfg.ADD_URI_SAFE_ATTR, transformCaseFunc);
		if (objectHasOwnProperty(cfg, "FORBID_CONTENTS") && arrayIsArray(cfg.FORBID_CONTENTS)) {
			if (FORBID_CONTENTS === DEFAULT_FORBID_CONTENTS) FORBID_CONTENTS = clone$1(FORBID_CONTENTS);
			addToSet(FORBID_CONTENTS, cfg.FORBID_CONTENTS, transformCaseFunc);
		}
		if (objectHasOwnProperty(cfg, "ADD_FORBID_CONTENTS") && arrayIsArray(cfg.ADD_FORBID_CONTENTS)) {
			if (FORBID_CONTENTS === DEFAULT_FORBID_CONTENTS) FORBID_CONTENTS = clone$1(FORBID_CONTENTS);
			addToSet(FORBID_CONTENTS, cfg.ADD_FORBID_CONTENTS, transformCaseFunc);
		}
		if (KEEP_CONTENT) ALLOWED_TAGS["#text"] = true;
		if (WHOLE_DOCUMENT) addToSet(ALLOWED_TAGS, [
			"html",
			"head",
			"body"
		]);
		if (ALLOWED_TAGS.table) {
			addToSet(ALLOWED_TAGS, ["tbody"]);
			delete FORBID_TAGS.tbody;
		}
		if (cfg.TRUSTED_TYPES_POLICY) {
			if (typeof cfg.TRUSTED_TYPES_POLICY.createHTML !== "function") throw typeErrorCreate("TRUSTED_TYPES_POLICY configuration option must provide a \"createHTML\" hook.");
			if (typeof cfg.TRUSTED_TYPES_POLICY.createScriptURL !== "function") throw typeErrorCreate("TRUSTED_TYPES_POLICY configuration option must provide a \"createScriptURL\" hook.");
			const previousTrustedTypesPolicy = trustedTypesPolicy;
			trustedTypesPolicy = cfg.TRUSTED_TYPES_POLICY;
			try {
				emptyHTML = _createTrustedHTML("");
			} catch (error) {
				trustedTypesPolicy = previousTrustedTypesPolicy;
				throw error;
			}
		} else if (cfg.TRUSTED_TYPES_POLICY === null) {
			trustedTypesPolicy = void 0;
			emptyHTML = "";
		} else {
			if (trustedTypesPolicy === void 0) trustedTypesPolicy = _getDefaultTrustedTypesPolicy();
			if (trustedTypesPolicy && typeof emptyHTML === "string") emptyHTML = _createTrustedHTML("");
		}
		if (freeze) freeze(cfg);
		CONFIG = cfg;
	};
	const ALL_SVG_TAGS = addToSet({}, [
		...svg$1,
		...svgFilters,
		...svgDisallowed
	]);
	const ALL_MATHML_TAGS = addToSet({}, [...mathMl$1, ...mathMlDisallowed]);
	/**
	* Namespace rules for an element in the SVG namespace.
	*
	* @param tagName the element's lowercase tag name
	* @param parent the (possibly simulated) parent node
	* @param parentTagName the parent's lowercase tag name
	* @returns true if a spec-compliant parser could produce this element
	*/
	const _checkSvgNamespace = function _checkSvgNamespace(tagName, parent, parentTagName) {
		if (parent.namespaceURI === HTML_NAMESPACE) return tagName === "svg";
		if (parent.namespaceURI === MATHML_NAMESPACE) return tagName === "svg" && (parentTagName === "annotation-xml" || MATHML_TEXT_INTEGRATION_POINTS[parentTagName]);
		return Boolean(ALL_SVG_TAGS[tagName]);
	};
	/**
	* Namespace rules for an element in the MathML namespace.
	*
	* @param tagName the element's lowercase tag name
	* @param parent the (possibly simulated) parent node
	* @param parentTagName the parent's lowercase tag name
	* @returns true if a spec-compliant parser could produce this element
	*/
	const _checkMathMlNamespace = function _checkMathMlNamespace(tagName, parent, parentTagName) {
		if (parent.namespaceURI === HTML_NAMESPACE) return tagName === "math";
		if (parent.namespaceURI === SVG_NAMESPACE) return tagName === "math" && HTML_INTEGRATION_POINTS[parentTagName];
		return Boolean(ALL_MATHML_TAGS[tagName]);
	};
	/**
	* Namespace rules for an element in the HTML namespace.
	*
	* @param tagName the element's lowercase tag name
	* @param parent the (possibly simulated) parent node
	* @param parentTagName the parent's lowercase tag name
	* @returns true if a spec-compliant parser could produce this element
	*/
	const _checkHtmlNamespace = function _checkHtmlNamespace(tagName, parent, parentTagName) {
		if (parent.namespaceURI === SVG_NAMESPACE && !HTML_INTEGRATION_POINTS[parentTagName]) return false;
		if (parent.namespaceURI === MATHML_NAMESPACE && !MATHML_TEXT_INTEGRATION_POINTS[parentTagName]) return false;
		return !ALL_MATHML_TAGS[tagName] && (COMMON_SVG_AND_HTML_ELEMENTS[tagName] || !ALL_SVG_TAGS[tagName]);
	};
	/**
	* @param element a DOM element whose namespace is being checked
	* @returns Return false if the element has a
	*  namespace that a spec-compliant parser would never
	*  return. Return true otherwise.
	*/
	const _checkValidNamespace = function _checkValidNamespace(element) {
		let parent = getParentNode(element);
		if (!parent || !parent.tagName) parent = {
			namespaceURI: NAMESPACE,
			tagName: "template"
		};
		const tagName = stringToLowerCase(element.tagName);
		const parentTagName = stringToLowerCase(parent.tagName);
		if (!ALLOWED_NAMESPACES[element.namespaceURI]) return false;
		if (element.namespaceURI === SVG_NAMESPACE) return _checkSvgNamespace(tagName, parent, parentTagName);
		if (element.namespaceURI === MATHML_NAMESPACE) return _checkMathMlNamespace(tagName, parent, parentTagName);
		if (element.namespaceURI === HTML_NAMESPACE) return _checkHtmlNamespace(tagName, parent, parentTagName);
		if (PARSER_MEDIA_TYPE === "application/xhtml+xml" && ALLOWED_NAMESPACES[element.namespaceURI]) return true;
		return false;
	};
	/**
	* _forceRemove
	*
	* @param node a DOM node
	*/
	const _forceRemove = function _forceRemove(node) {
		arrayPush(DOMPurify.removed, { element: node });
		try {
			getParentNode(node).removeChild(node);
		} catch (_) {
			remove(node);
			if (!getParentNode(node)) throw typeErrorCreate("a node selected for removal could not be detached from its tree and cannot be safely returned; refusing to sanitize in place");
		}
	};
	/**
	* _neutralizeRoot
	*
	* Fail-closed teardown of an in-place root after the sanitize walk aborts
	* (campaign-3 F2). An internal throw mid-walk — e.g. a page-registered
	* custom element's reaction detaches a node so `_forceRemove`'s deliberate
	* parentless guard throws, or any other re-entrant engine mutation — would
	* otherwise leave the caller's *live* tree half-sanitized, with everything
	* after the abort point still carrying its handlers. There is no safe way
	* to resume the walk (the tree mutated under us), so we strip the root bare:
	* remove every child and every attribute, then let the caller's catch see
	* the original error. Clobber-safe (cached `remove`/`childNodes`/`attributes`
	* getters; the root was already clobber-pre-flighted at the IN_PLACE entry).
	*
	* @param root the in-place root to empty
	*/
	const _neutralizeRoot = function _neutralizeRoot(root) {
		const childNodes = getChildNodes(root);
		if (childNodes) {
			const snapshot = [];
			arrayForEach(childNodes, (child) => {
				arrayPush(snapshot, child);
			});
			arrayForEach(snapshot, (child) => {
				try {
					remove(child);
				} catch (_) {}
			});
		}
		const attributes = getAttributes(root);
		if (attributes) for (let i = attributes.length - 1; i >= 0; --i) {
			const attribute = attributes[i];
			const name = attribute && attribute.name;
			if (typeof name === "string") try {
				root.removeAttribute(name);
			} catch (_) {}
		}
	};
	/**
	* _removeAttribute
	*
	* @param name an Attribute name
	* @param element a DOM node
	*/
	const _removeAttribute = function _removeAttribute(name, element) {
		try {
			arrayPush(DOMPurify.removed, {
				attribute: element.getAttributeNode(name),
				from: element
			});
		} catch (_) {
			arrayPush(DOMPurify.removed, {
				attribute: null,
				from: element
			});
		}
		element.removeAttribute(name);
		if (name === "is") if (RETURN_DOM || RETURN_DOM_FRAGMENT) try {
			_forceRemove(element);
		} catch (_) {}
		else try {
			element.setAttribute(name, "");
		} catch (_) {}
	};
	/**
	* _stripDisallowedAttributes
	*
	* Removes every attribute the active configuration does not allow from a
	* single element, using the same allowlist as the main attribute pass (so
	* `on*` handlers go, but no `/^on/` blocklist is introduced). Used only to
	* neutralise nodes that are being discarded from an in-place tree.
	*
	* @param element the element to strip
	*/
	const _stripDisallowedAttributes = function _stripDisallowedAttributes(element) {
		const attributes = getAttributes(element);
		if (!attributes) return;
		for (let i = attributes.length - 1; i >= 0; --i) {
			const attribute = attributes[i];
			const name = attribute && attribute.name;
			if (typeof name !== "string" || ALLOWED_ATTR[transformCaseFunc(name)]) continue;
			try {
				element.removeAttribute(name);
			} catch (_) {}
		}
	};
	/**
	* _neutralizeSubtree
	*
	* Completes the audit-5 F1 fix across every removal path. The KEEP_CONTENT
	* move-hoist neutralises only disallowed-tag removals; clobber, mXSS-canary,
	* namespace, comment, processing-instruction and KEEP_CONTENT:false removals
	* all drop their subtree wholesale via `_forceRemove`. On the IN_PLACE path
	* those dropped nodes are detached from the caller's LIVE tree but a
	* handler-bearing original among them (an `<img onerror>`/`<video>` that was
	* loading) keeps its queued resource event, which fires in page scope after
	* sanitize returns. This walks a removed subtree and strips every attribute
	* the active configuration does not allow — so `on*` handlers are cancelled
	* through the SAME allowlist that governs kept nodes, not a separate `/^on/`
	* blocklist. Run synchronously before sanitize returns, i.e. before any
	* queued event can fire. Hook-free by design: these nodes leave the output,
	* so firing attribute hooks for them would be surprising. Clobber-safe reads;
	* a doomed clobbered node may shadow `removeAttribute` (its own attributes are
	* irrelevant — it is discarded — while its non-clobbered descendants, e.g.
	* the `<img>`, are reached and scrubbed).
	*
	* @param root the root of a removed subtree to neutralise
	*/
	const _neutralizeSubtree = function _neutralizeSubtree(root) {
		const stack = [root];
		while (stack.length > 0) {
			const node = stack.pop();
			if ((getNodeType ? getNodeType(node) : node.nodeType) === NODE_TYPE.element) _stripDisallowedAttributes(node);
			const childNodes = getChildNodes(node);
			if (childNodes) for (let i = childNodes.length - 1; i >= 0; --i) stack.push(childNodes[i]);
		}
	};
	/**
	* _initDocument
	*
	* @param dirty - a string of dirty markup
	* @return a DOM, filled with the dirty markup
	*/
	const _initDocument = function _initDocument(dirty) {
		let doc = null;
		let leadingWhitespace = null;
		if (FORCE_BODY) dirty = "<remove></remove>" + dirty;
		else {
			const matches = stringMatch(dirty, /^[\r\n\t ]+/);
			leadingWhitespace = matches && matches[0];
		}
		if (PARSER_MEDIA_TYPE === "application/xhtml+xml" && NAMESPACE === HTML_NAMESPACE) dirty = "<html xmlns=\"http://www.w3.org/1999/xhtml\"><head></head><body>" + dirty + "</body></html>";
		const dirtyPayload = trustedTypesPolicy ? _createTrustedHTML(dirty) : dirty;
		if (NAMESPACE === HTML_NAMESPACE) try {
			doc = new DOMParser().parseFromString(dirtyPayload, PARSER_MEDIA_TYPE);
		} catch (_) {}
		if (!doc || !doc.documentElement) {
			doc = implementation.createDocument(NAMESPACE, "template", null);
			try {
				doc.documentElement.innerHTML = IS_EMPTY_INPUT ? emptyHTML : dirtyPayload;
			} catch (_) {}
		}
		const body = doc.body || doc.documentElement;
		if (dirty && leadingWhitespace) body.insertBefore(document.createTextNode(leadingWhitespace), body.childNodes[0] || null);
		if (NAMESPACE === HTML_NAMESPACE) return getElementsByTagName.call(doc, WHOLE_DOCUMENT ? "html" : "body")[0];
		return WHOLE_DOCUMENT ? doc.documentElement : body;
	};
	/**
	* Creates a NodeIterator object that you can use to traverse filtered lists of nodes or elements in a document.
	*
	* @param root The root element or node to start traversing on.
	* @return The created NodeIterator
	*/
	const _createNodeIterator = function _createNodeIterator(root) {
		return createNodeIterator.call(root.ownerDocument || root, root, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_COMMENT | NodeFilter.SHOW_TEXT | NodeFilter.SHOW_PROCESSING_INSTRUCTION | NodeFilter.SHOW_CDATA_SECTION, null);
	};
	/**
	* Replace template expression syntax (mustache, ERB, template
	* literal) with a space; shared by all SAFE_FOR_TEMPLATES scrub
	* sites. Order matters: mustache, then ERB, then template literal.
	*
	* @param value the string to scrub
	* @returns the scrubbed string
	*/
	const _stripTemplateExpressions = function _stripTemplateExpressions(value) {
		value = stringReplace(value, MUSTACHE_EXPR$1, " ");
		value = stringReplace(value, ERB_EXPR$1, " ");
		value = stringReplace(value, TMPLIT_EXPR$1, " ");
		return value;
	};
	/**
	* Strip template-engine expressions ({{...}}, ${...}, <%...%>) from the
	* character data of an element subtree. Used as the final safety net for
	* SAFE_FOR_TEMPLATES on every DOM-returning code path so that expressions
	* which only form after text-node normalization (e.g. fragments split across
	* stripped elements) cannot survive into a template-evaluating framework.
	*
	* Walks text/comment/CDATA/processing-instruction nodes and mutates `.data`
	* in place rather than round-tripping through innerHTML. This preserves
	* descendant node references (important for IN_PLACE callers), avoids a
	* serialize/reparse cycle, and reads literal character data — which means
	* `<%...%>` in text content matches the ERB regex against its real bytes
	* instead of the HTML-entity-escaped form innerHTML would produce.
	*
	* Attribute values are not visited here; SAFE_FOR_TEMPLATES handling for
	* attributes is performed during the per-node `_sanitizeAttributes` pass.
	*
	* @param node The root element whose character data should be scrubbed.
	*/
	const _scrubTemplateExpressions2 = function _scrubTemplateExpressions(node) {
		var _node$querySelectorAl;
		node.normalize();
		const walker = createNodeIterator.call(node.ownerDocument || node, node, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_COMMENT | NodeFilter.SHOW_CDATA_SECTION | NodeFilter.SHOW_PROCESSING_INSTRUCTION, null);
		let currentNode = walker.nextNode();
		while (currentNode) {
			currentNode.data = _stripTemplateExpressions(currentNode.data);
			currentNode = walker.nextNode();
		}
		const templates = (_node$querySelectorAl = node.querySelectorAll) === null || _node$querySelectorAl === void 0 ? void 0 : _node$querySelectorAl.call(node, "template");
		if (templates) arrayForEach(templates, (tmpl) => {
			if (_isDocumentFragment(tmpl.content)) _scrubTemplateExpressions2(tmpl.content);
		});
	};
	/**
	* _isClobbered
	*
	* Detect DOM-clobbering on HTMLFormElement nodes. Form is the only HTML
	* interface with [LegacyOverrideBuiltIns]; a descendant element with a
	* `name` attribute matching a prototype property shadows that property
	* on direct reads. We use this check at the IN_PLACE entry-point and
	* during attribute sanitization to refuse clobbered forms.
	*
	* @param element element to check for clobbering attacks
	* @return true if clobbered, false if safe
	*/
	const _isClobbered = function _isClobbered(element) {
		const realTagName = getNodeName ? getNodeName(element) : null;
		if (typeof realTagName !== "string") return false;
		if (transformCaseFunc(realTagName) !== "form") return false;
		return typeof element.nodeName !== "string" || typeof element.textContent !== "string" || typeof element.removeChild !== "function" || element.attributes !== getAttributes(element) || typeof element.removeAttribute !== "function" || typeof element.setAttribute !== "function" || typeof element.namespaceURI !== "string" || typeof element.insertBefore !== "function" || typeof element.hasChildNodes !== "function" || element.nodeType !== getNodeType(element) || element.childNodes !== getChildNodes(element);
	};
	/**
	* Checks whether the given value is a DocumentFragment from any realm.
	*
	* The realm-independent replacement reads `nodeType` through the cached
	* Node.prototype getter and compares to the DOCUMENT_FRAGMENT_NODE
	* constant (11). nodeType is a numeric value resolved from the node's
	* internal slot, identical across realms for the same kind of node.
	*
	* @param value object to check
	* @return true if value is a DocumentFragment-shaped node from any realm
	*/
	const _isDocumentFragment = function _isDocumentFragment(value) {
		if (!getNodeType || typeof value !== "object" || value === null) return false;
		try {
			return getNodeType(value) === NODE_TYPE.documentFragment;
		} catch (_) {
			return false;
		}
	};
	/**
	* Checks whether the given object is a DOM node, including nodes that
	* originate from a different window/realm (e.g. an iframe's
	* contentDocument). The previous `value instanceof Node` check was
	* realm-bound: nodes from a different window failed it, causing
	* sanitize() to silently stringify them and reset IN_PLACE to false,
	* returning the original node unsanitized. See GHSA-4w3q-35jp-p934.
	*
	* @param value object to check whether it's a DOM node
	* @return true if value is a DOM node from any realm
	*/
	const _isNode = function _isNode(value) {
		if (!getNodeType || typeof value !== "object" || value === null) return false;
		try {
			return typeof getNodeType(value) === "number";
		} catch (_) {
			return false;
		}
	};
	function _executeHooks(hooks, currentNode, data) {
		if (hooks.length === 0) return;
		arrayForEach(hooks, (hook) => {
			hook.call(DOMPurify, currentNode, data, CONFIG);
		});
	}
	/**
	* Structural-threat checks that condemn a node regardless of the
	* allowlists: mXSS via namespace confusion, risky CSS construction,
	* processing instructions, markup-bearing comments. Pure predicate;
	* the caller removes. Check order is load-bearing.
	*
	* @param currentNode the node to inspect
	* @param tagName the node's transformCaseFunc'd tag name
	* @return true if the node must be removed
	*/
	const _isUnsafeNode = function _isUnsafeNode(currentNode, tagName) {
		if (SAFE_FOR_XML && currentNode.hasChildNodes() && !_isNode(currentNode.firstElementChild) && regExpTest(ELEMENT_MARKUP_PROBE, currentNode.textContent) && regExpTest(ELEMENT_MARKUP_PROBE, currentNode.innerHTML)) return true;
		if (SAFE_FOR_XML && currentNode.namespaceURI === HTML_NAMESPACE && tagName === "style" && _isNode(currentNode.firstElementChild)) return true;
		if (currentNode.nodeType === NODE_TYPE.processingInstruction) return true;
		if (SAFE_FOR_XML && currentNode.nodeType === NODE_TYPE.comment && regExpTest(COMMENT_MARKUP_PROBE, currentNode.data)) return true;
		return false;
	};
	/**
	* Handle a node whose tag is forbidden or not allowlisted: keep
	* allowed custom elements (false return exits _sanitizeElements
	* early - namespace/fallback checks and the afterSanitizeElements
	* hook are intentionally skipped for kept custom elements), else
	* hoist content per KEEP_CONTENT and remove.
	*
	* @param currentNode the disallowed node
	* @param tagName the node's transformCaseFunc'd tag name
	* @return true if the node was removed, false if kept
	*/
	const _sanitizeDisallowedNode = function _sanitizeDisallowedNode(currentNode, tagName) {
		if (!FORBID_TAGS[tagName] && _isBasicCustomElement(tagName)) {
			if (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.tagNameCheck, tagName)) return false;
			if (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.tagNameCheck(tagName)) return false;
		}
		if (KEEP_CONTENT && !FORBID_CONTENTS[tagName]) {
			const parentNode = getParentNode(currentNode);
			const childNodes = getChildNodes(currentNode);
			if (childNodes && parentNode) {
				const childCount = childNodes.length;
				for (let i = childCount - 1; i >= 0; --i) {
					const hoisted = IN_PLACE ? childNodes[i] : cloneNode(childNodes[i], true);
					parentNode.insertBefore(hoisted, getNextSibling(currentNode));
				}
			}
		}
		_forceRemove(currentNode);
		return true;
	};
	/**
	* _sanitizeElements
	*
	* @protect nodeName
	* @protect textContent
	* @protect removeChild
	* @param currentNode to check for permission to exist
	* @return true if node was killed, false if left alive
	*/
	const _sanitizeElements = function _sanitizeElements(currentNode) {
		_executeHooks(hooks.beforeSanitizeElements, currentNode, null);
		if (_isClobbered(currentNode)) {
			_forceRemove(currentNode);
			return true;
		}
		const tagName = transformCaseFunc(getNodeName ? getNodeName(currentNode) : currentNode.nodeName);
		_executeHooks(hooks.uponSanitizeElement, currentNode, {
			tagName,
			allowedTags: ALLOWED_TAGS
		});
		if (_isUnsafeNode(currentNode, tagName)) {
			_forceRemove(currentNode);
			return true;
		}
		if (FORBID_TAGS[tagName] || !(EXTRA_ELEMENT_HANDLING.tagCheck instanceof Function && EXTRA_ELEMENT_HANDLING.tagCheck(tagName)) && !ALLOWED_TAGS[tagName]) return _sanitizeDisallowedNode(currentNode, tagName);
		if ((getNodeType ? getNodeType(currentNode) : currentNode.nodeType) === NODE_TYPE.element && !_checkValidNamespace(currentNode)) {
			_forceRemove(currentNode);
			return true;
		}
		if ((tagName === "noscript" || tagName === "noembed" || tagName === "noframes") && regExpTest(FALLBACK_TAG_CLOSE, currentNode.innerHTML)) {
			_forceRemove(currentNode);
			return true;
		}
		if (SAFE_FOR_TEMPLATES && currentNode.nodeType === NODE_TYPE.text) {
			const content = _stripTemplateExpressions(currentNode.textContent);
			if (currentNode.textContent !== content) {
				arrayPush(DOMPurify.removed, { element: currentNode.cloneNode() });
				currentNode.textContent = content;
			}
		}
		_executeHooks(hooks.afterSanitizeElements, currentNode, null);
		return false;
	};
	/**
	* _isValidAttribute
	*
	* @param lcTag Lowercase tag name of containing element.
	* @param lcName Lowercase attribute name.
	* @param value Attribute value.
	* @return Returns true if `value` is valid, otherwise false.
	*/
	const _isValidAttribute = function _isValidAttribute(lcTag, lcName, value) {
		if (FORBID_ATTR[lcName]) return false;
		if (SANITIZE_DOM && (lcName === "id" || lcName === "name") && (value in document || value in formElement)) return false;
		const nameIsPermitted = ALLOWED_ATTR[lcName] || EXTRA_ELEMENT_HANDLING.attributeCheck instanceof Function && EXTRA_ELEMENT_HANDLING.attributeCheck(lcName, lcTag);
		if (ALLOW_DATA_ATTR && regExpTest(DATA_ATTR$1, lcName));
		else if (ALLOW_ARIA_ATTR && regExpTest(ARIA_ATTR$1, lcName));
		else if (!nameIsPermitted) if (_isBasicCustomElement(lcTag) && (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.tagNameCheck, lcTag) || CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.tagNameCheck(lcTag)) && (CUSTOM_ELEMENT_HANDLING.attributeNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.attributeNameCheck, lcName) || CUSTOM_ELEMENT_HANDLING.attributeNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.attributeNameCheck(lcName, lcTag)) || lcName === "is" && CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements && (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.tagNameCheck, value) || CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.tagNameCheck(value)));
		else return false;
		else if (URI_SAFE_ATTRIBUTES[lcName]);
		else if (regExpTest(IS_ALLOWED_URI$1, stringReplace(value, ATTR_WHITESPACE$1, "")));
		else if ((lcName === "src" || lcName === "xlink:href" || lcName === "href") && lcTag !== "script" && stringIndexOf(value, "data:") === 0 && DATA_URI_TAGS[lcTag]);
		else if (ALLOW_UNKNOWN_PROTOCOLS && !regExpTest(IS_SCRIPT_OR_DATA$1, stringReplace(value, ATTR_WHITESPACE$1, "")));
		else if (value) return false;
		return true;
	};
	const RESERVED_CUSTOM_ELEMENT_NAMES = addToSet({}, [
		"annotation-xml",
		"color-profile",
		"font-face",
		"font-face-format",
		"font-face-name",
		"font-face-src",
		"font-face-uri",
		"missing-glyph"
	]);
	/**
	* _isBasicCustomElement
	* checks if at least one dash is included in tagName, and it's not the first char
	* for more sophisticated checking see https://github.com/sindresorhus/validate-element-name
	*
	* @param tagName name of the tag of the node to sanitize
	* @returns Returns true if the tag name meets the basic criteria for a custom element, otherwise false.
	*/
	const _isBasicCustomElement = function _isBasicCustomElement(tagName) {
		return !RESERVED_CUSTOM_ELEMENT_NAMES[stringToLowerCase(tagName)] && regExpTest(CUSTOM_ELEMENT$1, tagName);
	};
	/**
	* Wrap an attribute value in the matching Trusted Types object when
	* the active policy requires it. Namespaced attributes pass through
	* unchanged (no TT support yet, see
	* https://bugs.chromium.org/p/chromium/issues/detail?id=1305293).
	*
	* @param lcTag lowercase tag name of the containing element
	* @param lcName lowercase attribute name
	* @param namespaceURI the attribute's namespace, if any
	* @param value the attribute value to wrap
	* @return the value, wrapped when Trusted Types demand it
	*/
	const _applyTrustedTypesToAttribute = function _applyTrustedTypesToAttribute(lcTag, lcName, namespaceURI, value) {
		if (trustedTypesPolicy && typeof trustedTypes === "object" && typeof trustedTypes.getAttributeType === "function" && !namespaceURI) switch (trustedTypes.getAttributeType(lcTag, lcName)) {
			case "TrustedHTML": return _createTrustedHTML(value);
			case "TrustedScriptURL": return _createTrustedScriptURL(value);
		}
		return value;
	};
	/**
	* Write a modified attribute value back onto the element. On
	* success, re-probe for clobbering introduced by the new value and
	* remove the element when found; otherwise pop the removal entry
	* recorded by the earlier _removeAttribute (long-standing pairing
	* with the SANITIZE_NAMED_PROPS path - do not "fix" casually). On
	* failure, remove the attribute instead.
	*
	* @param currentNode the element carrying the attribute
	* @param name the attribute name as present on the element
	* @param namespaceURI the attribute's namespace, if any
	* @param value the new attribute value
	*/
	const _setAttributeValue = function _setAttributeValue(currentNode, name, namespaceURI, value) {
		try {
			if (namespaceURI) currentNode.setAttributeNS(namespaceURI, name, value);
			else currentNode.setAttribute(name, value);
			if (_isClobbered(currentNode)) _forceRemove(currentNode);
			else arrayPop(DOMPurify.removed);
		} catch (_) {
			_removeAttribute(name, currentNode);
		}
	};
	/**
	* _sanitizeAttributes
	*
	* @protect attributes
	* @protect nodeName
	* @protect removeAttribute
	* @protect setAttribute
	*
	* @param currentNode to sanitize
	*/
	const _sanitizeAttributes = function _sanitizeAttributes(currentNode) {
		_executeHooks(hooks.beforeSanitizeAttributes, currentNode, null);
		const attributes = currentNode.attributes;
		if (!attributes || _isClobbered(currentNode)) return;
		const hookEvent = {
			attrName: "",
			attrValue: "",
			keepAttr: true,
			allowedAttributes: ALLOWED_ATTR,
			forceKeepAttr: void 0
		};
		let l = attributes.length;
		const lcTag = transformCaseFunc(currentNode.nodeName);
		while (l--) {
			const attr = attributes[l];
			const name = attr.name, namespaceURI = attr.namespaceURI, attrValue = attr.value;
			const lcName = transformCaseFunc(name);
			const initValue = attrValue;
			let value = name === "value" ? initValue : stringTrim(initValue);
			hookEvent.attrName = lcName;
			hookEvent.attrValue = value;
			hookEvent.keepAttr = true;
			hookEvent.forceKeepAttr = void 0;
			_executeHooks(hooks.uponSanitizeAttribute, currentNode, hookEvent);
			value = hookEvent.attrValue;
			if (SANITIZE_NAMED_PROPS && (lcName === "id" || lcName === "name") && stringIndexOf(value, SANITIZE_NAMED_PROPS_PREFIX) !== 0) {
				_removeAttribute(name, currentNode);
				value = SANITIZE_NAMED_PROPS_PREFIX + value;
			}
			if (SAFE_FOR_XML && regExpTest(/((--!?|])>)|<\/(style|script|title|xmp|textarea|noscript|iframe|noembed|noframes)/i, value)) {
				_removeAttribute(name, currentNode);
				continue;
			}
			if (lcName === "attributename" && stringMatch(value, "href")) {
				_removeAttribute(name, currentNode);
				continue;
			}
			if (hookEvent.forceKeepAttr) continue;
			if (!hookEvent.keepAttr) {
				_removeAttribute(name, currentNode);
				continue;
			}
			if (!ALLOW_SELF_CLOSE_IN_ATTR && regExpTest(SELF_CLOSING_TAG, value)) {
				_removeAttribute(name, currentNode);
				continue;
			}
			if (SAFE_FOR_TEMPLATES) value = _stripTemplateExpressions(value);
			if (!_isValidAttribute(lcTag, lcName, value)) {
				_removeAttribute(name, currentNode);
				continue;
			}
			value = _applyTrustedTypesToAttribute(lcTag, lcName, namespaceURI, value);
			if (value !== initValue) _setAttributeValue(currentNode, name, namespaceURI, value);
		}
		_executeHooks(hooks.afterSanitizeAttributes, currentNode, null);
	};
	/**
	* _sanitizeShadowDOM
	*
	* @param fragment to iterate over recursively
	*/
	const _sanitizeShadowDOM2 = function _sanitizeShadowDOM(fragment) {
		let shadowNode = null;
		const shadowIterator = _createNodeIterator(fragment);
		_executeHooks(hooks.beforeSanitizeShadowDOM, fragment, null);
		while (shadowNode = shadowIterator.nextNode()) {
			_executeHooks(hooks.uponSanitizeShadowNode, shadowNode, null);
			_sanitizeElements(shadowNode);
			_sanitizeAttributes(shadowNode);
			if (_isDocumentFragment(shadowNode.content)) _sanitizeShadowDOM2(shadowNode.content);
			if ((getNodeType ? getNodeType(shadowNode) : shadowNode.nodeType) === NODE_TYPE.element) {
				const innerSr = getShadowRoot(shadowNode);
				if (_isDocumentFragment(innerSr)) {
					_sanitizeAttachedShadowRoots(innerSr);
					_sanitizeShadowDOM2(innerSr);
				}
			}
		}
		_executeHooks(hooks.afterSanitizeShadowDOM, fragment, null);
	};
	/**
	* _sanitizeAttachedShadowRoots
	*
	* Walks `root` and feeds every attached shadow root we encounter into
	* the existing _sanitizeShadowDOM pipeline. The default node iterator
	* does not descend into shadow trees, so nodes inside an attached
	* shadow root would otherwise be skipped entirely.
	*
	* Two real input paths put attached shadow roots in front of us:
	*   1. IN_PLACE on a DOM node that already has shadow roots attached.
	*   2. DOM-node input where importNode(dirty, true) deep-clones the
	*      shadow root because it was created with `clonable: true`.
	*
	* This pass runs once, up front, so the main iteration loop (and the
	* existing _sanitizeShadowDOM template-content recursion) stay
	* untouched — string-input paths are not affected.
	*
	* @param root the subtree root to walk for attached shadow roots
	*/
	const _sanitizeAttachedShadowRoots = function _sanitizeAttachedShadowRoots(root) {
		const stack = [{
			node: root,
			shadow: null
		}];
		while (stack.length > 0) {
			const item = stack.pop();
			if (item.shadow) {
				_sanitizeShadowDOM2(item.shadow);
				continue;
			}
			const node = item.node;
			const isElement = (getNodeType ? getNodeType(node) : node.nodeType) === NODE_TYPE.element;
			const childNodes = getChildNodes(node);
			if (childNodes) for (let i = childNodes.length - 1; i >= 0; --i) stack.push({
				node: childNodes[i],
				shadow: null
			});
			if (isElement) {
				const rootName = getNodeName ? getNodeName(node) : null;
				if (typeof rootName === "string" && transformCaseFunc(rootName) === "template") {
					const content = node.content;
					if (_isDocumentFragment(content)) stack.push({
						node: content,
						shadow: null
					});
				}
			}
			if (isElement) {
				const sr = getShadowRoot(node);
				if (_isDocumentFragment(sr)) stack.push({
					node: null,
					shadow: sr
				}, {
					node: sr,
					shadow: null
				});
			}
		}
	};
	DOMPurify.sanitize = function(dirty) {
		let cfg = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
		let body = null;
		let importedNode = null;
		let currentNode = null;
		let returnNode = null;
		IS_EMPTY_INPUT = !dirty;
		if (IS_EMPTY_INPUT) dirty = "<!-->";
		if (typeof dirty !== "string" && !_isNode(dirty)) {
			dirty = stringifyValue(dirty);
			if (typeof dirty !== "string") throw typeErrorCreate("dirty is not a string, aborting");
		}
		if (!DOMPurify.isSupported) return dirty;
		if (SET_CONFIG) {
			ALLOWED_TAGS = SET_CONFIG_ALLOWED_TAGS;
			ALLOWED_ATTR = SET_CONFIG_ALLOWED_ATTR;
		} else _parseConfig(cfg);
		if (hooks.uponSanitizeElement.length > 0 || hooks.uponSanitizeAttribute.length > 0) ALLOWED_TAGS = clone$1(ALLOWED_TAGS);
		if (hooks.uponSanitizeAttribute.length > 0) ALLOWED_ATTR = clone$1(ALLOWED_ATTR);
		DOMPurify.removed = [];
		const inPlace = IN_PLACE && typeof dirty !== "string" && _isNode(dirty);
		if (inPlace) {
			const nn = getNodeName ? getNodeName(dirty) : dirty.nodeName;
			if (typeof nn === "string") {
				const tagName = transformCaseFunc(nn);
				if (!ALLOWED_TAGS[tagName] || FORBID_TAGS[tagName]) throw typeErrorCreate("root node is forbidden and cannot be sanitized in-place");
			}
			if (_isClobbered(dirty)) throw typeErrorCreate("root node is clobbered and cannot be sanitized in-place");
			try {
				_sanitizeAttachedShadowRoots(dirty);
			} catch (error) {
				_neutralizeRoot(dirty);
				throw error;
			}
		} else if (_isNode(dirty)) {
			body = _initDocument("<!---->");
			importedNode = body.ownerDocument.importNode(dirty, true);
			if (importedNode.nodeType === NODE_TYPE.element && importedNode.nodeName === "BODY") body = importedNode;
			else if (importedNode.nodeName === "HTML") body = importedNode;
			else body.appendChild(importedNode);
			_sanitizeAttachedShadowRoots(importedNode);
		} else {
			if (!RETURN_DOM && !SAFE_FOR_TEMPLATES && !WHOLE_DOCUMENT && dirty.indexOf("<") === -1) return trustedTypesPolicy && RETURN_TRUSTED_TYPE ? _createTrustedHTML(dirty) : dirty;
			body = _initDocument(dirty);
			if (!body) return RETURN_DOM ? null : RETURN_TRUSTED_TYPE ? emptyHTML : "";
		}
		if (body && FORCE_BODY) _forceRemove(body.firstChild);
		const nodeIterator = _createNodeIterator(inPlace ? dirty : body);
		try {
			while (currentNode = nodeIterator.nextNode()) {
				_sanitizeElements(currentNode);
				_sanitizeAttributes(currentNode);
				if (_isDocumentFragment(currentNode.content)) _sanitizeShadowDOM2(currentNode.content);
			}
		} catch (error) {
			if (inPlace) _neutralizeRoot(dirty);
			throw error;
		}
		if (inPlace) {
			arrayForEach(DOMPurify.removed, (entry) => {
				if (entry.element) _neutralizeSubtree(entry.element);
			});
			if (SAFE_FOR_TEMPLATES) _scrubTemplateExpressions2(dirty);
			return dirty;
		}
		if (RETURN_DOM) {
			if (SAFE_FOR_TEMPLATES) _scrubTemplateExpressions2(body);
			if (RETURN_DOM_FRAGMENT) {
				returnNode = createDocumentFragment.call(body.ownerDocument);
				while (body.firstChild) returnNode.appendChild(body.firstChild);
			} else returnNode = body;
			if (ALLOWED_ATTR.shadowroot || ALLOWED_ATTR.shadowrootmode) returnNode = importNode.call(originalDocument, returnNode, true);
			return returnNode;
		}
		let serializedHTML = WHOLE_DOCUMENT ? body.outerHTML : body.innerHTML;
		if (WHOLE_DOCUMENT && ALLOWED_TAGS["!doctype"] && body.ownerDocument && body.ownerDocument.doctype && body.ownerDocument.doctype.name && regExpTest(DOCTYPE_NAME, body.ownerDocument.doctype.name)) serializedHTML = "<!DOCTYPE " + body.ownerDocument.doctype.name + ">\n" + serializedHTML;
		if (SAFE_FOR_TEMPLATES) serializedHTML = _stripTemplateExpressions(serializedHTML);
		return trustedTypesPolicy && RETURN_TRUSTED_TYPE ? _createTrustedHTML(serializedHTML) : serializedHTML;
	};
	DOMPurify.setConfig = function() {
		_parseConfig(arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {});
		SET_CONFIG = true;
		SET_CONFIG_ALLOWED_TAGS = ALLOWED_TAGS;
		SET_CONFIG_ALLOWED_ATTR = ALLOWED_ATTR;
	};
	DOMPurify.clearConfig = function() {
		CONFIG = null;
		SET_CONFIG = false;
		SET_CONFIG_ALLOWED_TAGS = null;
		SET_CONFIG_ALLOWED_ATTR = null;
		trustedTypesPolicy = defaultTrustedTypesPolicy;
		emptyHTML = "";
	};
	DOMPurify.isValidAttribute = function(tag, attr, value) {
		if (!CONFIG) _parseConfig({});
		return _isValidAttribute(transformCaseFunc(tag), transformCaseFunc(attr), value);
	};
	DOMPurify.addHook = function(entryPoint, hookFunction) {
		if (typeof hookFunction !== "function") return;
		if (!objectHasOwnProperty(hooks, entryPoint)) return;
		arrayPush(hooks[entryPoint], hookFunction);
	};
	DOMPurify.removeHook = function(entryPoint, hookFunction) {
		if (!objectHasOwnProperty(hooks, entryPoint)) return;
		if (hookFunction !== void 0) {
			const index = arrayLastIndexOf(hooks[entryPoint], hookFunction);
			return index === -1 ? void 0 : arraySplice(hooks[entryPoint], index, 1)[0];
		}
		return arrayPop(hooks[entryPoint]);
	};
	DOMPurify.removeHooks = function(entryPoint) {
		if (!objectHasOwnProperty(hooks, entryPoint)) return;
		hooks[entryPoint] = [];
	};
	DOMPurify.removeAllHooks = function() {
		hooks = _createHooksMap();
	};
	return DOMPurify;
}
var purify = createDOMPurify();
//#endregion
//#region node_modules/nanoid/url-alphabet/index.js
var urlAlphabet = "useandom-26T198340PX75pxJACKVERYMINDBUSHWOLF_GQZbfghjklqvwyzrict";
//#endregion
//#region node_modules/nanoid/index.browser.js
var nanoid = (size = 21) => {
	let id = "";
	let bytes = crypto.getRandomValues(new Uint8Array(size |= 0));
	while (size--) id += urlAlphabet[bytes[size] & 63];
	return id;
};
//#endregion
//#region resources/js/translations/interval.js
var intervalRegexp = /^({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])$/;
var anyIntervalRegexp = /({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])/;
var testInterval = function(count, interval) {
	/**
	* From the Symfony\Component\Translation\Interval Docs
	*
	* Tests if a given number belongs to a given math interval.
	*
	* An interval can represent a finite set of numbers:
	*
	*  {1,2,3,4}
	*
	* An interval can represent numbers between two numbers:
	*
	*  [1, +Inf]
	*  ]-1,2[
	*
	* The left delimiter can be [ (inclusive) or ] (exclusive).
	* The right delimiter can be [ (exclusive) or ] (inclusive).
	* Beside numbers, you can use -Inf and +Inf for the infinite.
	*/
	if (typeof interval !== "string") throw "Invalid interval: should be a string.";
	interval = interval.trim();
	var matches = interval.match(intervalRegexp);
	if (!matches) throw "Invalid interval: " + interval;
	if (matches[2]) {
		var items = matches[2].split(",");
		for (var i = 0; i < items.length; i++) if (parseInt(items[i], 10) === count) return true;
	} else {
		matches = matches.filter(function(match) {
			return !!match;
		});
		var leftDelimiter = matches[1];
		var leftNumber = convertNumber(matches[2]);
		if (leftNumber === Infinity) leftNumber = -Infinity;
		var rightNumber = convertNumber(matches[3]);
		var rightDelimiter = matches[4];
		return (leftDelimiter === "[" ? count >= leftNumber : count > leftNumber) && (rightDelimiter === "]" ? count <= rightNumber : count < rightNumber);
	}
	return false;
};
function convertNumber(str) {
	if (str === "-Inf") return -Infinity;
	else if (str === "+Inf" || str === "Inf" || str === "*") return Infinity;
	return parseInt(str, 10);
}
//#endregion
//#region resources/js/translations/message-selector.js
function choose(message, number, locale) {
	let messageParts = message.split("|");
	let explicitRules = [];
	for (let i = 0; i < messageParts.length; i++) {
		messageParts[i] = messageParts[i].trim();
		if (anyIntervalRegexp.test(messageParts[i])) {
			let messageSpaceSplit = messageParts[i].split(/\s/);
			explicitRules.push(messageSpaceSplit.shift());
			messageParts[i] = messageSpaceSplit.join(" ");
		}
	}
	if (messageParts.length === 1) return message;
	for (let j = 0; j < explicitRules.length; j++) if (testInterval(number, explicitRules[j])) return messageParts[j];
	return messageParts[getPluralForm(number, locale)];
}
var getPluralForm = function(count, locale) {
	if (locale.includes("_")) locale = locale.substr(0, locale.indexOf("_"));
	switch (locale) {
		case "az":
		case "bo":
		case "dz":
		case "id":
		case "ja":
		case "jv":
		case "ka":
		case "km":
		case "kn":
		case "ko":
		case "ms":
		case "th":
		case "tr":
		case "vi":
		case "zh": return 0;
		case "af":
		case "bn":
		case "bg":
		case "ca":
		case "da":
		case "de":
		case "el":
		case "en":
		case "eo":
		case "es":
		case "et":
		case "eu":
		case "fa":
		case "fi":
		case "fo":
		case "fur":
		case "fy":
		case "gl":
		case "gu":
		case "ha":
		case "he":
		case "hu":
		case "is":
		case "it":
		case "ku":
		case "lb":
		case "ml":
		case "mn":
		case "mr":
		case "nah":
		case "nb":
		case "ne":
		case "nl":
		case "nn":
		case "no":
		case "om":
		case "or":
		case "pa":
		case "pap":
		case "ps":
		case "pt":
		case "so":
		case "sq":
		case "sv":
		case "sw":
		case "ta":
		case "te":
		case "tk":
		case "ur":
		case "zu": return count == 1 ? 0 : 1;
		case "am":
		case "bh":
		case "fil":
		case "fr":
		case "gun":
		case "hi":
		case "hy":
		case "ln":
		case "mg":
		case "nso":
		case "xbr":
		case "ti":
		case "wa": return count === 0 || count === 1 ? 0 : 1;
		case "be":
		case "bs":
		case "hr":
		case "ru":
		case "sr":
		case "uk": return count % 10 == 1 && count % 100 != 11 ? 0 : count % 10 >= 2 && count % 10 <= 4 && (count % 100 < 10 || count % 100 >= 20) ? 1 : 2;
		case "cs":
		case "sk": return count == 1 ? 0 : count >= 2 && count <= 4 ? 1 : 2;
		case "ga": return count == 1 ? 0 : count == 2 ? 1 : 2;
		case "lt": return count % 10 == 1 && count % 100 != 11 ? 0 : count % 10 >= 2 && (count % 100 < 10 || count % 100 >= 20) ? 1 : 2;
		case "sl": return count % 100 == 1 ? 0 : count % 100 == 2 ? 1 : count % 100 == 3 || count % 100 == 4 ? 2 : 3;
		case "mk": return count % 10 == 1 ? 0 : 1;
		case "mt": return count == 1 ? 0 : count === 0 || count % 100 > 1 && count % 100 < 11 ? 1 : count % 100 > 10 && count % 100 < 20 ? 2 : 3;
		case "lv": return count === 0 ? 0 : count % 10 == 1 && count % 100 != 11 ? 1 : 2;
		case "pl": return count == 1 ? 0 : count % 10 >= 2 && count % 10 <= 4 && (count % 100 < 12 || count % 100 > 14) ? 1 : 2;
		case "cy": return count == 1 ? 0 : count == 2 ? 1 : count == 8 || count == 11 ? 2 : 3;
		case "ro": return count == 1 ? 0 : count === 0 || count % 100 > 0 && count % 100 < 20 ? 1 : 2;
		case "ar": return count === 0 ? 0 : count == 1 ? 1 : count == 2 ? 2 : count % 100 >= 3 && count % 100 <= 10 ? 3 : count % 100 >= 11 && count % 100 <= 99 ? 4 : 5;
		default: return 0;
	}
};
//#endregion
//#region resources/js/translations/translator.js
var translations = {};
var locale = "en";
function setTranslations(newTranslations) {
	translations = newTranslations;
}
function setLocale(newLocale) {
	locale = newLocale;
}
/**
* Get a translated string
*
* @param {String} key The translation key
* @param {Object} replacements A key/value set of string replacements
* @return String
*/
var translate = function(key, replacements = {}) {
	let message = getLine(key);
	for (let replace in replacements) message = message.split(":" + replace).join(replacements[replace]);
	return message;
};
/**
* Get a translated string determined by plurality
*
* @param {String} key The translation key
* @param {Integer|Array} count The number of items to determine the plurality. Can also just be an array.
* @param {Object} replacements A key/value set of string replacements
* @return String
*/
var translateChoice = function(key, count, replacements) {
	replacements = typeof replacements !== "undefined" ? replacements : {};
	count = Array.isArray(count) ? count.length : count;
	replacements.count = count;
	return choose(translate(key, replacements), count, locale);
};
/**
* Get a line from the supplied translation files
* @param {String} key
*/
var getLine = function(key) {
	return translations[`*.${key}`] || translations[key] || translations[`statamic::${key}`] || translations[`statamic::messages.${key}`] || key;
};
//#endregion
//#region resources/js/components/fieldtypes/replicator/PreviewHtml.js
var PreviewHtml = class {
	constructor(html) {
		this.html = purify.sanitize(html ?? "");
	}
};
//#endregion
//#region node_modules/marked/lib/marked.esm.js
/**
* marked v15.0.12 - a markdown parser
* Copyright (c) 2011-2025, Christopher Jeffrey. (MIT Licensed)
* https://github.com/markedjs/marked
*/
/**
* DO NOT EDIT THIS FILE
* The code in this file is generated from files in ./src/
*/
function _getDefaults() {
	return {
		async: false,
		breaks: false,
		extensions: null,
		gfm: true,
		hooks: null,
		pedantic: false,
		renderer: null,
		silent: false,
		tokenizer: null,
		walkTokens: null
	};
}
var _defaults = _getDefaults();
function changeDefaults(newDefaults) {
	_defaults = newDefaults;
}
var noopTest = { exec: () => null };
function edit(regex, opt = "") {
	let source = typeof regex === "string" ? regex : regex.source;
	const obj = {
		replace: (name, val) => {
			let valSource = typeof val === "string" ? val : val.source;
			valSource = valSource.replace(other.caret, "$1");
			source = source.replace(name, valSource);
			return obj;
		},
		getRegex: () => {
			return new RegExp(source, opt);
		}
	};
	return obj;
}
var other = {
	codeRemoveIndent: /^(?: {1,4}| {0,3}\t)/gm,
	outputLinkReplace: /\\([\[\]])/g,
	indentCodeCompensation: /^(\s+)(?:```)/,
	beginningSpace: /^\s+/,
	endingHash: /#$/,
	startingSpaceChar: /^ /,
	endingSpaceChar: / $/,
	nonSpaceChar: /[^ ]/,
	newLineCharGlobal: /\n/g,
	tabCharGlobal: /\t/g,
	multipleSpaceGlobal: /\s+/g,
	blankLine: /^[ \t]*$/,
	doubleBlankLine: /\n[ \t]*\n[ \t]*$/,
	blockquoteStart: /^ {0,3}>/,
	blockquoteSetextReplace: /\n {0,3}((?:=+|-+) *)(?=\n|$)/g,
	blockquoteSetextReplace2: /^ {0,3}>[ \t]?/gm,
	listReplaceTabs: /^\t+/,
	listReplaceNesting: /^ {1,4}(?=( {4})*[^ ])/g,
	listIsTask: /^\[[ xX]\] /,
	listReplaceTask: /^\[[ xX]\] +/,
	anyLine: /\n.*\n/,
	hrefBrackets: /^<(.*)>$/,
	tableDelimiter: /[:|]/,
	tableAlignChars: /^\||\| *$/g,
	tableRowBlankLine: /\n[ \t]*$/,
	tableAlignRight: /^ *-+: *$/,
	tableAlignCenter: /^ *:-+: *$/,
	tableAlignLeft: /^ *:-+ *$/,
	startATag: /^<a /i,
	endATag: /^<\/a>/i,
	startPreScriptTag: /^<(pre|code|kbd|script)(\s|>)/i,
	endPreScriptTag: /^<\/(pre|code|kbd|script)(\s|>)/i,
	startAngleBracket: /^</,
	endAngleBracket: />$/,
	pedanticHrefTitle: /^([^'"]*[^\s])\s+(['"])(.*)\2/,
	unicodeAlphaNumeric: /[\p{L}\p{N}]/u,
	escapeTest: /[&<>"']/,
	escapeReplace: /[&<>"']/g,
	escapeTestNoEncode: /[<>"']|&(?!(#\d{1,7}|#[Xx][a-fA-F0-9]{1,6}|\w+);)/,
	escapeReplaceNoEncode: /[<>"']|&(?!(#\d{1,7}|#[Xx][a-fA-F0-9]{1,6}|\w+);)/g,
	unescapeTest: /&(#(?:\d+)|(?:#x[0-9A-Fa-f]+)|(?:\w+));?/gi,
	caret: /(^|[^\[])\^/g,
	percentDecode: /%25/g,
	findPipe: /\|/g,
	splitPipe: / \|/,
	slashPipe: /\\\|/g,
	carriageReturn: /\r\n|\r/g,
	spaceLine: /^ +$/gm,
	notSpaceStart: /^\S*/,
	endingNewline: /\n$/,
	listItemRegex: (bull) => new RegExp(`^( {0,3}${bull})((?:[	 ][^\\n]*)?(?:\\n|$))`),
	nextBulletRegex: (indent) => new RegExp(`^ {0,${Math.min(3, indent - 1)}}(?:[*+-]|\\d{1,9}[.)])((?:[ 	][^\\n]*)?(?:\\n|$))`),
	hrRegex: (indent) => new RegExp(`^ {0,${Math.min(3, indent - 1)}}((?:- *){3,}|(?:_ *){3,}|(?:\\* *){3,})(?:\\n+|$)`),
	fencesBeginRegex: (indent) => new RegExp(`^ {0,${Math.min(3, indent - 1)}}(?:\`\`\`|~~~)`),
	headingBeginRegex: (indent) => new RegExp(`^ {0,${Math.min(3, indent - 1)}}#`),
	htmlBeginRegex: (indent) => new RegExp(`^ {0,${Math.min(3, indent - 1)}}<(?:[a-z].*>|!--)`, "i")
};
var newline = /^(?:[ \t]*(?:\n|$))+/;
var blockCode = /^((?: {4}| {0,3}\t)[^\n]+(?:\n(?:[ \t]*(?:\n|$))*)?)+/;
var fences = /^ {0,3}(`{3,}(?=[^`\n]*(?:\n|$))|~{3,})([^\n]*)(?:\n|$)(?:|([\s\S]*?)(?:\n|$))(?: {0,3}\1[~`]* *(?=\n|$)|$)/;
var hr = /^ {0,3}((?:-[\t ]*){3,}|(?:_[ \t]*){3,}|(?:\*[ \t]*){3,})(?:\n+|$)/;
var heading = /^ {0,3}(#{1,6})(?=\s|$)(.*)(?:\n+|$)/;
var bullet = /(?:[*+-]|\d{1,9}[.)])/;
var lheadingCore = /^(?!bull |blockCode|fences|blockquote|heading|html|table)((?:.|\n(?!\s*?\n|bull |blockCode|fences|blockquote|heading|html|table))+?)\n {0,3}(=+|-+) *(?:\n+|$)/;
var lheading = edit(lheadingCore).replace(/bull/g, bullet).replace(/blockCode/g, /(?: {4}| {0,3}\t)/).replace(/fences/g, / {0,3}(?:`{3,}|~{3,})/).replace(/blockquote/g, / {0,3}>/).replace(/heading/g, / {0,3}#{1,6}/).replace(/html/g, / {0,3}<[^\n>]+>\n/).replace(/\|table/g, "").getRegex();
var lheadingGfm = edit(lheadingCore).replace(/bull/g, bullet).replace(/blockCode/g, /(?: {4}| {0,3}\t)/).replace(/fences/g, / {0,3}(?:`{3,}|~{3,})/).replace(/blockquote/g, / {0,3}>/).replace(/heading/g, / {0,3}#{1,6}/).replace(/html/g, / {0,3}<[^\n>]+>\n/).replace(/table/g, / {0,3}\|?(?:[:\- ]*\|)+[\:\- ]*\n/).getRegex();
var _paragraph = /^([^\n]+(?:\n(?!hr|heading|lheading|blockquote|fences|list|html|table| +\n)[^\n]+)*)/;
var blockText = /^[^\n]+/;
var _blockLabel = /(?!\s*\])(?:\\.|[^\[\]\\])+/;
var def = edit(/^ {0,3}\[(label)\]: *(?:\n[ \t]*)?([^<\s][^\s]*|<.*?>)(?:(?: +(?:\n[ \t]*)?| *\n[ \t]*)(title))? *(?:\n+|$)/).replace("label", _blockLabel).replace("title", /(?:"(?:\\"?|[^"\\])*"|'[^'\n]*(?:\n[^'\n]+)*\n?'|\([^()]*\))/).getRegex();
var list = edit(/^( {0,3}bull)([ \t][^\n]+?)?(?:\n|$)/).replace(/bull/g, bullet).getRegex();
var _tag = "address|article|aside|base|basefont|blockquote|body|caption|center|col|colgroup|dd|details|dialog|dir|div|dl|dt|fieldset|figcaption|figure|footer|form|frame|frameset|h[1-6]|head|header|hr|html|iframe|legend|li|link|main|menu|menuitem|meta|nav|noframes|ol|optgroup|option|p|param|search|section|summary|table|tbody|td|tfoot|th|thead|title|tr|track|ul";
var _comment = /<!--(?:-?>|[\s\S]*?(?:-->|$))/;
var html = edit("^ {0,3}(?:<(script|pre|style|textarea)[\\s>][\\s\\S]*?(?:</\\1>[^\\n]*\\n+|$)|comment[^\\n]*(\\n+|$)|<\\?[\\s\\S]*?(?:\\?>\\n*|$)|<![A-Z][\\s\\S]*?(?:>\\n*|$)|<!\\[CDATA\\[[\\s\\S]*?(?:\\]\\]>\\n*|$)|</?(tag)(?: +|\\n|/?>)[\\s\\S]*?(?:(?:\\n[ 	]*)+\\n|$)|<(?!script|pre|style|textarea)([a-z][\\w-]*)(?:attribute)*? */?>(?=[ \\t]*(?:\\n|$))[\\s\\S]*?(?:(?:\\n[ 	]*)+\\n|$)|</(?!script|pre|style|textarea)[a-z][\\w-]*\\s*>(?=[ \\t]*(?:\\n|$))[\\s\\S]*?(?:(?:\\n[ 	]*)+\\n|$))", "i").replace("comment", _comment).replace("tag", _tag).replace("attribute", / +[a-zA-Z:_][\w.:-]*(?: *= *"[^"\n]*"| *= *'[^'\n]*'| *= *[^\s"'=<>`]+)?/).getRegex();
var paragraph = edit(_paragraph).replace("hr", hr).replace("heading", " {0,3}#{1,6}(?:\\s|$)").replace("|lheading", "").replace("|table", "").replace("blockquote", " {0,3}>").replace("fences", " {0,3}(?:`{3,}(?=[^`\\n]*\\n)|~{3,})[^\\n]*\\n").replace("list", " {0,3}(?:[*+-]|1[.)]) ").replace("html", "</?(?:tag)(?: +|\\n|/?>)|<(?:script|pre|style|textarea|!--)").replace("tag", _tag).getRegex();
var blockNormal = {
	blockquote: edit(/^( {0,3}> ?(paragraph|[^\n]*)(?:\n|$))+/).replace("paragraph", paragraph).getRegex(),
	code: blockCode,
	def,
	fences,
	heading,
	hr,
	html,
	lheading,
	list,
	newline,
	paragraph,
	table: noopTest,
	text: blockText
};
var gfmTable = edit("^ *([^\\n ].*)\\n {0,3}((?:\\| *)?:?-+:? *(?:\\| *:?-+:? *)*(?:\\| *)?)(?:\\n((?:(?! *\\n|hr|heading|blockquote|code|fences|list|html).*(?:\\n|$))*)\\n*|$)").replace("hr", hr).replace("heading", " {0,3}#{1,6}(?:\\s|$)").replace("blockquote", " {0,3}>").replace("code", "(?: {4}| {0,3}	)[^\\n]").replace("fences", " {0,3}(?:`{3,}(?=[^`\\n]*\\n)|~{3,})[^\\n]*\\n").replace("list", " {0,3}(?:[*+-]|1[.)]) ").replace("html", "</?(?:tag)(?: +|\\n|/?>)|<(?:script|pre|style|textarea|!--)").replace("tag", _tag).getRegex();
var blockGfm = {
	...blockNormal,
	lheading: lheadingGfm,
	table: gfmTable,
	paragraph: edit(_paragraph).replace("hr", hr).replace("heading", " {0,3}#{1,6}(?:\\s|$)").replace("|lheading", "").replace("table", gfmTable).replace("blockquote", " {0,3}>").replace("fences", " {0,3}(?:`{3,}(?=[^`\\n]*\\n)|~{3,})[^\\n]*\\n").replace("list", " {0,3}(?:[*+-]|1[.)]) ").replace("html", "</?(?:tag)(?: +|\\n|/?>)|<(?:script|pre|style|textarea|!--)").replace("tag", _tag).getRegex()
};
var blockPedantic = {
	...blockNormal,
	html: edit(`^ *(?:comment *(?:\\n|\\s*$)|<(tag)[\\s\\S]+?</\\1> *(?:\\n{2,}|\\s*$)|<tag(?:"[^"]*"|'[^']*'|\\s[^'"/>\\s]*)*?/?> *(?:\\n{2,}|\\s*$))`).replace("comment", _comment).replace(/tag/g, "(?!(?:a|em|strong|small|s|cite|q|dfn|abbr|data|time|code|var|samp|kbd|sub|sup|i|b|u|mark|ruby|rt|rp|bdi|bdo|span|br|wbr|ins|del|img)\\b)\\w+(?!:|[^\\w\\s@]*@)\\b").getRegex(),
	def: /^ *\[([^\]]+)\]: *<?([^\s>]+)>?(?: +(["(][^\n]+[")]))? *(?:\n+|$)/,
	heading: /^(#{1,6})(.*)(?:\n+|$)/,
	fences: noopTest,
	lheading: /^(.+?)\n {0,3}(=+|-+) *(?:\n+|$)/,
	paragraph: edit(_paragraph).replace("hr", hr).replace("heading", " *#{1,6} *[^\n]").replace("lheading", lheading).replace("|table", "").replace("blockquote", " {0,3}>").replace("|fences", "").replace("|list", "").replace("|html", "").replace("|tag", "").getRegex()
};
var escape = /^\\([!"#$%&'()*+,\-./:;<=>?@\[\]\\^_`{|}~])/;
var inlineCode = /^(`+)([^`]|[^`][\s\S]*?[^`])\1(?!`)/;
var br = /^( {2,}|\\)\n(?!\s*$)/;
var inlineText = /^(`+|[^`])(?:(?= {2,}\n)|[\s\S]*?(?:(?=[\\<!\[`*_]|\b_|$)|[^ ](?= {2,}\n)))/;
var _punctuation = /[\p{P}\p{S}]/u;
var _punctuationOrSpace = /[\s\p{P}\p{S}]/u;
var _notPunctuationOrSpace = /[^\s\p{P}\p{S}]/u;
var punctuation = edit(/^((?![*_])punctSpace)/, "u").replace(/punctSpace/g, _punctuationOrSpace).getRegex();
var _punctuationGfmStrongEm = /(?!~)[\p{P}\p{S}]/u;
var _punctuationOrSpaceGfmStrongEm = /(?!~)[\s\p{P}\p{S}]/u;
var _notPunctuationOrSpaceGfmStrongEm = /(?:[^\s\p{P}\p{S}]|~)/u;
var blockSkip = /\[[^[\]]*?\]\((?:\\.|[^\\\(\)]|\((?:\\.|[^\\\(\)])*\))*\)|`[^`]*?`|<[^<>]*?>/g;
var emStrongLDelimCore = /^(?:\*+(?:((?!\*)punct)|[^\s*]))|^_+(?:((?!_)punct)|([^\s_]))/;
var emStrongLDelim = edit(emStrongLDelimCore, "u").replace(/punct/g, _punctuation).getRegex();
var emStrongLDelimGfm = edit(emStrongLDelimCore, "u").replace(/punct/g, _punctuationGfmStrongEm).getRegex();
var emStrongRDelimAstCore = "^[^_*]*?__[^_*]*?\\*[^_*]*?(?=__)|[^*]+(?=[^*])|(?!\\*)punct(\\*+)(?=[\\s]|$)|notPunctSpace(\\*+)(?!\\*)(?=punctSpace|$)|(?!\\*)punctSpace(\\*+)(?=notPunctSpace)|[\\s](\\*+)(?!\\*)(?=punct)|(?!\\*)punct(\\*+)(?!\\*)(?=punct)|notPunctSpace(\\*+)(?=notPunctSpace)";
var emStrongRDelimAst = edit(emStrongRDelimAstCore, "gu").replace(/notPunctSpace/g, _notPunctuationOrSpace).replace(/punctSpace/g, _punctuationOrSpace).replace(/punct/g, _punctuation).getRegex();
var emStrongRDelimAstGfm = edit(emStrongRDelimAstCore, "gu").replace(/notPunctSpace/g, _notPunctuationOrSpaceGfmStrongEm).replace(/punctSpace/g, _punctuationOrSpaceGfmStrongEm).replace(/punct/g, _punctuationGfmStrongEm).getRegex();
var emStrongRDelimUnd = edit("^[^_*]*?\\*\\*[^_*]*?_[^_*]*?(?=\\*\\*)|[^_]+(?=[^_])|(?!_)punct(_+)(?=[\\s]|$)|notPunctSpace(_+)(?!_)(?=punctSpace|$)|(?!_)punctSpace(_+)(?=notPunctSpace)|[\\s](_+)(?!_)(?=punct)|(?!_)punct(_+)(?!_)(?=punct)", "gu").replace(/notPunctSpace/g, _notPunctuationOrSpace).replace(/punctSpace/g, _punctuationOrSpace).replace(/punct/g, _punctuation).getRegex();
var anyPunctuation = edit(/\\(punct)/, "gu").replace(/punct/g, _punctuation).getRegex();
var autolink = edit(/^<(scheme:[^\s\x00-\x1f<>]*|email)>/).replace("scheme", /[a-zA-Z][a-zA-Z0-9+.-]{1,31}/).replace("email", /[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+(@)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+(?![-_])/).getRegex();
var _inlineComment = edit(_comment).replace("(?:-->|$)", "-->").getRegex();
var tag = edit("^comment|^</[a-zA-Z][\\w:-]*\\s*>|^<[a-zA-Z][\\w-]*(?:attribute)*?\\s*/?>|^<\\?[\\s\\S]*?\\?>|^<![a-zA-Z]+\\s[\\s\\S]*?>|^<!\\[CDATA\\[[\\s\\S]*?\\]\\]>").replace("comment", _inlineComment).replace("attribute", /\s+[a-zA-Z:_][\w.:-]*(?:\s*=\s*"[^"]*"|\s*=\s*'[^']*'|\s*=\s*[^\s"'=<>`]+)?/).getRegex();
var _inlineLabel = /(?:\[(?:\\.|[^\[\]\\])*\]|\\.|`[^`]*`|[^\[\]\\`])*?/;
var link = edit(/^!?\[(label)\]\(\s*(href)(?:(?:[ \t]*(?:\n[ \t]*)?)(title))?\s*\)/).replace("label", _inlineLabel).replace("href", /<(?:\\.|[^\n<>\\])+>|[^ \t\n\x00-\x1f]*/).replace("title", /"(?:\\"?|[^"\\])*"|'(?:\\'?|[^'\\])*'|\((?:\\\)?|[^)\\])*\)/).getRegex();
var reflink = edit(/^!?\[(label)\]\[(ref)\]/).replace("label", _inlineLabel).replace("ref", _blockLabel).getRegex();
var nolink = edit(/^!?\[(ref)\](?:\[\])?/).replace("ref", _blockLabel).getRegex();
var inlineNormal = {
	_backpedal: noopTest,
	anyPunctuation,
	autolink,
	blockSkip,
	br,
	code: inlineCode,
	del: noopTest,
	emStrongLDelim,
	emStrongRDelimAst,
	emStrongRDelimUnd,
	escape,
	link,
	nolink,
	punctuation,
	reflink,
	reflinkSearch: edit("reflink|nolink(?!\\()", "g").replace("reflink", reflink).replace("nolink", nolink).getRegex(),
	tag,
	text: inlineText,
	url: noopTest
};
var inlinePedantic = {
	...inlineNormal,
	link: edit(/^!?\[(label)\]\((.*?)\)/).replace("label", _inlineLabel).getRegex(),
	reflink: edit(/^!?\[(label)\]\s*\[([^\]]*)\]/).replace("label", _inlineLabel).getRegex()
};
var inlineGfm = {
	...inlineNormal,
	emStrongRDelimAst: emStrongRDelimAstGfm,
	emStrongLDelim: emStrongLDelimGfm,
	url: edit(/^((?:ftp|https?):\/\/|www\.)(?:[a-zA-Z0-9\-]+\.?)+[^\s<]*|^email/, "i").replace("email", /[A-Za-z0-9._+-]+(@)[a-zA-Z0-9-_]+(?:\.[a-zA-Z0-9-_]*[a-zA-Z0-9])+(?![-_])/).getRegex(),
	_backpedal: /(?:[^?!.,:;*_'"~()&]+|\([^)]*\)|&(?![a-zA-Z0-9]+;$)|[?!.,:;*_'"~)]+(?!$))+/,
	del: /^(~~?)(?=[^\s~])((?:\\.|[^\\])*?(?:\\.|[^\s~\\]))\1(?=[^~]|$)/,
	text: /^([`~]+|[^`~])(?:(?= {2,}\n)|(?=[a-zA-Z0-9.!#$%&'*+\/=?_`{\|}~-]+@)|[\s\S]*?(?:(?=[\\<!\[`*~_]|\b_|https?:\/\/|ftp:\/\/|www\.|$)|[^ ](?= {2,}\n)|[^a-zA-Z0-9.!#$%&'*+\/=?_`{\|}~-](?=[a-zA-Z0-9.!#$%&'*+\/=?_`{\|}~-]+@)))/
};
var inlineBreaks = {
	...inlineGfm,
	br: edit(br).replace("{2,}", "*").getRegex(),
	text: edit(inlineGfm.text).replace("\\b_", "\\b_| {2,}\\n").replace(/\{2,\}/g, "*").getRegex()
};
var block = {
	normal: blockNormal,
	gfm: blockGfm,
	pedantic: blockPedantic
};
var inline = {
	normal: inlineNormal,
	gfm: inlineGfm,
	breaks: inlineBreaks,
	pedantic: inlinePedantic
};
var escapeReplacements = {
	"&": "&amp;",
	"<": "&lt;",
	">": "&gt;",
	"\"": "&quot;",
	"'": "&#39;"
};
var getEscapeReplacement = (ch) => escapeReplacements[ch];
function escape2(html2, encode) {
	if (encode) {
		if (other.escapeTest.test(html2)) return html2.replace(other.escapeReplace, getEscapeReplacement);
	} else if (other.escapeTestNoEncode.test(html2)) return html2.replace(other.escapeReplaceNoEncode, getEscapeReplacement);
	return html2;
}
function cleanUrl(href) {
	try {
		href = encodeURI(href).replace(other.percentDecode, "%");
	} catch {
		return null;
	}
	return href;
}
function splitCells(tableRow, count) {
	const cells = tableRow.replace(other.findPipe, (match, offset, str) => {
		let escaped = false;
		let curr = offset;
		while (--curr >= 0 && str[curr] === "\\") escaped = !escaped;
		if (escaped) return "|";
		else return " |";
	}).split(other.splitPipe);
	let i = 0;
	if (!cells[0].trim()) cells.shift();
	if (cells.length > 0 && !cells.at(-1)?.trim()) cells.pop();
	if (count) if (cells.length > count) cells.splice(count);
	else while (cells.length < count) cells.push("");
	for (; i < cells.length; i++) cells[i] = cells[i].trim().replace(other.slashPipe, "|");
	return cells;
}
function rtrim(str, c, invert) {
	const l = str.length;
	if (l === 0) return "";
	let suffLen = 0;
	while (suffLen < l) {
		const currChar = str.charAt(l - suffLen - 1);
		if (currChar === c && !invert) suffLen++;
		else if (currChar !== c && invert) suffLen++;
		else break;
	}
	return str.slice(0, l - suffLen);
}
function findClosingBracket(str, b) {
	if (str.indexOf(b[1]) === -1) return -1;
	let level = 0;
	for (let i = 0; i < str.length; i++) if (str[i] === "\\") i++;
	else if (str[i] === b[0]) level++;
	else if (str[i] === b[1]) {
		level--;
		if (level < 0) return i;
	}
	if (level > 0) return -2;
	return -1;
}
function outputLink(cap, link2, raw, lexer2, rules) {
	const href = link2.href;
	const title = link2.title || null;
	const text = cap[1].replace(rules.other.outputLinkReplace, "$1");
	lexer2.state.inLink = true;
	const token = {
		type: cap[0].charAt(0) === "!" ? "image" : "link",
		raw,
		href,
		title,
		text,
		tokens: lexer2.inlineTokens(text)
	};
	lexer2.state.inLink = false;
	return token;
}
function indentCodeCompensation(raw, text, rules) {
	const matchIndentToCode = raw.match(rules.other.indentCodeCompensation);
	if (matchIndentToCode === null) return text;
	const indentToCode = matchIndentToCode[1];
	return text.split("\n").map((node) => {
		const matchIndentInNode = node.match(rules.other.beginningSpace);
		if (matchIndentInNode === null) return node;
		const [indentInNode] = matchIndentInNode;
		if (indentInNode.length >= indentToCode.length) return node.slice(indentToCode.length);
		return node;
	}).join("\n");
}
var _Tokenizer = class {
	options;
	rules;
	lexer;
	constructor(options2) {
		this.options = options2 || _defaults;
	}
	space(src) {
		const cap = this.rules.block.newline.exec(src);
		if (cap && cap[0].length > 0) return {
			type: "space",
			raw: cap[0]
		};
	}
	code(src) {
		const cap = this.rules.block.code.exec(src);
		if (cap) {
			const text = cap[0].replace(this.rules.other.codeRemoveIndent, "");
			return {
				type: "code",
				raw: cap[0],
				codeBlockStyle: "indented",
				text: !this.options.pedantic ? rtrim(text, "\n") : text
			};
		}
	}
	fences(src) {
		const cap = this.rules.block.fences.exec(src);
		if (cap) {
			const raw = cap[0];
			const text = indentCodeCompensation(raw, cap[3] || "", this.rules);
			return {
				type: "code",
				raw,
				lang: cap[2] ? cap[2].trim().replace(this.rules.inline.anyPunctuation, "$1") : cap[2],
				text
			};
		}
	}
	heading(src) {
		const cap = this.rules.block.heading.exec(src);
		if (cap) {
			let text = cap[2].trim();
			if (this.rules.other.endingHash.test(text)) {
				const trimmed = rtrim(text, "#");
				if (this.options.pedantic) text = trimmed.trim();
				else if (!trimmed || this.rules.other.endingSpaceChar.test(trimmed)) text = trimmed.trim();
			}
			return {
				type: "heading",
				raw: cap[0],
				depth: cap[1].length,
				text,
				tokens: this.lexer.inline(text)
			};
		}
	}
	hr(src) {
		const cap = this.rules.block.hr.exec(src);
		if (cap) return {
			type: "hr",
			raw: rtrim(cap[0], "\n")
		};
	}
	blockquote(src) {
		const cap = this.rules.block.blockquote.exec(src);
		if (cap) {
			let lines = rtrim(cap[0], "\n").split("\n");
			let raw = "";
			let text = "";
			const tokens = [];
			while (lines.length > 0) {
				let inBlockquote = false;
				const currentLines = [];
				let i;
				for (i = 0; i < lines.length; i++) if (this.rules.other.blockquoteStart.test(lines[i])) {
					currentLines.push(lines[i]);
					inBlockquote = true;
				} else if (!inBlockquote) currentLines.push(lines[i]);
				else break;
				lines = lines.slice(i);
				const currentRaw = currentLines.join("\n");
				const currentText = currentRaw.replace(this.rules.other.blockquoteSetextReplace, "\n    $1").replace(this.rules.other.blockquoteSetextReplace2, "");
				raw = raw ? `${raw}
${currentRaw}` : currentRaw;
				text = text ? `${text}
${currentText}` : currentText;
				const top = this.lexer.state.top;
				this.lexer.state.top = true;
				this.lexer.blockTokens(currentText, tokens, true);
				this.lexer.state.top = top;
				if (lines.length === 0) break;
				const lastToken = tokens.at(-1);
				if (lastToken?.type === "code") break;
				else if (lastToken?.type === "blockquote") {
					const oldToken = lastToken;
					const newText = oldToken.raw + "\n" + lines.join("\n");
					const newToken = this.blockquote(newText);
					tokens[tokens.length - 1] = newToken;
					raw = raw.substring(0, raw.length - oldToken.raw.length) + newToken.raw;
					text = text.substring(0, text.length - oldToken.text.length) + newToken.text;
					break;
				} else if (lastToken?.type === "list") {
					const oldToken = lastToken;
					const newText = oldToken.raw + "\n" + lines.join("\n");
					const newToken = this.list(newText);
					tokens[tokens.length - 1] = newToken;
					raw = raw.substring(0, raw.length - lastToken.raw.length) + newToken.raw;
					text = text.substring(0, text.length - oldToken.raw.length) + newToken.raw;
					lines = newText.substring(tokens.at(-1).raw.length).split("\n");
					continue;
				}
			}
			return {
				type: "blockquote",
				raw,
				tokens,
				text
			};
		}
	}
	list(src) {
		let cap = this.rules.block.list.exec(src);
		if (cap) {
			let bull = cap[1].trim();
			const isordered = bull.length > 1;
			const list2 = {
				type: "list",
				raw: "",
				ordered: isordered,
				start: isordered ? +bull.slice(0, -1) : "",
				loose: false,
				items: []
			};
			bull = isordered ? `\\d{1,9}\\${bull.slice(-1)}` : `\\${bull}`;
			if (this.options.pedantic) bull = isordered ? bull : "[*+-]";
			const itemRegex = this.rules.other.listItemRegex(bull);
			let endsWithBlankLine = false;
			while (src) {
				let endEarly = false;
				let raw = "";
				let itemContents = "";
				if (!(cap = itemRegex.exec(src))) break;
				if (this.rules.block.hr.test(src)) break;
				raw = cap[0];
				src = src.substring(raw.length);
				let line = cap[2].split("\n", 1)[0].replace(this.rules.other.listReplaceTabs, (t) => " ".repeat(3 * t.length));
				let nextLine = src.split("\n", 1)[0];
				let blankLine = !line.trim();
				let indent = 0;
				if (this.options.pedantic) {
					indent = 2;
					itemContents = line.trimStart();
				} else if (blankLine) indent = cap[1].length + 1;
				else {
					indent = cap[2].search(this.rules.other.nonSpaceChar);
					indent = indent > 4 ? 1 : indent;
					itemContents = line.slice(indent);
					indent += cap[1].length;
				}
				if (blankLine && this.rules.other.blankLine.test(nextLine)) {
					raw += nextLine + "\n";
					src = src.substring(nextLine.length + 1);
					endEarly = true;
				}
				if (!endEarly) {
					const nextBulletRegex = this.rules.other.nextBulletRegex(indent);
					const hrRegex = this.rules.other.hrRegex(indent);
					const fencesBeginRegex = this.rules.other.fencesBeginRegex(indent);
					const headingBeginRegex = this.rules.other.headingBeginRegex(indent);
					const htmlBeginRegex = this.rules.other.htmlBeginRegex(indent);
					while (src) {
						const rawLine = src.split("\n", 1)[0];
						let nextLineWithoutTabs;
						nextLine = rawLine;
						if (this.options.pedantic) {
							nextLine = nextLine.replace(this.rules.other.listReplaceNesting, "  ");
							nextLineWithoutTabs = nextLine;
						} else nextLineWithoutTabs = nextLine.replace(this.rules.other.tabCharGlobal, "    ");
						if (fencesBeginRegex.test(nextLine)) break;
						if (headingBeginRegex.test(nextLine)) break;
						if (htmlBeginRegex.test(nextLine)) break;
						if (nextBulletRegex.test(nextLine)) break;
						if (hrRegex.test(nextLine)) break;
						if (nextLineWithoutTabs.search(this.rules.other.nonSpaceChar) >= indent || !nextLine.trim()) itemContents += "\n" + nextLineWithoutTabs.slice(indent);
						else {
							if (blankLine) break;
							if (line.replace(this.rules.other.tabCharGlobal, "    ").search(this.rules.other.nonSpaceChar) >= 4) break;
							if (fencesBeginRegex.test(line)) break;
							if (headingBeginRegex.test(line)) break;
							if (hrRegex.test(line)) break;
							itemContents += "\n" + nextLine;
						}
						if (!blankLine && !nextLine.trim()) blankLine = true;
						raw += rawLine + "\n";
						src = src.substring(rawLine.length + 1);
						line = nextLineWithoutTabs.slice(indent);
					}
				}
				if (!list2.loose) {
					if (endsWithBlankLine) list2.loose = true;
					else if (this.rules.other.doubleBlankLine.test(raw)) endsWithBlankLine = true;
				}
				let istask = null;
				let ischecked;
				if (this.options.gfm) {
					istask = this.rules.other.listIsTask.exec(itemContents);
					if (istask) {
						ischecked = istask[0] !== "[ ] ";
						itemContents = itemContents.replace(this.rules.other.listReplaceTask, "");
					}
				}
				list2.items.push({
					type: "list_item",
					raw,
					task: !!istask,
					checked: ischecked,
					loose: false,
					text: itemContents,
					tokens: []
				});
				list2.raw += raw;
			}
			const lastItem = list2.items.at(-1);
			if (lastItem) {
				lastItem.raw = lastItem.raw.trimEnd();
				lastItem.text = lastItem.text.trimEnd();
			} else return;
			list2.raw = list2.raw.trimEnd();
			for (let i = 0; i < list2.items.length; i++) {
				this.lexer.state.top = false;
				list2.items[i].tokens = this.lexer.blockTokens(list2.items[i].text, []);
				if (!list2.loose) {
					const spacers = list2.items[i].tokens.filter((t) => t.type === "space");
					list2.loose = spacers.length > 0 && spacers.some((t) => this.rules.other.anyLine.test(t.raw));
				}
			}
			if (list2.loose) for (let i = 0; i < list2.items.length; i++) list2.items[i].loose = true;
			return list2;
		}
	}
	html(src) {
		const cap = this.rules.block.html.exec(src);
		if (cap) return {
			type: "html",
			block: true,
			raw: cap[0],
			pre: cap[1] === "pre" || cap[1] === "script" || cap[1] === "style",
			text: cap[0]
		};
	}
	def(src) {
		const cap = this.rules.block.def.exec(src);
		if (cap) {
			const tag2 = cap[1].toLowerCase().replace(this.rules.other.multipleSpaceGlobal, " ");
			const href = cap[2] ? cap[2].replace(this.rules.other.hrefBrackets, "$1").replace(this.rules.inline.anyPunctuation, "$1") : "";
			const title = cap[3] ? cap[3].substring(1, cap[3].length - 1).replace(this.rules.inline.anyPunctuation, "$1") : cap[3];
			return {
				type: "def",
				tag: tag2,
				raw: cap[0],
				href,
				title
			};
		}
	}
	table(src) {
		const cap = this.rules.block.table.exec(src);
		if (!cap) return;
		if (!this.rules.other.tableDelimiter.test(cap[2])) return;
		const headers = splitCells(cap[1]);
		const aligns = cap[2].replace(this.rules.other.tableAlignChars, "").split("|");
		const rows = cap[3]?.trim() ? cap[3].replace(this.rules.other.tableRowBlankLine, "").split("\n") : [];
		const item = {
			type: "table",
			raw: cap[0],
			header: [],
			align: [],
			rows: []
		};
		if (headers.length !== aligns.length) return;
		for (const align of aligns) if (this.rules.other.tableAlignRight.test(align)) item.align.push("right");
		else if (this.rules.other.tableAlignCenter.test(align)) item.align.push("center");
		else if (this.rules.other.tableAlignLeft.test(align)) item.align.push("left");
		else item.align.push(null);
		for (let i = 0; i < headers.length; i++) item.header.push({
			text: headers[i],
			tokens: this.lexer.inline(headers[i]),
			header: true,
			align: item.align[i]
		});
		for (const row of rows) item.rows.push(splitCells(row, item.header.length).map((cell, i) => {
			return {
				text: cell,
				tokens: this.lexer.inline(cell),
				header: false,
				align: item.align[i]
			};
		}));
		return item;
	}
	lheading(src) {
		const cap = this.rules.block.lheading.exec(src);
		if (cap) return {
			type: "heading",
			raw: cap[0],
			depth: cap[2].charAt(0) === "=" ? 1 : 2,
			text: cap[1],
			tokens: this.lexer.inline(cap[1])
		};
	}
	paragraph(src) {
		const cap = this.rules.block.paragraph.exec(src);
		if (cap) {
			const text = cap[1].charAt(cap[1].length - 1) === "\n" ? cap[1].slice(0, -1) : cap[1];
			return {
				type: "paragraph",
				raw: cap[0],
				text,
				tokens: this.lexer.inline(text)
			};
		}
	}
	text(src) {
		const cap = this.rules.block.text.exec(src);
		if (cap) return {
			type: "text",
			raw: cap[0],
			text: cap[0],
			tokens: this.lexer.inline(cap[0])
		};
	}
	escape(src) {
		const cap = this.rules.inline.escape.exec(src);
		if (cap) return {
			type: "escape",
			raw: cap[0],
			text: cap[1]
		};
	}
	tag(src) {
		const cap = this.rules.inline.tag.exec(src);
		if (cap) {
			if (!this.lexer.state.inLink && this.rules.other.startATag.test(cap[0])) this.lexer.state.inLink = true;
			else if (this.lexer.state.inLink && this.rules.other.endATag.test(cap[0])) this.lexer.state.inLink = false;
			if (!this.lexer.state.inRawBlock && this.rules.other.startPreScriptTag.test(cap[0])) this.lexer.state.inRawBlock = true;
			else if (this.lexer.state.inRawBlock && this.rules.other.endPreScriptTag.test(cap[0])) this.lexer.state.inRawBlock = false;
			return {
				type: "html",
				raw: cap[0],
				inLink: this.lexer.state.inLink,
				inRawBlock: this.lexer.state.inRawBlock,
				block: false,
				text: cap[0]
			};
		}
	}
	link(src) {
		const cap = this.rules.inline.link.exec(src);
		if (cap) {
			const trimmedUrl = cap[2].trim();
			if (!this.options.pedantic && this.rules.other.startAngleBracket.test(trimmedUrl)) {
				if (!this.rules.other.endAngleBracket.test(trimmedUrl)) return;
				const rtrimSlash = rtrim(trimmedUrl.slice(0, -1), "\\");
				if ((trimmedUrl.length - rtrimSlash.length) % 2 === 0) return;
			} else {
				const lastParenIndex = findClosingBracket(cap[2], "()");
				if (lastParenIndex === -2) return;
				if (lastParenIndex > -1) {
					const linkLen = (cap[0].indexOf("!") === 0 ? 5 : 4) + cap[1].length + lastParenIndex;
					cap[2] = cap[2].substring(0, lastParenIndex);
					cap[0] = cap[0].substring(0, linkLen).trim();
					cap[3] = "";
				}
			}
			let href = cap[2];
			let title = "";
			if (this.options.pedantic) {
				const link2 = this.rules.other.pedanticHrefTitle.exec(href);
				if (link2) {
					href = link2[1];
					title = link2[3];
				}
			} else title = cap[3] ? cap[3].slice(1, -1) : "";
			href = href.trim();
			if (this.rules.other.startAngleBracket.test(href)) if (this.options.pedantic && !this.rules.other.endAngleBracket.test(trimmedUrl)) href = href.slice(1);
			else href = href.slice(1, -1);
			return outputLink(cap, {
				href: href ? href.replace(this.rules.inline.anyPunctuation, "$1") : href,
				title: title ? title.replace(this.rules.inline.anyPunctuation, "$1") : title
			}, cap[0], this.lexer, this.rules);
		}
	}
	reflink(src, links) {
		let cap;
		if ((cap = this.rules.inline.reflink.exec(src)) || (cap = this.rules.inline.nolink.exec(src))) {
			const link2 = links[(cap[2] || cap[1]).replace(this.rules.other.multipleSpaceGlobal, " ").toLowerCase()];
			if (!link2) {
				const text = cap[0].charAt(0);
				return {
					type: "text",
					raw: text,
					text
				};
			}
			return outputLink(cap, link2, cap[0], this.lexer, this.rules);
		}
	}
	emStrong(src, maskedSrc, prevChar = "") {
		let match = this.rules.inline.emStrongLDelim.exec(src);
		if (!match) return;
		if (match[3] && prevChar.match(this.rules.other.unicodeAlphaNumeric)) return;
		if (!(match[1] || match[2] || "") || !prevChar || this.rules.inline.punctuation.exec(prevChar)) {
			const lLength = [...match[0]].length - 1;
			let rDelim, rLength, delimTotal = lLength, midDelimTotal = 0;
			const endReg = match[0][0] === "*" ? this.rules.inline.emStrongRDelimAst : this.rules.inline.emStrongRDelimUnd;
			endReg.lastIndex = 0;
			maskedSrc = maskedSrc.slice(-1 * src.length + lLength);
			while ((match = endReg.exec(maskedSrc)) != null) {
				rDelim = match[1] || match[2] || match[3] || match[4] || match[5] || match[6];
				if (!rDelim) continue;
				rLength = [...rDelim].length;
				if (match[3] || match[4]) {
					delimTotal += rLength;
					continue;
				} else if (match[5] || match[6]) {
					if (lLength % 3 && !((lLength + rLength) % 3)) {
						midDelimTotal += rLength;
						continue;
					}
				}
				delimTotal -= rLength;
				if (delimTotal > 0) continue;
				rLength = Math.min(rLength, rLength + delimTotal + midDelimTotal);
				const lastCharLength = [...match[0]][0].length;
				const raw = src.slice(0, lLength + match.index + lastCharLength + rLength);
				if (Math.min(lLength, rLength) % 2) {
					const text2 = raw.slice(1, -1);
					return {
						type: "em",
						raw,
						text: text2,
						tokens: this.lexer.inlineTokens(text2)
					};
				}
				const text = raw.slice(2, -2);
				return {
					type: "strong",
					raw,
					text,
					tokens: this.lexer.inlineTokens(text)
				};
			}
		}
	}
	codespan(src) {
		const cap = this.rules.inline.code.exec(src);
		if (cap) {
			let text = cap[2].replace(this.rules.other.newLineCharGlobal, " ");
			const hasNonSpaceChars = this.rules.other.nonSpaceChar.test(text);
			const hasSpaceCharsOnBothEnds = this.rules.other.startingSpaceChar.test(text) && this.rules.other.endingSpaceChar.test(text);
			if (hasNonSpaceChars && hasSpaceCharsOnBothEnds) text = text.substring(1, text.length - 1);
			return {
				type: "codespan",
				raw: cap[0],
				text
			};
		}
	}
	br(src) {
		const cap = this.rules.inline.br.exec(src);
		if (cap) return {
			type: "br",
			raw: cap[0]
		};
	}
	del(src) {
		const cap = this.rules.inline.del.exec(src);
		if (cap) return {
			type: "del",
			raw: cap[0],
			text: cap[2],
			tokens: this.lexer.inlineTokens(cap[2])
		};
	}
	autolink(src) {
		const cap = this.rules.inline.autolink.exec(src);
		if (cap) {
			let text, href;
			if (cap[2] === "@") {
				text = cap[1];
				href = "mailto:" + text;
			} else {
				text = cap[1];
				href = text;
			}
			return {
				type: "link",
				raw: cap[0],
				text,
				href,
				tokens: [{
					type: "text",
					raw: text,
					text
				}]
			};
		}
	}
	url(src) {
		let cap;
		if (cap = this.rules.inline.url.exec(src)) {
			let text, href;
			if (cap[2] === "@") {
				text = cap[0];
				href = "mailto:" + text;
			} else {
				let prevCapZero;
				do {
					prevCapZero = cap[0];
					cap[0] = this.rules.inline._backpedal.exec(cap[0])?.[0] ?? "";
				} while (prevCapZero !== cap[0]);
				text = cap[0];
				if (cap[1] === "www.") href = "http://" + cap[0];
				else href = cap[0];
			}
			return {
				type: "link",
				raw: cap[0],
				text,
				href,
				tokens: [{
					type: "text",
					raw: text,
					text
				}]
			};
		}
	}
	inlineText(src) {
		const cap = this.rules.inline.text.exec(src);
		if (cap) {
			const escaped = this.lexer.state.inRawBlock;
			return {
				type: "text",
				raw: cap[0],
				text: cap[0],
				escaped
			};
		}
	}
};
var _Lexer = class __Lexer {
	tokens;
	options;
	state;
	tokenizer;
	inlineQueue;
	constructor(options2) {
		this.tokens = [];
		this.tokens.links = /* @__PURE__ */ Object.create(null);
		this.options = options2 || _defaults;
		this.options.tokenizer = this.options.tokenizer || new _Tokenizer();
		this.tokenizer = this.options.tokenizer;
		this.tokenizer.options = this.options;
		this.tokenizer.lexer = this;
		this.inlineQueue = [];
		this.state = {
			inLink: false,
			inRawBlock: false,
			top: true
		};
		const rules = {
			other,
			block: block.normal,
			inline: inline.normal
		};
		if (this.options.pedantic) {
			rules.block = block.pedantic;
			rules.inline = inline.pedantic;
		} else if (this.options.gfm) {
			rules.block = block.gfm;
			if (this.options.breaks) rules.inline = inline.breaks;
			else rules.inline = inline.gfm;
		}
		this.tokenizer.rules = rules;
	}
	/**
	* Expose Rules
	*/
	static get rules() {
		return {
			block,
			inline
		};
	}
	/**
	* Static Lex Method
	*/
	static lex(src, options2) {
		return new __Lexer(options2).lex(src);
	}
	/**
	* Static Lex Inline Method
	*/
	static lexInline(src, options2) {
		return new __Lexer(options2).inlineTokens(src);
	}
	/**
	* Preprocessing
	*/
	lex(src) {
		src = src.replace(other.carriageReturn, "\n");
		this.blockTokens(src, this.tokens);
		for (let i = 0; i < this.inlineQueue.length; i++) {
			const next = this.inlineQueue[i];
			this.inlineTokens(next.src, next.tokens);
		}
		this.inlineQueue = [];
		return this.tokens;
	}
	blockTokens(src, tokens = [], lastParagraphClipped = false) {
		if (this.options.pedantic) src = src.replace(other.tabCharGlobal, "    ").replace(other.spaceLine, "");
		while (src) {
			let token;
			if (this.options.extensions?.block?.some((extTokenizer) => {
				if (token = extTokenizer.call({ lexer: this }, src, tokens)) {
					src = src.substring(token.raw.length);
					tokens.push(token);
					return true;
				}
				return false;
			})) continue;
			if (token = this.tokenizer.space(src)) {
				src = src.substring(token.raw.length);
				const lastToken = tokens.at(-1);
				if (token.raw.length === 1 && lastToken !== void 0) lastToken.raw += "\n";
				else tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.code(src)) {
				src = src.substring(token.raw.length);
				const lastToken = tokens.at(-1);
				if (lastToken?.type === "paragraph" || lastToken?.type === "text") {
					lastToken.raw += "\n" + token.raw;
					lastToken.text += "\n" + token.text;
					this.inlineQueue.at(-1).src = lastToken.text;
				} else tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.fences(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.heading(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.hr(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.blockquote(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.list(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.html(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.def(src)) {
				src = src.substring(token.raw.length);
				const lastToken = tokens.at(-1);
				if (lastToken?.type === "paragraph" || lastToken?.type === "text") {
					lastToken.raw += "\n" + token.raw;
					lastToken.text += "\n" + token.raw;
					this.inlineQueue.at(-1).src = lastToken.text;
				} else if (!this.tokens.links[token.tag]) this.tokens.links[token.tag] = {
					href: token.href,
					title: token.title
				};
				continue;
			}
			if (token = this.tokenizer.table(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.lheading(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			let cutSrc = src;
			if (this.options.extensions?.startBlock) {
				let startIndex = Infinity;
				const tempSrc = src.slice(1);
				let tempStart;
				this.options.extensions.startBlock.forEach((getStartIndex) => {
					tempStart = getStartIndex.call({ lexer: this }, tempSrc);
					if (typeof tempStart === "number" && tempStart >= 0) startIndex = Math.min(startIndex, tempStart);
				});
				if (startIndex < Infinity && startIndex >= 0) cutSrc = src.substring(0, startIndex + 1);
			}
			if (this.state.top && (token = this.tokenizer.paragraph(cutSrc))) {
				const lastToken = tokens.at(-1);
				if (lastParagraphClipped && lastToken?.type === "paragraph") {
					lastToken.raw += "\n" + token.raw;
					lastToken.text += "\n" + token.text;
					this.inlineQueue.pop();
					this.inlineQueue.at(-1).src = lastToken.text;
				} else tokens.push(token);
				lastParagraphClipped = cutSrc.length !== src.length;
				src = src.substring(token.raw.length);
				continue;
			}
			if (token = this.tokenizer.text(src)) {
				src = src.substring(token.raw.length);
				const lastToken = tokens.at(-1);
				if (lastToken?.type === "text") {
					lastToken.raw += "\n" + token.raw;
					lastToken.text += "\n" + token.text;
					this.inlineQueue.pop();
					this.inlineQueue.at(-1).src = lastToken.text;
				} else tokens.push(token);
				continue;
			}
			if (src) {
				const errMsg = "Infinite loop on byte: " + src.charCodeAt(0);
				if (this.options.silent) {
					console.error(errMsg);
					break;
				} else throw new Error(errMsg);
			}
		}
		this.state.top = true;
		return tokens;
	}
	inline(src, tokens = []) {
		this.inlineQueue.push({
			src,
			tokens
		});
		return tokens;
	}
	/**
	* Lexing/Compiling
	*/
	inlineTokens(src, tokens = []) {
		let maskedSrc = src;
		let match = null;
		if (this.tokens.links) {
			const links = Object.keys(this.tokens.links);
			if (links.length > 0) {
				while ((match = this.tokenizer.rules.inline.reflinkSearch.exec(maskedSrc)) != null) if (links.includes(match[0].slice(match[0].lastIndexOf("[") + 1, -1))) maskedSrc = maskedSrc.slice(0, match.index) + "[" + "a".repeat(match[0].length - 2) + "]" + maskedSrc.slice(this.tokenizer.rules.inline.reflinkSearch.lastIndex);
			}
		}
		while ((match = this.tokenizer.rules.inline.anyPunctuation.exec(maskedSrc)) != null) maskedSrc = maskedSrc.slice(0, match.index) + "++" + maskedSrc.slice(this.tokenizer.rules.inline.anyPunctuation.lastIndex);
		while ((match = this.tokenizer.rules.inline.blockSkip.exec(maskedSrc)) != null) maskedSrc = maskedSrc.slice(0, match.index) + "[" + "a".repeat(match[0].length - 2) + "]" + maskedSrc.slice(this.tokenizer.rules.inline.blockSkip.lastIndex);
		let keepPrevChar = false;
		let prevChar = "";
		while (src) {
			if (!keepPrevChar) prevChar = "";
			keepPrevChar = false;
			let token;
			if (this.options.extensions?.inline?.some((extTokenizer) => {
				if (token = extTokenizer.call({ lexer: this }, src, tokens)) {
					src = src.substring(token.raw.length);
					tokens.push(token);
					return true;
				}
				return false;
			})) continue;
			if (token = this.tokenizer.escape(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.tag(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.link(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.reflink(src, this.tokens.links)) {
				src = src.substring(token.raw.length);
				const lastToken = tokens.at(-1);
				if (token.type === "text" && lastToken?.type === "text") {
					lastToken.raw += token.raw;
					lastToken.text += token.text;
				} else tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.emStrong(src, maskedSrc, prevChar)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.codespan(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.br(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.del(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (token = this.tokenizer.autolink(src)) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			if (!this.state.inLink && (token = this.tokenizer.url(src))) {
				src = src.substring(token.raw.length);
				tokens.push(token);
				continue;
			}
			let cutSrc = src;
			if (this.options.extensions?.startInline) {
				let startIndex = Infinity;
				const tempSrc = src.slice(1);
				let tempStart;
				this.options.extensions.startInline.forEach((getStartIndex) => {
					tempStart = getStartIndex.call({ lexer: this }, tempSrc);
					if (typeof tempStart === "number" && tempStart >= 0) startIndex = Math.min(startIndex, tempStart);
				});
				if (startIndex < Infinity && startIndex >= 0) cutSrc = src.substring(0, startIndex + 1);
			}
			if (token = this.tokenizer.inlineText(cutSrc)) {
				src = src.substring(token.raw.length);
				if (token.raw.slice(-1) !== "_") prevChar = token.raw.slice(-1);
				keepPrevChar = true;
				const lastToken = tokens.at(-1);
				if (lastToken?.type === "text") {
					lastToken.raw += token.raw;
					lastToken.text += token.text;
				} else tokens.push(token);
				continue;
			}
			if (src) {
				const errMsg = "Infinite loop on byte: " + src.charCodeAt(0);
				if (this.options.silent) {
					console.error(errMsg);
					break;
				} else throw new Error(errMsg);
			}
		}
		return tokens;
	}
};
var _Renderer = class {
	options;
	parser;
	constructor(options2) {
		this.options = options2 || _defaults;
	}
	space(token) {
		return "";
	}
	code({ text, lang, escaped }) {
		const langString = (lang || "").match(other.notSpaceStart)?.[0];
		const code = text.replace(other.endingNewline, "") + "\n";
		if (!langString) return "<pre><code>" + (escaped ? code : escape2(code, true)) + "</code></pre>\n";
		return "<pre><code class=\"language-" + escape2(langString) + "\">" + (escaped ? code : escape2(code, true)) + "</code></pre>\n";
	}
	blockquote({ tokens }) {
		return `<blockquote>
${this.parser.parse(tokens)}</blockquote>
`;
	}
	html({ text }) {
		return text;
	}
	heading({ tokens, depth }) {
		return `<h${depth}>${this.parser.parseInline(tokens)}</h${depth}>
`;
	}
	hr(token) {
		return "<hr>\n";
	}
	list(token) {
		const ordered = token.ordered;
		const start = token.start;
		let body = "";
		for (let j = 0; j < token.items.length; j++) {
			const item = token.items[j];
			body += this.listitem(item);
		}
		const type = ordered ? "ol" : "ul";
		const startAttr = ordered && start !== 1 ? " start=\"" + start + "\"" : "";
		return "<" + type + startAttr + ">\n" + body + "</" + type + ">\n";
	}
	listitem(item) {
		let itemBody = "";
		if (item.task) {
			const checkbox = this.checkbox({ checked: !!item.checked });
			if (item.loose) if (item.tokens[0]?.type === "paragraph") {
				item.tokens[0].text = checkbox + " " + item.tokens[0].text;
				if (item.tokens[0].tokens && item.tokens[0].tokens.length > 0 && item.tokens[0].tokens[0].type === "text") {
					item.tokens[0].tokens[0].text = checkbox + " " + escape2(item.tokens[0].tokens[0].text);
					item.tokens[0].tokens[0].escaped = true;
				}
			} else item.tokens.unshift({
				type: "text",
				raw: checkbox + " ",
				text: checkbox + " ",
				escaped: true
			});
			else itemBody += checkbox + " ";
		}
		itemBody += this.parser.parse(item.tokens, !!item.loose);
		return `<li>${itemBody}</li>
`;
	}
	checkbox({ checked }) {
		return "<input " + (checked ? "checked=\"\" " : "") + "disabled=\"\" type=\"checkbox\">";
	}
	paragraph({ tokens }) {
		return `<p>${this.parser.parseInline(tokens)}</p>
`;
	}
	table(token) {
		let header = "";
		let cell = "";
		for (let j = 0; j < token.header.length; j++) cell += this.tablecell(token.header[j]);
		header += this.tablerow({ text: cell });
		let body = "";
		for (let j = 0; j < token.rows.length; j++) {
			const row = token.rows[j];
			cell = "";
			for (let k = 0; k < row.length; k++) cell += this.tablecell(row[k]);
			body += this.tablerow({ text: cell });
		}
		if (body) body = `<tbody>${body}</tbody>`;
		return "<table>\n<thead>\n" + header + "</thead>\n" + body + "</table>\n";
	}
	tablerow({ text }) {
		return `<tr>
${text}</tr>
`;
	}
	tablecell(token) {
		const content = this.parser.parseInline(token.tokens);
		const type = token.header ? "th" : "td";
		return (token.align ? `<${type} align="${token.align}">` : `<${type}>`) + content + `</${type}>
`;
	}
	/**
	* span level renderer
	*/
	strong({ tokens }) {
		return `<strong>${this.parser.parseInline(tokens)}</strong>`;
	}
	em({ tokens }) {
		return `<em>${this.parser.parseInline(tokens)}</em>`;
	}
	codespan({ text }) {
		return `<code>${escape2(text, true)}</code>`;
	}
	br(token) {
		return "<br>";
	}
	del({ tokens }) {
		return `<del>${this.parser.parseInline(tokens)}</del>`;
	}
	link({ href, title, tokens }) {
		const text = this.parser.parseInline(tokens);
		const cleanHref = cleanUrl(href);
		if (cleanHref === null) return text;
		href = cleanHref;
		let out = "<a href=\"" + href + "\"";
		if (title) out += " title=\"" + escape2(title) + "\"";
		out += ">" + text + "</a>";
		return out;
	}
	image({ href, title, text, tokens }) {
		if (tokens) text = this.parser.parseInline(tokens, this.parser.textRenderer);
		const cleanHref = cleanUrl(href);
		if (cleanHref === null) return escape2(text);
		href = cleanHref;
		let out = `<img src="${href}" alt="${text}"`;
		if (title) out += ` title="${escape2(title)}"`;
		out += ">";
		return out;
	}
	text(token) {
		return "tokens" in token && token.tokens ? this.parser.parseInline(token.tokens) : "escaped" in token && token.escaped ? token.text : escape2(token.text);
	}
};
var _TextRenderer = class {
	strong({ text }) {
		return text;
	}
	em({ text }) {
		return text;
	}
	codespan({ text }) {
		return text;
	}
	del({ text }) {
		return text;
	}
	html({ text }) {
		return text;
	}
	text({ text }) {
		return text;
	}
	link({ text }) {
		return "" + text;
	}
	image({ text }) {
		return "" + text;
	}
	br() {
		return "";
	}
};
var _Parser = class __Parser {
	options;
	renderer;
	textRenderer;
	constructor(options2) {
		this.options = options2 || _defaults;
		this.options.renderer = this.options.renderer || new _Renderer();
		this.renderer = this.options.renderer;
		this.renderer.options = this.options;
		this.renderer.parser = this;
		this.textRenderer = new _TextRenderer();
	}
	/**
	* Static Parse Method
	*/
	static parse(tokens, options2) {
		return new __Parser(options2).parse(tokens);
	}
	/**
	* Static Parse Inline Method
	*/
	static parseInline(tokens, options2) {
		return new __Parser(options2).parseInline(tokens);
	}
	/**
	* Parse Loop
	*/
	parse(tokens, top = true) {
		let out = "";
		for (let i = 0; i < tokens.length; i++) {
			const anyToken = tokens[i];
			if (this.options.extensions?.renderers?.[anyToken.type]) {
				const genericToken = anyToken;
				const ret = this.options.extensions.renderers[genericToken.type].call({ parser: this }, genericToken);
				if (ret !== false || ![
					"space",
					"hr",
					"heading",
					"code",
					"table",
					"blockquote",
					"list",
					"html",
					"paragraph",
					"text"
				].includes(genericToken.type)) {
					out += ret || "";
					continue;
				}
			}
			const token = anyToken;
			switch (token.type) {
				case "space":
					out += this.renderer.space(token);
					continue;
				case "hr":
					out += this.renderer.hr(token);
					continue;
				case "heading":
					out += this.renderer.heading(token);
					continue;
				case "code":
					out += this.renderer.code(token);
					continue;
				case "table":
					out += this.renderer.table(token);
					continue;
				case "blockquote":
					out += this.renderer.blockquote(token);
					continue;
				case "list":
					out += this.renderer.list(token);
					continue;
				case "html":
					out += this.renderer.html(token);
					continue;
				case "paragraph":
					out += this.renderer.paragraph(token);
					continue;
				case "text": {
					let textToken = token;
					let body = this.renderer.text(textToken);
					while (i + 1 < tokens.length && tokens[i + 1].type === "text") {
						textToken = tokens[++i];
						body += "\n" + this.renderer.text(textToken);
					}
					if (top) out += this.renderer.paragraph({
						type: "paragraph",
						raw: body,
						text: body,
						tokens: [{
							type: "text",
							raw: body,
							text: body,
							escaped: true
						}]
					});
					else out += body;
					continue;
				}
				default: {
					const errMsg = "Token with \"" + token.type + "\" type was not found.";
					if (this.options.silent) {
						console.error(errMsg);
						return "";
					} else throw new Error(errMsg);
				}
			}
		}
		return out;
	}
	/**
	* Parse Inline Tokens
	*/
	parseInline(tokens, renderer = this.renderer) {
		let out = "";
		for (let i = 0; i < tokens.length; i++) {
			const anyToken = tokens[i];
			if (this.options.extensions?.renderers?.[anyToken.type]) {
				const ret = this.options.extensions.renderers[anyToken.type].call({ parser: this }, anyToken);
				if (ret !== false || ![
					"escape",
					"html",
					"link",
					"image",
					"strong",
					"em",
					"codespan",
					"br",
					"del",
					"text"
				].includes(anyToken.type)) {
					out += ret || "";
					continue;
				}
			}
			const token = anyToken;
			switch (token.type) {
				case "escape":
					out += renderer.text(token);
					break;
				case "html":
					out += renderer.html(token);
					break;
				case "link":
					out += renderer.link(token);
					break;
				case "image":
					out += renderer.image(token);
					break;
				case "strong":
					out += renderer.strong(token);
					break;
				case "em":
					out += renderer.em(token);
					break;
				case "codespan":
					out += renderer.codespan(token);
					break;
				case "br":
					out += renderer.br(token);
					break;
				case "del":
					out += renderer.del(token);
					break;
				case "text":
					out += renderer.text(token);
					break;
				default: {
					const errMsg = "Token with \"" + token.type + "\" type was not found.";
					if (this.options.silent) {
						console.error(errMsg);
						return "";
					} else throw new Error(errMsg);
				}
			}
		}
		return out;
	}
};
var _Hooks = class {
	options;
	block;
	constructor(options2) {
		this.options = options2 || _defaults;
	}
	static passThroughHooks = /* @__PURE__ */ new Set([
		"preprocess",
		"postprocess",
		"processAllTokens"
	]);
	/**
	* Process markdown before marked
	*/
	preprocess(markdown) {
		return markdown;
	}
	/**
	* Process HTML after marked is finished
	*/
	postprocess(html2) {
		return html2;
	}
	/**
	* Process all tokens before walk tokens
	*/
	processAllTokens(tokens) {
		return tokens;
	}
	/**
	* Provide function to tokenize markdown
	*/
	provideLexer() {
		return this.block ? _Lexer.lex : _Lexer.lexInline;
	}
	/**
	* Provide function to parse tokens
	*/
	provideParser() {
		return this.block ? _Parser.parse : _Parser.parseInline;
	}
};
var Marked = class {
	defaults = _getDefaults();
	options = this.setOptions;
	parse = this.parseMarkdown(true);
	parseInline = this.parseMarkdown(false);
	Parser = _Parser;
	Renderer = _Renderer;
	TextRenderer = _TextRenderer;
	Lexer = _Lexer;
	Tokenizer = _Tokenizer;
	Hooks = _Hooks;
	constructor(...args) {
		this.use(...args);
	}
	/**
	* Run callback for every token
	*/
	walkTokens(tokens, callback) {
		let values = [];
		for (const token of tokens) {
			values = values.concat(callback.call(this, token));
			switch (token.type) {
				case "table": {
					const tableToken = token;
					for (const cell of tableToken.header) values = values.concat(this.walkTokens(cell.tokens, callback));
					for (const row of tableToken.rows) for (const cell of row) values = values.concat(this.walkTokens(cell.tokens, callback));
					break;
				}
				case "list": {
					const listToken = token;
					values = values.concat(this.walkTokens(listToken.items, callback));
					break;
				}
				default: {
					const genericToken = token;
					if (this.defaults.extensions?.childTokens?.[genericToken.type]) this.defaults.extensions.childTokens[genericToken.type].forEach((childTokens) => {
						const tokens2 = genericToken[childTokens].flat(Infinity);
						values = values.concat(this.walkTokens(tokens2, callback));
					});
					else if (genericToken.tokens) values = values.concat(this.walkTokens(genericToken.tokens, callback));
				}
			}
		}
		return values;
	}
	use(...args) {
		const extensions = this.defaults.extensions || {
			renderers: {},
			childTokens: {}
		};
		args.forEach((pack) => {
			const opts = { ...pack };
			opts.async = this.defaults.async || opts.async || false;
			if (pack.extensions) {
				pack.extensions.forEach((ext) => {
					if (!ext.name) throw new Error("extension name required");
					if ("renderer" in ext) {
						const prevRenderer = extensions.renderers[ext.name];
						if (prevRenderer) extensions.renderers[ext.name] = function(...args2) {
							let ret = ext.renderer.apply(this, args2);
							if (ret === false) ret = prevRenderer.apply(this, args2);
							return ret;
						};
						else extensions.renderers[ext.name] = ext.renderer;
					}
					if ("tokenizer" in ext) {
						if (!ext.level || ext.level !== "block" && ext.level !== "inline") throw new Error("extension level must be 'block' or 'inline'");
						const extLevel = extensions[ext.level];
						if (extLevel) extLevel.unshift(ext.tokenizer);
						else extensions[ext.level] = [ext.tokenizer];
						if (ext.start) {
							if (ext.level === "block") if (extensions.startBlock) extensions.startBlock.push(ext.start);
							else extensions.startBlock = [ext.start];
							else if (ext.level === "inline") if (extensions.startInline) extensions.startInline.push(ext.start);
							else extensions.startInline = [ext.start];
						}
					}
					if ("childTokens" in ext && ext.childTokens) extensions.childTokens[ext.name] = ext.childTokens;
				});
				opts.extensions = extensions;
			}
			if (pack.renderer) {
				const renderer = this.defaults.renderer || new _Renderer(this.defaults);
				for (const prop in pack.renderer) {
					if (!(prop in renderer)) throw new Error(`renderer '${prop}' does not exist`);
					if (["options", "parser"].includes(prop)) continue;
					const rendererProp = prop;
					const rendererFunc = pack.renderer[rendererProp];
					const prevRenderer = renderer[rendererProp];
					renderer[rendererProp] = (...args2) => {
						let ret = rendererFunc.apply(renderer, args2);
						if (ret === false) ret = prevRenderer.apply(renderer, args2);
						return ret || "";
					};
				}
				opts.renderer = renderer;
			}
			if (pack.tokenizer) {
				const tokenizer = this.defaults.tokenizer || new _Tokenizer(this.defaults);
				for (const prop in pack.tokenizer) {
					if (!(prop in tokenizer)) throw new Error(`tokenizer '${prop}' does not exist`);
					if ([
						"options",
						"rules",
						"lexer"
					].includes(prop)) continue;
					const tokenizerProp = prop;
					const tokenizerFunc = pack.tokenizer[tokenizerProp];
					const prevTokenizer = tokenizer[tokenizerProp];
					tokenizer[tokenizerProp] = (...args2) => {
						let ret = tokenizerFunc.apply(tokenizer, args2);
						if (ret === false) ret = prevTokenizer.apply(tokenizer, args2);
						return ret;
					};
				}
				opts.tokenizer = tokenizer;
			}
			if (pack.hooks) {
				const hooks = this.defaults.hooks || new _Hooks();
				for (const prop in pack.hooks) {
					if (!(prop in hooks)) throw new Error(`hook '${prop}' does not exist`);
					if (["options", "block"].includes(prop)) continue;
					const hooksProp = prop;
					const hooksFunc = pack.hooks[hooksProp];
					const prevHook = hooks[hooksProp];
					if (_Hooks.passThroughHooks.has(prop)) hooks[hooksProp] = (arg) => {
						if (this.defaults.async) return Promise.resolve(hooksFunc.call(hooks, arg)).then((ret2) => {
							return prevHook.call(hooks, ret2);
						});
						const ret = hooksFunc.call(hooks, arg);
						return prevHook.call(hooks, ret);
					};
					else hooks[hooksProp] = (...args2) => {
						let ret = hooksFunc.apply(hooks, args2);
						if (ret === false) ret = prevHook.apply(hooks, args2);
						return ret;
					};
				}
				opts.hooks = hooks;
			}
			if (pack.walkTokens) {
				const walkTokens2 = this.defaults.walkTokens;
				const packWalktokens = pack.walkTokens;
				opts.walkTokens = function(token) {
					let values = [];
					values.push(packWalktokens.call(this, token));
					if (walkTokens2) values = values.concat(walkTokens2.call(this, token));
					return values;
				};
			}
			this.defaults = {
				...this.defaults,
				...opts
			};
		});
		return this;
	}
	setOptions(opt) {
		this.defaults = {
			...this.defaults,
			...opt
		};
		return this;
	}
	lexer(src, options2) {
		return _Lexer.lex(src, options2 ?? this.defaults);
	}
	parser(tokens, options2) {
		return _Parser.parse(tokens, options2 ?? this.defaults);
	}
	parseMarkdown(blockType) {
		const parse2 = (src, options2) => {
			const origOpt = { ...options2 };
			const opt = {
				...this.defaults,
				...origOpt
			};
			const throwError = this.onError(!!opt.silent, !!opt.async);
			if (this.defaults.async === true && origOpt.async === false) return throwError(/* @__PURE__ */ new Error("marked(): The async option was set to true by an extension. Remove async: false from the parse options object to return a Promise."));
			if (typeof src === "undefined" || src === null) return throwError(/* @__PURE__ */ new Error("marked(): input parameter is undefined or null"));
			if (typeof src !== "string") return throwError(/* @__PURE__ */ new Error("marked(): input parameter is of type " + Object.prototype.toString.call(src) + ", string expected"));
			if (opt.hooks) {
				opt.hooks.options = opt;
				opt.hooks.block = blockType;
			}
			const lexer2 = opt.hooks ? opt.hooks.provideLexer() : blockType ? _Lexer.lex : _Lexer.lexInline;
			const parser2 = opt.hooks ? opt.hooks.provideParser() : blockType ? _Parser.parse : _Parser.parseInline;
			if (opt.async) return Promise.resolve(opt.hooks ? opt.hooks.preprocess(src) : src).then((src2) => lexer2(src2, opt)).then((tokens) => opt.hooks ? opt.hooks.processAllTokens(tokens) : tokens).then((tokens) => opt.walkTokens ? Promise.all(this.walkTokens(tokens, opt.walkTokens)).then(() => tokens) : tokens).then((tokens) => parser2(tokens, opt)).then((html2) => opt.hooks ? opt.hooks.postprocess(html2) : html2).catch(throwError);
			try {
				if (opt.hooks) src = opt.hooks.preprocess(src);
				let tokens = lexer2(src, opt);
				if (opt.hooks) tokens = opt.hooks.processAllTokens(tokens);
				if (opt.walkTokens) this.walkTokens(tokens, opt.walkTokens);
				let html2 = parser2(tokens, opt);
				if (opt.hooks) html2 = opt.hooks.postprocess(html2);
				return html2;
			} catch (e) {
				return throwError(e);
			}
		};
		return parse2;
	}
	onError(silent, async) {
		return (e) => {
			e.message += "\nPlease report this to https://github.com/markedjs/marked.";
			if (silent) {
				const msg = "<p>An error occurred:</p><pre>" + escape2(e.message + "", true) + "</pre>";
				if (async) return Promise.resolve(msg);
				return msg;
			}
			if (async) return Promise.reject(e);
			throw e;
		};
	}
};
var markedInstance = new Marked();
function marked(src, opt) {
	return markedInstance.parse(src, opt);
}
marked.options = marked.setOptions = function(options2) {
	markedInstance.setOptions(options2);
	marked.defaults = markedInstance.defaults;
	changeDefaults(marked.defaults);
	return marked;
};
marked.getDefaults = _getDefaults;
marked.defaults = _defaults;
marked.use = function(...args) {
	markedInstance.use(...args);
	marked.defaults = markedInstance.defaults;
	changeDefaults(marked.defaults);
	return marked;
};
marked.walkTokens = function(tokens, callback) {
	return markedInstance.walkTokens(tokens, callback);
};
marked.parseInline = markedInstance.parseInline;
marked.Parser = _Parser;
marked.parser = _Parser.parse;
marked.Renderer = _Renderer;
marked.TextRenderer = _TextRenderer;
marked.Lexer = _Lexer;
marked.lexer = _Lexer.lex;
marked.Tokenizer = _Tokenizer;
marked.Hooks = _Hooks;
marked.parse = marked;
marked.options;
marked.setOptions;
marked.use;
marked.walkTokens;
marked.parseInline;
_Parser.parse;
_Lexer.lex;
//#endregion
//#region resources/js/util/markdown.js
function markdown_default(markdown, options = {}) {
	if (!markdown) return "";
	const renderer = new marked.Renderer();
	if (options.openLinksInNewTabs) renderer.link = function(href, title, text) {
		return marked.Renderer.prototype.link.call(this, href, title, text).replace("<a", "<a target='_blank' ");
	};
	return purify.sanitize(marked.parse(markdown, { renderer }), { ADD_ATTR: ["target"] });
}
//#endregion
//#region resources/js/bootstrap/globals.js
var globals_exports = /* @__PURE__ */ __exportAll({
	__: () => __,
	__n: () => __n,
	clone: () => clone,
	cp_url: () => cp_url,
	data_get: () => data_get,
	data_set: () => data_set,
	dd: () => dd,
	docs_url: () => docs_url,
	escapeHtml: () => escapeHtml,
	field_width_class: () => field_width_class,
	markdown: () => markdown,
	relative_url: () => relative_url,
	replicatorPreviewHtml: () => replicatorPreviewHtml,
	resource_url: () => resource_url,
	snake_case: () => snake_case,
	str_slug: () => str_slug,
	tailwind_width_class: () => tailwind_width_class,
	tidy_url: () => tidy_url,
	truncate: () => truncate,
	uniqid: () => uniqid,
	utf8atob: () => utf8atob,
	utf8btoa: () => utf8btoa
});
function cp_url(url) {
	url = Statamic.$config.get("cpUrl") + "/" + url;
	return tidy_url(url);
}
function docs_url(url) {
	return tidy_url("https://statamic.dev/" + url);
}
function resource_url(url) {
	url = Statamic.$config.get("resourceUrl") + "/" + url;
	return tidy_url(url);
}
function tidy_url(url) {
	return url.replace(/([^:])(\/\/+)/g, "$1/");
}
function relative_url(url) {
	return url.replace(/^(?:\/\/|[^/]+)*\//, "/");
}
function dd(args) {
	console.log(args);
}
function data_get(obj, path, fallback = null) {
	var value = (Array.isArray(path) ? path : path.split(".")).reduce((prev, curr) => prev && prev[curr], obj);
	return value !== void 0 ? value : fallback;
}
function data_set(obj, path, value) {
	var parts = path.split(".");
	while (parts.length - 1) {
		var key = parts.shift();
		var shouldBeArray = parts.length ? (/* @__PURE__ */ new RegExp("^[0-9]+$")).test(parts[0]) : false;
		if (!(key in obj)) obj[key] = shouldBeArray ? [] : {};
		obj = obj[key];
	}
	obj[parts[0]] = value;
}
function clone(value) {
	if (value === void 0) return void 0;
	return JSON.parse(JSON.stringify(value));
}
function tailwind_width_class(width) {
	return `${{
		25: "w-full @lg:w-1/4",
		33: "w-full @lg:w-1/3",
		50: "w-full @lg:w-1/2",
		66: "w-full @lg:w-2/3",
		75: "w-full @lg:w-3/4",
		100: "w-full"
	}[width] || "w-full"}`;
}
function field_width_class(width) {
	return `${{
		25: "field-w-25",
		33: "field-w-33",
		50: "field-w-50",
		66: "field-w-66",
		75: "field-w-75",
		100: "field-w-100"
	}[width] || "field-w-100"}`;
}
function markdown(value, options = {}) {
	return markdown_default(value, options);
}
function __(string, replacements) {
	return translate(string, replacements);
}
function __n(string, number, replacements) {
	return translateChoice(string, number, replacements);
}
function utf8btoa(stringToEncode) {
	const utf8String = encodeURIComponent(stringToEncode).replace(/%([0-9A-F]{2})/g, (_, code) => String.fromCharCode(`0x${code}`));
	return btoa(utf8String);
}
function utf8atob(stringToDecode) {
	const utf8String = atob(stringToDecode);
	return decodeURIComponent(utf8String.split("").map((c) => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2)).join(""));
}
function uniqid() {
	return nanoid();
}
function truncate(string, length, ending = "...") {
	if (string.length <= length) return string;
	return string.substring(0, length - ending.length) + ending;
}
function escapeHtml(string) {
	return string.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll("\"", "&quot;").replaceAll("'", "&#039;");
}
function replicatorPreviewHtml(html) {
	return new PreviewHtml(html);
}
function str_slug(string) {
	return Statamic.$slug.create(string);
}
function snake_case(string) {
	return Statamic.$slug.separatedBy("_").create(string);
}
//#endregion
export { data_set as a, markdown_default as c, PreviewHtml as d, setLocale as f, purify as g, nanoid as h, data_get as i, _Renderer as l, translate as m, clone as n, escapeHtml as o, setTranslations as p, cp_url as r, globals_exports as s, __ as t, marked as u };
