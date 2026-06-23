import { B as openBlock, C as createVNode, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { r as Head_default, y as DynamicHtmlRenderer_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/Show.vue
var _sfc_main = {
	__name: "Show",
	props: ["title", "html"],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Head: Head_default,
			DynamicHtmlRenderer: DynamicHtmlRenderer_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: [_ctx.__($props.title), _ctx.__("Utilities")] }, null, 8, ["title"]), createVNode($setup["DynamicHtmlRenderer"], { html: $props.html }, null, 8, ["html"])], 64);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
