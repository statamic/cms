import { B as openBlock, C as createVNode, K as resolveComponent, b as createSlots, f as Fragment, g as createBaseVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { n as DocsCallout_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/blueprints/Edit.vue
var _sfc_main = {
	__name: "Edit",
	props: {
		blueprint: Object,
		action: String,
		showTitle: Boolean,
		useTabs: {
			type: Boolean,
			default: true
		},
		canDefineLocalizable: {
			type: Boolean,
			default: void 0
		},
		resetRoute: String,
		isResettable: Boolean,
		isFormBlueprint: Boolean
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Head: Head_default,
			get DocsCallout() {
				return DocsCallout_default;
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
	class: "max-w-5xl 3xl:max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_dropdown_item = resolveComponent("ui-dropdown-item");
	const _component_ui_dropdown_menu = resolveComponent("ui-dropdown-menu");
	const _component_ui_dropdown = resolveComponent("ui-dropdown");
	const _component_blueprint_resetter = resolveComponent("blueprint-resetter");
	const _component_blueprint_builder = resolveComponent("blueprint-builder");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Edit Blueprint") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [createVNode(_component_blueprint_builder, {
		"show-title": $props.showTitle,
		action: $props.action,
		"initial-blueprint": $props.blueprint,
		"use-tabs": $props.useTabs,
		"can-define-localizable": $props.canDefineLocalizable,
		"is-form-blueprint": $props.isFormBlueprint
	}, createSlots({ _: 2 }, [$props.isResettable ? {
		name: "actions",
		fn: withCtx(() => [createVNode(_component_ui_dropdown, null, {
			default: withCtx(() => [createVNode(_component_ui_dropdown_menu, null, {
				default: withCtx(() => [createVNode(_component_ui_dropdown_item, {
					text: _ctx.__("Reset"),
					variant: "destructive",
					onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$refs.resetter.confirm())
				}, null, 8, ["text"])]),
				_: 1
			})]),
			_: 1
		}), createVNode(_component_blueprint_resetter, {
			ref: "resetter",
			route: $props.resetRoute,
			resource: $props.blueprint,
			reload: ""
		}, null, 8, ["route", "resource"])]),
		key: "0"
	} : void 0]), 1032, [
		"show-title",
		"action",
		"initial-blueprint",
		"use-tabs",
		"can-define-localizable",
		"is-form-blueprint"
	]), createVNode($setup["DocsCallout"], {
		topic: _ctx.__("Blueprints"),
		url: "blueprints"
	}, null, 8, ["topic"])])], 64);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
