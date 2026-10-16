import { n as __exportAll } from "./rolldown-runtime-B-1-B7_t.js";
import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, at as withDirectives, c as vShow, et as watch, f as Fragment, g as createBaseVNode, it as withCtx, q as resolveDirective, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-B-BfxmJA.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $t as Item_default, Jt as Separator_default, Qr as Button_default, U as Fields_default, Zn as Subheading_default, Zt as Menu_default, en as Dropdown_default, gi as Icon_default, mt as Switch_default, q as FieldsProvider_default, ri as Badge_default, vn as useUiDirection, yn as containerContextKey } from "./ui-DHa4llNP.js";
import { c as nodeViewProps, i as NodeViewWrapper } from "./dist-DgUdqBD8.js";
import { S as reveal } from "./api-BRRvKt3_.js";
import { f as ManagesPreviewText_default, y as HasFieldActions_default } from "./index-BAJ_kLUJ.js";
//#region resources/js/components/fieldtypes/bard/Set.vue
var Set_exports = /* @__PURE__ */ __exportAll({ default: () => Set_default });
var _sfc_main = {
	props: nodeViewProps,
	setup() {
		return { uiDirection: useUiDirection().direction };
	},
	data() {
		return { dropdownJustClosed: false };
	},
	components: {
		Button: Button_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default,
		DropdownSeparator: Separator_default,
		Fields: Fields_default,
		FieldsProvider: FieldsProvider_default,
		Switch: Switch_default,
		Subheading: Subheading_default,
		Badge: Badge_default,
		Icon: Icon_default,
		NodeViewWrapper
	},
	mixins: [ManagesPreviewText_default, HasFieldActions_default],
	inject: {
		bard: {},
		bardSets: {},
		publishContainer: { from: containerContextKey }
	},
	computed: {
		fields() {
			return this.config.fields;
		},
		hasFields() {
			return Array.isArray(this.fields) ? this.fields.length > 0 : Object.keys(this.fields || {}).length > 0;
		},
		display() {
			return __(this.config.display || this.values.type);
		},
		values() {
			return this.node.attrs.values;
		},
		extraValues() {
			return {};
		},
		meta() {
			return this.extension.options.bard.meta.existing[this.node.attrs.id] || {};
		},
		previews() {
			return data_get(this.publishContainer.previews.value, this.fieldPathPrefix) || {};
		},
		collapsed() {
			return this.extension.options.bard.collapsed.includes(this.node.attrs.id);
		},
		config() {
			return this.setConfigs.find((c) => c.handle === this.values.type) || {};
		},
		setConfigs() {
			return this.bard.setConfigs;
		},
		setGroup() {
			if (this.bardSets.length < 1) return null;
			return this.bardSets.find((group) => {
				return group.sets.filter((set) => set.handle === this.config.handle).length > 0;
			});
		},
		isSetGroupVisible() {
			return this.bardSets.length > 1 && this.setGroup?.display;
		},
		isReadOnly() {
			return this.bard.isReadOnly;
		},
		enabled: {
			get() {
				return this.node.attrs.enabled;
			},
			set(enabled) {
				return this.updateAttributes({ enabled });
			}
		},
		parentName() {
			return this.extension.options.bard.name;
		},
		index() {
			return this.extension.options.bard.setIndexes[this.node.attrs.id];
		},
		fieldPathPrefix() {
			const fpf = this.extension.options.bard.fieldPathPrefix;
			const handle = this.extension.options.bard.handle;
			return `${fpf ? `${fpf}.${handle}` : handle}.${this.index}.attrs.values`;
		},
		metaPathPrefix() {
			const mpp = this.extension.options.bard.metaPathPrefix;
			const handle = this.extension.options.bard.handle;
			return `${mpp ? `${mpp}.${handle}` : handle}.existing.${this.node.attrs.id}`;
		},
		instructions() {
			return this.config.instructions ? markdown(__(this.config.instructions)) : null;
		},
		hasError() {
			return this.extension.options.bard.setHasError(this.node.attrs.id);
		},
		showFieldPreviews() {
			return this.extension.options.bard.config.previews;
		},
		isInvalid() {
			return Object.keys(this.config).length === 0;
		},
		decorationSpecs() {
			return Object.assign({}, ...this.decorations.map((decoration) => decoration.type.spec));
		},
		withinSelection() {
			return this.decorationSpecs.withinSelection;
		},
		showSelectionHighlight() {
			return (this.selected || this.withinSelection) && this.bard.hasBeenFocused;
		},
		fieldVm() {
			return this.extension.options.bard;
		},
		fieldActionPayload() {
			return {
				index: this.index,
				values: this.values,
				config: this.config,
				update: (handle, value) => this.publishContainer.setFieldValue(`${this.fieldPathPrefix}.${handle}`, value),
				updateMeta: (handle, value) => this.publishContainer.setFieldMeta(`${this.metaPathPrefix}.${handle}`, value),
				isReadOnly: this.isReadOnly
			};
		},
		fieldActionBinding() {
			return "bard-fieldtype-set";
		}
	},
	methods: {
		focused() {
			this.extension.options.bard.$emit("focus");
		},
		blurred() {
			setTimeout(() => {
				const bard = this.extension.options.bard;
				if (!bard.$el.contains(document.activeElement)) bard.$emit("blur");
			}, 1);
		},
		toggleCollapsedState() {
			if (this.collapsed) this.expand();
			else this.collapse();
		},
		collapse() {
			this.extension.options.bard.collapseSet(this.node.attrs.id);
		},
		expand() {
			this.extension.options.bard.expandSet(this.node.attrs.id);
		},
		duplicate() {
			this.extension.options.bard.duplicateSet(this.node.attrs.id, this.node.attrs, this.getPos);
		},
		enableDragging() {
			this._draggableObserver?.disconnect();
			this.$el.setAttribute("draggable", true);
			document.addEventListener("mouseup", this.disableDragging, { once: true });
			document.addEventListener("dragend", this.disableDragging, { once: true });
		},
		disableDragging() {
			this.$el.setAttribute("draggable", false);
			this._draggableObserver?.observe(this.$el, {
				attributes: true,
				attributeFilter: ["draggable"]
			});
		},
		onSetDropdownClosed() {
			this.dropdownJustClosed = true;
			if (this._dropdownJustClosedTimeout) clearTimeout(this._dropdownJustClosedTimeout);
			this._dropdownJustClosedTimeout = setTimeout(() => {
				this.dropdownJustClosed = false;
				this._dropdownJustClosedTimeout = null;
			}, 150);
		},
		onMousedown(event) {
			this.focusClickedButton(event);
			this.preventFormControlNodeSelection(event);
		},
		clickedButton(event) {
			return (event.target instanceof Element ? event.target : event.target.parentElement)?.closest("button:not(:disabled)");
		},
		focusClickedButton(event) {
			if (event.defaultPrevented) return;
			const button = this.clickedButton(event);
			if (!button) return;
			button.focus({ preventScroll: true });
			if (document.activeElement === button) event.preventDefault();
		},
		keepButtonGroupFocused(event) {
			const button = this.clickedButton(event);
			if (!button?.closest("[data-ui-button-group]")) return;
			const refocus = () => {
				const active = document.activeElement;
				if (active === button) return;
				if (active && active !== document.body && !active.contains(button)) return;
				button.focus({ preventScroll: true });
			};
			requestAnimationFrame(refocus);
			setTimeout(refocus, 0);
		},
		preventFormControlNodeSelection(event) {
			if ((event.target instanceof Element ? event.target : event.target.parentElement)?.closest("[data-ui-combobox], [data-ui-input], [data-interactive]")) event.stopPropagation();
		},
		preventNodeSelectionDrag(event) {
			if ((event.target instanceof Element ? event.target : event.target.parentElement)?.closest("[draggable=\"true\"]")) return;
			const selection = window.getSelection();
			if (selection?.rangeCount && selection.containsNode(this.$el, false)) event.preventDefault();
		}
	},
	mounted() {
		watch(() => data_get(this.publishContainer.values.value, this.fieldPathPrefix), (values) => {
			if (!values) return;
			if (JSON.stringify(values) === JSON.stringify(this.node.attrs.values)) return;
			this.updateAttributes({ values });
		}, { deep: true });
		reveal.mount(this.$refs.container, this.expand);
		this.$el.setAttribute("draggable", false);
		this._draggableObserver = new MutationObserver(() => {
			if (this.$el.getAttribute("draggable") !== "false") this.$el.setAttribute("draggable", false);
		});
		this._draggableObserver.observe(this.$el, {
			attributes: true,
			attributeFilter: ["draggable"]
		});
	},
	updated() {
		this.$el.setAttribute("draggable", false);
	},
	beforeUnmount() {
		if (this._dropdownJustClosedTimeout) clearTimeout(this._dropdownJustClosedTimeout);
		this._draggableObserver?.disconnect();
	}
};
var _hoisted_1 = ["dir", "data-type"];
var _hoisted_2 = {
	ref: "content",
	hidden: ""
};
var _hoisted_3 = ["data-readonly"];
var _hoisted_4 = {
	key: 0,
	class: "flex items-center gap-2"
};
var _hoisted_5 = {
	key: 1,
	class: "flex items-center gap-2"
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Icon = resolveComponent("Icon");
	const _component_Badge = resolveComponent("Badge");
	const _component_Subheading = resolveComponent("Subheading");
	const _component_Switch = resolveComponent("Switch");
	const _component_Button = resolveComponent("Button");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_Fields = resolveComponent("Fields");
	const _component_FieldsProvider = resolveComponent("FieldsProvider");
	const _component_node_view_wrapper = resolveComponent("node-view-wrapper");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock(_component_node_view_wrapper, { class: "my-4" }, {
		default: withCtx(() => [createBaseVNode("div", {
			ref: "container",
			class: normalizeClass(["shadow-ui-sm relative w-full rounded-lg border border-gray-300 bg-white text-base dark:border-white/10 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black", {
				"st-set-is-selected": $options.showSelectionHighlight,
				"border-red-500": $options.hasError,
				"st-dropdown-just-closed": $data.dropdownJustClosed
			}]),
			dir: $setup.uiDirection,
			"data-type": $options.config.handle,
			contenteditable: "false",
			onCopy: _cache[4] || (_cache[4] = withModifiers(() => {}, ["stop"])),
			onPaste: _cache[5] || (_cache[5] = withModifiers(() => {}, ["stop"])),
			onCut: _cache[6] || (_cache[6] = withModifiers(() => {}, ["stop"])),
			onDragstart: _cache[7] || (_cache[7] = (...args) => $options.preventNodeSelectionDrag && $options.preventNodeSelectionDrag(...args)),
			onMousedown: _cache[8] || (_cache[8] = (...args) => $options.onMousedown && $options.onMousedown(...args)),
			onClick: _cache[9] || (_cache[9] = (...args) => $options.keepButtonGroupFocused && $options.keepButtonGroupFocused(...args))
		}, [
			createBaseVNode("div", _hoisted_2, null, 512),
			createBaseVNode("header", {
				class: normalizeClass(["group/header animate-border-color show-focus-within flex items-center rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 bg-gray-100/50 dark:bg-gray-925 data-readonly:dark:!bg-transparent hover:bg-gray-100 dark:hover:bg-gray-950/45 border-gray-300 dark:shadow-md", { "bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none": !$options.collapsed && $options.hasFields }]),
				"data-readonly": $options.isReadOnly ? true : void 0
			}, [
				!$options.isReadOnly ? (openBlock(), createElementBlock("span", {
					key: 0,
					"data-drag-handle": "",
					class: "flex cursor-grab",
					onMousedown: _cache[0] || (_cache[0] = (...args) => $options.enableDragging && $options.enableDragging(...args))
				}, [createVNode(_component_Icon, {
					name: "handles",
					class: "size-4 text-gray-400"
				})], 32)) : createCommentVNode("", true),
				createBaseVNode("button", {
					type: "button",
					class: "show-focus-within_target flex flex-1 min-w-0 cursor-pointer items-center gap-4 overflow-x-auto p-2 pe-4 focus:outline-none st-mask-horizontal-overflow",
					onClick: _cache[1] || (_cache[1] = (...args) => $options.toggleCollapsedState && $options.toggleCollapsedState(...args))
				}, [
					createVNode(_component_Badge, {
						size: "lg",
						pill: true,
						color: "white",
						class: "px-3"
					}, {
						default: withCtx(() => [$options.isSetGroupVisible ? (openBlock(), createElementBlock("span", _hoisted_4, [createTextVNode(toDisplayString(_ctx.__($options.setGroup.display)) + " ", 1), createVNode(_component_Icon, {
							name: "chevron-right",
							class: "relative top-px size-3"
						})])) : createCommentVNode("", true), createTextVNode(" " + toDisplayString(_ctx.__($options.config.display) || $options.config.handle), 1)]),
						_: 1
					}),
					$options.config.instructions && !$options.collapsed ? withDirectives((openBlock(), createBlock(_component_Icon, {
						key: 0,
						name: "info-square",
						class: "size-3.5! text-gray-500"
					}, null, 512)), [[_directive_tooltip, {
						content: _ctx.$markdown(_ctx.__($options.config.instructions)),
						html: true
					}]]) : createCommentVNode("", true),
					withDirectives(createVNode(_component_Subheading, {
						innerHTML: _ctx.previewText,
						class: "overflow-hidden text-ellipsis whitespace-nowrap"
					}, null, 8, ["innerHTML"]), [[vShow, $options.collapsed]])
				]),
				!$options.isReadOnly ? (openBlock(), createElementBlock("div", _hoisted_5, [withDirectives(createVNode(_component_Switch, {
					size: "xs",
					modelValue: $options.enabled,
					"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $options.enabled = $event),
					label: _ctx.__("Included in output")
				}, null, 8, ["modelValue", "label"]), [[_directive_tooltip, $options.enabled ? _ctx.__("Included in output") : _ctx.__("Hidden from output")]]), createVNode(_component_Dropdown, { onClosed: $options.onSetDropdownClosed }, {
					trigger: withCtx(() => [createVNode(_component_Button, {
						icon: "dots",
						variant: "ghost",
						size: "xs",
						"aria-label": _ctx.__("Open dropdown menu"),
						onMousedown: _cache[3] || (_cache[3] = withModifiers(() => {}, ["prevent"]))
					}, null, 8, ["aria-label"])]),
					default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
						default: withCtx(() => [
							_ctx.fieldActions.length ? (openBlock(true), createElementBlock(Fragment, { key: 0 }, renderList(_ctx.fieldActions, (action) => {
								return openBlock(), createBlock(_component_DropdownItem, {
									text: action.title,
									variant: action.dangerous ? "destructive" : "default",
									onClick: ($event) => action.run(action)
								}, null, 8, [
									"text",
									"variant",
									"onClick"
								]);
							}), 256)) : createCommentVNode("", true),
							_ctx.fieldActions.length ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 1 })) : createCommentVNode("", true),
							createVNode(_component_DropdownItem, {
								text: _ctx.__($options.collapsed ? _ctx.__("Expand Set") : _ctx.__("Collapse Set")),
								onClick: $options.toggleCollapsedState
							}, null, 8, ["text", "onClick"]),
							createVNode(_component_DropdownItem, {
								text: _ctx.__("Duplicate Set"),
								onClick: $options.duplicate
							}, null, 8, ["text", "onClick"]),
							createVNode(_component_DropdownItem, {
								text: _ctx.__("Delete Set"),
								variant: "destructive",
								onClick: _ctx.deleteNode
							}, null, 8, ["text", "onClick"])
						]),
						_: 1
					})]),
					_: 1
				}, 8, ["onClosed"])])) : createCommentVNode("", true)
			], 10, _hoisted_3),
			$options.index !== void 0 && $options.hasFields ? withDirectives((openBlock(), createElementBlock("div", {
				key: 0,
				class: normalizeClass([{
					"contain-paint": $options.collapsed,
					"isolate": !$options.collapsed
				}, "border-t border-t-gray-300! dark:border-t-white/10!"])
			}, [createVNode(_component_FieldsProvider, {
				fields: $options.fields,
				"as-config": false,
				"read-only": $options.isReadOnly,
				"field-path-prefix": $options.fieldPathPrefix,
				"meta-path-prefix": $options.metaPathPrefix
			}, {
				default: withCtx(() => [createVNode(_component_Fields, { class: "p-4" })]),
				_: 1
			}, 8, [
				"fields",
				"read-only",
				"field-path-prefix",
				"meta-path-prefix"
			])], 2)), [[vShow, !$options.collapsed]]) : createCommentVNode("", true)
		], 42, _hoisted_1)]),
		_: 1
	});
}
var Set_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Set.vue"]]);
//#endregion
export { Set_exports as n, Set_default as t };
