import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, T as defineComponent, W as renderList, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-B-BfxmJA.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { G as axios } from "./index.esm-B-zRpAhf.js";
import { $t as Menu_default, Ct as Item_default, Jn as Group_default$1, Ut as Header_default, jt as Modal_default, nn as Dropdown_default, qn as Item_default$1, r as Item_default$2, si as Button_default, tn as Item_default$3, wt as Group_default } from "./ui-DPzuvYkS.js";
import { H as ResourceDeleter_default, j as SubmissionListing_default, l as Layout_default, r as Head_default } from "./index-DJE_WRsq.js";
import { t as FormStatusIndicator_default } from "./FormStatusIndicator-CAX13lG0.js";
import { t as Layout_default$1 } from "./Layout-Beuk7oam.js";
import { t as PanelLayout_default } from "./PanelLayout-DGN-nSBD.js";
//#region resources/js/components/forms/ExportSubmissionsModal.vue
var _sfc_main = {
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
var ExportSubmissionsModal_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render$1], ["__file", "ExportSubmissionsModal.vue"]]);
//#endregion
//#region resources/js/pages/forms/submissions/Index.vue?vue&type=script&setup=true&lang.ts
var Index_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	layout: [
		Layout_default,
		PanelLayout_default,
		Layout_default$1
	],
	__name: "Index",
	props: [
		"form",
		"can",
		"columns",
		"filters",
		"actionUrl",
		"generateFakeSubmissionUrl",
		"summaryUrl",
		"chartsUpdateUrl",
		"exporters",
		"exportColumns",
		"redirectUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const deleter = ref(null);
		const generatingFakeSubmission = ref(false);
		const deletingFakeSubmissions = ref(false);
		const submissionListing = ref();
		const exportModalOpen = ref(false);
		const listingParameters = ref({});
		async function generateFakeSubmission(mode) {
			if (generatingFakeSubmission.value) return;
			generatingFakeSubmission.value = true;
			try {
				const { data } = await axios.post(props.generateFakeSubmissionUrl, { mode });
				Statamic.$toast.success(data.message);
				submissionListing.value?.refresh();
			} catch (error) {
				const message = error?.response?.data?.message ?? __("Something went wrong");
				Statamic.$toast.error(message, { duration: null });
			} finally {
				generatingFakeSubmission.value = false;
			}
		}
		async function deleteFakeSubmissions() {
			if (deletingFakeSubmissions.value) return;
			deletingFakeSubmissions.value = true;
			try {
				const { data } = await axios.post(props.actionUrl, {
					action: "delete_fake_submissions",
					selections: ["_all_fake_submissions_"],
					context: { form: props.form.handle },
					values: {}
				});
				const message = data?.message ?? __("Saved");
				if (data?.success === false) Statamic.$toast.error(message, { duration: null });
				else {
					Statamic.$toast.success(message);
					submissionListing.value?.refresh();
				}
			} catch (error) {
				const message = error?.response?.data?.message ?? __("Something went wrong");
				Statamic.$toast.error(message, { duration: null });
			} finally {
				deletingFakeSubmissions.value = false;
			}
		}
		function openExportModal() {
			listingParameters.value = submissionListing.value?.parameters ?? {};
			exportModalOpen.value = true;
		}
		const __returned__ = {
			props,
			deleter,
			generatingFakeSubmission,
			deletingFakeSubmissions,
			submissionListing,
			exportModalOpen,
			listingParameters,
			generateFakeSubmission,
			deleteFakeSubmissions,
			openExportModal,
			Head: Head_default,
			get Button() {
				return Button_default;
			},
			get CommandPaletteItem() {
				return Item_default$2;
			},
			get Dropdown() {
				return Dropdown_default;
			},
			get DropdownItem() {
				return Item_default$3;
			},
			get DropdownMenu() {
				return Menu_default;
			},
			get Header() {
				return Header_default;
			},
			FormStatusIndicator: FormStatusIndicator_default,
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
});
//#endregion
//#region resources/js/pages/forms/submissions/Index.vue
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: [
		_ctx.__("Results"),
		_ctx.__($props.form.title),
		_ctx.__("Forms")
	] }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], null, {
			title: withCtx(() => [createVNode($setup["FormStatusIndicator"], { status: $props.form.status }, null, 8, ["status"]), createTextVNode(" " + toDisplayString(_ctx.__($props.form.title)), 1)]),
			default: withCtx(() => [
				$props.can.edit || $props.can.delete ? (openBlock(), createBlock($setup["Dropdown"], {
					key: 0,
					placement: "left-start",
					class: "me-2"
				}, {
					default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
						default: withCtx(() => [$props.can.edit ? (openBlock(), createBlock($setup["DropdownItem"], {
							key: 0,
							text: _ctx.__("Configure Form"),
							icon: "cog",
							href: $props.form.editUrl
						}, null, 8, ["text", "href"])) : createCommentVNode("", true), $props.can.delete ? (openBlock(), createBlock($setup["DropdownItem"], {
							key: 1,
							text: _ctx.__("Delete Form"),
							icon: "trash",
							variant: "destructive",
							onClick: _cache[0] || (_cache[0] = ($event) => $setup.deleter.confirm())
						}, null, 8, ["text"])) : createCommentVNode("", true)]),
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
					text: _ctx.__("Delete Form"),
					icon: "trash",
					action: () => $setup.deleter.confirm()
				}, null, 8, ["text", "action"]),
				$props.can.delete ? (openBlock(), createBlock($setup["ResourceDeleter"], {
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
				$props.form.canGenerateFakeSubmissions ? (openBlock(), createBlock($setup["Dropdown"], { key: 3 }, {
					trigger: withCtx(() => [createVNode($setup["Button"], {
						text: _ctx.__("Generate Fake Submission"),
						loading: $setup.generatingFakeSubmission || $setup.deletingFakeSubmissions,
						disabled: $setup.generatingFakeSubmission || $setup.deletingFakeSubmissions
					}, null, 8, [
						"text",
						"loading",
						"disabled"
					])]),
					default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
						default: withCtx(() => [
							createVNode($setup["DropdownItem"], {
								text: _ctx.__("Submission Only"),
								icon: "flask",
								onClick: _cache[1] || (_cache[1] = ($event) => $setup.generateFakeSubmission("cp_only"))
							}, null, 8, ["text"]),
							createVNode($setup["DropdownItem"], {
								text: _ctx.__("Submission + All Workflows"),
								icon: "rocket",
								onClick: _cache[2] || (_cache[2] = ($event) => $setup.generateFakeSubmission("full_pipeline"))
							}, null, 8, ["text"]),
							createVNode($setup["DropdownItem"], {
								text: _ctx.__("Delete All Fake Submissions"),
								icon: "trash",
								variant: "destructive",
								onClick: $setup.deleteFakeSubmissions
							}, null, 8, ["text"])
						]),
						_: 1
					})]),
					_: 1
				})) : createCommentVNode("", true),
				$props.form.canGenerateFakeSubmissions ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
					key: 4,
					category: "Actions",
					text: _ctx.__("Generate Fake Submission"),
					icon: "flask",
					action: () => $setup.generateFakeSubmission("cp_only")
				}, null, 8, ["text", "action"])) : createCommentVNode("", true),
				$props.form.canGenerateFakeSubmissions ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
					key: 5,
					category: "Actions",
					text: _ctx.__("Generate Fake Submission + Run All Workflows"),
					icon: "rocket",
					action: () => $setup.generateFakeSubmission("full_pipeline")
				}, null, 8, ["text", "action"])) : createCommentVNode("", true),
				$props.form.canGenerateFakeSubmissions ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
					key: 6,
					category: "Actions",
					text: _ctx.__("Delete All Fake Submissions"),
					icon: "trash",
					action: $setup.deleteFakeSubmissions
				}, null, 8, ["text"])) : createCommentVNode("", true),
				$props.exporters.length ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
					key: 7,
					category: "Actions",
					text: _ctx.__("Export Submissions"),
					icon: "save",
					action: $setup.openExportModal,
					prioritize: ""
				}, null, 8, ["text"])) : createCommentVNode("", true)
			]),
			_: 1
		}),
		createVNode($setup["FormSubmissionListing"], {
			ref: "submissionListing",
			form: $props.form.handle,
			"action-url": $props.actionUrl,
			"summary-url": $props.summaryUrl,
			"charts-update-url": $props.chartsUpdateUrl,
			"sort-column": "datestamp",
			"sort-direction": "desc",
			columns: $props.columns,
			filters: $props.filters,
			can: $props.can
		}, null, 8, [
			"form",
			"action-url",
			"summary-url",
			"charts-update-url",
			"columns",
			"filters",
			"can"
		]),
		$setup.exportModalOpen ? (openBlock(), createBlock($setup["ExportSubmissionsModal"], {
			key: 0,
			exporters: $props.exporters,
			columns: $props.exportColumns,
			"listing-parameters": $setup.listingParameters,
			onClose: _cache[3] || (_cache[3] = ($event) => $setup.exportModalOpen = false)
		}, null, 8, [
			"exporters",
			"columns",
			"listing-parameters"
		])) : createCommentVNode("", true)
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(Index_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
