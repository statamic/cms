const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./dist-BPhk3Po_.js","./chunk-B-1-B7_t.js","./view-BHoMFKQh.js","./dist-AY222wij.js","./dist-QivoNauO.js","./Set-BU6JdaJ0.js","./_plugin-vue_export-helper-BOaGB7Aw.js","./index-Bu3QBQkS.js","./preload-helper-CW7Fztz1.js","./ui-BfR7XN6t.js","./index.esm-B4SStoAz.js","./vue.esm-bundler-BbHU-fTn.js","./globals-CR4DMcIG.js","./api-BR1uuoCk.js","./ui-BflfEhKe.css","./index-ik-wJsiG.css","./state-CAceU76W.js","./model-BO7ad71n.js"])))=>i.map(i=>d[i]);
import { r as __toESM, t as __commonJSMin } from "./chunk-B-1-B7_t.js";
import { At as toDisplayString, B as openBlock, C as createVNode, Dt as normalizeClass, F as onBeforeUnmount, J as resolveDynamicComponent, K as resolveComponent, L as onMounted, N as nextTick, O as h$1, T as defineComponent, W as renderList, _ as createBlock, _t as ref, at as withDirectives, c as vShow, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, kt as normalizeStyle, l as withKeys, q as resolveDirective, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { l as require_lib } from "./index.esm-B4SStoAz.js";
import { Ci as Icon_default, Cr as shift, Ft as Content_default, L as containerContextKey, Mt as Stack_default, Nt as Footer_default, Sr as offset, Vt as Input_default, _r as autoPlacement, br as hide, gr as arrow$2, li as Button_default, vr as computePosition, wr as size, xr as inline, yr as flip } from "./ui-BfR7XN6t.js";
import { t as __vitePreload } from "./preload-helper-CW7Fztz1.js";
import { h as nanoid, i as data_get } from "./globals-CR4DMcIG.js";
import { A as require_tiny_emitter } from "./api-BR1uuoCk.js";
import { _ as Fieldtype_default, g as Asset_default, h as Selector_default, i as ToolbarButton_default, l as ManagesSetMeta_default, m as availableButtons, p as addButtonHtml, u as SetPicker_default } from "./index-Bu3QBQkS.js";
import { i as Fragment$1, p as Slice } from "./dist-AY222wij.js";
import { _ as dropPoint, a as PluginKey, c as TextSelection, i as Plugin, m as Transform, o as Selection$1, r as NodeSelection, s as SelectionRange, u as Mapping } from "./dist-QivoNauO.js";
import { Bt as isTextSelection, Ct as isAtEndOfNode, Gt as mergeAttributes, H as findParentNodeClosestToPos, Ht as markInputRule, J as getAttributes, Jt as nodeInputRule, Mt as isNodeActive, Nt as isNodeEmpty, Pt as isNodeSelection, Q as getExtensionField, Qt as parseIndentedBlocks, R as findChildren, Ut as markPasteRule, _ as callOrReturn, c as Mark, dn as textInputRule, en as posToDOMRect, gn as keydownHandler, gt as getTextSerializersFromSchema, hn as wrappingInputRule, i as Extension, it as getNodeAtPosition, nn as renderNestedMarkdownContent, ot as getNodeType, pn as textblockTypeInputRule, pt as getText, st as getRenderedAttributes, tt as getMarkRange, u as Node3, v as canInsertNode, wt as isAtStartOfNode } from "./dist-BPhk3Po_.js";
import { n as Decoration, r as DecorationSet } from "./view-BHoMFKQh.js";
import { a as NodeViewContent, i as EditorContent, o as NodeViewWrapper, r as Editor, s as VueNodeViewRenderer, t as Set_default } from "./Set-BU6JdaJ0.js";
//#region node_modules/prosemirror-tables/dist/index.js
var import_tiny_emitter = /* @__PURE__ */ __toESM(require_tiny_emitter());
var readFromCache;
var addToCache;
if (typeof WeakMap != "undefined") {
	let cache = /* @__PURE__ */ new WeakMap();
	readFromCache = (key) => cache.get(key);
	addToCache = (key, value) => {
		cache.set(key, value);
		return value;
	};
} else {
	const cache = [];
	const cacheSize = 10;
	let cachePos = 0;
	readFromCache = (key) => {
		for (let i = 0; i < cache.length; i += 2) if (cache[i] == key) return cache[i + 1];
	};
	addToCache = (key, value) => {
		if (cachePos == cacheSize) cachePos = 0;
		cache[cachePos++] = key;
		return cache[cachePos++] = value;
	};
}
/**
* A table map describes the structure of a given table. To avoid
* recomputing them all the time, they are cached per table node. To
* be able to do that, positions saved in the map are relative to the
* start of the table, rather than the start of the document.
*
* @public
*/
var TableMap = class {
	constructor(width, height, map, problems) {
		this.width = width;
		this.height = height;
		this.map = map;
		this.problems = problems;
	}
	findCell(pos) {
		for (let i = 0; i < this.map.length; i++) {
			const curPos = this.map[i];
			if (curPos != pos) continue;
			const left = i % this.width;
			const top = i / this.width | 0;
			let right = left + 1;
			let bottom = top + 1;
			for (let j = 1; right < this.width && this.map[i + j] == curPos; j++) right++;
			for (let j = 1; bottom < this.height && this.map[i + this.width * j] == curPos; j++) bottom++;
			return {
				left,
				top,
				right,
				bottom
			};
		}
		throw new RangeError(`No cell with offset ${pos} found`);
	}
	colCount(pos) {
		for (let i = 0; i < this.map.length; i++) if (this.map[i] == pos) return i % this.width;
		throw new RangeError(`No cell with offset ${pos} found`);
	}
	nextCell(pos, axis, dir) {
		const { left, right, top, bottom } = this.findCell(pos);
		if (axis == "horiz") {
			if (dir < 0 ? left == 0 : right == this.width) return null;
			return this.map[top * this.width + (dir < 0 ? left - 1 : right)];
		} else {
			if (dir < 0 ? top == 0 : bottom == this.height) return null;
			return this.map[left + this.width * (dir < 0 ? top - 1 : bottom)];
		}
	}
	rectBetween(a, b) {
		const { left: leftA, right: rightA, top: topA, bottom: bottomA } = this.findCell(a);
		const { left: leftB, right: rightB, top: topB, bottom: bottomB } = this.findCell(b);
		return {
			left: Math.min(leftA, leftB),
			top: Math.min(topA, topB),
			right: Math.max(rightA, rightB),
			bottom: Math.max(bottomA, bottomB)
		};
	}
	cellsInRect(rect) {
		const result = [];
		const seen = {};
		for (let row = rect.top; row < rect.bottom; row++) for (let col = rect.left; col < rect.right; col++) {
			const index = row * this.width + col;
			const pos = this.map[index];
			if (seen[pos]) continue;
			seen[pos] = true;
			if (col == rect.left && col && this.map[index - 1] == pos || row == rect.top && row && this.map[index - this.width] == pos) continue;
			result.push(pos);
		}
		return result;
	}
	positionAt(row, col, table) {
		for (let i = 0, rowStart = 0;; i++) {
			const rowEnd = rowStart + table.child(i).nodeSize;
			if (i == row) {
				let index = col + row * this.width;
				const rowEndIndex = (row + 1) * this.width;
				while (index < rowEndIndex && this.map[index] < rowStart) index++;
				return index == rowEndIndex ? rowEnd - 1 : this.map[index];
			}
			rowStart = rowEnd;
		}
	}
	static get(table) {
		return readFromCache(table) || addToCache(table, computeMap(table));
	}
};
function computeMap(table) {
	if (table.type.spec.tableRole != "table") throw new RangeError("Not a table node: " + table.type.name);
	const width = findWidth(table), height = table.childCount;
	const map = [];
	let mapPos = 0;
	let problems = null;
	const colWidths = [];
	for (let i = 0, e = width * height; i < e; i++) map[i] = 0;
	for (let row = 0, pos = 0; row < height; row++) {
		const rowNode = table.child(row);
		pos++;
		for (let i = 0;; i++) {
			while (mapPos < map.length && map[mapPos] != 0) mapPos++;
			if (i == rowNode.childCount) break;
			const cellNode = rowNode.child(i);
			const { colspan, rowspan, colwidth } = cellNode.attrs;
			for (let h = 0; h < rowspan; h++) {
				if (h + row >= height) {
					(problems || (problems = [])).push({
						type: "overlong_rowspan",
						pos,
						n: rowspan - h
					});
					break;
				}
				const start = mapPos + h * width;
				for (let w = 0; w < colspan; w++) {
					if (map[start + w] == 0) map[start + w] = pos;
					else (problems || (problems = [])).push({
						type: "collision",
						row,
						pos,
						n: colspan - w
					});
					const colW = colwidth && colwidth[w];
					if (colW) {
						const widthIndex = (start + w) % width * 2, prev = colWidths[widthIndex];
						if (prev == null || prev != colW && colWidths[widthIndex + 1] == 1) {
							colWidths[widthIndex] = colW;
							colWidths[widthIndex + 1] = 1;
						} else if (prev == colW) colWidths[widthIndex + 1]++;
					}
				}
			}
			mapPos += colspan;
			pos += cellNode.nodeSize;
		}
		const expectedPos = (row + 1) * width;
		let missing = 0;
		while (mapPos < expectedPos) if (map[mapPos++] == 0) missing++;
		if (missing) (problems || (problems = [])).push({
			type: "missing",
			row,
			n: missing
		});
		pos++;
	}
	if (width === 0 || height === 0) (problems || (problems = [])).push({ type: "zero_sized" });
	const tableMap = new TableMap(width, height, map, problems);
	let badWidths = false;
	for (let i = 0; !badWidths && i < colWidths.length; i += 2) if (colWidths[i] != null && colWidths[i + 1] < height) badWidths = true;
	if (badWidths) findBadColWidths(tableMap, colWidths, table);
	return tableMap;
}
function findWidth(table) {
	let width = -1;
	let hasRowSpan = false;
	for (let row = 0; row < table.childCount; row++) {
		const rowNode = table.child(row);
		let rowWidth = 0;
		if (hasRowSpan) for (let j = 0; j < row; j++) {
			const prevRow = table.child(j);
			for (let i = 0; i < prevRow.childCount; i++) {
				const cell = prevRow.child(i);
				if (j + cell.attrs.rowspan > row) rowWidth += cell.attrs.colspan;
			}
		}
		for (let i = 0; i < rowNode.childCount; i++) {
			const cell = rowNode.child(i);
			rowWidth += cell.attrs.colspan;
			if (cell.attrs.rowspan > 1) hasRowSpan = true;
		}
		if (width == -1) width = rowWidth;
		else if (width != rowWidth) width = Math.max(width, rowWidth);
	}
	return width;
}
function findBadColWidths(map, colWidths, table) {
	if (!map.problems) map.problems = [];
	const seen = {};
	for (let i = 0; i < map.map.length; i++) {
		const pos = map.map[i];
		if (seen[pos]) continue;
		seen[pos] = true;
		const node = table.nodeAt(pos);
		if (!node) throw new RangeError(`No cell with offset ${pos} found`);
		let updated = null;
		const attrs = node.attrs;
		for (let j = 0; j < attrs.colspan; j++) {
			const colWidth = colWidths[(i + j) % map.width * 2];
			if (colWidth != null && (!attrs.colwidth || attrs.colwidth[j] != colWidth)) (updated || (updated = freshColWidth(attrs)))[j] = colWidth;
		}
		if (updated) map.problems.unshift({
			type: "colwidth mismatch",
			pos,
			colwidth: updated
		});
	}
}
function freshColWidth(attrs) {
	if (attrs.colwidth) return attrs.colwidth.slice();
	const result = [];
	for (let i = 0; i < attrs.colspan; i++) result.push(0);
	return result;
}
/**
* @public
*/
function tableNodeTypes(schema) {
	let result = schema.cached.tableNodeTypes;
	if (!result) {
		result = schema.cached.tableNodeTypes = {};
		for (const name in schema.nodes) {
			const type = schema.nodes[name], role = type.spec.tableRole;
			if (role) result[role] = type;
		}
	}
	return result;
}
/**
* @public
*/
var tableEditingKey = new PluginKey("selectingCells");
/**
* @public
*/
function cellAround($pos) {
	for (let d = $pos.depth - 1; d > 0; d--) if ($pos.node(d).type.spec.tableRole == "row") return $pos.node(0).resolve($pos.before(d + 1));
	return null;
}
function cellWrapping($pos) {
	for (let d = $pos.depth; d > 0; d--) {
		const role = $pos.node(d).type.spec.tableRole;
		if (role === "cell" || role === "header_cell") return $pos.node(d);
	}
	return null;
}
/**
* @public
*/
function isInTable(state) {
	const $head = state.selection.$head;
	for (let d = $head.depth; d > 0; d--) if ($head.node(d).type.spec.tableRole == "row") return true;
	return false;
}
/**
* @internal
*/
function selectionCell(state) {
	const sel = state.selection;
	if ("$anchorCell" in sel && sel.$anchorCell) return sel.$anchorCell.pos > sel.$headCell.pos ? sel.$anchorCell : sel.$headCell;
	else if ("node" in sel && sel.node && sel.node.type.spec.tableRole == "cell") return sel.$anchor;
	const $cell = cellAround(sel.$head) || cellNear(sel.$head);
	if ($cell) return $cell;
	throw new RangeError(`No cell found around position ${sel.head}`);
}
/**
* @public
*/
function cellNear($pos) {
	for (let after = $pos.nodeAfter, pos = $pos.pos; after; after = after.firstChild, pos++) {
		const role = after.type.spec.tableRole;
		if (role == "cell" || role == "header_cell") return $pos.doc.resolve(pos);
	}
	for (let before = $pos.nodeBefore, pos = $pos.pos; before; before = before.lastChild, pos--) {
		const role = before.type.spec.tableRole;
		if (role == "cell" || role == "header_cell") return $pos.doc.resolve(pos - before.nodeSize);
	}
}
/**
* @public
*/
function pointsAtCell($pos) {
	return $pos.parent.type.spec.tableRole == "row" && !!$pos.nodeAfter;
}
/**
* @public
*/
function moveCellForward($pos) {
	return $pos.node(0).resolve($pos.pos + $pos.nodeAfter.nodeSize);
}
/**
* @internal
*/
function inSameTable($cellA, $cellB) {
	return $cellA.depth == $cellB.depth && $cellA.pos >= $cellB.start(-1) && $cellA.pos <= $cellB.end(-1);
}
/**
* @public
*/
function nextCell($pos, axis, dir) {
	const table = $pos.node(-1);
	const map = TableMap.get(table);
	const tableStart = $pos.start(-1);
	const moved = map.nextCell($pos.pos - tableStart, axis, dir);
	return moved == null ? null : $pos.node(0).resolve(tableStart + moved);
}
/**
* @public
*/
function removeColSpan(attrs, pos, n = 1) {
	const result = {
		...attrs,
		colspan: attrs.colspan - n
	};
	if (result.colwidth) {
		result.colwidth = result.colwidth.slice();
		result.colwidth.splice(pos, n);
		if (!result.colwidth.some((w) => w > 0)) result.colwidth = null;
	}
	return result;
}
/**
* @public
*/
function addColSpan(attrs, pos, n = 1) {
	const result = {
		...attrs,
		colspan: attrs.colspan + n
	};
	if (result.colwidth) {
		result.colwidth = result.colwidth.slice();
		for (let i = 0; i < n; i++) result.colwidth.splice(pos, 0, 0);
	}
	return result;
}
/**
* @public
*/
function columnIsHeader(map, table, col) {
	const headerCell = tableNodeTypes(table.type.schema).header_cell;
	for (let row = 0; row < map.height; row++) if (table.nodeAt(map.map[col + row * map.width]).type != headerCell) return false;
	return true;
}
/**
* A [`Selection`](http://prosemirror.net/docs/ref/#state.Selection)
* subclass that represents a cell selection spanning part of a table.
* With the plugin enabled, these will be created when the user
* selects across cells, and will be drawn by giving selected cells a
* `selectedCell` CSS class.
*
* @public
*/
var CellSelection = class CellSelection extends Selection$1 {
	constructor($anchorCell, $headCell = $anchorCell) {
		const table = $anchorCell.node(-1);
		const map = TableMap.get(table);
		const tableStart = $anchorCell.start(-1);
		const rect = map.rectBetween($anchorCell.pos - tableStart, $headCell.pos - tableStart);
		const doc = $anchorCell.node(0);
		const cells = map.cellsInRect(rect).filter((p) => p != $headCell.pos - tableStart);
		cells.unshift($headCell.pos - tableStart);
		const ranges = cells.map((pos) => {
			const cell = table.nodeAt(pos);
			if (!cell) throw new RangeError(`No cell with offset ${pos} found`);
			const from = tableStart + pos + 1;
			return new SelectionRange(doc.resolve(from), doc.resolve(from + cell.content.size));
		});
		super(ranges[0].$from, ranges[0].$to, ranges);
		this.$anchorCell = $anchorCell;
		this.$headCell = $headCell;
	}
	map(doc, mapping) {
		const $anchorCell = doc.resolve(mapping.map(this.$anchorCell.pos));
		const $headCell = doc.resolve(mapping.map(this.$headCell.pos));
		if (pointsAtCell($anchorCell) && pointsAtCell($headCell) && inSameTable($anchorCell, $headCell)) {
			const tableChanged = this.$anchorCell.node(-1) != $anchorCell.node(-1);
			if (tableChanged && this.isRowSelection()) return CellSelection.rowSelection($anchorCell, $headCell);
			else if (tableChanged && this.isColSelection()) return CellSelection.colSelection($anchorCell, $headCell);
			else return new CellSelection($anchorCell, $headCell);
		}
		return TextSelection.between($anchorCell, $headCell);
	}
	content() {
		const table = this.$anchorCell.node(-1);
		const map = TableMap.get(table);
		const tableStart = this.$anchorCell.start(-1);
		const rect = map.rectBetween(this.$anchorCell.pos - tableStart, this.$headCell.pos - tableStart);
		const seen = {};
		const rows = [];
		for (let row = rect.top; row < rect.bottom; row++) {
			const rowContent = [];
			for (let index = row * map.width + rect.left, col = rect.left; col < rect.right; col++, index++) {
				const pos = map.map[index];
				if (seen[pos]) continue;
				seen[pos] = true;
				const cellRect = map.findCell(pos);
				let cell = table.nodeAt(pos);
				if (!cell) throw new RangeError(`No cell with offset ${pos} found`);
				const extraLeft = rect.left - cellRect.left;
				const extraRight = cellRect.right - rect.right;
				if (extraLeft > 0 || extraRight > 0) {
					let attrs = cell.attrs;
					if (extraLeft > 0) attrs = removeColSpan(attrs, 0, extraLeft);
					if (extraRight > 0) attrs = removeColSpan(attrs, attrs.colspan - extraRight, extraRight);
					if (cellRect.left < rect.left) {
						cell = cell.type.createAndFill(attrs);
						if (!cell) throw new RangeError(`Could not create cell with attrs ${JSON.stringify(attrs)}`);
					} else cell = cell.type.create(attrs, cell.content);
				}
				if (cellRect.top < rect.top || cellRect.bottom > rect.bottom) {
					const attrs = {
						...cell.attrs,
						rowspan: Math.min(cellRect.bottom, rect.bottom) - Math.max(cellRect.top, rect.top)
					};
					if (cellRect.top < rect.top) cell = cell.type.createAndFill(attrs);
					else cell = cell.type.create(attrs, cell.content);
				}
				rowContent.push(cell);
			}
			rows.push(table.child(row).copy(Fragment$1.from(rowContent)));
		}
		const fragment = this.isColSelection() && this.isRowSelection() ? table : rows;
		return new Slice(Fragment$1.from(fragment), 1, 1);
	}
	replace(tr, content = Slice.empty) {
		const mapFrom = tr.steps.length, ranges = this.ranges;
		for (let i = 0; i < ranges.length; i++) {
			const { $from, $to } = ranges[i], mapping = tr.mapping.slice(mapFrom);
			tr.replace(mapping.map($from.pos), mapping.map($to.pos), i ? Slice.empty : content);
		}
		const sel = Selection$1.findFrom(tr.doc.resolve(tr.mapping.slice(mapFrom).map(this.to)), -1);
		if (sel) tr.setSelection(sel);
	}
	replaceWith(tr, node) {
		this.replace(tr, new Slice(Fragment$1.from(node), 0, 0));
	}
	forEachCell(f) {
		const table = this.$anchorCell.node(-1);
		const map = TableMap.get(table);
		const tableStart = this.$anchorCell.start(-1);
		const cells = map.cellsInRect(map.rectBetween(this.$anchorCell.pos - tableStart, this.$headCell.pos - tableStart));
		for (let i = 0; i < cells.length; i++) f(table.nodeAt(cells[i]), tableStart + cells[i]);
	}
	isColSelection() {
		const anchorTop = this.$anchorCell.index(-1);
		const headTop = this.$headCell.index(-1);
		if (Math.min(anchorTop, headTop) > 0) return false;
		const anchorBottom = anchorTop + this.$anchorCell.nodeAfter.attrs.rowspan;
		const headBottom = headTop + this.$headCell.nodeAfter.attrs.rowspan;
		return Math.max(anchorBottom, headBottom) == this.$headCell.node(-1).childCount;
	}
	static colSelection($anchorCell, $headCell = $anchorCell) {
		const table = $anchorCell.node(-1);
		const map = TableMap.get(table);
		const tableStart = $anchorCell.start(-1);
		const anchorRect = map.findCell($anchorCell.pos - tableStart);
		const headRect = map.findCell($headCell.pos - tableStart);
		const doc = $anchorCell.node(0);
		if (anchorRect.top <= headRect.top) {
			if (anchorRect.top > 0) $anchorCell = doc.resolve(tableStart + map.map[anchorRect.left]);
			if (headRect.bottom < map.height) $headCell = doc.resolve(tableStart + map.map[map.width * (map.height - 1) + headRect.right - 1]);
		} else {
			if (headRect.top > 0) $headCell = doc.resolve(tableStart + map.map[headRect.left]);
			if (anchorRect.bottom < map.height) $anchorCell = doc.resolve(tableStart + map.map[map.width * (map.height - 1) + anchorRect.right - 1]);
		}
		return new CellSelection($anchorCell, $headCell);
	}
	isRowSelection() {
		const table = this.$anchorCell.node(-1);
		const map = TableMap.get(table);
		const tableStart = this.$anchorCell.start(-1);
		const anchorLeft = map.colCount(this.$anchorCell.pos - tableStart);
		const headLeft = map.colCount(this.$headCell.pos - tableStart);
		if (Math.min(anchorLeft, headLeft) > 0) return false;
		const anchorRight = anchorLeft + this.$anchorCell.nodeAfter.attrs.colspan;
		const headRight = headLeft + this.$headCell.nodeAfter.attrs.colspan;
		return Math.max(anchorRight, headRight) == map.width;
	}
	eq(other) {
		return other instanceof CellSelection && other.$anchorCell.pos == this.$anchorCell.pos && other.$headCell.pos == this.$headCell.pos;
	}
	static rowSelection($anchorCell, $headCell = $anchorCell) {
		const table = $anchorCell.node(-1);
		const map = TableMap.get(table);
		const tableStart = $anchorCell.start(-1);
		const anchorRect = map.findCell($anchorCell.pos - tableStart);
		const headRect = map.findCell($headCell.pos - tableStart);
		const doc = $anchorCell.node(0);
		if (anchorRect.left <= headRect.left) {
			if (anchorRect.left > 0) $anchorCell = doc.resolve(tableStart + map.map[anchorRect.top * map.width]);
			if (headRect.right < map.width) $headCell = doc.resolve(tableStart + map.map[map.width * (headRect.top + 1) - 1]);
		} else {
			if (headRect.left > 0) $headCell = doc.resolve(tableStart + map.map[headRect.top * map.width]);
			if (anchorRect.right < map.width) $anchorCell = doc.resolve(tableStart + map.map[map.width * (anchorRect.top + 1) - 1]);
		}
		return new CellSelection($anchorCell, $headCell);
	}
	toJSON() {
		return {
			type: "cell",
			anchor: this.$anchorCell.pos,
			head: this.$headCell.pos
		};
	}
	static fromJSON(doc, json) {
		return new CellSelection(doc.resolve(json.anchor), doc.resolve(json.head));
	}
	static create(doc, anchorCell, headCell = anchorCell) {
		return new CellSelection(doc.resolve(anchorCell), doc.resolve(headCell));
	}
	getBookmark() {
		return new CellBookmark(this.$anchorCell.pos, this.$headCell.pos);
	}
};
CellSelection.prototype.visible = false;
Selection$1.jsonID("cell", CellSelection);
/**
* @public
*/
var CellBookmark = class CellBookmark {
	constructor(anchor, head) {
		this.anchor = anchor;
		this.head = head;
	}
	map(mapping) {
		return new CellBookmark(mapping.map(this.anchor), mapping.map(this.head));
	}
	resolve(doc) {
		const $anchorCell = doc.resolve(this.anchor), $headCell = doc.resolve(this.head);
		if ($anchorCell.parent.type.spec.tableRole == "row" && $headCell.parent.type.spec.tableRole == "row" && $anchorCell.index() < $anchorCell.parent.childCount && $headCell.index() < $headCell.parent.childCount && inSameTable($anchorCell, $headCell)) return new CellSelection($anchorCell, $headCell);
		else return Selection$1.near($headCell, 1);
	}
};
function drawCellSelection(state) {
	if (!(state.selection instanceof CellSelection)) return null;
	const cells = [];
	state.selection.forEachCell((node, pos) => {
		cells.push(Decoration.node(pos, pos + node.nodeSize, { class: "selectedCell" }));
	});
	return DecorationSet.create(state.doc, cells);
}
function isCellBoundarySelection({ $from, $to }) {
	if ($from.pos == $to.pos || $from.pos < $to.pos - 6) return false;
	let afterFrom = $from.pos;
	let beforeTo = $to.pos;
	let depth = $from.depth;
	for (; depth >= 0; depth--, afterFrom++) if ($from.after(depth + 1) < $from.end(depth)) break;
	for (let d = $to.depth; d >= 0; d--, beforeTo--) if ($to.before(d + 1) > $to.start(d)) break;
	return afterFrom == beforeTo && /row|table/.test($from.node(depth).type.spec.tableRole);
}
function isTextSelectionAcrossCells({ $from, $to }) {
	let fromCellBoundaryNode;
	let toCellBoundaryNode;
	for (let i = $from.depth; i > 0; i--) {
		const node = $from.node(i);
		if (node.type.spec.tableRole === "cell" || node.type.spec.tableRole === "header_cell") {
			fromCellBoundaryNode = node;
			break;
		}
	}
	for (let i = $to.depth; i > 0; i--) {
		const node = $to.node(i);
		if (node.type.spec.tableRole === "cell" || node.type.spec.tableRole === "header_cell") {
			toCellBoundaryNode = node;
			break;
		}
	}
	return fromCellBoundaryNode !== toCellBoundaryNode && $to.parentOffset === 0;
}
function normalizeSelection(state, tr, allowTableNodeSelection) {
	const sel = (tr || state).selection;
	const doc = (tr || state).doc;
	let normalize;
	let role;
	if (sel instanceof NodeSelection && (role = sel.node.type.spec.tableRole)) {
		if (role == "cell" || role == "header_cell") normalize = CellSelection.create(doc, sel.from);
		else if (role == "row") {
			const $cell = doc.resolve(sel.from + 1);
			normalize = CellSelection.rowSelection($cell, $cell);
		} else if (!allowTableNodeSelection) {
			const map = TableMap.get(sel.node);
			const start = sel.from + 1;
			const lastCell = start + map.map[map.width * map.height - 1];
			normalize = CellSelection.create(doc, start + 1, lastCell);
		}
	} else if (sel instanceof TextSelection && isCellBoundarySelection(sel)) normalize = TextSelection.create(doc, sel.from);
	else if (sel instanceof TextSelection && isTextSelectionAcrossCells(sel)) normalize = TextSelection.create(doc, sel.$from.start(), sel.$from.end());
	if (normalize) (tr || (tr = state.tr)).setSelection(normalize);
	return tr;
}
/**
* @public
*/
var fixTablesKey = new PluginKey("fix-tables");
/**
* Helper for iterating through the nodes in a document that changed
* compared to the given previous document. Useful for avoiding
* duplicate work on each transaction.
*
* @public
*/
function changedDescendants(old, cur, offset, f) {
	const oldSize = old.childCount, curSize = cur.childCount;
	outer: for (let i = 0, j = 0; i < curSize; i++) {
		const child = cur.child(i);
		for (let scan = j, e = Math.min(oldSize, i + 3); scan < e; scan++) if (old.child(scan) == child) {
			j = scan + 1;
			offset += child.nodeSize;
			continue outer;
		}
		f(child, offset);
		if (j < oldSize && old.child(j).sameMarkup(child)) changedDescendants(old.child(j), child, offset + 1, f);
		else child.nodesBetween(0, child.content.size, f, offset + 1);
		offset += child.nodeSize;
	}
}
/**
* Inspect all tables in the given state's document and return a
* transaction that fixes them, if necessary. If `oldState` was
* provided, that is assumed to hold a previous, known-good state,
* which will be used to avoid re-scanning unchanged parts of the
* document.
*
* @public
*/
function fixTables(state, oldState) {
	let tr;
	const check = (node, pos) => {
		if (node.type.spec.tableRole == "table") tr = fixTable(state, node, pos, tr);
	};
	if (!oldState) state.doc.descendants(check);
	else if (oldState.doc != state.doc) changedDescendants(oldState.doc, state.doc, 0, check);
	return tr;
}
function fixTable(state, table, tablePos, tr) {
	const map = TableMap.get(table);
	if (!map.problems) return tr;
	if (!tr) tr = state.tr;
	const mustAdd = [];
	for (let i = 0; i < map.height; i++) mustAdd.push(0);
	for (let i = 0; i < map.problems.length; i++) {
		const prob = map.problems[i];
		if (prob.type == "collision") {
			const cell = table.nodeAt(prob.pos);
			if (!cell) continue;
			const attrs = cell.attrs;
			for (let j = 0; j < attrs.rowspan; j++) mustAdd[prob.row + j] += prob.n;
			tr.setNodeMarkup(tr.mapping.map(tablePos + 1 + prob.pos), null, removeColSpan(attrs, attrs.colspan - prob.n, prob.n));
		} else if (prob.type == "missing") mustAdd[prob.row] += prob.n;
		else if (prob.type == "overlong_rowspan") {
			const cell = table.nodeAt(prob.pos);
			if (!cell) continue;
			tr.setNodeMarkup(tr.mapping.map(tablePos + 1 + prob.pos), null, {
				...cell.attrs,
				rowspan: cell.attrs.rowspan - prob.n
			});
		} else if (prob.type == "colwidth mismatch") {
			const cell = table.nodeAt(prob.pos);
			if (!cell) continue;
			tr.setNodeMarkup(tr.mapping.map(tablePos + 1 + prob.pos), null, {
				...cell.attrs,
				colwidth: prob.colwidth
			});
		} else if (prob.type == "zero_sized") {
			const pos = tr.mapping.map(tablePos);
			tr.delete(pos, pos + table.nodeSize);
		}
	}
	let first, last;
	for (let i = 0; i < mustAdd.length; i++) if (mustAdd[i]) {
		if (first == null) first = i;
		last = i;
	}
	for (let i = 0, pos = tablePos + 1; i < map.height; i++) {
		const row = table.child(i);
		const end = pos + row.nodeSize;
		const add = mustAdd[i];
		if (add > 0) {
			let role = "cell";
			if (row.firstChild) role = row.firstChild.type.spec.tableRole;
			const nodes = [];
			for (let j = 0; j < add; j++) {
				const node = tableNodeTypes(state.schema)[role].createAndFill();
				if (node) nodes.push(node);
			}
			const side = (i == 0 || first == i - 1) && last == i ? pos + 1 : end - 1;
			tr.insert(tr.mapping.map(side), nodes);
		}
		pos = end;
	}
	return tr.setMeta(fixTablesKey, { fixTables: true });
}
/**
* Helper to get the selected rectangle in a table, if any. Adds table
* map, table node, and table start offset to the object for
* convenience.
*
* @public
*/
function selectedRect(state) {
	const sel = state.selection;
	const $pos = selectionCell(state);
	const table = $pos.node(-1);
	const tableStart = $pos.start(-1);
	const map = TableMap.get(table);
	return {
		...sel instanceof CellSelection ? map.rectBetween(sel.$anchorCell.pos - tableStart, sel.$headCell.pos - tableStart) : map.findCell($pos.pos - tableStart),
		tableStart,
		map,
		table
	};
}
/**
* Add a column at the given position in a table.
*
* @public
*/
function addColumn(tr, { map, tableStart, table }, col) {
	let refColumn = col > 0 ? -1 : 0;
	if (columnIsHeader(map, table, col + refColumn)) refColumn = col == 0 || col == map.width ? null : 0;
	for (let row = 0; row < map.height; row++) {
		const index = row * map.width + col;
		if (col > 0 && col < map.width && map.map[index - 1] == map.map[index]) {
			const pos = map.map[index];
			const cell = table.nodeAt(pos);
			tr.setNodeMarkup(tr.mapping.map(tableStart + pos), null, addColSpan(cell.attrs, col - map.colCount(pos)));
			row += cell.attrs.rowspan - 1;
		} else {
			const type = refColumn == null ? tableNodeTypes(table.type.schema).cell : table.nodeAt(map.map[index + refColumn]).type;
			const pos = map.positionAt(row, col, table);
			tr.insert(tr.mapping.map(tableStart + pos), type.createAndFill());
		}
	}
	return tr;
}
/**
* Command to add a column before the column with the selection.
*
* @public
*/
function addColumnBefore(state, dispatch) {
	if (!isInTable(state)) return false;
	if (dispatch) {
		const rect = selectedRect(state);
		dispatch(addColumn(state.tr, rect, rect.left));
	}
	return true;
}
/**
* Command to add a column after the column with the selection.
*
* @public
*/
function addColumnAfter(state, dispatch) {
	if (!isInTable(state)) return false;
	if (dispatch) {
		const rect = selectedRect(state);
		dispatch(addColumn(state.tr, rect, rect.right));
	}
	return true;
}
/**
* @public
*/
function removeColumn(tr, { map, table, tableStart }, col) {
	const mapStart = tr.mapping.maps.length;
	for (let row = 0; row < map.height;) {
		const index = row * map.width + col;
		const pos = map.map[index];
		const cell = table.nodeAt(pos);
		const attrs = cell.attrs;
		if (col > 0 && map.map[index - 1] == pos || col < map.width - 1 && map.map[index + 1] == pos) tr.setNodeMarkup(tr.mapping.slice(mapStart).map(tableStart + pos), null, removeColSpan(attrs, col - map.colCount(pos)));
		else {
			const start = tr.mapping.slice(mapStart).map(tableStart + pos);
			tr.delete(start, start + cell.nodeSize);
		}
		row += attrs.rowspan;
	}
}
/**
* Command function that removes the selected columns from a table.
*
* @public
*/
function deleteColumn(state, dispatch) {
	if (!isInTable(state)) return false;
	if (dispatch) {
		const rect = selectedRect(state);
		const tr = state.tr;
		if (rect.left == 0 && rect.right == rect.map.width) return false;
		for (let i = rect.right - 1;; i--) {
			removeColumn(tr, rect, i);
			if (i == rect.left) break;
			const table = rect.tableStart ? tr.doc.nodeAt(rect.tableStart - 1) : tr.doc;
			if (!table) throw new RangeError("No table found");
			rect.table = table;
			rect.map = TableMap.get(table);
		}
		dispatch(tr);
	}
	return true;
}
/**
* @public
*/
function rowIsHeader(map, table, row) {
	var _table$nodeAt;
	const headerCell = tableNodeTypes(table.type.schema).header_cell;
	for (let col = 0; col < map.width; col++) if (((_table$nodeAt = table.nodeAt(map.map[col + row * map.width])) === null || _table$nodeAt === void 0 ? void 0 : _table$nodeAt.type) != headerCell) return false;
	return true;
}
/**
* @public
*/
function addRow(tr, { map, tableStart, table }, row) {
	let rowPos = tableStart;
	for (let i = 0; i < row; i++) rowPos += table.child(i).nodeSize;
	const cells = [];
	let refRow = row > 0 ? -1 : 0;
	if (rowIsHeader(map, table, row + refRow)) refRow = row == 0 || row == map.height ? null : 0;
	for (let col = 0, index = map.width * row; col < map.width; col++, index++) if (row > 0 && row < map.height && map.map[index] == map.map[index - map.width]) {
		const pos = map.map[index];
		const attrs = table.nodeAt(pos).attrs;
		tr.setNodeMarkup(tableStart + pos, null, {
			...attrs,
			rowspan: attrs.rowspan + 1
		});
		col += attrs.colspan - 1;
	} else {
		var _table$nodeAt2;
		const type = refRow == null ? tableNodeTypes(table.type.schema).cell : (_table$nodeAt2 = table.nodeAt(map.map[index + refRow * map.width])) === null || _table$nodeAt2 === void 0 ? void 0 : _table$nodeAt2.type;
		const node = type === null || type === void 0 ? void 0 : type.createAndFill();
		if (node) cells.push(node);
	}
	tr.insert(rowPos, tableNodeTypes(table.type.schema).row.create(null, cells));
	return tr;
}
/**
* Add a table row before the selection.
*
* @public
*/
function addRowBefore(state, dispatch) {
	if (!isInTable(state)) return false;
	if (dispatch) {
		const rect = selectedRect(state);
		dispatch(addRow(state.tr, rect, rect.top));
	}
	return true;
}
/**
* Add a table row after the selection.
*
* @public
*/
function addRowAfter(state, dispatch) {
	if (!isInTable(state)) return false;
	if (dispatch) {
		const rect = selectedRect(state);
		dispatch(addRow(state.tr, rect, rect.bottom));
	}
	return true;
}
/**
* @public
*/
function removeRow(tr, { map, table, tableStart }, row) {
	let rowPos = 0;
	for (let i = 0; i < row; i++) rowPos += table.child(i).nodeSize;
	const nextRow = rowPos + table.child(row).nodeSize;
	const mapFrom = tr.mapping.maps.length;
	tr.delete(rowPos + tableStart, nextRow + tableStart);
	const seen = /* @__PURE__ */ new Set();
	for (let col = 0, index = row * map.width; col < map.width; col++, index++) {
		const pos = map.map[index];
		if (seen.has(pos)) continue;
		seen.add(pos);
		if (row > 0 && pos == map.map[index - map.width]) {
			const attrs = table.nodeAt(pos).attrs;
			tr.setNodeMarkup(tr.mapping.slice(mapFrom).map(pos + tableStart), null, {
				...attrs,
				rowspan: attrs.rowspan - 1
			});
			col += attrs.colspan - 1;
		} else if (row < map.height && pos == map.map[index + map.width]) {
			const cell = table.nodeAt(pos);
			const attrs = cell.attrs;
			const copy = cell.type.create({
				...attrs,
				rowspan: cell.attrs.rowspan - 1
			}, cell.content);
			const newPos = map.positionAt(row + 1, col, table);
			tr.insert(tr.mapping.slice(mapFrom).map(tableStart + newPos), copy);
			col += attrs.colspan - 1;
		}
	}
}
/**
* Remove the selected rows from a table.
*
* @public
*/
function deleteRow(state, dispatch) {
	if (!isInTable(state)) return false;
	if (dispatch) {
		const rect = selectedRect(state), tr = state.tr;
		if (rect.top == 0 && rect.bottom == rect.map.height) return false;
		for (let i = rect.bottom - 1;; i--) {
			removeRow(tr, rect, i);
			if (i == rect.top) break;
			const table = rect.tableStart ? tr.doc.nodeAt(rect.tableStart - 1) : tr.doc;
			if (!table) throw new RangeError("No table found");
			rect.table = table;
			rect.map = TableMap.get(rect.table);
		}
		dispatch(tr);
	}
	return true;
}
function isEmpty(cell) {
	const c = cell.content;
	return c.childCount == 1 && c.child(0).isTextblock && c.child(0).childCount == 0;
}
function cellsOverlapRectangle({ width, height, map }, rect) {
	let indexTop = rect.top * width + rect.left, indexLeft = indexTop;
	let indexBottom = (rect.bottom - 1) * width + rect.left, indexRight = indexTop + (rect.right - rect.left - 1);
	for (let i = rect.top; i < rect.bottom; i++) {
		if (rect.left > 0 && map[indexLeft] == map[indexLeft - 1] || rect.right < width && map[indexRight] == map[indexRight + 1]) return true;
		indexLeft += width;
		indexRight += width;
	}
	for (let i = rect.left; i < rect.right; i++) {
		if (rect.top > 0 && map[indexTop] == map[indexTop - width] || rect.bottom < height && map[indexBottom] == map[indexBottom + width]) return true;
		indexTop++;
		indexBottom++;
	}
	return false;
}
/**
* Merge the selected cells into a single cell. Only available when
* the selected cells' outline forms a rectangle.
*
* @public
*/
function mergeCells(state, dispatch) {
	const sel = state.selection;
	if (!(sel instanceof CellSelection) || sel.$anchorCell.pos == sel.$headCell.pos) return false;
	const rect = selectedRect(state), { map } = rect;
	if (cellsOverlapRectangle(map, rect)) return false;
	if (dispatch) {
		const tr = state.tr;
		const seen = {};
		let content = Fragment$1.empty;
		let mergedPos;
		let mergedCell;
		for (let row = rect.top; row < rect.bottom; row++) for (let col = rect.left; col < rect.right; col++) {
			const cellPos = map.map[row * map.width + col];
			const cell = rect.table.nodeAt(cellPos);
			if (seen[cellPos] || !cell) continue;
			seen[cellPos] = true;
			if (mergedPos == null) {
				mergedPos = cellPos;
				mergedCell = cell;
			} else {
				if (!isEmpty(cell)) content = content.append(cell.content);
				const mapped = tr.mapping.map(cellPos + rect.tableStart);
				tr.delete(mapped, mapped + cell.nodeSize);
			}
		}
		if (mergedPos == null || mergedCell == null) return true;
		tr.setNodeMarkup(mergedPos + rect.tableStart, null, {
			...addColSpan(mergedCell.attrs, mergedCell.attrs.colspan, rect.right - rect.left - mergedCell.attrs.colspan),
			rowspan: rect.bottom - rect.top
		});
		if (content.size > 0) {
			const end = mergedPos + 1 + mergedCell.content.size;
			const start = isEmpty(mergedCell) ? mergedPos + 1 : end;
			tr.replaceWith(start + rect.tableStart, end + rect.tableStart, content);
		}
		tr.setSelection(new CellSelection(tr.doc.resolve(mergedPos + rect.tableStart)));
		dispatch(tr);
	}
	return true;
}
/**
* Split a selected cell, whose rowpan or colspan is greater than one,
* into smaller cells. Use the first cell type for the new cells.
*
* @public
*/
function splitCell(state, dispatch) {
	const nodeTypes = tableNodeTypes(state.schema);
	return splitCellWithType(({ node }) => {
		return nodeTypes[node.type.spec.tableRole];
	})(state, dispatch);
}
/**
* Split a selected cell, whose rowpan or colspan is greater than one,
* into smaller cells with the cell type (th, td) returned by getType function.
*
* @public
*/
function splitCellWithType(getCellType) {
	return (state, dispatch) => {
		const sel = state.selection;
		let cellNode;
		let cellPos;
		if (!(sel instanceof CellSelection)) {
			var _cellAround;
			cellNode = cellWrapping(sel.$from);
			if (!cellNode) return false;
			cellPos = (_cellAround = cellAround(sel.$from)) === null || _cellAround === void 0 ? void 0 : _cellAround.pos;
		} else {
			if (sel.$anchorCell.pos != sel.$headCell.pos) return false;
			cellNode = sel.$anchorCell.nodeAfter;
			cellPos = sel.$anchorCell.pos;
		}
		if (cellNode == null || cellPos == null) return false;
		if (cellNode.attrs.colspan == 1 && cellNode.attrs.rowspan == 1) return false;
		if (dispatch) {
			let baseAttrs = cellNode.attrs;
			const attrs = [];
			const colwidth = baseAttrs.colwidth;
			if (baseAttrs.rowspan > 1) baseAttrs = {
				...baseAttrs,
				rowspan: 1
			};
			if (baseAttrs.colspan > 1) baseAttrs = {
				...baseAttrs,
				colspan: 1
			};
			const rect = selectedRect(state), tr = state.tr;
			for (let i = 0; i < rect.right - rect.left; i++) attrs.push(colwidth ? {
				...baseAttrs,
				colwidth: colwidth && colwidth[i] ? [colwidth[i]] : null
			} : baseAttrs);
			let lastCell;
			for (let row = rect.top; row < rect.bottom; row++) {
				let pos = rect.map.positionAt(row, rect.left, rect.table);
				if (row == rect.top) pos += cellNode.nodeSize;
				for (let col = rect.left, i = 0; col < rect.right; col++, i++) {
					if (col == rect.left && row == rect.top) continue;
					tr.insert(lastCell = tr.mapping.map(pos + rect.tableStart, 1), getCellType({
						node: cellNode,
						row,
						col
					}).createAndFill(attrs[i]));
				}
			}
			tr.setNodeMarkup(cellPos, getCellType({
				node: cellNode,
				row: rect.top,
				col: rect.left
			}), attrs[0]);
			if (sel instanceof CellSelection) tr.setSelection(new CellSelection(tr.doc.resolve(sel.$anchorCell.pos), lastCell ? tr.doc.resolve(lastCell) : void 0));
			dispatch(tr);
		}
		return true;
	};
}
/**
* Returns a command that sets the given attribute to the given value,
* and is only available when the currently selected cell doesn't
* already have that attribute set to that value.
*
* @public
*/
function setCellAttr(name, value) {
	return function(state, dispatch) {
		if (!isInTable(state)) return false;
		const $cell = selectionCell(state);
		if ($cell.nodeAfter.attrs[name] === value) return false;
		if (dispatch) {
			const tr = state.tr;
			if (state.selection instanceof CellSelection) state.selection.forEachCell((node, pos) => {
				if (node.attrs[name] !== value) tr.setNodeMarkup(pos, null, {
					...node.attrs,
					[name]: value
				});
			});
			else tr.setNodeMarkup($cell.pos, null, {
				...$cell.nodeAfter.attrs,
				[name]: value
			});
			dispatch(tr);
		}
		return true;
	};
}
function deprecated_toggleHeader(type) {
	return function(state, dispatch) {
		if (!isInTable(state)) return false;
		if (dispatch) {
			const types = tableNodeTypes(state.schema);
			const rect = selectedRect(state), tr = state.tr;
			const cells = rect.map.cellsInRect(type == "column" ? {
				left: rect.left,
				top: 0,
				right: rect.right,
				bottom: rect.map.height
			} : type == "row" ? {
				left: 0,
				top: rect.top,
				right: rect.map.width,
				bottom: rect.bottom
			} : rect);
			const nodes = cells.map((pos) => rect.table.nodeAt(pos));
			for (let i = 0; i < cells.length; i++) if (nodes[i].type == types.header_cell) tr.setNodeMarkup(rect.tableStart + cells[i], types.cell, nodes[i].attrs);
			if (tr.steps.length === 0) for (let i = 0; i < cells.length; i++) tr.setNodeMarkup(rect.tableStart + cells[i], types.header_cell, nodes[i].attrs);
			dispatch(tr);
		}
		return true;
	};
}
function isHeaderEnabledByType(type, rect, types) {
	const cellPositions = rect.map.cellsInRect({
		left: 0,
		top: 0,
		right: type == "row" ? rect.map.width : 1,
		bottom: type == "column" ? rect.map.height : 1
	});
	for (let i = 0; i < cellPositions.length; i++) {
		const cell = rect.table.nodeAt(cellPositions[i]);
		if (cell && cell.type !== types.header_cell) return false;
	}
	return true;
}
/**
* Toggles between row/column header and normal cells (Only applies to first row/column).
* For deprecated behavior pass `useDeprecatedLogic` in options with true.
*
* @public
*/
function toggleHeader(type, options) {
	options = options || { useDeprecatedLogic: false };
	if (options.useDeprecatedLogic) return deprecated_toggleHeader(type);
	return function(state, dispatch) {
		if (!isInTable(state)) return false;
		if (dispatch) {
			const types = tableNodeTypes(state.schema);
			const rect = selectedRect(state), tr = state.tr;
			const isHeaderRowEnabled = isHeaderEnabledByType("row", rect, types);
			const isHeaderColumnEnabled = isHeaderEnabledByType("column", rect, types);
			const selectionStartsAt = (type === "column" ? isHeaderRowEnabled : type === "row" ? isHeaderColumnEnabled : false) ? 1 : 0;
			const cellsRect = type == "column" ? {
				left: 0,
				top: selectionStartsAt,
				right: 1,
				bottom: rect.map.height
			} : type == "row" ? {
				left: selectionStartsAt,
				top: 0,
				right: rect.map.width,
				bottom: 1
			} : rect;
			const newType = type == "column" ? isHeaderColumnEnabled ? types.cell : types.header_cell : type == "row" ? isHeaderRowEnabled ? types.cell : types.header_cell : types.cell;
			rect.map.cellsInRect(cellsRect).forEach((relativeCellPos) => {
				const cellPos = relativeCellPos + rect.tableStart;
				const cell = tr.doc.nodeAt(cellPos);
				if (cell) tr.setNodeMarkup(cellPos, newType, cell.attrs);
			});
			dispatch(tr);
		}
		return true;
	};
}
toggleHeader("row", { useDeprecatedLogic: true });
toggleHeader("column", { useDeprecatedLogic: true });
/**
* Toggles whether the selected cells are header cells.
*
* @public
*/
var toggleHeaderCell = toggleHeader("cell", { useDeprecatedLogic: true });
function findNextCell($cell, dir) {
	if (dir < 0) {
		const before = $cell.nodeBefore;
		if (before) return $cell.pos - before.nodeSize;
		for (let row = $cell.index(-1) - 1, rowEnd = $cell.before(); row >= 0; row--) {
			const rowNode = $cell.node(-1).child(row);
			const lastChild = rowNode.lastChild;
			if (lastChild) return rowEnd - 1 - lastChild.nodeSize;
			rowEnd -= rowNode.nodeSize;
		}
	} else {
		if ($cell.index() < $cell.parent.childCount - 1) return $cell.pos + $cell.nodeAfter.nodeSize;
		const table = $cell.node(-1);
		for (let row = $cell.indexAfter(-1), rowStart = $cell.after(); row < table.childCount; row++) {
			const rowNode = table.child(row);
			if (rowNode.childCount) return rowStart + 1;
			rowStart += rowNode.nodeSize;
		}
	}
	return null;
}
/**
* Returns a command for selecting the next (direction=1) or previous
* (direction=-1) cell in a table.
*
* @public
*/
function goToNextCell(direction) {
	return function(state, dispatch) {
		if (!isInTable(state)) return false;
		const cell = findNextCell(selectionCell(state), direction);
		if (cell == null) return false;
		if (dispatch) {
			const $cell = state.doc.resolve(cell);
			dispatch(state.tr.setSelection(TextSelection.between($cell, moveCellForward($cell))).scrollIntoView());
		}
		return true;
	};
}
/**
* Deletes the table around the selection, if any.
*
* @public
*/
function deleteTable(state, dispatch) {
	const $pos = state.selection.$anchor;
	for (let d = $pos.depth; d > 0; d--) if ($pos.node(d).type.spec.tableRole == "table") {
		if (dispatch) dispatch(state.tr.delete($pos.before(d), $pos.after(d)).scrollIntoView());
		return true;
	}
	return false;
}
/**
* Deletes the content of the selected cells, if they are not empty.
*
* @public
*/
function deleteCellSelection(state, dispatch) {
	const sel = state.selection;
	if (!(sel instanceof CellSelection)) return false;
	if (dispatch) {
		const tr = state.tr;
		const baseContent = tableNodeTypes(state.schema).cell.createAndFill().content;
		sel.forEachCell((cell, pos) => {
			if (!cell.content.eq(baseContent)) tr.replace(tr.mapping.map(pos + 1), tr.mapping.map(pos + cell.nodeSize - 1), new Slice(baseContent, 0, 0));
		});
		if (tr.docChanged) dispatch(tr);
	}
	return true;
}
/**
* Get a rectangular area of cells from a slice, or null if the outer
* nodes of the slice aren't table cells or rows.
*
* @internal
*/
function pastedCells(slice) {
	if (slice.size === 0) return null;
	let { content, openStart, openEnd } = slice;
	while (content.childCount == 1 && (openStart > 0 && openEnd > 0 || content.child(0).type.spec.tableRole == "table")) {
		openStart--;
		openEnd--;
		content = content.child(0).content;
	}
	const first = content.child(0);
	const role = first.type.spec.tableRole;
	const schema = first.type.schema, rows = [];
	if (role == "row") for (let i = 0; i < content.childCount; i++) {
		let cells = content.child(i).content;
		const left = i ? 0 : Math.max(0, openStart - 1);
		const right = i < content.childCount - 1 ? 0 : Math.max(0, openEnd - 1);
		if (left || right) cells = fitSlice(tableNodeTypes(schema).row, new Slice(cells, left, right)).content;
		rows.push(cells);
	}
	else if (role == "cell" || role == "header_cell") rows.push(openStart || openEnd ? fitSlice(tableNodeTypes(schema).row, new Slice(content, openStart, openEnd)).content : content);
	else return null;
	return ensureRectangular(schema, rows);
}
function ensureRectangular(schema, rows) {
	const widths = [];
	for (let i = 0; i < rows.length; i++) {
		const row = rows[i];
		for (let j = row.childCount - 1; j >= 0; j--) {
			const { rowspan, colspan } = row.child(j).attrs;
			for (let r = i; r < i + rowspan; r++) widths[r] = (widths[r] || 0) + colspan;
		}
	}
	let width = 0;
	for (let r = 0; r < widths.length; r++) width = Math.max(width, widths[r]);
	for (let r = 0; r < widths.length; r++) {
		if (r >= rows.length) rows.push(Fragment$1.empty);
		if (widths[r] < width) {
			const empty = tableNodeTypes(schema).cell.createAndFill();
			const cells = [];
			for (let i = widths[r]; i < width; i++) cells.push(empty);
			rows[r] = rows[r].append(Fragment$1.from(cells));
		}
	}
	return {
		height: rows.length,
		width,
		rows
	};
}
function fitSlice(nodeType, slice) {
	const node = nodeType.createAndFill();
	return new Transform(node).replace(0, node.content.size, slice).doc;
}
/**
* Clip or extend (repeat) the given set of cells to cover the given
* width and height. Will clip rowspan/colspan cells at the edges when
* they stick out.
*
* @internal
*/
function clipCells({ width, height, rows }, newWidth, newHeight) {
	if (width != newWidth) {
		const added = [];
		const newRows = [];
		for (let row = 0; row < rows.length; row++) {
			const frag = rows[row], cells = [];
			for (let col = added[row] || 0, i = 0; col < newWidth; i++) {
				let cell = frag.child(i % frag.childCount);
				if (col + cell.attrs.colspan > newWidth) cell = cell.type.createChecked(removeColSpan(cell.attrs, cell.attrs.colspan, col + cell.attrs.colspan - newWidth), cell.content);
				cells.push(cell);
				col += cell.attrs.colspan;
				for (let j = 1; j < cell.attrs.rowspan; j++) added[row + j] = (added[row + j] || 0) + cell.attrs.colspan;
			}
			newRows.push(Fragment$1.from(cells));
		}
		rows = newRows;
		width = newWidth;
	}
	if (height != newHeight) {
		const newRows = [];
		for (let row = 0, i = 0; row < newHeight; row++, i++) {
			const cells = [], source = rows[i % height];
			for (let j = 0; j < source.childCount; j++) {
				let cell = source.child(j);
				if (row + cell.attrs.rowspan > newHeight) cell = cell.type.create({
					...cell.attrs,
					rowspan: Math.max(1, newHeight - cell.attrs.rowspan)
				}, cell.content);
				cells.push(cell);
			}
			newRows.push(Fragment$1.from(cells));
		}
		rows = newRows;
		height = newHeight;
	}
	return {
		width,
		height,
		rows
	};
}
function growTable(tr, map, table, start, width, height, mapFrom) {
	const schema = tr.doc.type.schema;
	const types = tableNodeTypes(schema);
	let empty;
	let emptyHead;
	if (width > map.width) for (let row = 0, rowEnd = 0; row < map.height; row++) {
		const rowNode = table.child(row);
		rowEnd += rowNode.nodeSize;
		const cells = [];
		let add;
		if (rowNode.lastChild == null || rowNode.lastChild.type == types.cell) add = empty || (empty = types.cell.createAndFill());
		else add = emptyHead || (emptyHead = types.header_cell.createAndFill());
		for (let i = map.width; i < width; i++) cells.push(add);
		tr.insert(tr.mapping.slice(mapFrom).map(rowEnd - 1 + start), cells);
	}
	if (height > map.height) {
		const cells = [];
		for (let i = 0, start$1 = (map.height - 1) * map.width; i < Math.max(map.width, width); i++) {
			const header = i >= map.width ? false : table.nodeAt(map.map[start$1 + i]).type == types.header_cell;
			cells.push(header ? emptyHead || (emptyHead = types.header_cell.createAndFill()) : empty || (empty = types.cell.createAndFill()));
		}
		const emptyRow = types.row.create(null, Fragment$1.from(cells)), rows = [];
		for (let i = map.height; i < height; i++) rows.push(emptyRow);
		tr.insert(tr.mapping.slice(mapFrom).map(start + table.nodeSize - 2), rows);
	}
	return !!(empty || emptyHead);
}
function isolateHorizontal(tr, map, table, start, left, right, top, mapFrom) {
	if (top == 0 || top == map.height) return false;
	let found = false;
	for (let col = left; col < right; col++) {
		const index = top * map.width + col, pos = map.map[index];
		if (map.map[index - map.width] == pos) {
			found = true;
			const cell = table.nodeAt(pos);
			const { top: cellTop, left: cellLeft } = map.findCell(pos);
			tr.setNodeMarkup(tr.mapping.slice(mapFrom).map(pos + start), null, {
				...cell.attrs,
				rowspan: top - cellTop
			});
			tr.insert(tr.mapping.slice(mapFrom).map(map.positionAt(top, cellLeft, table)), cell.type.createAndFill({
				...cell.attrs,
				rowspan: cellTop + cell.attrs.rowspan - top
			}));
			col += cell.attrs.colspan - 1;
		}
	}
	return found;
}
function isolateVertical(tr, map, table, start, top, bottom, left, mapFrom) {
	if (left == 0 || left == map.width) return false;
	let found = false;
	for (let row = top; row < bottom; row++) {
		const index = row * map.width + left, pos = map.map[index];
		if (map.map[index - 1] == pos) {
			found = true;
			const cell = table.nodeAt(pos);
			const cellLeft = map.colCount(pos);
			const updatePos = tr.mapping.slice(mapFrom).map(pos + start);
			tr.setNodeMarkup(updatePos, null, removeColSpan(cell.attrs, left - cellLeft, cell.attrs.colspan - (left - cellLeft)));
			tr.insert(updatePos + cell.nodeSize, cell.type.createAndFill(removeColSpan(cell.attrs, 0, left - cellLeft)));
			row += cell.attrs.rowspan - 1;
		}
	}
	return found;
}
/**
* Insert the given set of cells (as returned by `pastedCells`) into a
* table, at the position pointed at by rect.
*
* @internal
*/
function insertCells(state, dispatch, tableStart, rect, cells) {
	let table = tableStart ? state.doc.nodeAt(tableStart - 1) : state.doc;
	if (!table) throw new Error("No table found");
	let map = TableMap.get(table);
	const { top, left } = rect;
	const right = left + cells.width, bottom = top + cells.height;
	const tr = state.tr;
	let mapFrom = 0;
	function recomp() {
		table = tableStart ? tr.doc.nodeAt(tableStart - 1) : tr.doc;
		if (!table) throw new Error("No table found");
		map = TableMap.get(table);
		mapFrom = tr.mapping.maps.length;
	}
	if (growTable(tr, map, table, tableStart, right, bottom, mapFrom)) recomp();
	if (isolateHorizontal(tr, map, table, tableStart, left, right, top, mapFrom)) recomp();
	if (isolateHorizontal(tr, map, table, tableStart, left, right, bottom, mapFrom)) recomp();
	if (isolateVertical(tr, map, table, tableStart, top, bottom, left, mapFrom)) recomp();
	if (isolateVertical(tr, map, table, tableStart, top, bottom, right, mapFrom)) recomp();
	for (let row = top; row < bottom; row++) {
		const from = map.positionAt(row, left, table), to = map.positionAt(row, right, table);
		tr.replace(tr.mapping.slice(mapFrom).map(from + tableStart), tr.mapping.slice(mapFrom).map(to + tableStart), new Slice(cells.rows[row - top], 0, 0));
	}
	recomp();
	tr.setSelection(new CellSelection(tr.doc.resolve(tableStart + map.positionAt(top, left, table)), tr.doc.resolve(tableStart + map.positionAt(bottom - 1, right - 1, table))));
	dispatch(tr);
}
var handleKeyDown$1 = keydownHandler({
	ArrowLeft: arrow$1("horiz", -1),
	ArrowRight: arrow$1("horiz", 1),
	ArrowUp: arrow$1("vert", -1),
	ArrowDown: arrow$1("vert", 1),
	"Shift-ArrowLeft": shiftArrow("horiz", -1),
	"Shift-ArrowRight": shiftArrow("horiz", 1),
	"Shift-ArrowUp": shiftArrow("vert", -1),
	"Shift-ArrowDown": shiftArrow("vert", 1),
	Backspace: deleteCellSelection,
	"Mod-Backspace": deleteCellSelection,
	Delete: deleteCellSelection,
	"Mod-Delete": deleteCellSelection
});
function maybeSetSelection(state, dispatch, selection) {
	if (selection.eq(state.selection)) return false;
	if (dispatch) dispatch(state.tr.setSelection(selection).scrollIntoView());
	return true;
}
/**
* @internal
*/
function arrow$1(axis, dir) {
	return (state, dispatch, view) => {
		if (!view) return false;
		const sel = state.selection;
		if (sel instanceof CellSelection) return maybeSetSelection(state, dispatch, Selection$1.near(sel.$headCell, dir));
		if (axis != "horiz" && !sel.empty) return false;
		const end = atEndOfCell(view, axis, dir);
		if (end == null) return false;
		if (axis == "horiz") return maybeSetSelection(state, dispatch, Selection$1.near(state.doc.resolve(sel.head + dir), dir));
		else {
			const $cell = state.doc.resolve(end);
			const $next = nextCell($cell, axis, dir);
			let newSel;
			if ($next) newSel = Selection$1.near($next, 1);
			else if (dir < 0) newSel = Selection$1.near(state.doc.resolve($cell.before(-1)), -1);
			else newSel = Selection$1.near(state.doc.resolve($cell.after(-1)), 1);
			return maybeSetSelection(state, dispatch, newSel);
		}
	};
}
function shiftArrow(axis, dir) {
	return (state, dispatch, view) => {
		if (!view) return false;
		const sel = state.selection;
		let cellSel;
		if (sel instanceof CellSelection) cellSel = sel;
		else {
			const end = atEndOfCell(view, axis, dir);
			if (end == null) return false;
			cellSel = new CellSelection(state.doc.resolve(end));
		}
		const $head = nextCell(cellSel.$headCell, axis, dir);
		if (!$head) return false;
		return maybeSetSelection(state, dispatch, new CellSelection(cellSel.$anchorCell, $head));
	};
}
function handleTripleClick(view, pos) {
	const doc = view.state.doc, $cell = cellAround(doc.resolve(pos));
	if (!$cell) return false;
	view.dispatch(view.state.tr.setSelection(new CellSelection($cell)));
	return true;
}
/**
* @public
*/
function handlePaste(view, _, slice) {
	if (!isInTable(view.state)) return false;
	let cells = pastedCells(slice);
	const sel = view.state.selection;
	if (sel instanceof CellSelection) {
		if (!cells) cells = {
			width: 1,
			height: 1,
			rows: [Fragment$1.from(fitSlice(tableNodeTypes(view.state.schema).cell, slice))]
		};
		const table = sel.$anchorCell.node(-1);
		const start = sel.$anchorCell.start(-1);
		const rect = TableMap.get(table).rectBetween(sel.$anchorCell.pos - start, sel.$headCell.pos - start);
		cells = clipCells(cells, rect.right - rect.left, rect.bottom - rect.top);
		insertCells(view.state, view.dispatch, start, rect, cells);
		return true;
	} else if (cells) {
		const $cell = selectionCell(view.state);
		const start = $cell.start(-1);
		insertCells(view.state, view.dispatch, start, TableMap.get($cell.node(-1)).findCell($cell.pos - start), cells);
		return true;
	} else return false;
}
function handleMouseDown$1(view, startEvent) {
	var _cellUnderMouse;
	if (startEvent.button != 0) return;
	if (startEvent.ctrlKey || startEvent.metaKey) return;
	const startDOMCell = domInCell(view, startEvent.target);
	let $anchor;
	if (startEvent.shiftKey && view.state.selection instanceof CellSelection) {
		setCellSelection(view.state.selection.$anchorCell, startEvent);
		startEvent.preventDefault();
	} else if (startEvent.shiftKey && startDOMCell && ($anchor = cellAround(view.state.selection.$anchor)) != null && ((_cellUnderMouse = cellUnderMouse(view, startEvent)) === null || _cellUnderMouse === void 0 ? void 0 : _cellUnderMouse.pos) != $anchor.pos) {
		setCellSelection($anchor, startEvent);
		startEvent.preventDefault();
	} else if (!startDOMCell) return;
	function setCellSelection($anchor$1, event) {
		let $head = cellUnderMouse(view, event);
		const starting = tableEditingKey.getState(view.state) == null;
		if (!$head || !inSameTable($anchor$1, $head)) if (starting) $head = $anchor$1;
		else return;
		const selection = new CellSelection($anchor$1, $head);
		if (starting || !view.state.selection.eq(selection)) {
			const tr = view.state.tr.setSelection(selection);
			if (starting) tr.setMeta(tableEditingKey, $anchor$1.pos);
			view.dispatch(tr);
		}
	}
	function stop() {
		view.root.removeEventListener("mouseup", stop);
		view.root.removeEventListener("dragstart", stop);
		view.root.removeEventListener("mousemove", move);
		if (tableEditingKey.getState(view.state) != null) view.dispatch(view.state.tr.setMeta(tableEditingKey, -1));
	}
	function move(_event) {
		const event = _event;
		const anchor = tableEditingKey.getState(view.state);
		let $anchor$1;
		if (anchor != null) $anchor$1 = view.state.doc.resolve(anchor);
		else if (domInCell(view, event.target) != startDOMCell) {
			$anchor$1 = cellUnderMouse(view, startEvent);
			if (!$anchor$1) return stop();
		}
		if ($anchor$1) setCellSelection($anchor$1, event);
	}
	view.root.addEventListener("mouseup", stop);
	view.root.addEventListener("dragstart", stop);
	view.root.addEventListener("mousemove", move);
}
function atEndOfCell(view, axis, dir) {
	if (!(view.state.selection instanceof TextSelection)) return null;
	const { $head } = view.state.selection;
	for (let d = $head.depth - 1; d >= 0; d--) {
		const parent = $head.node(d);
		if ((dir < 0 ? $head.index(d) : $head.indexAfter(d)) != (dir < 0 ? 0 : parent.childCount)) return null;
		if (parent.type.spec.tableRole == "cell" || parent.type.spec.tableRole == "header_cell") {
			const cellPos = $head.before(d);
			const dirStr = axis == "vert" ? dir > 0 ? "down" : "up" : dir > 0 ? "right" : "left";
			return view.endOfTextblock(dirStr) ? cellPos : null;
		}
	}
	return null;
}
function domInCell(view, dom) {
	for (; dom && dom != view.dom; dom = dom.parentNode) if (dom.nodeName == "TD" || dom.nodeName == "TH") return dom;
	return null;
}
function cellUnderMouse(view, event) {
	const mousePos = view.posAtCoords({
		left: event.clientX,
		top: event.clientY
	});
	if (!mousePos) return null;
	let { inside, pos } = mousePos;
	return inside >= 0 && cellAround(view.state.doc.resolve(inside)) || cellAround(view.state.doc.resolve(pos));
}
/**
* @public
*/
var TableView$1 = class {
	constructor(node, defaultCellMinWidth) {
		this.node = node;
		this.defaultCellMinWidth = defaultCellMinWidth;
		this.dom = document.createElement("div");
		this.dom.className = "tableWrapper";
		this.table = this.dom.appendChild(document.createElement("table"));
		this.table.style.setProperty("--default-cell-min-width", `${defaultCellMinWidth}px`);
		this.colgroup = this.table.appendChild(document.createElement("colgroup"));
		updateColumnsOnResize(node, this.colgroup, this.table, defaultCellMinWidth);
		this.contentDOM = this.table.appendChild(document.createElement("tbody"));
	}
	update(node) {
		if (node.type != this.node.type) return false;
		this.node = node;
		updateColumnsOnResize(node, this.colgroup, this.table, this.defaultCellMinWidth);
		return true;
	}
	ignoreMutation(record) {
		return record.type == "attributes" && (record.target == this.table || this.colgroup.contains(record.target));
	}
};
/**
* @public
*/
function updateColumnsOnResize(node, colgroup, table, defaultCellMinWidth, overrideCol, overrideValue) {
	let totalWidth = 0;
	let fixedWidth = true;
	let nextDOM = colgroup.firstChild;
	const row = node.firstChild;
	if (!row) return;
	for (let i = 0, col = 0; i < row.childCount; i++) {
		const { colspan, colwidth } = row.child(i).attrs;
		for (let j = 0; j < colspan; j++, col++) {
			const hasWidth = overrideCol == col ? overrideValue : colwidth && colwidth[j];
			const cssWidth = hasWidth ? hasWidth + "px" : "";
			totalWidth += hasWidth || defaultCellMinWidth;
			if (!hasWidth) fixedWidth = false;
			if (!nextDOM) {
				const col$1 = document.createElement("col");
				col$1.style.width = cssWidth;
				colgroup.appendChild(col$1);
			} else {
				if (nextDOM.style.width != cssWidth) nextDOM.style.width = cssWidth;
				nextDOM = nextDOM.nextSibling;
			}
		}
	}
	while (nextDOM) {
		var _nextDOM$parentNode;
		const after = nextDOM.nextSibling;
		(_nextDOM$parentNode = nextDOM.parentNode) === null || _nextDOM$parentNode === void 0 || _nextDOM$parentNode.removeChild(nextDOM);
		nextDOM = after;
	}
	if (fixedWidth) {
		table.style.width = totalWidth + "px";
		table.style.minWidth = "";
	} else {
		table.style.width = "";
		table.style.minWidth = totalWidth + "px";
	}
}
/**
* @public
*/
var columnResizingPluginKey = new PluginKey("tableColumnResizing");
/**
* @public
*/
function columnResizing({ handleWidth = 5, cellMinWidth = 25, defaultCellMinWidth = 100, View = TableView$1, lastColumnResizable = true } = {}) {
	const plugin = new Plugin({
		key: columnResizingPluginKey,
		state: {
			init(_, state) {
				var _plugin$spec;
				const nodeViews = (_plugin$spec = plugin.spec) === null || _plugin$spec === void 0 || (_plugin$spec = _plugin$spec.props) === null || _plugin$spec === void 0 ? void 0 : _plugin$spec.nodeViews;
				const tableName = tableNodeTypes(state.schema).table.name;
				if (View && nodeViews) nodeViews[tableName] = (node, view) => {
					return new View(node, defaultCellMinWidth, view);
				};
				return new ResizeState(-1, false);
			},
			apply(tr, prev) {
				return prev.apply(tr);
			}
		},
		props: {
			attributes: (state) => {
				const pluginState = columnResizingPluginKey.getState(state);
				return pluginState && pluginState.activeHandle > -1 ? { class: "resize-cursor" } : {};
			},
			handleDOMEvents: {
				mousemove: (view, event) => {
					handleMouseMove(view, event, handleWidth, lastColumnResizable);
				},
				mouseleave: (view) => {
					handleMouseLeave(view);
				},
				mousedown: (view, event) => {
					handleMouseDown(view, event, cellMinWidth, defaultCellMinWidth);
				}
			},
			decorations: (state) => {
				const pluginState = columnResizingPluginKey.getState(state);
				if (pluginState && pluginState.activeHandle > -1) return handleDecorations(state, pluginState.activeHandle);
			},
			nodeViews: {}
		}
	});
	return plugin;
}
/**
* @public
*/
var ResizeState = class ResizeState {
	constructor(activeHandle, dragging) {
		this.activeHandle = activeHandle;
		this.dragging = dragging;
	}
	apply(tr) {
		const state = this;
		const action = tr.getMeta(columnResizingPluginKey);
		if (action && action.setHandle != null) return new ResizeState(action.setHandle, false);
		if (action && action.setDragging !== void 0) return new ResizeState(state.activeHandle, action.setDragging);
		if (state.activeHandle > -1 && tr.docChanged) {
			let handle = tr.mapping.map(state.activeHandle, -1);
			if (!pointsAtCell(tr.doc.resolve(handle))) handle = -1;
			return new ResizeState(handle, state.dragging);
		}
		return state;
	}
};
function handleMouseMove(view, event, handleWidth, lastColumnResizable) {
	if (!view.editable) return;
	const pluginState = columnResizingPluginKey.getState(view.state);
	if (!pluginState) return;
	if (!pluginState.dragging) {
		const target = domCellAround(event.target);
		let cell = -1;
		if (target) {
			const { left, right } = target.getBoundingClientRect();
			if (event.clientX - left <= handleWidth) cell = edgeCell(view, event, "left", handleWidth);
			else if (right - event.clientX <= handleWidth) cell = edgeCell(view, event, "right", handleWidth);
		}
		if (cell != pluginState.activeHandle) {
			if (!lastColumnResizable && cell !== -1) {
				const $cell = view.state.doc.resolve(cell);
				const table = $cell.node(-1);
				const map = TableMap.get(table);
				const tableStart = $cell.start(-1);
				if (map.colCount($cell.pos - tableStart) + $cell.nodeAfter.attrs.colspan - 1 == map.width - 1) return;
			}
			updateHandle(view, cell);
		}
	}
}
function handleMouseLeave(view) {
	if (!view.editable) return;
	const pluginState = columnResizingPluginKey.getState(view.state);
	if (pluginState && pluginState.activeHandle > -1 && !pluginState.dragging) updateHandle(view, -1);
}
function handleMouseDown(view, event, cellMinWidth, defaultCellMinWidth) {
	var _view$dom$ownerDocume;
	if (!view.editable) return false;
	const win = (_view$dom$ownerDocume = view.dom.ownerDocument.defaultView) !== null && _view$dom$ownerDocume !== void 0 ? _view$dom$ownerDocume : window;
	const pluginState = columnResizingPluginKey.getState(view.state);
	if (!pluginState || pluginState.activeHandle == -1 || pluginState.dragging) return false;
	const cell = view.state.doc.nodeAt(pluginState.activeHandle);
	const width = currentColWidth(view, pluginState.activeHandle, cell.attrs);
	view.dispatch(view.state.tr.setMeta(columnResizingPluginKey, { setDragging: {
		startX: event.clientX,
		startWidth: width
	} }));
	function finish(event$1) {
		win.removeEventListener("mouseup", finish);
		win.removeEventListener("mousemove", move);
		const pluginState$1 = columnResizingPluginKey.getState(view.state);
		if (pluginState$1 === null || pluginState$1 === void 0 ? void 0 : pluginState$1.dragging) {
			updateColumnWidth(view, pluginState$1.activeHandle, draggedWidth(pluginState$1.dragging, event$1, cellMinWidth));
			view.dispatch(view.state.tr.setMeta(columnResizingPluginKey, { setDragging: null }));
		}
	}
	function move(event$1) {
		if (!event$1.which) return finish(event$1);
		const pluginState$1 = columnResizingPluginKey.getState(view.state);
		if (!pluginState$1) return;
		if (pluginState$1.dragging) {
			const dragged = draggedWidth(pluginState$1.dragging, event$1, cellMinWidth);
			displayColumnWidth(view, pluginState$1.activeHandle, dragged, defaultCellMinWidth);
		}
	}
	displayColumnWidth(view, pluginState.activeHandle, width, defaultCellMinWidth);
	win.addEventListener("mouseup", finish);
	win.addEventListener("mousemove", move);
	event.preventDefault();
	return true;
}
function currentColWidth(view, cellPos, { colspan, colwidth }) {
	const width = colwidth && colwidth[colwidth.length - 1];
	if (width) return width;
	const dom = view.domAtPos(cellPos);
	let domWidth = dom.node.childNodes[dom.offset].offsetWidth, parts = colspan;
	if (colwidth) {
		for (let i = 0; i < colspan; i++) if (colwidth[i]) {
			domWidth -= colwidth[i];
			parts--;
		}
	}
	return domWidth / parts;
}
function domCellAround(target) {
	while (target && target.nodeName != "TD" && target.nodeName != "TH") target = target.classList && target.classList.contains("ProseMirror") ? null : target.parentNode;
	return target;
}
function edgeCell(view, event, side, handleWidth) {
	const offset = side == "right" ? -handleWidth : handleWidth;
	const found = view.posAtCoords({
		left: event.clientX + offset,
		top: event.clientY
	});
	if (!found) return -1;
	const { pos } = found;
	const $cell = cellAround(view.state.doc.resolve(pos));
	if (!$cell) return -1;
	if (side == "right") return $cell.pos;
	const map = TableMap.get($cell.node(-1)), start = $cell.start(-1);
	const index = map.map.indexOf($cell.pos - start);
	return index % map.width == 0 ? -1 : start + map.map[index - 1];
}
function draggedWidth(dragging, event, resizeMinWidth) {
	const offset = event.clientX - dragging.startX;
	return Math.max(resizeMinWidth, dragging.startWidth + offset);
}
function updateHandle(view, value) {
	view.dispatch(view.state.tr.setMeta(columnResizingPluginKey, { setHandle: value }));
}
function updateColumnWidth(view, cell, width) {
	const $cell = view.state.doc.resolve(cell);
	const table = $cell.node(-1), map = TableMap.get(table), start = $cell.start(-1);
	const col = map.colCount($cell.pos - start) + $cell.nodeAfter.attrs.colspan - 1;
	const tr = view.state.tr;
	for (let row = 0; row < map.height; row++) {
		const mapIndex = row * map.width + col;
		if (row && map.map[mapIndex] == map.map[mapIndex - map.width]) continue;
		const pos = map.map[mapIndex];
		const attrs = table.nodeAt(pos).attrs;
		const index = attrs.colspan == 1 ? 0 : col - map.colCount(pos);
		if (attrs.colwidth && attrs.colwidth[index] == width) continue;
		const colwidth = attrs.colwidth ? attrs.colwidth.slice() : zeroes(attrs.colspan);
		colwidth[index] = width;
		tr.setNodeMarkup(start + pos, null, {
			...attrs,
			colwidth
		});
	}
	if (tr.docChanged) view.dispatch(tr);
}
function displayColumnWidth(view, cell, width, defaultCellMinWidth) {
	const $cell = view.state.doc.resolve(cell);
	const table = $cell.node(-1), start = $cell.start(-1);
	const col = TableMap.get(table).colCount($cell.pos - start) + $cell.nodeAfter.attrs.colspan - 1;
	let dom = view.domAtPos($cell.start(-1)).node;
	while (dom && dom.nodeName != "TABLE") dom = dom.parentNode;
	if (!dom) return;
	updateColumnsOnResize(table, dom.firstChild, dom, defaultCellMinWidth, col, width);
}
function zeroes(n) {
	return Array(n).fill(0);
}
function handleDecorations(state, cell) {
	const decorations = [];
	const $cell = state.doc.resolve(cell);
	const table = $cell.node(-1);
	if (!table) return DecorationSet.empty;
	const map = TableMap.get(table);
	const start = $cell.start(-1);
	const col = map.colCount($cell.pos - start) + $cell.nodeAfter.attrs.colspan - 1;
	for (let row = 0; row < map.height; row++) {
		const index = col + row * map.width;
		if ((col == map.width - 1 || map.map[index] != map.map[index + 1]) && (row == 0 || map.map[index] != map.map[index - map.width])) {
			var _columnResizingPlugin;
			const cellPos = map.map[index];
			const pos = start + cellPos + table.nodeAt(cellPos).nodeSize - 1;
			const dom = document.createElement("div");
			dom.className = "column-resize-handle";
			if ((_columnResizingPlugin = columnResizingPluginKey.getState(state)) === null || _columnResizingPlugin === void 0 ? void 0 : _columnResizingPlugin.dragging) decorations.push(Decoration.node(start + cellPos, start + cellPos + table.nodeAt(cellPos).nodeSize, { class: "column-resize-dragging" }));
			decorations.push(Decoration.widget(pos, dom));
		}
	}
	return DecorationSet.create(state.doc, decorations);
}
/**
* Creates a [plugin](http://prosemirror.net/docs/ref/#state.Plugin)
* that, when added to an editor, enables cell-selection, handles
* cell-based copy/paste, and makes sure tables stay well-formed (each
* row has the same width, and cells don't overlap).
*
* You should probably put this plugin near the end of your array of
* plugins, since it handles mouse and arrow key events in tables
* rather broadly, and other plugins, like the gap cursor or the
* column-width dragging plugin, might want to get a turn first to
* perform more specific behavior.
*
* @public
*/
function tableEditing({ allowTableNodeSelection = false } = {}) {
	return new Plugin({
		key: tableEditingKey,
		state: {
			init() {
				return null;
			},
			apply(tr, cur) {
				const set = tr.getMeta(tableEditingKey);
				if (set != null) return set == -1 ? null : set;
				if (cur == null || !tr.docChanged) return cur;
				const { deleted, pos } = tr.mapping.mapResult(cur);
				return deleted ? null : pos;
			}
		},
		props: {
			decorations: drawCellSelection,
			handleDOMEvents: { mousedown: handleMouseDown$1 },
			createSelectionBetween(view) {
				return tableEditingKey.getState(view.state) != null ? view.state.selection : null;
			},
			handleTripleClick,
			handleKeyDown: handleKeyDown$1,
			handlePaste
		},
		appendTransaction(_, oldState, state) {
			return normalizeSelection(state, fixTables(state, oldState), allowTableNodeSelection);
		}
	});
}
//#endregion
//#region node_modules/@tiptap/vue-3/dist/menus/index.js
function combineDOMRects(rect1, rect2) {
	const top = Math.min(rect1.top, rect2.top);
	const bottom = Math.max(rect1.bottom, rect2.bottom);
	const left = Math.min(rect1.left, rect2.left);
	const width = Math.max(rect1.right, rect2.right) - left;
	const height = bottom - top;
	return new DOMRect(left, top, width, height);
}
var BubbleMenuView = class {
	constructor({ editor, element, view, pluginKey = "bubbleMenu", updateDelay = 250, resizeDelay = 60, shouldShow, appendTo, getReferencedVirtualElement, options }) {
		this.preventHide = false;
		this.isVisible = false;
		this.scrollTarget = window;
		this.floatingUIOptions = {
			strategy: "absolute",
			placement: "top",
			offset: 8,
			flip: {},
			shift: {},
			arrow: false,
			size: false,
			autoPlacement: false,
			hide: false,
			inline: false,
			onShow: void 0,
			onHide: void 0,
			onUpdate: void 0,
			onDestroy: void 0
		};
		this.shouldShow = ({ view, state, from, to }) => {
			const { doc, selection } = state;
			const { empty } = selection;
			const isEmptyTextBlock = !doc.textBetween(from, to).length && isTextSelection(state.selection);
			const isChildOfMenu = this.element.contains(document.activeElement);
			if (!(view.hasFocus() || isChildOfMenu) || empty || isEmptyTextBlock || !this.editor.isEditable) return false;
			return true;
		};
		this.mousedownHandler = () => {
			this.preventHide = true;
		};
		this.dragstartHandler = () => {
			this.hide();
		};
		/**
		* Handles the window resize event to update the position of the bubble menu.
		* It uses a debounce mechanism to prevent excessive updates.
		* The delay is defined by the `resizeDelay` property.
		*/
		this.resizeHandler = () => {
			if (this.resizeDebounceTimer) clearTimeout(this.resizeDebounceTimer);
			this.resizeDebounceTimer = window.setTimeout(() => {
				this.updatePosition();
			}, this.resizeDelay);
		};
		this.focusHandler = () => {
			setTimeout(() => this.update(this.editor.view));
		};
		this.blurHandler = ({ event }) => {
			var _a;
			if (this.editor.isDestroyed) {
				this.destroy();
				return;
			}
			if (this.preventHide) {
				this.preventHide = false;
				return;
			}
			if ((event == null ? void 0 : event.relatedTarget) && ((_a = this.element.parentNode) == null ? void 0 : _a.contains(event.relatedTarget))) return;
			if ((event == null ? void 0 : event.relatedTarget) === this.editor.view.dom) return;
			this.hide();
		};
		this.handleDebouncedUpdate = (view, oldState) => {
			const selectionChanged = !(oldState == null ? void 0 : oldState.selection.eq(view.state.selection));
			const docChanged = !(oldState == null ? void 0 : oldState.doc.eq(view.state.doc));
			if (!selectionChanged && !docChanged) return;
			if (this.updateDebounceTimer) clearTimeout(this.updateDebounceTimer);
			this.updateDebounceTimer = window.setTimeout(() => {
				this.updateHandler(view, selectionChanged, docChanged, oldState);
			}, this.updateDelay);
		};
		this.updateHandler = (view, selectionChanged, docChanged, oldState) => {
			const { composing } = view;
			if (composing || !selectionChanged && !docChanged) return;
			if (!this.getShouldShow(oldState)) {
				this.hide();
				return;
			}
			this.show();
			this.updatePosition();
		};
		/**
		* Handles the transaction event to update the position of the bubble menu.
		* This allows external code to trigger a position update via:
		* `editor.view.dispatch(editor.state.tr.setMeta(pluginKey, 'updatePosition'))`
		* The `pluginKey` defaults to `bubbleMenu`
		*/
		this.transactionHandler = ({ transaction: tr }) => {
			const meta = tr.getMeta(this.pluginKey);
			if (meta === "updatePosition") this.updatePosition();
			else if (meta && typeof meta === "object" && meta.type === "updateOptions") this.updateOptions(meta.options);
			else if (meta === "hide") this.hide();
			else if (meta === "show") {
				this.updatePosition();
				this.show();
			}
		};
		var _a;
		this.editor = editor;
		this.element = element;
		this.view = view;
		this.pluginKey = pluginKey;
		this.updateDelay = updateDelay;
		this.resizeDelay = resizeDelay;
		this.appendTo = appendTo;
		this.scrollTarget = (_a = options == null ? void 0 : options.scrollTarget) != null ? _a : window;
		this.getReferencedVirtualElement = getReferencedVirtualElement;
		this.floatingUIOptions = {
			...this.floatingUIOptions,
			...options
		};
		this.element.tabIndex = 0;
		if (shouldShow) this.shouldShow = shouldShow;
		this.element.addEventListener("mousedown", this.mousedownHandler, { capture: true });
		this.view.dom.addEventListener("dragstart", this.dragstartHandler);
		this.editor.on("focus", this.focusHandler);
		this.editor.on("blur", this.blurHandler);
		this.editor.on("transaction", this.transactionHandler);
		window.addEventListener("resize", this.resizeHandler);
		this.scrollTarget.addEventListener("scroll", this.resizeHandler);
		this.update(view, view.state);
		if (this.getShouldShow()) {
			this.show();
			this.updatePosition();
		}
	}
	get middlewares() {
		const middlewares = [];
		if (this.floatingUIOptions.flip) middlewares.push(flip(typeof this.floatingUIOptions.flip !== "boolean" ? this.floatingUIOptions.flip : void 0));
		if (this.floatingUIOptions.shift) middlewares.push(shift(typeof this.floatingUIOptions.shift !== "boolean" ? this.floatingUIOptions.shift : void 0));
		if (this.floatingUIOptions.offset) middlewares.push(offset(typeof this.floatingUIOptions.offset !== "boolean" ? this.floatingUIOptions.offset : void 0));
		if (this.floatingUIOptions.arrow) middlewares.push(arrow$2(this.floatingUIOptions.arrow));
		if (this.floatingUIOptions.size) middlewares.push(size(typeof this.floatingUIOptions.size !== "boolean" ? this.floatingUIOptions.size : void 0));
		if (this.floatingUIOptions.autoPlacement) middlewares.push(autoPlacement(typeof this.floatingUIOptions.autoPlacement !== "boolean" ? this.floatingUIOptions.autoPlacement : void 0));
		if (this.floatingUIOptions.hide) middlewares.push(hide(typeof this.floatingUIOptions.hide !== "boolean" ? this.floatingUIOptions.hide : void 0));
		if (this.floatingUIOptions.inline) middlewares.push(inline(typeof this.floatingUIOptions.inline !== "boolean" ? this.floatingUIOptions.inline : void 0));
		return middlewares;
	}
	get virtualElement() {
		var _a, _b, _c;
		const { selection } = this.editor.state;
		const referencedVirtualElement = (_a = this.getReferencedVirtualElement) == null ? void 0 : _a.call(this);
		if (referencedVirtualElement) return referencedVirtualElement;
		if (!((_c = (_b = this.view) == null ? void 0 : _b.dom) == null ? void 0 : _c.parentNode)) return;
		const domRect = posToDOMRect(this.view, selection.from, selection.to);
		let virtualElement = {
			getBoundingClientRect: () => domRect,
			getClientRects: () => [domRect]
		};
		if (selection instanceof NodeSelection) {
			let node = this.view.nodeDOM(selection.from);
			const nodeViewWrapper = node.dataset.nodeViewWrapper ? node : node.querySelector("[data-node-view-wrapper]");
			if (nodeViewWrapper) node = nodeViewWrapper;
			if (node) virtualElement = {
				getBoundingClientRect: () => node.getBoundingClientRect(),
				getClientRects: () => [node.getBoundingClientRect()]
			};
		}
		if (selection instanceof CellSelection) {
			const { $anchorCell, $headCell } = selection;
			const from = $anchorCell ? $anchorCell.pos : $headCell.pos;
			const to = $headCell ? $headCell.pos : $anchorCell.pos;
			const fromDOM = this.view.nodeDOM(from);
			const toDOM = this.view.nodeDOM(to);
			if (!fromDOM || !toDOM) return;
			const clientRect = fromDOM === toDOM ? fromDOM.getBoundingClientRect() : combineDOMRects(fromDOM.getBoundingClientRect(), toDOM.getBoundingClientRect());
			virtualElement = {
				getBoundingClientRect: () => clientRect,
				getClientRects: () => [clientRect]
			};
		}
		return virtualElement;
	}
	updatePosition() {
		if (!this.isVisible) return;
		const virtualElement = this.virtualElement;
		if (!virtualElement) return;
		computePosition(virtualElement, this.element, {
			placement: this.floatingUIOptions.placement,
			strategy: this.floatingUIOptions.strategy,
			middleware: this.middlewares
		}).then(({ x, y, strategy, middlewareData }) => {
			var _a, _b;
			if (!this.isVisible || this.editor.isDestroyed || !this.element.isConnected) return;
			if (((_a = middlewareData.hide) == null ? void 0 : _a.referenceHidden) || ((_b = middlewareData.hide) == null ? void 0 : _b.escaped)) {
				this.element.style.visibility = "hidden";
				return;
			}
			this.element.style.visibility = "visible";
			this.element.style.width = "max-content";
			this.element.style.position = strategy;
			this.element.style.left = `${x}px`;
			this.element.style.top = `${y}px`;
			if (this.isVisible && this.floatingUIOptions.onUpdate) this.floatingUIOptions.onUpdate();
		});
	}
	update(view, oldState) {
		const { state } = view;
		const hasValidSelection = state.selection.from !== state.selection.to;
		if (this.updateDelay > 0 && hasValidSelection) {
			this.handleDebouncedUpdate(view, oldState);
			return;
		}
		const selectionChanged = !(oldState == null ? void 0 : oldState.selection.eq(view.state.selection));
		const docChanged = !(oldState == null ? void 0 : oldState.doc.eq(view.state.doc));
		this.updateHandler(view, selectionChanged, docChanged, oldState);
	}
	getShouldShow(oldState) {
		var _a;
		const { state } = this.view;
		const { selection } = state;
		const { ranges } = selection;
		const from = Math.min(...ranges.map((range) => range.$from.pos));
		const to = Math.max(...ranges.map((range) => range.$to.pos));
		return ((_a = this.shouldShow) == null ? void 0 : _a.call(this, {
			editor: this.editor,
			element: this.element,
			view: this.view,
			state,
			oldState,
			from,
			to
		})) || false;
	}
	show() {
		var _a;
		if (this.isVisible) return;
		this.element.style.visibility = "visible";
		this.element.style.opacity = "1";
		const appendToElement = typeof this.appendTo === "function" ? this.appendTo() : this.appendTo;
		(_a = appendToElement != null ? appendToElement : this.view.dom.parentElement) == null || _a.appendChild(this.element);
		if (this.floatingUIOptions.onShow) this.floatingUIOptions.onShow();
		this.isVisible = true;
	}
	hide() {
		if (!this.isVisible) return;
		this.element.style.visibility = "hidden";
		this.element.style.opacity = "0";
		this.element.remove();
		if (this.floatingUIOptions.onHide) this.floatingUIOptions.onHide();
		this.isVisible = false;
	}
	updateOptions(newProps) {
		var _a;
		if (newProps.updateDelay !== void 0) this.updateDelay = newProps.updateDelay;
		if (newProps.resizeDelay !== void 0) this.resizeDelay = newProps.resizeDelay;
		if (newProps.appendTo !== void 0) this.appendTo = newProps.appendTo;
		if (newProps.getReferencedVirtualElement !== void 0) this.getReferencedVirtualElement = newProps.getReferencedVirtualElement;
		if (newProps.shouldShow !== void 0) {
			if (newProps.shouldShow) this.shouldShow = newProps.shouldShow;
		}
		if (newProps.options !== void 0) {
			const newScrollTarget = (_a = newProps.options.scrollTarget) != null ? _a : window;
			if (newScrollTarget !== this.scrollTarget) {
				this.scrollTarget.removeEventListener("scroll", this.resizeHandler);
				this.scrollTarget = newScrollTarget;
				this.scrollTarget.addEventListener("scroll", this.resizeHandler);
			}
			this.floatingUIOptions = {
				...this.floatingUIOptions,
				...newProps.options
			};
		}
	}
	destroy() {
		this.hide();
		this.element.removeEventListener("mousedown", this.mousedownHandler, { capture: true });
		this.view.dom.removeEventListener("dragstart", this.dragstartHandler);
		window.removeEventListener("resize", this.resizeHandler);
		this.scrollTarget.removeEventListener("scroll", this.resizeHandler);
		this.editor.off("focus", this.focusHandler);
		this.editor.off("blur", this.blurHandler);
		this.editor.off("transaction", this.transactionHandler);
		if (this.floatingUIOptions.onDestroy) this.floatingUIOptions.onDestroy();
	}
};
var BubbleMenuPlugin = (options) => {
	return new Plugin({
		key: typeof options.pluginKey === "string" ? new PluginKey(options.pluginKey) : options.pluginKey,
		view: (view) => new BubbleMenuView({
			view,
			...options
		})
	});
};
var BubbleMenu = defineComponent({
	name: "BubbleMenu",
	inheritAttrs: false,
	props: {
		pluginKey: {
			type: [String, Object],
			default: void 0
		},
		editor: {
			type: Object,
			required: true
		},
		updateDelay: {
			type: Number,
			default: void 0
		},
		resizeDelay: {
			type: Number,
			default: void 0
		},
		options: {
			type: Object,
			default: () => ({})
		},
		appendTo: {
			type: [Object, Function],
			default: void 0
		},
		shouldShow: {
			type: Function,
			default: null
		},
		getReferencedVirtualElement: {
			type: Function,
			default: void 0
		}
	},
	setup(props, { slots, attrs }) {
		var _a;
		const root = ref(null);
		const resolvedPluginKey = (_a = props.pluginKey) != null ? _a : new PluginKey("bubbleMenu");
		onMounted(() => {
			const { editor, options, resizeDelay, appendTo, shouldShow, getReferencedVirtualElement, updateDelay } = props;
			const el = root.value;
			if (!el) return;
			el.style.visibility = "hidden";
			el.style.position = "absolute";
			el.remove();
			nextTick(() => {
				editor.registerPlugin(BubbleMenuPlugin({
					editor,
					element: el,
					options,
					pluginKey: resolvedPluginKey,
					resizeDelay,
					appendTo,
					shouldShow,
					getReferencedVirtualElement,
					updateDelay
				}));
			});
		});
		onBeforeUnmount(() => {
			const { editor } = props;
			editor.unregisterPlugin(resolvedPluginKey);
		});
		return () => {
			var _a2;
			return h$1("div", {
				ref: root,
				...attrs
			}, (_a2 = slots.default) == null ? void 0 : _a2.call(slots));
		};
	}
});
var FloatingMenuView$1 = class {
	constructor({ editor, element, view, pluginKey = "floatingMenu", updateDelay = 250, resizeDelay = 60, options, appendTo, shouldShow }) {
		this.preventHide = false;
		this.isVisible = false;
		this.scrollTarget = window;
		this.shouldShow = ({ view, state }) => {
			const { selection } = state;
			const { $anchor, empty } = selection;
			const isRootDepth = $anchor.depth === 1;
			const isEmptyTextBlock = $anchor.parent.isTextblock && !$anchor.parent.type.spec.code && !$anchor.parent.textContent && $anchor.parent.childCount === 0 && !this.getTextContent($anchor.parent);
			if (!view.hasFocus() || !empty || !isRootDepth || !isEmptyTextBlock || !this.editor.isEditable) return false;
			return true;
		};
		this.floatingUIOptions = {
			strategy: "absolute",
			placement: "right",
			offset: 8,
			flip: {},
			shift: {},
			arrow: false,
			size: false,
			autoPlacement: false,
			hide: false,
			inline: false
		};
		this.updateHandler = (view, selectionChanged, docChanged, oldState) => {
			const { composing } = view;
			if (composing || !selectionChanged && !docChanged) return;
			if (!this.getShouldShow(oldState)) {
				this.hide();
				return;
			}
			this.updatePosition();
			this.show();
		};
		this.mousedownHandler = () => {
			this.preventHide = true;
		};
		this.focusHandler = () => {
			setTimeout(() => this.update(this.editor.view));
		};
		this.blurHandler = ({ event }) => {
			var _a;
			if (this.preventHide) {
				this.preventHide = false;
				return;
			}
			if ((event == null ? void 0 : event.relatedTarget) && ((_a = this.element.parentNode) == null ? void 0 : _a.contains(event.relatedTarget))) return;
			if ((event == null ? void 0 : event.relatedTarget) === this.editor.view.dom) return;
			this.hide();
		};
		/**
		* Handles the transaction event to update the position of the floating menu.
		* This allows external code to trigger a position update via:
		* `editor.view.dispatch(editor.state.tr.setMeta(pluginKey, 'updatePosition'))`
		* The `pluginKey` defaults to `floatingMenu`
		*/
		this.transactionHandler = ({ transaction: tr }) => {
			const meta = tr.getMeta(this.pluginKey);
			if (meta === "updatePosition") this.updatePosition();
			else if (meta && typeof meta === "object" && meta.type === "updateOptions") this.updateOptions(meta.options);
			else if (meta === "hide") this.hide();
			else if (meta === "show") {
				this.updatePosition();
				this.show();
			}
		};
		/**
		* Handles the window resize event to update the position of the floating menu.
		* It uses a debounce mechanism to prevent excessive updates.
		* The delay is defined by the `resizeDelay` property.
		*/
		this.resizeHandler = () => {
			if (this.resizeDebounceTimer) clearTimeout(this.resizeDebounceTimer);
			this.resizeDebounceTimer = window.setTimeout(() => {
				this.updatePosition();
			}, this.resizeDelay);
		};
		var _a;
		this.editor = editor;
		this.element = element;
		this.view = view;
		this.pluginKey = pluginKey;
		this.updateDelay = updateDelay;
		this.resizeDelay = resizeDelay;
		this.appendTo = appendTo;
		this.scrollTarget = (_a = options == null ? void 0 : options.scrollTarget) != null ? _a : window;
		this.floatingUIOptions = {
			...this.floatingUIOptions,
			...options
		};
		this.element.tabIndex = 0;
		if (shouldShow) this.shouldShow = shouldShow;
		this.element.addEventListener("mousedown", this.mousedownHandler, { capture: true });
		this.editor.on("focus", this.focusHandler);
		this.editor.on("blur", this.blurHandler);
		this.editor.on("transaction", this.transactionHandler);
		window.addEventListener("resize", this.resizeHandler);
		this.scrollTarget.addEventListener("scroll", this.resizeHandler);
		this.update(view, view.state);
		if (this.getShouldShow()) {
			this.show();
			this.updatePosition();
		}
	}
	getTextContent(node) {
		return getText(node, { textSerializers: getTextSerializersFromSchema(this.editor.schema) });
	}
	get middlewares() {
		const middlewares = [];
		if (this.floatingUIOptions.flip) middlewares.push(flip(typeof this.floatingUIOptions.flip !== "boolean" ? this.floatingUIOptions.flip : void 0));
		if (this.floatingUIOptions.shift) middlewares.push(shift(typeof this.floatingUIOptions.shift !== "boolean" ? this.floatingUIOptions.shift : void 0));
		if (this.floatingUIOptions.offset) middlewares.push(offset(typeof this.floatingUIOptions.offset !== "boolean" ? this.floatingUIOptions.offset : void 0));
		if (this.floatingUIOptions.arrow) middlewares.push(arrow$2(this.floatingUIOptions.arrow));
		if (this.floatingUIOptions.size) middlewares.push(size(typeof this.floatingUIOptions.size !== "boolean" ? this.floatingUIOptions.size : void 0));
		if (this.floatingUIOptions.autoPlacement) middlewares.push(autoPlacement(typeof this.floatingUIOptions.autoPlacement !== "boolean" ? this.floatingUIOptions.autoPlacement : void 0));
		if (this.floatingUIOptions.hide) middlewares.push(hide(typeof this.floatingUIOptions.hide !== "boolean" ? this.floatingUIOptions.hide : void 0));
		if (this.floatingUIOptions.inline) middlewares.push(inline(typeof this.floatingUIOptions.inline !== "boolean" ? this.floatingUIOptions.inline : void 0));
		return middlewares;
	}
	getShouldShow(oldState) {
		var _a;
		const { state } = this.view;
		const { selection } = state;
		const { ranges } = selection;
		const from = Math.min(...ranges.map((range) => range.$from.pos));
		const to = Math.max(...ranges.map((range) => range.$to.pos));
		return (_a = this.shouldShow) == null ? void 0 : _a.call(this, {
			editor: this.editor,
			view: this.view,
			state,
			oldState,
			from,
			to
		});
	}
	updateOptions(newProps) {
		var _a;
		if (newProps.updateDelay !== void 0) this.updateDelay = newProps.updateDelay;
		if (newProps.resizeDelay !== void 0) this.resizeDelay = newProps.resizeDelay;
		if (newProps.appendTo !== void 0) this.appendTo = newProps.appendTo;
		if (newProps.shouldShow !== void 0) {
			if (newProps.shouldShow) this.shouldShow = newProps.shouldShow;
		}
		if (newProps.options !== void 0) {
			const newScrollTarget = (_a = newProps.options.scrollTarget) != null ? _a : window;
			if (newScrollTarget !== this.scrollTarget) {
				this.scrollTarget.removeEventListener("scroll", this.resizeHandler);
				this.scrollTarget = newScrollTarget;
				this.scrollTarget.addEventListener("scroll", this.resizeHandler);
			}
			this.floatingUIOptions = {
				...this.floatingUIOptions,
				...newProps.options
			};
		}
	}
	updatePosition() {
		const { selection } = this.editor.state;
		const domRect = posToDOMRect(this.view, selection.from, selection.to);
		computePosition({
			getBoundingClientRect: () => domRect,
			getClientRects: () => [domRect]
		}, this.element, {
			placement: this.floatingUIOptions.placement,
			strategy: this.floatingUIOptions.strategy,
			middleware: this.middlewares
		}).then(({ x, y, strategy, middlewareData }) => {
			var _a, _b;
			if (((_a = middlewareData.hide) == null ? void 0 : _a.referenceHidden) || ((_b = middlewareData.hide) == null ? void 0 : _b.escaped)) {
				this.element.style.visibility = "hidden";
				return;
			}
			this.element.style.visibility = "visible";
			this.element.style.width = "max-content";
			this.element.style.position = strategy;
			this.element.style.left = `${x}px`;
			this.element.style.top = `${y}px`;
			if (this.isVisible && this.floatingUIOptions.onUpdate) this.floatingUIOptions.onUpdate();
		});
	}
	update(view, oldState) {
		const selectionChanged = !(oldState == null ? void 0 : oldState.selection.eq(view.state.selection));
		const docChanged = !(oldState == null ? void 0 : oldState.doc.eq(view.state.doc));
		this.updateHandler(view, selectionChanged, docChanged, oldState);
	}
	show() {
		var _a;
		if (this.isVisible) return;
		this.element.style.visibility = "visible";
		this.element.style.opacity = "1";
		const appendToElement = typeof this.appendTo === "function" ? this.appendTo() : this.appendTo;
		(_a = appendToElement != null ? appendToElement : this.view.dom.parentElement) == null || _a.appendChild(this.element);
		if (this.floatingUIOptions.onShow) this.floatingUIOptions.onShow();
		this.isVisible = true;
	}
	hide() {
		if (!this.isVisible) return;
		this.element.style.visibility = "hidden";
		this.element.style.opacity = "0";
		this.element.remove();
		if (this.floatingUIOptions.onHide) this.floatingUIOptions.onHide();
		this.isVisible = false;
	}
	destroy() {
		this.hide();
		this.element.removeEventListener("mousedown", this.mousedownHandler, { capture: true });
		window.removeEventListener("resize", this.resizeHandler);
		this.scrollTarget.removeEventListener("scroll", this.resizeHandler);
		this.editor.off("focus", this.focusHandler);
		this.editor.off("blur", this.blurHandler);
		this.editor.off("transaction", this.transactionHandler);
		if (this.floatingUIOptions.onDestroy) this.floatingUIOptions.onDestroy();
	}
};
var FloatingMenuPlugin$1 = (options) => {
	return new Plugin({
		key: typeof options.pluginKey === "string" ? new PluginKey(options.pluginKey) : options.pluginKey,
		view: (view) => new FloatingMenuView$1({
			view,
			...options
		})
	});
};
defineComponent({
	name: "FloatingMenu",
	inheritAttrs: false,
	props: {
		pluginKey: {
			type: null,
			default: void 0
		},
		editor: {
			type: Object,
			required: true
		},
		updateDelay: {
			type: Number,
			default: void 0
		},
		resizeDelay: {
			type: Number,
			default: void 0
		},
		options: {
			type: Object,
			default: () => ({})
		},
		appendTo: {
			type: [Object, Function],
			default: void 0
		},
		shouldShow: {
			type: Function,
			default: null
		}
	},
	setup(props, { slots, attrs }) {
		var _a;
		const root = ref(null);
		const resolvedPluginKey = (_a = props.pluginKey) != null ? _a : new PluginKey("floatingMenu");
		onMounted(() => {
			const { editor, updateDelay, resizeDelay, options, appendTo, shouldShow } = props;
			const el = root.value;
			if (!el) return;
			el.style.visibility = "hidden";
			el.style.position = "absolute";
			el.remove();
			editor.registerPlugin(FloatingMenuPlugin$1({
				pluginKey: resolvedPluginKey,
				editor,
				element: el,
				updateDelay,
				resizeDelay,
				options,
				appendTo,
				shouldShow
			}));
		});
		onBeforeUnmount(() => {
			const { editor } = props;
			editor.unregisterPlugin(resolvedPluginKey);
		});
		return () => {
			var _a2;
			return h$1("div", {
				ref: root,
				...attrs
			}, (_a2 = slots.default) == null ? void 0 : _a2.call(slots));
		};
	}
});
//#endregion
//#region resources/js/components/fieldtypes/bard/FloatingMenuPlugin.js
var FloatingMenuView = class {
	constructor({ editor, element, view, shouldShow, vm }) {
		this.editor = editor;
		this.element = element;
		this.view = view;
		this.vm = vm;
		this.shouldShow = shouldShow;
		this.editor.on("focus", this.focusHandler);
	}
	focusHandler = () => {
		this.update(this.editor.view);
	};
	update(view, oldState) {
		const { state } = view;
		const { doc, selection } = state;
		const { from, to } = selection;
		if (oldState && oldState.doc.eq(doc) && oldState.selection.eq(selection)) return;
		if (!this.shouldShow?.({
			editor: this.editor,
			view,
			state,
			oldState
		})) return this.hide();
		const { top: selectionTop } = posToDOMRect(view, from, to);
		const { top: editorTop } = this.editor.options.element.getBoundingClientRect();
		this.vm.y = Math.round(selectionTop - editorTop);
		this.show();
	}
	show() {
		this.vm.show = true;
	}
	hide() {
		this.vm.show = false;
	}
	destroy() {
		this.editor.off("focus", this.focusHandler);
	}
};
var FloatingMenuPlugin = (options) => {
	return new Plugin({
		key: new PluginKey(options.pluginKey),
		view: (view) => new FloatingMenuView({
			view,
			...options
		})
	});
};
//#endregion
//#region resources/js/components/fieldtypes/bard/FloatingMenu.js
var FloatingMenu = {
	emits: ["shown", "hidden"],
	name: "FloatingMenu",
	props: {
		editor: {
			type: Object,
			required: true
		},
		shouldShow: {
			type: Function,
			default: null
		},
		isShowing: { type: Boolean }
	},
	data() {
		return {
			show: false,
			x: 0,
			y: 0
		};
	},
	watch: {
		editor: {
			immediate: true,
			handler(editor) {
				if (!editor) return;
				this.$nextTick(() => {
					editor.registerPlugin(FloatingMenuPlugin({
						pluginKey: "floatingMenu",
						editor,
						vm: this,
						element: this.$el,
						shouldShow: this.shouldShow
					}));
				});
			}
		},
		isShowing(showing) {
			this.show = showing;
		},
		show(shown) {
			if (shown) this.$emit("shown");
			else this.$emit("hidden");
		}
	},
	render() {
		return this.$slots.default({
			x: this.x,
			y: this.y
		});
	},
	beforeUnmount() {
		this.editor.unregisterPlugin("floatingMenu");
	}
};
//#endregion
//#region node_modules/@tiptap/core/dist/jsx-runtime/jsx-runtime.js
var h = (tag, attributes) => {
	if (tag === "slot") return 0;
	if (tag instanceof Function) return tag(attributes);
	const { children, ...rest } = attributes != null ? attributes : {};
	if (tag === "svg") throw new Error("SVG elements are not supported in the JSX syntax, use the array syntax instead");
	return [
		tag,
		rest,
		children
	];
};
//#endregion
//#region node_modules/@tiptap/extension-blockquote/dist/index.js
var inputRegex$3 = /^\s*>\s$/;
var index_default$18 = Node3.create({
	name: "blockquote",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	content: "block+",
	group: "block",
	defining: true,
	parseHTML() {
		return [{ tag: "blockquote" }];
	},
	renderHTML({ HTMLAttributes }) {
		return /* @__PURE__ */ h("blockquote", {
			...mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			children: /* @__PURE__ */ h("slot", {})
		});
	},
	parseMarkdown: (token, helpers) => {
		var _a;
		const parseBlockChildren = (_a = helpers.parseBlockChildren) != null ? _a : helpers.parseChildren;
		return helpers.createNode("blockquote", void 0, parseBlockChildren(token.tokens || []));
	},
	renderMarkdown: (node, h) => {
		if (!node.content) return "";
		const prefix = ">";
		const result = [];
		node.content.forEach((child, index) => {
			var _a, _b;
			const linesWithPrefix = ((_b = (_a = h.renderChild) == null ? void 0 : _a.call(h, child, index)) != null ? _b : h.renderChildren([child])).split("\n").map((line) => {
				if (line.trim() === "") return prefix;
				return `${prefix} ${line}`;
			});
			result.push(linesWithPrefix.join("\n"));
		});
		return result.join(`
${prefix}
`);
	},
	addCommands() {
		return {
			setBlockquote: () => ({ commands }) => {
				return commands.wrapIn(this.name);
			},
			toggleBlockquote: () => ({ commands }) => {
				return commands.toggleWrap(this.name);
			},
			unsetBlockquote: () => ({ commands }) => {
				return commands.lift(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return { "Mod-Shift-b": () => this.editor.commands.toggleBlockquote() };
	},
	addInputRules() {
		return [wrappingInputRule({
			find: inputRegex$3,
			type: this.type
		})];
	}
});
//#endregion
//#region node_modules/@tiptap/extension-bold/dist/index.js
var starInputRegex$1 = /(?:^|\s)(\*\*(?!\s+\*\*)((?:[^*]+))\*\*(?!\s+\*\*))$/;
var starPasteRegex$1 = /(?:^|\s)(\*\*(?!\s+\*\*)((?:[^*]+))\*\*(?!\s+\*\*))/g;
var underscoreInputRegex$1 = /(?:^|\s)(__(?!\s+__)((?:[^_]+))__(?!\s+__))$/;
var underscorePasteRegex$1 = /(?:^|\s)(__(?!\s+__)((?:[^_]+))__(?!\s+__))/g;
var index_default$17 = Mark.create({
	name: "bold",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [
			{ tag: "strong" },
			{
				tag: "b",
				getAttrs: (node) => node.style.fontWeight !== "normal" && null
			},
			{
				style: "font-weight=400",
				clearMark: (mark) => mark.type.name === this.name
			},
			{
				style: "font-weight",
				getAttrs: (value) => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null
			}
		];
	},
	renderHTML({ HTMLAttributes }) {
		return /* @__PURE__ */ h("strong", {
			...mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			children: /* @__PURE__ */ h("slot", {})
		});
	},
	markdownTokenName: "strong",
	parseMarkdown: (token, helpers) => {
		return helpers.applyMark("bold", helpers.parseInline(token.tokens || []));
	},
	markdownOptions: { htmlReopen: {
		open: "<strong>",
		close: "</strong>"
	} },
	renderMarkdown: (node, h) => {
		return `**${h.renderChildren(node)}**`;
	},
	addCommands() {
		return {
			setBold: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleBold: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetBold: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return {
			"Mod-b": () => this.editor.commands.toggleBold(),
			"Mod-B": () => this.editor.commands.toggleBold()
		};
	},
	addInputRules() {
		return [markInputRule({
			find: starInputRegex$1,
			type: this.type
		}), markInputRule({
			find: underscoreInputRegex$1,
			type: this.type
		})];
	},
	addPasteRules() {
		return [markPasteRule({
			find: starPasteRegex$1,
			type: this.type
		}), markPasteRule({
			find: underscorePasteRegex$1,
			type: this.type
		})];
	}
});
//#endregion
//#region node_modules/@tiptap/extension-list/dist/index.js
var __defProp = Object.defineProperty;
var __export = (target, all) => {
	for (var name in all) __defProp(target, name, {
		get: all[name],
		enumerable: true
	});
};
var ListItemName = "listItem";
var TextStyleName = "textStyle";
var bulletListInputRegex = /^\s*([-+*])\s$/;
var BulletList = Node3.create({
	name: "bulletList",
	addOptions() {
		return {
			itemTypeName: "listItem",
			HTMLAttributes: {},
			keepMarks: false,
			keepAttributes: false
		};
	},
	group: "block list",
	content() {
		return `${this.options.itemTypeName}+`;
	},
	parseHTML() {
		return [{ tag: "ul" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"ul",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	markdownTokenName: "list",
	parseMarkdown: (token, helpers) => {
		if (token.type !== "list" || token.ordered) return [];
		return {
			type: "bulletList",
			content: token.items ? helpers.parseChildren(token.items) : []
		};
	},
	renderMarkdown: (node, h) => {
		if (!node.content) return "";
		return h.renderChildren(node.content, "\n");
	},
	markdownOptions: { indentsContent: true },
	addCommands() {
		return { toggleBulletList: () => ({ commands, chain }) => {
			if (this.options.keepAttributes) return chain().toggleList(this.name, this.options.itemTypeName, this.options.keepMarks).updateAttributes(ListItemName, this.editor.getAttributes(TextStyleName)).run();
			return commands.toggleList(this.name, this.options.itemTypeName, this.options.keepMarks);
		} };
	},
	addKeyboardShortcuts() {
		return { "Mod-Shift-8": () => this.editor.commands.toggleBulletList() };
	},
	addInputRules() {
		let inputRule = wrappingInputRule({
			find: bulletListInputRegex,
			type: this.type
		});
		if (this.options.keepMarks || this.options.keepAttributes) inputRule = wrappingInputRule({
			find: bulletListInputRegex,
			type: this.type,
			keepMarks: this.options.keepMarks,
			keepAttributes: this.options.keepAttributes,
			getAttributes: () => {
				return this.editor.getAttributes(TextStyleName);
			},
			editor: this.editor
		});
		return [inputRule];
	}
});
var ListItem = Node3.create({
	name: "listItem",
	addOptions() {
		return {
			HTMLAttributes: {},
			bulletListTypeName: "bulletList",
			orderedListTypeName: "orderedList"
		};
	},
	content: "paragraph block*",
	defining: true,
	parseHTML() {
		return [{ tag: "li" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"li",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	markdownTokenName: "list_item",
	parseMarkdown: (token, helpers) => {
		var _a;
		if (token.type !== "list_item") return [];
		const parseBlockChildren = (_a = helpers.parseBlockChildren) != null ? _a : helpers.parseChildren;
		let content = [];
		if (token.tokens && token.tokens.length > 0) if (token.tokens.some((t) => t.type === "paragraph")) content = parseBlockChildren(token.tokens);
		else {
			const firstToken = token.tokens[0];
			if (firstToken && firstToken.type === "text" && firstToken.tokens && firstToken.tokens.length > 0) {
				content = [{
					type: "paragraph",
					content: helpers.parseInline(firstToken.tokens)
				}];
				if (token.tokens.length > 1) {
					const additionalContent = parseBlockChildren(token.tokens.slice(1));
					content.push(...additionalContent);
				}
			} else content = parseBlockChildren(token.tokens);
		}
		if (content.length === 0) content = [{
			type: "paragraph",
			content: []
		}];
		return {
			type: "listItem",
			content
		};
	},
	renderMarkdown: (node, h, ctx) => {
		return renderNestedMarkdownContent(node, h, (context) => {
			var _a, _b;
			if (context.parentType === "bulletList") return "- ";
			if (context.parentType === "orderedList") return `${(((_b = (_a = context.meta) == null ? void 0 : _a.parentAttrs) == null ? void 0 : _b.start) || 1) + context.index}. `;
			return "- ";
		}, ctx);
	},
	addKeyboardShortcuts() {
		return {
			Enter: () => this.editor.commands.splitListItem(this.name),
			Tab: () => this.editor.commands.sinkListItem(this.name),
			"Shift-Tab": () => this.editor.commands.liftListItem(this.name)
		};
	}
});
__export({}, {
	findListItemPos: () => findListItemPos,
	getNextListDepth: () => getNextListDepth,
	handleBackspace: () => handleBackspace,
	handleDelete: () => handleDelete,
	hasListBefore: () => hasListBefore,
	hasListItemAfter: () => hasListItemAfter,
	hasListItemBefore: () => hasListItemBefore,
	listItemHasSubList: () => listItemHasSubList,
	nextListIsDeeper: () => nextListIsDeeper,
	nextListIsHigher: () => nextListIsHigher
});
var findListItemPos = (typeOrName, state) => {
	const { $from } = state.selection;
	const nodeType = getNodeType(typeOrName, state.schema);
	let currentNode = null;
	let currentDepth = $from.depth;
	let currentPos = $from.pos;
	let targetDepth = null;
	while (currentDepth > 0 && targetDepth === null) {
		currentNode = $from.node(currentDepth);
		if (currentNode.type === nodeType) targetDepth = currentDepth;
		else {
			currentDepth -= 1;
			currentPos -= 1;
		}
	}
	if (targetDepth === null) return null;
	return {
		$pos: state.doc.resolve(currentPos),
		depth: targetDepth
	};
};
var getNextListDepth = (typeOrName, state) => {
	const listItemPos = findListItemPos(typeOrName, state);
	if (!listItemPos) return false;
	const [, depth] = getNodeAtPosition(state, typeOrName, listItemPos.$pos.pos + 4);
	return depth;
};
var hasListBefore = (editorState, name, parentListTypes) => {
	const { $anchor } = editorState.selection;
	const previousNodePos = Math.max(0, $anchor.pos - 2);
	const previousNode = editorState.doc.resolve(previousNodePos).node();
	if (!previousNode || !parentListTypes.includes(previousNode.type.name)) return false;
	return true;
};
var hasListItemBefore = (typeOrName, state) => {
	var _a;
	const { $anchor } = state.selection;
	const $targetPos = state.doc.resolve($anchor.pos - 2);
	if ($targetPos.index() === 0) return false;
	if (((_a = $targetPos.nodeBefore) == null ? void 0 : _a.type.name) !== typeOrName) return false;
	return true;
};
var listItemHasSubList = (typeOrName, state, node) => {
	if (!node) return false;
	const nodeType = getNodeType(typeOrName, state.schema);
	let hasSubList = false;
	node.descendants((child) => {
		if (child.type === nodeType) hasSubList = true;
	});
	return hasSubList;
};
var handleBackspace = (editor, name, parentListTypes) => {
	if (editor.commands.undoInputRule()) return true;
	if (editor.state.selection.from !== editor.state.selection.to) return false;
	if (!isNodeActive(editor.state, name) && hasListBefore(editor.state, name, parentListTypes)) {
		const { $anchor } = editor.state.selection;
		const $listPos = editor.state.doc.resolve($anchor.before() - 1);
		const listDescendants = [];
		$listPos.node().descendants((node, pos) => {
			if (node.type.name === name) listDescendants.push({
				node,
				pos
			});
		});
		const lastItem = listDescendants.at(-1);
		if (!lastItem) return false;
		const $lastItemPos = editor.state.doc.resolve($listPos.start() + lastItem.pos + 1);
		return editor.chain().cut({
			from: $anchor.start() - 1,
			to: $anchor.end() + 1
		}, $lastItemPos.end()).joinForward().run();
	}
	if (!isNodeActive(editor.state, name)) return false;
	if (!isAtStartOfNode(editor.state)) return false;
	const listItemPos = findListItemPos(name, editor.state);
	if (!listItemPos) return false;
	const prevNode = editor.state.doc.resolve(listItemPos.$pos.pos - 2).node(listItemPos.depth);
	const previousListItemHasSubList = listItemHasSubList(name, editor.state, prevNode);
	if (hasListItemBefore(name, editor.state) && !previousListItemHasSubList) return editor.commands.joinItemBackward();
	return editor.chain().liftListItem(name).run();
};
var nextListIsDeeper = (typeOrName, state) => {
	const listDepth = getNextListDepth(typeOrName, state);
	const listItemPos = findListItemPos(typeOrName, state);
	if (!listItemPos || !listDepth) return false;
	if (listDepth > listItemPos.depth) return true;
	return false;
};
var nextListIsHigher = (typeOrName, state) => {
	const listDepth = getNextListDepth(typeOrName, state);
	const listItemPos = findListItemPos(typeOrName, state);
	if (!listItemPos || !listDepth) return false;
	if (listDepth < listItemPos.depth) return true;
	return false;
};
var handleDelete = (editor, name) => {
	if (!isNodeActive(editor.state, name)) return false;
	if (!isAtEndOfNode(editor.state, name)) return false;
	const { selection } = editor.state;
	const { $from, $to } = selection;
	if (!selection.empty && $from.sameParent($to)) return false;
	if (nextListIsDeeper(name, editor.state)) return editor.chain().focus(editor.state.selection.from + 4).lift(name).joinBackward().run();
	if (nextListIsHigher(name, editor.state)) return editor.chain().joinForward().joinBackward().run();
	return editor.commands.joinItemForward();
};
var hasListItemAfter = (typeOrName, state) => {
	var _a;
	const { $anchor } = state.selection;
	const $targetPos = state.doc.resolve($anchor.pos - $anchor.parentOffset - 2);
	if ($targetPos.index() === $targetPos.parent.childCount - 1) return false;
	if (((_a = $targetPos.nodeAfter) == null ? void 0 : _a.type.name) !== typeOrName) return false;
	return true;
};
var ListKeymap = Extension.create({
	name: "listKeymap",
	addOptions() {
		return { listTypes: [{
			itemName: "listItem",
			wrapperNames: ["bulletList", "orderedList"]
		}, {
			itemName: "taskItem",
			wrapperNames: ["taskList"]
		}] };
	},
	addKeyboardShortcuts() {
		return {
			Delete: ({ editor }) => {
				let handled = false;
				this.options.listTypes.forEach(({ itemName }) => {
					if (editor.state.schema.nodes[itemName] === void 0) return;
					if (handleDelete(editor, itemName)) handled = true;
				});
				return handled;
			},
			"Mod-Delete": ({ editor }) => {
				let handled = false;
				this.options.listTypes.forEach(({ itemName }) => {
					if (editor.state.schema.nodes[itemName] === void 0) return;
					if (handleDelete(editor, itemName)) handled = true;
				});
				return handled;
			},
			Backspace: ({ editor }) => {
				let handled = false;
				this.options.listTypes.forEach(({ itemName, wrapperNames }) => {
					if (editor.state.schema.nodes[itemName] === void 0) return;
					if (handleBackspace(editor, itemName, wrapperNames)) handled = true;
				});
				return handled;
			},
			"Mod-Backspace": ({ editor }) => {
				let handled = false;
				this.options.listTypes.forEach(({ itemName, wrapperNames }) => {
					if (editor.state.schema.nodes[itemName] === void 0) return;
					if (handleBackspace(editor, itemName, wrapperNames)) handled = true;
				});
				return handled;
			}
		};
	}
});
var ORDERED_LIST_ITEM_REGEX = /^(\s*)(\d+)\.\s+(.*)$/;
var INDENTED_LINE_REGEX = /^\s/;
function collectOrderedListItems(lines) {
	const listItems = [];
	let currentLineIndex = 0;
	let consumed = 0;
	while (currentLineIndex < lines.length) {
		const line = lines[currentLineIndex];
		const match = line.match(ORDERED_LIST_ITEM_REGEX);
		if (!match) break;
		const [, indent, number, content] = match;
		const indentLevel = indent.length;
		let itemContent = content;
		let nextLineIndex = currentLineIndex + 1;
		const itemLines = [line];
		while (nextLineIndex < lines.length) {
			const nextLine = lines[nextLineIndex];
			if (nextLine.match(ORDERED_LIST_ITEM_REGEX)) break;
			if (nextLine.trim() === "") {
				itemLines.push(nextLine);
				itemContent += "\n";
				nextLineIndex += 1;
			} else if (nextLine.match(INDENTED_LINE_REGEX)) {
				itemLines.push(nextLine);
				itemContent += `
${nextLine.slice(indentLevel + 2)}`;
				nextLineIndex += 1;
			} else break;
		}
		listItems.push({
			indent: indentLevel,
			number: parseInt(number, 10),
			content: itemContent.trim(),
			raw: itemLines.join("\n")
		});
		consumed = nextLineIndex;
		currentLineIndex = nextLineIndex;
	}
	return [listItems, consumed];
}
function buildNestedStructure(items, baseIndent, lexer) {
	var _a;
	const result = [];
	let currentIndex = 0;
	while (currentIndex < items.length) {
		const item = items[currentIndex];
		if (item.indent === baseIndent) {
			const contentLines = item.content.split("\n");
			const mainText = ((_a = contentLines[0]) == null ? void 0 : _a.trim()) || "";
			const tokens = [];
			if (mainText) tokens.push({
				type: "paragraph",
				raw: mainText,
				tokens: lexer.inlineTokens(mainText)
			});
			const additionalContent = contentLines.slice(1).join("\n").trim();
			if (additionalContent) {
				const blockTokens = lexer.blockTokens(additionalContent);
				tokens.push(...blockTokens);
			}
			let lookAheadIndex = currentIndex + 1;
			const nestedItems = [];
			while (lookAheadIndex < items.length && items[lookAheadIndex].indent > baseIndent) {
				nestedItems.push(items[lookAheadIndex]);
				lookAheadIndex += 1;
			}
			if (nestedItems.length > 0) {
				const nestedListItems = buildNestedStructure(nestedItems, Math.min(...nestedItems.map((nestedItem) => nestedItem.indent)), lexer);
				tokens.push({
					type: "list",
					ordered: true,
					start: nestedItems[0].number,
					items: nestedListItems,
					raw: nestedItems.map((nestedItem) => nestedItem.raw).join("\n")
				});
			}
			result.push({
				type: "list_item",
				raw: item.raw,
				tokens
			});
			currentIndex = lookAheadIndex;
		} else currentIndex += 1;
	}
	return result;
}
function parseListItems(items, helpers) {
	return items.map((item) => {
		if (item.type !== "list_item") return helpers.parseChildren([item])[0];
		const content = [];
		if (item.tokens && item.tokens.length > 0) item.tokens.forEach((itemToken) => {
			if (itemToken.type === "paragraph" || itemToken.type === "list" || itemToken.type === "blockquote" || itemToken.type === "code") content.push(...helpers.parseChildren([itemToken]));
			else if (itemToken.type === "text" && itemToken.tokens) {
				const inlineContent = helpers.parseChildren([itemToken]);
				content.push({
					type: "paragraph",
					content: inlineContent
				});
			} else {
				const parsed = helpers.parseChildren([itemToken]);
				if (parsed.length > 0) content.push(...parsed);
			}
		});
		return {
			type: "listItem",
			content
		};
	});
}
var ListItemName2 = "listItem";
var TextStyleName2 = "textStyle";
var orderedListInputRegex = /^(\d+)\.\s$/;
var OrderedList = Node3.create({
	name: "orderedList",
	addOptions() {
		return {
			itemTypeName: "listItem",
			HTMLAttributes: {},
			keepMarks: false,
			keepAttributes: false
		};
	},
	group: "block list",
	content() {
		return `${this.options.itemTypeName}+`;
	},
	addAttributes() {
		return {
			start: {
				default: 1,
				parseHTML: (element) => {
					return element.hasAttribute("start") ? parseInt(element.getAttribute("start") || "", 10) : 1;
				}
			},
			type: {
				default: null,
				parseHTML: (element) => element.getAttribute("type")
			}
		};
	},
	parseHTML() {
		return [{ tag: "ol" }];
	},
	renderHTML({ HTMLAttributes }) {
		const { start, ...attributesWithoutStart } = HTMLAttributes;
		return start === 1 ? [
			"ol",
			mergeAttributes(this.options.HTMLAttributes, attributesWithoutStart),
			0
		] : [
			"ol",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	markdownTokenName: "list",
	parseMarkdown: (token, helpers) => {
		if (token.type !== "list" || !token.ordered) return [];
		const startValue = token.start || 1;
		const content = token.items ? parseListItems(token.items, helpers) : [];
		if (startValue !== 1) return {
			type: "orderedList",
			attrs: { start: startValue },
			content
		};
		return {
			type: "orderedList",
			content
		};
	},
	renderMarkdown: (node, h) => {
		if (!node.content) return "";
		return h.renderChildren(node.content, "\n");
	},
	markdownTokenizer: {
		name: "orderedList",
		level: "block",
		start: (src) => {
			const match = src.match(/^(\s*)(\d+)\.\s+/);
			const index = match == null ? void 0 : match.index;
			return index !== void 0 ? index : -1;
		},
		tokenize: (src, _tokens, lexer) => {
			var _a;
			const lines = src.split("\n");
			const [listItems, consumed] = collectOrderedListItems(lines);
			if (listItems.length === 0) return;
			const items = buildNestedStructure(listItems, 0, lexer);
			if (items.length === 0) return;
			return {
				type: "list",
				ordered: true,
				start: ((_a = listItems[0]) == null ? void 0 : _a.number) || 1,
				items,
				raw: lines.slice(0, consumed).join("\n")
			};
		}
	},
	markdownOptions: { indentsContent: true },
	addCommands() {
		return { toggleOrderedList: () => ({ commands, chain }) => {
			if (this.options.keepAttributes) return chain().toggleList(this.name, this.options.itemTypeName, this.options.keepMarks).updateAttributes(ListItemName2, this.editor.getAttributes(TextStyleName2)).run();
			return commands.toggleList(this.name, this.options.itemTypeName, this.options.keepMarks);
		} };
	},
	addKeyboardShortcuts() {
		return { "Mod-Shift-7": () => this.editor.commands.toggleOrderedList() };
	},
	addInputRules() {
		let inputRule = wrappingInputRule({
			find: orderedListInputRegex,
			type: this.type,
			getAttributes: (match) => ({ start: +match[1] }),
			joinPredicate: (match, node) => node.childCount + node.attrs.start === +match[1]
		});
		if (this.options.keepMarks || this.options.keepAttributes) inputRule = wrappingInputRule({
			find: orderedListInputRegex,
			type: this.type,
			keepMarks: this.options.keepMarks,
			keepAttributes: this.options.keepAttributes,
			getAttributes: (match) => ({
				start: +match[1],
				...this.editor.getAttributes(TextStyleName2)
			}),
			joinPredicate: (match, node) => node.childCount + node.attrs.start === +match[1],
			editor: this.editor
		});
		return [inputRule];
	}
});
var inputRegex$2 = /^\s*(\[([( |x])?\])\s$/;
var TaskItem = Node3.create({
	name: "taskItem",
	addOptions() {
		return {
			nested: false,
			HTMLAttributes: {},
			taskListTypeName: "taskList",
			a11y: void 0
		};
	},
	content() {
		return this.options.nested ? "paragraph block*" : "paragraph+";
	},
	defining: true,
	addAttributes() {
		return { checked: {
			default: false,
			keepOnSplit: false,
			parseHTML: (element) => {
				const dataChecked = element.getAttribute("data-checked");
				return dataChecked === "" || dataChecked === "true";
			},
			renderHTML: (attributes) => ({ "data-checked": attributes.checked })
		} };
	},
	parseHTML() {
		return [{
			tag: `li[data-type="${this.name}"]`,
			priority: 51
		}];
	},
	renderHTML({ node, HTMLAttributes }) {
		return [
			"li",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { "data-type": this.name }),
			[
				"label",
				["input", {
					type: "checkbox",
					checked: node.attrs.checked ? "checked" : null
				}],
				["span"]
			],
			["div", 0]
		];
	},
	parseMarkdown: (token, h) => {
		const content = [];
		if (token.tokens && token.tokens.length > 0) content.push(h.createNode("paragraph", {}, h.parseInline(token.tokens)));
		else if (token.text) content.push(h.createNode("paragraph", {}, [h.createNode("text", { text: token.text })]));
		else content.push(h.createNode("paragraph", {}, []));
		if (token.nestedTokens && token.nestedTokens.length > 0) {
			const nestedContent = h.parseChildren(token.nestedTokens);
			content.push(...nestedContent);
		}
		return h.createNode("taskItem", { checked: token.checked || false }, content);
	},
	renderMarkdown: (node, h) => {
		var _a;
		return renderNestedMarkdownContent(node, h, `- [${((_a = node.attrs) == null ? void 0 : _a.checked) ? "x" : " "}] `);
	},
	addKeyboardShortcuts() {
		const shortcuts = {
			Enter: () => this.editor.commands.splitListItem(this.name),
			"Shift-Tab": () => this.editor.commands.liftListItem(this.name)
		};
		if (!this.options.nested) return shortcuts;
		return {
			...shortcuts,
			Tab: () => this.editor.commands.sinkListItem(this.name)
		};
	},
	addNodeView() {
		return ({ node, HTMLAttributes, getPos, editor }) => {
			const listItem = document.createElement("li");
			const checkboxWrapper = document.createElement("label");
			const checkboxStyler = document.createElement("span");
			const checkbox = document.createElement("input");
			const content = document.createElement("div");
			const updateA11Y = (currentNode) => {
				var _a, _b;
				checkbox.ariaLabel = ((_b = (_a = this.options.a11y) == null ? void 0 : _a.checkboxLabel) == null ? void 0 : _b.call(_a, currentNode, checkbox.checked)) || `Task item checkbox for ${currentNode.textContent || "empty task item"}`;
			};
			updateA11Y(node);
			checkboxWrapper.contentEditable = "false";
			checkbox.type = "checkbox";
			checkbox.addEventListener("mousedown", (event) => event.preventDefault());
			checkbox.addEventListener("change", (event) => {
				if (!editor.isEditable && !this.options.onReadOnlyChecked) {
					checkbox.checked = !checkbox.checked;
					return;
				}
				const { checked } = event.target;
				if (editor.isEditable && typeof getPos === "function") editor.chain().focus(void 0, { scrollIntoView: false }).command(({ tr }) => {
					const position = getPos();
					if (typeof position !== "number") return false;
					const currentNode = tr.doc.nodeAt(position);
					tr.setNodeMarkup(position, void 0, {
						...currentNode == null ? void 0 : currentNode.attrs,
						checked
					});
					return true;
				}).run();
				if (!editor.isEditable && this.options.onReadOnlyChecked) {
					if (!this.options.onReadOnlyChecked(node, checked)) checkbox.checked = !checkbox.checked;
				}
			});
			Object.entries(this.options.HTMLAttributes).forEach(([key, value]) => {
				listItem.setAttribute(key, value);
			});
			listItem.dataset.checked = node.attrs.checked;
			checkbox.checked = node.attrs.checked;
			checkboxWrapper.append(checkbox, checkboxStyler);
			listItem.append(checkboxWrapper, content);
			Object.entries(HTMLAttributes).forEach(([key, value]) => {
				listItem.setAttribute(key, value);
			});
			let prevRenderedAttributeKeys = new Set(Object.keys(HTMLAttributes));
			return {
				dom: listItem,
				contentDOM: content,
				update: (updatedNode) => {
					if (updatedNode.type !== this.type) return false;
					listItem.dataset.checked = updatedNode.attrs.checked;
					checkbox.checked = updatedNode.attrs.checked;
					updateA11Y(updatedNode);
					const extensionAttributes = editor.extensionManager.attributes;
					const newHTMLAttributes = getRenderedAttributes(updatedNode, extensionAttributes);
					const newKeys = new Set(Object.keys(newHTMLAttributes));
					const staticAttrs = this.options.HTMLAttributes;
					prevRenderedAttributeKeys.forEach((key) => {
						if (!newKeys.has(key)) if (key in staticAttrs) listItem.setAttribute(key, staticAttrs[key]);
						else listItem.removeAttribute(key);
					});
					Object.entries(newHTMLAttributes).forEach(([key, value]) => {
						if (value === null || value === void 0) if (key in staticAttrs) listItem.setAttribute(key, staticAttrs[key]);
						else listItem.removeAttribute(key);
						else listItem.setAttribute(key, value);
					});
					prevRenderedAttributeKeys = newKeys;
					return true;
				}
			};
		};
	},
	addInputRules() {
		return [wrappingInputRule({
			find: inputRegex$2,
			type: this.type,
			getAttributes: (match) => ({ checked: match[match.length - 1] === "x" })
		})];
	}
});
var TaskList = Node3.create({
	name: "taskList",
	addOptions() {
		return {
			itemTypeName: "taskItem",
			HTMLAttributes: {}
		};
	},
	group: "block list",
	content() {
		return `${this.options.itemTypeName}+`;
	},
	parseHTML() {
		return [{
			tag: `ul[data-type="${this.name}"]`,
			priority: 51
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"ul",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { "data-type": this.name }),
			0
		];
	},
	parseMarkdown: (token, h) => {
		return h.createNode("taskList", {}, h.parseChildren(token.items || []));
	},
	renderMarkdown: (node, h) => {
		if (!node.content) return "";
		return h.renderChildren(node.content, "\n");
	},
	markdownTokenizer: {
		name: "taskList",
		level: "block",
		start(src) {
			var _a;
			const index = (_a = src.match(/^\s*[-+*]\s+\[([ xX])\]\s+/)) == null ? void 0 : _a.index;
			return index !== void 0 ? index : -1;
		},
		tokenize(src, tokens, lexer) {
			const parseTaskListContent = (content) => {
				const nestedResult = parseIndentedBlocks(content, {
					itemPattern: /^(\s*)([-+*])\s+\[([ xX])\]\s+(.*)$/,
					extractItemData: (match) => ({
						indentLevel: match[1].length,
						mainContent: match[4],
						checked: match[3].toLowerCase() === "x"
					}),
					createToken: (data, nestedTokens) => ({
						type: "taskItem",
						raw: "",
						mainContent: data.mainContent,
						indentLevel: data.indentLevel,
						checked: data.checked,
						text: data.mainContent,
						tokens: lexer.inlineTokens(data.mainContent),
						nestedTokens
					}),
					customNestedParser: parseTaskListContent
				}, lexer);
				if (nestedResult) return [{
					type: "taskList",
					raw: nestedResult.raw,
					items: nestedResult.items
				}];
				return lexer.blockTokens(content);
			};
			const result = parseIndentedBlocks(src, {
				itemPattern: /^(\s*)([-+*])\s+\[([ xX])\]\s+(.*)$/,
				extractItemData: (match) => ({
					indentLevel: match[1].length,
					mainContent: match[4],
					checked: match[3].toLowerCase() === "x"
				}),
				createToken: (data, nestedTokens) => ({
					type: "taskItem",
					raw: "",
					mainContent: data.mainContent,
					indentLevel: data.indentLevel,
					checked: data.checked,
					text: data.mainContent,
					tokens: lexer.inlineTokens(data.mainContent),
					nestedTokens
				}),
				customNestedParser: parseTaskListContent
			}, lexer);
			if (!result) return;
			return {
				type: "taskList",
				raw: result.raw,
				items: result.items
			};
		}
	},
	markdownOptions: { indentsContent: true },
	addCommands() {
		return { toggleTaskList: () => ({ commands }) => {
			return commands.toggleList(this.name, this.options.itemTypeName);
		} };
	},
	addKeyboardShortcuts() {
		return { "Mod-Shift-9": () => this.editor.commands.toggleTaskList() };
	}
});
Extension.create({
	name: "listKit",
	addExtensions() {
		const extensions = [];
		if (this.options.bulletList !== false) extensions.push(BulletList.configure(this.options.bulletList));
		if (this.options.listItem !== false) extensions.push(ListItem.configure(this.options.listItem));
		if (this.options.listKeymap !== false) extensions.push(ListKeymap.configure(this.options.listKeymap));
		if (this.options.orderedList !== false) extensions.push(OrderedList.configure(this.options.orderedList));
		if (this.options.taskItem !== false) extensions.push(TaskItem.configure(this.options.taskItem));
		if (this.options.taskList !== false) extensions.push(TaskList.configure(this.options.taskList));
		return extensions;
	}
});
//#endregion
//#region node_modules/prosemirror-dropcursor/dist/index.js
/**
Create a plugin that, when added to a ProseMirror instance,
causes a decoration to show up at the drop position when something
is dragged over the editor.

Nodes may add a `disableDropCursor` property to their spec to
control the showing of a drop cursor inside them. This may be a
boolean or a function, which will be called with a view and a
position, and should return a boolean.
*/
function dropCursor(options = {}) {
	return new Plugin({ view(editorView) {
		return new DropCursorView(editorView, options);
	} });
}
var DropCursorView = class {
	constructor(editorView, options) {
		var _a;
		this.editorView = editorView;
		this.cursorPos = null;
		this.element = null;
		this.timeout = -1;
		this.width = (_a = options.width) !== null && _a !== void 0 ? _a : 1;
		this.color = options.color === false ? void 0 : options.color || "black";
		this.class = options.class;
		this.handlers = [
			"dragover",
			"dragend",
			"drop",
			"dragleave"
		].map((name) => {
			let handler = (e) => {
				this[name](e);
			};
			editorView.dom.addEventListener(name, handler);
			return {
				name,
				handler
			};
		});
	}
	destroy() {
		this.handlers.forEach(({ name, handler }) => this.editorView.dom.removeEventListener(name, handler));
	}
	update(editorView, prevState) {
		if (this.cursorPos != null && prevState.doc != editorView.state.doc) if (this.cursorPos > editorView.state.doc.content.size) this.setCursor(null);
		else this.updateOverlay();
	}
	setCursor(pos) {
		if (pos == this.cursorPos) return;
		this.cursorPos = pos;
		if (pos == null) {
			this.element.parentNode.removeChild(this.element);
			this.element = null;
		} else this.updateOverlay();
	}
	updateOverlay() {
		let $pos = this.editorView.state.doc.resolve(this.cursorPos);
		let isBlock = !$pos.parent.inlineContent, rect;
		let editorDOM = this.editorView.dom, editorRect = editorDOM.getBoundingClientRect();
		let scaleX = editorRect.width / editorDOM.offsetWidth, scaleY = editorRect.height / editorDOM.offsetHeight;
		if (isBlock) {
			let before = $pos.nodeBefore, after = $pos.nodeAfter;
			if (before || after) {
				let node = this.editorView.nodeDOM(this.cursorPos - (before ? before.nodeSize : 0));
				if (node) {
					let nodeRect = node.getBoundingClientRect();
					let top = before ? nodeRect.bottom : nodeRect.top;
					if (before && after) top = (top + this.editorView.nodeDOM(this.cursorPos).getBoundingClientRect().top) / 2;
					let halfWidth = this.width / 2 * scaleY;
					rect = {
						left: nodeRect.left,
						right: nodeRect.right,
						top: top - halfWidth,
						bottom: top + halfWidth
					};
				}
			}
		}
		if (!rect) {
			let coords = this.editorView.coordsAtPos(this.cursorPos);
			let halfWidth = this.width / 2 * scaleX;
			rect = {
				left: coords.left - halfWidth,
				right: coords.left + halfWidth,
				top: coords.top,
				bottom: coords.bottom
			};
		}
		let parent = this.editorView.dom.offsetParent;
		if (!this.element) {
			this.element = parent.appendChild(document.createElement("div"));
			if (this.class) this.element.className = this.class;
			this.element.style.cssText = "position: absolute; z-index: 50; pointer-events: none;";
			if (this.color) this.element.style.backgroundColor = this.color;
		}
		this.element.classList.toggle("prosemirror-dropcursor-block", isBlock);
		this.element.classList.toggle("prosemirror-dropcursor-inline", !isBlock);
		let parentLeft, parentTop;
		if (!parent || parent == document.body && getComputedStyle(parent).position == "static") {
			parentLeft = -pageXOffset;
			parentTop = -pageYOffset;
		} else {
			let rect = parent.getBoundingClientRect();
			let parentScaleX = rect.width / parent.offsetWidth, parentScaleY = rect.height / parent.offsetHeight;
			parentLeft = rect.left - parent.scrollLeft * parentScaleX;
			parentTop = rect.top - parent.scrollTop * parentScaleY;
		}
		this.element.style.left = (rect.left - parentLeft) / scaleX + "px";
		this.element.style.top = (rect.top - parentTop) / scaleY + "px";
		this.element.style.width = (rect.right - rect.left) / scaleX + "px";
		this.element.style.height = (rect.bottom - rect.top) / scaleY + "px";
	}
	scheduleRemoval(timeout) {
		clearTimeout(this.timeout);
		this.timeout = setTimeout(() => this.setCursor(null), timeout);
	}
	dragover(event) {
		if (!this.editorView.editable) return;
		let pos = this.editorView.posAtCoords({
			left: event.clientX,
			top: event.clientY
		});
		let node = pos && pos.inside >= 0 && this.editorView.state.doc.nodeAt(pos.inside);
		let disableDropCursor = node && node.type.spec.disableDropCursor;
		let disabled = typeof disableDropCursor == "function" ? disableDropCursor(this.editorView, pos, event) : disableDropCursor;
		if (pos && !disabled) {
			let target = pos.pos;
			if (this.editorView.dragging && this.editorView.dragging.slice) {
				let point = dropPoint(this.editorView.state.doc, target, this.editorView.dragging.slice);
				if (point != null) target = point;
			}
			this.setCursor(target);
			this.scheduleRemoval(5e3);
		}
	}
	dragend() {
		this.scheduleRemoval(20);
	}
	drop() {
		this.scheduleRemoval(20);
	}
	dragleave(event) {
		if (!this.editorView.dom.contains(event.relatedTarget)) this.setCursor(null);
	}
};
//#endregion
//#region node_modules/prosemirror-gapcursor/dist/index.js
/**
Gap cursor selections are represented using this class. Its
`$anchor` and `$head` properties both point at the cursor position.
*/
var GapCursor = class GapCursor extends Selection$1 {
	/**
	Create a gap cursor.
	*/
	constructor($pos) {
		super($pos, $pos);
	}
	map(doc, mapping) {
		let $pos = doc.resolve(mapping.map(this.head));
		return GapCursor.valid($pos) ? new GapCursor($pos) : Selection$1.near($pos);
	}
	content() {
		return Slice.empty;
	}
	eq(other) {
		return other instanceof GapCursor && other.head == this.head;
	}
	toJSON() {
		return {
			type: "gapcursor",
			pos: this.head
		};
	}
	/**
	@internal
	*/
	static fromJSON(doc, json) {
		if (typeof json.pos != "number") throw new RangeError("Invalid input for GapCursor.fromJSON");
		return new GapCursor(doc.resolve(json.pos));
	}
	/**
	@internal
	*/
	getBookmark() {
		return new GapBookmark(this.anchor);
	}
	/**
	@internal
	*/
	static valid($pos) {
		let parent = $pos.parent;
		if (parent.inlineContent || !closedBefore($pos) || !closedAfter($pos)) return false;
		let override = parent.type.spec.allowGapCursor;
		if (override != null) return override;
		let deflt = parent.contentMatchAt($pos.index()).defaultType;
		return deflt && deflt.isTextblock;
	}
	/**
	@internal
	*/
	static findGapCursorFrom($pos, dir, mustMove = false) {
		search: for (;;) {
			if (!mustMove && GapCursor.valid($pos)) return $pos;
			let pos = $pos.pos, next = null;
			for (let d = $pos.depth;; d--) {
				let parent = $pos.node(d);
				if (dir > 0 ? $pos.indexAfter(d) < parent.childCount : $pos.index(d) > 0) {
					next = parent.child(dir > 0 ? $pos.indexAfter(d) : $pos.index(d) - 1);
					break;
				} else if (d == 0) return null;
				pos += dir;
				let $cur = $pos.doc.resolve(pos);
				if (GapCursor.valid($cur)) return $cur;
			}
			for (;;) {
				let inside = dir > 0 ? next.firstChild : next.lastChild;
				if (!inside) {
					if (next.isAtom && !next.isText && !NodeSelection.isSelectable(next)) {
						$pos = $pos.doc.resolve(pos + next.nodeSize * dir);
						mustMove = false;
						continue search;
					}
					break;
				}
				next = inside;
				pos += dir;
				let $cur = $pos.doc.resolve(pos);
				if (GapCursor.valid($cur)) return $cur;
			}
			return null;
		}
	}
};
GapCursor.prototype.visible = false;
GapCursor.findFrom = GapCursor.findGapCursorFrom;
Selection$1.jsonID("gapcursor", GapCursor);
var GapBookmark = class GapBookmark {
	constructor(pos) {
		this.pos = pos;
	}
	map(mapping) {
		return new GapBookmark(mapping.map(this.pos));
	}
	resolve(doc) {
		let $pos = doc.resolve(this.pos);
		return GapCursor.valid($pos) ? new GapCursor($pos) : Selection$1.near($pos);
	}
};
function needsGap(type) {
	return type.isAtom || type.spec.isolating || type.spec.createGapCursor;
}
function closedBefore($pos) {
	for (let d = $pos.depth; d >= 0; d--) {
		let index = $pos.index(d), parent = $pos.node(d);
		if (index == 0) {
			if (parent.type.spec.isolating) return true;
			continue;
		}
		for (let before = parent.child(index - 1);; before = before.lastChild) {
			if (before.childCount == 0 && !before.inlineContent || needsGap(before.type)) return true;
			if (before.inlineContent) return false;
		}
	}
	return true;
}
function closedAfter($pos) {
	for (let d = $pos.depth; d >= 0; d--) {
		let index = $pos.indexAfter(d), parent = $pos.node(d);
		if (index == parent.childCount) {
			if (parent.type.spec.isolating) return true;
			continue;
		}
		for (let after = parent.child(index);; after = after.firstChild) {
			if (after.childCount == 0 && !after.inlineContent || needsGap(after.type)) return true;
			if (after.inlineContent) return false;
		}
	}
	return true;
}
/**
Create a gap cursor plugin. When enabled, this will capture clicks
near and arrow-key-motion past places that don't have a normally
selectable position nearby, and create a gap cursor selection for
them. The cursor is drawn as an element with class
`ProseMirror-gapcursor`. You can either include
`style/gapcursor.css` from the package's directory or add your own
styles to make it visible.
*/
function gapCursor() {
	return new Plugin({ props: {
		decorations: drawGapCursor,
		createSelectionBetween(_view, $anchor, $head) {
			return $anchor.pos == $head.pos && GapCursor.valid($head) ? new GapCursor($head) : null;
		},
		handleClick,
		handleKeyDown,
		handleDOMEvents: { beforeinput }
	} });
}
var handleKeyDown = keydownHandler({
	"ArrowLeft": arrow("horiz", -1),
	"ArrowRight": arrow("horiz", 1),
	"ArrowUp": arrow("vert", -1),
	"ArrowDown": arrow("vert", 1)
});
function arrow(axis, dir) {
	const dirStr = axis == "vert" ? dir > 0 ? "down" : "up" : dir > 0 ? "right" : "left";
	return function(state, dispatch, view) {
		let sel = state.selection;
		let $start = dir > 0 ? sel.$to : sel.$from, mustMove = sel.empty;
		if (sel instanceof TextSelection) {
			if (!view.endOfTextblock(dirStr) || $start.depth == 0) return false;
			mustMove = false;
			$start = state.doc.resolve(dir > 0 ? $start.after() : $start.before());
		}
		let $found = GapCursor.findGapCursorFrom($start, dir, mustMove);
		if (!$found) return false;
		if (dispatch) dispatch(state.tr.setSelection(new GapCursor($found)));
		return true;
	};
}
function handleClick(view, pos, event) {
	if (!view || !view.editable) return false;
	let $pos = view.state.doc.resolve(pos);
	if (!GapCursor.valid($pos)) return false;
	let clickPos = view.posAtCoords({
		left: event.clientX,
		top: event.clientY
	});
	if (clickPos && clickPos.inside > -1 && NodeSelection.isSelectable(view.state.doc.nodeAt(clickPos.inside))) return false;
	view.dispatch(view.state.tr.setSelection(new GapCursor($pos)));
	return true;
}
function beforeinput(view, event) {
	if (event.inputType != "insertCompositionText" || !(view.state.selection instanceof GapCursor)) return false;
	let { $from } = view.state.selection;
	let insert = $from.parent.contentMatchAt($from.index()).findWrapping(view.state.schema.nodes.text);
	if (!insert) return false;
	let frag = Fragment$1.empty;
	for (let i = insert.length - 1; i >= 0; i--) frag = Fragment$1.from(insert[i].createAndFill(null, frag));
	let tr = view.state.tr.replace($from.pos, $from.pos, new Slice(frag, 0, 0));
	tr.setSelection(TextSelection.near(tr.doc.resolve($from.pos + 1)));
	view.dispatch(tr);
	return false;
}
function drawGapCursor(state) {
	if (!(state.selection instanceof GapCursor)) return null;
	let node = document.createElement("div");
	node.className = "ProseMirror-gapcursor";
	return DecorationSet.create(state.doc, [Decoration.widget(state.selection.head, node, { key: "gapcursor" })]);
}
//#endregion
//#region node_modules/rope-sequence/dist/index.js
var GOOD_LEAF_SIZE = 200;
var RopeSequence = function RopeSequence() {};
RopeSequence.prototype.append = function append(other) {
	if (!other.length) return this;
	other = RopeSequence.from(other);
	return !this.length && other || other.length < GOOD_LEAF_SIZE && this.leafAppend(other) || this.length < GOOD_LEAF_SIZE && other.leafPrepend(this) || this.appendInner(other);
};
RopeSequence.prototype.prepend = function prepend(other) {
	if (!other.length) return this;
	return RopeSequence.from(other).append(this);
};
RopeSequence.prototype.appendInner = function appendInner(other) {
	return new Append(this, other);
};
RopeSequence.prototype.slice = function slice(from, to) {
	if (from === void 0) from = 0;
	if (to === void 0) to = this.length;
	if (from >= to) return RopeSequence.empty;
	return this.sliceInner(Math.max(0, from), Math.min(this.length, to));
};
RopeSequence.prototype.get = function get(i) {
	if (i < 0 || i >= this.length) return;
	return this.getInner(i);
};
RopeSequence.prototype.forEach = function forEach(f, from, to) {
	if (from === void 0) from = 0;
	if (to === void 0) to = this.length;
	if (from <= to) this.forEachInner(f, from, to, 0);
	else this.forEachInvertedInner(f, from, to, 0);
};
RopeSequence.prototype.map = function map(f, from, to) {
	if (from === void 0) from = 0;
	if (to === void 0) to = this.length;
	var result = [];
	this.forEach(function(elt, i) {
		return result.push(f(elt, i));
	}, from, to);
	return result;
};
RopeSequence.from = function from(values) {
	if (values instanceof RopeSequence) return values;
	return values && values.length ? new Leaf(values) : RopeSequence.empty;
};
var Leaf = /* @__PURE__ */ function(RopeSequence) {
	function Leaf(values) {
		RopeSequence.call(this);
		this.values = values;
	}
	if (RopeSequence) Leaf.__proto__ = RopeSequence;
	Leaf.prototype = Object.create(RopeSequence && RopeSequence.prototype);
	Leaf.prototype.constructor = Leaf;
	var prototypeAccessors = {
		length: { configurable: true },
		depth: { configurable: true }
	};
	Leaf.prototype.flatten = function flatten() {
		return this.values;
	};
	Leaf.prototype.sliceInner = function sliceInner(from, to) {
		if (from == 0 && to == this.length) return this;
		return new Leaf(this.values.slice(from, to));
	};
	Leaf.prototype.getInner = function getInner(i) {
		return this.values[i];
	};
	Leaf.prototype.forEachInner = function forEachInner(f, from, to, start) {
		for (var i = from; i < to; i++) if (f(this.values[i], start + i) === false) return false;
	};
	Leaf.prototype.forEachInvertedInner = function forEachInvertedInner(f, from, to, start) {
		for (var i = from - 1; i >= to; i--) if (f(this.values[i], start + i) === false) return false;
	};
	Leaf.prototype.leafAppend = function leafAppend(other) {
		if (this.length + other.length <= GOOD_LEAF_SIZE) return new Leaf(this.values.concat(other.flatten()));
	};
	Leaf.prototype.leafPrepend = function leafPrepend(other) {
		if (this.length + other.length <= GOOD_LEAF_SIZE) return new Leaf(other.flatten().concat(this.values));
	};
	prototypeAccessors.length.get = function() {
		return this.values.length;
	};
	prototypeAccessors.depth.get = function() {
		return 0;
	};
	Object.defineProperties(Leaf.prototype, prototypeAccessors);
	return Leaf;
}(RopeSequence);
RopeSequence.empty = new Leaf([]);
var Append = /* @__PURE__ */ function(RopeSequence) {
	function Append(left, right) {
		RopeSequence.call(this);
		this.left = left;
		this.right = right;
		this.length = left.length + right.length;
		this.depth = Math.max(left.depth, right.depth) + 1;
	}
	if (RopeSequence) Append.__proto__ = RopeSequence;
	Append.prototype = Object.create(RopeSequence && RopeSequence.prototype);
	Append.prototype.constructor = Append;
	Append.prototype.flatten = function flatten() {
		return this.left.flatten().concat(this.right.flatten());
	};
	Append.prototype.getInner = function getInner(i) {
		return i < this.left.length ? this.left.get(i) : this.right.get(i - this.left.length);
	};
	Append.prototype.forEachInner = function forEachInner(f, from, to, start) {
		var leftLen = this.left.length;
		if (from < leftLen && this.left.forEachInner(f, from, Math.min(to, leftLen), start) === false) return false;
		if (to > leftLen && this.right.forEachInner(f, Math.max(from - leftLen, 0), Math.min(this.length, to) - leftLen, start + leftLen) === false) return false;
	};
	Append.prototype.forEachInvertedInner = function forEachInvertedInner(f, from, to, start) {
		var leftLen = this.left.length;
		if (from > leftLen && this.right.forEachInvertedInner(f, from - leftLen, Math.max(to, leftLen) - leftLen, start + leftLen) === false) return false;
		if (to < leftLen && this.left.forEachInvertedInner(f, Math.min(from, leftLen), to, start) === false) return false;
	};
	Append.prototype.sliceInner = function sliceInner(from, to) {
		if (from == 0 && to == this.length) return this;
		var leftLen = this.left.length;
		if (to <= leftLen) return this.left.slice(from, to);
		if (from >= leftLen) return this.right.slice(from - leftLen, to - leftLen);
		return this.left.slice(from, leftLen).append(this.right.slice(0, to - leftLen));
	};
	Append.prototype.leafAppend = function leafAppend(other) {
		var inner = this.right.leafAppend(other);
		if (inner) return new Append(this.left, inner);
	};
	Append.prototype.leafPrepend = function leafPrepend(other) {
		var inner = this.left.leafPrepend(other);
		if (inner) return new Append(inner, this.right);
	};
	Append.prototype.appendInner = function appendInner(other) {
		if (this.left.depth >= Math.max(this.right.depth, other.depth) + 1) return new Append(this.left, new Append(this.right, other));
		return new Append(this, other);
	};
	return Append;
}(RopeSequence);
//#endregion
//#region node_modules/prosemirror-history/dist/index.js
var max_empty_items = 500;
var Branch = class Branch {
	constructor(items, eventCount) {
		this.items = items;
		this.eventCount = eventCount;
	}
	popEvent(state, preserveItems) {
		if (this.eventCount == 0) return null;
		let end = this.items.length;
		for (;; end--) if (this.items.get(end - 1).selection) {
			--end;
			break;
		}
		let remap, mapFrom;
		if (preserveItems) {
			remap = this.remapping(end, this.items.length);
			mapFrom = remap.maps.length;
		}
		let transform = state.tr;
		let selection, remaining;
		let addAfter = [], addBefore = [];
		this.items.forEach((item, i) => {
			if (!item.step) {
				if (!remap) {
					remap = this.remapping(end, i + 1);
					mapFrom = remap.maps.length;
				}
				mapFrom--;
				addBefore.push(item);
				return;
			}
			if (remap) {
				addBefore.push(new Item(item.map));
				let step = item.step.map(remap.slice(mapFrom)), map;
				if (step && transform.maybeStep(step).doc) {
					map = transform.mapping.maps[transform.mapping.maps.length - 1];
					addAfter.push(new Item(map, void 0, void 0, addAfter.length + addBefore.length));
				}
				mapFrom--;
				if (map) remap.appendMap(map, mapFrom);
			} else transform.maybeStep(item.step);
			if (item.selection) {
				selection = remap ? item.selection.map(remap.slice(mapFrom)) : item.selection;
				remaining = new Branch(this.items.slice(0, end).append(addBefore.reverse().concat(addAfter)), this.eventCount - 1);
				return false;
			}
		}, this.items.length, 0);
		return {
			remaining,
			transform,
			selection
		};
	}
	addTransform(transform, selection, histOptions, preserveItems) {
		let newItems = [], eventCount = this.eventCount;
		let oldItems = this.items, lastItem = !preserveItems && oldItems.length ? oldItems.get(oldItems.length - 1) : null;
		for (let i = 0; i < transform.steps.length; i++) {
			let step = transform.steps[i].invert(transform.docs[i]);
			let item = new Item(transform.mapping.maps[i], step, selection), merged;
			if (merged = lastItem && lastItem.merge(item)) {
				item = merged;
				if (i) newItems.pop();
				else oldItems = oldItems.slice(0, oldItems.length - 1);
			}
			newItems.push(item);
			if (selection) {
				eventCount++;
				selection = void 0;
			}
			if (!preserveItems) lastItem = item;
		}
		let overflow = eventCount - histOptions.depth;
		if (overflow > DEPTH_OVERFLOW) {
			oldItems = cutOffEvents(oldItems, overflow);
			eventCount -= overflow;
		}
		return new Branch(oldItems.append(newItems), eventCount);
	}
	remapping(from, to) {
		let maps = new Mapping();
		this.items.forEach((item, i) => {
			let mirrorPos = item.mirrorOffset != null && i - item.mirrorOffset >= from ? maps.maps.length - item.mirrorOffset : void 0;
			maps.appendMap(item.map, mirrorPos);
		}, from, to);
		return maps;
	}
	addMaps(array) {
		if (this.eventCount == 0) return this;
		return new Branch(this.items.append(array.map((map) => new Item(map))), this.eventCount);
	}
	rebased(rebasedTransform, rebasedCount) {
		if (!this.eventCount) return this;
		let rebasedItems = [], start = Math.max(0, this.items.length - rebasedCount);
		let mapping = rebasedTransform.mapping;
		let newUntil = rebasedTransform.steps.length;
		let eventCount = this.eventCount;
		this.items.forEach((item) => {
			if (item.selection) eventCount--;
		}, start);
		let iRebased = rebasedCount;
		this.items.forEach((item) => {
			let pos = mapping.getMirror(--iRebased);
			if (pos == null) return;
			newUntil = Math.min(newUntil, pos);
			let map = mapping.maps[pos];
			if (item.step) {
				let step = rebasedTransform.steps[pos].invert(rebasedTransform.docs[pos]);
				let selection = item.selection && item.selection.map(mapping.slice(iRebased + 1, pos));
				if (selection) eventCount++;
				rebasedItems.push(new Item(map, step, selection));
			} else rebasedItems.push(new Item(map));
		}, start);
		let newMaps = [];
		for (let i = rebasedCount; i < newUntil; i++) newMaps.push(new Item(mapping.maps[i]));
		let branch = new Branch(this.items.slice(0, start).append(newMaps).append(rebasedItems), eventCount);
		if (branch.emptyItemCount() > max_empty_items) branch = branch.compress(this.items.length - rebasedItems.length);
		return branch;
	}
	emptyItemCount() {
		let count = 0;
		this.items.forEach((item) => {
			if (!item.step) count++;
		});
		return count;
	}
	compress(upto = this.items.length) {
		let remap = this.remapping(0, upto), mapFrom = remap.maps.length;
		let items = [], events = 0;
		this.items.forEach((item, i) => {
			if (i >= upto) {
				items.push(item);
				if (item.selection) events++;
			} else if (item.step) {
				let step = item.step.map(remap.slice(mapFrom)), map = step && step.getMap();
				mapFrom--;
				if (map) remap.appendMap(map, mapFrom);
				if (step) {
					let selection = item.selection && item.selection.map(remap.slice(mapFrom));
					if (selection) events++;
					let newItem = new Item(map.invert(), step, selection), merged, last = items.length - 1;
					if (merged = items.length && items[last].merge(newItem)) items[last] = merged;
					else items.push(newItem);
				}
			} else if (item.map) mapFrom--;
		}, this.items.length, 0);
		return new Branch(RopeSequence.from(items.reverse()), events);
	}
};
Branch.empty = new Branch(RopeSequence.empty, 0);
function cutOffEvents(items, n) {
	let cutPoint;
	items.forEach((item, i) => {
		if (item.selection && n-- == 0) {
			cutPoint = i;
			return false;
		}
	});
	return items.slice(cutPoint);
}
var Item = class Item {
	constructor(map, step, selection, mirrorOffset) {
		this.map = map;
		this.step = step;
		this.selection = selection;
		this.mirrorOffset = mirrorOffset;
	}
	merge(other) {
		if (this.step && other.step && !other.selection) {
			let step = other.step.merge(this.step);
			if (step) return new Item(step.getMap().invert(), step, this.selection);
		}
	}
};
var HistoryState = class {
	constructor(done, undone, prevRanges, prevTime, prevComposition) {
		this.done = done;
		this.undone = undone;
		this.prevRanges = prevRanges;
		this.prevTime = prevTime;
		this.prevComposition = prevComposition;
	}
};
var DEPTH_OVERFLOW = 20;
function applyTransaction(history, state, tr, options) {
	let historyTr = tr.getMeta(historyKey), rebased;
	if (historyTr) return historyTr.historyState;
	if (tr.getMeta(closeHistoryKey)) history = new HistoryState(history.done, history.undone, null, 0, -1);
	let appended = tr.getMeta("appendedTransaction");
	if (tr.steps.length == 0) return history;
	else if (appended && appended.getMeta(historyKey)) if (appended.getMeta(historyKey).redo) return new HistoryState(history.done.addTransform(tr, void 0, options, mustPreserveItems(state)), history.undone, rangesFor(tr.mapping.maps), history.prevTime, history.prevComposition);
	else return new HistoryState(history.done, history.undone.addTransform(tr, void 0, options, mustPreserveItems(state)), null, history.prevTime, history.prevComposition);
	else if (tr.getMeta("addToHistory") !== false && !(appended && appended.getMeta("addToHistory") === false)) {
		let composition = tr.getMeta("composition");
		let newGroup = history.prevTime == 0 || !appended && history.prevComposition != composition && (history.prevTime < (tr.time || 0) - options.newGroupDelay || !isAdjacentTo(tr, history.prevRanges));
		let prevRanges = appended ? mapRanges(history.prevRanges, tr.mapping) : rangesFor(tr.mapping.maps);
		return new HistoryState(history.done.addTransform(tr, newGroup ? state.selection.getBookmark() : void 0, options, mustPreserveItems(state)), Branch.empty, prevRanges, tr.time, composition == null ? history.prevComposition : composition);
	} else if (rebased = tr.getMeta("rebased")) return new HistoryState(history.done.rebased(tr, rebased), history.undone.rebased(tr, rebased), mapRanges(history.prevRanges, tr.mapping), history.prevTime, history.prevComposition);
	else return new HistoryState(history.done.addMaps(tr.mapping.maps), history.undone.addMaps(tr.mapping.maps), mapRanges(history.prevRanges, tr.mapping), history.prevTime, history.prevComposition);
}
function isAdjacentTo(transform, prevRanges) {
	if (!prevRanges) return false;
	if (!transform.docChanged) return true;
	let adjacent = false;
	transform.mapping.maps[0].forEach((start, end) => {
		for (let i = 0; i < prevRanges.length; i += 2) if (start <= prevRanges[i + 1] && end >= prevRanges[i]) adjacent = true;
	});
	return adjacent;
}
function rangesFor(maps) {
	let result = [];
	for (let i = maps.length - 1; i >= 0 && result.length == 0; i--) maps[i].forEach((_from, _to, from, to) => result.push(from, to));
	return result;
}
function mapRanges(ranges, mapping) {
	if (!ranges) return null;
	let result = [];
	for (let i = 0; i < ranges.length; i += 2) {
		let from = mapping.map(ranges[i], 1), to = mapping.map(ranges[i + 1], -1);
		if (from <= to) result.push(from, to);
	}
	return result;
}
function histTransaction(history, state, redo) {
	let preserveItems = mustPreserveItems(state);
	let histOptions = historyKey.get(state).spec.config;
	let pop = (redo ? history.undone : history.done).popEvent(state, preserveItems);
	if (!pop) return null;
	let selection = pop.selection.resolve(pop.transform.doc);
	let added = (redo ? history.done : history.undone).addTransform(pop.transform, state.selection.getBookmark(), histOptions, preserveItems);
	let newHist = new HistoryState(redo ? added : pop.remaining, redo ? pop.remaining : added, null, 0, -1);
	return pop.transform.setSelection(selection).setMeta(historyKey, {
		redo,
		historyState: newHist
	});
}
var cachedPreserveItems = false, cachedPreserveItemsPlugins = null;
function mustPreserveItems(state) {
	let plugins = state.plugins;
	if (cachedPreserveItemsPlugins != plugins) {
		cachedPreserveItems = false;
		cachedPreserveItemsPlugins = plugins;
		for (let i = 0; i < plugins.length; i++) if (plugins[i].spec.historyPreserveItems) {
			cachedPreserveItems = true;
			break;
		}
	}
	return cachedPreserveItems;
}
var historyKey = new PluginKey("history");
var closeHistoryKey = new PluginKey("closeHistory");
/**
Returns a plugin that enables the undo history for an editor. The
plugin will track undo and redo stacks, which can be used with the
[`undo`](https://prosemirror.net/docs/ref/#history.undo) and [`redo`](https://prosemirror.net/docs/ref/#history.redo) commands.

You can set an `"addToHistory"` [metadata
property](https://prosemirror.net/docs/ref/#state.Transaction.setMeta) of `false` on a transaction
to prevent it from being rolled back by undo.
*/
function history(config = {}) {
	config = {
		depth: config.depth || 100,
		newGroupDelay: config.newGroupDelay || 500
	};
	return new Plugin({
		key: historyKey,
		state: {
			init() {
				return new HistoryState(Branch.empty, Branch.empty, null, 0, -1);
			},
			apply(tr, hist, state) {
				return applyTransaction(hist, state, tr, config);
			}
		},
		config,
		props: { handleDOMEvents: { beforeinput(view, e) {
			let inputType = e.inputType;
			let command = inputType == "historyUndo" ? undo : inputType == "historyRedo" ? redo : null;
			if (!command || !view.editable) return false;
			e.preventDefault();
			return command(view.state, view.dispatch);
		} } }
	});
}
function buildCommand(redo, scroll) {
	return (state, dispatch) => {
		let hist = historyKey.getState(state);
		if (!hist || (redo ? hist.undone : hist.done).eventCount == 0) return false;
		if (dispatch) {
			let tr = histTransaction(hist, state, redo);
			if (tr) dispatch(scroll ? tr.scrollIntoView() : tr);
		}
		return true;
	};
}
/**
A command function that undoes the last change, if any.
*/
var undo = buildCommand(false, true);
/**
A command function that redoes the last undone change, if any.
*/
var redo = buildCommand(true, true);
//#endregion
//#region node_modules/@tiptap/extensions/dist/index.js
var CharacterCount = Extension.create({
	name: "characterCount",
	addOptions() {
		return {
			limit: null,
			mode: "textSize",
			textCounter: (text) => text.length,
			wordCounter: (text) => text.split(" ").filter((word) => word !== "").length
		};
	},
	addStorage() {
		return {
			characters: () => 0,
			words: () => 0
		};
	},
	onBeforeCreate() {
		this.storage.characters = (options) => {
			const node = (options == null ? void 0 : options.node) || this.editor.state.doc;
			if (((options == null ? void 0 : options.mode) || this.options.mode) === "textSize") {
				const text = node.textBetween(0, node.content.size, void 0, " ");
				return this.options.textCounter(text);
			}
			return node.nodeSize;
		};
		this.storage.words = (options) => {
			const node = (options == null ? void 0 : options.node) || this.editor.state.doc;
			const text = node.textBetween(0, node.content.size, " ", " ");
			return this.options.wordCounter(text);
		};
	},
	addProseMirrorPlugins() {
		let initialEvaluationDone = false;
		return [new Plugin({
			key: new PluginKey("characterCount"),
			appendTransaction: (transactions, oldState, newState) => {
				if (initialEvaluationDone) return;
				const limit = this.options.limit;
				if (limit === null || limit === void 0 || limit === 0) {
					initialEvaluationDone = true;
					return;
				}
				const initialContentSize = this.storage.characters({ node: newState.doc });
				if (initialContentSize > limit) {
					const over = initialContentSize - limit;
					const from = 0;
					const to = over;
					console.warn(`[CharacterCount] Initial content exceeded limit of ${limit} characters. Content was automatically trimmed.`);
					const tr = newState.tr.deleteRange(from, to);
					initialEvaluationDone = true;
					return tr;
				}
				initialEvaluationDone = true;
			},
			filterTransaction: (transaction, state) => {
				const limit = this.options.limit;
				if (!transaction.docChanged || limit === 0 || limit === null || limit === void 0) return true;
				const oldSize = this.storage.characters({ node: state.doc });
				const newSize = this.storage.characters({ node: transaction.doc });
				if (newSize <= limit) return true;
				if (oldSize > limit && newSize > limit && newSize <= oldSize) return true;
				if (oldSize > limit && newSize > limit && newSize > oldSize) return false;
				if (!transaction.getMeta("paste")) return false;
				const pos = transaction.selection.$head.pos;
				const from = pos - (newSize - limit);
				const to = pos;
				transaction.deleteRange(from, to);
				if (this.storage.characters({ node: transaction.doc }) > limit) return false;
				return true;
			}
		})];
	}
});
var Dropcursor = Extension.create({
	name: "dropCursor",
	addOptions() {
		return {
			color: "currentColor",
			width: 1,
			class: void 0
		};
	},
	addProseMirrorPlugins() {
		return [dropCursor(this.options)];
	}
});
Extension.create({
	name: "focus",
	addOptions() {
		return {
			className: "has-focus",
			mode: "all"
		};
	},
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("focus"),
			props: { decorations: ({ doc, selection }) => {
				const { isEditable, isFocused } = this.editor;
				const { anchor } = selection;
				const decorations = [];
				if (!isEditable || !isFocused) return DecorationSet.create(doc, []);
				let maxLevels = 0;
				if (this.options.mode === "deepest") doc.descendants((node, pos) => {
					if (node.isText) return;
					if (!(anchor >= pos && anchor <= pos + node.nodeSize - 1)) return false;
					maxLevels += 1;
				});
				let currentLevel = 0;
				doc.descendants((node, pos) => {
					if (node.isText) return false;
					if (!(anchor >= pos && anchor <= pos + node.nodeSize - 1)) return false;
					currentLevel += 1;
					if (this.options.mode === "deepest" && maxLevels - currentLevel > 0 || this.options.mode === "shallowest" && currentLevel > 1) return this.options.mode === "deepest";
					decorations.push(Decoration.node(pos, pos + node.nodeSize, { class: this.options.className }));
				});
				return DecorationSet.create(doc, decorations);
			} }
		})];
	}
});
var Gapcursor = Extension.create({
	name: "gapCursor",
	addProseMirrorPlugins() {
		return [gapCursor()];
	},
	extendNodeSchema(extension) {
		var _a;
		return { allowGapCursor: (_a = callOrReturn(getExtensionField(extension, "allowGapCursor", {
			name: extension.name,
			options: extension.options,
			storage: extension.storage
		}))) != null ? _a : null };
	}
});
var DEFAULT_DATA_ATTRIBUTE = "placeholder";
function preparePlaceholderAttribute(attr) {
	return attr.replace(/\s+/g, "-").replace(/[^a-zA-Z0-9-]/g, "").replace(/^[0-9-]+/, "").replace(/^-+/, "").toLowerCase();
}
var Placeholder = Extension.create({
	name: "placeholder",
	addOptions() {
		return {
			emptyEditorClass: "is-editor-empty",
			emptyNodeClass: "is-empty",
			dataAttribute: DEFAULT_DATA_ATTRIBUTE,
			placeholder: "Write something …",
			showOnlyWhenEditable: true,
			showOnlyCurrent: true,
			includeChildren: false
		};
	},
	addProseMirrorPlugins() {
		const dataAttribute = this.options.dataAttribute ? `data-${preparePlaceholderAttribute(this.options.dataAttribute)}` : `data-${DEFAULT_DATA_ATTRIBUTE}`;
		return [new Plugin({
			key: new PluginKey("placeholder"),
			props: { decorations: ({ doc, selection }) => {
				const active = this.editor.isEditable || !this.options.showOnlyWhenEditable;
				const { anchor } = selection;
				const decorations = [];
				if (!active) return null;
				const isEmptyDoc = this.editor.isEmpty;
				doc.descendants((node, pos) => {
					const hasAnchor = anchor >= pos && anchor <= pos + node.nodeSize;
					const isEmpty = !node.isLeaf && isNodeEmpty(node);
					if (!node.type.isTextblock) return this.options.includeChildren;
					if ((hasAnchor || !this.options.showOnlyCurrent) && isEmpty) {
						const classes = [this.options.emptyNodeClass];
						if (isEmptyDoc) classes.push(this.options.emptyEditorClass);
						const decoration = Decoration.node(pos, pos + node.nodeSize, {
							class: classes.join(" "),
							[dataAttribute]: typeof this.options.placeholder === "function" ? this.options.placeholder({
								editor: this.editor,
								node,
								pos,
								hasAnchor
							}) : this.options.placeholder
						});
						decorations.push(decoration);
					}
					return this.options.includeChildren;
				});
				return DecorationSet.create(doc, decorations);
			} }
		})];
	}
});
Extension.create({
	name: "selection",
	addOptions() {
		return { className: "selection" };
	},
	addProseMirrorPlugins() {
		const { editor, options } = this;
		return [new Plugin({
			key: new PluginKey("selection"),
			props: { decorations(state) {
				if (state.selection.empty || editor.isFocused || !editor.isEditable || isNodeSelection(state.selection) || editor.view.dragging) return null;
				return DecorationSet.create(state.doc, [Decoration.inline(state.selection.from, state.selection.to, { class: options.className })]);
			} }
		})];
	}
});
function nodeEqualsType({ types, node }) {
	return node && Array.isArray(types) && types.includes(node.type) || (node == null ? void 0 : node.type) === types;
}
Extension.create({
	name: "trailingNode",
	addOptions() {
		return {
			node: void 0,
			notAfter: []
		};
	},
	addProseMirrorPlugins() {
		var _a;
		const plugin = new PluginKey(this.name);
		const defaultNode = this.options.node || ((_a = this.editor.schema.topNodeType.contentMatch.defaultType) == null ? void 0 : _a.name) || "paragraph";
		const disabledNodes = Object.entries(this.editor.schema.nodes).map(([, value]) => value).filter((node) => (this.options.notAfter || []).concat(defaultNode).includes(node.name));
		return [new Plugin({
			key: plugin,
			appendTransaction: (transactions, __, state) => {
				const { doc, tr, schema } = state;
				const shouldInsertNodeAtEnd = plugin.getState(state);
				const endPosition = doc.content.size;
				const type = schema.nodes[defaultNode];
				if (transactions.some((transaction) => transaction.getMeta("skipTrailingNode"))) return;
				if (!shouldInsertNodeAtEnd) return;
				return tr.insert(endPosition, type.create());
			},
			state: {
				init: (_, state) => {
					const lastNode = state.tr.doc.lastChild;
					return !nodeEqualsType({
						node: lastNode,
						types: disabledNodes
					});
				},
				apply: (tr, value) => {
					if (!tr.docChanged) return value;
					if (tr.getMeta("__uniqueIDTransaction")) return value;
					const lastNode = tr.doc.lastChild;
					return !nodeEqualsType({
						node: lastNode,
						types: disabledNodes
					});
				}
			}
		})];
	}
});
var UndoRedo = Extension.create({
	name: "undoRedo",
	addOptions() {
		return {
			depth: 100,
			newGroupDelay: 500
		};
	},
	addCommands() {
		return {
			undo: () => ({ state, dispatch }) => {
				return undo(state, dispatch);
			},
			redo: () => ({ state, dispatch }) => {
				return redo(state, dispatch);
			}
		};
	},
	addProseMirrorPlugins() {
		return [history(this.options)];
	},
	addKeyboardShortcuts() {
		return {
			"Mod-z": () => this.editor.commands.undo(),
			"Shift-Mod-z": () => this.editor.commands.redo(),
			"Mod-y": () => this.editor.commands.redo(),
			"Mod-я": () => this.editor.commands.undo(),
			"Shift-Mod-я": () => this.editor.commands.redo()
		};
	}
});
//#endregion
//#region node_modules/@tiptap/extension-character-count/dist/index.js
var index_default$16 = CharacterCount;
//#endregion
//#region node_modules/@tiptap/extension-code/dist/index.js
var inputRegex$1 = /(^|[^`])`([^`]+)`(?!`)$/;
var pasteRegex$1 = /(^|[^`])`([^`]+)`(?!`)/g;
var index_default$15 = Mark.create({
	name: "code",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	excludes: "_",
	code: true,
	exitable: true,
	parseHTML() {
		return [{ tag: "code" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"code",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	markdownTokenName: "codespan",
	parseMarkdown: (token, helpers) => {
		return helpers.applyMark("code", [{
			type: "text",
			text: token.text || ""
		}]);
	},
	renderMarkdown: (node, h) => {
		if (!node.content) return "";
		return `\`${h.renderChildren(node.content)}\``;
	},
	addCommands() {
		return {
			setCode: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleCode: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetCode: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return { "Mod-e": () => this.editor.commands.toggleCode() };
	},
	addInputRules() {
		return [markInputRule({
			find: inputRegex$1,
			type: this.type
		})];
	},
	addPasteRules() {
		return [markPasteRule({
			find: pasteRegex$1,
			type: this.type
		})];
	}
});
//#endregion
//#region node_modules/@tiptap/extension-code-block/dist/index.js
var DEFAULT_TAB_SIZE = 4;
var backtickInputRegex = /^```([a-z]+)?[\s\n]$/;
var tildeInputRegex = /^~~~([a-z]+)?[\s\n]$/;
var CodeBlock = Node3.create({
	name: "codeBlock",
	addOptions() {
		return {
			languageClassPrefix: "language-",
			exitOnTripleEnter: true,
			exitOnArrowDown: true,
			defaultLanguage: null,
			enableTabIndentation: false,
			tabSize: DEFAULT_TAB_SIZE,
			HTMLAttributes: {}
		};
	},
	content: "text*",
	marks: "",
	group: "block",
	code: true,
	defining: true,
	addAttributes() {
		return { language: {
			default: this.options.defaultLanguage,
			parseHTML: (element) => {
				var _a;
				const { languageClassPrefix } = this.options;
				if (!languageClassPrefix) return null;
				const language = [...((_a = element.firstElementChild) == null ? void 0 : _a.classList) || []].filter((className) => className.startsWith(languageClassPrefix)).map((className) => className.replace(languageClassPrefix, ""))[0];
				if (!language) return null;
				return language;
			},
			rendered: false
		} };
	},
	parseHTML() {
		return [{
			tag: "pre",
			preserveWhitespace: "full"
		}];
	},
	renderHTML({ node, HTMLAttributes }) {
		return [
			"pre",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			[
				"code",
				{ class: node.attrs.language ? this.options.languageClassPrefix + node.attrs.language : null },
				0
			]
		];
	},
	markdownTokenName: "code",
	parseMarkdown: (token, helpers) => {
		var _a, _b;
		if (((_a = token.raw) == null ? void 0 : _a.startsWith("```")) === false && ((_b = token.raw) == null ? void 0 : _b.startsWith("~~~")) === false && token.codeBlockStyle !== "indented") return [];
		return helpers.createNode("codeBlock", { language: token.lang || null }, token.text ? [helpers.createTextNode(token.text)] : []);
	},
	renderMarkdown: (node, h) => {
		var _a;
		let output = "";
		const language = ((_a = node.attrs) == null ? void 0 : _a.language) || "";
		if (!node.content) output = `\`\`\`${language}

\`\`\``;
		else output = [
			`\`\`\`${language}`,
			h.renderChildren(node.content),
			"```"
		].join("\n");
		return output;
	},
	addCommands() {
		return {
			setCodeBlock: (attributes) => ({ commands }) => {
				return commands.setNode(this.name, attributes);
			},
			toggleCodeBlock: (attributes) => ({ commands }) => {
				return commands.toggleNode(this.name, "paragraph", attributes);
			}
		};
	},
	addKeyboardShortcuts() {
		return {
			"Mod-Alt-c": () => this.editor.commands.toggleCodeBlock(),
			Backspace: () => {
				const { empty, $anchor } = this.editor.state.selection;
				const isAtStart = $anchor.pos === 1;
				if (!empty || $anchor.parent.type.name !== this.name) return false;
				if (isAtStart || !$anchor.parent.textContent.length) return this.editor.commands.clearNodes();
				return false;
			},
			Tab: ({ editor }) => {
				var _a;
				if (!this.options.enableTabIndentation) return false;
				const tabSize = (_a = this.options.tabSize) != null ? _a : DEFAULT_TAB_SIZE;
				const { state } = editor;
				const { selection } = state;
				const { $from, empty } = selection;
				if ($from.parent.type !== this.type) return false;
				const indent = " ".repeat(tabSize);
				if (empty) return editor.commands.insertContent(indent);
				return editor.commands.command(({ tr }) => {
					const { from, to } = selection;
					const indentedText = state.doc.textBetween(from, to, "\n", "\n").split("\n").map((line) => indent + line).join("\n");
					tr.replaceWith(from, to, state.schema.text(indentedText));
					return true;
				});
			},
			"Shift-Tab": ({ editor }) => {
				var _a;
				if (!this.options.enableTabIndentation) return false;
				const tabSize = (_a = this.options.tabSize) != null ? _a : DEFAULT_TAB_SIZE;
				const { state } = editor;
				const { selection } = state;
				const { $from, empty } = selection;
				if ($from.parent.type !== this.type) return false;
				if (empty) return editor.commands.command(({ tr }) => {
					var _a2;
					const { pos } = $from;
					const codeBlockStart = $from.start();
					const codeBlockEnd = $from.end();
					const lines = state.doc.textBetween(codeBlockStart, codeBlockEnd, "\n", "\n").split("\n");
					let currentLineIndex = 0;
					let charCount = 0;
					const relativeCursorPos = pos - codeBlockStart;
					for (let i = 0; i < lines.length; i += 1) {
						if (charCount + lines[i].length >= relativeCursorPos) {
							currentLineIndex = i;
							break;
						}
						charCount += lines[i].length + 1;
					}
					const leadingSpaces = ((_a2 = lines[currentLineIndex].match(/^ */)) == null ? void 0 : _a2[0]) || "";
					const spacesToRemove = Math.min(leadingSpaces.length, tabSize);
					if (spacesToRemove === 0) return true;
					let lineStartPos = codeBlockStart;
					for (let i = 0; i < currentLineIndex; i += 1) lineStartPos += lines[i].length + 1;
					tr.delete(lineStartPos, lineStartPos + spacesToRemove);
					if (pos - lineStartPos <= spacesToRemove) tr.setSelection(TextSelection.create(tr.doc, lineStartPos));
					return true;
				});
				return editor.commands.command(({ tr }) => {
					const { from, to } = selection;
					const reverseIndentText = state.doc.textBetween(from, to, "\n", "\n").split("\n").map((line) => {
						var _a2;
						const leadingSpaces = ((_a2 = line.match(/^ */)) == null ? void 0 : _a2[0]) || "";
						const spacesToRemove = Math.min(leadingSpaces.length, tabSize);
						return line.slice(spacesToRemove);
					}).join("\n");
					tr.replaceWith(from, to, state.schema.text(reverseIndentText));
					return true;
				});
			},
			Enter: ({ editor }) => {
				if (!this.options.exitOnTripleEnter) return false;
				const { state } = editor;
				const { selection } = state;
				const { $from, empty } = selection;
				if (!empty || $from.parent.type !== this.type) return false;
				const isAtEnd = $from.parentOffset === $from.parent.nodeSize - 2;
				const endsWithDoubleNewline = $from.parent.textContent.endsWith("\n\n");
				if (!isAtEnd || !endsWithDoubleNewline) return false;
				return editor.chain().command(({ tr }) => {
					tr.delete($from.pos - 2, $from.pos);
					return true;
				}).exitCode().run();
			},
			ArrowDown: ({ editor }) => {
				if (!this.options.exitOnArrowDown) return false;
				const { state } = editor;
				const { selection, doc } = state;
				const { $from, empty } = selection;
				if (!empty || $from.parent.type !== this.type) return false;
				if (!($from.parentOffset === $from.parent.nodeSize - 2)) return false;
				const after = $from.after();
				if (after === void 0) return false;
				if (doc.nodeAt(after)) return editor.commands.command(({ tr }) => {
					tr.setSelection(Selection$1.near(doc.resolve(after)));
					return true;
				});
				return editor.commands.exitCode();
			}
		};
	},
	addInputRules() {
		return [textblockTypeInputRule({
			find: backtickInputRegex,
			type: this.type,
			getAttributes: (match) => ({ language: match[1] })
		}), textblockTypeInputRule({
			find: tildeInputRegex,
			type: this.type,
			getAttributes: (match) => ({ language: match[1] })
		})];
	},
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("codeBlockVSCodeHandler"),
			props: { handlePaste: (view, event) => {
				if (!event.clipboardData) return false;
				if (this.editor.isActive(this.type.name)) return false;
				const text = event.clipboardData.getData("text/plain");
				const vscode = event.clipboardData.getData("vscode-editor-data");
				const vscodeData = vscode ? JSON.parse(vscode) : void 0;
				const language = vscodeData == null ? void 0 : vscodeData.mode;
				if (!text || !language) return false;
				const { tr, schema } = view.state;
				const textNode = schema.text(text.replace(/\r\n?/g, "\n"));
				tr.replaceSelectionWith(this.type.create({ language }, textNode));
				if (tr.selection.$from.parent.type !== this.type) tr.setSelection(TextSelection.near(tr.doc.resolve(Math.max(0, tr.selection.from - 2))));
				tr.setMeta("paste", true);
				view.dispatch(tr);
				return true;
			} }
		})];
	}
});
var core_default = (/* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin(((exports, module) => {
	function deepFreeze(obj) {
		if (obj instanceof Map) obj.clear = obj.delete = obj.set = function() {
			throw new Error("map is read-only");
		};
		else if (obj instanceof Set) obj.add = obj.clear = obj.delete = function() {
			throw new Error("set is read-only");
		};
		Object.freeze(obj);
		Object.getOwnPropertyNames(obj).forEach((name) => {
			const prop = obj[name];
			const type = typeof prop;
			if ((type === "object" || type === "function") && !Object.isFrozen(prop)) deepFreeze(prop);
		});
		return obj;
	}
	/** @typedef {import('highlight.js').CallbackResponse} CallbackResponse */
	/** @typedef {import('highlight.js').CompiledMode} CompiledMode */
	/** @implements CallbackResponse */
	var Response = class {
		/**
		* @param {CompiledMode} mode
		*/
		constructor(mode) {
			if (mode.data === void 0) mode.data = {};
			this.data = mode.data;
			this.isMatchIgnored = false;
		}
		ignoreMatch() {
			this.isMatchIgnored = true;
		}
	};
	/**
	* @param {string} value
	* @returns {string}
	*/
	function escapeHTML(value) {
		return value.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#x27;");
	}
	/**
	* performs a shallow merge of multiple objects into one
	*
	* @template T
	* @param {T} original
	* @param {Record<string,any>[]} objects
	* @returns {T} a single new object
	*/
	function inherit$1(original, ...objects) {
		/** @type Record<string,any> */
		const result = Object.create(null);
		for (const key in original) result[key] = original[key];
		objects.forEach(function(obj) {
			for (const key in obj) result[key] = obj[key];
		});
		return result;
	}
	/**
	* @typedef {object} Renderer
	* @property {(text: string) => void} addText
	* @property {(node: Node) => void} openNode
	* @property {(node: Node) => void} closeNode
	* @property {() => string} value
	*/
	/** @typedef {{scope?: string, language?: string, sublanguage?: boolean}} Node */
	/** @typedef {{walk: (r: Renderer) => void}} Tree */
	/** */
	var SPAN_CLOSE = "</span>";
	/**
	* Determines if a node needs to be wrapped in <span>
	*
	* @param {Node} node */
	var emitsWrappingTags = (node) => {
		return !!node.scope;
	};
	/**
	*
	* @param {string} name
	* @param {{prefix:string}} options
	*/
	var scopeToCSSClass = (name, { prefix }) => {
		if (name.startsWith("language:")) return name.replace("language:", "language-");
		if (name.includes(".")) {
			const pieces = name.split(".");
			return [`${prefix}${pieces.shift()}`, ...pieces.map((x, i) => `${x}${"_".repeat(i + 1)}`)].join(" ");
		}
		return `${prefix}${name}`;
	};
	/** @type {Renderer} */
	var HTMLRenderer = class {
		/**
		* Creates a new HTMLRenderer
		*
		* @param {Tree} parseTree - the parse tree (must support `walk` API)
		* @param {{classPrefix: string}} options
		*/
		constructor(parseTree, options) {
			this.buffer = "";
			this.classPrefix = options.classPrefix;
			parseTree.walk(this);
		}
		/**
		* Adds texts to the output stream
		*
		* @param {string} text */
		addText(text) {
			this.buffer += escapeHTML(text);
		}
		/**
		* Adds a node open to the output stream (if needed)
		*
		* @param {Node} node */
		openNode(node) {
			if (!emitsWrappingTags(node)) return;
			const className = scopeToCSSClass(node.scope, { prefix: this.classPrefix });
			this.span(className);
		}
		/**
		* Adds a node close to the output stream (if needed)
		*
		* @param {Node} node */
		closeNode(node) {
			if (!emitsWrappingTags(node)) return;
			this.buffer += SPAN_CLOSE;
		}
		/**
		* returns the accumulated buffer
		*/
		value() {
			return this.buffer;
		}
		/**
		* Builds a span element
		*
		* @param {string} className */
		span(className) {
			this.buffer += `<span class="${className}">`;
		}
	};
	/** @typedef {{scope?: string, language?: string, children: Node[]} | string} Node */
	/** @typedef {{scope?: string, language?: string, children: Node[]} } DataNode */
	/** @typedef {import('highlight.js').Emitter} Emitter */
	/**  */
	/** @returns {DataNode} */
	var newNode = (opts = {}) => {
		/** @type DataNode */
		const result = { children: [] };
		Object.assign(result, opts);
		return result;
	};
	var TokenTree = class TokenTree {
		constructor() {
			/** @type DataNode */
			this.rootNode = newNode();
			this.stack = [this.rootNode];
		}
		get top() {
			return this.stack[this.stack.length - 1];
		}
		get root() {
			return this.rootNode;
		}
		/** @param {Node} node */
		add(node) {
			this.top.children.push(node);
		}
		/** @param {string} scope */
		openNode(scope) {
			/** @type Node */
			const node = newNode({ scope });
			this.add(node);
			this.stack.push(node);
		}
		closeNode() {
			if (this.stack.length > 1) return this.stack.pop();
		}
		closeAllNodes() {
			while (this.closeNode());
		}
		toJSON() {
			return JSON.stringify(this.rootNode, null, 4);
		}
		/**
		* @typedef { import("./html_renderer").Renderer } Renderer
		* @param {Renderer} builder
		*/
		walk(builder) {
			return this.constructor._walk(builder, this.rootNode);
		}
		/**
		* @param {Renderer} builder
		* @param {Node} node
		*/
		static _walk(builder, node) {
			if (typeof node === "string") builder.addText(node);
			else if (node.children) {
				builder.openNode(node);
				node.children.forEach((child) => this._walk(builder, child));
				builder.closeNode(node);
			}
			return builder;
		}
		/**
		* @param {Node} node
		*/
		static _collapse(node) {
			if (typeof node === "string") return;
			if (!node.children) return;
			if (node.children.every((el) => typeof el === "string")) node.children = [node.children.join("")];
			else node.children.forEach((child) => {
				TokenTree._collapse(child);
			});
		}
	};
	/**
	Currently this is all private API, but this is the minimal API necessary
	that an Emitter must implement to fully support the parser.
	
	Minimal interface:
	
	- addText(text)
	- __addSublanguage(emitter, subLanguageName)
	- startScope(scope)
	- endScope()
	- finalize()
	- toHTML()
	
	*/
	/**
	* @implements {Emitter}
	*/
	var TokenTreeEmitter = class extends TokenTree {
		/**
		* @param {*} options
		*/
		constructor(options) {
			super();
			this.options = options;
		}
		/**
		* @param {string} text
		*/
		addText(text) {
			if (text === "") return;
			this.add(text);
		}
		/** @param {string} scope */
		startScope(scope) {
			this.openNode(scope);
		}
		endScope() {
			this.closeNode();
		}
		/**
		* @param {Emitter & {root: DataNode}} emitter
		* @param {string} name
		*/
		__addSublanguage(emitter, name) {
			/** @type DataNode */
			const node = emitter.root;
			if (name) node.scope = `language:${name}`;
			this.add(node);
		}
		toHTML() {
			return new HTMLRenderer(this, this.options).value();
		}
		finalize() {
			this.closeAllNodes();
			return true;
		}
	};
	/**
	* @param {string} value
	* @returns {RegExp}
	* */
	/**
	* @param {RegExp | string } re
	* @returns {string}
	*/
	function source(re) {
		if (!re) return null;
		if (typeof re === "string") return re;
		return re.source;
	}
	/**
	* @param {RegExp | string } re
	* @returns {string}
	*/
	function lookahead(re) {
		return concat("(?=", re, ")");
	}
	/**
	* @param {RegExp | string } re
	* @returns {string}
	*/
	function anyNumberOfTimes(re) {
		return concat("(?:", re, ")*");
	}
	/**
	* @param {RegExp | string } re
	* @returns {string}
	*/
	function optional(re) {
		return concat("(?:", re, ")?");
	}
	/**
	* @param {...(RegExp | string) } args
	* @returns {string}
	*/
	function concat(...args) {
		return args.map((x) => source(x)).join("");
	}
	/**
	* @param { Array<string | RegExp | Object> } args
	* @returns {object}
	*/
	function stripOptionsFromArgs(args) {
		const opts = args[args.length - 1];
		if (typeof opts === "object" && opts.constructor === Object) {
			args.splice(args.length - 1, 1);
			return opts;
		} else return {};
	}
	/** @typedef { {capture?: boolean} } RegexEitherOptions */
	/**
	* Any of the passed expresssions may match
	*
	* Creates a huge this | this | that | that match
	* @param {(RegExp | string)[] | [...(RegExp | string)[], RegexEitherOptions]} args
	* @returns {string}
	*/
	function either(...args) {
		return "(" + (stripOptionsFromArgs(args).capture ? "" : "?:") + args.map((x) => source(x)).join("|") + ")";
	}
	/**
	* @param {RegExp | string} re
	* @returns {number}
	*/
	function countMatchGroups(re) {
		return new RegExp(re.toString() + "|").exec("").length - 1;
	}
	/**
	* Does lexeme start with a regular expression match at the beginning
	* @param {RegExp} re
	* @param {string} lexeme
	*/
	function startsWith(re, lexeme) {
		const match = re && re.exec(lexeme);
		return match && match.index === 0;
	}
	var BACKREF_RE = /\[(?:[^\\\]]|\\.)*\]|\(\??|\\([1-9][0-9]*)|\\./;
	/**
	* @param {(string | RegExp)[]} regexps
	* @param {{joinWith: string}} opts
	* @returns {string}
	*/
	function _rewriteBackreferences(regexps, { joinWith }) {
		let numCaptures = 0;
		return regexps.map((regex) => {
			numCaptures += 1;
			const offset = numCaptures;
			let re = source(regex);
			let out = "";
			while (re.length > 0) {
				const match = BACKREF_RE.exec(re);
				if (!match) {
					out += re;
					break;
				}
				out += re.substring(0, match.index);
				re = re.substring(match.index + match[0].length);
				if (match[0][0] === "\\" && match[1]) out += "\\" + String(Number(match[1]) + offset);
				else {
					out += match[0];
					if (match[0] === "(") numCaptures++;
				}
			}
			return out;
		}).map((re) => `(${re})`).join(joinWith);
	}
	/** @typedef {import('highlight.js').Mode} Mode */
	/** @typedef {import('highlight.js').ModeCallback} ModeCallback */
	var MATCH_NOTHING_RE = /\b\B/;
	var IDENT_RE = "[a-zA-Z]\\w*";
	var UNDERSCORE_IDENT_RE = "[a-zA-Z_]\\w*";
	var NUMBER_RE = "\\b\\d+(\\.\\d+)?";
	var C_NUMBER_RE = "(-?)(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)";
	var BINARY_NUMBER_RE = "\\b(0b[01]+)";
	var RE_STARTERS_RE = "!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~";
	/**
	* @param { Partial<Mode> & {binary?: string | RegExp} } opts
	*/
	var SHEBANG = (opts = {}) => {
		const beginShebang = /^#![ ]*\//;
		if (opts.binary) opts.begin = concat(beginShebang, /.*\b/, opts.binary, /\b.*/);
		return inherit$1({
			scope: "meta",
			begin: beginShebang,
			end: /$/,
			relevance: 0,
			/** @type {ModeCallback} */
			"on:begin": (m, resp) => {
				if (m.index !== 0) resp.ignoreMatch();
			}
		}, opts);
	};
	var BACKSLASH_ESCAPE = {
		begin: "\\\\[\\s\\S]",
		relevance: 0
	};
	var APOS_STRING_MODE = {
		scope: "string",
		begin: "'",
		end: "'",
		illegal: "\\n",
		contains: [BACKSLASH_ESCAPE]
	};
	var QUOTE_STRING_MODE = {
		scope: "string",
		begin: "\"",
		end: "\"",
		illegal: "\\n",
		contains: [BACKSLASH_ESCAPE]
	};
	var PHRASAL_WORDS_MODE = { begin: /\b(a|an|the|are|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such|will|you|your|they|like|more)\b/ };
	/**
	* Creates a comment mode
	*
	* @param {string | RegExp} begin
	* @param {string | RegExp} end
	* @param {Mode | {}} [modeOptions]
	* @returns {Partial<Mode>}
	*/
	var COMMENT = function(begin, end, modeOptions = {}) {
		const mode = inherit$1({
			scope: "comment",
			begin,
			end,
			contains: []
		}, modeOptions);
		mode.contains.push({
			scope: "doctag",
			begin: "[ ]*(?=(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):)",
			end: /(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):/,
			excludeBegin: true,
			relevance: 0
		});
		const ENGLISH_WORD = either("I", "a", "is", "so", "us", "to", "at", "if", "in", "it", "on", /[A-Za-z]+['](d|ve|re|ll|t|s|n)/, /[A-Za-z]+[-][a-z]+/, /[A-Za-z][a-z]{2,}/);
		mode.contains.push({ begin: concat(/[ ]+/, "(", ENGLISH_WORD, /[.]?[:]?([.][ ]|[ ])/, "){3}") });
		return mode;
	};
	var C_LINE_COMMENT_MODE = COMMENT("//", "$");
	var C_BLOCK_COMMENT_MODE = COMMENT("/\\*", "\\*/");
	var HASH_COMMENT_MODE = COMMENT("#", "$");
	var NUMBER_MODE = {
		scope: "number",
		begin: NUMBER_RE,
		relevance: 0
	};
	var C_NUMBER_MODE = {
		scope: "number",
		begin: C_NUMBER_RE,
		relevance: 0
	};
	var BINARY_NUMBER_MODE = {
		scope: "number",
		begin: BINARY_NUMBER_RE,
		relevance: 0
	};
	var REGEXP_MODE = {
		scope: "regexp",
		begin: /\/(?=[^/\n]*\/)/,
		end: /\/[gimuy]*/,
		contains: [BACKSLASH_ESCAPE, {
			begin: /\[/,
			end: /\]/,
			relevance: 0,
			contains: [BACKSLASH_ESCAPE]
		}]
	};
	var TITLE_MODE = {
		scope: "title",
		begin: IDENT_RE,
		relevance: 0
	};
	var UNDERSCORE_TITLE_MODE = {
		scope: "title",
		begin: UNDERSCORE_IDENT_RE,
		relevance: 0
	};
	var METHOD_GUARD = {
		begin: "\\.\\s*[a-zA-Z_]\\w*",
		relevance: 0
	};
	/**
	* Adds end same as begin mechanics to a mode
	*
	* Your mode must include at least a single () match group as that first match
	* group is what is used for comparison
	* @param {Partial<Mode>} mode
	*/
	var END_SAME_AS_BEGIN = function(mode) {
		return Object.assign(mode, {
			/** @type {ModeCallback} */
			"on:begin": (m, resp) => {
				resp.data._beginMatch = m[1];
			},
			/** @type {ModeCallback} */
			"on:end": (m, resp) => {
				if (resp.data._beginMatch !== m[1]) resp.ignoreMatch();
			}
		});
	};
	var MODES = /*#__PURE__*/ Object.freeze({
		__proto__: null,
		APOS_STRING_MODE,
		BACKSLASH_ESCAPE,
		BINARY_NUMBER_MODE,
		BINARY_NUMBER_RE,
		COMMENT,
		C_BLOCK_COMMENT_MODE,
		C_LINE_COMMENT_MODE,
		C_NUMBER_MODE,
		C_NUMBER_RE,
		END_SAME_AS_BEGIN,
		HASH_COMMENT_MODE,
		IDENT_RE,
		MATCH_NOTHING_RE,
		METHOD_GUARD,
		NUMBER_MODE,
		NUMBER_RE,
		PHRASAL_WORDS_MODE,
		QUOTE_STRING_MODE,
		REGEXP_MODE,
		RE_STARTERS_RE,
		SHEBANG,
		TITLE_MODE,
		UNDERSCORE_IDENT_RE,
		UNDERSCORE_TITLE_MODE
	});
	/**
	@typedef {import('highlight.js').CallbackResponse} CallbackResponse
	@typedef {import('highlight.js').CompilerExt} CompilerExt
	*/
	/**
	* Skip a match if it has a preceding dot
	*
	* This is used for `beginKeywords` to prevent matching expressions such as
	* `bob.keyword.do()`. The mode compiler automatically wires this up as a
	* special _internal_ 'on:begin' callback for modes with `beginKeywords`
	* @param {RegExpMatchArray} match
	* @param {CallbackResponse} response
	*/
	function skipIfHasPrecedingDot(match, response) {
		if (match.input[match.index - 1] === ".") response.ignoreMatch();
	}
	/**
	*
	* @type {CompilerExt}
	*/
	function scopeClassName(mode, _parent) {
		if (mode.className !== void 0) {
			mode.scope = mode.className;
			delete mode.className;
		}
	}
	/**
	* `beginKeywords` syntactic sugar
	* @type {CompilerExt}
	*/
	function beginKeywords(mode, parent) {
		if (!parent) return;
		if (!mode.beginKeywords) return;
		mode.begin = "\\b(" + mode.beginKeywords.split(" ").join("|") + ")(?!\\.)(?=\\b|\\s)";
		mode.__beforeBegin = skipIfHasPrecedingDot;
		mode.keywords = mode.keywords || mode.beginKeywords;
		delete mode.beginKeywords;
		if (mode.relevance === void 0) mode.relevance = 0;
	}
	/**
	* Allow `illegal` to contain an array of illegal values
	* @type {CompilerExt}
	*/
	function compileIllegal(mode, _parent) {
		if (!Array.isArray(mode.illegal)) return;
		mode.illegal = either(...mode.illegal);
	}
	/**
	* `match` to match a single expression for readability
	* @type {CompilerExt}
	*/
	function compileMatch(mode, _parent) {
		if (!mode.match) return;
		if (mode.begin || mode.end) throw new Error("begin & end are not supported with match");
		mode.begin = mode.match;
		delete mode.match;
	}
	/**
	* provides the default 1 relevance to all modes
	* @type {CompilerExt}
	*/
	function compileRelevance(mode, _parent) {
		if (mode.relevance === void 0) mode.relevance = 1;
	}
	var beforeMatchExt = (mode, parent) => {
		if (!mode.beforeMatch) return;
		if (mode.starts) throw new Error("beforeMatch cannot be used with starts");
		const originalMode = Object.assign({}, mode);
		Object.keys(mode).forEach((key) => {
			delete mode[key];
		});
		mode.keywords = originalMode.keywords;
		mode.begin = concat(originalMode.beforeMatch, lookahead(originalMode.begin));
		mode.starts = {
			relevance: 0,
			contains: [Object.assign(originalMode, { endsParent: true })]
		};
		mode.relevance = 0;
		delete originalMode.beforeMatch;
	};
	var COMMON_KEYWORDS = [
		"of",
		"and",
		"for",
		"in",
		"not",
		"or",
		"if",
		"then",
		"parent",
		"list",
		"value"
	];
	var DEFAULT_KEYWORD_SCOPE = "keyword";
	/**
	* Given raw keywords from a language definition, compile them.
	*
	* @param {string | Record<string,string|string[]> | Array<string>} rawKeywords
	* @param {boolean} caseInsensitive
	*/
	function compileKeywords(rawKeywords, caseInsensitive, scopeName = DEFAULT_KEYWORD_SCOPE) {
		/** @type {import("highlight.js/private").KeywordDict} */
		const compiledKeywords = Object.create(null);
		if (typeof rawKeywords === "string") compileList(scopeName, rawKeywords.split(" "));
		else if (Array.isArray(rawKeywords)) compileList(scopeName, rawKeywords);
		else Object.keys(rawKeywords).forEach(function(scopeName) {
			Object.assign(compiledKeywords, compileKeywords(rawKeywords[scopeName], caseInsensitive, scopeName));
		});
		return compiledKeywords;
		/**
		* Compiles an individual list of keywords
		*
		* Ex: "for if when while|5"
		*
		* @param {string} scopeName
		* @param {Array<string>} keywordList
		*/
		function compileList(scopeName, keywordList) {
			if (caseInsensitive) keywordList = keywordList.map((x) => x.toLowerCase());
			keywordList.forEach(function(keyword) {
				const pair = keyword.split("|");
				compiledKeywords[pair[0]] = [scopeName, scoreForKeyword(pair[0], pair[1])];
			});
		}
	}
	/**
	* Returns the proper score for a given keyword
	*
	* Also takes into account comment keywords, which will be scored 0 UNLESS
	* another score has been manually assigned.
	* @param {string} keyword
	* @param {string} [providedScore]
	*/
	function scoreForKeyword(keyword, providedScore) {
		if (providedScore) return Number(providedScore);
		return commonKeyword(keyword) ? 0 : 1;
	}
	/**
	* Determines if a given keyword is common or not
	*
	* @param {string} keyword */
	function commonKeyword(keyword) {
		return COMMON_KEYWORDS.includes(keyword.toLowerCase());
	}
	/**
	* @type {Record<string, boolean>}
	*/
	var seenDeprecations = {};
	/**
	* @param {string} message
	*/
	var error = (message) => {
		console.error(message);
	};
	/**
	* @param {string} message
	* @param {any} args
	*/
	var warn = (message, ...args) => {
		console.log(`WARN: ${message}`, ...args);
	};
	/**
	* @param {string} version
	* @param {string} message
	*/
	var deprecated = (version, message) => {
		if (seenDeprecations[`${version}/${message}`]) return;
		console.log(`Deprecated as of ${version}. ${message}`);
		seenDeprecations[`${version}/${message}`] = true;
	};
	/**
	@typedef {import('highlight.js').CompiledMode} CompiledMode
	*/
	var MultiClassError = /* @__PURE__ */ new Error();
	/**
	* Renumbers labeled scope names to account for additional inner match
	* groups that otherwise would break everything.
	*
	* Lets say we 3 match scopes:
	*
	*   { 1 => ..., 2 => ..., 3 => ... }
	*
	* So what we need is a clean match like this:
	*
	*   (a)(b)(c) => [ "a", "b", "c" ]
	*
	* But this falls apart with inner match groups:
	*
	* (a)(((b)))(c) => ["a", "b", "b", "b", "c" ]
	*
	* Our scopes are now "out of alignment" and we're repeating `b` 3 times.
	* What needs to happen is the numbers are remapped:
	*
	*   { 1 => ..., 2 => ..., 5 => ... }
	*
	* We also need to know that the ONLY groups that should be output
	* are 1, 2, and 5.  This function handles this behavior.
	*
	* @param {CompiledMode} mode
	* @param {Array<RegExp | string>} regexes
	* @param {{key: "beginScope"|"endScope"}} opts
	*/
	function remapScopeNames(mode, regexes, { key }) {
		let offset = 0;
		const scopeNames = mode[key];
		/** @type Record<number,boolean> */
		const emit = {};
		/** @type Record<number,string> */
		const positions = {};
		for (let i = 1; i <= regexes.length; i++) {
			positions[i + offset] = scopeNames[i];
			emit[i + offset] = true;
			offset += countMatchGroups(regexes[i - 1]);
		}
		mode[key] = positions;
		mode[key]._emit = emit;
		mode[key]._multi = true;
	}
	/**
	* @param {CompiledMode} mode
	*/
	function beginMultiClass(mode) {
		if (!Array.isArray(mode.begin)) return;
		if (mode.skip || mode.excludeBegin || mode.returnBegin) {
			error("skip, excludeBegin, returnBegin not compatible with beginScope: {}");
			throw MultiClassError;
		}
		if (typeof mode.beginScope !== "object" || mode.beginScope === null) {
			error("beginScope must be object");
			throw MultiClassError;
		}
		remapScopeNames(mode, mode.begin, { key: "beginScope" });
		mode.begin = _rewriteBackreferences(mode.begin, { joinWith: "" });
	}
	/**
	* @param {CompiledMode} mode
	*/
	function endMultiClass(mode) {
		if (!Array.isArray(mode.end)) return;
		if (mode.skip || mode.excludeEnd || mode.returnEnd) {
			error("skip, excludeEnd, returnEnd not compatible with endScope: {}");
			throw MultiClassError;
		}
		if (typeof mode.endScope !== "object" || mode.endScope === null) {
			error("endScope must be object");
			throw MultiClassError;
		}
		remapScopeNames(mode, mode.end, { key: "endScope" });
		mode.end = _rewriteBackreferences(mode.end, { joinWith: "" });
	}
	/**
	* this exists only to allow `scope: {}` to be used beside `match:`
	* Otherwise `beginScope` would necessary and that would look weird
	
	{
	match: [ /def/, /\w+/ ]
	scope: { 1: "keyword" , 2: "title" }
	}
	
	* @param {CompiledMode} mode
	*/
	function scopeSugar(mode) {
		if (mode.scope && typeof mode.scope === "object" && mode.scope !== null) {
			mode.beginScope = mode.scope;
			delete mode.scope;
		}
	}
	/**
	* @param {CompiledMode} mode
	*/
	function MultiClass(mode) {
		scopeSugar(mode);
		if (typeof mode.beginScope === "string") mode.beginScope = { _wrap: mode.beginScope };
		if (typeof mode.endScope === "string") mode.endScope = { _wrap: mode.endScope };
		beginMultiClass(mode);
		endMultiClass(mode);
	}
	/**
	@typedef {import('highlight.js').Mode} Mode
	@typedef {import('highlight.js').CompiledMode} CompiledMode
	@typedef {import('highlight.js').Language} Language
	@typedef {import('highlight.js').HLJSPlugin} HLJSPlugin
	@typedef {import('highlight.js').CompiledLanguage} CompiledLanguage
	*/
	/**
	* Compiles a language definition result
	*
	* Given the raw result of a language definition (Language), compiles this so
	* that it is ready for highlighting code.
	* @param {Language} language
	* @returns {CompiledLanguage}
	*/
	function compileLanguage(language) {
		/**
		* Builds a regex with the case sensitivity of the current language
		*
		* @param {RegExp | string} value
		* @param {boolean} [global]
		*/
		function langRe(value, global) {
			return new RegExp(source(value), "m" + (language.case_insensitive ? "i" : "") + (language.unicodeRegex ? "u" : "") + (global ? "g" : ""));
		}
		/**
		Stores multiple regular expressions and allows you to quickly search for
		them all in a string simultaneously - returning the first match.  It does
		this by creating a huge (a|b|c) regex - each individual item wrapped with ()
		and joined by `|` - using match groups to track position.  When a match is
		found checking which position in the array has content allows us to figure
		out which of the original regexes / match groups triggered the match.
		
		The match object itself (the result of `Regex.exec`) is returned but also
		enhanced by merging in any meta-data that was registered with the regex.
		This is how we keep track of which mode matched, and what type of rule
		(`illegal`, `begin`, end, etc).
		*/
		class MultiRegex {
			constructor() {
				this.matchIndexes = {};
				this.regexes = [];
				this.matchAt = 1;
				this.position = 0;
			}
			addRule(re, opts) {
				opts.position = this.position++;
				this.matchIndexes[this.matchAt] = opts;
				this.regexes.push([opts, re]);
				this.matchAt += countMatchGroups(re) + 1;
			}
			compile() {
				if (this.regexes.length === 0) this.exec = () => null;
				const terminators = this.regexes.map((el) => el[1]);
				this.matcherRe = langRe(_rewriteBackreferences(terminators, { joinWith: "|" }), true);
				this.lastIndex = 0;
			}
			/** @param {string} s */
			exec(s) {
				this.matcherRe.lastIndex = this.lastIndex;
				const match = this.matcherRe.exec(s);
				if (!match) return null;
				const i = match.findIndex((el, i) => i > 0 && el !== void 0);
				const matchData = this.matchIndexes[i];
				match.splice(0, i);
				return Object.assign(match, matchData);
			}
		}
		class ResumableMultiRegex {
			constructor() {
				this.rules = [];
				this.multiRegexes = [];
				this.count = 0;
				this.lastIndex = 0;
				this.regexIndex = 0;
			}
			getMatcher(index) {
				if (this.multiRegexes[index]) return this.multiRegexes[index];
				const matcher = new MultiRegex();
				this.rules.slice(index).forEach(([re, opts]) => matcher.addRule(re, opts));
				matcher.compile();
				this.multiRegexes[index] = matcher;
				return matcher;
			}
			resumingScanAtSamePosition() {
				return this.regexIndex !== 0;
			}
			considerAll() {
				this.regexIndex = 0;
			}
			addRule(re, opts) {
				this.rules.push([re, opts]);
				if (opts.type === "begin") this.count++;
			}
			/** @param {string} s */
			exec(s) {
				const m = this.getMatcher(this.regexIndex);
				m.lastIndex = this.lastIndex;
				let result = m.exec(s);
				if (this.resumingScanAtSamePosition()) if (result && result.index === this.lastIndex);
				else {
					const m2 = this.getMatcher(0);
					m2.lastIndex = this.lastIndex + 1;
					result = m2.exec(s);
				}
				if (result) {
					this.regexIndex += result.position + 1;
					if (this.regexIndex === this.count) this.considerAll();
				}
				return result;
			}
		}
		/**
		* Given a mode, builds a huge ResumableMultiRegex that can be used to walk
		* the content and find matches.
		*
		* @param {CompiledMode} mode
		* @returns {ResumableMultiRegex}
		*/
		function buildModeRegex(mode) {
			const mm = new ResumableMultiRegex();
			mode.contains.forEach((term) => mm.addRule(term.begin, {
				rule: term,
				type: "begin"
			}));
			if (mode.terminatorEnd) mm.addRule(mode.terminatorEnd, { type: "end" });
			if (mode.illegal) mm.addRule(mode.illegal, { type: "illegal" });
			return mm;
		}
		/** skip vs abort vs ignore
		*
		* @skip   - The mode is still entered and exited normally (and contains rules apply),
		*           but all content is held and added to the parent buffer rather than being
		*           output when the mode ends.  Mostly used with `sublanguage` to build up
		*           a single large buffer than can be parsed by sublanguage.
		*
		*             - The mode begin ands ends normally.
		*             - Content matched is added to the parent mode buffer.
		*             - The parser cursor is moved forward normally.
		*
		* @abort  - A hack placeholder until we have ignore.  Aborts the mode (as if it
		*           never matched) but DOES NOT continue to match subsequent `contains`
		*           modes.  Abort is bad/suboptimal because it can result in modes
		*           farther down not getting applied because an earlier rule eats the
		*           content but then aborts.
		*
		*             - The mode does not begin.
		*             - Content matched by `begin` is added to the mode buffer.
		*             - The parser cursor is moved forward accordingly.
		*
		* @ignore - Ignores the mode (as if it never matched) and continues to match any
		*           subsequent `contains` modes.  Ignore isn't technically possible with
		*           the current parser implementation.
		*
		*             - The mode does not begin.
		*             - Content matched by `begin` is ignored.
		*             - The parser cursor is not moved forward.
		*/
		/**
		* Compiles an individual mode
		*
		* This can raise an error if the mode contains certain detectable known logic
		* issues.
		* @param {Mode} mode
		* @param {CompiledMode | null} [parent]
		* @returns {CompiledMode | never}
		*/
		function compileMode(mode, parent) {
			const cmode = mode;
			if (mode.isCompiled) return cmode;
			[
				scopeClassName,
				compileMatch,
				MultiClass,
				beforeMatchExt
			].forEach((ext) => ext(mode, parent));
			language.compilerExtensions.forEach((ext) => ext(mode, parent));
			mode.__beforeBegin = null;
			[
				beginKeywords,
				compileIllegal,
				compileRelevance
			].forEach((ext) => ext(mode, parent));
			mode.isCompiled = true;
			let keywordPattern = null;
			if (typeof mode.keywords === "object" && mode.keywords.$pattern) {
				mode.keywords = Object.assign({}, mode.keywords);
				keywordPattern = mode.keywords.$pattern;
				delete mode.keywords.$pattern;
			}
			keywordPattern = keywordPattern || /\w+/;
			if (mode.keywords) mode.keywords = compileKeywords(mode.keywords, language.case_insensitive);
			cmode.keywordPatternRe = langRe(keywordPattern, true);
			if (parent) {
				if (!mode.begin) mode.begin = /\B|\b/;
				cmode.beginRe = langRe(cmode.begin);
				if (!mode.end && !mode.endsWithParent) mode.end = /\B|\b/;
				if (mode.end) cmode.endRe = langRe(cmode.end);
				cmode.terminatorEnd = source(cmode.end) || "";
				if (mode.endsWithParent && parent.terminatorEnd) cmode.terminatorEnd += (mode.end ? "|" : "") + parent.terminatorEnd;
			}
			if (mode.illegal) cmode.illegalRe = langRe(mode.illegal);
			if (!mode.contains) mode.contains = [];
			mode.contains = [].concat(...mode.contains.map(function(c) {
				return expandOrCloneMode(c === "self" ? mode : c);
			}));
			mode.contains.forEach(function(c) {
				compileMode(c, cmode);
			});
			if (mode.starts) compileMode(mode.starts, parent);
			cmode.matcher = buildModeRegex(cmode);
			return cmode;
		}
		if (!language.compilerExtensions) language.compilerExtensions = [];
		if (language.contains && language.contains.includes("self")) throw new Error("ERR: contains `self` is not supported at the top-level of a language.  See documentation.");
		language.classNameAliases = inherit$1(language.classNameAliases || {});
		return compileMode(language);
	}
	/**
	* Determines if a mode has a dependency on it's parent or not
	*
	* If a mode does have a parent dependency then often we need to clone it if
	* it's used in multiple places so that each copy points to the correct parent,
	* where-as modes without a parent can often safely be re-used at the bottom of
	* a mode chain.
	*
	* @param {Mode | null} mode
	* @returns {boolean} - is there a dependency on the parent?
	* */
	function dependencyOnParent(mode) {
		if (!mode) return false;
		return mode.endsWithParent || dependencyOnParent(mode.starts);
	}
	/**
	* Expands a mode or clones it if necessary
	*
	* This is necessary for modes with parental dependenceis (see notes on
	* `dependencyOnParent`) and for nodes that have `variants` - which must then be
	* exploded into their own individual modes at compile time.
	*
	* @param {Mode} mode
	* @returns {Mode | Mode[]}
	* */
	function expandOrCloneMode(mode) {
		if (mode.variants && !mode.cachedVariants) mode.cachedVariants = mode.variants.map(function(variant) {
			return inherit$1(mode, { variants: null }, variant);
		});
		if (mode.cachedVariants) return mode.cachedVariants;
		if (dependencyOnParent(mode)) return inherit$1(mode, { starts: mode.starts ? inherit$1(mode.starts) : null });
		if (Object.isFrozen(mode)) return inherit$1(mode);
		return mode;
	}
	var version = "11.11.1";
	var HTMLInjectionError = class extends Error {
		constructor(reason, html) {
			super(reason);
			this.name = "HTMLInjectionError";
			this.html = html;
		}
	};
	/**
	@typedef {import('highlight.js').Mode} Mode
	@typedef {import('highlight.js').CompiledMode} CompiledMode
	@typedef {import('highlight.js').CompiledScope} CompiledScope
	@typedef {import('highlight.js').Language} Language
	@typedef {import('highlight.js').HLJSApi} HLJSApi
	@typedef {import('highlight.js').HLJSPlugin} HLJSPlugin
	@typedef {import('highlight.js').PluginEvent} PluginEvent
	@typedef {import('highlight.js').HLJSOptions} HLJSOptions
	@typedef {import('highlight.js').LanguageFn} LanguageFn
	@typedef {import('highlight.js').HighlightedHTMLElement} HighlightedHTMLElement
	@typedef {import('highlight.js').BeforeHighlightContext} BeforeHighlightContext
	@typedef {import('highlight.js/private').MatchType} MatchType
	@typedef {import('highlight.js/private').KeywordData} KeywordData
	@typedef {import('highlight.js/private').EnhancedMatch} EnhancedMatch
	@typedef {import('highlight.js/private').AnnotatedError} AnnotatedError
	@typedef {import('highlight.js').AutoHighlightResult} AutoHighlightResult
	@typedef {import('highlight.js').HighlightOptions} HighlightOptions
	@typedef {import('highlight.js').HighlightResult} HighlightResult
	*/
	var escape = escapeHTML;
	var inherit = inherit$1;
	var NO_MATCH = Symbol("nomatch");
	var MAX_KEYWORD_HITS = 7;
	/**
	* @param {any} hljs - object that is extended (legacy)
	* @returns {HLJSApi}
	*/
	var HLJS = function(hljs) {
		/** @type {Record<string, Language>} */
		const languages = Object.create(null);
		/** @type {Record<string, string>} */
		const aliases = Object.create(null);
		/** @type {HLJSPlugin[]} */
		const plugins = [];
		let SAFE_MODE = true;
		const LANGUAGE_NOT_FOUND = "Could not find the language '{}', did you forget to load/include a language module?";
		/** @type {Language} */
		const PLAINTEXT_LANGUAGE = {
			disableAutodetect: true,
			name: "Plain text",
			contains: []
		};
		/** @type HLJSOptions */
		let options = {
			ignoreUnescapedHTML: false,
			throwUnescapedHTML: false,
			noHighlightRe: /^(no-?highlight)$/i,
			languageDetectRe: /\blang(?:uage)?-([\w-]+)\b/i,
			classPrefix: "hljs-",
			cssSelector: "pre code",
			languages: null,
			__emitter: TokenTreeEmitter
		};
		/**
		* Tests a language name to see if highlighting should be skipped
		* @param {string} languageName
		*/
		function shouldNotHighlight(languageName) {
			return options.noHighlightRe.test(languageName);
		}
		/**
		* @param {HighlightedHTMLElement} block - the HTML element to determine language for
		*/
		function blockLanguage(block) {
			let classes = block.className + " ";
			classes += block.parentNode ? block.parentNode.className : "";
			const match = options.languageDetectRe.exec(classes);
			if (match) {
				const language = getLanguage(match[1]);
				if (!language) {
					warn(LANGUAGE_NOT_FOUND.replace("{}", match[1]));
					warn("Falling back to no-highlight mode for this block.", block);
				}
				return language ? match[1] : "no-highlight";
			}
			return classes.split(/\s+/).find((_class) => shouldNotHighlight(_class) || getLanguage(_class));
		}
		/**
		* Core highlighting function.
		*
		* OLD API
		* highlight(lang, code, ignoreIllegals, continuation)
		*
		* NEW API
		* highlight(code, {lang, ignoreIllegals})
		*
		* @param {string} codeOrLanguageName - the language to use for highlighting
		* @param {string | HighlightOptions} optionsOrCode - the code to highlight
		* @param {boolean} [ignoreIllegals] - whether to ignore illegal matches, default is to bail
		*
		* @returns {HighlightResult} Result - an object that represents the result
		* @property {string} language - the language name
		* @property {number} relevance - the relevance score
		* @property {string} value - the highlighted HTML code
		* @property {string} code - the original raw code
		* @property {CompiledMode} top - top of the current mode stack
		* @property {boolean} illegal - indicates whether any illegal matches were found
		*/
		function highlight(codeOrLanguageName, optionsOrCode, ignoreIllegals) {
			let code = "";
			let languageName = "";
			if (typeof optionsOrCode === "object") {
				code = codeOrLanguageName;
				ignoreIllegals = optionsOrCode.ignoreIllegals;
				languageName = optionsOrCode.language;
			} else {
				deprecated("10.7.0", "highlight(lang, code, ...args) has been deprecated.");
				deprecated("10.7.0", "Please use highlight(code, options) instead.\nhttps://github.com/highlightjs/highlight.js/issues/2277");
				languageName = codeOrLanguageName;
				code = optionsOrCode;
			}
			if (ignoreIllegals === void 0) ignoreIllegals = true;
			/** @type {BeforeHighlightContext} */
			const context = {
				code,
				language: languageName
			};
			fire("before:highlight", context);
			const result = context.result ? context.result : _highlight(context.language, context.code, ignoreIllegals);
			result.code = context.code;
			fire("after:highlight", result);
			return result;
		}
		/**
		* private highlight that's used internally and does not fire callbacks
		*
		* @param {string} languageName - the language to use for highlighting
		* @param {string} codeToHighlight - the code to highlight
		* @param {boolean?} [ignoreIllegals] - whether to ignore illegal matches, default is to bail
		* @param {CompiledMode?} [continuation] - current continuation mode, if any
		* @returns {HighlightResult} - result of the highlight operation
		*/
		function _highlight(languageName, codeToHighlight, ignoreIllegals, continuation) {
			const keywordHits = Object.create(null);
			/**
			* Return keyword data if a match is a keyword
			* @param {CompiledMode} mode - current mode
			* @param {string} matchText - the textual match
			* @returns {KeywordData | false}
			*/
			function keywordData(mode, matchText) {
				return mode.keywords[matchText];
			}
			function processKeywords() {
				if (!top.keywords) {
					emitter.addText(modeBuffer);
					return;
				}
				let lastIndex = 0;
				top.keywordPatternRe.lastIndex = 0;
				let match = top.keywordPatternRe.exec(modeBuffer);
				let buf = "";
				while (match) {
					buf += modeBuffer.substring(lastIndex, match.index);
					const word = language.case_insensitive ? match[0].toLowerCase() : match[0];
					const data = keywordData(top, word);
					if (data) {
						const [kind, keywordRelevance] = data;
						emitter.addText(buf);
						buf = "";
						keywordHits[word] = (keywordHits[word] || 0) + 1;
						if (keywordHits[word] <= MAX_KEYWORD_HITS) relevance += keywordRelevance;
						if (kind.startsWith("_")) buf += match[0];
						else {
							const cssClass = language.classNameAliases[kind] || kind;
							emitKeyword(match[0], cssClass);
						}
					} else buf += match[0];
					lastIndex = top.keywordPatternRe.lastIndex;
					match = top.keywordPatternRe.exec(modeBuffer);
				}
				buf += modeBuffer.substring(lastIndex);
				emitter.addText(buf);
			}
			function processSubLanguage() {
				if (modeBuffer === "") return;
				/** @type HighlightResult */
				let result = null;
				if (typeof top.subLanguage === "string") {
					if (!languages[top.subLanguage]) {
						emitter.addText(modeBuffer);
						return;
					}
					result = _highlight(top.subLanguage, modeBuffer, true, continuations[top.subLanguage]);
					continuations[top.subLanguage] = result._top;
				} else result = highlightAuto(modeBuffer, top.subLanguage.length ? top.subLanguage : null);
				if (top.relevance > 0) relevance += result.relevance;
				emitter.__addSublanguage(result._emitter, result.language);
			}
			function processBuffer() {
				if (top.subLanguage != null) processSubLanguage();
				else processKeywords();
				modeBuffer = "";
			}
			/**
			* @param {string} text
			* @param {string} scope
			*/
			function emitKeyword(keyword, scope) {
				if (keyword === "") return;
				emitter.startScope(scope);
				emitter.addText(keyword);
				emitter.endScope();
			}
			/**
			* @param {CompiledScope} scope
			* @param {RegExpMatchArray} match
			*/
			function emitMultiClass(scope, match) {
				let i = 1;
				const max = match.length - 1;
				while (i <= max) {
					if (!scope._emit[i]) {
						i++;
						continue;
					}
					const klass = language.classNameAliases[scope[i]] || scope[i];
					const text = match[i];
					if (klass) emitKeyword(text, klass);
					else {
						modeBuffer = text;
						processKeywords();
						modeBuffer = "";
					}
					i++;
				}
			}
			/**
			* @param {CompiledMode} mode - new mode to start
			* @param {RegExpMatchArray} match
			*/
			function startNewMode(mode, match) {
				if (mode.scope && typeof mode.scope === "string") emitter.openNode(language.classNameAliases[mode.scope] || mode.scope);
				if (mode.beginScope) {
					if (mode.beginScope._wrap) {
						emitKeyword(modeBuffer, language.classNameAliases[mode.beginScope._wrap] || mode.beginScope._wrap);
						modeBuffer = "";
					} else if (mode.beginScope._multi) {
						emitMultiClass(mode.beginScope, match);
						modeBuffer = "";
					}
				}
				top = Object.create(mode, { parent: { value: top } });
				return top;
			}
			/**
			* @param {CompiledMode } mode - the mode to potentially end
			* @param {RegExpMatchArray} match - the latest match
			* @param {string} matchPlusRemainder - match plus remainder of content
			* @returns {CompiledMode | void} - the next mode, or if void continue on in current mode
			*/
			function endOfMode(mode, match, matchPlusRemainder) {
				let matched = startsWith(mode.endRe, matchPlusRemainder);
				if (matched) {
					if (mode["on:end"]) {
						const resp = new Response(mode);
						mode["on:end"](match, resp);
						if (resp.isMatchIgnored) matched = false;
					}
					if (matched) {
						while (mode.endsParent && mode.parent) mode = mode.parent;
						return mode;
					}
				}
				if (mode.endsWithParent) return endOfMode(mode.parent, match, matchPlusRemainder);
			}
			/**
			* Handle matching but then ignoring a sequence of text
			*
			* @param {string} lexeme - string containing full match text
			*/
			function doIgnore(lexeme) {
				if (top.matcher.regexIndex === 0) {
					modeBuffer += lexeme[0];
					return 1;
				} else {
					resumeScanAtSamePosition = true;
					return 0;
				}
			}
			/**
			* Handle the start of a new potential mode match
			*
			* @param {EnhancedMatch} match - the current match
			* @returns {number} how far to advance the parse cursor
			*/
			function doBeginMatch(match) {
				const lexeme = match[0];
				const newMode = match.rule;
				const resp = new Response(newMode);
				const beforeCallbacks = [newMode.__beforeBegin, newMode["on:begin"]];
				for (const cb of beforeCallbacks) {
					if (!cb) continue;
					cb(match, resp);
					if (resp.isMatchIgnored) return doIgnore(lexeme);
				}
				if (newMode.skip) modeBuffer += lexeme;
				else {
					if (newMode.excludeBegin) modeBuffer += lexeme;
					processBuffer();
					if (!newMode.returnBegin && !newMode.excludeBegin) modeBuffer = lexeme;
				}
				startNewMode(newMode, match);
				return newMode.returnBegin ? 0 : lexeme.length;
			}
			/**
			* Handle the potential end of mode
			*
			* @param {RegExpMatchArray} match - the current match
			*/
			function doEndMatch(match) {
				const lexeme = match[0];
				const matchPlusRemainder = codeToHighlight.substring(match.index);
				const endMode = endOfMode(top, match, matchPlusRemainder);
				if (!endMode) return NO_MATCH;
				const origin = top;
				if (top.endScope && top.endScope._wrap) {
					processBuffer();
					emitKeyword(lexeme, top.endScope._wrap);
				} else if (top.endScope && top.endScope._multi) {
					processBuffer();
					emitMultiClass(top.endScope, match);
				} else if (origin.skip) modeBuffer += lexeme;
				else {
					if (!(origin.returnEnd || origin.excludeEnd)) modeBuffer += lexeme;
					processBuffer();
					if (origin.excludeEnd) modeBuffer = lexeme;
				}
				do {
					if (top.scope) emitter.closeNode();
					if (!top.skip && !top.subLanguage) relevance += top.relevance;
					top = top.parent;
				} while (top !== endMode.parent);
				if (endMode.starts) startNewMode(endMode.starts, match);
				return origin.returnEnd ? 0 : lexeme.length;
			}
			function processContinuations() {
				const list = [];
				for (let current = top; current !== language; current = current.parent) if (current.scope) list.unshift(current.scope);
				list.forEach((item) => emitter.openNode(item));
			}
			/** @type {{type?: MatchType, index?: number, rule?: Mode}}} */
			let lastMatch = {};
			/**
			*  Process an individual match
			*
			* @param {string} textBeforeMatch - text preceding the match (since the last match)
			* @param {EnhancedMatch} [match] - the match itself
			*/
			function processLexeme(textBeforeMatch, match) {
				const lexeme = match && match[0];
				modeBuffer += textBeforeMatch;
				if (lexeme == null) {
					processBuffer();
					return 0;
				}
				if (lastMatch.type === "begin" && match.type === "end" && lastMatch.index === match.index && lexeme === "") {
					modeBuffer += codeToHighlight.slice(match.index, match.index + 1);
					if (!SAFE_MODE) {
						/** @type {AnnotatedError} */
						const err = /* @__PURE__ */ new Error(`0 width match regex (${languageName})`);
						err.languageName = languageName;
						err.badRule = lastMatch.rule;
						throw err;
					}
					return 1;
				}
				lastMatch = match;
				if (match.type === "begin") return doBeginMatch(match);
				else if (match.type === "illegal" && !ignoreIllegals) {
					/** @type {AnnotatedError} */
					const err = /* @__PURE__ */ new Error("Illegal lexeme \"" + lexeme + "\" for mode \"" + (top.scope || "<unnamed>") + "\"");
					err.mode = top;
					throw err;
				} else if (match.type === "end") {
					const processed = doEndMatch(match);
					if (processed !== NO_MATCH) return processed;
				}
				if (match.type === "illegal" && lexeme === "") {
					modeBuffer += "\n";
					return 1;
				}
				if (iterations > 1e5 && iterations > match.index * 3) throw /* @__PURE__ */ new Error("potential infinite loop, way more iterations than matches");
				modeBuffer += lexeme;
				return lexeme.length;
			}
			const language = getLanguage(languageName);
			if (!language) {
				error(LANGUAGE_NOT_FOUND.replace("{}", languageName));
				throw new Error("Unknown language: \"" + languageName + "\"");
			}
			const md = compileLanguage(language);
			let result = "";
			/** @type {CompiledMode} */
			let top = continuation || md;
			/** @type Record<string,CompiledMode> */
			const continuations = {};
			const emitter = new options.__emitter(options);
			processContinuations();
			let modeBuffer = "";
			let relevance = 0;
			let index = 0;
			let iterations = 0;
			let resumeScanAtSamePosition = false;
			try {
				if (!language.__emitTokens) {
					top.matcher.considerAll();
					for (;;) {
						iterations++;
						if (resumeScanAtSamePosition) resumeScanAtSamePosition = false;
						else top.matcher.considerAll();
						top.matcher.lastIndex = index;
						const match = top.matcher.exec(codeToHighlight);
						if (!match) break;
						const processedCount = processLexeme(codeToHighlight.substring(index, match.index), match);
						index = match.index + processedCount;
					}
					processLexeme(codeToHighlight.substring(index));
				} else language.__emitTokens(codeToHighlight, emitter);
				emitter.finalize();
				result = emitter.toHTML();
				return {
					language: languageName,
					value: result,
					relevance,
					illegal: false,
					_emitter: emitter,
					_top: top
				};
			} catch (err) {
				if (err.message && err.message.includes("Illegal")) return {
					language: languageName,
					value: escape(codeToHighlight),
					illegal: true,
					relevance: 0,
					_illegalBy: {
						message: err.message,
						index,
						context: codeToHighlight.slice(index - 100, index + 100),
						mode: err.mode,
						resultSoFar: result
					},
					_emitter: emitter
				};
				else if (SAFE_MODE) return {
					language: languageName,
					value: escape(codeToHighlight),
					illegal: false,
					relevance: 0,
					errorRaised: err,
					_emitter: emitter,
					_top: top
				};
				else throw err;
			}
		}
		/**
		* returns a valid highlight result, without actually doing any actual work,
		* auto highlight starts with this and it's possible for small snippets that
		* auto-detection may not find a better match
		* @param {string} code
		* @returns {HighlightResult}
		*/
		function justTextHighlightResult(code) {
			const result = {
				value: escape(code),
				illegal: false,
				relevance: 0,
				_top: PLAINTEXT_LANGUAGE,
				_emitter: new options.__emitter(options)
			};
			result._emitter.addText(code);
			return result;
		}
		/**
		Highlighting with language detection. Accepts a string with the code to
		highlight. Returns an object with the following properties:
		
		- language (detected language)
		- relevance (int)
		- value (an HTML string with highlighting markup)
		- secondBest (object with the same structure for second-best heuristically
		detected language, may be absent)
		
		@param {string} code
		@param {Array<string>} [languageSubset]
		@returns {AutoHighlightResult}
		*/
		function highlightAuto(code, languageSubset) {
			languageSubset = languageSubset || options.languages || Object.keys(languages);
			const plaintext = justTextHighlightResult(code);
			const results = languageSubset.filter(getLanguage).filter(autoDetection).map((name) => _highlight(name, code, false));
			results.unshift(plaintext);
			const [best, secondBest] = results.sort((a, b) => {
				if (a.relevance !== b.relevance) return b.relevance - a.relevance;
				if (a.language && b.language) {
					if (getLanguage(a.language).supersetOf === b.language) return 1;
					else if (getLanguage(b.language).supersetOf === a.language) return -1;
				}
				return 0;
			});
			/** @type {AutoHighlightResult} */
			const result = best;
			result.secondBest = secondBest;
			return result;
		}
		/**
		* Builds new class name for block given the language name
		*
		* @param {HTMLElement} element
		* @param {string} [currentLang]
		* @param {string} [resultLang]
		*/
		function updateClassName(element, currentLang, resultLang) {
			const language = currentLang && aliases[currentLang] || resultLang;
			element.classList.add("hljs");
			element.classList.add(`language-${language}`);
		}
		/**
		* Applies highlighting to a DOM node containing code.
		*
		* @param {HighlightedHTMLElement} element - the HTML element to highlight
		*/
		function highlightElement(element) {
			/** @type HTMLElement */
			let node = null;
			const language = blockLanguage(element);
			if (shouldNotHighlight(language)) return;
			fire("before:highlightElement", {
				el: element,
				language
			});
			if (element.dataset.highlighted) {
				console.log("Element previously highlighted. To highlight again, first unset `dataset.highlighted`.", element);
				return;
			}
			if (element.children.length > 0) {
				if (!options.ignoreUnescapedHTML) {
					console.warn("One of your code blocks includes unescaped HTML. This is a potentially serious security risk.");
					console.warn("https://github.com/highlightjs/highlight.js/wiki/security");
					console.warn("The element with unescaped HTML:");
					console.warn(element);
				}
				if (options.throwUnescapedHTML) throw new HTMLInjectionError("One of your code blocks includes unescaped HTML.", element.innerHTML);
			}
			node = element;
			const text = node.textContent;
			const result = language ? highlight(text, {
				language,
				ignoreIllegals: true
			}) : highlightAuto(text);
			element.innerHTML = result.value;
			element.dataset.highlighted = "yes";
			updateClassName(element, language, result.language);
			element.result = {
				language: result.language,
				re: result.relevance,
				relevance: result.relevance
			};
			if (result.secondBest) element.secondBest = {
				language: result.secondBest.language,
				relevance: result.secondBest.relevance
			};
			fire("after:highlightElement", {
				el: element,
				result,
				text
			});
		}
		/**
		* Updates highlight.js global options with the passed options
		*
		* @param {Partial<HLJSOptions>} userOptions
		*/
		function configure(userOptions) {
			options = inherit(options, userOptions);
		}
		const initHighlighting = () => {
			highlightAll();
			deprecated("10.6.0", "initHighlighting() deprecated.  Use highlightAll() now.");
		};
		function initHighlightingOnLoad() {
			highlightAll();
			deprecated("10.6.0", "initHighlightingOnLoad() deprecated.  Use highlightAll() now.");
		}
		let wantsHighlight = false;
		/**
		* auto-highlights all pre>code elements on the page
		*/
		function highlightAll() {
			function boot() {
				highlightAll();
			}
			if (document.readyState === "loading") {
				if (!wantsHighlight) window.addEventListener("DOMContentLoaded", boot, false);
				wantsHighlight = true;
				return;
			}
			document.querySelectorAll(options.cssSelector).forEach(highlightElement);
		}
		/**
		* Register a language grammar module
		*
		* @param {string} languageName
		* @param {LanguageFn} languageDefinition
		*/
		function registerLanguage(languageName, languageDefinition) {
			let lang = null;
			try {
				lang = languageDefinition(hljs);
			} catch (error$1) {
				error("Language definition for '{}' could not be registered.".replace("{}", languageName));
				if (!SAFE_MODE) throw error$1;
				else error(error$1);
				lang = PLAINTEXT_LANGUAGE;
			}
			if (!lang.name) lang.name = languageName;
			languages[languageName] = lang;
			lang.rawDefinition = languageDefinition.bind(null, hljs);
			if (lang.aliases) registerAliases(lang.aliases, { languageName });
		}
		/**
		* Remove a language grammar module
		*
		* @param {string} languageName
		*/
		function unregisterLanguage(languageName) {
			delete languages[languageName];
			for (const alias of Object.keys(aliases)) if (aliases[alias] === languageName) delete aliases[alias];
		}
		/**
		* @returns {string[]} List of language internal names
		*/
		function listLanguages() {
			return Object.keys(languages);
		}
		/**
		* @param {string} name - name of the language to retrieve
		* @returns {Language | undefined}
		*/
		function getLanguage(name) {
			name = (name || "").toLowerCase();
			return languages[name] || languages[aliases[name]];
		}
		/**
		*
		* @param {string|string[]} aliasList - single alias or list of aliases
		* @param {{languageName: string}} opts
		*/
		function registerAliases(aliasList, { languageName }) {
			if (typeof aliasList === "string") aliasList = [aliasList];
			aliasList.forEach((alias) => {
				aliases[alias.toLowerCase()] = languageName;
			});
		}
		/**
		* Determines if a given language has auto-detection enabled
		* @param {string} name - name of the language
		*/
		function autoDetection(name) {
			const lang = getLanguage(name);
			return lang && !lang.disableAutodetect;
		}
		/**
		* Upgrades the old highlightBlock plugins to the new
		* highlightElement API
		* @param {HLJSPlugin} plugin
		*/
		function upgradePluginAPI(plugin) {
			if (plugin["before:highlightBlock"] && !plugin["before:highlightElement"]) plugin["before:highlightElement"] = (data) => {
				plugin["before:highlightBlock"](Object.assign({ block: data.el }, data));
			};
			if (plugin["after:highlightBlock"] && !plugin["after:highlightElement"]) plugin["after:highlightElement"] = (data) => {
				plugin["after:highlightBlock"](Object.assign({ block: data.el }, data));
			};
		}
		/**
		* @param {HLJSPlugin} plugin
		*/
		function addPlugin(plugin) {
			upgradePluginAPI(plugin);
			plugins.push(plugin);
		}
		/**
		* @param {HLJSPlugin} plugin
		*/
		function removePlugin(plugin) {
			const index = plugins.indexOf(plugin);
			if (index !== -1) plugins.splice(index, 1);
		}
		/**
		*
		* @param {PluginEvent} event
		* @param {any} args
		*/
		function fire(event, args) {
			const cb = event;
			plugins.forEach(function(plugin) {
				if (plugin[cb]) plugin[cb](args);
			});
		}
		/**
		* DEPRECATED
		* @param {HighlightedHTMLElement} el
		*/
		function deprecateHighlightBlock(el) {
			deprecated("10.7.0", "highlightBlock will be removed entirely in v12.0");
			deprecated("10.7.0", "Please use highlightElement now.");
			return highlightElement(el);
		}
		Object.assign(hljs, {
			highlight,
			highlightAuto,
			highlightAll,
			highlightElement,
			highlightBlock: deprecateHighlightBlock,
			configure,
			initHighlighting,
			initHighlightingOnLoad,
			registerLanguage,
			unregisterLanguage,
			listLanguages,
			getLanguage,
			registerAliases,
			autoDetection,
			inherit,
			addPlugin,
			removePlugin
		});
		hljs.debugMode = function() {
			SAFE_MODE = false;
		};
		hljs.safeMode = function() {
			SAFE_MODE = true;
		};
		hljs.versionString = version;
		hljs.regex = {
			concat,
			lookahead,
			either,
			optional,
			anyNumberOfTimes
		};
		for (const key in MODES) if (typeof MODES[key] === "object") deepFreeze(MODES[key]);
		Object.assign(hljs, MODES);
		return hljs;
	};
	var highlight = HLJS({});
	highlight.newInstance = () => HLJS({});
	module.exports = highlight;
	highlight.HighlightJS = highlight;
	highlight.default = highlight;
})))())).default;
//#endregion
//#region node_modules/@tiptap/extension-code-block-lowlight/dist/index.js
function parseNodes(nodes, className = []) {
	return nodes.flatMap((node) => {
		const classes = [...className, ...node.properties ? node.properties.className : []];
		if (node.children) return parseNodes(node.children, classes);
		return {
			text: node.value,
			classes
		};
	});
}
function getHighlightNodes(result) {
	return result.value || result.children || [];
}
function registered(aliasOrLanguage) {
	return Boolean(core_default.getLanguage(aliasOrLanguage));
}
function getDecorations({ doc, name, lowlight, defaultLanguage }) {
	const decorations = [];
	findChildren(doc, (node) => node.type.name === name).forEach((block) => {
		var _a;
		let from = block.pos + 1;
		const language = block.node.attrs.language || defaultLanguage;
		const languages = lowlight.listLanguages();
		parseNodes(language && (languages.includes(language) || registered(language) || ((_a = lowlight.registered) == null ? void 0 : _a.call(lowlight, language))) ? getHighlightNodes(lowlight.highlight(language, block.node.textContent)) : getHighlightNodes(lowlight.highlightAuto(block.node.textContent))).forEach((node) => {
			const to = from + node.text.length;
			if (node.classes.length) {
				const decoration = Decoration.inline(from, to, { class: node.classes.join(" ") });
				decorations.push(decoration);
			}
			from = to;
		});
	});
	return DecorationSet.create(doc, decorations);
}
function isFunction(param) {
	return typeof param === "function";
}
function LowlightPlugin({ name, lowlight, defaultLanguage }) {
	if (![
		"highlight",
		"highlightAuto",
		"listLanguages"
	].every((api) => isFunction(lowlight[api]))) throw Error("You should provide an instance of lowlight to use the code-block-lowlight extension");
	const lowlightPlugin = new Plugin({
		key: new PluginKey("lowlight"),
		state: {
			init: (_, { doc }) => getDecorations({
				doc,
				name,
				lowlight,
				defaultLanguage
			}),
			apply: (transaction, decorationSet, oldState, newState) => {
				const oldNodeName = oldState.selection.$head.parent.type.name;
				const newNodeName = newState.selection.$head.parent.type.name;
				const oldNodes = findChildren(oldState.doc, (node) => node.type.name === name);
				const newNodes = findChildren(newState.doc, (node) => node.type.name === name);
				if (transaction.docChanged && ([oldNodeName, newNodeName].includes(name) || newNodes.length !== oldNodes.length || transaction.steps.some((step) => {
					return step.from !== void 0 && step.to !== void 0 && oldNodes.some((node) => {
						return node.pos >= step.from && node.pos + node.node.nodeSize <= step.to;
					});
				}))) return getDecorations({
					doc: transaction.doc,
					name,
					lowlight,
					defaultLanguage
				});
				return decorationSet.map(transaction.mapping, transaction.doc);
			}
		},
		props: { decorations(state) {
			return lowlightPlugin.getState(state);
		} }
	});
	return lowlightPlugin;
}
var index_default$14 = CodeBlock.extend({
	addOptions() {
		var _a;
		return {
			...(_a = this.parent) == null ? void 0 : _a.call(this),
			lowlight: {},
			languageClassPrefix: "language-",
			exitOnTripleEnter: true,
			exitOnArrowDown: true,
			defaultLanguage: null,
			enableTabIndentation: false,
			tabSize: 4,
			HTMLAttributes: {}
		};
	},
	addProseMirrorPlugins() {
		var _a;
		return [...((_a = this.parent) == null ? void 0 : _a.call(this)) || [], LowlightPlugin({
			name: this.name,
			lowlight: this.options.lowlight,
			defaultLanguage: this.options.defaultLanguage
		})];
	}
});
var index_default$13 = Node3.create({
	name: "hardBreak",
	markdownTokenName: "br",
	addOptions() {
		return {
			keepMarks: true,
			HTMLAttributes: {}
		};
	},
	inline: true,
	group: "inline",
	selectable: false,
	linebreakReplacement: true,
	parseHTML() {
		return [{ tag: "br" }];
	},
	renderHTML({ HTMLAttributes }) {
		return ["br", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)];
	},
	renderText() {
		return "\n";
	},
	renderMarkdown: () => `  
`,
	parseMarkdown: () => {
		return { type: "hardBreak" };
	},
	addCommands() {
		return { setHardBreak: () => ({ commands, chain, state, editor }) => {
			return commands.first([() => commands.exitCode(), () => commands.command(() => {
				const { selection, storedMarks } = state;
				if (selection.$from.parent.type.spec.isolating) return false;
				const { keepMarks } = this.options;
				const { splittableMarks } = editor.extensionManager;
				const marks = storedMarks || selection.$to.parentOffset && selection.$from.marks();
				return chain().insertContent({ type: this.name }).command(({ tr, dispatch }) => {
					if (dispatch && marks && keepMarks) {
						const filteredMarks = marks.filter((mark) => splittableMarks.includes(mark.type.name));
						tr.ensureMarks(filteredMarks);
					}
					return true;
				}).run();
			})]);
		} };
	},
	addKeyboardShortcuts() {
		return {
			"Mod-Enter": () => this.editor.commands.setHardBreak(),
			"Shift-Enter": () => this.editor.commands.setHardBreak()
		};
	}
});
var index_default$12 = Node3.create({
	name: "heading",
	addOptions() {
		return {
			levels: [
				1,
				2,
				3,
				4,
				5,
				6
			],
			HTMLAttributes: {}
		};
	},
	content: "inline*",
	group: "block",
	defining: true,
	addAttributes() {
		return { level: {
			default: 1,
			rendered: false
		} };
	},
	parseHTML() {
		return this.options.levels.map((level) => ({
			tag: `h${level}`,
			attrs: { level }
		}));
	},
	renderHTML({ node, HTMLAttributes }) {
		return [
			`h${this.options.levels.includes(node.attrs.level) ? node.attrs.level : this.options.levels[0]}`,
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	parseMarkdown: (token, helpers) => {
		return helpers.createNode("heading", { level: token.depth || 1 }, helpers.parseInline(token.tokens || []));
	},
	renderMarkdown: (node, h) => {
		var _a;
		const level = ((_a = node.attrs) == null ? void 0 : _a.level) ? parseInt(node.attrs.level, 10) : 1;
		const headingChars = "#".repeat(level);
		if (!node.content) return "";
		return `${headingChars} ${h.renderChildren(node.content)}`;
	},
	addCommands() {
		return {
			setHeading: (attributes) => ({ commands }) => {
				if (!this.options.levels.includes(attributes.level)) return false;
				return commands.setNode(this.name, attributes);
			},
			toggleHeading: (attributes) => ({ commands }) => {
				if (!this.options.levels.includes(attributes.level)) return false;
				return commands.toggleNode(this.name, "paragraph", attributes);
			}
		};
	},
	addKeyboardShortcuts() {
		return this.options.levels.reduce((items, level) => ({
			...items,
			[`Mod-Alt-${level}`]: () => this.editor.commands.toggleHeading({ level })
		}), {});
	},
	addInputRules() {
		return this.options.levels.map((level) => {
			return textblockTypeInputRule({
				find: new RegExp(`^(#{${Math.min(...this.options.levels)},${level}})\\s$`),
				type: this.type,
				getAttributes: { level }
			});
		});
	}
});
//#endregion
//#region node_modules/@tiptap/extension-history/dist/index.js
var index_default$11 = UndoRedo;
var index_default$10 = Node3.create({
	name: "horizontalRule",
	addOptions() {
		return {
			HTMLAttributes: {},
			nextNodeType: "paragraph"
		};
	},
	group: "block",
	parseHTML() {
		return [{ tag: "hr" }];
	},
	renderHTML({ HTMLAttributes }) {
		return ["hr", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)];
	},
	markdownTokenName: "hr",
	parseMarkdown: (token, helpers) => {
		return helpers.createNode("horizontalRule");
	},
	renderMarkdown: () => {
		return "---";
	},
	addCommands() {
		return { setHorizontalRule: () => ({ chain, state }) => {
			if (!canInsertNode(state, state.schema.nodes[this.name])) return false;
			const { selection } = state;
			const { $to: $originTo } = selection;
			const currentChain = chain();
			if (isNodeSelection(selection)) currentChain.insertContentAt($originTo.pos, { type: this.name });
			else currentChain.insertContent({ type: this.name });
			return currentChain.command(({ state: chainState, tr, dispatch }) => {
				if (dispatch) {
					const { $to } = tr.selection;
					const posAfter = $to.end();
					if ($to.nodeAfter) if ($to.nodeAfter.isTextblock) tr.setSelection(TextSelection.create(tr.doc, $to.pos + 1));
					else if ($to.nodeAfter.isBlock) tr.setSelection(NodeSelection.create(tr.doc, $to.pos));
					else tr.setSelection(TextSelection.create(tr.doc, $to.pos));
					else {
						const nodeType = chainState.schema.nodes[this.options.nextNodeType] || $to.parent.type.contentMatch.defaultType;
						const node = nodeType == null ? void 0 : nodeType.create();
						if (node) {
							tr.insert(posAfter, node);
							tr.setSelection(TextSelection.create(tr.doc, posAfter + 1));
						}
					}
					tr.scrollIntoView();
				}
				return true;
			}).run();
		} };
	},
	addInputRules() {
		return [nodeInputRule({
			find: /^(?:---|—-|___\s|\*\*\*\s)$/,
			type: this.type
		})];
	}
});
//#endregion
//#region node_modules/@tiptap/extension-italic/dist/index.js
var starInputRegex = /(?:^|\s)(\*(?!\s+\*)((?:[^*]+))\*(?!\s+\*))$/;
var starPasteRegex = /(?:^|\s)(\*(?!\s+\*)((?:[^*]+))\*(?!\s+\*))/g;
var underscoreInputRegex = /(?:^|\s)(_(?!\s+_)((?:[^_]+))_(?!\s+_))$/;
var underscorePasteRegex = /(?:^|\s)(_(?!\s+_)((?:[^_]+))_(?!\s+_))/g;
var index_default$9 = Mark.create({
	name: "italic",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [
			{ tag: "em" },
			{
				tag: "i",
				getAttrs: (node) => node.style.fontStyle !== "normal" && null
			},
			{
				style: "font-style=normal",
				clearMark: (mark) => mark.type.name === this.name
			},
			{ style: "font-style=italic" }
		];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"em",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	addCommands() {
		return {
			setItalic: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleItalic: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetItalic: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	markdownTokenName: "em",
	parseMarkdown: (token, helpers) => {
		return helpers.applyMark("italic", helpers.parseInline(token.tokens || []));
	},
	markdownOptions: { htmlReopen: {
		open: "<em>",
		close: "</em>"
	} },
	renderMarkdown: (node, h) => {
		return `*${h.renderChildren(node)}*`;
	},
	addKeyboardShortcuts() {
		return {
			"Mod-i": () => this.editor.commands.toggleItalic(),
			"Mod-I": () => this.editor.commands.toggleItalic()
		};
	},
	addInputRules() {
		return [markInputRule({
			find: starInputRegex,
			type: this.type
		}), markInputRule({
			find: underscoreInputRegex,
			type: this.type
		})];
	},
	addPasteRules() {
		return [markPasteRule({
			find: starPasteRegex,
			type: this.type
		}), markPasteRule({
			find: underscorePasteRegex,
			type: this.type
		})];
	}
});
//#endregion
//#region node_modules/@tiptap/extension-paragraph/dist/index.js
var EMPTY_PARAGRAPH_MARKDOWN = "&nbsp;";
var NBSP_CHAR = "\xA0";
var index_default$8 = Node3.create({
	name: "paragraph",
	priority: 1e3,
	addOptions() {
		return { HTMLAttributes: {} };
	},
	group: "block",
	content: "inline*",
	parseHTML() {
		return [{ tag: "p" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"p",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	parseMarkdown: (token, helpers) => {
		const tokens = token.tokens || [];
		if (tokens.length === 1 && tokens[0].type === "image") return helpers.parseChildren([tokens[0]]);
		const content = helpers.parseInline(tokens);
		if (tokens.length === 1 && tokens[0].type === "text" && (tokens[0].raw === EMPTY_PARAGRAPH_MARKDOWN || tokens[0].text === EMPTY_PARAGRAPH_MARKDOWN || tokens[0].raw === NBSP_CHAR || tokens[0].text === NBSP_CHAR) && content.length === 1 && content[0].type === "text" && (content[0].text === EMPTY_PARAGRAPH_MARKDOWN || content[0].text === NBSP_CHAR)) return helpers.createNode("paragraph", void 0, []);
		return helpers.createNode("paragraph", void 0, content);
	},
	renderMarkdown: (node, h, ctx) => {
		var _a, _b;
		if (!node) return "";
		const content = Array.isArray(node.content) ? node.content : [];
		if (content.length === 0) {
			const previousContent = Array.isArray((_a = ctx == null ? void 0 : ctx.previousNode) == null ? void 0 : _a.content) ? ctx.previousNode.content : [];
			return ((_b = ctx == null ? void 0 : ctx.previousNode) == null ? void 0 : _b.type) === "paragraph" && previousContent.length === 0 ? EMPTY_PARAGRAPH_MARKDOWN : "";
		}
		return h.renderChildren(content);
	},
	addCommands() {
		return { setParagraph: () => ({ commands }) => {
			return commands.setNode(this.name);
		} };
	},
	addKeyboardShortcuts() {
		return { "Mod-Alt-0": () => this.editor.commands.setParagraph() };
	}
});
//#endregion
//#region node_modules/@tiptap/extension-strike/dist/index.js
var inputRegex = /(?:^|\s)(~~(?!\s+~~)((?:[^~]+))~~(?!\s+~~))$/;
var pasteRegex = /(?:^|\s)(~~(?!\s+~~)((?:[^~]+))~~(?!\s+~~))/g;
var index_default$7 = Mark.create({
	name: "strike",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [
			{ tag: "s" },
			{ tag: "del" },
			{ tag: "strike" },
			{
				style: "text-decoration",
				consuming: false,
				getAttrs: (style) => style.includes("line-through") ? {} : false
			}
		];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"s",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	markdownTokenName: "del",
	parseMarkdown: (token, helpers) => {
		return helpers.applyMark("strike", helpers.parseInline(token.tokens || []));
	},
	renderMarkdown: (node, h) => {
		return `~~${h.renderChildren(node)}~~`;
	},
	addCommands() {
		return {
			setStrike: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleStrike: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetStrike: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return { "Mod-Shift-s": () => this.editor.commands.toggleStrike() };
	},
	addInputRules() {
		return [markInputRule({
			find: inputRegex,
			type: this.type
		})];
	},
	addPasteRules() {
		return [markPasteRule({
			find: pasteRegex,
			type: this.type
		})];
	}
});
var index_default$6 = Mark.create({
	name: "subscript",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [{ tag: "sub" }, {
			style: "vertical-align",
			getAttrs(value) {
				if (value !== "sub") return false;
				return null;
			}
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"sub",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	addCommands() {
		return {
			setSubscript: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleSubscript: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetSubscript: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return { "Mod-,": () => this.editor.commands.toggleSubscript() };
	}
});
var index_default$5 = Mark.create({
	name: "superscript",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [{ tag: "sup" }, {
			style: "vertical-align",
			getAttrs(value) {
				if (value !== "super") return false;
				return null;
			}
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"sup",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	addCommands() {
		return {
			setSuperscript: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleSuperscript: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetSuperscript: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return { "Mod-.": () => this.editor.commands.toggleSuperscript() };
	}
});
//#endregion
//#region node_modules/@tiptap/extension-table/dist/index.js
function normalizeTableCellAlign(value) {
	if (value === "left" || value === "right" || value === "center") return value;
	return null;
}
function parseAlign(element) {
	const styleAlign = (element.style.textAlign || "").trim().toLowerCase();
	const attrAlign = (element.getAttribute("align") || "").trim().toLowerCase();
	return normalizeTableCellAlign(styleAlign || attrAlign);
}
function normalizeTableCellAlignFromAttributes(attributes) {
	return normalizeTableCellAlign(attributes == null ? void 0 : attributes.align);
}
function createAlignAttribute() {
	return {
		default: null,
		parseHTML: (element) => parseAlign(element),
		renderHTML: (attributes) => {
			if (!attributes.align) return {};
			return { style: `text-align: ${attributes.align}` };
		}
	};
}
var TableCell = Node3.create({
	name: "tableCell",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	content: "block+",
	addAttributes() {
		return {
			colspan: { default: 1 },
			rowspan: { default: 1 },
			colwidth: {
				default: null,
				parseHTML: (element) => {
					var _a, _b;
					const colwidth = element.getAttribute("colwidth");
					const value = colwidth ? colwidth.split(",").map((width) => parseInt(width, 10)) : null;
					if (!value) {
						const cols = (_a = element.closest("table")) == null ? void 0 : _a.querySelectorAll("colgroup > col");
						const cellIndex = Array.from(((_b = element.parentElement) == null ? void 0 : _b.children) || []).indexOf(element);
						if (cellIndex && cellIndex > -1 && cols && cols[cellIndex]) {
							const colWidth = cols[cellIndex].getAttribute("width");
							return colWidth ? [parseInt(colWidth, 10)] : null;
						}
					}
					return value;
				}
			},
			align: createAlignAttribute()
		};
	},
	tableRole: "cell",
	isolating: true,
	parseHTML() {
		return [{ tag: "td" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"td",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	}
});
var TableHeader = Node3.create({
	name: "tableHeader",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	content: "block+",
	addAttributes() {
		return {
			colspan: { default: 1 },
			rowspan: { default: 1 },
			colwidth: {
				default: null,
				parseHTML: (element) => {
					const colwidth = element.getAttribute("colwidth");
					return colwidth ? colwidth.split(",").map((width) => parseInt(width, 10)) : null;
				}
			},
			align: createAlignAttribute()
		};
	},
	tableRole: "header_cell",
	isolating: true,
	parseHTML() {
		return [{ tag: "th" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"th",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	}
});
var TableRow = Node3.create({
	name: "tableRow",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	content: "(tableCell | tableHeader)*",
	tableRole: "row",
	parseHTML() {
		return [{ tag: "tr" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"tr",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	}
});
function getColStyleDeclaration(minWidth, width) {
	if (width) return ["width", `${Math.max(width, minWidth)}px`];
	return ["min-width", `${minWidth}px`];
}
function updateColumns(node, colgroup, table, cellMinWidth, overrideCol, overrideValue) {
	var _a;
	let totalWidth = 0;
	let fixedWidth = true;
	let nextDOM = colgroup.firstChild;
	const row = node.firstChild;
	if (row !== null) for (let i = 0, col = 0; i < row.childCount; i += 1) {
		const { colspan, colwidth } = row.child(i).attrs;
		for (let j = 0; j < colspan; j += 1, col += 1) {
			const hasWidth = overrideCol === col ? overrideValue : colwidth && colwidth[j];
			const cssWidth = hasWidth ? `${hasWidth}px` : "";
			totalWidth += hasWidth || cellMinWidth;
			if (!hasWidth) fixedWidth = false;
			if (!nextDOM) {
				const colElement = document.createElement("col");
				const [propertyKey, propertyValue] = getColStyleDeclaration(cellMinWidth, hasWidth);
				colElement.style.setProperty(propertyKey, propertyValue);
				colgroup.appendChild(colElement);
			} else {
				if (nextDOM.style.width !== cssWidth) {
					const [propertyKey, propertyValue] = getColStyleDeclaration(cellMinWidth, hasWidth);
					nextDOM.style.setProperty(propertyKey, propertyValue);
				}
				nextDOM = nextDOM.nextSibling;
			}
		}
	}
	while (nextDOM) {
		const after = nextDOM.nextSibling;
		(_a = nextDOM.parentNode) == null || _a.removeChild(nextDOM);
		nextDOM = after;
	}
	const hasUserWidth = node.attrs.style && typeof node.attrs.style === "string" && /\bwidth\s*:/i.test(node.attrs.style);
	if (fixedWidth && !hasUserWidth) {
		table.style.width = `${totalWidth}px`;
		table.style.minWidth = "";
	} else {
		table.style.width = "";
		table.style.minWidth = `${totalWidth}px`;
	}
}
var TableView = class {
	constructor(node, cellMinWidth) {
		this.node = node;
		this.cellMinWidth = cellMinWidth;
		this.dom = document.createElement("div");
		this.dom.className = "tableWrapper";
		this.table = this.dom.appendChild(document.createElement("table"));
		if (node.attrs.style) this.table.style.cssText = node.attrs.style;
		this.colgroup = this.table.appendChild(document.createElement("colgroup"));
		updateColumns(node, this.colgroup, this.table, cellMinWidth);
		this.contentDOM = this.table.appendChild(document.createElement("tbody"));
	}
	update(node) {
		if (node.type !== this.node.type) return false;
		this.node = node;
		updateColumns(node, this.colgroup, this.table, this.cellMinWidth);
		return true;
	}
	ignoreMutation(mutation) {
		const target = mutation.target;
		const isInsideWrapper = this.dom.contains(target);
		const isInsideContent = this.contentDOM.contains(target);
		if (isInsideWrapper && !isInsideContent) {
			if (mutation.type === "attributes" || mutation.type === "childList" || mutation.type === "characterData") return true;
		}
		return false;
	}
};
function createColGroup(node, cellMinWidth, overrideCol, overrideValue) {
	let totalWidth = 0;
	let fixedWidth = true;
	const cols = [];
	const row = node.firstChild;
	if (!row) return {};
	for (let i = 0, col = 0; i < row.childCount; i += 1) {
		const { colspan, colwidth } = row.child(i).attrs;
		for (let j = 0; j < colspan; j += 1, col += 1) {
			const hasWidth = overrideCol === col ? overrideValue : colwidth && colwidth[j];
			totalWidth += hasWidth || cellMinWidth;
			if (!hasWidth) fixedWidth = false;
			const [property, value] = getColStyleDeclaration(cellMinWidth, hasWidth);
			cols.push(["col", { style: `${property}: ${value}` }]);
		}
	}
	const tableWidth = fixedWidth ? `${totalWidth}px` : "";
	const tableMinWidth = fixedWidth ? "" : `${totalWidth}px`;
	return {
		colgroup: [
			"colgroup",
			{},
			...cols
		],
		tableWidth,
		tableMinWidth
	};
}
function createCell(cellType, cellContent) {
	if (cellContent) return cellType.createChecked(null, cellContent);
	return cellType.createAndFill();
}
function getTableNodeTypes(schema) {
	if (schema.cached.tableNodeTypes) return schema.cached.tableNodeTypes;
	const roles = {};
	Object.keys(schema.nodes).forEach((type) => {
		const nodeType = schema.nodes[type];
		if (nodeType.spec.tableRole) roles[nodeType.spec.tableRole] = nodeType;
	});
	schema.cached.tableNodeTypes = roles;
	return roles;
}
function createTable(schema, rowsCount, colsCount, withHeaderRow, cellContent) {
	const types = getTableNodeTypes(schema);
	const headerCells = [];
	const cells = [];
	for (let index = 0; index < colsCount; index += 1) {
		const cell = createCell(types.cell, cellContent);
		if (cell) cells.push(cell);
		if (withHeaderRow) {
			const headerCell = createCell(types.header_cell, cellContent);
			if (headerCell) headerCells.push(headerCell);
		}
	}
	const rows = [];
	for (let index = 0; index < rowsCount; index += 1) rows.push(types.row.createChecked(null, withHeaderRow && index === 0 ? headerCells : cells));
	return types.table.createChecked(null, rows);
}
function isCellSelection(value) {
	return value instanceof CellSelection;
}
var deleteTableWhenAllCellsSelected = ({ editor }) => {
	const { selection } = editor.state;
	if (!isCellSelection(selection)) return false;
	let cellCount = 0;
	findParentNodeClosestToPos(selection.ranges[0].$from, (node) => {
		return node.type.name === "table";
	})?.node.descendants((node) => {
		if (node.type.name === "table") return false;
		if (["tableCell", "tableHeader"].includes(node.type.name)) cellCount += 1;
	});
	if (!(cellCount === selection.ranges.length)) return false;
	editor.commands.deleteTable();
	return true;
};
function collapseWhitespace(s) {
	return (s || "").replace(/\s+/g, " ").trim();
}
function renderTableToMarkdown(node, h, options = {}) {
	var _a;
	const cellSep = (_a = options.cellLineSeparator) != null ? _a : "";
	if (!node || !node.content || node.content.length === 0) return "";
	const rows = [];
	node.content.forEach((rowNode) => {
		const cells = [];
		if (rowNode.content) rowNode.content.forEach((cellNode) => {
			let raw = "";
			if (cellNode.content && Array.isArray(cellNode.content) && cellNode.content.length > 1) raw = cellNode.content.map((child) => h.renderChildren(child)).join(cellSep);
			else raw = cellNode.content ? h.renderChildren(cellNode.content) : "";
			const text = collapseWhitespace(raw);
			const isHeader = cellNode.type === "tableHeader";
			const align = normalizeTableCellAlignFromAttributes(cellNode.attrs);
			cells.push({
				text,
				isHeader,
				align
			});
		});
		rows.push(cells);
	});
	const columnCount = rows.reduce((max, r) => Math.max(max, r.length), 0);
	if (columnCount === 0) return "";
	const colWidths = new Array(columnCount).fill(0);
	rows.forEach((r) => {
		var _a2;
		for (let i = 0; i < columnCount; i += 1) {
			const len = (((_a2 = r[i]) == null ? void 0 : _a2.text) || "").length;
			if (len > colWidths[i]) colWidths[i] = len;
			if (colWidths[i] < 3) colWidths[i] = 3;
		}
	});
	const pad = (s, width) => s + " ".repeat(Math.max(0, width - s.length));
	const headerRow = rows[0];
	const hasHeader = headerRow.some((c) => c.isHeader);
	const colAlignments = new Array(columnCount).fill(null);
	rows.forEach((r) => {
		var _a2;
		for (let i = 0; i < columnCount; i += 1) if (!colAlignments[i] && ((_a2 = r[i]) == null ? void 0 : _a2.align)) colAlignments[i] = r[i].align;
	});
	let out = "\n";
	const headerTexts = new Array(columnCount).fill(0).map((_, i) => hasHeader ? headerRow[i] && headerRow[i].text || "" : "");
	out += `| ${headerTexts.map((t, i) => pad(t, colWidths[i])).join(" | ")} |
`;
	out += `| ${colWidths.map((w, index) => {
		const dashCount = Math.max(3, w);
		const alignment = colAlignments[index];
		if (alignment === "left") return `:${"-".repeat(dashCount)}`;
		if (alignment === "right") return `${"-".repeat(dashCount)}:`;
		if (alignment === "center") return `:${"-".repeat(dashCount)}:`;
		return "-".repeat(dashCount);
	}).join(" | ")} |
`;
	(hasHeader ? rows.slice(1) : rows).forEach((r) => {
		out += `| ${new Array(columnCount).fill(0).map((_, i) => pad(r[i] && r[i].text || "", colWidths[i])).join(" | ")} |
`;
	});
	return out;
}
var markdown_default = renderTableToMarkdown;
var Table = Node3.create({
	name: "table",
	addOptions() {
		return {
			HTMLAttributes: {},
			resizable: false,
			renderWrapper: false,
			handleWidth: 5,
			cellMinWidth: 25,
			View: TableView,
			lastColumnResizable: true,
			allowTableNodeSelection: false
		};
	},
	content: "tableRow+",
	tableRole: "table",
	isolating: true,
	group: "block",
	parseHTML() {
		return [{ tag: "table" }];
	},
	renderHTML({ node, HTMLAttributes }) {
		const { colgroup, tableWidth, tableMinWidth } = createColGroup(node, this.options.cellMinWidth);
		const userStyles = HTMLAttributes.style;
		function getTableStyle() {
			if (userStyles) return userStyles;
			return tableWidth ? `width: ${tableWidth}` : `min-width: ${tableMinWidth}`;
		}
		const table = [
			"table",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { style: getTableStyle() }),
			colgroup,
			["tbody", 0]
		];
		return this.options.renderWrapper ? [
			"div",
			{ class: "tableWrapper" },
			table
		] : table;
	},
	parseMarkdown: (token, h) => {
		const rows = [];
		const alignments = Array.isArray(token.align) ? token.align : [];
		if (token.header) {
			const headerCells = [];
			token.header.forEach((cell, index) => {
				var _a;
				const align = normalizeTableCellAlign((_a = alignments[index]) != null ? _a : cell.align);
				const attrs = align ? { align } : {};
				headerCells.push(h.createNode("tableHeader", attrs, [{
					type: "paragraph",
					content: h.parseInline(cell.tokens)
				}]));
			});
			rows.push(h.createNode("tableRow", {}, headerCells));
		}
		if (token.rows) token.rows.forEach((row) => {
			const bodyCells = [];
			row.forEach((cell, index) => {
				var _a;
				const align = normalizeTableCellAlign((_a = alignments[index]) != null ? _a : cell.align);
				const attrs = align ? { align } : {};
				bodyCells.push(h.createNode("tableCell", attrs, [{
					type: "paragraph",
					content: h.parseInline(cell.tokens)
				}]));
			});
			rows.push(h.createNode("tableRow", {}, bodyCells));
		});
		return h.createNode("table", void 0, rows);
	},
	renderMarkdown: (node, h) => {
		return markdown_default(node, h);
	},
	addCommands() {
		return {
			insertTable: ({ rows = 3, cols = 3, withHeaderRow = true } = {}) => ({ tr, dispatch, editor }) => {
				const node = createTable(editor.schema, rows, cols, withHeaderRow);
				if (dispatch) {
					const offset = tr.selection.from + 1;
					tr.replaceSelectionWith(node).scrollIntoView().setSelection(TextSelection.near(tr.doc.resolve(offset)));
				}
				return true;
			},
			addColumnBefore: () => ({ state, dispatch }) => {
				return addColumnBefore(state, dispatch);
			},
			addColumnAfter: () => ({ state, dispatch }) => {
				return addColumnAfter(state, dispatch);
			},
			deleteColumn: () => ({ state, dispatch }) => {
				return deleteColumn(state, dispatch);
			},
			addRowBefore: () => ({ state, dispatch }) => {
				return addRowBefore(state, dispatch);
			},
			addRowAfter: () => ({ state, dispatch }) => {
				return addRowAfter(state, dispatch);
			},
			deleteRow: () => ({ state, dispatch }) => {
				return deleteRow(state, dispatch);
			},
			deleteTable: () => ({ state, dispatch }) => {
				return deleteTable(state, dispatch);
			},
			mergeCells: () => ({ state, dispatch }) => {
				return mergeCells(state, dispatch);
			},
			splitCell: () => ({ state, dispatch }) => {
				return splitCell(state, dispatch);
			},
			toggleHeaderColumn: () => ({ state, dispatch }) => {
				return toggleHeader("column")(state, dispatch);
			},
			toggleHeaderRow: () => ({ state, dispatch }) => {
				return toggleHeader("row")(state, dispatch);
			},
			toggleHeaderCell: () => ({ state, dispatch }) => {
				return toggleHeaderCell(state, dispatch);
			},
			mergeOrSplit: () => ({ state, dispatch }) => {
				if (mergeCells(state, dispatch)) return true;
				return splitCell(state, dispatch);
			},
			setCellAttribute: (name, value) => ({ state, dispatch }) => {
				return setCellAttr(name, value)(state, dispatch);
			},
			goToNextCell: () => ({ state, dispatch }) => {
				return goToNextCell(1)(state, dispatch);
			},
			goToPreviousCell: () => ({ state, dispatch }) => {
				return goToNextCell(-1)(state, dispatch);
			},
			fixTables: () => ({ state, dispatch }) => {
				if (dispatch) fixTables(state);
				return true;
			},
			setCellSelection: (position) => ({ tr, dispatch }) => {
				if (dispatch) {
					const selection = CellSelection.create(tr.doc, position.anchorCell, position.headCell);
					tr.setSelection(selection);
				}
				return true;
			}
		};
	},
	addKeyboardShortcuts() {
		return {
			Tab: () => {
				if (this.editor.commands.goToNextCell()) return true;
				if (!this.editor.can().addRowAfter()) return false;
				return this.editor.chain().addRowAfter().goToNextCell().run();
			},
			"Shift-Tab": () => this.editor.commands.goToPreviousCell(),
			Backspace: deleteTableWhenAllCellsSelected,
			"Mod-Backspace": deleteTableWhenAllCellsSelected,
			Delete: deleteTableWhenAllCellsSelected,
			"Mod-Delete": deleteTableWhenAllCellsSelected
		};
	},
	addProseMirrorPlugins() {
		return [...this.options.resizable && this.editor.isEditable ? [columnResizing({
			handleWidth: this.options.handleWidth,
			cellMinWidth: this.options.cellMinWidth,
			defaultCellMinWidth: this.options.cellMinWidth,
			View: this.options.View,
			lastColumnResizable: this.options.lastColumnResizable
		})] : [], tableEditing({ allowTableNodeSelection: this.options.allowTableNodeSelection })];
	},
	extendNodeSchema(extension) {
		return { tableRole: callOrReturn(getExtensionField(extension, "tableRole", {
			name: extension.name,
			options: extension.options,
			storage: extension.storage
		})) };
	}
});
Extension.create({
	name: "tableKit",
	addExtensions() {
		const extensions = [];
		if (this.options.table !== false) extensions.push(Table.configure(this.options.table));
		if (this.options.tableCell !== false) extensions.push(TableCell.configure(this.options.tableCell));
		if (this.options.tableHeader !== false) extensions.push(TableHeader.configure(this.options.tableHeader));
		if (this.options.tableRow !== false) extensions.push(TableRow.configure(this.options.tableRow));
		return extensions;
	}
});
var index_default$4 = Node3.create({
	name: "text",
	group: "inline",
	parseMarkdown: (token) => {
		return {
			type: "text",
			text: token.text || ""
		};
	},
	renderMarkdown: (node) => node.text || ""
});
var index_default$3 = Extension.create({
	name: "textAlign",
	addOptions() {
		return {
			types: [],
			alignments: [
				"left",
				"center",
				"right",
				"justify"
			],
			defaultAlignment: null
		};
	},
	addGlobalAttributes() {
		return [{
			types: this.options.types,
			attributes: { textAlign: {
				default: this.options.defaultAlignment,
				parseHTML: (element) => {
					const alignment = element.style.textAlign;
					return this.options.alignments.includes(alignment) ? alignment : this.options.defaultAlignment;
				},
				renderHTML: (attributes) => {
					if (!attributes.textAlign) return {};
					return { style: `text-align: ${attributes.textAlign}` };
				}
			} }
		}];
	},
	addCommands() {
		return {
			setTextAlign: (alignment) => ({ commands }) => {
				if (!this.options.alignments.includes(alignment)) return false;
				return this.options.types.map((type) => commands.updateAttributes(type, { textAlign: alignment })).some((response) => response);
			},
			unsetTextAlign: () => ({ commands }) => {
				return this.options.types.map((type) => commands.resetAttributes(type, "textAlign")).some((response) => response);
			},
			toggleTextAlign: (alignment) => ({ editor, commands }) => {
				if (!this.options.alignments.includes(alignment)) return false;
				if (editor.isActive({ textAlign: alignment })) return commands.unsetTextAlign();
				return commands.setTextAlign(alignment);
			}
		};
	},
	addKeyboardShortcuts() {
		return {
			"Mod-Shift-l": () => this.editor.commands.setTextAlign("left"),
			"Mod-Shift-e": () => this.editor.commands.setTextAlign("center"),
			"Mod-Shift-r": () => this.editor.commands.setTextAlign("right"),
			"Mod-Shift-j": () => this.editor.commands.setTextAlign("justify")
		};
	}
});
//#endregion
//#region node_modules/@tiptap/extension-typography/dist/index.js
var emDash = (override) => textInputRule({
	find: /--$/,
	replace: override != null ? override : "—"
});
var ellipsis = (override) => textInputRule({
	find: /\.\.\.$/,
	replace: override != null ? override : "…"
});
var openDoubleQuote = (override) => textInputRule({
	find: /(?:^|[\s{[(<'"\u2018\u201C])(")$/,
	replace: override != null ? override : "“"
});
var closeDoubleQuote = (override) => textInputRule({
	find: /"$/,
	replace: override != null ? override : "”"
});
var openSingleQuote = (override) => textInputRule({
	find: /(?:^|[\s{[(<'"\u2018\u201C])(')$/,
	replace: override != null ? override : "‘"
});
var closeSingleQuote = (override) => textInputRule({
	find: /'$/,
	replace: override != null ? override : "’"
});
var leftArrow = (override) => textInputRule({
	find: /<-$/,
	replace: override != null ? override : "←"
});
var rightArrow = (override) => textInputRule({
	find: /->$/,
	replace: override != null ? override : "→"
});
var copyright = (override) => textInputRule({
	find: /\(c\)$/,
	replace: override != null ? override : "©"
});
var trademark = (override) => textInputRule({
	find: /\(tm\)$/,
	replace: override != null ? override : "™"
});
var servicemark = (override) => textInputRule({
	find: /\(sm\)$/,
	replace: override != null ? override : "℠"
});
var registeredTrademark = (override) => textInputRule({
	find: /\(r\)$/,
	replace: override != null ? override : "®"
});
var oneHalf = (override) => textInputRule({
	find: /(?:^|\s)(1\/2)\s$/,
	replace: override != null ? override : "½"
});
var plusMinus = (override) => textInputRule({
	find: /\+\/-$/,
	replace: override != null ? override : "±"
});
var notEqual = (override) => textInputRule({
	find: /!=$/,
	replace: override != null ? override : "≠"
});
var laquo = (override) => textInputRule({
	find: /<<$/,
	replace: override != null ? override : "«"
});
var raquo = (override) => textInputRule({
	find: />>$/,
	replace: override != null ? override : "»"
});
var multiplication = (override) => textInputRule({
	find: /\d+\s?([*x])\s?\d+$/,
	replace: override != null ? override : "×"
});
var superscriptTwo = (override) => textInputRule({
	find: /\^2$/,
	replace: override != null ? override : "²"
});
var superscriptThree = (override) => textInputRule({
	find: /\^3$/,
	replace: override != null ? override : "³"
});
var oneQuarter = (override) => textInputRule({
	find: /(?:^|\s)(1\/4)\s$/,
	replace: override != null ? override : "¼"
});
var threeQuarters = (override) => textInputRule({
	find: /(?:^|\s)(3\/4)\s$/,
	replace: override != null ? override : "¾"
});
var index_default$2 = Extension.create({
	name: "typography",
	addOptions() {
		return {
			closeDoubleQuote: "”",
			closeSingleQuote: "’",
			copyright: "©",
			ellipsis: "…",
			emDash: "—",
			laquo: "«",
			leftArrow: "←",
			multiplication: "×",
			notEqual: "≠",
			oneHalf: "½",
			oneQuarter: "¼",
			openDoubleQuote: "“",
			openSingleQuote: "‘",
			plusMinus: "±",
			raquo: "»",
			registeredTrademark: "®",
			rightArrow: "→",
			servicemark: "℠",
			superscriptThree: "³",
			superscriptTwo: "²",
			threeQuarters: "¾",
			trademark: "™"
		};
	},
	addInputRules() {
		var _a, _b;
		const rules = [];
		if (this.options.emDash !== false) rules.push(emDash(this.options.emDash));
		if (this.options.ellipsis !== false) rules.push(ellipsis(this.options.ellipsis));
		const isRTL = this.editor.options.textDirection === "rtl";
		if ((_a = this.options.doubleQuotes) == null ? void 0 : _a.rtl) {
			const { open, close } = this.options.doubleQuotes.rtl;
			rules.push(openDoubleQuote(open));
			rules.push(closeDoubleQuote(close));
		} else if (isRTL) {
			rules.push(openDoubleQuote("”"));
			rules.push(closeDoubleQuote("“"));
		} else {
			if (this.options.openDoubleQuote !== false) rules.push(openDoubleQuote(this.options.openDoubleQuote));
			if (this.options.closeDoubleQuote !== false) rules.push(closeDoubleQuote(this.options.closeDoubleQuote));
		}
		if ((_b = this.options.singleQuotes) == null ? void 0 : _b.rtl) {
			const { open, close } = this.options.singleQuotes.rtl;
			rules.push(openSingleQuote(open));
			rules.push(closeSingleQuote(close));
		} else if (isRTL) {
			rules.push(openSingleQuote("’"));
			rules.push(closeSingleQuote("‘"));
		} else {
			if (this.options.openSingleQuote !== false) rules.push(openSingleQuote(this.options.openSingleQuote));
			if (this.options.closeSingleQuote !== false) rules.push(closeSingleQuote(this.options.closeSingleQuote));
		}
		if (this.options.leftArrow !== false) rules.push(leftArrow(this.options.leftArrow));
		if (this.options.rightArrow !== false) rules.push(rightArrow(this.options.rightArrow));
		if (this.options.copyright !== false) rules.push(copyright(this.options.copyright));
		if (this.options.trademark !== false) rules.push(trademark(this.options.trademark));
		if (this.options.servicemark !== false) rules.push(servicemark(this.options.servicemark));
		if (this.options.registeredTrademark !== false) rules.push(registeredTrademark(this.options.registeredTrademark));
		if (this.options.oneHalf !== false) rules.push(oneHalf(this.options.oneHalf));
		if (this.options.plusMinus !== false) rules.push(plusMinus(this.options.plusMinus));
		if (this.options.notEqual !== false) rules.push(notEqual(this.options.notEqual));
		if (this.options.laquo !== false) rules.push(laquo(this.options.laquo));
		if (this.options.raquo !== false) rules.push(raquo(this.options.raquo));
		if (this.options.multiplication !== false) rules.push(multiplication(this.options.multiplication));
		if (this.options.superscriptTwo !== false) rules.push(superscriptTwo(this.options.superscriptTwo));
		if (this.options.superscriptThree !== false) rules.push(superscriptThree(this.options.superscriptThree));
		if (this.options.oneQuarter !== false) rules.push(oneQuarter(this.options.oneQuarter));
		if (this.options.threeQuarters !== false) rules.push(threeQuarters(this.options.threeQuarters));
		return rules;
	}
});
var index_default$1 = Mark.create({
	name: "underline",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [{ tag: "u" }, {
			style: "text-decoration",
			consuming: false,
			getAttrs: (style) => style.includes("underline") ? {} : false
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"u",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	parseMarkdown(token, helpers) {
		return helpers.applyMark(this.name || "underline", helpers.parseInline(token.tokens || []));
	},
	renderMarkdown(node, helpers) {
		return `++${helpers.renderChildren(node)}++`;
	},
	markdownTokenizer: {
		name: "underline",
		level: "inline",
		start(src) {
			return src.indexOf("++");
		},
		tokenize(src, _tokens, lexer) {
			const match = /^(\+\+)([\s\S]+?)(\+\+)/.exec(src);
			if (!match) return;
			const innerContent = match[2].trim();
			return {
				type: "underline",
				raw: match[0],
				text: innerContent,
				tokens: lexer.inlineTokens(innerContent)
			};
		}
	},
	addCommands() {
		return {
			setUnderline: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleUnderline: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetUnderline: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return {
			"Mod-u": () => this.editor.commands.toggleUnderline(),
			"Mod-U": () => this.editor.commands.toggleUnderline()
		};
	}
});
var index_default = Node3.create({
	name: "doc",
	topNode: true,
	content: "block+",
	renderMarkdown: (node, h) => {
		if (!node.content) return "";
		return h.renderChildren(node.content, "\n\n");
	}
});
//#endregion
//#region resources/js/components/fieldtypes/bard/Document.js
var DocumentBlock = index_default.extend({ content: "(block | root)+" });
var DocumentInline = index_default.extend({ content: "paragraph" });
//#endregion
//#region resources/js/components/fieldtypes/bard/Set.js
var Set$1 = Node3.create({
	name: "set",
	addNodeView() {
		return VueNodeViewRenderer(Set_default);
	},
	draggable: true,
	group: "root",
	addAttributes() {
		return {
			id: {
				default: null,
				parseHTML: (element) => element.querySelector("div")?.getAttribute("id")
			},
			enabled: {
				default: true,
				parseHTML: (element) => element.querySelector("div")?.getAttribute("enabled")
			},
			values: {
				default: null,
				parseHTML: (element) => element.querySelector("a")?.getAttribute("values")
			}
		};
	},
	parseHTML() {
		return [{
			tag: "bard-set",
			getAttrs: (dom) => JSON.parse(dom.innerHTML)
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"bard-set",
			{},
			JSON.stringify(HTMLAttributes)
		];
	},
	addCommands() {
		return {
			set: (attrs) => ({ tr, dispatch }) => {
				const { selection } = tr;
				const node = this.type.create(attrs);
				if (dispatch) dispatch(selection instanceof TextSelection ? tr.insert(selection.$cursor.pos - 1, node) : tr.insert(selection.$head.pos, node));
			},
			setAt: ({ attrs, pos }) => ({ tr, dispatch }) => {
				const node = this.type.create(attrs);
				if (dispatch) dispatch(tr.insert(pos, node));
			}
		};
	},
	addProseMirrorPlugins() {
		const bard = this.options.bard;
		const type = this.type;
		const sliceHasSetNodes = (slice) => {
			let found = false;
			slice.content.forEach((node) => {
				if (node.type === type) found = true;
			});
			return found;
		};
		return [
			new Plugin({
				key: new PluginKey("setBlockCharacterInput"),
				props: { handleKeyDown(view, event) {
					const { selection } = view.state;
					if (!(selection instanceof NodeSelection) || selection.node.type !== type) return false;
					const key = event.key;
					if ([
						"Backspace",
						"Delete",
						"Enter",
						"Escape",
						"Tab"
					].includes(key)) return false;
					if (key.length === 1) return true;
					return false;
				} }
			}),
			new Plugin({
				key: new PluginKey("setSelectionDecorator"),
				props: { decorations(state) {
					const { from, to } = state.selection;
					const decorations = [];
					state.doc.nodesBetween(from, to, (node, pos) => {
						if (node.type === type) decorations.push(Decoration.node(pos, pos + node.nodeSize, {}, { withinSelection: true }));
					});
					return DecorationSet.create(state.doc, decorations);
				} }
			}),
			new Plugin({
				key: new PluginKey("setPastedTransformer"),
				props: {
					handleDrop: (view, event, slice, moved) => {
						if (moved || !slice) return false;
						return sliceHasSetNodes(slice);
					},
					handlePaste: (view, event, slice) => {
						if (!sliceHasSetNodes(slice)) return false;
						(async () => {
							const content = [];
							for (const node of slice.content.content) if (node.type === type) {
								const newAttrs = await bard.pasteSet(node.attrs);
								content.push(node.type.create(newAttrs));
							} else content.push(node);
							const newSlice = new Slice(Fragment$1.fromArray(content), slice.openStart, slice.openEnd);
							const tr = view.state.tr.replaceSelection(newSlice);
							view.dispatch(tr);
						})();
						return true;
					}
				}
			})
		];
	}
});
//#endregion
//#region resources/js/components/fieldtypes/bard/Small.js
var Small = Mark.create({
	name: "small",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [{ tag: "small" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"small",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	addCommands() {
		return { toggleSmall: () => ({ commands }) => {
			return commands.toggleMark(this.name);
		} };
	}
});
//#endregion
//#region resources/js/components/fieldtypes/bard/Image.vue
var _sfc_main$3 = {
	mixins: [Asset_default],
	components: {
		NodeViewWrapper,
		Selector: Selector_default,
		Input: Input_default,
		Button: Button_default,
		Stack: Stack_default
	},
	inject: { publishContainer: { from: containerContextKey } },
	props: [
		"editor",
		"node",
		"decorations",
		"selected",
		"extension",
		"getPos",
		"updateAttributes",
		"deleteNode"
	],
	data() {
		return {
			assetId: null,
			assetAlt: null,
			editorAsset: null,
			showingSelector: false,
			loading: false,
			alt: this.node.attrs.alt,
			showingAltEdit: !!this.node.attrs.alt
		};
	},
	computed: {
		src() {
			if (this.editorAsset) return this.editorAsset.url;
		},
		actualSrc() {
			if (this.editorAsset) return `asset::${this.assetId}`;
			return this.src;
		},
		selections() {
			return this.assetId ? [this.assetId] : [];
		}
	},
	created() {
		let src = this.node.attrs.src;
		if (this.node.isNew) this.openSelector();
		if (src && src.startsWith("asset:")) this.assetId = src.substr(7);
		let id = this.assetId || src;
		if (id) this.loadAsset(id);
	},
	watch: {
		actualSrc(src) {
			if (!this.node.attrs.src) this.updateAttributes({
				src,
				asset: !!this.assetId
			});
		},
		alt(alt) {
			this.updateAttributes({ alt });
		},
		showingAltEdit(showingAltEdit) {
			if (showingAltEdit) this.$nextTick(() => this.$refs.alt.focus());
		}
	},
	methods: {
		openSelector() {
			this.showingSelector = true;
		},
		assetsSelected(selections) {
			this.loading = true;
			this.assetId = selections[0];
			this.loadAsset(this.assetId);
		},
		loadAsset(id) {
			const cache = this.extension.options.bard.assetsCache;
			if (cache[id]) {
				this.setAsset(cache[id]);
				return;
			}
			this.$axios.post(cp_url("assets-fieldtype"), { assets: [id] }).then((response) => {
				this.setAsset(response.data[0]);
			});
		},
		setAsset(asset) {
			this.extension.options.bard.assetsCache[asset.id] = asset;
			this.editorAsset = asset;
			this.assetId = asset.id;
			this.assetAlt = asset.values.alt;
			this.loading = false;
			this.updateAttributes({ src: this.actualSrc });
		},
		toggleAltEditor() {
			this.showingAltEdit = !this.showingAltEdit;
			if (!this.showingAltEdit) this.alt = null;
		},
		editorAssetSaved(asset) {
			this.setAsset(asset);
			this.closeEditor();
		}
	}
};
var _hoisted_1$3 = {
	key: 0,
	class: "p-2 text-center",
	draggable: "true",
	"data-drag-handle": ""
};
var _hoisted_2$2 = {
	ref: "content",
	hidden: ""
};
var _hoisted_3$2 = ["src"];
var _hoisted_4$2 = { class: "flex flex-wrap items-center justify-center gap-2 border-t px-2 py-2 text-center text-2xs text-white @container/toolbar dark:border-gray-900 dark:text-gray-300" };
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_Button = resolveComponent("Button");
	const _component_Input = resolveComponent("Input");
	const _component_selector = resolveComponent("selector");
	const _component_Stack = resolveComponent("Stack");
	const _component_asset_editor = resolveComponent("asset-editor");
	const _component_node_view_wrapper = resolveComponent("node-view-wrapper");
	return openBlock(), createBlock(_component_node_view_wrapper, null, {
		default: withCtx(() => [createBaseVNode("div", { class: normalizeClass(["bard-inline-image-container shadow-sm", { "border-blue-400": $props.selected }]) }, [
			$options.src ? (openBlock(), createElementBlock("div", _hoisted_1$3, [createBaseVNode("div", _hoisted_2$2, null, 512), createBaseVNode("img", {
				src: $options.src,
				class: "mx-auto block rounded-xs"
			}, null, 8, _hoisted_3$2)])) : createCommentVNode("", true),
			createBaseVNode("div", _hoisted_4$2, [
				!$options.src ? (openBlock(), createBlock(_component_Button, {
					key: 0,
					size: "sm",
					icon: "folder-photos",
					text: _ctx.__("Choose Image"),
					onMousedown: _cache[0] || (_cache[0] = withModifiers(() => {}, ["prevent"])),
					onClick: $options.openSelector
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				$options.src ? (openBlock(), createBlock(_component_Button, {
					key: 1,
					size: "sm",
					icon: "edit",
					text: _ctx.__("Edit Image"),
					onMousedown: _cache[1] || (_cache[1] = withModifiers(() => {}, ["prevent"])),
					onClick: _ctx.edit
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				$options.src ? (openBlock(), createBlock(_component_Button, {
					key: 2,
					size: "sm",
					icon: "rename",
					text: _ctx.__("Override Alt"),
					class: normalizeClass({ active: $data.showingAltEdit }),
					onMousedown: _cache[2] || (_cache[2] = withModifiers(() => {}, ["prevent"])),
					onClick: $options.toggleAltEditor
				}, null, 8, [
					"text",
					"class",
					"onClick"
				])) : createCommentVNode("", true),
				$options.src ? (openBlock(), createBlock(_component_Button, {
					key: 3,
					size: "sm",
					icon: "replace",
					text: _ctx.__("Replace"),
					onMousedown: _cache[3] || (_cache[3] = withModifiers(() => {}, ["prevent"])),
					onClick: $options.openSelector
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
				$options.src ? (openBlock(), createBlock(_component_Button, {
					key: 4,
					size: "sm",
					icon: "trash",
					text: _ctx.__("Remove"),
					onMousedown: _cache[4] || (_cache[4] = withModifiers(() => {}, ["prevent"])),
					onClick: $props.deleteNode
				}, null, 8, ["text", "onClick"])) : createCommentVNode("", true)
			]),
			$data.showingAltEdit ? (openBlock(), createElementBlock("div", {
				key: 1,
				class: "flex items-center rounded-b border-t p-2 dark:border-gray-900",
				onPaste: _cache[6] || (_cache[6] = withModifiers(() => {}, ["stop"]))
			}, [createVNode(_component_Input, {
				ref: "alt",
				name: "alt",
				modelValue: $data.alt,
				"onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => $data.alt = $event),
				placeholder: $data.assetAlt,
				prepend: _ctx.__("Alt Text"),
				class: "flex-1"
			}, null, 8, [
				"modelValue",
				"placeholder",
				"prepend"
			])], 32)) : createCommentVNode("", true),
			createVNode(_component_Stack, {
				open: $data.showingSelector,
				"onUpdate:open": _cache[8] || (_cache[8] = ($event) => $data.showingSelector = $event),
				inset: "",
				"show-close-button": false
			}, {
				default: withCtx(() => [createVNode(_component_selector, {
					container: $props.extension.options.bard.meta.assets.container,
					folder: $props.extension.options.bard.config.folder || "/",
					"restrict-folder-navigation": $props.extension.options.bard.config.restrict_assets,
					selected: $options.selections,
					"max-files": 1,
					columns: $props.extension.options.bard.meta.assets.columns,
					onSelected: $options.assetsSelected,
					onClosed: _cache[7] || (_cache[7] = ($event) => $data.showingSelector = false)
				}, null, 8, [
					"container",
					"folder",
					"restrict-folder-navigation",
					"selected",
					"columns",
					"onSelected"
				])]),
				_: 1
			}, 8, ["open"]),
			_ctx.editing ? (openBlock(), createBlock(_component_asset_editor, {
				key: 2,
				id: $data.assetId,
				showToolbar: false,
				"allow-deleting": false,
				"show-navigation": false,
				onClosed: _ctx.closeEditor,
				onSaved: $options.editorAssetSaved,
				onActionCompleted: _ctx.actionCompleted
			}, null, 8, [
				"id",
				"onClosed",
				"onSaved",
				"onActionCompleted"
			])) : createCommentVNode("", true)
		], 2)]),
		_: 1
	});
}
var Image_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$3, [["render", _sfc_render$3], ["__file", "Image.vue"]]);
//#endregion
//#region resources/js/components/fieldtypes/bard/Image.js
var Image = Node3.create({
	name: "image",
	addNodeView() {
		return VueNodeViewRenderer(Image_default);
	},
	inline: true,
	group: "inline",
	draggable: true,
	selectable: true,
	addAttributes() {
		return {
			src: {
				default: null,
				parseHTML: (element) => element.querySelector("img")?.getAttribute("data-src")
			},
			alt: {
				default: null,
				parseHTML: (element) => element.querySelector("img")?.getAttribute("alt")
			}
		};
	},
	parseHTML() {
		return [{
			tag: "img[src]",
			getAttrs: (dom) => {
				return {
					src: dom.getAttribute("data-src"),
					alt: dom.getAttribute("alt")
				};
			}
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return ["img", {
			...HTMLAttributes,
			src: "",
			"data-src": HTMLAttributes.src
		}];
	},
	addCommands() {
		return { insertImage: (attrs) => ({ tr, dispatch }) => {
			const { selection } = tr;
			const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos;
			const node = this.type.create(attrs);
			node.isNew = true;
			if (dispatch) dispatch(tr.insert(position, node));
		} };
	}
});
//#endregion
//#region resources/js/components/fieldtypes/bard/Link.js
var Link = Mark.create({
	name: "link",
	inclusive: false,
	addAttributes() {
		return {
			href: { default: null },
			rel: { default: null },
			target: { default: null },
			title: { default: null }
		};
	},
	parseHTML() {
		return [{ tag: "a[href]" }];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"a",
			HTMLAttributes,
			0
		];
	},
	addCommands() {
		return { setLink: (attributes) => ({ chain }) => {
			if (attributes.href) return chain().setMark(this.name, attributes).run();
			return chain().unsetMark(this.name, { extendEmptyMarkRange: true }).run();
		} };
	},
	addPasteRules() {
		return [markPasteRule({
			find: /https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-z]{2,6}\b(?:[-a-zA-Z0-9@:%_+.~#?&//=]*)/g,
			type: this.type,
			getAttributes: (url) => ({ href: url[0] })
		})];
	},
	addKeyboardShortcuts() {
		return { "Mod-k": () => {
			if (this.editor.state.selection.empty) return false;
			this.options.vm.events.emit("open-link-toolbar");
			return true;
		} };
	},
	addProseMirrorPlugins() {
		const vm = this.options.vm;
		return [new Plugin({
			key: new PluginKey("eventHandler"),
			props: { handleClick(view, pos) {
				const { schema, doc, tr } = view.state;
				const range = getMarkRange(doc.resolve(pos), schema.marks.link);
				if (range) {
					if (range.to === pos) return;
					const selection = new TextSelection(doc.resolve(range.from), doc.resolve(range.to));
					const transaction = tr.setSelection(selection);
					const attrs = getAttributes(view.state, schema.marks.link);
					view.dispatch(transaction);
					vm.events.emit("link-selected", attrs);
				} else vm.events.emit("link-deselected");
			} }
		})];
	}
});
//#endregion
//#region resources/js/components/fieldtypes/bard/LinkToolbar.vue
var import_lib = /* @__PURE__ */ __toESM(require_lib());
var _sfc_main$2 = {
	emits: [
		"updated",
		"canceled",
		"deselected"
	],
	components: {
		AssetSelector: Selector_default,
		Icon: Icon_default,
		Stack: Stack_default,
		StackContent: Content_default,
		StackFooter: Footer_default
	},
	props: {
		bard: {},
		config: Object,
		linkAttrs: Object
	},
	data() {
		return {
			linkType: "url",
			linkTypes: [
				{
					type: "url",
					title: __("URL")
				},
				{
					type: "entry",
					title: __("Entry")
				},
				{
					type: "asset",
					title: __("Asset")
				},
				{
					type: "mailto",
					title: __("Email")
				},
				{
					type: "tel",
					title: __("Phone")
				}
			],
			url: {},
			urlData: {},
			itemData: {},
			appends: null,
			title: null,
			rel: null,
			targetBlank: false,
			showAssetSelector: false,
			isLoading: false
		};
	},
	computed: {
		visibleLinkTypes() {
			return this.linkTypes.filter((type) => {
				if (type.type === "asset" && !this.config.container) return false;
				return true;
			});
		},
		displayValue() {
			switch (this.linkType) {
				case "url": return this.url.url;
				case "entry": return this.itemData.entry ? this.itemData.entry.title : null;
				case "asset": return this.itemData.asset ? this.itemData.asset.basename : null;
				case "mailto": return this.urlData.mailto ? this.urlData.mailto : null;
				case "tel": return this.urlData.tel ? this.urlData.tel : null;
			}
		},
		canCommit() {
			return !!this.url[this.linkType];
		},
		href() {
			return this.sanitizeLink(this.url[this.linkType]);
		},
		normalizedAppends() {
			const value = this.appends;
			if (!value) return "";
			if (value.startsWith("?") || value.startsWith("#")) return value;
			return value.includes("=") ? `?${value}` : `#${value}`;
		},
		defaultRel() {
			let rel = [];
			if (this.config.link_noopener) rel.push("noopener");
			if (this.config.link_noreferrer) rel.push("noreferrer");
			return rel.length ? rel.join(" ") : null;
		},
		relationshipConfig() {
			return {
				type: "entries",
				collections: this.collections,
				max_items: 1,
				select_across_sites: this.config.select_across_sites
			};
		},
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
			return utf8btoa(JSON.stringify(this.relationshipConfig));
		},
		collections() {
			return this.bard.meta.linkCollections;
		},
		canHaveTarget() {
			return [
				"url",
				"entry",
				"asset"
			].includes(this.linkType);
		},
		selectedTextIsEmail() {
			const { view, state } = this.bard.editor;
			const { from, to } = view.state.selection;
			return state.doc.textBetween(from, to, "").match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/);
		}
	},
	watch: {
		linkType(type) {
			if (type != "entry") this.appends = null;
			this.autofocus();
		},
		urlData: {
			deep: true,
			handler() {
				if (!["mailto", "tel"].includes(this.linkType)) return;
				this.setUrl(this.linkType, this.urlData[this.linkType] ? `${this.linkType}:${this.urlData[this.linkType]}` : null);
			}
		}
	},
	created() {
		this.applyAttrs(this.linkAttrs);
		this.bard.events.on("link-selected", this.applyAttrs);
		this.bard.events.on("link-deselected", () => this.$emit("deselected"));
		if (Object.keys(this.linkAttrs).length === 0 && this.selectedTextIsEmail) {
			this.linkType = "mailto";
			this.urlData = { mailto: this.selectedTextIsEmail };
		}
	},
	mounted() {
		this.autofocus();
	},
	beforeUnmount() {
		this.bard.events.off("link-selected");
		this.bard.events.off("link-deselected");
	},
	methods: {
		applyAttrs(attrs) {
			this.linkType = this.getLinkTypeForUrl(attrs.href);
			this.appends = this.getAppendsForUrl(attrs.href);
			this.url = { [this.linkType]: this.appends ? attrs.href?.replace(this.appends, "") : attrs.href };
			this.urlData = { [this.linkType]: this.getUrlDataForUrl(attrs.href) };
			this.itemData = { [this.linkType]: this.getItemDataForUrl(attrs.href) };
			this.title = attrs.title;
			this.rel = attrs.href ? attrs.rel : this.defaultRel;
			this.targetBlank = attrs.href ? attrs.target === "_blank" : this.config.target_blank || false;
		},
		autofocus() {
			if (this.linkType === "url") this.$nextTick(() => {
				setTimeout(() => {
					this.$refs.urlInput.focus();
				}, 50);
			});
		},
		setUrl(type, url) {
			this.url = {
				...this.url,
				[type]: url
			};
		},
		setItemData(type, itemData) {
			this.itemData = {
				...this.itemData,
				[type]: itemData
			};
		},
		remove() {
			this.$emit("updated", { href: null });
		},
		commit() {
			if (!this.href) return this.remove();
			this.$emit("updated", {
				href: this.href + this.normalizedAppends,
				rel: this.rel,
				target: this.canHaveTarget && this.targetBlank ? "_blank" : null,
				title: this.title
			});
		},
		sanitizeLink(link) {
			const str = link.trim();
			return str.match(/^\w[\w\-_\.]+\.(co|uk|com|org|net|gov|biz|info|us|eu|de|fr|it|es|pl|nz)/i) ? `https://${str}` : str;
		},
		openSelector() {
			if (this.linkType === "entry") this.openEntrySelector();
			else if (this.linkType === "asset") this.openAssetSelector();
		},
		openEntrySelector() {
			this.$refs.relationshipInput.openSelector();
		},
		openAssetSelector() {
			this.showAssetSelector = true;
		},
		assetSelected(data) {
			if (data.length) this.loadAssetData(data[0]);
		},
		loadAssetData(url) {
			this.$axios.post(cp_url("assets-fieldtype"), { assets: [url] }).then((response) => {
				this.selectItem("asset", response.data[0]);
				this.isLoading = false;
			});
		},
		entrySelected(data) {
			if (data.length) this.selectItem("entry", data[0]);
		},
		selectItem(type, item) {
			const ref = `${type}::${item.id}`;
			this.setItemData(type, item);
			this.setUrl(type, `statamic://${ref}`);
			this.putItemDataIntoMeta(ref, item);
		},
		putItemDataIntoMeta(ref, item) {
			let meta = this.bard.meta;
			meta.linkData[ref] = item;
			this.bard.updateMeta(meta);
		},
		getLinkTypeForUrl(url) {
			const { type } = this.parseDataUrl(url);
			if (type) return type;
			const matches = url ? url.match(/^(mailto|tel):(.*)$/) : null;
			if (matches) return matches[1];
			return "url";
		},
		getUrlDataForUrl(url) {
			const matches = url ? url.match(/^(mailto|tel):(.*)$/) : null;
			if (!matches) return null;
			return matches[2];
		},
		getItemDataForUrl(url) {
			const { ref } = this.parseDataUrl(url);
			if (!ref) return null;
			return this.bard.meta.linkData[ref];
		},
		getAppendsForUrl(urlString) {
			if (!urlString?.includes("statamic://entry::")) return null;
			return urlString.replace(urlString.split(/[?#]/)[0], "") || null;
		},
		parseDataUrl(url) {
			if (!url) return {};
			const appends = this.getAppendsForUrl(url);
			const matches = (appends ? url.replace(appends, "") : url).match(/^statamic:\/\/((.*?)::(.*))$/);
			if (!matches) return {};
			const [_, ref, type, id] = matches;
			return {
				ref,
				type,
				id
			};
		}
	}
};
var _hoisted_1$2 = { class: "flex gap-3 items-center" };
var _hoisted_2$1 = { class: "flex-1 min-w-0" };
var _hoisted_3$1 = {
	key: 1,
	class: "flex flex-1 items-center me-2 overflow-hidden min-w-0"
};
var _hoisted_4$1 = ["src"];
var _hoisted_5$1 = { class: "truncate min-w-0 flex-1" };
var _hoisted_6$1 = ["aria-label"];
var _hoisted_7$1 = { class: "space-y-5" };
var _hoisted_8$1 = { class: "flex items-center gap-2" };
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_select = resolveComponent("ui-select");
	const _component_ui_input = resolveComponent("ui-input");
	const _component_Icon = resolveComponent("Icon");
	const _component_ui_separator = resolveComponent("ui-separator");
	const _component_ui_switch = resolveComponent("ui-switch");
	const _component_ui_description = resolveComponent("ui-description");
	const _component_relationship_input = resolveComponent("relationship-input");
	const _component_asset_selector = resolveComponent("asset-selector");
	const _component_Stack = resolveComponent("Stack");
	const _component_StackContent = resolveComponent("StackContent");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_StackFooter = resolveComponent("StackFooter");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [createVNode(_component_StackContent, { class: "space-y-5" }, {
		default: withCtx(() => [
			createBaseVNode("section", _hoisted_1$2, [createVNode(_component_ui_select, {
				modelValue: $data.linkType,
				"onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.linkType = $event),
				options: $options.visibleLinkTypes,
				"option-label": "title",
				"option-value": "type",
				class: "w-1/4 min-w-24"
			}, null, 8, ["modelValue", "options"]), createBaseVNode("div", _hoisted_2$1, [$data.linkType === "url" ? (openBlock(), createBlock(_component_ui_input, {
				key: 0,
				modelValue: $data.url.url,
				"onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.url.url = $event),
				type: "text",
				ref: "urlInput",
				autofocus: "",
				placeholder: "https://",
				onKeydown: withKeys(withModifiers($options.commit, ["prevent"]), ["enter"])
			}, null, 8, ["modelValue", "onKeydown"])) : $data.linkType === "mailto" ? (openBlock(), createBlock(_component_ui_input, {
				key: 1,
				modelValue: $data.urlData.mailto,
				"onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $data.urlData.mailto = $event),
				type: "text",
				ref: "mailtoInput",
				placeholder: _ctx.__("Email Address"),
				onKeydown: withKeys(withModifiers($options.commit, ["prevent"]), ["enter"])
			}, null, 8, [
				"modelValue",
				"placeholder",
				"onKeydown"
			])) : $data.linkType === "tel" ? (openBlock(), createBlock(_component_ui_input, {
				key: 2,
				modelValue: $data.urlData.tel,
				"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $data.urlData.tel = $event),
				ref: "telInput",
				placeholder: _ctx.__("Phone Number"),
				onKeydown: withKeys(withModifiers($options.commit, ["prevent"]), ["enter"])
			}, null, 8, [
				"modelValue",
				"placeholder",
				"onKeydown"
			])) : (openBlock(), createElementBlock("div", {
				key: 3,
				class: normalizeClass([
					"flex overflow-hidden cursor-pointer items-center justify-between",
					"w-full block bg-white dark:bg-gray-900 min-w-0",
					"border border-gray-300 with-contrast:border-gray-500 dark:border-gray-700 dark:with-contrast:border-gray-500 dark:inset-shadow-2xs dark:inset-shadow-black",
					"text-gray-925 dark:text-gray-300 placeholder:text-gray-500 dark:placeholder:text-gray-400/85",
					"appearance-none antialiased shadow-ui-sm disabled:shadow-none disabled:opacity-50 not-prose",
					"text-sm rounded-lg px-2.5 py-1.5 h-10 leading-[1.125rem]"
				]),
				onClick: _cache[5] || (_cache[5] = (...args) => $options.openSelector && $options.openSelector(...args))
			}, [$data.isLoading ? (openBlock(), createBlock(_component_Icon, {
				key: 0,
				name: "loading"
			})) : (openBlock(), createElementBlock("div", _hoisted_3$1, [$data.linkType === "asset" && $data.itemData.asset && $data.itemData.asset.isImage ? (openBlock(), createElementBlock("img", {
				key: 0,
				src: $data.itemData.asset.thumbnail || $data.itemData.asset.url,
				class: "asset-thumbnail lazyloaded size-6 max-h-full max-w-full rounded-sm object-cover me-2 flex-shrink-0"
			}, null, 8, _hoisted_4$1)) : createCommentVNode("", true), createBaseVNode("div", _hoisted_5$1, toDisplayString($options.displayValue || _ctx.__("Choose item...")), 1)])), withDirectives((openBlock(), createElementBlock("button", {
				class: "flex items-center cursor-pointer",
				"aria-label": `${_ctx.__("Browse")}...`,
				onClick: _cache[4] || (_cache[4] = (...args) => $options.openSelector && $options.openSelector(...args))
			}, [withDirectives(createVNode(_component_Icon, {
				name: "folder-photos",
				class: "size-4"
			}, null, 512), [[vShow, $data.linkType === "asset"]]), withDirectives(createVNode(_component_Icon, {
				name: "folder",
				class: "size-4"
			}, null, 512), [[vShow, $data.linkType !== "asset"]])], 8, _hoisted_6$1)), [[_directive_tooltip, `${_ctx.__("Browse")}...`]])]))])]),
			createVNode(_component_ui_separator, { text: _ctx.__("Advanced Options") }, null, 8, ["text"]),
			createBaseVNode("section", _hoisted_7$1, [
				$data.linkType === "entry" ? (openBlock(), createBlock(_component_ui_input, {
					key: 0,
					type: "text",
					modelValue: $data.appends,
					"onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => $data.appends = $event),
					prepend: _ctx.__("Append"),
					placeholder: _ctx.__("?query=params#anchor")
				}, null, 8, [
					"modelValue",
					"prepend",
					"placeholder"
				])) : createCommentVNode("", true),
				createVNode(_component_ui_input, {
					type: "text",
					ref: "input",
					modelValue: $data.title,
					"onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => $data.title = $event),
					prepend: _ctx.__("Label"),
					placeholder: _ctx.__("Add a link label")
				}, null, 8, [
					"modelValue",
					"prepend",
					"placeholder"
				]),
				createVNode(_component_ui_input, {
					type: "text",
					ref: "input",
					modelValue: $data.rel,
					"onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => $data.rel = $event),
					prepend: _ctx.__("Rel"),
					placeholder: _ctx.__("noopener, noreferrer")
				}, null, 8, [
					"modelValue",
					"prepend",
					"placeholder"
				]),
				createBaseVNode("div", _hoisted_8$1, [createVNode(_component_ui_switch, {
					modelValue: $data.targetBlank,
					"onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => $data.targetBlank = $event)
				}, null, 8, ["modelValue"]), createVNode(_component_ui_description, { text: _ctx.__("Open in new window") }, null, 8, ["text"])])
			]),
			createVNode(_component_relationship_input, {
				class: "hidden",
				ref: "relationshipInput",
				name: "link",
				value: [],
				config: $options.relationshipConfig,
				"item-data-url": $options.itemDataUrl,
				"selections-url": $options.selectionsUrl,
				"filters-url": $options.filtersUrl,
				columns: [{
					label: _ctx.__("Title"),
					field: "title"
				}],
				"max-items": 1,
				site: $props.bard.site,
				search: true,
				onLoading: _cache[10] || (_cache[10] = ($event) => $data.isLoading = $event),
				onItemDataUpdated: $options.entrySelected
			}, null, 8, [
				"config",
				"item-data-url",
				"selections-url",
				"filters-url",
				"columns",
				"site",
				"onItemDataUpdated"
			]),
			createVNode(_component_Stack, {
				open: $data.showAssetSelector,
				"onUpdate:open": _cache[12] || (_cache[12] = ($event) => $data.showAssetSelector = $event),
				inset: "",
				"show-close-button": false
			}, {
				default: withCtx(() => [createVNode(_component_asset_selector, {
					container: { id: $props.config.container },
					folder: $props.config.folder || "/",
					"restrict-folder-navigation": $props.config.restrict_assets,
					selected: [],
					"max-files": 1,
					columns: $props.bard.meta.assets.columns,
					onSelected: $options.assetSelected,
					onClosed: _cache[11] || (_cache[11] = ($event) => $data.showAssetSelector = false)
				}, null, 8, [
					"container",
					"folder",
					"restrict-folder-navigation",
					"columns",
					"onSelected"
				])]),
				_: 1
			}, 8, ["open"])
		]),
		_: 1
	}), createVNode(_component_StackFooter, null, {
		end: withCtx(() => [
			createVNode(_component_ui_button, {
				onClick: _cache[13] || (_cache[13] = ($event) => _ctx.$emit("canceled")),
				text: _ctx.__("Cancel"),
				variant: "ghost"
			}, null, 8, ["text"]),
			createVNode(_component_ui_button, {
				text: _ctx.__("Remove Link"),
				onClick: $options.remove
			}, null, 8, ["text", "onClick"]),
			createVNode(_component_ui_button, {
				text: _ctx.__("Apply Link"),
				disabled: !$options.canCommit,
				onClick: $options.commit,
				variant: "primary"
			}, null, 8, [
				"text",
				"disabled",
				"onClick"
			])
		]),
		_: 1
	})], 64);
}
//#endregion
//#region resources/js/components/fieldtypes/bard/LinkToolbarButton.vue
var _sfc_main$1 = {
	mixins: [ToolbarButton_default],
	components: {
		LinkToolbar: /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "LinkToolbar.vue"]]),
		Stack: Stack_default
	},
	data() {
		return {
			linkAttrs: null,
			showingToolbar: false
		};
	},
	methods: {
		close() {
			this.showingToolbar = false;
		},
		setLink(attributes) {
			this.editor.chain().focus().setLink(attributes).run();
			this.linkAttrs = null;
			this.close();
		}
	},
	watch: { showingToolbar(showingToolbar) {
		if (showingToolbar) this.linkAttrs = this.editor.getAttributes("link");
		else {
			this.editor.commands.focus();
			this.linkAttrs = null;
		}
	} },
	created() {
		this.bard.events.on("open-link-toolbar", () => this.showingToolbar = true);
	},
	beforeUnmount() {
		this.bard.events.off("open-link-toolbar");
	}
};
var _hoisted_1$1 = ["innerHTML"];
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_Button = resolveComponent("Button");
	const _component_link_toolbar = resolveComponent("link-toolbar");
	const _component_Stack = resolveComponent("Stack");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock(_component_Stack, {
		title: _ctx.__("Link"),
		size: "narrow",
		inset: "",
		"wrap-slot": false,
		open: $data.showingToolbar,
		"onUpdate:open": _cache[1] || (_cache[1] = ($event) => $data.showingToolbar = $event)
	}, {
		trigger: withCtx(() => [withDirectives((openBlock(), createBlock(_component_Button, {
			class: normalizeClass(["px-2!", { active: _ctx.active }]),
			variant: "ghost",
			size: "sm",
			"aria-label": _ctx.button.text,
			onMousedown: _cache[0] || (_cache[0] = withModifiers(() => {}, ["prevent"]))
		}, {
			default: withCtx(() => [_ctx.button.svg ? (openBlock(), createBlock(_component_ui_icon, {
				key: 0,
				name: _ctx.button.svg,
				class: "size-4"
			}, null, 8, ["name"])) : createCommentVNode("", true), _ctx.button.html ? (openBlock(), createElementBlock("div", {
				key: 1,
				class: "flex items-center",
				innerHTML: _ctx.button.html
			}, null, 8, _hoisted_1$1)) : createCommentVNode("", true)]),
			_: 1
		}, 8, ["class", "aria-label"])), [[_directive_tooltip, _ctx.button.text]])]),
		default: withCtx(() => [$data.linkAttrs !== null ? (openBlock(), createBlock(_component_link_toolbar, {
			key: 0,
			ref: "toolbar",
			"link-attrs": $data.linkAttrs,
			config: _ctx.config,
			bard: _ctx.bard,
			onUpdated: $options.setLink,
			onCanceled: $options.close
		}, null, 8, [
			"link-attrs",
			"config",
			"bard",
			"onUpdated",
			"onCanceled"
		])) : createCommentVNode("", true)]),
		_: 1
	}, 8, ["title", "open"]);
}
var LinkToolbarButton_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "LinkToolbarButton.vue"]]);
//#endregion
//#region node_modules/read-time-estimate/dist/read-time-estimate.js
var require_read_time_estimate = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	(function(global, factory) {
		typeof exports === "object" && typeof module !== "undefined" ? module.exports = factory() : typeof define === "function" && define.amd ? define(factory) : (global = global || self, global.speedometer = factory());
	})(exports, function() {
		"use strict";
		const WORDS_PER_MIN = 275;
		const IMAGE_READ_TIME = 12;
		const CHINESE_KOREAN_READ_TIME = 500;
		const IMAGE_TAGS = ["img", "Image"];
		/**
		*  String#imageReadTime() -> { time, count }
		*
		*  Get Image Read Time from a string.
		*
		* */
		function imageCount(imageTags, string) {
			const pattern = `<(${imageTags.join("|")})([\\w\\W]+?)[\\/]?>`;
			const reg = new RegExp(pattern, "g");
			return (string.match(reg) || []).length;
		}
		function imageReadTime(customImageTime = IMAGE_READ_TIME, tags = IMAGE_TAGS, string) {
			let seconds = 0;
			const count = imageCount(tags, string);
			if (count > 10) seconds = count / 2 * (customImageTime + 3) + (count - 10) * 3;
			else seconds = count / 2 * (2 * customImageTime + (1 - count));
			return {
				time: seconds / 60,
				count
			};
		}
		/**
		*  String#wordsReadTime() -> { characterTime, otherLanguageTime, wordTime, wordCount }
		*
		*  Get Words count from a string.
		*
		* */
		function wordsCount(string) {
			const reg = /* @__PURE__ */ new RegExp("\\w+", "g");
			return (string.match(reg) || []).length;
		}
		function otherLanguageReadTime(string) {
			const reg = /* @__PURE__ */ new RegExp("[぀-ヿ㐀-䶿一-鿿豈-﫿ｦ-ﾟ]", "g");
			const count = (string.match(reg) || []).length;
			return {
				count,
				time: count / CHINESE_KOREAN_READ_TIME,
				formattedString: string.replace(reg, "")
			};
		}
		function wordsReadTime(string, wordsPerMin = WORDS_PER_MIN) {
			const { count: characterCount, time: otherLanguageTime, formattedString } = otherLanguageReadTime(string);
			const wordCount = wordsCount(formattedString);
			return {
				characterCount,
				otherLanguageTime,
				wordTime: wordCount / wordsPerMin,
				wordCount
			};
		}
		/**
		*  String#stripTags() -> String
		*
		*  Strip HTML tags string.
		*
		* */
		function stripTags(string) {
			const reg = /* @__PURE__ */ new RegExp("<\\w+(\\s+(\"[^\"]*\"|\\'[^\\']*'|[^>])+)?>|<\\/\\w+>", "gi");
			return string.replace(reg, "");
		}
		/**
		*  String#stripWhitespace() -> String
		*
		*  Strip HTML tags string.
		*
		* */
		function stripWhitespace(string) {
			return string.replace(/^\s+/, "").replace(/\s+$/, "");
		}
		/**
		*  String#humanizeTime() -> String
		*
		*  Convert time(in minutes) to a humanized string.
		*
		* */
		function humanizeTime(time) {
			if (time < .5) return "less than a minute";
			if (time >= .5 && time < 1.5) return "1 minute";
			return `${Math.ceil(time)} minutes`;
		}
		function readTime(string, customWordTime, customImageTime, chineseKoreanReadTime, imageTags) {
			const { time: imageTime, count: imageCount$$1 } = imageReadTime(customImageTime, imageTags, string);
			const { characterCount, otherLanguageTime, wordTime, wordCount } = wordsReadTime(stripTags(stripWhitespace(string)), customWordTime);
			return {
				humanizedDuration: humanizeTime(imageTime + wordTime),
				duration: imageTime + wordTime,
				totalWords: wordCount,
				wordTime,
				totalImages: imageCount$$1,
				imageTime,
				otherLanguageTimeCharacters: characterCount,
				otherLanguageTime
			};
		}
		return readTime;
	});
}));
//#endregion
//#region node_modules/highlight.js/es/languages/arduino.js
/** @type LanguageFn */
function cPlusPlus(hljs) {
	const regex = hljs.regex;
	const C_LINE_COMMENT_MODE = hljs.COMMENT("//", "$", { contains: [{ begin: /\\\n/ }] });
	const DECLTYPE_AUTO_RE = "decltype\\(auto\\)";
	const NAMESPACE_RE = "[a-zA-Z_]\\w*::";
	const FUNCTION_TYPE_RE = "(?!struct)(decltype\\(auto\\)|" + regex.optional(NAMESPACE_RE) + "[a-zA-Z_]\\w*" + regex.optional("<[^<>]+>") + ")";
	const CPP_PRIMITIVE_TYPES = {
		className: "type",
		begin: "\\b[a-z\\d_]*_t\\b"
	};
	const STRINGS = {
		className: "string",
		variants: [
			{
				begin: "(u8?|U|L)?\"",
				end: "\"",
				illegal: "\\n",
				contains: [hljs.BACKSLASH_ESCAPE]
			},
			{
				begin: "(u8?|U|L)?'(\\\\(x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4,8}|[0-7]{3}|\\S)|.)",
				end: "'",
				illegal: "."
			},
			hljs.END_SAME_AS_BEGIN({
				begin: /(?:u8?|U|L)?R"([^()\\ ]{0,16})\(/,
				end: /\)([^()\\ ]{0,16})"/
			})
		]
	};
	const NUMBERS = {
		className: "number",
		variants: [{ begin: "[+-]?(?:(?:[0-9](?:'?[0-9])*\\.(?:[0-9](?:'?[0-9])*)?|\\.[0-9](?:'?[0-9])*)(?:[Ee][+-]?[0-9](?:'?[0-9])*)?|[0-9](?:'?[0-9])*[Ee][+-]?[0-9](?:'?[0-9])*|0[Xx](?:[0-9A-Fa-f](?:'?[0-9A-Fa-f])*(?:\\.(?:[0-9A-Fa-f](?:'?[0-9A-Fa-f])*)?)?|\\.[0-9A-Fa-f](?:'?[0-9A-Fa-f])*)[Pp][+-]?[0-9](?:'?[0-9])*)(?:[Ff](?:16|32|64|128)?|(BF|bf)16|[Ll]|)" }, { begin: "[+-]?\\b(?:0[Bb][01](?:'?[01])*|0[Xx][0-9A-Fa-f](?:'?[0-9A-Fa-f])*|0(?:'?[0-7])*|[1-9](?:'?[0-9])*)(?:[Uu](?:LL?|ll?)|[Uu][Zz]?|(?:LL?|ll?)[Uu]?|[Zz][Uu]|)" }],
		relevance: 0
	};
	const PREPROCESSOR = {
		className: "meta",
		begin: /#\s*[a-z]+\b/,
		end: /$/,
		keywords: { keyword: "if else elif endif define undef warning error line pragma _Pragma ifdef ifndef include" },
		contains: [
			{
				begin: /\\\n/,
				relevance: 0
			},
			hljs.inherit(STRINGS, { className: "string" }),
			{
				className: "string",
				begin: /<.*?>/
			},
			C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE
		]
	};
	const TITLE_MODE = {
		className: "title",
		begin: regex.optional(NAMESPACE_RE) + hljs.IDENT_RE,
		relevance: 0
	};
	const FUNCTION_TITLE = regex.optional(NAMESPACE_RE) + hljs.IDENT_RE + "\\s*\\(";
	const RESERVED_KEYWORDS = [
		"alignas",
		"alignof",
		"and",
		"and_eq",
		"asm",
		"atomic_cancel",
		"atomic_commit",
		"atomic_noexcept",
		"auto",
		"bitand",
		"bitor",
		"break",
		"case",
		"catch",
		"class",
		"co_await",
		"co_return",
		"co_yield",
		"compl",
		"concept",
		"const_cast|10",
		"consteval",
		"constexpr",
		"constinit",
		"continue",
		"decltype",
		"default",
		"delete",
		"do",
		"dynamic_cast|10",
		"else",
		"enum",
		"explicit",
		"export",
		"extern",
		"false",
		"final",
		"for",
		"friend",
		"goto",
		"if",
		"import",
		"inline",
		"module",
		"mutable",
		"namespace",
		"new",
		"noexcept",
		"not",
		"not_eq",
		"nullptr",
		"operator",
		"or",
		"or_eq",
		"override",
		"private",
		"protected",
		"public",
		"reflexpr",
		"register",
		"reinterpret_cast|10",
		"requires",
		"return",
		"sizeof",
		"static_assert",
		"static_cast|10",
		"struct",
		"switch",
		"synchronized",
		"template",
		"this",
		"thread_local",
		"throw",
		"transaction_safe",
		"transaction_safe_dynamic",
		"true",
		"try",
		"typedef",
		"typeid",
		"typename",
		"union",
		"using",
		"virtual",
		"volatile",
		"while",
		"xor",
		"xor_eq"
	];
	const RESERVED_TYPES = [
		"bool",
		"char",
		"char16_t",
		"char32_t",
		"char8_t",
		"double",
		"float",
		"int",
		"long",
		"short",
		"void",
		"wchar_t",
		"unsigned",
		"signed",
		"const",
		"static"
	];
	const TYPE_HINTS = [
		"any",
		"auto_ptr",
		"barrier",
		"binary_semaphore",
		"bitset",
		"complex",
		"condition_variable",
		"condition_variable_any",
		"counting_semaphore",
		"deque",
		"false_type",
		"flat_map",
		"flat_set",
		"future",
		"imaginary",
		"initializer_list",
		"istringstream",
		"jthread",
		"latch",
		"lock_guard",
		"multimap",
		"multiset",
		"mutex",
		"optional",
		"ostringstream",
		"packaged_task",
		"pair",
		"promise",
		"priority_queue",
		"queue",
		"recursive_mutex",
		"recursive_timed_mutex",
		"scoped_lock",
		"set",
		"shared_future",
		"shared_lock",
		"shared_mutex",
		"shared_timed_mutex",
		"shared_ptr",
		"stack",
		"string_view",
		"stringstream",
		"timed_mutex",
		"thread",
		"true_type",
		"tuple",
		"unique_lock",
		"unique_ptr",
		"unordered_map",
		"unordered_multimap",
		"unordered_multiset",
		"unordered_set",
		"variant",
		"vector",
		"weak_ptr",
		"wstring",
		"wstring_view"
	];
	const FUNCTION_HINTS = [
		"abort",
		"abs",
		"acos",
		"apply",
		"as_const",
		"asin",
		"atan",
		"atan2",
		"calloc",
		"ceil",
		"cerr",
		"cin",
		"clog",
		"cos",
		"cosh",
		"cout",
		"declval",
		"endl",
		"exchange",
		"exit",
		"exp",
		"fabs",
		"floor",
		"fmod",
		"forward",
		"fprintf",
		"fputs",
		"free",
		"frexp",
		"fscanf",
		"future",
		"invoke",
		"isalnum",
		"isalpha",
		"iscntrl",
		"isdigit",
		"isgraph",
		"islower",
		"isprint",
		"ispunct",
		"isspace",
		"isupper",
		"isxdigit",
		"labs",
		"launder",
		"ldexp",
		"log",
		"log10",
		"make_pair",
		"make_shared",
		"make_shared_for_overwrite",
		"make_tuple",
		"make_unique",
		"malloc",
		"memchr",
		"memcmp",
		"memcpy",
		"memset",
		"modf",
		"move",
		"pow",
		"printf",
		"putchar",
		"puts",
		"realloc",
		"scanf",
		"sin",
		"sinh",
		"snprintf",
		"sprintf",
		"sqrt",
		"sscanf",
		"std",
		"stderr",
		"stdin",
		"stdout",
		"strcat",
		"strchr",
		"strcmp",
		"strcpy",
		"strcspn",
		"strlen",
		"strncat",
		"strncmp",
		"strncpy",
		"strpbrk",
		"strrchr",
		"strspn",
		"strstr",
		"swap",
		"tan",
		"tanh",
		"terminate",
		"to_underlying",
		"tolower",
		"toupper",
		"vfprintf",
		"visit",
		"vprintf",
		"vsprintf"
	];
	const CPP_KEYWORDS = {
		type: RESERVED_TYPES,
		keyword: RESERVED_KEYWORDS,
		literal: [
			"NULL",
			"false",
			"nullopt",
			"nullptr",
			"true"
		],
		built_in: ["_Pragma"],
		_type_hints: TYPE_HINTS
	};
	const FUNCTION_DISPATCH = {
		className: "function.dispatch",
		relevance: 0,
		keywords: { _hint: FUNCTION_HINTS },
		begin: regex.concat(/\b/, /(?!decltype)/, /(?!if)/, /(?!for)/, /(?!switch)/, /(?!while)/, hljs.IDENT_RE, regex.lookahead(/(<[^<>]+>|)\s*\(/))
	};
	const EXPRESSION_CONTAINS = [
		FUNCTION_DISPATCH,
		PREPROCESSOR,
		CPP_PRIMITIVE_TYPES,
		C_LINE_COMMENT_MODE,
		hljs.C_BLOCK_COMMENT_MODE,
		NUMBERS,
		STRINGS
	];
	const EXPRESSION_CONTEXT = {
		variants: [
			{
				begin: /=/,
				end: /;/
			},
			{
				begin: /\(/,
				end: /\)/
			},
			{
				beginKeywords: "new throw return else",
				end: /;/
			}
		],
		keywords: CPP_KEYWORDS,
		contains: EXPRESSION_CONTAINS.concat([{
			begin: /\(/,
			end: /\)/,
			keywords: CPP_KEYWORDS,
			contains: EXPRESSION_CONTAINS.concat(["self"]),
			relevance: 0
		}]),
		relevance: 0
	};
	const FUNCTION_DECLARATION = {
		className: "function",
		begin: "(" + FUNCTION_TYPE_RE + "[\\*&\\s]+)+" + FUNCTION_TITLE,
		returnBegin: true,
		end: /[{;=]/,
		excludeEnd: true,
		keywords: CPP_KEYWORDS,
		illegal: /[^\w\s\*&:<>.]/,
		contains: [
			{
				begin: DECLTYPE_AUTO_RE,
				keywords: CPP_KEYWORDS,
				relevance: 0
			},
			{
				begin: FUNCTION_TITLE,
				returnBegin: true,
				contains: [TITLE_MODE],
				relevance: 0
			},
			{
				begin: /::/,
				relevance: 0
			},
			{
				begin: /:/,
				endsWithParent: true,
				contains: [STRINGS, NUMBERS]
			},
			{
				relevance: 0,
				match: /,/
			},
			{
				className: "params",
				begin: /\(/,
				end: /\)/,
				keywords: CPP_KEYWORDS,
				relevance: 0,
				contains: [
					C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE,
					STRINGS,
					NUMBERS,
					CPP_PRIMITIVE_TYPES,
					{
						begin: /\(/,
						end: /\)/,
						keywords: CPP_KEYWORDS,
						relevance: 0,
						contains: [
							"self",
							C_LINE_COMMENT_MODE,
							hljs.C_BLOCK_COMMENT_MODE,
							STRINGS,
							NUMBERS,
							CPP_PRIMITIVE_TYPES
						]
					}
				]
			},
			CPP_PRIMITIVE_TYPES,
			C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			PREPROCESSOR
		]
	};
	return {
		name: "C++",
		aliases: [
			"cc",
			"c++",
			"h++",
			"hpp",
			"hh",
			"hxx",
			"cxx"
		],
		keywords: CPP_KEYWORDS,
		illegal: "</",
		classNameAliases: { "function.dispatch": "built_in" },
		contains: [].concat(EXPRESSION_CONTEXT, FUNCTION_DECLARATION, FUNCTION_DISPATCH, EXPRESSION_CONTAINS, [
			PREPROCESSOR,
			{
				begin: "\\b(deque|list|queue|priority_queue|pair|stack|vector|map|set|bitset|multiset|multimap|unordered_map|unordered_set|unordered_multiset|unordered_multimap|array|tuple|optional|variant|function|flat_map|flat_set)\\s*<(?!<)",
				end: ">",
				keywords: CPP_KEYWORDS,
				contains: ["self", CPP_PRIMITIVE_TYPES]
			},
			{
				begin: hljs.IDENT_RE + "::",
				keywords: CPP_KEYWORDS
			},
			{
				match: [
					/\b(?:enum(?:\s+(?:class|struct))?|class|struct|union)/,
					/\s+/,
					/\w+/
				],
				className: {
					1: "keyword",
					3: "title.class"
				}
			}
		])
	};
}
/** @type LanguageFn */
function arduino(hljs) {
	const ARDUINO_KW = {
		type: [
			"boolean",
			"byte",
			"word",
			"String"
		],
		built_in: [
			"KeyboardController",
			"MouseController",
			"SoftwareSerial",
			"EthernetServer",
			"EthernetClient",
			"LiquidCrystal",
			"RobotControl",
			"GSMVoiceCall",
			"EthernetUDP",
			"EsploraTFT",
			"HttpClient",
			"RobotMotor",
			"WiFiClient",
			"GSMScanner",
			"FileSystem",
			"Scheduler",
			"GSMServer",
			"YunClient",
			"YunServer",
			"IPAddress",
			"GSMClient",
			"GSMModem",
			"Keyboard",
			"Ethernet",
			"Console",
			"GSMBand",
			"Esplora",
			"Stepper",
			"Process",
			"WiFiUDP",
			"GSM_SMS",
			"Mailbox",
			"USBHost",
			"Firmata",
			"PImage",
			"Client",
			"Server",
			"GSMPIN",
			"FileIO",
			"Bridge",
			"Serial",
			"EEPROM",
			"Stream",
			"Mouse",
			"Audio",
			"Servo",
			"File",
			"Task",
			"GPRS",
			"WiFi",
			"Wire",
			"TFT",
			"GSM",
			"SPI",
			"SD"
		],
		_hints: [
			"setup",
			"loop",
			"runShellCommandAsynchronously",
			"analogWriteResolution",
			"retrieveCallingNumber",
			"printFirmwareVersion",
			"analogReadResolution",
			"sendDigitalPortPair",
			"noListenOnLocalhost",
			"readJoystickButton",
			"setFirmwareVersion",
			"readJoystickSwitch",
			"scrollDisplayRight",
			"getVoiceCallStatus",
			"scrollDisplayLeft",
			"writeMicroseconds",
			"delayMicroseconds",
			"beginTransmission",
			"getSignalStrength",
			"runAsynchronously",
			"getAsynchronously",
			"listenOnLocalhost",
			"getCurrentCarrier",
			"readAccelerometer",
			"messageAvailable",
			"sendDigitalPorts",
			"lineFollowConfig",
			"countryNameWrite",
			"runShellCommand",
			"readStringUntil",
			"rewindDirectory",
			"readTemperature",
			"setClockDivider",
			"readLightSensor",
			"endTransmission",
			"analogReference",
			"detachInterrupt",
			"countryNameRead",
			"attachInterrupt",
			"encryptionType",
			"readBytesUntil",
			"robotNameWrite",
			"readMicrophone",
			"robotNameRead",
			"cityNameWrite",
			"userNameWrite",
			"readJoystickY",
			"readJoystickX",
			"mouseReleased",
			"openNextFile",
			"scanNetworks",
			"noInterrupts",
			"digitalWrite",
			"beginSpeaker",
			"mousePressed",
			"isActionDone",
			"mouseDragged",
			"displayLogos",
			"noAutoscroll",
			"addParameter",
			"remoteNumber",
			"getModifiers",
			"keyboardRead",
			"userNameRead",
			"waitContinue",
			"processInput",
			"parseCommand",
			"printVersion",
			"readNetworks",
			"writeMessage",
			"blinkVersion",
			"cityNameRead",
			"readMessage",
			"setDataMode",
			"parsePacket",
			"isListening",
			"setBitOrder",
			"beginPacket",
			"isDirectory",
			"motorsWrite",
			"drawCompass",
			"digitalRead",
			"clearScreen",
			"serialEvent",
			"rightToLeft",
			"setTextSize",
			"leftToRight",
			"requestFrom",
			"keyReleased",
			"compassRead",
			"analogWrite",
			"interrupts",
			"WiFiServer",
			"disconnect",
			"playMelody",
			"parseFloat",
			"autoscroll",
			"getPINUsed",
			"setPINUsed",
			"setTimeout",
			"sendAnalog",
			"readSlider",
			"analogRead",
			"beginWrite",
			"createChar",
			"motorsStop",
			"keyPressed",
			"tempoWrite",
			"readButton",
			"subnetMask",
			"debugPrint",
			"macAddress",
			"writeGreen",
			"randomSeed",
			"attachGPRS",
			"readString",
			"sendString",
			"remotePort",
			"releaseAll",
			"mouseMoved",
			"background",
			"getXChange",
			"getYChange",
			"answerCall",
			"getResult",
			"voiceCall",
			"endPacket",
			"constrain",
			"getSocket",
			"writeJSON",
			"getButton",
			"available",
			"connected",
			"findUntil",
			"readBytes",
			"exitValue",
			"readGreen",
			"writeBlue",
			"startLoop",
			"IPAddress",
			"isPressed",
			"sendSysex",
			"pauseMode",
			"gatewayIP",
			"setCursor",
			"getOemKey",
			"tuneWrite",
			"noDisplay",
			"loadImage",
			"switchPIN",
			"onRequest",
			"onReceive",
			"changePIN",
			"playFile",
			"noBuffer",
			"parseInt",
			"overflow",
			"checkPIN",
			"knobRead",
			"beginTFT",
			"bitClear",
			"updateIR",
			"bitWrite",
			"position",
			"writeRGB",
			"highByte",
			"writeRed",
			"setSpeed",
			"readBlue",
			"noStroke",
			"remoteIP",
			"transfer",
			"shutdown",
			"hangCall",
			"beginSMS",
			"endWrite",
			"attached",
			"maintain",
			"noCursor",
			"checkReg",
			"checkPUK",
			"shiftOut",
			"isValid",
			"shiftIn",
			"pulseIn",
			"connect",
			"println",
			"localIP",
			"pinMode",
			"getIMEI",
			"display",
			"noBlink",
			"process",
			"getBand",
			"running",
			"beginSD",
			"drawBMP",
			"lowByte",
			"setBand",
			"release",
			"bitRead",
			"prepare",
			"pointTo",
			"readRed",
			"setMode",
			"noFill",
			"remove",
			"listen",
			"stroke",
			"detach",
			"attach",
			"noTone",
			"exists",
			"buffer",
			"height",
			"bitSet",
			"circle",
			"config",
			"cursor",
			"random",
			"IRread",
			"setDNS",
			"endSMS",
			"getKey",
			"micros",
			"millis",
			"begin",
			"print",
			"write",
			"ready",
			"flush",
			"width",
			"isPIN",
			"blink",
			"clear",
			"press",
			"mkdir",
			"rmdir",
			"close",
			"point",
			"yield",
			"image",
			"BSSID",
			"click",
			"delay",
			"read",
			"text",
			"move",
			"peek",
			"beep",
			"rect",
			"line",
			"open",
			"seek",
			"fill",
			"size",
			"turn",
			"stop",
			"home",
			"find",
			"step",
			"tone",
			"sqrt",
			"RSSI",
			"SSID",
			"end",
			"bit",
			"tan",
			"cos",
			"sin",
			"pow",
			"map",
			"abs",
			"max",
			"min",
			"get",
			"run",
			"put"
		],
		literal: [
			"DIGITAL_MESSAGE",
			"FIRMATA_STRING",
			"ANALOG_MESSAGE",
			"REPORT_DIGITAL",
			"REPORT_ANALOG",
			"INPUT_PULLUP",
			"SET_PIN_MODE",
			"INTERNAL2V56",
			"SYSTEM_RESET",
			"LED_BUILTIN",
			"INTERNAL1V1",
			"SYSEX_START",
			"INTERNAL",
			"EXTERNAL",
			"DEFAULT",
			"OUTPUT",
			"INPUT",
			"HIGH",
			"LOW"
		]
	};
	const ARDUINO = cPlusPlus(hljs);
	const kws = ARDUINO.keywords;
	kws.type = [...kws.type, ...ARDUINO_KW.type];
	kws.literal = [...kws.literal, ...ARDUINO_KW.literal];
	kws.built_in = [...kws.built_in, ...ARDUINO_KW.built_in];
	kws._hints = ARDUINO_KW._hints;
	ARDUINO.name = "Arduino";
	ARDUINO.aliases = ["ino"];
	ARDUINO.supersetOf = "cpp";
	return ARDUINO;
}
//#endregion
//#region node_modules/highlight.js/es/languages/bash.js
/** @type LanguageFn */
function bash(hljs) {
	const regex = hljs.regex;
	const VAR = {};
	const BRACED_VAR = {
		begin: /\$\{/,
		end: /\}/,
		contains: ["self", {
			begin: /:-/,
			contains: [VAR]
		}]
	};
	Object.assign(VAR, {
		className: "variable",
		variants: [{ begin: regex.concat(/\$[\w\d#@][\w\d_]*/, `(?![\\w\\d])(?![$])`) }, BRACED_VAR]
	});
	const SUBST = {
		className: "subst",
		begin: /\$\(/,
		end: /\)/,
		contains: [hljs.BACKSLASH_ESCAPE]
	};
	const COMMENT = hljs.inherit(hljs.COMMENT(), {
		match: [/(^|\s)/, /#.*$/],
		scope: { 2: "comment" }
	});
	const HERE_DOC = {
		begin: /<<-?\s*(?=\w+)/,
		starts: { contains: [hljs.END_SAME_AS_BEGIN({
			begin: /(\w+)/,
			end: /(\w+)/,
			className: "string"
		})] }
	};
	const QUOTE_STRING = {
		className: "string",
		begin: /"/,
		end: /"/,
		contains: [
			hljs.BACKSLASH_ESCAPE,
			VAR,
			SUBST
		]
	};
	SUBST.contains.push(QUOTE_STRING);
	const ESCAPED_QUOTE = { match: /\\"/ };
	const APOS_STRING = {
		className: "string",
		begin: /'/,
		end: /'/
	};
	const ESCAPED_APOS = { match: /\\'/ };
	const ARITHMETIC = {
		begin: /\$?\(\(/,
		end: /\)\)/,
		contains: [
			{
				begin: /\d+#[0-9a-f]+/,
				className: "number"
			},
			hljs.NUMBER_MODE,
			VAR
		]
	};
	const KNOWN_SHEBANG = hljs.SHEBANG({
		binary: `(${[
			"fish",
			"bash",
			"zsh",
			"sh",
			"csh",
			"ksh",
			"tcsh",
			"dash",
			"scsh"
		].join("|")})`,
		relevance: 10
	});
	const FUNCTION = {
		className: "function",
		begin: /\w[\w\d_]*\s*\(\s*\)\s*\{/,
		returnBegin: true,
		contains: [hljs.inherit(hljs.TITLE_MODE, { begin: /\w[\w\d_]*/ })],
		relevance: 0
	};
	const KEYWORDS = [
		"if",
		"then",
		"else",
		"elif",
		"fi",
		"time",
		"for",
		"while",
		"until",
		"in",
		"do",
		"done",
		"case",
		"esac",
		"coproc",
		"function",
		"select"
	];
	const LITERALS = ["true", "false"];
	const PATH_MODE = { match: /(\/[a-z._-]+)+/ };
	const SHELL_BUILT_INS = [
		"break",
		"cd",
		"continue",
		"eval",
		"exec",
		"exit",
		"export",
		"getopts",
		"hash",
		"pwd",
		"readonly",
		"return",
		"shift",
		"test",
		"times",
		"trap",
		"umask",
		"unset"
	];
	const BASH_BUILT_INS = [
		"alias",
		"bind",
		"builtin",
		"caller",
		"command",
		"declare",
		"echo",
		"enable",
		"help",
		"let",
		"local",
		"logout",
		"mapfile",
		"printf",
		"read",
		"readarray",
		"source",
		"sudo",
		"type",
		"typeset",
		"ulimit",
		"unalias"
	];
	const ZSH_BUILT_INS = [
		"autoload",
		"bg",
		"bindkey",
		"bye",
		"cap",
		"chdir",
		"clone",
		"comparguments",
		"compcall",
		"compctl",
		"compdescribe",
		"compfiles",
		"compgroups",
		"compquote",
		"comptags",
		"comptry",
		"compvalues",
		"dirs",
		"disable",
		"disown",
		"echotc",
		"echoti",
		"emulate",
		"fc",
		"fg",
		"float",
		"functions",
		"getcap",
		"getln",
		"history",
		"integer",
		"jobs",
		"kill",
		"limit",
		"log",
		"noglob",
		"popd",
		"print",
		"pushd",
		"pushln",
		"rehash",
		"sched",
		"setcap",
		"setopt",
		"stat",
		"suspend",
		"ttyctl",
		"unfunction",
		"unhash",
		"unlimit",
		"unsetopt",
		"vared",
		"wait",
		"whence",
		"where",
		"which",
		"zcompile",
		"zformat",
		"zftp",
		"zle",
		"zmodload",
		"zparseopts",
		"zprof",
		"zpty",
		"zregexparse",
		"zsocket",
		"zstyle",
		"ztcp"
	];
	const GNU_CORE_UTILS = [
		"chcon",
		"chgrp",
		"chown",
		"chmod",
		"cp",
		"dd",
		"df",
		"dir",
		"dircolors",
		"ln",
		"ls",
		"mkdir",
		"mkfifo",
		"mknod",
		"mktemp",
		"mv",
		"realpath",
		"rm",
		"rmdir",
		"shred",
		"sync",
		"touch",
		"truncate",
		"vdir",
		"b2sum",
		"base32",
		"base64",
		"cat",
		"cksum",
		"comm",
		"csplit",
		"cut",
		"expand",
		"fmt",
		"fold",
		"head",
		"join",
		"md5sum",
		"nl",
		"numfmt",
		"od",
		"paste",
		"ptx",
		"pr",
		"sha1sum",
		"sha224sum",
		"sha256sum",
		"sha384sum",
		"sha512sum",
		"shuf",
		"sort",
		"split",
		"sum",
		"tac",
		"tail",
		"tr",
		"tsort",
		"unexpand",
		"uniq",
		"wc",
		"arch",
		"basename",
		"chroot",
		"date",
		"dirname",
		"du",
		"echo",
		"env",
		"expr",
		"factor",
		"groups",
		"hostid",
		"id",
		"link",
		"logname",
		"nice",
		"nohup",
		"nproc",
		"pathchk",
		"pinky",
		"printenv",
		"printf",
		"pwd",
		"readlink",
		"runcon",
		"seq",
		"sleep",
		"stat",
		"stdbuf",
		"stty",
		"tee",
		"test",
		"timeout",
		"tty",
		"uname",
		"unlink",
		"uptime",
		"users",
		"who",
		"whoami",
		"yes"
	];
	return {
		name: "Bash",
		aliases: ["sh", "zsh"],
		keywords: {
			$pattern: /\b[a-z][a-z0-9._-]+\b/,
			keyword: KEYWORDS,
			literal: LITERALS,
			built_in: [
				...SHELL_BUILT_INS,
				...BASH_BUILT_INS,
				"set",
				"shopt",
				...ZSH_BUILT_INS,
				...GNU_CORE_UTILS
			]
		},
		contains: [
			KNOWN_SHEBANG,
			hljs.SHEBANG(),
			FUNCTION,
			ARITHMETIC,
			COMMENT,
			HERE_DOC,
			PATH_MODE,
			QUOTE_STRING,
			ESCAPED_QUOTE,
			APOS_STRING,
			ESCAPED_APOS,
			VAR
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/c.js
/** @type LanguageFn */
function c(hljs) {
	const regex = hljs.regex;
	const C_LINE_COMMENT_MODE = hljs.COMMENT("//", "$", { contains: [{ begin: /\\\n/ }] });
	const DECLTYPE_AUTO_RE = "decltype\\(auto\\)";
	const NAMESPACE_RE = "[a-zA-Z_]\\w*::";
	const FUNCTION_TYPE_RE = "(decltype\\(auto\\)|" + regex.optional(NAMESPACE_RE) + "[a-zA-Z_]\\w*" + regex.optional("<[^<>]+>") + ")";
	const TYPES = {
		className: "type",
		variants: [{ begin: "\\b[a-z\\d_]*_t\\b" }, { match: /\batomic_[a-z]{3,6}\b/ }]
	};
	const STRINGS = {
		className: "string",
		variants: [
			{
				begin: "(u8?|U|L)?\"",
				end: "\"",
				illegal: "\\n",
				contains: [hljs.BACKSLASH_ESCAPE]
			},
			{
				begin: "(u8?|U|L)?'(\\\\(x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4,8}|[0-7]{3}|\\S)|.)",
				end: "'",
				illegal: "."
			},
			hljs.END_SAME_AS_BEGIN({
				begin: /(?:u8?|U|L)?R"([^()\\ ]{0,16})\(/,
				end: /\)([^()\\ ]{0,16})"/
			})
		]
	};
	const NUMBERS = {
		className: "number",
		variants: [
			{ match: /\b(0b[01']+)/ },
			{ match: /(-?)\b([\d']+(\.[\d']*)?|\.[\d']+)((ll|LL|l|L)(u|U)?|(u|U)(ll|LL|l|L)?|f|F|b|B)/ },
			{ match: /(-?)\b(0[xX][a-fA-F0-9]+(?:'[a-fA-F0-9]+)*(?:\.[a-fA-F0-9]*(?:'[a-fA-F0-9]*)*)?(?:[pP][-+]?[0-9]+)?(l|L)?(u|U)?)/ },
			{ match: /(-?)\b\d+(?:'\d+)*(?:\.\d*(?:'\d*)*)?(?:[eE][-+]?\d+)?/ }
		],
		relevance: 0
	};
	const PREPROCESSOR = {
		className: "meta",
		begin: /#\s*[a-z]+\b/,
		end: /$/,
		keywords: { keyword: "if else elif endif define undef warning error line pragma _Pragma ifdef ifndef elifdef elifndef include" },
		contains: [
			{
				begin: /\\\n/,
				relevance: 0
			},
			hljs.inherit(STRINGS, { className: "string" }),
			{
				className: "string",
				begin: /<.*?>/
			},
			C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE
		]
	};
	const TITLE_MODE = {
		className: "title",
		begin: regex.optional(NAMESPACE_RE) + hljs.IDENT_RE,
		relevance: 0
	};
	const FUNCTION_TITLE = regex.optional(NAMESPACE_RE) + hljs.IDENT_RE + "\\s*\\(";
	const KEYWORDS = {
		keyword: [
			"asm",
			"auto",
			"break",
			"case",
			"continue",
			"default",
			"do",
			"else",
			"enum",
			"extern",
			"for",
			"fortran",
			"goto",
			"if",
			"inline",
			"register",
			"restrict",
			"return",
			"sizeof",
			"typeof",
			"typeof_unqual",
			"struct",
			"switch",
			"typedef",
			"union",
			"volatile",
			"while",
			"_Alignas",
			"_Alignof",
			"_Atomic",
			"_Generic",
			"_Noreturn",
			"_Static_assert",
			"_Thread_local",
			"alignas",
			"alignof",
			"noreturn",
			"static_assert",
			"thread_local",
			"_Pragma"
		],
		type: [
			"float",
			"double",
			"signed",
			"unsigned",
			"int",
			"short",
			"long",
			"char",
			"void",
			"_Bool",
			"_BitInt",
			"_Complex",
			"_Imaginary",
			"_Decimal32",
			"_Decimal64",
			"_Decimal96",
			"_Decimal128",
			"_Decimal64x",
			"_Decimal128x",
			"_Float16",
			"_Float32",
			"_Float64",
			"_Float128",
			"_Float32x",
			"_Float64x",
			"_Float128x",
			"const",
			"static",
			"constexpr",
			"complex",
			"bool",
			"imaginary"
		],
		literal: "true false NULL",
		built_in: "std string wstring cin cout cerr clog stdin stdout stderr stringstream istringstream ostringstream auto_ptr deque list queue stack vector map set pair bitset multiset multimap unordered_set unordered_map unordered_multiset unordered_multimap priority_queue make_pair array shared_ptr abort terminate abs acos asin atan2 atan calloc ceil cosh cos exit exp fabs floor fmod fprintf fputs free frexp fscanf future isalnum isalpha iscntrl isdigit isgraph islower isprint ispunct isspace isupper isxdigit tolower toupper labs ldexp log10 log malloc realloc memchr memcmp memcpy memset modf pow printf putchar puts scanf sinh sin snprintf sprintf sqrt sscanf strcat strchr strcmp strcpy strcspn strlen strncat strncmp strncpy strpbrk strrchr strspn strstr tanh tan vfprintf vprintf vsprintf endl initializer_list unique_ptr"
	};
	const EXPRESSION_CONTAINS = [
		PREPROCESSOR,
		TYPES,
		C_LINE_COMMENT_MODE,
		hljs.C_BLOCK_COMMENT_MODE,
		NUMBERS,
		STRINGS
	];
	const EXPRESSION_CONTEXT = {
		variants: [
			{
				begin: /=/,
				end: /;/
			},
			{
				begin: /\(/,
				end: /\)/
			},
			{
				beginKeywords: "new throw return else",
				end: /;/
			}
		],
		keywords: KEYWORDS,
		contains: EXPRESSION_CONTAINS.concat([{
			begin: /\(/,
			end: /\)/,
			keywords: KEYWORDS,
			contains: EXPRESSION_CONTAINS.concat(["self"]),
			relevance: 0
		}]),
		relevance: 0
	};
	const FUNCTION_DECLARATION = {
		begin: "(" + FUNCTION_TYPE_RE + "[\\*&\\s]+)+" + FUNCTION_TITLE,
		returnBegin: true,
		end: /[{;=]/,
		excludeEnd: true,
		keywords: KEYWORDS,
		illegal: /[^\w\s\*&:<>.]/,
		contains: [
			{
				begin: DECLTYPE_AUTO_RE,
				keywords: KEYWORDS,
				relevance: 0
			},
			{
				begin: FUNCTION_TITLE,
				returnBegin: true,
				contains: [hljs.inherit(TITLE_MODE, { className: "title.function" })],
				relevance: 0
			},
			{
				relevance: 0,
				match: /,/
			},
			{
				className: "params",
				begin: /\(/,
				end: /\)/,
				keywords: KEYWORDS,
				relevance: 0,
				contains: [
					C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE,
					STRINGS,
					NUMBERS,
					TYPES,
					{
						begin: /\(/,
						end: /\)/,
						keywords: KEYWORDS,
						relevance: 0,
						contains: [
							"self",
							C_LINE_COMMENT_MODE,
							hljs.C_BLOCK_COMMENT_MODE,
							STRINGS,
							NUMBERS,
							TYPES
						]
					}
				]
			},
			TYPES,
			C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			PREPROCESSOR
		]
	};
	return {
		name: "C",
		aliases: ["h"],
		keywords: KEYWORDS,
		disableAutodetect: true,
		illegal: "</",
		contains: [].concat(EXPRESSION_CONTEXT, FUNCTION_DECLARATION, EXPRESSION_CONTAINS, [
			PREPROCESSOR,
			{
				begin: hljs.IDENT_RE + "::",
				keywords: KEYWORDS
			},
			{
				className: "class",
				beginKeywords: "enum class struct union",
				end: /[{;:<>=]/,
				contains: [{ beginKeywords: "final class struct" }, hljs.TITLE_MODE]
			}
		]),
		exports: {
			preprocessor: PREPROCESSOR,
			strings: STRINGS,
			keywords: KEYWORDS
		}
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/cpp.js
/** @type LanguageFn */
function cpp(hljs) {
	const regex = hljs.regex;
	const C_LINE_COMMENT_MODE = hljs.COMMENT("//", "$", { contains: [{ begin: /\\\n/ }] });
	const DECLTYPE_AUTO_RE = "decltype\\(auto\\)";
	const NAMESPACE_RE = "[a-zA-Z_]\\w*::";
	const FUNCTION_TYPE_RE = "(?!struct)(decltype\\(auto\\)|" + regex.optional(NAMESPACE_RE) + "[a-zA-Z_]\\w*" + regex.optional("<[^<>]+>") + ")";
	const CPP_PRIMITIVE_TYPES = {
		className: "type",
		begin: "\\b[a-z\\d_]*_t\\b"
	};
	const STRINGS = {
		className: "string",
		variants: [
			{
				begin: "(u8?|U|L)?\"",
				end: "\"",
				illegal: "\\n",
				contains: [hljs.BACKSLASH_ESCAPE]
			},
			{
				begin: "(u8?|U|L)?'(\\\\(x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4,8}|[0-7]{3}|\\S)|.)",
				end: "'",
				illegal: "."
			},
			hljs.END_SAME_AS_BEGIN({
				begin: /(?:u8?|U|L)?R"([^()\\ ]{0,16})\(/,
				end: /\)([^()\\ ]{0,16})"/
			})
		]
	};
	const NUMBERS = {
		className: "number",
		variants: [{ begin: "[+-]?(?:(?:[0-9](?:'?[0-9])*\\.(?:[0-9](?:'?[0-9])*)?|\\.[0-9](?:'?[0-9])*)(?:[Ee][+-]?[0-9](?:'?[0-9])*)?|[0-9](?:'?[0-9])*[Ee][+-]?[0-9](?:'?[0-9])*|0[Xx](?:[0-9A-Fa-f](?:'?[0-9A-Fa-f])*(?:\\.(?:[0-9A-Fa-f](?:'?[0-9A-Fa-f])*)?)?|\\.[0-9A-Fa-f](?:'?[0-9A-Fa-f])*)[Pp][+-]?[0-9](?:'?[0-9])*)(?:[Ff](?:16|32|64|128)?|(BF|bf)16|[Ll]|)" }, { begin: "[+-]?\\b(?:0[Bb][01](?:'?[01])*|0[Xx][0-9A-Fa-f](?:'?[0-9A-Fa-f])*|0(?:'?[0-7])*|[1-9](?:'?[0-9])*)(?:[Uu](?:LL?|ll?)|[Uu][Zz]?|(?:LL?|ll?)[Uu]?|[Zz][Uu]|)" }],
		relevance: 0
	};
	const PREPROCESSOR = {
		className: "meta",
		begin: /#\s*[a-z]+\b/,
		end: /$/,
		keywords: { keyword: "if else elif endif define undef warning error line pragma _Pragma ifdef ifndef include" },
		contains: [
			{
				begin: /\\\n/,
				relevance: 0
			},
			hljs.inherit(STRINGS, { className: "string" }),
			{
				className: "string",
				begin: /<.*?>/
			},
			C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE
		]
	};
	const TITLE_MODE = {
		className: "title",
		begin: regex.optional(NAMESPACE_RE) + hljs.IDENT_RE,
		relevance: 0
	};
	const FUNCTION_TITLE = regex.optional(NAMESPACE_RE) + hljs.IDENT_RE + "\\s*\\(";
	const RESERVED_KEYWORDS = [
		"alignas",
		"alignof",
		"and",
		"and_eq",
		"asm",
		"atomic_cancel",
		"atomic_commit",
		"atomic_noexcept",
		"auto",
		"bitand",
		"bitor",
		"break",
		"case",
		"catch",
		"class",
		"co_await",
		"co_return",
		"co_yield",
		"compl",
		"concept",
		"const_cast|10",
		"consteval",
		"constexpr",
		"constinit",
		"continue",
		"decltype",
		"default",
		"delete",
		"do",
		"dynamic_cast|10",
		"else",
		"enum",
		"explicit",
		"export",
		"extern",
		"false",
		"final",
		"for",
		"friend",
		"goto",
		"if",
		"import",
		"inline",
		"module",
		"mutable",
		"namespace",
		"new",
		"noexcept",
		"not",
		"not_eq",
		"nullptr",
		"operator",
		"or",
		"or_eq",
		"override",
		"private",
		"protected",
		"public",
		"reflexpr",
		"register",
		"reinterpret_cast|10",
		"requires",
		"return",
		"sizeof",
		"static_assert",
		"static_cast|10",
		"struct",
		"switch",
		"synchronized",
		"template",
		"this",
		"thread_local",
		"throw",
		"transaction_safe",
		"transaction_safe_dynamic",
		"true",
		"try",
		"typedef",
		"typeid",
		"typename",
		"union",
		"using",
		"virtual",
		"volatile",
		"while",
		"xor",
		"xor_eq"
	];
	const RESERVED_TYPES = [
		"bool",
		"char",
		"char16_t",
		"char32_t",
		"char8_t",
		"double",
		"float",
		"int",
		"long",
		"short",
		"void",
		"wchar_t",
		"unsigned",
		"signed",
		"const",
		"static"
	];
	const TYPE_HINTS = [
		"any",
		"auto_ptr",
		"barrier",
		"binary_semaphore",
		"bitset",
		"complex",
		"condition_variable",
		"condition_variable_any",
		"counting_semaphore",
		"deque",
		"false_type",
		"flat_map",
		"flat_set",
		"future",
		"imaginary",
		"initializer_list",
		"istringstream",
		"jthread",
		"latch",
		"lock_guard",
		"multimap",
		"multiset",
		"mutex",
		"optional",
		"ostringstream",
		"packaged_task",
		"pair",
		"promise",
		"priority_queue",
		"queue",
		"recursive_mutex",
		"recursive_timed_mutex",
		"scoped_lock",
		"set",
		"shared_future",
		"shared_lock",
		"shared_mutex",
		"shared_timed_mutex",
		"shared_ptr",
		"stack",
		"string_view",
		"stringstream",
		"timed_mutex",
		"thread",
		"true_type",
		"tuple",
		"unique_lock",
		"unique_ptr",
		"unordered_map",
		"unordered_multimap",
		"unordered_multiset",
		"unordered_set",
		"variant",
		"vector",
		"weak_ptr",
		"wstring",
		"wstring_view"
	];
	const FUNCTION_HINTS = [
		"abort",
		"abs",
		"acos",
		"apply",
		"as_const",
		"asin",
		"atan",
		"atan2",
		"calloc",
		"ceil",
		"cerr",
		"cin",
		"clog",
		"cos",
		"cosh",
		"cout",
		"declval",
		"endl",
		"exchange",
		"exit",
		"exp",
		"fabs",
		"floor",
		"fmod",
		"forward",
		"fprintf",
		"fputs",
		"free",
		"frexp",
		"fscanf",
		"future",
		"invoke",
		"isalnum",
		"isalpha",
		"iscntrl",
		"isdigit",
		"isgraph",
		"islower",
		"isprint",
		"ispunct",
		"isspace",
		"isupper",
		"isxdigit",
		"labs",
		"launder",
		"ldexp",
		"log",
		"log10",
		"make_pair",
		"make_shared",
		"make_shared_for_overwrite",
		"make_tuple",
		"make_unique",
		"malloc",
		"memchr",
		"memcmp",
		"memcpy",
		"memset",
		"modf",
		"move",
		"pow",
		"printf",
		"putchar",
		"puts",
		"realloc",
		"scanf",
		"sin",
		"sinh",
		"snprintf",
		"sprintf",
		"sqrt",
		"sscanf",
		"std",
		"stderr",
		"stdin",
		"stdout",
		"strcat",
		"strchr",
		"strcmp",
		"strcpy",
		"strcspn",
		"strlen",
		"strncat",
		"strncmp",
		"strncpy",
		"strpbrk",
		"strrchr",
		"strspn",
		"strstr",
		"swap",
		"tan",
		"tanh",
		"terminate",
		"to_underlying",
		"tolower",
		"toupper",
		"vfprintf",
		"visit",
		"vprintf",
		"vsprintf"
	];
	const CPP_KEYWORDS = {
		type: RESERVED_TYPES,
		keyword: RESERVED_KEYWORDS,
		literal: [
			"NULL",
			"false",
			"nullopt",
			"nullptr",
			"true"
		],
		built_in: ["_Pragma"],
		_type_hints: TYPE_HINTS
	};
	const FUNCTION_DISPATCH = {
		className: "function.dispatch",
		relevance: 0,
		keywords: { _hint: FUNCTION_HINTS },
		begin: regex.concat(/\b/, /(?!decltype)/, /(?!if)/, /(?!for)/, /(?!switch)/, /(?!while)/, hljs.IDENT_RE, regex.lookahead(/(<[^<>]+>|)\s*\(/))
	};
	const EXPRESSION_CONTAINS = [
		FUNCTION_DISPATCH,
		PREPROCESSOR,
		CPP_PRIMITIVE_TYPES,
		C_LINE_COMMENT_MODE,
		hljs.C_BLOCK_COMMENT_MODE,
		NUMBERS,
		STRINGS
	];
	const EXPRESSION_CONTEXT = {
		variants: [
			{
				begin: /=/,
				end: /;/
			},
			{
				begin: /\(/,
				end: /\)/
			},
			{
				beginKeywords: "new throw return else",
				end: /;/
			}
		],
		keywords: CPP_KEYWORDS,
		contains: EXPRESSION_CONTAINS.concat([{
			begin: /\(/,
			end: /\)/,
			keywords: CPP_KEYWORDS,
			contains: EXPRESSION_CONTAINS.concat(["self"]),
			relevance: 0
		}]),
		relevance: 0
	};
	const FUNCTION_DECLARATION = {
		className: "function",
		begin: "(" + FUNCTION_TYPE_RE + "[\\*&\\s]+)+" + FUNCTION_TITLE,
		returnBegin: true,
		end: /[{;=]/,
		excludeEnd: true,
		keywords: CPP_KEYWORDS,
		illegal: /[^\w\s\*&:<>.]/,
		contains: [
			{
				begin: DECLTYPE_AUTO_RE,
				keywords: CPP_KEYWORDS,
				relevance: 0
			},
			{
				begin: FUNCTION_TITLE,
				returnBegin: true,
				contains: [TITLE_MODE],
				relevance: 0
			},
			{
				begin: /::/,
				relevance: 0
			},
			{
				begin: /:/,
				endsWithParent: true,
				contains: [STRINGS, NUMBERS]
			},
			{
				relevance: 0,
				match: /,/
			},
			{
				className: "params",
				begin: /\(/,
				end: /\)/,
				keywords: CPP_KEYWORDS,
				relevance: 0,
				contains: [
					C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE,
					STRINGS,
					NUMBERS,
					CPP_PRIMITIVE_TYPES,
					{
						begin: /\(/,
						end: /\)/,
						keywords: CPP_KEYWORDS,
						relevance: 0,
						contains: [
							"self",
							C_LINE_COMMENT_MODE,
							hljs.C_BLOCK_COMMENT_MODE,
							STRINGS,
							NUMBERS,
							CPP_PRIMITIVE_TYPES
						]
					}
				]
			},
			CPP_PRIMITIVE_TYPES,
			C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			PREPROCESSOR
		]
	};
	return {
		name: "C++",
		aliases: [
			"cc",
			"c++",
			"h++",
			"hpp",
			"hh",
			"hxx",
			"cxx"
		],
		keywords: CPP_KEYWORDS,
		illegal: "</",
		classNameAliases: { "function.dispatch": "built_in" },
		contains: [].concat(EXPRESSION_CONTEXT, FUNCTION_DECLARATION, FUNCTION_DISPATCH, EXPRESSION_CONTAINS, [
			PREPROCESSOR,
			{
				begin: "\\b(deque|list|queue|priority_queue|pair|stack|vector|map|set|bitset|multiset|multimap|unordered_map|unordered_set|unordered_multiset|unordered_multimap|array|tuple|optional|variant|function|flat_map|flat_set)\\s*<(?!<)",
				end: ">",
				keywords: CPP_KEYWORDS,
				contains: ["self", CPP_PRIMITIVE_TYPES]
			},
			{
				begin: hljs.IDENT_RE + "::",
				keywords: CPP_KEYWORDS
			},
			{
				match: [
					/\b(?:enum(?:\s+(?:class|struct))?|class|struct|union)/,
					/\s+/,
					/\w+/
				],
				className: {
					1: "keyword",
					3: "title.class"
				}
			}
		])
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/csharp.js
/** @type LanguageFn */
function csharp(hljs) {
	const BUILT_IN_KEYWORDS = [
		"bool",
		"byte",
		"char",
		"decimal",
		"delegate",
		"double",
		"dynamic",
		"enum",
		"float",
		"int",
		"long",
		"nint",
		"nuint",
		"object",
		"sbyte",
		"short",
		"string",
		"ulong",
		"uint",
		"ushort"
	];
	const FUNCTION_MODIFIERS = [
		"public",
		"private",
		"protected",
		"static",
		"internal",
		"protected",
		"abstract",
		"async",
		"extern",
		"override",
		"unsafe",
		"virtual",
		"new",
		"sealed",
		"partial"
	];
	const KEYWORDS = {
		keyword: [
			"abstract",
			"as",
			"base",
			"break",
			"case",
			"catch",
			"class",
			"const",
			"continue",
			"do",
			"else",
			"event",
			"explicit",
			"extern",
			"finally",
			"fixed",
			"for",
			"foreach",
			"goto",
			"if",
			"implicit",
			"in",
			"interface",
			"internal",
			"is",
			"lock",
			"namespace",
			"new",
			"operator",
			"out",
			"override",
			"params",
			"private",
			"protected",
			"public",
			"readonly",
			"record",
			"ref",
			"return",
			"scoped",
			"sealed",
			"sizeof",
			"stackalloc",
			"static",
			"struct",
			"switch",
			"this",
			"throw",
			"try",
			"typeof",
			"unchecked",
			"unsafe",
			"using",
			"virtual",
			"void",
			"volatile",
			"while"
		].concat([
			"add",
			"alias",
			"and",
			"ascending",
			"args",
			"async",
			"await",
			"by",
			"descending",
			"dynamic",
			"equals",
			"file",
			"from",
			"get",
			"global",
			"group",
			"init",
			"into",
			"join",
			"let",
			"nameof",
			"not",
			"notnull",
			"on",
			"or",
			"orderby",
			"partial",
			"record",
			"remove",
			"required",
			"scoped",
			"select",
			"set",
			"unmanaged",
			"value|0",
			"var",
			"when",
			"where",
			"with",
			"yield"
		]),
		built_in: BUILT_IN_KEYWORDS,
		literal: [
			"default",
			"false",
			"null",
			"true"
		]
	};
	const TITLE_MODE = hljs.inherit(hljs.TITLE_MODE, { begin: "[a-zA-Z](\\.?\\w)*" });
	const NUMBERS = {
		className: "number",
		variants: [
			{ begin: "\\b(0b[01']+)" },
			{ begin: "(-?)\\b([\\d']+(\\.[\\d']*)?|\\.[\\d']+)(u|U|l|L|ul|UL|f|F|b|B)" },
			{ begin: "(-?)(\\b0[xX][a-fA-F0-9']+|(\\b[\\d']+(\\.[\\d']*)?|\\.[\\d']+)([eE][-+]?[\\d']+)?)" }
		],
		relevance: 0
	};
	const RAW_STRING = {
		className: "string",
		begin: /"""("*)(?!")(.|\n)*?"""\1/,
		relevance: 1
	};
	const VERBATIM_STRING = {
		className: "string",
		begin: "@\"",
		end: "\"",
		contains: [{ begin: "\"\"" }]
	};
	const VERBATIM_STRING_NO_LF = hljs.inherit(VERBATIM_STRING, { illegal: /\n/ });
	const SUBST = {
		className: "subst",
		begin: /\{/,
		end: /\}/,
		keywords: KEYWORDS
	};
	const SUBST_NO_LF = hljs.inherit(SUBST, { illegal: /\n/ });
	const INTERPOLATED_STRING = {
		className: "string",
		begin: /\$"/,
		end: "\"",
		illegal: /\n/,
		contains: [
			{ begin: /\{\{/ },
			{ begin: /\}\}/ },
			hljs.BACKSLASH_ESCAPE,
			SUBST_NO_LF
		]
	};
	const INTERPOLATED_VERBATIM_STRING = {
		className: "string",
		begin: /\$@"/,
		end: "\"",
		contains: [
			{ begin: /\{\{/ },
			{ begin: /\}\}/ },
			{ begin: "\"\"" },
			SUBST
		]
	};
	const INTERPOLATED_VERBATIM_STRING_NO_LF = hljs.inherit(INTERPOLATED_VERBATIM_STRING, {
		illegal: /\n/,
		contains: [
			{ begin: /\{\{/ },
			{ begin: /\}\}/ },
			{ begin: "\"\"" },
			SUBST_NO_LF
		]
	});
	SUBST.contains = [
		INTERPOLATED_VERBATIM_STRING,
		INTERPOLATED_STRING,
		VERBATIM_STRING,
		hljs.APOS_STRING_MODE,
		hljs.QUOTE_STRING_MODE,
		NUMBERS,
		hljs.C_BLOCK_COMMENT_MODE
	];
	SUBST_NO_LF.contains = [
		INTERPOLATED_VERBATIM_STRING_NO_LF,
		INTERPOLATED_STRING,
		VERBATIM_STRING_NO_LF,
		hljs.APOS_STRING_MODE,
		hljs.QUOTE_STRING_MODE,
		NUMBERS,
		hljs.inherit(hljs.C_BLOCK_COMMENT_MODE, { illegal: /\n/ })
	];
	const STRING = { variants: [
		RAW_STRING,
		INTERPOLATED_VERBATIM_STRING,
		INTERPOLATED_STRING,
		VERBATIM_STRING,
		hljs.APOS_STRING_MODE,
		hljs.QUOTE_STRING_MODE
	] };
	const GENERIC_MODIFIER = {
		begin: "<",
		end: ">",
		contains: [{ beginKeywords: "in out" }, TITLE_MODE]
	};
	const TYPE_IDENT_RE = hljs.IDENT_RE + "(<" + hljs.IDENT_RE + "(\\s*,\\s*" + hljs.IDENT_RE + ")*>)?(\\[\\])?";
	const AT_IDENTIFIER = {
		begin: "@" + hljs.IDENT_RE,
		relevance: 0
	};
	return {
		name: "C#",
		aliases: ["cs", "c#"],
		keywords: KEYWORDS,
		illegal: /::/,
		contains: [
			hljs.COMMENT("///", "$", {
				returnBegin: true,
				contains: [{
					className: "doctag",
					variants: [
						{
							begin: "///",
							relevance: 0
						},
						{ begin: "<!--|-->" },
						{
							begin: "</?",
							end: ">"
						}
					]
				}]
			}),
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			{
				className: "meta",
				begin: "#",
				end: "$",
				keywords: { keyword: "if else elif endif define undef warning error line region endregion pragma checksum" }
			},
			STRING,
			NUMBERS,
			{
				beginKeywords: "class interface",
				relevance: 0,
				end: /[{;=]/,
				illegal: /[^\s:,]/,
				contains: [
					{ beginKeywords: "where class" },
					TITLE_MODE,
					GENERIC_MODIFIER,
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE
				]
			},
			{
				beginKeywords: "namespace",
				relevance: 0,
				end: /[{;=]/,
				illegal: /[^\s:]/,
				contains: [
					TITLE_MODE,
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE
				]
			},
			{
				beginKeywords: "record",
				relevance: 0,
				end: /[{;=]/,
				illegal: /[^\s:]/,
				contains: [
					TITLE_MODE,
					GENERIC_MODIFIER,
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE
				]
			},
			{
				className: "meta",
				begin: "^\\s*\\[(?=[\\w])",
				excludeBegin: true,
				end: "\\]",
				excludeEnd: true,
				contains: [{
					className: "string",
					begin: /"/,
					end: /"/
				}]
			},
			{
				beginKeywords: "new return throw await else",
				relevance: 0
			},
			{
				className: "function",
				begin: "(" + TYPE_IDENT_RE + "\\s+)+" + hljs.IDENT_RE + "\\s*(<[^=]+>\\s*)?\\(",
				returnBegin: true,
				end: /\s*[{;=]/,
				excludeEnd: true,
				keywords: KEYWORDS,
				contains: [
					{
						beginKeywords: FUNCTION_MODIFIERS.join(" "),
						relevance: 0
					},
					{
						begin: hljs.IDENT_RE + "\\s*(<[^=]+>\\s*)?\\(",
						returnBegin: true,
						contains: [hljs.TITLE_MODE, GENERIC_MODIFIER],
						relevance: 0
					},
					{ match: /\(\)/ },
					{
						className: "params",
						begin: /\(/,
						end: /\)/,
						excludeBegin: true,
						excludeEnd: true,
						keywords: KEYWORDS,
						relevance: 0,
						contains: [
							STRING,
							NUMBERS,
							hljs.C_BLOCK_COMMENT_MODE
						]
					},
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE
				]
			},
			AT_IDENTIFIER
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/css.js
var MODES$2 = (hljs) => {
	return {
		IMPORTANT: {
			scope: "meta",
			begin: "!important"
		},
		BLOCK_COMMENT: hljs.C_BLOCK_COMMENT_MODE,
		HEXCOLOR: {
			scope: "number",
			begin: /#(([0-9a-fA-F]{3,4})|(([0-9a-fA-F]{2}){3,4}))\b/
		},
		FUNCTION_DISPATCH: {
			className: "built_in",
			begin: /[\w-]+(?=\()/
		},
		ATTRIBUTE_SELECTOR_MODE: {
			scope: "selector-attr",
			begin: /\[/,
			end: /\]/,
			illegal: "$",
			contains: [hljs.APOS_STRING_MODE, hljs.QUOTE_STRING_MODE]
		},
		CSS_NUMBER_MODE: {
			scope: "number",
			begin: hljs.NUMBER_RE + "(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",
			relevance: 0
		},
		CSS_VARIABLE: {
			className: "attr",
			begin: /--[A-Za-z_][A-Za-z0-9_-]*/
		}
	};
};
var HTML_TAGS$2 = [
	"a",
	"abbr",
	"address",
	"article",
	"aside",
	"audio",
	"b",
	"blockquote",
	"body",
	"button",
	"canvas",
	"caption",
	"cite",
	"code",
	"dd",
	"del",
	"details",
	"dfn",
	"div",
	"dl",
	"dt",
	"em",
	"fieldset",
	"figcaption",
	"figure",
	"footer",
	"form",
	"h1",
	"h2",
	"h3",
	"h4",
	"h5",
	"h6",
	"header",
	"hgroup",
	"html",
	"i",
	"iframe",
	"img",
	"input",
	"ins",
	"kbd",
	"label",
	"legend",
	"li",
	"main",
	"mark",
	"menu",
	"nav",
	"object",
	"ol",
	"optgroup",
	"option",
	"p",
	"picture",
	"q",
	"quote",
	"samp",
	"section",
	"select",
	"source",
	"span",
	"strong",
	"summary",
	"sup",
	"table",
	"tbody",
	"td",
	"textarea",
	"tfoot",
	"th",
	"thead",
	"time",
	"tr",
	"ul",
	"var",
	"video"
];
var SVG_TAGS$2 = [
	"defs",
	"g",
	"marker",
	"mask",
	"pattern",
	"svg",
	"switch",
	"symbol",
	"feBlend",
	"feColorMatrix",
	"feComponentTransfer",
	"feComposite",
	"feConvolveMatrix",
	"feDiffuseLighting",
	"feDisplacementMap",
	"feFlood",
	"feGaussianBlur",
	"feImage",
	"feMerge",
	"feMorphology",
	"feOffset",
	"feSpecularLighting",
	"feTile",
	"feTurbulence",
	"linearGradient",
	"radialGradient",
	"stop",
	"circle",
	"ellipse",
	"image",
	"line",
	"path",
	"polygon",
	"polyline",
	"rect",
	"text",
	"use",
	"textPath",
	"tspan",
	"foreignObject",
	"clipPath"
];
var TAGS$2 = [...HTML_TAGS$2, ...SVG_TAGS$2];
var MEDIA_FEATURES$2 = [
	"any-hover",
	"any-pointer",
	"aspect-ratio",
	"color",
	"color-gamut",
	"color-index",
	"device-aspect-ratio",
	"device-height",
	"device-width",
	"display-mode",
	"forced-colors",
	"grid",
	"height",
	"hover",
	"inverted-colors",
	"monochrome",
	"orientation",
	"overflow-block",
	"overflow-inline",
	"pointer",
	"prefers-color-scheme",
	"prefers-contrast",
	"prefers-reduced-motion",
	"prefers-reduced-transparency",
	"resolution",
	"scan",
	"scripting",
	"update",
	"width",
	"min-width",
	"max-width",
	"min-height",
	"max-height"
].sort().reverse();
var PSEUDO_CLASSES$2 = [
	"active",
	"any-link",
	"blank",
	"checked",
	"current",
	"default",
	"defined",
	"dir",
	"disabled",
	"drop",
	"empty",
	"enabled",
	"first",
	"first-child",
	"first-of-type",
	"fullscreen",
	"future",
	"focus",
	"focus-visible",
	"focus-within",
	"has",
	"host",
	"host-context",
	"hover",
	"indeterminate",
	"in-range",
	"invalid",
	"is",
	"lang",
	"last-child",
	"last-of-type",
	"left",
	"link",
	"local-link",
	"not",
	"nth-child",
	"nth-col",
	"nth-last-child",
	"nth-last-col",
	"nth-last-of-type",
	"nth-of-type",
	"only-child",
	"only-of-type",
	"optional",
	"out-of-range",
	"past",
	"placeholder-shown",
	"read-only",
	"read-write",
	"required",
	"right",
	"root",
	"scope",
	"target",
	"target-within",
	"user-invalid",
	"valid",
	"visited",
	"where"
].sort().reverse();
var PSEUDO_ELEMENTS$2 = [
	"after",
	"backdrop",
	"before",
	"cue",
	"cue-region",
	"first-letter",
	"first-line",
	"grammar-error",
	"marker",
	"part",
	"placeholder",
	"selection",
	"slotted",
	"spelling-error"
].sort().reverse();
var ATTRIBUTES$2 = [
	"accent-color",
	"align-content",
	"align-items",
	"align-self",
	"alignment-baseline",
	"all",
	"anchor-name",
	"animation",
	"animation-composition",
	"animation-delay",
	"animation-direction",
	"animation-duration",
	"animation-fill-mode",
	"animation-iteration-count",
	"animation-name",
	"animation-play-state",
	"animation-range",
	"animation-range-end",
	"animation-range-start",
	"animation-timeline",
	"animation-timing-function",
	"appearance",
	"aspect-ratio",
	"backdrop-filter",
	"backface-visibility",
	"background",
	"background-attachment",
	"background-blend-mode",
	"background-clip",
	"background-color",
	"background-image",
	"background-origin",
	"background-position",
	"background-position-x",
	"background-position-y",
	"background-repeat",
	"background-size",
	"baseline-shift",
	"block-size",
	"border",
	"border-block",
	"border-block-color",
	"border-block-end",
	"border-block-end-color",
	"border-block-end-style",
	"border-block-end-width",
	"border-block-start",
	"border-block-start-color",
	"border-block-start-style",
	"border-block-start-width",
	"border-block-style",
	"border-block-width",
	"border-bottom",
	"border-bottom-color",
	"border-bottom-left-radius",
	"border-bottom-right-radius",
	"border-bottom-style",
	"border-bottom-width",
	"border-collapse",
	"border-color",
	"border-end-end-radius",
	"border-end-start-radius",
	"border-image",
	"border-image-outset",
	"border-image-repeat",
	"border-image-slice",
	"border-image-source",
	"border-image-width",
	"border-inline",
	"border-inline-color",
	"border-inline-end",
	"border-inline-end-color",
	"border-inline-end-style",
	"border-inline-end-width",
	"border-inline-start",
	"border-inline-start-color",
	"border-inline-start-style",
	"border-inline-start-width",
	"border-inline-style",
	"border-inline-width",
	"border-left",
	"border-left-color",
	"border-left-style",
	"border-left-width",
	"border-radius",
	"border-right",
	"border-right-color",
	"border-right-style",
	"border-right-width",
	"border-spacing",
	"border-start-end-radius",
	"border-start-start-radius",
	"border-style",
	"border-top",
	"border-top-color",
	"border-top-left-radius",
	"border-top-right-radius",
	"border-top-style",
	"border-top-width",
	"border-width",
	"bottom",
	"box-align",
	"box-decoration-break",
	"box-direction",
	"box-flex",
	"box-flex-group",
	"box-lines",
	"box-ordinal-group",
	"box-orient",
	"box-pack",
	"box-shadow",
	"box-sizing",
	"break-after",
	"break-before",
	"break-inside",
	"caption-side",
	"caret-color",
	"clear",
	"clip",
	"clip-path",
	"clip-rule",
	"color",
	"color-interpolation",
	"color-interpolation-filters",
	"color-profile",
	"color-rendering",
	"color-scheme",
	"column-count",
	"column-fill",
	"column-gap",
	"column-rule",
	"column-rule-color",
	"column-rule-style",
	"column-rule-width",
	"column-span",
	"column-width",
	"columns",
	"contain",
	"contain-intrinsic-block-size",
	"contain-intrinsic-height",
	"contain-intrinsic-inline-size",
	"contain-intrinsic-size",
	"contain-intrinsic-width",
	"container",
	"container-name",
	"container-type",
	"content",
	"content-visibility",
	"counter-increment",
	"counter-reset",
	"counter-set",
	"cue",
	"cue-after",
	"cue-before",
	"cursor",
	"cx",
	"cy",
	"direction",
	"display",
	"dominant-baseline",
	"empty-cells",
	"enable-background",
	"field-sizing",
	"fill",
	"fill-opacity",
	"fill-rule",
	"filter",
	"flex",
	"flex-basis",
	"flex-direction",
	"flex-flow",
	"flex-grow",
	"flex-shrink",
	"flex-wrap",
	"float",
	"flood-color",
	"flood-opacity",
	"flow",
	"font",
	"font-display",
	"font-family",
	"font-feature-settings",
	"font-kerning",
	"font-language-override",
	"font-optical-sizing",
	"font-palette",
	"font-size",
	"font-size-adjust",
	"font-smooth",
	"font-smoothing",
	"font-stretch",
	"font-style",
	"font-synthesis",
	"font-synthesis-position",
	"font-synthesis-small-caps",
	"font-synthesis-style",
	"font-synthesis-weight",
	"font-variant",
	"font-variant-alternates",
	"font-variant-caps",
	"font-variant-east-asian",
	"font-variant-emoji",
	"font-variant-ligatures",
	"font-variant-numeric",
	"font-variant-position",
	"font-variation-settings",
	"font-weight",
	"forced-color-adjust",
	"gap",
	"glyph-orientation-horizontal",
	"glyph-orientation-vertical",
	"grid",
	"grid-area",
	"grid-auto-columns",
	"grid-auto-flow",
	"grid-auto-rows",
	"grid-column",
	"grid-column-end",
	"grid-column-start",
	"grid-gap",
	"grid-row",
	"grid-row-end",
	"grid-row-start",
	"grid-template",
	"grid-template-areas",
	"grid-template-columns",
	"grid-template-rows",
	"hanging-punctuation",
	"height",
	"hyphenate-character",
	"hyphenate-limit-chars",
	"hyphens",
	"icon",
	"image-orientation",
	"image-rendering",
	"image-resolution",
	"ime-mode",
	"initial-letter",
	"initial-letter-align",
	"inline-size",
	"inset",
	"inset-area",
	"inset-block",
	"inset-block-end",
	"inset-block-start",
	"inset-inline",
	"inset-inline-end",
	"inset-inline-start",
	"isolation",
	"justify-content",
	"justify-items",
	"justify-self",
	"kerning",
	"left",
	"letter-spacing",
	"lighting-color",
	"line-break",
	"line-height",
	"line-height-step",
	"list-style",
	"list-style-image",
	"list-style-position",
	"list-style-type",
	"margin",
	"margin-block",
	"margin-block-end",
	"margin-block-start",
	"margin-bottom",
	"margin-inline",
	"margin-inline-end",
	"margin-inline-start",
	"margin-left",
	"margin-right",
	"margin-top",
	"margin-trim",
	"marker",
	"marker-end",
	"marker-mid",
	"marker-start",
	"marks",
	"mask",
	"mask-border",
	"mask-border-mode",
	"mask-border-outset",
	"mask-border-repeat",
	"mask-border-slice",
	"mask-border-source",
	"mask-border-width",
	"mask-clip",
	"mask-composite",
	"mask-image",
	"mask-mode",
	"mask-origin",
	"mask-position",
	"mask-repeat",
	"mask-size",
	"mask-type",
	"masonry-auto-flow",
	"math-depth",
	"math-shift",
	"math-style",
	"max-block-size",
	"max-height",
	"max-inline-size",
	"max-width",
	"min-block-size",
	"min-height",
	"min-inline-size",
	"min-width",
	"mix-blend-mode",
	"nav-down",
	"nav-index",
	"nav-left",
	"nav-right",
	"nav-up",
	"none",
	"normal",
	"object-fit",
	"object-position",
	"offset",
	"offset-anchor",
	"offset-distance",
	"offset-path",
	"offset-position",
	"offset-rotate",
	"opacity",
	"order",
	"orphans",
	"outline",
	"outline-color",
	"outline-offset",
	"outline-style",
	"outline-width",
	"overflow",
	"overflow-anchor",
	"overflow-block",
	"overflow-clip-margin",
	"overflow-inline",
	"overflow-wrap",
	"overflow-x",
	"overflow-y",
	"overlay",
	"overscroll-behavior",
	"overscroll-behavior-block",
	"overscroll-behavior-inline",
	"overscroll-behavior-x",
	"overscroll-behavior-y",
	"padding",
	"padding-block",
	"padding-block-end",
	"padding-block-start",
	"padding-bottom",
	"padding-inline",
	"padding-inline-end",
	"padding-inline-start",
	"padding-left",
	"padding-right",
	"padding-top",
	"page",
	"page-break-after",
	"page-break-before",
	"page-break-inside",
	"paint-order",
	"pause",
	"pause-after",
	"pause-before",
	"perspective",
	"perspective-origin",
	"place-content",
	"place-items",
	"place-self",
	"pointer-events",
	"position",
	"position-anchor",
	"position-visibility",
	"print-color-adjust",
	"quotes",
	"r",
	"resize",
	"rest",
	"rest-after",
	"rest-before",
	"right",
	"rotate",
	"row-gap",
	"ruby-align",
	"ruby-position",
	"scale",
	"scroll-behavior",
	"scroll-margin",
	"scroll-margin-block",
	"scroll-margin-block-end",
	"scroll-margin-block-start",
	"scroll-margin-bottom",
	"scroll-margin-inline",
	"scroll-margin-inline-end",
	"scroll-margin-inline-start",
	"scroll-margin-left",
	"scroll-margin-right",
	"scroll-margin-top",
	"scroll-padding",
	"scroll-padding-block",
	"scroll-padding-block-end",
	"scroll-padding-block-start",
	"scroll-padding-bottom",
	"scroll-padding-inline",
	"scroll-padding-inline-end",
	"scroll-padding-inline-start",
	"scroll-padding-left",
	"scroll-padding-right",
	"scroll-padding-top",
	"scroll-snap-align",
	"scroll-snap-stop",
	"scroll-snap-type",
	"scroll-timeline",
	"scroll-timeline-axis",
	"scroll-timeline-name",
	"scrollbar-color",
	"scrollbar-gutter",
	"scrollbar-width",
	"shape-image-threshold",
	"shape-margin",
	"shape-outside",
	"shape-rendering",
	"speak",
	"speak-as",
	"src",
	"stop-color",
	"stop-opacity",
	"stroke",
	"stroke-dasharray",
	"stroke-dashoffset",
	"stroke-linecap",
	"stroke-linejoin",
	"stroke-miterlimit",
	"stroke-opacity",
	"stroke-width",
	"tab-size",
	"table-layout",
	"text-align",
	"text-align-all",
	"text-align-last",
	"text-anchor",
	"text-combine-upright",
	"text-decoration",
	"text-decoration-color",
	"text-decoration-line",
	"text-decoration-skip",
	"text-decoration-skip-ink",
	"text-decoration-style",
	"text-decoration-thickness",
	"text-emphasis",
	"text-emphasis-color",
	"text-emphasis-position",
	"text-emphasis-style",
	"text-indent",
	"text-justify",
	"text-orientation",
	"text-overflow",
	"text-rendering",
	"text-shadow",
	"text-size-adjust",
	"text-transform",
	"text-underline-offset",
	"text-underline-position",
	"text-wrap",
	"text-wrap-mode",
	"text-wrap-style",
	"timeline-scope",
	"top",
	"touch-action",
	"transform",
	"transform-box",
	"transform-origin",
	"transform-style",
	"transition",
	"transition-behavior",
	"transition-delay",
	"transition-duration",
	"transition-property",
	"transition-timing-function",
	"translate",
	"unicode-bidi",
	"user-modify",
	"user-select",
	"vector-effect",
	"vertical-align",
	"view-timeline",
	"view-timeline-axis",
	"view-timeline-inset",
	"view-timeline-name",
	"view-transition-name",
	"visibility",
	"voice-balance",
	"voice-duration",
	"voice-family",
	"voice-pitch",
	"voice-range",
	"voice-rate",
	"voice-stress",
	"voice-volume",
	"white-space",
	"white-space-collapse",
	"widows",
	"width",
	"will-change",
	"word-break",
	"word-spacing",
	"word-wrap",
	"writing-mode",
	"x",
	"y",
	"z-index",
	"zoom"
].sort().reverse();
/** @type LanguageFn */
function css(hljs) {
	const regex = hljs.regex;
	const modes = MODES$2(hljs);
	const VENDOR_PREFIX = { begin: /-(webkit|moz|ms|o)-(?=[a-z])/ };
	const AT_MODIFIERS = "and or not only";
	const AT_PROPERTY_RE = /@-?\w[\w]*(-\w+)*/;
	const STRINGS = [hljs.APOS_STRING_MODE, hljs.QUOTE_STRING_MODE];
	return {
		name: "CSS",
		case_insensitive: true,
		illegal: /[=|'\$]/,
		keywords: { keyframePosition: "from to" },
		classNameAliases: { keyframePosition: "selector-tag" },
		contains: [
			modes.BLOCK_COMMENT,
			VENDOR_PREFIX,
			modes.CSS_NUMBER_MODE,
			{
				className: "selector-id",
				begin: /#[A-Za-z0-9_-]+/,
				relevance: 0
			},
			{
				className: "selector-class",
				begin: "\\.[a-zA-Z-][a-zA-Z0-9_-]*",
				relevance: 0
			},
			modes.ATTRIBUTE_SELECTOR_MODE,
			{
				className: "selector-pseudo",
				variants: [{ begin: ":(" + PSEUDO_CLASSES$2.join("|") + ")" }, { begin: ":(:)?(" + PSEUDO_ELEMENTS$2.join("|") + ")" }]
			},
			modes.CSS_VARIABLE,
			{
				className: "attribute",
				begin: "\\b(" + ATTRIBUTES$2.join("|") + ")\\b"
			},
			{
				begin: /:/,
				end: /[;}{]/,
				contains: [
					modes.BLOCK_COMMENT,
					modes.HEXCOLOR,
					modes.IMPORTANT,
					modes.CSS_NUMBER_MODE,
					...STRINGS,
					{
						begin: /(url|data-uri)\(/,
						end: /\)/,
						relevance: 0,
						keywords: { built_in: "url data-uri" },
						contains: [...STRINGS, {
							className: "string",
							begin: /[^)]/,
							endsWithParent: true,
							excludeEnd: true
						}]
					},
					modes.FUNCTION_DISPATCH
				]
			},
			{
				begin: regex.lookahead(/@/),
				end: "[{;]",
				relevance: 0,
				illegal: /:/,
				contains: [{
					className: "keyword",
					begin: AT_PROPERTY_RE
				}, {
					begin: /\s/,
					endsWithParent: true,
					excludeEnd: true,
					relevance: 0,
					keywords: {
						$pattern: /[a-z-]+/,
						keyword: AT_MODIFIERS,
						attribute: MEDIA_FEATURES$2.join(" ")
					},
					contains: [
						{
							begin: /[a-z-]+(?=:)/,
							className: "attribute"
						},
						...STRINGS,
						modes.CSS_NUMBER_MODE
					]
				}]
			},
			{
				className: "selector-tag",
				begin: "\\b(" + TAGS$2.join("|") + ")\\b"
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/diff.js
/** @type LanguageFn */
function diff(hljs) {
	const regex = hljs.regex;
	return {
		name: "Diff",
		aliases: ["patch"],
		contains: [
			{
				className: "meta",
				relevance: 10,
				match: regex.either(/^@@ +-\d+,\d+ +\+\d+,\d+ +@@/, /^\*\*\* +\d+,\d+ +\*\*\*\*$/, /^--- +\d+,\d+ +----$/)
			},
			{
				className: "comment",
				variants: [{
					begin: regex.either(/Index: /, /^index/, /={3,}/, /^-{3}/, /^\*{3} /, /^\+{3}/, /^diff --git/),
					end: /$/
				}, { match: /^\*{15}$/ }]
			},
			{
				className: "addition",
				begin: /^\+/,
				end: /$/
			},
			{
				className: "deletion",
				begin: /^-/,
				end: /$/
			},
			{
				className: "addition",
				begin: /^!/,
				end: /$/
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/go.js
function go(hljs) {
	const KEYWORDS = {
		keyword: [
			"break",
			"case",
			"chan",
			"const",
			"continue",
			"default",
			"defer",
			"else",
			"fallthrough",
			"for",
			"func",
			"go",
			"goto",
			"if",
			"import",
			"interface",
			"map",
			"package",
			"range",
			"return",
			"select",
			"struct",
			"switch",
			"type",
			"var"
		],
		type: [
			"bool",
			"byte",
			"complex64",
			"complex128",
			"error",
			"float32",
			"float64",
			"int8",
			"int16",
			"int32",
			"int64",
			"string",
			"uint8",
			"uint16",
			"uint32",
			"uint64",
			"int",
			"uint",
			"uintptr",
			"rune"
		],
		literal: [
			"true",
			"false",
			"iota",
			"nil"
		],
		built_in: [
			"append",
			"cap",
			"close",
			"complex",
			"copy",
			"imag",
			"len",
			"make",
			"new",
			"panic",
			"print",
			"println",
			"real",
			"recover",
			"delete"
		]
	};
	return {
		name: "Go",
		aliases: ["golang"],
		keywords: KEYWORDS,
		illegal: "</",
		contains: [
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			{
				className: "string",
				variants: [
					hljs.QUOTE_STRING_MODE,
					hljs.APOS_STRING_MODE,
					{
						begin: "`",
						end: "`"
					}
				]
			},
			{
				className: "number",
				variants: [
					{
						match: /-?\b0[xX]\.[a-fA-F0-9](_?[a-fA-F0-9])*[pP][+-]?\d(_?\d)*i?/,
						relevance: 0
					},
					{
						match: /-?\b0[xX](_?[a-fA-F0-9])+((\.([a-fA-F0-9](_?[a-fA-F0-9])*)?)?[pP][+-]?\d(_?\d)*)?i?/,
						relevance: 0
					},
					{
						match: /-?\b0[oO](_?[0-7])*i?/,
						relevance: 0
					},
					{
						match: /-?\.\d(_?\d)*([eE][+-]?\d(_?\d)*)?i?/,
						relevance: 0
					},
					{
						match: /-?\b\d(_?\d)*(\.(\d(_?\d)*)?)?([eE][+-]?\d(_?\d)*)?i?/,
						relevance: 0
					}
				]
			},
			{ begin: /:=/ },
			{
				className: "function",
				beginKeywords: "func",
				end: "\\s*(\\{|$)",
				excludeEnd: true,
				contains: [hljs.TITLE_MODE, {
					className: "params",
					begin: /\(/,
					end: /\)/,
					endsParent: true,
					keywords: KEYWORDS,
					illegal: /["']/
				}]
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/graphql.js
/** @type LanguageFn */
function graphql(hljs) {
	const regex = hljs.regex;
	return {
		name: "GraphQL",
		aliases: ["gql"],
		case_insensitive: true,
		disableAutodetect: false,
		keywords: {
			keyword: [
				"query",
				"mutation",
				"subscription",
				"type",
				"input",
				"schema",
				"directive",
				"interface",
				"union",
				"scalar",
				"fragment",
				"enum",
				"on"
			],
			literal: [
				"true",
				"false",
				"null"
			]
		},
		contains: [
			hljs.HASH_COMMENT_MODE,
			hljs.QUOTE_STRING_MODE,
			hljs.NUMBER_MODE,
			{
				scope: "punctuation",
				match: /[.]{3}/,
				relevance: 0
			},
			{
				scope: "punctuation",
				begin: /[\!\(\)\:\=\[\]\{\|\}]{1}/,
				relevance: 0
			},
			{
				scope: "variable",
				begin: /\$/,
				end: /\W/,
				excludeEnd: true,
				relevance: 0
			},
			{
				scope: "meta",
				match: /@\w+/,
				excludeEnd: true
			},
			{
				scope: "symbol",
				begin: regex.concat(/[_A-Za-z][_0-9A-Za-z]*/, regex.lookahead(/\s*:/)),
				relevance: 0
			}
		],
		illegal: [/[;<']/, /BEGIN/]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/ini.js
function ini(hljs) {
	const regex = hljs.regex;
	const NUMBERS = {
		className: "number",
		relevance: 0,
		variants: [{ begin: /([+-]+)?[\d]+_[\d_]+/ }, { begin: hljs.NUMBER_RE }]
	};
	const COMMENTS = hljs.COMMENT();
	COMMENTS.variants = [{
		begin: /;/,
		end: /$/
	}, {
		begin: /#/,
		end: /$/
	}];
	const VARIABLES = {
		className: "variable",
		variants: [{ begin: /\$[\w\d"][\w\d_]*/ }, { begin: /\$\{(.*?)\}/ }]
	};
	const LITERALS = {
		className: "literal",
		begin: /\bon|off|true|false|yes|no\b/
	};
	const STRINGS = {
		className: "string",
		contains: [hljs.BACKSLASH_ESCAPE],
		variants: [
			{
				begin: "'''",
				end: "'''",
				relevance: 10
			},
			{
				begin: "\"\"\"",
				end: "\"\"\"",
				relevance: 10
			},
			{
				begin: "\"",
				end: "\""
			},
			{
				begin: "'",
				end: "'"
			}
		]
	};
	const ARRAY = {
		begin: /\[/,
		end: /\]/,
		contains: [
			COMMENTS,
			LITERALS,
			VARIABLES,
			STRINGS,
			NUMBERS,
			"self"
		],
		relevance: 0
	};
	const ANY_KEY = regex.either(/[A-Za-z0-9_-]+/, /"(\\"|[^"])*"/, /'[^']*'/);
	return {
		name: "TOML, also INI",
		aliases: ["toml"],
		case_insensitive: true,
		illegal: /\S/,
		contains: [
			COMMENTS,
			{
				className: "section",
				begin: /\[+/,
				end: /\]+/
			},
			{
				begin: regex.concat(ANY_KEY, "(\\s*\\.\\s*", ANY_KEY, ")*", regex.lookahead(/\s*=\s*[^#\s]/)),
				className: "attr",
				starts: {
					end: /$/,
					contains: [
						COMMENTS,
						ARRAY,
						LITERALS,
						VARIABLES,
						STRINGS,
						NUMBERS
					]
				}
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/java.js
var decimalDigits$1 = "[0-9](_*[0-9])*";
var frac$1 = `\\.(${decimalDigits$1})`;
var hexDigits$1 = "[0-9a-fA-F](_*[0-9a-fA-F])*";
var NUMERIC$1 = {
	className: "number",
	variants: [
		{ begin: `(\\b(${decimalDigits$1})((${frac$1})|\\.)?|(${frac$1}))[eE][+-]?(${decimalDigits$1})[fFdD]?\\b` },
		{ begin: `\\b(${decimalDigits$1})((${frac$1})[fFdD]?\\b|\\.([fFdD]\\b)?)` },
		{ begin: `(${frac$1})[fFdD]?\\b` },
		{ begin: `\\b(${decimalDigits$1})[fFdD]\\b` },
		{ begin: `\\b0[xX]((${hexDigits$1})\\.?|(${hexDigits$1})?\\.(${hexDigits$1}))[pP][+-]?(${decimalDigits$1})[fFdD]?\\b` },
		{ begin: "\\b(0|[1-9](_*[0-9])*)[lL]?\\b" },
		{ begin: `\\b0[xX](${hexDigits$1})[lL]?\\b` },
		{ begin: "\\b0(_*[0-7])*[lL]?\\b" },
		{ begin: "\\b0[bB][01](_*[01])*[lL]?\\b" }
	],
	relevance: 0
};
/**
* Allows recursive regex expressions to a given depth
*
* ie: recurRegex("(abc~~~)", /~~~/g, 2) becomes:
* (abc(abc(abc)))
*
* @param {string} re
* @param {RegExp} substitution (should be a g mode regex)
* @param {number} depth
* @returns {string}``
*/
function recurRegex(re, substitution, depth) {
	if (depth === -1) return "";
	return re.replace(substitution, (_) => {
		return recurRegex(re, substitution, depth - 1);
	});
}
/** @type LanguageFn */
function java(hljs) {
	const regex = hljs.regex;
	const JAVA_IDENT_RE = "[À-ʸa-zA-Z_$][À-ʸa-zA-Z_$0-9]*";
	const GENERIC_IDENT_RE = JAVA_IDENT_RE + recurRegex("(?:<[À-ʸa-zA-Z_$][À-ʸa-zA-Z_$0-9]*~~~(?:\\s*,\\s*[À-ʸa-zA-Z_$][À-ʸa-zA-Z_$0-9]*~~~)*>)?", /~~~/g, 2);
	const KEYWORDS = {
		keyword: [
			"synchronized",
			"abstract",
			"private",
			"var",
			"static",
			"if",
			"const ",
			"for",
			"while",
			"strictfp",
			"finally",
			"protected",
			"import",
			"native",
			"final",
			"void",
			"enum",
			"else",
			"break",
			"transient",
			"catch",
			"instanceof",
			"volatile",
			"case",
			"assert",
			"package",
			"default",
			"public",
			"try",
			"switch",
			"continue",
			"throws",
			"protected",
			"public",
			"private",
			"module",
			"requires",
			"exports",
			"do",
			"sealed",
			"yield",
			"permits",
			"goto",
			"when"
		],
		literal: [
			"false",
			"true",
			"null"
		],
		type: [
			"char",
			"boolean",
			"long",
			"float",
			"int",
			"byte",
			"short",
			"double"
		],
		built_in: ["super", "this"]
	};
	const ANNOTATION = {
		className: "meta",
		begin: "@[À-ʸa-zA-Z_$][À-ʸa-zA-Z_$0-9]*",
		contains: [{
			begin: /\(/,
			end: /\)/,
			contains: ["self"]
		}]
	};
	const PARAMS = {
		className: "params",
		begin: /\(/,
		end: /\)/,
		keywords: KEYWORDS,
		relevance: 0,
		contains: [hljs.C_BLOCK_COMMENT_MODE],
		endsParent: true
	};
	return {
		name: "Java",
		aliases: ["jsp"],
		keywords: KEYWORDS,
		illegal: /<\/|#/,
		contains: [
			hljs.COMMENT("/\\*\\*", "\\*/", {
				relevance: 0,
				contains: [{
					begin: /\w+@/,
					relevance: 0
				}, {
					className: "doctag",
					begin: "@[A-Za-z]+"
				}]
			}),
			{
				begin: /import java\.[a-z]+\./,
				keywords: "import",
				relevance: 2
			},
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			{
				begin: /"""/,
				end: /"""/,
				className: "string",
				contains: [hljs.BACKSLASH_ESCAPE]
			},
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE,
			{
				match: [
					/\b(?:class|interface|enum|extends|implements|new)/,
					/\s+/,
					JAVA_IDENT_RE
				],
				className: {
					1: "keyword",
					3: "title.class"
				}
			},
			{
				match: /non-sealed/,
				scope: "keyword"
			},
			{
				begin: [
					regex.concat(/(?!else)/, JAVA_IDENT_RE),
					/\s+/,
					JAVA_IDENT_RE,
					/\s+/,
					/=(?!=)/
				],
				className: {
					1: "type",
					3: "variable",
					5: "operator"
				}
			},
			{
				begin: [
					/record/,
					/\s+/,
					JAVA_IDENT_RE
				],
				className: {
					1: "keyword",
					3: "title.class"
				},
				contains: [
					PARAMS,
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE
				]
			},
			{
				beginKeywords: "new throw return else",
				relevance: 0
			},
			{
				begin: [
					"(?:" + GENERIC_IDENT_RE + "\\s+)",
					hljs.UNDERSCORE_IDENT_RE,
					/\s*(?=\()/
				],
				className: { 2: "title.function" },
				keywords: KEYWORDS,
				contains: [
					{
						className: "params",
						begin: /\(/,
						end: /\)/,
						keywords: KEYWORDS,
						relevance: 0,
						contains: [
							ANNOTATION,
							hljs.APOS_STRING_MODE,
							hljs.QUOTE_STRING_MODE,
							NUMERIC$1,
							hljs.C_BLOCK_COMMENT_MODE
						]
					},
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE
				]
			},
			NUMERIC$1,
			ANNOTATION
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/javascript.js
var IDENT_RE$2 = "[A-Za-z$_][0-9A-Za-z$_]*";
var KEYWORDS$2 = [
	"as",
	"in",
	"of",
	"if",
	"for",
	"while",
	"finally",
	"var",
	"new",
	"function",
	"do",
	"return",
	"void",
	"else",
	"break",
	"catch",
	"instanceof",
	"with",
	"throw",
	"case",
	"default",
	"try",
	"switch",
	"continue",
	"typeof",
	"delete",
	"let",
	"yield",
	"const",
	"class",
	"debugger",
	"async",
	"await",
	"static",
	"import",
	"from",
	"export",
	"extends",
	"using"
];
var LITERALS$1 = [
	"true",
	"false",
	"null",
	"undefined",
	"NaN",
	"Infinity"
];
var TYPES$1 = [
	"Object",
	"Function",
	"Boolean",
	"Symbol",
	"Math",
	"Date",
	"Number",
	"BigInt",
	"String",
	"RegExp",
	"Array",
	"Float32Array",
	"Float64Array",
	"Int8Array",
	"Uint8Array",
	"Uint8ClampedArray",
	"Int16Array",
	"Int32Array",
	"Uint16Array",
	"Uint32Array",
	"BigInt64Array",
	"BigUint64Array",
	"Set",
	"Map",
	"WeakSet",
	"WeakMap",
	"ArrayBuffer",
	"SharedArrayBuffer",
	"Atomics",
	"DataView",
	"JSON",
	"Promise",
	"Generator",
	"GeneratorFunction",
	"AsyncFunction",
	"Reflect",
	"Proxy",
	"Intl",
	"WebAssembly"
];
var ERROR_TYPES$1 = [
	"Error",
	"EvalError",
	"InternalError",
	"RangeError",
	"ReferenceError",
	"SyntaxError",
	"TypeError",
	"URIError"
];
var BUILT_IN_GLOBALS$1 = [
	"setInterval",
	"setTimeout",
	"clearInterval",
	"clearTimeout",
	"require",
	"exports",
	"eval",
	"isFinite",
	"isNaN",
	"parseFloat",
	"parseInt",
	"decodeURI",
	"decodeURIComponent",
	"encodeURI",
	"encodeURIComponent",
	"escape",
	"unescape"
];
var BUILT_IN_VARIABLES$1 = [
	"arguments",
	"this",
	"super",
	"console",
	"window",
	"document",
	"localStorage",
	"sessionStorage",
	"module",
	"global"
];
var BUILT_INS$1 = [].concat(BUILT_IN_GLOBALS$1, TYPES$1, ERROR_TYPES$1);
/** @type LanguageFn */
function javascript$1(hljs) {
	const regex = hljs.regex;
	/**
	* Takes a string like "<Booger" and checks to see
	* if we can find a matching "</Booger" later in the
	* content.
	* @param {RegExpMatchArray} match
	* @param {{after:number}} param1
	*/
	const hasClosingTag = (match, { after }) => {
		const tag = "</" + match[0].slice(1);
		return match.input.indexOf(tag, after) !== -1;
	};
	const IDENT_RE$1 = IDENT_RE$2;
	const FRAGMENT = {
		begin: "<>",
		end: "</>"
	};
	const XML_SELF_CLOSING = /<[A-Za-z0-9\\._:-]+\s*\/>/;
	const XML_TAG = {
		begin: /<[A-Za-z0-9\\._:-]+/,
		end: /\/[A-Za-z0-9\\._:-]+>|\/>/,
		/**
		* @param {RegExpMatchArray} match
		* @param {CallbackResponse} response
		*/
		isTrulyOpeningTag: (match, response) => {
			const afterMatchIndex = match[0].length + match.index;
			const nextChar = match.input[afterMatchIndex];
			if (nextChar === "<" || nextChar === ",") {
				response.ignoreMatch();
				return;
			}
			if (nextChar === ">") {
				if (!hasClosingTag(match, { after: afterMatchIndex })) response.ignoreMatch();
			}
			let m;
			const afterMatch = match.input.substring(afterMatchIndex);
			if (m = afterMatch.match(/^\s*=/)) {
				response.ignoreMatch();
				return;
			}
			if (m = afterMatch.match(/^\s+extends\s+/)) {
				if (m.index === 0) {
					response.ignoreMatch();
					return;
				}
			}
		}
	};
	const KEYWORDS$1 = {
		$pattern: IDENT_RE$2,
		keyword: KEYWORDS$2,
		literal: LITERALS$1,
		built_in: BUILT_INS$1,
		"variable.language": BUILT_IN_VARIABLES$1
	};
	const decimalDigits = "[0-9](_?[0-9])*";
	const frac = `\\.(${decimalDigits})`;
	const decimalInteger = `0|[1-9](_?[0-9])*|0[0-7]*[89][0-9]*`;
	const NUMBER = {
		className: "number",
		variants: [
			{ begin: `(\\b(${decimalInteger})((${frac})|\\.)?|(${frac}))[eE][+-]?(${decimalDigits})\\b` },
			{ begin: `\\b(${decimalInteger})\\b((${frac})\\b|\\.)?|(${frac})\\b` },
			{ begin: `\\b(0|[1-9](_?[0-9])*)n\\b` },
			{ begin: "\\b0[xX][0-9a-fA-F](_?[0-9a-fA-F])*n?\\b" },
			{ begin: "\\b0[bB][0-1](_?[0-1])*n?\\b" },
			{ begin: "\\b0[oO][0-7](_?[0-7])*n?\\b" },
			{ begin: "\\b0[0-7]+n?\\b" }
		],
		relevance: 0
	};
	const SUBST = {
		className: "subst",
		begin: "\\$\\{",
		end: "\\}",
		keywords: KEYWORDS$1,
		contains: []
	};
	const HTML_TEMPLATE = {
		begin: ".?html`",
		end: "",
		starts: {
			end: "`",
			returnEnd: false,
			contains: [hljs.BACKSLASH_ESCAPE, SUBST],
			subLanguage: "xml"
		}
	};
	const CSS_TEMPLATE = {
		begin: ".?css`",
		end: "",
		starts: {
			end: "`",
			returnEnd: false,
			contains: [hljs.BACKSLASH_ESCAPE, SUBST],
			subLanguage: "css"
		}
	};
	const GRAPHQL_TEMPLATE = {
		begin: ".?gql`",
		end: "",
		starts: {
			end: "`",
			returnEnd: false,
			contains: [hljs.BACKSLASH_ESCAPE, SUBST],
			subLanguage: "graphql"
		}
	};
	const TEMPLATE_STRING = {
		className: "string",
		begin: "`",
		end: "`",
		contains: [hljs.BACKSLASH_ESCAPE, SUBST]
	};
	const COMMENT = {
		className: "comment",
		variants: [
			hljs.COMMENT(/\/\*\*(?!\/)/, "\\*/", {
				relevance: 0,
				contains: [{
					begin: "(?=@[A-Za-z]+)",
					relevance: 0,
					contains: [
						{
							className: "doctag",
							begin: "@[A-Za-z]+"
						},
						{
							className: "type",
							begin: "\\{",
							end: "\\}",
							excludeEnd: true,
							excludeBegin: true,
							relevance: 0
						},
						{
							className: "variable",
							begin: "[A-Za-z$_][0-9A-Za-z$_]*(?=\\s*(-)|$)",
							endsParent: true,
							relevance: 0
						},
						{
							begin: /(?=[^\n])\s/,
							relevance: 0
						}
					]
				}]
			}),
			hljs.C_BLOCK_COMMENT_MODE,
			hljs.C_LINE_COMMENT_MODE
		]
	};
	const SUBST_INTERNALS = [
		hljs.APOS_STRING_MODE,
		hljs.QUOTE_STRING_MODE,
		HTML_TEMPLATE,
		CSS_TEMPLATE,
		GRAPHQL_TEMPLATE,
		TEMPLATE_STRING,
		{ match: /\$\d+/ },
		NUMBER
	];
	SUBST.contains = SUBST_INTERNALS.concat({
		begin: /\{/,
		end: /\}/,
		keywords: KEYWORDS$1,
		contains: ["self"].concat(SUBST_INTERNALS)
	});
	const SUBST_AND_COMMENTS = [].concat(COMMENT, SUBST.contains);
	const PARAMS_CONTAINS = SUBST_AND_COMMENTS.concat([{
		begin: /(\s*)\(/,
		end: /\)/,
		keywords: KEYWORDS$1,
		contains: ["self"].concat(SUBST_AND_COMMENTS)
	}]);
	const PARAMS = {
		className: "params",
		begin: /(\s*)\(/,
		end: /\)/,
		excludeBegin: true,
		excludeEnd: true,
		keywords: KEYWORDS$1,
		contains: PARAMS_CONTAINS
	};
	const CLASS_OR_EXTENDS = { variants: [{
		match: [
			/class/,
			/\s+/,
			IDENT_RE$1,
			/\s+/,
			/extends/,
			/\s+/,
			regex.concat(IDENT_RE$1, "(", regex.concat(/\./, IDENT_RE$1), ")*")
		],
		scope: {
			1: "keyword",
			3: "title.class",
			5: "keyword",
			7: "title.class.inherited"
		}
	}, {
		match: [
			/class/,
			/\s+/,
			IDENT_RE$1
		],
		scope: {
			1: "keyword",
			3: "title.class"
		}
	}] };
	const CLASS_REFERENCE = {
		relevance: 0,
		match: regex.either(/\bJSON/, /\b[A-Z][a-z]+([A-Z][a-z]*|\d)*/, /\b[A-Z]{2,}([A-Z][a-z]+|\d)+([A-Z][a-z]*)*/, /\b[A-Z]{2,}[a-z]+([A-Z][a-z]+|\d)*([A-Z][a-z]*)*/),
		className: "title.class",
		keywords: { _: [...TYPES$1, ...ERROR_TYPES$1] }
	};
	const USE_STRICT = {
		label: "use_strict",
		className: "meta",
		relevance: 10,
		begin: /^\s*['"]use (strict|asm)['"]/
	};
	const FUNCTION_DEFINITION = {
		variants: [{ match: [
			/function/,
			/\s+/,
			IDENT_RE$1,
			/(?=\s*\()/
		] }, { match: [/function/, /\s*(?=\()/] }],
		className: {
			1: "keyword",
			3: "title.function"
		},
		label: "func.def",
		contains: [PARAMS],
		illegal: /%/
	};
	const UPPER_CASE_CONSTANT = {
		relevance: 0,
		match: /\b[A-Z][A-Z_0-9]+\b/,
		className: "variable.constant"
	};
	function noneOf(list) {
		return regex.concat("(?!", list.join("|"), ")");
	}
	const FUNCTION_CALL = {
		match: regex.concat(/\b/, noneOf([
			...BUILT_IN_GLOBALS$1,
			"super",
			"import"
		].map((x) => `${x}\\s*\\(`)), IDENT_RE$1, regex.lookahead(/\s*\(/)),
		className: "title.function",
		relevance: 0
	};
	const PROPERTY_ACCESS = {
		begin: regex.concat(/\./, regex.lookahead(regex.concat(IDENT_RE$1, /(?![0-9A-Za-z$_(])/))),
		end: IDENT_RE$1,
		excludeBegin: true,
		keywords: "prototype",
		className: "property",
		relevance: 0
	};
	const GETTER_OR_SETTER = {
		match: [
			/get|set/,
			/\s+/,
			IDENT_RE$1,
			/(?=\()/
		],
		className: {
			1: "keyword",
			3: "title.function"
		},
		contains: [{ begin: /\(\)/ }, PARAMS]
	};
	const FUNC_LEAD_IN_RE = "(\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)|" + hljs.UNDERSCORE_IDENT_RE + ")\\s*=>";
	const FUNCTION_VARIABLE = {
		match: [
			/const|var|let/,
			/\s+/,
			IDENT_RE$1,
			/\s*/,
			/=\s*/,
			/(async\s*)?/,
			regex.lookahead(FUNC_LEAD_IN_RE)
		],
		keywords: "async",
		className: {
			1: "keyword",
			3: "title.function"
		},
		contains: [PARAMS]
	};
	return {
		name: "JavaScript",
		aliases: [
			"js",
			"jsx",
			"mjs",
			"cjs"
		],
		keywords: KEYWORDS$1,
		exports: {
			PARAMS_CONTAINS,
			CLASS_REFERENCE
		},
		illegal: /#(?![$_A-z])/,
		contains: [
			hljs.SHEBANG({
				label: "shebang",
				binary: "node",
				relevance: 5
			}),
			USE_STRICT,
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE,
			HTML_TEMPLATE,
			CSS_TEMPLATE,
			GRAPHQL_TEMPLATE,
			TEMPLATE_STRING,
			COMMENT,
			{ match: /\$\d+/ },
			NUMBER,
			CLASS_REFERENCE,
			{
				scope: "attr",
				match: IDENT_RE$1 + regex.lookahead(":"),
				relevance: 0
			},
			FUNCTION_VARIABLE,
			{
				begin: "(" + hljs.RE_STARTERS_RE + "|\\b(case|return|throw)\\b)\\s*",
				keywords: "return throw case",
				relevance: 0,
				contains: [
					COMMENT,
					hljs.REGEXP_MODE,
					{
						className: "function",
						begin: FUNC_LEAD_IN_RE,
						returnBegin: true,
						end: "\\s*=>",
						contains: [{
							className: "params",
							variants: [
								{
									begin: hljs.UNDERSCORE_IDENT_RE,
									relevance: 0
								},
								{
									className: null,
									begin: /\(\s*\)/,
									skip: true
								},
								{
									begin: /(\s*)\(/,
									end: /\)/,
									excludeBegin: true,
									excludeEnd: true,
									keywords: KEYWORDS$1,
									contains: PARAMS_CONTAINS
								}
							]
						}]
					},
					{
						begin: /,/,
						relevance: 0
					},
					{
						match: /\s+/,
						relevance: 0
					},
					{
						variants: [
							{
								begin: FRAGMENT.begin,
								end: FRAGMENT.end
							},
							{ match: XML_SELF_CLOSING },
							{
								begin: XML_TAG.begin,
								"on:begin": XML_TAG.isTrulyOpeningTag,
								end: XML_TAG.end
							}
						],
						subLanguage: "xml",
						contains: [{
							begin: XML_TAG.begin,
							end: XML_TAG.end,
							skip: true,
							contains: ["self"]
						}]
					}
				]
			},
			FUNCTION_DEFINITION,
			{ beginKeywords: "while if switch catch for" },
			{
				begin: "\\b(?!function)" + hljs.UNDERSCORE_IDENT_RE + "\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)\\s*\\{",
				returnBegin: true,
				label: "func.def",
				contains: [PARAMS, hljs.inherit(hljs.TITLE_MODE, {
					begin: IDENT_RE$1,
					className: "title.function"
				})]
			},
			{
				match: /\.\.\./,
				relevance: 0
			},
			PROPERTY_ACCESS,
			{
				match: "\\$[A-Za-z$_][0-9A-Za-z$_]*",
				relevance: 0
			},
			{
				match: [/\bconstructor(?=\s*\()/],
				className: { 1: "title.function" },
				contains: [PARAMS]
			},
			FUNCTION_CALL,
			UPPER_CASE_CONSTANT,
			CLASS_OR_EXTENDS,
			GETTER_OR_SETTER,
			{ match: /\$[(.]/ }
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/json.js
function json(hljs) {
	const ATTRIBUTE = {
		className: "attr",
		begin: /"(\\.|[^\\"\r\n])*"(?=\s*:)/,
		relevance: 1.01
	};
	const PUNCTUATION = {
		match: /[{}[\],:]/,
		className: "punctuation",
		relevance: 0
	};
	const LITERALS = [
		"true",
		"false",
		"null"
	];
	const LITERALS_MODE = {
		scope: "literal",
		beginKeywords: LITERALS.join(" ")
	};
	return {
		name: "JSON",
		aliases: ["jsonc"],
		keywords: { literal: LITERALS },
		contains: [
			ATTRIBUTE,
			PUNCTUATION,
			hljs.QUOTE_STRING_MODE,
			LITERALS_MODE,
			hljs.C_NUMBER_MODE,
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE
		],
		illegal: "\\S"
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/kotlin.js
var decimalDigits = "[0-9](_*[0-9])*";
var frac = `\\.(${decimalDigits})`;
var hexDigits = "[0-9a-fA-F](_*[0-9a-fA-F])*";
var NUMERIC = {
	className: "number",
	variants: [
		{ begin: `(\\b(${decimalDigits})((${frac})|\\.)?|(${frac}))[eE][+-]?(${decimalDigits})[fFdD]?\\b` },
		{ begin: `\\b(${decimalDigits})((${frac})[fFdD]?\\b|\\.([fFdD]\\b)?)` },
		{ begin: `(${frac})[fFdD]?\\b` },
		{ begin: `\\b(${decimalDigits})[fFdD]\\b` },
		{ begin: `\\b0[xX]((${hexDigits})\\.?|(${hexDigits})?\\.(${hexDigits}))[pP][+-]?(${decimalDigits})[fFdD]?\\b` },
		{ begin: "\\b(0|[1-9](_*[0-9])*)[lL]?\\b" },
		{ begin: `\\b0[xX](${hexDigits})[lL]?\\b` },
		{ begin: "\\b0(_*[0-7])*[lL]?\\b" },
		{ begin: "\\b0[bB][01](_*[01])*[lL]?\\b" }
	],
	relevance: 0
};
function kotlin(hljs) {
	const KEYWORDS = {
		keyword: "abstract as val var vararg get set class object open private protected public noinline crossinline dynamic final enum if else do while for when throw try catch finally import package is in fun override companion reified inline lateinit init interface annotation data sealed internal infix operator out by constructor super tailrec where const inner suspend typealias external expect actual",
		built_in: "Byte Short Char Int Long Boolean Float Double Void Unit Nothing",
		literal: "true false null"
	};
	const KEYWORDS_WITH_LABEL = {
		className: "keyword",
		begin: /\b(break|continue|return|this)\b/,
		starts: { contains: [{
			className: "symbol",
			begin: /@\w+/
		}] }
	};
	const LABEL = {
		className: "symbol",
		begin: hljs.UNDERSCORE_IDENT_RE + "@"
	};
	const SUBST = {
		className: "subst",
		begin: /\$\{/,
		end: /\}/,
		contains: [hljs.C_NUMBER_MODE]
	};
	const VARIABLE = {
		className: "variable",
		begin: "\\$" + hljs.UNDERSCORE_IDENT_RE
	};
	const STRING = {
		className: "string",
		variants: [
			{
				begin: "\"\"\"",
				end: "\"\"\"(?=[^\"])",
				contains: [VARIABLE, SUBST]
			},
			{
				begin: "'",
				end: "'",
				illegal: /\n/,
				contains: [hljs.BACKSLASH_ESCAPE]
			},
			{
				begin: "\"",
				end: "\"",
				illegal: /\n/,
				contains: [
					hljs.BACKSLASH_ESCAPE,
					VARIABLE,
					SUBST
				]
			}
		]
	};
	SUBST.contains.push(STRING);
	const ANNOTATION_USE_SITE = {
		className: "meta",
		begin: "@(?:file|property|field|get|set|receiver|param|setparam|delegate)\\s*:(?:\\s*" + hljs.UNDERSCORE_IDENT_RE + ")?"
	};
	const ANNOTATION = {
		className: "meta",
		begin: "@" + hljs.UNDERSCORE_IDENT_RE,
		contains: [{
			begin: /\(/,
			end: /\)/,
			contains: [hljs.inherit(STRING, { className: "string" }), "self"]
		}]
	};
	const KOTLIN_NUMBER_MODE = NUMERIC;
	const KOTLIN_NESTED_COMMENT = hljs.COMMENT("/\\*", "\\*/", { contains: [hljs.C_BLOCK_COMMENT_MODE] });
	const KOTLIN_PAREN_TYPE = { variants: [{
		className: "type",
		begin: hljs.UNDERSCORE_IDENT_RE
	}, {
		begin: /\(/,
		end: /\)/,
		contains: []
	}] };
	const KOTLIN_PAREN_TYPE2 = KOTLIN_PAREN_TYPE;
	KOTLIN_PAREN_TYPE2.variants[1].contains = [KOTLIN_PAREN_TYPE];
	KOTLIN_PAREN_TYPE.variants[1].contains = [KOTLIN_PAREN_TYPE2];
	return {
		name: "Kotlin",
		aliases: ["kt", "kts"],
		keywords: KEYWORDS,
		contains: [
			hljs.COMMENT("/\\*\\*", "\\*/", {
				relevance: 0,
				contains: [{
					className: "doctag",
					begin: "@[A-Za-z]+"
				}]
			}),
			hljs.C_LINE_COMMENT_MODE,
			KOTLIN_NESTED_COMMENT,
			KEYWORDS_WITH_LABEL,
			LABEL,
			ANNOTATION_USE_SITE,
			ANNOTATION,
			{
				className: "function",
				beginKeywords: "fun",
				end: "[(]|$",
				returnBegin: true,
				excludeEnd: true,
				keywords: KEYWORDS,
				relevance: 5,
				contains: [
					{
						begin: hljs.UNDERSCORE_IDENT_RE + "\\s*\\(",
						returnBegin: true,
						relevance: 0,
						contains: [hljs.UNDERSCORE_TITLE_MODE]
					},
					{
						className: "type",
						begin: /</,
						end: />/,
						keywords: "reified",
						relevance: 0
					},
					{
						className: "params",
						begin: /\(/,
						end: /\)/,
						endsParent: true,
						keywords: KEYWORDS,
						relevance: 0,
						contains: [
							{
								begin: /:/,
								end: /[=,\/]/,
								endsWithParent: true,
								contains: [
									KOTLIN_PAREN_TYPE,
									hljs.C_LINE_COMMENT_MODE,
									KOTLIN_NESTED_COMMENT
								],
								relevance: 0
							},
							hljs.C_LINE_COMMENT_MODE,
							KOTLIN_NESTED_COMMENT,
							ANNOTATION_USE_SITE,
							ANNOTATION,
							STRING,
							hljs.C_NUMBER_MODE
						]
					},
					KOTLIN_NESTED_COMMENT
				]
			},
			{
				begin: [
					/class|interface|trait/,
					/\s+/,
					hljs.UNDERSCORE_IDENT_RE
				],
				beginScope: { 3: "title.class" },
				keywords: "class interface trait",
				end: /[:\{(]|$/,
				excludeEnd: true,
				illegal: "extends implements",
				contains: [
					{ beginKeywords: "public protected internal private constructor" },
					hljs.UNDERSCORE_TITLE_MODE,
					{
						className: "type",
						begin: /</,
						end: />/,
						excludeBegin: true,
						excludeEnd: true,
						relevance: 0
					},
					{
						className: "type",
						begin: /[,:]\s*/,
						end: /[<\(,){\s]|$/,
						excludeBegin: true,
						returnEnd: true
					},
					ANNOTATION_USE_SITE,
					ANNOTATION
				]
			},
			STRING,
			{
				className: "meta",
				begin: "^#!/usr/bin/env",
				end: "$",
				illegal: "\n"
			},
			KOTLIN_NUMBER_MODE
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/less.js
var MODES$1 = (hljs) => {
	return {
		IMPORTANT: {
			scope: "meta",
			begin: "!important"
		},
		BLOCK_COMMENT: hljs.C_BLOCK_COMMENT_MODE,
		HEXCOLOR: {
			scope: "number",
			begin: /#(([0-9a-fA-F]{3,4})|(([0-9a-fA-F]{2}){3,4}))\b/
		},
		FUNCTION_DISPATCH: {
			className: "built_in",
			begin: /[\w-]+(?=\()/
		},
		ATTRIBUTE_SELECTOR_MODE: {
			scope: "selector-attr",
			begin: /\[/,
			end: /\]/,
			illegal: "$",
			contains: [hljs.APOS_STRING_MODE, hljs.QUOTE_STRING_MODE]
		},
		CSS_NUMBER_MODE: {
			scope: "number",
			begin: hljs.NUMBER_RE + "(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",
			relevance: 0
		},
		CSS_VARIABLE: {
			className: "attr",
			begin: /--[A-Za-z_][A-Za-z0-9_-]*/
		}
	};
};
var HTML_TAGS$1 = [
	"a",
	"abbr",
	"address",
	"article",
	"aside",
	"audio",
	"b",
	"blockquote",
	"body",
	"button",
	"canvas",
	"caption",
	"cite",
	"code",
	"dd",
	"del",
	"details",
	"dfn",
	"div",
	"dl",
	"dt",
	"em",
	"fieldset",
	"figcaption",
	"figure",
	"footer",
	"form",
	"h1",
	"h2",
	"h3",
	"h4",
	"h5",
	"h6",
	"header",
	"hgroup",
	"html",
	"i",
	"iframe",
	"img",
	"input",
	"ins",
	"kbd",
	"label",
	"legend",
	"li",
	"main",
	"mark",
	"menu",
	"nav",
	"object",
	"ol",
	"optgroup",
	"option",
	"p",
	"picture",
	"q",
	"quote",
	"samp",
	"section",
	"select",
	"source",
	"span",
	"strong",
	"summary",
	"sup",
	"table",
	"tbody",
	"td",
	"textarea",
	"tfoot",
	"th",
	"thead",
	"time",
	"tr",
	"ul",
	"var",
	"video"
];
var SVG_TAGS$1 = [
	"defs",
	"g",
	"marker",
	"mask",
	"pattern",
	"svg",
	"switch",
	"symbol",
	"feBlend",
	"feColorMatrix",
	"feComponentTransfer",
	"feComposite",
	"feConvolveMatrix",
	"feDiffuseLighting",
	"feDisplacementMap",
	"feFlood",
	"feGaussianBlur",
	"feImage",
	"feMerge",
	"feMorphology",
	"feOffset",
	"feSpecularLighting",
	"feTile",
	"feTurbulence",
	"linearGradient",
	"radialGradient",
	"stop",
	"circle",
	"ellipse",
	"image",
	"line",
	"path",
	"polygon",
	"polyline",
	"rect",
	"text",
	"use",
	"textPath",
	"tspan",
	"foreignObject",
	"clipPath"
];
var TAGS$1 = [...HTML_TAGS$1, ...SVG_TAGS$1];
var MEDIA_FEATURES$1 = [
	"any-hover",
	"any-pointer",
	"aspect-ratio",
	"color",
	"color-gamut",
	"color-index",
	"device-aspect-ratio",
	"device-height",
	"device-width",
	"display-mode",
	"forced-colors",
	"grid",
	"height",
	"hover",
	"inverted-colors",
	"monochrome",
	"orientation",
	"overflow-block",
	"overflow-inline",
	"pointer",
	"prefers-color-scheme",
	"prefers-contrast",
	"prefers-reduced-motion",
	"prefers-reduced-transparency",
	"resolution",
	"scan",
	"scripting",
	"update",
	"width",
	"min-width",
	"max-width",
	"min-height",
	"max-height"
].sort().reverse();
var PSEUDO_CLASSES$1 = [
	"active",
	"any-link",
	"blank",
	"checked",
	"current",
	"default",
	"defined",
	"dir",
	"disabled",
	"drop",
	"empty",
	"enabled",
	"first",
	"first-child",
	"first-of-type",
	"fullscreen",
	"future",
	"focus",
	"focus-visible",
	"focus-within",
	"has",
	"host",
	"host-context",
	"hover",
	"indeterminate",
	"in-range",
	"invalid",
	"is",
	"lang",
	"last-child",
	"last-of-type",
	"left",
	"link",
	"local-link",
	"not",
	"nth-child",
	"nth-col",
	"nth-last-child",
	"nth-last-col",
	"nth-last-of-type",
	"nth-of-type",
	"only-child",
	"only-of-type",
	"optional",
	"out-of-range",
	"past",
	"placeholder-shown",
	"read-only",
	"read-write",
	"required",
	"right",
	"root",
	"scope",
	"target",
	"target-within",
	"user-invalid",
	"valid",
	"visited",
	"where"
].sort().reverse();
var PSEUDO_ELEMENTS$1 = [
	"after",
	"backdrop",
	"before",
	"cue",
	"cue-region",
	"first-letter",
	"first-line",
	"grammar-error",
	"marker",
	"part",
	"placeholder",
	"selection",
	"slotted",
	"spelling-error"
].sort().reverse();
var ATTRIBUTES$1 = [
	"accent-color",
	"align-content",
	"align-items",
	"align-self",
	"alignment-baseline",
	"all",
	"anchor-name",
	"animation",
	"animation-composition",
	"animation-delay",
	"animation-direction",
	"animation-duration",
	"animation-fill-mode",
	"animation-iteration-count",
	"animation-name",
	"animation-play-state",
	"animation-range",
	"animation-range-end",
	"animation-range-start",
	"animation-timeline",
	"animation-timing-function",
	"appearance",
	"aspect-ratio",
	"backdrop-filter",
	"backface-visibility",
	"background",
	"background-attachment",
	"background-blend-mode",
	"background-clip",
	"background-color",
	"background-image",
	"background-origin",
	"background-position",
	"background-position-x",
	"background-position-y",
	"background-repeat",
	"background-size",
	"baseline-shift",
	"block-size",
	"border",
	"border-block",
	"border-block-color",
	"border-block-end",
	"border-block-end-color",
	"border-block-end-style",
	"border-block-end-width",
	"border-block-start",
	"border-block-start-color",
	"border-block-start-style",
	"border-block-start-width",
	"border-block-style",
	"border-block-width",
	"border-bottom",
	"border-bottom-color",
	"border-bottom-left-radius",
	"border-bottom-right-radius",
	"border-bottom-style",
	"border-bottom-width",
	"border-collapse",
	"border-color",
	"border-end-end-radius",
	"border-end-start-radius",
	"border-image",
	"border-image-outset",
	"border-image-repeat",
	"border-image-slice",
	"border-image-source",
	"border-image-width",
	"border-inline",
	"border-inline-color",
	"border-inline-end",
	"border-inline-end-color",
	"border-inline-end-style",
	"border-inline-end-width",
	"border-inline-start",
	"border-inline-start-color",
	"border-inline-start-style",
	"border-inline-start-width",
	"border-inline-style",
	"border-inline-width",
	"border-left",
	"border-left-color",
	"border-left-style",
	"border-left-width",
	"border-radius",
	"border-right",
	"border-right-color",
	"border-right-style",
	"border-right-width",
	"border-spacing",
	"border-start-end-radius",
	"border-start-start-radius",
	"border-style",
	"border-top",
	"border-top-color",
	"border-top-left-radius",
	"border-top-right-radius",
	"border-top-style",
	"border-top-width",
	"border-width",
	"bottom",
	"box-align",
	"box-decoration-break",
	"box-direction",
	"box-flex",
	"box-flex-group",
	"box-lines",
	"box-ordinal-group",
	"box-orient",
	"box-pack",
	"box-shadow",
	"box-sizing",
	"break-after",
	"break-before",
	"break-inside",
	"caption-side",
	"caret-color",
	"clear",
	"clip",
	"clip-path",
	"clip-rule",
	"color",
	"color-interpolation",
	"color-interpolation-filters",
	"color-profile",
	"color-rendering",
	"color-scheme",
	"column-count",
	"column-fill",
	"column-gap",
	"column-rule",
	"column-rule-color",
	"column-rule-style",
	"column-rule-width",
	"column-span",
	"column-width",
	"columns",
	"contain",
	"contain-intrinsic-block-size",
	"contain-intrinsic-height",
	"contain-intrinsic-inline-size",
	"contain-intrinsic-size",
	"contain-intrinsic-width",
	"container",
	"container-name",
	"container-type",
	"content",
	"content-visibility",
	"counter-increment",
	"counter-reset",
	"counter-set",
	"cue",
	"cue-after",
	"cue-before",
	"cursor",
	"cx",
	"cy",
	"direction",
	"display",
	"dominant-baseline",
	"empty-cells",
	"enable-background",
	"field-sizing",
	"fill",
	"fill-opacity",
	"fill-rule",
	"filter",
	"flex",
	"flex-basis",
	"flex-direction",
	"flex-flow",
	"flex-grow",
	"flex-shrink",
	"flex-wrap",
	"float",
	"flood-color",
	"flood-opacity",
	"flow",
	"font",
	"font-display",
	"font-family",
	"font-feature-settings",
	"font-kerning",
	"font-language-override",
	"font-optical-sizing",
	"font-palette",
	"font-size",
	"font-size-adjust",
	"font-smooth",
	"font-smoothing",
	"font-stretch",
	"font-style",
	"font-synthesis",
	"font-synthesis-position",
	"font-synthesis-small-caps",
	"font-synthesis-style",
	"font-synthesis-weight",
	"font-variant",
	"font-variant-alternates",
	"font-variant-caps",
	"font-variant-east-asian",
	"font-variant-emoji",
	"font-variant-ligatures",
	"font-variant-numeric",
	"font-variant-position",
	"font-variation-settings",
	"font-weight",
	"forced-color-adjust",
	"gap",
	"glyph-orientation-horizontal",
	"glyph-orientation-vertical",
	"grid",
	"grid-area",
	"grid-auto-columns",
	"grid-auto-flow",
	"grid-auto-rows",
	"grid-column",
	"grid-column-end",
	"grid-column-start",
	"grid-gap",
	"grid-row",
	"grid-row-end",
	"grid-row-start",
	"grid-template",
	"grid-template-areas",
	"grid-template-columns",
	"grid-template-rows",
	"hanging-punctuation",
	"height",
	"hyphenate-character",
	"hyphenate-limit-chars",
	"hyphens",
	"icon",
	"image-orientation",
	"image-rendering",
	"image-resolution",
	"ime-mode",
	"initial-letter",
	"initial-letter-align",
	"inline-size",
	"inset",
	"inset-area",
	"inset-block",
	"inset-block-end",
	"inset-block-start",
	"inset-inline",
	"inset-inline-end",
	"inset-inline-start",
	"isolation",
	"justify-content",
	"justify-items",
	"justify-self",
	"kerning",
	"left",
	"letter-spacing",
	"lighting-color",
	"line-break",
	"line-height",
	"line-height-step",
	"list-style",
	"list-style-image",
	"list-style-position",
	"list-style-type",
	"margin",
	"margin-block",
	"margin-block-end",
	"margin-block-start",
	"margin-bottom",
	"margin-inline",
	"margin-inline-end",
	"margin-inline-start",
	"margin-left",
	"margin-right",
	"margin-top",
	"margin-trim",
	"marker",
	"marker-end",
	"marker-mid",
	"marker-start",
	"marks",
	"mask",
	"mask-border",
	"mask-border-mode",
	"mask-border-outset",
	"mask-border-repeat",
	"mask-border-slice",
	"mask-border-source",
	"mask-border-width",
	"mask-clip",
	"mask-composite",
	"mask-image",
	"mask-mode",
	"mask-origin",
	"mask-position",
	"mask-repeat",
	"mask-size",
	"mask-type",
	"masonry-auto-flow",
	"math-depth",
	"math-shift",
	"math-style",
	"max-block-size",
	"max-height",
	"max-inline-size",
	"max-width",
	"min-block-size",
	"min-height",
	"min-inline-size",
	"min-width",
	"mix-blend-mode",
	"nav-down",
	"nav-index",
	"nav-left",
	"nav-right",
	"nav-up",
	"none",
	"normal",
	"object-fit",
	"object-position",
	"offset",
	"offset-anchor",
	"offset-distance",
	"offset-path",
	"offset-position",
	"offset-rotate",
	"opacity",
	"order",
	"orphans",
	"outline",
	"outline-color",
	"outline-offset",
	"outline-style",
	"outline-width",
	"overflow",
	"overflow-anchor",
	"overflow-block",
	"overflow-clip-margin",
	"overflow-inline",
	"overflow-wrap",
	"overflow-x",
	"overflow-y",
	"overlay",
	"overscroll-behavior",
	"overscroll-behavior-block",
	"overscroll-behavior-inline",
	"overscroll-behavior-x",
	"overscroll-behavior-y",
	"padding",
	"padding-block",
	"padding-block-end",
	"padding-block-start",
	"padding-bottom",
	"padding-inline",
	"padding-inline-end",
	"padding-inline-start",
	"padding-left",
	"padding-right",
	"padding-top",
	"page",
	"page-break-after",
	"page-break-before",
	"page-break-inside",
	"paint-order",
	"pause",
	"pause-after",
	"pause-before",
	"perspective",
	"perspective-origin",
	"place-content",
	"place-items",
	"place-self",
	"pointer-events",
	"position",
	"position-anchor",
	"position-visibility",
	"print-color-adjust",
	"quotes",
	"r",
	"resize",
	"rest",
	"rest-after",
	"rest-before",
	"right",
	"rotate",
	"row-gap",
	"ruby-align",
	"ruby-position",
	"scale",
	"scroll-behavior",
	"scroll-margin",
	"scroll-margin-block",
	"scroll-margin-block-end",
	"scroll-margin-block-start",
	"scroll-margin-bottom",
	"scroll-margin-inline",
	"scroll-margin-inline-end",
	"scroll-margin-inline-start",
	"scroll-margin-left",
	"scroll-margin-right",
	"scroll-margin-top",
	"scroll-padding",
	"scroll-padding-block",
	"scroll-padding-block-end",
	"scroll-padding-block-start",
	"scroll-padding-bottom",
	"scroll-padding-inline",
	"scroll-padding-inline-end",
	"scroll-padding-inline-start",
	"scroll-padding-left",
	"scroll-padding-right",
	"scroll-padding-top",
	"scroll-snap-align",
	"scroll-snap-stop",
	"scroll-snap-type",
	"scroll-timeline",
	"scroll-timeline-axis",
	"scroll-timeline-name",
	"scrollbar-color",
	"scrollbar-gutter",
	"scrollbar-width",
	"shape-image-threshold",
	"shape-margin",
	"shape-outside",
	"shape-rendering",
	"speak",
	"speak-as",
	"src",
	"stop-color",
	"stop-opacity",
	"stroke",
	"stroke-dasharray",
	"stroke-dashoffset",
	"stroke-linecap",
	"stroke-linejoin",
	"stroke-miterlimit",
	"stroke-opacity",
	"stroke-width",
	"tab-size",
	"table-layout",
	"text-align",
	"text-align-all",
	"text-align-last",
	"text-anchor",
	"text-combine-upright",
	"text-decoration",
	"text-decoration-color",
	"text-decoration-line",
	"text-decoration-skip",
	"text-decoration-skip-ink",
	"text-decoration-style",
	"text-decoration-thickness",
	"text-emphasis",
	"text-emphasis-color",
	"text-emphasis-position",
	"text-emphasis-style",
	"text-indent",
	"text-justify",
	"text-orientation",
	"text-overflow",
	"text-rendering",
	"text-shadow",
	"text-size-adjust",
	"text-transform",
	"text-underline-offset",
	"text-underline-position",
	"text-wrap",
	"text-wrap-mode",
	"text-wrap-style",
	"timeline-scope",
	"top",
	"touch-action",
	"transform",
	"transform-box",
	"transform-origin",
	"transform-style",
	"transition",
	"transition-behavior",
	"transition-delay",
	"transition-duration",
	"transition-property",
	"transition-timing-function",
	"translate",
	"unicode-bidi",
	"user-modify",
	"user-select",
	"vector-effect",
	"vertical-align",
	"view-timeline",
	"view-timeline-axis",
	"view-timeline-inset",
	"view-timeline-name",
	"view-transition-name",
	"visibility",
	"voice-balance",
	"voice-duration",
	"voice-family",
	"voice-pitch",
	"voice-range",
	"voice-rate",
	"voice-stress",
	"voice-volume",
	"white-space",
	"white-space-collapse",
	"widows",
	"width",
	"will-change",
	"word-break",
	"word-spacing",
	"word-wrap",
	"writing-mode",
	"x",
	"y",
	"z-index",
	"zoom"
].sort().reverse();
var PSEUDO_SELECTORS = PSEUDO_CLASSES$1.concat(PSEUDO_ELEMENTS$1).sort().reverse();
/** @type LanguageFn */
function less(hljs) {
	const modes = MODES$1(hljs);
	const PSEUDO_SELECTORS$1 = PSEUDO_SELECTORS;
	const AT_MODIFIERS = "and or not only";
	const INTERP_IDENT_RE = "([\\w-]+|@\\{[\\w-]+\\})";
	const RULES = [];
	const VALUE_MODES = [];
	const STRING_MODE = function(c) {
		return {
			className: "string",
			begin: "~?" + c + ".*?" + c
		};
	};
	const IDENT_MODE = function(name, begin, relevance) {
		return {
			className: name,
			begin,
			relevance
		};
	};
	const AT_KEYWORDS = {
		$pattern: /[a-z-]+/,
		keyword: AT_MODIFIERS,
		attribute: MEDIA_FEATURES$1.join(" ")
	};
	const PARENS_MODE = {
		begin: "\\(",
		end: "\\)",
		contains: VALUE_MODES,
		keywords: AT_KEYWORDS,
		relevance: 0
	};
	VALUE_MODES.push(hljs.C_LINE_COMMENT_MODE, hljs.C_BLOCK_COMMENT_MODE, STRING_MODE("'"), STRING_MODE("\""), modes.CSS_NUMBER_MODE, {
		begin: "(url|data-uri)\\(",
		starts: {
			className: "string",
			end: "[\\)\\n]",
			excludeEnd: true
		}
	}, modes.HEXCOLOR, PARENS_MODE, IDENT_MODE("variable", "@@?[\\w-]+", 10), IDENT_MODE("variable", "@\\{[\\w-]+\\}"), IDENT_MODE("built_in", "~?`[^`]*?`"), {
		className: "attribute",
		begin: "[\\w-]+\\s*:",
		end: ":",
		returnBegin: true,
		excludeEnd: true
	}, modes.IMPORTANT, { beginKeywords: "and not" }, modes.FUNCTION_DISPATCH);
	const VALUE_WITH_RULESETS = VALUE_MODES.concat({
		begin: /\{/,
		end: /\}/,
		contains: RULES
	});
	const MIXIN_GUARD_MODE = {
		beginKeywords: "when",
		endsWithParent: true,
		contains: [{ beginKeywords: "and not" }].concat(VALUE_MODES)
	};
	const RULE_MODE = {
		begin: "([\\w-]+|@\\{[\\w-]+\\})\\s*:",
		returnBegin: true,
		end: /[;}]/,
		relevance: 0,
		contains: [
			{ begin: /-(webkit|moz|ms|o)-/ },
			modes.CSS_VARIABLE,
			{
				className: "attribute",
				begin: "\\b(" + ATTRIBUTES$1.join("|") + ")\\b",
				end: /(?=:)/,
				starts: {
					endsWithParent: true,
					illegal: "[<=$]",
					relevance: 0,
					contains: VALUE_MODES
				}
			}
		]
	};
	const AT_RULE_MODE = {
		className: "keyword",
		begin: "@(import|media|charset|font-face|(-[a-z]+-)?keyframes|supports|document|namespace|page|viewport|host)\\b",
		starts: {
			end: "[;{}]",
			keywords: AT_KEYWORDS,
			returnEnd: true,
			contains: VALUE_MODES,
			relevance: 0
		}
	};
	const VAR_RULE_MODE = {
		className: "variable",
		variants: [{
			begin: "@[\\w-]+\\s*:",
			relevance: 15
		}, { begin: "@[\\w-]+" }],
		starts: {
			end: "[;}]",
			returnEnd: true,
			contains: VALUE_WITH_RULESETS
		}
	};
	const SELECTOR_MODE = {
		variants: [{
			begin: "[\\.#:&\\[>]",
			end: "[;{}]"
		}, {
			begin: INTERP_IDENT_RE,
			end: /\{/
		}],
		returnBegin: true,
		returnEnd: true,
		illegal: "[<='$\"]",
		relevance: 0,
		contains: [
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			MIXIN_GUARD_MODE,
			IDENT_MODE("keyword", "all\\b"),
			IDENT_MODE("variable", "@\\{[\\w-]+\\}"),
			{
				begin: "\\b(" + TAGS$1.join("|") + ")\\b",
				className: "selector-tag"
			},
			modes.CSS_NUMBER_MODE,
			IDENT_MODE("selector-tag", INTERP_IDENT_RE, 0),
			IDENT_MODE("selector-id", "#([\\w-]+|@\\{[\\w-]+\\})"),
			IDENT_MODE("selector-class", "\\.([\\w-]+|@\\{[\\w-]+\\})", 0),
			IDENT_MODE("selector-tag", "&", 0),
			modes.ATTRIBUTE_SELECTOR_MODE,
			{
				className: "selector-pseudo",
				begin: ":(" + PSEUDO_CLASSES$1.join("|") + ")"
			},
			{
				className: "selector-pseudo",
				begin: ":(:)?(" + PSEUDO_ELEMENTS$1.join("|") + ")"
			},
			{
				begin: /\(/,
				end: /\)/,
				relevance: 0,
				contains: VALUE_WITH_RULESETS
			},
			{ begin: "!important" },
			modes.FUNCTION_DISPATCH
		]
	};
	const PSEUDO_SELECTOR_MODE = {
		begin: `[\\w-]+:(:)?(${PSEUDO_SELECTORS$1.join("|")})`,
		returnBegin: true,
		contains: [SELECTOR_MODE]
	};
	RULES.push(hljs.C_LINE_COMMENT_MODE, hljs.C_BLOCK_COMMENT_MODE, AT_RULE_MODE, VAR_RULE_MODE, PSEUDO_SELECTOR_MODE, RULE_MODE, SELECTOR_MODE, MIXIN_GUARD_MODE, modes.FUNCTION_DISPATCH);
	return {
		name: "Less",
		case_insensitive: true,
		illegal: "[=>'/<($\"]",
		contains: RULES
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/lua.js
function lua(hljs) {
	const OPENING_LONG_BRACKET = "\\[=*\\[";
	const CLOSING_LONG_BRACKET = "\\]=*\\]";
	const LONG_BRACKETS = {
		begin: OPENING_LONG_BRACKET,
		end: CLOSING_LONG_BRACKET,
		contains: ["self"]
	};
	const COMMENTS = [hljs.COMMENT("--(?!\\[=*\\[)", "$"), hljs.COMMENT("--\\[=*\\[", CLOSING_LONG_BRACKET, {
		contains: [LONG_BRACKETS],
		relevance: 10
	})];
	return {
		name: "Lua",
		aliases: ["pluto"],
		keywords: {
			$pattern: hljs.UNDERSCORE_IDENT_RE,
			literal: "true false nil",
			keyword: "and break do else elseif end for goto if in local not or repeat return then until while",
			built_in: "_G _ENV _VERSION __index __newindex __mode __call __metatable __tostring __len __gc __add __sub __mul __div __mod __pow __concat __unm __eq __lt __le assert collectgarbage dofile error getfenv getmetatable ipairs load loadfile loadstring module next pairs pcall print rawequal rawget rawset require select setfenv setmetatable tonumber tostring type unpack xpcall arg self coroutine resume yield status wrap create running debug getupvalue debug sethook getmetatable gethook setmetatable setlocal traceback setfenv getinfo setupvalue getlocal getregistry getfenv io lines write close flush open output type read stderr stdin input stdout popen tmpfile math log max acos huge ldexp pi cos tanh pow deg tan cosh sinh random randomseed frexp ceil floor rad abs sqrt modf asin min mod fmod log10 atan2 exp sin atan os exit setlocale date getenv difftime remove time clock tmpname rename execute package preload loadlib loaded loaders cpath config path seeall string sub upper len gfind rep find match char dump gmatch reverse byte format gsub lower table setn insert getn foreachi maxn foreach concat sort remove"
		},
		contains: COMMENTS.concat([
			{
				className: "function",
				beginKeywords: "function",
				end: "\\)",
				contains: [hljs.inherit(hljs.TITLE_MODE, { begin: "([_a-zA-Z]\\w*\\.)*([_a-zA-Z]\\w*:)?[_a-zA-Z]\\w*" }), {
					className: "params",
					begin: "\\(",
					endsWithParent: true,
					contains: COMMENTS
				}].concat(COMMENTS)
			},
			hljs.C_NUMBER_MODE,
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE,
			{
				className: "string",
				begin: OPENING_LONG_BRACKET,
				end: CLOSING_LONG_BRACKET,
				contains: [LONG_BRACKETS],
				relevance: 5
			}
		])
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/makefile.js
function makefile(hljs) {
	const VARIABLE = {
		className: "variable",
		variants: [{
			begin: "\\$\\(" + hljs.UNDERSCORE_IDENT_RE + "\\)",
			contains: [hljs.BACKSLASH_ESCAPE]
		}, { begin: /\$[@%<?\^\+\*]/ }]
	};
	const QUOTE_STRING = {
		className: "string",
		begin: /"/,
		end: /"/,
		contains: [hljs.BACKSLASH_ESCAPE, VARIABLE]
	};
	const FUNC = {
		className: "variable",
		begin: /\$\([\w-]+\s/,
		end: /\)/,
		keywords: { built_in: "subst patsubst strip findstring filter filter-out sort word wordlist firstword lastword dir notdir suffix basename addsuffix addprefix join wildcard realpath abspath error warning shell origin flavor foreach if or and call eval file value" },
		contains: [VARIABLE, QUOTE_STRING]
	};
	const ASSIGNMENT = { begin: "^" + hljs.UNDERSCORE_IDENT_RE + "\\s*(?=[:+?]?=)" };
	const META = {
		className: "meta",
		begin: /^\.PHONY:/,
		end: /$/,
		keywords: {
			$pattern: /[\.\w]+/,
			keyword: ".PHONY"
		}
	};
	const TARGET = {
		className: "section",
		begin: /^[^\s]+:/,
		end: /$/,
		contains: [VARIABLE]
	};
	return {
		name: "Makefile",
		aliases: [
			"mk",
			"mak",
			"make"
		],
		keywords: {
			$pattern: /[\w-]+/,
			keyword: "define endef undefine ifdef ifndef ifeq ifneq else endif include -include sinclude override export unexport private vpath"
		},
		contains: [
			hljs.HASH_COMMENT_MODE,
			VARIABLE,
			QUOTE_STRING,
			FUNC,
			ASSIGNMENT,
			META,
			TARGET
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/markdown.js
function markdown(hljs) {
	const regex = hljs.regex;
	const INLINE_HTML = {
		begin: /<\/?[A-Za-z_]/,
		end: ">",
		subLanguage: "xml",
		relevance: 0
	};
	const HORIZONTAL_RULE = {
		begin: "^[-\\*]{3,}",
		end: "$"
	};
	const CODE = {
		className: "code",
		variants: [
			{ begin: "(`{3,})[^`](.|\\n)*?\\1`*[ ]*" },
			{ begin: "(~{3,})[^~](.|\\n)*?\\1~*[ ]*" },
			{
				begin: "```",
				end: "```+[ ]*$"
			},
			{
				begin: "~~~",
				end: "~~~+[ ]*$"
			},
			{ begin: "`.+?`" },
			{
				begin: "(?=^( {4}|\\t))",
				contains: [{
					begin: "^( {4}|\\t)",
					end: "(\\n)$"
				}],
				relevance: 0
			}
		]
	};
	const LIST = {
		className: "bullet",
		begin: "^[ 	]*([*+-]|(\\d+\\.))(?=\\s+)",
		end: "\\s+",
		excludeEnd: true
	};
	const LINK_REFERENCE = {
		begin: /^\[[^\n]+\]:/,
		returnBegin: true,
		contains: [{
			className: "symbol",
			begin: /\[/,
			end: /\]/,
			excludeBegin: true,
			excludeEnd: true
		}, {
			className: "link",
			begin: /:\s*/,
			end: /$/,
			excludeBegin: true
		}]
	};
	const LINK = {
		variants: [
			{
				begin: /\[.+?\]\[.*?\]/,
				relevance: 0
			},
			{
				begin: /\[.+?\]\(((data|javascript|mailto):|(?:http|ftp)s?:\/\/).*?\)/,
				relevance: 2
			},
			{
				begin: regex.concat(/\[.+?\]\(/, /[A-Za-z][A-Za-z0-9+.-]*/, /:\/\/.*?\)/),
				relevance: 2
			},
			{
				begin: /\[.+?\]\([./?&#].*?\)/,
				relevance: 1
			},
			{
				begin: /\[.*?\]\(.*?\)/,
				relevance: 0
			}
		],
		returnBegin: true,
		contains: [
			{ match: /\[(?=\])/ },
			{
				className: "string",
				relevance: 0,
				begin: "\\[",
				end: "\\]",
				excludeBegin: true,
				returnEnd: true
			},
			{
				className: "link",
				relevance: 0,
				begin: "\\]\\(",
				end: "\\)",
				excludeBegin: true,
				excludeEnd: true
			},
			{
				className: "symbol",
				relevance: 0,
				begin: "\\]\\[",
				end: "\\]",
				excludeBegin: true,
				excludeEnd: true
			}
		]
	};
	const BOLD = {
		className: "strong",
		contains: [],
		variants: [{
			begin: /_{2}(?!\s)/,
			end: /_{2}/
		}, {
			begin: /\*{2}(?!\s)/,
			end: /\*{2}/
		}]
	};
	const ITALIC = {
		className: "emphasis",
		contains: [],
		variants: [{
			begin: /\*(?![*\s])/,
			end: /\*/
		}, {
			begin: /_(?![_\s])/,
			end: /_/,
			relevance: 0
		}]
	};
	const BOLD_WITHOUT_ITALIC = hljs.inherit(BOLD, { contains: [] });
	const ITALIC_WITHOUT_BOLD = hljs.inherit(ITALIC, { contains: [] });
	BOLD.contains.push(ITALIC_WITHOUT_BOLD);
	ITALIC.contains.push(BOLD_WITHOUT_ITALIC);
	let CONTAINABLE = [INLINE_HTML, LINK];
	[
		BOLD,
		ITALIC,
		BOLD_WITHOUT_ITALIC,
		ITALIC_WITHOUT_BOLD
	].forEach((m) => {
		m.contains = m.contains.concat(CONTAINABLE);
	});
	CONTAINABLE = CONTAINABLE.concat(BOLD, ITALIC);
	return {
		name: "Markdown",
		aliases: [
			"md",
			"mkdown",
			"mkd"
		],
		contains: [
			{
				className: "section",
				variants: [{
					begin: "^#{1,6}",
					end: "$",
					contains: CONTAINABLE
				}, {
					begin: "(?=^.+?\\n[=-]{2,}$)",
					contains: [{ begin: "^[=-]*$" }, {
						begin: "^",
						end: "\\n",
						contains: CONTAINABLE
					}]
				}]
			},
			INLINE_HTML,
			LIST,
			BOLD,
			ITALIC,
			{
				className: "quote",
				begin: "^>\\s+",
				contains: CONTAINABLE,
				end: "$"
			},
			CODE,
			HORIZONTAL_RULE,
			LINK,
			LINK_REFERENCE,
			{
				scope: "literal",
				match: /&([a-zA-Z0-9]+|#[0-9]{1,7}|#[Xx][0-9a-fA-F]{1,6});/
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/objectivec.js
function objectivec(hljs) {
	const API_CLASS = {
		className: "built_in",
		begin: "\\b(AV|CA|CF|CG|CI|CL|CM|CN|CT|MK|MP|MTK|MTL|NS|SCN|SK|UI|WK|XC)\\w+"
	};
	const IDENTIFIER_RE = /[a-zA-Z@][a-zA-Z0-9_]*/;
	const KEYWORDS = {
		"variable.language": ["this", "super"],
		$pattern: IDENTIFIER_RE,
		keyword: [
			"while",
			"export",
			"sizeof",
			"typedef",
			"const",
			"struct",
			"for",
			"union",
			"volatile",
			"static",
			"mutable",
			"if",
			"do",
			"return",
			"goto",
			"enum",
			"else",
			"break",
			"extern",
			"asm",
			"case",
			"default",
			"register",
			"explicit",
			"typename",
			"switch",
			"continue",
			"inline",
			"readonly",
			"assign",
			"readwrite",
			"self",
			"@synchronized",
			"id",
			"typeof",
			"nonatomic",
			"IBOutlet",
			"IBAction",
			"strong",
			"weak",
			"copy",
			"in",
			"out",
			"inout",
			"bycopy",
			"byref",
			"oneway",
			"__strong",
			"__weak",
			"__block",
			"__autoreleasing",
			"@private",
			"@protected",
			"@public",
			"@try",
			"@property",
			"@end",
			"@throw",
			"@catch",
			"@finally",
			"@autoreleasepool",
			"@synthesize",
			"@dynamic",
			"@selector",
			"@optional",
			"@required",
			"@encode",
			"@package",
			"@import",
			"@defs",
			"@compatibility_alias",
			"__bridge",
			"__bridge_transfer",
			"__bridge_retained",
			"__bridge_retain",
			"__covariant",
			"__contravariant",
			"__kindof",
			"_Nonnull",
			"_Nullable",
			"_Null_unspecified",
			"__FUNCTION__",
			"__PRETTY_FUNCTION__",
			"__attribute__",
			"getter",
			"setter",
			"retain",
			"unsafe_unretained",
			"nonnull",
			"nullable",
			"null_unspecified",
			"null_resettable",
			"class",
			"instancetype",
			"NS_DESIGNATED_INITIALIZER",
			"NS_UNAVAILABLE",
			"NS_REQUIRES_SUPER",
			"NS_RETURNS_INNER_POINTER",
			"NS_INLINE",
			"NS_AVAILABLE",
			"NS_DEPRECATED",
			"NS_ENUM",
			"NS_OPTIONS",
			"NS_SWIFT_UNAVAILABLE",
			"NS_ASSUME_NONNULL_BEGIN",
			"NS_ASSUME_NONNULL_END",
			"NS_REFINED_FOR_SWIFT",
			"NS_SWIFT_NAME",
			"NS_SWIFT_NOTHROW",
			"NS_DURING",
			"NS_HANDLER",
			"NS_ENDHANDLER",
			"NS_VALUERETURN",
			"NS_VOIDRETURN"
		],
		literal: [
			"false",
			"true",
			"FALSE",
			"TRUE",
			"nil",
			"YES",
			"NO",
			"NULL"
		],
		built_in: [
			"dispatch_once_t",
			"dispatch_queue_t",
			"dispatch_sync",
			"dispatch_async",
			"dispatch_once"
		],
		type: [
			"int",
			"float",
			"char",
			"unsigned",
			"signed",
			"short",
			"long",
			"double",
			"wchar_t",
			"unichar",
			"void",
			"bool",
			"BOOL",
			"id|0",
			"_Bool"
		]
	};
	const CLASS_KEYWORDS = {
		$pattern: IDENTIFIER_RE,
		keyword: [
			"@interface",
			"@class",
			"@protocol",
			"@implementation"
		]
	};
	return {
		name: "Objective-C",
		aliases: [
			"mm",
			"objc",
			"obj-c",
			"obj-c++",
			"objective-c++"
		],
		keywords: KEYWORDS,
		illegal: "</",
		contains: [
			API_CLASS,
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			hljs.C_NUMBER_MODE,
			hljs.QUOTE_STRING_MODE,
			hljs.APOS_STRING_MODE,
			{
				className: "string",
				variants: [{
					begin: "@\"",
					end: "\"",
					illegal: "\\n",
					contains: [hljs.BACKSLASH_ESCAPE]
				}]
			},
			{
				className: "meta",
				begin: /#\s*[a-z]+\b/,
				end: /$/,
				keywords: { keyword: "if else elif endif define undef warning error line pragma ifdef ifndef include" },
				contains: [
					{
						begin: /\\\n/,
						relevance: 0
					},
					hljs.inherit(hljs.QUOTE_STRING_MODE, { className: "string" }),
					{
						className: "string",
						begin: /<.*?>/,
						end: /$/,
						illegal: "\\n"
					},
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE
				]
			},
			{
				className: "class",
				begin: "(" + CLASS_KEYWORDS.keyword.join("|") + ")\\b",
				end: /(\{|$)/,
				excludeEnd: true,
				keywords: CLASS_KEYWORDS,
				contains: [hljs.UNDERSCORE_TITLE_MODE]
			},
			{
				begin: "\\." + hljs.UNDERSCORE_IDENT_RE,
				relevance: 0
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/perl.js
/** @type LanguageFn */
function perl(hljs) {
	const regex = hljs.regex;
	const KEYWORDS = [
		"abs",
		"accept",
		"alarm",
		"and",
		"atan2",
		"bind",
		"binmode",
		"bless",
		"break",
		"caller",
		"chdir",
		"chmod",
		"chomp",
		"chop",
		"chown",
		"chr",
		"chroot",
		"class",
		"close",
		"closedir",
		"connect",
		"continue",
		"cos",
		"crypt",
		"dbmclose",
		"dbmopen",
		"defined",
		"delete",
		"die",
		"do",
		"dump",
		"each",
		"else",
		"elsif",
		"endgrent",
		"endhostent",
		"endnetent",
		"endprotoent",
		"endpwent",
		"endservent",
		"eof",
		"eval",
		"exec",
		"exists",
		"exit",
		"exp",
		"fcntl",
		"field",
		"fileno",
		"flock",
		"for",
		"foreach",
		"fork",
		"format",
		"formline",
		"getc",
		"getgrent",
		"getgrgid",
		"getgrnam",
		"gethostbyaddr",
		"gethostbyname",
		"gethostent",
		"getlogin",
		"getnetbyaddr",
		"getnetbyname",
		"getnetent",
		"getpeername",
		"getpgrp",
		"getpriority",
		"getprotobyname",
		"getprotobynumber",
		"getprotoent",
		"getpwent",
		"getpwnam",
		"getpwuid",
		"getservbyname",
		"getservbyport",
		"getservent",
		"getsockname",
		"getsockopt",
		"given",
		"glob",
		"gmtime",
		"goto",
		"grep",
		"gt",
		"hex",
		"if",
		"index",
		"int",
		"ioctl",
		"join",
		"keys",
		"kill",
		"last",
		"lc",
		"lcfirst",
		"length",
		"link",
		"listen",
		"local",
		"localtime",
		"log",
		"lstat",
		"lt",
		"ma",
		"map",
		"method",
		"mkdir",
		"msgctl",
		"msgget",
		"msgrcv",
		"msgsnd",
		"my",
		"ne",
		"next",
		"no",
		"not",
		"oct",
		"open",
		"opendir",
		"or",
		"ord",
		"our",
		"pack",
		"package",
		"pipe",
		"pop",
		"pos",
		"print",
		"printf",
		"prototype",
		"push",
		"q|0",
		"qq",
		"quotemeta",
		"qw",
		"qx",
		"rand",
		"read",
		"readdir",
		"readline",
		"readlink",
		"readpipe",
		"recv",
		"redo",
		"ref",
		"rename",
		"require",
		"reset",
		"return",
		"reverse",
		"rewinddir",
		"rindex",
		"rmdir",
		"say",
		"scalar",
		"seek",
		"seekdir",
		"select",
		"semctl",
		"semget",
		"semop",
		"send",
		"setgrent",
		"sethostent",
		"setnetent",
		"setpgrp",
		"setpriority",
		"setprotoent",
		"setpwent",
		"setservent",
		"setsockopt",
		"shift",
		"shmctl",
		"shmget",
		"shmread",
		"shmwrite",
		"shutdown",
		"sin",
		"sleep",
		"socket",
		"socketpair",
		"sort",
		"splice",
		"split",
		"sprintf",
		"sqrt",
		"srand",
		"stat",
		"state",
		"study",
		"sub",
		"substr",
		"symlink",
		"syscall",
		"sysopen",
		"sysread",
		"sysseek",
		"system",
		"syswrite",
		"tell",
		"telldir",
		"tie",
		"tied",
		"time",
		"times",
		"tr",
		"truncate",
		"uc",
		"ucfirst",
		"umask",
		"undef",
		"unless",
		"unlink",
		"unpack",
		"unshift",
		"untie",
		"until",
		"use",
		"utime",
		"values",
		"vec",
		"wait",
		"waitpid",
		"wantarray",
		"warn",
		"when",
		"while",
		"write",
		"x|0",
		"xor",
		"y|0"
	];
	const REGEX_MODIFIERS = /[dualxmsipngr]{0,12}/;
	const PERL_KEYWORDS = {
		$pattern: /[\w.]+/,
		keyword: KEYWORDS.join(" ")
	};
	const SUBST = {
		className: "subst",
		begin: "[$@]\\{",
		end: "\\}",
		keywords: PERL_KEYWORDS
	};
	const METHOD = {
		begin: /->\{/,
		end: /\}/
	};
	const ATTR = {
		scope: "attr",
		match: /\s+:\s*\w+(\s*\(.*?\))?/
	};
	const VAR = {
		scope: "variable",
		variants: [
			{ begin: /\$\d/ },
			{ begin: regex.concat(/[$%@](?!")(\^\w\b|#\w+(::\w+)*|\{\w+\}|\w+(::\w*)*)/, `(?![A-Za-z])(?![@$%])`) },
			{
				begin: /[$%@](?!")[^\s\w{=]|\$=/,
				relevance: 0
			}
		],
		contains: [ATTR]
	};
	const NUMBER = {
		className: "number",
		variants: [
			{ match: /0?\.[0-9][0-9_]+\b/ },
			{ match: /\bv?(0|[1-9][0-9_]*(\.[0-9_]+)?|[1-9][0-9_]*)\b/ },
			{ match: /\b0[0-7][0-7_]*\b/ },
			{ match: /\b0x[0-9a-fA-F][0-9a-fA-F_]*\b/ },
			{ match: /\b0b[0-1][0-1_]*\b/ }
		],
		relevance: 0
	};
	const STRING_CONTAINS = [
		hljs.BACKSLASH_ESCAPE,
		SUBST,
		VAR
	];
	const REGEX_DELIMS = [
		/!/,
		/\//,
		/\|/,
		/\?/,
		/'/,
		/"/,
		/#/
	];
	/**
	* @param {string|RegExp} prefix
	* @param {string|RegExp} open
	* @param {string|RegExp} close
	*/
	const PAIRED_DOUBLE_RE = (prefix, open, close = "\\1") => {
		const middle = close === "\\1" ? close : regex.concat(close, open);
		return regex.concat(regex.concat("(?:", prefix, ")"), open, /(?:\\.|[^\\\/])*?/, middle, /(?:\\.|[^\\\/])*?/, close, REGEX_MODIFIERS);
	};
	/**
	* @param {string|RegExp} prefix
	* @param {string|RegExp} open
	* @param {string|RegExp} close
	*/
	const PAIRED_RE = (prefix, open, close) => {
		return regex.concat(regex.concat("(?:", prefix, ")"), open, /(?:\\.|[^\\\/])*?/, close, REGEX_MODIFIERS);
	};
	const PERL_DEFAULT_CONTAINS = [
		VAR,
		hljs.HASH_COMMENT_MODE,
		hljs.COMMENT(/^=\w/, /=cut/, { endsWithParent: true }),
		METHOD,
		{
			className: "string",
			contains: STRING_CONTAINS,
			variants: [
				{
					begin: "q[qwxr]?\\s*\\(",
					end: "\\)",
					relevance: 5
				},
				{
					begin: "q[qwxr]?\\s*\\[",
					end: "\\]",
					relevance: 5
				},
				{
					begin: "q[qwxr]?\\s*\\{",
					end: "\\}",
					relevance: 5
				},
				{
					begin: "q[qwxr]?\\s*\\|",
					end: "\\|",
					relevance: 5
				},
				{
					begin: "q[qwxr]?\\s*<",
					end: ">",
					relevance: 5
				},
				{
					begin: "qw\\s+q",
					end: "q",
					relevance: 5
				},
				{
					begin: "'",
					end: "'",
					contains: [hljs.BACKSLASH_ESCAPE]
				},
				{
					begin: "\"",
					end: "\""
				},
				{
					begin: "`",
					end: "`",
					contains: [hljs.BACKSLASH_ESCAPE]
				},
				{
					begin: /\{\w+\}/,
					relevance: 0
				},
				{
					begin: "-?\\w+\\s*=>",
					relevance: 0
				}
			]
		},
		NUMBER,
		{
			begin: "(\\/\\/|" + hljs.RE_STARTERS_RE + "|\\b(split|return|print|reverse|grep)\\b)\\s*",
			keywords: "split return print reverse grep",
			relevance: 0,
			contains: [
				hljs.HASH_COMMENT_MODE,
				{
					className: "regexp",
					variants: [
						{ begin: PAIRED_DOUBLE_RE("s|tr|y", regex.either(...REGEX_DELIMS, { capture: true })) },
						{ begin: PAIRED_DOUBLE_RE("s|tr|y", "\\(", "\\)") },
						{ begin: PAIRED_DOUBLE_RE("s|tr|y", "\\[", "\\]") },
						{ begin: PAIRED_DOUBLE_RE("s|tr|y", "\\{", "\\}") }
					],
					relevance: 2
				},
				{
					className: "regexp",
					variants: [
						{
							begin: /(m|qr)\/\//,
							relevance: 0
						},
						{ begin: PAIRED_RE("(?:m|qr)?", /\//, /\//) },
						{ begin: PAIRED_RE("m|qr", regex.either(...REGEX_DELIMS, { capture: true }), /\1/) },
						{ begin: PAIRED_RE("m|qr", /\(/, /\)/) },
						{ begin: PAIRED_RE("m|qr", /\[/, /\]/) },
						{ begin: PAIRED_RE("m|qr", /\{/, /\}/) }
					]
				}
			]
		},
		{
			className: "function",
			beginKeywords: "sub method",
			end: "(\\s*\\(.*?\\))?[;{]",
			excludeEnd: true,
			relevance: 5,
			contains: [hljs.TITLE_MODE, ATTR]
		},
		{
			className: "class",
			beginKeywords: "class",
			end: "[;{]",
			excludeEnd: true,
			relevance: 5,
			contains: [
				hljs.TITLE_MODE,
				ATTR,
				NUMBER
			]
		},
		{
			begin: "-\\w\\b",
			relevance: 0
		},
		{
			begin: "^__DATA__$",
			end: "^__END__$",
			subLanguage: "mojolicious",
			contains: [{
				begin: "^@@.*",
				end: "$",
				className: "comment"
			}]
		}
	];
	SUBST.contains = PERL_DEFAULT_CONTAINS;
	METHOD.contains = PERL_DEFAULT_CONTAINS;
	return {
		name: "Perl",
		aliases: ["pl", "pm"],
		keywords: PERL_KEYWORDS,
		contains: PERL_DEFAULT_CONTAINS
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/php.js
/**
* @param {HLJSApi} hljs
* @returns {LanguageDetail}
* */
function php(hljs) {
	const regex = hljs.regex;
	const NOT_PERL_ETC = /(?![A-Za-z0-9])(?![$])/;
	const IDENT_RE = regex.concat(/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/, NOT_PERL_ETC);
	const PASCAL_CASE_CLASS_NAME_RE = regex.concat(/(\\?[A-Z][a-z0-9_\x7f-\xff]+|\\?[A-Z]+(?=[A-Z][a-z0-9_\x7f-\xff])){1,}/, NOT_PERL_ETC);
	const UPCASE_NAME_RE = regex.concat(/[A-Z]+/, NOT_PERL_ETC);
	const VARIABLE = {
		scope: "variable",
		match: "\\$+" + IDENT_RE
	};
	const PREPROCESSOR = {
		scope: "meta",
		variants: [
			{
				begin: /<\?php/,
				relevance: 10
			},
			{ begin: /<\?=/ },
			{
				begin: /<\?/,
				relevance: .1
			},
			{ begin: /\?>/ }
		]
	};
	const SUBST = {
		scope: "subst",
		variants: [{ begin: /\$\w+/ }, {
			begin: /\{\$/,
			end: /\}/
		}]
	};
	const SINGLE_QUOTED = hljs.inherit(hljs.APOS_STRING_MODE, { illegal: null });
	const DOUBLE_QUOTED = hljs.inherit(hljs.QUOTE_STRING_MODE, {
		illegal: null,
		contains: hljs.QUOTE_STRING_MODE.contains.concat(SUBST)
	});
	const HEREDOC = {
		begin: /<<<[ \t]*(?:(\w+)|"(\w+)")\n/,
		end: /[ \t]*(\w+)\b/,
		contains: hljs.QUOTE_STRING_MODE.contains.concat(SUBST),
		"on:begin": (m, resp) => {
			resp.data._beginMatch = m[1] || m[2];
		},
		"on:end": (m, resp) => {
			if (resp.data._beginMatch !== m[1]) resp.ignoreMatch();
		}
	};
	const NOWDOC = hljs.END_SAME_AS_BEGIN({
		begin: /<<<[ \t]*'(\w+)'\n/,
		end: /[ \t]*(\w+)\b/
	});
	const WHITESPACE = "[ 	\n]";
	const STRING = {
		scope: "string",
		variants: [
			DOUBLE_QUOTED,
			SINGLE_QUOTED,
			HEREDOC,
			NOWDOC
		]
	};
	const NUMBER = {
		scope: "number",
		variants: [
			{ begin: `\\b0[bB][01]+(?:_[01]+)*\\b` },
			{ begin: `\\b0[oO][0-7]+(?:_[0-7]+)*\\b` },
			{ begin: `\\b0[xX][\\da-fA-F]+(?:_[\\da-fA-F]+)*\\b` },
			{ begin: `(?:\\b\\d+(?:_\\d+)*(\\.(?:\\d+(?:_\\d+)*))?|\\B\\.\\d+)(?:[eE][+-]?\\d+)?` }
		],
		relevance: 0
	};
	const LITERALS = [
		"false",
		"null",
		"true"
	];
	const KWS = [
		"__CLASS__",
		"__DIR__",
		"__FILE__",
		"__FUNCTION__",
		"__COMPILER_HALT_OFFSET__",
		"__LINE__",
		"__METHOD__",
		"__NAMESPACE__",
		"__TRAIT__",
		"die",
		"echo",
		"exit",
		"include",
		"include_once",
		"print",
		"require",
		"require_once",
		"array",
		"abstract",
		"and",
		"as",
		"binary",
		"bool",
		"boolean",
		"break",
		"callable",
		"case",
		"catch",
		"class",
		"clone",
		"const",
		"continue",
		"declare",
		"default",
		"do",
		"double",
		"else",
		"elseif",
		"empty",
		"enddeclare",
		"endfor",
		"endforeach",
		"endif",
		"endswitch",
		"endwhile",
		"enum",
		"eval",
		"extends",
		"final",
		"finally",
		"float",
		"for",
		"foreach",
		"from",
		"global",
		"goto",
		"if",
		"implements",
		"instanceof",
		"insteadof",
		"int",
		"integer",
		"interface",
		"isset",
		"iterable",
		"list",
		"match|0",
		"mixed",
		"new",
		"never",
		"object",
		"or",
		"private",
		"protected",
		"public",
		"readonly",
		"real",
		"return",
		"string",
		"switch",
		"throw",
		"trait",
		"try",
		"unset",
		"use",
		"var",
		"void",
		"while",
		"xor",
		"yield"
	];
	const BUILT_INS = [
		"Error|0",
		"AppendIterator",
		"ArgumentCountError",
		"ArithmeticError",
		"ArrayIterator",
		"ArrayObject",
		"AssertionError",
		"BadFunctionCallException",
		"BadMethodCallException",
		"CachingIterator",
		"CallbackFilterIterator",
		"CompileError",
		"Countable",
		"DirectoryIterator",
		"DivisionByZeroError",
		"DomainException",
		"EmptyIterator",
		"ErrorException",
		"Exception",
		"FilesystemIterator",
		"FilterIterator",
		"GlobIterator",
		"InfiniteIterator",
		"InvalidArgumentException",
		"IteratorIterator",
		"LengthException",
		"LimitIterator",
		"LogicException",
		"MultipleIterator",
		"NoRewindIterator",
		"OutOfBoundsException",
		"OutOfRangeException",
		"OuterIterator",
		"OverflowException",
		"ParentIterator",
		"ParseError",
		"RangeException",
		"RecursiveArrayIterator",
		"RecursiveCachingIterator",
		"RecursiveCallbackFilterIterator",
		"RecursiveDirectoryIterator",
		"RecursiveFilterIterator",
		"RecursiveIterator",
		"RecursiveIteratorIterator",
		"RecursiveRegexIterator",
		"RecursiveTreeIterator",
		"RegexIterator",
		"RuntimeException",
		"SeekableIterator",
		"SplDoublyLinkedList",
		"SplFileInfo",
		"SplFileObject",
		"SplFixedArray",
		"SplHeap",
		"SplMaxHeap",
		"SplMinHeap",
		"SplObjectStorage",
		"SplObserver",
		"SplPriorityQueue",
		"SplQueue",
		"SplStack",
		"SplSubject",
		"SplTempFileObject",
		"TypeError",
		"UnderflowException",
		"UnexpectedValueException",
		"UnhandledMatchError",
		"ArrayAccess",
		"BackedEnum",
		"Closure",
		"Fiber",
		"Generator",
		"Iterator",
		"IteratorAggregate",
		"Serializable",
		"Stringable",
		"Throwable",
		"Traversable",
		"UnitEnum",
		"WeakReference",
		"WeakMap",
		"Directory",
		"__PHP_Incomplete_Class",
		"parent",
		"php_user_filter",
		"self",
		"static",
		"stdClass"
	];
	/** Dual-case keywords
	*
	* ["then","FILE"] =>
	*     ["then", "THEN", "FILE", "file"]
	*
	* @param {string[]} items */
	const dualCase = (items) => {
		/** @type string[] */
		const result = [];
		items.forEach((item) => {
			result.push(item);
			if (item.toLowerCase() === item) result.push(item.toUpperCase());
			else result.push(item.toLowerCase());
		});
		return result;
	};
	const KEYWORDS = {
		keyword: KWS,
		literal: dualCase(LITERALS),
		built_in: BUILT_INS
	};
	/**
	* @param {string[]} items */
	const normalizeKeywords = (items) => {
		return items.map((item) => {
			return item.replace(/\|\d+$/, "");
		});
	};
	const CONSTRUCTOR_CALL = { variants: [{
		match: [
			/new/,
			regex.concat(WHITESPACE, "+"),
			regex.concat("(?!", normalizeKeywords(BUILT_INS).join("\\b|"), "\\b)"),
			PASCAL_CASE_CLASS_NAME_RE
		],
		scope: {
			1: "keyword",
			4: "title.class"
		}
	}] };
	const CONSTANT_REFERENCE = regex.concat(IDENT_RE, "\\b(?!\\()");
	const LEFT_AND_RIGHT_SIDE_OF_DOUBLE_COLON = { variants: [
		{
			match: [regex.concat(/::/, regex.lookahead(/(?!class\b)/)), CONSTANT_REFERENCE],
			scope: { 2: "variable.constant" }
		},
		{
			match: [/::/, /class/],
			scope: { 2: "variable.language" }
		},
		{
			match: [
				PASCAL_CASE_CLASS_NAME_RE,
				regex.concat(/::/, regex.lookahead(/(?!class\b)/)),
				CONSTANT_REFERENCE
			],
			scope: {
				1: "title.class",
				3: "variable.constant"
			}
		},
		{
			match: [PASCAL_CASE_CLASS_NAME_RE, regex.concat("::", regex.lookahead(/(?!class\b)/))],
			scope: { 1: "title.class" }
		},
		{
			match: [
				PASCAL_CASE_CLASS_NAME_RE,
				/::/,
				/class/
			],
			scope: {
				1: "title.class",
				3: "variable.language"
			}
		}
	] };
	const NAMED_ARGUMENT = {
		scope: "attr",
		match: regex.concat(IDENT_RE, regex.lookahead(":"), regex.lookahead(/(?!::)/))
	};
	const PARAMS_MODE = {
		relevance: 0,
		begin: /\(/,
		end: /\)/,
		keywords: KEYWORDS,
		contains: [
			NAMED_ARGUMENT,
			VARIABLE,
			LEFT_AND_RIGHT_SIDE_OF_DOUBLE_COLON,
			hljs.C_BLOCK_COMMENT_MODE,
			STRING,
			NUMBER,
			CONSTRUCTOR_CALL
		]
	};
	const FUNCTION_INVOKE = {
		relevance: 0,
		match: [
			/\b/,
			regex.concat("(?!fn\\b|function\\b|", normalizeKeywords(KWS).join("\\b|"), "|", normalizeKeywords(BUILT_INS).join("\\b|"), "\\b)"),
			IDENT_RE,
			regex.concat(WHITESPACE, "*"),
			regex.lookahead(/(?=\()/)
		],
		scope: { 3: "title.function.invoke" },
		contains: [PARAMS_MODE]
	};
	PARAMS_MODE.contains.push(FUNCTION_INVOKE);
	const ATTRIBUTE_CONTAINS = [
		NAMED_ARGUMENT,
		LEFT_AND_RIGHT_SIDE_OF_DOUBLE_COLON,
		hljs.C_BLOCK_COMMENT_MODE,
		STRING,
		NUMBER,
		CONSTRUCTOR_CALL
	];
	const ATTRIBUTES = {
		begin: regex.concat(/#\[\s*\\?/, regex.either(PASCAL_CASE_CLASS_NAME_RE, UPCASE_NAME_RE)),
		beginScope: "meta",
		end: /]/,
		endScope: "meta",
		keywords: {
			literal: LITERALS,
			keyword: ["new", "array"]
		},
		contains: [
			{
				begin: /\[/,
				end: /]/,
				keywords: {
					literal: LITERALS,
					keyword: ["new", "array"]
				},
				contains: ["self", ...ATTRIBUTE_CONTAINS]
			},
			...ATTRIBUTE_CONTAINS,
			{
				scope: "meta",
				variants: [{ match: PASCAL_CASE_CLASS_NAME_RE }, { match: UPCASE_NAME_RE }]
			}
		]
	};
	return {
		case_insensitive: false,
		keywords: KEYWORDS,
		contains: [
			ATTRIBUTES,
			hljs.HASH_COMMENT_MODE,
			hljs.COMMENT("//", "$"),
			hljs.COMMENT("/\\*", "\\*/", { contains: [{
				scope: "doctag",
				match: "@[A-Za-z]+"
			}] }),
			{
				match: /__halt_compiler\(\);/,
				keywords: "__halt_compiler",
				starts: {
					scope: "comment",
					end: hljs.MATCH_NOTHING_RE,
					contains: [{
						match: /\?>/,
						scope: "meta",
						endsParent: true
					}]
				}
			},
			PREPROCESSOR,
			{
				scope: "variable.language",
				match: /\$this\b/
			},
			VARIABLE,
			FUNCTION_INVOKE,
			LEFT_AND_RIGHT_SIDE_OF_DOUBLE_COLON,
			{
				match: [
					/const/,
					/\s/,
					IDENT_RE
				],
				scope: {
					1: "keyword",
					3: "variable.constant"
				}
			},
			CONSTRUCTOR_CALL,
			{
				scope: "function",
				relevance: 0,
				beginKeywords: "fn function",
				end: /[;{]/,
				excludeEnd: true,
				illegal: "[$%\\[]",
				contains: [
					{ beginKeywords: "use" },
					hljs.UNDERSCORE_TITLE_MODE,
					{
						begin: "=>",
						endsParent: true
					},
					{
						scope: "params",
						begin: "\\(",
						end: "\\)",
						excludeBegin: true,
						excludeEnd: true,
						keywords: KEYWORDS,
						contains: [
							"self",
							ATTRIBUTES,
							VARIABLE,
							LEFT_AND_RIGHT_SIDE_OF_DOUBLE_COLON,
							hljs.C_BLOCK_COMMENT_MODE,
							STRING,
							NUMBER
						]
					}
				]
			},
			{
				scope: "class",
				variants: [{
					beginKeywords: "enum",
					illegal: /[($"]/
				}, {
					beginKeywords: "class interface trait",
					illegal: /[:($"]/
				}],
				relevance: 0,
				end: /\{/,
				excludeEnd: true,
				contains: [{ beginKeywords: "extends implements" }, hljs.UNDERSCORE_TITLE_MODE]
			},
			{
				beginKeywords: "namespace",
				relevance: 0,
				end: ";",
				illegal: /[.']/,
				contains: [hljs.inherit(hljs.UNDERSCORE_TITLE_MODE, { scope: "title.class" })]
			},
			{
				beginKeywords: "use",
				relevance: 0,
				end: ";",
				contains: [{
					match: /\b(as|const|function)\b/,
					scope: "keyword"
				}, hljs.UNDERSCORE_TITLE_MODE]
			},
			STRING,
			NUMBER
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/php-template.js
function phpTemplate(hljs) {
	return {
		name: "PHP template",
		subLanguage: "xml",
		contains: [{
			begin: /<\?(php|=)?/,
			end: /\?>/,
			subLanguage: "php",
			contains: [
				{
					begin: "/\\*",
					end: "\\*/",
					skip: true
				},
				{
					begin: "b\"",
					end: "\"",
					skip: true
				},
				{
					begin: "b'",
					end: "'",
					skip: true
				},
				hljs.inherit(hljs.APOS_STRING_MODE, {
					illegal: null,
					className: null,
					contains: null,
					skip: true
				}),
				hljs.inherit(hljs.QUOTE_STRING_MODE, {
					illegal: null,
					className: null,
					contains: null,
					skip: true
				})
			]
		}]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/plaintext.js
function plaintext(hljs) {
	return {
		name: "Plain text",
		aliases: ["text", "txt"],
		disableAutodetect: true
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/python.js
function python(hljs) {
	const regex = hljs.regex;
	const IDENT_RE = /[\p{XID_Start}_]\p{XID_Continue}*/u;
	const RESERVED_WORDS = [
		"and",
		"as",
		"assert",
		"async",
		"await",
		"break",
		"case",
		"class",
		"continue",
		"def",
		"del",
		"elif",
		"else",
		"except",
		"finally",
		"for",
		"from",
		"global",
		"if",
		"import",
		"in",
		"is",
		"lambda",
		"match",
		"nonlocal|10",
		"not",
		"or",
		"pass",
		"raise",
		"return",
		"try",
		"while",
		"with",
		"yield"
	];
	const KEYWORDS = {
		$pattern: /[A-Za-z]\w+|__\w+__/,
		keyword: RESERVED_WORDS,
		built_in: [
			"__import__",
			"abs",
			"all",
			"any",
			"ascii",
			"bin",
			"bool",
			"breakpoint",
			"bytearray",
			"bytes",
			"callable",
			"chr",
			"classmethod",
			"compile",
			"complex",
			"delattr",
			"dict",
			"dir",
			"divmod",
			"enumerate",
			"eval",
			"exec",
			"filter",
			"float",
			"format",
			"frozenset",
			"getattr",
			"globals",
			"hasattr",
			"hash",
			"help",
			"hex",
			"id",
			"input",
			"int",
			"isinstance",
			"issubclass",
			"iter",
			"len",
			"list",
			"locals",
			"map",
			"max",
			"memoryview",
			"min",
			"next",
			"object",
			"oct",
			"open",
			"ord",
			"pow",
			"print",
			"property",
			"range",
			"repr",
			"reversed",
			"round",
			"set",
			"setattr",
			"slice",
			"sorted",
			"staticmethod",
			"str",
			"sum",
			"super",
			"tuple",
			"type",
			"vars",
			"zip"
		],
		literal: [
			"__debug__",
			"Ellipsis",
			"False",
			"None",
			"NotImplemented",
			"True"
		],
		type: [
			"Any",
			"Callable",
			"Coroutine",
			"Dict",
			"List",
			"Literal",
			"Generic",
			"Optional",
			"Sequence",
			"Set",
			"Tuple",
			"Type",
			"Union"
		]
	};
	const PROMPT = {
		className: "meta",
		begin: /^(>>>|\.\.\.) /
	};
	const SUBST = {
		className: "subst",
		begin: /\{/,
		end: /\}/,
		keywords: KEYWORDS,
		illegal: /#/
	};
	const LITERAL_BRACKET = {
		begin: /\{\{/,
		relevance: 0
	};
	const STRING = {
		className: "string",
		contains: [hljs.BACKSLASH_ESCAPE],
		variants: [
			{
				begin: /([uU]|[bB]|[rR]|[bB][rR]|[rR][bB])?'''/,
				end: /'''/,
				contains: [hljs.BACKSLASH_ESCAPE, PROMPT],
				relevance: 10
			},
			{
				begin: /([uU]|[bB]|[rR]|[bB][rR]|[rR][bB])?"""/,
				end: /"""/,
				contains: [hljs.BACKSLASH_ESCAPE, PROMPT],
				relevance: 10
			},
			{
				begin: /([fF][rR]|[rR][fF]|[fF])'''/,
				end: /'''/,
				contains: [
					hljs.BACKSLASH_ESCAPE,
					PROMPT,
					LITERAL_BRACKET,
					SUBST
				]
			},
			{
				begin: /([fF][rR]|[rR][fF]|[fF])"""/,
				end: /"""/,
				contains: [
					hljs.BACKSLASH_ESCAPE,
					PROMPT,
					LITERAL_BRACKET,
					SUBST
				]
			},
			{
				begin: /([uU]|[rR])'/,
				end: /'/,
				relevance: 10
			},
			{
				begin: /([uU]|[rR])"/,
				end: /"/,
				relevance: 10
			},
			{
				begin: /([bB]|[bB][rR]|[rR][bB])'/,
				end: /'/
			},
			{
				begin: /([bB]|[bB][rR]|[rR][bB])"/,
				end: /"/
			},
			{
				begin: /([fF][rR]|[rR][fF]|[fF])'/,
				end: /'/,
				contains: [
					hljs.BACKSLASH_ESCAPE,
					LITERAL_BRACKET,
					SUBST
				]
			},
			{
				begin: /([fF][rR]|[rR][fF]|[fF])"/,
				end: /"/,
				contains: [
					hljs.BACKSLASH_ESCAPE,
					LITERAL_BRACKET,
					SUBST
				]
			},
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE
		]
	};
	const digitpart = "[0-9](_?[0-9])*";
	const pointfloat = `(\\b(${digitpart}))?\\.(${digitpart})|\\b(${digitpart})\\.`;
	const lookahead = `\\b|${RESERVED_WORDS.join("|")}`;
	const NUMBER = {
		className: "number",
		relevance: 0,
		variants: [
			{ begin: `(\\b(${digitpart})|(${pointfloat}))[eE][+-]?(${digitpart})[jJ]?(?=${lookahead})` },
			{ begin: `(${pointfloat})[jJ]?` },
			{ begin: `\\b([1-9](_?[0-9])*|0+(_?0)*)[lLjJ]?(?=${lookahead})` },
			{ begin: `\\b0[bB](_?[01])+[lL]?(?=${lookahead})` },
			{ begin: `\\b0[oO](_?[0-7])+[lL]?(?=${lookahead})` },
			{ begin: `\\b0[xX](_?[0-9a-fA-F])+[lL]?(?=${lookahead})` },
			{ begin: `\\b(${digitpart})[jJ](?=${lookahead})` }
		]
	};
	const COMMENT_TYPE = {
		className: "comment",
		begin: regex.lookahead(/# type:/),
		end: /$/,
		keywords: KEYWORDS,
		contains: [{ begin: /# type:/ }, {
			begin: /#/,
			end: /\b\B/,
			endsWithParent: true
		}]
	};
	const PARAMS = {
		className: "params",
		variants: [{
			className: "",
			begin: /\(\s*\)/,
			skip: true
		}, {
			begin: /\(/,
			end: /\)/,
			excludeBegin: true,
			excludeEnd: true,
			keywords: KEYWORDS,
			contains: [
				"self",
				PROMPT,
				NUMBER,
				STRING,
				hljs.HASH_COMMENT_MODE
			]
		}]
	};
	SUBST.contains = [
		STRING,
		NUMBER,
		PROMPT
	];
	return {
		name: "Python",
		aliases: [
			"py",
			"gyp",
			"ipython"
		],
		unicodeRegex: true,
		keywords: KEYWORDS,
		illegal: /(<\/|\?)|=>/,
		contains: [
			PROMPT,
			NUMBER,
			{
				scope: "variable.language",
				match: /\bself\b/
			},
			{
				beginKeywords: "if",
				relevance: 0
			},
			{
				match: /\bor\b/,
				scope: "keyword"
			},
			STRING,
			COMMENT_TYPE,
			hljs.HASH_COMMENT_MODE,
			{
				match: [
					/\bdef/,
					/\s+/,
					IDENT_RE
				],
				scope: {
					1: "keyword",
					3: "title.function"
				},
				contains: [PARAMS]
			},
			{
				variants: [{ match: [
					/\bclass/,
					/\s+/,
					IDENT_RE,
					/\s*/,
					/\(\s*/,
					IDENT_RE,
					/\s*\)/
				] }, { match: [
					/\bclass/,
					/\s+/,
					IDENT_RE
				] }],
				scope: {
					1: "keyword",
					3: "title.class",
					6: "title.class.inherited"
				}
			},
			{
				className: "meta",
				begin: /^[\t ]*@/,
				end: /(?=#)|$/,
				contains: [
					NUMBER,
					PARAMS,
					STRING
				]
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/python-repl.js
function pythonRepl(hljs) {
	return {
		aliases: ["pycon"],
		contains: [{
			className: "meta.prompt",
			starts: {
				end: / |$/,
				starts: {
					end: "$",
					subLanguage: "python"
				}
			},
			variants: [{ begin: /^>>>(?=[ ]|$)/ }, { begin: /^\.\.\.(?=[ ]|$)/ }]
		}]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/r.js
/** @type LanguageFn */
function r(hljs) {
	const regex = hljs.regex;
	const IDENT_RE = /(?:(?:[a-zA-Z]|\.[._a-zA-Z])[._a-zA-Z0-9]*)|\.(?!\d)/;
	const NUMBER_TYPES_RE = regex.either(/0[xX][0-9a-fA-F]+\.[0-9a-fA-F]*[pP][+-]?\d+i?/, /0[xX][0-9a-fA-F]+(?:[pP][+-]?\d+)?[Li]?/, /(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?[Li]?/);
	const OPERATORS_RE = /[=!<>:]=|\|\||&&|:::?|<-|<<-|->>|->|\|>|[-+*\/?!$&|:<=>@^~]|\*\*/;
	const PUNCTUATION_RE = regex.either(/[()]/, /[{}]/, /\[\[/, /[[\]]/, /\\/, /,/);
	return {
		name: "R",
		keywords: {
			$pattern: IDENT_RE,
			keyword: "function if in break next repeat else for while",
			literal: "NULL NA TRUE FALSE Inf NaN NA_integer_|10 NA_real_|10 NA_character_|10 NA_complex_|10",
			built_in: "LETTERS letters month.abb month.name pi T F abs acos acosh all any anyNA Arg as.call as.character as.complex as.double as.environment as.integer as.logical as.null.default as.numeric as.raw asin asinh atan atanh attr attributes baseenv browser c call ceiling class Conj cos cosh cospi cummax cummin cumprod cumsum digamma dim dimnames emptyenv exp expression floor forceAndCall gamma gc.time globalenv Im interactive invisible is.array is.atomic is.call is.character is.complex is.double is.environment is.expression is.finite is.function is.infinite is.integer is.language is.list is.logical is.matrix is.na is.name is.nan is.null is.numeric is.object is.pairlist is.raw is.recursive is.single is.symbol lazyLoadDBfetch length lgamma list log max min missing Mod names nargs nzchar oldClass on.exit pos.to.env proc.time prod quote range Re rep retracemem return round seq_along seq_len seq.int sign signif sin sinh sinpi sqrt standardGeneric substitute sum switch tan tanh tanpi tracemem trigamma trunc unclass untracemem UseMethod xtfrm"
		},
		contains: [
			hljs.COMMENT(/#'/, /$/, { contains: [
				{
					scope: "doctag",
					match: /@examples/,
					starts: {
						end: regex.lookahead(regex.either(/\n^#'\s*(?=@[a-zA-Z]+)/, /\n^(?!#')/)),
						endsParent: true
					}
				},
				{
					scope: "doctag",
					begin: "@param",
					end: /$/,
					contains: [{
						scope: "variable",
						variants: [{ match: IDENT_RE }, { match: /`(?:\\.|[^`\\])+`/ }],
						endsParent: true
					}]
				},
				{
					scope: "doctag",
					match: /@[a-zA-Z]+/
				},
				{
					scope: "keyword",
					match: /\\[a-zA-Z]+/
				}
			] }),
			hljs.HASH_COMMENT_MODE,
			{
				scope: "string",
				contains: [hljs.BACKSLASH_ESCAPE],
				variants: [
					hljs.END_SAME_AS_BEGIN({
						begin: /[rR]"(-*)\(/,
						end: /\)(-*)"/
					}),
					hljs.END_SAME_AS_BEGIN({
						begin: /[rR]"(-*)\{/,
						end: /\}(-*)"/
					}),
					hljs.END_SAME_AS_BEGIN({
						begin: /[rR]"(-*)\[/,
						end: /\](-*)"/
					}),
					hljs.END_SAME_AS_BEGIN({
						begin: /[rR]'(-*)\(/,
						end: /\)(-*)'/
					}),
					hljs.END_SAME_AS_BEGIN({
						begin: /[rR]'(-*)\{/,
						end: /\}(-*)'/
					}),
					hljs.END_SAME_AS_BEGIN({
						begin: /[rR]'(-*)\[/,
						end: /\](-*)'/
					}),
					{
						begin: "\"",
						end: "\"",
						relevance: 0
					},
					{
						begin: "'",
						end: "'",
						relevance: 0
					}
				]
			},
			{
				relevance: 0,
				variants: [
					{
						scope: {
							1: "operator",
							2: "number"
						},
						match: [OPERATORS_RE, NUMBER_TYPES_RE]
					},
					{
						scope: {
							1: "operator",
							2: "number"
						},
						match: [/%[^%]*%/, NUMBER_TYPES_RE]
					},
					{
						scope: {
							1: "punctuation",
							2: "number"
						},
						match: [PUNCTUATION_RE, NUMBER_TYPES_RE]
					},
					{
						scope: { 2: "number" },
						match: [/[^a-zA-Z0-9._]|^/, NUMBER_TYPES_RE]
					}
				]
			},
			{
				scope: { 3: "operator" },
				match: [
					IDENT_RE,
					/\s+/,
					/<-/,
					/\s+/
				]
			},
			{
				scope: "operator",
				relevance: 0,
				variants: [{ match: OPERATORS_RE }, { match: /%[^%]*%/ }]
			},
			{
				scope: "punctuation",
				relevance: 0,
				match: PUNCTUATION_RE
			},
			{
				begin: "`",
				end: "`",
				contains: [{ begin: /\\./ }]
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/ruby.js
function ruby(hljs) {
	const regex = hljs.regex;
	const RUBY_METHOD_RE = "([a-zA-Z_]\\w*[!?=]?|[-+~]@|<<|>>|=~|===?|<=>|[<>]=?|\\*\\*|[-/+%^&*~`|]|\\[\\]=?)";
	const CLASS_NAME_RE = regex.either(/\b([A-Z]+[a-z0-9]+)+/, /\b([A-Z]+[a-z0-9]+)+[A-Z]+/);
	const CLASS_NAME_WITH_NAMESPACE_RE = regex.concat(CLASS_NAME_RE, /(::\w+)*/);
	const RUBY_KEYWORDS = {
		"variable.constant": [
			"__FILE__",
			"__LINE__",
			"__ENCODING__"
		],
		"variable.language": ["self", "super"],
		keyword: [
			"alias",
			"and",
			"begin",
			"BEGIN",
			"break",
			"case",
			"class",
			"defined",
			"do",
			"else",
			"elsif",
			"end",
			"END",
			"ensure",
			"for",
			"if",
			"in",
			"module",
			"next",
			"not",
			"or",
			"redo",
			"require",
			"rescue",
			"retry",
			"return",
			"then",
			"undef",
			"unless",
			"until",
			"when",
			"while",
			"yield",
			...[
				"include",
				"extend",
				"prepend",
				"public",
				"private",
				"protected",
				"raise",
				"throw"
			]
		],
		built_in: [
			"proc",
			"lambda",
			"attr_accessor",
			"attr_reader",
			"attr_writer",
			"define_method",
			"private_constant",
			"module_function"
		],
		literal: [
			"true",
			"false",
			"nil"
		]
	};
	const YARDOCTAG = {
		className: "doctag",
		begin: "@[A-Za-z]+"
	};
	const IRB_OBJECT = {
		begin: "#<",
		end: ">"
	};
	const COMMENT_MODES = [
		hljs.COMMENT("#", "$", { contains: [YARDOCTAG] }),
		hljs.COMMENT("^=begin", "^=end", {
			contains: [YARDOCTAG],
			relevance: 10
		}),
		hljs.COMMENT("^__END__", hljs.MATCH_NOTHING_RE)
	];
	const SUBST = {
		className: "subst",
		begin: /#\{/,
		end: /\}/,
		keywords: RUBY_KEYWORDS
	};
	const STRING = {
		className: "string",
		contains: [hljs.BACKSLASH_ESCAPE, SUBST],
		variants: [
			{
				begin: /'/,
				end: /'/
			},
			{
				begin: /"/,
				end: /"/
			},
			{
				begin: /`/,
				end: /`/
			},
			{
				begin: /%[qQwWx]?\(/,
				end: /\)/
			},
			{
				begin: /%[qQwWx]?\[/,
				end: /\]/
			},
			{
				begin: /%[qQwWx]?\{/,
				end: /\}/
			},
			{
				begin: /%[qQwWx]?</,
				end: />/
			},
			{
				begin: /%[qQwWx]?\//,
				end: /\//
			},
			{
				begin: /%[qQwWx]?%/,
				end: /%/
			},
			{
				begin: /%[qQwWx]?-/,
				end: /-/
			},
			{
				begin: /%[qQwWx]?\|/,
				end: /\|/
			},
			{ begin: /\B\?(\\\d{1,3})/ },
			{ begin: /\B\?(\\x[A-Fa-f0-9]{1,2})/ },
			{ begin: /\B\?(\\u\{?[A-Fa-f0-9]{1,6}\}?)/ },
			{ begin: /\B\?(\\M-\\C-|\\M-\\c|\\c\\M-|\\M-|\\C-\\M-)[\x20-\x7e]/ },
			{ begin: /\B\?\\(c|C-)[\x20-\x7e]/ },
			{ begin: /\B\?\\?\S/ },
			{
				begin: regex.concat(/<<[-~]?'?/, regex.lookahead(/(\w+)(?=\W)[^\n]*\n(?:[^\n]*\n)*?\s*\1\b/)),
				contains: [hljs.END_SAME_AS_BEGIN({
					begin: /(\w+)/,
					end: /(\w+)/,
					contains: [hljs.BACKSLASH_ESCAPE, SUBST]
				})]
			}
		]
	};
	const decimal = "[1-9](_?[0-9])*|0";
	const digits = "[0-9](_?[0-9])*";
	const NUMBER = {
		className: "number",
		relevance: 0,
		variants: [
			{ begin: `\\b(${decimal})(\\.(${digits}))?([eE][+-]?(${digits})|r)?i?\\b` },
			{ begin: "\\b0[dD][0-9](_?[0-9])*r?i?\\b" },
			{ begin: "\\b0[bB][0-1](_?[0-1])*r?i?\\b" },
			{ begin: "\\b0[oO][0-7](_?[0-7])*r?i?\\b" },
			{ begin: "\\b0[xX][0-9a-fA-F](_?[0-9a-fA-F])*r?i?\\b" },
			{ begin: "\\b0(_?[0-7])+r?i?\\b" }
		]
	};
	const PARAMS = { variants: [{ match: /\(\)/ }, {
		className: "params",
		begin: /\(/,
		end: /(?=\))/,
		excludeBegin: true,
		endsParent: true,
		keywords: RUBY_KEYWORDS
	}] };
	const RUBY_DEFAULT_CONTAINS = [
		STRING,
		{
			variants: [{ match: [
				/class\s+/,
				CLASS_NAME_WITH_NAMESPACE_RE,
				/\s+<\s+/,
				CLASS_NAME_WITH_NAMESPACE_RE
			] }, { match: [/\b(class|module)\s+/, CLASS_NAME_WITH_NAMESPACE_RE] }],
			scope: {
				2: "title.class",
				4: "title.class.inherited"
			},
			keywords: RUBY_KEYWORDS
		},
		{
			match: [/(include|extend)\s+/, CLASS_NAME_WITH_NAMESPACE_RE],
			scope: { 2: "title.class" },
			keywords: RUBY_KEYWORDS
		},
		{
			relevance: 0,
			match: [CLASS_NAME_WITH_NAMESPACE_RE, /\.new[. (]/],
			scope: { 1: "title.class" }
		},
		{
			relevance: 0,
			match: /\b[A-Z][A-Z_0-9]+\b/,
			className: "variable.constant"
		},
		{
			relevance: 0,
			match: CLASS_NAME_RE,
			scope: "title.class"
		},
		{
			match: [
				/def/,
				/\s+/,
				RUBY_METHOD_RE
			],
			scope: {
				1: "keyword",
				3: "title.function"
			},
			contains: [PARAMS]
		},
		{ begin: hljs.IDENT_RE + "::" },
		{
			className: "symbol",
			begin: hljs.UNDERSCORE_IDENT_RE + "(!|\\?)?:",
			relevance: 0
		},
		{
			className: "symbol",
			begin: ":(?!\\s)",
			contains: [STRING, { begin: RUBY_METHOD_RE }],
			relevance: 0
		},
		NUMBER,
		{
			className: "variable",
			begin: "(\\$\\W)|((\\$|@@?)(\\w+))(?=[^@$?])(?![A-Za-z])(?![@$?'])"
		},
		{
			className: "params",
			begin: /\|(?!=)/,
			end: /\|/,
			excludeBegin: true,
			excludeEnd: true,
			relevance: 0,
			keywords: RUBY_KEYWORDS
		},
		{
			begin: "(" + hljs.RE_STARTERS_RE + "|unless)\\s*",
			keywords: "unless",
			contains: [{
				className: "regexp",
				contains: [hljs.BACKSLASH_ESCAPE, SUBST],
				illegal: /\n/,
				variants: [
					{
						begin: "/",
						end: "/[a-z]*"
					},
					{
						begin: /%r\{/,
						end: /\}[a-z]*/
					},
					{
						begin: "%r\\(",
						end: "\\)[a-z]*"
					},
					{
						begin: "%r!",
						end: "![a-z]*"
					},
					{
						begin: "%r\\[",
						end: "\\][a-z]*"
					}
				]
			}].concat(IRB_OBJECT, COMMENT_MODES),
			relevance: 0
		}
	].concat(IRB_OBJECT, COMMENT_MODES);
	SUBST.contains = RUBY_DEFAULT_CONTAINS;
	PARAMS.contains = RUBY_DEFAULT_CONTAINS;
	const IRB_DEFAULT = [{
		begin: /^\s*=>/,
		starts: {
			end: "$",
			contains: RUBY_DEFAULT_CONTAINS
		}
	}, {
		className: "meta.prompt",
		begin: "^([>?]>|[\\w#]+\\(\\w+\\):\\d+:\\d+[>*]|(\\w+-)?\\d+\\.\\d+\\.\\d+(p\\d+)?[^\\d][^>]+>)(?=[ ])",
		starts: {
			end: "$",
			keywords: RUBY_KEYWORDS,
			contains: RUBY_DEFAULT_CONTAINS
		}
	}];
	COMMENT_MODES.unshift(IRB_OBJECT);
	return {
		name: "Ruby",
		aliases: [
			"rb",
			"gemspec",
			"podspec",
			"thor",
			"irb"
		],
		keywords: RUBY_KEYWORDS,
		illegal: /\/\*/,
		contains: [hljs.SHEBANG({ binary: "ruby" })].concat(IRB_DEFAULT).concat(COMMENT_MODES).concat(RUBY_DEFAULT_CONTAINS)
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/rust.js
/** @type LanguageFn */
function rust(hljs) {
	const regex = hljs.regex;
	const RAW_IDENTIFIER = /(r#)?/;
	const UNDERSCORE_IDENT_RE = regex.concat(RAW_IDENTIFIER, hljs.UNDERSCORE_IDENT_RE);
	const IDENT_RE = regex.concat(RAW_IDENTIFIER, hljs.IDENT_RE);
	const FUNCTION_INVOKE = {
		className: "title.function.invoke",
		relevance: 0,
		begin: regex.concat(/\b/, /(?!let|for|while|if|else|match\b)/, IDENT_RE, regex.lookahead(/\s*\(/))
	};
	const KEYWORDS = [
		"abstract",
		"as",
		"async",
		"await",
		"become",
		"box",
		"break",
		"const",
		"continue",
		"crate",
		"do",
		"dyn",
		"else",
		"enum",
		"extern",
		"false",
		"final",
		"fn",
		"for",
		"if",
		"impl",
		"in",
		"let",
		"loop",
		"macro",
		"match",
		"mod",
		"move",
		"mut",
		"override",
		"priv",
		"pub",
		"ref",
		"return",
		"self",
		"Self",
		"static",
		"struct",
		"super",
		"trait",
		"true",
		"try",
		"type",
		"typeof",
		"union",
		"unsafe",
		"unsized",
		"use",
		"virtual",
		"where",
		"while",
		"yield"
	];
	const LITERALS = [
		"true",
		"false",
		"Some",
		"None",
		"Ok",
		"Err"
	];
	const BUILTINS = [
		"drop ",
		"Copy",
		"Send",
		"Sized",
		"Sync",
		"Drop",
		"Fn",
		"FnMut",
		"FnOnce",
		"ToOwned",
		"Clone",
		"Debug",
		"PartialEq",
		"PartialOrd",
		"Eq",
		"Ord",
		"AsRef",
		"AsMut",
		"Into",
		"From",
		"Default",
		"Iterator",
		"Extend",
		"IntoIterator",
		"DoubleEndedIterator",
		"ExactSizeIterator",
		"SliceConcatExt",
		"ToString",
		"assert!",
		"assert_eq!",
		"bitflags!",
		"bytes!",
		"cfg!",
		"col!",
		"concat!",
		"concat_idents!",
		"debug_assert!",
		"debug_assert_eq!",
		"env!",
		"eprintln!",
		"panic!",
		"file!",
		"format!",
		"format_args!",
		"include_bytes!",
		"include_str!",
		"line!",
		"local_data_key!",
		"module_path!",
		"option_env!",
		"print!",
		"println!",
		"select!",
		"stringify!",
		"try!",
		"unimplemented!",
		"unreachable!",
		"vec!",
		"write!",
		"writeln!",
		"macro_rules!",
		"assert_ne!",
		"debug_assert_ne!"
	];
	const TYPES = [
		"i8",
		"i16",
		"i32",
		"i64",
		"i128",
		"isize",
		"u8",
		"u16",
		"u32",
		"u64",
		"u128",
		"usize",
		"f32",
		"f64",
		"str",
		"char",
		"bool",
		"Box",
		"Option",
		"Result",
		"String",
		"Vec"
	];
	return {
		name: "Rust",
		aliases: ["rs"],
		keywords: {
			$pattern: hljs.IDENT_RE + "!?",
			type: TYPES,
			keyword: KEYWORDS,
			literal: LITERALS,
			built_in: BUILTINS
		},
		illegal: "</",
		contains: [
			hljs.C_LINE_COMMENT_MODE,
			hljs.COMMENT("/\\*", "\\*/", { contains: ["self"] }),
			hljs.inherit(hljs.QUOTE_STRING_MODE, {
				begin: /b?"/,
				illegal: null
			}),
			{
				className: "symbol",
				begin: /'[a-zA-Z_][a-zA-Z0-9_]*(?!')/
			},
			{
				scope: "string",
				variants: [{ begin: /b?r(#*)"(.|\n)*?"\1(?!#)/ }, {
					begin: /b?'/,
					end: /'/,
					contains: [{
						scope: "char.escape",
						match: /\\('|\w|x\w{2}|u\w{4}|U\w{8})/
					}]
				}]
			},
			{
				className: "number",
				variants: [
					{ begin: "\\b0b([01_]+)([ui](8|16|32|64|128|size)|f(32|64))?" },
					{ begin: "\\b0o([0-7_]+)([ui](8|16|32|64|128|size)|f(32|64))?" },
					{ begin: "\\b0x([A-Fa-f0-9_]+)([ui](8|16|32|64|128|size)|f(32|64))?" },
					{ begin: "\\b(\\d[\\d_]*(\\.[0-9_]+)?([eE][+-]?[0-9_]+)?)([ui](8|16|32|64|128|size)|f(32|64))?" }
				],
				relevance: 0
			},
			{
				begin: [
					/fn/,
					/\s+/,
					UNDERSCORE_IDENT_RE
				],
				className: {
					1: "keyword",
					3: "title.function"
				}
			},
			{
				className: "meta",
				begin: "#!?\\[",
				end: "\\]",
				contains: [{
					className: "string",
					begin: /"/,
					end: /"/,
					contains: [hljs.BACKSLASH_ESCAPE]
				}]
			},
			{
				begin: [
					/let/,
					/\s+/,
					/(?:mut\s+)?/,
					UNDERSCORE_IDENT_RE
				],
				className: {
					1: "keyword",
					3: "keyword",
					4: "variable"
				}
			},
			{
				begin: [
					/for/,
					/\s+/,
					UNDERSCORE_IDENT_RE,
					/\s+/,
					/in/
				],
				className: {
					1: "keyword",
					3: "variable",
					5: "keyword"
				}
			},
			{
				begin: [
					/type/,
					/\s+/,
					UNDERSCORE_IDENT_RE
				],
				className: {
					1: "keyword",
					3: "title.class"
				}
			},
			{
				begin: [
					/(?:trait|enum|struct|union|impl|for)/,
					/\s+/,
					UNDERSCORE_IDENT_RE
				],
				className: {
					1: "keyword",
					3: "title.class"
				}
			},
			{
				begin: hljs.IDENT_RE + "::",
				keywords: {
					keyword: "Self",
					built_in: BUILTINS,
					type: TYPES
				}
			},
			{
				className: "punctuation",
				begin: "->"
			},
			FUNCTION_INVOKE
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/scss.js
var MODES = (hljs) => {
	return {
		IMPORTANT: {
			scope: "meta",
			begin: "!important"
		},
		BLOCK_COMMENT: hljs.C_BLOCK_COMMENT_MODE,
		HEXCOLOR: {
			scope: "number",
			begin: /#(([0-9a-fA-F]{3,4})|(([0-9a-fA-F]{2}){3,4}))\b/
		},
		FUNCTION_DISPATCH: {
			className: "built_in",
			begin: /[\w-]+(?=\()/
		},
		ATTRIBUTE_SELECTOR_MODE: {
			scope: "selector-attr",
			begin: /\[/,
			end: /\]/,
			illegal: "$",
			contains: [hljs.APOS_STRING_MODE, hljs.QUOTE_STRING_MODE]
		},
		CSS_NUMBER_MODE: {
			scope: "number",
			begin: hljs.NUMBER_RE + "(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",
			relevance: 0
		},
		CSS_VARIABLE: {
			className: "attr",
			begin: /--[A-Za-z_][A-Za-z0-9_-]*/
		}
	};
};
var HTML_TAGS = [
	"a",
	"abbr",
	"address",
	"article",
	"aside",
	"audio",
	"b",
	"blockquote",
	"body",
	"button",
	"canvas",
	"caption",
	"cite",
	"code",
	"dd",
	"del",
	"details",
	"dfn",
	"div",
	"dl",
	"dt",
	"em",
	"fieldset",
	"figcaption",
	"figure",
	"footer",
	"form",
	"h1",
	"h2",
	"h3",
	"h4",
	"h5",
	"h6",
	"header",
	"hgroup",
	"html",
	"i",
	"iframe",
	"img",
	"input",
	"ins",
	"kbd",
	"label",
	"legend",
	"li",
	"main",
	"mark",
	"menu",
	"nav",
	"object",
	"ol",
	"optgroup",
	"option",
	"p",
	"picture",
	"q",
	"quote",
	"samp",
	"section",
	"select",
	"source",
	"span",
	"strong",
	"summary",
	"sup",
	"table",
	"tbody",
	"td",
	"textarea",
	"tfoot",
	"th",
	"thead",
	"time",
	"tr",
	"ul",
	"var",
	"video"
];
var SVG_TAGS = [
	"defs",
	"g",
	"marker",
	"mask",
	"pattern",
	"svg",
	"switch",
	"symbol",
	"feBlend",
	"feColorMatrix",
	"feComponentTransfer",
	"feComposite",
	"feConvolveMatrix",
	"feDiffuseLighting",
	"feDisplacementMap",
	"feFlood",
	"feGaussianBlur",
	"feImage",
	"feMerge",
	"feMorphology",
	"feOffset",
	"feSpecularLighting",
	"feTile",
	"feTurbulence",
	"linearGradient",
	"radialGradient",
	"stop",
	"circle",
	"ellipse",
	"image",
	"line",
	"path",
	"polygon",
	"polyline",
	"rect",
	"text",
	"use",
	"textPath",
	"tspan",
	"foreignObject",
	"clipPath"
];
var TAGS = [...HTML_TAGS, ...SVG_TAGS];
var MEDIA_FEATURES = [
	"any-hover",
	"any-pointer",
	"aspect-ratio",
	"color",
	"color-gamut",
	"color-index",
	"device-aspect-ratio",
	"device-height",
	"device-width",
	"display-mode",
	"forced-colors",
	"grid",
	"height",
	"hover",
	"inverted-colors",
	"monochrome",
	"orientation",
	"overflow-block",
	"overflow-inline",
	"pointer",
	"prefers-color-scheme",
	"prefers-contrast",
	"prefers-reduced-motion",
	"prefers-reduced-transparency",
	"resolution",
	"scan",
	"scripting",
	"update",
	"width",
	"min-width",
	"max-width",
	"min-height",
	"max-height"
].sort().reverse();
var PSEUDO_CLASSES = [
	"active",
	"any-link",
	"blank",
	"checked",
	"current",
	"default",
	"defined",
	"dir",
	"disabled",
	"drop",
	"empty",
	"enabled",
	"first",
	"first-child",
	"first-of-type",
	"fullscreen",
	"future",
	"focus",
	"focus-visible",
	"focus-within",
	"has",
	"host",
	"host-context",
	"hover",
	"indeterminate",
	"in-range",
	"invalid",
	"is",
	"lang",
	"last-child",
	"last-of-type",
	"left",
	"link",
	"local-link",
	"not",
	"nth-child",
	"nth-col",
	"nth-last-child",
	"nth-last-col",
	"nth-last-of-type",
	"nth-of-type",
	"only-child",
	"only-of-type",
	"optional",
	"out-of-range",
	"past",
	"placeholder-shown",
	"read-only",
	"read-write",
	"required",
	"right",
	"root",
	"scope",
	"target",
	"target-within",
	"user-invalid",
	"valid",
	"visited",
	"where"
].sort().reverse();
var PSEUDO_ELEMENTS = [
	"after",
	"backdrop",
	"before",
	"cue",
	"cue-region",
	"first-letter",
	"first-line",
	"grammar-error",
	"marker",
	"part",
	"placeholder",
	"selection",
	"slotted",
	"spelling-error"
].sort().reverse();
var ATTRIBUTES = [
	"accent-color",
	"align-content",
	"align-items",
	"align-self",
	"alignment-baseline",
	"all",
	"anchor-name",
	"animation",
	"animation-composition",
	"animation-delay",
	"animation-direction",
	"animation-duration",
	"animation-fill-mode",
	"animation-iteration-count",
	"animation-name",
	"animation-play-state",
	"animation-range",
	"animation-range-end",
	"animation-range-start",
	"animation-timeline",
	"animation-timing-function",
	"appearance",
	"aspect-ratio",
	"backdrop-filter",
	"backface-visibility",
	"background",
	"background-attachment",
	"background-blend-mode",
	"background-clip",
	"background-color",
	"background-image",
	"background-origin",
	"background-position",
	"background-position-x",
	"background-position-y",
	"background-repeat",
	"background-size",
	"baseline-shift",
	"block-size",
	"border",
	"border-block",
	"border-block-color",
	"border-block-end",
	"border-block-end-color",
	"border-block-end-style",
	"border-block-end-width",
	"border-block-start",
	"border-block-start-color",
	"border-block-start-style",
	"border-block-start-width",
	"border-block-style",
	"border-block-width",
	"border-bottom",
	"border-bottom-color",
	"border-bottom-left-radius",
	"border-bottom-right-radius",
	"border-bottom-style",
	"border-bottom-width",
	"border-collapse",
	"border-color",
	"border-end-end-radius",
	"border-end-start-radius",
	"border-image",
	"border-image-outset",
	"border-image-repeat",
	"border-image-slice",
	"border-image-source",
	"border-image-width",
	"border-inline",
	"border-inline-color",
	"border-inline-end",
	"border-inline-end-color",
	"border-inline-end-style",
	"border-inline-end-width",
	"border-inline-start",
	"border-inline-start-color",
	"border-inline-start-style",
	"border-inline-start-width",
	"border-inline-style",
	"border-inline-width",
	"border-left",
	"border-left-color",
	"border-left-style",
	"border-left-width",
	"border-radius",
	"border-right",
	"border-right-color",
	"border-right-style",
	"border-right-width",
	"border-spacing",
	"border-start-end-radius",
	"border-start-start-radius",
	"border-style",
	"border-top",
	"border-top-color",
	"border-top-left-radius",
	"border-top-right-radius",
	"border-top-style",
	"border-top-width",
	"border-width",
	"bottom",
	"box-align",
	"box-decoration-break",
	"box-direction",
	"box-flex",
	"box-flex-group",
	"box-lines",
	"box-ordinal-group",
	"box-orient",
	"box-pack",
	"box-shadow",
	"box-sizing",
	"break-after",
	"break-before",
	"break-inside",
	"caption-side",
	"caret-color",
	"clear",
	"clip",
	"clip-path",
	"clip-rule",
	"color",
	"color-interpolation",
	"color-interpolation-filters",
	"color-profile",
	"color-rendering",
	"color-scheme",
	"column-count",
	"column-fill",
	"column-gap",
	"column-rule",
	"column-rule-color",
	"column-rule-style",
	"column-rule-width",
	"column-span",
	"column-width",
	"columns",
	"contain",
	"contain-intrinsic-block-size",
	"contain-intrinsic-height",
	"contain-intrinsic-inline-size",
	"contain-intrinsic-size",
	"contain-intrinsic-width",
	"container",
	"container-name",
	"container-type",
	"content",
	"content-visibility",
	"counter-increment",
	"counter-reset",
	"counter-set",
	"cue",
	"cue-after",
	"cue-before",
	"cursor",
	"cx",
	"cy",
	"direction",
	"display",
	"dominant-baseline",
	"empty-cells",
	"enable-background",
	"field-sizing",
	"fill",
	"fill-opacity",
	"fill-rule",
	"filter",
	"flex",
	"flex-basis",
	"flex-direction",
	"flex-flow",
	"flex-grow",
	"flex-shrink",
	"flex-wrap",
	"float",
	"flood-color",
	"flood-opacity",
	"flow",
	"font",
	"font-display",
	"font-family",
	"font-feature-settings",
	"font-kerning",
	"font-language-override",
	"font-optical-sizing",
	"font-palette",
	"font-size",
	"font-size-adjust",
	"font-smooth",
	"font-smoothing",
	"font-stretch",
	"font-style",
	"font-synthesis",
	"font-synthesis-position",
	"font-synthesis-small-caps",
	"font-synthesis-style",
	"font-synthesis-weight",
	"font-variant",
	"font-variant-alternates",
	"font-variant-caps",
	"font-variant-east-asian",
	"font-variant-emoji",
	"font-variant-ligatures",
	"font-variant-numeric",
	"font-variant-position",
	"font-variation-settings",
	"font-weight",
	"forced-color-adjust",
	"gap",
	"glyph-orientation-horizontal",
	"glyph-orientation-vertical",
	"grid",
	"grid-area",
	"grid-auto-columns",
	"grid-auto-flow",
	"grid-auto-rows",
	"grid-column",
	"grid-column-end",
	"grid-column-start",
	"grid-gap",
	"grid-row",
	"grid-row-end",
	"grid-row-start",
	"grid-template",
	"grid-template-areas",
	"grid-template-columns",
	"grid-template-rows",
	"hanging-punctuation",
	"height",
	"hyphenate-character",
	"hyphenate-limit-chars",
	"hyphens",
	"icon",
	"image-orientation",
	"image-rendering",
	"image-resolution",
	"ime-mode",
	"initial-letter",
	"initial-letter-align",
	"inline-size",
	"inset",
	"inset-area",
	"inset-block",
	"inset-block-end",
	"inset-block-start",
	"inset-inline",
	"inset-inline-end",
	"inset-inline-start",
	"isolation",
	"justify-content",
	"justify-items",
	"justify-self",
	"kerning",
	"left",
	"letter-spacing",
	"lighting-color",
	"line-break",
	"line-height",
	"line-height-step",
	"list-style",
	"list-style-image",
	"list-style-position",
	"list-style-type",
	"margin",
	"margin-block",
	"margin-block-end",
	"margin-block-start",
	"margin-bottom",
	"margin-inline",
	"margin-inline-end",
	"margin-inline-start",
	"margin-left",
	"margin-right",
	"margin-top",
	"margin-trim",
	"marker",
	"marker-end",
	"marker-mid",
	"marker-start",
	"marks",
	"mask",
	"mask-border",
	"mask-border-mode",
	"mask-border-outset",
	"mask-border-repeat",
	"mask-border-slice",
	"mask-border-source",
	"mask-border-width",
	"mask-clip",
	"mask-composite",
	"mask-image",
	"mask-mode",
	"mask-origin",
	"mask-position",
	"mask-repeat",
	"mask-size",
	"mask-type",
	"masonry-auto-flow",
	"math-depth",
	"math-shift",
	"math-style",
	"max-block-size",
	"max-height",
	"max-inline-size",
	"max-width",
	"min-block-size",
	"min-height",
	"min-inline-size",
	"min-width",
	"mix-blend-mode",
	"nav-down",
	"nav-index",
	"nav-left",
	"nav-right",
	"nav-up",
	"none",
	"normal",
	"object-fit",
	"object-position",
	"offset",
	"offset-anchor",
	"offset-distance",
	"offset-path",
	"offset-position",
	"offset-rotate",
	"opacity",
	"order",
	"orphans",
	"outline",
	"outline-color",
	"outline-offset",
	"outline-style",
	"outline-width",
	"overflow",
	"overflow-anchor",
	"overflow-block",
	"overflow-clip-margin",
	"overflow-inline",
	"overflow-wrap",
	"overflow-x",
	"overflow-y",
	"overlay",
	"overscroll-behavior",
	"overscroll-behavior-block",
	"overscroll-behavior-inline",
	"overscroll-behavior-x",
	"overscroll-behavior-y",
	"padding",
	"padding-block",
	"padding-block-end",
	"padding-block-start",
	"padding-bottom",
	"padding-inline",
	"padding-inline-end",
	"padding-inline-start",
	"padding-left",
	"padding-right",
	"padding-top",
	"page",
	"page-break-after",
	"page-break-before",
	"page-break-inside",
	"paint-order",
	"pause",
	"pause-after",
	"pause-before",
	"perspective",
	"perspective-origin",
	"place-content",
	"place-items",
	"place-self",
	"pointer-events",
	"position",
	"position-anchor",
	"position-visibility",
	"print-color-adjust",
	"quotes",
	"r",
	"resize",
	"rest",
	"rest-after",
	"rest-before",
	"right",
	"rotate",
	"row-gap",
	"ruby-align",
	"ruby-position",
	"scale",
	"scroll-behavior",
	"scroll-margin",
	"scroll-margin-block",
	"scroll-margin-block-end",
	"scroll-margin-block-start",
	"scroll-margin-bottom",
	"scroll-margin-inline",
	"scroll-margin-inline-end",
	"scroll-margin-inline-start",
	"scroll-margin-left",
	"scroll-margin-right",
	"scroll-margin-top",
	"scroll-padding",
	"scroll-padding-block",
	"scroll-padding-block-end",
	"scroll-padding-block-start",
	"scroll-padding-bottom",
	"scroll-padding-inline",
	"scroll-padding-inline-end",
	"scroll-padding-inline-start",
	"scroll-padding-left",
	"scroll-padding-right",
	"scroll-padding-top",
	"scroll-snap-align",
	"scroll-snap-stop",
	"scroll-snap-type",
	"scroll-timeline",
	"scroll-timeline-axis",
	"scroll-timeline-name",
	"scrollbar-color",
	"scrollbar-gutter",
	"scrollbar-width",
	"shape-image-threshold",
	"shape-margin",
	"shape-outside",
	"shape-rendering",
	"speak",
	"speak-as",
	"src",
	"stop-color",
	"stop-opacity",
	"stroke",
	"stroke-dasharray",
	"stroke-dashoffset",
	"stroke-linecap",
	"stroke-linejoin",
	"stroke-miterlimit",
	"stroke-opacity",
	"stroke-width",
	"tab-size",
	"table-layout",
	"text-align",
	"text-align-all",
	"text-align-last",
	"text-anchor",
	"text-combine-upright",
	"text-decoration",
	"text-decoration-color",
	"text-decoration-line",
	"text-decoration-skip",
	"text-decoration-skip-ink",
	"text-decoration-style",
	"text-decoration-thickness",
	"text-emphasis",
	"text-emphasis-color",
	"text-emphasis-position",
	"text-emphasis-style",
	"text-indent",
	"text-justify",
	"text-orientation",
	"text-overflow",
	"text-rendering",
	"text-shadow",
	"text-size-adjust",
	"text-transform",
	"text-underline-offset",
	"text-underline-position",
	"text-wrap",
	"text-wrap-mode",
	"text-wrap-style",
	"timeline-scope",
	"top",
	"touch-action",
	"transform",
	"transform-box",
	"transform-origin",
	"transform-style",
	"transition",
	"transition-behavior",
	"transition-delay",
	"transition-duration",
	"transition-property",
	"transition-timing-function",
	"translate",
	"unicode-bidi",
	"user-modify",
	"user-select",
	"vector-effect",
	"vertical-align",
	"view-timeline",
	"view-timeline-axis",
	"view-timeline-inset",
	"view-timeline-name",
	"view-transition-name",
	"visibility",
	"voice-balance",
	"voice-duration",
	"voice-family",
	"voice-pitch",
	"voice-range",
	"voice-rate",
	"voice-stress",
	"voice-volume",
	"white-space",
	"white-space-collapse",
	"widows",
	"width",
	"will-change",
	"word-break",
	"word-spacing",
	"word-wrap",
	"writing-mode",
	"x",
	"y",
	"z-index",
	"zoom"
].sort().reverse();
/** @type LanguageFn */
function scss(hljs) {
	const modes = MODES(hljs);
	const PSEUDO_ELEMENTS$1 = PSEUDO_ELEMENTS;
	const PSEUDO_CLASSES$1 = PSEUDO_CLASSES;
	const AT_IDENTIFIER = "@[a-z-]+";
	const AT_MODIFIERS = "and or not only";
	const VARIABLE = {
		className: "variable",
		begin: "(\\$[a-zA-Z-][a-zA-Z0-9_-]*)\\b",
		relevance: 0
	};
	return {
		name: "SCSS",
		case_insensitive: true,
		illegal: "[=/|']",
		contains: [
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			modes.CSS_NUMBER_MODE,
			{
				className: "selector-id",
				begin: "#[A-Za-z0-9_-]+",
				relevance: 0
			},
			{
				className: "selector-class",
				begin: "\\.[A-Za-z0-9_-]+",
				relevance: 0
			},
			modes.ATTRIBUTE_SELECTOR_MODE,
			{
				className: "selector-tag",
				begin: "\\b(" + TAGS.join("|") + ")\\b",
				relevance: 0
			},
			{
				className: "selector-pseudo",
				begin: ":(" + PSEUDO_CLASSES$1.join("|") + ")"
			},
			{
				className: "selector-pseudo",
				begin: ":(:)?(" + PSEUDO_ELEMENTS$1.join("|") + ")"
			},
			VARIABLE,
			{
				begin: /\(/,
				end: /\)/,
				contains: [modes.CSS_NUMBER_MODE]
			},
			modes.CSS_VARIABLE,
			{
				className: "attribute",
				begin: "\\b(" + ATTRIBUTES.join("|") + ")\\b"
			},
			{ begin: "\\b(whitespace|wait|w-resize|visible|vertical-text|vertical-ideographic|uppercase|upper-roman|upper-alpha|underline|transparent|top|thin|thick|text|text-top|text-bottom|tb-rl|table-header-group|table-footer-group|sw-resize|super|strict|static|square|solid|small-caps|separate|se-resize|scroll|s-resize|rtl|row-resize|ridge|right|repeat|repeat-y|repeat-x|relative|progress|pointer|overline|outside|outset|oblique|nowrap|not-allowed|normal|none|nw-resize|no-repeat|no-drop|newspaper|ne-resize|n-resize|move|middle|medium|ltr|lr-tb|lowercase|lower-roman|lower-alpha|loose|list-item|line|line-through|line-edge|lighter|left|keep-all|justify|italic|inter-word|inter-ideograph|inside|inset|inline|inline-block|inherit|inactive|ideograph-space|ideograph-parenthesis|ideograph-numeric|ideograph-alpha|horizontal|hidden|help|hand|groove|fixed|ellipsis|e-resize|double|dotted|distribute|distribute-space|distribute-letter|distribute-all-lines|disc|disabled|default|decimal|dashed|crosshair|collapse|col-resize|circle|char|center|capitalize|break-word|break-all|bottom|both|bolder|bold|block|bidi-override|below|baseline|auto|always|all-scroll|absolute|table|table-cell)\\b" },
			{
				begin: /:/,
				end: /[;}{]/,
				relevance: 0,
				contains: [
					modes.BLOCK_COMMENT,
					VARIABLE,
					modes.HEXCOLOR,
					modes.CSS_NUMBER_MODE,
					hljs.QUOTE_STRING_MODE,
					hljs.APOS_STRING_MODE,
					modes.IMPORTANT,
					modes.FUNCTION_DISPATCH
				]
			},
			{
				begin: "@(page|font-face)",
				keywords: {
					$pattern: AT_IDENTIFIER,
					keyword: "@page @font-face"
				}
			},
			{
				begin: "@",
				end: "[{;]",
				returnBegin: true,
				keywords: {
					$pattern: /[a-z-]+/,
					keyword: AT_MODIFIERS,
					attribute: MEDIA_FEATURES.join(" ")
				},
				contains: [
					{
						begin: AT_IDENTIFIER,
						className: "keyword"
					},
					{
						begin: /[a-z-]+(?=:)/,
						className: "attribute"
					},
					VARIABLE,
					hljs.QUOTE_STRING_MODE,
					hljs.APOS_STRING_MODE,
					modes.HEXCOLOR,
					modes.CSS_NUMBER_MODE
				]
			},
			modes.FUNCTION_DISPATCH
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/shell.js
/** @type LanguageFn */
function shell(hljs) {
	return {
		name: "Shell Session",
		aliases: ["console", "shellsession"],
		contains: [{
			className: "meta.prompt",
			begin: /^\s{0,3}[/~\w\d[\]()@-]*[>%$#][ ]?/,
			starts: {
				end: /[^\\](?=\s*$)/,
				subLanguage: "bash"
			}
		}]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/sql.js
function sql(hljs) {
	const regex = hljs.regex;
	const COMMENT_MODE = hljs.COMMENT("--", "$");
	const STRING = {
		scope: "string",
		variants: [{
			begin: /'/,
			end: /'/,
			contains: [{ match: /''/ }]
		}]
	};
	const QUOTED_IDENTIFIER = {
		begin: /"/,
		end: /"/,
		contains: [{ match: /""/ }]
	};
	const LITERALS = [
		"true",
		"false",
		"unknown"
	];
	const MULTI_WORD_TYPES = [
		"double precision",
		"large object",
		"with timezone",
		"without timezone"
	];
	const TYPES = [
		"bigint",
		"binary",
		"blob",
		"boolean",
		"char",
		"character",
		"clob",
		"date",
		"dec",
		"decfloat",
		"decimal",
		"float",
		"int",
		"integer",
		"interval",
		"nchar",
		"nclob",
		"national",
		"numeric",
		"real",
		"row",
		"smallint",
		"time",
		"timestamp",
		"varchar",
		"varying",
		"varbinary"
	];
	const NON_RESERVED_WORDS = [
		"add",
		"asc",
		"collation",
		"desc",
		"final",
		"first",
		"last",
		"view"
	];
	const RESERVED_WORDS = [
		"abs",
		"acos",
		"all",
		"allocate",
		"alter",
		"and",
		"any",
		"are",
		"array",
		"array_agg",
		"array_max_cardinality",
		"as",
		"asensitive",
		"asin",
		"asymmetric",
		"at",
		"atan",
		"atomic",
		"authorization",
		"avg",
		"begin",
		"begin_frame",
		"begin_partition",
		"between",
		"bigint",
		"binary",
		"blob",
		"boolean",
		"both",
		"by",
		"call",
		"called",
		"cardinality",
		"cascaded",
		"case",
		"cast",
		"ceil",
		"ceiling",
		"char",
		"char_length",
		"character",
		"character_length",
		"check",
		"classifier",
		"clob",
		"close",
		"coalesce",
		"collate",
		"collect",
		"column",
		"commit",
		"condition",
		"connect",
		"constraint",
		"contains",
		"convert",
		"copy",
		"corr",
		"corresponding",
		"cos",
		"cosh",
		"count",
		"covar_pop",
		"covar_samp",
		"create",
		"cross",
		"cube",
		"cume_dist",
		"current",
		"current_catalog",
		"current_date",
		"current_default_transform_group",
		"current_path",
		"current_role",
		"current_row",
		"current_schema",
		"current_time",
		"current_timestamp",
		"current_path",
		"current_role",
		"current_transform_group_for_type",
		"current_user",
		"cursor",
		"cycle",
		"date",
		"day",
		"deallocate",
		"dec",
		"decimal",
		"decfloat",
		"declare",
		"default",
		"define",
		"delete",
		"dense_rank",
		"deref",
		"describe",
		"deterministic",
		"disconnect",
		"distinct",
		"double",
		"drop",
		"dynamic",
		"each",
		"element",
		"else",
		"empty",
		"end",
		"end_frame",
		"end_partition",
		"end-exec",
		"equals",
		"escape",
		"every",
		"except",
		"exec",
		"execute",
		"exists",
		"exp",
		"external",
		"extract",
		"false",
		"fetch",
		"filter",
		"first_value",
		"float",
		"floor",
		"for",
		"foreign",
		"frame_row",
		"free",
		"from",
		"full",
		"function",
		"fusion",
		"get",
		"global",
		"grant",
		"group",
		"grouping",
		"groups",
		"having",
		"hold",
		"hour",
		"identity",
		"in",
		"indicator",
		"initial",
		"inner",
		"inout",
		"insensitive",
		"insert",
		"int",
		"integer",
		"intersect",
		"intersection",
		"interval",
		"into",
		"is",
		"join",
		"json_array",
		"json_arrayagg",
		"json_exists",
		"json_object",
		"json_objectagg",
		"json_query",
		"json_table",
		"json_table_primitive",
		"json_value",
		"lag",
		"language",
		"large",
		"last_value",
		"lateral",
		"lead",
		"leading",
		"left",
		"like",
		"like_regex",
		"listagg",
		"ln",
		"local",
		"localtime",
		"localtimestamp",
		"log",
		"log10",
		"lower",
		"match",
		"match_number",
		"match_recognize",
		"matches",
		"max",
		"member",
		"merge",
		"method",
		"min",
		"minute",
		"mod",
		"modifies",
		"module",
		"month",
		"multiset",
		"national",
		"natural",
		"nchar",
		"nclob",
		"new",
		"no",
		"none",
		"normalize",
		"not",
		"nth_value",
		"ntile",
		"null",
		"nullif",
		"numeric",
		"octet_length",
		"occurrences_regex",
		"of",
		"offset",
		"old",
		"omit",
		"on",
		"one",
		"only",
		"open",
		"or",
		"order",
		"out",
		"outer",
		"over",
		"overlaps",
		"overlay",
		"parameter",
		"partition",
		"pattern",
		"per",
		"percent",
		"percent_rank",
		"percentile_cont",
		"percentile_disc",
		"period",
		"portion",
		"position",
		"position_regex",
		"power",
		"precedes",
		"precision",
		"prepare",
		"primary",
		"procedure",
		"ptf",
		"range",
		"rank",
		"reads",
		"real",
		"recursive",
		"ref",
		"references",
		"referencing",
		"regr_avgx",
		"regr_avgy",
		"regr_count",
		"regr_intercept",
		"regr_r2",
		"regr_slope",
		"regr_sxx",
		"regr_sxy",
		"regr_syy",
		"release",
		"result",
		"return",
		"returns",
		"revoke",
		"right",
		"rollback",
		"rollup",
		"row",
		"row_number",
		"rows",
		"running",
		"savepoint",
		"scope",
		"scroll",
		"search",
		"second",
		"seek",
		"select",
		"sensitive",
		"session_user",
		"set",
		"show",
		"similar",
		"sin",
		"sinh",
		"skip",
		"smallint",
		"some",
		"specific",
		"specifictype",
		"sql",
		"sqlexception",
		"sqlstate",
		"sqlwarning",
		"sqrt",
		"start",
		"static",
		"stddev_pop",
		"stddev_samp",
		"submultiset",
		"subset",
		"substring",
		"substring_regex",
		"succeeds",
		"sum",
		"symmetric",
		"system",
		"system_time",
		"system_user",
		"table",
		"tablesample",
		"tan",
		"tanh",
		"then",
		"time",
		"timestamp",
		"timezone_hour",
		"timezone_minute",
		"to",
		"trailing",
		"translate",
		"translate_regex",
		"translation",
		"treat",
		"trigger",
		"trim",
		"trim_array",
		"true",
		"truncate",
		"uescape",
		"union",
		"unique",
		"unknown",
		"unnest",
		"update",
		"upper",
		"user",
		"using",
		"value",
		"values",
		"value_of",
		"var_pop",
		"var_samp",
		"varbinary",
		"varchar",
		"varying",
		"versioning",
		"when",
		"whenever",
		"where",
		"width_bucket",
		"window",
		"with",
		"within",
		"without",
		"year"
	];
	const RESERVED_FUNCTIONS = [
		"abs",
		"acos",
		"array_agg",
		"asin",
		"atan",
		"avg",
		"cast",
		"ceil",
		"ceiling",
		"coalesce",
		"corr",
		"cos",
		"cosh",
		"count",
		"covar_pop",
		"covar_samp",
		"cume_dist",
		"dense_rank",
		"deref",
		"element",
		"exp",
		"extract",
		"first_value",
		"floor",
		"json_array",
		"json_arrayagg",
		"json_exists",
		"json_object",
		"json_objectagg",
		"json_query",
		"json_table",
		"json_table_primitive",
		"json_value",
		"lag",
		"last_value",
		"lead",
		"listagg",
		"ln",
		"log",
		"log10",
		"lower",
		"max",
		"min",
		"mod",
		"nth_value",
		"ntile",
		"nullif",
		"percent_rank",
		"percentile_cont",
		"percentile_disc",
		"position",
		"position_regex",
		"power",
		"rank",
		"regr_avgx",
		"regr_avgy",
		"regr_count",
		"regr_intercept",
		"regr_r2",
		"regr_slope",
		"regr_sxx",
		"regr_sxy",
		"regr_syy",
		"row_number",
		"sin",
		"sinh",
		"sqrt",
		"stddev_pop",
		"stddev_samp",
		"substring",
		"substring_regex",
		"sum",
		"tan",
		"tanh",
		"translate",
		"translate_regex",
		"treat",
		"trim",
		"trim_array",
		"unnest",
		"upper",
		"value_of",
		"var_pop",
		"var_samp",
		"width_bucket"
	];
	const POSSIBLE_WITHOUT_PARENS = [
		"current_catalog",
		"current_date",
		"current_default_transform_group",
		"current_path",
		"current_role",
		"current_schema",
		"current_transform_group_for_type",
		"current_user",
		"session_user",
		"system_time",
		"system_user",
		"current_time",
		"localtime",
		"current_timestamp",
		"localtimestamp"
	];
	const COMBOS = [
		"create table",
		"insert into",
		"primary key",
		"foreign key",
		"not null",
		"alter table",
		"add constraint",
		"grouping sets",
		"on overflow",
		"character set",
		"respect nulls",
		"ignore nulls",
		"nulls first",
		"nulls last",
		"depth first",
		"breadth first"
	];
	const FUNCTIONS = RESERVED_FUNCTIONS;
	const KEYWORDS = [...RESERVED_WORDS, ...NON_RESERVED_WORDS].filter((keyword) => {
		return !RESERVED_FUNCTIONS.includes(keyword);
	});
	const VARIABLE = {
		scope: "variable",
		match: /@[a-z0-9][a-z0-9_]*/
	};
	const OPERATOR = {
		scope: "operator",
		match: /[-+*/=%^~]|&&?|\|\|?|!=?|<(?:=>?|<|>)?|>[>=]?/,
		relevance: 0
	};
	const FUNCTION_CALL = {
		match: regex.concat(/\b/, regex.either(...FUNCTIONS), /\s*\(/),
		relevance: 0,
		keywords: { built_in: FUNCTIONS }
	};
	function kws_to_regex(list) {
		return regex.concat(/\b/, regex.either(...list.map((kw) => {
			return kw.replace(/\s+/, "\\s+");
		})), /\b/);
	}
	const MULTI_WORD_KEYWORDS = {
		scope: "keyword",
		match: kws_to_regex(COMBOS),
		relevance: 0
	};
	function reduceRelevancy(list, { exceptions, when } = {}) {
		const qualifyFn = when;
		exceptions = exceptions || [];
		return list.map((item) => {
			if (item.match(/\|\d+$/) || exceptions.includes(item)) return item;
			else if (qualifyFn(item)) return `${item}|0`;
			else return item;
		});
	}
	return {
		name: "SQL",
		case_insensitive: true,
		illegal: /[{}]|<\//,
		keywords: {
			$pattern: /\b[\w\.]+/,
			keyword: reduceRelevancy(KEYWORDS, { when: (x) => x.length < 3 }),
			literal: LITERALS,
			type: TYPES,
			built_in: POSSIBLE_WITHOUT_PARENS
		},
		contains: [
			{
				scope: "type",
				match: kws_to_regex(MULTI_WORD_TYPES)
			},
			MULTI_WORD_KEYWORDS,
			FUNCTION_CALL,
			VARIABLE,
			STRING,
			QUOTED_IDENTIFIER,
			hljs.C_NUMBER_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			COMMENT_MODE,
			OPERATOR
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/swift.js
/**
* @param {string} value
* @returns {RegExp}
* */
/**
* @param {RegExp | string } re
* @returns {string}
*/
function source(re) {
	if (!re) return null;
	if (typeof re === "string") return re;
	return re.source;
}
/**
* @param {RegExp | string } re
* @returns {string}
*/
function lookahead(re) {
	return concat("(?=", re, ")");
}
/**
* @param {...(RegExp | string) } args
* @returns {string}
*/
function concat(...args) {
	return args.map((x) => source(x)).join("");
}
/**
* @param { Array<string | RegExp | Object> } args
* @returns {object}
*/
function stripOptionsFromArgs(args) {
	const opts = args[args.length - 1];
	if (typeof opts === "object" && opts.constructor === Object) {
		args.splice(args.length - 1, 1);
		return opts;
	} else return {};
}
/** @typedef { {capture?: boolean} } RegexEitherOptions */
/**
* Any of the passed expresssions may match
*
* Creates a huge this | this | that | that match
* @param {(RegExp | string)[] | [...(RegExp | string)[], RegexEitherOptions]} args
* @returns {string}
*/
function either(...args) {
	return "(" + (stripOptionsFromArgs(args).capture ? "" : "?:") + args.map((x) => source(x)).join("|") + ")";
}
var keywordWrapper = (keyword) => concat(/\b/, keyword, /\w$/.test(keyword) ? /\b/ : /\B/);
var dotKeywords = ["Protocol", "Type"].map(keywordWrapper);
var optionalDotKeywords = ["init", "self"].map(keywordWrapper);
var keywordTypes = ["Any", "Self"];
var keywords = [
	"actor",
	"any",
	"associatedtype",
	"async",
	"await",
	/as\?/,
	/as!/,
	"as",
	"borrowing",
	"break",
	"case",
	"catch",
	"class",
	"consume",
	"consuming",
	"continue",
	"convenience",
	"copy",
	"default",
	"defer",
	"deinit",
	"didSet",
	"distributed",
	"do",
	"dynamic",
	"each",
	"else",
	"enum",
	"extension",
	"fallthrough",
	/fileprivate\(set\)/,
	"fileprivate",
	"final",
	"for",
	"func",
	"get",
	"guard",
	"if",
	"import",
	"indirect",
	"infix",
	/init\?/,
	/init!/,
	"inout",
	/internal\(set\)/,
	"internal",
	"in",
	"is",
	"isolated",
	"nonisolated",
	"lazy",
	"let",
	"macro",
	"mutating",
	"nonmutating",
	/open\(set\)/,
	"open",
	"operator",
	"optional",
	"override",
	"package",
	"postfix",
	"precedencegroup",
	"prefix",
	/private\(set\)/,
	"private",
	"protocol",
	/public\(set\)/,
	"public",
	"repeat",
	"required",
	"rethrows",
	"return",
	"set",
	"some",
	"static",
	"struct",
	"subscript",
	"super",
	"switch",
	"throws",
	"throw",
	/try\?/,
	/try!/,
	"try",
	"typealias",
	/unowned\(safe\)/,
	/unowned\(unsafe\)/,
	"unowned",
	"var",
	"weak",
	"where",
	"while",
	"willSet"
];
var literals = [
	"false",
	"nil",
	"true"
];
var precedencegroupKeywords = [
	"assignment",
	"associativity",
	"higherThan",
	"left",
	"lowerThan",
	"none",
	"right"
];
var numberSignKeywords = [
	"#colorLiteral",
	"#column",
	"#dsohandle",
	"#else",
	"#elseif",
	"#endif",
	"#error",
	"#file",
	"#fileID",
	"#fileLiteral",
	"#filePath",
	"#function",
	"#if",
	"#imageLiteral",
	"#keyPath",
	"#line",
	"#selector",
	"#sourceLocation",
	"#warning"
];
var builtIns = [
	"abs",
	"all",
	"any",
	"assert",
	"assertionFailure",
	"debugPrint",
	"dump",
	"fatalError",
	"getVaList",
	"isKnownUniquelyReferenced",
	"max",
	"min",
	"numericCast",
	"pointwiseMax",
	"pointwiseMin",
	"precondition",
	"preconditionFailure",
	"print",
	"readLine",
	"repeatElement",
	"sequence",
	"stride",
	"swap",
	"swift_unboxFromSwiftValueWithType",
	"transcode",
	"type",
	"unsafeBitCast",
	"unsafeDowncast",
	"withExtendedLifetime",
	"withUnsafeMutablePointer",
	"withUnsafePointer",
	"withVaList",
	"withoutActuallyEscaping",
	"zip"
];
var operatorHead = either(/[/=\-+!*%<>&|^~?]/, /[\u00A1-\u00A7]/, /[\u00A9\u00AB]/, /[\u00AC\u00AE]/, /[\u00B0\u00B1]/, /[\u00B6\u00BB\u00BF\u00D7\u00F7]/, /[\u2016-\u2017]/, /[\u2020-\u2027]/, /[\u2030-\u203E]/, /[\u2041-\u2053]/, /[\u2055-\u205E]/, /[\u2190-\u23FF]/, /[\u2500-\u2775]/, /[\u2794-\u2BFF]/, /[\u2E00-\u2E7F]/, /[\u3001-\u3003]/, /[\u3008-\u3020]/, /[\u3030]/);
var operatorCharacter = either(operatorHead, /[\u0300-\u036F]/, /[\u1DC0-\u1DFF]/, /[\u20D0-\u20FF]/, /[\uFE00-\uFE0F]/, /[\uFE20-\uFE2F]/);
var operator = concat(operatorHead, operatorCharacter, "*");
var identifierHead = either(/[a-zA-Z_]/, /[\u00A8\u00AA\u00AD\u00AF\u00B2-\u00B5\u00B7-\u00BA]/, /[\u00BC-\u00BE\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF]/, /[\u0100-\u02FF\u0370-\u167F\u1681-\u180D\u180F-\u1DBF]/, /[\u1E00-\u1FFF]/, /[\u200B-\u200D\u202A-\u202E\u203F-\u2040\u2054\u2060-\u206F]/, /[\u2070-\u20CF\u2100-\u218F\u2460-\u24FF\u2776-\u2793]/, /[\u2C00-\u2DFF\u2E80-\u2FFF]/, /[\u3004-\u3007\u3021-\u302F\u3031-\u303F\u3040-\uD7FF]/, /[\uF900-\uFD3D\uFD40-\uFDCF\uFDF0-\uFE1F\uFE30-\uFE44]/, /[\uFE47-\uFEFE\uFF00-\uFFFD]/);
var identifierCharacter = either(identifierHead, /\d/, /[\u0300-\u036F\u1DC0-\u1DFF\u20D0-\u20FF\uFE20-\uFE2F]/);
var identifier = concat(identifierHead, identifierCharacter, "*");
var typeIdentifier = concat(/[A-Z]/, identifierCharacter, "*");
var keywordAttributes = [
	"attached",
	"autoclosure",
	concat(/convention\(/, either("swift", "block", "c"), /\)/),
	"discardableResult",
	"dynamicCallable",
	"dynamicMemberLookup",
	"escaping",
	"freestanding",
	"frozen",
	"GKInspectable",
	"IBAction",
	"IBDesignable",
	"IBInspectable",
	"IBOutlet",
	"IBSegueAction",
	"inlinable",
	"main",
	"nonobjc",
	"NSApplicationMain",
	"NSCopying",
	"NSManaged",
	concat(/objc\(/, identifier, /\)/),
	"objc",
	"objcMembers",
	"propertyWrapper",
	"requires_stored_property_inits",
	"resultBuilder",
	"Sendable",
	"testable",
	"UIApplicationMain",
	"unchecked",
	"unknown",
	"usableFromInline",
	"warn_unqualified_access"
];
var availabilityKeywords = [
	"iOS",
	"iOSApplicationExtension",
	"macOS",
	"macOSApplicationExtension",
	"macCatalyst",
	"macCatalystApplicationExtension",
	"watchOS",
	"watchOSApplicationExtension",
	"tvOS",
	"tvOSApplicationExtension",
	"swift"
];
/** @type LanguageFn */
function swift(hljs) {
	const WHITESPACE = {
		match: /\s+/,
		relevance: 0
	};
	const BLOCK_COMMENT = hljs.COMMENT("/\\*", "\\*/", { contains: ["self"] });
	const COMMENTS = [hljs.C_LINE_COMMENT_MODE, BLOCK_COMMENT];
	const DOT_KEYWORD = {
		match: [/\./, either(...dotKeywords, ...optionalDotKeywords)],
		className: { 2: "keyword" }
	};
	const KEYWORD_GUARD = {
		match: concat(/\./, either(...keywords)),
		relevance: 0
	};
	const PLAIN_KEYWORDS = keywords.filter((kw) => typeof kw === "string").concat(["_|0"]);
	const KEYWORD = { variants: [{
		className: "keyword",
		match: either(...keywords.filter((kw) => typeof kw !== "string").concat(keywordTypes).map(keywordWrapper), ...optionalDotKeywords)
	}] };
	const KEYWORDS = {
		$pattern: either(/\b\w+/, /#\w+/),
		keyword: PLAIN_KEYWORDS.concat(numberSignKeywords),
		literal: literals
	};
	const KEYWORD_MODES = [
		DOT_KEYWORD,
		KEYWORD_GUARD,
		KEYWORD
	];
	const BUILT_INS = [{
		match: concat(/\./, either(...builtIns)),
		relevance: 0
	}, {
		className: "built_in",
		match: concat(/\b/, either(...builtIns), /(?=\()/)
	}];
	const OPERATOR_GUARD = {
		match: /->/,
		relevance: 0
	};
	const OPERATORS = [OPERATOR_GUARD, {
		className: "operator",
		relevance: 0,
		variants: [{ match: operator }, { match: `\\.(\\.|${operatorCharacter})+` }]
	}];
	const decimalDigits = "([0-9]_*)+";
	const hexDigits = "([0-9a-fA-F]_*)+";
	const NUMBER = {
		className: "number",
		relevance: 0,
		variants: [
			{ match: `\\b(${decimalDigits})(\\.(${decimalDigits}))?([eE][+-]?(${decimalDigits}))?\\b` },
			{ match: `\\b0x(${hexDigits})(\\.(${hexDigits}))?([pP][+-]?(${decimalDigits}))?\\b` },
			{ match: /\b0o([0-7]_*)+\b/ },
			{ match: /\b0b([01]_*)+\b/ }
		]
	};
	const ESCAPED_CHARACTER = (rawDelimiter = "") => ({
		className: "subst",
		variants: [{ match: concat(/\\/, rawDelimiter, /[0\\tnr"']/) }, { match: concat(/\\/, rawDelimiter, /u\{[0-9a-fA-F]{1,8}\}/) }]
	});
	const ESCAPED_NEWLINE = (rawDelimiter = "") => ({
		className: "subst",
		match: concat(/\\/, rawDelimiter, /[\t ]*(?:[\r\n]|\r\n)/)
	});
	const INTERPOLATION = (rawDelimiter = "") => ({
		className: "subst",
		label: "interpol",
		begin: concat(/\\/, rawDelimiter, /\(/),
		end: /\)/
	});
	const MULTILINE_STRING = (rawDelimiter = "") => ({
		begin: concat(rawDelimiter, /"""/),
		end: concat(/"""/, rawDelimiter),
		contains: [
			ESCAPED_CHARACTER(rawDelimiter),
			ESCAPED_NEWLINE(rawDelimiter),
			INTERPOLATION(rawDelimiter)
		]
	});
	const SINGLE_LINE_STRING = (rawDelimiter = "") => ({
		begin: concat(rawDelimiter, /"/),
		end: concat(/"/, rawDelimiter),
		contains: [ESCAPED_CHARACTER(rawDelimiter), INTERPOLATION(rawDelimiter)]
	});
	const STRING = {
		className: "string",
		variants: [
			MULTILINE_STRING(),
			MULTILINE_STRING("#"),
			MULTILINE_STRING("##"),
			MULTILINE_STRING("###"),
			SINGLE_LINE_STRING(),
			SINGLE_LINE_STRING("#"),
			SINGLE_LINE_STRING("##"),
			SINGLE_LINE_STRING("###")
		]
	};
	const REGEXP_CONTENTS = [hljs.BACKSLASH_ESCAPE, {
		begin: /\[/,
		end: /\]/,
		relevance: 0,
		contains: [hljs.BACKSLASH_ESCAPE]
	}];
	const BARE_REGEXP_LITERAL = {
		begin: /\/[^\s](?=[^/\n]*\/)/,
		end: /\//,
		contains: REGEXP_CONTENTS
	};
	const EXTENDED_REGEXP_LITERAL = (rawDelimiter) => {
		const begin = concat(rawDelimiter, /\//);
		const end = concat(/\//, rawDelimiter);
		return {
			begin,
			end,
			contains: [...REGEXP_CONTENTS, {
				scope: "comment",
				begin: `#(?!.*${end})`,
				end: /$/
			}]
		};
	};
	const REGEXP = {
		scope: "regexp",
		variants: [
			EXTENDED_REGEXP_LITERAL("###"),
			EXTENDED_REGEXP_LITERAL("##"),
			EXTENDED_REGEXP_LITERAL("#"),
			BARE_REGEXP_LITERAL
		]
	};
	const QUOTED_IDENTIFIER = { match: concat(/`/, identifier, /`/) };
	const IDENTIFIERS = [
		QUOTED_IDENTIFIER,
		{
			className: "variable",
			match: /\$\d+/
		},
		{
			className: "variable",
			match: `\\$${identifierCharacter}+`
		}
	];
	const ATTRIBUTES = [
		{
			match: /(@|#(un)?)available/,
			scope: "keyword",
			starts: { contains: [{
				begin: /\(/,
				end: /\)/,
				keywords: availabilityKeywords,
				contains: [
					...OPERATORS,
					NUMBER,
					STRING
				]
			}] }
		},
		{
			scope: "keyword",
			match: concat(/@/, either(...keywordAttributes), lookahead(either(/\(/, /\s+/)))
		},
		{
			scope: "meta",
			match: concat(/@/, identifier)
		}
	];
	const TYPE = {
		match: lookahead(/\b[A-Z]/),
		relevance: 0,
		contains: [
			{
				className: "type",
				match: concat(/(AV|CA|CF|CG|CI|CL|CM|CN|CT|MK|MP|MTK|MTL|NS|SCN|SK|UI|WK|XC)/, identifierCharacter, "+")
			},
			{
				className: "type",
				match: typeIdentifier,
				relevance: 0
			},
			{
				match: /[?!]+/,
				relevance: 0
			},
			{
				match: /\.\.\./,
				relevance: 0
			},
			{
				match: concat(/\s+&\s+/, lookahead(typeIdentifier)),
				relevance: 0
			}
		]
	};
	const GENERIC_ARGUMENTS = {
		begin: /</,
		end: />/,
		keywords: KEYWORDS,
		contains: [
			...COMMENTS,
			...KEYWORD_MODES,
			...ATTRIBUTES,
			OPERATOR_GUARD,
			TYPE
		]
	};
	TYPE.contains.push(GENERIC_ARGUMENTS);
	const TUPLE = {
		begin: /\(/,
		end: /\)/,
		relevance: 0,
		keywords: KEYWORDS,
		contains: [
			"self",
			{
				match: concat(identifier, /\s*:/),
				keywords: "_|0",
				relevance: 0
			},
			...COMMENTS,
			REGEXP,
			...KEYWORD_MODES,
			...BUILT_INS,
			...OPERATORS,
			NUMBER,
			STRING,
			...IDENTIFIERS,
			...ATTRIBUTES,
			TYPE
		]
	};
	const GENERIC_PARAMETERS = {
		begin: /</,
		end: />/,
		keywords: "repeat each",
		contains: [...COMMENTS, TYPE]
	};
	const FUNCTION_PARAMETERS = {
		begin: /\(/,
		end: /\)/,
		keywords: KEYWORDS,
		contains: [
			{
				begin: either(lookahead(concat(identifier, /\s*:/)), lookahead(concat(identifier, /\s+/, identifier, /\s*:/))),
				end: /:/,
				relevance: 0,
				contains: [{
					className: "keyword",
					match: /\b_\b/
				}, {
					className: "params",
					match: identifier
				}]
			},
			...COMMENTS,
			...KEYWORD_MODES,
			...OPERATORS,
			NUMBER,
			STRING,
			...ATTRIBUTES,
			TYPE,
			TUPLE
		],
		endsParent: true,
		illegal: /["']/
	};
	const FUNCTION_OR_MACRO = {
		match: [
			/(func|macro)/,
			/\s+/,
			either(QUOTED_IDENTIFIER.match, identifier, operator)
		],
		className: {
			1: "keyword",
			3: "title.function"
		},
		contains: [
			GENERIC_PARAMETERS,
			FUNCTION_PARAMETERS,
			WHITESPACE
		],
		illegal: [/\[/, /%/]
	};
	const INIT_SUBSCRIPT = {
		match: [/\b(?:subscript|init[?!]?)/, /\s*(?=[<(])/],
		className: { 1: "keyword" },
		contains: [
			GENERIC_PARAMETERS,
			FUNCTION_PARAMETERS,
			WHITESPACE
		],
		illegal: /\[|%/
	};
	const OPERATOR_DECLARATION = {
		match: [
			/operator/,
			/\s+/,
			operator
		],
		className: {
			1: "keyword",
			3: "title"
		}
	};
	const PRECEDENCEGROUP = {
		begin: [
			/precedencegroup/,
			/\s+/,
			typeIdentifier
		],
		className: {
			1: "keyword",
			3: "title"
		},
		contains: [TYPE],
		keywords: [...precedencegroupKeywords, ...literals],
		end: /}/
	};
	const CLASS_FUNC_DECLARATION = {
		match: [
			/class\b/,
			/\s+/,
			/func\b/,
			/\s+/,
			/\b[A-Za-z_][A-Za-z0-9_]*\b/
		],
		scope: {
			1: "keyword",
			3: "keyword",
			5: "title.function"
		}
	};
	const CLASS_VAR_DECLARATION = {
		match: [
			/class\b/,
			/\s+/,
			/var\b/
		],
		scope: {
			1: "keyword",
			3: "keyword"
		}
	};
	const TYPE_DECLARATION = {
		begin: [
			/(struct|protocol|class|extension|enum|actor)/,
			/\s+/,
			identifier,
			/\s*/
		],
		beginScope: {
			1: "keyword",
			3: "title.class"
		},
		keywords: KEYWORDS,
		contains: [
			GENERIC_PARAMETERS,
			...KEYWORD_MODES,
			{
				begin: /:/,
				end: /\{/,
				keywords: KEYWORDS,
				contains: [{
					scope: "title.class.inherited",
					match: typeIdentifier
				}, ...KEYWORD_MODES],
				relevance: 0
			}
		]
	};
	for (const variant of STRING.variants) {
		const interpolation = variant.contains.find((mode) => mode.label === "interpol");
		interpolation.keywords = KEYWORDS;
		const submodes = [
			...KEYWORD_MODES,
			...BUILT_INS,
			...OPERATORS,
			NUMBER,
			STRING,
			...IDENTIFIERS
		];
		interpolation.contains = [...submodes, {
			begin: /\(/,
			end: /\)/,
			contains: ["self", ...submodes]
		}];
	}
	return {
		name: "Swift",
		keywords: KEYWORDS,
		contains: [
			...COMMENTS,
			FUNCTION_OR_MACRO,
			INIT_SUBSCRIPT,
			CLASS_FUNC_DECLARATION,
			CLASS_VAR_DECLARATION,
			TYPE_DECLARATION,
			OPERATOR_DECLARATION,
			PRECEDENCEGROUP,
			{
				beginKeywords: "import",
				end: /$/,
				contains: [...COMMENTS],
				relevance: 0
			},
			REGEXP,
			...KEYWORD_MODES,
			...BUILT_INS,
			...OPERATORS,
			NUMBER,
			STRING,
			...IDENTIFIERS,
			...ATTRIBUTES,
			TYPE,
			TUPLE
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/typescript.js
var IDENT_RE = "[A-Za-z$_][0-9A-Za-z$_]*";
var KEYWORDS = [
	"as",
	"in",
	"of",
	"if",
	"for",
	"while",
	"finally",
	"var",
	"new",
	"function",
	"do",
	"return",
	"void",
	"else",
	"break",
	"catch",
	"instanceof",
	"with",
	"throw",
	"case",
	"default",
	"try",
	"switch",
	"continue",
	"typeof",
	"delete",
	"let",
	"yield",
	"const",
	"class",
	"debugger",
	"async",
	"await",
	"static",
	"import",
	"from",
	"export",
	"extends",
	"using"
];
var LITERALS = [
	"true",
	"false",
	"null",
	"undefined",
	"NaN",
	"Infinity"
];
var TYPES = [
	"Object",
	"Function",
	"Boolean",
	"Symbol",
	"Math",
	"Date",
	"Number",
	"BigInt",
	"String",
	"RegExp",
	"Array",
	"Float32Array",
	"Float64Array",
	"Int8Array",
	"Uint8Array",
	"Uint8ClampedArray",
	"Int16Array",
	"Int32Array",
	"Uint16Array",
	"Uint32Array",
	"BigInt64Array",
	"BigUint64Array",
	"Set",
	"Map",
	"WeakSet",
	"WeakMap",
	"ArrayBuffer",
	"SharedArrayBuffer",
	"Atomics",
	"DataView",
	"JSON",
	"Promise",
	"Generator",
	"GeneratorFunction",
	"AsyncFunction",
	"Reflect",
	"Proxy",
	"Intl",
	"WebAssembly"
];
var ERROR_TYPES = [
	"Error",
	"EvalError",
	"InternalError",
	"RangeError",
	"ReferenceError",
	"SyntaxError",
	"TypeError",
	"URIError"
];
var BUILT_IN_GLOBALS = [
	"setInterval",
	"setTimeout",
	"clearInterval",
	"clearTimeout",
	"require",
	"exports",
	"eval",
	"isFinite",
	"isNaN",
	"parseFloat",
	"parseInt",
	"decodeURI",
	"decodeURIComponent",
	"encodeURI",
	"encodeURIComponent",
	"escape",
	"unescape"
];
var BUILT_IN_VARIABLES = [
	"arguments",
	"this",
	"super",
	"console",
	"window",
	"document",
	"localStorage",
	"sessionStorage",
	"module",
	"global"
];
var BUILT_INS = [].concat(BUILT_IN_GLOBALS, TYPES, ERROR_TYPES);
/** @type LanguageFn */
function javascript(hljs) {
	const regex = hljs.regex;
	/**
	* Takes a string like "<Booger" and checks to see
	* if we can find a matching "</Booger" later in the
	* content.
	* @param {RegExpMatchArray} match
	* @param {{after:number}} param1
	*/
	const hasClosingTag = (match, { after }) => {
		const tag = "</" + match[0].slice(1);
		return match.input.indexOf(tag, after) !== -1;
	};
	const IDENT_RE$1 = IDENT_RE;
	const FRAGMENT = {
		begin: "<>",
		end: "</>"
	};
	const XML_SELF_CLOSING = /<[A-Za-z0-9\\._:-]+\s*\/>/;
	const XML_TAG = {
		begin: /<[A-Za-z0-9\\._:-]+/,
		end: /\/[A-Za-z0-9\\._:-]+>|\/>/,
		/**
		* @param {RegExpMatchArray} match
		* @param {CallbackResponse} response
		*/
		isTrulyOpeningTag: (match, response) => {
			const afterMatchIndex = match[0].length + match.index;
			const nextChar = match.input[afterMatchIndex];
			if (nextChar === "<" || nextChar === ",") {
				response.ignoreMatch();
				return;
			}
			if (nextChar === ">") {
				if (!hasClosingTag(match, { after: afterMatchIndex })) response.ignoreMatch();
			}
			let m;
			const afterMatch = match.input.substring(afterMatchIndex);
			if (m = afterMatch.match(/^\s*=/)) {
				response.ignoreMatch();
				return;
			}
			if (m = afterMatch.match(/^\s+extends\s+/)) {
				if (m.index === 0) {
					response.ignoreMatch();
					return;
				}
			}
		}
	};
	const KEYWORDS$1 = {
		$pattern: IDENT_RE,
		keyword: KEYWORDS,
		literal: LITERALS,
		built_in: BUILT_INS,
		"variable.language": BUILT_IN_VARIABLES
	};
	const decimalDigits = "[0-9](_?[0-9])*";
	const frac = `\\.(${decimalDigits})`;
	const decimalInteger = `0|[1-9](_?[0-9])*|0[0-7]*[89][0-9]*`;
	const NUMBER = {
		className: "number",
		variants: [
			{ begin: `(\\b(${decimalInteger})((${frac})|\\.)?|(${frac}))[eE][+-]?(${decimalDigits})\\b` },
			{ begin: `\\b(${decimalInteger})\\b((${frac})\\b|\\.)?|(${frac})\\b` },
			{ begin: `\\b(0|[1-9](_?[0-9])*)n\\b` },
			{ begin: "\\b0[xX][0-9a-fA-F](_?[0-9a-fA-F])*n?\\b" },
			{ begin: "\\b0[bB][0-1](_?[0-1])*n?\\b" },
			{ begin: "\\b0[oO][0-7](_?[0-7])*n?\\b" },
			{ begin: "\\b0[0-7]+n?\\b" }
		],
		relevance: 0
	};
	const SUBST = {
		className: "subst",
		begin: "\\$\\{",
		end: "\\}",
		keywords: KEYWORDS$1,
		contains: []
	};
	const HTML_TEMPLATE = {
		begin: ".?html`",
		end: "",
		starts: {
			end: "`",
			returnEnd: false,
			contains: [hljs.BACKSLASH_ESCAPE, SUBST],
			subLanguage: "xml"
		}
	};
	const CSS_TEMPLATE = {
		begin: ".?css`",
		end: "",
		starts: {
			end: "`",
			returnEnd: false,
			contains: [hljs.BACKSLASH_ESCAPE, SUBST],
			subLanguage: "css"
		}
	};
	const GRAPHQL_TEMPLATE = {
		begin: ".?gql`",
		end: "",
		starts: {
			end: "`",
			returnEnd: false,
			contains: [hljs.BACKSLASH_ESCAPE, SUBST],
			subLanguage: "graphql"
		}
	};
	const TEMPLATE_STRING = {
		className: "string",
		begin: "`",
		end: "`",
		contains: [hljs.BACKSLASH_ESCAPE, SUBST]
	};
	const COMMENT = {
		className: "comment",
		variants: [
			hljs.COMMENT(/\/\*\*(?!\/)/, "\\*/", {
				relevance: 0,
				contains: [{
					begin: "(?=@[A-Za-z]+)",
					relevance: 0,
					contains: [
						{
							className: "doctag",
							begin: "@[A-Za-z]+"
						},
						{
							className: "type",
							begin: "\\{",
							end: "\\}",
							excludeEnd: true,
							excludeBegin: true,
							relevance: 0
						},
						{
							className: "variable",
							begin: "[A-Za-z$_][0-9A-Za-z$_]*(?=\\s*(-)|$)",
							endsParent: true,
							relevance: 0
						},
						{
							begin: /(?=[^\n])\s/,
							relevance: 0
						}
					]
				}]
			}),
			hljs.C_BLOCK_COMMENT_MODE,
			hljs.C_LINE_COMMENT_MODE
		]
	};
	const SUBST_INTERNALS = [
		hljs.APOS_STRING_MODE,
		hljs.QUOTE_STRING_MODE,
		HTML_TEMPLATE,
		CSS_TEMPLATE,
		GRAPHQL_TEMPLATE,
		TEMPLATE_STRING,
		{ match: /\$\d+/ },
		NUMBER
	];
	SUBST.contains = SUBST_INTERNALS.concat({
		begin: /\{/,
		end: /\}/,
		keywords: KEYWORDS$1,
		contains: ["self"].concat(SUBST_INTERNALS)
	});
	const SUBST_AND_COMMENTS = [].concat(COMMENT, SUBST.contains);
	const PARAMS_CONTAINS = SUBST_AND_COMMENTS.concat([{
		begin: /(\s*)\(/,
		end: /\)/,
		keywords: KEYWORDS$1,
		contains: ["self"].concat(SUBST_AND_COMMENTS)
	}]);
	const PARAMS = {
		className: "params",
		begin: /(\s*)\(/,
		end: /\)/,
		excludeBegin: true,
		excludeEnd: true,
		keywords: KEYWORDS$1,
		contains: PARAMS_CONTAINS
	};
	const CLASS_OR_EXTENDS = { variants: [{
		match: [
			/class/,
			/\s+/,
			IDENT_RE$1,
			/\s+/,
			/extends/,
			/\s+/,
			regex.concat(IDENT_RE$1, "(", regex.concat(/\./, IDENT_RE$1), ")*")
		],
		scope: {
			1: "keyword",
			3: "title.class",
			5: "keyword",
			7: "title.class.inherited"
		}
	}, {
		match: [
			/class/,
			/\s+/,
			IDENT_RE$1
		],
		scope: {
			1: "keyword",
			3: "title.class"
		}
	}] };
	const CLASS_REFERENCE = {
		relevance: 0,
		match: regex.either(/\bJSON/, /\b[A-Z][a-z]+([A-Z][a-z]*|\d)*/, /\b[A-Z]{2,}([A-Z][a-z]+|\d)+([A-Z][a-z]*)*/, /\b[A-Z]{2,}[a-z]+([A-Z][a-z]+|\d)*([A-Z][a-z]*)*/),
		className: "title.class",
		keywords: { _: [...TYPES, ...ERROR_TYPES] }
	};
	const USE_STRICT = {
		label: "use_strict",
		className: "meta",
		relevance: 10,
		begin: /^\s*['"]use (strict|asm)['"]/
	};
	const FUNCTION_DEFINITION = {
		variants: [{ match: [
			/function/,
			/\s+/,
			IDENT_RE$1,
			/(?=\s*\()/
		] }, { match: [/function/, /\s*(?=\()/] }],
		className: {
			1: "keyword",
			3: "title.function"
		},
		label: "func.def",
		contains: [PARAMS],
		illegal: /%/
	};
	const UPPER_CASE_CONSTANT = {
		relevance: 0,
		match: /\b[A-Z][A-Z_0-9]+\b/,
		className: "variable.constant"
	};
	function noneOf(list) {
		return regex.concat("(?!", list.join("|"), ")");
	}
	const FUNCTION_CALL = {
		match: regex.concat(/\b/, noneOf([
			...BUILT_IN_GLOBALS,
			"super",
			"import"
		].map((x) => `${x}\\s*\\(`)), IDENT_RE$1, regex.lookahead(/\s*\(/)),
		className: "title.function",
		relevance: 0
	};
	const PROPERTY_ACCESS = {
		begin: regex.concat(/\./, regex.lookahead(regex.concat(IDENT_RE$1, /(?![0-9A-Za-z$_(])/))),
		end: IDENT_RE$1,
		excludeBegin: true,
		keywords: "prototype",
		className: "property",
		relevance: 0
	};
	const GETTER_OR_SETTER = {
		match: [
			/get|set/,
			/\s+/,
			IDENT_RE$1,
			/(?=\()/
		],
		className: {
			1: "keyword",
			3: "title.function"
		},
		contains: [{ begin: /\(\)/ }, PARAMS]
	};
	const FUNC_LEAD_IN_RE = "(\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)|" + hljs.UNDERSCORE_IDENT_RE + ")\\s*=>";
	const FUNCTION_VARIABLE = {
		match: [
			/const|var|let/,
			/\s+/,
			IDENT_RE$1,
			/\s*/,
			/=\s*/,
			/(async\s*)?/,
			regex.lookahead(FUNC_LEAD_IN_RE)
		],
		keywords: "async",
		className: {
			1: "keyword",
			3: "title.function"
		},
		contains: [PARAMS]
	};
	return {
		name: "JavaScript",
		aliases: [
			"js",
			"jsx",
			"mjs",
			"cjs"
		],
		keywords: KEYWORDS$1,
		exports: {
			PARAMS_CONTAINS,
			CLASS_REFERENCE
		},
		illegal: /#(?![$_A-z])/,
		contains: [
			hljs.SHEBANG({
				label: "shebang",
				binary: "node",
				relevance: 5
			}),
			USE_STRICT,
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE,
			HTML_TEMPLATE,
			CSS_TEMPLATE,
			GRAPHQL_TEMPLATE,
			TEMPLATE_STRING,
			COMMENT,
			{ match: /\$\d+/ },
			NUMBER,
			CLASS_REFERENCE,
			{
				scope: "attr",
				match: IDENT_RE$1 + regex.lookahead(":"),
				relevance: 0
			},
			FUNCTION_VARIABLE,
			{
				begin: "(" + hljs.RE_STARTERS_RE + "|\\b(case|return|throw)\\b)\\s*",
				keywords: "return throw case",
				relevance: 0,
				contains: [
					COMMENT,
					hljs.REGEXP_MODE,
					{
						className: "function",
						begin: FUNC_LEAD_IN_RE,
						returnBegin: true,
						end: "\\s*=>",
						contains: [{
							className: "params",
							variants: [
								{
									begin: hljs.UNDERSCORE_IDENT_RE,
									relevance: 0
								},
								{
									className: null,
									begin: /\(\s*\)/,
									skip: true
								},
								{
									begin: /(\s*)\(/,
									end: /\)/,
									excludeBegin: true,
									excludeEnd: true,
									keywords: KEYWORDS$1,
									contains: PARAMS_CONTAINS
								}
							]
						}]
					},
					{
						begin: /,/,
						relevance: 0
					},
					{
						match: /\s+/,
						relevance: 0
					},
					{
						variants: [
							{
								begin: FRAGMENT.begin,
								end: FRAGMENT.end
							},
							{ match: XML_SELF_CLOSING },
							{
								begin: XML_TAG.begin,
								"on:begin": XML_TAG.isTrulyOpeningTag,
								end: XML_TAG.end
							}
						],
						subLanguage: "xml",
						contains: [{
							begin: XML_TAG.begin,
							end: XML_TAG.end,
							skip: true,
							contains: ["self"]
						}]
					}
				]
			},
			FUNCTION_DEFINITION,
			{ beginKeywords: "while if switch catch for" },
			{
				begin: "\\b(?!function)" + hljs.UNDERSCORE_IDENT_RE + "\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)\\s*\\{",
				returnBegin: true,
				label: "func.def",
				contains: [PARAMS, hljs.inherit(hljs.TITLE_MODE, {
					begin: IDENT_RE$1,
					className: "title.function"
				})]
			},
			{
				match: /\.\.\./,
				relevance: 0
			},
			PROPERTY_ACCESS,
			{
				match: "\\$[A-Za-z$_][0-9A-Za-z$_]*",
				relevance: 0
			},
			{
				match: [/\bconstructor(?=\s*\()/],
				className: { 1: "title.function" },
				contains: [PARAMS]
			},
			FUNCTION_CALL,
			UPPER_CASE_CONSTANT,
			CLASS_OR_EXTENDS,
			GETTER_OR_SETTER,
			{ match: /\$[(.]/ }
		]
	};
}
/** @type LanguageFn */
function typescript(hljs) {
	const regex = hljs.regex;
	const tsLanguage = javascript(hljs);
	const IDENT_RE$1 = IDENT_RE;
	const TYPES = [
		"any",
		"void",
		"number",
		"boolean",
		"string",
		"object",
		"never",
		"symbol",
		"bigint",
		"unknown"
	];
	const NAMESPACE = {
		begin: [
			/namespace/,
			/\s+/,
			hljs.IDENT_RE
		],
		beginScope: {
			1: "keyword",
			3: "title.class"
		}
	};
	const INTERFACE = {
		beginKeywords: "interface",
		end: /\{/,
		excludeEnd: true,
		keywords: {
			keyword: "interface extends",
			built_in: TYPES
		},
		contains: [tsLanguage.exports.CLASS_REFERENCE]
	};
	const USE_STRICT = {
		className: "meta",
		relevance: 10,
		begin: /^\s*['"]use strict['"]/
	};
	const KEYWORDS$1 = {
		$pattern: IDENT_RE,
		keyword: KEYWORDS.concat([
			"type",
			"interface",
			"public",
			"private",
			"protected",
			"implements",
			"declare",
			"abstract",
			"readonly",
			"enum",
			"override",
			"satisfies"
		]),
		literal: LITERALS,
		built_in: BUILT_INS.concat(TYPES),
		"variable.language": BUILT_IN_VARIABLES
	};
	const DECORATOR = {
		className: "meta",
		begin: "@[A-Za-z$_][0-9A-Za-z$_]*"
	};
	const swapMode = (mode, label, replacement) => {
		const indx = mode.contains.findIndex((m) => m.label === label);
		if (indx === -1) throw new Error("can not find mode to replace");
		mode.contains.splice(indx, 1, replacement);
	};
	Object.assign(tsLanguage.keywords, KEYWORDS$1);
	tsLanguage.exports.PARAMS_CONTAINS.push(DECORATOR);
	const ATTRIBUTE_HIGHLIGHT = tsLanguage.contains.find((c) => c.scope === "attr");
	const OPTIONAL_KEY_OR_ARGUMENT = Object.assign({}, ATTRIBUTE_HIGHLIGHT, { match: regex.concat(IDENT_RE$1, regex.lookahead(/\s*\?:/)) });
	tsLanguage.exports.PARAMS_CONTAINS.push([
		tsLanguage.exports.CLASS_REFERENCE,
		ATTRIBUTE_HIGHLIGHT,
		OPTIONAL_KEY_OR_ARGUMENT
	]);
	tsLanguage.contains = tsLanguage.contains.concat([
		DECORATOR,
		NAMESPACE,
		INTERFACE,
		OPTIONAL_KEY_OR_ARGUMENT
	]);
	swapMode(tsLanguage, "shebang", hljs.SHEBANG());
	swapMode(tsLanguage, "use_strict", USE_STRICT);
	const functionDeclaration = tsLanguage.contains.find((m) => m.label === "func.def");
	functionDeclaration.relevance = 0;
	Object.assign(tsLanguage, {
		name: "TypeScript",
		aliases: [
			"ts",
			"tsx",
			"mts",
			"cts"
		]
	});
	return tsLanguage;
}
//#endregion
//#region node_modules/highlight.js/es/languages/vbnet.js
/** @type LanguageFn */
function vbnet(hljs) {
	const regex = hljs.regex;
	/**
	* Character Literal
	* Either a single character ("a"C) or an escaped double quote (""""C).
	*/
	const CHARACTER = {
		className: "string",
		begin: /"(""|[^/n])"C\b/
	};
	const STRING = {
		className: "string",
		begin: /"/,
		end: /"/,
		illegal: /\n/,
		contains: [{ begin: /""/ }]
	};
	/** Date Literals consist of a date, a time, or both separated by whitespace, surrounded by # */
	const MM_DD_YYYY = /\d{1,2}\/\d{1,2}\/\d{4}/;
	const YYYY_MM_DD = /\d{4}-\d{1,2}-\d{1,2}/;
	const TIME_12H = /(\d|1[012])(:\d+){0,2} *(AM|PM)/;
	const TIME_24H = /\d{1,2}(:\d{1,2}){1,2}/;
	const DATE = {
		className: "literal",
		variants: [
			{ begin: regex.concat(/# */, regex.either(YYYY_MM_DD, MM_DD_YYYY), / *#/) },
			{ begin: regex.concat(/# */, TIME_24H, / *#/) },
			{ begin: regex.concat(/# */, TIME_12H, / *#/) },
			{ begin: regex.concat(/# */, regex.either(YYYY_MM_DD, MM_DD_YYYY), / +/, regex.either(TIME_12H, TIME_24H), / *#/) }
		]
	};
	const NUMBER = {
		className: "number",
		relevance: 0,
		variants: [
			{ begin: /\b\d[\d_]*((\.[\d_]+(E[+-]?[\d_]+)?)|(E[+-]?[\d_]+))[RFD@!#]?/ },
			{ begin: /\b\d[\d_]*((U?[SIL])|[%&])?/ },
			{ begin: /&H[\dA-F_]+((U?[SIL])|[%&])?/ },
			{ begin: /&O[0-7_]+((U?[SIL])|[%&])?/ },
			{ begin: /&B[01_]+((U?[SIL])|[%&])?/ }
		]
	};
	const LABEL = {
		className: "label",
		begin: /^\w+:/
	};
	const DOC_COMMENT = hljs.COMMENT(/'''/, /$/, { contains: [{
		className: "doctag",
		begin: /<\/?/,
		end: />/
	}] });
	const COMMENT = hljs.COMMENT(null, /$/, { variants: [{ begin: /'/ }, { begin: /([\t ]|^)REM(?=\s)/ }] });
	return {
		name: "Visual Basic .NET",
		aliases: ["vb"],
		case_insensitive: true,
		classNameAliases: { label: "symbol" },
		keywords: {
			keyword: "addhandler alias aggregate ansi as async assembly auto binary by byref byval call case catch class compare const continue custom declare default delegate dim distinct do each equals else elseif end enum erase error event exit explicit finally for friend from function get global goto group handles if implements imports in inherits interface into iterator join key let lib loop me mid module mustinherit mustoverride mybase myclass namespace narrowing new next notinheritable notoverridable of off on operator option optional order overloads overridable overrides paramarray partial preserve private property protected public raiseevent readonly redim removehandler resume return select set shadows shared skip static step stop structure strict sub synclock take text then throw to try unicode until using when where while widening with withevents writeonly yield",
			built_in: "addressof and andalso await directcast gettype getxmlnamespace is isfalse isnot istrue like mod nameof new not or orelse trycast typeof xor cbool cbyte cchar cdate cdbl cdec cint clng cobj csbyte cshort csng cstr cuint culng cushort",
			type: "boolean byte char date decimal double integer long object sbyte short single string uinteger ulong ushort",
			literal: "true false nothing"
		},
		illegal: "//|\\{|\\}|endif|gosub|variant|wend|^\\$ ",
		contains: [
			CHARACTER,
			STRING,
			DATE,
			NUMBER,
			LABEL,
			DOC_COMMENT,
			COMMENT,
			{
				className: "meta",
				begin: /[\t ]*#(const|disable|else|elseif|enable|end|externalsource|if|region)\b/,
				end: /$/,
				keywords: { keyword: "const disable else elseif enable end externalsource if region then" },
				contains: [COMMENT]
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/wasm.js
/** @type LanguageFn */
function wasm(hljs) {
	hljs.regex;
	const BLOCK_COMMENT = hljs.COMMENT(/\(;/, /;\)/);
	BLOCK_COMMENT.contains.push("self");
	return {
		name: "WebAssembly",
		keywords: {
			$pattern: /[\w.]+/,
			keyword: [
				"anyfunc",
				"block",
				"br",
				"br_if",
				"br_table",
				"call",
				"call_indirect",
				"data",
				"drop",
				"elem",
				"else",
				"end",
				"export",
				"func",
				"global.get",
				"global.set",
				"local.get",
				"local.set",
				"local.tee",
				"get_global",
				"get_local",
				"global",
				"if",
				"import",
				"local",
				"loop",
				"memory",
				"memory.grow",
				"memory.size",
				"module",
				"mut",
				"nop",
				"offset",
				"param",
				"result",
				"return",
				"select",
				"set_global",
				"set_local",
				"start",
				"table",
				"tee_local",
				"then",
				"type",
				"unreachable"
			]
		},
		contains: [
			hljs.COMMENT(/;;/, /$/),
			BLOCK_COMMENT,
			{
				match: [
					/(?:offset|align)/,
					/\s*/,
					/=/
				],
				className: {
					1: "keyword",
					3: "operator"
				}
			},
			{
				className: "variable",
				begin: /\$[\w_]+/
			},
			{
				match: /(\((?!;)|\))+/,
				className: "punctuation",
				relevance: 0
			},
			{
				begin: [
					/(?:func|call|call_indirect)/,
					/\s+/,
					/\$[^\s)]+/
				],
				className: {
					1: "keyword",
					3: "title.function"
				}
			},
			hljs.QUOTE_STRING_MODE,
			{
				match: /(i32|i64|f32|f64)(?!\.)/,
				className: "type"
			},
			{
				className: "keyword",
				match: /\b(f32|f64|i32|i64)(?:\.(?:abs|add|and|ceil|clz|const|convert_[su]\/i(?:32|64)|copysign|ctz|demote\/f64|div(?:_[su])?|eqz?|extend_[su]\/i32|floor|ge(?:_[su])?|gt(?:_[su])?|le(?:_[su])?|load(?:(?:8|16|32)_[su])?|lt(?:_[su])?|max|min|mul|nearest|neg?|or|popcnt|promote\/f32|reinterpret\/[fi](?:32|64)|rem_[su]|rot[lr]|shl|shr_[su]|store(?:8|16|32)?|sqrt|sub|trunc(?:_[su]\/f(?:32|64))?|wrap\/i64|xor))\b/
			},
			{
				className: "number",
				relevance: 0,
				match: /[+-]?\b(?:\d(?:_?\d)*(?:\.\d(?:_?\d)*)?(?:[eE][+-]?\d(?:_?\d)*)?|0x[\da-fA-F](?:_?[\da-fA-F])*(?:\.[\da-fA-F](?:_?[\da-fA-D])*)?(?:[pP][+-]?\d(?:_?\d)*)?)\b|\binf\b|\bnan(?::0x[\da-fA-F](?:_?[\da-fA-D])*)?\b/
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/xml.js
/** @type LanguageFn */
function xml(hljs) {
	const regex = hljs.regex;
	const TAG_NAME_RE = regex.concat(/[\p{L}_]/u, regex.optional(/[\p{L}0-9_.-]*:/u), /[\p{L}0-9_.-]*/u);
	const XML_IDENT_RE = /[\p{L}0-9._:-]+/u;
	const XML_ENTITIES = {
		className: "symbol",
		begin: /&[a-z]+;|&#[0-9]+;|&#x[a-f0-9]+;/
	};
	const XML_META_KEYWORDS = {
		begin: /\s/,
		contains: [{
			className: "keyword",
			begin: /#?[a-z_][a-z1-9_-]+/,
			illegal: /\n/
		}]
	};
	const XML_META_PAR_KEYWORDS = hljs.inherit(XML_META_KEYWORDS, {
		begin: /\(/,
		end: /\)/
	});
	const APOS_META_STRING_MODE = hljs.inherit(hljs.APOS_STRING_MODE, { className: "string" });
	const QUOTE_META_STRING_MODE = hljs.inherit(hljs.QUOTE_STRING_MODE, { className: "string" });
	const TAG_INTERNALS = {
		endsWithParent: true,
		illegal: /</,
		relevance: 0,
		contains: [{
			className: "attr",
			begin: XML_IDENT_RE,
			relevance: 0
		}, {
			begin: /=\s*/,
			relevance: 0,
			contains: [{
				className: "string",
				endsParent: true,
				variants: [
					{
						begin: /"/,
						end: /"/,
						contains: [XML_ENTITIES]
					},
					{
						begin: /'/,
						end: /'/,
						contains: [XML_ENTITIES]
					},
					{ begin: /[^\s"'=<>`]+/ }
				]
			}]
		}]
	};
	return {
		name: "HTML, XML",
		aliases: [
			"html",
			"xhtml",
			"rss",
			"atom",
			"xjb",
			"xsd",
			"xsl",
			"plist",
			"wsf",
			"svg"
		],
		case_insensitive: true,
		unicodeRegex: true,
		contains: [
			{
				className: "meta",
				begin: /<![a-z]/,
				end: />/,
				relevance: 10,
				contains: [
					XML_META_KEYWORDS,
					QUOTE_META_STRING_MODE,
					APOS_META_STRING_MODE,
					XML_META_PAR_KEYWORDS,
					{
						begin: /\[/,
						end: /\]/,
						contains: [{
							className: "meta",
							begin: /<![a-z]/,
							end: />/,
							contains: [
								XML_META_KEYWORDS,
								XML_META_PAR_KEYWORDS,
								QUOTE_META_STRING_MODE,
								APOS_META_STRING_MODE
							]
						}]
					}
				]
			},
			hljs.COMMENT(/<!--/, /-->/, { relevance: 10 }),
			{
				begin: /<!\[CDATA\[/,
				end: /\]\]>/,
				relevance: 10
			},
			XML_ENTITIES,
			{
				className: "meta",
				end: /\?>/,
				variants: [{
					begin: /<\?xml/,
					relevance: 10,
					contains: [QUOTE_META_STRING_MODE]
				}, { begin: /<\?[a-z][a-z0-9]+/ }]
			},
			{
				className: "tag",
				begin: /<style(?=\s|>)/,
				end: />/,
				keywords: { name: "style" },
				contains: [TAG_INTERNALS],
				starts: {
					end: /<\/style>/,
					returnEnd: true,
					subLanguage: ["css", "xml"]
				}
			},
			{
				className: "tag",
				begin: /<script(?=\s|>)/,
				end: />/,
				keywords: { name: "script" },
				contains: [TAG_INTERNALS],
				starts: {
					end: /<\/script>/,
					returnEnd: true,
					subLanguage: [
						"javascript",
						"handlebars",
						"xml"
					]
				}
			},
			{
				className: "tag",
				begin: /<>|<\/>/
			},
			{
				className: "tag",
				begin: regex.concat(/</, regex.lookahead(regex.concat(TAG_NAME_RE, regex.either(/\/>/, />/, /\s/)))),
				end: /\/?>/,
				contains: [{
					className: "name",
					begin: TAG_NAME_RE,
					relevance: 0,
					starts: TAG_INTERNALS
				}]
			},
			{
				className: "tag",
				begin: regex.concat(/<\//, regex.lookahead(regex.concat(TAG_NAME_RE, />/))),
				contains: [{
					className: "name",
					begin: TAG_NAME_RE,
					relevance: 0
				}, {
					begin: />/,
					relevance: 0,
					endsParent: true
				}]
			}
		]
	};
}
//#endregion
//#region node_modules/highlight.js/es/languages/yaml.js
function yaml(hljs) {
	const LITERALS = "true false yes no null";
	const KEY = {
		className: "attr",
		variants: [
			{ begin: /[\w*@][\w*@ :()\./-]*:(?=[ \t]|$)/ },
			{ begin: /"[\w*@][\w*@ :()\./-]*":(?=[ \t]|$)/ },
			{ begin: /'[\w*@][\w*@ :()\./-]*':(?=[ \t]|$)/ }
		]
	};
	const TEMPLATE_VARIABLES = {
		className: "template-variable",
		variants: [{
			begin: /\{\{/,
			end: /\}\}/
		}, {
			begin: /%\{/,
			end: /\}/
		}]
	};
	const SINGLE_QUOTE_STRING = {
		className: "string",
		relevance: 0,
		begin: /'/,
		end: /'/,
		contains: [{
			match: /''/,
			scope: "char.escape",
			relevance: 0
		}]
	};
	const STRING = {
		className: "string",
		relevance: 0,
		variants: [{
			begin: /"/,
			end: /"/
		}, { begin: /\S+/ }],
		contains: [hljs.BACKSLASH_ESCAPE, TEMPLATE_VARIABLES]
	};
	const CONTAINER_STRING = hljs.inherit(STRING, { variants: [
		{
			begin: /'/,
			end: /'/,
			contains: [{
				begin: /''/,
				relevance: 0
			}]
		},
		{
			begin: /"/,
			end: /"/
		},
		{ begin: /[^\s,{}[\]]+/ }
	] });
	const TIMESTAMP = {
		className: "number",
		begin: "\\b[0-9]{4}(-[0-9][0-9]){0,2}([Tt \\t][0-9][0-9]?(:[0-9][0-9]){2})?(\\.[0-9]*)?([ \\t])*(Z|[-+][0-9][0-9]?(:[0-9][0-9])?)?\\b"
	};
	const VALUE_CONTAINER = {
		end: ",",
		endsWithParent: true,
		excludeEnd: true,
		keywords: LITERALS,
		relevance: 0
	};
	const OBJECT = {
		begin: /\{/,
		end: /\}/,
		contains: [VALUE_CONTAINER],
		illegal: "\\n",
		relevance: 0
	};
	const ARRAY = {
		begin: "\\[",
		end: "\\]",
		contains: [VALUE_CONTAINER],
		illegal: "\\n",
		relevance: 0
	};
	const MODES = [
		KEY,
		{
			className: "meta",
			begin: "^---\\s*$",
			relevance: 10
		},
		{
			className: "string",
			begin: "[\\|>]([1-9]?[+-])?[ ]*\\n( +)[^ ][^\\n]*\\n(\\2[^\\n]+\\n?)*"
		},
		{
			begin: "<%[%=-]?",
			end: "[%-]?%>",
			subLanguage: "ruby",
			excludeBegin: true,
			excludeEnd: true,
			relevance: 0
		},
		{
			className: "type",
			begin: "!\\w+![\\w#;/?:@&=+$,.~*'()[\\]]+"
		},
		{
			className: "type",
			begin: "!<[\\w#;/?:@&=+$,.~*'()[\\]]+>"
		},
		{
			className: "type",
			begin: "![\\w#;/?:@&=+$,.~*'()[\\]]+"
		},
		{
			className: "type",
			begin: "!![\\w#;/?:@&=+$,.~*'()[\\]]+"
		},
		{
			className: "meta",
			begin: "&" + hljs.UNDERSCORE_IDENT_RE + "$"
		},
		{
			className: "meta",
			begin: "\\*" + hljs.UNDERSCORE_IDENT_RE + "$"
		},
		{
			className: "bullet",
			begin: "-(?=[ ]|$)",
			relevance: 0
		},
		hljs.HASH_COMMENT_MODE,
		{
			beginKeywords: LITERALS,
			keywords: { literal: LITERALS }
		},
		TIMESTAMP,
		{
			className: "number",
			begin: hljs.C_NUMBER_RE + "\\b",
			relevance: 0
		},
		OBJECT,
		ARRAY,
		SINGLE_QUOTE_STRING,
		STRING
	];
	const VALUE_MODES = [...MODES];
	VALUE_MODES.pop();
	VALUE_MODES.push(CONTAINER_STRING);
	VALUE_CONTAINER.contains = VALUE_MODES;
	return {
		name: "YAML",
		case_insensitive: true,
		aliases: ["yml"],
		contains: MODES
	};
}
//#endregion
//#region node_modules/lowlight/lib/common.js
/**
* @import {LanguageFn} from 'highlight.js'
*/
/**
* Map of grammars.
*
* @type {Record<string, LanguageFn>}
*/
var grammars = {
	arduino,
	bash,
	c,
	cpp,
	csharp,
	css,
	diff,
	go,
	graphql,
	ini,
	java,
	javascript: javascript$1,
	json,
	kotlin,
	less,
	lua,
	makefile,
	markdown,
	objectivec,
	perl,
	php,
	"php-template": phpTemplate,
	plaintext,
	python,
	"python-repl": pythonRepl,
	r,
	ruby,
	rust,
	scss,
	shell,
	sql,
	swift,
	typescript,
	vbnet,
	wasm,
	xml,
	yaml
};
//#endregion
//#region node_modules/lowlight/lib/index.js
/**
* @import {ElementContent, Element, RootData, Root} from 'hast'
* @import {Emitter, HLJSOptions as HljsOptions, HighlightResult, LanguageFn} from 'highlight.js'
*/
/**
* @typedef {Object} ExtraOptions
*   Extra fields.
* @property {ReadonlyArray<string> | null | undefined} [subset]
*   List of allowed languages (default: all registered languages).
*
* @typedef Options
*   Configuration for `highlight`.
* @property {string | null | undefined} [prefix='hljs-']
*   Class prefix (default: `'hljs-'`).
*
* @typedef {Options & ExtraOptions} AutoOptions
*   Configuration for `highlightAuto`.
*/
/** @type {AutoOptions} */
var emptyOptions = {};
var defaultPrefix = "hljs-";
/**
* Create a `lowlight` instance.
*
* @param {Readonly<Record<string, LanguageFn>> | null | undefined} [grammars]
*   Grammars to add (optional).
* @returns
*   Lowlight.
*/
function createLowlight(grammars) {
	const high = core_default.newInstance();
	if (grammars) register(grammars);
	return {
		highlight,
		highlightAuto,
		listLanguages,
		register,
		registerAlias,
		registered
	};
	/**
	* Highlight `value` (code) as `language` (name).
	*
	* @example
	*   ```js
	*   import {common, createLowlight} from 'lowlight'
	*
	*   const lowlight = createLowlight(common)
	*
	*   console.log(lowlight.highlight('css', 'em { color: red }'))
	*   ```
	*
	*   Yields:
	*
	*   ```js
	*   {type: 'root', children: [Array], data: {language: 'css', relevance: 3}}
	*   ```
	*
	* @param {string} language
	*   Programming language name.
	* @param {string} value
	*   Code to highlight.
	* @param {Readonly<Options> | null | undefined} [options={}]
	*   Configuration (optional).
	* @returns {Root}
	*   Tree; with the following `data` fields: `language` (`string`), detected
	*   programming language name; `relevance` (`number`), how sure lowlight is
	*   that the given code is in the language.
	*/
	function highlight(language, value, options) {
		const settings = options || emptyOptions;
		const prefix = typeof settings.prefix === "string" ? settings.prefix : defaultPrefix;
		if (!high.getLanguage(language)) throw new Error("Unknown language: `" + language + "` is not registered");
		high.configure({
			__emitter: HastEmitter,
			classPrefix: prefix
		});
		const result = high.highlight(value, {
			ignoreIllegals: true,
			language
		});
		/* c8 ignore next 5 */
		if (result.errorRaised) throw new Error("Could not highlight with `Highlight.js`", { cause: result.errorRaised });
		const root = result._emitter.root;
		const data = root.data;
		data.language = result.language;
		data.relevance = result.relevance;
		return root;
	}
	/**
	* Highlight `value` (code) and guess its programming language.
	*
	* @example
	*   ```js
	*   import {common, createLowlight} from 'lowlight'
	*
	*   const lowlight = createLowlight(common)
	*
	*   console.log(lowlight.highlightAuto('"hello, " + name + "!"'))
	*   ```
	*
	*   Yields:
	*
	*   ```js
	*   {type: 'root', children: [Array], data: {language: 'arduino', relevance: 2}}
	*   ```
	*
	* @param {string} value
	*   Code to highlight.
	* @param {Readonly<AutoOptions> | null | undefined} [options={}]
	*   Configuration (optional).
	* @returns {Root}
	*   Tree; with the following `data` fields: `language` (`string`), detected
	*   programming language name; `relevance` (`number`), how sure lowlight is
	*   that the given code is in the language.
	*/
	function highlightAuto(value, options) {
		const subset = (options || emptyOptions).subset || listLanguages();
		let index = -1;
		let relevance = 0;
		/** @type {Root | undefined} */
		let result;
		while (++index < subset.length) {
			const name = subset[index];
			if (!high.getLanguage(name)) continue;
			const current = highlight(name, value, options);
			if (current.data && current.data.relevance !== void 0 && current.data.relevance > relevance) {
				relevance = current.data.relevance;
				result = current;
			}
		}
		return result || {
			type: "root",
			children: [],
			data: {
				language: void 0,
				relevance
			}
		};
	}
	/**
	* List registered languages.
	*
	* @example
	*   ```js
	*   import {createLowlight} from 'lowlight'
	*   import markdown from 'highlight.js/lib/languages/markdown'
	*
	*   const lowlight = createLowlight()
	*
	*   console.log(lowlight.listLanguages()) // => []
	*
	*   lowlight.register({markdown})
	*
	*   console.log(lowlight.listLanguages()) // => ['markdown']
	*   ```
	*
	* @returns {Array<string>}
	*   Names of registered language.
	*/
	function listLanguages() {
		return high.listLanguages();
	}
	/**
	* Register languages.
	*
	* @example
	*   ```js
	*   import {createLowlight} from 'lowlight'
	*   import xml from 'highlight.js/lib/languages/xml'
	*
	*   const lowlight = createLowlight()
	*
	*   lowlight.register({xml})
	*
	*   // Note: `html` is an alias for `xml`.
	*   console.log(lowlight.highlight('html', '<em>Emphasis</em>'))
	*   ```
	*
	*   Yields:
	*
	*   ```js
	*   {type: 'root', children: [Array], data: {language: 'html', relevance: 2}}
	*   ```
	*
	* @overload
	* @param {Readonly<Record<string, LanguageFn>>} grammars
	* @returns {undefined}
	*
	* @overload
	* @param {string} name
	* @param {LanguageFn} grammar
	* @returns {undefined}
	*
	* @param {Readonly<Record<string, LanguageFn>> | string} grammarsOrName
	*   Grammars or programming language name.
	* @param {LanguageFn | undefined} [grammar]
	*   Grammar, if with name.
	* @returns {undefined}
	*   Nothing.
	*/
	function register(grammarsOrName, grammar) {
		if (typeof grammarsOrName === "string") high.registerLanguage(grammarsOrName, grammar);
		else {
			/** @type {string} */
			let name;
			for (name in grammarsOrName) if (Object.hasOwn(grammarsOrName, name)) high.registerLanguage(name, grammarsOrName[name]);
		}
	}
	/**
	* Register aliases.
	*
	* @example
	*   ```js
	*   import {createLowlight} from 'lowlight'
	*   import markdown from 'highlight.js/lib/languages/markdown'
	*
	*   const lowlight = createLowlight()
	*
	*   lowlight.register({markdown})
	*
	*   // lowlight.highlight('mdown', '<em>Emphasis</em>')
	*   // ^ would throw: Error: Unknown language: `mdown` is not registered
	*
	*   lowlight.registerAlias({markdown: ['mdown', 'mkdn', 'mdwn', 'ron']})
	*   lowlight.highlight('mdown', '<em>Emphasis</em>')
	*   // ^ Works!
	*   ```
	*
	* @overload
	* @param {Readonly<Record<string, ReadonlyArray<string> | string>>} aliases
	* @returns {undefined}
	*
	* @overload
	* @param {string} language
	* @param {ReadonlyArray<string> | string} alias
	* @returns {undefined}
	*
	* @param {Readonly<Record<string, ReadonlyArray<string> | string>> | string} aliasesOrName
	*   Map of programming language names to one or more aliases, or programming
	*   language name.
	* @param {ReadonlyArray<string> | string | undefined} [alias]
	*   One or more aliases for the programming language, if with `name`.
	* @returns {undefined}
	*   Nothing.
	*/
	function registerAlias(aliasesOrName, alias) {
		if (typeof aliasesOrName === "string") high.registerAliases(typeof alias === "string" ? alias : [...alias], { languageName: aliasesOrName });
		else {
			/** @type {string} */
			let key;
			for (key in aliasesOrName) if (Object.hasOwn(aliasesOrName, key)) {
				const aliases = aliasesOrName[key];
				high.registerAliases(typeof aliases === "string" ? aliases : [...aliases], { languageName: key });
			}
		}
	}
	/**
	* Check whether an alias or name is registered.
	*
	* @example
	*   ```js
	*   import {createLowlight} from 'lowlight'
	*   import javascript from 'highlight.js/lib/languages/javascript'
	*
	*   const lowlight = createLowlight({javascript})
	*
	*   console.log(lowlight.registered('funkyscript')) // => `false`
	*
	*   lowlight.registerAlias({javascript: 'funkyscript'})
	*   console.log(lowlight.registered('funkyscript')) // => `true`
	*   ```
	*
	* @param {string} aliasOrName
	*   Name of a language or alias for one.
	* @returns {boolean}
	*   Whether `aliasOrName` is registered.
	*/
	function registered(aliasOrName) {
		return Boolean(high.getLanguage(aliasOrName));
	}
}
/** @type {Emitter} */
var HastEmitter = class {
	/**
	* @param {Readonly<HljsOptions>} options
	*   Configuration.
	* @returns
	*   Instance.
	*/
	constructor(options) {
		/** @type {HljsOptions} */
		this.options = options;
		/** @type {Root} */
		this.root = {
			type: "root",
			children: [],
			data: {
				language: void 0,
				relevance: 0
			}
		};
		/** @type {[Root, ...Array<Element>]} */
		this.stack = [this.root];
	}
	/**
	* @param {string} value
	*   Text to add.
	* @returns {undefined}
	*   Nothing.
	*
	*/
	addText(value) {
		if (value === "") return;
		const current = this.stack[this.stack.length - 1];
		const tail = current.children[current.children.length - 1];
		if (tail && tail.type === "text") tail.value += value;
		else current.children.push({
			type: "text",
			value
		});
	}
	/**
	*
	* @param {unknown} rawName
	*   Name to add.
	* @returns {undefined}
	*   Nothing.
	*/
	startScope(rawName) {
		this.openNode(String(rawName));
	}
	/**
	* @returns {undefined}
	*   Nothing.
	*/
	endScope() {
		this.closeNode();
	}
	/**
	* @param {HastEmitter} other
	*   Other emitter.
	* @param {string} name
	*   Name of the sublanguage.
	* @returns {undefined}
	*   Nothing.
	*/
	__addSublanguage(other, name) {
		const current = this.stack[this.stack.length - 1];
		const results = other.root.children;
		if (name) current.children.push({
			type: "element",
			tagName: "span",
			properties: { className: [name] },
			children: results
		});
		else current.children.push(...results);
	}
	/**
	* @param {string} name
	*   Name to add.
	* @returns {undefined}
	*   Nothing.
	*/
	openNode(name) {
		const self = this;
		const className = name.split(".").map(function(d, i) {
			return i ? d + "_".repeat(i) : self.options.classPrefix + d;
		});
		const current = this.stack[this.stack.length - 1];
		/** @type {Element} */
		const child = {
			type: "element",
			tagName: "span",
			properties: { className },
			children: []
		};
		current.children.push(child);
		this.stack.push(child);
	}
	/**
	* @returns {undefined}
	*   Nothing.
	*/
	closeNode() {
		this.stack.pop();
	}
	/**
	* @returns {undefined}
	*   Nothing.
	*/
	finalize() {}
	/**
	* @returns {string}
	*   Nothing.
	*/
	toHTML() {
		return "";
	}
};
//#endregion
//#region node_modules/highlight.js/styles/github.css
var import_read_time_estimate = /* @__PURE__ */ __toESM(require_read_time_estimate());
//#endregion
//#region resources/js/util/tiptap.js
async function tiptap_default() {
	const [core, vue3, state, model, view] = await Promise.all([
		__vitePreload(() => import("./dist-BPhk3Po_.js").then((n) => n.N), __vite__mapDeps([0,1,2,3,4]), import.meta.url),
		__vitePreload(() => import("./Set-BU6JdaJ0.js").then((n) => n.c), __vite__mapDeps([5,6,1,7,8,9,10,11,12,13,14,15,0,2,3,4]), import.meta.url),
		__vitePreload(() => import("./state-CAceU76W.js").then((n) => n.t), __vite__mapDeps([16,1,4,3]), import.meta.url),
		__vitePreload(() => import("./model-BO7ad71n.js").then((n) => n.t), __vite__mapDeps([17,1,3]), import.meta.url),
		__vitePreload(() => import("./view-BHoMFKQh.js").then((n) => n.t), __vite__mapDeps([2,1,3,4]), import.meta.url)
	]);
	return {
		core,
		vue3,
		pm: {
			state,
			model,
			view
		}
	};
}
//#endregion
//#region resources/js/components/fieldtypes/bard/BardFieldtype.vue
var lowlight = createLowlight(grammars);
var tiptap = null;
var commandPaletteCallbackRegistered = false;
var _sfc_main = {
	mixins: [Fieldtype_default, ManagesSetMeta_default],
	components: {
		BubbleMenu,
		BardToolbarButton: ToolbarButton_default,
		SetPicker: SetPicker_default,
		EditorContent,
		FloatingMenu,
		LinkToolbarButton: LinkToolbarButton_default
	},
	provide: { isInBardField: true },
	data() {
		return {
			events: new import_tiny_emitter.default(),
			editor: null,
			html: null,
			json: [],
			fullScreenMode: false,
			buttons: [],
			collapsed: this.meta.collapsed,
			mounted: false,
			initError: null,
			pageHeader: null,
			escBinding: null,
			showAddSetButton: false,
			hasBeenFocused: false,
			provide: {
				bard: this.makeBardProvide(),
				bardSets: this.config.sets,
				showReplicatorFieldPreviews: this.config.previews
			},
			errorsById: {},
			debounceNextUpdate: true,
			setsCache: {},
			assetsCache: {},
			loadingSet: null
		};
	},
	computed: {
		setFieldPathPrefix() {
			return this.fieldPathPrefix ? `${this.fieldPathPrefix}.${this.handle}` : this.handle;
		},
		toolbarIsFixed() {
			return this.config.toolbar_mode === "fixed";
		},
		toolbarIsFloating() {
			return this.config.toolbar_mode === "floating";
		},
		showFixedToolbar() {
			return this.toolbarIsFixed && (this.visibleButtons.length > 0 || this.hasExtraButtons);
		},
		hasExtraButtons() {
			return this.setConfigs.length > 0 || this.config.fullscreen;
		},
		readingTime() {
			if (this.html) {
				var durationMs = (0, import_read_time_estimate.default)(this.html, 265, 12, 500, [
					"img",
					"Image",
					"bard-set"
				]).duration * 60 * 1e3;
				var minutes = Math.floor(durationMs / 6e4);
				var seconds = Math.floor(durationMs % 6e4 / 1e3);
				return String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0");
			}
		},
		characterAndWordCountText() {
			const showWordCount = this.config.word_count;
			const wordCount = this.editor.storage.characterCount.words();
			const wordCountText = `${__n(":count word|:count words", wordCount)}`;
			const charLimit = this.config.character_limit;
			const showCharLimit = charLimit > 0;
			const charCount = this.editor.storage.characterCount.characters();
			if (showCharLimit && showWordCount) return `${wordCountText}, ${__(":count/:total characters", {
				count: charCount,
				total: charLimit
			})}`;
			if (showCharLimit) return `${charCount}/${charLimit}`;
			if (showWordCount) return wordCountText;
		},
		setIndexes() {
			let indexes = {};
			this.json.forEach((item, i) => {
				if (item.type === "set") indexes[item.attrs.id] = i;
			});
			return indexes;
		},
		site() {
			return this.publishContainer.site ?? this.$config.get("selectedSite");
		},
		replicatorPreview() {
			if (!this.showFieldPreviews) return;
			const stack = [...this.value];
			let text = "";
			while (stack.length) {
				const node = stack.shift();
				if (node.type === "text") text += ` ${node.text || ""}`;
				else if (node.type === "set") {
					const handle = node.attrs.values.type;
					const set = this.setConfigs.find((set) => set.handle === handle);
					text += ` [${__(set ? set.display : handle)}]`;
				}
				if (text.length > 150) break;
				if (node.content) stack.unshift(...node.content);
			}
			return text;
		},
		inputIsInline() {
			return this.config.inline;
		},
		wrapperClasses() {
			return `form-group publish-field publish-field__${this.handle} bard-fieldtype`;
		},
		hasSets() {
			return this.value.some((item) => item.type === "set");
		},
		setConfigs() {
			return this.groupConfigs.reduce((sets, group) => {
				return sets.concat(group.sets);
			}, []);
		},
		groupConfigs() {
			return this.config.sets;
		},
		internalFieldActions() {
			return [
				{
					title: __("Expand All Sets"),
					icon: "expand",
					quick: true,
					disabled: () => this.collapsed.length === 0,
					visibleWhenReadOnly: true,
					run: this.expandAll,
					visible: this.setConfigs.length > 0 && this.hasSets
				},
				{
					title: __("Collapse All Sets"),
					icon: "collapse",
					quick: true,
					disabled: () => this.collapsed.length > 0,
					visibleWhenReadOnly: true,
					run: this.collapseAll,
					visible: this.setConfigs.length > 0 && this.hasSets
				},
				{
					title: __("Toggle Fullscreen Mode"),
					icon: ({ vm }) => vm.fullScreenMode ? "fullscreen-close" : "fullscreen-open",
					quick: true,
					run: this.toggleFullscreen,
					visibleWhenReadOnly: true,
					visible: this.config.fullscreen
				}
			];
		},
		shouldShowAddSetHelperText() {
			return !this.$refs.setPicker?.isOpen && this.suitableToShowSetButton(this.editor);
		}
	},
	created() {
		if (!Statamic.$components.has("NodeViewWrapper")) Statamic.$components.register("NodeViewWrapper", NodeViewWrapper);
		if (!Statamic.$components.has("NodeViewContent")) Statamic.$components.register("NodeViewContent", NodeViewContent);
	},
	async mounted() {
		tiptap = await tiptap_default();
		this.initToolbarButtons();
		this.initEditor();
		this.json = this.editor.getJSON().content;
		this.html = this.editor.getHTML();
		this.$nextTick(() => this.mounted = true);
		this.pageHeader = document.querySelector(".global-header");
		if (!commandPaletteCallbackRegistered) {
			commandPaletteCallbackRegistered = true;
			Statamic.$commandPalette.preventIf(() => {
				const selection = window.getSelection();
				return (selection?.anchorNode)?.parentElement?.closest(".bard-editor") !== null && selection?.toString().length > 0;
			});
		}
		this.$nextTick(() => {
			let el = document.querySelector(`label[for="${this.fieldId}"]`);
			if (el) el.addEventListener("click", () => {
				this.editor.commands.focus();
			});
		});
	},
	beforeUnmount() {
		this.editor?.destroy();
		this.escBinding?.destroy();
	},
	watch: {
		json(json, oldJson) {
			if (!this.mounted) return;
			if (JSON.stringify(json) === JSON.stringify(oldJson)) return;
			const shouldDebounce = this.debounceNextUpdate;
			this.debounceNextUpdate = true;
			if (shouldDebounce) this.updateDebounced(json);
			else {
				this.updateDebounced.cancel();
				this.update(json);
			}
		},
		value(value, oldValue) {
			if (!this.editor) return;
			if (document.activeElement?.closest(".bard-content")) return;
			const oldContent = this.editor.getJSON();
			const content = this.valueToContent(value);
			if (JSON.stringify(content) !== JSON.stringify(oldContent)) {
				this.editor.commands.clearContent();
				this.editor.commands.setContent(content, true);
			}
		},
		readOnly(readOnly) {
			this.editor.setEditable(!this.readOnly);
		},
		collapsed(value) {
			const meta = this.meta;
			meta.collapsed = value;
			this.updateMeta(meta);
		},
		fullScreenMode(fullScreenMode) {
			this.initEditor();
			if (fullScreenMode) {
				this.escBinding = this.$keys.bindGlobal("esc", this.closeFullscreen);
				this.$nextTick(() => {
					if (this.editor) this.editor.commands.focus();
				});
			} else this.escBinding?.destroy();
		},
		loadingSet(loading) {
			this.$progress.loading("bard-set", !!loading);
		},
		"publishContainer.errors": {
			immediate: true,
			handler(errors) {
				this.errorsById = Object.entries(errors).reduce((acc, [key, value]) => {
					if (!key.startsWith(this.setFieldPathPrefix)) return acc;
					const setIndex = key.replace(`${this.setFieldPathPrefix}.`, "").split(".").shift();
					const setId = this.value[setIndex]?.attrs.id;
					if (setId) acc[setId] = value;
					return acc;
				}, {});
			}
		}
	},
	methods: {
		addSet(handle) {
			this.loadingSet = handle;
			this.fetchSet(handle).then((data) => this._addSet(handle, data)).catch(() => this.$toast.error(__("Something went wrong"))).finally(() => this.loadingSet = null);
		},
		_addSet(handle, data) {
			const id = nanoid();
			const deepCopy = JSON.parse(JSON.stringify(data.defaults));
			const values = Object.assign({}, { type: handle }, deepCopy);
			this.updateSetMeta(id, data.new);
			const { $head } = this.editor.view.state.selection;
			const { nodeBefore } = $head;
			this.debounceNextUpdate = false;
			this.$nextTick(() => {
				if (nodeBefore) this.editor.commands.setAt({
					attrs: {
						id,
						values
					},
					pos: $head.pos
				});
				else this.editor.commands.set({
					id,
					values
				});
			});
		},
		async fetchSet(set) {
			return new Promise(async (resolve, reject) => {
				const field = this.bardFieldPath();
				const setCacheKey = `${field}.${set}`;
				const reference = this.publishContainer.reference;
				const token = this.publishContainer.blueprint.token;
				if (this.meta.new?.hasOwnProperty(set)) {
					let meta = this.meta.new[set];
					let defaults = this.meta.defaults[set];
					resolve({
						new: meta,
						defaults
					});
					return;
				}
				if (this.setsCache[setCacheKey]) {
					resolve(this.setsCache[setCacheKey]);
					return;
				}
				this.$axios.post(cp_url("fieldtypes/replicator/set"), {
					token,
					reference,
					field,
					set
				}).then((response) => {
					this.setsCache[setCacheKey] = response.data;
					resolve(response.data);
				}).catch((error) => reject(error));
			});
		},
		/**
		* Returns the path to the Bard field, replacing any set indexes with handles.
		*/
		bardFieldPath() {
			if (!this.fieldPathPrefix) return this.handle;
			return this.fieldPathKeys.map((key, index) => {
				if (["attrs", "values"].includes(key)) return;
				if (Number.isInteger(parseInt(key))) {
					let setValues = data_get(this.publishContainer.values, this.fieldPathKeys.slice(0, index + 1).join("."));
					return setValues.attrs?.values.type || setValues.type;
				}
				return key;
			}).filter((key) => key !== void 0).concat(this.handle).join(".");
		},
		duplicateSet(old_id, attrs, getPos) {
			const id = nanoid();
			const enabled = attrs.enabled;
			const deepCopy = JSON.parse(JSON.stringify(attrs.values));
			const values = Object.assign({}, deepCopy);
			this.updateSetMeta(id, this.meta.existing[old_id]);
			this.debounceNextUpdate = false;
			this.$nextTick(() => {
				const pos = getPos();
				const insertPos = pos + (this.editor.state.doc.nodeAt(pos)?.nodeSize || 0);
				this.editor.commands.setAt({
					attrs: {
						id,
						enabled,
						values
					},
					pos: insertPos
				});
			});
		},
		async pasteSet(attrs) {
			const old_id = attrs.id;
			const id = nanoid();
			const enabled = attrs.enabled;
			const values = Object.assign({}, attrs.values);
			if (this.meta.existing[old_id]) this.updateSetMeta(id, this.meta.existing[old_id]);
			else {
				const data = await this.fetchSet(values.type);
				this.updateSetMeta(id, data.new);
			}
			return {
				id,
				enabled,
				values
			};
		},
		collapseSet(id) {
			if (!this.collapsed.includes(id)) this.collapsed.push(id);
		},
		expandSet(id) {
			if (this.config.collapse === "accordion") {
				this.collapsed = Object.keys(this.meta.existing).filter((v) => v !== id);
				return;
			}
			if (this.collapsed.includes(id)) {
				var index = this.collapsed.indexOf(id);
				this.collapsed.splice(index, 1);
			}
		},
		collapseAll() {
			this.collapsed = Object.keys(this.meta.existing);
		},
		expandAll() {
			this.collapsed = [];
		},
		toggleCollapseSets() {
			this.collapsed.length === 0 ? this.collapseAll() : this.expandAll();
		},
		toggleFullscreen() {
			this.fullScreenMode = !this.fullScreenMode;
		},
		closeFullscreen() {
			this.fullScreenMode = false;
		},
		shouldShowSetButton({ view, state }) {
			const isActive = this.suitableToShowSetButton({
				view,
				state
			});
			return this.setConfigs.length && (this.config.always_show_set_button || isActive);
		},
		suitableToShowSetButton({ view, state }) {
			const { selection } = state;
			const { $anchor, empty } = selection;
			const isRootDepth = $anchor.depth === 1;
			const isEmptyTextBlock = $anchor.parent.isTextblock && !$anchor.parent.firstChild && !$anchor.parent.type.spec.code && !$anchor.parent.textContent;
			const isAroundInlineImage = state.selection.$to.nodeBefore?.type.name === "image" || state.selection.$to.nodeAfter?.type.name === "image";
			return view.hasFocus() && empty && isRootDepth && isEmptyTextBlock && !isAroundInlineImage;
		},
		initToolbarButtons() {
			const selectedButtons = this.config.buttons || [
				"h2",
				"h3",
				"bold",
				"italic",
				"unorderedlist",
				"orderedlist",
				"removeformat",
				"quote",
				"anchor",
				"table"
			];
			if (selectedButtons.includes("table")) selectedButtons.push("deletetable", "addcolumnbefore", "addcolumnafter", "deletecolumn", "addrowbefore", "addrowafter", "deleterow", "togglecellmerge", "toggleheadercell");
			let buttons = selectedButtons.map((button) => {
				return availableButtons().find((b) => b.name === button.toLowerCase()) || button;
			});
			this.$bard.buttonCallbacks.forEach((callback) => {
				const buttonFn = (button) => selectedButtons.includes(button.name) ? button : null;
				const addedButtons = callback(buttons, buttonFn);
				if (!addedButtons) return;
				buttons = buttons.concat(Array.isArray(addedButtons) ? addedButtons : [addedButtons]);
			});
			buttons = buttons.filter((button) => !!button);
			buttons = buttons.filter((button) => typeof button != "string");
			buttons = addButtonHtml(buttons);
			buttons = buttons.filter((button) => {
				return button.condition ? button.condition.call(null, this.config) : true;
			});
			if (buttons.find((b) => b.name === "table")) buttons.push({
				name: "deletetable",
				text: __("Delete Table"),
				command: (editor) => editor.commands.deleteTable(),
				svg: "delete-table",
				visibleWhenActive: "table"
			}, {
				name: "addcolumnbefore",
				text: __("Add Column Before"),
				command: (editor) => editor.commands.addColumnBefore(),
				svg: "add-col-before",
				visibleWhenActive: "table"
			}, {
				name: "addcolumnafter",
				text: __("Add Column After"),
				command: (editor) => editor.commands.addColumnAfter(),
				svg: "add-col-after",
				visibleWhenActive: "table"
			}, {
				name: "deletecolumn",
				text: __("Delete Column"),
				command: (editor) => editor.commands.deleteColumn(),
				svg: "delete-col",
				visibleWhenActive: "table"
			}, {
				name: "addrowbefore",
				text: __("Add Row Before"),
				command: (editor) => editor.commands.addRowBefore(),
				svg: "add-row-before",
				visibleWhenActive: "table"
			}, {
				name: "addrowafter",
				text: __("Add Row After"),
				command: (editor) => editor.commands.addRowAfter(),
				svg: "add-row-after",
				visibleWhenActive: "table"
			}, {
				name: "deleterow",
				text: __("Delete Row"),
				command: (editor) => editor.commands.deleteRow(),
				svg: "delete-row",
				visibleWhenActive: "table"
			}, {
				name: "toggleheadercell",
				text: __("Toggle Header Cell"),
				command: (editor) => editor.commands.toggleHeaderCell(),
				svg: "flip-vertical",
				visibleWhenActive: "table"
			}, {
				name: "togglecellmerge",
				text: __("Merge Cells"),
				command: (editor) => editor.commands.mergeCells(),
				svg: "combine-cells",
				visibleWhenActive: "table"
			});
			this.buttons = buttons;
		},
		buttonIsActive(button) {
			if (button.hasOwnProperty("active")) return button.active(this.editor, button.args);
			const name = button[button.hasOwnProperty("activeName") ? "activeName" : "name"];
			return this.editor.isActive(name, button.args);
		},
		buttonIsVisible(button) {
			if (button.hasOwnProperty("visible")) return button.visible(this.editor, button.args);
			if (!button.hasOwnProperty("visibleWhenActive")) return true;
			return this.editor.isActive(button.visibleWhenActive, button.args);
		},
		visibleButtons(buttons) {
			return buttons.filter((button) => this.buttonIsVisible(button));
		},
		initEditor() {
			if (this.editor) this.editor.destroy();
			const content = this.valueToContent(clone(this.value));
			this.editor = new Editor({
				extensions: this.getExtensions(),
				content,
				editable: !this.readOnly,
				enableInputRules: this.config.enable_input_rules,
				enablePasteRules: this.config.enable_paste_rules,
				editorProps: { attributes: { class: "bard-content" } },
				onDrop: () => this.debounceNextUpdate = false,
				onFocus: () => {
					this.hasBeenFocused = true;
					this.$emit("focus");
				},
				onBlur: () => {
					setTimeout(() => {
						const isInsideBard = this.$refs.container.contains(document.activeElement);
						const isSetPickerSearch = document.activeElement.hasAttribute("data-set-picker-search-input");
						const isSetPickerOpen = !!this.$refs.setPicker?.isOpen;
						if (!isInsideBard && !isSetPickerSearch && !isSetPickerOpen) {
							this.$emit("blur");
							this.showAddSetButton = false;
						}
					}, 1);
				},
				onUpdate: () => {
					const oldJson = this.json;
					const newJson = clone(this.editor.getJSON().content);
					const countNodes = (nodes) => {
						if (!nodes || !Array.isArray(nodes)) return 0;
						let count = nodes.length;
						nodes.forEach((node) => {
							if (node.content) count += countNodes(node.content);
						});
						return count;
					};
					if (countNodes(oldJson) !== countNodes(newJson)) this.debounceNextUpdate = false;
					this.json = newJson;
					this.html = this.editor.getHTML();
				},
				onCreate: ({ editor }) => {
					const state = editor.view.state;
					if (content !== null && typeof content === "object") try {
						state.schema.nodeFromJSON(content);
					} catch (error) {
						const invalidError = this.invalidError(error);
						if (invalidError) this.initError = invalidError;
						else {
							this.initError = __("Something went wrong");
							console.error(error);
						}
					}
				}
			});
		},
		invalidError(error) {
			const messages = {
				"Invalid text node in JSON": "Invalid content, text values must be strings",
				"Empty text nodes are not allowed": "Invalid content, text values cannot be empty"
			};
			if (messages[error.message]) return __(messages[error.message]);
			let match;
			if (match = error.message.match(/^(?:There is no|Unknown) (?:node|mark) type:? (\w*)(?: in this schema)?$/)) if (match[1]) return __("Invalid content, :type button/extension is not enabled", { type: match[1] });
			else return __("Invalid content, nodes and marks must have a type");
		},
		setHasError(id) {
			return this.errorsById.hasOwnProperty(id) && this.errorsById[id].length > 0;
		},
		valueToContent(value) {
			return value.length ? {
				type: "doc",
				content: value
			} : null;
		},
		getExtensions() {
			let modeExts = this.inputIsInline ? [DocumentInline] : [DocumentBlock, index_default$13];
			if (this.inputIsInline && this.config.inline_hard_breaks) modeExts.push(index_default$13.extend({ addKeyboardShortcuts() {
				return {
					...this.parent?.(),
					Enter: () => this.editor.commands.setHardBreak()
				};
			} }));
			if (this.config.placeholder) modeExts.push(Placeholder.configure({ placeholder: __(this.config.placeholder) }));
			const DisableCtrlEnter = Extension.create({
				name: "disableCtrlEnter",
				addKeyboardShortcuts() {
					return {
						"Ctrl-Enter": () => true,
						"Cmd-Enter": () => true
					};
				}
			});
			const SlashSetPicker = Extension.create({
				name: "slashSetPicker",
				addKeyboardShortcuts() {
					return { "/": () => {
						const { view, state } = this.editor;
						if (this.options.allowed({
							view,
							state
						})) {
							if (this.options.setConfigs.length === 1) this.options.addSet(this.options.setConfigs[0].handle);
							else this.options.openSetPicker();
							return true;
						}
						return false;
					} };
				}
			});
			let exts = [
				index_default$16.configure({ limit: this.config.character_limit }),
				...modeExts,
				DisableCtrlEnter,
				SlashSetPicker.configure({
					shown: computed(() => this.showAddSetButton),
					allowed: this.suitableToShowSetButton,
					openSetPicker: this.openSetPicker,
					setConfigs: this.setConfigs,
					addSet: this.addSet
				}),
				Dropcursor,
				Gapcursor,
				index_default$11,
				index_default$8,
				Set$1.configure({
					bard: this,
					foo: "bar"
				}),
				index_default$4
			];
			if (this.config.smart_typography) exts.push(index_default$2);
			let btns = this.buttons.map((button) => button.name);
			if (btns.includes("anchor")) exts.push(Link.configure({ vm: this }));
			if (btns.includes("bold")) exts.push(index_default$17);
			if (btns.includes("code")) exts.push(index_default$15);
			if (btns.includes("codeblock")) exts.push(index_default$14.configure({ lowlight }));
			if (btns.includes("horizontalrule")) exts.push(index_default$10);
			if (btns.includes("image")) exts.push(Image.configure({ bard: this }));
			if (btns.includes("italic")) exts.push(index_default$9);
			if (btns.includes("quote")) exts.push(index_default$18);
			if (btns.includes("orderedlist")) exts.push(OrderedList);
			if (btns.includes("orderedlist") || btns.includes("unorderedlist")) exts.push(ListItem);
			if (btns.includes("underline")) exts.push(index_default$1);
			if (btns.includes("unorderedlist")) exts.push(BulletList);
			if (btns.includes("small")) exts.push(Small);
			if (btns.includes("strikethrough")) exts.push(index_default$7);
			if (btns.includes("subscript")) exts.push(index_default$6);
			if (btns.includes("superscript")) exts.push(index_default$5);
			let levels = [];
			if (btns.includes("h1")) levels.push(1);
			if (btns.includes("h2")) levels.push(2);
			if (btns.includes("h3")) levels.push(3);
			if (btns.includes("h4")) levels.push(4);
			if (btns.includes("h5")) levels.push(5);
			if (btns.includes("h6")) levels.push(6);
			if (levels.length) exts.push(index_default$12.configure({ levels }));
			let alignmentTypes = ["paragraph"];
			if (levels.length) alignmentTypes.push("heading");
			let alignments = [];
			if (btns.includes("alignleft")) alignments.push("left");
			if (btns.includes("aligncenter")) alignments.push("center");
			if (btns.includes("alignright")) alignments.push("right");
			if (btns.includes("alignjustify")) alignments.push("justify");
			if (alignments.length) exts.push(index_default$3.configure({
				types: alignmentTypes,
				alignments
			}));
			if (btns.includes("table")) exts.push(Table.configure({ resizable: true }), TableHeader, TableCell, TableRow);
			this.$bard.extensionCallbacks.forEach((callback) => {
				let returned = callback({
					bard: this,
					tiptap
				});
				exts = exts.concat(Array.isArray(returned) ? returned : [returned]);
			});
			this.$bard.extensionReplacementCallbacks.forEach(({ callback, name }) => {
				let index = exts.findIndex((ext) => ext.name === name);
				if (index === -1) return;
				let extension = exts[index];
				let newExtension = callback({
					bard: this,
					extension
				});
				exts[index] = newExtension;
			});
			return exts;
		},
		ignorePageHeader(ignore) {
			if (this.pageHeader) this.pageHeader.style["pointer-events"] = ignore ? "none" : "all";
		},
		makeBardProvide() {
			const bard = {};
			Object.defineProperties(bard, {
				setConfigs: { get: () => this.setConfigs },
				isReadOnly: { get: () => this.readOnly },
				hasBeenFocused: { get: () => this.hasBeenFocused }
			});
			return bard;
		},
		openSetPicker() {
			this.$refs.setPicker.open();
		}
	}
};
var _hoisted_1 = {
	key: 0,
	class: "bard-fixed-toolbar border-0"
};
var _hoisted_2 = {
	key: 0,
	class: "no-select flex flex-1 flex-wrap items-center gap-1"
};
var _hoisted_3 = {
	key: 1,
	class: "bard-fixed-toolbar"
};
var _hoisted_4 = {
	key: 0,
	class: "no-select flex flex-1 flex-wrap items-center gap-1"
};
var _hoisted_5 = { class: "bard-floating-toolbar" };
var _hoisted_6 = ["textContent"];
var _hoisted_7 = {
	key: 2,
	class: "bard-footer-toolbar"
};
var _hoisted_8 = { key: 0 };
var _hoisted_9 = { key: 1 };
var _hoisted_10 = ["textContent"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_publish_field_fullscreen_header = resolveComponent("publish-field-fullscreen-header");
	const _component_bubble_menu = resolveComponent("bubble-menu");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_ui_description = resolveComponent("ui-description");
	const _component_set_picker = resolveComponent("set-picker");
	const _component_floating_menu = resolveComponent("floating-menu");
	const _component_editor_content = resolveComponent("editor-content");
	const _component_portal = resolveComponent("portal");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createBlock(_component_portal, {
		name: "bard-fullscreen",
		disabled: !$data.fullScreenMode,
		provide: $data.provide
	}, {
		default: withCtx(() => [createBaseVNode("div", { class: normalizeClass({ "publish-fields": $data.fullScreenMode }) }, [createBaseVNode("div", { class: normalizeClass($data.fullScreenMode && $options.wrapperClasses) }, [createBaseVNode("div", {
			class: normalizeClass(["bard-fieldtype antialiased with-contrast:border-gray-500 shadow-ui-sm", { "bard-fullscreen": $data.fullScreenMode }]),
			ref: "container",
			onDragstart: _cache[2] || (_cache[2] = withModifiers(($event) => $options.ignorePageHeader(true), ["stop"])),
			onDragend: _cache[3] || (_cache[3] = ($event) => $options.ignorePageHeader(false))
		}, [
			$data.fullScreenMode ? (openBlock(), createBlock(_component_publish_field_fullscreen_header, {
				key: 0,
				title: _ctx.config.display,
				"field-actions": _ctx.fieldActions,
				onClose: $options.toggleFullscreen
			}, {
				default: withCtx(() => [!_ctx.readOnly && $options.showFixedToolbar ? (openBlock(), createElementBlock("div", _hoisted_1, [$options.toolbarIsFixed ? (openBlock(), createElementBlock("div", _hoisted_2, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.visibleButtons($data.buttons), (button) => {
					return openBlock(), createBlock(resolveDynamicComponent(button.component || "BardToolbarButton"), {
						key: button.name,
						button,
						active: $options.buttonIsActive(button),
						config: _ctx.config,
						bard: this,
						editor: $data.editor
					}, null, 8, [
						"button",
						"active",
						"config",
						"editor"
					]);
				}), 128))])) : createCommentVNode("", true)])) : createCommentVNode("", true)]),
				_: 1
			}, 8, [
				"title",
				"field-actions",
				"onClose"
			])) : createCommentVNode("", true),
			!_ctx.readOnly && $options.showFixedToolbar && !$data.fullScreenMode ? (openBlock(), createElementBlock("div", _hoisted_3, [$options.toolbarIsFixed ? (openBlock(), createElementBlock("div", _hoisted_4, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.visibleButtons($data.buttons), (button) => {
				return openBlock(), createBlock(resolveDynamicComponent(button.component || "BardToolbarButton"), {
					key: button.name,
					button,
					active: $options.buttonIsActive(button),
					config: _ctx.config,
					bard: this,
					editor: $data.editor
				}, null, 8, [
					"button",
					"active",
					"config",
					"editor"
				]);
			}), 128))])) : createCommentVNode("", true)])) : createCommentVNode("", true),
			createBaseVNode("div", {
				class: normalizeClass(["bard-editor @container/bard", {
					"mode:read-only": _ctx.readOnly,
					"mode:minimal": !$options.showFixedToolbar,
					"mode:inline": $options.inputIsInline,
					"focus-within:focus-outline": !$data.fullScreenMode
				}]),
				tabindex: "0"
			}, [
				$data.editor && $options.toolbarIsFloating && !_ctx.readOnly ? (openBlock(), createBlock(_component_bubble_menu, {
					editor: $data.editor,
					key: `bubble-menu-${$data.fullScreenMode}`,
					options: {
						placement: "top",
						offset: [0, 10]
					}
				}, {
					default: withCtx(() => [createBaseVNode("div", _hoisted_5, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.visibleButtons($data.buttons), (button) => {
						return openBlock(), createBlock(resolveDynamicComponent(button.component || "BardToolbarButton"), {
							key: button.name,
							button,
							active: $options.buttonIsActive(button),
							bard: this,
							config: _ctx.config,
							editor: $data.editor,
							variant: "floating"
						}, null, 8, [
							"button",
							"active",
							"config",
							"editor"
						]);
					}), 128))])]),
					_: 1
				}, 8, ["editor"])) : createCommentVNode("", true),
				$data.editor ? (openBlock(), createBlock(_component_floating_menu, {
					key: 1,
					editor: $data.editor,
					"should-show": $options.shouldShowSetButton,
					"is-showing": $data.showAddSetButton,
					onShown: _cache[0] || (_cache[0] = ($event) => $data.showAddSetButton = true),
					onHidden: _cache[1] || (_cache[1] = ($event) => $data.showAddSetButton = false)
				}, {
					default: withCtx(({ y }) => [$data.showAddSetButton ? (openBlock(), createBlock(_component_set_picker, {
						key: 0,
						ref: "setPicker",
						sets: $options.groupConfigs,
						class: "bard-set-selector",
						"loading-set": $data.loadingSet,
						onAdded: $options.addSet
					}, {
						trigger: withCtx(() => [createBaseVNode("div", {
							class: "absolute flex items-center gap-2 top-[-6px] z-1 -start-7 @lg/bard:-start-4.5 group",
							style: normalizeStyle({ transform: `translateY(${y}px)` })
						}, [withDirectives(createVNode(_component_ui_button, {
							icon: "plus",
							size: "sm",
							"aria-label": _ctx.__("Add Set")
						}, null, 8, ["aria-label"]), [[_directive_tooltip, _ctx.__("Add Set")]]), withDirectives(createVNode(_component_ui_description, {
							text: _ctx.__("Type '/' to insert a set"),
							class: normalizeClass({ "ps-9": $data.fullScreenMode })
						}, null, 8, ["text", "class"]), [[vShow, $options.shouldShowAddSetHelperText]])], 4)]),
						_: 2
					}, 1032, [
						"sets",
						"loading-set",
						"onAdded"
					])) : createCommentVNode("", true)]),
					_: 1
				}, 8, [
					"editor",
					"should-show",
					"is-showing"
				])) : createCommentVNode("", true),
				$data.initError ? (openBlock(), createElementBlock("div", {
					key: 2,
					class: "bard-error",
					textContent: toDisplayString($data.initError)
				}, null, 8, _hoisted_6)) : createCommentVNode("", true),
				createVNode(_component_editor_content, {
					editor: $data.editor,
					id: _ctx.fieldId
				}, null, 8, ["editor", "id"])
			], 2),
			$data.editor && (_ctx.config.reading_time || _ctx.config.character_limit || _ctx.config.word_count) ? (openBlock(), createElementBlock("div", _hoisted_7, [_ctx.config.reading_time ? (openBlock(), createElementBlock("div", _hoisted_8, toDisplayString($options.readingTime) + " " + toDisplayString(_ctx.__("Reading Time")), 1)) : (openBlock(), createElementBlock("div", _hoisted_9)), _ctx.config.character_limit || _ctx.config.word_count ? (openBlock(), createElementBlock("div", {
				key: 2,
				textContent: toDisplayString($options.characterAndWordCountText)
			}, null, 8, _hoisted_10)) : createCommentVNode("", true)])) : createCommentVNode("", true)
		], 34)], 2)], 2)]),
		_: 1
	}, 8, ["disabled", "provide"]);
}
var BardFieldtype_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "BardFieldtype.vue"]]);
//#endregion
export { BardFieldtype_default as default };
