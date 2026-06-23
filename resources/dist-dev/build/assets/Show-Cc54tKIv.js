const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./PageTree-BsFaz_Gy.js","./_plugin-vue_export-helper-BOaGB7Aw.js","./ui-BfR7XN6t.js","./chunk-B-1-B7_t.js","./preload-helper-CW7Fztz1.js","./index.esm-B4SStoAz.js","./vue.esm-bundler-BbHU-fTn.js","./globals-CR4DMcIG.js","./api-BR1uuoCk.js","./ui-BflfEhKe.css","./v3-R_90cesw.js"])))=>i.map(i=>d[i]);
import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, E as getCurrentInstance, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, _t as ref, at as withDirectives, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, o as vModelCheckbox, q as resolveDirective, v as createCommentVNode, w as defineAsyncComponent, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { G as axios, c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { $n as CalendarHeadCell_default, $r as $14e0f24ef4ac5c92$export$42c81a444fbfb5d4, $t as Menu_default, At as Close_default, E as StatusIndicator_default, Gr as $35ea8db9cb2ccb90$export$ca871e8dbb80966f, Gt as Label_default, Jn as Heading_default, Qn as CalendarHeader_default, Qr as $11d87f3f76e88657$export$e57ff100d91bd4b9, Tt as Popover_default, Ut as Header_default, Wr as $35ea8db9cb2ccb90$export$99faa760c7908e4f, Wt as Field_default, Xt as Separator_default, Zn as CalendarHeading_default, Zr as $11d87f3f76e88657$export$b21e0b124e224484, ar as CalendarCell_default, ci as Group_default, en as Label_default$1, er as CalendarGridRow_default, i as Listing_default, ir as CalendarCellTrigger_default, jn as Select_default, jt as Modal_default, li as Button_default, ni as $14e0f24ef4ac5c92$export$ef8b6d9133084f4e, nn as Dropdown_default, nr as CalendarGridBody_default, nt as Group_default$1, or as CalendarRoot_default, qr as $fae977aafc393c5c$export$8e384432362ed0f0, rr as CalendarGrid_default, ti as $14e0f24ef4ac5c92$export$aa8b41735afcabd2, tn as Item_default$1, tr as CalendarGridHead_default, tt as Item_default, u as ItemActions_default } from "./ui-BfR7XN6t.js";
import { t as __vitePreload } from "./preload-helper-CW7Fztz1.js";
import { D as DateFormatter, b as preferences } from "./api-BR1uuoCk.js";
import { O as CreateEntryButton_default, r as Head_default } from "./index-Bu3QBQkS.js";
import { t as SiteSelector_default } from "./SiteSelector-BCgXo6du.js";
//#region resources/js/components/collections/DeleteEntryConfirmation.vue
var _sfc_main$7 = {
	__name: "DeleteEntryConfirmation",
	props: { children: Number },
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			props: __props,
			modalOpen: ref(true),
			shouldDeleteChildren: ref(false),
			get Modal() {
				return Modal_default;
			},
			get ModalClose() {
				return Close_default;
			},
			get Button() {
				return Button_default;
			},
			ref
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$6 = ["textContent"];
var _hoisted_2$4 = {
	key: 0,
	class: "flex items-center"
};
var _hoisted_3$3 = { class: "flex items-center justify-end space-x-3 pt-3 pb-1" };
function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Modal"], {
		title: _ctx.__("Delete Entry"),
		open: $setup.modalOpen,
		"onUpdate:open": _cache[2] || (_cache[2] = ($event) => $setup.modalOpen = $event),
		onDismissed: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("cancel"))
	}, {
		footer: withCtx(() => [createBaseVNode("div", _hoisted_3$3, [createVNode($setup["ModalClose"], null, {
			default: withCtx(() => [createVNode($setup["Button"], {
				variant: "ghost",
				text: _ctx.__("Cancel")
			}, null, 8, ["text"])]),
			_: 1
		}), createVNode($setup["Button"], {
			variant: "danger",
			text: _ctx.__("Delete"),
			onClick: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("confirm", $setup.shouldDeleteChildren))
		}, null, 8, ["text"])])]),
		default: withCtx(() => [createBaseVNode("p", {
			class: "mb-4",
			textContent: toDisplayString(_ctx.__("Are you sure you want to delete this entry?"))
		}, null, 8, _hoisted_1$6), $props.children ? (openBlock(), createElementBlock("label", _hoisted_2$4, [withDirectives(createBaseVNode("input", {
			type: "checkbox",
			class: "ltr:mr-2 rtl:ml-2",
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.shouldDeleteChildren = $event)
		}, null, 512), [[vModelCheckbox, $setup.shouldDeleteChildren]]), createTextVNode(" " + toDisplayString(_ctx.__n("Delete child entry|Delete :count child entries", $props.children)), 1)])) : createCommentVNode("", true)]),
		_: 1
	}, 8, ["title", "open"]);
}
var DeleteEntryConfirmation_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$7, [["render", _sfc_render$7], ["__file", "DeleteEntryConfirmation.vue"]]);
//#endregion
//#region resources/js/components/collections/DeleteLocalizationConfirmation.vue
var _sfc_main$6 = {
	components: {
		Modal: Modal_default,
		Field: Field_default,
		Button: Button_default,
		ButtonGroup: Group_default
	},
	props: { entries: {
		type: Number,
		required: true
	} },
	data() {
		return {
			behavior: null,
			error: false
		};
	},
	computed: { instructions() {
		let url = docs_url("/tips/localizing-entries#deleting");
		return `${__("messages.choose_entry_localization_deletion_behavior")} <a href="${url}" target="_blank">${__("Learn more")}</a>`;
	} },
	methods: { confirm() {
		if (!this.behavior) {
			this.error = true;
			return;
		}
		this.$emit("confirm", this.behavior);
	} }
};
var _hoisted_1$5 = { class: "flex items-center justify-end space-x-3 pt-3 pb-1" };
function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Button = resolveComponent("Button");
	const _component_ButtonGroup = resolveComponent("ButtonGroup");
	const _component_Field = resolveComponent("Field");
	const _component_Modal = resolveComponent("Modal");
	return openBlock(), createBlock(_component_Modal, {
		title: _ctx.__("Delete"),
		open: "",
		"onUpdate:open": _cache[3] || (_cache[3] = ($event) => _ctx.$emit("cancel"))
	}, {
		footer: withCtx(() => [createBaseVNode("div", _hoisted_1$5, [createVNode(_component_Button, {
			variant: "ghost",
			onClick: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("cancel")),
			text: _ctx.__("Cancel")
		}, null, 8, ["text"]), createVNode(_component_Button, {
			variant: "danger",
			onClick: $options.confirm,
			text: _ctx.__("Confirm")
		}, null, 8, ["onClick", "text"])])]),
		default: withCtx(() => [_cache[4] || (_cache[4] = createBaseVNode("p", null, "Are you sure you want to delete this?", -1)), createVNode(_component_Field, {
			errors: $data.error ? [_ctx.__("statamic::validation.required")] : null,
			instructions: $options.instructions,
			label: _ctx.__("Localizations")
		}, {
			default: withCtx(() => [createVNode(_component_ButtonGroup, { ref: "buttonGroup" }, {
				default: withCtx(() => [createVNode(_component_Button, {
					ref: "button",
					name: _ctx.name,
					onClick: _cache[0] || (_cache[0] = ($event) => $data.behavior = "delete"),
					value: "delete",
					variant: $data.behavior === "delete" ? "danger" : "default",
					text: _ctx.__("Delete")
				}, null, 8, [
					"name",
					"variant",
					"text"
				]), createVNode(_component_Button, {
					ref: "button",
					name: _ctx.name,
					onClick: _cache[1] || (_cache[1] = ($event) => $data.behavior = "copy"),
					value: "copy",
					variant: $data.behavior === "copy" ? "primary" : "default",
					text: _ctx.__("Detach")
				}, null, 8, [
					"name",
					"variant",
					"text"
				])]),
				_: 1
			}, 512)]),
			_: 1
		}, 8, [
			"errors",
			"instructions",
			"label"
		])]),
		_: 1
	}, 8, ["title"]);
}
var DeleteLocalizationConfirmation_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$6, [["render", _sfc_render$6], ["__file", "DeleteLocalizationConfirmation.vue"]]);
//#endregion
//#region resources/js/components/entries/calendar/MonthEntry.vue
var _sfc_main$5 = {
	__name: "MonthEntry",
	props: { entry: {
		type: Object,
		required: true
	} },
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const __returned__ = {
			props,
			entryClasses: computed(() => ({
				"border-green-500 hover:bg-green-50 dark:hover:bg-green-900": props.entry.status === "published",
				"border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800": props.entry.status === "draft",
				"border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900": props.entry.status === "scheduled"
			})),
			time: computed(() => DateFormatter.format(props.entry.date?.date || props.entry.date, "time")),
			computed,
			get Link() {
				return link_default;
			},
			get DateFormatter() {
				return DateFormatter;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$4 = ["textContent"];
var _hoisted_2$3 = ["textContent"];
function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Link"], {
		href: $props.entry.edit_url,
		key: $props.entry.id,
		class: normalizeClass(["text-2xs @3xl:text-xs px-2 border-s-2 rounded-e-sm cursor-pointer flex flex-col group/entry", $setup.entryClasses])
	}, {
		default: withCtx(() => [createBaseVNode("span", {
			class: "line-clamp-2",
			textContent: toDisplayString($props.entry.title)
		}, null, 8, _hoisted_1$4), createBaseVNode("span", {
			class: "hidden @4xl:block text-2xs text-gray-400 dark:text-gray-400 group-hover/entry:dark:text-gray-300",
			textContent: toDisplayString($setup.time)
		}, null, 8, _hoisted_2$3)]),
		_: 1
	}, 8, ["href", "class"]);
}
var MonthEntry_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$5, [["render", _sfc_render$5], ["__file", "MonthEntry.vue"]]);
//#endregion
//#region resources/js/components/entries/calendar/calendar.js
function getWeekDates(currentDate) {
	if (!currentDate) throw new Error("getWeekDates called with undefined currentDate");
	const weekStart = $14e0f24ef4ac5c92$export$42c81a444fbfb5d4(currentDate, new DateFormatter().locale);
	const weekDates = [];
	for (let i = 0; i < 7; i++) weekDates.push(weekStart.add({ days: i }));
	return weekDates;
}
function isToday(date) {
	const today = /* @__PURE__ */ new Date();
	return new Date(date.year, date.month - 1, date.day).toDateString() === today.toDateString();
}
function getCurrentDateRange(currentDate, viewMode) {
	if (!currentDate || !viewMode) throw new Error("getCurrentDateRange called with undefined values");
	return viewMode === "week" ? getWeekDateRange(currentDate) : getMonthDateRange(currentDate);
}
function getWeekDateRange(date) {
	const weekDates = getWeekDates(date);
	return {
		startDate: new Date(weekDates[0].year, weekDates[0].month - 1, weekDates[0].day),
		endDate: new Date(weekDates[6].year, weekDates[6].month - 1, weekDates[6].day)
	};
}
function getMonthDateRange(date) {
	const locale = new DateFormatter().locale;
	const firstDayOfMonth = new $35ea8db9cb2ccb90$export$99faa760c7908e4f(date.year, date.month, 1);
	const lastDayOfMonth = new $35ea8db9cb2ccb90$export$99faa760c7908e4f(date.year, date.month, date.calendar.getDaysInMonth(date));
	const weekStartOfFirst = $14e0f24ef4ac5c92$export$42c81a444fbfb5d4(firstDayOfMonth, locale);
	const weekEndOfLast = $14e0f24ef4ac5c92$export$ef8b6d9133084f4e(lastDayOfMonth, locale);
	return {
		startDate: new Date(weekStartOfFirst.year, weekStartOfFirst.month - 1, weekStartOfFirst.day),
		endDate: new Date(weekEndOfLast.year, weekEndOfLast.month - 1, weekEndOfLast.day)
	};
}
function getCreateUrlDateParam(date, hour) {
	if (hour) date = new $35ea8db9cb2ccb90$export$ca871e8dbb80966f(date.year, date.month, date.day, hour, 0);
	const d = $11d87f3f76e88657$export$e57ff100d91bd4b9(date.toDate($14e0f24ef4ac5c92$export$aa8b41735afcabd2()), "UTC");
	const pad = (n) => String(n).padStart(2, "0");
	return `${d.year}-${pad(d.month)}-${pad(d.day)}-${pad(d.hour)}${pad(d.minute)}`;
}
//#endregion
//#region resources/js/components/entries/calendar/Month.vue
var _sfc_main$4 = {
	__name: "Month",
	props: {
		weekDays: {
			type: Array,
			required: true
		},
		grid: {
			type: Array,
			required: true
		},
		entries: {
			type: Array,
			required: true
		},
		selectedDate: {
			type: Object,
			default: null
		},
		createUrl: {
			type: String,
			required: true
		},
		blueprints: {
			type: Array,
			default: () => []
		}
	},
	emits: ["select-date"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const emit = __emit;
		const isCurrentDay = (dayIndex) => {
			const currentDayName = DateFormatter.format("now", { weekday: "long" });
			return dayIndex === props.weekDays.findIndex((day) => day.toLowerCase() === currentDayName.toLowerCase());
		};
		const getEntriesForDate = (date) => {
			const dateStr = date.toString();
			return props.entries.filter((entry) => {
				return $fae977aafc393c5c$export$8e384432362ed0f0(entry.date?.date || entry.date).toString().split("T")[0] === dateStr;
			});
		};
		const weekHasEntries = (weekDates) => {
			return weekDates.some((date) => getEntriesForDate(date).length > 0);
		};
		const cellClasses = (weekDate, monthValue) => ({
			"bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700": weekDate.month !== monthValue.month,
			"bg-white dark:bg-gray-900": weekDate.month === monthValue.month,
			"bg-ui-accent-bg/10! border border-ui-accent-bg!": isToday(weekDate)
		});
		const dateNumberClasses = (weekDate, selected, today, outsideView) => ({
			"text-gray-400 dark:text-gray-600": outsideView,
			"text-gray-900 dark:text-white": !outsideView,
			"text-white bg-blue-600": props.selectedDate && props.selectedDate.toString() === weekDate.toString(),
			"text-ui-accent-text": today
		});
		const entryStatusClasses = (status) => ({
			"bg-green-500": status === "published",
			"bg-gray-300": status === "draft",
			"bg-purple-500": status === "scheduled"
		});
		const selectDate = (date) => {
			emit("select-date", date);
		};
		const __returned__ = {
			props,
			emit,
			isCurrentDay,
			getEntriesForDate,
			weekHasEntries,
			cellClasses,
			dateNumberClasses,
			entryStatusClasses,
			selectDate,
			get CalendarCell() {
				return CalendarCell_default;
			},
			get CalendarCellTrigger() {
				return CalendarCellTrigger_default;
			},
			get CalendarGrid() {
				return CalendarGrid_default;
			},
			get CalendarGridBody() {
				return CalendarGridBody_default;
			},
			get CalendarGridHead() {
				return CalendarGridHead_default;
			},
			get CalendarGridRow() {
				return CalendarGridRow_default;
			},
			get CalendarHeadCell() {
				return CalendarHeadCell_default;
			},
			CalendarEntry: MonthEntry_default,
			CreateEntryButton: CreateEntryButton_default,
			get Button() {
				return Button_default;
			},
			get isToday() {
				return isToday;
			},
			get getCreateUrlDateParam() {
				return getCreateUrlDateParam;
			},
			get DateFormatter() {
				return DateFormatter;
			},
			get parseAbsoluteToLocal() {
				return $fae977aafc393c5c$export$8e384432362ed0f0;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$3 = { class: "flex items-center justify-center gap-1" };
var _hoisted_2$2 = {
	key: 0,
	class: "w-1.5 h-1.5 mr-1 bg-ui-accent-bg rounded-full"
};
var _hoisted_3$2 = ["textContent"];
var _hoisted_4$2 = ["textContent"];
var _hoisted_5$2 = ["textContent"];
var _hoisted_6$2 = {
	key: 0,
	class: "@3xl:hidden w-full"
};
var _hoisted_7$2 = { class: "flex h-1 rounded-full overflow-hidden items-center justify-center" };
var _hoisted_8$2 = { class: "space-y-1.5 flex-1 overflow-auto overscroll-contain h-full w-full hidden @3xl:block" };
var _hoisted_9$2 = { class: "absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity hidden @3xl:block" };
function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["CalendarGrid"], { class: "w-full border-collapse" }, {
		default: withCtx(() => [createVNode($setup["CalendarGridHead"], null, {
			default: withCtx(() => [createVNode($setup["CalendarGridRow"], { class: "grid grid-cols-7 gap-3 mb-2" }, {
				default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.weekDays, (day, index) => {
					return openBlock(), createBlock($setup["CalendarHeadCell"], {
						key: day,
						class: "p-2 text-center font-medium text-sm text-gray-700 dark:text-gray-400 bg-gray-200/75 dark:bg-gray-900/75 rounded-lg"
					}, {
						default: withCtx(() => [createBaseVNode("div", _hoisted_1$3, [
							$setup.isCurrentDay(index) ? (openBlock(), createElementBlock("div", _hoisted_2$2)) : createCommentVNode("", true),
							createBaseVNode("span", {
								class: "@4xl:hidden",
								textContent: toDisplayString(day.slice(0, 2))
							}, null, 8, _hoisted_3$2),
							createBaseVNode("span", {
								class: "hidden @4xl:block",
								textContent: toDisplayString(day)
							}, null, 8, _hoisted_4$2)
						])]),
						_: 2
					}, 1024);
				}), 128))]),
				_: 1
			})]),
			_: 1
		}), createVNode($setup["CalendarGridBody"], { class: "space-y-3 calendar-grid" }, {
			default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.grid, (month) => {
				return openBlock(), createElementBlock(Fragment, { key: month.value.toString() }, [(openBlock(true), createElementBlock(Fragment, null, renderList(month.rows.filter((weekDates) => weekDates.some((date) => date.month === month.value.month)), (weekDates, weekIndex) => {
					return openBlock(), createBlock($setup["CalendarGridRow"], {
						key: `weekDate-${weekIndex}`,
						"data-week-has-entries": $setup.weekHasEntries(weekDates),
						class: "grid grid-cols-7 gap-3"
					}, {
						default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList(weekDates, (weekDate) => {
							return openBlock(), createBlock($setup["CalendarCell"], {
								key: weekDate.toString(),
								date: weekDate,
								class: normalizeClass(["aspect-square p-2 rounded-xl ring ring-gray-200 dark:ring-gray-700 shadow-ui-sm group relative", $setup.cellClasses(weekDate, month.value)])
							}, {
								default: withCtx(() => [createVNode($setup["CalendarCellTrigger"], {
									day: weekDate,
									month: month.value,
									class: "max-w-full max-h-full aspect-square flex flex-col items-center justify-center @3xl:items-start @3xl:justify-start",
									onClick: ($event) => $setup.selectDate(weekDate)
								}, {
									default: withCtx(({ dayValue, selected, today, outsideView }) => [
										createBaseVNode("div", {
											class: normalizeClass(["text-sm mb-1 rounded-full size-6 flex items-center justify-center", $setup.dateNumberClasses(weekDate, selected, today, outsideView)]),
											textContent: toDisplayString(dayValue)
										}, null, 10, _hoisted_5$2),
										$setup.getEntriesForDate(weekDate).length > 0 ? (openBlock(), createElementBlock("div", _hoisted_6$2, [createBaseVNode("div", _hoisted_7$2, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.getEntriesForDate(weekDate).slice(0, 4), (entry, index) => {
											return openBlock(), createElementBlock("div", {
												key: entry.id,
												class: normalizeClass(["w-1/4 h-full first:rounded-s-full last:rounded-e-full", $setup.entryStatusClasses(entry.status)])
											}, null, 2);
										}), 128))])])) : createCommentVNode("", true),
										createBaseVNode("div", _hoisted_8$2, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.getEntriesForDate(weekDate), (entry) => {
											return openBlock(), createBlock($setup["CalendarEntry"], {
												key: entry.id,
												entry
											}, null, 8, ["entry"]);
										}), 128))])
									]),
									_: 2
								}, 1032, [
									"day",
									"month",
									"onClick"
								]), createBaseVNode("div", _hoisted_9$2, [createVNode($setup["CreateEntryButton"], {
									params: { values: { date: $setup.getCreateUrlDateParam(weekDate) } },
									blueprints: $props.blueprints,
									variant: "subtle",
									size: "sm"
								}, {
									trigger: withCtx(({ create }) => [createVNode($setup["Button"], {
										icon: "plus",
										size: "sm",
										variant: "subtle",
										onClick: create
									}, null, 8, ["onClick"])]),
									_: 1
								}, 8, ["params", "blueprints"])])]),
								_: 2
							}, 1032, ["date", "class"]);
						}), 128))]),
						_: 2
					}, 1032, ["data-week-has-entries"]);
				}), 128))], 64);
			}), 128))]),
			_: 1
		})]),
		_: 1
	});
}
var Month_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$4, [
	["render", _sfc_render$4],
	["__scopeId", "data-v-d0b4e507"],
	["__file", "Month.vue"]
]);
//#endregion
//#region resources/js/components/entries/calendar/WeekEntry.vue
var _sfc_main$3 = {
	__name: "WeekEntry",
	props: { entry: {
		type: Object,
		required: true
	} },
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const __returned__ = {
			props,
			entryClasses: computed(() => ({
				"border-green-500 bg-green-50 dark:bg-green-900/20": props.entry.status === "published",
				"border-gray-300 bg-gray-50 dark:bg-gray-800": props.entry.status === "draft",
				"border-purple-500 bg-purple-50 dark:bg-purple-900/20": props.entry.status === "scheduled"
			})),
			computed,
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
};
var _hoisted_1$2 = ["textContent"];
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Link"], {
		href: $props.entry.edit_url,
		key: $props.entry.id,
		class: normalizeClass(["block text-xs p-1 rounded-r border-l-2 mb-1 cursor-pointer hover:shadow-sm", $setup.entryClasses])
	}, {
		default: withCtx(() => [createBaseVNode("div", {
			class: "font-medium line-clamp-2",
			textContent: toDisplayString($props.entry.title)
		}, null, 8, _hoisted_1$2)]),
		_: 1
	}, 8, ["href", "class"]);
}
var WeekEntry_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$3, [["render", _sfc_render$3], ["__file", "WeekEntry.vue"]]);
//#endregion
//#region resources/js/components/entries/calendar/Week.vue
var _sfc_main$2 = {
	__name: "Week",
	props: {
		weekDates: {
			type: Array,
			required: true
		},
		entries: {
			type: Array,
			required: true
		},
		selectedDate: {
			type: Object,
			default: null
		},
		createUrl: {
			type: String,
			required: true
		},
		blueprints: {
			type: Array,
			default: () => []
		}
	},
	emits: ["select-date"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const emit = __emit;
		const $date = new DateFormatter();
		const visibleHours = Array.from({ length: 24 }, (_, i) => i);
		function getEntriesForHour(date, hour) {
			const dateStr = date.toString();
			return props.entries.filter((entry) => {
				const entryDate = $fae977aafc393c5c$export$8e384432362ed0f0(entry.date?.date || entry.date);
				if (entryDate.toString().split("T")[0] !== dateStr) return false;
				return entryDate.hour === hour;
			});
		}
		const headerClasses = (date) => ({
			"bg-blue-50 dark:bg-blue-900/20": isSelectedDate(date),
			"bg-gray-50 dark:bg-gray-800": isToday(date),
			"bg-gray-50 dark:bg-gray-900/10": !isSelectedDate(date) && !isToday(date)
		});
		const dateNumberClasses = (date) => ({
			"text-blue-600 dark:text-blue-400": isSelectedDate(date),
			"text-gray-900 dark:text-white": !isSelectedDate(date),
			"rounded-full text-white bg-ui-accent-bg": isToday(date)
		});
		const hourCellClasses = (date, hour) => ({ "hover:bg-gray-50 dark:hover:bg-gray-800/50": getEntriesForHour(date, hour).length === 0 });
		const isSelectedDate = (date) => {
			return props.selectedDate && props.selectedDate.toString() === date.toString();
		};
		const selectDate = (date) => {
			emit("select-date", date);
		};
		function getHourLabel(hour) {
			const date = /* @__PURE__ */ new Date();
			date.setHours(hour, 0, 0, 0);
			return DateFormatter.format(date, { hour: "numeric" });
		}
		const __returned__ = {
			props,
			emit,
			$date,
			visibleHours,
			getEntriesForHour,
			headerClasses,
			dateNumberClasses,
			hourCellClasses,
			isSelectedDate,
			selectDate,
			getHourLabel,
			ref,
			CalendarEntry: WeekEntry_default,
			CreateEntryButton: CreateEntryButton_default,
			get Button() {
				return Button_default;
			},
			get isToday() {
				return isToday;
			},
			get getCreateUrlDateParam() {
				return getCreateUrlDateParam;
			},
			get DateFormatter() {
				return DateFormatter;
			},
			get parseAbsoluteToLocal() {
				return $fae977aafc393c5c$export$8e384432362ed0f0;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$1 = { class: "w-full" };
var _hoisted_2$1 = { class: "grid grid-cols-8 border border-gray-200 dark:border-gray-700 rounded-t-lg overflow-hidden" };
var _hoisted_3$1 = ["onClick"];
var _hoisted_4$1 = ["textContent"];
var _hoisted_5$1 = ["textContent"];
var _hoisted_6$1 = {
	ref: "weekViewContainer",
	class: "grid grid-cols-8 gap-0 border border-gray-200 dark:border-gray-700 rounded-b-lg overflow-auto max-h-[60vh]"
};
var _hoisted_7$1 = { class: "bg-gray-50 dark:bg-gray-900/10" };
var _hoisted_8$1 = ["textContent"];
var _hoisted_9$1 = ["onClick"];
var _hoisted_10 = { class: "absolute inset-0 p-1 overflow-scroll overscroll-contain" };
var _hoisted_11 = {
	key: 0,
	class: "absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity"
};
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$1, [createBaseVNode("div", _hoisted_2$1, [_cache[0] || (_cache[0] = createBaseVNode("div", { class: "p-3 text-sm bg-gray-50 dark:bg-gray-900/10 font-medium text-gray-500 dark:text-gray-400" }, null, -1)), (openBlock(true), createElementBlock(Fragment, null, renderList($props.weekDates, (date) => {
		return openBlock(), createElementBlock("div", {
			key: date.toString(),
			class: normalizeClass(["p-3 text-center border-l border-gray-200 dark:border-gray-700", $setup.headerClasses(date)]),
			onClick: ($event) => $setup.selectDate(date)
		}, [createBaseVNode("div", {
			class: "text-xs text-gray-500 dark:text-gray-400",
			textContent: toDisplayString($setup.$date.format(date, { weekday: "short" }))
		}, null, 8, _hoisted_4$1), createBaseVNode("div", {
			class: normalizeClass(["text-sm font-medium inline p-1", $setup.dateNumberClasses(date)]),
			textContent: toDisplayString(date.day)
		}, null, 10, _hoisted_5$1)], 10, _hoisted_3$1);
	}), 128))]), createBaseVNode("div", _hoisted_6$1, [createBaseVNode("div", _hoisted_7$1, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.visibleHours, (hour) => {
		return openBlock(), createElementBlock("div", {
			key: hour,
			class: "h-12 border-b border-gray-200 dark:border-gray-700 flex items-start justify-end pr-2 pt-1"
		}, [createBaseVNode("span", {
			class: "text-xs text-gray-500 dark:text-gray-400",
			textContent: toDisplayString($setup.getHourLabel(hour))
		}, null, 8, _hoisted_8$1)]);
	}), 128))]), (openBlock(true), createElementBlock(Fragment, null, renderList($props.weekDates, (date) => {
		return openBlock(), createElementBlock("div", {
			key: date.toString(),
			class: "bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700"
		}, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.visibleHours, (hour) => {
			return openBlock(), createElementBlock("div", {
				key: hour,
				class: normalizeClass(["h-12 border-b border-gray-200 dark:border-gray-700 relative group", $setup.hourCellClasses(date, hour)]),
				onClick: ($event) => $setup.selectDate(date)
			}, [createBaseVNode("div", _hoisted_10, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.getEntriesForHour(date, hour), (entry) => {
				return openBlock(), createBlock($setup["CalendarEntry"], {
					key: entry.id,
					entry
				}, null, 8, ["entry"]);
			}), 128))]), $setup.getEntriesForHour(date, hour).length === 0 ? (openBlock(), createElementBlock("div", _hoisted_11, [createVNode($setup["CreateEntryButton"], {
				params: { values: { date: $setup.getCreateUrlDateParam(date, hour) } },
				blueprints: $props.blueprints,
				variant: "subtle",
				size: "sm"
			}, {
				trigger: withCtx(({ create }) => [createVNode($setup["Button"], {
					icon: "plus",
					size: "sm",
					variant: "subtle",
					onClick: create
				}, null, 8, ["onClick"])]),
				_: 1
			}, 8, ["params", "blueprints"])])) : createCommentVNode("", true)], 10, _hoisted_9$1);
		}), 128))]);
	}), 128))], 512)]);
}
var Week_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "Week.vue"]]);
//#endregion
//#region resources/js/components/entries/calendar/Calendar.vue
var _sfc_main$1 = {
	__name: "Calendar",
	props: {
		collection: {
			type: String,
			required: true
		},
		blueprints: {
			type: Array,
			default: () => []
		},
		createUrl: {
			type: String,
			required: true
		},
		site: {
			type: String,
			required: true
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const $date = new DateFormatter();
		const currentDate = ref(new $35ea8db9cb2ccb90$export$99faa760c7908e4f((/* @__PURE__ */ new Date()).getFullYear(), (/* @__PURE__ */ new Date()).getMonth() + 1, (/* @__PURE__ */ new Date()).getDate()));
		const selectedDate = ref(null);
		const selectedDateTime = computed(() => $11d87f3f76e88657$export$b21e0b124e224484(selectedDate.value));
		const entries = ref([]);
		const loading = ref(false);
		const viewModePreferenceKey = `collections.${props.collection}.calendar.view`;
		const viewMode = ref(preferences.get(viewModePreferenceKey, "month"));
		watch(viewMode, (viewMode) => preferences.set(viewModePreferenceKey, viewMode));
		async function fetchEntries() {
			loading.value = true;
			try {
				const { startDate, endDate } = getCurrentDateRange(currentDate.value, viewMode.value);
				const response = await axios.get(cp_url(`collections/${props.collection}/entries`), { params: {
					filters: utf8btoa(JSON.stringify({
						site: { site: props.site },
						fields: { date: {
							operator: "between",
							range_value: {
								start: startDate.toISOString(),
								end: endDate.toISOString()
							}
						} }
					})),
					per_page: 1e3
				} });
				entries.value = Object.values(response.data.data);
			} catch (err) {
				console.error("Failed to fetch entries:", err);
				Statamic.$toast.error(__("Failed to load entries"));
			} finally {
				loading.value = false;
			}
		}
		function goToToday() {
			const today = /* @__PURE__ */ new Date();
			currentDate.value = new $35ea8db9cb2ccb90$export$99faa760c7908e4f(today.getFullYear(), today.getMonth() + 1, today.getDate());
		}
		function goToPreviousPeriod() {
			const period = viewMode.value === "week" ? "weeks" : "months";
			currentDate.value = currentDate.value.subtract({ [period]: 1 });
		}
		function goToNextPeriod() {
			const period = viewMode.value === "week" ? "weeks" : "months";
			currentDate.value = currentDate.value.add({ [period]: 1 });
		}
		function selectDate(date) {
			selectedDate.value = date;
		}
		function handleMonthChange(newMonth) {
			currentDate.value = new $35ea8db9cb2ccb90$export$99faa760c7908e4f(currentDate.value.year, newMonth, currentDate.value.day);
		}
		function handleYearChange(newYear) {
			currentDate.value = new $35ea8db9cb2ccb90$export$99faa760c7908e4f(newYear, currentDate.value.month, currentDate.value.day);
		}
		const monthOptions = computed(() => {
			const months = [];
			for (let i = 1; i <= 12; i++) {
				const date = new Date(2024, i - 1, 1);
				months.push({
					value: i,
					label: $date.format(date, { month: "long" })
				});
			}
			return months;
		});
		const yearOptions = computed(() => {
			const currentYear = (/* @__PURE__ */ new Date()).getFullYear();
			const years = [];
			for (let i = currentYear - 10; i <= currentYear + 10; i++) years.push({
				value: i,
				label: i.toString()
			});
			return years;
		});
		const selectedDateEntries = computed(() => {
			if (!selectedDate.value) return [];
			const dateStr = selectedDate.value.toString();
			return entries.value.filter((entry) => {
				return new Date(entry.date?.date || entry.date).toISOString().split("T")[0] === dateStr;
			});
		});
		const columns = computed(() => [{
			label: __("Title"),
			field: "title",
			visible: true
		}, {
			label: __("Status"),
			field: "status",
			visible: true
		}]);
		watch(() => props.site, () => fetchEntries());
		watch(() => [
			currentDate.value.year,
			currentDate.value.month,
			currentDate.value.day,
			viewMode.value
		], (newValue, oldValue) => {
			if (!oldValue || shouldFetchEntries(newValue, oldValue)) fetchEntries();
		}, {
			deep: true,
			immediate: true
		});
		function shouldFetchEntries([year, month, day, view], [oldYear, oldMonth, oldDay, oldView]) {
			if (view !== oldView) return true;
			if (view === "month" && (year !== oldYear || month !== oldMonth)) return true;
			if (view === "week") {
				const newDate = new $35ea8db9cb2ccb90$export$99faa760c7908e4f(year, month, day);
				const oldDate = new $35ea8db9cb2ccb90$export$99faa760c7908e4f(oldYear, oldMonth, oldDay);
				const newWeekStart = getWeekDates(newDate)[0];
				const oldWeekStart = getWeekDates(oldDate)[0];
				return newWeekStart.toString() !== oldWeekStart.toString();
			}
			return false;
		}
		const __returned__ = {
			props,
			$date,
			currentDate,
			selectedDate,
			selectedDateTime,
			entries,
			loading,
			viewModePreferenceKey,
			viewMode,
			fetchEntries,
			goToToday,
			goToPreviousPeriod,
			goToNextPeriod,
			selectDate,
			handleMonthChange,
			handleYearChange,
			monthOptions,
			yearOptions,
			selectedDateEntries,
			columns,
			shouldFetchEntries,
			ref,
			watch,
			computed,
			getCurrentInstance,
			get axios() {
				return axios;
			},
			get CalendarHeader() {
				return CalendarHeader_default;
			},
			get CalendarHeading() {
				return CalendarHeading_default;
			},
			get CalendarRoot() {
				return CalendarRoot_default;
			},
			get CalendarDate() {
				return $35ea8db9cb2ccb90$export$99faa760c7908e4f;
			},
			get toCalendarDateTime() {
				return $11d87f3f76e88657$export$b21e0b124e224484;
			},
			Month: Month_default,
			Week: Week_default,
			get Listing() {
				return Listing_default;
			},
			get StatusIndicator() {
				return StatusIndicator_default;
			},
			get DateFormatter() {
				return DateFormatter;
			},
			get getWeekDates() {
				return getWeekDates;
			},
			get getCurrentDateRange() {
				return getCurrentDateRange;
			},
			get Link() {
				return link_default;
			},
			get ToggleGroup() {
				return Group_default$1;
			},
			get ToggleItem() {
				return Item_default;
			},
			get Button() {
				return Button_default;
			},
			get Popover() {
				return Popover_default;
			},
			get Label() {
				return Label_default;
			},
			get Select() {
				return Select_default;
			},
			get Heading() {
				return Heading_default;
			},
			get preferences() {
				return preferences;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "@container" };
var _hoisted_2 = { class: "flex items-center justify-between w-full @3xl:flex-1 @3xl:justify-start" };
var _hoisted_3 = { class: "flex items-center gap-2 @3xl:hidden" };
var _hoisted_4 = { class: "@3xl:flex-1 px-2 text-center" };
var _hoisted_5 = { class: "flex items-center gap-3" };
var _hoisted_6 = { class: "space-y-2" };
var _hoisted_7 = { class: "space-y-2" };
var _hoisted_8 = { class: "hidden @3xl:flex @3xl:flex-1 items-center gap-2 w-1/4 justify-end" };
var _hoisted_9 = {
	key: 0,
	class: "mt-6"
};
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["CalendarRoot"], {
		modelValue: $setup.currentDate,
		"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.currentDate = $event),
		locale: $setup.$date.locale,
		"fixed-weeks": "",
		"weekday-format": "long",
		class: "bg-gray-100 dark:bg-gray-800 rounded-2xl p-3"
	}, {
		default: withCtx(({ weekDays, grid }) => [createVNode($setup["CalendarHeader"], { class: "flex flex-col @3xl:flex-row items-center gap-4 pb-4 @3xl:pb-8" }, {
			default: withCtx(() => [
				createBaseVNode("div", _hoisted_2, [createVNode($setup["ToggleGroup"], {
					modelValue: $setup.viewMode,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.viewMode = $event),
					class: "flex"
				}, {
					default: withCtx(() => [createVNode($setup["ToggleItem"], {
						value: "week",
						label: _ctx.__("Week")
					}, null, 8, ["label"]), createVNode($setup["ToggleItem"], {
						value: "month",
						label: _ctx.__("Month")
					}, null, 8, ["label"])]),
					_: 1
				}, 8, ["modelValue"]), createBaseVNode("div", _hoisted_3, [
					createVNode($setup["Button"], {
						icon: "chevron-left",
						onClick: $setup.goToPreviousPeriod
					}),
					createVNode($setup["Button"], {
						onClick: $setup.goToToday,
						text: _ctx.__("Today")
					}, null, 8, ["text"]),
					createVNode($setup["Button"], {
						icon: "chevron-right",
						onClick: $setup.goToNextPeriod
					})
				])]),
				createBaseVNode("div", _hoisted_4, [createVNode($setup["Popover"], {
					class: "w-full",
					arrow: ""
				}, {
					trigger: withCtx(() => [createBaseVNode("button", null, [createVNode($setup["CalendarHeading"], { class: "text-2xl font-medium text-gray-800 dark:text-white cursor-pointer hover:text-gray-600 dark:hover:text-gray-300 transition-colors" })])]),
					default: withCtx(() => [createBaseVNode("div", _hoisted_5, [createBaseVNode("div", _hoisted_6, [createVNode($setup["Label"], {
						for: "month",
						text: _ctx.__("Month")
					}, null, 8, ["text"]), createVNode($setup["Select"], {
						"model-value": $setup.currentDate.month,
						options: $setup.monthOptions,
						"option-value": "value",
						"option-label": "label",
						"onUpdate:modelValue": $setup.handleMonthChange
					}, null, 8, ["model-value", "options"])]), createBaseVNode("div", _hoisted_7, [createVNode($setup["Label"], {
						for: "month",
						text: _ctx.__("Year")
					}, null, 8, ["text"]), createVNode($setup["Select"], {
						"model-value": $setup.currentDate.year,
						options: $setup.yearOptions,
						"option-value": "value",
						"option-label": "label",
						"onUpdate:modelValue": $setup.handleYearChange
					}, null, 8, ["model-value", "options"])])])]),
					_: 1
				})]),
				createBaseVNode("div", _hoisted_8, [
					createVNode($setup["Button"], {
						icon: "chevron-left",
						onClick: $setup.goToPreviousPeriod
					}),
					createVNode($setup["Button"], {
						onClick: $setup.goToToday,
						text: _ctx.__("Today")
					}, null, 8, ["text"]),
					createVNode($setup["Button"], {
						icon: "chevron-right",
						onClick: $setup.goToNextPeriod
					})
				])
			]),
			_: 1
		}), $setup.viewMode === "month" ? (openBlock(), createBlock($setup["Month"], {
			key: 0,
			"week-days": weekDays,
			grid,
			entries: $setup.entries,
			"selected-date": $setup.selectedDate,
			"create-url": $props.createUrl,
			blueprints: $props.blueprints,
			onSelectDate: $setup.selectDate
		}, null, 8, [
			"week-days",
			"grid",
			"entries",
			"selected-date",
			"create-url",
			"blueprints"
		])) : (openBlock(), createBlock($setup["Week"], {
			key: 1,
			"week-dates": $setup.getWeekDates($setup.currentDate),
			entries: $setup.entries,
			"selected-date": $setup.selectedDate,
			"create-url": $props.createUrl,
			blueprints: $props.blueprints,
			onSelectDate: $setup.selectDate
		}, null, 8, [
			"week-dates",
			"entries",
			"selected-date",
			"create-url",
			"blueprints"
		]))]),
		_: 1
	}, 8, ["modelValue", "locale"]), $setup.selectedDate ? (openBlock(), createElementBlock("div", _hoisted_9, [createVNode($setup["Heading"], {
		size: "lg",
		class: "flex justify-center pb-4",
		text: $setup.$date.format($setup.selectedDateTime, {
			weekday: "long",
			year: "numeric",
			month: "long",
			day: "numeric"
		})
	}, null, 8, ["text"]), createVNode($setup["Listing"], {
		items: $setup.selectedDateEntries,
		columns: $setup.columns,
		"allow-search": false,
		"allow-customizing-columns": false,
		"show-pagination-totals": false,
		"show-pagination-page-links": false,
		"show-pagination-per-page-selector": false
	}, {
		"cell-title": withCtx(({ row: entry }) => [createVNode($setup["Link"], {
			href: entry.edit_url,
			textContent: toDisplayString(entry.title)
		}, null, 8, ["href", "textContent"])]),
		"cell-status": withCtx(({ row: entry }) => [createVNode($setup["StatusIndicator"], {
			status: entry.status,
			"show-dot": false,
			"show-label": ""
		}, null, 8, ["status"])]),
		_: 1
	}, 8, ["items", "columns"])])) : createCommentVNode("", true)]);
}
var Calendar_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "Calendar.vue"]]);
//#endregion
//#region resources/js/pages/collections/Show.vue
var _sfc_main = {
	components: {
		Head: Head_default,
		DropdownSeparator: Separator_default,
		DropdownItem: Item_default$1,
		DropdownLabel: Label_default$1,
		DropdownMenu: Menu_default,
		ItemActions: ItemActions_default,
		Dropdown: Dropdown_default,
		Header: Header_default,
		Button: Button_default,
		ToggleGroup: Group_default$1,
		ToggleItem: Item_default,
		PageTree: defineAsyncComponent(() => __vitePreload(() => import("./PageTree-BsFaz_Gy.js"), __vite__mapDeps([0,1,2,3,4,5,6,7,8,9,10]), import.meta.url)),
		DeleteEntryConfirmation: DeleteEntryConfirmation_default,
		DeleteLocalizationConfirmation: DeleteLocalizationConfirmation_default,
		SiteSelector: SiteSelector_default,
		CollectionCalendar: Calendar_default
	},
	props: {
		title: {
			type: String,
			required: true
		},
		handle: {
			type: String,
			required: true
		},
		icon: {
			type: String,
			required: true
		},
		canCreate: {
			type: Boolean,
			required: true
		},
		createUrls: {
			type: Object,
			required: true
		},
		createLabel: {
			type: String,
			required: true
		},
		blueprints: {
			type: Array,
			required: true
		},
		structured: {
			type: Boolean,
			default: false
		},
		dated: {
			type: Boolean,
			default: false
		},
		sortColumn: {
			type: String,
			required: true
		},
		sortDirection: {
			type: String,
			required: true
		},
		columns: {
			type: Array,
			required: true
		},
		filters: {
			type: Array,
			required: true
		},
		actions: {
			type: Array,
			required: true
		},
		actionUrl: {
			type: String,
			required: true
		},
		entriesActionUrl: {
			type: String,
			required: true
		},
		reorderUrl: {
			type: String,
			required: true
		},
		editUrl: {
			type: String,
			required: true
		},
		blueprintsUrl: {
			type: String,
			required: true
		},
		scaffoldUrl: {
			type: String,
			required: true
		},
		canEdit: {
			type: Boolean,
			required: true
		},
		canEditBlueprints: {
			type: Boolean,
			required: true
		},
		initialSite: {
			type: String,
			required: true
		},
		sites: { type: Array },
		totalSitesCount: { type: Number },
		canChangeLocalizationDeleteBehavior: { type: Boolean },
		structurePagesUrl: { type: String },
		structureSubmitUrl: { type: String },
		structureMaxDepth: {
			type: Number,
			default: Infinity
		},
		structureExpectsRoot: { type: Boolean },
		structureShowSlugs: { type: Boolean }
	},
	data() {
		return {
			mounted: false,
			view: null,
			deletedEntries: [],
			showEntryDeletionConfirmation: false,
			entryBeingDeleted: null,
			entryDeletionConfirmCallback: null,
			deleteLocalizationBehavior: null,
			showLocalizationDeleteBehaviorConfirmation: false,
			localizationDeleteBehaviorConfirmCallback: null,
			site: this.initialSite,
			reordering: false,
			preferencesPrefix: `collections.${this.handle}`
		};
	},
	computed: {
		treeIsDirty() {
			return this.$dirty.has("page-tree");
		},
		canUseStructureTree() {
			return this.structured && this.structureMaxDepth !== 1;
		},
		canUseCalendar() {
			return this.dated;
		},
		reorderable() {
			return this.structured && this.structureMaxDepth === 1;
		},
		numberOfChildrenToBeDeleted() {
			let children = 0;
			const countChildren = (entry) => {
				entry.children.forEach((child) => {
					children++;
					countChildren(child);
				});
			};
			countChildren(this.entryBeingDeleted);
			return children;
		},
		createUrl() {
			return this.createUrls[this.site || this.initialSite];
		}
	},
	watch: { view(view) {
		this.site = this.site || this.initialSite;
		this.$preferences.set(`collections.${this.handle}.view`, view);
	} },
	mounted() {
		this.view = this.initialView();
		this.mounted = true;
		this.addToCommandPalette();
	},
	methods: {
		cancelTreeProgress() {
			this.$refs.tree.cancel();
			this.deletedEntries = [];
		},
		saveTree() {
			if (this.deletedEntries.length === 0) {
				this.performTreeSaving();
				return;
			}
			if (!this.canChangeLocalizationDeleteBehavior) {
				this.deleteLocalizationBehavior = "copy";
				this.$nextTick(() => this.performTreeSaving());
				return;
			}
			this.showLocalizationDeleteBehaviorConfirmation = true;
			this.localizationDeleteBehaviorConfirmCallback = (behavior) => {
				this.deleteLocalizationBehavior = behavior;
				this.showLocalizationDeleteBehaviorConfirmation = false;
				this.$nextTick(() => this.performTreeSaving());
			};
		},
		performTreeSaving() {
			this.$refs.tree.save().then(() => this.deletedEntries = []).catch(() => {});
		},
		markTreeDirty() {
			this.$dirty.add("page-tree");
		},
		markTreeClean() {
			this.$dirty.remove("page-tree");
		},
		initialView() {
			const savedView = this.$preferences.get(`collections.${this.handle}.view`);
			if (savedView) {
				if (savedView === "tree" && this.canUseStructureTree) return "tree";
				if (savedView === "calendar" && this.canUseCalendar) return "calendar";
				if (savedView === "list") return "list";
			}
			return this.canUseStructureTree ? "tree" : "list";
		},
		deleteTreeBranch(branch, removeFromUi) {
			this.showEntryDeletionConfirmation = true;
			this.entryBeingDeleted = branch;
			this.entryDeletionConfirmCallback = (shouldDeleteChildren) => {
				this.deletedEntries.push(branch.id);
				if (shouldDeleteChildren) this.markEntriesForDeletion(branch);
				removeFromUi(shouldDeleteChildren);
				this.showEntryDeletionConfirmation = false;
				this.entryBeingDeleted = false;
			};
		},
		markEntriesForDeletion(branch) {
			const addDeletableChildren = (branch) => {
				branch.children.forEach((child) => {
					this.deletedEntries.push(child.id);
					addDeletableChildren(child);
				});
			};
			addDeletableChildren(branch);
		},
		isRedirectBranch(branch) {
			return branch.redirect != null;
		},
		createEntry(blueprint, parent) {
			let url = `${this.createUrl}?blueprint=${blueprint}`;
			if (parent) url += "&parent=" + parent;
			router.get(url);
		},
		editPage(page, $event) {
			const url = page.edit_url;
			$event.metaKey ? window.open(url) : router.get(url);
		},
		afterActionSuccessfullyCompleted(response) {
			if (!response.redirect) router.reload();
		},
		addToCommandPalette() {
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: [__("Collection"), __("Configure")],
				icon: "cog",
				url: this.editUrl
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: [__("Collection"), __("Edit Blueprints")],
				icon: "blueprint-edit",
				url: this.blueprintsUrl
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: [__("Collection"), __("Scaffold Views")],
				icon: "scaffold",
				url: this.scaffoldUrl
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Switch to List Layout"),
				icon: "layout-list",
				when: () => this.view !== "list",
				action: () => this.view = "list"
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Switch to Calendar Layout"),
				icon: "calendar",
				when: () => this.canUseCalendar && this.view !== "calendar",
				action: () => this.view = "calendar"
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Switch to Tree Layout"),
				icon: "navigation",
				when: () => this.canUseStructureTree && this.view !== "tree",
				action: () => this.view = "tree"
			});
			this.$refs.actions?.preparedActions.forEach((action) => Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: [__("Collection"), action.title],
				icon: action.icon,
				action: action.run
			}));
		}
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_site_selector = resolveComponent("site-selector");
	const _component_Button = resolveComponent("Button");
	const _component_ui_toggle_item = resolveComponent("ui-toggle-item");
	const _component_ui_toggle_group = resolveComponent("ui-toggle-group");
	const _component_create_entry_button = resolveComponent("create-entry-button");
	const _component_Header = resolveComponent("Header");
	const _component_entry_list = resolveComponent("entry-list");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_DropdownLabel = resolveComponent("DropdownLabel");
	const _component_page_tree = resolveComponent("page-tree");
	const _component_CollectionCalendar = resolveComponent("CollectionCalendar");
	const _component_delete_entry_confirmation = resolveComponent("delete-entry-confirmation");
	const _component_delete_localization_confirmation = resolveComponent("delete-localization-confirmation");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", null, [
		createVNode(_component_Head, { title: [_ctx.__($props.title), _ctx.__("Collections")] }, null, 8, ["title"]),
		createVNode(_component_Header, {
			title: _ctx.__($props.title),
			icon: $props.icon
		}, {
			default: withCtx(() => [
				createVNode(_component_ItemActions, {
					ref: "actions",
					url: $props.actionUrl,
					actions: $props.actions,
					item: $props.handle
				}, {
					default: withCtx(({ actions }) => [$props.canEdit || $props.canEditBlueprints || actions.length ? (openBlock(), createBlock(_component_Dropdown, {
						key: 0,
						placement: "left-start"
					}, {
						default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
							default: withCtx(() => [
								$props.canEdit ? (openBlock(), createBlock(_component_DropdownItem, {
									key: 0,
									text: _ctx.__("Configure Collection"),
									icon: "cog",
									href: $props.editUrl
								}, null, 8, ["text", "href"])) : createCommentVNode("", true),
								$props.canEditBlueprints ? (openBlock(), createBlock(_component_DropdownItem, {
									key: 1,
									text: _ctx.__("Edit Blueprints"),
									icon: "blueprint-edit",
									href: $props.blueprintsUrl
								}, null, 8, ["text", "href"])) : createCommentVNode("", true),
								$props.canEdit ? (openBlock(), createBlock(_component_DropdownItem, {
									key: 2,
									text: _ctx.__("Scaffold Views"),
									icon: "scaffold",
									href: $props.scaffoldUrl
								}, null, 8, ["text", "href"])) : createCommentVNode("", true),
								$props.canEdit || $props.canEditBlueprints || actions.length ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 3 })) : createCommentVNode("", true),
								(openBlock(true), createElementBlock(Fragment, null, renderList(actions, (action) => {
									return openBlock(), createBlock(_component_DropdownItem, {
										key: action.handle,
										text: _ctx.__(action.title),
										icon: action.icon,
										variant: action.dangerous ? "destructive" : "default",
										onClick: action.run
									}, null, 8, [
										"text",
										"icon",
										"variant",
										"onClick"
									]);
								}), 128))
							]),
							_: 2
						}, 1024)]),
						_: 2
					}, 1024)) : createCommentVNode("", true)]),
					_: 1
				}, 8, [
					"url",
					"actions",
					"item"
				]),
				$data.view === "tree" ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [
					$options.treeIsDirty ? (openBlock(), createBlock(_component_ui_button, {
						key: 0,
						variant: "filled",
						text: _ctx.__("Discard Changes"),
						onClick: $options.cancelTreeProgress
					}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
					$props.sites.length > 1 ? (openBlock(), createBlock(_component_site_selector, {
						key: 1,
						sites: $props.sites,
						modelValue: $data.site,
						"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.site = $event)
					}, null, 8, ["sites", "modelValue"])) : createCommentVNode("", true),
					$options.treeIsDirty ? withDirectives((openBlock(), createBlock(_component_Button, {
						key: 2,
						disabled: !$options.treeIsDirty,
						text: _ctx.__("Save Changes"),
						variant: $data.deletedEntries.length ? "danger" : "default",
						onClick: $options.saveTree
					}, null, 8, [
						"disabled",
						"text",
						"variant",
						"onClick"
					])), [[_directive_tooltip, $data.deletedEntries.length ? _ctx.__n("An entry will be deleted|:count entries will be deleted", $data.deletedEntries.length) : null]]) : createCommentVNode("", true)
				], 64)) : createCommentVNode("", true),
				$data.view === "list" && $options.reorderable ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [
					$props.sites.length > 1 && $data.reordering && $data.site ? (openBlock(), createBlock(_component_site_selector, {
						key: 0,
						sites: $props.sites,
						modelValue: $data.site,
						"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.site = $event)
					}, null, 8, ["sites", "modelValue"])) : createCommentVNode("", true),
					!$data.reordering ? (openBlock(), createBlock(_component_Button, {
						key: 1,
						onClick: _cache[2] || (_cache[2] = ($event) => $data.reordering = true),
						text: _ctx.__("Reorder")
					}, null, 8, ["text"])) : createCommentVNode("", true),
					$data.reordering ? (openBlock(), createElementBlock(Fragment, { key: 2 }, [createVNode(_component_Button, {
						onClick: _cache[3] || (_cache[3] = ($event) => $data.reordering = false),
						text: _ctx.__("Cancel")
					}, null, 8, ["text"]), createVNode(_component_Button, {
						onClick: _ctx.$refs.list.saveOrder,
						text: _ctx.__("Save Order"),
						variant: "primary"
					}, null, 8, ["onClick", "text"])], 64)) : createCommentVNode("", true)
				], 64)) : createCommentVNode("", true),
				$data.view == "calendar" ? (openBlock(), createElementBlock(Fragment, { key: 2 }, [$props.sites.length > 1 ? (openBlock(), createBlock(_component_site_selector, {
					key: 0,
					sites: $props.sites,
					modelValue: $data.site,
					"onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => $data.site = $event)
				}, null, 8, ["sites", "modelValue"])) : createCommentVNode("", true)], 64)) : createCommentVNode("", true),
				$options.canUseStructureTree || $options.canUseCalendar ? (openBlock(), createBlock(_component_ui_toggle_group, {
					key: 3,
					modelValue: $data.view,
					"onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => $data.view = $event)
				}, {
					default: withCtx(() => [
						$options.canUseStructureTree ? (openBlock(), createBlock(_component_ui_toggle_item, {
							key: 0,
							icon: "navigation",
							value: "tree"
						})) : createCommentVNode("", true),
						createVNode(_component_ui_toggle_item, {
							icon: "layout-list",
							value: "list"
						}),
						$options.canUseCalendar ? (openBlock(), createBlock(_component_ui_toggle_item, {
							key: 1,
							icon: "calendar",
							value: "calendar"
						})) : createCommentVNode("", true)
					]),
					_: 1
				}, 8, ["modelValue"])) : createCommentVNode("", true),
				!$data.reordering && $props.canCreate ? (openBlock(), createBlock(_component_create_entry_button, {
					key: 4,
					blueprints: $props.blueprints,
					text: $props.createLabel,
					"command-palette": true
				}, null, 8, ["blueprints", "text"])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["title", "icon"]),
		$data.view === "list" ? (openBlock(), createBlock(_component_entry_list, {
			key: 0,
			ref: "list",
			collection: $props.handle,
			"sort-column": $props.sortColumn,
			"sort-direction": $props.sortDirection,
			columns: $props.columns,
			filters: $props.filters,
			"action-url": $props.entriesActionUrl,
			reordering: $data.reordering,
			"reorder-url": $props.reorderUrl,
			site: $data.site,
			onReordered: _cache[6] || (_cache[6] = ($event) => $data.reordering = false),
			onSiteChanged: _cache[7] || (_cache[7] = ($event) => $data.site = $event)
		}, null, 8, [
			"collection",
			"sort-column",
			"sort-direction",
			"columns",
			"filters",
			"action-url",
			"reordering",
			"reorder-url",
			"site"
		])) : createCommentVNode("", true),
		$options.canUseStructureTree && $data.view === "tree" ? (openBlock(), createBlock(_component_page_tree, {
			key: 1,
			ref: "tree",
			collections: [$props.handle],
			blueprints: $props.blueprints,
			"create-url": $options.createUrl,
			"pages-url": $props.structurePagesUrl,
			"submit-url": $props.structureSubmitUrl,
			"submit-parameters": {
				deletedEntries: $data.deletedEntries,
				deleteLocalizationBehavior: $data.deleteLocalizationBehavior
			},
			"max-depth": $props.structureMaxDepth,
			"expects-root": $props.structureExpectsRoot,
			"show-slugs": $props.structureShowSlugs,
			site: $data.site,
			"preferences-prefix": $data.preferencesPrefix,
			onEditPage: $options.editPage,
			onChanged: $options.markTreeDirty,
			onSaved: $options.markTreeClean
		}, {
			"branch-icon": withCtx(({ branch }) => [$options.isRedirectBranch(branch) ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
				key: 0,
				name: "external-link"
			}, null, 512)), [[_directive_tooltip, _ctx.__("Redirect")]]) : createCommentVNode("", true)]),
			"branch-options": withCtx(({ branch, removeBranch, depth }) => [
				depth < $props.structureMaxDepth ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [$props.blueprints.length > 1 ? (openBlock(), createBlock(_component_DropdownLabel, {
					key: 0,
					text: _ctx.__("Create Child Entry")
				}, null, 8, ["text"])) : createCommentVNode("", true), (openBlock(true), createElementBlock(Fragment, null, renderList($props.blueprints, (blueprint) => {
					return openBlock(), createBlock(_component_DropdownItem, {
						onClick: ($event) => $options.createEntry(blueprint.handle, branch.id),
						icon: blueprint.icon || "add-entry",
						key: blueprint.handle,
						text: $props.blueprints.length > 1 ? _ctx.__(blueprint.title) : _ctx.__("Create Child Entry")
					}, null, 8, [
						"onClick",
						"icon",
						"text"
					]);
				}), 128))], 64)) : createCommentVNode("", true),
				depth < $props.structureMaxDepth && branch.can_delete ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 1 })) : createCommentVNode("", true),
				branch.can_delete ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 2,
					text: _ctx.__("Delete"),
					icon: "trash",
					variant: "destructive",
					onClick: ($event) => $options.deleteTreeBranch(branch, removeBranch)
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, [
			"collections",
			"blueprints",
			"create-url",
			"pages-url",
			"submit-url",
			"submit-parameters",
			"max-depth",
			"expects-root",
			"show-slugs",
			"site",
			"preferences-prefix",
			"onEditPage",
			"onChanged",
			"onSaved"
		])) : createCommentVNode("", true),
		$data.view === "calendar" ? (openBlock(), createBlock(_component_CollectionCalendar, {
			key: 2,
			ref: "calendar",
			collection: $props.handle,
			blueprints: $props.blueprints,
			"create-url": $options.createUrl,
			site: $data.site
		}, null, 8, [
			"collection",
			"blueprints",
			"create-url",
			"site"
		])) : createCommentVNode("", true),
		$data.showEntryDeletionConfirmation ? (openBlock(), createBlock(_component_delete_entry_confirmation, {
			key: 3,
			children: $options.numberOfChildrenToBeDeleted,
			onConfirm: $data.entryDeletionConfirmCallback,
			onCancel: _cache[8] || (_cache[8] = ($event) => {
				$data.showEntryDeletionConfirmation = false;
				$data.entryBeingDeleted = null;
			})
		}, null, 8, ["children", "onConfirm"])) : createCommentVNode("", true),
		$data.showLocalizationDeleteBehaviorConfirmation ? (openBlock(), createBlock(_component_delete_localization_confirmation, {
			key: 4,
			entries: $data.deletedEntries.length,
			onConfirm: $data.localizationDeleteBehaviorConfirmCallback,
			onCancel: _cache[9] || (_cache[9] = ($event) => $data.showLocalizationDeleteBehaviorConfirmation = false)
		}, null, 8, ["entries", "onConfirm"])) : createCommentVNode("", true)
	]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
