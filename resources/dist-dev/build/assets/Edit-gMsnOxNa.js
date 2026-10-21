import { $ as useTemplateRef, At as toDisplayString, B as openBlock, C as createVNode, L as onMounted, R as onUnmounted, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, it as withCtx, p as Teleport, y as createElementBlock } from "./vue.esm-bundler-DZ9hA_XK.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { c as router } from "./index.esm-D7PRwS1l.js";
import { I as Container_default, L as Tabs_default, N as Request, Ut as Header_default, j as Pipeline, oi as Button_default, wi as Icon_default } from "./ui-B0AhlaTC.js";
import { c as Layout_default, r as Head_default } from "./index-Mrn_b1WS.js";
import { t as Layout_default$1 } from "./Layout-CqZioJdW.js";
import { t as PanelLayout_default } from "./PanelLayout-CT7LfZwO.js";
// resources/js/pages/forms/Edit.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: [
	Layout_default,
	PanelLayout_default,
	Layout_default$1
] }, {
	__name: "Edit",
	props: {
		blueprint: {
			type: Object,
			required: true
		},
		initialValues: {
			type: Object,
			required: true,
			default: () => ({})
		},
		initialMeta: {
			type: Object,
			required: true,
			default: () => ({})
		},
		action: {
			type: String,
			required: true
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const container = useTemplateRef("container");
		const values = ref(props.initialValues);
		const meta = ref(props.initialMeta);
		const errors = ref({});
		const saving = ref(false);
		function save() {
			new Pipeline().provide({
				container,
				errors,
				saving
			}).through([new Request(props.action, "PATCH")]).then((response) => {
				Statamic.$toast.success(__("Saved"));
				if (response.data.redirect) router.get(response.data.redirect);
			});
		}
		let saveKeyBinding;
		onMounted(() => {
			saveKeyBinding = Statamic.$keys.bindGlobal(["mod+s"], (e) => {
				e.preventDefault();
				save();
			});
		});
		onUnmounted(() => saveKeyBinding.destroy());
		const __returned__ = {
			props,
			container,
			values,
			meta,
			errors,
			saving,
			save,
			get saveKeyBinding() {
				return saveKeyBinding;
			},
			set saveKeyBinding(v) {
				saveKeyBinding = v;
			},
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get Icon() {
				return Icon_default;
			},
			onMounted,
			onUnmounted,
			ref,
			useTemplateRef,
			get Pipeline() {
				return Pipeline;
			},
			get Request() {
				return Request;
			},
			get router() {
				return router;
			},
			get Container() {
				return Container_default;
			},
			get Tabs() {
				return Tabs_default;
			},
			Layout: Layout_default,
			PanelLayout: PanelLayout_default,
			FormsLayout: Layout_default$1,
			Head: Head_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1 = { class: "hidden sm:inline" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [
			_ctx.__("Configure"),
			_ctx.__($props.initialValues.title),
			_ctx.__("Forms")
		] }, null, 8, ["title"]),
		(openBlock(), createBlock(Teleport, { to: "#form-layout-actions" }, [createVNode($setup["Button"], {
			variant: "primary",
			onClick: $setup.save,
			disabled: $setup.saving,
			"aria-label": _ctx.__("Save")
		}, {
			default: withCtx(() => [createVNode($setup["Icon"], {
				name: "save",
				class: "sm:hidden"
			}), createBaseVNode("span", _hoisted_1, toDisplayString(_ctx.__("Save")), 1)]),
			_: 1
		}, 8, ["disabled", "aria-label"])])),
		createVNode($setup["Header"], {
			title: _ctx.__("Configure Form"),
			icon: "cog"
		}, {
			default: withCtx(() => [createVNode($setup["Button"], {
				variant: "primary",
				text: _ctx.__("Save"),
				onClick: $setup.save,
				disabled: $setup.saving
			}, null, 8, ["text", "disabled"])]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["Container"], {
			ref: "container",
			blueprint: $props.blueprint,
			meta: $setup.meta,
			errors: $setup.errors,
			"as-config": "",
			modelValue: $setup.values,
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.values = $event)
		}, {
			default: withCtx(() => [createVNode($setup["Tabs"])]),
			_: 1
		}, 8, [
			"blueprint",
			"meta",
			"errors",
			"modelValue"
		])
	], 64);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
export { Edit_default as default };
