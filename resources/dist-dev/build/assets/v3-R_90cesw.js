import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, G as renderSlot, K as resolveComponent, L as onMounted, M as mergeProps, N as nextTick, S as createTextVNode, T as defineComponent, W as renderList, _ as createBlock, _t as ref, et as watch, f as Fragment, g as createBaseVNode, h as computed, ht as reactive, it as withCtx, kt as normalizeStyle, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
//#region node_modules/helper-js/dist/index.esm.js
/*!
* helper-js v3.1.6
* Author: phphe <phphe@outlook.com> (https://github.com/phphe)
* Homepage: null
* Released under the MIT License.
*/
function isArray(v) {
	return Object.prototype.toString.call(v) === "[object Array]";
}
function isObject(v) {
	return Object.prototype.toString.call(v) === "[object Object]";
}
function isFunction(v) {
	return typeof v === "function";
}
/**
* If n greater than `max`, return `max`, else n.
* 如果n大于max, 返回max, 否则n.
* @param n
* @param max
* @returns
*/
function notGreaterThan(n, max) {
	return n < max ? n : max;
}
/**
* remove item from array. return removed count
* 从数组删除项. 返回删除计数
* @param arr
* @param v
* @returns
*/
function arrayRemove(arr, v) {
	let index;
	let count = 0;
	while ((index = arr.indexOf(v)) > -1) {
		arr.splice(index, 1);
		count++;
	}
	return count;
}
/**
* get last of array
* 返回数组末项
* @param arr
* @returns
*/
function arrayLast(arr) {
	return arr[arr.length - 1];
}
function toArrayIfNot(arrOrNot) {
	return isArray(arrOrNot) ? arrOrNot : [arrOrNot];
}
function objectOnly(obj, keys) {
	let keysSet = new Set(keys);
	const r = {};
	keysSet.forEach((key) => {
		r[key] = obj[key];
	});
	return r;
}
function iterateAll(val) {
	let opt = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
	return function* () {
		if (!opt.reverse) if (val.length != null) for (let i = 0; i < val.length; i++) {
			const info = {
				value: val[i],
				index: i
			};
			if (!opt.exclude || !opt.exclude(info)) yield info;
		}
		else if (isObject(val)) for (const key of Object.keys(val)) {
			const info = {
				value: val[key],
				key
			};
			if (!opt.exclude || !opt.exclude(info)) yield info;
		}
		else throw "Unsupported type";
		else if (val.length != null) for (let i = val.length - 1; i >= 0; i--) {
			const info = {
				value: val[i],
				index: i
			};
			if (!opt.exclude || !opt.exclude(info)) yield info;
		}
		else if (isObject(val)) {
			const keys = Object.keys(val);
			keys.reverse();
			for (const key of keys) {
				const info = {
					value: val[key],
					key
				};
				if (!opt.exclude || !opt.exclude(info)) yield info;
			}
		} else throw "Unsupported type";
	}();
}
function assignIfNoKey(obj, key, val) {
	if (!obj.hasOwnProperty(key)) obj[key] = val;
}
function objectAssignIfNoKey(obj1, obj2) {
	Object.keys(obj2).forEach((key) => {
		assignIfNoKey(obj1, key, obj2[key]);
	});
	return obj1;
}
function withoutUndefined(obj) {
	const r = {};
	Object.keys(obj).forEach((key) => {
		if (obj[key] !== void 0) r[key] = obj[key];
	});
	return r;
}
/**
* walk tree data by with depth first search. tree data example: `[{children: [{}, {}]}]`
* 深度优先遍历树形数据. 树形数据示例: `[{children: [{}, {}]}]`
* @param obj
* @param handler
* @param opt
*/
function walkTreeData(obj, handler) {
	let opt = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
	opt = objectAssignIfNoKey({ ...opt }, { childrenKey: "children" });
	const { childrenKey } = opt;
	const rootChildren = isArray(obj) ? obj : [obj];
	class StopException {}
	const func = (children, parent, parentPath) => {
		if (opt.reverse) {
			children = children.slice();
			children.reverse();
		}
		const len = children.length;
		for (let i = 0; i < len; i++) {
			const item = children[i];
			const index = opt.reverse ? len - i - 1 : i;
			const path = parentPath ? [...parentPath, index] : [];
			if (opt.childFirst) {
				if (item[childrenKey] != null) func(item[childrenKey], item, path);
			}
			const r = handler(item, index, parent, path);
			if (r === false) throw new StopException();
			else if (r === "skip children") continue;
			else if (r === "skip siblings") break;
			if (!opt.childFirst) {
				if (item[childrenKey] != null) func(item[childrenKey], item, path);
			}
		}
	};
	try {
		func(rootChildren, null, isArray(obj) ? [] : null);
	} catch (e) {
		if (e instanceof StopException);
		else throw e;
	}
}
/**
* like Array.find
* @param obj
* @param handler return true when found.
* @param opt
* @returns
*/
function findInfoInTreeData(obj, handler) {
	let opt = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
	let r;
	walkTreeData(obj, function() {
		if (handler(...arguments)) {
			r = {
				node: arguments.length <= 0 ? void 0 : arguments[0],
				index: arguments.length <= 1 ? void 0 : arguments[1],
				parent: arguments.length <= 2 ? void 0 : arguments[2],
				path: arguments.length <= 3 ? void 0 : arguments[3]
			};
			return false;
		}
	}, opt);
	return r;
}
/**
* like Array.find
* @param obj
* @param handler return true when found.
* @param opt
* @returns
*/
function findTreeData(obj, handler) {
	return findInfoInTreeData(obj, handler, arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {})?.node;
}
function cloneTreeData(root, options) {
	const opt = { childrenKey: "children" };
	if (options) Object.assign(opt, options);
	const { childrenKey, nodeHandler } = opt;
	const td = new TreeData();
	td.childrenKey = childrenKey;
	walkTreeData(root, (node, index, parent, path) => {
		let newNode = Object.assign({}, node);
		if (newNode[childrenKey]) newNode[childrenKey] = [];
		if (nodeHandler) newNode = nodeHandler(newNode, {
			oldNode: node,
			index,
			parent,
			path
		});
		td.set(path, newNode);
	}, { childrenKey });
	return td.data;
}
var TreeData = class {
	data;
	childrenKey = "children";
	constructor() {
		let data = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : [];
		this.data = data;
	}
	get rootChildren() {
		const { childrenKey } = this;
		const { data } = this;
		return isArray(data) ? data : data[childrenKey];
	}
	iteratePath(path) {
		var _this = this;
		let opt = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
		return function* () {
			const { childrenKey, rootChildren } = _this;
			if (!opt.reverse) {
				let prevPath = [];
				let prevChildren = rootChildren;
				for (const index of path) {
					const currentPath = [...prevPath, index];
					const currentNode = prevChildren[index];
					yield {
						path: currentPath,
						node: currentNode
					};
					prevPath = currentPath;
					prevChildren = currentNode[childrenKey];
				}
			} else {
				const list = [..._this.iteratePath(path, {
					...opt,
					reverse: false
				})];
				list.reverse();
				for (const { path: path0, node } of list) yield {
					path: path0,
					node
				};
			}
		}();
	}
	getFamily(path) {
		const all = [];
		for (const { node } of this.iteratePath(path)) all.push(node);
		return all;
	}
	get(path) {
		return arrayLast(this.getFamily(path));
	}
	getParentAndIndex(path) {
		const parentPath = path.slice();
		const index = parentPath.pop();
		return {
			parent: this.get(parentPath),
			index,
			parentPath
		};
	}
	getParent(path) {
		return this.getParentAndIndex(path).parent;
	}
	set(path, node) {
		if (path == null || path.length === 0) this.data = node;
		else {
			const { childrenKey } = this;
			let { rootChildren } = this;
			const { parent, index } = this.getParentAndIndex(path);
			let parentChildren;
			if (path.length === 1) {
				if (!rootChildren) if (this.data) this.data[childrenKey] = [];
				else this.data = [];
				parentChildren = rootChildren;
			} else {
				if (!parent[childrenKey]) parent[childrenKey] = [];
				parentChildren = parent[childrenKey];
			}
			parentChildren[index] = node;
		}
	}
	delete(path) {
		const { childrenKey, rootChildren } = this;
		const { parent, index } = this.getParentAndIndex(path);
		const parentChildren = path.length === 1 ? rootChildren : parent[childrenKey];
		const node = parentChildren[index];
		parentChildren.splice(index, 1);
		return node;
	}
	walk(handler, opt) {
		const { childrenKey, data } = this;
		return walkTreeData(data, handler, childrenKey, opt);
	}
	clone() {
		let opt = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
		return cloneTreeData(this.data), withoutUndefined({
			childrenKey: this.childrenKey,
			nodeHandler: opt.nodeHandler || void 0
		});
	}
};
/**
* if it is function, return result, else return it directly.
* @param valueOrGetter
* @param args
* @returns
*/
function resolveValueOrGettter(valueOrGetter) {
	let args = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : [];
	if (isFunction(valueOrGetter)) return valueOrGetter(...args);
	else return valueOrGetter;
}
/**
* apply finally function to a function, execute it after target return, event it error
* 在目标方法结束或出错后执行另一方法
* @param func
* @param finallyFunc
* @returns
*/
function applyFinally(func, finallyFunc) {
	const wrapped = function() {
		let r, e;
		try {
			r = func(...arguments);
		} catch (error) {
			e = error;
		} finally {
			finallyFunc();
		}
		if (!e) return r;
		else throw e;
	};
	return wrapped;
}
/**
* Cache function return by arguments
* @param func
* @returns
*/
function cacheFunction(func) {
	let options = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
	const cachedArgsArr = [];
	let map;
	const defaultValue = {};
	let noArgsCache = defaultValue;
	const wrapped = function() {
		for (var _len6 = arguments.length, args = new Array(_len6), _key6 = 0; _key6 < _len6; _key6++) args[_key6] = arguments[_key6];
		if (args.length === 0) {
			if (noArgsCache === defaultValue) noArgsCache = func();
			return noArgsCache;
		}
		if (!map) map = new ArrayKeyMap();
		if (!map.has(args)) {
			map.set(args, func(...args));
			if (options.capacity != null) {
				cachedArgsArr.push(args);
				const removed = cachedArgsArr.splice(0, cachedArgsArr.length - options.capacity);
				for (const args of removed) map.delete(args);
			}
		}
		return map.get(args);
	};
	const clearCache = () => {
		map = null;
		cachedArgsArr.splice(0, cachedArgsArr.length);
	};
	return {
		action: wrapped,
		clearCache
	};
}
function promisePin() {
	let resolve, reject;
	return {
		promise: new Promise((resolve2, reject2) => {
			resolve = resolve2;
			reject = reject2;
		}),
		resolve,
		reject
	};
}
/**
* NOT RECOMMEND. Use Node.contains instead.
*/
function isDescendantOf(el, parent) {
	while (true) if (el.parentNode == null) return false;
	else if (el.parentNode === parent) return true;
	else el = el.parentNode;
}
/**
* relative to viewport. like position fixed. alias getViewportPosition
* 相对于视口. 类似 position fixed. 别名 getViewportPosition
* @param el
* @returns
*/
function getBoundingClientRect(el) {
	let xy = el.getBoundingClientRect();
	if (document.documentElement.clientTop > 0) {
		const top = xy.top - document.documentElement.clientTop, bottom = xy.bottom, left = xy.left - document.documentElement.clientLeft, right = xy.right;
		const json = {
			top,
			right,
			bottom,
			left,
			width: xy.width || right - left,
			height: xy.height || bottom - top,
			x: left,
			y: top
		};
		xy = {
			...json,
			toJSON: () => json
		};
	}
	return xy;
}
function findParent(el, callback) {
	let opt = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
	let cur = opt && opt.withSelf ? el : el.parentElement;
	while (cur) {
		const shouldBreak = opt.until && cur === opt.until;
		if (shouldBreak && !opt.withUntil) return;
		const r = callback(cur);
		if (r === "break") return;
		else if (r) return cur;
		else if (shouldBreak) return;
		else cur = cur.parentElement;
	}
}
function hasClass(el, className) {
	if (el.classList) return el.classList.contains(className);
	else return new RegExp("(^| )" + className + "( |$)", "gi").test(el.className);
}
/**
* has any class in classNames
* @param el
* @param classNames
* @returns
*/
function hasClassIn(el, classNames) {
	for (const className of classNames) if (hasClass(el, className)) return true;
	return false;
}
/**
* listen event on element
* @param el
* @param name
* @param handler
* @param options
*/
function on(el, name, handler, options) {
	if (el.addEventListener) el.addEventListener(name, handler, options);
	else if (el.attachEvent) el.attachEvent(`on${name}`, handler, options);
}
/**
* cancel event lisitener on element
* @param el
* @param name
* @param handler
* @param options
*/
function off(el, name, handler, options) {
	if (el.removeEventListener) el.removeEventListener(name, handler, options);
	else if (el.detachEvent) el.detachEvent(`on${name}`, handler, options);
}
/**
* lisiten multi events, and can stop and resume them. start listening by default. start is alias for resume
* @param info
* @returns
*/
function extendedListen(info) {
	let destroyFuncs = [];
	const listenAll = () => {
		if (r.listening) return;
		for (const item of info) {
			on.apply(this, item);
			const destroy = () => off.apply(this, item);
			destroyFuncs.push(destroy);
		}
		r.listening = true;
	};
	const destroyAll = () => {
		if (!r.listening) return;
		for (const destroy of destroyFuncs) destroy();
		destroyFuncs = [];
		r.listening = false;
	};
	const r = {
		listening: false,
		stop: destroyAll,
		resume: listenAll,
		start: listenAll
	};
	r.start();
	return r;
}
/**
* like jquery $(el).css(), but only can read
* @param el
* @param name
* @returns
*/
function css(el, name) {
	return getComputedStyle(el)[name];
}
/**
* binarySearch, 二分查找
* @param arr
* @param callback return `mid - your_value` for ascending array
* @param opt
* @returns
*/
function binarySearch(arr, callback) {
	let opt = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
	opt = {
		start: 0,
		end: arr.length - 1,
		maxTimes: 1e3,
		...opt
	};
	let { start, end } = opt;
	const { returnNearestIfNoHit, maxTimes } = opt;
	let midNum;
	let mid;
	if (start == null) {
		start = 0;
		end = arr.length - 1;
	}
	let i = 0;
	let r;
	while (start >= 0 && start <= end) {
		if (i >= maxTimes) throw Error(`binarySearch: loop times is over ${maxTimes}, you can increase the limit.`);
		midNum = Math.floor((end - start) / 2 + start);
		mid = arr[midNum];
		const count = i + 1;
		r = callback(mid, midNum, count);
		if (r > 0) end = midNum - 1;
		else if (r < 0) start = midNum + 1;
		else return {
			index: midNum,
			value: mid,
			count,
			hit: true
		};
		i++;
	}
	return returnNearestIfNoHit ? {
		index: midNum,
		value: mid,
		count: i + 1,
		hit: false,
		greater: r > 0
	} : null;
}
function makeStorageHelper(storage) {
	return {
		storage,
		set(name, value, minutes) {
			if (value == null) this.storage.removeItem(name);
			else this.storage.setItem(name, JSON.stringify({
				value,
				expired_at: minutes ? (/* @__PURE__ */ new Date()).getTime() + minutes * 60 * 1e3 : null
			}));
		},
		get(name) {
			let t = this.storage.getItem(name);
			if (t) {
				t = JSON.parse(t);
				if (!t.expired_at || t.expired_at > (/* @__PURE__ */ new Date()).getTime()) return t.value;
				else this.storage.removeItem(name);
			}
			return null;
		},
		clear() {
			this.storage.clear();
		}
	};
}
cacheFunction(function() {
	return makeStorageHelper(localStorage);
});
cacheFunction(function() {
	return makeStorageHelper(sessionStorage);
});
/**
* Like Map, support array as key. array order is used.
*/
var ArrayKeyMap = class {
	_map = (() => /* @__PURE__ */ new Map())();
	_values = {};
	_objCount = 0;
	_keysToString(keys) {
		const { _map } = this;
		let t = [];
		for (const key of keys) {
			if (!_map.has(key)) return null;
			t.push(_map.get(key)[0]);
		}
		return t.toString();
	}
	has(keys) {
		if (this._keysToString(keys) == null) return false;
		return true;
	}
	/**
	* throw error if not found
	* @param keys
	* @returns
	*/
	get(keys) {
		const { _values } = this;
		const key2 = this._keysToString(keys);
		if (key2 == null) throw "Value not found by specified keys";
		return _values[key2][1];
	}
	set(keys, value) {
		const { _map, _values } = this;
		let t = [];
		for (const key of keys) {
			let str;
			let count = 1;
			if (_map.has(key)) {
				[str, count] = _map.get(key);
				count++;
			} else {
				this._objCount++;
				str = this._objCount.toString();
			}
			_map.set(key, [str, count]);
			t.push(str);
		}
		_values[t.toString()] = [keys.slice(), value];
	}
	delete(keys) {
		const { _values, _map } = this;
		const key2 = this._keysToString(keys);
		if (key2 == null) throw "Value not found by specified keys";
		delete _values[key2];
		for (const key of keys) {
			let [str, count] = _map.get(key);
			count--;
			if (count === 0) _map.delete(key);
			else _map.set(key, [str, count]);
		}
	}
	clear() {
		this._map.clear();
		this._values = {};
	}
	*entries() {
		const { _values } = this;
		for (const key in _values) {
			const [keys, value] = _values[key];
			yield [keys, value];
		}
	}
	count() {
		return [...this.entries()].length;
	}
};
//#endregion
//#region node_modules/@he-tree/vue/node_modules/@virtual-list/vue/dist/v3/index.mjs
/*!
* @virtual-list/vue
* Author: phphe <phphe@outlook.com> (https://github.com/phphe)
* Homepage: https://virtual-list.phphe.com
* Released under the MIT License.
*/
var ce$1 = defineComponent({ props: { table: Boolean } }), Q$1 = (e, i) => {
	const m = e.__vccOpts || e;
	for (const [d, v] of i) m[d] = v;
	return m;
}, de$1 = { key: 0 }, fe = { key: 1 };
function me(e, i, m, d, v, z) {
	return e.table ? (openBlock(), createElementBlock("table", de$1, [
		renderSlot(e.$slots, "prepend"),
		createBaseVNode("tbody", null, [renderSlot(e.$slots, "default")]),
		renderSlot(e.$slots, "append")
	])) : (openBlock(), createElementBlock("div", fe, [
		renderSlot(e.$slots, "prepend"),
		renderSlot(e.$slots, "default"),
		renderSlot(e.$slots, "append")
	]));
}
var be = defineComponent({
	components: { VirtualListTable: /* @__PURE__ */ Q$1(ce$1, [["render", me]]) },
	props: {
		items: Array,
		disabled: Boolean,
		horizontal: Boolean,
		firstRender: {
			type: Number,
			default: 10
		},
		buffer: {
			type: Number,
			default: 100
		},
		itemKey: { type: [String, Function] },
		itemSize: { type: Function },
		table: Boolean
	},
	setup(e) {
		const i = ref(0), m = ref(e.firstRender - 1), d = computed(() => {
			var t;
			return notGreaterThan(m.value, (((t = e.items) == null ? void 0 : t.length) || 1) - 1);
		}), v = ref(0), z = computed(() => f.value[i.value] ? E(i.value) : 0), C = computed(() => f.value.length > 0 ? E(f.value.length - 1) + arrayLast(L.value) : 0), o = computed(() => f.value[d.value] ? C.value - E(d.value) - L.value[d.value] : 0), b = computed(() => e.disabled ? {} : { overflow: "auto" }), U = computed(() => {
			const t = { display: "flex" };
			return e.disabled || (e.horizontal ? Object.assign(t, {
				"margin-left": z.value + "px",
				"margin-right": o.value + "px",
				width: C.value - o.value - z.value + "px"
			}) : Object.assign(t, {
				"margin-top": z.value + "px",
				"margin-bottom": o.value + "px"
			})), t["flex-direction"] = e.horizontal ? "row" : "column", e.table && (delete t.display, delete t["flex-direction"]), t;
		}), $ = computed(() => reactive((e.items || []).map(() => null))), L = computed(() => (e.items || []).map((t, n) => {
			var g;
			if ($[n] != null) return $[n];
			let l = (g = e.itemSize) == null ? void 0 : g.call(e, t, n);
			return l ??= v.value, l;
		})), f = computed(() => {
			const t = [];
			return L.value.reduce((n, l) => (t.push(n), n + l), 0), t;
		});
		watch(() => e.items, y);
		const X = computed(() => {
			if (!e.items || e.disabled) return;
			const t = [];
			for (let n = i.value; n <= d.value; n++) {
				const l = e.items[n];
				if (!l) break;
				t.push({
					item: l,
					index: n
				});
			}
			return t;
		}), N = ref(), V = ref();
		onMounted(async () => {
			y();
			try {
				Z();
			} catch {
				await nextTick(), y();
			}
		});
		let T;
		function Y() {
			const t = N.value;
			if (!t) return;
			const n = O(t);
			T != null && e.buffer - Math.abs(n - T) >= 10 || (T = n, y());
		}
		let B = !1, K = !1;
		async function y() {
			var H;
			if (B) {
				K = !0;
				return;
			}
			if (!e.items || e.disabled) return;
			B = !0;
			const t = N.value, n = (H = V.value) == null ? void 0 : H.$el;
			if (!t || !n) return;
			v.value || (v.value = ne()), i.value = ee(), m.value = te(), await nextTick();
			let l, g = 0;
			const F = {}, q = e.table ? n.querySelector("tbody").children : n.children;
			for (let a = 0; a < q.length; a++) {
				const r = q[a], c = css(r, "position");
				if (c && ["absolute", "fixed"].includes(c)) continue;
				const h = css(r, "display") !== "none" ? A(r) : 0, S = r.getAttribute("vt-index"), R = S ? parseInt(S) : i.value + g;
				F[R] = (F[R] || 0) + h, g++;
			}
			for (const a of Object.keys(F)) {
				const r = parseInt(a);
				$.value[r] !== F[r] && ($.value[r] = F[r], l = !0);
			}
			l && await nextTick(), B = !1, K && (K = !1, y());
			function ee() {
				const a = O(t) - j(t) - e.buffer;
				return binarySearch(f.value, (c) => c - a, { returnNearestIfNoHit: !0 }).index;
			}
			function te() {
				const a = O(t) - j(t) + x(t) + e.buffer;
				return binarySearch(f.value, (c) => c - a, { returnNearestIfNoHit: !0 }).index;
			}
			function ne() {
				const r = [], c = e.table ? n.querySelector("tbody").children : n.children;
				for (let h = 0; h < c.length; h++) {
					const S = c[h], R = getComputedStyle(S);
					if (["absolute", "fixed"].includes(R.position)) continue;
					const le = A(S);
					if (r.push(le), r.length >= 10) break;
				}
				return r.length === 0 ? 0 : r.reduce((h, S) => h + S, 0) / r.length;
			}
		}
		function x(t) {
			const n = getComputedStyle(t);
			let l = parseFloat(e.horizontal ? n.width : n.height);
			return n.boxSizing === "border-box" && (e.horizontal ? l = l - parseFloat(n.borderLeftWidth) - parseFloat(n.borderRightWidth) : l = l - parseFloat(n.borderTopWidth) - parseFloat(n.borderBottomWidth)), l;
		}
		function A(t) {
			let n = x(t);
			const l = getComputedStyle(t);
			return e.horizontal ? n += parseFloat(l.borderLeftWidth) + parseFloat(l.borderRightWidth) + parseFloat(l.marginLeft) + parseFloat(l.marginRight) : n += parseFloat(l.borderTopWidth) + parseFloat(l.borderBottomWidth) + parseFloat(l.marginTop) + parseFloat(l.marginBottom), n = Number.isNaN(n) ? 0 : n, n;
		}
		function O(t) {
			return e.horizontal ? t.scrollLeft : t.scrollTop;
		}
		function j(t) {
			const n = getComputedStyle(t);
			return e.horizontal ? parseFloat(n.paddingLeft) : parseFloat(n.paddingTop);
		}
		function E(t) {
			return f.value[t];
		}
		function Z() {
			const t = N.value;
			new ResizeObserver((l) => {
				for (let g of l) if (hasClass(g.target, "vtlist")) {
					y();
					break;
				}
			}).observe(t);
		}
		function _(t, n) {
			if (e.itemKey) {
				if (typeof e.itemKey == "string" && e.itemKey === "index") return n;
				if (typeof e.itemKey == "function") return e.itemKey(t, n);
			}
		}
		return {
			listElRef: N,
			listInnerRef: V,
			onscroll: Y,
			listStyle: b,
			listInnerStyle: U,
			visibleItemsInfo: X,
			getItemKey: _,
			update: y,
			sizes: L,
			positions: f,
			runtimeSizes: $
		};
	}
});
function ye(e, i, m, d, v, z) {
	const C = resolveComponent("VirtualListTable");
	return openBlock(), createElementBlock("div", {
		class: "vtlist",
		ref: "listElRef",
		style: normalizeStyle(e.listStyle),
		onScrollPassive: i[0] || (i[0] = (...o) => e.onscroll && e.onscroll(...o))
	}, [createVNode(C, {
		class: "vtlist-inner",
		ref: "listInnerRef",
		style: normalizeStyle(e.listInnerStyle),
		table: e.table
	}, {
		prepend: withCtx(() => [renderSlot(e.$slots, "prepend")]),
		append: withCtx(() => [renderSlot(e.$slots, "append")]),
		default: withCtx(() => [e.disabled ? (openBlock(!0), createElementBlock(Fragment, { key: 0 }, renderList(e.items, (o, b) => renderSlot(e.$slots, "default", {
			key: e.getItemKey(o, b),
			item: o,
			index: b
		})), 128)) : (openBlock(!0), createElementBlock(Fragment, { key: 1 }, renderList(e.visibleItemsInfo, ({ item: o, index: b }) => renderSlot(e.$slots, "default", {
			key: e.getItemKey(o, b),
			item: o,
			index: b
		})), 128))]),
		_: 3
	}, 8, ["style", "table"])], 36);
}
var Se = /* @__PURE__ */ Q$1(be, [["render", ye]]);
//#endregion
//#region node_modules/@he-tree/tree-utils/dist/index.esm.js
/*!
* @he-tree/tree-utils v0.1.0-alpha.7
* Author: phphe <phphe@outlook.com> (https://github.com/phphe)
* Homepage: null
* Released under the MIT License.
*/
var CHILDREN = "children";
/**
* help to handle tree data. 帮助处理树形数据.
*/
function makeTreeProcessor(data) {
	const opt2 = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
	const utilsBase = {
		...defaultOptions$1,
		...opt2,
		data,
		stats: null,
		statsFlat: null,
		_statsMap: null,
		initialized: false,
		init() {
			const { data, childrenKey } = this;
			const td = new TreeData([]);
			this._statsMap = /* @__PURE__ */ new Map();
			walkTreeData(data, (nodeData, index, parent, path) => {
				const stat = this.statHandler({
					...statDefault(),
					data: nodeData,
					open: Boolean(this.defaultOpen),
					parent: td.getParent(path),
					children: [],
					level: path.length
				});
				this._statsMap.set(nodeData, stat);
				td.set(path, stat);
			}, { childrenKey });
			const statsFlat = [];
			td.walk((stat) => {
				statsFlat.push(stat);
			});
			this.stats = this.statsHandler(td.rootChildren);
			this.statsFlat = this.statsFlatHandler(statsFlat);
			this.initialized = true;
		},
		getStat(nodeData) {
			let r = this._statsMap.get(nodeData);
			if (!r) throw new StatNotFoundError("Stat not found");
			return r;
		},
		has(nodeData) {
			if (nodeData["isStat"]) return this.statsFlat.indexOf(nodeData) > -1;
			else try {
				let r = this.getStat(nodeData);
				return Boolean(r);
			} catch (error) {
				if (error instanceof StatNotFoundError) return false;
				throw error;
			}
		},
		_getPathByStat(stat) {
			if (stat == null) return [];
			const index = this.getSiblings(stat).indexOf(stat);
			return [...stat.parent ? this._getPathByStat(stat.parent) : [], index];
		},
		/**
		* call it after a stat's `checked` changed
		* @param stat
		* @returns return false mean ignored
		*/
		afterOneCheckChanged(stat) {
			const { checked } = stat;
			if (stat._ignoreCheckedOnce) {
				delete stat._ignoreCheckedOnce;
				return false;
			}
			const checkParent = (stat) => {
				const { parent } = stat;
				if (parent) {
					let hasChecked;
					let hasUnchecked;
					let hasHalfChecked;
					for (const child of parent.children) if (child.checked) hasChecked = true;
					else if (child.checked === 0) hasHalfChecked = true;
					else hasUnchecked = true;
					const parentChecked = hasHalfChecked ? 0 : !hasUnchecked ? true : hasChecked ? 0 : false;
					if (parent.checked !== parentChecked) {
						this._ignoreCheckedOnce(parent);
						parent.checked = parentChecked;
					}
					checkParent(parent);
				}
			};
			checkParent(stat);
			walkTreeData(stat.children, (child) => {
				if (child.checked !== checked) {
					this._ignoreCheckedOnce(child);
					child.checked = checked;
				}
			}, { childrenKey: CHILDREN });
			return true;
		},
		_ignoreCheckedOnce(stat) {
			stat._ignoreCheckedOnce = true;
			setTimeout(() => {
				if (stat._ignoreCheckedOnce) stat._ignoreCheckedOnce = false;
			}, 100);
		},
		isVisible(statOrNodeData) {
			const stat = statOrNodeData["isStat"] ? statOrNodeData : this.getStat(statOrNodeData);
			const walk = (stat) => {
				return !stat || !stat.hidden && stat.open && walk(stat.parent);
			};
			return Boolean(!stat.hidden && walk(stat.parent));
		},
		/**
		* call it to update all stats' `checked`
		*/
		updateCheck() {
			walkTreeData(this.stats, (stat) => {
				if (stat.children && stat.children.length > 0) {
					const allChecked = stat.children.every((v) => v.checked === true);
					const allUnchecked = stat.children.every((v) => v.checked === false);
					const checked = allChecked ? true : allUnchecked ? false : 0;
					if (stat.checked !== checked) {
						this._ignoreCheckedOnce(stat);
						stat.checked = checked;
					}
				}
			}, {
				childFirst: true,
				childrenKey: CHILDREN
			});
		},
		getChecked() {
			let withDemi = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : false;
			return this.statsFlat.filter((v) => {
				return v.checked || withDemi && v.checked === 0;
			});
		},
		getUnchecked() {
			let withDemi = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : true;
			return this.statsFlat.filter((v) => {
				return withDemi ? !v.checked : v.checked === false;
			});
		},
		/**
		* open all nodes
		*/
		openAll() {
			for (const stat of this.statsFlat) stat.open = true;
		},
		/**
		* close all nodes
		*/
		closeAll() {
			for (const stat of this.statsFlat) stat.open = false;
		},
		openNodeAndParents(nodeOrStat) {
			const stat = nodeOrStat["isStat"] ? nodeOrStat : this.getStat(nodeOrStat);
			for (const parentStat of this.iterateParent(stat, { withSelf: true })) parentStat.open = true;
		},
		_calcFlatIndex(parent, index) {
			let flatIndex = parent ? this.statsFlat.indexOf(parent) + 1 : 0;
			const siblings = parent ? parent.children : this.stats;
			for (let i = 0; i < index; i++) flatIndex += this._count(siblings[i]);
			return flatIndex;
		},
		add(nodeData, parent, index) {
			if (this.has(nodeData)) throw "Can't add because data exists in tree";
			const siblings = parent ? parent.children : this.stats;
			if (index == null) index = siblings.length;
			const stat = this.statHandler({
				...statDefault(),
				open: Boolean(this.defaultOpen),
				data: nodeData,
				parent: parent || null,
				children: [],
				level: parent ? parent.level + 1 : 1
			});
			this._setPosition(stat, parent || null, index);
			const children = nodeData[this.childrenKey];
			if (children) {
				const childrenSnap = children.slice();
				for (const child of childrenSnap) this.add(child, stat);
			}
		},
		remove(stat) {
			const siblings = this.getSiblings(stat);
			if (siblings.includes(stat)) {
				arrayRemove(siblings, stat);
				const stats = this._flat(stat);
				this.statsFlat.splice(this.statsFlat.indexOf(stat), stats.length);
				for (const stat of stats) this._statsMap.delete(stat.data);
				this.afterRemoveStat(stat);
				return true;
			}
			return false;
		},
		getSiblings(stat) {
			const { parent } = stat;
			return parent ? parent.children : this.stats;
		},
		/**
		* The node should not exsit.
		* @param node
		* @param parent
		* @param index
		*/
		_setPosition(stat, parent, index) {
			(parent ? parent.children : this.stats).splice(index, 0, stat);
			stat.parent = parent;
			stat.level = parent ? parent.level + 1 : 1;
			const flatIndex = this._calcFlatIndex(parent, index);
			const stats = this._flat(stat);
			this.statsFlat.splice(flatIndex, 0, ...stats);
			for (const stat of stats) if (!this._statsMap.has(stat.data)) this._statsMap.set(stat.data, stat);
			walkTreeData(stat, (node, index, parent) => {
				if (parent) node.level = parent.level + 1;
			}, { childrenKey: CHILDREN });
			this.afterSetStat(stat, parent, index);
		},
		*iterateParent(stat, opt) {
			let t = opt !== null && opt !== void 0 && opt.withSelf ? stat : stat.parent;
			while (t) {
				yield t;
				t = t.parent;
			}
		},
		move(stat, parent, index) {
			if (this.has(stat)) {
				if (stat.parent === parent && this.getSiblings(stat).indexOf(stat) === index) return false;
				if (stat === parent) throw new Error("Can't move node to it self");
				if (parent && stat.level < parent.level) {
					let t;
					for (const item of this.iterateParent(parent)) if (item.level === stat.level) {
						t = item;
						break;
					}
					if (stat === t) throw new Error("Can't move node to its descendant");
				}
				this.remove(stat);
			}
			this._setPosition(stat, parent, index);
			return true;
		},
		/**
		* convert stat and its children to one-dimensional array
		* 转换节点和其后代节点为一维数组
		* @param stat
		* @returns
		*/
		_flat(stat) {
			const r = [];
			walkTreeData(stat, (child) => {
				r.push(child);
			}, { childrenKey: CHILDREN });
			return r;
		},
		/**
		* get count of stat and its all children
		* 统计节点和其后代节点数量
		* @param stat
		*/
		_count(stat) {
			return this._flat(stat).length;
		},
		getData(filter, root) {
			const { childrenKey } = this;
			const td = new TreeData([]);
			td.childrenKey = childrenKey;
			walkTreeData(root || this.stats, (stat, index, parent, path) => {
				let newData = {
					...stat.data,
					[childrenKey]: []
				};
				if (filter) newData = filter(newData);
				td.set(path, newData);
			}, { childrenKey: CHILDREN });
			return td.data;
		}
	};
	const utils = utilsBase;
	if (!utilsBase.noInitialization) utils.init();
	return utils;
}
var defaultOptions$1 = {
	childrenKey: "children",
	defaultOpen: false,
	statsHandler(stats) {
		return stats;
	},
	statsFlatHandler(statsFlat) {
		return statsFlat;
	},
	afterSetStat(stat, parent, index) {},
	afterRemoveStat(stat) {},
	statHandler(stat) {
		return stat;
	}
};
function statDefault() {
	return {
		isStat: true,
		hidden: false,
		checked: false,
		style: null,
		class: null,
		draggable: null,
		droppable: null
	};
}
var StatNotFoundError = class extends Error {
	constructor(message) {
		super(message);
		this.name = "StatNotFoundError";
	}
};
//#endregion
//#region node_modules/drag-event-service/dist/index.esm.js
/*!
* drag-event-service v2.0.0
* Author: phphe <phphe@outlook.com> (https://github.com/phphe)
* Homepage: null
* Released under the MIT License.
*/
var events = {
	start: ["mousedown", "touchstart"],
	move: ["mousemove", "touchmove"],
	end: ["mouseup", "touchend"]
};
var DragEventService = {
	isTouch(e) {
		return e.type && e.type.startsWith("touch");
	},
	_getStore(el) {
		if (!el._wrapperStore) el._wrapperStore = [];
		return el._wrapperStore;
	},
	on(el, name, handler, options) {
		const { args, mouseArgs, touchArgs } = resolveOptions(options);
		const store = this._getStore(el);
		const ts = this;
		const wrapper = function(e) {
			let mouse;
			if (ts.isTouch(e)) mouse = {
				x: e.changedTouches[0].pageX,
				y: e.changedTouches[0].pageY,
				pageX: e.changedTouches[0].pageX,
				pageY: e.changedTouches[0].pageY,
				clientX: e.changedTouches[0].clientX,
				clientY: e.changedTouches[0].clientY,
				screenX: e.changedTouches[0].screenX,
				screenY: e.changedTouches[0].screenY
			};
			else {
				mouse = {
					x: e.pageX,
					y: e.pageY,
					pageX: e.pageX,
					pageY: e.pageY,
					clientX: e.clientX,
					clientY: e.clientY,
					screenX: e.screenX,
					screenY: e.screenY
				};
				if (name === "start" && e.which !== 1) return;
			}
			return handler.call(this, e, mouse);
		};
		store.push({
			handler,
			wrapper
		});
		on.call(null, el, events[name][0], wrapper, ...[...args, ...mouseArgs]);
		on.call(null, el, events[name][1], wrapper, ...[...args, ...touchArgs]);
	},
	off(el, name, handler, options) {
		const { args, mouseArgs, touchArgs } = resolveOptions(options);
		const store = this._getStore(el);
		for (let i = store.length - 1; i >= 0; i--) {
			const { handler: handler2, wrapper } = store[i];
			if (handler === handler2) {
				off.call(null, el, events[name][0], wrapper, ...[...args, ...mouseArgs]);
				off.call(null, el, events[name][1], wrapper, ...[...args, ...mouseArgs]);
				store.splice(i, 1);
			}
		}
	}
};
function resolveOptions(options) {
	if (!options) options = {};
	return {
		args: options.args || [],
		mouseArgs: options.mouseArgs || [],
		touchArgs: options.touchArgs || []
	};
}
//#endregion
//#region node_modules/@he-tree/dnd-utils/dist/index.esm.js
/*!
* @he-tree/dnd-utils v0.1.0-alpha.4
* Author: phphe <phphe@outlook.com> (https://github.com/phphe)
* Homepage: null
* Released under the MIT License.
*/
var instances = /* @__PURE__ */ new Map();
var context = {
	triggerElement: null,
	dragElement: null,
	internal: false,
	dropEffect: "none",
	preventDefault: false
};
var ctx = context;
function syncDropEffect(e) {
	if (e.dataTransfer) e.dataTransfer.dropEffect = ctx.dropEffect;
}
function extendedDND(root) {
	let options = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
	if (instances.has(root)) throw "Already registered on specified element";
	const opt = { ...options };
	const ins = opt;
	objectAssignIfNoKey(opt, defaultOptions);
	DragEventService.on(root, "start", beforeDragStart, { touchArgs: [{ passive: true }] });
	DragEventService.on(root, "end", onClickEnd);
	function beforeDragStart(e) {
		var _opt$beforeDragStart;
		const node = e.target;
		if (node.nodeType === Node.ELEMENT_NODE) ctx.triggerElement = node;
		const el = node;
		if (opt.ingoreHTMLTags && el.tagName) {
			if (opt.ingoreHTMLTags.find((tag) => tag.toUpperCase() === el.tagName)) return;
		}
		const dragElement = (_opt$beforeDragStart = opt.beforeDragStart) === null || _opt$beforeDragStart === void 0 ? void 0 : _opt$beforeDragStart.call(opt, e);
		if (dragElement) {
			dragElement.setAttribute("draggable", "true");
			ctx.dragElement = dragElement;
		}
	}
	function onClickEnd(e) {
		if (ctx.dragElement) ctx.dragElement.removeAttribute("draggable");
	}
	function onDragStart(e) {
		var _opt$onDragStart;
		ctx.internal = true;
		(_opt$onDragStart = opt.onDragStart) === null || _opt$onDragStart === void 0 || _opt$onDragStart.call(opt, e);
		syncDropEffect(e);
	}
	function onDrag(e) {
		var _opt$onDrag;
		(_opt$onDrag = opt.onDrag) === null || _opt$onDrag === void 0 || _opt$onDrag.call(opt, e);
		syncDropEffect(e);
	}
	function onDragEnd(e) {
		var _opt$onDragEnd;
		(_opt$onDragEnd = opt.onDragEnd) === null || _opt$onDragEnd === void 0 || _opt$onDragEnd.call(opt, e);
		ctx.internal = false;
		if (ctx.dragElement) ctx.dragElement.removeAttribute("draggable");
		ctx.triggerElement = null;
		ctx.dragElement = null;
		ctx.dropEffect = "none";
	}
	const destroyDropZoneListeners = extendedDropZone(root, {
		onDragLeave(e) {
			var _opt$onDragLeave;
			(_opt$onDragLeave = opt.onDragLeave) === null || _opt$onDragLeave === void 0 || _opt$onDragLeave.call(opt, e);
			syncDropEffect(e);
		},
		onDragEnter(e) {
			var _opt$onDragEnter;
			ins.ifPreventDefault(e) && e.preventDefault();
			(_opt$onDragEnter = opt.onDragEnter) === null || _opt$onDragEnter === void 0 || _opt$onDragEnter.call(opt, e);
			syncDropEffect(e);
		},
		onDragOver(e) {
			var _opt$onDragOver;
			ins.ifPreventDefault(e) && e.preventDefault();
			(_opt$onDragOver = opt.onDragOver) === null || _opt$onDragOver === void 0 || _opt$onDragOver.call(opt, e);
			syncDropEffect(e);
		},
		onDrop(e) {
			var _opt$onDrop;
			ins.ifPreventDefault(e) && e.preventDefault();
			(_opt$onDrop = opt.onDrop) === null || _opt$onDrop === void 0 || _opt$onDrop.call(opt, e);
		},
		onEnter(e) {
			var _opt$onEnter;
			(_opt$onEnter = opt.onEnter) === null || _opt$onEnter === void 0 || _opt$onEnter.call(opt, e);
		},
		onLeave(e) {
			var _opt$onLeave;
			(_opt$onLeave = opt.onLeave) === null || _opt$onLeave === void 0 || _opt$onLeave.call(opt, e);
		}
	});
	on(root, "dragstart", onDragStart);
	on(root, "drag", onDrag);
	on(root, "dragend", onDragEnd);
	const destroy = () => {
		DragEventService.off(root, "start", beforeDragStart, { touchArgs: [{ passive: true }] });
		off(root, "dragstart", onDragStart);
		off(root, "drag", onDrag);
		off(root, "dragend", onDragEnd);
		destroyDropZoneListeners();
		instances.delete(root);
	};
	Object.assign(opt, {
		root,
		destroy
	});
	instances.set(root, ins);
	return ins;
}
var defaultOptions = {
	ingoreHTMLTags: [
		"INPUT",
		"TEXTAREA",
		"SELECT",
		"OPTGROUP",
		"OPTION"
	],
	ifPreventDefault(event) {
		if (context.dragElement) return true;
		return ctx.preventDefault;
	},
	beforeDragStart(event) {},
	onDragStart(event) {},
	onDrag(event) {},
	onDragEnter(event) {},
	onDragLeave(event) {},
	onDragOver(event) {},
	onDragEnd(event) {},
	onDrop(event) {}
};
var justEnteredTarget = null;
var setJustEnteredTarget = (el) => {
	justEnteredTarget = el;
	setTimeout(() => {
		justEnteredTarget = null;
	}, 20);
};
function extendedDropZone(el) {
	let opt = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
	const dropZone = el;
	let entered = false;
	const onEnter = (e) => {
		var _opt$onEnter2;
		entered = true;
		(_opt$onEnter2 = opt.onEnter) === null || _opt$onEnter2 === void 0 || _opt$onEnter2.call(opt, e);
		endListeners.resume();
	};
	const onDragEnter = (e) => {
		var _opt$onDragEnter2;
		setJustEnteredTarget(e.target);
		(_opt$onDragEnter2 = opt.onDragEnter) === null || _opt$onDragEnter2 === void 0 || _opt$onDragEnter2.call(opt, e);
		if (!entered) onEnter(e);
	};
	const onDragOver = (e) => {
		var _opt$onDragOver2;
		if (!entered) onEnter(e);
		(_opt$onDragOver2 = opt.onDragOver) === null || _opt$onDragOver2 === void 0 || _opt$onDragOver2.call(opt, e);
	};
	const onDragLeave = (e) => {
		var _opt$onDragLeave2;
		(_opt$onDragLeave2 = opt.onDragLeave) === null || _opt$onDragLeave2 === void 0 || _opt$onDragLeave2.call(opt, e);
		const doLeave = function() {
			var _opt$onLeave2;
			let event = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : e;
			entered = false;
			(_opt$onLeave2 = opt.onLeave) === null || _opt$onLeave2 === void 0 || _opt$onLeave2.call(opt, event);
			endListeners.stop();
		};
		const justEnter = justEnteredTarget;
		justEnteredTarget = null;
		if (justEnter && isDescendantOf(justEnter, dropZone));
		else doLeave();
	};
	const onDrop = (e) => {
		var _opt$onDrop2;
		(_opt$onDrop2 = opt.onDrop) === null || _opt$onDrop2 === void 0 || _opt$onDrop2.call(opt, e);
	};
	const onEndBeforeLeave = (e) => {
		var _opt$onEndBeforeLeave;
		if (e instanceof KeyboardEvent) {
			if (e.key !== "Escape") return;
		}
		entered = false;
		endListeners.stop();
		(_opt$onEndBeforeLeave = opt.onEndBeforeLeave) === null || _opt$onEndBeforeLeave === void 0 || _opt$onEndBeforeLeave.call(opt, e);
	};
	const endListeners = extendedListen([
		[
			el,
			"drop",
			onEndBeforeLeave
		],
		[
			window,
			"mouseup",
			onEndBeforeLeave
		],
		[
			window,
			"touchend",
			onEndBeforeLeave
		],
		[
			window,
			"keydown",
			onEndBeforeLeave
		]
	]);
	endListeners.stop();
	const resume = () => {
		on(el, "dragenter", onDragEnter);
		on(el, "dragover", onDragOver);
		on(el, "dragleave", onDragLeave);
		on(el, "drop", onDrop);
	};
	const destroy = () => {
		off(el, "dragenter", onDragEnter);
		off(el, "dragover", onDragOver);
		off(el, "dragleave", onDragLeave);
		off(el, "drop", onDrop);
		endListeners.stop();
	};
	resume();
	return destroy;
}
//#endregion
//#region node_modules/@he-tree/vue/dist/v3/index.mjs
/*!
* @he-tree/vue
* Author: phphe <phphe@outlook.com> (https://github.com/phphe)
* Homepage: https://hetree.phphe.com/
* Released under the MIT License.
*/
var Q = !1;
var Ee = () => {
	Q = !0, setTimeout(() => {
		Q = !1;
	}, 100);
}, Pe = defineComponent({
	props: [
		"stat",
		"rtl",
		"btt",
		"indent",
		"table",
		"treeLine",
		"treeLineOffset",
		"processor",
		"activeDescendant",
		"isPlaceholder"
	],
	emits: [
		"open",
		"close",
		"check"
	],
	setup(e, { emit: n }) {
		const t = computed(() => ({ [e.rtl ? "paddingRight" : "paddingLeft"]: e.indent * (e.stat.level - 1) + "px" }));
		watch(() => e.stat.checked, (i) => {
			Q || e.processor.afterOneCheckChanged(e.stat) && n("check", e.stat);
		}), watch(() => e.stat.open, (i) => {
			Q || (n(i ? "open" : "close", e.stat), Ee());
		});
		const a = computed(() => {
			const i = [], o = (g) => {
				var u;
				if (g.parent) {
					let f = (u = g.parent) == null ? void 0 : u.children.indexOf(g);
					do {
						f++;
						let k = g.parent.children[f];
						if (k) {
							if (!k.hidden) return !0;
						} else break;
					} while (!0);
				}
				return !1;
			}, d = e.rtl ? "right" : "left", s = e.btt ? "top" : "bottom";
			let c = e.stat;
			for (; c;) {
				let g = (c.level - 2) * e.indent + e.treeLineOffset;
				const u = o(c), f = () => {
					i.push({ style: {
						[d]: g + "px",
						[s]: u ? 0 : "50%"
					} });
				};
				c === e.stat ? c.level > 1 && f() : u && f(), c = c.parent;
			}
			return i;
		}), h = computed(() => {
			let i = (e.stat.level - 2) * e.indent + e.treeLineOffset;
			return { [e.rtl ? "right" : "left"]: i + "px" };
		}), p = computed(() => {
			var i;
			return ((i = e.stat.parent) == null ? void 0 : i.children) || e.processor.stats;
		}), w = computed(() => p.value.length), m = computed(() => p.value.indexOf(e.stat) + 1);
		return {
			indentStyle: t,
			vLines: a,
			hLineStyle: h,
			ariaAttrs: computed(() => {
				var c;
				if (e.isPlaceholder) return { "aria-hidden": "true" };
				const i = e.stat, o = i.children && i.children.length > 0, d = {
					role: "treeitem",
					"aria-level": i.level,
					"aria-setsize": w.value,
					"aria-posinset": m.value,
					tabindex: i === e.activeDescendant ? 0 : -1
				};
				o && (d["aria-expanded"] = i.open ? "true" : "false"), i.checked === !0 ? d["aria-checked"] = "true" : i.checked === 0 ? d["aria-checked"] = "mixed" : i.checked === !1 && i.checked;
				const s = i.ariaLabel ?? ((c = i.data) == null ? void 0 : c.ariaLabel);
				return s && (d["aria-label"] = s), i.draggable === !1 && (d["aria-disabled"] = "true"), d;
			})
		};
	}
}), ce = (e, n) => {
	const t = e.__vccOpts || e;
	for (const [a, h] of n) t[a] = h;
	return t;
}, Te = { class: "tree-node-inner" };
function Ve(e, n, t, a, h, p) {
	return e.table ? (openBlock(), createElementBlock("tr", mergeProps({
		key: 1,
		class: "tree-node",
		ref: "el"
	}, e.ariaAttrs), [renderSlot(e.$slots, "default", { indentStyle: e.indentStyle })], 16)) : (openBlock(), createElementBlock("div", mergeProps({
		key: 0,
		class: ["tree-node", { "tree-node--with-tree-line": e.treeLine }],
		style: e.indentStyle,
		ref: "el"
	}, e.ariaAttrs), [e.treeLine ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [(openBlock(!0), createElementBlock(Fragment, null, renderList(e.vLines, (w) => (openBlock(), createElementBlock("div", {
		class: "tree-line tree-vline",
		style: normalizeStyle(w.style)
	}, null, 4))), 256)), e.stat.level > 1 ? (openBlock(), createElementBlock("div", {
		key: 0,
		class: "tree-line tree-hline",
		style: normalizeStyle(e.hLineStyle)
	}, null, 4)) : createCommentVNode("", !0)], 64)) : createCommentVNode("", !0), createBaseVNode("div", Te, [renderSlot(e.$slots, "default", { indentStyle: e.indentStyle })])], 16));
}
var _e = /* @__PURE__ */ ce(Pe, [["render", Ve]]);
function Ae(e, n = {}) {
	return makeTreeProcessor(e, {
		...n,
		statHandler(a) {
			return this._statHandler2 && (a = this._statHandler2(a)), ie(n.statHandler, reactive(a));
		},
		statsHandler(a) {
			return ie(n.statsHandler, reactive(a));
		},
		statsFlatHandler(a) {
			return ie(n.statsFlatHandler, reactive(a));
		}
	});
}
function ie(e, n) {
	return e ? e(n) : n;
}
var Fe = defineComponent({
	components: {
		VirtualList: Se,
		TreeNode: _e
	},
	props: {
		value: {
			required: false,
			type: Array
		},
		modelValue: {
			required: true,
			type: Array
		},
		updateBehavior: {
			type: String,
			default: "modify"
		},
		processor: {
			type: Object,
			default: () => Ae([], { noInitialization: !0 })
		},
		childrenKey: {
			type: String,
			default: "children"
		},
		/**
		* for default slot. 用于默认插槽
		*/
		textKey: {
			type: String,
			default: "text"
		},
		/**
		* node indent. 节点缩进
		*/
		indent: {
			type: Number,
			default: 20
		},
		/**
		* Enable virtual list. 启用虚拟列表
		*/
		virtualization: {
			type: Boolean,
			default: !1
		},
		/**
		* Render count for virtual list at start. 虚拟列表初始渲染数量.
		*/
		virtualizationPrerenderCount: {
			type: Number,
			default: 20
		},
		/**
		* Open all nodes by default. 默认打开所有节点.
		*/
		defaultOpen: {
			type: Boolean,
			default: !0
		},
		statHandler: { type: Function },
		/**
		* From right to left. 由右向左显示.
		*/
		rtl: {
			type: Boolean,
			default: !1
		},
		/**
		* From bottom to top. 由下向上显示
		*/
		btt: {
			type: Boolean,
			default: !1
		},
		/**
		* Display as table
		*/
		table: {
			type: Boolean,
			default: !1
		},
		watermark: {
			type: Boolean,
			default: !1
		},
		nodeKey: {
			type: [String, Function],
			default: "index"
		},
		treeLine: {
			type: Boolean,
			default: !1
		},
		treeLineOffset: {
			type: Number,
			default: 8
		},
		ariaLabel: {
			type: String,
			default: "Tree"
		},
		i18n: {
			type: Object,
			default: () => ({})
		}
	},
	emits: [
		"update:modelValue",
		"click:node",
		"open:node",
		"close:node",
		"check:node",
		"beforeDragStart",
		"before-drag-start",
		"after-drop",
		"change",
		"enter",
		"leave"
	],
	data() {
		return {
			stats: [],
			statsFlat: [],
			dragNode: null,
			dragOvering: !1,
			placeholderData: {},
			placeholderColspan: 1,
			batchUpdateWaiting: !1,
			self: this,
			_ignoreValueChangeOnce: !1,
			activeDescendant: null,
			liveAnnouncement: null,
			ariaInstructionsId: "he-tree-inst-" + Math.random().toString(36).slice(2, 9)
		};
	},
	computed: {
		valueComputed() {
			return this.modelValue || [];
		},
		visibleStats() {
			const { statsFlat: e, isVisible: n } = this;
			let t = e;
			return this.btt && (t = t.slice(), t.reverse()), t.filter((a) => n(a));
		},
		rootChildren() {
			return this.stats;
		}
	},
	methods: {
		_emitValue(e) {
			this.$emit("update:modelValue", e);
		},
		/**
		* private method
		* @param value
		*/
		_updateValue(e) {
			return this.updateBehavior === "disabled" ? !1 : (e !== this.valueComputed && (this._ignoreValueChangeOnce = !0), this._emitValue(e), !0);
		},
		getStat: ae(S("getStat")),
		has: ae(S("has")),
		updateCheck: S("updateCheck"),
		getChecked: S("getChecked"),
		getUnchecked: S("getUnchecked"),
		openAll: S("openAll"),
		closeAll: S("closeAll"),
		openNodeAndParents: S("openNodeAndParents"),
		isVisible: S("isVisible"),
		move: we("move"),
		add: ae(we("add")),
		addMulti(e, n, t) {
			this.batchUpdate(() => {
				let a = t;
				for (const h of e) this.add(h, n, a), a != null && a++;
			});
		},
		remove: S("remove"),
		removeMulti(e) {
			let n = [...e];
			this.batchUpdate(() => {
				for (const t of n) this.remove(t);
			});
		},
		iterateParent: S("iterateParent"),
		getSiblings: S("getSiblings"),
		getData: S("getData"),
		_onKeydown(e) {},
		getRootEl() {
			return this.$refs.vtlist.listElRef;
		},
		batchUpdate(e) {
			const n = this.ignoreUpdate(e);
			return this.batchUpdateWaiting || this._updateValue(this.updateBehavior === "new" ? this.getData() : this.valueComputed), n;
		},
		ignoreUpdate(e) {
			const n = this.batchUpdateWaiting;
			this.batchUpdateWaiting = !0;
			const t = e();
			return this.batchUpdateWaiting = n, t;
		}
	},
	watch: {
		processor: {
			immediate: !0,
			handler(e) {
				if (e) {
					const n = (t) => {
						if (t) {
							const { childrenKey: a } = this;
							return t[a] || (t[a] = []), t[a];
						} else return this.valueComputed;
					};
					e._statHandler2 = this.statHandler ? (t) => t.data === this.placeholderData ? t : this.statHandler(t) : null, e.afterSetStat = (t, a, h) => {
						const { childrenKey: p, updateBehavior: w } = this;
						let m = this.valueComputed;
						if (w === "new") {
							if (this.batchUpdateWaiting) return;
							m = this.getData();
						} else if (w === "modify") {
							const y = n(a == null ? void 0 : a.data);
							y.includes(t.data) || y.splice(h, 0, t.data);
						}
						this.batchUpdateWaiting || this._updateValue(m);
					}, e.afterRemoveStat = (t) => {
						var w;
						const { childrenKey: a, updateBehavior: h } = this;
						let p = this.valueComputed;
						if (h === "new") {
							if (this.batchUpdateWaiting) return;
							p = this.getData();
						} else if (h === "modify") arrayRemove(n((w = t.parent) == null ? void 0 : w.data), t.data);
						this.batchUpdateWaiting || this._updateValue(p);
					};
				}
				e.initialized || (e.data = this.valueComputed, Object.assign(e, objectOnly(this, ["childrenKey", "defaultOpen"])), e.init(), e.updateCheck()), this.stats = e.stats, this.statsFlat = e.statsFlat, e.data !== this.valueComputed && this._updateValue(e.data);
			}
		},
		visibleStats: { handler(e) {
			this.activeDescendant && !e.includes(this.activeDescendant) && (this.activeDescendant = e.length > 0 ? e[0] : null), !this.activeDescendant && e.length > 0 && (this.activeDescendant = e[0]);
		} },
		valueComputed: { handler(e) {
			if (this.dragOvering || this.dragNode || this._ignoreValueChangeOnce) this._ignoreValueChangeOnce = !1;
			else {
				const { processor: t } = this;
				t.data = e, t.init(), this.stats = t.stats, this.statsFlat = t.statsFlat;
			}
		} }
	},
	created() {},
	mounted() {
		typeof window < "u" && (this.watermark === !1 && (window._heTreeWatermarkDisabled = !0), this.watermark && !window._heTreeWatermarkDisabled && (window._heTreeWatermark || (window._heTreeWatermark = !0, console.log("%c[he-tree] Vue tree component:  https://hetree.phphe.com", "color:#0075ff; font-size:14px;"))));
	}
});
function S(e) {
	return function(...n) {
		return this.processor[e](...n);
	};
}
function we(e) {
	return function(...n) {
		return this.batchUpdate(() => this.processor[e](...n));
	};
}
function ae(e) {
	return function(n, ...t) {
		return n && (n = reactive(n)), e.call(this, n, ...t);
	};
}
var Re = {
	key: 0,
	class: "drag-placeholder he-tree-drag-placeholder"
}, Ke = ["colspan"], Ue = { class: "drag-placeholder he-tree-drag-placeholder" }, Me = {
	key: 0,
	class: "sr-only",
	"aria-live": "polite",
	"aria-atomic": "true"
}, He = ["id"];
function We(e, n, t, a, h, p) {
	const w = resolveComponent("TreeNode"), m = resolveComponent("VirtualList");
	return openBlock(), createBlock(m, {
		class: normalizeClass(["he-tree", {
			"he-tree--rtl rtl": e.rtl,
			"he-tree--drag-overing drag-overing": e.dragOvering
		}]),
		role: "tree",
		"aria-label": e.ariaLabel,
		"aria-describedby": e.ariaInstructionsId,
		onKeydown: e._onKeydown,
		ref: "vtlist",
		items: e.visibleStats,
		disabled: !e.virtualization,
		table: e.table,
		itemKey: e.nodeKey
	}, {
		prepend: withCtx(() => [renderSlot(e.$slots, "prepend", { tree: e.self })]),
		default: withCtx(({ item: y, index: i }) => [createVNode(w, {
			"vt-index": i,
			class: normalizeClass([y.class, {
				"drag-placeholder-wrapper": y.data === e.placeholderData,
				"dragging-node": y === e.dragNode
			}]),
			style: normalizeStyle(y.style),
			stat: y,
			rtl: e.rtl,
			btt: e.btt,
			indent: e.indent,
			table: e.table,
			treeLine: e.treeLine,
			treeLineOffset: e.treeLineOffset,
			processor: e.processor,
			activeDescendant: e.activeDescendant,
			isPlaceholder: y.data === e.placeholderData,
			onClick: (o) => e.$emit("click:node", y),
			onOpen: n[0] || (n[0] = (o) => e.$emit("open:node", o)),
			onClose: n[1] || (n[1] = (o) => e.$emit("close:node", o)),
			onCheck: n[2] || (n[2] = (o) => e.$emit("check:node", o))
		}, {
			default: withCtx(({ indentStyle: o }) => [y.data === e.placeholderData ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [e.table ? (openBlock(), createElementBlock("td", {
				key: 1,
				style: normalizeStyle(o),
				colspan: e.placeholderColspan
			}, [createBaseVNode("div", Ue, [renderSlot(e.$slots, "placeholder", { tree: e.self })])], 12, Ke)) : (openBlock(), createElementBlock("div", Re, [renderSlot(e.$slots, "placeholder", { tree: e.self })]))], 64)) : renderSlot(e.$slots, "default", {
				key: 1,
				node: y.data,
				stat: y,
				indentStyle: o,
				tree: e.self
			}, () => [createTextVNode(toDisplayString(y.data[e.textKey]), 1)])]),
			_: 2
		}, 1032, [
			"vt-index",
			"class",
			"style",
			"stat",
			"rtl",
			"btt",
			"indent",
			"table",
			"treeLine",
			"treeLineOffset",
			"processor",
			"activeDescendant",
			"isPlaceholder",
			"onClick"
		])]),
		append: withCtx(() => {
			var y;
			return [
				renderSlot(e.$slots, "append", { tree: e.self }),
				e.liveAnnouncement != null ? (openBlock(), createElementBlock("div", Me, toDisplayString(e.liveAnnouncement), 1)) : createCommentVNode("", !0),
				(y = e.i18n) != null && y.instructions ? (openBlock(), createElementBlock("div", {
					key: 1,
					id: e.ariaInstructionsId,
					class: "sr-only"
				}, toDisplayString(e.i18n.instructions), 9, He)) : createCommentVNode("", !0)
			];
		}),
		_: 3
	}, 8, [
		"class",
		"aria-label",
		"aria-describedby",
		"onKeydown",
		"items",
		"disabled",
		"table",
		"itemKey"
	]);
}
var ze = /* @__PURE__ */ ce(Fe, [["render", We]]);
var l = null, r = null, G, W, F, q, le, C, de, st = {
	get startInfo() {
		return G;
	},
	get targetInfo() {
		return W;
	},
	get dragNode() {
		return C;
	},
	get startTree() {
		return l;
	},
	get targetTree() {
		return r;
	},
	get closestNode() {
		return de;
	}
}, je = defineComponent({
	extends: ze,
	props: {
		triggerClass: { type: [String, Array] },
		disableDrag: Boolean,
		disableDrop: Boolean,
		eachDraggable: { type: Function },
		eachDroppable: { type: Function },
		rootDroppable: {
			type: [Boolean, Function],
			default: !0
		},
		/**
		* open closed node when drag over
		*/
		dragOpen: {
			type: Boolean,
			default: !0
		},
		dragOpenDelay: {
			type: Number,
			default: 0
		},
		/**
		* e.g.: you can load children by ajax in the hook
		*/
		beforeDragOpen: { type: Function },
		resolveStartMovePoint: { type: [String, Function] },
		/**
		* if remove placeholder when drag leave a tree
		*/
		keepPlaceholder: { type: Boolean },
		/**
		* prevent drop if greater than maxLevel
		*/
		maxLevel: { type: Number },
		/**
		* copy when drag
		*/
		dragCopy: { type: Boolean },
		/**
		* return new data when drag copy
		*/
		dragCopyDataHandler: { type: Function },
		onExternalDragOver: { type: Function },
		externalDataHandler: { type: Function },
		ondragstart: { type: Function },
		dragOverThrottleInterval: {
			type: Number,
			default: 0
		}
	},
	data() {
		return { treeDraggableInstance: null };
	},
	computed: {},
	methods: {
		getNodeByElement(e) {
			const n = e.getAttribute("vt-index");
			return n == null ? null : this.visibleStats[n];
		},
		isDraggable(e) {
			if (this.disableDrag) return !1;
			if (e.draggable != null) return e.draggable;
			if (this.eachDraggable) {
				const t = this.eachDraggable(e);
				if (t != null) return t;
			}
			const { parent: n } = e;
			return n ? this.isDraggable(n) : !0;
		},
		isDroppable(e) {
			if (this.disableDrop) return !1;
			if (!e) return resolveValueOrGettter(this.rootDroppable, [this, l]);
			if (e.droppable != null) return e.droppable;
			if (this.eachDroppable) {
				const t = this.eachDroppable(e);
				if (t != null) return t;
			}
			const { parent: n } = e;
			return n ? this.isDroppable(n) : !0;
		},
		_eachDroppable() {
			var e;
			return resolveValueOrGettter((e = this._isDragCopy) == null ? void 0 : e.call(this), [this]);
		},
		_focusNode(e) {
			this.activeDescendant = e, this.$nextTick(() => {
				this.getRootEl().querySelector(".tree-node[tabindex=\"0\"]")?.focus();
			});
		},
		_announce(e) {
			this.liveAnnouncement = null, this.$nextTick(() => {
				this.liveAnnouncement = e;
			});
		},
		_getVisibleStats() {
			return this.visibleStats.filter((e) => e.data !== this.placeholderData);
		},
		_onKeydown(e) {
			var w, m, y, i;
			const n = e.target.closest(".tree-node");
			if (!n) return;
			const t = this.getNodeByElement(n);
			if (!t || t.data === this.placeholderData) return;
			const a = this._getVisibleStats(), h = a.indexOf(t);
			if (h === -1) return;
			let p = !1;
			if (e.altKey && !this.disableDrag) {
				const o = t.parent ? t.parent.children : this.stats, d = o.indexOf(t);
				switch (e.key) {
					case "ArrowUp":
						if (d > 0) {
							this.move(t, t.parent || null, d - 1), this.$emit("change");
							const s = t.parent ? t.parent.children : this.stats, c = s.indexOf(t);
							this._announce((w = this.i18n) != null && w.movedToPosition ? this.i18n.movedToPosition(c + 1, s.length) : `Moved to position ${c + 1} of ${s.length}`), this._focusNode(t);
						}
						p = !0;
						break;
					case "ArrowDown":
						if (d < o.length - 1) {
							this.move(t, t.parent || null, d + 1), this.$emit("change");
							const s = t.parent ? t.parent.children : this.stats, c = s.indexOf(t);
							this._announce((m = this.i18n) != null && m.movedToPosition ? this.i18n.movedToPosition(c + 1, s.length) : `Moved to position ${c + 1} of ${s.length}`), this._focusNode(t);
						}
						p = !0;
						break;
					case "ArrowLeft":
						if (t.parent) {
							const s = t.parent.parent || null, g = (s ? s.children : this.stats).indexOf(t.parent);
							this.move(t, s, g + 1), this.$emit("change");
							const u = s ? s.children : this.stats, f = u.indexOf(t);
							this._announce((y = this.i18n) != null && y.outdentedToLevel ? this.i18n.outdentedToLevel(t.level, f + 1, u.length) : `Outdented to level ${t.level}, position ${f + 1} of ${u.length}`), this._focusNode(t);
						}
						p = !0;
						break;
					case "ArrowRight":
						if (d > 0) {
							const s = o[d - 1];
							if (this.isDroppable(s)) {
								s.open = !0;
								const c = s.children.filter((g) => g !== t).length;
								this.move(t, s, c), this.$emit("change"), this._announce((i = this.i18n) != null && i.indentedToLevel ? this.i18n.indentedToLevel(t.level, s.children.indexOf(t) + 1, s.children.length) : `Indented to level ${t.level}, position ${s.children.indexOf(t) + 1} of ${s.children.length}`), this._focusNode(t);
							}
						}
						p = !0;
						break;
				}
			} else if (!e.altKey && !e.ctrlKey && !e.metaKey) switch (e.key) {
				case "ArrowUp":
					h > 0 && this._focusNode(a[h - 1]), p = !0;
					break;
				case "ArrowDown":
					h < a.length - 1 && this._focusNode(a[h + 1]), p = !0;
					break;
				case "ArrowRight":
					if (t.children && t.children.length > 0 && !t.open) t.open = !0;
					else if (t.children && t.children.length > 0 && t.open) {
						const o = t.children.find((d) => !d.hidden);
						o && this._focusNode(o);
					}
					p = !0;
					break;
				case "ArrowLeft":
					t.children && t.children.length > 0 && t.open ? t.open = !1 : t.parent && this._focusNode(t.parent), p = !0;
					break;
				case "Home":
					a.length > 0 && this._focusNode(a[0]), p = !0;
					break;
				case "End":
					a.length > 0 && this._focusNode(a[a.length - 1]), p = !0;
					break;
				case "Enter":
				case " ":
					this.$emit("click:node", t), p = !0;
					break;
				case "*": {
					const o = t.parent ? t.parent.children : this.stats;
					for (const d of o) d.children && d.children.length > 0 && (d.open = !0);
					p = !0;
					break;
				}
			}
			p && (e.preventDefault(), e.stopPropagation());
		}
	},
	mounted() {
		const e = (i, o) => {
			let d = !0;
			return l && l !== this && (d = d && this._isMoved), this.table && !this._isDragCopy && (d = d && this._isDragCopy), d && (i.x !== o.x || i.y !== o.y);
		}, n = (i, o) => {
			r.ignoreUpdate(() => {
				if (!r.has(r.placeholderData)) {
					if (r.table) {
						let s = 0;
						const c = r.getRootEl().querySelector("tr");
						if (c) for (const { value: g } of iterateAll(c.children)) css(g, "display") !== "none" && (s += g.colSpan || 1);
						s < 1 && (s = 1), r.placeholderColspan = s;
					}
					r.add(r.placeholderData);
				}
				const d = r.getStat(r.placeholderData);
				r.move(d, i, o);
			});
		}, t = () => {
			const i = this;
			if (i.has(i.placeholderData)) return i.remove(i.getStat(i.placeholderData)), !0;
		}, a = (i) => {
			i ? context.dropEffect = l != null && l.dragCopy ? "copy" : "move" : context.dropEffect = "none";
		}, h = (i) => {
			const o = this;
			i ? a(!0) : o.keepPlaceholder ? o.has(o.placeholderData) || a(!1) : (t(), a(!1));
		};
		let p = {
			x: 0,
			y: 0
		};
		const w = this.getRootEl();
		let m = null;
		const y = () => {
			r != null && r.has(r.placeholderData) && r.ignoreUpdate(() => {
				r.remove(r.getStat(r.placeholderData)), l && (l.dragNode.hidden = !1, l.dragOvering = !1);
			});
		};
		this.treeDraggableInstance = extendedDND(w, {
			beforeDragStart: (i) => {
				if (!context.triggerElement) return;
				let o = this.triggerClass;
				(!o || o.length === 0) && (o = "tree-node");
				let d = toArrayIfNot(o);
				if (m = findParent(findParent(context.triggerElement, (c) => {
					if (hasClassIn(c, d)) return !0;
					if (hasClass(c, "tree-node")) return "break";
				}, {
					withSelf: !0,
					until: w
				}), (c) => {
					if (hasClass(c, "tree-node")) return !0;
				}, {
					withSelf: !0,
					until: w
				}), !!m) {
					if (C = this.getNodeByElement(m), !C) throw "Can't find drag node";
					if (this.isDraggable(C)) return this.$emit("before-drag-start", C), this.$emit("beforeDragStart", C), w;
				}
			},
			onDragStart: (i) => {
				var c, g, u;
				if (!m || !C) return;
				{
					const { x: f, y: k } = m.getBoundingClientRect(), { clientX: D, clientY: E } = i;
					(c = i.dataTransfer) == null || c.setDragImage(m, D - f, E - k);
				}
				le = {
					x: i.clientX,
					y: i.clientY
				}, l = this, l.dragNode = C, q = (() => {
					if (this.resolveStartMovePoint === "mouse") return {
						x: i.clientX,
						y: i.clientY
					};
					if (typeof this.resolveStartMovePoint == "function") return this.resolveStartMovePoint(m);
					{
						let f, k = 0;
						if (this.table) {
							let D = m.getBoundingClientRect();
							f = {
								x: D.x,
								y: D.y
							}, this.rtl && (f.x = D.right), k = D.height;
						} else if (!this.rtl) f = m.children[0].getBoundingClientRect().toJSON(), k = f.height;
						else {
							const D = m.children[0].getBoundingClientRect();
							f = {
								x: D.right,
								y: D.y
							}, k = D.height;
						}
						return this.btt && (f.y += k), f;
					}
				})(), this.dragOvering = !0;
				const d = l.getSiblings(l.dragNode), s = d.indexOf(C);
				G = {
					tree: l,
					dragNode: C,
					parent: C.parent,
					siblings: d,
					indexBeforeDrop: s
				}, r = this, (g = i.dataTransfer) == null || g.setData("text", `he-tree drag start at ${(/* @__PURE__ */ new Date()).toISOString()}`), l._eachDroppable() || setTimeout(() => {
					C.hidden = !0, n(C.parent, s + 1);
				}, 0), (u = this.ondragstart) == null || u.call(this, i);
			},
			onEnter: (i) => {
				this.$emit("enter", i);
			},
			onLeave: (i) => {
				F = null, this.dragOvering = !1, context.preventDefault = !1, t(), this.$emit("leave", i);
			},
			onDragOver: applyFinally((i) => {
				if (!l) {
					if (!this.onExternalDragOver || this.onExternalDragOver(i) === !1) return;
					context.preventDefault = !0;
				}
				if (this.dragOverThrottleInterval > 0) if (this._lastValidDragOver == null) this._lastValidDragOver = (/* @__PURE__ */ new Date()).getTime();
				else {
					let v = (/* @__PURE__ */ new Date()).getTime();
					if (v - this._lastValidDragOver > this.dragOverThrottleInterval) this._lastValidDragOver = v;
					else return;
				}
				const o = {
					x: i.clientX,
					y: i.clientY
				}, d = e(o, p);
				if (p = o, !d) return;
				this.dragOvering = !0, r = this;
				const s = q ? {
					x: q.x + (o.x - le.x),
					y: q.y + (o.y - le.y)
				} : { ...o }, { btt: c, rtl: g } = r;
				if (r.disableDrop) {
					context.dropEffect = "none";
					return;
				}
				let u, f;
				const k = r.getRootEl().querySelectorAll(".tree-node"), D = [];
				k.forEach((v) => {
					!hasClassIn(v, ["drag-placeholder-wrapper", "dragging-node"]) && css(v, "display") !== "none" && D.push(v);
				});
				const E = binarySearch(D, (v) => getBoundingClientRect(v)[c ? "bottom" : "top"] - s.y, { returnNearestIfNoHit: !0 });
				let O = null, T, te;
				E.hit || (E.greater ? c || (O = E.index - 1, D[O] || O++) : c && (O = E.index + 1, D[O] || O--)), O ??= E.index, T = D[O], te = c ? D[O - 1] : D[O + 1], u = T && r.getNodeByElement(T), f = te && r.getNodeByElement(te);
				const { indent: j } = r, B = cacheFunction(() => {
					if (r.table) {
						let v = getBoundingClientRect(T).toJSON();
						const L = j * (u.level - 1);
						return g ? (v.width -= L, v.right -= L) : v.x += L, v;
					} else return getBoundingClientRect(T.firstElementChild);
				}).action, he = cacheFunction(() => c ? s.y > B().y + B().height / 2 : s.y < B().y + B().height / 2).action, ke = cacheFunction(() => c ? !T || O === D.length - 1 && he() : !T || O === 0 && he()).action, ue = cacheFunction(() => g ? s.x - (B().x + B().width) : B().x - s.x).action, Oe = cacheFunction(() => g ? s.x < B().x + B().width - j : s.x > B().x + j).action;
				let P;
				if (ke()) P = 1, u = null;
				else if (u) ue() > 0 ? P = u.level - Math.ceil(ue() / j) : Oe() ? P = u.level + 1 : P = u.level;
				else return;
				f && P < f.level && (P = f.level), (async () => {
					let v, L, V = !1, K = null;
					const M = async (N) => {
						if (N.open) return !0;
						if (r.dragOpen) if (r.dragOpenDelay) if (K = N, F === N) V = !0;
						else {
							let I = promisePin();
							F = N;
							const U = N;
							return setTimeout(async () => {
								U !== F ? (V = !0, I.resolve(!0)) : (r.beforeDragOpen && await r.beforeDragOpen(N), U !== F ? (V = !0, I.resolve(!0)) : (N.open = !0, K = null, I.resolve(!0)));
							}, r.dragOpenDelay), await I.promise;
						}
						else return r.beforeDragOpen && await r.beforeDragOpen(N), N.open = !0, !0;
						else return !1;
					}, H = async () => {
						if (r.isDroppable(u) && await M(u)) {
							if (V) return;
							v = u, L = null;
						} else return !1;
					}, fe = (N = P) => {
						let I = u, U = [];
						for (; I && I.level >= N;) I = I.parent || null, U.unshift(I);
						let pe = 0;
						for (const ve of U) {
							if (r.isDroppable(ve)) return v = ve, L = U[pe + 1] || u, !0;
							pe++;
						}
						return !1;
					};
					de = u || null, u ? P > u.level ? await H() === !1 && fe(u.level) : fe() === !1 && await H() : r.isDroppable(null) && (v = null), F = K;
					const ge = !!(!V && (v || v === null));
					return {
						cancelled: V,
						success: ge,
						parent: v,
						index: ge ? L ? (v ? v.children : r.stats).filter((N) => N.data !== r.placeholderData).indexOf(L) + 1 : 0 : -1
					};
				})().then((v) => {
					if (!v.cancelled) {
						if (!v.success) {
							h(!1);
							return;
						}
						if (r.maxLevel != null && r.maxLevel > 0) {
							let L = 1;
							if (l) {
								const K = l.dragNode;
								let M = 0;
								walkTreeData(K, (H) => {
									H.level > M && (M = H.level);
								}, { childrenKey: CHILDREN }), L = M - K.level + 1;
							}
							if (L + (v.parent ? v.parent.level : 0) > r.maxLevel) {
								h(!1);
								return;
							}
						}
						h(!0), n(v.parent, v.index);
					}
				});
			}, () => {}),
			onDrop: (i) => {
				r = this;
				const o = !l;
				if (!r) return;
				const d = l == null ? void 0 : l.dragNode;
				let s, c = (() => {
					var u;
					let g = !0;
					if (!r.has(r.placeholderData)) g = !1;
					else if (o) s = (u = this.externalDataHandler) == null ? void 0 : u.call(this, i), g = s != null;
					else if (!l.dragCopy) {
						const f = r.getStat(r.placeholderData);
						l === r && f.parent === d.parent && findTreeData(d, (k) => k === f);
					}
					return g;
				})();
				if (c) {
					const g = r.getStat(r.placeholderData), u = r.getSiblings(g);
					W = {
						tree: r,
						dragNode: d,
						parent: g.parent,
						siblings: u,
						indexBeforeDrop: u.indexOf(g)
					};
				}
				(() => {
					if (y(), c) {
						let g = W.indexBeforeDrop;
						l && !l.dragCopy && l === r && G.parent == W.parent && G.indexBeforeDrop < g && g--, l && l !== r && !l._eachDroppable() && l.batchUpdate(() => {
							l.remove(d), l.updateCheck();
						}), r.batchUpdate(() => {
							let u = l == null ? void 0 : l.dragNode, f;
							s ? f = s : l._eachDroppable() && (f = cloneTreeData(l.dragNode.data, { childrenKey: l.childrenKey }), l.dragCopyDataHandler && (f = l.dragCopyDataHandler(f))), f && (r.add(f), u = r.getStat(f)), r.move(u, W.parent, g), r.updateCheck();
						});
					}
					r.$emit("after-drop"), c && (l && (l.dragCopy || l.$emit("change")), r !== l && r.$emit("change"));
				})();
			},
			onDragEnd: (i) => {
				y(), l && (l.dragNode && (l.dragNode.hidden = !1), l.dragNode = null, l.dragOvering = !1, l = null), r = null, F = null, C = null, de = null;
			}
		});
	},
	unmounted() {
		var e;
		(e = this.treeDraggableInstance) == null || e.destroy();
	}
});
defineComponent({ props: { open: { type: Boolean } } });
//#endregion
export { st as n, walkTreeData as r, je as t };
