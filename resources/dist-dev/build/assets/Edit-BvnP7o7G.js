import { B as openBlock, C as createVNode, G as renderSlot, K as resolveComponent, W as renderList, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $t as Menu_default, A as BeforeSaveHooks, F as Components_default, I as Container_default, M as PipelineStopped, N as Request, Ut as Header_default, Xt as Separator_default, j as Pipeline, k as AfterSaveHooks, li as Button_default, nn as Dropdown_default, tn as Item_default, u as ItemActions_default, v as clone, z as Tabs_default } from "./ui-BfR7XN6t.js";
import { N as HasActions_default, r as Head_default } from "./index-Bu3QBQkS.js";
import { t as SiteSelector_default } from "./SiteSelector-BCgXo6du.js";
//#region resources/js/components/globals/PublishForm.vue
var _sfc_main$1 = {
	mixins: [HasActions_default],
	components: {
		PublishComponents: Components_default,
		PublishContainer: Container_default,
		PublishTabs: Tabs_default,
		Dropdown: Dropdown_default,
		DropdownItem: Item_default,
		DropdownSeparator: Separator_default,
		Button: Button_default,
		DropdownMenu: Menu_default,
		Header: Header_default,
		SiteSelector: SiteSelector_default,
		ItemActions: ItemActions_default
	},
	props: {
		publishContainer: String,
		initialReference: String,
		initialFieldset: Object,
		initialValues: Object,
		initialMeta: Object,
		initialTitle: String,
		initialHandle: String,
		initialBlueprintHandle: String,
		initialLocalizations: Array,
		initialLocalizedFields: Array,
		initialHasOrigin: Boolean,
		initialOriginValues: Object,
		initialOriginMeta: Object,
		initialSite: String,
		globalsUrl: String,
		initialActions: Object,
		method: String,
		isCreating: Boolean,
		initialReadOnly: Boolean,
		canEdit: Boolean,
		canConfigure: Boolean,
		configureUrl: String,
		canEditBlueprint: Boolean
	},
	data() {
		return {
			actions: this.initialActions,
			localizing: false,
			fieldset: this.initialFieldset,
			title: this.initialTitle,
			values: clone(this.initialValues),
			visibleValues: {},
			meta: clone(this.initialMeta),
			localizations: clone(this.initialLocalizations),
			localizedFields: this.initialLocalizedFields,
			hasOrigin: this.initialHasOrigin,
			originValues: this.initialOriginValues || {},
			originMeta: this.initialOriginMeta || {},
			site: this.initialSite,
			reference: this.initialReference,
			readOnly: this.initialReadOnly,
			syncFieldConfirmationText: __("messages.sync_entry_field_confirmation_text"),
			pendingLocalization: null
		};
	},
	setup() {
		const savingRef = ref(false);
		const errorsRef = ref({});
		return {
			savingRef: computed(() => savingRef),
			errorsRef: computed(() => errorsRef)
		};
	},
	computed: {
		containerRef() {
			return computed(() => this.$refs.container);
		},
		saving() {
			return this.savingRef.value;
		},
		errors() {
			return this.errorsRef.value;
		},
		somethingIsLoading() {
			return !this.$progress.isComplete();
		},
		canSave() {
			return !this.readOnly && !this.somethingIsLoading;
		},
		showLocalizationSelector() {
			return this.localizations.length > 1;
		},
		isBase() {
			return this.publishContainer === "base";
		},
		isDirty() {
			return this.$dirty.has(this.publishContainer);
		},
		activeLocalization() {
			return this.localizations.find((l) => l.active);
		},
		originLocalization() {
			return this.localizations.find((l) => l.origin);
		}
	},
	watch: { saving(saving) {
		this.$progress.loading(`${this.publishContainer}-global-publish-form`, saving);
	} },
	methods: {
		save() {
			if (!this.canSave) return;
			new Pipeline().provide({
				container: this.containerRef,
				errors: this.errorsRef,
				saving: this.savingRef
			}).through([
				new BeforeSaveHooks("global-set", {
					globalSet: this.initialHandle,
					values: this.values
				}),
				new Request(this.actions.save, this.method, {
					_blueprint: this.fieldset.handle,
					_localized: this.localizedFields
				}),
				new AfterSaveHooks("global-set", {
					globalSet: this.initialHandle,
					reference: this.initialReference
				})
			]).then((response) => {
				if (!this.isCreating) this.$toast.success(__("Saved"));
				this.$nextTick(() => this.$emit("saved", response));
			}).catch((e) => {
				if (!(e instanceof PipelineStopped)) {
					this.$toast.error(__("Something went wrong"));
					console.error(e);
				}
			});
		},
		localizationSelected(localizationHandle) {
			let localization = this.localizations.find((localization) => localization.handle === localizationHandle);
			if (localization.active) return;
			if (this.isDirty) {
				this.pendingLocalization = localization;
				return;
			}
			this.switchToLocalization(localization);
		},
		confirmSwitchLocalization() {
			this.switchToLocalization(this.pendingLocalization);
			this.pendingLocalization = null;
		},
		switchToLocalization(localization) {
			this.localizing = localization.handle;
			if (this.publishContainer === "base") window.history.replaceState({}, "", localization.url + window.location.hash);
			this.$axios.get(localization.url).then((response) => {
				const data = response.data;
				this.values = data.values;
				this.originValues = data.originValues;
				this.meta = data.meta;
				this.localizations = data.localizations;
				this.localizedFields = data.localizedFields;
				this.hasOrigin = data.hasOrigin;
				this.actions = data.actions;
				this.fieldset = data.blueprint;
				this.site = localization.handle;
				this.reference = data.reference;
				this.localizing = false;
				this.afterActionSuccessfullyCompleted(data);
				this.$nextTick(() => this.$refs.container.clearDirtyState());
			});
		},
		localizationStatusText(localization) {
			return localization.exists ? "This global set exists in this site." : "This global set does not exist for this site.";
		},
		addToCommandPalette() {
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Save"),
				icon: "save",
				action: () => this.save(),
				prioritize: true
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Configure"),
				icon: "cog",
				when: () => this.canConfigure,
				url: this.configureUrl
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Edit Blueprint"),
				icon: "blueprint-edit",
				when: () => this.canEditBlueprint,
				url: this.actions.editBlueprint
			});
		},
		afterActionSuccessfullyCompleted(response) {
			if (response.itemActions) this.itemActions = response.itemActions;
		}
	},
	mounted() {
		this.$keys.bindGlobal(["mod+s"], (e) => {
			e.preventDefault();
			this.save();
		});
		this.addToCommandPalette();
	},
	created() {
		window.history.replaceState({}, document.title, document.location.href.replace("created=true", ""));
	}
};
var _hoisted_1$1 = { class: "hidden items-center gap-2 sm:gap-3 md:flex" };
var _hoisted_2 = {
	key: 0,
	class: "px-8 py-16 border border-dashed border-gray-400 dark:border-gray-600 rounded-lg text-center"
};
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Button = resolveComponent("Button");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_SiteSelector = resolveComponent("SiteSelector");
	const _component_Header = resolveComponent("Header");
	const _component_ui_heading = resolveComponent("ui-heading");
	const _component_PublishContainer = resolveComponent("PublishContainer");
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	return openBlock(), createElementBlock("div", null, [
		createVNode(_component_Header, {
			title: _ctx.__($data.title),
			icon: "globals"
		}, {
			default: withCtx(() => [
				_ctx.hasItemActions ? (openBlock(), createBlock(_component_ItemActions, {
					key: 0,
					url: _ctx.itemActionUrl,
					actions: _ctx.itemActions,
					item: $props.initialHandle,
					onStarted: _ctx.actionStarted,
					onCompleted: _ctx.actionCompleted
				}, {
					default: withCtx(({ actions: preparedActions }) => [$props.canConfigure || $props.canEditBlueprint || _ctx.hasItemActions ? (openBlock(), createBlock(_component_Dropdown, { key: 0 }, {
						trigger: withCtx(() => [createVNode(_component_Button, {
							icon: "dots",
							variant: "ghost",
							"aria-label": _ctx.__("Open dropdown menu")
						}, null, 8, ["aria-label"])]),
						default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
							default: withCtx(() => [
								$props.canConfigure ? (openBlock(), createBlock(_component_DropdownItem, {
									key: 0,
									text: _ctx.__("Configure"),
									icon: "cog",
									href: $props.configureUrl
								}, null, 8, ["text", "href"])) : createCommentVNode("", true),
								$props.canEditBlueprint ? (openBlock(), createBlock(_component_DropdownItem, {
									key: 1,
									text: _ctx.__("Edit Blueprint"),
									icon: "blueprint-edit",
									href: $data.actions.editBlueprint
								}, null, 8, ["text", "href"])) : createCommentVNode("", true),
								_ctx.hasItemActions && ($props.canConfigure || $props.canEditBlueprint) ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 2 })) : createCommentVNode("", true),
								(openBlock(true), createElementBlock(Fragment, null, renderList(preparedActions, (action) => {
									return openBlock(), createBlock(_component_DropdownItem, {
										key: action.handle,
										text: _ctx.__(action.title),
										icon: action.icon,
										variant: action.dangerous ? "destructive" : "default",
										onClick: action.run
									}, null, 8, [
										"text",
										"icon",
										"variant",
										"onClick"
									]);
								}), 128))
							]),
							_: 2
						}, 1024)]),
						_: 2
					}, 1024)) : createCommentVNode("", true)]),
					_: 1
				}, 8, [
					"url",
					"actions",
					"item",
					"onStarted",
					"onCompleted"
				])) : $props.canConfigure || $props.canEditBlueprint ? (openBlock(), createBlock(_component_Dropdown, { key: 1 }, {
					trigger: withCtx(() => [createVNode(_component_Button, {
						icon: "dots",
						variant: "ghost",
						"aria-label": _ctx.__("Open dropdown menu")
					}, null, 8, ["aria-label"])]),
					default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
						default: withCtx(() => [$props.canConfigure ? (openBlock(), createBlock(_component_DropdownItem, {
							key: 0,
							text: _ctx.__("Configure"),
							icon: "cog",
							href: $props.configureUrl
						}, null, 8, ["text", "href"])) : createCommentVNode("", true), $props.canEditBlueprint ? (openBlock(), createBlock(_component_DropdownItem, {
							key: 1,
							text: _ctx.__("Edit Blueprint"),
							icon: "blueprint-edit",
							href: $data.actions.editBlueprint
						}, null, 8, ["text", "href"])) : createCommentVNode("", true)]),
						_: 1
					})]),
					_: 1
				})) : createCommentVNode("", true),
				!$props.canEdit ? (openBlock(), createBlock(_component_ui_badge, {
					key: 2,
					icon: "padlock-locked",
					text: _ctx.__("Read Only")
				}, null, 8, ["text"])) : createCommentVNode("", true),
				$options.showLocalizationSelector ? (openBlock(), createBlock(_component_SiteSelector, {
					key: 3,
					sites: $data.localizations,
					"model-value": $data.site,
					"onUpdate:modelValue": $options.localizationSelected
				}, null, 8, [
					"sites",
					"model-value",
					"onUpdate:modelValue"
				])) : createCommentVNode("", true),
				createBaseVNode("div", _hoisted_1$1, [$props.canEdit ? (openBlock(), createBlock(_component_Button, {
					key: 0,
					variant: "primary",
					text: _ctx.__("Save"),
					disabled: !$options.canSave,
					onClick: withModifiers($options.save, ["prevent"])
				}, null, 8, [
					"text",
					"disabled",
					"onClick"
				])) : createCommentVNode("", true)]),
				renderSlot(_ctx.$slots, "action-buttons-right")
			]),
			_: 3
		}, 8, ["title"]),
		$data.fieldset.empty ? (openBlock(), createElementBlock("div", _hoisted_2, [createVNode(_component_ui_heading, {
			class: "mx-auto max-w-md",
			text: _ctx.__("messages.global_set_no_fields_description")
		}, null, 8, ["text"])])) : createCommentVNode("", true),
		$data.fieldset && !$data.fieldset.empty ? (openBlock(), createBlock(_component_PublishContainer, {
			key: 1,
			ref: "container",
			name: $props.publishContainer,
			reference: $data.reference,
			blueprint: $data.fieldset,
			modelValue: $data.values,
			"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.values = $event),
			meta: $data.meta,
			"origin-values": $data.originValues,
			"origin-meta": $data.originMeta,
			errors: $options.errors,
			site: $data.site,
			"read-only": $data.readOnly,
			"modified-fields": $data.localizedFields,
			"onUpdate:modifiedFields": _cache[1] || (_cache[1] = ($event) => $data.localizedFields = $event),
			"sync-field-confirmation-text": $data.syncFieldConfirmationText,
			"remember-tab": ""
		}, null, 8, [
			"name",
			"reference",
			"blueprint",
			"modelValue",
			"meta",
			"origin-values",
			"origin-meta",
			"errors",
			"site",
			"read-only",
			"modified-fields",
			"sync-field-confirmation-text"
		])) : createCommentVNode("", true),
		createVNode(_component_confirmation_modal, {
			open: $data.pendingLocalization,
			title: _ctx.__("Unsaved Changes"),
			"body-text": _ctx.__("Are you sure? Unsaved changes will be lost."),
			"button-text": _ctx.__("Continue"),
			danger: true,
			onConfirm: $options.confirmSwitchLocalization,
			onCancel: _cache[2] || (_cache[2] = ($event) => $data.pendingLocalization = null)
		}, null, 8, [
			"open",
			"title",
			"body-text",
			"button-text",
			"onConfirm"
		])
	]);
}
var PublishForm_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "PublishForm.vue"]]);
//#endregion
//#region resources/js/pages/globals/Edit.vue
var _sfc_main = {
	__name: "Edit",
	props: [
		"actions",
		"globalsUrl",
		"title",
		"handle",
		"reference",
		"blueprintHandle",
		"blueprint",
		"values",
		"localizedFields",
		"meta",
		"localizations",
		"hasOrigin",
		"originValues",
		"originMeta",
		"locale",
		"canConfigure",
		"configureUrl",
		"canEdit",
		"canEditBlueprint",
		"itemActions",
		"actionUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			GlobalPublishForm: PublishForm_default,
			Head: Head_default
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
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["Head"], { title: _ctx.__("Edit Global Set") }, null, 8, ["title"]), createVNode($setup["GlobalPublishForm"], {
		"publish-container": "base",
		"initial-actions": $props.actions,
		method: "patch",
		"globals-url": $props.globalsUrl,
		"initial-title": $props.title,
		"initial-handle": $props.handle,
		"initial-reference": $props.reference,
		"initial-blueprint-handle": $props.blueprintHandle,
		"initial-fieldset": $props.blueprint,
		"initial-values": $props.values,
		"initial-localized-fields": $props.localizedFields,
		"initial-meta": $props.meta,
		"initial-localizations": $props.localizations,
		"initial-has-origin": $props.hasOrigin,
		"initial-origin-values": $props.originValues,
		"initial-origin-meta": $props.originMeta,
		"initial-site": $props.locale,
		"can-configure": $props.canConfigure,
		"configure-url": $props.configureUrl,
		"can-edit": $props.canEdit,
		"can-edit-blueprint": $props.canEditBlueprint,
		"initial-item-actions": $props.itemActions,
		"item-action-url": $props.actionUrl
	}, null, 8, [
		"initial-actions",
		"globals-url",
		"initial-title",
		"initial-handle",
		"initial-reference",
		"initial-blueprint-handle",
		"initial-fieldset",
		"initial-values",
		"initial-localized-fields",
		"initial-meta",
		"initial-localizations",
		"initial-has-origin",
		"initial-origin-values",
		"initial-origin-meta",
		"initial-site",
		"can-configure",
		"configure-url",
		"can-edit",
		"can-edit-blueprint",
		"initial-item-actions",
		"item-action-url"
	])]);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
