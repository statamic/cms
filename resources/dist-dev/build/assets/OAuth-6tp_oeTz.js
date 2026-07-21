import { At as toDisplayString, B as openBlock, C as createVNode, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { G as axios, c as router } from "./index.esm-CCyh9ppR.js";
import { C as requireElevatedSession, Vt as Header_default, fi as Button_default, i as Listing_default } from "./ui-BKXK9FxZ.js";
import { T as toast } from "./api-CKHCsH_G.js";
import { r as Head_default } from "./index-Du980QkA.js";
//#region resources/js/pages/users/OAuth.vue
var _sfc_main = {
	__name: "OAuth",
	props: ["providers"],
	setup(__props, { expose: __expose }) {
		__expose();
		const columns = [{
			label: __("Provider"),
			field: "label"
		}, {
			label: "",
			field: "actions"
		}];
		function connect(provider) {
			requireElevatedSession().then(() => window.location = provider.connectUrl).catch(() => toast.error(__("statamic::messages.elevated_session_required")));
		}
		function disconnect(provider) {
			requireElevatedSession().then(() => performDisconnect(provider)).catch(() => toast.error(__("statamic::messages.elevated_session_required")));
		}
		function performDisconnect(provider) {
			axios.delete(provider.disconnectUrl).then(() => {
				toast.success(__("statamic::messages.oauth_disconnected", { provider: provider.label }));
				router.reload();
			});
		}
		const __returned__ = {
			columns,
			connect,
			disconnect,
			performDisconnect,
			get router() {
				return router;
			},
			get axios() {
				return axios;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get Listing() {
				return Listing_default;
			},
			get toast() {
				return toast;
			},
			get requireElevatedSession() {
				return requireElevatedSession;
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
var _hoisted_2 = { class: "flex items-center gap-2" };
var _hoisted_3 = ["innerHTML"];
var _hoisted_4 = { class: "text-right" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Sign-in Providers") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [createVNode($setup["Header"], {
		title: _ctx.__("Sign-in Providers"),
		icon: "sign-in"
	}, null, 8, ["title"]), createVNode($setup["Listing"], {
		items: $props.providers,
		columns: $setup.columns,
		"allow-search": false,
		"allow-customizing-columns": false
	}, {
		"cell-label": withCtx(({ row }) => [createBaseVNode("div", _hoisted_2, [row.icon ? (openBlock(), createElementBlock("span", {
			key: 0,
			class: "flex size-4 items-center [&_svg]:size-4",
			innerHTML: row.icon
		}, null, 8, _hoisted_3)) : createCommentVNode("", true), createBaseVNode("span", null, toDisplayString(row.label), 1)])]),
		"cell-actions": withCtx(({ row }) => [createBaseVNode("div", _hoisted_4, [row.connected ? (openBlock(), createBlock($setup["Button"], {
			key: 0,
			size: "xs",
			text: _ctx.__("Disconnect"),
			onClick: ($event) => $setup.disconnect(row)
		}, null, 8, ["text", "onClick"])) : (openBlock(), createBlock($setup["Button"], {
			key: 1,
			size: "xs",
			text: _ctx.__("Connect"),
			onClick: ($event) => $setup.connect(row)
		}, null, 8, ["text", "onClick"]))])]),
		_: 1
	}, 8, ["items"])])], 64);
}
var OAuth_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "OAuth.vue"]]);
//#endregion
export { OAuth_default as default };
