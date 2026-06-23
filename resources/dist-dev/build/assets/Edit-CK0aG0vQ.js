import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, G as renderSlot, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, at as withDirectives, f as Fragment, g as createBaseVNode, it as withCtx, q as resolveDirective, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, Ci as Icon_default, Gn as Panel_default, Jn as Heading_default, Mt as Stack_default, Ut as Header_default, Vt as Input_default, Wt as Field_default, Xt as Separator_default, ci as Group_default, en as Label_default, li as Button_default, nn as Dropdown_default, qn as Header_default$1, r as Item_default$1, tn as Item_default } from "./ui-BfR7XN6t.js";
import { i as data_get } from "./globals-CR4DMcIG.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
import { n as st, r as walkTreeData, t as je } from "./v3-R_90cesw.js";
//#region resources/js/components/nav/Branch.vue
var _sfc_main$4 = {
	components: {
		Icon: Icon_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		Button: Button_default
	},
	props: {
		item: Object,
		parentSection: Object,
		depth: Number,
		root: Boolean,
		stat: Object,
		isOpen: Boolean,
		isChild: Boolean,
		hasChildren: Boolean,
		disableSections: Boolean
	},
	data() {
		return { editing: false };
	},
	computed: {
		isSection() {
			if (this.disableSections) return false;
			return this.depth === 1;
		},
		title() {
			return this.item.title || this.item.entry_title || this.item.url;
		},
		icon() {
			return data_get(this.item, "config.icon") || data_get(this.item, "original.icon") || "entries";
		},
		isAlreadySvg() {
			return this.icon.startsWith("<svg");
		},
		isRenamedSection() {
			return this.isSection && this.item.text !== data_get(this.item, "config.display_original");
		},
		isHidden() {
			return data_get(this.item, "manipulations.action") === "@hide";
		},
		isInHiddenSection() {
			return this.parentSection && this.parentSection.data.manipulations.action === "@hide";
		},
		isPinnedAlias() {
			return data_get(this.item, "manipulations.action") === "@alias" && this.inTopLevelSection;
		},
		isAlias() {
			return data_get(this.item, "manipulations.action") === "@alias";
		},
		isMoved() {
			return data_get(this.item, "manipulations.action") === "@move";
		},
		isModified() {
			return data_get(this.item, "manipulations.action") === "@modify";
		},
		isCustom() {
			return data_get(this.item, "manipulations.action") === "@create";
		},
		inTopLevelSection() {
			return this.parentSection?.data?.text === "Top Level";
		}
	},
	methods: { remove() {
		const store = this.item._vm.store;
		store.deleteNode(this.item);
		this.$emit("removed", store);
	} }
};
var _hoisted_1$4 = { class: "flex flex-1 items-center p-1.5 text-xs leading-normal" };
var _hoisted_2$4 = ["textContent"];
var _hoisted_3$3 = { class: "flex items-center gap-2 sm:gap-3" };
function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Icon = resolveComponent("Icon");
	const _component_Button = resolveComponent("Button");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", { class: normalizeClass(["page-tree-branch flex", {
		"ml-[-24px]": $options.inTopLevelSection,
		"page-tree-branch--has-children": $props.hasChildren
	}]) }, [_cache[2] || (_cache[2] = createBaseVNode("div", { class: "page-move w-6" }, null, -1)), createBaseVNode("div", _hoisted_1$4, [createBaseVNode("div", { class: normalizeClass(["flex gap-2 sm:gap-3 grow items-center", { "opacity-50": $options.isHidden || $options.isInHiddenSection }]) }, [
		!$options.isSection && !$props.isChild ? (openBlock(), createBlock(_component_Icon, {
			key: 0,
			class: "size-4",
			name: $options.icon
		}, null, 8, ["name"])) : createCommentVNode("", true),
		createBaseVNode("a", {
			onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("edit", $event)),
			class: normalizeClass({ "text-sm font-medium is-section": $options.isSection }),
			textContent: toDisplayString(_ctx.__($props.item.text))
		}, null, 10, _hoisted_2$4),
		$props.hasChildren && !$options.isSection ? (openBlock(), createBlock(_component_Button, {
			key: 1,
			class: normalizeClass(["transition duration-100 [&_svg]:size-4! -mx-1.5", {
				"-rotate-90 is-closed": !$props.isOpen,
				"is-open": $props.isOpen
			}]),
			icon: "chevron-down",
			size: "xs",
			round: "",
			variant: "ghost",
			onClick: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("toggle-open"))
		}, null, 8, ["class"])) : createCommentVNode("", true)
	], 2), createBaseVNode("div", _hoisted_3$3, [
		renderSlot(_ctx.$slots, "branch-icon", { branch: $props.item }),
		$options.isRenamedSection ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 0,
			class: "size-4 text-gray-400 dark:text-gray-600",
			name: "fieldsets"
		}, null, 512)), [[_directive_tooltip, _ctx.__("Renamed Section")]]) : $options.isHidden ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 1,
			class: "size-4 text-gray-400 dark:text-gray-600",
			name: "eye-closed"
		}, null, 512)), [[_directive_tooltip, $options.isSection ? _ctx.__("Hidden Section") : _ctx.__("Hidden Item")]]) : $options.isPinnedAlias ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 2,
			class: "size-4 text-gray-400 dark:text-gray-600",
			name: "pin"
		}, null, 512)), [[_directive_tooltip, _ctx.__("Pinned Item")]]) : $options.isAlias ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 3,
			class: "size-4 text-gray-400 dark:text-gray-600",
			name: "duplicate"
		}, null, 512)), [[_directive_tooltip, _ctx.__("Aliased Item")]]) : $options.isMoved ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 4,
			class: "size-4 text-gray-400 dark:text-gray-600",
			name: "moved"
		}, null, 512)), [[_directive_tooltip, _ctx.__("Moved Item")]]) : $options.isModified ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 5,
			class: "size-4 text-gray-400 dark:text-gray-600",
			name: "fieldsets"
		}, null, 512)), [[_directive_tooltip, _ctx.__("Modified Item")]]) : $options.isCustom ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 6,
			class: "size-4 text-gray-400 dark:text-gray-600",
			name: "user-edit"
		}, null, 512)), [[_directive_tooltip, $options.isSection ? _ctx.__("Custom Section") : _ctx.__("Custom Item")]]) : createCommentVNode("", true),
		createVNode(_component_Dropdown, { placement: "left-start" }, {
			default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
				default: withCtx(() => [renderSlot(_ctx.$slots, "branch-options", {
					item: $props.item,
					depth: $props.depth,
					removeBranch: $options.remove,
					inTopLevelSection: $options.inTopLevelSection
				})]),
				_: 3
			})]),
			_: 3
		})
	])])], 2);
}
var Branch_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$4, [["render", _sfc_render$4], ["__file", "Branch.vue"]]);
//#endregion
//#region resources/js/components/nav/TopLevelSectionBranch.vue
var _sfc_main$3 = {
	props: { stat: Object },
	computed: { hasChildren() {
		return this.stat.children.filter((node) => node.hidden === false).length;
	} }
};
var _hoisted_1$3 = { class: "flex first-branch" };
var _hoisted_2$3 = {
	key: 0,
	class: "is-top-level-section-placeholder w-full rounded-lg border border-dashed border-blue-400 bg-blue-500/10 p-2"
};
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1$3, [!$options.hasChildren ? (openBlock(), createElementBlock("div", _hoisted_2$3, " \xA0 ")) : createCommentVNode("", true)]);
}
var TopLevelSectionBranch_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$3, [["render", _sfc_render$3], ["__file", "TopLevelSectionBranch.vue"]]);
//#endregion
//#region resources/js/components/nav/ItemEditor.vue
var _sfc_main$2 = {
	components: {
		Button: Button_default,
		Heading: Heading_default,
		Field: Field_default,
		Input: Input_default,
		Stack: Stack_default
	},
	emits: ["closed", "updated"],
	props: {
		creating: false,
		item: null,
		isChild: false
	},
	data() {
		return {
			config: { ...this.item?.data?.config || this.createNewItem() },
			saveKeyBinding: null,
			validateDisplay: false,
			validateUrl: false
		};
	},
	created() {
		this.saveKeyBinding = this.$keys.bindGlobal([
			"enter",
			"mod+enter",
			"mod+s"
		], (e) => {
			e.preventDefault();
			this.save();
		});
	},
	beforeUnmount() {
		this.saveKeyBinding.destroy();
	},
	methods: {
		createNewItem() {
			return {
				display: "",
				url: "",
				icon: null
			};
		},
		save() {
			this.validateDisplay = false;
			this.validateUrl = false;
			if (!this.config.display) this.validateDisplay = true;
			if (!this.config.url) this.validateUrl = true;
			if (this.validateDisplay || this.validateUrl) return;
			let config = clone(this.config);
			if (this.isChild) config.icon = null;
			else if (!config.icon) config.icon = data_get(this.item, "original.icon");
			this.$emit("updated", this.config, this.item);
		}
	}
};
var _hoisted_1$2 = { class: "" };
var _hoisted_2$2 = { class: "" };
var _hoisted_3$2 = { class: "flex flex-col space-y-6" };
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Input = resolveComponent("Input");
	const _component_Field = resolveComponent("Field");
	const _component_icon_fieldtype = resolveComponent("icon-fieldtype");
	const _component_publish_field_meta = resolveComponent("publish-field-meta");
	const _component_Button = resolveComponent("Button");
	const _component_Stack = resolveComponent("Stack");
	return openBlock(), createBlock(_component_Stack, {
		size: "narrow",
		title: $props.creating ? _ctx.__("Add Nav Item") : _ctx.__("Edit Nav Item"),
		open: "",
		"onUpdate:open": _cache[3] || (_cache[3] = ($event) => _ctx.$emit("closed"))
	}, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$2, [createBaseVNode("div", _hoisted_2$2, [createBaseVNode("div", _hoisted_3$2, [
			createVNode(_component_Field, {
				id: "display",
				label: _ctx.__("Display"),
				required: ""
			}, {
				default: withCtx(() => [createVNode(_component_Input, {
					id: "display",
					modelValue: $data.config.display,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.config.display = $event),
					focus: true,
					error: $data.validateDisplay ? _ctx.__("statamic::validation.required") : null
				}, null, 8, ["modelValue", "error"])]),
				_: 1
			}, 8, ["label"]),
			createVNode(_component_Field, {
				id: "url",
				label: _ctx.__("URL"),
				required: ""
			}, {
				default: withCtx(() => [createVNode(_component_Input, {
					id: "url",
					modelValue: $data.config.url,
					"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.config.url = $event),
					error: $data.validateUrl ? _ctx.__("statamic::validation.required") : null
				}, null, 8, ["modelValue", "error"])]),
				_: 1
			}, 8, ["label"]),
			!$props.isChild ? (openBlock(), createBlock(_component_Field, {
				key: 0,
				id: "icon",
				label: _ctx.__("Icon")
			}, {
				default: withCtx(() => [createVNode(_component_publish_field_meta, {
					config: {
						handle: "icon",
						type: "icon"
					},
					"initial-value": $data.config.icon
				}, {
					default: withCtx(({ meta, value, loading, config: fieldtypeConfig }) => [!loading ? (openBlock(), createBlock(_component_icon_fieldtype, {
						key: 0,
						handle: "icon",
						config: fieldtypeConfig,
						meta,
						value,
						"onUpdate:value": _cache[2] || (_cache[2] = ($event) => $data.config.icon = $event)
					}, null, 8, [
						"config",
						"meta",
						"value"
					])) : createCommentVNode("", true)]),
					_: 1
				}, 8, ["initial-value"])]),
				_: 1
			}, 8, ["label"])) : createCommentVNode("", true),
			createVNode(_component_Button, {
				variant: "primary",
				text: _ctx.__("Save"),
				onClick: $options.save
			}, null, 8, ["text", "onClick"])
		])])])]),
		_: 1
	}, 8, ["title"]);
}
var ItemEditor_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "ItemEditor.vue"]]);
//#endregion
//#region resources/js/components/nav/SectionEditor.vue
var _sfc_main$1 = {
	components: {
		Button: Button_default,
		Heading: Heading_default,
		Field: Field_default,
		Input: Input_default,
		Stack: Stack_default
	},
	emits: ["closed", "updated"],
	props: {
		creating: false,
		sectionItem: {}
	},
	data() {
		return {
			section: this.sectionItem?.data?.text || "",
			saveKeyBinding: null,
			validate: false
		};
	},
	created() {
		this.saveKeyBinding = this.$keys.bindGlobal([
			"enter",
			"mod+enter",
			"mod+s"
		], (e) => {
			e.preventDefault();
			this.save();
		});
	},
	beforeUnmount() {
		this.saveKeyBinding.destroy();
	},
	methods: { save() {
		this.validate = false;
		if (!this.section) {
			this.validate = true;
			return;
		}
		this.$emit("updated", this.section, this.sectionItem);
	} }
};
var _hoisted_1$1 = { class: "" };
var _hoisted_2$1 = { class: "" };
var _hoisted_3$1 = { class: "flex flex-col space-y-6" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Input = resolveComponent("Input");
	const _component_Field = resolveComponent("Field");
	const _component_Button = resolveComponent("Button");
	const _component_Stack = resolveComponent("Stack");
	return openBlock(), createBlock(_component_Stack, {
		size: "narrow",
		title: $props.creating ? _ctx.__("Add Section") : _ctx.__("Edit Section"),
		open: "",
		"onUpdate:open": _cache[1] || (_cache[1] = ($event) => _ctx.$emit("closed"))
	}, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$1, [createBaseVNode("div", _hoisted_2$1, [createBaseVNode("div", _hoisted_3$1, [createVNode(_component_Field, {
			id: "display",
			label: _ctx.__("Display"),
			required: ""
		}, {
			default: withCtx(() => [createVNode(_component_Input, {
				id: "display",
				modelValue: $data.section,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.section = $event),
				focus: true,
				error: $data.validate ? _ctx.__("statamic::validation.required") : null
			}, null, 8, ["modelValue", "error"])]),
			_: 1
		}, 8, ["label"]), createVNode(_component_Button, {
			variant: "primary",
			text: _ctx.__("Save"),
			onClick: $options.save
		}, null, 8, ["text", "onClick"])])])])]),
		_: 1
	}, 8, ["title"]);
}
//#endregion
//#region resources/js/pages/preferences/nav/Edit.vue
var _sfc_main = {
	components: {
		Head: Head_default,
		Header: Header_default,
		DropdownLabel: Label_default,
		Button: Button_default,
		ButtonGroup: Group_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default,
		DropdownSeparator: Separator_default,
		Draggable: je,
		TreeBranch: Branch_default,
		ItemEditor: ItemEditor_default,
		SectionEditor: /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "SectionEditor.vue"]]),
		TopLevelSectionBranch: TopLevelSectionBranch_default,
		Panel: Panel_default,
		PanelHeader: Header_default$1,
		Icon: Icon_default,
		CommandPaletteItem: Item_default$1
	},
	props: {
		title: {
			type: String,
			required: true
		},
		nav: {
			type: Array,
			required: true
		},
		indexUrl: { type: String },
		updateUrl: {
			type: String,
			required: true
		},
		destroyUrl: {
			type: String,
			required: true
		},
		saveAsOptions: {
			type: Array,
			default: () => []
		}
	},
	data() {
		return {
			initialNav: clone(this.nav),
			loading: false,
			treeData: [],
			originalSectionItems: {},
			changed: false,
			targetStat: null,
			creatingItem: false,
			creatingItemIsChild: false,
			editingItem: false,
			creatingSection: false,
			editingSection: false,
			confirmingReset: false,
			confirmingRemoval: false,
			draggingStat: false,
			isDraggingIntoTopLevelSection: false
		};
	},
	created() {
		this.$keys.bindGlobal(["mod+s"], (e) => {
			e.preventDefault();
			this.save();
		});
	},
	mounted() {
		this.setInitialNav(this.nav);
		this.addToCommandPalette();
	},
	computed: {
		treeDraggableI18n() {
			return { instructions: __("messages.tree_aria_instructions") };
		},
		isDirty() {
			return this.changed;
		},
		hasSaveAsOptions() {
			return this.saveAsOptions.length;
		},
		direction() {
			return this.$config.get("direction", "ltr");
		}
	},
	methods: {
		setInitialNav(nav) {
			let navConfig = clone(nav);
			this.setOriginalSectionItems(navConfig);
			this.treeData = Object.values(navConfig.map((section) => this.normalizeNavConfig(section)));
		},
		setOriginalSectionItems(nav) {
			nav.forEach((section) => this.originalSectionItems[section.display_original] = section.items_original || []);
		},
		discardChanges() {
			this.setInitialNav(this.initialNav);
			this.changed = false;
		},
		normalizeNavConfig(config, isSectionNode = true) {
			let item = {
				text: config.display,
				config,
				original: config.original,
				manipulations: isSectionNode ? config : config.manipulations || {},
				isSection: isSectionNode,
				open: isSectionNode
			};
			let children = config.items || config.children || [];
			if (children) item.children = children.map((childItem) => {
				return {
					text: childItem.display,
					children: childItem.children.map((childChildItem) => this.normalizeNavConfig(childChildItem, false)),
					open: false,
					config: childItem,
					original: childItem.original,
					manipulations: childItem.manipulations || {},
					isSection: false
				};
			});
			return item;
		},
		eachDroppable(targetStat) {
			if (this.isSectionNode(st.dragNode)) return false;
			this.isDraggingIntoTopLevelSection = this.checkIfDraggingIntoTopLevelSection();
			if (st.dragNode.children.length && targetStat.level >= 2) return false;
			if (targetStat.level >= 3) return false;
			return true;
		},
		checkIfDraggingIntoTopLevelSection() {
			return this.getParentSectionNode(st.closestNode)?.data?.text === "Top Level";
		},
		rootDroppable() {
			if (st.closestNode === null) return false;
			return this.isSectionNode(st.dragNode);
		},
		isSectionNode(stat) {
			return stat?.data?.isSection;
		},
		isParentItemNode(stat) {
			return !this.isSectionNode(stat) && !this.isChildItemNode(stat);
		},
		isChildItemNode(stat) {
			if (!stat.parent) return false;
			return !this.isSectionNode(stat.parent);
		},
		isCustomSectionNode(stat) {
			return this.isSectionNode(stat) && stat.data?.manipulations?.action === "@create";
		},
		getParentSectionNode(stat) {
			if (!this.isSectionNode(stat) && stat !== void 0) return this.getParentSectionNode(stat.parent);
			return stat;
		},
		addItem(targetStat) {
			this.targetStat = targetStat;
			this.creatingItem = true;
			this.creatingItemIsChild = this.isParentItemNode(targetStat);
		},
		addItemToTopLevel() {
			this.addItem(this.$refs.tree.rootChildren[0]);
		},
		addSection() {
			this.creatingSection = true;
		},
		itemAdded(createdConfig) {
			let item = this.normalizeNavConfig(createdConfig, false);
			item.manipulations = {
				action: "@create",
				display: createdConfig.display,
				url: createdConfig.url,
				icon: createdConfig.icon
			};
			this.$refs.tree.add(item, this.targetStat);
			this.resetItemEditor();
			this.changed = true;
		},
		sectionAdded(sectionDisplay) {
			let item = this.normalizeNavConfig({
				action: "@create",
				display: sectionDisplay,
				display_original: false
			});
			this.$refs.tree.add(item);
			this.resetSectionEditor();
			this.changed = true;
		},
		editItem(stat) {
			if (this.isSectionNode(stat)) this.editingSection = stat;
			else this.editingItem = stat;
		},
		itemUpdated(updatedConfig, item) {
			item.data.text = updatedConfig.display;
			item.data.config.icon = updatedConfig.icon;
			this.updateItemManipulation(item, "display", updatedConfig.display);
			this.updateItemManipulation(item, "url", updatedConfig.url);
			this.updateItemManipulation(item, "icon", updatedConfig.icon);
			this.updateItemAction(item);
			this.resetItemEditor();
			this.changed = true;
		},
		sectionUpdated(sectionDisplay, sectionItem) {
			sectionItem.data.text = sectionDisplay;
			this.resetSectionEditor();
			this.changed = true;
		},
		updateItemManipulation(stat, key, value) {
			if (stat.data.manipulations.action === "@create" || value !== stat.data.original[key]) stat.data.manipulations[key] = value;
			else delete stat.data.manipulations[key];
		},
		updateItemAction(stat) {
			if (this.isSectionNode(stat)) return;
			let detectedAction = this.detectItemAction(stat);
			if (detectedAction) stat.data.manipulations.action = detectedAction;
			else delete stat.data.manipulations.action;
			if (this.isChildItemNode(stat)) this.updateItemAction(stat.parent);
		},
		detectItemAction(stat) {
			let currentAction = stat.data.manipulations.action;
			switch (true) {
				case currentAction === "@create": return "@create";
				case currentAction === "@alias": return "@alias";
				case currentAction === "@hide": return "@hide";
				case this.itemHasMoved(stat): return "@move";
				case this.itemHasBeenModified(stat): return "@modify";
			}
			return null;
		},
		itemHasMoved(stat) {
			if (this.itemIsWithinOriginalParentItem(stat)) return false;
			return this.itemHasMovedWithinSection(stat) || this.itemHasMovedToAnotherSection(stat);
		},
		itemIsWithinOriginalParentItem(stat) {
			let parentsOriginalChildIds = (stat.parent.data.original || { children: [] }).children.map((child) => child.id);
			return this.isChildItemNode(stat) && parentsOriginalChildIds.includes(stat.data.config.id);
		},
		itemHasMovedWithinSection(stat) {
			let parentsOriginalChildIds = (stat.parent.data.original || { children: [] }).children.map((child) => child.id);
			if (this.isChildItemNode(stat) && !parentsOriginalChildIds.includes(stat.data.config.id)) return true;
			let currentSection = this.getParentSectionNode(stat).data?.config?.display_original || "Top Level";
			let sectionsOriginalIds = this.originalSectionItems[currentSection];
			if (sectionsOriginalIds === void 0) return false;
			if (!this.isChildItemNode(stat) && !sectionsOriginalIds.includes(stat.data.config.id)) return true;
			return false;
		},
		itemHasMovedToAnotherSection(stat) {
			return (this.getParentSectionNode(stat).data.config?.display_original || "Top Level") !== (stat.data.original.section || stat.parent.data.original.section);
		},
		itemHasBeenModified(stat) {
			return this.itemHasModifiedProperties(stat) || this.itemHasModifiedChildren(stat);
		},
		itemHasModifiedProperties(stat) {
			const { action, reorder, children, ...remaining } = stat.data.manipulations;
			return Object.keys(remaining).length > 0;
		},
		itemHasModifiedChildren(stat) {
			return stat.children.filter((childItem) => {
				return Object.keys(childItem.data.manipulations).length > 0;
			}).length > 0;
		},
		expandAll() {
			walkTreeData(this.$refs.tree.rootChildren, (stat) => {
				if (!this.isSectionNode(stat)) stat.open = true;
			});
		},
		collapseAll() {
			walkTreeData(this.$refs.tree.rootChildren, (stat) => {
				if (!this.isSectionNode(stat)) stat.open = false;
			});
		},
		resetItemEditor() {
			this.editingItem = false;
			this.creatingItem = false;
			this.creatingItemIsChild = false;
			this.targetStat = false;
		},
		resetSectionEditor() {
			this.editingSection = false;
			this.creatingSection = false;
		},
		pinItem(stat) {
			this.aliasItem(stat, this.$refs.tree.rootChildren[0]);
		},
		aliasItem(stat, parentStat) {
			let currentAction = stat.data.manipulations.action;
			let newItem = this.normalizeNavConfig({ ...stat.data.config }, false);
			if (currentAction === "@create") newItem.manipulations = { ...stat.data.manipulations };
			else newItem.manipulations = { action: "@alias" };
			newItem.children = [];
			if (newItem.original) newItem.original.children = [];
			parentStat = parentStat || stat.parent;
			this.$refs.tree.add(newItem, parentStat);
			this.changed = true;
		},
		itemIsVisible(stat) {
			return stat.data?.manipulations?.action !== "@hide";
		},
		isHideable(stat) {
			let action = stat.data.manipulations.action;
			if (this.isSectionNode(stat) && action === "@create") return false;
			return !["@alias", "@create"].includes(action);
		},
		removeItem(stat, bypassConfirmation = false) {
			if (this.isCustomSectionNode(stat) && stat.children.length && !bypassConfirmation) return this.confirmingRemoval = stat;
			this.$refs.tree.remove(stat);
			this.changed = true;
			this.confirmingRemoval = false;
		},
		hideItem(stat) {
			stat.data.manipulations.action = "@hide";
			this.updateItemAction(stat);
			this.changed = true;
		},
		showItem(stat) {
			delete stat.data.manipulations["action"];
			this.updateItemAction(stat);
			this.changed = true;
		},
		reset() {
			this.$axios.delete(this.destroyUrl).then(() => {
				router.reload();
				this.confirmingReset = false;
			}).catch(() => this.$toast.error(__("Something went wrong")));
		},
		save() {
			if (!this.changed) return;
			this.saveAs(this.updateUrl);
		},
		saveAs(url) {
			let tree = this.preparePreferencesSubmission();
			this.$axios.patch(url, { tree }).then(() => {
				router.reload();
				this.changed = false;
			}).catch(() => this.$toast.error(__("Something went wrong")));
		},
		preparePreferencesSubmission() {
			let tree = [];
			this.treeData.forEach((section) => {
				tree.push({
					display: section.text,
					display_original: section.config.display_original || section.text,
					action: section.manipulations.action || false,
					items: this.prepareItemsForSubmission(section.children)
				});
			});
			return tree;
		},
		prepareItemsForSubmission(treeItems) {
			let items = [];
			treeItems.forEach((item) => {
				items.push({
					id: this.prepareItemIdForSubmission(item),
					manipulations: item.manipulations,
					children: item.children ? this.prepareItemsForSubmission(item.children) : []
				});
			});
			return items;
		},
		prepareItemIdForSubmission(item) {
			return data_get(item, "original.id", item.text.toLowerCase().replaceAll(" ", "_"));
		},
		statHandler(stat) {
			stat.open = stat.data.open;
			return stat;
		},
		beforeDragStart(stat) {
			this.isDraggingIntoTopLevelSection = false;
			this.draggingStat = stat;
		},
		afterDrop() {
			this.updateItemAction(this.draggingStat);
			this.draggingStat = false;
			return true;
		},
		addToCommandPalette() {
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Add Nav Item"),
				icon: "plus",
				action: () => this.addItemToTopLevel()
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Add Section"),
				icon: "add-section",
				action: () => this.addSection()
			});
			Statamic.$commandPalette.add({
				category: Statamic.$commandPalette.category.Actions,
				text: __("Reset Nav Customizations"),
				icon: "history",
				action: () => this.confirmingReset = true
			});
		}
	}
};
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
var _hoisted_2 = {
	key: 0,
	class: "loading card"
};
var _hoisted_3 = { class: "page-tree-header font-medium text-sm items-center flex justify-between" };
var _hoisted_4 = ["textContent"];
var _hoisted_5 = { class: "flex gap-2" };
var _hoisted_6 = {
	key: 1,
	class: "page-tree w-full"
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_Button = resolveComponent("Button");
	const _component_CommandPaletteItem = resolveComponent("CommandPaletteItem");
	const _component_DropdownLabel = resolveComponent("DropdownLabel");
	const _component_ButtonGroup = resolveComponent("ButtonGroup");
	const _component_Header = resolveComponent("Header");
	const _component_Icon = resolveComponent("Icon");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_PanelHeader = resolveComponent("PanelHeader");
	const _component_top_level_section_branch = resolveComponent("top-level-section-branch");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_tree_branch = resolveComponent("tree-branch");
	const _component_Draggable = resolveComponent("Draggable");
	const _component_Panel = resolveComponent("Panel");
	const _component_item_editor = resolveComponent("item-editor");
	const _component_section_editor = resolveComponent("section-editor");
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode(_component_Head, { title: $props.title }, null, 8, ["title"]),
		createVNode(_component_Header, {
			title: $props.title,
			icon: "preferences"
		}, {
			default: withCtx(() => [
				createVNode(_component_Dropdown, { placement: "left-start" }, {
					default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
						default: withCtx(() => [createVNode(_component_DropdownItem, {
							text: _ctx.__("Reset Nav Customizations"),
							variant: "destructive",
							icon: "history",
							onClick: _cache[0] || (_cache[0] = ($event) => $data.confirmingReset = true)
						}, null, 8, ["text"])]),
						_: 1
					})]),
					_: 1
				}),
				$options.isDirty ? (openBlock(), createBlock(_component_CommandPaletteItem, {
					key: 0,
					category: _ctx.$commandPalette.category.Actions,
					text: _ctx.__("Discard Changes"),
					icon: "trash",
					action: $options.discardChanges
				}, {
					default: withCtx(({ text, action }) => [createVNode(_component_Button, {
						variant: "filled",
						text: _ctx.__("Discard Changes"),
						onClick: action
					}, null, 8, ["text", "onClick"])]),
					_: 1
				}, 8, [
					"category",
					"text",
					"action"
				])) : createCommentVNode("", true),
				createVNode(_component_Dropdown, { placement: "left-start" }, {
					trigger: withCtx(() => [createVNode(_component_Button, {
						text: _ctx.__("Add"),
						"icon-append": "chevron-down"
					}, null, 8, ["text"])]),
					default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
						default: withCtx(() => [createVNode(_component_DropdownItem, {
							text: _ctx.__("Add Nav Item"),
							onClick: $options.addItemToTopLevel,
							icon: "plus"
						}, null, 8, ["text", "onClick"]), createVNode(_component_DropdownItem, {
							text: _ctx.__("Add Section"),
							onClick: $options.addSection,
							icon: "add-section"
						}, null, 8, ["text", "onClick"])]),
						_: 1
					})]),
					_: 1
				}),
				createVNode(_component_ButtonGroup, null, {
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
							disabled: !$data.changed,
							text,
							onClick: action
						}, null, 8, [
							"disabled",
							"text",
							"onClick"
						])]),
						_: 1
					}, 8, [
						"category",
						"text",
						"action"
					]), $options.hasSaveAsOptions ? (openBlock(), createBlock(_component_Dropdown, {
						key: 0,
						align: "end"
					}, {
						trigger: withCtx(() => [createVNode(_component_Button, {
							icon: "chevron-down",
							variant: "primary"
						})]),
						default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
							default: withCtx(() => [createVNode(_component_DropdownLabel, null, {
								default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Save to")) + "...", 1)]),
								_: 1
							}), (openBlock(true), createElementBlock(Fragment, null, renderList($props.saveAsOptions, (option) => {
								return openBlock(), createBlock(_component_DropdownItem, {
									key: option.url,
									text: option.label,
									onClick: ($event) => $options.saveAs(option.url)
								}, null, 8, ["text", "onClick"]);
							}), 128))]),
							_: 1
						})]),
						_: 1
					})) : createCommentVNode("", true)]),
					_: 1
				})
			]),
			_: 1
		}, 8, ["title"]),
		createVNode(_component_Panel, { class: "nav-builder" }, {
			default: withCtx(() => [
				$data.loading ? (openBlock(), createElementBlock("div", _hoisted_2, [createVNode(_component_Icon, { name: "loading" })])) : createCommentVNode("", true),
				createVNode(_component_PanelHeader, null, {
					default: withCtx(() => [createBaseVNode("div", _hoisted_3, [createBaseVNode("div", { textContent: toDisplayString(_ctx.__("Navigation")) }, null, 8, _hoisted_4), createBaseVNode("div", _hoisted_5, [createVNode(_component_ui_button, {
						size: "sm",
						icon: "tree-collapse",
						text: _ctx.__("Collapse"),
						onClick: $options.collapseAll
					}, null, 8, ["text", "onClick"]), createVNode(_component_ui_button, {
						size: "sm",
						icon: "tree-expand",
						text: _ctx.__("Expand"),
						onClick: $options.expandAll
					}, null, 8, ["text", "onClick"])])])]),
					_: 1
				}),
				!$data.loading ? (openBlock(), createElementBlock("div", _hoisted_6, [createVNode(_component_Draggable, {
					ref: "tree",
					modelValue: $data.treeData,
					"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.treeData = $event),
					"node-key": (stat) => stat.data.id,
					space: 1,
					indent: 24,
					dir: $options.direction,
					"stat-handler": $options.statHandler,
					i18n: $options.treeDraggableI18n,
					"aria-label": _ctx.__("Tree Structure"),
					"keep-placeholder": "",
					"trigger-class": "page-move",
					"each-droppable": $options.eachDroppable,
					"root-droppable": $options.rootDroppable,
					onChange: _cache[2] || (_cache[2] = ($event) => $data.changed = true),
					onBeforeDragStart: $options.beforeDragStart,
					onAfterDrop: $options.afterDrop
				}, {
					placeholder: withCtx(() => [createBaseVNode("div", { class: normalizeClass(["rounded-lg border border-dashed border-blue-400 bg-blue-500/10 p-2", {
						"mt-6 is-section-placeholder": $options.isSectionNode($data.draggingStat),
						"ml-[-24px]": $data.isDraggingIntoTopLevelSection
					}]) }, " \xA0 ", 2)]),
					default: withCtx(({ node, stat }) => [stat.level === 1 && stat.data?.text === "Top Level" ? (openBlock(), createBlock(_component_top_level_section_branch, {
						key: 0,
						stat
					}, null, 8, ["stat"])) : (openBlock(), createBlock(_component_tree_branch, {
						key: 1,
						item: node,
						"parent-section": $options.getParentSectionNode(stat),
						depth: stat.level,
						stat,
						"is-open": stat.open,
						"is-child": $options.isChildItemNode(stat),
						"has-children": stat.children.length > 0,
						class: normalizeClass(["mb-px", { "mt-6": $options.isSectionNode(stat) }]),
						onEdit: ($event) => $options.editItem(stat),
						onToggleOpen: ($event) => stat.open = !stat.open
					}, {
						"branch-options": withCtx(({ inTopLevelSection }) => [
							stat.level < 3 ? (openBlock(), createBlock(_component_DropdownItem, {
								key: 0,
								text: _ctx.__("Add Item"),
								onClick: ($event) => $options.addItem(stat),
								icon: "plus"
							}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
							createVNode(_component_DropdownItem, {
								text: _ctx.__("Edit"),
								onClick: ($event) => $options.editItem(stat),
								icon: "edit"
							}, null, 8, ["text", "onClick"]),
							!$options.isSectionNode(stat) && !inTopLevelSection ? (openBlock(), createBlock(_component_DropdownItem, {
								key: 1,
								text: _ctx.__("Pin to Top Level"),
								icon: "pin",
								onClick: ($event) => $options.pinItem(stat)
							}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
							!$options.isSectionNode(stat) ? (openBlock(), createBlock(_component_DropdownItem, {
								key: 2,
								text: _ctx.__("Duplicate"),
								icon: "duplicate",
								onClick: ($event) => $options.aliasItem(stat)
							}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
							createVNode(_component_DropdownSeparator),
							$options.itemIsVisible(stat) ? (openBlock(), createBlock(_component_DropdownItem, {
								key: 3,
								text: $options.isHideable(stat) ? _ctx.__("Hide") : _ctx.__("Remove"),
								icon: $options.isHideable(stat) ? "eye-closed" : "trash",
								variant: "destructive",
								onClick: ($event) => $options.isHideable(stat) ? $options.hideItem(stat) : $options.removeItem(stat)
							}, null, 8, [
								"text",
								"icon",
								"onClick"
							])) : (openBlock(), createBlock(_component_DropdownItem, {
								key: 4,
								text: _ctx.__("Show"),
								icon: "eye",
								onClick: ($event) => $options.showItem(stat)
							}, null, 8, ["text", "onClick"]))
						]),
						_: 2
					}, 1032, [
						"item",
						"parent-section",
						"depth",
						"stat",
						"is-open",
						"is-child",
						"has-children",
						"class",
						"onEdit",
						"onToggleOpen"
					]))]),
					_: 1
				}, 8, [
					"modelValue",
					"node-key",
					"dir",
					"stat-handler",
					"i18n",
					"aria-label",
					"each-droppable",
					"root-droppable",
					"onBeforeDragStart",
					"onAfterDrop"
				])])) : createCommentVNode("", true)
			]),
			_: 1
		}),
		$data.creatingItem ? (openBlock(), createBlock(_component_item_editor, {
			key: 0,
			creating: true,
			"is-child": $data.creatingItemIsChild,
			onClosed: $options.resetItemEditor,
			onUpdated: $options.itemAdded
		}, null, 8, [
			"is-child",
			"onClosed",
			"onUpdated"
		])) : createCommentVNode("", true),
		$data.editingItem ? (openBlock(), createBlock(_component_item_editor, {
			key: 1,
			item: $data.editingItem,
			"is-child": $options.isChildItemNode($data.editingItem),
			onClosed: $options.resetItemEditor,
			onUpdated: $options.itemUpdated
		}, null, 8, [
			"item",
			"is-child",
			"onClosed",
			"onUpdated"
		])) : createCommentVNode("", true),
		$data.creatingSection ? (openBlock(), createBlock(_component_section_editor, {
			key: 2,
			creating: true,
			onClosed: $options.resetSectionEditor,
			onUpdated: $options.sectionAdded
		}, null, 8, ["onClosed", "onUpdated"])) : createCommentVNode("", true),
		$data.editingSection ? (openBlock(), createBlock(_component_section_editor, {
			key: 3,
			"section-item": $data.editingSection,
			onClosed: $options.resetSectionEditor,
			onUpdated: $options.sectionUpdated
		}, null, 8, [
			"section-item",
			"onClosed",
			"onUpdated"
		])) : createCommentVNode("", true),
		createVNode(_component_confirmation_modal, {
			open: $data.confirmingReset,
			title: _ctx.__("Reset"),
			bodyText: _ctx.__("Are you sure you want to reset nav customizations?"),
			buttonText: _ctx.__("Reset"),
			danger: true,
			onConfirm: $options.reset,
			onCancel: _cache[3] || (_cache[3] = ($event) => $data.confirmingReset = false)
		}, null, 8, [
			"open",
			"title",
			"bodyText",
			"buttonText",
			"onConfirm"
		]),
		createVNode(_component_confirmation_modal, {
			open: $data.confirmingRemoval,
			title: _ctx.__("Remove"),
			bodyText: _ctx.__("Are you sure you want to remove this section and all of its children?"),
			buttonText: _ctx.__("Remove"),
			danger: true,
			onConfirm: _cache[4] || (_cache[4] = ($event) => $options.removeItem($data.confirmingRemoval, true)),
			onCancel: _cache[5] || (_cache[5] = ($event) => $data.confirmingRemoval = false)
		}, null, 8, [
			"open",
			"title",
			"bodyText",
			"buttonText"
		])
	]);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
