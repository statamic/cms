import { H as openBlock, K as renderList, Mt as toDisplayString, T as createVNode, b as createCommentVNode, ot as withCtx, p as Fragment, v as createBaseVNode, w as createTextVNode, x as createElementBlock, y as createBlock } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { i as link_default } from "./index.esm-CMKtkbzu.js";
import { Kn as Panel_default, Vt as Header_default, hi as Icon_default, n as DocsCallout_default, ni as Badge_default } from "./ui-N8_n-Jsq.js";
import { r as Head_default } from "./index-CtKWr6Jk.js";
// resources/js/pages/preferences/nav/Index.vue
var _sfc_main = {
	__name: "Index",
	props: [
		"defaultPreferences",
		"defaultPreferencesUrl",
		"roles",
		"userPreferences",
		"userPreferencesUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			get Link() {
				return link_default;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get CardPanel() {
				return Panel_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Badge() {
				return Badge_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
var _hoisted_2 = { class: "space-y-6" };
var _hoisted_3 = { class: "flex items-center justify-between" };
var _hoisted_4 = { class: "flex items-center gap-2 sm:gap-3" };
var _hoisted_5 = { class: "flex items-center gap-2 sm:gap-3" };
var _hoisted_6 = { class: "flex items-center justify-between" };
var _hoisted_7 = { class: "flex items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode($setup["Head"], { title: _ctx.__("CP Nav Preferences") }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__("CP Nav Preferences"),
			icon: "preferences"
		}, null, 8, ["title"]),
		createBaseVNode("section", _hoisted_2, [
			createVNode($setup["CardPanel"], { heading: _ctx.__("Global Preferences") }, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_3, [createBaseVNode("div", _hoisted_4, [createVNode($setup["Icon"], { name: "globals" }), createVNode($setup["Link"], { href: $props.defaultPreferencesUrl }, {
					default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Default")), 1)]),
					_: 1
				}, 8, ["href"])]), Object.keys($props.defaultPreferences).length ? (openBlock(), createBlock($setup["Badge"], {
					key: 0,
					color: "green"
				}, {
					default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Modified")), 1)]),
					_: 1
				})) : createCommentVNode("", true)])]),
				_: 1
			}, 8, ["heading"]),
			$props.roles.length ? (openBlock(), createBlock($setup["CardPanel"], {
				key: 0,
				heading: _ctx.__("Preferences by Role")
			}, {
				default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.roles, (role) => {
					return openBlock(), createElementBlock("div", {
						key: role.handle,
						class: "flex items-center justify-between"
					}, [createBaseVNode("div", _hoisted_5, [createVNode($setup["Icon"], { name: "permissions" }), createVNode($setup["Link"], { href: role.editUrl }, {
						default: withCtx(() => [createTextVNode(toDisplayString(role.title), 1)]),
						_: 2
					}, 1032, ["href"])]), Object.keys(role.preferences).length ? (openBlock(), createBlock($setup["Badge"], {
						key: 0,
						color: "green"
					}, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Modified")), 1)]),
						_: 1
					})) : createCommentVNode("", true)]);
				}), 128))]),
				_: 1
			}, 8, ["heading"])) : createCommentVNode("", true),
			createVNode($setup["CardPanel"], { heading: _ctx.__("User Preferences") }, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_6, [createBaseVNode("div", _hoisted_7, [createVNode($setup["Icon"], { name: "avatar" }), createVNode($setup["Link"], { href: $props.userPreferencesUrl }, {
					default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("My Nav")), 1)]),
					_: 1
				}, 8, ["href"])]), Object.keys($props.userPreferences).length ? (openBlock(), createBlock($setup["Badge"], {
					key: 0,
					color: "green"
				}, {
					default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Modified")), 1)]),
					_: 1
				})) : createCommentVNode("", true)])]),
				_: 1
			}, 8, ["heading"])
		]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Customizing the Control Panel Nav"),
			url: "customizing-the-cp-nav"
		}, null, 8, ["topic"])
	]);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
export { Index_default as default };
