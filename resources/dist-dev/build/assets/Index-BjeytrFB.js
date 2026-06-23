import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default } from "./index.esm-B4SStoAz.js";
import { Gn as Panel_default, Jn as Heading_default, Ut as Header_default, Yn as Card_default, ft as Cell_default, li as Button_default, lt as Row_default, n as DocsCallout_default, pt as Table_default, r as Item_default, ui as Badge_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/updater/Index.vue
var _sfc_main = {
	__name: "Index",
	props: [
		"requestError",
		"statamic",
		"addons"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const __returned__ = {
			props,
			securityUpdateAvailable: computed(() => props.statamic.security || props.addons.some((addon) => addon.security)),
			get Link() {
				return link_default;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Card() {
				return Card_default;
			},
			get Panel() {
				return Panel_default;
			},
			get Table() {
				return Table_default;
			},
			get TableRow() {
				return Row_default;
			},
			get TableCell() {
				return Cell_default;
			},
			get Badge() {
				return Badge_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Button() {
				return Button_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			get CommandPaletteItem() {
				return Item_default;
			},
			computed
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "max-w-page mx-auto" };
var _hoisted_2 = {
	key: 1,
	class: "space-y-6"
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Updates") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Updates"),
			icon: "updates"
		}, {
			actions: withCtx(() => [$setup.securityUpdateAvailable ? (openBlock(), createBlock($setup["Badge"], {
				key: 0,
				text: _ctx.__("Security update available"),
				color: "red",
				size: "lg",
				icon: "alert-warning-exclamation-mark"
			}, null, 8, ["text"])) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"]),
		$props.requestError ? (openBlock(), createBlock($setup["Card"], {
			key: 0,
			class: "w-full space-y-4 flex items-center justify-between"
		}, {
			default: withCtx(() => [createVNode($setup["Heading"], {
				size: "lg",
				class: "mb-0!",
				text: _ctx.__("statamic::messages.outpost_issue_try_later"),
				icon: "warning-diamond"
			}, null, 8, ["text"]), createVNode($setup["Button"], {
				href: _ctx.cp_url("updater"),
				variant: "primary"
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Try Again")), 1)]),
				_: 1
			}, 8, ["href"])]),
			_: 1
		})) : (openBlock(), createElementBlock("section", _hoisted_2, [createVNode($setup["Panel"], { heading: _ctx.__("Core") }, {
			default: withCtx(() => [createVNode($setup["Card"], { class: "py-0!" }, {
				default: withCtx(() => [createVNode($setup["Table"], { class: "w-full" }, {
					default: withCtx(() => [createVNode($setup["TableRow"], null, {
						default: withCtx(() => [
							createVNode($setup["TableCell"], { class: "w-64 font-bold" }, {
								default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
									category: "Actions",
									text: [
										_ctx.__("Updates"),
										_ctx.__("Core"),
										_ctx.__("Statamic")
									],
									icon: "updates",
									url: _ctx.cp_url("updater/statamic"),
									prioritize: ""
								}, {
									default: withCtx(({ url }) => [createVNode($setup["Link"], {
										href: url,
										textContent: toDisplayString(_ctx.__("Statamic"))
									}, null, 8, ["href", "textContent"])]),
									_: 1
								}, 8, ["text", "url"])]),
								_: 1
							}),
							createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString($props.statamic.currentVersion), 1)]),
								_: 1
							}),
							$props.statamic.availableUpdatesCount ? (openBlock(), createBlock($setup["TableCell"], {
								key: 0,
								class: "text-right"
							}, {
								default: withCtx(() => [createVNode($setup["Badge"], {
									size: "sm",
									text: _ctx.__n("1 update|:count updates", $props.statamic.availableUpdatesCount),
									color: $props.statamic.security ? "red" : "amber"
								}, null, 8, ["text", "color"])]),
								_: 1
							})) : (openBlock(), createBlock($setup["TableCell"], {
								key: 1,
								class: "text-right"
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Up to date")), 1)]),
								_: 1
							}))
						]),
						_: 1
					})]),
					_: 1
				})]),
				_: 1
			})]),
			_: 1
		}, 8, ["heading"]), $props.addons.length ? (openBlock(), createBlock($setup["Panel"], {
			key: 0,
			heading: _ctx.__("Addons")
		}, {
			default: withCtx(() => [createVNode($setup["Card"], { class: "py-0!" }, {
				default: withCtx(() => [createVNode($setup["Table"], { class: "w-full" }, {
					default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.addons, (addon) => {
						return openBlock(), createBlock($setup["TableRow"], { key: addon.slug }, {
							default: withCtx(() => [
								createVNode($setup["TableCell"], { class: "w-64 font-bold" }, {
									default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
										category: "Actions",
										text: [
											_ctx.__("Updates"),
											_ctx.__("Addons"),
											addon.name
										],
										icon: "updates",
										url: _ctx.cp_url(`updater/${addon.slug}`)
									}, {
										default: withCtx(({ url }) => [createVNode($setup["Link"], {
											href: url,
											textContent: toDisplayString(addon.name)
										}, null, 8, ["href", "textContent"])]),
										_: 2
									}, 1032, ["text", "url"])]),
									_: 2
								}, 1024),
								createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(addon.version), 1)]),
									_: 2
								}, 1024),
								addon.availableUpdatesCount ? (openBlock(), createBlock($setup["TableCell"], {
									key: 0,
									class: "text-right"
								}, {
									default: withCtx(() => [createVNode($setup["Badge"], {
										size: "sm",
										text: _ctx.__n("1 update|:count updates", addon.availableUpdatesCount),
										color: addon.security ? "red" : "amber"
									}, null, 8, ["text", "color"])]),
									_: 2
								}, 1024)) : (openBlock(), createBlock($setup["TableCell"], {
									key: 1,
									class: "text-right"
								}, {
									default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Up to date")), 1)]),
									_: 1
								}))
							]),
							_: 2
						}, 1024);
					}), 128))]),
					_: 1
				})]),
				_: 1
			})]),
			_: 1
		}, 8, ["heading"])) : createCommentVNode("", true)])),
		!$props.requestError ? (openBlock(), createBlock($setup["DocsCallout"], {
			key: 2,
			topic: _ctx.__("Updates"),
			url: "updating"
		}, null, 8, ["topic"])) : createCommentVNode("", true)
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
