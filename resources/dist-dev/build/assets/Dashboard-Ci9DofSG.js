import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, J as resolveDynamicComponent, K as resolveComponent, M as mergeProps, S as createTextVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Ci as Icon_default, Kt as Menu_default, n as DocsCallout_default, qt as Item_default } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default, y as DynamicHtmlRenderer_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/Dashboard.vue
var _sfc_main = {
	__name: "Dashboard",
	props: {
		widgets: Array,
		pro: Boolean,
		blueprintsUrl: String,
		collectionsCreateUrl: String,
		navigationCreateUrl: String
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		if (props.widgets.length === 0) useArchitecturalBackground();
		function classes(widget) {
			return `${widget.classes} ${tailwindWidthClass(widget.width)}`;
		}
		function tailwindWidthClass(width) {
			const sizes = {
				sm: "w-full @2xl:w-1/2 @4xl:w-1/3 @7xl:w-1/4",
				md: "w-full @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3",
				lg: "w-full @2xl:w-full @4xl:w-2/3 @7xl:w-3/4",
				full: "w-full"
			};
			return sizes[typeof width === "number" ? {
				25: "sm",
				33: "sm",
				50: "md",
				66: "md",
				75: "lg",
				100: "full"
			}[width] ?? "full" : width] ?? sizes.md;
		}
		const __returned__ = {
			props,
			classes,
			tailwindWidthClass,
			Head: Head_default,
			DynamicHtmlRenderer: DynamicHtmlRenderer_default,
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
var _hoisted_1 = { class: "widgets @container/widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3" };
var _hoisted_2 = { class: "py-8 pt-16 text-center" };
var _hoisted_3 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_header = resolveComponent("ui-header");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Dashboard") }, null, 8, ["title"]),
		$props.widgets.length ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createVNode(_component_ui_header, {
			title: _ctx.__("Dashboard"),
			icon: "dashboard"
		}, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.widgets, (widget) => {
			return openBlock(), createElementBlock("div", { class: normalizeClass(["px-3 starting-style-transition", $setup.classes(widget)]) }, [widget.component ? (openBlock(), createBlock(resolveDynamicComponent(widget.component.name), mergeProps({
				key: 0,
				ref_for: true
			}, widget.component.props), null, 16)) : (openBlock(), createBlock($setup["DynamicHtmlRenderer"], {
				key: 1,
				html: widget.html
			}, null, 8, ["html"]))], 2);
		}), 256))])], 64)) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [createBaseVNode("header", _hoisted_2, [createBaseVNode("h1", _hoisted_3, [createVNode($setup["Icon"], {
			name: "dashboard",
			class: "size-5 text-gray-500"
		}), createTextVNode(" " + toDisplayString(_ctx.__("Dashboard")), 1)])]), createVNode($setup["EmptyStateMenu"], {
			heading: _ctx.__("statamic::messages.getting_started_widget_header"),
			subheading: _ctx.__("statamic::messages.getting_started_widget_intro")
		}, {
			default: withCtx(() => [
				createVNode($setup["EmptyStateItem"], {
					href: "https://statamic.dev",
					icon: "docs",
					heading: _ctx.__("Read the Documentation"),
					description: _ctx.__("statamic::messages.getting_started_widget_docs")
				}, null, 8, ["heading", "description"]),
				!$props.pro ? (openBlock(), createBlock($setup["EmptyStateItem"], {
					key: 0,
					href: "https://statamic.dev/licensing",
					icon: "pro-ribbon",
					heading: _ctx.__("Enable Pro Mode"),
					description: _ctx.__("statamic::messages.getting_started_widget_pro")
				}, null, 8, ["heading", "description"])) : createCommentVNode("", true),
				createVNode($setup["EmptyStateItem"], {
					href: $props.blueprintsUrl,
					icon: "blueprints",
					heading: _ctx.__("Create a Blueprint"),
					description: _ctx.__("statamic::messages.blueprints_intro")
				}, null, 8, [
					"href",
					"heading",
					"description"
				]),
				createVNode($setup["EmptyStateItem"], {
					href: $props.collectionsCreateUrl,
					icon: "collections",
					heading: _ctx.__("Create a Collection"),
					description: _ctx.__("statamic::messages.getting_started_widget_collections")
				}, null, 8, [
					"href",
					"heading",
					"description"
				]),
				createVNode($setup["EmptyStateItem"], {
					href: $props.navigationCreateUrl,
					icon: "navigation",
					heading: _ctx.__("Create a Navigation"),
					description: _ctx.__("statamic::messages.getting_started_widget_navigation")
				}, null, 8, [
					"href",
					"heading",
					"description"
				])
			]),
			_: 1
		}, 8, ["heading", "subheading"])], 64)),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Widgets"),
			url: "widgets"
		}, null, 8, ["topic"])
	], 64);
}
var Dashboard_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Dashboard.vue"]]);
//#endregion
export { Dashboard_default as default };
