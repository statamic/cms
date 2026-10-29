import { D as defineComponent, H as openBlock, K as renderList, Mt as toDisplayString, T as createVNode, b as createCommentVNode, kt as normalizeClass, nt as watch, ot as withCtx, p as Fragment, v as createBaseVNode, w as createTextVNode, x as createElementBlock, y as createBlock, yt as ref } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { a as link_default } from "./index.esm-D1UivzAX.js";
import { $ as Item_default, Ut as Header_default, cr as Card_default, di as Badge_default, et as Group_default, i as Listing_default, ir as Panel_default, n as DocsCallout_default, oi as Button_default, or as Header_default$1, s as Table_default, sr as Heading_default, wi as Icon_default } from "./ui-BUy1lrUe.js";
import { t as __ } from "./globals-D3CqQKLv.js";
import { b as preferences } from "./api-BzIpSBRz.js";
import { c as Layout_default, r as Head_default } from "./index-BV-PTyMc.js";
import { t as FormStatusIndicator_default } from "./FormStatusIndicator-DJYX_3TF.js";
import { t as Layout_default$1 } from "./Layout-Ci96BcaH.js";
import { t as PanelLayout_default } from "./PanelLayout-Bo-zlPsM.js";
// resources/js/pages/forms/connect/Index.vue?vue&type=script&setup=true&lang.ts
var View = /* @__PURE__ */ function(View) {
	View["Grid"] = "grid";
	View["List"] = "list";
	return View;
}(View || {});
var Index_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	layout: [
		Layout_default,
		PanelLayout_default,
		Layout_default$1
	],
	__name: "Index",
	props: {
		form: {},
		connectors: {}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const view = ref(preferences.get("forms.connect.view", "grid"));
		watch(view, (view) => preferences.set("forms.connect.view", view));
		const __returned__ = {
			View,
			view,
			Head: Head_default,
			get Badge() {
				return Badge_default;
			},
			get Button() {
				return Button_default;
			},
			get Card() {
				return Card_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get Header() {
				return Header_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Panel() {
				return Panel_default;
			},
			get PanelHeader() {
				return Header_default$1;
			},
			get ToggleGroup() {
				return Group_default;
			},
			get ToggleItem() {
				return Item_default;
			},
			get Listing() {
				return Listing_default;
			},
			get ListingTable() {
				return Table_default;
			},
			FormStatusIndicator: FormStatusIndicator_default,
			get Link() {
				return link_default;
			},
			get __() {
				return __;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/pages/forms/connect/Index.vue
var _hoisted_1 = { class: "mx-auto max-w-5xl" };
var _hoisted_2 = { class: "flex items-center gap-2 sm:gap-3" };
var _hoisted_3 = { class: "inline-flex items-center gap-2" };
var _hoisted_4 = {
	key: 0,
	class: "grid gap-4 grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6"
};
var _hoisted_5 = ["innerHTML"];
var _hoisted_6 = { class: "flex items-center justify-center gap-1.5 text-gray-800 dark:text-gray-200" };
var _hoisted_7 = { class: "truncate text-xs" };
var _hoisted_8 = { key: 1 };
var _hoisted_9 = ["innerHTML"];
var _hoisted_10 = { class: "truncate text-sm text-gray-800 dark:text-gray-200" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: [
		$setup.__("Connect"),
		$setup.__($props.form.title),
		$setup.__("Forms")
	] }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], { class: "mb-2" }, {
			title: withCtx(() => [createVNode($setup["FormStatusIndicator"], { status: $props.form.status }, null, 8, ["status"]), createTextVNode(" " + toDisplayString($setup.__($props.form.title)), 1)]),
			actions: withCtx(() => [createBaseVNode("div", _hoisted_2, [createVNode($setup["Button"], {
				variant: "primary",
				href: "https://statamic.com/addons?category=forms",
				target: "_blank",
				rel: "noopener",
				"icon-append": "external-link"
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString($setup.__("Explore Integrations")), 1)]),
				_: 1
			}), createVNode($setup["ToggleGroup"], {
				modelValue: $setup.view,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.view = $event)
			}, {
				default: withCtx(() => [createVNode($setup["ToggleItem"], {
					value: $setup.View.Grid,
					icon: "layout-grid"
				}, null, 8, ["value"]), createVNode($setup["ToggleItem"], {
					value: $setup.View.List,
					icon: "layout-list"
				}, null, 8, ["value"])]),
				_: 1
			}, 8, ["modelValue"])])]),
			_: 1
		}),
		createVNode($setup["Panel"], null, {
			default: withCtx(() => [createVNode($setup["PanelHeader"], null, {
				default: withCtx(() => [createVNode($setup["Heading"], null, {
					default: withCtx(() => [createBaseVNode("span", _hoisted_3, [createVNode($setup["Icon"], {
						name: "connection",
						class: "size-4! opacity-60! text-gray-925 dark:text-white",
						"aria-hidden": "true"
					}), createTextVNode(" " + toDisplayString($setup.__("Connect")), 1)])]),
					_: 1
				})]),
				_: 1
			}), createVNode($setup["Card"], { class: normalizeClass({ "p-0!": $setup.view === $setup.View.List }) }, {
				default: withCtx(() => [$setup.view === $setup.View.Grid ? (openBlock(), createElementBlock("div", _hoisted_4, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.connectors, (connector) => {
					return openBlock(), createElementBlock("div", {
						key: connector.handle,
						class: "space-y-2"
					}, [createVNode($setup["Link"], {
						href: connector.url,
						"aria-label": $setup.__(connector.title),
						class: "relative flex mb-2 aspect-square items-center justify-center rounded-lg border border-gray-300 bg-gray-50/30 p-8 text-gray-700 hover:bg-gray-100/50 dark:border-gray-700 dark:bg-gray-950/40 dark:text-gray-300 dark:hover:bg-gray-900"
					}, {
						default: withCtx(() => [createBaseVNode("span", {
							class: "[&_svg]:size-12",
							"aria-hidden": "true",
							innerHTML: connector.icon
						}, null, 8, _hoisted_5)]),
						_: 2
					}, 1032, ["href", "aria-label"]), createBaseVNode("div", _hoisted_6, [connector.count ? (openBlock(), createBlock($setup["Badge"], {
						key: 0,
						size: "sm",
						color: "white",
						pill: ""
					}, {
						default: withCtx(() => [createTextVNode(toDisplayString(connector.count), 1)]),
						_: 2
					}, 1024)) : createCommentVNode("", true), createBaseVNode("span", _hoisted_7, toDisplayString($setup.__(connector.title)), 1)])]);
				}), 128))])) : (openBlock(), createElementBlock("div", _hoisted_8, [createVNode($setup["Listing"], {
					id: "connectors-listing",
					class: "pt-1",
					items: $props.connectors,
					columns: [
						{
							field: "title",
							label: $setup.__("Connector"),
							sortable: true,
							visible: true
						},
						{
							field: "description",
							label: $setup.__("Description"),
							sortable: false,
							visible: true
						},
						{
							field: "developer",
							label: $setup.__("Developer"),
							sortable: true,
							visible: true
						}
					],
					searchable: false,
					"allow-customizing-columns": false
				}, {
					default: withCtx(() => [createVNode($setup["ListingTable"], null, {
						"cell-title": withCtx(({ row: connector }) => [createVNode($setup["Link"], {
							href: connector.url,
							class: "flex min-w-0 items-center gap-2"
						}, {
							default: withCtx(() => [
								createBaseVNode("span", {
									class: "size-7 flex items-center justify-center text-gray-700 dark:text-gray-300 [&_svg]:size-5",
									"aria-hidden": "true",
									innerHTML: connector.icon
								}, null, 8, _hoisted_9),
								connector.count ? (openBlock(), createBlock($setup["Badge"], {
									key: 0,
									size: "sm",
									color: "white",
									pill: ""
								}, {
									default: withCtx(() => [createTextVNode(toDisplayString(connector.count), 1)]),
									_: 2
								}, 1024)) : createCommentVNode("", true),
								createBaseVNode("span", _hoisted_10, toDisplayString($setup.__(connector.title)), 1)
							]),
							_: 2
						}, 1032, ["href"])]),
						_: 1
					})]),
					_: 1
				}, 8, ["items", "columns"])]))]),
				_: 1
			}, 8, ["class"])]),
			_: 1
		}),
		createVNode($setup["DocsCallout"], {
			topic: $setup.__("Connections"),
			url: "forms"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(Index_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "Index.vue"]]);
export { Index_default as default };
