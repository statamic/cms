import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, F as onBeforeUnmount, K as resolveComponent, S as createTextVNode, _ as createBlock, _t as ref, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { G as axios, c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, tn as Item_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/blueprints/ScopedIndex.vue
var _sfc_main = {
	__name: "ScopedIndex",
	props: [
		"blueprints",
		"reorderUrl",
		"createUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const rows = ref(props.blueprints);
		const hasBeenReordered = ref(false);
		const reorderable = computed(() => rows.value.length > 1);
		const columns = ref([
			{
				label: __("Title"),
				field: "title"
			},
			{
				label: __("Handle"),
				field: "handle"
			},
			{
				label: __("Fields"),
				field: "fields"
			}
		]);
		watch(() => props.blueprints, (newRows) => rows.value = newRows, { deep: true });
		function reordered(newRows) {
			rows.value = newRows;
			hasBeenReordered.value = true;
		}
		function saveOrder() {
			const order = rows.value.map((blueprint) => blueprint.handle);
			axios.post(props.reorderUrl, { order }).then(() => {
				Statamic.$toast.success(__("Blueprints successfully reordered"));
				hasBeenReordered.value = false;
			}).catch(() => Statamic.$toast.error(__("Something went wrong")));
		}
		const reloadPage = () => router.reload();
		const saveKeyBinding = ref(Statamic.$keys.bindGlobal(["mod+s"], (e) => {
			if (hasBeenReordered.value) {
				e.preventDefault();
				saveOrder();
			}
		}));
		onBeforeUnmount(() => saveKeyBinding.value?.destroy());
		const __returned__ = {
			props,
			rows,
			hasBeenReordered,
			reorderable,
			columns,
			reordered,
			saveOrder,
			reloadPage,
			saveKeyBinding,
			ref,
			computed,
			watch,
			onBeforeUnmount,
			get Link() {
				return link_default;
			},
			get router() {
				return router;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get DropdownItem() {
				return Item_default;
			},
			get Listing() {
				return Listing_default;
			},
			get axios() {
				return axios;
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
var _hoisted_2 = { class: "flex items-center" };
var _hoisted_3 = ["textContent"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_resource_deleter = resolveComponent("resource-deleter");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Blueprints") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Blueprints"),
			icon: "blueprints"
		}, {
			default: withCtx(() => [$setup.reorderable ? (openBlock(), createBlock($setup["Button"], {
				key: 0,
				disabled: !$setup.hasBeenReordered,
				onClick: $setup.saveOrder
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Save Order")), 1)]),
				_: 1
			}, 8, ["disabled"])) : createCommentVNode("", true), createVNode($setup["Button"], {
				text: _ctx.__("Create Blueprint"),
				href: $props.createUrl,
				variant: "primary"
			}, null, 8, ["text", "href"])]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["Listing"], {
			items: $setup.rows,
			columns: $setup.columns,
			"allow-search": false,
			"allow-customizing-columns": false,
			reorderable: $setup.reorderable,
			sortable: false,
			"allow-actions-while-reordering": true,
			onRefreshing: $setup.reloadPage,
			onReordered: $setup.reordered
		}, {
			"cell-title": withCtx(({ row: blueprint }) => [createBaseVNode("div", _hoisted_2, [
				createBaseVNode("div", { class: normalizeClass(["little-dot me-2", [blueprint.hidden ? "hollow" : "bg-green-500"]]) }, null, 2),
				createVNode($setup["Link"], {
					href: blueprint.edit_url,
					textContent: toDisplayString(_ctx.__(blueprint.title))
				}, null, 8, ["href", "textContent"]),
				createVNode(_component_resource_deleter, {
					ref: `deleter_${blueprint.id}`,
					resource: blueprint,
					reload: ""
				}, null, 8, ["resource"])
			])]),
			"cell-handle": withCtx(({ value }) => [createBaseVNode("span", {
				class: "font-mono text-xs",
				textContent: toDisplayString(value)
			}, null, 8, _hoisted_3)]),
			"prepended-row-actions": withCtx(({ row: blueprint }) => [createVNode($setup["DropdownItem"], {
				text: _ctx.__("Edit"),
				icon: "edit",
				href: blueprint.edit_url
			}, null, 8, ["text", "href"]), createVNode($setup["DropdownItem"], {
				text: _ctx.__("Delete"),
				icon: "trash",
				variant: "destructive",
				onClick: ($event) => _ctx.$refs[`deleter_${blueprint.id}`].confirm()
			}, null, 8, ["text", "onClick"])]),
			_: 1
		}, 8, [
			"items",
			"columns",
			"reorderable"
		]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Blueprints"),
			url: "blueprints"
		}, null, 8, ["topic"])
	])], 64);
}
var ScopedIndex_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "ScopedIndex.vue"]]);
//#endregion
export { ScopedIndex_default as default };
