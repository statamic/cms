import { At as toDisplayString, B as openBlock, C as createVNode, L as onMounted, S as createTextVNode, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, r as Item_default, tn as Item_default$1, ui as Badge_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/addons/Index.vue
var _sfc_main = {
	__name: "Index",
	props: {
		addons: Array,
		columns: Array
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const rows = ref(props.addons);
		onMounted(() => {
			props.addons.forEach((addon) => {
				if (addon.marketplace_url) Statamic.$commandPalette.add({
					category: Statamic.$commandPalette.category.Actions,
					text: [__("Browse the Marketplace"), addon.name],
					icon: "external-link",
					url: addon.marketplace_url,
					openNewTab: true
				});
			});
		});
		const __returned__ = {
			props,
			rows,
			ref,
			onMounted,
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
			get Listing() {
				return Listing_default;
			},
			get Badge() {
				return Badge_default;
			},
			get DropdownItem() {
				return Item_default$1;
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
var _hoisted_2 = ["href"];
var _hoisted_3 = { key: 1 };
var _hoisted_4 = { class: "font-mono text-xs" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Addons") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Addons"),
			icon: "addons"
		}, {
			default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
				category: "Actions",
				text: _ctx.__("Browse the Marketplace"),
				icon: "external-link",
				url: "https://statamic.com/addons",
				"open-new-tab": "",
				prioritize: ""
			}, {
				default: withCtx(({ text, url, icon }) => [createVNode($setup["Button"], {
					variant: "primary",
					text,
					href: url,
					icon,
					target: "_blank"
				}, null, 8, [
					"text",
					"href",
					"icon"
				])]),
				_: 1
			}, 8, ["text"])]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["Listing"], {
			items: $setup.rows,
			columns: $props.columns,
			"allow-search": false,
			"allow-customizing-columns": false
		}, {
			"cell-name": withCtx(({ row: addon }) => [addon.marketplace_url ? (openBlock(), createElementBlock("a", {
				key: 0,
				href: addon.marketplace_url,
				target: "_blank"
			}, toDisplayString(_ctx.__(addon.name)), 9, _hoisted_2)) : (openBlock(), createElementBlock("span", _hoisted_3, [createTextVNode(toDisplayString(_ctx.__(addon.name)) + " ", 1), createVNode($setup["Badge"], {
				class: "ml-1",
				size: "sm",
				text: _ctx.__("Unlisted")
			}, null, 8, ["text"])]))]),
			"cell-version": withCtx(({ value: handle }) => [createBaseVNode("span", _hoisted_4, toDisplayString(handle), 1)]),
			"prepended-row-actions": withCtx(({ row: addon }) => [
				addon.marketplace_url ? (openBlock(), createBlock($setup["DropdownItem"], {
					key: 0,
					text: _ctx.__("View on the Marketplace"),
					icon: "external-link",
					href: addon.marketplace_url,
					target: "_blank"
				}, null, 8, ["text", "href"])) : createCommentVNode("", true),
				addon.updates_url ? (openBlock(), createBlock($setup["DropdownItem"], {
					key: 1,
					text: _ctx.__("Release Notes"),
					icon: "updates",
					href: addon.updates_url
				}, null, 8, ["text", "href"])) : createCommentVNode("", true),
				addon.settings_url ? (openBlock(), createBlock($setup["DropdownItem"], {
					key: 2,
					text: _ctx.__("Settings"),
					icon: "cog",
					href: addon.settings_url
				}, null, 8, ["text", "href"])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["items", "columns"]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Addons"),
			url: "addons"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
