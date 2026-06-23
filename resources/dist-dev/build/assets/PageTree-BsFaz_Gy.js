import { At as toDisplayString, B as openBlock, C as createVNode, D as guardReactiveProps, Dt as normalizeClass, G as renderSlot, K as resolveComponent, Ot as normalizeProps, _ as createBlock, at as withDirectives, c as vShow, g as createBaseVNode, it as withCtx, q as resolveDirective, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { $t as Menu_default, Ci as Icon_default, Gn as Panel_default, nn as Dropdown_default, qn as Header_default } from "./ui-BfR7XN6t.js";
import { n as clone } from "./globals-CR4DMcIG.js";
import { n as st, r as walkTreeData, t as je } from "./v3-R_90cesw.js";
//#region resources/js/components/structures/Branch.vue
var _sfc_main$1 = {
	components: {
		Link: link_default,
		Dropdown: Dropdown_default,
		DropdownMenu: Menu_default,
		Icon: Icon_default
	},
	props: {
		page: Object,
		depth: Number,
		root: Boolean,
		firstPageIsRoot: Boolean,
		isOpen: Boolean,
		hasChildren: Boolean,
		showBlueprint: Boolean,
		showSlugs: Boolean,
		editable: {
			type: Boolean,
			default: true
		},
		stat: Object
	},
	data() {
		return { editing: false };
	},
	computed: {
		isTopLevelBranch() {
			return this.depth === 1;
		},
		isRoot() {
			return this.root;
		},
		isEntry() {
			return Boolean(this.page.id);
		},
		isLink() {
			return !this.page.id && this.page.title && this.page.url;
		},
		isText() {
			return this.page.title && !this.page.url;
		},
		title() {
			return this.page.title || this.page.entry_title || this.page.url;
		},
		slugPath() {
			return this.isRoot ? "/" : "/" + this.page.slug;
		}
	},
	methods: {
		getStatusTooltip() {
			let label = __(this.page.status) || __("Text item");
			return label[0].toUpperCase() + label.slice(1);
		},
		remove(deleteChildren) {
			this.$emit("removed", this.stat, deleteChildren);
		}
	}
};
var _hoisted_1$1 = {
	key: 0,
	class: "page-move w-6"
};
var _hoisted_2$1 = { class: "flex flex-1 items-center px-1.5 text-xs leading-normal" };
var _hoisted_3$1 = ["href", "textContent"];
var _hoisted_4$1 = {
	key: 1,
	class: "pt-[2px] font-mono text-2xs text-gray-700 dark:text-gray-500"
};
var _hoisted_5$1 = {
	key: 3,
	class: "flex items-center gap-2"
};
var _hoisted_6 = { class: "flex items-center gap-2 sm:gap-3" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_status_indicator = resolveComponent("ui-status-indicator");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_Icon = resolveComponent("Icon");
	const _component_Link = resolveComponent("Link");
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_DropdownMenu = resolveComponent("DropdownMenu");
	const _component_Dropdown = resolveComponent("Dropdown");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", { class: normalizeClass(["page-tree-branch flex", { "page-tree-branch--has-children": $props.hasChildren }]) }, [renderSlot(_ctx.$slots, "branch-action", { branch: $props.page }, () => [$props.editable ? (openBlock(), createElementBlock("div", _hoisted_1$1)) : createCommentVNode("", true)]), createBaseVNode("div", _hoisted_2$1, [createBaseVNode("div", {
		class: "flex gap-2 sm:gap-3 grow items-center py-3",
		onClick: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("branch-clicked", $props.page))
	}, [
		withDirectives(createVNode(_component_ui_status_indicator, { status: $props.page.status }, null, 8, ["status"]), [[_directive_tooltip, $options.getStatusTooltip()]]),
		$options.isRoot ? withDirectives((openBlock(), createBlock(_component_ui_icon, {
			key: 0,
			name: "home",
			class: "size-4"
		}, null, 512)), [[_directive_tooltip, _ctx.__("This is the root page")]]) : createCommentVNode("", true),
		createBaseVNode("a", {
			onClick: _cache[0] || (_cache[0] = withModifiers(($event) => _ctx.$emit("edit", $event), ["prevent"])),
			class: normalizeClass({ "text-sm font-medium is-top-level-branch": $options.isTopLevelBranch }),
			href: $props.page.edit_url,
			textContent: toDisplayString($options.title)
		}, null, 10, _hoisted_3$1),
		$props.showSlugs ? (openBlock(), createElementBlock("span", _hoisted_4$1, toDisplayString($options.slugPath), 1)) : createCommentVNode("", true),
		$props.hasChildren ? (openBlock(), createBlock(_component_ui_button, {
			key: 2,
			class: normalizeClass(["transition duration-100 [&_svg]:size-4! -mx-1.5", {
				"-rotate-90 is-closed": !$props.isOpen,
				"is-open": $props.isOpen
			}]),
			icon: "chevron-down",
			size: "xs",
			round: "",
			variant: "ghost",
			"aria-label": $props.isOpen ? _ctx.__("Collapse") : _ctx.__("Expand"),
			"aria-expanded": $props.isOpen,
			onClick: _cache[1] || (_cache[1] = withModifiers(($event) => _ctx.$emit("toggle-open"), ["stop"]))
		}, null, 8, [
			"class",
			"aria-label",
			"aria-expanded"
		])) : createCommentVNode("", true),
		$props.page.collection && $props.editable ? (openBlock(), createElementBlock("div", _hoisted_5$1, [createVNode(_component_Icon, {
			name: "navigation",
			class: "size-3.5 text-gray-500"
		}), createBaseVNode("div", null, [
			createVNode(_component_Link, {
				href: $props.page.collection.create_url,
				textContent: toDisplayString(_ctx.__("Add")),
				class: "hover:text-ui-accent-text"
			}, null, 8, ["href", "textContent"]),
			_cache[3] || (_cache[3] = createBaseVNode("span", { class: "mx-1 text-gray-400 dark:text-gray-500" }, "/", -1)),
			createVNode(_component_Link, {
				href: $props.page.collection.edit_url,
				textContent: toDisplayString(_ctx.__("Edit")),
				class: "hover:text-ui-accent-text"
			}, null, 8, ["href", "textContent"])
		])])) : createCommentVNode("", true)
	]), createBaseVNode("div", _hoisted_6, [
		$props.showBlueprint && $props.page.entry_blueprint ? withDirectives((openBlock(), createBlock(_component_ui_badge, {
			key: 0,
			text: _ctx.__($props.page.entry_blueprint.title),
			size: "sm",
			variant: "filled"
		}, null, 8, ["text"])), [[_directive_tooltip, _ctx.__("Blueprint")]]) : createCommentVNode("", true),
		renderSlot(_ctx.$slots, "branch-icon", { branch: $props.page }),
		$props.editable ? (openBlock(), createBlock(_component_Dropdown, {
			key: 1,
			placement: "left-start",
			class: normalizeClass({ invisible: $options.isRoot })
		}, {
			default: withCtx(() => [createVNode(_component_DropdownMenu, null, {
				default: withCtx(() => [renderSlot(_ctx.$slots, "branch-options", {
					branch: $props.page,
					depth: $props.depth,
					removeBranch: $options.remove
				})]),
				_: 3
			})]),
			_: 3
		}, 8, ["class"])) : createCommentVNode("", true)
	])])], 2);
}
//#endregion
//#region resources/js/components/structures/PageTree.vue
var _sfc_main = {
	components: {
		Draggable: je,
		TreeBranch: /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "Branch.vue"]]),
		PanelHeader: Header_default,
		Panel: Panel_default,
		Icon: Icon_default
	},
	props: {
		pagesUrl: {
			type: String,
			required: true
		},
		submitUrl: { type: String },
		submitParameters: {
			type: Object,
			default: () => ({})
		},
		createUrl: { type: String },
		site: {
			type: String,
			required: true
		},
		localizations: { type: Array },
		maxDepth: {
			type: Number,
			default: Infinity
		},
		expectsRoot: {
			type: Boolean,
			required: true
		},
		showSlugs: {
			type: Boolean,
			default: false
		},
		preferencesPrefix: { type: String },
		editable: {
			type: Boolean,
			default: true
		},
		blueprints: { type: Array }
	},
	data() {
		return {
			loading: false,
			saving: false,
			pages: [],
			treeData: [],
			collapsedState: [],
			discardingChanges: false,
			ready: false
		};
	},
	computed: {
		activeLocalization() {
			return this.localizations.find((l) => l.active);
		},
		preferencesKey() {
			return this.preferencesPrefix ? `${this.preferencesPrefix}.${this.site}.pagetree` : null;
		},
		direction() {
			return this.$config.get("direction", "ltr");
		},
		treeDraggableI18n() {
			return { instructions: __("messages.tree_aria_instructions") };
		}
	},
	watch: {
		site(site) {
			this.getPages();
		},
		collapsedState: {
			deep: true,
			handler(state) {
				if (this.preferencesKey) localStorage.setItem(this.preferencesKey, JSON.stringify(state));
			}
		}
	},
	created() {
		this.collapsedState = this.getCollapsedState();
		this.getPages().then(() => {
			this.initialPages = clone(this.pages);
		});
		this.$keys.bindGlobal(["mod+s"], (e) => {
			e.preventDefault();
			this.save();
		});
	},
	mounted() {
		setTimeout(() => this.ready = true, 500);
	},
	methods: {
		isRoot(stat) {
			if (!this.expectsRoot) return false;
			return stat.level === 1 && stat.data.id === this.treeData[0]?.id;
		},
		getPages() {
			this.loading = true;
			const url = `${this.pagesUrl}?site=${this.site}`;
			return this.$axios.get(url).then((response) => {
				this.pages = response.data.pages;
				this.updateTreeData();
				this.loading = false;
				this.$emit("loaded", this.pages);
			});
		},
		treeUpdated() {
			this.pages = this.$refs.tree.getData();
			this.$emit("changed", this.pages);
		},
		afterDrop() {
			const root = this.$refs.tree.getData()[0];
			if (this.expectsRoot && root.id !== this.pages[0].id && root.children?.length > 0) {
				const { dragNode, parent, indexBeforeDrop } = st.startInfo;
				this.$refs.tree.move(dragNode, parent, indexBeforeDrop);
				return;
			}
			this.treeUpdated();
		},
		cleanPagesForSubmission(pages) {
			return pages.map((page) => ({
				id: page.id,
				children: this.cleanPagesForSubmission(page.children)
			}));
		},
		save() {
			if (!this.editable) return;
			this.saving = true;
			const payload = {
				pages: this.cleanPagesForSubmission(this.pages),
				site: this.site,
				expectsRoot: this.expectsRoot,
				...this.submitParameters
			};
			return this.$axios.patch(this.submitUrl, payload).then((response) => {
				if (!response.data.saved) return this.$toast.error(`Couldn't save tree`);
				this.$emit("saved", response);
				this.$toast.success(__("Saved"));
				this.initialPages = this.pages;
				return response;
			}).catch((e) => {
				let message = e.response ? e.response.data.message : __("Something went wrong");
				if (e.response && e.response.status === 422) {
					const { errors } = e.response.data;
					message = errors[Object.keys(errors)[0]][0];
				}
				this.$toast.error(message);
				return Promise.reject(e);
			}).finally(() => this.saving = false);
		},
		addPages(pages, targetParent) {
			this.$refs.tree.addMulti(pages, targetParent);
			this.treeUpdated();
		},
		updateTreeData() {
			this.treeData = [...this.pages];
		},
		pageRemoved(stat, deleteChildren) {
			const children = [];
			if (!deleteChildren) stat.children.forEach((child) => children.push({ ...child.data }));
			this.$refs.tree.batchUpdate(() => {
				this.$refs.tree.remove(stat);
				if (children.length) this.$refs.tree.addMulti(children, stat.parent);
			});
			this.treeUpdated();
		},
		localizationSelected(localization) {
			if (localization.active) return;
			if (localization.exists) this.editLocalization(localization);
			else this.createLocalization(localization);
		},
		editLocalization(localization) {
			router.get(localization.url);
		},
		createLocalization(localization) {
			console.log("todo.");
		},
		cancel() {
			this.discardingChanges = true;
		},
		confirmDiscard() {
			this.pages = this.initialPages;
			this.updateTreeData();
			this.$emit("canceled");
			this.discardingChanges = false;
		},
		eachDroppable(targetStat) {
			if (!this.expectsRoot) return true;
			return !this.isRoot(targetStat);
		},
		pageUpdated() {
			this.pages = this.$refs.tree.getData();
			this.$emit("changed", this.pages);
		},
		expandAll() {
			this.$refs.tree.openAll();
			this.collapsedState = [];
		},
		collapseAll() {
			this.$refs.tree.closeAll();
			this.collapsedState = [];
			walkTreeData(this.treeData, (node) => {
				this.collapsedState.push(node.id);
			});
		},
		getNodeByBranchId(id) {
			let branch;
			walkTreeData(this.treeData, (node) => {
				if (node.id === id) {
					branch = node;
					return false;
				}
			});
			return branch;
		},
		getCollapsedState() {
			if (!this.preferencesKey) return [];
			return JSON.parse(localStorage.getItem(this.preferencesKey) || "[]");
		},
		nodeOpened(node) {
			this.collapsedState.splice(this.collapsedState.indexOf(node.data.id), 1);
		},
		nodeClosed(node) {
			this.collapsedState.push(node.data.id);
		},
		statHandler(stat) {
			stat.open = !this.collapsedState.includes(stat.data.id);
			return stat;
		}
	}
};
var _hoisted_1 = {
	key: 0,
	class: "no-results flex w-full items-center"
};
var _hoisted_2 = {
	key: 0,
	class: "loading card"
};
var _hoisted_3 = { class: "page-tree-header font-medium text-sm items-center flex justify-between" };
var _hoisted_4 = ["textContent"];
var _hoisted_5 = { class: "flex gap-2" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Icon = resolveComponent("Icon");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_ui_panel_header = resolveComponent("ui-panel-header");
	const _component_tree_branch = resolveComponent("tree-branch");
	const _component_Draggable = resolveComponent("Draggable");
	const _component_ui_panel = resolveComponent("ui-panel");
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	return openBlock(), createElementBlock("div", null, [
		!$data.loading && $data.treeData.length == 0 ? (openBlock(), createElementBlock("div", _hoisted_1, [renderSlot(_ctx.$slots, "empty")])) : createCommentVNode("", true),
		withDirectives(createVNode(_component_ui_panel, null, {
			default: withCtx(() => [
				$data.loading ? (openBlock(), createElementBlock("div", _hoisted_2, [createVNode(_component_Icon, { name: "loading" })])) : createCommentVNode("", true),
				createVNode(_component_ui_panel_header, null, {
					default: withCtx(() => [createBaseVNode("div", _hoisted_3, [createBaseVNode("div", { textContent: toDisplayString(_ctx.__("Tree Structure")) }, null, 8, _hoisted_4), createBaseVNode("div", _hoisted_5, [createVNode(_component_ui_button, {
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
				!$data.loading ? (openBlock(), createElementBlock("div", {
					key: 1,
					class: normalizeClass(["page-tree", { "page-tree--ready": $data.ready }])
				}, [createVNode(_component_Draggable, {
					ref: "tree",
					modelValue: $data.treeData,
					"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.treeData = $event),
					"disable-drag": !$props.editable,
					space: 1,
					indent: 24,
					dir: $options.direction,
					"node-key": (stat) => stat.data.id,
					dragOverThrottleInterval: 30,
					"each-droppable": $options.eachDroppable,
					"max-level": $props.maxDepth,
					"stat-handler": $options.statHandler,
					i18n: $options.treeDraggableI18n,
					"aria-label": _ctx.__("Tree Structure"),
					onAfterDrop: $options.afterDrop,
					"onOpen:node": $options.nodeOpened,
					"onClose:node": $options.nodeClosed
				}, {
					placeholder: withCtx(() => [..._cache[2] || (_cache[2] = [createBaseVNode("div", { class: "w-full rounded-sm border border-dashed border-blue-400 bg-blue-500/10 p-2" }, "\xA0", -1)])]),
					default: withCtx(({ node, stat }) => [createVNode(_component_tree_branch, {
						ref: `branch-${node.id}`,
						page: node,
						stat,
						depth: stat.level,
						"first-page-is-root": $props.expectsRoot,
						"is-open": stat.open,
						"has-children": stat.children.length > 0,
						"show-slugs": $props.showSlugs,
						"show-blueprint": $props.blueprints?.length > 1,
						editable: $props.editable,
						root: $options.isRoot(stat),
						onEdit: ($event) => _ctx.$emit("edit-page", node, $event),
						onToggleOpen: ($event) => stat.open = !stat.open,
						onRemoved: $options.pageRemoved,
						onBranchClicked: ($event) => _ctx.$emit("branch-clicked", node),
						class: "mb-px"
					}, {
						"branch-action": withCtx((props) => [renderSlot(_ctx.$slots, "branch-action", normalizeProps(guardReactiveProps({
							...props,
							stat
						})))]),
						"branch-icon": withCtx((props) => [renderSlot(_ctx.$slots, "branch-icon", normalizeProps(guardReactiveProps({
							...props,
							stat
						})))]),
						"branch-options": withCtx((props) => [renderSlot(_ctx.$slots, "branch-options", normalizeProps(guardReactiveProps({
							...props,
							stat
						})))]),
						_: 2
					}, 1032, [
						"page",
						"stat",
						"depth",
						"first-page-is-root",
						"is-open",
						"has-children",
						"show-slugs",
						"show-blueprint",
						"editable",
						"root",
						"onEdit",
						"onToggleOpen",
						"onRemoved",
						"onBranchClicked"
					])]),
					_: 3
				}, 8, [
					"modelValue",
					"disable-drag",
					"dir",
					"node-key",
					"each-droppable",
					"max-level",
					"stat-handler",
					"i18n",
					"aria-label",
					"onAfterDrop",
					"onOpen:node",
					"onClose:node"
				])], 2)) : createCommentVNode("", true)
			]),
			_: 3
		}, 512), [[vShow, $data.treeData.length]]),
		createVNode(_component_confirmation_modal, {
			open: $data.discardingChanges,
			title: _ctx.__("Discard Changes"),
			"body-text": _ctx.__("Are you sure?"),
			"button-text": _ctx.__("Discard Changes"),
			danger: true,
			onConfirm: $options.confirmDiscard,
			onCancel: _cache[1] || (_cache[1] = ($event) => $data.discardingChanges = false)
		}, null, 8, [
			"open",
			"title",
			"body-text",
			"button-text",
			"onConfirm"
		])
	]);
}
var PageTree_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "PageTree.vue"]]);
//#endregion
export { PageTree_default as default };
