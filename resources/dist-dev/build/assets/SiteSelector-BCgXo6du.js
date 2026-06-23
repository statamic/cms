import { B as openBlock, _ as createBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { jn as Select_default } from "./ui-BfR7XN6t.js";
//#region resources/js/components/SiteSelector.vue
var _sfc_main = {
	__name: "SiteSelector",
	props: {
		sites: {
			type: Array,
			required: true
		},
		modelValue: {
			type: String,
			required: true
		}
	},
	emits: ["update:modelValue"],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = { get Select() {
			return Select_default;
		} };
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Select"], {
		class: "w-36",
		options: $props.sites,
		"option-label": "name",
		"option-value": "handle",
		align: "end",
		"adaptive-width": true,
		"model-value": $props.modelValue,
		"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("update:modelValue", $event))
	}, null, 8, ["options", "model-value"]);
}
var SiteSelector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "SiteSelector.vue"]]);
//#endregion
export { SiteSelector_default as t };
