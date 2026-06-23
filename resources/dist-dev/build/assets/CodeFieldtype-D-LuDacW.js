import { B as openBlock, K as resolveComponent, _ as createBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { yn as CodeEditor_default } from "./ui-BfR7XN6t.js";
import { _ as Fieldtype_default } from "./index-Bu3QBQkS.js";
//#region resources/js/components/fieldtypes/CodeFieldtype.vue
var _sfc_main = {
	mixins: [Fieldtype_default],
	components: { CodeEditor: CodeEditor_default },
	data() {
		return { escBinding: null };
	},
	computed: {
		mode() {
			return this.value.mode || this.config.mode;
		},
		replicatorPreview() {
			if (!this.showFieldPreviews) return;
			return this.value.code ? truncate(this.value.code, 60) : "";
		},
		internalFieldActions() {
			return [{
				title: __("Toggle Fullscreen Mode"),
				icon: ({ vm }) => vm.$refs.codeEditor.fullScreenMode ? "fullscreen-close" : "fullscreen-open",
				quick: true,
				visible: this.config.fullscreen,
				visibleWhenReadOnly: true,
				run: ({ vm }) => vm.toggleFullscreen()
			}];
		}
	},
	methods: {
		toggleFullscreen() {
			const wasFullscreen = this.$refs.codeEditor.fullScreenMode;
			this.$refs.codeEditor.toggleFullscreen();
			if (wasFullscreen) {
				if (this.escBinding) {
					this.escBinding.destroy();
					this.escBinding = null;
				}
			} else this.escBinding = this.$keys.bindGlobal("esc", this.toggleFullscreen);
		},
		modeUpdated(mode) {
			this.updateDebounced({
				code: this.value.code,
				mode
			});
		},
		codeUpdated(code) {
			this.updateDebounced({
				code,
				mode: this.mode
			});
		}
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_CodeEditor = resolveComponent("CodeEditor");
	return openBlock(), createBlock(_component_CodeEditor, {
		ref: "codeEditor",
		"color-mode": _ctx.config.color_mode,
		rulers: _ctx.config.rulers,
		disabled: _ctx.config.disabled,
		"read-only": _ctx.config.read_only,
		"key-map": _ctx.config.key_map,
		"tab-size": _ctx.config.indent_size,
		"indent-type": _ctx.config.indent_type,
		"line-numbers": _ctx.config.line_numbers,
		"line-wrapping": _ctx.config.line_wrapping,
		"allow-mode-selection": _ctx.config.mode_selectable,
		"show-mode-label": _ctx.config.show_mode_label,
		mode: $options.mode,
		"model-value": _ctx.value.code,
		title: _ctx.config.display,
		"field-actions": _ctx.fieldActions,
		"onUpdate:mode": $options.modeUpdated,
		"onUpdate:modelValue": $options.codeUpdated
	}, null, 8, [
		"color-mode",
		"rulers",
		"disabled",
		"read-only",
		"key-map",
		"tab-size",
		"indent-type",
		"line-numbers",
		"line-wrapping",
		"allow-mode-selection",
		"show-mode-label",
		"mode",
		"model-value",
		"title",
		"field-actions",
		"onUpdate:mode",
		"onUpdate:modelValue"
	]);
}
var CodeFieldtype_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "CodeFieldtype.vue"]]);
//#endregion
export { CodeFieldtype_default as default };
