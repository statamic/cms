import { B as openBlock, C as createVNode, D as guardReactiveProps, G as renderSlot, Ot as normalizeProps, _ as createBlock, b as createSlots, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-DZ9hA_XK.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { Ln as Combobox_default, Ot as HasInputOptions_default, Vt as Input_default, mt as Switch_default } from "./ui-B0AhlaTC.js";
// resources/js/components/field-conditions/Condition.vue
var _sfc_main = {
	__name: "Condition",
	props: {
		condition: Object,
		suggestableFields: Array,
		excludeHandle: String,
		excludeOperators: {
			type: Array,
			default: () => []
		},
		size: String
	},
	emits: ["update:condition"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const operatorOptions = computed(() => [
			{
				label: __("Equals"),
				value: "equals"
			},
			{
				label: __("Does not equal"),
				value: "not"
			},
			{
				label: __("Contains"),
				value: "contains"
			},
			{
				label: __("Contains Any"),
				value: "contains_any"
			},
			{
				label: __("Includes Any"),
				value: "includes_any"
			},
			{
				label: "===",
				value: "==="
			},
			{
				label: "!==",
				value: "!=="
			},
			{
				label: ">",
				value: ">"
			},
			{
				label: ">=",
				value: ">="
			},
			{
				label: "<",
				value: "<"
			},
			{
				label: "<=",
				value: "<="
			},
			{
				label: __("Custom"),
				value: "custom"
			}
		].filter((op) => !props.excludeOperators.includes(op.value)));
		const selectedField = computed(() => props.suggestableFields.find((field) => field.handle === props.condition.field));
		const fieldOptions = computed(() => {
			return props.suggestableFields.filter((field) => field.handle !== props.excludeHandle && field.handle !== props.condition.field).map((field) => {
				let display = field.config.display;
				if (!display) display = field.handle.replace(/_/g, " ").replace(/(?:^|\s)\S/g, function(a) {
					return a.toUpperCase();
				});
				return {
					value: field.handle,
					label: display
				};
			});
		});
		const isToggleField = (field) => [
			"toggle",
			"revealer",
			"yes_no"
		].includes(field?.config?.type);
		const showValueToggle = computed(() => isToggleField(selectedField.value) && [
			"equals",
			"not",
			"===",
			"!=="
		].includes(props.condition.operator));
		const valueToggleLabel = computed(() => {
			const field = __(selectedField.value?.config.display) || props.condition.field;
			return field ? __("Value for :field", { field }) : __("Value");
		});
		const showValueDropdown = computed(() => {
			return [
				"button_group",
				"checkboxes",
				"radio",
				"select",
				"dropdown",
				"multi_choice",
				"ranking",
				"image_choice"
			].includes(selectedField.value?.config?.type) && [
				"equals",
				"not",
				"===",
				"!=="
			].includes(props.condition.operator);
		});
		const showNumberInput = computed(() => {
			return [
				"number",
				"integer",
				"opinion_scale",
				"star_ranking"
			].includes(selectedField.value?.config?.type) && [
				"equals",
				"not",
				"===",
				"!=="
			].includes(props.condition.operator);
		});
		const valueOptions = computed(() => {
			if (!showValueDropdown.value || !selectedField.value?.config.options) return null;
			return HasInputOptions_default.methods.normalizeInputOptions(selectedField.value.config.options);
		});
		const onFieldBlur = (search) => search ? update("field", search) : null;
		const onValueBlur = (value) => value ? update("value", value) : null;
		const onValueToggle = (checked) => update("value", checked.toString());
		const update = (key, value) => {
			const condition = {
				...props.condition,
				[key]: value
			};
			if (key === "field" && isToggleField(props.suggestableFields.find((field) => field.handle === value)) && !condition.value) condition.value = "false";
			emit("update:condition", condition);
		};
		const __returned__ = {
			emit,
			props,
			operatorOptions,
			selectedField,
			fieldOptions,
			isToggleField,
			showValueToggle,
			valueToggleLabel,
			showValueDropdown,
			showNumberInput,
			valueOptions,
			onFieldBlur,
			onValueBlur,
			onValueToggle,
			update,
			get Combobox() {
				return Combobox_default;
			},
			get Input() {
				return Input_default;
			},
			get Switch() {
				return Switch_default;
			},
			computed,
			get HasInputOptions() {
				return HasInputOptions_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createBaseVNode("li", null, [createVNode($setup["Combobox"], {
			"model-value": $props.condition.field,
			options: $setup.fieldOptions,
			"option-label": "label",
			"option-value": "value",
			placeholder: _ctx.__("Field"),
			size: $props.size,
			searchable: "",
			"adaptive-width": "",
			taggable: "",
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.update("field", $event)),
			"onSearch:blur": $setup.onFieldBlur
		}, createSlots({ _: 2 }, [_ctx.$slots["field-option"] ? {
			name: "option",
			fn: withCtx((optionProps) => [renderSlot(_ctx.$slots, "field-option", normalizeProps(guardReactiveProps(optionProps)))]),
			key: "0"
		} : void 0, _ctx.$slots["field-selected"] ? {
			name: "selected-option",
			fn: withCtx(({ option }) => [renderSlot(_ctx.$slots, "field-selected", {
				option,
				field: $setup.selectedField
			})]),
			key: "1"
		} : void 0]), 1032, [
			"model-value",
			"options",
			"placeholder",
			"size"
		])]),
		createBaseVNode("li", null, [createVNode($setup["Combobox"], {
			"model-value": $props.condition.operator,
			options: $setup.operatorOptions,
			"option-label": "label",
			"option-value": "value",
			placeholder: _ctx.__("Operator"),
			searchable: false,
			size: $props.size,
			"adaptive-width": "",
			"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.update("operator", $event))
		}, null, 8, [
			"model-value",
			"options",
			"placeholder",
			"size"
		])]),
		createBaseVNode("li", null, [$setup.showValueToggle ? (openBlock(), createBlock($setup["Switch"], {
			key: 0,
			label: $setup.valueToggleLabel,
			"model-value": $props.condition.value === "true",
			"onUpdate:modelValue": $setup.onValueToggle
		}, null, 8, ["label", "model-value"])) : $setup.showValueDropdown ? (openBlock(), createBlock($setup["Combobox"], {
			key: 1,
			"model-value": $props.condition.value,
			options: $setup.valueOptions,
			"option-label": "label",
			"option-value": "value",
			placeholder: _ctx.__("Value"),
			size: $props.size,
			searchable: "",
			"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.update("value", $event)),
			"onSearch:blur": $setup.onValueBlur
		}, null, 8, [
			"model-value",
			"options",
			"placeholder",
			"size"
		])) : (openBlock(), createBlock($setup["Input"], {
			key: 2,
			"model-value": $props.condition.value,
			placeholder: _ctx.__("Value"),
			size: $props.size,
			type: $setup.showNumberInput ? "number" : "text",
			"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.update("value", $event))
		}, null, 8, [
			"model-value",
			"placeholder",
			"size",
			"type"
		]))])
	], 64);
}
var Condition_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Condition.vue"]]);
export { Condition_default as t };
