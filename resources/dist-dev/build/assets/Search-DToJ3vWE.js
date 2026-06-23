import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { Hn as Panel_default, Jt as ErrorMessage_default, Ut as Header_default, ct as Rows_default, dt as Column_default, ft as Cell_default, li as Button_default, lt as Row_default, n as DocsCallout_default, pt as Table_default, ui as Badge_default, ut as Columns_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/Search.vue
var _sfc_main = {
	__name: "Search",
	props: [
		"indexes",
		"updateUrl",
		"errors"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		function updateAll() {
			const indexes = props.indexes.map((index) => `${index.name}::${index.locale}`);
			router.post(props.updateUrl, { indexes });
		}
		function updateIndex(index) {
			const indexes = [`${index.name}::${index.locale}`];
			router.post(props.updateUrl, { indexes });
		}
		const __returned__ = {
			props,
			updateAll,
			updateIndex,
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get CardPanel() {
				return Panel_default;
			},
			get Table() {
				return Table_default;
			},
			get TableColumns() {
				return Columns_default;
			},
			get TableColumn() {
				return Column_default;
			},
			get TableRows() {
				return Rows_default;
			},
			get TableRow() {
				return Row_default;
			},
			get TableCell() {
				return Cell_default;
			},
			get Badge() {
				return Badge_default;
			},
			get ErrorMessage() {
				return ErrorMessage_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get router() {
				return router;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "max-w-page mx-auto" };
var _hoisted_2 = { class: "flex items-start" };
var _hoisted_3 = ["innerHTML"];
var _hoisted_4 = ["textContent"];
var _hoisted_5 = {
	key: 0,
	class: "flex flex-wrap"
};
var _hoisted_6 = {
	key: 1,
	class: "flex flex-wrap gap-1 text-sm text-gray"
};
var _hoisted_7 = { class: "flex flex-wrap gap-2" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: [_ctx.__("Search"), _ctx.__("Utilities")] }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Search"),
			icon: "magnifying-glass"
		}, {
			default: withCtx(() => [createVNode($setup["Button"], {
				variant: "primary",
				onClick: $setup.updateAll
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Update Indexes")), 1)]),
				_: 1
			})]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["CardPanel"], { heading: _ctx.__("Search Indexes") }, {
			default: withCtx(() => [$props.errors.indexes ? (openBlock(), createBlock($setup["ErrorMessage"], {
				key: 0,
				text: $props.errors.indexes[0],
				class: "p-4"
			}, null, 8, ["text"])) : createCommentVNode("", true), createVNode($setup["Table"], null, {
				default: withCtx(() => [createVNode($setup["TableColumns"], null, {
					default: withCtx(() => [
						createVNode($setup["TableColumn"], { textContent: toDisplayString(_ctx.__("Index")) }, null, 8, ["textContent"]),
						createVNode($setup["TableColumn"], { textContent: toDisplayString(_ctx.__("Driver")) }, null, 8, ["textContent"]),
						createVNode($setup["TableColumn"], { textContent: toDisplayString(_ctx.__("Searchables")) }, null, 8, ["textContent"]),
						createVNode($setup["TableColumn"], { textContent: toDisplayString(_ctx.__("Fields")) }, null, 8, ["textContent"]),
						createVNode($setup["TableColumn"])
					]),
					_: 1
				}), createVNode($setup["TableRows"], null, {
					default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.indexes, (index) => {
						return openBlock(), createBlock($setup["TableRow"], { key: `${index.name}::${index.locale}` }, {
							default: withCtx(() => [
								createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createBaseVNode("div", _hoisted_2, [createBaseVNode("div", {
										class: "-mt-0.5 flex size-6 shrink-0 me-2 text-gray-925 dark:text-gray-200 [&_.icon-background]:fill-gray-100 dark:[&_.icon-background]:fill-gray-900",
										innerHTML: index.driverIcon
									}, null, 8, _hoisted_3), createBaseVNode("span", {
										class: "text-gray-900 dark:text-gray-200",
										textContent: toDisplayString(index.title)
									}, null, 8, _hoisted_4)])]),
									_: 2
								}, 1024),
								createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(index.driver.charAt(0).toUpperCase() + index.driver.slice(1)), 1)]),
									_: 2
								}, 1024),
								createVNode($setup["TableCell"], null, {
									default: withCtx(() => [typeof index.searchables === "string" ? (openBlock(), createElementBlock("div", _hoisted_5, [createVNode($setup["Badge"], { textContent: toDisplayString(index.searchables) }, null, 8, ["textContent"])])) : (openBlock(), createElementBlock("div", _hoisted_6, [(openBlock(true), createElementBlock(Fragment, null, renderList(index.searchables, (searchable) => {
										return openBlock(), createBlock($setup["Badge"], {
											key: searchable,
											textContent: toDisplayString(searchable)
										}, null, 8, ["textContent"]);
									}), 128))]))]),
									_: 2
								}, 1024),
								createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createBaseVNode("div", _hoisted_7, [(openBlock(true), createElementBlock(Fragment, null, renderList(index.fields, (field) => {
										return openBlock(), createBlock($setup["Badge"], {
											key: field,
											textContent: toDisplayString(field)
										}, null, 8, ["textContent"]);
									}), 128))])]),
									_: 2
								}, 1024),
								createVNode($setup["TableCell"], { class: "text-right rtl:text-left" }, {
									default: withCtx(() => [createVNode($setup["Button"], {
										size: "sm",
										onClick: ($event) => $setup.updateIndex(index)
									}, {
										default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Update")), 1)]),
										_: 1
									}, 8, ["onClick"])]),
									_: 2
								}, 1024)
							]),
							_: 2
						}, 1024);
					}), 128))]),
					_: 1
				})]),
				_: 1
			})]),
			_: 1
		}, 8, ["heading"]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Search Indexes"),
			url: "search#indexes"
		}, null, 8, ["topic"])
	])], 64);
}
var Search_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Search.vue"]]);
//#endregion
export { Search_default as default };
