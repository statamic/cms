import { B as openBlock, C as createVNode, _ as createBlock, _t as ref, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { r as head_default } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, Ut as Header_default, li as Button_default, nn as Dropdown_default, tn as Item_default } from "./ui-BfR7XN6t.js";
import { D as ResourceDeleter_default } from "./index-Bu3QBQkS.js";
import { t as Listing_default } from "./Listing-BeRZIBQe.js";
//#region resources/js/pages/user-groups/Show.vue
var _sfc_main = {
	__name: "Show",
	props: {
		group: {
			type: Object,
			required: true
		},
		filters: {
			type: Object,
			required: true
		},
		listingConfig: {
			type: Object,
			required: true
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const deleter = ref(null);
		function handleDelete() {
			deleter.value?.confirm();
		}
		const __returned__ = {
			props,
			deleter,
			handleDelete,
			get Head() {
				return head_default;
			},
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
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
			UserListing: Listing_default,
			ResourceDeleter: ResourceDeleter_default,
			ref
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
		createVNode($setup["Head"], { title: _ctx.__($props.group.title) }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__($props.group.title),
			icon: "groups"
		}, {
			actions: withCtx(() => [$props.group.canEdit || $props.group.canDelete ? (openBlock(), createBlock($setup["Dropdown"], { key: 0 }, {
				trigger: withCtx(() => [createVNode($setup["Button"], {
					icon: "dots",
					variant: "ghost",
					"aria-label": _ctx.__("Actions")
				}, null, 8, ["aria-label"])]),
				default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
					default: withCtx(() => [$props.group.canEdit ? (openBlock(), createBlock($setup["DropdownItem"], {
						key: 0,
						text: _ctx.__("Edit Group"),
						icon: "edit",
						href: $props.group.editUrl
					}, null, 8, ["text", "href"])) : createCommentVNode("", true), $props.group.canDelete ? (openBlock(), createBlock($setup["DropdownItem"], {
						key: 1,
						text: _ctx.__("Delete Group"),
						icon: "trash",
						onClick: $setup.handleDelete
					}, null, 8, ["text"])) : createCommentVNode("", true)]),
					_: 1
				})]),
				_: 1
			})) : createCommentVNode("", true), $props.group.canEdit ? (openBlock(), createBlock($setup["Button"], {
				key: 1,
				variant: "primary",
				text: _ctx.__("Edit Group"),
				href: $props.group.editUrl
			}, null, 8, ["text", "href"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["UserListing"], {
			"listing-key": $props.listingConfig.listingKey,
			group: $props.listingConfig.groupId,
			filters: $props.filters,
			"allow-filter-presets": $props.listingConfig.allowFilterPresets,
			"action-url": $props.listingConfig.actionUrl
		}, null, 8, [
			"listing-key",
			"group",
			"filters",
			"allow-filter-presets",
			"action-url"
		]),
		$props.group.canDelete ? (openBlock(), createBlock($setup["ResourceDeleter"], {
			key: 0,
			ref: "deleter",
			"resource-title": $props.group.title,
			route: $props.group.deleteUrl,
			redirect: "/cp/user-groups"
		}, null, 8, ["resource-title", "route"])) : createCommentVNode("", true)
	]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
