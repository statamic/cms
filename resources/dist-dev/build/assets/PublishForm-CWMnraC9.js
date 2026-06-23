import { B as openBlock, C as createVNode, Dt as normalizeClass, f as Fragment, g as createBaseVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { O as Form_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/PublishForm.vue
var _sfc_main = {
	__name: "PublishForm",
	props: {
		icon: {
			type: String,
			required: false
		},
		title: {
			type: String,
			required: true
		},
		blueprint: {
			type: Object,
			required: true
		},
		values: {
			type: Object,
			required: true
		},
		meta: {
			type: Object,
			required: true
		},
		submitUrl: {
			type: String,
			required: true
		},
		submitMethod: {
			type: String,
			required: true
		},
		readOnly: {
			type: Boolean,
			required: false
		},
		asConfig: {
			type: Boolean,
			required: false
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			props: __props,
			get PublishForm() {
				return Form_default;
			},
			Head: Head_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: $props.title }, null, 8, ["title"]), createBaseVNode("div", { class: normalizeClass({ "max-w-page mx-auto": $props.asConfig }) }, [createVNode($setup["PublishForm"], {
		icon: $props.icon,
		title: $props.title,
		blueprint: $props.blueprint,
		"initial-values": $props.values,
		"initial-meta": $props.meta,
		"submit-url": $props.submitUrl,
		"submit-method": $props.submitMethod,
		"read-only": $props.readOnly,
		"as-config": $props.asConfig
	}, null, 8, [
		"icon",
		"title",
		"blueprint",
		"initial-values",
		"initial-meta",
		"submit-url",
		"submit-method",
		"read-only",
		"as-config"
	])], 2)], 64);
}
var PublishForm_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "PublishForm.vue"]]);
//#endregion
export { PublishForm_default as default };
