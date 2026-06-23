import { At as toDisplayString, B as openBlock, C as createVNode, f as Fragment, g as createBaseVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Di as AuthCard_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import Outside_default from "./Outside-BOZ997t_.js";
//#region resources/js/pages/auth/Unauthorized.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Outside_default }, {
	__name: "Unauthorized",
	props: [
		"isLoggedIn",
		"loginUrl",
		"logoutUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Head: Head_default,
			Outside: Outside_default,
			get AuthCard() {
				return AuthCard_default;
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
});
var _hoisted_1 = { class: "flex justify-center" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Unauthorized") }, null, 8, ["title"]), createVNode($setup["AuthCard"], {
		icon: "key",
		title: _ctx.__("Unauthorized"),
		description: _ctx.__("You do not have permission to access this URL")
	}, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1, [createVNode($setup["Button"], {
			as: "a",
			variant: "primary",
			href: $props.isLoggedIn ? $props.logoutUrl : $props.loginUrl,
			class: "w-full",
			textContent: toDisplayString($props.isLoggedIn ? _ctx.__("Log out") : _ctx.__("Log in"))
		}, null, 8, ["href", "textContent"])])]),
		_: 1
	}, 8, ["title", "description"])], 64);
}
var Unauthorized_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Unauthorized.vue"]]);
//#endregion
export { Unauthorized_default as default };
