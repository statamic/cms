import { B as openBlock, C as createVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { r as Head_default, x as PublishForm_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/roles/Edit.vue
var _sfc_main = {
	__name: "Edit",
	props: {
		role: Object,
		super: Boolean,
		permissions: Array,
		canAssignSuper: Boolean,
		action: String
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			RolePublishForm: PublishForm_default,
			Head: Head_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["Head"], { title: _ctx.__("Configure Role") }, null, 8, ["title"]), createVNode($setup["RolePublishForm"], {
		action: $props.action,
		method: "patch",
		"can-assign-super": $props.canAssignSuper,
		"initial-title": $props.role.title,
		"initial-handle": $props.role.handle,
		"initial-super": $props.super,
		"initial-permissions": $props.permissions
	}, null, 8, [
		"action",
		"can-assign-super",
		"initial-title",
		"initial-handle",
		"initial-super",
		"initial-permissions"
	])]);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
