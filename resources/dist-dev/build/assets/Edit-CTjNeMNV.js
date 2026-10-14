import { At as toDisplayString, B as openBlock, C as createVNode, J as resolveDynamicComponent, L as onMounted, M as mergeProps, N as nextTick, R as onUnmounted, S as createTextVNode, _ as createBlock, _t as ref, et as watch, f as Fragment, g as createBaseVNode, it as withCtx, p as Teleport, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-B-BfxmJA.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { G as axios, i as link_default } from "./index.esm-B-zRpAhf.js";
import { Ti as Icon_default, Ut as Header_default, ar as Panel_default, cr as Heading_default, fi as Badge_default, lr as Card_default, si as Button_default, sr as Header_default$1 } from "./ui-DPzuvYkS.js";
import { g as keys } from "./api-DX2Pd1oX.js";
import { l as Layout_default, r as Head_default } from "./index-DJE_WRsq.js";
import { t as FormStatusIndicator_default } from "./FormStatusIndicator-CAX13lG0.js";
import { t as Layout_default$1 } from "./Layout-Beuk7oam.js";
import { t as PanelLayout_default } from "./PanelLayout-DGN-nSBD.js";
//#region resources/js/pages/forms/connect/Edit.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: [
	Layout_default,
	PanelLayout_default,
	Layout_default$1
] }, {
	__name: "Edit",
	props: {
		form: Object,
		connector: Object,
		component: Object,
		value: Array,
		action: String,
		isConfigured: Boolean,
		suggestableFields: Array
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const errors = ref({});
		const saving = ref(false);
		const saveBinding = ref(null);
		const value = ref(props.value);
		const save = () => {
			if (saving.value) return;
			errors.value = {};
			saving.value = true;
			axios.patch(props.action, value.value).then((response) => {
				value.value = response.data;
				nextTick(() => Statamic.$dirty.remove("connections"));
				Statamic.$toast.success(__("Saved"));
			}).catch((e) => {
				if (e.response?.status === 422) {
					errors.value = e.response.data.errors;
					Statamic.$toast.error(e.response.data.message);
				} else Statamic.$toast.error(__("Something went wrong"));
			}).finally(() => saving.value = false);
		};
		watch(value, () => Statamic.$dirty.add("connections"), { deep: true });
		onMounted(() => {
			if (!props.isConfigured) return;
			saveBinding.value = keys.bindGlobal(["mod+s"], (e) => {
				e.preventDefault();
				save();
			});
		});
		onUnmounted(() => {
			saveBinding.value?.destroy();
			Statamic.$dirty.remove("connections");
		});
		const __returned__ = {
			props,
			errors,
			saving,
			saveBinding,
			value,
			save,
			nextTick,
			onMounted,
			onUnmounted,
			ref,
			watch,
			get axios() {
				return axios;
			},
			get keys() {
				return keys;
			},
			Layout: Layout_default,
			PanelLayout: PanelLayout_default,
			FormsLayout: Layout_default$1,
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
			FormStatusIndicator: FormStatusIndicator_default,
			get Link() {
				return link_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1 = { class: "hidden sm:inline" };
var _hoisted_2 = { class: "mx-auto max-w-5xl" };
var _hoisted_3 = { class: "flex items-center justify-between gap-3" };
var _hoisted_4 = { class: "relative inline-flex items-center gap-1.5" };
var _hoisted_5 = ["innerHTML"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [
			_ctx.__($props.connector.title),
			_ctx.__("Connect"),
			_ctx.__($props.form.title),
			_ctx.__("Forms")
		] }, null, 8, ["title"]),
		$props.isConfigured ? (openBlock(), createBlock(Teleport, {
			key: 0,
			to: "#form-layout-actions"
		}, [createVNode($setup["Button"], {
			variant: "primary",
			"aria-label": _ctx.__("Save"),
			disabled: $setup.saving,
			onClick: $setup.save
		}, {
			default: withCtx(() => [createVNode($setup["Icon"], {
				name: "save",
				class: "sm:hidden"
			}), createBaseVNode("span", _hoisted_1, toDisplayString(_ctx.__("Save")), 1)]),
			_: 1
		}, 8, ["aria-label", "disabled"])])) : createCommentVNode("", true),
		createBaseVNode("div", _hoisted_2, [createVNode($setup["Header"], { class: "mb-2" }, {
			title: withCtx(() => [createVNode($setup["FormStatusIndicator"], { status: $props.form.status }, null, 8, ["status"]), createTextVNode(" " + toDisplayString(_ctx.__($props.form.title)), 1)]),
			_: 1
		}), createVNode($setup["Panel"], null, {
			default: withCtx(() => [createVNode($setup["PanelHeader"], null, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_3, [createVNode($setup["Heading"], null, {
					default: withCtx(() => [
						createVNode($setup["Link"], {
							href: _ctx.cp_url(`forms/${$props.form.handle}/connect`),
							class: "inline-flex items-center gap-2 text-inherit my-0! cursor-pointer hover:opacity-80"
						}, {
							default: withCtx(() => [createVNode($setup["Icon"], {
								name: "connection",
								class: "size-4! opacity-60! text-gray-925 dark:text-white",
								"aria-hidden": "true"
							}), createTextVNode(" " + toDisplayString(_ctx.__("Connect")), 1)]),
							_: 1
						}, 8, ["href"]),
						createVNode($setup["Icon"], {
							name: "chevron-right",
							class: "size-3.5 text-gray-400 dark:text-gray-500",
							"aria-hidden": "true"
						}),
						createBaseVNode("span", _hoisted_4, [
							$props.connector.icon ? (openBlock(), createElementBlock("span", {
								key: 0,
								class: "size-4 text-gray-700 dark:text-gray-300 [&_svg]:size-4",
								"aria-hidden": "true",
								innerHTML: $props.connector.icon
							}, null, 8, _hoisted_5)) : createCommentVNode("", true),
							createBaseVNode("span", null, toDisplayString(_ctx.__($props.connector.title)), 1),
							createVNode($setup["Badge"], {
								pill: "",
								class: "absolute start-full top-1/2 ms-1.5 size-6 -translate-y-1/2"
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString($setup.value.length), 1)]),
								_: 1
							})
						])
					]),
					_: 1
				}), _cache[1] || (_cache[1] = createBaseVNode("div", { id: "connection-list-actions" }, null, -1))])]),
				_: 1
			}), createVNode($setup["Card"], null, {
				default: withCtx(() => [(openBlock(), createBlock(resolveDynamicComponent($props.component.name), mergeProps({
					form: $props.form,
					errors: $setup.errors,
					modelValue: $setup.value,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.value = $event)
				}, $props.component.props), null, 16, [
					"form",
					"errors",
					"modelValue"
				]))]),
				_: 1
			})]),
			_: 1
		})])
	], 64);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
