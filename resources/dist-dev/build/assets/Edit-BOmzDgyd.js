import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, it as withCtx, u as withModifiers, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Fn as Sortable, In as SwapAnimation, Ut as Header_default, li as Button_default } from "./ui-BfR7XN6t.js";
import { T as SuggestsConditionalFields_default, r as Head_default, w as Sections_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/fieldsets/Edit.vue
var _sfc_main = {
	mixins: [SuggestsConditionalFields_default],
	components: {
		Head: Head_default,
		Sections: Sections_default,
		Header: Header_default,
		Button: Button_default
	},
	props: ["action", "initialFieldset"],
	data() {
		return {
			method: "patch",
			initialTitle: this.initialFieldset.title,
			fieldset: clone(this.initialFieldset),
			errors: {},
			editingField: null
		};
	},
	computed: {
		sections: {
			get() {
				return this.fieldset.sections;
			},
			set(sections) {
				this.fieldset.sections = sections;
			}
		},
		fieldsForConditionSuggestions() {
			return this.sections.reduce((fields, section) => fields.concat(section.fields || []), []);
		}
	},
	mounted() {
		this.makeSortable();
	},
	watch: {
		fieldset: {
			deep: true,
			handler() {
				this.$dirty.add("fieldsets");
			}
		},
		sections: {
			deep: true,
			handler() {
				this.$nextTick(() => this.makeSortable());
			}
		}
	},
	methods: {
		save() {
			this.$axios[this.method](this.action, this.fieldset).then((response) => {
				this.$toast.success(__("Saved"));
				this.errors = {};
				this.$dirty.remove("fieldsets");
			}).catch((e) => {
				this.$toast.error(e.response.data.message);
				this.errors = e.response.data.errors;
			});
		},
		makeSortable() {
			if (this.sortableSections) this.sortableSections.destroy();
			if (this.sortableFields) this.sortableFields.destroy();
			this.sortableSections = new Sortable(this.$el.querySelector(".blueprint-sections"), {
				draggable: ".blueprint-section",
				handle: ".blueprint-section-drag-handle",
				mirror: {
					constrainDimensions: true,
					appendTo: "body"
				},
				plugins: [SwapAnimation]
			}).on("sortable:stop", (e) => {
				this.fieldset.sections.splice(e.newIndex, 0, this.fieldset.sections.splice(e.oldIndex, 1)[0]);
			});
			this.sortableFields = new Sortable(this.$el.querySelectorAll(".blueprint-section-draggable-zone"), {
				draggable: ".blueprint-section-field",
				handle: ".blueprint-drag-handle",
				mirror: {
					constrainDimensions: true,
					appendTo: "body"
				},
				plugins: [SwapAnimation]
			}).on("sortable:stop", (e) => {
				const oldSection = this.fieldset.sections.find((section) => section._id === e.oldContainer.dataset.section);
				const newSection = this.fieldset.sections.find((section) => section._id === e.newContainer.dataset.section);
				if (!oldSection || !newSection) return;
				const field = oldSection.fields.splice(e.oldIndex, 1)[0];
				newSection.fields.splice(e.newIndex, 0, field);
			});
		}
	},
	created() {
		this.$keys.bindGlobal(["mod+s"], (e) => {
			e.preventDefault();
			this.save();
		});
		this.$events.$on("root-form-save", () => {
			this.save();
		});
	},
	beforeUnmount() {
		this.$events.$off("root-form-save");
		if (this.sortableSections) this.sortableSections.destroy();
		if (this.sortableFields) this.sortableFields.destroy();
	}
};
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_Button = resolveComponent("Button");
	const _component_ui_command_palette_item = resolveComponent("ui-command-palette-item");
	const _component_Header = resolveComponent("Header");
	const _component_ui_input = resolveComponent("ui-input");
	const _component_ui_field = resolveComponent("ui-field");
	const _component_ui_card = resolveComponent("ui-card");
	const _component_ui_panel = resolveComponent("ui-panel");
	const _component_sections = resolveComponent("sections");
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode(_component_Head, { title: _ctx.__("Edit Fieldset") }, null, 8, ["title"]),
		createVNode(_component_Header, {
			title: _ctx.__("Edit Fieldset"),
			icon: "fieldsets"
		}, {
			default: withCtx(() => [createVNode(_component_ui_command_palette_item, {
				category: _ctx.$commandPalette.category.Actions,
				text: _ctx.__("Save"),
				icon: "save",
				action: $options.save,
				prioritize: ""
			}, {
				default: withCtx(({ text, url, icon, action }) => [createVNode(_component_Button, {
					type: "submit",
					variant: "primary",
					onClick: withModifiers(action, ["prevent"]),
					textContent: toDisplayString(text)
				}, null, 8, ["onClick", "textContent"])]),
				_: 1
			}, 8, [
				"category",
				"text",
				"action"
			])]),
			_: 1
		}, 8, ["title"]),
		createVNode(_component_ui_panel, { heading: _ctx.__("Settings") }, {
			default: withCtx(() => [createVNode(_component_ui_card, null, {
				default: withCtx(() => [createVNode(_component_ui_field, {
					label: _ctx.__("Title"),
					instructions: _ctx.__("messages.fieldsets_title_instructions"),
					errors: $data.errors.title
				}, {
					default: withCtx(() => [createVNode(_component_ui_input, {
						modelValue: $data.fieldset.title,
						"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.fieldset.title = $event)
					}, null, 8, ["modelValue"])]),
					_: 1
				}, 8, [
					"label",
					"instructions",
					"errors"
				])]),
				_: 1
			})]),
			_: 1
		}, 8, ["heading"]),
		createVNode(_component_sections, {
			class: "mt-8",
			"tab-id": "fieldset",
			"initial-sections": $options.sections,
			"show-section-collapsible-field": true,
			"exclude-fieldset": $data.fieldset.handle,
			"with-command-palette": "",
			onUpdated: _cache[1] || (_cache[1] = ($event) => $options.sections = $event)
		}, null, 8, ["initial-sections", "exclude-fieldset"])
	]);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
