import { B as openBlock, C as createVNode, Dt as normalizeClass, _ as createBlock, _t as ref, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { Di as AuthCard_default, Jt as ErrorMessage_default, Vt as Input_default, Wt as Field_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import Outside_default from "./Outside-BOZ997t_.js";
//#region resources/js/pages/auth/protect/Password.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Outside_default }, {
	__name: "Password",
	props: [
		"errors",
		"token",
		"submitUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const processing = ref(false);
		const password = ref("");
		const errors = ref(props.errors?.passwordProtect || {});
		watch(() => props.errors, (newErrors) => errors.value = newErrors.passwordProtect);
		const shaking = computed(() => Object.keys(errors.value).length ? "animation-shake" : "");
		const submit = () => {
			processing.value = true;
			router.post(props.submitUrl, {
				password: password.value,
				token: props.token
			}, {
				onBefore: () => {
					processing.value = true;
					errors.value = {};
				},
				onSuccess: () => {
					return false;
				},
				onError: () => processing.value = false
			});
		};
		const __returned__ = {
			props,
			processing,
			password,
			errors,
			shaking,
			submit,
			Head: Head_default,
			Outside: Outside_default,
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
			get ErrorMessage() {
				return ErrorMessage_default;
			},
			get router() {
				return router;
			},
			computed,
			ref,
			watch
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Protected Page") }, null, 8, ["title"]), createVNode($setup["AuthCard"], {
		icon: "key",
		title: _ctx.__("Protected Page"),
		description: $props.token ? _ctx.__("statamic::messages.password_protect_enter_password") : _ctx.__("statamic::messages.password_protect_token_missing"),
		class: normalizeClass([$setup.shaking])
	}, {
		default: withCtx(() => [createBaseVNode("form", {
			onSubmit: withModifiers($setup.submit, ["prevent"]),
			class: "flex flex-col gap-6"
		}, [
			createVNode($setup["Field"], { error: $setup.errors?.password }, {
				default: withCtx(() => [createVNode($setup["Input"], {
					type: "password",
					modelValue: $setup.password,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.password = $event),
					autofocus: "",
					placeholder: _ctx.__("statamic::messages.password_protect_enter_password")
				}, null, 8, ["modelValue", "placeholder"])]),
				_: 1
			}, 8, ["error"]),
			$setup.errors?.token ? (openBlock(), createBlock($setup["ErrorMessage"], {
				key: 0,
				text: $setup.errors?.token
			}, null, 8, ["text"])) : createCommentVNode("", true),
			createVNode($setup["Button"], {
				type: "submit",
				variant: "primary",
				class: "w-full",
				text: _ctx.__("Submit")
			}, null, 8, ["text"])
		], 32)]),
		_: 1
	}, 8, [
		"title",
		"description",
		"class"
	])], 64);
}
var Password_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Password.vue"]]);
//#endregion
export { Password_default as default };
