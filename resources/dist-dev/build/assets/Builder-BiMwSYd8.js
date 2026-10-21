import { n as __exportAll, r as __toESM } from "./rolldown-runtime-SbdDWaPq.js";
import { $ as useTemplateRef, At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, G as renderSlot, H as provide, J as resolveDynamicComponent, L as onMounted, N as nextTick, R as onUnmounted, S as createTextVNode, T as defineComponent, W as renderList, _ as createBlock, _t as ref, at as withDirectives, b as createSlots, c as vShow, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, l as withKeys, p as Teleport, q as resolveDirective, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-DZ9hA_XK.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { G as axios, o as usePage } from "./index.esm-D7PRwS1l.js";
import { $ as Item_default$1, Bn as Sortable, Cn as ConfirmationModal_default, Ct as Item_default, Gt as Label_default, I as Container_default, Jn as createContext, K as KEYS, Ln as Combobox_default, U as Fields_default, Un as require_fuzzysort, Ut as Header_default$1, Vn as Draggable, Vt as Input_default, Wt as Field_default, at as List_default, cr as Card_default, di as Badge_default, et as Group_default$1, in as Description_default, ir as Panel_default, it as Tabs_default, mi as mapValues, nt as Textarea_default, oi as Button_default, or as Header_default, pt as Content_default, q as FieldsProvider_default, rt as Trigger_default, sr as Heading_default, wi as Icon_default, wt as Group_default } from "./ui-B0AhlaTC.js";
import { h as nanoid, s as uniqid, t as __$1 } from "./globals-DT9jB_va.js";
import { M as debounce_default, g as keys, x as progress } from "./api-CwAMx3xr.js";
import { t as Condition_default } from "./Condition-CexQ3dtu.js";
import { n as useFieldNumberingPreference, t as FieldNumberingToggle_default } from "./FieldNumberingToggle-ChP4_s-Z.js";
import { i as collectsValue, n as categories, r as categoryColorClasses, t as FieldNumber_default } from "./FieldNumber-DniEyU2j.js";
import { D as Builder_default$2, E as Builder_default$1, T as WidthSelector_default, c as Layout_default, r as Head_default } from "./index-Mrn_b1WS.js";
import { t as FormStatusIndicator_default } from "./FormStatusIndicator-QnHW78yu.js";
import { t as Layout_default$1 } from "./Layout-CqZioJdW.js";
import { t as LayoutPanel_default } from "./LayoutPanel-uI4NDNeo.js";
import { t as PanelLayout_default } from "./PanelLayout-CT7LfZwO.js";
// resources/js/components/forms/builder/pages/PageRule.vue
var _sfc_main$2 = {
	__name: "PageRule",
	props: {
		rule: {
			type: Object,
			required: true
		},
		suggestableFields: {
			type: Array,
			required: true
		},
		pageDestinationOptions: {
			type: Array,
			required: true
		},
		fieldtypes: {
			type: Array,
			default: () => []
		}
	},
	emits: ["update:rule", "remove"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const joinOptions = [{
			label: __("All of the conditions pass"),
			short_label: __("And"),
			value: "and"
		}, {
			label: __("Any of the conditions pass"),
			short_label: __("Or"),
			value: "or"
		}];
		const conditions = computed({
			get: () => props.rule.conditions,
			set: (value) => update("conditions", value)
		});
		const destination = computed({
			get: () => props.rule.destination,
			set: (value) => update("destination", value)
		});
		const destinationPageIsMissing = computed(() => {
			if (!destination.value) return false;
			return !props.pageDestinationOptions.find((option) => option.value === destination.value);
		});
		const update = (key, value) => emit("update:rule", {
			...props.rule,
			[key]: value
		});
		const addCondition = () => {
			const newConditions = [...conditions.value, {
				_id: nanoid(),
				join: "and",
				field: null,
				operator: "equals",
				value: null
			}];
			update("conditions", newConditions);
		};
		const updateCondition = (index, condition) => {
			const newConditions = [...conditions.value];
			newConditions[index] = condition;
			update("conditions", newConditions);
		};
		const updateConditionJoin = (index, join) => {
			const newConditions = [...conditions.value];
			newConditions[index] = {
				...newConditions[index],
				join
			};
			update("conditions", newConditions);
		};
		const removeCondition = (index) => {
			const newConditions = [...conditions.value];
			newConditions.splice(index, 1);
			update("conditions", newConditions);
		};
		const getFieldtypeCategory = (fieldtypeHandle) => {
			const categoryKey = (props.fieldtypes?.find((field) => field.handle === fieldtypeHandle))?.categories?.[0] || "other";
			return categories[categoryKey] ?? categories.other;
		};
		const fieldIconClasses = (fieldtypeHandle) => `size-4 shrink-0 ${categoryColorClasses[getFieldtypeCategory(fieldtypeHandle)?.color]?.icon || "text-gray-600 dark:text-gray-400"}`;
		const findSuggestableField = (handle) => props.suggestableFields.find((f) => f.handle === handle);
		const shouldBeIndented = (index) => {
			if (index === 0) return false;
			return conditions.value[index].join === "and";
		};
		const __returned__ = {
			emit,
			props,
			joinOptions,
			conditions,
			destination,
			destinationPageIsMissing,
			update,
			addCondition,
			updateCondition,
			updateConditionJoin,
			removeCondition,
			getFieldtypeCategory,
			fieldIconClasses,
			findSuggestableField,
			shouldBeIndented,
			get Button() {
				return Button_default;
			},
			get Combobox() {
				return Combobox_default;
			},
			get Icon() {
				return Icon_default;
			},
			computed,
			get uniqid() {
				return nanoid;
			},
			Condition: Condition_default,
			FieldNumber: FieldNumber_default,
			get categories() {
				return categories;
			},
			get categoryColorClasses() {
				return categoryColorClasses;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$15 = {
	class: "group/rule",
	"data-logic-text": ""
};
var _hoisted_2$15 = { class: "logic-text" };
var _hoisted_3$15 = {
	key: 0,
	class: "flex items-center"
};
var _hoisted_4$14 = {
	class: "logic-text-badge logic-text__condition",
	"aria-hidden": "true"
};
var _hoisted_5$14 = { class: "ms-0.5 mb-2.5 inline-flex items-center gap-1.5 opacity-0 pointer-events-none transition-opacity group-hover/rule:opacity-100 group-hover/rule:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto" };
var _hoisted_6$11 = {
	key: 1,
	class: "flex items-center gap-0.25"
};
var _hoisted_7$6 = { class: "logic-text__condition" };
var _hoisted_8$5 = { class: "block truncate" };
var _hoisted_9$5 = { class: "ms-0.5 mb-2.5 inline-flex items-center gap-1.5 opacity-0 pointer-events-none transition-opacity group-hover/rule:opacity-100 group-hover/rule:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto" };
var _hoisted_10$3 = { class: "inline-flex items-center gap-2" };
var _hoisted_11$3 = { class: "truncate" };
var _hoisted_12$2 = { class: "inline-flex items-center gap-2 truncate" };
var _hoisted_13$2 = { class: "truncate" };
var _hoisted_14$1 = {
	class: "logic-text-badge logic-text__condition",
	"aria-hidden": "true"
};
var _hoisted_15$1 = {
	class: "logic-text-badge",
	"aria-hidden": "true"
};
var _hoisted_16$1 = { class: "flex min-w-0 gap-2 items-center text-left" };
var _hoisted_17$1 = { class: "block truncate" };
var _hoisted_18$1 = {
	key: 0,
	class: "flex min-w-0 items-center gap-1 -ms-0.75"
};
var _hoisted_19$1 = { class: "flex shrink-0 items-center justify-center size-6 text-red-600 dark:text-red-500" };
var _hoisted_20$1 = { class: "block truncate text-red-600 dark:text-red-500" };
var _hoisted_21$1 = {
	key: 1,
	class: "flex min-w-0 items-center gap-1 -ms-0.75"
};
var _hoisted_22$1 = { class: "flex shrink-0 items-center justify-center size-6 text-gray-500 dark:text-gray-300" };
var _hoisted_23$1 = { class: "block truncate" };
function _sfc_render$15(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$15, [createBaseVNode("div", _hoisted_2$15, [createBaseVNode("ol", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.conditions, (condition, index) => {
		return openBlock(), createElementBlock("li", {
			key: condition._id,
			class: normalizeClass({ "ms-(--inner-indent) indented-condition": $setup.shouldBeIndented(index) })
		}, [index === 0 ? (openBlock(), createElementBlock("div", _hoisted_3$15, [createBaseVNode("div", _hoisted_4$14, toDisplayString(_ctx.__("If")), 1), createBaseVNode("div", _hoisted_5$14, [createVNode($setup["Button"], {
			size: "sm",
			inset: "",
			variant: "subtle",
			class: "size-7 [&_svg]:opacity-50 rounded-full ms-[0.025rem]",
			icon: "trash",
			"aria-label": _ctx.__("Delete rule"),
			onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("remove"))
		}, null, 8, ["aria-label"])])])) : (openBlock(), createElementBlock("div", _hoisted_6$11, [createBaseVNode("div", _hoisted_7$6, [createVNode($setup["Combobox"], {
			"model-value": condition.join || "and",
			class: "max-w-24",
			size: "sm",
			options: $setup.joinOptions,
			"option-label": "label",
			"option-value": "value",
			placeholder: _ctx.__("And"),
			searchable: false,
			"adaptive-width": "",
			"onUpdate:modelValue": ($event) => $setup.updateConditionJoin(index, $event)
		}, {
			"selected-option": withCtx(({ option }) => [createBaseVNode("span", _hoisted_8$5, toDisplayString(option.short_label), 1)]),
			_: 1
		}, 8, [
			"model-value",
			"placeholder",
			"onUpdate:modelValue"
		])]), createBaseVNode("div", _hoisted_9$5, [createVNode($setup["Button"], {
			size: "sm",
			inset: "",
			variant: "subtle",
			class: "size-7 rounded-full [&_div]:opacity-75 [&_div]:-translate-y-px",
			text: "×",
			"aria-label": _ctx.__("Remove condition"),
			onClick: ($event) => $setup.removeCondition(index)
		}, null, 8, ["aria-label", "onClick"])])])), createBaseVNode("ol", null, [createVNode($setup["Condition"], {
			condition,
			"suggestable-fields": $props.suggestableFields,
			"exclude-operators": ["custom"],
			size: "sm",
			"onUpdate:condition": ($event) => $setup.updateCondition(index, $event)
		}, {
			"field-option": withCtx(({ value, label }) => [createBaseVNode("span", _hoisted_10$3, [
				createVNode($setup["FieldNumber"], { "field-key": value }, null, 8, ["field-key"]),
				$setup.findSuggestableField(value)?.icon ? (openBlock(), createBlock($setup["Icon"], {
					key: 0,
					name: $setup.findSuggestableField(value).icon,
					class: normalizeClass($setup.fieldIconClasses($setup.findSuggestableField(value).config.type))
				}, null, 8, ["name", "class"])) : createCommentVNode("", true),
				createBaseVNode("span", _hoisted_11$3, toDisplayString(_ctx.__(label)), 1)
			])]),
			"field-selected": withCtx(({ option, field: selectedField }) => [createBaseVNode("span", _hoisted_12$2, [
				createVNode($setup["FieldNumber"], { "field-key": option.value }, null, 8, ["field-key"]),
				selectedField?.icon ? (openBlock(), createBlock($setup["Icon"], {
					key: 0,
					name: selectedField.icon,
					class: normalizeClass($setup.fieldIconClasses(selectedField.config.type))
				}, null, 8, ["name", "class"])) : createCommentVNode("", true),
				createBaseVNode("span", _hoisted_13$2, toDisplayString(_ctx.__($setup.findSuggestableField(option.value)?.config?.display ?? option.value)), 1)
			])]),
			_: 1
		}, 8, [
			"condition",
			"suggestable-fields",
			"onUpdate:condition"
		])])], 2);
	}), 128)), createBaseVNode("li", null, [createBaseVNode("div", _hoisted_14$1, toDisplayString(_ctx.__("Then")), 1), createBaseVNode("ol", null, [createBaseVNode("li", null, [createBaseVNode("div", _hoisted_15$1, toDisplayString(_ctx.__("Go to")), 1)]), createBaseVNode("li", null, [createVNode($setup["Combobox"], {
		modelValue: $setup.destination,
		"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.destination = $event),
		size: "sm",
		variant: "default",
		options: $props.pageDestinationOptions,
		"option-label": "label",
		"option-value": "value",
		placeholder: _ctx.__("Select a page"),
		searchable: ""
	}, {
		option: withCtx(({ icon, label }) => [createBaseVNode("div", _hoisted_16$1, [icon ? (openBlock(), createBlock($setup["Icon"], {
			key: 0,
			name: icon,
			class: "size-4 shrink-0 text-gray-500 dark:text-gray-300"
		}, null, 8, ["name"])) : createCommentVNode("", true), createBaseVNode("span", _hoisted_17$1, toDisplayString(label), 1)])]),
		"selected-option": withCtx(({ option }) => [$setup.destinationPageIsMissing ? (openBlock(), createElementBlock("div", _hoisted_18$1, [createBaseVNode("div", _hoisted_19$1, [createVNode($setup["Icon"], {
			name: "trash",
			class: "size-4 shrink-0 text-red-600 dark:text-red-500"
		})]), createBaseVNode("span", _hoisted_20$1, toDisplayString(_ctx.__("Deleted page")), 1)])) : (openBlock(), createElementBlock("div", _hoisted_21$1, [createBaseVNode("div", _hoisted_22$1, [option.icon ? (openBlock(), createBlock($setup["Icon"], {
			key: 0,
			name: option.icon,
			class: "size-4 shrink-0 text-gray-500 dark:text-gray-300"
		}, null, 8, ["name"])) : createCommentVNode("", true)]), createBaseVNode("span", _hoisted_23$1, toDisplayString(option.label), 1)]))]),
		_: 1
	}, 8, [
		"modelValue",
		"options",
		"placeholder"
	])])])])]), createVNode($setup["Button"], {
		size: "sm",
		variant: "subtle",
		class: "ms-4 bg-transparent!",
		text: _ctx.__("+ Add Condition"),
		onClick: $setup.addCondition
	}, null, 8, ["text"])])]);
}
var PageRule_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$15], ["__file", "PageRule.vue"]]);
// resources/js/components/forms/builder/pages/PageRuleBuilder.vue
var _sfc_main$1 = {
	__name: "PageRuleBuilder",
	props: {
		rules: {
			type: Array,
			required: true
		},
		suggestableFields: {
			type: Array,
			required: true
		},
		pageDestinationOptions: {
			type: Array,
			required: true
		},
		fieldtypes: {
			type: Array,
			default: () => []
		}
	},
	emits: ["update:rules"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const updateRule = (index, rule) => {
			const newRules = [...props.rules];
			newRules[index] = rule;
			emit("update:rules", newRules);
		};
		const addRule = () => {
			const defaultDestination = props.pageDestinationOptions.length > 0 ? props.pageDestinationOptions[0].value : null;
			const newRules = [...props.rules, {
				_id: nanoid(),
				conditions: [{
					_id: nanoid(),
					field: null,
					operator: "equals",
					value: null
				}],
				destination: defaultDestination
			}];
			emit("update:rules", newRules);
		};
		const removeRule = (index) => {
			const newRules = [...props.rules];
			newRules.splice(index, 1);
			emit("update:rules", newRules);
		};
		const __returned__ = {
			emit,
			props,
			updateRule,
			addRule,
			removeRule,
			get Button() {
				return Button_default;
			},
			get uniqid() {
				return nanoid;
			},
			PageRule: PageRule_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$14 = { class: "group/logic-tab space-y-6" };
var _hoisted_2$14 = {
	key: 0,
	class: "my-6 border-t border-dashed border-gray-400 dark:border-gray-700"
};
var _hoisted_3$14 = { class: "mt-8 mb-6 pt-4 border-t border-dashed border-gray-300 dark:border-gray-700" };
function _sfc_render$14(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$14, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.rules, (rule, index) => {
		return openBlock(), createElementBlock(Fragment, { key: rule._id }, [index > 0 ? (openBlock(), createElementBlock("div", _hoisted_2$14)) : createCommentVNode("", true), createVNode($setup["PageRule"], {
			rule,
			"suggestable-fields": $props.suggestableFields,
			"page-destination-options": $props.pageDestinationOptions,
			fieldtypes: $props.fieldtypes,
			"onUpdate:rule": ($event) => $setup.updateRule(index, $event),
			onRemove: ($event) => $setup.removeRule(index)
		}, null, 8, [
			"rule",
			"suggestable-fields",
			"page-destination-options",
			"fieldtypes",
			"onUpdate:rule",
			"onRemove"
		])], 64);
	}), 128)), createBaseVNode("div", _hoisted_3$14, [createVNode($setup["Button"], {
		size: "sm",
		text: _ctx.__("+ Add Rule"),
		onClick: $setup.addRule
	}, null, 8, ["text"])])]);
}
var PageRuleBuilder_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$14], ["__file", "PageRuleBuilder.vue"]]);
// resources/js/components/forms/builder/ActionInspector.vue?vue&type=script&setup=true&lang.ts
var ActionInspectorTabs = /* @__PURE__ */ function(ActionInspectorTabs) {
	ActionInspectorTabs["Settings"] = "settings";
	ActionInspectorTabs["Logic"] = "logic";
	return ActionInspectorTabs;
}(ActionInspectorTabs || {});
var ActionInspector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ActionInspector",
	setup(__props, { expose: __expose }) {
		__expose();
		const { dirty, fieldtypes, inspecting: page, pages } = injectBuilderContext();
		const activeTab = ref("settings");
		const pageIndex = computed(() => pages.value.findIndex((p) => p._id === page.value._id));
		const isFirstPage = computed(() => pageIndex.value === 0);
		const isLastPage = computed(() => pageIndex.value === pages.value.length - 1);
		const title = computed(() => isLastPage.value ? __$1("Submit button") : __$1("Page Buttons"));
		const submitButtonLabel = computed(() => isLastPage.value ? __$1("Submit Button Label") : __$1("Next Button Label"));
		const submitButtonPlaceholder = computed(() => isLastPage.value ? __$1("Submit") : __$1("Next Page"));
		const suggestableConditionFields = computed(() => {
			return pages.value.slice(0, pageIndex.value + 1).flatMap((page) => page.sections).flatMap((section) => section.fields).filter((f) => f.type === "import" || f.handle).map((f) => ({
				handle: f.handle,
				config: {
					...f.config,
					type: f.fieldtype
				},
				icon: f.icon
			}));
		});
		const pageDestinationOptions = computed(() => {
			const currentPageIndex = pageIndex.value;
			return pages.value.slice(currentPageIndex + 1).map((p, index) => {
				const actualIndex = currentPageIndex + 1 + index;
				const label = p.display ? __$1(p.display) : __$1("Page :current of :total", {
					current: actualIndex + 1,
					total: pages.value.length
				});
				return {
					value: p._id,
					label,
					icon: "page"
				};
			});
		});
		watch(page, () => {
			if (activeTab.value === "logic" && isLastPage.value) activeTab.value = "settings";
		});
		watch(() => page.value.button_label, dirty);
		watch(() => page.value.show_previous_button, dirty);
		watch(() => page.value.previous_page_label, dirty);
		watch(() => page.value.rules, dirty, { deep: true });
		const addPreviousButton = () => {
			page.value.show_previous_button = true;
		};
		const removePreviousButton = () => {
			page.value.show_previous_button = false;
			page.value.previous_page_label = null;
		};
		const __returned__ = {
			dirty,
			fieldtypes,
			page,
			pages,
			ActionInspectorTabs,
			activeTab,
			pageIndex,
			isFirstPage,
			isLastPage,
			title,
			submitButtonLabel,
			submitButtonPlaceholder,
			suggestableConditionFields,
			pageDestinationOptions,
			addPreviousButton,
			removePreviousButton,
			get Button() {
				return Button_default;
			},
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Input() {
				return Input_default;
			},
			get TabContent() {
				return Content_default;
			},
			get TabList() {
				return List_default;
			},
			get Tabs() {
				return Tabs_default;
			},
			get TabTrigger() {
				return Trigger_default;
			},
			PageRuleBuilder: PageRuleBuilder_default,
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/ActionInspector.vue
var _hoisted_1$13 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$13 = { class: "space-y-6 pt-8" };
var _hoisted_3$13 = { class: "flex items-center gap-2.5" };
var _hoisted_4$13 = { class: "size-4" };
var _hoisted_5$13 = ["href"];
var _hoisted_6$10 = { class: "grid *:[grid-area:1/1]" };
var _hoisted_7$5 = { class: "space-y-6 pt-8" };
var _hoisted_8$4 = { class: "flex items-center gap-2.5" };
var _hoisted_9$4 = { class: "size-4" };
var _hoisted_10$2 = ["href"];
var _hoisted_11$2 = { class: "grid *:[grid-area:1/1]" };
function _sfc_render$13(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$13, [createVNode($setup["Tabs"], {
		modelValue: $setup.activeTab,
		"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.activeTab = $event),
		"unmount-on-hide": false
	}, {
		default: withCtx(() => [
			createVNode($setup["TabList"], { class: "inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!" }, {
				default: withCtx(() => [createVNode($setup["TabTrigger"], {
					name: $setup.ActionInspectorTabs.Settings,
					text: $setup.__("Settings")
				}, null, 8, ["name", "text"]), !$setup.isLastPage ? (openBlock(), createBlock($setup["TabTrigger"], {
					key: 0,
					name: $setup.ActionInspectorTabs.Logic,
					text: $setup.__("Logic")
				}, null, 8, ["name", "text"])) : createCommentVNode("", true)]),
				_: 1
			}),
			createVNode($setup["TabContent"], { name: $setup.ActionInspectorTabs.Settings }, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_2$13, [
					createBaseVNode("div", _hoisted_3$13, [createBaseVNode("div", _hoisted_4$13, [createVNode($setup["Icon"], {
						name: "page",
						class: "size-4 text-gray-500 dark:text-gray-300"
					})]), createBaseVNode("a", {
						href: `#actions-${$setup.page._id}`,
						class: "inline-flex items-center gap-1.5 text-xl font-medium antialiased"
					}, [createTextVNode(toDisplayString($setup.title) + " ", 1), createBaseVNode("div", _hoisted_6$10, [createVNode($setup["Icon"], {
						name: "arrow-up",
						"data-field-direction-up": "",
						"aria-hidden": "true"
					}), createVNode($setup["Icon"], {
						name: "arrow-down",
						"data-field-direction-down": "",
						"aria-hidden": "true"
					})])], 8, _hoisted_5$13)]),
					createVNode($setup["Field"], { label: $setup.submitButtonLabel }, {
						default: withCtx(() => [createVNode($setup["Input"], {
							modelValue: $setup.page.button_label,
							"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.page.button_label = $event),
							placeholder: $setup.submitButtonPlaceholder,
							focus: ""
						}, null, 8, ["modelValue", "placeholder"])]),
						_: 1
					}, 8, ["label"]),
					!$setup.isFirstPage ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [!$setup.page.show_previous_button ? (openBlock(), createBlock($setup["Button"], {
						key: 0,
						size: "sm",
						text: $setup.__("Add Previous Button"),
						onClick: $setup.addPreviousButton
					}, null, 8, ["text"])) : (openBlock(), createBlock($setup["Field"], {
						key: 1,
						label: $setup.__("Previous Button Label")
					}, {
						actions: withCtx(() => [createVNode($setup["Button"], {
							size: "sm",
							variant: "ghost",
							icon: "trash",
							"aria-label": $setup.__("Remove Previous Button"),
							onClick: $setup.removePreviousButton
						}, null, 8, ["aria-label"])]),
						default: withCtx(() => [createVNode($setup["Input"], {
							modelValue: $setup.page.previous_page_label,
							"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.page.previous_page_label = $event),
							placeholder: $setup.__("Previous Page")
						}, null, 8, ["modelValue", "placeholder"])]),
						_: 1
					}, 8, ["label"]))], 64)) : createCommentVNode("", true)
				])]),
				_: 1
			}, 8, ["name"]),
			!$setup.isLastPage ? (openBlock(), createBlock($setup["TabContent"], {
				key: 0,
				name: $setup.ActionInspectorTabs.Logic
			}, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_7$5, [createBaseVNode("div", _hoisted_8$4, [createBaseVNode("div", _hoisted_9$4, [createVNode($setup["Icon"], {
					name: "page",
					class: "size-4 text-gray-500 dark:text-gray-300"
				})]), createBaseVNode("a", {
					href: `#actions-${$setup.page._id}`,
					class: "inline-flex items-center gap-1.5 text-xl font-medium antialiased"
				}, [createTextVNode(toDisplayString($setup.title) + " ", 1), createBaseVNode("div", _hoisted_11$2, [createVNode($setup["Icon"], {
					name: "arrow-up",
					"data-field-direction-up": "",
					"aria-hidden": "true"
				}), createVNode($setup["Icon"], {
					name: "arrow-down",
					"data-field-direction-down": "",
					"aria-hidden": "true"
				})])], 8, _hoisted_10$2)]), createVNode($setup["PageRuleBuilder"], {
					rules: $setup.page.rules,
					"onUpdate:rules": _cache[2] || (_cache[2] = ($event) => $setup.page.rules = $event),
					"suggestable-fields": $setup.suggestableConditionFields,
					"page-destination-options": $setup.pageDestinationOptions,
					fieldtypes: $setup.fieldtypes
				}, null, 8, [
					"rules",
					"suggestable-fields",
					"page-destination-options",
					"fieldtypes"
				])])]),
				_: 1
			}, 8, ["name"])) : createCommentVNode("", true)
		]),
		_: 1
	}, 8, ["modelValue"])]);
}
var ActionInspector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(ActionInspector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$13], ["__file", "ActionInspector.vue"]]);
// resources/js/components/forms/builder/EmptyInspector.vue
var _sfc_main = {
	__name: "EmptyInspector",
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = { get Icon() {
			return Icon_default;
		} };
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$12 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$12 = { class: "space-y-4.5 pt-5 px-2 @xs:px-4" };
var _hoisted_3$12 = { class: "flex items-center gap-2" };
var _hoisted_4$12 = { class: "size-4" };
var _hoisted_5$12 = { class: "inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased" };
var _hoisted_6$9 = { class: "truncate" };
var _hoisted_7$4 = { class: "text-zinc-600 dark:text-zinc-400 text-[0.95rem] text-pretty" };
function _sfc_render$12(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$12, [createBaseVNode("div", _hoisted_2$12, [createBaseVNode("div", _hoisted_3$12, [createBaseVNode("div", _hoisted_4$12, [createVNode($setup["Icon"], {
		name: "forms",
		class: "size-4 text-gray-500 dark:text-gray-300"
	})]), createBaseVNode("span", _hoisted_5$12, [createBaseVNode("span", _hoisted_6$9, toDisplayString(_ctx.__("Edit this form")), 1)])]), createBaseVNode("p", _hoisted_7$4, toDisplayString(_ctx.__("statamic::messages.form_builder_empty_inspector")), 1)])]);
}
var EmptyInspector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render$12], ["__file", "EmptyInspector.vue"]]);
// resources/js/composables/forms/field-conditions.js
var LOGIC_KEYS = [
	"hidden",
	"if",
	"unless",
	"if_any",
	"unless_any",
	"always_save"
];
function writeFieldConditions(field, conditions) {
	const config = { ...field.config };
	const merged = {
		hidden: conditions.hidden ?? config.hidden ?? false,
		if: conditions.if || null,
		unless: conditions.unless || null,
		if_any: conditions.if_any || null,
		unless_any: conditions.unless_any || null,
		always_save: conditions.always_save ?? config.always_save ?? false
	};
	LOGIC_KEYS.forEach((key) => {
		if (merged[key]) config[key] = merged[key];
		else delete config[key];
	});
	field.config = config;
	if (field.type === "reference") {
		const overrides = new Set((field.config_overrides ?? []).filter((key) => !LOGIC_KEYS.includes(key)));
		LOGIC_KEYS.forEach((key) => key in config && overrides.add(key));
		field.config_overrides = [...overrides];
	}
}
// resources/js/components/forms/builder/FieldInspector.vue?vue&type=script&setup=true&lang.ts
var FieldInspectorTabs = /* @__PURE__ */ function(FieldInspectorTabs) {
	FieldInspectorTabs["Settings"] = "settings";
	FieldInspectorTabs["Logic"] = "logic";
	FieldInspectorTabs["Validation"] = "validation";
	return FieldInspectorTabs;
}(FieldInspectorTabs || {});
var FieldInspector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FieldInspector",
	setup(__props, { expose: __expose }) {
		__expose();
		const cache = /* @__PURE__ */ new Map();
		const { dirty, errors: contextErrors, fieldtypes, form, inspecting: field, pages, showFieldDirection, withoutDirtying } = injectBuilderContext();
		const errors = computed(() => {
			const result = {};
			const prefix = `${field.value._id}.`;
			const allErrors = contextErrors.value ?? {};
			for (const [key, messages] of Object.entries(allErrors)) if (key.startsWith(prefix)) {
				const configField = key.slice(prefix.length);
				result[configField] = messages;
			}
			return result;
		});
		const loading = ref(true);
		const values = ref(null);
		const meta = ref(null);
		const originValues = ref(null);
		const originMeta = ref(null);
		const fieldtype = ref(null);
		const blueprint = ref(null);
		const activeTab = ref("settings");
		const modifiedFields = ref([]);
		let skipNextPreviewUpdate = false;
		const extraValues = computed(() => ({ isNew: !!field.value.isNew }));
		const shouldShowValidationTab = computed(() => collectsValue(getFieldtypeCategoryHandle(field.value.config.type)));
		const adjustedBlueprint = computed(() => {
			const bp = JSON.parse(JSON.stringify(blueprint.value));
			bp.tabs[0].sections.forEach((section) => {
				section.fields.forEach((field) => {
					field.localizable = true;
				});
			});
			return bp;
		});
		const load = () => {
			modifiedFields.value = field.value.config_overrides ?? [];
			const cached = cache.get(field.value._id);
			if (cached) {
				loading.value = false;
				fieldtype.value = cached.fieldtype;
				blueprint.value = cached.blueprint;
				skipNextPreviewUpdate = true;
				withoutDirtying(() => values.value = cached.values);
				meta.value = cached.meta;
				originValues.value = cached.originValues;
				originMeta.value = cached.originMeta;
				return;
			}
			loading.value = true;
			axios.post(cp_url(`forms/${form.handle}/builder/fields/edit`), {
				type: field.value.fieldtype,
				reference: field.value.type === "reference" ? field.value.field_reference : false,
				values: {
					...field.value.config,
					handle: field.value.handle
				}
			}).then((response) => {
				loading.value = false;
				fieldtype.value = response.data.fieldtype;
				blueprint.value = response.data.blueprint;
				skipNextPreviewUpdate = true;
				withoutDirtying(() => values.value = response.data.values);
				meta.value = response.data.meta;
				originValues.value = response.data.originValues;
				originMeta.value = response.data.originMeta;
				cache.set(field.value._id, {
					fieldtype: response.data.fieldtype,
					blueprint: response.data.blueprint,
					values: response.data.values,
					meta: response.data.meta,
					originValues: response.data.originValues,
					originMeta: response.data.originMeta
				});
			}).catch((e) => {
				if (e.response && e.response.status === 422) {
					const { message } = e.response.data;
					Statamic.$toast.error(message);
				} else Statamic.$toast.error(e.response?.data?.message || __("Something went wrong"));
			});
		};
		const updatePreview = debounce_default(() => {
			const fieldId = field.value._id;
			axios.post(cp_url(`forms/${form.handle}/builder/fields/update`), {
				type: field.value.fieldtype,
				values: values.value
			}).then((response) => {
				if (field.value._id !== fieldId) return;
				const { handle, ...config } = response.data.values;
				field.value.config = config;
				if (response.data.preview) field.value.preview = {
					config: {
						...response.data.preview.config,
						handle: field.value.handle ?? field.value._id
					},
					value: response.data.preview.value,
					meta: response.data.preview.meta
				};
				cache.delete(field.value._id);
			});
		}, 500);
		const currentPageIndex = computed(() => {
			return pages.value.findIndex((page) => page.sections.some((section) => section.fields.some((f) => f._id === field.value._id)));
		});
		const suggestableConditionFields = computed(() => {
			return pages.value.slice(0, currentPageIndex.value + 1).flatMap((page) => page.sections).flatMap((section) => section.fields).filter((f) => f._id !== field.value._id).filter((f) => f.type === "import" || f.handle).filter((f) => f.type === "import" || collectsValue(getFieldtypeCategoryHandle(f.config.type))).map((f) => ({
				handle: f.handle,
				config: {
					...f.config,
					type: f.fieldtype
				},
				icon: f.icon
			}));
		});
		const getFieldtypeCategoryHandle = (fieldtypeHandle) => {
			return (fieldtypes?.find((field) => field.handle === fieldtypeHandle))?.categories?.[0] || "other";
		};
		const getFieldtypeCategory = (fieldtypeHandle) => categories[getFieldtypeCategoryHandle(fieldtypeHandle)] ?? categories.other;
		const fieldIconClasses = (fieldtypeHandle) => `size-4 shrink-0 ${categoryColorClasses[getFieldtypeCategory(fieldtypeHandle)?.color]?.icon}`;
		const findSuggestableField = (handle) => suggestableConditionFields.value.find((f) => f.handle === handle);
		const updateFieldConditions = (conditions) => {
			writeFieldConditions(field.value, conditions);
			Object.assign(values.value, conditions);
			dirty();
		};
		watch(field, () => {
			updatePreview.cancel();
			loading.value = !cache.has(field.value._id);
			load();
			if (activeTab.value === "validation" && !shouldShowValidationTab.value) activeTab.value = "settings";
		});
		watch(values, () => {
			dirty();
			if (skipNextPreviewUpdate) {
				skipNextPreviewUpdate = false;
				return;
			}
			updatePreview();
		}, { deep: true });
		watch(() => values.value?.handle, (handle) => {
			if (field.value.isNew && handle !== void 0) field.value.handle = handle;
		});
		watch(modifiedFields, (fields) => {
			if (field.value.type === "reference") field.value.config_overrides = fields;
		}, { deep: true });
		onMounted(() => load());
		const __returned__ = {
			cache,
			dirty,
			contextErrors,
			fieldtypes,
			form,
			field,
			pages,
			showFieldDirection,
			withoutDirtying,
			errors,
			FieldInspectorTabs,
			loading,
			values,
			meta,
			originValues,
			originMeta,
			fieldtype,
			blueprint,
			activeTab,
			modifiedFields,
			get skipNextPreviewUpdate() {
				return skipNextPreviewUpdate;
			},
			set skipNextPreviewUpdate(v) {
				skipNextPreviewUpdate = v;
			},
			extraValues,
			shouldShowValidationTab,
			adjustedBlueprint,
			load,
			updatePreview,
			currentPageIndex,
			suggestableConditionFields,
			getFieldtypeCategoryHandle,
			getFieldtypeCategory,
			fieldIconClasses,
			findSuggestableField,
			updateFieldConditions,
			get Icon() {
				return Icon_default;
			},
			get PublishContainer() {
				return Container_default;
			},
			get PublishFields() {
				return Fields_default;
			},
			get PublishFieldsProvider() {
				return FieldsProvider_default;
			},
			get TabContent() {
				return Content_default;
			},
			get TabList() {
				return List_default;
			},
			get Tabs() {
				return Tabs_default;
			},
			get TabTrigger() {
				return Trigger_default;
			},
			FieldValidationBuilder: Builder_default$1,
			FieldConditionsBuilder: Builder_default$2,
			FieldNumber: FieldNumber_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/FieldInspector.vue
var _hoisted_1$11 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$11 = {
	key: 0,
	class: "absolute inset-0 z-200 flex items-center justify-center text-center"
};
var _hoisted_3$11 = { class: "space-y-6 pt-8" };
var _hoisted_4$11 = {
	"data-field-settings": "",
	class: "flex items-center gap-2.5"
};
var _hoisted_5$11 = { class: "size-4" };
var _hoisted_6$8 = ["href"];
var _hoisted_7$3 = { class: "truncate" };
var _hoisted_8$3 = {
	key: 0,
	class: "grid *:[grid-area:1/1]"
};
var _hoisted_9$3 = { class: "space-y-6 pt-8" };
var _hoisted_10$1 = { class: "flex items-center gap-2.5" };
var _hoisted_11$1 = { class: "size-4" };
var _hoisted_12$1 = ["href"];
var _hoisted_13$1 = { class: "truncate" };
var _hoisted_14 = {
	key: 0,
	class: "grid *:[grid-area:1/1]"
};
var _hoisted_15 = { class: "inline-flex items-center gap-2" };
var _hoisted_16 = { class: "truncate" };
var _hoisted_17 = { class: "inline-flex items-center gap-2 truncate" };
var _hoisted_18 = { class: "truncate" };
var _hoisted_19 = { class: "space-y-6 pt-8" };
var _hoisted_20 = {
	"data-field-settings": "",
	class: "flex items-center gap-2.5"
};
var _hoisted_21 = { class: "size-4" };
var _hoisted_22 = ["href"];
var _hoisted_23 = { class: "truncate" };
var _hoisted_24 = {
	key: 0,
	class: "grid *:[grid-area:1/1]"
};
function _sfc_render$11(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$11, [$setup.loading ? (openBlock(), createElementBlock("div", _hoisted_2$11, [createVNode($setup["Icon"], { name: "loading" })])) : (openBlock(), createBlock($setup["Tabs"], {
		key: 1,
		modelValue: $setup.activeTab,
		"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.activeTab = $event),
		"unmount-on-hide": false
	}, {
		default: withCtx(() => [
			createVNode($setup["TabList"], { class: "inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!" }, {
				default: withCtx(() => [
					createVNode($setup["TabTrigger"], {
						name: $setup.FieldInspectorTabs.Settings,
						text: _ctx.__("Settings")
					}, null, 8, ["name", "text"]),
					createVNode($setup["TabTrigger"], {
						name: $setup.FieldInspectorTabs.Logic,
						text: _ctx.__("Logic")
					}, null, 8, ["name", "text"]),
					$setup.shouldShowValidationTab ? (openBlock(), createBlock($setup["TabTrigger"], {
						key: 0,
						name: $setup.FieldInspectorTabs.Validation,
						text: _ctx.__("Validation")
					}, null, 8, ["name", "text"])) : createCommentVNode("", true)
				]),
				_: 1
			}),
			createVNode($setup["TabContent"], { name: $setup.FieldInspectorTabs.Settings }, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_3$11, [createBaseVNode("div", _hoisted_4$11, [createBaseVNode("div", _hoisted_5$11, [createVNode($setup["Icon"], {
					name: $setup.field.icon,
					class: "size-4 text-gray-500 dark:text-gray-300"
				}, null, 8, ["name"])]), createBaseVNode("a", {
					href: `#field-${$setup.field._id}`,
					class: "inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased"
				}, [createBaseVNode("span", _hoisted_7$3, toDisplayString(_ctx.__($setup.field.config.display)), 1), $setup.showFieldDirection ? (openBlock(), createElementBlock("div", _hoisted_8$3, [createVNode($setup["Icon"], {
					name: "arrow-up",
					"data-field-direction-up": "",
					"aria-hidden": "true"
				}), createVNode($setup["Icon"], {
					name: "arrow-down",
					"data-field-direction-down": "",
					"aria-hidden": "true"
				})])) : createCommentVNode("", true)], 8, _hoisted_6$8)]), createVNode($setup["PublishContainer"], {
					ref: "container",
					blueprint: $setup.adjustedBlueprint,
					meta: $setup.meta,
					errors: $setup.errors,
					"extra-values": $setup.extraValues,
					modelValue: $setup.values,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.values = $event),
					"modified-fields": $setup.modifiedFields,
					"onUpdate:modifiedFields": _cache[1] || (_cache[1] = ($event) => $setup.modifiedFields = $event),
					"origin-values": $setup.originValues,
					"origin-meta": $setup.originMeta,
					"track-dirty-state": false
				}, {
					default: withCtx(() => [createVNode($setup["PublishFieldsProvider"], { fields: $setup.adjustedBlueprint.tabs[0].sections[0].fields }, {
						default: withCtx(() => [createVNode($setup["PublishFields"])]),
						_: 1
					}, 8, ["fields"])]),
					_: 1
				}, 8, [
					"blueprint",
					"meta",
					"errors",
					"extra-values",
					"modelValue",
					"modified-fields",
					"origin-values",
					"origin-meta"
				])])]),
				_: 1
			}, 8, ["name"]),
			createVNode($setup["TabContent"], { name: $setup.FieldInspectorTabs.Logic }, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_9$3, [createBaseVNode("div", _hoisted_10$1, [createBaseVNode("div", _hoisted_11$1, [createVNode($setup["Icon"], {
					name: $setup.field.icon,
					class: "size-4 text-gray-500 dark:text-gray-300"
				}, null, 8, ["name"])]), createBaseVNode("a", {
					href: `#field-${$setup.field._id}`,
					class: "inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased"
				}, [createBaseVNode("span", _hoisted_13$1, toDisplayString(_ctx.__($setup.field.config.display)), 1), $setup.showFieldDirection ? (openBlock(), createElementBlock("div", _hoisted_14, [createVNode($setup["Icon"], {
					name: "arrow-up",
					"data-field-direction-up": "",
					"aria-hidden": "true"
				}), createVNode($setup["Icon"], {
					name: "arrow-down",
					"data-field-direction-down": "",
					"aria-hidden": "true"
				})])) : createCommentVNode("", true)], 8, _hoisted_12$1)]), createVNode($setup["FieldConditionsBuilder"], {
					config: $setup.field.config,
					"suggestable-fields": $setup.suggestableConditionFields,
					"allow-custom-conditions": false,
					"show-always-hide-option": true,
					size: "sm",
					onUpdated: $setup.updateFieldConditions
				}, {
					"field-option": withCtx(({ value, label }) => [createBaseVNode("span", _hoisted_15, [
						createVNode($setup["FieldNumber"], { "field-key": value }, null, 8, ["field-key"]),
						$setup.findSuggestableField(value)?.icon ? (openBlock(), createBlock($setup["Icon"], {
							key: 0,
							name: $setup.findSuggestableField(value).icon,
							class: normalizeClass($setup.fieldIconClasses($setup.findSuggestableField(value)?.config?.type))
						}, null, 8, ["name", "class"])) : createCommentVNode("", true),
						createBaseVNode("span", _hoisted_16, toDisplayString(_ctx.__(label)), 1)
					])]),
					"field-selected": withCtx(({ option, field: selectedField }) => [createBaseVNode("span", _hoisted_17, [
						createVNode($setup["FieldNumber"], { "field-key": option.value }, null, 8, ["field-key"]),
						selectedField?.icon ? (openBlock(), createBlock($setup["Icon"], {
							key: 0,
							name: selectedField.icon,
							class: normalizeClass($setup.fieldIconClasses(selectedField.config.type))
						}, null, 8, ["name", "class"])) : createCommentVNode("", true),
						createBaseVNode("span", _hoisted_18, toDisplayString(_ctx.__($setup.findSuggestableField(option.value)?.config?.display ?? option.value)), 1)
					])]),
					_: 1
				}, 8, ["config", "suggestable-fields"])])]),
				_: 1
			}, 8, ["name"]),
			createVNode($setup["TabContent"], { name: $setup.FieldInspectorTabs.Validation }, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_19, [createBaseVNode("div", _hoisted_20, [createBaseVNode("div", _hoisted_21, [createVNode($setup["Icon"], {
					name: $setup.field.icon,
					class: "size-4 text-gray-500 dark:text-gray-300"
				}, null, 8, ["name"])]), createBaseVNode("a", {
					href: `#field-${$setup.field._id}`,
					class: "inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased"
				}, [createBaseVNode("span", _hoisted_23, toDisplayString(_ctx.__($setup.field.config.display)), 1), $setup.showFieldDirection ? (openBlock(), createElementBlock("div", _hoisted_24, [createVNode($setup["Icon"], {
					name: "arrow-up",
					"data-field-direction-up": "",
					"aria-hidden": "true"
				}), createVNode($setup["Icon"], {
					name: "arrow-down",
					"data-field-direction-down": "",
					"aria-hidden": "true"
				})])) : createCommentVNode("", true)], 8, _hoisted_22)]), createVNode($setup["FieldValidationBuilder"], {
					config: $setup.values,
					onUpdated: _cache[2] || (_cache[2] = ($event) => $setup.values.validate = $event)
				}, null, 8, ["config"])])]),
				_: 1
			}, 8, ["name"])
		]),
		_: 1
	}, 8, ["modelValue"]))]);
}
var FieldInspector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(FieldInspector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$11], ["__file", "FieldInspector.vue"]]);
// resources/js/components/forms/builder/FieldsetInspector.vue?vue&type=script&setup=true&lang.ts
var FieldsetInspector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FieldsetInspector",
	setup(__props, { expose: __expose }) {
		__expose();
		const { dirty, inspecting: fieldsetImport } = injectBuilderContext();
		const fieldsets = Object.values(usePage().props.fieldsets);
		const fieldsetSuggestions = computed(() => fieldsets.map((fieldset) => ({
			value: fieldset.handle,
			label: __$1(fieldset.title)
		})));
		const selectedFieldset = computed(() => fieldsets.find((fieldset) => fieldset.handle === fieldsetImport.value.fieldset));
		const selectedFieldsetHasSections = computed(() => selectedFieldset.value?.has_sections === true);
		watch(() => fieldsetImport.value.fieldset, () => {
			dirty();
			if (!selectedFieldsetHasSections.value) delete fieldsetImport.value.section_behavior;
		});
		watch(() => fieldsetImport.value.prefix, dirty);
		watch(() => fieldsetImport.value.section_behavior, dirty);
		const __returned__ = {
			dirty,
			fieldsetImport,
			fieldsets,
			fieldsetSuggestions,
			selectedFieldset,
			selectedFieldsetHasSections,
			get Combobox() {
				return Combobox_default;
			},
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Input() {
				return Input_default;
			},
			get Radio() {
				return Item_default;
			},
			get RadioGroup() {
				return Group_default;
			},
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/FieldsetInspector.vue
var _hoisted_1$10 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$10 = { class: "space-y-6 pt-8" };
var _hoisted_3$10 = { class: "flex items-center gap-2.5" };
var _hoisted_4$10 = { class: "size-4" };
var _hoisted_5$10 = ["href"];
var _hoisted_6$7 = { class: "grid *:[grid-area:1/1]" };
function _sfc_render$10(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$10, [createBaseVNode("div", _hoisted_2$10, [
		createBaseVNode("div", _hoisted_3$10, [createBaseVNode("div", _hoisted_4$10, [createVNode($setup["Icon"], {
			name: "link",
			class: "size-4 text-gray-500 dark:text-gray-300"
		})]), createBaseVNode("a", {
			href: `#import-${$setup.fieldsetImport._id}`,
			class: "inline-flex items-center gap-1.5 text-xl font-medium antialiased"
		}, [createTextVNode(toDisplayString($setup.__("Linked Fieldset")) + " ", 1), createBaseVNode("div", _hoisted_6$7, [createVNode($setup["Icon"], {
			name: "arrow-up",
			"data-field-direction-up": "",
			"aria-hidden": "true"
		}), createVNode($setup["Icon"], {
			name: "arrow-down",
			"data-field-direction-down": "",
			"aria-hidden": "true"
		})])], 8, _hoisted_5$10)]),
		createVNode($setup["Field"], { label: $setup.__("Fieldset") }, {
			default: withCtx(() => [createVNode($setup["Combobox"], {
				placeholder: $setup.__("Fieldsets"),
				options: $setup.fieldsetSuggestions,
				searchable: "",
				"model-value": $setup.fieldsetImport.fieldset,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.fieldsetImport.fieldset = $event)
			}, null, 8, [
				"placeholder",
				"options",
				"model-value"
			])]),
			_: 1
		}, 8, ["label"]),
		createVNode($setup["Field"], {
			label: $setup.__("Prefix"),
			instructions: $setup.__("messages.fieldset_import_prefix_instructions")
		}, {
			default: withCtx(() => [createVNode($setup["Input"], {
				modelValue: $setup.fieldsetImport.prefix,
				"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.fieldsetImport.prefix = $event),
				placeholder: $setup.__("e.g. hero_")
			}, null, 8, ["modelValue", "placeholder"])]),
			_: 1
		}, 8, ["label", "instructions"]),
		$setup.selectedFieldsetHasSections ? (openBlock(), createBlock($setup["Field"], {
			key: 0,
			label: $setup.__("Section Behavior"),
			instructions: $setup.__("messages.fieldset_import_section_behavior_instructions")
		}, {
			default: withCtx(() => [createVNode($setup["RadioGroup"], {
				modelValue: $setup.fieldsetImport.section_behavior,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.fieldsetImport.section_behavior = $event)
			}, {
				default: withCtx(() => [createVNode($setup["Radio"], {
					label: $setup.__("Preserve"),
					description: $setup.__("Keep imported sections as-is."),
					value: "preserve"
				}, null, 8, ["label", "description"]), createVNode($setup["Radio"], {
					label: $setup.__("Flatten"),
					description: $setup.__("Merge all fields into this section."),
					value: "flatten"
				}, null, 8, ["label", "description"])]),
				_: 1
			}, 8, ["modelValue"])]),
			_: 1
		}, 8, ["label", "instructions"])) : createCommentVNode("", true)
	])]);
}
var FieldsetInspector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(FieldsetInspector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$10], ["__file", "FieldsetInspector.vue"]]);
// resources/js/components/forms/builder/FieldtypeHint.vue?vue&type=script&setup=true&lang.ts
var FieldtypeHint_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FieldtypeHint",
	setup(__props, { expose: __expose }) {
		__expose();
		const { inspecting: fieldtype } = injectBuilderContext();
		const __returned__ = {
			fieldtype,
			get Badge() {
				return Badge_default;
			},
			get Icon() {
				return Icon_default;
			},
			get PublishContainer() {
				return Container_default;
			},
			get PublishFields() {
				return Fields_default;
			},
			get PublishFieldsProvider() {
				return FieldsProvider_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/FieldtypeHint.vue
var _hoisted_1$9 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$9 = { class: "space-y-6 pt-8" };
var _hoisted_3$9 = { class: "flex items-center gap-2" };
var _hoisted_4$9 = { class: "size-4" };
var _hoisted_5$9 = { class: "inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased" };
var _hoisted_6$6 = { class: "truncate" };
var _hoisted_7$2 = {
	key: 0,
	class: "text-zinc-600 dark:text-zinc-400"
};
var _hoisted_8$2 = { key: 1 };
var _hoisted_9$2 = {
	class: "bg-white dark:bg-gray-900 rounded-md p-4 border border-gray-200 dark:border-gray-700",
	inert: ""
};
function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$9, [createBaseVNode("div", _hoisted_2$9, [
		createBaseVNode("div", _hoisted_3$9, [createBaseVNode("div", _hoisted_4$9, [createVNode($setup["Icon"], {
			name: $setup.fieldtype.icon,
			class: "size-4 text-gray-500 dark:text-gray-300"
		}, null, 8, ["name"])]), createBaseVNode("span", _hoisted_5$9, [createBaseVNode("span", _hoisted_6$6, toDisplayString(_ctx.__($setup.fieldtype.title)), 1)])]),
		$setup.fieldtype.description ? (openBlock(), createElementBlock("p", _hoisted_7$2, toDisplayString(_ctx.__($setup.fieldtype.description)), 1)) : createCommentVNode("", true),
		$setup.fieldtype.example ? (openBlock(), createElementBlock("div", _hoisted_8$2, [createVNode($setup["Badge"], {
			class: "mb-2",
			size: "sm",
			text: _ctx.__("Example")
		}, null, 8, ["text"]), createBaseVNode("div", _hoisted_9$2, [createVNode($setup["PublishContainer"], {
			meta: { [$setup.fieldtype.handle]: $setup.fieldtype.example.meta },
			blueprint: { tabs: [{ fields: $setup.fieldtype.example.config }] },
			"model-value": { [$setup.fieldtype.handle]: $setup.fieldtype.example.value }
		}, {
			default: withCtx(() => [createVNode($setup["PublishFieldsProvider"], { fields: [$setup.fieldtype.example.config] }, {
				default: withCtx(() => [createVNode($setup["PublishFields"])]),
				_: 1
			}, 8, ["fields"])]),
			_: 1
		}, 8, [
			"meta",
			"blueprint",
			"model-value"
		])])])) : createCommentVNode("", true)
	])]);
}
var FieldtypeHint_default = /*#__PURE__*/ _plugin_vue_export_helper_default(FieldtypeHint_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$9], ["__file", "FieldtypeHint.vue"]]);
// resources/js/components/forms/builder/FieldtypeSelector.vue?vue&type=script&setup=true&lang.ts
var import_fuzzysort = /* @__PURE__ */ __toESM(require_fuzzysort(), 1);
var FieldtypeSelector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FieldtypeSelector",
	setup(__props, { expose: __expose }) {
		__expose();
		const { fieldtypes, fieldView, formsProInstalled, inspect } = injectBuilderContext();
		const search = ref("");
		const isSearching = computed(() => search.value.length > 0);
		const hasFieldsets = Object.keys(usePage().props.fieldsets ?? {}).length > 0;
		const allFieldtypes = computed(() => {
			let options = [...fieldtypes];
			options.push({
				handle: "section",
				title: __$1("Section"),
				description: __$1("Sections group fields into clearly defined areas, helping you structure, organize, and navigate your form."),
				categories: ["structure"],
				keywords: [],
				icon: "add-section",
				order: 1,
				config: []
			});
			if (formsProInstalled) options.push({
				handle: "page_break",
				title: __$1("Page Break"),
				description: __$1("Page Breaks split a form into multiple steps, turning a long form into a more manageable, step-by-step experience."),
				categories: ["structure"],
				keywords: [],
				icon: "page",
				order: 2,
				config: []
			});
			if (hasFieldsets) options.push({
				handle: "fieldset",
				title: __$1("Link Existing"),
				description: __$1("Link one or more existing fields from a fieldset."),
				categories: ["fieldsets"],
				keywords: [],
				icon: "link",
				config: []
			});
			return options;
		});
		const groupedFieldtypes = computed(() => {
			return mapValues(categories, (category, handle) => {
				category.handle = handle;
				category.fieldtypes = [];
				allFieldtypes.value.forEach((fieldtype) => {
					let categories = fieldtype.categories;
					if (categories.length === 0) categories = ["other"];
					if (categories.includes(handle)) category.fieldtypes.push(fieldtype);
				});
				category.fieldtypes.sort((a, b) => {
					const aHasOrder = a.order != null;
					const bHasOrder = b.order != null;
					if (aHasOrder && bHasOrder) return a.order - b.order;
					if (aHasOrder) return -1;
					if (bHasOrder) return 1;
					return a.title.localeCompare(b.title);
				});
				return category;
			});
		});
		const searchFieldtypes = computed(() => {
			let options = allFieldtypes.value;
			if (search.value) return import_fuzzysort.default.go(search.value, options, {
				all: true,
				keys: [
					"title",
					(obj) => obj.categories?.join(),
					(obj) => obj.keywords?.join()
				],
				scoreFn: (scores) => {
					const textScore = scores[0]?.score * 1;
					const categoriesScore = scores[1]?.score * .1;
					const keywordsScore = scores[2]?.score * .4;
					return Math.max(textScore, categoriesScore, keywordsScore);
				}
			}).map((result) => result.obj);
			return options;
		});
		const __returned__ = {
			fieldtypes,
			fieldView,
			formsProInstalled,
			inspect,
			search,
			isSearching,
			hasFieldsets,
			allFieldtypes,
			groupedFieldtypes,
			searchFieldtypes,
			displayedFieldtypes: computed(() => isSearching.value ? [{ fieldtypes: searchFieldtypes.value }] : groupedFieldtypes.value),
			get Button() {
				return Button_default;
			},
			get Input() {
				return Input_default;
			},
			FieldView,
			InspectorType,
			get categoryColorClasses() {
				return categoryColorClasses;
			},
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/FieldtypeSelector.vue
var _hoisted_1$8 = {
	style: { "--graph-paper-y-offset": "9rem" },
	class: "bg-graph-paper [&_button]:w-full [&_button>div]:truncate [&_button>div]:block [&_button]:rounded-xl [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5"
};
var _hoisted_2$8 = { class: "fieldtype-source-container px-0.5 max-[1000px]:ps-2 pt-6" };
var _hoisted_3$8 = { class: "py-10 max-[1000px]:pb-20 grid gap-8 @container" };
var _hoisted_4$8 = { class: "fieldtype-source grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2" };
var _hoisted_5$8 = ["data-fieldtype"];
function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$8, [createBaseVNode("div", _hoisted_2$8, [createVNode($setup["Input"], {
		icon: "magnifying-glass",
		"legible-text": false,
		"input-class": "rounded-xl placeholder-xs",
		placeholder: $setup.__("Search Field Types..."),
		modelValue: $setup.search,
		"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.search = $event)
	}, null, 8, ["placeholder", "modelValue"]), createBaseVNode("ul", _hoisted_3$8, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.displayedFieldtypes, (group) => {
		return withDirectives((openBlock(), createElementBlock("li", { key: group.handle }, [group.title ? (openBlock(), createElementBlock("h2", {
			key: 0,
			class: normalizeClass(["inline-flex items-center px-1.5 pb-1.5 bg-gray-100 dark:bg-gray-900 text-sm text-gray-950 dark:text-gray-200 font-medium", $setup.fieldView === $setup.FieldView.Collapsed ? "gap-1.5" : "gap-0"])
		}, [createBaseVNode("span", {
			class: normalizeClass(["h-2 shrink-0 rounded-full", {
				[$setup.categoryColorClasses[group.color].dot]: true,
				"w-2 opacity-100": $setup.fieldView === $setup.FieldView.Collapsed,
				"w-0 opacity-0": $setup.fieldView === $setup.FieldView.Expanded
			}]),
			"aria-hidden": "true"
		}, null, 2), createTextVNode(" " + toDisplayString(group.title), 1)], 2)) : createCommentVNode("", true), createBaseVNode("ul", _hoisted_4$8, [(openBlock(true), createElementBlock(Fragment, null, renderList(group.fieldtypes, (fieldtype) => {
			return openBlock(), createElementBlock("li", {
				key: fieldtype.handle,
				class: "fieldtype-draggable list-none",
				"data-fieldtype": fieldtype.handle,
				tabindex: "-1"
			}, [createVNode($setup["Button"], {
				text: $setup.__(fieldtype.title),
				title: $setup.__(fieldtype.title),
				icon: fieldtype.icon,
				onClick: ($event) => $setup.inspect($setup.InspectorType.FieldtypeHint, fieldtype),
				class: "show-focus"
			}, null, 8, [
				"text",
				"title",
				"icon",
				"onClick"
			])], 8, _hoisted_5$8);
		}), 128))])])), [[vShow, group.fieldtypes.length > 0]]);
	}), 128))])])]);
}
var FieldtypeSelector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(FieldtypeSelector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$8], ["__file", "FieldtypeSelector.vue"]]);
// resources/js/components/forms/builder/LinkFieldsInspector.vue?vue&type=script&setup=true&lang.ts
var LinkFieldsInspector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "LinkFieldsInspector",
	setup(__props, { expose: __expose }) {
		__expose();
		const { dirty, form, inspect, inspecting: fieldsetImport } = injectBuilderContext();
		const fieldset = ref(null);
		const reference = ref(null);
		const importPrefix = ref(null);
		const sectionBehavior = ref("preserve");
		const loadingPreviews = ref(false);
		const fieldsetPreviews = ref({});
		const fieldsets = Object.values(usePage().props.fieldsets);
		const fieldSuggestions = fieldsets.flatMap((fieldset) => {
			return fieldset.fields.filter((field) => field.type !== "import").map((field) => ({
				value: `${fieldset.handle}.${field.handle}`,
				label: __$1(field.config.display),
				fieldset: __$1(fieldset.title)
			}));
		});
		const fieldsetSuggestions = computed(() => fieldsets.map((fieldset) => ({
			value: fieldset.handle,
			label: __$1(fieldset.title)
		})));
		const selectedFieldsetHasSections = computed(() => {
			if (!fieldset.value) return false;
			return fieldsets.find((f) => f.handle === fieldset.value)?.has_sections === true;
		});
		const loadFieldsetPreviews = () => {
			if (!fieldset.value) return;
			loadingPreviews.value = true;
			axios.post(cp_url(`forms/${form.handle}/builder/fieldset-previews`), { fieldset: fieldset.value }).then((response) => fieldsetPreviews.value = response.data.previews).catch(() => fieldsetPreviews.value = {}).finally(() => loadingPreviews.value = false);
		};
		const linkField = () => {
			const lastDot = reference.value.lastIndexOf(".");
			const fieldsetHandle = reference.value.substring(0, lastDot);
			const fieldHandle = reference.value.substring(lastDot + 1);
			const fieldData = fieldsets.find((f) => f.handle === fieldsetHandle).fields.find((f) => f.handle === fieldHandle);
			Object.assign(fieldsetImport.value, {
				handle: fieldHandle,
				type: "reference",
				field_reference: reference.value,
				config: { ...fieldData.config },
				config_overrides: [],
				fieldtype: fieldData.config.type,
				icon: fieldData.icon,
				preview: null
			});
			dirty();
			inspect(InspectorType.Field, fieldsetImport.value);
		};
		const linkFieldset = () => {
			Object.assign(fieldsetImport.value, {
				type: "import",
				fieldset: fieldset.value,
				prefix: importPrefix.value || null,
				previews: { ...fieldsetPreviews.value }
			});
			if (selectedFieldsetHasSections.value && sectionBehavior.value !== "preserve") fieldsetImport.value.section_behavior = sectionBehavior.value;
			dirty();
			inspect(InspectorType.FieldsetImport, fieldsetImport.value);
		};
		watch(fieldset, () => {
			if (!selectedFieldsetHasSections.value) sectionBehavior.value = "preserve";
			loadFieldsetPreviews();
		});
		const __returned__ = {
			dirty,
			form,
			inspect,
			fieldsetImport,
			fieldset,
			reference,
			importPrefix,
			sectionBehavior,
			loadingPreviews,
			fieldsetPreviews,
			fieldsets,
			fieldSuggestions,
			fieldsetSuggestions,
			selectedFieldsetHasSections,
			loadFieldsetPreviews,
			linkField,
			linkFieldset,
			get Button() {
				return Button_default;
			},
			get Combobox() {
				return Combobox_default;
			},
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Input() {
				return Input_default;
			},
			get Radio() {
				return Item_default;
			},
			get RadioGroup() {
				return Group_default;
			},
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/LinkFieldsInspector.vue
var _hoisted_1$7 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$7 = { class: "space-y-6 pt-8" };
var _hoisted_3$7 = { class: "flex items-center gap-2.5" };
var _hoisted_4$7 = { class: "size-4" };
var _hoisted_5$7 = ["href"];
var _hoisted_6$5 = { class: "grid *:[grid-area:1/1]" };
var _hoisted_7$1 = { class: "flex items-center" };
var _hoisted_8$1 = ["textContent"];
var _hoisted_9$1 = ["textContent"];
var _hoisted_10 = ["textContent"];
var _hoisted_11 = { class: "my-4 flex items-center" };
var _hoisted_12 = ["textContent"];
var _hoisted_13 = ["textContent"];
function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$7, [createBaseVNode("div", _hoisted_2$7, [
		createBaseVNode("div", _hoisted_3$7, [createBaseVNode("div", _hoisted_4$7, [createVNode($setup["Icon"], {
			name: "page",
			class: "size-4 text-gray-500 dark:text-gray-300"
		})]), createBaseVNode("a", {
			href: `#fieldset-${$setup.fieldsetImport._id}`,
			class: "inline-flex items-center gap-1.5 text-xl font-medium antialiased"
		}, [createTextVNode(toDisplayString($setup.__("Link Fields")) + " ", 1), createBaseVNode("div", _hoisted_6$5, [createVNode($setup["Icon"], {
			name: "arrow-up",
			"data-field-direction-up": "",
			"aria-hidden": "true"
		}), createVNode($setup["Icon"], {
			name: "arrow-down",
			"data-field-direction-down": "",
			"aria-hidden": "true"
		})])], 8, _hoisted_5$7)]),
		createVNode($setup["Field"], {
			label: $setup.__("Link a single field"),
			instructions: $setup.__("Changes to this field in the fieldset will stay in sync.")
		}, {
			default: withCtx(() => [createVNode($setup["Combobox"], {
				placeholder: $setup.__("Fields"),
				options: $setup.fieldSuggestions,
				searchable: "",
				"model-value": $setup.reference,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.reference = $event)
			}, {
				option: withCtx((option) => [createBaseVNode("div", _hoisted_7$1, [createBaseVNode("span", {
					textContent: toDisplayString(option.fieldset),
					class: "text-2xs text-gray-500 dark:text-gray-300 ltr:mr-2 rtl:ml-2"
				}, null, 8, _hoisted_8$1), createBaseVNode("span", { textContent: toDisplayString(option.label) }, null, 8, _hoisted_9$1)])]),
				"no-options": withCtx(() => [createBaseVNode("div", {
					class: "px-4 py-2 text-sm text-gray-700 dark:text-gray-500 ltr:text-left rtl:text-right",
					textContent: toDisplayString($setup.__("No options to choose from."))
				}, null, 8, _hoisted_10)]),
				_: 1
			}, 8, [
				"placeholder",
				"options",
				"model-value"
			])]),
			_: 1
		}, 8, ["label", "instructions"]),
		createVNode($setup["Button"], {
			class: "w-full mt-6",
			variant: "primary",
			disabled: !$setup.reference,
			text: $setup.__("Link Field"),
			onClick: $setup.linkField
		}, null, 8, ["disabled", "text"]),
		createBaseVNode("div", _hoisted_11, [
			_cache[4] || (_cache[4] = createBaseVNode("div", { class: "flex-1 border-b border-gray-300 dark:border-gray-500" }, null, -1)),
			createBaseVNode("div", {
				class: "mx-4 text-2xs text-gray-600 dark:text-gray-400",
				textContent: toDisplayString($setup.__("or"))
			}, null, 8, _hoisted_12),
			_cache[5] || (_cache[5] = createBaseVNode("div", { class: "flex-1 border-b border-gray-300 dark:border-gray-500" }, null, -1))
		]),
		createVNode($setup["Field"], {
			class: "mb-6",
			label: $setup.__("Link a fieldset"),
			instructions: $setup.__("Changes to this fieldset will stay in sync.")
		}, {
			default: withCtx(() => [createVNode($setup["Combobox"], {
				placeholder: $setup.__("Fieldsets"),
				options: $setup.fieldsetSuggestions,
				searchable: "",
				"model-value": $setup.fieldset,
				"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.fieldset = $event)
			}, {
				"no-options": withCtx(() => [createBaseVNode("div", {
					class: "px-4 py-2 text-sm text-gray-700 dark:text-gray-500 ltr:text-left rtl:text-right",
					textContent: toDisplayString($setup.__("No options to choose from."))
				}, null, 8, _hoisted_13)]),
				_: 1
			}, 8, [
				"placeholder",
				"options",
				"model-value"
			])]),
			_: 1
		}, 8, ["label", "instructions"]),
		createVNode($setup["Field"], {
			label: $setup.__("Prefix"),
			instructions: $setup.__("messages.fieldset_link_fields_prefix_instructions")
		}, {
			default: withCtx(() => [createVNode($setup["Input"], {
				modelValue: $setup.importPrefix,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.importPrefix = $event),
				placeholder: $setup.__("e.g. hero_")
			}, null, 8, ["modelValue", "placeholder"])]),
			_: 1
		}, 8, ["label", "instructions"]),
		$setup.selectedFieldsetHasSections ? (openBlock(), createBlock($setup["Field"], {
			key: 0,
			label: $setup.__("Section Behavior"),
			instructions: $setup.__("messages.fieldset_import_section_behavior_instructions"),
			class: "mt-6"
		}, {
			default: withCtx(() => [createVNode($setup["RadioGroup"], {
				modelValue: $setup.sectionBehavior,
				"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.sectionBehavior = $event)
			}, {
				default: withCtx(() => [createVNode($setup["Radio"], {
					label: $setup.__("Preserve"),
					description: $setup.__("Keep imported sections as-is."),
					value: "preserve"
				}, null, 8, ["label", "description"]), createVNode($setup["Radio"], {
					label: $setup.__("Flatten"),
					description: $setup.__("Merge all fields into this section."),
					value: "flatten"
				}, null, 8, ["label", "description"])]),
				_: 1
			}, 8, ["modelValue"])]),
			_: 1
		}, 8, ["label", "instructions"])) : createCommentVNode("", true),
		createVNode($setup["Button"], {
			class: "w-full mt-6",
			variant: "primary",
			disabled: !$setup.fieldset,
			text: $setup.__("Link Fieldset"),
			onClick: $setup.linkFieldset
		}, null, 8, ["disabled", "text"])
	])]);
}
var LinkFieldsInspector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(LinkFieldsInspector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$7], ["__file", "LinkFieldsInspector.vue"]]);
// resources/js/composables/forms/use-drag-and-drop.js
var SORT_CONTAINER_SELECTOR = ".field-sort-container";
/**
* Handles dragging fieldtypes from the source panel onto drop zones.
* Should be used once at the Builder level.
*/
function useFieldtypeDraggable({ pages, onDragStart, onDrop }) {
	let draggable = null;
	let lastClientY = 0;
	const dropTarget = createDropTarget();
	const dropIndicator = createDropIndicator();
	const refresh = () => {
		draggable?.destroy();
		init();
	};
	const init = () => {
		draggable?.destroy();
		const fieldtypeSource = document.querySelectorAll(".fieldtype-source-container");
		const dropZones = document.querySelectorAll(".section-drop-zone, .section-gap-drop-zone");
		const containers = [...fieldtypeSource, ...dropZones];
		if (containers.length === 0) return;
		draggable = new Draggable(containers, {
			draggable: ".fieldtype-draggable",
			delay: {
				mouse: 0,
				touch: 150
			},
			distance: 5,
			mirror: {
				constrainDimensions: true,
				appendTo: "body"
			}
		});
		draggable.on("drag:start", () => {
			dropTarget.reset();
			onDragStart?.();
		});
		draggable.on("mirror:created", (event) => {
			const width = `${event.source.offsetWidth}px`;
			event.mirror.style.width = width;
			const button = event.mirror.querySelector("button");
			if (button) {
				button.style.width = width;
				button.style.justifyContent = "flex-start";
			}
		});
		draggable.on("drag:move", (event) => {
			lastClientY = event.sensorEvent.clientY;
			renderIndicator(event);
		});
		draggable.on("drag:over:container", (event) => {
			if (event.overContainer.classList.contains("fieldtype-source-container")) {
				dropTarget.reset();
				hideIndicator();
				return;
			}
			if (isDropZone(event.overContainer)) dropTarget.enter(event.overContainer);
		});
		draggable.on("drag:out:container", (event) => dropTarget.leave(event.overContainer));
		draggable.on("drag:stop", (event) => {
			const target = dropTarget.effective;
			dropTarget.reset();
			hideIndicator();
			event.mirror?.remove();
			if (!target) return;
			handleDrop(target, event.source.dataset.fieldtype);
		});
	};
	const renderIndicator = (event) => {
		const target = dropTarget.effective;
		if (!target) return hideIndicator();
		const isGap = target.classList.contains("section-gap-drop-zone");
		const fieldtype = event.source.dataset.fieldtype;
		if (isGap && !(fieldtype === "section" || fieldtype === "page_break")) return hideIndicator();
		if (isGap) return target.appendChild(dropIndicator);
		const sortContainer = target.querySelector(SORT_CONTAINER_SELECTOR);
		if (!sortContainer) return hideIndicator();
		const fields = sortContainer.querySelectorAll("[data-field-item]");
		if (fields.length === 0) {
			sortContainer.prepend(dropIndicator);
			return;
		}
		const reference = fields[indexFromClientY(fields, lastClientY)] ?? null;
		sortContainer.insertBefore(dropIndicator, reference);
	};
	const hideIndicator = () => dropIndicator.remove();
	const handleDrop = (target, fieldtypeHandle) => {
		if (!fieldtypeHandle) return;
		const pageEl = target.closest("[data-form-page]");
		if (!pageEl) return;
		const pageId = pageEl.dataset.formPage;
		const page = pages.value.find((p) => p._id === pageId);
		if (!page) return;
		if (target.classList.contains("section-gap-drop-zone")) {
			onDrop({
				pageId,
				fieldtypeHandle,
				sectionIndex: parseInt(target.dataset.sectionGapIndex, 10),
				fieldIndex: null
			});
			return;
		}
		const sectionId = target.dataset.sectionDropZone;
		if (!sectionId) return;
		const sectionIndex = page.sections.findIndex((s) => s._id === sectionId);
		if (sectionIndex === -1) return;
		onDrop({
			pageId,
			fieldtypeHandle,
			sectionId,
			sectionIndex,
			fieldIndex: indexFromClientY(document.querySelectorAll(`[data-section-drop-zone="${sectionId}"] [data-field-item]`), lastClientY)
		});
	};
	onMounted(async () => {
		await nextTick();
		await nextTick();
		init();
	});
	onUnmounted(() => {
		draggable?.destroy();
	});
	watch(() => pages.value.flatMap((p) => p.sections).length, () => nextTick(refresh));
	watch(() => pages.value.flatMap((p) => p.sections.flatMap((s) => s.fields)).length, () => nextTick(refresh));
}
/**
* Handles sorting/reordering fields within sections.
* Should be used per-page.
*/
function useSortable({ container, sections, fieldView, onFieldMoved, onMirrorCreated }) {
	let sortables = [];
	const refresh = () => {
		destroy();
		init();
	};
	const destroy = () => {
		sortables.forEach((sortable) => sortable.destroy());
		sortables = [];
	};
	const init = () => {
		destroy();
		const el = container.value;
		if (!el) return;
		const containers = [...el.querySelectorAll(SORT_CONTAINER_SELECTOR)];
		if (containers.length === 0) return;
		groupContainersByPage(containers).forEach((group) => sortables.push(createSortable(group)));
	};
	const createSortable = (containers) => {
		const sortable = new Sortable(containers, {
			draggable: "[data-field-item]",
			handle: "[data-field-item]",
			distance: 5,
			mirror: {
				constrainDimensions: true,
				appendTo: "body"
			},
			exclude: { plugins: [Draggable.Plugins.Focusable] }
		});
		sortable.on("drag:start", () => document.documentElement.classList.add("cursor-grabbing"));
		sortable.on("drag:stop", () => document.documentElement.classList.remove("cursor-grabbing"));
		sortable.on("mirror:created", (event) => {
			onMirrorCreated?.(event);
			if (fieldView?.value !== "collapsed") return;
			event.mirror.querySelectorAll("[data-collapsed-field-icon]").forEach((el) => el.style.display = "inline-flex");
			event.mirror.querySelectorAll("[data-ui-input-group]").forEach((inputGroup) => {
				inputGroup.querySelectorAll("[data-ui-description]").forEach((el) => el.style.display = "none");
				Array.from(inputGroup.children).forEach((child, index) => {
					if (index === 0) return;
					if (child.querySelector("[data-logic-attached]")) return;
					child.style.display = "none";
				});
			});
		});
		let dragStartState = null;
		sortable.on("sortable:start", (event) => {
			const container = event.dragEvent.sourceContainer;
			dragStartState = {
				container,
				index: [...container.querySelectorAll("[data-field-item]")].indexOf(event.dragEvent.source),
				element: event.dragEvent.source
			};
		});
		sortable.on("sortable:stop", (event) => {
			const { oldIndex, newIndex, oldContainer, newContainer } = event;
			const from = oldContainer.dataset.sortSection;
			const to = newContainer.dataset.sortSection;
			if (!from || !to) {
				dragStartState = null;
				return;
			}
			if (from === to && oldIndex === newIndex) {
				dragStartState = null;
				return;
			}
			if (dragStartState) {
				const { container, index, element } = dragStartState;
				element.remove();
				const children = [...container.querySelectorAll("[data-field-item]")];
				if (index < children.length) container.insertBefore(element, children[index]);
				else container.appendChild(element);
			}
			dragStartState = null;
			onFieldMoved(from, to, oldIndex, newIndex);
		});
		return sortable;
	};
	onMounted(async () => {
		await nextTick();
		await nextTick();
		init();
	});
	onUnmounted(destroy);
	watch(() => sections.value.map((s) => s.fields.length).join(","), () => nextTick(refresh));
}
function groupContainersByPage(containers) {
	const groups = /* @__PURE__ */ new Map();
	containers.forEach((container) => {
		const page = container.closest("[data-form-page]") ?? "ungrouped";
		if (!groups.has(page)) groups.set(page, []);
		groups.get(page).push(container);
	});
	return [...groups.values()];
}
function createDropTarget() {
	let current = null;
	let sticky = null;
	return {
		get effective() {
			return current ?? sticky;
		},
		enter(el) {
			current = el;
			sticky = el;
		},
		leave(el) {
			if (current === el) current = null;
		},
		reset() {
			current = null;
			sticky = null;
		}
	};
}
function createDropIndicator() {
	const el = document.createElement("div");
	el.className = "h-1 w-full rounded-full bg-zinc-300 col-span-full";
	el.dataset.dropIndicator = "";
	return el;
}
function isDropZone(el) {
	return el.classList.contains("section-drop-zone") || el.classList.contains("section-gap-drop-zone");
}
function indexFromClientY(elements, clientY) {
	let index = 0;
	for (const el of elements) {
		const rect = el.getBoundingClientRect();
		if (clientY > rect.top + rect.height / 2) index++;
	}
	return index;
}
// resources/js/components/forms/builder/ImportField.vue?vue&type=script&setup=true&lang.ts
var ImportField_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ImportField",
	props: {
		field: {},
		isFirstRow: { type: Boolean },
		isLastRow: { type: Boolean }
	},
	emits: ["remove"],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const { errors, inspect, inspecting, inspectorType } = injectBuilderContext();
		const fieldsets = Object.values(usePage().props.fieldsets);
		const isInspecting = computed(() => inspectorType.value === InspectorType.FieldsetImport && inspecting.value?._id === props.field._id);
		const fieldset = computed(() => fieldsets.find((fs) => fs.handle === props.field.fieldset));
		const fieldsetFields = computed(() => {
			if (!fieldset.value) return [];
			return fieldset.value.fields.filter((f) => f.type !== "import");
		});
		const inspectFieldsetImport = () => inspect(InspectorType.FieldsetImport, props.field);
		const __returned__ = {
			props,
			errors,
			inspect,
			inspecting,
			inspectorType,
			fieldsets,
			isInspecting,
			fieldset,
			fieldsetFields,
			inspectFieldsetImport,
			errorMessage: computed(() => {
				const allErrors = errors?.value ?? {};
				const key = Object.keys(allErrors).find((k) => k.startsWith(`${props.field._id}.`));
				return key ? allErrors[key]?.[0] : null;
			}),
			get Button() {
				return Button_default;
			},
			get Description() {
				return Description_default;
			},
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Label() {
				return Label_default;
			},
			FieldNumber: FieldNumber_default,
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/ImportField.vue
var _hoisted_1$6 = [
	"id",
	"data-editing-field",
	"data-editing-item",
	"data-first-row",
	"data-last-row"
];
var _hoisted_2$6 = {
	key: 0,
	class: "!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center bg-blue-50 ps-2.5",
	"data-editing-field-actions": ""
};
var _hoisted_3$6 = {
	"data-fieldset-group": "",
	class: "space-y-7"
};
var _hoisted_4$6 = ["id"];
var _hoisted_5$6 = {
	key: 0,
	"data-fieldset-label": "",
	class: "inline-flex gap-1.75 rounded-md font-mono text-2xs text-indigo-800"
};
var _hoisted_6$4 = { class: "inline-flex" };
var _hoisted_7 = { class: "sr-only" };
var _hoisted_8 = {
	key: 0,
	class: "relative -top-px ms-0.5 text-red-600"
};
var _hoisted_9 = {
	class: "pointer-events-none",
	inert: ""
};
function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", {
		id: `import-${$props.field._id}`,
		"data-field-item": "",
		"data-editing-field": $setup.isInspecting ? "" : void 0,
		"data-editing-item": $setup.isInspecting ? "" : void 0,
		"data-first-row": $props.isFirstRow ? "" : void 0,
		"data-last-row": $props.isLastRow ? "" : void 0,
		class: normalizeClass([{ "cursor-pointer": !$setup.isInspecting }]),
		onClick: _cache[1] || (_cache[1] = withModifiers(($event) => $setup.isInspecting || $setup.inspectFieldsetImport(), ["stop"]))
	}, [$setup.isInspecting ? (openBlock(), createElementBlock("div", _hoisted_2$6, [createVNode($setup["Button"], {
		size: "sm",
		inset: "",
		icon: "trash",
		variant: "subtle",
		"aria-label": $setup.__("Remove fieldset"),
		title: $setup.__("Remove fieldset"),
		class: "[&_svg]:opacity-45",
		onClick: _cache[0] || (_cache[0] = withModifiers(($event) => _ctx.$emit("remove"), ["stop"]))
	}, null, 8, ["aria-label", "title"])])) : createCommentVNode("", true), createBaseVNode("div", _hoisted_3$6, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.fieldsetFields, (fieldsetField, fieldsetFieldIndex) => {
		return openBlock(), createElementBlock("div", {
			key: fieldsetField.handle,
			id: fieldsetFieldIndex === 0 ? "fieldset-start" : fieldsetFieldIndex === $setup.fieldsetFields.length - 1 ? "fieldset-end" : void 0
		}, [
			fieldsetFieldIndex === 0 ? (openBlock(), createElementBlock("span", _hoisted_5$6, [withDirectives((openBlock(), createElementBlock("span", _hoisted_6$4, [createVNode($setup["Icon"], {
				name: "link",
				class: "size-3.5",
				"aria-hidden": "true"
			})])), [[_directive_tooltip, $setup.__("Linked Fieldset")]]), createBaseVNode("span", _hoisted_7, toDisplayString($setup.__("Linked Fieldset: :title", { title: $setup.__($setup.fieldset.title) })), 1)])) : createCommentVNode("", true),
			createVNode($setup["Field"], {
				label: $setup.__(fieldsetField.config.display),
				instructions: fieldsetField.config.instructions
			}, {
				label: withCtx(() => [createVNode($setup["Label"], null, {
					default: withCtx(() => [
						createVNode($setup["FieldNumber"], {
							"field-key": `${$props.field._id}:${fieldsetField.handle}`,
							class: "me-1"
						}, null, 8, ["field-key"]),
						createVNode($setup["Icon"], {
							name: fieldsetField.icon || "generic-field",
							"data-collapsed-field-icon": "",
							class: "size-3.5 mb-0.25! me-2.5 text-gray-600 dark:text-gray-400",
							"aria-hidden": "true"
						}, null, 8, ["name"]),
						createBaseVNode("span", null, [createTextVNode(toDisplayString($setup.__(fieldsetField.config.display)) + " ", 1), fieldsetField.config.validate?.includes("required") ? (openBlock(), createElementBlock("span", _hoisted_8, "*")) : createCommentVNode("", true)])
					]),
					_: 2
				}, 1024)]),
				default: withCtx(() => [createBaseVNode("div", _hoisted_9, [(openBlock(), createBlock(resolveDynamicComponent(`${$props.field.previews[fieldsetField.handle].config.component || $props.field.previews[fieldsetField.handle].config.type}-fieldtype`), {
					config: $props.field.previews[fieldsetField.handle].config,
					value: $props.field.previews[fieldsetField.handle].value,
					meta: $props.field.previews[fieldsetField.handle].meta,
					handle: fieldsetField.handle
				}, null, 8, [
					"config",
					"value",
					"meta",
					"handle"
				]))])]),
				_: 2
			}, 1032, ["label", "instructions"]),
			$setup.errorMessage && fieldsetFieldIndex === $setup.fieldsetFields.length - 1 ? (openBlock(), createBlock($setup["Description"], {
				key: 1,
				text: $setup.errorMessage,
				class: "!text-red-600 mt-4"
			}, null, 8, ["text"])) : createCommentVNode("", true)
		], 8, _hoisted_4$6);
	}), 128))])], 10, _hoisted_1$6);
}
var ImportField_default = /*#__PURE__*/ _plugin_vue_export_helper_default(ImportField_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$6], ["__file", "ImportField.vue"]]);
// resources/js/components/forms/builder/RegularFormField.vue?vue&type=script&setup=true&lang.ts
var RegularFormField_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "RegularFormField",
	props: {
		field: {},
		fieldtypes: {},
		isFirstRow: { type: Boolean },
		isLastRow: { type: Boolean }
	},
	emits: [
		"duplicate",
		"remove",
		"width-changed"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const { dirty, errors, fieldView, inspect, inspecting, inspectorType } = injectBuilderContext();
		const inspectField = () => inspect(InspectorType.Field, props.field);
		const isInspecting = computed(() => inspectorType.value === InspectorType.Field && inspecting.value?._id === props.field._id);
		const toggleVisibility = () => {
			props.field.config.hidden = !props.field.config.hidden;
			dirty();
		};
		const fieldtypeCategory = computed(() => {
			const hue = props.fieldtypes.find((f) => f.handle === props.field.fieldtype)?.categories?.[0] || "other";
			return categories[hue] ?? categories.other;
		});
		const __returned__ = {
			props,
			dirty,
			errors,
			fieldView,
			inspect,
			inspecting,
			inspectorType,
			inspectField,
			isInspecting,
			toggleVisibility,
			fieldtypeCategory,
			isInformationField: computed(() => fieldtypeCategory.value === categories.information),
			iconColorClass: computed(() => categoryColorClasses[fieldtypeCategory.value.color].icon),
			hasLogic: computed(() => KEYS.some((key) => props.field.config[key] && Object.keys(props.field.config[key]).length > 0)),
			hasErrors: computed(() => {
				const allErrors = errors?.value ?? {};
				return Object.keys(allErrors).some((key) => key.startsWith(`${props.field._id}.`));
			}),
			get Button() {
				return Button_default;
			},
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Label() {
				return Label_default;
			},
			FieldView,
			FieldNumber: FieldNumber_default,
			WidthSelector: WidthSelector_default,
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/RegularFormField.vue
var _hoisted_1$5 = [
	"id",
	"data-editing-field",
	"data-editing-item",
	"data-first-row",
	"data-last-row"
];
var _hoisted_2$5 = {
	key: 0,
	class: "!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center bg-blue-50 dark:bg-blue-950 ps-3",
	"data-editing-field-actions": ""
};
var _hoisted_3$5 = {
	key: 0,
	inert: ""
};
var _hoisted_4$5 = {
	key: 0,
	class: "relative -top-px ms-0.5 text-red-600"
};
var _hoisted_5$5 = {
	key: 0,
	inert: ""
};
function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", {
		id: `field-${$props.field._id}`,
		"data-field-item": "",
		"data-editing-field": $setup.isInspecting ? "" : void 0,
		"data-editing-item": $setup.isInspecting ? "" : void 0,
		"data-first-row": $props.isFirstRow ? "" : void 0,
		"data-last-row": $props.isLastRow ? "" : void 0,
		class: normalizeClass([`field-w-${$props.field.config.width || 100}`, { "cursor-pointer": !$setup.isInspecting }]),
		onClick: _cache[3] || (_cache[3] = withModifiers(($event) => $setup.isInspecting || $setup.inspectField(), ["stop"]))
	}, [$setup.isInspecting ? (openBlock(), createElementBlock("div", _hoisted_2$5, [
		createVNode($setup["WidthSelector"], {
			size: "base",
			variant: "filled",
			class: "me-2 bg-blue-50! border-blue-300! dark:bg-blue-950/40! dark:border-blue-600! [&_[data-state]]:!border-blue-200 dark:[&_[data-state]]:!border-blue-700 [&_[data-state='selected']]:bg-blue-100! [&_[data-state='selected'][data-last='false']]:!border-blue-100 [&_[data-last='true']]:!border-blue-300 dark:[&_[data-state='selected']]:bg-blue-900! dark:[&_[data-state='selected'][data-last='false']]:!border-blue-900 dark:[&_[data-last='true']]:!border-blue-600",
			"model-value": $props.field.config.width || 100,
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("width-changed", $event))
		}, null, 8, ["model-value"]),
		createVNode($setup["Button"], {
			size: "sm",
			inset: "",
			icon: "duplicate",
			variant: "subtle",
			"aria-label": $setup.__("Duplicate field"),
			title: $setup.__("Duplicate field"),
			class: "[&_svg]:opacity-45",
			onClick: _cache[1] || (_cache[1] = withModifiers(($event) => _ctx.$emit("duplicate"), ["stop"]))
		}, null, 8, ["aria-label", "title"]),
		createVNode($setup["Button"], {
			size: "sm",
			inset: "",
			icon: $props.field.config.hidden ? "eye-closed" : "eye",
			variant: "subtle",
			"aria-label": $props.field.config.hidden ? $setup.__("Show field") : $setup.__("Hide field"),
			title: $props.field.config.hidden ? $setup.__("Show field") : $setup.__("Hide field"),
			class: "[&_svg]:opacity-45",
			onClick: withModifiers($setup.toggleVisibility, ["stop"])
		}, null, 8, [
			"icon",
			"aria-label",
			"title"
		]),
		createVNode($setup["Button"], {
			size: "sm",
			inset: "",
			icon: "trash",
			variant: "subtle",
			"aria-label": $setup.__("Remove field"),
			title: $setup.__("Remove field"),
			class: "[&_svg]:opacity-45",
			onClick: _cache[2] || (_cache[2] = withModifiers(($event) => _ctx.$emit("remove"), ["stop"]))
		}, null, 8, ["aria-label", "title"])
	])) : createCommentVNode("", true), $setup.isInformationField && $setup.fieldView === $setup.FieldView.Collapsed ? (openBlock(), createBlock($setup["Field"], {
		key: 1,
		class: normalizeClass({ "opacity-60": $props.field.config.hidden })
	}, {
		label: withCtx(() => [createVNode($setup["Label"], { class: normalizeClass(["mb-0", { "cursor-pointer": !$setup.isInspecting }]) }, {
			default: withCtx(() => [
				createVNode($setup["FieldNumber"], {
					"field-key": $props.field._id,
					class: "me-1"
				}, null, 8, ["field-key"]),
				createVNode($setup["Icon"], {
					name: $props.field.icon,
					"data-collapsed-field-icon": "",
					class: normalizeClass(["size-3.5 mb-0.25! me-2.5", $setup.iconColorClass]),
					"aria-hidden": "true"
				}, null, 8, ["name", "class"]),
				$setup.hasLogic ? withDirectives((openBlock(), createBlock($setup["Icon"], {
					key: 0,
					"data-logic-attached": "",
					name: "logic-tree",
					class: "size-3.5! inline-block text-gray-400 dark:text-gray-500 me-2.5 mb-0.5",
					"aria-label": $setup.__("Logic attached")
				}, null, 8, ["aria-label"])), [[_directive_tooltip, $setup.__("Logic attached")]]) : createCommentVNode("", true),
				createBaseVNode("span", null, toDisplayString($setup.__($props.field.config.display)), 1),
				$props.field.type === "reference" ? withDirectives((openBlock(), createBlock($setup["Icon"], {
					key: 1,
					name: "link",
					class: "inline-block size-3! text-indigo-500 dark:text-indigo-400 mb-0.5 ms-2",
					"aria-label": $setup.__("Linked Field")
				}, null, 8, ["aria-label"])), [[_directive_tooltip, $setup.__("Linked Field")]]) : createCommentVNode("", true),
				$props.field.config.hidden ? withDirectives((openBlock(), createBlock($setup["Icon"], {
					key: 2,
					name: "eye-closed",
					class: "inline-block size-3.5! text-gray-400 dark:text-gray-500 mb-0.5 ms-2",
					"aria-label": $setup.__("Hidden")
				}, null, 8, ["aria-label"])), [[_directive_tooltip, $setup.__("Hidden")]]) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["class"])]),
		_: 1
	}, 8, ["class"])) : $setup.isInformationField ? (openBlock(), createElementBlock("div", {
		key: 2,
		class: normalizeClass({ "opacity-60": $props.field.config.hidden })
	}, [$props.field.preview ? (openBlock(), createElementBlock("div", _hoisted_3$5, [(openBlock(), createBlock(resolveDynamicComponent(`${$props.field.preview.config.component || $props.field.preview.config.type}-fieldtype`), {
		config: $props.field.preview.config,
		value: $props.field.preview.value,
		meta: $props.field.preview.meta,
		handle: $props.field.handle
	}, null, 8, [
		"config",
		"value",
		"meta",
		"handle"
	]))])) : createCommentVNode("", true)], 2)) : (openBlock(), createBlock($setup["Field"], {
		key: 3,
		class: normalizeClass({ "opacity-60": $props.field.config.hidden }),
		label: $setup.__($props.field.config.display),
		instructions: $props.field.config.instructions,
		error: $setup.hasErrors ? $setup.__("This field has errors. Please fix them before saving.") : null
	}, {
		label: withCtx(() => [createVNode($setup["Label"], { class: normalizeClass(["", { "cursor-pointer": !$setup.isInspecting }]) }, {
			default: withCtx(() => [
				createVNode($setup["FieldNumber"], {
					"field-key": $props.field._id,
					class: "me-1"
				}, null, 8, ["field-key"]),
				createVNode($setup["Icon"], {
					name: $props.field.icon,
					"data-collapsed-field-icon": "",
					class: normalizeClass(["size-3.5 mb-0.25! me-2.5", $setup.iconColorClass]),
					"aria-hidden": "true"
				}, null, 8, ["name", "class"]),
				$setup.hasLogic ? withDirectives((openBlock(), createBlock($setup["Icon"], {
					key: 0,
					"data-logic-attached": "",
					name: "logic-tree",
					class: "size-3.5! inline-block text-gray-400 dark:text-gray-500 me-2.5 mb-0.5",
					"aria-label": $setup.__("Logic attached")
				}, null, 8, ["aria-label"])), [[_directive_tooltip, $setup.__("Logic attached")]]) : createCommentVNode("", true),
				createBaseVNode("span", null, [createTextVNode(toDisplayString($setup.__($props.field.config.display)) + " ", 1), $props.field.config.validate?.includes("required") ? (openBlock(), createElementBlock("span", _hoisted_4$5, "*")) : createCommentVNode("", true)]),
				$props.field.type === "reference" ? withDirectives((openBlock(), createBlock($setup["Icon"], {
					key: 1,
					name: "link",
					class: "inline-block size-3! text-indigo-500 dark:text-indigo-400 mb-0.5 ms-2",
					"aria-label": $setup.__("Linked Field: :reference", { reference: $props.field.field_reference })
				}, null, 8, ["aria-label"])), [[_directive_tooltip, $setup.__("Linked Field: :reference", { reference: $props.field.field_reference })]]) : createCommentVNode("", true),
				$props.field.config.hidden ? withDirectives((openBlock(), createBlock($setup["Icon"], {
					key: 2,
					name: "eye-closed",
					class: "inline-block size-3.5! text-gray-400 dark:text-gray-500 mb-0.5 ms-2",
					"aria-label": $setup.__("Hidden")
				}, null, 8, ["aria-label"])), [[_directive_tooltip, $setup.__("Hidden")]]) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["class"])]),
		default: withCtx(() => [$props.field.preview ? (openBlock(), createElementBlock("div", _hoisted_5$5, [(openBlock(), createBlock(resolveDynamicComponent(`${$props.field.preview.config.component || $props.field.preview.config.type}-fieldtype`), {
			config: $props.field.preview.config,
			value: $props.field.preview.value,
			meta: $props.field.preview.meta,
			handle: $props.field.handle
		}, null, 8, [
			"config",
			"value",
			"meta",
			"handle"
		]))])) : createCommentVNode("", true)]),
		_: 1
	}, 8, [
		"class",
		"label",
		"instructions",
		"error"
	]))], 10, _hoisted_1$5);
}
var RegularFormField_default = /*#__PURE__*/ _plugin_vue_export_helper_default(RegularFormField_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$5], ["__file", "RegularFormField.vue"]]);
// resources/js/components/forms/builder/Section.vue?vue&type=script&setup=true&lang.ts
var Section_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Section",
	props: {
		section: {},
		canDeleteSection: { type: Boolean }
	},
	emits: ["deleted"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const { clearInspector, dirty, ensureUniqueDisplay, fieldtypes, fieldView, inspect, inspecting, inspectorType, pages } = injectBuilderContext();
		const isOnlySection = computed(() => pages.value.flatMap((page) => page.sections).length === 1);
		const editSection = () => inspect(InspectorType.Section, props.section);
		const isInspecting = computed(() => inspectorType.value === InspectorType.Section && inspecting.value?._id === props.section._id);
		const inspectLinkFields = (field) => inspect(InspectorType.LinkFields, field);
		const isInspectingLinkFields = (field) => inspectorType.value === InspectorType.LinkFields && inspecting.value?._id === field._id;
		const addLinkFieldsPlaceholder = () => {
			const placeholder = {
				_id: uniqid(),
				type: "link_fields"
			};
			props.section.fields.push(placeholder);
			inspectLinkFields(placeholder);
			dirty();
		};
		const toggleCollapsed = () => {
			props.section.collapsibleInteracted = true;
			props.section.collapsed = !props.section.collapsed;
		};
		const inspectField = (field) => inspect(InspectorType.Field, field);
		const blueprint = computed(() => ({ tabs: [{
			handle: "main",
			sections: [{ fields: props.section.fields.filter((field) => field.preview?.config).map((field) => field.preview.config) }]
		}] }));
		const containerMeta = computed(() => {
			const meta = {};
			props.section.fields.forEach((field) => {
				if (field.preview?.meta) meta[field.preview.config.handle] = field.preview.meta;
			});
			return meta;
		});
		const containerValues = computed(() => {
			const values = {};
			props.section.fields.forEach((field) => {
				if (field.preview?.value) values[field.preview.config.handle] = field.preview.value;
			});
			return values;
		});
		const updateFieldWidth = (field, width) => {
			field.config.width = width;
			if (field.type === "reference" && !field.config_overrides.includes("width")) field.config_overrides.push("width");
			dirty();
		};
		const duplicateField = (field) => {
			const { section } = props;
			const index = section.fields.indexOf(field);
			const _id = uniqid();
			const newField = {
				...field,
				_id,
				handle: null,
				isNew: true,
				config: {
					...field.config,
					display: ensureUniqueDisplay(`${field.config.display} (${__$1("Duplicate")})`)
				},
				preview: {
					config: {
						...field.preview.config,
						handle: _id
					},
					value: field.preview.value,
					meta: field.preview.meta
				}
			};
			section.fields.splice(index + 1, 0, newField);
			dirty();
			inspectField(newField);
			setTimeout(() => document.getElementById("field_display")?.select(), 250);
		};
		const fieldPendingDeletion = ref(null);
		const confirmingFieldDeletion = ref(false);
		const confirmDeleteField = (field) => {
			fieldPendingDeletion.value = field;
			confirmingFieldDeletion.value = true;
		};
		const deleteField = () => {
			dirty();
			clearInspector();
			props.section.fields.splice(props.section.fields.indexOf(fieldPendingDeletion.value), 1);
			fieldPendingDeletion.value = null;
			confirmingFieldDeletion.value = false;
		};
		const confirmingDelete = ref(false);
		const confirmDelete = () => confirmingDelete.value = true;
		const deleteSection = () => emit("deleted", props.section._id);
		const getFieldWidth = (field) => {
			const w = Number(field?.config?.width);
			return w > 0 && w <= 100 ? w : 100;
		};
		const rowBoundaries = computed(() => {
			const fields = props.section.fields;
			let width = 0;
			let firstRowEnd = fields.length - 1;
			let lastRowStart = 0;
			let wrapped = false;
			for (let i = 0; i < fields.length; i++) {
				const fieldWidth = getFieldWidth(fields[i]);
				if (width > 0 && width + fieldWidth > 100) {
					if (!wrapped) firstRowEnd = i - 1;
					lastRowStart = i;
					width = 0;
					wrapped = true;
				}
				width += fieldWidth;
			}
			return {
				firstRowEnd,
				lastRowStart
			};
		});
		const isFieldInFirstRow = (index) => index <= rowBoundaries.value.firstRowEnd;
		const isFieldInLastRow = (index) => index >= rowBoundaries.value.lastRowStart;
		const __returned__ = {
			emit,
			props,
			clearInspector,
			dirty,
			ensureUniqueDisplay,
			fieldtypes,
			fieldView,
			inspect,
			inspecting,
			inspectorType,
			pages,
			isOnlySection,
			editSection,
			isInspecting,
			inspectLinkFields,
			isInspectingLinkFields,
			addLinkFieldsPlaceholder,
			toggleCollapsed,
			inspectField,
			blueprint,
			containerMeta,
			containerValues,
			updateFieldWidth,
			duplicateField,
			fieldPendingDeletion,
			confirmingFieldDeletion,
			confirmDeleteField,
			deleteField,
			confirmingDelete,
			confirmDelete,
			deleteSection,
			getFieldWidth,
			rowBoundaries,
			isFieldInFirstRow,
			isFieldInLastRow,
			get Button() {
				return Button_default;
			},
			get Card() {
				return Card_default;
			},
			get ConfirmationModal() {
				return ConfirmationModal_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Panel() {
				return Panel_default;
			},
			get PanelHeader() {
				return Header_default;
			},
			get PublishContainer() {
				return Container_default;
			},
			FieldView,
			ImportField: ImportField_default,
			RegularFormField: RegularFormField_default,
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/Section.vue
var _hoisted_1$4 = { class: "publish-section-collapsible__inner min-h-0" };
var _hoisted_2$4 = ["data-sort-section"];
var _hoisted_3$4 = { class: "text-zinc-500 mr-2" };
var _hoisted_4$4 = ["data-sort-section", "data-fields-collapsed"];
var _hoisted_5$4 = [
	"id",
	"data-editing-field",
	"data-editing-item",
	"data-first-row",
	"data-last-row",
	"onClick"
];
var _hoisted_6$3 = { class: "text-zinc-500 mr-2" };
function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Panel"], {
		id: `section-${$props.section._id}`,
		class: normalizeClass(["mx-auto max-w-5xl", { "pb-0": $props.section.collapsed }]),
		"data-panel-collapsed": $props.section.collapsed ? "true" : "false",
		"data-editing-item": $setup.isInspecting ? "" : void 0
	}, {
		default: withCtx(() => [
			createVNode($setup["PanelHeader"], {
				class: "flex items-center justify-between",
				onClick: $setup.toggleCollapsed
			}, {
				default: withCtx(() => [createVNode($setup["Heading"], {
					class: "cursor-pointer flex-1",
					text: $setup.__($props.section.display)
				}, null, 8, ["text"]), createBaseVNode("div", null, [
					createVNode($setup["Button"], {
						class: "[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0",
						icon: "pencil-line",
						size: "sm",
						variant: "ghost",
						"aria-label": $setup.__("Edit section"),
						onClick: withModifiers($setup.editSection, ["stop"])
					}, null, 8, ["aria-label"]),
					$props.canDeleteSection ? (openBlock(), createBlock($setup["Button"], {
						key: 0,
						class: "[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0",
						icon: "trash",
						size: "sm",
						variant: "ghost",
						"aria-label": $setup.__("Delete section"),
						onClick: withModifiers($setup.confirmDelete, ["stop"])
					}, null, 8, ["aria-label"])) : createCommentVNode("", true),
					createVNode($setup["Button"], {
						class: "[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0",
						icon: $props.section.collapsed ? "expand" : "collapse",
						size: "sm",
						variant: "ghost",
						"aria-label": $setup.__("Toggle section visibility"),
						onClick: withModifiers($setup.toggleCollapsed, ["stop"])
					}, null, 8, ["icon", "aria-label"])
				])]),
				_: 1
			}),
			createBaseVNode("div", { class: normalizeClass(["publish-section-collapsible grid", [$props.section.collapsed ? "publish-section-collapsible--collapsed" : "publish-section-collapsible--expanded", { "publish-section-collapsible--interacted": $props.section.collapsibleInteracted }]]) }, [createBaseVNode("div", _hoisted_1$4, [createVNode($setup["Card"], {
				class: "section-drop-zone",
				"data-section-drop-zone": $props.section._id
			}, {
				default: withCtx(() => [$props.section.fields.length === 0 ? (openBlock(), createElementBlock("div", {
					key: 0,
					class: "field-sort-container",
					"data-sort-section": $props.section._id
				}, [createBaseVNode("div", {
					"data-empty-section": "",
					class: normalizeClass(["flex items-center justify-center rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700", {
						"h-[670px]": $setup.isOnlySection,
						"h-[200px]": !$setup.isOnlySection
					}])
				}, [createBaseVNode("div", null, [createBaseVNode("span", _hoisted_3$4, toDisplayString($setup.__("Drag fields here to build your form or")), 1), createVNode($setup["Button"], {
					size: "xs",
					pill: "",
					icon: "link",
					text: $setup.__("Link Existing"),
					onClick: $setup.addLinkFieldsPlaceholder
				}, null, 8, ["text"])])], 2)], 8, _hoisted_2$4)) : (openBlock(), createBlock($setup["PublishContainer"], {
					key: 1,
					name: "form-builder-" + $props.section._id,
					blueprint: $setup.blueprint,
					meta: $setup.containerMeta,
					"model-value": $setup.containerValues,
					"track-dirty-state": false
				}, {
					default: withCtx(() => [createBaseVNode("div", {
						class: "field-sort-container field-grid",
						"data-sort-section": $props.section._id,
						"data-fields-collapsed": $setup.fieldView === $setup.FieldView.Collapsed ? "true" : null
					}, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.section.fields, (field, fieldIndex) => {
						return openBlock(), createElementBlock(Fragment, { key: field._id }, [field.type === "link_fields" ? (openBlock(), createElementBlock("div", {
							key: 0,
							id: `fieldset-${field._id}`,
							"data-field-item": "",
							"data-editing-field": $setup.isInspectingLinkFields(field) ? "" : void 0,
							"data-editing-item": $setup.isInspectingLinkFields(field) ? "" : void 0,
							"data-first-row": $setup.isFieldInFirstRow(fieldIndex) ? "" : void 0,
							"data-last-row": $setup.isFieldInLastRow(fieldIndex) ? "" : void 0,
							class: normalizeClass([[{ "cursor-pointer": !$setup.isInspectingLinkFields(field) }], "border border-dashed rounded-lg p-4 flex items-center justify-center"]),
							onClick: withModifiers(($event) => $setup.isInspectingLinkFields(field) || $setup.inspectLinkFields(field), ["stop"])
						}, [createBaseVNode("span", _hoisted_6$3, toDisplayString($setup.__("Select a field or fieldset to import")), 1)], 10, _hoisted_5$4)) : field.type === "import" ? (openBlock(), createBlock($setup["ImportField"], {
							key: 1,
							field,
							"is-first-row": $setup.isFieldInFirstRow(fieldIndex),
							"is-last-row": $setup.isFieldInLastRow(fieldIndex),
							onRemove: ($event) => _ctx.confirmRemoveField(field)
						}, null, 8, [
							"field",
							"is-first-row",
							"is-last-row",
							"onRemove"
						])) : (openBlock(), createBlock($setup["RegularFormField"], {
							key: 2,
							field,
							fieldtypes: $setup.fieldtypes,
							"is-first-row": $setup.isFieldInFirstRow(fieldIndex),
							"is-last-row": $setup.isFieldInLastRow(fieldIndex),
							onDuplicate: ($event) => $setup.duplicateField(field),
							onWidthChanged: ($event) => $setup.updateFieldWidth(field, $event),
							onRemove: ($event) => $setup.confirmDeleteField(field)
						}, null, 8, [
							"field",
							"fieldtypes",
							"is-first-row",
							"is-last-row",
							"onDuplicate",
							"onWidthChanged",
							"onRemove"
						]))], 64);
					}), 128))], 8, _hoisted_4$4)]),
					_: 1
				}, 8, [
					"name",
					"blueprint",
					"meta",
					"model-value"
				])), renderSlot(_ctx.$slots, "footer")]),
				_: 3
			}, 8, ["data-section-drop-zone"])])], 2),
			createVNode($setup["ConfirmationModal"], {
				open: $setup.confirmingDelete,
				"onUpdate:open": _cache[0] || (_cache[0] = ($event) => $setup.confirmingDelete = $event),
				title: $setup.__("Delete Section"),
				"body-text": $setup.__("Are you sure you want to delete this section? All fields in this section will also be deleted."),
				"button-text": $setup.__("Delete"),
				danger: "",
				onConfirm: $setup.deleteSection
			}, null, 8, [
				"open",
				"title",
				"body-text",
				"button-text"
			]),
			createVNode($setup["ConfirmationModal"], {
				open: $setup.confirmingFieldDeletion,
				"onUpdate:open": _cache[1] || (_cache[1] = ($event) => $setup.confirmingFieldDeletion = $event),
				title: $setup.__("Delete Field"),
				"body-text": $setup.__("Are you sure you want to delete this field? This action cannot be undone."),
				"button-text": $setup.__("Delete"),
				danger: "",
				onConfirm: $setup.deleteField
			}, null, 8, [
				"open",
				"title",
				"body-text",
				"button-text"
			])
		]),
		_: 3
	}, 8, [
		"id",
		"class",
		"data-panel-collapsed",
		"data-editing-item"
	]);
}
var Section_default = /*#__PURE__*/ _plugin_vue_export_helper_default(Section_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$4], ["__file", "Section.vue"]]);
// resources/js/components/forms/builder/Page.vue?vue&type=script&setup=true&lang.ts
var Page_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Page",
	props: { page: {} },
	setup(__props, { expose: __expose }) {
		__expose();
		const { addSection, clearInspector, dirty, fieldView, formsProInstalled, inspect, inspecting, inspectorType, pages } = injectBuilderContext();
		const props = __props;
		const inspectPage = () => inspect(InspectorType.Page, props.page);
		const isInspectingPage = computed(() => inspectorType.value === InspectorType.Page && inspecting.value?._id === props.page._id);
		const inspectAction = () => inspect(InspectorType.Action, props.page);
		const isInspectingAction = computed(() => inspectorType.value === InspectorType.Action && inspecting.value?._id === props.page._id);
		const container = useTemplateRef("container");
		const sections = computed(() => props.page.sections);
		const canDeleteSection = computed(() => sections.value.length > 1);
		const isLastPage = computed(() => pages.value.findIndex((p) => p._id === props.page._id) === pages.value.length - 1);
		const hasLogic = computed(() => {
			return (props.page.rules ?? []).some((rule) => {
				if (!rule.destination) return false;
				return (rule.conditions ?? []).some((condition) => condition.field && condition.value);
			});
		});
		const placeholderTitle = computed(() => {
			let pageIndex = pages.value.findIndex((p) => p._id === props.page._id);
			return __$1("Page :current of :total", {
				current: pageIndex + 1,
				total: pages.value.length
			});
		});
		const sectionDeleted = (sectionId) => {
			dirty();
			clearInspector();
			props.page.sections = props.page.sections.filter((section) => section._id !== sectionId);
		};
		const moveField = (fromSectionId, toSectionId, oldIndex, newIndex) => {
			const fromSection = sections.value.find((s) => s._id === fromSectionId);
			const toSection = sections.value.find((s) => s._id === toSectionId);
			if (!fromSection || !toSection) return;
			const [field] = fromSection.fields.splice(oldIndex, 1);
			toSection.fields.splice(newIndex, 0, field);
		};
		useSortable({
			container,
			sections,
			fieldView,
			onFieldMoved: moveField
		});
		onMounted(() => {
			if (sections.value.length === 0) {
				addSection(props.page._id, 0);
				clearInspector();
			}
		});
		const __returned__ = {
			addSection,
			clearInspector,
			dirty,
			fieldView,
			formsProInstalled,
			inspect,
			inspecting,
			inspectorType,
			pages,
			props,
			inspectPage,
			isInspectingPage,
			inspectAction,
			isInspectingAction,
			container,
			sections,
			canDeleteSection,
			isLastPage,
			hasLogic,
			placeholderTitle,
			sectionDeleted,
			moveField,
			get Button() {
				return Button_default;
			},
			get Icon() {
				return Icon_default;
			},
			Section: Section_default,
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/Page.vue
var _hoisted_1$3 = ["data-form-page"];
var _hoisted_2$3 = [
	"id",
	"aria-label",
	"onKeydown"
];
var _hoisted_3$3 = { class: "flex items-center gap-4 cursor-pointer" };
var _hoisted_4$3 = ["data-editing-item"];
var _hoisted_5$3 = ["id"];
var _hoisted_6$2 = ["data-section-gap-index"];
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", {
		ref: "container",
		"data-form-page": $props.page._id
	}, [
		$setup.formsProInstalled ? (openBlock(), createElementBlock("div", {
			key: 0,
			id: `page-${$props.page._id}`,
			class: "mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-2 -mt-2",
			role: "button",
			tabindex: "0",
			"aria-label": $props.page.display ? $setup.__($props.page.display) : $setup.placeholderTitle,
			"data-form-page-label": "",
			onClick: $setup.inspectPage,
			onKeydown: [withKeys(withModifiers($setup.inspectPage, ["prevent"]), ["enter"]), withKeys(withModifiers($setup.inspectPage, ["prevent"]), ["space"])]
		}, [createBaseVNode("div", _hoisted_3$3, [
			_cache[0] || (_cache[0] = createBaseVNode("div", {
				class: "h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700",
				"aria-hidden": "true"
			}, null, -1)),
			createBaseVNode("div", {
				class: normalizeClass(["flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200 scroll-mt-[7rem]", $setup.isInspectingPage ? "bg-blue-50 border-blue-400! dark:bg-blue-950 dark:border-blue-500/75!" : ""]),
				"data-editing-item": $setup.isInspectingPage ? "" : void 0
			}, [createVNode($setup["Icon"], {
				name: "page",
				class: "size-4 shrink-0 text-gray-500 dark:text-gray-400",
				"aria-hidden": "true"
			}), createTextVNode(" " + toDisplayString($props.page.display ? $setup.__($props.page.display) : $setup.placeholderTitle), 1)], 10, _hoisted_4$3),
			_cache[1] || (_cache[1] = createBaseVNode("div", {
				class: "h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700",
				"aria-hidden": "true"
			}, null, -1))
		])], 40, _hoisted_2$3)) : createCommentVNode("", true),
		_cache[2] || (_cache[2] = createBaseVNode("div", {
			tabindex: "-1",
			class: "section-gap-drop-zone mx-auto max-w-5xl h-6 flex items-center -mt-4",
			"data-section-gap-index": "0"
		}, null, -1)),
		(openBlock(true), createElementBlock(Fragment, null, renderList($setup.sections, (section, sectionIndex) => {
			return openBlock(), createElementBlock(Fragment, { key: section._id }, [createVNode($setup["Section"], {
				section,
				"can-delete-section": $setup.canDeleteSection,
				onDeleted: $setup.sectionDeleted
			}, createSlots({ _: 2 }, [section.fields.length && sectionIndex === $setup.sections.length - 1 ? {
				name: "footer",
				fn: withCtx(() => [createBaseVNode("div", {
					id: `actions-${$props.page._id}`,
					"data-pagination": "",
					class: "mt-8"
				}, [createBaseVNode("div", {
					class: "cursor-pointer flex items-center gap-2.5",
					onClick: withModifiers($setup.inspectAction, ["prevent"])
				}, [
					$setup.hasLogic ? withDirectives((openBlock(), createBlock($setup["Icon"], {
						key: 0,
						"data-logic-attached": "",
						name: "logic-tree",
						class: "relative size-3.5! shrink-0 text-gray-400 dark:text-gray-500",
						"aria-label": $setup.__("Logic attached")
					}, null, 8, ["aria-label"])), [[_directive_tooltip, $setup.__("Logic attached")]]) : createCommentVNode("", true),
					$props.page.show_previous_button ? (openBlock(), createBlock($setup["Button"], {
						key: 1,
						variant: "filled",
						icon: "chevron-left",
						"data-editing-field": $setup.isInspectingAction ? "" : void 0,
						"data-editing-item": $setup.isInspectingAction ? "" : void 0,
						class: "ps-3",
						text: $props.page.previous_page_label?.length ? $setup.__($props.page.previous_page_label) : $setup.__("Previous Page")
					}, null, 8, [
						"data-editing-field",
						"data-editing-item",
						"text"
					])) : createCommentVNode("", true),
					createVNode($setup["Button"], {
						variant: "primary",
						"data-editing-field": $setup.isInspectingAction ? "" : void 0,
						"data-editing-item": $setup.isInspectingAction ? "" : void 0,
						class: "border-0! dark:border-0! ring-0! shadow-none!",
						style: { "--theme-color-primary": "var(--theme-color-gray-950)" },
						text: $props.page.button_label?.length ? $setup.__($props.page.button_label) : $setup.isLastPage ? $setup.__("Submit") : $setup.__("Next Page")
					}, null, 8, [
						"data-editing-field",
						"data-editing-item",
						"text"
					])
				])], 8, _hoisted_5$3)]),
				key: "0"
			} : void 0]), 1032, ["section", "can-delete-section"]), createBaseVNode("div", {
				tabindex: "-1",
				class: "section-gap-drop-zone mx-auto max-w-5xl h-14 -mt-8 flex items-center",
				"data-section-gap-index": sectionIndex + 1
			}, null, 8, _hoisted_6$2)], 64);
		}), 128))
	], 8, _hoisted_1$3);
}
var Page_default = /*#__PURE__*/ _plugin_vue_export_helper_default(Page_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$3], ["__file", "Page.vue"]]);
// resources/js/components/forms/builder/PageInspector.vue?vue&type=script&setup=true&lang.ts
var PageInspectorTabs = /* @__PURE__ */ function(PageInspectorTabs) {
	PageInspectorTabs["Settings"] = "settings";
	return PageInspectorTabs;
}(PageInspectorTabs || {});
var PageInspector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "PageInspector",
	setup(__props, { expose: __expose }) {
		__expose();
		const { deletePage, dirty, inspecting: page, pages, showFieldDirection } = injectBuilderContext();
		const confirmingDelete = ref(false);
		const activeTab = ref("settings");
		const pageIndex = computed(() => pages.value.findIndex((p) => p._id === page.value._id));
		const canDeletePage = computed(() => pages.value.length > 1);
		const pageTitle = computed(() => page.value.display ? __(page.value.display) : placeholderTitle.value);
		const placeholderTitle = computed(() => __("Page :current of :total", {
			current: pageIndex.value + 1,
			total: pages.value.length
		}));
		const confirmDelete = () => confirmingDelete.value = true;
		const handleDeletePage = () => deletePage(page.value._id);
		watch(() => page.value.display, dirty);
		watch(() => page.value.instructions, dirty);
		const __returned__ = {
			deletePage,
			dirty,
			page,
			pages,
			showFieldDirection,
			PageInspectorTabs,
			confirmingDelete,
			activeTab,
			pageIndex,
			canDeletePage,
			pageTitle,
			placeholderTitle,
			confirmDelete,
			handleDeletePage,
			get Button() {
				return Button_default;
			},
			get ConfirmationModal() {
				return ConfirmationModal_default;
			},
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Input() {
				return Input_default;
			},
			get TabContent() {
				return Content_default;
			},
			get TabList() {
				return List_default;
			},
			get Tabs() {
				return Tabs_default;
			},
			get TabTrigger() {
				return Trigger_default;
			},
			get Textarea() {
				return Textarea_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/PageInspector.vue
var _hoisted_1$2 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$2 = { class: "group/logic-tab space-y-6 pt-8" };
var _hoisted_3$2 = { class: "flex items-center gap-2.5" };
var _hoisted_4$2 = { class: "size-4" };
var _hoisted_5$2 = ["href"];
var _hoisted_6$1 = {
	key: 0,
	class: "grid *:[grid-area:1/1]"
};
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$2, [createVNode($setup["Tabs"], {
		modelValue: $setup.activeTab,
		"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.activeTab = $event),
		"unmount-on-hide": false
	}, {
		default: withCtx(() => [createVNode($setup["TabList"], { class: "inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!" }, {
			default: withCtx(() => [createVNode($setup["TabTrigger"], {
				name: $setup.PageInspectorTabs.Settings,
				text: _ctx.__("Settings")
			}, null, 8, ["name", "text"])]),
			_: 1
		}), createVNode($setup["TabContent"], { name: $setup.PageInspectorTabs.Settings }, {
			default: withCtx(() => [createBaseVNode("div", _hoisted_2$2, [
				createBaseVNode("div", _hoisted_3$2, [createBaseVNode("div", _hoisted_4$2, [createVNode($setup["Icon"], {
					name: "page",
					class: "size-4 text-gray-500 dark:text-gray-300"
				})]), createBaseVNode("a", {
					href: `#page-${$setup.page._id}`,
					class: "inline-flex items-center gap-1.5 text-xl font-medium antialiased"
				}, [createTextVNode(toDisplayString($setup.pageTitle) + " ", 1), $setup.showFieldDirection ? (openBlock(), createElementBlock("div", _hoisted_6$1, [createVNode($setup["Icon"], {
					name: "arrow-up",
					"data-field-direction-up": "",
					"aria-hidden": "true"
				}), createVNode($setup["Icon"], {
					name: "arrow-down",
					"data-field-direction-down": "",
					"aria-hidden": "true"
				})])) : createCommentVNode("", true)], 8, _hoisted_5$2)]),
				createVNode($setup["Field"], { label: _ctx.__("Label") }, {
					default: withCtx(() => [createVNode($setup["Input"], {
						modelValue: $setup.page.display,
						"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.page.display = $event),
						placeholder: $setup.placeholderTitle,
						focus: ""
					}, null, 8, ["modelValue", "placeholder"])]),
					_: 1
				}, 8, ["label"]),
				createVNode($setup["Field"], {
					label: _ctx.__("Help Text"),
					instructions: _ctx.__("Additional field instructions like this.")
				}, {
					default: withCtx(() => [createVNode($setup["Textarea"], {
						modelValue: $setup.page.instructions,
						"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.page.instructions = $event),
						rows: 2,
						resize: "vertical"
					}, null, 8, ["modelValue"])]),
					_: 1
				}, 8, ["label", "instructions"]),
				createVNode($setup["Button"], {
					size: "sm",
					icon: "trash",
					text: _ctx.__("Delete Page"),
					onClick: $setup.confirmDelete
				}, null, 8, ["text"])
			])]),
			_: 1
		}, 8, ["name"])]),
		_: 1
	}, 8, ["modelValue"]), createVNode($setup["ConfirmationModal"], {
		open: $setup.confirmingDelete,
		"onUpdate:open": _cache[3] || (_cache[3] = ($event) => $setup.confirmingDelete = $event),
		title: _ctx.__("Delete Page"),
		"body-text": _ctx.__("Are you sure you want to delete this page? All sections and fields in this page will also be deleted."),
		"button-text": _ctx.__("Delete"),
		danger: "",
		onConfirm: $setup.handleDeletePage
	}, null, 8, [
		"open",
		"title",
		"body-text",
		"button-text"
	])]);
}
var PageInspector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(PageInspector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$2], ["__file", "PageInspector.vue"]]);
// resources/js/components/forms/builder/SectionInspector.vue?vue&type=script&setup=true&lang.ts
var SectionInspector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "SectionInspector",
	setup(__props, { expose: __expose }) {
		__expose();
		const { dirty, inspecting: section } = injectBuilderContext();
		watch(() => section.value.display, dirty);
		const __returned__ = {
			dirty,
			section,
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Input() {
				return Input_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/builder/SectionInspector.vue
var _hoisted_1$1 = { class: "@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5" };
var _hoisted_2$1 = { class: "group/logic-tab space-y-6 pt-8" };
var _hoisted_3$1 = { class: "flex items-center gap-2.5" };
var _hoisted_4$1 = { class: "size-4" };
var _hoisted_5$1 = ["href"];
var _hoisted_6 = { class: "grid *:[grid-area:1/1]" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$1, [createBaseVNode("div", _hoisted_2$1, [createBaseVNode("div", _hoisted_3$1, [createBaseVNode("div", _hoisted_4$1, [createVNode($setup["Icon"], {
		name: "page",
		class: "size-4 text-gray-500 dark:text-gray-300"
	})]), createBaseVNode("a", {
		href: `#section-${$setup.section._id}`,
		class: "inline-flex items-center gap-1.5 text-xl font-medium antialiased"
	}, [createTextVNode(toDisplayString(_ctx.__($setup.section.display)) + " ", 1), createBaseVNode("div", _hoisted_6, [createVNode($setup["Icon"], {
		name: "arrow-up",
		"data-field-direction-up": "",
		"aria-hidden": "true"
	}), createVNode($setup["Icon"], {
		name: "arrow-down",
		"data-field-direction-down": "",
		"aria-hidden": "true"
	})])], 8, _hoisted_5$1)]), createVNode($setup["Field"], { label: _ctx.__("Label") }, {
		default: withCtx(() => [createVNode($setup["Input"], {
			modelValue: $setup.section.display,
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.section.display = $event),
			focus: ""
		}, null, 8, ["modelValue"])]),
		_: 1
	}, 8, ["label"])])]);
}
var SectionInspector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(SectionInspector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$1], ["__file", "SectionInspector.vue"]]);
// resources/js/pages/forms/Builder.vue?vue&type=script&setup=true&lang.ts
var InspectorType = /* @__PURE__ */ function(InspectorType) {
	InspectorType["Page"] = "page";
	InspectorType["Section"] = "section";
	InspectorType["Field"] = "field";
	InspectorType["Action"] = "action";
	InspectorType["FieldtypeHint"] = "fieldtype_hint";
	InspectorType["LinkFields"] = "link_fields";
	InspectorType["FieldsetImport"] = "import";
	return InspectorType;
}({});
var FieldView = /* @__PURE__ */ function(FieldView) {
	FieldView["Expanded"] = "expanded";
	FieldView["Collapsed"] = "collapsed";
	return FieldView;
}({});
var [injectBuilderContext, provideBuilderContext] = createContext("FormBuilder");
var Builder_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	layout: [
		Layout_default,
		PanelLayout_default,
		Layout_default$1
	],
	__name: "Builder",
	props: {
		form: {},
		initialFormFields: {},
		formsProInstalled: { type: Boolean },
		fieldtypes: {},
		action: {}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const formFields = ref(props.initialFormFields);
		const saving = ref(false);
		const inspecting = ref(null);
		const inspectorType = ref(null);
		const fieldView = ref("expanded");
		const isLeftPanelOpen = ref(false);
		const isRightPanelOpen = ref(false);
		const saveBinding = ref(null);
		const errors = ref({});
		const pages = computed(() => formFields.value.pages);
		const allSections = computed(() => pages.value.flatMap((page) => page.sections));
		const shouldShowViewSelector = computed(() => allSections.value.some((section) => section.fields.length > 0));
		const fieldCount = computed(() => allSections.value.flatMap((section) => section.fields).length);
		const inspect = (type, data) => {
			inspecting.value = data;
			inspectorType.value = type;
		};
		const clearInspector = () => {
			inspecting.value = null;
			inspectorType.value = null;
		};
		const toggleLeftPanel = () => isLeftPanelOpen.value = !isLeftPanelOpen.value;
		const toggleRightPanel = () => isRightPanelOpen.value = !isRightPanelOpen.value;
		const addPage = (atIndex = null, sections = []) => {
			const page = {
				_id: uniqid(),
				display: null,
				instructions: null,
				button_label: null,
				show_previous_button: false,
				previous_page_label: null,
				rules: [],
				sections
			};
			formFields.value.pages.splice(atIndex ?? formFields.value.pages.length, 0, page);
			dirty();
			inspect("page", page);
			return page;
		};
		const addPageAt = (pageId, sectionIndex, fieldIndex = null) => {
			const pageIndex = pages.value.findIndex((p) => p._id === pageId);
			if (pageIndex === -1) return;
			const sections = pages.value[pageIndex].sections;
			let sectionsForNewPage = [];
			if (fieldIndex !== null && sectionIndex < sections.length) {
				const remainingFields = sections[sectionIndex].fields.splice(fieldIndex);
				if (remainingFields.length > 0) sectionsForNewPage.push({
					_id: uniqid(),
					collapsed: false,
					display: __$1("Section"),
					fields: remainingFields
				});
				sectionsForNewPage.push(...sections.splice(sectionIndex + 1));
			} else sectionsForNewPage = sections.splice(sectionIndex);
			if (sectionsForNewPage.length === 0) sectionsForNewPage.push({
				_id: uniqid(),
				collapsed: false,
				display: __$1("Section"),
				fields: []
			});
			addPage(pageIndex + 1, sectionsForNewPage);
		};
		const deletePage = (pageId) => {
			if (pages.value.length <= 1) return;
			const pageIndex = pages.value.findIndex((p) => p._id === pageId);
			if (pageIndex === -1) return;
			formFields.value.pages.splice(pageIndex, 1);
			clearInspector();
			dirty();
		};
		const addSection = (pageId, atIndex, fields = []) => {
			const page = pages.value.find((p) => p._id === pageId);
			if (!page) return;
			const section = {
				_id: uniqid(),
				collapsed: false,
				display: __$1("Section"),
				fields
			};
			page.sections.splice(atIndex, 0, section);
			dirty();
			inspect("section", section);
			return section;
		};
		const addSectionAt = (pageId, sourceSectionId, fieldIndex) => {
			const page = pages.value.find((p) => p._id === pageId);
			if (!page) return;
			const sourceSection = page.sections.find((s) => s._id === sourceSectionId);
			if (!sourceSection) return;
			const sourceSectionIndex = page.sections.indexOf(sourceSection);
			const movedFields = sourceSection.fields.splice(fieldIndex);
			addSection(pageId, sourceSectionIndex + 1, movedFields);
		};
		const addField = (pageId, sectionId, fieldtypeHandle, atIndex) => {
			const page = pages.value.find((p) => p._id === pageId);
			if (!page) return;
			const section = page.sections.find((s) => s._id === sectionId);
			if (!section) return;
			const fieldtype = props.fieldtypes.find((f) => f.handle === fieldtypeHandle);
			if (!fieldtype) return;
			const _id = uniqid();
			const field = {
				_id,
				config: {
					type: fieldtypeHandle,
					display: ensureUniqueDisplay(__$1(fieldtype.title)),
					hidden: false
				},
				fieldtype: fieldtypeHandle,
				handle: null,
				isNew: true,
				icon: fieldtype?.icon || "fieldtype-generic",
				type: "inline",
				preview: {
					config: {
						...fieldtype.preview?.config,
						handle: _id
					},
					value: fieldtype.preview?.value,
					meta: fieldtype.preview?.meta
				}
			};
			section.fields.splice(atIndex, 0, field);
			inspect("field", field);
			dirty();
			setTimeout(() => document.getElementById("field_display")?.select(), 250);
		};
		const ensureUniqueDisplay = (display) => {
			const displays = pages.value.flatMap((page) => page.sections).flatMap((section) => section.fields).map((field) => field.config?.display);
			if (!displays.includes(display)) return display;
			let count = 2;
			while (displays.includes(`${display} ${count}`)) count++;
			return `${display} ${count}`;
		};
		const onFieldtypeDrop = ({ pageId, fieldtypeHandle, sectionId, sectionIndex, fieldIndex }) => {
			if (fieldtypeHandle === "page_break") {
				addPageAt(pageId, sectionIndex, fieldIndex);
				return;
			}
			if (fieldtypeHandle === "section") {
				fieldIndex !== null && sectionId ? addSectionAt(pageId, sectionId, fieldIndex) : addSection(pageId, sectionIndex);
				return;
			}
			if (fieldtypeHandle === "fieldset") {
				const page = pages.value.find((p) => p._id === pageId);
				if (!page) return;
				const section = page.sections.find((s) => s._id === sectionId);
				if (!section) return;
				let fieldsetImport = {
					_id: uniqid(),
					type: "link_fields"
				};
				section.fields.splice(fieldIndex, 0, fieldsetImport);
				inspect("link_fields", fieldsetImport);
				return;
			}
			addField(pageId, sectionId, fieldtypeHandle, fieldIndex);
		};
		useFieldtypeDraggable({
			pages,
			onDragStart: () => isLeftPanelOpen.value = false,
			onDrop: onFieldtypeDrop
		});
		const avoidTrackingDirtyState = ref(false);
		const trackingDirtyState = computed(() => !avoidTrackingDirtyState.value);
		const isDirty = computed(() => Statamic.$dirty.has("form-builder"));
		function dirty() {
			if (trackingDirtyState.value) Statamic.$dirty.add("form-builder");
		}
		function clearDirtyState() {
			Statamic.$dirty.remove("form-builder");
		}
		function withoutDirtying(callback) {
			const previous = avoidTrackingDirtyState.value;
			avoidTrackingDirtyState.value = true;
			callback();
			nextTick(() => avoidTrackingDirtyState.value = previous);
		}
		const save = () => {
			if (saving.value) return;
			errors.value = {};
			saving.value = true;
			axios.patch(props.action, formFields.value).then((response) => {
				pages.value.flatMap((page) => page.sections).flatMap((section) => section.fields).forEach((field) => delete field.isNew);
				clearDirtyState();
				Statamic.$toast.success(__$1("Saved"));
			}).catch((e) => {
				if (e.response?.status === 422) {
					errors.value = e.response.data.errors;
					Statamic.$toast.error(e.response.data.message);
				} else Statamic.$toast.error(__$1("Something went wrong"));
			}).finally(() => saving.value = false);
		};
		const { showFieldNumbers } = useFieldNumberingPreference();
		const fieldNumbers = computed(() => {
			if (!showFieldNumbers.value) return /* @__PURE__ */ new Map();
			const fieldsets = Object.values(usePage().props.fieldsets ?? {});
			const fieldKeys = pages.value.flatMap((page) => page.sections || []).flatMap((section) => section.fields || []).flatMap((field) => {
				if (field.type === "link_fields") return [];
				if (field.type === "import") return (fieldsets.find((fieldset) => fieldset.handle === field.fieldset)?.fields || []).filter((f) => f.type !== "import").map((f) => [`${field._id}:${f.handle}`]);
				let isInformational = props.fieldtypes.find((fieldtype) => fieldtype.handle === field.fieldtype)?.categories?.[0] === "information";
				if (field.config?.hidden || isInformational) return [];
				return [[field._id, field.handle]];
			});
			const map = /* @__PURE__ */ new Map();
			fieldKeys.forEach((keys, index) => keys.forEach((key) => map.set(key, index + 1)));
			return map;
		});
		provide("fieldNumbers", fieldNumbers);
		provideBuilderContext({
			addSection,
			deletePage,
			clearInspector,
			dirty,
			ensureUniqueDisplay,
			errors,
			fieldtypes: props.fieldtypes,
			fieldView,
			form: props.form,
			formsProInstalled: props.formsProInstalled,
			inspect,
			inspecting,
			inspectorType,
			isDirty,
			isLeftPanelOpen,
			isRightPanelOpen,
			pages,
			showFieldDirection: true,
			toggleLeftPanel,
			toggleRightPanel,
			withoutDirtying
		});
		const onEscape = (event) => {
			if (event.key !== "Escape") return;
			if (isLeftPanelOpen.value || isRightPanelOpen.value) {
				isLeftPanelOpen.value = false;
				isRightPanelOpen.value = false;
				return;
			}
			if (inspecting.value) clearInspector();
		};
		watch(saving, (saving) => progress.loading("form-builder", saving));
		onMounted(() => {
			if (formFields.value.pages.length === 0) addPage();
			document.addEventListener("keydown", onEscape);
			saveBinding.value = keys.bindGlobal(["return", "mod+s"], (e) => {
				e.preventDefault();
				save();
			});
		});
		onUnmounted(() => {
			clearDirtyState();
			document.removeEventListener("keydown", onEscape);
			saveBinding.value?.destroy();
		});
		const __returned__ = {
			InspectorType,
			FieldView,
			injectBuilderContext,
			provideBuilderContext,
			props,
			formFields,
			saving,
			inspecting,
			inspectorType,
			fieldView,
			isLeftPanelOpen,
			isRightPanelOpen,
			saveBinding,
			errors,
			pages,
			allSections,
			shouldShowViewSelector,
			fieldCount,
			inspect,
			clearInspector,
			toggleLeftPanel,
			toggleRightPanel,
			addPage,
			addPageAt,
			deletePage,
			addSection,
			addSectionAt,
			addField,
			ensureUniqueDisplay,
			onFieldtypeDrop,
			avoidTrackingDirtyState,
			trackingDirtyState,
			isDirty,
			dirty,
			clearDirtyState,
			withoutDirtying,
			save,
			showFieldNumbers,
			fieldNumbers,
			onEscape,
			get Button() {
				return Button_default;
			},
			get Header() {
				return Header_default$1;
			},
			get Icon() {
				return Icon_default;
			},
			get ToggleGroup() {
				return Group_default$1;
			},
			get ToggleItem() {
				return Item_default$1;
			},
			FormStatusIndicator: FormStatusIndicator_default,
			Head: Head_default,
			LayoutPanel: LayoutPanel_default,
			ActionInspector: ActionInspector_default,
			EmptyInspector: EmptyInspector_default,
			FieldInspector: FieldInspector_default,
			FieldsetInspector: FieldsetInspector_default,
			FieldtypeHint: FieldtypeHint_default,
			FieldtypeSelector: FieldtypeSelector_default,
			LinkFieldsInspector: LinkFieldsInspector_default,
			Page: Page_default,
			PageInspector: PageInspector_default,
			SectionInspector: SectionInspector_default,
			FieldNumberingToggle: FieldNumberingToggle_default,
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/pages/forms/Builder.vue
var Builder_exports = /* @__PURE__ */ __exportAll({
	FieldView: () => FieldView,
	InspectorType: () => InspectorType,
	default: () => Builder_default,
	injectBuilderContext: () => injectBuilderContext,
	provideBuilderContext: () => provideBuilderContext
});
var _hoisted_1 = { class: "hidden sm:inline" };
var _hoisted_2 = { class: "col-span-full row-start-1 max-[1000px]:pt-14" };
var _hoisted_3 = { class: "flex items-center gap-2.5" };
var _hoisted_4 = {
	key: 0,
	class: "mx-auto text-center max-w-5xl max-[600px]:p-5 px-5.75 sm:px-6.25 mb-5 text-sm text-gray-600 dark:text-gray-300"
};
var _hoisted_5 = ["data-visible"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [
			$setup.__("Edit"),
			$setup.__($props.form.title),
			$setup.__("Forms")
		] }, null, 8, ["title"]),
		(openBlock(), createBlock(Teleport, { to: "#form-layout-actions" }, [createVNode($setup["Button"], {
			variant: "primary",
			"aria-label": $setup.__("Save"),
			disabled: $setup.saving,
			onClick: $setup.save
		}, {
			default: withCtx(() => [createVNode($setup["Icon"], {
				name: "save",
				class: "sm:hidden"
			}), createBaseVNode("span", _hoisted_1, toDisplayString($setup.__("Save")), 1)]),
			_: 1
		}, 8, ["aria-label", "disabled"])])),
		createVNode($setup["Button"], {
			class: "min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above) sm:-translate-x-3 md:-translate-x-9 col-start-1 row-start-1",
			text: $setup.__("Form Builder"),
			icon: "bar-sidebar-left-panel-open",
			onClick: $setup.toggleLeftPanel
		}, null, 8, ["text"]),
		createVNode($setup["LayoutPanel"], {
			side: "left",
			"mobile-open": $setup.isLeftPanelOpen
		}, {
			default: withCtx(() => [createVNode($setup["FieldtypeSelector"])]),
			_: 1
		}, 8, ["mobile-open"]),
		createBaseVNode("div", _hoisted_2, [
			createVNode($setup["Header"], { class: "mx-auto max-w-5xl" }, {
				title: withCtx(() => [createVNode($setup["FormStatusIndicator"], { status: $props.form.status }, null, 8, ["status"]), createTextVNode(" " + toDisplayString($setup.__($props.form.title)), 1)]),
				actions: withCtx(() => [createBaseVNode("div", _hoisted_3, [createVNode($setup["FieldNumberingToggle"]), $setup.shouldShowViewSelector ? (openBlock(), createBlock($setup["ToggleGroup"], {
					key: 0,
					modelValue: $setup.fieldView,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.fieldView = $event),
					size: "xs"
				}, {
					default: withCtx(() => [withDirectives(createVNode($setup["ToggleItem"], {
						value: $setup.FieldView.Expanded,
						icon: "expand",
						"aria-label": $setup.__("Expanded view")
					}, null, 8, ["value", "aria-label"]), [[_directive_tooltip, $setup.__("Expanded view")]]), withDirectives(createVNode($setup["ToggleItem"], {
						value: $setup.FieldView.Collapsed,
						icon: "collapse",
						"aria-label": $setup.__("Collapsed view")
					}, null, 8, ["value", "aria-label"]), [[_directive_tooltip, $setup.__("Collapsed view")]])]),
					_: 1
				}, 8, ["modelValue"])) : createCommentVNode("", true)])]),
				_: 1
			}),
			(openBlock(true), createElementBlock(Fragment, null, renderList($setup.pages, (page) => {
				return openBlock(), createBlock($setup["Page"], {
					key: page._id,
					page
				}, null, 8, ["page"]);
			}), 128)),
			$setup.fieldView === $setup.FieldView.Collapsed ? (openBlock(), createElementBlock("p", _hoisted_4, [createBaseVNode("strong", null, toDisplayString($setup.fieldCount), 1), createTextVNode(" " + toDisplayString(_ctx.__n("field on this form|fields on this form", $setup.fieldCount)), 1)])) : createCommentVNode("", true)
		]),
		$setup.inspectorType ? (openBlock(), createBlock($setup["Button"], {
			key: 0,
			class: "min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above) sm:translate-x-3 md:translate-x-9 mb-5 col-start-3 row-start-1",
			text: $setup.__("Settings"),
			icon: "cog",
			onClick: $setup.toggleRightPanel
		}, null, 8, ["text"])) : createCommentVNode("", true),
		createVNode($setup["LayoutPanel"], {
			side: "right",
			"mobile-open": $setup.isRightPanelOpen
		}, {
			default: withCtx(() => [
				$setup.inspectorType === $setup.InspectorType.Action ? (openBlock(), createBlock($setup["ActionInspector"], { key: 0 })) : createCommentVNode("", true),
				$setup.inspectorType === $setup.InspectorType.Page ? (openBlock(), createBlock($setup["PageInspector"], { key: 1 })) : createCommentVNode("", true),
				$setup.inspectorType === $setup.InspectorType.Section ? (openBlock(), createBlock($setup["SectionInspector"], { key: 2 })) : createCommentVNode("", true),
				$setup.inspectorType === $setup.InspectorType.Field ? (openBlock(), createBlock($setup["FieldInspector"], { key: 3 })) : createCommentVNode("", true),
				$setup.inspectorType === $setup.InspectorType.LinkFields ? (openBlock(), createBlock($setup["LinkFieldsInspector"], { key: 4 })) : createCommentVNode("", true),
				$setup.inspectorType === $setup.InspectorType.FieldsetImport ? (openBlock(), createBlock($setup["FieldsetInspector"], { key: 5 })) : createCommentVNode("", true),
				$setup.inspectorType === $setup.InspectorType.FieldtypeHint ? (openBlock(), createBlock($setup["FieldtypeHint"], { key: 6 })) : createCommentVNode("", true),
				!$setup.inspectorType ? (openBlock(), createBlock($setup["EmptyInspector"], { key: 7 })) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["mobile-open"]),
		createBaseVNode("div", {
			class: "mobile-panel-backdrop",
			"data-visible": $setup.isLeftPanelOpen || $setup.isRightPanelOpen,
			onClick: _cache[1] || (_cache[1] = ($event) => {
				$setup.isLeftPanelOpen = false;
				$setup.isRightPanelOpen = false;
			})
		}, null, 8, _hoisted_5)
	], 64);
}
var Builder_default = /*#__PURE__*/ _plugin_vue_export_helper_default(Builder_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "Builder.vue"]]);
export { FieldInspector_default as a, useSortable as i, provideBuilderContext as n, writeFieldConditions as o, PageInspector_default as r, PageRule_default as s, Builder_exports as t };
