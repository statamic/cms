import { B as openBlock, C as createVNode, K as resolveComponent, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/fieldsets/Create.vue
var _sfc_main = {
	__name: "Create",
	props: ["route"],
	setup(__props, { expose: __expose }) {
		__expose();
		useArchitecturalBackground();
		const __returned__ = {
			Head: Head_default,
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
	const _component_ui_create_form = resolveComponent("ui-create-form");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Create Fieldset") }, null, 8, ["title"]), createVNode(_component_ui_create_form, {
		title: _ctx.__("Create Fieldset"),
		subtitle: _ctx.__("messages.fields_fieldsets_description"),
		icon: "fieldsets",
		route: $props.route,
		"title-instructions": _ctx.__("messages.fieldsets_title_instructions"),
		"handle-instructions": _ctx.__("messages.fieldsets_handle_instructions")
	}, null, 8, [
		"title",
		"subtitle",
		"route",
		"title-instructions",
		"handle-instructions"
	])], 64);
}
var Create_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Create.vue"]]);
//#endregion
export { Create_default as default };
