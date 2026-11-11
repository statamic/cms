import { $ as useAttrs, F as mergeProps, J as renderSlot, Mt as normalizeProps, P as mergeModels, Pt as toDisplayString, R as onBeforeUnmount, S as createSlots, T as createVNode, U as openBlock, X as resolveDirective, _ as computed, b as createCommentVNode, ct as withCtx, d as withModifiers, it as watch, jt as normalizeClass, k as guardReactiveProps, l as vShow, lt as withDirectives, nt as useSlots, p as Fragment, q as renderList, tt as useModel, v as createBaseVNode, w as createTextVNode, x as createElementBlock, xt as ref, y as createBlock } from "./vue.esm-bundler-T63OhLKP.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { G as axios, c as router } from "./index.esm-CUcrycdV.js";
import { t as Thumbnail_default } from "./Thumbnail-D97ns4MC.js";
import { $n as Heading_default, At as Modal_default, Fn as Select_default, Ht as Header_default$1, Pt as Content_default, Qn as Header_default, Qr as Button_default, Qt as Menu_default, S as BulkActionsFloatingToolbar_default, Tt as Footer_default, Ut as Field_default, Xn as Panel_default, a as injectListingContext, en as Item_default$1, er as Card_default, g as Search_default, gi as Icon_default, i as Listing_default, jt as Stack_default, kt as Close_default, ln as Text_default, o as Pagination_default, r as Item_default, ri as Badge_default, rn as Description_default, rt as Textarea_default, s as Table_default, tn as Dropdown_default, x as CustomizeColumns_default, yt as Skeleton_default } from "./ui-d_k7wG4y.js";
import { T as toast, _ as numberFormatter, l as dateFormatter, s as config } from "./api-CD-B5vx5.js";
import { E as FileIcon_default, r as Head_default } from "./index-DnBwcRtG.js";
// resources/js/components/utilities/CacheBulkActions.vue
var _sfc_main$7 = {
	__name: "CacheBulkActions",
	props: {
		label: {
			type: String,
			required: true
		},
		processing: {
			type: Boolean,
			default: false
		}
	},
	emits: ["run"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const emit = __emit;
		const { selections, clearSelections } = injectListingContext();
		const visible = ref(false);
		let timeout = null;
		const actions = computed(() => [{
			handle: "cache",
			title: props.label,
			run() {
				if (!props.processing) emit("run", [...selections.value]);
			}
		}]);
		function deselect() {
			if (!props.processing) clearSelections();
		}
		watch(() => selections.value.length > 0, (selected) => {
			clearTimeout(timeout);
			if (selected) timeout = setTimeout(() => visible.value = true, 300);
			else visible.value = false;
		}, { immediate: true });
		onBeforeUnmount(() => {
			clearTimeout(timeout);
		});
		const __returned__ = {
			props,
			emit,
			selections,
			clearSelections,
			visible,
			get timeout() {
				return timeout;
			},
			set timeout(v) {
				timeout = v;
			},
			actions,
			deselect,
			computed,
			onBeforeUnmount,
			ref,
			watch,
			injectListingContext,
			BulkActionsFloatingToolbar: BulkActionsFloatingToolbar_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["BulkActionsFloatingToolbar"], {
		actions: $setup.actions,
		visible: $setup.visible,
		selections: $setup.selections,
		"clear-selections": $setup.deselect,
		inert: $props.processing || void 0,
		"aria-busy": $props.processing || void 0
	}, null, 8, [
		"actions",
		"visible",
		"selections",
		"inert",
		"aria-busy"
	]);
}
var CacheBulkActions_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$7, [["render", _sfc_render$7], ["__file", "CacheBulkActions.vue"]]);
// resources/js/components/utilities/CacheListing.vue
var _sfc_main$6 = /*@__PURE__*/ Object.assign({ inheritAttrs: false }, {
	__name: "CacheListing",
	props: /*@__PURE__*/ mergeModels({
		allowSelections: {
			type: Boolean,
			default: true
		},
		allowCustomizingColumns: {
			type: Boolean,
			default: true
		},
		bulkAction: {
			type: String,
			default: "Clear"
		},
		processing: {
			type: Boolean,
			default: false
		}
	}, {
		"selections": {
			type: Array,
			default: () => []
		},
		"selectionsModifiers": {}
	}),
	emits: /*@__PURE__*/ mergeModels(["bulk-action"], ["update:selections"]),
	setup(__props, { expose: __expose, emit: __emit }) {
		const emit = __emit;
		const selections = useModel(__props, "selections");
		const attrs = useAttrs();
		const slots = useSlots();
		const listing = ref(null);
		const cellSlots = computed(() => Object.keys(slots).filter((name) => name.startsWith("cell-")));
		__expose({ refresh: () => listing.value.refresh() });
		const __returned__ = {
			emit,
			selections,
			attrs,
			slots,
			listing,
			cellSlots,
			computed,
			ref,
			useAttrs,
			useSlots,
			get Listing() {
				return Listing_default;
			},
			get ListingCustomizeColumns() {
				return CustomizeColumns_default;
			},
			get ListingPagination() {
				return Pagination_default;
			},
			get ListingSearch() {
				return Search_default;
			},
			get ListingTable() {
				return Table_default;
			},
			get Panel() {
				return Panel_default;
			},
			get PanelFooter() {
				return Footer_default;
			},
			get PanelHeader() {
				return Header_default;
			},
			get Skeleton() {
				return Skeleton_default;
			},
			CacheBulkActions: CacheBulkActions_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1$6 = { class: "flex min-h-16 items-center gap-3" };
var _hoisted_2$5 = { class: "min-h-8 min-w-0 flex-1" };
var _hoisted_3$5 = {
	key: 0,
	class: "shrink-0 pe-2"
};
var _hoisted_4$5 = { class: "relative flex min-h-16 items-center gap-2 overflow-clip sm:gap-3 st-overflow-clip-margin" };
var _hoisted_5$3 = { class: "flex min-w-0 flex-1 items-center gap-2 overflow-x-auto py-1 ps-1 -ms-1 sm:gap-3" };
var _hoisted_6$3 = { class: "min-h-8 min-w-0 flex-1" };
var _hoisted_7$3 = {
	key: 0,
	class: "shrink-0 pe-2"
};
function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Listing"], mergeProps({ ref: "listing" }, $setup.attrs, {
		selections: $props.allowSelections ? $setup.selections : void 0,
		"allow-bulk-actions": $props.allowSelections,
		"allow-customizing-columns": $props.allowCustomizingColumns,
		"allow-select-all-matching": false,
		"max-selections": 1e3,
		"onUpdate:selections": _cache[1] || (_cache[1] = ($event) => $setup.selections = $event),
		"onUpdate:searchQuery": _cache[2] || (_cache[2] = ($event) => $setup.selections = [])
	}), createSlots({
		initializing: withCtx(() => [createBaseVNode("div", _hoisted_1$6, [createVNode($setup["Skeleton"], { class: "h-9 w-64" })]), createVNode($setup["Panel"], null, {
			default: withCtx(() => [$setup.slots.breadcrumbs || $setup.slots["panel-actions"] ? (openBlock(), createBlock($setup["PanelHeader"], {
				key: 0,
				class: "flex items-center justify-between gap-2 px-1!"
			}, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_2$5, [renderSlot(_ctx.$slots, "breadcrumbs")]), $setup.slots["panel-actions"] ? (openBlock(), createElementBlock("div", _hoisted_3$5, [renderSlot(_ctx.$slots, "panel-actions")])) : createCommentVNode("", true)]),
				_: 3
			})) : createCommentVNode("", true), createVNode($setup["Skeleton"], { class: "h-48 w-full" })]),
			_: 3
		})]),
		default: withCtx(() => [
			createBaseVNode("div", _hoisted_4$5, [createBaseVNode("div", _hoisted_5$3, [createVNode($setup["ListingSearch"])]), $props.allowCustomizingColumns ? (openBlock(), createBlock($setup["ListingCustomizeColumns"], { key: 0 })) : createCommentVNode("", true)]),
			createVNode($setup["Panel"], { class: "relative overflow-x-auto overscroll-x-contain" }, {
				default: withCtx(() => [
					$setup.slots.breadcrumbs || $setup.slots["panel-actions"] ? (openBlock(), createBlock($setup["PanelHeader"], {
						key: 0,
						class: "flex items-center justify-between gap-2 px-1!"
					}, {
						default: withCtx(() => [createBaseVNode("div", _hoisted_6$3, [renderSlot(_ctx.$slots, "breadcrumbs")]), $setup.slots["panel-actions"] ? (openBlock(), createElementBlock("div", _hoisted_7$3, [renderSlot(_ctx.$slots, "panel-actions")])) : createCommentVNode("", true)]),
						_: 3
					})) : createCommentVNode("", true),
					createVNode($setup["ListingTable"], null, createSlots({ _: 2 }, [renderList($setup.cellSlots, (name) => {
						return {
							name,
							fn: withCtx((slotProps) => [renderSlot(_ctx.$slots, name, normalizeProps(guardReactiveProps(slotProps)))])
						};
					})]), 1024),
					createVNode($setup["PanelFooter"], null, {
						default: withCtx(() => [createVNode($setup["ListingPagination"])]),
						_: 1
					})
				]),
				_: 3
			}),
			$props.allowSelections ? (openBlock(), createBlock($setup["CacheBulkActions"], {
				key: 0,
				label: _ctx.__($props.bulkAction),
				processing: $props.processing,
				onRun: _cache[0] || (_cache[0] = ($event) => $setup.emit("bulk-action", $event))
			}, null, 8, ["label", "processing"])) : createCommentVNode("", true)
		]),
		_: 2
	}, [renderList($setup.cellSlots, (name) => {
		return {
			name,
			fn: withCtx((slotProps) => [renderSlot(_ctx.$slots, name, normalizeProps(guardReactiveProps(slotProps)))])
		};
	})]), 1040, [
		"selections",
		"allow-bulk-actions",
		"allow-customizing-columns"
	]);
}
var CacheListing_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$6, [["render", _sfc_render$6], ["__file", "CacheListing.vue"]]);
// resources/js/components/utilities/StaticCacheExplorer.vue
var _sfc_main$5 = {
	__name: "StaticCacheExplorer",
	props: /*@__PURE__*/ mergeModels({
		url: {
			type: String,
			required: true
		},
		invalidateUrl: {
			type: String,
			required: true
		},
		cacheUrl: {
			type: String,
			required: true
		},
		sites: {
			type: Array,
			required: true
		}
	}, {
		"status": {
			type: String,
			default: "cached"
		},
		"statusModifiers": {}
	}),
	emits: /*@__PURE__*/ mergeModels([
		"invalidated",
		"cached",
		"processing"
	], ["update:status"]),
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const emit = __emit;
		const status = useModel(__props, "status");
		const listing = ref(null);
		const site = ref(null);
		const domain = ref(null);
		const domains = ref([]);
		const selections = ref([]);
		const processing = ref(false);
		const pendingAction = ref(null);
		const siteOptions = computed(() => [{
			value: null,
			label: __("All sites")
		}, ...props.sites]);
		const domainOptions = computed(() => {
			const values = [.../* @__PURE__ */ new Set([...domains.value, ...domain.value ? [domain.value] : []])];
			return [{
				value: null,
				label: __("All domains")
			}, ...values.map((value) => ({
				value,
				label: value
			}))];
		});
		watch([
			site,
			domain,
			status
		], () => {
			selections.value = [];
		});
		function requestCompleted({ response }) {
			domains.value = response.data.meta.domains;
		}
		function generatedDate(date) {
			return dateFormatter.format(date, {
				preset: "datetime",
				second: "2-digit",
				timeZoneName: "short"
			});
		}
		async function cachePages(urls, selected = false) {
			if (status.value !== "uncached" || processing.value || !urls.length) return;
			processing.value = true;
			emit("processing", true);
			const cached = [];
			const failed = [];
			try {
				for (const url of urls) {
					pendingAction.value = url;
					try {
						const { data } = await axios.post(props.cacheUrl, { url });
						cached.push({
							url,
							data
						});
					} catch (error) {
						const errors = error.response?.data?.errors;
						failed.push(errors ? Object.values(errors).flat()[0] : error.response?.data?.message || __("Unable to cache this page."));
					}
				}
				if (cached.length) {
					const cachedUrls = cached.map((page) => page.url);
					selections.value = selections.value.filter((url) => !cachedUrls.includes(url));
					toast.success(selected ? __n("statamic::messages.cache_utility_pages_cached", cached.length) : cached[0].data.message);
					listing.value.refresh();
					const result = selected ? {
						count: cached.length,
						urls: cachedUrls
					} : cached[0].data;
					emit("cached", result);
				}
				if (failed.length) toast.error(selected ? __n("statamic::messages.cache_utility_pages_cache_failed", failed.length) : failed[0]);
			} finally {
				processing.value = false;
				pendingAction.value = null;
				emit("processing", false);
			}
		}
		async function invalidate(urls, selected = false) {
			if (status.value !== "cached" || processing.value || !urls.length) return;
			processing.value = true;
			pendingAction.value = selected ? "selected" : urls[0];
			emit("processing", true);
			try {
				const { data } = await axios.post(props.invalidateUrl, {
					urls,
					site: site.value,
					domain: domain.value
				});
				toast.success(data.message);
				selections.value = [];
				listing.value.refresh();
				emit("invalidated", data);
			} catch (error) {
				const errors = error.response?.data?.errors;
				toast.error(errors ? Object.values(errors).flat()[0] : __("Unable to clear cached pages."));
			} finally {
				processing.value = false;
				pendingAction.value = null;
				emit("processing", false);
			}
		}
		const __returned__ = {
			props,
			emit,
			status,
			listing,
			site,
			domain,
			domains,
			selections,
			processing,
			pendingAction,
			siteOptions,
			domainOptions,
			requestCompleted,
			generatedDate,
			cachePages,
			invalidate,
			computed,
			ref,
			watch,
			get axios() {
				return axios;
			},
			get Button() {
				return Button_default;
			},
			get Field() {
				return Field_default;
			},
			get Select() {
				return Select_default;
			},
			get dateFormatter() {
				return dateFormatter;
			},
			get toast() {
				return toast;
			},
			CacheListing: CacheListing_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$5 = { class: "mx-auto flex max-w-page flex-col gap-4" };
var _hoisted_2$4 = { class: "flex flex-wrap items-end gap-4" };
var _hoisted_3$4 = { class: "text-[0.8125rem]" };
var _hoisted_4$4 = ["href"];
var _hoisted_5$2 = { class: "text-[0.8125rem]" };
var _hoisted_6$2 = ["href"];
var _hoisted_7$2 = ["datetime", "title"];
var _hoisted_8$2 = { key: 1 };
function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", _hoisted_1$5, [createBaseVNode("div", _hoisted_2$4, [
		createVNode($setup["Field"], {
			label: _ctx.__("Status"),
			class: "min-w-40 flex-1"
		}, {
			default: withCtx(() => [createVNode($setup["Select"], {
				modelValue: $setup.status,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.status = $event),
				options: [{
					value: "cached",
					label: _ctx.__("Cached")
				}, {
					value: "uncached",
					label: _ctx.__("Uncached")
				}],
				disabled: $setup.processing
			}, null, 8, [
				"modelValue",
				"options",
				"disabled"
			])]),
			_: 1
		}, 8, ["label"]),
		createVNode($setup["Field"], {
			label: _ctx.__("Site"),
			class: "min-w-48 flex-1"
		}, {
			default: withCtx(() => [createVNode($setup["Select"], {
				modelValue: $setup.site,
				"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.site = $event),
				options: $setup.siteOptions,
				placeholder: _ctx.__("All sites"),
				disabled: $setup.processing
			}, null, 8, [
				"modelValue",
				"options",
				"placeholder",
				"disabled"
			])]),
			_: 1
		}, 8, ["label"]),
		createVNode($setup["Field"], {
			label: _ctx.__("Domain"),
			class: "min-w-48 flex-1"
		}, {
			default: withCtx(() => [createVNode($setup["Select"], {
				modelValue: $setup.domain,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.domain = $event),
				options: $setup.domainOptions,
				placeholder: _ctx.__("All domains"),
				disabled: $setup.processing
			}, null, 8, [
				"modelValue",
				"options",
				"placeholder",
				"disabled"
			])]),
			_: 1
		}, 8, ["label"])
	]), (openBlock(), createBlock($setup["CacheListing"], {
		key: $setup.status,
		ref: "listing",
		selections: $setup.selections,
		"onUpdate:selections": _cache[3] || (_cache[3] = ($event) => $setup.selections = $event),
		url: $props.url,
		"additional-parameters": {
			site: $setup.site,
			domain: $setup.domain,
			status: $setup.status
		},
		"preferences-prefix": $setup.status === "cached" ? "cache.static_pages" : "cache.uncached_pages",
		"sort-column": $setup.status === "cached" ? "generated_at" : "url",
		"sort-direction": $setup.status === "cached" ? "desc" : "asc",
		"allow-selections": "",
		"allow-presets": false,
		"allow-select-all-matching": false,
		"per-page": 15,
		"max-selections": 1e3,
		"show-pagination-page-links": false,
		"show-pagination-per-page-selector": false,
		inert: $setup.processing || void 0,
		"bulk-action": $setup.status === "cached" ? "Clear" : "Cache",
		processing: $setup.processing,
		onBulkAction: _cache[4] || (_cache[4] = ($event) => $setup.status === "cached" ? $setup.invalidate($event, true) : $setup.cachePages($event, true)),
		"onUpdate:searchQuery": _cache[5] || (_cache[5] = ($event) => $setup.selections = []),
		onRequestCompleted: $setup.requestCompleted
	}, {
		"cell-url": withCtx(({ row }) => [createBaseVNode("div", _hoisted_3$4, [createBaseVNode("a", {
			href: row.url,
			target: "_blank",
			rel: "noopener noreferrer",
			class: "break-all font-mono"
		}, toDisplayString(row.url), 9, _hoisted_4$4)])]),
		"cell-path": withCtx(({ row }) => [createBaseVNode("div", _hoisted_5$2, [createBaseVNode("a", {
			href: row.url,
			target: "_blank",
			rel: "noopener noreferrer",
			class: "break-all font-mono"
		}, toDisplayString(row.path), 9, _hoisted_6$2)])]),
		"cell-generated_at": withCtx(({ row }) => [row.generated_at ? withDirectives((openBlock(), createElementBlock("time", {
			key: 0,
			datetime: row.generated_at,
			title: $setup.generatedDate(row.generated_at),
			class: "whitespace-nowrap tabular-nums"
		}, [createTextVNode(toDisplayString($setup.dateFormatter.format(row.generated_at, { relative: true })), 1)], 8, _hoisted_7$2)), [[_directive_tooltip, $setup.generatedDate(row.generated_at)]]) : (openBlock(), createElementBlock("span", _hoisted_8$2, "—"))]),
		"cell-site_name": withCtx(({ row }) => [createTextVNode(toDisplayString(row.site_name || _ctx.__("Unknown site")), 1)]),
		"cell-actions": withCtx(({ row }) => [$setup.status === "cached" ? (openBlock(), createBlock($setup["Button"], {
			key: 0,
			text: _ctx.__("Clear"),
			"aria-label": _ctx.__("Clear :url", { url: row.url }),
			size: "sm",
			loading: $setup.pendingAction === row.url,
			disabled: $setup.processing,
			onClick: ($event) => $setup.invalidate([row.url])
		}, null, 8, [
			"text",
			"aria-label",
			"loading",
			"disabled",
			"onClick"
		])) : (openBlock(), createBlock($setup["Button"], {
			key: 1,
			text: _ctx.__("Cache"),
			"aria-label": _ctx.__("Cache :url", { url: row.url }),
			size: "sm",
			loading: $setup.pendingAction === row.url,
			disabled: $setup.processing,
			onClick: ($event) => $setup.cachePages([row.url])
		}, null, 8, [
			"text",
			"aria-label",
			"loading",
			"disabled",
			"onClick"
		]))]),
		_: 1
	}, 8, [
		"selections",
		"url",
		"additional-parameters",
		"preferences-prefix",
		"sort-column",
		"sort-direction",
		"inert",
		"bulk-action",
		"processing"
	]))]);
}
var StaticCacheExplorer_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$5, [["render", _sfc_render$5], ["__file", "StaticCacheExplorer.vue"]]);
// resources/js/components/utilities/ImageCacheVariants.vue
var _sfc_main$4 = {
	__name: "ImageCacheVariants",
	props: {
		container: {
			type: Object,
			required: true
		},
		asset: {
			type: Object,
			required: true
		},
		url: {
			type: String,
			required: true
		},
		clearUrl: {
			type: String,
			required: true
		},
		processing: {
			type: Boolean,
			default: false
		}
	},
	emits: ["cleared", "processing"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const emit = __emit;
		const listing = ref(null);
		const dimensions = ref({});
		const failed = ref(/* @__PURE__ */ new Set());
		const pendingVariant = ref(null);
		const selections = ref([]);
		const busy = computed(() => props.processing || pendingVariant.value !== null);
		async function clearVariant(variant, selected = false) {
			const ids = selected ? variant : [variant.id];
			if (busy.value || !ids.length) return;
			pendingVariant.value = selected ? "selected" : variant.id;
			emit("processing", true);
			try {
				const { data } = await axios.post(props.clearUrl, {
					container: props.container.id,
					asset: props.asset.id,
					...selected ? { variants: ids } : { variant: variant.id }
				});
				toast.success(data.message);
				ids.forEach((id) => {
					delete dimensions.value[id];
					failed.value.delete(id);
				});
				selections.value = [];
				if (data.remaining > 0) listing.value.refresh();
				emit("cleared", data);
			} catch (error) {
				const errors = error.response?.data?.errors;
				toast.error(errors ? Object.values(errors).flat()[0] : __("Unable to clear cached variation."));
			} finally {
				pendingVariant.value = null;
				emit("processing", false);
			}
		}
		function imageLoaded(row, event) {
			const { naturalWidth, naturalHeight } = event.target;
			if (naturalWidth && naturalHeight) dimensions.value[row.id] = `${naturalWidth} × ${naturalHeight}`;
		}
		function generatedDate(date) {
			return dateFormatter.format(date, {
				preset: "datetime",
				second: "2-digit",
				timeZoneName: "short"
			});
		}
		const __returned__ = {
			props,
			emit,
			listing,
			dimensions,
			failed,
			pendingVariant,
			selections,
			busy,
			clearVariant,
			imageLoaded,
			generatedDate,
			computed,
			ref,
			get axios() {
				return axios;
			},
			get Button() {
				return Button_default;
			},
			get dateFormatter() {
				return dateFormatter;
			},
			get toast() {
				return toast;
			},
			FileIcon: FileIcon_default,
			CacheListing: CacheListing_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$4 = ["href", "aria-label"];
var _hoisted_2$3 = [
	"src",
	"onLoad",
	"onError"
];
var _hoisted_3$3 = {
	key: 1,
	class: "flex h-16 w-24 items-center justify-center"
};
var _hoisted_4$3 = [
	"src",
	"onLoad",
	"onError"
];
var _hoisted_5$1 = { class: "whitespace-nowrap tabular-nums" };
var _hoisted_6$1 = { class: "whitespace-nowrap tabular-nums" };
var _hoisted_7$1 = ["datetime", "title"];
var _hoisted_8$1 = { class: "font-mono text-[0.8125rem]" };
function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock($setup["CacheListing"], {
		ref: "listing",
		selections: $setup.selections,
		"onUpdate:selections": _cache[0] || (_cache[0] = ($event) => $setup.selections = $event),
		url: $props.url,
		"additional-parameters": {
			container: $props.container.id,
			asset: $props.asset.id
		},
		"preferences-prefix": "cache.image_variants",
		"sort-column": "size",
		"sort-direction": "desc",
		"allow-presets": false,
		"allow-customizing-columns": false,
		"per-page": 15,
		"show-pagination-page-links": false,
		"show-pagination-per-page-selector": false,
		inert: $setup.busy || void 0,
		processing: $setup.busy,
		onBulkAction: _cache[1] || (_cache[1] = ($event) => $setup.clearVariant($event, true))
	}, {
		breadcrumbs: withCtx(() => [renderSlot(_ctx.$slots, "breadcrumbs")]),
		"cell-preview": withCtx(({ row }) => [!$setup.failed.has(row.id) ? (openBlock(), createElementBlock("a", {
			key: 0,
			href: row.preview_url,
			"aria-label": _ctx.__("View cached image :filename", { filename: row.title }),
			target: "_blank",
			rel: "noopener noreferrer"
		}, [createBaseVNode("img", {
			src: row.preview_url,
			alt: "",
			width: "96",
			height: "64",
			loading: "lazy",
			class: "h-16 w-24 rounded-sm object-contain outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10",
			onLoad: ($event) => $setup.imageLoaded(row, $event),
			onError: ($event) => $setup.failed.add(row.id)
		}, null, 40, _hoisted_2$3)], 8, _hoisted_1$4)) : (openBlock(), createElementBlock("div", _hoisted_3$3, [createVNode($setup["FileIcon"], {
			extension: row.format.toLowerCase(),
			class: "size-8"
		}, null, 8, ["extension"])]))]),
		"cell-dimensions": withCtx(({ row, isColumnVisible }) => [!isColumnVisible("preview") && !$setup.failed.has(row.id) ? (openBlock(), createElementBlock("img", {
			key: 0,
			src: row.preview_url,
			alt: "",
			loading: "lazy",
			class: "sr-only",
			onLoad: ($event) => $setup.imageLoaded(row, $event),
			onError: ($event) => $setup.failed.add(row.id)
		}, null, 40, _hoisted_4$3)) : createCommentVNode("", true), createBaseVNode("div", _hoisted_5$1, toDisplayString($setup.dimensions[row.id] || "—"), 1)]),
		"cell-size": withCtx(({ row }) => [createBaseVNode("div", _hoisted_6$1, toDisplayString(row.size_formatted), 1)]),
		"cell-generated_at": withCtx(({ row }) => [withDirectives((openBlock(), createElementBlock("time", {
			datetime: row.generated_at,
			title: $setup.generatedDate(row.generated_at),
			class: "whitespace-nowrap tabular-nums"
		}, [createTextVNode(toDisplayString($setup.dateFormatter.format(row.generated_at, { relative: true })), 1)], 8, _hoisted_7$1)), [[_directive_tooltip, $setup.generatedDate(row.generated_at)]])]),
		"cell-hash": withCtx(({ row }) => [createBaseVNode("div", _hoisted_8$1, toDisplayString(row.hash), 1)]),
		"cell-actions": withCtx(({ row }) => [createVNode($setup["Button"], {
			text: _ctx.__("Clear"),
			"aria-label": _ctx.__("Clear cached variation :hash", { hash: row.hash }),
			size: "sm",
			loading: $setup.pendingVariant === row.id || $setup.pendingVariant === "selected" && $setup.selections.includes(row.id),
			disabled: $setup.busy,
			onClick: ($event) => $setup.clearVariant(row)
		}, null, 8, [
			"text",
			"aria-label",
			"loading",
			"disabled",
			"onClick"
		])]),
		_: 3
	}, 8, [
		"selections",
		"url",
		"additional-parameters",
		"inert",
		"processing"
	]);
}
var ImageCacheVariants_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$4, [["render", _sfc_render$4], ["__file", "ImageCacheVariants.vue"]]);
// resources/js/components/utilities/ImageCacheAssets.vue
var _sfc_main$3 = {
	__name: "ImageCacheAssets",
	props: /*@__PURE__*/ mergeModels({
		container: {
			type: Object,
			required: true
		},
		url: {
			type: String,
			required: true
		},
		clearUrl: {
			type: String,
			required: true
		},
		variantsUrl: {
			type: String,
			required: true
		},
		clearVariantUrl: {
			type: String,
			required: true
		}
	}, {
		"selectedAsset": {
			type: Object,
			default: null
		},
		"selectedAssetModifiers": {}
	}),
	emits: /*@__PURE__*/ mergeModels(["cleared", "processing"], ["update:selectedAsset"]),
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const emit = __emit;
		const listing = ref(null);
		const pendingAsset = ref(null);
		const selections = ref([]);
		const selectedAsset = useModel(__props, "selectedAsset");
		watch(selectedAsset, () => selections.value = []);
		function variantCleared(result) {
			if (result.remaining === 0) selectedAsset.value = null;
			else if (selectedAsset.value) selectedAsset.value = {
				...selectedAsset.value,
				count: result.remaining,
				size: result.remaining_size
			};
			listing.value.refresh();
			emit("cleared", result);
		}
		async function clear(asset, selected = false) {
			const ids = selected ? asset : [asset.id];
			if (pendingAsset.value || !ids.length || !selected && !asset.count) return;
			pendingAsset.value = selected ? "selected" : asset.id;
			emit("processing", true);
			try {
				const { data } = await axios.post(props.clearUrl, {
					container: props.container.id,
					...selected ? { assets: ids } : { asset: asset.id }
				});
				toast.success(data.message);
				selections.value = [];
				selectedAsset.value = null;
				listing.value.refresh();
				emit("cleared", data);
			} catch (error) {
				const errors = error.response?.data?.errors;
				toast.error(errors ? Object.values(errors).flat()[0] : __("Unable to clear cached images."));
			} finally {
				pendingAsset.value = null;
				emit("processing", false);
			}
		}
		const __returned__ = {
			props,
			emit,
			listing,
			pendingAsset,
			selections,
			selectedAsset,
			variantCleared,
			clear,
			ref,
			watch,
			get axios() {
				return axios;
			},
			get Button() {
				return Button_default;
			},
			get numberFormatter() {
				return numberFormatter;
			},
			get toast() {
				return toast;
			},
			AssetThumbnail: Thumbnail_default,
			ImageCacheVariants: ImageCacheVariants_default,
			CacheListing: CacheListing_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$3 = { class: "flex flex-col gap-4" };
var _hoisted_2$2 = { class: "flex min-w-0 items-center gap-3" };
var _hoisted_3$2 = { class: "whitespace-nowrap tabular-nums" };
var _hoisted_4$2 = { class: "whitespace-nowrap tabular-nums" };
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$3, [$setup.selectedAsset ? (openBlock(), createBlock($setup["ImageCacheVariants"], {
		key: 0,
		container: $props.container,
		asset: $setup.selectedAsset,
		url: $props.variantsUrl,
		"clear-url": $props.clearVariantUrl,
		processing: !!$setup.pendingAsset,
		onCleared: $setup.variantCleared,
		onProcessing: _cache[0] || (_cache[0] = ($event) => $setup.emit("processing", $event))
	}, {
		breadcrumbs: withCtx(() => [renderSlot(_ctx.$slots, "breadcrumbs")]),
		_: 3
	}, 8, [
		"container",
		"asset",
		"url",
		"clear-url",
		"processing"
	])) : createCommentVNode("", true), withDirectives(createVNode($setup["CacheListing"], {
		ref: "listing",
		selections: $setup.selections,
		"onUpdate:selections": _cache[1] || (_cache[1] = ($event) => $setup.selections = $event),
		url: $props.url,
		"additional-parameters": { container: $props.container.id },
		"preferences-prefix": "cache.image_assets",
		"sort-column": "size",
		"sort-direction": "desc",
		"allow-presets": false,
		"per-page": 15,
		"show-pagination-page-links": false,
		"show-pagination-per-page-selector": false,
		inert: $setup.pendingAsset ? true : void 0,
		processing: !!$setup.pendingAsset,
		onBulkAction: _cache[2] || (_cache[2] = ($event) => $setup.clear($event, true))
	}, createSlots({
		breadcrumbs: withCtx(() => [renderSlot(_ctx.$slots, "breadcrumbs")]),
		"cell-count": withCtx(({ row }) => [createBaseVNode("div", _hoisted_3$2, toDisplayString($setup.numberFormatter.format(row.count)), 1)]),
		"cell-size": withCtx(({ row }) => [createBaseVNode("div", _hoisted_4$2, toDisplayString(row.size_formatted), 1)]),
		"cell-actions": withCtx(({ row }) => [createVNode($setup["Button"], {
			text: _ctx.__("Clear"),
			"aria-label": _ctx.__("Clear cached images for :asset", { asset: row.path }),
			size: "sm",
			loading: $setup.pendingAsset === row.id || $setup.pendingAsset === "selected" && $setup.selections.includes(row.id),
			disabled: !!$setup.pendingAsset || !row.count,
			onClick: ($event) => $setup.clear(row)
		}, null, 8, [
			"text",
			"aria-label",
			"loading",
			"disabled",
			"onClick"
		])]),
		_: 2
	}, [renderList(["path", "title"], (field) => {
		return {
			name: `cell-${field}`,
			fn: withCtx(({ row }) => [createBaseVNode("div", _hoisted_2$2, [createVNode($setup["AssetThumbnail"], {
				asset: row,
				square: true,
				class: "size-8 shrink-0"
			}, null, 8, ["asset"]), createVNode($setup["Button"], {
				text: row[field],
				"aria-label": _ctx.__("Browse cached images for :asset", { asset: row.path }),
				variant: "ghost",
				size: "sm",
				class: normalizeClass(["-ms-3 min-w-0 shrink whitespace-normal! break-all text-start", { "font-mono": field === "path" }]),
				disabled: !!$setup.pendingAsset,
				onClick: ($event) => $setup.selectedAsset = row
			}, null, 8, [
				"text",
				"aria-label",
				"class",
				"disabled",
				"onClick"
			])])])
		};
	})]), 1032, [
		"selections",
		"url",
		"additional-parameters",
		"inert",
		"processing"
	]), [[vShow, !$setup.selectedAsset]])]);
}
var ImageCacheAssets_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$3, [["render", _sfc_render$3], ["__file", "ImageCacheAssets.vue"]]);
// resources/js/components/utilities/ImageCacheBreadcrumbs.vue
var _sfc_main$2 = {
	__name: "ImageCacheBreadcrumbs",
	props: {
		container: {
			type: Object,
			default: null
		},
		asset: {
			type: Object,
			default: null
		},
		showContainers: {
			type: Boolean,
			default: true
		},
		disabled: {
			type: Boolean,
			default: false
		}
	},
	emits: ["containers", "assets"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const __returned__ = {
			props,
			emit: __emit,
			items: computed(() => {
				const items = [];
				if (props.showContainers) items.push({
					label: __("All containers"),
					icon: "home",
					action: "containers"
				});
				if (props.container) items.push({
					label: props.container.title,
					icon: "folder-open",
					action: "assets"
				});
				if (props.asset) items.push({
					label: props.asset.title,
					title: props.asset.path
				});
				return items;
			}),
			computed,
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
};
var _hoisted_1$2 = ["aria-label"];
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("nav", {
		"aria-label": _ctx.__("Breadcrumbs"),
		class: "flex min-h-8 items-center overflow-x-auto"
	}, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.items, (item, index) => {
		return openBlock(), createElementBlock("div", {
			key: index,
			class: "flex shrink-0 items-center"
		}, [index ? (openBlock(), createBlock($setup["Icon"], {
			key: 0,
			name: "chevron-right",
			class: "size-4 text-gray-500"
		})) : createCommentVNode("", true), createVNode($setup["Button"], {
			text: item.label,
			icon: item.icon,
			title: item.title,
			"aria-current": index === $setup.items.length - 1 ? "page" : void 0,
			variant: "ghost",
			class: "h-8! gap-2",
			disabled: $props.disabled,
			onClick: ($event) => item.action && $setup.emit(item.action)
		}, null, 8, [
			"text",
			"icon",
			"title",
			"aria-current",
			"disabled",
			"onClick"
		])]);
	}), 128))], 8, _hoisted_1$2);
}
var ImageCacheBreadcrumbs_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "ImageCacheBreadcrumbs.vue"]]);
// resources/js/components/utilities/ImageCacheExplorer.vue
var _sfc_main$1 = {
	__name: "ImageCacheExplorer",
	props: {
		url: {
			type: String,
			required: true
		},
		clearUrl: {
			type: String,
			required: true
		},
		assetsUrl: {
			type: String,
			required: true
		},
		clearAssetUrl: {
			type: String,
			required: true
		},
		variantsUrl: {
			type: String,
			required: true
		},
		clearVariantUrl: {
			type: String,
			required: true
		}
	},
	emits: ["cleared", "processing"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const props = __props;
		const emit = __emit;
		const listing = ref(null);
		const pendingContainer = ref(null);
		const selectedContainer = ref(null);
		const selectedAsset = ref(null);
		const containerCount = ref(null);
		const childProcessing = ref(false);
		const selections = ref([]);
		watch(selectedContainer, () => selections.value = []);
		function processingChanged(processing) {
			childProcessing.value = processing;
			emit("processing", processing);
		}
		function showContainers() {
			selectedAsset.value = null;
			selectedContainer.value = null;
		}
		function containersLoaded({ response }) {
			const meta = response.data.meta;
			containerCount.value = meta.container_count;
			if (meta.single_container && !selectedContainer.value) selectedContainer.value = meta.single_container;
		}
		function assetCleared(result) {
			listing.value.refresh();
			emit("cleared", result);
		}
		async function clear(container, selected = false) {
			const ids = selected ? container : [container.id];
			if (pendingContainer.value || !ids.length || !selected && !container.count) return;
			pendingContainer.value = selected ? "selected" : container.id;
			emit("processing", true);
			try {
				const { data } = await axios.post(props.clearUrl, selected ? { containers: ids } : { container: container.id });
				toast.success(data.message);
				selections.value = [];
				listing.value.refresh();
				emit("cleared", data);
			} catch (error) {
				const errors = error.response?.data?.errors;
				toast.error(errors ? Object.values(errors).flat()[0] : __("Unable to clear cached images."));
			} finally {
				pendingContainer.value = null;
				emit("processing", false);
			}
		}
		const __returned__ = {
			props,
			emit,
			listing,
			pendingContainer,
			selectedContainer,
			selectedAsset,
			containerCount,
			childProcessing,
			selections,
			processingChanged,
			showContainers,
			containersLoaded,
			assetCleared,
			clear,
			ref,
			watch,
			get axios() {
				return axios;
			},
			get Button() {
				return Button_default;
			},
			get numberFormatter() {
				return numberFormatter;
			},
			get toast() {
				return toast;
			},
			ImageCacheAssets: ImageCacheAssets_default,
			ImageCacheBreadcrumbs: ImageCacheBreadcrumbs_default,
			CacheListing: CacheListing_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$1 = { class: "mx-auto max-w-page" };
var _hoisted_2$1 = { class: "font-mono" };
var _hoisted_3$1 = { class: "whitespace-nowrap tabular-nums" };
var _hoisted_4$1 = { class: "whitespace-nowrap tabular-nums" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$1, [$setup.selectedContainer ? (openBlock(), createBlock($setup["ImageCacheAssets"], {
		key: 0,
		"selected-asset": $setup.selectedAsset,
		"onUpdate:selectedAsset": _cache[1] || (_cache[1] = ($event) => $setup.selectedAsset = $event),
		container: $setup.selectedContainer,
		url: $props.assetsUrl,
		"clear-url": $props.clearAssetUrl,
		"variants-url": $props.variantsUrl,
		"clear-variant-url": $props.clearVariantUrl,
		onProcessing: $setup.processingChanged,
		onCleared: $setup.assetCleared
	}, {
		breadcrumbs: withCtx(() => [createVNode($setup["ImageCacheBreadcrumbs"], {
			container: $setup.selectedContainer,
			asset: $setup.selectedAsset,
			"show-containers": $setup.containerCount !== 1,
			disabled: !!$setup.pendingContainer || $setup.childProcessing,
			onContainers: $setup.showContainers,
			onAssets: _cache[0] || (_cache[0] = ($event) => $setup.selectedAsset = null)
		}, null, 8, [
			"container",
			"asset",
			"show-containers",
			"disabled"
		])]),
		_: 1
	}, 8, [
		"selected-asset",
		"container",
		"url",
		"clear-url",
		"variants-url",
		"clear-variant-url"
	])) : createCommentVNode("", true), withDirectives(createVNode($setup["CacheListing"], {
		ref: "listing",
		selections: $setup.selections,
		"onUpdate:selections": _cache[3] || (_cache[3] = ($event) => $setup.selections = $event),
		url: $props.url,
		"preferences-prefix": "cache.image_containers",
		"sort-column": "size",
		"sort-direction": "desc",
		"allow-presets": false,
		"per-page": 15,
		"show-pagination-page-links": false,
		"show-pagination-per-page-selector": false,
		inert: $setup.pendingContainer ? true : void 0,
		processing: !!$setup.pendingContainer,
		onBulkAction: _cache[4] || (_cache[4] = ($event) => $setup.clear($event, true)),
		onRequestCompleted: $setup.containersLoaded
	}, {
		breadcrumbs: withCtx(() => [$setup.containerCount !== null ? (openBlock(), createBlock($setup["ImageCacheBreadcrumbs"], {
			key: 0,
			container: $setup.selectedContainer,
			asset: $setup.selectedAsset,
			"show-containers": $setup.containerCount !== 1,
			disabled: !!$setup.pendingContainer || $setup.childProcessing,
			onContainers: $setup.showContainers,
			onAssets: _cache[2] || (_cache[2] = ($event) => $setup.selectedAsset = null)
		}, null, 8, [
			"container",
			"asset",
			"show-containers",
			"disabled"
		])) : createCommentVNode("", true)]),
		"cell-title": withCtx(({ row }) => [createVNode($setup["Button"], {
			text: row.title,
			"aria-label": _ctx.__("Browse cached images in :container", { container: row.title }),
			variant: "ghost",
			size: "sm",
			class: "-ms-3",
			disabled: !!$setup.pendingContainer,
			onClick: ($event) => $setup.selectedContainer = row
		}, null, 8, [
			"text",
			"aria-label",
			"disabled",
			"onClick"
		])]),
		"cell-handle": withCtx(({ row }) => [createBaseVNode("div", _hoisted_2$1, [createVNode($setup["Button"], {
			text: row.handle,
			"aria-label": _ctx.__("Browse cached images in :container", { container: row.title }),
			variant: "ghost",
			size: "sm",
			class: "-ms-3",
			disabled: !!$setup.pendingContainer,
			onClick: ($event) => $setup.selectedContainer = row
		}, null, 8, [
			"text",
			"aria-label",
			"disabled",
			"onClick"
		])])]),
		"cell-count": withCtx(({ row }) => [createBaseVNode("div", _hoisted_3$1, toDisplayString($setup.numberFormatter.format(row.count)), 1)]),
		"cell-size": withCtx(({ row }) => [createBaseVNode("div", _hoisted_4$1, toDisplayString(row.size_formatted), 1)]),
		"cell-actions": withCtx(({ row }) => [createVNode($setup["Button"], {
			text: _ctx.__("Clear"),
			"aria-label": _ctx.__("Clear cached images in :container", { container: row.title }),
			size: "sm",
			loading: $setup.pendingContainer === row.id || $setup.pendingContainer === "selected" && $setup.selections.includes(row.id),
			disabled: !!$setup.pendingContainer || !row.count,
			onClick: ($event) => $setup.clear(row)
		}, null, 8, [
			"text",
			"aria-label",
			"loading",
			"disabled",
			"onClick"
		])]),
		_: 1
	}, 8, [
		"selections",
		"url",
		"inert",
		"processing"
	]), [[vShow, !$setup.selectedContainer]])]);
}
var ImageCacheExplorer_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "ImageCacheExplorer.vue"]]);
// resources/js/pages/utilities/Cache.vue
var _sfc_main = {
	__name: "Cache",
	props: [
		"stache",
		"cache",
		"static",
		"staticCoverage",
		"images",
		"cacheSites",
		"staticPagesUrl",
		"cacheStaticPageUrl",
		"previewStaticPagesUrl",
		"imageContainersUrl",
		"clearImageContainerUrl",
		"imageAssetsUrl",
		"clearImageAssetUrl",
		"imageVariantsUrl",
		"clearImageVariantUrl",
		"clearAllUrl",
		"clearStacheUrl",
		"warmStacheUrl",
		"clearStaticUrl",
		"warmStaticUrl",
		"invalidatePagesUrl",
		"clearApplicationUrl",
		"clearImageUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const activeAction = ref(null);
		const staticUrls = ref(null);
		const invalidateStaticUrlsModal = ref(false);
		const invalidateStaticUrlsError = ref(null);
		const explorerOpen = ref(false);
		const explorerStatus = ref("cached");
		const imageExplorerOpen = ref(false);
		const invalidationSite = ref(null);
		const preview = ref(null);
		const previewError = ref(null);
		const previewLoading = ref(false);
		const linkToDocs = config.get("linkToDocs");
		const cacheSections = computed(() => {
			const sections = [
				{
					id: "static",
					title: __("Static Page Cache"),
					help: __("statamic::messages.cache_utility_static_cache_description"),
					documentation: "https://statamic.dev/static-caching",
					enabled: props.static.enabled,
					metrics: [{
						label: __("Cached pages"),
						value: numberFormatter.format(props.static.count)
					}, {
						label: __("Strategy"),
						value: props.static.strategy
					}],
					actions: [
						{
							label: __("Warm"),
							handle: "warmStatic",
							run: warmStatic
						},
						{
							label: __("Clear URLs..."),
							handle: "invalidateStaticUrls",
							run: openInvalidateStaticUrls
						},
						{
							label: __("Clear"),
							handle: "clearStatic",
							run: clearStatic
						}
					]
				},
				{
					id: "images",
					title: __("Image Cache"),
					help: __("statamic::messages.cache_utility_image_cache_description"),
					documentation: "https://statamic.dev/image-manipulation#caching",
					enabled: true,
					loading: !props.images,
					metrics: [
						{
							label: __("Variations"),
							value: props.images ? numberFormatter.format(props.images.count) : null
						},
						{
							label: __("Assets"),
							value: props.images ? numberFormatter.format(props.images.assets) : null
						},
						{
							label: __("Size"),
							value: props.images?.size
						}
					],
					actions: [{
						label: __("Clear"),
						handle: "clearImage",
						run: clearImage
					}]
				},
				{
					id: "stache",
					title: __("Content Stache"),
					help: __("statamic::messages.cache_utility_stache_description"),
					documentation: "https://statamic.dev/stache",
					enabled: true,
					metrics: [
						{
							label: __("Records"),
							value: numberFormatter.format(props.stache.records)
						},
						{
							label: __("Size"),
							value: props.stache.size
						},
						{
							label: __("Build time"),
							value: props.stache.time,
							badge: true
						},
						{
							label: __("Last rebuild"),
							value: props.stache.rebuilt,
							badge: true
						}
					].filter((metric) => metric.value),
					actions: [{
						label: __("Warm"),
						handle: "warmStache",
						run: warmStache
					}, {
						label: __("Clear"),
						handle: "clearStache",
						run: clearStache
					}]
				},
				{
					id: "application",
					title: __("Application Cache"),
					help: __("statamic::messages.cache_utility_application_cache_description"),
					documentation: "https://statamic.dev/caching#application-cache",
					enabled: true,
					metrics: [{
						label: __("Driver"),
						value: props.cache.driver
					}],
					actions: [{
						label: __("Clear"),
						handle: "clearApplication",
						run: clearApplication
					}]
				}
			];
			return (props.static.enabled ? [
				"static",
				"stache",
				"images",
				"application"
			] : [
				"stache",
				"images",
				"application",
				"static"
			]).map((id) => sections.find((section) => section.id === id));
		});
		watch([
			staticUrls,
			invalidationSite,
			invalidateStaticUrlsModal
		], ([urls, site, open], previous, onCleanup) => {
			preview.value = null;
			previewError.value = null;
			previewLoading.value = false;
			invalidateStaticUrlsError.value = null;
			const rules = urls?.split("\n").map((url) => url.trim()).filter(Boolean) ?? [];
			if (!open || !rules.length) return;
			const controller = new AbortController();
			previewLoading.value = true;
			const timeout = setTimeout(async () => {
				try {
					const { data } = await axios.post(props.previewStaticPagesUrl, {
						urls: rules,
						site
					}, { signal: controller.signal });
					if (!controller.signal.aborted) preview.value = data;
				} catch (error) {
					if (controller.signal.aborted) return;
					const errors = error.response?.data?.errors;
					previewError.value = errors ? Object.values(errors).flat()[0] : __("Unable to preview cached pages.");
				} finally {
					if (!controller.signal.aborted) previewLoading.value = false;
				}
			}, 300);
			onCleanup(() => {
				clearTimeout(timeout);
				controller.abort();
			});
		});
		function cachedPagesInvalidated() {
			router.reload({
				only: ["static", "staticCoverage"],
				preserveScroll: true
			});
		}
		function browseStaticPages(status = "cached") {
			explorerStatus.value = status;
			explorerOpen.value = true;
		}
		function cachedImagesCleared() {
			router.reload({
				only: ["images"],
				preserveScroll: true
			});
		}
		function runAction(action, url, options = {}) {
			if (activeAction.value) return;
			activeAction.value = action;
			router.post(url, options.data ?? {}, {
				preserveScroll: true,
				onSuccess: () => options.onSuccess?.(),
				onError: (errors) => {
					if (options.onError) options.onError(errors);
					else toast.error(Object.values(errors).flat()[0]);
				},
				onFinish: () => {
					activeAction.value = null;
				}
			});
		}
		function clearAll() {
			runAction("clearAll", props.clearAllUrl);
		}
		function clearStache() {
			runAction("clearStache", props.clearStacheUrl);
		}
		function warmStache() {
			runAction("warmStache", props.warmStacheUrl);
		}
		function clearStatic() {
			runAction("clearStatic", props.clearStaticUrl);
		}
		function warmStatic() {
			runAction("warmStatic", props.warmStaticUrl);
		}
		function clearApplication() {
			runAction("clearApplication", props.clearApplicationUrl);
		}
		function clearImage() {
			runAction("clearImage", props.clearImageUrl);
		}
		function openInvalidateStaticUrls() {
			if (activeAction.value) return;
			invalidateStaticUrlsError.value = null;
			invalidateStaticUrlsModal.value = true;
		}
		function invalidateStaticUrls() {
			if (previewLoading.value || !preview.value?.total) return;
			invalidateStaticUrlsError.value = null;
			runAction("invalidateStaticUrls", props.invalidatePagesUrl, {
				data: {
					urls: staticUrls.value?.split("\n"),
					site: invalidationSite.value
				},
				onSuccess: () => {
					staticUrls.value = null;
					invalidateStaticUrlsModal.value = false;
				},
				onError: (errors) => {
					invalidateStaticUrlsError.value = Object.values(errors).flat()[0];
				}
			});
		}
		const __returned__ = {
			props,
			activeAction,
			staticUrls,
			invalidateStaticUrlsModal,
			invalidateStaticUrlsError,
			explorerOpen,
			explorerStatus,
			imageExplorerOpen,
			invalidationSite,
			preview,
			previewError,
			previewLoading,
			linkToDocs,
			cacheSections,
			cachedPagesInvalidated,
			browseStaticPages,
			cachedImagesCleared,
			runAction,
			clearAll,
			clearStache,
			warmStache,
			clearStatic,
			warmStatic,
			clearApplication,
			clearImage,
			openInvalidateStaticUrls,
			invalidateStaticUrls,
			get router() {
				return router;
			},
			computed,
			ref,
			watch,
			get axios() {
				return axios;
			},
			get config() {
				return config;
			},
			get numberFormatter() {
				return numberFormatter;
			},
			get toast() {
				return toast;
			},
			Head: Head_default,
			StaticCacheExplorer: StaticCacheExplorer_default,
			ImageCacheExplorer: ImageCacheExplorer_default,
			get Header() {
				return Header_default$1;
			},
			get Button() {
				return Button_default;
			},
			get Panel() {
				return Panel_default;
			},
			get PanelHeader() {
				return Header_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Card() {
				return Card_default;
			},
			get Description() {
				return Description_default;
			},
			get Badge() {
				return Badge_default;
			},
			get Text() {
				return Text_default;
			},
			get Skeleton() {
				return Skeleton_default;
			},
			get Icon() {
				return Icon_default;
			},
			get CommandPaletteItem() {
				return Item_default;
			},
			get Dropdown() {
				return Dropdown_default;
			},
			get DropdownMenu() {
				return Menu_default;
			},
			get DropdownItem() {
				return Item_default$1;
			},
			get Textarea() {
				return Textarea_default;
			},
			get Field() {
				return Field_default;
			},
			get Modal() {
				return Modal_default;
			},
			get ModalClose() {
				return Close_default;
			},
			get Select() {
				return Select_default;
			},
			get Stack() {
				return Stack_default;
			},
			get StackContent() {
				return Content_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = {
	key: 2,
	class: "flex flex-col gap-2",
	"aria-live": "polite"
};
var _hoisted_2 = {
	key: 0,
	role: "list",
	class: "flex max-h-48 flex-col gap-1 overflow-auto rounded-lg bg-gray-50 p-3 dark:bg-gray-900"
};
var _hoisted_3 = { class: "flex items-center justify-end gap-3 pt-3 pb-1" };
var _hoisted_4 = { class: "max-w-page mx-auto" };
var _hoisted_5 = { class: "flex flex-col gap-4" };
var _hoisted_6 = { class: "flex min-w-0 items-center gap-1" };
var _hoisted_7 = { class: "flex flex-wrap items-center gap-2" };
var _hoisted_8 = { class: "flex flex-col gap-3 px-4.5 py-4" };
var _hoisted_9 = { class: "flex flex-wrap gap-x-10 gap-y-4" };
var _hoisted_10 = {
	key: 0,
	class: "flex flex-wrap gap-2"
};
var _hoisted_11 = {
	key: 0,
	class: "mt-12 mb-10 flex justify-center text-center"
};
var _hoisted_12 = { class: "flex items-center justify-between gap-4" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: [_ctx.__("Cache Manager"), _ctx.__("Utilities")] }, null, 8, ["title"]),
		createVNode($setup["Modal"], {
			title: _ctx.__("Clear URLs"),
			open: $setup.invalidateStaticUrlsModal,
			"onUpdate:open": _cache[2] || (_cache[2] = ($event) => $setup.invalidateStaticUrlsModal = $event)
		}, {
			default: withCtx(() => [createBaseVNode("form", {
				class: "flex flex-col gap-3",
				onSubmit: withModifiers($setup.invalidateStaticUrls, ["prevent"])
			}, [
				$props.cacheSites.length > 1 ? (openBlock(), createBlock($setup["Field"], {
					key: 0,
					label: _ctx.__("Site")
				}, {
					default: withCtx(() => [createVNode($setup["Select"], {
						modelValue: $setup.invalidationSite,
						"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.invalidationSite = $event),
						options: [{
							value: null,
							label: _ctx.__("All sites")
						}, ...$props.cacheSites],
						placeholder: _ctx.__("All sites"),
						disabled: !!$setup.activeAction
					}, null, 8, [
						"modelValue",
						"options",
						"placeholder",
						"disabled"
					])]),
					_: 1
				}, 8, ["label"])) : createCommentVNode("", true),
				createVNode($setup["Field"], {
					label: _ctx.__("URLs"),
					instructions: _ctx.__("statamic::messages.cache_utility_urls_instructions"),
					error: $setup.invalidateStaticUrlsError || $setup.previewError
				}, {
					default: withCtx(() => [createVNode($setup["Textarea"], {
						modelValue: $setup.staticUrls,
						"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.staticUrls = $event),
						name: "urls",
						autocomplete: "off",
						autocapitalize: "off",
						spellcheck: false,
						placeholder: "/about\n/blog/*",
						disabled: !!$setup.activeAction
					}, null, 8, ["modelValue", "disabled"])]),
					_: 1
				}, 8, [
					"label",
					"instructions",
					"error"
				]),
				$setup.previewLoading ? (openBlock(), createBlock($setup["Description"], {
					key: 1,
					role: "status"
				}, {
					default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Loading preview...")), 1)]),
					_: 1
				})) : $setup.preview ? (openBlock(), createElementBlock("div", _hoisted_1, [
					createVNode($setup["Text"], { as: "p" }, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__n("statamic::messages.cache_utility_matches", $setup.preview.total)), 1)]),
						_: 1
					}),
					$setup.preview.total ? (openBlock(), createElementBlock("ul", _hoisted_2, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.preview.data, (page) => {
						return openBlock(), createElementBlock("li", {
							key: page.url,
							class: "break-all font-mono text-sm"
						}, toDisplayString(page.url), 1);
					}), 128))])) : createCommentVNode("", true),
					$setup.preview.total > $setup.preview.data.length ? (openBlock(), createBlock($setup["Description"], { key: 1 }, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Showing :count of :total matches.", {
							count: $setup.preview.data.length,
							total: $setup.preview.total
						})), 1)]),
						_: 1
					})) : createCommentVNode("", true)
				])) : createCommentVNode("", true),
				createBaseVNode("div", _hoisted_3, [createVNode($setup["ModalClose"], { "as-child": "" }, {
					default: withCtx(() => [createVNode($setup["Button"], {
						variant: "ghost",
						disabled: !!$setup.activeAction,
						text: _ctx.__("Cancel")
					}, null, 8, ["disabled", "text"])]),
					_: 1
				}), createVNode($setup["Button"], {
					type: "submit",
					variant: "primary",
					loading: $setup.activeAction === "invalidateStaticUrls",
					disabled: !!$setup.activeAction || $setup.previewLoading || !$setup.preview?.total,
					text: _ctx.__("Clear")
				}, null, 8, [
					"loading",
					"disabled",
					"text"
				])])
			], 32)]),
			_: 1
		}, 8, ["title", "open"]),
		createVNode($setup["Stack"], {
			open: $setup.explorerOpen,
			"onUpdate:open": _cache[5] || (_cache[5] = ($event) => $setup.explorerOpen = $event),
			title: _ctx.__("Static Page Cache"),
			"before-close": () => !$setup.activeAction,
			size: "full"
		}, {
			default: withCtx(() => [$setup.explorerOpen ? (openBlock(), createBlock($setup["StaticCacheExplorer"], {
				key: 0,
				status: $setup.explorerStatus,
				"onUpdate:status": _cache[3] || (_cache[3] = ($event) => $setup.explorerStatus = $event),
				url: $props.staticPagesUrl,
				"cache-url": $props.cacheStaticPageUrl,
				"invalidate-url": $props.invalidatePagesUrl,
				sites: $props.cacheSites,
				onProcessing: _cache[4] || (_cache[4] = ($event) => $setup.activeAction = $event ? "staticPageAction" : null),
				onInvalidated: $setup.cachedPagesInvalidated,
				onCached: $setup.cachedPagesInvalidated
			}, null, 8, [
				"status",
				"url",
				"cache-url",
				"invalidate-url",
				"sites"
			])) : createCommentVNode("", true)]),
			_: 1
		}, 8, [
			"open",
			"title",
			"before-close"
		]),
		createVNode($setup["Stack"], {
			open: $setup.imageExplorerOpen,
			"onUpdate:open": _cache[7] || (_cache[7] = ($event) => $setup.imageExplorerOpen = $event),
			title: _ctx.__("Cached images"),
			"before-close": () => !$setup.activeAction,
			size: "full"
		}, {
			default: withCtx(() => [createVNode($setup["StackContent"], { class: "pt-0!" }, {
				default: withCtx(() => [$setup.imageExplorerOpen ? (openBlock(), createBlock($setup["ImageCacheExplorer"], {
					key: 0,
					url: $props.imageContainersUrl,
					"clear-url": $props.clearImageContainerUrl,
					"assets-url": $props.imageAssetsUrl,
					"clear-asset-url": $props.clearImageAssetUrl,
					"variants-url": $props.imageVariantsUrl,
					"clear-variant-url": $props.clearImageVariantUrl,
					onProcessing: _cache[6] || (_cache[6] = ($event) => $setup.activeAction = $event ? "clearImageContainer" : null),
					onCleared: $setup.cachedImagesCleared
				}, null, 8, [
					"url",
					"clear-url",
					"assets-url",
					"clear-asset-url",
					"variants-url",
					"clear-variant-url"
				])) : createCommentVNode("", true)]),
				_: 1
			})]),
			_: 1
		}, 8, [
			"open",
			"title",
			"before-close"
		]),
		createBaseVNode("div", _hoisted_4, [
			createVNode($setup["Header"], {
				title: _ctx.__("Cache Manager"),
				icon: "cache"
			}, {
				default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
					category: "Actions",
					text: _ctx.__("Clear all caches"),
					icon: "live-preview",
					action: $setup.clearAll,
					when: () => !$setup.activeAction && !$setup.explorerOpen && !$setup.imageExplorerOpen
				}, {
					default: withCtx(({ text }) => [createVNode($setup["Button"], {
						text,
						loading: $setup.activeAction === "clearAll",
						disabled: !!$setup.activeAction,
						onClick: $setup.clearAll
					}, null, 8, [
						"text",
						"loading",
						"disabled"
					])]),
					_: 1
				}, 8, ["text", "when"])]),
				_: 1
			}, 8, ["title"]),
			createBaseVNode("div", _hoisted_5, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.cacheSections, (section) => {
				return openBlock(), createBlock($setup["Panel"], {
					key: section.id,
					class: "mb-0!"
				}, {
					default: withCtx(() => [createVNode($setup["PanelHeader"], { class: "flex min-h-10 flex-wrap items-center justify-between gap-x-4 gap-y-3" }, {
						default: withCtx(() => [createBaseVNode("div", _hoisted_6, [
							createVNode($setup["Heading"], { level: 2 }, {
								default: withCtx(() => [createTextVNode(toDisplayString(section.title), 1)]),
								_: 2
							}, 1024),
							withDirectives(createVNode($setup["Button"], {
								"aria-label": section.help,
								icon: "info-square",
								"icon-only": "",
								variant: "subtle",
								size: "sm",
								onClick: _cache[8] || (_cache[8] = ($event) => $event.currentTarget.focus())
							}, null, 8, ["aria-label"]), [[_directive_tooltip, section.help]]),
							!section.enabled ? (openBlock(), createBlock($setup["Badge"], {
								key: 0,
								text: _ctx.__("Disabled")
							}, null, 8, ["text"])) : createCommentVNode("", true)
						]), createBaseVNode("div", _hoisted_7, [section.enabled ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [section.id === "static" ? (openBlock(), createBlock($setup["Dropdown"], {
							key: 0,
							align: "end"
						}, {
							trigger: withCtx(() => [createVNode($setup["Button"], {
								text: _ctx.__("Browse pages"),
								"icon-append": "chevron-down",
								size: "sm",
								disabled: !!$setup.activeAction
							}, null, 8, ["text", "disabled"])]),
							default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
								default: withCtx(() => [createVNode($setup["DropdownItem"], {
									text: _ctx.__("Cached pages"),
									onClick: _cache[9] || (_cache[9] = ($event) => $setup.browseStaticPages("cached"))
								}, null, 8, ["text"]), createVNode($setup["DropdownItem"], {
									text: _ctx.__("Uncached pages"),
									onClick: _cache[10] || (_cache[10] = ($event) => $setup.browseStaticPages("uncached"))
								}, null, 8, ["text"])]),
								_: 1
							})]),
							_: 1
						})) : section.id === "images" ? (openBlock(), createBlock($setup["Button"], {
							key: 1,
							text: _ctx.__("Browse images"),
							"aria-label": _ctx.__("Browse cached images"),
							"icon-append": "chevron-right",
							size: "sm",
							disabled: !!$setup.activeAction,
							onClick: _cache[11] || (_cache[11] = ($event) => $setup.imageExplorerOpen = true)
						}, null, 8, [
							"text",
							"aria-label",
							"disabled"
						])) : createCommentVNode("", true), (openBlock(true), createElementBlock(Fragment, null, renderList(section.actions, (action) => {
							return openBlock(), createBlock($setup["Button"], {
								key: action.handle,
								text: action.label,
								size: "sm",
								loading: $setup.activeAction === action.handle,
								disabled: !!$setup.activeAction,
								onClick: ($event) => action.run()
							}, null, 8, [
								"text",
								"loading",
								"disabled",
								"onClick"
							]);
						}), 128))], 64)) : createCommentVNode("", true)])]),
						_: 2
					}, 1024), section.enabled ? (openBlock(), createBlock($setup["Card"], {
						key: 0,
						inset: ""
					}, {
						default: withCtx(() => [createBaseVNode("div", _hoisted_8, [
							createBaseVNode("dl", _hoisted_9, [(openBlock(true), createElementBlock(Fragment, null, renderList(section.metrics.filter((metric) => !metric.badge), (metric) => {
								return openBlock(), createElementBlock("div", {
									key: metric.label,
									class: "flex flex-col gap-1"
								}, [createBaseVNode("dt", null, [createVNode($setup["Description"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(metric.label), 1)]),
									_: 2
								}, 1024)]), createBaseVNode("dd", null, [section.loading ? (openBlock(), createBlock($setup["Skeleton"], {
									key: 0,
									class: "h-8 w-20"
								})) : (openBlock(), createBlock($setup["Heading"], {
									key: 1,
									size: "2xl",
									class: "tabular-nums"
								}, {
									default: withCtx(() => [createTextVNode(toDisplayString(metric.value), 1)]),
									_: 2
								}, 1024))])]);
							}), 128))]),
							section.metrics.some((metric) => metric.badge) ? (openBlock(), createElementBlock("div", _hoisted_10, [(openBlock(true), createElementBlock(Fragment, null, renderList(section.metrics.filter((metric) => metric.badge), (metric) => {
								return openBlock(), createBlock($setup["Badge"], {
									key: metric.label,
									prepend: metric.label,
									class: "tabular-nums"
								}, {
									default: withCtx(() => [createTextVNode(toDisplayString(metric.value), 1)]),
									_: 2
								}, 1032, ["prepend"]);
							}), 128))])) : createCommentVNode("", true),
							section.id === "static" && section.enabled && $props.staticCoverage ? (openBlock(), createBlock($setup["Description"], {
								key: 1,
								class: "tabular-nums"
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__n("statamic::messages.cache_utility_coverage", $props.staticCoverage.total, {
									cached: $setup.numberFormatter.format($props.staticCoverage.cached),
									total: $setup.numberFormatter.format($props.staticCoverage.total)
								})), 1)]),
								_: 1
							})) : createCommentVNode("", true)
						])]),
						_: 2
					}, 1024)) : createCommentVNode("", true)]),
					_: 2
				}, 1024);
			}), 128))]),
			$setup.linkToDocs ? (openBlock(), createElementBlock("div", _hoisted_11, [createVNode($setup["Dropdown"], {
				align: "center",
				side: "top"
			}, {
				trigger: withCtx(() => [createVNode($setup["Badge"], {
					as: "button",
					type: "button",
					text: _ctx.__("Learn about caching"),
					"icon-append": "chevron-down",
					pill: ""
				}, null, 8, ["text"])]),
				default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
					default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.cacheSections, (section) => {
						return openBlock(), createBlock($setup["DropdownItem"], {
							key: section.id,
							href: section.documentation,
							target: "_blank",
							rel: "noopener noreferrer"
						}, {
							default: withCtx(() => [createBaseVNode("div", _hoisted_12, [createBaseVNode("span", null, toDisplayString(section.title), 1), createVNode($setup["Icon"], {
								name: "external-link",
								class: "size-3! text-gray-500",
								"aria-hidden": "true"
							})])]),
							_: 2
						}, 1032, ["href"]);
					}), 128))]),
					_: 1
				})]),
				_: 1
			})])) : createCommentVNode("", true)
		])
	], 64);
}
var Cache_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Cache.vue"]]);
export { Cache_default as default };
