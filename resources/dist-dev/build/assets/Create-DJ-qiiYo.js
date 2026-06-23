import { B as openBlock, C as createVNode, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { r as Head_default, x as PublishForm_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/roles/Create.vue
var _sfc_main = {
	__name: "Create",
	props: {
		permissions: Array,
		canAssignSuper: Boolean,
		action: String,
		indexUrl: String
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
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Create Role") }, null, 8, ["title"]), createVNode($setup["RolePublishForm"], {
		action: $props.action,
		method: "post",
		"can-assign-super": $props.canAssignSuper,
		"initial-permissions": $props.permissions,
		"index-url": $props.indexUrl
	}, null, 8, [
		"action",
		"can-assign-super",
		"initial-permissions",
		"index-url"
	])], 64);
}
var Create_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Create.vue"]]);
//#endregion
export { Create_default as default };
