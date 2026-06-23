import { B as openBlock, C as createVNode, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { S as Wizard_default, n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/users/Create.vue
var _sfc_main = {
	__name: "Create",
	props: [
		"route",
		"usersIndexUrl",
		"usersCreateUrl",
		"canCreateSupers",
		"canAssignRoles",
		"canAssignGroups",
		"activationExpiry",
		"separateNameFields",
		"canSendInvitation",
		"blueprint",
		"fields",
		"meta",
		"initialValues"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		useArchitecturalBackground();
		const __returned__ = {
			UserWizard: Wizard_default,
			Head: Head_default,
			get useArchitecturalBackground() {
				return useArchitecturalBackground;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Create User") }, null, 8, ["title"]), createVNode($setup["UserWizard"], {
		route: $props.route,
		"users-index-url": $props.usersIndexUrl,
		"users-create-url": $props.usersCreateUrl,
		"can-create-supers": $props.canCreateSupers,
		"can-assign-roles": $props.canAssignRoles,
		"can-assign-groups": $props.canAssignGroups,
		"activation-expiry": $props.activationExpiry,
		"separate-name-fields": $props.separateNameFields,
		"can-send-invitation": $props.canSendInvitation,
		blueprint: $props.blueprint,
		fields: $props.fields,
		meta: $props.meta,
		"initial-values": $props.initialValues
	}, null, 8, [
		"route",
		"users-index-url",
		"users-create-url",
		"can-create-supers",
		"can-assign-roles",
		"can-assign-groups",
		"activation-expiry",
		"separate-name-fields",
		"can-send-invitation",
		"blueprint",
		"fields",
		"meta",
		"initial-values"
	])], 64);
}
var Create_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Create.vue"]]);
//#endregion
export { Create_default as default };
