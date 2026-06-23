import { B as openBlock, C as createVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Jn as Heading_default, Yn as Card_default, in as Description_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { o as useBodyClasses } from "./index-Bu3QBQkS.js";
import Blank_default from "./Blank-CKDkY2Cn.js";
//#region resources/js/pages/errors/404.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Blank_default }, {
	__name: "404",
	setup(__props, { expose: __expose }) {
		__expose();
		useBodyClasses("bg-gray-50 dark:bg-gray-900");
		const __returned__ = {
			get Card() {
				return Card_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Description() {
				return Description_default;
			},
			get Button() {
				return Button_default;
			},
			Blank: Blank_default,
			get useBodyClasses() {
				return useBodyClasses;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1 = { class: "flex min-h-screen flex-col items-center justify-center" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["Card"], { class: "text-center flex flex-col items-center space-y-2" }, {
		default: withCtx(() => [
			createVNode($setup["Heading"], {
				size: "2xl",
				text: _ctx.__("404")
			}, null, 8, ["text"]),
			createVNode($setup["Description"], { text: _ctx.__("The page you are looking for could not be found.") }, null, 8, ["text"]),
			createVNode($setup["Button"], {
				as: "a",
				href: _ctx.cp_url("/"),
				size: "xs",
				variant: "filled",
				class: "mt-4",
				text: _ctx.__("Return to Control Panel")
			}, null, 8, ["href", "text"])
		]),
		_: 1
	})]);
}
var _404_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "404.vue"]]);
//#endregion
export { _404_default as default };
