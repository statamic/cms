import { At as toDisplayString, B as openBlock, C as createVNode, W as renderList, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $t as Menu_default, Ct as Item_default$1, Ut as Header_default, jt as Modal_default, li as Button_default, nn as Dropdown_default, r as Item_default$2, tn as Item_default, wt as Group_default } from "./ui-BfR7XN6t.js";
import { C as SubmissionListing_default, D as ResourceDeleter_default, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/forms/Show.vue
var _sfc_main = {
	__name: "Show",
	props: [
		"form",
		"columns",
		"filters",
		"actionUrl",
		"exporters",
		"redirectUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const deleter = ref(null);
		const submissionListing = ref(null);
		const exportModalOpen = ref(false);
		const exportFormat = ref(null);
		const exportScope = ref("all");
		const listingParameters = ref({});
		const hasFilteredScope = computed(() => {
			const params = listingParameters.value;
			const hasSortOverride = params.sort && params.sort !== "datestamp" || params.order && params.order !== "desc";
			return !!(params.search || params.filters || hasSortOverride);
		});
		function openExportModal() {
			listingParameters.value = submissionListing.value?.parameters ?? {};
			exportFormat.value = props.exporters[0]?.handle ?? null;
			exportScope.value = "all";
			exportModalOpen.value = true;
		}
		function exportSubmissions() {
			const exporter = props.exporters.find((e) => e.handle === exportFormat.value);
			if (!exporter) return;
			let url = exporter.downloadUrl;
			if (exportScope.value === "filtered") {
				const params = listingParameters.value;
				const query = new URLSearchParams();
				if (params.search) query.set("search", params.search);
				if (params.sort) query.set("sort", params.sort);
				if (params.order) query.set("order", params.order);
				if (params.filters) query.set("filters", params.filters);
				const separator = url.includes("?") ? "&" : "?";
				url += separator + query.toString();
			}
			window.open(url, "_blank");
			exportModalOpen.value = false;
		}
		const __returned__ = {
			props,
			deleter,
			submissionListing,
			exportModalOpen,
			exportFormat,
			exportScope,
			listingParameters,
			hasFilteredScope,
			openExportModal,
			exportSubmissions,
			ref,
			computed,
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
				return Item_default;
			},
			get Button() {
				return Button_default;
			},
			get Modal() {
				return Modal_default;
			},
			get RadioGroup() {
				return Group_default;
			},
			get Radio() {
				return Item_default$1;
			},
			get CommandPaletteItem() {
				return Item_default$2;
			},
			ResourceDeleter: ResourceDeleter_default,
			FormSubmissionListing: SubmissionListing_default
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
var _hoisted_2 = { class: "space-y-4" };
var _hoisted_3 = { class: "text-sm font-medium mb-1.5 block" };
var _hoisted_4 = { class: "text-sm font-medium mb-1.5 block" };
var _hoisted_5 = { class: "flex justify-end p-2" };
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
		createVNode($setup["Modal"], {
			open: $setup.exportModalOpen,
			"onUpdate:open": _cache[3] || (_cache[3] = ($event) => $setup.exportModalOpen = $event),
			title: _ctx.__("Export Submissions")
		}, {
			footer: withCtx(() => [createBaseVNode("div", _hoisted_5, [createVNode($setup["Button"], {
				variant: "primary",
				text: _ctx.__("Export"),
				onClick: $setup.exportSubmissions
			}, null, 8, ["text"])])]),
			default: withCtx(() => [createBaseVNode("div", _hoisted_2, [createBaseVNode("div", null, [createBaseVNode("label", _hoisted_3, toDisplayString(_ctx.__("Format")), 1), createVNode($setup["RadioGroup"], {
				modelValue: $setup.exportFormat,
				"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.exportFormat = $event),
				inline: ""
			}, {
				default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.exporters, (format) => {
					return openBlock(), createBlock($setup["Radio"], {
						key: format.handle,
						value: format.handle,
						label: format.title
					}, null, 8, ["value", "label"]);
				}), 128))]),
				_: 1
			}, 8, ["modelValue"])]), createBaseVNode("div", null, [createBaseVNode("label", _hoisted_4, toDisplayString(_ctx.__("Submissions")), 1), createVNode($setup["RadioGroup"], {
				modelValue: $setup.exportScope,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.exportScope = $event)
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
			}, 8, ["modelValue"])])])]),
			_: 1
		}, 8, ["open", "title"])
	]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
