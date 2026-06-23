import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, S as createTextVNode, _ as createBlock, _t as ref, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { Di as AuthCard_default, Vt as Input_default, Wt as Field_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import Outside_default from "./Outside-BOZ997t_.js";
//#region resources/js/pages/auth/two-factor/Challenge.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Outside_default }, {
	__name: "Challenge",
	props: [
		"errors",
		"mode",
		"action",
		"redirect"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const errors = ref(props.errors);
		watch(() => props.errors, (newErrors) => errors.value = newErrors);
		const mode = ref(props.mode);
		const code = ref("");
		const recoveryCode = ref("");
		const processing = ref(false);
		const shaking = computed(() => Object.keys(errors.value).length ? "animation-shake" : "");
		const submit = () => {
			processing.value = true;
			router.post(props.action, {
				code: code.value,
				recovery_code: recoveryCode.value,
				redirect: props.redirect
			}, {
				onBefore: () => {
					processing.value = true;
					errors.value = {};
				},
				onSuccess: (response) => window.location.href = response.url,
				onError: () => processing.value = false
			});
		};
		const __returned__ = {
			props,
			errors,
			mode,
			code,
			recoveryCode,
			processing,
			shaking,
			submit,
			Head: Head_default,
			Outside: Outside_default,
			get AuthCard() {
				return AuthCard_default;
			},
			get Field() {
				return Field_default;
			},
			get Input() {
				return Input_default;
			},
			get Button() {
				return Button_default;
			},
			computed,
			ref,
			watch,
			get router() {
				return router;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Two-Factor Authentication") }, null, 8, ["title"]), createVNode($setup["AuthCard"], {
		icon: "phone-lock",
		title: _ctx.__("Two-Factor Authentication"),
		description: $setup.mode === "code" ? _ctx.__("statamic::messages.two_factor_challenge_code_instructions") : _ctx.__("statamic::messages.two_factor_recovery_code_instructions"),
		class: normalizeClass([$setup.shaking])
	}, {
		default: withCtx(() => [createBaseVNode("form", {
			onSubmit: withModifiers($setup.submit, ["prevent"]),
			class: "flex flex-col gap-6"
		}, [
			$setup.mode === "code" ? (openBlock(), createBlock($setup["Field"], {
				key: 0,
				label: _ctx.__("Code"),
				error: $setup.errors.code
			}, {
				default: withCtx(() => [createVNode($setup["Input"], {
					type: "text",
					modelValue: $setup.code,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.code = $event),
					pattern: "[0-9]*",
					maxlength: "6",
					inputmode: "numeric",
					autofocus: "",
					autocomplete: "one-time-code"
				}, null, 8, ["modelValue"])]),
				_: 1
			}, 8, ["label", "error"])) : createCommentVNode("", true),
			$setup.mode === "recovery_code" ? (openBlock(), createBlock($setup["Field"], {
				key: 1,
				label: _ctx.__("Recovery Code"),
				error: $setup.errors.recovery_code
			}, {
				default: withCtx(() => [createVNode($setup["Input"], {
					type: "text",
					modelValue: $setup.recoveryCode,
					"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.recoveryCode = $event),
					maxlength: "21",
					autofocus: "",
					autocomplete: "off"
				}, null, 8, ["modelValue"])]),
				_: 1
			}, 8, ["label", "error"])) : createCommentVNode("", true),
			createVNode($setup["Button"], {
				type: "submit",
				variant: "primary",
				disabled: $setup.processing,
				loading: $setup.processing,
				class: "w-full"
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Continue")), 1)]),
				_: 1
			}, 8, ["disabled", "loading"]),
			$setup.mode === "code" ? (openBlock(), createElementBlock("button", {
				key: 2,
				class: "cursor-pointer text-xs text-gray-500 hover:text-gray-900 dark:hover:text-gray-300",
				type: "button",
				onClick: _cache[2] || (_cache[2] = ($event) => $setup.mode = "recovery_code")
			}, toDisplayString(_ctx.__("Use recovery code")), 1)) : createCommentVNode("", true),
			$setup.mode === "recovery_code" ? (openBlock(), createElementBlock("button", {
				key: 3,
				class: "cursor-pointer text-xs text-gray-500 hover:text-gray-900 dark:hover:text-gray-300",
				type: "button",
				onClick: _cache[3] || (_cache[3] = ($event) => $setup.mode = "code")
			}, toDisplayString(_ctx.__("Use one-time code")), 1)) : createCommentVNode("", true)
		], 32)]),
		_: 1
	}, 8, [
		"title",
		"description",
		"class"
	])], 64);
}
var Challenge_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Challenge.vue"]]);
//#endregion
export { Challenge_default as default };
