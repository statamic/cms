import { Pt as toDisplayString, T as createVNode, U as openBlock, ct as withCtx, p as Fragment, v as createBaseVNode, w as createTextVNode, x as createElementBlock } from "./vue.esm-bundler-T63OhLKP.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { Di as Icon_default, Gt as Header_default, lr as Heading_default, on as Description_default, ur as Card_default } from "./ui-CRMYfg9R.js";
import { c as Layout_default, r as Head_default } from "./index-CLBNR4sz.js";
import { t as FormStatusIndicator_default } from "./FormStatusIndicator-BGUPai4W.js";
import { t as Layout_default$1 } from "./Layout-DsYnKmj9.js";
import { t as PanelLayout_default } from "./PanelLayout-CB5-F8X8.js";
// resources/js/pages/forms/FormsProRequired.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: [
	Layout_default,
	PanelLayout_default,
	Layout_default$1
] }, {
	__name: "FormsProRequired",
	props: { form: Object },
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Layout: Layout_default,
			PanelLayout: PanelLayout_default,
			FormsLayout: Layout_default$1,
			get Card() {
				return Card_default;
			},
			get Description() {
				return Description_default;
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
			FormStatusIndicator: FormStatusIndicator_default,
			Head: Head_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1 = { class: "mx-auto max-w-5xl" };
var _hoisted_2 = {
	class: "relative mb-4 h-16 w-14",
	"aria-hidden": "true"
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: [
		_ctx.__("Forms Pro required"),
		_ctx.__($props.form.title),
		_ctx.__("Forms")
	] }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [createVNode($setup["Header"], { class: "mb-2" }, {
		title: withCtx(() => [createVNode($setup["FormStatusIndicator"], { status: $props.form.status }, null, 8, ["status"]), createTextVNode(" " + toDisplayString(_ctx.__($props.form.title)), 1)]),
		_: 1
	}), createVNode($setup["Card"], { class: "flex flex-col items-center gap-2 py-12 text-center" }, {
		default: withCtx(() => [
			createBaseVNode("div", _hoisted_2, [
				_cache[0] || (_cache[0] = createBaseVNode("div", { class: "absolute inset-0 translate-x-3 -translate-y-2 rounded-md border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-850" }, null, -1)),
				_cache[1] || (_cache[1] = createBaseVNode("div", { class: "absolute inset-0 translate-x-1.5 -translate-y-1 rounded-md border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-850" }, null, -1)),
				_cache[2] || (_cache[2] = createBaseVNode("div", { class: "absolute inset-0 flex flex-col gap-1.25 rounded-md border border-gray-300 bg-white p-2 shadow-ui-sm dark:border-gray-600 dark:bg-gray-800" }, [
					createBaseVNode("div", { class: "h-0.75 w-2/3 rounded-full bg-gray-300 dark:bg-gray-600" }),
					createBaseVNode("div", { class: "h-2 rounded-sm border border-gray-200 dark:border-gray-600" }),
					createBaseVNode("div", { class: "h-2 rounded-sm border border-gray-200 dark:border-gray-600" })
				], -1)),
				createVNode($setup["Icon"], {
					name: "padlock-locked",
					class: "absolute -right-2 -bottom-2 size-5 text-gray-500 dark:text-gray-400 [&_path]:fill-white dark:[&_path]:fill-gray-800"
				})
			]),
			createVNode($setup["Heading"], {
				size: "lg",
				text: _ctx.__("Forms Pro required")
			}, null, 8, ["text"]),
			createVNode($setup["Description"], {
				class: "max-w-sm",
				text: _ctx.__("statamic::messages.form_forms_pro_required")
			}, null, 8, ["text"])
		]),
		_: 1
	})])], 64);
}
var FormsProRequired_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "FormsProRequired.vue"]]);
export { FormsProRequired_default as default };
