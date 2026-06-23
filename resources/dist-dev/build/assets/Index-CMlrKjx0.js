import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default } from "./index.esm-B4SStoAz.js";
import { Ci as Icon_default, Kt as Menu_default, Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, qt as Item_default$1, r as Item_default, tn as Item_default$2 } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/taxonomies/Index.vue
var _sfc_main = {
	__name: "Index",
	props: [
		"taxonomies",
		"columns",
		"canCreate",
		"createUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		if (props.taxonomies.length === 0) useArchitecturalBackground();
		const __returned__ = {
			props,
			ref,
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get CommandPaletteItem() {
				return Item_default;
			},
			get Icon() {
				return Icon_default;
			},
			get EmptyStateMenu() {
				return Menu_default;
			},
			get EmptyStateItem() {
				return Item_default$1;
			},
			get Listing() {
				return Listing_default;
			},
			get DropdownItem() {
				return Item_default$2;
			},
			get Link() {
				return link_default;
			},
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
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
var _hoisted_2 = { class: "py-8 pt-16 text-center" };
var _hoisted_3 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_resource_deleter = resolveComponent("resource-deleter");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Taxonomies") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		$props.taxonomies.length ? (openBlock(), createBlock($setup["Header"], {
			key: 0,
			title: _ctx.__("Taxonomies"),
			icon: "taxonomies"
		}, {
			default: withCtx(() => [$props.canCreate ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
				key: 0,
				category: "Actions",
				prioritize: "",
				text: _ctx.__("Create Taxonomy"),
				url: $props.createUrl,
				icon: "taxonomies"
			}, {
				default: withCtx(({ text, url }) => [createVNode($setup["Button"], {
					text,
					href: url,
					variant: "primary"
				}, null, 8, ["text", "href"])]),
				_: 1
			}, 8, ["text", "url"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"])) : createCommentVNode("", true),
		$props.taxonomies.length ? (openBlock(), createBlock($setup["Listing"], {
			key: 1,
			items: $props.taxonomies,
			columns: $props.columns,
			"allow-search": false,
			"allow-customizing-columns": false
		}, {
			"cell-title": withCtx(({ row: taxonomy }) => [createVNode($setup["Link"], { href: taxonomy.terms_url }, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__(taxonomy.title)), 1)]),
				_: 2
			}, 1032, ["href"]), createVNode(_component_resource_deleter, {
				ref: `deleter_${taxonomy.id}`,
				resource: taxonomy,
				reload: ""
			}, null, 8, ["resource"])]),
			"prepended-row-actions": withCtx(({ row: taxonomy, index }) => [
				createVNode($setup["DropdownItem"], {
					text: _ctx.__("Configure"),
					icon: "cog",
					href: taxonomy.edit_url
				}, null, 8, ["text", "href"]),
				createVNode($setup["DropdownItem"], {
					text: _ctx.__("Edit Blueprints"),
					icon: "blueprint-edit",
					href: taxonomy.blueprints_url
				}, null, 8, ["text", "href"]),
				createVNode($setup["DropdownItem"], {
					text: _ctx.__("Delete Taxonomy"),
					icon: "trash",
					variant: "destructive",
					onClick: ($event) => _ctx.$refs[`deleter_${taxonomy.id}`].confirm()
				}, null, 8, ["text", "onClick"])
			]),
			_: 1
		}, 8, ["items", "columns"])) : (openBlock(), createElementBlock(Fragment, { key: 2 }, [createBaseVNode("header", _hoisted_2, [createBaseVNode("h1", _hoisted_3, [createVNode($setup["Icon"], {
			name: "taxonomies",
			class: "size-5 text-gray-500"
		}), createTextVNode(" " + toDisplayString(_ctx.__("Taxonomies")), 1)])]), createVNode($setup["EmptyStateMenu"], { heading: _ctx.__("statamic::messages.taxonomy_configure_intro") }, {
			default: withCtx(() => [createVNode($setup["EmptyStateItem"], {
				href: $props.createUrl,
				icon: "taxonomies",
				heading: _ctx.__("Create Taxonomy"),
				description: _ctx.__("Get started by creating your first taxonomy.")
			}, null, 8, [
				"href",
				"heading",
				"description"
			])]),
			_: 1
		}, 8, ["heading"])], 64)),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Taxonomies"),
			url: "taxonomies"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
