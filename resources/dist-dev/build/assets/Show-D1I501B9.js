import { At as toDisplayString, B as openBlock, C as createVNode, W as renderList, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-FaDyk5AC.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $t as Item_default$2, Hn as Item_default$1, St as Group_default, Un as Group_default$1, Vt as Header_default, Zt as Menu_default, en as Dropdown_default, kt as Modal_default, pi as Button_default, r as Item_default$3, xt as Item_default } from "./ui-C6seNKvP.js";
import { O as ResourceDeleter_default, r as Head_default, w as SubmissionListing_default } from "./index-CaF3D-v6.js";
//#region resources/js/components/forms/ExportSubmissionsModal.vue
var _sfc_main$1 = {
	__name: "ExportSubmissionsModal",
	props: {
		exporters: {
			type: Array,
			required: true
		},
		columns: {
			type: Array,
			required: true
		},
		listingParameters: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["close"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const format = ref(props.exporters[0]?.handle ?? null);
		const scope = ref("all");
		const selectedColumns = ref(props.columns.map((column) => column.handle));
		const hasFilteredScope = computed(() => {
			const params = props.listingParameters;
			return !!(params.search || params.filters);
		});
		const selectedExporter = computed(() => props.exporters.find((exporter) => exporter.handle === format.value));
		const canSelectColumns = computed(() => selectedExporter.value?.supportsColumnSelection ?? false);
		const allColumnsSelected = computed(() => selectedColumns.value.length === props.columns.length);
		const canExport = computed(() => selectedExporter.value && (!canSelectColumns.value || selectedColumns.value.length > 0));
		function toggleAllColumns() {
			selectedColumns.value = allColumnsSelected.value ? [] : props.columns.map((column) => column.handle);
		}
		function exportSubmissions() {
			if (!canExport.value) return;
			const params = props.listingParameters;
			const query = new URLSearchParams();
			if (params.sort) query.set("sort", params.sort);
			if (params.order) query.set("order", params.order);
			if (scope.value === "filtered") {
				if (params.search) query.set("search", params.search);
				if (params.filters) query.set("filters", params.filters);
			}
			if (canSelectColumns.value && !allColumnsSelected.value) query.set("columns", selectedColumns.value.join(","));
			let url = selectedExporter.value.downloadUrl;
			if (query.size) {
				const separator = url.includes("?") ? "&" : "?";
				url += separator + query.toString();
			}
			window.open(url, "_blank");
			emit("close");
		}
		const __returned__ = {
			emit,
			props,
			format,
			scope,
			selectedColumns,
			hasFilteredScope,
			selectedExporter,
			canSelectColumns,
			allColumnsSelected,
			canExport,
			toggleAllColumns,
			exportSubmissions,
			ref,
			computed,
			get Modal() {
				return Modal_default;
			},
			get Button() {
				return Button_default;
			},
			get RadioGroup() {
				return Group_default;
			},
			get Radio() {
				return Item_default;
			},
			get CheckboxGroup() {
				return Group_default$1;
			},
			get Checkbox() {
				return Item_default$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$1 = { class: "space-y-6" };
var _hoisted_2 = { class: "text-sm font-medium mb-1.5 block" };
var _hoisted_3 = { class: "text-sm font-medium mb-1.5 block" };
var _hoisted_4 = { key: 0 };
var _hoisted_5 = { class: "flex items-center justify-between mb-1.5" };
var _hoisted_6 = { class: "text-sm font-medium block" };
var _hoisted_7 = { class: "max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 p-3" };
var _hoisted_8 = { class: "flex justify-end p-2" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Modal"], {
		title: _ctx.__("Export Submissions"),
		open: "",
		"onUpdate:open": _cache[3] || (_cache[3] = ($event) => $setup.emit("close"))
	}, {
		footer: withCtx(() => [createBaseVNode("div", _hoisted_8, [createVNode($setup["Button"], {
			variant: "primary",
			text: _ctx.__("Export"),
			disabled: !$setup.canExport,
			onClick: $setup.exportSubmissions
		}, null, 8, ["text", "disabled"])])]),
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$1, [
			createBaseVNode("div", null, [createBaseVNode("label", _hoisted_2, toDisplayString(_ctx.__("Format")), 1), createVNode($setup["RadioGroup"], {
				modelValue: $setup.format,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.format = $event),
				inline: ""
			}, {
				default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.exporters, (exporter) => {
					return openBlock(), createBlock($setup["Radio"], {
						key: exporter.handle,
						value: exporter.handle,
						label: exporter.title
					}, null, 8, ["value", "label"]);
				}), 128))]),
				_: 1
			}, 8, ["modelValue"])]),
			createBaseVNode("div", null, [createBaseVNode("label", _hoisted_3, toDisplayString(_ctx.__("Submissions")), 1), createVNode($setup["RadioGroup"], {
				modelValue: $setup.scope,
				"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.scope = $event)
			}, {
				default: withCtx(() => [createVNode($setup["Radio"], {
					value: "all",
					label: _ctx.__("All Submissions")
				}, null, 8, ["label"]), createVNode($setup["Radio"], {
					value: "filtered",
					label: _ctx.__("Filtered Submissions"),
					description: _ctx.__("statamic::messages.form_export_filtered_description"),
					disabled: !$setup.hasFilteredScope
				}, null, 8, [
					"label",
					"description",
					"disabled"
				])]),
				_: 1
			}, 8, ["modelValue"])]),
			$setup.canSelectColumns ? (openBlock(), createElementBlock("div", _hoisted_4, [createBaseVNode("div", _hoisted_5, [createBaseVNode("label", _hoisted_6, toDisplayString(_ctx.__("Columns")), 1), createBaseVNode("button", {
				type: "button",
				class: "cursor-pointer text-xs text-gray-500 hover:text-gray-800 dark:hover:text-gray-200",
				onClick: $setup.toggleAllColumns
			}, toDisplayString($setup.allColumnsSelected ? _ctx.__("Deselect All") : _ctx.__("Select All")), 1)]), createBaseVNode("div", _hoisted_7, [createVNode($setup["CheckboxGroup"], {
				modelValue: $setup.selectedColumns,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.selectedColumns = $event)
			}, {
				default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.columns, (column) => {
					return openBlock(), createBlock($setup["Checkbox"], {
						key: column.handle,
						value: column.handle,
						label: column.title
					}, null, 8, ["value", "label"]);
				}), 128))]),
				_: 1
			}, 8, ["modelValue"])])])) : createCommentVNode("", true)
		])]),
		_: 1
	}, 8, ["title"]);
}
var ExportSubmissionsModal_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "ExportSubmissionsModal.vue"]]);
//#endregion
//#region resources/js/pages/forms/Show.vue
var _sfc_main = {
	__name: "Show",
	props: [
		"form",
		"columns",
		"filters",
		"actionUrl",
		"exporters",
		"exportColumns",
		"redirectUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const deleter = ref(null);
		const submissionListing = ref(null);
		const exportModalOpen = ref(false);
		const listingParameters = ref({});
		function openExportModal() {
			listingParameters.value = submissionListing.value?.parameters ?? {};
			exportModalOpen.value = true;
		}
		const __returned__ = {
			props,
			deleter,
			submissionListing,
			exportModalOpen,
			listingParameters,
			openExportModal,
			ref,
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Dropdown() {
				return Dropdown_default;
			},
			get DropdownMenu() {
				return Menu_default;
			},
			get DropdownItem() {
				return Item_default$2;
			},
			get Button() {
				return Button_default;
			},
			get CommandPaletteItem() {
				return Item_default$3;
			},
			ResourceDeleter: ResourceDeleter_default,
			FormSubmissionListing: SubmissionListing_default,
			ExportSubmissionsModal: ExportSubmissionsModal_default
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
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode($setup["Head"], { title: [_ctx.__($props.form.title), _ctx.__("Forms")] }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__($props.form.title),
			icon: "forms"
		}, {
			default: withCtx(() => [
				$props.form.canEdit || $props.form.canDelete ? (openBlock(), createBlock($setup["Dropdown"], {
					key: 0,
					placement: "left-start",
					class: "me-2"
				}, {
					default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
						default: withCtx(() => [
							$props.form.canEdit ? (openBlock(), createBlock($setup["DropdownItem"], {
								key: 0,
								text: _ctx.__("Configure Form"),
								icon: "cog",
								href: $props.form.editUrl
							}, null, 8, ["text", "href"])) : createCommentVNode("", true),
							$props.form.canConfigureFields ? (openBlock(), createBlock($setup["DropdownItem"], {
								key: 1,
								text: _ctx.__("Edit Blueprint"),
								icon: "blueprint-edit",
								href: $props.form.blueprintUrl
							}, null, 8, ["text", "href"])) : createCommentVNode("", true),
							$props.form.canDelete ? (openBlock(), createBlock($setup["DropdownItem"], {
								key: 2,
								text: _ctx.__("Delete Form"),
								icon: "trash",
								variant: "destructive",
								onClick: _cache[0] || (_cache[0] = ($event) => $setup.deleter.confirm())
							}, null, 8, ["text"])) : createCommentVNode("", true)
						]),
						_: 1
					})]),
					_: 1
				})) : createCommentVNode("", true),
				createVNode($setup["CommandPaletteItem"], {
					category: "Actions",
					text: _ctx.__("Configure Form"),
					icon: "cog",
					url: $props.form.editUrl
				}, null, 8, ["text", "url"]),
				createVNode($setup["CommandPaletteItem"], {
					category: "Actions",
					text: _ctx.__("Edit Blueprint"),
					icon: "blueprint-edit",
					url: $props.form.blueprintUrl
				}, null, 8, ["text", "url"]),
				createVNode($setup["CommandPaletteItem"], {
					category: "Actions",
					text: _ctx.__("Delete Form"),
					icon: "trash",
					action: () => $setup.deleter.confirm()
				}, null, 8, ["text", "action"]),
				$props.form.canDelete ? (openBlock(), createBlock($setup["ResourceDeleter"], {
					key: 1,
					ref: "deleter",
					"resource-title": $props.form.title,
					route: $props.form.deleteUrl,
					redirect: $props.redirectUrl
				}, null, 8, [
					"resource-title",
					"route",
					"redirect"
				])) : createCommentVNode("", true),
				$props.exporters.length ? (openBlock(), createBlock($setup["Button"], {
					key: 2,
					text: _ctx.__("Export Submissions"),
					onClick: $setup.openExportModal
				}, null, 8, ["text"])) : createCommentVNode("", true),
				$props.exporters.length ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
					key: 3,
					category: "Actions",
					text: _ctx.__("Export Submissions"),
					icon: "save",
					action: $setup.openExportModal,
					prioritize: ""
				}, null, 8, ["text"])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["FormSubmissionListing"], {
			ref: "submissionListing",
			form: $props.form.handle,
			"action-url": $props.actionUrl,
			"sort-column": "datestamp",
			"sort-direction": "desc",
			columns: $props.columns,
			filters: $props.filters
		}, null, 8, [
			"form",
			"action-url",
			"columns",
			"filters"
		]),
		$setup.exportModalOpen ? (openBlock(), createBlock($setup["ExportSubmissionsModal"], {
			key: 0,
			exporters: $props.exporters,
			columns: $props.exportColumns,
			"listing-parameters": $setup.listingParameters,
			onClose: _cache[1] || (_cache[1] = ($event) => $setup.exportModalOpen = false)
		}, null, 8, [
			"exporters",
			"columns",
			"listing-parameters"
		])) : createCommentVNode("", true)
	]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
