import { At as toDisplayString, B as openBlock, C as createVNode, _t as ref, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, u as withModifiers, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default, n as form_default } from "./index.esm-B4SStoAz.js";
import { Ci as Icon_default, Di as AuthCard_default, Jn as Heading_default, Vt as Input_default, Wt as Field_default, Yn as Card_default, in as Description_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import Outside_default from "./Outside-BOZ997t_.js";
//#region resources/js/pages/auth/passwords/Reset.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Outside_default }, {
	__name: "Reset",
	props: [
		"errors",
		"action",
		"token",
		"email",
		"emailReadonly",
		"redirect",
		"title",
		"loginUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const errors = ref(props.errors);
		watch(() => props.errors, (newErrors) => errors.value = newErrors);
		const email = ref(props.email);
		const password = ref("");
		const passwordConfirmation = ref("");
		const processing = ref(false);
		const submit = () => {
			processing.value = true;
			router.post(props.action, {
				email: email.value,
				password: password.value,
				password_confirmation: passwordConfirmation.value,
				token: props.token,
				redirect: props.redirect
			}, {
				onBefore: () => {
					processing.value = true;
					errors.value = {};
				},
				onError: () => processing.value = false
			});
		};
		const __returned__ = {
			props,
			errors,
			email,
			password,
			passwordConfirmation,
			processing,
			submit,
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
var _hoisted_1 = { class: "mt-4 w-full text-center dark:mt-6" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: $props.title }, null, 8, ["title"]),
		createVNode($setup["AuthCard"], {
			icon: "key",
			title: $props.title,
			description: _ctx.__("statamic::messages.set_new_password_instructions")
		}, {
			default: withCtx(() => [createBaseVNode("form", {
				onSubmit: withModifiers($setup.submit, ["prevent"]),
				class: "flex flex-col gap-6"
			}, [
				createVNode($setup["Field"], {
					label: _ctx.__("Email Address"),
					error: $setup.errors?.email
				}, {
					default: withCtx(() => [createVNode($setup["Input"], {
						modelValue: $setup.email,
						"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.email = $event),
						"read-only": $props.emailReadonly,
						focus: !$props.emailReadonly,
						type: "email"
					}, null, 8, [
						"modelValue",
						"read-only",
						"focus"
					])]),
					_: 1
				}, 8, ["label", "error"]),
				createVNode($setup["Field"], {
					label: _ctx.__("Password"),
					error: $setup.errors?.password
				}, {
					default: withCtx(() => [createVNode($setup["Input"], {
						modelValue: $setup.password,
						"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.password = $event),
						focus: $props.emailReadonly,
						type: "password"
					}, null, 8, ["modelValue", "focus"])]),
					_: 1
				}, 8, ["label", "error"]),
				createVNode($setup["Field"], {
					label: _ctx.__("Confirm Password"),
					error: $setup.errors?.password_confirmation
				}, {
					default: withCtx(() => [createVNode($setup["Input"], {
						modelValue: $setup.passwordConfirmation,
						"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.passwordConfirmation = $event),
						type: "password"
					}, null, 8, ["modelValue"])]),
					_: 1
				}, 8, ["label", "error"]),
				createVNode($setup["Button"], {
					type: "submit",
					variant: "primary",
					text: $props.title
				}, null, 8, ["text"])
			], 32)]),
			_: 1
		}, 8, ["title", "description"]),
		createBaseVNode("div", _hoisted_1, [createVNode($setup["Link"], {
			href: $props.loginUrl,
			class: "text-ui-accent-text text-sm hover:text-ui-accent-text/80",
			textContent: toDisplayString(_ctx.__("Back to login"))
		}, null, 8, ["href", "textContent"])])
	], 64);
}
var Reset_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Reset.vue"]]);
//#endregion
export { Reset_default as default };
