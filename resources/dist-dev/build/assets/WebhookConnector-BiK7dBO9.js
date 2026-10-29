import { D as defineComponent, H as openBlock, Mt as toDisplayString, T as createVNode, l as vShow, ot as withCtx, p as Fragment, st as withDirectives, v as createBaseVNode, w as createTextVNode, x as createElementBlock, yt as ref } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { Gt as Label_default, Wt as Field_default, di as Badge_default, oi as Button_default, wi as Icon_default } from "./ui-BUy1lrUe.js";
import { a as ConnectionSummary_default, s as ConnectionList_default } from "./index-BV-PTyMc.js";
// resources/js/components/forms/connections/WebhookConnector.vue?vue&type=script&setup=true&lang.ts
var WebhookConnector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "WebhookConnector",
	props: {
		form: Object,
		modelValue: {
			type: Array,
			default: () => []
		},
		errors: {
			type: Object,
			default: () => ({})
		},
		blueprint: Object,
		meta: {
			type: Object,
			default: () => ({})
		},
		defaults: Object,
		examplePayload: String
	},
	emits: ["update:modelValue"],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const __returned__ = {
			props,
			showExamplePayload: ref(props.modelValue.length === 0),
			get Badge() {
				return Badge_default;
			},
			get Button() {
				return Button_default;
			},
			get Field() {
				return Field_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Label() {
				return Label_default;
			},
			ConnectionList: ConnectionList_default,
			ConnectionSummary: ConnectionSummary_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/connections/WebhookConnector.vue
var _hoisted_1 = { class: "overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs text-gray-800 dark:border-white/10 dark:bg-gray-950/40 dark:text-gray-300" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Label"], {
			text: _ctx.__("Webhooks"),
			class: "mb-2"
		}, null, 8, ["text"]),
		createVNode($setup["ConnectionList"], {
			"model-value": $props.modelValue,
			errors: $props.errors,
			defaults: $props.defaults,
			blueprint: $props.blueprint,
			meta: $props.meta,
			name: "webhook-connection",
			"add-label": _ctx.__("Add Webhook"),
			description: _ctx.__("statamic::messages.webhook_connection_instructions"),
			"delete-heading": _ctx.__("Delete Webhook"),
			"delete-description": _ctx.__("statamic::messages.webhook_connection_delete_confirmation"),
			"always-label": _ctx.__("Always send"),
			"if-label": _ctx.__("Send if..."),
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("update:modelValue", $event))
		}, {
			header: withCtx(({ item: webhook, collapsed }) => [createVNode($setup["Badge"], {
				size: "lg",
				pill: "",
				color: "white",
				class: "px-3 text-gray-950 gap-1"
			}, {
				default: withCtx(() => [createVNode($setup["Icon"], {
					name: "globe-setting",
					class: "size-3.5 me-1 opacity-100! text-purple-600 dark:text-purple-400",
					"aria-hidden": "true"
				}), createTextVNode(" " + toDisplayString(webhook.url || _ctx.__("New Webhook")), 1)]),
				_: 2
			}, 1024), withDirectives(createVNode($setup["ConnectionSummary"], { conditions: webhook.conditions }, null, 8, ["conditions"]), [[vShow, collapsed]])]),
			_: 1
		}, 8, [
			"model-value",
			"errors",
			"defaults",
			"blueprint",
			"meta",
			"add-label",
			"description",
			"delete-heading",
			"delete-description",
			"always-label",
			"if-label"
		]),
		createVNode($setup["Field"], {
			class: "mt-8",
			label: _ctx.__("Example Payload"),
			instructions: _ctx.__("statamic::messages.webhook_connection_payload_instructions")
		}, {
			actions: withCtx(() => [createVNode($setup["Button"], {
				variant: "subtle",
				size: "xs",
				"icon-append": $setup.showExamplePayload ? "chevron-up" : "chevron-down",
				text: $setup.showExamplePayload ? _ctx.__("Hide") : _ctx.__("Show"),
				"aria-expanded": $setup.showExamplePayload,
				onClick: _cache[1] || (_cache[1] = ($event) => $setup.showExamplePayload = !$setup.showExamplePayload)
			}, null, 8, [
				"icon-append",
				"text",
				"aria-expanded"
			])]),
			default: withCtx(() => [withDirectives(createBaseVNode("pre", _hoisted_1, [createBaseVNode("code", null, toDisplayString($props.examplePayload), 1)], 512), [[vShow, $setup.showExamplePayload]])]),
			_: 1
		}, 8, ["label", "instructions"])
	], 64);
}
var WebhookConnector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(WebhookConnector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "WebhookConnector.vue"]]);
export { WebhookConnector_default as default };
