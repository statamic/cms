import { H as openBlock, J as resolveComponent, y as createBlock } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { wn as CodeEditor_default } from "./ui-BUy1lrUe.js";
import { v as Fieldtype_default } from "./index-BV-PTyMc.js";
// resources/js/components/fieldtypes/YamlFieldtype.vue
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
export { YamlFieldtype_default as default };
