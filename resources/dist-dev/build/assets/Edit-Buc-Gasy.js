import { B as openBlock, C as createVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { k as PublishForm_default, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/users/Edit.vue
var _sfc_main = {
	__name: "Edit",
	props: [
		"actions",
		"title",
		"reference",
		"blueprint",
		"values",
		"meta",
		"canEditPassword",
		"canEditBlueprint",
		"requiresCurrentPassword",
		"itemActions",
		"itemActionUrl",
		"twoFactor"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			UserPublishForm: PublishForm_default,
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
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["Head"], { title: _ctx.__("Edit User") }, null, 8, ["title"]), createVNode($setup["UserPublishForm"], {
		actions: $props.actions,
		method: "patch",
		"publish-container": "base",
		"initial-title": $props.title,
		"initial-reference": $props.reference,
		"initial-fieldset": $props.blueprint,
		"initial-values": $props.values,
		"initial-meta": $props.meta,
		"can-edit-password": $props.canEditPassword,
		"can-edit-blueprint": $props.canEditBlueprint,
		"requires-current-password": $props.requiresCurrentPassword,
		"initial-item-actions": $props.itemActions,
		"item-action-url": $props.itemActionUrl,
		"two-factor": $props.twoFactor
	}, null, 8, [
		"actions",
		"initial-title",
		"initial-reference",
		"initial-fieldset",
		"initial-values",
		"initial-meta",
		"can-edit-password",
		"can-edit-blueprint",
		"requires-current-password",
		"initial-item-actions",
		"item-action-url",
		"two-factor"
	])]);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
