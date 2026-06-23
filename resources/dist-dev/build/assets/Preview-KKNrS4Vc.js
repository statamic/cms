import { B as openBlock, _ as createBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Z as Popout_default } from "./ui-BfR7XN6t.js";
import Blank_default from "./Blank-CKDkY2Cn.js";
//#region resources/js/pages/entries/Preview.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Blank_default }, {
	__name: "Preview",
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			get LivePreviewPopout() {
				return Popout_default;
			},
			Blank: Blank_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["LivePreviewPopout"]);
}
var Preview_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Preview.vue"]]);
//#endregion
export { Preview_default as default };
