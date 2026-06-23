import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, f as Fragment, g as createBaseVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Ci as Icon_default, Kt as Menu_default, n as DocsCallout_default, qt as Item_default } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/assets/Empty.vue
var _sfc_main = {
	__name: "Empty",
	props: ["createUrl"],
	setup(__props, { expose: __expose }) {
		__expose();
		useArchitecturalBackground();
		const __returned__ = {
			Head: Head_default,
			get Icon() {
				return Icon_default;
			},
			get EmptyStateMenu() {
				return Menu_default;
			},
			get EmptyStateItem() {
				return Item_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get useArchitecturalBackground() {
				return useArchitecturalBackground;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "py-8 mt-8 text-center starting-style-transition" };
var _hoisted_2 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Asset Containers") }, null, 8, ["title"]),
		createBaseVNode("header", _hoisted_1, [createBaseVNode("h1", _hoisted_2, [createVNode($setup["Icon"], {
			name: "assets",
			class: "size-5 text-gray-500"
		}), createTextVNode(" " + toDisplayString(_ctx.__("Asset Containers")), 1)])]),
		createVNode($setup["EmptyStateMenu"], { heading: _ctx.__("statamic::messages.asset_container_intro") }, {
			default: withCtx(() => [createVNode($setup["EmptyStateItem"], {
				href: $props.createUrl,
				icon: "assets",
				heading: _ctx.__("Create Asset Container"),
				description: _ctx.__("Get started by creating your first asset container.")
			}, null, 8, [
				"href",
				"heading",
				"description"
			])]),
			_: 1
		}, 8, ["heading"]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Assets"),
			url: "assets"
		}, null, 8, ["topic"])
	], 64);
}
var Empty_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Empty.vue"]]);
//#endregion
export { Empty_default as default };
