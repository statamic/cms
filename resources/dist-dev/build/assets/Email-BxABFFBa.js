import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { a as useForm } from "./index.esm-B4SStoAz.js";
import { Hn as Panel_default, Ut as Header_default, Vt as Input_default, ct as Rows_default, ft as Cell_default, li as Button_default, lt as Row_default, pt as Table_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/Email.vue
var _sfc_main = {
	__name: "Email",
	props: [
		"sendUrl",
		"defaultEmail",
		"config",
		"errors"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const form = useForm({ email: props.defaultEmail });
		function send() {
			form.post(props.sendUrl);
		}
		const __returned__ = {
			props,
			form,
			send,
			get useForm() {
				return useForm;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get CardPanel() {
				return Panel_default;
			},
			get Input() {
				return Input_default;
			},
			get Button() {
				return Button_default;
			},
			get Table() {
				return Table_default;
			},
			get TableRows() {
				return Rows_default;
			},
			get TableRow() {
				return Row_default;
			},
			get TableCell() {
				return Cell_default;
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
var _hoisted_3 = {
	key: 0,
	class: "mt-4 text-red-600 text-sm"
};
var _hoisted_4 = { key: 0 };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode($setup["Head"], { title: [_ctx.__("Email"), _ctx.__("Utilities")] }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__("Email"),
			icon: "mail-settings"
		}, null, 8, ["title"]),
		createVNode($setup["CardPanel"], { heading: _ctx.__("Send Test Email") }, {
			default: withCtx(() => [createBaseVNode("form", { onSubmit: withModifiers($setup.send, ["prevent"]) }, [createBaseVNode("div", _hoisted_2, [createVNode($setup["Input"], {
				modelValue: $setup.form.email,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.form.email = $event),
				name: "email"
			}, null, 8, ["modelValue"]), createVNode($setup["Button"], {
				variant: "primary",
				type: "submit",
				loading: $setup.form.processing
			}, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Send")), 1)]),
				_: 1
			}, 8, ["loading"])]), $props.errors.email ? (openBlock(), createElementBlock("p", _hoisted_3, toDisplayString($props.errors.email), 1)) : createCommentVNode("", true)], 32)]),
			_: 1
		}, 8, ["heading"]),
		createVNode($setup["CardPanel"], {
			class: "mt-6",
			heading: _ctx.__("Configuration"),
			subheading: _ctx.__("statamic::messages.email_utility_configuration_description", { path: $props.config.path })
		}, {
			default: withCtx(() => [createVNode($setup["Table"], null, {
				default: withCtx(() => [createVNode($setup["TableRows"], null, {
					default: withCtx(() => [
						createVNode($setup["TableRow"], { class: "[&_td:first-child]:font-medium" }, {
							default: withCtx(() => [createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Default Mailer")), 1)]),
								_: 1
							}), createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString($props.config.default), 1)]),
								_: 1
							})]),
							_: 1
						}),
						$props.config.smtp ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [
							createVNode($setup["TableRow"], null, {
								default: withCtx(() => [createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Host")), 1)]),
									_: 1
								}), createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString($props.config.smtp.host), 1)]),
									_: 1
								})]),
								_: 1
							}),
							createVNode($setup["TableRow"], null, {
								default: withCtx(() => [createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Port")), 1)]),
									_: 1
								}), createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString($props.config.smtp.port), 1)]),
									_: 1
								})]),
								_: 1
							}),
							createVNode($setup["TableRow"], null, {
								default: withCtx(() => [createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Encryption")), 1)]),
									_: 1
								}), createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString($props.config.smtp.encryption), 1)]),
									_: 1
								})]),
								_: 1
							}),
							createVNode($setup["TableRow"], null, {
								default: withCtx(() => [createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Username")), 1)]),
									_: 1
								}), createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString($props.config.smtp.username), 1)]),
									_: 1
								})]),
								_: 1
							}),
							createVNode($setup["TableRow"], null, {
								default: withCtx(() => [createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Password")), 1)]),
									_: 1
								}), createVNode($setup["TableCell"], null, {
									default: withCtx(() => [createTextVNode(toDisplayString($props.config.smtp.password), 1)]),
									_: 1
								})]),
								_: 1
							})
						], 64)) : createCommentVNode("", true),
						$props.config.sendmail ? (openBlock(), createBlock($setup["TableRow"], { key: 1 }, {
							default: withCtx(() => [createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Sendmail")), 1)]),
								_: 1
							}), createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString($props.config.sendmail.path), 1)]),
								_: 1
							})]),
							_: 1
						})) : createCommentVNode("", true),
						createVNode($setup["TableRow"], null, {
							default: withCtx(() => [createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Default From Address")), 1)]),
								_: 1
							}), createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString($props.config.from.address), 1)]),
								_: 1
							})]),
							_: 1
						}),
						createVNode($setup["TableRow"], null, {
							default: withCtx(() => [createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Default From Name")), 1)]),
								_: 1
							}), createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString($props.config.from.name), 1)]),
								_: 1
							})]),
							_: 1
						}),
						createVNode($setup["TableRow"], null, {
							default: withCtx(() => [createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Markdown theme")), 1)]),
								_: 1
							}), createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString($props.config.markdown.theme), 1)]),
								_: 1
							})]),
							_: 1
						}),
						createVNode($setup["TableRow"], null, {
							default: withCtx(() => [createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Markdown paths")), 1)]),
								_: 1
							}), createVNode($setup["TableCell"], null, {
								default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList($props.config.markdown.paths, (path, index) => {
									return openBlock(), createElementBlock(Fragment, { key: index }, [createTextVNode(toDisplayString(path), 1), index < $props.config.markdown.paths.length - 1 ? (openBlock(), createElementBlock("br", _hoisted_4)) : createCommentVNode("", true)], 64);
								}), 128))]),
								_: 1
							})]),
							_: 1
						})
					]),
					_: 1
				})]),
				_: 1
			})]),
			_: 1
		}, 8, ["heading", "subheading"])
	]);
}
var Email_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Email.vue"]]);
//#endregion
export { Email_default as default };
