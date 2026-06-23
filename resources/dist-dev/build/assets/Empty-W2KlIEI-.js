import { At as toDisplayString, B as openBlock, C as createVNode, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Ci as Icon_default, Kt as Menu_default, n as DocsCallout_default, qt as Item_default } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/taxonomies/Empty.vue
var _sfc_main = {
	__name: "Empty",
	props: [
		"taxonomyTitle",
		"createUrl",
		"taxonomyEditUrl",
		"taxonomyBlueprintsUrl",
		"canCreate",
		"canEdit",
		"canConfigureFields"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		useArchitecturalBackground();
		const __returned__ = {
			Head: Head_default,
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
var _hoisted_1 = { class: "py-8 pt-16 text-center" };
var _hoisted_2 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [_ctx.__($props.taxonomyTitle), _ctx.__("Taxonomies")] }, null, 8, ["title"]),
		createBaseVNode("header", _hoisted_1, [createBaseVNode("h1", _hoisted_2, [createVNode($setup["Icon"], {
			name: "taxonomies",
			class: "size-5 text-gray-500"
		}), createBaseVNode("span", null, toDisplayString(_ctx.__($props.taxonomyTitle)), 1)])]),
		createVNode($setup["EmptyStateMenu"], { heading: _ctx.__("Start designing your taxonomy with these steps") }, {
			default: withCtx(() => [
				$props.canEdit ? (openBlock(), createBlock($setup["EmptyStateItem"], {
					key: 0,
					href: $props.taxonomyEditUrl,
					icon: "configure",
					heading: _ctx.__("Configure Taxonomy"),
					description: _ctx.__("statamic::messages.taxonomy_next_steps_configure_description")
				}, null, 8, [
					"href",
					"heading",
					"description"
				])) : createCommentVNode("", true),
				$props.canCreate ? (openBlock(), createBlock($setup["EmptyStateItem"], {
					key: 1,
					href: $props.createUrl,
					icon: "fieldtype-taxonomy",
					heading: _ctx.__("Create Term"),
					description: _ctx.__("statamic::messages.taxonomy_next_steps_create_term_description")
				}, null, 8, [
					"href",
					"heading",
					"description"
				])) : createCommentVNode("", true),
				$props.canConfigureFields ? (openBlock(), createBlock($setup["EmptyStateItem"], {
					key: 2,
					href: $props.taxonomyBlueprintsUrl,
					icon: "blueprints",
					heading: _ctx.__("Configure Blueprints"),
					description: _ctx.__("statamic::messages.taxonomy_next_steps_blueprints_description")
				}, null, 8, [
					"href",
					"heading",
					"description"
				])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["heading"]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Taxonomies"),
			url: "taxonomies"
		}, null, 8, ["topic"])
	], 64);
}
var Empty_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Empty.vue"]]);
//#endregion
export { Empty_default as default };
