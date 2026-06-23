import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, W as renderList, _ as createBlock, _t as ref, at as withDirectives, f as Fragment, g as createBaseVNode, it as withCtx, q as resolveDirective, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, Ci as Icon_default, E as StatusIndicator_default, Gn as Panel_default, Kn as Subheading_default, Ut as Header_default, en as Label_default, li as Button_default, n as DocsCallout_default, nn as Dropdown_default, tn as Item_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/blueprints/Index.vue
var _sfc_main = {
	__name: "Index",
	props: [
		"collections",
		"taxonomies",
		"navs",
		"assetContainers",
		"globals",
		"forms",
		"userBlueprint",
		"groupBlueprint",
		"additional"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			resetters: ref({}),
			ref,
			get Link() {
				return link_default;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Dropdown() {
				return Dropdown_default;
			},
			get DropdownMenu() {
				return Menu_default;
			},
			get DropdownLabel() {
				return Label_default;
			},
			get DropdownItem() {
				return Item_default;
			},
			get Button() {
				return Button_default;
			},
			get Subheading() {
				return Subheading_default;
			},
			get Panel() {
				return Panel_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get Icon() {
				return Icon_default;
			},
			get StatusIndicator() {
				return StatusIndicator_default;
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
var _hoisted_2 = { class: "space-y-6 starting-style-transition-children" };
var _hoisted_3 = { class: "data-table" };
var _hoisted_4 = {
	class: "text-end!",
	scope: "col"
};
var _hoisted_5 = { class: "flex items-center gap-2" };
var _hoisted_6 = { class: "text-end" };
var _hoisted_7 = ["textContent"];
var _hoisted_8 = { class: "data-table" };
var _hoisted_9 = { class: "text-end!" };
var _hoisted_10 = { class: "flex items-center gap-2" };
var _hoisted_11 = { class: "text-end" };
var _hoisted_12 = ["textContent"];
var _hoisted_13 = { class: "data-table" };
var _hoisted_14 = { class: "text-start!" };
var _hoisted_15 = { class: "flex items-center gap-2" };
var _hoisted_16 = { class: "data-table" };
var _hoisted_17 = { class: "text-start!" };
var _hoisted_18 = { class: "flex items-center gap-2" };
var _hoisted_19 = { class: "data-table" };
var _hoisted_20 = { class: "text-start!" };
var _hoisted_21 = { class: "flex items-center gap-2" };
var _hoisted_22 = { class: "data-table" };
var _hoisted_23 = { class: "text-start!" };
var _hoisted_24 = { class: "flex items-center gap-2" };
var _hoisted_25 = { class: "data-table" };
var _hoisted_26 = { class: "text-start!" };
var _hoisted_27 = { class: "flex items-center gap-2" };
var _hoisted_28 = { class: "flex items-center gap-2" };
var _hoisted_29 = { class: "data-table" };
var _hoisted_30 = { class: "text-start!" };
var _hoisted_31 = { class: "flex items-center gap-2" };
var _hoisted_32 = { class: "actions-column" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_blueprint_resetter = resolveComponent("blueprint-resetter");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Blueprints") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Blueprints"),
			icon: "blueprints"
		}, {
			default: withCtx(() => [createVNode($setup["Dropdown"], { align: "end" }, {
				trigger: withCtx(() => [createVNode($setup["Button"], {
					text: _ctx.__("Create Blueprint"),
					"icon-append": "chevron-down",
					variant: "primary"
				}, null, 8, ["text"])]),
				default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
					default: withCtx(() => [$props.collections.length ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createVNode($setup["DropdownLabel"], { text: _ctx.__("Collections") }, null, 8, ["text"]), (openBlock(true), createElementBlock(Fragment, null, renderList($props.collections, (collection) => {
						return openBlock(), createBlock($setup["DropdownItem"], {
							key: collection.handle,
							href: collection.create_url,
							icon: "collections",
							text: _ctx.__(collection.title)
						}, null, 8, ["href", "text"]);
					}), 128))], 64)) : createCommentVNode("", true), $props.taxonomies.length ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [createVNode($setup["DropdownLabel"], { text: _ctx.__("Taxonomies") }, null, 8, ["text"]), (openBlock(true), createElementBlock(Fragment, null, renderList($props.taxonomies, (taxonomy) => {
						return openBlock(), createBlock($setup["DropdownItem"], {
							key: taxonomy.handle,
							href: taxonomy.create_url,
							icon: "taxonomies",
							text: _ctx.__(taxonomy.title)
						}, null, 8, ["href", "text"]);
					}), 128))], 64)) : createCommentVNode("", true)]),
					_: 1
				})]),
				_: 1
			})]),
			_: 1
		}, 8, ["title"]),
		createBaseVNode("section", _hoisted_2, [
			$props.collections.length ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createVNode($setup["Subheading"], {
				size: "lg",
				class: "mb-2",
				text: _ctx.__("Collections")
			}, null, 8, ["text"]), createVNode($setup["Panel"], null, {
				default: withCtx(() => [createBaseVNode("table", _hoisted_3, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", null, toDisplayString(_ctx.__("Blueprint")), 1), createBaseVNode("th", _hoisted_4, toDisplayString(_ctx.__("Collection")), 1)])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.collections, (collection) => {
					return openBlock(), createElementBlock(Fragment, { key: collection.handle }, [(openBlock(true), createElementBlock(Fragment, null, renderList(collection.blueprints, (blueprint) => {
						return openBlock(), createElementBlock("tr", { key: blueprint.handle }, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_5, [
							createVNode($setup["Icon"], {
								name: "collections",
								class: "text-gray-500 me-1"
							}),
							withDirectives(createVNode($setup["StatusIndicator"], { status: blueprint.hidden ? "hidden" : "published" }, null, 8, ["status"]), [[_directive_tooltip, _ctx.__(blueprint.hidden ? "Hidden" : "Visible")]]),
							createVNode($setup["Link"], {
								href: blueprint.edit_url,
								textContent: toDisplayString(_ctx.__(blueprint.title))
							}, null, 8, ["href", "textContent"])
						])]), createBaseVNode("td", _hoisted_6, [createBaseVNode("span", {
							class: "pe-2 font-mono text-xs text-gray-500 dark:text-gray-400",
							textContent: toDisplayString(_ctx.__(collection.title))
						}, null, 8, _hoisted_7)])]);
					}), 128))], 64);
				}), 128))])])]),
				_: 1
			})], 64)) : createCommentVNode("", true),
			$props.taxonomies.length ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [createVNode($setup["Subheading"], {
				size: "lg",
				class: "mb-2",
				text: _ctx.__("Taxonomies")
			}, null, 8, ["text"]), createVNode($setup["Panel"], null, {
				default: withCtx(() => [createBaseVNode("table", _hoisted_8, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", null, toDisplayString(_ctx.__("Blueprint")), 1), createBaseVNode("th", _hoisted_9, toDisplayString(_ctx.__("Taxonomy")), 1)])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.taxonomies, (taxonomy) => {
					return openBlock(), createElementBlock(Fragment, { key: taxonomy.handle }, [(openBlock(true), createElementBlock(Fragment, null, renderList(taxonomy.blueprints, (blueprint) => {
						return openBlock(), createElementBlock("tr", { key: blueprint.handle }, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_10, [
							createVNode($setup["Icon"], {
								name: "taxonomies",
								class: "text-gray-500 me-1"
							}),
							withDirectives(createVNode($setup["StatusIndicator"], { status: blueprint.hidden ? "hidden" : "published" }, null, 8, ["status"]), [[_directive_tooltip, _ctx.__(blueprint.hidden ? "Hidden" : "Visible")]]),
							createVNode($setup["Link"], {
								href: blueprint.edit_url,
								textContent: toDisplayString(_ctx.__(blueprint.title))
							}, null, 8, ["href", "textContent"])
						])]), createBaseVNode("td", _hoisted_11, [createBaseVNode("span", {
							class: "pe-2 font-mono text-xs text-gray-500 dark:text-gray-400",
							textContent: toDisplayString(_ctx.__(taxonomy.title))
						}, null, 8, _hoisted_12)])]);
					}), 128))], 64);
				}), 128))])])]),
				_: 1
			})], 64)) : createCommentVNode("", true),
			$props.navs.length ? (openBlock(), createElementBlock(Fragment, { key: 2 }, [createVNode($setup["Subheading"], {
				size: "lg",
				class: "mb-2",
				text: _ctx.__("Navigation")
			}, null, 8, ["text"]), createVNode($setup["Panel"], null, {
				default: withCtx(() => [createBaseVNode("table", _hoisted_13, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", _hoisted_14, toDisplayString(_ctx.__("Blueprint")), 1)])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.navs, (nav) => {
					return openBlock(), createElementBlock("tr", { key: nav.handle }, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_15, [createVNode($setup["Icon"], {
						name: "navigation",
						class: "text-gray-500 me-1"
					}), createVNode($setup["Link"], {
						href: nav.edit_url,
						textContent: toDisplayString(_ctx.__(nav.title))
					}, null, 8, ["href", "textContent"])])])]);
				}), 128))])])]),
				_: 1
			})], 64)) : createCommentVNode("", true),
			$props.assetContainers.length ? (openBlock(), createElementBlock(Fragment, { key: 3 }, [createVNode($setup["Subheading"], {
				size: "lg",
				class: "mb-2",
				text: _ctx.__("Asset Containers")
			}, null, 8, ["text"]), createVNode($setup["Panel"], null, {
				default: withCtx(() => [createBaseVNode("table", _hoisted_16, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", _hoisted_17, toDisplayString(_ctx.__("Blueprint")), 1)])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.assetContainers, (container) => {
					return openBlock(), createElementBlock("tr", { key: container.handle }, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_18, [createVNode($setup["Icon"], {
						name: "assets",
						class: "text-gray-500 me-1"
					}), createVNode($setup["Link"], {
						href: container.edit_url,
						textContent: toDisplayString(_ctx.__(container.title))
					}, null, 8, ["href", "textContent"])])])]);
				}), 128))])])]),
				_: 1
			})], 64)) : createCommentVNode("", true),
			$props.globals.length ? (openBlock(), createElementBlock(Fragment, { key: 4 }, [createVNode($setup["Subheading"], {
				size: "lg",
				class: "mb-2",
				text: _ctx.__("Globals")
			}, null, 8, ["text"]), createVNode($setup["Panel"], null, {
				default: withCtx(() => [createBaseVNode("table", _hoisted_19, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", _hoisted_20, toDisplayString(_ctx.__("Blueprint")), 1)])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.globals, (global) => {
					return openBlock(), createElementBlock("tr", { key: global.handle }, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_21, [createVNode($setup["Icon"], {
						name: "globals",
						class: "text-gray-500 me-1"
					}), createVNode($setup["Link"], {
						href: global.edit_url,
						textContent: toDisplayString(_ctx.__(global.title))
					}, null, 8, ["href", "textContent"])])])]);
				}), 128))])])]),
				_: 1
			})], 64)) : createCommentVNode("", true),
			$props.forms.length ? (openBlock(), createElementBlock(Fragment, { key: 5 }, [createVNode($setup["Subheading"], {
				size: "lg",
				class: "mb-2",
				text: _ctx.__("Forms")
			}, null, 8, ["text"]), createVNode($setup["Panel"], null, {
				default: withCtx(() => [createBaseVNode("table", _hoisted_22, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", _hoisted_23, toDisplayString(_ctx.__("Blueprint")), 1)])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.forms, (form) => {
					return openBlock(), createElementBlock("tr", { key: form.handle }, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_24, [createVNode($setup["Icon"], {
						name: "forms",
						class: "text-gray-500 me-1"
					}), createVNode($setup["Link"], {
						href: form.edit_url,
						textContent: toDisplayString(_ctx.__(form.title))
					}, null, 8, ["href", "textContent"])])])]);
				}), 128))])])]),
				_: 1
			})], 64)) : createCommentVNode("", true),
			createVNode($setup["Subheading"], {
				size: "lg",
				class: "mb-2",
				text: _ctx.__("Users")
			}, null, 8, ["text"]),
			createVNode($setup["Panel"], null, {
				default: withCtx(() => [createBaseVNode("table", _hoisted_25, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", _hoisted_26, toDisplayString(_ctx.__("Blueprint")), 1)])]), createBaseVNode("tbody", null, [createBaseVNode("tr", null, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_27, [createVNode($setup["Icon"], {
					name: "users",
					class: "text-gray-500 me-1"
				}), createVNode($setup["Link"], {
					href: $props.userBlueprint.edit_url,
					textContent: toDisplayString(_ctx.__("User"))
				}, null, 8, ["href", "textContent"])])])]), createBaseVNode("tr", null, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_28, [createVNode($setup["Icon"], {
					name: "groups",
					class: "text-gray-500 me-1"
				}), createVNode($setup["Link"], {
					href: $props.groupBlueprint.edit_url,
					textContent: toDisplayString(_ctx.__("Group"))
				}, null, 8, ["href", "textContent"])])])])])])]),
				_: 1
			}),
			(openBlock(true), createElementBlock(Fragment, null, renderList($props.additional, (namespace) => {
				return openBlock(), createElementBlock(Fragment, { key: namespace.namespace }, [createVNode($setup["Subheading"], {
					size: "lg",
					class: "mb-2",
					text: namespace.title
				}, null, 8, ["text"]), createVNode($setup["Panel"], null, {
					default: withCtx(() => [createBaseVNode("table", _hoisted_29, [createBaseVNode("thead", null, [createBaseVNode("tr", null, [createBaseVNode("th", _hoisted_30, toDisplayString(_ctx.__("Blueprint")), 1), _cache[0] || (_cache[0] = createBaseVNode("th", {
						scope: "col",
						class: "actions-column"
					}, null, -1))])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList(namespace.blueprints, (blueprint) => {
						return openBlock(), createElementBlock("tr", { key: blueprint.handle }, [createBaseVNode("td", null, [createBaseVNode("div", _hoisted_31, [createVNode($setup["Icon"], {
							name: "blueprints",
							class: "text-gray-500 me-1"
						}), createVNode($setup["Link"], {
							href: blueprint.edit_url,
							textContent: toDisplayString(_ctx.__(blueprint.title))
						}, null, 8, ["href", "textContent"])])]), createBaseVNode("td", _hoisted_32, [blueprint.is_resettable ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createVNode($setup["Dropdown"], { class: "mr-3" }, {
							default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
								default: withCtx(() => [createVNode($setup["DropdownItem"], {
									text: _ctx.__("Reset"),
									variant: "destructive",
									onClick: ($event) => $setup.resetters[`${blueprint.namespace}_${blueprint.handle}`].confirm()
								}, null, 8, ["text", "onClick"])]),
								_: 2
							}, 1024)]),
							_: 2
						}, 1024), createVNode(_component_blueprint_resetter, {
							ref_for: true,
							ref: (el) => $setup.resetters[`${blueprint.namespace}_${blueprint.handle}`] = el,
							resource: blueprint,
							reload: ""
						}, null, 8, ["resource"])], 64)) : createCommentVNode("", true)])]);
					}), 128))])])]),
					_: 2
				}, 1024)], 64);
			}), 128))
		]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Blueprints"),
			url: "blueprints"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
