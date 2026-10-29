import { D as defineComponent, H as openBlock, K as renderList, Mt as toDisplayString, T as createVNode, _ as computed, b as createCommentVNode, d as withModifiers, l as vShow, nt as watch, ot as withCtx, p as Fragment, st as withDirectives, v as createBaseVNode, w as createTextVNode, x as createElementBlock, y as createBlock, yt as ref } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { K as axios, s as usePage } from "./index.esm-D1UivzAX.js";
import { Gt as Label_default, Jt as ErrorMessage_default, Mt as Stack_default, di as Badge_default, in as Description_default, oi as Button_default, wi as Icon_default } from "./ui-BUy1lrUe.js";
import { t as __$1 } from "./globals-D3CqQKLv.js";
import { a as ConnectionSummary_default, s as ConnectionList_default } from "./index-BV-PTyMc.js";
// resources/js/components/forms/connections/EmailPreview.vue?vue&type=script&setup=true&lang.ts
var Format = /* @__PURE__ */ function(Format) {
	Format["Html"] = "html";
	Format["Text"] = "text";
	return Format;
}(Format || {});
var EmailPreview_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "EmailPreview",
	props: {
		modelValue: {},
		url: {}
	},
	emits: ["update:modelValue"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const emit = __emit;
		const props = __props;
		const preview = ref(null);
		const error = ref(null);
		const loading = ref(false);
		const envelope = computed(() => [
			{
				label: __$1("Subject"),
				value: preview.value?.subject
			},
			{
				label: __$1("From"),
				value: preview.value?.from.join(", ")
			},
			{
				label: __$1("To"),
				value: preview.value?.to.join(", ")
			},
			{
				label: __$1("Cc"),
				value: preview.value?.cc.join(", ")
			},
			{
				label: __$1("Bcc"),
				value: preview.value?.bcc.join(", ")
			},
			{
				label: __$1("Reply To"),
				value: preview.value?.reply_to.join(", ")
			},
			{
				label: __$1("Attachments"),
				value: preview.value?.attachments && __$1("statamic::messages.email_connection_preview_attachments")
			}
		].filter((row) => row.value));
		const load = (email) => {
			preview.value = null;
			error.value = null;
			loading.value = true;
			axios.post(props.url, email).then((response) => preview.value = response.data).catch((e) => error.value = e.response?.data?.message ?? __$1("Something went wrong")).finally(() => loading.value = false);
		};
		const close = () => emit("update:modelValue", null);
		watch(() => props.modelValue, (email) => email && load(email), { immediate: true });
		const __returned__ = {
			Format,
			emit,
			props,
			preview,
			error,
			loading,
			envelope,
			load,
			close,
			get Description() {
				return Description_default;
			},
			get ErrorMessage() {
				return ErrorMessage_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Stack() {
				return Stack_default;
			},
			get __() {
				return __$1;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/connections/EmailPreview.vue
var _hoisted_1$1 = { class: "flex h-full flex-col" };
var _hoisted_2$1 = { class: "space-y-3 border-b border-gray-300 px-6 py-4 dark:border-gray-700" };
var _hoisted_3 = {
	key: 0,
	class: "grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm"
};
var _hoisted_4 = { class: "text-gray-600 dark:text-gray-400" };
var _hoisted_5 = { class: "break-words text-gray-900 dark:text-gray-100" };
var _hoisted_6 = {
	key: 0,
	class: "flex flex-1 items-center justify-center"
};
var _hoisted_7 = ["srcdoc", "title"];
var _hoisted_8 = {
	key: 3,
	class: "flex-1 overflow-auto whitespace-pre-wrap p-6 font-mono text-sm text-gray-900 dark:text-gray-100"
};
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Stack"], {
		open: $props.modelValue !== null,
		size: "half",
		inset: "",
		title: $setup.__("Preview Email"),
		icon: "mail",
		"onUpdate:open": _cache[0] || (_cache[0] = ($event) => $event || $setup.close())
	}, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$1, [createBaseVNode("div", _hoisted_2$1, [createVNode($setup["Description"], { text: $setup.preview?.sample === false ? $setup.__("statamic::messages.email_connection_preview_latest_instructions") : $setup.__("statamic::messages.email_connection_preview_sample_instructions") }, null, 8, ["text"]), $setup.preview ? (openBlock(), createElementBlock("dl", _hoisted_3, [(openBlock(true), createElementBlock(Fragment, null, renderList($setup.envelope, (row) => {
			return openBlock(), createElementBlock(Fragment, { key: row.label }, [createBaseVNode("dt", _hoisted_4, toDisplayString(row.label), 1), createBaseVNode("dd", _hoisted_5, toDisplayString(row.value), 1)], 64);
		}), 128))])) : createCommentVNode("", true)]), $setup.loading ? (openBlock(), createElementBlock("div", _hoisted_6, [createVNode($setup["Icon"], { name: "loading" })])) : $setup.error ? (openBlock(), createBlock($setup["ErrorMessage"], {
			key: 1,
			class: "p-6"
		}, {
			default: withCtx(() => [createTextVNode(toDisplayString($setup.error), 1)]),
			_: 1
		})) : $setup.preview?.format === $setup.Format.Html ? (openBlock(), createElementBlock("iframe", {
			key: 2,
			srcdoc: $setup.preview.body,
			sandbox: "allow-popups allow-popups-to-escape-sandbox",
			class: "w-full flex-1 border-0 bg-white",
			title: $setup.__("Preview Email")
		}, null, 8, _hoisted_7)) : $setup.preview ? (openBlock(), createElementBlock("pre", _hoisted_8, toDisplayString($setup.preview.body), 1)) : createCommentVNode("", true)])]),
		_: 1
	}, 8, ["open", "title"]);
}
var EmailPreview_default = /*#__PURE__*/ _plugin_vue_export_helper_default(EmailPreview_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render$1], ["__file", "EmailPreview.vue"]]);
// resources/js/components/forms/connections/EmailConnector.vue?vue&type=script&setup=true&lang.ts
var EmailConnector_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "EmailConnector",
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
		previewUrl: String
	},
	emits: ["update:modelValue"],
	setup(__props, { expose: __expose }) {
		__expose();
		const suggestableFields = usePage().props.suggestableFields ?? [];
		const previewing = ref(null);
		const recipients = (to) => [to].flat().map((recipient) => {
			if (!recipient.startsWith("field:")) return recipient;
			const handle = recipient.replace("field:", "");
			const field = suggestableFields.find((field) => field.handle === handle);
			return __(field?.config?.display ?? handle);
		}).join(", ");
		const __returned__ = {
			suggestableFields,
			previewing,
			recipients,
			get Badge() {
				return Badge_default;
			},
			get Button() {
				return Button_default;
			},
			get Icon() {
				return Icon_default;
			},
			get Label() {
				return Label_default;
			},
			ConnectionList: ConnectionList_default,
			ConnectionSummary: ConnectionSummary_default,
			EmailPreview: EmailPreview_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
});
// resources/js/components/forms/connections/EmailConnector.vue
var _hoisted_1 = { class: "flex items-center justify-between w-full gap-2 me-1" };
var _hoisted_2 = { class: "flex items-center gap-1.75" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Label"], {
			text: _ctx.__("Emails"),
			class: "mb-2"
		}, null, 8, ["text"]),
		createVNode($setup["ConnectionList"], {
			"model-value": $props.modelValue,
			errors: $props.errors,
			defaults: $props.defaults,
			blueprint: $props.blueprint,
			meta: $props.meta,
			name: "email-connection",
			"add-label": _ctx.__("Add Email"),
			description: _ctx.__("statamic::messages.email_connection_instructions"),
			"delete-heading": _ctx.__("Delete Email"),
			"delete-description": _ctx.__("statamic::messages.email_connection_delete_confirmation"),
			"always-label": _ctx.__("Always send"),
			"if-label": _ctx.__("Send if..."),
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("update:modelValue", $event))
		}, {
			header: withCtx(({ item: email, collapsed }) => [createBaseVNode("div", _hoisted_1, [createBaseVNode("div", _hoisted_2, [createVNode($setup["Badge"], {
				size: "lg",
				pill: "",
				color: "white",
				class: "px-3 text-gray-950 gap-1"
			}, {
				default: withCtx(() => [createVNode($setup["Icon"], {
					name: "mail-sign-at",
					class: "size-3.5 me-1 opacity-100! text-blue-600 dark:text-blue-400",
					"aria-hidden": "true"
				}), createTextVNode(" " + toDisplayString(email.to?.length ? _ctx.__("Message sent to :email", { email: $setup.recipients(email.to) }) : _ctx.__("New Email")), 1)]),
				_: 2
			}, 1024), withDirectives(createVNode($setup["ConnectionSummary"], {
				conditions: email.conditions,
				fallback: email.subject
			}, null, 8, ["conditions", "fallback"]), [[vShow, collapsed]])]), createVNode($setup["Button"], {
				icon: "eye",
				size: "xs",
				text: _ctx.__("Preview"),
				onClick: withModifiers(($event) => $setup.previewing = email, ["stop"])
			}, null, 8, ["text", "onClick"])])]),
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
		createVNode($setup["EmailPreview"], {
			modelValue: $setup.previewing,
			"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.previewing = $event),
			url: $props.previewUrl
		}, null, 8, ["modelValue", "url"])
	], 64);
}
var EmailConnector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(EmailConnector_vue_vue_type_script_setup_true_lang_default, [["render", _sfc_render], ["__file", "EmailConnector.vue"]]);
export { EmailConnector_default as default };
