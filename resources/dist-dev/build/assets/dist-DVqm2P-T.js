import { n as __exportAll$1 } from "./rolldown-runtime-B-1-B7_t.js";
import { c as NodeRange, f as Schema, i as Fragment$1, n as DOMParser, p as Slice, r as DOMSerializer, s as Node$1 } from "./dist-D1jI0QqO.js";
import { a as PluginKey, b as liftTarget, c as TextSelection, d as RemoveMarkStep, f as ReplaceAroundStep, g as canSplit, h as canJoin, i as Plugin, m as Transform, n as EditorState, o as Selection, p as ReplaceStep, r as NodeSelection, t as AllSelection, v as findWrapping, x as replaceStep, y as joinPoint } from "./dist-BGVo02xK.js";
import { i as EditorView, n as Decoration$1, r as DecorationSet } from "./view-DKtc-YdG.js";
//#region node_modules/@tiptap/core/dist/rolldown-runtime-D7D4PA-g.js
var __defProp = Object.defineProperty;
var __exportAll = (all, no_symbols) => {
	let target = {};
	for (var name in all) __defProp(target, name, {
		get: all[name],
		enumerable: true
	});
	if (!no_symbols) __defProp(target, Symbol.toStringTag, { value: "Module" });
	return target;
};
//#endregion
//#region node_modules/prosemirror-commands/dist/index.js
/**
Delete the selection, if there is one.
*/
var deleteSelection$1 = (state, dispatch) => {
	if (state.selection.empty) return false;
	if (dispatch) dispatch(state.tr.deleteSelection().scrollIntoView());
	return true;
};
function atBlockStart(state, view) {
	let { $cursor } = state.selection;
	if (!$cursor || (view ? !view.endOfTextblock("backward", state) : $cursor.parentOffset > 0)) return null;
	return $cursor;
}
/**
If the selection is empty and at the start of a textblock, try to
reduce the distance between that block and the one before it—if
there's a block directly before it that can be joined, join them.
If not, try to move the selected block closer to the next one in
the document structure by lifting it out of its parent or moving it
into a parent of the previous block. Will use the view for accurate
(bidi-aware) start-of-textblock detection if given.
*/
var joinBackward = (state, dispatch, view) => {
	let $cursor = atBlockStart(state, view);
	if (!$cursor) return false;
	let $cut = findCutBefore($cursor);
	if (!$cut) {
		let range = $cursor.blockRange(), target = range && liftTarget(range);
		if (target == null) return false;
		if (dispatch) dispatch(state.tr.lift(range, target).scrollIntoView());
		return true;
	}
	let before = $cut.nodeBefore;
	if (deleteBarrier(state, $cut, dispatch, -1)) return true;
	if ($cursor.parent.content.size == 0 && (textblockAt(before, "end") || NodeSelection.isSelectable(before))) for (let depth = $cursor.depth;; depth--) {
		let delStep = replaceStep(state.doc, $cursor.before(depth), $cursor.after(depth), Slice.empty);
		if (delStep && delStep.slice.size < delStep.to - delStep.from) {
			if (dispatch) {
				let tr = state.tr.step(delStep);
				tr.setSelection(textblockAt(before, "end") ? Selection.findFrom(tr.doc.resolve(tr.mapping.map($cut.pos, -1)), -1) : NodeSelection.create(tr.doc, $cut.pos - before.nodeSize));
				dispatch(tr.scrollIntoView());
			}
			return true;
		}
		if (depth == 1 || $cursor.node(depth - 1).childCount > 1) break;
	}
	if (before.isAtom && $cut.depth == $cursor.depth - 1) {
		if (dispatch) dispatch(state.tr.delete($cut.pos - before.nodeSize, $cut.pos).scrollIntoView());
		return true;
	}
	return false;
};
/**
A more limited form of [`joinBackward`](https://prosemirror.net/docs/ref/#commands.joinBackward)
that only tries to join the current textblock to the one before
it, if the cursor is at the start of a textblock.
*/
var joinTextblockBackward = (state, dispatch, view) => {
	let $cursor = atBlockStart(state, view);
	if (!$cursor) return false;
	let $cut = findCutBefore($cursor);
	return $cut ? joinTextblocksAround(state, $cut, dispatch) : false;
};
/**
A more limited form of [`joinForward`](https://prosemirror.net/docs/ref/#commands.joinForward)
that only tries to join the current textblock to the one after
it, if the cursor is at the end of a textblock.
*/
var joinTextblockForward = (state, dispatch, view) => {
	let $cursor = atBlockEnd(state, view);
	if (!$cursor) return false;
	let $cut = findCutAfter($cursor);
	return $cut ? joinTextblocksAround(state, $cut, dispatch) : false;
};
function joinTextblocksAround(state, $cut, dispatch) {
	let beforeText = $cut.nodeBefore, beforePos = $cut.pos - 1;
	for (; !beforeText.isTextblock; beforePos--) {
		if (beforeText.type.spec.isolating) return false;
		let child = beforeText.lastChild;
		if (!child) return false;
		beforeText = child;
	}
	let afterText = $cut.nodeAfter, afterPos = $cut.pos + 1;
	for (; !afterText.isTextblock; afterPos++) {
		if (afterText.type.spec.isolating) return false;
		let child = afterText.firstChild;
		if (!child) return false;
		afterText = child;
	}
	let step = replaceStep(state.doc, beforePos, afterPos, Slice.empty);
	if (!step || step.from != beforePos || step instanceof ReplaceStep && step.slice.size >= afterPos - beforePos) return false;
	if (dispatch) {
		let tr = state.tr.step(step);
		tr.setSelection(TextSelection.create(tr.doc, beforePos));
		dispatch(tr.scrollIntoView());
	}
	return true;
}
function textblockAt(node, side, only = false) {
	for (let scan = node; scan; scan = side == "start" ? scan.firstChild : scan.lastChild) {
		if (scan.isTextblock) return true;
		if (only && scan.childCount != 1) return false;
	}
	return false;
}
/**
When the selection is empty and at the start of a textblock, select
the node before that textblock, if possible. This is intended to be
bound to keys like backspace, after
[`joinBackward`](https://prosemirror.net/docs/ref/#commands.joinBackward) or other deleting
commands, as a fall-back behavior when the schema doesn't allow
deletion at the selected point.
*/
var selectNodeBackward = (state, dispatch, view) => {
	let { $head, empty } = state.selection, $cut = $head;
	if (!empty) return false;
	if ($head.parent.isTextblock) {
		if (view ? !view.endOfTextblock("backward", state) : $head.parentOffset > 0) return false;
		$cut = findCutBefore($head);
	}
	let node = $cut && $cut.nodeBefore;
	if (!node || !NodeSelection.isSelectable(node)) return false;
	if (dispatch) dispatch(state.tr.setSelection(NodeSelection.create(state.doc, $cut.pos - node.nodeSize)).scrollIntoView());
	return true;
};
function findCutBefore($pos) {
	if (!$pos.parent.type.spec.isolating) for (let i = $pos.depth - 1; i >= 0; i--) {
		if ($pos.index(i) > 0) return $pos.doc.resolve($pos.before(i + 1));
		if ($pos.node(i).type.spec.isolating) break;
	}
	return null;
}
function atBlockEnd(state, view) {
	let { $cursor } = state.selection;
	if (!$cursor || (view ? !view.endOfTextblock("forward", state) : $cursor.parentOffset < $cursor.parent.content.size)) return null;
	return $cursor;
}
/**
If the selection is empty and the cursor is at the end of a
textblock, try to reduce or remove the boundary between that block
and the one after it, either by joining them or by moving the other
block closer to this one in the tree structure. Will use the view
for accurate start-of-textblock detection if given.
*/
var joinForward = (state, dispatch, view) => {
	let $cursor = atBlockEnd(state, view);
	if (!$cursor) return false;
	let $cut = findCutAfter($cursor);
	if (!$cut) return false;
	let after = $cut.nodeAfter;
	if (deleteBarrier(state, $cut, dispatch, 1)) return true;
	if ($cursor.parent.content.size == 0 && (textblockAt(after, "start") || NodeSelection.isSelectable(after))) {
		let delStep = replaceStep(state.doc, $cursor.before(), $cursor.after(), Slice.empty);
		if (delStep && delStep.slice.size < delStep.to - delStep.from) {
			if (dispatch) {
				let tr = state.tr.step(delStep);
				tr.setSelection(textblockAt(after, "start") ? Selection.findFrom(tr.doc.resolve(tr.mapping.map($cut.pos)), 1) : NodeSelection.create(tr.doc, tr.mapping.map($cut.pos)));
				dispatch(tr.scrollIntoView());
			}
			return true;
		}
	}
	if (after.isAtom && $cut.depth == $cursor.depth - 1) {
		if (dispatch) dispatch(state.tr.delete($cut.pos, $cut.pos + after.nodeSize).scrollIntoView());
		return true;
	}
	return false;
};
/**
When the selection is empty and at the end of a textblock, select
the node coming after that textblock, if possible. This is intended
to be bound to keys like delete, after
[`joinForward`](https://prosemirror.net/docs/ref/#commands.joinForward) and similar deleting
commands, to provide a fall-back behavior when the schema doesn't
allow deletion at the selected point.
*/
var selectNodeForward = (state, dispatch, view) => {
	let { $head, empty } = state.selection, $cut = $head;
	if (!empty) return false;
	if ($head.parent.isTextblock) {
		if (view ? !view.endOfTextblock("forward", state) : $head.parentOffset < $head.parent.content.size) return false;
		$cut = findCutAfter($head);
	}
	let node = $cut && $cut.nodeAfter;
	if (!node || !NodeSelection.isSelectable(node)) return false;
	if (dispatch) dispatch(state.tr.setSelection(NodeSelection.create(state.doc, $cut.pos)).scrollIntoView());
	return true;
};
function findCutAfter($pos) {
	if (!$pos.parent.type.spec.isolating) for (let i = $pos.depth - 1; i >= 0; i--) {
		let parent = $pos.node(i);
		if ($pos.index(i) + 1 < parent.childCount) return $pos.doc.resolve($pos.after(i + 1));
		if (parent.type.spec.isolating) break;
	}
	return null;
}
/**
Join the selected block or, if there is a text selection, the
closest ancestor block of the selection that can be joined, with
the sibling above it.
*/
var joinUp = (state, dispatch) => {
	let sel = state.selection, nodeSel = sel instanceof NodeSelection, point;
	if (nodeSel) {
		if (sel.node.isTextblock || !canJoin(state.doc, sel.from)) return false;
		point = sel.from;
	} else {
		point = joinPoint(state.doc, sel.from, -1);
		if (point == null) return false;
	}
	if (dispatch) {
		let tr = state.tr.join(point);
		if (nodeSel) tr.setSelection(NodeSelection.create(tr.doc, point - state.doc.resolve(point).nodeBefore.nodeSize));
		dispatch(tr.scrollIntoView());
	}
	return true;
};
/**
Join the selected block, or the closest ancestor of the selection
that can be joined, with the sibling after it.
*/
var joinDown = (state, dispatch) => {
	let sel = state.selection, point;
	if (sel instanceof NodeSelection) {
		if (sel.node.isTextblock || !canJoin(state.doc, sel.to)) return false;
		point = sel.to;
	} else {
		point = joinPoint(state.doc, sel.to, 1);
		if (point == null) return false;
	}
	if (dispatch) dispatch(state.tr.join(point).scrollIntoView());
	return true;
};
/**
Lift the selected block, or the closest ancestor block of the
selection that can be lifted, out of its parent node.
*/
var lift = (state, dispatch) => {
	let { $from, $to } = state.selection;
	let range = $from.blockRange($to), target = range && liftTarget(range);
	if (target == null) return false;
	if (dispatch) dispatch(state.tr.lift(range, target).scrollIntoView());
	return true;
};
/**
If the selection is in a node whose type has a truthy
[`code`](https://prosemirror.net/docs/ref/#model.NodeSpec.code) property in its spec, replace the
selection with a newline character.
*/
var newlineInCode = (state, dispatch) => {
	let { $head, $anchor } = state.selection;
	if (!$head.parent.type.spec.code || !$head.sameParent($anchor)) return false;
	if (dispatch) dispatch(state.tr.insertText("\n").scrollIntoView());
	return true;
};
function defaultBlockAt$1(match) {
	for (let i = 0; i < match.edgeCount; i++) {
		let { type } = match.edge(i);
		if (type.isTextblock && !type.hasRequiredAttrs()) return type;
	}
	return null;
}
/**
When the selection is in a node with a truthy
[`code`](https://prosemirror.net/docs/ref/#model.NodeSpec.code) property in its spec, create a
default block after the code block, and move the cursor there.
*/
var exitCode = (state, dispatch) => {
	let { $head, $anchor } = state.selection;
	if (!$head.parent.type.spec.code || !$head.sameParent($anchor)) return false;
	let above = $head.node(-1), after = $head.indexAfter(-1), type = defaultBlockAt$1(above.contentMatchAt(after));
	if (!type || !above.canReplaceWith(after, after, type)) return false;
	if (dispatch) {
		let pos = $head.after(), tr = state.tr.replaceWith(pos, pos, type.createAndFill());
		tr.setSelection(Selection.near(tr.doc.resolve(pos), 1));
		dispatch(tr.scrollIntoView());
	}
	return true;
};
/**
If a block node is selected, create an empty paragraph before (if
it is its parent's first child) or after it.
*/
var createParagraphNear = (state, dispatch) => {
	let sel = state.selection, { $from, $to } = sel;
	if (sel instanceof AllSelection || $from.parent.inlineContent || $to.parent.inlineContent) return false;
	let type = defaultBlockAt$1($to.parent.contentMatchAt($to.indexAfter()));
	if (!type || !type.isTextblock) return false;
	if (dispatch) {
		let side = (!$from.parentOffset && $to.index() < $to.parent.childCount ? $from : $to).pos;
		let tr = state.tr.insert(side, type.createAndFill());
		tr.setSelection(TextSelection.create(tr.doc, side + 1));
		dispatch(tr.scrollIntoView());
	}
	return true;
};
/**
If the cursor is in an empty textblock that can be lifted, lift the
block.
*/
var liftEmptyBlock = (state, dispatch) => {
	let { $cursor } = state.selection;
	if (!$cursor || $cursor.parent.content.size) return false;
	if ($cursor.depth > 1 && $cursor.after() != $cursor.end(-1)) {
		let before = $cursor.before();
		if (canSplit(state.doc, before)) {
			if (dispatch) dispatch(state.tr.split(before).scrollIntoView());
			return true;
		}
	}
	let range = $cursor.blockRange(), target = range && liftTarget(range);
	if (target == null) return false;
	if (dispatch) dispatch(state.tr.lift(range, target).scrollIntoView());
	return true;
};
/**
Create a variant of [`splitBlock`](https://prosemirror.net/docs/ref/#commands.splitBlock) that uses
a custom function to determine the type of the newly split off block.
*/
function splitBlockAs(splitNode) {
	return (state, dispatch) => {
		if (state.selection instanceof NodeSelection && state.selection.node.isBlock) {
			let { $from } = state.selection;
			if (!$from.parentOffset || !canSplit(state.doc, $from.pos)) return false;
			if (dispatch) dispatch(state.tr.split($from.pos).scrollIntoView());
			return true;
		}
		if (!state.selection.$from.depth) return false;
		let tr = state.tr;
		if (!state.selection.empty && (state.selection instanceof TextSelection || state.selection instanceof AllSelection)) tr.deleteSelection();
		let { $from } = tr.selection, mapFrom = tr.steps.length;
		let types = [];
		let splitDepth, deflt, atEnd = false, atStart = false;
		for (let d = $from.depth;; d--) if ($from.node(d).isBlock) {
			atEnd = $from.end(d) == $from.pos + ($from.depth - d);
			atStart = $from.start(d) == $from.pos - ($from.depth - d);
			deflt = defaultBlockAt$1($from.node(d - 1).contentMatchAt($from.indexAfter(d - 1)));
			let splitType = splitNode && splitNode($from.parent, atEnd, $from);
			types.unshift(splitType || (atEnd && deflt ? { type: deflt } : null));
			splitDepth = d;
			break;
		} else {
			if (d == 1) return false;
			types.unshift(null);
		}
		let splitPos = $from.pos;
		let can = canSplit(tr.doc, splitPos, types.length, types);
		if (!can) {
			types[0] = deflt ? { type: deflt } : null;
			can = canSplit(tr.doc, splitPos, types.length, types);
		}
		if (!can) return false;
		tr.split(splitPos, types.length, types);
		if (!atEnd && atStart && $from.node(splitDepth).type != deflt) {
			let mapping = tr.mapping.slice(mapFrom);
			let first = mapping.map($from.before(splitDepth)), $first = tr.doc.resolve(first);
			if (deflt && $from.node(splitDepth - 1).canReplaceWith($first.index(), $first.index() + 1, deflt)) tr.setNodeMarkup(mapping.map($from.before(splitDepth)), deflt);
		}
		if (dispatch) dispatch(tr.scrollIntoView());
		return true;
	};
}
/**
Split the parent block of the selection. If the selection is a text
selection, also delete its content.
*/
var splitBlock$1 = splitBlockAs();
/**
Move the selection to the node wrapping the current selection, if
any. (Will not select the document node.)
*/
var selectParentNode = (state, dispatch) => {
	let { $from, to } = state.selection, pos;
	let same = $from.sharedDepth(to);
	if (same == 0) return false;
	pos = $from.before(same);
	if (dispatch) dispatch(state.tr.setSelection(NodeSelection.create(state.doc, pos)));
	return true;
};
/**
Select the whole document.
*/
var selectAll$1 = (state, dispatch) => {
	if (dispatch) dispatch(state.tr.setSelection(new AllSelection(state.doc)));
	return true;
};
function joinMaybeClear(state, $pos, dispatch) {
	let before = $pos.nodeBefore, after = $pos.nodeAfter, index = $pos.index();
	if (!before || !after || !before.type.compatibleContent(after.type)) return false;
	if (!before.content.size && $pos.parent.canReplace(index - 1, index)) {
		if (dispatch) dispatch(state.tr.delete($pos.pos - before.nodeSize, $pos.pos).scrollIntoView());
		return true;
	}
	if (!$pos.parent.canReplace(index, index + 1) || !(after.isTextblock || canJoin(state.doc, $pos.pos))) return false;
	if (dispatch) dispatch(state.tr.join($pos.pos).scrollIntoView());
	return true;
}
function deleteBarrier(state, $cut, dispatch, dir) {
	let before = $cut.nodeBefore, after = $cut.nodeAfter, conn, match;
	let isolated = before.type.spec.isolating || after.type.spec.isolating;
	if (!isolated && joinMaybeClear(state, $cut, dispatch)) return true;
	let canDelAfter = !isolated && $cut.parent.canReplace($cut.index(), $cut.index() + 1);
	if (canDelAfter && (conn = (match = before.contentMatchAt(before.childCount)).findWrapping(after.type)) && match.matchType(conn[0] || after.type).validEnd) {
		if (dispatch) {
			let end = $cut.pos + after.nodeSize, wrap = Fragment$1.empty;
			for (let i = conn.length - 1; i >= 0; i--) wrap = Fragment$1.from(conn[i].create(null, wrap));
			wrap = Fragment$1.from(before.copy(wrap));
			let tr = state.tr.step(new ReplaceAroundStep($cut.pos - 1, end, $cut.pos, end, new Slice(wrap, 1, 0), conn.length, true));
			let $joinAt = tr.doc.resolve(end + 2 * conn.length);
			if ($joinAt.nodeAfter && $joinAt.nodeAfter.type == before.type && canJoin(tr.doc, $joinAt.pos)) tr.join($joinAt.pos);
			dispatch(tr.scrollIntoView());
		}
		return true;
	}
	let selAfter = after.type.spec.isolating || dir > 0 && isolated ? null : Selection.findFrom($cut, 1);
	let range = selAfter && selAfter.$from.blockRange(selAfter.$to), target = range && liftTarget(range);
	if (target != null && target >= $cut.depth) {
		if (dispatch) dispatch(state.tr.lift(range, target).scrollIntoView());
		return true;
	}
	if (canDelAfter && textblockAt(after, "start", true) && textblockAt(before, "end")) {
		let at = before, wrap = [];
		for (;;) {
			wrap.push(at);
			if (at.isTextblock) break;
			at = at.lastChild;
		}
		let afterText = after, afterDepth = 1;
		for (; !afterText.isTextblock; afterText = afterText.firstChild) afterDepth++;
		if (at.canReplace(at.childCount, at.childCount, afterText.content)) {
			if (dispatch) {
				let end = Fragment$1.empty;
				for (let i = wrap.length - 1; i >= 0; i--) end = Fragment$1.from(wrap[i].copy(end));
				dispatch(state.tr.step(new ReplaceAroundStep($cut.pos - wrap.length, $cut.pos + after.nodeSize, $cut.pos + afterDepth, $cut.pos + after.nodeSize - afterDepth, new Slice(end, wrap.length, 0), 0, true)).scrollIntoView());
			}
			return true;
		}
	}
	return false;
}
function selectTextblockSide(side) {
	return function(state, dispatch) {
		let sel = state.selection, $pos = side < 0 ? sel.$from : sel.$to;
		let depth = $pos.depth;
		while ($pos.node(depth).isInline) {
			if (!depth) return false;
			depth--;
		}
		if (!$pos.node(depth).isTextblock) return false;
		if (dispatch) dispatch(state.tr.setSelection(TextSelection.create(state.doc, side < 0 ? $pos.start(depth) : $pos.end(depth))));
		return true;
	};
}
/**
Moves the cursor to the start of current text block.
*/
var selectTextblockStart = selectTextblockSide(-1);
/**
Moves the cursor to the end of current text block.
*/
var selectTextblockEnd = selectTextblockSide(1);
/**
Wrap the selection in a node of the given type with the given
attributes.
*/
function wrapIn(nodeType, attrs = null) {
	return function(state, dispatch) {
		let { $from, $to } = state.selection;
		let range = $from.blockRange($to), wrapping = range && findWrapping(range, nodeType, attrs);
		if (!wrapping) return false;
		if (dispatch) dispatch(state.tr.wrap(range, wrapping).scrollIntoView());
		return true;
	};
}
/**
Returns a command that tries to set the selected textblocks to the
given node type with the given attributes.
*/
function setBlockType(nodeType, attrs = null) {
	return function(state, dispatch) {
		let applicable = false;
		for (let i = 0; i < state.selection.ranges.length && !applicable; i++) {
			let { $from: { pos: from }, $to: { pos: to } } = state.selection.ranges[i];
			state.doc.nodesBetween(from, to, (node, pos) => {
				if (applicable) return false;
				if (!node.isTextblock || node.hasMarkup(nodeType, attrs)) return;
				if (node.type == nodeType) applicable = true;
				else {
					let $pos = state.doc.resolve(pos), index = $pos.index();
					applicable = $pos.parent.canReplaceWith(index, index + 1, nodeType);
				}
			});
		}
		if (!applicable) return false;
		if (dispatch) {
			let tr = state.tr;
			for (let i = 0; i < state.selection.ranges.length; i++) {
				let { $from: { pos: from }, $to: { pos: to } } = state.selection.ranges[i];
				tr.setBlockType(from, to, nodeType, attrs);
			}
			dispatch(tr.scrollIntoView());
		}
		return true;
	};
}
/**
Combine a number of command functions into a single function (which
calls them one by one until one returns true).
*/
function chainCommands(...commands) {
	return function(state, dispatch, view) {
		for (let i = 0; i < commands.length; i++) if (commands[i](state, dispatch, view)) return true;
		return false;
	};
}
var backspace = chainCommands(deleteSelection$1, joinBackward, selectNodeBackward);
var del = chainCommands(deleteSelection$1, joinForward, selectNodeForward);
/**
A basic keymap containing bindings not specific to any schema.
Binds the following keys (when multiple commands are listed, they
are chained with [`chainCommands`](https://prosemirror.net/docs/ref/#commands.chainCommands)):

* **Enter** to `newlineInCode`, `createParagraphNear`, `liftEmptyBlock`, `splitBlock`
* **Mod-Enter** to `exitCode`
* **Backspace** and **Mod-Backspace** to `deleteSelection`, `joinBackward`, `selectNodeBackward`
* **Delete** and **Mod-Delete** to `deleteSelection`, `joinForward`, `selectNodeForward`
* **Mod-Delete** to `deleteSelection`, `joinForward`, `selectNodeForward`
* **Mod-a** to `selectAll`
*/
var pcBaseKeymap = {
	"Enter": chainCommands(newlineInCode, createParagraphNear, liftEmptyBlock, splitBlock$1),
	"Mod-Enter": exitCode,
	"Backspace": backspace,
	"Mod-Backspace": backspace,
	"Shift-Backspace": backspace,
	"Delete": del,
	"Mod-Delete": del,
	"Mod-a": selectAll$1
};
/**
A copy of `pcBaseKeymap` that also binds **Ctrl-h** like Backspace,
**Ctrl-d** like Delete, **Alt-Backspace** like Ctrl-Backspace, and
**Ctrl-Alt-Backspace**, **Alt-Delete**, and **Alt-d** like
Ctrl-Delete.
*/
var macBaseKeymap = {
	"Ctrl-h": pcBaseKeymap["Backspace"],
	"Alt-Backspace": pcBaseKeymap["Mod-Backspace"],
	"Ctrl-d": pcBaseKeymap["Delete"],
	"Ctrl-Alt-Backspace": pcBaseKeymap["Mod-Delete"],
	"Alt-Delete": pcBaseKeymap["Mod-Delete"],
	"Alt-d": pcBaseKeymap["Mod-Delete"],
	"Ctrl-a": selectTextblockStart,
	"Ctrl-e": selectTextblockEnd
};
for (let key in pcBaseKeymap) macBaseKeymap[key] = pcBaseKeymap[key];
typeof navigator != "undefined" ? /Mac|iP(hone|[oa]d)/.test(navigator.platform) : typeof os != "undefined" && os.platform && os.platform();
//#endregion
//#region node_modules/prosemirror-schema-list/dist/index.js
/**
Returns a command function that wraps the selection in a list with
the given type an attributes. If `dispatch` is null, only return a
value to indicate whether this is possible, but don't actually
perform the change.
*/
function wrapInList(listType, attrs = null) {
	return function(state, dispatch) {
		let { $from, $to } = state.selection;
		let range = $from.blockRange($to);
		if (!range) return false;
		let tr = dispatch ? state.tr : null;
		if (!wrapRangeInList(tr, range, listType, attrs)) return false;
		if (dispatch) dispatch(tr.scrollIntoView());
		return true;
	};
}
/**
Try to wrap the given node range in a list of the given type.
Return `true` when this is possible, `false` otherwise. When `tr`
is non-null, the wrapping is added to that transaction. When it is
`null`, the function only queries whether the wrapping is
possible.
*/
function wrapRangeInList(tr, range, listType, attrs = null) {
	let doJoin = false, outerRange = range, doc = range.$from.doc;
	if (range.depth >= 2 && range.$from.node(range.depth - 1).type.compatibleContent(listType) && range.startIndex == 0) {
		if (range.$from.index(range.depth - 1) == 0) return false;
		let $insert = doc.resolve(range.start - 2);
		outerRange = new NodeRange($insert, $insert, range.depth);
		if (range.endIndex < range.parent.childCount) range = new NodeRange(range.$from, doc.resolve(range.$to.end(range.depth)), range.depth);
		doJoin = true;
	}
	let wrap = findWrapping(outerRange, listType, attrs, range);
	if (!wrap) return false;
	if (tr) doWrapInList(tr, range, wrap, doJoin, listType);
	return true;
}
function doWrapInList(tr, range, wrappers, joinBefore, listType) {
	let content = Fragment$1.empty;
	for (let i = wrappers.length - 1; i >= 0; i--) content = Fragment$1.from(wrappers[i].type.create(wrappers[i].attrs, content));
	tr.step(new ReplaceAroundStep(range.start - (joinBefore ? 2 : 0), range.end, range.start, range.end, new Slice(content, 0, 0), wrappers.length, true));
	let found = 0;
	for (let i = 0; i < wrappers.length; i++) if (wrappers[i].type == listType) found = i + 1;
	let splitDepth = wrappers.length - found;
	let splitPos = range.start + wrappers.length - (joinBefore ? 2 : 0), parent = range.parent;
	for (let i = range.startIndex, e = range.endIndex, first = true; i < e; i++, first = false) {
		if (!first && canSplit(tr.doc, splitPos, splitDepth)) {
			tr.split(splitPos, splitDepth);
			splitPos += 2 * splitDepth;
		}
		splitPos += parent.child(i).nodeSize;
	}
	return tr;
}
/**
Create a command to lift the list item around the selection up into
a wrapping list.
*/
function liftListItem(itemType) {
	return function(state, dispatch) {
		let { $from, $to } = state.selection;
		let range = $from.blockRange($to, (node) => node.childCount > 0 && node.firstChild.type == itemType);
		if (!range) return false;
		if (!dispatch) return true;
		if ($from.node(range.depth - 1).type == itemType) return liftToOuterList(state, dispatch, itemType, range);
		else return liftOutOfList(state, dispatch, range);
	};
}
function liftToOuterList(state, dispatch, itemType, range) {
	let tr = state.tr, end = range.end, endOfList = range.$to.end(range.depth);
	if (end < endOfList) {
		tr.step(new ReplaceAroundStep(end - 1, endOfList, end, endOfList, new Slice(Fragment$1.from(itemType.create(null, range.parent.copy())), 1, 0), 1, true));
		range = new NodeRange(tr.doc.resolve(range.$from.pos), tr.doc.resolve(endOfList), range.depth);
	}
	const target = liftTarget(range);
	if (target == null) return false;
	tr.lift(range, target);
	let $after = tr.doc.resolve(tr.mapping.map(end, -1) - 1);
	if (canJoin(tr.doc, $after.pos) && $after.nodeBefore.type == $after.nodeAfter.type) tr.join($after.pos);
	dispatch(tr.scrollIntoView());
	return true;
}
function liftOutOfList(state, dispatch, range) {
	let tr = state.tr, list = range.parent;
	for (let pos = range.end, i = range.endIndex - 1, e = range.startIndex; i > e; i--) {
		pos -= list.child(i).nodeSize;
		tr.delete(pos - 1, pos + 1);
	}
	let $start = tr.doc.resolve(range.start), item = $start.nodeAfter;
	if (tr.mapping.map(range.end) != range.start + $start.nodeAfter.nodeSize) return false;
	let atStart = range.startIndex == 0, atEnd = range.endIndex == list.childCount;
	let parent = $start.node(-1), indexBefore = $start.index(-1);
	if (!parent.canReplace(indexBefore + (atStart ? 0 : 1), indexBefore + 1, item.content.append(atEnd ? Fragment$1.empty : Fragment$1.from(list)))) return false;
	let start = $start.pos, end = start + item.nodeSize;
	tr.step(new ReplaceAroundStep(start - (atStart ? 1 : 0), end + (atEnd ? 1 : 0), start + 1, end - 1, new Slice((atStart ? Fragment$1.empty : Fragment$1.from(list.copy(Fragment$1.empty))).append(atEnd ? Fragment$1.empty : Fragment$1.from(list.copy(Fragment$1.empty))), atStart ? 0 : 1, atEnd ? 0 : 1), atStart ? 0 : 1));
	dispatch(tr.scrollIntoView());
	return true;
}
/**
Create a command to sink the list item around the selection down
into an inner list.
*/
function sinkListItem(itemType) {
	return function(state, dispatch) {
		let { $from, $to } = state.selection;
		let range = $from.blockRange($to, (node) => node.childCount > 0 && node.firstChild.type == itemType);
		if (!range) return false;
		let startIndex = range.startIndex;
		if (startIndex == 0) return false;
		let parent = range.parent, nodeBefore = parent.child(startIndex - 1);
		if (nodeBefore.type != itemType) return false;
		if (dispatch) {
			let nestedBefore = nodeBefore.lastChild && nodeBefore.lastChild.type == parent.type;
			let inner = Fragment$1.from(nestedBefore ? itemType.create() : null);
			let slice = new Slice(Fragment$1.from(itemType.create(null, Fragment$1.from(parent.type.create(null, inner)))), nestedBefore ? 3 : 1, 0);
			let before = range.start, after = range.end;
			dispatch(state.tr.step(new ReplaceAroundStep(before - (nestedBefore ? 3 : 1), after, before, after, slice, 1, true)).scrollIntoView());
		}
		return true;
	};
}
//#endregion
//#region node_modules/w3c-keyname/index.js
var base = {
	8: "Backspace",
	9: "Tab",
	10: "Enter",
	12: "NumLock",
	13: "Enter",
	16: "Shift",
	17: "Control",
	18: "Alt",
	20: "CapsLock",
	27: "Escape",
	32: " ",
	33: "PageUp",
	34: "PageDown",
	35: "End",
	36: "Home",
	37: "ArrowLeft",
	38: "ArrowUp",
	39: "ArrowRight",
	40: "ArrowDown",
	44: "PrintScreen",
	45: "Insert",
	46: "Delete",
	59: ";",
	61: "=",
	91: "Meta",
	92: "Meta",
	106: "*",
	107: "+",
	108: ",",
	109: "-",
	110: ".",
	111: "/",
	144: "NumLock",
	145: "ScrollLock",
	160: "Shift",
	161: "Shift",
	162: "Control",
	163: "Control",
	164: "Alt",
	165: "Alt",
	173: "-",
	186: ";",
	187: "=",
	188: ",",
	189: "-",
	190: ".",
	191: "/",
	192: "`",
	219: "[",
	220: "\\",
	221: "]",
	222: "'"
};
var shift = {
	48: ")",
	49: "!",
	50: "@",
	51: "#",
	52: "$",
	53: "%",
	54: "^",
	55: "&",
	56: "*",
	57: "(",
	59: ":",
	61: "+",
	173: "_",
	186: ":",
	187: "+",
	188: "<",
	189: "_",
	190: ">",
	191: "?",
	192: "~",
	219: "{",
	220: "|",
	221: "}",
	222: "\""
};
var mac$1 = typeof navigator != "undefined" && /Mac/.test(navigator.platform);
var ie = typeof navigator != "undefined" && /MSIE \d|Trident\/(?:[7-9]|\d{2,})\..*rv:(\d+)/.exec(navigator.userAgent);
for (var i = 0; i < 10; i++) base[48 + i] = base[96 + i] = String(i);
for (var i = 1; i <= 24; i++) base[i + 111] = "F" + i;
for (var i = 65; i <= 90; i++) {
	base[i] = String.fromCharCode(i + 32);
	shift[i] = String.fromCharCode(i);
}
for (var code in base) if (!shift.hasOwnProperty(code)) shift[code] = base[code];
function keyName(event) {
	var name = !(mac$1 && event.metaKey && event.shiftKey && !event.ctrlKey && !event.altKey || ie && event.shiftKey && event.key && event.key.length == 1 || event.key == "Unidentified") && event.key || (event.shiftKey ? shift : base)[event.keyCode] || event.key || "Unidentified";
	if (name == "Esc") name = "Escape";
	if (name == "Del") name = "Delete";
	if (name == "Left") name = "ArrowLeft";
	if (name == "Up") name = "ArrowUp";
	if (name == "Right") name = "ArrowRight";
	if (name == "Down") name = "ArrowDown";
	return name;
}
//#endregion
//#region node_modules/prosemirror-keymap/dist/index.js
var mac = typeof navigator != "undefined" && /Mac|iP(hone|[oa]d)/.test(navigator.platform);
var windows = typeof navigator != "undefined" && /Win/.test(navigator.platform);
function normalizeKeyName$1(name) {
	let parts = name.split(/-(?!$)/), result = parts[parts.length - 1];
	if (result == "Space") result = " ";
	let alt, ctrl, shift, meta;
	for (let i = 0; i < parts.length - 1; i++) {
		let mod = parts[i];
		if (/^(cmd|meta|m)$/i.test(mod)) meta = true;
		else if (/^a(lt)?$/i.test(mod)) alt = true;
		else if (/^(c|ctrl|control)$/i.test(mod)) ctrl = true;
		else if (/^s(hift)?$/i.test(mod)) shift = true;
		else if (/^mod$/i.test(mod)) if (mac) meta = true;
		else ctrl = true;
		else throw new Error("Unrecognized modifier name: " + mod);
	}
	if (alt) result = "Alt-" + result;
	if (ctrl) result = "Ctrl-" + result;
	if (meta) result = "Meta-" + result;
	if (shift) result = "Shift-" + result;
	return result;
}
function normalize(map) {
	let copy = Object.create(null);
	for (let prop in map) copy[normalizeKeyName$1(prop)] = map[prop];
	return copy;
}
function modifiers(name, event, shift = true) {
	if (event.altKey) name = "Alt-" + name;
	if (event.ctrlKey) name = "Ctrl-" + name;
	if (event.metaKey) name = "Meta-" + name;
	if (shift && event.shiftKey) name = "Shift-" + name;
	return name;
}
/**
Create a keymap plugin for the given set of bindings.

Bindings should map key names to [command](https://prosemirror.net/docs/ref/#commands)-style
functions, which will be called with `(EditorState, dispatch,
EditorView)` arguments, and should return true when they've handled
the key. Note that the view argument isn't part of the command
protocol, but can be used as an escape hatch if a binding needs to
directly interact with the UI.

Key names may be strings like `"Shift-Ctrl-Enter"`—a key
identifier prefixed with zero or more modifiers. Key identifiers
are based on the strings that can appear in
[`KeyEvent.key`](https:developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/key).
Use lowercase letters to refer to letter keys (or uppercase letters
if you want shift to be held). You may use `"Space"` as an alias
for the `" "` name.

Modifiers can be given in any order. `Shift-` (or `s-`), `Alt-` (or
`a-`), `Ctrl-` (or `c-` or `Control-`) and `Cmd-` (or `m-` or
`Meta-`) are recognized. For characters that are created by holding
shift, the `Shift-` prefix is implied, and should not be added
explicitly.

You can use `Mod-` as a shorthand for `Cmd-` on Mac and `Ctrl-` on
other platforms.

You can add multiple keymap plugins to an editor. The order in
which they appear determines their precedence (the ones early in
the array get to dispatch first).
*/
function keymap(bindings) {
	return new Plugin({ props: { handleKeyDown: keydownHandler(bindings) } });
}
/**
Given a set of bindings (using the same format as
[`keymap`](https://prosemirror.net/docs/ref/#keymap.keymap)), return a [keydown
handler](https://prosemirror.net/docs/ref/#view.EditorProps.handleKeyDown) that handles them.
*/
function keydownHandler(bindings) {
	let map = normalize(bindings);
	return function(view, event) {
		let name = keyName(event), baseName, direct = map[modifiers(name, event)];
		if (direct && direct(view.state, view.dispatch, view)) return true;
		if (name.length == 1 && name != " ") {
			if (event.shiftKey) {
				let noShift = map[modifiers(name, event, false)];
				if (noShift && noShift(view.state, view.dispatch, view)) return true;
			}
			if ((event.altKey || event.metaKey || event.ctrlKey) && !(windows && event.ctrlKey && event.altKey) && (baseName = base[event.keyCode]) && baseName != name) {
				let fromCode = map[modifiers(baseName, event)];
				if (fromCode && fromCode(view.state, view.dispatch, view)) return true;
			}
		}
		return false;
	};
}
//#endregion
//#region node_modules/@tiptap/core/dist/index.js
var dist_exports = /* @__PURE__ */ __exportAll$1({
	CommandManager: () => CommandManager,
	DECORATION_MANAGER_PLUGIN_KEY: () => DECORATION_MANAGER_PLUGIN_KEY,
	Decoration: () => Decoration,
	DecorationManager: () => DecorationManager,
	Editor: () => Editor,
	Extendable: () => Extendable,
	Extension: () => Extension,
	Fragment: () => Fragment,
	InlineDecoration: () => InlineDecoration,
	InputRule: () => InputRule,
	MappablePosition: () => MappablePosition,
	Mark: () => Mark,
	MarkView: () => MarkView,
	Node: () => Node,
	NodeDecoration: () => NodeDecoration,
	NodePos: () => NodePos,
	NodeView: () => NodeView,
	PasteRule: () => PasteRule,
	ResizableNodeView: () => ResizableNodeView,
	ResizableNodeview: () => ResizableNodeview,
	Tracker: () => Tracker,
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
	createElement: () => h,
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
	h: () => h,
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
	markdown: () => markdown_exports,
	marksEqual: () => marksEqual,
	mergeAttributes: () => mergeAttributes,
	mergeDeep: () => mergeDeep,
	minMax: () => minMax,
	nodeInputRule: () => nodeInputRule,
	nodePasteRule: () => nodePasteRule,
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
	wrappingInputRule: () => wrappingInputRule
});
/**
* Takes a Transaction & Editor State and turns it into a chainable state object
* @param config The transaction and state to create the chainable state from
* @returns A chainable Editor state object
*/
function createChainableState(config) {
	const { state, transaction } = config;
	let { selection } = transaction;
	let { doc } = transaction;
	let { storedMarks } = transaction;
	return {
		...state,
		apply: state.apply.bind(state),
		applyTransaction: state.applyTransaction.bind(state),
		plugins: state.plugins,
		schema: state.schema,
		reconfigure: state.reconfigure.bind(state),
		toJSON: state.toJSON.bind(state),
		get storedMarks() {
			return storedMarks;
		},
		get selection() {
			return selection;
		},
		get doc() {
			return doc;
		},
		get tr() {
			selection = transaction.selection;
			doc = transaction.doc;
			storedMarks = transaction.storedMarks;
			return transaction;
		}
	};
}
var CommandManager = class CommandManager {
	constructor(props) {
		this.editor = props.editor;
		this.rawCommands = this.editor.extensionManager.commands;
		this.customState = props.state;
	}
	get hasCustomState() {
		return !!this.customState;
	}
	get state() {
		return this.customState || this.editor.state;
	}
	get commands() {
		const { rawCommands, editor, state } = this;
		const { view } = editor;
		const { tr } = state;
		const props = this.buildProps(tr);
		return Object.fromEntries(Object.entries(rawCommands).map(([name, command]) => {
			const method = (...args) => {
				const callback = command(...args)(props);
				if (!tr.getMeta("preventDispatch") && !this.hasCustomState) view.dispatch(tr);
				return callback;
			};
			return [name, method];
		}));
	}
	get chain() {
		return () => this.createChain();
	}
	get can() {
		return () => this.createCan();
	}
	createChain(startTr, shouldDispatch = true) {
		const { rawCommands, editor, state } = this;
		const { view } = editor;
		const callbacks = [];
		const hasStartTransaction = !!startTr;
		const tr = startTr || state.tr;
		const run = () => {
			if (!hasStartTransaction && shouldDispatch && !tr.getMeta("preventDispatch") && !this.hasCustomState) view.dispatch(tr);
			return callbacks.every((callback) => callback === true);
		};
		const chain = {
			...Object.fromEntries(Object.entries(rawCommands).map(([name, command]) => {
				const chainedCommand = (...args) => {
					const props = this.buildProps(tr, shouldDispatch);
					const callback = command(...args)(props);
					callbacks.push(callback);
					return chain;
				};
				return [name, chainedCommand];
			})),
			run
		};
		return chain;
	}
	/**
	* Creates a chain that safely returns `false` when run.
	* @returns A non-dispatching command chain.
	* @example
	* const chain = CommandManager.createFakeChain()
	* chain.focus().run() // false
	*/
	static createFakeChain() {
		const chain = new Proxy({}, { get: (_target, property) => {
			if (property === "then") return;
			if (property === "run") return () => false;
			return () => chain;
		} });
		return chain;
	}
	createCan(startTr) {
		const { rawCommands, state } = this;
		const dispatch = false;
		const tr = startTr || state.tr;
		const props = this.buildProps(tr, dispatch);
		return {
			...Object.fromEntries(Object.entries(rawCommands).map(([name, command]) => {
				return [name, (...args) => command(...args)({
					...props,
					dispatch: void 0
				})];
			})),
			chain: () => this.createChain(tr, dispatch)
		};
	}
	/**
	* Creates capability checks that safely return `false`.
	* @returns A non-dispatching capability checker.
	* @example
	* const can = CommandManager.createFallbackCan()
	* can.focus() // false
	*/
	static createFallbackCan() {
		const chain = CommandManager.createFakeChain();
		return new Proxy({ chain: () => chain }, { get: (target, property) => {
			if (property === "then") return;
			if (property === "chain") return target.chain;
			return () => false;
		} });
	}
	buildProps(tr, shouldDispatch = true) {
		const { rawCommands, editor, state } = this;
		const { view } = editor;
		const props = {
			tr,
			editor,
			view,
			state: createChainableState({
				state,
				transaction: tr
			}),
			dispatch: shouldDispatch ? () => void 0 : void 0,
			chain: () => this.createChain(tr, shouldDispatch),
			can: () => this.createCan(tr),
			get commands() {
				return Object.fromEntries(Object.entries(rawCommands).map(([name, command]) => {
					return [name, (...args) => command(...args)(props)];
				}));
			}
		};
		return props;
	}
};
var blur = () => ({ editor, view }) => {
	requestAnimationFrame(() => {
		if (!editor.isDestroyed) {
			var _window;
			view.dom.blur();
			(_window = window) === null || _window === void 0 || (_window = _window.getSelection()) === null || _window === void 0 || _window.removeAllRanges();
		}
	});
	return true;
};
var clearContent = (emitUpdate = true) => ({ commands }) => {
	return commands.setContent("", { emitUpdate });
};
var clearNodes = () => ({ state, tr, dispatch }) => {
	const { selection } = tr;
	const { ranges } = selection;
	if (!dispatch) return true;
	ranges.forEach(({ $from, $to }) => {
		state.doc.nodesBetween($from.pos, $to.pos, (node, pos) => {
			if (node.type.isText) return;
			const { doc, mapping } = tr;
			const $mappedFrom = doc.resolve(mapping.map(pos));
			const $mappedTo = doc.resolve(mapping.map(pos + node.nodeSize));
			const nodeRange = $mappedFrom.blockRange($mappedTo);
			if (!nodeRange) return;
			const targetLiftDepth = liftTarget(nodeRange);
			if (node.type.isTextblock) {
				const { defaultType } = $mappedFrom.parent.contentMatchAt($mappedFrom.index());
				tr.setNodeMarkup(nodeRange.start, defaultType);
			}
			if (targetLiftDepth || targetLiftDepth === 0) tr.lift(nodeRange, targetLiftDepth);
		});
	});
	return true;
};
var command = (fn) => (props) => {
	return fn(props);
};
var createParagraphNear$1 = () => ({ state, dispatch }) => {
	return createParagraphNear(state, dispatch);
};
var cut = (originRange, targetPos) => ({ editor, tr }) => {
	const { state } = editor;
	const contentSlice = state.doc.slice(originRange.from, originRange.to);
	tr.deleteRange(originRange.from, originRange.to);
	const newPos = tr.mapping.map(targetPos);
	tr.insert(newPos, contentSlice.content);
	tr.setSelection(new TextSelection(tr.doc.resolve(Math.max(newPos - 1, 0))));
	return true;
};
var deleteCurrentNode = () => ({ tr, dispatch }) => {
	const { selection } = tr;
	const currentNode = selection.$anchor.node();
	if (currentNode.content.size > 0) return false;
	const $pos = tr.selection.$anchor;
	for (let depth = $pos.depth; depth > 0; depth -= 1) if ($pos.node(depth).type === currentNode.type) {
		if (dispatch) {
			const from = $pos.before(depth);
			const to = $pos.after(depth);
			tr.delete(from, to).scrollIntoView();
		}
		return true;
	}
	return false;
};
function getNodeType(nameOrType, schema) {
	if (typeof nameOrType === "string") {
		if (!schema.nodes[nameOrType]) throw Error(`There is no node type named '${nameOrType}'. Maybe you forgot to add the extension?`);
		return schema.nodes[nameOrType];
	}
	return nameOrType;
}
var deleteNode = (typeOrName) => ({ tr, state, dispatch }) => {
	const type = getNodeType(typeOrName, state.schema);
	const $pos = tr.selection.$anchor;
	for (let depth = $pos.depth; depth > 0; depth -= 1) if ($pos.node(depth).type === type) {
		if (dispatch) {
			const from = $pos.before(depth);
			const to = $pos.after(depth);
			tr.delete(from, to).scrollIntoView();
		}
		return true;
	}
	return false;
};
var deleteRange = (range) => ({ tr, dispatch }) => {
	const { from, to } = range;
	if (dispatch) tr.delete(from, to);
	return true;
};
/**
* Check if a node has text content based on its content specification.
* Returns true if the node's content spec matches text* or text+ patterns.
*/
var hasTextContent = (nodeSpec) => {
	if (!nodeSpec.content) return false;
	return /^text(\*|\+)/.test(nodeSpec.content);
};
/**
* Expand selection position for a specific side (left or right) to handle inline text nodes.
* This function checks if the position is within an inline node with text content and
* expands it to include the entire node boundaries for proper deletion.
* @param $pos - The resolved position to expand
* @param schema - The ProseMirror schema
* @param side - Which side to expand ('left' or 'right')
* @returns The expanded position for deletion
*/
var expandSelectionForSide = ($pos, schema, side) => {
	if (!$pos.parent.isInline) return $pos.pos;
	if (side === "left" && $pos.pos > $pos.start() || side === "right" && $pos.pos < $pos.end()) return $pos.pos;
	const parentContent = schema.nodes[$pos.parent.type.name].spec;
	if (!hasTextContent(parentContent)) return $pos.pos;
	return side === "left" ? $pos.start() - 1 : $pos.end() + 1;
};
/**
* Expand selection range to properly handle deletion of inline text nodes.
* Inline text nodes don't collapse correctly when text inside is deleted,
* so we need to expand the selection to include the entire node.
* See: https://code.haverbeke.berlin/prosemirror/prosemirror/issues/1365
*/
var expandSelectionForInlineText = ($from, $to, schema) => {
	return {
		from: expandSelectionForSide($from, schema, "left"),
		to: expandSelectionForSide($to, schema, "right")
	};
};
var deleteSelection = () => ({ state, dispatch }) => {
	if (state.selection.empty) return false;
	if (dispatch) {
		const tr = state.tr;
		const { ranges } = state.selection;
		const mapFrom = tr.steps.length;
		ranges.forEach((range) => {
			const mapping = tr.mapping.slice(mapFrom);
			const { from, to } = expandSelectionForInlineText(tr.doc.resolve(mapping.map(range.$from.pos)), tr.doc.resolve(mapping.map(range.$to.pos)), state.schema);
			tr.deleteRange(from, to);
		});
		if (!tr.selection.empty) tr.setSelection(TextSelection.near(tr.doc.resolve(tr.selection.from)));
		tr.scrollIntoView();
		dispatch(tr);
	}
	return true;
};
var enter = () => ({ commands }) => {
	return commands.keyboardShortcut("Enter");
};
var exitCode$1 = () => ({ state, dispatch }) => {
	return exitCode(state, dispatch);
};
function isRegExp(value) {
	return Object.prototype.toString.call(value) === "[object RegExp]";
}
/**
* Check if object1 includes object2
* @param object1 Object
* @param object2 Object
*/
function objectIncludes(object1, object2, options = { strict: true }) {
	const keys = Object.keys(object2);
	if (!keys.length) return true;
	return keys.every((key) => {
		if (options.strict) return object2[key] === object1[key];
		if (isRegExp(object2[key])) return object2[key].test(object1[key]);
		return object2[key] === object1[key];
	});
}
function findMarkInSet(marks, type, attributes = {}) {
	return marks.find((item) => {
		return item.type === type && objectIncludes(Object.fromEntries(Object.keys(attributes).map((k) => [k, item.attrs[k]])), attributes);
	});
}
function isMarkInSet(marks, type, attributes = {}) {
	return !!findMarkInSet(marks, type, attributes);
}
/**
* Get the range of a mark at a resolved position.
*/
function getMarkRange($pos, type, attributes) {
	if (!$pos || !type) return;
	let start = $pos.parent.childAfter($pos.parentOffset);
	if (!start.node || !start.node.marks.some((mark) => mark.type === type)) start = $pos.parent.childBefore($pos.parentOffset);
	if (!start.node || !start.node.marks.some((mark) => mark.type === type)) return;
	if (!attributes) {
		const firstMark = start.node.marks.find((mark) => mark.type === type);
		if (firstMark) attributes = firstMark.attrs;
	}
	if (!findMarkInSet([...start.node.marks], type, attributes)) return;
	let startIndex = start.index;
	let startPos = $pos.start() + start.offset;
	let endIndex = startIndex + 1;
	let endPos = startPos + start.node.nodeSize;
	while (startIndex > 0 && isMarkInSet([...$pos.parent.child(startIndex - 1).marks], type, attributes)) {
		startIndex -= 1;
		startPos -= $pos.parent.child(startIndex).nodeSize;
	}
	while (endIndex < $pos.parent.childCount && isMarkInSet([...$pos.parent.child(endIndex).marks], type, attributes)) {
		endPos += $pos.parent.child(endIndex).nodeSize;
		endIndex += 1;
	}
	return {
		from: startPos,
		to: endPos
	};
}
function getMarkType(nameOrType, schema) {
	if (typeof nameOrType === "string") {
		if (!schema.marks[nameOrType]) throw Error(`There is no mark type named '${nameOrType}'. Maybe you forgot to add the extension?`);
		return schema.marks[nameOrType];
	}
	return nameOrType;
}
var extendMarkRange = (typeOrName, attributes) => ({ tr, state, dispatch }) => {
	const type = getMarkType(typeOrName, state.schema);
	const { doc, selection } = tr;
	const { $from, from, to } = selection;
	if (dispatch) {
		const range = getMarkRange($from, type, attributes);
		if (range && range.from <= from && range.to >= to) {
			const newSelection = TextSelection.create(doc, range.from, range.to);
			tr.setSelection(newSelection);
		}
	}
	return true;
};
var first = (commands) => (props) => {
	const items = typeof commands === "function" ? commands(props) : commands;
	for (let i = 0; i < items.length; i += 1) if (items[i](props)) return true;
	return false;
};
function isTextSelection(value) {
	return value instanceof TextSelection;
}
function minMax(value = 0, min = 0, max = 0) {
	return Math.min(Math.max(value, min), max);
}
function resolveFocusPosition(doc, position = null) {
	if (!position) return null;
	const selectionAtStart = Selection.atStart(doc);
	const selectionAtEnd = Selection.atEnd(doc);
	if (position === "start" || position === true) return selectionAtStart;
	if (position === "end") return selectionAtEnd;
	const minPos = selectionAtStart.from;
	const maxPos = selectionAtEnd.to;
	if (position === "all") return TextSelection.create(doc, minMax(0, minPos, maxPos), minMax(doc.content.size, minPos, maxPos));
	return TextSelection.create(doc, minMax(position, minPos, maxPos), minMax(position, minPos, maxPos));
}
function isAndroid() {
	return ["Android"].includes(navigator.platform) || /android/i.test(navigator.userAgent);
}
function isiOS() {
	return [
		"iPad Simulator",
		"iPhone Simulator",
		"iPod Simulator",
		"iPad",
		"iPhone",
		"iPod"
	].includes(navigator.platform) || navigator.userAgent.includes("Mac") && "ontouchend" in document;
}
/**
* Detects if the current browser is Safari (but not iOS Safari or Chrome).
* @returns `true` if the browser is Safari, `false` otherwise.
* @example
* if (isSafari()) {
*   // Safari-specific handling
* }
*/
function isSafari() {
	return typeof navigator !== "undefined" ? /^((?!chrome|android).)*safari/i.test(navigator.userAgent) : false;
}
var focus = (position = null, options = {}) => ({ editor, view, tr, dispatch }) => {
	options = {
		scrollIntoView: true,
		...options
	};
	const delayedFocus = () => {
		if (isiOS() || isAndroid()) view.dom.focus();
		if (isSafari() && !isiOS() && !isAndroid()) view.dom.focus({ preventScroll: true });
		requestAnimationFrame(() => {
			if (!editor.isDestroyed) {
				view.focus();
				if (options === null || options === void 0 ? void 0 : options.scrollIntoView) editor.commands.scrollIntoView();
			}
		});
	};
	try {
		if (view.hasFocus() && position === null || position === false) return true;
	} catch {
		return false;
	}
	if (dispatch && position === null && !isTextSelection(editor.state.selection)) {
		delayedFocus();
		return true;
	}
	const selection = resolveFocusPosition(tr.doc, position) || editor.state.selection;
	const isSameSelection = editor.state.selection.eq(selection);
	if (dispatch) {
		if (!isSameSelection) tr.setSelection(selection);
		if (isSameSelection && tr.storedMarks) tr.setStoredMarks(tr.storedMarks);
		delayedFocus();
	}
	return true;
};
var forEach = (items, fn) => (props) => {
	return items.every((item, index) => fn(item, {
		...props,
		index
	}));
};
var insertContent = (value, options) => ({ tr, commands }) => {
	return commands.insertContentAt({
		from: tr.selection.from,
		to: tr.selection.to
	}, value, options);
};
var removeWhitespaces = (node) => {
	const children = node.childNodes;
	for (let i = children.length - 1; i >= 0; i -= 1) {
		const child = children[i];
		if (child.nodeType === 3 && child.nodeValue && /^(\n\s\s|\n)$/.test(child.nodeValue)) node.removeChild(child);
		else if (child.nodeType === 1) removeWhitespaces(child);
	}
	return node;
};
function elementFromString(value) {
	if (typeof window === "undefined") throw new Error("[tiptap error]: there is no window object available, so this function cannot be used");
	const wrappedValue = `<body>${value}</body>`;
	const html = new window.DOMParser().parseFromString(wrappedValue, "text/html").body;
	return removeWhitespaces(html);
}
/**
* Checks whether a value is already a ProseMirror node or fragment. Looks for the
* `nodesBetween` method, because `instanceof` fails when prosemirror-model is loaded twice.
* @param value Any value that could be passed as content
* @returns True for a node or a fragment, false for JSON, HTML and everything else
* @example ```js
* isProseMirrorContent(editor.state.doc)
* ```
*/
function isProseMirrorContent(value) {
	return typeof (value === null || value === void 0 ? void 0 : value.nodesBetween) === "function";
}
/**
* Takes a JSON or HTML content and creates a Prosemirror node or fragment from it.
* @param content The JSON or HTML content to create the node from
* @param schema The Prosemirror schema to use for the node
* @param options Options for the parser
* @returns The created Prosemirror node or fragment
*/
function createNodeFromContent(content, schema, options) {
	if (isProseMirrorContent(content)) return content;
	const isJSONContent = typeof content === "object" && content !== null;
	options = {
		slice: true,
		parseOptions: {},
		...options
	};
	const isTextContent = typeof content === "string";
	if (isJSONContent) try {
		if (Array.isArray(content) && content.length > 0) return Fragment$1.fromArray(content.map((item) => schema.nodeFromJSON(item)));
		const node = schema.nodeFromJSON(content);
		if (options.errorOnInvalidContent) node.check();
		return node;
	} catch (error) {
		if (options.errorOnInvalidContent) throw new Error("[tiptap error]: Invalid JSON content", { cause: error });
		console.warn("[tiptap warn]: Invalid content.", "Passed value:", content, "Error:", error);
		return createNodeFromContent("", schema, options);
	}
	if (isTextContent) {
		if (options.errorOnInvalidContent) {
			let hasInvalidContent = false;
			let invalidContent = "";
			const contentCheckSchema = new Schema({
				topNode: schema.spec.topNode,
				marks: schema.spec.marks,
				nodes: schema.spec.nodes.append({ __tiptap__private__unknown__catch__all__node: {
					content: "inline*",
					group: "block",
					parseDOM: [{
						tag: "*",
						getAttrs: (e) => {
							hasInvalidContent = true;
							invalidContent = typeof e === "string" ? e : e.outerHTML;
							return null;
						}
					}]
				} })
			});
			if (options.slice) DOMParser.fromSchema(contentCheckSchema).parseSlice(elementFromString(content), options.parseOptions);
			else DOMParser.fromSchema(contentCheckSchema).parse(elementFromString(content), options.parseOptions);
			if (options.errorOnInvalidContent && hasInvalidContent) throw new Error("[tiptap error]: Invalid HTML content", { cause: /* @__PURE__ */ new Error(`Invalid element found: ${invalidContent}`) });
		}
		const parser = DOMParser.fromSchema(schema);
		if (options.slice) return parser.parseSlice(elementFromString(content), options.parseOptions).content;
		return parser.parse(elementFromString(content), options.parseOptions);
	}
	return createNodeFromContent("", schema, options);
}
/**
* Checks whether a node or fragment is a fragment. Looks for a missing `type` field,
* because `instanceof` fails when prosemirror-model is loaded twice.
* @param nodeOrFragment A ProseMirror node or fragment
* @returns True for a fragment, false for a node
* @example ```js
* isFragment(editor.state.doc.content)
* ```
*/
function isFragment(nodeOrFragment) {
	return !("type" in nodeOrFragment);
}
function selectionToInsertionEnd(tr, startLen, bias) {
	const last = tr.steps.length - 1;
	if (last < startLen) return;
	const step = tr.steps[last];
	if (!(step instanceof ReplaceStep || step instanceof ReplaceAroundStep)) return;
	const map = tr.mapping.maps[last];
	let end = 0;
	map.forEach((_from, _to, _newFrom, newTo) => {
		if (end === 0) end = newTo;
	});
	tr.setSelection(Selection.near(tr.doc.resolve(end), bias));
}
var insertContentAt = (position, value, options) => ({ tr, dispatch, editor }) => {
	if (dispatch) {
		options = {
			parseOptions: editor.options.parseOptions,
			updateSelection: true,
			applyInputRules: false,
			applyPasteRules: false,
			...options
		};
		let content;
		const emitContentError = (error) => {
			editor.emit("contentError", {
				editor,
				error,
				disableCollaboration: () => {
					if ("collaboration" in editor.storage && typeof editor.storage.collaboration === "object" && editor.storage.collaboration) editor.storage.collaboration.isDisabled = true;
				}
			});
		};
		const parseOptions = {
			preserveWhitespace: "full",
			...options.parseOptions
		};
		if (!options.errorOnInvalidContent && !editor.options.enableContentCheck && editor.options.emitContentError) try {
			createNodeFromContent(value, editor.schema, {
				parseOptions,
				errorOnInvalidContent: true
			});
		} catch (e) {
			emitContentError(e);
		}
		try {
			var _options$errorOnInval;
			content = createNodeFromContent(value, editor.schema, {
				parseOptions,
				errorOnInvalidContent: (_options$errorOnInval = options.errorOnInvalidContent) !== null && _options$errorOnInval !== void 0 ? _options$errorOnInval : editor.options.enableContentCheck
			});
		} catch (e) {
			emitContentError(e);
			return false;
		}
		let { from, to } = typeof position === "number" ? {
			from: position,
			to: position
		} : {
			from: position.from,
			to: position.to
		};
		let isOnlyTextContent = true;
		let isOnlyBlockContent = true;
		const nodes = isFragment(content) ? content.content : [content];
		nodes.forEach((node) => {
			node.check();
			isOnlyTextContent = isOnlyTextContent ? node.isText && node.marks.length === 0 : false;
			isOnlyBlockContent = isOnlyBlockContent ? node.isBlock : false;
		});
		if (from === to && isOnlyBlockContent) {
			const { parent } = tr.doc.resolve(from);
			if (parent.isTextblock && !parent.type.spec.code && !parent.childCount) {
				from -= 1;
				to += 1;
			}
		}
		let newContent;
		if (isOnlyTextContent) {
			if (Array.isArray(value)) newContent = value.map((item) => item.text || "").join("");
			else if (isProseMirrorContent(value)) newContent = nodes.map((node) => {
				var _node$text;
				return (_node$text = node.text) !== null && _node$text !== void 0 ? _node$text : "";
			}).join("");
			else if (typeof value === "object" && !!value && !!value.text) newContent = value.text;
			else newContent = value;
			tr.insertText(newContent, from, to);
		} else {
			newContent = Fragment$1.from(nodes);
			const $from = tr.doc.resolve(from);
			const $fromNode = $from.node();
			const fromSelectionAtStart = $from.parentOffset === 0;
			const isTextSelection = $fromNode.isText || $fromNode.isTextblock;
			const hasContent = $fromNode.content.size > 0;
			if (fromSelectionAtStart && isTextSelection && hasContent && isOnlyBlockContent) from = Math.max(0, from - 1);
			tr.replaceWith(from, to, nodes);
		}
		if (options.updateSelection) selectionToInsertionEnd(tr, tr.steps.length - 1, -1);
		if (options.applyInputRules) tr.setMeta("applyInputRules", {
			from,
			text: newContent
		});
		if (options.applyPasteRules) tr.setMeta("applyPasteRules", {
			from,
			text: newContent
		});
	}
	return true;
};
/**
* Gets the default block type at a given match
* @param match The content match to get the default block type from
* @returns The default block type or null
*/
function defaultBlockAt(match) {
	for (let i = 0; i < match.edgeCount; i += 1) {
		const { type } = match.edge(i);
		if (type.isTextblock && !type.hasRequiredAttrs()) return type;
	}
	return null;
}
var insertDefaultBlock = (options = {}) => ({ tr, dispatch, editor }) => {
	const { pos, attrs, content, updateSelection = true } = options;
	let $pos;
	if (typeof pos === "number") $pos = tr.doc.resolve(pos);
	else if (pos) $pos = pos;
	else $pos = tr.selection.$from;
	const defaultType = defaultBlockAt($pos.parent.contentMatchAt($pos.index()));
	if (!defaultType) return false;
	const validAttrKeys = Object.keys(defaultType.spec.attrs || {});
	const filteredAttrs = attrs ? Object.fromEntries(Object.entries(attrs).filter(([key]) => validAttrKeys.includes(key))) : {};
	let node;
	if (content) {
		const parsed = createNodeFromContent(content, editor.schema);
		node = defaultType.createAndFill(filteredAttrs, parsed);
	} else node = defaultType.createAndFill(filteredAttrs);
	if (!node) return false;
	if (dispatch) {
		tr.insert($pos.pos, node);
		if (updateSelection) selectionToInsertionEnd(tr, tr.steps.length - 1, -1);
	}
	return true;
};
var joinUp$1 = () => ({ state, dispatch }) => {
	return joinUp(state, dispatch);
};
var joinDown$1 = () => ({ state, dispatch }) => {
	return joinDown(state, dispatch);
};
var joinBackward$1 = () => ({ state, dispatch }) => {
	return joinBackward(state, dispatch);
};
var joinForward$1 = () => ({ state, dispatch }) => {
	return joinForward(state, dispatch);
};
var joinItemBackward = () => ({ state, dispatch, tr }) => {
	try {
		const point = joinPoint(state.doc, state.selection.$from.pos, -1);
		if (point === null || point === void 0) return false;
		tr.join(point, 2);
		if (dispatch) dispatch(tr);
		return true;
	} catch {
		return false;
	}
};
var joinItemForward = () => ({ state, dispatch, tr }) => {
	try {
		const point = joinPoint(state.doc, state.selection.$from.pos, 1);
		if (point === null || point === void 0) return false;
		tr.join(point, 2);
		if (dispatch) dispatch(tr);
		return true;
	} catch {
		return false;
	}
};
var joinTextblockBackward$1 = () => ({ state, dispatch }) => {
	return joinTextblockBackward(state, dispatch);
};
var joinTextblockForward$1 = () => ({ state, dispatch }) => {
	return joinTextblockForward(state, dispatch);
};
function isMacOS() {
	return typeof navigator !== "undefined" ? /Mac/.test(navigator.platform) : false;
}
function normalizeKeyName(name) {
	const parts = name.split(/-(?!$)/);
	let result = parts[parts.length - 1];
	if (result === "Space") result = " ";
	let alt;
	let ctrl;
	let shift;
	let meta;
	for (let i = 0; i < parts.length - 1; i += 1) {
		const mod = parts[i];
		if (/^(cmd|meta|m)$/i.test(mod)) meta = true;
		else if (/^a(lt)?$/i.test(mod)) alt = true;
		else if (/^(c|ctrl|control)$/i.test(mod)) ctrl = true;
		else if (/^s(hift)?$/i.test(mod)) shift = true;
		else if (/^mod$/i.test(mod)) if (isiOS() || isMacOS()) meta = true;
		else ctrl = true;
		else throw new Error(`Unrecognized modifier name: ${mod}`);
	}
	if (alt) result = `Alt-${result}`;
	if (ctrl) result = `Ctrl-${result}`;
	if (meta) result = `Meta-${result}`;
	if (shift) result = `Shift-${result}`;
	return result;
}
var keyboardShortcut = (name) => ({ editor, view, tr, dispatch }) => {
	const keys = normalizeKeyName(name).split(/-(?!$)/);
	const key = keys.find((item) => ![
		"Alt",
		"Ctrl",
		"Meta",
		"Shift"
	].includes(item));
	const event = new KeyboardEvent("keydown", {
		key: key === "Space" ? " " : key,
		altKey: keys.includes("Alt"),
		ctrlKey: keys.includes("Ctrl"),
		metaKey: keys.includes("Meta"),
		shiftKey: keys.includes("Shift"),
		bubbles: true,
		cancelable: true
	});
	const capturedTransaction = editor.captureTransaction(() => {
		view.someProp("handleKeyDown", (f) => f(view, event));
	});
	capturedTransaction === null || capturedTransaction === void 0 || capturedTransaction.steps.forEach((step) => {
		const newStep = step.map(tr.mapping);
		if (newStep && dispatch) tr.maybeStep(newStep);
	});
	return true;
};
function isNodeActive(state, typeOrName, attributes = {}) {
	const { from, to, empty } = state.selection;
	const type = typeOrName ? getNodeType(typeOrName, state.schema) : null;
	const nodeRanges = [];
	state.doc.nodesBetween(from, to, (node, pos) => {
		if (node.isText) return;
		const relativeFrom = Math.max(from, pos);
		const relativeTo = Math.min(to, pos + node.nodeSize);
		nodeRanges.push({
			node,
			from: relativeFrom,
			to: relativeTo
		});
	});
	const selectionRange = to - from;
	const matchedNodeRanges = nodeRanges.filter((nodeRange) => {
		if (!type) return true;
		return type.name === nodeRange.node.type.name;
	}).filter((nodeRange) => objectIncludes(nodeRange.node.attrs, attributes, { strict: false }));
	if (empty) return !!matchedNodeRanges.length;
	return matchedNodeRanges.reduce((sum, nodeRange) => sum + nodeRange.to - nodeRange.from, 0) >= selectionRange;
}
var lift$1 = (typeOrName, attributes = {}) => ({ state, dispatch }) => {
	if (!isNodeActive(state, getNodeType(typeOrName, state.schema), attributes)) return false;
	return lift(state, dispatch);
};
var liftEmptyBlock$1 = () => ({ state, dispatch }) => {
	return liftEmptyBlock(state, dispatch);
};
var liftListItem$1 = (typeOrName) => ({ state, dispatch }) => {
	return liftListItem(getNodeType(typeOrName, state.schema))(state, dispatch);
};
var newlineInCode$1 = () => ({ state, dispatch }) => {
	return newlineInCode(state, dispatch);
};
/**
* Get the type of a schema item by its name.
* @param name The name of the schema item
* @param schema The Prosemiror schema to search in
* @returns The type of the schema item (`node` or `mark`), or null if it doesn't exist
*/
function getSchemaTypeNameByName(name, schema) {
	if (schema.nodes[name]) return "node";
	if (schema.marks[name]) return "mark";
	return null;
}
/**
* Remove a property or an array of properties from an object
* @param obj Object
* @param key Key to remove
*/
function deleteProps(obj, propOrProps) {
	const props = typeof propOrProps === "string" ? [propOrProps] : propOrProps;
	return Object.keys(obj).reduce((newObj, prop) => {
		if (!props.includes(prop)) newObj[prop] = obj[prop];
		return newObj;
	}, {});
}
var resetAttributes = (typeOrName, attributes) => ({ tr, state, dispatch }) => {
	let nodeType = null;
	let markType = null;
	const schemaType = getSchemaTypeNameByName(typeof typeOrName === "string" ? typeOrName : typeOrName.name, state.schema);
	if (!schemaType) return false;
	if (schemaType === "node") nodeType = getNodeType(typeOrName, state.schema);
	if (schemaType === "mark") markType = getMarkType(typeOrName, state.schema);
	let canReset = false;
	tr.selection.ranges.forEach((range) => {
		state.doc.nodesBetween(range.$from.pos, range.$to.pos, (node, pos) => {
			if (nodeType && nodeType === node.type) {
				canReset = true;
				if (dispatch) tr.setNodeMarkup(pos, void 0, deleteProps(node.attrs, attributes));
			}
			if (markType && node.marks.length) node.marks.forEach((mark) => {
				if (markType === mark.type) {
					canReset = true;
					if (dispatch) tr.addMark(pos, pos + node.nodeSize, markType.create(deleteProps(mark.attrs, attributes)));
				}
			});
		});
	});
	return canReset;
};
var scrollIntoView = () => ({ tr, dispatch }) => {
	if (dispatch) tr.scrollIntoView();
	return true;
};
var selectAll = () => ({ tr, dispatch }) => {
	if (dispatch) {
		const selection = new AllSelection(tr.doc);
		tr.setSelection(selection);
	}
	return true;
};
var selectNodeBackward$1 = () => ({ state, dispatch }) => {
	return selectNodeBackward(state, dispatch);
};
var selectNodeForward$1 = () => ({ state, dispatch }) => {
	return selectNodeForward(state, dispatch);
};
var selectParentNode$1 = () => ({ state, dispatch }) => {
	return selectParentNode(state, dispatch);
};
var selectTextblockEnd$1 = () => ({ state, dispatch }) => {
	return selectTextblockEnd(state, dispatch);
};
var selectTextblockStart$1 = () => ({ state, dispatch }) => {
	return selectTextblockStart(state, dispatch);
};
/**
* Create a new Prosemirror document node from content.
* @param content The JSON or HTML content to create the document from
* @param schema The Prosemirror schema to use for the document
* @param parseOptions Options for the parser
* @returns The created Prosemirror document node
*/
function createDocument(content, schema, parseOptions = {}, options = {}) {
	return createNodeFromContent(content, schema, {
		slice: false,
		parseOptions,
		errorOnInvalidContent: options.errorOnInvalidContent
	});
}
var setContent = (content, { errorOnInvalidContent, emitUpdate = true, parseOptions = {} } = {}) => ({ editor, tr, dispatch, commands }) => {
	const { doc } = tr;
	if (parseOptions.preserveWhitespace !== "full") {
		const document = createDocument(content, editor.schema, parseOptions, { errorOnInvalidContent: errorOnInvalidContent !== null && errorOnInvalidContent !== void 0 ? errorOnInvalidContent : editor.options.enableContentCheck });
		if (dispatch) {
			const nodes = isFragment(document) ? document.content : [document];
			tr.replaceWith(0, doc.content.size, nodes).setMeta("preventUpdate", !emitUpdate);
		}
		return true;
	}
	if (dispatch) tr.setMeta("preventUpdate", !emitUpdate);
	return commands.insertContentAt({
		from: 0,
		to: doc.content.size
	}, content, {
		parseOptions,
		errorOnInvalidContent: errorOnInvalidContent !== null && errorOnInvalidContent !== void 0 ? errorOnInvalidContent : editor.options.enableContentCheck
	});
};
function getMarkAttributes(state, typeOrName) {
	const type = getMarkType(typeOrName, state.schema);
	const { from, to, empty } = state.selection;
	const marks = [];
	if (empty) {
		if (state.storedMarks) marks.push(...state.storedMarks);
		marks.push(...state.selection.$head.marks());
	} else state.doc.nodesBetween(from, to, (node) => {
		marks.push(...node.marks);
	});
	const mark = marks.find((markItem) => markItem.type.name === type.name);
	if (!mark) return {};
	return { ...mark.attrs };
}
/**
* Returns a new `Transform` based on all steps of the passed transactions.
* @param oldDoc The Prosemirror node to start from
* @param transactions The transactions to combine
* @returns A new `Transform` with all steps of the passed transactions
*/
function combineTransactionSteps(oldDoc, transactions) {
	const transform = new Transform(oldDoc);
	transactions.forEach((transaction) => {
		transaction.steps.forEach((step) => {
			transform.step(step);
		});
	});
	return transform;
}
/**
* Find children inside a Prosemirror node that match a predicate.
* @param node The Prosemirror node to search in
* @param predicate The predicate to match
* @returns An array of nodes with their positions
*/
function findChildren(node, predicate) {
	const nodesWithPos = [];
	node.descendants((child, pos) => {
		if (predicate(child)) nodesWithPos.push({
			node: child,
			pos
		});
	});
	return nodesWithPos;
}
/**
* Same as `findChildren` but searches only within a `range`.
* @param node The Prosemirror node to search in
* @param range The range to search in
* @param predicate The predicate to match
* @returns An array of nodes with their positions
*/
function findChildrenInRange(node, range, predicate) {
	const nodesWithPos = [];
	node.nodesBetween(range.from, range.to, (child, pos) => {
		if (predicate(child)) nodesWithPos.push({
			node: child,
			pos
		});
	});
	return nodesWithPos;
}
/**
* Finds the closest parent node to a resolved position that matches a predicate.
* @param $pos The resolved position to search from
* @param predicate The predicate to match
* @returns The closest parent node to the resolved position that matches the predicate
* @example ```js
* findParentNodeClosestToPos($from, node => node.type.name === 'paragraph')
* ```
*/
function findParentNodeClosestToPos($pos, predicate) {
	for (let i = $pos.depth; i > 0; i -= 1) {
		const node = $pos.node(i);
		if (predicate(node)) return {
			pos: i > 0 ? $pos.before(i) : 0,
			start: $pos.start(i),
			depth: i,
			node
		};
	}
}
/**
* Finds the closest parent node to the current selection that matches a predicate.
* @param predicate The predicate to match
* @returns A command that finds the closest parent node to the current selection that matches the predicate
* @example ```js
* findParentNode(node => node.type.name === 'paragraph')
* ```
*/
function findParentNode(predicate) {
	return (selection) => findParentNodeClosestToPos(selection.$from, predicate);
}
/**
* Returns a field from an extension
* @param extension The Tiptap extension
* @param field The field, for example `renderHTML` or `priority`
* @param context The context object that should be passed as `this` into the function
* @returns The field value
*/
function getExtensionField(extension, field, context) {
	if (extension.config[field] === void 0 && extension.parent) return getExtensionField(extension.parent, field, context);
	if (typeof extension.config[field] === "function") return extension.config[field].bind({
		...context,
		parent: extension.parent ? getExtensionField(extension.parent, field, context) : null
	});
	return extension.config[field];
}
/**
* Create a flattened array of extensions by traversing the `addExtensions` field.
* @param extensions An array of Tiptap extensions
* @returns A flattened array of Tiptap extensions
*/
function flattenExtensions(extensions) {
	return extensions.map((extension) => {
		const addExtensions = getExtensionField(extension, "addExtensions", {
			name: extension.name,
			options: extension.options,
			storage: extension.storage
		});
		if (addExtensions) return [extension, ...flattenExtensions(addExtensions())];
		return extension;
	}).flat(10);
}
function getHTMLFromFragment(fragment, schema) {
	const documentFragment = DOMSerializer.fromSchema(schema).serializeFragment(fragment);
	const container = document.implementation.createHTMLDocument().createElement("div");
	container.appendChild(documentFragment);
	return container.innerHTML;
}
function isFunction(value) {
	return typeof value === "function";
}
/**
* Optionally calls `value` as a function.
* Otherwise it is returned directly.
* @param value Function or any value.
* @param context Optional context to bind to function.
* @param props Optional props to pass to function.
*/
function callOrReturn(value, context = void 0, ...props) {
	if (isFunction(value)) {
		if (context) return value.bind(context)(...props);
		return value(...props);
	}
	return value;
}
function isEmptyObject(value = {}) {
	return Object.keys(value).length === 0 && value.constructor === Object;
}
function splitExtensions(extensions) {
	return {
		baseExtensions: extensions.filter((extension) => extension.type === "extension"),
		nodeExtensions: extensions.filter((extension) => extension.type === "node"),
		markExtensions: extensions.filter((extension) => extension.type === "mark")
	};
}
/**
* Get a list of all extension attributes defined in `addAttribute` and `addGlobalAttribute`.
* @param extensions List of extensions
*/
function getAttributesFromExtensions(extensions) {
	const extensionAttributes = [];
	const { nodeExtensions, markExtensions } = splitExtensions(extensions);
	const nodeAndMarkExtensions = [...nodeExtensions, ...markExtensions];
	const defaultAttribute = {
		default: null,
		validate: void 0,
		rendered: true,
		renderHTML: null,
		parseHTML: null,
		keepOnSplit: true,
		isRequired: false
	};
	const nodeExtensionTypes = nodeExtensions.filter((ext) => ext.name !== "text").map((ext) => ext.name);
	const markExtensionTypes = markExtensions.map((ext) => ext.name);
	const allExtensionTypes = [...nodeExtensionTypes, ...markExtensionTypes];
	extensions.forEach((extension) => {
		const addGlobalAttributes = getExtensionField(extension, "addGlobalAttributes", {
			name: extension.name,
			options: extension.options,
			storage: extension.storage,
			extensions: nodeAndMarkExtensions
		});
		if (!addGlobalAttributes) return;
		addGlobalAttributes().forEach((globalAttribute) => {
			let resolvedTypes;
			if (Array.isArray(globalAttribute.types)) resolvedTypes = globalAttribute.types;
			else if (globalAttribute.types === "*") resolvedTypes = allExtensionTypes;
			else if (globalAttribute.types === "nodes") resolvedTypes = nodeExtensionTypes;
			else if (globalAttribute.types === "marks") resolvedTypes = markExtensionTypes;
			else resolvedTypes = [];
			resolvedTypes.forEach((type) => {
				Object.entries(globalAttribute.attributes).forEach(([name, attribute]) => {
					extensionAttributes.push({
						type,
						name,
						attribute: {
							...defaultAttribute,
							...attribute
						}
					});
				});
			});
		});
	});
	nodeAndMarkExtensions.forEach((extension) => {
		const addAttributes = getExtensionField(extension, "addAttributes", {
			name: extension.name,
			options: extension.options,
			storage: extension.storage
		});
		if (!addAttributes) return;
		const attributes = addAttributes();
		Object.entries(attributes).forEach(([name, attribute]) => {
			const mergedAttr = {
				...defaultAttribute,
				...attribute
			};
			if (typeof (mergedAttr === null || mergedAttr === void 0 ? void 0 : mergedAttr.default) === "function") mergedAttr.default = mergedAttr.default();
			if ((mergedAttr === null || mergedAttr === void 0 ? void 0 : mergedAttr.isRequired) && (mergedAttr === null || mergedAttr === void 0 ? void 0 : mergedAttr.default) === void 0) delete mergedAttr.default;
			extensionAttributes.push({
				type: extension.name,
				name,
				attribute: mergedAttr
			});
		});
	});
	return extensionAttributes;
}
/** Splits a CSS style string into declarations, ignoring semicolons inside quotes/parentheses. */
function splitStyleDeclarations(styles) {
	const result = [];
	let current = "";
	let inSingleQuote = false;
	let inDoubleQuote = false;
	let parenDepth = 0;
	const length = styles.length;
	for (let i = 0; i < length; i += 1) {
		const char = styles[i];
		if (char === "'" && !inDoubleQuote) {
			inSingleQuote = !inSingleQuote;
			current += char;
			continue;
		}
		if (char === "\"" && !inSingleQuote) {
			inDoubleQuote = !inDoubleQuote;
			current += char;
			continue;
		}
		if (!inSingleQuote && !inDoubleQuote) {
			if (char === "(") {
				parenDepth += 1;
				current += char;
				continue;
			}
			if (char === ")" && parenDepth > 0) {
				parenDepth -= 1;
				current += char;
				continue;
			}
			if (char === ";" && parenDepth === 0) {
				result.push(current);
				current = "";
				continue;
			}
		}
		current += char;
	}
	if (current) result.push(current);
	return result;
}
/** Yields property/value pairs from a style string. */
function parseStyleEntries(styles) {
	const pairs = [];
	const declarations = splitStyleDeclarations(styles || "");
	const numDeclarations = declarations.length;
	for (let i = 0; i < numDeclarations; i += 1) {
		const declaration = declarations[i];
		const firstColonIndex = declaration.indexOf(":");
		if (firstColonIndex === -1) continue;
		const property = declaration.slice(0, firstColonIndex).trim();
		const value = declaration.slice(firstColonIndex + 1).trim();
		if (property && value) pairs.push([property, value]);
	}
	return pairs;
}
function mergeAttributes(...objects) {
	return objects.filter((item) => !!item).reduce((items, item) => {
		const mergedAttributes = { ...items };
		Object.entries(item).forEach(([key, value]) => {
			if (key === "__proto__") {
				Object.defineProperty(mergedAttributes, key, {
					configurable: true,
					enumerable: true,
					value,
					writable: true
				});
				return;
			}
			if (!mergedAttributes[key]) {
				mergedAttributes[key] = value;
				return;
			}
			if (key === "class") {
				const valueClasses = value ? String(value).split(" ") : [];
				const existingClasses = mergedAttributes[key] ? mergedAttributes[key].split(" ") : [];
				const insertClasses = valueClasses.filter((valueClass) => !existingClasses.includes(valueClass));
				mergedAttributes[key] = [...existingClasses, ...insertClasses].join(" ");
			} else if (key === "style") {
				const styleMap = new Map([...parseStyleEntries(mergedAttributes[key]), ...parseStyleEntries(value)]);
				mergedAttributes[key] = Array.from(styleMap.entries()).map(([property, val]) => `${property}: ${val}`).join("; ");
			} else mergedAttributes[key] = value;
		});
		return mergedAttributes;
	}, {});
}
function getRenderedAttributes(nodeOrMark, extensionAttributes) {
	return extensionAttributes.filter((attribute) => attribute.type === nodeOrMark.type.name).filter((item) => item.attribute.rendered).map((item) => {
		if (!item.attribute.renderHTML) return { [item.name]: nodeOrMark.attrs[item.name] };
		return item.attribute.renderHTML(nodeOrMark.attrs) || {};
	}).reduce((attributes, attribute) => mergeAttributes(attributes, attribute), {});
}
function fromString(value) {
	if (typeof value !== "string") return value;
	if (value.match(/^[+-]?(?:\d*\.)?\d+$/)) return Number(value);
	if (value === "true") return true;
	if (value === "false") return false;
	return value;
}
/**
* This function merges extension attributes into parserule attributes (`attrs` or `getAttrs`).
* Cancels when `getAttrs` returned `false`.
* @param parseRule ProseMirror ParseRule
* @param extensionAttributes List of attributes to inject
*/
function injectExtensionAttributesToParseRule(parseRule, extensionAttributes) {
	if ("style" in parseRule) return parseRule;
	return {
		...parseRule,
		getAttrs: (node) => {
			const oldAttributes = parseRule.getAttrs ? parseRule.getAttrs(node) : parseRule.attrs;
			if (oldAttributes === false) return false;
			const newAttributes = extensionAttributes.reduce((items, item) => {
				const value = item.attribute.parseHTML ? item.attribute.parseHTML(node) : fromString(node.getAttribute(item.name));
				if (value === null || value === void 0) return items;
				return {
					...items,
					[item.name]: value
				};
			}, {});
			return {
				...oldAttributes,
				...newAttributes
			};
		}
	};
}
function cleanUpSchemaItem(data) {
	return Object.fromEntries(Object.entries(data).filter(([key, value]) => {
		if (key === "attrs" && isEmptyObject(value)) return false;
		return value !== null && value !== void 0;
	}));
}
/**
* Builds an attribute spec tuple for ProseMirror schema from an extension attribute.
* @param extensionAttribute The extension attribute to build the spec for
* @returns A tuple of [attributeName, spec]
*/
function buildAttributeSpec(extensionAttribute) {
	var _extensionAttribute$a, _extensionAttribute$a2;
	const spec = {};
	if (!(extensionAttribute === null || extensionAttribute === void 0 || (_extensionAttribute$a = extensionAttribute.attribute) === null || _extensionAttribute$a === void 0 ? void 0 : _extensionAttribute$a.isRequired) && "default" in ((extensionAttribute === null || extensionAttribute === void 0 ? void 0 : extensionAttribute.attribute) || {})) spec.default = extensionAttribute.attribute.default;
	if ((extensionAttribute === null || extensionAttribute === void 0 || (_extensionAttribute$a2 = extensionAttribute.attribute) === null || _extensionAttribute$a2 === void 0 ? void 0 : _extensionAttribute$a2.validate) !== void 0) spec.validate = extensionAttribute.attribute.validate;
	return [extensionAttribute.name, spec];
}
/**
* Creates a new Prosemirror schema based on the given extensions.
* @param extensions An array of Tiptap extensions
* @param editor The editor instance
* @returns A Prosemirror schema
*/
function getSchemaByResolvedExtensions(extensions, editor) {
	var _nodeExtensions$find;
	const allAttributes = getAttributesFromExtensions(extensions);
	const { nodeExtensions, markExtensions } = splitExtensions(extensions);
	return new Schema({
		topNode: (_nodeExtensions$find = nodeExtensions.find((extension) => getExtensionField(extension, "topNode"))) === null || _nodeExtensions$find === void 0 ? void 0 : _nodeExtensions$find.name,
		nodes: Object.fromEntries(nodeExtensions.map((extension) => {
			const extensionAttributes = allAttributes.filter((attribute) => attribute.type === extension.name);
			const context = {
				name: extension.name,
				options: extension.options,
				storage: extension.storage,
				editor
			};
			const schema = cleanUpSchemaItem({
				...extensions.reduce((fields, e) => {
					const extendNodeSchema = getExtensionField(e, "extendNodeSchema", context);
					return {
						...fields,
						...extendNodeSchema ? extendNodeSchema(extension) : {}
					};
				}, {}),
				content: callOrReturn(getExtensionField(extension, "content", context)),
				marks: callOrReturn(getExtensionField(extension, "marks", context)),
				group: callOrReturn(getExtensionField(extension, "group", context)),
				inline: callOrReturn(getExtensionField(extension, "inline", context)),
				atom: callOrReturn(getExtensionField(extension, "atom", context)),
				selectable: callOrReturn(getExtensionField(extension, "selectable", context)),
				draggable: callOrReturn(getExtensionField(extension, "draggable", context)),
				code: callOrReturn(getExtensionField(extension, "code", context)),
				whitespace: callOrReturn(getExtensionField(extension, "whitespace", context)),
				linebreakReplacement: callOrReturn(getExtensionField(extension, "linebreakReplacement", context)),
				defining: callOrReturn(getExtensionField(extension, "defining", context)),
				isolating: callOrReturn(getExtensionField(extension, "isolating", context)),
				attrs: Object.fromEntries(extensionAttributes.map(buildAttributeSpec))
			});
			const parseHTML = callOrReturn(getExtensionField(extension, "parseHTML", context));
			if (parseHTML) schema.parseDOM = parseHTML.map((parseRule) => injectExtensionAttributesToParseRule(parseRule, extensionAttributes));
			const renderHTML = getExtensionField(extension, "renderHTML", context);
			if (renderHTML) schema.toDOM = (node) => renderHTML({
				node,
				HTMLAttributes: getRenderedAttributes(node, extensionAttributes)
			});
			const renderText = getExtensionField(extension, "renderText", context);
			if (renderText) schema.toText = renderText;
			return [extension.name, schema];
		})),
		marks: Object.fromEntries(markExtensions.map((extension) => {
			const extensionAttributes = allAttributes.filter((attribute) => attribute.type === extension.name);
			const context = {
				name: extension.name,
				options: extension.options,
				storage: extension.storage,
				editor
			};
			const schema = cleanUpSchemaItem({
				...extensions.reduce((fields, e) => {
					const extendMarkSchema = getExtensionField(e, "extendMarkSchema", context);
					return {
						...fields,
						...extendMarkSchema ? extendMarkSchema(extension) : {}
					};
				}, {}),
				inclusive: callOrReturn(getExtensionField(extension, "inclusive", context)),
				excludes: callOrReturn(getExtensionField(extension, "excludes", context)),
				group: callOrReturn(getExtensionField(extension, "group", context)),
				spanning: callOrReturn(getExtensionField(extension, "spanning", context)),
				code: callOrReturn(getExtensionField(extension, "code", context)),
				attrs: Object.fromEntries(extensionAttributes.map(buildAttributeSpec))
			});
			const parseHTML = callOrReturn(getExtensionField(extension, "parseHTML", context));
			if (parseHTML) schema.parseDOM = parseHTML.map((parseRule) => injectExtensionAttributesToParseRule(parseRule, extensionAttributes));
			const renderHTML = getExtensionField(extension, "renderHTML", context);
			if (renderHTML) schema.toDOM = (mark) => renderHTML({
				mark,
				HTMLAttributes: getRenderedAttributes(mark, extensionAttributes)
			});
			return [extension.name, schema];
		}))
	});
}
/**
* Find duplicates in an array.
*/
function findDuplicates(items) {
	const filtered = items.filter((el, index) => items.indexOf(el) !== index);
	return Array.from(new Set(filtered));
}
/**
* Sort extensions by priority.
* @param extensions An array of Tiptap extensions
* @returns A sorted array of Tiptap extensions by priority
*/
function sortExtensions(extensions) {
	const defaultPriority = 100;
	return extensions.sort((a, b) => {
		const priorityA = getExtensionField(a, "priority") || defaultPriority;
		const priorityB = getExtensionField(b, "priority") || defaultPriority;
		if (priorityA > priorityB) return -1;
		if (priorityA < priorityB) return 1;
		return 0;
	});
}
/**
* Returns a flattened and sorted extension list while
* also checking for duplicated extensions and warns the user.
* @param extensions An array of Tiptap extensions
* @returns An flattened and sorted array of Tiptap extensions
*/
function resolveExtensions(extensions) {
	const resolvedExtensions = sortExtensions(flattenExtensions(extensions));
	const duplicatedNames = findDuplicates(resolvedExtensions.map((extension) => extension.name));
	if (duplicatedNames.length) console.warn(`[tiptap warn]: Duplicate extension names found: [${duplicatedNames.map((item) => `'${item}'`).join(", ")}]. This can lead to issues.`);
	return resolvedExtensions;
}
function getSchema(extensions, editor) {
	return getSchemaByResolvedExtensions(resolveExtensions(extensions), editor);
}
/**
* Generate HTML from a JSONContent
* @param doc The JSONContent to generate HTML from
* @param extensions The extensions to use for the schema
* @returns The generated HTML
*/
function generateHTML(doc, extensions) {
	const schema = getSchema(extensions);
	return getHTMLFromFragment(Node$1.fromJSON(schema, doc).content, schema);
}
/**
* Generate JSONContent from HTML
* @param html The HTML to generate JSONContent from
* @param extensions The extensions to use for the schema
* @returns The generated JSONContent
*/
function generateJSON(html, extensions) {
	const schema = getSchema(extensions);
	const dom = elementFromString(html);
	return DOMParser.fromSchema(schema).parse(dom).toJSON();
}
/**
* Gets the text between two positions in a Prosemirror node
* and serializes it using the given text serializers and block separator (see getText)
* @param startNode The Prosemirror node to start from
* @param range The range of the text to get
* @param options Options for the text serializer & block separator
* @returns The text between the two positions
*/
function getTextBetween(startNode, range, options) {
	const { from, to } = range;
	const { blockSeparator = "\n\n", textSerializers = {} } = options || {};
	let text = "";
	startNode.nodesBetween(from, to, (node, pos, parent, index) => {
		if (node.isBlock && pos > from) text += blockSeparator;
		const textSerializer = textSerializers === null || textSerializers === void 0 ? void 0 : textSerializers[node.type.name];
		if (textSerializer) {
			if (parent) text += textSerializer({
				node,
				pos,
				parent,
				index,
				range
			});
			return false;
		}
		if (node.isText) {
			var _node$text;
			text += node === null || node === void 0 || (_node$text = node.text) === null || _node$text === void 0 ? void 0 : _node$text.slice(Math.max(from, pos) - pos, to - pos);
		}
	});
	return text;
}
/**
* Gets the text of a Prosemirror node
* @param node The Prosemirror node
* @param options Options for the text serializer & block separator
* @returns The text of the node
* @example ```js
* const text = getText(node, { blockSeparator: '\n' })
* ```
*/
function getText(node, options) {
	return getTextBetween(node, {
		from: 0,
		to: node.content.size
	}, options);
}
/**
* Find text serializers `toText` in a Prosemirror schema
* @param schema The Prosemirror schema to search in
* @returns A record of text serializers by node name
*/
function getTextSerializersFromSchema(schema) {
	return Object.fromEntries(Object.entries(schema.nodes).filter(([, node]) => node.spec.toText).map(([name, node]) => [name, node.spec.toText]));
}
/**
* Generate raw text from a JSONContent
* @param doc The JSONContent to generate text from
* @param extensions The extensions to use for the schema
* @param options Options for the text generation f.e. blockSeparator or textSerializers
* @returns The generated text
*/
function generateText(doc, extensions, options) {
	const { blockSeparator = "\n\n", textSerializers = {} } = options || {};
	const schema = getSchema(extensions);
	return getText(Node$1.fromJSON(schema, doc), {
		blockSeparator,
		textSerializers: {
			...getTextSerializersFromSchema(schema),
			...textSerializers
		}
	});
}
function getNodeAttributes(state, typeOrName) {
	const type = getNodeType(typeOrName, state.schema);
	const { from, to } = state.selection;
	const nodes = [];
	state.doc.nodesBetween(from, to, (node) => {
		nodes.push(node);
	});
	const node = nodes.reverse().find((nodeItem) => nodeItem.type.name === type.name);
	if (!node) return {};
	return { ...node.attrs };
}
/**
* Get node or mark attributes by type or name on the current editor state
* @param state The current editor state
* @param typeOrName The node or mark type or name
* @returns The attributes of the node or mark or an empty object
*/
function getAttributes(state, typeOrName) {
	const schemaType = getSchemaTypeNameByName(typeof typeOrName === "string" ? typeOrName : typeOrName.name, state.schema);
	if (schemaType === "node") return getNodeAttributes(state, typeOrName);
	if (schemaType === "mark") return getMarkAttributes(state, typeOrName);
	return {};
}
/**
* Removes duplicated values within an array.
* Supports numbers, strings and objects.
*/
function removeDuplicates(array, by = JSON.stringify) {
	const seen = {};
	return array.filter((item) => {
		const key = by(item);
		return Object.prototype.hasOwnProperty.call(seen, key) ? false : seen[key] = true;
	});
}
/**
* Removes duplicated ranges and ranges that are
* fully captured by other ranges.
*/
function simplifyChangedRanges(changes) {
	const uniqueChanges = removeDuplicates(changes);
	return uniqueChanges.length === 1 ? uniqueChanges : uniqueChanges.filter((change, index) => {
		return !uniqueChanges.filter((_, i) => i !== index).some((otherChange) => {
			return change.oldRange.from >= otherChange.oldRange.from && change.oldRange.to <= otherChange.oldRange.to && change.newRange.from >= otherChange.newRange.from && change.newRange.to <= otherChange.newRange.to;
		});
	});
}
/**
* Returns a list of changed ranges
* based on the first and last state of all steps.
*/
function getChangedRanges(transform) {
	const { mapping, steps } = transform;
	const changes = [];
	mapping.maps.forEach((stepMap, index) => {
		const ranges = [];
		if (!stepMap.ranges.length) {
			const { from, to } = steps[index];
			if (from === void 0 || to === void 0) return;
			ranges.push({
				from,
				to
			});
		} else stepMap.forEach((from, to) => {
			ranges.push({
				from,
				to
			});
		});
		ranges.forEach(({ from, to }) => {
			const newStart = mapping.slice(index).map(from, -1);
			const newEnd = mapping.slice(index).map(to);
			const oldStart = mapping.invert().map(newStart, -1);
			const oldEnd = mapping.invert().map(newEnd);
			changes.push({
				oldRange: {
					from: oldStart,
					to: oldEnd
				},
				newRange: {
					from: newStart,
					to: newEnd
				}
			});
		});
	});
	return simplifyChangedRanges(changes);
}
function getDebugJSON(node, startOffset = 0) {
	const increment = node.type === node.type.schema.topNodeType ? 0 : 1;
	const from = startOffset;
	const to = from + node.nodeSize;
	const marks = node.marks.map((mark) => {
		const output = { type: mark.type.name };
		if (Object.keys(mark.attrs).length) output.attrs = { ...mark.attrs };
		return output;
	});
	const attrs = { ...node.attrs };
	const output = {
		type: node.type.name,
		from,
		to
	};
	if (Object.keys(attrs).length) output.attrs = attrs;
	if (marks.length) output.marks = marks;
	if (node.content.childCount) {
		output.content = [];
		node.forEach((child, offset) => {
			var _output$content;
			(_output$content = output.content) === null || _output$content === void 0 || _output$content.push(getDebugJSON(child, startOffset + offset + increment));
		});
	}
	if (node.text) output.text = node.text;
	return output;
}
function getMarksBetween(from, to, doc) {
	const marks = [];
	if (from === to) doc.resolve(from).marks().forEach((mark) => {
		const range = getMarkRange(doc.resolve(from), mark.type);
		if (!range) return;
		marks.push({
			mark,
			...range
		});
	});
	else doc.nodesBetween(from, to, (node, pos) => {
		if (!node || (node === null || node === void 0 ? void 0 : node.nodeSize) === void 0) return;
		marks.push(...node.marks.map((mark) => ({
			from: pos,
			to: pos + node.nodeSize,
			mark
		})));
	});
	return marks;
}
/**
* Finds the first node of a given type or name in the current selection.
* @param state The editor state.
* @param typeOrName The node type or name.
* @param pos The position to start searching from.
* @param maxDepth The maximum depth to search.
* @returns The node and the depth as an array.
*/
var getNodeAtPosition = (state, typeOrName, pos, maxDepth = 20) => {
	const $pos = state.doc.resolve(pos);
	let currentDepth = maxDepth;
	let node = null;
	while (currentDepth > 0 && node === null) {
		const currentNode = $pos.node(currentDepth);
		if ((currentNode === null || currentNode === void 0 ? void 0 : currentNode.type.name) === typeOrName) node = currentNode;
		else currentDepth -= 1;
	}
	return [node, currentDepth];
};
/**
* Returns the block-level sibling immediately before the cursor's textblock
* (or null when the cursor is at the first child of its block parent).
*
* The position does not have to sit inside a textblock: for any resolved
* position, the result is the sibling immediately before `$pos.parent`. At a
* GapCursor position this is the sibling before the whole container, not the
* node before the gap.
*
* @param $pos The resolved position to look around
* @returns The previous block-level sibling, or null
* @example ```js
* // Cursor in a top-level paragraph after a list:
* // <ul><li>A</li></ul><p>|B</p>
* getPreviousBlockSibling($from) // <ul>
*
* // Cursor in the second paragraph of a list item:
* // <ul><li><p>A</p><p>|B</p></li></ul>
* getPreviousBlockSibling($from) // <p>A</p>
*
* // Cursor in the first child of its block parent:
* // <doc><p>|A</p></doc>
* getPreviousBlockSibling($from) // null
* ```
*/
var getPreviousBlockSibling = ($pos) => {
	const parentDepth = $pos.depth - 1;
	if (parentDepth < 0) return null;
	const index = $pos.index(parentDepth);
	if (index === 0) return null;
	return $pos.node(parentDepth).child(index - 1);
};
/**
* Tries to get a node or mark type by its name.
* @param name The name of the node or mark type
* @param schema The Prosemiror schema to search in
* @returns The node or mark type, or null if it doesn't exist
*/
function getSchemaTypeByName(name, schema) {
	return schema.nodes[name] || schema.marks[name] || null;
}
/**
* Return attributes of an extension that should be splitted by keepOnSplit flag
* @param extensionAttributes Array of extension attributes
* @param typeName The type of the extension
* @param attributes The attributes of the extension
* @returns The splitted attributes
*/
function getSplittedAttributes(extensionAttributes, typeName, attributes) {
	return Object.fromEntries(Object.entries(attributes).filter(([name]) => {
		const extensionAttribute = extensionAttributes.find((item) => {
			return item.type === typeName && item.name === name;
		});
		if (!extensionAttribute) return false;
		return extensionAttribute.attribute.keepOnSplit;
	}));
}
/**
* Returns the text content of a resolved prosemirror position
* @param $from The resolved position to get the text content from
* @param maxMatch The maximum number of characters to match
* @returns The text content
*/
var getTextContentFromNodes = ($from, maxMatch = 500) => {
	let textBefore = "";
	const sliceEndPos = $from.parentOffset;
	$from.parent.nodesBetween(Math.max(0, sliceEndPos - maxMatch), sliceEndPos, (node, pos, parent, index) => {
		var _node$type$spec$toTex, _node$type$spec;
		const chunk = ((_node$type$spec$toTex = (_node$type$spec = node.type.spec).toText) === null || _node$type$spec$toTex === void 0 ? void 0 : _node$type$spec$toTex.call(_node$type$spec, {
			node,
			pos,
			parent,
			index
		})) || node.textContent || "%leaf%";
		textBefore += node.isAtom && !node.isText ? chunk : chunk.slice(0, Math.max(0, sliceEndPos - pos));
	});
	return textBefore;
};
function isMarkActive(state, typeOrName, attributes = {}) {
	const { empty, ranges } = state.selection;
	const type = typeOrName ? getMarkType(typeOrName, state.schema) : null;
	if (empty) return !!(state.storedMarks || state.selection.$from.marks()).filter((mark) => {
		if (!type) return true;
		return type.name === mark.type.name;
	}).find((mark) => objectIncludes(mark.attrs, attributes, { strict: false }));
	let selectionRange = 0;
	const markRanges = [];
	ranges.forEach(({ $from, $to }) => {
		const from = $from.pos;
		const to = $to.pos;
		state.doc.nodesBetween(from, to, (node, pos) => {
			if (type && node.inlineContent && !node.type.allowsMarkType(type)) return false;
			if (!node.isText && !node.marks.length) return;
			const relativeFrom = Math.max(from, pos);
			const relativeTo = Math.min(to, pos + node.nodeSize);
			const range = relativeTo - relativeFrom;
			selectionRange += range;
			markRanges.push(...node.marks.map((mark) => ({
				mark,
				from: relativeFrom,
				to: relativeTo
			})));
		});
	});
	if (selectionRange === 0) return false;
	const matchedRange = markRanges.filter((markRange) => {
		if (!type) return true;
		return type.name === markRange.mark.type.name;
	}).filter((markRange) => objectIncludes(markRange.mark.attrs, attributes, { strict: false })).reduce((sum, markRange) => sum + markRange.to - markRange.from, 0);
	const excludedRange = markRanges.filter((markRange) => {
		if (!type) return true;
		return markRange.mark.type !== type && markRange.mark.type.excludes(type);
	}).reduce((sum, markRange) => sum + markRange.to - markRange.from, 0);
	return (matchedRange > 0 ? matchedRange + excludedRange : matchedRange) >= selectionRange;
}
function isActive(state, name, attributes = {}) {
	if (!name) return isNodeActive(state, null, attributes) || isMarkActive(state, null, attributes);
	const schemaType = getSchemaTypeNameByName(name, state.schema);
	if (schemaType === "node") return isNodeActive(state, name, attributes);
	if (schemaType === "mark") return isMarkActive(state, name, attributes);
	return false;
}
var isAtEndOfNode = (state, nodeType) => {
	const { $from, $to, $anchor } = state.selection;
	if (nodeType) {
		const parentNode = findParentNode((node) => node.type.name === nodeType)(state.selection);
		if (!parentNode) return false;
		const $parentPos = state.doc.resolve(parentNode.pos + 1);
		if ($anchor.pos + 1 === $parentPos.end()) return true;
		return false;
	}
	if ($to.parentOffset < $to.parent.nodeSize - 2 || $from.pos !== $to.pos) return false;
	return true;
};
var isAtStartOfNode = (state) => {
	const { $from, $to } = state.selection;
	if ($from.parentOffset > 0 || $from.pos !== $to.pos) return false;
	return true;
};
function isExtensionRulesEnabled(extension, enabled) {
	if (Array.isArray(enabled)) return enabled.some((enabledExtension) => {
		return (typeof enabledExtension === "string" ? enabledExtension : enabledExtension.name) === extension.name;
	});
	return enabled;
}
function isList(name, extensions) {
	const { nodeExtensions } = splitExtensions(extensions);
	const extension = nodeExtensions.find((item) => item.name === name);
	if (!extension) return false;
	const group = callOrReturn(getExtensionField(extension, "group", {
		name: extension.name,
		options: extension.options,
		storage: extension.storage
	}));
	if (typeof group !== "string") return false;
	return group.split(" ").includes("list");
}
/**
* Returns true if the given prosemirror node is empty.
*/
function isNodeEmpty(node, { checkChildren = true, ignoreWhitespace = false } = {}) {
	if (ignoreWhitespace) {
		if (node.type.name === "hardBreak") return true;
		if (node.isText) {
			var _node$text;
			return !/\S/.test((_node$text = node.text) !== null && _node$text !== void 0 ? _node$text : "");
		}
	}
	if (node.isText) return !node.text;
	if (node.isAtom || node.isLeaf) return false;
	if (node.content.childCount === 0) return true;
	if (checkChildren) {
		let isContentEmpty = true;
		node.content.forEach((childNode) => {
			if (isContentEmpty === false) return;
			if (!isNodeEmpty(childNode, {
				ignoreWhitespace,
				checkChildren
			})) isContentEmpty = false;
		});
		return isContentEmpty;
	}
	return false;
}
function isNodeSelection(value) {
	return value instanceof NodeSelection;
}
/**
* Determines whether a node view should be considered selected for the given
* editor selection.
*
* A node is considered selected when the current selection fully covers it
* (e.g. a `NodeSelection`). When `selectedOnTextSelection` is enabled, the
* node is additionally considered selected if a `TextSelection` is fully
* contained within the node's range.
*
* @param selection The current editor selection.
* @param pos The start position of the node in the document.
* @param nodeSize The size of the node.
* @param selectedOnTextSelection When `true`, also treat selections inside the node as selected.
* @returns `true` if the node view should render as selected.
*/
function isNodeViewSelected({ selection, pos, nodeSize, selectedOnTextSelection = false }) {
	const { from, to } = selection;
	if (from <= pos && to >= pos + nodeSize) return true;
	if (selectedOnTextSelection && isTextSelection(selection) && from > pos && to < pos + nodeSize) return true;
	return false;
}
/**
* Checks if a value is a ProseMirror step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror Step
* @example ```js
* isProseMirrorStep(transaction.steps[0])
* ```
*/
function isProseMirrorStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (typeof step.apply !== "function" || typeof step.getMap !== "function" || typeof step.invert !== "function" || typeof step.map !== "function" || typeof step.merge !== "function" || typeof step.toJSON !== "function") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror add mark step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror AddMarkStep
* @example ```js
* isProseMirrorAddMarkStep(transaction.steps[0])
* ```
*/
function isProseMirrorAddMarkStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "addMark") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror add node mark step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror AddNodeMarkStep
* @example ```js
* isProseMirrorAddNodeMarkStep(transaction.steps[0])
* ```
*/
function isProseMirrorAddNodeMarkStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "addNodeMark") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror attribute step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror AttrStep
* @example ```js
* isProseMirrorAttrStep(transaction.steps[0])
* ```
*/
function isProseMirrorAttrStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "attr") return false;
	return true;
}
/**
* Check whether a selection provides the cell-iteration API.
*
* @param selection - Selection to inspect.
* @returns Whether the selection is a table cell selection.
* @example ```js
* isProseMirrorCellSelection(editor.state.selection)
* ```
*/
function isProseMirrorCellSelection(selection) {
	if (selection === null || typeof selection !== "object") return false;
	return "forEachCell" in selection && typeof selection.forEachCell === "function";
}
/**
* Checks if a value is a ProseMirror document attribute step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror DocAttrStep
* @example ```js
* isProseMirrorDocAttrStep(transaction.steps[0])
* ```
*/
function isProseMirrorDocAttrStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "docAttr") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror fragment by inspecting it
* @param value The value to check
* @returns - A boolean, if true the value is a ProseMirror fragment
* @example ```js
* isProseMirrorFragment(editor.state.doc.content)
* ```
*/
function isProseMirrorFragment(value) {
	if (value === null || typeof value !== "object") return false;
	const fragment = value;
	if (!Array.isArray(fragment.content) || typeof fragment.size !== "number" || typeof fragment.nodesBetween !== "function" || typeof fragment.descendants !== "function" || typeof fragment.textBetween !== "function" || typeof fragment.append !== "function" || typeof fragment.cut !== "function" || typeof fragment.eq !== "function" || typeof fragment.child !== "function" || typeof fragment.forEach !== "function") return false;
	return true;
}
/**
* Check whether a selection exposes a selected node.
*
* @param selection - Selection to inspect.
* @returns Whether the selection is a node selection.
* @example ```js
* isProseMirrorNodeSelection(editor.state.selection)
* ```
*/
function isProseMirrorNodeSelection(selection) {
	if (selection === null || typeof selection !== "object") return false;
	return "node" in selection && selection.node != null;
}
/**
* Checks if a value is a ProseMirror remove mark step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror RemoveMarkStep
* @example ```js
* isProseMirrorRemoveMarkStep(transaction.steps[0])
* ```
*/
function isProseMirrorRemoveMarkStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "removeMark") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror remove node mark step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror RemoveNodeMarkStep
* @example ```js
* isProseMirrorRemoveNodeMarkStep(transaction.steps[0])
* ```
*/
function isProseMirrorRemoveNodeMarkStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "removeNodeMark") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror replace around step
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror ReplaceAroundStep
* @example ```js
* isProseMirrorReplaceAroundStep(transaction.steps[0])
* ```
*/
function isProseMirrorReplaceAroundStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "replaceAround") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror replace step result
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror ReplaceStep
* @example ```js
* isProseMirrorReplaceStep(transaction.steps[0])
* ```
*/
function isProseMirrorReplaceStep(value) {
	if (value === null || typeof value !== "object") return false;
	const step = value;
	if (!isProseMirrorStep(step)) return false;
	const json = step.toJSON();
	if (json === null || typeof json !== "object" || json.stepType !== "replace") return false;
	return true;
}
/**
* Checks if a value is a ProseMirror slice by inspecting it
* @param value The value to check
* @returns - A boolean, if true the value is a ProseMirror slice
* @example ```js
* isProseMirrorSlice(editor.state.doc.content.slice(0, 10))
* ```
*/
function isProseMirrorSlice(value) {
	if (value === null || typeof value !== "object") return false;
	const slice = value;
	const openStartIsNumber = Number.isInteger(slice.openStart) && slice.openStart >= 0;
	const openEndIsNumber = Number.isInteger(slice.openEnd) && slice.openEnd >= 0;
	const sliceHasRequiredProperties = typeof slice.size === "number" && typeof slice.eq === "function" && typeof slice.toJSON === "function";
	if (!openStartIsNumber || !openEndIsNumber || !sliceHasRequiredProperties) return false;
	const content = slice.content;
	if (content === null || typeof content !== "object") return false;
	if (!isProseMirrorFragment(content)) return false;
	return true;
}
/**
* Checks if a value is a ProseMirror step result
* @param value The value to check
* @returns - A boolean, if the boolean is true the value is a ProseMirror StepResult
* @example ```js
* isProseMirrorStepResult(step.apply(editor.state.doc))
* ```
*/
function isProseMirrorStepResult(value) {
	if (value === null || typeof value !== "object") return false;
	const result = value;
	const isValidDoc = result.doc !== null && typeof result.doc === "object" && result.failed === null;
	const isValidFailed = typeof result.failed === "string" && result.doc === null;
	if (!isValidDoc && !isValidFailed) return false;
	return true;
}
/**
* A class that represents a mappable position in the editor. It can be extended
* by other extensions to add additional position mapping capabilities.
*/
var MappablePosition = class MappablePosition {
	constructor(position) {
		this.position = position;
	}
	/**
	* Creates a MappablePosition from a JSON object.
	*/
	static fromJSON(json) {
		return new MappablePosition(json.position);
	}
	/**
	* Converts the MappablePosition to a JSON object.
	*/
	toJSON() {
		return { position: this.position };
	}
};
/**
* Calculates the new position after applying a transaction.
*
* @returns The new mappable position and the map result.
*/
function getUpdatedPosition(position, transaction) {
	const mapResult = transaction.mapping.mapResult(position.position);
	return {
		position: new MappablePosition(mapResult.pos),
		mapResult
	};
}
/**
* Creates a MappablePosition from a position number. This is the default
* implementation for Tiptap core. It can be overridden by other Tiptap
* extensions.
*
* @param position The position (as a number) where the MappablePosition will be created.
* @returns A new MappablePosition instance at the given position.
*/
function createMappablePosition(position) {
	return new MappablePosition(position);
}
function posToDOMRect(view, from, to) {
	const minPos = 0;
	const maxPos = view.state.doc.content.size;
	const resolvedFrom = minMax(from, minPos, maxPos);
	const resolvedEnd = minMax(to, minPos, maxPos);
	const start = view.coordsAtPos(resolvedFrom);
	const end = view.coordsAtPos(resolvedEnd, -1);
	const top = Math.min(start.top, end.top);
	const bottom = Math.max(start.bottom, end.bottom);
	const left = Math.min(start.left, end.left);
	const right = Math.max(start.right, end.right);
	const data = {
		top,
		bottom,
		left,
		right,
		width: right - left,
		height: bottom - top,
		x: left,
		y: top
	};
	return {
		...data,
		toJSON: () => data
	};
}
/**
* The actual implementation of the rewriteUnknownContent function
*/
function rewriteUnknownContentInner({ json, validMarks, validNodes, options, rewrittenContent = [] }) {
	if (json.marks && Array.isArray(json.marks)) json.marks = json.marks.filter((mark) => {
		if (mark === null || mark === void 0) return false;
		const name = typeof mark === "string" ? mark : mark.type;
		if (validMarks.has(name)) return true;
		rewrittenContent.push({
			original: JSON.parse(JSON.stringify(mark)),
			unsupported: name
		});
		return false;
	});
	if (json.content && Array.isArray(json.content)) json.content = json.content.map((value) => {
		if (value === null || value === void 0) return null;
		return rewriteUnknownContentInner({
			json: value,
			validMarks,
			validNodes,
			options,
			rewrittenContent
		}).json;
	}).filter((a) => a !== null && a !== void 0);
	if (json.type && !validNodes.has(json.type)) {
		rewrittenContent.push({
			original: JSON.parse(JSON.stringify(json)),
			unsupported: json.type
		});
		if (json.content && Array.isArray(json.content) && (options === null || options === void 0 ? void 0 : options.fallbackToParagraph) !== false) {
			json.type = "paragraph";
			return {
				json,
				rewrittenContent
			};
		}
		return {
			json: null,
			rewrittenContent
		};
	}
	return {
		json,
		rewrittenContent
	};
}
/**
* Rewrite unknown nodes and marks within JSON content
* Allowing for user within the editor
*/
function rewriteUnknownContent(json, schema, options) {
	return rewriteUnknownContentInner({
		json,
		validNodes: new Set(Object.keys(schema.nodes)),
		validMarks: new Set(Object.keys(schema.marks)),
		options
	});
}
function canSetMark(state, tr, newMarkType) {
	const { selection } = tr;
	let cursor = null;
	if (isTextSelection(selection)) cursor = selection.$cursor;
	if (cursor) {
		var _state$storedMarks;
		const currentMarks = (_state$storedMarks = state.storedMarks) !== null && _state$storedMarks !== void 0 ? _state$storedMarks : cursor.marks();
		return cursor.parent.type.allowsMarkType(newMarkType) && (!!newMarkType.isInSet(currentMarks) || !currentMarks.some((mark) => mark.type.excludes(newMarkType)));
	}
	const { ranges } = selection;
	return ranges.some(({ $from, $to }) => {
		let someNodeSupportsMark = $from.depth === 0 ? state.doc.inlineContent && state.doc.type.allowsMarkType(newMarkType) : false;
		state.doc.nodesBetween($from.pos, $to.pos, (node, _pos, parent) => {
			if (someNodeSupportsMark) return false;
			if (node.isInline) {
				const parentAllowsMarkType = !parent || parent.type.allowsMarkType(newMarkType);
				const currentMarksAllowMarkType = !!newMarkType.isInSet(node.marks) || !node.marks.some((otherMark) => otherMark.type.excludes(newMarkType));
				someNodeSupportsMark = parentAllowsMarkType && currentMarksAllowMarkType;
			}
			return !someNodeSupportsMark;
		});
		return someNodeSupportsMark;
	});
}
var setMark = (typeOrName, attributes = {}) => ({ tr, state, dispatch }) => {
	const { selection } = tr;
	const { empty, ranges } = selection;
	const type = getMarkType(typeOrName, state.schema);
	if (dispatch) if (empty) {
		const oldAttributes = getMarkAttributes(state, type);
		tr.addStoredMark(type.create({
			...oldAttributes,
			...attributes
		}));
	} else ranges.forEach((range) => {
		const from = range.$from.pos;
		const to = range.$to.pos;
		state.doc.nodesBetween(from, to, (node, pos) => {
			const trimmedFrom = Math.max(pos, from);
			const trimmedTo = Math.min(pos + node.nodeSize, to);
			if (node.marks.find((mark) => mark.type === type)) node.marks.forEach((mark) => {
				if (type === mark.type) tr.addMark(trimmedFrom, trimmedTo, type.create({
					...mark.attrs,
					...attributes
				}));
			});
			else tr.addMark(trimmedFrom, trimmedTo, type.create(attributes));
		});
	});
	return canSetMark(state, tr, type);
};
var setMeta = (key, value) => ({ tr }) => {
	tr.setMeta(key, value);
	return true;
};
var setNode = (typeOrName, attributes = {}) => ({ state, dispatch, chain }) => {
	const type = getNodeType(typeOrName, state.schema);
	let attributesToCopy;
	if (state.selection.$anchor.sameParent(state.selection.$head)) attributesToCopy = state.selection.$anchor.parent.attrs;
	if (!type.isTextblock) {
		console.warn("[tiptap warn]: Currently \"setNode()\" only supports text block nodes.");
		return false;
	}
	return chain().command(({ commands }) => {
		if (setBlockType(type, {
			...attributesToCopy,
			...attributes
		})(state)) return true;
		return commands.clearNodes();
	}).command(({ state: updatedState }) => {
		return setBlockType(type, {
			...attributesToCopy,
			...attributes
		})(updatedState, dispatch);
	}).run();
};
var setNodeSelection = (position) => ({ tr, dispatch }) => {
	if (dispatch) {
		const { doc } = tr;
		const from = minMax(position, 0, doc.content.size);
		const selection = NodeSelection.create(doc, from);
		tr.setSelection(selection);
	}
	return true;
};
var setTextDirection = (direction, position) => ({ tr, state, dispatch }) => {
	const { selection } = state;
	let from;
	let to;
	if (typeof position === "number") {
		from = position;
		to = position;
	} else if (position && "from" in position && "to" in position) {
		from = position.from;
		to = position.to;
	} else {
		from = selection.from;
		to = selection.to;
	}
	if (dispatch) tr.doc.nodesBetween(from, to, (node, pos) => {
		if (node.isText) return;
		tr.setNodeMarkup(pos, void 0, {
			...node.attrs,
			dir: direction
		});
	});
	return true;
};
var setTextSelection = (position) => ({ tr, dispatch }) => {
	if (dispatch) {
		const { doc } = tr;
		const { from, to } = typeof position === "number" ? {
			from: position,
			to: position
		} : position;
		const minPos = TextSelection.atStart(doc).from;
		const maxPos = TextSelection.atEnd(doc).to;
		const resolvedFrom = minMax(from, minPos, maxPos);
		const resolvedEnd = minMax(to, minPos, maxPos);
		const selection = TextSelection.create(doc, resolvedFrom, resolvedEnd);
		tr.setSelection(selection);
	}
	return true;
};
var sinkListItem$1 = (typeOrName) => ({ state, dispatch }) => {
	return sinkListItem(getNodeType(typeOrName, state.schema))(state, dispatch);
};
function ensureMarks(state, splittableMarks) {
	const marks = state.storedMarks || state.selection.$to.parentOffset && state.selection.$from.marks();
	if (marks) {
		const filteredMarks = marks.filter((mark) => splittableMarks === null || splittableMarks === void 0 ? void 0 : splittableMarks.includes(mark.type.name));
		state.tr.ensureMarks(filteredMarks);
	}
}
var splitBlock = ({ keepMarks = true } = {}) => ({ tr, state, dispatch, editor }) => {
	const { selection, doc } = tr;
	const { $from, $to } = selection;
	const extensionAttributes = editor.extensionManager.attributes;
	const newAttributes = getSplittedAttributes(extensionAttributes, $from.node().type.name, $from.node().attrs);
	if (selection instanceof NodeSelection && selection.node.isBlock) {
		if (!$from.parentOffset || !canSplit(doc, $from.pos)) return false;
		if (dispatch) {
			if (keepMarks) ensureMarks(state, editor.extensionManager.splittableMarks);
			tr.split($from.pos).scrollIntoView();
		}
		return true;
	}
	if (!$from.parent.isBlock) return false;
	const atEnd = $to.parentOffset === $to.parent.content.size;
	const deflt = $from.depth === 0 ? void 0 : defaultBlockAt($from.node(-1).contentMatchAt($from.indexAfter(-1)));
	let types = atEnd && deflt ? [{
		type: deflt,
		attrs: newAttributes
	}] : void 0;
	let can = canSplit(tr.doc, tr.mapping.map($from.pos), 1, types);
	if (!types && !can && canSplit(tr.doc, tr.mapping.map($from.pos), 1, deflt ? [{ type: deflt }] : void 0)) {
		can = true;
		types = deflt ? [{
			type: deflt,
			attrs: newAttributes
		}] : void 0;
	}
	if (dispatch) {
		if (can) {
			if (selection instanceof TextSelection) tr.deleteSelection();
			tr.split(tr.mapping.map($from.pos), 1, types);
			if (deflt && !atEnd && !$from.parentOffset && $from.parent.type !== deflt) {
				const first = tr.mapping.map($from.before());
				const $first = tr.doc.resolve(first);
				if ($from.node(-1).canReplaceWith($first.index(), $first.index() + 1, deflt)) tr.setNodeMarkup(tr.mapping.map($from.before()), deflt);
			}
		}
		if (keepMarks) ensureMarks(state, editor.extensionManager.splittableMarks);
		tr.scrollIntoView();
	}
	return can;
};
var splitListItem = (typeOrName, overrideAttrs = {}) => ({ tr, state, dispatch, editor }) => {
	const type = getNodeType(typeOrName, state.schema);
	const { $from, $to } = state.selection;
	const node = state.selection.node;
	if (node && node.isBlock || $from.depth < 2 || !$from.sameParent($to)) return false;
	const grandParent = $from.node(-1);
	if (grandParent.type !== type) return false;
	const extensionAttributes = editor.extensionManager.attributes;
	if ($from.parent.content.size === 0 && $from.node(-1).childCount === $from.indexAfter(-1)) {
		if ($from.depth === 2 || $from.node(-3).type !== type || $from.index(-2) !== $from.node(-2).childCount - 1) return false;
		if (dispatch) {
			var _type$contentMatch$de;
			let wrap = Fragment$1.empty;
			const depthBefore = $from.index(-1) ? 1 : $from.index(-2) ? 2 : 3;
			for (let d = $from.depth - depthBefore; d >= $from.depth - 3; d -= 1) wrap = Fragment$1.from($from.node(d).copy(wrap));
			const depthAfter = $from.indexAfter(-1) < $from.node(-2).childCount ? 1 : $from.indexAfter(-2) < $from.node(-3).childCount ? 2 : 3;
			const newNextTypeAttributes = {
				...getSplittedAttributes(extensionAttributes, $from.node().type.name, $from.node().attrs),
				...overrideAttrs
			};
			const nextType = ((_type$contentMatch$de = type.contentMatch.defaultType) === null || _type$contentMatch$de === void 0 ? void 0 : _type$contentMatch$de.createAndFill(newNextTypeAttributes)) || void 0;
			wrap = wrap.append(Fragment$1.from(type.createAndFill(null, nextType) || void 0));
			const start = $from.before($from.depth - (depthBefore - 1));
			tr.replace(start, $from.after(-depthAfter), new Slice(wrap, 4 - depthBefore, 0));
			let sel = -1;
			tr.doc.nodesBetween(start, tr.doc.content.size, (n, pos) => {
				if (sel > -1) return false;
				if (n.isTextblock && n.content.size === 0) sel = pos + 1;
			});
			if (sel > -1) tr.setSelection(TextSelection.near(tr.doc.resolve(sel)));
			tr.scrollIntoView();
		}
		return true;
	}
	const nextType = $to.pos === $from.end() ? grandParent.contentMatchAt(0).defaultType : null;
	const newTypeAttributes = {
		...getSplittedAttributes(extensionAttributes, grandParent.type.name, grandParent.attrs),
		...overrideAttrs
	};
	const newNextTypeAttributes = {
		...getSplittedAttributes(extensionAttributes, $from.node().type.name, $from.node().attrs),
		...overrideAttrs
	};
	tr.delete($from.pos, $to.pos);
	const types = nextType ? [{
		type,
		attrs: newTypeAttributes
	}, {
		type: nextType,
		attrs: newNextTypeAttributes
	}] : [{
		type,
		attrs: newTypeAttributes
	}];
	if (!canSplit(tr.doc, $from.pos, 2)) return false;
	if (dispatch) {
		const { selection, storedMarks } = state;
		const { splittableMarks } = editor.extensionManager;
		const marks = storedMarks || selection.$to.parentOffset && selection.$from.marks();
		tr.split($from.pos, 2, types).scrollIntoView();
		if (!marks || !dispatch) return true;
		const filteredMarks = marks.filter((mark) => splittableMarks.includes(mark.type.name));
		tr.ensureMarks(filteredMarks);
	}
	return true;
};
/**
* Normalise a list type attribute for comparison.
* Treats null, undefined, and "1" as equivalent (the default numeric type).
*/
function normalizeListType(type) {
	return !type || type === "1" ? null : type;
}
/**
* Check if two list type attributes are compatible for joining.
* Lists can only join when they have the same type (both default, or both the same non-default type).
*/
function areListTypesCompatible(typeA, typeB) {
	return normalizeListType(typeA) === normalizeListType(typeB);
}
var joinListBackwards = (tr, listType) => {
	const list = findParentNode((node) => node.type === listType)(tr.selection);
	if (!list) return true;
	const before = tr.doc.resolve(Math.max(0, list.pos - 1)).before(list.depth);
	if (before === void 0) return true;
	const nodeBefore = tr.doc.nodeAt(before);
	if (!(list.node.type === (nodeBefore === null || nodeBefore === void 0 ? void 0 : nodeBefore.type) && canJoin(tr.doc, list.pos))) return true;
	if (!areListTypesCompatible(list.node.attrs.type, nodeBefore === null || nodeBefore === void 0 ? void 0 : nodeBefore.attrs.type)) return true;
	tr.join(list.pos);
	return true;
};
var joinListForwards = (tr, listType) => {
	const list = findParentNode((node) => node.type === listType)(tr.selection);
	if (!list) return true;
	const after = tr.doc.resolve(list.start).after(list.depth);
	if (after === void 0) return true;
	const nodeAfter = tr.doc.nodeAt(after);
	if (!(list.node.type === (nodeAfter === null || nodeAfter === void 0 ? void 0 : nodeAfter.type) && canJoin(tr.doc, after))) return true;
	if (!areListTypesCompatible(list.node.attrs.type, nodeAfter === null || nodeAfter === void 0 ? void 0 : nodeAfter.attrs.type)) return true;
	tr.join(after);
	return true;
};
function createInnerSelectionForWholeDocList(tr) {
	const doc = tr.doc;
	const list = doc.firstChild;
	if (!list) return null;
	const $start = doc.resolve(1);
	const $end = doc.resolve(list.nodeSize - 1);
	return TextSelection.between($start, $end);
}
var toggleList = (listTypeOrName, itemTypeOrName, keepMarks, attributes = {}) => ({ editor, tr, state, dispatch, chain, commands, can }) => {
	const { extensions, splittableMarks } = editor.extensionManager;
	const listType = getNodeType(listTypeOrName, state.schema);
	const itemType = getNodeType(itemTypeOrName, state.schema);
	const { selection, storedMarks } = state;
	const { $from, $to } = selection;
	const range = $from.blockRange($to);
	const marks = storedMarks || selection.$to.parentOffset && selection.$from.marks();
	if (!range) return false;
	const parentList = findParentNode((node) => isList(node.type.name, extensions))(selection);
	const isAllSelection = selection.from === 0 && selection.to === state.doc.content.size;
	const topLevelNodes = state.doc.content.content;
	const soleTopLevelNode = topLevelNodes.length === 1 ? topLevelNodes[0] : null;
	const allSelectionList = isAllSelection && soleTopLevelNode && isList(soleTopLevelNode.type.name, extensions) ? {
		node: soleTopLevelNode,
		pos: 0,
		depth: 0
	} : null;
	const currentList = parentList !== null && parentList !== void 0 ? parentList : allSelectionList;
	const isInsideExistingList = !!parentList && range.depth >= 1 && range.depth - parentList.depth <= 1;
	const hasWholeDocSelectedList = !!allSelectionList;
	if ((isInsideExistingList || hasWholeDocSelectedList) && currentList) {
		if (currentList.node.type === listType) {
			if (isAllSelection && hasWholeDocSelectedList) return chain().command(({ tr: trx, dispatch: disp }) => {
				const nextSelection = createInnerSelectionForWholeDocList(trx);
				if (!nextSelection) return false;
				trx.setSelection(nextSelection);
				if (disp) disp(trx);
				return true;
			}).liftListItem(itemType).run();
			return commands.liftListItem(itemType);
		}
		if (isList(currentList.node.type.name, extensions) && listType.validContent(currentList.node.content)) return chain().command(() => {
			tr.setNodeMarkup(currentList.pos, listType);
			return true;
		}).command(() => joinListBackwards(tr, listType)).command(() => joinListForwards(tr, listType)).run();
	}
	if (!keepMarks || !marks || !dispatch) return chain().command(() => {
		if (can().wrapInList(listType, attributes)) return true;
		return commands.clearNodes();
	}).wrapInList(listType, attributes).command(() => joinListBackwards(tr, listType)).command(() => joinListForwards(tr, listType)).run();
	return chain().command(() => {
		const canWrapInList = can().wrapInList(listType, attributes);
		const filteredMarks = marks.filter((mark) => splittableMarks.includes(mark.type.name));
		tr.ensureMarks(filteredMarks);
		if (canWrapInList) return true;
		return commands.clearNodes();
	}).wrapInList(listType, attributes).command(() => joinListBackwards(tr, listType)).command(() => joinListForwards(tr, listType)).run();
};
var toggleMark = (typeOrName, attributes = {}, options = {}) => ({ state, commands }) => {
	const { extendEmptyMarkRange = false } = options;
	const type = getMarkType(typeOrName, state.schema);
	if (isMarkActive(state, type, attributes)) return commands.unsetMark(type, { extendEmptyMarkRange });
	return commands.setMark(type, attributes);
};
var toggleNode = (typeOrName, toggleTypeOrName, attributes = {}) => ({ state, commands }) => {
	const type = getNodeType(typeOrName, state.schema);
	const toggleType = getNodeType(toggleTypeOrName, state.schema);
	const isActive = isNodeActive(state, type, attributes);
	let attributesToCopy;
	if (state.selection.$anchor.sameParent(state.selection.$head)) attributesToCopy = state.selection.$anchor.parent.attrs;
	if (isActive) return commands.setNode(toggleType, attributesToCopy);
	return commands.setNode(type, {
		...attributesToCopy,
		...attributes
	});
};
var toggleWrap = (typeOrName, attributes = {}) => ({ state, commands }) => {
	const type = getNodeType(typeOrName, state.schema);
	if (isNodeActive(state, type, attributes)) return commands.lift(type);
	return commands.wrapIn(type, attributes);
};
var undoInputRule = () => ({ state, dispatch }) => {
	const plugins = state.plugins;
	for (let i = 0; i < plugins.length; i += 1) {
		const plugin = plugins[i];
		let undoable;
		if (plugin.spec.isInputRules && (undoable = plugin.getState(state))) {
			if (dispatch) {
				const tr = state.tr;
				const toUndo = undoable.transform;
				for (let j = toUndo.steps.length - 1; j >= 0; j -= 1) tr.step(toUndo.steps[j].invert(toUndo.docs[j]));
				if (undoable.text) {
					const marks = tr.doc.resolve(undoable.from).marks();
					tr.replaceWith(undoable.from, undoable.to, state.schema.text(undoable.text, marks));
				} else tr.delete(undoable.from, undoable.to);
			}
			return true;
		}
	}
	return false;
};
var unsetAllMarks = (options = {}) => ({ tr, dispatch, editor }) => {
	const { ignoreClearable = false } = options;
	const { selection } = tr;
	const { empty, ranges } = selection;
	if (empty) return true;
	const { nonClearableMarks } = editor.extensionManager;
	if (dispatch) {
		const clearableMarkTypes = Object.values(editor.schema.marks).filter((markType) => ignoreClearable || !nonClearableMarks.includes(markType.name));
		ranges.forEach((range) => {
			for (const markType of clearableMarkTypes) tr.removeMark(range.$from.pos, range.$to.pos, markType);
		});
	}
	return true;
};
var unsetMark = (typeOrName, options = {}) => ({ tr, state, dispatch }) => {
	const { extendEmptyMarkRange = false } = options;
	const { selection } = tr;
	const type = getMarkType(typeOrName, state.schema);
	const { $from, empty, ranges } = selection;
	if (!dispatch) return true;
	if (empty && extendEmptyMarkRange) {
		var _$from$marks$find;
		let { from, to } = selection;
		const range = getMarkRange($from, type, (_$from$marks$find = $from.marks().find((mark) => mark.type === type)) === null || _$from$marks$find === void 0 ? void 0 : _$from$marks$find.attrs);
		if (range) {
			from = range.from;
			to = range.to;
		}
		tr.removeMark(from, to, type);
	} else ranges.forEach((range) => {
		tr.removeMark(range.$from.pos, range.$to.pos, type);
	});
	tr.removeStoredMark(type);
	return true;
};
var unsetTextDirection = (position) => ({ tr, state, dispatch }) => {
	const { selection } = state;
	let from;
	let to;
	if (typeof position === "number") {
		from = position;
		to = position;
	} else if (position && "from" in position && "to" in position) {
		from = position.from;
		to = position.to;
	} else {
		from = selection.from;
		to = selection.to;
	}
	if (dispatch) tr.doc.nodesBetween(from, to, (node, pos) => {
		if (node.isText) return;
		const newAttrs = { ...node.attrs };
		delete newAttrs.dir;
		tr.setNodeMarkup(pos, void 0, newAttrs);
	});
	return true;
};
var updateAttributes = (typeOrName, attributes = {}) => ({ tr, state, dispatch }) => {
	let nodeType = null;
	let markType = null;
	const schemaType = getSchemaTypeNameByName(typeof typeOrName === "string" ? typeOrName : typeOrName.name, state.schema);
	if (!schemaType) return false;
	if (schemaType === "node") nodeType = getNodeType(typeOrName, state.schema);
	if (schemaType === "mark") markType = getMarkType(typeOrName, state.schema);
	let canUpdate = false;
	tr.selection.ranges.forEach((range) => {
		const from = range.$from.pos;
		const to = range.$to.pos;
		let lastPos;
		let lastNode;
		let trimmedFrom;
		let trimmedTo;
		if (tr.selection.empty) state.doc.nodesBetween(from, to, (node, pos) => {
			if (nodeType && nodeType === node.type) {
				canUpdate = true;
				trimmedFrom = Math.max(pos, from);
				trimmedTo = Math.min(pos + node.nodeSize, to);
				lastPos = pos;
				lastNode = node;
			}
		});
		else state.doc.nodesBetween(from, to, (node, pos) => {
			if (pos < from && nodeType && nodeType === node.type) {
				canUpdate = true;
				trimmedFrom = Math.max(pos, from);
				trimmedTo = Math.min(pos + node.nodeSize, to);
				lastPos = pos;
				lastNode = node;
			}
			if (pos >= from && pos <= to) {
				if (nodeType && nodeType === node.type) {
					canUpdate = true;
					if (dispatch) tr.setNodeMarkup(pos, void 0, {
						...node.attrs,
						...attributes
					});
				}
				if (markType && node.marks.length) node.marks.forEach((mark) => {
					if (markType === mark.type) {
						canUpdate = true;
						if (dispatch) {
							const trimmedFrom2 = Math.max(pos, from);
							const trimmedTo2 = Math.min(pos + node.nodeSize, to);
							tr.addMark(trimmedFrom2, trimmedTo2, markType.create({
								...mark.attrs,
								...attributes
							}));
						}
					}
				});
			}
		});
		if (lastNode) {
			if (lastPos !== void 0 && dispatch) tr.setNodeMarkup(lastPos, void 0, {
				...lastNode.attrs,
				...attributes
			});
			if (markType && lastNode.marks.length) lastNode.marks.forEach((mark) => {
				if (markType === mark.type && dispatch) tr.addMark(trimmedFrom, trimmedTo, markType.create({
					...mark.attrs,
					...attributes
				}));
			});
		}
	});
	return canUpdate;
};
var DECORATION_MANAGER_PLUGIN_KEY = new PluginKey("__tiptap_decorations__");
var updateDecorations = (extensionName) => ({ tr, dispatch }) => {
	if (dispatch) tr.setMeta(DECORATION_MANAGER_PLUGIN_KEY, {
		type: "force",
		name: extensionName
	});
	return true;
};
var wrapIn$1 = (typeOrName, attributes = {}) => ({ state, dispatch }) => {
	return wrapIn(getNodeType(typeOrName, state.schema), attributes)(state, dispatch);
};
var wrapInList$1 = (typeOrName, attributes = {}) => ({ state, dispatch }) => {
	return wrapInList(getNodeType(typeOrName, state.schema), attributes)(state, dispatch);
};
var commands_exports = /* @__PURE__ */ __exportAll({
	blur: () => blur,
	clearContent: () => clearContent,
	clearNodes: () => clearNodes,
	command: () => command,
	createParagraphNear: () => createParagraphNear$1,
	cut: () => cut,
	deleteCurrentNode: () => deleteCurrentNode,
	deleteNode: () => deleteNode,
	deleteRange: () => deleteRange,
	deleteSelection: () => deleteSelection,
	enter: () => enter,
	exitCode: () => exitCode$1,
	extendMarkRange: () => extendMarkRange,
	first: () => first,
	focus: () => focus,
	forEach: () => forEach,
	insertContent: () => insertContent,
	insertContentAt: () => insertContentAt,
	insertDefaultBlock: () => insertDefaultBlock,
	joinBackward: () => joinBackward$1,
	joinDown: () => joinDown$1,
	joinForward: () => joinForward$1,
	joinItemBackward: () => joinItemBackward,
	joinItemForward: () => joinItemForward,
	joinTextblockBackward: () => joinTextblockBackward$1,
	joinTextblockForward: () => joinTextblockForward$1,
	joinUp: () => joinUp$1,
	keyboardShortcut: () => keyboardShortcut,
	lift: () => lift$1,
	liftEmptyBlock: () => liftEmptyBlock$1,
	liftListItem: () => liftListItem$1,
	newlineInCode: () => newlineInCode$1,
	resetAttributes: () => resetAttributes,
	scrollIntoView: () => scrollIntoView,
	selectAll: () => selectAll,
	selectNodeBackward: () => selectNodeBackward$1,
	selectNodeForward: () => selectNodeForward$1,
	selectParentNode: () => selectParentNode$1,
	selectTextblockEnd: () => selectTextblockEnd$1,
	selectTextblockStart: () => selectTextblockStart$1,
	setContent: () => setContent,
	setMark: () => setMark,
	setMeta: () => setMeta,
	setNode: () => setNode,
	setNodeSelection: () => setNodeSelection,
	setTextDirection: () => setTextDirection,
	setTextSelection: () => setTextSelection,
	sinkListItem: () => sinkListItem$1,
	splitBlock: () => splitBlock,
	splitListItem: () => splitListItem,
	toggleList: () => toggleList,
	toggleMark: () => toggleMark,
	toggleNode: () => toggleNode,
	toggleWrap: () => toggleWrap,
	undoInputRule: () => undoInputRule,
	unsetAllMarks: () => unsetAllMarks,
	unsetMark: () => unsetMark,
	unsetTextDirection: () => unsetTextDirection,
	updateAttributes: () => updateAttributes,
	updateDecorations: () => updateDecorations,
	wrapIn: () => wrapIn$1,
	wrapInList: () => wrapInList$1
});
/**
* Tracks which editors are running decoration create() callbacks inside state.apply,
* using a private WeakMap to keep this off the public Editor surface.
*/
var depthByEditor = /* @__PURE__ */ new WeakMap();
/**
* Marks callback as decoration-apply work for editor, so editor.state can warn
* about stale reads. Counts depth to handle nested applies.
*
* @param editor The editor the decorations belong to
* @param callback The work to run inside the scope
* @returns Whatever `callback` returns
* @example
* runInDecorationApplyScope(editor, () => spec.create({ editor, state, view }))
*/
function runInDecorationApplyScope(editor, callback) {
	var _depthByEditor$get;
	depthByEditor.set(editor, ((_depthByEditor$get = depthByEditor.get(editor)) !== null && _depthByEditor$get !== void 0 ? _depthByEditor$get : 0) + 1);
	try {
		return callback();
	} finally {
		var _depthByEditor$get2;
		const remaining = ((_depthByEditor$get2 = depthByEditor.get(editor)) !== null && _depthByEditor$get2 !== void 0 ? _depthByEditor$get2 : 1) - 1;
		if (remaining > 0) depthByEditor.set(editor, remaining);
		else depthByEditor.delete(editor);
	}
}
/**
* Whether the editor is currently inside a decoration apply scope.
*
* @param editor The editor to check
* @returns `true` while decoration create() callbacks run
* @example
* if (isInDecorationApplyScope(editor)) {
*   // reads of editor.state are stale here
* }
*/
function isInDecorationApplyScope(editor) {
	return depthByEditor.has(editor);
}
var EventEmitter = class {
	constructor() {
		this.callbacks = {};
	}
	on(event, fn) {
		if (!this.callbacks[event]) this.callbacks[event] = [];
		this.callbacks[event].push(fn);
		return this;
	}
	emit(event, ...args) {
		const callbacks = this.callbacks[event];
		if (callbacks) callbacks.forEach((callback) => callback.apply(this, args));
		return this;
	}
	off(event, fn) {
		const callbacks = this.callbacks[event];
		if (callbacks) if (fn) this.callbacks[event] = callbacks.filter((callback) => callback !== fn);
		else delete this.callbacks[event];
		return this;
	}
	once(event, fn) {
		const onceFn = (...args) => {
			this.off(event, onceFn);
			fn.apply(this, args);
		};
		return this.on(event, onceFn);
	}
	removeAllListeners() {
		this.callbacks = {};
	}
};
/**
* Whether the editor runs in a development build. Use it to guard warnings so
* bundlers can drop them from production output.
*/
var isDev = typeof process !== "undefined" && true;
function isWidgetDecoration(decoration) {
	return decoration.kind === "widget";
}
/**
* Converts a list of decorations to ProseMirror decorations.
* @param decorations The decorations to convert.
* @param extensionName The name of the extension that created the decorations.
* @returns The converted decorations and the widget keys.
*/
function decorationsToPMDecorations(decorations, extensionName) {
	const pmDecorations = [];
	const widgetKeys = /* @__PURE__ */ new Set();
	for (const decoration of decorations) {
		if (decoration.kind === "widget") {
			if (isWidgetDecoration(decoration)) widgetKeys.add(decoration.key);
		}
		pmDecorations.push(decoration.toPMDecoration(extensionName));
	}
	return {
		decorations: pmDecorations,
		widgetKeys
	};
}
/**
* Builds a DecorationSet from a list of decorations.
* @param doc The document to build the decoration set for.
* @param decorations The decorations to build the set from.
* @param extensionName The name of the extension that created the decorations.
* @returns The built decoration set and the widget keys.
*/
function buildDecorationSet(doc, decorations, extensionName) {
	const { decorations: pmDecorations, widgetKeys } = decorationsToPMDecorations(decorations, extensionName);
	return {
		set: DecorationSet.create(doc, pmDecorations),
		widgetKeys
	};
}
/**
* Whether the block range `[from, to)` owns a decoration at `position`.
*
* `to` is the next block's start, so that block owns it. The last block is the
* exception: nothing follows it, so it owns the end of the document.
*
* @param options The position, the range, and the document size.
* @returns True when the range owns the position.
* @example
* // <p>foo</p><p>bar</p>, blocks [0, 5] and [5, 10]
* rangeOwnsPosition({ position: 5, from: 0, to: 5, docSize: 10 }) // false, block 2 owns it
* rangeOwnsPosition({ position: 5, from: 5, to: 10, docSize: 10 }) // true
* rangeOwnsPosition({ position: 10, from: 5, to: 10, docSize: 10 }) // true, end of document
*/
function rangeOwnsPosition({ position, from, to, docSize }) {
	if (position < from) return false;
	if (position < to) return true;
	return position === to && to === docSize;
}
/**
* Filter decorations the block range `[from, to)` does not own.
* Must match the stale sweep in `DecorationManager.rebuildRanges`.
*
* @param options The decorations, the range, and the warning bookkeeping.
* @returns The filtered decorations.
*/
function filterOutOfRangeDecorations({ decorations, from, to, docSize, extensionName, warnedExtensions }) {
	return decorations.filter((decoration) => {
		if (rangeOwnsPosition({
			position: decoration.anchor,
			from,
			to,
			docSize
		})) return true;
		if (decoration.anchor === to) return false;
		if (!warnedExtensions.has(extensionName)) {
			warnedExtensions.add(extensionName);
			console.warn(`[tiptap warn]: Extension "${extensionName}" returned a decoration outside the requested range [${from}, ${to}). It was ignored.`);
		}
		return false;
	});
}
/**
* Extracts the widget key from a decoration spec.
* @param decoration The decoration to extract the key from.
* @returns The widget key, or undefined if the decoration has no key.
*/
function widgetKeyOf(decoration) {
	var _decoration$spec;
	const key = (_decoration$spec = decoration.spec) === null || _decoration$spec === void 0 ? void 0 : _decoration$spec.key;
	return typeof key === "string" ? key : void 0;
}
/**
* Finds widget keys which are still duplicated in the final decoration set.
* @param decorationSet The merged decoration set to inspect.
* @returns The duplicate keys and their producing extensions.
*/
function findDuplicateWidgetKeys(decorationSet) {
	const extensionsByKey = /* @__PURE__ */ new Map();
	const counts = /* @__PURE__ */ new Map();
	for (const decoration of decorationSet.find()) {
		var _extensionName, _extensionsByKey$get, _counts$get;
		const key = widgetKeyOf(decoration);
		if (!key) continue;
		const extension = (_extensionName = decoration.spec.extensionName) !== null && _extensionName !== void 0 ? _extensionName : "unknown";
		const extensions = (_extensionsByKey$get = extensionsByKey.get(key)) !== null && _extensionsByKey$get !== void 0 ? _extensionsByKey$get : /* @__PURE__ */ new Set();
		extensions.add(extension);
		extensionsByKey.set(key, extensions);
		counts.set(key, ((_counts$get = counts.get(key)) !== null && _counts$get !== void 0 ? _counts$get : 0) + 1);
	}
	return Array.from(extensionsByKey, ([key, extensions]) => ({
		key,
		extensions
	})).filter(({ key }) => {
		var _counts$get2;
		return ((_counts$get2 = counts.get(key)) !== null && _counts$get2 !== void 0 ? _counts$get2 : 0) > 1;
	});
}
/**
* Whether a step only sets a node attribute.
*
* Matched by `jsonID` rather than `instanceof`, which fails when two copies of
* prosemirror-transform are loaded.
*
* @param step The step to check.
* @returns `true` for an `AttrStep`, which carries the target node's position.
* @example
* if (isAttrStep(step)) {
*   rebuildBlockAt(step.pos)
* }
*/
function isAttrStep(step) {
	return step.jsonID === "attr";
}
/**
* Check if a step has a resolvable changed range.
* @param step The step to check.
* @returns True if the step has a resolvable changed range, false otherwise.
*/
function hasResolvableChangedRange(step) {
	let hasMappedRange = false;
	step.getMap().forEach(() => {
		hasMappedRange = true;
	});
	if (hasMappedRange || isAttrStep(step)) return true;
	const positionalStep = step;
	return typeof positionalStep.from === "number" && typeof positionalStep.to === "number";
}
/**
* Expands a changed range to the top-level blocks it touches. Blocks are
* ordered, so the walk stops as soon as it passes the range.
*/
function blockRangeFor(doc, changed) {
	let from = null;
	let to = 0;
	let nodeStart = 0;
	for (let index = 0; index < doc.childCount; index += 1) {
		if (nodeStart > changed.to) break;
		const nodeEnd = nodeStart + doc.child(index).nodeSize;
		if (nodeEnd >= changed.from) {
			if (from === null) from = nodeStart;
			to = nodeEnd;
		}
		nodeStart = nodeEnd;
	}
	return from === null ? null : {
		from,
		to
	};
}
/**
* Returns the top-level block ranges to recompute after a transaction,
* or `{ type: 'full' }` when the whole document must be rebuilt.
* @param tr The transaction to inspect.
* @param doc The new document after the transaction.
* @returns The block ranges to recompute, or a full-recompute signal.
*/
function getRebuildRanges(tr, doc) {
	if (tr.steps.some((step) => !hasResolvableChangedRange(step))) return { type: "full" };
	const newRanges = getChangedRanges(tr).map(({ newRange }) => newRange);
	tr.steps.forEach((step, index) => {
		if (!isAttrStep(step)) return;
		const mapping = tr.mapping.slice(index);
		newRanges.push({
			from: mapping.map(step.pos, -1),
			to: mapping.map(step.pos + 1)
		});
	});
	const ranges = [];
	for (const newRange of newRanges) {
		const blockRange = blockRangeFor(doc, newRange);
		if (blockRange) ranges.push(blockRange);
	}
	ranges.sort((a, b) => a.from - b.from);
	const merged = [];
	for (const range of ranges) {
		const last = merged[merged.length - 1];
		if (last && range.from <= last.to) last.to = Math.max(last.to, range.to);
		else merged.push({ ...range });
	}
	return {
		type: "ranges",
		ranges: merged
	};
}
/**
* Maps a decoration set through a mapping and prunes the keys of any widget
* dropped because its position was deleted.
* @param set The decoration set to map.
* @param mapping The mapping to use.
* @param doc The document to map the set through.
* @param widgetKeys The set of widget keys to prune.
* @returns The mapped decoration set.
*/
function mapDecorationSet(set, mapping, doc, widgetKeys) {
	return set.map(mapping, doc, { onRemove: (removedSpec) => {
		const key = removedSpec === null || removedSpec === void 0 ? void 0 : removedSpec.key;
		if (typeof key === "string") widgetKeys.delete(key);
	} });
}
/**
* Moves an extension's existing decorations to their new positions after a
* transaction, and prunes widget keys for any widget whose position was deleted.
* @param name The name of the decoration extension.
* @param previous The previous decoration manager state.
* @param tr The transaction to map through.
* @returns The updated decoration set and widget keys.
*/
function mapDecorations(name, previous, tr) {
	var _previous$decorationS, _previous$widgetKeysB;
	const previousSet = (_previous$decorationS = previous.decorationSetsByExtension[name]) !== null && _previous$decorationS !== void 0 ? _previous$decorationS : DecorationSet.empty;
	const widgetKeys = new Set((_previous$widgetKeysB = previous.widgetKeysByExtension[name]) !== null && _previous$widgetKeysB !== void 0 ? _previous$widgetKeysB : []);
	return {
		set: mapDecorationSet(previousSet, tr.mapping, tr.doc, widgetKeys),
		widgetKeys
	};
}
/**
* Merges multiple decoration sets into a single decoration set.
* @param doc The document to merge the decoration sets for.
* @param decorationSetsByExtension The decoration sets to merge.
* @returns The merged decoration set.
*/
function mergeDecorationSets(doc, decorationSetsByExtension) {
	const allDecorations = Object.values(decorationSetsByExtension).flatMap((set) => set.find());
	return DecorationSet.create(doc, allDecorations);
}
/**
* Unions all widget keys from multiple extensions into a single set.
* @param widgetKeysByExtension The widget keys to union.
* @returns The unioned widget keys.
*/
function unionWidgetKeys(widgetKeysByExtension) {
	const merged = /* @__PURE__ */ new Set();
	for (const keys of Object.values(widgetKeysByExtension)) for (const key of keys) merged.add(key);
	return merged;
}
/**
* Validates a decoration spec to ensure it follows the correct pattern for its update strategy.
* @param name The name of the extension.
* @param spec The decoration spec to validate
*/
function validateDecorationSpec(name, spec) {
	var _update;
	switch ((_update = spec.update) !== null && _update !== void 0 ? _update : "document") {
		case "document":
			if (spec.createInRange) throw new Error(`[tiptap error]: Extension "${name}" provides createInRange() but does not use the "changedRanges" decoration update strategy.`);
			return;
		case "changedRanges":
			if (!spec.createInRange) throw new Error(`[tiptap error]: Extension "${name}" uses the "changedRanges" decoration update strategy but does not provide createInRange().`);
			return;
		case "manual":
			if (spec.createInRange) throw new Error(`[tiptap error]: Extension "${name}" uses the "manual" decoration update strategy, which is not compatible with createInRange(). createInRange() requires the "changedRanges" strategy.`);
			if (spec.shouldUpdate) throw new Error(`[tiptap error]: Extension "${name}" cannot combine the "manual" decoration update strategy with shouldUpdate().`);
			return;
		default: throw new Error(`[tiptap error]: Extension "${name}" uses an unknown decoration update strategy. Expected "document", "changedRanges", or "manual".`);
	}
}
/**
* Decides whether a decoration spec should be recomputed for a transaction.
* @param spec The decoration spec to check.
* @param props Properties containing editor, transaction, and state.
* @param forced Whether recomputation was forced.
* @returns `true` if the decoration should be recomputed.
*/
function shouldRecomputeDecoration(spec, props, forced) {
	if (forced) return true;
	if (spec.update === "manual") return false;
	return spec.shouldUpdate ? spec.shouldUpdate(props) : props.tr.docChanged;
}
var EMPTY_KEYS = /* @__PURE__ */ new Set();
function liveWidgetKeys(editor) {
	var _editor$extensionMana, _editor$extensionMana2;
	return (_editor$extensionMana = (_editor$extensionMana2 = editor.extensionManager) === null || _editor$extensionMana2 === void 0 || (_editor$extensionMana2 = _editor$extensionMana2.decorationManager) === null || _editor$extensionMana2 === void 0 ? void 0 : _editor$extensionMana2.liveWidgetKeys()) !== null && _editor$extensionMana !== void 0 ? _editor$extensionMana : EMPTY_KEYS;
}
var DecorationManager = class {
	constructor(options) {
		this.warnedWidgetKeys = /* @__PURE__ */ new Set();
		this.warnedOutOfRangeExtensions = /* @__PURE__ */ new Set();
		this.handleBeforeTransaction = ({ nextState }) => {
			const state = DECORATION_MANAGER_PLUGIN_KEY.getState(nextState);
			if (state) this.warnDuplicateWidgetKeys(state);
		};
		this.editor = options.editor;
		this.entries = this.resolveEntries(options.entries);
		this.entries.forEach(({ name, spec }) => validateDecorationSpec(name, spec));
		this.plugin = this.entries.length > 0 ? this.createPlugin() : null;
		this.editor.on("beforeTransaction", this.handleBeforeTransaction);
	}
	destroy() {
		this.editor.off("beforeTransaction", this.handleBeforeTransaction);
	}
	/**
	* Returns the set of live widget keys from all decoration extensions.
	* @returns A readonly set of widget keys
	*/
	liveWidgetKeys() {
		var _DECORATION_MANAGER_P, _DECORATION_MANAGER_P2;
		return (_DECORATION_MANAGER_P = (_DECORATION_MANAGER_P2 = DECORATION_MANAGER_PLUGIN_KEY.getState(this.editor.state)) === null || _DECORATION_MANAGER_P2 === void 0 ? void 0 : _DECORATION_MANAGER_P2.widgetKeys) !== null && _DECORATION_MANAGER_P !== void 0 ? _DECORATION_MANAGER_P : EMPTY_KEYS;
	}
	/**
	* The mounted editor view, or `null` when destroyed. Decoration callbacks
	* must never receive the placeholder view `editor.view` falls back to.
	* @returns The mounted editor view, or `null`
	*/
	get mountedView() {
		return this.editor.isDestroyed ? null : this.editor.view;
	}
	/**
	* Resolves decoration entries by calling the addDecorations function for each extension entry.
	* @param entries The decoration manager entries to resolve
	* @returns An array of resolved decoration entries
	*/
	resolveEntries(entries) {
		const resolved = [];
		for (const { name, addDecorations } of entries) {
			const spec = addDecorations();
			if (spec) resolved.push({
				name,
				spec
			});
		}
		return resolved;
	}
	/**
	* Creates the ProseMirror plugin for managing decorations.
	* @returns A ProseMirror plugin with state management
	*/
	createPlugin() {
		const { editor, entries } = this;
		return new Plugin({
			key: DECORATION_MANAGER_PLUGIN_KEY,
			state: {
				init: (_config, state) => {
					const decorationSetsByExtension = {};
					const widgetKeysByExtension = {};
					for (const { name, spec } of entries) {
						const { set, widgetKeys } = this.buildFullSet(name, spec, state);
						decorationSetsByExtension[name] = set;
						widgetKeysByExtension[name] = widgetKeys;
					}
					const managerState = {
						decorationSetsByExtension,
						widgetKeysByExtension,
						mergedDecorationSet: this.buildMergedSet(state.doc, decorationSetsByExtension),
						widgetKeys: unionWidgetKeys(widgetKeysByExtension)
					};
					this.warnDuplicateWidgetKeys(managerState);
					return managerState;
				},
				apply: (tr, previous, oldState, newState) => {
					const meta = tr.getMeta(DECORATION_MANAGER_PLUGIN_KEY);
					const forceAll = (meta === null || meta === void 0 ? void 0 : meta.type) === "force" && !meta.name;
					const forceName = (meta === null || meta === void 0 ? void 0 : meta.type) === "force" ? meta.name : void 0;
					const decorationSetsByExtension = {};
					const widgetKeysByExtension = {};
					const recomputedNames = /* @__PURE__ */ new Set();
					runInDecorationApplyScope(editor, () => {
						for (const { name, spec } of entries) {
							const forced = forceAll || forceName === name;
							if (!shouldRecomputeDecoration(spec, {
								editor,
								tr,
								oldState,
								newState
							}, forced)) {
								const result = mapDecorations(name, previous, tr);
								decorationSetsByExtension[name] = result.set;
								widgetKeysByExtension[name] = result.widgetKeys;
							} else if (spec.update === "changedRanges" && tr.docChanged && !forced) {
								const result = this.applyChangedRangesRecompute(name, spec, previous, tr, newState);
								decorationSetsByExtension[name] = result.set;
								widgetKeysByExtension[name] = result.widgetKeys;
								recomputedNames.add(name);
							} else {
								const { set, widgetKeys } = this.buildFullSet(name, spec, newState);
								decorationSetsByExtension[name] = set;
								widgetKeysByExtension[name] = widgetKeys;
								recomputedNames.add(name);
							}
						}
					});
					if (recomputedNames.size === 0 && !tr.docChanged) return previous;
					return {
						decorationSetsByExtension,
						widgetKeysByExtension,
						mergedDecorationSet: this.mergeAfterApply({
							entries,
							previous,
							tr,
							decorationSetsByExtension,
							recomputedNames
						}),
						widgetKeys: unionWidgetKeys(widgetKeysByExtension)
					};
				}
			},
			props: { decorations(state) {
				var _DECORATION_MANAGER_P3, _DECORATION_MANAGER_P4;
				return (_DECORATION_MANAGER_P3 = (_DECORATION_MANAGER_P4 = DECORATION_MANAGER_PLUGIN_KEY.getState(state)) === null || _DECORATION_MANAGER_P4 === void 0 ? void 0 : _DECORATION_MANAGER_P4.mergedDecorationSet) !== null && _DECORATION_MANAGER_P3 !== void 0 ? _DECORATION_MANAGER_P3 : DecorationSet.empty;
			} }
		});
	}
	/**
	* Applies changed ranges recomputation to a decoration set, dropping stale decorations and rebuilding only the touched blocks.
	* @param name The name of the decoration extension
	* @param spec The decoration spec
	* @param previous The previous decoration manager state
	* @param tr The transaction to apply
	* @param newState The new editor state
	* @returns The updated decoration set and widget keys
	*/
	applyChangedRangesRecompute(name, spec, previous, tr, newState) {
		const resolution = getRebuildRanges(tr, newState.doc);
		if (resolution.type === "full") return this.buildFullSet(name, spec, newState);
		return this.rebuildRanges(name, spec, previous, tr, newState, resolution.ranges);
	}
	/**
	* Rebuilds decorations for the changed block ranges: maps the previous set
	* forward, then for each range removes stale decorations, calls
	* `createInRange`, and adds the new ones while syncing widget keys.
	* @param name The extension name.
	* @param spec The decoration spec.
	* @param previous The previous decoration manager state.
	* @param tr The transaction to apply.
	* @param newState The new editor state.
	* @param ranges The block ranges to rebuild.
	* @returns The updated decoration set and widget keys.
	*/
	rebuildRanges(name, spec, previous, tr, newState, ranges) {
		var _previous$decorationS, _previous$widgetKeysB;
		const previousSet = (_previous$decorationS = previous.decorationSetsByExtension[name]) !== null && _previous$decorationS !== void 0 ? _previous$decorationS : DecorationSet.empty;
		const widgetKeys = new Set((_previous$widgetKeysB = previous.widgetKeysByExtension[name]) !== null && _previous$widgetKeysB !== void 0 ? _previous$widgetKeysB : []);
		let set = mapDecorationSet(previousSet, tr.mapping, tr.doc, widgetKeys);
		const docSize = newState.doc.content.size;
		for (const { from, to } of ranges) {
			const stale = set.find(from, to).filter((decoration) => rangeOwnsPosition({
				position: decoration.from,
				from,
				to,
				docSize
			}));
			for (const decoration of stale) {
				const key = widgetKeyOf(decoration);
				if (key) widgetKeys.delete(key);
			}
			set = set.remove(stale);
			const { decorations: pmDecorations, widgetKeys: addedKeys } = decorationsToPMDecorations(filterOutOfRangeDecorations({
				decorations: this.runCreate(name, "createInRange", () => spec.createInRange({
					editor: this.editor,
					state: newState,
					view: this.mountedView,
					from,
					to
				})),
				from,
				to,
				docSize,
				extensionName: name,
				warnedExtensions: this.warnedOutOfRangeExtensions
			}), name);
			set = set.add(newState.doc, pmDecorations);
			for (const key of addedKeys) widgetKeys.add(key);
		}
		return {
			set,
			widgetKeys
		};
	}
	/**
	* Builds a full decoration set for the entire document.
	* @param name The name of the decoration extension
	* @param spec The decoration spec
	* @param state The editor state
	* @returns The decoration set and widget keys
	*/
	buildFullSet(name, spec, state) {
		const decorations = this.runCreate(name, "create", () => spec.create({
			editor: this.editor,
			state,
			view: this.mountedView
		}));
		return buildDecorationSet(state.doc, decorations, name);
	}
	/**
	* Runs a decoration callback and swallows anything it throws. These run inside
	* `state.apply`, where an uncaught error would abort the whole transaction.
	* @param name The extension name.
	* @param method The callback name, used in the error message.
	* @param create The callback to run.
	* @returns The decorations, or an empty array if the callback threw.
	*/
	runCreate(name, method, create) {
		try {
			return create();
		} catch (error) {
			console.error(`[tiptap error]: Extension "${name}" threw in \`addDecorations().${method}()\`. Its decorations were dropped for this update.`, error);
			return [];
		}
	}
	warnDuplicateWidgetKeys(state) {
		if (!isDev) return;
		if (state.widgetKeys.size === 0) {
			this.warnedWidgetKeys.clear();
			return;
		}
		const duplicateKeys = findDuplicateWidgetKeys(state.mergedDecorationSet);
		const nextWarningKeys = new Set(duplicateKeys.map(({ key }) => key));
		for (const { key, extensions } of duplicateKeys) {
			if (this.warnedWidgetKeys.has(key)) continue;
			const names = Array.from(extensions).map((name) => `"${name}"`).join(", ");
			console.warn(`[tiptap warn]: Duplicate widget decoration key "${key}" in extension${extensions.size === 1 ? "" : "s"} ${names}. Widget decoration keys must be globally unique, otherwise ProseMirror misplaces the widget DOM. Use a stable, unique key (e.g. \`comment-\${id}\`).`);
		}
		this.warnedWidgetKeys = nextWarningKeys;
	}
	/**
	* Builds the merged DecorationSet during init. Skips the merge for a
	* single extension since its per-extension set is already correct.
	* @param doc The document to build the merged set for.
	* @param decorationSetsByExtension The per-extension decoration sets.
	* @returns The merged decoration set.
	*/
	buildMergedSet(doc, decorationSetsByExtension) {
		const names = Object.keys(decorationSetsByExtension);
		if (names.length === 1) return decorationSetsByExtension[names[0]];
		return mergeDecorationSets(doc, decorationSetsByExtension);
	}
	/**
	* Computes the merged DecorationSet after apply. Single extension skips the
	* merge; nothing recomputed maps the previous merged set forward; otherwise
	* the merge is rebuilt from the per-extension sets.
	*/
	mergeAfterApply({ entries, previous, tr, decorationSetsByExtension, recomputedNames }) {
		if (entries.length === 1) return decorationSetsByExtension[entries[0].name];
		if (recomputedNames.size === 0) return previous.mergedDecorationSet.map(tr.mapping, tr.doc);
		return mergeDecorationSets(tr.doc, decorationSetsByExtension);
	}
};
/**
* Compare two attribute objects for equality.
* Handles null/undefined and asserts key presence in both objects so that
* `{ foo: undefined }` and `{ bar: undefined }` are not treated as equal.
*/
function attrsEqual(a, b) {
	if (a === b) return true;
	if (!a || !b) return false;
	const keysA = Object.keys(a);
	const keysB = Object.keys(b);
	if (keysA.length !== keysB.length) return false;
	return keysA.every((key) => Object.prototype.hasOwnProperty.call(b, key) && Object.is(a[key], b[key]));
}
function canInsertNode(state, nodeType) {
	const { selection } = state;
	const { $from } = selection;
	if (selection instanceof NodeSelection) {
		const index = $from.index();
		return $from.parent.canReplaceWith(index, index + 1, nodeType);
	}
	let depth = $from.depth;
	while (depth >= 0) {
		const index = $from.index(depth);
		if ($from.node(depth).contentMatchAt(index).matchType(nodeType)) return true;
		depth -= 1;
	}
	return false;
}
function createStyleTag(style, nonce, suffix) {
	const tiptapStyleTag = document.querySelector(`style[data-tiptap-style${suffix ? `-${suffix}` : ""}]`);
	if (tiptapStyleTag !== null) return tiptapStyleTag;
	const styleNode = document.createElement("style");
	if (nonce) styleNode.setAttribute("nonce", nonce);
	styleNode.setAttribute(`data-tiptap-style${suffix ? `-${suffix}` : ""}`, "");
	styleNode.innerHTML = style;
	document.getElementsByTagName("head")[0].appendChild(styleNode);
	return styleNode;
}
function escapeForRegEx(string) {
	return string.replace(/[-/\\^$*+?.()|[\]{}]/g, "\\$&");
}
/**
* Read a CSS property value directly from an element's raw inline `style`
* attribute, bypassing the CSSOM (e.g. `element.style.fontFamily`) which
* canonicalizes values and can change formatting. The original format is
* preserved (quotes, hex vs rgb, etc.).
*
* When a property is declared more than once, the last declaration wins —
* this matches CSS cascade order and is useful when nested spans are merged
* and the child's value should take priority.
*
* Property name comparison is case-insensitive.
*
* @param element - The element whose `style` attribute should be read.
* @param propertyName - The CSS property name (e.g. `font-family`).
* @returns The raw value string, or `null` if the property is not present.
*
* @example
* ```ts
* parseHTML: element => getStyleProperty(element, 'font-family')
* ```
*/
function getStyleProperty(element, propertyName) {
	const styleAttr = element.getAttribute("style");
	if (!styleAttr) return null;
	const decls = styleAttr.split(";").map((decl) => decl.trim()).filter(Boolean);
	const target = propertyName.toLowerCase();
	for (let i = decls.length - 1; i >= 0; i -= 1) {
		const decl = decls[i];
		const colonIndex = decl.indexOf(":");
		if (colonIndex === -1) continue;
		if (decl.slice(0, colonIndex).trim().toLowerCase() === target) return decl.slice(colonIndex + 1).trim();
	}
	return null;
}
/**
* Decode common HTML entities in text content so they display as literal
* characters inside the editor.  The decode order matters: `&amp;` must be
* decoded **last** so that doubly-encoded sequences like `&amp;lt;` first
* survive the `&lt;` pass and then correctly become `&lt;` (not `<`).
*/
function decodeHtmlEntities(text) {
	return text.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&amp;/g, "&");
}
/**
* Encode HTML special characters so they roundtrip safely through markdown.
* `&` is encoded **first** to avoid double-encoding the ampersand in other
* entities (e.g. `<` → `&lt;`, not `&amp;lt;`).
*
* Note: `"` is intentionally NOT encoded here because double quotes are
* ordinary characters in markdown and do not need escaping.  The decode
* function still handles `&quot;` because the markdown tokenizer may emit it.
*/
function encodeHtmlEntities(text) {
	return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
/**
* Detects if the current browser is Firefox.
* @returns `true` if the browser is Firefox, `false` otherwise.
* @example
* if (isFirefox()) {
*   // Firefox-specific handling
* }
*/
function isFirefox() {
	return typeof navigator !== "undefined" ? /Firefox/.test(navigator.userAgent) : false;
}
function isNumber(value) {
	return typeof value === "number";
}
function getType(value) {
	return Object.prototype.toString.call(value).slice(8, -1);
}
function isPlainObject(value) {
	if (getType(value) !== "Object") return false;
	return value.constructor === Object && Object.getPrototypeOf(value) === Object.prototype;
}
function isString(value) {
	return typeof value === "string";
}
function isWhitespace(character) {
	return /\s/.test(character);
}
function isAsciiLetter(character) {
	return /^[a-zA-Z]$/.test(character);
}
function isWordCharacter(character) {
	return /^\w$/.test(character);
}
function skipWhitespace(input, index) {
	while (index < input.length && isWhitespace(input[index])) index += 1;
	return index;
}
function skipToWhitespace(input, index) {
	while (index < input.length && !isWhitespace(input[index])) index += 1;
	return index;
}
function readName(input, index, allowHyphen) {
	while (index < input.length && (isWordCharacter(input[index]) || allowHyphen && input[index] === "-")) index += 1;
	return index;
}
function readShorthand(input, index) {
	const prefix = input[index];
	const nameStart = index + 1;
	const nameEnd = readName(input, nameStart, true);
	if (nameEnd === nameStart) return { nextIndex: skipToWhitespace(input, nameEnd) };
	return {
		token: {
			type: prefix === "." ? "class" : "id",
			name: input.slice(nameStart, nameEnd)
		},
		nextIndex: skipToWhitespace(input, nameEnd)
	};
}
function readQuotedSegment(input, index) {
	const closingQuote = input.indexOf(input[index], index + 1);
	return { nextIndex: skipToWhitespace(input, closingQuote === -1 ? index : closingQuote + 1) };
}
function readKeyValue(input, name, index, allowWhitespace) {
	let valueStart = allowWhitespace ? skipWhitespace(input, index) : index;
	if (input[valueStart] !== "=") return;
	valueStart = allowWhitespace ? skipWhitespace(input, valueStart + 1) : valueStart + 1;
	const quote = input[valueStart];
	if (quote !== "\"" && quote !== "'") return;
	const valueEnd = input.indexOf(quote, valueStart + 1);
	if (valueEnd === -1) return;
	return {
		token: {
			type: "keyValue",
			name,
			value: input.slice(valueStart + 1, valueEnd)
		},
		nextIndex: skipToWhitespace(input, valueEnd + 1)
	};
}
function readNamedAttribute(input, index, syntax) {
	const isPandoc = syntax === "pandoc";
	const nameEnd = readName(input, index + 1, isPandoc);
	const name = input.slice(index, nameEnd);
	const keyValue = readKeyValue(input, name, nameEnd, isPandoc);
	if (keyValue) return keyValue;
	const isStandalone = nameEnd === input.length || isWhitespace(input[nameEnd]);
	return {
		token: isPandoc && isStandalone ? {
			type: "boolean",
			name
		} : void 0,
		nextIndex: syntax === "shortcode" ? skipToWhitespace(input, nameEnd) : nameEnd
	};
}
function readAttribute(input, index, syntax) {
	const prefix = input[index];
	if (syntax === "pandoc" && (prefix === "." || prefix === "#")) return readShorthand(input, index);
	if (prefix === "\"" || prefix === "'") return readQuotedSegment(input, index);
	if (syntax === "pandoc" ? isAsciiLetter(prefix) : isWordCharacter(prefix)) return readNamedAttribute(input, index, syntax);
	return { nextIndex: skipToWhitespace(input, index) };
}
function tokenizeAttributes(input, syntax) {
	const tokens = [];
	let index = 0;
	while (index < input.length) {
		index = skipWhitespace(input, index);
		if (index >= input.length) break;
		const result = readAttribute(input, index, syntax);
		if (result.token) tokens.push(result.token);
		index = result.nextIndex;
	}
	return tokens;
}
function applyTokens(tokens) {
	const attributes = {};
	const classes = tokens.filter((token) => token.type === "class").map((token) => token.name);
	const id = tokens.find((token) => token.type === "id");
	if (classes.length > 0) attributes.class = classes.join(" ");
	if (id) attributes.id = id.name;
	tokens.forEach((token) => {
		if (token.type === "keyValue") attributes[token.name] = token.value;
	});
	tokens.forEach((token) => {
		if (token.type === "boolean") attributes[token.name] = true;
	});
	return attributes;
}
/**
* Parses a Pandoc-style attribute string into an object.
*
* @param attrString - The attribute string to parse
* @returns Parsed attributes object
*
* @example
* ```ts
* parseAttributes('.btn #submit disabled type="button"')
* // { class: 'btn', id: 'submit', disabled: true, type: 'button' }
* ```
*/
function parseAttributes(attrString) {
	if (!(attrString === null || attrString === void 0 ? void 0 : attrString.trim())) return {};
	return applyTokens(tokenizeAttributes(attrString, "pandoc"));
}
/**
* Serializes an attributes object to a Pandoc-style attribute string.
*
* @param attributes - The attributes object to serialize
* @returns Serialized attribute string
*
* @example
* ```ts
* serializeAttributes({ class: 'btn primary', id: 'submit', disabled: true, type: 'button' })
* // '.btn .primary #submit disabled type="button"'
* ```
*/
function serializeAttributes(attributes) {
	if (!attributes || Object.keys(attributes).length === 0) return "";
	const parts = [];
	if (attributes.class) String(attributes.class).split(/\s+/).filter(Boolean).forEach((className) => parts.push(`.${className}`));
	if (attributes.id) parts.push(`#${attributes.id}`);
	Object.entries(attributes).forEach(([key, value]) => {
		if (key === "class" || key === "id") return;
		if (value === true) parts.push(key);
		else if (value !== false && value != null) parts.push(`${key}="${String(value)}"`);
	});
	return parts.join(" ");
}
/**
* Creates a complete markdown spec for atomic block nodes using Pandoc syntax.
*
* The generated spec handles:
* - Parsing self-closing blocks with `:::blockName {attributes}`
* - Extracting and parsing attributes
* - Validating required attributes
* - Rendering blocks back to markdown
*
* @param options - Configuration for the atomic block markdown spec
* @returns Complete markdown specification object
*
* @example
* ```ts
* const youtubeSpec = createAtomBlockMarkdownSpec({
*   nodeName: 'youtube',
*   requiredAttributes: ['src'],
*   defaultAttributes: { start: 0 },
*   allowedAttributes: ['src', 'start', 'width', 'height'] // Only these get rendered to markdown
* })
*
* // Usage in extension:
* export const Youtube = Node.create({
*   // ... other config
*   markdown: youtubeSpec
* })
* ```
*/
function createAtomBlockMarkdownSpec(options) {
	const { nodeName, name: markdownName, parseAttributes: parseAttributes$2 = parseAttributes, serializeAttributes: serializeAttributes$2 = serializeAttributes, defaultAttributes = {}, requiredAttributes = [], allowedAttributes } = options;
	const blockName = markdownName || nodeName;
	const filterAttributes = (attrs) => {
		if (!allowedAttributes) return attrs;
		const filtered = {};
		allowedAttributes.forEach((key) => {
			if (key in attrs) filtered[key] = attrs[key];
		});
		return filtered;
	};
	return {
		parseMarkdown: (token, h) => {
			const attrs = {
				...defaultAttributes,
				...token.attributes
			};
			return h.createNode(nodeName, attrs, []);
		},
		markdownTokenizer: {
			name: nodeName,
			level: "block",
			start(src) {
				var _src$match;
				const regex = new RegExp(`^:::${blockName}(?:\\s|$)`, "m");
				const index = (_src$match = src.match(regex)) === null || _src$match === void 0 ? void 0 : _src$match.index;
				return index !== void 0 ? index : -1;
			},
			tokenize(src, _tokens, _lexer) {
				const regex = new RegExp(`^:::${blockName}(?:\\s+\\{([^}]*)\\})?\\s*:::(?:\\n|$)`);
				const match = src.match(regex);
				if (!match) return;
				const attributes = parseAttributes$2(match[1] || "");
				if (requiredAttributes.find((required) => !(required in attributes))) return;
				return {
					type: nodeName,
					raw: match[0],
					attributes
				};
			}
		},
		renderMarkdown: (node) => {
			const attrs = serializeAttributes$2(filterAttributes(node.attrs || {}));
			return `:::${blockName}${attrs ? ` {${attrs}}` : ""} :::`;
		}
	};
}
/**
* Creates a complete markdown spec for block-level nodes using Pandoc syntax.
*
* The generated spec handles:
* - Parsing blocks with `:::blockName {attributes}` syntax
* - Extracting and parsing attributes
* - Rendering blocks back to markdown with proper formatting
* - Nested content support
*
* @param options - Configuration for the block markdown spec
* @returns Complete markdown specification object
*
* @example
* ```ts
* const calloutSpec = createBlockMarkdownSpec({
*   nodeName: 'callout',
*   defaultAttributes: { type: 'info' },
*   allowedAttributes: ['type', 'title'] // Only these get rendered to markdown
* })
*
* // Usage in extension:
* export const Callout = Node.create({
*   // ... other config
*   markdown: calloutSpec
* })
* ```
*/
function createBlockMarkdownSpec(options) {
	const { nodeName, name: markdownName, getContent, parseAttributes: parseAttributes$1 = parseAttributes, serializeAttributes: serializeAttributes$1 = serializeAttributes, defaultAttributes = {}, content = "block", allowedAttributes } = options;
	const blockName = markdownName || nodeName;
	const filterAttributes = (attrs) => {
		if (!allowedAttributes) return attrs;
		const filtered = {};
		allowedAttributes.forEach((key) => {
			if (key in attrs) filtered[key] = attrs[key];
		});
		return filtered;
	};
	return {
		parseMarkdown: (token, h) => {
			let nodeContent;
			if (getContent) {
				const contentResult = getContent(token);
				nodeContent = typeof contentResult === "string" ? [{
					type: "text",
					text: contentResult
				}] : contentResult;
			} else if (content === "block") nodeContent = h.parseChildren(token.tokens || []);
			else nodeContent = h.parseInline(token.tokens || []);
			const attrs = {
				...defaultAttributes,
				...token.attributes
			};
			return h.createNode(nodeName, attrs, nodeContent);
		},
		markdownTokenizer: {
			name: nodeName,
			level: "block",
			start(src) {
				var _src$match;
				const regex = new RegExp(`^:::${blockName}`, "m");
				const index = (_src$match = src.match(regex)) === null || _src$match === void 0 ? void 0 : _src$match.index;
				return index !== void 0 ? index : -1;
			},
			tokenize(src, _tokens, lexer) {
				const openingRegex = new RegExp(`^:::${blockName}(?:\\s+\\{([^}]*)\\})?\\s*\\n`);
				const openingMatch = src.match(openingRegex);
				if (!openingMatch) return;
				const [openingTag, attrString = ""] = openingMatch;
				const attributes = parseAttributes$1(attrString);
				let level = 1;
				const position = openingTag.length;
				let matchedContent = "";
				const blockPattern = /^:::([\w-]*)(\s.*)?/gm;
				const remaining = src.slice(position);
				blockPattern.lastIndex = 0;
				for (;;) {
					var _match$;
					const match = blockPattern.exec(remaining);
					if (match === null) break;
					const matchPos = match.index;
					const blockType = match[1];
					if ((_match$ = match[2]) === null || _match$ === void 0 ? void 0 : _match$.endsWith(":::")) continue;
					if (blockType) level += 1;
					else {
						level -= 1;
						if (level === 0) {
							const rawContent = remaining.slice(0, matchPos);
							matchedContent = rawContent.trim();
							const fullMatch = src.slice(0, position + matchPos + match[0].length);
							let contentTokens = [];
							if (matchedContent) if (content === "block") {
								contentTokens = lexer.blockTokens(rawContent);
								contentTokens.forEach((token) => {
									if (token.text && (!token.tokens || token.tokens.length === 0)) token.tokens = lexer.inlineTokens(token.text);
								});
								while (contentTokens.length > 0) {
									const lastToken = contentTokens[contentTokens.length - 1];
									if (lastToken.type === "paragraph" && (!lastToken.text || lastToken.text.trim() === "")) contentTokens.pop();
									else break;
								}
							} else contentTokens = lexer.inlineTokens(matchedContent);
							return {
								type: nodeName,
								raw: fullMatch,
								attributes,
								content: matchedContent,
								tokens: contentTokens
							};
						}
					}
				}
			}
		},
		renderMarkdown: (node, h) => {
			const attrs = serializeAttributes$1(filterAttributes(node.attrs || {}));
			return `:::${blockName}${attrs ? ` {${attrs}}` : ""}\n\n${h.renderChildren(node.content || [], "\n\n")}\n\n:::`;
		}
	};
}
function parseShortcodeAttributes(attrString) {
	const attributes = {};
	tokenizeAttributes(attrString, "shortcode").forEach((token) => {
		if (token.type === "keyValue") attributes[token.name] = token.value;
	});
	return attributes;
}
/**
* Serialize attributes back to shortcode format
* Always quotes all values with double quotes
*/
function serializeShortcodeAttributes(attrs) {
	return Object.entries(attrs).filter(([, value]) => value !== void 0 && value !== null).map(([key, value]) => `${key}="${value}"`).join(" ");
}
/**
* Creates a complete markdown spec for inline nodes using attribute syntax.
*
* The generated spec handles:
* - Parsing shortcode syntax with `[nodeName attributes]content[/nodeName]` format
* - Self-closing shortcodes like `[emoji name=party_popper]`
* - Extracting and parsing attributes from the opening tag
* - Rendering inline elements back to shortcode markdown
* - Supporting both content-based and self-closing inline elements
*
* @param options - Configuration for the inline markdown spec
* @returns Complete markdown specification object
*
* @example
* ```ts
* // Self-closing mention: [mention id="madonna" label="Madonna"]
* const mentionSpec = createInlineMarkdownSpec({
*   nodeName: 'mention',
*   selfClosing: true,
*   defaultAttributes: { type: 'user' },
*   allowedAttributes: ['id', 'label'] // Only these get rendered to markdown
* })
*
* // Self-closing emoji: [emoji name="party_popper"]
* const emojiSpec = createInlineMarkdownSpec({
*   nodeName: 'emoji',
*   selfClosing: true,
*   allowedAttributes: ['name']
* })
*
* // With content: [highlight color="yellow"]text[/highlight]
* const highlightSpec = createInlineMarkdownSpec({
*   nodeName: 'highlight',
*   selfClosing: false,
*   allowedAttributes: ['color', 'style']
* })
*
* // Usage in extension:
* export const Mention = Node.create({
*   name: 'mention', // Must match nodeName
*   // ... other config
*   markdown: mentionSpec
* })
* ```
*/
function createInlineMarkdownSpec(options) {
	const { nodeName, name: shortcodeName, getContent, parseAttributes = parseShortcodeAttributes, serializeAttributes = serializeShortcodeAttributes, defaultAttributes = {}, selfClosing = false, allowedAttributes } = options;
	const shortcode = shortcodeName || nodeName;
	const filterAttributes = (attrs) => {
		if (!allowedAttributes) return attrs;
		const filtered = {};
		allowedAttributes.forEach((attr) => {
			const attrName = typeof attr === "string" ? attr : attr.name;
			const skipIfDefault = typeof attr === "string" ? void 0 : attr.skipIfDefault;
			if (attrName in attrs) {
				const value = attrs[attrName];
				if (skipIfDefault !== void 0 && value === skipIfDefault) return;
				filtered[attrName] = value;
			}
		});
		return filtered;
	};
	const escapedShortcode = shortcode.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
	return {
		parseMarkdown: (token, h) => {
			const attrs = {
				...defaultAttributes,
				...token.attributes
			};
			if (selfClosing) return h.createNode(nodeName, attrs);
			const content = getContent ? getContent(token) : token.content || "";
			if (content) return h.createNode(nodeName, attrs, [h.createTextNode(content)]);
			return h.createNode(nodeName, attrs, []);
		},
		markdownTokenizer: {
			name: nodeName,
			level: "inline",
			start(src) {
				const startPattern = selfClosing ? new RegExp(`\\[${escapedShortcode}\\s*[^\\]]*\\]`) : new RegExp(`\\[${escapedShortcode}\\s*[^\\]]*\\][\\s\\S]*?\\[\\/${escapedShortcode}\\]`);
				const match = src.match(startPattern);
				const index = match === null || match === void 0 ? void 0 : match.index;
				return index !== void 0 ? index : -1;
			},
			tokenize(src, _tokens, _lexer) {
				const tokenPattern = selfClosing ? new RegExp(`^\\[${escapedShortcode}\\s*([^\\]]*)\\]`) : new RegExp(`^\\[${escapedShortcode}\\s*([^\\]]*)\\]([\\s\\S]*?)\\[\\/${escapedShortcode}\\]`);
				const match = src.match(tokenPattern);
				if (!match) return;
				let content = "";
				let attrString = "";
				if (selfClosing) {
					const [, attrs] = match;
					attrString = attrs;
				} else {
					const [, attrs, contentMatch] = match;
					attrString = attrs;
					content = contentMatch || "";
				}
				const attributes = parseAttributes(attrString.trim());
				return {
					type: nodeName,
					raw: match[0],
					content: content.trim(),
					attributes
				};
			}
		},
		renderMarkdown: (node) => {
			let content = "";
			if (getContent) content = getContent(node);
			else if (node.content && node.content.length > 0) content = node.content.filter((child) => child.type === "text").map((child) => child.text).join("");
			const attrs = serializeAttributes(filterAttributes(node.attrs || {}));
			const attrString = attrs ? ` ${attrs}` : "";
			if (selfClosing) return `[${shortcode}${attrString}]`;
			return `[${shortcode}${attrString}]${content}[/${shortcode}]`;
		}
	};
}
/**
* Parses markdown text into hierarchical indented blocks with proper nesting.
*
* This utility handles:
* - Line-by-line parsing with pattern matching
* - Hierarchical nesting based on indentation levels
* - Nested content collection and parsing
* - Empty line handling
* - Content dedenting for nested blocks
*
* The key difference from flat parsing is that this maintains the hierarchical
* structure where nested items become `nestedTokens` of their parent items,
* rather than being flattened into a single array.
*
* @param src - The markdown source text to parse
* @param config - Configuration object defining how to parse and create tokens
* @param lexer - Markdown lexer for parsing nested content
* @returns Parsed result with hierarchical items, or undefined if no matches
*
* @example
* ```ts
* const result = parseIndentedBlocks(src, {
*   itemPattern: /^(\s*)([-+*])\s+\[([ xX])\]\s+(.*)$/,
*   extractItemData: (match) => ({
*     indentLevel: match[1].length,
*     mainContent: match[4],
*     checked: match[3].toLowerCase() === 'x'
*   }),
*   createToken: (data, nestedTokens) => ({
*     type: 'taskItem',
*     checked: data.checked,
*     text: data.mainContent,
*     nestedTokens
*   })
* }, lexer)
* ```
*/
function parseIndentedBlocks(src, config, lexer) {
	const lines = src.split("\n");
	const items = [];
	let totalRaw = "";
	let i = 0;
	const baseIndentSize = config.baseIndentSize || 2;
	while (i < lines.length) {
		const currentLine = lines[i];
		const itemMatch = currentLine.match(config.itemPattern);
		if (!itemMatch) if (items.length > 0) break;
		else if (currentLine.trim() === "") {
			i += 1;
			totalRaw = `${totalRaw}${currentLine}\n`;
			continue;
		} else return;
		const itemData = config.extractItemData(itemMatch);
		const { indentLevel, mainContent } = itemData;
		totalRaw = `${totalRaw}${currentLine}\n`;
		const itemContent = [mainContent];
		i += 1;
		while (i < lines.length) {
			var _nextLine$match;
			const nextLine = lines[i];
			if (nextLine.trim() === "") {
				var _nextNonEmpty$match;
				const nextNonEmptyIndex = lines.slice(i + 1).findIndex((l) => l.trim() !== "");
				if (nextNonEmptyIndex === -1) break;
				if ((((_nextNonEmpty$match = lines[i + 1 + nextNonEmptyIndex].match(/^(\s*)/)) === null || _nextNonEmpty$match === void 0 || (_nextNonEmpty$match = _nextNonEmpty$match[1]) === null || _nextNonEmpty$match === void 0 ? void 0 : _nextNonEmpty$match.length) || 0) > indentLevel) {
					itemContent.push(nextLine);
					totalRaw = `${totalRaw}${nextLine}\n`;
					i += 1;
					continue;
				} else break;
			}
			if ((((_nextLine$match = nextLine.match(/^(\s*)/)) === null || _nextLine$match === void 0 || (_nextLine$match = _nextLine$match[1]) === null || _nextLine$match === void 0 ? void 0 : _nextLine$match.length) || 0) > indentLevel) {
				itemContent.push(nextLine);
				totalRaw = `${totalRaw}${nextLine}\n`;
				i += 1;
			} else break;
		}
		let nestedTokens;
		const nestedContent = itemContent.slice(1);
		if (nestedContent.length > 0) {
			const dedentedNested = nestedContent.map((nestedLine) => nestedLine.slice(indentLevel + baseIndentSize)).join("\n");
			if (dedentedNested.trim()) if (config.customNestedParser) nestedTokens = config.customNestedParser(dedentedNested);
			else nestedTokens = lexer.blockTokens(dedentedNested);
		}
		const token = config.createToken(itemData, nestedTokens);
		items.push(token);
	}
	if (items.length === 0) return;
	return {
		items,
		raw: totalRaw
	};
}
/**
* @fileoverview Utility functions for rendering nested content in markdown.
*
* This module provides reusable utilities for extensions that need to render
* content with a prefix on the main line and properly indented nested content.
*/
/**
* Utility function for rendering content with a main line prefix and nested indented content.
*
* This function handles the common pattern of rendering content with:
* 1. A main line with a prefix (like "- " for lists, "> " for blockquotes, etc.)
* 2. Nested content that gets indented properly
*
* @param node - The ProseMirror node representing the content
* @param h - The markdown renderer helper
* @param prefixOrGenerator - Either a string prefix or a function that generates the prefix from context
* @param ctx - Optional context object (used when prefixOrGenerator is a function)
* @param options - Optional rendering options
* @param options.alignNestedToPrefix - Indent nested content to the width of the prefix
* instead of the configured indent size, when the configured one is narrower
* @returns The rendered markdown string
*
* @example
* ```ts
* // For a bullet list item with static prefix
* return renderNestedMarkdownContent(node, h, '- ')
*
* // For a task item with static prefix
* const prefix = `- [${node.attrs?.checked ? 'x' : ' '}] `
* return renderNestedMarkdownContent(node, h, prefix)
*
* // For an ordered list item, where the nested block has to line up with the marker
* return renderNestedMarkdownContent(node, h, '10. ', ctx, { alignNestedToPrefix: true })
*
* // For a blockquote with static prefix
* return renderNestedMarkdownContent(node, h, '> ')
*
* // For content with dynamic prefix based on context
* return renderNestedMarkdownContent(node, h, ctx => {
*   if (ctx.parentType === 'orderedList') {
*     return `${ctx.index + 1}. `
*   }
*   return '- '
* }, ctx)
*
* // Custom extension example
* const CustomContainer = Node.create({
*   name: 'customContainer',
*   // ... other config
*   markdown: {
*     render: (node, h) => {
*       const type = node.attrs?.type || 'info'
*       return renderNestedMarkdownContent(node, h, `[${type}] `)
*     }
*   }
* })
* ```
*/
var TAB_STOP = 4;
/** Width of indentation in Markdown columns, where a tab runs to the next tab stop. */
function columnWidth(text) {
	let width = 0;
	for (const character of text) width = character === "	" ? width + TAB_STOP - width % TAB_STOP : width + 1;
	return width;
}
function renderNestedMarkdownContent(node, h, prefixOrGenerator, ctx, options) {
	if (!node || !Array.isArray(node.content)) return "";
	const prefix = typeof prefixOrGenerator === "function" ? prefixOrGenerator(ctx) : prefixOrGenerator;
	const [content, ...children] = node.content;
	let output = `${prefix}${h.renderChildren([content])}`;
	if (children && children.length > 0) children.forEach((child, index) => {
		var _h$renderChild, _h$renderChild2;
		const childContent = (_h$renderChild = (_h$renderChild2 = h.renderChild) === null || _h$renderChild2 === void 0 ? void 0 : _h$renderChild2.call(h, child, index + 1)) !== null && _h$renderChild !== void 0 ? _h$renderChild : h.renderChildren([child]);
		if (childContent !== void 0 && childContent !== null) {
			const indentLine = (line) => {
				if (!(options === null || options === void 0 ? void 0 : options.alignNestedToPrefix)) return h.indent(line);
				const configured = h.indent("");
				const prefixWidth = columnWidth(prefix);
				return (columnWidth(configured) >= prefixWidth ? configured : " ".repeat(prefixWidth)) + line;
			};
			const indentedChild = childContent.split("\n").map((line) => line ? indentLine(line) : indentLine("")).join("\n");
			output += child.type === "paragraph" ? `\n\n${indentedChild}` : `\n${indentedChild}`;
		}
	});
	return output;
}
var markdown_exports = /* @__PURE__ */ __exportAll({
	createAtomBlockMarkdownSpec: () => createAtomBlockMarkdownSpec,
	createBlockMarkdownSpec: () => createBlockMarkdownSpec,
	createInlineMarkdownSpec: () => createInlineMarkdownSpec,
	parseAttributes: () => parseAttributes,
	parseIndentedBlocks: () => parseIndentedBlocks,
	renderNestedMarkdownContent: () => renderNestedMarkdownContent,
	serializeAttributes: () => serializeAttributes
});
function markTypeName(mark) {
	return typeof mark.type === "string" ? mark.type : mark.type.name;
}
/**
* Compare two arrays of mark objects for equality (order-insensitive).
* Marks are matched by type name and attributes (via attrsEqual),
* so key ordering in attrs does not matter, nor does mark array order.
*/
function marksEqual(a, b) {
	if (a.length !== b.length) return false;
	const consumed = Array.from({ length: b.length }, () => false);
	return a.every((markA) => {
		const nameA = markTypeName(markA);
		const idx = b.findIndex((markB, i) => !consumed[i] && nameA === markTypeName(markB) && attrsEqual(markA.attrs, markB.attrs));
		if (idx === -1) return false;
		consumed[idx] = true;
		return true;
	});
}
function mergeDeep(target, source) {
	const output = { ...target };
	if (isPlainObject(target) && isPlainObject(source)) Object.keys(source).forEach((key) => {
		if (isPlainObject(source[key]) && isPlainObject(target[key])) output[key] = mergeDeep(target[key], source[key]);
		else output[key] = source[key];
	});
	return output;
}
function updateMarkViewAttributes(checkMark, editor, attrs = {}) {
	const { state } = editor;
	const { doc, tr } = state;
	const thisMark = checkMark;
	doc.descendants((node, pos) => {
		const from = tr.mapping.map(pos);
		const to = tr.mapping.map(pos) + node.nodeSize;
		let foundMark = null;
		node.marks.forEach((mark) => {
			if (mark !== thisMark) return false;
			foundMark = mark;
		});
		if (!foundMark) return;
		let needsUpdate = false;
		Object.keys(attrs).forEach((k) => {
			if (attrs[k] !== foundMark.attrs[k]) needsUpdate = true;
		});
		if (needsUpdate) {
			const updatedMark = checkMark.type.create({
				...checkMark.attrs,
				...attrs
			});
			tr.removeMark(from, to, checkMark.type);
			tr.addMark(from, to, updatedMark);
		}
	});
	if (tr.docChanged) editor.view.dispatch(tr);
}
var MarkView = class {
	constructor(component, props, options) {
		this.component = component;
		this.editor = props.editor;
		this.options = { ...options };
		this.mark = props.mark;
		this.HTMLAttributes = props.HTMLAttributes;
	}
	get dom() {
		return this.editor.view.dom;
	}
	get contentDOM() {
		return null;
	}
	/**
	* Update the attributes of the mark in the document.
	* @param attrs The attributes to update.
	*/
	updateAttributes(attrs, checkMark) {
		updateMarkViewAttributes(checkMark || this.mark, this.editor, attrs);
	}
	ignoreMutation(mutation) {
		if (!this.dom || !this.contentDOM) return true;
		if (typeof this.options.ignoreMutation === "function") return this.options.ignoreMutation({ mutation });
		if (mutation.type === "selection") return false;
		if (this.contentDOM.contains(mutation.target) && mutation.type === "childList" && (isiOS() || isAndroid()) && this.editor.isFocused) {
			if ([...Array.from(mutation.addedNodes), ...Array.from(mutation.removedNodes)].every((node) => node.isContentEditable)) return false;
		}
		if (this.contentDOM === mutation.target && mutation.type === "attributes") return true;
		if (this.contentDOM.contains(mutation.target)) return false;
		return true;
	}
};
var InputRule = class {
	constructor(config) {
		var _config$undoable;
		this.find = config.find;
		this.handler = config.handler;
		this.undoable = (_config$undoable = config.undoable) !== null && _config$undoable !== void 0 ? _config$undoable : true;
	}
};
var inputRuleMatcherHandler = (text, find) => {
	if (isRegExp(find)) return find.exec(text);
	const inputRuleMatch = find(text);
	if (!inputRuleMatch) return null;
	const result = [inputRuleMatch.text];
	result.index = inputRuleMatch.index;
	result.input = text;
	result.data = inputRuleMatch.data;
	if (inputRuleMatch.replaceWith) {
		if (!inputRuleMatch.text.includes(inputRuleMatch.replaceWith)) console.warn("[tiptap warn]: \"inputRuleMatch.replaceWith\" must be part of \"inputRuleMatch.text\".");
		result.push(inputRuleMatch.replaceWith);
	}
	return result;
};
function run$1(config) {
	var _ref;
	const { editor, from, to, text, rules, plugin } = config;
	const { view } = editor;
	if (view.composing) return false;
	const $from = view.state.doc.resolve(from);
	if ($from.parent.type.spec.code || !!((_ref = $from.nodeBefore || $from.nodeAfter) === null || _ref === void 0 ? void 0 : _ref.marks.find((mark) => mark.type.spec.code))) return false;
	let matched = false;
	const textBefore = getTextContentFromNodes($from) + text;
	rules.forEach((rule) => {
		if (matched) return;
		const match = inputRuleMatcherHandler(textBefore, rule.find);
		if (!match) return;
		const matchedDocLength = match[0].length - text.length;
		if (matchedDocLength > 0) {
			const matchStartOffset = $from.parentOffset - matchedDocLength;
			if (matchStartOffset < 0 || $from.parent.textBetween(matchStartOffset, $from.parentOffset) !== match[0].slice(0, matchedDocLength)) return;
		}
		const tr = view.state.tr;
		const state = createChainableState({
			state: view.state,
			transaction: tr
		});
		const range = {
			from: from - (match[0].length - text.length),
			to
		};
		const { commands, chain, can } = new CommandManager({
			editor,
			state
		});
		if (rule.handler({
			state,
			range,
			match,
			commands,
			chain,
			can
		}) === null || !tr.steps.length) return;
		if (rule.undoable) tr.setMeta(plugin, {
			transform: tr,
			from,
			to,
			text
		});
		view.dispatch(tr);
		matched = true;
	});
	return matched;
}
/**
* Create an input rules plugin. When enabled, it will cause text
* input that matches any of the given rules to trigger the rule’s
* action.
*/
function inputRulesPlugin(props) {
	const { editor, rules } = props;
	const plugin = new Plugin({
		state: {
			init() {
				return null;
			},
			apply(tr, prev, state) {
				const stored = tr.getMeta(plugin);
				if (stored) return stored;
				const simulatedInputMeta = tr.getMeta("applyInputRules");
				if (!!simulatedInputMeta) setTimeout(() => {
					let { text } = simulatedInputMeta;
					if (typeof text === "string") text = text;
					else text = getHTMLFromFragment(Fragment$1.from(text), state.schema);
					const { from } = simulatedInputMeta;
					run$1({
						editor,
						from,
						to: from + text.length,
						text,
						rules,
						plugin
					});
				});
				return tr.selectionSet || tr.docChanged ? null : prev;
			}
		},
		props: {
			handleTextInput(view, from, to, text) {
				return run$1({
					editor,
					from,
					to,
					text,
					rules,
					plugin
				});
			},
			handleDOMEvents: { compositionend: (view) => {
				setTimeout(() => {
					const { $cursor } = view.state.selection;
					if ($cursor) run$1({
						editor,
						from: $cursor.pos,
						to: $cursor.pos,
						text: "",
						rules,
						plugin
					});
				});
				return false;
			} },
			handleKeyDown(view, event) {
				if (event.key !== "Enter") return false;
				const { $cursor } = view.state.selection;
				if ($cursor) return run$1({
					editor,
					from: $cursor.pos,
					to: $cursor.pos,
					text: "\n",
					rules,
					plugin
				});
				return false;
			}
		},
		isInputRules: true
	});
	return plugin;
}
var Extendable = class {
	constructor(config = {}) {
		this.type = "extendable";
		this.parent = null;
		this.child = null;
		this.name = "";
		this.config = { name: this.name };
		this.config = {
			...this.config,
			...config
		};
		this.name = this.config.name;
	}
	get options() {
		return { ...callOrReturn(getExtensionField(this, "addOptions", { name: this.name })) };
	}
	get storage() {
		return { ...callOrReturn(getExtensionField(this, "addStorage", {
			name: this.name,
			options: this.options
		})) };
	}
	configure(options = {}) {
		const extension = this.extend({
			...this.config,
			addOptions: () => {
				return mergeDeep(this.options, options);
			}
		});
		extension.name = this.name;
		extension.parent = this.parent;
		this.child = null;
		return extension;
	}
	extend(extendedConfig = {}) {
		const extension = new this.constructor({
			...this.config,
			...extendedConfig
		});
		extension.parent = this;
		this.child = extension;
		extension.name = "name" in extendedConfig ? extendedConfig.name : extension.parent.name;
		return extension;
	}
};
/**
* The Mark class is used to create custom mark extensions.
* @see https://tiptap.dev/api/extensions#create-a-new-extension
*/
var Mark = class Mark extends Extendable {
	constructor(..._args) {
		super(..._args);
		this.type = "mark";
	}
	/**
	* Create a new Mark instance
	* @param config - Mark configuration object or a function that returns a configuration object
	*/
	static create(config = {}) {
		return new Mark(typeof config === "function" ? config() : config);
	}
	static handleExit({ editor, mark }) {
		const { tr } = editor.state;
		const currentPos = editor.state.selection.$from;
		if (currentPos.pos === currentPos.end()) {
			const currentMarks = currentPos.marks();
			if (!!!currentMarks.find((m) => (m === null || m === void 0 ? void 0 : m.type.name) === mark.name)) return false;
			const removeMark = currentMarks.find((m) => (m === null || m === void 0 ? void 0 : m.type.name) === mark.name);
			if (removeMark) tr.removeStoredMark(removeMark);
			tr.insertText(" ", currentPos.pos);
			editor.view.dispatch(tr);
			return true;
		}
		return false;
	}
	configure(options) {
		return super.configure(options);
	}
	extend(extendedConfig) {
		const resolvedConfig = typeof extendedConfig === "function" ? extendedConfig() : extendedConfig;
		return super.extend(resolvedConfig);
	}
};
/**
* Paste rules are used to react to pasted content.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#paste-rules
*/
var PasteRule = class {
	constructor(config) {
		this.find = config.find;
		this.handler = config.handler;
	}
};
var pasteRuleMatcherHandler = (text, find, event) => {
	if (isRegExp(find)) return [...text.matchAll(find)];
	const matches = find(text, event);
	if (!matches) return [];
	return matches.map((pasteRuleMatch) => {
		const result = [pasteRuleMatch.text];
		result.index = pasteRuleMatch.index;
		result.input = text;
		result.data = pasteRuleMatch.data;
		if (pasteRuleMatch.replaceWith) {
			if (!pasteRuleMatch.text.includes(pasteRuleMatch.replaceWith)) console.warn("[tiptap warn]: \"pasteRuleMatch.replaceWith\" must be part of \"pasteRuleMatch.text\".");
			result.push(pasteRuleMatch.replaceWith);
		}
		return result;
	});
};
function run(config) {
	const { editor, state, from, to, rule, pasteEvent, dropEvent } = config;
	const { commands, chain, can } = new CommandManager({
		editor,
		state
	});
	const handlers = [];
	state.doc.nodesBetween(from, to, (node, pos) => {
		var _node$type, _ref, _node$content$size, _node$content;
		if (((_node$type = node.type) === null || _node$type === void 0 || (_node$type = _node$type.spec) === null || _node$type === void 0 ? void 0 : _node$type.code) || !(node.isText || node.isTextblock || node.isInline)) return;
		const contentSize = (_ref = (_node$content$size = (_node$content = node.content) === null || _node$content === void 0 ? void 0 : _node$content.size) !== null && _node$content$size !== void 0 ? _node$content$size : node.nodeSize) !== null && _ref !== void 0 ? _ref : 0;
		const resolvedFrom = Math.max(from, pos);
		const resolvedTo = Math.min(to, pos + contentSize);
		if (resolvedFrom >= resolvedTo) return;
		pasteRuleMatcherHandler(node.isText ? node.text || "" : node.textBetween(resolvedFrom - pos, resolvedTo - pos, void 0, "￼"), rule.find, pasteEvent).forEach((match) => {
			if (match.index === void 0) return;
			const start = resolvedFrom + match.index + 1;
			const end = start + match[0].length;
			const range = {
				from: state.tr.mapping.map(start),
				to: state.tr.mapping.map(end)
			};
			const handler = rule.handler({
				state,
				range,
				match,
				commands,
				chain,
				can,
				pasteEvent,
				dropEvent
			});
			handlers.push(handler);
		});
	});
	return handlers.every((handler) => handler !== null);
}
var tiptapDragFromOtherEditor = null;
var createClipboardPasteEvent = (text) => {
	var _event$clipboardData;
	const event = new ClipboardEvent("paste", { clipboardData: new DataTransfer() });
	(_event$clipboardData = event.clipboardData) === null || _event$clipboardData === void 0 || _event$clipboardData.setData("text/html", text);
	return event;
};
/**
* Create an paste rules plugin. When enabled, it will cause pasted
* text that matches any of the given rules to trigger the rule’s
* action.
*/
function pasteRulesPlugin(props) {
	const { editor, rules } = props;
	let dragSourceElement = null;
	let isPastedFromProseMirror = false;
	let isDroppedFromProseMirror = false;
	let pasteEvent = typeof ClipboardEvent !== "undefined" ? new ClipboardEvent("paste") : null;
	let dropEvent;
	try {
		dropEvent = typeof DragEvent !== "undefined" ? new DragEvent("drop") : null;
	} catch {
		dropEvent = null;
	}
	const processEvent = ({ state, from, to, rule, pasteEvt }) => {
		const tr = state.tr;
		if (!run({
			editor,
			state: createChainableState({
				state,
				transaction: tr
			}),
			from: Math.max(from - 1, 0),
			to: to.b - 1,
			rule,
			pasteEvent: pasteEvt,
			dropEvent
		}) || !tr.steps.length) return;
		try {
			dropEvent = typeof DragEvent !== "undefined" ? new DragEvent("drop") : null;
		} catch {
			dropEvent = null;
		}
		pasteEvent = typeof ClipboardEvent !== "undefined" ? new ClipboardEvent("paste") : null;
		return tr;
	};
	return rules.map((rule) => {
		return new Plugin({
			view(view) {
				const handleDragstart = (event) => {
					var _view$dom$parentEleme;
					dragSourceElement = ((_view$dom$parentEleme = view.dom.parentElement) === null || _view$dom$parentEleme === void 0 ? void 0 : _view$dom$parentEleme.contains(event.target)) ? view.dom.parentElement : null;
					if (dragSourceElement) tiptapDragFromOtherEditor = editor;
				};
				const handleDragend = () => {
					if (tiptapDragFromOtherEditor) tiptapDragFromOtherEditor = null;
				};
				window.addEventListener("dragstart", handleDragstart);
				window.addEventListener("dragend", handleDragend);
				return { destroy() {
					window.removeEventListener("dragstart", handleDragstart);
					window.removeEventListener("dragend", handleDragend);
				} };
			},
			props: { handleDOMEvents: {
				drop: (view, event) => {
					isDroppedFromProseMirror = dragSourceElement === view.dom.parentElement;
					dropEvent = event;
					if (!isDroppedFromProseMirror) {
						const dragFromOtherEditor = tiptapDragFromOtherEditor;
						if (dragFromOtherEditor === null || dragFromOtherEditor === void 0 ? void 0 : dragFromOtherEditor.isEditable) setTimeout(() => {
							const selection = dragFromOtherEditor.state.selection;
							if (selection) dragFromOtherEditor.commands.deleteRange({
								from: selection.from,
								to: selection.to
							});
						}, 10);
					}
					return false;
				},
				paste: (_view, event) => {
					var _clipboardData;
					const html = (_clipboardData = event.clipboardData) === null || _clipboardData === void 0 ? void 0 : _clipboardData.getData("text/html");
					pasteEvent = event;
					isPastedFromProseMirror = !!(html === null || html === void 0 ? void 0 : html.includes("data-pm-slice"));
					return false;
				}
			} },
			appendTransaction: (transactions, oldState, state) => {
				const transaction = transactions[0];
				const isPaste = transaction.getMeta("uiEvent") === "paste" && !isPastedFromProseMirror;
				const isDrop = transaction.getMeta("uiEvent") === "drop" && !isDroppedFromProseMirror;
				const simulatedPasteMeta = transaction.getMeta("applyPasteRules");
				const isSimulatedPaste = !!simulatedPasteMeta;
				if (!isPaste && !isDrop && !isSimulatedPaste) return;
				if (isSimulatedPaste) {
					let { text } = simulatedPasteMeta;
					if (typeof text === "string") text = text;
					else text = getHTMLFromFragment(Fragment$1.from(text), state.schema);
					const { from } = simulatedPasteMeta;
					const to = from + text.length;
					const pasteEvt = createClipboardPasteEvent(text);
					return processEvent({
						rule,
						state,
						from,
						to: { b: to },
						pasteEvt
					});
				}
				const from = oldState.doc.content.findDiffStart(state.doc.content);
				const to = oldState.doc.content.findDiffEnd(state.doc.content);
				if (!isNumber(from) || !to || from === to.b) return;
				return processEvent({
					rule,
					state,
					from,
					to,
					pasteEvt: pasteEvent
				});
			}
		});
	});
}
var ExtensionManager = class {
	constructor(extensions, editor) {
		this.splittableMarks = [];
		this.nonClearableMarks = [];
		this.decorationManager = null;
		this.editor = editor;
		this.baseExtensions = extensions;
		this.extensions = resolveExtensions(extensions);
		this.schema = getSchemaByResolvedExtensions(this.extensions, editor);
		this.setupExtensions();
	}
	/**
	* Get all commands from the extensions.
	* @returns An object with all commands where the key is the command name and the value is the command function
	*/
	get commands() {
		return this.extensions.reduce((commands, extension) => {
			const addCommands = getExtensionField(extension, "addCommands", {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor: this.editor,
				type: getSchemaTypeByName(extension.name, this.schema)
			});
			if (!addCommands) return commands;
			return {
				...commands,
				...addCommands()
			};
		}, {});
	}
	/**
	* Get all registered Prosemirror plugins from the extensions.
	* @returns An array of Prosemirror plugins
	*/
	get plugins() {
		const { editor } = this;
		const allPlugins = sortExtensions([...this.extensions].reverse()).flatMap((extension) => {
			const context = {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor,
				type: getSchemaTypeByName(extension.name, this.schema)
			};
			const plugins = [];
			const addKeyboardShortcuts = getExtensionField(extension, "addKeyboardShortcuts", context);
			let defaultBindings = {};
			if (extension.type === "mark" && getExtensionField(extension, "exitable", context)) defaultBindings.ArrowRight = () => Mark.handleExit({
				editor,
				mark: extension
			});
			if (addKeyboardShortcuts) {
				const bindings = Object.fromEntries(Object.entries(addKeyboardShortcuts()).map(([shortcut, method]) => {
					return [shortcut, () => method({ editor })];
				}));
				defaultBindings = {
					...defaultBindings,
					...bindings
				};
			}
			const keyMapPlugin = keymap(defaultBindings);
			plugins.push(keyMapPlugin);
			const addInputRules = getExtensionField(extension, "addInputRules", context);
			if (isExtensionRulesEnabled(extension, editor.options.enableInputRules) && addInputRules) {
				const rules = addInputRules();
				if (rules && rules.length) {
					const inputResult = inputRulesPlugin({
						editor,
						rules
					});
					const inputPlugins = Array.isArray(inputResult) ? inputResult : [inputResult];
					plugins.push(...inputPlugins);
				}
			}
			const addPasteRules = getExtensionField(extension, "addPasteRules", context);
			if (isExtensionRulesEnabled(extension, editor.options.enablePasteRules) && addPasteRules) {
				const rules = addPasteRules();
				if (rules && rules.length) {
					const pasteRules = pasteRulesPlugin({
						editor,
						rules
					});
					plugins.push(...pasteRules);
				}
			}
			const addProseMirrorPlugins = getExtensionField(extension, "addProseMirrorPlugins", context);
			if (addProseMirrorPlugins) {
				const proseMirrorPlugins = addProseMirrorPlugins();
				plugins.push(...proseMirrorPlugins);
			}
			return plugins;
		});
		const decorationPlugin = this.createDecorationPlugin();
		if (decorationPlugin) allPlugins.push(decorationPlugin);
		return allPlugins;
	}
	/**
	* Aggregates decorations from extensions into a single plugin, or returns null
	* if none exist. Destroys the previous manager to avoid orphaned listeners.
	* @returns A ProseMirror plugin or `null`
	* @example
	* const plugin = editor.extensionManager.createDecorationPlugin()
	*/
	createDecorationPlugin() {
		var _this$decorationManag;
		const { editor } = this;
		(_this$decorationManag = this.decorationManager) === null || _this$decorationManag === void 0 || _this$decorationManag.destroy();
		const entries = [];
		this.extensions.forEach((extension) => {
			const addDecorations = getExtensionField(extension, "addDecorations", {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor,
				type: getSchemaTypeByName(extension.name, this.schema)
			});
			if (!addDecorations) return;
			entries.push({
				name: extension.name,
				addDecorations
			});
		});
		this.decorationManager = new DecorationManager({
			editor,
			entries
		});
		return this.decorationManager.plugin;
	}
	/**
	* Get all attributes from the extensions.
	* @returns An array of attributes
	*/
	get attributes() {
		return getAttributesFromExtensions(this.extensions);
	}
	/**
	* Get all node views from the extensions.
	* @returns An object with all node views where the key is the node name and the value is the node view function
	*/
	get nodeViews() {
		const { editor } = this;
		const { nodeExtensions } = splitExtensions(this.extensions);
		return Object.fromEntries(nodeExtensions.filter((extension) => !!getExtensionField(extension, "addNodeView")).map((extension) => {
			const extensionAttributes = this.attributes.filter((attribute) => attribute.type === extension.name);
			const addNodeView = getExtensionField(extension, "addNodeView", {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor,
				type: getNodeType(extension.name, this.schema)
			});
			if (!addNodeView) return [];
			const nodeViewResult = addNodeView();
			if (!nodeViewResult) return [];
			const nodeview = (node, view, getPos, decorations, innerDecorations) => {
				return nodeViewResult({
					node,
					view,
					getPos,
					decorations,
					innerDecorations,
					editor,
					extension,
					HTMLAttributes: getRenderedAttributes(node, extensionAttributes)
				});
			};
			return [extension.name, nodeview];
		}));
	}
	/**
	* Get the composed dispatchTransaction function from all extensions.
	* @param baseDispatch The base dispatch function (e.g. from the editor or user props)
	* @returns A composed dispatch function
	*/
	dispatchTransaction(baseDispatch) {
		const { editor } = this;
		return sortExtensions([...this.extensions].reverse()).reduceRight((next, extension) => {
			const context = {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor,
				type: getSchemaTypeByName(extension.name, this.schema)
			};
			const dispatchTransaction = getExtensionField(extension, "dispatchTransaction", context);
			if (!dispatchTransaction) return next;
			return (transaction) => {
				dispatchTransaction.call(context, {
					transaction,
					next
				});
			};
		}, baseDispatch);
	}
	/**
	* Get the composed transformPastedHTML function from all extensions.
	* @param baseTransform The base transform function (e.g. from the editor props)
	* @returns A composed transform function that chains all extension transforms
	*/
	transformPastedHTML(baseTransform) {
		const { editor } = this;
		return sortExtensions([...this.extensions]).reduce((transform, extension) => {
			const context = {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor,
				type: getSchemaTypeByName(extension.name, this.schema)
			};
			const extensionTransform = getExtensionField(extension, "transformPastedHTML", context);
			if (!extensionTransform) return transform;
			return (html, view) => {
				const transformedHtml = transform(html, view);
				return extensionTransform.call(context, transformedHtml);
			};
		}, baseTransform || ((html) => html));
	}
	get markViews() {
		const { editor } = this;
		const { markExtensions } = splitExtensions(this.extensions);
		return Object.fromEntries(markExtensions.filter((extension) => !!getExtensionField(extension, "addMarkView")).map((extension) => {
			const extensionAttributes = this.attributes.filter((attribute) => attribute.type === extension.name);
			const addMarkView = getExtensionField(extension, "addMarkView", {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor,
				type: getMarkType(extension.name, this.schema)
			});
			if (!addMarkView) return [];
			const markView = (mark, view, inline) => {
				const HTMLAttributes = getRenderedAttributes(mark, extensionAttributes);
				return addMarkView()({
					mark,
					view,
					inline,
					editor,
					extension,
					HTMLAttributes,
					updateAttributes: (attrs) => {
						updateMarkViewAttributes(mark, editor, attrs);
					}
				});
			};
			return [extension.name, markView];
		}));
	}
	/**
	* Destroy the extension manager and clean up all extension references
	* to prevent memory leaks through parent/child extension chains.
	*
	* Walks each extension's full parent chain and nulls every forward
	* `parent.child → current` link where the parent still points to the
	* current node. This breaks the retention path from module-scope
	* singleton roots through deep extend() chains.
	*
	* Only ancestor `.child` links matching the current chain are cleared.
	* The `.parent` pointer on ancestors is never touched — extensions
	* may be shared across live editors, so their own backward references
	* and non-matching forward links must remain intact.
	*/
	destroy() {
		var _this$decorationManag2;
		(_this$decorationManag2 = this.decorationManager) === null || _this$decorationManag2 === void 0 || _this$decorationManag2.destroy();
		this.extensions.forEach((extension) => {
			let current = extension;
			while (current.parent) {
				const parent = current.parent;
				if (parent.child === current) parent.child = null;
				current = parent;
			}
		});
		this.extensions = [];
		this.baseExtensions = [];
		this.decorationManager = null;
		this.schema = null;
		this.editor = null;
	}
	/**
	* Go through all extensions, create extension storages & setup marks
	* & bind editor event listener.
	*/
	setupExtensions() {
		const extensions = this.extensions;
		this.editor.extensionStorage = Object.fromEntries(extensions.map((extension) => [extension.name, extension.storage]));
		extensions.forEach((extension) => {
			const context = {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor: this.editor,
				type: getSchemaTypeByName(extension.name, this.schema)
			};
			if (extension.type === "mark") {
				var _callOrReturn, _callOrReturn2;
				if ((_callOrReturn = callOrReturn(getExtensionField(extension, "keepOnSplit", context))) !== null && _callOrReturn !== void 0 ? _callOrReturn : true) this.splittableMarks.push(extension.name);
				if (!((_callOrReturn2 = callOrReturn(getExtensionField(extension, "clearable", context))) !== null && _callOrReturn2 !== void 0 ? _callOrReturn2 : true)) this.nonClearableMarks.push(extension.name);
			}
			const onBeforeCreate = getExtensionField(extension, "onBeforeCreate", context);
			const onCreate = getExtensionField(extension, "onCreate", context);
			const onUpdate = getExtensionField(extension, "onUpdate", context);
			const onSelectionUpdate = getExtensionField(extension, "onSelectionUpdate", context);
			const onTransaction = getExtensionField(extension, "onTransaction", context);
			const onFocus = getExtensionField(extension, "onFocus", context);
			const onBlur = getExtensionField(extension, "onBlur", context);
			const onDestroy = getExtensionField(extension, "onDestroy", context);
			if (onBeforeCreate) this.editor.on("beforeCreate", onBeforeCreate);
			if (onCreate) this.editor.on("create", onCreate);
			if (onUpdate) this.editor.on("update", onUpdate);
			if (onSelectionUpdate) this.editor.on("selectionUpdate", onSelectionUpdate);
			if (onTransaction) this.editor.on("transaction", onTransaction);
			if (onFocus) this.editor.on("focus", onFocus);
			if (onBlur) this.editor.on("blur", onBlur);
			if (onDestroy) this.editor.on("destroy", onDestroy);
		});
	}
};
ExtensionManager.resolve = resolveExtensions;
ExtensionManager.sort = sortExtensions;
ExtensionManager.flatten = flattenExtensions;
/**
* The Extension class is the base class for all extensions.
* @see https://tiptap.dev/api/extensions#create-a-new-extension
*/
var Extension = class Extension extends Extendable {
	constructor(..._args) {
		super(..._args);
		this.type = "extension";
	}
	/**
	* Create a new Extension instance
	* @param config - Extension configuration object or a function that returns a configuration object
	*/
	static create(config = {}) {
		return new Extension(typeof config === "function" ? config() : config);
	}
	configure(options) {
		return super.configure(options);
	}
	extend(extendedConfig) {
		const resolvedConfig = typeof extendedConfig === "function" ? extendedConfig() : extendedConfig;
		return super.extend(resolvedConfig);
	}
};
var ClipboardTextSerializer = Extension.create({
	name: "clipboardTextSerializer",
	addOptions() {
		return { blockSeparator: void 0 };
	},
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("clipboardTextSerializer"),
			props: { clipboardTextSerializer: () => {
				const { editor } = this;
				const { state, schema } = editor;
				const { doc, selection } = state;
				const textSerializers = getTextSerializersFromSchema(schema);
				const { blockSeparator } = this.options;
				const options = {
					...blockSeparator !== void 0 ? { blockSeparator } : {},
					textSerializers
				};
				return [...selection.ranges].sort((a, b) => a.$from.pos - b.$from.pos).map(({ $from, $to }) => getTextBetween(doc, {
					from: $from.pos,
					to: $to.pos
				}, options)).join(blockSeparator !== null && blockSeparator !== void 0 ? blockSeparator : "\n\n");
			} }
		})];
	}
});
var Commands = Extension.create({
	name: "commands",
	addCommands() {
		return { ...commands_exports };
	}
});
/**
* This extension allows you to be notified when the user deletes content you are interested in.
*/
var Delete = Extension.create({
	name: "delete",
	onUpdate({ transaction, appendedTransactions }) {
		var _this$editor$options$4, _this$editor$options$5;
		const callback = () => {
			var _this$editor$options$, _this$editor$options$2, _this$editor$options$3;
			if ((_this$editor$options$ = (_this$editor$options$2 = this.editor.options.coreExtensionOptions) === null || _this$editor$options$2 === void 0 || (_this$editor$options$2 = _this$editor$options$2.delete) === null || _this$editor$options$2 === void 0 || (_this$editor$options$3 = _this$editor$options$2.filterTransaction) === null || _this$editor$options$3 === void 0 ? void 0 : _this$editor$options$3.call(_this$editor$options$2, transaction)) !== null && _this$editor$options$ !== void 0 ? _this$editor$options$ : transaction.getMeta("y-sync$")) return;
			const nextTransaction = combineTransactionSteps(transaction.before, [transaction, ...appendedTransactions]);
			getChangedRanges(nextTransaction).forEach((change) => {
				if (nextTransaction.mapping.mapResult(change.oldRange.from).deletedAfter && nextTransaction.mapping.mapResult(change.oldRange.to).deletedBefore) nextTransaction.before.nodesBetween(change.oldRange.from, change.oldRange.to, (node, from) => {
					const to = from + node.nodeSize - 2;
					const isFullyWithinRange = change.oldRange.from <= from && to <= change.oldRange.to;
					this.editor.emit("delete", {
						type: "node",
						node,
						from,
						to,
						newFrom: nextTransaction.mapping.map(from),
						newTo: nextTransaction.mapping.map(to),
						deletedRange: change.oldRange,
						newRange: change.newRange,
						partial: !isFullyWithinRange,
						editor: this.editor,
						transaction,
						combinedTransform: nextTransaction
					});
				});
			});
			const mapping = nextTransaction.mapping;
			nextTransaction.steps.forEach((step, index) => {
				if (step instanceof RemoveMarkStep) {
					var _nextTransaction$doc$, _nextTransaction$doc$2;
					const newStart = mapping.slice(index).map(step.from, -1);
					const newEnd = mapping.slice(index).map(step.to);
					const oldStart = mapping.invert().map(newStart, -1);
					const oldEnd = mapping.invert().map(newEnd);
					const foundBeforeMark = newStart > 0 ? (_nextTransaction$doc$ = nextTransaction.doc.nodeAt(newStart - 1)) === null || _nextTransaction$doc$ === void 0 ? void 0 : _nextTransaction$doc$.marks.some((mark) => mark.eq(step.mark)) : false;
					const foundAfterMark = (_nextTransaction$doc$2 = nextTransaction.doc.nodeAt(newEnd)) === null || _nextTransaction$doc$2 === void 0 ? void 0 : _nextTransaction$doc$2.marks.some((mark) => mark.eq(step.mark));
					this.editor.emit("delete", {
						type: "mark",
						mark: step.mark,
						from: step.from,
						to: step.to,
						deletedRange: {
							from: oldStart,
							to: oldEnd
						},
						newRange: {
							from: newStart,
							to: newEnd
						},
						partial: Boolean(foundAfterMark || foundBeforeMark),
						editor: this.editor,
						transaction,
						combinedTransform: nextTransaction
					});
				}
			});
		};
		if ((_this$editor$options$4 = (_this$editor$options$5 = this.editor.options.coreExtensionOptions) === null || _this$editor$options$5 === void 0 || (_this$editor$options$5 = _this$editor$options$5.delete) === null || _this$editor$options$5 === void 0 ? void 0 : _this$editor$options$5.async) !== null && _this$editor$options$4 !== void 0 ? _this$editor$options$4 : true) setTimeout(callback, 0);
		else callback();
	}
});
var Drop = Extension.create({
	name: "drop",
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("tiptapDrop"),
			props: { handleDrop: (_, e, slice, moved) => {
				this.editor.emit("drop", {
					editor: this.editor,
					event: e,
					slice,
					moved
				});
			} }
		})];
	}
});
var Editable = Extension.create({
	name: "editable",
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("editable"),
			props: { editable: () => this.editor.options.editable }
		})];
	}
});
var focusEventsPluginKey = new PluginKey("focusEvents");
var FocusEvents = Extension.create({
	name: "focusEvents",
	addProseMirrorPlugins() {
		const { editor } = this;
		return [new Plugin({
			key: focusEventsPluginKey,
			props: { handleDOMEvents: {
				focus: (view, event) => {
					editor.isFocused = true;
					const transaction = editor.state.tr.setMeta("focus", { event }).setMeta("addToHistory", false);
					view.dispatch(transaction);
					return false;
				},
				blur: (view, event) => {
					editor.isFocused = false;
					const transaction = editor.state.tr.setMeta("blur", { event }).setMeta("addToHistory", false);
					view.dispatch(transaction);
					return false;
				}
			} }
		})];
	}
});
var Keymap = Extension.create({
	name: "keymap",
	addKeyboardShortcuts() {
		const handleBackspace = () => this.editor.commands.first(({ commands }) => [
			() => commands.undoInputRule(),
			() => commands.command(({ tr }) => {
				const { selection, doc } = tr;
				const { empty, $anchor } = selection;
				const { pos, parent } = $anchor;
				const $parentPos = $anchor.parent.isTextblock && pos > 0 ? tr.doc.resolve(pos - 1) : $anchor;
				const parentIsIsolating = $parentPos.parent.type.spec.isolating;
				const parentPos = $anchor.pos - $anchor.parentOffset;
				const isAtStart = parentIsIsolating && $parentPos.parent.childCount === 1 ? parentPos === $anchor.pos : Selection.atStart(doc).from === pos;
				if (!empty || !parent.type.isTextblock || parent.textContent.length || !isAtStart || isAtStart && $anchor.parent.type.name === "paragraph") return false;
				return commands.clearNodes();
			}),
			() => commands.deleteSelection(),
			() => commands.joinBackward(),
			() => commands.selectNodeBackward()
		]);
		const handleDelete = () => this.editor.commands.first(({ commands }) => [
			() => commands.deleteSelection(),
			() => commands.deleteCurrentNode(),
			() => commands.joinForward(),
			() => commands.selectNodeForward()
		]);
		const handleEnter = () => this.editor.commands.first(({ commands }) => [
			() => commands.newlineInCode(),
			() => commands.createParagraphNear(),
			() => commands.liftEmptyBlock(),
			() => commands.splitBlock()
		]);
		const baseKeymap = {
			Enter: handleEnter,
			"Mod-Enter": () => this.editor.commands.exitCode(),
			Backspace: handleBackspace,
			"Mod-Backspace": handleBackspace,
			"Shift-Backspace": handleBackspace,
			Delete: handleDelete,
			"Mod-Delete": handleDelete,
			"Mod-a": () => this.editor.commands.selectAll()
		};
		const pcKeymap = { ...baseKeymap };
		const macKeymap = {
			...baseKeymap,
			"Ctrl-h": handleBackspace,
			"Alt-Backspace": handleBackspace,
			"Ctrl-d": handleDelete,
			"Ctrl-Alt-Backspace": handleDelete,
			"Alt-Delete": handleDelete,
			"Alt-d": handleDelete,
			"Ctrl-a": () => this.editor.commands.selectTextblockStart(),
			"Ctrl-e": () => this.editor.commands.selectTextblockEnd()
		};
		if (isiOS() || isMacOS()) return macKeymap;
		return pcKeymap;
	},
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("clearDocument"),
			appendTransaction: (transactions, oldState, newState) => {
				if (transactions.some((tr) => tr.getMeta("composition"))) return;
				const docChanges = transactions.some((transaction) => transaction.docChanged) && !oldState.doc.eq(newState.doc);
				const ignoreTr = transactions.some((transaction) => transaction.getMeta("preventClearDocument"));
				if (!docChanges || ignoreTr) return;
				const { empty, from, to } = oldState.selection;
				const allFrom = Selection.atStart(oldState.doc).from;
				const allEnd = Selection.atEnd(oldState.doc).to;
				if (empty || !(from === allFrom && to === allEnd)) return;
				if (!isNodeEmpty(newState.doc)) return;
				const tr = newState.tr;
				const state = createChainableState({
					state: newState,
					transaction: tr
				});
				const { commands } = new CommandManager({
					editor: this.editor,
					state
				});
				commands.clearNodes();
				if (!tr.steps.length) return;
				return tr;
			}
		})];
	}
});
var Paste = Extension.create({
	name: "paste",
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("tiptapPaste"),
			props: { handlePaste: (_view, e, slice) => {
				this.editor.emit("paste", {
					editor: this.editor,
					event: e,
					slice
				});
			} }
		})];
	}
});
/**
* The Tabindex extension adds a configurable tabindex attribute to the editor.
*
* By default, the editor gets tabindex="0" when editable. This can be customized
* via coreExtensionOptions to support specific focus ordering requirements in forms
* or to enable focusing on non-editable editors.
*
* @example
* ```ts
* new Editor({
*   coreExtensionOptions: {
*     tabindex: {
*       value: '-1',
*     },
*   },
* })
* ```
*/
var Tabindex = Extension.create({
	name: "tabindex",
	addOptions() {
		return { value: void 0 };
	},
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("tabindex"),
			props: { attributes: () => {
				var _this$options$value;
				if (!this.editor.isEditable && this.options.value === void 0) return {};
				return { tabindex: (_this$options$value = this.options.value) !== null && _this$options$value !== void 0 ? _this$options$value : "0" };
			} }
		})];
	}
});
/**
* The TextDirection extension adds support for setting text direction (LTR/RTL/auto)
* on all nodes in the editor.
*
* This extension adds a global `dir` attribute to all node types, which can be used
* to control bidirectional text rendering. The direction can be set globally via
* editor options or per-node using commands.
*/
var TextDirection = Extension.create({
	name: "textDirection",
	addOptions() {
		return { direction: void 0 };
	},
	addGlobalAttributes() {
		if (!this.options.direction) return [];
		const { nodeExtensions } = splitExtensions(this.extensions);
		return [{
			types: nodeExtensions.filter((extension) => extension.name !== "text").map((extension) => extension.name),
			attributes: { dir: {
				default: this.options.direction,
				parseHTML: (element) => {
					const dir = element.getAttribute("dir");
					if (dir && (dir === "ltr" || dir === "rtl" || dir === "auto")) return dir;
					return this.options.direction;
				},
				renderHTML: (attributes) => {
					if (!attributes.dir) return {};
					return { dir: attributes.dir };
				}
			} }
		}];
	},
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("textDirection"),
			props: { attributes: () => {
				const direction = this.options.direction;
				if (!direction) return {};
				return { dir: direction };
			} }
		})];
	}
});
var extensions_exports = /* @__PURE__ */ __exportAll({
	ClipboardTextSerializer: () => ClipboardTextSerializer,
	Commands: () => Commands,
	Delete: () => Delete,
	Drop: () => Drop,
	Editable: () => Editable,
	FocusEvents: () => FocusEvents,
	Keymap: () => Keymap,
	Paste: () => Paste,
	Tabindex: () => Tabindex,
	TextDirection: () => TextDirection,
	focusEventsPluginKey: () => focusEventsPluginKey
});
var hasChecked = false;
/**
* Warns once when prosemirror-model is loaded twice
* @param schema The editor schema
* @returns void
* @example ```js
* warnOnDuplicatedProseMirrorModel(editor.schema)
* ```
*/
function warnOnDuplicatedProseMirrorModel(schema) {
	if (hasChecked) return;
	hasChecked = true;
	let content;
	try {
		content = ReplaceStep.fromJSON(schema, {
			from: 0,
			to: 0
		}).slice.content;
	} catch {
		return;
	}
	if (content instanceof Fragment$1) return;
	console.warn("[tiptap warn]: prosemirror-model is loaded more than once. Wrapping and splitting nodes will fail. Deduplicate it in your lock file, or alias it to a single copy in your bundler.");
}
var NodePos = class NodePos {
	get name() {
		return this.node.type.name;
	}
	constructor(pos, editor, isBlock = false, node = null) {
		this.currentNode = null;
		this.actualDepth = null;
		this.isBlock = isBlock;
		this.resolvedPos = pos;
		this.editor = editor;
		this.currentNode = node;
	}
	get node() {
		return this.currentNode || this.resolvedPos.node();
	}
	get element() {
		return this.editor.view.domAtPos(this.pos).node;
	}
	get depth() {
		var _this$actualDepth;
		return (_this$actualDepth = this.actualDepth) !== null && _this$actualDepth !== void 0 ? _this$actualDepth : this.resolvedPos.depth;
	}
	get pos() {
		return this.resolvedPos.pos;
	}
	get content() {
		return this.node.content;
	}
	set content(content) {
		let from = this.from;
		let to = this.to;
		if (this.isBlock) {
			if (this.content.size === 0) {
				console.error(`You can’t set content on a block node. Tried to set content on ${this.name} at ${this.pos}`);
				return;
			}
			from = this.from + 1;
			to = this.to - 1;
		}
		this.editor.commands.insertContentAt({
			from,
			to
		}, content);
	}
	get attributes() {
		return this.node.attrs;
	}
	get textContent() {
		return this.node.textContent;
	}
	get size() {
		return this.node.nodeSize;
	}
	get from() {
		if (this.isBlock) return this.pos;
		return this.resolvedPos.start(this.resolvedPos.depth);
	}
	get range() {
		return {
			from: this.from,
			to: this.to
		};
	}
	get to() {
		if (this.isBlock) return this.pos + this.size;
		return this.resolvedPos.end(this.resolvedPos.depth) + (this.node.isText ? 0 : 1);
	}
	get parent() {
		if (this.depth === 0) return null;
		const parentPos = this.resolvedPos.start(this.resolvedPos.depth - 1);
		return new NodePos(this.resolvedPos.doc.resolve(parentPos), this.editor);
	}
	get before() {
		let $pos = this.resolvedPos.doc.resolve(this.from - (this.isBlock ? 1 : 2));
		if ($pos.depth !== this.depth) $pos = this.resolvedPos.doc.resolve(this.from - 3);
		return new NodePos($pos, this.editor);
	}
	get after() {
		let $pos = this.resolvedPos.doc.resolve(this.to + (this.isBlock ? 2 : 1));
		if ($pos.depth !== this.depth) $pos = this.resolvedPos.doc.resolve(this.to + 3);
		return new NodePos($pos, this.editor);
	}
	get children() {
		const children = [];
		this.node.content.forEach((node, offset) => {
			const isBlock = node.isBlock && !node.isTextblock;
			const isNonTextAtom = node.isAtom && !node.isText;
			const isInline = node.isInline;
			const targetPos = this.pos + offset + (isNonTextAtom ? 0 : 1);
			if (targetPos < 0 || targetPos > this.resolvedPos.doc.nodeSize - 2) return;
			const $pos = this.resolvedPos.doc.resolve(targetPos);
			if (!isBlock && !isInline && $pos.depth <= this.depth) return;
			const childNodePos = new NodePos($pos, this.editor, isBlock, isBlock || isInline ? node : null);
			if (isBlock) childNodePos.actualDepth = this.depth + 1;
			children.push(childNodePos);
		});
		return children;
	}
	get firstChild() {
		return this.children[0] || null;
	}
	get lastChild() {
		const children = this.children;
		return children[children.length - 1] || null;
	}
	closest(selector, attributes = {}) {
		let node = null;
		let currentNode = this.parent;
		while (currentNode && !node) {
			if (currentNode.node.type.name === selector) if (Object.keys(attributes).length > 0) {
				const nodeAttributes = currentNode.node.attrs;
				const attrKeys = Object.keys(attributes);
				for (let index = 0; index < attrKeys.length; index += 1) {
					const key = attrKeys[index];
					if (nodeAttributes[key] !== attributes[key]) break;
				}
			} else node = currentNode;
			currentNode = currentNode.parent;
		}
		return node;
	}
	querySelector(selector, attributes = {}) {
		return this.querySelectorAll(selector, attributes, true)[0] || null;
	}
	querySelectorAll(selector, attributes = {}, firstItemOnly = false) {
		let nodes = [];
		if (!this.children || this.children.length === 0) return nodes;
		const attrKeys = Object.keys(attributes);
		/**
		* Finds all children recursively that match the selector and attributes
		* If firstItemOnly is true, it will return the first item found
		*/
		this.children.forEach((childPos) => {
			if (firstItemOnly && nodes.length > 0) return;
			if (childPos.node.type.name === selector) {
				if (attrKeys.every((key) => attributes[key] === childPos.node.attrs[key])) nodes.push(childPos);
			}
			if (firstItemOnly && nodes.length > 0) return;
			nodes = nodes.concat(childPos.querySelectorAll(selector, attributes, firstItemOnly));
		});
		return nodes;
	}
	setAttribute(attributes) {
		const { tr } = this.editor.state;
		tr.setNodeMarkup(this.from, void 0, {
			...this.node.attrs,
			...attributes
		});
		this.editor.view.dispatch(tr);
	}
};
var style = `.ProseMirror {
  position: relative;
}

.ProseMirror {
  word-wrap: break-word;
  white-space: pre-wrap;
  white-space: break-spaces;
  -webkit-font-variant-ligatures: none;
  font-variant-ligatures: none;
  font-feature-settings: "liga" 0; /* the above doesn't seem to work in Edge */
}

.ProseMirror [contenteditable="false"] {
  white-space: normal;
}

.ProseMirror [contenteditable="false"] [contenteditable="true"] {
  white-space: pre-wrap;
}

.ProseMirror pre {
  white-space: pre-wrap;
}

img.ProseMirror-separator {
  display: inline !important;
  border: none !important;
  margin: 0 !important;
  width: 0 !important;
  height: 0 !important;
}

.ProseMirror-gapcursor {
  display: none;
  pointer-events: none;
  position: absolute;
  margin: 0;
}

.ProseMirror-gapcursor:after {
  content: "";
  display: block;
  position: absolute;
  top: -2px;
  width: 20px;
  border-top: 1px solid black;
  animation: ProseMirror-cursor-blink 1.1s steps(2, start) infinite;
}

@keyframes ProseMirror-cursor-blink {
  to {
    visibility: hidden;
  }
}

.ProseMirror-hideselection *::selection {
  background: transparent;
}

.ProseMirror-hideselection *::-moz-selection {
  background: transparent;
}

.ProseMirror-hideselection * {
  caret-color: transparent;
}

.ProseMirror-focused .ProseMirror-gapcursor {
  display: block;
}`;
var Editor = class extends EventEmitter {
	constructor(options = {}) {
		super();
		this.css = null;
		this.className = "tiptap";
		this.editorView = null;
		this.isFocused = false;
		this.destroyed = false;
		this.isInitialized = false;
		this.extensionStorage = {};
		this.instanceId = Math.random().toString(36).slice(2, 9);
		this.hasWarnedStaleDecorationRead = false;
		this.options = {
			element: typeof document !== "undefined" ? document.createElement("div") : null,
			content: "",
			injectCSS: true,
			injectNonce: void 0,
			extensions: [],
			autofocus: false,
			editable: true,
			textDirection: void 0,
			editorProps: {},
			parseOptions: {},
			coreExtensionOptions: {},
			enableInputRules: true,
			enablePasteRules: true,
			enableCoreExtensions: true,
			enableContentCheck: false,
			emitContentError: false,
			onBeforeCreate: () => null,
			onCreate: () => null,
			onMount: () => null,
			onUnmount: () => null,
			onUpdate: () => null,
			onSelectionUpdate: () => null,
			onTransaction: () => null,
			onFocus: () => null,
			onBlur: () => null,
			onDestroy: () => null,
			onContentError: ({ error }) => {
				throw error;
			},
			onPaste: () => null,
			onDrop: () => null,
			onDelete: () => null,
			enableExtensionDispatchTransaction: true
		};
		this.isCapturingTransaction = false;
		this.capturedTransaction = null;
		this.utils = {
			getUpdatedPosition,
			createMappablePosition
		};
		this.setOptions(options);
		this.createExtensionManager();
		this.createCommandManager();
		this.createSchema();
		this.on("beforeCreate", this.options.onBeforeCreate);
		this.emit("beforeCreate", { editor: this });
		this.on("mount", this.options.onMount);
		this.on("unmount", this.options.onUnmount);
		this.on("contentError", this.options.onContentError);
		this.on("create", this.options.onCreate);
		this.on("update", this.options.onUpdate);
		this.on("selectionUpdate", this.options.onSelectionUpdate);
		this.on("transaction", this.options.onTransaction);
		this.on("focus", this.options.onFocus);
		this.on("blur", this.options.onBlur);
		this.on("destroy", this.options.onDestroy);
		this.on("drop", ({ event, slice, moved }) => this.options.onDrop(event, slice, moved));
		this.on("paste", ({ event, slice }) => this.options.onPaste(event, slice));
		this.on("delete", this.options.onDelete);
		const initialDoc = this.createDoc();
		if (!this.editorState) {
			const selection = resolveFocusPosition(initialDoc, this.options.autofocus);
			this.editorState = EditorState.create({
				doc: initialDoc,
				schema: this.schema,
				selection: selection || void 0
			});
		}
		warnOnDuplicatedProseMirrorModel(this.schema);
		if (this.options.element) this.mount(this.options.element);
	}
	/**
	* Attach the editor to the DOM, creating a new editor view.
	*/
	mount(el) {
		if (typeof document === "undefined") throw new Error(`[tiptap error]: The editor cannot be mounted because there is no 'document' defined in this environment.`);
		this.createView(el);
		this.emit("mount", { editor: this });
		if (this.css && !document.head.contains(this.css)) document.head.appendChild(this.css);
		window.setTimeout(() => {
			if (this.isDestroyed) return;
			if (this.options.autofocus !== false && this.options.autofocus !== null) this.commands.focus(this.options.autofocus);
			this.emit("create", { editor: this });
			this.isInitialized = true;
		}, 0);
	}
	/**
	* Remove the editor from the DOM, but still allow remounting at a different point in time
	*/
	unmount() {
		if (this.editorView) {
			this.editorState = this.editorView.state;
			const dom = this.editorView.dom;
			if (dom === null || dom === void 0 ? void 0 : dom.editor) delete dom.editor;
			this.editorView.destroy();
		}
		this.editorView = null;
		this.isInitialized = false;
		if (this.css && !document.querySelectorAll(`.${this.className}`).length) try {
			if (typeof this.css.remove === "function") this.css.remove();
			else if (this.css.parentNode) this.css.parentNode.removeChild(this.css);
		} catch (error) {
			console.warn("Failed to remove CSS element:", error);
		}
		this.css = null;
		this.emit("unmount", { editor: this });
	}
	/**
	* Returns the editor storage.
	*/
	get storage() {
		return this.extensionStorage;
	}
	/**
	* An object of all registered commands.
	*/
	get commands() {
		return this.commandManager.commands;
	}
	/**
	* Create a command chain to call multiple commands at once.
	*/
	chain() {
		if (!this.commandManager) return CommandManager.createFakeChain();
		return this.commandManager.chain();
	}
	/**
	* Check if a command or a command chain can be executed. Without executing it.
	*/
	can() {
		if (!this.commandManager) return CommandManager.createFallbackCan();
		return this.commandManager.can();
	}
	/**
	* Inject CSS styles.
	*/
	injectCSS() {
		if (this.options.injectCSS && typeof document !== "undefined") this.css = createStyleTag(style, this.options.injectNonce);
	}
	/**
	* Update editor options.
	*
	* @param options A list of options
	*/
	setOptions(options = {}) {
		this.options = {
			...this.options,
			...options
		};
		if (!this.editorView || !this.state || this.isDestroyed) return;
		if (this.options.editorProps) this.view.setProps(this.options.editorProps);
		this.view.updateState(this.state);
	}
	/**
	* Update editable state of the editor.
	*/
	setEditable(editable, emitUpdate = true) {
		this.setOptions({ editable });
		if (emitUpdate) this.emit("update", {
			editor: this,
			transaction: this.state.tr,
			appendedTransactions: []
		});
	}
	/**
	* Returns whether the editor is editable.
	*/
	get isEditable() {
		return this.options.editable && this.view && this.view.editable;
	}
	/**
	* Returns the editor view.
	*/
	get view() {
		if (this.editorView) return this.editorView;
		return new Proxy({
			state: this.editorState,
			updateState: (state) => {
				this.editorState = state;
			},
			dispatch: (tr) => {
				this.dispatchTransaction(tr);
			},
			composing: false,
			dragging: null,
			editable: true,
			isDestroyed: false
		}, { get: (obj, key) => {
			if (this.editorView) return this.editorView[key];
			if (key === "state") return this.editorState;
			if (key in obj) return Reflect.get(obj, key);
			throw new Error(`[tiptap error]: The editor view is not available. Cannot access view['${key}']. The editor may not be mounted yet.`);
		} });
	}
	/**
	* Returns the editor state.
	*/
	get state() {
		if (isDev && !this.hasWarnedStaleDecorationRead && isInDecorationApplyScope(this)) {
			this.hasWarnedStaleDecorationRead = true;
			console.warn("[tiptap warn]: `editor.state` was read while decoration `create()` was running. It returns the pre-transaction document. Use the `state` argument passed to `create()` instead. Helpers like `editor.isActive()` read `editor.state` too, so pass `state` to their standalone versions instead of calling them on the editor.");
		}
		if (this.editorView) this.editorState = this.view.state;
		return this.editorState;
	}
	/**
	* Register a ProseMirror plugin.
	*
	* @param plugin A ProseMirror plugin
	* @param handlePlugins Control how to merge the plugin into the existing plugins.
	* @returns The new editor state
	*/
	registerPlugin(plugin, handlePlugins) {
		const plugins = isFunction(handlePlugins) ? handlePlugins(plugin, [...this.state.plugins]) : [...this.state.plugins, plugin];
		const state = this.state.reconfigure({ plugins });
		this.view.updateState(state);
		return state;
	}
	/**
	* Unregister a ProseMirror plugin.
	*
	* @param nameOrPluginKeyToRemove The plugins name
	* @returns The new editor state or undefined if the editor is destroyed
	*/
	unregisterPlugin(nameOrPluginKeyToRemove) {
		if (this.isDestroyed) return;
		const prevPlugins = this.state.plugins;
		let plugins = prevPlugins;
		[].concat(nameOrPluginKeyToRemove).forEach((nameOrPluginKey) => {
			const name = typeof nameOrPluginKey === "string" ? `${nameOrPluginKey}$` : nameOrPluginKey.key;
			plugins = plugins.filter((plugin) => !plugin.key.startsWith(name));
		});
		if (prevPlugins.length === plugins.length) return;
		const state = this.state.reconfigure({ plugins });
		this.view.updateState(state);
		return state;
	}
	/**
	* Creates an extension manager.
	*/
	createExtensionManager() {
		var _this$options$coreExt, _this$options$coreExt2;
		const allExtensions = [...this.options.enableCoreExtensions ? [
			Editable,
			ClipboardTextSerializer.configure({ blockSeparator: (_this$options$coreExt = this.options.coreExtensionOptions) === null || _this$options$coreExt === void 0 || (_this$options$coreExt = _this$options$coreExt.clipboardTextSerializer) === null || _this$options$coreExt === void 0 ? void 0 : _this$options$coreExt.blockSeparator }),
			Commands,
			FocusEvents,
			Keymap,
			Tabindex.configure({ value: (_this$options$coreExt2 = this.options.coreExtensionOptions) === null || _this$options$coreExt2 === void 0 || (_this$options$coreExt2 = _this$options$coreExt2.tabindex) === null || _this$options$coreExt2 === void 0 ? void 0 : _this$options$coreExt2.value }),
			Drop,
			Paste,
			Delete,
			TextDirection.configure({ direction: this.options.textDirection })
		].filter((ext) => {
			if (typeof this.options.enableCoreExtensions === "object") return this.options.enableCoreExtensions[ext.name] !== false;
			return true;
		}) : [], ...this.options.extensions].filter((extension) => {
			return [
				"extension",
				"node",
				"mark"
			].includes(extension === null || extension === void 0 ? void 0 : extension.type);
		});
		this.extensionManager = new ExtensionManager(allExtensions, this);
	}
	/**
	* Creates an command manager.
	*/
	createCommandManager() {
		this.commandManager = new CommandManager({ editor: this });
	}
	/**
	* Creates a ProseMirror schema.
	*/
	createSchema() {
		this.schema = this.extensionManager.schema;
	}
	/**
	* Creates the initial document.
	*/
	createDoc() {
		let doc;
		try {
			doc = createDocument(this.options.content, this.schema, this.options.parseOptions, { errorOnInvalidContent: this.options.enableContentCheck });
		} catch (e) {
			if (!(e instanceof Error) || !["[tiptap error]: Invalid JSON content", "[tiptap error]: Invalid HTML content"].includes(e.message)) throw e;
			const fallbackDoc = createDocument(this.options.content, this.schema, this.options.parseOptions, { errorOnInvalidContent: false });
			this.editorState = EditorState.create({
				doc: fallbackDoc,
				schema: this.schema,
				selection: resolveFocusPosition(fallbackDoc, this.options.autofocus) || void 0
			});
			this.emit("contentError", {
				editor: this,
				error: e,
				disableCollaboration: () => {
					if ("collaboration" in this.storage && typeof this.storage.collaboration === "object" && this.storage.collaboration) this.storage.collaboration.isDisabled = true;
					this.options.extensions = this.options.extensions.filter((extension) => extension.name !== "collaboration");
					this.createExtensionManager();
				}
			});
			return this.editorState.doc;
		}
		return doc;
	}
	/**
	* Creates a ProseMirror view.
	*/
	createView(element) {
		const { editorProps, enableExtensionDispatchTransaction } = this.options;
		const baseDispatch = editorProps.dispatchTransaction || this.dispatchTransaction.bind(this);
		const dispatch = enableExtensionDispatchTransaction ? this.extensionManager.dispatchTransaction(baseDispatch) : baseDispatch;
		const baseTransformPastedHTML = editorProps.transformPastedHTML;
		const transformPastedHTML = this.extensionManager.transformPastedHTML(baseTransformPastedHTML);
		this.editorView = new EditorView(element, {
			...editorProps,
			attributes: {
				role: "textbox",
				...editorProps === null || editorProps === void 0 ? void 0 : editorProps.attributes
			},
			dispatchTransaction: dispatch,
			transformPastedHTML,
			state: this.editorState,
			markViews: this.extensionManager.markViews,
			nodeViews: this.extensionManager.nodeViews
		});
		const newState = this.state.reconfigure({ plugins: this.extensionManager.plugins });
		this.view.updateState(newState);
		this.prependClass();
		this.injectCSS();
		const dom = this.view.dom;
		dom.editor = this;
	}
	/**
	* Creates all node and mark views.
	*/
	createNodeViews() {
		if (this.view.isDestroyed) return;
		this.view.setProps({
			markViews: this.extensionManager.markViews,
			nodeViews: this.extensionManager.nodeViews
		});
	}
	/**
	* Prepend class name to element.
	*/
	prependClass() {
		this.view.dom.className = `${this.className} ${this.view.dom.className}`;
	}
	captureTransaction(fn) {
		this.isCapturingTransaction = true;
		fn();
		this.isCapturingTransaction = false;
		const tr = this.capturedTransaction;
		this.capturedTransaction = null;
		return tr;
	}
	/**
	* The callback over which to send transactions (state updates) produced by the view.
	*
	* @param transaction An editor state transaction
	*/
	dispatchTransaction(transaction) {
		if (this.view.isDestroyed) return;
		if (this.isCapturingTransaction) {
			if (!this.capturedTransaction) {
				this.capturedTransaction = transaction;
				return;
			}
			transaction.steps.forEach((step) => {
				var _this$capturedTransac;
				return (_this$capturedTransac = this.capturedTransaction) === null || _this$capturedTransac === void 0 ? void 0 : _this$capturedTransac.step(step);
			});
			return;
		}
		const { state, transactions } = this.state.applyTransaction(transaction);
		const selectionHasChanged = !this.state.selection.eq(state.selection);
		const rootTrWasApplied = transactions.includes(transaction);
		const prevState = this.state;
		this.emit("beforeTransaction", {
			editor: this,
			transaction,
			nextState: state
		});
		if (!rootTrWasApplied) return;
		this.view.updateState(state);
		this.emit("transaction", {
			editor: this,
			transaction,
			appendedTransactions: transactions.slice(1)
		});
		if (selectionHasChanged) this.emit("selectionUpdate", {
			editor: this,
			transaction
		});
		const mostRecentFocusTr = transactions.findLast((tr) => tr.getMeta("focus") || tr.getMeta("blur"));
		const focus = mostRecentFocusTr === null || mostRecentFocusTr === void 0 ? void 0 : mostRecentFocusTr.getMeta("focus");
		const blur = mostRecentFocusTr === null || mostRecentFocusTr === void 0 ? void 0 : mostRecentFocusTr.getMeta("blur");
		if (focus) this.emit("focus", {
			editor: this,
			event: focus.event,
			transaction: mostRecentFocusTr
		});
		if (blur) this.emit("blur", {
			editor: this,
			event: blur.event,
			transaction: mostRecentFocusTr
		});
		if (transaction.getMeta("preventUpdate") || !transactions.some((tr) => tr.docChanged) || prevState.doc.eq(state.doc)) return;
		this.emit("update", {
			editor: this,
			transaction,
			appendedTransactions: transactions.slice(1)
		});
	}
	/**
	* Get attributes of the currently selected node or mark.
	*/
	getAttributes(nameOrType) {
		return getAttributes(this.state, nameOrType);
	}
	isActive(nameOrAttributes, attributesOrUndefined) {
		const name = typeof nameOrAttributes === "string" ? nameOrAttributes : null;
		const attributes = typeof nameOrAttributes === "string" ? attributesOrUndefined : nameOrAttributes;
		return isActive(this.state, name, attributes);
	}
	/**
	* Get the document as JSON.
	*/
	getJSON() {
		return this.state.doc.toJSON();
	}
	/**
	* Get the document as HTML.
	*/
	getHTML() {
		return getHTMLFromFragment(this.state.doc.content, this.schema);
	}
	/**
	* Get the document as text.
	*/
	getText(options) {
		const { blockSeparator = "\n\n", textSerializers = {} } = options || {};
		return getText(this.state.doc, {
			blockSeparator,
			textSerializers: {
				...getTextSerializersFromSchema(this.schema),
				...textSerializers
			}
		});
	}
	/**
	* Check if there is no content.
	*/
	get isEmpty() {
		return isNodeEmpty(this.state.doc);
	}
	/**
	* Destroy the editor.
	*/
	destroy() {
		if (this.destroyed) return;
		this.destroyed = true;
		this.emit("destroy");
		this.unmount();
		this.removeAllListeners();
		this.extensionManager.destroy();
		this.extensionManager = null;
		this.schema = null;
		this.commandManager = null;
		this.extensionStorage = {};
	}
	/**
	* Check if the editor is already destroyed.
	*/
	get isDestroyed() {
		var _this$editorView$isDe, _this$editorView;
		return (_this$editorView$isDe = (_this$editorView = this.editorView) === null || _this$editorView === void 0 ? void 0 : _this$editorView.isDestroyed) !== null && _this$editorView$isDe !== void 0 ? _this$editorView$isDe : true;
	}
	$node(selector, attributes) {
		var _this$$doc;
		return ((_this$$doc = this.$doc) === null || _this$$doc === void 0 ? void 0 : _this$$doc.querySelector(selector, attributes)) || null;
	}
	$nodes(selector, attributes) {
		var _this$$doc2;
		return ((_this$$doc2 = this.$doc) === null || _this$$doc2 === void 0 ? void 0 : _this$$doc2.querySelectorAll(selector, attributes)) || null;
	}
	$pos(pos) {
		const $pos = this.state.doc.resolve(pos);
		const node = pos > 0 && $pos.nodeAfter && !$pos.nodeAfter.isText && $pos.nodeAfter.isAtom ? $pos.nodeAfter : null;
		return new NodePos($pos, this, false, node);
	}
	get $doc() {
		return this.$pos(0);
	}
};
/**
* Base class for decorations built in `addDecorations()`. Shadows `Decoration`
* from `@tiptap/pm/view`, like core's `Node` and `Mark` do, so alias one of them
* in files that need both.
*
* @example
* import { Decoration } from '@tiptap/core'
* import { Decoration as PMDecoration } from '@tiptap/pm/view'
*
* const highlight = Decoration.Inline(1, 5, { class: 'highlight' })
*/
var Decoration = class {
	static Inline(from, to, attrs = {}, spec) {
		return new InlineDecoration(from, to, attrs, spec);
	}
	static Node(pos, to, attrs = {}, spec) {
		return new NodeDecoration(pos, to, attrs, spec);
	}
	/**
	* Creates a widget decoration: a DOM node drawn at a document position.
	*
	* The `key` is the widget's identity. While it stays the same, ProseMirror
	* keeps the widget mounted and only its position tracks the document.
	* `render`, `side`, `destroy` and other options are fixed on first mount.
	* Change the key to remount with new options.
	*
	* @param pos The document position where the widget is drawn.
	* @param render Called once on first mount. Returns the DOM node.
	* @param options Must include a unique `key`. See `WidgetDecorationOptions`.
	* @returns The widget decoration.
	*/
	static Widget(pos, render, options) {
		const { key, ...spec } = options;
		return new WidgetDecoration(pos, render, key, spec);
	}
};
/**
* Represents an inline decoration (text-level highlighting, etc.).
*/
var InlineDecoration = class extends Decoration {
	constructor(from, to, attrs = {}, spec) {
		super();
		this.kind = "inline";
		this.from = from;
		this.to = to;
		this.attrs = attrs;
		this.spec = spec;
	}
	get anchor() {
		return this.from;
	}
	toPMDecoration(extensionName) {
		const spec = extensionName ? {
			...this.spec,
			extensionName
		} : this.spec;
		return Decoration$1.inline(this.from, this.to, this.attrs, spec);
	}
};
/**
* Represents a node-level decoration (block-level highlights, etc.).
*/
var NodeDecoration = class extends Decoration {
	constructor(pos, to, attrs = {}, spec) {
		super();
		this.kind = "node";
		this.from = pos;
		this.to = to;
		this.attrs = attrs;
		this.spec = spec;
	}
	get anchor() {
		return this.from;
	}
	toPMDecoration(extensionName) {
		const spec = extensionName ? {
			...this.spec,
			extensionName
		} : this.spec;
		return Decoration$1.node(this.from, this.to, this.attrs, spec);
	}
};
/**
* Represents a widget decoration (inline widgets, etc.).
*/
var WidgetDecoration = class extends Decoration {
	constructor(pos, render, key, spec) {
		super();
		this.kind = "widget";
		this.pos = pos;
		this.render = render;
		this.key = key;
		this.spec = spec;
	}
	get anchor() {
		return this.pos;
	}
	toPMDecoration(extensionName) {
		const spec = extensionName ? {
			...this.spec,
			key: this.key,
			extensionName
		} : {
			...this.spec,
			key: this.key
		};
		return Decoration$1.widget(this.pos, this.render, spec);
	}
};
function getCache(editor, cacheKey) {
	const host = editor;
	let cache = host[cacheKey];
	if (!cache) {
		cache = {
			renderers: /* @__PURE__ */ new Map(),
			props: /* @__PURE__ */ new Map(),
			pendingProps: /* @__PURE__ */ new Map(),
			flushScheduled: false
		};
		host[cacheKey] = cache;
		const sweep = cache;
		editor.on("destroy", () => {
			sweep.pendingProps.clear();
			sweep.renderers.forEach((renderer) => renderer.destroy());
			sweep.renderers.clear();
			sweep.props.clear();
		});
	}
	return cache;
}
function flushPendingProps(cache) {
	cache.flushScheduled = false;
	for (const [key, props] of cache.pendingProps) {
		const renderer = cache.renderers.get(key);
		if (renderer) {
			renderer.updateProps(props);
			cache.props.set(key, { ...props });
		}
	}
	cache.pendingProps.clear();
}
/**
* Builds a widget decoration backed by a framework component renderer.
*
* Owns everything that is not framework specific: the per-editor renderer
* cache, prop diffing, the deferred prop flush, the key reassignment guard and
* the ProseMirror option pass-through. Framework packages supply only `create`,
* `context` and `materialize`.
*
* @param options The widget options plus the three framework hooks.
* @returns The widget decoration to return from `addDecorations`.
* @example
* createWidgetDecoration<ReactRenderer>({
*   editor, pos, key, props, cacheKey: WIDGET_CACHE,
*   context: getPos => ({ editor, getPos }),
*   create: renderProps => new ReactRenderer(component, { editor, props: renderProps }),
*   materialize: renderer => renderer.element,
* })
*/
function createWidgetDecoration(options) {
	const { editor, pos, key, props, cacheKey, context, create, materialize, side, relaxedSide, marks, stopEvent, ignoreSelection, destroy } = options;
	const cache = getCache(editor, cacheKey);
	if (cache.renderers.has(key)) {
		var _cache$pendingProps$g;
		const previous = (_cache$pendingProps$g = cache.pendingProps.get(key)) !== null && _cache$pendingProps$g !== void 0 ? _cache$pendingProps$g : cache.props.get(key);
		if (!previous || !attrsEqual(previous, props)) {
			cache.pendingProps.set(key, props);
			if (!cache.flushScheduled) {
				cache.flushScheduled = true;
				queueMicrotask(() => flushPendingProps(cache));
			}
		}
	}
	const render = (_view, getPos) => {
		const renderProps = {
			...props,
			...context(getPos)
		};
		let renderer = cache.renderers.get(key);
		if (renderer) renderer.updateProps(renderProps);
		else {
			renderer = create(renderProps);
			cache.renderers.set(key, renderer);
			cache.props.set(key, { ...props });
		}
		return materialize(renderer);
	};
	return Decoration.Widget(pos, render, {
		key,
		side,
		relaxedSide,
		marks,
		stopEvent,
		ignoreSelection,
		destroy: (rendererElement) => {
			if (liveWidgetKeys(editor).has(key)) return;
			try {
				var _cache$renderers$get;
				(_cache$renderers$get = cache.renderers.get(key)) === null || _cache$renderers$get === void 0 || _cache$renderers$get.destroy();
				cache.renderers.delete(key);
				cache.props.delete(key);
				cache.pendingProps.delete(key);
			} finally {
				destroy === null || destroy === void 0 || destroy(rendererElement);
			}
		}
	});
}
/**
* Build an input rule that adds a mark when the
* matched text is typed into it.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#input-rules
*/
function markInputRule(config) {
	return new InputRule({
		find: config.find,
		handler: ({ state, range, match }) => {
			const attributes = callOrReturn(config.getAttributes, void 0, match);
			if (attributes === false || attributes === null) return null;
			const { tr } = state;
			const captureGroup = match[match.length - 1];
			const fullMatch = match[0];
			if (captureGroup) {
				const startSpaces = fullMatch.search(/\S/);
				const textStart = range.from + fullMatch.indexOf(captureGroup);
				const textEnd = textStart + captureGroup.length;
				if (getMarksBetween(range.from, range.to, state.doc).filter((item) => {
					return item.mark.type.excluded.find((type) => type === config.type && type !== item.mark.type);
				}).filter((item) => item.to > textStart).length) return null;
				if (textEnd < range.to) tr.delete(textEnd, range.to);
				if (textStart > range.from) tr.delete(range.from + startSpaces, textStart);
				const markEnd = range.from + startSpaces + captureGroup.length;
				tr.addMark(range.from + startSpaces, markEnd, config.type.create(attributes || {}));
				tr.removeStoredMark(config.type);
			}
		},
		undoable: config.undoable
	});
}
/**
* Build an input rule that adds a node when the
* matched text is typed into it.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#input-rules
*/
function nodeInputRule(config) {
	return new InputRule({
		find: config.find,
		handler: ({ state, range, match }) => {
			const attributes = callOrReturn(config.getAttributes, void 0, match) || {};
			const { tr } = state;
			const start = range.from;
			let end = range.to;
			const newNode = config.type.create(attributes);
			if (match[1]) {
				let matchStart = start + match[0].lastIndexOf(match[1]);
				if (matchStart > end) matchStart = end;
				else end = matchStart + match[1].length;
				const lastChar = match[0][match[0].length - 1];
				tr.insertText(lastChar, start + match[0].length - 1);
				tr.replaceWith(matchStart, end, newNode);
			} else if (match[0]) {
				const insertionStart = config.type.isInline ? start : start - 1;
				tr.insert(insertionStart, config.type.create(attributes)).delete(tr.mapping.map(start), tr.mapping.map(end));
			}
			tr.scrollIntoView();
		},
		undoable: config.undoable
	});
}
/**
* Build an input rule that changes the type of a textblock when the
* matched text is typed into it. When using a regular expresion you’ll
* probably want the regexp to start with `^`, so that the pattern can
* only occur at the start of a textblock.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#input-rules
*/
function textblockTypeInputRule(config) {
	return new InputRule({
		find: config.find,
		handler: ({ state, range, match }) => {
			const $start = state.doc.resolve(range.from);
			const attributes = callOrReturn(config.getAttributes, void 0, match) || {};
			if (!$start.node(-1).canReplaceWith($start.index(-1), $start.indexAfter(-1), config.type)) return null;
			state.tr.delete(range.from, range.to).setBlockType(range.from, range.from, config.type, attributes);
		},
		undoable: config.undoable
	});
}
/**
* Build an input rule that replaces text when the
* matched text is typed into it.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#input-rules
*/
function textInputRule(config) {
	return new InputRule({
		find: config.find,
		handler: ({ state, range, match }) => {
			let insert = config.replace;
			let start = range.from;
			const end = range.to;
			if (match[1]) {
				const offset = match[0].lastIndexOf(match[1]);
				insert += match[0].slice(offset + match[1].length);
				start += offset;
				const cutOff = start - end;
				if (cutOff > 0) {
					insert = match[0].slice(offset - cutOff, offset) + insert;
					start = end;
				}
			}
			state.tr.insertText(insert, start, end);
		},
		undoable: config.undoable
	});
}
/**
* Build an input rule for automatically wrapping a textblock when a
* given string is typed. When using a regular expresion you’ll
* probably want the regexp to start with `^`, so that the pattern can
* only occur at the start of a textblock.
*
* `type` is the type of node to wrap in.
*
* By default, if there’s a node with the same type above the newly
* wrapped node, the rule will try to join those
* two nodes. You can pass a join predicate, which takes a regular
* expression match and the node before the wrapped node, and can
* return a boolean to indicate whether a join should happen.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#input-rules
*/
function wrappingInputRule(config) {
	return new InputRule({
		find: config.find,
		handler: ({ state, range, match, chain }) => {
			const attributes = callOrReturn(config.getAttributes, void 0, match) || {};
			const tr = state.tr.delete(range.from, range.to);
			const blockRange = tr.doc.resolve(range.from).blockRange();
			const wrapping = blockRange && findWrapping(blockRange, config.type, attributes);
			if (!wrapping) return null;
			tr.wrap(blockRange, wrapping);
			if (config.keepMarks && config.editor) {
				const { selection, storedMarks } = state;
				const { splittableMarks } = config.editor.extensionManager;
				const marks = storedMarks || selection.$to.parentOffset && selection.$from.marks();
				if (marks) {
					const filteredMarks = marks.filter((mark) => splittableMarks.includes(mark.type.name));
					tr.ensureMarks(filteredMarks);
				}
			}
			if (config.keepAttributes) {
				/** If the nodeType is `bulletList` or `orderedList` set the `nodeType` as `listItem` */
				const nodeType = config.type.name === "bulletList" || config.type.name === "orderedList" ? "listItem" : "taskList";
				chain().updateAttributes(nodeType, attributes).run();
			}
			const before = tr.doc.resolve(range.from - 1).nodeBefore;
			if (before && before.type === config.type && canJoin(tr.doc, range.from - 1) && (!config.joinPredicate || config.joinPredicate(match, before))) tr.join(range.from - 1);
		},
		undoable: config.undoable
	});
}
var jsxElements = /* @__PURE__ */ new WeakSet();
var jsxFragments = /* @__PURE__ */ new WeakSet();
/** Create a new JSX element from the given spec */
function createJSXElement(spec) {
	const element = spec;
	jsxElements.add(element);
	return element;
}
/** Check if a spec is a JSX element */
function isJSXElement(value) {
	return Array.isArray(value) && jsxElements.has(value);
}
function flattenFragmentChildren(children) {
	return children.flatMap((child) => {
		if (child == null) return [];
		if (Array.isArray(child) && jsxFragments.has(child) && !isJSXElement(child)) return flattenFragmentChildren(child);
		return [child];
	});
}
function Fragment(props) {
	jsxFragments.add(props.children);
	return props.children;
}
function render(tag, attributes) {
	if (tag === "slot") return 0;
	if (tag instanceof Function) {
		const result = tag(attributes);
		if (Array.isArray(result) && !isJSXElement(result) && !jsxFragments.has(result)) return createJSXElement(result);
		return result;
	}
	const { children, ...rest } = attributes !== null && attributes !== void 0 ? attributes : {};
	if (tag === "svg") throw new Error("SVG elements are not supported in the JSX syntax, use the array syntax instead");
	if (Array.isArray(children)) {
		if (isJSXElement(children)) return createJSXElement([
			tag,
			rest,
			children
		]);
		if (children.length === 0) return createJSXElement([tag, rest]);
		const flattenedChildren = flattenFragmentChildren(children);
		if (flattenedChildren.length === 0) return createJSXElement([tag, rest]);
		return createJSXElement([
			tag,
			rest,
			...flattenedChildren
		]);
	}
	if (children !== void 0 && children !== null) return createJSXElement([
		tag,
		rest,
		children
	]);
	return createJSXElement([tag, rest]);
}
var h = (tag, attributes) => render(tag, attributes);
var isTouchEvent = (e) => {
	return "touches" in e;
};
/**
* A NodeView implementation that adds resize handles to any DOM element.
*
* This class creates a resizable node view for Tiptap/ProseMirror editors.
* It wraps your element with resize handles and manages the resize interaction,
* including aspect ratio preservation, min/max constraints, and keyboard modifiers.
*
* @example
* ```ts
* // Basic usage in a Tiptap extension
* addNodeView() {
*   return ({ node, getPos }) => {
*     const img = document.createElement('img')
*     img.src = node.attrs.src
*
*     return new ResizableNodeView({
*       element: img,
*       node,
*       getPos,
*       onResize: (width, height) => {
*         img.style.width = `${width}px`
*         img.style.height = `${height}px`
*       },
*       onCommit: (width, height) => {
*         this.editor.commands.updateAttributes('image', { width, height })
*       },
*       onUpdate: () => true,
*       options: {
*         min: { width: 100, height: 100 },
*         preserveAspectRatio: true
*       }
*     })
*   }
* }
* ```
*/
var ResizableNodeView = class {
	/**
	* Creates a new ResizableNodeView instance.
	*
	* The constructor sets up the resize handles, applies initial sizing from
	* node attributes, and configures all resize behavior options.
	*
	* @param options - Configuration options for the resizable node view
	*/
	constructor(options) {
		var _options$options, _options$options2, _options$options3, _options$options4, _options$options5, _options$options6;
		this.directions = [
			"bottom-left",
			"bottom-right",
			"top-left",
			"top-right"
		];
		this.minSize = {
			height: 8,
			width: 8
		};
		this.preserveAspectRatio = false;
		this.classNames = {
			container: "",
			wrapper: "",
			handle: "",
			resizing: ""
		};
		this.initialWidth = 0;
		this.initialHeight = 0;
		this.aspectRatio = 1;
		this.isResizing = false;
		this.activeHandle = null;
		this.startX = 0;
		this.startY = 0;
		this.startWidth = 0;
		this.startHeight = 0;
		this.isShiftKeyPressed = false;
		this.lastEditableState = void 0;
		this.handleMap = /* @__PURE__ */ new Map();
		this.handleMouseMove = (event) => {
			if (!this.isResizing || !this.activeHandle) return;
			const deltaX = event.clientX - this.startX;
			const deltaY = event.clientY - this.startY;
			this.handleResize(deltaX, deltaY);
		};
		this.handleTouchMove = (event) => {
			if (!this.isResizing || !this.activeHandle) return;
			const touch = event.touches[0];
			if (!touch) return;
			const deltaX = touch.clientX - this.startX;
			const deltaY = touch.clientY - this.startY;
			this.handleResize(deltaX, deltaY);
		};
		this.handleMouseUp = () => {
			if (!this.isResizing) return;
			const finalWidth = this.element.offsetWidth;
			const finalHeight = this.element.offsetHeight;
			this.onCommit(finalWidth, finalHeight);
			this.isResizing = false;
			this.activeHandle = null;
			this.container.dataset.resizeState = "false";
			if (this.classNames.resizing) this.container.classList.remove(this.classNames.resizing);
			document.removeEventListener("mousemove", this.handleMouseMove);
			document.removeEventListener("mouseup", this.handleMouseUp);
			document.removeEventListener("keydown", this.handleKeyDown);
			document.removeEventListener("keyup", this.handleKeyUp);
		};
		this.handleKeyDown = (event) => {
			if (event.key === "Shift") this.isShiftKeyPressed = true;
		};
		this.handleKeyUp = (event) => {
			if (event.key === "Shift") this.isShiftKeyPressed = false;
		};
		this.node = options.node;
		this.editor = options.editor;
		this.element = options.element;
		this.element.draggable = false;
		this.contentElement = options.contentElement;
		this.getPos = options.getPos;
		this.onResize = options.onResize;
		this.onCommit = options.onCommit;
		this.onUpdate = options.onUpdate;
		if ((_options$options = options.options) === null || _options$options === void 0 ? void 0 : _options$options.min) this.minSize = {
			...this.minSize,
			...options.options.min
		};
		if ((_options$options2 = options.options) === null || _options$options2 === void 0 ? void 0 : _options$options2.max) this.maxSize = options.options.max;
		if (options === null || options === void 0 || (_options$options3 = options.options) === null || _options$options3 === void 0 ? void 0 : _options$options3.directions) this.directions = options.options.directions;
		if ((_options$options4 = options.options) === null || _options$options4 === void 0 ? void 0 : _options$options4.preserveAspectRatio) this.preserveAspectRatio = options.options.preserveAspectRatio;
		if ((_options$options5 = options.options) === null || _options$options5 === void 0 ? void 0 : _options$options5.className) this.classNames = {
			container: options.options.className.container || "",
			wrapper: options.options.className.wrapper || "",
			handle: options.options.className.handle || "",
			resizing: options.options.className.resizing || ""
		};
		if ((_options$options6 = options.options) === null || _options$options6 === void 0 ? void 0 : _options$options6.createCustomHandle) this.createCustomHandle = options.options.createCustomHandle;
		this.wrapper = this.createWrapper();
		this.container = this.createContainer();
		this.applyInitialSize();
		this.attachHandles();
		this.editor.on("update", this.handleEditorUpdate.bind(this));
	}
	/**
	* Returns the top-level DOM node that should be placed in the editor.
	*
	* This is required by the ProseMirror NodeView interface. The container
	* includes the wrapper, handles, and the actual content element.
	*
	* @returns The container element to be inserted into the editor
	*/
	get dom() {
		return this.container;
	}
	get contentDOM() {
		var _this$contentElement;
		return (_this$contentElement = this.contentElement) !== null && _this$contentElement !== void 0 ? _this$contentElement : null;
	}
	handleEditorUpdate() {
		const isEditable = this.editor.isEditable;
		if (isEditable === this.lastEditableState) return;
		this.lastEditableState = isEditable;
		if (!isEditable) this.removeHandles();
		else if (isEditable && this.handleMap.size === 0) this.attachHandles();
	}
	/**
	* Called when the node's content or attributes change.
	*
	* Updates the internal node reference. If a custom `onUpdate` callback
	* was provided, it will be called to handle additional update logic.
	*
	* @param node - The new/updated node
	* @param decorations - Node decorations
	* @param innerDecorations - Inner decorations
	* @returns `false` if the node type has changed (requires full rebuild), otherwise the result of `onUpdate` or `true`
	*/
	update(node, decorations, innerDecorations) {
		if (node.type !== this.node.type) return false;
		this.node = node;
		if (this.onUpdate) return this.onUpdate(node, decorations, innerDecorations);
		return true;
	}
	/**
	* Cleanup method called when the node view is being removed.
	*
	* Removes all event listeners to prevent memory leaks. This is required
	* by the ProseMirror NodeView interface. If a resize is active when
	* destroy is called, it will be properly cancelled.
	*/
	destroy() {
		if (this.isResizing) {
			this.container.dataset.resizeState = "false";
			if (this.classNames.resizing) this.container.classList.remove(this.classNames.resizing);
			document.removeEventListener("mousemove", this.handleMouseMove);
			document.removeEventListener("mouseup", this.handleMouseUp);
			document.removeEventListener("keydown", this.handleKeyDown);
			document.removeEventListener("keyup", this.handleKeyUp);
			this.isResizing = false;
			this.activeHandle = null;
		}
		this.editor.off("update", this.handleEditorUpdate.bind(this));
		this.container.remove();
	}
	/**
	* Creates the outer container element.
	*
	* The container is the top-level element returned by the NodeView and
	* wraps the entire resizable node. It's set up with flexbox to handle
	* alignment and includes data attributes for styling and identification.
	*
	* @returns The container element
	*/
	createContainer() {
		const element = document.createElement("div");
		element.dataset.resizeContainer = "";
		element.dataset.node = this.node.type.name;
		element.style.display = this.node.type.isInline ? "inline-flex" : "flex";
		if (this.classNames.container) element.className = this.classNames.container;
		element.appendChild(this.wrapper);
		return element;
	}
	/**
	* Creates the wrapper element that contains the content and handles.
	*
	* The wrapper uses relative positioning so that resize handles can be
	* positioned absolutely within it. This is the direct parent of the
	* content element being made resizable.
	*
	* @returns The wrapper element
	*/
	createWrapper() {
		const element = document.createElement("div");
		element.style.position = "relative";
		element.style.display = "block";
		element.dataset.resizeWrapper = "";
		if (this.classNames.wrapper) element.className = this.classNames.wrapper;
		element.appendChild(this.element);
		return element;
	}
	/**
	* Creates a resize handle element for a specific direction.
	*
	* Each handle is absolutely positioned and includes a data attribute
	* identifying its direction for styling purposes.
	*
	* @param direction - The resize direction for this handle
	* @returns The handle element
	*/
	createHandle(direction) {
		const handle = document.createElement("div");
		handle.dataset.resizeHandle = direction;
		handle.style.position = "absolute";
		if (this.classNames.handle) handle.className = this.classNames.handle;
		return handle;
	}
	/**
	* Positions a handle element according to its direction.
	*
	* Corner handles (e.g., 'top-left') are positioned at the intersection
	* of two edges. Edge handles (e.g., 'top') span the full width or height.
	*
	* @param handle - The handle element to position
	* @param direction - The direction determining the position
	*/
	positionHandle(handle, direction) {
		const isTop = direction.includes("top");
		const isBottom = direction.includes("bottom");
		const isLeft = direction.includes("left");
		const isRight = direction.includes("right");
		if (isTop) handle.style.top = "0";
		if (isBottom) handle.style.bottom = "0";
		if (isLeft) handle.style.left = "0";
		if (isRight) handle.style.right = "0";
		if (direction === "top" || direction === "bottom") {
			handle.style.left = "0";
			handle.style.right = "0";
		}
		if (direction === "left" || direction === "right") {
			handle.style.top = "0";
			handle.style.bottom = "0";
		}
	}
	/**
	* Creates and attaches all resize handles to the wrapper.
	*
	* Iterates through the configured directions, creates a handle for each,
	* positions it, attaches the mousedown listener, and appends it to the DOM.
	*/
	attachHandles() {
		this.directions.forEach((direction) => {
			let handle;
			if (this.createCustomHandle) handle = this.createCustomHandle(direction);
			else handle = this.createHandle(direction);
			if (!(handle instanceof HTMLElement)) {
				console.warn(`[ResizableNodeView] createCustomHandle("${direction}") did not return an HTMLElement. Falling back to default handle.`);
				handle = this.createHandle(direction);
			}
			if (!this.createCustomHandle) this.positionHandle(handle, direction);
			handle.addEventListener("mousedown", (event) => this.handleResizeStart(event, direction));
			handle.addEventListener("touchstart", (event) => this.handleResizeStart(event, direction));
			this.handleMap.set(direction, handle);
			this.wrapper.appendChild(handle);
		});
	}
	/**
	* Removes all resize handles from the wrapper.
	*
	* Cleans up the handle map and removes each handle element from the DOM.
	*/
	removeHandles() {
		this.handleMap.forEach((el) => el.remove());
		this.handleMap.clear();
	}
	/**
	* Applies initial sizing from node attributes to the element.
	*
	* If width/height attributes exist on the node, they're applied to the element.
	* Otherwise, the element's natural/current dimensions are measured. The aspect
	* ratio is calculated for later use in aspect-ratio-preserving resizes.
	*/
	applyInitialSize() {
		const width = this.node.attrs.width;
		const height = this.node.attrs.height;
		if (width) {
			this.element.style.width = `${width}px`;
			this.initialWidth = width;
		} else this.initialWidth = this.element.offsetWidth;
		if (height) {
			this.element.style.height = `${height}px`;
			this.initialHeight = height;
		} else this.initialHeight = this.element.offsetHeight;
		if (this.initialWidth > 0 && this.initialHeight > 0) this.aspectRatio = this.initialWidth / this.initialHeight;
	}
	/**
	* Initiates a resize operation when a handle is clicked.
	*
	* Captures the starting mouse position and element dimensions, sets up
	* the resize state, adds the resizing class and state attribute, and
	* attaches document-level listeners for mouse movement and keyboard input.
	*
	* @param event - The mouse down event
	* @param direction - The direction of the handle being dragged
	*/
	handleResizeStart(event, direction) {
		event.preventDefault();
		event.stopPropagation();
		this.isResizing = true;
		this.activeHandle = direction;
		if (isTouchEvent(event)) {
			this.startX = event.touches[0].clientX;
			this.startY = event.touches[0].clientY;
		} else {
			this.startX = event.clientX;
			this.startY = event.clientY;
		}
		this.startWidth = this.element.offsetWidth;
		this.startHeight = this.element.offsetHeight;
		if (this.startWidth > 0 && this.startHeight > 0) this.aspectRatio = this.startWidth / this.startHeight;
		if (this.getPos() !== void 0) {}
		this.container.dataset.resizeState = "true";
		if (this.classNames.resizing) this.container.classList.add(this.classNames.resizing);
		document.addEventListener("mousemove", this.handleMouseMove);
		document.addEventListener("touchmove", this.handleTouchMove);
		document.addEventListener("mouseup", this.handleMouseUp);
		document.addEventListener("keydown", this.handleKeyDown);
		document.addEventListener("keyup", this.handleKeyUp);
	}
	handleResize(deltaX, deltaY) {
		if (!this.activeHandle) return;
		const shouldPreserveAspectRatio = this.preserveAspectRatio || this.isShiftKeyPressed;
		const { width, height } = this.calculateNewDimensions(this.activeHandle, deltaX, deltaY);
		const constrained = this.applyConstraints(width, height, shouldPreserveAspectRatio);
		this.element.style.width = `${constrained.width}px`;
		this.element.style.height = `${constrained.height}px`;
		if (this.onResize) this.onResize(constrained.width, constrained.height);
	}
	/**
	* Calculates new dimensions based on mouse delta and resize direction.
	*
	* Takes the starting dimensions and applies the mouse movement delta
	* according to the handle direction. For corner handles, both dimensions
	* are affected. For edge handles, only one dimension changes. If aspect
	* ratio should be preserved, delegates to applyAspectRatio.
	*
	* @param direction - The active resize handle direction
	* @param deltaX - Horizontal mouse movement since resize start
	* @param deltaY - Vertical mouse movement since resize start
	* @returns The calculated width and height
	*/
	calculateNewDimensions(direction, deltaX, deltaY) {
		let newWidth = this.startWidth;
		let newHeight = this.startHeight;
		const isRight = direction.includes("right");
		const isLeft = direction.includes("left");
		const isBottom = direction.includes("bottom");
		const isTop = direction.includes("top");
		if (isRight) newWidth = this.startWidth + deltaX;
		else if (isLeft) newWidth = this.startWidth - deltaX;
		if (isBottom) newHeight = this.startHeight + deltaY;
		else if (isTop) newHeight = this.startHeight - deltaY;
		if (direction === "right" || direction === "left") newWidth = this.startWidth + (isRight ? deltaX : -deltaX);
		if (direction === "top" || direction === "bottom") newHeight = this.startHeight + (isBottom ? deltaY : -deltaY);
		if (this.preserveAspectRatio || this.isShiftKeyPressed) return this.applyAspectRatio(newWidth, newHeight, direction);
		return {
			width: newWidth,
			height: newHeight
		};
	}
	/**
	* Applies min/max constraints to dimensions.
	*
	* When aspect ratio is NOT preserved, constraints are applied independently
	* to width and height. When aspect ratio IS preserved, constraints are
	* applied while maintaining the aspect ratio—if one dimension hits a limit,
	* the other is recalculated proportionally.
	*
	* This ensures that aspect ratio is never broken when constrained.
	*
	* @param width - The unconstrained width
	* @param height - The unconstrained height
	* @param preserveAspectRatio - Whether to maintain aspect ratio while constraining
	* @returns The constrained dimensions
	*/
	applyConstraints(width, height, preserveAspectRatio) {
		var _this$maxSize3, _this$maxSize4;
		if (!preserveAspectRatio) {
			var _this$maxSize, _this$maxSize2;
			let constrainedWidth = Math.max(this.minSize.width, width);
			let constrainedHeight = Math.max(this.minSize.height, height);
			if ((_this$maxSize = this.maxSize) === null || _this$maxSize === void 0 ? void 0 : _this$maxSize.width) constrainedWidth = Math.min(this.maxSize.width, constrainedWidth);
			if ((_this$maxSize2 = this.maxSize) === null || _this$maxSize2 === void 0 ? void 0 : _this$maxSize2.height) constrainedHeight = Math.min(this.maxSize.height, constrainedHeight);
			return {
				width: constrainedWidth,
				height: constrainedHeight
			};
		}
		let constrainedWidth = width;
		let constrainedHeight = height;
		if (constrainedWidth < this.minSize.width) {
			constrainedWidth = this.minSize.width;
			constrainedHeight = constrainedWidth / this.aspectRatio;
		}
		if (constrainedHeight < this.minSize.height) {
			constrainedHeight = this.minSize.height;
			constrainedWidth = constrainedHeight * this.aspectRatio;
		}
		if (((_this$maxSize3 = this.maxSize) === null || _this$maxSize3 === void 0 ? void 0 : _this$maxSize3.width) && constrainedWidth > this.maxSize.width) {
			constrainedWidth = this.maxSize.width;
			constrainedHeight = constrainedWidth / this.aspectRatio;
		}
		if (((_this$maxSize4 = this.maxSize) === null || _this$maxSize4 === void 0 ? void 0 : _this$maxSize4.height) && constrainedHeight > this.maxSize.height) {
			constrainedHeight = this.maxSize.height;
			constrainedWidth = constrainedHeight * this.aspectRatio;
		}
		return {
			width: constrainedWidth,
			height: constrainedHeight
		};
	}
	/**
	* Adjusts dimensions to maintain the original aspect ratio.
	*
	* For horizontal handles (left/right), uses width as the primary dimension
	* and calculates height from it. For vertical handles (top/bottom), uses
	* height as primary and calculates width. For corner handles, uses width
	* as the primary dimension.
	*
	* @param width - The new width
	* @param height - The new height
	* @param direction - The active resize direction
	* @returns Dimensions adjusted to preserve aspect ratio
	*/
	applyAspectRatio(width, height, direction) {
		const isHorizontal = direction === "left" || direction === "right";
		const isVertical = direction === "top" || direction === "bottom";
		if (isHorizontal) return {
			width,
			height: width / this.aspectRatio
		};
		if (isVertical) return {
			width: height * this.aspectRatio,
			height
		};
		return {
			width,
			height: width / this.aspectRatio
		};
	}
};
/**
* Alias for ResizableNodeView to maintain consistent naming.
* @deprecated Use ResizableNodeView instead - will be removed in future versions.
*/
var ResizableNodeview = ResizableNodeView;
/**
* The Node class is used to create custom node extensions.
* @see https://tiptap.dev/api/extensions#create-a-new-extension
*/
var Node = class Node extends Extendable {
	constructor(..._args) {
		super(..._args);
		this.type = "node";
	}
	/**
	* Create a new Node instance
	* @param config - Node configuration object or a function that returns a configuration object
	*/
	static create(config = {}) {
		return new Node(typeof config === "function" ? config() : config);
	}
	configure(options) {
		return super.configure(options);
	}
	extend(extendedConfig) {
		const resolvedConfig = typeof extendedConfig === "function" ? extendedConfig() : extendedConfig;
		return super.extend(resolvedConfig);
	}
};
/**
* Node views are used to customize the rendered DOM structure of a node.
* @see https://tiptap.dev/guide/node-views
*/
var NodeView = class {
	constructor(component, props, options) {
		this.isDragging = false;
		this.component = component;
		this.editor = props.editor;
		this.options = {
			stopEvent: null,
			ignoreMutation: null,
			...options
		};
		this.extension = props.extension;
		this.node = props.node;
		this.decorations = props.decorations;
		this.innerDecorations = props.innerDecorations;
		this.view = props.view;
		this.HTMLAttributes = props.HTMLAttributes;
		this.getPos = () => {
			try {
				return props.getPos();
			} catch {
				return;
			}
		};
		this.mount();
	}
	mount() {}
	get dom() {
		return this.editor.view.dom;
	}
	get contentDOM() {
		return null;
	}
	onDragStart(event) {
		var _target$parentElement, _this$contentDOM;
		const { view } = this.editor;
		const target = event.target;
		const dragHandle = target.nodeType === 3 ? (_target$parentElement = target.parentElement) === null || _target$parentElement === void 0 ? void 0 : _target$parentElement.closest("[data-drag-handle]") : target.closest("[data-drag-handle]");
		if (!this.dom || ((_this$contentDOM = this.contentDOM) === null || _this$contentDOM === void 0 ? void 0 : _this$contentDOM.contains(target)) || !dragHandle) return;
		let x = 0;
		let y = 0;
		if (this.dom !== dragHandle) {
			var _event$offsetX, _nativeEvent, _event$offsetY, _nativeEvent2;
			const domBox = this.dom.getBoundingClientRect();
			const handleBox = dragHandle.getBoundingClientRect();
			const offsetX = (_event$offsetX = event.offsetX) !== null && _event$offsetX !== void 0 ? _event$offsetX : (_nativeEvent = event.nativeEvent) === null || _nativeEvent === void 0 ? void 0 : _nativeEvent.offsetX;
			const offsetY = (_event$offsetY = event.offsetY) !== null && _event$offsetY !== void 0 ? _event$offsetY : (_nativeEvent2 = event.nativeEvent) === null || _nativeEvent2 === void 0 ? void 0 : _nativeEvent2.offsetY;
			x = handleBox.x - domBox.x + offsetX;
			y = handleBox.y - domBox.y + offsetY;
		}
		const clonedNode = this.dom.cloneNode(true);
		try {
			const domBox = this.dom.getBoundingClientRect();
			clonedNode.style.width = `${Math.round(domBox.width)}px`;
			clonedNode.style.height = `${Math.round(domBox.height)}px`;
			clonedNode.style.boxSizing = "border-box";
			clonedNode.style.pointerEvents = "none";
		} catch {}
		let dragImageWrapper = null;
		try {
			var _event$dataTransfer;
			dragImageWrapper = document.createElement("div");
			dragImageWrapper.style.position = "absolute";
			dragImageWrapper.style.top = "-9999px";
			dragImageWrapper.style.left = "-9999px";
			dragImageWrapper.style.pointerEvents = "none";
			dragImageWrapper.appendChild(clonedNode);
			document.body.appendChild(dragImageWrapper);
			(_event$dataTransfer = event.dataTransfer) === null || _event$dataTransfer === void 0 || _event$dataTransfer.setDragImage(clonedNode, x, y);
		} finally {
			if (dragImageWrapper) setTimeout(() => {
				try {
					dragImageWrapper === null || dragImageWrapper === void 0 || dragImageWrapper.remove();
				} catch {}
			}, 0);
		}
		const pos = this.getPos();
		if (typeof pos !== "number") return;
		const selection = NodeSelection.create(view.state.doc, pos);
		const transaction = view.state.tr.setSelection(selection);
		view.dispatch(transaction);
	}
	stopEvent(event) {
		var _this$contentDOM2;
		if (!this.dom) return false;
		if (typeof this.options.stopEvent === "function") return this.options.stopEvent({ event });
		const target = event.target;
		if (!(this.dom.contains(target) && !((_this$contentDOM2 = this.contentDOM) === null || _this$contentDOM2 === void 0 ? void 0 : _this$contentDOM2.contains(target)))) return false;
		const isDragEvent = event.type.startsWith("drag");
		const isDragOverEnterEvent = event.type === "dragover" || event.type === "dragenter";
		const isDropEvent = event.type === "drop";
		if (([
			"INPUT",
			"BUTTON",
			"SELECT",
			"TEXTAREA"
		].includes(target.tagName) || target.isContentEditable) && !isDropEvent && !isDragEvent) return true;
		const { isEditable } = this.editor;
		const { isDragging } = this;
		const isDraggable = !!this.node.type.spec.draggable;
		const isSelectable = NodeSelection.isSelectable(this.node);
		const isCopyEvent = event.type === "copy";
		const isPasteEvent = event.type === "paste";
		const isCutEvent = event.type === "cut";
		const isClickEvent = event.type === "mousedown";
		if (!isDraggable && isSelectable && isDragEvent && event.target === this.dom) event.preventDefault();
		if (isDraggable && isDragEvent && !isDragging && event.target === this.dom) {
			event.preventDefault();
			return false;
		}
		if (isDraggable && isEditable && !isDragging && isClickEvent) {
			const dragHandle = target.closest("[data-drag-handle]");
			if (dragHandle && (this.dom === dragHandle || this.dom.contains(dragHandle))) {
				this.isDragging = true;
				document.addEventListener("dragend", () => {
					this.isDragging = false;
				}, { once: true });
				document.addEventListener("drop", () => {
					this.isDragging = false;
				}, { once: true });
				document.addEventListener("mouseup", () => {
					this.isDragging = false;
				}, { once: true });
			}
		}
		if (isDragging || isDragOverEnterEvent || isDropEvent || isCopyEvent || isPasteEvent || isCutEvent || isClickEvent && isSelectable) return false;
		return true;
	}
	/**
	* Called when a DOM [mutation](https://developer.mozilla.org/en-US/docs/Web/API/MutationObserver) or a selection change happens within the view.
	* @return `false` if the editor should re-read the selection or re-parse the range around the mutation
	* @return `true` if it can safely be ignored.
	*/
	ignoreMutation(mutation) {
		if (!this.dom || !this.contentDOM) return true;
		if (typeof this.options.ignoreMutation === "function") return this.options.ignoreMutation({ mutation });
		if (this.node.isLeaf || this.node.isAtom) return true;
		if (mutation.type === "selection") return false;
		if (this.contentDOM.contains(mutation.target) && mutation.type === "childList" && (isiOS() || isAndroid()) && this.editor.isFocused) {
			if ([...Array.from(mutation.addedNodes), ...Array.from(mutation.removedNodes)].every((node) => node.isContentEditable)) return false;
		}
		if (this.contentDOM === mutation.target && mutation.type === "attributes") return true;
		if (this.contentDOM.contains(mutation.target)) return false;
		return true;
	}
	/**
	* Update the attributes of the prosemirror node.
	*/
	updateAttributes(attributes) {
		this.editor.commands.command(({ tr }) => {
			const pos = this.getPos();
			if (typeof pos !== "number") return false;
			tr.setNodeMarkup(pos, void 0, {
				...this.node.attrs,
				...attributes
			});
			return true;
		});
	}
	/**
	* Delete the node.
	*/
	deleteNode() {
		const from = this.getPos();
		if (typeof from !== "number") return;
		const to = from + this.node.nodeSize;
		this.editor.commands.deleteRange({
			from,
			to
		});
	}
};
/**
* Build an paste rule that adds a mark when the
* matched text is pasted into it.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#paste-rules
*/
function markPasteRule(config) {
	return new PasteRule({
		find: config.find,
		handler: ({ state, range, match, pasteEvent }) => {
			const attributes = callOrReturn(config.getAttributes, void 0, match, pasteEvent);
			if (attributes === false || attributes === null) return null;
			const { tr } = state;
			const captureGroup = match[match.length - 1];
			const fullMatch = match[0];
			let markEnd = range.to;
			if (captureGroup) {
				const startSpaces = fullMatch.search(/\S/);
				const textStart = range.from + fullMatch.indexOf(captureGroup);
				const textEnd = textStart + captureGroup.length;
				if (getMarksBetween(range.from, range.to, state.doc).filter((item) => {
					return item.mark.type.excluded.find((type) => type === config.type && type !== item.mark.type);
				}).filter((item) => item.to > textStart).length) return null;
				if (textEnd < range.to) tr.delete(textEnd, range.to);
				if (textStart > range.from) tr.delete(range.from + startSpaces, textStart);
				markEnd = range.from + startSpaces + captureGroup.length;
				tr.addMark(range.from + startSpaces, markEnd, config.type.create(attributes || {}));
				if (!(match.index !== void 0 && match.input !== void 0 && match.index + match[0].length >= match.input.length)) tr.removeStoredMark(config.type);
			}
		}
	});
}
/**
* Build an paste rule that adds a node when the
* matched text is pasted into it.
* @see https://tiptap.dev/docs/editor/api/paste-rules
*/
function nodePasteRule(config) {
	return new PasteRule({
		find: config.find,
		handler({ match, chain, range, pasteEvent }) {
			const attributes = callOrReturn(config.getAttributes, void 0, match, pasteEvent);
			const content = callOrReturn(config.getContent, void 0, attributes);
			if (attributes === false || attributes === null) return null;
			const node = {
				type: config.type.name,
				attrs: attributes
			};
			if (content) node.content = content;
			if (match.input) chain().deleteRange(range).insertContentAt(range.from, node);
		}
	});
}
/**
* Build an paste rule that replaces text when the
* matched text is pasted into it.
* @see https://tiptap.dev/docs/editor/extensions/custom-extensions/extend-existing#paste-rules
*/
function textPasteRule(config) {
	return new PasteRule({
		find: config.find,
		handler: ({ state, range, match }) => {
			let insert = config.replace;
			let start = range.from;
			const end = range.to;
			if (match[1]) {
				const offset = match[0].lastIndexOf(match[1]);
				insert += match[0].slice(offset + match[1].length);
				start += offset;
				const cutOff = start - end;
				if (cutOff > 0) {
					insert = match[0].slice(offset - cutOff, offset) + insert;
					start = end;
				}
			}
			state.tr.insertText(insert, start, end);
		}
	});
}
var Tracker = class {
	constructor(transaction) {
		this.transaction = transaction;
		this.currentStep = this.transaction.steps.length;
	}
	map(position) {
		let deleted = false;
		return {
			position: this.transaction.steps.slice(this.currentStep).reduce((newPosition, step) => {
				const mapResult = step.getMap().mapResult(newPosition);
				if (mapResult.deleted) deleted = true;
				return mapResult.pos;
			}, position),
			deleted
		};
	}
};
//#endregion
export { generateJSON as $, isProseMirrorNodeSelection as $t, createChainableState as A, resolveFocusPosition as An, isActive as At, dist_exports as B, wrappingInputRule as Bn, isMarkActive as Bt, attrsEqual as C, parseAttributes as Cn, getTextBetween as Ct, commands_exports as D, removeDuplicates as Dn, h as Dt, combineTransactionSteps as E, posToDOMRect as En, getUpdatedPosition as Et, createStyleTag as F, splitExtensions as Fn, isExtensionRulesEnabled as Ft, findChildren as G, isNumber as Gt, encodeHtmlEntities as H, isNodeEmpty as Ht, createWidgetDecoration as I, textInputRule as In, isFirefox as It, findParentNode as J, isProseMirrorAddNodeMarkStep as Jt, findChildrenInRange as K, isPlainObject as Kt, decodeHtmlEntities as L, textPasteRule as Ln, isFunction as Lt, createInlineMarkdownSpec as M, selectionToInsertionEnd as Mn, isAtEndOfNode as Mt, createMappablePosition as N, serializeAttributes as Nn, isAtStartOfNode as Nt, createAtomBlockMarkdownSpec as O, renderNestedMarkdownContent as On, injectExtensionAttributesToParseRule as Ot, createNodeFromContent as P, sortExtensions as Pn, isEmptyObject as Pt, generateHTML as Q, isProseMirrorFragment as Qt, defaultBlockAt as R, textblockTypeInputRule as Rn, isList as Rt, WidgetDecoration as S, objectIncludes as Sn, getText as St, canInsertNode as T, pasteRulesPlugin as Tn, getTextSerializersFromSchema as Tt, escapeForRegEx as U, isNodeSelection as Ut, elementFromString as V, keydownHandler as Vn, isNodeActive as Vt, extensions_exports as W, isNodeViewSelected as Wt, flattenExtensions as X, isProseMirrorCellSelection as Xt, findParentNodeClosestToPos as Y, isProseMirrorAttrStep as Yt, fromString as Z, isProseMirrorDocAttrStep as Zt, NodeView as _, mergeAttributes as _n, getSchemaByResolvedExtensions as _t, Editor as a, isProseMirrorStep as an, getExtensionField as at, ResizableNodeview as b, nodeInputRule as bn, getSplittedAttributes as bt, Fragment as c, isSafari as cn, getMarkRange as ct, MappablePosition as d, isiOS as dn, getNodeAtPosition as dt, isProseMirrorRemoveMarkStep as en, generateText as et, Mark as f, liveWidgetKeys as fn, getNodeAttributes as ft, NodePos as g, marksEqual as gn, getSchema as gt, NodeDecoration as h, markdown_exports as hn, getRenderedAttributes as ht, DecorationManager as i, isProseMirrorSlice as in, getDebugJSON as it, createDocument as j, rewriteUnknownContent as jn, isAndroid as jt, createBlockMarkdownSpec as k, resolveExtensions as kn, inputRulesPlugin as kt, InlineDecoration as l, isString as ln, getMarkType as lt, Node as m, markPasteRule as mn, getPreviousBlockSibling as mt, DECORATION_MANAGER_PLUGIN_KEY as n, isProseMirrorReplaceAroundStep as nn, getAttributesFromExtensions as nt, Extendable as o, isProseMirrorStepResult as on, getHTMLFromFragment as ot, MarkView as p, markInputRule as pn, getNodeType as pt, findDuplicates as q, isProseMirrorAddMarkStep as qt, Decoration as r, isProseMirrorReplaceStep as rn, getChangedRanges as rt, Extension as s, isRegExp as sn, getMarkAttributes as st, CommandManager as t, isProseMirrorRemoveNodeMarkStep as tn, getAttributes as tt, InputRule as u, isTextSelection as un, getMarksBetween as ut, PasteRule as v, mergeDeep as vn, getSchemaTypeByName as vt, callOrReturn as w, parseIndentedBlocks as wn, getTextContentFromNodes as wt, Tracker as x, nodePasteRule as xn, getStyleProperty as xt, ResizableNodeView as y, minMax as yn, getSchemaTypeNameByName as yt, deleteProps as z, updateMarkViewAttributes as zn, isMacOS as zt };
