const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./PageTree-BsFaz_Gy.js","./_plugin-vue_export-helper-BOaGB7Aw.js","./ui-BfR7XN6t.js","./chunk-B-1-B7_t.js","./preload-helper-CW7Fztz1.js","./index.esm-B4SStoAz.js","./vue.esm-bundler-BbHU-fTn.js","./globals-CR4DMcIG.js","./api-BR1uuoCk.js","./ui-BflfEhKe.css","./v3-R_90cesw.js"])))=>i.map(i=>d[i]);
import { r as __toESM } from "./chunk-B-1-B7_t.js";
import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, _t as ref, at as withDirectives, b as createSlots, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, o as vModelCheckbox, q as resolveDirective, v as createCommentVNode, w as defineAsyncComponent, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, l as require_lib } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, Ci as Icon_default, I as Container_default, Jn as Heading_default, Kt as Menu_default$1, Mt as Stack_default, N as Request, Ut as Header_default, Xt as Separator_default, j as Pipeline, jt as Modal_default, li as Button_default, mt as Close_default, nn as Dropdown_default, pi as mapValues, qt as Item_default$1, tn as Item_default, u as ItemActions_default, yi as flatten } from "./ui-BfR7XN6t.js";
import { t as __vitePreload } from "./preload-helper-CW7Fztz1.js";
import { h as nanoid, n as clone } from "./globals-CR4DMcIG.js";
import { I as pick, N as HasActions_default, r as Head_default, t as toggleArchitecturalBackground } from "./index-Bu3QBQkS.js";
import { t as SiteSelector_default } from "./SiteSelector-BCgXo6du.js";
//#region resources/js/components/structures/PageEditor.vue
var _sfc_main$3 = {
	emits: [
		"closed",
		"submitted",
		"publish-info-updated",
		"localized-fields-updated"
	],
	components: {
		Heading: Heading_default,
		Button: Button_default,
		PublishContainer: Container_default,
		Icon: Icon_default,
		Stack: Stack_default,
		StackClose: Close_default
	},
	props: {
		id: String,
		entry: String,
		site: String,
		publishInfo: Object,
		blueprint: Object,
		handle: String,
		editEntryUrl: String,
		creating: Boolean,
		readOnly: Boolean
	},
	data() {
		return {
			type: this.entry ? "entry" : "url",
			values: null,
			meta: null,
			originValues: null,
			originMeta: null,
			extraValues: null,
			localizedFields: null,
			loading: true,
			saveKeyBinding: null,
			publishContainer: "tree-page",
			closingWithChanges: false
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
		headerText() {
			return this.entry ? __("Link to Entry") : __("Nav Item");
		},
		adjustedBlueprint() {
			function getFields(blueprint) {
				return flatten(blueprint.tabs[0].sections.map((sections) => sections.fields));
			}
			function isMissingField(blueprint, handle) {
				return !getFields(blueprint).some((field) => field.handle === handle);
			}
			function hasField(blueprint, handle) {
				return !isMissingField(blueprint, handle);
			}
			function getField(handle) {
				for (let sectionIndex = 0; sectionIndex < blueprint.tabs[0].sections.length; sectionIndex++) {
					const section = blueprint.tabs[0].sections[sectionIndex];
					for (let fieldIndex = 0; fieldIndex < section.fields.length; fieldIndex++) if (section.fields[fieldIndex].handle === handle) return {
						section: sectionIndex,
						field: fieldIndex
					};
				}
				return {
					section: null,
					field: null
				};
			}
			const blueprint = clone(this.blueprint);
			if (this.type == "url" && isMissingField(blueprint, "url")) blueprint.tabs[0].sections[0].fields.unshift({
				handle: "url",
				type: "text",
				display: __("URL"),
				instructions: __("Enter any internal or external URL.")
			});
			if (this.type == "entry" && hasField(blueprint, "url")) {
				const { section, field } = getField("url");
				blueprint.tabs[0].sections[section].fields.splice(field, 1);
			}
			if (isMissingField(blueprint, "title")) blueprint.tabs[0].sections[0].fields.unshift({
				handle: "title",
				type: "text",
				display: __("Title")
			});
			blueprint.tabs[0].sections.forEach((section, sectionIndex) => {
				section.fields.forEach((field, fieldIndex) => {
					blueprint.tabs[0].sections[sectionIndex].fields[fieldIndex].localizable = true;
				});
			});
			return blueprint;
		}
	},
	watch: { localizedFields: {
		deep: true,
		handler(fields) {
			if (this.loading) return;
			this.$emit("localized-fields-updated", fields);
		}
	} },
	methods: {
		submit() {
			const postUrl = cp_url(`navigation/${this.handle}/pages`);
			const values = this.containerRef.value.visibleValues;
			new Pipeline().provide({
				container: this.containerRef,
				errors: this.errorsRef,
				saving: this.savingRef
			}).through([new Request(postUrl, "POST", {
				type: this.type,
				values
			})]).then(() => this.$emit("submitted", values));
		},
		shouldClose() {
			if (this.$dirty.has(this.publishContainer)) {
				this.closingWithChanges = true;
				return false;
			}
			return true;
		},
		confirmClose(close) {
			if (this.shouldClose()) this.$refs.stack.close();
		},
		confirmCloseWithChanges() {
			this.closingWithChanges = false;
			this.$emit("closed");
		},
		getPageValues() {
			const hasPublishInfo = !!this.publishInfo;
			const hasPublishInfoWithValues = hasPublishInfo && this.publishInfo.hasOwnProperty("values");
			if (hasPublishInfo && hasPublishInfoWithValues) {
				this.updatePublishInfo(this.publishInfo);
				this.loading = false;
				return;
			}
			const creating = this.creating || hasPublishInfo && !hasPublishInfoWithValues;
			let url = creating ? cp_url(`navigation/${this.handle}/pages/create`) : cp_url(`navigation/${this.handle}/pages/${this.id}/edit`);
			url += `?site=${this.site}`;
			if (creating && this.type == "entry") url += `&entry=${this.entry}`;
			this.$axios.get(url).then((response) => {
				this.updatePublishInfo(response.data);
				this.emitPublishInfoUpdated(hasPublishInfo && this.publishInfo.new);
				this.loading = false;
			});
		},
		updatePublishInfo(info) {
			this.values = info.values;
			this.originValues = info.originValues;
			this.meta = info.meta;
			this.originMeta = info.originMeta;
			this.extraValues = info.extraValues;
			this.localizedFields = info.localizedFields;
		},
		emitPublishInfoUpdated(isNew) {
			this.$emit("publish-info-updated", clone({
				values: this.values,
				originValues: this.originValues,
				meta: this.meta,
				originMeta: this.originMeta,
				extraValues: this.extraValues,
				localizedFields: this.localizedFields,
				entry: this.entry,
				new: isNew
			}));
		}
	},
	created() {
		this.saveKeyBinding = this.$keys.bindGlobal(["mod+enter", "mod+s"], (e) => {
			e.preventDefault();
			this.submit();
		});
		this.getPageValues();
	},
	beforeUnmount() {
		this.saveKeyBinding.destroy();
	}
};
var _hoisted_1$2 = { class: "flex h-full flex-col bg-gray-100 dark:bg-gray-850" };
var _hoisted_2$1 = {
	key: 0,
	class: "relative flex-1 overflow-auto"
};
var _hoisted_3$1 = { class: "absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-75 text-center dark:bg-gray-850" };
var _hoisted_4$1 = {
	key: 1,
	class: "flex-1 overflow-auto px-1 pt-4"
};
var _hoisted_5 = {
	key: 0,
	class: "absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-75 dark:bg-gray-600"
};
var _hoisted_6 = {
	key: 2,
	class: "flex flex-wrap flex-row-reverse gap-2 items-end justify-between border-t bg-gray-200 p-4 dark:border-gray-900 dark:bg-gray-700"
};
var _hoisted_7 = {
	key: 0,
	class: "flex flex-wrap justify-end"
};
var _hoisted_8 = { key: 1 };
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Icon = resolveComponent("Icon");
	const _component_PublishContainer = resolveComponent("PublishContainer");
	const _component_Button = resolveComponent("Button");
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	const _component_Stack = resolveComponent("Stack");
	return openBlock(), createBlock(_component_Stack, {
		ref: "stack",
		size: "narrow",
		open: "",
		inset: "",
		"before-close": $options.shouldClose,
		"onUpdate:open": _cache[3] || (_cache[3] = ($event) => _ctx.$emit("closed")),
		title: $options.headerText
	}, {
		default: withCtx(() => [createBaseVNode("div", _hoisted_1$2, [
			$data.loading ? (openBlock(), createElementBlock("div", _hoisted_2$1, [createBaseVNode("div", _hoisted_3$1, [createVNode(_component_Icon, { name: "loading" })])])) : createCommentVNode("", true),
			!$data.loading ? (openBlock(), createElementBlock("div", _hoisted_4$1, [$options.saving ? (openBlock(), createElementBlock("div", _hoisted_5, [createVNode(_component_Icon, { name: "loading" })])) : createCommentVNode("", true), createVNode(_component_PublishContainer, {
				ref: "container",
				name: $data.publishContainer,
				blueprint: $options.adjustedBlueprint,
				meta: $data.meta,
				errors: $options.errors,
				"origin-values": $data.originValues,
				"origin-meta": $data.originMeta,
				"extra-values": $data.extraValues,
				site: $props.site,
				modelValue: $data.values,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.values = $event),
				"modified-fields": $data.localizedFields,
				"onUpdate:modifiedFields": _cache[1] || (_cache[1] = ($event) => $data.localizedFields = $event)
			}, null, 8, [
				"name",
				"blueprint",
				"meta",
				"errors",
				"origin-values",
				"origin-meta",
				"extra-values",
				"site",
				"modelValue",
				"modified-fields"
			])])) : createCommentVNode("", true),
			!$data.loading && (!$props.readOnly || $data.type === "entry") ? (openBlock(), createElementBlock("div", _hoisted_6, [!$props.readOnly ? (openBlock(), createElementBlock("div", _hoisted_7, [createVNode(_component_Button, {
				variant: "ghost",
				class: "me-2",
				text: _ctx.__("Cancel"),
				onClick: $options.confirmClose
			}, null, 8, ["text", "onClick"]), createVNode(_component_Button, {
				variant: "primary",
				text: _ctx.__("Apply"),
				onClick: $options.submit
			}, null, 8, ["text", "onClick"])])) : createCommentVNode("", true), $data.type === "entry" ? (openBlock(), createElementBlock("div", _hoisted_8, [createVNode(_component_Button, {
				icon: "external-link",
				variant: "ghost",
				text: _ctx.__("Edit Entry"),
				href: $props.editEntryUrl,
				target: "_blank"
			}, null, 8, ["text", "href"])])) : createCommentVNode("", true)])) : createCommentVNode("", true)
		]), createVNode(_component_confirmation_modal, {
			open: $data.closingWithChanges,
			title: _ctx.__("Unsaved Changes"),
			"body-text": _ctx.__("Are you sure? Unsaved changes will be lost."),
			"button-text": _ctx.__("Discard Changes"),
			danger: true,
			onConfirm: $options.confirmCloseWithChanges,
			onCancel: _cache[2] || (_cache[2] = ($event) => $data.closingWithChanges = false)
		}, null, 8, [
			"open",
			"title",
			"body-text",
			"button-text",
			"onConfirm"
		])]),
		_: 1
	}, 8, ["before-close", "title"]);
}
var PageEditor_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$3, [["render", _sfc_render$3], ["__file", "PageEditor.vue"]]);
//#endregion
//#region resources/js/components/structures/PageSelector.vue
var import_lib = /* @__PURE__ */ __toESM(require_lib());
var _sfc_main$2 = {
	props: {
		site: String,
		collections: Array,
		canSelectAcrossSites: Boolean,
		queryScopes: {
			type: Array,
			default: () => []
		},
		maxItems: {
			type: Number,
			required: false
		},
		tree: {
			type: Object,
			required: false
		}
	},
	data() {
		return {
			config: {
				type: "entries",
				collections: this.collections,
				select_across_sites: this.canSelectAcrossSites,
				query_scopes: this.queryScopes
			},
			columns: [{
				label: __("Title"),
				field: "title"
			}, {
				label: __("Slug"),
				field: "slug"
			}]
		};
	},
	computed: {
		itemDataUrl() {
			return cp_url("fieldtypes/relationship/data") + "?" + import_lib.default.stringify({ config: this.configParameter });
		},
		selectionsUrl() {
			return cp_url("fieldtypes/relationship") + "?" + import_lib.default.stringify({
				config: this.configParameter,
				collections: this.collections
			});
		},
		filtersUrl() {
			return cp_url("fieldtypes/relationship/filters") + "?" + import_lib.default.stringify({
				config: this.configParameter,
				collections: this.collections
			});
		},
		configParameter() {
			return utf8btoa(JSON.stringify(this.config));
		}
	},
	methods: {
		linkExistingItem() {
			this.$refs.input.openSelector();
		},
		itemDataUpdated(data) {
			if (data.length) this.$emit("selected", data);
		}
	}
};
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_relationship_input = resolveComponent("relationship-input");
	return openBlock(), createBlock(_component_relationship_input, {
		class: "hidden",
		ref: "input",
		name: "entries",
		value: [],
		config: $data.config,
		site: $props.site,
		"item-data-url": $options.itemDataUrl,
		"selections-url": $options.selectionsUrl,
		"filters-url": $options.filtersUrl,
		search: true,
		columns: $data.columns,
		"can-create": false,
		"can-reorder": false,
		"max-items": $props.maxItems,
		tree: $props.tree,
		onItemDataUpdated: $options.itemDataUpdated
	}, null, 8, [
		"config",
		"site",
		"item-data-url",
		"selections-url",
		"filters-url",
		"columns",
		"max-items",
		"tree",
		"onItemDataUpdated"
	]);
}
var PageSelector_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "PageSelector.vue"]]);
//#endregion
//#region resources/js/components/navigation/RemovePageConfirmation.vue
var _sfc_main$1 = {
	__name: "RemovePageConfirmation",
	props: { children: Number },
	emits: ["confirm", "cancel"],
	setup(__props, { expose: __expose, emit: __emit }) {
		__expose();
		const __returned__ = {
			emits: __emit,
			props: __props,
			modalOpen: ref(true),
			shouldDeleteChildren: ref(false),
			get Modal() {
				return Modal_default;
			},
			get Button() {
				return Button_default;
			},
			ref
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1$1 = ["textContent"];
var _hoisted_2 = ["textContent"];
var _hoisted_3 = {
	key: 0,
	class: "flex items-center"
};
var _hoisted_4 = { class: "flex items-center justify-end space-x-3 pt-3 pb-1" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock($setup["Modal"], {
		title: _ctx.__("Remove Item"),
		open: $setup.modalOpen,
		"onUpdate:open": _cache[3] || (_cache[3] = ($event) => $setup.modalOpen = $event),
		onDismissed: _cache[4] || (_cache[4] = ($event) => _ctx.$emit("cancel"))
	}, {
		footer: withCtx(() => [createBaseVNode("div", _hoisted_4, [createVNode($setup["Button"], {
			variant: "ghost",
			onClick: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("cancel")),
			text: _ctx.__("Cancel")
		}, null, 8, ["text"]), createVNode($setup["Button"], {
			variant: "danger",
			onClick: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("confirm", $setup.shouldDeleteChildren)),
			text: _ctx.__("Remove")
		}, null, 8, ["text"])])]),
		default: withCtx(() => [
			createBaseVNode("p", {
				class: "mb-4",
				textContent: toDisplayString(_ctx.__("Are you sure you want to remove this item?"))
			}, null, 8, _hoisted_1$1),
			createBaseVNode("p", {
				class: "mb-4",
				textContent: toDisplayString(_ctx.__("Only the references will be removed. Entries will not be deleted."))
			}, null, 8, _hoisted_2),
			$props.children ? (openBlock(), createElementBlock("label", _hoisted_3, [withDirectives(createBaseVNode("input", {
				type: "checkbox",
				class: "ltr:mr-2 rtl:ml-2",
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.shouldDeleteChildren = $event)
			}, null, 512), [[vModelCheckbox, $setup.shouldDeleteChildren]]), createTextVNode(" " + toDisplayString(_ctx.__n("Remove child item|Remove :count child items", $props.children)), 1)])) : createCommentVNode("", true)
		]),
		_: 1
	}, 8, ["title", "open"]);
}
var RemovePageConfirmation_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "RemovePageConfirmation.vue"]]);
//#endregion
//#region resources/js/pages/navigation/Show.vue
var _sfc_main = {
	mixins: [HasActions_default],
	components: {
		Head: Head_default,
		Button: Button_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		DropdownItem: Item_default,
		DropdownSeparator: Separator_default,
		PageTree: defineAsyncComponent(() => __vitePreload(() => import("./PageTree-BsFaz_Gy.js"), __vite__mapDeps([0,1,2,3,4,5,6,7,8,9,10]), import.meta.url)),
		PageEditor: PageEditor_default,
		PageSelector: PageSelector_default,
		RemovePageConfirmation: RemovePageConfirmation_default,
		SiteSelector: SiteSelector_default,
		EmptyStateMenu: Menu_default$1,
		EmptyStateItem: Item_default$1,
		Header: Header_default,
		ItemActions: ItemActions_default
	},
	props: {
		title: {
			type: String,
			required: true
		},
		handle: {
			type: String,
			required: true
		},
		collections: {
			type: Array,
			required: true
		},
		editUrl: {
			type: String,
			required: true
		},
		blueprintUrl: {
			type: String,
			required: true
		},
		pagesUrl: {
			type: String,
			required: true
		},
		submitUrl: {
			type: String,
			required: true
		},
		initialMaxDepth: {
			type: Number,
			default: null
		},
		expectsRoot: {
			type: Boolean,
			required: true
		},
		site: {
			type: String,
			required: true
		},
		sites: {
			type: Array,
			required: true
		},
		blueprint: {
			type: Object,
			required: true
		},
		canEdit: {
			type: Boolean,
			required: true
		},
		canSelectAcrossSites: {
			type: Boolean,
			required: true
		},
		canEditBlueprint: {
			type: Boolean,
			required: true
		},
		entryQueryScopes: {
			type: Array,
			default: () => []
		},
		collectionTree: {
			type: Object,
			required: false
		}
	},
	data() {
		return {
			mounted: false,
			changed: false,
			maxDepth: this.initialMaxDepth || Infinity,
			creatingPage: false,
			editingPage: false,
			targetParent: null,
			showPageDeletionConfirmation: false,
			pageBeingDeleted: null,
			pageDeletionConfirmCallback: null,
			removePageOnCancel: false,
			preferencesPrefix: `navs.${this.handle}`,
			publishInfo: {}
		};
	},
	computed: {
		isDirty() {
			return this.$dirty.has("page-tree");
		},
		numberOfChildrenToBeDeleted() {
			let children = 0;
			const countChildren = (page) => {
				page.children.forEach((child) => {
					children++;
					countChildren(child);
				});
			};
			countChildren(this.pageBeingDeleted);
			return children;
		},
		hasCollections() {
			return this.collections.length > 0;
		},
		submissionData() {
			return mapValues(this.publishInfo, (value) => {
				return pick(value, [
					"entry",
					"values",
					"localizedFields",
					"new"
				]);
			});
		},
		direction() {
			return this.$config.get("direction", "ltr");
		},
		fields() {
			return this.blueprint.tabs.reduce((fields, tab) => {
				return tab.sections.reduce((fields, section) => {
					return fields.concat(section.fields);
				}, []);
			}, []);
		},
		maxPagesSelection() {
			if (this.fields.filter((field) => field.required).length > 0) return 1;
		}
	},
	watch: { changed(changed) {
		this.$dirty.state("page-tree", changed);
	} },
	mounted() {
		this.mounted = true;
		this.addToCommandPalette();
	},
	methods: {
		addLink() {
			if (!this.hasCollections) this.linkPage();
		},
		linkPage(vm) {
			this.targetParent = vm;
			this.openPageCreator();
		},
		linkEntries(vm) {
			this.targetParent = vm;
			this.$refs.selector.linkExistingItem();
		},
		entriesSelected(pages) {
			pages = pages.map((page) => ({
				...page,
				id: nanoid(),
				entry: page.id,
				entry_title: page.title,
				title: null
			}));
			pages.forEach((page) => {
				this.publishInfo = {
					...this.publishInfo,
					[page.id]: {
						entry: page.entry,
						new: true
					}
				};
			});
			this.$refs.tree.addPages(pages, this.targetParent);
			if (this.maxPagesSelection === 1) {
				this.removePageOnCancel = true;
				this.$wait(300).then(() => this.editPage(pages[0]));
			}
		},
		isEntryBranch(branch) {
			return !!branch.entry;
		},
		isLinkBranch(branch) {
			return !this.isEntryBranch(branch) && branch.url;
		},
		isTextBranch(branch) {
			return !this.isEntryBranch(branch) && !this.isLinkBranch(branch);
		},
		editPage(page) {
			this.editingPage = { page };
		},
		updatePage(values) {
			this.editingPage.page.url = values.url;
			this.editingPage.page.title = values.title;
			this.editingPage.page.values = values;
			this.$refs.tree.pageUpdated();
			this.publishInfo[this.editingPage.page.id].values = values;
			this.editingPage = false;
		},
		closePageEditor() {
			if (this.removePageOnCancel) {
				this.$refs.tree.$refs[`branch-${this.editingPage.page.id}`].remove();
				this.removePageOnCancel = false;
			}
			this.editingPage = false;
		},
		openPageCreator() {
			this.creatingPage = { info: null };
		},
		closePageCreator() {
			this.creatingPage = false;
		},
		pageCreated(values) {
			const page = {
				id: nanoid(),
				title: values.title,
				url: values.url,
				status: null,
				children: []
			};
			this.publishInfo[page.id] = {
				...this.creatingPage.info,
				values,
				entry: null,
				new: true
			};
			this.$refs.tree.addPages([page], this.targetParent);
			this.closePageCreator();
		},
		deleteTreeBranch(branch, removeFromUi) {
			this.showPageDeletionConfirmation = true;
			this.pageBeingDeleted = branch;
			this.pageDeletionConfirmCallback = (shouldDeleteChildren) => {
				removeFromUi(shouldDeleteChildren);
				this.showPageDeletionConfirmation = false;
				this.pageBeingDeleted = branch;
				delete this.publishInfo[branch.id];
			};
		},
		siteSelected(site) {
			router.get(this.sites.find((s) => s.handle === site).url);
		},
		updatePublishInfo(info) {
			this.publishInfo = {
				...this.publishInfo,
				[this.editingPage.page.id]: info
			};
		},
		updatePendingCreatedPagePublishInfo(info) {
			this.creatingPage.info = info;
		},
		updateLocalizedFields(fields) {
			this.publishInfo[this.editingPage.page.id].localizedFields = fields;
		},
		updatePendingCreatedPageLocalizedFields(fields) {
			this.creatingPage.info.localizedFields = fields;
		},
		treeSaved(response) {
			if (!response.data.saved) return this.$toast.error(`Couldn't save tree`);
			this.replaceGeneratedIds(response.data.generatedIds);
			this.changed = false;
		},
		treeLoaded(pages) {
			toggleArchitecturalBackground(pages.length === 0);
		},
		treeChanged(pages) {
			this.changed = true;
			this.targetParent = null;
			toggleArchitecturalBackground(pages.length === 0);
		},
		replaceGeneratedIds(ids) {
			for (let [oldId, newId] of Object.entries(ids)) {
				this.publishInfo[newId] = {
					...this.publishInfo[oldId],
					new: false
				};
				delete this.publishInfo[oldId];
				let branch = this.$refs.tree.getNodeByBranchId(oldId);
				branch.id = newId;
				this.$refs.tree.pageUpdated();
			}
		},
		addToCommandPalette() {
			Statamic.$commandPalette.add({
				when: () => this.canEdit,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Save Changes"),
				icon: "save",
				action: () => this.$refs.tree?.save(),
				prioritize: true
			});
			Statamic.$commandPalette.add({
				when: () => this.canEdit && this.hasCollections,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Add Nav Item"),
				icon: "add-list",
				action: () => this.linkPage()
			});
			Statamic.$commandPalette.add({
				when: () => this.canEdit && this.hasCollections,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Add Link to Entry"),
				icon: "add-link",
				action: () => this.linkEntries()
			});
			Statamic.$commandPalette.add({
				when: () => this.canEdit && !this.hasCollections,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Add Nav Item"),
				icon: "add-link",
				action: () => this.addLink()
			});
			Statamic.$commandPalette.add({
				when: () => this.canEdit,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Configure Navigation"),
				icon: "cog",
				url: this.editUrl
			});
			Statamic.$commandPalette.add({
				when: () => this.canEditBlueprint,
				category: Statamic.$commandPalette.category.Actions,
				text: __("Edit Blueprints"),
				icon: "blueprint-edit",
				url: this.blueprintUrl
			});
		}
	}
};
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_DropdownItem = resolveComponent("DropdownItem");
	const _component_DropdownSeparator = resolveComponent("DropdownSeparator");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _component_ItemActions = resolveComponent("ItemActions");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_site_selector = resolveComponent("site-selector");
	const _component_Button = resolveComponent("Button");
	const _component_Header = resolveComponent("Header");
	const _component_EmptyStateItem = resolveComponent("EmptyStateItem");
	const _component_EmptyStateMenu = resolveComponent("EmptyStateMenu");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_page_tree = resolveComponent("page-tree");
	const _component_page_selector = resolveComponent("page-selector");
	const _component_page_editor = resolveComponent("page-editor");
	const _component_remove_page_confirmation = resolveComponent("remove-page-confirmation");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode(_component_Head, { title: [_ctx.__($props.title), _ctx.__("Navigation")] }, null, 8, ["title"]),
		$data.mounted ? (openBlock(), createBlock(_component_Header, {
			key: 0,
			title: _ctx.__($props.title),
			icon: "navigation"
		}, {
			default: withCtx(() => [
				_ctx.hasItemActions ? (openBlock(), createBlock(_component_ItemActions, {
					key: 0,
					url: _ctx.itemActionUrl,
					actions: _ctx.itemActions,
					item: $props.handle,
					onStarted: _ctx.actionStarted,
					onCompleted: _ctx.actionCompleted
				}, {
					default: withCtx(({ actions: preparedActions }) => [$props.canEdit || $props.canEditBlueprint || _ctx.hasItemActions ? (openBlock(), createBlock(_component_Dropdown, {
						key: 0,
						placement: "left-start"
					}, {
						default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
							default: withCtx(() => [
								$props.canEdit ? (openBlock(), createBlock(_component_DropdownItem, {
									key: 0,
									text: _ctx.__("Configure Navigation"),
									icon: "cog",
									href: $props.editUrl
								}, null, 8, ["text", "href"])) : createCommentVNode("", true),
								$props.canEditBlueprint ? (openBlock(), createBlock(_component_DropdownItem, {
									key: 1,
									text: _ctx.__("Edit Blueprints"),
									icon: "blueprint-edit",
									href: $props.blueprintUrl
								}, null, 8, ["text", "href"])) : createCommentVNode("", true),
								_ctx.hasItemActions && ($props.canEdit || $props.canEditBlueprint) ? (openBlock(), createBlock(_component_DropdownSeparator, { key: 2 })) : createCommentVNode("", true),
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
				])) : $props.canEdit || $props.canEditBlueprint ? (openBlock(), createBlock(_component_Dropdown, {
					key: 1,
					placement: "left-start"
				}, {
					default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
						default: withCtx(() => [$props.canEdit ? (openBlock(), createBlock(_component_DropdownItem, {
							key: 0,
							text: _ctx.__("Configure Navigation"),
							icon: "cog",
							href: $props.editUrl
						}, null, 8, ["text", "href"])) : createCommentVNode("", true), $props.canEditBlueprint ? (openBlock(), createBlock(_component_DropdownItem, {
							key: 1,
							text: _ctx.__("Edit Blueprints"),
							icon: "blueprint-edit",
							href: $props.blueprintUrl
						}, null, 8, ["text", "href"])) : createCommentVNode("", true)]),
						_: 1
					})]),
					_: 1
				})) : createCommentVNode("", true),
				$options.isDirty ? (openBlock(), createBlock(_component_ui_button, {
					key: 2,
					variant: "filled",
					text: _ctx.__("Discard Changes"),
					onClick: _ctx.$refs.tree.cancel
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				$props.sites.length > 1 ? (openBlock(), createBlock(_component_site_selector, {
					key: 3,
					sites: $props.sites,
					"model-value": $props.site,
					"onUpdate:modelValue": $options.siteSelected
				}, null, 8, [
					"sites",
					"model-value",
					"onUpdate:modelValue"
				])) : createCommentVNode("", true),
				$props.canEdit && $options.hasCollections ? (openBlock(), createBlock(_component_Dropdown, {
					key: 4,
					placement: "left-start",
					disabled: !$options.hasCollections
				}, {
					trigger: withCtx(() => [createVNode(_component_Button, {
						text: _ctx.__("Add"),
						"icon-append": "chevron-down"
					}, null, 8, ["text"])]),
					default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
						default: withCtx(() => [createVNode(_component_DropdownItem, {
							text: _ctx.__("Add Nav Item"),
							onClick: _cache[0] || (_cache[0] = ($event) => $options.linkPage()),
							icon: "add-list"
						}, null, 8, ["text"]), createVNode(_component_DropdownItem, {
							text: _ctx.__("Add Link to Entry"),
							onClick: _cache[1] || (_cache[1] = ($event) => $options.linkEntries()),
							icon: "add-link"
						}, null, 8, ["text"])]),
						_: 1
					})]),
					_: 1
				}, 8, ["disabled"])) : $props.canEdit && !$options.hasCollections ? (openBlock(), createBlock(_component_Button, {
					key: 5,
					text: _ctx.__("Add Nav Item"),
					onClick: $options.addLink
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				$props.canEdit ? (openBlock(), createBlock(_component_Button, {
					key: 6,
					disabled: !$data.changed,
					variant: "primary",
					text: _ctx.__("Save Changes"),
					onClick: _ctx.$refs.tree?.save
				}, null, 8, [
					"disabled",
					"text",
					"onClick"
				])) : createCommentVNode("", true)
			]),
			_: 1
		}, 8, ["title"])) : createCommentVNode("", true),
		createVNode(_component_page_tree, {
			ref: "tree",
			"pages-url": $props.pagesUrl,
			"submit-url": $props.submitUrl,
			"submit-parameters": { data: $options.submissionData },
			"max-depth": $data.maxDepth,
			"expects-root": $props.expectsRoot,
			site: $props.site,
			"preferences-prefix": $data.preferencesPrefix,
			editable: $props.canEdit,
			onEditPage: $options.editPage,
			onLoaded: $options.treeLoaded,
			onChanged: $options.treeChanged,
			onSaved: $options.treeSaved,
			onCanceled: _cache[4] || (_cache[4] = ($event) => $data.changed = false)
		}, createSlots({
			empty: withCtx(() => [createVNode(_component_EmptyStateMenu, { heading: _ctx.__("Start designing your navigation with these steps") }, {
				default: withCtx(() => [
					createVNode(_component_EmptyStateItem, {
						href: $props.editUrl,
						icon: "configure",
						heading: _ctx.__("Configure Navigation"),
						description: _ctx.__("messages.navigation_configure_settings_intro")
					}, null, 8, [
						"href",
						"heading",
						"description"
					]),
					createVNode(_component_EmptyStateItem, {
						icon: "fieldtype-link",
						heading: _ctx.__("Link to URL"),
						description: _ctx.__("messages.navigation_link_to_url_instructions"),
						onClick: _cache[2] || (_cache[2] = ($event) => $options.linkPage())
					}, null, 8, ["heading", "description"]),
					$options.hasCollections ? (openBlock(), createBlock(_component_EmptyStateItem, {
						key: 0,
						icon: "navigation",
						heading: _ctx.__("Link to Entry"),
						description: _ctx.__("messages.navigation_link_to_entry_instructions"),
						onClick: _cache[3] || (_cache[3] = ($event) => $options.linkEntries())
					}, null, 8, ["heading", "description"])) : createCommentVNode("", true),
					createVNode(_component_EmptyStateItem, {
						href: _ctx.docs_url("navigation"),
						icon: "support",
						heading: _ctx.__("Read the Documentation"),
						description: _ctx.__("messages.navigation_documentation_instructions")
					}, null, 8, [
						"href",
						"heading",
						"description"
					])
				]),
				_: 1
			}, 8, ["heading"])]),
			"branch-icon": withCtx(({ branch }) => [
				$options.isEntryBranch(branch) ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
					key: 0,
					class: "size-3.5! text-gray-500",
					name: "link",
					tabindex: "-1"
				}, null, 512)), [[_directive_tooltip, _ctx.__("Entry link")]]) : createCommentVNode("", true),
				$options.isLinkBranch(branch) ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
					key: 1,
					class: "size-3.5! text-gray-500",
					name: "external-link",
					tabindex: "-1"
				}, null, 512)), [[_directive_tooltip, _ctx.__("External link")]]) : createCommentVNode("", true),
				$options.isTextBranch(branch) ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
					key: 2,
					class: "size-3.5! text-gray-500",
					name: "page",
					tabindex: "-1"
				}, null, 512)), [[_directive_tooltip, _ctx.__("Text")]]) : createCommentVNode("", true)
			]),
			_: 2
		}, [$props.canEdit ? {
			name: "branch-options",
			fn: withCtx(({ branch, removeBranch, stat, depth }) => [
				$options.isEntryBranch(stat) ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 0,
					text: _ctx.__("Edit Entry"),
					href: branch.edit_url,
					icon: "edit"
				}, null, 8, ["text", "href"])) : createCommentVNode("", true),
				createVNode(_component_DropdownItem, {
					text: _ctx.__("Edit Nav Item"),
					onClick: ($event) => $options.editPage(branch),
					icon: "edit"
				}, null, 8, ["text", "onClick"]),
				depth < $data.maxDepth ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 1,
					text: _ctx.__("Add child nav item"),
					onClick: ($event) => $options.linkPage(stat),
					icon: "add-list"
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				depth < $data.maxDepth && $options.hasCollections ? (openBlock(), createBlock(_component_DropdownItem, {
					key: 2,
					text: _ctx.__("Add child link to entry"),
					onClick: ($event) => $options.linkEntries(stat),
					icon: "add-link"
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				createVNode(_component_DropdownSeparator),
				createVNode(_component_DropdownItem, {
					text: _ctx.__("Remove"),
					variant: "destructive",
					onClick: ($event) => $options.deleteTreeBranch(branch, removeBranch),
					icon: "trash"
				}, null, 8, ["text", "onClick"])
			]),
			key: "0"
		} : void 0]), 1032, [
			"pages-url",
			"submit-url",
			"submit-parameters",
			"max-depth",
			"expects-root",
			"site",
			"preferences-prefix",
			"editable",
			"onEditPage",
			"onLoaded",
			"onChanged",
			"onSaved"
		]),
		$options.hasCollections ? (openBlock(), createBlock(_component_page_selector, {
			key: 1,
			ref: "selector",
			site: $props.site,
			collections: $props.collections,
			"query-scopes": $props.entryQueryScopes,
			"max-items": $options.maxPagesSelection,
			"can-select-across-sites": $props.canSelectAcrossSites,
			tree: $props.collectionTree,
			onSelected: $options.entriesSelected
		}, null, 8, [
			"site",
			"collections",
			"query-scopes",
			"max-items",
			"can-select-across-sites",
			"tree",
			"onSelected"
		])) : createCommentVNode("", true),
		$data.editingPage ? (openBlock(), createBlock(_component_page_editor, {
			key: 2,
			site: $props.site,
			id: $data.editingPage.page.id,
			entry: $data.editingPage.page.entry,
			editEntryUrl: $data.editingPage.page.entry ? $data.editingPage.page.edit_url : null,
			"publish-info": $data.publishInfo[$data.editingPage.page.id],
			blueprint: $props.blueprint,
			handle: $props.handle,
			"read-only": !$props.canEdit,
			onPublishInfoUpdated: $options.updatePublishInfo,
			onLocalizedFieldsUpdated: $options.updateLocalizedFields,
			onClosed: $options.closePageEditor,
			onSubmitted: $options.updatePage
		}, null, 8, [
			"site",
			"id",
			"entry",
			"editEntryUrl",
			"publish-info",
			"blueprint",
			"handle",
			"read-only",
			"onPublishInfoUpdated",
			"onLocalizedFieldsUpdated",
			"onClosed",
			"onSubmitted"
		])) : createCommentVNode("", true),
		$data.creatingPage ? (openBlock(), createBlock(_component_page_editor, {
			key: 3,
			creating: "",
			site: $props.site,
			blueprint: $props.blueprint,
			handle: $props.handle,
			"read-only": !$props.canEdit,
			onPublishInfoUpdated: $options.updatePendingCreatedPagePublishInfo,
			onLocalizedFieldsUpdated: $options.updatePendingCreatedPageLocalizedFields,
			onClosed: $options.closePageCreator,
			onSubmitted: $options.pageCreated
		}, null, 8, [
			"site",
			"blueprint",
			"handle",
			"read-only",
			"onPublishInfoUpdated",
			"onLocalizedFieldsUpdated",
			"onClosed",
			"onSubmitted"
		])) : createCommentVNode("", true),
		$data.showPageDeletionConfirmation ? (openBlock(), createBlock(_component_remove_page_confirmation, {
			key: 4,
			children: $options.numberOfChildrenToBeDeleted,
			onConfirm: $data.pageDeletionConfirmCallback,
			onCancel: _cache[5] || (_cache[5] = ($event) => {
				$data.showPageDeletionConfirmation = false;
				$data.pageBeingDeleted = null;
			})
		}, null, 8, ["children", "onConfirm"])) : createCommentVNode("", true)
	]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
