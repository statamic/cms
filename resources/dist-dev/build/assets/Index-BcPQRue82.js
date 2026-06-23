import { At as toDisplayString, B as openBlock, C as createVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default } from "./index.esm-B4SStoAz.js";
import { Hn as Panel_default, Jn as Heading_default, Kn as Subheading_default, Ut as Header_default, n as DocsCallout_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/Index.vue
var _sfc_main = {
	__name: "Index",
	props: ["utilities"],
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
			get Heading() {
				return Heading_default;
			},
			get Subheading() {
				return Subheading_default;
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
var _hoisted_1 = { class: "max-w-page mx-auto" };
var _hoisted_2 = { class: "flex flex-wrap starting-style-transition-children" };
var _hoisted_3 = ["innerHTML"];
var _hoisted_4 = { class: "flex-1 md:me-6" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Utilities") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Utilities"),
			icon: "utilities"
		}, null, 8, ["title"]),
		createVNode($setup["CardPanel"], null, {
			default: withCtx(() => [createBaseVNode("div", _hoisted_2, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.utilities, (utility) => {
				return openBlock(), createBlock($setup["Link"], {
					key: utility.url,
					href: utility.url,
					class: "group w-full items-start rounded-md border border-transparent px-2 py-3 lg:p-4 hover:bg-gray-100 dark:hover:bg-gray-800 md:flex lg:w-1/2"
				}, {
					default: withCtx(() => [createBaseVNode("div", {
						class: "size-6 text-gray-400 mt-1 mb-2 me-4",
						innerHTML: utility.icon
					}, null, 8, _hoisted_3), createBaseVNode("div", _hoisted_4, [createVNode($setup["Heading"], {
						size: "lg",
						text: utility.title
					}, null, 8, ["text"]), createVNode($setup["Subheading"], { textContent: toDisplayString(utility.description) }, null, 8, ["textContent"])])]),
					_: 2
				}, 1032, ["href"]);
			}), 128))])]),
			_: 1
		}),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Utilities"),
			url: "extending/utilities"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
