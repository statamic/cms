import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, S as createTextVNode, W as renderList, _ as createBlock, at as withDirectives, f as Fragment, g as createBaseVNode, it as withCtx, q as resolveDirective, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Gn as Panel_default$1, Hn as Panel_default, Jn as Heading_default, Ut as Header_default, Yn as Card_default, ft as Cell_default, li as Button_default, lt as Row_default, n as DocsCallout_default, pt as Table_default, ui as Badge_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/Licensing.vue
var _sfc_main = {
	__name: "Licensing",
	props: [
		"requestError",
		"site",
		"statamic",
		"addons",
		"unlistedAddons",
		"configCached",
		"addToCartUrl",
		"usingLicenseKeyFile",
		"refreshUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			props: __props,
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get Card() {
				return Card_default;
			},
			get CardPanel() {
				return Panel_default;
			},
			get Panel() {
				return Panel_default$1;
			},
			get Heading() {
				return Heading_default;
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
			get DocsCallout() {
				return DocsCallout_default;
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
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
var _hoisted_2 = {
	key: 1,
	class: "space-y-6"
};
var _hoisted_3 = ["innerHTML"];
var _hoisted_4 = ["innerHTML"];
var _hoisted_5 = { class: "flex gap-2 sm:gap-3" };
var _hoisted_6 = {
	key: 0,
	class: "text-xs"
};
var _hoisted_7 = { class: "flex gap-2 sm:gap-3" };
var _hoisted_8 = {
	key: 0,
	class: "text-pink"
};
var _hoisted_9 = { class: "flex gap-2 sm:gap-3" };
var _hoisted_10 = { class: "font-bold" };
var _hoisted_11 = ["href"];
var _hoisted_12 = {
	key: 0,
	class: "ms-auto"
};
var _hoisted_13 = { class: "flex gap-2 sm:gap-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode($setup["Head"], { title: [_ctx.__("Licensing"), _ctx.__("Utilities")] }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__("Licensing"),
			icon: "license"
		}, {
			default: withCtx(() => [
				createVNode($setup["Button"], {
					href: $props.site.url,
					target: "_blank",
					text: _ctx.__("Manage on statamic.com")
				}, null, 8, ["href", "text"]),
				$props.addToCartUrl ? (openBlock(), createBlock($setup["Button"], {
					key: 0,
					href: $props.addToCartUrl,
					target: "_blank",
					text: _ctx.__("Buy Licenses")
				}, null, 8, ["href", "text"])) : createCommentVNode("", true),
				withDirectives(createVNode($setup["Button"], {
					href: $props.refreshUrl,
					variant: "primary",
					text: _ctx.__("Sync")
				}, null, 8, ["href", "text"]), [[_directive_tooltip, _ctx.__("statamic::messages.licensing_sync_instructions")]])
			]),
			_: 1
		}, 8, ["title"]),
		$props.requestError ? (openBlock(), createBlock($setup["Card"], {
			key: 0,
			class: "w-full space-y-4 flex items-center justify-between"
		}, {
			default: withCtx(() => [createVNode($setup["Heading"], {
				size: "lg",
				class: "mb-0!",
				text: $props.usingLicenseKeyFile ? _ctx.__("statamic::messages.outpost_license_key_error") : _ctx.__("statamic::messages.outpost_issue_try_later"),
				icon: "warning-diamond"
			}, null, 8, ["text"]), createVNode($setup["Button"], {
				href: $props.refreshUrl,
				variant: "primary"
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Try Again")), 1)]),
				_: 1
			}, 8, ["href"])]),
			_: 1
		})) : (openBlock(), createElementBlock("section", _hoisted_2, [
			$props.configCached ? (openBlock(), createBlock($setup["CardPanel"], {
				key: 0,
				heading: _ctx.__("Configuration is cached")
			}, {
				default: withCtx(() => [createBaseVNode("p", {
					class: "text-gray-700 text-sm",
					innerHTML: _ctx.__("statamic::messages.licensing_config_cached_warning")
				}, null, 8, _hoisted_3)]),
				_: 1
			}, 8, ["heading"])) : createCommentVNode("", true),
			$props.site.usesIncorrectKeyFormat ? (openBlock(), createBlock($setup["CardPanel"], {
				key: 1,
				heading: _ctx.__("statamic::messages.licensing_incorrect_key_format_heading")
			}, {
				default: withCtx(() => [createBaseVNode("p", {
					class: "text-gray-700 text-sm",
					innerHTML: _ctx.__("statamic::messages.licensing_incorrect_key_format_body")
				}, null, 8, _hoisted_4)]),
				_: 1
			}, 8, ["heading"])) : createCommentVNode("", true),
			createVNode($setup["Panel"], { heading: _ctx.__("Site") }, {
				default: withCtx(() => [createVNode($setup["Card"], { class: "py-0!" }, {
					default: withCtx(() => [createVNode($setup["Table"], { class: "w-full" }, {
						default: withCtx(() => [createVNode($setup["TableRow"], null, {
							default: withCtx(() => [
								createVNode($setup["TableCell"], { class: "w-64 font-bold" }, {
									default: withCtx(() => [createBaseVNode("div", _hoisted_5, [createBaseVNode("span", { class: normalizeClass(["little-dot mt-[0.45rem]", $props.site.valid ? "bg-green-500" : "bg-red-500 dark:bg-red-600"]) }, null, 2), createTextVNode(" " + toDisplayString($props.site.key ?? _ctx.__("No license key")), 1)])]),
									_: 1
								}),
								createVNode($setup["TableCell"], { class: "relative" }, {
									default: withCtx(() => [createTextVNode(toDisplayString($props.site.domain?.url ?? "") + " ", 1), $props.site.hasMultipleDomains ? (openBlock(), createElementBlock("span", _hoisted_6, " (" + toDisplayString(_ctx.__("and :count more", { count: $props.site.additionalDomainCount })) + ") ", 1)) : createCommentVNode("", true)]),
									_: 1
								}),
								createVNode($setup["TableCell"], { class: "text-end" }, {
									default: withCtx(() => [$props.site.invalidReason ? (openBlock(), createBlock($setup["Badge"], {
										key: 0,
										color: "red"
									}, {
										default: withCtx(() => [createTextVNode(toDisplayString($props.site.invalidReason), 1)]),
										_: 1
									})) : createCommentVNode("", true)]),
									_: 1
								})
							]),
							_: 1
						})]),
						_: 1
					})]),
					_: 1
				})]),
				_: 1
			}, 8, ["heading"]),
			createVNode($setup["Panel"], { heading: _ctx.__("Core") }, {
				default: withCtx(() => [createVNode($setup["Card"], { class: "py-0!" }, {
					default: withCtx(() => [createVNode($setup["Table"], { class: "w-full" }, {
						default: withCtx(() => [createVNode($setup["TableRow"], null, {
							default: withCtx(() => [
								createVNode($setup["TableCell"], { class: "w-64 font-bold" }, {
									default: withCtx(() => [createBaseVNode("div", _hoisted_7, [createBaseVNode("span", { class: normalizeClass(["little-dot mt-[0.45rem]", $props.statamic.valid ? "bg-green-500" : "bg-red-500"]) }, null, 2), createBaseVNode("span", null, [createTextVNode(toDisplayString(_ctx.__("Statamic")) + " ", 1), $props.statamic.pro ? (openBlock(), createElementBlock("span", _hoisted_8, toDisplayString(_ctx.__("Pro")), 1)) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [createTextVNode(toDisplayString(_ctx.__("Free")), 1)], 64))])])]),
									_: 1
								}),
								createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString($props.statamic.version), 1)]),
									_: 1
								}),
								createVNode($setup["TableCell"], { class: "text-end" }, {
									default: withCtx(() => [$props.statamic.invalidReason ? (openBlock(), createBlock($setup["Badge"], {
										key: 0,
										color: "red"
									}, {
										default: withCtx(() => [createTextVNode(toDisplayString($props.statamic.invalidReason), 1)]),
										_: 1
									})) : createCommentVNode("", true)]),
									_: 1
								})
							]),
							_: 1
						})]),
						_: 1
					})]),
					_: 1
				})]),
				_: 1
			}, 8, ["heading"]),
			$props.addons.length ? (openBlock(), createBlock($setup["Panel"], {
				key: 2,
				heading: _ctx.__("Addons")
			}, {
				default: withCtx(() => [createVNode($setup["Card"], { class: "py-0!" }, {
					default: withCtx(() => [createVNode($setup["Table"], { class: "w-full" }, {
						default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.addons, (addon) => {
							return openBlock(), createBlock($setup["TableRow"], { key: addon.name }, {
								default: withCtx(() => [
									createVNode($setup["TableCell"], { class: "w-64" }, {
										default: withCtx(() => [createBaseVNode("div", _hoisted_9, [
											createBaseVNode("span", { class: normalizeClass(["little-dot mt-[0.45rem]", addon.valid ? "bg-green-" : "bg-red-500"]) }, null, 2),
											createBaseVNode("span", _hoisted_10, [createBaseVNode("a", {
												href: addon.marketplaceUrl,
												class: "underline"
											}, toDisplayString(addon.name), 9, _hoisted_11)]),
											addon.edition ? (openBlock(), createElementBlock("div", _hoisted_12, [createVNode($setup["Badge"], null, {
												default: withCtx(() => [createTextVNode(toDisplayString(addon.edition), 1)]),
												_: 2
											}, 1024)])) : createCommentVNode("", true)
										])]),
										_: 2
									}, 1024),
									createVNode($setup["TableCell"], null, {
										default: withCtx(() => [createTextVNode(toDisplayString(addon.version), 1)]),
										_: 2
									}, 1024),
									createVNode($setup["TableCell"], { class: "text-red-600 text-end" }, {
										default: withCtx(() => [createTextVNode(toDisplayString(addon.invalidReason), 1)]),
										_: 2
									}, 1024)
								]),
								_: 2
							}, 1024);
						}), 128))]),
						_: 1
					})]),
					_: 1
				})]),
				_: 1
			}, 8, ["heading"])) : createCommentVNode("", true),
			$props.unlistedAddons.length ? (openBlock(), createBlock($setup["Panel"], {
				key: 3,
				heading: _ctx.__("Unlisted Addons")
			}, {
				default: withCtx(() => [createVNode($setup["Card"], { class: "py-0!" }, {
					default: withCtx(() => [createVNode($setup["Table"], { class: "w-full" }, {
						default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.unlistedAddons, (addon) => {
							return openBlock(), createBlock($setup["TableRow"], { key: addon.name }, {
								default: withCtx(() => [createVNode($setup["TableCell"], { class: "w-64" }, {
									default: withCtx(() => [createBaseVNode("div", _hoisted_13, [_cache[0] || (_cache[0] = createBaseVNode("span", { class: "little-dot mt-[0.45rem] bg-green-500" }, null, -1)), createTextVNode(" " + toDisplayString(addon.name), 1)])]),
									_: 2
								}, 1024), createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(addon.version), 1)]),
									_: 2
								}, 1024)]),
								_: 2
							}, 1024);
						}), 128))]),
						_: 1
					})]),
					_: 1
				})]),
				_: 1
			}, 8, ["heading"])) : createCommentVNode("", true)
		])),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Licensing"),
			url: "licensing"
		}, null, 8, ["topic"])
	]);
}
var Licensing_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Licensing.vue"]]);
//#endregion
export { Licensing_default as default };
