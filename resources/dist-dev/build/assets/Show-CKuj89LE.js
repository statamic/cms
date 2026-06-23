import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, _ as createBlock, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, Ut as Header_default, i as Listing_default, nn as Dropdown_default, tn as Item_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/taxonomies/Show.vue
var _sfc_main = {
	components: {
		Link: link_default,
		Head: Head_default,
		Header: Header_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default,
		Listing: Listing_default
	},
	props: [
		"taxonomy",
		"taxonomyTitle",
		"blueprints",
		"site",
		"columns",
		"filters",
		"canCreate",
		"createUrl",
		"actionUrl",
		"sortColumn",
		"sortDirection",
		"taxonomyEditUrl",
		"taxonomyBlueprintsUrl",
		"canEdit",
		"canDelete",
		"canConfigureFields",
		"deleteUrl",
		"createLabel"
	],
	data() {
		return {
			preferencesPrefix: `taxonomies.${this.taxonomy}`,
			requestUrl: cp_url(`taxonomies/${this.taxonomy}/terms`)
		};
	},
	mounted() {
		this.addToCommandPalette();
	},
	methods: {
		deleteTaxonomy() {
			this.$refs.deleter.confirm();
		},
		addToCommandPalette() {
			Statamic.$commandPalette.add({
				when: () => this.canCreate,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Create Term"),
				icon: "taxonomies",
				url: this.createUrl,
				prioritize: true
			});
			Statamic.$commandPalette.add({
				when: () => this.canEdit,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Configure Taxonomy"),
				icon: "cog",
				url: this.taxonomyEditUrl
			});
			Statamic.$commandPalette.add({
				when: () => this.canConfigureFields,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Edit Blueprints"),
				icon: "blueprint-edit",
				url: this.taxonomyBlueprintsUrl
			});
			Statamic.$commandPalette.add({
				when: () => this.canDelete,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Delete Taxonomy"),
				icon: "trash",
				action: () => this.deleteTaxonomy()
			});
		}
	}
};
var _hoisted_1 = { class: "flex items-center" };
var _hoisted_2 = { class: "text-2xs font-mono" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_create_term_button = resolveComponent("create-term-button");
	const _component_Header = resolveComponent("Header");
	const _component_resource_deleter = resolveComponent("resource-deleter");
	const _component_Link = resolveComponent("Link");
	const _component_Listing = resolveComponent("Listing");
	return openBlock(), createElementBlock("div", null, [
		createVNode(_component_Head, { title: [_ctx.__($props.taxonomyTitle), _ctx.__("Taxonomies")] }, null, 8, ["title"]),
		createVNode(_component_Header, { title: _ctx.__($props.taxonomyTitle) }, {
			default: withCtx(() => [createVNode(_component_Dropdown, null, {
				default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
					default: withCtx(() => [
						$props.canEdit ? (openBlock(), createBlock(_component_DropdownItem, {
							key: 0,
							text: _ctx.__("Configure Taxonomy"),
							icon: "cog",
							href: $props.taxonomyEditUrl
						}, null, 8, ["text", "href"])) : createCommentVNode("", true),
						$props.canConfigureFields ? (openBlock(), createBlock(_component_DropdownItem, {
							key: 1,
							text: _ctx.__("Edit Blueprints"),
							icon: "blueprint-edit",
							href: $props.taxonomyBlueprintsUrl
						}, null, 8, ["text", "href"])) : createCommentVNode("", true),
						$props.canDelete ? (openBlock(), createBlock(_component_DropdownItem, {
							key: 2,
							text: _ctx.__("Delete Taxonomy"),
							icon: "trash",
							variant: "destructive",
							onClick: _cache[0] || (_cache[0] = ($event) => $options.deleteTaxonomy())
						}, null, 8, ["text"])) : createCommentVNode("", true)
					]),
					_: 1
				})]),
				_: 1
			}), $props.canCreate ? (openBlock(), createBlock(_component_create_term_button, {
				key: 0,
				url: $props.createUrl,
				text: $props.createLabel,
				blueprints: $props.blueprints
			}, null, 8, [
				"url",
				"text",
				"blueprints"
			])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"]),
		$props.canDelete ? (openBlock(), createBlock(_component_resource_deleter, {
			key: 0,
			ref: "deleter",
			"resource-title": $props.taxonomyTitle,
			route: $props.deleteUrl,
			redirect: "/cp/taxonomies"
		}, null, 8, ["resource-title", "route"])) : createCommentVNode("", true),
		createVNode(_component_Listing, {
			ref: "listing",
			url: $data.requestUrl,
			columns: $props.columns,
			"action-url": $props.actionUrl,
			"action-context": { taxonomy: $props.taxonomy },
			"sort-column": $props.sortColumn,
			"sort-direction": $props.sortDirection,
			"preferences-prefix": $data.preferencesPrefix,
			filters: $props.filters,
			"push-query": ""
		}, {
			"cell-title": withCtx(({ row: term }) => [createBaseVNode("div", _hoisted_1, [createVNode(_component_Link, { href: term.edit_url }, {
				default: withCtx(() => [createTextVNode(toDisplayString(term.title), 1)]),
				_: 2
			}, 1032, ["href"])])]),
			"cell-slug": withCtx(({ row: term }) => [createBaseVNode("span", _hoisted_2, toDisplayString(term.slug), 1)]),
			"prepended-row-actions": withCtx(({ row: term }) => [term.has_template ? (openBlock(), createBlock(_component_DropdownItem, {
				key: 0,
				text: _ctx.__("Visit URL"),
				href: term.permalink,
				target: "_blank",
				icon: "eye"
			}, null, 8, ["text", "href"])) : createCommentVNode("", true), createVNode(_component_DropdownItem, {
				text: _ctx.__("Edit"),
				href: term.edit_url,
				icon: "edit"
			}, null, 8, ["text", "href"])]),
			_: 1
		}, 8, [
			"url",
			"columns",
			"action-url",
			"action-context",
			"sort-column",
			"sort-direction",
			"preferences-prefix",
			"filters"
		])
	]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
