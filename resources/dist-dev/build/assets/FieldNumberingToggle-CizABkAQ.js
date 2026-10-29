import { D as defineComponent, H as openBlock, T as createVNode, Y as resolveDirective, _ as computed, d as withModifiers, nt as watch, ot as withCtx, st as withDirectives, y as createBlock, yt as ref } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { $ as Item_default, et as Group_default } from "./ui-BUy1lrUe.js";
import { b as preferences } from "./api-BzIpSBRz.js";
// resources/js/composables/forms/field-numbering.js
var state = null;
function useFieldNumberingPreference() {
	if (!state) {
		const showFieldNumbers = ref(preferences.get("forms.field_numbering", false));
		watch(showFieldNumbers, (value) => preferences.set("forms.field_numbering", value));
		state = { showFieldNumbers };
	}
	return state;
}
// resources/js/components/forms/FieldNumberingToggle.vue?vue&type=script&setup=true&lang.ts
var FieldNumberingToggle_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FieldNumberingToggle",
	setup(__props, { expose: __expose }) {
		__expose();
		const { showFieldNumbers } = useFieldNumberingPreference();
		const toggle = () => showFieldNumbers.value = !showFieldNumbers.value;
		const __returned__ = {
			showFieldNumbers,
			toggle,
			label: computed(() => showFieldNumbers.value ? __("Hide field numbers") : __("Show field numbers")),
			get ToggleGroup() {
				return Group_default;
			},
			get ToggleItem() {
				return Item_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/FieldNumberingToggle.vue
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock($setup["ToggleGroup"], {
		"model-value": $setup.showFieldNumbers ? "on" : null,
		size: "xs"
	}, {
		default: withCtx(() => [withDirectives(createVNode($setup["ToggleItem"], {
			value: "on",
			icon: "mail-sign-hashtag",
			"aria-label": $setup.label,
			onClick: withModifiers($setup.toggle, ["prevent"])
		}, null, 8, ["aria-label"]), [[_directive_tooltip, $setup.label]])]),
		_: 1
	}, 8, ["model-value"]);
}
var FieldNumberingToggle_default = /*#__PURE__*/ _plugin_vue_export_helper_default(FieldNumberingToggle_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "FieldNumberingToggle.vue"]]);
export { useFieldNumberingPreference as n, FieldNumberingToggle_default as t };
