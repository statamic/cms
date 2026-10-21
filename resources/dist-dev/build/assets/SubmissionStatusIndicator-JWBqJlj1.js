import { At as toDisplayString, B as openBlock, Dt as normalizeClass, at as withDirectives, h as computed, q as resolveDirective, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-DZ9hA_XK.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
// resources/js/components/forms/SubmissionStatusIndicator.vue
var _sfc_main = {
	__name: "SubmissionStatusIndicator",
	props: {
		status: {
			type: String,
			required: false,
			default: "finalized",
			validator: (value) => [
				"finalized",
				"partial",
				"spam"
			].includes(value)
		},
		showDot: {
			type: Boolean,
			default: true
		},
		showLabel: {
			type: Boolean,
			default: false
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const __returned__ = {
			props,
			statusClass: computed(() => {
				return {
					finalized: "bg-green-400",
					partial: "bg-gray-300 dark:bg-gray-200",
					spam: "bg-amber-400"
				}[props.status];
			}),
			label: computed(() => {
				return {
					finalized: __("Finalized"),
					partial: __("Partial"),
					spam: __("Spam")
				}[props.status];
			}),
			computed
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "flex items-center gap-2" };
var _hoisted_2 = ["textContent"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("span", _hoisted_1, [$props.showDot ? withDirectives((openBlock(), createElementBlock("span", {
		key: 0,
		class: normalizeClass(["size-2 rounded-full", $setup.statusClass])
	}, null, 2)), [[_directive_tooltip, $setup.label]]) : createCommentVNode("", true), $props.showLabel ? (openBlock(), createElementBlock("span", {
		key: 1,
		class: normalizeClass(["status-index-field select-none", `status-${$props.status}`]),
		textContent: toDisplayString($setup.label)
	}, null, 10, _hoisted_2)) : createCommentVNode("", true)]);
}
var SubmissionStatusIndicator_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "SubmissionStatusIndicator.vue"]]);
export { SubmissionStatusIndicator_default as t };
