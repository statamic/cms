import { D as defineComponent, H as openBlock, M as inject, Mt as toDisplayString, _ as computed, b as createCommentVNode, x as createElementBlock } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
// resources/js/components/forms/builder/categories.js
var categories = {
	structure: {
		get title() {
			return __("Structure");
		},
		color: "purple"
	},
	information: {
		get title() {
			return __("Information");
		},
		color: "pink"
	},
	text: {
		get title() {
			return __("Text");
		},
		color: "purple"
	},
	choice: {
		get title() {
			return __("Choice");
		},
		color: "orange"
	},
	rate: {
		get title() {
			return __("Rate");
		},
		color: "amber"
	},
	contact: {
		get title() {
			return __("Contact Info");
		},
		color: "blue"
	},
	number: {
		get title() {
			return __("Number");
		},
		color: "teal"
	},
	datetime: {
		get title() {
			return __("Date and Time");
		},
		color: "fuchsia"
	},
	media: {
		get title() {
			return __("Media");
		},
		color: "cyan"
	},
	payment: {
		get title() {
			return __("Payment");
		},
		color: "green"
	},
	fieldsets: {
		get title() {
			return __("Fieldsets");
		},
		color: "gray"
	},
	other: {
		get title() {
			return __("Other");
		},
		color: "gray"
	}
};
var collectsValue = (category) => !["information", "structure"].includes(category);
var categoryColorClasses = {
	purple: {
		dot: "bg-purple-500",
		icon: "text-purple-600 dark:text-purple-400"
	},
	pink: {
		dot: "bg-pink-500",
		icon: "text-pink-600 dark:text-pink-400"
	},
	orange: {
		dot: "bg-orange-500",
		icon: "text-orange-600 dark:text-orange-400"
	},
	amber: {
		dot: "bg-green-500",
		icon: "text-green-600 dark:text-green-400"
	},
	blue: {
		dot: "bg-blue-500",
		icon: "text-blue-600 dark:text-blue-400"
	},
	teal: {
		dot: "bg-teal-500",
		icon: "text-teal-600 dark:text-teal-400"
	},
	fuchsia: {
		dot: "bg-fuchsia-500",
		icon: "text-fuchsia-600 dark:text-fuchsia-400"
	},
	cyan: {
		dot: "bg-cyan-500",
		icon: "text-cyan-600 dark:text-cyan-400"
	},
	green: {
		dot: "bg-green-500",
		icon: "text-green-600 dark:text-green-400"
	},
	gray: {
		dot: "bg-gray-500",
		icon: "text-gray-600 dark:text-gray-400"
	}
};
// resources/js/components/forms/FieldNumber.vue?vue&type=script&setup=true&lang.ts
var FieldNumber_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FieldNumber",
	props: { fieldKey: {} },
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const fieldNumbers = inject("fieldNumbers", null);
		const __returned__ = {
			props,
			fieldNumbers,
			number: computed(() => props.fieldKey ? fieldNumbers?.value?.get(props.fieldKey) ?? null : null)
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/FieldNumber.vue
var _hoisted_1 = {
	key: 0,
	"data-field-number": "",
	class: "font-mono text-2xs text-gray-500 tabular-nums dark:text-gray-400"
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return $setup.number ? (openBlock(), createElementBlock("span", _hoisted_1, toDisplayString($setup.number) + ".", 1)) : createCommentVNode("", true);
}
var FieldNumber_default = /*#__PURE__*/ _plugin_vue_export_helper_default(FieldNumber_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "FieldNumber.vue"]]);
export { collectsValue as i, categories as n, categoryColorClasses as r, FieldNumber_default as t };
