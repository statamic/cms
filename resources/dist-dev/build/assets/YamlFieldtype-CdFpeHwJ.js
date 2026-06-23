import { B as openBlock, K as resolveComponent, _ as createBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { yn as CodeEditor_default } from "./ui-BfR7XN6t.js";
import { _ as Fieldtype_default } from "./index-Bu3QBQkS.js";
//#region resources/js/components/fieldtypes/YamlFieldtype.vue
var _sfc_main = {
	mixins: [Fieldtype_default],
	components: { CodeEditor: CodeEditor_default }
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_CodeEditor = resolveComponent("CodeEditor");
	return openBlock(), createBlock(_component_CodeEditor, {
		ref: "codeEditor",
		"allow-mode-selection": false,
		disabled: _ctx.config.disabled,
		"indent-type": "spaces",
		mode: "yaml",
		"model-value": _ctx.value,
		"read-only": _ctx.isReadOnly,
		"show-mode-label": false,
		"tab-size": 2,
		theme: _ctx.config.color_mode,
		"onUpdate:modelValue": _ctx.update
	}, null, 8, [
		"disabled",
		"model-value",
		"read-only",
		"theme",
		"onUpdate:modelValue"
	]);
}
var YamlFieldtype_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "YamlFieldtype.vue"]]);
//#endregion
export { YamlFieldtype_default as default };
