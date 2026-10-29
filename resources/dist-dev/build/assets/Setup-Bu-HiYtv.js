import { H as openBlock, T as createVNode, b as createCommentVNode, ot as withCtx, p as Fragment, x as createElementBlock, y as createBlock, yt as ref } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { C as requireElevatedSession, Zi as AuthCard_default, Zr as Button_default } from "./ui-BOQtKSPJ.js";
import { D as Setup_default$1, r as Head_default } from "./index-C2weejEn.js";
import Outside_default from "./Outside-H6wFhLNp.js";
// resources/js/pages/auth/two-factor/Setup.vue
var _sfc_main = /*@__PURE__*/ Object.assign({ layout: Outside_default }, {
	__name: "Setup",
	props: ["routes", "redirect"],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const setupModalOpen = ref(false);
		function openSetupModal() {
			requireElevatedSession().then(() => setupModalOpen.value = true).catch(() => Statamic.$toast.error(__("statamic::messages.elevated_session_required")));
		}
		function setupComplete() {
			window.location.href = props.redirect;
		}
		const __returned__ = {
			props,
			setupModalOpen,
			openSetupModal,
			setupComplete,
			Head: Head_default,
			Outside: Outside_default,
			TwoFactorSetup: Setup_default$1,
			get requireElevatedSession() {
				return requireElevatedSession;
			},
			get AuthCard() {
				return AuthCard_default;
			},
			get Button() {
				return Button_default;
			},
			ref
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
		title: _ctx.__("Set up Two Factor Authentication"),
		description: _ctx.__("statamic::messages.two_factor_account_requirement")
	}, {
		default: withCtx(() => [createVNode($setup["Button"], {
			variant: "primary",
			onClick: $setup.openSetupModal,
			text: _ctx.__("Set up"),
			class: "w-full"
		}, null, 8, ["text"]), $setup.setupModalOpen ? (openBlock(), createBlock($setup["TwoFactorSetup"], {
			key: 0,
			"enable-url": $props.routes.enable,
			"recovery-code-urls": $props.routes.recovery_codes,
			onClose: _cache[0] || (_cache[0] = ($event) => $setup.setupModalOpen = false),
			onSetupComplete: $setup.setupComplete
		}, null, 8, ["enable-url", "recovery-code-urls"])) : createCommentVNode("", true)]),
		_: 1
	}, 8, ["title", "description"])], 64);
}
var Setup_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Setup.vue"]]);
export { Setup_default as default };
