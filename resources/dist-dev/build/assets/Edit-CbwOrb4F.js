import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $t as Menu_default, I as Container_default, Ut as Header_default, ci as Group_default, en as Label_default, li as Button_default, nn as Dropdown_default, r as Item_default$1, tn as Item_default, z as Tabs_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/preferences/Edit.vue
var _sfc_main = {
	components: {
		Head: Head_default,
		Header: Header_default,
		Button: Button_default,
		ButtonGroup: Group_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default,
		DropdownLabel: Label_default,
		PublishContainer: Container_default,
		PublishTabs: Tabs_default,
		CommandPaletteItem: Item_default$1
	},
	props: {
		blueprint: {
			required: true,
			type: Object
		},
		meta: {
			required: true,
			type: Object
		},
		values: {
			required: true,
			type: Object
		},
		title: {
			required: true,
			type: String
		},
		actionUrl: {
			required: true,
			type: String
		},
		saveAsOptions: {
			type: Array,
			default: () => []
		}
	},
	data() {
		return {
			name: "base",
			saving: false,
			currentValues: this.values,
			error: null,
			errors: {},
			isDropdownOpen: false
		};
	},
	computed: {
		hasSaveAsOptions() {
			return this.saveAsOptions.length;
		},
		isDirty() {
			return this.$dirty.has(this.name);
		}
	},
	methods: {
		clearErrors() {
			this.error = null;
			this.errors = {};
		},
		save() {
			this.saveAs(this.actionUrl);
		},
		saveAs(url) {
			this.saving = true;
			this.clearErrors();
			this.isDropdownOpen = false;
			this.$axios.patch(url, this.currentValues).then(() => {
				this.$refs.container.saved();
				this.$nextTick(() => location.reload());
			}).catch((e) => this.handleAxiosError(e));
		},
		handleAxiosError(e) {
			this.saving = false;
			if (e.response && e.response.status === 422) {
				const { message, errors } = e.response.data;
				this.error = message;
				this.errors = errors;
				this.$toast.error(message);
			} else {
				const message = data_get(e, "response.data.message");
				this.$toast.error(message || e);
				console.log(e);
			}
		},
		toggleDropdown() {
			this.isDropdownOpen = !this.isDropdownOpen;
		},
		onDropdownOpen() {
			this.isDropdownOpen = true;
		},
		onDropdownClose() {
			this.isDropdownOpen = false;
		}
	},
	created() {
		this.$keys.bindGlobal(["mod+s"], (e) => {
			e.preventDefault();
			this.save();
		});
		this.$keys.bindGlobal(["escape"], (e) => {
			if (this.isDropdownOpen) {
				e.preventDefault();
				this.isDropdownOpen = false;
			}
		});
	},
	watch: { saving(saving) {
		this.$progress.loading("preferences-edit-form", saving);
	} }
};
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
var _hoisted_2 = {
	key: 0,
	id: "save-options-description",
	class: "sr-only"
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_Button = resolveComponent("Button");
	const _component_CommandPaletteItem = resolveComponent("CommandPaletteItem");
	const _component_DropdownLabel = resolveComponent("DropdownLabel");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ButtonGroup = resolveComponent("ButtonGroup");
	const _component_Header = resolveComponent("Header");
	const _component_PublishTabs = resolveComponent("PublishTabs");
	const _component_PublishContainer = resolveComponent("PublishContainer");
	return openBlock(), createElementBlock(Fragment, null, [createVNode(_component_Head, { title: $props.title }, null, 8, ["title"]), createVNode(_component_PublishContainer, {
		ref: "container",
		name: $data.name,
		blueprint: $props.blueprint,
		modelValue: $data.currentValues,
		"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.currentValues = $event),
		reference: "collection",
		meta: $props.meta,
		errors: $data.errors,
		"as-config": ""
	}, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1, [createVNode(_component_Header, {
			title: $props.title,
			icon: "preferences"
		}, {
			default: withCtx(() => [createVNode(_component_ButtonGroup, {
				role: "group",
				"aria-label": "Save options"
			}, {
				default: withCtx(() => [createVNode(_component_CommandPaletteItem, {
					category: _ctx.$commandPalette.category.Actions,
					text: _ctx.__("Save"),
					icon: "save",
					action: $options.save,
					prioritize: ""
				}, {
					default: withCtx(({ text, action }) => [createVNode(_component_Button, {
						type: "submit",
						variant: "primary",
						text,
						onClick: action,
						"aria-describedby": $options.hasSaveAsOptions ? "save-options-description" : void 0
					}, null, 8, [
						"text",
						"onClick",
						"aria-describedby"
					])]),
					_: 1
				}, 8, [
					"category",
					"text",
					"action"
				]), $options.hasSaveAsOptions ? (openBlock(), createBlock(_component_Dropdown, {
					key: 0,
					align: "end",
					"aria-label": _ctx.__("Additional save options"),
					onOpen: $options.onDropdownOpen,
					onClose: $options.onDropdownClose
				}, {
					trigger: withCtx(() => [createVNode(_component_Button, {
						icon: "chevron-down",
						variant: "primary",
						"aria-label": _ctx.__("Open save options menu"),
						"aria-expanded": $data.isDropdownOpen,
						"aria-haspopup": true,
						"aria-describedby": "save-options-description",
						onClick: $options.toggleDropdown
					}, null, 8, [
						"aria-label",
						"aria-expanded",
						"onClick"
					])]),
					default: withCtx(() => [createVNode(_component_DropdownMenu, {
						role: "menu",
						"aria-labelledby": "save-options-label"
					}, {
						default: withCtx(() => [createVNode(_component_DropdownLabel, { id: "save-options-label" }, {
							default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Save to")) + "...", 1)]),
							_: 1
						}), (openBlock(true), createElementBlock(Fragment, null, renderList($props.saveAsOptions, (option) => {
							return openBlock(), createBlock(_component_DropdownItem, {
								key: option.url,
								text: option.label,
								onClick: ($event) => $options.saveAs(option.url),
								role: "menuitem",
								"aria-label": `${_ctx.__("Save to")} ${option.label}`
							}, null, 8, [
								"text",
								"onClick",
								"aria-label"
							]);
						}), 128))]),
						_: 1
					})]),
					_: 1
				}, 8, [
					"aria-label",
					"onOpen",
					"onClose"
				])) : createCommentVNode("", true)]),
				_: 1
			}), $options.hasSaveAsOptions ? (openBlock(), createElementBlock("div", _hoisted_2, toDisplayString(_ctx.__("Press enter to access additional save options")), 1)) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["title"]), createVNode(_component_PublishTabs)])]),
		_: 1
	}, 8, [
		"name",
		"blueprint",
		"modelValue",
		"meta",
		"errors"
	])], 64);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [
	["render", _sfc_render],
	["__scopeId", "data-v-42d33bbd"],
	["__file", "Edit.vue"]
]);
//#endregion
export { Edit_default as default };
