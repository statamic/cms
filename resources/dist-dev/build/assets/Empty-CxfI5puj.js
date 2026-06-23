import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/collections/Empty.vue
var _sfc_main = {
	__name: "Empty",
	props: [
		"icon",
		"title",
		"blueprints",
		"canEdit",
		"editUrl",
		"canEditBlueprints",
		"canCreate",
		"createLabel",
		"createEntryUrl",
		"blueprintsUrl",
		"scaffoldUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		useArchitecturalBackground();
		const __returned__ = {
			props: __props,
			Head: Head_default,
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
var _hoisted_1 = { class: "py-8 mt-8 text-center starting-style-transition" };
var _hoisted_2 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
var _hoisted_3 = ["textContent"];
var _hoisted_4 = ["href", "textContent"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_ui_empty_state_item = resolveComponent("ui-empty-state-item");
	const _component_ui_empty_state_menu = resolveComponent("ui-empty-state-menu");
	const _component_ui_docs_callout = resolveComponent("ui-docs-callout");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [_ctx.__($props.title), _ctx.__("Collections")] }, null, 8, ["title"]),
		createBaseVNode("header", _hoisted_1, [createBaseVNode("h1", _hoisted_2, [createVNode(_component_ui_icon, {
			name: $props.icon,
			class: "size-5 text-gray-500"
		}, null, 8, ["name"]), createBaseVNode("span", { textContent: toDisplayString(_ctx.__($props.title)) }, null, 8, _hoisted_3)])]),
		createVNode(_component_ui_empty_state_menu, { heading: _ctx.__("Start designing your collection with these steps") }, {
			default: withCtx(() => [
				$props.canEdit ? (openBlock(), createBlock(_component_ui_empty_state_item, {
					key: 0,
					href: $props.editUrl,
					icon: "configure",
					heading: _ctx.__("Configure Collection"),
					description: _ctx.__("statamic::messages.collection_next_steps_configure_description")
				}, null, 8, [
					"href",
					"heading",
					"description"
				])) : createCommentVNode("", true),
				$props.canCreate ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [$props.blueprints.length > 1 ? (openBlock(), createBlock(_component_ui_empty_state_item, {
					key: 0,
					icon: "fieldtype-entries",
					heading: $props.createLabel,
					description: _ctx.__("statamic::messages.collection_next_steps_create_entry_description")
				}, {
					default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.blueprints, (blueprint) => {
						return openBlock(), createElementBlock("a", {
							href: blueprint.createEntryUrl,
							class: "text-blue-600 text-sm rtl:ml-2 ltr:mr-2",
							textContent: toDisplayString(blueprint.title)
						}, null, 8, _hoisted_4);
					}), 256))]),
					_: 1
				}, 8, ["heading", "description"])) : (openBlock(), createBlock(_component_ui_empty_state_item, {
					key: 1,
					href: $props.createEntryUrl,
					icon: "fieldtype-entries",
					heading: $props.createLabel,
					description: _ctx.__("statamic::messages.collection_next_steps_create_entry_description")
				}, null, 8, [
					"href",
					"heading",
					"description"
				]))], 64)) : createCommentVNode("", true),
				$props.canEditBlueprints ? (openBlock(), createBlock(_component_ui_empty_state_item, {
					key: 2,
					href: $props.blueprintsUrl,
					icon: "blueprints",
					heading: _ctx.__("Configure Blueprints"),
					description: _ctx.__("statamic::messages.collection_next_steps_blueprints_description")
				}, null, 8, [
					"href",
					"heading",
					"description"
				])) : createCommentVNode("", true),
				$props.canEdit ? (openBlock(), createBlock(_component_ui_empty_state_item, {
					key: 3,
					href: $props.scaffoldUrl,
					icon: "scaffold",
					heading: _ctx.__("Scaffold Views"),
					description: _ctx.__("statamic::messages.collection_next_steps_scaffold_description")
				}, null, 8, [
					"href",
					"heading",
					"description"
				])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["heading"]),
		createVNode(_component_ui_docs_callout, {
			topic: _ctx.__("Collections"),
			url: "collections"
		}, null, 8, ["topic"])
	], 64);
}
var Empty_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Empty.vue"]]);
//#endregion
export { Empty_default as default };
