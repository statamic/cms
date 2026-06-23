import { B as openBlock, C as createVNode, K as resolveComponent, g as createBaseVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { n as DocsCallout_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/assets/Browse.vue
var _sfc_main = {
	components: {
		Head: Head_default,
		DocsCallout: DocsCallout_default
	},
	props: [
		"container",
		"folder",
		"columns",
		"filters",
		"canCreateContainers",
		"createContainerUrl",
		"editing"
	],
	data() {
		return {
			path: this.folder,
			selectedAssets: []
		};
	},
	computed: { headTitle() {
		const title = __(this.container.title);
		const section = __("Assets");
		return title === section ? section : [title, section];
	} },
	mounted() {
		this.bindBrowserNavigation();
	},
	methods: {
		/**
		* Bind browser navigation features
		*
		* This will initialize the state for using the history API to allow
		* navigation back and forth through folders using browser buttons.
		*/
		bindBrowserNavigation() {
			window.history.replaceState({
				container: { ...this.container },
				path: this.path
			}, "");
			window.onpopstate = (e) => {
				this.path = e.state.path;
			};
		},
		editAsset(asset) {
			event.preventDefault();
			this.$refs.browser.edit(asset.id);
		},
		/**
		* When a user has navigated to another folder.
		*/
		navigate(path) {
			let previousPath = this.path;
			this.path = path;
			this.pushState();
			if (!path.includes("/edit") && !previousPath.includes("/edit")) this.selectedAssets = [];
		},
		/**
		* Push a new state onto the browser's history
		*/
		pushState() {
			let url = cp_url("assets/browse/" + this.container.id);
			if (this.path !== "/") url += "/" + this.path;
			window.history.pushState({
				container: { ...this.container },
				path: this.path
			}, "", url);
		},
		/**
		* When selections are changed, we need them reflected here.
		*/
		updateSelections(selections) {
			this.selectedAssets = selections;
		}
	}
};
var _hoisted_1 = { class: "h-full" };
var _hoisted_2 = { class: "flex justify-between py-8" };
var _hoisted_3 = { class: "flex gap-2 sm:gap-3" };
var _hoisted_4 = { class: "flex justify-between py-3" };
var _hoisted_5 = { class: "flex justify-between" };
var _hoisted_6 = { class: "starting-style-transition starting-style-transition--delay" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_ui_skeleton = resolveComponent("ui-skeleton");
	const _component_DocsCallout = resolveComponent("DocsCallout");
	const _component_asset_browser = resolveComponent("asset-browser");
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode(_component_Head, { title: $options.headTitle }, null, 8, ["title"]), createVNode(_component_asset_browser, {
		ref: "browser",
		"can-create-containers": $props.canCreateContainers,
		"create-container-url": $props.createContainerUrl,
		container: $props.container,
		"initial-per-page": _ctx.$config.get("paginationSize"),
		"initial-editing-asset-id": $props.editing,
		"selected-path": $data.path,
		"selected-assets": $data.selectedAssets,
		"initial-columns": $props.columns,
		filters: $props.filters,
		onNavigated: $options.navigate,
		onSelectionsUpdated: $options.updateSelections,
		onEditAsset: $options.editAsset
	}, {
		initializing: withCtx(() => [
			createBaseVNode("div", _hoisted_2, [createVNode(_component_ui_skeleton, { class: "h-8 w-95" }), createBaseVNode("div", _hoisted_3, [
				createVNode(_component_ui_skeleton, { class: "h-10 w-26" }),
				createVNode(_component_ui_skeleton, { class: "h-10 w-36" }),
				createVNode(_component_ui_skeleton, { class: "h-10 w-20" })
			])]),
			createBaseVNode("div", _hoisted_4, [createVNode(_component_ui_skeleton, { class: "h-9 w-95" }), createVNode(_component_ui_skeleton, { class: "h-9 w-10" })]),
			createBaseVNode("div", _hoisted_5, [createVNode(_component_ui_skeleton, { class: "h-30 w-full" })])
		]),
		footer: withCtx(() => [createBaseVNode("div", _hoisted_6, [createVNode(_component_DocsCallout, {
			topic: _ctx.__("Assets"),
			url: "assets"
		}, null, 8, ["topic"])])]),
		_: 1
	}, 8, [
		"can-create-containers",
		"create-container-url",
		"container",
		"initial-per-page",
		"initial-editing-asset-id",
		"selected-path",
		"selected-assets",
		"initial-columns",
		"filters",
		"onNavigated",
		"onSelectionsUpdated",
		"onEditAsset"
	])]);
}
var Browse_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Browse.vue"]]);
//#endregion
export { Browse_default as default };
