import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, _ as createBlock, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { Ci as Icon_default, Kt as Menu_default, Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, qt as Item_default$1, r as Item_default, tn as Item_default$2 } from "./ui-BfR7XN6t.js";
import { c as useStatamicPageProps, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/forms/Index.vue
var _sfc_main = {
	__name: "Index",
	props: [
		"forms",
		"initialColumns",
		"actionUrl",
		"canCreate",
		"createUrl",
		"configureEmailUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const { isPro } = useStatamicPageProps();
		const isEmpty = computed(() => props.forms.length === 0);
		const reloadPage = () => router.reload();
		const __returned__ = {
			props,
			isPro,
			isEmpty,
			reloadPage,
			computed,
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get CommandPaletteItem() {
				return Item_default;
			},
			get EmptyStateMenu() {
				return Menu_default;
			},
			get EmptyStateItem() {
				return Item_default$1;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Listing() {
				return Listing_default;
			},
			get DropdownItem() {
				return Item_default$2;
			},
			get useStatamicPageProps() {
				return useStatamicPageProps;
			},
			get Link() {
				return link_default;
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
var _hoisted_2 = { class: "py-8 pt-16 text-center" };
var _hoisted_3 = { class: "text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Forms") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [$setup.isEmpty ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [
		createBaseVNode("header", _hoisted_2, [createBaseVNode("h1", _hoisted_3, [createVNode($setup["Icon"], {
			name: "collections",
			class: "size-5 text-gray-500"
		}), createTextVNode(" " + toDisplayString(_ctx.__("Forms")), 1)])]),
		createVNode($setup["EmptyStateMenu"], { heading: _ctx.__("statamic::messages.form_configure_intro") }, {
			default: withCtx(() => [$props.canCreate ? (openBlock(), createBlock($setup["EmptyStateItem"], {
				key: 0,
				href: $props.createUrl,
				icon: "forms",
				heading: _ctx.__("Create Form"),
				description: _ctx.__("statamic::messages.form_create_description")
			}, null, 8, [
				"href",
				"heading",
				"description"
			])) : createCommentVNode("", true), createVNode($setup["EmptyStateItem"], {
				href: $props.configureEmailUrl,
				icon: "mail-settings",
				heading: _ctx.__("Configure Email"),
				description: _ctx.__("statamic::messages.form_configure_email_description")
			}, null, 8, [
				"href",
				"heading",
				"description"
			])]),
			_: 1
		}, 8, ["heading"]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Forms"),
			url: "forms"
		}, null, 8, ["topic"])
	], 64)) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [
		createVNode($setup["Header"], {
			title: _ctx.__("Forms"),
			icon: "forms"
		}, {
			default: withCtx(() => [$setup.isPro && $props.canCreate ? (openBlock(), createBlock($setup["CommandPaletteItem"], {
				key: 0,
				category: "Actions",
				text: _ctx.__("Create Form"),
				icon: "forms",
				url: $props.createUrl
			}, {
				default: withCtx(({ text, url }) => [createVNode($setup["Button"], {
					href: url,
					text,
					variant: "primary"
				}, null, 8, ["href", "text"])]),
				_: 1
			}, 8, ["text", "url"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"]),
		createVNode($setup["Listing"], {
			items: $props.forms,
			columns: $props.initialColumns,
			"action-url": $props.actionUrl,
			onRefreshing: $setup.reloadPage
		}, {
			"cell-title": withCtx(({ row: form }) => [createVNode($setup["Link"], { href: form.show_url }, {
				default: withCtx(() => [createTextVNode(toDisplayString(form.title), 1)]),
				_: 2
			}, 1032, ["href"])]),
			"prepended-row-actions": withCtx(({ row: form }) => [form.can_edit ? (openBlock(), createBlock($setup["DropdownItem"], {
				key: 0,
				text: _ctx.__("Configure"),
				href: form.edit_url,
				icon: "cog"
			}, null, 8, ["text", "href"])) : createCommentVNode("", true), form.can_edit_blueprint ? (openBlock(), createBlock($setup["DropdownItem"], {
				key: 1,
				icon: "blueprint-edit",
				text: _ctx.__("Edit Blueprint"),
				href: form.blueprint_url
			}, null, 8, ["text", "href"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, [
			"items",
			"columns",
			"action-url"
		]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Forms"),
			url: "forms"
		}, null, 8, ["topic"])
	], 64))])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
