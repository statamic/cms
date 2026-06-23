import { r as __toESM, t as __commonJSMin } from "./chunk-B-1-B7_t.js";
import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, at as withDirectives, c as vShow, f as Fragment, g as createBaseVNode, it as withCtx, kt as normalizeStyle, pt as markRaw, q as resolveDirective, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { An as require_matchbrackets, Cn as require_javascript, Dn as require_clike, En as require_xml, H as throttle_default, Mt as Stack_default, On as require_css, Rn as require_codemirror, Sn as require_htmlmixed, Tn as require_markdown, bn as require_yaml, kn as require_sublime, li as Button_default, nt as Group_default, tt as Item_default, wn as require_gfm, xn as require_php } from "./ui-BfR7XN6t.js";
import { g as purify, l as _Renderer, u as marked } from "./globals-CR4DMcIG.js";
import { F as Uploader_default, P as Uploads_default, _ as Fieldtype_default, d as availableButtons, h as Selector_default } from "./index-Bu3QBQkS.js";
//#region node_modules/@davidenke/marked-text-renderer/dist/index.js
var TextRenderer = class extends _Renderer {
	#fancyMode = false;
	constructor(fancyMode = false, options) {
		super(options);
		this.#fancyMode = Boolean(fancyMode);
	}
	code(tokens) {
		if (!this.#fancyMode) return tokens.text;
		let output = "";
		if (tokens.lang) output = `${tokens.lang}:\n`;
		tokens.text.split("\n").forEach((line) => {
			output = `${output}\t${line}\n`;
		});
		return output;
	}
	blockquote({ tokens }) {
		const quote = this.parser.parse(tokens);
		let parsedQuote = quote;
		if (parsedQuote.endsWith("\n")) parsedQuote = parsedQuote.slice(0, -1);
		if (!this.#fancyMode) return `"${quote}"\n`;
		return `\n\t"${quote.split("\n").map((line, i) => {
			if (i === 0) return line;
			if (line === "	") return "";
			if (line === "") return "";
			return `\t${line}`;
		}).join("\n")}"\n`;
	}
	html() {
		return "";
	}
	heading(tokens) {
		const text = tokens.text;
		if (this.#fancyMode) {
			if (tokens.depth === 1) return `\n${text}\n\n`;
			else if (tokens.depth === 2) return `\n${text}\n`;
		}
		return `${text}\n`;
	}
	hr() {
		if (!this.#fancyMode) return "\n";
		return `${"-".repeat(25)}\n`;
	}
	list({ items }) {
		return `${items.map((item) => this.listitem(item)).join("\n")}\n`;
	}
	listitem(tokens) {
		return `- ${tokens.text}`;
	}
	checkbox(tokens) {
		if (!this.#fancyMode) return "";
		return tokens.checked ? "[x]\n" : "[ ]\n";
	}
	paragraph({ tokens }) {
		return `${this.parser.parseInline(tokens)}\n`;
	}
	table(tokens) {
		return `${tokens.header}\n${tokens.rows.flatMap((cells) => cells.map((cell) => cell.text)).join("\n")}\n`;
	}
	tablerow(tokens) {
		if (!this.#fancyMode) return `\n${tokens.text}\n`;
		return tokens.text.slice(1) + " |\n";
	}
	tablecell(tokens) {
		if (!this.#fancyMode) return tokens.text;
		return ` | ${tokens.text}`;
	}
	strong(tokens) {
		if (!this.#fancyMode) return tokens.text;
		return tokens.text.toUpperCase();
	}
	em({ tokens }) {
		if (!this.#fancyMode) return this.parser.parseInline(tokens);
		return `*${this.parser.parseInline(tokens)}*`;
	}
	codespan(tokens) {
		if (!this.#fancyMode) return tokens.text;
		return `\`${tokens.text}\``;
	}
	br() {
		return "\n";
	}
	del(tokens) {
		if (!this.#fancyMode) return tokens.text;
		return `~${tokens.text}~`;
	}
	link(tokens) {
		if (!this.#fancyMode) return tokens.text;
		const { href, title, text } = tokens;
		return `${text} (${title ? `${title} ` : ""}${href})`;
	}
	image(tokens) {
		if (!this.#fancyMode) return tokens.text;
		const { href, title, text } = tokens;
		return `${text}(${title ? `${title} ` : ""}${href})`;
	}
	text(tokens) {
		return tokens.text;
	}
};
//#endregion
//#region node_modules/codemirror/addon/edit/closebrackets.js
var require_closebrackets = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	(function(mod) {
		if (typeof exports == "object" && typeof module == "object") mod(require_codemirror());
		else if (typeof define == "function" && define.amd) define(["../../lib/codemirror"], mod);
		else mod(CodeMirror);
	})(function(CodeMirror) {
		var defaults = {
			pairs: "()[]{}''\"\"",
			closeBefore: ")]}'\":;>",
			triples: "",
			explode: "[]{}"
		};
		var Pos = CodeMirror.Pos;
		CodeMirror.defineOption("autoCloseBrackets", false, function(cm, val, old) {
			if (old && old != CodeMirror.Init) {
				cm.removeKeyMap(keyMap);
				cm.state.closeBrackets = null;
			}
			if (val) {
				ensureBound(getOption(val, "pairs"));
				cm.state.closeBrackets = val;
				cm.addKeyMap(keyMap);
			}
		});
		function getOption(conf, name) {
			if (name == "pairs" && typeof conf == "string") return conf;
			if (typeof conf == "object" && conf[name] != null) return conf[name];
			return defaults[name];
		}
		var keyMap = {
			Backspace: handleBackspace,
			Enter: handleEnter
		};
		function ensureBound(chars) {
			for (var i = 0; i < chars.length; i++) {
				var ch = chars.charAt(i), key = "'" + ch + "'";
				if (!keyMap[key]) keyMap[key] = handler(ch);
			}
		}
		ensureBound(defaults.pairs + "`");
		function handler(ch) {
			return function(cm) {
				return handleChar(cm, ch);
			};
		}
		function getConfig(cm) {
			var deflt = cm.state.closeBrackets;
			if (!deflt || deflt.override) return deflt;
			return cm.getModeAt(cm.getCursor()).closeBrackets || deflt;
		}
		function handleBackspace(cm) {
			var conf = getConfig(cm);
			if (!conf || cm.getOption("disableInput")) return CodeMirror.Pass;
			var pairs = getOption(conf, "pairs");
			var ranges = cm.listSelections();
			for (var i = 0; i < ranges.length; i++) {
				if (!ranges[i].empty()) return CodeMirror.Pass;
				var around = charsAround(cm, ranges[i].head);
				if (!around || pairs.indexOf(around) % 2 != 0) return CodeMirror.Pass;
			}
			for (var i = ranges.length - 1; i >= 0; i--) {
				var cur = ranges[i].head;
				cm.replaceRange("", Pos(cur.line, cur.ch - 1), Pos(cur.line, cur.ch + 1), "+delete");
			}
		}
		function handleEnter(cm) {
			var conf = getConfig(cm);
			var explode = conf && getOption(conf, "explode");
			if (!explode || cm.getOption("disableInput")) return CodeMirror.Pass;
			var ranges = cm.listSelections();
			for (var i = 0; i < ranges.length; i++) {
				if (!ranges[i].empty()) return CodeMirror.Pass;
				var around = charsAround(cm, ranges[i].head);
				if (!around || explode.indexOf(around) % 2 != 0) return CodeMirror.Pass;
			}
			cm.operation(function() {
				var linesep = cm.lineSeparator() || "\n";
				cm.replaceSelection(linesep + linesep, null);
				moveSel(cm, -1);
				ranges = cm.listSelections();
				for (var i = 0; i < ranges.length; i++) {
					var line = ranges[i].head.line;
					cm.indentLine(line, null, true);
					cm.indentLine(line + 1, null, true);
				}
			});
		}
		function moveSel(cm, dir) {
			var newRanges = [], ranges = cm.listSelections(), primary = 0;
			for (var i = 0; i < ranges.length; i++) {
				var range = ranges[i];
				if (range.head == cm.getCursor()) primary = i;
				var pos = range.head.ch || dir > 0 ? {
					line: range.head.line,
					ch: range.head.ch + dir
				} : { line: range.head.line - 1 };
				newRanges.push({
					anchor: pos,
					head: pos
				});
			}
			cm.setSelections(newRanges, primary);
		}
		function contractSelection(sel) {
			var inverted = CodeMirror.cmpPos(sel.anchor, sel.head) > 0;
			return {
				anchor: new Pos(sel.anchor.line, sel.anchor.ch + (inverted ? -1 : 1)),
				head: new Pos(sel.head.line, sel.head.ch + (inverted ? 1 : -1))
			};
		}
		function handleChar(cm, ch) {
			var conf = getConfig(cm);
			if (!conf || cm.getOption("disableInput")) return CodeMirror.Pass;
			var pairs = getOption(conf, "pairs");
			var pos = pairs.indexOf(ch);
			if (pos == -1) return CodeMirror.Pass;
			var closeBefore = getOption(conf, "closeBefore");
			var triples = getOption(conf, "triples");
			var identical = pairs.charAt(pos + 1) == ch;
			var ranges = cm.listSelections();
			var opening = pos % 2 == 0;
			var type;
			for (var i = 0; i < ranges.length; i++) {
				var range = ranges[i], cur = range.head, curType;
				var next = cm.getRange(cur, Pos(cur.line, cur.ch + 1));
				if (opening && !range.empty()) curType = "surround";
				else if ((identical || !opening) && next == ch) if (identical && stringStartsAfter(cm, cur)) curType = "both";
				else if (triples.indexOf(ch) >= 0 && cm.getRange(cur, Pos(cur.line, cur.ch + 3)) == ch + ch + ch) curType = "skipThree";
				else curType = "skip";
				else if (identical && cur.ch > 1 && triples.indexOf(ch) >= 0 && cm.getRange(Pos(cur.line, cur.ch - 2), cur) == ch + ch) {
					if (cur.ch > 2 && /\bstring/.test(cm.getTokenTypeAt(Pos(cur.line, cur.ch - 2)))) return CodeMirror.Pass;
					curType = "addFour";
				} else if (identical) {
					var prev = cur.ch == 0 ? " " : cm.getRange(Pos(cur.line, cur.ch - 1), cur);
					if (!CodeMirror.isWordChar(next) && prev != ch && !CodeMirror.isWordChar(prev)) curType = "both";
					else return CodeMirror.Pass;
				} else if (opening && (next.length === 0 || /\s/.test(next) || closeBefore.indexOf(next) > -1)) curType = "both";
				else return CodeMirror.Pass;
				if (!type) type = curType;
				else if (type != curType) return CodeMirror.Pass;
			}
			var left = pos % 2 ? pairs.charAt(pos - 1) : ch;
			var right = pos % 2 ? ch : pairs.charAt(pos + 1);
			cm.operation(function() {
				if (type == "skip") moveSel(cm, 1);
				else if (type == "skipThree") moveSel(cm, 3);
				else if (type == "surround") {
					var sels = cm.getSelections();
					for (var i = 0; i < sels.length; i++) sels[i] = left + sels[i] + right;
					cm.replaceSelections(sels, "around");
					sels = cm.listSelections().slice();
					for (var i = 0; i < sels.length; i++) sels[i] = contractSelection(sels[i]);
					cm.setSelections(sels);
				} else if (type == "both") {
					cm.replaceSelection(left + right, null);
					cm.triggerElectric(left + right);
					moveSel(cm, -1);
				} else if (type == "addFour") {
					cm.replaceSelection(left + left + left + left, "before");
					moveSel(cm, 1);
				}
			});
		}
		function charsAround(cm, pos) {
			var str = cm.getRange(Pos(pos.line, pos.ch - 1), Pos(pos.line, pos.ch + 1));
			return str.length == 2 ? str : null;
		}
		function stringStartsAfter(cm, pos) {
			var token = cm.getTokenAt(Pos(pos.line, pos.ch + 1));
			return /\bstring/.test(token.type) && token.start == pos.ch && (pos.ch == 0 || !/\bstring/.test(cm.getTokenTypeAt(pos)));
		}
	});
}));
//#endregion
//#region node_modules/codemirror/addon/display/autorefresh.js
var require_autorefresh = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	(function(mod) {
		if (typeof exports == "object" && typeof module == "object") mod(require_codemirror());
		else if (typeof define == "function" && define.amd) define(["../../lib/codemirror"], mod);
		else mod(CodeMirror);
	})(function(CodeMirror) {
		"use strict";
		CodeMirror.defineOption("autoRefresh", false, function(cm, val) {
			if (cm.state.autoRefresh) {
				stopListening(cm, cm.state.autoRefresh);
				cm.state.autoRefresh = null;
			}
			if (val && cm.display.wrapper.offsetHeight == 0) startListening(cm, cm.state.autoRefresh = { delay: val.delay || 250 });
		});
		function startListening(cm, state) {
			function check() {
				if (cm.display.wrapper.offsetHeight) {
					stopListening(cm, state);
					if (cm.display.lastWrapHeight != cm.display.wrapper.clientHeight) cm.refresh();
				} else state.timeout = setTimeout(check, state.delay);
			}
			state.timeout = setTimeout(check, state.delay);
			state.hurry = function() {
				clearTimeout(state.timeout);
				state.timeout = setTimeout(check, 50);
			};
			CodeMirror.on(window, "mouseup", state.hurry);
			CodeMirror.on(window, "keyup", state.hurry);
		}
		function stopListening(_cm, state) {
			clearTimeout(state.timeout);
			CodeMirror.off(window, "mouseup", state.hurry);
			CodeMirror.off(window, "keyup", state.hurry);
		}
	});
}));
//#endregion
//#region node_modules/codemirror/addon/edit/continuelist.js
var require_continuelist = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	(function(mod) {
		if (typeof exports == "object" && typeof module == "object") mod(require_codemirror());
		else if (typeof define == "function" && define.amd) define(["../../lib/codemirror"], mod);
		else mod(CodeMirror);
	})(function(CodeMirror) {
		"use strict";
		var listRE = /^(\s*)(>[> ]*|[*+-] \[[x ]\]\s|[*+-]\s|(\d+)([.)]))(\s*)/, emptyListRE = /^(\s*)(>[> ]*|[*+-] \[[x ]\]|[*+-]|(\d+)[.)])(\s*)$/, unorderedListRE = /[*+-]\s/;
		CodeMirror.commands.newlineAndIndentContinueMarkdownList = function(cm) {
			if (cm.getOption("disableInput")) return CodeMirror.Pass;
			var ranges = cm.listSelections(), replacements = [];
			for (var i = 0; i < ranges.length; i++) {
				var pos = ranges[i].head;
				var eolState = cm.getStateAfter(pos.line);
				var inner = CodeMirror.innerMode(cm.getMode(), eolState);
				if (inner.mode.name !== "markdown" && inner.mode.helperType !== "markdown") {
					cm.execCommand("newlineAndIndent");
					return;
				} else eolState = inner.state;
				var inList = eolState.list !== false;
				var inQuote = eolState.quote !== 0;
				var line = cm.getLine(pos.line), match = listRE.exec(line);
				var cursorBeforeBullet = /^\s*$/.test(line.slice(0, pos.ch));
				if (!ranges[i].empty() || !inList && !inQuote || !match || cursorBeforeBullet) {
					cm.execCommand("newlineAndIndent");
					return;
				}
				if (emptyListRE.test(line)) {
					var endOfQuote = inQuote && />\s*$/.test(line);
					var endOfList = !/>\s*$/.test(line);
					if (endOfQuote || endOfList) cm.replaceRange("", {
						line: pos.line,
						ch: 0
					}, {
						line: pos.line,
						ch: pos.ch + 1
					});
					replacements[i] = "\n";
				} else {
					var indent = match[1], after = match[5];
					var numbered = !(unorderedListRE.test(match[2]) || match[2].indexOf(">") >= 0);
					var bullet = numbered ? parseInt(match[3], 10) + 1 + match[4] : match[2].replace("x", " ");
					replacements[i] = "\n" + indent + bullet + after;
					if (numbered) incrementRemainingMarkdownListNumbers(cm, pos);
				}
			}
			cm.replaceSelections(replacements);
		};
		function incrementRemainingMarkdownListNumbers(cm, pos) {
			var startLine = pos.line, lookAhead = 0, skipCount = 0;
			var startItem = listRE.exec(cm.getLine(startLine)), startIndent = startItem[1];
			do {
				lookAhead += 1;
				var nextLineNumber = startLine + lookAhead;
				var nextLine = cm.getLine(nextLineNumber), nextItem = listRE.exec(nextLine);
				if (nextItem) {
					var nextIndent = nextItem[1];
					var newNumber = parseInt(startItem[3], 10) + lookAhead - skipCount;
					var nextNumber = parseInt(nextItem[3], 10), itemNumber = nextNumber;
					if (startIndent === nextIndent && !isNaN(nextNumber)) {
						if (newNumber === nextNumber) itemNumber = nextNumber + 1;
						if (newNumber > nextNumber) itemNumber = newNumber + 1;
						cm.replaceRange(nextLine.replace(listRE, nextIndent + itemNumber + nextItem[4] + nextItem[5]), {
							line: nextLineNumber,
							ch: 0
						}, {
							line: nextLineNumber,
							ch: nextLine.length
						});
					} else {
						if (startIndent.length > nextIndent.length) return;
						if (startIndent.length < nextIndent.length && lookAhead === 1) return;
						skipCount += 1;
					}
				}
			} while (nextItem);
		}
	});
}));
//#endregion
//#region resources/js/components/fieldtypes/markdown/MarkdownToolbar.vue
var import_codemirror = /* @__PURE__ */ __toESM(require_codemirror());
require_matchbrackets();
require_css();
require_clike();
require_xml();
require_markdown();
require_gfm();
require_javascript();
require_htmlmixed();
require_php();
require_yaml();
require_closebrackets();
require_autorefresh();
require_continuelist();
var _sfc_main$1 = {
	components: {
		Button: Button_default,
		ToggleGroup: Group_default,
		ToggleItem: Item_default
	},
	props: {
		buttons: {
			type: Array,
			required: true
		},
		isReadOnly: {
			type: Boolean,
			default: false
		},
		showDarkMode: {
			type: Boolean,
			default: false
		},
		darkMode: {
			type: Boolean,
			default: false
		},
		isFullscreen: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return { mode: "write" };
	},
	watch: { mode(newVal) {
		this.$emit("update:mode", newVal);
	} },
	emits: [
		"update:mode",
		"toggle-dark-mode",
		"button-click"
	],
	methods: { handleButtonClick(button) {
		this.$emit("button-click", button.command);
	} }
};
var _hoisted_1$1 = { "data-markdown-toolbar": "" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Button = resolveComponent("Button");
	const _component_ToggleItem = resolveComponent("ToggleItem");
	const _component_ToggleGroup = resolveComponent("ToggleGroup");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock("div", _hoisted_1$1, [!$props.isReadOnly ? (openBlock(), createElementBlock("div", {
		key: 0,
		class: normalizeClass(["flex items-center", { "gap-2": $props.isFullscreen }])
	}, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.buttons, (button) => {
		return withDirectives((openBlock(), createBlock(_component_Button, {
			"aria-label": button.text,
			icon: button.svg,
			key: button.name,
			onClick: ($event) => $options.handleButtonClick(button),
			size: "sm",
			variant: "ghost"
		}, null, 8, [
			"aria-label",
			"icon",
			"onClick"
		])), [[_directive_tooltip, button.text]]);
	}), 128))], 2)) : createCommentVNode("", true), createVNode(_component_ToggleGroup, {
		modelValue: $data.mode,
		"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.mode = $event),
		size: "sm",
		class: "-me-1",
		"data-markdown-mode-toggle": ""
	}, {
		default: withCtx(() => [withDirectives(createVNode(_component_ToggleItem, {
			icon: "pencil",
			value: "write"
		}, null, 512), [[_directive_tooltip, _ctx.__("Writing Mode")]]), withDirectives(createVNode(_component_ToggleItem, {
			icon: "eye",
			value: "preview"
		}, null, 512), [[_directive_tooltip, _ctx.__("Preview Mode")]])]),
		_: 1
	}, 8, ["modelValue"])]);
}
var MarkdownToolbar_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "MarkdownToolbar.vue"]]);
require_sublime();
/**
* `ucs2decode` function from the punycode.js library.
*
* Creates an array containing the decimal code points of each Unicode
* character in the string. While JavaScript uses UCS-2 internally, this
* function will convert a pair of surrogate halves (each of which UCS-2
* exposes as separate characters) into a single code point, matching
* UTF-16.
*
* @see     <http://goo.gl/8M09r>
* @see     <http://goo.gl/u4UUC>
*
* @param   {String}  string   The Unicode input string (UCS-2).
*
* @return  {Array}   The new array of code points.
*/
function ucs2decode(string) {
	const output = [];
	let counter = 0;
	const length = string.length;
	while (counter < length) {
		const value = string.charCodeAt(counter++);
		if (value >= 55296 && value <= 56319 && counter < length) {
			const extra = string.charCodeAt(counter++);
			if ((extra & 64512) == 56320) output.push(((value & 1023) << 10) + (extra & 1023) + 65536);
			else {
				output.push(value);
				counter--;
			}
		} else output.push(value);
	}
	return output;
}
var _sfc_main = {
	mixins: [Fieldtype_default],
	components: {
		Button: Button_default,
		AssetSelector: Selector_default,
		Uploader: Uploader_default,
		Uploads: Uploads_default,
		MarkdownToolbar: MarkdownToolbar_default,
		Stack: Stack_default
	},
	data() {
		return {
			data: this.value || "",
			buttons: [],
			mode: "write",
			selections: null,
			showAssetSelector: false,
			selectedAssets: [],
			draggingFile: false,
			showCheatsheet: false,
			fullScreenMode: false,
			darkMode: false,
			codemirror: null,
			uploads: [],
			count: {
				characters: 0,
				words: 0
			},
			escBinding: null,
			markdownPreviewText: null,
			showFloatingToolbar: false,
			floatingToolbarX: 0,
			floatingToolbarY: 0
		};
	},
	watch: {
		data: { handler(data) {
			this.updateDebounced(data);
			this.updateCount(data);
		} },
		mode: { handler(mode) {
			if (mode === "preview") this.updateMarkdownPreview();
		} },
		readOnly: { handler(readOnly) {
			this.codemirror.setOption("readOnly", readOnly ? "nocursor" : false);
		} }
	},
	mounted() {
		this.initToolbarButtons();
		this.$nextTick(() => {
			this.initCodeMirror();
		});
		if (this.data) this.updateCount(this.data);
	},
	beforeUnmount() {
		this.$events.$off("livepreview.opened", this.throttledResizeEvent);
		this.$events.$off("livepreview.closed", this.throttledResizeEvent);
		this.$events.$off("livepreview.resizing", this.throttledResizeEvent);
		if (this.codemirror && this.toolbarIsFloating) {
			this.codemirror.off("cursorActivity", this.handleCursorActivity);
			this.codemirror.off("blur", this.hideFloatingToolbar);
		}
	},
	methods: {
		closeFullScreen() {
			this.fullScreenMode = false;
			this.escBinding.destroy();
			this.trackHeightUpdates();
		},
		openFullScreen() {
			this.fullScreenMode = true;
			this.escBinding = this.$keys.bindGlobal("esc", this.closeFullScreen);
			this.trackHeightUpdates();
			this.$nextTick(() => {
				if (this.codemirror) this.codemirror.focus();
			});
		},
		toggleFullscreen() {
			if (this.fullScreenMode) this.closeFullScreen();
			else this.openFullScreen();
		},
		toggleDarkMode() {
			this.darkMode = !this.darkMode;
		},
		getText(selection) {
			const i = this.selections.indexOf(selection);
			return this.codemirror.getSelections()[i];
		},
		toggleInline(type) {
			const elements = {
				bold: {
					pattern: /^\*{2}(.*)\*{2}$/,
					delimiter: "**"
				},
				code: {
					pattern: /^\`(.*)\`$/,
					delimiter: "`"
				},
				italic: {
					pattern: /^\_(.*)\_$/,
					delimiter: "_"
				},
				strikethrough: {
					pattern: /^\~\~(.*)\~\~$/,
					delimiter: "~~"
				}
			};
			const replacements = this.selections.map((selection) => {
				const text = this.getText(selection);
				const { delimiter, pattern } = elements[type];
				return text.match(pattern) ? this.removeInline(selection, delimiter) : `${delimiter}${text}${delimiter}`;
			});
			this.codemirror.replaceSelections(replacements, "around");
			this.codemirror.focus();
		},
		toggleBlock(type) {
			const replacements = this.selections.map((selection) => {
				const text = this.getText(selection);
				const delimiter = "```";
				return text.match(new RegExp(`^\`\`\`(.*)\n(.*)\n\`\`\`$`)) ? this.removeInline(selection, delimiter) : `${delimiter}\n${text}\n${delimiter}`;
			});
			this.codemirror.replaceSelections(replacements, "around");
			this.codemirror.focus();
		},
		removeInline(selection, delimiter) {
			return this.getText(selection).slice(delimiter.length, -delimiter.length);
		},
		toggleLine(type) {
			const startPoint = this.codemirror.getCursor("start");
			const endPoint = this.codemirror.getCursor("end");
			const patterns = {
				quote: /^(\s*)\>\s+/,
				"unordered-list": /^(\s*)(\*|\-|\+)\s+/,
				"ordered-list": /^(\s*)\d+\.\s+/
			};
			const prefixes = {
				quote: "> ",
				"unordered-list": "- ",
				"ordered-list": "1. "
			};
			for (let i = startPoint.line; i <= endPoint.line; i++) {
				const text = this.codemirror.getLine(i);
				const newText = this.isInside(type) ? text.replace(patterns[type], "$1") : prefixes[type] + text;
				this.codemirror.replaceRange(newText, {
					line: i,
					ch: 0
				}, {
					line: i,
					ch: Infinity
				});
			}
			this.codemirror.focus();
		},
		getState(position) {
			position = position || this.codemirror.getCursor("start");
			const state = this.codemirror.getTokenAt(position);
			if (!state.type) return {};
			const types = state.type.split(" ");
			const ret = {};
			const text = this.codemirror.getLine(position.line);
			types.forEach((type) => {
				switch (type) {
					case "strong":
						ret.bold = true;
						break;
					case "variable-2":
						ret[/^\s*\d+\.\s/.test(text) ? "ordered-list" : "unordered-list"] = true;
						break;
					case "atom":
					case "quote":
						ret.quote = true;
						break;
					case "em":
						ret.italic = true;
						break;
					case "strikethrough":
						ret.strikethrough = true;
						break;
					case "comment":
						ret.code = true;
						break;
					case "link":
						ret.link = true;
						break;
					case "tag":
						ret.image = true;
						break;
					default: if (type.match(/^header(\-[1-6])?$/)) ret[type.replace("header", "heading")] = true;
				}
			});
			return ret;
		},
		isInside(type) {
			return this.getState()[type] ?? false;
		},
		insertTable() {
			const doc = this.codemirror.getDoc();
			const cursor = doc.getCursor();
			const line = doc.getLine(cursor.line);
			const pos = { line: cursor.line };
			const table = "|     |     |\n| --- | --- |\n|     |     |";
			if (line.length === 0) doc.replaceRange(table, pos);
			else {
				doc.replaceRange("\n\n|     |     |\n| --- | --- |\n|     |     |", pos);
				cursor.line += 2;
			}
			this.codemirror.focus();
			this.codemirror.setCursor(cursor.line, 2);
		},
		insertImage(url, alt) {
			const doc = this.codemirror.doc;
			const selection = doc.somethingSelected() ? doc.getSelection() : alt || "";
			const imageText = `![${selection}](${url || ""})`;
			doc.replaceSelection(imageText, "start");
			const line = doc.getCursor().line;
			const start = doc.getCursor().ch + 2;
			const end = start + selection.length;
			doc.setSelection({
				line,
				ch: start
			}, {
				line,
				ch: end
			});
			this.codemirror.focus();
		},
		appendImage(url, alt = "") {
			this.data += `\n\n![${alt}](${url})`;
		},
		insertLink(url, text) {
			const doc = this.codemirror.doc;
			const selection = doc.somethingSelected() ? doc.getSelection() : text || "";
			if (!url) {
				url = prompt(__("Enter URL"), "https://");
				if (!url) return;
			}
			const linkText = `[${selection}](${url})`;
			doc.replaceSelection(linkText, "start");
			const line = doc.getCursor().line;
			const start = doc.getCursor().ch + 1;
			const end = start + selection.length;
			doc.setSelection({
				line,
				ch: start
			}, {
				line,
				ch: end
			});
			this.codemirror.focus();
		},
		appendLink(url, text = "") {
			this.data += `\n\n[${text}](${url})`;
		},
		/**
		* Open the asset selector
		*/
		addAsset() {
			if (!this.assetsEnabled) return;
			this.showAssetSelector = true;
		},
		/**
		* Execute a keyboard shortcut, when applicable
		*/
		shortcut(e) {
			if (e.keyCode === 27) {
				e.preventDefault();
				this.codemirror.getInputField().blur();
				return;
			}
			if (!(e.metaKey || e.ctrlKey)) return;
			if (this.assetsEnabled && e.shiftKey && e.keyCode === 65) {
				e.preventDefault();
				this.addAsset();
				return;
			}
			const shortcuts = {
				66: () => this.toggleInline("bold"),
				73: () => this.toggleInline("italic")
			};
			if (shortcuts[e.keyCode]) {
				e.preventDefault();
				shortcuts[e.keyCode]();
			}
		},
		/**
		* When assets are selected from the modal, this event gets fired.
		*
		* @param  Array assets  All the assets that were selected
		*/
		assetsSelected(assets) {
			this.closeAssetSelector();
			this.selectedAssets = [];
			this.$axios.post(cp_url("assets-fieldtype"), { assets }).then(({ data }) => {
				data.forEach((asset) => {
					const alt = asset.values.alt || "";
					const url = encodeURI(`statamic://${asset.reference}`);
					const method = assets.length === 1 ? "insert" : "append";
					if (asset.isImage) this[`${method}Image`](url, alt);
					else this[`${method}Link`](url, alt);
				});
			});
		},
		closeAssetSelector() {
			this.showAssetSelector = false;
		},
		uploadsUpdated(uploads) {
			this.uploads = uploads;
		},
		uploadComplete(upload, uploads) {
			if (upload.is_image) this.insertImage(upload.url);
			else this.insertLink(upload.url);
			if (uploads.length > 1) this.codemirror.setCursor(this.codemirror.lineCount(), 0);
		},
		focus() {
			this.codemirror.focus();
		},
		focusCodeMirror() {
			if (this.codemirror) this.codemirror.focus();
		},
		trackHeightUpdates() {
			this.$events.$on("livepreview.opened", this.throttledResizeEvent);
			this.$events.$on("livepreview.closed", this.throttledResizeEvent);
			this.$events.$on("livepreview.resizing", this.throttledResizeEvent);
		},
		throttledResizeEvent: throttle_default(function() {
			window.dispatchEvent(new Event("resize"));
		}, 100),
		updateMarkdownPreview() {
			this.$axios.post(this.meta.previewUrl, {
				value: this.data,
				config: this.config
			}).then((response) => this.markdownPreviewText = purify.sanitize(response.data)).catch((e) => this.$toast.error(e.response ? e.response.data.message : __("Something went wrong")));
		},
		initCodeMirror() {
			var self = this;
			self.codemirror = markRaw((0, import_codemirror.default)(this.$refs.codemirror, {
				value: self.data,
				mode: "gfm",
				dragDrop: false,
				keyMap: "sublime",
				direction: document.querySelector("html").getAttribute("dir") ?? "ltr",
				lineWrapping: true,
				viewportMargin: Infinity,
				tabindex: 0,
				autoRefresh: true,
				readOnly: self.isReadOnly ? "nocursor" : false,
				inputStyle: "contenteditable",
				spellcheck: true,
				extraKeys: {
					Enter: "newlineAndIndentContinueMarkdownList",
					"Cmd-Left": "goLineLeftSmart"
				}
			}));
			if (this.toolbarIsFloating) {
				self.codemirror.on("cursorActivity", this.handleCursorActivity);
				self.codemirror.on("blur", this.hideFloatingToolbar);
			}
			self.codemirror.on("change", function(cm) {
				self.data = cm.doc.getValue();
			});
			self.codemirror.on("focus", () => self.$emit("focus"));
			self.codemirror.on("blur", () => self.$emit("blur"));
			self.codemirror.on("beforeSelectionChange", function(cm, obj) {
				self.selections = obj.ranges;
			});
			this.$watch("value", function(val) {
				if (val !== self.codemirror.doc.getValue()) self.codemirror.doc.setValue(val);
			});
			this.trackHeightUpdates();
		},
		initToolbarButtons() {
			let buttons = this.config.buttons.map((button) => {
				return availableButtons().find((b) => b.name === button.toLowerCase()) || button;
			});
			buttons = buttons.filter((button) => {
				return button.condition ? button.condition.call(null, this.config) : true;
			});
			this.buttons = buttons;
		},
		updateCount(data) {
			const trimmed = data.trim();
			const characters = ucs2decode(trimmed.replace(/\s/g, "")).length;
			const words = trimmed.split(/\s+/).filter((word) => word.length > 0).length;
			this.count = {
				characters,
				words
			};
		},
		handleButtonClick(command) {
			command(this);
		},
		handleCursorActivity() {
			if (!this.toolbarIsFloating) return;
			const selection = this.codemirror.getSelection();
			if (selection && selection.length > 0 && !this.isReadOnly) {
				const doc = this.codemirror.getDoc();
				this.selections = doc.listSelections();
				this.showFloatingToolbar = true;
				this.updateFloatingToolbarPosition();
			} else this.showFloatingToolbar = false;
		},
		hideFloatingToolbar() {
			this.showFloatingToolbar = false;
		},
		updateFloatingToolbarPosition() {
			if (!this.codemirror || !this.showFloatingToolbar) return;
			const from = this.codemirror.getCursor("from");
			const to = this.codemirror.getCursor("to");
			const fromCoords = this.codemirror.cursorCoords(from);
			const toCoords = this.codemirror.cursorCoords(to);
			const editorRect = this.codemirror.getWrapperElement().getBoundingClientRect();
			const x = Math.round((fromCoords.left + toCoords.right) / 2 - editorRect.left);
			const y = Math.round(fromCoords.top - editorRect.top - 50);
			this.floatingToolbarX = x;
			this.floatingToolbarY = y;
		}
	},
	computed: {
		assetsEnabled() {
			return Boolean(this.config?.container);
		},
		container() {
			return this.meta.assets?.container;
		},
		assetSelectorColumns() {
			return this.meta.assets?.columns;
		},
		editor() {
			return this;
		},
		folder() {
			return this.config.folder || "/";
		},
		restrictAssetNavigation() {
			return this.config.restrict_assets || false;
		},
		toolbarIsFixed() {
			return this.config.toolbar_mode === "fixed";
		},
		toolbarIsFloating() {
			return this.config.toolbar_mode === "floating";
		},
		showFixedToolbar() {
			return this.toolbarIsFixed && this.buttons.length > 0;
		},
		replicatorPreview() {
			if (!this.showFieldPreviews) return;
			return marked(this.data || "", { renderer: new TextRenderer() }).replace(/<\/?[^>]+(>|$)/g, "");
		},
		internalFieldActions() {
			return [{
				title: __("Toggle Fullscreen Mode"),
				icon: ({ vm }) => vm.fullScreenMode ? "fullscreen-close" : "fullscreen-open",
				quick: true,
				visible: this.config.fullscreen,
				visibleWhenReadOnly: true,
				run: this.toggleFullscreen
			}];
		}
	}
};
var _hoisted_1 = { class: "drag-notification" };
var _hoisted_2 = ["id"];
var _hoisted_3 = { class: "markdown-cheatsheet-helper" };
var _hoisted_4 = {
	key: 0,
	class: "flex items-center pe-2 gap-2 sm:gap-3 text-xs"
};
var _hoisted_5 = { class: "whitespace-nowrap" };
var _hoisted_6 = ["textContent"];
var _hoisted_7 = { class: "whitespace-nowrap" };
var _hoisted_8 = ["textContent"];
var _hoisted_9 = {
	key: 1,
	class: "drag-notification"
};
var _hoisted_10 = ["innerHTML"];
var _hoisted_11 = ["innerHTML"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_markdown_toolbar = resolveComponent("markdown-toolbar");
	const _component_publish_field_fullscreen_header = resolveComponent("publish-field-fullscreen-header");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_uploads = resolveComponent("uploads");
	const _component_Button = resolveComponent("Button");
	const _component_uploader = resolveComponent("uploader");
	const _component_asset_selector = resolveComponent("asset-selector");
	const _component_Stack = resolveComponent("Stack");
	const _component_portal = resolveComponent("portal");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock(_component_portal, {
		name: "markdown-fullscreen",
		disabled: !$data.fullScreenMode,
		"target-class": "markdown-fieldtype"
	}, {
		default: withCtx(() => [createBaseVNode("div", null, [createBaseVNode("div", { class: normalizeClass(["@container/markdown w-full block bg-white dark:bg-gray-900! rounded-lg relative border border-gray-300 with-contrast:border-gray-500 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black text-gray-900 dark:text-gray-300 appearance-none antialiased shadow-ui-sm disabled:shadow-none", {
			"markdown-fullscreen": $data.fullScreenMode,
			"markdown-dark-mode": $data.darkMode,
			"border-dashed": _ctx.isReadOnly
		}]) }, [
			createVNode(_component_uploader, {
				ref: "uploader",
				enabled: $options.assetsEnabled,
				container: $options.container?.id,
				path: $options.folder,
				onUpdated: $options.uploadsUpdated,
				onUploadComplete: $options.uploadComplete
			}, {
				default: withCtx(({ dragging }) => [createBaseVNode("div", null, [
					$data.fullScreenMode ? (openBlock(), createBlock(_component_publish_field_fullscreen_header, {
						key: 0,
						title: _ctx.config.display,
						"field-actions": _ctx.fieldActions,
						onClose: $options.toggleFullscreen
					}, {
						default: withCtx(() => [$data.fullScreenMode ? (openBlock(), createBlock(_component_markdown_toolbar, {
							key: 0,
							mode: $data.mode,
							"onUpdate:mode": _cache[0] || (_cache[0] = ($event) => $data.mode = $event),
							buttons: $data.buttons,
							"is-read-only": _ctx.isReadOnly,
							"show-dark-mode": $data.fullScreenMode,
							"dark-mode": $data.darkMode,
							"is-fullscreen": true,
							onToggleDarkMode: $options.toggleDarkMode,
							onButtonClick: $options.handleButtonClick
						}, null, 8, [
							"mode",
							"buttons",
							"is-read-only",
							"show-dark-mode",
							"dark-mode",
							"onToggleDarkMode",
							"onButtonClick"
						])) : createCommentVNode("", true)]),
						_: 1
					}, 8, [
						"title",
						"field-actions",
						"onClose"
					])) : createCommentVNode("", true),
					!$data.fullScreenMode && $options.showFixedToolbar ? (openBlock(), createBlock(_component_markdown_toolbar, {
						key: 1,
						mode: $data.mode,
						"onUpdate:mode": _cache[1] || (_cache[1] = ($event) => $data.mode = $event),
						buttons: $data.buttons,
						"is-read-only": _ctx.isReadOnly,
						"show-dark-mode": false,
						"dark-mode": $data.darkMode,
						"is-fullscreen": false,
						onToggleDarkMode: $options.toggleDarkMode,
						onButtonClick: $options.handleButtonClick,
						class: "sticky z-(--z-index-portal) top-0 sm:-top-2 mb-2 [&~*]:-mt-2"
					}, null, 8, [
						"mode",
						"buttons",
						"is-read-only",
						"dark-mode",
						"onToggleDarkMode",
						"onButtonClick"
					])) : createCommentVNode("", true),
					withDirectives(createBaseVNode("div", _hoisted_1, [createVNode(_component_ui_icon, {
						name: "upload",
						class: "mb-4 size-12"
					}), createTextVNode(" " + toDisplayString(_ctx.__("Drop File to Upload")), 1)], 512), [[vShow, dragging]]),
					$data.uploads.length ? (openBlock(), createBlock(_component_uploads, {
						key: 2,
						uploads: $data.uploads,
						class: "-mt-px"
					}, null, 8, ["uploads"])) : createCommentVNode("", true),
					createBaseVNode("div", {
						class: normalizeClass(`mode-wrap mode-${$data.mode}`, { "prose p-3": $data.mode == "preview" }),
						onClick: _cache[9] || (_cache[9] = (...args) => $options.focus && $options.focus(...args))
					}, [withDirectives(createBaseVNode("div", {
						class: "markdown-writer",
						ref: "writer",
						onDragover: _cache[5] || (_cache[5] = ($event) => $data.draggingFile = true),
						onDragleave: _cache[6] || (_cache[6] = ($event) => $data.draggingFile = false),
						onDrop: _cache[7] || (_cache[7] = ($event) => $data.draggingFile = false),
						onKeydown: _cache[8] || (_cache[8] = (...args) => $options.shortcut && $options.shortcut(...args))
					}, [
						createBaseVNode("div", {
							class: normalizeClass(["editor relative top-[0.5px] z-(--z-index-above) st-text-legibility", { "focus-within:focus-outline": !$data.fullScreenMode }]),
							ref: "codemirror"
						}, [$data.showFloatingToolbar && $options.toolbarIsFloating && !_ctx.isReadOnly ? (openBlock(), createElementBlock("div", {
							key: 0,
							class: "markdown-floating-toolbar absolute z-50 flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2 py-1 shadow-lg dark:border-white/10 dark:bg-gray-900",
							style: normalizeStyle({
								left: `${$data.floatingToolbarX}px`,
								top: `${$data.floatingToolbarY}px`
							}),
							onMousedown: _cache[2] || (_cache[2] = withModifiers(() => {}, ["prevent"]))
						}, [(openBlock(true), createElementBlock(Fragment, null, renderList($data.buttons, (button) => {
							return withDirectives((openBlock(), createBlock(_component_Button, {
								"aria-label": button.text,
								icon: button.svg,
								key: button.name,
								onClick: ($event) => $options.handleButtonClick(button.command),
								size: "sm",
								variant: "ghost"
							}, null, 8, [
								"aria-label",
								"icon",
								"onClick"
							])), [[_directive_tooltip, button.text]]);
						}), 128))], 36)) : createCommentVNode("", true)], 2),
						_ctx.id ? (openBlock(), createElementBlock("input", {
							key: 0,
							id: _ctx.id,
							type: "text",
							class: "sr-only",
							onFocus: _cache[3] || (_cache[3] = (...args) => $options.focusCodeMirror && $options.focusCodeMirror(...args)),
							tabindex: "-1"
						}, null, 40, _hoisted_2)) : createCommentVNode("", true),
						createBaseVNode("footer", { class: normalizeClass(["flex items-center justify-between bg-gray-50 dark:bg-gray-900 rounded-b-[calc(var(--radius-lg)-1px)] border-t border-gray-300 dark:border-white/10 p-1 text-sm w-full", { "absolute inset-x-0 bottom-0 rounded-none z-(--z-index-above)": $data.fullScreenMode }]) }, [createBaseVNode("div", _hoisted_3, [createVNode(_component_Button, {
							icon: "markdown",
							size: "sm",
							variant: "subtle",
							onClick: _cache[4] || (_cache[4] = ($event) => $data.showCheatsheet = true),
							"aria-label": _ctx.__("Show Markdown Cheatsheet"),
							text: _ctx.__("Markdown Cheatsheet")
						}, null, 8, ["aria-label", "text"])]), $data.fullScreenMode ? (openBlock(), createElementBlock("div", _hoisted_4, [createBaseVNode("div", _hoisted_5, [createBaseVNode("span", { textContent: toDisplayString($data.count.words) }, null, 8, _hoisted_6), createTextVNode(" " + toDisplayString(_ctx.__("Words")), 1)]), createBaseVNode("div", _hoisted_7, [createBaseVNode("span", { textContent: toDisplayString($data.count.characters) }, null, 8, _hoisted_8), createTextVNode(" " + toDisplayString(_ctx.__("Characters")), 1)])])) : createCommentVNode("", true)], 2),
						$options.assetsEnabled && $data.draggingFile ? (openBlock(), createElementBlock("div", _hoisted_9, [createVNode(_component_ui_icon, {
							name: "upload",
							class: "mb-4 size-12"
						}), createTextVNode(" " + toDisplayString(_ctx.__("Drop File to Upload")), 1)])) : createCommentVNode("", true)
					], 544), [[vShow, $data.mode == "write"]]), withDirectives(createBaseVNode("div", {
						innerHTML: $data.markdownPreviewText,
						class: "markdown-preview p-3 prose prose-sm @md/markdown:prose-base"
					}, null, 8, _hoisted_10), [[vShow, $data.mode == "preview"]])], 2)
				])]),
				_: 1
			}, 8, [
				"enabled",
				"container",
				"path",
				"onUpdated",
				"onUploadComplete"
			]),
			!_ctx.isReadOnly ? (openBlock(), createBlock(_component_Stack, {
				key: 0,
				open: $data.showAssetSelector,
				inset: "",
				"show-close-button": false
			}, {
				default: withCtx(() => [createVNode(_component_asset_selector, {
					container: $options.container,
					folder: $options.folder,
					selected: $data.selectedAssets,
					"restrict-folder-navigation": $options.restrictAssetNavigation,
					columns: $options.assetSelectorColumns,
					onSelected: $options.assetsSelected,
					onClosed: $options.closeAssetSelector
				}, null, 8, [
					"container",
					"folder",
					"selected",
					"restrict-folder-navigation",
					"columns",
					"onSelected",
					"onClosed"
				])]),
				_: 1
			}, 8, ["open"])) : createCommentVNode("", true),
			createVNode(_component_Stack, {
				size: "narrow",
				open: $data.showCheatsheet,
				"onUpdate:open": _cache[10] || (_cache[10] = ($event) => $data.showCheatsheet = $event),
				title: _ctx.__("Markdown Cheatsheet")
			}, {
				default: withCtx(() => [createBaseVNode("div", {
					class: "prose prose-zinc prose-headings:font-medium prose-pre:prose-code:!text-white mx-auto max-w-3xl [&>*:first-child]:![margin-block-start:0]",
					innerHTML: _ctx.__("markdown.cheatsheet")
				}, null, 8, _hoisted_11)]),
				_: 1
			}, 8, ["open", "title"])
		], 2)])]),
		_: 1
	}, 8, ["disabled"]);
}
var MarkdownFieldtype_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "MarkdownFieldtype.vue"]]);
//#endregion
export { MarkdownFieldtype_default as default };
