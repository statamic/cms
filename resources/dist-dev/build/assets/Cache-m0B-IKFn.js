import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, At as Close_default, Gn as Panel_default, Jn as Heading_default, Jt as ErrorMessage_default, Ut as Header_default, Yn as Card_default, in as Description_default, it as Textarea_default, jt as Modal_default, li as Button_default, n as DocsCallout_default, nn as Dropdown_default, qn as Header_default$1, r as Item_default, tn as Item_default$1, ui as Badge_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/Cache.vue
var _sfc_main = {
	__name: "Cache",
	props: [
		"stache",
		"cache",
		"static",
		"images",
		"clearAllUrl",
		"clearStacheUrl",
		"warmStacheUrl",
		"clearStaticUrl",
		"invalidatePagesUrl",
		"clearApplicationUrl",
		"clearImageUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		function clearAll() {
			router.post(props.clearAllUrl);
		}
		function clearStache() {
			router.post(props.clearStacheUrl);
		}
		function warmStache() {
			router.post(props.warmStacheUrl);
		}
		function clearStatic() {
			router.post(props.clearStaticUrl);
		}
		function clearApplication() {
			router.post(props.clearApplicationUrl);
		}
		function clearImage() {
			router.post(props.clearImageUrl);
		}
		const staticUrls = ref(null);
		const invalidateStaticUrlsModal = ref(false);
		const isInvalidatingStaticUrls = ref(false);
		const invalidateStaticUrlsError = ref(null);
		function invalidateStaticUrls() {
			isInvalidatingStaticUrls.value = true;
			invalidateStaticUrlsError.value = null;
			router.post(props.invalidatePagesUrl, { urls: staticUrls.value?.split("\n") }, {
				onSuccess: () => {
					staticUrls.value = null;
					invalidateStaticUrlsModal.value = false;
				},
				onError: (errors) => invalidateStaticUrlsError.value = errors.urls,
				onFinish: () => isInvalidatingStaticUrls.value = false
			});
		}
		const __returned__ = {
			props,
			clearAll,
			clearStache,
			warmStache,
			clearStatic,
			clearApplication,
			clearImage,
			staticUrls,
			invalidateStaticUrlsModal,
			isInvalidatingStaticUrls,
			invalidateStaticUrlsError,
			invalidateStaticUrls,
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
			get Panel() {
				return Panel_default;
			},
			get PanelHeader() {
				return Header_default$1;
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
			get DocsCallout() {
				return DocsCallout_default;
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
			get ErrorMessage() {
				return ErrorMessage_default;
			},
			get Modal() {
				return Modal_default;
			},
			get ModalClose() {
				return Close_default;
			},
			ref
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "max-w-page mx-auto" };
var _hoisted_2 = { class: "grid grid-cols-1 lg:grid-cols-2 gap-6" };
var _hoisted_3 = { class: "flex gap-2" };
var _hoisted_4 = { class: "flex flex-wrap gap-2 mt-3" };
var _hoisted_5 = {
	key: 0,
	class: "flex gap-2"
};
var _hoisted_6 = { class: "flex items-center justify-end space-x-3 pt-3 pb-1" };
var _hoisted_7 = { class: "flex flex-wrap gap-2 mt-3" };
var _hoisted_8 = { class: "flex gap-2" };
var _hoisted_9 = { class: "flex flex-wrap gap-2 mt-3" };
var _hoisted_10 = { class: "flex gap-2" };
var _hoisted_11 = { class: "flex flex-wrap gap-2 mt-3" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: [_ctx.__("Cache Manager"), _ctx.__("Utilities")] }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Cache Manager"),
			icon: "cache"
		}, {
			default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
				category: "Actions",
				text: _ctx.__("Clear All"),
				icon: "live-preview",
				action: $setup.clearAll,
				prioritize: ""
			}, {
				default: withCtx(({ text }) => [createVNode($setup["Button"], {
					text,
					variant: "primary",
					onClick: $setup.clearAll
				}, null, 8, ["text"])]),
				_: 1
			}, 8, ["text"])]),
			_: 1
		}, 8, ["title"]),
		createBaseVNode("div", _hoisted_2, [
			createVNode($setup["Panel"], { class: "h-full flex flex-col" }, {
				default: withCtx(() => [createVNode($setup["PanelHeader"], { class: "flex items-center justify-between min-h-10" }, {
					default: withCtx(() => [createVNode($setup["Heading"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Content Stache")), 1)]),
						_: 1
					}), createBaseVNode("div", _hoisted_3, [createVNode($setup["CommandPaletteItem"], {
						category: "Actions",
						text: [_ctx.__("Warm"), _ctx.__("Content Stache")],
						icon: "fire-flame-burn-hot",
						action: $setup.warmStache
					}, {
						default: withCtx(({ text }) => [createVNode($setup["Button"], {
							text: _ctx.__("Warm"),
							size: "sm",
							onClick: $setup.warmStache
						}, null, 8, ["text"])]),
						_: 1
					}, 8, ["text"]), createVNode($setup["CommandPaletteItem"], {
						category: "Actions",
						text: [_ctx.__("Clear"), _ctx.__("Content Stache")],
						icon: "live-preview",
						action: $setup.clearStache
					}, {
						default: withCtx(({ text }) => [createVNode($setup["Button"], {
							text: _ctx.__("Clear"),
							size: "sm",
							onClick: $setup.clearStache
						}, null, 8, ["text"])]),
						_: 1
					}, 8, ["text"])])]),
					_: 1
				}), createVNode($setup["Card"], { class: "flex-1" }, {
					default: withCtx(() => [createVNode($setup["Description"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("statamic::messages.cache_utility_stache_description")), 1)]),
						_: 1
					}), createBaseVNode("div", _hoisted_4, [
						createVNode($setup["Badge"], { prepend: _ctx.__("Records") }, {
							default: withCtx(() => [createTextVNode(toDisplayString($props.stache.records), 1)]),
							_: 1
						}, 8, ["prepend"]),
						$props.stache.size ? (openBlock(), createBlock($setup["Badge"], {
							key: 0,
							prepend: _ctx.__("Size")
						}, {
							default: withCtx(() => [createTextVNode(toDisplayString($props.stache.size), 1)]),
							_: 1
						}, 8, ["prepend"])) : createCommentVNode("", true),
						$props.stache.time ? (openBlock(), createBlock($setup["Badge"], {
							key: 1,
							prepend: _ctx.__("Build time")
						}, {
							default: withCtx(() => [createTextVNode(toDisplayString($props.stache.time), 1)]),
							_: 1
						}, 8, ["prepend"])) : createCommentVNode("", true),
						$props.stache.rebuilt ? (openBlock(), createBlock($setup["Badge"], {
							key: 2,
							prepend: _ctx.__("Last rebuild")
						}, {
							default: withCtx(() => [createTextVNode(toDisplayString($props.stache.rebuilt), 1)]),
							_: 1
						}, 8, ["prepend"])) : createCommentVNode("", true)
					])]),
					_: 1
				})]),
				_: 1
			}),
			createVNode($setup["Panel"], { class: "h-full flex flex-col" }, {
				default: withCtx(() => [createVNode($setup["PanelHeader"], { class: "flex items-center justify-between min-h-10" }, {
					default: withCtx(() => [createVNode($setup["Heading"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Static Page Cache")), 1)]),
						_: 1
					}), $props.static.enabled ? (openBlock(), createElementBlock("div", _hoisted_5, [createVNode($setup["CommandPaletteItem"], {
						category: "Actions",
						text: [_ctx.__("Clear"), _ctx.__("Static Page Cache")],
						icon: "live-preview",
						action: $setup.invalidateStaticUrls
					}, {
						default: withCtx(() => [createVNode($setup["Dropdown"], { align: "end" }, {
							trigger: withCtx(() => [createVNode($setup["Button"], {
								text: _ctx.__("Invalidate"),
								size: "sm",
								"icon-append": "chevron-down"
							}, null, 8, ["text"])]),
							default: withCtx(() => [createVNode($setup["DropdownMenu"], null, {
								default: withCtx(() => [createVNode($setup["DropdownItem"], {
									text: _ctx.__("Everything"),
									icon: "layers-stacks",
									onClick: $setup.clearStatic
								}, null, 8, ["text"]), createVNode($setup["DropdownItem"], {
									text: _ctx.__("Specific URLs"),
									icon: "link",
									onClick: _cache[0] || (_cache[0] = ($event) => $setup.invalidateStaticUrlsModal = true)
								}, null, 8, ["text"])]),
								_: 1
							})]),
							_: 1
						})]),
						_: 1
					}, 8, ["text"]), createVNode($setup["Modal"], {
						title: _ctx.__("Invalidate Static Cache"),
						open: $setup.invalidateStaticUrlsModal,
						"onUpdate:open": _cache[2] || (_cache[2] = ($event) => $setup.invalidateStaticUrlsModal = $event)
					}, {
						default: withCtx(() => [
							createBaseVNode("p", null, toDisplayString(_ctx.__("Specify the URLs you want to invalidate. One line per URL.")), 1),
							createVNode($setup["Textarea"], {
								class: "font-mono",
								modelValue: $setup.staticUrls,
								"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.staticUrls = $event),
								disabled: $setup.isInvalidatingStaticUrls
							}, null, 8, ["modelValue", "disabled"]),
							$setup.invalidateStaticUrlsError ? (openBlock(), createBlock($setup["ErrorMessage"], {
								key: 0,
								text: $setup.invalidateStaticUrlsError,
								class: "mt-2"
							}, null, 8, ["text"])) : createCommentVNode("", true),
							createBaseVNode("div", _hoisted_6, [createVNode($setup["ModalClose"], { asChild: "" }, {
								default: withCtx(() => [createVNode($setup["Button"], {
									variant: "ghost",
									disabled: $setup.isInvalidatingStaticUrls,
									text: _ctx.__("Cancel")
								}, null, 8, ["disabled", "text"])]),
								_: 1
							}), createVNode($setup["Button"], {
								type: "submit",
								variant: "primary",
								disabled: $setup.isInvalidatingStaticUrls,
								text: _ctx.__("Invalidate URLs"),
								onClick: $setup.invalidateStaticUrls
							}, null, 8, ["disabled", "text"])])
						]),
						_: 1
					}, 8, ["title", "open"])])) : createCommentVNode("", true)]),
					_: 1
				}), createVNode($setup["Card"], { class: "flex-1" }, {
					default: withCtx(() => [createVNode($setup["Description"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("statamic::messages.cache_utility_static_cache_description")), 1)]),
						_: 1
					}), createBaseVNode("div", _hoisted_7, [createVNode($setup["Badge"], { prepend: _ctx.__("Strategy") }, {
						default: withCtx(() => [createTextVNode(toDisplayString($props.static.strategy), 1)]),
						_: 1
					}, 8, ["prepend"]), $props.static.enabled ? (openBlock(), createBlock($setup["Badge"], {
						key: 0,
						prepend: _ctx.__("Cached Pages")
					}, {
						default: withCtx(() => [createTextVNode(toDisplayString($props.static.count), 1)]),
						_: 1
					}, 8, ["prepend"])) : createCommentVNode("", true)])]),
					_: 1
				})]),
				_: 1
			}),
			createVNode($setup["Panel"], { class: "h-full flex flex-col" }, {
				default: withCtx(() => [createVNode($setup["PanelHeader"], { class: "flex items-center justify-between min-h-10" }, {
					default: withCtx(() => [createVNode($setup["Heading"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Application Cache")), 1)]),
						_: 1
					}), createBaseVNode("div", _hoisted_8, [createVNode($setup["CommandPaletteItem"], {
						category: "Actions",
						text: [_ctx.__("Clear"), _ctx.__("Application Cache")],
						icon: "live-preview",
						action: $setup.clearApplication
					}, {
						default: withCtx(() => [createVNode($setup["Button"], {
							text: _ctx.__("Clear"),
							size: "sm",
							onClick: $setup.clearApplication
						}, null, 8, ["text"])]),
						_: 1
					}, 8, ["text"])])]),
					_: 1
				}), createVNode($setup["Card"], { class: "flex-1" }, {
					default: withCtx(() => [createVNode($setup["Description"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("statamic::messages.cache_utility_application_cache_description")), 1)]),
						_: 1
					}), createBaseVNode("div", _hoisted_9, [createVNode($setup["Badge"], { prepend: _ctx.__("Driver") }, {
						default: withCtx(() => [createTextVNode(toDisplayString($props.cache.driver), 1)]),
						_: 1
					}, 8, ["prepend"])])]),
					_: 1
				})]),
				_: 1
			}),
			createVNode($setup["Panel"], { class: "h-full flex flex-col" }, {
				default: withCtx(() => [createVNode($setup["PanelHeader"], { class: "flex items-center justify-between min-h-10" }, {
					default: withCtx(() => [createVNode($setup["Heading"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Image Cache")), 1)]),
						_: 1
					}), createBaseVNode("div", _hoisted_10, [createVNode($setup["CommandPaletteItem"], {
						category: "Actions",
						text: [_ctx.__("Clear"), _ctx.__("Image Cache")],
						icon: "live-preview",
						action: $setup.clearImage
					}, {
						default: withCtx(() => [createVNode($setup["Button"], {
							text: _ctx.__("Clear"),
							size: "sm",
							onClick: $setup.clearImage
						}, null, 8, ["text"])]),
						_: 1
					}, 8, ["text"])])]),
					_: 1
				}), createVNode($setup["Card"], { class: "flex-1" }, {
					default: withCtx(() => [createVNode($setup["Description"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("statamic::messages.cache_utility_image_cache_description")), 1)]),
						_: 1
					}), createBaseVNode("div", _hoisted_11, [createVNode($setup["Badge"], { prepend: _ctx.__("Cached images") }, {
						default: withCtx(() => [createTextVNode(toDisplayString($props.images.count), 1)]),
						_: 1
					}, 8, ["prepend"]), createVNode($setup["Badge"], { prepend: _ctx.__("Size") }, {
						default: withCtx(() => [createTextVNode(toDisplayString($props.images.size), 1)]),
						_: 1
					}, 8, ["prepend"])])]),
					_: 1
				})]),
				_: 1
			})
		]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("caching"),
			url: "caching"
		}, null, 8, ["topic"])
	])], 64);
}
var Cache_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Cache.vue"]]);
//#endregion
export { Cache_default as default };
