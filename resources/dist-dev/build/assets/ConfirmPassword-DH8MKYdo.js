import { B as openBlock, C as createVNode, _ as createBlock, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, n as form_default } from "./index.esm-B4SStoAz.js";
import { Di as AuthCard_default, Jt as ErrorMessage_default, St as Separator_default, Vt as Input_default, Wt as Field_default, in as Description_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { a as Layout_default, r as Head_default } from "./index-Bu3QBQkS.js";
import { t as usePasskey } from "./passkey-BkGMpDpX.js";
import Outside_default from "./Outside-BOZ997t_.js";
//#region resources/js/pages/auth/ConfirmPassword.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: (h, page) => page.props.outside ? h(Outside_default, () => page) : h(Layout_default, () => page) }, {
	__name: "ConfirmPassword",
	props: [
		"method",
		"allowPasskey",
		"status",
		"submitUrl",
		"resendUrl",
		"passkeyOptionsUrl",
		"outside"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const isConfirmingPassword = computed(() => props.method === "password_confirmation");
		const isUsingVerificationCode = computed(() => props.method === "verification_code");
		const isOnlyUsingPasskey = computed(() => props.method === "passkey");
		const passkey = usePasskey();
		async function confirmWithPasskey() {
			await passkey.authenticate(props.passkeyOptionsUrl, props.submitUrl, (response) => router.get(response.redirect));
		}
		const __returned__ = {
			props,
			isConfirmingPassword,
			isUsingVerificationCode,
			isOnlyUsingPasskey,
			passkey,
			confirmWithPasskey,
			Outside: Outside_default,
			Layout: Layout_default,
			Head: Head_default,
			get AuthCard() {
				return AuthCard_default;
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
			get Description() {
				return Description_default;
			},
			get ErrorMessage() {
				return ErrorMessage_default;
			},
			get Separator() {
				return Separator_default;
			},
			computed,
			get Form() {
				return form_default;
			},
			get router() {
				return router;
			},
			get usePasskey() {
				return usePasskey;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
var _hoisted_1 = { class: "flex items-center gap-4" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Confirm Password") }, null, 8, ["title"]), createVNode($setup["AuthCard"], {
		icon: "key",
		class: "max-w-md mx-auto mt-8",
		title: _ctx.__("Confirm Your Identity"),
		description: _ctx.__("statamic::messages.elevated_session_reauthenticate")
	}, {
		default: withCtx(() => [
			$props.status ? (openBlock(), createBlock($setup["Description"], {
				key: 0,
				variant: "success",
				text: $props.status,
				class: "mb-6"
			}, null, 8, ["text"])) : createCommentVNode("", true),
			!$setup.isOnlyUsingPasskey ? (openBlock(), createBlock($setup["Form"], {
				key: 1,
				method: "post",
				action: $props.submitUrl,
				class: "flex flex-col gap-6"
			}, {
				default: withCtx(({ errors }) => [
					$setup.isConfirmingPassword ? (openBlock(), createBlock($setup["Field"], {
						key: 0,
						label: _ctx.__("Password"),
						error: errors.password
					}, {
						default: withCtx(() => [createVNode($setup["Input"], {
							name: "password",
							type: "password",
							viewable: "",
							autofocus: ""
						})]),
						_: 1
					}, 8, ["label", "error"])) : createCommentVNode("", true),
					$setup.isUsingVerificationCode ? (openBlock(), createBlock($setup["Field"], {
						key: 1,
						label: _ctx.__("Verification Code"),
						error: errors.verification_code
					}, {
						default: withCtx(() => [createVNode($setup["Input"], {
							name: "verification_code",
							autofocus: ""
						})]),
						_: 1
					}, 8, ["label", "error"])) : createCommentVNode("", true),
					createBaseVNode("div", _hoisted_1, [createVNode($setup["Button"], {
						type: "submit",
						variant: "primary",
						text: _ctx.__("Submit"),
						class: "flex-1"
					}, null, 8, ["text"]), $setup.isUsingVerificationCode ? (openBlock(), createBlock($setup["Button"], {
						key: 0,
						as: "a",
						class: "flex-1",
						href: $props.resendUrl,
						text: _ctx.__("Resend code")
					}, null, 8, ["href", "text"])) : createCommentVNode("", true)])
				]),
				_: 1
			}, 8, ["action"])) : createCommentVNode("", true),
			$props.allowPasskey ? (openBlock(), createElementBlock(Fragment, { key: 2 }, [
				!$setup.isOnlyUsingPasskey ? (openBlock(), createBlock($setup["Separator"], {
					key: 0,
					variant: "dots",
					text: _ctx.__("or"),
					class: "py-3"
				}, null, 8, ["text"])) : createCommentVNode("", true),
				createVNode($setup["Button"], {
					class: "w-full",
					variant: $setup.isOnlyUsingPasskey ? "primary" : "default",
					text: _ctx.__("Confirm with Passkey"),
					icon: $setup.passkey.waiting.value ? null : "key",
					disabled: $setup.passkey.waiting.value,
					loading: $setup.passkey.waiting.value,
					onClick: $setup.confirmWithPasskey
				}, null, 8, [
					"variant",
					"text",
					"icon",
					"disabled",
					"loading"
				]),
				$setup.passkey.error.value ? (openBlock(), createBlock($setup["ErrorMessage"], {
					key: 1,
					text: $setup.passkey.error.value
				}, null, 8, ["text"])) : createCommentVNode("", true)
			], 64)) : createCommentVNode("", true)
		]),
		_: 1
	}, 8, ["title", "description"])], 64);
}
var ConfirmPassword_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "ConfirmPassword.vue"]]);
//#endregion
export { ConfirmPassword_default as default };
