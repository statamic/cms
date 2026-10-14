import { n as __exportAll } from "./rolldown-runtime-B-1-B7_t.js";
import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, G as renderSlot, S as createTextVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, p as Teleport, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-B-BfxmJA.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default, o as usePage } from "./index.esm-B-zRpAhf.js";
import { si as Button_default } from "./ui-DPzuvYkS.js";
//#region resources/js/pages/forms/Layout.vue
var Layout_exports = /* @__PURE__ */ __exportAll({ default: () => Layout_default });
var _sfc_main = {
	__name: "Layout",
	setup(__props, { expose: __expose }) {
		__expose();
		const page = usePage();
		const form = computed(() => page.props.form);
		const can = (ability) => page.props.can?.[ability] ?? false;
		const navItems = computed(() => [
			can("editFields") && {
				label: __("Edit"),
				href: cp_url(`forms/${form.value.handle}/builder`)
			},
			can("editFields") && {
				label: __("Logic"),
				href: cp_url(`forms/${form.value.handle}/logic`)
			},
			can("edit") && {
				label: __("Connect"),
				href: cp_url(`forms/${form.value.handle}/connect`)
			},
			can("viewSubmissions") && {
				label: __("Results"),
				href: cp_url(`forms/${form.value.handle}/submissions`)
			},
			can("edit") && {
				label: __("Configure"),
				href: cp_url(`forms/${form.value.handle}/edit`)
			}
		].filter(Boolean));
		const currentPath = computed(() => new URL(page.url, window.location.origin).pathname);
		const isActive = (href) => currentPath.value.includes(new URL(href, window.location.origin).pathname);
		const activeSectionLabel = computed(() => {
			return navItems.value.find((item) => isActive(item.href))?.label ?? navItems.value[0]?.label ?? "";
		});
		const closeMobileNavPopover = () => {
			document.getElementById("popover-global-header-nav")?.hidePopover?.();
		};
		const __returned__ = {
			page,
			form,
			can,
			navItems,
			currentPath,
			isActive,
			activeSectionLabel,
			closeMobileNavPopover,
			computed,
			get Link() {
				return link_default;
			},
			get usePage() {
				return usePage;
			},
			get Button() {
				return Button_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "flex items-center justify-center" };
var _hoisted_2 = {
	key: 0,
	class: "global-header-nav-popover lg:hidden"
};
var _hoisted_3 = {
	id: "popover-global-header-nav",
	popover: "",
	class: "global-header-nav-popover__menu"
};
var _hoisted_4 = ["aria-label"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [(openBlock(), createBlock(Teleport, { to: "#global-header-slot" }, [createBaseVNode("div", _hoisted_1, [$setup.navItems.length > 1 ? (openBlock(), createElementBlock("div", _hoisted_2, [createVNode($setup["Button"], {
		id: "anchor-global-header-nav",
		variant: "ghost",
		class: "text-white! border-0! shadow-none! [&_svg]:text-white/80",
		popovertarget: "popover-global-header-nav",
		text: $setup.activeSectionLabel,
		"icon-append": "chevron-down"
	}, null, 8, ["text"]), createBaseVNode("nav", _hoisted_3, [createBaseVNode("ul", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.navItems, (navItem) => {
		return openBlock(), createElementBlock("li", { key: `m-${navItem.href}` }, [createVNode($setup["Link"], {
			href: navItem.href,
			class: normalizeClass({ active: $setup.isActive(navItem.href) }),
			"aria-current": $setup.isActive(navItem.href) ? "page" : void 0,
			onClick: $setup.closeMobileNavPopover
		}, {
			default: withCtx(() => [createTextVNode(toDisplayString(navItem.label), 1)]),
			_: 2
		}, 1032, [
			"href",
			"class",
			"aria-current"
		])]);
	}), 128))])])])) : createCommentVNode("", true), $setup.navItems.length > 1 ? (openBlock(), createElementBlock("nav", {
		key: 1,
		class: "global-header-nav hidden lg:block",
		"aria-label": _ctx.__("form_navigation")
	}, [createBaseVNode("ul", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.navItems, (navItem) => {
		return openBlock(), createElementBlock("li", { key: navItem.href }, [createVNode($setup["Link"], {
			href: navItem.href,
			class: normalizeClass({ active: $setup.isActive(navItem.href) }),
			"aria-current": $setup.isActive(navItem.href) ? "page" : void 0
		}, {
			default: withCtx(() => [createTextVNode(toDisplayString(navItem.label), 1)]),
			_: 2
		}, 1032, [
			"href",
			"class",
			"aria-current"
		])]);
	}), 128))])], 8, _hoisted_4)) : createCommentVNode("", true)])])), renderSlot(_ctx.$slots, "default")], 64);
}
var Layout_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Layout.vue"]]);
//#endregion
export { Layout_exports as n, Layout_default as t };
