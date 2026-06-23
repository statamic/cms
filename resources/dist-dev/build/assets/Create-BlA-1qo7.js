import { B as openBlock, C as createVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { r as head_default } from "./index.esm-B4SStoAz.js";
import { b as PublishForm_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/user-groups/Create.vue
var _sfc_main = {
	__name: "Create",
	props: {
		title: {
			type: String,
			required: true
		},
		actions: {
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
		blueprint: {
			type: Object,
			required: true
		},
		canEditBlueprint: {
			type: Boolean,
			default: false
		},
		isCreating: {
			type: Boolean,
			default: false
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			props: __props,
			get Head() {
				return head_default;
			},
			PublishForm: PublishForm_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", null, [createVNode($setup["Head"], { title: $props.title }, null, 8, ["title"]), createVNode($setup["PublishForm"], {
		"publish-container": "base",
		"initial-title": $props.title,
		"initial-fieldset": $props.blueprint,
		"initial-values": $props.values,
		"initial-meta": $props.meta,
		actions: $props.actions,
		method: "post",
		"can-edit-blueprint": $props.canEditBlueprint,
		"is-creating": $props.isCreating
	}, null, 8, [
		"initial-title",
		"initial-fieldset",
		"initial-values",
		"initial-meta",
		"actions",
		"can-edit-blueprint",
		"is-creating"
	])]);
}
var Create_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Create.vue"]]);
//#endregion
export { Create_default as default };
