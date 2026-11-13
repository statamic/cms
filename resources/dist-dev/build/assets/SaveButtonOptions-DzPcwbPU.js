import { J as renderSlot, Pt as toDisplayString, T as createVNode, U as openBlock, Y as resolveComponent, Z as resolveDynamicComponent, b as createCommentVNode, ct as withCtx, y as createBlock } from "./vue.esm-bundler-T63OhLKP.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { Et as Group_default, Tt as Item_default, ci as Button_default, en as Menu_default, rn as Dropdown_default, tn as Label_default } from "./ui-DSDOemJ5.js";
// resources/js/components/publish/SaveButtonOptions.vue
var _sfc_main = {
	components: {
		Button: Button_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownLabel: Label_default,
		Radio: Item_default,
		RadioGroup: Group_default
	},
	props: {
		showOptions: {
			type: Boolean,
			default: true
		},
		preferencesPrefix: {
			type: String,
			required: true
		}
	},
	data() {
		return { currentOption: null };
	},
	computed: {
		preferencesKey() {
			return `${this.preferencesPrefix}.after_save`;
		},
		wrapperComponent() {
			return this.showOptions ? "ui-button-group" : "div";
		}
	},
	mounted() {
		this.setInitialValue();
		this.$watch("currentOption", (value) => this.setPreference(value));
	},
	methods: {
		setInitialValue() {
			this.currentOption = this.$preferences.get(this.preferencesKey) || this.$config.get("afterSave");
		},
		setPreference(value) {
			if (value === this.$preferences.get(this.preferencesKey)) return;
			this.$preferences.set(this.preferencesKey, value);
		}
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Button = resolveComponent("Button");
	const _component_DropdownLabel = resolveComponent("DropdownLabel");
	const _component_Radio = resolveComponent("Radio");
	const _component_RadioGroup = resolveComponent("RadioGroup");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	return openBlock(), createBlock(resolveDynamicComponent($options.wrapperComponent), null, {
		default: withCtx(() => [renderSlot(_ctx.$slots, "default"), $props.showOptions ? (openBlock(), createBlock(_component_Dropdown, {
			key: 0,
			align: "end"
		}, {
			trigger: withCtx(() => [createVNode(_component_Button, {
				variant: "primary",
				icon: "chevron-down",
				"aria-label": _ctx.__("Save options")
			}, null, 8, ["aria-label"])]),
			default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
				default: withCtx(() => [createVNode(_component_DropdownLabel, {
					id: "after-saving-label",
					textContent: toDisplayString(_ctx.__("After Saving"))
				}, null, 8, ["textContent"]), createVNode(_component_RadioGroup, {
					modelValue: $data.currentOption,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.currentOption = $event),
					"aria-labelledby": "after-saving-label"
				}, {
					default: withCtx(() => [
						createVNode(_component_Radio, {
							label: _ctx.__("Go To Listing"),
							value: "listing"
						}, null, 8, ["label"]),
						createVNode(_component_Radio, {
							label: _ctx.__("Continue Editing"),
							value: "continue_editing"
						}, null, 8, ["label"]),
						createVNode(_component_Radio, {
							label: _ctx.__("Create Another"),
							value: "create_another"
						}, null, 8, ["label"])
					]),
					_: 1
				}, 8, ["modelValue"])]),
				_: 1
			})]),
			_: 1
		})) : createCommentVNode("", true)]),
		_: 3
	});
}
var SaveButtonOptions_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "SaveButtonOptions.vue"]]);
export { SaveButtonOptions_default as t };
