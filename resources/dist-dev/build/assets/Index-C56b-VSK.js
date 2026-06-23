import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, f as Fragment, g as createBaseVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, r as Item_default, tn as Item_default$1 } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/roles/Index.vue
var _sfc_main = {
	__name: "Index",
	props: {
		roles: Array,
		columns: Array,
		createUrl: String
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const reloadPage = () => router.reload();
		const __returned__ = {
			reloadPage,
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get CommandPaletteItem() {
				return Item_default;
			},
			get Button() {
				return Button_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get DropdownItem() {
				return Item_default$1;
			},
			get Listing() {
				return Listing_default;
			},
			get Link() {
				return link_default;
			},
			get router() {
				return router;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "max-w-page mx-auto" };
var _hoisted_2 = { class: "font-mono text-xs" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_resource_deleter = resolveComponent("resource-deleter");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Roles & Permissions") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Roles & Permissions"),
			icon: "permissions"
		}, {
			default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
				category: "Actions",
				text: _ctx.__("Create Role"),
				url: $props.createUrl,
				icon: "permissions",
				prioritize: ""
			}, {
				default: withCtx(({ text, url }) => [createVNode($setup["Button"], {
					text,
					href: url,
					variant: "primary"
				}, null, 8, ["text", "href"])]),
				_: 1
			}, 8, ["text", "url"])]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["Listing"], {
			items: $props.roles,
			columns: $props.columns,
			"allow-search": false,
			"allow-customizing-columns": false,
			onRefreshing: $setup.reloadPage
		}, {
			"cell-title": withCtx(({ row: role, index }) => [createVNode($setup["Link"], { href: role.edit_url }, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__(role.title)), 1)]),
				_: 2
			}, 1032, ["href"]), createVNode(_component_resource_deleter, {
				ref: `deleter_${role.id}`,
				resource: role,
				"requires-elevated-session": "",
				reload: ""
			}, null, 8, ["resource"])]),
			"cell-handle": withCtx(({ value: handle }) => [createBaseVNode("span", _hoisted_2, toDisplayString(handle), 1)]),
			"prepended-row-actions": withCtx(({ row: role }) => [createVNode($setup["DropdownItem"], {
				text: _ctx.__("Configure"),
				icon: "cog",
				href: role.edit_url
			}, null, 8, ["text", "href"]), createVNode($setup["DropdownItem"], {
				text: _ctx.__("Delete"),
				icon: "trash",
				variant: "destructive",
				onClick: ($event) => _ctx.$refs[`deleter_${role.id}`].confirm()
			}, null, 8, ["text", "onClick"])]),
			_: 1
		}, 8, ["items", "columns"]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Roles & Permissions"),
			url: "users#permissions"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
