import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { Ci as Icon_default, Kt as Menu_default, Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, qt as Item_default, r as Item_default$1, tn as Item_default$2 } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/user-groups/Index.vue
var _sfc_main = {
	__name: "Index",
	props: {
		groups: Array,
		createUrl: String,
		editBlueprintUrl: String,
		canCreate: Boolean,
		canConfigureFields: Boolean
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		if (props.groups.length === 0) useArchitecturalBackground();
		const columns = ref([
			{
				label: __("Title"),
				field: "title"
			},
			{
				label: __("Handle"),
				field: "handle"
			},
			{
				label: __("Users"),
				field: "users"
			},
			{
				label: __("Roles"),
				field: "roles"
			}
		]);
		const reloadPage = () => router.reload();
		const __returned__ = {
			props,
			columns,
			reloadPage,
			get Icon() {
				return Icon_default;
			},
			get EmptyStateMenu() {
				return Menu_default;
			},
			get EmptyStateItem() {
				return Item_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get CommandPaletteItem() {
				return Item_default$1;
			},
			get DropdownItem() {
				return Item_default$2;
			},
			get Listing() {
				return Listing_default;
			},
			get useArchitecturalBackground() {
				return useArchitecturalBackground;
			},
			Head: Head_default,
			get Link() {
				return link_default;
			},
			get router() {
				return router;
			},
			ref
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
var _hoisted_3 = { class: "py-8 mt-8 text-center starting-style-transition" };
var _hoisted_4 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_resource_deleter = resolveComponent("resource-deleter");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("User Groups") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [$props.groups.length ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createVNode($setup["Header"], {
		title: _ctx.__("User Groups"),
		icon: "groups"
	}, {
		default: withCtx(() => [$props.canConfigureFields ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
			key: 0,
			category: "actions",
			text: _ctx.__("Edit User Group Blueprint"),
			url: $props.editBlueprintUrl,
			icon: "blueprint-edit"
		}, {
			default: withCtx(({ text, url }) => [createVNode($setup["Button"], {
				text,
				href: url
			}, null, 8, ["text", "href"])]),
			_: 1
		}, 8, ["text", "url"])) : createCommentVNode("", true), $props.canCreate ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
			key: 1,
			category: "actions",
			prioritize: "",
			text: _ctx.__("Create User Group"),
			url: $props.createUrl,
			icon: "groups"
		}, {
			default: withCtx(({ text, url }) => [createVNode($setup["Button"], {
				text,
				href: url,
				variant: "primary"
			}, null, 8, ["text", "href"])]),
			_: 1
		}, 8, ["text", "url"])) : createCommentVNode("", true)]),
		_: 1
	}, 8, ["title"]), createVNode($setup["Listing"], {
		items: $props.groups,
		columns: $setup.columns,
		"allow-search": false,
		"allow-customizing-columns": false,
		onRefreshing: $setup.reloadPage
	}, {
		"cell-title": withCtx(({ row: group }) => [createVNode($setup["Link"], { href: group.show_url }, {
			default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__(group.title)), 1)]),
			_: 2
		}, 1032, ["href"]), createVNode(_component_resource_deleter, {
			ref: `deleter_${group.id}`,
			resource: group,
			reload: ""
		}, null, 8, ["resource"])]),
		"cell-handle": withCtx(({ value: handle }) => [createBaseVNode("span", _hoisted_2, toDisplayString(handle), 1)]),
		"prepended-row-actions": withCtx(({ row: group }) => [
			createVNode($setup["DropdownItem"], {
				text: _ctx.__("View"),
				icon: "eye",
				href: group.show_url
			}, null, 8, ["text", "href"]),
			createVNode($setup["DropdownItem"], {
				text: _ctx.__("Configure"),
				icon: "cog",
				href: group.edit_url
			}, null, 8, ["text", "href"]),
			createVNode($setup["DropdownItem"], {
				text: _ctx.__("Delete"),
				icon: "trash",
				variant: "destructive",
				onClick: ($event) => _ctx.$refs[`deleter_${group.id}`].confirm()
			}, null, 8, ["text", "onClick"])
		]),
		_: 1
	}, 8, ["items", "columns"])], 64)) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [createBaseVNode("header", _hoisted_3, [createBaseVNode("h1", _hoisted_4, [createVNode($setup["Icon"], {
		name: "groups",
		class: "size-5 text-gray-500"
	}), createTextVNode(" " + toDisplayString(_ctx.__("User Groups")), 1)])]), createVNode($setup["EmptyStateMenu"], { heading: _ctx.__("statamic::messages.user_groups_intro") }, {
		default: withCtx(() => [createVNode($setup["EmptyStateItem"], {
			href: $props.createUrl,
			icon: "groups",
			heading: _ctx.__("Create User Group"),
			description: _ctx.__("Get started by creating your first user group.")
		}, null, 8, [
			"href",
			"heading",
			"description"
		])]),
		_: 1
	}, 8, ["heading"])], 64)), createVNode($setup["DocsCallout"], {
		topic: _ctx.__("User Groups"),
		url: "users#user-groups"
	}, null, 8, ["topic"])])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
