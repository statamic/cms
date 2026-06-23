import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, L as onMounted, R as onUnmounted, W as renderList, _ as createBlock, _t as ref, at as withDirectives, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, q as resolveDirective, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { Di as AuthCard_default, Jt as ErrorMessage_default, St as Separator_default, Vt as Input_default, Wt as Field_default, li as Button_default, zn as Item_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import { t as usePasskey } from "./passkey-BkGMpDpX.js";
import Outside_default from "./Outside-BOZ997t_.js";
//#region resources/js/pages/auth/Login.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Outside_default }, {
	__name: "Login",
	props: [
		"errors",
		"emailLoginEnabled",
		"passkeyOptionsUrl",
		"passkeyVerifyUrl",
		"oauthEnabled",
		"providers",
		"submitUrl",
		"forgotPasswordUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const errors = ref(props.errors);
		watch(() => props.errors, (newErrors) => errors.value = newErrors);
		const email = ref("");
		const password = ref("");
		const remember = ref(false);
		const processing = ref(false);
		const shaking = computed(() => Object.keys(errors.value).length ? "animation-shake" : "");
		const showOAuth = computed(() => props.oauthEnabled && props.providers.length > 0);
		const submit = () => {
			processing.value = true;
			router.post(props.submitUrl, {
				email: email.value,
				password: password.value,
				remember: remember.value
			}, {
				onBefore: () => {
					processing.value = true;
					errors.value = {};
				},
				onError: () => processing.value = false
			});
		};
		const passkey = usePasskey();
		const showPasskeyLogin = computed(() => {
			return props.emailLoginEnabled && passkey.supported;
		});
		const emailAutocomplete = computed(() => {
			let tokens = "username";
			if (showPasskeyLogin.value) tokens += " webauthn";
			return tokens;
		});
		const passwordAutocomplete = computed(() => {
			let tokens = "current-password";
			if (showPasskeyLogin.value) tokens += " webauthn";
			return tokens;
		});
		async function loginWithPasskey(useBrowserAutofill = false) {
			await passkey.authenticate(props.passkeyOptionsUrl, props.passkeyVerifyUrl, (data) => {
				if (data.redirect) window.location = data.redirect;
			}, useBrowserAutofill);
		}
		onMounted(() => {
			if (showPasskeyLogin.value) loginWithPasskey(true);
		});
		onUnmounted(() => passkey.cancel());
		const __returned__ = {
			props,
			errors,
			email,
			password,
			remember,
			processing,
			shaking,
			showOAuth,
			submit,
			passkey,
			showPasskeyLogin,
			emailAutocomplete,
			passwordAutocomplete,
			loginWithPasskey,
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
			get Separator() {
				return Separator_default;
			},
			get Checkbox() {
				return Item_default;
			},
			get ErrorMessage() {
				return ErrorMessage_default;
			},
			get Link() {
				return link_default;
			},
			get router() {
				return router;
			},
			computed,
			onMounted,
			onUnmounted,
			ref,
			watch,
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
var _hoisted_1 = { class: "flex flex-col gap-y-4" };
var _hoisted_2 = {
	key: 1,
	class: "flex gap-4 justify-center items-center"
};
var _hoisted_3 = { class: "sr-only" };
var _hoisted_4 = { key: 0 };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Log in") }, null, 8, ["title"]), createVNode($setup["AuthCard"], {
		icon: "sign-in",
		title: $props.emailLoginEnabled ? _ctx.__("Sign in with email") : _ctx.__("Sign in with OAuth"),
		description: _ctx.__("Sign into your Statamic Control Panel"),
		class: normalizeClass([$setup.shaking])
	}, {
		default: withCtx(() => [createBaseVNode("div", null, [$props.emailLoginEnabled ? (openBlock(), createElementBlock("form", {
			key: 0,
			onSubmit: withModifiers($setup.submit, ["prevent"]),
			class: "flex flex-col gap-6"
		}, [
			createVNode($setup["Field"], {
				label: _ctx.__("Email"),
				error: $setup.errors?.email
			}, {
				default: withCtx(() => [createVNode($setup["Input"], {
					modelValue: $setup.email,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.email = $event),
					name: "email",
					autofocus: "",
					tabindex: "1",
					autocomplete: $setup.emailAutocomplete
				}, null, 8, ["modelValue", "autocomplete"])]),
				_: 1
			}, 8, ["label", "error"]),
			createVNode($setup["Field"], {
				label: _ctx.__("Password"),
				error: $setup.errors?.password
			}, {
				actions: withCtx(() => [createVNode($setup["Link"], {
					href: $props.forgotPasswordUrl,
					class: "text-ui-accent-text mb-1.5 text-sm hover:text-ui-accent-text/80",
					tabindex: "6",
					textContent: toDisplayString(_ctx.__("Forgot password?"))
				}, null, 8, ["href", "textContent"])]),
				default: withCtx(() => [createVNode($setup["Input"], {
					modelValue: $setup.password,
					"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.password = $event),
					name: "password",
					type: "password",
					autocomplete: $setup.passwordAutocomplete,
					tabindex: "2"
				}, null, 8, ["modelValue", "autocomplete"])]),
				_: 1
			}, 8, ["label", "error"]),
			createVNode($setup["Checkbox"], {
				modelValue: $setup.remember,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.remember = $event),
				name: "remember",
				label: _ctx.__("Remember me"),
				tabindex: "4"
			}, null, 8, ["modelValue", "label"]),
			createVNode($setup["Button"], {
				type: "submit",
				variant: "primary",
				disabled: $setup.processing,
				text: _ctx.__("Continue"),
				tabindex: "5"
			}, null, 8, ["disabled", "text"])
		], 32)) : createCommentVNode("", true), $setup.showOAuth || $setup.showPasskeyLogin ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [$props.emailLoginEnabled ? (openBlock(), createBlock($setup["Separator"], {
			key: 0,
			variant: "dots",
			text: _ctx.__("Or sign in with"),
			class: "py-3"
		}, null, 8, ["text"])) : createCommentVNode("", true), createBaseVNode("div", _hoisted_1, [$setup.showPasskeyLogin ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createVNode($setup["Button"], {
			text: _ctx.__("Passkey"),
			class: "w-full",
			icon: $setup.passkey.waiting.value ? null : "key",
			disabled: $setup.passkey.waiting.value,
			loading: $setup.passkey.waiting.value,
			onClick: _cache[3] || (_cache[3] = ($event) => $setup.loginWithPasskey())
		}, null, 8, [
			"text",
			"icon",
			"disabled",
			"loading"
		]), $setup.passkey.error.value ? (openBlock(), createBlock($setup["ErrorMessage"], {
			key: 0,
			text: $setup.passkey.error.value
		}, null, 8, ["text"])) : createCommentVNode("", true)], 64)) : createCommentVNode("", true), $setup.showOAuth ? (openBlock(), createElementBlock("div", _hoisted_2, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.providers, (provider) => {
			return withDirectives((openBlock(), createBlock($setup["Button"], {
				key: provider.name,
				as: "a",
				class: "flex-1 [&_svg]:opacity-100!",
				href: provider.url,
				icon: provider.icon,
				"icon-only": !!provider.icon
			}, {
				default: withCtx(() => [createBaseVNode("span", _hoisted_3, toDisplayString(_ctx.__("Sign in with :provider", { provider: provider.label })), 1), !provider.icon ? (openBlock(), createElementBlock("span", _hoisted_4, toDisplayString(provider.label), 1)) : createCommentVNode("", true)]),
				_: 2
			}, 1032, [
				"href",
				"icon",
				"icon-only"
			])), [[_directive_tooltip, _ctx.__("Sign in with :provider", { provider: provider.label })]]);
		}), 128))])) : createCommentVNode("", true)])], 64)) : createCommentVNode("", true)])]),
		_: 1
	}, 8, [
		"title",
		"description",
		"class"
	])], 64);
}
var Login_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Login.vue"]]);
//#endregion
export { Login_default as default };
