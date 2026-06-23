import { B as openBlock, C as createVNode, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $ as CreateForm_default } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/blueprints/Create.vue
var _sfc_main = {
	__name: "Create",
	props: ["route", "icon"],
	setup(__props, { expose: __expose }) {
		__expose();
		useArchitecturalBackground();
		const __returned__ = {
			Head: Head_default,
			get CreateForm() {
				return CreateForm_default;
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
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Create Blueprint") }, null, 8, ["title"]), createVNode($setup["CreateForm"], {
		icon: $props.icon,
		route: $props.route,
		"without-handle": "",
		title: _ctx.__("Create Blueprint"),
		subtitle: _ctx.__("messages.blueprints_intro"),
		"title-instructions": _ctx.__("messages.blueprints_title_instructions")
	}, null, 8, [
		"icon",
		"route",
		"title",
		"subtitle",
		"title-instructions"
	])], 64);
}
var Create_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Create.vue"]]);
//#endregion
export { Create_default as default };
