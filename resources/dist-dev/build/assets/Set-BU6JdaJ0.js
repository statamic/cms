import { n as __exportAll } from "./chunk-B-1-B7_t.js";
import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, E as getCurrentInstance, F as onBeforeUnmount, H as provide, K as resolveComponent, L as onMounted, N as nextTick, O as h, S as createTextVNode, T as defineComponent, Tt as unref, W as renderList, _ as createBlock, _t as ref, a as render, at as withDirectives, bt as toRaw, c as vShow, ct as customRef, et as watch, f as Fragment, g as createBaseVNode, ht as reactive, it as withCtx, pt as markRaw, q as resolveDirective, tt as watchEffect, u as withModifiers, v as createCommentVNode, y as createElementBlock, yt as shallowRef } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $t as Menu_default, Ci as Icon_default, G as Fields_default, Kn as Subheading_default, L as containerContextKey, Xt as Separator_default, Y as FieldsProvider_default, gt as Switch_default, li as Button_default, nn as Dropdown_default, tn as Item_default, ui as Badge_default } from "./ui-BfR7XN6t.js";
import { S as reveal } from "./api-BR1uuoCk.js";
import { f as ManagesPreviewText_default, v as HasFieldActions_default } from "./index-Bu3QBQkS.js";
import { $ as getHTMLFromFragment, $t as pasteRulesPlugin, A as decodeHtmlEntities, At as isMacOS, B as findDuplicates, Bt as isTextSelection, C as createBlockMarkdownSpec, Ct as isAtEndOfNode, D as createMappablePosition, Dt as isFirefox, E as createInlineMarkdownSpec, Et as isExtensionRulesEnabled, F as encodeHtmlEntities, Ft as isNumber, G as generateHTML, Gt as mergeAttributes, H as findParentNodeClosestToPos, Ht as markInputRule, I as escapeForRegEx, It as isPlainObject, J as getAttributes, Jt as nodeInputRule, K as generateJSON, Kt as mergeDeep, L as extensions_exports, Lt as isRegExp, M as deleteProps, Mt as isNodeActive, Nt as isNodeEmpty, O as createNodeFromContent, Ot as isFunction, P as elementFromString, Pt as isNodeSelection, Q as getExtensionField, Qt as parseIndentedBlocks, R as findChildren, Rt as isSafari, S as createAtomBlockMarkdownSpec, St as isAndroid, T as createDocument, Tt as isEmptyObject, U as flattenExtensions, Ut as markPasteRule, V as findParentNode, Vt as isiOS, W as fromString, Wt as markdown_exports, X as getChangedRanges, Xt as objectIncludes, Y as getAttributesFromExtensions, Yt as nodePasteRule, Z as getDebugJSON, Zt as parseAttributes, _ as callOrReturn, _t as getUpdatedPosition, a as Fragment6, an as rewriteUnknownContent, at as getNodeAttributes, b as combineTransactionSteps, bt as inputRulesPlugin, c as Mark, cn as serializeAttributes, ct as getSchema, d as NodePos, dn as textInputRule, dt as getSchemaTypeNameByName, en as posToDOMRect, et as getMarkAttributes, f as NodeView, fn as textPasteRule, ft as getSplittedAttributes, g as Tracker, gt as getTextSerializersFromSchema, h as ResizableNodeview, hn as wrappingInputRule, ht as getTextContentFromNodes, i as Extension, in as resolveFocusPosition, it as getNodeAtPosition, j as defaultBlockAt, jt as isMarkActive, k as createStyleTag, kt as isList, l as MarkView, ln as sortExtensions, lt as getSchemaByResolvedExtensions, m as ResizableNodeView, mn as updateMarkViewAttributes, mt as getTextBetween, n as Editor$1, nn as renderNestedMarkdownContent, nt as getMarkType, o as InputRule, on as schedulePositionCheck, ot as getNodeType, p as PasteRule, pn as textblockTypeInputRule, pt as getText, q as generateText, qt as minMax, r as Extendable, rn as resolveExtensions, rt as getMarksBetween, s as MappablePosition, sn as selectionToInsertionEnd, st as getRenderedAttributes, t as CommandManager, tn as removeDuplicates, tt as getMarkRange, u as Node3, un as splitExtensions, ut as getSchemaTypeByName, v as canInsertNode, vt as h$1, w as createChainableState, wt as isAtStartOfNode, x as commands_exports, xt as isActive, y as cancelPositionCheck, yt as injectExtensionAttributesToParseRule, z as findChildrenInRange, zt as isString } from "./dist-BPhk3Po_.js";
//#region node_modules/@tiptap/vue-3/dist/index.js
var dist_exports = /* @__PURE__ */ __exportAll({
	CommandManager: () => CommandManager,
	Editor: () => Editor,
	EditorContent: () => EditorContent,
	Extendable: () => Extendable,
	Extension: () => Extension,
	Fragment: () => Fragment6,
	InputRule: () => InputRule,
	MappablePosition: () => MappablePosition,
	Mark: () => Mark,
	MarkView: () => MarkView,
	MarkViewContent: () => MarkViewContent,
	Node: () => Node3,
	NodePos: () => NodePos,
	NodeView: () => NodeView,
	NodeViewContent: () => NodeViewContent,
	NodeViewWrapper: () => NodeViewWrapper,
	PasteRule: () => PasteRule,
	ResizableNodeView: () => ResizableNodeView,
	ResizableNodeview: () => ResizableNodeview,
	Tracker: () => Tracker,
	VueMarkView: () => VueMarkView,
	VueMarkViewRenderer: () => VueMarkViewRenderer,
	VueNodeViewRenderer: () => VueNodeViewRenderer,
	VueRenderer: () => VueRenderer,
	callOrReturn: () => callOrReturn,
	canInsertNode: () => canInsertNode,
	cancelPositionCheck: () => cancelPositionCheck,
	combineTransactionSteps: () => combineTransactionSteps,
	commands: () => commands_exports,
	createAtomBlockMarkdownSpec: () => createAtomBlockMarkdownSpec,
	createBlockMarkdownSpec: () => createBlockMarkdownSpec,
	createChainableState: () => createChainableState,
	createDocument: () => createDocument,
	createElement: () => h$1,
	createInlineMarkdownSpec: () => createInlineMarkdownSpec,
	createMappablePosition: () => createMappablePosition,
	createNodeFromContent: () => createNodeFromContent,
	createStyleTag: () => createStyleTag,
	decodeHtmlEntities: () => decodeHtmlEntities,
	defaultBlockAt: () => defaultBlockAt,
	deleteProps: () => deleteProps,
	elementFromString: () => elementFromString,
	encodeHtmlEntities: () => encodeHtmlEntities,
	escapeForRegEx: () => escapeForRegEx,
	extensions: () => extensions_exports,
	findChildren: () => findChildren,
	findChildrenInRange: () => findChildrenInRange,
	findDuplicates: () => findDuplicates,
	findParentNode: () => findParentNode,
	findParentNodeClosestToPos: () => findParentNodeClosestToPos,
	flattenExtensions: () => flattenExtensions,
	fromString: () => fromString,
	generateHTML: () => generateHTML,
	generateJSON: () => generateJSON,
	generateText: () => generateText,
	getAttributes: () => getAttributes,
	getAttributesFromExtensions: () => getAttributesFromExtensions,
	getChangedRanges: () => getChangedRanges,
	getDebugJSON: () => getDebugJSON,
	getExtensionField: () => getExtensionField,
	getHTMLFromFragment: () => getHTMLFromFragment,
	getMarkAttributes: () => getMarkAttributes,
	getMarkRange: () => getMarkRange,
	getMarkType: () => getMarkType,
	getMarksBetween: () => getMarksBetween,
	getNodeAtPosition: () => getNodeAtPosition,
	getNodeAttributes: () => getNodeAttributes,
	getNodeType: () => getNodeType,
	getRenderedAttributes: () => getRenderedAttributes,
	getSchema: () => getSchema,
	getSchemaByResolvedExtensions: () => getSchemaByResolvedExtensions,
	getSchemaTypeByName: () => getSchemaTypeByName,
	getSchemaTypeNameByName: () => getSchemaTypeNameByName,
	getSplittedAttributes: () => getSplittedAttributes,
	getText: () => getText,
	getTextBetween: () => getTextBetween,
	getTextContentFromNodes: () => getTextContentFromNodes,
	getTextSerializersFromSchema: () => getTextSerializersFromSchema,
	getUpdatedPosition: () => getUpdatedPosition,
	h: () => h$1,
	injectExtensionAttributesToParseRule: () => injectExtensionAttributesToParseRule,
	inputRulesPlugin: () => inputRulesPlugin,
	isActive: () => isActive,
	isAndroid: () => isAndroid,
	isAtEndOfNode: () => isAtEndOfNode,
	isAtStartOfNode: () => isAtStartOfNode,
	isEmptyObject: () => isEmptyObject,
	isExtensionRulesEnabled: () => isExtensionRulesEnabled,
	isFirefox: () => isFirefox,
	isFunction: () => isFunction,
	isList: () => isList,
	isMacOS: () => isMacOS,
	isMarkActive: () => isMarkActive,
	isNodeActive: () => isNodeActive,
	isNodeEmpty: () => isNodeEmpty,
	isNodeSelection: () => isNodeSelection,
	isNumber: () => isNumber,
	isPlainObject: () => isPlainObject,
	isRegExp: () => isRegExp,
	isSafari: () => isSafari,
	isString: () => isString,
	isTextSelection: () => isTextSelection,
	isiOS: () => isiOS,
	markInputRule: () => markInputRule,
	markPasteRule: () => markPasteRule,
	markViewProps: () => markViewProps,
	markdown: () => markdown_exports,
	mergeAttributes: () => mergeAttributes,
	mergeDeep: () => mergeDeep,
	minMax: () => minMax,
	nodeInputRule: () => nodeInputRule,
	nodePasteRule: () => nodePasteRule,
	nodeViewProps: () => nodeViewProps,
	objectIncludes: () => objectIncludes,
	parseAttributes: () => parseAttributes,
	parseIndentedBlocks: () => parseIndentedBlocks,
	pasteRulesPlugin: () => pasteRulesPlugin,
	posToDOMRect: () => posToDOMRect,
	removeDuplicates: () => removeDuplicates,
	renderNestedMarkdownContent: () => renderNestedMarkdownContent,
	resolveExtensions: () => resolveExtensions,
	resolveFocusPosition: () => resolveFocusPosition,
	rewriteUnknownContent: () => rewriteUnknownContent,
	schedulePositionCheck: () => schedulePositionCheck,
	selectionToInsertionEnd: () => selectionToInsertionEnd,
	serializeAttributes: () => serializeAttributes,
	sortExtensions: () => sortExtensions,
	splitExtensions: () => splitExtensions,
	textInputRule: () => textInputRule,
	textPasteRule: () => textPasteRule,
	textblockTypeInputRule: () => textblockTypeInputRule,
	updateMarkViewAttributes: () => updateMarkViewAttributes,
	useEditor: () => useEditor,
	wrappingInputRule: () => wrappingInputRule
});
function useDebouncedRef(value) {
	return customRef((track, trigger) => {
		return {
			get() {
				track();
				return value;
			},
			set(newValue) {
				value = newValue;
				requestAnimationFrame(() => {
					requestAnimationFrame(() => {
						trigger();
					});
				});
			}
		};
	});
}
var Editor = class extends Editor$1 {
	constructor(options = {}) {
		super(options);
		this.contentComponent = null;
		this.appContext = null;
		this.reactiveState = useDebouncedRef(this.view.state);
		this.reactiveExtensionStorage = useDebouncedRef(this.extensionStorage);
		this.on("beforeTransaction", ({ nextState }) => {
			this.reactiveState.value = nextState;
			this.reactiveExtensionStorage.value = this.extensionStorage;
		});
		return markRaw(this);
	}
	get state() {
		return this.reactiveState ? this.reactiveState.value : this.view.state;
	}
	get storage() {
		return this.reactiveExtensionStorage ? this.reactiveExtensionStorage.value : super.storage;
	}
	/**
	* Register a ProseMirror plugin.
	*/
	registerPlugin(plugin, handlePlugins) {
		const nextState = super.registerPlugin(plugin, handlePlugins);
		if (this.reactiveState) this.reactiveState.value = nextState;
		return nextState;
	}
	/**
	* Unregister a ProseMirror plugin.
	*/
	unregisterPlugin(nameOrPluginKey) {
		const nextState = super.unregisterPlugin(nameOrPluginKey);
		if (this.reactiveState && nextState) this.reactiveState.value = nextState;
		return nextState;
	}
};
var EditorContent = defineComponent({
	name: "EditorContent",
	props: { editor: {
		default: null,
		type: Object
	} },
	setup(props) {
		const rootEl = ref();
		const instance = getCurrentInstance();
		watchEffect(() => {
			const editor = props.editor;
			if (editor && editor.options.element && rootEl.value) nextTick(() => {
				var _a;
				if (!rootEl.value || !((_a = editor.view.dom) == null ? void 0 : _a.parentNode)) return;
				const element = unref(rootEl.value);
				rootEl.value.append(...editor.view.dom.parentNode.childNodes);
				editor.contentComponent = instance.ctx._;
				if (instance) editor.appContext = {
					...instance.appContext,
					provides: instance.provides
				};
				editor.setOptions({ element });
				editor.createNodeViews();
			});
		});
		onBeforeUnmount(() => {
			const editor = props.editor;
			if (!editor) return;
			editor.contentComponent = null;
			editor.appContext = null;
		});
		return { rootEl };
	},
	render() {
		return h("div", { ref: (el) => {
			this.rootEl = el;
		} });
	}
});
var NodeViewContent = defineComponent({
	name: "NodeViewContent",
	props: { as: {
		type: String,
		default: "div"
	} },
	render() {
		return h(this.as, {
			style: { whiteSpace: "pre-wrap" },
			"data-node-view-content": ""
		});
	}
});
var NodeViewWrapper = defineComponent({
	name: "NodeViewWrapper",
	props: { as: {
		type: String,
		default: "div"
	} },
	inject: ["onDragStart", "decorationClasses"],
	render() {
		var _a, _b;
		return h(this.as, {
			class: this.decorationClasses,
			style: { whiteSpace: "normal" },
			"data-node-view-wrapper": "",
			onDragstart: this.onDragStart
		}, (_b = (_a = this.$slots).default) == null ? void 0 : _b.call(_a));
	}
});
var useEditor = (options = {}) => {
	const editor = shallowRef();
	onMounted(() => {
		editor.value = new Editor(options);
	});
	onBeforeUnmount(() => {
		var _a, _b, _c, _d;
		const nodes = (_b = (_a = editor.value) == null ? void 0 : _a.view.dom) == null ? void 0 : _b.parentNode;
		const newEl = nodes == null ? void 0 : nodes.cloneNode(true);
		(_c = nodes == null ? void 0 : nodes.parentNode) == null || _c.replaceChild(newEl, nodes);
		(_d = editor.value) == null || _d.destroy();
	});
	return editor;
};
var VueRenderer = class {
	constructor(component, { props = {}, editor }) {
		/**
		* Flag to track if the renderer has been destroyed, preventing queued or asynchronous renders from executing after teardown.
		*/
		this.destroyed = false;
		this.editor = editor;
		this.component = markRaw(component);
		this.el = document.createElement("div");
		this.props = reactive(props);
		this.renderedComponent = this.renderComponent();
	}
	get element() {
		return this.renderedComponent.el;
	}
	get ref() {
		var _a, _b, _c, _d;
		if ((_b = (_a = this.renderedComponent.vNode) == null ? void 0 : _a.component) == null ? void 0 : _b.exposed) return this.renderedComponent.vNode.component.exposed;
		return (_d = (_c = this.renderedComponent.vNode) == null ? void 0 : _c.component) == null ? void 0 : _d.proxy;
	}
	renderComponent() {
		if (this.destroyed) return this.renderedComponent;
		let vNode = h(this.component, this.props);
		if (this.editor.appContext) vNode.appContext = this.editor.appContext;
		if (typeof document !== "undefined" && this.el) render(vNode, this.el);
		const destroy = () => {
			if (this.el) render(null, this.el);
			this.el = null;
			vNode = null;
		};
		return {
			vNode,
			destroy,
			el: this.el ? this.el.firstElementChild : null
		};
	}
	updateProps(props = {}) {
		if (this.destroyed) return;
		Object.entries(props).forEach(([key, value]) => {
			this.props[key] = value;
		});
		this.renderComponent();
	}
	destroy() {
		if (this.destroyed) return;
		this.destroyed = true;
		this.renderedComponent.destroy();
	}
};
var markViewProps = {
	editor: {
		type: Object,
		required: true
	},
	mark: {
		type: Object,
		required: true
	},
	extension: {
		type: Object,
		required: true
	},
	inline: {
		type: Boolean,
		required: true
	},
	view: {
		type: Object,
		required: true
	},
	updateAttributes: {
		type: Function,
		required: true
	},
	HTMLAttributes: {
		type: Object,
		required: true
	}
};
var MarkViewContent = defineComponent({
	name: "MarkViewContent",
	props: { as: {
		type: String,
		default: "span"
	} },
	render() {
		return h(this.as, {
			style: { whiteSpace: "inherit" },
			"data-mark-view-content": ""
		});
	}
});
var VueMarkView = class extends MarkView {
	constructor(component, props, options) {
		super(component, props, options);
		const componentProps = {
			...props,
			updateAttributes: this.updateAttributes.bind(this)
		};
		const extendedComponent = defineComponent({
			extends: { ...component },
			props: Object.keys(componentProps),
			template: this.component.template,
			setup: (reactiveProps) => {
				var _a;
				return (_a = component.setup) == null ? void 0 : _a.call(component, reactiveProps, { expose: () => void 0 });
			},
			__scopeId: component.__scopeId,
			__cssModules: component.__cssModules,
			__name: component.__name,
			__file: component.__file
		});
		this.renderer = new VueRenderer(extendedComponent, {
			editor: this.editor,
			props: componentProps
		});
	}
	get dom() {
		return this.renderer.element;
	}
	get contentDOM() {
		return this.dom.querySelector("[data-mark-view-content]");
	}
	updateAttributes(attrs) {
		const unproxiedMark = toRaw(this.mark);
		super.updateAttributes(attrs, unproxiedMark);
	}
	destroy() {
		this.renderer.destroy();
	}
};
function VueMarkViewRenderer(component, options = {}) {
	return (props) => {
		if (!props.editor.contentComponent) return {};
		return new VueMarkView(component, props, options);
	};
}
var nodeViewProps = {
	editor: {
		type: Object,
		required: true
	},
	node: {
		type: Object,
		required: true
	},
	decorations: {
		type: Object,
		required: true
	},
	selected: {
		type: Boolean,
		required: true
	},
	extension: {
		type: Object,
		required: true
	},
	getPos: {
		type: Function,
		required: true
	},
	updateAttributes: {
		type: Function,
		required: true
	},
	deleteNode: {
		type: Function,
		required: true
	},
	view: {
		type: Object,
		required: true
	},
	innerDecorations: {
		type: Object,
		required: true
	},
	HTMLAttributes: {
		type: Object,
		required: true
	}
};
var VueNodeView = class extends NodeView {
	constructor() {
		super(...arguments);
		/**
		* Callback registered with the per-editor position-update registry.
		* Stored so it can be unregistered in destroy().
		*/
		this.positionCheckCallback = null;
		this.cachedExtensionWithSyncedStorage = null;
	}
	/**
	* Returns a proxy of the extension that redirects storage access to the editor's mutable storage.
	* This preserves the original prototype chain (instanceof checks, methods like configure/extend work).
	* Cached to avoid proxy creation on every update.
	*/
	get extensionWithSyncedStorage() {
		if (!this.cachedExtensionWithSyncedStorage) {
			const editor = this.editor;
			const extension = this.extension;
			this.cachedExtensionWithSyncedStorage = new Proxy(extension, { get(target, prop, receiver) {
				var _a;
				if (prop === "storage") return (_a = editor.storage[extension.name]) != null ? _a : {};
				return Reflect.get(target, prop, receiver);
			} });
		}
		return this.cachedExtensionWithSyncedStorage;
	}
	mount() {
		const props = {
			editor: this.editor,
			node: this.node,
			decorations: this.decorations,
			innerDecorations: this.innerDecorations,
			view: this.view,
			selected: false,
			extension: this.extensionWithSyncedStorage,
			HTMLAttributes: this.HTMLAttributes,
			getPos: () => this.getPos(),
			updateAttributes: (attributes = {}) => this.updateAttributes(attributes),
			deleteNode: () => this.deleteNode()
		};
		const onDragStart = this.onDragStart.bind(this);
		this.decorationClasses = ref(this.getDecorationClasses());
		const extendedComponent = defineComponent({
			extends: { ...this.component },
			props: Object.keys(props),
			template: this.component.template,
			setup: (reactiveProps) => {
				var _a, _b;
				provide("onDragStart", onDragStart);
				provide("decorationClasses", this.decorationClasses);
				return (_b = (_a = this.component).setup) == null ? void 0 : _b.call(_a, reactiveProps, { expose: () => void 0 });
			},
			__scopeId: this.component.__scopeId,
			__cssModules: this.component.__cssModules,
			__name: this.component.__name,
			__file: this.component.__file
		});
		this.handleSelectionUpdate = this.handleSelectionUpdate.bind(this);
		this.editor.on("selectionUpdate", this.handleSelectionUpdate);
		this.currentPos = this.getPos();
		this.positionCheckCallback = () => {
			if (!this.renderer) return;
			const newPos = this.getPos();
			if (typeof newPos !== "number" || newPos === this.currentPos) return;
			this.currentPos = newPos;
			this.renderer.updateProps({ getPos: () => this.getPos() });
		};
		schedulePositionCheck(this.editor, this.positionCheckCallback);
		this.renderer = new VueRenderer(extendedComponent, {
			editor: this.editor,
			props
		});
	}
	/**
	* Return the DOM element.
	* This is the element that will be used to display the node view.
	*/
	get dom() {
		if (!this.renderer.element || !this.renderer.element.hasAttribute("data-node-view-wrapper")) throw Error("Please use the NodeViewWrapper component for your node view.");
		return this.renderer.element;
	}
	/**
	* Return the content DOM element.
	* This is the element that will be used to display the rich-text content of the node.
	*/
	get contentDOM() {
		if (this.node.isLeaf) return null;
		return this.dom.querySelector("[data-node-view-content]");
	}
	/**
	* On editor selection update, check if the node is selected.
	* If it is, call `selectNode`, otherwise call `deselectNode`.
	*/
	handleSelectionUpdate() {
		const { from, to } = this.editor.state.selection;
		const pos = this.getPos();
		if (typeof pos !== "number") return;
		if (from <= pos && to >= pos + this.node.nodeSize) {
			if (this.renderer.props.selected) return;
			this.selectNode();
		} else {
			if (!this.renderer.props.selected) return;
			this.deselectNode();
		}
	}
	/**
	* On update, update the React component.
	* To prevent unnecessary updates, the `update` option can be used.
	*/
	update(node, decorations, innerDecorations) {
		const rerenderComponent = (props) => {
			this.decorationClasses.value = this.getDecorationClasses();
			this.renderer.updateProps(props);
		};
		if (typeof this.options.update === "function") {
			const oldNode = this.node;
			const oldDecorations = this.decorations;
			const oldInnerDecorations = this.innerDecorations;
			this.node = node;
			this.decorations = decorations;
			this.innerDecorations = innerDecorations;
			this.currentPos = this.getPos();
			return this.options.update({
				oldNode,
				oldDecorations,
				newNode: node,
				newDecorations: decorations,
				oldInnerDecorations,
				innerDecorations,
				updateProps: () => rerenderComponent({
					node,
					decorations,
					innerDecorations,
					extension: this.extensionWithSyncedStorage
				})
			});
		}
		if (node.type !== this.node.type) return false;
		const newPos = this.getPos();
		if (node === this.node && this.decorations === decorations && this.innerDecorations === innerDecorations) {
			if (newPos === this.currentPos) return true;
			this.currentPos = newPos;
			rerenderComponent({
				node,
				decorations,
				innerDecorations,
				extension: this.extensionWithSyncedStorage,
				getPos: () => this.getPos()
			});
			return true;
		}
		this.node = node;
		this.decorations = decorations;
		this.innerDecorations = innerDecorations;
		this.currentPos = newPos;
		rerenderComponent({
			node,
			decorations,
			innerDecorations,
			extension: this.extensionWithSyncedStorage
		});
		return true;
	}
	/**
	* Select the node.
	* Add the `selected` prop and the `ProseMirror-selectednode` class.
	*/
	selectNode() {
		this.renderer.updateProps({ selected: true });
		if (this.renderer.element) this.renderer.element.classList.add("ProseMirror-selectednode");
	}
	/**
	* Deselect the node.
	* Remove the `selected` prop and the `ProseMirror-selectednode` class.
	*/
	deselectNode() {
		this.renderer.updateProps({ selected: false });
		if (this.renderer.element) this.renderer.element.classList.remove("ProseMirror-selectednode");
	}
	getDecorationClasses() {
		return this.decorations.flatMap((item) => item.type.attrs.class).join(" ");
	}
	destroy() {
		this.renderer.destroy();
		this.editor.off("selectionUpdate", this.handleSelectionUpdate);
		if (this.positionCheckCallback) {
			cancelPositionCheck(this.editor, this.positionCheckCallback);
			this.positionCheckCallback = null;
		}
	}
};
function VueNodeViewRenderer(component, options) {
	return (props) => {
		if (!props.editor.contentComponent) return {};
		return new VueNodeView(typeof component === "function" && "__vccOpts" in component ? component.__vccOpts : component, props, options);
	};
}
//#endregion
//#region resources/js/components/fieldtypes/bard/Set.vue
var Set_exports = /* @__PURE__ */ __exportAll({ default: () => Set_default });
var _sfc_main = {
	props: nodeViewProps,
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
		this._draggableObserver?.disconnect();
	}
};
var _hoisted_1 = ["data-type"];
var _hoisted_2 = {
	ref: "content",
	hidden: ""
};
var _hoisted_3 = {
	key: 0,
	class: "flex items-center gap-2"
};
var _hoisted_4 = {
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
				"st-set-is-selected [&:not(:has(:focus-within))]:border-blue-400! [&:not(:has(:focus-within))]:dark:border-blue-400! [&:not(:has(:focus-within))]:before:content-[''] [&:not(:has(:focus-within))]:before:absolute [&:not(:has(:focus-within))]:before:inset-[-1px] [&:not(:has(:focus-within))]:before:pointer-events-none [&:not(:has(:focus-within))]:before:border-2 [&:not(:has(:focus-within))]:before:border-blue-400 [&:not(:has(:focus-within))]:dark:before:border-blue-400 [&:not(:has(:focus-within))]:before:rounded-lg": $options.showSelectionHighlight,
				"border-red-500": $options.hasError
			}]),
			"data-type": $options.config.handle,
			contenteditable: "false",
			onCopy: _cache[4] || (_cache[4] = withModifiers(() => {}, ["stop"])),
			onPaste: _cache[5] || (_cache[5] = withModifiers(() => {}, ["stop"])),
			onCut: _cache[6] || (_cache[6] = withModifiers(() => {}, ["stop"]))
		}, [
			createBaseVNode("div", _hoisted_2, null, 512),
			createBaseVNode("header", { class: normalizeClass(["group/header animate-border-color show-focus-within flex items-center rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 bg-gray-100/50 dark:bg-gray-925 hover:bg-gray-100 dark:hover:bg-gray-950/45 border-gray-300 dark:shadow-md", { "bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none": !$options.collapsed && $options.hasFields }]) }, [
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
						default: withCtx(() => [$options.isSetGroupVisible ? (openBlock(), createElementBlock("span", _hoisted_3, [createTextVNode(toDisplayString(_ctx.__($options.setGroup.display)) + " ", 1), createVNode(_component_Icon, {
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
				!$options.isReadOnly ? (openBlock(), createElementBlock("div", _hoisted_4, [withDirectives(createVNode(_component_Switch, {
					size: "xs",
					modelValue: $options.enabled,
					"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $options.enabled = $event)
				}, null, 8, ["modelValue"]), [[_directive_tooltip, $options.enabled ? _ctx.__("Included in output") : _ctx.__("Hidden from output")]]), createVNode(_component_Dropdown, null, {
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
				})])) : createCommentVNode("", true)
			], 2),
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
export { NodeViewContent as a, dist_exports as c, EditorContent as i, Set_exports as n, NodeViewWrapper as o, Editor as r, VueNodeViewRenderer as s, Set_default as t };
