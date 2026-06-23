import { B as openBlock, C as createVNode, _ as createBlock, f as Fragment, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Ut as Header_default, li as Button_default, n as DocsCallout_default, r as Item_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import { t as Listing_default } from "./Listing-BeRZIBQe.js";
//#region resources/js/pages/users/Index.vue
var _sfc_main = {
	__name: "Index",
	props: {
		filters: Object,
		sortColumn: String,
		sortDirection: String,
		actionUrl: String,
		createUrl: String,
		editBlueprintUrl: String,
		canCreate: Boolean,
		canConfigureFields: Boolean
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Listing: Listing_default,
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
				return Item_default;
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
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Users") }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__("Users"),
			icon: "users"
		}, {
			default: withCtx(() => [$props.canConfigureFields ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
				key: 0,
				category: "actions",
				text: _ctx.__("Edit User Blueprint"),
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
				text: _ctx.__("Create User"),
				url: $props.createUrl,
				icon: "users"
			}, {
				default: withCtx(({ text, url }) => [createVNode($setup["Button"], {
					text,
					href: url,
					variant: "primary"
				}, null, 8, ["text", "href"])]),
				_: 1
			}, 8, ["text", "url"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["Listing"], {
			"initial-sort-column": $props.sortColumn,
			"initial-sort-direction": $props.sortDirection,
			filters: $props.filters,
			"action-url": $props.actionUrl
		}, null, 8, [
			"initial-sort-column",
			"initial-sort-direction",
			"filters",
			"action-url"
		]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Users"),
			url: "users"
		}, null, 8, ["topic"])
	], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
