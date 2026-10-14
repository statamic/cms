import { r as __toESM } from "./rolldown-runtime-B-1-B7_t.js";
import { $ as useTemplateRef, A as inject, At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, F as onBeforeUnmount, G as renderSlot, H as provide, K as resolveComponent, L as onMounted, N as nextTick, R as onUnmounted, S as createTextVNode, T as defineComponent, W as renderList, _ as createBlock, _t as ref, at as withDirectives, c as vShow, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, kt as normalizeStyle, p as Teleport, q as resolveDirective, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-B-BfxmJA.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { G as axios, o as usePage } from "./index.esm-B-zRpAhf.js";
import { $ as Item_default$1, $t as Menu_default, Fr as Primitive, G as Converter_default, K as KEYS, Ti as Icon_default, Ut as Header_default$1, Wn as require_fuzzysort, ar as Panel_default, cr as Heading_default, et as Group_default, fi as Badge_default, lr as Card_default, nn as Dropdown_default, or as Subheading_default, si as Button_default, sr as Header_default, tn as Item_default } from "./ui-DPzuvYkS.js";
import { h as nanoid } from "./globals-B4fSnfEe.js";
import { b as preferences, g as keys } from "./api-DX2Pd1oX.js";
import { M as FieldNumberingToggle_default, N as useFieldNumberingPreference, R as Builder_default, _ as categoryColorClasses, g as categories, l as Layout_default, o as operatorLabel, r as Head_default, s as FieldNumber_default, v as collectsValue } from "./index-DJE_WRsq.js";
import { t as FormStatusIndicator_default } from "./FormStatusIndicator-CAX13lG0.js";
import { t as Layout_default$1 } from "./Layout-Beuk7oam.js";
import { t as LayoutPanel_default } from "./LayoutPanel-LbEHwEdJ.js";
import { t as PanelLayout_default } from "./PanelLayout-DGN-nSBD.js";
import { a as FieldInspector_default, i as useSortable, n as provideBuilderContext, o as writeFieldConditions, r as PageInspector_default, s as PageRule_default } from "./Builder-CMM1QwKA.js";
//#region resources/js/components/forms/logic/LogicRulePicker.vue
var import_fuzzysort = /* @__PURE__ */ __toESM(require_fuzzysort());
var _sfc_main$7 = {
	__name: "LogicRulePicker",
	props: {
		items: {
			type: Array,
			default: () => []
		},
		align: {
			type: String,
			default: "start"
		},
		searchPlaceholder: {
			type: String,
			default: null
		}
	},
	emits: ["added"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const search = ref(null);
		const selectionIndex = ref(0);
		const keybindings = ref([]);
		const isOpen = ref(false);
		const hasMultipleItems = computed(() => props.items.length > 1);
		const visibleItems = computed(() => {
			let items = props.items.filter((item) => !item.hide);
			if (search.value) items = import_fuzzysort.default.go(search.value, items, {
				all: true,
				keys: [
					(item) => __(item.display),
					"handle",
					(item) => __(item.instructions)
				],
				scoreFn: (scores) => {
					const displayScore = scores[0]?.score ?? -Infinity;
					const handleScore = scores[1]?.score ?? -Infinity;
					const instructionsScore = (scores[2]?.score ?? -Infinity) * .5;
					return Math.max(displayScore, handleScore, instructionsScore);
				}
			}).map((result) => result.obj);
			return items;
		});
		const noSearchResults = computed(() => search.value && visibleItems.value.length === 0);
		const searchPlaceholderText = computed(() => props.searchPlaceholder ? __(props.searchPlaceholder) : __("Search"));
		const selectItem = (handle) => {
			emit("added", handle);
			isOpen.value = false;
			search.value = null;
		};
		const singleButtonClicked = () => selectItem(props.items[0].handle);
		const bindKeys = () => {
			keybindings.value = [
				keys.bindGlobal("up", keypressUp),
				keys.bindGlobal("down", keypressDown),
				keys.bindGlobal("enter", keypressEnter)
			];
		};
		const unbindKeys = () => {
			keybindings.value.forEach((binding) => binding.destroy());
			keybindings.value = [];
		};
		const keypressUp = (e) => {
			e.preventDefault();
			selectionIndex.value = selectionIndex.value === 0 ? visibleItems.value.length - 1 : selectionIndex.value - 1;
		};
		const keypressDown = (e) => {
			e.preventDefault();
			selectionIndex.value = selectionIndex.value === visibleItems.value.length - 1 ? 0 : selectionIndex.value + 1;
		};
		const keypressEnter = (e) => {
			e.preventDefault();
			const item = visibleItems.value[selectionIndex.value];
			if (item) selectItem(item.handle);
		};
		watch(isOpen, (open) => {
			if (open) bindKeys();
			else unbindKeys();
		});
		watch(search, () => selectionIndex.value = 0);
		onBeforeUnmount(() => unbindKeys());
		const __returned__ = {
			emit,
			props,
			search,
			selectionIndex,
			keybindings,
			isOpen,
			hasMultipleItems,
			visibleItems,
			noSearchResults,
			searchPlaceholderText,
			selectItem,
			singleButtonClicked,
			bindKeys,
			unbindKeys,
			keypressUp,
			keypressDown,
			keypressEnter,
			get Primitive() {
				return Primitive;
			},
			get fuzzysort() {
				return import_fuzzysort.default;
			},
			computed,
			ref,
			watch,
			onBeforeUnmount,
			get keys() {
				return keys;
			},
			FieldNumber: FieldNumber_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$11 = { class: "flex items-center border-b border-gray-200 dark:border-gray-600 p-1.5 gap-1.5" };
var _hoisted_2$11 = { class: "max-h-[21rem] overflow-auto p-1.5 st-custom-scrollbar" };
var _hoisted_3$9 = ["onMouseover", "title"];
var _hoisted_4$7 = ["onClick"];
var _hoisted_5$4 = { class: "flex-1" };
var _hoisted_6$3 = { class: "line-clamp-1 text-sm text-gray-900 dark:text-gray-200" };
var _hoisted_7$3 = {
	key: 0,
	class: "p-3 text-center text-xs text-gray-600"
};
function _sfc_render$12(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_input = resolveComponent("ui-input");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_ui_description = resolveComponent("ui-description");
	const _component_ui_popover = resolveComponent("ui-popover");
	return !$setup.hasMultipleItems ? (openBlock(), createBlock($setup["Primitive"], {
		key: 0,
		"as-child": "",
		onClick: $setup.singleButtonClicked
	}, {
		default: withCtx(() => [renderSlot(_ctx.$slots, "trigger")]),
		_: 3
	})) : (openBlock(), createBlock(_component_ui_popover, {
		key: 1,
		align: $props.align,
		open: $setup.isOpen,
		"onUpdate:open": _cache[1] || (_cache[1] = ($event) => $setup.isOpen = $event),
		class: "select-none w-72 rounded-b-lg",
		inset: ""
	}, {
		trigger: withCtx(() => [renderSlot(_ctx.$slots, "trigger")]),
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$11, [createVNode(_component_ui_input, {
			placeholder: $setup.searchPlaceholderText,
			class: "[&_svg]:size-5",
			"icon-prepend": "magnifying-glass",
			size: "sm",
			type: "text",
			modelValue: $setup.search,
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.search = $event),
			variant: "ghost"
		}, null, 8, ["placeholder", "modelValue"])]), createBaseVNode("div", _hoisted_2$11, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.visibleItems, (item, i) => {
			return openBlock(), createElementBlock("div", {
				key: item.handle,
				class: normalizeClass(["cursor-pointer rounded-md", { "bg-gray-100 dark:bg-gray-900": $setup.selectionIndex === i }]),
				onMouseover: ($event) => $setup.selectionIndex = i,
				title: _ctx.__(item.instructions)
			}, [createBaseVNode("div", {
				onClick: ($event) => $setup.selectItem(item.handle),
				class: "flex items-center rounded-lg p-2.5 gap-2 sm:gap-3 cursor-pointer"
			}, [createVNode(_component_ui_icon, {
				name: item.icon || "plus",
				class: normalizeClass(["size-4", item.iconClass || "text-gray-600 dark:text-gray-300"])
			}, null, 8, ["name", "class"]), createBaseVNode("div", _hoisted_5$4, [createBaseVNode("div", _hoisted_6$3, [createVNode($setup["FieldNumber"], {
				"field-key": item.handle,
				class: "me-1"
			}, null, 8, ["field-key"]), createTextVNode(toDisplayString(_ctx.__(item.display || item.handle)), 1)]), item.instructions ? (openBlock(), createBlock(_component_ui_description, {
				key: 0,
				class: "w-56 truncate text-2xs"
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__(item.instructions)), 1)]),
				_: 2
			}, 1024)) : createCommentVNode("", true)])], 8, _hoisted_4$7)], 42, _hoisted_3$9);
		}), 128)), $setup.noSearchResults ? (openBlock(), createElementBlock("div", _hoisted_7$3, toDisplayString(_ctx.__("No results")), 1)) : createCommentVNode("", true)])]),
		_: 3
	}, 8, ["align", "open"]));
}
var LogicRulePicker_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$7, [["render", _sfc_render$12], ["__file", "LogicRulePicker.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/AddLogicRuleButton.vue
var _sfc_main$6 = {
	__name: "AddLogicRuleButton",
	props: {
		items: {
			type: Array,
			default: () => []
		},
		label: String,
		showConnector: {
			type: Boolean,
			default: true
		},
		searchPlaceholder: String
	},
	emits: ["added"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const __returned__ = {
			emit,
			props,
			buttonLabel: computed(() => props.label ? __(props.label) : __("Add Rule")),
			LogicRulePicker: LogicRulePicker_default,
			get Button() {
				return Button_default;
			},
			computed
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$10 = { class: "inline-block" };
var _hoisted_2$10 = {
	key: 0,
	class: "absolute group-hover:opacity-0 transition-opacity delay-25 duration-125 inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-gray-850"
};
function _sfc_render$11(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$10, [createVNode($setup["LogicRulePicker"], {
		items: $props.items,
		"search-placeholder": $props.searchPlaceholder,
		onAdded: _cache[0] || (_cache[0] = ($event) => $setup.emit("added", $event))
	}, {
		trigger: withCtx(() => [createBaseVNode("div", { class: normalizeClass(["inline-flex relative pt-2", { "pt-6": $props.showConnector }]) }, [$props.showConnector ? (openBlock(), createElementBlock("div", _hoisted_2$10)) : createCommentVNode("", true), createVNode($setup["Button"], {
			size: "sm",
			text: $setup.buttonLabel,
			icon: "plus",
			class: "relative"
		}, null, 8, ["text"])], 2)]),
		_: 1
	}, 8, ["items", "search-placeholder"])]);
}
var AddLogicRuleButton_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$6, [["render", _sfc_render$11], ["__file", "AddLogicRuleButton.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/PageLogicRule.vue
var _sfc_main$5 = {
	__name: "PageLogicRule",
	props: {
		rule: {
			type: Object,
			required: true
		},
		pageId: {
			type: String,
			required: true
		},
		pageDisplay: {
			type: String,
			required: true
		},
		id: String,
		collapsed: Boolean,
		suggestableFields: {
			type: Array,
			default: () => []
		},
		pageDestinationOptions: {
			type: Array,
			default: () => []
		},
		fieldtypes: Array
	},
	emits: [
		"collapsed",
		"expanded",
		"removed",
		"update:rule"
	],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const getFieldConfig = (handle) => props.suggestableFields.find((field) => field.handle === handle);
		const getFieldDisplay = (handle) => __(getFieldConfig(handle)?.config?.display) || handle;
		const getIconClass = (category) => {
			return categoryColorClasses[categories[category]?.color || "gray"]?.icon || "text-gray-600 dark:text-gray-400";
		};
		const firstFieldConfig = computed(() => {
			const firstCondition = props.rule.conditions?.[0];
			if (!firstCondition?.field) return null;
			const field = getFieldConfig(firstCondition.field);
			return {
				handle: firstCondition.field,
				display: __(field?.config?.display) || firstCondition.field,
				icon: field?.icon || "generic-field",
				iconClass: getIconClass(field?.category)
			};
		});
		const previewParts = computed(() => {
			if (!props.rule.conditions || props.rule.conditions.length === 0) return null;
			const parts = [];
			props.rule.conditions.forEach((condition, index) => {
				if (!condition.field) return;
				if (index === 0) {
					parts.push({
						type: "operator",
						text: operatorLabel(condition.operator)
					});
					if (condition.value !== null && condition.value !== void 0 && condition.value !== "") {
						const displayValue = Array.isArray(condition.value) ? condition.value.join(", ") : String(condition.value);
						parts.push({
							type: "value",
							text: displayValue
						});
					}
				} else {
					parts.push({
						type: "join",
						text: condition.join === "or" ? __("or") : __("and")
					});
					parts.push({
						type: "field-plain",
						text: getFieldDisplay(condition.field)
					});
					parts.push({
						type: "operator",
						text: operatorLabel(condition.operator)
					});
					if (condition.value !== null && condition.value !== void 0 && condition.value !== "") {
						const displayValue = Array.isArray(condition.value) ? condition.value.join(", ") : String(condition.value);
						parts.push({
							type: "value",
							text: displayValue
						});
					}
				}
			});
			if (parts.length === 0) return null;
			const destination = props.pageDestinationOptions.find((p) => p.value === props.rule.destination);
			parts.push({
				type: "destination",
				text: destination?.label || props.rule.destination || __("Select page")
			});
			return parts;
		});
		const collapsedSummary = computed(() => {
			if (!props.rule.conditions || props.rule.conditions.length === 0) return __("No conditions configured");
			if (!previewParts.value) return __("Configure conditions");
			return null;
		});
		const toggleCollapsedState = () => props.collapsed ? emit("expanded") : emit("collapsed");
		const __returned__ = {
			emit,
			props,
			getFieldConfig,
			getFieldDisplay,
			getIconClass,
			firstFieldConfig,
			previewParts,
			collapsedSummary,
			toggleCollapsedState,
			computed,
			get Badge() {
				return Badge_default;
			},
			get Button() {
				return Button_default;
			},
			get Dropdown() {
				return Dropdown_default;
			},
			get DropdownItem() {
				return Item_default;
			},
			get DropdownMenu() {
				return Menu_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Subheading() {
				return Subheading_default;
			},
			PageRule: PageRule_default,
			FieldNumber: FieldNumber_default,
			get categories() {
				return categories;
			},
			get categoryColorClasses() {
				return categoryColorClasses;
			},
			get operatorLabel() {
				return operatorLabel;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$9 = ["data-collapsed"];
var _hoisted_2$9 = {
	key: 0,
	class: "lowercase"
};
var _hoisted_3$8 = {
	key: 1,
	class: "font-mono text-gray-900 dark:text-gray-100"
};
var _hoisted_4$6 = {
	key: 3,
	class: "text-gray-700 dark:text-gray-300"
};
var _hoisted_5$3 = { class: "st-text-trim-cap" };
var _hoisted_6$2 = { class: "st-text-trim-cap" };
var _hoisted_7$2 = ["tabindex", "inert"];
var _hoisted_8$2 = { class: "p-4" };
function _sfc_render$10(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", null, [createBaseVNode("div", {
		layout: "",
		"data-replicator-rule": "",
		class: "@container relative w-full rounded-lg border border-gray-300 text-base dark:border-white/10 bg-white dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm",
		"data-collapsed": $props.collapsed ?? void 0
	}, [createBaseVNode("header", { class: normalizeClass(["group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 dark:bg-gray-925 border-gray-300 dark:shadow-md", {
		"bg-white dark:bg-gray-900": $props.collapsed,
		"bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none": !$props.collapsed
	}]) }, [createBaseVNode("button", {
		type: "button",
		class: "show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 ps-0 min-w-0 focus:outline-none cursor-pointer",
		onClick: $setup.toggleCollapsedState
	}, [
		$props.collapsed ? (openBlock(), createBlock($setup["Badge"], {
			key: 0,
			pill: "",
			size: "sm",
			color: "white",
			class: "font-medium text-gray-800 dark:text-gray-200"
		}, {
			default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("If")), 1)]),
			_: 1
		})) : createCommentVNode("", true),
		$props.collapsed && $setup.firstFieldConfig ? (openBlock(), createBlock($setup["Badge"], {
			key: 1,
			size: "lg",
			pill: "",
			color: "white",
			class: "px-3 text-gray-950 gap-1"
		}, {
			default: withCtx(() => [
				createVNode($setup["FieldNumber"], {
					"field-key": $setup.firstFieldConfig.handle,
					class: "me-0.5"
				}, null, 8, ["field-key"]),
				createVNode($setup["Icon"], {
					name: $setup.firstFieldConfig.icon,
					class: normalizeClass(["size-3.5 me-1 rounded-sm opacity-100!", $setup.firstFieldConfig.iconClass]),
					"aria-hidden": "true"
				}, null, 8, ["name", "class"]),
				createTextVNode(" " + toDisplayString($setup.firstFieldConfig.display), 1)
			]),
			_: 1
		})) : createCommentVNode("", true),
		withDirectives(createVNode($setup["Subheading"], { class: "overflow-hidden text-ellipsis whitespace-nowrap text-xs flex items-center gap-1" }, {
			default: withCtx(() => [$setup.collapsedSummary ? (openBlock(), createElementBlock("span", _hoisted_2$9, toDisplayString($setup.collapsedSummary), 1)) : $setup.previewParts ? (openBlock(true), createElementBlock(Fragment, { key: 1 }, renderList($setup.previewParts, (part, index) => {
				return openBlock(), createElementBlock(Fragment, { key: index }, [part.type === "operator" ? (openBlock(), createBlock($setup["Badge"], {
					key: 0,
					class: "inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
				}, {
					default: withCtx(() => [createTextVNode(toDisplayString(part.text), 1)]),
					_: 2
				}, 1024)) : part.type === "value" ? (openBlock(), createElementBlock("span", _hoisted_3$8, toDisplayString(part.text), 1)) : part.type === "join" ? (openBlock(), createBlock($setup["Badge"], {
					key: 2,
					class: "inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
				}, {
					default: withCtx(() => [createTextVNode(toDisplayString(part.text), 1)]),
					_: 2
				}, 1024)) : part.type === "field-plain" ? (openBlock(), createElementBlock("span", _hoisted_4$6, toDisplayString(part.text), 1)) : part.type === "destination" ? (openBlock(), createBlock($setup["Badge"], {
					key: 4,
					pill: "",
					class: "inline-flex items-center gap-1.25 px-1.5 py-1.25 dark:py-1.25 font-medium bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-300"
				}, {
					default: withCtx(() => [
						createVNode($setup["Icon"], {
							name: "page",
							class: "size-2.75! shrink-0",
							"aria-hidden": "true"
						}),
						createBaseVNode("span", _hoisted_5$3, toDisplayString(_ctx.__("Go to")), 1),
						createBaseVNode("span", _hoisted_6$2, toDisplayString(part.text), 1)
					]),
					_: 2
				}, 1024)) : createCommentVNode("", true)], 64);
			}), 128)) : createCommentVNode("", true)]),
			_: 1
		}, 512), [[vShow, $props.collapsed]]),
		!$props.collapsed && $setup.firstFieldConfig ? (openBlock(), createBlock($setup["Badge"], {
			key: 2,
			size: "lg",
			pill: "",
			color: "white",
			class: "px-3 text-gray-950 gap-1"
		}, {
			default: withCtx(() => [
				createVNode($setup["FieldNumber"], {
					"field-key": $setup.firstFieldConfig.handle,
					class: "me-0.5"
				}, null, 8, ["field-key"]),
				createVNode($setup["Icon"], {
					name: $setup.firstFieldConfig.icon,
					class: normalizeClass(["size-3.5 me-0.5 rounded-sm opacity-100!", $setup.firstFieldConfig.iconClass]),
					"aria-hidden": "true"
				}, null, 8, ["name", "class"]),
				createTextVNode(" " + toDisplayString($setup.firstFieldConfig.display), 1)
			]),
			_: 1
		})) : createCommentVNode("", true)
	]), createVNode($setup["Dropdown"], { align: "end" }, {
		trigger: withCtx(() => [createVNode($setup["Button"], {
			icon: "dots",
			variant: "ghost",
			size: "xs",
			class: "me-2",
			"aria-label": _ctx.__("Open row actions")
		}, null, 8, ["aria-label"])]),
		default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
			default: withCtx(() => [createVNode($setup["DropdownItem"], {
				text: _ctx.__("Delete rule"),
				icon: "trash",
				variant: "destructive",
				onClick: _cache[0] || (_cache[0] = ($event) => $setup.emit("removed"))
			}, null, 8, ["text"])]),
			_: 1
		})]),
		_: 1
	})], 2), withDirectives(createBaseVNode("div", { class: normalizeClass([{
		"contain-paint": $props.collapsed,
		"isolate": !$props.collapsed
	}, "border-t border-t-gray-300! dark:border-t-white/10!"]) }, [createBaseVNode("div", {
		tabindex: $props.collapsed ? -1 : void 0,
		inert: $props.collapsed
	}, [createBaseVNode("div", _hoisted_8$2, [createVNode($setup["PageRule"], {
		rule: $props.rule,
		"suggestable-fields": $props.suggestableFields,
		"page-destination-options": $props.pageDestinationOptions,
		fieldtypes: $props.fieldtypes,
		"onUpdate:rule": _cache[1] || (_cache[1] = ($event) => $setup.emit("update:rule", $event)),
		onRemove: _cache[2] || (_cache[2] = ($event) => $setup.emit("removed"))
	}, null, 8, [
		"rule",
		"suggestable-fields",
		"page-destination-options",
		"fieldtypes"
	])])], 8, _hoisted_7$2)], 2), [[vShow, !$props.collapsed]])], 8, _hoisted_1$9)]);
}
var PageLogicRule_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$5, [["render", _sfc_render$10], ["__file", "PageLogicRule.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/LogicEmptyState.vue
var _sfc_main$4 = {
	__name: "LogicEmptyState",
	props: {
		heading: {
			type: String,
			required: true
		},
		description: {
			type: String,
			default: null
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$8 = { class: "flex items-start gap-2.5" };
var _hoisted_2$8 = { class: "flex flex-col items-start gap-3" };
var _hoisted_3$7 = { class: "text-sm font-medium text-gray-900 dark:text-gray-100" };
var _hoisted_4$5 = {
	key: 0,
	class: "mt-1 text-sm text-gray-500 dark:text-gray-400"
};
var _hoisted_5$2 = { key: 0 };
function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$8, [createBaseVNode("div", _hoisted_2$8, [createBaseVNode("div", null, [createBaseVNode("p", _hoisted_3$7, toDisplayString($props.heading), 1), $props.description ? (openBlock(), createElementBlock("p", _hoisted_4$5, toDisplayString($props.description), 1)) : createCommentVNode("", true)]), _ctx.$slots.default ? (openBlock(), createElementBlock("div", _hoisted_5$2, [renderSlot(_ctx.$slots, "default")])) : createCommentVNode("", true)])]);
}
var LogicEmptyState_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$4, [["render", _sfc_render$9], ["__file", "LogicEmptyState.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/PageLogic.vue
var _sfc_main$3 = {
	__name: "PageLogic",
	props: {
		pages: {
			type: Array,
			required: true
		},
		suggestableFields: {
			type: Array,
			required: true
		},
		fieldtypes: Array
	},
	emits: ["update:pages"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const collapsed = ref([]);
		const rules = computed(() => {
			const rules = [];
			props.pages.forEach((page, pageIndex) => {
				if (page.rules && page.rules.length > 0) page.rules.forEach((rule) => {
					rules.push({
						...rule,
						_pageId: page._id,
						_pageDisplay: __(page.display) || __("Page :number", { number: pageIndex + 1 })
					});
				});
			});
			return rules;
		});
		const expand = (id) => collapsed.value = collapsed.value.filter((ruleId) => ruleId !== id);
		const expandAll = () => collapsed.value = [];
		const collapseAll = () => collapsed.value = rules.value.map((rule) => rule._id);
		const collapse = (id) => {
			if (!collapsed.value.includes(id)) collapsed.value.push(id);
		};
		const allRulesAreCollapsed = computed(() => rules.value.every((rule) => collapsed.value.includes(rule._id)));
		const rulesView = computed(() => allRulesAreCollapsed.value ? "collapsed" : "expanded");
		const getSuggestableFieldsForPage = (pageId) => {
			const pageIndex = props.pages.findIndex((page) => page._id === pageId);
			return props.suggestableFields.filter((field) => field.pageIndex <= pageIndex);
		};
		const getPageDestinationOptions = (pageId) => {
			const pageIndex = props.pages.findIndex((page) => page._id === pageId);
			return props.pages.slice(pageIndex + 1).map((page, index) => ({
				label: __(page.display) || __("Page :number", { number: pageIndex + index + 2 }),
				value: page._id,
				icon: "page"
			}));
		};
		const availablePages = computed(() => {
			return props.pages.slice(0, -1).map((page, index) => ({
				handle: page._id,
				display: __(page.display) || __("Page :number", { number: index + 1 }),
				icon: "page"
			}));
		});
		const addRule = (pageId) => {
			const newRule = {
				_id: nanoid(),
				conditions: [{
					_id: nanoid(),
					field: null,
					operator: "equals",
					value: null
				}],
				destination: null
			};
			emit("update:pages", props.pages.map((page) => page._id === pageId ? {
				...page,
				rules: [...page.rules || [], newRule]
			} : page));
			nextTick(() => expand(newRule._id));
		};
		const removeRule = (ruleId, pageId) => {
			emit("update:pages", props.pages.map((page) => {
				if (page._id !== pageId) return page;
				return {
					...page,
					rules: page.rules.filter((rule) => rule._id !== ruleId)
				};
			}));
			collapsed.value = collapsed.value.filter((id) => id !== ruleId);
		};
		const updateRule = (ruleId, pageId, updatedRule) => {
			emit("update:pages", props.pages.map((page) => {
				if (page._id !== pageId) return page;
				return {
					...page,
					rules: page.rules.map((rule) => rule._id === ruleId ? {
						...updatedRule,
						_id: ruleId
					} : rule)
				};
			}));
		};
		watch(rules, (rules, oldRules) => {
			const oldIds = new Set((oldRules || []).map((rule) => rule._id));
			rules.forEach((rule) => {
				if (!oldIds.has(rule._id)) collapsed.value.push(rule._id);
			});
		}, { immediate: true });
		const __returned__ = {
			emit,
			props,
			collapsed,
			rules,
			expand,
			expandAll,
			collapseAll,
			collapse,
			allRulesAreCollapsed,
			rulesView,
			getSuggestableFieldsForPage,
			getPageDestinationOptions,
			availablePages,
			addRule,
			removeRule,
			updateRule,
			get Button() {
				return Button_default;
			},
			get Card() {
				return Card_default;
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
				return Header_default;
			},
			AddLogicRuleButton: AddLogicRuleButton_default,
			PageLogicRule: PageLogicRule_default,
			LogicEmptyState: LogicEmptyState_default,
			computed,
			nextTick,
			ref,
			watch,
			get uniqid() {
				return nanoid;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$7 = { class: "flex items-center justify-between gap-3" };
var _hoisted_2$7 = { class: "flex items-center gap-2.5" };
var _hoisted_3$6 = {
	key: 0,
	class: "flex items-center gap-2"
};
var _hoisted_4$4 = {
	class: "relative space-y-6 mb-0",
	"data-logic-list": ""
};
function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Panel"], null, {
		default: withCtx(() => [createVNode($setup["PanelHeader"], null, {
			default: withCtx(() => [createBaseVNode("div", _hoisted_1$7, [createBaseVNode("div", _hoisted_2$7, [createVNode($setup["Icon"], {
				name: "page",
				class: "size-4 text-gray-500 dark:text-gray-300"
			}), createVNode($setup["Heading"], { text: _ctx.__("Page Logic") }, null, 8, ["text"])]), $setup.rules.length > 0 ? (openBlock(), createElementBlock("div", _hoisted_3$6, [createVNode($setup["Button"], {
				size: "xs",
				variant: "ghost",
				icon: $setup.rulesView === "collapsed" ? "expand" : "collapse",
				"aria-label": $setup.rulesView === "collapsed" ? _ctx.__("Expand all rules") : _ctx.__("Collapse all rules"),
				onClick: _cache[0] || (_cache[0] = ($event) => $setup.rulesView === "collapsed" ? $setup.expandAll() : $setup.collapseAll())
			}, null, 8, ["icon", "aria-label"])])) : createCommentVNode("", true)])]),
			_: 1
		}), createVNode($setup["Card"], null, {
			default: withCtx(() => [$setup.rules.length === 0 ? (openBlock(), createBlock($setup["LogicEmptyState"], {
				key: 0,
				heading: _ctx.__("No page logic yet"),
				description: _ctx.__("Send users to different pages based on their answers.")
			}, {
				default: withCtx(() => [$setup.availablePages.length > 0 ? (openBlock(), createBlock($setup["AddLogicRuleButton"], {
					key: 0,
					flush: "",
					items: $setup.availablePages,
					"show-connector": false,
					label: _ctx.__("Add Rule"),
					"search-placeholder": _ctx.__("Search Pages"),
					onAdded: $setup.addRule
				}, null, 8, [
					"items",
					"label",
					"search-placeholder"
				])) : createCommentVNode("", true)]),
				_: 1
			}, 8, ["heading", "description"])) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [createBaseVNode("div", _hoisted_4$4, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.rules, (rule) => {
				return openBlock(), createBlock($setup["PageLogicRule"], {
					id: rule._id,
					key: rule._id,
					rule,
					"page-id": rule._pageId,
					"page-display": rule._pageDisplay,
					collapsed: $setup.collapsed.includes(rule._id),
					"suggestable-fields": $setup.getSuggestableFieldsForPage(rule._pageId),
					"page-destination-options": $setup.getPageDestinationOptions(rule._pageId),
					fieldtypes: $props.fieldtypes,
					onCollapsed: ($event) => $setup.collapse(rule._id),
					onExpanded: ($event) => $setup.expand(rule._id),
					onRemoved: ($event) => $setup.removeRule(rule._id, rule._pageId),
					"onUpdate:rule": ($event) => $setup.updateRule(rule._id, rule._pageId, $event)
				}, null, 8, [
					"id",
					"rule",
					"page-id",
					"page-display",
					"collapsed",
					"suggestable-fields",
					"page-destination-options",
					"fieldtypes",
					"onCollapsed",
					"onExpanded",
					"onRemoved",
					"onUpdate:rule"
				]);
			}), 128))]), $setup.availablePages.length > 0 ? (openBlock(), createBlock($setup["AddLogicRuleButton"], {
				key: 0,
				items: $setup.availablePages,
				"show-connector": $setup.rules.length > 0,
				label: _ctx.__("Add Rule"),
				"search-placeholder": _ctx.__("Search Pages"),
				onAdded: $setup.addRule
			}, null, 8, [
				"items",
				"show-connector",
				"label",
				"search-placeholder"
			])) : createCommentVNode("", true)], 64))]),
			_: 1
		})]),
		_: 1
	});
}
var PageLogic_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$3, [
	["render", _sfc_render$8],
	["__scopeId", "data-v-db6511e9"],
	["__file", "PageLogic.vue"]
]);
//#endregion
//#region resources/js/components/forms/logic/FieldConditions.vue
var _sfc_main$2 = {
	__name: "FieldConditions",
	props: {
		conditions: {
			type: Object,
			default: () => ({})
		},
		suggestableFields: {
			type: Array,
			default: () => []
		},
		fieldtypes: Array
	},
	emits: ["update:conditions"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const getFieldtypeCategory = (fieldtypeHandle) => {
			return categories[(props.fieldtypes?.find((field) => field.handle === fieldtypeHandle))?.categories?.[0] || "other"] ?? categories.other;
		};
		const fieldIconClasses = (fieldtypeHandle) => `size-4 shrink-0 ${categoryColorClasses[getFieldtypeCategory(fieldtypeHandle)?.color]?.icon}`;
		const findSuggestableField = (handle) => props.suggestableFields.find((f) => f.handle === handle);
		const onConditionsUpdated = (conditions) => emit("update:conditions", conditions);
		const onAlwaysSaveUpdated = (alwaysSave) => emit("update:conditions", {
			...props.conditions,
			always_save: alwaysSave
		});
		const __returned__ = {
			emit,
			props,
			getFieldtypeCategory,
			fieldIconClasses,
			findSuggestableField,
			onConditionsUpdated,
			onAlwaysSaveUpdated,
			get Icon() {
				return Icon_default;
			},
			FieldNumber: FieldNumber_default,
			FieldConditionsBuilder: Builder_default,
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
var _hoisted_1$6 = { class: "inline-flex items-center gap-2" };
var _hoisted_2$6 = { class: "truncate" };
var _hoisted_3$5 = { class: "inline-flex items-center gap-2 truncate" };
var _hoisted_4$3 = { class: "truncate" };
function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["FieldConditionsBuilder"], {
		config: $props.conditions,
		"suggestable-fields": $props.suggestableFields,
		fieldtypes: $props.fieldtypes,
		"allow-custom-conditions": false,
		"show-always-hide-option": true,
		"show-always-save": false,
		size: "sm",
		onUpdated: $setup.onConditionsUpdated,
		onUpdatedAlwaysSave: $setup.onAlwaysSaveUpdated
	}, {
		"field-option": withCtx(({ value, label }) => [createBaseVNode("span", _hoisted_1$6, [
			createVNode($setup["FieldNumber"], { "field-key": value }, null, 8, ["field-key"]),
			$setup.findSuggestableField(value)?.icon ? (openBlock(), createBlock($setup["Icon"], {
				key: 0,
				name: $setup.findSuggestableField(value).icon,
				class: normalizeClass($setup.fieldIconClasses($setup.findSuggestableField(value)?.config?.type))
			}, null, 8, ["name", "class"])) : createCommentVNode("", true),
			createBaseVNode("span", _hoisted_2$6, toDisplayString(_ctx.__(label)), 1)
		])]),
		"field-selected": withCtx(({ option, field: selectedField }) => [createBaseVNode("span", _hoisted_3$5, [
			createVNode($setup["FieldNumber"], { "field-key": option.value }, null, 8, ["field-key"]),
			selectedField?.icon ? (openBlock(), createBlock($setup["Icon"], {
				key: 0,
				name: selectedField.icon,
				class: normalizeClass($setup.fieldIconClasses(selectedField.config.type))
			}, null, 8, ["name", "class"])) : createCommentVNode("", true),
			createBaseVNode("span", _hoisted_4$3, toDisplayString(_ctx.__($setup.findSuggestableField(option.value)?.config?.display ?? option.value)), 1)
		])]),
		_: 1
	}, 8, [
		"config",
		"suggestable-fields",
		"fieldtypes"
	]);
}
var FieldConditions_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$7], ["__file", "FieldConditions.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/FieldLogicRule.vue
var _sfc_main$1 = {
	__name: "FieldLogicRule",
	props: {
		config: Object,
		id: String,
		collapsed: Boolean,
		readOnly: Boolean,
		enabled: Boolean,
		hasError: Boolean,
		conditions: {
			type: Object,
			default: () => ({})
		},
		suggestableFields: {
			type: Array,
			default: () => []
		},
		fieldtypes: Array
	},
	emits: [
		"collapsed",
		"expanded",
		"removed",
		"update:conditions"
	],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const converter = new Converter_default();
		const emit = __emit;
		const props = __props;
		const getFieldDisplay = (handle) => __(props.suggestableFields.find((field) => field.handle === handle)?.config?.display) || handle;
		const hasConditions = computed(() => props.conditions.if || props.conditions.unless || props.conditions.if_any || props.conditions.unless_any);
		const isHideCondition = computed(() => props.conditions.unless || props.conditions.unless_any);
		const previewParts = computed(() => {
			const conditionsObj = props.conditions.if || props.conditions.unless || props.conditions.if_any || props.conditions.unless_any;
			if (!conditionsObj || typeof conditionsObj !== "object") return null;
			const entries = Object.entries(conditionsObj);
			if (entries.length === 0) return null;
			const [fieldHandle, rawValue] = entries[0];
			const parts = [];
			parts.push({
				type: "field",
				text: getFieldDisplay(fieldHandle)
			});
			const { operator, value } = converter.splitRhs(fieldHandle, rawValue);
			parts.push({
				type: "operator",
				text: operatorLabel(operator)
			});
			if (value && value !== "empty") parts.push({
				type: "value",
				text: String(value)
			});
			if (entries.length > 1) parts.push({
				type: "more",
				text: `+${entries.length - 1}`
			});
			return parts;
		});
		const collapsedSummary = computed(() => {
			if (!hasConditions.value) return props.conditions.hidden ? __("Always hide") : __("Always show");
			if (!previewParts.value) return __("Configure conditions");
			return null;
		});
		const prefixLabel = computed(() => isHideCondition.value ? __("Hide when") : __("Show when"));
		const prefixIcon = computed(() => isHideCondition.value ? "eye-closed" : "eye");
		const toggleCollapsedState = () => props.collapsed ? emit("expanded") : emit("collapsed");
		const onConditionsUpdated = (conditions) => emit("update:conditions", conditions);
		const __returned__ = {
			converter,
			emit,
			props,
			getFieldDisplay,
			hasConditions,
			isHideCondition,
			previewParts,
			collapsedSummary,
			prefixLabel,
			prefixIcon,
			toggleCollapsedState,
			onConditionsUpdated,
			computed,
			get Badge() {
				return Badge_default;
			},
			get Button() {
				return Button_default;
			},
			get Dropdown() {
				return Dropdown_default;
			},
			get DropdownItem() {
				return Item_default;
			},
			get DropdownMenu() {
				return Menu_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Subheading() {
				return Subheading_default;
			},
			FieldNumber: FieldNumber_default,
			FieldConditions: FieldConditions_default,
			get Converter() {
				return Converter_default;
			},
			get operatorLabel() {
				return operatorLabel;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$5 = [
	"data-collapsed",
	"data-error",
	"data-readonly",
	"data-type"
];
var _hoisted_2$5 = { class: "st-text-trim-cap" };
var _hoisted_3$4 = {
	key: 0,
	class: "lowercase"
};
var _hoisted_4$2 = {
	key: 0,
	class: "text-gray-700 dark:text-gray-300"
};
var _hoisted_5$1 = {
	key: 2,
	class: "font-mono text-[0.75rem] text-gray-900 dark:text-gray-100"
};
var _hoisted_6$1 = {
	key: 3,
	class: "text-gray-500 dark:text-gray-400 text-2xs"
};
var _hoisted_7$1 = ["tabindex", "inert"];
var _hoisted_8$1 = { class: "p-4" };
function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", null, [createBaseVNode("div", {
		layout: "",
		"data-replicator-rule": "",
		class: normalizeClass(["@container relative w-full rounded-lg border border-gray-300 text-base dark:border-white/10 bg-white dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm", { "border-red-500": $props.hasError }]),
		"data-collapsed": $props.collapsed ?? void 0,
		"data-error": $props.hasError ?? void 0,
		"data-readonly": $props.readOnly ?? void 0,
		"data-type": $props.config?.handle
	}, [createBaseVNode("header", { class: normalizeClass(["group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 dark:bg-gray-925 border-gray-300 dark:shadow-md", {
		"bg-white dark:bg-gray-900": $props.collapsed,
		"bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none": !$props.collapsed
	}]) }, [createBaseVNode("button", {
		type: "button",
		class: "show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 ps-0 min-w-0 focus:outline-none cursor-pointer",
		onClick: $setup.toggleCollapsedState
	}, [
		createVNode($setup["Badge"], {
			size: "lg",
			pill: "",
			color: "white",
			class: "px-3 text-gray-950 gap-1"
		}, {
			default: withCtx(() => [
				createVNode($setup["FieldNumber"], {
					"field-key": $props.config?.handle,
					class: "me-0.5"
				}, null, 8, ["field-key"]),
				$props.config?.icon ? (openBlock(), createBlock($setup["Icon"], {
					key: 0,
					name: $props.config.icon,
					class: normalizeClass(["size-3.5 me-1 rounded-sm opacity-100!", $props.config.iconClass]),
					"aria-hidden": "true"
				}, null, 8, ["name", "class"])) : createCommentVNode("", true),
				createTextVNode(" " + toDisplayString(_ctx.__($props.config?.display) || $props.config?.handle), 1)
			]),
			_: 1
		}),
		$props.collapsed ? (openBlock(), createBlock($setup["Badge"], {
			key: 0,
			pill: "",
			color: "white",
			class: "px-1.5 py-1 font-medium st-text-trim-ex-alphabetic text-gray-800 dark:text-gray-200 gap-1.5"
		}, {
			default: withCtx(() => [createVNode($setup["Icon"], {
				name: $setup.prefixIcon,
				class: "size-3.5!",
				"aria-hidden": "true"
			}, null, 8, ["name"]), createBaseVNode("span", _hoisted_2$5, toDisplayString($setup.prefixLabel), 1)]),
			_: 1
		})) : createCommentVNode("", true),
		withDirectives(createVNode($setup["Subheading"], { class: "overflow-hidden text-ellipsis whitespace-nowrap text-xs flex items-center gap-1" }, {
			default: withCtx(() => [$setup.collapsedSummary ? (openBlock(), createElementBlock("span", _hoisted_3$4, toDisplayString($setup.collapsedSummary), 1)) : $setup.previewParts ? (openBlock(true), createElementBlock(Fragment, { key: 1 }, renderList($setup.previewParts, (part, index) => {
				return openBlock(), createElementBlock(Fragment, { key: index }, [part.type === "field" ? (openBlock(), createElementBlock("span", _hoisted_4$2, toDisplayString(part.text), 1)) : part.type === "operator" ? (openBlock(), createBlock($setup["Badge"], {
					key: 1,
					class: "inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
				}, {
					default: withCtx(() => [createTextVNode(toDisplayString(part.text), 1)]),
					_: 2
				}, 1024)) : part.type === "value" ? (openBlock(), createElementBlock("span", _hoisted_5$1, toDisplayString(part.text), 1)) : part.type === "more" ? (openBlock(), createElementBlock("span", _hoisted_6$1, toDisplayString(part.text), 1)) : createCommentVNode("", true)], 64);
			}), 128)) : createCommentVNode("", true)]),
			_: 1
		}, 512), [[vShow, $props.collapsed]])
	]), createVNode($setup["Dropdown"], { align: "end" }, {
		trigger: withCtx(() => [createVNode($setup["Button"], {
			icon: "dots",
			variant: "ghost",
			size: "xs",
			class: "me-2",
			"aria-label": _ctx.__("Open row actions")
		}, null, 8, ["aria-label"])]),
		default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
			default: withCtx(() => [createVNode($setup["DropdownItem"], {
				text: _ctx.__("Delete rule"),
				icon: "trash",
				variant: "destructive",
				onClick: _cache[0] || (_cache[0] = ($event) => $setup.emit("removed"))
			}, null, 8, ["text"])]),
			_: 1
		})]),
		_: 1
	})], 2), withDirectives(createBaseVNode("div", { class: normalizeClass([{
		"contain-paint": $props.collapsed,
		"isolate": !$props.collapsed
	}, "border-t border-t-gray-300! dark:border-t-white/10!"]) }, [createBaseVNode("div", {
		tabindex: $props.collapsed ? -1 : void 0,
		inert: $props.collapsed
	}, [createBaseVNode("div", _hoisted_8$1, [createVNode($setup["FieldConditions"], {
		conditions: $props.conditions,
		"suggestable-fields": $props.suggestableFields,
		fieldtypes: $props.fieldtypes,
		"onUpdate:conditions": $setup.onConditionsUpdated
	}, null, 8, [
		"conditions",
		"suggestable-fields",
		"fieldtypes"
	])])], 8, _hoisted_7$1)], 2), [[vShow, !$props.collapsed]])], 10, _hoisted_1$5)]);
}
var FieldLogicRule_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$6], ["__file", "FieldLogicRule.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/FieldLogic.vue
var _sfc_main = {
	__name: "FieldLogic",
	props: {
		fields: {
			type: Array,
			required: true
		},
		suggestableFields: {
			type: Array,
			required: true
		},
		fieldtypes: Array
	},
	emits: ["update:fields"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const collapsed = ref([]);
		const fieldHasLogic = (field) => field.hidden || KEYS.some((key) => field[key]);
		const fieldsWithLogic = computed(() => props.fields.filter(fieldHasLogic));
		const fieldsWithoutLogic = computed(() => props.fields.filter((field) => !fieldHasLogic(field)));
		const expand = (id) => collapsed.value = collapsed.value.filter((setId) => setId !== id);
		const expandAll = () => collapsed.value = [];
		const collapseAll = () => collapsed.value = fieldsWithLogic.value.map((field) => field._id);
		const collapse = (id) => {
			if (!collapsed.value.includes(id)) collapsed.value.push(id);
		};
		const allFieldsAreCollapsed = computed(() => fieldsWithLogic.value.every((field) => collapsed.value.includes(field._id)));
		const rulesView = computed(() => allFieldsAreCollapsed.value ? "collapsed" : "expanded");
		const getIconClass = (category) => {
			return categoryColorClasses[categories[category]?.color || "gray"]?.icon || "text-gray-600 dark:text-gray-400";
		};
		const availableFields = computed(() => {
			return fieldsWithoutLogic.value.map((field) => ({
				handle: field._id,
				display: field.display,
				icon: field.icon || "generic-field",
				iconClass: getIconClass(field.category)
			}));
		});
		const getFieldConfig = (field) => ({
			handle: field._id,
			display: field.display,
			icon: field.icon || "generic-field",
			iconClass: getIconClass(field.category)
		});
		const getConditionsConfig = (field) => ({
			handle: field.handle,
			hidden: field.hidden,
			if: field.if,
			unless: field.unless,
			if_any: field.if_any,
			unless_any: field.unless_any,
			always_save: field.always_save
		});
		const getSuggestableFieldsForField = (field) => {
			return props.suggestableFields.filter((f) => f.pageIndex <= field.pageIndex && f.handle !== field.handle);
		};
		const addCondition = (fieldId) => {
			emit("update:fields", props.fields.map((field) => field._id === fieldId ? {
				...field,
				if: {}
			} : field));
			nextTick(() => expand(fieldId));
		};
		const removeCondition = (fieldId) => {
			emit("update:fields", props.fields.map((field) => field._id === fieldId ? {
				...field,
				if: null,
				unless: null,
				if_any: null,
				unless_any: null,
				always_save: false
			} : field));
			collapsed.value = collapsed.value.filter((id) => id !== fieldId);
		};
		const updateConditions = (fieldId, conditions) => {
			emit("update:fields", props.fields.map((field) => field._id === fieldId ? {
				...field,
				hidden: conditions.hidden ?? field.hidden,
				if: conditions.if || null,
				unless: conditions.unless || null,
				if_any: conditions.if_any || null,
				unless_any: conditions.unless_any || null,
				always_save: conditions.always_save ?? field.always_save
			} : field));
		};
		watch(fieldsWithLogic, (fields, oldFields) => {
			const oldIds = new Set((oldFields || []).map((f) => f._id));
			fields.forEach((field) => {
				if (!oldIds.has(field._id)) collapsed.value.push(field._id);
			});
		}, { immediate: true });
		const __returned__ = {
			emit,
			props,
			collapsed,
			fieldHasLogic,
			fieldsWithLogic,
			fieldsWithoutLogic,
			expand,
			expandAll,
			collapseAll,
			collapse,
			allFieldsAreCollapsed,
			rulesView,
			getIconClass,
			availableFields,
			getFieldConfig,
			getConditionsConfig,
			getSuggestableFieldsForField,
			addCondition,
			removeCondition,
			updateConditions,
			get Button() {
				return Button_default;
			},
			get Card() {
				return Card_default;
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
				return Header_default;
			},
			AddLogicRuleButton: AddLogicRuleButton_default,
			FieldLogicRule: FieldLogicRule_default,
			LogicEmptyState: LogicEmptyState_default,
			computed,
			nextTick,
			ref,
			watch,
			get categories() {
				return categories;
			},
			get categoryColorClasses() {
				return categoryColorClasses;
			},
			get KEYS() {
				return KEYS;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$4 = { class: "flex items-center justify-between gap-3" };
var _hoisted_2$4 = { class: "flex items-center gap-2.5" };
var _hoisted_3$3 = {
	key: 0,
	class: "flex items-center gap-2"
};
var _hoisted_4$1 = {
	class: "relative space-y-6 mb-0",
	"data-logic-list": ""
};
function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Panel"], null, {
		default: withCtx(() => [createVNode($setup["PanelHeader"], null, {
			default: withCtx(() => [createBaseVNode("div", _hoisted_1$4, [createBaseVNode("div", _hoisted_2$4, [createVNode($setup["Icon"], {
				name: "form-text-field",
				class: "size-4 text-gray-500 dark:text-gray-300"
			}), createVNode($setup["Heading"], { text: _ctx.__("Field Logic") }, null, 8, ["text"])]), $setup.fieldsWithLogic.length > 0 ? (openBlock(), createElementBlock("div", _hoisted_3$3, [createVNode($setup["Button"], {
				size: "xs",
				variant: "ghost",
				icon: $setup.rulesView === "collapsed" ? "expand" : "collapse",
				"aria-label": $setup.rulesView === "collapsed" ? _ctx.__("Expand all rules") : _ctx.__("Collapse all rules"),
				onClick: _cache[0] || (_cache[0] = ($event) => $setup.rulesView === "collapsed" ? $setup.expandAll() : $setup.collapseAll())
			}, null, 8, ["icon", "aria-label"])])) : createCommentVNode("", true)])]),
			_: 1
		}), createVNode($setup["Card"], null, {
			default: withCtx(() => [$setup.fieldsWithLogic.length === 0 ? (openBlock(), createBlock($setup["LogicEmptyState"], {
				key: 0,
				heading: _ctx.__("No field logic yet"),
				description: _ctx.__("Show or hide fields based on previous answers.")
			}, {
				default: withCtx(() => [$setup.availableFields.length > 0 ? (openBlock(), createBlock($setup["AddLogicRuleButton"], {
					key: 0,
					flush: "",
					items: $setup.availableFields,
					"show-connector": false,
					label: _ctx.__("Add Rule"),
					"search-placeholder": _ctx.__("Search Fields"),
					onAdded: $setup.addCondition
				}, null, 8, [
					"items",
					"label",
					"search-placeholder"
				])) : createCommentVNode("", true)]),
				_: 1
			}, 8, ["heading", "description"])) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [createBaseVNode("div", _hoisted_4$1, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.fieldsWithLogic, (field) => {
				return openBlock(), createBlock($setup["FieldLogicRule"], {
					id: field._id,
					key: field._id,
					config: $setup.getFieldConfig(field),
					collapsed: $setup.collapsed.includes(field._id),
					"read-only": false,
					enabled: true,
					"has-error": false,
					conditions: $setup.getConditionsConfig(field),
					"suggestable-fields": $setup.getSuggestableFieldsForField(field),
					fieldtypes: $props.fieldtypes,
					onCollapsed: ($event) => $setup.collapse(field._id),
					onExpanded: ($event) => $setup.expand(field._id),
					onRemoved: ($event) => $setup.removeCondition(field._id),
					"onUpdate:conditions": ($event) => $setup.updateConditions(field._id, $event)
				}, null, 8, [
					"id",
					"config",
					"collapsed",
					"conditions",
					"suggestable-fields",
					"fieldtypes",
					"onCollapsed",
					"onExpanded",
					"onRemoved",
					"onUpdate:conditions"
				]);
			}), 128))]), $setup.availableFields.length > 0 ? (openBlock(), createBlock($setup["AddLogicRuleButton"], {
				key: 0,
				items: $setup.availableFields,
				"show-connector": $setup.fieldsWithLogic.length > 0,
				label: _ctx.__("Add Rule"),
				"search-placeholder": _ctx.__("Search Fields"),
				onAdded: $setup.addCondition
			}, null, 8, [
				"items",
				"show-connector",
				"label",
				"search-placeholder"
			])) : createCommentVNode("", true)], 64))]),
			_: 1
		})]),
		_: 1
	});
}
var FieldLogic_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [
	["render", _sfc_render$5],
	["__scopeId", "data-v-a18f175c"],
	["__file", "FieldLogic.vue"]
]);
//#endregion
//#region resources/js/components/forms/logic/LogicList.vue?vue&type=script&setup=true&lang.ts
var LogicList_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "LogicList",
	props: {
		pages: {
			type: Array,
			required: true
		},
		suggestableFields: {
			type: Array,
			required: true
		},
		fieldtypes: Array
	},
	emits: ["update:pages"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const fieldCategory = (field) => props.fieldtypes.find((fieldtype) => fieldtype.handle === field.fieldtype)?.categories?.[0] ?? "other";
		const __returned__ = {
			emit,
			props,
			fieldCategory,
			fields: computed({
				get: () => props.pages.flatMap((page, pageIndex) => page.sections.flatMap((section) => section.fields.filter((field) => field.type !== "import").map((field) => ({
					_id: field.handle,
					handle: field.handle,
					display: field.config?.display ?? field.handle,
					icon: field.icon,
					category: fieldCategory(field),
					fieldtype: field.fieldtype,
					pageIndex,
					hidden: field.config?.hidden ?? false,
					if: field.config?.if ?? null,
					unless: field.config?.unless ?? null,
					if_any: field.config?.if_any ?? null,
					unless_any: field.config?.unless_any ?? null,
					always_save: field.config?.always_save ?? false,
					options: field.config?.options
				})))),
				set: (fields) => {
					const nodes = props.pages.flatMap((page) => page.sections).flatMap((section) => section.fields);
					fields.forEach((field) => {
						const node = nodes.find((node) => node.handle === field.handle);
						if (node) writeFieldConditions(node, field);
					});
				}
			}),
			PageLogic: PageLogic_default,
			FieldLogic: FieldLogic_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
//#endregion
//#region resources/js/components/forms/logic/LogicList.vue
function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [$props.pages.length > 1 ? (openBlock(), createBlock($setup["PageLogic"], {
		key: 0,
		class: "mb-6",
		pages: $props.pages,
		"suggestable-fields": $props.suggestableFields,
		fieldtypes: $props.fieldtypes,
		"onUpdate:pages": _cache[0] || (_cache[0] = ($event) => $setup.emit("update:pages", $event))
	}, null, 8, [
		"pages",
		"suggestable-fields",
		"fieldtypes"
	])) : createCommentVNode("", true), createVNode($setup["FieldLogic"], {
		fields: $setup.fields,
		"onUpdate:fields": _cache[1] || (_cache[1] = ($event) => $setup.fields = $event),
		"suggestable-fields": $props.suggestableFields,
		fieldtypes: $props.fieldtypes
	}, null, 8, [
		"fields",
		"suggestable-fields",
		"fieldtypes"
	])], 64);
}
var LogicList_default = /*#__PURE__*/ _plugin_vue_export_helper_default(LogicList_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$4], ["__file", "LogicList.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/LogicTreeField.vue?vue&type=script&setup=true&lang.ts
var mutedIconClass = "text-gray-600 dark:text-gray-400";
var LogicTreeField_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "LogicTreeField",
	props: { field: {} },
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const fieldtypes = inject("fieldtypes", []);
		const category = computed(() => fieldtypes.find((fieldtype) => fieldtype.handle === props.field.fieldtype)?.categories?.[0] ?? "other");
		const __returned__ = {
			props,
			fieldtypes,
			mutedIconClass,
			category,
			iconClass: computed(() => {
				if (props.field.type === "reference") return mutedIconClass;
				return categoryColorClasses[categories[category.value]?.color || "gray"]?.icon || mutedIconClass;
			}),
			FieldNumber: FieldNumber_default,
			get Icon() {
				return Icon_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
//#endregion
//#region resources/js/components/forms/logic/LogicTreeField.vue
var _hoisted_1$3 = { class: "linked-list__field-name min-w-0 flex-1" };
var _hoisted_2$3 = {
	key: 0,
	class: "inline-flex size-4 shrink-0"
};
var _hoisted_3$2 = {
	key: 1,
	class: "inline-flex size-4 shrink-0"
};
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Icon"], {
			name: $props.field.icon || "generic-field",
			class: normalizeClass(["size-4 shrink-0", $setup.iconClass]),
			"aria-hidden": "true"
		}, null, 8, ["name", "class"]),
		createBaseVNode("span", _hoisted_1$3, [createVNode($setup["FieldNumber"], { "field-key": $props.field.handle }, null, 8, ["field-key"]), createTextVNode(" " + toDisplayString(_ctx.__($props.field.config?.display) || $props.field.handle), 1)]),
		$props.field.config?.hidden ? withDirectives((openBlock(), createElementBlock("span", _hoisted_2$3, [createVNode($setup["Icon"], {
			name: "eye-closed",
			class: "size-4 shrink-0 text-indigo-500 dark:text-indigo-400",
			"aria-hidden": "true"
		})])), [[_directive_tooltip, _ctx.__("Hidden")]]) : createCommentVNode("", true),
		$props.field.type === "reference" ? withDirectives((openBlock(), createElementBlock("span", _hoisted_3$2, [createVNode($setup["Icon"], {
			name: "link",
			class: "size-4 shrink-0 text-indigo-500 dark:text-indigo-400",
			"aria-hidden": "true"
		})])), [[_directive_tooltip, _ctx.__("Linked Field: :reference", { reference: $props.field.field_reference })]]) : createCommentVNode("", true)
	], 64);
}
var LogicTreeField_default = /*#__PURE__*/ _plugin_vue_export_helper_default(LogicTreeField_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$3], ["__file", "LogicTreeField.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/LogicTreeFieldset.vue?vue&type=script&setup=true&lang.ts
var LogicTreeFieldset_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "LogicTreeFieldset",
	props: { field: {} },
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const __returned__ = {
			props,
			fieldsetTitle: computed(() => {
				return Object.values(usePage().props.fieldsets ?? {}).find((fieldset) => fieldset.handle === props.field.fieldset)?.title;
			}),
			rows: computed(() => Object.entries(props.field.previews ?? {}).map(([handle, preview]) => ({
				handle,
				display: preview.config?.display ?? handle,
				icon: preview.icon ?? "generic-field"
			}))),
			FieldNumber: FieldNumber_default,
			get Icon() {
				return Icon_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
//#endregion
//#region resources/js/components/forms/logic/LogicTreeFieldset.vue
var _hoisted_1$2 = { class: "linked-list__field-name min-w-0 flex-1" };
var _hoisted_2$2 = { class: "inline-flex size-4 shrink-0" };
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(true), createElementBlock(Fragment, null, renderList($setup.rows, (row) => {
		return openBlock(), createElementBlock("div", {
			key: row.handle,
			class: "linked-list__fieldset-row"
		}, [
			createVNode($setup["Icon"], {
				name: row.icon,
				class: "size-4 shrink-0 text-gray-600 dark:text-gray-400",
				"aria-hidden": "true"
			}, null, 8, ["name"]),
			createBaseVNode("span", _hoisted_1$2, [createVNode($setup["FieldNumber"], { "field-key": `${$props.field._id}:${row.handle}` }, null, 8, ["field-key"]), createTextVNode(" " + toDisplayString(_ctx.__(row.display)), 1)]),
			withDirectives((openBlock(), createElementBlock("span", _hoisted_2$2, [createVNode($setup["Icon"], {
				name: "link",
				class: "size-4 shrink-0 text-indigo-500 dark:text-indigo-400",
				"aria-hidden": "true"
			})])), [[_directive_tooltip, $setup.fieldsetTitle ? _ctx.__("Linked Fieldset: :title", { title: _ctx.__($setup.fieldsetTitle) }) : _ctx.__("Linked Fieldset")]])
		]);
	}), 128);
}
var LogicTreeFieldset_default = /*#__PURE__*/ _plugin_vue_export_helper_default(LogicTreeFieldset_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$2], ["__file", "LogicTreeFieldset.vue"]]);
//#endregion
//#region resources/js/components/forms/logic/LogicTree.vue?vue&type=script&setup=true&lang.ts
var TreeDensity = /* @__PURE__ */ function(TreeDensity) {
	TreeDensity["Compressed"] = "compressed";
	TreeDensity["Expanded"] = "expanded";
	return TreeDensity;
}({});
var SelectionType = /* @__PURE__ */ function(SelectionType) {
	SelectionType["Field"] = "field";
	SelectionType["Page"] = "page";
	return SelectionType;
}({});
var LogicTree_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "LogicTree",
	props: {
		pages: {
			type: Array,
			required: true
		},
		density: {
			type: String,
			default: "compressed"
		},
		selected: {
			type: Object,
			default: null
		}
	},
	emits: ["update:pages", "select"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const tree = useTemplateRef("tree");
		const isInspectorOpen = ref(false);
		const isRightPanelCollapsed = ref(true);
		const adjacentRightCells = ref({});
		let resizeObserver = null;
		const pageAnchor = (pageIndex) => `--page-${pageIndex + 1}`;
		const fieldConnections = computed(() => {
			const connections = {};
			props.pages.forEach((page, pageIndex) => {
				(page.rules ?? []).forEach((rule) => {
					if (!rule.destination) return;
					const destinationPageIndex = props.pages.findIndex((page) => page._id === rule.destination);
					if (destinationPageIndex <= pageIndex) return;
					const condition = (rule.conditions ?? []).find((condition) => condition.field && condition.value !== null && condition.value !== "");
					if (!condition?.field) return;
					connections[condition.field] = {
						endConnection: pageAnchor(destinationPageIndex),
						leap: destinationPageIndex - pageIndex > 1,
						destinationPageIndex
					};
				});
			});
			return connections;
		});
		const measureAdjacentRightCells = () => {
			if (!tree.value) return;
			const columns = [...tree.value.querySelectorAll(".linked-list__column")];
			const adjacent = {};
			tree.value.querySelectorAll(".linked-list__page-leap").forEach((cell) => {
				const index = columns.indexOf(cell.closest(".linked-list__column"));
				const rect = cell.getBoundingClientRect();
				adjacent[cell.dataset.fieldId] = columns.slice(index + 1).flatMap((column) => [...column.querySelectorAll("[data-field-item]")]).some((other) => {
					const next = other.getBoundingClientRect();
					return rect.top < next.bottom && rect.bottom > next.top;
				});
			});
			adjacentRightCells.value = adjacent;
		};
		const pageTitle = (page, pageIndex) => __(page.display) || __("Page :number", { number: pageIndex + 1 });
		const fieldConnection = (field) => field.type === "import" ? null : fieldConnections.value[field.handle] ?? null;
		const isConnectorDestination = (pageIndex) => connectorDestinationPageIndices.value.has(pageIndex);
		const connectorDestinationPageIndices = computed(() => {
			return new Set(Object.values(fieldConnections.value).map((connection) => connection.destinationPageIndex));
		});
		const hasPageRules = (page) => (page.rules ?? []).some((rule) => {
			if (!rule.destination) return false;
			return (rule.conditions ?? []).some((condition) => condition.field && condition.value !== null && condition.value !== "");
		});
		const hasPageIndicators = (page, pageIndex) => isConnectorDestination(pageIndex) || hasPageRules(page);
		const selectField = (field) => field.type === "import" || emit("select", {
			type: "field",
			id: field._id
		});
		const selectPage = (page) => emit("select", {
			type: "page",
			id: page._id
		});
		const isFieldSelected = (field) => props.selected?.type === "field" && props.selected.id === field._id;
		const isPageSelected = (page) => props.selected?.type === "page" && props.selected.id === page._id;
		const selectedFieldEndConnectionPageIndex = computed(() => {
			if (props.selected?.type !== "field") return null;
			for (const page of props.pages) for (const section of page.sections) {
				const field = section.fields.find((f) => f._id === props.selected.id);
				if (field) return fieldConnection(field)?.destinationPageIndex ?? null;
			}
			return null;
		});
		const isEndConnectionColumn = (pageIndex) => selectedFieldEndConnectionPageIndex.value === pageIndex;
		const allSections = computed(() => props.pages.flatMap((page) => page.sections));
		const moveField = (fromSectionId, toSectionId, oldIndex, newIndex) => {
			const pages = props.pages.map((page) => ({
				...page,
				sections: page.sections.map((section) => ({
					...section,
					fields: [...section.fields]
				}))
			}));
			const sections = pages.flatMap((page) => page.sections);
			const fromSection = sections.find((section) => section._id === fromSectionId);
			const toSection = sections.find((section) => section._id === toSectionId);
			if (!fromSection || !toSection) return;
			const [field] = fromSection.fields.splice(oldIndex, 1);
			if (!field) return;
			toSection.fields.splice(newIndex, 0, field);
			emit("update:pages", pages);
		};
		const onMirrorCreated = ({ source, mirror }) => {
			mirror.style.width = `${source.offsetWidth}px`;
			mirror.classList.add("linked-list__mirror");
		};
		useSortable({
			container: tree,
			sections: allSections,
			onFieldMoved: moveField,
			onMirrorCreated
		});
		watch(() => props.selected, (selection) => {
			isInspectorOpen.value = false;
			if (selection) isRightPanelCollapsed.value = false;
		});
		watch(() => [props.pages, props.density], () => nextTick(measureAdjacentRightCells), { deep: true });
		const onEscape = (event) => {
			if (event.key !== "Escape") return;
			if (isInspectorOpen.value) {
				isInspectorOpen.value = false;
				return;
			}
			if (props.selected) {
				emit("select", null);
				return;
			}
			if (!isRightPanelCollapsed.value) isRightPanelCollapsed.value = true;
		};
		onMounted(() => {
			document.addEventListener("keydown", onEscape);
			resizeObserver = new ResizeObserver(measureAdjacentRightCells);
			resizeObserver.observe(tree.value);
		});
		onUnmounted(() => {
			document.removeEventListener("keydown", onEscape);
			resizeObserver?.disconnect();
			resizeObserver = null;
		});
		const __returned__ = {
			TreeDensity,
			SelectionType,
			emit,
			props,
			tree,
			isInspectorOpen,
			isRightPanelCollapsed,
			adjacentRightCells,
			get resizeObserver() {
				return resizeObserver;
			},
			set resizeObserver(v) {
				resizeObserver = v;
			},
			pageAnchor,
			fieldConnections,
			measureAdjacentRightCells,
			pageTitle,
			fieldConnection,
			isConnectorDestination,
			connectorDestinationPageIndices,
			hasPageRules,
			hasPageIndicators,
			selectField,
			selectPage,
			isFieldSelected,
			isPageSelected,
			selectedFieldEndConnectionPageIndex,
			isEndConnectionColumn,
			allSections,
			moveField,
			onMirrorCreated,
			onEscape,
			LogicTreeField: LogicTreeField_default,
			LogicTreeFieldset: LogicTreeFieldset_default,
			LayoutPanel: LayoutPanel_default,
			FieldInspector: FieldInspector_default,
			PageInspector: PageInspector_default,
			get Button() {
				return Button_default;
			},
			get Icon() {
				return Icon_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
//#endregion
//#region resources/js/components/forms/logic/LogicTree.vue
var _hoisted_1$1 = { class: "st-full-bleed-content st-separator-on-scroll col-span-full flex flex-col flex-1 min-h-0" };
var _hoisted_2$1 = { class: "flex-1 min-h-0 overflow-y-auto" };
var _hoisted_3$1 = {
	ref: "tree",
	class: "linked-list-container"
};
var _hoisted_4 = ["data-form-page"];
var _hoisted_5 = ["onClick"];
var _hoisted_6 = {
	key: 1,
	class: "inline-flex shrink-0"
};
var _hoisted_7 = { class: "line-clamp-1" };
var _hoisted_8 = { class: "linked-list__sections" };
var _hoisted_9 = ["aria-label"];
var _hoisted_10 = { class: "linked-list__section-marker-label line-clamp-2 text-center" };
var _hoisted_11 = ["data-sort-section"];
var _hoisted_12 = {
	key: 0,
	class: "linked-list__empty-section"
};
var _hoisted_13 = [
	"data-field-id",
	"data-field-item-selected",
	"onClick"
];
var _hoisted_14 = {
	key: 0,
	class: "linked-list__extra-leap-connector"
};
var _hoisted_15 = ["data-visible"];
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [
		createBaseVNode("div", _hoisted_1$1, [createBaseVNode("div", _hoisted_2$1, [createBaseVNode("div", _hoisted_3$1, [createBaseVNode("div", { class: normalizeClass(["linked-list", { "linked-list--expanded": $props.density === $setup.TreeDensity.Expanded }]) }, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.pages, (page, pageIndex) => {
			return openBlock(), createElementBlock("div", {
				key: page._id,
				"data-form-page": page._id,
				class: normalizeClass(["linked-list__column", { "linked-list__column--has-end-connection": $setup.isEndConnectionColumn(pageIndex) }])
			}, [createBaseVNode("div", {
				class: "linked-list__page-name cursor-pointer",
				style: normalizeStyle({ "anchor-name": $setup.pageAnchor(pageIndex) }),
				onClick: ($event) => $setup.selectPage(page)
			}, [createBaseVNode("div", { class: normalizeClass(["flex w-full min-w-0 flex-nowrap items-center justify-center gap-1.5", { "-ms-1.5": $setup.hasPageIndicators(page, pageIndex) }]) }, [
				$setup.isConnectorDestination(pageIndex) ? (openBlock(), createBlock($setup["Icon"], {
					key: 0,
					name: "chevron-right",
					class: "logic-arrow size-3! shrink-0 -ms-2.5 relative -top-0.25 text-blue-400 dark:text-blue-500",
					"aria-hidden": "true"
				})) : createCommentVNode("", true),
				$setup.hasPageRules(page) ? withDirectives((openBlock(), createElementBlock("span", _hoisted_6, [createVNode($setup["Icon"], {
					name: "logic-tree",
					class: "size-3.5! text-gray-400 dark:text-gray-500",
					"aria-hidden": "true"
				})])), [[_directive_tooltip, _ctx.__("Logic attached")]]) : createCommentVNode("", true),
				createBaseVNode("div", { class: normalizeClass(["mx-auto flex w-full shrink-0 justify-center items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-xs font-medium text-gray-850 bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200", {
					"w-[85%]!": $setup.hasPageIndicators(page, pageIndex),
					"ring-1 ring-blue-500 border-transparent": $setup.isPageSelected(page)
				}]) }, [createVNode($setup["Icon"], {
					name: "page",
					class: "size-4 shrink-0 -ms-1.5 text-gray-500 dark:text-gray-400",
					"aria-hidden": "true"
				}), createBaseVNode("span", _hoisted_7, toDisplayString($setup.pageTitle(page, pageIndex)), 1)], 2)
			], 2)], 12, _hoisted_5), createBaseVNode("div", _hoisted_8, [(openBlock(true), createElementBlock(Fragment, null, renderList(page.sections, (section) => {
				return openBlock(), createElementBlock("div", {
					key: section._id,
					class: "linked-list__section"
				}, [createBaseVNode("div", {
					class: "linked-list__section-marker",
					"aria-label": _ctx.__(section.display)
				}, [createBaseVNode("span", _hoisted_10, toDisplayString(_ctx.__(section.display)), 1)], 8, _hoisted_9), createBaseVNode("ul", {
					class: "field-sort-container",
					"data-sort-section": section._id
				}, [!section.fields.length ? (openBlock(), createElementBlock("div", _hoisted_12, toDisplayString(_ctx.__("No fields")), 1)) : createCommentVNode("", true), (openBlock(true), createElementBlock(Fragment, null, renderList(section.fields, (field) => {
					return withDirectives((openBlock(), createElementBlock("li", {
						key: field._id,
						"data-field-item": "",
						"data-field-id": field._id,
						"data-field-item-selected": $setup.isFieldSelected(field) ? "" : void 0,
						class: normalizeClass(["cursor-pointer", {
							"linked-list__fieldset": field.type === "import",
							"linked-list__hidden-field": field.config?.hidden,
							"linked-list__connector": $setup.fieldConnection(field),
							"linked-list__page-leap": $setup.fieldConnection(field)?.leap,
							"has-adjacent-cell": $setup.fieldConnection(field)?.leap && $setup.adjacentRightCells[field._id],
							"ring-1 ring-blue-500": $setup.isFieldSelected(field)
						}]),
						style: normalizeStyle($setup.fieldConnection(field) ? { "--end-connection": $setup.fieldConnection(field).endConnection } : null),
						onClick: ($event) => $setup.selectField(field)
					}, [field.type === "import" ? (openBlock(), createBlock($setup["LogicTreeFieldset"], {
						key: 0,
						field
					}, null, 8, ["field"])) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [$setup.fieldConnection(field)?.leap ? (openBlock(), createElementBlock("div", _hoisted_14)) : createCommentVNode("", true), createVNode($setup["LogicTreeField"], { field }, null, 8, ["field"])], 64))], 14, _hoisted_13)), [[_directive_tooltip, field.type === "import" ? _ctx.__(`Logic can't be added to imported fields. Please edit the fieldset instead.`) : null]]);
				}), 128))], 8, _hoisted_11)]);
			}), 128))])], 10, _hoisted_4);
		}), 128))], 2)], 512)])]),
		$props.selected ? (openBlock(), createBlock($setup["Button"], {
			key: 0,
			class: "min-[1000px]:hidden sticky top-3 z-(--z-index-above) sm:translate-x-3 md:translate-x-9 mb-5 col-start-3 row-start-1",
			text: _ctx.__("Settings"),
			icon: "cog",
			onClick: _cache[0] || (_cache[0] = ($event) => $setup.isInspectorOpen = !$setup.isInspectorOpen)
		}, null, 8, ["text"])) : createCommentVNode("", true),
		!$setup.isRightPanelCollapsed ? (openBlock(), createBlock($setup["LayoutPanel"], {
			key: 1,
			side: "right",
			"mobile-open": $setup.isInspectorOpen
		}, {
			default: withCtx(() => [$props.selected?.type === $setup.SelectionType.Page ? (openBlock(), createBlock($setup["PageInspector"], { key: 0 })) : $props.selected?.type === $setup.SelectionType.Field ? (openBlock(), createBlock($setup["FieldInspector"], { key: 1 })) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["mobile-open"])) : createCommentVNode("", true),
		createBaseVNode("div", {
			class: "mobile-panel-backdrop",
			"data-visible": $setup.isInspectorOpen,
			onClick: _cache[1] || (_cache[1] = ($event) => $setup.isInspectorOpen = false)
		}, null, 8, _hoisted_15)
	], 64);
}
var LogicTree_default = /*#__PURE__*/ _plugin_vue_export_helper_default(LogicTree_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$1], ["__file", "LogicTree.vue"]]);
//#endregion
//#region resources/js/pages/forms/Logic.vue?vue&type=script&setup=true&lang.ts
var View = /* @__PURE__ */ function(View) {
	View["List"] = "list";
	View["Tree"] = "tree";
	return View;
}(View || {});
var Logic_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	layout: [
		Layout_default,
		PanelLayout_default,
		Layout_default$1
	],
	__name: "Logic",
	props: {
		form: Object,
		formFields: Object,
		action: String,
		fieldtypes: Array
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const errors = ref({});
		const saving = ref(false);
		const saveBinding = ref(null);
		const formFields = ref(props.formFields);
		const selected = ref(null);
		const view = ref(preferences.get("forms.logic.view", "list"));
		const treeDensity = ref(preferences.get("forms.logic.tree.density", TreeDensity.Compressed));
		provide("fieldtypes", props.fieldtypes);
		const pages = computed({
			get: () => formFields.value.pages,
			set: (pages) => formFields.value.pages = pages
		});
		const fieldCategory = (field) => props.fieldtypes.find((fieldtype) => fieldtype.handle === field.fieldtype)?.categories?.[0] ?? "other";
		const findField = (id) => pages.value.flatMap((page) => page.sections).flatMap((section) => section.fields).find((field) => field._id === id) ?? null;
		const flattenedFields = computed(() => pages.value.flatMap((page, pageIndex) => page.sections.flatMap((section) => section.fields.filter((field) => field.type !== "import").map((field) => ({
			field,
			pageIndex
		})))));
		const selectedPage = computed(() => selected.value?.type === SelectionType.Page ? pages.value.find((page) => page._id === selected.value.id) ?? null : null);
		const selectedField = computed(() => selected.value?.type === SelectionType.Field ? findField(selected.value.id) : null);
		const { showFieldNumbers } = useFieldNumberingPreference();
		const fieldNumbers = computed(() => {
			if (!showFieldNumbers.value) return /* @__PURE__ */ new Map();
			let number = 0;
			const map = /* @__PURE__ */ new Map();
			pages.value.forEach((page) => page.sections.forEach((section) => section.fields.forEach((field) => {
				if (field.type === "import") {
					Object.keys(field.previews ?? {}).forEach((handle) => map.set(`${field._id}:${handle}`, ++number));
					return;
				}
				if (field.config?.hidden || fieldCategory(field) === "information") return;
				map.set(field.handle, ++number);
			})));
			return map;
		});
		provide("fieldNumbers", fieldNumbers);
		const suggestableFields = computed(() => flattenedFields.value.filter(({ field }) => collectsValue(fieldCategory(field))).map(({ field, pageIndex }) => ({
			handle: field.handle,
			icon: field.icon,
			category: fieldCategory(field),
			pageIndex,
			config: {
				type: field.fieldtype,
				display: field.config?.display,
				options: field.config?.options
			}
		})));
		const inspecting = computed(() => selectedField.value ?? selectedPage.value);
		const avoidTrackingDirtyState = ref(false);
		const dirty = () => {
			if (!avoidTrackingDirtyState.value) Statamic.$dirty.add("form-logic");
		};
		const clearDirtyState = () => Statamic.$dirty.remove("form-logic");
		const withoutDirtying = (callback) => {
			const previous = avoidTrackingDirtyState.value;
			avoidTrackingDirtyState.value = true;
			callback();
			nextTick(() => avoidTrackingDirtyState.value = previous);
		};
		const deletePage = (pageId) => {
			if (pages.value.length <= 1) return;
			const pageIndex = pages.value.findIndex((page) => page._id === pageId);
			if (pageIndex === -1) return;
			formFields.value.pages.splice(pageIndex, 1);
			selected.value = null;
			dirty();
		};
		provideBuilderContext({
			deletePage,
			dirty,
			errors,
			fieldtypes: props.fieldtypes,
			form: props.form,
			inspecting,
			pages,
			showFieldDirection: false,
			withoutDirtying
		});
		const save = () => {
			if (saving.value) return;
			errors.value = {};
			saving.value = true;
			axios.patch(props.action, { pages: pages.value }).then((response) => {
				clearDirtyState();
				Statamic.$toast.success(__("Saved"));
			}).catch((e) => {
				if (e.response?.status === 422) {
					errors.value = e.response.data.errors;
					Statamic.$toast.error(e.response.data.message);
				} else Statamic.$toast.error(__("Something went wrong"));
			}).finally(() => saving.value = false);
		};
		watch(formFields, dirty, { deep: true });
		watch(view, (view) => {
			preferences.set("forms.logic.view", view);
			if (view !== "tree") selected.value = null;
		});
		watch(treeDensity, (density) => preferences.set("forms.logic.tree.density", density));
		onMounted(() => {
			saveBinding.value = keys.bindGlobal(["return", "mod+s"], (e) => {
				e.preventDefault();
				save();
			});
		});
		onUnmounted(() => {
			clearDirtyState();
			saveBinding.value?.destroy();
		});
		const __returned__ = {
			View,
			props,
			errors,
			saving,
			saveBinding,
			formFields,
			selected,
			view,
			treeDensity,
			pages,
			fieldCategory,
			findField,
			flattenedFields,
			selectedPage,
			selectedField,
			showFieldNumbers,
			fieldNumbers,
			suggestableFields,
			inspecting,
			avoidTrackingDirtyState,
			dirty,
			clearDirtyState,
			withoutDirtying,
			deletePage,
			save,
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
				return Group_default;
			},
			get ToggleItem() {
				return Item_default$1;
			},
			FormStatusIndicator: FormStatusIndicator_default,
			FieldNumberingToggle: FieldNumberingToggle_default,
			LogicList: LogicList_default,
			LogicTree: LogicTree_default,
			TreeDensity,
			Head: Head_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
//#endregion
//#region resources/js/pages/forms/Logic.vue
var _hoisted_1 = { class: "hidden sm:inline" };
var _hoisted_2 = { class: "mx-auto w-full max-w-5xl min-w-0 shrink-0" };
var _hoisted_3 = { class: "flex items-center gap-2.5" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [
			_ctx.__("Logic"),
			_ctx.__($props.form.title),
			_ctx.__("Forms")
		] }, null, 8, ["title"]),
		(openBlock(), createBlock(Teleport, { to: "#form-layout-actions" }, [createVNode($setup["Button"], {
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
		}, 8, ["aria-label", "disabled"])])),
		createBaseVNode("div", _hoisted_2, [createVNode($setup["Header"], { class: "mb-2 md:py-9" }, {
			title: withCtx(() => [createVNode($setup["FormStatusIndicator"], { status: $props.form.status }, null, 8, ["status"]), createTextVNode(" " + toDisplayString(_ctx.__($props.form.title)), 1)]),
			actions: withCtx(() => [createBaseVNode("div", _hoisted_3, [
				createVNode($setup["FieldNumberingToggle"]),
				$setup.view === $setup.View.Tree ? (openBlock(), createBlock($setup["ToggleGroup"], {
					key: 0,
					modelValue: $setup.treeDensity,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.treeDensity = $event),
					size: "xs"
				}, {
					default: withCtx(() => [withDirectives(createVNode($setup["ToggleItem"], {
						value: $setup.TreeDensity.Expanded,
						icon: "expand",
						"aria-label": _ctx.__("Expanded view")
					}, null, 8, ["value", "aria-label"]), [[_directive_tooltip, _ctx.__("Expanded view")]]), withDirectives(createVNode($setup["ToggleItem"], {
						value: $setup.TreeDensity.Compressed,
						icon: "collapse",
						"aria-label": _ctx.__("Collapsed view")
					}, null, 8, ["value", "aria-label"]), [[_directive_tooltip, _ctx.__("Collapsed view")]])]),
					_: 1
				}, 8, ["modelValue"])) : createCommentVNode("", true),
				createVNode($setup["ToggleGroup"], {
					modelValue: $setup.view,
					"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.view = $event),
					size: "sm"
				}, {
					default: withCtx(() => [createVNode($setup["ToggleItem"], {
						value: $setup.View.List,
						icon: "layout-list",
						label: _ctx.__("List")
					}, null, 8, ["value", "label"]), createVNode($setup["ToggleItem"], {
						value: $setup.View.Tree,
						icon: "logic-tree",
						label: _ctx.__("Tree")
					}, null, 8, ["value", "label"])]),
					_: 1
				}, 8, ["modelValue"])
			])]),
			_: 1
		}), $setup.view === $setup.View.List ? (openBlock(), createBlock($setup["LogicList"], {
			key: 0,
			pages: $setup.pages,
			"onUpdate:pages": _cache[2] || (_cache[2] = ($event) => $setup.pages = $event),
			"suggestable-fields": $setup.suggestableFields,
			fieldtypes: $props.fieldtypes
		}, null, 8, [
			"pages",
			"suggestable-fields",
			"fieldtypes"
		])) : createCommentVNode("", true)]),
		$setup.view === $setup.View.Tree ? (openBlock(), createBlock($setup["LogicTree"], {
			key: 0,
			pages: $setup.pages,
			"onUpdate:pages": _cache[3] || (_cache[3] = ($event) => $setup.pages = $event),
			density: $setup.treeDensity,
			selected: $setup.selected,
			onSelect: _cache[4] || (_cache[4] = ($event) => $setup.selected = $event)
		}, null, 8, [
			"pages",
			"density",
			"selected"
		])) : createCommentVNode("", true)
	], 64);
}
var Logic_default = /*#__PURE__*/ _plugin_vue_export_helper_default(Logic_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "Logic.vue"]]);
//#endregion
export { Logic_default as default };
