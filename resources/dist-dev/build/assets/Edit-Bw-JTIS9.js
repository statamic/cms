import { $ as useTemplateRef, At as toDisplayString, B as openBlock, C as createVNode, L as onMounted, R as onUnmounted, S as createTextVNode, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { I as Container_default, N as Request, Ut as Header_default, j as Pipeline, li as Button_default, n as DocsCallout_default, r as Item_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/sites/Edit.vue
var _sfc_main = {
	__name: "Edit",
	props: {
		blueprint: Object,
		initialValues: Object,
		meta: Object,
		updateUrl: String
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const container = useTemplateRef("container");
		const values = ref(props.initialValues);
		const errors = ref({});
		const saving = ref(false);
		const pageTitle = computed(() => Statamic.$config.get("multisiteEnabled") ? __("Configure Sites") : __("Configure Site"));
		const initialSiteHandles = computed(() => {
			return Statamic.$config.get("multisiteEnabled") ? props.initialValues.sites.map((site) => site.handle) : [props.initialValues.handle];
		});
		const currentSiteHandles = computed(() => {
			return Statamic.$config.get("multisiteEnabled") ? values.value.sites.map((site) => site.handle) : [values.value.handle];
		});
		const initialHandleChanged = computed(() => initialSiteHandles.value.filter((handle) => !currentSiteHandles.value.includes(handle)).length > 0);
		const initialHandleChangedWarning = computed(() => __("Warning! Changing a site handle may break existing site content!"));
		function save() {
			if (initialHandleChanged.value && !confirm(initialHandleChangedWarning.value)) return;
			new Pipeline().provide({
				container,
				errors,
				saving
			}).through([new Request(props.updateUrl, "patch")]).then((response) => {
				Statamic.$toast.success(__("Saved"));
				if (Statamic.$config.get("multisiteEnabled")) window.location.reload();
			});
		}
		let saveKeyBinding;
		onMounted(() => {
			saveKeyBinding = Statamic.$keys.bindGlobal(["mod+s"], (e) => {
				e.preventDefault();
				save();
			});
		});
		onUnmounted(() => saveKeyBinding.destroy());
		const __returned__ = {
			props,
			container,
			values,
			errors,
			saving,
			pageTitle,
			initialSiteHandles,
			currentSiteHandles,
			initialHandleChanged,
			initialHandleChangedWarning,
			save,
			get saveKeyBinding() {
				return saveKeyBinding;
			},
			set saveKeyBinding(v) {
				saveKeyBinding = v;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get CommandPaletteItem() {
				return Item_default;
			},
			get Button() {
				return Button_default;
			},
			get PublishContainer() {
				return Container_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			computed,
			onMounted,
			onUnmounted,
			ref,
			useTemplateRef,
			get Pipeline() {
				return Pipeline;
			},
			get Request() {
				return Request;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "max-w-page mx-auto" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Configure Sites") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: $setup.pageTitle,
			icon: "site"
		}, {
			default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
				category: _ctx.$commandPalette.category.Actions,
				text: _ctx.__("Save"),
				icon: "save",
				action: $setup.save,
				prioritize: ""
			}, {
				default: withCtx(({ text, action }) => [createVNode($setup["Button"], {
					type: "submit",
					variant: "primary",
					onClick: action
				}, {
					default: withCtx(() => [createTextVNode(toDisplayString(text), 1)]),
					_: 2
				}, 1032, ["onClick"])]),
				_: 1
			}, 8, ["category", "text"])]),
			_: 1
		}, 8, ["title"]),
		$props.blueprint ? (openBlock(), createBlock($setup["PublishContainer"], {
			key: 0,
			ref: "container",
			name: "sites",
			reference: "sites",
			blueprint: $props.blueprint,
			modelValue: $setup.values,
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.values = $event),
			meta: $props.meta,
			errors: $setup.errors
		}, null, 8, [
			"blueprint",
			"modelValue",
			"meta",
			"errors"
		])) : createCommentVNode("", true),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Multi-Site"),
			url: "multi-site"
		}, null, 8, ["topic"])
	])], 64);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
