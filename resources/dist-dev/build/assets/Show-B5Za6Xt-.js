import { At as toDisplayString, B as openBlock, C as createVNode, H as provide, S as createTextVNode, _ as createBlock, h as computed, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-DZ9hA_XK.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { O as Form_default } from "./ui-B0AhlaTC.js";
import { l as dateFormatter } from "./api-CwAMx3xr.js";
import { t as SubmissionStatusIndicator_default } from "./SubmissionStatusIndicator-JWBqJlj1.js";
import { n as useFieldNumberingPreference, t as FieldNumberingToggle_default } from "./FieldNumberingToggle-ChP4_s-Z.js";
import { c as Layout_default, r as Head_default } from "./index-Mrn_b1WS.js";
import { t as Layout_default$1 } from "./Layout-CqZioJdW.js";
import { t as PanelLayout_default } from "./PanelLayout-CT7LfZwO.js";
// resources/js/pages/forms/submissions/Show.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: [
	Layout_default,
	PanelLayout_default,
	Layout_default$1
] }, {
	__name: "Show",
	props: [
		"form",
		"id",
		"formTitle",
		"status",
		"date",
		"blueprint",
		"values",
		"meta"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const { showFieldNumbers } = useFieldNumberingPreference();
		const formattedDate = computed(() => dateFormatter.format(props.date));
		const title = computed(() => `${__("Form Submission")} ${props.id}`);
		const blueprint = computed(() => {
			if (!showFieldNumbers.value) return props.blueprint;
			let number = 0;
			return {
				...props.blueprint,
				tabs: (props.blueprint.tabs || []).map((tab) => ({
					...tab,
					sections: (tab.sections || []).map((section) => ({
						...section,
						fields: (section.fields || []).map((field) => {
							if ([
								"form_heading",
								"form_paragraph",
								"info"
							].includes(field.type)) return field;
							return {
								...field,
								display: `${++number}. ${field.display ?? field.handle}`
							};
						})
					}))
				}))
			};
		});
		provide("isFormSubmission", true);
		const __returned__ = {
			props,
			showFieldNumbers,
			formattedDate,
			title,
			blueprint,
			computed,
			provide,
			Head: Head_default,
			get PublishForm() {
				return Form_default;
			},
			get dateFormatter() {
				return dateFormatter;
			},
			Layout: Layout_default,
			PanelLayout: PanelLayout_default,
			FormsLayout: Layout_default$1,
			SubmissionStatusIndicator: SubmissionStatusIndicator_default,
			FieldNumberingToggle: FieldNumberingToggle_default,
			get useFieldNumberingPreference() {
				return useFieldNumberingPreference;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["Head"], { title: [
		$setup.title,
		_ctx.__($props.formTitle),
		_ctx.__("Forms")
	] }, null, 8, ["title"]), (openBlock(), createBlock($setup["PublishForm"], {
		key: $setup.showFieldNumbers ? "numbered" : null,
		title: $setup.formattedDate,
		blueprint: $setup.blueprint,
		"initial-values": $props.values,
		"initial-meta": $props.meta,
		"submit-url": null,
		"read-only": ""
	}, {
		title: withCtx(() => [createVNode($setup["SubmissionStatusIndicator"], { status: $props.status }, null, 8, ["status"]), createTextVNode(" " + toDisplayString($setup.formattedDate), 1)]),
		actions: withCtx(() => [createVNode($setup["FieldNumberingToggle"])]),
		_: 1
	}, 8, [
		"title",
		"blueprint",
		"initial-values",
		"initial-meta"
	]))]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
export { Show_default as default };
