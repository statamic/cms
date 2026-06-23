import { At as toDisplayString, B as openBlock, C as createVNode, L as onMounted, R as onUnmounted, S as createTextVNode, _t as ref, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { Gn as Panel_default, Ut as Header_default, Vt as Input_default, gt as Switch_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/collections/Scaffold.vue
var _sfc_main = {
	__name: "Scaffold",
	props: {
		collection: Object,
		route: String
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const index = ref(`${props.collection.handle}/index`);
		const show = ref(`${props.collection.handle}/show`);
		const selected = ref({
			index: true,
			show: true
		});
		const canSubmit = computed(() => {
			return Object.keys(files.value).length > 0;
		});
		const files = computed(() => {
			var files = {};
			if (selected.value.index) files.index = index.value;
			if (selected.value.show) files.show = show.value;
			return files;
		});
		const submit = () => {
			router.post(props.route, files.value);
		};
		let submitKeyBinding;
		onMounted(() => {
			submitKeyBinding = Statamic.$keys.bindGlobal(["return"], (e) => {
				if (canSubmit.value) submit();
			});
		});
		onUnmounted(() => submitKeyBinding.destroy());
		const __returned__ = {
			props,
			index,
			show,
			selected,
			canSubmit,
			files,
			submit,
			get submitKeyBinding() {
				return submitKeyBinding;
			},
			set submitKeyBinding(v) {
				submitKeyBinding = v;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get Panel() {
				return Panel_default;
			},
			get Switch() {
				return Switch_default;
			},
			get Input() {
				return Input_default;
			},
			ref,
			computed,
			onMounted,
			onUnmounted,
			get router() {
				return router;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "data-table" };
var _hoisted_2 = { class: "w-1/4" };
var _hoisted_3 = { class: "flex items-center gap-2 sm:gap-3" };
var _hoisted_4 = ["textContent"];
var _hoisted_5 = { class: "w-1/4" };
var _hoisted_6 = { class: "flex items-center gap-2 sm:gap-3" };
var _hoisted_7 = ["textContent"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [
			_ctx.__("Scaffold Views"),
			_ctx.__($props.collection.title),
			_ctx.__("Collections")
		] }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__("Scaffold Views"),
			icon: "scaffold"
		}, {
			default: withCtx(() => [createVNode($setup["Button"], {
				variant: "primary",
				tabindex: "4",
				disabled: !$setup.canSubmit,
				onClick: $setup.submit
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Create Views")), 1)]),
				_: 1
			}, 8, ["disabled"])]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["Panel"], { heading: _ctx.__("messages.collection_scaffold_instructions") }, {
			default: withCtx(() => [createBaseVNode("table", _hoisted_1, [createBaseVNode("tbody", null, [createBaseVNode("tr", null, [createBaseVNode("td", _hoisted_2, [createBaseVNode("div", _hoisted_3, [createVNode($setup["Switch"], {
				modelValue: $setup.selected.index,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.selected.index = $event),
				size: "sm",
				id: "field_index"
			}, null, 8, ["modelValue"]), createBaseVNode("label", {
				for: "field_index",
				textContent: toDisplayString(_ctx.__("Index Template"))
			}, null, 8, _hoisted_4)])]), createBaseVNode("td", null, [createVNode($setup["Input"], {
				modelValue: $setup.index,
				"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.index = $event),
				disabled: !$setup.selected.index
			}, null, 8, ["modelValue", "disabled"])])]), createBaseVNode("tr", null, [createBaseVNode("td", _hoisted_5, [createBaseVNode("div", _hoisted_6, [createVNode($setup["Switch"], {
				modelValue: $setup.selected.show,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.selected.show = $event),
				size: "sm",
				id: "field_template"
			}, null, 8, ["modelValue"]), createBaseVNode("label", {
				for: "field_template",
				textContent: toDisplayString(_ctx.__("Show Template"))
			}, null, 8, _hoisted_7)])]), createBaseVNode("td", null, [createVNode($setup["Input"], {
				modelValue: $setup.show,
				"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.show = $event),
				disabled: !$setup.selected.show
			}, null, 8, ["modelValue", "disabled"])])])])])]),
			_: 1
		}, 8, ["heading"])
	], 64);
}
var Scaffold_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Scaffold.vue"]]);
//#endregion
export { Scaffold_default as default };
