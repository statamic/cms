import { B as openBlock, C as createVNode, H as provide, h as computed, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { O as Form_default } from "./ui-BfR7XN6t.js";
import { l as dateFormatter } from "./api-BR1uuoCk.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/forms/Submission.vue
var _sfc_main = {
	__name: "Submission",
	props: [
		"id",
		"formTitle",
		"date",
		"blueprint",
		"values",
		"meta"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const formattedDate = computed(() => dateFormatter.format(props.date));
		const title = computed(() => `${__("Form Submission")} ${props.id}`);
		provide("isFormSubmission", true);
		const __returned__ = {
			props,
			formattedDate,
			title,
			computed,
			provide,
			Head: Head_default,
			get PublishForm() {
				return Form_default;
			},
			get dateFormatter() {
				return dateFormatter;
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
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["Head"], { title: [
		$setup.title,
		_ctx.__($props.formTitle),
		_ctx.__("Forms")
	] }, null, 8, ["title"]), createVNode($setup["PublishForm"], {
		icon: "forms",
		title: $setup.formattedDate,
		blueprint: $props.blueprint,
		"initial-values": $props.values,
		"initial-meta": $props.meta,
		"submit-url": null,
		"read-only": ""
	}, null, 8, [
		"title",
		"blueprint",
		"initial-values",
		"initial-meta"
	])]);
}
var Submission_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Submission.vue"]]);
//#endregion
export { Submission_default as default };
