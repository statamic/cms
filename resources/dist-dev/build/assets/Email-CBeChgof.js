import { At as toDisplayString, B as openBlock, C as createVNode, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { i as link_default, n as form_default } from "./index.esm-B4SStoAz.js";
import { Ci as Icon_default, Di as AuthCard_default, Jn as Heading_default, Vt as Input_default, Wt as Field_default, Yn as Card_default, in as Description_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import Outside_default from "./Outside-BOZ997t_.js";
//#region resources/js/pages/auth/passwords/Email.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Outside_default }, {
	__name: "Email",
	props: ["action", "loginUrl"],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Head: Head_default,
			Outside: Outside_default,
			get AuthCard() {
				return AuthCard_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Description() {
				return Description_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Card() {
				return Card_default;
			},
			get Input() {
				return Input_default;
			},
			get Field() {
				return Field_default;
			},
			get Button() {
				return Button_default;
			},
			get Link() {
				return link_default;
			},
			get Form() {
				return form_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1 = { class: "flex flex-col justify-center items-center mb-8 py-3" };
var _hoisted_2 = {
	key: 0,
	class: "flex flex-col gap-6"
};
var _hoisted_3 = { class: "mt-4 w-full text-center dark:mt-6" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Reset Password") }, null, 8, ["title"]),
		createVNode($setup["AuthCard"], null, {
			default: withCtx(() => [createVNode($setup["Form"], {
				method: "post",
				action: $props.action,
				"error-bag": "user.forgot_password"
			}, {
				default: withCtx(({ errors, wasSuccessful }) => [createBaseVNode("header", _hoisted_1, [!wasSuccessful ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [
					createVNode($setup["Card"], { class: "p-2! mb-4 flex items-center justify-center" }, {
						default: withCtx(() => [createVNode($setup["Icon"], {
							name: "key",
							class: "size-5"
						})]),
						_: 1
					}),
					createVNode($setup["Heading"], {
						level: 1,
						size: "xl",
						text: _ctx.__("Reset Your Password")
					}, null, 8, ["text"]),
					createVNode($setup["Description"], {
						text: _ctx.__("statamic::messages.forgot_password_enter_email"),
						class: "text-center"
					}, null, 8, ["text"])
				], 64)) : createCommentVNode("", true), wasSuccessful ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [
					createVNode($setup["Card"], { class: "p-2! mb-4 flex items-center justify-center" }, {
						default: withCtx(() => [createVNode($setup["Icon"], {
							name: "mail-check",
							class: "size-5"
						})]),
						_: 1
					}),
					createVNode($setup["Heading"], {
						level: 1,
						size: "xl",
						text: _ctx.__("Password Reset Sent")
					}, null, 8, ["text"]),
					createVNode($setup["Description"], {
						text: _ctx.__("statamic::messages.forgot_password_sent"),
						class: "text-center"
					}, null, 8, ["text"])
				], 64)) : createCommentVNode("", true)]), !wasSuccessful ? (openBlock(), createElementBlock("div", _hoisted_2, [createVNode($setup["Field"], {
					label: _ctx.__("Email Address"),
					error: errors.email
				}, {
					default: withCtx(() => [createVNode($setup["Input"], {
						name: "email",
						autofocus: "",
						type: "email"
					})]),
					_: 1
				}, 8, ["label", "error"]), createVNode($setup["Button"], {
					type: "submit",
					variant: "primary",
					text: _ctx.__("Submit")
				}, null, 8, ["text"])])) : createCommentVNode("", true)]),
				_: 1
			}, 8, ["action"])]),
			_: 1
		}),
		createBaseVNode("div", _hoisted_3, [createVNode($setup["Link"], {
			href: $props.loginUrl,
			class: "text-ui-accent-text text-sm hover:text-ui-accent-text/80",
			textContent: toDisplayString(_ctx.__("I remember my password"))
		}, null, 8, ["href", "textContent"])])
	], 64);
}
var Email_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Email.vue"]]);
//#endregion
export { Email_default as default };
