import { n as __exportAll, r as __toESM, t as __commonJSMin } from "./chunk-B-1-B7_t.js";
import { F as onBeforeUnmount, H as provide, L as onMounted, O as h, R as onUnmounted, T as defineComponent, _t as ref, et as watch, f as Fragment, h as computed, ht as reactive, i as createSSRApp, pt as markRaw, yt as shallowRef } from "./vue.esm-bundler-BbHU-fTn.js";
//#region node_modules/axios/lib/helpers/bind.js
/**
* Create a bound version of a function with a specified `this` context
*
* @param {Function} fn - The function to bind
* @param {*} thisArg - The value to be passed as the `this` parameter
* @returns {Function} A new function that will call the original function with the specified `this` context
*/
function bind(fn, thisArg) {
	return function wrap() {
		return fn.apply(thisArg, arguments);
	};
}
//#endregion
//#region node_modules/axios/lib/utils.js
var { toString: toString$1 } = Object.prototype;
var { getPrototypeOf } = Object;
var { iterator, toStringTag } = Symbol;
var kindOf = ((cache) => (thing) => {
	const str = toString$1.call(thing);
	return cache[str] || (cache[str] = str.slice(8, -1).toLowerCase());
})(Object.create(null));
var kindOfTest = (type) => {
	type = type.toLowerCase();
	return (thing) => kindOf(thing) === type;
};
var typeOfTest = (type) => (thing) => typeof thing === type;
/**
* Determine if a value is a non-null object
*
* @param {Object} val The value to test
*
* @returns {boolean} True if value is an Array, otherwise false
*/
var { isArray: isArray$1 } = Array;
/**
* Determine if a value is undefined
*
* @param {*} val The value to test
*
* @returns {boolean} True if the value is undefined, otherwise false
*/
var isUndefined = typeOfTest("undefined");
/**
* Determine if a value is a Buffer
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a Buffer, otherwise false
*/
function isBuffer$1(val) {
	return val !== null && !isUndefined(val) && val.constructor !== null && !isUndefined(val.constructor) && isFunction$2(val.constructor.isBuffer) && val.constructor.isBuffer(val);
}
/**
* Determine if a value is an ArrayBuffer
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is an ArrayBuffer, otherwise false
*/
var isArrayBuffer = kindOfTest("ArrayBuffer");
/**
* Determine if a value is a view on an ArrayBuffer
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a view on an ArrayBuffer, otherwise false
*/
function isArrayBufferView(val) {
	let result;
	if (typeof ArrayBuffer !== "undefined" && ArrayBuffer.isView) result = ArrayBuffer.isView(val);
	else result = val && val.buffer && isArrayBuffer(val.buffer);
	return result;
}
/**
* Determine if a value is a String
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a String, otherwise false
*/
var isString = typeOfTest("string");
/**
* Determine if a value is a Function
*
* @param {*} val The value to test
* @returns {boolean} True if value is a Function, otherwise false
*/
var isFunction$2 = typeOfTest("function");
/**
* Determine if a value is a Number
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a Number, otherwise false
*/
var isNumber = typeOfTest("number");
/**
* Determine if a value is an Object
*
* @param {*} thing The value to test
*
* @returns {boolean} True if value is an Object, otherwise false
*/
var isObject$1 = (thing) => thing !== null && typeof thing === "object";
/**
* Determine if a value is a Boolean
*
* @param {*} thing The value to test
* @returns {boolean} True if value is a Boolean, otherwise false
*/
var isBoolean = (thing) => thing === true || thing === false;
/**
* Determine if a value is a plain Object
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a plain Object, otherwise false
*/
var isPlainObject$1 = (val) => {
	if (kindOf(val) !== "object") return false;
	const prototype = getPrototypeOf(val);
	return (prototype === null || prototype === Object.prototype || Object.getPrototypeOf(prototype) === null) && !(toStringTag in val) && !(iterator in val);
};
/**
* Determine if a value is an empty object (safely handles Buffers)
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is an empty object, otherwise false
*/
var isEmptyObject = (val) => {
	if (!isObject$1(val) || isBuffer$1(val)) return false;
	try {
		return Object.keys(val).length === 0 && Object.getPrototypeOf(val) === Object.prototype;
	} catch (e) {
		return false;
	}
};
/**
* Determine if a value is a Date
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a Date, otherwise false
*/
var isDate = kindOfTest("Date");
/**
* Determine if a value is a File
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a File, otherwise false
*/
var isFile$2 = kindOfTest("File");
/**
* Determine if a value is a React Native Blob
* React Native "blob": an object with a `uri` attribute. Optionally, it can
* also have a `name` and `type` attribute to specify filename and content type
*
* @see https://github.com/facebook/react-native/blob/26684cf3adf4094eb6c405d345a75bf8c7c0bf88/Libraries/Network/FormData.js#L68-L71
*
* @param {*} value The value to test
*
* @returns {boolean} True if value is a React Native Blob, otherwise false
*/
var isReactNativeBlob = (value) => {
	return !!(value && typeof value.uri !== "undefined");
};
/**
* Determine if environment is React Native
* ReactNative `FormData` has a non-standard `getParts()` method
*
* @param {*} formData The formData to test
*
* @returns {boolean} True if environment is React Native, otherwise false
*/
var isReactNative = (formData) => formData && typeof formData.getParts !== "undefined";
/**
* Determine if a value is a Blob
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a Blob, otherwise false
*/
var isBlob = kindOfTest("Blob");
/**
* Determine if a value is a FileList
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a FileList, otherwise false
*/
var isFileList = kindOfTest("FileList");
/**
* Determine if a value is a Stream
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a Stream, otherwise false
*/
var isStream = (val) => isObject$1(val) && isFunction$2(val.pipe);
/**
* Determine if a value is a FormData
*
* @param {*} thing The value to test
*
* @returns {boolean} True if value is an FormData, otherwise false
*/
function getGlobal() {
	if (typeof globalThis !== "undefined") return globalThis;
	if (typeof self !== "undefined") return self;
	if (typeof window !== "undefined") return window;
	if (typeof global !== "undefined") return global;
	return {};
}
var G = getGlobal();
var FormDataCtor = typeof G.FormData !== "undefined" ? G.FormData : void 0;
var isFormData$1 = (thing) => {
	if (!thing) return false;
	if (FormDataCtor && thing instanceof FormDataCtor) return true;
	const proto = getPrototypeOf(thing);
	if (!proto || proto === Object.prototype) return false;
	if (!isFunction$2(thing.append)) return false;
	const kind = kindOf(thing);
	return kind === "formdata" || kind === "object" && isFunction$2(thing.toString) && thing.toString() === "[object FormData]";
};
/**
* Determine if a value is a URLSearchParams object
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a URLSearchParams object, otherwise false
*/
var isURLSearchParams = kindOfTest("URLSearchParams");
var [isReadableStream, isRequest, isResponse, isHeaders] = [
	"ReadableStream",
	"Request",
	"Response",
	"Headers"
].map(kindOfTest);
/**
* Trim excess whitespace off the beginning and end of a string
*
* @param {String} str The String to trim
*
* @returns {String} The String freed of excess whitespace
*/
var trim = (str) => {
	return str.trim ? str.trim() : str.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");
};
/**
* Iterate over an Array or an Object invoking a function for each item.
*
* If `obj` is an Array callback will be called passing
* the value, index, and complete array for each item.
*
* If 'obj' is an Object callback will be called passing
* the value, key, and complete object for each property.
*
* @param {Object|Array<unknown>} obj The object to iterate
* @param {Function} fn The callback to invoke for each item
*
* @param {Object} [options]
* @param {Boolean} [options.allOwnKeys = false]
* @returns {any}
*/
function forEach(obj, fn, { allOwnKeys = false } = {}) {
	if (obj === null || typeof obj === "undefined") return;
	let i;
	let l;
	if (typeof obj !== "object") obj = [obj];
	if (isArray$1(obj)) for (i = 0, l = obj.length; i < l; i++) fn.call(null, obj[i], i, obj);
	else {
		if (isBuffer$1(obj)) return;
		const keys = allOwnKeys ? Object.getOwnPropertyNames(obj) : Object.keys(obj);
		const len = keys.length;
		let key;
		for (i = 0; i < len; i++) {
			key = keys[i];
			fn.call(null, obj[key], key, obj);
		}
	}
}
/**
* Finds a key in an object, case-insensitive, returning the actual key name.
* Returns null if the object is a Buffer or if no match is found.
*
* @param {Object} obj - The object to search.
* @param {string} key - The key to find (case-insensitive).
* @returns {?string} The actual key name if found, otherwise null.
*/
function findKey(obj, key) {
	if (isBuffer$1(obj)) return null;
	key = key.toLowerCase();
	const keys = Object.keys(obj);
	let i = keys.length;
	let _key;
	while (i-- > 0) {
		_key = keys[i];
		if (key === _key.toLowerCase()) return _key;
	}
	return null;
}
var _global = (() => {
	if (typeof globalThis !== "undefined") return globalThis;
	return typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : global;
})();
var isContextDefined = (context) => !isUndefined(context) && context !== _global;
/**
* Accepts varargs expecting each argument to be an object, then
* immutably merges the properties of each object and returns result.
*
* When multiple objects contain the same key the later object in
* the arguments list will take precedence.
*
* Example:
*
* ```js
* const result = merge({foo: 123}, {foo: 456});
* console.log(result.foo); // outputs 456
* ```
*
* @param {Object} obj1 Object to merge
*
* @returns {Object} Result of all merge properties
*/
function merge$1(...objs) {
	const { caseless, skipUndefined } = isContextDefined(this) && this || {};
	const result = {};
	const assignValue = (val, key) => {
		if (key === "__proto__" || key === "constructor" || key === "prototype") return;
		const targetKey = caseless && findKey(result, key) || key;
		const existing = hasOwnProperty$14(result, targetKey) ? result[targetKey] : void 0;
		if (isPlainObject$1(existing) && isPlainObject$1(val)) result[targetKey] = merge$1(existing, val);
		else if (isPlainObject$1(val)) result[targetKey] = merge$1({}, val);
		else if (isArray$1(val)) result[targetKey] = val.slice();
		else if (!skipUndefined || !isUndefined(val)) result[targetKey] = val;
	};
	for (let i = 0, l = objs.length; i < l; i++) objs[i] && forEach(objs[i], assignValue);
	return result;
}
/**
* Extends object a by mutably adding to it the properties of object b.
*
* @param {Object} a The object to be extended
* @param {Object} b The object to copy properties from
* @param {Object} thisArg The object to bind function to
*
* @param {Object} [options]
* @param {Boolean} [options.allOwnKeys]
* @returns {Object} The resulting value of object a
*/
var extend = (a, b, thisArg, { allOwnKeys } = {}) => {
	forEach(b, (val, key) => {
		if (thisArg && isFunction$2(val)) Object.defineProperty(a, key, {
			__proto__: null,
			value: bind(val, thisArg),
			writable: true,
			enumerable: true,
			configurable: true
		});
		else Object.defineProperty(a, key, {
			__proto__: null,
			value: val,
			writable: true,
			enumerable: true,
			configurable: true
		});
	}, { allOwnKeys });
	return a;
};
/**
* Remove byte order marker. This catches EF BB BF (the UTF-8 BOM)
*
* @param {string} content with BOM
*
* @returns {string} content value without BOM
*/
var stripBOM = (content) => {
	if (content.charCodeAt(0) === 65279) content = content.slice(1);
	return content;
};
/**
* Inherit the prototype methods from one constructor into another
* @param {function} constructor
* @param {function} superConstructor
* @param {object} [props]
* @param {object} [descriptors]
*
* @returns {void}
*/
var inherits = (constructor, superConstructor, props, descriptors) => {
	constructor.prototype = Object.create(superConstructor.prototype, descriptors);
	Object.defineProperty(constructor.prototype, "constructor", {
		__proto__: null,
		value: constructor,
		writable: true,
		enumerable: false,
		configurable: true
	});
	Object.defineProperty(constructor, "super", {
		__proto__: null,
		value: superConstructor.prototype
	});
	props && Object.assign(constructor.prototype, props);
};
/**
* Resolve object with deep prototype chain to a flat object
* @param {Object} sourceObj source object
* @param {Object} [destObj]
* @param {Function|Boolean} [filter]
* @param {Function} [propFilter]
*
* @returns {Object}
*/
var toFlatObject = (sourceObj, destObj, filter, propFilter) => {
	let props;
	let i;
	let prop;
	const merged = {};
	destObj = destObj || {};
	if (sourceObj == null) return destObj;
	do {
		props = Object.getOwnPropertyNames(sourceObj);
		i = props.length;
		while (i-- > 0) {
			prop = props[i];
			if ((!propFilter || propFilter(prop, sourceObj, destObj)) && !merged[prop]) {
				destObj[prop] = sourceObj[prop];
				merged[prop] = true;
			}
		}
		sourceObj = filter !== false && getPrototypeOf(sourceObj);
	} while (sourceObj && (!filter || filter(sourceObj, destObj)) && sourceObj !== Object.prototype);
	return destObj;
};
/**
* Determines whether a string ends with the characters of a specified string
*
* @param {String} str
* @param {String} searchString
* @param {Number} [position= 0]
*
* @returns {boolean}
*/
var endsWith = (str, searchString, position) => {
	str = String(str);
	if (position === void 0 || position > str.length) position = str.length;
	position -= searchString.length;
	const lastIndex = str.indexOf(searchString, position);
	return lastIndex !== -1 && lastIndex === position;
};
/**
* Returns new array from array like object or null if failed
*
* @param {*} [thing]
*
* @returns {?Array}
*/
var toArray = (thing) => {
	if (!thing) return null;
	if (isArray$1(thing)) return thing;
	let i = thing.length;
	if (!isNumber(i)) return null;
	const arr = new Array(i);
	while (i-- > 0) arr[i] = thing[i];
	return arr;
};
/**
* Checking if the Uint8Array exists and if it does, it returns a function that checks if the
* thing passed in is an instance of Uint8Array
*
* @param {TypedArray}
*
* @returns {Array}
*/
var isTypedArray$1 = ((TypedArray) => {
	return (thing) => {
		return TypedArray && thing instanceof TypedArray;
	};
})(typeof Uint8Array !== "undefined" && getPrototypeOf(Uint8Array));
/**
* For each entry in the object, call the function with the key and value.
*
* @param {Object<any, any>} obj - The object to iterate over.
* @param {Function} fn - The function to call for each entry.
*
* @returns {void}
*/
var forEachEntry = (obj, fn) => {
	const _iterator = (obj && obj[iterator]).call(obj);
	let result;
	while ((result = _iterator.next()) && !result.done) {
		const pair = result.value;
		fn.call(obj, pair[0], pair[1]);
	}
};
/**
* It takes a regular expression and a string, and returns an array of all the matches
*
* @param {string} regExp - The regular expression to match against.
* @param {string} str - The string to search.
*
* @returns {Array<boolean>}
*/
var matchAll = (regExp, str) => {
	let matches;
	const arr = [];
	while ((matches = regExp.exec(str)) !== null) arr.push(matches);
	return arr;
};
var isHTMLForm = kindOfTest("HTMLFormElement");
var toCamelCase = (str) => {
	return str.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g, function replacer(m, p1, p2) {
		return p1.toUpperCase() + p2;
	});
};
var hasOwnProperty$14 = (({ hasOwnProperty }) => (obj, prop) => hasOwnProperty.call(obj, prop))(Object.prototype);
/**
* Determine if a value is a RegExp object
*
* @param {*} val The value to test
*
* @returns {boolean} True if value is a RegExp object, otherwise false
*/
var isRegExp = kindOfTest("RegExp");
var reduceDescriptors = (obj, reducer) => {
	const descriptors = Object.getOwnPropertyDescriptors(obj);
	const reducedDescriptors = {};
	forEach(descriptors, (descriptor, name) => {
		let ret;
		if ((ret = reducer(descriptor, name, obj)) !== false) reducedDescriptors[name] = ret || descriptor;
	});
	Object.defineProperties(obj, reducedDescriptors);
};
/**
* Makes all methods read-only
* @param {Object} obj
*/
var freezeMethods = (obj) => {
	reduceDescriptors(obj, (descriptor, name) => {
		if (isFunction$2(obj) && [
			"arguments",
			"caller",
			"callee"
		].includes(name)) return false;
		const value = obj[name];
		if (!isFunction$2(value)) return;
		descriptor.enumerable = false;
		if ("writable" in descriptor) {
			descriptor.writable = false;
			return;
		}
		if (!descriptor.set) descriptor.set = () => {
			throw Error("Can not rewrite read-only method '" + name + "'");
		};
	});
};
/**
* Converts an array or a delimited string into an object set with values as keys and true as values.
* Useful for fast membership checks.
*
* @param {Array|string} arrayOrString - The array or string to convert.
* @param {string} delimiter - The delimiter to use if input is a string.
* @returns {Object} An object with keys from the array or string, values set to true.
*/
var toObjectSet = (arrayOrString, delimiter) => {
	const obj = {};
	const define = (arr) => {
		arr.forEach((value) => {
			obj[value] = true;
		});
	};
	isArray$1(arrayOrString) ? define(arrayOrString) : define(String(arrayOrString).split(delimiter));
	return obj;
};
var noop$1 = () => {};
var toFiniteNumber = (value, defaultValue) => {
	return value != null && Number.isFinite(value = +value) ? value : defaultValue;
};
/**
* If the thing is a FormData object, return true, otherwise return false.
*
* @param {unknown} thing - The thing to check.
*
* @returns {boolean}
*/
function isSpecCompliantForm(thing) {
	return !!(thing && isFunction$2(thing.append) && thing[toStringTag] === "FormData" && thing[iterator]);
}
/**
* Recursively converts an object to a JSON-compatible object, handling circular references and Buffers.
*
* @param {Object} obj - The object to convert.
* @returns {Object} The JSON-compatible object.
*/
var toJSONObject = (obj) => {
	const stack = new Array(10);
	const visit = (source, i) => {
		if (isObject$1(source)) {
			if (stack.indexOf(source) >= 0) return;
			if (isBuffer$1(source)) return source;
			if (!("toJSON" in source)) {
				stack[i] = source;
				const target = isArray$1(source) ? [] : {};
				forEach(source, (value, key) => {
					const reducedValue = visit(value, i + 1);
					!isUndefined(reducedValue) && (target[key] = reducedValue);
				});
				stack[i] = void 0;
				return target;
			}
		}
		return source;
	};
	return visit(obj, 0);
};
/**
* Determines if a value is an async function.
*
* @param {*} thing - The value to test.
* @returns {boolean} True if value is an async function, otherwise false.
*/
var isAsyncFn = kindOfTest("AsyncFunction");
/**
* Determines if a value is thenable (has then and catch methods).
*
* @param {*} thing - The value to test.
* @returns {boolean} True if value is thenable, otherwise false.
*/
var isThenable = (thing) => thing && (isObject$1(thing) || isFunction$2(thing)) && isFunction$2(thing.then) && isFunction$2(thing.catch);
/**
* Provides a cross-platform setImmediate implementation.
* Uses native setImmediate if available, otherwise falls back to postMessage or setTimeout.
*
* @param {boolean} setImmediateSupported - Whether setImmediate is supported.
* @param {boolean} postMessageSupported - Whether postMessage is supported.
* @returns {Function} A function to schedule a callback asynchronously.
*/
var _setImmediate = ((setImmediateSupported, postMessageSupported) => {
	if (setImmediateSupported) return setImmediate;
	return postMessageSupported ? ((token, callbacks) => {
		_global.addEventListener("message", ({ source, data }) => {
			if (source === _global && data === token) callbacks.length && callbacks.shift()();
		}, false);
		return (cb) => {
			callbacks.push(cb);
			_global.postMessage(token, "*");
		};
	})(`axios@${Math.random()}`, []) : (cb) => setTimeout(cb);
})(typeof setImmediate === "function", isFunction$2(_global.postMessage));
/**
* Schedules a microtask or asynchronous callback as soon as possible.
* Uses queueMicrotask if available, otherwise falls back to process.nextTick or _setImmediate.
*
* @type {Function}
*/
var asap = typeof queueMicrotask !== "undefined" ? queueMicrotask.bind(_global) : typeof process !== "undefined" && process.nextTick || _setImmediate;
var isIterable = (thing) => thing != null && isFunction$2(thing[iterator]);
var utils_default = {
	isArray: isArray$1,
	isArrayBuffer,
	isBuffer: isBuffer$1,
	isFormData: isFormData$1,
	isArrayBufferView,
	isString,
	isNumber,
	isBoolean,
	isObject: isObject$1,
	isPlainObject: isPlainObject$1,
	isEmptyObject,
	isReadableStream,
	isRequest,
	isResponse,
	isHeaders,
	isUndefined,
	isDate,
	isFile: isFile$2,
	isReactNativeBlob,
	isReactNative,
	isBlob,
	isRegExp,
	isFunction: isFunction$2,
	isStream,
	isURLSearchParams,
	isTypedArray: isTypedArray$1,
	isFileList,
	forEach,
	merge: merge$1,
	extend,
	trim,
	stripBOM,
	inherits,
	toFlatObject,
	kindOf,
	kindOfTest,
	endsWith,
	toArray,
	forEachEntry,
	matchAll,
	isHTMLForm,
	hasOwnProperty: hasOwnProperty$14,
	hasOwnProp: hasOwnProperty$14,
	reduceDescriptors,
	freezeMethods,
	toObjectSet,
	toCamelCase,
	noop: noop$1,
	toFiniteNumber,
	findKey,
	global: _global,
	isContextDefined,
	isSpecCompliantForm,
	toJSONObject,
	isAsyncFn,
	isThenable,
	setImmediate: _setImmediate,
	asap,
	isIterable
};
//#endregion
//#region node_modules/axios/lib/helpers/parseHeaders.js
var ignoreDuplicateOf = utils_default.toObjectSet([
	"age",
	"authorization",
	"content-length",
	"content-type",
	"etag",
	"expires",
	"from",
	"host",
	"if-modified-since",
	"if-unmodified-since",
	"last-modified",
	"location",
	"max-forwards",
	"proxy-authorization",
	"referer",
	"retry-after",
	"user-agent"
]);
/**
* Parse headers into an object
*
* ```
* Date: Wed, 27 Aug 2014 08:58:49 GMT
* Content-Type: application/json
* Connection: keep-alive
* Transfer-Encoding: chunked
* ```
*
* @param {String} rawHeaders Headers needing to be parsed
*
* @returns {Object} Headers parsed into an object
*/
var parseHeaders_default = (rawHeaders) => {
	const parsed = {};
	let key;
	let val;
	let i;
	rawHeaders && rawHeaders.split("\n").forEach(function parser(line) {
		i = line.indexOf(":");
		key = line.substring(0, i).trim().toLowerCase();
		val = line.substring(i + 1).trim();
		if (!key || parsed[key] && ignoreDuplicateOf[key]) return;
		if (key === "set-cookie") if (parsed[key]) parsed[key].push(val);
		else parsed[key] = [val];
		else parsed[key] = parsed[key] ? parsed[key] + ", " + val : val;
	});
	return parsed;
};
//#endregion
//#region node_modules/axios/lib/core/AxiosHeaders.js
var $internals = Symbol("internals");
var INVALID_HEADER_VALUE_CHARS_RE = /[^\x09\x20-\x7E\x80-\xFF]/g;
function trimSPorHTAB(str) {
	let start = 0;
	let end = str.length;
	while (start < end) {
		const code = str.charCodeAt(start);
		if (code !== 9 && code !== 32) break;
		start += 1;
	}
	while (end > start) {
		const code = str.charCodeAt(end - 1);
		if (code !== 9 && code !== 32) break;
		end -= 1;
	}
	return start === 0 && end === str.length ? str : str.slice(start, end);
}
function normalizeHeader(header) {
	return header && String(header).trim().toLowerCase();
}
function sanitizeHeaderValue(str) {
	return trimSPorHTAB(str.replace(INVALID_HEADER_VALUE_CHARS_RE, ""));
}
function normalizeValue(value) {
	if (value === false || value == null) return value;
	return utils_default.isArray(value) ? value.map(normalizeValue) : sanitizeHeaderValue(String(value));
}
function parseTokens(str) {
	const tokens = Object.create(null);
	const tokensRE = /([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;
	let match;
	while (match = tokensRE.exec(str)) tokens[match[1]] = match[2];
	return tokens;
}
var isValidHeaderName = (str) => /^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(str.trim());
function matchHeaderValue(context, value, header, filter, isHeaderNameFilter) {
	if (utils_default.isFunction(filter)) return filter.call(this, value, header);
	if (isHeaderNameFilter) value = header;
	if (!utils_default.isString(value)) return;
	if (utils_default.isString(filter)) return value.indexOf(filter) !== -1;
	if (utils_default.isRegExp(filter)) return filter.test(value);
}
function formatHeader(header) {
	return header.trim().toLowerCase().replace(/([a-z\d])(\w*)/g, (w, char, str) => {
		return char.toUpperCase() + str;
	});
}
function buildAccessors(obj, header) {
	const accessorName = utils_default.toCamelCase(" " + header);
	[
		"get",
		"set",
		"has"
	].forEach((methodName) => {
		Object.defineProperty(obj, methodName + accessorName, {
			__proto__: null,
			value: function(arg1, arg2, arg3) {
				return this[methodName].call(this, header, arg1, arg2, arg3);
			},
			configurable: true
		});
	});
}
var AxiosHeaders$1 = class {
	constructor(headers) {
		headers && this.set(headers);
	}
	set(header, valueOrRewrite, rewrite) {
		const self = this;
		function setHeader(_value, _header, _rewrite) {
			const lHeader = normalizeHeader(_header);
			if (!lHeader) throw new Error("header name must be a non-empty string");
			const key = utils_default.findKey(self, lHeader);
			if (!key || self[key] === void 0 || _rewrite === true || _rewrite === void 0 && self[key] !== false) self[key || _header] = normalizeValue(_value);
		}
		const setHeaders = (headers, _rewrite) => utils_default.forEach(headers, (_value, _header) => setHeader(_value, _header, _rewrite));
		if (utils_default.isPlainObject(header) || header instanceof this.constructor) setHeaders(header, valueOrRewrite);
		else if (utils_default.isString(header) && (header = header.trim()) && !isValidHeaderName(header)) setHeaders(parseHeaders_default(header), valueOrRewrite);
		else if (utils_default.isObject(header) && utils_default.isIterable(header)) {
			let obj = {}, dest, key;
			for (const entry of header) {
				if (!utils_default.isArray(entry)) throw TypeError("Object iterator must return a key-value pair");
				obj[key = entry[0]] = (dest = obj[key]) ? utils_default.isArray(dest) ? [...dest, entry[1]] : [dest, entry[1]] : entry[1];
			}
			setHeaders(obj, valueOrRewrite);
		} else header != null && setHeader(valueOrRewrite, header, rewrite);
		return this;
	}
	get(header, parser) {
		header = normalizeHeader(header);
		if (header) {
			const key = utils_default.findKey(this, header);
			if (key) {
				const value = this[key];
				if (!parser) return value;
				if (parser === true) return parseTokens(value);
				if (utils_default.isFunction(parser)) return parser.call(this, value, key);
				if (utils_default.isRegExp(parser)) return parser.exec(value);
				throw new TypeError("parser must be boolean|regexp|function");
			}
		}
	}
	has(header, matcher) {
		header = normalizeHeader(header);
		if (header) {
			const key = utils_default.findKey(this, header);
			return !!(key && this[key] !== void 0 && (!matcher || matchHeaderValue(this, this[key], key, matcher)));
		}
		return false;
	}
	delete(header, matcher) {
		const self = this;
		let deleted = false;
		function deleteHeader(_header) {
			_header = normalizeHeader(_header);
			if (_header) {
				const key = utils_default.findKey(self, _header);
				if (key && (!matcher || matchHeaderValue(self, self[key], key, matcher))) {
					delete self[key];
					deleted = true;
				}
			}
		}
		if (utils_default.isArray(header)) header.forEach(deleteHeader);
		else deleteHeader(header);
		return deleted;
	}
	clear(matcher) {
		const keys = Object.keys(this);
		let i = keys.length;
		let deleted = false;
		while (i--) {
			const key = keys[i];
			if (!matcher || matchHeaderValue(this, this[key], key, matcher, true)) {
				delete this[key];
				deleted = true;
			}
		}
		return deleted;
	}
	normalize(format) {
		const self = this;
		const headers = {};
		utils_default.forEach(this, (value, header) => {
			const key = utils_default.findKey(headers, header);
			if (key) {
				self[key] = normalizeValue(value);
				delete self[header];
				return;
			}
			const normalized = format ? formatHeader(header) : String(header).trim();
			if (normalized !== header) delete self[header];
			self[normalized] = normalizeValue(value);
			headers[normalized] = true;
		});
		return this;
	}
	concat(...targets) {
		return this.constructor.concat(this, ...targets);
	}
	toJSON(asStrings) {
		const obj = Object.create(null);
		utils_default.forEach(this, (value, header) => {
			value != null && value !== false && (obj[header] = asStrings && utils_default.isArray(value) ? value.join(", ") : value);
		});
		return obj;
	}
	[Symbol.iterator]() {
		return Object.entries(this.toJSON())[Symbol.iterator]();
	}
	toString() {
		return Object.entries(this.toJSON()).map(([header, value]) => header + ": " + value).join("\n");
	}
	getSetCookie() {
		return this.get("set-cookie") || [];
	}
	get [Symbol.toStringTag]() {
		return "AxiosHeaders";
	}
	static from(thing) {
		return thing instanceof this ? thing : new this(thing);
	}
	static concat(first, ...targets) {
		const computed = new this(first);
		targets.forEach((target) => computed.set(target));
		return computed;
	}
	static accessor(header) {
		const accessors = (this[$internals] = this[$internals] = { accessors: {} }).accessors;
		const prototype = this.prototype;
		function defineAccessor(_header) {
			const lHeader = normalizeHeader(_header);
			if (!accessors[lHeader]) {
				buildAccessors(prototype, _header);
				accessors[lHeader] = true;
			}
		}
		utils_default.isArray(header) ? header.forEach(defineAccessor) : defineAccessor(header);
		return this;
	}
};
AxiosHeaders$1.accessor([
	"Content-Type",
	"Content-Length",
	"Accept",
	"Accept-Encoding",
	"User-Agent",
	"Authorization"
]);
utils_default.reduceDescriptors(AxiosHeaders$1.prototype, ({ value }, key) => {
	let mapped = key[0].toUpperCase() + key.slice(1);
	return {
		get: () => value,
		set(headerValue) {
			this[mapped] = headerValue;
		}
	};
});
utils_default.freezeMethods(AxiosHeaders$1);
//#endregion
//#region node_modules/axios/lib/core/AxiosError.js
var REDACTED = "[REDACTED ****]";
function hasOwnOrPrototypeToJSON(source) {
	if (utils_default.hasOwnProp(source, "toJSON")) return true;
	let prototype = Object.getPrototypeOf(source);
	while (prototype && prototype !== Object.prototype) {
		if (utils_default.hasOwnProp(prototype, "toJSON")) return true;
		prototype = Object.getPrototypeOf(prototype);
	}
	return false;
}
function redactConfig(config, redactKeys) {
	const lowerKeys = new Set(redactKeys.map((k) => String(k).toLowerCase()));
	const seen = [];
	const visit = (source) => {
		if (source === null || typeof source !== "object") return source;
		if (utils_default.isBuffer(source)) return source;
		if (seen.indexOf(source) !== -1) return void 0;
		if (source instanceof AxiosHeaders$1) source = source.toJSON();
		seen.push(source);
		let result;
		if (utils_default.isArray(source)) {
			result = [];
			source.forEach((v, i) => {
				const reducedValue = visit(v);
				if (!utils_default.isUndefined(reducedValue)) result[i] = reducedValue;
			});
		} else {
			if (!utils_default.isPlainObject(source) && hasOwnOrPrototypeToJSON(source)) {
				seen.pop();
				return source;
			}
			result = Object.create(null);
			for (const [key, value] of Object.entries(source)) {
				const reducedValue = lowerKeys.has(key.toLowerCase()) ? REDACTED : visit(value);
				if (!utils_default.isUndefined(reducedValue)) result[key] = reducedValue;
			}
		}
		seen.pop();
		return result;
	};
	return visit(config);
}
var AxiosError$1 = class AxiosError$1 extends Error {
	static from(error, code, config, request, response, customProps) {
		const axiosError = new AxiosError$1(error.message, code || error.code, config, request, response);
		axiosError.cause = error;
		axiosError.name = error.name;
		if (error.status != null && axiosError.status == null) axiosError.status = error.status;
		customProps && Object.assign(axiosError, customProps);
		return axiosError;
	}
	/**
	* Create an Error with the specified message, config, error code, request and response.
	*
	* @param {string} message The error message.
	* @param {string} [code] The error code (for example, 'ECONNABORTED').
	* @param {Object} [config] The config.
	* @param {Object} [request] The request.
	* @param {Object} [response] The response.
	*
	* @returns {Error} The created error.
	*/
	constructor(message, code, config, request, response) {
		super(message);
		Object.defineProperty(this, "message", {
			__proto__: null,
			value: message,
			enumerable: true,
			writable: true,
			configurable: true
		});
		this.name = "AxiosError";
		this.isAxiosError = true;
		code && (this.code = code);
		config && (this.config = config);
		request && (this.request = request);
		if (response) {
			this.response = response;
			this.status = response.status;
		}
	}
	toJSON() {
		const config = this.config;
		const redactKeys = config && utils_default.hasOwnProp(config, "redact") ? config.redact : void 0;
		const serializedConfig = utils_default.isArray(redactKeys) && redactKeys.length > 0 ? redactConfig(config, redactKeys) : utils_default.toJSONObject(config);
		return {
			message: this.message,
			name: this.name,
			description: this.description,
			number: this.number,
			fileName: this.fileName,
			lineNumber: this.lineNumber,
			columnNumber: this.columnNumber,
			stack: this.stack,
			config: serializedConfig,
			code: this.code,
			status: this.status
		};
	}
};
AxiosError$1.ERR_BAD_OPTION_VALUE = "ERR_BAD_OPTION_VALUE";
AxiosError$1.ERR_BAD_OPTION = "ERR_BAD_OPTION";
AxiosError$1.ECONNABORTED = "ECONNABORTED";
AxiosError$1.ETIMEDOUT = "ETIMEDOUT";
AxiosError$1.ECONNREFUSED = "ECONNREFUSED";
AxiosError$1.ERR_NETWORK = "ERR_NETWORK";
AxiosError$1.ERR_FR_TOO_MANY_REDIRECTS = "ERR_FR_TOO_MANY_REDIRECTS";
AxiosError$1.ERR_DEPRECATED = "ERR_DEPRECATED";
AxiosError$1.ERR_BAD_RESPONSE = "ERR_BAD_RESPONSE";
AxiosError$1.ERR_BAD_REQUEST = "ERR_BAD_REQUEST";
AxiosError$1.ERR_CANCELED = "ERR_CANCELED";
AxiosError$1.ERR_NOT_SUPPORT = "ERR_NOT_SUPPORT";
AxiosError$1.ERR_INVALID_URL = "ERR_INVALID_URL";
AxiosError$1.ERR_FORM_DATA_DEPTH_EXCEEDED = "ERR_FORM_DATA_DEPTH_EXCEEDED";
//#endregion
//#region node_modules/axios/lib/helpers/toFormData.js
/**
* Determines if the given thing is a array or js object.
*
* @param {string} thing - The object or array to be visited.
*
* @returns {boolean}
*/
function isVisitable(thing) {
	return utils_default.isPlainObject(thing) || utils_default.isArray(thing);
}
/**
* It removes the brackets from the end of a string
*
* @param {string} key - The key of the parameter.
*
* @returns {string} the key without the brackets.
*/
function removeBrackets(key) {
	return utils_default.endsWith(key, "[]") ? key.slice(0, -2) : key;
}
/**
* It takes a path, a key, and a boolean, and returns a string
*
* @param {string} path - The path to the current key.
* @param {string} key - The key of the current object being iterated over.
* @param {string} dots - If true, the key will be rendered with dots instead of brackets.
*
* @returns {string} The path to the current key.
*/
function renderKey(path, key, dots) {
	if (!path) return key;
	return path.concat(key).map(function each(token, i) {
		token = removeBrackets(token);
		return !dots && i ? "[" + token + "]" : token;
	}).join(dots ? "." : "");
}
/**
* If the array is an array and none of its elements are visitable, then it's a flat array.
*
* @param {Array<any>} arr - The array to check
*
* @returns {boolean}
*/
function isFlatArray(arr) {
	return utils_default.isArray(arr) && !arr.some(isVisitable);
}
var predicates = utils_default.toFlatObject(utils_default, {}, null, function filter(prop) {
	return /^is[A-Z]/.test(prop);
});
/**
* Convert a data object to FormData
*
* @param {Object} obj
* @param {?Object} [formData]
* @param {?Object} [options]
* @param {Function} [options.visitor]
* @param {Boolean} [options.metaTokens = true]
* @param {Boolean} [options.dots = false]
* @param {?Boolean} [options.indexes = false]
*
* @returns {Object}
**/
/**
* It converts an object into a FormData object
*
* @param {Object<any, any>} obj - The object to convert to form data.
* @param {string} formData - The FormData object to append to.
* @param {Object<string, any>} options
*
* @returns
*/
function toFormData$1(obj, formData, options) {
	if (!utils_default.isObject(obj)) throw new TypeError("target must be an object");
	formData = formData || new FormData();
	options = utils_default.toFlatObject(options, {
		metaTokens: true,
		dots: false,
		indexes: false
	}, false, function defined(option, source) {
		return !utils_default.isUndefined(source[option]);
	});
	const metaTokens = options.metaTokens;
	const visitor = options.visitor || defaultVisitor;
	const dots = options.dots;
	const indexes = options.indexes;
	const _Blob = options.Blob || typeof Blob !== "undefined" && Blob;
	const maxDepth = options.maxDepth === void 0 ? 100 : options.maxDepth;
	const useBlob = _Blob && utils_default.isSpecCompliantForm(formData);
	if (!utils_default.isFunction(visitor)) throw new TypeError("visitor must be a function");
	function convertValue(value) {
		if (value === null) return "";
		if (utils_default.isDate(value)) return value.toISOString();
		if (utils_default.isBoolean(value)) return value.toString();
		if (!useBlob && utils_default.isBlob(value)) throw new AxiosError$1("Blob is not supported. Use a Buffer instead.");
		if (utils_default.isArrayBuffer(value) || utils_default.isTypedArray(value)) return useBlob && typeof Blob === "function" ? new Blob([value]) : Buffer.from(value);
		return value;
	}
	/**
	* Default visitor.
	*
	* @param {*} value
	* @param {String|Number} key
	* @param {Array<String|Number>} path
	* @this {FormData}
	*
	* @returns {boolean} return true to visit the each prop of the value recursively
	*/
	function defaultVisitor(value, key, path) {
		let arr = value;
		if (utils_default.isReactNative(formData) && utils_default.isReactNativeBlob(value)) {
			formData.append(renderKey(path, key, dots), convertValue(value));
			return false;
		}
		if (value && !path && typeof value === "object") {
			if (utils_default.endsWith(key, "{}")) {
				key = metaTokens ? key : key.slice(0, -2);
				value = JSON.stringify(value);
			} else if (utils_default.isArray(value) && isFlatArray(value) || (utils_default.isFileList(value) || utils_default.endsWith(key, "[]")) && (arr = utils_default.toArray(value))) {
				key = removeBrackets(key);
				arr.forEach(function each(el, index) {
					!(utils_default.isUndefined(el) || el === null) && formData.append(indexes === true ? renderKey([key], index, dots) : indexes === null ? key : key + "[]", convertValue(el));
				});
				return false;
			}
		}
		if (isVisitable(value)) return true;
		formData.append(renderKey(path, key, dots), convertValue(value));
		return false;
	}
	const stack = [];
	const exposedHelpers = Object.assign(predicates, {
		defaultVisitor,
		convertValue,
		isVisitable
	});
	function build(value, path, depth = 0) {
		if (utils_default.isUndefined(value)) return;
		if (depth > maxDepth) throw new AxiosError$1("Object is too deeply nested (" + depth + " levels). Max depth: " + maxDepth, AxiosError$1.ERR_FORM_DATA_DEPTH_EXCEEDED);
		if (stack.indexOf(value) !== -1) throw Error("Circular reference detected in " + path.join("."));
		stack.push(value);
		utils_default.forEach(value, function each(el, key) {
			if ((!(utils_default.isUndefined(el) || el === null) && visitor.call(formData, el, utils_default.isString(key) ? key.trim() : key, path, exposedHelpers)) === true) build(el, path ? path.concat(key) : [key], depth + 1);
		});
		stack.pop();
	}
	if (!utils_default.isObject(obj)) throw new TypeError("data must be an object");
	build(obj);
	return formData;
}
//#endregion
//#region node_modules/axios/lib/helpers/AxiosURLSearchParams.js
/**
* It encodes a string by replacing all characters that are not in the unreserved set with
* their percent-encoded equivalents
*
* @param {string} str - The string to encode.
*
* @returns {string} The encoded string.
*/
function encode$1(str) {
	const charMap = {
		"!": "%21",
		"'": "%27",
		"(": "%28",
		")": "%29",
		"~": "%7E",
		"%20": "+"
	};
	return encodeURIComponent(str).replace(/[!'()~]|%20/g, function replacer(match) {
		return charMap[match];
	});
}
/**
* It takes a params object and converts it to a FormData object
*
* @param {Object<string, any>} params - The parameters to be converted to a FormData object.
* @param {Object<string, any>} options - The options object passed to the Axios constructor.
*
* @returns {void}
*/
function AxiosURLSearchParams(params, options) {
	this._pairs = [];
	params && toFormData$1(params, this, options);
}
var prototype = AxiosURLSearchParams.prototype;
prototype.append = function append(name, value) {
	this._pairs.push([name, value]);
};
prototype.toString = function toString(encoder) {
	const _encode = encoder ? function(value) {
		return encoder.call(this, value, encode$1);
	} : encode$1;
	return this._pairs.map(function each(pair) {
		return _encode(pair[0]) + "=" + _encode(pair[1]);
	}, "").join("&");
};
//#endregion
//#region node_modules/axios/lib/helpers/buildURL.js
/**
* It replaces URL-encoded forms of `:`, `$`, `,`, and spaces with
* their plain counterparts (`:`, `$`, `,`, `+`).
*
* @param {string} val The value to be encoded.
*
* @returns {string} The encoded value.
*/
function encode(val) {
	return encodeURIComponent(val).replace(/%3A/gi, ":").replace(/%24/g, "$").replace(/%2C/gi, ",").replace(/%20/g, "+");
}
/**
* Build a URL by appending params to the end
*
* @param {string} url The base of the url (e.g., http://www.google.com)
* @param {object} [params] The params to be appended
* @param {?(object|Function)} options
*
* @returns {string} The formatted url
*/
function buildURL(url, params, options) {
	if (!params) return url;
	const _encode = options && options.encode || encode;
	const _options = utils_default.isFunction(options) ? { serialize: options } : options;
	const serializeFn = _options && _options.serialize;
	let serializedParams;
	if (serializeFn) serializedParams = serializeFn(params, _options);
	else serializedParams = utils_default.isURLSearchParams(params) ? params.toString() : new AxiosURLSearchParams(params, _options).toString(_encode);
	if (serializedParams) {
		const hashmarkIndex = url.indexOf("#");
		if (hashmarkIndex !== -1) url = url.slice(0, hashmarkIndex);
		url += (url.indexOf("?") === -1 ? "?" : "&") + serializedParams;
	}
	return url;
}
//#endregion
//#region node_modules/axios/lib/core/InterceptorManager.js
var InterceptorManager = class {
	constructor() {
		this.handlers = [];
	}
	/**
	* Add a new interceptor to the stack
	*
	* @param {Function} fulfilled The function to handle `then` for a `Promise`
	* @param {Function} rejected The function to handle `reject` for a `Promise`
	* @param {Object} options The options for the interceptor, synchronous and runWhen
	*
	* @return {Number} An ID used to remove interceptor later
	*/
	use(fulfilled, rejected, options) {
		this.handlers.push({
			fulfilled,
			rejected,
			synchronous: options ? options.synchronous : false,
			runWhen: options ? options.runWhen : null
		});
		return this.handlers.length - 1;
	}
	/**
	* Remove an interceptor from the stack
	*
	* @param {Number} id The ID that was returned by `use`
	*
	* @returns {void}
	*/
	eject(id) {
		if (this.handlers[id]) this.handlers[id] = null;
	}
	/**
	* Clear all interceptors from the stack
	*
	* @returns {void}
	*/
	clear() {
		if (this.handlers) this.handlers = [];
	}
	/**
	* Iterate over all the registered interceptors
	*
	* This method is particularly useful for skipping over any
	* interceptors that may have become `null` calling `eject`.
	*
	* @param {Function} fn The function to call for each interceptor
	*
	* @returns {void}
	*/
	forEach(fn) {
		utils_default.forEach(this.handlers, function forEachHandler(h) {
			if (h !== null) fn(h);
		});
	}
};
//#endregion
//#region node_modules/axios/lib/defaults/transitional.js
var transitional_default = {
	silentJSONParsing: true,
	forcedJSONParsing: true,
	clarifyTimeoutError: false,
	legacyInterceptorReqResOrdering: true
};
//#endregion
//#region node_modules/axios/lib/platform/browser/index.js
var browser_default = {
	isBrowser: true,
	classes: {
		URLSearchParams: typeof URLSearchParams !== "undefined" ? URLSearchParams : AxiosURLSearchParams,
		FormData: typeof FormData !== "undefined" ? FormData : null,
		Blob: typeof Blob !== "undefined" ? Blob : null
	},
	protocols: [
		"http",
		"https",
		"file",
		"blob",
		"url",
		"data"
	]
};
//#endregion
//#region node_modules/axios/lib/platform/common/utils.js
var utils_exports = /* @__PURE__ */ __exportAll({
	hasBrowserEnv: () => hasBrowserEnv,
	hasStandardBrowserEnv: () => hasStandardBrowserEnv,
	hasStandardBrowserWebWorkerEnv: () => hasStandardBrowserWebWorkerEnv,
	navigator: () => _navigator,
	origin: () => origin
});
var hasBrowserEnv = typeof window !== "undefined" && typeof document !== "undefined";
var _navigator = typeof navigator === "object" && navigator || void 0;
/**
* Determine if we're running in a standard browser environment
*
* This allows axios to run in a web worker, and react-native.
* Both environments support XMLHttpRequest, but not fully standard globals.
*
* web workers:
*  typeof window -> undefined
*  typeof document -> undefined
*
* react-native:
*  navigator.product -> 'ReactNative'
* nativescript
*  navigator.product -> 'NativeScript' or 'NS'
*
* @returns {boolean}
*/
var hasStandardBrowserEnv = hasBrowserEnv && (!_navigator || [
	"ReactNative",
	"NativeScript",
	"NS"
].indexOf(_navigator.product) < 0);
/**
* Determine if we're running in a standard browser webWorker environment
*
* Although the `isStandardBrowserEnv` method indicates that
* `allows axios to run in a web worker`, the WebWorker will still be
* filtered out due to its judgment standard
* `typeof window !== 'undefined' && typeof document !== 'undefined'`.
* This leads to a problem when axios post `FormData` in webWorker
*/
var hasStandardBrowserWebWorkerEnv = (() => {
	return typeof WorkerGlobalScope !== "undefined" && self instanceof WorkerGlobalScope && typeof self.importScripts === "function";
})();
var origin = hasBrowserEnv && window.location.href || "http://localhost";
//#endregion
//#region node_modules/axios/lib/platform/index.js
var platform_default = {
	...utils_exports,
	...browser_default
};
//#endregion
//#region node_modules/axios/lib/helpers/toURLEncodedForm.js
function toURLEncodedForm(data, options) {
	return toFormData$1(data, new platform_default.classes.URLSearchParams(), {
		visitor: function(value, key, path, helpers) {
			if (platform_default.isNode && utils_default.isBuffer(value)) {
				this.append(key, value.toString("base64"));
				return false;
			}
			return helpers.defaultVisitor.apply(this, arguments);
		},
		...options
	});
}
//#endregion
//#region node_modules/axios/lib/helpers/formDataToJSON.js
/**
* It takes a string like `foo[x][y][z]` and returns an array like `['foo', 'x', 'y', 'z']
*
* @param {string} name - The name of the property to get.
*
* @returns An array of strings.
*/
function parsePropPath(name) {
	return utils_default.matchAll(/\w+|\[(\w*)]/g, name).map((match) => {
		return match[0] === "[]" ? "" : match[1] || match[0];
	});
}
/**
* Convert an array to an object.
*
* @param {Array<any>} arr - The array to convert to an object.
*
* @returns An object with the same keys and values as the array.
*/
function arrayToObject(arr) {
	const obj = {};
	const keys = Object.keys(arr);
	let i;
	const len = keys.length;
	let key;
	for (i = 0; i < len; i++) {
		key = keys[i];
		obj[key] = arr[key];
	}
	return obj;
}
/**
* It takes a FormData object and returns a JavaScript object
*
* @param {string} formData The FormData object to convert to JSON.
*
* @returns {Object<string, any> | null} The converted object.
*/
function formDataToJSON(formData) {
	function buildPath(path, value, target, index) {
		let name = path[index++];
		if (name === "__proto__") return true;
		const isNumericKey = Number.isFinite(+name);
		const isLast = index >= path.length;
		name = !name && utils_default.isArray(target) ? target.length : name;
		if (isLast) {
			if (utils_default.hasOwnProp(target, name)) target[name] = utils_default.isArray(target[name]) ? target[name].concat(value) : [target[name], value];
			else target[name] = value;
			return !isNumericKey;
		}
		if (!target[name] || !utils_default.isObject(target[name])) target[name] = [];
		if (buildPath(path, value, target[name], index) && utils_default.isArray(target[name])) target[name] = arrayToObject(target[name]);
		return !isNumericKey;
	}
	if (utils_default.isFormData(formData) && utils_default.isFunction(formData.entries)) {
		const obj = {};
		utils_default.forEachEntry(formData, (name, value) => {
			buildPath(parsePropPath(name), value, obj, 0);
		});
		return obj;
	}
	return null;
}
//#endregion
//#region node_modules/axios/lib/defaults/index.js
var own = (obj, key) => obj != null && utils_default.hasOwnProp(obj, key) ? obj[key] : void 0;
/**
* It takes a string, tries to parse it, and if it fails, it returns the stringified version
* of the input
*
* @param {any} rawValue - The value to be stringified.
* @param {Function} parser - A function that parses a string into a JavaScript object.
* @param {Function} encoder - A function that takes a value and returns a string.
*
* @returns {string} A stringified version of the rawValue.
*/
function stringifySafely(rawValue, parser, encoder) {
	if (utils_default.isString(rawValue)) try {
		(parser || JSON.parse)(rawValue);
		return utils_default.trim(rawValue);
	} catch (e) {
		if (e.name !== "SyntaxError") throw e;
	}
	return (encoder || JSON.stringify)(rawValue);
}
var defaults = {
	transitional: transitional_default,
	adapter: [
		"xhr",
		"http",
		"fetch"
	],
	transformRequest: [function transformRequest(data, headers) {
		const contentType = headers.getContentType() || "";
		const hasJSONContentType = contentType.indexOf("application/json") > -1;
		const isObjectPayload = utils_default.isObject(data);
		if (isObjectPayload && utils_default.isHTMLForm(data)) data = new FormData(data);
		if (utils_default.isFormData(data)) return hasJSONContentType ? JSON.stringify(formDataToJSON(data)) : data;
		if (utils_default.isArrayBuffer(data) || utils_default.isBuffer(data) || utils_default.isStream(data) || utils_default.isFile(data) || utils_default.isBlob(data) || utils_default.isReadableStream(data)) return data;
		if (utils_default.isArrayBufferView(data)) return data.buffer;
		if (utils_default.isURLSearchParams(data)) {
			headers.setContentType("application/x-www-form-urlencoded;charset=utf-8", false);
			return data.toString();
		}
		let isFileList;
		if (isObjectPayload) {
			const formSerializer = own(this, "formSerializer");
			if (contentType.indexOf("application/x-www-form-urlencoded") > -1) return toURLEncodedForm(data, formSerializer).toString();
			if ((isFileList = utils_default.isFileList(data)) || contentType.indexOf("multipart/form-data") > -1) {
				const env = own(this, "env");
				const _FormData = env && env.FormData;
				return toFormData$1(isFileList ? { "files[]": data } : data, _FormData && new _FormData(), formSerializer);
			}
		}
		if (isObjectPayload || hasJSONContentType) {
			headers.setContentType("application/json", false);
			return stringifySafely(data);
		}
		return data;
	}],
	transformResponse: [function transformResponse(data) {
		const transitional = own(this, "transitional") || defaults.transitional;
		const forcedJSONParsing = transitional && transitional.forcedJSONParsing;
		const responseType = own(this, "responseType");
		const JSONRequested = responseType === "json";
		if (utils_default.isResponse(data) || utils_default.isReadableStream(data)) return data;
		if (data && utils_default.isString(data) && (forcedJSONParsing && !responseType || JSONRequested)) {
			const strictJSONParsing = !(transitional && transitional.silentJSONParsing) && JSONRequested;
			try {
				return JSON.parse(data, own(this, "parseReviver"));
			} catch (e) {
				if (strictJSONParsing) {
					if (e.name === "SyntaxError") throw AxiosError$1.from(e, AxiosError$1.ERR_BAD_RESPONSE, this, null, own(this, "response"));
					throw e;
				}
			}
		}
		return data;
	}],
	/**
	* A timeout in milliseconds to abort a request. If set to 0 (default) a
	* timeout is not created.
	*/
	timeout: 0,
	xsrfCookieName: "XSRF-TOKEN",
	xsrfHeaderName: "X-XSRF-TOKEN",
	maxContentLength: -1,
	maxBodyLength: -1,
	env: {
		FormData: platform_default.classes.FormData,
		Blob: platform_default.classes.Blob
	},
	validateStatus: function validateStatus(status) {
		return status >= 200 && status < 300;
	},
	headers: { common: {
		Accept: "application/json, text/plain, */*",
		"Content-Type": void 0
	} }
};
utils_default.forEach([
	"delete",
	"get",
	"head",
	"post",
	"put",
	"patch",
	"query"
], (method) => {
	defaults.headers[method] = {};
});
//#endregion
//#region node_modules/axios/lib/core/transformData.js
/**
* Transform the data for a request or a response
*
* @param {Array|Function} fns A single function or Array of functions
* @param {?Object} response The response object
*
* @returns {*} The resulting transformed data
*/
function transformData(fns, response) {
	const config = this || defaults;
	const context = response || config;
	const headers = AxiosHeaders$1.from(context.headers);
	let data = context.data;
	utils_default.forEach(fns, function transform(fn) {
		data = fn.call(config, data, headers.normalize(), response ? response.status : void 0);
	});
	headers.normalize();
	return data;
}
//#endregion
//#region node_modules/axios/lib/cancel/isCancel.js
function isCancel$1(value) {
	return !!(value && value.__CANCEL__);
}
//#endregion
//#region node_modules/axios/lib/cancel/CanceledError.js
var CanceledError$1 = class extends AxiosError$1 {
	/**
	* A `CanceledError` is an object that is thrown when an operation is canceled.
	*
	* @param {string=} message The message.
	* @param {Object=} config The config.
	* @param {Object=} request The request.
	*
	* @returns {CanceledError} The created error.
	*/
	constructor(message, config, request) {
		super(message == null ? "canceled" : message, AxiosError$1.ERR_CANCELED, config, request);
		this.name = "CanceledError";
		this.__CANCEL__ = true;
	}
};
//#endregion
//#region node_modules/axios/lib/core/settle.js
/**
* Resolve or reject a Promise based on response status.
*
* @param {Function} resolve A function that resolves the promise.
* @param {Function} reject A function that rejects the promise.
* @param {object} response The response.
*
* @returns {object} The response.
*/
function settle(resolve, reject, response) {
	const validateStatus = response.config.validateStatus;
	if (!response.status || !validateStatus || validateStatus(response.status)) resolve(response);
	else reject(new AxiosError$1("Request failed with status code " + response.status, response.status >= 400 && response.status < 500 ? AxiosError$1.ERR_BAD_REQUEST : AxiosError$1.ERR_BAD_RESPONSE, response.config, response.request, response));
}
//#endregion
//#region node_modules/axios/lib/helpers/parseProtocol.js
function parseProtocol(url) {
	const match = /^([-+\w]{1,25}):(?:\/\/)?/.exec(url);
	return match && match[1] || "";
}
//#endregion
//#region node_modules/axios/lib/helpers/speedometer.js
/**
* Calculate data maxRate
* @param {Number} [samplesCount= 10]
* @param {Number} [min= 1000]
* @returns {Function}
*/
function speedometer(samplesCount, min) {
	samplesCount = samplesCount || 10;
	const bytes = new Array(samplesCount);
	const timestamps = new Array(samplesCount);
	let head = 0;
	let tail = 0;
	let firstSampleTS;
	min = min !== void 0 ? min : 1e3;
	return function push(chunkLength) {
		const now = Date.now();
		const startedAt = timestamps[tail];
		if (!firstSampleTS) firstSampleTS = now;
		bytes[head] = chunkLength;
		timestamps[head] = now;
		let i = tail;
		let bytesCount = 0;
		while (i !== head) {
			bytesCount += bytes[i++];
			i = i % samplesCount;
		}
		head = (head + 1) % samplesCount;
		if (head === tail) tail = (tail + 1) % samplesCount;
		if (now - firstSampleTS < min) return;
		const passed = startedAt && now - startedAt;
		return passed ? Math.round(bytesCount * 1e3 / passed) : void 0;
	};
}
//#endregion
//#region node_modules/axios/lib/helpers/throttle.js
/**
* Throttle decorator
* @param {Function} fn
* @param {Number} freq
* @return {Function}
*/
function throttle(fn, freq) {
	let timestamp = 0;
	let threshold = 1e3 / freq;
	let lastArgs;
	let timer;
	const invoke = (args, now = Date.now()) => {
		timestamp = now;
		lastArgs = null;
		if (timer) {
			clearTimeout(timer);
			timer = null;
		}
		fn(...args);
	};
	const throttled = (...args) => {
		const now = Date.now();
		const passed = now - timestamp;
		if (passed >= threshold) invoke(args, now);
		else {
			lastArgs = args;
			if (!timer) timer = setTimeout(() => {
				timer = null;
				invoke(lastArgs);
			}, threshold - passed);
		}
	};
	const flush = () => lastArgs && invoke(lastArgs);
	return [throttled, flush];
}
//#endregion
//#region node_modules/axios/lib/helpers/progressEventReducer.js
var progressEventReducer = (listener, isDownloadStream, freq = 3) => {
	let bytesNotified = 0;
	const _speedometer = speedometer(50, 250);
	return throttle((e) => {
		const rawLoaded = e.loaded;
		const total = e.lengthComputable ? e.total : void 0;
		const loaded = total != null ? Math.min(rawLoaded, total) : rawLoaded;
		const progressBytes = Math.max(0, loaded - bytesNotified);
		const rate = _speedometer(progressBytes);
		bytesNotified = Math.max(bytesNotified, loaded);
		listener({
			loaded,
			total,
			progress: total ? loaded / total : void 0,
			bytes: progressBytes,
			rate: rate ? rate : void 0,
			estimated: rate && total ? (total - loaded) / rate : void 0,
			event: e,
			lengthComputable: total != null,
			[isDownloadStream ? "download" : "upload"]: true
		});
	}, freq);
};
var progressEventDecorator = (total, throttled) => {
	const lengthComputable = total != null;
	return [(loaded) => throttled[0]({
		lengthComputable,
		total,
		loaded
	}), throttled[1]];
};
var asyncDecorator = (fn) => (...args) => utils_default.asap(() => fn(...args));
//#endregion
//#region node_modules/axios/lib/helpers/isURLSameOrigin.js
var isURLSameOrigin_default = platform_default.hasStandardBrowserEnv ? ((origin, isMSIE) => (url) => {
	url = new URL(url, platform_default.origin);
	return origin.protocol === url.protocol && origin.host === url.host && (isMSIE || origin.port === url.port);
})(new URL(platform_default.origin), platform_default.navigator && /(msie|trident)/i.test(platform_default.navigator.userAgent)) : () => true;
//#endregion
//#region node_modules/axios/lib/helpers/cookies.js
var cookies_default = platform_default.hasStandardBrowserEnv ? {
	write(name, value, expires, path, domain, secure, sameSite) {
		if (typeof document === "undefined") return;
		const cookie = [`${name}=${encodeURIComponent(value)}`];
		if (utils_default.isNumber(expires)) cookie.push(`expires=${new Date(expires).toUTCString()}`);
		if (utils_default.isString(path)) cookie.push(`path=${path}`);
		if (utils_default.isString(domain)) cookie.push(`domain=${domain}`);
		if (secure === true) cookie.push("secure");
		if (utils_default.isString(sameSite)) cookie.push(`SameSite=${sameSite}`);
		document.cookie = cookie.join("; ");
	},
	read(name) {
		if (typeof document === "undefined") return null;
		const cookies = document.cookie.split(";");
		for (let i = 0; i < cookies.length; i++) {
			const cookie = cookies[i].replace(/^\s+/, "");
			const eq = cookie.indexOf("=");
			if (eq !== -1 && cookie.slice(0, eq) === name) return decodeURIComponent(cookie.slice(eq + 1));
		}
		return null;
	},
	remove(name) {
		this.write(name, "", Date.now() - 864e5, "/");
	}
} : {
	write() {},
	read() {
		return null;
	},
	remove() {}
};
//#endregion
//#region node_modules/axios/lib/helpers/isAbsoluteURL.js
/**
* Determines whether the specified URL is absolute
*
* @param {string} url The URL to test
*
* @returns {boolean} True if the specified URL is absolute, otherwise false
*/
function isAbsoluteURL(url) {
	if (typeof url !== "string") return false;
	return /^([a-z][a-z\d+\-.]*:)?\/\//i.test(url);
}
//#endregion
//#region node_modules/axios/lib/helpers/combineURLs.js
/**
* Creates a new URL by combining the specified URLs
*
* @param {string} baseURL The base URL
* @param {string} relativeURL The relative URL
*
* @returns {string} The combined URL
*/
function combineURLs(baseURL, relativeURL) {
	return relativeURL ? baseURL.replace(/\/?\/$/, "") + "/" + relativeURL.replace(/^\/+/, "") : baseURL;
}
//#endregion
//#region node_modules/axios/lib/core/buildFullPath.js
/**
* Creates a new URL by combining the baseURL with the requestedURL,
* only when the requestedURL is not already an absolute URL.
* If the requestURL is absolute, this function returns the requestedURL untouched.
*
* @param {string} baseURL The base URL
* @param {string} requestedURL Absolute or relative URL to combine
*
* @returns {string} The combined full path
*/
function buildFullPath(baseURL, requestedURL, allowAbsoluteUrls) {
	let isRelativeUrl = !isAbsoluteURL(requestedURL);
	if (baseURL && (isRelativeUrl || allowAbsoluteUrls === false)) return combineURLs(baseURL, requestedURL);
	return requestedURL;
}
//#endregion
//#region node_modules/axios/lib/core/mergeConfig.js
var headersToObject = (thing) => thing instanceof AxiosHeaders$1 ? { ...thing } : thing;
/**
* Config-specific merge-function which creates a new config-object
* by merging two configuration objects together.
*
* @param {Object} config1
* @param {Object} config2
*
* @returns {Object} New object resulting from merging config2 to config1
*/
function mergeConfig$2(config1, config2) {
	config2 = config2 || {};
	const config = Object.create(null);
	Object.defineProperty(config, "hasOwnProperty", {
		__proto__: null,
		value: Object.prototype.hasOwnProperty,
		enumerable: false,
		writable: true,
		configurable: true
	});
	function getMergedValue(target, source, prop, caseless) {
		if (utils_default.isPlainObject(target) && utils_default.isPlainObject(source)) return utils_default.merge.call({ caseless }, target, source);
		else if (utils_default.isPlainObject(source)) return utils_default.merge({}, source);
		else if (utils_default.isArray(source)) return source.slice();
		return source;
	}
	function mergeDeepProperties(a, b, prop, caseless) {
		if (!utils_default.isUndefined(b)) return getMergedValue(a, b, prop, caseless);
		else if (!utils_default.isUndefined(a)) return getMergedValue(void 0, a, prop, caseless);
	}
	function valueFromConfig2(a, b) {
		if (!utils_default.isUndefined(b)) return getMergedValue(void 0, b);
	}
	function defaultToConfig2(a, b) {
		if (!utils_default.isUndefined(b)) return getMergedValue(void 0, b);
		else if (!utils_default.isUndefined(a)) return getMergedValue(void 0, a);
	}
	function mergeDirectKeys(a, b, prop) {
		if (utils_default.hasOwnProp(config2, prop)) return getMergedValue(a, b);
		else if (utils_default.hasOwnProp(config1, prop)) return getMergedValue(void 0, a);
	}
	const mergeMap = {
		url: valueFromConfig2,
		method: valueFromConfig2,
		data: valueFromConfig2,
		baseURL: defaultToConfig2,
		transformRequest: defaultToConfig2,
		transformResponse: defaultToConfig2,
		paramsSerializer: defaultToConfig2,
		timeout: defaultToConfig2,
		timeoutMessage: defaultToConfig2,
		withCredentials: defaultToConfig2,
		withXSRFToken: defaultToConfig2,
		adapter: defaultToConfig2,
		responseType: defaultToConfig2,
		xsrfCookieName: defaultToConfig2,
		xsrfHeaderName: defaultToConfig2,
		onUploadProgress: defaultToConfig2,
		onDownloadProgress: defaultToConfig2,
		decompress: defaultToConfig2,
		maxContentLength: defaultToConfig2,
		maxBodyLength: defaultToConfig2,
		beforeRedirect: defaultToConfig2,
		transport: defaultToConfig2,
		httpAgent: defaultToConfig2,
		httpsAgent: defaultToConfig2,
		cancelToken: defaultToConfig2,
		socketPath: defaultToConfig2,
		allowedSocketPaths: defaultToConfig2,
		responseEncoding: defaultToConfig2,
		validateStatus: mergeDirectKeys,
		headers: (a, b, prop) => mergeDeepProperties(headersToObject(a), headersToObject(b), prop, true)
	};
	utils_default.forEach(Object.keys({
		...config1,
		...config2
	}), function computeConfigValue(prop) {
		if (prop === "__proto__" || prop === "constructor" || prop === "prototype") return;
		const merge = utils_default.hasOwnProp(mergeMap, prop) ? mergeMap[prop] : mergeDeepProperties;
		const configValue = merge(utils_default.hasOwnProp(config1, prop) ? config1[prop] : void 0, utils_default.hasOwnProp(config2, prop) ? config2[prop] : void 0, prop);
		utils_default.isUndefined(configValue) && merge !== mergeDirectKeys || (config[prop] = configValue);
	});
	return config;
}
//#endregion
//#region node_modules/axios/lib/helpers/resolveConfig.js
var FORM_DATA_CONTENT_HEADERS = ["content-type", "content-length"];
function setFormDataHeaders(headers, formHeaders, policy) {
	if (policy !== "content-only") {
		headers.set(formHeaders);
		return;
	}
	Object.entries(formHeaders).forEach(([key, val]) => {
		if (FORM_DATA_CONTENT_HEADERS.includes(key.toLowerCase())) headers.set(key, val);
	});
}
/**
* Encode a UTF-8 string to a Latin-1 byte string for use with btoa().
* This is a modern replacement for the deprecated unescape(encodeURIComponent(str)) pattern.
*
* @param {string} str The string to encode
*
* @returns {string} UTF-8 bytes as a Latin-1 string
*/
var encodeUTF8 = (str) => encodeURIComponent(str).replace(/%([0-9A-F]{2})/gi, (_, hex) => String.fromCharCode(parseInt(hex, 16)));
var resolveConfig_default = (config) => {
	const newConfig = mergeConfig$2({}, config);
	const own = (key) => utils_default.hasOwnProp(newConfig, key) ? newConfig[key] : void 0;
	const data = own("data");
	let withXSRFToken = own("withXSRFToken");
	const xsrfHeaderName = own("xsrfHeaderName");
	const xsrfCookieName = own("xsrfCookieName");
	let headers = own("headers");
	const auth = own("auth");
	const baseURL = own("baseURL");
	const allowAbsoluteUrls = own("allowAbsoluteUrls");
	const url = own("url");
	newConfig.headers = headers = AxiosHeaders$1.from(headers);
	newConfig.url = buildURL(buildFullPath(baseURL, url, allowAbsoluteUrls), config.params, config.paramsSerializer);
	if (auth) headers.set("Authorization", "Basic " + btoa((auth.username || "") + ":" + (auth.password ? encodeUTF8(auth.password) : "")));
	if (utils_default.isFormData(data)) {
		if (platform_default.hasStandardBrowserEnv || platform_default.hasStandardBrowserWebWorkerEnv) headers.setContentType(void 0);
		else if (utils_default.isFunction(data.getHeaders)) setFormDataHeaders(headers, data.getHeaders(), own("formDataHeaderPolicy"));
	}
	if (platform_default.hasStandardBrowserEnv) {
		if (utils_default.isFunction(withXSRFToken)) withXSRFToken = withXSRFToken(newConfig);
		if (withXSRFToken === true || withXSRFToken == null && isURLSameOrigin_default(newConfig.url)) {
			const xsrfValue = xsrfHeaderName && xsrfCookieName && cookies_default.read(xsrfCookieName);
			if (xsrfValue) headers.set(xsrfHeaderName, xsrfValue);
		}
	}
	return newConfig;
};
var xhr_default = typeof XMLHttpRequest !== "undefined" && function(config) {
	return new Promise(function dispatchXhrRequest(resolve, reject) {
		const _config = resolveConfig_default(config);
		let requestData = _config.data;
		const requestHeaders = AxiosHeaders$1.from(_config.headers).normalize();
		let { responseType, onUploadProgress, onDownloadProgress } = _config;
		let onCanceled;
		let uploadThrottled, downloadThrottled;
		let flushUpload, flushDownload;
		function done() {
			flushUpload && flushUpload();
			flushDownload && flushDownload();
			_config.cancelToken && _config.cancelToken.unsubscribe(onCanceled);
			_config.signal && _config.signal.removeEventListener("abort", onCanceled);
		}
		let request = new XMLHttpRequest();
		request.open(_config.method.toUpperCase(), _config.url, true);
		request.timeout = _config.timeout;
		function onloadend() {
			if (!request) return;
			const responseHeaders = AxiosHeaders$1.from("getAllResponseHeaders" in request && request.getAllResponseHeaders());
			settle(function _resolve(value) {
				resolve(value);
				done();
			}, function _reject(err) {
				reject(err);
				done();
			}, {
				data: !responseType || responseType === "text" || responseType === "json" ? request.responseText : request.response,
				status: request.status,
				statusText: request.statusText,
				headers: responseHeaders,
				config,
				request
			});
			request = null;
		}
		if ("onloadend" in request) request.onloadend = onloadend;
		else request.onreadystatechange = function handleLoad() {
			if (!request || request.readyState !== 4) return;
			if (request.status === 0 && !(request.responseURL && request.responseURL.startsWith("file:"))) return;
			setTimeout(onloadend);
		};
		request.onabort = function handleAbort() {
			if (!request) return;
			reject(new AxiosError$1("Request aborted", AxiosError$1.ECONNABORTED, config, request));
			done();
			request = null;
		};
		request.onerror = function handleError(event) {
			const err = new AxiosError$1(event && event.message ? event.message : "Network Error", AxiosError$1.ERR_NETWORK, config, request);
			err.event = event || null;
			reject(err);
			done();
			request = null;
		};
		request.ontimeout = function handleTimeout() {
			let timeoutErrorMessage = _config.timeout ? "timeout of " + _config.timeout + "ms exceeded" : "timeout exceeded";
			const transitional = _config.transitional || transitional_default;
			if (_config.timeoutErrorMessage) timeoutErrorMessage = _config.timeoutErrorMessage;
			reject(new AxiosError$1(timeoutErrorMessage, transitional.clarifyTimeoutError ? AxiosError$1.ETIMEDOUT : AxiosError$1.ECONNABORTED, config, request));
			done();
			request = null;
		};
		requestData === void 0 && requestHeaders.setContentType(null);
		if ("setRequestHeader" in request) utils_default.forEach(requestHeaders.toJSON(), function setRequestHeader(val, key) {
			request.setRequestHeader(key, val);
		});
		if (!utils_default.isUndefined(_config.withCredentials)) request.withCredentials = !!_config.withCredentials;
		if (responseType && responseType !== "json") request.responseType = _config.responseType;
		if (onDownloadProgress) {
			[downloadThrottled, flushDownload] = progressEventReducer(onDownloadProgress, true);
			request.addEventListener("progress", downloadThrottled);
		}
		if (onUploadProgress && request.upload) {
			[uploadThrottled, flushUpload] = progressEventReducer(onUploadProgress);
			request.upload.addEventListener("progress", uploadThrottled);
			request.upload.addEventListener("loadend", flushUpload);
		}
		if (_config.cancelToken || _config.signal) {
			onCanceled = (cancel) => {
				if (!request) return;
				reject(!cancel || cancel.type ? new CanceledError$1(null, config, request) : cancel);
				request.abort();
				done();
				request = null;
			};
			_config.cancelToken && _config.cancelToken.subscribe(onCanceled);
			if (_config.signal) _config.signal.aborted ? onCanceled() : _config.signal.addEventListener("abort", onCanceled);
		}
		const protocol = parseProtocol(_config.url);
		if (protocol && !platform_default.protocols.includes(protocol)) {
			reject(new AxiosError$1("Unsupported protocol " + protocol + ":", AxiosError$1.ERR_BAD_REQUEST, config));
			return;
		}
		request.send(requestData || null);
	});
};
//#endregion
//#region node_modules/axios/lib/helpers/composeSignals.js
var composeSignals = (signals, timeout) => {
	const { length } = signals = signals ? signals.filter(Boolean) : [];
	if (timeout || length) {
		let controller = new AbortController();
		let aborted;
		const onabort = function(reason) {
			if (!aborted) {
				aborted = true;
				unsubscribe();
				const err = reason instanceof Error ? reason : this.reason;
				controller.abort(err instanceof AxiosError$1 ? err : new CanceledError$1(err instanceof Error ? err.message : err));
			}
		};
		let timer = timeout && setTimeout(() => {
			timer = null;
			onabort(new AxiosError$1(`timeout of ${timeout}ms exceeded`, AxiosError$1.ETIMEDOUT));
		}, timeout);
		const unsubscribe = () => {
			if (signals) {
				timer && clearTimeout(timer);
				timer = null;
				signals.forEach((signal) => {
					signal.unsubscribe ? signal.unsubscribe(onabort) : signal.removeEventListener("abort", onabort);
				});
				signals = null;
			}
		};
		signals.forEach((signal) => signal.addEventListener("abort", onabort));
		const { signal } = controller;
		signal.unsubscribe = () => utils_default.asap(unsubscribe);
		return signal;
	}
};
//#endregion
//#region node_modules/axios/lib/helpers/trackStream.js
var streamChunk = function* (chunk, chunkSize) {
	let len = chunk.byteLength;
	if (!chunkSize || len < chunkSize) {
		yield chunk;
		return;
	}
	let pos = 0;
	let end;
	while (pos < len) {
		end = pos + chunkSize;
		yield chunk.slice(pos, end);
		pos = end;
	}
};
var readBytes = async function* (iterable, chunkSize) {
	for await (const chunk of readStream(iterable)) yield* streamChunk(chunk, chunkSize);
};
var readStream = async function* (stream) {
	if (stream[Symbol.asyncIterator]) {
		yield* stream;
		return;
	}
	const reader = stream.getReader();
	try {
		for (;;) {
			const { done, value } = await reader.read();
			if (done) break;
			yield value;
		}
	} finally {
		await reader.cancel();
	}
};
var trackStream = (stream, chunkSize, onProgress, onFinish) => {
	const iterator = readBytes(stream, chunkSize);
	let bytes = 0;
	let done;
	let _onFinish = (e) => {
		if (!done) {
			done = true;
			onFinish && onFinish(e);
		}
	};
	return new ReadableStream({
		async pull(controller) {
			try {
				const { done, value } = await iterator.next();
				if (done) {
					_onFinish();
					controller.close();
					return;
				}
				let len = value.byteLength;
				if (onProgress) onProgress(bytes += len);
				controller.enqueue(new Uint8Array(value));
			} catch (err) {
				_onFinish(err);
				throw err;
			}
		},
		cancel(reason) {
			_onFinish(reason);
			return iterator.return();
		}
	}, { highWaterMark: 2 });
};
//#endregion
//#region node_modules/axios/lib/helpers/estimateDataURLDecodedBytes.js
/**
* Estimate decoded byte length of a data:// URL *without* allocating large buffers.
* - For base64: compute exact decoded size using length and padding;
*               handle %XX at the character-count level (no string allocation).
* - For non-base64: use UTF-8 byteLength of the encoded body as a safe upper bound.
*
* @param {string} url
* @returns {number}
*/
function estimateDataURLDecodedBytes(url) {
	if (!url || typeof url !== "string") return 0;
	if (!url.startsWith("data:")) return 0;
	const comma = url.indexOf(",");
	if (comma < 0) return 0;
	const meta = url.slice(5, comma);
	const body = url.slice(comma + 1);
	if (/;base64/i.test(meta)) {
		let effectiveLen = body.length;
		const len = body.length;
		for (let i = 0; i < len; i++) if (body.charCodeAt(i) === 37 && i + 2 < len) {
			const a = body.charCodeAt(i + 1);
			const b = body.charCodeAt(i + 2);
			if ((a >= 48 && a <= 57 || a >= 65 && a <= 70 || a >= 97 && a <= 102) && (b >= 48 && b <= 57 || b >= 65 && b <= 70 || b >= 97 && b <= 102)) {
				effectiveLen -= 2;
				i += 2;
			}
		}
		let pad = 0;
		let idx = len - 1;
		const tailIsPct3D = (j) => j >= 2 && body.charCodeAt(j - 2) === 37 && body.charCodeAt(j - 1) === 51 && (body.charCodeAt(j) === 68 || body.charCodeAt(j) === 100);
		if (idx >= 0) {
			if (body.charCodeAt(idx) === 61) {
				pad++;
				idx--;
			} else if (tailIsPct3D(idx)) {
				pad++;
				idx -= 3;
			}
		}
		if (pad === 1 && idx >= 0) {
			if (body.charCodeAt(idx) === 61) pad++;
			else if (tailIsPct3D(idx)) pad++;
		}
		const bytes = Math.floor(effectiveLen / 4) * 3 - (pad || 0);
		return bytes > 0 ? bytes : 0;
	}
	if (typeof Buffer !== "undefined" && typeof Buffer.byteLength === "function") return Buffer.byteLength(body, "utf8");
	let bytes = 0;
	for (let i = 0, len = body.length; i < len; i++) {
		const c = body.charCodeAt(i);
		if (c < 128) bytes += 1;
		else if (c < 2048) bytes += 2;
		else if (c >= 55296 && c <= 56319 && i + 1 < len) {
			const next = body.charCodeAt(i + 1);
			if (next >= 56320 && next <= 57343) {
				bytes += 4;
				i++;
			} else bytes += 3;
		} else bytes += 3;
	}
	return bytes;
}
//#endregion
//#region node_modules/axios/lib/env/data.js
var VERSION$1 = "1.16.0";
//#endregion
//#region node_modules/axios/lib/adapters/fetch.js
var DEFAULT_CHUNK_SIZE = 64 * 1024;
var { isFunction: isFunction$1 } = utils_default;
var test = (fn, ...args) => {
	try {
		return !!fn(...args);
	} catch (e) {
		return false;
	}
};
var factory = (env) => {
	const globalObject = utils_default.global ?? globalThis;
	const { ReadableStream, TextEncoder } = globalObject;
	env = utils_default.merge.call({ skipUndefined: true }, {
		Request: globalObject.Request,
		Response: globalObject.Response
	}, env);
	const { fetch: envFetch, Request, Response } = env;
	const isFetchSupported = envFetch ? isFunction$1(envFetch) : typeof fetch === "function";
	const isRequestSupported = isFunction$1(Request);
	const isResponseSupported = isFunction$1(Response);
	if (!isFetchSupported) return false;
	const isReadableStreamSupported = isFetchSupported && isFunction$1(ReadableStream);
	const encodeText = isFetchSupported && (typeof TextEncoder === "function" ? ((encoder) => (str) => encoder.encode(str))(new TextEncoder()) : async (str) => new Uint8Array(await new Request(str).arrayBuffer()));
	const supportsRequestStream = isRequestSupported && isReadableStreamSupported && test(() => {
		let duplexAccessed = false;
		const request = new Request(platform_default.origin, {
			body: new ReadableStream(),
			method: "POST",
			get duplex() {
				duplexAccessed = true;
				return "half";
			}
		});
		const hasContentType = request.headers.has("Content-Type");
		if (request.body != null) request.body.cancel();
		return duplexAccessed && !hasContentType;
	});
	const supportsResponseStream = isResponseSupported && isReadableStreamSupported && test(() => utils_default.isReadableStream(new Response("").body));
	const resolvers = { stream: supportsResponseStream && ((res) => res.body) };
	isFetchSupported && (() => {
		[
			"text",
			"arrayBuffer",
			"blob",
			"formData",
			"stream"
		].forEach((type) => {
			!resolvers[type] && (resolvers[type] = (res, config) => {
				let method = res && res[type];
				if (method) return method.call(res);
				throw new AxiosError$1(`Response type '${type}' is not supported`, AxiosError$1.ERR_NOT_SUPPORT, config);
			});
		});
	})();
	const getBodyLength = async (body) => {
		if (body == null) return 0;
		if (utils_default.isBlob(body)) return body.size;
		if (utils_default.isSpecCompliantForm(body)) return (await new Request(platform_default.origin, {
			method: "POST",
			body
		}).arrayBuffer()).byteLength;
		if (utils_default.isArrayBufferView(body) || utils_default.isArrayBuffer(body)) return body.byteLength;
		if (utils_default.isURLSearchParams(body)) body = body + "";
		if (utils_default.isString(body)) return (await encodeText(body)).byteLength;
	};
	const resolveBodyLength = async (headers, body) => {
		const length = utils_default.toFiniteNumber(headers.getContentLength());
		return length == null ? getBodyLength(body) : length;
	};
	return async (config) => {
		let { url, method, data, signal, cancelToken, timeout, onDownloadProgress, onUploadProgress, responseType, headers, withCredentials = "same-origin", fetchOptions, maxContentLength, maxBodyLength } = resolveConfig_default(config);
		const hasMaxContentLength = utils_default.isNumber(maxContentLength) && maxContentLength > -1;
		const hasMaxBodyLength = utils_default.isNumber(maxBodyLength) && maxBodyLength > -1;
		let _fetch = envFetch || fetch;
		responseType = responseType ? (responseType + "").toLowerCase() : "text";
		let composedSignal = composeSignals([signal, cancelToken && cancelToken.toAbortSignal()], timeout);
		let request = null;
		const unsubscribe = composedSignal && composedSignal.unsubscribe && (() => {
			composedSignal.unsubscribe();
		});
		let requestContentLength;
		try {
			if (hasMaxContentLength && typeof url === "string" && url.startsWith("data:")) {
				if (estimateDataURLDecodedBytes(url) > maxContentLength) throw new AxiosError$1("maxContentLength size of " + maxContentLength + " exceeded", AxiosError$1.ERR_BAD_RESPONSE, config, request);
			}
			if (hasMaxBodyLength && method !== "get" && method !== "head") {
				const outboundLength = await resolveBodyLength(headers, data);
				if (typeof outboundLength === "number" && isFinite(outboundLength) && outboundLength > maxBodyLength) throw new AxiosError$1("Request body larger than maxBodyLength limit", AxiosError$1.ERR_BAD_REQUEST, config, request);
			}
			if (onUploadProgress && supportsRequestStream && method !== "get" && method !== "head" && (requestContentLength = await resolveBodyLength(headers, data)) !== 0) {
				let _request = new Request(url, {
					method: "POST",
					body: data,
					duplex: "half"
				});
				let contentTypeHeader;
				if (utils_default.isFormData(data) && (contentTypeHeader = _request.headers.get("content-type"))) headers.setContentType(contentTypeHeader);
				if (_request.body) {
					const [onProgress, flush] = progressEventDecorator(requestContentLength, progressEventReducer(asyncDecorator(onUploadProgress)));
					data = trackStream(_request.body, DEFAULT_CHUNK_SIZE, onProgress, flush);
				}
			}
			if (!utils_default.isString(withCredentials)) withCredentials = withCredentials ? "include" : "omit";
			const isCredentialsSupported = isRequestSupported && "credentials" in Request.prototype;
			if (utils_default.isFormData(data)) {
				const contentType = headers.getContentType();
				if (contentType && /^multipart\/form-data/i.test(contentType) && !/boundary=/i.test(contentType)) headers.delete("content-type");
			}
			headers.set("User-Agent", "axios/" + VERSION$1, false);
			const resolvedOptions = {
				...fetchOptions,
				signal: composedSignal,
				method: method.toUpperCase(),
				headers: headers.normalize().toJSON(),
				body: data,
				duplex: "half",
				credentials: isCredentialsSupported ? withCredentials : void 0
			};
			request = isRequestSupported && new Request(url, resolvedOptions);
			let response = await (isRequestSupported ? _fetch(request, fetchOptions) : _fetch(url, resolvedOptions));
			if (hasMaxContentLength) {
				const declaredLength = utils_default.toFiniteNumber(response.headers.get("content-length"));
				if (declaredLength != null && declaredLength > maxContentLength) throw new AxiosError$1("maxContentLength size of " + maxContentLength + " exceeded", AxiosError$1.ERR_BAD_RESPONSE, config, request);
			}
			const isStreamResponse = supportsResponseStream && (responseType === "stream" || responseType === "response");
			if (supportsResponseStream && response.body && (onDownloadProgress || hasMaxContentLength || isStreamResponse && unsubscribe)) {
				const options = {};
				[
					"status",
					"statusText",
					"headers"
				].forEach((prop) => {
					options[prop] = response[prop];
				});
				const responseContentLength = utils_default.toFiniteNumber(response.headers.get("content-length"));
				const [onProgress, flush] = onDownloadProgress && progressEventDecorator(responseContentLength, progressEventReducer(asyncDecorator(onDownloadProgress), true)) || [];
				let bytesRead = 0;
				const onChunkProgress = (loadedBytes) => {
					if (hasMaxContentLength) {
						bytesRead = loadedBytes;
						if (bytesRead > maxContentLength) throw new AxiosError$1("maxContentLength size of " + maxContentLength + " exceeded", AxiosError$1.ERR_BAD_RESPONSE, config, request);
					}
					onProgress && onProgress(loadedBytes);
				};
				response = new Response(trackStream(response.body, DEFAULT_CHUNK_SIZE, onChunkProgress, () => {
					flush && flush();
					unsubscribe && unsubscribe();
				}), options);
			}
			responseType = responseType || "text";
			let responseData = await resolvers[utils_default.findKey(resolvers, responseType) || "text"](response, config);
			if (hasMaxContentLength && !supportsResponseStream && !isStreamResponse) {
				let materializedSize;
				if (responseData != null) {
					if (typeof responseData.byteLength === "number") materializedSize = responseData.byteLength;
					else if (typeof responseData.size === "number") materializedSize = responseData.size;
					else if (typeof responseData === "string") materializedSize = typeof TextEncoder === "function" ? new TextEncoder().encode(responseData).byteLength : responseData.length;
				}
				if (typeof materializedSize === "number" && materializedSize > maxContentLength) throw new AxiosError$1("maxContentLength size of " + maxContentLength + " exceeded", AxiosError$1.ERR_BAD_RESPONSE, config, request);
			}
			!isStreamResponse && unsubscribe && unsubscribe();
			return await new Promise((resolve, reject) => {
				settle(resolve, reject, {
					data: responseData,
					headers: AxiosHeaders$1.from(response.headers),
					status: response.status,
					statusText: response.statusText,
					config,
					request
				});
			});
		} catch (err) {
			unsubscribe && unsubscribe();
			if (composedSignal && composedSignal.aborted && composedSignal.reason instanceof AxiosError$1) {
				const canceledError = composedSignal.reason;
				canceledError.config = config;
				request && (canceledError.request = request);
				err !== canceledError && (canceledError.cause = err);
				throw canceledError;
			}
			if (err && err.name === "TypeError" && /Load failed|fetch/i.test(err.message)) throw Object.assign(new AxiosError$1("Network Error", AxiosError$1.ERR_NETWORK, config, request, err && err.response), { cause: err.cause || err });
			throw AxiosError$1.from(err, err && err.code, config, request, err && err.response);
		}
	};
};
var seedCache = /* @__PURE__ */ new Map();
var getFetch = (config) => {
	let env = config && config.env || {};
	const { fetch, Request, Response } = env;
	const seeds = [
		Request,
		Response,
		fetch
	];
	let i = seeds.length, seed, target, map = seedCache;
	while (i--) {
		seed = seeds[i];
		target = map.get(seed);
		target === void 0 && map.set(seed, target = i ? /* @__PURE__ */ new Map() : factory(env));
		map = target;
	}
	return target;
};
getFetch();
//#endregion
//#region node_modules/axios/lib/adapters/adapters.js
/**
* Known adapters mapping.
* Provides environment-specific adapters for Axios:
* - `http` for Node.js
* - `xhr` for browsers
* - `fetch` for fetch API-based requests
*
* @type {Object<string, Function|Object>}
*/
var knownAdapters = {
	http: null,
	xhr: xhr_default,
	fetch: { get: getFetch }
};
utils_default.forEach(knownAdapters, (fn, value) => {
	if (fn) {
		try {
			Object.defineProperty(fn, "name", {
				__proto__: null,
				value
			});
		} catch (e) {}
		Object.defineProperty(fn, "adapterName", {
			__proto__: null,
			value
		});
	}
});
/**
* Render a rejection reason string for unknown or unsupported adapters
*
* @param {string} reason
* @returns {string}
*/
var renderReason = (reason) => `- ${reason}`;
/**
* Check if the adapter is resolved (function, null, or false)
*
* @param {Function|null|false} adapter
* @returns {boolean}
*/
var isResolvedHandle = (adapter) => utils_default.isFunction(adapter) || adapter === null || adapter === false;
/**
* Get the first suitable adapter from the provided list.
* Tries each adapter in order until a supported one is found.
* Throws an AxiosError if no adapter is suitable.
*
* @param {Array<string|Function>|string|Function} adapters - Adapter(s) by name or function.
* @param {Object} config - Axios request configuration
* @throws {AxiosError} If no suitable adapter is available
* @returns {Function} The resolved adapter function
*/
function getAdapter$1(adapters, config) {
	adapters = utils_default.isArray(adapters) ? adapters : [adapters];
	const { length } = adapters;
	let nameOrAdapter;
	let adapter;
	const rejectedReasons = {};
	for (let i = 0; i < length; i++) {
		nameOrAdapter = adapters[i];
		let id;
		adapter = nameOrAdapter;
		if (!isResolvedHandle(nameOrAdapter)) {
			adapter = knownAdapters[(id = String(nameOrAdapter)).toLowerCase()];
			if (adapter === void 0) throw new AxiosError$1(`Unknown adapter '${id}'`);
		}
		if (adapter && (utils_default.isFunction(adapter) || (adapter = adapter.get(config)))) break;
		rejectedReasons[id || "#" + i] = adapter;
	}
	if (!adapter) {
		const reasons = Object.entries(rejectedReasons).map(([id, state]) => `adapter ${id} ` + (state === false ? "is not supported by the environment" : "is not available in the build"));
		throw new AxiosError$1(`There is no suitable adapter to dispatch the request ` + (length ? reasons.length > 1 ? "since :\n" + reasons.map(renderReason).join("\n") : " " + renderReason(reasons[0]) : "as no adapter specified"), "ERR_NOT_SUPPORT");
	}
	return adapter;
}
/**
* Exports Axios adapters and utility to resolve an adapter
*/
var adapters_default = {
	/**
	* Resolve an adapter from a list of adapter names or functions.
	* @type {Function}
	*/
	getAdapter: getAdapter$1,
	/**
	* Exposes all known adapters
	* @type {Object<string, Function|Object>}
	*/
	adapters: knownAdapters
};
//#endregion
//#region node_modules/axios/lib/core/dispatchRequest.js
/**
* Throws a `CanceledError` if cancellation has been requested.
*
* @param {Object} config The config that is to be used for the request
*
* @returns {void}
*/
function throwIfCancellationRequested(config) {
	if (config.cancelToken) config.cancelToken.throwIfRequested();
	if (config.signal && config.signal.aborted) throw new CanceledError$1(null, config);
}
/**
* Dispatch a request to the server using the configured adapter.
*
* @param {object} config The config that is to be used for the request
*
* @returns {Promise} The Promise to be fulfilled
*/
function dispatchRequest(config) {
	throwIfCancellationRequested(config);
	config.headers = AxiosHeaders$1.from(config.headers);
	config.data = transformData.call(config, config.transformRequest);
	if ([
		"post",
		"put",
		"patch"
	].indexOf(config.method) !== -1) config.headers.setContentType("application/x-www-form-urlencoded", false);
	return adapters_default.getAdapter(config.adapter || defaults.adapter, config)(config).then(function onAdapterResolution(response) {
		throwIfCancellationRequested(config);
		config.response = response;
		try {
			response.data = transformData.call(config, config.transformResponse, response);
		} finally {
			delete config.response;
		}
		response.headers = AxiosHeaders$1.from(response.headers);
		return response;
	}, function onAdapterRejection(reason) {
		if (!isCancel$1(reason)) {
			throwIfCancellationRequested(config);
			if (reason && reason.response) {
				config.response = reason.response;
				try {
					reason.response.data = transformData.call(config, config.transformResponse, reason.response);
				} finally {
					delete config.response;
				}
				reason.response.headers = AxiosHeaders$1.from(reason.response.headers);
			}
		}
		return Promise.reject(reason);
	});
}
//#endregion
//#region node_modules/axios/lib/helpers/validator.js
var validators$1 = {};
[
	"object",
	"boolean",
	"number",
	"function",
	"string",
	"symbol"
].forEach((type, i) => {
	validators$1[type] = function validator(thing) {
		return typeof thing === type || "a" + (i < 1 ? "n " : " ") + type;
	};
});
var deprecatedWarnings = {};
/**
* Transitional option validator
*
* @param {function|boolean?} validator - set to false if the transitional option has been removed
* @param {string?} version - deprecated version / removed since version
* @param {string?} message - some message with additional info
*
* @returns {function}
*/
validators$1.transitional = function transitional(validator, version, message) {
	function formatMessage(opt, desc) {
		return "[Axios v" + VERSION$1 + "] Transitional option '" + opt + "'" + desc + (message ? ". " + message : "");
	}
	return (value, opt, opts) => {
		if (validator === false) throw new AxiosError$1(formatMessage(opt, " has been removed" + (version ? " in " + version : "")), AxiosError$1.ERR_DEPRECATED);
		if (version && !deprecatedWarnings[opt]) {
			deprecatedWarnings[opt] = true;
			console.warn(formatMessage(opt, " has been deprecated since v" + version + " and will be removed in the near future"));
		}
		return validator ? validator(value, opt, opts) : true;
	};
};
validators$1.spelling = function spelling(correctSpelling) {
	return (value, opt) => {
		console.warn(`${opt} is likely a misspelling of ${correctSpelling}`);
		return true;
	};
};
/**
* Assert object's properties type
*
* @param {object} options
* @param {object} schema
* @param {boolean?} allowUnknown
*
* @returns {object}
*/
function assertOptions(options, schema, allowUnknown) {
	if (typeof options !== "object") throw new AxiosError$1("options must be an object", AxiosError$1.ERR_BAD_OPTION_VALUE);
	const keys = Object.keys(options);
	let i = keys.length;
	while (i-- > 0) {
		const opt = keys[i];
		const validator = Object.prototype.hasOwnProperty.call(schema, opt) ? schema[opt] : void 0;
		if (validator) {
			const value = options[opt];
			const result = value === void 0 || validator(value, opt, options);
			if (result !== true) throw new AxiosError$1("option " + opt + " must be " + result, AxiosError$1.ERR_BAD_OPTION_VALUE);
			continue;
		}
		if (allowUnknown !== true) throw new AxiosError$1("Unknown option " + opt, AxiosError$1.ERR_BAD_OPTION);
	}
}
var validator_default = {
	assertOptions,
	validators: validators$1
};
//#endregion
//#region node_modules/axios/lib/core/Axios.js
var validators = validator_default.validators;
/**
* Create a new instance of Axios
*
* @param {Object} instanceConfig The default config for the instance
*
* @return {Axios} A new instance of Axios
*/
var Axios$1 = class {
	constructor(instanceConfig) {
		this.defaults = instanceConfig || {};
		this.interceptors = {
			request: new InterceptorManager(),
			response: new InterceptorManager()
		};
	}
	/**
	* Dispatch a request
	*
	* @param {String|Object} configOrUrl The config specific for this request (merged with this.defaults)
	* @param {?Object} config
	*
	* @returns {Promise} The Promise to be fulfilled
	*/
	async request(configOrUrl, config) {
		try {
			return await this._request(configOrUrl, config);
		} catch (err) {
			if (err instanceof Error) {
				let dummy = {};
				Error.captureStackTrace ? Error.captureStackTrace(dummy) : dummy = /* @__PURE__ */ new Error();
				const stack = (() => {
					if (!dummy.stack) return "";
					const firstNewlineIndex = dummy.stack.indexOf("\n");
					return firstNewlineIndex === -1 ? "" : dummy.stack.slice(firstNewlineIndex + 1);
				})();
				try {
					if (!err.stack) err.stack = stack;
					else if (stack) {
						const firstNewlineIndex = stack.indexOf("\n");
						const secondNewlineIndex = firstNewlineIndex === -1 ? -1 : stack.indexOf("\n", firstNewlineIndex + 1);
						const stackWithoutTwoTopLines = secondNewlineIndex === -1 ? "" : stack.slice(secondNewlineIndex + 1);
						if (!String(err.stack).endsWith(stackWithoutTwoTopLines)) err.stack += "\n" + stack;
					}
				} catch (e) {}
			}
			throw err;
		}
	}
	_request(configOrUrl, config) {
		if (typeof configOrUrl === "string") {
			config = config || {};
			config.url = configOrUrl;
		} else config = configOrUrl || {};
		config = mergeConfig$2(this.defaults, config);
		const { transitional, paramsSerializer, headers } = config;
		if (transitional !== void 0) validator_default.assertOptions(transitional, {
			silentJSONParsing: validators.transitional(validators.boolean),
			forcedJSONParsing: validators.transitional(validators.boolean),
			clarifyTimeoutError: validators.transitional(validators.boolean),
			legacyInterceptorReqResOrdering: validators.transitional(validators.boolean)
		}, false);
		if (paramsSerializer != null) if (utils_default.isFunction(paramsSerializer)) config.paramsSerializer = { serialize: paramsSerializer };
		else validator_default.assertOptions(paramsSerializer, {
			encode: validators.function,
			serialize: validators.function
		}, true);
		if (config.allowAbsoluteUrls !== void 0) {} else if (this.defaults.allowAbsoluteUrls !== void 0) config.allowAbsoluteUrls = this.defaults.allowAbsoluteUrls;
		else config.allowAbsoluteUrls = true;
		validator_default.assertOptions(config, {
			baseUrl: validators.spelling("baseURL"),
			withXsrfToken: validators.spelling("withXSRFToken")
		}, true);
		config.method = (config.method || this.defaults.method || "get").toLowerCase();
		let contextHeaders = headers && utils_default.merge(headers.common, headers[config.method]);
		headers && utils_default.forEach([
			"delete",
			"get",
			"head",
			"post",
			"put",
			"patch",
			"query",
			"common"
		], (method) => {
			delete headers[method];
		});
		config.headers = AxiosHeaders$1.concat(contextHeaders, headers);
		const requestInterceptorChain = [];
		let synchronousRequestInterceptors = true;
		this.interceptors.request.forEach(function unshiftRequestInterceptors(interceptor) {
			if (typeof interceptor.runWhen === "function" && interceptor.runWhen(config) === false) return;
			synchronousRequestInterceptors = synchronousRequestInterceptors && interceptor.synchronous;
			const transitional = config.transitional || transitional_default;
			if (transitional && transitional.legacyInterceptorReqResOrdering) requestInterceptorChain.unshift(interceptor.fulfilled, interceptor.rejected);
			else requestInterceptorChain.push(interceptor.fulfilled, interceptor.rejected);
		});
		const responseInterceptorChain = [];
		this.interceptors.response.forEach(function pushResponseInterceptors(interceptor) {
			responseInterceptorChain.push(interceptor.fulfilled, interceptor.rejected);
		});
		let promise;
		let i = 0;
		let len;
		if (!synchronousRequestInterceptors) {
			const chain = [dispatchRequest.bind(this), void 0];
			chain.unshift(...requestInterceptorChain);
			chain.push(...responseInterceptorChain);
			len = chain.length;
			promise = Promise.resolve(config);
			while (i < len) promise = promise.then(chain[i++], chain[i++]);
			return promise;
		}
		len = requestInterceptorChain.length;
		let newConfig = config;
		while (i < len) {
			const onFulfilled = requestInterceptorChain[i++];
			const onRejected = requestInterceptorChain[i++];
			try {
				newConfig = onFulfilled(newConfig);
			} catch (error) {
				onRejected.call(this, error);
				break;
			}
		}
		try {
			promise = dispatchRequest.call(this, newConfig);
		} catch (error) {
			return Promise.reject(error);
		}
		i = 0;
		len = responseInterceptorChain.length;
		while (i < len) promise = promise.then(responseInterceptorChain[i++], responseInterceptorChain[i++]);
		return promise;
	}
	getUri(config) {
		config = mergeConfig$2(this.defaults, config);
		return buildURL(buildFullPath(config.baseURL, config.url, config.allowAbsoluteUrls), config.params, config.paramsSerializer);
	}
};
utils_default.forEach([
	"delete",
	"get",
	"head",
	"options"
], function forEachMethodNoData(method) {
	Axios$1.prototype[method] = function(url, config) {
		return this.request(mergeConfig$2(config || {}, {
			method,
			url,
			data: (config || {}).data
		}));
	};
});
utils_default.forEach([
	"post",
	"put",
	"patch",
	"query"
], function forEachMethodWithData(method) {
	function generateHTTPMethod(isForm) {
		return function httpMethod(url, data, config) {
			return this.request(mergeConfig$2(config || {}, {
				method,
				headers: isForm ? { "Content-Type": "multipart/form-data" } : {},
				url,
				data
			}));
		};
	}
	Axios$1.prototype[method] = generateHTTPMethod();
	if (method !== "query") Axios$1.prototype[method + "Form"] = generateHTTPMethod(true);
});
//#endregion
//#region node_modules/axios/lib/cancel/CancelToken.js
/**
* A `CancelToken` is an object that can be used to request cancellation of an operation.
*
* @param {Function} executor The executor function.
*
* @returns {CancelToken}
*/
var CancelToken$1 = class CancelToken$1 {
	constructor(executor) {
		if (typeof executor !== "function") throw new TypeError("executor must be a function.");
		let resolvePromise;
		this.promise = new Promise(function promiseExecutor(resolve) {
			resolvePromise = resolve;
		});
		const token = this;
		this.promise.then((cancel) => {
			if (!token._listeners) return;
			let i = token._listeners.length;
			while (i-- > 0) token._listeners[i](cancel);
			token._listeners = null;
		});
		this.promise.then = (onfulfilled) => {
			let _resolve;
			const promise = new Promise((resolve) => {
				token.subscribe(resolve);
				_resolve = resolve;
			}).then(onfulfilled);
			promise.cancel = function reject() {
				token.unsubscribe(_resolve);
			};
			return promise;
		};
		executor(function cancel(message, config, request) {
			if (token.reason) return;
			token.reason = new CanceledError$1(message, config, request);
			resolvePromise(token.reason);
		});
	}
	/**
	* Throws a `CanceledError` if cancellation has been requested.
	*/
	throwIfRequested() {
		if (this.reason) throw this.reason;
	}
	/**
	* Subscribe to the cancel signal
	*/
	subscribe(listener) {
		if (this.reason) {
			listener(this.reason);
			return;
		}
		if (this._listeners) this._listeners.push(listener);
		else this._listeners = [listener];
	}
	/**
	* Unsubscribe from the cancel signal
	*/
	unsubscribe(listener) {
		if (!this._listeners) return;
		const index = this._listeners.indexOf(listener);
		if (index !== -1) this._listeners.splice(index, 1);
	}
	toAbortSignal() {
		const controller = new AbortController();
		const abort = (err) => {
			controller.abort(err);
		};
		this.subscribe(abort);
		controller.signal.unsubscribe = () => this.unsubscribe(abort);
		return controller.signal;
	}
	/**
	* Returns an object that contains a new `CancelToken` and a function that, when called,
	* cancels the `CancelToken`.
	*/
	static source() {
		let cancel;
		return {
			token: new CancelToken$1(function executor(c) {
				cancel = c;
			}),
			cancel
		};
	}
};
//#endregion
//#region node_modules/axios/lib/helpers/spread.js
/**
* Syntactic sugar for invoking a function and expanding an array for arguments.
*
* Common use case would be to use `Function.prototype.apply`.
*
*  ```js
*  function f(x, y, z) {}
*  const args = [1, 2, 3];
*  f.apply(null, args);
*  ```
*
* With `spread` this example can be re-written.
*
*  ```js
*  spread(function(x, y, z) {})([1, 2, 3]);
*  ```
*
* @param {Function} callback
*
* @returns {Function}
*/
function spread$1(callback) {
	return function wrap(arr) {
		return callback.apply(null, arr);
	};
}
//#endregion
//#region node_modules/axios/lib/helpers/isAxiosError.js
/**
* Determines whether the payload is an error thrown by Axios
*
* @param {*} payload The value to test
*
* @returns {boolean} True if the payload is an error thrown by Axios, otherwise false
*/
function isAxiosError$1(payload) {
	return utils_default.isObject(payload) && payload.isAxiosError === true;
}
//#endregion
//#region node_modules/axios/lib/helpers/HttpStatusCode.js
var HttpStatusCode$1 = {
	Continue: 100,
	SwitchingProtocols: 101,
	Processing: 102,
	EarlyHints: 103,
	Ok: 200,
	Created: 201,
	Accepted: 202,
	NonAuthoritativeInformation: 203,
	NoContent: 204,
	ResetContent: 205,
	PartialContent: 206,
	MultiStatus: 207,
	AlreadyReported: 208,
	ImUsed: 226,
	MultipleChoices: 300,
	MovedPermanently: 301,
	Found: 302,
	SeeOther: 303,
	NotModified: 304,
	UseProxy: 305,
	Unused: 306,
	TemporaryRedirect: 307,
	PermanentRedirect: 308,
	BadRequest: 400,
	Unauthorized: 401,
	PaymentRequired: 402,
	Forbidden: 403,
	NotFound: 404,
	MethodNotAllowed: 405,
	NotAcceptable: 406,
	ProxyAuthenticationRequired: 407,
	RequestTimeout: 408,
	Conflict: 409,
	Gone: 410,
	LengthRequired: 411,
	PreconditionFailed: 412,
	PayloadTooLarge: 413,
	UriTooLong: 414,
	UnsupportedMediaType: 415,
	RangeNotSatisfiable: 416,
	ExpectationFailed: 417,
	ImATeapot: 418,
	MisdirectedRequest: 421,
	UnprocessableEntity: 422,
	Locked: 423,
	FailedDependency: 424,
	TooEarly: 425,
	UpgradeRequired: 426,
	PreconditionRequired: 428,
	TooManyRequests: 429,
	RequestHeaderFieldsTooLarge: 431,
	UnavailableForLegalReasons: 451,
	InternalServerError: 500,
	NotImplemented: 501,
	BadGateway: 502,
	ServiceUnavailable: 503,
	GatewayTimeout: 504,
	HttpVersionNotSupported: 505,
	VariantAlsoNegotiates: 506,
	InsufficientStorage: 507,
	LoopDetected: 508,
	NotExtended: 510,
	NetworkAuthenticationRequired: 511,
	WebServerIsDown: 521,
	ConnectionTimedOut: 522,
	OriginIsUnreachable: 523,
	TimeoutOccurred: 524,
	SslHandshakeFailed: 525,
	InvalidSslCertificate: 526
};
Object.entries(HttpStatusCode$1).forEach(([key, value]) => {
	HttpStatusCode$1[value] = key;
});
//#endregion
//#region node_modules/axios/lib/axios.js
/**
* Create an instance of Axios
*
* @param {Object} defaultConfig The default config for the instance
*
* @returns {Axios} A new instance of Axios
*/
function createInstance(defaultConfig) {
	const context = new Axios$1(defaultConfig);
	const instance = bind(Axios$1.prototype.request, context);
	utils_default.extend(instance, Axios$1.prototype, context, { allOwnKeys: true });
	utils_default.extend(instance, context, null, { allOwnKeys: true });
	instance.create = function create(instanceConfig) {
		return createInstance(mergeConfig$2(defaultConfig, instanceConfig));
	};
	return instance;
}
var axios = createInstance(defaults);
axios.Axios = Axios$1;
axios.CanceledError = CanceledError$1;
axios.CancelToken = CancelToken$1;
axios.isCancel = isCancel$1;
axios.VERSION = VERSION$1;
axios.toFormData = toFormData$1;
axios.AxiosError = AxiosError$1;
axios.Cancel = axios.CanceledError;
axios.all = function all(promises) {
	return Promise.all(promises);
};
axios.spread = spread$1;
axios.isAxiosError = isAxiosError$1;
axios.mergeConfig = mergeConfig$2;
axios.AxiosHeaders = AxiosHeaders$1;
axios.formToJSON = (thing) => formDataToJSON(utils_default.isHTMLForm(thing) ? new FormData(thing) : thing);
axios.getAdapter = adapters_default.getAdapter;
axios.HttpStatusCode = HttpStatusCode$1;
axios.default = axios;
//#endregion
//#region node_modules/axios/index.js
var { Axios, AxiosError, CanceledError, isCancel, CancelToken, VERSION, all, Cancel, isAxiosError, spread, toFormData, AxiosHeaders, HttpStatusCode, formToJSON, getAdapter, mergeConfig: mergeConfig$1, create } = axios;
//#endregion
//#region node_modules/lodash-es/_freeGlobal.js
/** Detect free variable `global` from Node.js. */
var freeGlobal = typeof global == "object" && global && global.Object === Object && global;
//#endregion
//#region node_modules/lodash-es/_root.js
/** Detect free variable `self`. */
var freeSelf = typeof self == "object" && self && self.Object === Object && self;
/** Used as a reference to the global object. */
var root = freeGlobal || freeSelf || Function("return this")();
//#endregion
//#region node_modules/lodash-es/_Symbol.js
/** Built-in value references. */
var Symbol$1 = root.Symbol;
//#endregion
//#region node_modules/lodash-es/_getRawTag.js
/** Used for built-in method references. */
var objectProto$4 = Object.prototype;
/** Used to check objects for own properties. */
var hasOwnProperty$13 = objectProto$4.hasOwnProperty;
/**
* Used to resolve the
* [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
* of values.
*/
var nativeObjectToString$1 = objectProto$4.toString;
/** Built-in value references. */
var symToStringTag$1 = Symbol$1 ? Symbol$1.toStringTag : void 0;
/**
* A specialized version of `baseGetTag` which ignores `Symbol.toStringTag` values.
*
* @private
* @param {*} value The value to query.
* @returns {string} Returns the raw `toStringTag`.
*/
function getRawTag(value) {
	var isOwn = hasOwnProperty$13.call(value, symToStringTag$1), tag = value[symToStringTag$1];
	try {
		value[symToStringTag$1] = void 0;
		var unmasked = true;
	} catch (e) {}
	var result = nativeObjectToString$1.call(value);
	if (unmasked) if (isOwn) value[symToStringTag$1] = tag;
	else delete value[symToStringTag$1];
	return result;
}
//#endregion
//#region node_modules/lodash-es/_objectToString.js
/**
* Used to resolve the
* [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
* of values.
*/
var nativeObjectToString = Object.prototype.toString;
/**
* Converts `value` to a string using `Object.prototype.toString`.
*
* @private
* @param {*} value The value to convert.
* @returns {string} Returns the converted string.
*/
function objectToString(value) {
	return nativeObjectToString.call(value);
}
//#endregion
//#region node_modules/lodash-es/_baseGetTag.js
/** `Object#toString` result references. */
var nullTag = "[object Null]", undefinedTag = "[object Undefined]";
/** Built-in value references. */
var symToStringTag = Symbol$1 ? Symbol$1.toStringTag : void 0;
/**
* The base implementation of `getTag` without fallbacks for buggy environments.
*
* @private
* @param {*} value The value to query.
* @returns {string} Returns the `toStringTag`.
*/
function baseGetTag(value) {
	if (value == null) return value === void 0 ? undefinedTag : nullTag;
	return symToStringTag && symToStringTag in Object(value) ? getRawTag(value) : objectToString(value);
}
//#endregion
//#region node_modules/lodash-es/isObjectLike.js
/**
* Checks if `value` is object-like. A value is object-like if it's not `null`
* and has a `typeof` result of "object".
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is object-like, else `false`.
* @example
*
* _.isObjectLike({});
* // => true
*
* _.isObjectLike([1, 2, 3]);
* // => true
*
* _.isObjectLike(_.noop);
* // => false
*
* _.isObjectLike(null);
* // => false
*/
function isObjectLike(value) {
	return value != null && typeof value == "object";
}
//#endregion
//#region node_modules/lodash-es/isSymbol.js
/** `Object#toString` result references. */
var symbolTag$3 = "[object Symbol]";
/**
* Checks if `value` is classified as a `Symbol` primitive or object.
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a symbol, else `false`.
* @example
*
* _.isSymbol(Symbol.iterator);
* // => true
*
* _.isSymbol('abc');
* // => false
*/
function isSymbol(value) {
	return typeof value == "symbol" || isObjectLike(value) && baseGetTag(value) == symbolTag$3;
}
//#endregion
//#region node_modules/lodash-es/_arrayMap.js
/**
* A specialized version of `_.map` for arrays without support for iteratee
* shorthands.
*
* @private
* @param {Array} [array] The array to iterate over.
* @param {Function} iteratee The function invoked per iteration.
* @returns {Array} Returns the new mapped array.
*/
function arrayMap(array, iteratee) {
	var index = -1, length = array == null ? 0 : array.length, result = Array(length);
	while (++index < length) result[index] = iteratee(array[index], index, array);
	return result;
}
//#endregion
//#region node_modules/lodash-es/isArray.js
/**
* Checks if `value` is classified as an `Array` object.
*
* @static
* @memberOf _
* @since 0.1.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is an array, else `false`.
* @example
*
* _.isArray([1, 2, 3]);
* // => true
*
* _.isArray(document.body.children);
* // => false
*
* _.isArray('abc');
* // => false
*
* _.isArray(_.noop);
* // => false
*/
var isArray = Array.isArray;
//#endregion
//#region node_modules/lodash-es/_baseToString.js
/** Used as references for various `Number` constants. */
var INFINITY$1 = Infinity;
/** Used to convert symbols to primitives and strings. */
var symbolProto$2 = Symbol$1 ? Symbol$1.prototype : void 0, symbolToString = symbolProto$2 ? symbolProto$2.toString : void 0;
/**
* The base implementation of `_.toString` which doesn't convert nullish
* values to empty strings.
*
* @private
* @param {*} value The value to process.
* @returns {string} Returns the string.
*/
function baseToString(value) {
	if (typeof value == "string") return value;
	if (isArray(value)) return arrayMap(value, baseToString) + "";
	if (isSymbol(value)) return symbolToString ? symbolToString.call(value) : "";
	var result = value + "";
	return result == "0" && 1 / value == -INFINITY$1 ? "-0" : result;
}
//#endregion
//#region node_modules/lodash-es/_trimmedEndIndex.js
/** Used to match a single whitespace character. */
var reWhitespace = /\s/;
/**
* Used by `_.trim` and `_.trimEnd` to get the index of the last non-whitespace
* character of `string`.
*
* @private
* @param {string} string The string to inspect.
* @returns {number} Returns the index of the last non-whitespace character.
*/
function trimmedEndIndex(string) {
	var index = string.length;
	while (index-- && reWhitespace.test(string.charAt(index)));
	return index;
}
//#endregion
//#region node_modules/lodash-es/_baseTrim.js
/** Used to match leading whitespace. */
var reTrimStart = /^\s+/;
/**
* The base implementation of `_.trim`.
*
* @private
* @param {string} string The string to trim.
* @returns {string} Returns the trimmed string.
*/
function baseTrim(string) {
	return string ? string.slice(0, trimmedEndIndex(string) + 1).replace(reTrimStart, "") : string;
}
//#endregion
//#region node_modules/lodash-es/isObject.js
/**
* Checks if `value` is the
* [language type](http://www.ecma-international.org/ecma-262/7.0/#sec-ecmascript-language-types)
* of `Object`. (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)
*
* @static
* @memberOf _
* @since 0.1.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is an object, else `false`.
* @example
*
* _.isObject({});
* // => true
*
* _.isObject([1, 2, 3]);
* // => true
*
* _.isObject(_.noop);
* // => true
*
* _.isObject(null);
* // => false
*/
function isObject(value) {
	var type = typeof value;
	return value != null && (type == "object" || type == "function");
}
//#endregion
//#region node_modules/lodash-es/toNumber.js
/** Used as references for various `Number` constants. */
var NAN = NaN;
/** Used to detect bad signed hexadecimal string values. */
var reIsBadHex = /^[-+]0x[0-9a-f]+$/i;
/** Used to detect binary string values. */
var reIsBinary = /^0b[01]+$/i;
/** Used to detect octal string values. */
var reIsOctal = /^0o[0-7]+$/i;
/** Built-in method references without a dependency on `root`. */
var freeParseInt = parseInt;
/**
* Converts `value` to a number.
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to process.
* @returns {number} Returns the number.
* @example
*
* _.toNumber(3.2);
* // => 3.2
*
* _.toNumber(Number.MIN_VALUE);
* // => 5e-324
*
* _.toNumber(Infinity);
* // => Infinity
*
* _.toNumber('3.2');
* // => 3.2
*/
function toNumber(value) {
	if (typeof value == "number") return value;
	if (isSymbol(value)) return NAN;
	if (isObject(value)) {
		var other = typeof value.valueOf == "function" ? value.valueOf() : value;
		value = isObject(other) ? other + "" : other;
	}
	if (typeof value != "string") return value === 0 ? value : +value;
	value = baseTrim(value);
	var isBinary = reIsBinary.test(value);
	return isBinary || reIsOctal.test(value) ? freeParseInt(value.slice(2), isBinary ? 2 : 8) : reIsBadHex.test(value) ? NAN : +value;
}
//#endregion
//#region node_modules/lodash-es/identity.js
/**
* This method returns the first argument it receives.
*
* @static
* @since 0.1.0
* @memberOf _
* @category Util
* @param {*} value Any value.
* @returns {*} Returns `value`.
* @example
*
* var object = { 'a': 1 };
*
* console.log(_.identity(object) === object);
* // => true
*/
function identity(value) {
	return value;
}
//#endregion
//#region node_modules/lodash-es/isFunction.js
/** `Object#toString` result references. */
var asyncTag = "[object AsyncFunction]", funcTag$2 = "[object Function]", genTag$1 = "[object GeneratorFunction]", proxyTag = "[object Proxy]";
/**
* Checks if `value` is classified as a `Function` object.
*
* @static
* @memberOf _
* @since 0.1.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a function, else `false`.
* @example
*
* _.isFunction(_);
* // => true
*
* _.isFunction(/abc/);
* // => false
*/
function isFunction(value) {
	if (!isObject(value)) return false;
	var tag = baseGetTag(value);
	return tag == funcTag$2 || tag == genTag$1 || tag == asyncTag || tag == proxyTag;
}
//#endregion
//#region node_modules/lodash-es/_coreJsData.js
/** Used to detect overreaching core-js shims. */
var coreJsData = root["__core-js_shared__"];
//#endregion
//#region node_modules/lodash-es/_isMasked.js
/** Used to detect methods masquerading as native. */
var maskSrcKey = function() {
	var uid = /[^.]+$/.exec(coreJsData && coreJsData.keys && coreJsData.keys.IE_PROTO || "");
	return uid ? "Symbol(src)_1." + uid : "";
}();
/**
* Checks if `func` has its source masked.
*
* @private
* @param {Function} func The function to check.
* @returns {boolean} Returns `true` if `func` is masked, else `false`.
*/
function isMasked(func) {
	return !!maskSrcKey && maskSrcKey in func;
}
//#endregion
//#region node_modules/lodash-es/_toSource.js
/** Used to resolve the decompiled source of functions. */
var funcToString$2 = Function.prototype.toString;
/**
* Converts `func` to its source code.
*
* @private
* @param {Function} func The function to convert.
* @returns {string} Returns the source code.
*/
function toSource(func) {
	if (func != null) {
		try {
			return funcToString$2.call(func);
		} catch (e) {}
		try {
			return func + "";
		} catch (e) {}
	}
	return "";
}
//#endregion
//#region node_modules/lodash-es/_baseIsNative.js
/**
* Used to match `RegExp`
* [syntax characters](http://ecma-international.org/ecma-262/7.0/#sec-patterns).
*/
var reRegExpChar = /[\\^$.*+?()[\]{}|]/g;
/** Used to detect host constructors (Safari). */
var reIsHostCtor = /^\[object .+?Constructor\]$/;
/** Used for built-in method references. */
var funcProto$1 = Function.prototype, objectProto$3 = Object.prototype;
/** Used to resolve the decompiled source of functions. */
var funcToString$1 = funcProto$1.toString;
/** Used to check objects for own properties. */
var hasOwnProperty$12 = objectProto$3.hasOwnProperty;
/** Used to detect if a method is native. */
var reIsNative = RegExp("^" + funcToString$1.call(hasOwnProperty$12).replace(reRegExpChar, "\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") + "$");
/**
* The base implementation of `_.isNative` without bad shim checks.
*
* @private
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a native function,
*  else `false`.
*/
function baseIsNative(value) {
	if (!isObject(value) || isMasked(value)) return false;
	return (isFunction(value) ? reIsNative : reIsHostCtor).test(toSource(value));
}
//#endregion
//#region node_modules/lodash-es/_getValue.js
/**
* Gets the value at `key` of `object`.
*
* @private
* @param {Object} [object] The object to query.
* @param {string} key The key of the property to get.
* @returns {*} Returns the property value.
*/
function getValue(object, key) {
	return object == null ? void 0 : object[key];
}
//#endregion
//#region node_modules/lodash-es/_getNative.js
/**
* Gets the native function at `key` of `object`.
*
* @private
* @param {Object} object The object to query.
* @param {string} key The key of the method to get.
* @returns {*} Returns the function if it's native, else `undefined`.
*/
function getNative(object, key) {
	var value = getValue(object, key);
	return baseIsNative(value) ? value : void 0;
}
//#endregion
//#region node_modules/lodash-es/_WeakMap.js
var WeakMap$1 = getNative(root, "WeakMap");
//#endregion
//#region node_modules/lodash-es/_baseCreate.js
/** Built-in value references. */
var objectCreate = Object.create;
/**
* The base implementation of `_.create` without support for assigning
* properties to the created object.
*
* @private
* @param {Object} proto The object to inherit from.
* @returns {Object} Returns the new object.
*/
var baseCreate = function() {
	function object() {}
	return function(proto) {
		if (!isObject(proto)) return {};
		if (objectCreate) return objectCreate(proto);
		object.prototype = proto;
		var result = new object();
		object.prototype = void 0;
		return result;
	};
}();
//#endregion
//#region node_modules/lodash-es/_apply.js
/**
* A faster alternative to `Function#apply`, this function invokes `func`
* with the `this` binding of `thisArg` and the arguments of `args`.
*
* @private
* @param {Function} func The function to invoke.
* @param {*} thisArg The `this` binding of `func`.
* @param {Array} args The arguments to invoke `func` with.
* @returns {*} Returns the result of `func`.
*/
function apply(func, thisArg, args) {
	switch (args.length) {
		case 0: return func.call(thisArg);
		case 1: return func.call(thisArg, args[0]);
		case 2: return func.call(thisArg, args[0], args[1]);
		case 3: return func.call(thisArg, args[0], args[1], args[2]);
	}
	return func.apply(thisArg, args);
}
//#endregion
//#region node_modules/lodash-es/_copyArray.js
/**
* Copies the values of `source` to `array`.
*
* @private
* @param {Array} source The array to copy values from.
* @param {Array} [array=[]] The array to copy values to.
* @returns {Array} Returns `array`.
*/
function copyArray(source, array) {
	var index = -1, length = source.length;
	array || (array = Array(length));
	while (++index < length) array[index] = source[index];
	return array;
}
//#endregion
//#region node_modules/lodash-es/_shortOut.js
/** Used to detect hot functions by number of calls within a span of milliseconds. */
var HOT_COUNT = 800, HOT_SPAN = 16;
var nativeNow = Date.now;
/**
* Creates a function that'll short out and invoke `identity` instead
* of `func` when it's called `HOT_COUNT` or more times in `HOT_SPAN`
* milliseconds.
*
* @private
* @param {Function} func The function to restrict.
* @returns {Function} Returns the new shortable function.
*/
function shortOut(func) {
	var count = 0, lastCalled = 0;
	return function() {
		var stamp = nativeNow(), remaining = HOT_SPAN - (stamp - lastCalled);
		lastCalled = stamp;
		if (remaining > 0) {
			if (++count >= HOT_COUNT) return arguments[0];
		} else count = 0;
		return func.apply(void 0, arguments);
	};
}
//#endregion
//#region node_modules/lodash-es/constant.js
/**
* Creates a function that returns `value`.
*
* @static
* @memberOf _
* @since 2.4.0
* @category Util
* @param {*} value The value to return from the new function.
* @returns {Function} Returns the new constant function.
* @example
*
* var objects = _.times(2, _.constant({ 'a': 1 }));
*
* console.log(objects);
* // => [{ 'a': 1 }, { 'a': 1 }]
*
* console.log(objects[0] === objects[1]);
* // => true
*/
function constant(value) {
	return function() {
		return value;
	};
}
//#endregion
//#region node_modules/lodash-es/_defineProperty.js
var defineProperty = function() {
	try {
		var func = getNative(Object, "defineProperty");
		func({}, "", {});
		return func;
	} catch (e) {}
}();
//#endregion
//#region node_modules/lodash-es/_setToString.js
/**
* Sets the `toString` method of `func` to return `string`.
*
* @private
* @param {Function} func The function to modify.
* @param {Function} string The `toString` result.
* @returns {Function} Returns `func`.
*/
var setToString = shortOut(!defineProperty ? identity : function(func, string) {
	return defineProperty(func, "toString", {
		"configurable": true,
		"enumerable": false,
		"value": constant(string),
		"writable": true
	});
});
//#endregion
//#region node_modules/lodash-es/_arrayEach.js
/**
* A specialized version of `_.forEach` for arrays without support for
* iteratee shorthands.
*
* @private
* @param {Array} [array] The array to iterate over.
* @param {Function} iteratee The function invoked per iteration.
* @returns {Array} Returns `array`.
*/
function arrayEach(array, iteratee) {
	var index = -1, length = array == null ? 0 : array.length;
	while (++index < length) if (iteratee(array[index], index, array) === false) break;
	return array;
}
//#endregion
//#region node_modules/lodash-es/_isIndex.js
/** Used as references for various `Number` constants. */
var MAX_SAFE_INTEGER$1 = 9007199254740991;
/** Used to detect unsigned integer values. */
var reIsUint = /^(?:0|[1-9]\d*)$/;
/**
* Checks if `value` is a valid array-like index.
*
* @private
* @param {*} value The value to check.
* @param {number} [length=MAX_SAFE_INTEGER] The upper bounds of a valid index.
* @returns {boolean} Returns `true` if `value` is a valid index, else `false`.
*/
function isIndex(value, length) {
	var type = typeof value;
	length = length == null ? MAX_SAFE_INTEGER$1 : length;
	return !!length && (type == "number" || type != "symbol" && reIsUint.test(value)) && value > -1 && value % 1 == 0 && value < length;
}
//#endregion
//#region node_modules/lodash-es/_baseAssignValue.js
/**
* The base implementation of `assignValue` and `assignMergeValue` without
* value checks.
*
* @private
* @param {Object} object The object to modify.
* @param {string} key The key of the property to assign.
* @param {*} value The value to assign.
*/
function baseAssignValue(object, key, value) {
	if (key == "__proto__" && defineProperty) defineProperty(object, key, {
		"configurable": true,
		"enumerable": true,
		"value": value,
		"writable": true
	});
	else object[key] = value;
}
//#endregion
//#region node_modules/lodash-es/eq.js
/**
* Performs a
* [`SameValueZero`](http://ecma-international.org/ecma-262/7.0/#sec-samevaluezero)
* comparison between two values to determine if they are equivalent.
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to compare.
* @param {*} other The other value to compare.
* @returns {boolean} Returns `true` if the values are equivalent, else `false`.
* @example
*
* var object = { 'a': 1 };
* var other = { 'a': 1 };
*
* _.eq(object, object);
* // => true
*
* _.eq(object, other);
* // => false
*
* _.eq('a', 'a');
* // => true
*
* _.eq('a', Object('a'));
* // => false
*
* _.eq(NaN, NaN);
* // => true
*/
function eq(value, other) {
	return value === other || value !== value && other !== other;
}
//#endregion
//#region node_modules/lodash-es/_assignValue.js
/** Used to check objects for own properties. */
var hasOwnProperty$11 = Object.prototype.hasOwnProperty;
/**
* Assigns `value` to `key` of `object` if the existing value is not equivalent
* using [`SameValueZero`](http://ecma-international.org/ecma-262/7.0/#sec-samevaluezero)
* for equality comparisons.
*
* @private
* @param {Object} object The object to modify.
* @param {string} key The key of the property to assign.
* @param {*} value The value to assign.
*/
function assignValue(object, key, value) {
	var objValue = object[key];
	if (!(hasOwnProperty$11.call(object, key) && eq(objValue, value)) || value === void 0 && !(key in object)) baseAssignValue(object, key, value);
}
//#endregion
//#region node_modules/lodash-es/_copyObject.js
/**
* Copies properties of `source` to `object`.
*
* @private
* @param {Object} source The object to copy properties from.
* @param {Array} props The property identifiers to copy.
* @param {Object} [object={}] The object to copy properties to.
* @param {Function} [customizer] The function to customize copied values.
* @returns {Object} Returns `object`.
*/
function copyObject(source, props, object, customizer) {
	var isNew = !object;
	object || (object = {});
	var index = -1, length = props.length;
	while (++index < length) {
		var key = props[index];
		var newValue = customizer ? customizer(object[key], source[key], key, object, source) : void 0;
		if (newValue === void 0) newValue = source[key];
		if (isNew) baseAssignValue(object, key, newValue);
		else assignValue(object, key, newValue);
	}
	return object;
}
//#endregion
//#region node_modules/lodash-es/_overRest.js
var nativeMax$1 = Math.max;
/**
* A specialized version of `baseRest` which transforms the rest array.
*
* @private
* @param {Function} func The function to apply a rest parameter to.
* @param {number} [start=func.length-1] The start position of the rest parameter.
* @param {Function} transform The rest array transform.
* @returns {Function} Returns the new function.
*/
function overRest(func, start, transform) {
	start = nativeMax$1(start === void 0 ? func.length - 1 : start, 0);
	return function() {
		var args = arguments, index = -1, length = nativeMax$1(args.length - start, 0), array = Array(length);
		while (++index < length) array[index] = args[start + index];
		index = -1;
		var otherArgs = Array(start + 1);
		while (++index < start) otherArgs[index] = args[index];
		otherArgs[start] = transform(array);
		return apply(func, this, otherArgs);
	};
}
//#endregion
//#region node_modules/lodash-es/_baseRest.js
/**
* The base implementation of `_.rest` which doesn't validate or coerce arguments.
*
* @private
* @param {Function} func The function to apply a rest parameter to.
* @param {number} [start=func.length-1] The start position of the rest parameter.
* @returns {Function} Returns the new function.
*/
function baseRest(func, start) {
	return setToString(overRest(func, start, identity), func + "");
}
//#endregion
//#region node_modules/lodash-es/isLength.js
/** Used as references for various `Number` constants. */
var MAX_SAFE_INTEGER = 9007199254740991;
/**
* Checks if `value` is a valid array-like length.
*
* **Note:** This method is loosely based on
* [`ToLength`](http://ecma-international.org/ecma-262/7.0/#sec-tolength).
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a valid length, else `false`.
* @example
*
* _.isLength(3);
* // => true
*
* _.isLength(Number.MIN_VALUE);
* // => false
*
* _.isLength(Infinity);
* // => false
*
* _.isLength('3');
* // => false
*/
function isLength(value) {
	return typeof value == "number" && value > -1 && value % 1 == 0 && value <= MAX_SAFE_INTEGER;
}
//#endregion
//#region node_modules/lodash-es/isArrayLike.js
/**
* Checks if `value` is array-like. A value is considered array-like if it's
* not a function and has a `value.length` that's an integer greater than or
* equal to `0` and less than or equal to `Number.MAX_SAFE_INTEGER`.
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is array-like, else `false`.
* @example
*
* _.isArrayLike([1, 2, 3]);
* // => true
*
* _.isArrayLike(document.body.children);
* // => true
*
* _.isArrayLike('abc');
* // => true
*
* _.isArrayLike(_.noop);
* // => false
*/
function isArrayLike(value) {
	return value != null && isLength(value.length) && !isFunction(value);
}
//#endregion
//#region node_modules/lodash-es/_isIterateeCall.js
/**
* Checks if the given arguments are from an iteratee call.
*
* @private
* @param {*} value The potential iteratee value argument.
* @param {*} index The potential iteratee index or key argument.
* @param {*} object The potential iteratee object argument.
* @returns {boolean} Returns `true` if the arguments are from an iteratee call,
*  else `false`.
*/
function isIterateeCall(value, index, object) {
	if (!isObject(object)) return false;
	var type = typeof index;
	if (type == "number" ? isArrayLike(object) && isIndex(index, object.length) : type == "string" && index in object) return eq(object[index], value);
	return false;
}
//#endregion
//#region node_modules/lodash-es/_createAssigner.js
/**
* Creates a function like `_.assign`.
*
* @private
* @param {Function} assigner The function to assign values.
* @returns {Function} Returns the new assigner function.
*/
function createAssigner(assigner) {
	return baseRest(function(object, sources) {
		var index = -1, length = sources.length, customizer = length > 1 ? sources[length - 1] : void 0, guard = length > 2 ? sources[2] : void 0;
		customizer = assigner.length > 3 && typeof customizer == "function" ? (length--, customizer) : void 0;
		if (guard && isIterateeCall(sources[0], sources[1], guard)) {
			customizer = length < 3 ? void 0 : customizer;
			length = 1;
		}
		object = Object(object);
		while (++index < length) {
			var source = sources[index];
			if (source) assigner(object, source, index, customizer);
		}
		return object;
	});
}
//#endregion
//#region node_modules/lodash-es/_isPrototype.js
/** Used for built-in method references. */
var objectProto$2 = Object.prototype;
/**
* Checks if `value` is likely a prototype object.
*
* @private
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a prototype, else `false`.
*/
function isPrototype(value) {
	var Ctor = value && value.constructor;
	return value === (typeof Ctor == "function" && Ctor.prototype || objectProto$2);
}
//#endregion
//#region node_modules/lodash-es/_baseTimes.js
/**
* The base implementation of `_.times` without support for iteratee shorthands
* or max array length checks.
*
* @private
* @param {number} n The number of times to invoke `iteratee`.
* @param {Function} iteratee The function invoked per iteration.
* @returns {Array} Returns the array of results.
*/
function baseTimes(n, iteratee) {
	var index = -1, result = Array(n);
	while (++index < n) result[index] = iteratee(index);
	return result;
}
//#endregion
//#region node_modules/lodash-es/_baseIsArguments.js
/** `Object#toString` result references. */
var argsTag$3 = "[object Arguments]";
/**
* The base implementation of `_.isArguments`.
*
* @private
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is an `arguments` object,
*/
function baseIsArguments(value) {
	return isObjectLike(value) && baseGetTag(value) == argsTag$3;
}
//#endregion
//#region node_modules/lodash-es/isArguments.js
/** Used for built-in method references. */
var objectProto$1 = Object.prototype;
/** Used to check objects for own properties. */
var hasOwnProperty$10 = objectProto$1.hasOwnProperty;
/** Built-in value references. */
var propertyIsEnumerable$1 = objectProto$1.propertyIsEnumerable;
/**
* Checks if `value` is likely an `arguments` object.
*
* @static
* @memberOf _
* @since 0.1.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is an `arguments` object,
*  else `false`.
* @example
*
* _.isArguments(function() { return arguments; }());
* // => true
*
* _.isArguments([1, 2, 3]);
* // => false
*/
var isArguments = baseIsArguments(function() {
	return arguments;
}()) ? baseIsArguments : function(value) {
	return isObjectLike(value) && hasOwnProperty$10.call(value, "callee") && !propertyIsEnumerable$1.call(value, "callee");
};
//#endregion
//#region node_modules/lodash-es/stubFalse.js
/**
* This method returns `false`.
*
* @static
* @memberOf _
* @since 4.13.0
* @category Util
* @returns {boolean} Returns `false`.
* @example
*
* _.times(2, _.stubFalse);
* // => [false, false]
*/
function stubFalse() {
	return false;
}
//#endregion
//#region node_modules/lodash-es/isBuffer.js
/** Detect free variable `exports`. */
var freeExports$2 = typeof exports == "object" && exports && !exports.nodeType && exports;
/** Detect free variable `module`. */
var freeModule$2 = freeExports$2 && typeof module == "object" && module && !module.nodeType && module;
/** Built-in value references. */
var Buffer$2 = freeModule$2 && freeModule$2.exports === freeExports$2 ? root.Buffer : void 0;
/**
* Checks if `value` is a buffer.
*
* @static
* @memberOf _
* @since 4.3.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a buffer, else `false`.
* @example
*
* _.isBuffer(new Buffer(2));
* // => true
*
* _.isBuffer(new Uint8Array(2));
* // => false
*/
var isBuffer = (Buffer$2 ? Buffer$2.isBuffer : void 0) || stubFalse;
//#endregion
//#region node_modules/lodash-es/_baseIsTypedArray.js
/** `Object#toString` result references. */
var argsTag$2 = "[object Arguments]", arrayTag$2 = "[object Array]", boolTag$3 = "[object Boolean]", dateTag$3 = "[object Date]", errorTag$2 = "[object Error]", funcTag$1 = "[object Function]", mapTag$5 = "[object Map]", numberTag$3 = "[object Number]", objectTag$4 = "[object Object]", regexpTag$3 = "[object RegExp]", setTag$5 = "[object Set]", stringTag$3 = "[object String]", weakMapTag$2 = "[object WeakMap]";
var arrayBufferTag$3 = "[object ArrayBuffer]", dataViewTag$4 = "[object DataView]", float32Tag$2 = "[object Float32Array]", float64Tag$2 = "[object Float64Array]", int8Tag$2 = "[object Int8Array]", int16Tag$2 = "[object Int16Array]", int32Tag$2 = "[object Int32Array]", uint8Tag$2 = "[object Uint8Array]", uint8ClampedTag$2 = "[object Uint8ClampedArray]", uint16Tag$2 = "[object Uint16Array]", uint32Tag$2 = "[object Uint32Array]";
/** Used to identify `toStringTag` values of typed arrays. */
var typedArrayTags = {};
typedArrayTags[float32Tag$2] = typedArrayTags[float64Tag$2] = typedArrayTags[int8Tag$2] = typedArrayTags[int16Tag$2] = typedArrayTags[int32Tag$2] = typedArrayTags[uint8Tag$2] = typedArrayTags[uint8ClampedTag$2] = typedArrayTags[uint16Tag$2] = typedArrayTags[uint32Tag$2] = true;
typedArrayTags[argsTag$2] = typedArrayTags[arrayTag$2] = typedArrayTags[arrayBufferTag$3] = typedArrayTags[boolTag$3] = typedArrayTags[dataViewTag$4] = typedArrayTags[dateTag$3] = typedArrayTags[errorTag$2] = typedArrayTags[funcTag$1] = typedArrayTags[mapTag$5] = typedArrayTags[numberTag$3] = typedArrayTags[objectTag$4] = typedArrayTags[regexpTag$3] = typedArrayTags[setTag$5] = typedArrayTags[stringTag$3] = typedArrayTags[weakMapTag$2] = false;
/**
* The base implementation of `_.isTypedArray` without Node.js optimizations.
*
* @private
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a typed array, else `false`.
*/
function baseIsTypedArray(value) {
	return isObjectLike(value) && isLength(value.length) && !!typedArrayTags[baseGetTag(value)];
}
//#endregion
//#region node_modules/lodash-es/_baseUnary.js
/**
* The base implementation of `_.unary` without support for storing metadata.
*
* @private
* @param {Function} func The function to cap arguments for.
* @returns {Function} Returns the new capped function.
*/
function baseUnary(func) {
	return function(value) {
		return func(value);
	};
}
//#endregion
//#region node_modules/lodash-es/_nodeUtil.js
/** Detect free variable `exports`. */
var freeExports$1 = typeof exports == "object" && exports && !exports.nodeType && exports;
/** Detect free variable `module`. */
var freeModule$1 = freeExports$1 && typeof module == "object" && module && !module.nodeType && module;
/** Detect free variable `process` from Node.js. */
var freeProcess = freeModule$1 && freeModule$1.exports === freeExports$1 && freeGlobal.process;
/** Used to access faster Node.js helpers. */
var nodeUtil = function() {
	try {
		var types = freeModule$1 && freeModule$1.require && freeModule$1.require("util").types;
		if (types) return types;
		return freeProcess && freeProcess.binding && freeProcess.binding("util");
	} catch (e) {}
}();
//#endregion
//#region node_modules/lodash-es/isTypedArray.js
var nodeIsTypedArray = nodeUtil && nodeUtil.isTypedArray;
/**
* Checks if `value` is classified as a typed array.
*
* @static
* @memberOf _
* @since 3.0.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a typed array, else `false`.
* @example
*
* _.isTypedArray(new Uint8Array);
* // => true
*
* _.isTypedArray([]);
* // => false
*/
var isTypedArray = nodeIsTypedArray ? baseUnary(nodeIsTypedArray) : baseIsTypedArray;
//#endregion
//#region node_modules/lodash-es/_arrayLikeKeys.js
/** Used to check objects for own properties. */
var hasOwnProperty$9 = Object.prototype.hasOwnProperty;
/**
* Creates an array of the enumerable property names of the array-like `value`.
*
* @private
* @param {*} value The value to query.
* @param {boolean} inherited Specify returning inherited property names.
* @returns {Array} Returns the array of property names.
*/
function arrayLikeKeys(value, inherited) {
	var isArr = isArray(value), isArg = !isArr && isArguments(value), isBuff = !isArr && !isArg && isBuffer(value), isType = !isArr && !isArg && !isBuff && isTypedArray(value), skipIndexes = isArr || isArg || isBuff || isType, result = skipIndexes ? baseTimes(value.length, String) : [], length = result.length;
	for (var key in value) if ((inherited || hasOwnProperty$9.call(value, key)) && !(skipIndexes && (key == "length" || isBuff && (key == "offset" || key == "parent") || isType && (key == "buffer" || key == "byteLength" || key == "byteOffset") || isIndex(key, length)))) result.push(key);
	return result;
}
//#endregion
//#region node_modules/lodash-es/_overArg.js
/**
* Creates a unary function that invokes `func` with its argument transformed.
*
* @private
* @param {Function} func The function to wrap.
* @param {Function} transform The argument transform.
* @returns {Function} Returns the new function.
*/
function overArg(func, transform) {
	return function(arg) {
		return func(transform(arg));
	};
}
//#endregion
//#region node_modules/lodash-es/_nativeKeys.js
var nativeKeys = overArg(Object.keys, Object);
//#endregion
//#region node_modules/lodash-es/_baseKeys.js
/** Used to check objects for own properties. */
var hasOwnProperty$8 = Object.prototype.hasOwnProperty;
/**
* The base implementation of `_.keys` which doesn't treat sparse arrays as dense.
*
* @private
* @param {Object} object The object to query.
* @returns {Array} Returns the array of property names.
*/
function baseKeys(object) {
	if (!isPrototype(object)) return nativeKeys(object);
	var result = [];
	for (var key in Object(object)) if (hasOwnProperty$8.call(object, key) && key != "constructor") result.push(key);
	return result;
}
//#endregion
//#region node_modules/lodash-es/keys.js
/**
* Creates an array of the own enumerable property names of `object`.
*
* **Note:** Non-object values are coerced to objects. See the
* [ES spec](http://ecma-international.org/ecma-262/7.0/#sec-object.keys)
* for more details.
*
* @static
* @since 0.1.0
* @memberOf _
* @category Object
* @param {Object} object The object to query.
* @returns {Array} Returns the array of property names.
* @example
*
* function Foo() {
*   this.a = 1;
*   this.b = 2;
* }
*
* Foo.prototype.c = 3;
*
* _.keys(new Foo);
* // => ['a', 'b'] (iteration order is not guaranteed)
*
* _.keys('hi');
* // => ['0', '1']
*/
function keys(object) {
	return isArrayLike(object) ? arrayLikeKeys(object) : baseKeys(object);
}
//#endregion
//#region node_modules/lodash-es/_nativeKeysIn.js
/**
* This function is like
* [`Object.keys`](http://ecma-international.org/ecma-262/7.0/#sec-object.keys)
* except that it includes inherited enumerable properties.
*
* @private
* @param {Object} object The object to query.
* @returns {Array} Returns the array of property names.
*/
function nativeKeysIn(object) {
	var result = [];
	if (object != null) for (var key in Object(object)) result.push(key);
	return result;
}
//#endregion
//#region node_modules/lodash-es/_baseKeysIn.js
/** Used to check objects for own properties. */
var hasOwnProperty$7 = Object.prototype.hasOwnProperty;
/**
* The base implementation of `_.keysIn` which doesn't treat sparse arrays as dense.
*
* @private
* @param {Object} object The object to query.
* @returns {Array} Returns the array of property names.
*/
function baseKeysIn(object) {
	if (!isObject(object)) return nativeKeysIn(object);
	var isProto = isPrototype(object), result = [];
	for (var key in object) if (!(key == "constructor" && (isProto || !hasOwnProperty$7.call(object, key)))) result.push(key);
	return result;
}
//#endregion
//#region node_modules/lodash-es/keysIn.js
/**
* Creates an array of the own and inherited enumerable property names of `object`.
*
* **Note:** Non-object values are coerced to objects.
*
* @static
* @memberOf _
* @since 3.0.0
* @category Object
* @param {Object} object The object to query.
* @returns {Array} Returns the array of property names.
* @example
*
* function Foo() {
*   this.a = 1;
*   this.b = 2;
* }
*
* Foo.prototype.c = 3;
*
* _.keysIn(new Foo);
* // => ['a', 'b', 'c'] (iteration order is not guaranteed)
*/
function keysIn(object) {
	return isArrayLike(object) ? arrayLikeKeys(object, true) : baseKeysIn(object);
}
//#endregion
//#region node_modules/lodash-es/_isKey.js
/** Used to match property names within property paths. */
var reIsDeepProp = /\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/, reIsPlainProp = /^\w*$/;
/**
* Checks if `value` is a property name and not a property path.
*
* @private
* @param {*} value The value to check.
* @param {Object} [object] The object to query keys on.
* @returns {boolean} Returns `true` if `value` is a property name, else `false`.
*/
function isKey(value, object) {
	if (isArray(value)) return false;
	var type = typeof value;
	if (type == "number" || type == "symbol" || type == "boolean" || value == null || isSymbol(value)) return true;
	return reIsPlainProp.test(value) || !reIsDeepProp.test(value) || object != null && value in Object(object);
}
//#endregion
//#region node_modules/lodash-es/_nativeCreate.js
var nativeCreate = getNative(Object, "create");
//#endregion
//#region node_modules/lodash-es/_hashClear.js
/**
* Removes all key-value entries from the hash.
*
* @private
* @name clear
* @memberOf Hash
*/
function hashClear() {
	this.__data__ = nativeCreate ? nativeCreate(null) : {};
	this.size = 0;
}
//#endregion
//#region node_modules/lodash-es/_hashDelete.js
/**
* Removes `key` and its value from the hash.
*
* @private
* @name delete
* @memberOf Hash
* @param {Object} hash The hash to modify.
* @param {string} key The key of the value to remove.
* @returns {boolean} Returns `true` if the entry was removed, else `false`.
*/
function hashDelete(key) {
	var result = this.has(key) && delete this.__data__[key];
	this.size -= result ? 1 : 0;
	return result;
}
//#endregion
//#region node_modules/lodash-es/_hashGet.js
/** Used to stand-in for `undefined` hash values. */
var HASH_UNDEFINED$2 = "__lodash_hash_undefined__";
/** Used to check objects for own properties. */
var hasOwnProperty$6 = Object.prototype.hasOwnProperty;
/**
* Gets the hash value for `key`.
*
* @private
* @name get
* @memberOf Hash
* @param {string} key The key of the value to get.
* @returns {*} Returns the entry value.
*/
function hashGet(key) {
	var data = this.__data__;
	if (nativeCreate) {
		var result = data[key];
		return result === HASH_UNDEFINED$2 ? void 0 : result;
	}
	return hasOwnProperty$6.call(data, key) ? data[key] : void 0;
}
//#endregion
//#region node_modules/lodash-es/_hashHas.js
/** Used to check objects for own properties. */
var hasOwnProperty$5 = Object.prototype.hasOwnProperty;
/**
* Checks if a hash value for `key` exists.
*
* @private
* @name has
* @memberOf Hash
* @param {string} key The key of the entry to check.
* @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.
*/
function hashHas(key) {
	var data = this.__data__;
	return nativeCreate ? data[key] !== void 0 : hasOwnProperty$5.call(data, key);
}
//#endregion
//#region node_modules/lodash-es/_hashSet.js
/** Used to stand-in for `undefined` hash values. */
var HASH_UNDEFINED$1 = "__lodash_hash_undefined__";
/**
* Sets the hash `key` to `value`.
*
* @private
* @name set
* @memberOf Hash
* @param {string} key The key of the value to set.
* @param {*} value The value to set.
* @returns {Object} Returns the hash instance.
*/
function hashSet(key, value) {
	var data = this.__data__;
	this.size += this.has(key) ? 0 : 1;
	data[key] = nativeCreate && value === void 0 ? HASH_UNDEFINED$1 : value;
	return this;
}
//#endregion
//#region node_modules/lodash-es/_Hash.js
/**
* Creates a hash object.
*
* @private
* @constructor
* @param {Array} [entries] The key-value pairs to cache.
*/
function Hash(entries) {
	var index = -1, length = entries == null ? 0 : entries.length;
	this.clear();
	while (++index < length) {
		var entry = entries[index];
		this.set(entry[0], entry[1]);
	}
}
Hash.prototype.clear = hashClear;
Hash.prototype["delete"] = hashDelete;
Hash.prototype.get = hashGet;
Hash.prototype.has = hashHas;
Hash.prototype.set = hashSet;
//#endregion
//#region node_modules/lodash-es/_listCacheClear.js
/**
* Removes all key-value entries from the list cache.
*
* @private
* @name clear
* @memberOf ListCache
*/
function listCacheClear() {
	this.__data__ = [];
	this.size = 0;
}
//#endregion
//#region node_modules/lodash-es/_assocIndexOf.js
/**
* Gets the index at which the `key` is found in `array` of key-value pairs.
*
* @private
* @param {Array} array The array to inspect.
* @param {*} key The key to search for.
* @returns {number} Returns the index of the matched value, else `-1`.
*/
function assocIndexOf(array, key) {
	var length = array.length;
	while (length--) if (eq(array[length][0], key)) return length;
	return -1;
}
//#endregion
//#region node_modules/lodash-es/_listCacheDelete.js
/** Built-in value references. */
var splice = Array.prototype.splice;
/**
* Removes `key` and its value from the list cache.
*
* @private
* @name delete
* @memberOf ListCache
* @param {string} key The key of the value to remove.
* @returns {boolean} Returns `true` if the entry was removed, else `false`.
*/
function listCacheDelete(key) {
	var data = this.__data__, index = assocIndexOf(data, key);
	if (index < 0) return false;
	if (index == data.length - 1) data.pop();
	else splice.call(data, index, 1);
	--this.size;
	return true;
}
//#endregion
//#region node_modules/lodash-es/_listCacheGet.js
/**
* Gets the list cache value for `key`.
*
* @private
* @name get
* @memberOf ListCache
* @param {string} key The key of the value to get.
* @returns {*} Returns the entry value.
*/
function listCacheGet(key) {
	var data = this.__data__, index = assocIndexOf(data, key);
	return index < 0 ? void 0 : data[index][1];
}
//#endregion
//#region node_modules/lodash-es/_listCacheHas.js
/**
* Checks if a list cache value for `key` exists.
*
* @private
* @name has
* @memberOf ListCache
* @param {string} key The key of the entry to check.
* @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.
*/
function listCacheHas(key) {
	return assocIndexOf(this.__data__, key) > -1;
}
//#endregion
//#region node_modules/lodash-es/_listCacheSet.js
/**
* Sets the list cache `key` to `value`.
*
* @private
* @name set
* @memberOf ListCache
* @param {string} key The key of the value to set.
* @param {*} value The value to set.
* @returns {Object} Returns the list cache instance.
*/
function listCacheSet(key, value) {
	var data = this.__data__, index = assocIndexOf(data, key);
	if (index < 0) {
		++this.size;
		data.push([key, value]);
	} else data[index][1] = value;
	return this;
}
//#endregion
//#region node_modules/lodash-es/_ListCache.js
/**
* Creates an list cache object.
*
* @private
* @constructor
* @param {Array} [entries] The key-value pairs to cache.
*/
function ListCache(entries) {
	var index = -1, length = entries == null ? 0 : entries.length;
	this.clear();
	while (++index < length) {
		var entry = entries[index];
		this.set(entry[0], entry[1]);
	}
}
ListCache.prototype.clear = listCacheClear;
ListCache.prototype["delete"] = listCacheDelete;
ListCache.prototype.get = listCacheGet;
ListCache.prototype.has = listCacheHas;
ListCache.prototype.set = listCacheSet;
//#endregion
//#region node_modules/lodash-es/_Map.js
var Map$1 = getNative(root, "Map");
//#endregion
//#region node_modules/lodash-es/_mapCacheClear.js
/**
* Removes all key-value entries from the map.
*
* @private
* @name clear
* @memberOf MapCache
*/
function mapCacheClear() {
	this.size = 0;
	this.__data__ = {
		"hash": new Hash(),
		"map": new (Map$1 || ListCache)(),
		"string": new Hash()
	};
}
//#endregion
//#region node_modules/lodash-es/_isKeyable.js
/**
* Checks if `value` is suitable for use as unique object key.
*
* @private
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is suitable, else `false`.
*/
function isKeyable(value) {
	var type = typeof value;
	return type == "string" || type == "number" || type == "symbol" || type == "boolean" ? value !== "__proto__" : value === null;
}
//#endregion
//#region node_modules/lodash-es/_getMapData.js
/**
* Gets the data for `map`.
*
* @private
* @param {Object} map The map to query.
* @param {string} key The reference key.
* @returns {*} Returns the map data.
*/
function getMapData(map, key) {
	var data = map.__data__;
	return isKeyable(key) ? data[typeof key == "string" ? "string" : "hash"] : data.map;
}
//#endregion
//#region node_modules/lodash-es/_mapCacheDelete.js
/**
* Removes `key` and its value from the map.
*
* @private
* @name delete
* @memberOf MapCache
* @param {string} key The key of the value to remove.
* @returns {boolean} Returns `true` if the entry was removed, else `false`.
*/
function mapCacheDelete(key) {
	var result = getMapData(this, key)["delete"](key);
	this.size -= result ? 1 : 0;
	return result;
}
//#endregion
//#region node_modules/lodash-es/_mapCacheGet.js
/**
* Gets the map value for `key`.
*
* @private
* @name get
* @memberOf MapCache
* @param {string} key The key of the value to get.
* @returns {*} Returns the entry value.
*/
function mapCacheGet(key) {
	return getMapData(this, key).get(key);
}
//#endregion
//#region node_modules/lodash-es/_mapCacheHas.js
/**
* Checks if a map value for `key` exists.
*
* @private
* @name has
* @memberOf MapCache
* @param {string} key The key of the entry to check.
* @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.
*/
function mapCacheHas(key) {
	return getMapData(this, key).has(key);
}
//#endregion
//#region node_modules/lodash-es/_mapCacheSet.js
/**
* Sets the map `key` to `value`.
*
* @private
* @name set
* @memberOf MapCache
* @param {string} key The key of the value to set.
* @param {*} value The value to set.
* @returns {Object} Returns the map cache instance.
*/
function mapCacheSet(key, value) {
	var data = getMapData(this, key), size = data.size;
	data.set(key, value);
	this.size += data.size == size ? 0 : 1;
	return this;
}
//#endregion
//#region node_modules/lodash-es/_MapCache.js
/**
* Creates a map cache object to store key-value pairs.
*
* @private
* @constructor
* @param {Array} [entries] The key-value pairs to cache.
*/
function MapCache(entries) {
	var index = -1, length = entries == null ? 0 : entries.length;
	this.clear();
	while (++index < length) {
		var entry = entries[index];
		this.set(entry[0], entry[1]);
	}
}
MapCache.prototype.clear = mapCacheClear;
MapCache.prototype["delete"] = mapCacheDelete;
MapCache.prototype.get = mapCacheGet;
MapCache.prototype.has = mapCacheHas;
MapCache.prototype.set = mapCacheSet;
//#endregion
//#region node_modules/lodash-es/memoize.js
/** Error message constants. */
var FUNC_ERROR_TEXT$1 = "Expected a function";
/**
* Creates a function that memoizes the result of `func`. If `resolver` is
* provided, it determines the cache key for storing the result based on the
* arguments provided to the memoized function. By default, the first argument
* provided to the memoized function is used as the map cache key. The `func`
* is invoked with the `this` binding of the memoized function.
*
* **Note:** The cache is exposed as the `cache` property on the memoized
* function. Its creation may be customized by replacing the `_.memoize.Cache`
* constructor with one whose instances implement the
* [`Map`](http://ecma-international.org/ecma-262/7.0/#sec-properties-of-the-map-prototype-object)
* method interface of `clear`, `delete`, `get`, `has`, and `set`.
*
* @static
* @memberOf _
* @since 0.1.0
* @category Function
* @param {Function} func The function to have its output memoized.
* @param {Function} [resolver] The function to resolve the cache key.
* @returns {Function} Returns the new memoized function.
* @example
*
* var object = { 'a': 1, 'b': 2 };
* var other = { 'c': 3, 'd': 4 };
*
* var values = _.memoize(_.values);
* values(object);
* // => [1, 2]
*
* values(other);
* // => [3, 4]
*
* object.a = 2;
* values(object);
* // => [1, 2]
*
* // Modify the result cache.
* values.cache.set(object, ['a', 'b']);
* values(object);
* // => ['a', 'b']
*
* // Replace `_.memoize.Cache`.
* _.memoize.Cache = WeakMap;
*/
function memoize(func, resolver) {
	if (typeof func != "function" || resolver != null && typeof resolver != "function") throw new TypeError(FUNC_ERROR_TEXT$1);
	var memoized = function() {
		var args = arguments, key = resolver ? resolver.apply(this, args) : args[0], cache = memoized.cache;
		if (cache.has(key)) return cache.get(key);
		var result = func.apply(this, args);
		memoized.cache = cache.set(key, result) || cache;
		return result;
	};
	memoized.cache = new (memoize.Cache || MapCache)();
	return memoized;
}
memoize.Cache = MapCache;
//#endregion
//#region node_modules/lodash-es/_memoizeCapped.js
/** Used as the maximum memoize cache size. */
var MAX_MEMOIZE_SIZE = 500;
/**
* A specialized version of `_.memoize` which clears the memoized function's
* cache when it exceeds `MAX_MEMOIZE_SIZE`.
*
* @private
* @param {Function} func The function to have its output memoized.
* @returns {Function} Returns the new memoized function.
*/
function memoizeCapped(func) {
	var result = memoize(func, function(key) {
		if (cache.size === MAX_MEMOIZE_SIZE) cache.clear();
		return key;
	});
	var cache = result.cache;
	return result;
}
//#endregion
//#region node_modules/lodash-es/_stringToPath.js
/** Used to match property names within property paths. */
var rePropName = /[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g;
/** Used to match backslashes in property paths. */
var reEscapeChar = /\\(\\)?/g;
/**
* Converts `string` to a property path array.
*
* @private
* @param {string} string The string to convert.
* @returns {Array} Returns the property path array.
*/
var stringToPath = memoizeCapped(function(string) {
	var result = [];
	if (string.charCodeAt(0) === 46) result.push("");
	string.replace(rePropName, function(match, number, quote, subString) {
		result.push(quote ? subString.replace(reEscapeChar, "$1") : number || match);
	});
	return result;
});
//#endregion
//#region node_modules/lodash-es/toString.js
/**
* Converts `value` to a string. An empty string is returned for `null`
* and `undefined` values. The sign of `-0` is preserved.
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to convert.
* @returns {string} Returns the converted string.
* @example
*
* _.toString(null);
* // => ''
*
* _.toString(-0);
* // => '-0'
*
* _.toString([1, 2, 3]);
* // => '1,2,3'
*/
function toString(value) {
	return value == null ? "" : baseToString(value);
}
//#endregion
//#region node_modules/lodash-es/_castPath.js
/**
* Casts `value` to a path array if it's not one.
*
* @private
* @param {*} value The value to inspect.
* @param {Object} [object] The object to query keys on.
* @returns {Array} Returns the cast property path array.
*/
function castPath(value, object) {
	if (isArray(value)) return value;
	return isKey(value, object) ? [value] : stringToPath(toString(value));
}
//#endregion
//#region node_modules/lodash-es/_toKey.js
/** Used as references for various `Number` constants. */
var INFINITY = Infinity;
/**
* Converts `value` to a string key if it's not a string or symbol.
*
* @private
* @param {*} value The value to inspect.
* @returns {string|symbol} Returns the key.
*/
function toKey(value) {
	if (typeof value == "string" || isSymbol(value)) return value;
	var result = value + "";
	return result == "0" && 1 / value == -INFINITY ? "-0" : result;
}
//#endregion
//#region node_modules/lodash-es/_baseGet.js
/**
* The base implementation of `_.get` without support for default values.
*
* @private
* @param {Object} object The object to query.
* @param {Array|string} path The path of the property to get.
* @returns {*} Returns the resolved value.
*/
function baseGet(object, path) {
	path = castPath(path, object);
	var index = 0, length = path.length;
	while (object != null && index < length) object = object[toKey(path[index++])];
	return index && index == length ? object : void 0;
}
//#endregion
//#region node_modules/lodash-es/get.js
/**
* Gets the value at `path` of `object`. If the resolved value is
* `undefined`, the `defaultValue` is returned in its place.
*
* @static
* @memberOf _
* @since 3.7.0
* @category Object
* @param {Object} object The object to query.
* @param {Array|string} path The path of the property to get.
* @param {*} [defaultValue] The value returned for `undefined` resolved values.
* @returns {*} Returns the resolved value.
* @example
*
* var object = { 'a': [{ 'b': { 'c': 3 } }] };
*
* _.get(object, 'a[0].b.c');
* // => 3
*
* _.get(object, ['a', '0', 'b', 'c']);
* // => 3
*
* _.get(object, 'a.b.c', 'default');
* // => 'default'
*/
function get(object, path, defaultValue) {
	var result = object == null ? void 0 : baseGet(object, path);
	return result === void 0 ? defaultValue : result;
}
//#endregion
//#region node_modules/lodash-es/_arrayPush.js
/**
* Appends the elements of `values` to `array`.
*
* @private
* @param {Array} array The array to modify.
* @param {Array} values The values to append.
* @returns {Array} Returns `array`.
*/
function arrayPush(array, values) {
	var index = -1, length = values.length, offset = array.length;
	while (++index < length) array[offset + index] = values[index];
	return array;
}
//#endregion
//#region node_modules/lodash-es/_getPrototype.js
/** Built-in value references. */
var getPrototype = overArg(Object.getPrototypeOf, Object);
//#endregion
//#region node_modules/lodash-es/isPlainObject.js
/** `Object#toString` result references. */
var objectTag$3 = "[object Object]";
/** Used for built-in method references. */
var funcProto = Function.prototype, objectProto = Object.prototype;
/** Used to resolve the decompiled source of functions. */
var funcToString = funcProto.toString;
/** Used to check objects for own properties. */
var hasOwnProperty$4 = objectProto.hasOwnProperty;
/** Used to infer the `Object` constructor. */
var objectCtorString = funcToString.call(Object);
/**
* Checks if `value` is a plain object, that is, an object created by the
* `Object` constructor or one with a `[[Prototype]]` of `null`.
*
* @static
* @memberOf _
* @since 0.8.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a plain object, else `false`.
* @example
*
* function Foo() {
*   this.a = 1;
* }
*
* _.isPlainObject(new Foo);
* // => false
*
* _.isPlainObject([1, 2, 3]);
* // => false
*
* _.isPlainObject({ 'x': 0, 'y': 0 });
* // => true
*
* _.isPlainObject(Object.create(null));
* // => true
*/
function isPlainObject(value) {
	if (!isObjectLike(value) || baseGetTag(value) != objectTag$3) return false;
	var proto = getPrototype(value);
	if (proto === null) return true;
	var Ctor = hasOwnProperty$4.call(proto, "constructor") && proto.constructor;
	return typeof Ctor == "function" && Ctor instanceof Ctor && funcToString.call(Ctor) == objectCtorString;
}
//#endregion
//#region node_modules/lodash-es/_basePropertyOf.js
/**
* The base implementation of `_.propertyOf` without support for deep paths.
*
* @private
* @param {Object} object The object to query.
* @returns {Function} Returns the new accessor function.
*/
function basePropertyOf(object) {
	return function(key) {
		return object == null ? void 0 : object[key];
	};
}
//#endregion
//#region node_modules/lodash-es/_stackClear.js
/**
* Removes all key-value entries from the stack.
*
* @private
* @name clear
* @memberOf Stack
*/
function stackClear() {
	this.__data__ = new ListCache();
	this.size = 0;
}
//#endregion
//#region node_modules/lodash-es/_stackDelete.js
/**
* Removes `key` and its value from the stack.
*
* @private
* @name delete
* @memberOf Stack
* @param {string} key The key of the value to remove.
* @returns {boolean} Returns `true` if the entry was removed, else `false`.
*/
function stackDelete(key) {
	var data = this.__data__, result = data["delete"](key);
	this.size = data.size;
	return result;
}
//#endregion
//#region node_modules/lodash-es/_stackGet.js
/**
* Gets the stack value for `key`.
*
* @private
* @name get
* @memberOf Stack
* @param {string} key The key of the value to get.
* @returns {*} Returns the entry value.
*/
function stackGet(key) {
	return this.__data__.get(key);
}
//#endregion
//#region node_modules/lodash-es/_stackHas.js
/**
* Checks if a stack value for `key` exists.
*
* @private
* @name has
* @memberOf Stack
* @param {string} key The key of the entry to check.
* @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.
*/
function stackHas(key) {
	return this.__data__.has(key);
}
//#endregion
//#region node_modules/lodash-es/_stackSet.js
/** Used as the size to enable large array optimizations. */
var LARGE_ARRAY_SIZE = 200;
/**
* Sets the stack `key` to `value`.
*
* @private
* @name set
* @memberOf Stack
* @param {string} key The key of the value to set.
* @param {*} value The value to set.
* @returns {Object} Returns the stack cache instance.
*/
function stackSet(key, value) {
	var data = this.__data__;
	if (data instanceof ListCache) {
		var pairs = data.__data__;
		if (!Map$1 || pairs.length < LARGE_ARRAY_SIZE - 1) {
			pairs.push([key, value]);
			this.size = ++data.size;
			return this;
		}
		data = this.__data__ = new MapCache(pairs);
	}
	data.set(key, value);
	this.size = data.size;
	return this;
}
//#endregion
//#region node_modules/lodash-es/_Stack.js
/**
* Creates a stack cache object to store key-value pairs.
*
* @private
* @constructor
* @param {Array} [entries] The key-value pairs to cache.
*/
function Stack(entries) {
	var data = this.__data__ = new ListCache(entries);
	this.size = data.size;
}
Stack.prototype.clear = stackClear;
Stack.prototype["delete"] = stackDelete;
Stack.prototype.get = stackGet;
Stack.prototype.has = stackHas;
Stack.prototype.set = stackSet;
//#endregion
//#region node_modules/lodash-es/_baseAssign.js
/**
* The base implementation of `_.assign` without support for multiple sources
* or `customizer` functions.
*
* @private
* @param {Object} object The destination object.
* @param {Object} source The source object.
* @returns {Object} Returns `object`.
*/
function baseAssign(object, source) {
	return object && copyObject(source, keys(source), object);
}
//#endregion
//#region node_modules/lodash-es/_baseAssignIn.js
/**
* The base implementation of `_.assignIn` without support for multiple sources
* or `customizer` functions.
*
* @private
* @param {Object} object The destination object.
* @param {Object} source The source object.
* @returns {Object} Returns `object`.
*/
function baseAssignIn(object, source) {
	return object && copyObject(source, keysIn(source), object);
}
//#endregion
//#region node_modules/lodash-es/_cloneBuffer.js
/** Detect free variable `exports`. */
var freeExports = typeof exports == "object" && exports && !exports.nodeType && exports;
/** Detect free variable `module`. */
var freeModule = freeExports && typeof module == "object" && module && !module.nodeType && module;
/** Built-in value references. */
var Buffer$1 = freeModule && freeModule.exports === freeExports ? root.Buffer : void 0, allocUnsafe = Buffer$1 ? Buffer$1.allocUnsafe : void 0;
/**
* Creates a clone of  `buffer`.
*
* @private
* @param {Buffer} buffer The buffer to clone.
* @param {boolean} [isDeep] Specify a deep clone.
* @returns {Buffer} Returns the cloned buffer.
*/
function cloneBuffer(buffer, isDeep) {
	if (isDeep) return buffer.slice();
	var length = buffer.length, result = allocUnsafe ? allocUnsafe(length) : new buffer.constructor(length);
	buffer.copy(result);
	return result;
}
//#endregion
//#region node_modules/lodash-es/_arrayFilter.js
/**
* A specialized version of `_.filter` for arrays without support for
* iteratee shorthands.
*
* @private
* @param {Array} [array] The array to iterate over.
* @param {Function} predicate The function invoked per iteration.
* @returns {Array} Returns the new filtered array.
*/
function arrayFilter(array, predicate) {
	var index = -1, length = array == null ? 0 : array.length, resIndex = 0, result = [];
	while (++index < length) {
		var value = array[index];
		if (predicate(value, index, array)) result[resIndex++] = value;
	}
	return result;
}
//#endregion
//#region node_modules/lodash-es/stubArray.js
/**
* This method returns a new empty array.
*
* @static
* @memberOf _
* @since 4.13.0
* @category Util
* @returns {Array} Returns the new empty array.
* @example
*
* var arrays = _.times(2, _.stubArray);
*
* console.log(arrays);
* // => [[], []]
*
* console.log(arrays[0] === arrays[1]);
* // => false
*/
function stubArray() {
	return [];
}
//#endregion
//#region node_modules/lodash-es/_getSymbols.js
/** Built-in value references. */
var propertyIsEnumerable = Object.prototype.propertyIsEnumerable;
var nativeGetSymbols = Object.getOwnPropertySymbols;
/**
* Creates an array of the own enumerable symbols of `object`.
*
* @private
* @param {Object} object The object to query.
* @returns {Array} Returns the array of symbols.
*/
var getSymbols = !nativeGetSymbols ? stubArray : function(object) {
	if (object == null) return [];
	object = Object(object);
	return arrayFilter(nativeGetSymbols(object), function(symbol) {
		return propertyIsEnumerable.call(object, symbol);
	});
};
//#endregion
//#region node_modules/lodash-es/_copySymbols.js
/**
* Copies own symbols of `source` to `object`.
*
* @private
* @param {Object} source The object to copy symbols from.
* @param {Object} [object={}] The object to copy symbols to.
* @returns {Object} Returns `object`.
*/
function copySymbols(source, object) {
	return copyObject(source, getSymbols(source), object);
}
//#endregion
//#region node_modules/lodash-es/_getSymbolsIn.js
/**
* Creates an array of the own and inherited enumerable symbols of `object`.
*
* @private
* @param {Object} object The object to query.
* @returns {Array} Returns the array of symbols.
*/
var getSymbolsIn = !Object.getOwnPropertySymbols ? stubArray : function(object) {
	var result = [];
	while (object) {
		arrayPush(result, getSymbols(object));
		object = getPrototype(object);
	}
	return result;
};
//#endregion
//#region node_modules/lodash-es/_copySymbolsIn.js
/**
* Copies own and inherited symbols of `source` to `object`.
*
* @private
* @param {Object} source The object to copy symbols from.
* @param {Object} [object={}] The object to copy symbols to.
* @returns {Object} Returns `object`.
*/
function copySymbolsIn(source, object) {
	return copyObject(source, getSymbolsIn(source), object);
}
//#endregion
//#region node_modules/lodash-es/_baseGetAllKeys.js
/**
* The base implementation of `getAllKeys` and `getAllKeysIn` which uses
* `keysFunc` and `symbolsFunc` to get the enumerable property names and
* symbols of `object`.
*
* @private
* @param {Object} object The object to query.
* @param {Function} keysFunc The function to get the keys of `object`.
* @param {Function} symbolsFunc The function to get the symbols of `object`.
* @returns {Array} Returns the array of property names and symbols.
*/
function baseGetAllKeys(object, keysFunc, symbolsFunc) {
	var result = keysFunc(object);
	return isArray(object) ? result : arrayPush(result, symbolsFunc(object));
}
//#endregion
//#region node_modules/lodash-es/_getAllKeys.js
/**
* Creates an array of own enumerable property names and symbols of `object`.
*
* @private
* @param {Object} object The object to query.
* @returns {Array} Returns the array of property names and symbols.
*/
function getAllKeys(object) {
	return baseGetAllKeys(object, keys, getSymbols);
}
//#endregion
//#region node_modules/lodash-es/_getAllKeysIn.js
/**
* Creates an array of own and inherited enumerable property names and
* symbols of `object`.
*
* @private
* @param {Object} object The object to query.
* @returns {Array} Returns the array of property names and symbols.
*/
function getAllKeysIn(object) {
	return baseGetAllKeys(object, keysIn, getSymbolsIn);
}
//#endregion
//#region node_modules/lodash-es/_DataView.js
var DataView$1 = getNative(root, "DataView");
//#endregion
//#region node_modules/lodash-es/_Promise.js
var Promise$1 = getNative(root, "Promise");
//#endregion
//#region node_modules/lodash-es/_Set.js
var Set$1 = getNative(root, "Set");
//#endregion
//#region node_modules/lodash-es/_getTag.js
/** `Object#toString` result references. */
var mapTag$4 = "[object Map]", objectTag$2 = "[object Object]", promiseTag = "[object Promise]", setTag$4 = "[object Set]", weakMapTag$1 = "[object WeakMap]";
var dataViewTag$3 = "[object DataView]";
/** Used to detect maps, sets, and weakmaps. */
var dataViewCtorString = toSource(DataView$1), mapCtorString = toSource(Map$1), promiseCtorString = toSource(Promise$1), setCtorString = toSource(Set$1), weakMapCtorString = toSource(WeakMap$1);
/**
* Gets the `toStringTag` of `value`.
*
* @private
* @param {*} value The value to query.
* @returns {string} Returns the `toStringTag`.
*/
var getTag = baseGetTag;
if (DataView$1 && getTag(new DataView$1(/* @__PURE__ */ new ArrayBuffer(1))) != dataViewTag$3 || Map$1 && getTag(new Map$1()) != mapTag$4 || Promise$1 && getTag(Promise$1.resolve()) != promiseTag || Set$1 && getTag(new Set$1()) != setTag$4 || WeakMap$1 && getTag(new WeakMap$1()) != weakMapTag$1) getTag = function(value) {
	var result = baseGetTag(value), Ctor = result == objectTag$2 ? value.constructor : void 0, ctorString = Ctor ? toSource(Ctor) : "";
	if (ctorString) switch (ctorString) {
		case dataViewCtorString: return dataViewTag$3;
		case mapCtorString: return mapTag$4;
		case promiseCtorString: return promiseTag;
		case setCtorString: return setTag$4;
		case weakMapCtorString: return weakMapTag$1;
	}
	return result;
};
var _getTag_default = getTag;
//#endregion
//#region node_modules/lodash-es/_initCloneArray.js
/** Used to check objects for own properties. */
var hasOwnProperty$3 = Object.prototype.hasOwnProperty;
/**
* Initializes an array clone.
*
* @private
* @param {Array} array The array to clone.
* @returns {Array} Returns the initialized clone.
*/
function initCloneArray(array) {
	var length = array.length, result = new array.constructor(length);
	if (length && typeof array[0] == "string" && hasOwnProperty$3.call(array, "index")) {
		result.index = array.index;
		result.input = array.input;
	}
	return result;
}
//#endregion
//#region node_modules/lodash-es/_Uint8Array.js
/** Built-in value references. */
var Uint8Array$1 = root.Uint8Array;
//#endregion
//#region node_modules/lodash-es/_cloneArrayBuffer.js
/**
* Creates a clone of `arrayBuffer`.
*
* @private
* @param {ArrayBuffer} arrayBuffer The array buffer to clone.
* @returns {ArrayBuffer} Returns the cloned array buffer.
*/
function cloneArrayBuffer(arrayBuffer) {
	var result = new arrayBuffer.constructor(arrayBuffer.byteLength);
	new Uint8Array$1(result).set(new Uint8Array$1(arrayBuffer));
	return result;
}
//#endregion
//#region node_modules/lodash-es/_cloneDataView.js
/**
* Creates a clone of `dataView`.
*
* @private
* @param {Object} dataView The data view to clone.
* @param {boolean} [isDeep] Specify a deep clone.
* @returns {Object} Returns the cloned data view.
*/
function cloneDataView(dataView, isDeep) {
	var buffer = isDeep ? cloneArrayBuffer(dataView.buffer) : dataView.buffer;
	return new dataView.constructor(buffer, dataView.byteOffset, dataView.byteLength);
}
//#endregion
//#region node_modules/lodash-es/_cloneRegExp.js
/** Used to match `RegExp` flags from their coerced string values. */
var reFlags = /\w*$/;
/**
* Creates a clone of `regexp`.
*
* @private
* @param {Object} regexp The regexp to clone.
* @returns {Object} Returns the cloned regexp.
*/
function cloneRegExp(regexp) {
	var result = new regexp.constructor(regexp.source, reFlags.exec(regexp));
	result.lastIndex = regexp.lastIndex;
	return result;
}
//#endregion
//#region node_modules/lodash-es/_cloneSymbol.js
/** Used to convert symbols to primitives and strings. */
var symbolProto$1 = Symbol$1 ? Symbol$1.prototype : void 0, symbolValueOf$1 = symbolProto$1 ? symbolProto$1.valueOf : void 0;
/**
* Creates a clone of the `symbol` object.
*
* @private
* @param {Object} symbol The symbol object to clone.
* @returns {Object} Returns the cloned symbol object.
*/
function cloneSymbol(symbol) {
	return symbolValueOf$1 ? Object(symbolValueOf$1.call(symbol)) : {};
}
//#endregion
//#region node_modules/lodash-es/_cloneTypedArray.js
/**
* Creates a clone of `typedArray`.
*
* @private
* @param {Object} typedArray The typed array to clone.
* @param {boolean} [isDeep] Specify a deep clone.
* @returns {Object} Returns the cloned typed array.
*/
function cloneTypedArray(typedArray, isDeep) {
	var buffer = isDeep ? cloneArrayBuffer(typedArray.buffer) : typedArray.buffer;
	return new typedArray.constructor(buffer, typedArray.byteOffset, typedArray.length);
}
//#endregion
//#region node_modules/lodash-es/_initCloneByTag.js
/** `Object#toString` result references. */
var boolTag$2 = "[object Boolean]", dateTag$2 = "[object Date]", mapTag$3 = "[object Map]", numberTag$2 = "[object Number]", regexpTag$2 = "[object RegExp]", setTag$3 = "[object Set]", stringTag$2 = "[object String]", symbolTag$2 = "[object Symbol]";
var arrayBufferTag$2 = "[object ArrayBuffer]", dataViewTag$2 = "[object DataView]", float32Tag$1 = "[object Float32Array]", float64Tag$1 = "[object Float64Array]", int8Tag$1 = "[object Int8Array]", int16Tag$1 = "[object Int16Array]", int32Tag$1 = "[object Int32Array]", uint8Tag$1 = "[object Uint8Array]", uint8ClampedTag$1 = "[object Uint8ClampedArray]", uint16Tag$1 = "[object Uint16Array]", uint32Tag$1 = "[object Uint32Array]";
/**
* Initializes an object clone based on its `toStringTag`.
*
* **Note:** This function only supports cloning values with tags of
* `Boolean`, `Date`, `Error`, `Map`, `Number`, `RegExp`, `Set`, or `String`.
*
* @private
* @param {Object} object The object to clone.
* @param {string} tag The `toStringTag` of the object to clone.
* @param {boolean} [isDeep] Specify a deep clone.
* @returns {Object} Returns the initialized clone.
*/
function initCloneByTag(object, tag, isDeep) {
	var Ctor = object.constructor;
	switch (tag) {
		case arrayBufferTag$2: return cloneArrayBuffer(object);
		case boolTag$2:
		case dateTag$2: return new Ctor(+object);
		case dataViewTag$2: return cloneDataView(object, isDeep);
		case float32Tag$1:
		case float64Tag$1:
		case int8Tag$1:
		case int16Tag$1:
		case int32Tag$1:
		case uint8Tag$1:
		case uint8ClampedTag$1:
		case uint16Tag$1:
		case uint32Tag$1: return cloneTypedArray(object, isDeep);
		case mapTag$3: return new Ctor();
		case numberTag$2:
		case stringTag$2: return new Ctor(object);
		case regexpTag$2: return cloneRegExp(object);
		case setTag$3: return new Ctor();
		case symbolTag$2: return cloneSymbol(object);
	}
}
//#endregion
//#region node_modules/lodash-es/_initCloneObject.js
/**
* Initializes an object clone.
*
* @private
* @param {Object} object The object to clone.
* @returns {Object} Returns the initialized clone.
*/
function initCloneObject(object) {
	return typeof object.constructor == "function" && !isPrototype(object) ? baseCreate(getPrototype(object)) : {};
}
//#endregion
//#region node_modules/lodash-es/_baseIsMap.js
/** `Object#toString` result references. */
var mapTag$2 = "[object Map]";
/**
* The base implementation of `_.isMap` without Node.js optimizations.
*
* @private
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a map, else `false`.
*/
function baseIsMap(value) {
	return isObjectLike(value) && _getTag_default(value) == mapTag$2;
}
//#endregion
//#region node_modules/lodash-es/isMap.js
var nodeIsMap = nodeUtil && nodeUtil.isMap;
/**
* Checks if `value` is classified as a `Map` object.
*
* @static
* @memberOf _
* @since 4.3.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a map, else `false`.
* @example
*
* _.isMap(new Map);
* // => true
*
* _.isMap(new WeakMap);
* // => false
*/
var isMap = nodeIsMap ? baseUnary(nodeIsMap) : baseIsMap;
//#endregion
//#region node_modules/lodash-es/_baseIsSet.js
/** `Object#toString` result references. */
var setTag$2 = "[object Set]";
/**
* The base implementation of `_.isSet` without Node.js optimizations.
*
* @private
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a set, else `false`.
*/
function baseIsSet(value) {
	return isObjectLike(value) && _getTag_default(value) == setTag$2;
}
//#endregion
//#region node_modules/lodash-es/isSet.js
var nodeIsSet = nodeUtil && nodeUtil.isSet;
/**
* Checks if `value` is classified as a `Set` object.
*
* @static
* @memberOf _
* @since 4.3.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is a set, else `false`.
* @example
*
* _.isSet(new Set);
* // => true
*
* _.isSet(new WeakSet);
* // => false
*/
var isSet = nodeIsSet ? baseUnary(nodeIsSet) : baseIsSet;
//#endregion
//#region node_modules/lodash-es/_baseClone.js
/** Used to compose bitmasks for cloning. */
var CLONE_DEEP_FLAG$1 = 1, CLONE_FLAT_FLAG = 2, CLONE_SYMBOLS_FLAG$1 = 4;
/** `Object#toString` result references. */
var argsTag$1 = "[object Arguments]", arrayTag$1 = "[object Array]", boolTag$1 = "[object Boolean]", dateTag$1 = "[object Date]", errorTag$1 = "[object Error]", funcTag = "[object Function]", genTag = "[object GeneratorFunction]", mapTag$1 = "[object Map]", numberTag$1 = "[object Number]", objectTag$1 = "[object Object]", regexpTag$1 = "[object RegExp]", setTag$1 = "[object Set]", stringTag$1 = "[object String]", symbolTag$1 = "[object Symbol]", weakMapTag = "[object WeakMap]";
var arrayBufferTag$1 = "[object ArrayBuffer]", dataViewTag$1 = "[object DataView]", float32Tag = "[object Float32Array]", float64Tag = "[object Float64Array]", int8Tag = "[object Int8Array]", int16Tag = "[object Int16Array]", int32Tag = "[object Int32Array]", uint8Tag = "[object Uint8Array]", uint8ClampedTag = "[object Uint8ClampedArray]", uint16Tag = "[object Uint16Array]", uint32Tag = "[object Uint32Array]";
/** Used to identify `toStringTag` values supported by `_.clone`. */
var cloneableTags = {};
cloneableTags[argsTag$1] = cloneableTags[arrayTag$1] = cloneableTags[arrayBufferTag$1] = cloneableTags[dataViewTag$1] = cloneableTags[boolTag$1] = cloneableTags[dateTag$1] = cloneableTags[float32Tag] = cloneableTags[float64Tag] = cloneableTags[int8Tag] = cloneableTags[int16Tag] = cloneableTags[int32Tag] = cloneableTags[mapTag$1] = cloneableTags[numberTag$1] = cloneableTags[objectTag$1] = cloneableTags[regexpTag$1] = cloneableTags[setTag$1] = cloneableTags[stringTag$1] = cloneableTags[symbolTag$1] = cloneableTags[uint8Tag] = cloneableTags[uint8ClampedTag] = cloneableTags[uint16Tag] = cloneableTags[uint32Tag] = true;
cloneableTags[errorTag$1] = cloneableTags[funcTag] = cloneableTags[weakMapTag] = false;
/**
* The base implementation of `_.clone` and `_.cloneDeep` which tracks
* traversed objects.
*
* @private
* @param {*} value The value to clone.
* @param {boolean} bitmask The bitmask flags.
*  1 - Deep clone
*  2 - Flatten inherited properties
*  4 - Clone symbols
* @param {Function} [customizer] The function to customize cloning.
* @param {string} [key] The key of `value`.
* @param {Object} [object] The parent object of `value`.
* @param {Object} [stack] Tracks traversed objects and their clone counterparts.
* @returns {*} Returns the cloned value.
*/
function baseClone(value, bitmask, customizer, key, object, stack) {
	var result, isDeep = bitmask & CLONE_DEEP_FLAG$1, isFlat = bitmask & CLONE_FLAT_FLAG, isFull = bitmask & CLONE_SYMBOLS_FLAG$1;
	if (customizer) result = object ? customizer(value, key, object, stack) : customizer(value);
	if (result !== void 0) return result;
	if (!isObject(value)) return value;
	var isArr = isArray(value);
	if (isArr) {
		result = initCloneArray(value);
		if (!isDeep) return copyArray(value, result);
	} else {
		var tag = _getTag_default(value), isFunc = tag == funcTag || tag == genTag;
		if (isBuffer(value)) return cloneBuffer(value, isDeep);
		if (tag == objectTag$1 || tag == argsTag$1 || isFunc && !object) {
			result = isFlat || isFunc ? {} : initCloneObject(value);
			if (!isDeep) return isFlat ? copySymbolsIn(value, baseAssignIn(result, value)) : copySymbols(value, baseAssign(result, value));
		} else {
			if (!cloneableTags[tag]) return object ? value : {};
			result = initCloneByTag(value, tag, isDeep);
		}
	}
	stack || (stack = new Stack());
	var stacked = stack.get(value);
	if (stacked) return stacked;
	stack.set(value, result);
	if (isSet(value)) value.forEach(function(subValue) {
		result.add(baseClone(subValue, bitmask, customizer, subValue, value, stack));
	});
	else if (isMap(value)) value.forEach(function(subValue, key) {
		result.set(key, baseClone(subValue, bitmask, customizer, key, value, stack));
	});
	var props = isArr ? void 0 : (isFull ? isFlat ? getAllKeysIn : getAllKeys : isFlat ? keysIn : keys)(value);
	arrayEach(props || value, function(subValue, key) {
		if (props) {
			key = subValue;
			subValue = value[key];
		}
		assignValue(result, key, baseClone(subValue, bitmask, customizer, key, value, stack));
	});
	return result;
}
//#endregion
//#region node_modules/lodash-es/cloneDeep.js
/** Used to compose bitmasks for cloning. */
var CLONE_DEEP_FLAG = 1, CLONE_SYMBOLS_FLAG = 4;
/**
* This method is like `_.clone` except that it recursively clones `value`.
*
* @static
* @memberOf _
* @since 1.0.0
* @category Lang
* @param {*} value The value to recursively clone.
* @returns {*} Returns the deep cloned value.
* @see _.clone
* @example
*
* var objects = [{ 'a': 1 }, { 'b': 2 }];
*
* var deep = _.cloneDeep(objects);
* console.log(deep[0] === objects[0]);
* // => false
*/
function cloneDeep(value) {
	return baseClone(value, CLONE_DEEP_FLAG | CLONE_SYMBOLS_FLAG);
}
//#endregion
//#region node_modules/lodash-es/_setCacheAdd.js
/** Used to stand-in for `undefined` hash values. */
var HASH_UNDEFINED = "__lodash_hash_undefined__";
/**
* Adds `value` to the array cache.
*
* @private
* @name add
* @memberOf SetCache
* @alias push
* @param {*} value The value to cache.
* @returns {Object} Returns the cache instance.
*/
function setCacheAdd(value) {
	this.__data__.set(value, HASH_UNDEFINED);
	return this;
}
//#endregion
//#region node_modules/lodash-es/_setCacheHas.js
/**
* Checks if `value` is in the array cache.
*
* @private
* @name has
* @memberOf SetCache
* @param {*} value The value to search for.
* @returns {boolean} Returns `true` if `value` is found, else `false`.
*/
function setCacheHas(value) {
	return this.__data__.has(value);
}
//#endregion
//#region node_modules/lodash-es/_SetCache.js
/**
*
* Creates an array cache object to store unique values.
*
* @private
* @constructor
* @param {Array} [values] The values to cache.
*/
function SetCache(values) {
	var index = -1, length = values == null ? 0 : values.length;
	this.__data__ = new MapCache();
	while (++index < length) this.add(values[index]);
}
SetCache.prototype.add = SetCache.prototype.push = setCacheAdd;
SetCache.prototype.has = setCacheHas;
//#endregion
//#region node_modules/lodash-es/_arraySome.js
/**
* A specialized version of `_.some` for arrays without support for iteratee
* shorthands.
*
* @private
* @param {Array} [array] The array to iterate over.
* @param {Function} predicate The function invoked per iteration.
* @returns {boolean} Returns `true` if any element passes the predicate check,
*  else `false`.
*/
function arraySome(array, predicate) {
	var index = -1, length = array == null ? 0 : array.length;
	while (++index < length) if (predicate(array[index], index, array)) return true;
	return false;
}
//#endregion
//#region node_modules/lodash-es/_cacheHas.js
/**
* Checks if a `cache` value for `key` exists.
*
* @private
* @param {Object} cache The cache to query.
* @param {string} key The key of the entry to check.
* @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.
*/
function cacheHas(cache, key) {
	return cache.has(key);
}
//#endregion
//#region node_modules/lodash-es/_equalArrays.js
/** Used to compose bitmasks for value comparisons. */
var COMPARE_PARTIAL_FLAG$3 = 1, COMPARE_UNORDERED_FLAG$1 = 2;
/**
* A specialized version of `baseIsEqualDeep` for arrays with support for
* partial deep comparisons.
*
* @private
* @param {Array} array The array to compare.
* @param {Array} other The other array to compare.
* @param {number} bitmask The bitmask flags. See `baseIsEqual` for more details.
* @param {Function} customizer The function to customize comparisons.
* @param {Function} equalFunc The function to determine equivalents of values.
* @param {Object} stack Tracks traversed `array` and `other` objects.
* @returns {boolean} Returns `true` if the arrays are equivalent, else `false`.
*/
function equalArrays(array, other, bitmask, customizer, equalFunc, stack) {
	var isPartial = bitmask & COMPARE_PARTIAL_FLAG$3, arrLength = array.length, othLength = other.length;
	if (arrLength != othLength && !(isPartial && othLength > arrLength)) return false;
	var arrStacked = stack.get(array);
	var othStacked = stack.get(other);
	if (arrStacked && othStacked) return arrStacked == other && othStacked == array;
	var index = -1, result = true, seen = bitmask & COMPARE_UNORDERED_FLAG$1 ? new SetCache() : void 0;
	stack.set(array, other);
	stack.set(other, array);
	while (++index < arrLength) {
		var arrValue = array[index], othValue = other[index];
		if (customizer) var compared = isPartial ? customizer(othValue, arrValue, index, other, array, stack) : customizer(arrValue, othValue, index, array, other, stack);
		if (compared !== void 0) {
			if (compared) continue;
			result = false;
			break;
		}
		if (seen) {
			if (!arraySome(other, function(othValue, othIndex) {
				if (!cacheHas(seen, othIndex) && (arrValue === othValue || equalFunc(arrValue, othValue, bitmask, customizer, stack))) return seen.push(othIndex);
			})) {
				result = false;
				break;
			}
		} else if (!(arrValue === othValue || equalFunc(arrValue, othValue, bitmask, customizer, stack))) {
			result = false;
			break;
		}
	}
	stack["delete"](array);
	stack["delete"](other);
	return result;
}
//#endregion
//#region node_modules/lodash-es/_mapToArray.js
/**
* Converts `map` to its key-value pairs.
*
* @private
* @param {Object} map The map to convert.
* @returns {Array} Returns the key-value pairs.
*/
function mapToArray(map) {
	var index = -1, result = Array(map.size);
	map.forEach(function(value, key) {
		result[++index] = [key, value];
	});
	return result;
}
//#endregion
//#region node_modules/lodash-es/_setToArray.js
/**
* Converts `set` to an array of its values.
*
* @private
* @param {Object} set The set to convert.
* @returns {Array} Returns the values.
*/
function setToArray(set) {
	var index = -1, result = Array(set.size);
	set.forEach(function(value) {
		result[++index] = value;
	});
	return result;
}
//#endregion
//#region node_modules/lodash-es/_equalByTag.js
/** Used to compose bitmasks for value comparisons. */
var COMPARE_PARTIAL_FLAG$2 = 1, COMPARE_UNORDERED_FLAG = 2;
/** `Object#toString` result references. */
var boolTag = "[object Boolean]", dateTag = "[object Date]", errorTag = "[object Error]", mapTag = "[object Map]", numberTag = "[object Number]", regexpTag = "[object RegExp]", setTag = "[object Set]", stringTag = "[object String]", symbolTag = "[object Symbol]";
var arrayBufferTag = "[object ArrayBuffer]", dataViewTag = "[object DataView]";
/** Used to convert symbols to primitives and strings. */
var symbolProto = Symbol$1 ? Symbol$1.prototype : void 0, symbolValueOf = symbolProto ? symbolProto.valueOf : void 0;
/**
* A specialized version of `baseIsEqualDeep` for comparing objects of
* the same `toStringTag`.
*
* **Note:** This function only supports comparing values with tags of
* `Boolean`, `Date`, `Error`, `Number`, `RegExp`, or `String`.
*
* @private
* @param {Object} object The object to compare.
* @param {Object} other The other object to compare.
* @param {string} tag The `toStringTag` of the objects to compare.
* @param {number} bitmask The bitmask flags. See `baseIsEqual` for more details.
* @param {Function} customizer The function to customize comparisons.
* @param {Function} equalFunc The function to determine equivalents of values.
* @param {Object} stack Tracks traversed `object` and `other` objects.
* @returns {boolean} Returns `true` if the objects are equivalent, else `false`.
*/
function equalByTag(object, other, tag, bitmask, customizer, equalFunc, stack) {
	switch (tag) {
		case dataViewTag:
			if (object.byteLength != other.byteLength || object.byteOffset != other.byteOffset) return false;
			object = object.buffer;
			other = other.buffer;
		case arrayBufferTag:
			if (object.byteLength != other.byteLength || !equalFunc(new Uint8Array$1(object), new Uint8Array$1(other))) return false;
			return true;
		case boolTag:
		case dateTag:
		case numberTag: return eq(+object, +other);
		case errorTag: return object.name == other.name && object.message == other.message;
		case regexpTag:
		case stringTag: return object == other + "";
		case mapTag: var convert = mapToArray;
		case setTag:
			var isPartial = bitmask & COMPARE_PARTIAL_FLAG$2;
			convert || (convert = setToArray);
			if (object.size != other.size && !isPartial) return false;
			var stacked = stack.get(object);
			if (stacked) return stacked == other;
			bitmask |= COMPARE_UNORDERED_FLAG;
			stack.set(object, other);
			var result = equalArrays(convert(object), convert(other), bitmask, customizer, equalFunc, stack);
			stack["delete"](object);
			return result;
		case symbolTag: if (symbolValueOf) return symbolValueOf.call(object) == symbolValueOf.call(other);
	}
	return false;
}
//#endregion
//#region node_modules/lodash-es/_equalObjects.js
/** Used to compose bitmasks for value comparisons. */
var COMPARE_PARTIAL_FLAG$1 = 1;
/** Used to check objects for own properties. */
var hasOwnProperty$2 = Object.prototype.hasOwnProperty;
/**
* A specialized version of `baseIsEqualDeep` for objects with support for
* partial deep comparisons.
*
* @private
* @param {Object} object The object to compare.
* @param {Object} other The other object to compare.
* @param {number} bitmask The bitmask flags. See `baseIsEqual` for more details.
* @param {Function} customizer The function to customize comparisons.
* @param {Function} equalFunc The function to determine equivalents of values.
* @param {Object} stack Tracks traversed `object` and `other` objects.
* @returns {boolean} Returns `true` if the objects are equivalent, else `false`.
*/
function equalObjects(object, other, bitmask, customizer, equalFunc, stack) {
	var isPartial = bitmask & COMPARE_PARTIAL_FLAG$1, objProps = getAllKeys(object), objLength = objProps.length;
	if (objLength != getAllKeys(other).length && !isPartial) return false;
	var index = objLength;
	while (index--) {
		var key = objProps[index];
		if (!(isPartial ? key in other : hasOwnProperty$2.call(other, key))) return false;
	}
	var objStacked = stack.get(object);
	var othStacked = stack.get(other);
	if (objStacked && othStacked) return objStacked == other && othStacked == object;
	var result = true;
	stack.set(object, other);
	stack.set(other, object);
	var skipCtor = isPartial;
	while (++index < objLength) {
		key = objProps[index];
		var objValue = object[key], othValue = other[key];
		if (customizer) var compared = isPartial ? customizer(othValue, objValue, key, other, object, stack) : customizer(objValue, othValue, key, object, other, stack);
		if (!(compared === void 0 ? objValue === othValue || equalFunc(objValue, othValue, bitmask, customizer, stack) : compared)) {
			result = false;
			break;
		}
		skipCtor || (skipCtor = key == "constructor");
	}
	if (result && !skipCtor) {
		var objCtor = object.constructor, othCtor = other.constructor;
		if (objCtor != othCtor && "constructor" in object && "constructor" in other && !(typeof objCtor == "function" && objCtor instanceof objCtor && typeof othCtor == "function" && othCtor instanceof othCtor)) result = false;
	}
	stack["delete"](object);
	stack["delete"](other);
	return result;
}
//#endregion
//#region node_modules/lodash-es/_baseIsEqualDeep.js
/** Used to compose bitmasks for value comparisons. */
var COMPARE_PARTIAL_FLAG = 1;
/** `Object#toString` result references. */
var argsTag = "[object Arguments]", arrayTag = "[object Array]", objectTag = "[object Object]";
/** Used to check objects for own properties. */
var hasOwnProperty$1 = Object.prototype.hasOwnProperty;
/**
* A specialized version of `baseIsEqual` for arrays and objects which performs
* deep comparisons and tracks traversed objects enabling objects with circular
* references to be compared.
*
* @private
* @param {Object} object The object to compare.
* @param {Object} other The other object to compare.
* @param {number} bitmask The bitmask flags. See `baseIsEqual` for more details.
* @param {Function} customizer The function to customize comparisons.
* @param {Function} equalFunc The function to determine equivalents of values.
* @param {Object} [stack] Tracks traversed `object` and `other` objects.
* @returns {boolean} Returns `true` if the objects are equivalent, else `false`.
*/
function baseIsEqualDeep(object, other, bitmask, customizer, equalFunc, stack) {
	var objIsArr = isArray(object), othIsArr = isArray(other), objTag = objIsArr ? arrayTag : _getTag_default(object), othTag = othIsArr ? arrayTag : _getTag_default(other);
	objTag = objTag == argsTag ? objectTag : objTag;
	othTag = othTag == argsTag ? objectTag : othTag;
	var objIsObj = objTag == objectTag, othIsObj = othTag == objectTag, isSameTag = objTag == othTag;
	if (isSameTag && isBuffer(object)) {
		if (!isBuffer(other)) return false;
		objIsArr = true;
		objIsObj = false;
	}
	if (isSameTag && !objIsObj) {
		stack || (stack = new Stack());
		return objIsArr || isTypedArray(object) ? equalArrays(object, other, bitmask, customizer, equalFunc, stack) : equalByTag(object, other, objTag, bitmask, customizer, equalFunc, stack);
	}
	if (!(bitmask & COMPARE_PARTIAL_FLAG)) {
		var objIsWrapped = objIsObj && hasOwnProperty$1.call(object, "__wrapped__"), othIsWrapped = othIsObj && hasOwnProperty$1.call(other, "__wrapped__");
		if (objIsWrapped || othIsWrapped) {
			var objUnwrapped = objIsWrapped ? object.value() : object, othUnwrapped = othIsWrapped ? other.value() : other;
			stack || (stack = new Stack());
			return equalFunc(objUnwrapped, othUnwrapped, bitmask, customizer, stack);
		}
	}
	if (!isSameTag) return false;
	stack || (stack = new Stack());
	return equalObjects(object, other, bitmask, customizer, equalFunc, stack);
}
//#endregion
//#region node_modules/lodash-es/_baseIsEqual.js
/**
* The base implementation of `_.isEqual` which supports partial comparisons
* and tracks traversed objects.
*
* @private
* @param {*} value The value to compare.
* @param {*} other The other value to compare.
* @param {boolean} bitmask The bitmask flags.
*  1 - Unordered comparison
*  2 - Partial comparison
* @param {Function} [customizer] The function to customize comparisons.
* @param {Object} [stack] Tracks traversed `value` and `other` objects.
* @returns {boolean} Returns `true` if the values are equivalent, else `false`.
*/
function baseIsEqual(value, other, bitmask, customizer, stack) {
	if (value === other) return true;
	if (value == null || other == null || !isObjectLike(value) && !isObjectLike(other)) return value !== value && other !== other;
	return baseIsEqualDeep(value, other, bitmask, customizer, baseIsEqual, stack);
}
//#endregion
//#region node_modules/lodash-es/_hasPath.js
/**
* Checks if `path` exists on `object`.
*
* @private
* @param {Object} object The object to query.
* @param {Array|string} path The path to check.
* @param {Function} hasFunc The function to check properties.
* @returns {boolean} Returns `true` if `path` exists, else `false`.
*/
function hasPath(object, path, hasFunc) {
	path = castPath(path, object);
	var index = -1, length = path.length, result = false;
	while (++index < length) {
		var key = toKey(path[index]);
		if (!(result = object != null && hasFunc(object, key))) break;
		object = object[key];
	}
	if (result || ++index != length) return result;
	length = object == null ? 0 : object.length;
	return !!length && isLength(length) && isIndex(key, length) && (isArray(object) || isArguments(object));
}
//#endregion
//#region node_modules/lodash-es/_createBaseFor.js
/**
* Creates a base function for methods like `_.forIn` and `_.forOwn`.
*
* @private
* @param {boolean} [fromRight] Specify iterating from right to left.
* @returns {Function} Returns the new base function.
*/
function createBaseFor(fromRight) {
	return function(object, iteratee, keysFunc) {
		var index = -1, iterable = Object(object), props = keysFunc(object), length = props.length;
		while (length--) {
			var key = props[fromRight ? length : ++index];
			if (iteratee(iterable[key], key, iterable) === false) break;
		}
		return object;
	};
}
//#endregion
//#region node_modules/lodash-es/_baseFor.js
/**
* The base implementation of `baseForOwn` which iterates over `object`
* properties returned by `keysFunc` and invokes `iteratee` for each property.
* Iteratee functions may exit iteration early by explicitly returning `false`.
*
* @private
* @param {Object} object The object to iterate over.
* @param {Function} iteratee The function invoked per iteration.
* @param {Function} keysFunc The function to get the keys of `object`.
* @returns {Object} Returns `object`.
*/
var baseFor = createBaseFor();
//#endregion
//#region node_modules/lodash-es/now.js
/**
* Gets the timestamp of the number of milliseconds that have elapsed since
* the Unix epoch (1 January 1970 00:00:00 UTC).
*
* @static
* @memberOf _
* @since 2.4.0
* @category Date
* @returns {number} Returns the timestamp.
* @example
*
* _.defer(function(stamp) {
*   console.log(_.now() - stamp);
* }, _.now());
* // => Logs the number of milliseconds it took for the deferred invocation.
*/
var now = function() {
	return root.Date.now();
};
//#endregion
//#region node_modules/lodash-es/debounce.js
/** Error message constants. */
var FUNC_ERROR_TEXT = "Expected a function";
var nativeMax = Math.max, nativeMin = Math.min;
/**
* Creates a debounced function that delays invoking `func` until after `wait`
* milliseconds have elapsed since the last time the debounced function was
* invoked. The debounced function comes with a `cancel` method to cancel
* delayed `func` invocations and a `flush` method to immediately invoke them.
* Provide `options` to indicate whether `func` should be invoked on the
* leading and/or trailing edge of the `wait` timeout. The `func` is invoked
* with the last arguments provided to the debounced function. Subsequent
* calls to the debounced function return the result of the last `func`
* invocation.
*
* **Note:** If `leading` and `trailing` options are `true`, `func` is
* invoked on the trailing edge of the timeout only if the debounced function
* is invoked more than once during the `wait` timeout.
*
* If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
* until to the next tick, similar to `setTimeout` with a timeout of `0`.
*
* See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
* for details over the differences between `_.debounce` and `_.throttle`.
*
* @static
* @memberOf _
* @since 0.1.0
* @category Function
* @param {Function} func The function to debounce.
* @param {number} [wait=0] The number of milliseconds to delay.
* @param {Object} [options={}] The options object.
* @param {boolean} [options.leading=false]
*  Specify invoking on the leading edge of the timeout.
* @param {number} [options.maxWait]
*  The maximum time `func` is allowed to be delayed before it's invoked.
* @param {boolean} [options.trailing=true]
*  Specify invoking on the trailing edge of the timeout.
* @returns {Function} Returns the new debounced function.
* @example
*
* // Avoid costly calculations while the window size is in flux.
* jQuery(window).on('resize', _.debounce(calculateLayout, 150));
*
* // Invoke `sendMail` when clicked, debouncing subsequent calls.
* jQuery(element).on('click', _.debounce(sendMail, 300, {
*   'leading': true,
*   'trailing': false
* }));
*
* // Ensure `batchLog` is invoked once after 1 second of debounced calls.
* var debounced = _.debounce(batchLog, 250, { 'maxWait': 1000 });
* var source = new EventSource('/stream');
* jQuery(source).on('message', debounced);
*
* // Cancel the trailing debounced invocation.
* jQuery(window).on('popstate', debounced.cancel);
*/
function debounce$1(func, wait, options) {
	var lastArgs, lastThis, maxWait, result, timerId, lastCallTime, lastInvokeTime = 0, leading = false, maxing = false, trailing = true;
	if (typeof func != "function") throw new TypeError(FUNC_ERROR_TEXT);
	wait = toNumber(wait) || 0;
	if (isObject(options)) {
		leading = !!options.leading;
		maxing = "maxWait" in options;
		maxWait = maxing ? nativeMax(toNumber(options.maxWait) || 0, wait) : maxWait;
		trailing = "trailing" in options ? !!options.trailing : trailing;
	}
	function invokeFunc(time) {
		var args = lastArgs, thisArg = lastThis;
		lastArgs = lastThis = void 0;
		lastInvokeTime = time;
		result = func.apply(thisArg, args);
		return result;
	}
	function leadingEdge(time) {
		lastInvokeTime = time;
		timerId = setTimeout(timerExpired, wait);
		return leading ? invokeFunc(time) : result;
	}
	function remainingWait(time) {
		var timeSinceLastCall = time - lastCallTime, timeSinceLastInvoke = time - lastInvokeTime, timeWaiting = wait - timeSinceLastCall;
		return maxing ? nativeMin(timeWaiting, maxWait - timeSinceLastInvoke) : timeWaiting;
	}
	function shouldInvoke(time) {
		var timeSinceLastCall = time - lastCallTime, timeSinceLastInvoke = time - lastInvokeTime;
		return lastCallTime === void 0 || timeSinceLastCall >= wait || timeSinceLastCall < 0 || maxing && timeSinceLastInvoke >= maxWait;
	}
	function timerExpired() {
		var time = now();
		if (shouldInvoke(time)) return trailingEdge(time);
		timerId = setTimeout(timerExpired, remainingWait(time));
	}
	function trailingEdge(time) {
		timerId = void 0;
		if (trailing && lastArgs) return invokeFunc(time);
		lastArgs = lastThis = void 0;
		return result;
	}
	function cancel() {
		if (timerId !== void 0) clearTimeout(timerId);
		lastInvokeTime = 0;
		lastArgs = lastCallTime = lastThis = timerId = void 0;
	}
	function flush() {
		return timerId === void 0 ? result : trailingEdge(now());
	}
	function debounced() {
		var time = now(), isInvoking = shouldInvoke(time);
		lastArgs = arguments;
		lastThis = this;
		lastCallTime = time;
		if (isInvoking) {
			if (timerId === void 0) return leadingEdge(lastCallTime);
			if (maxing) {
				clearTimeout(timerId);
				timerId = setTimeout(timerExpired, wait);
				return invokeFunc(lastCallTime);
			}
		}
		if (timerId === void 0) timerId = setTimeout(timerExpired, wait);
		return result;
	}
	debounced.cancel = cancel;
	debounced.flush = flush;
	return debounced;
}
//#endregion
//#region node_modules/lodash-es/_assignMergeValue.js
/**
* This function is like `assignValue` except that it doesn't assign
* `undefined` values.
*
* @private
* @param {Object} object The object to modify.
* @param {string} key The key of the property to assign.
* @param {*} value The value to assign.
*/
function assignMergeValue(object, key, value) {
	if (value !== void 0 && !eq(object[key], value) || value === void 0 && !(key in object)) baseAssignValue(object, key, value);
}
//#endregion
//#region node_modules/lodash-es/isArrayLikeObject.js
/**
* This method is like `_.isArrayLike` except that it also checks if `value`
* is an object.
*
* @static
* @memberOf _
* @since 4.0.0
* @category Lang
* @param {*} value The value to check.
* @returns {boolean} Returns `true` if `value` is an array-like object,
*  else `false`.
* @example
*
* _.isArrayLikeObject([1, 2, 3]);
* // => true
*
* _.isArrayLikeObject(document.body.children);
* // => true
*
* _.isArrayLikeObject('abc');
* // => false
*
* _.isArrayLikeObject(_.noop);
* // => false
*/
function isArrayLikeObject(value) {
	return isObjectLike(value) && isArrayLike(value);
}
//#endregion
//#region node_modules/lodash-es/_safeGet.js
/**
* Gets the value at `key`, unless `key` is "__proto__" or "constructor".
*
* @private
* @param {Object} object The object to query.
* @param {string} key The key of the property to get.
* @returns {*} Returns the property value.
*/
function safeGet(object, key) {
	if (key === "constructor" && typeof object[key] === "function") return;
	if (key == "__proto__") return;
	return object[key];
}
//#endregion
//#region node_modules/lodash-es/toPlainObject.js
/**
* Converts `value` to a plain object flattening inherited enumerable string
* keyed properties of `value` to own properties of the plain object.
*
* @static
* @memberOf _
* @since 3.0.0
* @category Lang
* @param {*} value The value to convert.
* @returns {Object} Returns the converted plain object.
* @example
*
* function Foo() {
*   this.b = 2;
* }
*
* Foo.prototype.c = 3;
*
* _.assign({ 'a': 1 }, new Foo);
* // => { 'a': 1, 'b': 2 }
*
* _.assign({ 'a': 1 }, _.toPlainObject(new Foo));
* // => { 'a': 1, 'b': 2, 'c': 3 }
*/
function toPlainObject(value) {
	return copyObject(value, keysIn(value));
}
//#endregion
//#region node_modules/lodash-es/_baseMergeDeep.js
/**
* A specialized version of `baseMerge` for arrays and objects which performs
* deep merges and tracks traversed objects enabling objects with circular
* references to be merged.
*
* @private
* @param {Object} object The destination object.
* @param {Object} source The source object.
* @param {string} key The key of the value to merge.
* @param {number} srcIndex The index of `source`.
* @param {Function} mergeFunc The function to merge values.
* @param {Function} [customizer] The function to customize assigned values.
* @param {Object} [stack] Tracks traversed source values and their merged
*  counterparts.
*/
function baseMergeDeep(object, source, key, srcIndex, mergeFunc, customizer, stack) {
	var objValue = safeGet(object, key), srcValue = safeGet(source, key), stacked = stack.get(srcValue);
	if (stacked) {
		assignMergeValue(object, key, stacked);
		return;
	}
	var newValue = customizer ? customizer(objValue, srcValue, key + "", object, source, stack) : void 0;
	var isCommon = newValue === void 0;
	if (isCommon) {
		var isArr = isArray(srcValue), isBuff = !isArr && isBuffer(srcValue), isTyped = !isArr && !isBuff && isTypedArray(srcValue);
		newValue = srcValue;
		if (isArr || isBuff || isTyped) if (isArray(objValue)) newValue = objValue;
		else if (isArrayLikeObject(objValue)) newValue = copyArray(objValue);
		else if (isBuff) {
			isCommon = false;
			newValue = cloneBuffer(srcValue, true);
		} else if (isTyped) {
			isCommon = false;
			newValue = cloneTypedArray(srcValue, true);
		} else newValue = [];
		else if (isPlainObject(srcValue) || isArguments(srcValue)) {
			newValue = objValue;
			if (isArguments(objValue)) newValue = toPlainObject(objValue);
			else if (!isObject(objValue) || isFunction(objValue)) newValue = initCloneObject(srcValue);
		} else isCommon = false;
	}
	if (isCommon) {
		stack.set(srcValue, newValue);
		mergeFunc(newValue, srcValue, srcIndex, customizer, stack);
		stack["delete"](srcValue);
	}
	assignMergeValue(object, key, newValue);
}
//#endregion
//#region node_modules/lodash-es/_baseMerge.js
/**
* The base implementation of `_.merge` without support for multiple sources.
*
* @private
* @param {Object} object The destination object.
* @param {Object} source The source object.
* @param {number} srcIndex The index of `source`.
* @param {Function} [customizer] The function to customize merged values.
* @param {Object} [stack] Tracks traversed source values and their merged
*  counterparts.
*/
function baseMerge(object, source, srcIndex, customizer, stack) {
	if (object === source) return;
	baseFor(source, function(srcValue, key) {
		stack || (stack = new Stack());
		if (isObject(srcValue)) baseMergeDeep(object, source, key, srcIndex, baseMerge, customizer, stack);
		else {
			var newValue = customizer ? customizer(safeGet(object, key), srcValue, key + "", object, source, stack) : void 0;
			if (newValue === void 0) newValue = srcValue;
			assignMergeValue(object, key, newValue);
		}
	}, keysIn);
}
//#endregion
//#region node_modules/lodash-es/_escapeHtmlChar.js
/**
* Used by `_.escape` to convert characters to HTML entities.
*
* @private
* @param {string} chr The matched character to escape.
* @returns {string} Returns the escaped character.
*/
var escapeHtmlChar = basePropertyOf({
	"&": "&amp;",
	"<": "&lt;",
	">": "&gt;",
	"\"": "&quot;",
	"'": "&#39;"
});
//#endregion
//#region node_modules/lodash-es/escape.js
/** Used to match HTML entities and HTML characters. */
var reUnescapedHtml = /[&<>"']/g, reHasUnescapedHtml = RegExp(reUnescapedHtml.source);
/**
* Converts the characters "&", "<", ">", '"', and "'" in `string` to their
* corresponding HTML entities.
*
* **Note:** No other characters are escaped. To escape additional
* characters use a third-party library like [_he_](https://mths.be/he).
*
* Though the ">" character is escaped for symmetry, characters like
* ">" and "/" don't need escaping in HTML and have no special meaning
* unless they're part of a tag or unquoted attribute value. See
* [Mathias Bynens's article](https://mathiasbynens.be/notes/ambiguous-ampersands)
* (under "semi-related fun fact") for more details.
*
* When working with HTML you should always
* [quote attribute values](http://wonko.com/post/html-escaping) to reduce
* XSS vectors.
*
* @static
* @since 0.1.0
* @memberOf _
* @category String
* @param {string} [string=''] The string to escape.
* @returns {string} Returns the escaped string.
* @example
*
* _.escape('fred, barney, & pebbles');
* // => 'fred, barney, &amp; pebbles'
*/
function escape$1(string) {
	string = toString(string);
	return string && reHasUnescapedHtml.test(string) ? string.replace(reUnescapedHtml, escapeHtmlChar) : string;
}
//#endregion
//#region node_modules/lodash-es/_baseHas.js
/** Used to check objects for own properties. */
var hasOwnProperty = Object.prototype.hasOwnProperty;
/**
* The base implementation of `_.has` without support for deep paths.
*
* @private
* @param {Object} [object] The object to query.
* @param {Array|string} key The key to check.
* @returns {boolean} Returns `true` if `key` exists, else `false`.
*/
function baseHas(object, key) {
	return object != null && hasOwnProperty.call(object, key);
}
//#endregion
//#region node_modules/lodash-es/has.js
/**
* Checks if `path` is a direct property of `object`.
*
* @static
* @since 0.1.0
* @memberOf _
* @category Object
* @param {Object} object The object to query.
* @param {Array|string} path The path to check.
* @returns {boolean} Returns `true` if `path` exists, else `false`.
* @example
*
* var object = { 'a': { 'b': 2 } };
* var other = _.create({ 'a': _.create({ 'b': 2 }) });
*
* _.has(object, 'a');
* // => true
*
* _.has(object, 'a.b');
* // => true
*
* _.has(object, ['a', 'b']);
* // => true
*
* _.has(other, 'a');
* // => false
*/
function has(object, path) {
	return object != null && hasPath(object, path, baseHas);
}
//#endregion
//#region node_modules/lodash-es/isEqual.js
/**
* Performs a deep comparison between two values to determine if they are
* equivalent.
*
* **Note:** This method supports comparing arrays, array buffers, booleans,
* date objects, error objects, maps, numbers, `Object` objects, regexes,
* sets, strings, symbols, and typed arrays. `Object` objects are compared
* by their own, not inherited, enumerable properties. Functions and DOM
* nodes are compared by strict equality, i.e. `===`.
*
* @static
* @memberOf _
* @since 0.1.0
* @category Lang
* @param {*} value The value to compare.
* @param {*} other The other value to compare.
* @returns {boolean} Returns `true` if the values are equivalent, else `false`.
* @example
*
* var object = { 'a': 1 };
* var other = { 'a': 1 };
*
* _.isEqual(object, other);
* // => true
*
* object === other;
* // => false
*/
function isEqual(value, other) {
	return baseIsEqual(value, other);
}
//#endregion
//#region node_modules/lodash-es/merge.js
/**
* This method is like `_.assign` except that it recursively merges own and
* inherited enumerable string keyed properties of source objects into the
* destination object. Source properties that resolve to `undefined` are
* skipped if a destination value exists. Array and plain object properties
* are merged recursively. Other objects and value types are overridden by
* assignment. Source objects are applied from left to right. Subsequent
* sources overwrite property assignments of previous sources.
*
* **Note:** This method mutates `object`.
*
* @static
* @memberOf _
* @since 0.5.0
* @category Object
* @param {Object} object The destination object.
* @param {...Object} [sources] The source objects.
* @returns {Object} Returns `object`.
* @example
*
* var object = {
*   'a': [{ 'b': 2 }, { 'd': 4 }]
* };
*
* var other = {
*   'a': [{ 'c': 3 }, { 'e': 5 }]
* };
*
* _.merge(object, other);
* // => { 'a': [{ 'b': 2, 'c': 3 }, { 'd': 4, 'e': 5 }] }
*/
var merge = createAssigner(function(object, source, srcIndex) {
	baseMerge(object, source, srcIndex);
});
//#endregion
//#region node_modules/lodash-es/_baseSet.js
/**
* The base implementation of `_.set`.
*
* @private
* @param {Object} object The object to modify.
* @param {Array|string} path The path of the property to set.
* @param {*} value The value to set.
* @param {Function} [customizer] The function to customize path creation.
* @returns {Object} Returns `object`.
*/
function baseSet(object, path, value, customizer) {
	if (!isObject(object)) return object;
	path = castPath(path, object);
	var index = -1, length = path.length, lastIndex = length - 1, nested = object;
	while (nested != null && ++index < length) {
		var key = toKey(path[index]), newValue = value;
		if (key === "__proto__" || key === "constructor" || key === "prototype") return object;
		if (index != lastIndex) {
			var objValue = nested[key];
			newValue = customizer ? customizer(objValue, key, nested) : void 0;
			if (newValue === void 0) newValue = isObject(objValue) ? objValue : isIndex(path[index + 1]) ? [] : {};
		}
		assignValue(nested, key, newValue);
		nested = nested[key];
	}
	return object;
}
//#endregion
//#region node_modules/lodash-es/set.js
/**
* Sets the value at `path` of `object`. If a portion of `path` doesn't exist,
* it's created. Arrays are created for missing index properties while objects
* are created for all other missing properties. Use `_.setWith` to customize
* `path` creation.
*
* **Note:** This method mutates `object`.
*
* @static
* @memberOf _
* @since 3.7.0
* @category Object
* @param {Object} object The object to modify.
* @param {Array|string} path The path of the property to set.
* @param {*} value The value to set.
* @returns {Object} Returns `object`.
* @example
*
* var object = { 'a': [{ 'b': { 'c': 3 } }] };
*
* _.set(object, 'a[0].b.c', 4);
* console.log(object.a[0].b.c);
* // => 4
*
* _.set(object, ['x', '0', 'y', 'z'], 5);
* console.log(object.x[0].y.z);
* // => 5
*/
function set(object, path, value) {
	return object == null ? object : baseSet(object, path, value);
}
//#endregion
//#region node_modules/es-errors/type.js
var require_type = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./type')} */
	module.exports = TypeError;
}));
//#endregion
//#region __vite-browser-external
var require___vite_browser_external = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	module.exports = {};
}));
//#endregion
//#region node_modules/object-inspect/index.js
var require_object_inspect = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var hasMap = typeof Map === "function" && Map.prototype;
	var mapSizeDescriptor = Object.getOwnPropertyDescriptor && hasMap ? Object.getOwnPropertyDescriptor(Map.prototype, "size") : null;
	var mapSize = hasMap && mapSizeDescriptor && typeof mapSizeDescriptor.get === "function" ? mapSizeDescriptor.get : null;
	var mapForEach = hasMap && Map.prototype.forEach;
	var hasSet = typeof Set === "function" && Set.prototype;
	var setSizeDescriptor = Object.getOwnPropertyDescriptor && hasSet ? Object.getOwnPropertyDescriptor(Set.prototype, "size") : null;
	var setSize = hasSet && setSizeDescriptor && typeof setSizeDescriptor.get === "function" ? setSizeDescriptor.get : null;
	var setForEach = hasSet && Set.prototype.forEach;
	var weakMapHas = typeof WeakMap === "function" && WeakMap.prototype ? WeakMap.prototype.has : null;
	var weakSetHas = typeof WeakSet === "function" && WeakSet.prototype ? WeakSet.prototype.has : null;
	var weakRefDeref = typeof WeakRef === "function" && WeakRef.prototype ? WeakRef.prototype.deref : null;
	var booleanValueOf = Boolean.prototype.valueOf;
	var objectToString = Object.prototype.toString;
	var functionToString = Function.prototype.toString;
	var $match = String.prototype.match;
	var $slice = String.prototype.slice;
	var $replace = String.prototype.replace;
	var $toUpperCase = String.prototype.toUpperCase;
	var $toLowerCase = String.prototype.toLowerCase;
	var $test = RegExp.prototype.test;
	var $concat = Array.prototype.concat;
	var $join = Array.prototype.join;
	var $arrSlice = Array.prototype.slice;
	var $floor = Math.floor;
	var bigIntValueOf = typeof BigInt === "function" ? BigInt.prototype.valueOf : null;
	var gOPS = Object.getOwnPropertySymbols;
	var symToString = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? Symbol.prototype.toString : null;
	var hasShammedSymbols = typeof Symbol === "function" && typeof Symbol.iterator === "object";
	var toStringTag = typeof Symbol === "function" && Symbol.toStringTag && (typeof Symbol.toStringTag === hasShammedSymbols ? "object" : "symbol") ? Symbol.toStringTag : null;
	var isEnumerable = Object.prototype.propertyIsEnumerable;
	var gPO = (typeof Reflect === "function" ? Reflect.getPrototypeOf : Object.getPrototypeOf) || ([].__proto__ === Array.prototype ? function(O) {
		return O.__proto__;
	} : null);
	function addNumericSeparator(num, str) {
		if (num === Infinity || num === -Infinity || num !== num || num && num > -1e3 && num < 1e3 || $test.call(/e/, str)) return str;
		var sepRegex = /[0-9](?=(?:[0-9]{3})+(?![0-9]))/g;
		if (typeof num === "number") {
			var int = num < 0 ? -$floor(-num) : $floor(num);
			if (int !== num) {
				var intStr = String(int);
				var dec = $slice.call(str, intStr.length + 1);
				return $replace.call(intStr, sepRegex, "$&_") + "." + $replace.call($replace.call(dec, /([0-9]{3})/g, "$&_"), /_$/, "");
			}
		}
		return $replace.call(str, sepRegex, "$&_");
	}
	var utilInspect = require___vite_browser_external();
	var inspectCustom = utilInspect.custom;
	var inspectSymbol = isSymbol(inspectCustom) ? inspectCustom : null;
	var quotes = {
		__proto__: null,
		"double": "\"",
		single: "'"
	};
	var quoteREs = {
		__proto__: null,
		"double": /(["\\])/g,
		single: /(['\\])/g
	};
	module.exports = function inspect_(obj, options, depth, seen) {
		var opts = options || {};
		if (has(opts, "quoteStyle") && !has(quotes, opts.quoteStyle)) throw new TypeError("option \"quoteStyle\" must be \"single\" or \"double\"");
		if (has(opts, "maxStringLength") && (typeof opts.maxStringLength === "number" ? opts.maxStringLength < 0 && opts.maxStringLength !== Infinity : opts.maxStringLength !== null)) throw new TypeError("option \"maxStringLength\", if provided, must be a positive integer, Infinity, or `null`");
		var customInspect = has(opts, "customInspect") ? opts.customInspect : true;
		if (typeof customInspect !== "boolean" && customInspect !== "symbol") throw new TypeError("option \"customInspect\", if provided, must be `true`, `false`, or `'symbol'`");
		if (has(opts, "indent") && opts.indent !== null && opts.indent !== "	" && !(parseInt(opts.indent, 10) === opts.indent && opts.indent > 0)) throw new TypeError("option \"indent\" must be \"\\t\", an integer > 0, or `null`");
		if (has(opts, "numericSeparator") && typeof opts.numericSeparator !== "boolean") throw new TypeError("option \"numericSeparator\", if provided, must be `true` or `false`");
		var numericSeparator = opts.numericSeparator;
		if (typeof obj === "undefined") return "undefined";
		if (obj === null) return "null";
		if (typeof obj === "boolean") return obj ? "true" : "false";
		if (typeof obj === "string") return inspectString(obj, opts);
		if (typeof obj === "number") {
			if (obj === 0) return Infinity / obj > 0 ? "0" : "-0";
			var str = String(obj);
			return numericSeparator ? addNumericSeparator(obj, str) : str;
		}
		if (typeof obj === "bigint") {
			var bigIntStr = String(obj) + "n";
			return numericSeparator ? addNumericSeparator(obj, bigIntStr) : bigIntStr;
		}
		var maxDepth = typeof opts.depth === "undefined" ? 5 : opts.depth;
		if (typeof depth === "undefined") depth = 0;
		if (depth >= maxDepth && maxDepth > 0 && typeof obj === "object") return isArray(obj) ? "[Array]" : "[Object]";
		var indent = getIndent(opts, depth);
		if (typeof seen === "undefined") seen = [];
		else if (indexOf(seen, obj) >= 0) return "[Circular]";
		function inspect(value, from, noIndent) {
			if (from) {
				seen = $arrSlice.call(seen);
				seen.push(from);
			}
			if (noIndent) {
				var newOpts = { depth: opts.depth };
				if (has(opts, "quoteStyle")) newOpts.quoteStyle = opts.quoteStyle;
				return inspect_(value, newOpts, depth + 1, seen);
			}
			return inspect_(value, opts, depth + 1, seen);
		}
		if (typeof obj === "function" && !isRegExp(obj)) {
			var name = nameOf(obj);
			var keys = arrObjKeys(obj, inspect);
			return "[Function" + (name ? ": " + name : " (anonymous)") + "]" + (keys.length > 0 ? " { " + $join.call(keys, ", ") + " }" : "");
		}
		if (isSymbol(obj)) {
			var symString = hasShammedSymbols ? $replace.call(String(obj), /^(Symbol\(.*\))_[^)]*$/, "$1") : symToString.call(obj);
			return typeof obj === "object" && !hasShammedSymbols ? markBoxed(symString) : symString;
		}
		if (isElement(obj)) {
			var s = "<" + $toLowerCase.call(String(obj.nodeName));
			var attrs = obj.attributes || [];
			for (var i = 0; i < attrs.length; i++) s += " " + attrs[i].name + "=" + wrapQuotes(quote(attrs[i].value), "double", opts);
			s += ">";
			if (obj.childNodes && obj.childNodes.length) s += "...";
			s += "</" + $toLowerCase.call(String(obj.nodeName)) + ">";
			return s;
		}
		if (isArray(obj)) {
			if (obj.length === 0) return "[]";
			var xs = arrObjKeys(obj, inspect);
			if (indent && !singleLineValues(xs)) return "[" + indentedJoin(xs, indent) + "]";
			return "[ " + $join.call(xs, ", ") + " ]";
		}
		if (isError(obj)) {
			var parts = arrObjKeys(obj, inspect);
			if (!("cause" in Error.prototype) && "cause" in obj && !isEnumerable.call(obj, "cause")) return "{ [" + String(obj) + "] " + $join.call($concat.call("[cause]: " + inspect(obj.cause), parts), ", ") + " }";
			if (parts.length === 0) return "[" + String(obj) + "]";
			return "{ [" + String(obj) + "] " + $join.call(parts, ", ") + " }";
		}
		if (typeof obj === "object" && customInspect) {
			if (inspectSymbol && typeof obj[inspectSymbol] === "function" && utilInspect) return utilInspect(obj, { depth: maxDepth - depth });
			else if (customInspect !== "symbol" && typeof obj.inspect === "function") return obj.inspect();
		}
		if (isMap(obj)) {
			var mapParts = [];
			if (mapForEach) mapForEach.call(obj, function(value, key) {
				mapParts.push(inspect(key, obj, true) + " => " + inspect(value, obj));
			});
			return collectionOf("Map", mapSize.call(obj), mapParts, indent);
		}
		if (isSet(obj)) {
			var setParts = [];
			if (setForEach) setForEach.call(obj, function(value) {
				setParts.push(inspect(value, obj));
			});
			return collectionOf("Set", setSize.call(obj), setParts, indent);
		}
		if (isWeakMap(obj)) return weakCollectionOf("WeakMap");
		if (isWeakSet(obj)) return weakCollectionOf("WeakSet");
		if (isWeakRef(obj)) return weakCollectionOf("WeakRef");
		if (isNumber(obj)) return markBoxed(inspect(Number(obj)));
		if (isBigInt(obj)) return markBoxed(inspect(bigIntValueOf.call(obj)));
		if (isBoolean(obj)) return markBoxed(booleanValueOf.call(obj));
		if (isString(obj)) return markBoxed(inspect(String(obj)));
		if (typeof window !== "undefined" && obj === window) return "{ [object Window] }";
		if (typeof globalThis !== "undefined" && obj === globalThis || typeof global !== "undefined" && obj === global) return "{ [object globalThis] }";
		if (!isDate(obj) && !isRegExp(obj)) {
			var ys = arrObjKeys(obj, inspect);
			var isPlainObject = gPO ? gPO(obj) === Object.prototype : obj instanceof Object || obj.constructor === Object;
			var protoTag = obj instanceof Object ? "" : "null prototype";
			var stringTag = !isPlainObject && toStringTag && Object(obj) === obj && toStringTag in obj ? $slice.call(toStr(obj), 8, -1) : protoTag ? "Object" : "";
			var tag = (isPlainObject || typeof obj.constructor !== "function" ? "" : obj.constructor.name ? obj.constructor.name + " " : "") + (stringTag || protoTag ? "[" + $join.call($concat.call([], stringTag || [], protoTag || []), ": ") + "] " : "");
			if (ys.length === 0) return tag + "{}";
			if (indent) return tag + "{" + indentedJoin(ys, indent) + "}";
			return tag + "{ " + $join.call(ys, ", ") + " }";
		}
		return String(obj);
	};
	function wrapQuotes(s, defaultStyle, opts) {
		var quoteChar = quotes[opts.quoteStyle || defaultStyle];
		return quoteChar + s + quoteChar;
	}
	function quote(s) {
		return $replace.call(String(s), /"/g, "&quot;");
	}
	function canTrustToString(obj) {
		return !toStringTag || !(typeof obj === "object" && (toStringTag in obj || typeof obj[toStringTag] !== "undefined"));
	}
	function isArray(obj) {
		return toStr(obj) === "[object Array]" && canTrustToString(obj);
	}
	function isDate(obj) {
		return toStr(obj) === "[object Date]" && canTrustToString(obj);
	}
	function isRegExp(obj) {
		return toStr(obj) === "[object RegExp]" && canTrustToString(obj);
	}
	function isError(obj) {
		return toStr(obj) === "[object Error]" && canTrustToString(obj);
	}
	function isString(obj) {
		return toStr(obj) === "[object String]" && canTrustToString(obj);
	}
	function isNumber(obj) {
		return toStr(obj) === "[object Number]" && canTrustToString(obj);
	}
	function isBoolean(obj) {
		return toStr(obj) === "[object Boolean]" && canTrustToString(obj);
	}
	function isSymbol(obj) {
		if (hasShammedSymbols) return obj && typeof obj === "object" && obj instanceof Symbol;
		if (typeof obj === "symbol") return true;
		if (!obj || typeof obj !== "object" || !symToString) return false;
		try {
			symToString.call(obj);
			return true;
		} catch (e) {}
		return false;
	}
	function isBigInt(obj) {
		if (!obj || typeof obj !== "object" || !bigIntValueOf) return false;
		try {
			bigIntValueOf.call(obj);
			return true;
		} catch (e) {}
		return false;
	}
	var hasOwn = Object.prototype.hasOwnProperty || function(key) {
		return key in this;
	};
	function has(obj, key) {
		return hasOwn.call(obj, key);
	}
	function toStr(obj) {
		return objectToString.call(obj);
	}
	function nameOf(f) {
		if (f.name) return f.name;
		var m = $match.call(functionToString.call(f), /^function\s*([\w$]+)/);
		if (m) return m[1];
		return null;
	}
	function indexOf(xs, x) {
		if (xs.indexOf) return xs.indexOf(x);
		for (var i = 0, l = xs.length; i < l; i++) if (xs[i] === x) return i;
		return -1;
	}
	function isMap(x) {
		if (!mapSize || !x || typeof x !== "object") return false;
		try {
			mapSize.call(x);
			try {
				setSize.call(x);
			} catch (s) {
				return true;
			}
			return x instanceof Map;
		} catch (e) {}
		return false;
	}
	function isWeakMap(x) {
		if (!weakMapHas || !x || typeof x !== "object") return false;
		try {
			weakMapHas.call(x, weakMapHas);
			try {
				weakSetHas.call(x, weakSetHas);
			} catch (s) {
				return true;
			}
			return x instanceof WeakMap;
		} catch (e) {}
		return false;
	}
	function isWeakRef(x) {
		if (!weakRefDeref || !x || typeof x !== "object") return false;
		try {
			weakRefDeref.call(x);
			return true;
		} catch (e) {}
		return false;
	}
	function isSet(x) {
		if (!setSize || !x || typeof x !== "object") return false;
		try {
			setSize.call(x);
			try {
				mapSize.call(x);
			} catch (m) {
				return true;
			}
			return x instanceof Set;
		} catch (e) {}
		return false;
	}
	function isWeakSet(x) {
		if (!weakSetHas || !x || typeof x !== "object") return false;
		try {
			weakSetHas.call(x, weakSetHas);
			try {
				weakMapHas.call(x, weakMapHas);
			} catch (s) {
				return true;
			}
			return x instanceof WeakSet;
		} catch (e) {}
		return false;
	}
	function isElement(x) {
		if (!x || typeof x !== "object") return false;
		if (typeof HTMLElement !== "undefined" && x instanceof HTMLElement) return true;
		return typeof x.nodeName === "string" && typeof x.getAttribute === "function";
	}
	function inspectString(str, opts) {
		if (str.length > opts.maxStringLength) {
			var remaining = str.length - opts.maxStringLength;
			var trailer = "... " + remaining + " more character" + (remaining > 1 ? "s" : "");
			return inspectString($slice.call(str, 0, opts.maxStringLength), opts) + trailer;
		}
		var quoteRE = quoteREs[opts.quoteStyle || "single"];
		quoteRE.lastIndex = 0;
		return wrapQuotes($replace.call($replace.call(str, quoteRE, "\\$1"), /[\x00-\x1f]/g, lowbyte), "single", opts);
	}
	function lowbyte(c) {
		var n = c.charCodeAt(0);
		var x = {
			8: "b",
			9: "t",
			10: "n",
			12: "f",
			13: "r"
		}[n];
		if (x) return "\\" + x;
		return "\\x" + (n < 16 ? "0" : "") + $toUpperCase.call(n.toString(16));
	}
	function markBoxed(str) {
		return "Object(" + str + ")";
	}
	function weakCollectionOf(type) {
		return type + " { ? }";
	}
	function collectionOf(type, size, entries, indent) {
		var joinedEntries = indent ? indentedJoin(entries, indent) : $join.call(entries, ", ");
		return type + " (" + size + ") {" + joinedEntries + "}";
	}
	function singleLineValues(xs) {
		for (var i = 0; i < xs.length; i++) if (indexOf(xs[i], "\n") >= 0) return false;
		return true;
	}
	function getIndent(opts, depth) {
		var baseIndent;
		if (opts.indent === "	") baseIndent = "	";
		else if (typeof opts.indent === "number" && opts.indent > 0) baseIndent = $join.call(Array(opts.indent + 1), " ");
		else return null;
		return {
			base: baseIndent,
			prev: $join.call(Array(depth + 1), baseIndent)
		};
	}
	function indentedJoin(xs, indent) {
		if (xs.length === 0) return "";
		var lineJoiner = "\n" + indent.prev + indent.base;
		return lineJoiner + $join.call(xs, "," + lineJoiner) + "\n" + indent.prev;
	}
	function arrObjKeys(obj, inspect) {
		var isArr = isArray(obj);
		var xs = [];
		if (isArr) {
			xs.length = obj.length;
			for (var i = 0; i < obj.length; i++) xs[i] = has(obj, i) ? inspect(obj[i], obj) : "";
		}
		var syms = typeof gOPS === "function" ? gOPS(obj) : [];
		var symMap;
		if (hasShammedSymbols) {
			symMap = {};
			for (var k = 0; k < syms.length; k++) symMap["$" + syms[k]] = syms[k];
		}
		for (var key in obj) {
			if (!has(obj, key)) continue;
			if (isArr && String(Number(key)) === key && key < obj.length) continue;
			if (hasShammedSymbols && symMap["$" + key] instanceof Symbol) continue;
			else if ($test.call(/[^\w$]/, key)) xs.push(inspect(key, obj) + ": " + inspect(obj[key], obj));
			else xs.push(key + ": " + inspect(obj[key], obj));
		}
		if (typeof gOPS === "function") {
			for (var j = 0; j < syms.length; j++) if (isEnumerable.call(obj, syms[j])) xs.push("[" + inspect(syms[j]) + "]: " + inspect(obj[syms[j]], obj));
		}
		return xs;
	}
}));
//#endregion
//#region node_modules/side-channel-list/index.js
var require_side_channel_list = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var inspect = require_object_inspect();
	var $TypeError = require_type();
	/** @type {import('./list.d.ts').listGetNode} */
	var listGetNode = function(list, key, isDelete) {
		/** @type {typeof list | NonNullable<(typeof list)['next']>} */
		var prev = list;
		/** @type {(typeof list)['next']} */
		var curr;
		for (; (curr = prev.next) != null; prev = curr) if (curr.key === key) {
			prev.next = curr.next;
			if (!isDelete) {
				curr.next = list.next;
				list.next = curr;
			}
			return curr;
		}
	};
	/** @type {import('./list.d.ts').listGet} */
	var listGet = function(objects, key) {
		if (!objects) return;
		var node = listGetNode(objects, key);
		return node && node.value;
	};
	/** @type {import('./list.d.ts').listSet} */
	var listSet = function(objects, key, value) {
		var node = listGetNode(objects, key);
		if (node) node.value = value;
		else objects.next = {
			key,
			next: objects.next,
			value
		};
	};
	/** @type {import('./list.d.ts').listHas} */
	var listHas = function(objects, key) {
		if (!objects) return false;
		return !!listGetNode(objects, key);
	};
	/** @type {import('./list.d.ts').listDelete} */
	var listDelete = function(objects, key) {
		if (objects) return listGetNode(objects, key, true);
	};
	/** @type {import('.')} */
	module.exports = function getSideChannelList() {
		/** @typedef {ReturnType<typeof getSideChannelList>} Channel */
		/** @typedef {Parameters<Channel['get']>[0]} K */
		/** @typedef {Parameters<Channel['set']>[1]} V */
		/** @type {import('./list.d.ts').RootNode<V, K> | undefined} */ var $o;
		/** @type {Channel} */
		var channel = {
			assert: function(key) {
				if (!channel.has(key)) throw new $TypeError("Side channel does not contain " + inspect(key));
			},
			"delete": function(key) {
				var root = $o && $o.next;
				var deletedNode = listDelete($o, key);
				if (deletedNode && root && root === deletedNode) $o = void 0;
				return !!deletedNode;
			},
			get: function(key) {
				return listGet($o, key);
			},
			has: function(key) {
				return listHas($o, key);
			},
			set: function(key, value) {
				if (!$o) $o = { next: void 0 };
				listSet($o, key, value);
			}
		};
		return channel;
	};
}));
//#endregion
//#region node_modules/es-object-atoms/index.js
var require_es_object_atoms = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('.')} */
	module.exports = Object;
}));
//#endregion
//#region node_modules/es-errors/index.js
var require_es_errors = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('.')} */
	module.exports = Error;
}));
//#endregion
//#region node_modules/es-errors/eval.js
var require_eval = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./eval')} */
	module.exports = EvalError;
}));
//#endregion
//#region node_modules/es-errors/range.js
var require_range = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./range')} */
	module.exports = RangeError;
}));
//#endregion
//#region node_modules/es-errors/ref.js
var require_ref = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./ref')} */
	module.exports = ReferenceError;
}));
//#endregion
//#region node_modules/es-errors/syntax.js
var require_syntax = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./syntax')} */
	module.exports = SyntaxError;
}));
//#endregion
//#region node_modules/es-errors/uri.js
var require_uri = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./uri')} */
	module.exports = URIError;
}));
//#endregion
//#region node_modules/math-intrinsics/abs.js
var require_abs = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./abs')} */
	module.exports = Math.abs;
}));
//#endregion
//#region node_modules/math-intrinsics/floor.js
var require_floor = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./floor')} */
	module.exports = Math.floor;
}));
//#endregion
//#region node_modules/math-intrinsics/max.js
var require_max = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./max')} */
	module.exports = Math.max;
}));
//#endregion
//#region node_modules/math-intrinsics/min.js
var require_min = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./min')} */
	module.exports = Math.min;
}));
//#endregion
//#region node_modules/math-intrinsics/pow.js
var require_pow = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./pow')} */
	module.exports = Math.pow;
}));
//#endregion
//#region node_modules/math-intrinsics/round.js
var require_round = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./round')} */
	module.exports = Math.round;
}));
//#endregion
//#region node_modules/math-intrinsics/isNaN.js
var require_isNaN = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./isNaN')} */
	module.exports = Number.isNaN || function isNaN(a) {
		return a !== a;
	};
}));
//#endregion
//#region node_modules/math-intrinsics/sign.js
var require_sign = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var $isNaN = require_isNaN();
	/** @type {import('./sign')} */
	module.exports = function sign(number) {
		if ($isNaN(number) || number === 0) return number;
		return number < 0 ? -1 : 1;
	};
}));
//#endregion
//#region node_modules/gopd/gOPD.js
var require_gOPD = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./gOPD')} */
	module.exports = Object.getOwnPropertyDescriptor;
}));
//#endregion
//#region node_modules/gopd/index.js
var require_gopd = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('.')} */
	var $gOPD = require_gOPD();
	if ($gOPD) try {
		$gOPD([], "length");
	} catch (e) {
		$gOPD = null;
	}
	module.exports = $gOPD;
}));
//#endregion
//#region node_modules/es-define-property/index.js
var require_es_define_property = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('.')} */
	var $defineProperty = Object.defineProperty || false;
	if ($defineProperty) try {
		$defineProperty({}, "a", { value: 1 });
	} catch (e) {
		$defineProperty = false;
	}
	module.exports = $defineProperty;
}));
//#endregion
//#region node_modules/has-symbols/shams.js
var require_shams = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./shams')} */
	module.exports = function hasSymbols() {
		if (typeof Symbol !== "function" || typeof Object.getOwnPropertySymbols !== "function") return false;
		if (typeof Symbol.iterator === "symbol") return true;
		/** @type {{ [k in symbol]?: unknown }} */
		var obj = {};
		var sym = Symbol("test");
		var symObj = Object(sym);
		if (typeof sym === "string") return false;
		if (Object.prototype.toString.call(sym) !== "[object Symbol]") return false;
		if (Object.prototype.toString.call(symObj) !== "[object Symbol]") return false;
		var symVal = 42;
		obj[sym] = symVal;
		for (var _ in obj) return false;
		if (typeof Object.keys === "function" && Object.keys(obj).length !== 0) return false;
		if (typeof Object.getOwnPropertyNames === "function" && Object.getOwnPropertyNames(obj).length !== 0) return false;
		var syms = Object.getOwnPropertySymbols(obj);
		if (syms.length !== 1 || syms[0] !== sym) return false;
		if (!Object.prototype.propertyIsEnumerable.call(obj, sym)) return false;
		if (typeof Object.getOwnPropertyDescriptor === "function") {
			var descriptor = Object.getOwnPropertyDescriptor(obj, sym);
			if (descriptor.value !== symVal || descriptor.enumerable !== true) return false;
		}
		return true;
	};
}));
//#endregion
//#region node_modules/has-symbols/index.js
var require_has_symbols = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var origSymbol = typeof Symbol !== "undefined" && Symbol;
	var hasSymbolSham = require_shams();
	/** @type {import('.')} */
	module.exports = function hasNativeSymbols() {
		if (typeof origSymbol !== "function") return false;
		if (typeof Symbol !== "function") return false;
		if (typeof origSymbol("foo") !== "symbol") return false;
		if (typeof Symbol("bar") !== "symbol") return false;
		return hasSymbolSham();
	};
}));
//#endregion
//#region node_modules/get-proto/Reflect.getPrototypeOf.js
var require_Reflect_getPrototypeOf = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./Reflect.getPrototypeOf')} */
	module.exports = typeof Reflect !== "undefined" && Reflect.getPrototypeOf || null;
}));
//#endregion
//#region node_modules/get-proto/Object.getPrototypeOf.js
var require_Object_getPrototypeOf = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./Object.getPrototypeOf')} */
	module.exports = require_es_object_atoms().getPrototypeOf || null;
}));
//#endregion
//#region node_modules/function-bind/implementation.js
var require_implementation = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var ERROR_MESSAGE = "Function.prototype.bind called on incompatible ";
	var toStr = Object.prototype.toString;
	var max = Math.max;
	var funcType = "[object Function]";
	var concatty = function concatty(a, b) {
		var arr = [];
		for (var i = 0; i < a.length; i += 1) arr[i] = a[i];
		for (var j = 0; j < b.length; j += 1) arr[j + a.length] = b[j];
		return arr;
	};
	var slicy = function slicy(arrLike, offset) {
		var arr = [];
		for (var i = offset || 0, j = 0; i < arrLike.length; i += 1, j += 1) arr[j] = arrLike[i];
		return arr;
	};
	var joiny = function(arr, joiner) {
		var str = "";
		for (var i = 0; i < arr.length; i += 1) {
			str += arr[i];
			if (i + 1 < arr.length) str += joiner;
		}
		return str;
	};
	module.exports = function bind(that) {
		var target = this;
		if (typeof target !== "function" || toStr.apply(target) !== funcType) throw new TypeError(ERROR_MESSAGE + target);
		var args = slicy(arguments, 1);
		var bound;
		var binder = function() {
			if (this instanceof bound) {
				var result = target.apply(this, concatty(args, arguments));
				if (Object(result) === result) return result;
				return this;
			}
			return target.apply(that, concatty(args, arguments));
		};
		var boundLength = max(0, target.length - args.length);
		var boundArgs = [];
		for (var i = 0; i < boundLength; i++) boundArgs[i] = "$" + i;
		bound = Function("binder", "return function (" + joiny(boundArgs, ",") + "){ return binder.apply(this,arguments); }")(binder);
		if (target.prototype) {
			var Empty = function Empty() {};
			Empty.prototype = target.prototype;
			bound.prototype = new Empty();
			Empty.prototype = null;
		}
		return bound;
	};
}));
//#endregion
//#region node_modules/function-bind/index.js
var require_function_bind = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var implementation = require_implementation();
	module.exports = Function.prototype.bind || implementation;
}));
//#endregion
//#region node_modules/call-bind-apply-helpers/functionCall.js
var require_functionCall = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./functionCall')} */
	module.exports = Function.prototype.call;
}));
//#endregion
//#region node_modules/call-bind-apply-helpers/functionApply.js
var require_functionApply = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./functionApply')} */
	module.exports = Function.prototype.apply;
}));
//#endregion
//#region node_modules/call-bind-apply-helpers/reflectApply.js
var require_reflectApply = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** @type {import('./reflectApply')} */
	module.exports = typeof Reflect !== "undefined" && Reflect && Reflect.apply;
}));
//#endregion
//#region node_modules/call-bind-apply-helpers/actualApply.js
var require_actualApply = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var bind = require_function_bind();
	var $apply = require_functionApply();
	var $call = require_functionCall();
	/** @type {import('./actualApply')} */
	module.exports = require_reflectApply() || bind.call($call, $apply);
}));
//#endregion
//#region node_modules/call-bind-apply-helpers/index.js
var require_call_bind_apply_helpers = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var bind = require_function_bind();
	var $TypeError = require_type();
	var $call = require_functionCall();
	var $actualApply = require_actualApply();
	/** @type {(args: [Function, thisArg?: unknown, ...args: unknown[]]) => Function} TODO FIXME, find a way to use import('.') */
	module.exports = function callBindBasic(args) {
		if (args.length < 1 || typeof args[0] !== "function") throw new $TypeError("a function is required");
		return $actualApply(bind, $call, args);
	};
}));
//#endregion
//#region node_modules/dunder-proto/get.js
var require_get = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var callBind = require_call_bind_apply_helpers();
	var gOPD = require_gopd();
	var hasProtoAccessor;
	try {
		hasProtoAccessor = [].__proto__ === Array.prototype;
	} catch (e) {
		if (!e || typeof e !== "object" || !("code" in e) || e.code !== "ERR_PROTO_ACCESS") throw e;
	}
	var desc = !!hasProtoAccessor && gOPD && gOPD(Object.prototype, "__proto__");
	var $Object = Object;
	var $getPrototypeOf = $Object.getPrototypeOf;
	/** @type {import('./get')} */
	module.exports = desc && typeof desc.get === "function" ? callBind([desc.get]) : typeof $getPrototypeOf === "function" ? function getDunder(value) {
		return $getPrototypeOf(value == null ? value : $Object(value));
	} : false;
}));
//#endregion
//#region node_modules/get-proto/index.js
var require_get_proto = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var reflectGetProto = require_Reflect_getPrototypeOf();
	var originalGetProto = require_Object_getPrototypeOf();
	var getDunderProto = require_get();
	/** @type {import('.')} */
	module.exports = reflectGetProto ? function getProto(O) {
		return reflectGetProto(O);
	} : originalGetProto ? function getProto(O) {
		if (!O || typeof O !== "object" && typeof O !== "function") throw new TypeError("getProto: not an object");
		return originalGetProto(O);
	} : getDunderProto ? function getProto(O) {
		return getDunderProto(O);
	} : null;
}));
//#endregion
//#region node_modules/hasown/index.js
var require_hasown = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var call = Function.prototype.call;
	var $hasOwn = Object.prototype.hasOwnProperty;
	/** @type {import('.')} */
	module.exports = require_function_bind().call(call, $hasOwn);
}));
//#endregion
//#region node_modules/get-intrinsic/index.js
var require_get_intrinsic = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var undefined;
	var $Object = require_es_object_atoms();
	var $Error = require_es_errors();
	var $EvalError = require_eval();
	var $RangeError = require_range();
	var $ReferenceError = require_ref();
	var $SyntaxError = require_syntax();
	var $TypeError = require_type();
	var $URIError = require_uri();
	var abs = require_abs();
	var floor = require_floor();
	var max = require_max();
	var min = require_min();
	var pow = require_pow();
	var round = require_round();
	var sign = require_sign();
	var $Function = Function;
	var getEvalledConstructor = function(expressionSyntax) {
		try {
			return $Function("\"use strict\"; return (" + expressionSyntax + ").constructor;")();
		} catch (e) {}
	};
	var $gOPD = require_gopd();
	var $defineProperty = require_es_define_property();
	var throwTypeError = function() {
		throw new $TypeError();
	};
	var ThrowTypeError = $gOPD ? function() {
		try {
			arguments.callee;
			return throwTypeError;
		} catch (calleeThrows) {
			try {
				return $gOPD(arguments, "callee").get;
			} catch (gOPDthrows) {
				return throwTypeError;
			}
		}
	}() : throwTypeError;
	var hasSymbols = require_has_symbols()();
	var getProto = require_get_proto();
	var $ObjectGPO = require_Object_getPrototypeOf();
	var $ReflectGPO = require_Reflect_getPrototypeOf();
	var $apply = require_functionApply();
	var $call = require_functionCall();
	var needsEval = {};
	var TypedArray = typeof Uint8Array === "undefined" || !getProto ? undefined : getProto(Uint8Array);
	var INTRINSICS = {
		__proto__: null,
		"%AggregateError%": typeof AggregateError === "undefined" ? undefined : AggregateError,
		"%Array%": Array,
		"%ArrayBuffer%": typeof ArrayBuffer === "undefined" ? undefined : ArrayBuffer,
		"%ArrayIteratorPrototype%": hasSymbols && getProto ? getProto([][Symbol.iterator]()) : undefined,
		"%AsyncFromSyncIteratorPrototype%": undefined,
		"%AsyncFunction%": needsEval,
		"%AsyncGenerator%": needsEval,
		"%AsyncGeneratorFunction%": needsEval,
		"%AsyncIteratorPrototype%": needsEval,
		"%Atomics%": typeof Atomics === "undefined" ? undefined : Atomics,
		"%BigInt%": typeof BigInt === "undefined" ? undefined : BigInt,
		"%BigInt64Array%": typeof BigInt64Array === "undefined" ? undefined : BigInt64Array,
		"%BigUint64Array%": typeof BigUint64Array === "undefined" ? undefined : BigUint64Array,
		"%Boolean%": Boolean,
		"%DataView%": typeof DataView === "undefined" ? undefined : DataView,
		"%Date%": Date,
		"%decodeURI%": decodeURI,
		"%decodeURIComponent%": decodeURIComponent,
		"%encodeURI%": encodeURI,
		"%encodeURIComponent%": encodeURIComponent,
		"%Error%": $Error,
		"%eval%": eval,
		"%EvalError%": $EvalError,
		"%Float16Array%": typeof Float16Array === "undefined" ? undefined : Float16Array,
		"%Float32Array%": typeof Float32Array === "undefined" ? undefined : Float32Array,
		"%Float64Array%": typeof Float64Array === "undefined" ? undefined : Float64Array,
		"%FinalizationRegistry%": typeof FinalizationRegistry === "undefined" ? undefined : FinalizationRegistry,
		"%Function%": $Function,
		"%GeneratorFunction%": needsEval,
		"%Int8Array%": typeof Int8Array === "undefined" ? undefined : Int8Array,
		"%Int16Array%": typeof Int16Array === "undefined" ? undefined : Int16Array,
		"%Int32Array%": typeof Int32Array === "undefined" ? undefined : Int32Array,
		"%isFinite%": isFinite,
		"%isNaN%": isNaN,
		"%IteratorPrototype%": hasSymbols && getProto ? getProto(getProto([][Symbol.iterator]())) : undefined,
		"%JSON%": typeof JSON === "object" ? JSON : undefined,
		"%Map%": typeof Map === "undefined" ? undefined : Map,
		"%MapIteratorPrototype%": typeof Map === "undefined" || !hasSymbols || !getProto ? undefined : getProto((/* @__PURE__ */ new Map())[Symbol.iterator]()),
		"%Math%": Math,
		"%Number%": Number,
		"%Object%": $Object,
		"%Object.getOwnPropertyDescriptor%": $gOPD,
		"%parseFloat%": parseFloat,
		"%parseInt%": parseInt,
		"%Promise%": typeof Promise === "undefined" ? undefined : Promise,
		"%Proxy%": typeof Proxy === "undefined" ? undefined : Proxy,
		"%RangeError%": $RangeError,
		"%ReferenceError%": $ReferenceError,
		"%Reflect%": typeof Reflect === "undefined" ? undefined : Reflect,
		"%RegExp%": RegExp,
		"%Set%": typeof Set === "undefined" ? undefined : Set,
		"%SetIteratorPrototype%": typeof Set === "undefined" || !hasSymbols || !getProto ? undefined : getProto((/* @__PURE__ */ new Set())[Symbol.iterator]()),
		"%SharedArrayBuffer%": typeof SharedArrayBuffer === "undefined" ? undefined : SharedArrayBuffer,
		"%String%": String,
		"%StringIteratorPrototype%": hasSymbols && getProto ? getProto(""[Symbol.iterator]()) : undefined,
		"%Symbol%": hasSymbols ? Symbol : undefined,
		"%SyntaxError%": $SyntaxError,
		"%ThrowTypeError%": ThrowTypeError,
		"%TypedArray%": TypedArray,
		"%TypeError%": $TypeError,
		"%Uint8Array%": typeof Uint8Array === "undefined" ? undefined : Uint8Array,
		"%Uint8ClampedArray%": typeof Uint8ClampedArray === "undefined" ? undefined : Uint8ClampedArray,
		"%Uint16Array%": typeof Uint16Array === "undefined" ? undefined : Uint16Array,
		"%Uint32Array%": typeof Uint32Array === "undefined" ? undefined : Uint32Array,
		"%URIError%": $URIError,
		"%WeakMap%": typeof WeakMap === "undefined" ? undefined : WeakMap,
		"%WeakRef%": typeof WeakRef === "undefined" ? undefined : WeakRef,
		"%WeakSet%": typeof WeakSet === "undefined" ? undefined : WeakSet,
		"%Function.prototype.call%": $call,
		"%Function.prototype.apply%": $apply,
		"%Object.defineProperty%": $defineProperty,
		"%Object.getPrototypeOf%": $ObjectGPO,
		"%Math.abs%": abs,
		"%Math.floor%": floor,
		"%Math.max%": max,
		"%Math.min%": min,
		"%Math.pow%": pow,
		"%Math.round%": round,
		"%Math.sign%": sign,
		"%Reflect.getPrototypeOf%": $ReflectGPO
	};
	if (getProto) try {
		null.error;
	} catch (e) {
		INTRINSICS["%Error.prototype%"] = getProto(getProto(e));
	}
	var doEval = function doEval(name) {
		var value;
		if (name === "%AsyncFunction%") value = getEvalledConstructor("async function () {}");
		else if (name === "%GeneratorFunction%") value = getEvalledConstructor("function* () {}");
		else if (name === "%AsyncGeneratorFunction%") value = getEvalledConstructor("async function* () {}");
		else if (name === "%AsyncGenerator%") {
			var fn = doEval("%AsyncGeneratorFunction%");
			if (fn) value = fn.prototype;
		} else if (name === "%AsyncIteratorPrototype%") {
			var gen = doEval("%AsyncGenerator%");
			if (gen && getProto) value = getProto(gen.prototype);
		}
		INTRINSICS[name] = value;
		return value;
	};
	var LEGACY_ALIASES = {
		__proto__: null,
		"%ArrayBufferPrototype%": ["ArrayBuffer", "prototype"],
		"%ArrayPrototype%": ["Array", "prototype"],
		"%ArrayProto_entries%": [
			"Array",
			"prototype",
			"entries"
		],
		"%ArrayProto_forEach%": [
			"Array",
			"prototype",
			"forEach"
		],
		"%ArrayProto_keys%": [
			"Array",
			"prototype",
			"keys"
		],
		"%ArrayProto_values%": [
			"Array",
			"prototype",
			"values"
		],
		"%AsyncFunctionPrototype%": ["AsyncFunction", "prototype"],
		"%AsyncGenerator%": ["AsyncGeneratorFunction", "prototype"],
		"%AsyncGeneratorPrototype%": [
			"AsyncGeneratorFunction",
			"prototype",
			"prototype"
		],
		"%BooleanPrototype%": ["Boolean", "prototype"],
		"%DataViewPrototype%": ["DataView", "prototype"],
		"%DatePrototype%": ["Date", "prototype"],
		"%ErrorPrototype%": ["Error", "prototype"],
		"%EvalErrorPrototype%": ["EvalError", "prototype"],
		"%Float32ArrayPrototype%": ["Float32Array", "prototype"],
		"%Float64ArrayPrototype%": ["Float64Array", "prototype"],
		"%FunctionPrototype%": ["Function", "prototype"],
		"%Generator%": ["GeneratorFunction", "prototype"],
		"%GeneratorPrototype%": [
			"GeneratorFunction",
			"prototype",
			"prototype"
		],
		"%Int8ArrayPrototype%": ["Int8Array", "prototype"],
		"%Int16ArrayPrototype%": ["Int16Array", "prototype"],
		"%Int32ArrayPrototype%": ["Int32Array", "prototype"],
		"%JSONParse%": ["JSON", "parse"],
		"%JSONStringify%": ["JSON", "stringify"],
		"%MapPrototype%": ["Map", "prototype"],
		"%NumberPrototype%": ["Number", "prototype"],
		"%ObjectPrototype%": ["Object", "prototype"],
		"%ObjProto_toString%": [
			"Object",
			"prototype",
			"toString"
		],
		"%ObjProto_valueOf%": [
			"Object",
			"prototype",
			"valueOf"
		],
		"%PromisePrototype%": ["Promise", "prototype"],
		"%PromiseProto_then%": [
			"Promise",
			"prototype",
			"then"
		],
		"%Promise_all%": ["Promise", "all"],
		"%Promise_reject%": ["Promise", "reject"],
		"%Promise_resolve%": ["Promise", "resolve"],
		"%RangeErrorPrototype%": ["RangeError", "prototype"],
		"%ReferenceErrorPrototype%": ["ReferenceError", "prototype"],
		"%RegExpPrototype%": ["RegExp", "prototype"],
		"%SetPrototype%": ["Set", "prototype"],
		"%SharedArrayBufferPrototype%": ["SharedArrayBuffer", "prototype"],
		"%StringPrototype%": ["String", "prototype"],
		"%SymbolPrototype%": ["Symbol", "prototype"],
		"%SyntaxErrorPrototype%": ["SyntaxError", "prototype"],
		"%TypedArrayPrototype%": ["TypedArray", "prototype"],
		"%TypeErrorPrototype%": ["TypeError", "prototype"],
		"%Uint8ArrayPrototype%": ["Uint8Array", "prototype"],
		"%Uint8ClampedArrayPrototype%": ["Uint8ClampedArray", "prototype"],
		"%Uint16ArrayPrototype%": ["Uint16Array", "prototype"],
		"%Uint32ArrayPrototype%": ["Uint32Array", "prototype"],
		"%URIErrorPrototype%": ["URIError", "prototype"],
		"%WeakMapPrototype%": ["WeakMap", "prototype"],
		"%WeakSetPrototype%": ["WeakSet", "prototype"]
	};
	var bind = require_function_bind();
	var hasOwn = require_hasown();
	var $concat = bind.call($call, Array.prototype.concat);
	var $spliceApply = bind.call($apply, Array.prototype.splice);
	var $replace = bind.call($call, String.prototype.replace);
	var $strSlice = bind.call($call, String.prototype.slice);
	var $exec = bind.call($call, RegExp.prototype.exec);
	var rePropName = /[^%.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|%$))/g;
	var reEscapeChar = /\\(\\)?/g;
	var stringToPath = function stringToPath(string) {
		var first = $strSlice(string, 0, 1);
		var last = $strSlice(string, -1);
		if (first === "%" && last !== "%") throw new $SyntaxError("invalid intrinsic syntax, expected closing `%`");
		else if (last === "%" && first !== "%") throw new $SyntaxError("invalid intrinsic syntax, expected opening `%`");
		var result = [];
		$replace(string, rePropName, function(match, number, quote, subString) {
			result[result.length] = quote ? $replace(subString, reEscapeChar, "$1") : number || match;
		});
		return result;
	};
	var getBaseIntrinsic = function getBaseIntrinsic(name, allowMissing) {
		var intrinsicName = name;
		var alias;
		if (hasOwn(LEGACY_ALIASES, intrinsicName)) {
			alias = LEGACY_ALIASES[intrinsicName];
			intrinsicName = "%" + alias[0] + "%";
		}
		if (hasOwn(INTRINSICS, intrinsicName)) {
			var value = INTRINSICS[intrinsicName];
			if (value === needsEval) value = doEval(intrinsicName);
			if (typeof value === "undefined" && !allowMissing) throw new $TypeError("intrinsic " + name + " exists, but is not available. Please file an issue!");
			return {
				alias,
				name: intrinsicName,
				value
			};
		}
		throw new $SyntaxError("intrinsic " + name + " does not exist!");
	};
	module.exports = function GetIntrinsic(name, allowMissing) {
		if (typeof name !== "string" || name.length === 0) throw new $TypeError("intrinsic name must be a non-empty string");
		if (arguments.length > 1 && typeof allowMissing !== "boolean") throw new $TypeError("\"allowMissing\" argument must be a boolean");
		if ($exec(/^%?[^%]*%?$/, name) === null) throw new $SyntaxError("`%` may not be present anywhere but at the beginning and end of the intrinsic name");
		var parts = stringToPath(name);
		var intrinsicBaseName = parts.length > 0 ? parts[0] : "";
		var intrinsic = getBaseIntrinsic("%" + intrinsicBaseName + "%", allowMissing);
		var intrinsicRealName = intrinsic.name;
		var value = intrinsic.value;
		var skipFurtherCaching = false;
		var alias = intrinsic.alias;
		if (alias) {
			intrinsicBaseName = alias[0];
			$spliceApply(parts, $concat([0, 1], alias));
		}
		for (var i = 1, isOwn = true; i < parts.length; i += 1) {
			var part = parts[i];
			var first = $strSlice(part, 0, 1);
			var last = $strSlice(part, -1);
			if ((first === "\"" || first === "'" || first === "`" || last === "\"" || last === "'" || last === "`") && first !== last) throw new $SyntaxError("property names with quotes must have matching quotes");
			if (part === "constructor" || !isOwn) skipFurtherCaching = true;
			intrinsicBaseName += "." + part;
			intrinsicRealName = "%" + intrinsicBaseName + "%";
			if (hasOwn(INTRINSICS, intrinsicRealName)) value = INTRINSICS[intrinsicRealName];
			else if (value != null) {
				if (!(part in value)) {
					if (!allowMissing) throw new $TypeError("base intrinsic for " + name + " exists, but the property is not available.");
					return;
				}
				if ($gOPD && i + 1 >= parts.length) {
					var desc = $gOPD(value, part);
					isOwn = !!desc;
					if (isOwn && "get" in desc && !("originalValue" in desc.get)) value = desc.get;
					else value = value[part];
				} else {
					isOwn = hasOwn(value, part);
					value = value[part];
				}
				if (isOwn && !skipFurtherCaching) INTRINSICS[intrinsicRealName] = value;
			}
		}
		return value;
	};
}));
//#endregion
//#region node_modules/call-bound/index.js
var require_call_bound = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var GetIntrinsic = require_get_intrinsic();
	var callBindBasic = require_call_bind_apply_helpers();
	/** @type {(thisArg: string, searchString: string, position?: number) => number} */
	var $indexOf = callBindBasic([GetIntrinsic("%String.prototype.indexOf%")]);
	/** @type {import('.')} */
	module.exports = function callBoundIntrinsic(name, allowMissing) {
		var intrinsic = GetIntrinsic(name, !!allowMissing);
		if (typeof intrinsic === "function" && $indexOf(name, ".prototype.") > -1) return callBindBasic([intrinsic]);
		return intrinsic;
	};
}));
//#endregion
//#region node_modules/side-channel-map/index.js
var require_side_channel_map = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var GetIntrinsic = require_get_intrinsic();
	var callBound = require_call_bound();
	var inspect = require_object_inspect();
	var $TypeError = require_type();
	var $Map = GetIntrinsic("%Map%", true);
	/** @type {<K, V>(thisArg: Map<K, V>, key: K) => V} */
	var $mapGet = callBound("Map.prototype.get", true);
	/** @type {<K, V>(thisArg: Map<K, V>, key: K, value: V) => void} */
	var $mapSet = callBound("Map.prototype.set", true);
	/** @type {<K, V>(thisArg: Map<K, V>, key: K) => boolean} */
	var $mapHas = callBound("Map.prototype.has", true);
	/** @type {<K, V>(thisArg: Map<K, V>, key: K) => boolean} */
	var $mapDelete = callBound("Map.prototype.delete", true);
	/** @type {<K, V>(thisArg: Map<K, V>) => number} */
	var $mapSize = callBound("Map.prototype.size", true);
	/** @type {import('.')} */
	module.exports = !!$Map && function getSideChannelMap() {
		/** @typedef {ReturnType<typeof getSideChannelMap>} Channel */
		/** @typedef {Parameters<Channel['get']>[0]} K */
		/** @typedef {Parameters<Channel['set']>[1]} V */
		/** @type {Map<K, V> | undefined} */ var $m;
		/** @type {Channel} */
		var channel = {
			assert: function(key) {
				if (!channel.has(key)) throw new $TypeError("Side channel does not contain " + inspect(key));
			},
			"delete": function(key) {
				if ($m) {
					var result = $mapDelete($m, key);
					if ($mapSize($m) === 0) $m = void 0;
					return result;
				}
				return false;
			},
			get: function(key) {
				if ($m) return $mapGet($m, key);
			},
			has: function(key) {
				if ($m) return $mapHas($m, key);
				return false;
			},
			set: function(key, value) {
				if (!$m) $m = new $Map();
				$mapSet($m, key, value);
			}
		};
		return channel;
	};
}));
//#endregion
//#region node_modules/side-channel-weakmap/index.js
var require_side_channel_weakmap = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var GetIntrinsic = require_get_intrinsic();
	var callBound = require_call_bound();
	var inspect = require_object_inspect();
	var getSideChannelMap = require_side_channel_map();
	var $TypeError = require_type();
	var $WeakMap = GetIntrinsic("%WeakMap%", true);
	/** @type {<K extends object, V>(thisArg: WeakMap<K, V>, key: K) => V} */
	var $weakMapGet = callBound("WeakMap.prototype.get", true);
	/** @type {<K extends object, V>(thisArg: WeakMap<K, V>, key: K, value: V) => void} */
	var $weakMapSet = callBound("WeakMap.prototype.set", true);
	/** @type {<K extends object, V>(thisArg: WeakMap<K, V>, key: K) => boolean} */
	var $weakMapHas = callBound("WeakMap.prototype.has", true);
	/** @type {<K extends object, V>(thisArg: WeakMap<K, V>, key: K) => boolean} */
	var $weakMapDelete = callBound("WeakMap.prototype.delete", true);
	/** @type {import('.')} */
	module.exports = $WeakMap ? function getSideChannelWeakMap() {
		/** @typedef {ReturnType<typeof getSideChannelWeakMap>} Channel */
		/** @typedef {Parameters<Channel['get']>[0]} K */
		/** @typedef {Parameters<Channel['set']>[1]} V */
		/** @type {WeakMap<K & object, V> | undefined} */ var $wm;
		/** @type {Channel | undefined} */ var $m;
		/** @type {Channel} */
		var channel = {
			assert: function(key) {
				if (!channel.has(key)) throw new $TypeError("Side channel does not contain " + inspect(key));
			},
			"delete": function(key) {
				if ($WeakMap && key && (typeof key === "object" || typeof key === "function")) {
					if ($wm) return $weakMapDelete($wm, key);
				} else if (getSideChannelMap) {
					if ($m) return $m["delete"](key);
				}
				return false;
			},
			get: function(key) {
				if ($WeakMap && key && (typeof key === "object" || typeof key === "function")) {
					if ($wm) return $weakMapGet($wm, key);
				}
				return $m && $m.get(key);
			},
			has: function(key) {
				if ($WeakMap && key && (typeof key === "object" || typeof key === "function")) {
					if ($wm) return $weakMapHas($wm, key);
				}
				return !!$m && $m.has(key);
			},
			set: function(key, value) {
				if ($WeakMap && key && (typeof key === "object" || typeof key === "function")) {
					if (!$wm) $wm = new $WeakMap();
					$weakMapSet($wm, key, value);
				} else if (getSideChannelMap) {
					if (!$m) $m = getSideChannelMap();
					/** @type {NonNullable<typeof $m>} */ $m.set(key, value);
				}
			}
		};
		return channel;
	} : getSideChannelMap;
}));
//#endregion
//#region node_modules/side-channel/index.js
var require_side_channel = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var $TypeError = require_type();
	var inspect = require_object_inspect();
	var getSideChannelList = require_side_channel_list();
	var getSideChannelMap = require_side_channel_map();
	var makeChannel = require_side_channel_weakmap() || getSideChannelMap || getSideChannelList;
	/** @type {import('.')} */
	module.exports = function getSideChannel() {
		/** @typedef {ReturnType<typeof getSideChannel>} Channel */
		/** @type {Channel | undefined} */ var $channelData;
		/** @type {Channel} */
		var channel = {
			assert: function(key) {
				if (!channel.has(key)) throw new $TypeError("Side channel does not contain " + inspect(key));
			},
			"delete": function(key) {
				return !!$channelData && $channelData["delete"](key);
			},
			get: function(key) {
				return $channelData && $channelData.get(key);
			},
			has: function(key) {
				return !!$channelData && $channelData.has(key);
			},
			set: function(key, value) {
				if (!$channelData) $channelData = makeChannel();
				$channelData.set(key, value);
			}
		};
		return channel;
	};
}));
//#endregion
//#region node_modules/qs/lib/formats.js
var require_formats = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var replace = String.prototype.replace;
	var percentTwenties = /%20/g;
	var Format = {
		RFC1738: "RFC1738",
		RFC3986: "RFC3986"
	};
	module.exports = {
		"default": Format.RFC3986,
		formatters: {
			RFC1738: function(value) {
				return replace.call(value, percentTwenties, "+");
			},
			RFC3986: function(value) {
				return String(value);
			}
		},
		RFC1738: Format.RFC1738,
		RFC3986: Format.RFC3986
	};
}));
//#endregion
//#region node_modules/qs/lib/utils.js
var require_utils = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var formats = require_formats();
	var getSideChannel = require_side_channel();
	var has = Object.prototype.hasOwnProperty;
	var isArray = Array.isArray;
	var overflowChannel = getSideChannel();
	var markOverflow = function markOverflow(obj, maxIndex) {
		overflowChannel.set(obj, maxIndex);
		return obj;
	};
	var isOverflow = function isOverflow(obj) {
		return overflowChannel.has(obj);
	};
	var getMaxIndex = function getMaxIndex(obj) {
		return overflowChannel.get(obj);
	};
	var setMaxIndex = function setMaxIndex(obj, maxIndex) {
		overflowChannel.set(obj, maxIndex);
	};
	var hexTable = function() {
		var array = [];
		for (var i = 0; i < 256; ++i) array[array.length] = "%" + ((i < 16 ? "0" : "") + i.toString(16)).toUpperCase();
		return array;
	}();
	var compactQueue = function compactQueue(queue) {
		while (queue.length > 1) {
			var item = queue.pop();
			var obj = item.obj[item.prop];
			if (isArray(obj)) {
				var compacted = [];
				for (var j = 0; j < obj.length; ++j) if (typeof obj[j] !== "undefined") compacted[compacted.length] = obj[j];
				item.obj[item.prop] = compacted;
			}
		}
	};
	var arrayToObject = function arrayToObject(source, options) {
		var obj = options && options.plainObjects ? { __proto__: null } : {};
		for (var i = 0; i < source.length; ++i) if (typeof source[i] !== "undefined") obj[i] = source[i];
		return obj;
	};
	var merge = function merge(target, source, options) {
		if (!source) return target;
		if (typeof source !== "object" && typeof source !== "function") {
			if (isArray(target)) {
				var nextIndex = target.length;
				if (options && typeof options.arrayLimit === "number" && nextIndex > options.arrayLimit) return markOverflow(arrayToObject(target.concat(source), options), nextIndex);
				target[nextIndex] = source;
			} else if (target && typeof target === "object") {
				if (isOverflow(target)) {
					var newIndex = getMaxIndex(target) + 1;
					target[newIndex] = source;
					setMaxIndex(target, newIndex);
				} else if (options && options.strictMerge) return [target, source];
				else if (options && (options.plainObjects || options.allowPrototypes) || !has.call(Object.prototype, source)) target[source] = true;
			} else return [target, source];
			return target;
		}
		if (!target || typeof target !== "object") {
			if (isOverflow(source)) {
				var sourceKeys = Object.keys(source);
				var result = options && options.plainObjects ? {
					__proto__: null,
					0: target
				} : { 0: target };
				for (var m = 0; m < sourceKeys.length; m++) {
					var oldKey = parseInt(sourceKeys[m], 10);
					result[oldKey + 1] = source[sourceKeys[m]];
				}
				return markOverflow(result, getMaxIndex(source) + 1);
			}
			var combined = [target].concat(source);
			if (options && typeof options.arrayLimit === "number" && combined.length > options.arrayLimit) return markOverflow(arrayToObject(combined, options), combined.length - 1);
			return combined;
		}
		var mergeTarget = target;
		if (isArray(target) && !isArray(source)) mergeTarget = arrayToObject(target, options);
		if (isArray(target) && isArray(source)) {
			source.forEach(function(item, i) {
				if (has.call(target, i)) {
					var targetItem = target[i];
					if (targetItem && typeof targetItem === "object" && item && typeof item === "object") target[i] = merge(targetItem, item, options);
					else target[target.length] = item;
				} else target[i] = item;
			});
			return target;
		}
		return Object.keys(source).reduce(function(acc, key) {
			var value = source[key];
			if (has.call(acc, key)) acc[key] = merge(acc[key], value, options);
			else acc[key] = value;
			if (isOverflow(source) && !isOverflow(acc)) markOverflow(acc, getMaxIndex(source));
			if (isOverflow(acc)) {
				var keyNum = parseInt(key, 10);
				if (String(keyNum) === key && keyNum >= 0 && keyNum > getMaxIndex(acc)) setMaxIndex(acc, keyNum);
			}
			return acc;
		}, mergeTarget);
	};
	var assign = function assignSingleSource(target, source) {
		return Object.keys(source).reduce(function(acc, key) {
			acc[key] = source[key];
			return acc;
		}, target);
	};
	var decode = function(str, defaultDecoder, charset) {
		var strWithoutPlus = str.replace(/\+/g, " ");
		if (charset === "iso-8859-1") return strWithoutPlus.replace(/%[0-9a-f]{2}/gi, unescape);
		try {
			return decodeURIComponent(strWithoutPlus);
		} catch (e) {
			return strWithoutPlus;
		}
	};
	var limit = 1024;
	module.exports = {
		arrayToObject,
		assign,
		combine: function combine(a, b, arrayLimit, plainObjects) {
			if (isOverflow(a)) {
				var newIndex = getMaxIndex(a) + 1;
				a[newIndex] = b;
				setMaxIndex(a, newIndex);
				return a;
			}
			var result = [].concat(a, b);
			if (result.length > arrayLimit) return markOverflow(arrayToObject(result, { plainObjects }), result.length - 1);
			return result;
		},
		compact: function compact(value) {
			var queue = [{
				obj: { o: value },
				prop: "o"
			}];
			var refs = [];
			for (var i = 0; i < queue.length; ++i) {
				var item = queue[i];
				var obj = item.obj[item.prop];
				var keys = Object.keys(obj);
				for (var j = 0; j < keys.length; ++j) {
					var key = keys[j];
					var val = obj[key];
					if (typeof val === "object" && val !== null && refs.indexOf(val) === -1) {
						queue[queue.length] = {
							obj,
							prop: key
						};
						refs[refs.length] = val;
					}
				}
			}
			compactQueue(queue);
			return value;
		},
		decode,
		encode: function encode(str, defaultEncoder, charset, kind, format) {
			if (str.length === 0) return str;
			var string = str;
			if (typeof str === "symbol") string = Symbol.prototype.toString.call(str);
			else if (typeof str !== "string") string = String(str);
			if (charset === "iso-8859-1") return escape(string).replace(/%u[0-9a-f]{4}/gi, function($0) {
				return "%26%23" + parseInt($0.slice(2), 16) + "%3B";
			});
			var out = "";
			for (var j = 0; j < string.length; j += limit) {
				var segment = string.length >= limit ? string.slice(j, j + limit) : string;
				var arr = [];
				for (var i = 0; i < segment.length; ++i) {
					var c = segment.charCodeAt(i);
					if (c === 45 || c === 46 || c === 95 || c === 126 || c >= 48 && c <= 57 || c >= 65 && c <= 90 || c >= 97 && c <= 122 || format === formats.RFC1738 && (c === 40 || c === 41)) {
						arr[arr.length] = segment.charAt(i);
						continue;
					}
					if (c < 128) {
						arr[arr.length] = hexTable[c];
						continue;
					}
					if (c < 2048) {
						arr[arr.length] = hexTable[192 | c >> 6] + hexTable[128 | c & 63];
						continue;
					}
					if (c < 55296 || c >= 57344) {
						arr[arr.length] = hexTable[224 | c >> 12] + hexTable[128 | c >> 6 & 63] + hexTable[128 | c & 63];
						continue;
					}
					i += 1;
					c = 65536 + ((c & 1023) << 10 | segment.charCodeAt(i) & 1023);
					arr[arr.length] = hexTable[240 | c >> 18] + hexTable[128 | c >> 12 & 63] + hexTable[128 | c >> 6 & 63] + hexTable[128 | c & 63];
				}
				out += arr.join("");
			}
			return out;
		},
		isBuffer: function isBuffer(obj) {
			if (!obj || typeof obj !== "object") return false;
			return !!(obj.constructor && obj.constructor.isBuffer && obj.constructor.isBuffer(obj));
		},
		isOverflow,
		isRegExp: function isRegExp(obj) {
			return Object.prototype.toString.call(obj) === "[object RegExp]";
		},
		markOverflow,
		maybeMap: function maybeMap(val, fn) {
			if (isArray(val)) {
				var mapped = [];
				for (var i = 0; i < val.length; i += 1) mapped[mapped.length] = fn(val[i]);
				return mapped;
			}
			return fn(val);
		},
		merge
	};
}));
//#endregion
//#region node_modules/qs/lib/stringify.js
var require_stringify = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var getSideChannel = require_side_channel();
	var utils = require_utils();
	var formats = require_formats();
	var has = Object.prototype.hasOwnProperty;
	var arrayPrefixGenerators = {
		brackets: function brackets(prefix) {
			return prefix + "[]";
		},
		comma: "comma",
		indices: function indices(prefix, key) {
			return prefix + "[" + key + "]";
		},
		repeat: function repeat(prefix) {
			return prefix;
		}
	};
	var isArray = Array.isArray;
	var push = Array.prototype.push;
	var pushToArray = function(arr, valueOrArray) {
		push.apply(arr, isArray(valueOrArray) ? valueOrArray : [valueOrArray]);
	};
	var toISO = Date.prototype.toISOString;
	var defaultFormat = formats["default"];
	var defaults = {
		addQueryPrefix: false,
		allowDots: false,
		allowEmptyArrays: false,
		arrayFormat: "indices",
		charset: "utf-8",
		charsetSentinel: false,
		commaRoundTrip: false,
		delimiter: "&",
		encode: true,
		encodeDotInKeys: false,
		encoder: utils.encode,
		encodeValuesOnly: false,
		filter: void 0,
		format: defaultFormat,
		formatter: formats.formatters[defaultFormat],
		indices: false,
		serializeDate: function serializeDate(date) {
			return toISO.call(date);
		},
		skipNulls: false,
		strictNullHandling: false
	};
	var isNonNullishPrimitive = function isNonNullishPrimitive(v) {
		return typeof v === "string" || typeof v === "number" || typeof v === "boolean" || typeof v === "symbol" || typeof v === "bigint";
	};
	var sentinel = {};
	var stringify = function stringify(object, prefix, generateArrayPrefix, commaRoundTrip, allowEmptyArrays, strictNullHandling, skipNulls, encodeDotInKeys, encoder, filter, sort, allowDots, serializeDate, format, formatter, encodeValuesOnly, charset, sideChannel) {
		var obj = object;
		var tmpSc = sideChannel;
		var step = 0;
		var findFlag = false;
		while ((tmpSc = tmpSc.get(sentinel)) !== void 0 && !findFlag) {
			var pos = tmpSc.get(object);
			step += 1;
			if (typeof pos !== "undefined") if (pos === step) throw new RangeError("Cyclic object value");
			else findFlag = true;
			if (typeof tmpSc.get(sentinel) === "undefined") step = 0;
		}
		if (typeof filter === "function") obj = filter(prefix, obj);
		else if (obj instanceof Date) obj = serializeDate(obj);
		else if (generateArrayPrefix === "comma" && isArray(obj)) obj = utils.maybeMap(obj, function(value) {
			if (value instanceof Date) return serializeDate(value);
			return value;
		});
		if (obj === null) {
			if (strictNullHandling) return formatter(encoder && !encodeValuesOnly ? encoder(prefix, defaults.encoder, charset, "key", format) : prefix);
			obj = "";
		}
		if (isNonNullishPrimitive(obj) || utils.isBuffer(obj)) {
			if (encoder) return [formatter(encodeValuesOnly ? prefix : encoder(prefix, defaults.encoder, charset, "key", format)) + "=" + formatter(encoder(obj, defaults.encoder, charset, "value", format))];
			return [formatter(prefix) + "=" + formatter(String(obj))];
		}
		var values = [];
		if (typeof obj === "undefined") return values;
		var objKeys;
		if (generateArrayPrefix === "comma" && isArray(obj)) {
			if (encodeValuesOnly && encoder) obj = utils.maybeMap(obj, function(v) {
				return v == null ? v : encoder(v);
			});
			objKeys = [{ value: obj.length > 0 ? obj.join(",") || null : void 0 }];
		} else if (isArray(filter)) objKeys = filter;
		else {
			var keys = Object.keys(obj);
			objKeys = sort ? keys.sort(sort) : keys;
		}
		var encodedPrefix = encodeDotInKeys ? String(prefix).replace(/\./g, "%2E") : String(prefix);
		var adjustedPrefix = commaRoundTrip && isArray(obj) && obj.length === 1 ? encodedPrefix + "[]" : encodedPrefix;
		if (allowEmptyArrays && isArray(obj) && obj.length === 0) return adjustedPrefix + "[]";
		for (var j = 0; j < objKeys.length; ++j) {
			var key = objKeys[j];
			var value = typeof key === "object" && key && typeof key.value !== "undefined" ? key.value : obj[key];
			if (skipNulls && value === null) continue;
			var encodedKey = allowDots && encodeDotInKeys ? String(key).replace(/\./g, "%2E") : String(key);
			var keyPrefix = isArray(obj) ? typeof generateArrayPrefix === "function" ? generateArrayPrefix(adjustedPrefix, encodedKey) : adjustedPrefix : adjustedPrefix + (allowDots ? "." + encodedKey : "[" + encodedKey + "]");
			sideChannel.set(object, step);
			var valueSideChannel = getSideChannel();
			valueSideChannel.set(sentinel, sideChannel);
			pushToArray(values, stringify(value, keyPrefix, generateArrayPrefix, commaRoundTrip, allowEmptyArrays, strictNullHandling, skipNulls, encodeDotInKeys, generateArrayPrefix === "comma" && encodeValuesOnly && isArray(obj) ? null : encoder, filter, sort, allowDots, serializeDate, format, formatter, encodeValuesOnly, charset, valueSideChannel));
		}
		return values;
	};
	var normalizeStringifyOptions = function normalizeStringifyOptions(opts) {
		if (!opts) return defaults;
		if (typeof opts.allowEmptyArrays !== "undefined" && typeof opts.allowEmptyArrays !== "boolean") throw new TypeError("`allowEmptyArrays` option can only be `true` or `false`, when provided");
		if (typeof opts.encodeDotInKeys !== "undefined" && typeof opts.encodeDotInKeys !== "boolean") throw new TypeError("`encodeDotInKeys` option can only be `true` or `false`, when provided");
		if (opts.encoder !== null && typeof opts.encoder !== "undefined" && typeof opts.encoder !== "function") throw new TypeError("Encoder has to be a function.");
		var charset = opts.charset || defaults.charset;
		if (typeof opts.charset !== "undefined" && opts.charset !== "utf-8" && opts.charset !== "iso-8859-1") throw new TypeError("The charset option must be either utf-8, iso-8859-1, or undefined");
		var format = formats["default"];
		if (typeof opts.format !== "undefined") {
			if (!has.call(formats.formatters, opts.format)) throw new TypeError("Unknown format option provided.");
			format = opts.format;
		}
		var formatter = formats.formatters[format];
		var filter = defaults.filter;
		if (typeof opts.filter === "function" || isArray(opts.filter)) filter = opts.filter;
		var arrayFormat;
		if (opts.arrayFormat in arrayPrefixGenerators) arrayFormat = opts.arrayFormat;
		else if ("indices" in opts) arrayFormat = opts.indices ? "indices" : "repeat";
		else arrayFormat = defaults.arrayFormat;
		if ("commaRoundTrip" in opts && typeof opts.commaRoundTrip !== "boolean") throw new TypeError("`commaRoundTrip` must be a boolean, or absent");
		var allowDots = typeof opts.allowDots === "undefined" ? opts.encodeDotInKeys === true ? true : defaults.allowDots : !!opts.allowDots;
		return {
			addQueryPrefix: typeof opts.addQueryPrefix === "boolean" ? opts.addQueryPrefix : defaults.addQueryPrefix,
			allowDots,
			allowEmptyArrays: typeof opts.allowEmptyArrays === "boolean" ? !!opts.allowEmptyArrays : defaults.allowEmptyArrays,
			arrayFormat,
			charset,
			charsetSentinel: typeof opts.charsetSentinel === "boolean" ? opts.charsetSentinel : defaults.charsetSentinel,
			commaRoundTrip: !!opts.commaRoundTrip,
			delimiter: typeof opts.delimiter === "undefined" ? defaults.delimiter : opts.delimiter,
			encode: typeof opts.encode === "boolean" ? opts.encode : defaults.encode,
			encodeDotInKeys: typeof opts.encodeDotInKeys === "boolean" ? opts.encodeDotInKeys : defaults.encodeDotInKeys,
			encoder: typeof opts.encoder === "function" ? opts.encoder : defaults.encoder,
			encodeValuesOnly: typeof opts.encodeValuesOnly === "boolean" ? opts.encodeValuesOnly : defaults.encodeValuesOnly,
			filter,
			format,
			formatter,
			serializeDate: typeof opts.serializeDate === "function" ? opts.serializeDate : defaults.serializeDate,
			skipNulls: typeof opts.skipNulls === "boolean" ? opts.skipNulls : defaults.skipNulls,
			sort: typeof opts.sort === "function" ? opts.sort : null,
			strictNullHandling: typeof opts.strictNullHandling === "boolean" ? opts.strictNullHandling : defaults.strictNullHandling
		};
	};
	module.exports = function(object, opts) {
		var obj = object;
		var options = normalizeStringifyOptions(opts);
		var objKeys;
		var filter;
		if (typeof options.filter === "function") {
			filter = options.filter;
			obj = filter("", obj);
		} else if (isArray(options.filter)) {
			filter = options.filter;
			objKeys = filter;
		}
		var keys = [];
		if (typeof obj !== "object" || obj === null) return "";
		var generateArrayPrefix = arrayPrefixGenerators[options.arrayFormat];
		var commaRoundTrip = generateArrayPrefix === "comma" && options.commaRoundTrip;
		if (!objKeys) objKeys = Object.keys(obj);
		if (options.sort) objKeys.sort(options.sort);
		var sideChannel = getSideChannel();
		for (var i = 0; i < objKeys.length; ++i) {
			var key = objKeys[i];
			if (typeof key === "undefined" || key === null) continue;
			var value = obj[key];
			if (options.skipNulls && value === null) continue;
			pushToArray(keys, stringify(value, key, generateArrayPrefix, commaRoundTrip, options.allowEmptyArrays, options.strictNullHandling, options.skipNulls, options.encodeDotInKeys, options.encode ? options.encoder : null, options.filter, options.sort, options.allowDots, options.serializeDate, options.format, options.formatter, options.encodeValuesOnly, options.charset, sideChannel));
		}
		var joined = keys.join(options.delimiter);
		var prefix = options.addQueryPrefix === true ? "?" : "";
		if (options.charsetSentinel) if (options.charset === "iso-8859-1") prefix += "utf8=%26%2310003%3B" + options.delimiter;
		else prefix += "utf8=%E2%9C%93" + options.delimiter;
		return joined.length > 0 ? prefix + joined : "";
	};
}));
//#endregion
//#region node_modules/qs/lib/parse.js
var require_parse = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var utils = require_utils();
	var has = Object.prototype.hasOwnProperty;
	var isArray = Array.isArray;
	var defaults = {
		allowDots: false,
		allowEmptyArrays: false,
		allowPrototypes: false,
		allowSparse: false,
		arrayLimit: 20,
		charset: "utf-8",
		charsetSentinel: false,
		comma: false,
		decodeDotInKeys: false,
		decoder: utils.decode,
		delimiter: "&",
		depth: 5,
		duplicates: "combine",
		ignoreQueryPrefix: false,
		interpretNumericEntities: false,
		parameterLimit: 1e3,
		parseArrays: true,
		plainObjects: false,
		strictDepth: false,
		strictMerge: true,
		strictNullHandling: false,
		throwOnLimitExceeded: false
	};
	var interpretNumericEntities = function(str) {
		return str.replace(/&#(\d+);/g, function($0, numberStr) {
			return String.fromCharCode(parseInt(numberStr, 10));
		});
	};
	var parseArrayValue = function(val, options, currentArrayLength) {
		if (val && typeof val === "string" && options.comma && val.indexOf(",") > -1) return val.split(",");
		if (options.throwOnLimitExceeded && currentArrayLength >= options.arrayLimit) throw new RangeError("Array limit exceeded. Only " + options.arrayLimit + " element" + (options.arrayLimit === 1 ? "" : "s") + " allowed in an array.");
		return val;
	};
	var isoSentinel = "utf8=%26%2310003%3B";
	var charsetSentinel = "utf8=%E2%9C%93";
	var parseValues = function parseQueryStringValues(str, options) {
		var obj = { __proto__: null };
		var cleanStr = options.ignoreQueryPrefix ? str.replace(/^\?/, "") : str;
		cleanStr = cleanStr.replace(/%5B/gi, "[").replace(/%5D/gi, "]");
		var limit = options.parameterLimit === Infinity ? void 0 : options.parameterLimit;
		var parts = cleanStr.split(options.delimiter, options.throwOnLimitExceeded && typeof limit !== "undefined" ? limit + 1 : limit);
		if (options.throwOnLimitExceeded && typeof limit !== "undefined" && parts.length > limit) throw new RangeError("Parameter limit exceeded. Only " + limit + " parameter" + (limit === 1 ? "" : "s") + " allowed.");
		var skipIndex = -1;
		var i;
		var charset = options.charset;
		if (options.charsetSentinel) {
			for (i = 0; i < parts.length; ++i) if (parts[i].indexOf("utf8=") === 0) {
				if (parts[i] === charsetSentinel) charset = "utf-8";
				else if (parts[i] === isoSentinel) charset = "iso-8859-1";
				skipIndex = i;
				i = parts.length;
			}
		}
		for (i = 0; i < parts.length; ++i) {
			if (i === skipIndex) continue;
			var part = parts[i];
			var bracketEqualsPos = part.indexOf("]=");
			var pos = bracketEqualsPos === -1 ? part.indexOf("=") : bracketEqualsPos + 1;
			var key;
			var val;
			if (pos === -1) {
				key = options.decoder(part, defaults.decoder, charset, "key");
				val = options.strictNullHandling ? null : "";
			} else {
				key = options.decoder(part.slice(0, pos), defaults.decoder, charset, "key");
				if (key !== null) val = utils.maybeMap(parseArrayValue(part.slice(pos + 1), options, isArray(obj[key]) ? obj[key].length : 0), function(encodedVal) {
					return options.decoder(encodedVal, defaults.decoder, charset, "value");
				});
			}
			if (val && options.interpretNumericEntities && charset === "iso-8859-1") val = interpretNumericEntities(String(val));
			if (part.indexOf("[]=") > -1) val = isArray(val) ? [val] : val;
			if (options.comma && isArray(val) && val.length > options.arrayLimit) {
				if (options.throwOnLimitExceeded) throw new RangeError("Array limit exceeded. Only " + options.arrayLimit + " element" + (options.arrayLimit === 1 ? "" : "s") + " allowed in an array.");
				val = utils.combine([], val, options.arrayLimit, options.plainObjects);
			}
			if (key !== null) {
				var existing = has.call(obj, key);
				if (existing && (options.duplicates === "combine" || part.indexOf("[]=") > -1)) obj[key] = utils.combine(obj[key], val, options.arrayLimit, options.plainObjects);
				else if (!existing || options.duplicates === "last") obj[key] = val;
			}
		}
		return obj;
	};
	var parseObject = function(chain, val, options, valuesParsed) {
		var currentArrayLength = 0;
		if (chain.length > 0 && chain[chain.length - 1] === "[]") {
			var parentKey = chain.slice(0, -1).join("");
			currentArrayLength = Array.isArray(val) && val[parentKey] ? val[parentKey].length : 0;
		}
		var leaf = valuesParsed ? val : parseArrayValue(val, options, currentArrayLength);
		for (var i = chain.length - 1; i >= 0; --i) {
			var obj;
			var root = chain[i];
			if (root === "[]" && options.parseArrays) if (utils.isOverflow(leaf)) obj = leaf;
			else obj = options.allowEmptyArrays && (leaf === "" || options.strictNullHandling && leaf === null) ? [] : utils.combine([], leaf, options.arrayLimit, options.plainObjects);
			else {
				obj = options.plainObjects ? { __proto__: null } : {};
				var cleanRoot = root.charAt(0) === "[" && root.charAt(root.length - 1) === "]" ? root.slice(1, -1) : root;
				var decodedRoot = options.decodeDotInKeys ? cleanRoot.replace(/%2E/g, ".") : cleanRoot;
				var index = parseInt(decodedRoot, 10);
				var isValidArrayIndex = !isNaN(index) && root !== decodedRoot && String(index) === decodedRoot && index >= 0 && options.parseArrays;
				if (!options.parseArrays && decodedRoot === "") obj = { 0: leaf };
				else if (isValidArrayIndex && index < options.arrayLimit) {
					obj = [];
					obj[index] = leaf;
				} else if (isValidArrayIndex && options.throwOnLimitExceeded) throw new RangeError("Array limit exceeded. Only " + options.arrayLimit + " element" + (options.arrayLimit === 1 ? "" : "s") + " allowed in an array.");
				else if (isValidArrayIndex) {
					obj[index] = leaf;
					utils.markOverflow(obj, index);
				} else if (decodedRoot !== "__proto__") obj[decodedRoot] = leaf;
			}
			leaf = obj;
		}
		return leaf;
	};
	var splitKeyIntoSegments = function splitKeyIntoSegments(originalKey, options) {
		var key = options.allowDots ? originalKey.replace(/\.([^.[]+)/g, "[$1]") : originalKey;
		if (options.depth <= 0) {
			if (!options.plainObjects && has.call(Object.prototype, key)) {
				if (!options.allowPrototypes) return;
			}
			return [key];
		}
		var segments = [];
		var first = key.indexOf("[");
		var parent = first >= 0 ? key.slice(0, first) : key;
		if (parent) {
			if (!options.plainObjects && has.call(Object.prototype, parent)) {
				if (!options.allowPrototypes) return;
			}
			segments[segments.length] = parent;
		}
		var n = key.length;
		var open = first;
		var collected = 0;
		while (open >= 0 && collected < options.depth) {
			var level = 1;
			var i = open + 1;
			var close = -1;
			while (i < n && close < 0) {
				var cu = key.charCodeAt(i);
				if (cu === 91) level += 1;
				else if (cu === 93) {
					level -= 1;
					if (level === 0) close = i;
				}
				i += 1;
			}
			if (close < 0) {
				segments[segments.length] = "[" + key.slice(open) + "]";
				return segments;
			}
			var seg = key.slice(open, close + 1);
			var content = seg.slice(1, -1);
			if (!options.plainObjects && has.call(Object.prototype, content) && !options.allowPrototypes) return;
			segments[segments.length] = seg;
			collected += 1;
			open = key.indexOf("[", close + 1);
		}
		if (open >= 0) {
			if (options.strictDepth === true) throw new RangeError("Input depth exceeded depth option of " + options.depth + " and strictDepth is true");
			segments[segments.length] = "[" + key.slice(open) + "]";
		}
		return segments;
	};
	var parseKeys = function parseQueryStringKeys(givenKey, val, options, valuesParsed) {
		if (!givenKey) return;
		var keys = splitKeyIntoSegments(givenKey, options);
		if (!keys) return;
		return parseObject(keys, val, options, valuesParsed);
	};
	var normalizeParseOptions = function normalizeParseOptions(opts) {
		if (!opts) return defaults;
		if (typeof opts.allowEmptyArrays !== "undefined" && typeof opts.allowEmptyArrays !== "boolean") throw new TypeError("`allowEmptyArrays` option can only be `true` or `false`, when provided");
		if (typeof opts.decodeDotInKeys !== "undefined" && typeof opts.decodeDotInKeys !== "boolean") throw new TypeError("`decodeDotInKeys` option can only be `true` or `false`, when provided");
		if (opts.decoder !== null && typeof opts.decoder !== "undefined" && typeof opts.decoder !== "function") throw new TypeError("Decoder has to be a function.");
		if (typeof opts.charset !== "undefined" && opts.charset !== "utf-8" && opts.charset !== "iso-8859-1") throw new TypeError("The charset option must be either utf-8, iso-8859-1, or undefined");
		if (typeof opts.throwOnLimitExceeded !== "undefined" && typeof opts.throwOnLimitExceeded !== "boolean") throw new TypeError("`throwOnLimitExceeded` option must be a boolean");
		var charset = typeof opts.charset === "undefined" ? defaults.charset : opts.charset;
		var duplicates = typeof opts.duplicates === "undefined" ? defaults.duplicates : opts.duplicates;
		if (duplicates !== "combine" && duplicates !== "first" && duplicates !== "last") throw new TypeError("The duplicates option must be either combine, first, or last");
		return {
			allowDots: typeof opts.allowDots === "undefined" ? opts.decodeDotInKeys === true ? true : defaults.allowDots : !!opts.allowDots,
			allowEmptyArrays: typeof opts.allowEmptyArrays === "boolean" ? !!opts.allowEmptyArrays : defaults.allowEmptyArrays,
			allowPrototypes: typeof opts.allowPrototypes === "boolean" ? opts.allowPrototypes : defaults.allowPrototypes,
			allowSparse: typeof opts.allowSparse === "boolean" ? opts.allowSparse : defaults.allowSparse,
			arrayLimit: typeof opts.arrayLimit === "number" ? opts.arrayLimit : defaults.arrayLimit,
			charset,
			charsetSentinel: typeof opts.charsetSentinel === "boolean" ? opts.charsetSentinel : defaults.charsetSentinel,
			comma: typeof opts.comma === "boolean" ? opts.comma : defaults.comma,
			decodeDotInKeys: typeof opts.decodeDotInKeys === "boolean" ? opts.decodeDotInKeys : defaults.decodeDotInKeys,
			decoder: typeof opts.decoder === "function" ? opts.decoder : defaults.decoder,
			delimiter: typeof opts.delimiter === "string" || utils.isRegExp(opts.delimiter) ? opts.delimiter : defaults.delimiter,
			depth: typeof opts.depth === "number" || opts.depth === false ? +opts.depth : defaults.depth,
			duplicates,
			ignoreQueryPrefix: opts.ignoreQueryPrefix === true,
			interpretNumericEntities: typeof opts.interpretNumericEntities === "boolean" ? opts.interpretNumericEntities : defaults.interpretNumericEntities,
			parameterLimit: typeof opts.parameterLimit === "number" ? opts.parameterLimit : defaults.parameterLimit,
			parseArrays: opts.parseArrays !== false,
			plainObjects: typeof opts.plainObjects === "boolean" ? opts.plainObjects : defaults.plainObjects,
			strictDepth: typeof opts.strictDepth === "boolean" ? !!opts.strictDepth : defaults.strictDepth,
			strictMerge: typeof opts.strictMerge === "boolean" ? !!opts.strictMerge : defaults.strictMerge,
			strictNullHandling: typeof opts.strictNullHandling === "boolean" ? opts.strictNullHandling : defaults.strictNullHandling,
			throwOnLimitExceeded: typeof opts.throwOnLimitExceeded === "boolean" ? opts.throwOnLimitExceeded : false
		};
	};
	module.exports = function(str, opts) {
		var options = normalizeParseOptions(opts);
		if (str === "" || str === null || typeof str === "undefined") return options.plainObjects ? { __proto__: null } : {};
		var tempObj = typeof str === "string" ? parseValues(str, options) : str;
		var obj = options.plainObjects ? { __proto__: null } : {};
		var keys = Object.keys(tempObj);
		for (var i = 0; i < keys.length; ++i) {
			var key = keys[i];
			var newObj = parseKeys(key, tempObj[key], options, typeof str === "string");
			obj = utils.merge(obj, newObj, options);
		}
		if (options.allowSparse === true) return obj;
		return utils.compact(obj);
	};
}));
//#endregion
//#region node_modules/qs/lib/index.js
var require_lib = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var stringify = require_stringify();
	var parse = require_parse();
	module.exports = {
		formats: require_formats(),
		parse,
		stringify
	};
}));
//#endregion
//#region node_modules/@inertiajs/core/dist/index.esm.js
var import_lib = /* @__PURE__ */ __toESM(require_lib(), 1);
var Config = class {
	constructor(defaults) {
		this.config = {};
		this.defaults = defaults;
	}
	extend(defaults) {
		if (defaults) this.defaults = {
			...this.defaults,
			...defaults
		};
		return this;
	}
	replace(newConfig) {
		this.config = newConfig;
	}
	get(key) {
		return has(this.config, key) ? get(this.config, key) : get(this.defaults, key);
	}
	set(keyOrValues, value) {
		if (typeof keyOrValues === "string") set(this.config, keyOrValues, value);
		else Object.entries(keyOrValues).forEach(([key, val]) => {
			set(this.config, key, val);
		});
	}
};
var config$1 = new Config({
	form: {
		recentlySuccessfulDuration: 2e3,
		forceIndicesArrayFormatInFormData: true,
		withAllErrors: false
	},
	future: {
		preserveEqualProps: false,
		useDataInertiaHeadAttribute: false,
		useDialogForErrorModal: false,
		useScriptElementForInitialPage: false
	},
	prefetch: {
		cacheFor: 3e4,
		hoverDelay: 75
	}
});
function debounce(fn, delay) {
	let timeoutID;
	return function(...args) {
		clearTimeout(timeoutID);
		timeoutID = setTimeout(() => fn.apply(this, args), delay);
	};
}
function fireEvent(name, options) {
	return document.dispatchEvent(new CustomEvent(`inertia:${name}`, options));
}
var fireBeforeEvent = (visit) => {
	return fireEvent("before", {
		cancelable: true,
		detail: { visit }
	});
};
var fireErrorEvent = (errors) => {
	return fireEvent("error", { detail: { errors } });
};
var fireExceptionEvent = (exception) => {
	return fireEvent("exception", {
		cancelable: true,
		detail: { exception }
	});
};
var fireFinishEvent = (visit) => {
	return fireEvent("finish", { detail: { visit } });
};
var fireInvalidEvent = (response) => {
	return fireEvent("invalid", {
		cancelable: true,
		detail: { response }
	});
};
var fireBeforeUpdateEvent = (page2) => {
	return fireEvent("beforeUpdate", { detail: { page: page2 } });
};
var fireNavigateEvent = (page2) => {
	return fireEvent("navigate", { detail: { page: page2 } });
};
var fireProgressEvent = (progress3) => {
	return fireEvent("progress", { detail: { progress: progress3 } });
};
var fireStartEvent = (visit) => {
	return fireEvent("start", { detail: { visit } });
};
var fireSuccessEvent = (page2) => {
	return fireEvent("success", { detail: { page: page2 } });
};
var firePrefetchedEvent = (response, visit) => {
	return fireEvent("prefetched", { detail: {
		fetchedAt: Date.now(),
		response: response.data,
		visit
	} });
};
var firePrefetchingEvent = (visit) => {
	return fireEvent("prefetching", { detail: { visit } });
};
var fireFlashEvent = (flash) => {
	return fireEvent("flash", { detail: { flash } });
};
var SessionStorage = class {
	static set(key, value) {
		if (typeof window !== "undefined") window.sessionStorage.setItem(key, JSON.stringify(value));
	}
	static get(key) {
		if (typeof window !== "undefined") return JSON.parse(window.sessionStorage.getItem(key) || "null");
	}
	static merge(key, value) {
		const existing = this.get(key);
		if (existing === null) this.set(key, value);
		else this.set(key, {
			...existing,
			...value
		});
	}
	static remove(key) {
		if (typeof window !== "undefined") window.sessionStorage.removeItem(key);
	}
	static removeNested(key, nestedKey) {
		const existing = this.get(key);
		if (existing !== null) {
			delete existing[nestedKey];
			this.set(key, existing);
		}
	}
	static exists(key) {
		try {
			return this.get(key) !== null;
		} catch (error) {
			return false;
		}
	}
	static clear() {
		if (typeof window !== "undefined") window.sessionStorage.clear();
	}
};
SessionStorage.locationVisitKey = "inertiaLocationVisit";
var encryptHistory = async (data) => {
	if (typeof window === "undefined") throw new Error("Unable to encrypt history");
	const iv = getIv();
	const key = await getOrCreateKey(await getKeyFromSessionStorage());
	if (!key) throw new Error("Unable to encrypt history");
	return await encryptData(iv, key, data);
};
var historySessionStorageKeys = {
	key: "historyKey",
	iv: "historyIv"
};
var decryptHistory = async (data) => {
	const iv = getIv();
	const storedKey = await getKeyFromSessionStorage();
	if (!storedKey) throw new Error("Unable to decrypt history");
	return await decryptData(iv, storedKey, data);
};
var encryptData = async (iv, key, data) => {
	if (typeof window === "undefined") throw new Error("Unable to encrypt history");
	if (typeof window.crypto.subtle === "undefined") {
		console.warn("Encryption is not supported in this environment. SSL is required.");
		return Promise.resolve(data);
	}
	const textEncoder = new TextEncoder();
	const str = JSON.stringify(data);
	const encoded = new Uint8Array(str.length * 3);
	const result = textEncoder.encodeInto(str, encoded);
	return window.crypto.subtle.encrypt({
		name: "AES-GCM",
		iv
	}, key, encoded.subarray(0, result.written));
};
var decryptData = async (iv, key, data) => {
	if (typeof window.crypto.subtle === "undefined") {
		console.warn("Decryption is not supported in this environment. SSL is required.");
		return Promise.resolve(data);
	}
	const decrypted = await window.crypto.subtle.decrypt({
		name: "AES-GCM",
		iv
	}, key, data);
	return JSON.parse(new TextDecoder().decode(decrypted));
};
var getIv = () => {
	const ivString = SessionStorage.get(historySessionStorageKeys.iv);
	if (ivString) return new Uint8Array(ivString);
	const iv = window.crypto.getRandomValues(new Uint8Array(12));
	SessionStorage.set(historySessionStorageKeys.iv, Array.from(iv));
	return iv;
};
var createKey = async () => {
	if (typeof window.crypto.subtle === "undefined") {
		console.warn("Encryption is not supported in this environment. SSL is required.");
		return Promise.resolve(null);
	}
	return window.crypto.subtle.generateKey({
		name: "AES-GCM",
		length: 256
	}, true, ["encrypt", "decrypt"]);
};
var saveKey = async (key) => {
	if (typeof window.crypto.subtle === "undefined") {
		console.warn("Encryption is not supported in this environment. SSL is required.");
		return Promise.resolve();
	}
	const keyData = await window.crypto.subtle.exportKey("raw", key);
	SessionStorage.set(historySessionStorageKeys.key, Array.from(new Uint8Array(keyData)));
};
var getOrCreateKey = async (key) => {
	if (key) return key;
	const newKey = await createKey();
	if (!newKey) return null;
	await saveKey(newKey);
	return newKey;
};
var getKeyFromSessionStorage = async () => {
	const stringKey = SessionStorage.get(historySessionStorageKeys.key);
	if (!stringKey) return null;
	return await window.crypto.subtle.importKey("raw", new Uint8Array(stringKey), {
		name: "AES-GCM",
		length: 256
	}, true, ["encrypt", "decrypt"]);
};
var objectsAreEqual = (obj1, obj2, excludeKeys) => {
	if (obj1 === obj2) return true;
	for (const key in obj1) {
		if (excludeKeys.includes(key)) continue;
		if (obj1[key] === obj2[key]) continue;
		if (!compareValues(obj1[key], obj2[key])) return false;
	}
	for (const key in obj2) {
		if (excludeKeys.includes(key)) continue;
		if (!(key in obj1)) return false;
	}
	return true;
};
var compareValues = (value1, value2) => {
	switch (typeof value1) {
		case "object": return objectsAreEqual(value1, value2, []);
		case "function": return value1.toString() === value2.toString();
		default: return value1 === value2;
	}
};
var conversionMap = {
	ms: 1,
	s: 1e3,
	m: 1e3 * 60,
	h: 1e3 * 60 * 60,
	d: 1e3 * 60 * 60 * 24
};
var timeToMs = (time) => {
	if (typeof time === "number") return time;
	for (const [unit, conversion] of Object.entries(conversionMap)) if (time.endsWith(unit)) return parseFloat(time) * conversion;
	return parseInt(time);
};
var PrefetchedRequests = class {
	constructor() {
		this.cached = [];
		this.inFlightRequests = [];
		this.removalTimers = [];
		this.currentUseId = null;
	}
	add(params, sendFunc, { cacheFor, cacheTags }) {
		if (this.findInFlight(params)) return Promise.resolve();
		const existing = this.findCached(params);
		if (!params.fresh && existing && existing.staleTimestamp > Date.now()) return Promise.resolve();
		const [stale, prefetchExpiresIn] = this.extractStaleValues(cacheFor);
		const promise = new Promise((resolve, reject) => {
			sendFunc({
				...params,
				onCancel: () => {
					this.remove(params);
					params.onCancel();
					reject();
				},
				onError: (error) => {
					this.remove(params);
					params.onError(error);
					reject();
				},
				onPrefetching(visitParams) {
					params.onPrefetching(visitParams);
				},
				onPrefetched(response, visit) {
					params.onPrefetched(response, visit);
				},
				onPrefetchResponse(response) {
					resolve(response);
				},
				onPrefetchError(error) {
					prefetchedRequests.removeFromInFlight(params);
					reject(error);
				}
			});
		}).then((response) => {
			this.remove(params);
			const pageResponse = response.getPageResponse();
			page$1.mergeOncePropsIntoResponse(pageResponse);
			this.cached.push({
				params: { ...params },
				staleTimestamp: Date.now() + stale,
				expiresAt: Date.now() + prefetchExpiresIn,
				response: promise,
				singleUse: prefetchExpiresIn === 0,
				timestamp: Date.now(),
				inFlight: false,
				tags: Array.isArray(cacheTags) ? cacheTags : [cacheTags]
			});
			const oncePropExpiresIn = this.getShortestOncePropTtl(pageResponse);
			this.scheduleForRemoval(params, oncePropExpiresIn ? Math.min(prefetchExpiresIn, oncePropExpiresIn) : prefetchExpiresIn);
			this.removeFromInFlight(params);
			response.handlePrefetch();
			return response;
		});
		this.inFlightRequests.push({
			params: { ...params },
			response: promise,
			staleTimestamp: null,
			inFlight: true
		});
		return promise;
	}
	removeAll() {
		this.cached = [];
		this.removalTimers.forEach((removalTimer) => {
			clearTimeout(removalTimer.timer);
		});
		this.removalTimers = [];
	}
	removeByTags(tags) {
		this.cached = this.cached.filter((prefetched) => {
			return !prefetched.tags.some((tag) => tags.includes(tag));
		});
	}
	remove(params) {
		this.cached = this.cached.filter((prefetched) => {
			return !this.paramsAreEqual(prefetched.params, params);
		});
		this.clearTimer(params);
	}
	removeFromInFlight(params) {
		this.inFlightRequests = this.inFlightRequests.filter((prefetching) => {
			return !this.paramsAreEqual(prefetching.params, params);
		});
	}
	extractStaleValues(cacheFor) {
		const [stale, expires] = this.cacheForToStaleAndExpires(cacheFor);
		return [timeToMs(stale), timeToMs(expires)];
	}
	cacheForToStaleAndExpires(cacheFor) {
		if (!Array.isArray(cacheFor)) return [cacheFor, cacheFor];
		switch (cacheFor.length) {
			case 0: return [0, 0];
			case 1: return [cacheFor[0], cacheFor[0]];
			default: return [cacheFor[0], cacheFor[1]];
		}
	}
	clearTimer(params) {
		const timer = this.removalTimers.find((removalTimer) => {
			return this.paramsAreEqual(removalTimer.params, params);
		});
		if (timer) {
			clearTimeout(timer.timer);
			this.removalTimers = this.removalTimers.filter((removalTimer) => removalTimer !== timer);
		}
	}
	scheduleForRemoval(params, expiresIn) {
		if (typeof window === "undefined") return;
		this.clearTimer(params);
		if (expiresIn > 0) {
			const timer = window.setTimeout(() => this.remove(params), expiresIn);
			this.removalTimers.push({
				params,
				timer
			});
		}
	}
	get(params) {
		return this.findCached(params) || this.findInFlight(params);
	}
	use(prefetched, params) {
		const id = `${params.url.pathname}-${Date.now()}-${Math.random().toString(36).substring(7)}`;
		this.currentUseId = id;
		return prefetched.response.then((response) => {
			if (this.currentUseId !== id) return;
			response.mergeParams({
				...params,
				onPrefetched: () => {}
			});
			this.removeSingleUseItems(params);
			return response.handle();
		});
	}
	removeSingleUseItems(params) {
		this.cached = this.cached.filter((prefetched) => {
			if (!this.paramsAreEqual(prefetched.params, params)) return true;
			return !prefetched.singleUse;
		});
	}
	findCached(params) {
		return this.cached.find((prefetched) => {
			return this.paramsAreEqual(prefetched.params, params);
		}) || null;
	}
	findInFlight(params) {
		return this.inFlightRequests.find((prefetched) => {
			return this.paramsAreEqual(prefetched.params, params);
		}) || null;
	}
	withoutPurposePrefetchHeader(params) {
		const newParams = cloneDeep(params);
		if (newParams.headers["Purpose"] === "prefetch") delete newParams.headers["Purpose"];
		return newParams;
	}
	paramsAreEqual(params1, params2) {
		return objectsAreEqual(this.withoutPurposePrefetchHeader(params1), this.withoutPurposePrefetchHeader(params2), [
			"showProgress",
			"replace",
			"prefetch",
			"preserveScroll",
			"preserveState",
			"onBefore",
			"onBeforeUpdate",
			"onStart",
			"onProgress",
			"onFinish",
			"onCancel",
			"onSuccess",
			"onError",
			"onFlash",
			"onPrefetched",
			"onCancelToken",
			"onPrefetching",
			"async",
			"viewTransition"
		]);
	}
	updateCachedOncePropsFromCurrentPage() {
		this.cached.forEach((prefetched) => {
			prefetched.response.then((response) => {
				const pageResponse = response.getPageResponse();
				page$1.mergeOncePropsIntoResponse(pageResponse, { force: true });
				for (const [group, deferredProps] of Object.entries(pageResponse.deferredProps ?? {})) {
					const remaining = deferredProps.filter((prop) => pageResponse.props[prop] === void 0);
					if (remaining.length > 0) pageResponse.deferredProps[group] = remaining;
					else delete pageResponse.deferredProps[group];
				}
				const oncePropExpiresIn = this.getShortestOncePropTtl(pageResponse);
				if (oncePropExpiresIn === null) return;
				const prefetchExpiresIn = prefetched.expiresAt - Date.now();
				const expiresIn = Math.min(prefetchExpiresIn, oncePropExpiresIn);
				if (expiresIn > 0) this.scheduleForRemoval(prefetched.params, expiresIn);
				else this.remove(prefetched.params);
			});
		});
	}
	getShortestOncePropTtl(page2) {
		const expiryTimestamps = Object.values(page2.onceProps ?? {}).map((onceProp) => onceProp.expiresAt).filter((expiresAt) => !!expiresAt);
		if (expiryTimestamps.length === 0) return null;
		return Math.min(...expiryTimestamps) - Date.now();
	}
};
var prefetchedRequests = new PrefetchedRequests();
var elementInViewport = (el) => {
	if (el.offsetParent === null) return false;
	const rect = el.getBoundingClientRect();
	const verticallyVisible = rect.top < window.innerHeight && rect.bottom >= 0;
	const horizontallyVisible = rect.left < window.innerWidth && rect.right >= 0;
	return verticallyVisible && horizontallyVisible;
};
var getScrollableParent = (element) => {
	const allowsVerticalScroll = (el) => {
		const computedStyle = window.getComputedStyle(el);
		if (["scroll", "overlay"].includes(computedStyle.overflowY)) return true;
		if (computedStyle.overflowY !== "auto") return false;
		if (["visible", "clip"].includes(computedStyle.overflowX)) return true;
		return hasDimensionConstraint(computedStyle.maxHeight, el.style.height) || isConstrainedByLayout(el, "height");
	};
	const allowsHorizontalScroll = (el) => {
		const computedStyle = window.getComputedStyle(el);
		if (["scroll", "overlay"].includes(computedStyle.overflowX)) return true;
		if (computedStyle.overflowX !== "auto") return false;
		if (["visible", "clip"].includes(computedStyle.overflowY)) return true;
		return hasDimensionConstraint(computedStyle.maxWidth, el.style.width) || isConstrainedByLayout(el, "width");
	};
	const hasDimensionConstraint = (computedMaxDimension, inlineStyleDimension) => {
		if (computedMaxDimension && computedMaxDimension !== "none" && computedMaxDimension !== "0px") return true;
		if (inlineStyleDimension && inlineStyleDimension !== "auto" && inlineStyleDimension !== "0") return true;
		return false;
	};
	const isConstrainedByLayout = (el, dimension) => {
		const parent2 = el.parentElement;
		if (!parent2) return false;
		const parentStyle = window.getComputedStyle(parent2);
		if (["flex", "inline-flex"].includes(parentStyle.display)) {
			const isColumnLayout = ["column", "column-reverse"].includes(parentStyle.flexDirection);
			return dimension === "height" ? isColumnLayout : !isColumnLayout;
		}
		return ["grid", "inline-grid"].includes(parentStyle.display);
	};
	let parent = element?.parentElement;
	while (parent) {
		const allowsScroll = allowsVerticalScroll(parent) || allowsHorizontalScroll(parent);
		if (window.getComputedStyle(parent).display !== "contents" && allowsScroll) return parent;
		parent = parent.parentElement;
	}
	return null;
};
var getElementsInViewportFromCollection = (elements, referenceElement) => {
	if (!referenceElement) return elements.filter((element) => elementInViewport(element));
	const referenceIndex = elements.indexOf(referenceElement);
	const upwardElements = [];
	const downwardElements = [];
	for (let i = referenceIndex; i >= 0; i--) {
		const element = elements[i];
		if (elementInViewport(element)) upwardElements.push(element);
		else break;
	}
	for (let i = referenceIndex + 1; i < elements.length; i++) {
		const element = elements[i];
		if (elementInViewport(element)) downwardElements.push(element);
		else break;
	}
	return [...upwardElements.reverse(), ...downwardElements];
};
var requestAnimationFrame = (cb, times = 1) => {
	window.requestAnimationFrame(() => {
		if (times > 1) requestAnimationFrame(cb, times - 1);
		else cb();
	});
};
var getInitialPageFromDOM = (id, useScriptElement = false) => {
	if (typeof window === "undefined") return null;
	if (!useScriptElement) {
		const el = document.getElementById(id);
		if (el?.dataset.page) return JSON.parse(el.dataset.page);
	}
	const scriptEl = document.querySelector(`script[data-page="${id}"][type="application/json"]`);
	if (scriptEl?.textContent) return JSON.parse(scriptEl.textContent);
	return null;
};
var isServer = typeof window === "undefined";
var isFirefox = !isServer && /Firefox/i.test(window.navigator.userAgent);
var Scroll = class {
	static save() {
		history.saveScrollPositions(this.getScrollRegions());
	}
	static getScrollRegions() {
		return Array.from(this.regions()).map((region) => ({
			top: region.scrollTop,
			left: region.scrollLeft
		}));
	}
	static regions() {
		return document.querySelectorAll("[scroll-region]");
	}
	static scrollToTop() {
		if (isFirefox && getComputedStyle(document.documentElement).scrollBehavior === "smooth") return requestAnimationFrame(() => window.scrollTo(0, 0), 2);
		window.scrollTo(0, 0);
	}
	static reset() {
		if (!(isServer ? null : window.location.hash)) this.scrollToTop();
		this.regions().forEach((region) => {
			if (typeof region.scrollTo === "function") region.scrollTo(0, 0);
			else {
				region.scrollTop = 0;
				region.scrollLeft = 0;
			}
		});
		this.save();
		this.scrollToAnchor();
	}
	static scrollToAnchor() {
		const anchorHash = isServer ? null : window.location.hash;
		if (anchorHash) setTimeout(() => {
			const anchorElement = document.getElementById(anchorHash.slice(1));
			anchorElement ? anchorElement.scrollIntoView() : this.scrollToTop();
		});
	}
	static restore(scrollRegions) {
		if (isServer) return;
		window.requestAnimationFrame(() => {
			this.restoreDocument();
			this.restoreScrollRegions(scrollRegions);
		});
	}
	static restoreScrollRegions(scrollRegions) {
		if (isServer) return;
		this.regions().forEach((region, index) => {
			const scrollPosition = scrollRegions[index];
			if (!scrollPosition) return;
			if (typeof region.scrollTo === "function") region.scrollTo(scrollPosition.left, scrollPosition.top);
			else {
				region.scrollTop = scrollPosition.top;
				region.scrollLeft = scrollPosition.left;
			}
		});
	}
	static restoreDocument() {
		const scrollPosition = history.getDocumentScrollPosition();
		window.scrollTo(scrollPosition.left, scrollPosition.top);
	}
	static onScroll(event) {
		const target = event.target;
		if (typeof target.hasAttribute === "function" && target.hasAttribute("scroll-region")) this.save();
	}
	static onWindowScroll() {
		history.saveDocumentScrollPosition({
			top: window.scrollY,
			left: window.scrollX
		});
	}
};
var isFile$1 = (value) => typeof File !== "undefined" && value instanceof File || value instanceof Blob || typeof FileList !== "undefined" && value instanceof FileList && value.length > 0;
function hasFiles$1(data) {
	return isFile$1(data) || data instanceof FormData && Array.from(data.values()).some((value) => hasFiles$1(value)) || typeof data === "object" && data !== null && Object.values(data).some((value) => hasFiles$1(value));
}
var isFormData = (value) => value instanceof FormData;
function objectToFormData(source, form = new FormData(), parentKey = null, queryStringArrayFormat = "brackets") {
	source = source || {};
	for (const key in source) if (Object.prototype.hasOwnProperty.call(source, key)) append(form, composeKey(parentKey, key, "indices"), source[key], queryStringArrayFormat);
	return form;
}
function composeKey(parent, key, format) {
	if (!parent) return key;
	return format === "brackets" ? `${parent}[]` : `${parent}[${key}]`;
}
function append(form, key, value, format) {
	if (Array.isArray(value)) return Array.from(value.keys()).forEach((index) => append(form, composeKey(key, index.toString(), format), value[index], format));
	else if (value instanceof Date) return form.append(key, value.toISOString());
	else if (value instanceof File) return form.append(key, value, value.name);
	else if (value instanceof Blob) return form.append(key, value);
	else if (typeof value === "boolean") return form.append(key, value ? "1" : "0");
	else if (typeof value === "string") return form.append(key, value);
	else if (typeof value === "number") return form.append(key, `${value}`);
	else if (value === null || value === void 0) return form.append(key, "");
	objectToFormData(value, form, key, format);
}
function hrefToUrl(href) {
	return new URL(href.toString(), typeof window === "undefined" ? void 0 : window.location.toString());
}
var transformUrlAndData = (href, data, method, forceFormData, queryStringArrayFormat) => {
	let url = typeof href === "string" ? hrefToUrl(href) : href;
	if ((hasFiles$1(data) || forceFormData) && !isFormData(data)) {
		if (config$1.get("form.forceIndicesArrayFormatInFormData")) queryStringArrayFormat = "indices";
		data = objectToFormData(data, new FormData(), null, queryStringArrayFormat);
	}
	if (isFormData(data)) return [url, data];
	const [_href, _data] = mergeDataIntoQueryString(method, url, data, queryStringArrayFormat);
	return [hrefToUrl(_href), _data];
};
function mergeDataIntoQueryString(method, href, data, qsArrayFormat = "brackets") {
	const hasDataForQueryString = method === "get" && !isFormData(data) && Object.keys(data).length > 0;
	const hasHost = urlHasProtocol(href.toString());
	const hasAbsolutePath = hasHost || href.toString().startsWith("/") || href.toString() === "";
	const hasRelativePath = !hasAbsolutePath && !href.toString().startsWith("#") && !href.toString().startsWith("?");
	const hasRelativePathWithDotPrefix = /^[.]{1,2}([/]|$)/.test(href.toString());
	const hasSearch = href.toString().includes("?") || hasDataForQueryString;
	const hasHash = href.toString().includes("#");
	const url = new URL(href.toString(), typeof window === "undefined" ? "http://localhost" : window.location.toString());
	if (hasDataForQueryString) {
		const hasIndices = /\[\d+\]/.test(decodeURIComponent(url.search));
		url.search = import_lib.stringify({
			...import_lib.parse(url.search, {
				ignoreQueryPrefix: true,
				allowSparse: true
			}),
			...data
		}, {
			encodeValuesOnly: true,
			arrayFormat: hasIndices ? "indices" : qsArrayFormat
		});
	}
	return [[
		hasHost ? `${url.protocol}//${url.host}` : "",
		hasAbsolutePath ? url.pathname : "",
		hasRelativePath ? url.pathname.substring(hasRelativePathWithDotPrefix ? 0 : 1) : "",
		hasSearch ? url.search : "",
		hasHash ? url.hash : ""
	].join(""), hasDataForQueryString ? {} : data];
}
function urlWithoutHash(url) {
	url = new URL(url.href);
	url.hash = "";
	return url;
}
var setHashIfSameUrl = (originUrl, destinationUrl) => {
	if (originUrl.hash && !destinationUrl.hash && urlWithoutHash(originUrl).href === destinationUrl.href) destinationUrl.hash = originUrl.hash;
};
var isSameUrlWithoutHash = (url1, url2) => {
	return urlWithoutHash(url1).href === urlWithoutHash(url2).href;
};
var isSameUrlWithoutQueryOrHash = (url1, url2) => {
	return url1.origin === url2.origin && url1.pathname === url2.pathname;
};
function isUrlMethodPair(href) {
	return href !== null && typeof href === "object" && href !== void 0 && "url" in href && "method" in href;
}
function urlHasProtocol(url) {
	return /^([a-z][a-z0-9+.-]*:)?\/\/[^/]/i.test(url);
}
function urlToString(url, absolute) {
	const urlObj = typeof url === "string" ? hrefToUrl(url) : url;
	return absolute ? `${urlObj.protocol}//${urlObj.host}${urlObj.pathname}${urlObj.search}${urlObj.hash}` : `${urlObj.pathname}${urlObj.search}${urlObj.hash}`;
}
var CurrentPage = class {
	constructor() {
		this.componentId = {};
		this.listeners = [];
		this.isFirstPageLoad = true;
		this.cleared = false;
		this.pendingDeferredProps = null;
		this.historyQuotaExceeded = false;
	}
	init({ initialPage, swapComponent, resolveComponent, onFlash }) {
		this.page = {
			...initialPage,
			flash: initialPage.flash ?? {}
		};
		this.swapComponent = swapComponent;
		this.resolveComponent = resolveComponent;
		this.onFlashCallback = onFlash;
		eventHandler.on("historyQuotaExceeded", () => {
			this.historyQuotaExceeded = true;
		});
		return this;
	}
	set(page2, { replace = false, preserveScroll = false, preserveState = false, viewTransition = false } = {}) {
		if (Object.keys(page2.deferredProps || {}).length) {
			this.pendingDeferredProps = {
				deferredProps: page2.deferredProps,
				component: page2.component,
				url: page2.url
			};
			if (page2.initialDeferredProps === void 0) page2.initialDeferredProps = page2.deferredProps;
		}
		this.componentId = {};
		const componentId = this.componentId;
		if (page2.clearHistory) history.clear();
		return this.resolve(page2.component).then((component) => {
			if (componentId !== this.componentId) return;
			page2.rememberedState ?? (page2.rememberedState = {});
			const isServer3 = typeof window === "undefined";
			const location = !isServer3 ? window.location : new URL(page2.url);
			const scrollRegions = !isServer3 && preserveScroll ? Scroll.getScrollRegions() : [];
			replace = replace || isSameUrlWithoutHash(hrefToUrl(page2.url), location);
			const pageForHistory = {
				...page2,
				flash: {}
			};
			return new Promise((resolve) => replace ? history.replaceState(pageForHistory, resolve) : history.pushState(pageForHistory, resolve)).then(() => {
				const isNewComponent = !this.isTheSame(page2);
				if (!isNewComponent && Object.keys(page2.props.errors || {}).length > 0) viewTransition = false;
				this.page = page2;
				this.cleared = false;
				if (this.hasOnceProps()) prefetchedRequests.updateCachedOncePropsFromCurrentPage();
				if (isNewComponent) this.fireEventsFor("newComponent");
				if (this.isFirstPageLoad) this.fireEventsFor("firstLoad");
				this.isFirstPageLoad = false;
				if (this.historyQuotaExceeded) {
					this.historyQuotaExceeded = false;
					return;
				}
				return this.swap({
					component,
					page: page2,
					preserveState,
					viewTransition
				}).then(() => {
					if (preserveScroll) window.requestAnimationFrame(() => Scroll.restoreScrollRegions(scrollRegions));
					else Scroll.reset();
					if (this.pendingDeferredProps && this.pendingDeferredProps.component === page2.component && this.pendingDeferredProps.url === page2.url) eventHandler.fireInternalEvent("loadDeferredProps", this.pendingDeferredProps.deferredProps);
					this.pendingDeferredProps = null;
					if (!replace) fireNavigateEvent(page2);
				});
			});
		});
	}
	setQuietly(page2, { preserveState = false } = {}) {
		return this.resolve(page2.component).then((component) => {
			this.page = page2;
			this.cleared = false;
			history.setCurrent(page2);
			return this.swap({
				component,
				page: page2,
				preserveState,
				viewTransition: false
			});
		});
	}
	clear() {
		this.cleared = true;
	}
	isCleared() {
		return this.cleared;
	}
	get() {
		return this.page;
	}
	getWithoutFlashData() {
		return {
			...this.page,
			flash: {}
		};
	}
	hasOnceProps() {
		return Object.keys(this.page.onceProps ?? {}).length > 0;
	}
	merge(data) {
		this.page = {
			...this.page,
			...data
		};
	}
	setFlash(flash) {
		this.page = {
			...this.page,
			flash
		};
		this.onFlashCallback?.(flash);
	}
	setUrlHash(hash) {
		if (!this.page.url.includes(hash)) this.page.url += hash;
	}
	remember(data) {
		this.page.rememberedState = data;
	}
	swap({ component, page: page2, preserveState, viewTransition }) {
		const doSwap = () => this.swapComponent({
			component,
			page: page2,
			preserveState
		});
		if (!viewTransition || !document?.startViewTransition || document.visibilityState === "hidden") return doSwap();
		const viewTransitionCallback = typeof viewTransition === "boolean" ? () => null : viewTransition;
		return new Promise((resolve) => {
			viewTransitionCallback(document.startViewTransition(() => doSwap().then(resolve)));
		});
	}
	resolve(component) {
		return Promise.resolve(this.resolveComponent(component));
	}
	isTheSame(page2) {
		return this.page.component === page2.component;
	}
	on(event, callback) {
		this.listeners.push({
			event,
			callback
		});
		return () => {
			this.listeners = this.listeners.filter((listener) => listener.event !== event && listener.callback !== callback);
		};
	}
	fireEventsFor(event) {
		this.listeners.filter((listener) => listener.event === event).forEach((listener) => listener.callback());
	}
	mergeOncePropsIntoResponse(response, { force = false } = {}) {
		Object.entries(response.onceProps ?? {}).forEach(([key, onceProp]) => {
			const existingOnceProp = this.page.onceProps?.[key];
			if (existingOnceProp === void 0) return;
			if (force || response.props[onceProp.prop] === void 0) {
				response.props[onceProp.prop] = this.page.props[existingOnceProp.prop];
				response.onceProps[key].expiresAt = existingOnceProp.expiresAt;
			}
		});
	}
};
var page$1 = new CurrentPage();
var Queue = class {
	constructor() {
		this.items = [];
		this.processingPromise = null;
	}
	add(item) {
		this.items.push(item);
		return this.process();
	}
	process() {
		this.processingPromise ?? (this.processingPromise = this.processNext().finally(() => {
			this.processingPromise = null;
		}));
		return this.processingPromise;
	}
	processNext() {
		const next = this.items.shift();
		if (next) return Promise.resolve(next()).then(() => this.processNext());
		return Promise.resolve();
	}
};
var isServer2 = typeof window === "undefined";
var queue = new Queue();
var isChromeIOS = !isServer2 && /CriOS/.test(window.navigator.userAgent);
var History = class {
	constructor() {
		this.rememberedState = "rememberedState";
		this.scrollRegions = "scrollRegions";
		this.preserveUrl = false;
		this.current = {};
		this.initialState = null;
	}
	remember(data, key) {
		this.replaceState({
			...page$1.getWithoutFlashData(),
			rememberedState: {
				...page$1.get()?.rememberedState ?? {},
				[key]: data
			}
		});
	}
	restore(key) {
		if (!isServer2) return this.current[this.rememberedState]?.[key] !== void 0 ? this.current[this.rememberedState]?.[key] : this.initialState?.[this.rememberedState]?.[key];
	}
	pushState(page2, cb = null) {
		if (isServer2) return;
		if (this.preserveUrl) {
			cb && cb();
			return;
		}
		this.current = page2;
		queue.add(() => {
			return this.getPageData(page2).then((data) => {
				const doPush = () => this.doPushState({ page: data }, page2.url).then(() => cb?.());
				if (isChromeIOS) return new Promise((resolve) => {
					setTimeout(() => doPush().then(resolve));
				});
				return doPush();
			});
		});
	}
	clonePageProps(page2) {
		try {
			structuredClone(page2.props);
			return page2;
		} catch {
			return {
				...page2,
				props: cloneDeep(page2.props)
			};
		}
	}
	getPageData(page2) {
		const pageWithClonedProps = this.clonePageProps(page2);
		return new Promise((resolve) => {
			return page2.encryptHistory ? encryptHistory(pageWithClonedProps).then(resolve) : resolve(pageWithClonedProps);
		});
	}
	processQueue() {
		return queue.process();
	}
	decrypt(page2 = null) {
		if (isServer2) return Promise.resolve(page2 ?? page$1.get());
		const pageData = page2 ?? window.history.state?.page;
		return this.decryptPageData(pageData).then((data) => {
			if (!data) throw new Error("Unable to decrypt history");
			if (this.initialState === null) this.initialState = data ?? void 0;
			else this.current = data ?? {};
			return data;
		});
	}
	decryptPageData(pageData) {
		return pageData instanceof ArrayBuffer ? decryptHistory(pageData) : Promise.resolve(pageData);
	}
	saveScrollPositions(scrollRegions) {
		queue.add(() => {
			return Promise.resolve().then(() => {
				if (!window.history.state?.page) return;
				if (isEqual(this.getScrollRegions(), scrollRegions)) return;
				return this.doReplaceState({
					page: window.history.state.page,
					scrollRegions
				});
			});
		});
	}
	saveDocumentScrollPosition(scrollRegion) {
		queue.add(() => {
			return Promise.resolve().then(() => {
				if (!window.history.state?.page) return;
				if (isEqual(this.getDocumentScrollPosition(), scrollRegion)) return;
				return this.doReplaceState({
					page: window.history.state.page,
					documentScrollPosition: scrollRegion
				});
			});
		});
	}
	getScrollRegions() {
		return window.history.state?.scrollRegions || [];
	}
	getDocumentScrollPosition() {
		return window.history.state?.documentScrollPosition || {
			top: 0,
			left: 0
		};
	}
	replaceState(page2, cb = null) {
		if (isEqual(this.current, page2)) {
			cb && cb();
			return;
		}
		const { flash, ...pageWithoutFlash } = page2;
		page$1.merge(pageWithoutFlash);
		if (isServer2) return;
		if (this.preserveUrl) {
			cb && cb();
			return;
		}
		this.current = page2;
		queue.add(() => {
			return this.getPageData(page2).then((data) => {
				const doReplace = () => this.doReplaceState({ page: data }, page2.url).then(() => cb?.());
				if (isChromeIOS) return new Promise((resolve) => {
					setTimeout(() => doReplace().then(resolve));
				});
				return doReplace();
			});
		});
	}
	isHistoryThrottleError(error) {
		return error instanceof Error && error.name === "SecurityError" && (error.message.includes("history.pushState") || error.message.includes("history.replaceState"));
	}
	isQuotaExceededError(error) {
		return error instanceof Error && error.name === "QuotaExceededError";
	}
	withThrottleProtection(cb) {
		return Promise.resolve().then(() => {
			try {
				return cb();
			} catch (error) {
				if (!this.isHistoryThrottleError(error)) throw error;
				console.error(error.message);
			}
		});
	}
	doReplaceState(data, url) {
		return this.withThrottleProtection(() => {
			window.history.replaceState({
				...data,
				scrollRegions: data.scrollRegions ?? window.history.state?.scrollRegions,
				documentScrollPosition: data.documentScrollPosition ?? window.history.state?.documentScrollPosition
			}, "", url);
		});
	}
	doPushState(data, url) {
		return this.withThrottleProtection(() => {
			try {
				window.history.pushState(data, "", url);
			} catch (error) {
				if (!this.isQuotaExceededError(error)) throw error;
				eventHandler.fireInternalEvent("historyQuotaExceeded", url);
			}
		});
	}
	getState(key, defaultValue) {
		return this.current?.[key] ?? defaultValue;
	}
	deleteState(key) {
		if (this.current[key] !== void 0) {
			delete this.current[key];
			this.replaceState(this.current);
		}
	}
	clearInitialState(key) {
		if (this.initialState && this.initialState[key] !== void 0) delete this.initialState[key];
	}
	browserHasHistoryEntry() {
		return !isServer2 && !!window.history.state?.page;
	}
	clear() {
		SessionStorage.remove(historySessionStorageKeys.key);
		SessionStorage.remove(historySessionStorageKeys.iv);
	}
	setCurrent(page2) {
		this.current = page2;
	}
	isValidState(state) {
		return !!state.page;
	}
	getAllState() {
		return this.current;
	}
};
if (typeof window !== "undefined" && window.history.scrollRestoration) window.history.scrollRestoration = "manual";
var history = new History();
var EventHandler = class {
	constructor() {
		this.internalListeners = [];
	}
	init() {
		if (typeof window !== "undefined") {
			window.addEventListener("popstate", this.handlePopstateEvent.bind(this));
			window.addEventListener("pageshow", this.handlePageshowEvent.bind(this));
			window.addEventListener("scroll", debounce(Scroll.onWindowScroll.bind(Scroll), 100), true);
		}
		if (typeof document !== "undefined") document.addEventListener("scroll", debounce(Scroll.onScroll.bind(Scroll), 100), true);
	}
	onGlobalEvent(type, callback) {
		const listener = ((event) => {
			const response = callback(event);
			if (event.cancelable && !event.defaultPrevented && response === false) event.preventDefault();
		});
		return this.registerListener(`inertia:${type}`, listener);
	}
	on(event, callback) {
		this.internalListeners.push({
			event,
			listener: callback
		});
		return () => {
			this.internalListeners = this.internalListeners.filter((listener) => listener.listener !== callback);
		};
	}
	onMissingHistoryItem() {
		page$1.clear();
		this.fireInternalEvent("missingHistoryItem");
	}
	fireInternalEvent(event, ...args) {
		this.internalListeners.filter((listener) => listener.event === event).forEach((listener) => listener.listener(...args));
	}
	registerListener(type, listener) {
		document.addEventListener(type, listener);
		return () => document.removeEventListener(type, listener);
	}
	handlePageshowEvent(event) {
		if (event.persisted) history.decrypt().catch(() => this.onMissingHistoryItem());
	}
	handlePopstateEvent(event) {
		const state = event.state || null;
		if (state === null) {
			const url = hrefToUrl(page$1.get().url);
			url.hash = window.location.hash;
			history.replaceState({
				...page$1.getWithoutFlashData(),
				url: url.href
			});
			Scroll.reset();
			return;
		}
		if (!history.isValidState(state)) return this.onMissingHistoryItem();
		history.decrypt(state.page).then((data) => {
			if (page$1.get().version !== data.version) {
				this.onMissingHistoryItem();
				return;
			}
			router.cancelAll({ prefetch: false });
			page$1.setQuietly(data, { preserveState: false }).then(() => {
				Scroll.restore(history.getScrollRegions());
				fireNavigateEvent(page$1.get());
				const pendingDeferred = {};
				const pageProps = page$1.get().props;
				for (const [group, props] of Object.entries(data.initialDeferredProps ?? data.deferredProps ?? {})) {
					const missing = props.filter((prop) => pageProps[prop] === void 0);
					if (missing.length > 0) pendingDeferred[group] = missing;
				}
				if (Object.keys(pendingDeferred).length > 0) this.fireInternalEvent("loadDeferredProps", pendingDeferred);
			});
		}).catch(() => {
			this.onMissingHistoryItem();
		});
	}
};
var eventHandler = new EventHandler();
var NavigationType = class {
	constructor() {
		this.type = this.resolveType();
	}
	resolveType() {
		if (typeof window === "undefined") return "navigate";
		if (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation").length > 0) return window.performance.getEntriesByType("navigation")[0].type;
		return "navigate";
	}
	get() {
		return this.type;
	}
	isBackForward() {
		return this.type === "back_forward";
	}
	isReload() {
		return this.type === "reload";
	}
};
var navigationType = new NavigationType();
var InitialVisit = class {
	static handle() {
		this.clearRememberedStateOnReload();
		[
			this.handleBackForward,
			this.handleLocation,
			this.handleDefault
		].find((handler) => handler.bind(this)());
	}
	static clearRememberedStateOnReload() {
		if (navigationType.isReload()) {
			history.deleteState(history.rememberedState);
			history.clearInitialState(history.rememberedState);
		}
	}
	static handleBackForward() {
		if (!navigationType.isBackForward() || !history.browserHasHistoryEntry()) return false;
		const scrollRegions = history.getScrollRegions();
		history.decrypt().then((data) => {
			page$1.set(data, {
				preserveScroll: true,
				preserveState: true
			}).then(() => {
				Scroll.restore(scrollRegions);
				fireNavigateEvent(page$1.get());
			});
		}).catch(() => {
			eventHandler.onMissingHistoryItem();
		});
		return true;
	}
	/**
	* @link https://inertiajs.com/redirects#external-redirects
	*/
	static handleLocation() {
		if (!SessionStorage.exists(SessionStorage.locationVisitKey)) return false;
		const locationVisit = SessionStorage.get(SessionStorage.locationVisitKey) || {};
		SessionStorage.remove(SessionStorage.locationVisitKey);
		if (typeof window !== "undefined") page$1.setUrlHash(window.location.hash);
		history.decrypt(page$1.get()).then(() => {
			const rememberedState = history.getState(history.rememberedState, {});
			const scrollRegions = history.getScrollRegions();
			page$1.remember(rememberedState);
			page$1.set(page$1.get(), {
				preserveScroll: locationVisit.preserveScroll,
				preserveState: true
			}).then(() => {
				if (locationVisit.preserveScroll) Scroll.restore(scrollRegions);
				fireNavigateEvent(page$1.get());
			});
		}).catch(() => {
			eventHandler.onMissingHistoryItem();
		});
		return true;
	}
	static handleDefault() {
		if (typeof window !== "undefined") page$1.setUrlHash(window.location.hash);
		page$1.set(page$1.get(), {
			preserveScroll: true,
			preserveState: true
		}).then(() => {
			if (navigationType.isReload()) Scroll.restore(history.getScrollRegions());
			else Scroll.scrollToAnchor();
			const page2 = page$1.get();
			fireNavigateEvent(page2);
			const flash = page2.flash;
			if (Object.keys(flash).length > 0) queueMicrotask(() => fireFlashEvent(flash));
		});
	}
};
var Poll = class {
	constructor(interval, cb, options) {
		this.id = null;
		this.throttle = false;
		this.keepAlive = false;
		this.cbCount = 0;
		this.keepAlive = options.keepAlive ?? false;
		this.cb = cb;
		this.interval = interval;
		if (options.autoStart ?? true) this.start();
	}
	stop() {
		if (this.id) clearInterval(this.id);
	}
	start() {
		if (typeof window === "undefined") return;
		this.stop();
		this.id = window.setInterval(() => {
			if (!this.throttle || this.cbCount % 10 === 0) this.cb();
			if (this.throttle) this.cbCount++;
		}, this.interval);
	}
	isInBackground(hidden) {
		this.throttle = this.keepAlive ? false : hidden;
		if (this.throttle) this.cbCount = 0;
	}
};
var Polls = class {
	constructor() {
		this.polls = [];
		this.setupVisibilityListener();
	}
	add(interval, cb, options) {
		const poll = new Poll(interval, cb, options);
		this.polls.push(poll);
		return {
			stop: () => poll.stop(),
			start: () => poll.start()
		};
	}
	clear() {
		this.polls.forEach((poll) => poll.stop());
		this.polls = [];
	}
	setupVisibilityListener() {
		if (typeof document === "undefined") return;
		document.addEventListener("visibilitychange", () => {
			this.polls.forEach((poll) => poll.isInBackground(document.hidden));
		}, false);
	}
};
var polls = new Polls();
var RequestParams = class _RequestParams {
	constructor(params) {
		this.callbacks = [];
		if (!params.prefetch) this.params = params;
		else {
			const wrappedCallbacks = {
				onBefore: this.wrapCallback(params, "onBefore"),
				onBeforeUpdate: this.wrapCallback(params, "onBeforeUpdate"),
				onStart: this.wrapCallback(params, "onStart"),
				onProgress: this.wrapCallback(params, "onProgress"),
				onFinish: this.wrapCallback(params, "onFinish"),
				onCancel: this.wrapCallback(params, "onCancel"),
				onSuccess: this.wrapCallback(params, "onSuccess"),
				onError: this.wrapCallback(params, "onError"),
				onFlash: this.wrapCallback(params, "onFlash"),
				onCancelToken: this.wrapCallback(params, "onCancelToken"),
				onPrefetched: this.wrapCallback(params, "onPrefetched"),
				onPrefetching: this.wrapCallback(params, "onPrefetching")
			};
			this.params = {
				...params,
				...wrappedCallbacks,
				onPrefetchResponse: params.onPrefetchResponse || (() => {}),
				onPrefetchError: params.onPrefetchError || (() => {})
			};
		}
	}
	static create(params) {
		return new _RequestParams(params);
	}
	data() {
		return this.params.method === "get" ? null : this.params.data;
	}
	queryParams() {
		return this.params.method === "get" ? this.params.data : {};
	}
	isPartial() {
		return this.params.only.length > 0 || this.params.except.length > 0 || this.params.reset.length > 0;
	}
	isPrefetch() {
		return this.params.prefetch === true;
	}
	isDeferredPropsRequest() {
		return this.params.deferredProps === true;
	}
	onCancelToken(cb) {
		this.params.onCancelToken({ cancel: cb });
	}
	markAsFinished() {
		this.params.completed = true;
		this.params.cancelled = false;
		this.params.interrupted = false;
	}
	markAsCancelled({ cancelled = true, interrupted = false }) {
		this.params.onCancel();
		this.params.completed = false;
		this.params.cancelled = cancelled;
		this.params.interrupted = interrupted;
	}
	wasCancelledAtAll() {
		return this.params.cancelled || this.params.interrupted;
	}
	onFinish() {
		this.params.onFinish(this.params);
	}
	onStart() {
		this.params.onStart(this.params);
	}
	onPrefetching() {
		this.params.onPrefetching(this.params);
	}
	onPrefetchResponse(response) {
		if (this.params.onPrefetchResponse) this.params.onPrefetchResponse(response);
	}
	onPrefetchError(error) {
		if (this.params.onPrefetchError) this.params.onPrefetchError(error);
	}
	all() {
		return this.params;
	}
	headers() {
		const headers = { ...this.params.headers };
		if (this.isPartial()) headers["X-Inertia-Partial-Component"] = page$1.get().component;
		const only = this.params.only.concat(this.params.reset);
		if (only.length > 0) headers["X-Inertia-Partial-Data"] = only.join(",");
		if (this.params.except.length > 0) headers["X-Inertia-Partial-Except"] = this.params.except.join(",");
		if (this.params.reset.length > 0) headers["X-Inertia-Reset"] = this.params.reset.join(",");
		if (this.params.errorBag && this.params.errorBag.length > 0) headers["X-Inertia-Error-Bag"] = this.params.errorBag;
		return headers;
	}
	setPreserveOptions(page2) {
		this.params.preserveScroll = _RequestParams.resolvePreserveOption(this.params.preserveScroll, page2);
		this.params.preserveState = _RequestParams.resolvePreserveOption(this.params.preserveState, page2);
	}
	runCallbacks() {
		this.callbacks.forEach(({ name, args }) => {
			this.params[name](...args);
		});
	}
	merge(toMerge) {
		this.params = {
			...this.params,
			...toMerge
		};
	}
	wrapCallback(params, name) {
		return (...args) => {
			this.recordCallback(name, args);
			params[name](...args);
		};
	}
	recordCallback(name, args) {
		this.callbacks.push({
			name,
			args
		});
	}
	static resolvePreserveOption(value, page2) {
		if (typeof value === "function") return value(page2);
		if (value === "errors") return Object.keys(page2.props.errors || {}).length > 0;
		return value;
	}
};
var modal_default = {
	modal: null,
	listener: null,
	createIframeAndPage(html) {
		if (typeof html === "object") html = `All Inertia requests must receive a valid Inertia response, however a plain JSON response was received.<hr>${JSON.stringify(html)}`;
		const page2 = document.createElement("html");
		page2.innerHTML = html;
		page2.querySelectorAll("a").forEach((a) => a.setAttribute("target", "_top"));
		const iframe = document.createElement("iframe");
		iframe.style.backgroundColor = "white";
		iframe.style.borderRadius = "5px";
		iframe.style.width = "100%";
		iframe.style.height = "100%";
		return {
			iframe,
			page: page2
		};
	},
	show(html) {
		const { iframe, page: page2 } = this.createIframeAndPage(html);
		this.modal = document.createElement("div");
		this.modal.style.position = "fixed";
		this.modal.style.width = "100vw";
		this.modal.style.height = "100vh";
		this.modal.style.padding = "50px";
		this.modal.style.boxSizing = "border-box";
		this.modal.style.backgroundColor = "rgba(0, 0, 0, .6)";
		this.modal.style.zIndex = 2e5;
		this.modal.addEventListener("click", () => this.hide());
		this.modal.appendChild(iframe);
		document.body.prepend(this.modal);
		document.body.style.overflow = "hidden";
		if (!iframe.contentWindow) throw new Error("iframe not yet ready.");
		iframe.contentWindow.document.open();
		iframe.contentWindow.document.write(page2.outerHTML);
		iframe.contentWindow.document.close();
		this.listener = this.hideOnEscape.bind(this);
		document.addEventListener("keydown", this.listener);
	},
	hide() {
		this.modal.outerHTML = "";
		this.modal = null;
		document.body.style.overflow = "visible";
		document.removeEventListener("keydown", this.listener);
	},
	hideOnEscape(event) {
		if (event.keyCode === 27) this.hide();
	}
};
var dialog_default = { show(html) {
	const { iframe, page: page2 } = modal_default.createIframeAndPage(html);
	iframe.style.boxSizing = "border-box";
	iframe.style.display = "block";
	const dialog = document.createElement("dialog");
	dialog.id = "inertia-error-dialog";
	Object.assign(dialog.style, {
		width: "calc(100vw - 100px)",
		height: "calc(100vh - 100px)",
		padding: "0",
		margin: "auto",
		border: "none",
		backgroundColor: "transparent"
	});
	const dialogStyleElement = document.createElement("style");
	dialogStyleElement.textContent = `
      dialog#inertia-error-dialog::backdrop {
        background-color: rgba(0, 0, 0, 0.6);
      }

      dialog#inertia-error-dialog:focus {
        outline: none;
      }
    `;
	document.head.appendChild(dialogStyleElement);
	dialog.addEventListener("click", (event) => {
		if (event.target === dialog) dialog.close();
	});
	dialog.addEventListener("close", () => {
		dialogStyleElement.remove();
		dialog.remove();
	});
	dialog.appendChild(iframe);
	document.body.prepend(dialog);
	dialog.showModal();
	dialog.focus();
	if (!iframe.contentWindow) throw new Error("iframe not yet ready.");
	iframe.contentWindow.document.open();
	iframe.contentWindow.document.write(page2.outerHTML);
	iframe.contentWindow.document.close();
} };
var queue2 = new Queue();
var Response = class _Response {
	constructor(requestParams, response, originatingPage) {
		this.requestParams = requestParams;
		this.response = response;
		this.originatingPage = originatingPage;
		this.wasPrefetched = false;
	}
	static create(params, response, originatingPage) {
		return new _Response(params, response, originatingPage);
	}
	async handlePrefetch() {
		if (isSameUrlWithoutHash(this.requestParams.all().url, window.location)) this.handle();
	}
	async handle() {
		return queue2.add(() => this.process());
	}
	async process() {
		if (this.requestParams.all().prefetch) {
			this.wasPrefetched = true;
			this.requestParams.all().prefetch = false;
			this.requestParams.all().onPrefetched(this.response, this.requestParams.all());
			firePrefetchedEvent(this.response, this.requestParams.all());
			return Promise.resolve();
		}
		this.requestParams.runCallbacks();
		if (!this.isInertiaResponse()) return this.handleNonInertiaResponse();
		await history.processQueue();
		history.preserveUrl = this.requestParams.all().preserveUrl;
		await this.setPage();
		const errors = page$1.get().props.errors || {};
		if (Object.keys(errors).length > 0) {
			const scopedErrors = this.getScopedErrors(errors);
			fireErrorEvent(scopedErrors);
			return this.requestParams.all().onError(scopedErrors);
		}
		router.flushByCacheTags(this.requestParams.all().invalidateCacheTags || []);
		if (!this.wasPrefetched) router.flush(page$1.get().url);
		const { flash } = page$1.get();
		if (Object.keys(flash).length > 0 && !this.requestParams.isDeferredPropsRequest()) {
			fireFlashEvent(flash);
			this.requestParams.all().onFlash(flash);
		}
		fireSuccessEvent(page$1.get());
		await this.requestParams.all().onSuccess(page$1.get());
		history.preserveUrl = false;
	}
	mergeParams(params) {
		this.requestParams.merge(params);
	}
	getPageResponse() {
		const data = this.getDataFromResponse(this.response.data);
		if (typeof data === "object") return this.response.data = {
			...data,
			flash: data.flash ?? {}
		};
		return this.response.data = data;
	}
	async handleNonInertiaResponse() {
		if (this.isLocationVisit()) {
			const locationUrl = hrefToUrl(this.getHeader("x-inertia-location"));
			setHashIfSameUrl(this.requestParams.all().url, locationUrl);
			return this.locationVisit(locationUrl);
		}
		const response = {
			...this.response,
			data: this.getDataFromResponse(this.response.data)
		};
		if (fireInvalidEvent(response)) return config$1.get("future.useDialogForErrorModal") ? dialog_default.show(response.data) : modal_default.show(response.data);
	}
	isInertiaResponse() {
		return this.hasHeader("x-inertia");
	}
	hasStatus(status2) {
		return this.response.status === status2;
	}
	getHeader(header) {
		return this.response.headers[header];
	}
	hasHeader(header) {
		return this.getHeader(header) !== void 0;
	}
	isLocationVisit() {
		return this.hasStatus(409) && this.hasHeader("x-inertia-location");
	}
	/**
	* @link https://inertiajs.com/redirects#external-redirects
	*/
	locationVisit(url) {
		try {
			SessionStorage.set(SessionStorage.locationVisitKey, { preserveScroll: this.requestParams.all().preserveScroll === true });
			if (typeof window === "undefined") return;
			if (isSameUrlWithoutHash(window.location, url)) window.location.reload();
			else window.location.href = url.href;
		} catch (error) {
			return false;
		}
	}
	async setPage() {
		const pageResponse = this.getPageResponse();
		if (!this.shouldSetPage(pageResponse)) return Promise.resolve();
		this.mergeProps(pageResponse);
		page$1.mergeOncePropsIntoResponse(pageResponse);
		this.preserveEqualProps(pageResponse);
		await this.setRememberedState(pageResponse);
		this.requestParams.setPreserveOptions(pageResponse);
		pageResponse.url = history.preserveUrl ? page$1.get().url : this.pageUrl(pageResponse);
		this.requestParams.all().onBeforeUpdate(pageResponse);
		fireBeforeUpdateEvent(pageResponse);
		return page$1.set(pageResponse, {
			replace: this.requestParams.all().replace,
			preserveScroll: this.requestParams.all().preserveScroll,
			preserveState: this.requestParams.all().preserveState,
			viewTransition: this.requestParams.all().viewTransition
		});
	}
	getDataFromResponse(response) {
		if (typeof response !== "string") return response;
		try {
			return JSON.parse(response);
		} catch (error) {
			return response;
		}
	}
	shouldSetPage(pageResponse) {
		if (!this.requestParams.all().async) return true;
		if (this.originatingPage.component !== pageResponse.component) return true;
		if (this.originatingPage.component !== page$1.get().component) return false;
		const originatingUrl = hrefToUrl(this.originatingPage.url);
		const currentPageUrl = hrefToUrl(page$1.get().url);
		return originatingUrl.origin === currentPageUrl.origin && originatingUrl.pathname === currentPageUrl.pathname;
	}
	pageUrl(pageResponse) {
		const responseUrl = hrefToUrl(pageResponse.url);
		setHashIfSameUrl(this.requestParams.all().url, responseUrl);
		return responseUrl.pathname + responseUrl.search + responseUrl.hash;
	}
	preserveEqualProps(pageResponse) {
		if (pageResponse.component !== page$1.get().component || config$1.get("future.preserveEqualProps") !== true) return;
		const currentPageProps = page$1.get().props;
		Object.entries(pageResponse.props).forEach(([key, value]) => {
			if (isEqual(value, currentPageProps[key])) pageResponse.props[key] = currentPageProps[key];
		});
	}
	mergeProps(pageResponse) {
		if (!this.requestParams.isPartial() || pageResponse.component !== page$1.get().component) return;
		const propsToAppend = pageResponse.mergeProps || [];
		const propsToPrepend = pageResponse.prependProps || [];
		const propsToDeepMerge = pageResponse.deepMergeProps || [];
		const matchPropsOn = pageResponse.matchPropsOn || [];
		const mergeProp = (prop, shouldAppend) => {
			const currentProp = get(page$1.get().props, prop);
			const incomingProp = get(pageResponse.props, prop);
			if (Array.isArray(incomingProp)) {
				const newArray = this.mergeOrMatchItems(currentProp || [], incomingProp, prop, matchPropsOn, shouldAppend);
				set(pageResponse.props, prop, newArray);
			} else if (typeof incomingProp === "object" && incomingProp !== null) {
				const newObject = {
					...currentProp || {},
					...incomingProp
				};
				set(pageResponse.props, prop, newObject);
			}
		};
		propsToAppend.forEach((prop) => mergeProp(prop, true));
		propsToPrepend.forEach((prop) => mergeProp(prop, false));
		propsToDeepMerge.forEach((prop) => {
			const currentProp = page$1.get().props[prop];
			const incomingProp = pageResponse.props[prop];
			const deepMerge = (target, source, matchProp) => {
				if (Array.isArray(source)) return this.mergeOrMatchItems(target, source, matchProp, matchPropsOn);
				if (typeof source === "object" && source !== null) return Object.keys(source).reduce((acc, key) => {
					acc[key] = deepMerge(target ? target[key] : void 0, source[key], `${matchProp}.${key}`);
					return acc;
				}, { ...target });
				return source;
			};
			pageResponse.props[prop] = deepMerge(currentProp, incomingProp, prop);
		});
		pageResponse.props = {
			...page$1.get().props,
			...pageResponse.props
		};
		if (this.requestParams.isDeferredPropsRequest()) {
			const currentErrors = page$1.get().props.errors;
			if (currentErrors && Object.keys(currentErrors).length > 0) pageResponse.props.errors = currentErrors;
		}
		if (page$1.get().scrollProps) pageResponse.scrollProps = {
			...page$1.get().scrollProps || {},
			...pageResponse.scrollProps || {}
		};
		if (page$1.hasOnceProps()) pageResponse.onceProps = {
			...page$1.get().onceProps || {},
			...pageResponse.onceProps || {}
		};
		if (this.requestParams.isDeferredPropsRequest()) pageResponse.flash = { ...page$1.get().flash };
		const currentOriginalDeferred = page$1.get().initialDeferredProps;
		if (currentOriginalDeferred && Object.keys(currentOriginalDeferred).length > 0) pageResponse.initialDeferredProps = currentOriginalDeferred;
	}
	mergeOrMatchItems(existingItems, newItems, matchProp, matchPropsOn, shouldAppend = true) {
		const items = Array.isArray(existingItems) ? existingItems : [];
		const matchingKey = matchPropsOn.find((key) => {
			return key.split(".").slice(0, -1).join(".") === matchProp;
		});
		if (!matchingKey) return shouldAppend ? [...items, ...newItems] : [...newItems, ...items];
		const uniqueProperty = matchingKey.split(".").pop() || "";
		const newItemsMap = /* @__PURE__ */ new Map();
		newItems.forEach((item) => {
			if (this.hasUniqueProperty(item, uniqueProperty)) newItemsMap.set(item[uniqueProperty], item);
		});
		return shouldAppend ? this.appendWithMatching(items, newItems, newItemsMap, uniqueProperty) : this.prependWithMatching(items, newItems, newItemsMap, uniqueProperty);
	}
	appendWithMatching(existingItems, newItems, newItemsMap, uniqueProperty) {
		const updatedExisting = existingItems.map((item) => {
			if (this.hasUniqueProperty(item, uniqueProperty) && newItemsMap.has(item[uniqueProperty])) return newItemsMap.get(item[uniqueProperty]);
			return item;
		});
		const newItemsToAdd = newItems.filter((item) => {
			if (!this.hasUniqueProperty(item, uniqueProperty)) return true;
			return !existingItems.some((existing) => this.hasUniqueProperty(existing, uniqueProperty) && existing[uniqueProperty] === item[uniqueProperty]);
		});
		return [...updatedExisting, ...newItemsToAdd];
	}
	prependWithMatching(existingItems, newItems, newItemsMap, uniqueProperty) {
		const untouchedExisting = existingItems.filter((item) => {
			if (this.hasUniqueProperty(item, uniqueProperty)) return !newItemsMap.has(item[uniqueProperty]);
			return true;
		});
		return [...newItems, ...untouchedExisting];
	}
	hasUniqueProperty(item, property) {
		return item && typeof item === "object" && property in item;
	}
	async setRememberedState(pageResponse) {
		const rememberedState = await history.getState(history.rememberedState, {});
		if (this.requestParams.all().preserveState && rememberedState && pageResponse.component === page$1.get().component) pageResponse.rememberedState = rememberedState;
	}
	getScopedErrors(errors) {
		if (!this.requestParams.all().errorBag) return errors;
		return errors[this.requestParams.all().errorBag || ""] || {};
	}
};
var Request = class _Request {
	constructor(params, page2) {
		this.page = page2;
		this.requestHasFinished = false;
		this.requestParams = RequestParams.create(params);
		this.cancelToken = new AbortController();
	}
	static create(params, page2) {
		return new _Request(params, page2);
	}
	isPrefetch() {
		return this.requestParams.isPrefetch();
	}
	async send() {
		this.requestParams.onCancelToken(() => this.cancel({ cancelled: true }));
		fireStartEvent(this.requestParams.all());
		this.requestParams.onStart();
		if (this.requestParams.all().prefetch) {
			this.requestParams.onPrefetching();
			firePrefetchingEvent(this.requestParams.all());
		}
		const originallyPrefetch = this.requestParams.all().prefetch;
		return axios({
			method: this.requestParams.all().method,
			url: urlWithoutHash(this.requestParams.all().url).href,
			data: this.requestParams.data(),
			params: this.requestParams.queryParams(),
			signal: this.cancelToken.signal,
			headers: this.getHeaders(),
			onUploadProgress: this.onProgress.bind(this),
			responseType: "text"
		}).then((response) => {
			this.response = Response.create(this.requestParams, response, this.page);
			return this.response.handle();
		}).catch((error) => {
			if (error?.response) {
				this.response = Response.create(this.requestParams, error.response, this.page);
				return this.response.handle();
			}
			return Promise.reject(error);
		}).catch((error) => {
			if (axios.isCancel(error)) return;
			if (fireExceptionEvent(error)) {
				if (originallyPrefetch) this.requestParams.onPrefetchError(error);
				return Promise.reject(error);
			}
		}).finally(() => {
			this.finish();
			if (originallyPrefetch && this.response) this.requestParams.onPrefetchResponse(this.response);
		});
	}
	finish() {
		if (this.requestParams.wasCancelledAtAll()) return;
		this.requestParams.markAsFinished();
		this.fireFinishEvents();
	}
	fireFinishEvents() {
		if (this.requestHasFinished) return;
		this.requestHasFinished = true;
		fireFinishEvent(this.requestParams.all());
		this.requestParams.onFinish();
	}
	cancel({ cancelled = false, interrupted = false }) {
		if (this.requestHasFinished) return;
		this.cancelToken.abort();
		this.requestParams.markAsCancelled({
			cancelled,
			interrupted
		});
		this.fireFinishEvents();
	}
	onProgress(progress3) {
		if (this.requestParams.data() instanceof FormData) {
			progress3.percentage = progress3.progress ? Math.round(progress3.progress * 100) : 0;
			fireProgressEvent(progress3);
			this.requestParams.all().onProgress(progress3);
		}
	}
	getHeaders() {
		const headers = {
			...this.requestParams.headers(),
			Accept: "text/html, application/xhtml+xml",
			"X-Requested-With": "XMLHttpRequest",
			"X-Inertia": true
		};
		const page2 = page$1.get();
		if (page2.version) headers["X-Inertia-Version"] = page2.version;
		const onceProps = Object.entries(page2.onceProps || {}).filter(([, onceProp]) => {
			if (page2.props[onceProp.prop] === void 0) return false;
			return !onceProp.expiresAt || onceProp.expiresAt > Date.now();
		}).map(([key]) => key);
		if (onceProps.length > 0) headers["X-Inertia-Except-Once-Props"] = onceProps.join(",");
		return headers;
	}
};
var RequestStream = class {
	constructor({ maxConcurrent, interruptible }) {
		this.requests = [];
		this.maxConcurrent = maxConcurrent;
		this.interruptible = interruptible;
	}
	send(request) {
		this.requests.push(request);
		request.send().finally(() => {
			this.requests = this.requests.filter((r) => r !== request);
		});
	}
	interruptInFlight() {
		this.cancel({ interrupted: true }, false);
	}
	cancelInFlight({ prefetch = true } = {}) {
		this.requests.filter((request) => prefetch || !request.isPrefetch()).forEach((request) => request.cancel({ cancelled: true }));
	}
	cancel({ cancelled = false, interrupted = false } = {}, force = false) {
		if (!force && !this.shouldCancel()) return;
		this.requests.shift()?.cancel({
			cancelled,
			interrupted
		});
	}
	shouldCancel() {
		return this.interruptible && this.requests.length >= this.maxConcurrent;
	}
};
var Router = class {
	constructor() {
		this.syncRequestStream = new RequestStream({
			maxConcurrent: 1,
			interruptible: true
		});
		this.asyncRequestStream = new RequestStream({
			maxConcurrent: Infinity,
			interruptible: false
		});
		this.clientVisitQueue = new Queue();
	}
	init({ initialPage, resolveComponent, swapComponent, onFlash }) {
		page$1.init({
			initialPage,
			resolveComponent,
			swapComponent,
			onFlash
		});
		InitialVisit.handle();
		eventHandler.init();
		eventHandler.on("missingHistoryItem", () => {
			if (typeof window !== "undefined") this.visit(window.location.href, {
				preserveState: true,
				preserveScroll: true,
				replace: true
			});
		});
		eventHandler.on("loadDeferredProps", (deferredProps) => {
			this.loadDeferredProps(deferredProps);
		});
		eventHandler.on("historyQuotaExceeded", (url) => {
			window.location.href = url;
		});
	}
	get(url, data = {}, options = {}) {
		return this.visit(url, {
			...options,
			method: "get",
			data
		});
	}
	post(url, data = {}, options = {}) {
		return this.visit(url, {
			preserveState: true,
			...options,
			method: "post",
			data
		});
	}
	put(url, data = {}, options = {}) {
		return this.visit(url, {
			preserveState: true,
			...options,
			method: "put",
			data
		});
	}
	patch(url, data = {}, options = {}) {
		return this.visit(url, {
			preserveState: true,
			...options,
			method: "patch",
			data
		});
	}
	delete(url, options = {}) {
		return this.visit(url, {
			preserveState: true,
			...options,
			method: "delete"
		});
	}
	reload(options = {}) {
		return this.doReload(options);
	}
	doReload(options = {}) {
		if (typeof window === "undefined") return;
		return this.visit(window.location.href, {
			...options,
			preserveScroll: true,
			preserveState: true,
			async: true,
			headers: {
				...options.headers || {},
				"Cache-Control": "no-cache"
			}
		});
	}
	remember(data, key = "default") {
		history.remember(data, key);
	}
	restore(key = "default") {
		return history.restore(key);
	}
	on(type, callback) {
		if (typeof window === "undefined") return () => {};
		return eventHandler.onGlobalEvent(type, callback);
	}
	/**
	* @deprecated Use cancelAll() instead.
	*/
	cancel() {
		this.syncRequestStream.cancelInFlight();
	}
	cancelAll({ async = true, prefetch = true, sync = true } = {}) {
		if (async) this.asyncRequestStream.cancelInFlight({ prefetch });
		if (sync) this.syncRequestStream.cancelInFlight();
	}
	poll(interval, requestOptions = {}, options = {}) {
		return polls.add(interval, () => this.reload(requestOptions), {
			autoStart: options.autoStart ?? true,
			keepAlive: options.keepAlive ?? false
		});
	}
	visit(href, options = {}) {
		const visit = this.getPendingVisit(href, {
			...options,
			showProgress: options.showProgress ?? !options.async
		});
		const events = this.getVisitEvents(options);
		if (events.onBefore(visit) === false || !fireBeforeEvent(visit)) return;
		const currentPageUrl = hrefToUrl(page$1.get().url);
		if (!(visit.only.length > 0 || visit.except.length > 0 || visit.reset.length > 0 ? isSameUrlWithoutQueryOrHash(visit.url, currentPageUrl) : isSameUrlWithoutHash(visit.url, currentPageUrl))) this.asyncRequestStream.cancelInFlight({ prefetch: false });
		if (!visit.async) this.syncRequestStream.interruptInFlight();
		if (!page$1.isCleared() && !visit.preserveUrl) Scroll.save();
		const requestParams = {
			...visit,
			...events
		};
		const prefetched = prefetchedRequests.get(requestParams);
		if (prefetched) {
			progress.reveal(prefetched.inFlight);
			prefetchedRequests.use(prefetched, requestParams);
		} else {
			progress.reveal(true);
			(visit.async ? this.asyncRequestStream : this.syncRequestStream).send(Request.create(requestParams, page$1.get()));
		}
	}
	getCached(href, options = {}) {
		return prefetchedRequests.findCached(this.getPrefetchParams(href, options));
	}
	flush(href, options = {}) {
		prefetchedRequests.remove(this.getPrefetchParams(href, options));
	}
	flushAll() {
		prefetchedRequests.removeAll();
	}
	flushByCacheTags(tags) {
		prefetchedRequests.removeByTags(Array.isArray(tags) ? tags : [tags]);
	}
	getPrefetching(href, options = {}) {
		return prefetchedRequests.findInFlight(this.getPrefetchParams(href, options));
	}
	prefetch(href, options = {}, prefetchOptions = {}) {
		if ((options.method ?? (isUrlMethodPair(href) ? href.method : "get")) !== "get") throw new Error("Prefetch requests must use the GET method");
		const visit = this.getPendingVisit(href, {
			...options,
			async: true,
			showProgress: false,
			prefetch: true,
			viewTransition: false
		});
		if (visit.url.origin + visit.url.pathname + visit.url.search === window.location.origin + window.location.pathname + window.location.search) return;
		const events = this.getVisitEvents(options);
		if (events.onBefore(visit) === false || !fireBeforeEvent(visit)) return;
		progress.hide();
		this.asyncRequestStream.interruptInFlight();
		const requestParams = {
			...visit,
			...events
		};
		const ensureCurrentPageIsSet = () => {
			return new Promise((resolve) => {
				const checkIfPageIsDefined = () => {
					if (page$1.get()) resolve();
					else setTimeout(checkIfPageIsDefined, 50);
				};
				checkIfPageIsDefined();
			});
		};
		ensureCurrentPageIsSet().then(() => {
			prefetchedRequests.add(requestParams, (params) => {
				this.asyncRequestStream.send(Request.create(params, page$1.get()));
			}, {
				cacheFor: config$1.get("prefetch.cacheFor"),
				cacheTags: [],
				...prefetchOptions
			});
		});
	}
	clearHistory() {
		history.clear();
	}
	decryptHistory() {
		return history.decrypt();
	}
	resolveComponent(component) {
		return page$1.resolve(component);
	}
	replace(params) {
		this.clientVisit(params, { replace: true });
	}
	replaceProp(name, value, options) {
		this.replace({
			preserveScroll: true,
			preserveState: true,
			props(currentProps) {
				const newValue = typeof value === "function" ? value(get(currentProps, name), currentProps) : value;
				return set(cloneDeep(currentProps), name, newValue);
			},
			...options || {}
		});
	}
	appendToProp(name, value, options) {
		this.replaceProp(name, (currentValue, currentProps) => {
			const newValue = typeof value === "function" ? value(currentValue, currentProps) : value;
			if (!Array.isArray(currentValue)) currentValue = currentValue !== void 0 ? [currentValue] : [];
			return [...currentValue, newValue];
		}, options);
	}
	prependToProp(name, value, options) {
		this.replaceProp(name, (currentValue, currentProps) => {
			const newValue = typeof value === "function" ? value(currentValue, currentProps) : value;
			if (!Array.isArray(currentValue)) currentValue = currentValue !== void 0 ? [currentValue] : [];
			return [newValue, ...currentValue];
		}, options);
	}
	push(params) {
		this.clientVisit(params);
	}
	flash(keyOrData, value) {
		const current = page$1.get().flash;
		let flash;
		if (typeof keyOrData === "function") flash = keyOrData(current);
		else if (typeof keyOrData === "string") flash = {
			...current,
			[keyOrData]: value
		};
		else if (keyOrData && Object.keys(keyOrData).length) flash = {
			...current,
			...keyOrData
		};
		else return;
		page$1.setFlash(flash);
		if (Object.keys(flash).length) fireFlashEvent(flash);
	}
	clientVisit(params, { replace = false } = {}) {
		this.clientVisitQueue.add(() => this.performClientVisit(params, { replace }));
	}
	performClientVisit(params, { replace = false } = {}) {
		const current = page$1.get();
		const onceProps = typeof params.props === "function" ? Object.fromEntries(Object.values(current.onceProps ?? {}).map((onceProp) => [onceProp.prop, current.props[onceProp.prop]])) : {};
		const props = typeof params.props === "function" ? params.props(current.props, onceProps) : params.props ?? current.props;
		const flash = typeof params.flash === "function" ? params.flash(current.flash) : params.flash;
		const { viewTransition, onError, onFinish, onFlash, onSuccess, ...pageParams } = params;
		const page2 = {
			...current,
			...pageParams,
			flash: flash ?? {},
			props
		};
		const preserveScroll = RequestParams.resolvePreserveOption(params.preserveScroll ?? false, page2);
		const preserveState = RequestParams.resolvePreserveOption(params.preserveState ?? false, page2);
		return page$1.set(page2, {
			replace,
			preserveScroll,
			preserveState,
			viewTransition
		}).then(() => {
			const currentFlash = page$1.get().flash;
			if (Object.keys(currentFlash).length > 0) {
				fireFlashEvent(currentFlash);
				onFlash?.(currentFlash);
			}
			const errors = page$1.get().props.errors || {};
			if (Object.keys(errors).length === 0) {
				onSuccess?.(page$1.get());
				return;
			}
			const scopedErrors = params.errorBag ? errors[params.errorBag || ""] || {} : errors;
			onError?.(scopedErrors);
		}).finally(() => onFinish?.(params));
	}
	getPrefetchParams(href, options) {
		return {
			...this.getPendingVisit(href, {
				...options,
				async: true,
				showProgress: false,
				prefetch: true,
				viewTransition: false
			}),
			...this.getVisitEvents(options)
		};
	}
	getPendingVisit(href, options, pendingVisitOptions = {}) {
		if (isUrlMethodPair(href)) {
			const urlMethodPair = href;
			href = urlMethodPair.url;
			options.method = options.method ?? urlMethodPair.method;
		}
		const defaultVisitOptionsCallback = config$1.get("visitOptions");
		const configuredOptions = defaultVisitOptionsCallback ? defaultVisitOptionsCallback(href.toString(), cloneDeep(options)) || {} : {};
		const mergedOptions = {
			method: "get",
			data: {},
			replace: false,
			preserveScroll: false,
			preserveState: false,
			only: [],
			except: [],
			headers: {},
			errorBag: "",
			forceFormData: false,
			queryStringArrayFormat: "brackets",
			async: false,
			showProgress: true,
			fresh: false,
			reset: [],
			preserveUrl: false,
			prefetch: false,
			invalidateCacheTags: [],
			viewTransition: false,
			...options,
			...configuredOptions
		};
		const [url, _data] = transformUrlAndData(href, mergedOptions.data, mergedOptions.method, mergedOptions.forceFormData, mergedOptions.queryStringArrayFormat);
		const visit = {
			cancelled: false,
			completed: false,
			interrupted: false,
			...mergedOptions,
			...pendingVisitOptions,
			url,
			data: _data
		};
		if (visit.prefetch) visit.headers["Purpose"] = "prefetch";
		return visit;
	}
	getVisitEvents(options) {
		return {
			onCancelToken: options.onCancelToken || (() => {}),
			onBefore: options.onBefore || (() => {}),
			onBeforeUpdate: options.onBeforeUpdate || (() => {}),
			onStart: options.onStart || (() => {}),
			onProgress: options.onProgress || (() => {}),
			onFinish: options.onFinish || (() => {}),
			onCancel: options.onCancel || (() => {}),
			onSuccess: options.onSuccess || (() => {}),
			onError: options.onError || (() => {}),
			onFlash: options.onFlash || (() => {}),
			onPrefetched: options.onPrefetched || (() => {}),
			onPrefetching: options.onPrefetching || (() => {})
		};
	}
	loadDeferredProps(deferred) {
		if (deferred) Object.entries(deferred).forEach(([_, group]) => {
			this.doReload({
				only: group,
				deferredProps: true
			});
		});
	}
};
var UseFormUtils = class {
	/**
	* Creates a callback that returns a UrlMethodPair.
	*
	* createWayfinderCallback(urlMethodPair)
	* createWayfinderCallback(method, url)
	* createWayfinderCallback(() => urlMethodPair)
	* createWayfinderCallback(() => method, () => url)
	*/
	static createWayfinderCallback(...args) {
		return () => {
			if (args.length === 1) return isUrlMethodPair(args[0]) ? args[0] : args[0]();
			return {
				method: typeof args[0] === "function" ? args[0]() : args[0],
				url: typeof args[1] === "function" ? args[1]() : args[1]
			};
		};
	}
	/**
	* Parses all useForm() arguments into { rememberKey, data, precognitionEndpoint }.
	*
	* useForm()
	* useForm(data)
	* useForm(rememberKey, data)
	* useForm(method, url, data)
	* useForm(urlMethodPair, data)
	*
	*/
	static parseUseFormArguments(...args) {
		if (args.length === 0) return {
			rememberKey: null,
			data: {},
			precognitionEndpoint: null
		};
		if (args.length === 1) return {
			rememberKey: null,
			data: args[0],
			precognitionEndpoint: null
		};
		if (args.length === 2) {
			if (typeof args[0] === "string") return {
				rememberKey: args[0],
				data: args[1],
				precognitionEndpoint: null
			};
			return {
				rememberKey: null,
				data: args[1],
				precognitionEndpoint: this.createWayfinderCallback(args[0])
			};
		}
		return {
			rememberKey: null,
			data: args[2],
			precognitionEndpoint: this.createWayfinderCallback(args[0], args[1])
		};
	}
	/**
	* Parses all submission arguments into { method, url, options }.
	* It uses the Precognition endpoint if no explicit method/url are provided.
	*
	* form.submit(method, url)
	* form.submit(method, url, options)
	* form.submit(urlMethodPair)
	* form.submit(urlMethodPair, options)
	* form.submit()
	* form.submit(options)
	*/
	static parseSubmitArguments(args, precognitionEndpoint) {
		if (args.length === 3 || args.length === 2 && typeof args[0] === "string") return {
			method: args[0],
			url: args[1],
			options: args[2] ?? {}
		};
		if (isUrlMethodPair(args[0])) return {
			...args[0],
			options: args[1] ?? {}
		};
		return {
			...precognitionEndpoint(),
			options: args[0] ?? {}
		};
	}
	/**
	* Merges headers into the Precognition validate() arguments.
	*/
	static mergeHeadersForValidation(field, config2, headers) {
		const merge = (config3) => {
			config3.headers = {
				...headers ?? {},
				...config3.headers ?? {}
			};
			return config3;
		};
		if (field && typeof field === "object" && !("target" in field)) field = merge(field);
		else if (config2 && typeof config2 === "object") config2 = merge(config2);
		else if (typeof field === "string") config2 = merge(config2 ?? {});
		else field = merge(field ?? {});
		return [field, config2];
	}
};
function undotKey(key) {
	if (!key.includes(".")) return key;
	const transformSegment = (segment) => {
		if (segment.startsWith("[") && segment.endsWith("]")) return segment;
		return segment.split(".").reduce((result, part, index) => index === 0 ? part : `${result}[${part}]`);
	};
	return key.replace(/\\\./g, "__ESCAPED_DOT__").split(/(\[[^\]]*\])/).filter(Boolean).map(transformSegment).join("").replace(/__ESCAPED_DOT__/g, ".");
}
function parseKey(key) {
	const path = [];
	const pattern = /([^\[\]]+)|\[(\d*)\]/g;
	let match;
	while ((match = pattern.exec(key)) !== null) if (match[1] !== void 0) path.push(match[1]);
	else if (match[2] !== void 0) path.push(match[2] === "" ? "" : Number(match[2]));
	return path;
}
function setNestedObject(obj, path, value) {
	let current = obj;
	for (let i = 0; i < path.length - 1; i++) {
		if (!(path[i] in current)) current[path[i]] = {};
		current = current[path[i]];
	}
	current[path[path.length - 1]] = value;
}
function objectHasSequentialNumericKeys(value) {
	const keys = Object.keys(value);
	const numericKeys = keys.filter((k) => /^\d+$/.test(k)).map(Number).sort((a, b) => a - b);
	return keys.length === numericKeys.length && numericKeys.length > 0 && numericKeys[0] === 0 && numericKeys.every((n, i) => n === i);
}
function convertSequentialObjectsToArrays(value) {
	if (Array.isArray(value)) return value.map(convertSequentialObjectsToArrays);
	if (typeof value !== "object" || value === null || isFile$1(value)) return value;
	if (objectHasSequentialNumericKeys(value)) {
		const result2 = [];
		for (let i = 0; i < Object.keys(value).length; i++) result2[i] = convertSequentialObjectsToArrays(value[i]);
		return result2;
	}
	const result = {};
	for (const key in value) result[key] = convertSequentialObjectsToArrays(value[key]);
	return result;
}
function formDataToObject(source) {
	const form = {};
	for (const [key, value] of source.entries()) {
		if (value instanceof File && value.size === 0 && value.name === "") continue;
		const path = parseKey(undotKey(key));
		if (path[path.length - 1] === "") {
			const arrayPath = path.slice(0, -1);
			const existing = get(form, arrayPath);
			if (Array.isArray(existing)) existing.push(value);
			else if (existing && typeof existing === "object" && !isFile$1(existing)) {
				const numericKeys = Object.keys(existing).filter((k) => /^\d+$/.test(k)).map(Number).sort((a, b) => a - b);
				set(form, arrayPath, numericKeys.length > 0 ? [...numericKeys.map((k) => existing[k]), value] : [value]);
			} else set(form, arrayPath, [value]);
			continue;
		}
		setNestedObject(form, path.map(String), value);
	}
	return convertSequentialObjectsToArrays(form);
}
var Renderer = {
	preferredAttribute() {
		return config$1.get("future.useDataInertiaHeadAttribute") ? "data-inertia" : "inertia";
	},
	buildDOMElement(tag) {
		const template = document.createElement("template");
		template.innerHTML = tag;
		const node = template.content.firstChild;
		if (!tag.startsWith("<script ")) return node;
		const script = document.createElement("script");
		script.innerHTML = node.innerHTML;
		node.getAttributeNames().forEach((name) => {
			script.setAttribute(name, node.getAttribute(name) || "");
		});
		return script;
	},
	isInertiaManagedElement(element) {
		return element.nodeType === Node.ELEMENT_NODE && element.getAttribute(this.preferredAttribute()) !== null;
	},
	findMatchingElementIndex(element, elements) {
		const attribute = this.preferredAttribute();
		const key = element.getAttribute(attribute);
		if (key !== null) return elements.findIndex((element2) => element2.getAttribute(attribute) === key);
		return -1;
	},
	update: debounce(function(elements) {
		const sourceElements = elements.map((element) => this.buildDOMElement(element));
		Array.from(document.head.childNodes).filter((element) => this.isInertiaManagedElement(element)).forEach((targetElement) => {
			const index = this.findMatchingElementIndex(targetElement, sourceElements);
			if (index === -1) {
				targetElement?.parentNode?.removeChild(targetElement);
				return;
			}
			const sourceElement = sourceElements.splice(index, 1)[0];
			if (sourceElement && !targetElement.isEqualNode(sourceElement)) targetElement?.parentNode?.replaceChild(sourceElement, targetElement);
		});
		sourceElements.forEach((element) => document.head.appendChild(element));
	}, 1)
};
function createHeadManager(isServer3, titleCallback, onUpdate) {
	const states = {};
	let lastProviderId = 0;
	function connect() {
		const id = lastProviderId += 1;
		states[id] = [];
		return id.toString();
	}
	function disconnect(id) {
		if (id === null || Object.keys(states).indexOf(id) === -1) return;
		delete states[id];
		commit();
	}
	function reconnect(id) {
		if (Object.keys(states).indexOf(id) === -1) states[id] = [];
	}
	function update(id, elements = []) {
		if (id !== null && Object.keys(states).indexOf(id) > -1) states[id] = elements;
		commit();
	}
	function collect() {
		const title = titleCallback("");
		const attribute = Renderer.preferredAttribute();
		const defaults = { ...title ? { title: `<title ${attribute}="">${title}</title>` } : {} };
		const elements = Object.values(states).reduce((carry, elements2) => carry.concat(elements2), []).reduce((carry, element) => {
			if (element.indexOf("<") === -1) return carry;
			if (element.indexOf("<title ") === 0) {
				const title2 = element.match(/(<title [^>]+>)(.*?)(<\/title>)/);
				carry.title = title2 ? `${title2[1]}${titleCallback(title2[2])}${title2[3]}` : element;
				return carry;
			}
			const match = element.match(attribute === "inertia" ? / inertia="[^"]+"/ : / data-inertia="[^"]+"/);
			if (match) carry[match[0]] = element;
			else carry[Object.keys(carry).length] = element;
			return carry;
		}, defaults);
		return Object.values(elements);
	}
	function commit() {
		isServer3 ? onUpdate(collect()) : Renderer.update(collect());
	}
	commit();
	return {
		forceUpdate: commit,
		createProvider: function() {
			const id = connect();
			return {
				preferredAttribute: Renderer.preferredAttribute,
				reconnect: () => reconnect(id),
				update: (elements) => update(id, elements),
				disconnect: () => disconnect(id)
			};
		}
	};
}
var MERGE_INTENT_HEADER = "X-Inertia-Infinite-Scroll-Merge-Intent";
var useInfiniteScrollData = (options) => {
	const getScrollPropFromCurrentPage = () => {
		const scrollProp = page$1.get().scrollProps?.[options.getPropName()];
		if (scrollProp) return scrollProp;
		throw new Error(`The page object does not contain a scroll prop named "${options.getPropName()}".`);
	};
	const state = {
		component: null,
		loading: false,
		previousPage: null,
		nextPage: null,
		lastLoadedPage: null,
		requestCount: 0
	};
	const resetState = () => {
		const scrollProp = getScrollPropFromCurrentPage();
		state.component = page$1.get().component;
		state.loading = false;
		state.previousPage = scrollProp.previousPage;
		state.nextPage = scrollProp.nextPage;
		state.lastLoadedPage = scrollProp.currentPage;
		state.requestCount = 0;
	};
	const getRememberKey = () => `inertia:infinite-scroll-data:${options.getPropName()}`;
	if (typeof window !== "undefined") {
		resetState();
		const rememberedState = router.restore(getRememberKey());
		if (rememberedState && typeof rememberedState === "object" && rememberedState.lastLoadedPage === getScrollPropFromCurrentPage().currentPage) {
			state.previousPage = rememberedState.previousPage;
			state.nextPage = rememberedState.nextPage;
			state.lastLoadedPage = rememberedState.lastLoadedPage;
			state.requestCount = rememberedState.requestCount || 0;
		}
	}
	const removeEventListener = router.on("success", (event) => {
		if (state.component === event.detail.page.component && getScrollPropFromCurrentPage().reset) {
			resetState();
			options.onReset?.();
		}
	});
	const getScrollPropKeyForSide = (side) => {
		return side === "next" ? "nextPage" : "previousPage";
	};
	const findPageToLoad = (side) => {
		return state[getScrollPropKeyForSide(side)];
	};
	const syncStateOnSuccess = (side) => {
		const scrollProp = getScrollPropFromCurrentPage();
		const paginationProp = getScrollPropKeyForSide(side);
		state.lastLoadedPage = scrollProp.currentPage;
		state[paginationProp] = scrollProp[paginationProp];
		state.requestCount += 1;
		router.remember({
			previousPage: state.previousPage,
			nextPage: state.nextPage,
			lastLoadedPage: state.lastLoadedPage,
			requestCount: state.requestCount
		}, getRememberKey());
	};
	const getPageName = () => getScrollPropFromCurrentPage().pageName;
	const getRequestCount = () => state.requestCount;
	const fetchPage = (side, reloadOptions = {}) => {
		const page2 = findPageToLoad(side);
		if (state.loading || page2 === null) return;
		state.loading = true;
		router.reload({
			...reloadOptions,
			data: { [getPageName()]: page2 },
			only: [options.getPropName()],
			preserveUrl: true,
			headers: {
				[MERGE_INTENT_HEADER]: side === "previous" ? "prepend" : "append",
				...reloadOptions.headers
			},
			onBefore: (visit) => {
				side === "next" ? options.onBeforeNextRequest() : options.onBeforePreviousRequest();
				reloadOptions.onBefore?.(visit);
			},
			onBeforeUpdate: (page3) => {
				options.onBeforeUpdate();
				reloadOptions.onBeforeUpdate?.(page3);
			},
			onSuccess: (page3) => {
				syncStateOnSuccess(side);
				reloadOptions.onSuccess?.(page3);
			},
			onFinish: (visit) => {
				state.loading = false;
				side === "next" ? options.onCompleteNextRequest(state.lastLoadedPage) : options.onCompletePreviousRequest(state.lastLoadedPage);
				reloadOptions.onFinish?.(visit);
			}
		});
	};
	const getLastLoadedPage = () => state.lastLoadedPage;
	const hasPrevious = () => !!state.previousPage;
	const hasNext = () => !!state.nextPage;
	const fetchPrevious = (reloadOptions) => fetchPage("previous", reloadOptions);
	const fetchNext = (reloadOptions) => fetchPage("next", reloadOptions);
	return {
		getLastLoadedPage,
		getPageName,
		getRequestCount,
		hasPrevious,
		hasNext,
		fetchNext,
		fetchPrevious,
		removeEventListener
	};
};
var useIntersectionObservers = () => {
	const intersectionObservers = [];
	const newIntersectionObserver = (callback, options = {}) => {
		const observer = new IntersectionObserver((entries) => {
			for (const entry of entries) if (entry.isIntersecting) callback(entry);
		}, options);
		intersectionObservers.push(observer);
		return observer;
	};
	const flushAll = () => {
		intersectionObservers.forEach((observer) => observer.disconnect());
		intersectionObservers.length = 0;
	};
	return {
		new: newIntersectionObserver,
		flushAll
	};
};
var INFINITE_SCROLL_PAGE_KEY = "infiniteScrollPage";
var INFINITE_SCROLL_IGNORE_KEY = "infiniteScrollIgnore";
var getPageFromElement = (element) => element.dataset[INFINITE_SCROLL_PAGE_KEY];
var useInfiniteScrollElementManager = (options) => {
	const intersectionObservers = useIntersectionObservers();
	let itemsObserver;
	let startElementObserver;
	let endElementObserver;
	let itemsMutationObserver;
	let triggersEnabled = false;
	const setupObservers = () => {
		itemsMutationObserver = new MutationObserver((mutations) => {
			mutations.forEach((mutation) => {
				mutation.addedNodes.forEach((node) => {
					if (node.nodeType !== Node.ELEMENT_NODE) return;
					addedElements.add(node);
				});
			});
			rememberElementsDebounced();
		});
		itemsMutationObserver.observe(options.getItemsElement(), { childList: true });
		itemsObserver = intersectionObservers.new((entry) => options.onItemIntersected(entry.target));
		const observerOptions = {
			root: options.getScrollableParent(),
			rootMargin: `${Math.max(1, options.getTriggerMargin())}px`
		};
		startElementObserver = intersectionObservers.new(options.onPreviousTriggered, observerOptions);
		endElementObserver = intersectionObservers.new(options.onNextTriggered, observerOptions);
	};
	const enableTriggers = () => {
		if (triggersEnabled) disableTriggers();
		const startElement = options.getStartElement();
		const endElement = options.getEndElement();
		if (startElement && options.shouldFetchPrevious()) startElementObserver.observe(startElement);
		if (endElement && options.shouldFetchNext()) endElementObserver.observe(endElement);
		triggersEnabled = true;
	};
	const disableTriggers = () => {
		if (!triggersEnabled) return;
		startElementObserver.disconnect();
		endElementObserver.disconnect();
		triggersEnabled = false;
	};
	const refreshTriggers = () => {
		if (triggersEnabled) enableTriggers();
	};
	const flushAll = () => {
		disableTriggers();
		intersectionObservers.flushAll();
		itemsMutationObserver?.disconnect();
	};
	const addedElements = /* @__PURE__ */ new Set();
	const elementIsUntagged = (element) => !(INFINITE_SCROLL_PAGE_KEY in element.dataset) && !(INFINITE_SCROLL_IGNORE_KEY in element.dataset);
	const processManuallyAddedElements = () => {
		Array.from(addedElements).forEach((element) => {
			if (elementIsUntagged(element)) element.dataset[INFINITE_SCROLL_IGNORE_KEY] = "true";
			itemsObserver.observe(element);
		});
		addedElements.clear();
	};
	const findUntaggedElements = (containerElement) => {
		return Array.from(containerElement.querySelectorAll(`:scope > *:not([data-infinite-scroll-page]):not([data-infinite-scroll-ignore])`));
	};
	let hasRestoredElements = false;
	const processServerLoadedElements = (loadedPage) => {
		if (!hasRestoredElements) {
			hasRestoredElements = true;
			if (restoreElements()) return;
		}
		findUntaggedElements(options.getItemsElement()).forEach((element) => {
			if (elementIsUntagged(element)) element.dataset[INFINITE_SCROLL_PAGE_KEY] = loadedPage?.toString() || "1";
			itemsObserver.observe(element);
		});
		rememberElements();
	};
	const getElementsRememberKey = () => `inertia:infinite-scroll-elements:${options.getPropName()}`;
	const rememberElements = () => {
		const pageElementRange = {};
		const childNodes = options.getItemsElement().childNodes;
		for (let index = 0; index < childNodes.length; index++) {
			const node = childNodes[index];
			if (node.nodeType !== Node.ELEMENT_NODE) continue;
			const page2 = getPageFromElement(node);
			if (typeof page2 === "undefined") continue;
			if (!(page2 in pageElementRange)) pageElementRange[page2] = {
				from: index,
				to: index
			};
			else pageElementRange[page2].to = index;
		}
		router.remember(pageElementRange, getElementsRememberKey());
	};
	const rememberElementsDebounced = debounce(rememberElements, 250);
	const restoreElements = () => {
		const pageElementRange = router.restore(getElementsRememberKey());
		if (!pageElementRange || typeof pageElementRange !== "object") return false;
		const childNodes = options.getItemsElement().childNodes;
		for (let index = 0; index < childNodes.length; index++) {
			const node = childNodes[index];
			if (node.nodeType !== Node.ELEMENT_NODE) continue;
			const element = node;
			let elementPage;
			for (const [page2, range] of Object.entries(pageElementRange)) if (index >= range.from && index <= range.to) {
				elementPage = page2;
				break;
			}
			if (elementPage) element.dataset[INFINITE_SCROLL_PAGE_KEY] = elementPage;
			else if (!elementIsUntagged(element)) continue;
			else element.dataset[INFINITE_SCROLL_IGNORE_KEY] = "true";
			itemsObserver.observe(element);
		}
		return true;
	};
	return {
		setupObservers,
		enableTriggers,
		disableTriggers,
		refreshTriggers,
		flushAll,
		processManuallyAddedElements,
		processServerLoadedElements
	};
};
var queue3 = new Queue();
var initialUrl;
var payloadUrl;
var initialUrlWasAbsolute = null;
var useInfiniteScrollQueryString = (options) => {
	let enabled = true;
	const queuePageUpdate = (page2) => {
		queue3.add(() => {
			return new Promise((resolve) => {
				if (!enabled) {
					initialUrl = payloadUrl = null;
					return resolve();
				}
				if (!initialUrl || !payloadUrl) {
					const currentPageUrl = page$1.get().url;
					initialUrl = hrefToUrl(currentPageUrl);
					payloadUrl = hrefToUrl(currentPageUrl);
					initialUrlWasAbsolute = urlHasProtocol(currentPageUrl);
				}
				const pageName = options.getPageName();
				const searchParams = payloadUrl.searchParams;
				if (page2 === "1") searchParams.delete(pageName);
				else searchParams.set(pageName, page2);
				setTimeout(() => resolve());
			});
		}).finally(() => {
			if (enabled && initialUrl && payloadUrl && initialUrl.href !== payloadUrl.href && initialUrlWasAbsolute !== null) router.replace({
				url: urlToString(payloadUrl, initialUrlWasAbsolute),
				preserveScroll: true,
				preserveState: true
			});
			initialUrl = payloadUrl = initialUrlWasAbsolute = null;
		});
	};
	return {
		onItemIntersected: debounce((itemElement) => {
			const itemsElement = options.getItemsElement();
			if (!enabled || options.shouldPreserveUrl() || !itemElement || !itemsElement) return;
			const pageMap = /* @__PURE__ */ new Map();
			getElementsInViewportFromCollection([...itemsElement.children], itemElement).forEach((element) => {
				const page2 = getPageFromElement(element) ?? "1";
				if (pageMap.has(page2)) pageMap.set(page2, pageMap.get(page2) + 1);
				else pageMap.set(page2, 1);
			});
			const mostVisiblePage = Array.from(pageMap.entries()).sort((a, b) => b[1] - a[1])[0]?.[0];
			if (mostVisiblePage !== void 0) queuePageUpdate(mostVisiblePage);
		}, 250),
		cancel: () => enabled = false
	};
};
var useInfiniteScrollPreservation = (options) => {
	const createCallbacks = () => {
		let currentScrollTop;
		let referenceElement = null;
		let referenceElementTop = 0;
		const captureScrollPosition = () => {
			const scrollableContainer = options.getScrollableParent();
			const itemsElement = options.getItemsElement();
			currentScrollTop = scrollableContainer?.scrollTop || window.scrollY;
			const visibleElements = getElementsInViewportFromCollection([...itemsElement.children]);
			if (visibleElements.length > 0) {
				referenceElement = visibleElements[0];
				const containerRect = scrollableContainer?.getBoundingClientRect() || { top: 0 };
				const containerTop = scrollableContainer ? containerRect.top : 0;
				referenceElementTop = referenceElement.getBoundingClientRect().top - containerTop;
			}
		};
		const restoreScrollPosition = () => {
			if (!referenceElement) return;
			let attempts = 0;
			let restored = false;
			const restore = () => {
				attempts++;
				if (restored || attempts > 10) return false;
				const scrollableContainer = options.getScrollableParent();
				const containerRect = scrollableContainer?.getBoundingClientRect() || { top: 0 };
				const containerTop = scrollableContainer ? containerRect.top : 0;
				const adjustment = referenceElement.getBoundingClientRect().top - containerTop - referenceElementTop;
				if (adjustment === 0) {
					window.requestAnimationFrame(restore);
					return;
				}
				if (scrollableContainer) scrollableContainer.scrollTo({ top: currentScrollTop + adjustment });
				else window.scrollTo(0, window.scrollY + adjustment);
				restored = true;
			};
			window.requestAnimationFrame(restore);
		};
		return {
			captureScrollPosition,
			restoreScrollPosition
		};
	};
	return { createCallbacks };
};
function useInfiniteScroll(options) {
	const queryStringManager = useInfiniteScrollQueryString({
		...options,
		getPageName: () => dataManager.getPageName()
	});
	const scrollPreservation = useInfiniteScrollPreservation(options);
	const elementManager = useInfiniteScrollElementManager({
		...options,
		onItemIntersected: queryStringManager.onItemIntersected,
		onPreviousTriggered: () => dataManager.fetchPrevious(),
		onNextTriggered: () => dataManager.fetchNext()
	});
	const dataManager = useInfiniteScrollData({
		...options,
		onBeforeUpdate: elementManager.processManuallyAddedElements,
		onCompletePreviousRequest: (loadedPage) => {
			options.onCompletePreviousRequest();
			requestAnimationFrame(() => elementManager.processServerLoadedElements(loadedPage), 2);
		},
		onCompleteNextRequest: (loadedPage) => {
			options.onCompleteNextRequest();
			requestAnimationFrame(() => elementManager.processServerLoadedElements(loadedPage), 2);
		},
		onReset: options.onDataReset
	});
	const addScrollPreservationCallbacks = (reloadOptions) => {
		const { captureScrollPosition, restoreScrollPosition } = scrollPreservation.createCallbacks();
		const originalOnBeforeUpdate = reloadOptions.onBeforeUpdate || (() => {});
		const originalOnSuccess = reloadOptions.onSuccess || (() => {});
		reloadOptions.onBeforeUpdate = (page2) => {
			originalOnBeforeUpdate(page2);
			captureScrollPosition();
		};
		reloadOptions.onSuccess = (page2) => {
			originalOnSuccess(page2);
			restoreScrollPosition();
		};
		return reloadOptions;
	};
	const originalFetchNext = dataManager.fetchNext;
	dataManager.fetchNext = (reloadOptions = {}) => {
		if (options.inReverseMode()) reloadOptions = addScrollPreservationCallbacks(reloadOptions);
		originalFetchNext(reloadOptions);
	};
	const originalFetchPrevious = dataManager.fetchPrevious;
	dataManager.fetchPrevious = (reloadOptions = {}) => {
		if (!options.inReverseMode()) reloadOptions = addScrollPreservationCallbacks(reloadOptions);
		originalFetchPrevious(reloadOptions);
	};
	const removeEventListener = router.on("success", () => requestAnimationFrame(elementManager.refreshTriggers, 2));
	return {
		dataManager,
		elementManager,
		flush: () => {
			removeEventListener();
			dataManager.removeEventListener();
			elementManager.flushAll();
			queryStringManager.cancel();
		}
	};
}
function isContentEditableOrPrevented(event) {
	return event.target instanceof HTMLElement && event.target.isContentEditable || event.defaultPrevented;
}
function shouldIntercept(event) {
	const isLink = event.currentTarget.tagName.toLowerCase() === "a";
	return !(isContentEditableOrPrevented(event) || isLink && event.altKey || isLink && event.ctrlKey || isLink && event.metaKey || isLink && event.shiftKey || isLink && "button" in event && event.button !== 0);
}
function shouldNavigate(event) {
	const isButton = event.currentTarget.tagName.toLowerCase() === "button";
	return !isContentEditableOrPrevented(event) && (event.key === "Enter" || isButton && event.key === " ");
}
var baseComponentSelector = "nprogress";
var progress2;
var settings = {
	minimum: .08,
	easing: "linear",
	positionUsing: "translate3d",
	speed: 200,
	trickle: true,
	trickleSpeed: 200,
	showSpinner: true,
	barSelector: "[role=\"bar\"]",
	spinnerSelector: "[role=\"spinner\"]",
	parent: "body",
	color: "#29d",
	includeCSS: true,
	template: [
		"<div class=\"bar\" role=\"bar\">",
		"<div class=\"peg\"></div>",
		"</div>",
		"<div class=\"spinner\" role=\"spinner\">",
		"<div class=\"spinner-icon\"></div>",
		"</div>"
	].join("")
};
var status = null;
var configure = (options) => {
	Object.assign(settings, options);
	if (settings.includeCSS) injectCSS(settings.color);
	progress2 = document.createElement("div");
	progress2.id = baseComponentSelector;
	progress2.innerHTML = settings.template;
};
var set5 = (n) => {
	const started = isStarted();
	n = clamp(n, settings.minimum, 1);
	status = n === 1 ? null : n;
	const progress3 = render(!started);
	const bar = progress3.querySelector(settings.barSelector);
	const speed = settings.speed;
	const ease = settings.easing;
	progress3.offsetWidth;
	queue4((next) => {
		const barStyles = (() => {
			if (settings.positionUsing === "translate3d") return {
				transition: `all ${speed}ms ${ease}`,
				transform: `translate3d(${toBarPercentage(n)}%,0,0)`
			};
			if (settings.positionUsing === "translate") return {
				transition: `all ${speed}ms ${ease}`,
				transform: `translate(${toBarPercentage(n)}%,0)`
			};
			return { marginLeft: `${toBarPercentage(n)}%` };
		})();
		for (const key in barStyles) bar.style[key] = barStyles[key];
		if (n !== 1) return setTimeout(next, speed);
		progress3.style.transition = "none";
		progress3.style.opacity = "1";
		progress3.offsetWidth;
		setTimeout(() => {
			progress3.style.transition = `all ${speed}ms linear`;
			progress3.style.opacity = "0";
			setTimeout(() => {
				remove();
				progress3.style.transition = "";
				progress3.style.opacity = "";
				next();
			}, speed);
		}, speed);
	});
};
var isStarted = () => typeof status === "number";
var start = () => {
	if (!status) set5(0);
	const work = function() {
		setTimeout(function() {
			if (!status) return;
			increaseByRandom();
			work();
		}, settings.trickleSpeed);
	};
	if (settings.trickle) work();
};
var done = (force) => {
	if (!force && !status) return;
	increaseByRandom(.3 + .5 * Math.random());
	set5(1);
};
var increaseByRandom = (amount) => {
	const n = status;
	if (n === null) return start();
	if (n > 1) return;
	amount = typeof amount === "number" ? amount : (() => {
		const ranges = {
			.1: [0, .2],
			.04: [.2, .5],
			.02: [.5, .8],
			.005: [.8, .99]
		};
		for (const r in ranges) if (n >= ranges[r][0] && n < ranges[r][1]) return parseFloat(r);
		return 0;
	})();
	return set5(clamp(n + amount, 0, .994));
};
var render = (fromStart) => {
	if (isRendered()) return document.getElementById(baseComponentSelector);
	document.documentElement.classList.add(`${baseComponentSelector}-busy`);
	const bar = progress2.querySelector(settings.barSelector);
	const perc = fromStart ? "-100" : toBarPercentage(status || 0);
	const parent = getParent();
	bar.style.transition = "all 0 linear";
	bar.style.transform = `translate3d(${perc}%,0,0)`;
	if (!settings.showSpinner) progress2.querySelector(settings.spinnerSelector)?.remove();
	if (parent !== document.body) parent.classList.add(`${baseComponentSelector}-custom-parent`);
	parent.appendChild(progress2);
	return progress2;
};
var getParent = () => {
	return isDOM(settings.parent) ? settings.parent : document.querySelector(settings.parent);
};
var remove = () => {
	document.documentElement.classList.remove(`${baseComponentSelector}-busy`);
	getParent().classList.remove(`${baseComponentSelector}-custom-parent`);
	progress2?.remove();
};
var isRendered = () => {
	return document.getElementById(baseComponentSelector) !== null;
};
var isDOM = (obj) => {
	if (typeof HTMLElement === "object") return obj instanceof HTMLElement;
	return obj && typeof obj === "object" && obj.nodeType === 1 && typeof obj.nodeName === "string";
};
function clamp(n, min, max) {
	if (n < min) return min;
	if (n > max) return max;
	return n;
}
var toBarPercentage = (n) => (-1 + n) * 100;
var queue4 = /* @__PURE__ */ (() => {
	const pending = [];
	const next = () => {
		const fn = pending.shift();
		if (fn) fn(next);
	};
	return (fn) => {
		pending.push(fn);
		if (pending.length === 1) next();
	};
})();
var injectCSS = (color) => {
	const element = document.createElement("style");
	element.textContent = `
    #${baseComponentSelector} {
      pointer-events: none;
    }

    #${baseComponentSelector} .bar {
      background: ${color};

      position: fixed;
      z-index: 1031;
      top: 0;
      left: 0;

      width: 100%;
      height: 2px;
    }

    #${baseComponentSelector} .peg {
      display: block;
      position: absolute;
      right: 0px;
      width: 100px;
      height: 100%;
      box-shadow: 0 0 10px ${color}, 0 0 5px ${color};
      opacity: 1.0;

      transform: rotate(3deg) translate(0px, -4px);
    }

    #${baseComponentSelector} .spinner {
      display: block;
      position: fixed;
      z-index: 1031;
      top: 15px;
      right: 15px;
    }

    #${baseComponentSelector} .spinner-icon {
      width: 18px;
      height: 18px;
      box-sizing: border-box;

      border: solid 2px transparent;
      border-top-color: ${color};
      border-left-color: ${color};
      border-radius: 50%;

      animation: ${baseComponentSelector}-spinner 400ms linear infinite;
    }

    .${baseComponentSelector}-custom-parent {
      overflow: hidden;
      position: relative;
    }

    .${baseComponentSelector}-custom-parent #${baseComponentSelector} .spinner,
    .${baseComponentSelector}-custom-parent #${baseComponentSelector} .bar {
      position: absolute;
    }

    @keyframes ${baseComponentSelector}-spinner {
      0%   { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  `;
	document.head.appendChild(element);
};
var show = () => {
	if (progress2) progress2.style.display = "";
};
var hide = () => {
	if (progress2) progress2.style.display = "none";
};
var progress_component_default = {
	configure,
	isStarted,
	done,
	set: set5,
	remove,
	start,
	status,
	show,
	hide
};
var Progress = class {
	constructor() {
		this.hideCount = 0;
	}
	start() {
		progress_component_default.start();
	}
	reveal(force = false) {
		this.hideCount = Math.max(0, this.hideCount - 1);
		if (force || this.hideCount === 0) progress_component_default.show();
	}
	hide() {
		this.hideCount++;
		progress_component_default.hide();
	}
	set(status2) {
		progress_component_default.set(Math.max(0, Math.min(1, status2)));
	}
	finish() {
		progress_component_default.done();
	}
	reset() {
		progress_component_default.set(0);
	}
	remove() {
		progress_component_default.done();
		progress_component_default.remove();
	}
	isStarted() {
		return progress_component_default.isStarted();
	}
	getStatus() {
		return progress_component_default.status;
	}
};
var progress = new Progress();
progress.reveal;
progress.hide;
function addEventListeners(delay) {
	document.addEventListener("inertia:start", (e) => handleStartEvent(e, delay));
	document.addEventListener("inertia:progress", handleProgressEvent);
}
function handleStartEvent(event, delay) {
	if (!event.detail.visit.showProgress) progress.hide();
	const timeout = setTimeout(() => progress.start(), delay);
	document.addEventListener("inertia:finish", (e) => finish(e, timeout), { once: true });
}
function handleProgressEvent(event) {
	if (progress.isStarted() && event.detail.progress?.percentage) progress.set(Math.max(progress.getStatus(), event.detail.progress.percentage / 100 * .9));
}
function finish(event, timeout) {
	clearTimeout(timeout);
	if (!progress.isStarted()) return;
	if (event.detail.visit.completed) progress.finish();
	else if (event.detail.visit.interrupted) progress.reset();
	else if (event.detail.visit.cancelled) progress.remove();
}
function setupProgress({ delay = 250, color = "#29d", includeCSS = true, showSpinner = false } = {}) {
	addEventListeners(delay);
	progress_component_default.configure({
		showSpinner,
		includeCSS,
		color
	});
}
var FormComponentResetSymbol = /* @__PURE__ */ Symbol("FormComponentReset");
function isFormElement(element) {
	return element instanceof HTMLInputElement || element instanceof HTMLSelectElement || element instanceof HTMLTextAreaElement;
}
function resetInputElement(input, defaultValues) {
	const oldValue = input.value;
	const oldChecked = input.checked;
	switch (input.type.toLowerCase()) {
		case "checkbox":
			input.checked = defaultValues.includes(input.value);
			break;
		case "radio":
			input.checked = defaultValues[0] === input.value;
			break;
		case "file":
			input.value = "";
			break;
		case "button":
		case "submit":
		case "reset":
		case "image": break;
		default: input.value = defaultValues[0] !== null && defaultValues[0] !== void 0 ? String(defaultValues[0]) : "";
	}
	return input.value !== oldValue || input.checked !== oldChecked;
}
function resetSelectElement(select, defaultValues) {
	const oldValue = select.value;
	const oldSelectedOptions = Array.from(select.selectedOptions).map((opt) => opt.value);
	if (select.multiple) {
		const defaultStrings = defaultValues.map((value) => String(value));
		Array.from(select.options).forEach((option) => {
			option.selected = defaultStrings.includes(option.value);
		});
	} else select.value = defaultValues[0] !== void 0 ? String(defaultValues[0]) : "";
	const newSelectedOptions = Array.from(select.selectedOptions).map((opt) => opt.value);
	return select.multiple ? JSON.stringify(oldSelectedOptions.sort()) !== JSON.stringify(newSelectedOptions.sort()) : select.value !== oldValue;
}
function resetFormElement(element, defaultValues) {
	if (element.disabled) {
		if (element instanceof HTMLInputElement) {
			const oldValue = element.value;
			const oldChecked = element.checked;
			switch (element.type.toLowerCase()) {
				case "checkbox":
				case "radio":
					element.checked = element.defaultChecked;
					return element.checked !== oldChecked;
				case "file":
					element.value = "";
					return oldValue !== "";
				case "button":
				case "submit":
				case "reset":
				case "image": return false;
				default:
					element.value = element.defaultValue;
					return element.value !== oldValue;
			}
		} else if (element instanceof HTMLSelectElement) {
			const oldSelectedOptions = Array.from(element.selectedOptions).map((opt) => opt.value);
			Array.from(element.options).forEach((option) => {
				option.selected = option.defaultSelected;
			});
			const newSelectedOptions = Array.from(element.selectedOptions).map((opt) => opt.value);
			return JSON.stringify(oldSelectedOptions.sort()) !== JSON.stringify(newSelectedOptions.sort());
		} else if (element instanceof HTMLTextAreaElement) {
			const oldValue = element.value;
			element.value = element.defaultValue;
			return element.value !== oldValue;
		}
		return false;
	}
	if (element instanceof HTMLInputElement) return resetInputElement(element, defaultValues);
	else if (element instanceof HTMLSelectElement) return resetSelectElement(element, defaultValues);
	else if (element instanceof HTMLTextAreaElement) {
		const oldValue = element.value;
		element.value = defaultValues[0] !== void 0 ? String(defaultValues[0]) : "";
		return element.value !== oldValue;
	}
	return false;
}
function resetFieldElements(elements, defaultValues) {
	let hasChanged = false;
	if (elements instanceof RadioNodeList || elements instanceof HTMLCollection) Array.from(elements).forEach((node, index) => {
		if (node instanceof Element && isFormElement(node)) {
			if (node instanceof HTMLInputElement && ["checkbox", "radio"].includes(node.type.toLowerCase())) {
				if (resetFormElement(node, defaultValues)) hasChanged = true;
			} else if (resetFormElement(node, defaultValues[index] !== void 0 ? [defaultValues[index]] : [defaultValues[0] ?? null].filter(Boolean))) hasChanged = true;
		}
	});
	else if (isFormElement(elements)) hasChanged = resetFormElement(elements, defaultValues);
	return hasChanged;
}
function resetFormFields(formElement, defaults, fieldNames) {
	if (!formElement) return;
	const resetEntireForm = !fieldNames || fieldNames.length === 0;
	if (resetEntireForm) {
		const formData = new FormData(formElement);
		const formElementNames = Array.from(formElement.elements).map((el) => isFormElement(el) ? el.name : "").filter(Boolean);
		fieldNames = [.../* @__PURE__ */ new Set([
			...defaults.keys(),
			...formData.keys(),
			...formElementNames
		])];
	}
	let hasChanged = false;
	fieldNames.forEach((fieldName) => {
		const elements = formElement.elements.namedItem(fieldName);
		if (elements) {
			if (resetFieldElements(elements, defaults.getAll(fieldName))) hasChanged = true;
		}
	});
	if (hasChanged && resetEntireForm) formElement.dispatchEvent(new CustomEvent("reset", {
		bubbles: true,
		cancelable: true,
		detail: { [FormComponentResetSymbol]: true }
	}));
}
var router = new Router();
/* NProgress, (c) 2013, 2014 Rico Sta. Cruz - http://ricostacruz.com/nprogress
* @license MIT */
//#endregion
//#region node_modules/laravel-precognition/dist/client.js
/**
* The configured axios client.
*/
var axiosClient = axios.create();
/**
* The request fingerprint resolver.
*/
var requestFingerprintResolver = (config, axios) => `${config.method}:${config.baseURL ?? axios.defaults.baseURL ?? ""}${config.url}`;
/**
* The precognition success resolver.
*/
var successResolver = (response) => response.status === 204 && response.headers["precognition-success"] === "true";
/**
* The abort controller cache.
*/
var abortControllers = {};
/**
* The precognitive HTTP client instance.
*/
var client = {
	get: (url, data = {}, config = {}) => request(mergeConfig("get", url, data, config)),
	post: (url, data = {}, config = {}) => request(mergeConfig("post", url, data, config)),
	patch: (url, data = {}, config = {}) => request(mergeConfig("patch", url, data, config)),
	put: (url, data = {}, config = {}) => request(mergeConfig("put", url, data, config)),
	delete: (url, data = {}, config = {}) => request(mergeConfig("delete", url, data, config)),
	use(axios) {
		axiosClient = axios;
		return client;
	},
	axios() {
		return axiosClient;
	},
	fingerprintRequestsUsing(callback) {
		requestFingerprintResolver = callback === null ? () => null : callback;
		return client;
	},
	determineSuccessUsing(callback) {
		successResolver = callback;
		return client;
	}
};
/**
* Merge the client specified arguments with the provided configuration.
*/
var mergeConfig = (method, url, data, config) => ({
	url,
	method,
	...config,
	...["get", "delete"].includes(method) ? { params: merge({}, data, config?.params) } : { data: merge({}, data, config?.data) }
});
/**
* Send and handle a new request.
*/
var request = (userConfig = {}) => {
	const config = [
		resolveConfig,
		abortMatchingRequests,
		refreshAbortController
	].reduce((config, callback) => callback(config), userConfig);
	if ((config.onBefore ?? (() => true))() === false) return Promise.resolve(null);
	(config.onStart ?? (() => null))();
	return axiosClient.request(config).then(async (response) => {
		if (config.precognitive) validatePrecognitionResponse(response);
		const status = response.status;
		let payload = response;
		if (config.precognitive && config.onPrecognitionSuccess && successResolver(payload)) payload = await Promise.resolve(config.onPrecognitionSuccess(payload) ?? payload);
		if (config.onSuccess && isSuccess(status)) payload = await Promise.resolve(config.onSuccess(payload) ?? payload);
		return (resolveStatusHandler(config, status) ?? ((response) => response))(payload) ?? payload;
	}, (error) => {
		if (isNotServerGeneratedError(error)) return Promise.reject(error);
		if (config.precognitive) validatePrecognitionResponse(error.response);
		return (resolveStatusHandler(config, error.response.status) ?? ((_, error) => Promise.reject(error)))(error.response, error);
	}).finally(config.onFinish ?? (() => null));
};
/**
* Resolve the configuration.
*/
var resolveConfig = (config) => {
	const only = config.only ?? config.validate;
	return {
		...config,
		timeout: config.timeout ?? axiosClient.defaults["timeout"] ?? 3e4,
		precognitive: config.precognitive !== false,
		fingerprint: typeof config.fingerprint === "undefined" ? requestFingerprintResolver(config, axiosClient) : config.fingerprint,
		headers: {
			...config.headers,
			"Content-Type": resolveContentType(config),
			...config.precognitive !== false ? { Precognition: true } : {},
			...only ? { "Precognition-Validate-Only": Array.from(only).join() } : {}
		}
	};
};
/**
* Determine if the status is successful.
*/
var isSuccess = (status) => status >= 200 && status < 300;
/**
* Abort an existing request with the same configured fingerprint.
*/
var abortMatchingRequests = (config) => {
	if (typeof config.fingerprint !== "string") return config;
	abortControllers[config.fingerprint]?.abort();
	delete abortControllers[config.fingerprint];
	return config;
};
/**
* Create and configure the abort controller for a new request.
*/
var refreshAbortController = (config) => {
	if (typeof config.fingerprint !== "string" || config.signal || config.cancelToken || !config.precognitive) return config;
	abortControllers[config.fingerprint] = new AbortController();
	return {
		...config,
		signal: abortControllers[config.fingerprint].signal
	};
};
/**
* Ensure that the response is a Precognition response.
*/
var validatePrecognitionResponse = (response) => {
	if (response.headers?.precognition !== "true") throw Error("Did not receive a Precognition response. Ensure you have the Precognition middleware in place for the route.");
};
/**
* Determine if the error was not triggered by a server response.
*/
var isNotServerGeneratedError = (error) => {
	return !isAxiosError(error) || typeof error.response?.status !== "number" || isCancel(error);
};
/**
* Resolve the handler for the given HTTP response status.
*/
var resolveStatusHandler = (config, code) => ({
	401: config.onUnauthorized,
	403: config.onForbidden,
	404: config.onNotFound,
	409: config.onConflict,
	422: config.onValidationError,
	423: config.onLocked
})[code];
/**
* Resolve the request's "Content-Type" header.
*/
var resolveContentType = (config) => config.headers?.["Content-Type"] ?? config.headers?.["Content-type"] ?? config.headers?.["content-type"] ?? (hasFiles(config.data) ? "multipart/form-data" : "application/json");
/**
* Determine if the payload has any files.
*
* @see https://github.com/inertiajs/inertia/blob/master/packages/core/src/files.ts
*/
var hasFiles = (data) => isFile(data) || typeof data === "object" && data !== null && Object.values(data).some((value) => hasFiles(value));
/**
* Determine if the value is a file.
*/
var isFile = (value) => typeof File !== "undefined" && value instanceof File || value instanceof Blob || typeof FileList !== "undefined" && value instanceof FileList && value.length > 0;
//#endregion
//#region node_modules/laravel-precognition/dist/validator.js
/**
* Expand a wildcard path to concrete paths using the given data.
*
* Examples:
* - 'users.*' with {users: [{name: 'A'}, {name: 'B'}]} => ['users.0', 'users.1']
* - 'users.*.name' with {users: [{name: 'A'}, {name: 'B'}]} => ['users.0.name', 'users.1.name']
* - 'author.*' with {author: {name: 'John', bio: 'Dev'}} => ['author.name', 'author.bio']
*/
var expandWildcardPaths = (pattern, data) => {
	if (!pattern.includes("*")) return [pattern];
	const parts = pattern.split(".");
	let paths = [""];
	for (const part of parts) if (part === "*") {
		const expanded = [];
		for (const path of paths) {
			const value = path ? get(data, path) : data;
			if (Array.isArray(value)) for (let index = 0; index < value.length; index++) expanded.push(path ? `${path}.${index}` : String(index));
			else if (value !== null && typeof value === "object") for (const key of Object.keys(value)) expanded.push(path ? `${path}.${key}` : key);
		}
		paths = expanded;
	} else paths = paths.map((path) => path ? `${path}.${part}` : part);
	return paths;
};
/**
* Determine if a key matches the given pattern.
*/
var keyMatchesPattern = (key, pattern) => {
	if (!pattern.includes("*")) return key === pattern;
	return new RegExp("^" + pattern.replace(/\./g, "\\.").replace(/\*/g, "[^.]+") + "$").test(key);
};
/**
* Omit entries from an object whose keys match the given patterns.
*/
var omitByPattern = (obj, patterns) => {
	return Object.fromEntries(Object.entries(obj).filter(([key]) => {
		return !patterns.some((pattern) => keyMatchesPattern(key, pattern));
	}));
};
var createValidator = (callback, initialData = {}) => {
	/**
	* Event listener state.
	*/
	const listeners = {
		errorsChanged: [],
		touchedChanged: [],
		validatingChanged: [],
		validatedChanged: []
	};
	/**
	* Validate files state.
	*/
	let validateFiles = false;
	/**
	* Processing validation state.
	*/
	let validating = false;
	/**
	* Set the validating inputs.
	*
	* Returns an array of listeners that should be invoked once all state
	* changes have taken place.
	*/
	const setValidating = (value) => {
		if (value !== validating) {
			validating = value;
			return listeners.validatingChanged;
		}
		return [];
	};
	/**
	* Inputs that have been validated.
	*/
	let validated = [];
	/**
	* Set the validated inputs.
	*
	* Returns an array of listeners that should be invoked once all state
	* changes have taken place.
	*/
	const setValidated = (value) => {
		const uniqueNames = [...new Set(value)];
		if (validated.length !== uniqueNames.length || !uniqueNames.every((name) => validated.includes(name))) {
			validated = uniqueNames;
			return listeners.validatedChanged;
		}
		return [];
	};
	/**
	* Valid validation state.
	*/
	const valid = () => validated.filter((name) => typeof errors[name] === "undefined");
	/**
	* Touched input state.
	*/
	let touched = [];
	/**
	* Set the touched inputs.
	*
	* Returns an array of listeners that should be invoked once all state
	* changes have taken place.
	*/
	const setTouched = (value) => {
		const uniqueNames = [...new Set(value)];
		if (touched.length !== uniqueNames.length || !uniqueNames.every((name) => touched.includes(name))) {
			touched = uniqueNames;
			return listeners.touchedChanged;
		}
		return [];
	};
	/**
	* Validation errors state.
	*/
	let errors = {};
	/**
	* Set the input errors.
	*
	* Returns an array of listeners that should be invoked once all state
	* changes have taken place.
	*/
	const setErrors = (value) => {
		const prepared = toValidationErrors(value);
		if (!isEqual(errors, prepared)) {
			errors = prepared;
			return listeners.errorsChanged;
		}
		return [];
	};
	/**
	* Forget the given input's errors.
	*
	* Returns an array of listeners that should be invoked once all state
	* changes have taken place.
	*/
	const forgetError = (name) => {
		const newErrors = { ...errors };
		delete newErrors[resolveName(name)];
		return setErrors(newErrors);
	};
	/**
	* Has errors state.
	*/
	const hasErrors = () => Object.keys(errors).length > 0;
	/**
	* Debouncing timeout state.
	*/
	let debounceTimeoutDuration = 1500;
	const setDebounceTimeout = (value) => {
		debounceTimeoutDuration = value;
		validator.cancel();
		validator = createValidator();
	};
	/**
	* The old data.
	*/
	let oldData = initialData;
	/**
	* The data currently being validated.
	*/
	let validatingData = null;
	/**
	* The old touched.
	*/
	let oldTouched = [];
	/**
	* The touched currently being validated.
	*/
	let validatingTouched = null;
	/**
	* Create a debounced validation callback.
	*/
	const createValidator = () => debounce$1((instanceConfig) => {
		callback({
			get: (url, data = {}, globalConfig = {}) => client.get(url, parseData(data), resolveConfig(globalConfig, instanceConfig, data)),
			post: (url, data = {}, globalConfig = {}) => client.post(url, parseData(data), resolveConfig(globalConfig, instanceConfig, data)),
			patch: (url, data = {}, globalConfig = {}) => client.patch(url, parseData(data), resolveConfig(globalConfig, instanceConfig, data)),
			put: (url, data = {}, globalConfig = {}) => client.put(url, parseData(data), resolveConfig(globalConfig, instanceConfig, data)),
			delete: (url, data = {}, globalConfig = {}) => client.delete(url, parseData(data), resolveConfig(globalConfig, instanceConfig, data))
		}).catch((error) => {
			if (isCancel(error)) return null;
			if (isAxiosError(error) && error.response?.status === 422) return null;
			return Promise.reject(error);
		});
	}, debounceTimeoutDuration, {
		leading: true,
		trailing: true
	});
	/**
	* Validator state.
	*/
	let validator = createValidator();
	/**
	* Resolve the configuration.
	*/
	const resolveConfig = (globalConfig, instanceConfig, data = {}) => {
		const config = {
			...globalConfig,
			...instanceConfig
		};
		const only = Array.from(config.only ?? config.validate ?? touched);
		return {
			...instanceConfig,
			...mergeConfig$1(globalConfig, instanceConfig),
			only,
			timeout: config.timeout ?? 5e3,
			onValidationError: (response, axiosError) => {
				[...setValidated([...validated, ...only]), ...setErrors(merge(omitByPattern({ ...errors }, only), response.data.errors))].forEach((listener) => listener());
				return config.onValidationError ? config.onValidationError(response, axiosError) : Promise.reject(axiosError);
			},
			onSuccess: (response) => {
				setValidated([...validated, ...only]).forEach((listener) => listener());
				return config.onSuccess ? config.onSuccess(response) : response;
			},
			onPrecognitionSuccess: (response) => {
				[...setValidated([...validated, ...only]), ...setErrors(omitByPattern({ ...errors }, only))].forEach((listener) => listener());
				return config.onPrecognitionSuccess ? config.onPrecognitionSuccess(response) : response;
			},
			onBefore: () => {
				const hasWildcards = touched.some((name) => name.includes("*"));
				const expandedTouched = hasWildcards ? [...new Set(touched.flatMap((name) => expandWildcardPaths(name, data)))] : touched;
				if (config.onBeforeValidation && config.onBeforeValidation({
					data,
					touched: expandedTouched
				}, {
					data: oldData,
					touched: oldTouched
				}) === false) return false;
				if ((config.onBefore || (() => true))() === false) return false;
				if (hasWildcards) setTouched(expandedTouched).forEach((listener) => listener());
				validatingTouched = touched;
				validatingData = data;
				return true;
			},
			onStart: () => {
				setValidating(true).forEach((listener) => listener());
				(config.onStart ?? (() => null))();
			},
			onFinish: () => {
				setValidating(false).forEach((listener) => listener());
				oldTouched = validatingTouched;
				oldData = validatingData;
				validatingTouched = validatingData = null;
				(config.onFinish ?? (() => null))();
			}
		};
	};
	/**
	* Validate the given input.
	*/
	const validate = (name, value, config) => {
		if (typeof name === "undefined") {
			const only = Array.from(config?.only ?? config?.validate ?? []);
			setTouched([...touched, ...only]).forEach((listener) => listener());
			validator(config ?? {});
			return;
		}
		if (isFile(value) && !validateFiles) {
			console.warn("Precognition file validation is not active. Call the \"validateFiles\" function on your form to enable it.");
			return;
		}
		name = resolveName(name);
		if (name.includes("*") || get(oldData, name) !== value) {
			setTouched([name, ...touched]).forEach((listener) => listener());
			validator(config ?? {});
		}
	};
	/**
	* Parse the validated data.
	*/
	const parseData = (data) => validateFiles === false ? forgetFiles(data) : data;
	/**
	* The form validator instance.
	*/
	const form = {
		touched: () => touched,
		validate(name, value, config) {
			if (typeof name === "object" && !("target" in name)) {
				config = name;
				name = value = void 0;
			}
			validate(name, value, config);
			return form;
		},
		touch(input) {
			const inputs = Array.isArray(input) ? input : [resolveName(input)];
			setTouched([...touched, ...inputs]).forEach((listener) => listener());
			return form;
		},
		validating: () => validating,
		valid,
		errors: () => errors,
		hasErrors,
		setErrors(value) {
			setErrors(value).forEach((listener) => listener());
			return form;
		},
		forgetError(name) {
			forgetError(name).forEach((listener) => listener());
			return form;
		},
		defaults(data) {
			initialData = data;
			oldData = data;
			return form;
		},
		reset(...names) {
			if (names.length === 0) setTouched([]).forEach((listener) => listener());
			else {
				const newTouched = [...touched];
				names.forEach((name) => {
					if (newTouched.includes(name)) newTouched.splice(newTouched.indexOf(name), 1);
					set(oldData, name, get(initialData, name));
				});
				setTouched(newTouched).forEach((listener) => listener());
			}
			return form;
		},
		setTimeout(value) {
			setDebounceTimeout(value);
			return form;
		},
		on(event, callback) {
			listeners[event].push(callback);
			return form;
		},
		validateFiles() {
			validateFiles = true;
			return form;
		},
		withoutFileValidation() {
			validateFiles = false;
			return form;
		}
	};
	return form;
};
/**
* Normalise the validation errors as Inertia formatted errors.
*/
var toSimpleValidationErrors = (errors) => {
	return Object.keys(errors).reduce((carry, key) => ({
		...carry,
		[key]: Array.isArray(errors[key]) ? errors[key][0] : errors[key]
	}), {});
};
/**
* Normalise the validation errors as Laravel formatted errors.
*/
var toValidationErrors = (errors) => {
	return Object.keys(errors).reduce((carry, key) => ({
		...carry,
		[key]: typeof errors[key] === "string" ? [errors[key]] : errors[key]
	}), {});
};
/**
* Resolve the input's "name" attribute.
*/
var resolveName = (name) => {
	return typeof name !== "string" ? name.target.name : name;
};
/**
* Forget any files from the payload.
*/
var forgetFiles = (data) => {
	const newData = { ...data };
	Object.keys(newData).forEach((name) => {
		const value = newData[name];
		if (value === null) return;
		if (isFile(value)) {
			delete newData[name];
			return;
		}
		if (Array.isArray(value)) {
			newData[name] = Object.values(forgetFiles({ ...value }));
			return;
		}
		if (typeof value === "object") {
			newData[name] = forgetFiles(newData[name]);
			return;
		}
	});
	return newData;
};
//#endregion
//#region node_modules/@inertiajs/vue3/dist/index.esm.js
var remember_default = { created() {
	if (!this.$options.remember) return;
	if (Array.isArray(this.$options.remember)) this.$options.remember = { data: this.$options.remember };
	if (typeof this.$options.remember === "string") this.$options.remember = { data: [this.$options.remember] };
	if (typeof this.$options.remember.data === "string") this.$options.remember = { data: [this.$options.remember.data] };
	const rememberKey = this.$options.remember.key instanceof Function ? this.$options.remember.key.call(this) : this.$options.remember.key;
	const restored = router.restore(rememberKey);
	const rememberable = this.$options.remember.data.filter((key2) => {
		return !(this[key2] !== null && typeof this[key2] === "object" && this[key2].__rememberable === false);
	});
	const hasCallbacks = (key2) => {
		return this[key2] !== null && typeof this[key2] === "object" && typeof this[key2].__remember === "function" && typeof this[key2].__restore === "function";
	};
	rememberable.forEach((key2) => {
		if (this[key2] !== void 0 && restored !== void 0 && restored[key2] !== void 0) hasCallbacks(key2) ? this[key2].__restore(restored[key2]) : this[key2] = restored[key2];
		this.$watch(key2, () => {
			router.remember(rememberable.reduce((data, key3) => ({
				...data,
				[key3]: cloneDeep(hasCallbacks(key3) ? this[key3].__remember() : this[key3])
			}), {}), rememberKey);
		}, {
			immediate: true,
			deep: true
		});
	});
} };
var reservedFormKeys = null;
var bootstrapping = false;
function validateFormDataKeys(data) {
	if (bootstrapping) return;
	if (reservedFormKeys === null) {
		bootstrapping = true;
		reservedFormKeys = new Set(Object.keys(useForm({})));
		bootstrapping = false;
	}
	const conflicts = Object.keys(data).filter((key2) => reservedFormKeys.has(key2));
	if (conflicts.length > 0) console.error(`[Inertia] useForm() data contains field(s) that conflict with form properties: ${conflicts.map((k) => `"${k}"`).join(", ")}. These fields will be overwritten by form methods/properties. Please rename these fields.`);
}
function useForm(...args) {
	let { rememberKey, data, precognitionEndpoint } = UseFormUtils.parseUseFormArguments(...args);
	const restored = rememberKey ? router.restore(rememberKey) : null;
	let defaults = typeof data === "function" ? cloneDeep(data()) : cloneDeep(data);
	validateFormDataKeys(defaults);
	let cancelToken = null;
	let recentlySuccessfulTimeoutId;
	let transform = (data2) => data2;
	let validatorRef = null;
	let rememberExcludeKeys = [];
	let defaultsCalledInOnSuccess = false;
	const typedForm = reactive({
		...restored ? restored.data : cloneDeep(defaults),
		isDirty: false,
		errors: restored ? restored.errors : {},
		hasErrors: false,
		processing: false,
		progress: null,
		wasSuccessful: false,
		recentlySuccessful: false,
		withPrecognition(...args2) {
			precognitionEndpoint = UseFormUtils.createWayfinderCallback(...args2);
			const formWithPrecognition = this;
			let withAllErrors = null;
			const validator = createValidator((client) => {
				const { method, url } = precognitionEndpoint();
				const transformedData = cloneDeep(transform(this.data()));
				return client[method](url, transformedData);
			}, cloneDeep(defaults));
			validatorRef = validator;
			validator.on("validatingChanged", () => {
				formWithPrecognition.validating = validator.validating();
			}).on("validatedChanged", () => {
				formWithPrecognition.__valid = validator.valid();
			}).on("touchedChanged", () => {
				formWithPrecognition.__touched = validator.touched();
			}).on("errorsChanged", () => {
				const validationErrors = withAllErrors ?? config.get("form.withAllErrors") ? validator.errors() : toSimpleValidationErrors(validator.errors());
				this.errors = {};
				this.setError(validationErrors);
				formWithPrecognition.__valid = validator.valid();
			});
			const tap = (value, callback) => {
				callback(value);
				return value;
			};
			Object.assign(formWithPrecognition, {
				__touched: [],
				__valid: [],
				validating: false,
				validator: () => validator,
				withAllErrors: () => tap(formWithPrecognition, () => withAllErrors = true),
				valid: (field) => formWithPrecognition.__valid.includes(field),
				invalid: (field) => field in this.errors,
				setValidationTimeout: (duration) => tap(formWithPrecognition, () => validator.setTimeout(duration)),
				validateFiles: () => tap(formWithPrecognition, () => validator.validateFiles()),
				withoutFileValidation: () => tap(formWithPrecognition, () => validator.withoutFileValidation()),
				touch: (field, ...fields) => {
					if (Array.isArray(field)) validator.touch(field);
					else if (typeof field === "string") validator.touch([field, ...fields]);
					else validator.touch(field);
					return formWithPrecognition;
				},
				touched: (field) => typeof field === "string" ? formWithPrecognition.__touched.includes(field) : formWithPrecognition.__touched.length > 0,
				validate: (field, config3) => {
					if (typeof field === "object" && !("target" in field)) {
						config3 = field;
						field = void 0;
					}
					if (field === void 0) validator.validate(config3);
					else {
						const fieldName = resolveName(field);
						const transformedData = transform(this.data());
						validator.validate(fieldName, get(transformedData, fieldName), config3);
					}
					return formWithPrecognition;
				},
				setErrors: (errors) => tap(formWithPrecognition, () => this.setError(errors)),
				forgetError: (field) => tap(formWithPrecognition, () => this.clearErrors(resolveName(field)))
			});
			return formWithPrecognition;
		},
		data() {
			return Object.keys(defaults).reduce((carry, key2) => {
				return set(carry, key2, get(this, key2));
			}, {});
		},
		transform(callback) {
			transform = callback;
			return this;
		},
		defaults(fieldOrFields, maybeValue) {
			if (typeof data === "function") throw new Error("You cannot call `defaults()` when using a function to define your form data.");
			defaultsCalledInOnSuccess = true;
			if (typeof fieldOrFields === "undefined") {
				defaults = cloneDeep(this.data());
				this.isDirty = false;
			} else defaults = typeof fieldOrFields === "string" ? set(cloneDeep(defaults), fieldOrFields, maybeValue) : Object.assign({}, cloneDeep(defaults), fieldOrFields);
			validatorRef?.defaults(defaults);
			return this;
		},
		reset(...fields) {
			const resolvedData = typeof data === "function" ? cloneDeep(data()) : cloneDeep(defaults);
			const clonedData = cloneDeep(resolvedData);
			if (fields.length === 0) {
				defaults = clonedData;
				Object.assign(this, resolvedData);
			} else fields.filter((key2) => has(clonedData, key2)).forEach((key2) => {
				set(defaults, key2, get(clonedData, key2));
				set(this, key2, get(resolvedData, key2));
			});
			validatorRef?.reset(...fields);
			return this;
		},
		setError(fieldOrFields, maybeValue) {
			const errors = typeof fieldOrFields === "string" ? { [fieldOrFields]: maybeValue } : fieldOrFields;
			Object.assign(this.errors, errors);
			this.hasErrors = Object.keys(this.errors).length > 0;
			validatorRef?.setErrors(errors);
			return this;
		},
		clearErrors(...fields) {
			this.errors = Object.keys(this.errors).reduce((carry, field) => ({
				...carry,
				...fields.length > 0 && !fields.includes(field) ? { [field]: this.errors[field] } : {}
			}), {});
			this.hasErrors = Object.keys(this.errors).length > 0;
			if (validatorRef) if (fields.length === 0) validatorRef.setErrors({});
			else fields.forEach(validatorRef.forgetError);
			return this;
		},
		resetAndClearErrors(...fields) {
			this.reset(...fields);
			this.clearErrors(...fields);
			return this;
		},
		submit(...args2) {
			const { method, url, options } = UseFormUtils.parseSubmitArguments(args2, precognitionEndpoint);
			defaultsCalledInOnSuccess = false;
			const _options = {
				...options,
				onCancelToken: (token) => {
					cancelToken = token;
					if (options.onCancelToken) return options.onCancelToken(token);
				},
				onBefore: (visit) => {
					this.wasSuccessful = false;
					this.recentlySuccessful = false;
					clearTimeout(recentlySuccessfulTimeoutId);
					if (options.onBefore) return options.onBefore(visit);
				},
				onStart: (visit) => {
					this.processing = true;
					if (options.onStart) return options.onStart(visit);
				},
				onProgress: (event) => {
					this.progress = event ?? null;
					if (options.onProgress) return options.onProgress(event);
				},
				onSuccess: async (page2) => {
					this.processing = false;
					this.progress = null;
					this.clearErrors();
					this.wasSuccessful = true;
					this.recentlySuccessful = true;
					recentlySuccessfulTimeoutId = setTimeout(() => this.recentlySuccessful = false, config.get("form.recentlySuccessfulDuration"));
					const onSuccess = options.onSuccess ? await options.onSuccess(page2) : null;
					if (!defaultsCalledInOnSuccess) {
						defaults = cloneDeep(this.data());
						this.isDirty = false;
					}
					return onSuccess;
				},
				onError: (errors) => {
					this.processing = false;
					this.progress = null;
					this.clearErrors().setError(errors);
					if (options.onError) return options.onError(errors);
				},
				onCancel: () => {
					this.processing = false;
					this.progress = null;
					if (options.onCancel) return options.onCancel();
				},
				onFinish: (visit) => {
					this.processing = false;
					this.progress = null;
					cancelToken = null;
					if (options.onFinish) return options.onFinish(visit);
				}
			};
			const transformedData = transform(this.data());
			if (method === "delete") router.delete(url, {
				..._options,
				data: transformedData
			});
			else router[method](url, transformedData, _options);
		},
		get(url, options) {
			this.submit("get", url, options);
		},
		post(url, options) {
			this.submit("post", url, options);
		},
		put(url, options) {
			this.submit("put", url, options);
		},
		patch(url, options) {
			this.submit("patch", url, options);
		},
		delete(url, options) {
			this.submit("delete", url, options);
		},
		cancel() {
			if (cancelToken) cancelToken.cancel();
		},
		dontRemember(...keys) {
			rememberExcludeKeys = keys;
			return this;
		},
		__rememberable: rememberKey === null,
		__remember() {
			const data2 = this.data();
			if (rememberExcludeKeys.length > 0) {
				const filtered = { ...data2 };
				rememberExcludeKeys.forEach((k) => delete filtered[k]);
				return {
					data: filtered,
					errors: this.errors
				};
			}
			return {
				data: data2,
				errors: this.errors
			};
		},
		__restore(restored2) {
			Object.assign(this, restored2.data);
			this.setError(restored2.errors);
		}
	});
	watch(typedForm, (newValue) => {
		typedForm.isDirty = !isEqual(typedForm.data(), defaults);
		const storedData = router.restore(rememberKey);
		const newData = cloneDeep(newValue.__remember());
		if (rememberKey && !isEqual(storedData, newData)) router.remember(newData, rememberKey);
	}, {
		immediate: true,
		deep: true
	});
	return precognitionEndpoint ? typedForm.withPrecognition(precognitionEndpoint) : typedForm;
}
var component = ref(void 0);
var page = ref();
var layout = shallowRef(null);
var key = ref(void 0);
var headManager;
var app_default = defineComponent({
	name: "Inertia",
	props: {
		initialPage: {
			type: Object,
			required: true
		},
		initialComponent: {
			type: Object,
			required: false
		},
		resolveComponent: {
			type: Function,
			required: false
		},
		titleCallback: {
			type: Function,
			required: false,
			default: (title) => title
		},
		onHeadUpdate: {
			type: Function,
			required: false,
			default: () => () => {}
		}
	},
	setup({ initialPage, initialComponent, resolveComponent, titleCallback, onHeadUpdate }) {
		component.value = initialComponent ? markRaw(initialComponent) : void 0;
		page.value = {
			...initialPage,
			flash: initialPage.flash ?? {}
		};
		key.value = void 0;
		const isServer = typeof window === "undefined";
		headManager = createHeadManager(isServer, titleCallback || ((title) => title), onHeadUpdate || (() => {}));
		if (!isServer) {
			router.init({
				initialPage,
				resolveComponent,
				swapComponent: async (options) => {
					component.value = markRaw(options.component);
					page.value = options.page;
					key.value = options.preserveState ? key.value : Date.now();
				},
				onFlash: (flash) => {
					page.value = {
						...page.value,
						flash
					};
				}
			});
			router.on("navigate", () => headManager.forceUpdate());
		}
		return () => {
			if (component.value) {
				component.value.inheritAttrs = !!component.value.inheritAttrs;
				const child = h(component.value, {
					...page.value.props,
					key: key.value
				});
				if (layout.value) {
					component.value.layout = layout.value;
					layout.value = null;
				}
				if (component.value.layout) {
					if (typeof component.value.layout === "function") return component.value.layout(h, child);
					return (Array.isArray(component.value.layout) ? component.value.layout : [component.value.layout]).concat(child).reverse().reduce((child2, layout2) => {
						layout2.inheritAttrs = !!layout2.inheritAttrs;
						return h(layout2, { ...page.value.props }, () => child2);
					});
				}
				return child;
			}
		};
	}
});
var plugin = { install(app) {
	router.form = useForm;
	Object.defineProperty(app.config.globalProperties, "$inertia", { get: () => router });
	Object.defineProperty(app.config.globalProperties, "$page", { get: () => page.value });
	Object.defineProperty(app.config.globalProperties, "$headManager", { get: () => headManager });
	app.mixin(remember_default);
} };
function usePage() {
	return reactive({
		props: computed(() => page.value?.props),
		url: computed(() => page.value?.url),
		component: computed(() => page.value?.component),
		version: computed(() => page.value?.version),
		clearHistory: computed(() => page.value?.clearHistory),
		deferredProps: computed(() => page.value?.deferredProps),
		mergeProps: computed(() => page.value?.mergeProps),
		prependProps: computed(() => page.value?.prependProps),
		deepMergeProps: computed(() => page.value?.deepMergeProps),
		matchPropsOn: computed(() => page.value?.matchPropsOn),
		rememberedState: computed(() => page.value?.rememberedState),
		encryptHistory: computed(() => page.value?.encryptHistory),
		scrollProps: computed(() => page.value?.scrollProps),
		flash: computed(() => page.value?.flash)
	});
}
async function createInertiaApp({ id = "app", resolve, setup, title, progress: progress2 = {}, page: page2, render, defaults = {} }) {
	config.replace(defaults);
	const isServer = typeof window === "undefined";
	const useScriptElementForInitialPage = config.get("future.useScriptElementForInitialPage");
	const initialPage = page2 || getInitialPageFromDOM(id, useScriptElementForInitialPage);
	const resolveComponent = (name) => Promise.resolve(resolve(name)).then((module) => module.default || module);
	let head = [];
	const vueApp = await Promise.all([resolveComponent(initialPage.component), router.decryptHistory().catch(() => {})]).then(([initialComponent]) => {
		const props = {
			initialPage,
			initialComponent,
			resolveComponent,
			titleCallback: title
		};
		if (isServer) return setup({
			el: null,
			App: app_default,
			props: {
				...props,
				onHeadUpdate: (elements) => head = elements
			},
			plugin
		});
		return setup({
			el: document.getElementById(id),
			App: app_default,
			props,
			plugin
		});
	});
	if (!isServer && progress2) setupProgress(progress2);
	if (isServer && render) {
		const element = () => {
			if (!useScriptElementForInitialPage) return h("div", {
				id,
				"data-page": JSON.stringify(initialPage),
				innerHTML: vueApp ? render(vueApp) : ""
			});
			return [h("script", {
				"data-page": id,
				type: "application/json",
				innerHTML: JSON.stringify(initialPage).replace(/\//g, "\\/")
			}), h("div", {
				id,
				innerHTML: vueApp ? render(vueApp) : ""
			})];
		};
		const body = await render(createSSRApp({ render: () => element() }));
		return {
			head,
			body
		};
	}
}
defineComponent({
	name: "Deferred",
	props: { data: {
		type: [String, Array],
		required: true
	} },
	render() {
		const keys = Array.isArray(this.$props.data) ? this.$props.data : [this.$props.data];
		if (!this.$slots.fallback) throw new Error("`<Deferred>` requires a `<template #fallback>` slot");
		return keys.every((key2) => this.$page.props[key2] !== void 0) ? this.$slots.default?.() : this.$slots.fallback();
	}
});
var noop = () => void 0;
var FormContextKey = /* @__PURE__ */ Symbol("InertiaFormContext");
var form_default = defineComponent({
	name: "Form",
	slots: Object,
	props: {
		action: {
			type: [String, Object],
			default: ""
		},
		method: {
			type: String,
			default: "get"
		},
		headers: {
			type: Object,
			default: () => ({})
		},
		queryStringArrayFormat: {
			type: String,
			default: "brackets"
		},
		errorBag: {
			type: [String, null],
			default: null
		},
		showProgress: {
			type: Boolean,
			default: true
		},
		transform: {
			type: Function,
			default: (data) => data
		},
		options: {
			type: Object,
			default: () => ({})
		},
		resetOnError: {
			type: [Boolean, Array],
			default: false
		},
		resetOnSuccess: {
			type: [Boolean, Array],
			default: false
		},
		setDefaultsOnSuccess: {
			type: Boolean,
			default: false
		},
		onCancelToken: {
			type: Function,
			default: noop
		},
		onBefore: {
			type: Function,
			default: noop
		},
		onStart: {
			type: Function,
			default: noop
		},
		onProgress: {
			type: Function,
			default: noop
		},
		onFinish: {
			type: Function,
			default: noop
		},
		onCancel: {
			type: Function,
			default: noop
		},
		onSuccess: {
			type: Function,
			default: noop
		},
		onError: {
			type: Function,
			default: noop
		},
		onSubmitComplete: {
			type: Function,
			default: noop
		},
		disableWhileProcessing: {
			type: Boolean,
			default: false
		},
		invalidateCacheTags: {
			type: [String, Array],
			default: () => []
		},
		validateFiles: {
			type: Boolean,
			default: false
		},
		validationTimeout: {
			type: Number,
			default: 1500
		},
		withAllErrors: {
			type: Boolean,
			default: null
		}
	},
	setup(props, { slots, attrs, expose }) {
		const getTransformedData = () => {
			const [_url, data] = getUrlAndData();
			return props.transform(data);
		};
		const form = useForm({}).withPrecognition(() => method.value, () => getUrlAndData()[0]).transform(getTransformedData).setValidationTimeout(props.validationTimeout);
		if (props.validateFiles) form.validateFiles();
		if (props.withAllErrors ?? config$1.get("form.withAllErrors")) form.withAllErrors();
		const formElement = ref();
		const method = computed(() => isUrlMethodPair(props.action) ? props.action.method : props.method.toLowerCase());
		const isDirty = ref(false);
		const defaultData = ref(new FormData());
		const onFormUpdate = (event) => {
			if (event.type === "reset" && event.detail?.[FormComponentResetSymbol]) event.preventDefault();
			isDirty.value = event.type === "reset" ? false : !isEqual(getData(), formDataToObject(defaultData.value));
		};
		const formEvents = [
			"input",
			"change",
			"reset"
		];
		onMounted(() => {
			defaultData.value = getFormData();
			form.defaults(getData());
			formEvents.forEach((e) => formElement.value.addEventListener(e, onFormUpdate));
		});
		watch(() => props.validateFiles, (value) => value ? form.validateFiles() : form.withoutFileValidation());
		watch(() => props.validationTimeout, (value) => form.setValidationTimeout(value));
		onBeforeUnmount(() => formEvents.forEach((e) => formElement.value?.removeEventListener(e, onFormUpdate)));
		const getFormData = (submitter) => new FormData(formElement.value, submitter);
		const getData = (submitter) => formDataToObject(getFormData(submitter));
		const getUrlAndData = (submitter) => {
			return mergeDataIntoQueryString(method.value, isUrlMethodPair(props.action) ? props.action.url : props.action, getData(submitter), props.queryStringArrayFormat);
		};
		const submit = (submitter) => {
			const [url, data] = getUrlAndData(submitter);
			if (submitter?.getAttribute("formtarget") === "_blank" && method.value === "get") {
				window.open(url, "_blank");
				return;
			}
			const maybeReset = (resetOption) => {
				if (!resetOption) return;
				if (resetOption === true) reset();
				else if (resetOption.length > 0) reset(...resetOption);
			};
			const submitOptions = {
				headers: props.headers,
				queryStringArrayFormat: props.queryStringArrayFormat,
				errorBag: props.errorBag,
				showProgress: props.showProgress,
				invalidateCacheTags: props.invalidateCacheTags,
				onCancelToken: props.onCancelToken,
				onBefore: props.onBefore,
				onStart: props.onStart,
				onProgress: props.onProgress,
				onFinish: props.onFinish,
				onCancel: props.onCancel,
				onSuccess: (...args) => {
					props.onSuccess?.(...args);
					props.onSubmitComplete?.(exposed);
					maybeReset(props.resetOnSuccess);
					if (props.setDefaultsOnSuccess === true) defaults();
				},
				onError: (...args) => {
					props.onError?.(...args);
					maybeReset(props.resetOnError);
				},
				...props.options
			};
			form.transform(() => props.transform(data)).submit(method.value, url, submitOptions);
			form.transform(getTransformedData);
		};
		const reset = (...fields) => {
			resetFormFields(formElement.value, defaultData.value, fields);
			form.reset(...fields);
		};
		const clearErrors = (...fields) => {
			form.clearErrors(...fields);
		};
		const resetAndClearErrors = (...fields) => {
			clearErrors(...fields);
			reset(...fields);
		};
		const defaults = () => {
			defaultData.value = getFormData();
			isDirty.value = false;
		};
		const exposed = {
			get errors() {
				return form.errors;
			},
			get hasErrors() {
				return form.hasErrors;
			},
			get processing() {
				return form.processing;
			},
			get progress() {
				return form.progress;
			},
			get wasSuccessful() {
				return form.wasSuccessful;
			},
			get recentlySuccessful() {
				return form.recentlySuccessful;
			},
			get validating() {
				return form.validating;
			},
			clearErrors,
			resetAndClearErrors,
			setError: (fieldOrFields, maybeValue) => form.setError(typeof fieldOrFields === "string" ? { [fieldOrFields]: maybeValue } : fieldOrFields),
			get isDirty() {
				return isDirty.value;
			},
			reset,
			submit,
			defaults,
			getData,
			getFormData,
			touch: form.touch,
			valid: form.valid,
			invalid: form.invalid,
			touched: form.touched,
			validate: (field, config3) => form.validate(...UseFormUtils.mergeHeadersForValidation(field, config3, props.headers)),
			validator: () => form.validator()
		};
		expose(exposed);
		provide(FormContextKey, exposed);
		return () => {
			return h("form", {
				...attrs,
				ref: formElement,
				action: isUrlMethodPair(props.action) ? props.action.url : props.action,
				method: method.value,
				onSubmit: (event) => {
					event.preventDefault();
					submit(event.submitter);
				},
				inert: props.disableWhileProcessing && form.processing
			}, slots.default ? slots.default(exposed) : []);
		};
	}
});
var head_default = defineComponent({
	props: { title: {
		type: String,
		required: false
	} },
	data() {
		return { provider: this.$headManager.createProvider() };
	},
	beforeUnmount() {
		this.provider.disconnect();
	},
	methods: {
		isUnaryTag(node) {
			return typeof node.type === "string" && [
				"area",
				"base",
				"br",
				"col",
				"embed",
				"hr",
				"img",
				"input",
				"keygen",
				"link",
				"meta",
				"param",
				"source",
				"track",
				"wbr"
			].indexOf(node.type) > -1;
		},
		renderTagStart(node) {
			node.props = node.props || {};
			node.props[this.provider.preferredAttribute()] = node.props["head-key"] !== void 0 ? node.props["head-key"] : "";
			const attrs = Object.keys(node.props).reduce((carry, name) => {
				const value = String(node.props[name]);
				if (["key", "head-key"].includes(name)) return carry;
				else if (value === "") return carry + ` ${name}`;
				else return carry + ` ${name}="${escape$1(value)}"`;
			}, "");
			return `<${String(node.type)}${attrs}>`;
		},
		renderTagChildren(node) {
			const { children } = node;
			if (typeof children === "string") return children;
			if (Array.isArray(children)) return children.reduce((html, child) => {
				return html + this.renderTag(child);
			}, "");
			return "";
		},
		isFunctionNode(node) {
			return typeof node.type === "function";
		},
		isComponentNode(node) {
			return typeof node.type === "object";
		},
		isCommentNode(node) {
			return /(comment|cmt)/i.test(node.type.toString());
		},
		isFragmentNode(node) {
			return /(fragment|fgt|symbol\(\))/i.test(node.type.toString());
		},
		isTextNode(node) {
			return /(text|txt)/i.test(node.type.toString());
		},
		renderTag(node) {
			if (this.isTextNode(node)) return String(node.children);
			else if (this.isFragmentNode(node)) return "";
			else if (this.isCommentNode(node)) return "";
			let html = this.renderTagStart(node);
			if (node.children) html += this.renderTagChildren(node);
			if (!this.isUnaryTag(node)) html += `</${String(node.type)}>`;
			return html;
		},
		addTitleElement(elements) {
			if (this.title && !elements.find((tag) => tag.startsWith("<title"))) elements.push(`<title ${this.provider.preferredAttribute()}>${this.title}</title>`);
			return elements;
		},
		renderNodes(nodes) {
			const elements = nodes.flatMap((node) => this.resolveNode(node)).map((node) => this.renderTag(node)).filter((node) => node);
			return this.addTitleElement(elements);
		},
		resolveNode(node) {
			if (this.isFunctionNode(node)) return this.resolveNode(node.type());
			else if (this.isComponentNode(node)) {
				console.warn(`Using components in the <Head> component is not supported.`);
				return [];
			} else if (this.isTextNode(node) && node.children) return node;
			else if (this.isFragmentNode(node) && node.children) return node.children.flatMap((child) => this.resolveNode(child));
			else if (this.isCommentNode(node)) return [];
			else return node;
		}
	},
	render() {
		this.provider.update(this.renderNodes(this.$slots.default ? this.$slots.default() : []));
	}
});
var resolveHTMLElement = (value, fallback) => {
	if (!value) return fallback;
	if (typeof value === "string") return document.querySelector(value);
	if (typeof value === "function") return value() || null;
	return fallback;
};
defineComponent({
	name: "InfiniteScroll",
	slots: Object,
	props: {
		data: {
			type: String,
			required: true
		},
		buffer: {
			type: Number,
			default: 0
		},
		onlyNext: {
			type: Boolean,
			default: false
		},
		onlyPrevious: {
			type: Boolean,
			default: false
		},
		as: {
			type: String,
			default: "div"
		},
		manual: {
			type: Boolean,
			default: false
		},
		manualAfter: {
			type: Number,
			default: 0
		},
		preserveUrl: {
			type: Boolean,
			default: false
		},
		reverse: {
			type: Boolean,
			default: false
		},
		autoScroll: {
			type: Boolean,
			default: void 0
		},
		itemsElement: {
			type: [
				String,
				Function,
				Object
			],
			default: null
		},
		startElement: {
			type: [
				String,
				Function,
				Object
			],
			default: null
		},
		endElement: {
			type: [
				String,
				Function,
				Object
			],
			default: null
		}
	},
	inheritAttrs: false,
	setup(props, { slots, attrs, expose }) {
		const itemsElementRef = ref(null);
		const startElementRef = ref(null);
		const endElementRef = ref(null);
		const itemsElement = computed(() => resolveHTMLElement(props.itemsElement, itemsElementRef.value));
		const scrollableParent = computed(() => getScrollableParent(itemsElement.value));
		const startElement = computed(() => resolveHTMLElement(props.startElement, startElementRef.value));
		const endElement = computed(() => resolveHTMLElement(props.endElement, endElementRef.value));
		const loadingPrevious = ref(false);
		const loadingNext = ref(false);
		const requestCount = ref(0);
		const hasPreviousPage = ref(false);
		const hasNextPage = ref(false);
		const syncStateFromDataManager = () => {
			requestCount.value = dataManager.getRequestCount();
			hasPreviousPage.value = dataManager.hasPrevious();
			hasNextPage.value = dataManager.hasNext();
		};
		const { dataManager, elementManager, flush: flushInfiniteScroll } = useInfiniteScroll({
			getPropName: () => props.data,
			inReverseMode: () => props.reverse,
			shouldFetchNext: () => !props.onlyPrevious,
			shouldFetchPrevious: () => !props.onlyNext,
			shouldPreserveUrl: () => props.preserveUrl,
			getTriggerMargin: () => props.buffer,
			getStartElement: () => startElement.value,
			getEndElement: () => endElement.value,
			getItemsElement: () => itemsElement.value,
			getScrollableParent: () => scrollableParent.value,
			onBeforePreviousRequest: () => loadingPrevious.value = true,
			onBeforeNextRequest: () => loadingNext.value = true,
			onCompletePreviousRequest: () => {
				loadingPrevious.value = false;
				syncStateFromDataManager();
			},
			onCompleteNextRequest: () => {
				loadingNext.value = false;
				syncStateFromDataManager();
			},
			onDataReset: syncStateFromDataManager
		});
		syncStateFromDataManager();
		if (typeof window === "undefined") {
			const scrollProp = usePage().scrollProps?.[props.data];
			if (scrollProp) {
				hasPreviousPage.value = !!scrollProp.previousPage;
				hasNextPage.value = !!scrollProp.nextPage;
			}
		}
		const autoLoad = computed(() => !manualMode.value);
		const manualMode = computed(() => props.manual || props.manualAfter > 0 && requestCount.value >= props.manualAfter);
		const scrollToBottom = () => {
			if (scrollableParent.value) scrollableParent.value.scrollTo({
				top: scrollableParent.value.scrollHeight,
				behavior: "instant"
			});
			else window.scrollTo({
				top: document.body.scrollHeight,
				behavior: "instant"
			});
		};
		onMounted(() => {
			elementManager.setupObservers();
			elementManager.processServerLoadedElements(dataManager.getLastLoadedPage());
			if (props.autoScroll !== void 0 ? props.autoScroll : props.reverse) scrollToBottom();
			if (autoLoad.value) elementManager.enableTriggers();
		});
		onUnmounted(flushInfiniteScroll);
		watch(() => [
			autoLoad.value,
			props.onlyNext,
			props.onlyPrevious
		], ([enabled]) => {
			enabled ? elementManager.enableTriggers() : elementManager.disableTriggers();
		});
		expose({
			fetchNext: dataManager.fetchNext,
			fetchPrevious: dataManager.fetchPrevious,
			hasPrevious: dataManager.hasPrevious,
			hasNext: dataManager.hasNext
		});
		return () => {
			const renderElements = [];
			const sharedExposed = {
				loadingPrevious: loadingPrevious.value,
				loadingNext: loadingNext.value,
				hasPrevious: hasPreviousPage.value,
				hasNext: hasNextPage.value
			};
			if (!props.startElement) {
				const headerAutoMode = autoLoad.value && !props.onlyNext;
				const exposedPrevious = {
					loading: loadingPrevious.value,
					fetch: dataManager.fetchPrevious,
					autoMode: headerAutoMode,
					manualMode: !headerAutoMode,
					hasMore: hasPreviousPage.value,
					...sharedExposed
				};
				renderElements.push(h("div", { ref: startElementRef }, slots.previous ? slots.previous(exposedPrevious) : loadingPrevious.value ? slots.loading?.(exposedPrevious) : void 0));
			}
			renderElements.push(h(props.as, {
				...attrs,
				ref: itemsElementRef
			}, slots.default?.({
				loading: loadingPrevious.value || loadingNext.value,
				loadingPrevious: loadingPrevious.value,
				loadingNext: loadingNext.value
			})));
			if (!props.endElement) {
				const footerAutoMode = autoLoad.value && !props.onlyPrevious;
				const exposedNext = {
					loading: loadingNext.value,
					fetch: dataManager.fetchNext,
					autoMode: footerAutoMode,
					manualMode: !footerAutoMode,
					hasMore: hasNextPage.value,
					...sharedExposed
				};
				renderElements.push(h("div", { ref: endElementRef }, slots.next ? slots.next(exposedNext) : loadingNext.value ? slots.loading?.(exposedNext) : void 0));
			}
			return h(Fragment, {}, props.reverse ? [...renderElements].reverse() : renderElements);
		};
	}
});
var noop2 = () => {};
var link_default = defineComponent({
	name: "Link",
	props: {
		as: {
			type: [String, Object],
			default: "a"
		},
		data: {
			type: Object,
			default: () => ({})
		},
		href: {
			type: [String, Object],
			default: ""
		},
		method: {
			type: String,
			default: "get"
		},
		replace: {
			type: Boolean,
			default: false
		},
		preserveScroll: {
			type: [
				Boolean,
				String,
				Function
			],
			default: false
		},
		preserveState: {
			type: [
				Boolean,
				String,
				Function
			],
			default: null
		},
		preserveUrl: {
			type: Boolean,
			default: false
		},
		only: {
			type: Array,
			default: () => []
		},
		except: {
			type: Array,
			default: () => []
		},
		headers: {
			type: Object,
			default: () => ({})
		},
		queryStringArrayFormat: {
			type: String,
			default: "brackets"
		},
		async: {
			type: Boolean,
			default: false
		},
		prefetch: {
			type: [
				Boolean,
				String,
				Array
			],
			default: false
		},
		cacheFor: {
			type: [
				Number,
				String,
				Array
			],
			default: 0
		},
		onStart: {
			type: Function,
			default: noop2
		},
		onProgress: {
			type: Function,
			default: noop2
		},
		onFinish: {
			type: Function,
			default: noop2
		},
		onBefore: {
			type: Function,
			default: noop2
		},
		onCancel: {
			type: Function,
			default: noop2
		},
		onSuccess: {
			type: Function,
			default: noop2
		},
		onError: {
			type: Function,
			default: noop2
		},
		onCancelToken: {
			type: Function,
			default: noop2
		},
		onPrefetching: {
			type: Function,
			default: noop2
		},
		onPrefetched: {
			type: Function,
			default: noop2
		},
		cacheTags: {
			type: [String, Array],
			default: () => []
		},
		viewTransition: {
			type: [Boolean, Object],
			default: false
		}
	},
	setup(props, { slots, attrs }) {
		const inFlightCount = ref(0);
		const hoverTimeout = ref();
		const prefetchModes = computed(() => {
			if (props.prefetch === true) return ["hover"];
			if (props.prefetch === false) return [];
			if (Array.isArray(props.prefetch)) return props.prefetch;
			return [props.prefetch];
		});
		const cacheForValue = computed(() => {
			if (props.cacheFor !== 0) return props.cacheFor;
			if (prefetchModes.value.length === 1 && prefetchModes.value[0] === "click") return 0;
			return config.get("prefetch.cacheFor");
		});
		onMounted(() => {
			if (prefetchModes.value.includes("mount")) prefetch();
		});
		onUnmounted(() => {
			clearTimeout(hoverTimeout.value);
		});
		const method = computed(() => isUrlMethodPair(props.href) ? props.href.method : (props.method ?? "get").toLowerCase());
		const as = computed(() => {
			if (typeof props.as !== "string" || props.as.toLowerCase() !== "a") return props.as;
			return method.value !== "get" ? "button" : props.as.toLowerCase();
		});
		const mergeDataArray = computed(() => mergeDataIntoQueryString(method.value, isUrlMethodPair(props.href) ? props.href.url : props.href, props.data || {}, props.queryStringArrayFormat));
		const href = computed(() => mergeDataArray.value[0]);
		const data = computed(() => mergeDataArray.value[1]);
		const elProps = computed(() => {
			if (as.value === "button") return { type: "button" };
			if (as.value === "a" || typeof as.value !== "string") return { href: href.value };
			return {};
		});
		const baseParams = computed(() => ({
			data: data.value,
			method: method.value,
			replace: props.replace,
			preserveScroll: props.preserveScroll,
			preserveState: props.preserveState ?? method.value !== "get",
			preserveUrl: props.preserveUrl,
			only: props.only,
			except: props.except,
			headers: props.headers,
			async: props.async
		}));
		const visitParams = computed(() => ({
			...baseParams.value,
			viewTransition: props.viewTransition,
			onCancelToken: props.onCancelToken,
			onBefore: props.onBefore,
			onStart: (visit) => {
				inFlightCount.value++;
				props.onStart?.(visit);
			},
			onProgress: props.onProgress,
			onFinish: (visit) => {
				inFlightCount.value--;
				props.onFinish?.(visit);
			},
			onCancel: props.onCancel,
			onSuccess: props.onSuccess,
			onError: props.onError
		}));
		const prefetch = () => {
			router.prefetch(href.value, {
				...baseParams.value,
				onPrefetching: props.onPrefetching,
				onPrefetched: props.onPrefetched
			}, {
				cacheFor: cacheForValue.value,
				cacheTags: props.cacheTags
			});
		};
		const regularEvents = { onClick: (event) => {
			if (shouldIntercept(event)) {
				event.preventDefault();
				router.visit(href.value, visitParams.value);
			}
		} };
		const prefetchHoverEvents = {
			onMouseenter: () => {
				hoverTimeout.value = setTimeout(() => {
					prefetch();
				}, config.get("prefetch.hoverDelay"));
			},
			onMouseleave: () => {
				clearTimeout(hoverTimeout.value);
			},
			onClick: regularEvents.onClick
		};
		const prefetchClickEvents = {
			onMousedown: (event) => {
				if (shouldIntercept(event)) {
					event.preventDefault();
					prefetch();
				}
			},
			onKeydown: (event) => {
				if (shouldNavigate(event)) {
					event.preventDefault();
					prefetch();
				}
			},
			onMouseup: (event) => {
				if (shouldIntercept(event)) {
					event.preventDefault();
					router.visit(href.value, visitParams.value);
				}
			},
			onKeyup: (event) => {
				if (shouldNavigate(event)) {
					event.preventDefault();
					router.visit(href.value, visitParams.value);
				}
			},
			onClick: (event) => {
				if (shouldIntercept(event)) event.preventDefault();
			}
		};
		return () => {
			return h(as.value, {
				...attrs,
				...elProps.value,
				"data-loading": inFlightCount.value > 0 ? "" : void 0,
				...(() => {
					if (prefetchModes.value.includes("hover")) return prefetchHoverEvents;
					if (prefetchModes.value.includes("click")) return prefetchClickEvents;
					return regularEvents;
				})()
			}, slots);
		};
	}
});
function usePoll(interval, requestOptions = {}, options = {
	keepAlive: false,
	autoStart: true
}) {
	const { stop, start } = router.poll(interval, requestOptions, {
		...options,
		autoStart: false
	});
	onMounted(() => {
		if (options.autoStart ?? true) start();
	});
	onUnmounted(() => {
		stop();
	});
	return {
		stop,
		start
	};
}
defineComponent({
	name: "WhenVisible",
	slots: Object,
	props: {
		data: { type: [String, Array] },
		params: { type: Object },
		buffer: {
			type: Number,
			default: 0
		},
		as: {
			type: String,
			default: "div"
		},
		always: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			loaded: false,
			fetching: false,
			observer: null
		};
	},
	unmounted() {
		this.observer?.disconnect();
	},
	computed: { keys() {
		return this.data ? Array.isArray(this.data) ? this.data : [this.data] : [];
	} },
	created() {
		const page2 = usePage();
		this.$watch(() => this.keys.map((key2) => page2.props[key2]), () => {
			const exists = this.keys.length > 0 && this.keys.every((key2) => page2.props[key2] !== void 0);
			this.loaded = exists;
			if (exists && !this.always) return;
			if (!this.observer || !exists) this.$nextTick(this.registerObserver);
		}, { immediate: true });
	},
	methods: {
		registerObserver() {
			if (typeof window === "undefined") return;
			this.observer?.disconnect();
			this.observer = new IntersectionObserver((entries) => {
				if (!entries[0].isIntersecting) return;
				if (this.fetching) return;
				if (!this.always && this.loaded) return;
				this.fetching = true;
				const reloadParams = this.getReloadParams();
				router.reload({
					...reloadParams,
					onStart: (e) => {
						this.fetching = true;
						reloadParams.onStart?.(e);
					},
					onFinish: (e) => {
						this.loaded = true;
						this.fetching = false;
						reloadParams.onFinish?.(e);
						if (!this.always) this.observer?.disconnect();
					}
				});
			}, { rootMargin: `${this.$props.buffer}px` });
			this.observer.observe(this.$el.nextSibling);
		},
		getReloadParams() {
			const reloadParams = { ...this.$props.params };
			if (this.$props.data) reloadParams.only = Array.isArray(this.$props.data) ? this.$props.data : [this.$props.data];
			return reloadParams;
		}
	},
	render() {
		const els = [];
		if (this.$props.always || !this.loaded) els.push(h(this.$props.as));
		if (!this.loaded) els.push(this.$slots.fallback ? this.$slots.fallback({}) : null);
		else if (this.$slots.default) els.push(this.$slots.default({ fetching: this.fetching }));
		return els;
	}
});
var config = config$1.extend({});
//#endregion
export { isArguments as A, isObject as B, get as C, isKey as D, castPath as E, baseAssignValue as F, axios as G, arrayMap as H, arrayEach as I, setToString as L, isArrayLike as M, baseRest as N, keys as O, overRest as P, identity as R, arrayPush as S, toKey as T, isSymbol as U, isArray as V, Symbol$1 as W, setToArray as _, useForm as a, Set$1 as b, router as c, isEqual as d, isArrayLikeObject as f, baseIsEqual as g, hasPath as h, link_default as i, isIterateeCall as j, baseUnary as k, require_lib as l, baseFor as m, form_default as n, usePage as o, debounce$1 as p, head_default as r, usePoll as s, createInertiaApp as t, baseSet as u, cacheHas as v, baseGet as w, Stack as x, SetCache as y, toNumber as z };
