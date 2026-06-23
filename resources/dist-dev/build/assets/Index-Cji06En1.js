import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, b as createSlots, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, Ci as Icon_default, E as StatusIndicator_default, Hn as Panel_default, Kt as Menu_default$1, Xt as Separator_default, en as Label_default, n as DocsCallout_default, nn as Dropdown_default, qt as Item_default$1, tn as Item_default, u as ItemActions_default, ui as Badge_default } from "./ui-BfR7XN6t.js";
import { n as useArchitecturalBackground, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/components/collections/Listing.vue
var _sfc_main$1 = {
	components: {
		Link: link_default,
		CardPanel: Panel_default,
		StatusIndicator: StatusIndicator_default,
		Badge: Badge_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownLabel: Label_default,
		DropdownItem: Item_default,
		DropdownSeparator: Separator_default,
		ItemActions: ItemActions_default
	},
	props: {
		canCreateCollections: Boolean,
		createUrl: String,
		initialRows: Array,
		initialColumns: Array,
		actionUrl: String
	},
	data() {
		const modePreference = this.$preferences.get("collections.listing_mode");
		return {
			initializedRequest: false,
			items: this.initialRows,
			columns: this.initialColumns,
			requestUrl: cp_url(`collections`),
			mode: !modePreference || !["list", "grid"].includes(modePreference) ? "list" : modePreference,
			source: null
		};
	},
	watch: { mode(mode) {
		this.$preferences.set("collections.listing_mode", mode);
	} },
	mounted() {
		this.addToCommandPalette();
	},
	methods: {
		request() {
			if (this.source) this.source.abort();
			this.source = new AbortController();
			this.$axios.get(this.requestUrl, {
				params: this.parameters,
				signal: this.source.signal
			}).then((response) => {
				this.columns = response.data.meta.columns;
				this.items = Object.values(response.data.data);
				this.meta = response.data.meta;
				this.loading = false;
			}).catch((e) => {
				if (this.$axios.isCancel(e)) return;
				this.loading = false;
				this.initializing = false;
				if (e.request && !e.response) return;
				this.$toast.error(e.response ? e.response.data.message : __("Something went wrong"), { duration: null });
			});
		},
		actionStarted() {
			this.loading = true;
		},
		actionCompleted(successful, response) {
			successful ? this.$toast.success(response.message || __("Action completed")) : this.$toast.error(response.message || __("Action failed"));
			this.request();
		},
		addToCommandPalette() {
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Create Collection"),
				icon: "collections",
				when: () => this.canCreateCollections,
				url: this.createUrl
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Switch to Grid Layout"),
				icon: "layout-grid",
				when: () => this.mode === "list",
				action: () => this.mode = "grid"
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Switch to List Layout"),
				icon: "layout-list",
				when: () => this.mode === "grid",
				action: () => this.mode = "list"
			});
		}
	}
};
var _hoisted_1$1 = {
	key: 0,
	class: "@container/collections flex flex-wrap py-2 gap-y-6 -mx-3"
};
var _hoisted_2$1 = { class: "flex items-center gap-1.5" };
var _hoisted_3 = {
	key: 0,
	class: "text-sm text-gray-600 dark:text-gray-400"
};
var _hoisted_4 = { class: "flex items-center gap-2" };
var _hoisted_5 = { class: "flex flex-col gap-[8px] justify-between py-1 px-5" };
var _hoisted_6 = {
	key: 0,
	class: "w-full [&_td]:py-1 [&_td]:px-5 [&_td]:text-sm"
};
var _hoisted_7 = { class: "flex items-center gap-2" };
var _hoisted_8 = { class: "text-end font-mono text-xs text-gray-500 ps-6 whitespace-nowrap" };
var _hoisted_9 = {
	key: 1,
	class: "flex flex-col items-center justify-center space-y-2 h-full"
};
var _hoisted_10 = { class: "flex items-center gap-1.5" };
var _hoisted_11 = {
	key: 0,
	class: "flex items-center gap-1.5"
};
var _hoisted_12 = {
	key: 1,
	class: "flex items-center gap-1.5"
};
var _hoisted_13 = { class: "flex items-center gap-2 sm:gap-3" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_toggle_item = resolveComponent("ui-toggle-item");
	const _component_ui_toggle_group = resolveComponent("ui-toggle-group");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_ui_header = resolveComponent("ui-header");
	const _component_ui_heading = resolveComponent("ui-heading");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_ui_skeleton = resolveComponent("ui-skeleton");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_create_entry_button = resolveComponent("create-entry-button");
	const _component_ui_panel_header = resolveComponent("ui-panel-header");
	const _component_ui_listing_table_head = resolveComponent("ui-listing-table-head");
	const _component_StatusIndicator = resolveComponent("StatusIndicator");
	const _component_Link = resolveComponent("Link");
	const _component_date_time = resolveComponent("date-time");
	const _component_ui_listing_table_body = resolveComponent("ui-listing-table-body");
	const _component_ui_subheading = resolveComponent("ui-subheading");
	const _component_ui_listing = resolveComponent("ui-listing");
	const _component_ui_card = resolveComponent("ui-card");
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_ui_panel_footer = resolveComponent("ui-panel-footer");
	const _component_ui_panel = resolveComponent("ui-panel");
	const _component_ui_icon = resolveComponent("ui-icon");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode(_component_ui_header, {
			title: _ctx.__("Collections"),
			icon: "collections"
		}, {
			default: withCtx(() => [createVNode(_component_ui_toggle_group, {
				modelValue: $data.mode,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.mode = $event)
			}, {
				default: withCtx(() => [createVNode(_component_ui_toggle_item, {
					icon: "layout-list",
					value: "list",
					"aria-label": _ctx.__("List view")
				}, null, 8, ["aria-label"]), createVNode(_component_ui_toggle_item, {
					icon: "layout-grid",
					value: "grid",
					"aria-label": _ctx.__("Grid view")
				}, null, 8, ["aria-label"])]),
				_: 1
			}, 8, ["modelValue"]), $props.canCreateCollections ? (openBlock(), createBlock(_component_ui_button, {
				key: 0,
				href: $props.createUrl,
				text: _ctx.__("Create Collection"),
				variant: "primary"
			}, null, 8, ["href", "text"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"]),
		$data.mode === "grid" ? (openBlock(), createElementBlock("div", _hoisted_1$1, [(openBlock(true), createElementBlock(Fragment, null, renderList($data.items, (collection) => {
			return openBlock(), createElementBlock("div", {
				class: "w-full @4xl:w-1/2 px-3",
				key: collection.id
			}, [createVNode(_component_ui_panel, null, {
				default: withCtx(() => [
					createVNode(_component_ui_panel_header, { class: "flex items-center justify-between" }, {
						default: withCtx(() => [createBaseVNode("div", _hoisted_2$1, [createVNode(_component_ui_heading, {
							size: "lg",
							text: _ctx.__(collection.title),
							href: collection.available_in_selected_site ? collection.entries_url : collection.edit_url
						}, null, 8, ["text", "href"]), collection.available_in_selected_site ? (openBlock(), createElementBlock("span", _hoisted_3, " (" + toDisplayString(_ctx.__n("messages.entry_count", collection.entries_count, { count: collection.entries_count })) + ") ", 1)) : createCommentVNode("", true)]), createBaseVNode("aside", _hoisted_4, [createVNode(_component_ItemActions, {
							url: collection.actions_url,
							item: collection.id,
							onStarted: $options.actionStarted,
							onCompleted: $options.actionCompleted
						}, {
							default: withCtx(({ actions, loadActions, shouldShowSkeleton }) => [createVNode(_component_Dropdown, {
								placement: "left-start",
								onMouseover: loadActions,
								onFocus: loadActions,
								onClick: loadActions
							}, {
								default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
									default: withCtx(() => [
										collection.available_in_selected_site ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 0,
											text: _ctx.__("View"),
											icon: "eye",
											href: collection.entries_url
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										collection.available_in_selected_site && collection.url ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 1,
											text: _ctx.__("Visit URL"),
											icon: "external-link",
											target: "_blank",
											href: collection.url
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										collection.editable ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 2,
											text: _ctx.__("Configure"),
											icon: "cog",
											href: collection.edit_url
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										collection.blueprint_editable ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 3,
											text: _ctx.__("Edit Blueprints"),
											icon: "blueprint-edit",
											href: collection.blueprints_url
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										collection.editable ? (openBlock(), createBlock(_component_DropdownItem, {
											key: 4,
											text: _ctx.__("Scaffold Views"),
											icon: "scaffold",
											href: collection.scaffold_url
										}, null, 8, ["text", "href"])) : createCommentVNode("", true),
										shouldShowSkeleton || actions.length ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 5 })) : createCommentVNode("", true),
										shouldShowSkeleton ? (openBlock(), createElementBlock(Fragment, { key: 6 }, renderList(3, (index) => {
											return createBaseVNode("div", {
												key: index,
												class: "contents"
											}, [createVNode(_component_ui_skeleton, { class: "m-1 size-5" }), createVNode(_component_ui_skeleton, { class: normalizeClass(["mx-2 my-1.5 h-5", index === 1 ? "w-28" : index === 2 ? "w-36" : "w-24"]) }, null, 8, ["class"])]);
										}), 64)) : (openBlock(true), createElementBlock(Fragment, { key: 7 }, renderList(actions, (action) => {
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
							}, 1032, [
								"onMouseover",
								"onFocus",
								"onClick"
							])]),
							_: 2
						}, 1032, [
							"url",
							"item",
							"onStarted",
							"onCompleted"
						]), collection.available_in_selected_site ? (openBlock(), createBlock(_component_create_entry_button, {
							key: 0,
							variant: "default",
							blueprints: collection.blueprints,
							text: collection.create_label,
							size: "sm"
						}, null, 8, ["blueprints", "text"])) : createCommentVNode("", true)])]),
						_: 2
					}, 1024),
					createVNode(_component_ui_card, { class: "h-40 px-0! py-2!" }, {
						default: withCtx(() => [collection.available_in_selected_site ? (openBlock(), createBlock(_component_ui_listing, {
							key: 0,
							url: collection.entries_listing_url,
							"per-page": 5,
							filters: collection.filters,
							columns: collection.columns,
							"sort-column": collection.sort_column,
							"sort-direction": collection.sort_direction
						}, {
							initializing: withCtx(() => [createBaseVNode("div", _hoisted_5, [
								createVNode(_component_ui_skeleton, { class: "h-[18px] w-full" }),
								createVNode(_component_ui_skeleton, { class: "h-[18px] w-full" }),
								createVNode(_component_ui_skeleton, { class: "h-[18px] w-full" }),
								createVNode(_component_ui_skeleton, { class: "h-[18px] w-full" }),
								createVNode(_component_ui_skeleton, { class: "h-[18px] w-full" })
							])]),
							default: withCtx(({ items }) => [items.length ? (openBlock(), createElementBlock("table", _hoisted_6, [createVNode(_component_ui_listing_table_head, { "sr-only": "" }), createVNode(_component_ui_listing_table_body, { class: "divide-y divide-gray-200 dark:divide-gray-700" }, createSlots({
								"cell-title": withCtx(({ row: entry }) => [createBaseVNode("div", _hoisted_7, [createVNode(_component_StatusIndicator, { status: entry.status }, null, 8, ["status"]), createVNode(_component_Link, {
									href: entry.edit_url,
									class: "line-clamp-1 overflow-hidden text-ellipsis",
									text: entry.title
								}, null, 8, ["href", "text"])])]),
								_: 2
							}, [collection.dated ? {
								name: "cell-date",
								fn: withCtx(({ row: entry }) => [createBaseVNode("div", _hoisted_8, [createVNode(_component_date_time, {
									of: entry.date.date,
									"date-only": ""
								}, null, 8, ["of"])])]),
								key: "0"
							} : void 0]), 1024)])) : (openBlock(), createBlock(_component_ui_subheading, {
								key: 1,
								class: "text-center h-full flex items-center justify-center"
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Nothing to see here, yet.")), 1)]),
								_: 1
							}))]),
							_: 2
						}, 1032, [
							"url",
							"filters",
							"columns",
							"sort-column",
							"sort-direction"
						])) : (openBlock(), createElementBlock("div", _hoisted_9, [createVNode(_component_ui_subheading, { class: "text-center" }, {
							default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("This collection isn't available in this site.")), 1)]),
							_: 1
						}), createVNode(_component_ui_button, {
							href: collection.edit_url,
							text: _ctx.__("Configure"),
							size: "sm"
						}, null, 8, ["href", "text"])]))]),
						_: 2
					}, 1024),
					collection.available_in_selected_site ? (openBlock(), createBlock(_component_ui_panel_footer, {
						key: 0,
						class: "flex items-center gap-6 gap-y-1.5 text-sm text-gray-600 dark:text-gray-400"
					}, {
						default: withCtx(() => [
							createBaseVNode("div", _hoisted_10, [createVNode(_component_ui_badge, {
								text: String(collection.published_entries_count),
								pill: "",
								class: "bg-white! dark:bg-gray-700! [&_span]:st-text-trim-cap"
							}, null, 8, ["text"]), createBaseVNode("span", null, toDisplayString(_ctx.__("Published")), 1)]),
							collection.scheduled_entries_count > 0 ? (openBlock(), createElementBlock("div", _hoisted_11, [createVNode(_component_ui_badge, {
								text: String(collection.scheduled_entries_count),
								pill: "",
								class: "bg-white! dark:bg-gray-700! [&_span]:st-text-trim-cap"
							}, null, 8, ["text"]), createBaseVNode("span", null, toDisplayString(_ctx.__("Scheduled")), 1)])) : createCommentVNode("", true),
							collection.draft_entries_count > 0 ? (openBlock(), createElementBlock("div", _hoisted_12, [createVNode(_component_ui_badge, {
								text: String(collection.draft_entries_count),
								pill: "",
								class: "bg-white! dark:bg-gray-700! [&_span]:st-text-trim-cap"
							}, null, 8, ["text"]), createBaseVNode("span", null, toDisplayString(_ctx.__("Drafts")), 1)])) : createCommentVNode("", true)
						]),
						_: 2
					}, 1024)) : createCommentVNode("", true)
				]),
				_: 2
			}, 1024)]);
		}), 128))])) : createCommentVNode("", true),
		$data.mode === "list" ? (openBlock(), createBlock(_component_ui_listing, {
			key: 1,
			items: $data.items,
			columns: $data.columns,
			"action-url": $props.actionUrl,
			"allow-search": false,
			"allow-customizing-columns": false,
			onRefreshing: $options.request
		}, {
			"cell-title": withCtx(({ row: collection }) => [createVNode(_component_Link, {
				href: collection.available_in_selected_site ? collection.entries_url : collection.edit_url,
				class: "flex items-center gap-2"
			}, {
				default: withCtx(() => [createVNode(_component_ui_icon, { name: collection.icon || "collections" }, null, 8, ["name"]), createTextVNode(" " + toDisplayString(_ctx.__(collection.title)), 1)]),
				_: 2
			}, 1032, ["href"])]),
			"cell-entries_count": withCtx(({ row: collection }) => [createBaseVNode("div", _hoisted_13, [
				collection.published_entries_count > 0 ? (openBlock(), createBlock(_component_ui_badge, {
					key: 0,
					color: "green",
					text: _ctx.__("Published"),
					append: collection.published_entries_count,
					pill: ""
				}, null, 8, ["text", "append"])) : createCommentVNode("", true),
				collection.scheduled_entries_count > 0 ? (openBlock(), createBlock(_component_ui_badge, {
					key: 1,
					color: "yellow",
					text: _ctx.__("Scheduled"),
					append: collection.scheduled_entries_count,
					pill: ""
				}, null, 8, ["text", "append"])) : createCommentVNode("", true),
				collection.draft_entries_count > 0 ? (openBlock(), createBlock(_component_ui_badge, {
					key: 2,
					text: _ctx.__("Drafts"),
					append: collection.draft_entries_count,
					pill: ""
				}, null, 8, ["text", "append"])) : createCommentVNode("", true)
			])]),
			"prepended-row-actions": withCtx(({ row: collection }) => [
				createVNode(_component_DropdownItem, {
					text: _ctx.__("View"),
					icon: "eye",
					href: collection.entries_url
				}, null, 8, ["text", "href"]),
				collection.url ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 0,
					text: _ctx.__("Visit URL"),
					icon: "external-link",
					target: "_blank",
					href: collection.url
				}, null, 8, ["text", "href"])) : createCommentVNode("", true),
				collection.editable ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 1,
					text: _ctx.__("Configure"),
					icon: "cog",
					href: collection.edit_url
				}, null, 8, ["text", "href"])) : createCommentVNode("", true),
				collection.blueprint_editable ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 2,
					text: _ctx.__("Edit Blueprints"),
					icon: "blueprint-edit",
					href: collection.blueprints_url
				}, null, 8, ["text", "href"])) : createCommentVNode("", true),
				collection.editable ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 3,
					text: _ctx.__("Scaffold Views"),
					icon: "scaffold",
					href: collection.scaffold_url
				}, null, 8, ["text", "href"])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, [
			"items",
			"columns",
			"action-url",
			"onRefreshing"
		])) : createCommentVNode("", true)
	], 64);
}
var Listing_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [
	["render", _sfc_render$1],
	["__scopeId", "data-v-a3d29dc5"],
	["__file", "Listing.vue"]
]);
//#endregion
//#region resources/js/pages/collections/Index.vue
var _sfc_main = {
	__name: "Index",
	props: {
		collections: Array,
		columns: Array,
		createUrl: String,
		actionUrl: String,
		canCreate: Boolean
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		if (props.collections.length === 0) useArchitecturalBackground();
		const __returned__ = {
			props,
			Listing: Listing_default,
			get Icon() {
				return Icon_default;
			},
			get EmptyStateMenu() {
				return Menu_default$1;
			},
			get EmptyStateItem() {
				return Item_default$1;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get useArchitecturalBackground() {
				return useArchitecturalBackground;
			},
			Head: Head_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "py-8 pt-16 text-center" };
var _hoisted_2 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Collections") }, null, 8, ["title"]),
		$props.collections.length ? (openBlock(), createBlock($setup["Listing"], {
			key: 0,
			"initial-rows": $props.collections,
			"initial-columns": $props.columns,
			"can-create-collections": $props.canCreate,
			"create-url": $props.createUrl,
			"action-url": $props.actionUrl
		}, null, 8, [
			"initial-rows",
			"initial-columns",
			"can-create-collections",
			"create-url",
			"action-url"
		])) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [createBaseVNode("header", _hoisted_1, [createBaseVNode("h1", _hoisted_2, [createVNode($setup["Icon"], {
			name: "collections",
			class: "size-5 text-gray-500"
		}), createTextVNode(" " + toDisplayString(_ctx.__("Collections")), 1)])]), createVNode($setup["EmptyStateMenu"], { heading: _ctx.__("statamic::messages.collection_configure_intro") }, {
			default: withCtx(() => [createVNode($setup["EmptyStateItem"], {
				href: $props.createUrl,
				icon: "collections",
				heading: _ctx.__("Create Collection"),
				description: _ctx.__("Get started by creating your first collection.")
			}, null, 8, [
				"href",
				"heading",
				"description"
			])]),
			_: 1
		}, 8, ["heading"])], 64)),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Collections"),
			url: "collections"
		}, null, 8, ["topic"])
	], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
