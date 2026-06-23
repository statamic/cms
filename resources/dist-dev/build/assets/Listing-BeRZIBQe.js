import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default } from "./index.esm-B4SStoAz.js";
import { i as Listing_default$1, tn as Item_default } from "./ui-BfR7XN6t.js";
//#region resources/js/components/users/Listing.vue
var _sfc_main = {
	components: {
		Link: link_default,
		Listing: Listing_default$1,
		DropdownItem: Item_default
	},
	props: {
		group: String,
		allowFilterPresets: { default: true },
		actionUrl: {
			type: String,
			default: null
		},
		filters: {
			type: Array,
			default: () => []
		},
		initialSortColumn: {
			type: String,
			default: "email"
		},
		initialSortDirection: {
			type: String,
			default: "asc"
		}
	},
	data() {
		return {
			preferencesPrefix: "users",
			requestUrl: cp_url("users")
		};
	},
	computed: {
		actionContext() {
			return { group: this.group };
		},
		additionalParameters() {
			return { group: this.group };
		}
	}
};
var _hoisted_1 = ["textContent"];
var _hoisted_2 = { class: "role-index-field" };
var _hoisted_3 = {
	key: 0,
	class: "role-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1"
};
var _hoisted_4 = { key: 1 };
var _hoisted_5 = { class: "role-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1" };
var _hoisted_6 = { class: "groups-index-field" };
var _hoisted_7 = { class: "groups-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1" };
var _hoisted_8 = { class: "flex items-center" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_avatar = resolveComponent("ui-avatar");
	const _component_Link = resolveComponent("Link");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_Listing = resolveComponent("Listing", true);
	return openBlock(), createElementBlock("div", null, [createVNode(_component_Listing, {
		url: $data.requestUrl,
		"action-url": $props.actionUrl,
		"action-context": $options.actionContext,
		"preferences-prefix": $data.preferencesPrefix,
		filters: $props.filters,
		"allow-presets": $props.allowFilterPresets,
		"sort-column": $props.initialSortColumn,
		"sort-direction": $props.initialSortDirection,
		"additional-parameters": $options.additionalParameters,
		"push-query": ""
	}, {
		"cell-email": withCtx(({ row: user }) => [createVNode(_component_Link, {
			class: "title-index-field",
			href: user.edit_url,
			onClick: _cache[0] || (_cache[0] = withModifiers(() => {}, ["stop"]))
		}, {
			default: withCtx(() => [createVNode(_component_ui_avatar, {
				user,
				class: "size-8 text-xs ltr:mr-2 rtl:ml-2"
			}, null, 8, ["user"]), createBaseVNode("span", { textContent: toDisplayString(user.email) }, null, 8, _hoisted_1)]),
			_: 2
		}, 1032, ["href"])]),
		"cell-roles": withCtx(({ row: user, value: roles }) => [createBaseVNode("div", _hoisted_2, [
			user.super ? (openBlock(), createElementBlock("div", _hoisted_3, toDisplayString(_ctx.__("Super Admin")), 1)) : createCommentVNode("", true),
			!roles || roles.length === 0 ? (openBlock(), createElementBlock("div", _hoisted_4)) : createCommentVNode("", true),
			(openBlock(true), createElementBlock(Fragment, null, renderList(roles || [], (role, i) => {
				return openBlock(), createElementBlock("div", _hoisted_5, toDisplayString(_ctx.__(role.title)), 1);
			}), 256))
		])]),
		"cell-groups": withCtx(({ row: user, value: groups }) => [createBaseVNode("div", _hoisted_6, [(openBlock(true), createElementBlock(Fragment, null, renderList(groups || [], (group) => {
			return openBlock(), createElementBlock("div", _hoisted_7, toDisplayString(_ctx.__(group.title)), 1);
		}), 256))])]),
		"cell-two_factor": withCtx(({ row: user, value }) => [createBaseVNode("div", _hoisted_8, [value ? (openBlock(), createBlock(_component_ui_icon, {
			key: 0,
			name: "checkmark",
			class: "size-3 text-green-600"
		})) : (openBlock(), createBlock(_component_ui_icon, {
			key: 1,
			name: "x",
			class: "size-3 text-gray-400 dark:text-gray-600"
		}))])]),
		"prepended-row-actions": withCtx(({ row: user }) => [user.editable ? (openBlock(), createBlock(_component_DropdownItem, {
			key: 0,
			text: _ctx.__("Edit"),
			href: user.edit_url,
			icon: "edit"
		}, null, 8, ["text", "href"])) : (openBlock(), createBlock(_component_DropdownItem, {
			key: 1,
			text: _ctx.__("View"),
			href: user.edit_url,
			icon: "eye"
		}, null, 8, ["text", "href"]))]),
		_: 1
	}, 8, [
		"url",
		"action-url",
		"action-context",
		"preferences-prefix",
		"filters",
		"allow-presets",
		"sort-column",
		"sort-direction",
		"additional-parameters"
	])]);
}
var Listing_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Listing.vue"]]);
//#endregion
export { Listing_default as t };
