import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, _ as createBlock, at as withDirectives, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, q as resolveDirective, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { Ci as Icon_default, Kt as Menu_default, Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, qt as Item_default$1, r as Item_default, tn as Item_default$2 } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/globals/Index.vue
var _sfc_main = {
	__name: "Index",
	props: {
		globals: Array,
		columns: Array,
		createUrl: String,
		canCreate: Boolean,
		actionUrl: String
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		if (props.globals.length === 0) useArchitecturalBackground();
		const __returned__ = {
			props,
			actionContext: computed(() => {
				return { site: Statamic.$config.get("selectedSite") };
			}),
			Head: Head_default,
			get Link() {
				return link_default;
			},
			get router() {
				return router;
			},
			get Header() {
				return Header_default;
			},
			get CommandPaletteItem() {
				return Item_default;
			},
			get Button() {
				return Button_default;
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
			get DocsCallout() {
				return DocsCallout_default;
			},
			get Listing() {
				return Listing_default;
			},
			get DropdownItem() {
				return Item_default$2;
			},
			get useArchitecturalBackground() {
				return useArchitecturalBackground;
			},
			computed
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
var _hoisted_2 = { class: "font-mono text-2xs" };
var _hoisted_3 = { class: "py-8 pt-16 text-center" };
var _hoisted_4 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Global Sets") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		$props.globals.length ? (openBlock(), createBlock($setup["Header"], {
			key: 0,
			title: _ctx.__("Globals"),
			icon: "globals"
		}, {
			default: withCtx(() => [$props.canCreate ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
				key: 0,
				category: "Actions",
				prioritize: "",
				text: _ctx.__("Create Global Set"),
				url: $props.createUrl,
				icon: "globals"
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
		$props.globals.length ? (openBlock(), createBlock($setup["Listing"], {
			key: 1,
			items: $props.globals,
			columns: $props.columns,
			"action-url": $props.actionUrl,
			"action-context": $setup.actionContext,
			"allow-search": false,
			"allow-customizing-columns": false,
			onRefreshing: _cache[0] || (_cache[0] = () => $setup.router.reload())
		}, {
			"cell-title": withCtx(({ row: global }) => [withDirectives((openBlock(), createBlock($setup["Link"], { href: global.edit_url }, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__(global.title)), 1)]),
				_: 2
			}, 1032, ["href"])), [[_directive_tooltip, global.handle]])]),
			"cell-handle": withCtx(({ row: global }) => [createBaseVNode("span", _hoisted_2, toDisplayString(global.handle), 1)]),
			"prepended-row-actions": withCtx(({ row: global }) => [createVNode($setup["DropdownItem"], {
				text: _ctx.__("Edit"),
				icon: "edit",
				href: global.edit_url
			}, null, 8, ["text", "href"]), global.configurable ? (openBlock(), createBlock($setup["DropdownItem"], {
				key: 0,
				text: _ctx.__("Configure"),
				icon: "cog",
				href: global.configure_url
			}, null, 8, ["text", "href"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, [
			"items",
			"columns",
			"action-url",
			"action-context"
		])) : (openBlock(), createElementBlock(Fragment, { key: 2 }, [createBaseVNode("header", _hoisted_3, [createBaseVNode("h1", _hoisted_4, [createVNode($setup["Icon"], {
			name: "globals",
			class: "size-5 text-gray-500"
		}), createTextVNode(" " + toDisplayString(_ctx.__("Globals")), 1)])]), createVNode($setup["EmptyStateMenu"], { heading: _ctx.__("statamic::messages.global_set_config_intro") }, {
			default: withCtx(() => [$props.canCreate ? (openBlock(), createBlock($setup["EmptyStateItem"], {
				key: 0,
				href: $props.createUrl,
				icon: "globals",
				heading: _ctx.__("Create Global Set"),
				description: _ctx.__("statamic::messages.global_next_steps_create_description")
			}, null, 8, [
				"href",
				"heading",
				"description"
			])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["heading"])], 64)),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Global Variables"),
			url: "globals"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
