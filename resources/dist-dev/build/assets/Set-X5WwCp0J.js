import { n as __exportAll } from "./rolldown-runtime-B-1-B7_t.js";
import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, E as getCurrentInstance, Et as camelize, F as onBeforeUnmount, H as provide, K as resolveComponent, L as onMounted, N as nextTick, O as h, S as createTextVNode, T as defineComponent, Tt as unref, W as renderList, _ as createBlock, _t as ref, a as render, at as withDirectives, bt as toRaw, c as vShow, ct as customRef, et as watch, f as Fragment, g as createBaseVNode, ht as reactive, it as withCtx, pt as markRaw, q as resolveDirective, tt as watchEffect, u as withModifiers, v as createCommentVNode, y as createElementBlock, yt as shallowRef } from "./vue.esm-bundler-FaDyk5AC.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { $t as Item_default, Di as Icon_default, Jt as Separator_default, U as Fields_default, Yn as Subheading_default, Zt as Menu_default, en as Dropdown_default, mi as Badge_default, mt as Switch_default, pi as Button_default, q as FieldsProvider_default, vn as useUiDirection, yn as containerContextKey } from "./ui-BUQe37jk.js";
import { S as reveal } from "./api-wG-DH_vw.js";
import { f as ManagesPreviewText_default, y as HasFieldActions_default } from "./index-DP5MoZk7.js";
import { $ as generateJSON, $t as isProseMirrorNodeSelection, A as createChainableState, An as resolveFocusPosition, At as isActive, Bn as wrappingInputRule, Bt as isMarkActive, C as attrsEqual, Cn as parseAttributes, Ct as getTextBetween, D as commands_exports, Dn as removeDuplicates, Dt as h$1, E as combineTransactionSteps, En as posToDOMRect, Et as getUpdatedPosition, F as createStyleTag, Fn as splitExtensions, Ft as isExtensionRulesEnabled, G as findChildren, Gt as isNumber, H as encodeHtmlEntities, Ht as isNodeEmpty, I as createWidgetDecoration, In as textInputRule, It as isFirefox, J as findParentNode, Jt as isProseMirrorAddNodeMarkStep, K as findChildrenInRange, Kt as isPlainObject, L as decodeHtmlEntities, Ln as textPasteRule, Lt as isFunction, M as createInlineMarkdownSpec, Mn as selectionToInsertionEnd, Mt as isAtEndOfNode, N as createMappablePosition, Nn as serializeAttributes, Nt as isAtStartOfNode, O as createAtomBlockMarkdownSpec, On as renderNestedMarkdownContent, Ot as injectExtensionAttributesToParseRule, P as createNodeFromContent, Pn as sortExtensions, Pt as isEmptyObject, Q as generateHTML, Qt as isProseMirrorFragment, R as defaultBlockAt, Rn as textblockTypeInputRule, Rt as isList, S as WidgetDecoration, Sn as objectIncludes, St as getText, T as canInsertNode, Tn as pasteRulesPlugin, Tt as getTextSerializersFromSchema, U as escapeForRegEx, Ut as isNodeSelection, V as elementFromString, Vt as isNodeActive, W as extensions_exports, Wt as isNodeViewSelected, X as flattenExtensions, Xt as isProseMirrorCellSelection, Y as findParentNodeClosestToPos, Yt as isProseMirrorAttrStep, Z as fromString, Zt as isProseMirrorDocAttrStep, _ as NodeView, _n as mergeAttributes, _t as getSchemaByResolvedExtensions, a as Editor$1, an as isProseMirrorStep, at as getExtensionField, b as ResizableNodeview, bn as nodeInputRule, bt as getSplittedAttributes, c as Fragment$1, cn as isSafari, ct as getMarkRange, d as MappablePosition, dn as isiOS, dt as getNodeAtPosition, en as isProseMirrorRemoveMarkStep, et as generateText, f as Mark, fn as liveWidgetKeys, ft as getNodeAttributes, g as NodePos, gn as marksEqual, gt as getSchema, h as NodeDecoration, hn as markdown_exports, ht as getRenderedAttributes, i as DecorationManager, in as isProseMirrorSlice, it as getDebugJSON, j as createDocument, jn as rewriteUnknownContent, jt as isAndroid, k as createBlockMarkdownSpec, kn as resolveExtensions, kt as inputRulesPlugin, l as InlineDecoration, ln as isString, lt as getMarkType, m as Node, mn as markPasteRule, mt as getPreviousBlockSibling, n as DECORATION_MANAGER_PLUGIN_KEY, nn as isProseMirrorReplaceAroundStep, nt as getAttributesFromExtensions, o as Extendable, on as isProseMirrorStepResult, ot as getHTMLFromFragment, p as MarkView, pn as markInputRule, pt as getNodeType, q as findDuplicates, qt as isProseMirrorAddMarkStep, r as Decoration, rn as isProseMirrorReplaceStep, rt as getChangedRanges, s as Extension, sn as isRegExp, st as getMarkAttributes, t as CommandManager, tn as isProseMirrorRemoveNodeMarkStep, tt as getAttributes, u as InputRule, un as isTextSelection, ut as getMarksBetween, v as PasteRule, vn as mergeDeep, vt as getSchemaTypeByName, w as callOrReturn, wn as parseIndentedBlocks, wt as getTextContentFromNodes, x as Tracker, xn as nodePasteRule, xt as getStyleProperty, y as ResizableNodeView, yn as minMax, yt as getSchemaTypeNameByName, z as deleteProps, zn as updateMarkViewAttributes, zt as isMacOS } from "./dist-DVqm2P-T.js";
//#region node_modules/@tiptap/vue-3/dist/index.js
var dist_exports = /* @__PURE__ */ __exportAll({
	CommandManager: () => CommandManager,
	DECORATION_MANAGER_PLUGIN_KEY: () => DECORATION_MANAGER_PLUGIN_KEY,
	Decoration: () => Decoration,
	DecorationManager: () => DecorationManager,
	Editor: () => Editor,
	EditorContent: () => EditorContent,
	Extendable: () => Extendable,
	Extension: () => Extension,
	Fragment: () => Fragment$1,
	InlineDecoration: () => InlineDecoration,
	InputRule: () => InputRule,
	MappablePosition: () => MappablePosition,
	Mark: () => Mark,
	MarkView: () => MarkView,
	MarkViewContent: () => MarkViewContent,
	Node: () => Node,
	NodeDecoration: () => NodeDecoration,
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
	VueWidgetRenderer: () => VueWidgetRenderer,
	WidgetDecoration: () => WidgetDecoration,
	attrsEqual: () => attrsEqual,
	callOrReturn: () => callOrReturn,
	canInsertNode: () => canInsertNode,
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
	createWidgetDecoration: () => createWidgetDecoration,
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
	getPreviousBlockSibling: () => getPreviousBlockSibling,
	getRenderedAttributes: () => getRenderedAttributes,
	getSchema: () => getSchema,
	getSchemaByResolvedExtensions: () => getSchemaByResolvedExtensions,
	getSchemaTypeByName: () => getSchemaTypeByName,
	getSchemaTypeNameByName: () => getSchemaTypeNameByName,
	getSplittedAttributes: () => getSplittedAttributes,
	getStyleProperty: () => getStyleProperty,
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
	isNodeViewSelected: () => isNodeViewSelected,
	isNumber: () => isNumber,
	isPlainObject: () => isPlainObject,
	isProseMirrorAddMarkStep: () => isProseMirrorAddMarkStep,
	isProseMirrorAddNodeMarkStep: () => isProseMirrorAddNodeMarkStep,
	isProseMirrorAttrStep: () => isProseMirrorAttrStep,
	isProseMirrorCellSelection: () => isProseMirrorCellSelection,
	isProseMirrorDocAttrStep: () => isProseMirrorDocAttrStep,
	isProseMirrorFragment: () => isProseMirrorFragment,
	isProseMirrorNodeSelection: () => isProseMirrorNodeSelection,
	isProseMirrorRemoveMarkStep: () => isProseMirrorRemoveMarkStep,
	isProseMirrorRemoveNodeMarkStep: () => isProseMirrorRemoveNodeMarkStep,
	isProseMirrorReplaceAroundStep: () => isProseMirrorReplaceAroundStep,
	isProseMirrorReplaceStep: () => isProseMirrorReplaceStep,
	isProseMirrorSlice: () => isProseMirrorSlice,
	isProseMirrorStep: () => isProseMirrorStep,
	isProseMirrorStepResult: () => isProseMirrorStepResult,
	isRegExp: () => isRegExp,
	isSafari: () => isSafari,
	isString: () => isString,
	isTextSelection: () => isTextSelection,
	isiOS: () => isiOS,
	liveWidgetKeys: () => liveWidgetKeys,
	markInputRule: () => markInputRule,
	markPasteRule: () => markPasteRule,
	markViewProps: () => markViewProps,
	markdown: () => markdown_exports,
	marksEqual: () => marksEqual,
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
				var _editor$view$dom;
				if (!rootEl.value || !((_editor$view$dom = editor.view.dom) === null || _editor$view$dom === void 0 ? void 0 : _editor$view$dom.parentNode)) return;
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
	inject: { nodeViewContentRef: { default: void 0 } },
	mounted() {
		const ref = this.nodeViewContentRef;
		if (ref && this.$el) ref(this.$el);
	},
	beforeUnmount() {
		const ref = this.nodeViewContentRef;
		if (ref) ref(null);
	},
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
		var _this$$slots$default, _this$$slots;
		return h(this.as, {
			class: this.decorationClasses,
			style: { whiteSpace: "normal" },
			"data-node-view-wrapper": "",
			onDragstart: this.onDragStart
		}, (_this$$slots$default = (_this$$slots = this.$slots).default) === null || _this$$slots$default === void 0 ? void 0 : _this$$slots$default.call(_this$$slots));
	}
});
var useEditor = (options = {}) => {
	const editor = shallowRef();
	onMounted(() => {
		editor.value = new Editor(options);
	});
	onBeforeUnmount(() => {
		var _editor$value;
		(_editor$value = editor.value) === null || _editor$value === void 0 || _editor$value.destroy();
	});
	return editor;
};
/**
* This class is used to render Vue components inside the editor.
*/
var VueRenderer = class {
	constructor(component, { props = {}, editor }) {
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
		var _this$renderedCompone, _this$renderedCompone2;
		if ((_this$renderedCompone = this.renderedComponent.vNode) === null || _this$renderedCompone === void 0 || (_this$renderedCompone = _this$renderedCompone.component) === null || _this$renderedCompone === void 0 ? void 0 : _this$renderedCompone.exposed) return this.renderedComponent.vNode.component.exposed;
		return (_this$renderedCompone2 = this.renderedComponent.vNode) === null || _this$renderedCompone2 === void 0 || (_this$renderedCompone2 = _this$renderedCompone2.component) === null || _this$renderedCompone2 === void 0 ? void 0 : _this$renderedCompone2.proxy;
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
				var _setup;
				return (_setup = component.setup) === null || _setup === void 0 ? void 0 : _setup.call(component, reactiveProps, { expose: () => void 0 });
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
	constructor(component, props, options) {
		super(component, props, options);
		this.cachedExtensionWithSyncedStorage = null;
		this.handlePositionUpdate = () => {
			const newPos = this.getPos();
			if (typeof newPos !== "number" || newPos === this.currentPos) return;
			this.currentPos = newPos;
			this.renderer.updateProps({ getPos: () => this.getPos() });
		};
		if (this.options.trackNodeViewPosition) this.editor.on("update", this.handlePositionUpdate);
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
				if (prop === "storage") {
					var _editor$storage;
					return (_editor$storage = editor.storage[extension.name]) !== null && _editor$storage !== void 0 ? _editor$storage : {};
				}
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
		const mountProps = props;
		const onDragStart = this.onDragStart.bind(this);
		this.decorationClasses = ref(this.getDecorationClasses());
		const extendedComponent = defineComponent({
			extends: { ...this.component },
			props: Object.keys(props),
			template: this.component.template,
			setup: (reactiveProps) => {
				var _setup, _ref;
				provide("onDragStart", onDragStart);
				provide("decorationClasses", this.decorationClasses);
				provide("nodeViewContentRef", (el) => {
					if (!el || el === this.contentDOMElement) return;
					if (this.contentDOMElement) while (this.contentDOMElement.firstChild) el.appendChild(this.contentDOMElement.firstChild);
					this.contentDOMElement = el;
				});
				return (_setup = (_ref = this.component).setup) === null || _setup === void 0 ? void 0 : _setup.call(_ref, reactiveProps, { expose: () => void 0 });
			},
			__scopeId: this.component.__scopeId,
			__cssModules: this.component.__cssModules,
			__name: this.component.__name,
			__file: this.component.__file
		});
		this.handleSelectionUpdate = this.handleSelectionUpdate.bind(this);
		this.editor.on("selectionUpdate", this.handleSelectionUpdate);
		this.currentPos = this.getPos();
		if (!this.node.isLeaf) {
			if (this.options.contentDOMElementTag) this.contentDOMElement = document.createElement(this.options.contentDOMElementTag);
			else this.contentDOMElement = document.createElement(this.node.isInline ? "span" : "div");
			this.contentDOMElement.style.whiteSpace = "inherit";
			this.contentDOMElement.dataset.nodeViewContentVue = "";
		}
		this.renderer = new VueRenderer(extendedComponent, {
			editor: this.editor,
			props: mountProps
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
		return this.contentDOMElement;
	}
	/**
	* On editor selection update, check if the node is selected.
	* If it is, call `selectNode`, otherwise call `deselectNode`.
	*/
	handleSelectionUpdate() {
		const pos = this.getPos();
		if (typeof pos !== "number") return;
		if (isNodeViewSelected({
			selection: this.editor.state.selection,
			pos,
			nodeSize: this.node.nodeSize,
			selectedOnTextSelection: this.options.selectedOnTextSelection
		})) {
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
		if (!(node !== this.node)) {
			this.node = node;
			this.decorations = decorations;
			this.innerDecorations = innerDecorations;
			this.decorationClasses.value = this.getDecorationClasses();
			return true;
		}
		this.node = node;
		this.decorations = decorations;
		this.innerDecorations = innerDecorations;
		this.currentPos = this.getPos();
		const extraProps = {
			node,
			decorations,
			innerDecorations,
			extension: this.extensionWithSyncedStorage
		};
		if (this.options.trackNodeViewPosition) extraProps.getPos = () => this.getPos();
		rerenderComponent(extraProps);
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
		if (this.options.trackNodeViewPosition) this.editor.off("update", this.handlePositionUpdate);
		this.contentDOMElement = null;
	}
};
function VueNodeViewRenderer(component, options) {
	return (props) => {
		if (!props.editor.contentComponent) return {};
		return new VueNodeView(typeof component === "function" && "__vccOpts" in component ? component.__vccOpts : component, props, options);
	};
}
function collectPropNames(component, names) {
	var _options$mixins;
	const options = component;
	if (!options) return;
	(_options$mixins = options.mixins) === null || _options$mixins === void 0 || _options$mixins.forEach((mixin) => collectPropNames(mixin, names));
	collectPropNames(options.extends, names);
	const { props } = options;
	if (Array.isArray(props)) props.forEach((name) => names.add(camelize(name)));
	else if (props) Object.keys(props).forEach((name) => names.add(camelize(name)));
}
/**
* Builds prop declarations for the widget props a component does not declare itself.
* Declaring them keeps Vue from rendering them as DOM attributes, while leaving
* the component's own `type`, `default` and `validator` options untouched.
*
* @param component The widget component, including its `extends` and `mixins` chain.
* @param props The props passed through `VueWidgetRenderer`.
* @returns A Vue props object for the undeclared keys only.
* @example
* undeclaredWidgetProps(MyWidget, { label: 'a' }) // => { editor: null, getPos: null }
*/
function undeclaredWidgetProps(component, props) {
	const declared = /* @__PURE__ */ new Set();
	collectPropNames(component, declared);
	const declarations = {};
	for (const name of [
		"editor",
		"getPos",
		...Object.keys(props)
	]) if (!declared.has(camelize(name))) declarations[name] = null;
	return declarations;
}
/** Keeps an array declaration an array, so Vue does not read numeric keys as prop names. */
function mergePropsOption(declared, undeclared) {
	if (Array.isArray(declared)) return [...declared, ...Object.keys(undeclared)];
	return {
		...declared,
		...undeclared
	};
}
/**
* Wraps a functional component so it can be used as a widget decoration.
*
* A functional component is its own render function, so the options wrapper
* used for object components would spread it away and leave Vue with nothing
* to render.
*
* @param component The functional component.
* @param props The props passed through `VueWidgetRenderer`.
* @returns A functional component declaring the widget props.
* @example
* wrapFunctionalWidget(props => h('span', props.label), { label: 'a' })
*/
function wrapFunctionalWidget(component, props) {
	var _component$displayNam;
	const wrapped = (componentProps, context) => component(componentProps, context);
	wrapped.props = mergePropsOption(component.props, undeclaredWidgetProps(component, props));
	wrapped.emits = component.emits;
	wrapped.inheritAttrs = component.inheritAttrs;
	wrapped.displayName = (_component$displayNam = component.displayName) !== null && _component$displayNam !== void 0 ? _component$displayNam : component.name;
	return wrapped;
}
var WIDGET_CACHE = Symbol("tiptapVueWidgetCache");
/**
* Renders a Vue component into a ProseMirror widget decoration.
* Reuses Tiptap's `VueRenderer` so the component shares the editor's app
* context (provide/inject works as usual). Use a stable `key` for stateful
* widgets. The component must render a single root element.
* @example
* addDecorations() {
*   return {
*     create: ({ editor, state }) =>
*       findMatches(state.doc).map(match =>
*         VueWidgetRenderer(MyWidget, {
*           editor, pos: match.pos, key: `match-${match.id}`,
*           props: { label: match.label },
*         }),
*       ),
*   }
* }
*/
function VueWidgetRenderer(component, options) {
	const { editor, props = {} } = options;
	const wrappedComponent = isFunctionalComponent(component) ? wrapFunctionalWidget(component, props) : buildOptionsWidget(component, props);
	return createWidgetDecoration({
		...options,
		props,
		cacheKey: WIDGET_CACHE,
		context: (getPos) => ({
			editor: markRaw(editor),
			getPos
		}),
		create: (renderProps) => new VueRenderer(wrappedComponent, {
			editor,
			props: renderProps
		}),
		materialize: (renderer) => renderer.element
	});
}
function isFunctionalComponent(component) {
	return typeof component === "function";
}
/**
* Wraps an object component so the widget props are declared on it. Everything
* Vue reads off the original options has to be carried over by hand, because
* `extends` alone does not apply to `template`, `setup` or the compiler keys.
*/
function buildOptionsWidget(component, props) {
	return defineComponent({
		extends: { ...component },
		props: undeclaredWidgetProps(component, props),
		template: component.template,
		setup: (reactiveProps, context) => {
			var _setup;
			return (_setup = component.setup) === null || _setup === void 0 ? void 0 : _setup.call(component, reactiveProps, context);
		},
		__scopeId: component.__scopeId,
		__cssModules: component.__cssModules,
		__name: component.__name,
		__file: component.__file
	});
}
//#endregion
//#region resources/js/components/fieldtypes/bard/Set.vue
var Set_exports = /* @__PURE__ */ __exportAll({ default: () => Set_default });
var _sfc_main = {
	props: nodeViewProps,
	setup() {
		return { uiDirection: useUiDirection().direction };
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
		this._draggableObserver?.disconnect();
	}
};
var _hoisted_1 = ["dir", "data-type"];
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
			dir: $setup.uiDirection,
			"data-type": $options.config.handle,
			contenteditable: "false",
			onCopy: _cache[4] || (_cache[4] = withModifiers(() => {}, ["stop"])),
			onPaste: _cache[5] || (_cache[5] = withModifiers(() => {}, ["stop"])),
			onCut: _cache[6] || (_cache[6] = withModifiers(() => {}, ["stop"])),
			onDragstart: _cache[7] || (_cache[7] = (...args) => $options.preventNodeSelectionDrag && $options.preventNodeSelectionDrag(...args)),
			onMousedown: _cache[8] || (_cache[8] = (...args) => $options.preventFormControlNodeSelection && $options.preventFormControlNodeSelection(...args))
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
