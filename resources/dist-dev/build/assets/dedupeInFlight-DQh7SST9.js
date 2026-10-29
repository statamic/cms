import { r as __toESM, t as __commonJSMin } from "./rolldown-runtime-SbdDWaPq.js";
import { A as h, At as normalizeProps, H as openBlock, J as resolveComponent, K as renderList, L as onBeforeUnmount, Mt as toDisplayString, P as mergeProps, T as createVNode, Y as resolveDirective, Z as toHandlers, _ as computed, b as createCommentVNode, c as vModelText, d as withModifiers, ht as markRaw, jt as normalizeStyle, k as guardReactiveProps, kt as normalizeClass, l as vShow, nt as watch, ot as withCtx, p as Fragment, q as renderSlot, r as TransitionGroup, st as withDirectives, tt as useTemplateRef, u as withKeys, v as createBaseVNode, w as createTextVNode, x as createElementBlock, y as createBlock, yt as ref, z as onMounted } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { K as axios, l as router, m as debounce } from "./index.esm-D1UivzAX.js";
import { $ as Item_default$3, $n as Card_default, $t as Item_default$1, At as Stack_default, Ft as MiddleEllipsis_default, Ht as Field_default, I as Container_default, Jt as Separator_default$1, L as Tabs_default, Pn as Select_default, Pt as wait, Qn as Heading_default, Qt as Label_default$1, St as Group_default, Vt as Header_default$1, Xn as Subheading_default, Xr as Group_default$1, Yn as Panel_default, Zn as Header_default, Zr as Button_default, Zt as Menu_default, a as injectListingContext, di as flatten, dn as Label_default, en as Dropdown_default, et as Group_default$2, fn as Item_default$2, g as Search_default, h as Filters_default, hi as Icon_default, hn as Context_default, i as Listing_default, kt as Modal_default, ln as Separator_default, o as Pagination_default, qt as Editable_default, ri as sortBy, s as Table_default$1, u as ItemActions_default, un as Menu_default$1, wt as Footer_default, x as CustomizeColumns_default, xt as Item_default, yt as Slider_default, zt as Input_default } from "./ui-BOQtKSPJ.js";
import { p as nanoid } from "./globals-BvjechXl.js";
import { N as __vitePreload, T as toast, b as preferences, g as keys } from "./api-0euGzY2a.js";
import { t as pick } from "./pick-BGNNxrds.js";
import { t as shortcutLabel } from "./shortcutLabel-jRIt5C8V.js";
import { t as HasPreferences_default } from "./HasPreferences-BtRP-cCV.js";
// resources/js/components/assets/Browser/Thumbnail.vue
var _sfc_main$14 = { props: {
	asset: Object,
	alt: {
		default: "",
		type: String
	},
	square: {
		default: false,
		type: Boolean
	}
} };
var _hoisted_1$12 = { class: "" };
var _hoisted_2$10 = ["src", "alt"];
var _hoisted_3$10 = ["src", "alt"];
function _sfc_render$13(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_file_icon = resolveComponent("file-icon");
	return openBlock(), createElementBlock("div", _hoisted_1$12, [$props.asset.thumbnail ? (openBlock(), createElementBlock("img", {
		key: 0,
		src: $props.asset.thumbnail,
		alt: $props.alt,
		class: normalizeClass(["asset-thumbnail mx-auto max-h-8 max-w-full rounded-sm", { "h-8 w-8 object-cover": $props.square }]),
		loading: "lazy",
		draggable: false,
		onError: _cache[0] || (_cache[0] = ($event) => $props.asset.thumbnail = null)
	}, null, 42, _hoisted_2$10)) : $props.asset.is_svg ? (openBlock(), createElementBlock("img", {
		key: 1,
		src: $props.asset.url,
		alt: $props.alt,
		class: "asset-thumbnail mx-auto h-8 max-w-full rounded-sm",
		loading: "lazy",
		draggable: false
	}, null, 8, _hoisted_3$10)) : (openBlock(), createBlock(_component_file_icon, {
		key: 2,
		extension: $props.asset.extension,
		class: "asset-thumbnail h-8 w-8 rounded-sm p-px"
	}, null, 8, ["extension"]))]);
}
var Thumbnail_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$14, [["render", _sfc_render$13], ["__file", "Thumbnail.vue"]]);
// resources/js/components/assets/Editor/FocalPointPreviewFrame.vue
var _sfc_main$13 = {
	props: [
		"x",
		"y",
		"z",
		"imageUrl",
		"imageDimensions"
	],
	data() {
		return { frameDimensions: {
			w: 100,
			h: 100
		} };
	},
	mounted() {
		const frame = this.$refs["frame"];
		this.frameDimensions = {
			w: frame.clientWidth,
			h: frame.clientHeight
		};
	},
	computed: {
		bgImageDimensions() {
			const ratio = ({ w, h }) => w / h;
			if (ratio(this.imageDimensions) > ratio(this.frameDimensions)) return {
				h: this.frameDimensions.h,
				w: this.frameDimensions.h / this.imageDimensions.h * this.imageDimensions.w
			};
			else return {
				w: this.frameDimensions.w,
				h: this.frameDimensions.w / this.imageDimensions.w * this.imageDimensions.h
			};
		},
		frameWidthPercent() {
			return this.frameDimensions.w / this.bgImageDimensions.w * 100;
		},
		frameHeightPercent() {
			return this.frameDimensions.h / this.bgImageDimensions.h * 100;
		},
		relOffsetLeft() {
			let ol = this.x - this.frameWidthPercent / 2;
			ol = Math.max(ol, 0);
			return Math.min(ol, 100 - this.frameWidthPercent);
		},
		offsetLeft() {
			return this.relOffsetLeft * this.bgImageDimensions.w / 100;
		},
		relOffsetTop() {
			let ot = this.y - this.frameHeightPercent / 2;
			ot = Math.max(ot, 0);
			return Math.min(ot, 100 - this.frameHeightPercent);
		},
		offsetTop() {
			return this.relOffsetTop * this.bgImageDimensions.h / 100;
		},
		backgroundPosition() {
			return `-${this.offsetLeft}px -${this.offsetTop}px`;
		},
		transformOrigin() {
			const origin = {
				x: (this.x - this.relOffsetLeft) / this.frameWidthPercent * 100,
				y: (this.y - this.relOffsetTop) / this.frameHeightPercent * 100
			};
			return `${origin.x}% ${origin.y}%`;
		}
	}
};
function _sfc_render$12(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", {
		class: "frame-image",
		ref: "frame",
		style: normalizeStyle({
			backgroundImage: `url('${encodeURI($props.imageUrl)}')`,
			backgroundPosition: $options.backgroundPosition,
			transform: `scale(${$props.z})`,
			transformOrigin: $options.transformOrigin
		})
	}, null, 4);
}
// resources/js/components/assets/Editor/FocalPointEditor.vue
var _sfc_main$12 = {
	components: {
		Card: Card_default,
		Heading: Heading_default,
		Subheading: Subheading_default,
		Button: Button_default,
		FocalPointPreviewFrame: /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$13, [["render", _sfc_render$12], ["__file", "FocalPointPreviewFrame.vue"]])
	},
	props: ["data", "image"],
	data() {
		return {
			x: 50,
			y: 50,
			z: 1,
			imageDimensions: null
		};
	},
	mounted() {
		const coords = (this.data || "50-50-1").split("-");
		this.x = Number(coords[0] ?? 50);
		this.y = Number(coords[1] ?? 50);
		this.z = Number(coords[2] ?? 1);
		this.clampPercent("x");
		this.clampPercent("y");
		this.clampZoom();
		this.saveKeyBinding = this.$keys.bindGlobal(["mod+s"], (e) => {
			e.preventDefault();
			this.select();
			this.$emit("save");
		});
		this.saveAndCloseKeyBinding = this.$keys.bindGlobal(["mod+shift+s"], (e) => {
			e.preventDefault();
			this.select();
			this.$emit("save", { close: true });
		});
	},
	beforeUnmount() {
		this.saveKeyBinding.destroy();
		this.saveAndCloseKeyBinding.destroy();
	},
	computed: { reticleSize() {
		if (!this.imageDimensions || !this.z) return 0;
		return Math.min(this.imageDimensions.w, this.imageDimensions.h) / this.z;
	} },
	methods: {
		setImageDimensions() {
			const image = this.$refs.image;
			this.imageDimensions = {
				w: image.clientWidth,
				h: image.clientHeight
			};
		},
		define(e) {
			var rect = e.target.getBoundingClientRect();
			var imageW = rect.width;
			var imageH = rect.height;
			var offsetX = e.clientX - rect.left;
			var offsetY = e.clientY - rect.top;
			this.x = Math.round(offsetX / imageW * 100);
			this.y = Math.round(offsetY / imageH * 100);
			this.clampPercent("x");
			this.clampPercent("y");
		},
		clampPercent(axis) {
			const value = Number(this[axis]);
			const safeValue = Number.isFinite(value) ? value : 50;
			this[axis] = Math.min(100, Math.max(0, Math.round(safeValue)));
		},
		clampZoom() {
			const value = Number(this.z);
			const clamped = Math.min(10, Math.max(1, Number.isFinite(value) ? value : 1));
			this.z = Math.round(clamped * 10) / 10;
		},
		select() {
			this.clampPercent("x");
			this.clampPercent("y");
			this.clampZoom();
			this.$emit("selected", this.x + "-" + this.y + "-" + this.z);
			this.close();
		},
		close() {
			this.$emit("closed");
		},
		reset() {
			this.x = 50;
			this.y = 50;
			this.z = 1;
		}
	}
};
var _hoisted_1$11 = { class: "focal-point" };
var _hoisted_2$9 = { class: "p-6" };
var _hoisted_3$9 = { class: "focal-point-image" };
var _hoisted_4$9 = ["src"];
var _hoisted_5$7 = { class: "mb-4 flex items-center justify-center text-sm" };
var _hoisted_6$7 = { class: "mx-2 flex items-center gap-1" };
var _hoisted_7$6 = { class: "mx-2 flex items-center gap-1" };
var _hoisted_8$5 = { class: "mx-2 flex items-center gap-1" };
var _hoisted_9$5 = { class: "px-4" };
var _hoisted_10$5 = { class: "mb-4 flex flex-wrap items-center justify-center gap-2" };
var _hoisted_11$5 = { class: "rounded-b bg-gray-100 p-4 text-center dark:bg-gray-850" };
function _sfc_render$11(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Heading = resolveComponent("Heading");
	const _component_Subheading = resolveComponent("Subheading");
	const _component_ui_input = resolveComponent("ui-input");
	const _component_Button = resolveComponent("Button");
	const _component_Card = resolveComponent("Card");
	const _component_focal_point_preview_frame = resolveComponent("focal-point-preview-frame");
	const _component_portal = resolveComponent("portal");
	return openBlock(), createBlock(_component_portal, { name: "focal-point" }, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$11, [createVNode(_component_Card, {
			class: "focal-point-toolbox",
			inset: ""
		}, {
			default: withCtx(() => [
				createBaseVNode("div", _hoisted_2$9, [
					createVNode(_component_Heading, { size: "xl" }, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Focal Point")), 1)]),
						_: 1
					}),
					createVNode(_component_Subheading, null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("messages.focal_point_instructions")), 1)]),
						_: 1
					}),
					createBaseVNode("div", _hoisted_3$9, [createBaseVNode("img", {
						ref: "image",
						src: $props.image,
						onClick: _cache[0] || (_cache[0] = (...args) => $options.define && $options.define(...args)),
						onLoad: _cache[1] || (_cache[1] = (...args) => $options.setImageDimensions && $options.setImageDimensions(...args))
					}, null, 40, _hoisted_4$9), createBaseVNode("div", {
						class: normalizeClass(["focal-point-reticle", { zoomed: $data.z > 1 }]),
						style: normalizeStyle({
							top: `${$data.y}%`,
							left: `${$data.x}%`,
							width: `${$options.reticleSize}px`,
							height: `${$options.reticleSize}px`,
							marginTop: `-${$options.reticleSize / 2}px`,
							marginLeft: `-${$options.reticleSize / 2}px`
						})
					}, null, 6)])
				]),
				createBaseVNode("div", _hoisted_5$7, [
					createBaseVNode("div", _hoisted_6$7, [
						_cache[8] || (_cache[8] = createBaseVNode("label", {
							class: "me-1",
							for: "focal-point-x"
						}, "X", -1)),
						createVNode(_component_ui_input, {
							id: "focal-point-x",
							modelValue: $data.x,
							"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $data.x = $event),
							modelModifiers: { number: true },
							type: "number",
							min: "0",
							max: "100",
							step: "1",
							size: "sm",
							onChange: _cache[3] || (_cache[3] = ($event) => $options.clampPercent("x"))
						}, null, 8, ["modelValue"]),
						_cache[9] || (_cache[9] = createBaseVNode("span", null, "%", -1))
					]),
					createBaseVNode("div", _hoisted_7$6, [
						_cache[10] || (_cache[10] = createBaseVNode("label", {
							class: "me-1",
							for: "focal-point-y"
						}, "Y", -1)),
						createVNode(_component_ui_input, {
							id: "focal-point-y",
							modelValue: $data.y,
							"onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => $data.y = $event),
							modelModifiers: { number: true },
							type: "number",
							min: "0",
							max: "100",
							step: "1",
							size: "sm",
							onChange: _cache[5] || (_cache[5] = ($event) => $options.clampPercent("y"))
						}, null, 8, ["modelValue"]),
						_cache[11] || (_cache[11] = createBaseVNode("span", null, "%", -1))
					]),
					createBaseVNode("div", _hoisted_8$5, [_cache[12] || (_cache[12] = createBaseVNode("label", {
						class: "me-1",
						for: "focal-point-z"
					}, "Z", -1)), createVNode(_component_ui_input, {
						id: "focal-point-z",
						modelValue: $data.z,
						"onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => $data.z = $event),
						modelModifiers: { number: true },
						type: "number",
						min: "1",
						max: "10",
						step: "0.1",
						size: "sm",
						onChange: $options.clampZoom
					}, null, 8, ["modelValue", "onChange"])])
				]),
				createBaseVNode("div", _hoisted_9$5, [withDirectives(createBaseVNode("input", {
					type: "range",
					"onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => $data.z = $event),
					min: "1",
					max: "10",
					step: "0.1",
					class: "mb-4 w-full"
				}, null, 512), [[
					vModelText,
					$data.z,
					void 0,
					{ number: true }
				]]), createBaseVNode("div", _hoisted_10$5, [
					createVNode(_component_Button, {
						text: _ctx.__("Cancel"),
						onClick: $options.close
					}, null, 8, ["text", "onClick"]),
					createVNode(_component_Button, {
						text: _ctx.__("Reset"),
						onClick: $options.reset
					}, null, 8, ["text", "onClick"]),
					createVNode(_component_Button, {
						variant: "primary",
						text: _ctx.__("Finish"),
						onClick: $options.select
					}, null, 8, ["text", "onClick"])
				])]),
				createBaseVNode("h6", _hoisted_11$5, toDisplayString(_ctx.__("messages.focal_point_previews_are_examples")), 1)
			]),
			_: 1
		}), (openBlock(), createElementBlock(Fragment, null, renderList(9, (n) => {
			return createBaseVNode("div", {
				key: n,
				class: normalizeClass(`frame frame-${n}`)
			}, [$data.imageDimensions ? (openBlock(), createBlock(_component_focal_point_preview_frame, {
				key: 0,
				x: $data.x,
				y: $data.y,
				z: $data.z,
				"image-url": $props.image,
				"image-dimensions": $data.imageDimensions
			}, null, 8, [
				"x",
				"y",
				"z",
				"image-url",
				"image-dimensions"
			])) : createCommentVNode("", true)], 2);
		}), 64))])]),
		_: 1
	});
}
var FocalPointEditor_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$12, [["render", _sfc_render$11], ["__file", "FocalPointEditor.vue"]]);
/*!
* Cropper.js v1.6.2
* https://fengyuanchen.github.io/cropperjs
*
* Copyright 2015-present Chen Fengyuan
* Released under the MIT license
*
* Date: 2024-04-21T07:43:05.335Z
*/
// node_modules/cropperjs/dist/cropper.css
var import_cropper = /* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin(((exports, module, this$4) => {
	(function(global, factory) {
		typeof exports === "object" && typeof module !== "undefined" ? module.exports = factory() : typeof define === "function" && define.amd ? define(factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, global.Cropper = factory());
	})(this$4, (function() {
		"use strict";
		function ownKeys(e, r) {
			var t = Object.keys(e);
			if (Object.getOwnPropertySymbols) {
				var o = Object.getOwnPropertySymbols(e);
				r && (o = o.filter(function(r) {
					return Object.getOwnPropertyDescriptor(e, r).enumerable;
				})), t.push.apply(t, o);
			}
			return t;
		}
		function _objectSpread2(e) {
			for (var r = 1; r < arguments.length; r++) {
				var t = null != arguments[r] ? arguments[r] : {};
				r % 2 ? ownKeys(Object(t), !0).forEach(function(r) {
					_defineProperty(e, r, t[r]);
				}) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function(r) {
					Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
				});
			}
			return e;
		}
		function _toPrimitive(t, r) {
			if ("object" != typeof t || !t) return t;
			var e = t[Symbol.toPrimitive];
			if (void 0 !== e) {
				var i = e.call(t, r || "default");
				if ("object" != typeof i) return i;
				throw new TypeError("@@toPrimitive must return a primitive value.");
			}
			return ("string" === r ? String : Number)(t);
		}
		function _toPropertyKey(t) {
			var i = _toPrimitive(t, "string");
			return "symbol" == typeof i ? i : i + "";
		}
		function _typeof(o) {
			"@babel/helpers - typeof";
			return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(o) {
				return typeof o;
			} : function(o) {
				return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
			}, _typeof(o);
		}
		function _classCallCheck(instance, Constructor) {
			if (!(instance instanceof Constructor)) throw new TypeError("Cannot call a class as a function");
		}
		function _defineProperties(target, props) {
			for (var i = 0; i < props.length; i++) {
				var descriptor = props[i];
				descriptor.enumerable = descriptor.enumerable || false;
				descriptor.configurable = true;
				if ("value" in descriptor) descriptor.writable = true;
				Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor);
			}
		}
		function _createClass(Constructor, protoProps, staticProps) {
			if (protoProps) _defineProperties(Constructor.prototype, protoProps);
			if (staticProps) _defineProperties(Constructor, staticProps);
			Object.defineProperty(Constructor, "prototype", { writable: false });
			return Constructor;
		}
		function _defineProperty(obj, key, value) {
			key = _toPropertyKey(key);
			if (key in obj) Object.defineProperty(obj, key, {
				value,
				enumerable: true,
				configurable: true,
				writable: true
			});
			else obj[key] = value;
			return obj;
		}
		function _toConsumableArray(arr) {
			return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread();
		}
		function _arrayWithoutHoles(arr) {
			if (Array.isArray(arr)) return _arrayLikeToArray(arr);
		}
		function _iterableToArray(iter) {
			if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter);
		}
		function _unsupportedIterableToArray(o, minLen) {
			if (!o) return;
			if (typeof o === "string") return _arrayLikeToArray(o, minLen);
			var n = Object.prototype.toString.call(o).slice(8, -1);
			if (n === "Object" && o.constructor) n = o.constructor.name;
			if (n === "Map" || n === "Set") return Array.from(o);
			if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
		}
		function _arrayLikeToArray(arr, len) {
			if (len == null || len > arr.length) len = arr.length;
			for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];
			return arr2;
		}
		function _nonIterableSpread() {
			throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
		}
		var IS_BROWSER = typeof window !== "undefined" && typeof window.document !== "undefined";
		var WINDOW = IS_BROWSER ? window : {};
		var IS_TOUCH_DEVICE = IS_BROWSER && WINDOW.document.documentElement ? "ontouchstart" in WINDOW.document.documentElement : false;
		var HAS_POINTER_EVENT = IS_BROWSER ? "PointerEvent" in WINDOW : false;
		var NAMESPACE = "cropper";
		var ACTION_ALL = "all";
		var ACTION_CROP = "crop";
		var ACTION_MOVE = "move";
		var ACTION_ZOOM = "zoom";
		var ACTION_EAST = "e";
		var ACTION_WEST = "w";
		var ACTION_SOUTH = "s";
		var ACTION_NORTH = "n";
		var ACTION_NORTH_EAST = "ne";
		var ACTION_NORTH_WEST = "nw";
		var ACTION_SOUTH_EAST = "se";
		var ACTION_SOUTH_WEST = "sw";
		var CLASS_CROP = "".concat(NAMESPACE, "-crop");
		var CLASS_DISABLED = "".concat(NAMESPACE, "-disabled");
		var CLASS_HIDDEN = "".concat(NAMESPACE, "-hidden");
		var CLASS_HIDE = "".concat(NAMESPACE, "-hide");
		var CLASS_INVISIBLE = "".concat(NAMESPACE, "-invisible");
		var CLASS_MODAL = "".concat(NAMESPACE, "-modal");
		var CLASS_MOVE = "".concat(NAMESPACE, "-move");
		var DATA_ACTION = "".concat(NAMESPACE, "Action");
		var DATA_PREVIEW = "".concat(NAMESPACE, "Preview");
		var DRAG_MODE_CROP = "crop";
		var DRAG_MODE_MOVE = "move";
		var DRAG_MODE_NONE = "none";
		var EVENT_CROP = "crop";
		var EVENT_CROP_END = "cropend";
		var EVENT_CROP_MOVE = "cropmove";
		var EVENT_CROP_START = "cropstart";
		var EVENT_DBLCLICK = "dblclick";
		var EVENT_TOUCH_START = IS_TOUCH_DEVICE ? "touchstart" : "mousedown";
		var EVENT_TOUCH_MOVE = IS_TOUCH_DEVICE ? "touchmove" : "mousemove";
		var EVENT_TOUCH_END = IS_TOUCH_DEVICE ? "touchend touchcancel" : "mouseup";
		var EVENT_POINTER_DOWN = HAS_POINTER_EVENT ? "pointerdown" : EVENT_TOUCH_START;
		var EVENT_POINTER_MOVE = HAS_POINTER_EVENT ? "pointermove" : EVENT_TOUCH_MOVE;
		var EVENT_POINTER_UP = HAS_POINTER_EVENT ? "pointerup pointercancel" : EVENT_TOUCH_END;
		var EVENT_READY = "ready";
		var EVENT_RESIZE = "resize";
		var EVENT_WHEEL = "wheel";
		var EVENT_ZOOM = "zoom";
		var MIME_TYPE_JPEG = "image/jpeg";
		var REGEXP_ACTIONS = /^e|w|s|n|se|sw|ne|nw|all|crop|move|zoom$/;
		var REGEXP_DATA_URL = /^data:/;
		var REGEXP_DATA_URL_JPEG = /^data:image\/jpeg;base64,/;
		var REGEXP_TAG_NAME = /^img|canvas$/i;
		var MIN_CONTAINER_WIDTH = 200;
		var MIN_CONTAINER_HEIGHT = 100;
		var DEFAULTS = {
			viewMode: 0,
			dragMode: DRAG_MODE_CROP,
			initialAspectRatio: NaN,
			aspectRatio: NaN,
			data: null,
			preview: "",
			responsive: true,
			restore: true,
			checkCrossOrigin: true,
			checkOrientation: true,
			modal: true,
			guides: true,
			center: true,
			highlight: true,
			background: true,
			autoCrop: true,
			autoCropArea: .8,
			movable: true,
			rotatable: true,
			scalable: true,
			zoomable: true,
			zoomOnTouch: true,
			zoomOnWheel: true,
			wheelZoomRatio: .1,
			cropBoxMovable: true,
			cropBoxResizable: true,
			toggleDragModeOnDblclick: true,
			minCanvasWidth: 0,
			minCanvasHeight: 0,
			minCropBoxWidth: 0,
			minCropBoxHeight: 0,
			minContainerWidth: MIN_CONTAINER_WIDTH,
			minContainerHeight: MIN_CONTAINER_HEIGHT,
			ready: null,
			cropstart: null,
			cropmove: null,
			cropend: null,
			crop: null,
			zoom: null
		};
		var TEMPLATE = "<div class=\"cropper-container\" touch-action=\"none\"><div class=\"cropper-wrap-box\"><div class=\"cropper-canvas\"></div></div><div class=\"cropper-drag-box\"></div><div class=\"cropper-crop-box\"><span class=\"cropper-view-box\"></span><span class=\"cropper-dashed dashed-h\"></span><span class=\"cropper-dashed dashed-v\"></span><span class=\"cropper-center\"></span><span class=\"cropper-face\"></span><span class=\"cropper-line line-e\" data-cropper-action=\"e\"></span><span class=\"cropper-line line-n\" data-cropper-action=\"n\"></span><span class=\"cropper-line line-w\" data-cropper-action=\"w\"></span><span class=\"cropper-line line-s\" data-cropper-action=\"s\"></span><span class=\"cropper-point point-e\" data-cropper-action=\"e\"></span><span class=\"cropper-point point-n\" data-cropper-action=\"n\"></span><span class=\"cropper-point point-w\" data-cropper-action=\"w\"></span><span class=\"cropper-point point-s\" data-cropper-action=\"s\"></span><span class=\"cropper-point point-ne\" data-cropper-action=\"ne\"></span><span class=\"cropper-point point-nw\" data-cropper-action=\"nw\"></span><span class=\"cropper-point point-sw\" data-cropper-action=\"sw\"></span><span class=\"cropper-point point-se\" data-cropper-action=\"se\"></span></div></div>";
		/**
		* Check if the given value is not a number.
		*/
		var isNaN = Number.isNaN || WINDOW.isNaN;
		/**
		* Check if the given value is a number.
		* @param {*} value - The value to check.
		* @returns {boolean} Returns `true` if the given value is a number, else `false`.
		*/
		function isNumber(value) {
			return typeof value === "number" && !isNaN(value);
		}
		/**
		* Check if the given value is a positive number.
		* @param {*} value - The value to check.
		* @returns {boolean} Returns `true` if the given value is a positive number, else `false`.
		*/
		var isPositiveNumber = function isPositiveNumber(value) {
			return value > 0 && value < Infinity;
		};
		/**
		* Check if the given value is undefined.
		* @param {*} value - The value to check.
		* @returns {boolean} Returns `true` if the given value is undefined, else `false`.
		*/
		function isUndefined(value) {
			return typeof value === "undefined";
		}
		/**
		* Check if the given value is an object.
		* @param {*} value - The value to check.
		* @returns {boolean} Returns `true` if the given value is an object, else `false`.
		*/
		function isObject(value) {
			return _typeof(value) === "object" && value !== null;
		}
		var hasOwnProperty = Object.prototype.hasOwnProperty;
		/**
		* Check if the given value is a plain object.
		* @param {*} value - The value to check.
		* @returns {boolean} Returns `true` if the given value is a plain object, else `false`.
		*/
		function isPlainObject(value) {
			if (!isObject(value)) return false;
			try {
				var _constructor = value.constructor;
				var prototype = _constructor.prototype;
				return _constructor && prototype && hasOwnProperty.call(prototype, "isPrototypeOf");
			} catch (error) {
				return false;
			}
		}
		/**
		* Check if the given value is a function.
		* @param {*} value - The value to check.
		* @returns {boolean} Returns `true` if the given value is a function, else `false`.
		*/
		function isFunction(value) {
			return typeof value === "function";
		}
		var slice = Array.prototype.slice;
		/**
		* Convert array-like or iterable object to an array.
		* @param {*} value - The value to convert.
		* @returns {Array} Returns a new array.
		*/
		function toArray(value) {
			return Array.from ? Array.from(value) : slice.call(value);
		}
		/**
		* Iterate the given data.
		* @param {*} data - The data to iterate.
		* @param {Function} callback - The process function for each element.
		* @returns {*} The original data.
		*/
		function forEach(data, callback) {
			if (data && isFunction(callback)) {
				if (Array.isArray(data) || isNumber(data.length)) toArray(data).forEach(function(value, key) {
					callback.call(data, value, key, data);
				});
				else if (isObject(data)) Object.keys(data).forEach(function(key) {
					callback.call(data, data[key], key, data);
				});
			}
			return data;
		}
		/**
		* Extend the given object.
		* @param {*} target - The target object to extend.
		* @param {*} args - The rest objects for merging to the target object.
		* @returns {Object} The extended object.
		*/
		var assign = Object.assign || function assign(target) {
			for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) args[_key - 1] = arguments[_key];
			if (isObject(target) && args.length > 0) args.forEach(function(arg) {
				if (isObject(arg)) Object.keys(arg).forEach(function(key) {
					target[key] = arg[key];
				});
			});
			return target;
		};
		var REGEXP_DECIMALS = /\.\d*(?:0|9){12}\d*$/;
		/**
		* Normalize decimal number.
		* Check out {@link https://0.30000000000000004.com/}
		* @param {number} value - The value to normalize.
		* @param {number} [times=100000000000] - The times for normalizing.
		* @returns {number} Returns the normalized number.
		*/
		function normalizeDecimalNumber(value) {
			var times = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 1e11;
			return REGEXP_DECIMALS.test(value) ? Math.round(value * times) / times : value;
		}
		var REGEXP_SUFFIX = /^width|height|left|top|marginLeft|marginTop$/;
		/**
		* Apply styles to the given element.
		* @param {Element} element - The target element.
		* @param {Object} styles - The styles for applying.
		*/
		function setStyle(element, styles) {
			var style = element.style;
			forEach(styles, function(value, property) {
				if (REGEXP_SUFFIX.test(property) && isNumber(value)) value = "".concat(value, "px");
				style[property] = value;
			});
		}
		/**
		* Check if the given element has a special class.
		* @param {Element} element - The element to check.
		* @param {string} value - The class to search.
		* @returns {boolean} Returns `true` if the special class was found.
		*/
		function hasClass(element, value) {
			return element.classList ? element.classList.contains(value) : element.className.indexOf(value) > -1;
		}
		/**
		* Add classes to the given element.
		* @param {Element} element - The target element.
		* @param {string} value - The classes to be added.
		*/
		function addClass(element, value) {
			if (!value) return;
			if (isNumber(element.length)) {
				forEach(element, function(elem) {
					addClass(elem, value);
				});
				return;
			}
			if (element.classList) {
				element.classList.add(value);
				return;
			}
			var className = element.className.trim();
			if (!className) element.className = value;
			else if (className.indexOf(value) < 0) element.className = "".concat(className, " ").concat(value);
		}
		/**
		* Remove classes from the given element.
		* @param {Element} element - The target element.
		* @param {string} value - The classes to be removed.
		*/
		function removeClass(element, value) {
			if (!value) return;
			if (isNumber(element.length)) {
				forEach(element, function(elem) {
					removeClass(elem, value);
				});
				return;
			}
			if (element.classList) {
				element.classList.remove(value);
				return;
			}
			if (element.className.indexOf(value) >= 0) element.className = element.className.replace(value, "");
		}
		/**
		* Add or remove classes from the given element.
		* @param {Element} element - The target element.
		* @param {string} value - The classes to be toggled.
		* @param {boolean} added - Add only.
		*/
		function toggleClass(element, value, added) {
			if (!value) return;
			if (isNumber(element.length)) {
				forEach(element, function(elem) {
					toggleClass(elem, value, added);
				});
				return;
			}
			if (added) addClass(element, value);
			else removeClass(element, value);
		}
		var REGEXP_CAMEL_CASE = /([a-z\d])([A-Z])/g;
		/**
		* Transform the given string from camelCase to kebab-case
		* @param {string} value - The value to transform.
		* @returns {string} The transformed value.
		*/
		function toParamCase(value) {
			return value.replace(REGEXP_CAMEL_CASE, "$1-$2").toLowerCase();
		}
		/**
		* Get data from the given element.
		* @param {Element} element - The target element.
		* @param {string} name - The data key to get.
		* @returns {string} The data value.
		*/
		function getData(element, name) {
			if (isObject(element[name])) return element[name];
			if (element.dataset) return element.dataset[name];
			return element.getAttribute("data-".concat(toParamCase(name)));
		}
		/**
		* Set data to the given element.
		* @param {Element} element - The target element.
		* @param {string} name - The data key to set.
		* @param {string} data - The data value.
		*/
		function setData(element, name, data) {
			if (isObject(data)) element[name] = data;
			else if (element.dataset) element.dataset[name] = data;
			else element.setAttribute("data-".concat(toParamCase(name)), data);
		}
		/**
		* Remove data from the given element.
		* @param {Element} element - The target element.
		* @param {string} name - The data key to remove.
		*/
		function removeData(element, name) {
			if (isObject(element[name])) try {
				delete element[name];
			} catch (error) {
				element[name] = void 0;
			}
			else if (element.dataset) try {
				delete element.dataset[name];
			} catch (error) {
				element.dataset[name] = void 0;
			}
			else element.removeAttribute("data-".concat(toParamCase(name)));
		}
		var REGEXP_SPACES = /\s\s*/;
		var onceSupported = function() {
			var supported = false;
			if (IS_BROWSER) {
				var once = false;
				var listener = function listener() {};
				var options = Object.defineProperty({}, "once", {
					get: function get() {
						supported = true;
						return once;
					},
					/**
					* This setter can fix a `TypeError` in strict mode
					* {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Errors/Getter_only}
					* @param {boolean} value - The value to set
					*/
					set: function set(value) {
						once = value;
					}
				});
				WINDOW.addEventListener("test", listener, options);
				WINDOW.removeEventListener("test", listener, options);
			}
			return supported;
		}();
		/**
		* Remove event listener from the target element.
		* @param {Element} element - The event target.
		* @param {string} type - The event type(s).
		* @param {Function} listener - The event listener.
		* @param {Object} options - The event options.
		*/
		function removeListener(element, type, listener) {
			var options = arguments.length > 3 && arguments[3] !== void 0 ? arguments[3] : {};
			var handler = listener;
			type.trim().split(REGEXP_SPACES).forEach(function(event) {
				if (!onceSupported) {
					var listeners = element.listeners;
					if (listeners && listeners[event] && listeners[event][listener]) {
						handler = listeners[event][listener];
						delete listeners[event][listener];
						if (Object.keys(listeners[event]).length === 0) delete listeners[event];
						if (Object.keys(listeners).length === 0) delete element.listeners;
					}
				}
				element.removeEventListener(event, handler, options);
			});
		}
		/**
		* Add event listener to the target element.
		* @param {Element} element - The event target.
		* @param {string} type - The event type(s).
		* @param {Function} listener - The event listener.
		* @param {Object} options - The event options.
		*/
		function addListener(element, type, listener) {
			var options = arguments.length > 3 && arguments[3] !== void 0 ? arguments[3] : {};
			var _handler = listener;
			type.trim().split(REGEXP_SPACES).forEach(function(event) {
				if (options.once && !onceSupported) {
					var _element$listeners = element.listeners, listeners = _element$listeners === void 0 ? {} : _element$listeners;
					_handler = function handler() {
						delete listeners[event][listener];
						element.removeEventListener(event, _handler, options);
						for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) args[_key2] = arguments[_key2];
						listener.apply(element, args);
					};
					if (!listeners[event]) listeners[event] = {};
					if (listeners[event][listener]) element.removeEventListener(event, listeners[event][listener], options);
					listeners[event][listener] = _handler;
					element.listeners = listeners;
				}
				element.addEventListener(event, _handler, options);
			});
		}
		/**
		* Dispatch event on the target element.
		* @param {Element} element - The event target.
		* @param {string} type - The event type(s).
		* @param {Object} data - The additional event data.
		* @returns {boolean} Indicate if the event is default prevented or not.
		*/
		function dispatchEvent(element, type, data) {
			var event;
			if (isFunction(Event) && isFunction(CustomEvent)) event = new CustomEvent(type, {
				detail: data,
				bubbles: true,
				cancelable: true
			});
			else {
				event = document.createEvent("CustomEvent");
				event.initCustomEvent(type, true, true, data);
			}
			return element.dispatchEvent(event);
		}
		/**
		* Get the offset base on the document.
		* @param {Element} element - The target element.
		* @returns {Object} The offset data.
		*/
		function getOffset(element) {
			var box = element.getBoundingClientRect();
			return {
				left: box.left + (window.pageXOffset - document.documentElement.clientLeft),
				top: box.top + (window.pageYOffset - document.documentElement.clientTop)
			};
		}
		var location = WINDOW.location;
		var REGEXP_ORIGINS = /^(\w+:)\/\/([^:/?#]*):?(\d*)/i;
		/**
		* Check if the given URL is a cross origin URL.
		* @param {string} url - The target URL.
		* @returns {boolean} Returns `true` if the given URL is a cross origin URL, else `false`.
		*/
		function isCrossOriginURL(url) {
			var parts = url.match(REGEXP_ORIGINS);
			return parts !== null && (parts[1] !== location.protocol || parts[2] !== location.hostname || parts[3] !== location.port);
		}
		/**
		* Add timestamp to the given URL.
		* @param {string} url - The target URL.
		* @returns {string} The result URL.
		*/
		function addTimestamp(url) {
			var timestamp = "timestamp=".concat((/* @__PURE__ */ new Date()).getTime());
			return url + (url.indexOf("?") === -1 ? "?" : "&") + timestamp;
		}
		/**
		* Get transforms base on the given object.
		* @param {Object} obj - The target object.
		* @returns {string} A string contains transform values.
		*/
		function getTransforms(_ref) {
			var rotate = _ref.rotate, scaleX = _ref.scaleX, scaleY = _ref.scaleY, translateX = _ref.translateX, translateY = _ref.translateY;
			var values = [];
			if (isNumber(translateX) && translateX !== 0) values.push("translateX(".concat(translateX, "px)"));
			if (isNumber(translateY) && translateY !== 0) values.push("translateY(".concat(translateY, "px)"));
			if (isNumber(rotate) && rotate !== 0) values.push("rotate(".concat(rotate, "deg)"));
			if (isNumber(scaleX) && scaleX !== 1) values.push("scaleX(".concat(scaleX, ")"));
			if (isNumber(scaleY) && scaleY !== 1) values.push("scaleY(".concat(scaleY, ")"));
			var transform = values.length ? values.join(" ") : "none";
			return {
				WebkitTransform: transform,
				msTransform: transform,
				transform
			};
		}
		/**
		* Get the max ratio of a group of pointers.
		* @param {string} pointers - The target pointers.
		* @returns {number} The result ratio.
		*/
		function getMaxZoomRatio(pointers) {
			var pointers2 = _objectSpread2({}, pointers);
			var maxRatio = 0;
			forEach(pointers, function(pointer, pointerId) {
				delete pointers2[pointerId];
				forEach(pointers2, function(pointer2) {
					var x1 = Math.abs(pointer.startX - pointer2.startX);
					var y1 = Math.abs(pointer.startY - pointer2.startY);
					var x2 = Math.abs(pointer.endX - pointer2.endX);
					var y2 = Math.abs(pointer.endY - pointer2.endY);
					var z1 = Math.sqrt(x1 * x1 + y1 * y1);
					var ratio = (Math.sqrt(x2 * x2 + y2 * y2) - z1) / z1;
					if (Math.abs(ratio) > Math.abs(maxRatio)) maxRatio = ratio;
				});
			});
			return maxRatio;
		}
		/**
		* Get a pointer from an event object.
		* @param {Object} event - The target event object.
		* @param {boolean} endOnly - Indicates if only returns the end point coordinate or not.
		* @returns {Object} The result pointer contains start and/or end point coordinates.
		*/
		function getPointer(_ref2, endOnly) {
			var pageX = _ref2.pageX, pageY = _ref2.pageY;
			var end = {
				endX: pageX,
				endY: pageY
			};
			return endOnly ? end : _objectSpread2({
				startX: pageX,
				startY: pageY
			}, end);
		}
		/**
		* Get the center point coordinate of a group of pointers.
		* @param {Object} pointers - The target pointers.
		* @returns {Object} The center point coordinate.
		*/
		function getPointersCenter(pointers) {
			var pageX = 0;
			var pageY = 0;
			var count = 0;
			forEach(pointers, function(_ref3) {
				var startX = _ref3.startX, startY = _ref3.startY;
				pageX += startX;
				pageY += startY;
				count += 1;
			});
			pageX /= count;
			pageY /= count;
			return {
				pageX,
				pageY
			};
		}
		/**
		* Get the max sizes in a rectangle under the given aspect ratio.
		* @param {Object} data - The original sizes.
		* @param {string} [type='contain'] - The adjust type.
		* @returns {Object} The result sizes.
		*/
		function getAdjustedSizes(_ref4) {
			var aspectRatio = _ref4.aspectRatio, height = _ref4.height, width = _ref4.width;
			var type = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : "contain";
			var isValidWidth = isPositiveNumber(width);
			var isValidHeight = isPositiveNumber(height);
			if (isValidWidth && isValidHeight) {
				var adjustedWidth = height * aspectRatio;
				if (type === "contain" && adjustedWidth > width || type === "cover" && adjustedWidth < width) height = width / aspectRatio;
				else width = height * aspectRatio;
			} else if (isValidWidth) height = width / aspectRatio;
			else if (isValidHeight) width = height * aspectRatio;
			return {
				width,
				height
			};
		}
		/**
		* Get the new sizes of a rectangle after rotated.
		* @param {Object} data - The original sizes.
		* @returns {Object} The result sizes.
		*/
		function getRotatedSizes(_ref5) {
			var width = _ref5.width, height = _ref5.height, degree = _ref5.degree;
			degree = Math.abs(degree) % 180;
			if (degree === 90) return {
				width: height,
				height: width
			};
			var arc = degree % 90 * Math.PI / 180;
			var sinArc = Math.sin(arc);
			var cosArc = Math.cos(arc);
			var newWidth = width * cosArc + height * sinArc;
			var newHeight = width * sinArc + height * cosArc;
			return degree > 90 ? {
				width: newHeight,
				height: newWidth
			} : {
				width: newWidth,
				height: newHeight
			};
		}
		/**
		* Get a canvas which drew the given image.
		* @param {HTMLImageElement} image - The image for drawing.
		* @param {Object} imageData - The image data.
		* @param {Object} canvasData - The canvas data.
		* @param {Object} options - The options.
		* @returns {HTMLCanvasElement} The result canvas.
		*/
		function getSourceCanvas(image, _ref6, _ref7, _ref8) {
			var imageAspectRatio = _ref6.aspectRatio, imageNaturalWidth = _ref6.naturalWidth, imageNaturalHeight = _ref6.naturalHeight, _ref6$rotate = _ref6.rotate, rotate = _ref6$rotate === void 0 ? 0 : _ref6$rotate, _ref6$scaleX = _ref6.scaleX, scaleX = _ref6$scaleX === void 0 ? 1 : _ref6$scaleX, _ref6$scaleY = _ref6.scaleY, scaleY = _ref6$scaleY === void 0 ? 1 : _ref6$scaleY;
			var aspectRatio = _ref7.aspectRatio, naturalWidth = _ref7.naturalWidth, naturalHeight = _ref7.naturalHeight;
			var _ref8$fillColor = _ref8.fillColor, fillColor = _ref8$fillColor === void 0 ? "transparent" : _ref8$fillColor, _ref8$imageSmoothingE = _ref8.imageSmoothingEnabled, imageSmoothingEnabled = _ref8$imageSmoothingE === void 0 ? true : _ref8$imageSmoothingE, _ref8$imageSmoothingQ = _ref8.imageSmoothingQuality, imageSmoothingQuality = _ref8$imageSmoothingQ === void 0 ? "low" : _ref8$imageSmoothingQ, _ref8$maxWidth = _ref8.maxWidth, maxWidth = _ref8$maxWidth === void 0 ? Infinity : _ref8$maxWidth, _ref8$maxHeight = _ref8.maxHeight, maxHeight = _ref8$maxHeight === void 0 ? Infinity : _ref8$maxHeight, _ref8$minWidth = _ref8.minWidth, minWidth = _ref8$minWidth === void 0 ? 0 : _ref8$minWidth, _ref8$minHeight = _ref8.minHeight, minHeight = _ref8$minHeight === void 0 ? 0 : _ref8$minHeight;
			var canvas = document.createElement("canvas");
			var context = canvas.getContext("2d");
			var maxSizes = getAdjustedSizes({
				aspectRatio,
				width: maxWidth,
				height: maxHeight
			});
			var minSizes = getAdjustedSizes({
				aspectRatio,
				width: minWidth,
				height: minHeight
			}, "cover");
			var width = Math.min(maxSizes.width, Math.max(minSizes.width, naturalWidth));
			var height = Math.min(maxSizes.height, Math.max(minSizes.height, naturalHeight));
			var destMaxSizes = getAdjustedSizes({
				aspectRatio: imageAspectRatio,
				width: maxWidth,
				height: maxHeight
			});
			var destMinSizes = getAdjustedSizes({
				aspectRatio: imageAspectRatio,
				width: minWidth,
				height: minHeight
			}, "cover");
			var destWidth = Math.min(destMaxSizes.width, Math.max(destMinSizes.width, imageNaturalWidth));
			var destHeight = Math.min(destMaxSizes.height, Math.max(destMinSizes.height, imageNaturalHeight));
			var params = [
				-destWidth / 2,
				-destHeight / 2,
				destWidth,
				destHeight
			];
			canvas.width = normalizeDecimalNumber(width);
			canvas.height = normalizeDecimalNumber(height);
			context.fillStyle = fillColor;
			context.fillRect(0, 0, width, height);
			context.save();
			context.translate(width / 2, height / 2);
			context.rotate(rotate * Math.PI / 180);
			context.scale(scaleX, scaleY);
			context.imageSmoothingEnabled = imageSmoothingEnabled;
			context.imageSmoothingQuality = imageSmoothingQuality;
			context.drawImage.apply(context, [image].concat(_toConsumableArray(params.map(function(param) {
				return Math.floor(normalizeDecimalNumber(param));
			}))));
			context.restore();
			return canvas;
		}
		var fromCharCode = String.fromCharCode;
		/**
		* Get string from char code in data view.
		* @param {DataView} dataView - The data view for read.
		* @param {number} start - The start index.
		* @param {number} length - The read length.
		* @returns {string} The read result.
		*/
		function getStringFromCharCode(dataView, start, length) {
			var str = "";
			length += start;
			for (var i = start; i < length; i += 1) str += fromCharCode(dataView.getUint8(i));
			return str;
		}
		var REGEXP_DATA_URL_HEAD = /^data:.*,/;
		/**
		* Transform Data URL to array buffer.
		* @param {string} dataURL - The Data URL to transform.
		* @returns {ArrayBuffer} The result array buffer.
		*/
		function dataURLToArrayBuffer(dataURL) {
			var base64 = dataURL.replace(REGEXP_DATA_URL_HEAD, "");
			var binary = atob(base64);
			var arrayBuffer = new ArrayBuffer(binary.length);
			var uint8 = new Uint8Array(arrayBuffer);
			forEach(uint8, function(value, i) {
				uint8[i] = binary.charCodeAt(i);
			});
			return arrayBuffer;
		}
		/**
		* Transform array buffer to Data URL.
		* @param {ArrayBuffer} arrayBuffer - The array buffer to transform.
		* @param {string} mimeType - The mime type of the Data URL.
		* @returns {string} The result Data URL.
		*/
		function arrayBufferToDataURL(arrayBuffer, mimeType) {
			var chunks = [];
			var chunkSize = 8192;
			var uint8 = new Uint8Array(arrayBuffer);
			while (uint8.length > 0) {
				chunks.push(fromCharCode.apply(null, toArray(uint8.subarray(0, chunkSize))));
				uint8 = uint8.subarray(chunkSize);
			}
			return "data:".concat(mimeType, ";base64,").concat(btoa(chunks.join("")));
		}
		/**
		* Get orientation value from given array buffer.
		* @param {ArrayBuffer} arrayBuffer - The array buffer to read.
		* @returns {number} The read orientation value.
		*/
		function resetAndGetOrientation(arrayBuffer) {
			var dataView = new DataView(arrayBuffer);
			var orientation;
			try {
				var littleEndian;
				var app1Start;
				var ifdStart;
				if (dataView.getUint8(0) === 255 && dataView.getUint8(1) === 216) {
					var length = dataView.byteLength;
					var offset = 2;
					while (offset + 1 < length) {
						if (dataView.getUint8(offset) === 255 && dataView.getUint8(offset + 1) === 225) {
							app1Start = offset;
							break;
						}
						offset += 1;
					}
				}
				if (app1Start) {
					var exifIDCode = app1Start + 4;
					var tiffOffset = app1Start + 10;
					if (getStringFromCharCode(dataView, exifIDCode, 4) === "Exif") {
						var endianness = dataView.getUint16(tiffOffset);
						littleEndian = endianness === 18761;
						if (littleEndian || endianness === 19789) {
							if (dataView.getUint16(tiffOffset + 2, littleEndian) === 42) {
								var firstIFDOffset = dataView.getUint32(tiffOffset + 4, littleEndian);
								if (firstIFDOffset >= 8) ifdStart = tiffOffset + firstIFDOffset;
							}
						}
					}
				}
				if (ifdStart) {
					var _length = dataView.getUint16(ifdStart, littleEndian);
					var _offset;
					var i = 0;
					for (; i < _length; i += 1) {
						_offset = ifdStart + i * 12 + 2;
						if (dataView.getUint16(_offset, littleEndian) === 274) {
							_offset += 8;
							orientation = dataView.getUint16(_offset, littleEndian);
							dataView.setUint16(_offset, 1, littleEndian);
							break;
						}
					}
				}
			} catch (error) {
				orientation = 1;
			}
			return orientation;
		}
		/**
		* Parse Exif Orientation value.
		* @param {number} orientation - The orientation to parse.
		* @returns {Object} The parsed result.
		*/
		function parseOrientation(orientation) {
			var rotate = 0;
			var scaleX = 1;
			var scaleY = 1;
			switch (orientation) {
				case 2:
					scaleX = -1;
					break;
				case 3:
					rotate = -180;
					break;
				case 4:
					scaleY = -1;
					break;
				case 5:
					rotate = 90;
					scaleY = -1;
					break;
				case 6:
					rotate = 90;
					break;
				case 7:
					rotate = 90;
					scaleX = -1;
					break;
				case 8: rotate = -90;
			}
			return {
				rotate,
				scaleX,
				scaleY
			};
		}
		var render = {
			render: function render() {
				this.initContainer();
				this.initCanvas();
				this.initCropBox();
				this.renderCanvas();
				if (this.cropped) this.renderCropBox();
			},
			initContainer: function initContainer() {
				var element = this.element, options = this.options, container = this.container, cropper = this.cropper;
				var minWidth = Number(options.minContainerWidth);
				var minHeight = Number(options.minContainerHeight);
				addClass(cropper, CLASS_HIDDEN);
				removeClass(element, CLASS_HIDDEN);
				var containerData = {
					width: Math.max(container.offsetWidth, minWidth >= 0 ? minWidth : MIN_CONTAINER_WIDTH),
					height: Math.max(container.offsetHeight, minHeight >= 0 ? minHeight : MIN_CONTAINER_HEIGHT)
				};
				this.containerData = containerData;
				setStyle(cropper, {
					width: containerData.width,
					height: containerData.height
				});
				addClass(element, CLASS_HIDDEN);
				removeClass(cropper, CLASS_HIDDEN);
			},
			initCanvas: function initCanvas() {
				var containerData = this.containerData, imageData = this.imageData;
				var viewMode = this.options.viewMode;
				var rotated = Math.abs(imageData.rotate) % 180 === 90;
				var naturalWidth = rotated ? imageData.naturalHeight : imageData.naturalWidth;
				var naturalHeight = rotated ? imageData.naturalWidth : imageData.naturalHeight;
				var aspectRatio = naturalWidth / naturalHeight;
				var canvasWidth = containerData.width;
				var canvasHeight = containerData.height;
				if (containerData.height * aspectRatio > containerData.width) {
					if (viewMode === 3) canvasWidth = containerData.height * aspectRatio;
					else canvasHeight = containerData.width / aspectRatio;
				} else if (viewMode === 3) canvasHeight = containerData.width / aspectRatio;
				else canvasWidth = containerData.height * aspectRatio;
				var canvasData = {
					aspectRatio,
					naturalWidth,
					naturalHeight,
					width: canvasWidth,
					height: canvasHeight
				};
				this.canvasData = canvasData;
				this.limited = viewMode === 1 || viewMode === 2;
				this.limitCanvas(true, true);
				canvasData.width = Math.min(Math.max(canvasData.width, canvasData.minWidth), canvasData.maxWidth);
				canvasData.height = Math.min(Math.max(canvasData.height, canvasData.minHeight), canvasData.maxHeight);
				canvasData.left = (containerData.width - canvasData.width) / 2;
				canvasData.top = (containerData.height - canvasData.height) / 2;
				canvasData.oldLeft = canvasData.left;
				canvasData.oldTop = canvasData.top;
				this.initialCanvasData = assign({}, canvasData);
			},
			limitCanvas: function limitCanvas(sizeLimited, positionLimited) {
				var options = this.options, containerData = this.containerData, canvasData = this.canvasData, cropBoxData = this.cropBoxData;
				var viewMode = options.viewMode;
				var aspectRatio = canvasData.aspectRatio;
				var cropped = this.cropped && cropBoxData;
				if (sizeLimited) {
					var minCanvasWidth = Number(options.minCanvasWidth) || 0;
					var minCanvasHeight = Number(options.minCanvasHeight) || 0;
					if (viewMode > 1) {
						minCanvasWidth = Math.max(minCanvasWidth, containerData.width);
						minCanvasHeight = Math.max(minCanvasHeight, containerData.height);
						if (viewMode === 3) {
							if (minCanvasHeight * aspectRatio > minCanvasWidth) minCanvasWidth = minCanvasHeight * aspectRatio;
							else minCanvasHeight = minCanvasWidth / aspectRatio;
						}
					} else if (viewMode > 0) {
						if (minCanvasWidth) minCanvasWidth = Math.max(minCanvasWidth, cropped ? cropBoxData.width : 0);
						else if (minCanvasHeight) minCanvasHeight = Math.max(minCanvasHeight, cropped ? cropBoxData.height : 0);
						else if (cropped) {
							minCanvasWidth = cropBoxData.width;
							minCanvasHeight = cropBoxData.height;
							if (minCanvasHeight * aspectRatio > minCanvasWidth) minCanvasWidth = minCanvasHeight * aspectRatio;
							else minCanvasHeight = minCanvasWidth / aspectRatio;
						}
					}
					var _getAdjustedSizes = getAdjustedSizes({
						aspectRatio,
						width: minCanvasWidth,
						height: minCanvasHeight
					});
					minCanvasWidth = _getAdjustedSizes.width;
					minCanvasHeight = _getAdjustedSizes.height;
					canvasData.minWidth = minCanvasWidth;
					canvasData.minHeight = minCanvasHeight;
					canvasData.maxWidth = Infinity;
					canvasData.maxHeight = Infinity;
				}
				if (positionLimited) {
					if (viewMode > (cropped ? 0 : 1)) {
						var newCanvasLeft = containerData.width - canvasData.width;
						var newCanvasTop = containerData.height - canvasData.height;
						canvasData.minLeft = Math.min(0, newCanvasLeft);
						canvasData.minTop = Math.min(0, newCanvasTop);
						canvasData.maxLeft = Math.max(0, newCanvasLeft);
						canvasData.maxTop = Math.max(0, newCanvasTop);
						if (cropped && this.limited) {
							canvasData.minLeft = Math.min(cropBoxData.left, cropBoxData.left + (cropBoxData.width - canvasData.width));
							canvasData.minTop = Math.min(cropBoxData.top, cropBoxData.top + (cropBoxData.height - canvasData.height));
							canvasData.maxLeft = cropBoxData.left;
							canvasData.maxTop = cropBoxData.top;
							if (viewMode === 2) {
								if (canvasData.width >= containerData.width) {
									canvasData.minLeft = Math.min(0, newCanvasLeft);
									canvasData.maxLeft = Math.max(0, newCanvasLeft);
								}
								if (canvasData.height >= containerData.height) {
									canvasData.minTop = Math.min(0, newCanvasTop);
									canvasData.maxTop = Math.max(0, newCanvasTop);
								}
							}
						}
					} else {
						canvasData.minLeft = -canvasData.width;
						canvasData.minTop = -canvasData.height;
						canvasData.maxLeft = containerData.width;
						canvasData.maxTop = containerData.height;
					}
				}
			},
			renderCanvas: function renderCanvas(changed, transformed) {
				var canvasData = this.canvasData, imageData = this.imageData;
				if (transformed) {
					var _getRotatedSizes = getRotatedSizes({
						width: imageData.naturalWidth * Math.abs(imageData.scaleX || 1),
						height: imageData.naturalHeight * Math.abs(imageData.scaleY || 1),
						degree: imageData.rotate || 0
					}), naturalWidth = _getRotatedSizes.width, naturalHeight = _getRotatedSizes.height;
					var width = canvasData.width * (naturalWidth / canvasData.naturalWidth);
					var height = canvasData.height * (naturalHeight / canvasData.naturalHeight);
					canvasData.left -= (width - canvasData.width) / 2;
					canvasData.top -= (height - canvasData.height) / 2;
					canvasData.width = width;
					canvasData.height = height;
					canvasData.aspectRatio = naturalWidth / naturalHeight;
					canvasData.naturalWidth = naturalWidth;
					canvasData.naturalHeight = naturalHeight;
					this.limitCanvas(true, false);
				}
				if (canvasData.width > canvasData.maxWidth || canvasData.width < canvasData.minWidth) canvasData.left = canvasData.oldLeft;
				if (canvasData.height > canvasData.maxHeight || canvasData.height < canvasData.minHeight) canvasData.top = canvasData.oldTop;
				canvasData.width = Math.min(Math.max(canvasData.width, canvasData.minWidth), canvasData.maxWidth);
				canvasData.height = Math.min(Math.max(canvasData.height, canvasData.minHeight), canvasData.maxHeight);
				this.limitCanvas(false, true);
				canvasData.left = Math.min(Math.max(canvasData.left, canvasData.minLeft), canvasData.maxLeft);
				canvasData.top = Math.min(Math.max(canvasData.top, canvasData.minTop), canvasData.maxTop);
				canvasData.oldLeft = canvasData.left;
				canvasData.oldTop = canvasData.top;
				setStyle(this.canvas, assign({
					width: canvasData.width,
					height: canvasData.height
				}, getTransforms({
					translateX: canvasData.left,
					translateY: canvasData.top
				})));
				this.renderImage(changed);
				if (this.cropped && this.limited) this.limitCropBox(true, true);
			},
			renderImage: function renderImage(changed) {
				var canvasData = this.canvasData, imageData = this.imageData;
				var width = imageData.naturalWidth * (canvasData.width / canvasData.naturalWidth);
				var height = imageData.naturalHeight * (canvasData.height / canvasData.naturalHeight);
				assign(imageData, {
					width,
					height,
					left: (canvasData.width - width) / 2,
					top: (canvasData.height - height) / 2
				});
				setStyle(this.image, assign({
					width: imageData.width,
					height: imageData.height
				}, getTransforms(assign({
					translateX: imageData.left,
					translateY: imageData.top
				}, imageData))));
				if (changed) this.output();
			},
			initCropBox: function initCropBox() {
				var options = this.options, canvasData = this.canvasData;
				var aspectRatio = options.aspectRatio || options.initialAspectRatio;
				var autoCropArea = Number(options.autoCropArea) || .8;
				var cropBoxData = {
					width: canvasData.width,
					height: canvasData.height
				};
				if (aspectRatio) {
					if (canvasData.height * aspectRatio > canvasData.width) cropBoxData.height = cropBoxData.width / aspectRatio;
					else cropBoxData.width = cropBoxData.height * aspectRatio;
				}
				this.cropBoxData = cropBoxData;
				this.limitCropBox(true, true);
				cropBoxData.width = Math.min(Math.max(cropBoxData.width, cropBoxData.minWidth), cropBoxData.maxWidth);
				cropBoxData.height = Math.min(Math.max(cropBoxData.height, cropBoxData.minHeight), cropBoxData.maxHeight);
				cropBoxData.width = Math.max(cropBoxData.minWidth, cropBoxData.width * autoCropArea);
				cropBoxData.height = Math.max(cropBoxData.minHeight, cropBoxData.height * autoCropArea);
				cropBoxData.left = canvasData.left + (canvasData.width - cropBoxData.width) / 2;
				cropBoxData.top = canvasData.top + (canvasData.height - cropBoxData.height) / 2;
				cropBoxData.oldLeft = cropBoxData.left;
				cropBoxData.oldTop = cropBoxData.top;
				this.initialCropBoxData = assign({}, cropBoxData);
			},
			limitCropBox: function limitCropBox(sizeLimited, positionLimited) {
				var options = this.options, containerData = this.containerData, canvasData = this.canvasData, cropBoxData = this.cropBoxData, limited = this.limited;
				var aspectRatio = options.aspectRatio;
				if (sizeLimited) {
					var minCropBoxWidth = Number(options.minCropBoxWidth) || 0;
					var minCropBoxHeight = Number(options.minCropBoxHeight) || 0;
					var maxCropBoxWidth = limited ? Math.min(containerData.width, canvasData.width, canvasData.width + canvasData.left, containerData.width - canvasData.left) : containerData.width;
					var maxCropBoxHeight = limited ? Math.min(containerData.height, canvasData.height, canvasData.height + canvasData.top, containerData.height - canvasData.top) : containerData.height;
					minCropBoxWidth = Math.min(minCropBoxWidth, containerData.width);
					minCropBoxHeight = Math.min(minCropBoxHeight, containerData.height);
					if (aspectRatio) {
						if (minCropBoxWidth && minCropBoxHeight) {
							if (minCropBoxHeight * aspectRatio > minCropBoxWidth) minCropBoxHeight = minCropBoxWidth / aspectRatio;
							else minCropBoxWidth = minCropBoxHeight * aspectRatio;
						} else if (minCropBoxWidth) minCropBoxHeight = minCropBoxWidth / aspectRatio;
						else if (minCropBoxHeight) minCropBoxWidth = minCropBoxHeight * aspectRatio;
						if (maxCropBoxHeight * aspectRatio > maxCropBoxWidth) maxCropBoxHeight = maxCropBoxWidth / aspectRatio;
						else maxCropBoxWidth = maxCropBoxHeight * aspectRatio;
					}
					cropBoxData.minWidth = Math.min(minCropBoxWidth, maxCropBoxWidth);
					cropBoxData.minHeight = Math.min(minCropBoxHeight, maxCropBoxHeight);
					cropBoxData.maxWidth = maxCropBoxWidth;
					cropBoxData.maxHeight = maxCropBoxHeight;
				}
				if (positionLimited) {
					if (limited) {
						cropBoxData.minLeft = Math.max(0, canvasData.left);
						cropBoxData.minTop = Math.max(0, canvasData.top);
						cropBoxData.maxLeft = Math.min(containerData.width, canvasData.left + canvasData.width) - cropBoxData.width;
						cropBoxData.maxTop = Math.min(containerData.height, canvasData.top + canvasData.height) - cropBoxData.height;
					} else {
						cropBoxData.minLeft = 0;
						cropBoxData.minTop = 0;
						cropBoxData.maxLeft = containerData.width - cropBoxData.width;
						cropBoxData.maxTop = containerData.height - cropBoxData.height;
					}
				}
			},
			renderCropBox: function renderCropBox() {
				var options = this.options, containerData = this.containerData, cropBoxData = this.cropBoxData;
				if (cropBoxData.width > cropBoxData.maxWidth || cropBoxData.width < cropBoxData.minWidth) cropBoxData.left = cropBoxData.oldLeft;
				if (cropBoxData.height > cropBoxData.maxHeight || cropBoxData.height < cropBoxData.minHeight) cropBoxData.top = cropBoxData.oldTop;
				cropBoxData.width = Math.min(Math.max(cropBoxData.width, cropBoxData.minWidth), cropBoxData.maxWidth);
				cropBoxData.height = Math.min(Math.max(cropBoxData.height, cropBoxData.minHeight), cropBoxData.maxHeight);
				this.limitCropBox(false, true);
				cropBoxData.left = Math.min(Math.max(cropBoxData.left, cropBoxData.minLeft), cropBoxData.maxLeft);
				cropBoxData.top = Math.min(Math.max(cropBoxData.top, cropBoxData.minTop), cropBoxData.maxTop);
				cropBoxData.oldLeft = cropBoxData.left;
				cropBoxData.oldTop = cropBoxData.top;
				if (options.movable && options.cropBoxMovable) setData(this.face, DATA_ACTION, cropBoxData.width >= containerData.width && cropBoxData.height >= containerData.height ? ACTION_MOVE : ACTION_ALL);
				setStyle(this.cropBox, assign({
					width: cropBoxData.width,
					height: cropBoxData.height
				}, getTransforms({
					translateX: cropBoxData.left,
					translateY: cropBoxData.top
				})));
				if (this.cropped && this.limited) this.limitCanvas(true, true);
				if (!this.disabled) this.output();
			},
			output: function output() {
				this.preview();
				dispatchEvent(this.element, EVENT_CROP, this.getData());
			}
		};
		var preview = {
			initPreview: function initPreview() {
				var element = this.element, crossOrigin = this.crossOrigin;
				var preview = this.options.preview;
				var url = crossOrigin ? this.crossOriginUrl : this.url;
				var alt = element.alt || "The image to preview";
				var image = document.createElement("img");
				if (crossOrigin) image.crossOrigin = crossOrigin;
				image.src = url;
				image.alt = alt;
				this.viewBox.appendChild(image);
				this.viewBoxImage = image;
				if (!preview) return;
				var previews = preview;
				if (typeof preview === "string") previews = element.ownerDocument.querySelectorAll(preview);
				else if (preview.querySelector) previews = [preview];
				this.previews = previews;
				forEach(previews, function(el) {
					var img = document.createElement("img");
					setData(el, DATA_PREVIEW, {
						width: el.offsetWidth,
						height: el.offsetHeight,
						html: el.innerHTML
					});
					if (crossOrigin) img.crossOrigin = crossOrigin;
					img.src = url;
					img.alt = alt;
					/**
					* Override img element styles
					* Add `display:block` to avoid margin top issue
					* Add `height:auto` to override `height` attribute on IE8
					* (Occur only when margin-top <= -height)
					*/
					img.style.cssText = "display:block;width:100%;height:auto;min-width:0!important;min-height:0!important;max-width:none!important;max-height:none!important;image-orientation:0deg!important;\"";
					el.innerHTML = "";
					el.appendChild(img);
				});
			},
			resetPreview: function resetPreview() {
				forEach(this.previews, function(element) {
					var data = getData(element, DATA_PREVIEW);
					setStyle(element, {
						width: data.width,
						height: data.height
					});
					element.innerHTML = data.html;
					removeData(element, DATA_PREVIEW);
				});
			},
			preview: function preview() {
				var imageData = this.imageData, canvasData = this.canvasData, cropBoxData = this.cropBoxData;
				var cropBoxWidth = cropBoxData.width, cropBoxHeight = cropBoxData.height;
				var width = imageData.width, height = imageData.height;
				var left = cropBoxData.left - canvasData.left - imageData.left;
				var top = cropBoxData.top - canvasData.top - imageData.top;
				if (!this.cropped || this.disabled) return;
				setStyle(this.viewBoxImage, assign({
					width,
					height
				}, getTransforms(assign({
					translateX: -left,
					translateY: -top
				}, imageData))));
				forEach(this.previews, function(element) {
					var data = getData(element, DATA_PREVIEW);
					var originalWidth = data.width;
					var originalHeight = data.height;
					var newWidth = originalWidth;
					var newHeight = originalHeight;
					var ratio = 1;
					if (cropBoxWidth) {
						ratio = originalWidth / cropBoxWidth;
						newHeight = cropBoxHeight * ratio;
					}
					if (cropBoxHeight && newHeight > originalHeight) {
						ratio = originalHeight / cropBoxHeight;
						newWidth = cropBoxWidth * ratio;
						newHeight = originalHeight;
					}
					setStyle(element, {
						width: newWidth,
						height: newHeight
					});
					setStyle(element.getElementsByTagName("img")[0], assign({
						width: width * ratio,
						height: height * ratio
					}, getTransforms(assign({
						translateX: -left * ratio,
						translateY: -top * ratio
					}, imageData))));
				});
			}
		};
		var events = {
			bind: function bind() {
				var element = this.element, options = this.options, cropper = this.cropper;
				if (isFunction(options.cropstart)) addListener(element, EVENT_CROP_START, options.cropstart);
				if (isFunction(options.cropmove)) addListener(element, EVENT_CROP_MOVE, options.cropmove);
				if (isFunction(options.cropend)) addListener(element, EVENT_CROP_END, options.cropend);
				if (isFunction(options.crop)) addListener(element, EVENT_CROP, options.crop);
				if (isFunction(options.zoom)) addListener(element, EVENT_ZOOM, options.zoom);
				addListener(cropper, EVENT_POINTER_DOWN, this.onCropStart = this.cropStart.bind(this));
				if (options.zoomable && options.zoomOnWheel) addListener(cropper, EVENT_WHEEL, this.onWheel = this.wheel.bind(this), {
					passive: false,
					capture: true
				});
				if (options.toggleDragModeOnDblclick) addListener(cropper, EVENT_DBLCLICK, this.onDblclick = this.dblclick.bind(this));
				addListener(element.ownerDocument, EVENT_POINTER_MOVE, this.onCropMove = this.cropMove.bind(this));
				addListener(element.ownerDocument, EVENT_POINTER_UP, this.onCropEnd = this.cropEnd.bind(this));
				if (options.responsive) addListener(window, EVENT_RESIZE, this.onResize = this.resize.bind(this));
			},
			unbind: function unbind() {
				var element = this.element, options = this.options, cropper = this.cropper;
				if (isFunction(options.cropstart)) removeListener(element, EVENT_CROP_START, options.cropstart);
				if (isFunction(options.cropmove)) removeListener(element, EVENT_CROP_MOVE, options.cropmove);
				if (isFunction(options.cropend)) removeListener(element, EVENT_CROP_END, options.cropend);
				if (isFunction(options.crop)) removeListener(element, EVENT_CROP, options.crop);
				if (isFunction(options.zoom)) removeListener(element, EVENT_ZOOM, options.zoom);
				removeListener(cropper, EVENT_POINTER_DOWN, this.onCropStart);
				if (options.zoomable && options.zoomOnWheel) removeListener(cropper, EVENT_WHEEL, this.onWheel, {
					passive: false,
					capture: true
				});
				if (options.toggleDragModeOnDblclick) removeListener(cropper, EVENT_DBLCLICK, this.onDblclick);
				removeListener(element.ownerDocument, EVENT_POINTER_MOVE, this.onCropMove);
				removeListener(element.ownerDocument, EVENT_POINTER_UP, this.onCropEnd);
				if (options.responsive) removeListener(window, EVENT_RESIZE, this.onResize);
			}
		};
		var handlers = {
			resize: function resize() {
				if (this.disabled) return;
				var options = this.options, container = this.container, containerData = this.containerData;
				var ratioX = container.offsetWidth / containerData.width;
				var ratioY = container.offsetHeight / containerData.height;
				var ratio = Math.abs(ratioX - 1) > Math.abs(ratioY - 1) ? ratioX : ratioY;
				if (ratio !== 1) {
					var canvasData;
					var cropBoxData;
					if (options.restore) {
						canvasData = this.getCanvasData();
						cropBoxData = this.getCropBoxData();
					}
					this.render();
					if (options.restore) {
						this.setCanvasData(forEach(canvasData, function(n, i) {
							canvasData[i] = n * ratio;
						}));
						this.setCropBoxData(forEach(cropBoxData, function(n, i) {
							cropBoxData[i] = n * ratio;
						}));
					}
				}
			},
			dblclick: function dblclick() {
				if (this.disabled || this.options.dragMode === DRAG_MODE_NONE) return;
				this.setDragMode(hasClass(this.dragBox, CLASS_CROP) ? DRAG_MODE_MOVE : DRAG_MODE_CROP);
			},
			wheel: function wheel(event) {
				var _this = this;
				var ratio = Number(this.options.wheelZoomRatio) || .1;
				var delta = 1;
				if (this.disabled) return;
				event.preventDefault();
				if (this.wheeling) return;
				this.wheeling = true;
				setTimeout(function() {
					_this.wheeling = false;
				}, 50);
				if (event.deltaY) delta = event.deltaY > 0 ? 1 : -1;
				else if (event.wheelDelta) delta = -event.wheelDelta / 120;
				else if (event.detail) delta = event.detail > 0 ? 1 : -1;
				this.zoom(-delta * ratio, event);
			},
			cropStart: function cropStart(event) {
				var buttons = event.buttons, button = event.button;
				if (this.disabled || (event.type === "mousedown" || event.type === "pointerdown" && event.pointerType === "mouse") && (isNumber(buttons) && buttons !== 1 || isNumber(button) && button !== 0 || event.ctrlKey)) return;
				var options = this.options, pointers = this.pointers;
				var action;
				if (event.changedTouches) forEach(event.changedTouches, function(touch) {
					pointers[touch.identifier] = getPointer(touch);
				});
				else pointers[event.pointerId || 0] = getPointer(event);
				if (Object.keys(pointers).length > 1 && options.zoomable && options.zoomOnTouch) action = ACTION_ZOOM;
				else action = getData(event.target, DATA_ACTION);
				if (!REGEXP_ACTIONS.test(action)) return;
				if (dispatchEvent(this.element, EVENT_CROP_START, {
					originalEvent: event,
					action
				}) === false) return;
				event.preventDefault();
				this.action = action;
				this.cropping = false;
				if (action === ACTION_CROP) {
					this.cropping = true;
					addClass(this.dragBox, CLASS_MODAL);
				}
			},
			cropMove: function cropMove(event) {
				var action = this.action;
				if (this.disabled || !action) return;
				var pointers = this.pointers;
				event.preventDefault();
				if (dispatchEvent(this.element, EVENT_CROP_MOVE, {
					originalEvent: event,
					action
				}) === false) return;
				if (event.changedTouches) forEach(event.changedTouches, function(touch) {
					assign(pointers[touch.identifier] || {}, getPointer(touch, true));
				});
				else assign(pointers[event.pointerId || 0] || {}, getPointer(event, true));
				this.change(event);
			},
			cropEnd: function cropEnd(event) {
				if (this.disabled) return;
				var action = this.action, pointers = this.pointers;
				if (event.changedTouches) forEach(event.changedTouches, function(touch) {
					delete pointers[touch.identifier];
				});
				else delete pointers[event.pointerId || 0];
				if (!action) return;
				event.preventDefault();
				if (!Object.keys(pointers).length) this.action = "";
				if (this.cropping) {
					this.cropping = false;
					toggleClass(this.dragBox, CLASS_MODAL, this.cropped && this.options.modal);
				}
				dispatchEvent(this.element, EVENT_CROP_END, {
					originalEvent: event,
					action
				});
			}
		};
		var change = { change: function change(event) {
			var options = this.options, canvasData = this.canvasData, containerData = this.containerData, cropBoxData = this.cropBoxData, pointers = this.pointers;
			var action = this.action;
			var aspectRatio = options.aspectRatio;
			var left = cropBoxData.left, top = cropBoxData.top, width = cropBoxData.width, height = cropBoxData.height;
			var right = left + width;
			var bottom = top + height;
			var minLeft = 0;
			var minTop = 0;
			var maxWidth = containerData.width;
			var maxHeight = containerData.height;
			var renderable = true;
			var offset;
			if (!aspectRatio && event.shiftKey) aspectRatio = width && height ? width / height : 1;
			if (this.limited) {
				minLeft = cropBoxData.minLeft;
				minTop = cropBoxData.minTop;
				maxWidth = minLeft + Math.min(containerData.width, canvasData.width, canvasData.left + canvasData.width);
				maxHeight = minTop + Math.min(containerData.height, canvasData.height, canvasData.top + canvasData.height);
			}
			var pointer = pointers[Object.keys(pointers)[0]];
			var range = {
				x: pointer.endX - pointer.startX,
				y: pointer.endY - pointer.startY
			};
			var check = function check(side) {
				switch (side) {
					case ACTION_EAST:
						if (right + range.x > maxWidth) range.x = maxWidth - right;
						break;
					case ACTION_WEST:
						if (left + range.x < minLeft) range.x = minLeft - left;
						break;
					case ACTION_NORTH:
						if (top + range.y < minTop) range.y = minTop - top;
						break;
					case ACTION_SOUTH: if (bottom + range.y > maxHeight) range.y = maxHeight - bottom;
				}
			};
			switch (action) {
				case ACTION_ALL:
					left += range.x;
					top += range.y;
					break;
				case ACTION_EAST:
					if (range.x >= 0 && (right >= maxWidth || aspectRatio && (top <= minTop || bottom >= maxHeight))) {
						renderable = false;
						break;
					}
					check(ACTION_EAST);
					width += range.x;
					if (width < 0) {
						action = ACTION_WEST;
						width = -width;
						left -= width;
					}
					if (aspectRatio) {
						height = width / aspectRatio;
						top += (cropBoxData.height - height) / 2;
					}
					break;
				case ACTION_NORTH:
					if (range.y <= 0 && (top <= minTop || aspectRatio && (left <= minLeft || right >= maxWidth))) {
						renderable = false;
						break;
					}
					check(ACTION_NORTH);
					height -= range.y;
					top += range.y;
					if (height < 0) {
						action = ACTION_SOUTH;
						height = -height;
						top -= height;
					}
					if (aspectRatio) {
						width = height * aspectRatio;
						left += (cropBoxData.width - width) / 2;
					}
					break;
				case ACTION_WEST:
					if (range.x <= 0 && (left <= minLeft || aspectRatio && (top <= minTop || bottom >= maxHeight))) {
						renderable = false;
						break;
					}
					check(ACTION_WEST);
					width -= range.x;
					left += range.x;
					if (width < 0) {
						action = ACTION_EAST;
						width = -width;
						left -= width;
					}
					if (aspectRatio) {
						height = width / aspectRatio;
						top += (cropBoxData.height - height) / 2;
					}
					break;
				case ACTION_SOUTH:
					if (range.y >= 0 && (bottom >= maxHeight || aspectRatio && (left <= minLeft || right >= maxWidth))) {
						renderable = false;
						break;
					}
					check(ACTION_SOUTH);
					height += range.y;
					if (height < 0) {
						action = ACTION_NORTH;
						height = -height;
						top -= height;
					}
					if (aspectRatio) {
						width = height * aspectRatio;
						left += (cropBoxData.width - width) / 2;
					}
					break;
				case ACTION_NORTH_EAST:
					if (aspectRatio) {
						if (range.y <= 0 && (top <= minTop || right >= maxWidth)) {
							renderable = false;
							break;
						}
						check(ACTION_NORTH);
						height -= range.y;
						top += range.y;
						width = height * aspectRatio;
					} else {
						check(ACTION_NORTH);
						check(ACTION_EAST);
						if (range.x >= 0) {
							if (right < maxWidth) width += range.x;
							else if (range.y <= 0 && top <= minTop) renderable = false;
						} else width += range.x;
						if (range.y <= 0) {
							if (top > minTop) {
								height -= range.y;
								top += range.y;
							}
						} else {
							height -= range.y;
							top += range.y;
						}
					}
					if (width < 0 && height < 0) {
						action = ACTION_SOUTH_WEST;
						height = -height;
						width = -width;
						top -= height;
						left -= width;
					} else if (width < 0) {
						action = ACTION_NORTH_WEST;
						width = -width;
						left -= width;
					} else if (height < 0) {
						action = ACTION_SOUTH_EAST;
						height = -height;
						top -= height;
					}
					break;
				case ACTION_NORTH_WEST:
					if (aspectRatio) {
						if (range.y <= 0 && (top <= minTop || left <= minLeft)) {
							renderable = false;
							break;
						}
						check(ACTION_NORTH);
						height -= range.y;
						top += range.y;
						width = height * aspectRatio;
						left += cropBoxData.width - width;
					} else {
						check(ACTION_NORTH);
						check(ACTION_WEST);
						if (range.x <= 0) {
							if (left > minLeft) {
								width -= range.x;
								left += range.x;
							} else if (range.y <= 0 && top <= minTop) renderable = false;
						} else {
							width -= range.x;
							left += range.x;
						}
						if (range.y <= 0) {
							if (top > minTop) {
								height -= range.y;
								top += range.y;
							}
						} else {
							height -= range.y;
							top += range.y;
						}
					}
					if (width < 0 && height < 0) {
						action = ACTION_SOUTH_EAST;
						height = -height;
						width = -width;
						top -= height;
						left -= width;
					} else if (width < 0) {
						action = ACTION_NORTH_EAST;
						width = -width;
						left -= width;
					} else if (height < 0) {
						action = ACTION_SOUTH_WEST;
						height = -height;
						top -= height;
					}
					break;
				case ACTION_SOUTH_WEST:
					if (aspectRatio) {
						if (range.x <= 0 && (left <= minLeft || bottom >= maxHeight)) {
							renderable = false;
							break;
						}
						check(ACTION_WEST);
						width -= range.x;
						left += range.x;
						height = width / aspectRatio;
					} else {
						check(ACTION_SOUTH);
						check(ACTION_WEST);
						if (range.x <= 0) {
							if (left > minLeft) {
								width -= range.x;
								left += range.x;
							} else if (range.y >= 0 && bottom >= maxHeight) renderable = false;
						} else {
							width -= range.x;
							left += range.x;
						}
						if (range.y >= 0) {
							if (bottom < maxHeight) height += range.y;
						} else height += range.y;
					}
					if (width < 0 && height < 0) {
						action = ACTION_NORTH_EAST;
						height = -height;
						width = -width;
						top -= height;
						left -= width;
					} else if (width < 0) {
						action = ACTION_SOUTH_EAST;
						width = -width;
						left -= width;
					} else if (height < 0) {
						action = ACTION_NORTH_WEST;
						height = -height;
						top -= height;
					}
					break;
				case ACTION_SOUTH_EAST:
					if (aspectRatio) {
						if (range.x >= 0 && (right >= maxWidth || bottom >= maxHeight)) {
							renderable = false;
							break;
						}
						check(ACTION_EAST);
						width += range.x;
						height = width / aspectRatio;
					} else {
						check(ACTION_SOUTH);
						check(ACTION_EAST);
						if (range.x >= 0) {
							if (right < maxWidth) width += range.x;
							else if (range.y >= 0 && bottom >= maxHeight) renderable = false;
						} else width += range.x;
						if (range.y >= 0) {
							if (bottom < maxHeight) height += range.y;
						} else height += range.y;
					}
					if (width < 0 && height < 0) {
						action = ACTION_NORTH_WEST;
						height = -height;
						width = -width;
						top -= height;
						left -= width;
					} else if (width < 0) {
						action = ACTION_SOUTH_WEST;
						width = -width;
						left -= width;
					} else if (height < 0) {
						action = ACTION_NORTH_EAST;
						height = -height;
						top -= height;
					}
					break;
				case ACTION_MOVE:
					this.move(range.x, range.y);
					renderable = false;
					break;
				case ACTION_ZOOM:
					this.zoom(getMaxZoomRatio(pointers), event);
					renderable = false;
					break;
				case ACTION_CROP:
					if (!range.x || !range.y) {
						renderable = false;
						break;
					}
					offset = getOffset(this.cropper);
					left = pointer.startX - offset.left;
					top = pointer.startY - offset.top;
					width = cropBoxData.minWidth;
					height = cropBoxData.minHeight;
					if (range.x > 0) action = range.y > 0 ? ACTION_SOUTH_EAST : ACTION_NORTH_EAST;
					else if (range.x < 0) {
						left -= width;
						action = range.y > 0 ? ACTION_SOUTH_WEST : ACTION_NORTH_WEST;
					}
					if (range.y < 0) top -= height;
					if (!this.cropped) {
						removeClass(this.cropBox, CLASS_HIDDEN);
						this.cropped = true;
						if (this.limited) this.limitCropBox(true, true);
					}
			}
			if (renderable) {
				cropBoxData.width = width;
				cropBoxData.height = height;
				cropBoxData.left = left;
				cropBoxData.top = top;
				this.action = action;
				this.renderCropBox();
			}
			forEach(pointers, function(p) {
				p.startX = p.endX;
				p.startY = p.endY;
			});
		} };
		var methods = {
			crop: function crop() {
				if (this.ready && !this.cropped && !this.disabled) {
					this.cropped = true;
					this.limitCropBox(true, true);
					if (this.options.modal) addClass(this.dragBox, CLASS_MODAL);
					removeClass(this.cropBox, CLASS_HIDDEN);
					this.setCropBoxData(this.initialCropBoxData);
				}
				return this;
			},
			reset: function reset() {
				if (this.ready && !this.disabled) {
					this.imageData = assign({}, this.initialImageData);
					this.canvasData = assign({}, this.initialCanvasData);
					this.cropBoxData = assign({}, this.initialCropBoxData);
					this.renderCanvas();
					if (this.cropped) this.renderCropBox();
				}
				return this;
			},
			clear: function clear() {
				if (this.cropped && !this.disabled) {
					assign(this.cropBoxData, {
						left: 0,
						top: 0,
						width: 0,
						height: 0
					});
					this.cropped = false;
					this.renderCropBox();
					this.limitCanvas(true, true);
					this.renderCanvas();
					removeClass(this.dragBox, CLASS_MODAL);
					addClass(this.cropBox, CLASS_HIDDEN);
				}
				return this;
			},
			/**
			* Replace the image's src and rebuild the cropper
			* @param {string} url - The new URL.
			* @param {boolean} [hasSameSize] - Indicate if the new image has the same size as the old one.
			* @returns {Cropper} this
			*/
			replace: function replace(url) {
				var hasSameSize = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : false;
				if (!this.disabled && url) {
					if (this.isImg) this.element.src = url;
					if (hasSameSize) {
						this.url = url;
						this.image.src = url;
						if (this.ready) {
							this.viewBoxImage.src = url;
							forEach(this.previews, function(element) {
								element.getElementsByTagName("img")[0].src = url;
							});
						}
					} else {
						if (this.isImg) this.replaced = true;
						this.options.data = null;
						this.uncreate();
						this.load(url);
					}
				}
				return this;
			},
			enable: function enable() {
				if (this.ready && this.disabled) {
					this.disabled = false;
					removeClass(this.cropper, CLASS_DISABLED);
				}
				return this;
			},
			disable: function disable() {
				if (this.ready && !this.disabled) {
					this.disabled = true;
					addClass(this.cropper, CLASS_DISABLED);
				}
				return this;
			},
			/**
			* Destroy the cropper and remove the instance from the image
			* @returns {Cropper} this
			*/
			destroy: function destroy() {
				var element = this.element;
				if (!element[NAMESPACE]) return this;
				element[NAMESPACE] = void 0;
				if (this.isImg && this.replaced) element.src = this.originalUrl;
				this.uncreate();
				return this;
			},
			/**
			* Move the canvas with relative offsets
			* @param {number} offsetX - The relative offset distance on the x-axis.
			* @param {number} [offsetY=offsetX] - The relative offset distance on the y-axis.
			* @returns {Cropper} this
			*/
			move: function move(offsetX) {
				var offsetY = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : offsetX;
				var _this$canvasData = this.canvasData, left = _this$canvasData.left, top = _this$canvasData.top;
				return this.moveTo(isUndefined(offsetX) ? offsetX : left + Number(offsetX), isUndefined(offsetY) ? offsetY : top + Number(offsetY));
			},
			/**
			* Move the canvas to an absolute point
			* @param {number} x - The x-axis coordinate.
			* @param {number} [y=x] - The y-axis coordinate.
			* @returns {Cropper} this
			*/
			moveTo: function moveTo(x) {
				var y = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : x;
				var canvasData = this.canvasData;
				var changed = false;
				x = Number(x);
				y = Number(y);
				if (this.ready && !this.disabled && this.options.movable) {
					if (isNumber(x)) {
						canvasData.left = x;
						changed = true;
					}
					if (isNumber(y)) {
						canvasData.top = y;
						changed = true;
					}
					if (changed) this.renderCanvas(true);
				}
				return this;
			},
			/**
			* Zoom the canvas with a relative ratio
			* @param {number} ratio - The target ratio.
			* @param {Event} _originalEvent - The original event if any.
			* @returns {Cropper} this
			*/
			zoom: function zoom(ratio, _originalEvent) {
				var canvasData = this.canvasData;
				ratio = Number(ratio);
				if (ratio < 0) ratio = 1 / (1 - ratio);
				else ratio = 1 + ratio;
				return this.zoomTo(canvasData.width * ratio / canvasData.naturalWidth, null, _originalEvent);
			},
			/**
			* Zoom the canvas to an absolute ratio
			* @param {number} ratio - The target ratio.
			* @param {Object} pivot - The zoom pivot point coordinate.
			* @param {Event} _originalEvent - The original event if any.
			* @returns {Cropper} this
			*/
			zoomTo: function zoomTo(ratio, pivot, _originalEvent) {
				var options = this.options, canvasData = this.canvasData;
				var width = canvasData.width, height = canvasData.height, naturalWidth = canvasData.naturalWidth, naturalHeight = canvasData.naturalHeight;
				ratio = Number(ratio);
				if (ratio >= 0 && this.ready && !this.disabled && options.zoomable) {
					var newWidth = naturalWidth * ratio;
					var newHeight = naturalHeight * ratio;
					if (dispatchEvent(this.element, EVENT_ZOOM, {
						ratio,
						oldRatio: width / naturalWidth,
						originalEvent: _originalEvent
					}) === false) return this;
					if (_originalEvent) {
						var pointers = this.pointers;
						var offset = getOffset(this.cropper);
						var center = pointers && Object.keys(pointers).length ? getPointersCenter(pointers) : {
							pageX: _originalEvent.pageX,
							pageY: _originalEvent.pageY
						};
						canvasData.left -= (newWidth - width) * ((center.pageX - offset.left - canvasData.left) / width);
						canvasData.top -= (newHeight - height) * ((center.pageY - offset.top - canvasData.top) / height);
					} else if (isPlainObject(pivot) && isNumber(pivot.x) && isNumber(pivot.y)) {
						canvasData.left -= (newWidth - width) * ((pivot.x - canvasData.left) / width);
						canvasData.top -= (newHeight - height) * ((pivot.y - canvasData.top) / height);
					} else {
						canvasData.left -= (newWidth - width) / 2;
						canvasData.top -= (newHeight - height) / 2;
					}
					canvasData.width = newWidth;
					canvasData.height = newHeight;
					this.renderCanvas(true);
				}
				return this;
			},
			/**
			* Rotate the canvas with a relative degree
			* @param {number} degree - The rotate degree.
			* @returns {Cropper} this
			*/
			rotate: function rotate(degree) {
				return this.rotateTo((this.imageData.rotate || 0) + Number(degree));
			},
			/**
			* Rotate the canvas to an absolute degree
			* @param {number} degree - The rotate degree.
			* @returns {Cropper} this
			*/
			rotateTo: function rotateTo(degree) {
				degree = Number(degree);
				if (isNumber(degree) && this.ready && !this.disabled && this.options.rotatable) {
					this.imageData.rotate = degree % 360;
					this.renderCanvas(true, true);
				}
				return this;
			},
			/**
			* Scale the image on the x-axis.
			* @param {number} scaleX - The scale ratio on the x-axis.
			* @returns {Cropper} this
			*/
			scaleX: function scaleX(_scaleX) {
				var scaleY = this.imageData.scaleY;
				return this.scale(_scaleX, isNumber(scaleY) ? scaleY : 1);
			},
			/**
			* Scale the image on the y-axis.
			* @param {number} scaleY - The scale ratio on the y-axis.
			* @returns {Cropper} this
			*/
			scaleY: function scaleY(_scaleY) {
				var scaleX = this.imageData.scaleX;
				return this.scale(isNumber(scaleX) ? scaleX : 1, _scaleY);
			},
			/**
			* Scale the image
			* @param {number} scaleX - The scale ratio on the x-axis.
			* @param {number} [scaleY=scaleX] - The scale ratio on the y-axis.
			* @returns {Cropper} this
			*/
			scale: function scale(scaleX) {
				var scaleY = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : scaleX;
				var imageData = this.imageData;
				var transformed = false;
				scaleX = Number(scaleX);
				scaleY = Number(scaleY);
				if (this.ready && !this.disabled && this.options.scalable) {
					if (isNumber(scaleX)) {
						imageData.scaleX = scaleX;
						transformed = true;
					}
					if (isNumber(scaleY)) {
						imageData.scaleY = scaleY;
						transformed = true;
					}
					if (transformed) this.renderCanvas(true, true);
				}
				return this;
			},
			/**
			* Get the cropped area position and size data (base on the original image)
			* @param {boolean} [rounded=false] - Indicate if round the data values or not.
			* @returns {Object} The result cropped data.
			*/
			getData: function getData() {
				var rounded = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : false;
				var options = this.options, imageData = this.imageData, canvasData = this.canvasData, cropBoxData = this.cropBoxData;
				var data;
				if (this.ready && this.cropped) {
					data = {
						x: cropBoxData.left - canvasData.left,
						y: cropBoxData.top - canvasData.top,
						width: cropBoxData.width,
						height: cropBoxData.height
					};
					var ratio = imageData.width / imageData.naturalWidth;
					forEach(data, function(n, i) {
						data[i] = n / ratio;
					});
					if (rounded) {
						var bottom = Math.round(data.y + data.height);
						var right = Math.round(data.x + data.width);
						data.x = Math.round(data.x);
						data.y = Math.round(data.y);
						data.width = right - data.x;
						data.height = bottom - data.y;
					}
				} else data = {
					x: 0,
					y: 0,
					width: 0,
					height: 0
				};
				if (options.rotatable) data.rotate = imageData.rotate || 0;
				if (options.scalable) {
					data.scaleX = imageData.scaleX || 1;
					data.scaleY = imageData.scaleY || 1;
				}
				return data;
			},
			/**
			* Set the cropped area position and size with new data
			* @param {Object} data - The new data.
			* @returns {Cropper} this
			*/
			setData: function setData(data) {
				var options = this.options, imageData = this.imageData, canvasData = this.canvasData;
				var cropBoxData = {};
				if (this.ready && !this.disabled && isPlainObject(data)) {
					var transformed = false;
					if (options.rotatable) {
						if (isNumber(data.rotate) && data.rotate !== imageData.rotate) {
							imageData.rotate = data.rotate;
							transformed = true;
						}
					}
					if (options.scalable) {
						if (isNumber(data.scaleX) && data.scaleX !== imageData.scaleX) {
							imageData.scaleX = data.scaleX;
							transformed = true;
						}
						if (isNumber(data.scaleY) && data.scaleY !== imageData.scaleY) {
							imageData.scaleY = data.scaleY;
							transformed = true;
						}
					}
					if (transformed) this.renderCanvas(true, true);
					var ratio = imageData.width / imageData.naturalWidth;
					if (isNumber(data.x)) cropBoxData.left = data.x * ratio + canvasData.left;
					if (isNumber(data.y)) cropBoxData.top = data.y * ratio + canvasData.top;
					if (isNumber(data.width)) cropBoxData.width = data.width * ratio;
					if (isNumber(data.height)) cropBoxData.height = data.height * ratio;
					this.setCropBoxData(cropBoxData);
				}
				return this;
			},
			/**
			* Get the container size data.
			* @returns {Object} The result container data.
			*/
			getContainerData: function getContainerData() {
				return this.ready ? assign({}, this.containerData) : {};
			},
			/**
			* Get the image position and size data.
			* @returns {Object} The result image data.
			*/
			getImageData: function getImageData() {
				return this.sized ? assign({}, this.imageData) : {};
			},
			/**
			* Get the canvas position and size data.
			* @returns {Object} The result canvas data.
			*/
			getCanvasData: function getCanvasData() {
				var canvasData = this.canvasData;
				var data = {};
				if (this.ready) forEach([
					"left",
					"top",
					"width",
					"height",
					"naturalWidth",
					"naturalHeight"
				], function(n) {
					data[n] = canvasData[n];
				});
				return data;
			},
			/**
			* Set the canvas position and size with new data.
			* @param {Object} data - The new canvas data.
			* @returns {Cropper} this
			*/
			setCanvasData: function setCanvasData(data) {
				var canvasData = this.canvasData;
				var aspectRatio = canvasData.aspectRatio;
				if (this.ready && !this.disabled && isPlainObject(data)) {
					if (isNumber(data.left)) canvasData.left = data.left;
					if (isNumber(data.top)) canvasData.top = data.top;
					if (isNumber(data.width)) {
						canvasData.width = data.width;
						canvasData.height = data.width / aspectRatio;
					} else if (isNumber(data.height)) {
						canvasData.height = data.height;
						canvasData.width = data.height * aspectRatio;
					}
					this.renderCanvas(true);
				}
				return this;
			},
			/**
			* Get the crop box position and size data.
			* @returns {Object} The result crop box data.
			*/
			getCropBoxData: function getCropBoxData() {
				var cropBoxData = this.cropBoxData;
				var data;
				if (this.ready && this.cropped) data = {
					left: cropBoxData.left,
					top: cropBoxData.top,
					width: cropBoxData.width,
					height: cropBoxData.height
				};
				return data || {};
			},
			/**
			* Set the crop box position and size with new data.
			* @param {Object} data - The new crop box data.
			* @returns {Cropper} this
			*/
			setCropBoxData: function setCropBoxData(data) {
				var cropBoxData = this.cropBoxData;
				var aspectRatio = this.options.aspectRatio;
				var widthChanged;
				var heightChanged;
				if (this.ready && this.cropped && !this.disabled && isPlainObject(data)) {
					if (isNumber(data.left)) cropBoxData.left = data.left;
					if (isNumber(data.top)) cropBoxData.top = data.top;
					if (isNumber(data.width) && data.width !== cropBoxData.width) {
						widthChanged = true;
						cropBoxData.width = data.width;
					}
					if (isNumber(data.height) && data.height !== cropBoxData.height) {
						heightChanged = true;
						cropBoxData.height = data.height;
					}
					if (aspectRatio) {
						if (widthChanged) cropBoxData.height = cropBoxData.width / aspectRatio;
						else if (heightChanged) cropBoxData.width = cropBoxData.height * aspectRatio;
					}
					this.renderCropBox();
				}
				return this;
			},
			/**
			* Get a canvas drawn the cropped image.
			* @param {Object} [options={}] - The config options.
			* @returns {HTMLCanvasElement} - The result canvas.
			*/
			getCroppedCanvas: function getCroppedCanvas() {
				var options = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
				if (!this.ready || !window.HTMLCanvasElement) return null;
				var canvasData = this.canvasData;
				var source = getSourceCanvas(this.image, this.imageData, canvasData, options);
				if (!this.cropped) return source;
				var _this$getData = this.getData(options.rounded), initialX = _this$getData.x, initialY = _this$getData.y, initialWidth = _this$getData.width, initialHeight = _this$getData.height;
				var ratio = source.width / Math.floor(canvasData.naturalWidth);
				if (ratio !== 1) {
					initialX *= ratio;
					initialY *= ratio;
					initialWidth *= ratio;
					initialHeight *= ratio;
				}
				var aspectRatio = initialWidth / initialHeight;
				var maxSizes = getAdjustedSizes({
					aspectRatio,
					width: options.maxWidth || Infinity,
					height: options.maxHeight || Infinity
				});
				var minSizes = getAdjustedSizes({
					aspectRatio,
					width: options.minWidth || 0,
					height: options.minHeight || 0
				}, "cover");
				var _getAdjustedSizes = getAdjustedSizes({
					aspectRatio,
					width: options.width || (ratio !== 1 ? source.width : initialWidth),
					height: options.height || (ratio !== 1 ? source.height : initialHeight)
				}), width = _getAdjustedSizes.width, height = _getAdjustedSizes.height;
				width = Math.min(maxSizes.width, Math.max(minSizes.width, width));
				height = Math.min(maxSizes.height, Math.max(minSizes.height, height));
				var canvas = document.createElement("canvas");
				var context = canvas.getContext("2d");
				canvas.width = normalizeDecimalNumber(width);
				canvas.height = normalizeDecimalNumber(height);
				context.fillStyle = options.fillColor || "transparent";
				context.fillRect(0, 0, width, height);
				var _options$imageSmoothi = options.imageSmoothingEnabled, imageSmoothingEnabled = _options$imageSmoothi === void 0 ? true : _options$imageSmoothi, imageSmoothingQuality = options.imageSmoothingQuality;
				context.imageSmoothingEnabled = imageSmoothingEnabled;
				if (imageSmoothingQuality) context.imageSmoothingQuality = imageSmoothingQuality;
				var sourceWidth = source.width;
				var sourceHeight = source.height;
				var srcX = initialX;
				var srcY = initialY;
				var srcWidth;
				var srcHeight;
				var dstX;
				var dstY;
				var dstWidth;
				var dstHeight;
				if (srcX <= -initialWidth || srcX > sourceWidth) {
					srcX = 0;
					srcWidth = 0;
					dstX = 0;
					dstWidth = 0;
				} else if (srcX <= 0) {
					dstX = -srcX;
					srcX = 0;
					srcWidth = Math.min(sourceWidth, initialWidth + srcX);
					dstWidth = srcWidth;
				} else if (srcX <= sourceWidth) {
					dstX = 0;
					srcWidth = Math.min(initialWidth, sourceWidth - srcX);
					dstWidth = srcWidth;
				}
				if (srcWidth <= 0 || srcY <= -initialHeight || srcY > sourceHeight) {
					srcY = 0;
					srcHeight = 0;
					dstY = 0;
					dstHeight = 0;
				} else if (srcY <= 0) {
					dstY = -srcY;
					srcY = 0;
					srcHeight = Math.min(sourceHeight, initialHeight + srcY);
					dstHeight = srcHeight;
				} else if (srcY <= sourceHeight) {
					dstY = 0;
					srcHeight = Math.min(initialHeight, sourceHeight - srcY);
					dstHeight = srcHeight;
				}
				var params = [
					srcX,
					srcY,
					srcWidth,
					srcHeight
				];
				if (dstWidth > 0 && dstHeight > 0) {
					var scale = width / initialWidth;
					params.push(dstX * scale, dstY * scale, dstWidth * scale, dstHeight * scale);
				}
				context.drawImage.apply(context, [source].concat(_toConsumableArray(params.map(function(param) {
					return Math.floor(normalizeDecimalNumber(param));
				}))));
				return canvas;
			},
			/**
			* Change the aspect ratio of the crop box.
			* @param {number} aspectRatio - The new aspect ratio.
			* @returns {Cropper} this
			*/
			setAspectRatio: function setAspectRatio(aspectRatio) {
				var options = this.options;
				if (!this.disabled && !isUndefined(aspectRatio)) {
					options.aspectRatio = Math.max(0, aspectRatio) || NaN;
					if (this.ready) {
						this.initCropBox();
						if (this.cropped) this.renderCropBox();
					}
				}
				return this;
			},
			/**
			* Change the drag mode.
			* @param {string} mode - The new drag mode.
			* @returns {Cropper} this
			*/
			setDragMode: function setDragMode(mode) {
				var options = this.options, dragBox = this.dragBox, face = this.face;
				if (this.ready && !this.disabled) {
					var croppable = mode === DRAG_MODE_CROP;
					var movable = options.movable && mode === DRAG_MODE_MOVE;
					mode = croppable || movable ? mode : DRAG_MODE_NONE;
					options.dragMode = mode;
					setData(dragBox, DATA_ACTION, mode);
					toggleClass(dragBox, CLASS_CROP, croppable);
					toggleClass(dragBox, CLASS_MOVE, movable);
					if (!options.cropBoxMovable) {
						setData(face, DATA_ACTION, mode);
						toggleClass(face, CLASS_CROP, croppable);
						toggleClass(face, CLASS_MOVE, movable);
					}
				}
				return this;
			}
		};
		var AnotherCropper = WINDOW.Cropper;
		var Cropper = /*#__PURE__*/ function() {
			/**
			* Create a new Cropper.
			* @param {Element} element - The target element for cropping.
			* @param {Object} [options={}] - The configuration options.
			*/
			function Cropper(element) {
				var options = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
				_classCallCheck(this, Cropper);
				if (!element || !REGEXP_TAG_NAME.test(element.tagName)) throw new Error("The first argument is required and must be an <img> or <canvas> element.");
				this.element = element;
				this.options = assign({}, DEFAULTS, isPlainObject(options) && options);
				this.cropped = false;
				this.disabled = false;
				this.pointers = {};
				this.ready = false;
				this.reloading = false;
				this.replaced = false;
				this.sized = false;
				this.sizing = false;
				this.init();
			}
			return _createClass(Cropper, [
				{
					key: "init",
					value: function init() {
						var element = this.element;
						var tagName = element.tagName.toLowerCase();
						var url;
						if (element[NAMESPACE]) return;
						element[NAMESPACE] = this;
						if (tagName === "img") {
							this.isImg = true;
							url = element.getAttribute("src") || "";
							this.originalUrl = url;
							if (!url) return;
							url = element.src;
						} else if (tagName === "canvas" && window.HTMLCanvasElement) url = element.toDataURL();
						this.load(url);
					}
				},
				{
					key: "load",
					value: function load(url) {
						var _this = this;
						if (!url) return;
						this.url = url;
						this.imageData = {};
						var element = this.element, options = this.options;
						if (!options.rotatable && !options.scalable) options.checkOrientation = false;
						if (!options.checkOrientation || !window.ArrayBuffer) {
							this.clone();
							return;
						}
						if (REGEXP_DATA_URL.test(url)) {
							if (REGEXP_DATA_URL_JPEG.test(url)) this.read(dataURLToArrayBuffer(url));
							else this.clone();
							return;
						}
						var xhr = new XMLHttpRequest();
						var clone = this.clone.bind(this);
						this.reloading = true;
						this.xhr = xhr;
						xhr.onabort = clone;
						xhr.onerror = clone;
						xhr.ontimeout = clone;
						xhr.onprogress = function() {
							if (xhr.getResponseHeader("content-type") !== MIME_TYPE_JPEG) xhr.abort();
						};
						xhr.onload = function() {
							_this.read(xhr.response);
						};
						xhr.onloadend = function() {
							_this.reloading = false;
							_this.xhr = null;
						};
						if (options.checkCrossOrigin && isCrossOriginURL(url) && element.crossOrigin) url = addTimestamp(url);
						xhr.open("GET", url, true);
						xhr.responseType = "arraybuffer";
						xhr.withCredentials = element.crossOrigin === "use-credentials";
						xhr.send();
					}
				},
				{
					key: "read",
					value: function read(arrayBuffer) {
						var options = this.options, imageData = this.imageData;
						var orientation = resetAndGetOrientation(arrayBuffer);
						var rotate = 0;
						var scaleX = 1;
						var scaleY = 1;
						if (orientation > 1) {
							this.url = arrayBufferToDataURL(arrayBuffer, MIME_TYPE_JPEG);
							var _parseOrientation = parseOrientation(orientation);
							rotate = _parseOrientation.rotate;
							scaleX = _parseOrientation.scaleX;
							scaleY = _parseOrientation.scaleY;
						}
						if (options.rotatable) imageData.rotate = rotate;
						if (options.scalable) {
							imageData.scaleX = scaleX;
							imageData.scaleY = scaleY;
						}
						this.clone();
					}
				},
				{
					key: "clone",
					value: function clone() {
						var element = this.element, url = this.url;
						var crossOrigin = element.crossOrigin;
						var crossOriginUrl = url;
						if (this.options.checkCrossOrigin && isCrossOriginURL(url)) {
							if (!crossOrigin) crossOrigin = "anonymous";
							crossOriginUrl = addTimestamp(url);
						}
						this.crossOrigin = crossOrigin;
						this.crossOriginUrl = crossOriginUrl;
						var image = document.createElement("img");
						if (crossOrigin) image.crossOrigin = crossOrigin;
						image.src = crossOriginUrl || url;
						image.alt = element.alt || "The image to crop";
						this.image = image;
						image.onload = this.start.bind(this);
						image.onerror = this.stop.bind(this);
						addClass(image, CLASS_HIDE);
						element.parentNode.insertBefore(image, element.nextSibling);
					}
				},
				{
					key: "start",
					value: function start() {
						var _this2 = this;
						var image = this.image;
						image.onload = null;
						image.onerror = null;
						this.sizing = true;
						var isIOSWebKit = WINDOW.navigator && /(?:iPad|iPhone|iPod).*?AppleWebKit/i.test(WINDOW.navigator.userAgent);
						var done = function done(naturalWidth, naturalHeight) {
							assign(_this2.imageData, {
								naturalWidth,
								naturalHeight,
								aspectRatio: naturalWidth / naturalHeight
							});
							_this2.initialImageData = assign({}, _this2.imageData);
							_this2.sizing = false;
							_this2.sized = true;
							_this2.build();
						};
						if (image.naturalWidth && !isIOSWebKit) {
							done(image.naturalWidth, image.naturalHeight);
							return;
						}
						var sizingImage = document.createElement("img");
						var body = document.body || document.documentElement;
						this.sizingImage = sizingImage;
						sizingImage.onload = function() {
							done(sizingImage.width, sizingImage.height);
							if (!isIOSWebKit) body.removeChild(sizingImage);
						};
						sizingImage.src = image.src;
						if (!isIOSWebKit) {
							sizingImage.style.cssText = "left:0;max-height:none!important;max-width:none!important;min-height:0!important;min-width:0!important;opacity:0;position:absolute;top:0;z-index:-1;";
							body.appendChild(sizingImage);
						}
					}
				},
				{
					key: "stop",
					value: function stop() {
						var image = this.image;
						image.onload = null;
						image.onerror = null;
						image.parentNode.removeChild(image);
						this.image = null;
					}
				},
				{
					key: "build",
					value: function build() {
						if (!this.sized || this.ready) return;
						var element = this.element, options = this.options, image = this.image;
						var container = element.parentNode;
						var template = document.createElement("div");
						template.innerHTML = TEMPLATE;
						var cropper = template.querySelector(".".concat(NAMESPACE, "-container"));
						var canvas = cropper.querySelector(".".concat(NAMESPACE, "-canvas"));
						var dragBox = cropper.querySelector(".".concat(NAMESPACE, "-drag-box"));
						var cropBox = cropper.querySelector(".".concat(NAMESPACE, "-crop-box"));
						var face = cropBox.querySelector(".".concat(NAMESPACE, "-face"));
						this.container = container;
						this.cropper = cropper;
						this.canvas = canvas;
						this.dragBox = dragBox;
						this.cropBox = cropBox;
						this.viewBox = cropper.querySelector(".".concat(NAMESPACE, "-view-box"));
						this.face = face;
						canvas.appendChild(image);
						addClass(element, CLASS_HIDDEN);
						container.insertBefore(cropper, element.nextSibling);
						removeClass(image, CLASS_HIDE);
						this.initPreview();
						this.bind();
						options.initialAspectRatio = Math.max(0, options.initialAspectRatio) || NaN;
						options.aspectRatio = Math.max(0, options.aspectRatio) || NaN;
						options.viewMode = Math.max(0, Math.min(3, Math.round(options.viewMode))) || 0;
						addClass(cropBox, CLASS_HIDDEN);
						if (!options.guides) addClass(cropBox.getElementsByClassName("".concat(NAMESPACE, "-dashed")), CLASS_HIDDEN);
						if (!options.center) addClass(cropBox.getElementsByClassName("".concat(NAMESPACE, "-center")), CLASS_HIDDEN);
						if (options.background) addClass(cropper, "".concat(NAMESPACE, "-bg"));
						if (!options.highlight) addClass(face, CLASS_INVISIBLE);
						if (options.cropBoxMovable) {
							addClass(face, CLASS_MOVE);
							setData(face, DATA_ACTION, ACTION_ALL);
						}
						if (!options.cropBoxResizable) {
							addClass(cropBox.getElementsByClassName("".concat(NAMESPACE, "-line")), CLASS_HIDDEN);
							addClass(cropBox.getElementsByClassName("".concat(NAMESPACE, "-point")), CLASS_HIDDEN);
						}
						this.render();
						this.ready = true;
						this.setDragMode(options.dragMode);
						if (options.autoCrop) this.crop();
						this.setData(options.data);
						if (isFunction(options.ready)) addListener(element, EVENT_READY, options.ready, { once: true });
						dispatchEvent(element, EVENT_READY);
					}
				},
				{
					key: "unbuild",
					value: function unbuild() {
						if (!this.ready) return;
						this.ready = false;
						this.unbind();
						this.resetPreview();
						var parentNode = this.cropper.parentNode;
						if (parentNode) parentNode.removeChild(this.cropper);
						removeClass(this.element, CLASS_HIDDEN);
					}
				},
				{
					key: "uncreate",
					value: function uncreate() {
						if (this.ready) {
							this.unbuild();
							this.ready = false;
							this.cropped = false;
						} else if (this.sizing) {
							this.sizingImage.onload = null;
							this.sizing = false;
							this.sized = false;
						} else if (this.reloading) {
							this.xhr.onabort = null;
							this.xhr.abort();
						} else if (this.image) this.stop();
					}
				}
			], [{
				key: "noConflict",
				value: function noConflict() {
					window.Cropper = AnotherCropper;
					return Cropper;
				}
			}, {
				key: "setDefaults",
				value: function setDefaults(options) {
					assign(DEFAULTS, isPlainObject(options) && options);
				}
			}]);
		}();
		assign(Cropper.prototype, render, preview, events, handlers, change, methods);
		return Cropper;
	}));
})))());
// resources/js/components/assets/Editor/CropEditor.vue
var _sfc_main$11 = {
	__name: "CropEditor",
	props: {
		asset: {
			type: Object,
			required: true
		},
		open: {
			type: Boolean,
			default: false
		},
		canReplace: {
			type: Boolean,
			default: false
		}
	},
	emits: [
		"replaced",
		"created",
		"update:open"
	],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const cropper = ref(null);
		const selectedRatio = ref(null);
		const baseRatio = ref(null);
		const isFlipped = ref(false);
		const enterBinding = ref(null);
		const saveBinding = ref(null);
		const isOptionKeyPressed = ref(false);
		const initialCropBoxCenter = ref(null);
		const isAdjustingCropBox = ref(false);
		const animationFrameId = ref(null);
		const imageRef = useTemplateRef("image");
		const showConfirmation = ref(false);
		const uploading = ref(false);
		const pendingCrop = ref(null);
		const cropDimensions = ref(null);
		const cropEdges = ref({});
		const defaultQuality = Statamic.$config.get("cropQuality") || 90;
		function normalizeFormat(format) {
			format = (format || "").toLowerCase();
			return format === "jpeg" ? "jpg" : format;
		}
		const sourceFormat = computed(() => normalizeFormat(props.asset.extension));
		const isConvertible = computed(() => [
			"jpg",
			"png",
			"webp",
			"avif"
		].includes(sourceFormat.value));
		const initialQuality = () => sourceFormat.value === "png" ? 100 : defaultQuality;
		const format = ref(sourceFormat.value);
		const quality = ref(initialQuality());
		const background = ref("white");
		const aspectRatios = ref(Statamic.$config.get("cropAspectRatios") || []);
		const formatLabels = {
			jpg: "JPEG",
			png: "PNG",
			webp: "WebP",
			avif: "AVIF"
		};
		const formatOptions = computed(() => {
			return [.../* @__PURE__ */ new Set([
				sourceFormat.value,
				"jpg",
				"webp"
			])].map((value) => ({
				value,
				label: formatLabels[value] ?? value.toUpperCase()
			}));
		});
		const outputUsesQuality = computed(() => [
			"jpg",
			"webp",
			"avif"
		].includes(format.value));
		const showQuality = computed(() => isConvertible.value);
		const showFormatSelector = computed(() => isConvertible.value);
		const showBackground = computed(() => format.value === "jpg" && [
			"png",
			"webp",
			"avif"
		].includes(sourceFormat.value));
		const canReplaceOutput = computed(() => props.canReplace && format.value === sourceFormat.value);
		watch(format, (value) => {
			if (value === "png") quality.value = 100;
		});
		watch(quality, (value) => {
			if (sourceFormat.value === "png" && format.value === "png" && value < 100) format.value = "webp";
		});
		watch(() => props.open, (newValue) => {
			if (newValue) bindKeyboardShortcuts();
			else cleanup();
		});
		onBeforeUnmount(() => cleanup());
		function cleanup() {
			unbindKeyboardShortcuts();
			destroyCropper();
			resetState();
		}
		function resetState() {
			selectedRatio.value = null;
			baseRatio.value = null;
			isFlipped.value = false;
			isAdjustingCropBox.value = false;
			initialCropBoxCenter.value = null;
			showConfirmation.value = false;
			uploading.value = false;
			pendingCrop.value = null;
			cropDimensions.value = null;
			cropEdges.value = {};
			quality.value = initialQuality();
			format.value = sourceFormat.value;
			background.value = "white";
		}
		function destroyCropper() {
			if (cropper.value) {
				cropper.value.destroy();
				cropper.value = null;
			}
		}
		const crossOrigin = computed(() => {
			try {
				return new URL(props.asset.preview, window.location.href).origin !== window.location.origin ? "anonymous" : null;
			} catch {
				return null;
			}
		});
		function onImageError() {
			if (crossOrigin.value) {
				toast.error(__("Unable to crop image from external source. The image must be served with proper CORS headers."));
				close();
			}
		}
		function initCropper() {
			destroyCropper();
			imageRef.value.decode().then(() => createCropper(imageRef.value));
		}
		function createCropper(imageElement) {
			cropper.value = new import_cropper.default(imageElement, {
				aspectRatio: NaN,
				viewMode: 1,
				dragMode: "crop",
				autoCropArea: 1,
				restore: false,
				guides: true,
				center: true,
				highlight: true,
				cropBoxMovable: true,
				cropBoxResizable: true,
				zoomable: true,
				zoomOnTouch: true,
				zoomOnWheel: true,
				scalable: false,
				rotatable: false,
				responsive: true,
				movable: false,
				cropstart: onCropStart,
				cropmove: onCropMove,
				cropend: onCropEnd,
				ready: updateCropEdges,
				crop: onCrop
			});
		}
		function onCrop(event) {
			cropDimensions.value = {
				width: Math.round(event.detail.width),
				height: Math.round(event.detail.height)
			};
			updateCropEdges();
		}
		function updateCropEdges() {
			if (!cropper.value) return;
			const container = cropper.value.getContainerData();
			const canvas = cropper.value.getCanvasData();
			const box = cropper.value.getCropBoxData();
			const top = Math.max(canvas.top, 0);
			const left = Math.max(canvas.left, 0);
			const right = Math.min(canvas.left + canvas.width, container.width);
			const bottom = Math.min(canvas.top + canvas.height, container.height);
			cropEdges.value = {
				"at-top": box.top - top < 1,
				"at-left": box.left - left < 1,
				"at-right": right - (box.left + box.width) < 1,
				"at-bottom": bottom - (box.top + box.height) < 1
			};
		}
		function onCropStart() {
			const cropBoxData = cropper.value.getCropBoxData();
			initialCropBoxCenter.value = {
				x: cropBoxData.left + cropBoxData.width / 2,
				y: cropBoxData.top + cropBoxData.height / 2
			};
			isAdjustingCropBox.value = false;
		}
		function onCropMove() {
			if (!isOptionKeyPressed.value || !initialCropBoxCenter.value || isAdjustingCropBox.value) return;
			if (animationFrameId.value) cancelAnimationFrame(animationFrameId.value);
			animationFrameId.value = requestAnimationFrame(() => {
				adjustCropBoxCenter();
			});
		}
		function onCropEnd() {
			if (animationFrameId.value) {
				cancelAnimationFrame(animationFrameId.value);
				animationFrameId.value = null;
			}
			initialCropBoxCenter.value = null;
			isAdjustingCropBox.value = false;
		}
		function adjustCropBoxCenter() {
			if (!isOptionKeyPressed.value || !initialCropBoxCenter.value || isAdjustingCropBox.value) return;
			isAdjustingCropBox.value = true;
			const cropBoxData = cropper.value.getCropBoxData();
			const currentCenter = {
				x: cropBoxData.left + cropBoxData.width / 2,
				y: cropBoxData.top + cropBoxData.height / 2
			};
			const centerDeltaX = currentCenter.x - initialCropBoxCenter.value.x;
			const centerDeltaY = currentCenter.y - initialCropBoxCenter.value.y;
			if (Math.abs(centerDeltaX) > 1 || Math.abs(centerDeltaY) > 1) {
				const newLeft = initialCropBoxCenter.value.x - cropBoxData.width / 2;
				const newTop = initialCropBoxCenter.value.y - cropBoxData.height / 2;
				cropper.value.setCropBoxData({
					left: newLeft,
					top: newTop,
					width: cropBoxData.width,
					height: cropBoxData.height
				});
			}
			isAdjustingCropBox.value = false;
		}
		function setAspectRatio(ratio) {
			if (ratio === null) {
				cropper.value.setAspectRatio(NaN);
				baseRatio.value = null;
				isFlipped.value = false;
			} else {
				baseRatio.value = ratio;
				isFlipped.value = false;
				applyCurrentRatio();
				expandCropBoxToFill();
			}
		}
		function toggleOrientation() {
			if (baseRatio.value === null) return;
			isFlipped.value = !isFlipped.value;
			applyCurrentRatio();
			expandCropBoxToFill();
		}
		function applyCurrentRatio() {
			if (baseRatio.value === null) return;
			const ratioToApply = isFlipped.value ? 1 / baseRatio.value : baseRatio.value;
			const matchingRatio = aspectRatios.value.find((r) => Math.abs(r.value - ratioToApply) < .001);
			if (matchingRatio && matchingRatio.value === ratioToApply) selectedRatio.value = matchingRatio.value;
			cropper.value.setAspectRatio(ratioToApply);
		}
		function expandCropBoxToFill() {
			const canvasData = cropper.value.getCanvasData();
			let cropWidth = canvasData.width;
			let cropHeight = canvasData.height;
			if (baseRatio.value !== null) {
				const ratioToApply = isFlipped.value ? 1 / baseRatio.value : baseRatio.value;
				if (canvasData.width / canvasData.height > ratioToApply) {
					cropWidth = canvasData.height * ratioToApply;
					cropHeight = canvasData.height;
				} else {
					cropWidth = canvasData.width;
					cropHeight = canvasData.width / ratioToApply;
				}
			}
			const left = canvasData.left + (canvasData.width - cropWidth) / 2;
			const top = canvasData.top + (canvasData.height - cropHeight) / 2;
			cropper.value.setCropBoxData({
				left,
				top,
				width: cropWidth,
				height: cropHeight
			});
		}
		function crop() {
			const data = cropper.value.getData(true);
			if (!data || data.width < 1 || data.height < 1) {
				toast.error(__("Failed to crop image"));
				return;
			}
			pendingCrop.value = {
				x: Math.max(0, data.x),
				y: Math.max(0, data.y),
				width: data.width,
				height: data.height
			};
			showConfirmation.value = true;
		}
		function reset() {
			resetState();
			cropper.value.reset();
			cropper.value.setAspectRatio(NaN);
		}
		function bindKeyboardShortcuts() {
			enterBinding.value = keys.bindGlobal("enter", (e) => {
				if (cropper.value && !e.shiftKey && !e.ctrlKey && !e.metaKey) {
					const activeElement = document.activeElement;
					if (!(activeElement && (activeElement.tagName === "INPUT" || activeElement.tagName === "TEXTAREA" || activeElement.tagName === "SELECT"))) {
						e.preventDefault();
						crop();
					}
				}
			});
			saveBinding.value = keys.bindGlobal(["mod+s", "mod+shift+s"], (e) => {
				e.preventDefault();
				if (cropper.value && !showConfirmation.value && !uploading.value) crop();
			});
			window.addEventListener("keydown", handleKeyDown);
			window.addEventListener("keyup", handleKeyUp);
		}
		function handleKeyDown(event) {
			if (event.key === "Alt" || event.altKey) isOptionKeyPressed.value = true;
		}
		function handleKeyUp(event) {
			if (event.key === "Alt") isOptionKeyPressed.value = false;
		}
		function unbindKeyboardShortcuts() {
			if (enterBinding.value) {
				enterBinding.value.destroy();
				enterBinding.value = null;
			}
			if (saveBinding.value) {
				saveBinding.value.destroy();
				saveBinding.value = null;
			}
			window.removeEventListener("keydown", handleKeyDown);
			window.removeEventListener("keyup", handleKeyUp);
			if (animationFrameId.value) {
				cancelAnimationFrame(animationFrameId.value);
				animationFrameId.value = null;
			}
			isOptionKeyPressed.value = false;
			isAdjustingCropBox.value = false;
		}
		async function upload(replaceOriginal) {
			if (!pendingCrop.value) return;
			uploading.value = true;
			try {
				const response = await axios.post(props.asset.cropUrl, {
					...pendingCrop.value,
					format: format.value,
					quality: outputUsesQuality.value ? quality.value : null,
					background: showBackground.value ? background.value : null,
					replace: replaceOriginal
				});
				if (response.data?.data) {
					showConfirmation.value = false;
					pendingCrop.value = null;
					close();
					await wait(300);
					if (replaceOriginal) {
						toast.success(__("Image replaced successfully"));
						emit("replaced");
					} else {
						toast.success(__("Cropped image saved successfully"));
						emit("created", response.data.data.id);
					}
				}
			} catch (error) {
				toast.error(error.response?.data?.message || __("Failed to upload cropped image"));
			} finally {
				uploading.value = false;
			}
		}
		function dismissConfirmation() {
			showConfirmation.value = false;
			pendingCrop.value = null;
		}
		function close() {
			emit("update:open", false);
		}
		const __returned__ = {
			emit,
			props,
			cropper,
			selectedRatio,
			baseRatio,
			isFlipped,
			enterBinding,
			saveBinding,
			isOptionKeyPressed,
			initialCropBoxCenter,
			isAdjustingCropBox,
			animationFrameId,
			imageRef,
			showConfirmation,
			uploading,
			pendingCrop,
			cropDimensions,
			cropEdges,
			defaultQuality,
			normalizeFormat,
			sourceFormat,
			isConvertible,
			initialQuality,
			format,
			quality,
			background,
			aspectRatios,
			formatLabels,
			formatOptions,
			outputUsesQuality,
			showQuality,
			showFormatSelector,
			showBackground,
			canReplaceOutput,
			cleanup,
			resetState,
			destroyCropper,
			crossOrigin,
			onImageError,
			initCropper,
			createCropper,
			onCrop,
			updateCropEdges,
			onCropStart,
			onCropMove,
			onCropEnd,
			adjustCropBoxCenter,
			setAspectRatio,
			toggleOrientation,
			applyCurrentRatio,
			expandCropBoxToFill,
			crop,
			reset,
			bindKeyboardShortcuts,
			handleKeyDown,
			handleKeyUp,
			unbindKeyboardShortcuts,
			upload,
			dismissConfirmation,
			close,
			get Cropper() {
				return import_cropper.default;
			},
			computed,
			onBeforeUnmount,
			ref,
			useTemplateRef,
			watch,
			get Button() {
				return Button_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Modal() {
				return Modal_default;
			},
			get Radio() {
				return Item_default;
			},
			get RadioGroup() {
				return Group_default;
			},
			get Select() {
				return Select_default;
			},
			get Slider() {
				return Slider_default;
			},
			get Stack() {
				return Stack_default;
			},
			get Field() {
				return Field_default;
			},
			get Subheading() {
				return Subheading_default;
			},
			get keys() {
				return keys;
			},
			get toast() {
				return toast;
			},
			get wait() {
				return wait;
			},
			get axios() {
				return axios;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$10 = { class: "min-h-0 flex h-full flex-col bg-gray-100 dark:bg-gray-850" };
var _hoisted_2$8 = { class: "relative flex w-full items-center justify-between px-4 py-3 border-b dark:border-gray-700" };
var _hoisted_3$8 = ["aria-label"];
var _hoisted_4$8 = [
	"src",
	"crossorigin",
	"alt"
];
var _hoisted_5$6 = ["aria-label", "textContent"];
var _hoisted_6$6 = { class: "flex flex-wrap items-center gap-3 border-t dark:border-gray-700 px-4 py-3" };
var _hoisted_7$5 = {
	key: 0,
	class: "flex gap-3"
};
var _hoisted_8$4 = { class: "flex gap-3 ms-auto" };
var _hoisted_9$4 = {
	key: 0,
	class: "pointer-events-none absolute inset-0 flex select-none items-center justify-center bg-white bg-opacity-75 dark:bg-gray-850"
};
var _hoisted_10$4 = {
	key: 1,
	class: "mt-6 space-y-4"
};
var _hoisted_11$4 = { class: "flex justify-between gap-4" };
var _hoisted_12$3 = { class: "flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg p-2 @lg:px-4 @lg:py-3 with-contrast:border with-contrast:border-gray-500" };
var _hoisted_13$2 = { key: 0 };
var _hoisted_14$1 = { class: "flex items-center justify-end space-x-3 pt-3 pb-1" };
function _sfc_render$10(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_button = resolveComponent("ui-button");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock($setup["Stack"], {
		size: "full",
		open: $props.open,
		inset: "",
		"onUpdate:open": _cache[7] || (_cache[7] = ($event) => _ctx.$emit("update:open", $event)),
		onOpened: $setup.initCropper,
		"show-close-button": false
	}, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$10, [
			createBaseVNode("header", _hoisted_2$8, [createVNode($setup["Heading"], { size: "lg" }, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Crop Image")), 1)]),
				_: 1
			}), createVNode(_component_ui_button, {
				variant: "ghost",
				icon: "x",
				round: "",
				onClick: $setup.close,
				"aria-label": _ctx.__("Close")
			}, null, 8, ["aria-label"])]),
			createBaseVNode("div", {
				class: "bg-gray-300 p-3 inset-shadow-xs dark:bg-gray-850 flex flex-1 flex-col overflow-auto relative min-h-0 w-full items-center justify-center",
				role: "img",
				"aria-label": _ctx.__("Image crop area")
			}, [createBaseVNode("div", { class: normalizeClass([$setup.cropEdges, "crop-editor-canvas h-full w-full min-h-0 flex items-center justify-center"]) }, [createBaseVNode("img", {
				ref: "image",
				src: $props.asset.preview,
				crossorigin: $setup.crossOrigin,
				alt: _ctx.__("Image to crop"),
				class: "max-w-full max-h-full",
				onError: $setup.onImageError
			}, null, 40, _hoisted_4$8)], 2), $setup.cropDimensions ? (openBlock(), createElementBlock("div", {
				key: 0,
				class: "absolute top-5 end-5 z-10 rounded-md bg-gray-900/75 px-2 py-1 text-xs font-medium text-white tabular-nums pointer-events-none",
				"aria-label": _ctx.__("Dimensions"),
				textContent: toDisplayString(_ctx.__("messages.width_x_height", {
					width: $setup.cropDimensions.width,
					height: $setup.cropDimensions.height
				}))
			}, null, 8, _hoisted_5$6)) : createCommentVNode("", true)], 8, _hoisted_3$8),
			createBaseVNode("div", _hoisted_6$6, [$setup.aspectRatios.length ? (openBlock(), createElementBlock("div", _hoisted_7$5, [createVNode($setup["Select"], {
				clearable: "",
				modelValue: $setup.selectedRatio,
				"onUpdate:modelValue": [_cache[0] || (_cache[0] = ($event) => $setup.selectedRatio = $event), $setup.setAspectRatio],
				options: $setup.aspectRatios,
				"option-label": "label",
				"option-value": "value",
				placeholder: _ctx.__("Aspect ratio"),
				size: "sm",
				class: "w-48",
				"aria-label": _ctx.__("Select aspect ratio"),
				"adaptive-width": ""
			}, null, 8, [
				"modelValue",
				"options",
				"placeholder",
				"aria-label"
			]), $setup.selectedRatio ? (openBlock(), createBlock($setup["Button"], {
				key: 0,
				icon: "flip-vertical",
				variant: $setup.isFlipped ? "pressed" : "ghost",
				size: "sm",
				text: _ctx.__("Flip Orientation"),
				"aria-label": _ctx.__("Flip crop orientation"),
				"aria-pressed": $setup.isFlipped,
				onClick: $setup.toggleOrientation
			}, null, 8, [
				"variant",
				"text",
				"aria-label",
				"aria-pressed"
			])) : createCommentVNode("", true)])) : createCommentVNode("", true), createBaseVNode("div", _hoisted_8$4, [
				createVNode($setup["Button"], {
					variant: "ghost",
					text: _ctx.__("Cancel"),
					"aria-label": _ctx.__("Cancel cropping"),
					onClick: $setup.close
				}, null, 8, ["text", "aria-label"]),
				createVNode($setup["Button"], {
					variant: "ghost",
					text: _ctx.__("Reset"),
					"aria-label": _ctx.__("Reset crop selection"),
					onClick: $setup.reset
				}, null, 8, ["text", "aria-label"]),
				createVNode($setup["Button"], {
					variant: "primary",
					text: _ctx.__("Finish"),
					"aria-label": _ctx.__("Finish cropping"),
					disabled: !$setup.cropper,
					onClick: $setup.crop
				}, null, 8, [
					"text",
					"aria-label",
					"disabled"
				])
			])]),
			createVNode($setup["Modal"], {
				open: $setup.showConfirmation,
				title: _ctx.__("Save Cropped Image"),
				dismissible: !$setup.uploading,
				"onUpdate:open": _cache[6] || (_cache[6] = (open) => {
					if (!open) $setup.dismissConfirmation();
				})
			}, {
				footer: withCtx(() => [createBaseVNode("div", _hoisted_14$1, [
					createVNode($setup["Button"], {
						variant: "ghost",
						disabled: $setup.uploading,
						text: _ctx.__("Cancel"),
						onClick: $setup.dismissConfirmation
					}, null, 8, ["disabled", "text"]),
					createVNode($setup["Button"], {
						disabled: $setup.uploading,
						text: _ctx.__("Save as Copy"),
						onClick: _cache[4] || (_cache[4] = ($event) => $setup.upload(false))
					}, null, 8, ["disabled", "text"]),
					$props.canReplace ? withDirectives((openBlock(), createBlock($setup["Button"], {
						key: 0,
						variant: "primary",
						disabled: $setup.uploading || !$setup.canReplaceOutput,
						text: _ctx.__("Replace Original"),
						onClick: _cache[5] || (_cache[5] = ($event) => $setup.upload(true))
					}, null, 8, ["disabled", "text"])), [[_directive_tooltip, $props.canReplace && !$setup.canReplaceOutput ? _ctx.__("messages.crop_replace_unavailable_format") : null]]) : createCommentVNode("", true)
				])]),
				default: withCtx(() => [
					$setup.uploading ? (openBlock(), createElementBlock("div", _hoisted_9$4, [createVNode($setup["Icon"], { name: "loading" })])) : createCommentVNode("", true),
					createBaseVNode("p", null, toDisplayString($setup.canReplaceOutput ? _ctx.__("messages.crop_save_copy_or_replace") : _ctx.__("messages.crop_save_as_copy_confirm")), 1),
					$setup.showQuality ? (openBlock(), createElementBlock("div", _hoisted_10$4, [createBaseVNode("div", _hoisted_11$4, [createVNode($setup["Field"], {
						id: "crop-quality",
						label: _ctx.__("Quality"),
						class: "w-1/2 space-y-2"
					}, {
						default: withCtx(() => [createBaseVNode("div", _hoisted_12$3, [createVNode($setup["Slider"], {
							id: "crop-quality",
							modelValue: $setup.quality,
							"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.quality = $event),
							min: 1,
							max: 100,
							"aria-label": _ctx.__("Quality")
						}, null, 8, ["modelValue", "aria-label"]), createVNode($setup["Subheading"], {
							text: `${$setup.quality}%`,
							class: "w-14 justify-center"
						}, null, 8, ["text"])])]),
						_: 1
					}, 8, ["label"]), $setup.showFormatSelector ? (openBlock(), createBlock($setup["Field"], {
						key: 0,
						class: "w-1/2 space-y-2",
						id: "crop-format",
						label: _ctx.__("Format")
					}, {
						default: withCtx(() => [createVNode($setup["Select"], {
							id: "crop-format",
							modelValue: $setup.format,
							"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.format = $event),
							options: $setup.formatOptions,
							"option-label": "label",
							"option-value": "value",
							size: "sm",
							"aria-label": _ctx.__("Output format")
						}, null, 8, [
							"modelValue",
							"options",
							"aria-label"
						])]),
						_: 1
					}, 8, ["label"])) : createCommentVNode("", true)]), $setup.showBackground ? (openBlock(), createElementBlock("div", _hoisted_13$2, [createVNode($setup["Field"], {
						id: "crop-background",
						label: _ctx.__("Background"),
						instructions: _ctx.__("messages.crop_jpeg_background_help")
					}, {
						default: withCtx(() => [createVNode($setup["RadioGroup"], {
							modelValue: $setup.background,
							"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.background = $event),
							appearance: "inline",
							"aria-label": _ctx.__("Background colour")
						}, {
							default: withCtx(() => [createVNode($setup["Radio"], {
								value: "white",
								label: _ctx.__("White")
							}, null, 8, ["label"]), createVNode($setup["Radio"], {
								value: "black",
								label: _ctx.__("Black")
							}, null, 8, ["label"])]),
							_: 1
						}, 8, ["modelValue", "aria-label"])]),
						_: 1
					}, 8, ["label", "instructions"])])) : createCommentVNode("", true)])) : createCommentVNode("", true)
				]),
				_: 1
			}, 8, [
				"open",
				"title",
				"dismissible"
			])
		])]),
		_: 1
	}, 8, ["open"]);
}
var CropEditor_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$11, [["render", _sfc_render$10], ["__file", "CropEditor.vue"]]);
// resources/js/components/assets/Editor/PdfViewer.vue
var _sfc_main$10 = {
	__name: "PdfViewer",
	props: { src: {
		type: String,
		required: true
	} },
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const pages = ref(null);
		const isLoading = ref(true);
		const isRendering = ref(false);
		const hasError = ref(false);
		let currentRenderId = 0;
		let loadingTask = null;
		let pdfDocument = null;
		let pageStates = [];
		let observer = null;
		const scale = Math.min(2, window.devicePixelRatio || 2);
		onMounted(() => renderPdf());
		onBeforeUnmount(() => cleanup());
		watch(() => props.src, () => renderPdf());
		watch(isRendering, (value) => Statamic.$progress.loading("pdf", value), { flush: "sync" });
		async function renderPdf() {
			const renderId = ++currentRenderId;
			cleanup({ invalidateRender: false });
			isLoading.value = true;
			isRendering.value = true;
			hasError.value = false;
			if (!props.src) {
				isLoading.value = false;
				isRendering.value = false;
				return;
			}
			try {
				const pdf = await loadDocument();
				if (renderId !== currentRenderId) return;
				pdfDocument = pdf;
				const viewerContext = await initViewer(pdf);
				for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
					const page = await pdf.getPage(pageNumber);
					if (renderId !== currentRenderId) return;
					const viewport = page.getViewport({ scale });
					const pageContainer = document.createElement("div");
					pageContainer.className = "pdf-page";
					pageContainer.dataset.pageNumber = String(pageNumber);
					const canvas = document.createElement("canvas");
					canvas.className = "pdf-page-canvas";
					canvas.width = Math.floor(viewport.width);
					canvas.height = Math.floor(viewport.height);
					pageContainer.appendChild(canvas);
					pages.value?.appendChild(pageContainer);
					pageStates.push({
						pageNumber,
						page,
						viewport,
						canvas,
						container: pageContainer,
						rendered: false,
						rendering: false,
						renderTask: null
					});
				}
				observer = new IntersectionObserver((entries) => {
					for (const entry of entries) {
						if (!entry.isIntersecting) continue;
						const pageNumber = parseInt(entry.target.dataset.pageNumber, 10);
						const state = pageStates[pageNumber - 1];
						if (state && !state.rendered && !state.rendering) renderPage(state, renderId, viewerContext);
					}
				}, {
					root: pages.value,
					rootMargin: "200px 0px"
				});
				for (const state of pageStates) observer.observe(state.container);
				if (pageStates.length > 0) await renderPage(pageStates[0], renderId, viewerContext);
				if (renderId !== currentRenderId) return;
				isLoading.value = false;
			} catch (error) {
				if (renderId === currentRenderId) {
					hasError.value = true;
					console.error(error);
				}
			} finally {
				if (renderId === currentRenderId) isRendering.value = false;
			}
		}
		async function renderPage(state, renderId, viewerContext) {
			if (renderId !== currentRenderId) return;
			state.rendering = true;
			try {
				const { canvas, page, viewport, container } = state;
				const { linkService, AnnotationLayerBuilder } = viewerContext;
				const canvasContext = canvas.getContext("2d");
				if (!canvasContext) return;
				const task = page.render({
					canvasContext,
					viewport
				});
				state.renderTask = task;
				await task.promise;
				if (renderId !== currentRenderId) return;
				await new AnnotationLayerBuilder({
					pdfPage: page,
					linkService,
					renderForms: true,
					onAppend: (div) => container.appendChild(div)
				}).render({ viewport });
				state.rendered = true;
			} catch (error) {
				if (renderId !== currentRenderId) return;
				if (error?.name === "RenderingCancelledException") return;
				const errorDiv = document.createElement("div");
				errorDiv.className = "pdf-page-error";
				errorDiv.textContent = __("Something went wrong");
				state.container.appendChild(errorDiv);
				console.warn(`Failed to render PDF page ${state.pageNumber}`, error);
			} finally {
				state.page.cleanup();
				state.rendering = false;
				state.renderTask = null;
			}
		}
		async function loadDocument() {
			const [pdfjsLib, { default: pdfjsWorkerUrl }] = await Promise.all([__vitePreload(() => import("./pdf-CYVFffGK.js"), [], import.meta.url), __vitePreload(() => import("./pdf.worker.min-DHWohrg3.js"), [], import.meta.url)]);
			pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorkerUrl;
			loadingTask = pdfjsLib.getDocument({
				url: props.src,
				verbosity: pdfjsLib.VerbosityLevel.ERRORS
			});
			return await loadingTask.promise;
		}
		async function initViewer(pdf) {
			const { AnnotationLayerBuilder, EventBus, PDFLinkService } = await __vitePreload(async () => {
				const { AnnotationLayerBuilder, EventBus, PDFLinkService } = await import("./pdf_viewer-sXb5B3ke.js");
				return {
					AnnotationLayerBuilder,
					EventBus,
					PDFLinkService
				};
			}, [], import.meta.url);
			const linkService = new PDFLinkService({ eventBus: new EventBus() });
			linkService.externalLinkEnabled = false;
			linkService.setViewer({
				currentPageNumber: 1,
				pagesRotation: 0,
				isInPresentationMode: false,
				pageLabelToPageNumber: () => null,
				scrollPageIntoView: ({ pageNumber }) => {
					const state = pageStates[pageNumber - 1];
					if (state?.container) state.container.scrollIntoView({
						behavior: "smooth",
						block: "start"
					});
				}
			});
			linkService.setDocument(pdf, null);
			return {
				linkService,
				AnnotationLayerBuilder
			};
		}
		function cleanup({ invalidateRender = true } = {}) {
			if (invalidateRender) currentRenderId++;
			isRendering.value = false;
			for (const state of pageStates) {
				state.renderTask?.cancel();
				state.renderTask = null;
			}
			observer?.disconnect();
			observer = null;
			loadingTask?.destroy();
			loadingTask = null;
			pdfDocument?.destroy();
			pdfDocument = null;
			pageStates = [];
			pages.value?.replaceChildren();
		}
		const __returned__ = {
			props,
			pages,
			isLoading,
			isRendering,
			hasError,
			get currentRenderId() {
				return currentRenderId;
			},
			set currentRenderId(v) {
				currentRenderId = v;
			},
			get loadingTask() {
				return loadingTask;
			},
			set loadingTask(v) {
				loadingTask = v;
			},
			get pdfDocument() {
				return pdfDocument;
			},
			set pdfDocument(v) {
				pdfDocument = v;
			},
			get pageStates() {
				return pageStates;
			},
			set pageStates(v) {
				pageStates = v;
			},
			get observer() {
				return observer;
			},
			set observer(v) {
				observer = v;
			},
			scale,
			renderPdf,
			renderPage,
			loadDocument,
			initViewer,
			cleanup,
			onBeforeUnmount,
			onMounted,
			ref,
			watch,
			get Icon() {
				return Icon_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$9 = { class: "relative h-full min-h-0" };
var _hoisted_2$7 = {
	key: 0,
	class: "h-full flex items-center justify-center"
};
var _hoisted_3$7 = ["textContent"];
var _hoisted_4$7 = {
	ref: "pages",
	class: "pdf-pages h-full min-h-0 overflow-auto text-gray-500"
};
function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$9, [$setup.isLoading || $setup.hasError ? (openBlock(), createElementBlock("div", _hoisted_2$7, [$setup.isLoading ? (openBlock(), createBlock($setup["Icon"], {
		key: 0,
		name: "loading",
		class: "text-gray-50"
	})) : createCommentVNode("", true), $setup.hasError ? (openBlock(), createElementBlock("div", {
		key: 1,
		class: "text-gray-500 flex gap-2",
		textContent: toDisplayString(_ctx.__("Something went wrong"))
	}, null, 8, _hoisted_3$7)) : createCommentVNode("", true)])) : createCommentVNode("", true), createBaseVNode("div", _hoisted_4$7, null, 512)]);
}
var PdfViewer_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$10, [["render", _sfc_render$9], ["__file", "PdfViewer.vue"]]);
// resources/js/composables/checkerboard.js
var PREFERENCE_KEYS = {
	listing: "assets.browser_checkerboard_mode",
	editor: "assets.editor_checkerboard_mode"
};
var DEFAULT_MODE = "transparent";
var CHECKERBOARD_MODES = [
	"light",
	"dark",
	"transparent"
];
var stateByContext = {};
function normalizeMode(raw) {
	return CHECKERBOARD_MODES.includes(raw) ? raw : DEFAULT_MODE;
}
/**
* @param {'listing'|'editor'} [context='listing'] - 'listing' for assets fieldtype and browser grid, 'editor' for asset editor
*/
function useCheckerboard(context = "listing") {
	if (stateByContext[context]) return stateByContext[context];
	const preferenceKey = PREFERENCE_KEYS[context];
	const mode = ref(normalizeMode(preferences.get(preferenceKey, DEFAULT_MODE)));
	watch(mode, (value) => preferences.set(preferenceKey, value === "transparent" ? null : value));
	const nextMode = computed(() => {
		const i = CHECKERBOARD_MODES.indexOf(mode.value);
		return CHECKERBOARD_MODES[(i >= 0 ? i + 1 : CHECKERBOARD_MODES.length) % CHECKERBOARD_MODES.length];
	});
	const enabled = computed(() => mode.value !== "transparent");
	const icon = computed(() => {
		if (mode.value === "light") return "sun";
		if (mode.value === "dark") return "moon";
		return "eye-slash";
	});
	function cycle() {
		mode.value = nextMode.value;
	}
	stateByContext[context] = {
		mode,
		enabled,
		icon,
		cycle
	};
	return stateByContext[context];
}
// resources/js/components/assets/Editor/Editor.vue
var _sfc_main$9 = {
	emits: [
		"previous",
		"next",
		"saved",
		"closed",
		"created",
		"action-started",
		"action-completed"
	],
	components: {
		Button: Button_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default$1,
		ItemActions: ItemActions_default,
		FocalPointEditor: FocalPointEditor_default,
		CropEditor: CropEditor_default,
		PdfViewer: PdfViewer_default,
		PublishContainer: Container_default,
		PublishTabs: Tabs_default,
		Icon: Icon_default,
		Stack: Stack_default
	},
	props: {
		id: { required: true },
		showToolbar: {
			type: Boolean,
			default: true
		},
		showNavigation: {
			type: Boolean,
			default: true
		},
		allowDeleting: {
			type: Boolean,
			default() {
				return true;
			}
		},
		redirectAfterCrop: {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			loading: true,
			saving: false,
			asset: null,
			publishContainer: "asset",
			values: {},
			extraValues: {},
			meta: {},
			fields: null,
			fieldset: null,
			showFocalPointEditor: false,
			showCropEditor: false,
			error: null,
			errors: {},
			actions: [],
			closingWithChanges: false
		};
	},
	computed: {
		readOnly() {
			return !this.asset.isEditable;
		},
		saveAndCloseTooltip() {
			return __("Save & Close (:shortcut)", { shortcut: shortcutLabel("mod+shift+s") });
		},
		isImage() {
			if (!this.asset) return false;
			return this.asset.isImage;
		},
		isCroppable() {
			return this.isImage && this.asset.extension !== "gif";
		},
		canCrop() {
			return this.isCroppable && this.asset.canCrop;
		},
		hasErrors: function() {
			return this.error || Object.keys(this.errors).length;
		},
		canUseGoogleDocsViewer() {
			return Statamic.$config.get("googleDocsViewer");
		},
		isFocalPointEditorEnabled() {
			return Statamic.$config.get("focalPointEditorEnabled");
		}
	},
	setup() {
		const checkerboard = useCheckerboard("editor");
		return {
			checkerboardMode: checkerboard.mode,
			checkerboardIcon: checkerboard.icon,
			showCheckerboard: checkerboard.enabled,
			cycleCheckerboard: checkerboard.cycle
		};
	},
	mounted() {
		this.load();
		window.addEventListener("keydown", this.keydown);
		this.saveKeyBinding = this.$keys.bindGlobal(["mod+s"], (e) => {
			e.preventDefault();
			if (this.readOnly || this.loading || this.saving) return;
			this.save().catch(() => {});
		});
		this.saveAndCloseKeyBinding = this.$keys.bindGlobal(["mod+shift+s"], (e) => {
			e.preventDefault();
			if (this.readOnly || this.loading || this.saving) return;
			this.saveAndClose();
		});
	},
	beforeUnmount() {
		window.removeEventListener("keydown", this.keydown);
		this.saveKeyBinding.destroy();
		this.saveAndCloseKeyBinding.destroy();
	},
	events: { "close-child-editor": function() {
		this.closeFocalPointEditor();
		this.closeCropEditor();
		this.closeImageEditor();
		this.closeRenamer();
	} },
	methods: {
		/**
		* Load the asset data
		*
		* This component is given an asset ID.
		* It needs to get the corresponding data from the server.
		*/
		load() {
			this.loading = true;
			const url = cp_url(`assets/${utf8btoa(this.id)}`);
			return this.$axios.get(url).then((response) => {
				const data = response.data.data;
				this.asset = data;
				this.values = Array.isArray(data.values) ? {} : data.values;
				this.meta = data.meta;
				this.actionUrl = data.actionUrl;
				this.actions = data.actions;
				this.fieldset = data.blueprint;
				let fields = this.fieldset.tabs;
				fields = fields.map((tab) => tab.sections);
				fields = flatten(fields);
				fields = fields.map((section) => section.fields);
				fields = flatten(fields);
				this.fields = fields;
				this.extraValues = pick(this.asset, [
					"filename",
					"basename",
					"extension",
					"path",
					"mimeType",
					"width",
					"height",
					"duration"
				]);
				this.loading = false;
			});
		},
		keydown(event) {
			if (!event.metaKey && !event.ctrlKey) return;
			if (this.isEditingText(event.target)) return;
			if (event.key === "ArrowLeft") this.navigateToPreviousAsset();
			if (event.key === "ArrowRight") this.navigateToNextAsset();
		},
		isEditingText(element) {
			if (!element) return false;
			const tag = element.tagName;
			return tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT" || element.isContentEditable;
		},
		navigateToPreviousAsset() {
			if (this.saving) return;
			if (!this.$dirty.has(this.publishContainer)) return this.$emit("previous");
			this.save().then(() => this.$emit("previous")).catch(() => {});
		},
		navigateToNextAsset() {
			if (this.saving) return;
			if (!this.$dirty.has(this.publishContainer)) return this.$emit("next");
			this.save().then(() => this.$emit("next")).catch(() => {});
		},
		openFocalPointEditor() {
			this.showFocalPointEditor = true;
		},
		closeFocalPointEditor() {
			this.showFocalPointEditor = false;
		},
		selectFocalPoint(point) {
			point = point === "50-50-1" ? null : point;
			this.values["focus"] = point;
			this.$dirty.add(this.publishContainer);
		},
		saveFocalPoint({ close = false } = {}) {
			this.$nextTick(() => {
				this.save().then(() => close && this.$emit("closed")).catch(() => {});
			});
		},
		openCropEditor() {
			this.showCropEditor = true;
		},
		closeCropEditor() {
			this.showCropEditor = false;
		},
		async handleCropReplaced() {
			const originalPreview = this.asset?.preview;
			const originalThumbnail = this.asset?.thumbnail;
			await this.load();
			Statamic.$callbacks.call("bustAndReloadImageCaches", [originalPreview, originalThumbnail]);
		},
		handleCropCreated(newAssetId) {
			if (!this.redirectAfterCrop) {
				this.$emit("created", newAssetId);
				return;
			}
			const [containerHandle, assetPath] = newAssetId.split("::");
			const editUrl = cp_url(`assets/browse/${containerHandle}/${assetPath}/edit`);
			router.get(editUrl);
		},
		updateValues(values) {
			let updated = {
				...event,
				focus: values.focus
			};
			if (JSON.stringify(values) === JSON.stringify(updated)) return;
			values = updated;
		},
		save() {
			this.saving = true;
			const url = cp_url(`assets/${utf8btoa(this.id)}`);
			return this.$axios.patch(url, this.$refs.container.visibleValues).then((response) => {
				this.$emit("saved", response.data.asset);
				this.$toast.success(__("Saved"));
				this.saving = false;
				this.clearErrors();
				this.$nextTick(() => this.$refs.container.clearDirtyState());
				Statamic.$events.$emit("asset.saved", this.asset);
			}).catch((e) => {
				this.saving = false;
				if (e.response && e.response.status === 422) {
					const { message, errors, error } = e.response.data;
					this.error = message;
					this.errors = errors;
					this.$toast.error(error);
				} else if (e.response) this.$toast.error(e.response.data.message);
				else this.$toast.error(__("Something went wrong"));
				throw e;
			});
		},
		saveAndClose() {
			this.save().then(() => this.$emit("closed")).catch(() => {});
		},
		clearErrors() {
			this.error = null;
			this.errors = {};
		},
		shouldClose() {
			if (this.$dirty.has(this.publishContainer)) {
				this.closingWithChanges = true;
				return false;
			}
			return true;
		},
		confirmClose(close) {
			if (this.shouldClose()) this.$refs.stack.close();
		},
		confirmCloseWithChanges() {
			this.closingWithChanges = false;
			this.$refs.container.clearDirtyState();
			this.$emit("closed");
		},
		open() {
			window.open(this.asset.url, "_blank");
		},
		download() {
			window.open(this.asset.downloadUrl);
		},
		canRunAction(handle) {
			return this.actions.find((action) => action.handle == handle);
		},
		runAction(actions, handle) {
			actions.find((action) => action.handle === handle).run();
		},
		actionStarted() {
			this.$emit("action-started");
		},
		actionCompleted(successful, response) {
			this.$emit("action-completed", successful, response);
			if (successful) this.$emit("closed");
		},
		filterForActionsMenu(actions) {
			const buttonActions = [
				"rename_asset",
				"move_asset",
				"replace_asset",
				"reupload_asset",
				"download_asset",
				"delete",
				"copy_asset_url"
			];
			return actions.filter((action) => !buttonActions.includes(action.handle));
		}
	}
};
var _hoisted_1$8 = {
	key: 0,
	class: "loading"
};
var _hoisted_2$6 = {
	id: "asset-editor-header",
	class: "relative flex w-full justify-between px-2"
};
var _hoisted_3$6 = ["aria-label"];
var _hoisted_4$6 = { class: "text-sm group-hover:text-ui-accent-text/80 dark:text-gray-400 dark:group-hover:text-gray-200" };
var _hoisted_5$5 = { class: "flex flex-1 grow flex-col overflow-auto md:flex-row md:justify-between" };
var _hoisted_6$5 = { class: "editor-preview md:min-h-auto flex min-h-[45vh] w-full flex-1 flex-col justify-between bg-gray-800 shadow-[inset_0px_4px_3px_0px_black] dark:bg-gray-900 md:w-1/2 md:flex-auto md:grow lg:w-2/3 md:ltr:rounded-se-xl" };
var _hoisted_7$4 = {
	key: 0,
	class: "@container/toolbar dark flex flex-wrap items-center justify-center gap-2 px-2 py-4"
};
var _hoisted_8$3 = {
	key: 1,
	class: "flex flex-1 flex-col justify-center items-center p-8 h-full min-h-0"
};
var _hoisted_9$3 = ["src"];
var _hoisted_10$3 = {
	key: 1,
	class: "flex h-full w-full flex-col shadow-ui-xl dark:bg-gray-800"
};
var _hoisted_11$3 = { class: "grid grid-cols-3 gap-1" };
var _hoisted_12$2 = ["src"];
var _hoisted_13$1 = ["src"];
var _hoisted_14 = ["src"];
var _hoisted_15 = ["src"];
var _hoisted_16 = {
	key: 2,
	class: "w-full shadow-none"
};
var _hoisted_17 = ["src"];
var _hoisted_18 = [
	"src",
	"width",
	"height"
];
var _hoisted_19 = ["src"];
var _hoisted_20 = {
	key: 3,
	class: "h-full"
};
var _hoisted_21 = ["src"];
var _hoisted_22 = { class: "h-1/2 w-full overflow-scroll sm:p-4 md:h-full md:w-1/3 md:grow md:pt-px" };
var _hoisted_23 = { class: "flex w-full items-center justify-end rounded-b border-t dark:border-gray-700 bg-gray-100 dark:bg-gray-900 px-4 py-3" };
var _hoisted_24 = { class: "hidden h-full flex-1 gap-2 sm:gap-3 py-1 sm:flex" };
var _hoisted_25 = ["datetime", "textContent"];
var _hoisted_26 = { class: "flex items-center space-x-3 rtl:space-x-reverse" };
function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Icon = resolveComponent("Icon");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_pdf_viewer = resolveComponent("pdf-viewer");
	const _component_PublishTabs = resolveComponent("PublishTabs");
	const _component_PublishContainer = resolveComponent("PublishContainer");
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_focal_point_editor = resolveComponent("focal-point-editor");
	const _component_crop_editor = resolveComponent("crop-editor");
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	const _component_Stack = resolveComponent("Stack");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock(_component_Stack, {
		size: "full",
		open: "",
		inset: "",
		ref: "stack",
		"before-close": $options.shouldClose,
		"onUpdate:open": _cache[5] || (_cache[5] = ($event) => _ctx.$emit("closed")),
		"show-close-button": false
	}, {
		default: withCtx(() => [createBaseVNode("div", { class: normalizeClass(["asset-editor relative flex h-full flex-col rounded-sm bg-gray-100 dark:bg-gray-850", $options.isImage ? "is-image" : "is-file"]) }, [
			$data.loading ? (openBlock(), createElementBlock("div", _hoisted_1$8, [createVNode(_component_Icon, { name: "loading" })])) : createCommentVNode("", true),
			!$data.loading ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [
				createBaseVNode("header", _hoisted_2$6, [withDirectives((openBlock(), createElementBlock("button", {
					class: "group flex items-center gap-2 sm:gap-3 p-4",
					onClick: _cache[0] || (_cache[0] = (...args) => $options.open && $options.open(...args)),
					"aria-label": _ctx.__("Open in a new window")
				}, [createVNode(_component_ui_icon, {
					name: "folder-photos",
					class: "size-5 group-hover:text-ui-accent-text/80"
				}), createBaseVNode("span", _hoisted_4$6, toDisplayString($data.asset.path), 1)], 8, _hoisted_3$6)), [[
					_directive_tooltip,
					_ctx.__("Open in a new window"),
					void 0,
					{ right: true }
				]]), createVNode(_component_ui_button, {
					variant: "ghost",
					icon: "x",
					class: "absolute top-1.5 end-1.5",
					round: "",
					onClick: _cache[1] || (_cache[1] = ($event) => $options.confirmClose()),
					"aria-label": _ctx.__("Close Editor")
				}, null, 8, ["aria-label"])]),
				createBaseVNode("div", _hoisted_5$5, [createBaseVNode("div", _hoisted_6$5, [$props.showToolbar ? (openBlock(), createElementBlock("div", _hoisted_7$4, [createVNode(_component_ItemActions, {
					item: $props.id,
					url: _ctx.actionUrl,
					actions: $data.actions,
					onStarted: $options.actionStarted,
					onCompleted: $options.actionCompleted
				}, {
					default: withCtx(({ actions }) => [
						$data.asset.can_be_transparent ? (openBlock(), createBlock(_component_ui_button, {
							key: 0,
							inset: "",
							size: "sm",
							variant: "ghost",
							icon: $setup.checkerboardIcon,
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Transparency"),
							onClick: $setup.cycleCheckerboard
						}, null, 8, [
							"icon",
							"text",
							"onClick"
						])) : createCommentVNode("", true),
						$data.asset.isEditable && $options.isImage && $options.isFocalPointEditorEnabled ? (openBlock(), createBlock(_component_ui_button, {
							key: 1,
							inset: "",
							size: "sm",
							onClick: withModifiers($options.openFocalPointEditor, ["prevent"]),
							icon: "focus",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Focal Point")
						}, null, 8, ["onClick", "text"])) : createCommentVNode("", true),
						$options.canCrop ? (openBlock(), createBlock(_component_ui_button, {
							key: 2,
							inset: "",
							size: "sm",
							onClick: withModifiers($options.openCropEditor, ["prevent"]),
							icon: "crop",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Crop")
						}, null, 8, ["onClick", "text"])) : createCommentVNode("", true),
						$options.canRunAction("rename_asset") ? (openBlock(), createBlock(_component_ui_button, {
							key: 3,
							inset: "",
							size: "sm",
							onClick: withModifiers(($event) => $options.runAction(actions, "rename_asset"), ["prevent"]),
							icon: "rename",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Rename")
						}, null, 8, ["onClick", "text"])) : createCommentVNode("", true),
						$options.canRunAction("move_asset") ? (openBlock(), createBlock(_component_ui_button, {
							key: 4,
							inset: "",
							size: "sm",
							onClick: withModifiers(($event) => $options.runAction(actions, "move_asset"), ["prevent"]),
							icon: "move-folder",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Move to Folder")
						}, null, 8, ["onClick", "text"])) : createCommentVNode("", true),
						$options.canRunAction("replace_asset") ? (openBlock(), createBlock(_component_ui_button, {
							key: 5,
							inset: "",
							size: "sm",
							onClick: withModifiers(($event) => $options.runAction(actions, "replace_asset"), ["prevent"]),
							icon: "replace",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Replace")
						}, null, 8, ["onClick", "text"])) : createCommentVNode("", true),
						$options.canRunAction("reupload_asset") ? (openBlock(), createBlock(_component_ui_button, {
							key: 6,
							inset: "",
							size: "sm",
							onClick: withModifiers(($event) => $options.runAction(actions, "reupload_asset"), ["prevent"]),
							icon: "upload-cloud",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Reupload")
						}, null, 8, ["onClick", "text"])) : createCommentVNode("", true),
						createVNode(_component_ui_button, {
							inset: "",
							size: "sm",
							onClick: $options.download,
							icon: "download",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Download")
						}, null, 8, ["onClick", "text"]),
						$props.allowDeleting && $options.canRunAction("delete") ? (openBlock(), createBlock(_component_ui_button, {
							key: 7,
							inset: "",
							size: "sm",
							onClick: ($event) => $options.runAction(actions, "delete"),
							icon: "trash",
							variant: "ghost",
							class: "[&_svg]:!opacity-45",
							text: _ctx.__("Delete")
						}, null, 8, ["onClick", "text"])) : createCommentVNode("", true),
						$options.filterForActionsMenu(actions).length ? (openBlock(), createBlock(_component_Dropdown, {
							key: 8,
							class: "me-4"
						}, {
							default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
								default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($options.filterForActionsMenu(actions), (action) => {
									return openBlock(), createBlock(_component_DropdownItem, {
										key: action.handle,
										text: _ctx.__(action.title),
										icon: action.icon,
										variant: action.dangerous ? "destructive" : "default",
										onClick: action.run
									}, null, 8, [
										"text",
										"icon",
										"variant",
										"onClick"
									]);
								}), 128))]),
								_: 2
							}, 1024)]),
							_: 2
						}, 1024)) : createCommentVNode("", true)
					]),
					_: 1
				}, 8, [
					"item",
					"url",
					"actions",
					"onStarted",
					"onCompleted"
				])])) : createCommentVNode("", true), $data.asset.isImage || $data.asset.isSvg || $data.asset.isAudio || $data.asset.isVideo || $data.asset.preview ? (openBlock(), createElementBlock("div", _hoisted_8$3, [$data.asset.isImage ? (openBlock(), createElementBlock("div", {
					key: 0,
					class: normalizeClass(["max-w-full max-h-full", { [`bg-checkerboard bg-checkerboard-${$setup.checkerboardMode} rounded-md`]: $data.asset.can_be_transparent && $setup.showCheckerboard }])
				}, [createBaseVNode("img", {
					src: $data.asset.preview,
					class: "relative asset-thumb shadow-ui-xl max-w-full max-h-full object-contain"
				}, null, 8, _hoisted_9$3)], 2)) : $data.asset.isSvg ? (openBlock(), createElementBlock("div", _hoisted_10$3, [createBaseVNode("div", _hoisted_11$3, [
					createBaseVNode("div", { class: normalizeClass(["flex items-center justify-center p-3 aspect-square", { [`bg-checkerboard bg-checkerboard-${$setup.checkerboardMode}`]: $setup.showCheckerboard }]) }, [createBaseVNode("img", {
						src: $data.asset.url,
						class: "asset-thumb relative z-10 w-4"
					}, null, 8, _hoisted_12$2)], 2),
					createBaseVNode("div", { class: normalizeClass(["flex items-center justify-center p-3 aspect-square", { [`bg-checkerboard bg-checkerboard-${$setup.checkerboardMode}`]: $setup.showCheckerboard }]) }, [createBaseVNode("img", {
						src: $data.asset.url,
						class: "asset-thumb relative z-10 w-12"
					}, null, 8, _hoisted_13$1)], 2),
					createBaseVNode("div", { class: normalizeClass(["flex items-center justify-center p-3 aspect-square", { [`bg-checkerboard bg-checkerboard-${$setup.checkerboardMode}`]: $setup.showCheckerboard }]) }, [createBaseVNode("img", {
						src: $data.asset.url,
						class: "asset-thumb relative z-10 w-24"
					}, null, 8, _hoisted_14)], 2)
				]), createBaseVNode("div", { class: normalizeClass(["h-full min-h-0 mt-1 flex items-center justify-center p-3 aspect-square", { [`bg-checkerboard bg-checkerboard-${$setup.checkerboardMode}`]: $setup.showCheckerboard }]) }, [createBaseVNode("img", {
					src: $data.asset.url,
					class: "asset-thumb relative z-10 max-h-full w-2/3 max-w-full"
				}, null, 8, _hoisted_15)], 2)])) : $data.asset.isAudio ? (openBlock(), createElementBlock("div", _hoisted_16, [createBaseVNode("audio", {
					src: $data.asset.url,
					class: "w-full",
					controls: "",
					preload: "auto"
				}, null, 8, _hoisted_17)])) : $data.asset.isVideo ? (openBlock(), createElementBlock("video", {
					key: 3,
					src: $data.asset.url,
					width: $data.asset.width,
					height: $data.asset.height,
					class: "max-w-full max-h-full object-contain",
					controls: ""
				}, null, 8, _hoisted_18)) : $data.asset.preview ? (openBlock(), createElementBlock("img", {
					key: 4,
					src: $data.asset.preview,
					class: "asset-thumb shadow-ui-xl max-w-full max-h-full object-contain"
				}, null, 8, _hoisted_19)) : createCommentVNode("", true)])) : $data.asset.isPdf ? (openBlock(), createBlock(_component_pdf_viewer, {
					key: 2,
					src: $data.asset.pdfUrl
				}, null, 8, ["src"])) : $data.asset.isPreviewable && $options.canUseGoogleDocsViewer ? (openBlock(), createElementBlock("div", _hoisted_20, [createBaseVNode("iframe", {
					class: "h-full w-full",
					frameborder: "0",
					src: "https://docs.google.com/gview?url=" + $data.asset.permalink + "&embedded=true"
				}, null, 8, _hoisted_21)])) : createCommentVNode("", true)]), $data.fields ? (openBlock(), createBlock(_component_PublishContainer, {
					key: 0,
					ref: "container",
					"read-only": $options.readOnly,
					name: $data.publishContainer,
					reference: $props.id,
					blueprint: $data.fieldset,
					"model-value": $data.values,
					"extra-values": $data.extraValues,
					meta: $data.meta,
					errors: $data.errors,
					"onUpdate:modelValue": $options.updateValues
				}, {
					default: withCtx(() => [createBaseVNode("div", _hoisted_22, [createVNode(_component_PublishTabs)])]),
					_: 1
				}, 8, [
					"read-only",
					"name",
					"reference",
					"blueprint",
					"model-value",
					"extra-values",
					"meta",
					"errors",
					"onUpdate:modelValue"
				])) : createCommentVNode("", true)]),
				createBaseVNode("div", _hoisted_23, [createBaseVNode("div", _hoisted_24, [
					$data.asset.width && $data.asset.height ? (openBlock(), createBlock(_component_ui_badge, {
						key: 0,
						pill: "",
						icon: "assets",
						text: _ctx.__("messages.width_x_height", {
							width: Math.round($data.asset.width),
							height: Math.round($data.asset.height)
						})
					}, null, 8, ["text"])) : createCommentVNode("", true),
					createVNode(_component_ui_badge, {
						pill: "",
						icon: "memory",
						text: $data.asset.size
					}, null, 8, ["text"]),
					createVNode(_component_ui_badge, {
						pill: "",
						icon: "fingerprint"
					}, {
						default: withCtx(() => [withDirectives(createBaseVNode("time", {
							class: "st-text-trim-optical-cap",
							datetime: $data.asset.lastModified,
							textContent: toDisplayString($data.asset.lastModifiedRelative)
						}, null, 8, _hoisted_25), [[_directive_tooltip, _ctx.$date.format($data.asset.lastModified)]])]),
						_: 1
					})
				]), createBaseVNode("div", _hoisted_26, [
					$props.showNavigation ? withDirectives((openBlock(), createBlock(_component_ui_button, {
						key: 0,
						icon: "chevron-left",
						onClick: $options.navigateToPreviousAsset
					}, null, 8, ["onClick"])), [[_directive_tooltip, _ctx.__("Previous Asset")]]) : createCommentVNode("", true),
					$props.showNavigation ? withDirectives((openBlock(), createBlock(_component_ui_button, {
						key: 1,
						icon: "chevron-right",
						onClick: $options.navigateToNextAsset
					}, null, 8, ["onClick"])), [[_directive_tooltip, _ctx.__("Next Asset")]]) : createCommentVNode("", true),
					!$options.readOnly ? withDirectives((openBlock(), createBlock(_component_ui_button, {
						key: 2,
						variant: "primary",
						icon: $data.saving ? null : "save",
						onClick: $options.saveAndClose,
						loading: $data.saving,
						text: _ctx.__("Save")
					}, null, 8, [
						"icon",
						"onClick",
						"loading",
						"text"
					])), [[_directive_tooltip, $options.saveAndCloseTooltip]]) : createCommentVNode("", true)
				])])
			], 64)) : createCommentVNode("", true),
			$data.showFocalPointEditor && $options.isFocalPointEditorEnabled ? (openBlock(), createBlock(_component_focal_point_editor, {
				key: 2,
				data: $data.values.focus,
				image: $data.asset.preview,
				onSelected: $options.selectFocalPoint,
				onClosed: $options.closeFocalPointEditor,
				onSave: $options.saveFocalPoint
			}, null, 8, [
				"data",
				"image",
				"onSelected",
				"onClosed",
				"onSave"
			])) : createCommentVNode("", true),
			$options.isCroppable ? (openBlock(), createBlock(_component_crop_editor, {
				key: 3,
				asset: $data.asset,
				"can-replace": $data.asset.canReuploadCrop,
				open: $data.showCropEditor,
				"onUpdate:open": _cache[2] || (_cache[2] = ($event) => $data.showCropEditor = $event),
				onReplaced: $options.handleCropReplaced,
				onCreated: $options.handleCropCreated
			}, null, 8, [
				"asset",
				"can-replace",
				"open",
				"onReplaced",
				"onCreated"
			])) : createCommentVNode("", true),
			createVNode(_component_confirmation_modal, {
				open: $data.closingWithChanges,
				"onUpdate:open": _cache[3] || (_cache[3] = ($event) => $data.closingWithChanges = $event),
				title: _ctx.__("Unsaved Changes"),
				"body-text": _ctx.__("Are you sure? Unsaved changes will be lost."),
				"button-text": _ctx.__("Discard Changes"),
				danger: true,
				onConfirm: $options.confirmCloseWithChanges,
				onCancel: _cache[4] || (_cache[4] = ($event) => $data.closingWithChanges = false)
			}, null, 8, [
				"open",
				"title",
				"body-text",
				"button-text",
				"onConfirm"
			])
		], 2)]),
		_: 1
	}, 8, ["before-close"]);
}
var Editor_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$9, [["render", _sfc_render$8], ["__file", "Editor.vue"]]);
// resources/js/components/assets/Browser/dragBadge.js
/**
* Show a count badge next to the cursor while several assets are being dragged.
*
* Browsers render drag images semi-transparent, so the native drag image is swapped for
* an empty one and the badge is a regular element that follows the cursor instead.
*/
function showMultipleAssetDragBadge(event, count) {
	hideNativeDragImage(event);
	const badge = document.createElement("div");
	badge.className = "pointer-events-none fixed top-0 left-0 z-[9999] flex items-center justify-center h-7 min-w-7 rounded-full bg-blue-500 px-2 text-xs font-semibold text-white";
	badge.textContent = count;
	document.body.appendChild(badge);
	const follow = ({ clientX, clientY }) => {
		badge.style.transform = `translate(${clientX + 18}px, ${clientY + 20}px)`;
	};
	const cleanUp = () => {
		badge.remove();
		document.removeEventListener("dragover", follow, true);
		document.removeEventListener("dragend", cleanUp, true);
		document.removeEventListener("drop", cleanUp, true);
	};
	follow(event);
	document.addEventListener("dragover", follow, true);
	document.addEventListener("dragend", cleanUp, true);
	document.addEventListener("drop", cleanUp, true);
}
function hideNativeDragImage(event) {
	const empty = document.createElement("div");
	empty.className = "fixed -top-[1000px] size-px";
	document.body.appendChild(empty);
	event.dataTransfer.setDragImage(empty, 0, 0);
	setTimeout(() => empty.remove());
}
// resources/js/components/assets/Browser/AssetBrowserMixin.js
var AssetBrowserMixin_default = {
	props: {
		actionUrl: String,
		containerIsEmpty: Boolean,
		folder: Object,
		folderActionUrl: String,
		folders: Array,
		path: String,
		restrictFolderNavigation: Boolean,
		creatingFolder: Boolean,
		creatingFolderError: Boolean,
		selectedAssets: {
			type: Array,
			default: () => []
		},
		moving: {
			type: Array,
			default: () => []
		},
		moved: {
			type: Array,
			default: () => []
		},
		removed: {
			type: Array,
			default: () => []
		},
		drops: {
			type: Array,
			default: () => []
		}
	},
	data() {
		return {
			newFolderName: null,
			draggingAsset: null,
			draggingFolder: null,
			dragOverFolder: null
		};
	},
	computed: {
		visibleFolders() {
			return this.folders.filter((folder) => !this.isRemoved(folder.path));
		},
		draggedAssetIds() {
			if (!this.draggingAsset) return [];
			if (!this.selectedAssets.includes(this.draggingAsset)) return [this.draggingAsset];
			return this.assets.filter((asset) => asset.id === this.draggingAsset || this.selectedAssets.includes(asset.id) && this.canMoveAsset(asset) && !this.isBeingMoved(asset.id)).map((asset) => asset.id);
		},
		draggingMovable() {
			if (this.draggingAsset) {
				const asset = this.assets.find((asset) => asset.id === this.draggingAsset);
				return !!asset && this.canMoveAsset(asset);
			}
			if (this.draggingFolder) {
				const folder = this.folders.find((folder) => folder.path === this.draggingFolder);
				return !!folder && this.canMoveFolder(folder);
			}
			return false;
		}
	},
	watch: {
		draggingAsset() {
			this.$emit("prevent-dragging", this.draggingAsset !== null);
		},
		draggingFolder() {
			this.$emit("prevent-dragging", this.draggingFolder !== null);
		},
		draggingMovable(movable) {
			this.$emit("dragging-movable", movable);
		},
		newFolderName() {
			if (this.creatingFolderError) this.$emit("update:creatingFolderError", null);
		}
	},
	methods: {
		actionCompleted(successful = null, response = {}) {
			successful ? this.actionSuccess(response) : this.actionFailed(response);
			this.$emit("action-completed");
		},
		actionSuccess(response) {
			if (response.message !== false) Statamic.$toast.success(response.message || __("Action completed"));
		},
		actionFailed(response) {
			Statamic.$toast.error(response.message || __("Action failed"));
		},
		actionStarted() {
			this.$emit("action-started");
		},
		edit(id) {
			this.$emit("edit", id);
		},
		folderActions(folder) {
			return folder.actions || this.folder.actions || [];
		},
		selectFolder(path) {
			this.$emit("select-folder", path);
		},
		focusNewFolderInput() {
			this.$refs.newFolderInput?.edit();
		},
		clearNewFolderName() {
			this.newFolderName = null;
		},
		canMoveAsset(asset) {
			return asset.actions.some((action) => action.handle === "move_asset");
		},
		canMoveFolder(folder) {
			return folder.actions.some((action) => action.handle === "move_asset_folder");
		},
		assetDragStarted(event, asset) {
			this.draggingAsset = asset.id;
			if (this.draggedAssetIds.length > 1) showMultipleAssetDragBadge(event, this.draggedAssetIds.length);
		},
		canDropOn(folder) {
			return this.draggingMovable && folder.path !== this.draggingFolder;
		},
		folderDragOver(event, folder) {
			if (!this.canDropOn(folder)) return;
			event.preventDefault();
			this.dragOverFolder = folder.path;
		},
		folderDragLeave(event) {
			if (event.currentTarget.contains(event.relatedTarget)) return;
			this.dragOverFolder = null;
		},
		handleFolderDrop(destinationFolder) {
			const destination = destinationFolder.path;
			if (this.draggingAsset && this.draggingMovable) {
				let action = this.assets.find((asset) => asset.id === this.draggingAsset).actions.find((action) => action.handle === "move_asset");
				let ids = this.assets.filter((asset) => this.draggedAssetIds.includes(asset.id) && parentPath(asset.path) !== destination).map((asset) => asset.id);
				if (ids.length) this.$emit("move-items", {
					url: this.actionUrl,
					action,
					items: ids,
					destination
				});
			}
			if (this.draggingFolder && this.draggingMovable) {
				let folder = this.folders.find((folder) => folder.path === this.draggingFolder);
				let action = folder.actions.find((action) => action.handle === "move_asset_folder");
				if (folder.path !== destination && parentPath(folder.path) !== destination) this.$emit("move-items", {
					url: this.folderActionUrl,
					action,
					items: [folder.path],
					destination
				});
			}
			this.draggingAsset = null;
			this.draggingFolder = null;
			this.dragOverFolder = null;
		},
		isDragging(id) {
			return this.draggedAssetIds.includes(id);
		},
		isMoving(item) {
			return this.moving.includes(item);
		},
		isMoved(item) {
			return this.moved.includes(item);
		},
		isRemoved(item) {
			return this.removed.includes(item);
		},
		isBeingMoved(item) {
			return this.isMoving(item) || this.isMoved(item) || this.isRemoved(item);
		},
		moveStateClasses(item) {
			if (this.isMoved(item)) return "pointer-events-none opacity-0 transition-opacity duration-300";
			if (this.isMoving(item)) return "pointer-events-none opacity-50 transition-opacity duration-150";
			return null;
		},
		dropFor(folder) {
			return this.drops.find((drop) => drop.path === folder.path) ?? null;
		}
	}
};
function parentPath(path) {
	return path.includes("/") ? path.slice(0, path.lastIndexOf("/")) : "/";
}
// resources/js/components/assets/Browser/AnimatedFolder.vue
var _sfc_main$8 = {
	props: { 
	/**
	* The drop to animate into the folder, as `{ id, fileKey, files: [{ thumbnail }], closing }`.
	* The folder opens and stays open until `closing` is set. Up to three files are shown.
	* Changing the `id` replays the whole animation, and changing `fileKey` drops the files in again.
	*/
drop: {
		type: Object,
		default: null
	} },
	computed: { sheets() {
		const fan = [
			"",
			"translate(-6 2) rotate(-8 40 39)",
			"translate(6 1) rotate(7 40 39)"
		];
		return (this.drop?.files?.length ? this.drop.files.slice(0, 3) : [{}]).map((file, index) => ({
			index,
			thumbnail: file.thumbnail,
			transform: fan[index]
		})).reverse();
	} },
	data() {
		return { folderPath: "M73.6842 8.8H33.6842C33.1152 8.79854 32.5701 8.56117 32.1684 8.14L26.5263 2.2C25.9489 1.53249 25.2486 0.993597 24.4672 0.615578C23.6859 0.237558 22.8396 0.0281878 21.9789 0H7.53684C5.53795 0 3.62092 0.82979 2.20749 2.30683C0.794058 3.78386 0 5.78715 0 7.876V58.124C0 60.2128 0.794058 62.2161 2.20749 63.6932C3.62092 65.1702 5.53795 66 7.53684 66H72.4632C74.4621 66 76.3791 65.1702 77.7925 63.6932C79.2059 62.2161 80 60.2128 80 58.124V15.4C80 13.6496 79.3346 11.9708 78.1501 10.7331C76.9657 9.49535 75.3593 8.8 73.6842 8.8Z" };
	}
};
var _hoisted_1$7 = {
	viewBox: "0 0 80 66",
	fill: "none",
	xmlns: "http://www.w3.org/2000/svg",
	class: "overflow-visible"
};
var _hoisted_2$5 = ["d"];
var _hoisted_3$5 = ["transform"];
var _hoisted_4$5 = ["href"];
function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("svg", _hoisted_1$7, [
		createBaseVNode("path", {
			d: $data.folderPath,
			fill: "currentColor"
		}, null, 8, _hoisted_2$5),
		$props.drop ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [_cache[6] || (_cache[6] = createBaseVNode("rect", {
			x: "0",
			y: "15.4",
			width: "80",
			height: "50.6",
			rx: "7.5",
			class: "fill-black/25"
		}, null, -1)), (openBlock(), createElementBlock("g", {
			key: $props.drop.fileKey,
			class: "origin-center [transform-box:fill-box] motion-safe:animate-folder-file"
		}, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.sheets, (sheet) => {
			return openBlock(), createElementBlock("g", {
				key: sheet.index,
				transform: sheet.transform
			}, [_cache[5] || (_cache[5] = createBaseVNode("rect", {
				x: "25",
				y: "20",
				width: "30",
				height: "38",
				rx: "2.5",
				class: "fill-white stroke-black/10"
			}, null, -1)), sheet.thumbnail ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createBaseVNode("image", {
				href: sheet.thumbnail,
				x: "28",
				y: "23",
				width: "24",
				height: "24",
				preserveAspectRatio: "xMidYMid slice"
			}, null, 8, _hoisted_4$5), _cache[0] || (_cache[0] = createBaseVNode("rect", {
				x: "28",
				y: "50",
				width: "16",
				height: "2",
				rx: "1",
				class: "fill-gray-300"
			}, null, -1))], 64)) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [
				_cache[1] || (_cache[1] = createBaseVNode("rect", {
					x: "29",
					y: "25",
					width: "22",
					height: "2",
					rx: "1",
					class: "fill-gray-300"
				}, null, -1)),
				_cache[2] || (_cache[2] = createBaseVNode("rect", {
					x: "29",
					y: "30",
					width: "22",
					height: "2",
					rx: "1",
					class: "fill-gray-300"
				}, null, -1)),
				_cache[3] || (_cache[3] = createBaseVNode("rect", {
					x: "29",
					y: "35",
					width: "22",
					height: "2",
					rx: "1",
					class: "fill-gray-300"
				}, null, -1)),
				_cache[4] || (_cache[4] = createBaseVNode("rect", {
					x: "29",
					y: "40",
					width: "14",
					height: "2",
					rx: "1",
					class: "fill-gray-300"
				}, null, -1))
			], 64))], 8, _hoisted_3$5);
		}), 128))]))], 64)) : createCommentVNode("", true),
		(openBlock(), createElementBlock("rect", {
			key: $props.drop?.id ?? "flap",
			x: "0",
			y: "15.4",
			width: "80",
			height: "50.6",
			rx: "7.5",
			fill: "currentColor",
			class: normalizeClass(["origin-bottom [transform-box:fill-box]", {
				"motion-safe:animate-folder-open": $props.drop && !$props.drop.closing,
				"motion-safe:animate-folder-close": $props.drop?.closing
			}])
		}, null, 2))
	]);
}
var AnimatedFolder_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$8, [["render", _sfc_render$7], ["__file", "AnimatedFolder.vue"]]);
// resources/js/components/assets/Browser/Breadcrumbs.vue
var _sfc_main$7 = {
	props: {
		path: String,
		droppable: Boolean,
		/** Search results come from any folder, so the current folder becomes a valid drop target. */
		currentDroppable: Boolean,
		/** The paths of folders that are currently receiving a drop. */
		openPaths: {
			type: Array,
			default: () => []
		}
	},
	emits: ["navigated", "dropped"],
	data() {
		return { dropTarget: null };
	},
	computed: { pathParts() {
		let parts = ["/"];
		if (this.path === "/") return parts;
		return parts.concat(this.path.split("/"));
	} },
	watch: { droppable(droppable) {
		if (!droppable) this.dropTarget = null;
	} },
	methods: {
		pathFor(index) {
			return index === 0 ? "/" : this.pathParts.slice(1, index + 1).join("/");
		},
		iconFor(part, index) {
			if (this.openPaths.includes(this.pathFor(index))) return "folder-open";
			if (this.isHomeFolder(part)) return "home";
			return this.isLastFolder(index) ? "folder-open" : "folder";
		},
		selectFolder(index) {
			this.$emit("navigated", this.pathFor(index));
		},
		canDropOn(index) {
			return this.droppable && (this.currentDroppable || !this.isLastFolder(index));
		},
		dragOver(event, index) {
			if (!this.canDropOn(index)) return;
			event.preventDefault();
			this.dropTarget = index;
		},
		dragLeave(event) {
			if (event.currentTarget.contains(event.relatedTarget)) return;
			this.dropTarget = null;
		},
		drop(event, index) {
			if (!this.canDropOn(index)) return;
			event.preventDefault();
			this.dropTarget = null;
			this.$emit("dropped", this.pathFor(index));
		},
		isHomeFolder(part) {
			return part === "/";
		},
		isLastFolder(index) {
			return index === this.pathParts.length - 1;
		}
	}
};
var _hoisted_1$6 = { class: "flex flex-wrap gap-y-4" };
function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_ui_button = resolveComponent("ui-button");
	return openBlock(), createElementBlock("div", _hoisted_1$6, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.pathParts, (part, index) => {
		return openBlock(), createElementBlock("div", {
			key: index,
			class: "flex items-center"
		}, [index !== 0 ? (openBlock(), createBlock(_component_ui_icon, {
			key: 0,
			name: "chevron-right",
			class: "size-4 text-gray-500"
		})) : createCommentVNode("", true), createVNode(_component_ui_button, {
			variant: "ghost",
			icon: $options.iconFor(part, index),
			text: $options.isHomeFolder(part) ? _ctx.__("All") : part,
			onClick: ($event) => $options.selectFolder(index),
			onDragover: ($event) => $options.dragOver($event, index),
			onDragleave: _cache[0] || (_cache[0] = ($event) => $options.dragLeave($event)),
			onDrop: ($event) => $options.drop($event, index),
			class: normalizeClass(["gap-2 h-8!", { "ring-2 ring-blue-400": $data.dropTarget === index }])
		}, null, 8, [
			"icon",
			"text",
			"onClick",
			"onDragover",
			"onDrop",
			"class"
		])]);
	}), 128))]);
}
var Breadcrumbs_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$7, [["render", _sfc_render$6], ["__file", "Breadcrumbs.vue"]]);
// resources/svg/folder.svg
var _hoisted_1$5 = {
	xmlns: "http://www.w3.org/2000/svg",
	fill: "none",
	viewBox: "0 0 80 66"
};
function render(_ctx, _cache) {
	return openBlock(), createElementBlock("svg", _hoisted_1$5, [..._cache[0] || (_cache[0] = [createBaseVNode("path", {
		fill: "currentColor",
		d: "M73.684 8.8h-40a2.1 2.1 0 0 1-1.516-.66L26.526 2.2A6.4 6.4 0 0 0 24.467.616 6.2 6.2 0 0 0 21.98 0H7.537C5.538 0 3.62.83 2.207 2.307.794 3.784 0 5.787 0 7.876v50.248c0 2.089.794 4.092 2.207 5.57C3.621 65.17 5.538 66 7.537 66h64.926c2 0 3.916-.83 5.33-2.307C79.206 62.216 80 60.213 80 58.124V15.4c0-1.75-.665-3.43-1.85-4.667C76.966 9.495 75.36 8.8 73.684 8.8"
	}, null, -1)])]);
}
// resources/js/components/assets/Browser/Grid.vue
var _sfc_main$6 = {
	mixins: [AssetBrowserMixin_default],
	components: {
		AnimatedFolder: AnimatedFolder_default,
		Breadcrumbs: Breadcrumbs_default,
		ContextItem: Item_default$2,
		ContextLabel: Label_default,
		ContextMenu: Menu_default$1,
		ContextSeparator: Separator_default,
		Context: Context_default,
		Editable: Editable_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownLabel: Label_default$1,
		DropdownItem: Item_default$1,
		DropdownSeparator: Separator_default$1,
		ItemActions: ItemActions_default,
		FolderSvg: { render },
		MiddleEllipsis: MiddleEllipsis_default
	},
	props: {
		assets: { type: Array },
		thumbnailSize: { type: Number },
		maxFiles: { type: Number },
		showCheckerboard: {
			type: Boolean,
			default: false
		},
		checkerboardMode: {
			type: String,
			default: "transparent"
		},
		reflowing: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return { shifting: false };
	},
	computed: {
		gridSize() {
			return `repeat(auto-fill, minmax(${this.thumbnailSize}px, 1fr))`;
		},
		visibleAssets() {
			return this.assets.filter((asset) => !this.isRemoved(asset.id));
		},
		reflowMoveClass() {
			return this.reflowing ? "transition-transform duration-300 ease-out" : void 0;
		},
		reflowLeaveActiveClass() {
			return this.reflowing ? "absolute" : void 0;
		}
	},
	setup() {
		const { selectionClicked } = injectListingContext();
		return { selectionClicked };
	},
	methods: { isSelected(id) {
		return this.selectedAssets.includes(id);
	} }
};
var _hoisted_1$4 = [
	"onDragover",
	"onDrop",
	"onDragstart"
];
var _hoisted_2$4 = {
	key: 0,
	class: "absolute inset-0 z-10 flex animate-in fade-in fill-mode-both items-center justify-center rounded-xl bg-white/75 [animation-delay:400ms] dark:bg-gray-900/75"
};
var _hoisted_3$4 = ["onClick"];
var _hoisted_4$4 = ["textContent", "title"];
var _hoisted_5$4 = {
	key: "creating-folder",
	class: "group/folder relative p-1"
};
var _hoisted_6$4 = { class: "group h-[66px] w-[80px]" };
var _hoisted_7$3 = {
	key: 0,
	class: "absolute inset-0 z-10 flex animate-in fade-in fill-mode-both items-center justify-center rounded-[inherit] bg-white/75 [animation-delay:400ms] dark:bg-gray-900/75"
};
var _hoisted_8$2 = [
	"onDragstart",
	"onClick",
	"onDblclick"
];
var _hoisted_9$2 = { class: "asset-thumb" };
var _hoisted_10$2 = ["src", "onError"];
var _hoisted_11$2 = { class: "absolute end-1 top-1 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity [&_button]:bg-white [&_button]:hover:bg-white [&_button]:dark:bg-gray-900 [&_button]:dark:hover:bg-gray-900" };
var _hoisted_12$1 = { class: "asset-filename" };
var _hoisted_13 = {
	key: 2,
	class: "text-center text-gray-500 text-sm py-4"
};
function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_AnimatedFolder = resolveComponent("AnimatedFolder");
	const _component_ContextItem = resolveComponent("ContextItem");
	const _component_ContextMenu = resolveComponent("ContextMenu");
	const _component_Context = resolveComponent("Context");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_FolderSvg = resolveComponent("FolderSvg");
	const _component_Editable = resolveComponent("Editable");
	const _component_file_icon = resolveComponent("file-icon");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ContextSeparator = resolveComponent("ContextSeparator");
	const _component_MiddleEllipsis = resolveComponent("MiddleEllipsis");
	const _component_ui_card = resolveComponent("ui-card");
	return openBlock(), createBlock(_component_ui_card, { class: normalizeClass(["asset-browser-grid", {
		"space-y-8": _ctx.folders.length || $props.assets.length || _ctx.creatingFolder,
		"!p-0": _ctx.folders.length === 0 && $props.assets.length === 0 && !_ctx.creatingFolder
	}]) }, {
		default: withCtx(() => [
			_ctx.folders.length || _ctx.creatingFolder ? (openBlock(), createBlock(TransitionGroup, {
				key: 0,
				tag: "section",
				class: "folder-grid-listing",
				"move-class": $options.reflowMoveClass,
				"leave-active-class": $options.reflowLeaveActiveClass
			}, {
				default: withCtx(() => [!_ctx.restrictFolderNavigation ? (openBlock(true), createElementBlock(Fragment, { key: 0 }, renderList(_ctx.visibleFolders, (folder) => {
					return openBlock(), createElementBlock("div", {
						key: folder.path,
						class: normalizeClass(["group/folder relative rounded-xl p-1", [_ctx.moveStateClasses(folder.path), { "ring-2 ring-blue-400": _ctx.dragOverFolder === folder.path }]]),
						draggable: true,
						onDragover: ($event) => _ctx.folderDragOver($event, folder),
						onDragleave: _cache[0] || (_cache[0] = (...args) => _ctx.folderDragLeave && _ctx.folderDragLeave(...args)),
						onDrop: withModifiers(($event) => _ctx.handleFolderDrop(folder), ["prevent"]),
						onDragstart: ($event) => _ctx.draggingFolder = folder.path,
						onDragend: _cache[1] || (_cache[1] = ($event) => {
							_ctx.draggingFolder = null;
							_ctx.dragOverFolder = null;
						})
					}, [_ctx.isMoving(folder.path) ? (openBlock(), createElementBlock("div", _hoisted_2$4, [createVNode(_component_ui_icon, {
						name: "loading",
						class: "size-6 text-gray-700 dark:text-gray-200"
					})])) : createCommentVNode("", true), createVNode(_component_ItemActions, {
						url: _ctx.folderActionUrl,
						actions: _ctx.folderActions(folder),
						item: folder.path,
						onStarted: _ctx.actionStarted,
						onCompleted: _ctx.actionCompleted
					}, {
						default: withCtx(({ actions }) => [createVNode(_component_Context, null, {
							trigger: withCtx(() => [createBaseVNode("button", {
								onClick: ($event) => _ctx.selectFolder(folder.path),
								class: "group h-[66px] w-[80px]"
							}, [createVNode(_component_AnimatedFolder, {
								drop: _ctx.dropFor(folder),
								class: "size-full text-blue-400/90 hover:text-blue-400"
							}, null, 8, ["drop"]), createBaseVNode("div", {
								class: "overflow-hidden mt-2 text-center text-xs text-ellipsis whitespace-nowrap text-gray-500 dark:text-gray-300",
								textContent: toDisplayString(folder.basename),
								title: folder.basename
							}, null, 8, _hoisted_4$4)], 8, _hoisted_3$4)]),
							default: withCtx(() => [createVNode(_component_ContextMenu, null, {
								default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList(actions, (action) => {
									return openBlock(), createBlock(_component_ContextItem, {
										key: action.handle,
										text: _ctx.__(action.title),
										icon: action.icon,
										variant: action.dangerous ? "destructive" : "default",
										onClick: action.run
									}, null, 8, [
										"text",
										"icon",
										"variant",
										"onClick"
									]);
								}), 128))]),
								_: 2
							}, 1024)]),
							_: 2
						}, 1024)]),
						_: 2
					}, 1032, [
						"url",
						"actions",
						"item",
						"onStarted",
						"onCompleted"
					])], 42, _hoisted_1$4);
				}), 128)) : createCommentVNode("", true), _ctx.creatingFolder ? (openBlock(), createElementBlock("div", _hoisted_5$4, [createBaseVNode("div", _hoisted_6$4, [createVNode(_component_FolderSvg, { class: "size-full text-blue-400/90 hover:text-blue-400" }), createVNode(_component_Editable, {
					ref: "newFolderInput",
					modelValue: _ctx.newFolderName,
					"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.newFolderName = $event),
					"start-with-edit-mode": true,
					"submit-mode": "both",
					placeholder: _ctx.__("Name"),
					class: normalizeClass(["flex w-[80px] items-center placeholder:lowercase justify-center overflow-hidden mt-2 text-center text-xs text-ellipsis whitespace-nowrap placeholder:text-gray-400 dark:placeholder:text-gray-500 text-gray-500", { "st-has-error": _ctx.creatingFolderError }]),
					onSubmit: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("create-folder", _ctx.newFolderName)),
					onCancel: _cache[4] || (_cache[4] = () => {
						_ctx.newFolderName = null;
						_ctx.$emit("cancel-creating-folder");
					})
				}, null, 8, [
					"modelValue",
					"placeholder",
					"class"
				])])])) : createCommentVNode("", true)]),
				_: 1
			}, 8, ["move-class", "leave-active-class"])) : createCommentVNode("", true),
			$props.assets.length ? (openBlock(), createBlock(TransitionGroup, {
				key: 1,
				tag: "section",
				class: "asset-grid-listing",
				style: normalizeStyle({ gridTemplateColumns: $options.gridSize }),
				"move-class": $options.reflowMoveClass,
				"leave-active-class": $options.reflowLeaveActiveClass
			}, {
				default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($options.visibleAssets, (asset) => {
					return openBlock(), createElementBlock("div", {
						key: asset.id,
						class: normalizeClass(["group relative", [_ctx.moveStateClasses(asset.id), { selected: $options.isSelected(asset.id) }]])
					}, [createVNode(_component_ItemActions, {
						url: _ctx.actionUrl,
						actions: asset.actions,
						item: asset.id,
						onStarted: _ctx.actionStarted,
						onCompleted: _ctx.actionCompleted
					}, {
						default: withCtx(({ actions }) => [createVNode(_component_Context, null, {
							trigger: withCtx(() => [createBaseVNode("div", { class: normalizeClass(["asset-tile group relative bg-white dark:bg-gray-900", [
								{ "opacity-50!": _ctx.isDragging(asset.id) && !_ctx.isMoving(asset.id) },
								asset.can_be_transparent && $props.showCheckerboard ? `bg-checkerboard bg-checkerboard-${$props.checkerboardMode}` : "",
								asset.can_be_transparent && !$props.showCheckerboard ? "bg-checkerboard before:opacity-0 hover:before:opacity-100" : ""
							]]) }, [
								_ctx.isMoving(asset.id) ? (openBlock(), createElementBlock("div", _hoisted_7$3, [createVNode(_component_ui_icon, {
									name: "loading",
									class: "size-6 text-gray-700 dark:text-gray-200"
								})])) : createCommentVNode("", true),
								createBaseVNode("button", {
									class: "size-full",
									draggable: true,
									onDragover: _cache[5] || (_cache[5] = withModifiers(() => {}, ["prevent"])),
									onDragstart: ($event) => _ctx.assetDragStarted($event, asset),
									onDragend: _cache[6] || (_cache[6] = ($event) => _ctx.draggingAsset = null),
									onClick: withModifiers(($event) => $setup.selectionClicked($props.assets.indexOf(asset), $event), ["stop"]),
									onDblclick: withModifiers(($event) => _ctx.$emit("edit-asset", asset), ["stop"])
								}, [createBaseVNode("div", { class: normalizeClass(["relative flex aspect-square size-full items-center justify-center", { "cursor-pointer": $props.maxFiles === 1 }]) }, [createBaseVNode("div", _hoisted_9$2, [asset.thumbnail ? (openBlock(), createElementBlock("img", {
									key: 0,
									src: asset.thumbnail,
									loading: "lazy",
									draggable: false,
									class: normalizeClass({
										"w-full p-4": asset.extension === "svg",
										"rounded-lg p-1": asset.orientation === "square"
									}),
									onError: ($event) => asset.thumbnail = null
								}, null, 42, _hoisted_10$2)) : (openBlock(), createBlock(_component_file_icon, {
									key: 1,
									extension: asset.extension,
									class: "size-1/2"
								}, null, 8, ["extension"]))])], 2)], 40, _hoisted_8$2),
								createBaseVNode("div", _hoisted_11$2, [createVNode(_component_Dropdown, { placement: "left-start" }, {
									default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
										default: withCtx(() => [
											createVNode(_component_DropdownItem, {
												text: _ctx.__(asset.editable ? "Edit" : "View"),
												onClick: ($event) => _ctx.edit(asset.id),
												icon: "edit"
											}, null, 8, ["text", "onClick"]),
											asset.actions.length ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 0 })) : createCommentVNode("", true),
											(openBlock(true), createElementBlock(Fragment, null, renderList(actions, (action) => {
												return openBlock(), createBlock(_component_DropdownItem, {
													key: action.handle,
													text: _ctx.__(action.title),
													icon: action.icon,
													variant: action.dangerous ? "destructive" : "default",
													onClick: action.run
												}, null, 8, [
													"text",
													"icon",
													"variant",
													"onClick"
												]);
											}), 128))
										]),
										_: 2
									}, 1024)]),
									_: 2
								}, 1024)])
							], 2)]),
							default: withCtx(() => [createVNode(_component_ContextMenu, null, {
								default: withCtx(() => [
									createVNode(_component_ContextItem, {
										icon: "edit",
										text: _ctx.__(asset.editable ? "Edit" : "View"),
										onClick: ($event) => _ctx.edit(asset.id)
									}, null, 8, ["text", "onClick"]),
									createVNode(_component_ContextSeparator),
									(openBlock(true), createElementBlock(Fragment, null, renderList(actions, (action) => {
										return openBlock(), createBlock(_component_ContextItem, {
											key: action.handle,
											text: _ctx.__(action.title),
											icon: action.icon,
											variant: action.dangerous ? "destructive" : "default",
											onClick: action.run
										}, null, 8, [
											"text",
											"icon",
											"variant",
											"onClick"
										]);
									}), 128))
								]),
								_: 2
							}, 1024)]),
							_: 2
						}, 1024)]),
						_: 2
					}, 1032, [
						"url",
						"actions",
						"item",
						"onStarted",
						"onCompleted"
					]), createBaseVNode("div", _hoisted_12$1, [createVNode(_component_MiddleEllipsis, { text: asset.basename }, null, 8, ["text"])])], 2);
				}), 128))]),
				_: 1
			}, 8, [
				"style",
				"move-class",
				"leave-active-class"
			])) : createCommentVNode("", true),
			_ctx.folders.length === 0 && $props.assets.length === 0 && !_ctx.creatingFolder ? (openBlock(), createElementBlock("div", _hoisted_13, toDisplayString(_ctx.__("No items found")), 1)) : createCommentVNode("", true)
		]),
		_: 1
	}, 8, ["class"]);
}
var Grid_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$6, [["render", _sfc_render$5], ["__file", "Grid.vue"]]);
// resources/js/components/assets/Browser/Table.vue
var _sfc_main$5 = {
	mixins: [AssetBrowserMixin_default],
	components: {
		AnimatedFolder: AnimatedFolder_default,
		AssetThumbnail: Thumbnail_default,
		Breadcrumbs: Breadcrumbs_default,
		Card: Card_default,
		Dropdown: Dropdown_default,
		DropdownItem: Item_default$1,
		DropdownLabel: Label_default$1,
		DropdownMenu: Menu_default,
		DropdownSeparator: Separator_default$1,
		Editable: Editable_default,
		ItemActions: ItemActions_default,
		Panel: Panel_default,
		PanelFooter: Footer_default,
		PanelHeader: Header_default,
		ListingTable: Table_default$1
	},
	props: {
		assets: Array,
		loading: Boolean,
		columns: Array,
		visibleColumns: Array,
		isSearching: Boolean
	},
	watch: { creatingFolder(creating) {
		if (creating) this.$nextTick(() => {
			this.$refs.newFolderInput.$el.scrollIntoView();
		});
	} }
};
var _hoisted_1$3 = [
	"draggable",
	"onDragover",
	"onDrop",
	"onDragstart"
];
var _hoisted_2$3 = [
	"data-moving",
	"data-moved",
	"onClick"
];
var _hoisted_3$3 = { class: "relative me-2 flex size-8 shrink-0 items-center justify-center" };
var _hoisted_4$3 = {
	key: 0,
	class: "absolute inset-0 flex animate-in fade-in fill-mode-both items-center justify-center [animation-delay:400ms]"
};
var _hoisted_5$3 = { class: "actions-column pe-3!" };
var _hoisted_6$3 = { key: 1 };
var _hoisted_7$2 = ["colspan"];
var _hoisted_8$1 = { class: "group flex cursor-pointer items-center" };
var _hoisted_9$1 = [
	"onDragstart",
	"data-moving",
	"data-moved",
	"data-removed"
];
var _hoisted_10$1 = { class: "relative shrink-0" };
var _hoisted_11$1 = {
	key: 0,
	class: "absolute inset-0 flex animate-in fade-in fill-mode-both items-center justify-center [animation-delay:400ms]"
};
var _hoisted_12 = ["onClick"];
function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_AnimatedFolder = resolveComponent("AnimatedFolder");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_file_icon = resolveComponent("file-icon");
	const _component_Editable = resolveComponent("Editable");
	const _component_asset_thumbnail = resolveComponent("asset-thumbnail");
	const _component_ListingTable = resolveComponent("ListingTable");
	const _component_Card = resolveComponent("Card");
	return openBlock(), createBlock(_component_Card, {
		inset: "",
		variant: "flat",
		class: "[&_tr:has([data-moving])]:pointer-events-none [&_tr:has([data-moved])]:pointer-events-none [&_tr:has([data-moved])]:opacity-0 [&_tr:has([data-moved])]:transition-opacity [&_tr:has([data-moved])]:duration-300 [&_tr:has([data-removed])]:hidden"
	}, {
		default: withCtx(() => [createVNode(_component_ListingTable, { contained: "" }, {
			"tbody-start": withCtx(() => [!_ctx.restrictFolderNavigation ? (openBlock(true), createElementBlock(Fragment, { key: 0 }, renderList(_ctx.visibleFolders, (folder, i) => {
				return openBlock(), createElementBlock("tr", {
					key: folder.path,
					class: "pointer-events-auto",
					draggable: _ctx.canMoveFolder(folder),
					onDragover: ($event) => _ctx.folderDragOver($event, folder),
					onDragleave: _cache[0] || (_cache[0] = (...args) => _ctx.folderDragLeave && _ctx.folderDragLeave(...args)),
					onDrop: withModifiers(($event) => _ctx.handleFolderDrop(folder), ["prevent"]),
					onDragstart: ($event) => _ctx.draggingFolder = folder.path,
					onDragend: _cache[1] || (_cache[1] = ($event) => {
						_ctx.draggingFolder = null;
						_ctx.dragOverFolder = null;
					})
				}, [
					_cache[7] || (_cache[7] = createBaseVNode("td", null, null, -1)),
					(openBlock(true), createElementBlock(Fragment, null, renderList($props.visibleColumns, (column) => {
						return openBlock(), createElementBlock("td", null, [column.field === "basename" ? (openBlock(), createElementBlock("a", {
							key: 0,
							class: normalizeClass(["group -m-1 -me-2.5 flex w-fit cursor-pointer items-center rounded-lg p-1 pe-2.5", [_ctx.moveStateClasses(folder.path), { "ring-2 ring-blue-400": _ctx.dragOverFolder === folder.path }]]),
							"data-moving": _ctx.isMoving(folder.path) || null,
							"data-moved": _ctx.isMoved(folder.path) || null,
							onClick: ($event) => _ctx.selectFolder(folder.path)
						}, [createBaseVNode("div", _hoisted_3$3, [createVNode(_component_AnimatedFolder, {
							drop: _ctx.dropFor(folder),
							class: "w-6.5 text-blue-400/90 group-hover:text-blue-400"
						}, null, 8, ["drop"]), _ctx.isMoving(folder.path) ? (openBlock(), createElementBlock("div", _hoisted_4$3, [createVNode(_component_ui_icon, {
							name: "loading",
							class: "size-5 text-gray-700 dark:text-gray-200"
						})])) : createCommentVNode("", true)]), createTextVNode(" " + toDisplayString(folder.basename), 1)], 10, _hoisted_2$3)) : createCommentVNode("", true)]);
					}), 256)),
					createBaseVNode("td", _hoisted_5$3, [createVNode(_component_ItemActions, {
						url: _ctx.folderActionUrl,
						actions: folder.actions,
						item: folder.path,
						onStarted: _ctx.actionStarted,
						onCompleted: _ctx.actionCompleted
					}, {
						default: withCtx(({ actions }) => [_ctx.folderActions(folder).length ? (openBlock(), createBlock(_component_Dropdown, {
							key: 0,
							placement: "left-start"
						}, {
							default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
								default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList(actions, (action) => {
									return openBlock(), createBlock(_component_DropdownItem, {
										key: action.handle,
										text: _ctx.__(action.title),
										icon: action.icon,
										variant: action.dangerous ? "destructive" : "default",
										onClick: action.run
									}, null, 8, [
										"text",
										"icon",
										"variant",
										"onClick"
									]);
								}), 128))]),
								_: 2
							}, 1024)]),
							_: 2
						}, 1024)) : createCommentVNode("", true)]),
						_: 2
					}, 1032, [
						"url",
						"actions",
						"item",
						"onStarted",
						"onCompleted"
					])])
				], 40, _hoisted_1$3);
			}), 128)) : createCommentVNode("", true), _ctx.creatingFolder ? (openBlock(), createElementBlock("tr", _hoisted_6$3, [_cache[8] || (_cache[8] = createBaseVNode("td", null, null, -1)), createBaseVNode("td", { colspan: $props.visibleColumns.length + 1 }, [createBaseVNode("a", _hoisted_8$1, [createVNode(_component_file_icon, {
				extension: "folder",
				class: "me-2 inline-block size-8 shrink-0 text-blue-400/90 group-hover:text-blue-400 dark:text-blue-400/90 dark:group-hover:text-blue-400"
			}), createVNode(_component_Editable, {
				ref: "newFolderInput",
				modelValue: _ctx.newFolderName,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.newFolderName = $event),
				"start-with-edit-mode": true,
				"submit-mode": "both",
				placeholder: _ctx.__("Name"),
				class: normalizeClass(["placeholder:lowercase", { "st-has-error": _ctx.creatingFolderError }]),
				onSubmit: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("create-folder", _ctx.newFolderName)),
				onCancel: _cache[4] || (_cache[4] = () => {
					_ctx.newFolderName = null;
					_ctx.$emit("cancel-creating-folder");
				})
			}, null, 8, [
				"modelValue",
				"placeholder",
				"class"
			])])], 8, _hoisted_7$2)])) : createCommentVNode("", true)]),
			"cell-basename": withCtx(({ row: asset, checkboxId }) => [createBaseVNode("div", {
				class: normalizeClass(["group flex w-fit items-center gap-2 sm:gap-3", [_ctx.moveStateClasses(asset.id), { "text-gray-400 dark:text-gray-500": _ctx.isDragging(asset.id) }]]),
				draggable: true,
				onDragover: _cache[5] || (_cache[5] = withModifiers(() => {}, ["prevent"])),
				onDragstart: ($event) => _ctx.assetDragStarted($event, asset),
				onDragend: _cache[6] || (_cache[6] = ($event) => _ctx.draggingAsset = null),
				"data-moving": _ctx.isMoving(asset.id) || null,
				"data-moved": _ctx.isMoved(asset.id) || null,
				"data-removed": _ctx.isRemoved(asset.id) || null
			}, [createBaseVNode("div", _hoisted_10$1, [createVNode(_component_asset_thumbnail, {
				asset,
				alt: "",
				square: true,
				class: normalizeClass(["size-8 cursor-pointer", { "opacity-50": _ctx.isDragging(asset.id) }]),
				onClick: withModifiers(($event) => _ctx.$emit("edit-asset", asset), ["stop"])
			}, null, 8, [
				"asset",
				"class",
				"onClick"
			]), _ctx.isMoving(asset.id) ? (openBlock(), createElementBlock("div", _hoisted_11$1, [createVNode(_component_ui_icon, {
				name: "loading",
				class: "size-5 text-gray-700 dark:text-gray-200"
			})])) : createCommentVNode("", true)]), createBaseVNode("button", {
				class: "cursor-pointer normal-nums select-none group-hover:text-ui-accent-text/80 dark:group-hover:text-ui-accent-text text-start",
				onClick: ($event) => _ctx.$emit("edit-asset", asset)
			}, toDisplayString($props.isSearching ? asset.path : asset.basename), 9, _hoisted_12)], 42, _hoisted_9$1)]),
			"prepended-row-actions": withCtx(({ row: asset }) => [createVNode(_component_DropdownItem, {
				text: _ctx.__(asset.editable ? "Edit" : "View"),
				onClick: ($event) => _ctx.edit(asset.id),
				icon: "edit"
			}, null, 8, ["text", "onClick"])]),
			_: 1
		})]),
		_: 1
	});
}
var Table_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$5, [["render", _sfc_render$4], ["__file", "Table.vue"]]);
// node_modules/form-data/lib/browser.js
var require_browser = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	module.exports = typeof self === "object" ? self.FormData : window.FormData;
}));
// node_modules/upload/lib/Upload.js
var require_Upload = /* @__PURE__ */ __commonJSMin(((exports, module, this$3) => {
	var __importDefault = this$3 && this$3.__importDefault || function(mod) {
		return mod && mod.__esModule ? mod : { "default": mod };
	};
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.Upload = void 0;
	var form_data_1 = __importDefault(require_browser());
	var Upload = class {
		constructor(options) {
			this.events = {
				state: /* @__PURE__ */ new Set(),
				error: /* @__PURE__ */ new Set(),
				progress: /* @__PURE__ */ new Set()
			};
			this.withCredentials = false;
			this._uploadedBytes = 0;
			this._totalBytes = 0;
			this._state = "new";
			if (!options) throw new Error("Options are required.");
			if (!options.url || typeof options.url !== "string") throw new Error("Destination URL is missing or invalid.");
			this.form = options.form;
			this.url = options.url;
			this.headers = options.headers;
			this.withCredentials = options.withCredentials;
		}
		/**
		* POSTs the form.
		*/
		upload() {
			return new Promise((resolve, reject) => {
				if (typeof window !== "undefined" && typeof XMLHttpRequest !== "undefined") {
					this.xhr = new XMLHttpRequest();
					if (this.withCredentials) this.xhr.withCredentials = true;
					this.xhr.open("POST", this.url, true);
					if (typeof this.headers === "object") for (const headerName of Object.keys(this.headers)) this.xhr.setRequestHeader(headerName, this.headers[headerName]);
					this.xhr.addEventListener("loadstart", () => {
						this.setState("started");
					});
					if (this.xhr.upload) this.xhr.upload.addEventListener("progress", (e) => {
						if (this._totalBytes !== e.total) this.setTotalBytes(e.total);
						this.setUploadedBytes(e.loaded);
					});
					this.xhr.addEventListener("load", () => {
						if (this.xhr) {
							this.setUploadedBytes(this.totalBytes);
							this.setState("successful");
							const response = {};
							const lines = this.xhr.getAllResponseHeaders().replace(/\r/g, "").split("\n");
							const headers = {};
							for (const line of lines) {
								const split = line.split(":");
								if (split.length != 2) continue;
								headers[split[0].trim()] = split[1].trim();
							}
							response.headers = headers;
							response.status = this.xhr.status;
							response.xhr = this.xhr;
							switch (this.xhr.responseType) {
								case "json":
									response.data = JSON.stringify(this.xhr.response);
									break;
								default: response.data = this.xhr.response;
							}
							resolve(response);
						}
					});
					this.xhr.addEventListener("error", () => {
						this.setState("failed");
						this.emit("error");
						reject();
					});
					this.xhr.addEventListener("abort", () => {
						this.setState("aborted");
					});
					if (this.form instanceof FormData) this.xhr.send(this.form);
					else {
						const form = this.form;
						const formData = new FormData();
						for (const key of Object.keys(this.form)) formData.set(key, form[key]);
						this.xhr.send(formData);
					}
				} else {
					const callback = (error, res) => {
						if (error) {
							this.setState("failed");
							this.emit("error");
							reject();
						} else {
							this.setUploadedBytes(this.totalBytes);
							this.setState("successful");
							let body = "";
							res.on("readable", () => {
								const chunk = res.read();
								if (chunk) body += chunk;
							});
							res.on("end", () => {
								const response = {};
								response.data = body;
								response.headers = res.headers;
								resolve(response);
							});
						}
					};
					const url = new URL(this.url);
					const options = {
						hostname: url.hostname,
						port: url.port,
						path: url.pathname,
						method: "POST",
						headers: this.headers
					};
					let formData;
					if (this.form instanceof form_data_1.default) formData = this.form;
					else {
						const form = this.form;
						formData = new form_data_1.default();
						for (const key of Object.keys(this.form)) formData.append(key, form[key]);
					}
					formData.getLength((error, length) => {
						this.setTotalBytes(length);
					});
					formData.on("data", (chunk) => {
						if (this.state === "new") this.setState("started");
						if (chunk.hasOwnProperty("length")) this.increaseUploadedBytes(chunk.length);
					});
					formData.submit(options, callback);
				}
			});
		}
		abort() {
			var _a;
			(_a = this.xhr) === null || _a === void 0 || _a.abort();
		}
		get uploadedBytes() {
			return this._uploadedBytes;
		}
		setUploadedBytes(value) {
			this._uploadedBytes = value;
			this.emit("progress", this.progress);
		}
		increaseUploadedBytes(value) {
			this._uploadedBytes += value;
			this.emit("progress", this.progress);
		}
		get totalBytes() {
			return this._totalBytes;
		}
		setTotalBytes(value) {
			this._totalBytes = value;
			this.emit("progress", this.progress);
		}
		/**
		* Current upload progress. A float between 0 and 1.
		*/
		get progress() {
			return this._totalBytes === 0 ? 0 : this._uploadedBytes / this._totalBytes;
		}
		get state() {
			return this._state;
		}
		setState(value) {
			const oldState = this._state;
			this._state = value;
			if (oldState !== this._state) this.emit("state", this._state);
		}
		/**
		* Adds a listener for a given event.
		* @param eventType Event type.
		* @param listener Listener function.
		*/
		on(eventType, listener) {
			this.events[eventType].add(listener);
		}
		/**
		* Removes a listener for a given event.
		* @param eventType Event type.
		* @param listener Listener function.
		*/
		off(eventType, listener) {
			this.events[eventType].delete(listener);
		}
		emit(eventType, ...args) {
			for (const listener of this.events[eventType]) listener.apply(this, args);
		}
	};
	exports.Upload = Upload;
}));
// node_modules/upload/lib/UploadFunction.js
var require_UploadFunction = /* @__PURE__ */ __commonJSMin(((exports, module, this$2) => {
	var __awaiter = this$2 && this$2.__awaiter || function(thisArg, _arguments, P, generator) {
		function adopt(value) {
			return value instanceof P ? value : new P(function(resolve) {
				resolve(value);
			});
		}
		return new (P || (P = Promise))(function(resolve, reject) {
			function fulfilled(value) {
				try {
					step(generator.next(value));
				} catch (e) {
					reject(e);
				}
			}
			function rejected(value) {
				try {
					step(generator["throw"](value));
				} catch (e) {
					reject(e);
				}
			}
			function step(result) {
				result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
			}
			step((generator = generator.apply(thisArg, _arguments || [])).next());
		});
	};
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.upload = void 0;
	var Upload_1 = require_Upload();
	function upload(url, form, options, withCredentials = false) {
		return __awaiter(this, void 0, void 0, function* () {
			const upload = new Upload_1.Upload(Object.assign({
				url,
				form,
				withCredentials
			}, options));
			if (options === null || options === void 0 ? void 0 : options.onProgress) upload.on("progress", options.onProgress);
			return yield upload.upload();
		});
	}
	exports.upload = upload;
}));
// resources/js/components/assets/Uploader.vue
var import_lib = (/* @__PURE__ */ __commonJSMin(((exports, module, this$1) => {
	var __createBinding = this$1 && this$1.__createBinding || (Object.create ? (function(o, m, k, k2) {
		if (k2 === void 0) k2 = k;
		Object.defineProperty(o, k2, {
			enumerable: true,
			get: function() {
				return m[k];
			}
		});
	}) : (function(o, m, k, k2) {
		if (k2 === void 0) k2 = k;
		o[k2] = m[k];
	}));
	var __exportStar = this$1 && this$1.__exportStar || function(m, exports) {
		for (var p in m) if (p !== "default" && !exports.hasOwnProperty(p)) __createBinding(exports, m, p);
	};
	Object.defineProperty(exports, "__esModule", { value: true });
	__exportStar(require_Upload(), exports);
	__exportStar(require_UploadFunction(), exports);
})))();
var Uploader_default = /*#__PURE__*/ _plugin_vue_export_helper_default({
	emits: [
		"updated",
		"upload-complete",
		"error"
	],
	render() {
		const fileField = h("input", {
			class: { hidden: true },
			type: "file",
			multiple: true,
			ref: "nativeFileField"
		});
		const events = this.enabled ? {
			onDragenter: this.dragenter,
			onDragover: this.dragover,
			onDragleave: this.dragleave,
			onDrop: this.drop
		} : {};
		return h("div", {
			class: "h-full",
			...events
		}, [h("div", { class: ["h-full", { "pointer-events-none": this.dragging }] }, [fileField, ...this.$slots.default({ dragging: this.enabled ? this.dragging : false })])]);
	},
	props: {
		enabled: {
			type: Boolean,
			default: () => true
		},
		container: String,
		path: String,
		url: {
			type: String,
			default: () => cp_url("assets")
		},
		extraData: {
			type: Object,
			default: () => ({})
		}
	},
	data() {
		return {
			dragging: false,
			uploads: []
		};
	},
	mounted() {
		this.$refs.nativeFileField.addEventListener("change", this.addNativeFileFieldSelections);
	},
	beforeUnmount() {
		this.$refs.nativeFileField.removeEventListener("change", this.addNativeFileFieldSelections);
	},
	watch: { uploads: {
		deep: true,
		handler(uploads) {
			this.$emit("updated", uploads);
			this.processUploadQueue();
		}
	} },
	computed: { activeUploads() {
		return this.uploads.filter((u) => u.instance.state === "started");
	} },
	methods: {
		browse() {
			this.$refs.nativeFileField.click();
		},
		addNativeFileFieldSelections(e) {
			for (let i = 0; i < e.target.files.length; i++) this.addFile(e.target.files[i]);
		},
		isFileDrag(e) {
			return Array.from(e.dataTransfer?.types ?? []).includes("Files");
		},
		dragenter(e) {
			if (!this.isFileDrag(e)) return;
			e.stopPropagation();
			e.preventDefault();
			this.dragging = true;
		},
		dragover(e) {
			if (!this.isFileDrag(e)) return;
			e.stopPropagation();
			e.preventDefault();
		},
		dragleave(e) {
			if (e.target !== e.currentTarget) return;
			this.dragging = false;
		},
		drop(e) {
			if (!this.isFileDrag(e)) return;
			e.stopPropagation();
			e.preventDefault();
			this.dragging = false;
			const { files, items } = e.dataTransfer;
			if (items && items.length && items[0].webkitGetAsEntry) this.addFilesFromDataTransferItems(items);
			else this.addFilesFromFileList(files);
		},
		addFilesFromFileList(files) {
			for (let i = 0; i < files.length; i++) this.addFile(files[i]);
		},
		addFilesFromDataTransferItems(items) {
			for (let i = 0; i < items.length; i++) {
				let item = items[i];
				if (item.webkitGetAsEntry) {
					const entry = item.webkitGetAsEntry();
					if (entry?.isFile) this.addFile(item.getAsFile());
					else if (entry?.isDirectory) this.addFilesFromDirectory(entry, entry.name);
				} else if (item.getAsFile) {
					if (item.kind === "file" || !item.kind) this.addFile(item.getAsFile());
				}
			}
		},
		addFilesFromDirectory(directory, path) {
			const reader = directory.createReader();
			const readEntries = () => reader.readEntries((entries) => {
				if (!entries.length) return;
				for (let entry of entries) if (entry.isFile) entry.file((file) => {
					if (!file.name.startsWith(".")) {
						file.relativePath = path;
						this.addFile(file);
					}
				});
				else if (entry.isDirectory) this.addFilesFromDirectory(entry, `${path}/${entry.name}`);
				readEntries();
			}, console.error);
			return readEntries();
		},
		addFile(file, data = {}) {
			if (!this.enabled) return;
			const id = nanoid();
			const upload = this.makeUpload(id, file, data);
			this.uploads.push({
				id,
				basename: file.name,
				extension: file.name.split(".").pop(),
				percent: 0,
				errorMessage: null,
				errorStatus: null,
				instance: upload,
				retry: (opts) => this.retry(id, opts)
			});
		},
		findUpload(id) {
			return this.uploads.find((u) => u.id === id);
		},
		findUploadIndex(id) {
			return this.uploads.findIndex((u) => u.id === id);
		},
		makeUpload(id, file, data = {}) {
			const upload = new import_lib.Upload({
				url: this.url,
				form: this.makeFormData(file, data),
				headers: { Accept: "application/json" }
			});
			upload.on("progress", (progress) => {
				this.findUpload(id).percent = progress * 100;
			});
			return upload;
		},
		makeFormData(file, data = {}) {
			const form = new FormData();
			form.append("file", file);
			if (file.relativePath) form.append("relativePath", file.relativePath);
			let parameters = {
				...this.extraData,
				container: this.container,
				folder: this.path,
				_token: Statamic.$config.get("csrfToken")
			};
			for (let key in parameters) form.append(key, parameters[key]);
			for (let key in data) form.append(key, data[key]);
			return form;
		},
		processUploadQueue() {
			if (this.activeUploads.length) return;
			const upload = this.uploads.find((u) => u.instance.state === "new" && !u.errorMessage);
			if (!upload) return;
			const id = upload.id;
			upload.instance.upload().then((response) => {
				let json = null;
				try {
					json = JSON.parse(response.data);
				} catch (error) {}
				response.status === 200 ? this.handleUploadSuccess(id, json) : this.handleUploadError(id, response.status, json);
				this.processUploadQueue();
			});
		},
		handleUploadSuccess(id, response) {
			this.$emit("upload-complete", response.data, this.uploads);
			this.uploads.splice(this.findUploadIndex(id), 1);
			this.handleToasts(response._toasts ?? []);
		},
		handleUploadError(id, status, response) {
			const upload = this.findUpload(id);
			let msg = response?.message;
			if (!msg) {
				if (status === 413) msg = __("Upload failed. The file is larger than is allowed by your server.");
				else msg = __("Upload failed. The file might be larger than is allowed by your server.");
			} else if ([422, 409].includes(status)) msg = Object.values(response.errors)[0][0];
			this.handleToasts(response?._toasts ?? []);
			upload.errorMessage = msg;
			upload.errorStatus = status;
			upload.existingId = response?.existing;
			this.$emit("error", upload, this.uploads);
			this.processUploadQueue();
		},
		handleToasts(toasts) {
			toasts.forEach((toast) => Statamic.$toast[toast.type](toast.message, { duration: toast.duration }));
		},
		retry(id, args) {
			let file = this.findUpload(id).instance.form.get("file");
			this.addFile(file, args);
			this.uploads.splice(this.findUploadIndex(id), 1);
		}
	}
}, [["__file", "Uploader.vue"]]);
// resources/js/components/assets/Upload.vue
var _sfc_main$3 = {
	components: {
		Button: Button_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default$1,
		Input: Input_default,
		Icon: Icon_default
	},
	props: {
		extension: String,
		basename: String,
		percent: Number,
		error: String,
		errorStatus: Number,
		allowSelectingExisting: Boolean
	},
	data() {
		return {
			showNewFilenameModal: false,
			newFilename: ""
		};
	},
	computed: { status() {
		if (this.error) return "error";
		else if (this.percent === 100) return "pending";
		else return "uploading";
	} },
	methods: {
		clear() {
			this.$emit("clear");
		},
		retryAndOverwrite() {
			this.$emit("retry", { option: "overwrite" });
		},
		retryWithTimestamp() {
			this.$emit("retry", { option: "timestamp" });
		},
		openNewFilenameModal() {
			this.showNewFilenameModal = true;
			this.newFilename = this.basename.substring(0, this.basename.lastIndexOf("."));
		},
		confirmNewFilename() {
			this.showNewFilenameModal = false;
			this.retryWithNewFilename();
		},
		retryWithNewFilename() {
			this.$emit("retry", {
				option: "rename",
				filename: this.newFilename
			});
		},
		selectExisting() {
			this.$emit("existing-selected");
		}
	}
};
var _hoisted_1$2 = { class: "p-3 overflow-hidden dark:border-gray-700 dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-400" };
var _hoisted_2$2 = { class: "flex flex-1 items-center gap-2 sm:gap-3" };
var _hoisted_3$2 = { class: "size-7 flex items-center justify-center" };
var _hoisted_4$2 = { class: "truncate" };
var _hoisted_5$2 = {
	key: 0,
	class: "h-1.5 flex-1 rounded-lg bg-gray-100"
};
var _hoisted_6$2 = {
	key: 1,
	class: "flex-1"
};
var _hoisted_7$1 = {
	key: 2,
	class: "flex items-center gap-2"
};
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_Icon = resolveComponent("Icon");
	const _component_Button = resolveComponent("Button");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_Input = resolveComponent("Input");
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", _hoisted_1$2, [createBaseVNode("div", _hoisted_2$2, [
		createBaseVNode("div", _hoisted_3$2, [$options.status === "error" ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 0,
			name: "warning-diamond",
			class: "size-5 text-red-600"
		}, null, 512)), [[_directive_tooltip, $props.error]]) : (openBlock(), createBlock(_component_Icon, {
			key: 1,
			name: "loading"
		}))]),
		createBaseVNode("div", _hoisted_4$2, toDisplayString($props.basename), 1),
		$options.status !== "error" ? (openBlock(), createElementBlock("div", _hoisted_5$2, [createBaseVNode("div", {
			class: "h-full rounded-sm bg-blue-500",
			style: normalizeStyle({ width: $props.percent + "%" })
		}, null, 4)])) : (openBlock(), createElementBlock("div", _hoisted_6$2)),
		$options.status === "error" ? (openBlock(), createElementBlock("div", _hoisted_7$1, [$props.errorStatus === 409 ? (openBlock(), createBlock(_component_Dropdown, { key: 0 }, {
			trigger: withCtx(() => [createVNode(_component_Button, {
				size: "xs",
				text: `${_ctx.__("Fix")}...`
			}, null, 8, ["text"])]),
			default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
				default: withCtx(() => [
					createVNode(_component_DropdownItem, {
						onClick: $options.retryAndOverwrite,
						text: _ctx.__("messages.uploader_overwrite_existing")
					}, null, 8, ["onClick", "text"]),
					createVNode(_component_DropdownItem, {
						onClick: $options.openNewFilenameModal,
						text: `${_ctx.__("messages.uploader_choose_new_filename")}...`
					}, null, 8, ["onClick", "text"]),
					createVNode(_component_DropdownItem, {
						onClick: $options.retryWithTimestamp,
						text: _ctx.__("messages.uploader_append_timestamp")
					}, null, 8, ["onClick", "text"]),
					$props.allowSelectingExisting ? (openBlock(), createBlock(_component_DropdownItem, {
						key: 0,
						onClick: $options.selectExisting,
						text: _ctx.__("messages.uploader_discard_use_existing")
					}, null, 8, ["onClick", "text"])) : createCommentVNode("", true)
				]),
				_: 1
			})]),
			_: 1
		})) : createCommentVNode("", true), createVNode(_component_Button, {
			size: "xs",
			onClick: $options.clear,
			text: _ctx.__("Discard")
		}, null, 8, ["onClick", "text"])])) : createCommentVNode("", true)
	]), createVNode(_component_confirmation_modal, {
		open: $data.showNewFilenameModal,
		title: _ctx.__("New Filename"),
		onCancel: _cache[1] || (_cache[1] = ($event) => $data.showNewFilenameModal = false),
		onConfirm: $options.confirmNewFilename
	}, {
		default: withCtx(() => [createVNode(_component_Input, {
			autoselect: "",
			modelValue: $data.newFilename,
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.newFilename = $event),
			onKeydown: withKeys($options.confirmNewFilename, ["enter"])
		}, null, 8, ["modelValue", "onKeydown"])]),
		_: 1
	}, 8, [
		"open",
		"title",
		"onConfirm"
	])]);
}
// resources/js/components/assets/Uploads.vue
var _sfc_main$2 = {
	props: {
		uploads: Array,
		allowSelectingExisting: Boolean
	},
	components: { Upload: /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$3, [["render", _sfc_render$3], ["__file", "Upload.vue"]]) },
	methods: {
		clearUpload(i) {
			this.uploads.splice(i, 1);
		},
		retry(i, args) {
			this.uploads[i].retry(args);
		},
		existingSelected(i) {
			this.$emit("existing-selected", this.uploads[i]);
			this.clearUpload(i);
		}
	}
};
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_upload = resolveComponent("upload");
	return openBlock(true), createElementBlock(Fragment, null, renderList($props.uploads, (upload, i) => {
		return openBlock(), createBlock(_component_upload, {
			key: upload.id,
			basename: upload.basename,
			extension: upload.extension,
			percent: upload.percent,
			error: upload.errorMessage,
			"error-status": upload.errorStatus,
			"allow-selecting-existing": $props.allowSelectingExisting,
			onClear: ($event) => $options.clearUpload(i),
			onRetry: ($event) => $options.retry(i, $event),
			onExistingSelected: ($event) => $options.existingSelected(i)
		}, null, 8, [
			"basename",
			"extension",
			"percent",
			"error",
			"error-status",
			"allow-selecting-existing",
			"onClear",
			"onRetry",
			"onExistingSelected"
		]);
	}), 128);
}
var Uploads_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "Uploads.vue"]]);
// resources/js/components/assets/Browser/Browser.vue
var ANIMATION_DELAY = 250;
var FADE_OUT_DURATION = 300;
var FOLDER_MIN_OPEN_DURATION = 520;
var FOLDER_CLOSE_DURATION = 300;
var REFLOW_DURATION = 400;
var _sfc_main$1 = {
	mixins: [HasPreferences_default],
	components: {
		PanelFooter: Footer_default,
		Panel: Panel_default,
		PanelHeader: Header_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default$1,
		Dropdown: Dropdown_default,
		DropdownSeparator: Separator_default$1,
		AssetThumbnail: Thumbnail_default,
		AssetEditor: Editor_default,
		ItemActions: ItemActions_default,
		Uploader: Uploader_default,
		Uploads: Uploads_default,
		Grid: Grid_default,
		Table: Table_default,
		Header: Header_default$1,
		Button: Button_default,
		ButtonGroup: Group_default$1,
		Listing: Listing_default,
		ListingTable: Table_default$1,
		ListingPagination: Pagination_default,
		ListingSearch: Search_default,
		ListingFilters: Filters_default,
		ListingCustomizeColumns: CustomizeColumns_default,
		Breadcrumbs: Breadcrumbs_default,
		Slider: Slider_default,
		Icon: Icon_default,
		ToggleGroup: Group_default$2,
		ToggleItem: Item_default$3
	},
	props: {
		allowBulkActions: {
			type: Boolean,
			default: true
		},
		allowSelectingExistingUpload: Boolean,
		autoselectUploads: Boolean,
		canCreateContainers: Boolean,
		createContainerUrl: String,
		container: Object,
		initialEditingAssetId: String,
		maxFiles: Number,
		queryScopes: Array,
		restrictFolderNavigation: Boolean,
		selectedAssets: Array,
		selectedPath: String,
		filters: Array,
		initialColumns: {
			type: Array,
			default: () => []
		},
		uploader: {
			type: Object,
			default: null
		}
	},
	setup() {
		const checkerboard = useCheckerboard();
		return {
			showCheckerboard: checkerboard.enabled,
			checkerboardIcon: checkerboard.icon,
			checkerboardMode: checkerboard.mode,
			cycleCheckerboard: checkerboard.cycle
		};
	},
	data() {
		return {
			columns: this.initialColumns,
			visibleColumns: this.initialColumns.filter((column) => column.visible),
			containers: [],
			initializing: true,
			loading: true,
			assets: [],
			path: this.selectedPath,
			folders: [],
			folder: {},
			searchQuery: "",
			activeFilters: {},
			editedAssetId: this.initialEditingAssetId,
			creatingFolder: false,
			creatingFolderError: false,
			uploads: [],
			page: 1,
			preferencesPrefix: `assets.${this.container.id}`,
			meta: {},
			sortColumn: this.container.sort_field,
			sortDirection: this.container.sort_direction,
			mode: "table",
			actionUrl: null,
			folderActionUrl: null,
			shifting: false,
			lastItemClicked: null,
			preventDragging: false,
			draggingMovable: false,
			moving: [],
			moved: [],
			removed: [],
			settlingMoves: [],
			drops: [],
			reflowing: false,
			gridThumbnailSize: this.$preferences.get("assets.browser_thumbnail_size", 200)
		};
	},
	computed: {
		requestUrl() {
			return this.isSearching ? cp_url(`assets/browse/search/${this.container.id}/${this.restrictFolderNavigation ? this.path : ""}`).replace(/\/$/, "") : cp_url(`assets/browse/folders/${this.container.id}/${this.path || ""}`).replace(/\/$/, "");
		},
		actionContext() {
			return { container: this.container.id };
		},
		additionalParameters() {
			return { queryScopes: this.queryScopes };
		},
		canCreateFolders() {
			return this.folder && this.container.can_create_folders && !this.restrictFolderNavigation;
		},
		canUpload() {
			return this.folder && this.container.can_upload;
		},
		containerIsEmpty() {
			return this.assets.length === 0 && this.folders.length === 0 && (!this.folder || !this.folder.parent_path);
		},
		editedAssetBasename() {
			let asset = this.assets.find((asset) => asset.id == this.editedAssetId);
			return asset ? asset.basename : null;
		},
		hasMaxFiles() {
			return this.maxFiles !== void 0 && this.maxFiles !== Infinity;
		},
		hasSelections() {
			return this.selectedAssets.length > 0;
		},
		hasActiveFilters() {
			return Object.entries(this.activeFilters).some(([key, value]) => {
				if (Array.isArray(value)) return value.length > 0;
				else if (typeof value === "object" && value !== null) return Object.keys(value).length > 0;
				return Boolean(value);
			});
		},
		isSearching() {
			return this.searchQuery || this.hasActiveFilters;
		},
		parameters() {
			return {
				page: this.page,
				perPage: this.perPage,
				sort: this.sortColumn,
				order: this.sortDirection,
				search: this.searchQuery,
				queryScopes: this.queryScopes,
				columns: this.visibleColumnParameters
			};
		},
		visibleColumnParameters: {
			get() {
				if (this.visibleColumns === null || this.visibleColumns === void 0) return null;
				return this.visibleColumns.map((column) => column.field).join(",");
			},
			set(value) {
				this.visibleColumns = value.split(",").map((field) => this.columns.find((column) => column.field === field));
			}
		},
		reachedSelectionLimit() {
			return this.selectedAssets.length >= this.maxFiles;
		},
		openDropPaths() {
			return this.drops.filter((drop) => !drop.closing).map((drop) => drop.path);
		},
		showAssetEditor() {
			return Boolean(this.editedAssetId);
		},
		sharedAssetProps() {
			return {
				actionUrl: this.actionUrl,
				containerIsEmpty: this.containerIsEmpty,
				folder: this.folder,
				folderActionUrl: this.folderActionUrl,
				folders: this.folders,
				maxFiles: this.maxFiles,
				restrictFolderNavigation: this.restrictFolderNavigation,
				path: this.path,
				selectedAssets: this.selectedAssets,
				moving: this.moving,
				moved: this.moved,
				removed: this.removed,
				drops: this.drops,
				creatingFolder: this.creatingFolder,
				creatingFolderError: this.creatingFolderError
			};
		},
		sharedAssetEvents() {
			return {
				"action-completed": this.actionCompleted,
				"action-started": this.actionStarted,
				"edit": this.edit,
				"edit-asset": (event) => this.$emit("edit-asset", event),
				"select-folder": this.selectFolder,
				"create-folder": this.createFolder,
				"cancel-creating-folder": () => {
					this.creatingFolder = false;
					this.creatingFolderError = false;
				},
				"prevent-dragging": (preventDragging) => this.preventDragging = preventDragging,
				"dragging-movable": (movable) => this.draggingMovable = movable,
				"move-items": this.moveItems,
				"update:creatingFolderError": (value) => this.creatingFolderError = value
			};
		}
	},
	mounted() {
		this.mode = this.getPreference("mode") || "table";
		this.addToCommandPalette();
	},
	watch: {
		mode(mode) {
			this.setPreference("mode", mode == "table" ? null : mode);
		},
		initializing(initializing) {
			if (initializing === false) this.$emit("initialized");
		},
		editedAssetId(editedAssetId) {
			let path = editedAssetId ? [this.path, this.editedAssetBasename].filter((value) => value != "/").join("/") + "/edit" : this.path;
			this.$emit("navigated", path);
		},
		loading(loading) {
			this.$progress.loading("asset-browser", loading);
		},
		parameters(after, before) {
			if (this.initializing || JSON.stringify(before) === JSON.stringify(after)) return;
			this.loadAssets();
		},
		path(path) {
			this.loadAssets();
			this.$emit("path-changed", path);
		},
		searchQuery() {
			this.page = 1;
		},
		activeFilters: {
			deep: true,
			handler() {
				this.page = 1;
			}
		},
		selectedPath: {
			immediate: true,
			handler(newPath) {
				if (!newPath.endsWith("/edit")) this.path = newPath;
			}
		},
		gridThumbnailSize: { handler: debounce(function(size) {
			this.$preferences.set("assets.browser_thumbnail_size", size);
		}, 300) }
	},
	methods: {
		filtersUpdated(filters) {
			this.activeFilters = filters;
		},
		modeChanged(mode) {
			this.mode = mode;
		},
		startCreatingFolder() {
			this.creatingFolder = true;
			this.creatingFolderError = false;
		},
		listingRequestCompleted({ response }) {
			this.assets = response.data.data;
			if (this.isSearching) {
				this.folder = null;
				this.folders = [];
			} else {
				const { meta, links } = response.data;
				this.folder = meta.folder;
				this.folders = meta.folder.folders;
				this.actionUrl = links.asset_action;
				this.folderActionUrl = links.folder_action;
			}
			this.initializing = false;
			this.settleMoves();
		},
		actionStarted() {
			this.loading = true;
		},
		actionCompleted() {
			this.$refs.listing.refresh();
		},
		containerActionCompleted(successful, response = {}) {
			if (!successful) {
				Statamic.$toast.error(response.message || __("Action failed"));
				return;
			}
			if (response.message !== false) Statamic.$toast.success(response.message || __("Action completed"));
			if (!response.redirect) router.reload({ onSuccess: () => this.$refs.listing.refresh() });
		},
		assetSaved() {
			this.loadAssets();
		},
		assetCreated(id) {
			this.closeAssetEditor();
			this.autoselect(id);
			this.loadAssets();
		},
		clearShift() {
			this.shifting = false;
		},
		async editPreviousAsset() {
			let currentAssetIndex = this.assets.findIndex((asset) => asset.id === this.editedAssetId);
			if (currentAssetIndex === 0) {
				if (this.page > 1) {
					this.page = this.page - 1;
					await this.loadAssets();
					if (this.assets.length > 0) {
						this.editedAssetId = null;
						this.$nextTick(() => {
							this.editedAssetId = this.assets.slice(-1)[0].id;
						});
					}
				}
				this.editedAssetId = null;
				return;
			}
			this.editedAssetId = null;
			this.$nextTick(() => {
				this.editedAssetId = this.assets.slice(currentAssetIndex - 1, currentAssetIndex)[0].id;
			});
		},
		async editNextAsset() {
			let currentAssetIndex = this.assets.findIndex((asset) => asset.id === this.editedAssetId);
			if (currentAssetIndex === this.assets.length - 1) {
				if (this.meta.last_page > this.page) {
					this.page = this.page + 1;
					await this.loadAssets();
					if (this.assets.length > 0) {
						this.editedAssetId = null;
						this.$nextTick(() => {
							this.editedAssetId = this.assets[0].id;
						});
					}
				}
				this.editedAssetId = null;
				return;
			}
			this.editedAssetId = null;
			this.$nextTick(() => {
				this.editedAssetId = this.assets.slice(currentAssetIndex + 1, currentAssetIndex + 2)[0].id;
			});
		},
		closeAssetEditor() {
			this.editedAssetId = null;
		},
		createFolder(name) {
			this.$axios.post(cp_url(`asset-containers/${this.container.id}/folders`), {
				path: this.path,
				directory: name
			}).then((response) => {
				this.$toast.success(__("Folder created"));
				this.folders.push(response.data);
				this.folders = sortBy(this.folders, "title");
				this.creatingFolder = false;
				this.creatingFolderError = false;
				this.$refs.grid?.clearNewFolderName();
				this.$refs.table?.clearNewFolderName();
			}).catch((e) => {
				if (e.response && e.response.status === 422) {
					const { message, errors } = e.response.data;
					errors.directory ? this.$toast.error(errors.directory[0]) : this.$toast.error(message);
					this.creatingFolderError = true;
					this.$refs.grid?.focusNewFolderInput();
					this.$refs.table?.focusNewFolderInput();
				} else {
					this.$toast.error(__("Something went wrong"));
					this.creatingFolderError = true;
				}
			});
		},
		edit(id) {
			this.editedAssetId = id;
		},
		existingUploadSelected(upload) {
			this.selectedAssets.push(upload.existingId);
			this.$emit("selections-updated", this.selectedAssets);
		},
		folderActions(folder) {
			return folder.actions || this.folder.actions || [];
		},
		loadAssets() {
			this.$nextTick(() => this.$refs.listing.refresh());
		},
		openFileBrowser() {
			(this.uploader || this.$refs.internalUploader).browse();
		},
		selectFolder(path) {
			this.path = path;
			this.page = 1;
			this.$emit("navigated", this.path);
		},
		moveItems({ url, action, items, destination }) {
			const payload = {
				action: action.handle,
				context: action.context,
				selections: items,
				values: { folder: destination }
			};
			const files = items.slice(0, 3).map((item) => ({ thumbnail: this.assets.find((asset) => asset.id === item)?.thumbnail }));
			const startedAt = Date.now();
			let failed = false;
			this.moving.push(...items);
			this.actionStarted();
			const opened = new Promise((resolve) => setTimeout(resolve, ANIMATION_DELAY)).then(() => {
				if (failed) return false;
				this.openDrop(destination, files);
				if (this.isSearching) return true;
				const stillMoving = items.filter((item) => this.moving.includes(item));
				this.moving = this.moving.filter((item) => !stillMoving.includes(item));
				this.moved.push(...stillMoving);
				setTimeout(() => this.removeMovedItems(stillMoving), FADE_OUT_DURATION);
				return true;
			});
			this.$axios.post(url, payload).then(({ data }) => {
				const folder = destination === "/" ? __("All") : destination.split("/").pop();
				Statamic.$toast.success(__n("Moved to :folder|Moved :count items to :folder", items.length, { folder }));
				this.settlingMoves.push({
					items,
					callback: data.callback
				});
			}).catch((error) => {
				failed = true;
				this.forgetMoves(items);
				Statamic.$toast.error(error.response?.data?.message || __("Action failed"));
			}).finally(() => {
				opened.then((didOpen) => didOpen && this.closeDrop(destination));
				const wait = startedAt + ANIMATION_DELAY + FADE_OUT_DURATION - Date.now();
				setTimeout(() => this.$refs.listing?.refresh(), Math.max(0, wait));
			});
		},
		openDrop(path, files) {
			const now = Date.now();
			const open = this.drops.find((drop) => drop.path === path && !drop.closing);
			if (open) {
				open.pending++;
				open.files = files;
				open.fileKey = now;
				return;
			}
			this.drops = [...this.drops.filter((drop) => drop.path !== path), {
				id: now,
				fileKey: now,
				path,
				files,
				pending: 1,
				openedAt: now,
				closing: false
			}];
		},
		closeDrop(path) {
			const drop = this.drops.find((drop) => drop.path === path && !drop.closing);
			if (!drop || --drop.pending > 0) return;
			const wait = drop.openedAt + FOLDER_MIN_OPEN_DURATION - Date.now();
			setTimeout(() => {
				if (drop.pending > 0) return;
				drop.closing = true;
				setTimeout(() => this.drops = this.drops.filter((other) => other !== drop), FOLDER_CLOSE_DURATION);
			}, Math.max(0, wait));
		},
		removeMovedItems(items) {
			const stillMoved = items.filter((item) => this.moved.includes(item));
			if (!stillMoved.length) return;
			this.reflow();
			this.removed.push(...stillMoved);
		},
		forgetMoves(items) {
			this.moving = this.moving.filter((item) => !items.includes(item));
			this.moved = this.moved.filter((item) => !items.includes(item));
			this.removed = this.removed.filter((item) => !items.includes(item));
		},
		settleMoves() {
			this.forgetMoves(this.settlingMoves.flatMap(({ items }) => items));
			this.settlingMoves.forEach(({ callback }) => {
				if (callback) Statamic.$callbacks.call(callback[0], ...callback.slice(1));
			});
			this.settlingMoves = [];
			this.loading = this.moving.length > 0 || this.moved.length > 0;
		},
		reflow() {
			this.reflowing = true;
			clearTimeout(this.reflowTimeout);
			this.reflowTimeout = setTimeout(() => this.reflowing = false, REFLOW_DURATION);
		},
		moveDraggedItemToFolder(path) {
			this.$refs[this.mode]?.handleFolderDrop({ path });
		},
		selectRange(from, to) {
			for (var i = from; i <= to; i++) {
				let asset = this.assets[i].id;
				if (!this.selectedAssets.includes(asset) && !this.reachedSelectionLimit) this.selectedAssets.push(asset);
				this.$emit("selections-updated", this.selectedAssets);
			}
		},
		shiftDown() {
			this.shifting = true;
		},
		sorted(column, direction) {
			this.sortColumn = column;
			this.sortDirection = direction;
		},
		toggleSelection(id, index, $event) {
			const i = this.selectedAssets.indexOf(id);
			this.$refs.browser.focus();
			if (this.maxFiles === 1) this.selectedAssets = [id];
			else if (i != -1) this.selectedAssets.splice(i, 1);
			else if (!this.reachedSelectionLimit) {
				if ($event.shiftKey && this.lastItemClicked !== null) this.selectRange(Math.min(this.lastItemClicked, index), Math.max(this.lastItemClicked, index));
				else this.selectedAssets.push(id);
			}
			this.$emit("selections-updated", this.selectedAssets);
			this.lastItemClicked = index;
		},
		uploadCompleted(asset) {
			if (this.autoselectUploads) this.autoselect(asset.id);
			this.loadAssets();
			this.$toast.success(__(":file uploaded", { file: asset.basename }));
		},
		autoselect(id) {
			this.sortColumn = "last_modified";
			this.sortDirection = "desc";
			if (this.maxFiles === 1) this.selectedAssets.splice(0, this.selectedAssets.length, id);
			else if (!this.reachedSelectionLimit) this.selectedAssets.push(id);
			this.$emit("selections-updated", this.selectedAssets);
		},
		uploadError(upload, uploads) {
			this.uploads = uploads;
			this.$toast.error(upload.errorMessage);
		},
		uploadsUpdated(uploads) {
			this.uploads = uploads;
		},
		addToCommandPalette() {
			Statamic.$commandPalette.add({
				when: () => this.canUpload,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Upload"),
				icon: "upload",
				action: () => this.openFileBrowser(),
				prioritize: true
			});
			Statamic.$commandPalette.add({
				when: () => this.canCreateFolders,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Create Folder"),
				icon: "folder-add",
				action: () => this.startCreatingFolder()
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Toggle Grid Layout"),
				icon: "layout-grid",
				when: () => this.mode === "table",
				action: () => this.mode = "grid"
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Toggle List Layout"),
				icon: "layout-list",
				when: () => this.mode === "grid",
				action: () => this.mode = "table"
			});
			if (this.createContainerUrl) Statamic.$commandPalette.add({
				when: () => this.canCreateContainers,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Create Container"),
				icon: "container-add",
				url: this.createContainerUrl
			});
			Statamic.$commandPalette.add({
				when: () => this.container.can_edit,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Configure Container"),
				icon: "cog",
				url: this.container.edit_url
			});
			Statamic.$commandPalette.add({
				when: () => this.container.can_edit_blueprint,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Edit Blueprint"),
				icon: "blueprint-edit",
				url: this.container.blueprint_url
			});
			Statamic.$commandPalette.add({
				when: () => this.container.can_delete,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Delete Container"),
				icon: "trash",
				action: () => this.$refs.deleter.confirm()
			});
			this.container.actions?.forEach((action) => Statamic.$commandPalette.add({
				when: () => Boolean(this.$refs.containerActions),
				category: Statamic.$commandPalette.category.Actions,
				text: [__("Container"), action.title],
				icon: action.icon,
				action: () => this.$refs.containerActions.preparedActions.find((prepared) => prepared.handle === action.handle)?.run()
			}));
		}
	}
};
var _hoisted_1$1 = { class: "pb-1" };
var _hoisted_2$1 = { class: "drag-notification" };
var _hoisted_3$1 = { class: "flex items-center gap-2 sm:gap-3 py-3 relative overflow-clip st-overflow-clip-margin" };
var _hoisted_4$1 = { class: "flex flex-1 items-center gap-2 sm:gap-3" };
var _hoisted_5$1 = ["textContent"];
var _hoisted_6$1 = {
	key: 1,
	class: "flex items-center gap-2 mr-2"
};
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Icon = resolveComponent("Icon");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_resource_deleter = resolveComponent("resource-deleter");
	const _component_Button = resolveComponent("Button");
	const _component_ToggleItem = resolveComponent("ToggleItem");
	const _component_ToggleGroup = resolveComponent("ToggleGroup");
	const _component_Header = resolveComponent("Header");
	const _component_ListingSearch = resolveComponent("ListingSearch");
	const _component_ListingFilters = resolveComponent("ListingFilters");
	const _component_ListingCustomizeColumns = resolveComponent("ListingCustomizeColumns");
	const _component_Breadcrumbs = resolveComponent("Breadcrumbs");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_Slider = resolveComponent("Slider");
	const _component_PanelHeader = resolveComponent("PanelHeader");
	const _component_Uploads = resolveComponent("Uploads");
	const _component_Table = resolveComponent("Table");
	const _component_Grid = resolveComponent("Grid");
	const _component_ListingPagination = resolveComponent("ListingPagination");
	const _component_PanelFooter = resolveComponent("PanelFooter");
	const _component_Panel = resolveComponent("Panel");
	const _component_Listing = resolveComponent("Listing");
	const _component_Uploader = resolveComponent("Uploader");
	const _component_AssetEditor = resolveComponent("AssetEditor");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", {
		ref: "browser",
		class: "h-full",
		onKeydown: _cache[4] || (_cache[4] = withModifiers((...args) => $options.shiftDown && $options.shiftDown(...args), ["shift"])),
		onKeyup: _cache[5] || (_cache[5] = (...args) => $options.clearShift && $options.clearShift(...args))
	}, [createVNode(_component_Uploader, {
		ref: "internalUploader",
		container: $props.container.id,
		path: $data.path,
		enabled: !$props.uploader && !$data.preventDragging && $options.canUpload,
		onUpdated: $options.uploadsUpdated,
		onUploadComplete: $options.uploadCompleted,
		onError: $options.uploadError
	}, {
		default: withCtx(({ dragging }) => [createBaseVNode("div", _hoisted_1$1, [withDirectives(createBaseVNode("div", _hoisted_2$1, [createVNode(_component_Icon, {
			name: "upload-cloud-large",
			class: "m-4 size-13"
		}), createBaseVNode("span", null, toDisplayString(_ctx.__("Drop File to Upload")), 1)], 512), [[vShow, dragging]]), createVNode(_component_Listing, {
			ref: "listing",
			url: $options.requestUrl,
			columns: $data.columns,
			"sort-column": $data.sortColumn,
			"sort-direction": $data.sortDirection,
			filters: $props.filters,
			"action-url": $data.actionUrl,
			"action-context": $options.actionContext,
			"allow-bulk-actions": $props.allowBulkActions,
			"allow-select-all-matching": false,
			selections: $props.selectedAssets,
			"max-selections": $props.maxFiles,
			"preferences-prefix": $data.preferencesPrefix,
			"additional-parameters": $options.additionalParameters,
			"search-query": $data.searchQuery,
			"onUpdate:searchQuery": _cache[2] || (_cache[2] = ($event) => $data.searchQuery = $event),
			onRequestCompleted: $options.listingRequestCompleted,
			"onUpdate:selections": _cache[3] || (_cache[3] = ($event) => _ctx.$emit("selections-updated", $event))
		}, {
			default: withCtx(({ items }) => [
				renderSlot(_ctx.$slots, "header", normalizeProps(guardReactiveProps({
					canUpload: $options.canUpload,
					openFileBrowser: $options.openFileBrowser,
					canCreateFolders: $options.canCreateFolders,
					startCreatingFolder: $options.startCreatingFolder,
					mode: $data.mode,
					modeChanged: $options.modeChanged
				})), () => [createVNode(_component_Header, {
					title: _ctx.__($props.container.title),
					icon: "assets"
				}, {
					default: withCtx(() => [
						createVNode(_component_ItemActions, {
							ref: "containerActions",
							url: $props.container.actions_url,
							actions: $props.container.actions,
							item: $props.container.id,
							onCompleted: $options.containerActionCompleted
						}, {
							default: withCtx(({ actions }) => [$props.container.can_edit || $props.container.can_delete || $props.container.can_edit_blueprint || actions.length ? (openBlock(), createBlock(_component_Dropdown, { key: 0 }, {
								default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
									default: withCtx(() => [
										$props.canCreateContainers ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 0,
											icon: "container-add",
											text: _ctx.__("Create Container"),
											href: $props.createContainerUrl
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										$props.container.can_edit ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 1,
											icon: "cog",
											text: _ctx.__("Configure Container"),
											href: $props.container.edit_url
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										$props.container.can_edit_blueprint ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 2,
											icon: "blueprint-edit",
											text: _ctx.__("Edit Blueprint"),
											href: $props.container.blueprint_url
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										($props.canCreateContainers || $props.container.can_edit || $props.container.can_edit_blueprint) && actions.length ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 3 })) : createCommentVNode("", true),
										(openBlock(true), createElementBlock(Fragment, null, renderList(actions, (action) => {
											return openBlock(), createBlock(_component_DropdownItem, {
												key: action.handle,
												text: _ctx.__(action.title),
												icon: action.icon,
												variant: action.dangerous ? "destructive" : "default",
												onClick: action.run
											}, null, 8, [
												"text",
												"icon",
												"variant",
												"onClick"
											]);
										}), 128)),
										$props.container.can_delete ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 4 })) : createCommentVNode("", true),
										$props.container.can_delete ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 5,
											icon: "trash",
											variant: "destructive",
											text: _ctx.__("Delete Container"),
											onClick: _cache[0] || (_cache[0] = ($event) => {
												$event.preventDefault();
												_ctx.$refs.deleter.confirm();
											})
										}, null, 8, ["text"])) : createCommentVNode("", true)
									]),
									_: 2
								}, 1024)]),
								_: 2
							}, 1024)) : createCommentVNode("", true)]),
							_: 1
						}, 8, [
							"url",
							"actions",
							"item",
							"onCompleted"
						]),
						createVNode(_component_resource_deleter, {
							ref: "deleter",
							"resource-title": _ctx.__($props.container.title),
							route: $props.container.delete_url
						}, null, 8, ["resource-title", "route"]),
						$options.canUpload ? (openBlock(), createBlock(_component_Button, {
							key: 0,
							text: _ctx.__("Upload"),
							icon: "upload",
							onClick: $options.openFileBrowser
						}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
						$options.canCreateFolders ? (openBlock(), createBlock(_component_Button, {
							key: 1,
							text: _ctx.__("Create Folder"),
							icon: "folder-add",
							onClick: $options.startCreatingFolder
						}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
						createVNode(_component_ToggleGroup, {
							"model-value": $data.mode,
							"onUpdate:modelValue": $options.modeChanged
						}, {
							default: withCtx(() => [createVNode(_component_ToggleItem, {
								icon: "layout-grid",
								value: "grid"
							}), createVNode(_component_ToggleItem, {
								icon: "layout-list",
								value: "table"
							})]),
							_: 1
						}, 8, ["model-value", "onUpdate:modelValue"])
					]),
					_: 1
				}, 8, ["title"]), createBaseVNode("div", _hoisted_3$1, [createBaseVNode("div", _hoisted_4$1, [createVNode(_component_ListingSearch, { label: _ctx.__("Search assets") }, null, 8, ["label"]), createVNode(_component_ListingFilters, { onFiltersUpdated: $options.filtersUpdated }, null, 8, ["onFiltersUpdated"])]), $data.mode === "table" ? (openBlock(), createBlock(_component_ListingCustomizeColumns, { key: 0 })) : createCommentVNode("", true)])]),
				$options.containerIsEmpty && !$data.creatingFolder ? (openBlock(), createElementBlock("div", {
					key: 0,
					class: "rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center text-gray-500",
					textContent: toDisplayString(_ctx.__("No results"))
				}, null, 8, _hoisted_5$1)) : (openBlock(), createBlock(_component_Panel, {
					key: 1,
					class: normalizeClass({ "relative overflow-x-auto overscroll-x-contain": $data.mode === "table" })
				}, {
					default: withCtx(() => [
						createVNode(_component_PanelHeader, { class: "flex items-center justify-between gap-2 px-1!" }, {
							default: withCtx(() => [!$props.restrictFolderNavigation ? (openBlock(), createBlock(_component_Breadcrumbs, {
								key: 0,
								path: $data.path,
								droppable: $data.draggingMovable,
								"current-droppable": $options.isSearching,
								"open-paths": $options.openDropPaths,
								onNavigated: $options.selectFolder,
								onDropped: $options.moveDraggedItemToFolder
							}, null, 8, [
								"path",
								"droppable",
								"current-droppable",
								"open-paths",
								"onNavigated",
								"onDropped"
							])) : createCommentVNode("", true), $data.mode === "grid" ? (openBlock(), createElementBlock("div", _hoisted_6$1, [withDirectives(createVNode(_component_ui_button, {
								inset: "",
								size: "sm",
								variant: "ghost",
								"icon-only": "",
								icon: $setup.checkerboardIcon,
								"aria-label": _ctx.__("Transparency"),
								onClick: $setup.cycleCheckerboard
							}, null, 8, [
								"icon",
								"aria-label",
								"onClick"
							]), [[_directive_tooltip, _ctx.__("Transparency")]]), createVNode(_component_Slider, {
								size: "sm",
								class: "w-24!",
								variant: "subtle",
								modelValue: $data.gridThumbnailSize,
								"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.gridThumbnailSize = $event),
								min: 60,
								max: 300,
								step: 25
							}, null, 8, ["modelValue"])])) : createCommentVNode("", true)]),
							_: 1
						}),
						$data.uploads.length ? (openBlock(), createBlock(_component_Uploads, {
							key: 0,
							uploads: $data.uploads,
							"allow-selecting-existing": $props.allowSelectingExistingUpload,
							class: "mb-3 rounded-lg",
							onExistingSelected: $options.existingUploadSelected
						}, null, 8, [
							"uploads",
							"allow-selecting-existing",
							"onExistingSelected"
						])) : createCommentVNode("", true),
						$data.mode === "table" ? (openBlock(), createBlock(_component_Table, mergeProps({
							key: 1,
							ref: "table",
							assets: items,
							folders: $data.folders,
							columns: $data.columns,
							"visible-columns": $data.visibleColumns,
							"is-searching": $options.isSearching
						}, $options.sharedAssetProps, toHandlers($options.sharedAssetEvents)), null, 16, [
							"assets",
							"folders",
							"columns",
							"visible-columns",
							"is-searching"
						])) : createCommentVNode("", true),
						$data.mode === "grid" ? (openBlock(), createBlock(_component_Grid, mergeProps({
							key: 2,
							ref: "grid",
							assets: items,
							"action-url": $data.actionUrl,
							"thumbnail-size": $data.gridThumbnailSize,
							"show-checkerboard": $setup.showCheckerboard,
							"checkerboard-mode": $setup.checkerboardMode,
							reflowing: $data.reflowing
						}, $options.sharedAssetProps, toHandlers($options.sharedAssetEvents)), null, 16, [
							"assets",
							"action-url",
							"thumbnail-size",
							"show-checkerboard",
							"checkerboard-mode",
							"reflowing"
						])) : createCommentVNode("", true),
						createVNode(_component_PanelFooter, null, {
							default: withCtx(() => [createVNode(_component_ListingPagination)]),
							_: 1
						})
					]),
					_: 2
				}, 1032, ["class"])),
				renderSlot(_ctx.$slots, "footer")
			]),
			_: 3
		}, 8, [
			"url",
			"columns",
			"sort-column",
			"sort-direction",
			"filters",
			"action-url",
			"action-context",
			"allow-bulk-actions",
			"selections",
			"max-selections",
			"preferences-prefix",
			"additional-parameters",
			"search-query",
			"onRequestCompleted"
		])])]),
		_: 3
	}, 8, [
		"container",
		"path",
		"enabled",
		"onUpdated",
		"onUploadComplete",
		"onError"
	]), $options.showAssetEditor ? (openBlock(), createBlock(_component_AssetEditor, {
		key: 0,
		id: $data.editedAssetId,
		"redirect-after-crop": !$props.autoselectUploads,
		onPrevious: $options.editPreviousAsset,
		onNext: $options.editNextAsset,
		onClosed: $options.closeAssetEditor,
		onSaved: $options.assetSaved,
		onCreated: $options.assetCreated,
		onActionStarted: $options.actionStarted,
		onActionCompleted: $options.actionCompleted
	}, null, 8, [
		"id",
		"redirect-after-crop",
		"onPrevious",
		"onNext",
		"onClosed",
		"onSaved",
		"onCreated",
		"onActionStarted",
		"onActionCompleted"
	])) : createCommentVNode("", true)], 544);
}
var Browser_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "Browser.vue"]]);
// resources/js/components/assets/Selector.vue
var _sfc_main = {
	mixins: [HasPreferences_default],
	components: {
		Uploads: Uploads_default,
		Uploader: Uploader_default,
		PanelHeader: Header_default,
		Grid: Grid_default,
		Slider: Slider_default,
		ListingPagination: Pagination_default,
		Breadcrumbs: Breadcrumbs_default,
		AssetBrowser: Browser_default,
		Button: Button_default,
		ToggleGroup: Group_default$2,
		ToggleItem: Item_default$3,
		Table: Table_default$1,
		Search: Search_default,
		Pagination: Pagination_default,
		Panel: Panel_default,
		PanelFooter: Footer_default,
		Icon: Icon_default
	},
	props: {
		container: Object,
		folder: String,
		selected: Array,
		maxFiles: Number,
		queryScopes: Array,
		columns: Array,
		restrictFolderNavigation: {
			type: Boolean,
			default() {
				return false;
			}
		}
	},
	data() {
		return {
			browserSelections: this.selected,
			currentPath: this.folder,
			uploaderInstance: null
		};
	},
	mounted() {
		this.uploaderInstance = markRaw(this.$refs.uploader);
	},
	computed: { hasMaxFiles() {
		return this.maxFiles === Infinity ? false : Boolean(this.maxFiles);
	} },
	watch: { browserSelections: {
		deep: true,
		handler: function(selections) {
			if (this.maxFiles === 1 && selections.length === 1) this.select();
		}
	} },
	methods: {
		/**
		* Confirm the updated selections
		*/
		select() {
			this.$emit("selected", this.browserSelections);
			this.close();
		},
		/**
		* Close this selector
		*/
		close() {
			this.$emit("closed");
		},
		/**
		* Selections have been updated within the browser component.
		*/
		selectionsUpdated(selections) {
			this.browserSelections = selections;
		},
		toggleAssetSelection(asset) {
			this.browserSelections = this.browserSelections.includes(asset.id) ? this.browserSelections.filter((id) => id !== asset.id) : [...this.browserSelections, asset.id];
		},
		focusSearchInput() {
			this.$nextTick(() => this.$refs.search.focus());
		}
	}
};
var _hoisted_1 = { class: "relative h-full" };
var _hoisted_2 = { class: "drag-notification" };
var _hoisted_3 = { class: "flex h-full min-h-0 flex-col" };
var _hoisted_4 = { class: "flex flex-1 flex-col gap-4 overflow-auto p-4" };
var _hoisted_5 = { class: "flex flex-1" };
var _hoisted_6 = { class: "absolute inset-0 z-200 flex items-center justify-center text-center" };
var _hoisted_7 = { class: "flex items-center gap-2 sm:gap-3 mb-4" };
var _hoisted_8 = { class: "flex flex-1 items-center gap-2 sm:gap-3" };
var _hoisted_9 = { class: "flex items-center justify-between border-t bg-gray-100 dark:bg-gray-850 dark:border-gray-700 px-4 py-2 sm:p-4" };
var _hoisted_10 = ["textContent"];
var _hoisted_11 = { class: "flex items-center space-x-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Icon = resolveComponent("Icon");
	const _component_Search = resolveComponent("Search");
	const _component_Button = resolveComponent("Button");
	const _component_ToggleItem = resolveComponent("ToggleItem");
	const _component_ToggleGroup = resolveComponent("ToggleGroup");
	const _component_AssetBrowser = resolveComponent("AssetBrowser");
	const _component_Uploader = resolveComponent("Uploader");
	return openBlock(), createBlock(_component_Uploader, {
		ref: "uploader",
		container: $props.container.id,
		path: $data.currentPath,
		enabled: $props.container.can_upload,
		onUpdated: _cache[1] || (_cache[1] = (uploads) => _ctx.$refs.browser?.uploadsUpdated(uploads)),
		onUploadComplete: _cache[2] || (_cache[2] = (asset) => _ctx.$refs.browser?.uploadCompleted(asset)),
		onError: _cache[3] || (_cache[3] = (upload, uploads) => _ctx.$refs.browser?.uploadError(upload, uploads))
	}, {
		default: withCtx(({ dragging }) => [createBaseVNode("div", _hoisted_1, [withDirectives(createBaseVNode("div", _hoisted_2, [createVNode(_component_Icon, {
			name: "upload-cloud-large",
			class: "m-4 size-13"
		}), createBaseVNode("span", null, toDisplayString(_ctx.__("Drop File to Upload")), 1)], 512), [[vShow, dragging]]), createBaseVNode("div", _hoisted_3, [createBaseVNode("div", _hoisted_4, [createVNode(_component_AssetBrowser, {
			ref: "browser",
			container: $props.container,
			"initial-per-page": _ctx.$config.get("paginationSize"),
			"initial-columns": $props.columns,
			"selected-path": $props.folder,
			"selected-assets": $data.browserSelections,
			"restrict-folder-navigation": $props.restrictFolderNavigation,
			"max-files": $props.maxFiles,
			"query-scopes": $props.queryScopes,
			"autoselect-uploads": true,
			uploader: $data.uploaderInstance,
			"allow-selecting-existing-upload": "",
			"allow-bulk-actions": false,
			onSelectionsUpdated: $options.selectionsUpdated,
			onEditAsset: $options.toggleAssetSelection,
			onInitialized: $options.focusSearchInput,
			onPathChanged: _cache[0] || (_cache[0] = ($event) => $data.currentPath = $event)
		}, {
			initializing: withCtx(() => [createBaseVNode("div", _hoisted_5, [createBaseVNode("div", _hoisted_6, [createVNode(_component_Icon, { name: "loading" })])])]),
			header: withCtx(({ canUpload, openFileBrowser, canCreateFolders, startCreatingFolder, mode, modeChanged }) => [createBaseVNode("div", _hoisted_7, [
				createBaseVNode("div", _hoisted_8, [createVNode(_component_Search, {
					ref: "search",
					label: _ctx.__("Search assets")
				}, null, 8, ["label"])]),
				canUpload ? (openBlock(), createBlock(_component_Button, {
					key: 0,
					text: _ctx.__("Upload"),
					icon: "upload",
					onClick: openFileBrowser
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				canCreateFolders ? (openBlock(), createBlock(_component_Button, {
					key: 1,
					text: _ctx.__("Create Folder"),
					icon: "folder-add",
					onClick: startCreatingFolder
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				createVNode(_component_ToggleGroup, {
					"model-value": mode,
					"onUpdate:modelValue": modeChanged
				}, {
					default: withCtx(() => [createVNode(_component_ToggleItem, {
						icon: "layout-grid",
						value: "grid"
					}), createVNode(_component_ToggleItem, {
						icon: "layout-list",
						value: "table"
					})]),
					_: 1
				}, 8, ["model-value", "onUpdate:modelValue"])
			])]),
			_: 1
		}, 8, [
			"container",
			"initial-per-page",
			"initial-columns",
			"selected-path",
			"selected-assets",
			"restrict-folder-navigation",
			"max-files",
			"query-scopes",
			"uploader",
			"onSelectionsUpdated",
			"onEditAsset",
			"onInitialized"
		])]), createBaseVNode("div", _hoisted_9, [createBaseVNode("div", {
			class: "dark:text-gray-200 text-sm text-gray-700",
			textContent: toDisplayString($options.hasMaxFiles ? _ctx.__n(":count/:max selected", $data.browserSelections, { max: $props.maxFiles }) : _ctx.__n(":count asset selected|:count assets selected", $data.browserSelections))
		}, null, 8, _hoisted_10), createBaseVNode("div", _hoisted_11, [createVNode(_component_Button, {
			variant: "ghost",
			onClick: $options.close
		}, {
			default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Cancel")), 1)]),
			_: 1
		}, 8, ["onClick"]), createVNode(_component_Button, {
			variant: "primary",
			onClick: $options.select
		}, {
			default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Select")), 1)]),
			_: 1
		}, 8, ["onClick"])])])])])]),
		_: 1
	}, 8, [
		"container",
		"path",
		"enabled"
	]);
}
var Selector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Selector.vue"]]);
// resources/js/util/dedupeInFlight.js
var namespaces = /* @__PURE__ */ new Map();
/**
* Share identical in-flight async work across callers within a namespace.
* Once the promise settles the entry is removed — this is not a settled-result cache.
*
* The factory is invoked synchronously so callers that assert immediately after
* kicking off the request still see the underlying work start.
*
* @param {string} namespace
* @param {string} key
* @param {() => Promise<any>} factory
* @returns {Promise<any>}
*/
function dedupeInFlight(namespace, key, factory) {
	let map = namespaces.get(namespace);
	if (!map) {
		map = /* @__PURE__ */ new Map();
		namespaces.set(namespace, map);
	}
	let entry = map.get(key);
	if (entry) return entry;
	entry = Promise.resolve(factory()).finally(() => {
		if (map.get(key) === entry) map.delete(key);
	});
	map.set(key, entry);
	return entry;
}
export { Uploader_default as a, Thumbnail_default as c, Uploads_default as i, Selector_default as n, Editor_default as o, Browser_default as r, useCheckerboard as s, dedupeInFlight as t };
