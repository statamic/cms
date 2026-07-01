import { n as __exportAll } from "./chunk-B-1-B7_t.js";
import { c as NodeRange, f as Schema, i as Fragment, n as DOMParser, p as Slice, r as DOMSerializer, s as Node } from "./dist-AY222wij.js";
import { a as PluginKey, b as liftTarget, c as TextSelection, d as RemoveMarkStep, f as ReplaceAroundStep, g as canSplit, h as canJoin, i as Plugin, m as Transform, n as EditorState, o as Selection, p as ReplaceStep, r as NodeSelection, t as AllSelection, v as findWrapping, x as replaceStep, y as joinPoint } from "./dist-QivoNauO.js";
import { i as EditorView } from "./view-BHoMFKQh.js";
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
var joinBackward$1 = (state, dispatch, view) => {
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
var joinTextblockBackward$1 = (state, dispatch, view) => {
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
var joinTextblockForward$1 = (state, dispatch, view) => {
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
var selectNodeBackward$1 = (state, dispatch, view) => {
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
var joinForward$1 = (state, dispatch, view) => {
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
var selectNodeForward$1 = (state, dispatch, view) => {
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
var joinUp$1 = (state, dispatch) => {
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
var joinDown$1 = (state, dispatch) => {
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
var lift$1 = (state, dispatch) => {
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
var newlineInCode$1 = (state, dispatch) => {
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
var exitCode$1 = (state, dispatch) => {
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
var createParagraphNear$1 = (state, dispatch) => {
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
var liftEmptyBlock$1 = (state, dispatch) => {
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
		let { $from, $to } = state.selection;
		if (state.selection instanceof NodeSelection && state.selection.node.isBlock) {
			if (!$from.parentOffset || !canSplit(state.doc, $from.pos)) return false;
			if (dispatch) dispatch(state.tr.split($from.pos).scrollIntoView());
			return true;
		}
		if (!$from.depth) return false;
		let types = [];
		let splitDepth, deflt, atEnd = false, atStart = false;
		for (let d = $from.depth;; d--) if ($from.node(d).isBlock) {
			atEnd = $from.end(d) == $from.pos + ($from.depth - d);
			atStart = $from.start(d) == $from.pos - ($from.depth - d);
			deflt = defaultBlockAt$1($from.node(d - 1).contentMatchAt($from.indexAfter(d - 1)));
			let splitType = splitNode && splitNode($to.parent, atEnd, $from);
			types.unshift(splitType || (atEnd && deflt ? { type: deflt } : null));
			splitDepth = d;
			break;
		} else {
			if (d == 1) return false;
			types.unshift(null);
		}
		let tr = state.tr;
		if (state.selection instanceof TextSelection || state.selection instanceof AllSelection) tr.deleteSelection();
		let splitPos = tr.mapping.map($from.pos);
		let can = canSplit(tr.doc, splitPos, types.length, types);
		if (!can) {
			types[0] = deflt ? { type: deflt } : null;
			can = canSplit(tr.doc, splitPos, types.length, types);
		}
		if (!can) return false;
		tr.split(splitPos, types.length, types);
		if (!atEnd && atStart && $from.node(splitDepth).type != deflt) {
			let first = tr.mapping.map($from.before(splitDepth)), $first = tr.doc.resolve(first);
			if (deflt && $from.node(splitDepth - 1).canReplaceWith($first.index(), $first.index() + 1, deflt)) tr.setNodeMarkup(tr.mapping.map($from.before(splitDepth)), deflt);
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
var selectParentNode$1 = (state, dispatch) => {
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
			let end = $cut.pos + after.nodeSize, wrap = Fragment.empty;
			for (let i = conn.length - 1; i >= 0; i--) wrap = Fragment.from(conn[i].create(null, wrap));
			wrap = Fragment.from(before.copy(wrap));
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
				let end = Fragment.empty;
				for (let i = wrap.length - 1; i >= 0; i--) end = Fragment.from(wrap[i].copy(end));
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
var selectTextblockStart$1 = selectTextblockSide(-1);
/**
Moves the cursor to the end of current text block.
*/
var selectTextblockEnd$1 = selectTextblockSide(1);
/**
Wrap the selection in a node of the given type with the given
attributes.
*/
function wrapIn$1(nodeType, attrs = null) {
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
var backspace = chainCommands(deleteSelection$1, joinBackward$1, selectNodeBackward$1);
var del = chainCommands(deleteSelection$1, joinForward$1, selectNodeForward$1);
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
	"Enter": chainCommands(newlineInCode$1, createParagraphNear$1, liftEmptyBlock$1, splitBlock$1),
	"Mod-Enter": exitCode$1,
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
	"Ctrl-a": selectTextblockStart$1,
	"Ctrl-e": selectTextblockEnd$1
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
function wrapInList$1(listType, attrs = null) {
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
	let content = Fragment.empty;
	for (let i = wrappers.length - 1; i >= 0; i--) content = Fragment.from(wrappers[i].type.create(wrappers[i].attrs, content));
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
function liftListItem$1(itemType) {
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
		tr.step(new ReplaceAroundStep(end - 1, endOfList, end, endOfList, new Slice(Fragment.from(itemType.create(null, range.parent.copy())), 1, 0), 1, true));
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
	if (!parent.canReplace(indexBefore + (atStart ? 0 : 1), indexBefore + 1, item.content.append(atEnd ? Fragment.empty : Fragment.from(list)))) return false;
	let start = $start.pos, end = start + item.nodeSize;
	tr.step(new ReplaceAroundStep(start - (atStart ? 1 : 0), end + (atEnd ? 1 : 0), start + 1, end - 1, new Slice((atStart ? Fragment.empty : Fragment.from(list.copy(Fragment.empty))).append(atEnd ? Fragment.empty : Fragment.from(list.copy(Fragment.empty))), atStart ? 0 : 1, atEnd ? 0 : 1), atStart ? 0 : 1));
	dispatch(tr.scrollIntoView());
	return true;
}
/**
Create a command to sink the list item around the selection down
into an inner list.
*/
function sinkListItem$1(itemType) {
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
			let inner = Fragment.from(nestedBefore ? itemType.create() : null);
			let slice = new Slice(Fragment.from(itemType.create(null, Fragment.from(parent.type.create(null, inner)))), nestedBefore ? 3 : 1, 0);
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
var dist_exports = /* @__PURE__ */ __exportAll({
	CommandManager: () => CommandManager,
	Editor: () => Editor,
	Extendable: () => Extendable,
	Extension: () => Extension,
	Fragment: () => Fragment6,
	InputRule: () => InputRule,
	MappablePosition: () => MappablePosition,
	Mark: () => Mark,
	MarkView: () => MarkView,
	Node: () => Node3,
	NodePos: () => NodePos,
	NodeView: () => NodeView,
	PasteRule: () => PasteRule,
	ResizableNodeView: () => ResizableNodeView,
	ResizableNodeview: () => ResizableNodeview,
	Tracker: () => Tracker,
	callOrReturn: () => callOrReturn,
	canInsertNode: () => canInsertNode,
	cancelPositionCheck: () => cancelPositionCheck,
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
	isNumber: () => isNumber,
	isPlainObject: () => isPlainObject,
	isRegExp: () => isRegExp,
	isSafari: () => isSafari,
	isString: () => isString,
	isTextSelection: () => isTextSelection,
	isiOS: () => isiOS,
	markInputRule: () => markInputRule,
	markPasteRule: () => markPasteRule,
	markdown: () => markdown_exports,
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
	schedulePositionCheck: () => schedulePositionCheck,
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
var __defProp = Object.defineProperty;
var __export = (target, all) => {
	for (var name in all) __defProp(target, name, {
		get: all[name],
		enumerable: true
	});
};
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
var CommandManager = class {
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
		return Object.fromEntries(Object.entries(rawCommands).map(([name, command2]) => {
			const method = (...args) => {
				const callback = command2(...args)(props);
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
		const run3 = () => {
			if (!hasStartTransaction && shouldDispatch && !tr.getMeta("preventDispatch") && !this.hasCustomState) view.dispatch(tr);
			return callbacks.every((callback) => callback === true);
		};
		const chain = {
			...Object.fromEntries(Object.entries(rawCommands).map(([name, command2]) => {
				const chainedCommand = (...args) => {
					const props = this.buildProps(tr, shouldDispatch);
					const callback = command2(...args)(props);
					callbacks.push(callback);
					return chain;
				};
				return [name, chainedCommand];
			})),
			run: run3
		};
		return chain;
	}
	createCan(startTr) {
		const { rawCommands, state } = this;
		const dispatch = false;
		const tr = startTr || state.tr;
		const props = this.buildProps(tr, dispatch);
		return {
			...Object.fromEntries(Object.entries(rawCommands).map(([name, command2]) => {
				return [name, (...args) => command2(...args)({
					...props,
					dispatch: void 0
				})];
			})),
			chain: () => this.createChain(tr, dispatch)
		};
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
				return Object.fromEntries(Object.entries(rawCommands).map(([name, command2]) => {
					return [name, (...args) => command2(...args)(props)];
				}));
			}
		};
		return props;
	}
};
var commands_exports = {};
__export(commands_exports, {
	blur: () => blur,
	clearContent: () => clearContent,
	clearNodes: () => clearNodes,
	command: () => command,
	createParagraphNear: () => createParagraphNear,
	cut: () => cut,
	deleteCurrentNode: () => deleteCurrentNode,
	deleteNode: () => deleteNode,
	deleteRange: () => deleteRange,
	deleteSelection: () => deleteSelection,
	enter: () => enter,
	exitCode: () => exitCode,
	extendMarkRange: () => extendMarkRange,
	first: () => first,
	focus: () => focus,
	forEach: () => forEach,
	insertContent: () => insertContent,
	insertContentAt: () => insertContentAt,
	joinBackward: () => joinBackward,
	joinDown: () => joinDown,
	joinForward: () => joinForward,
	joinItemBackward: () => joinItemBackward,
	joinItemForward: () => joinItemForward,
	joinTextblockBackward: () => joinTextblockBackward,
	joinTextblockForward: () => joinTextblockForward,
	joinUp: () => joinUp,
	keyboardShortcut: () => keyboardShortcut,
	lift: () => lift,
	liftEmptyBlock: () => liftEmptyBlock,
	liftListItem: () => liftListItem,
	newlineInCode: () => newlineInCode,
	resetAttributes: () => resetAttributes,
	scrollIntoView: () => scrollIntoView,
	selectAll: () => selectAll,
	selectNodeBackward: () => selectNodeBackward,
	selectNodeForward: () => selectNodeForward,
	selectParentNode: () => selectParentNode,
	selectTextblockEnd: () => selectTextblockEnd,
	selectTextblockStart: () => selectTextblockStart,
	setContent: () => setContent,
	setMark: () => setMark,
	setMeta: () => setMeta,
	setNode: () => setNode,
	setNodeSelection: () => setNodeSelection,
	setTextDirection: () => setTextDirection,
	setTextSelection: () => setTextSelection,
	sinkListItem: () => sinkListItem,
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
	wrapIn: () => wrapIn,
	wrapInList: () => wrapInList
});
var blur = () => ({ editor, view }) => {
	requestAnimationFrame(() => {
		var _a;
		if (!editor.isDestroyed) {
			view.dom.blur();
			(_a = window == null ? void 0 : window.getSelection()) == null || _a.removeAllRanges();
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
var createParagraphNear = () => ({ state, dispatch }) => {
	return createParagraphNear$1(state, dispatch);
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
var deleteSelection = () => ({ state, dispatch }) => {
	return deleteSelection$1(state, dispatch);
};
var enter = () => ({ commands }) => {
	return commands.keyboardShortcut("Enter");
};
var exitCode = () => ({ state, dispatch }) => {
	return exitCode$1(state, dispatch);
};
function isRegExp(value) {
	return Object.prototype.toString.call(value) === "[object RegExp]";
}
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
function getMarkRange($pos, type, attributes) {
	if (!$pos || !type) return;
	let start = $pos.parent.childAfter($pos.parentOffset);
	if (!start.node || !start.node.marks.some((mark2) => mark2.type === type)) start = $pos.parent.childBefore($pos.parentOffset);
	if (!start.node || !start.node.marks.some((mark2) => mark2.type === type)) return;
	if (!attributes) {
		const firstMark = start.node.marks.find((mark2) => mark2.type === type);
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
	return navigator.platform === "Android" || /android/i.test(navigator.userAgent);
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
				if (options == null ? void 0 : options.scrollIntoView) editor.commands.scrollIntoView();
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
function createNodeFromContent(content, schema, options) {
	if (content instanceof Node || content instanceof Fragment) return content;
	options = {
		slice: true,
		parseOptions: {},
		...options
	};
	const isJSONContent = typeof content === "object" && content !== null;
	const isTextContent = typeof content === "string";
	if (isJSONContent) try {
		if (Array.isArray(content) && content.length > 0) return Fragment.fromArray(content.map((item) => schema.nodeFromJSON(item)));
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
var isFragment = (nodeOrFragment) => {
	return !("type" in nodeOrFragment);
};
var insertContentAt = (position, value, options) => ({ tr, dispatch, editor }) => {
	var _a;
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
			content = createNodeFromContent(value, editor.schema, {
				parseOptions,
				errorOnInvalidContent: (_a = options.errorOnInvalidContent) != null ? _a : editor.options.enableContentCheck
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
		(isFragment(content) ? content : [content]).forEach((node) => {
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
			if (Array.isArray(value)) newContent = value.map((v) => v.text || "").join("");
			else if (value instanceof Fragment) {
				let text = "";
				value.forEach((node) => {
					if (node.text) text += node.text;
				});
				newContent = text;
			} else if (typeof value === "object" && !!value && !!value.text) newContent = value.text;
			else newContent = value;
			tr.insertText(newContent, from, to);
		} else {
			newContent = content;
			const $from = tr.doc.resolve(from);
			const $fromNode = $from.node();
			const fromSelectionAtStart = $from.parentOffset === 0;
			const isTextSelection2 = $fromNode.isText || $fromNode.isTextblock;
			const hasContent = $fromNode.content.size > 0;
			if (fromSelectionAtStart && isTextSelection2 && hasContent && isOnlyBlockContent) from = Math.max(0, from - 1);
			tr.replaceWith(from, to, newContent);
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
var joinUp = () => ({ state, dispatch }) => {
	return joinUp$1(state, dispatch);
};
var joinDown = () => ({ state, dispatch }) => {
	return joinDown$1(state, dispatch);
};
var joinBackward = () => ({ state, dispatch }) => {
	return joinBackward$1(state, dispatch);
};
var joinForward = () => ({ state, dispatch }) => {
	return joinForward$1(state, dispatch);
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
var joinTextblockBackward = () => ({ state, dispatch }) => {
	return joinTextblockBackward$1(state, dispatch);
};
var joinTextblockForward = () => ({ state, dispatch }) => {
	return joinTextblockForward$1(state, dispatch);
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
	editor.captureTransaction(() => {
		view.someProp("handleKeyDown", (f) => f(view, event));
	})?.steps.forEach((step) => {
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
var lift = (typeOrName, attributes = {}) => ({ state, dispatch }) => {
	if (!isNodeActive(state, getNodeType(typeOrName, state.schema), attributes)) return false;
	return lift$1(state, dispatch);
};
var liftEmptyBlock = () => ({ state, dispatch }) => {
	return liftEmptyBlock$1(state, dispatch);
};
var liftListItem = (typeOrName) => ({ state, dispatch }) => {
	return liftListItem$1(getNodeType(typeOrName, state.schema))(state, dispatch);
};
var newlineInCode = () => ({ state, dispatch }) => {
	return newlineInCode$1(state, dispatch);
};
function getSchemaTypeNameByName(name, schema) {
	if (schema.nodes[name]) return "node";
	if (schema.marks[name]) return "mark";
	return null;
}
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
var selectNodeBackward = () => ({ state, dispatch }) => {
	return selectNodeBackward$1(state, dispatch);
};
var selectNodeForward = () => ({ state, dispatch }) => {
	return selectNodeForward$1(state, dispatch);
};
var selectParentNode = () => ({ state, dispatch }) => {
	return selectParentNode$1(state, dispatch);
};
var selectTextblockEnd = () => ({ state, dispatch }) => {
	return selectTextblockEnd$1(state, dispatch);
};
var selectTextblockStart = () => ({ state, dispatch }) => {
	return selectTextblockStart$1(state, dispatch);
};
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
		const document2 = createDocument(content, editor.schema, parseOptions, { errorOnInvalidContent: errorOnInvalidContent != null ? errorOnInvalidContent : editor.options.enableContentCheck });
		if (dispatch) tr.replaceWith(0, doc.content.size, document2).setMeta("preventUpdate", !emitUpdate);
		return true;
	}
	if (dispatch) tr.setMeta("preventUpdate", !emitUpdate);
	return commands.insertContentAt({
		from: 0,
		to: doc.content.size
	}, content, {
		parseOptions,
		errorOnInvalidContent: errorOnInvalidContent != null ? errorOnInvalidContent : editor.options.enableContentCheck
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
function combineTransactionSteps(oldDoc, transactions) {
	const transform = new Transform(oldDoc);
	transactions.forEach((transaction) => {
		transaction.steps.forEach((step) => {
			transform.step(step);
		});
	});
	return transform;
}
function defaultBlockAt(match) {
	for (let i = 0; i < match.edgeCount; i += 1) {
		const { type } = match.edge(i);
		if (type.isTextblock && !type.hasRequiredAttrs()) return type;
	}
	return null;
}
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
function findParentNode(predicate) {
	return (selection) => findParentNodeClosestToPos(selection.$from, predicate);
}
function getExtensionField(extension, field, context) {
	if (extension.config[field] === void 0 && extension.parent) return getExtensionField(extension.parent, field, context);
	if (typeof extension.config[field] === "function") return extension.config[field].bind({
		...context,
		parent: extension.parent ? getExtensionField(extension.parent, field, context) : null
	});
	return extension.config[field];
}
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
			if (typeof (mergedAttr == null ? void 0 : mergedAttr.default) === "function") mergedAttr.default = mergedAttr.default();
			if ((mergedAttr == null ? void 0 : mergedAttr.isRequired) && (mergedAttr == null ? void 0 : mergedAttr.default) === void 0) delete mergedAttr.default;
			extensionAttributes.push({
				type: extension.name,
				name,
				attribute: mergedAttr
			});
		});
	});
	return extensionAttributes;
}
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
function buildAttributeSpec(extensionAttribute) {
	var _a, _b;
	const spec = {};
	if (!((_a = extensionAttribute == null ? void 0 : extensionAttribute.attribute) == null ? void 0 : _a.isRequired) && "default" in ((extensionAttribute == null ? void 0 : extensionAttribute.attribute) || {})) spec.default = extensionAttribute.attribute.default;
	if (((_b = extensionAttribute == null ? void 0 : extensionAttribute.attribute) == null ? void 0 : _b.validate) !== void 0) spec.validate = extensionAttribute.attribute.validate;
	return [extensionAttribute.name, spec];
}
function getSchemaByResolvedExtensions(extensions, editor) {
	var _a;
	const allAttributes = getAttributesFromExtensions(extensions);
	const { nodeExtensions, markExtensions } = splitExtensions(extensions);
	return new Schema({
		topNode: (_a = nodeExtensions.find((extension) => getExtensionField(extension, "topNode"))) == null ? void 0 : _a.name,
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
function findDuplicates(items) {
	const filtered = items.filter((el, index) => items.indexOf(el) !== index);
	return Array.from(new Set(filtered));
}
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
function resolveExtensions(extensions) {
	const resolvedExtensions = sortExtensions(flattenExtensions(extensions));
	const duplicatedNames = findDuplicates(resolvedExtensions.map((extension) => extension.name));
	if (duplicatedNames.length) console.warn(`[tiptap warn]: Duplicate extension names found: [${duplicatedNames.map((item) => `'${item}'`).join(", ")}]. This can lead to issues.`);
	return resolvedExtensions;
}
function getSchema(extensions, editor) {
	return getSchemaByResolvedExtensions(resolveExtensions(extensions), editor);
}
function generateHTML(doc, extensions) {
	const schema = getSchema(extensions);
	return getHTMLFromFragment(Node.fromJSON(schema, doc).content, schema);
}
function generateJSON(html, extensions) {
	const schema = getSchema(extensions);
	const dom = elementFromString(html);
	return DOMParser.fromSchema(schema).parse(dom).toJSON();
}
function getTextBetween(startNode, range, options) {
	const { from, to } = range;
	const { blockSeparator = "\n\n", textSerializers = {} } = options || {};
	let text = "";
	startNode.nodesBetween(from, to, (node, pos, parent, index) => {
		var _a;
		if (node.isBlock && pos > from) text += blockSeparator;
		const textSerializer = textSerializers == null ? void 0 : textSerializers[node.type.name];
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
		if (node.isText) text += (_a = node == null ? void 0 : node.text) == null ? void 0 : _a.slice(Math.max(from, pos) - pos, to - pos);
	});
	return text;
}
function getText(node, options) {
	return getTextBetween(node, {
		from: 0,
		to: node.content.size
	}, options);
}
function getTextSerializersFromSchema(schema) {
	return Object.fromEntries(Object.entries(schema.nodes).filter(([, node]) => node.spec.toText).map(([name, node]) => [name, node.spec.toText]));
}
function generateText(doc, extensions, options) {
	const { blockSeparator = "\n\n", textSerializers = {} } = options || {};
	const schema = getSchema(extensions);
	return getText(Node.fromJSON(schema, doc), {
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
	state.doc.nodesBetween(from, to, (node2) => {
		nodes.push(node2);
	});
	const node = nodes.reverse().find((nodeItem) => nodeItem.type.name === type.name);
	if (!node) return {};
	return { ...node.attrs };
}
function getAttributes(state, typeOrName) {
	const schemaType = getSchemaTypeNameByName(typeof typeOrName === "string" ? typeOrName : typeOrName.name, state.schema);
	if (schemaType === "node") return getNodeAttributes(state, typeOrName);
	if (schemaType === "mark") return getMarkAttributes(state, typeOrName);
	return {};
}
function removeDuplicates(array, by = JSON.stringify) {
	const seen = {};
	return array.filter((item) => {
		const key = by(item);
		return Object.prototype.hasOwnProperty.call(seen, key) ? false : seen[key] = true;
	});
}
function simplifyChangedRanges(changes) {
	const uniqueChanges = removeDuplicates(changes);
	return uniqueChanges.length === 1 ? uniqueChanges : uniqueChanges.filter((change, index) => {
		return !uniqueChanges.filter((_, i) => i !== index).some((otherChange) => {
			return change.oldRange.from >= otherChange.oldRange.from && change.oldRange.to <= otherChange.oldRange.to && change.newRange.from >= otherChange.newRange.from && change.newRange.to <= otherChange.newRange.to;
		});
	});
}
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
		const output2 = { type: mark.type.name };
		if (Object.keys(mark.attrs).length) output2.attrs = { ...mark.attrs };
		return output2;
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
			var _a;
			(_a = output.content) == null || _a.push(getDebugJSON(child, startOffset + offset + increment));
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
		if (!node || (node == null ? void 0 : node.nodeSize) === void 0) return;
		marks.push(...node.marks.map((mark) => ({
			from: pos,
			to: pos + node.nodeSize,
			mark
		})));
	});
	return marks;
}
var getNodeAtPosition = (state, typeOrName, pos, maxDepth = 20) => {
	const $pos = state.doc.resolve(pos);
	let currentDepth = maxDepth;
	let node = null;
	while (currentDepth > 0 && node === null) {
		const currentNode = $pos.node(currentDepth);
		if ((currentNode == null ? void 0 : currentNode.type.name) === typeOrName) node = currentNode;
		else currentDepth -= 1;
	}
	return [node, currentDepth];
};
function getSchemaTypeByName(name, schema) {
	return schema.nodes[name] || schema.marks[name] || null;
}
function getSplittedAttributes(extensionAttributes, typeName, attributes) {
	return Object.fromEntries(Object.entries(attributes).filter(([name]) => {
		const extensionAttribute = extensionAttributes.find((item) => {
			return item.type === typeName && item.name === name;
		});
		if (!extensionAttribute) return false;
		return extensionAttribute.attribute.keepOnSplit;
	}));
}
var getTextContentFromNodes = ($from, maxMatch = 500) => {
	let textBefore = "";
	const sliceEndPos = $from.parentOffset;
	$from.parent.nodesBetween(Math.max(0, sliceEndPos - maxMatch), sliceEndPos, (node, pos, parent, index) => {
		var _a, _b;
		const chunk = ((_b = (_a = node.type.spec).toText) == null ? void 0 : _b.call(_a, {
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
			const range2 = relativeTo - relativeFrom;
			selectionRange += range2;
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
function isNodeEmpty(node, { checkChildren = true, ignoreWhitespace = false } = {}) {
	var _a;
	if (ignoreWhitespace) {
		if (node.type.name === "hardBreak") return true;
		if (node.isText) return !/\S/.test((_a = node.text) != null ? _a : "");
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
var MappablePosition = class _MappablePosition {
	constructor(position) {
		this.position = position;
	}
	/**
	* Creates a MappablePosition from a JSON object.
	*/
	static fromJSON(json) {
		return new _MappablePosition(json.position);
	}
	/**
	* Converts the MappablePosition to a JSON object.
	*/
	toJSON() {
		return { position: this.position };
	}
};
function getUpdatedPosition(position, transaction) {
	const mapResult = transaction.mapping.mapResult(position.position);
	return {
		position: new MappablePosition(mapResult.pos),
		mapResult
	};
}
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
function rewriteUnknownContentInner({ json, validMarks, validNodes, options, rewrittenContent = [] }) {
	if (json.marks && Array.isArray(json.marks)) json.marks = json.marks.filter((mark) => {
		const name = typeof mark === "string" ? mark : mark.type;
		if (validMarks.has(name)) return true;
		rewrittenContent.push({
			original: JSON.parse(JSON.stringify(mark)),
			unsupported: name
		});
		return false;
	});
	if (json.content && Array.isArray(json.content)) json.content = json.content.map((value) => rewriteUnknownContentInner({
		json: value,
		validMarks,
		validNodes,
		options,
		rewrittenContent
	}).json).filter((a) => a !== null && a !== void 0);
	if (json.type && !validNodes.has(json.type)) {
		rewrittenContent.push({
			original: JSON.parse(JSON.stringify(json)),
			unsupported: json.type
		});
		if (json.content && Array.isArray(json.content) && (options == null ? void 0 : options.fallbackToParagraph) !== false) {
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
function rewriteUnknownContent(json, schema, options) {
	return rewriteUnknownContentInner({
		json,
		validNodes: new Set(Object.keys(schema.nodes)),
		validMarks: new Set(Object.keys(schema.marks)),
		options
	});
}
function canSetMark(state, tr, newMarkType) {
	var _a;
	const { selection } = tr;
	let cursor = null;
	if (isTextSelection(selection)) cursor = selection.$cursor;
	if (cursor) {
		const currentMarks = (_a = state.storedMarks) != null ? _a : cursor.marks();
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
var sinkListItem = (typeOrName) => ({ state, dispatch }) => {
	return sinkListItem$1(getNodeType(typeOrName, state.schema))(state, dispatch);
};
function ensureMarks(state, splittableMarks) {
	const marks = state.storedMarks || state.selection.$to.parentOffset && state.selection.$from.marks();
	if (marks) {
		const filteredMarks = marks.filter((mark) => splittableMarks == null ? void 0 : splittableMarks.includes(mark.type.name));
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
				const first2 = tr.mapping.map($from.before());
				const $first = tr.doc.resolve(first2);
				if ($from.node(-1).canReplaceWith($first.index(), $first.index() + 1, deflt)) tr.setNodeMarkup(tr.mapping.map($from.before()), deflt);
			}
		}
		if (keepMarks) ensureMarks(state, editor.extensionManager.splittableMarks);
		tr.scrollIntoView();
	}
	return can;
};
var splitListItem = (typeOrName, overrideAttrs = {}) => ({ tr, state, dispatch, editor }) => {
	var _a;
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
			let wrap = Fragment.empty;
			const depthBefore = $from.index(-1) ? 1 : $from.index(-2) ? 2 : 3;
			for (let d = $from.depth - depthBefore; d >= $from.depth - 3; d -= 1) wrap = Fragment.from($from.node(d).copy(wrap));
			const depthAfter = $from.indexAfter(-1) < $from.node(-2).childCount ? 1 : $from.indexAfter(-2) < $from.node(-3).childCount ? 2 : 3;
			const newNextTypeAttributes2 = {
				...getSplittedAttributes(extensionAttributes, $from.node().type.name, $from.node().attrs),
				...overrideAttrs
			};
			const nextType2 = ((_a = type.contentMatch.defaultType) == null ? void 0 : _a.createAndFill(newNextTypeAttributes2)) || void 0;
			wrap = wrap.append(Fragment.from(type.createAndFill(null, nextType2) || void 0));
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
var joinListBackwards = (tr, listType) => {
	const list = findParentNode((node) => node.type === listType)(tr.selection);
	if (!list) return true;
	const before = tr.doc.resolve(Math.max(0, list.pos - 1)).before(list.depth);
	if (before === void 0) return true;
	const nodeBefore = tr.doc.nodeAt(before);
	if (!(list.node.type === (nodeBefore == null ? void 0 : nodeBefore.type) && canJoin(tr.doc, list.pos))) return true;
	tr.join(list.pos);
	return true;
};
var joinListForwards = (tr, listType) => {
	const list = findParentNode((node) => node.type === listType)(tr.selection);
	if (!list) return true;
	const after = tr.doc.resolve(list.start).after(list.depth);
	if (after === void 0) return true;
	const nodeAfter = tr.doc.nodeAt(after);
	if (!(list.node.type === (nodeAfter == null ? void 0 : nodeAfter.type) && canJoin(tr.doc, after))) return true;
	tr.join(after);
	return true;
};
function createInnerSelectionForWholeDocList(tr) {
	const doc = tr.doc;
	const list = doc.firstChild;
	if (!list) return null;
	const from = 1;
	const to = list.nodeSize - 1;
	return TextSelection.create(doc, from, to);
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
	const currentList = parentList != null ? parentList : allSelectionList;
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
	const isActive2 = isNodeActive(state, type, attributes);
	let attributesToCopy;
	if (state.selection.$anchor.sameParent(state.selection.$head)) attributesToCopy = state.selection.$anchor.parent.attrs;
	if (isActive2) return commands.setNode(toggleType, attributesToCopy);
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
var unsetAllMarks = () => ({ tr, dispatch }) => {
	const { selection } = tr;
	const { empty, ranges } = selection;
	if (empty) return true;
	if (dispatch) ranges.forEach((range) => {
		tr.removeMark(range.$from.pos, range.$to.pos);
	});
	return true;
};
var unsetMark = (typeOrName, options = {}) => ({ tr, state, dispatch }) => {
	var _a;
	const { extendEmptyMarkRange = false } = options;
	const { selection } = tr;
	const type = getMarkType(typeOrName, state.schema);
	const { $from, empty, ranges } = selection;
	if (!dispatch) return true;
	if (empty && extendEmptyMarkRange) {
		let { from, to } = selection;
		const range = getMarkRange($from, type, (_a = $from.marks().find((mark) => mark.type === type)) == null ? void 0 : _a.attrs);
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
var wrapIn = (typeOrName, attributes = {}) => ({ state, dispatch }) => {
	return wrapIn$1(getNodeType(typeOrName, state.schema), attributes)(state, dispatch);
};
var wrapInList = (typeOrName, attributes = {}) => ({ state, dispatch }) => {
	return wrapInList$1(getNodeType(typeOrName, state.schema), attributes)(state, dispatch);
};
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
var InputRule = class {
	constructor(config) {
		var _a;
		this.find = config.find;
		this.handler = config.handler;
		this.undoable = (_a = config.undoable) != null ? _a : true;
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
function run(config) {
	var _a;
	const { editor, from, to, text, rules, plugin } = config;
	const { view } = editor;
	if (view.composing) return false;
	const $from = view.state.doc.resolve(from);
	if ($from.parent.type.spec.code || !!((_a = $from.nodeBefore || $from.nodeAfter) == null ? void 0 : _a.marks.find((mark) => mark.type.spec.code))) return false;
	let matched = false;
	const textBefore = getTextContentFromNodes($from) + text;
	rules.forEach((rule) => {
		if (matched) return;
		const match = inputRuleMatcherHandler(textBefore, rule.find);
		if (!match) return;
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
					else text = getHTMLFromFragment(Fragment.from(text), state.schema);
					const { from } = simulatedInputMeta;
					run({
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
				return run({
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
					if ($cursor) run({
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
				if ($cursor) return run({
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
function getType(value) {
	return Object.prototype.toString.call(value).slice(8, -1);
}
function isPlainObject(value) {
	if (getType(value) !== "Object") return false;
	return value.constructor === Object && Object.getPrototypeOf(value) === Object.prototype;
}
function mergeDeep(target, source) {
	const output = { ...target };
	if (isPlainObject(target) && isPlainObject(source)) Object.keys(source).forEach((key) => {
		if (isPlainObject(source[key]) && isPlainObject(target[key])) output[key] = mergeDeep(target[key], source[key]);
		else output[key] = source[key];
	});
	return output;
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
		return { ...callOrReturn(getExtensionField(this, "addOptions", { name: this.name })) || {} };
	}
	get storage() {
		return { ...callOrReturn(getExtensionField(this, "addStorage", {
			name: this.name,
			options: this.options
		})) || {} };
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
var Mark = class _Mark extends Extendable {
	constructor() {
		super(...arguments);
		this.type = "mark";
	}
	/**
	* Create a new Mark instance
	* @param config - Mark configuration object or a function that returns a configuration object
	*/
	static create(config = {}) {
		return new _Mark(typeof config === "function" ? config() : config);
	}
	static handleExit({ editor, mark }) {
		const { tr } = editor.state;
		const currentPos = editor.state.selection.$from;
		if (currentPos.pos === currentPos.end()) {
			const currentMarks = currentPos.marks();
			if (!!!currentMarks.find((m) => (m == null ? void 0 : m.type.name) === mark.name)) return false;
			const removeMark = currentMarks.find((m) => (m == null ? void 0 : m.type.name) === mark.name);
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
function isNumber(value) {
	return typeof value === "number";
}
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
function run2(config) {
	const { editor, state, from, to, rule, pasteEvent, dropEvent } = config;
	const { commands, chain, can } = new CommandManager({
		editor,
		state
	});
	const handlers = [];
	state.doc.nodesBetween(from, to, (node, pos) => {
		var _a, _b, _c, _d, _e;
		if (((_b = (_a = node.type) == null ? void 0 : _a.spec) == null ? void 0 : _b.code) || !(node.isText || node.isTextblock || node.isInline)) return;
		const contentSize = (_e = (_d = (_c = node.content) == null ? void 0 : _c.size) != null ? _d : node.nodeSize) != null ? _e : 0;
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
	var _a;
	const event = new ClipboardEvent("paste", { clipboardData: new DataTransfer() });
	(_a = event.clipboardData) == null || _a.setData("text/html", text);
	return event;
};
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
		if (!run2({
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
					var _a;
					dragSourceElement = ((_a = view.dom.parentElement) == null ? void 0 : _a.contains(event.target)) ? view.dom.parentElement : null;
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
						if (dragFromOtherEditor == null ? void 0 : dragFromOtherEditor.isEditable) setTimeout(() => {
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
					var _a;
					const html = (_a = event.clipboardData) == null ? void 0 : _a.getData("text/html");
					pasteEvent = event;
					isPastedFromProseMirror = !!(html == null ? void 0 : html.includes("data-pm-slice"));
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
					else text = getHTMLFromFragment(Fragment.from(text), state.schema);
					const { from: from2 } = simulatedPasteMeta;
					const to2 = from2 + text.length;
					const pasteEvt = createClipboardPasteEvent(text);
					return processEvent({
						rule,
						state,
						from: from2,
						to: { b: to2 },
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
		return sortExtensions([...this.extensions].reverse()).flatMap((extension) => {
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
	* Go through all extensions, create extension storages & setup marks
	* & bind editor event listener.
	*/
	setupExtensions() {
		const extensions = this.extensions;
		this.editor.extensionStorage = Object.fromEntries(extensions.map((extension) => [extension.name, extension.storage]));
		extensions.forEach((extension) => {
			var _a;
			const context = {
				name: extension.name,
				options: extension.options,
				storage: this.editor.extensionStorage[extension.name],
				editor: this.editor,
				type: getSchemaTypeByName(extension.name, this.schema)
			};
			if (extension.type === "mark") {
				if ((_a = callOrReturn(getExtensionField(extension, "keepOnSplit", context))) != null ? _a : true) this.splittableMarks.push(extension.name);
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
var extensions_exports = {};
__export(extensions_exports, {
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
var Extension = class _Extension extends Extendable {
	constructor() {
		super(...arguments);
		this.type = "extension";
	}
	/**
	* Create a new Extension instance
	* @param config - Extension configuration object or a function that returns a configuration object
	*/
	static create(config = {}) {
		return new _Extension(typeof config === "function" ? config() : config);
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
				const { ranges } = selection;
				const from = Math.min(...ranges.map((range2) => range2.$from.pos));
				const to = Math.max(...ranges.map((range2) => range2.$to.pos));
				const textSerializers = getTextSerializersFromSchema(schema);
				return getTextBetween(doc, {
					from,
					to
				}, {
					...this.options.blockSeparator !== void 0 ? { blockSeparator: this.options.blockSeparator } : {},
					textSerializers
				});
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
var Delete = Extension.create({
	name: "delete",
	onUpdate({ transaction, appendedTransactions }) {
		var _a, _b, _c;
		const callback = () => {
			var _a2, _b2, _c2, _d;
			if ((_d = (_c2 = (_b2 = (_a2 = this.editor.options.coreExtensionOptions) == null ? void 0 : _a2.delete) == null ? void 0 : _b2.filterTransaction) == null ? void 0 : _c2.call(_b2, transaction)) != null ? _d : transaction.getMeta("y-sync$")) return;
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
				var _a3, _b3;
				if (step instanceof RemoveMarkStep) {
					const newStart = mapping.slice(index).map(step.from, -1);
					const newEnd = mapping.slice(index).map(step.to);
					const oldStart = mapping.invert().map(newStart, -1);
					const oldEnd = mapping.invert().map(newEnd);
					const foundBeforeMark = newStart > 0 ? (_a3 = nextTransaction.doc.nodeAt(newStart - 1)) == null ? void 0 : _a3.marks.some((mark) => mark.eq(step.mark)) : false;
					const foundAfterMark = (_b3 = nextTransaction.doc.nodeAt(newEnd)) == null ? void 0 : _b3.marks.some((mark) => mark.eq(step.mark));
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
		if ((_c = (_b = (_a = this.editor.options.coreExtensionOptions) == null ? void 0 : _a.delete) == null ? void 0 : _b.async) != null ? _c : true) setTimeout(callback, 0);
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
				if (transactions.some((tr2) => tr2.getMeta("composition"))) return;
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
var Tabindex = Extension.create({
	name: "tabindex",
	addProseMirrorPlugins() {
		return [new Plugin({
			key: new PluginKey("tabindex"),
			props: { attributes: () => this.editor.isEditable ? { tabindex: "0" } : {} }
		})];
	}
});
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
var NodePos = class _NodePos {
	constructor(pos, editor, isBlock = false, node = null) {
		this.currentNode = null;
		this.actualDepth = null;
		this.isBlock = isBlock;
		this.resolvedPos = pos;
		this.editor = editor;
		this.currentNode = node;
	}
	get name() {
		return this.node.type.name;
	}
	get node() {
		return this.currentNode || this.resolvedPos.node();
	}
	get element() {
		return this.editor.view.domAtPos(this.pos).node;
	}
	get depth() {
		var _a;
		return (_a = this.actualDepth) != null ? _a : this.resolvedPos.depth;
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
				console.error(`You can\u2019t set content on a block node. Tried to set content on ${this.name} at ${this.pos}`);
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
		return new _NodePos(this.resolvedPos.doc.resolve(parentPos), this.editor);
	}
	get before() {
		let $pos = this.resolvedPos.doc.resolve(this.from - (this.isBlock ? 1 : 2));
		if ($pos.depth !== this.depth) $pos = this.resolvedPos.doc.resolve(this.from - 3);
		return new _NodePos($pos, this.editor);
	}
	get after() {
		let $pos = this.resolvedPos.doc.resolve(this.to + (this.isBlock ? 2 : 1));
		if ($pos.depth !== this.depth) $pos = this.resolvedPos.doc.resolve(this.to + 3);
		return new _NodePos($pos, this.editor);
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
			const childNodePos = new _NodePos($pos, this.editor, isBlock, isBlock || isInline ? node : null);
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
function createStyleTag(style2, nonce, suffix) {
	const tiptapStyleTag = document.querySelector(`style[data-tiptap-style${suffix ? `-${suffix}` : ""}]`);
	if (tiptapStyleTag !== null) return tiptapStyleTag;
	const styleNode = document.createElement("style");
	if (nonce) styleNode.setAttribute("nonce", nonce);
	styleNode.setAttribute(`data-tiptap-style${suffix ? `-${suffix}` : ""}`, "");
	styleNode.innerHTML = style2;
	document.getElementsByTagName("head")[0].appendChild(styleNode);
	return styleNode;
}
var Editor = class extends EventEmitter {
	constructor(options = {}) {
		super();
		this.css = null;
		this.className = "tiptap";
		this.editorView = null;
		this.isFocused = false;
		/**
		* The editor is considered initialized after the `create` event has been emitted.
		*/
		this.isInitialized = false;
		this.extensionStorage = {};
		/**
		* A unique ID for this editor instance.
		*/
		this.instanceId = Math.random().toString(36).slice(2, 9);
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
		/**
		* Returns a set of utilities for working with positions and ranges.
		*/
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
		const selection = resolveFocusPosition(initialDoc, this.options.autofocus);
		this.editorState = EditorState.create({
			doc: initialDoc,
			schema: this.schema,
			selection: selection || void 0
		});
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
			const dom = this.editorView.dom;
			if (dom == null ? void 0 : dom.editor) delete dom.editor;
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
		return this.commandManager.chain();
	}
	/**
	* Check if a command or a command chain can be executed. Without executing it.
	*/
	can() {
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
		var _a, _b;
		const allExtensions = [...this.options.enableCoreExtensions ? [
			Editable,
			ClipboardTextSerializer.configure({ blockSeparator: (_b = (_a = this.options.coreExtensionOptions) == null ? void 0 : _a.clipboardTextSerializer) == null ? void 0 : _b.blockSeparator }),
			Commands,
			FocusEvents,
			Keymap,
			Tabindex,
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
			].includes(extension == null ? void 0 : extension.type);
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
			this.emit("contentError", {
				editor: this,
				error: e,
				disableCollaboration: () => {
					if ("collaboration" in this.storage && typeof this.storage.collaboration === "object" && this.storage.collaboration) this.storage.collaboration.isDisabled = true;
					this.options.extensions = this.options.extensions.filter((extension) => extension.name !== "collaboration");
					this.createExtensionManager();
				}
			});
			doc = createDocument(this.options.content, this.schema, this.options.parseOptions, { errorOnInvalidContent: false });
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
				...editorProps == null ? void 0 : editorProps.attributes
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
				var _a;
				return (_a = this.capturedTransaction) == null ? void 0 : _a.step(step);
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
		const focus2 = mostRecentFocusTr == null ? void 0 : mostRecentFocusTr.getMeta("focus");
		const blur2 = mostRecentFocusTr == null ? void 0 : mostRecentFocusTr.getMeta("blur");
		if (focus2) this.emit("focus", {
			editor: this,
			event: focus2.event,
			transaction: mostRecentFocusTr
		});
		if (blur2) this.emit("blur", {
			editor: this,
			event: blur2.event,
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
		this.emit("destroy");
		this.unmount();
		this.removeAllListeners();
	}
	/**
	* Check if the editor is already destroyed.
	*/
	get isDestroyed() {
		var _a, _b;
		return (_b = (_a = this.editorView) == null ? void 0 : _a.isDestroyed) != null ? _b : true;
	}
	$node(selector, attributes) {
		var _a;
		return ((_a = this.$doc) == null ? void 0 : _a.querySelector(selector, attributes)) || null;
	}
	$nodes(selector, attributes) {
		var _a;
		return ((_a = this.$doc) == null ? void 0 : _a.querySelectorAll(selector, attributes)) || null;
	}
	$pos(pos) {
		return new NodePos(this.state.doc.resolve(pos), this);
	}
	get $doc() {
		return this.$pos(0);
	}
};
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
				const nodeType = config.type.name === "bulletList" || config.type.name === "orderedList" ? "listItem" : "taskList";
				chain().updateAttributes(nodeType, attributes).run();
			}
			const before = tr.doc.resolve(range.from - 1).nodeBefore;
			if (before && before.type === config.type && canJoin(tr.doc, range.from - 1) && (!config.joinPredicate || config.joinPredicate(match, before))) tr.join(range.from - 1);
		},
		undoable: config.undoable
	});
}
function Fragment6(props) {
	return props.children;
}
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
var isTouchEvent = (e) => {
	return "touches" in e;
};
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
		/** Active resize handle directions */
		this.directions = [
			"bottom-left",
			"bottom-right",
			"top-left",
			"top-right"
		];
		/** Minimum allowed dimensions */
		this.minSize = {
			height: 8,
			width: 8
		};
		/** Whether to always preserve aspect ratio */
		this.preserveAspectRatio = false;
		/** CSS class names for elements */
		this.classNames = {
			container: "",
			wrapper: "",
			handle: "",
			resizing: ""
		};
		/** Initial width of the element (for aspect ratio calculation) */
		this.initialWidth = 0;
		/** Initial height of the element (for aspect ratio calculation) */
		this.initialHeight = 0;
		/** Calculated aspect ratio (width / height) */
		this.aspectRatio = 1;
		/** Whether a resize operation is currently active */
		this.isResizing = false;
		/** The handle currently being dragged */
		this.activeHandle = null;
		/** Starting mouse X position when resize began */
		this.startX = 0;
		/** Starting mouse Y position when resize began */
		this.startY = 0;
		/** Element width when resize began */
		this.startWidth = 0;
		/** Element height when resize began */
		this.startHeight = 0;
		/** Whether Shift key is currently pressed (for temporary aspect ratio lock) */
		this.isShiftKeyPressed = false;
		/** Last known editable state of the editor */
		this.lastEditableState = void 0;
		/** Map of handle elements by direction */
		this.handleMap = /* @__PURE__ */ new Map();
		/**
		* Handles mouse movement during an active resize.
		*
		* Calculates the delta from the starting position, computes new dimensions
		* based on the active handle direction, applies constraints and aspect ratio,
		* then updates the element's style and calls the onResize callback.
		*
		* @param event - The mouse move event
		*/
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
		/**
		* Completes the resize operation when the mouse button is released.
		*
		* Captures final dimensions, calls the onCommit callback to persist changes,
		* removes the resizing state and class, and cleans up document-level listeners.
		*/
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
		/**
		* Tracks Shift key state to enable temporary aspect ratio locking.
		*
		* When Shift is pressed during resize, aspect ratio is preserved even if
		* preserveAspectRatio is false.
		*
		* @param event - The keyboard event
		*/
		this.handleKeyDown = (event) => {
			if (event.key === "Shift") this.isShiftKeyPressed = true;
		};
		/**
		* Tracks Shift key release to disable temporary aspect ratio locking.
		*
		* @param event - The keyboard event
		*/
		this.handleKeyUp = (event) => {
			if (event.key === "Shift") this.isShiftKeyPressed = false;
		};
		var _a, _b, _c, _d, _e, _f;
		this.node = options.node;
		this.editor = options.editor;
		this.element = options.element;
		this.contentElement = options.contentElement;
		this.getPos = options.getPos;
		this.onResize = options.onResize;
		this.onCommit = options.onCommit;
		this.onUpdate = options.onUpdate;
		if ((_a = options.options) == null ? void 0 : _a.min) this.minSize = {
			...this.minSize,
			...options.options.min
		};
		if ((_b = options.options) == null ? void 0 : _b.max) this.maxSize = options.options.max;
		if ((_c = options == null ? void 0 : options.options) == null ? void 0 : _c.directions) this.directions = options.options.directions;
		if ((_d = options.options) == null ? void 0 : _d.preserveAspectRatio) this.preserveAspectRatio = options.options.preserveAspectRatio;
		if ((_e = options.options) == null ? void 0 : _e.className) this.classNames = {
			container: options.options.className.container || "",
			wrapper: options.options.className.wrapper || "",
			handle: options.options.className.handle || "",
			resizing: options.options.className.resizing || ""
		};
		if ((_f = options.options) == null ? void 0 : _f.createCustomHandle) this.createCustomHandle = options.options.createCustomHandle;
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
		var _a;
		return (_a = this.contentElement) != null ? _a : null;
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
		var _a, _b, _c, _d;
		if (!preserveAspectRatio) {
			let constrainedWidth2 = Math.max(this.minSize.width, width);
			let constrainedHeight2 = Math.max(this.minSize.height, height);
			if ((_a = this.maxSize) == null ? void 0 : _a.width) constrainedWidth2 = Math.min(this.maxSize.width, constrainedWidth2);
			if ((_b = this.maxSize) == null ? void 0 : _b.height) constrainedHeight2 = Math.min(this.maxSize.height, constrainedHeight2);
			return {
				width: constrainedWidth2,
				height: constrainedHeight2
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
		if (((_c = this.maxSize) == null ? void 0 : _c.width) && constrainedWidth > this.maxSize.width) {
			constrainedWidth = this.maxSize.width;
			constrainedHeight = constrainedWidth / this.aspectRatio;
		}
		if (((_d = this.maxSize) == null ? void 0 : _d.height) && constrainedHeight > this.maxSize.height) {
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
var ResizableNodeview = ResizableNodeView;
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
function escapeForRegEx(string) {
	return string.replace(/[-/\\^$*+?.()|[\]{}]/g, "\\$&");
}
function decodeHtmlEntities(text) {
	return text.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&amp;/g, "&");
}
function encodeHtmlEntities(text) {
	return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
function isFirefox() {
	return typeof navigator !== "undefined" ? /Firefox/.test(navigator.userAgent) : false;
}
function isString(value) {
	return typeof value === "string";
}
var markdown_exports = {};
__export(markdown_exports, {
	createAtomBlockMarkdownSpec: () => createAtomBlockMarkdownSpec,
	createBlockMarkdownSpec: () => createBlockMarkdownSpec,
	createInlineMarkdownSpec: () => createInlineMarkdownSpec,
	parseAttributes: () => parseAttributes,
	parseIndentedBlocks: () => parseIndentedBlocks,
	renderNestedMarkdownContent: () => renderNestedMarkdownContent,
	serializeAttributes: () => serializeAttributes
});
function parseAttributes(attrString) {
	if (!(attrString == null ? void 0 : attrString.trim())) return {};
	const attributes = {};
	const quotedStrings = [];
	const tempString = attrString.replace(/["']([^"']*)["']/g, (match) => {
		quotedStrings.push(match);
		return `__QUOTED_${quotedStrings.length - 1}__`;
	});
	const classMatches = tempString.match(/(?:^|\s)\.([a-zA-Z][\w-]*)/g);
	if (classMatches) attributes.class = classMatches.map((match) => match.trim().slice(1)).join(" ");
	const idMatch = tempString.match(/(?:^|\s)#([a-zA-Z][\w-]*)/);
	if (idMatch) attributes.id = idMatch[1];
	Array.from(tempString.matchAll(/([a-zA-Z][\w-]*)\s*=\s*(__QUOTED_\d+__)/g)).forEach(([, key, quotedRef]) => {
		var _a;
		const quotedValue = quotedStrings[parseInt(((_a = quotedRef.match(/__QUOTED_(\d+)__/)) == null ? void 0 : _a[1]) || "0", 10)];
		if (quotedValue) attributes[key] = quotedValue.slice(1, -1);
	});
	const cleanString = tempString.replace(/(?:^|\s)\.([a-zA-Z][\w-]*)/g, "").replace(/(?:^|\s)#([a-zA-Z][\w-]*)/g, "").replace(/([a-zA-Z][\w-]*)\s*=\s*__QUOTED_\d+__/g, "").trim();
	if (cleanString) cleanString.split(/\s+/).filter(Boolean).forEach((attr) => {
		if (attr.match(/^[a-zA-Z][\w-]*$/)) attributes[attr] = true;
	});
	return attributes;
}
function serializeAttributes(attributes) {
	if (!attributes || Object.keys(attributes).length === 0) return "";
	const parts = [];
	if (attributes.class) String(attributes.class).split(/\s+/).filter(Boolean).forEach((cls) => parts.push(`.${cls}`));
	if (attributes.id) parts.push(`#${attributes.id}`);
	Object.entries(attributes).forEach(([key, value]) => {
		if (key === "class" || key === "id") return;
		if (value === true) parts.push(key);
		else if (value !== false && value != null) parts.push(`${key}="${String(value)}"`);
	});
	return parts.join(" ");
}
function createAtomBlockMarkdownSpec(options) {
	const { nodeName, name: markdownName, parseAttributes: parseAttributes2 = parseAttributes, serializeAttributes: serializeAttributes2 = serializeAttributes, defaultAttributes = {}, requiredAttributes = [], allowedAttributes } = options;
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
		parseMarkdown: (token, h2) => {
			const attrs = {
				...defaultAttributes,
				...token.attributes
			};
			return h2.createNode(nodeName, attrs, []);
		},
		markdownTokenizer: {
			name: nodeName,
			level: "block",
			start(src) {
				var _a;
				const regex = new RegExp(`^:::${blockName}(?:\\s|$)`, "m");
				const index = (_a = src.match(regex)) == null ? void 0 : _a.index;
				return index !== void 0 ? index : -1;
			},
			tokenize(src, _tokens, _lexer) {
				const regex = new RegExp(`^:::${blockName}(?:\\s+\\{([^}]*)\\})?\\s*:::(?:\\n|$)`);
				const match = src.match(regex);
				if (!match) return;
				const attributes = parseAttributes2(match[1] || "");
				if (requiredAttributes.find((required) => !(required in attributes))) return;
				return {
					type: nodeName,
					raw: match[0],
					attributes
				};
			}
		},
		renderMarkdown: (node) => {
			const attrs = serializeAttributes2(filterAttributes(node.attrs || {}));
			return `:::${blockName}${attrs ? ` {${attrs}}` : ""} :::`;
		}
	};
}
function createBlockMarkdownSpec(options) {
	const { nodeName, name: markdownName, getContent, parseAttributes: parseAttributes2 = parseAttributes, serializeAttributes: serializeAttributes2 = serializeAttributes, defaultAttributes = {}, content = "block", allowedAttributes } = options;
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
		parseMarkdown: (token, h2) => {
			let nodeContent;
			if (getContent) {
				const contentResult = getContent(token);
				nodeContent = typeof contentResult === "string" ? [{
					type: "text",
					text: contentResult
				}] : contentResult;
			} else if (content === "block") nodeContent = h2.parseChildren(token.tokens || []);
			else nodeContent = h2.parseInline(token.tokens || []);
			const attrs = {
				...defaultAttributes,
				...token.attributes
			};
			return h2.createNode(nodeName, attrs, nodeContent);
		},
		markdownTokenizer: {
			name: nodeName,
			level: "block",
			start(src) {
				var _a;
				const regex = new RegExp(`^:::${blockName}`, "m");
				const index = (_a = src.match(regex)) == null ? void 0 : _a.index;
				return index !== void 0 ? index : -1;
			},
			tokenize(src, _tokens, lexer) {
				var _a;
				const openingRegex = new RegExp(`^:::${blockName}(?:\\s+\\{([^}]*)\\})?\\s*\\n`);
				const openingMatch = src.match(openingRegex);
				if (!openingMatch) return;
				const [openingTag, attrString = ""] = openingMatch;
				const attributes = parseAttributes2(attrString);
				let level = 1;
				const position = openingTag.length;
				let matchedContent = "";
				const blockPattern = /^:::([\w-]*)(\s.*)?/gm;
				const remaining = src.slice(position);
				blockPattern.lastIndex = 0;
				for (;;) {
					const match = blockPattern.exec(remaining);
					if (match === null) break;
					const matchPos = match.index;
					const blockType = match[1];
					if ((_a = match[2]) == null ? void 0 : _a.endsWith(":::")) continue;
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
		renderMarkdown: (node, h2) => {
			const attrs = serializeAttributes2(filterAttributes(node.attrs || {}));
			return `:::${blockName}${attrs ? ` {${attrs}}` : ""}

${h2.renderChildren(node.content || [], "\n\n")}

:::`;
		}
	};
}
function parseShortcodeAttributes(attrString) {
	if (!attrString.trim()) return {};
	const attributes = {};
	const regex = /(\w+)=(?:"([^"]*)"|'([^']*)')/g;
	let match = regex.exec(attrString);
	while (match !== null) {
		const [, key, doubleQuoted, singleQuoted] = match;
		attributes[key] = doubleQuoted || singleQuoted;
		match = regex.exec(attrString);
	}
	return attributes;
}
function serializeShortcodeAttributes(attrs) {
	return Object.entries(attrs).filter(([, value]) => value !== void 0 && value !== null).map(([key, value]) => `${key}="${value}"`).join(" ");
}
function createInlineMarkdownSpec(options) {
	const { nodeName, name: shortcodeName, getContent, parseAttributes: parseAttributes2 = parseShortcodeAttributes, serializeAttributes: serializeAttributes2 = serializeShortcodeAttributes, defaultAttributes = {}, selfClosing = false, allowedAttributes } = options;
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
		parseMarkdown: (token, h2) => {
			const attrs = {
				...defaultAttributes,
				...token.attributes
			};
			if (selfClosing) return h2.createNode(nodeName, attrs);
			const content = getContent ? getContent(token) : token.content || "";
			if (content) return h2.createNode(nodeName, attrs, [h2.createTextNode(content)]);
			return h2.createNode(nodeName, attrs, []);
		},
		markdownTokenizer: {
			name: nodeName,
			level: "inline",
			start(src) {
				const startPattern = selfClosing ? new RegExp(`\\[${escapedShortcode}\\s*[^\\]]*\\]`) : new RegExp(`\\[${escapedShortcode}\\s*[^\\]]*\\][\\s\\S]*?\\[\\/${escapedShortcode}\\]`);
				const match = src.match(startPattern);
				const index = match == null ? void 0 : match.index;
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
				const attributes = parseAttributes2(attrString.trim());
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
			const attrs = serializeAttributes2(filterAttributes(node.attrs || {}));
			const attrString = attrs ? ` ${attrs}` : "";
			if (selfClosing) return `[${shortcode}${attrString}]`;
			return `[${shortcode}${attrString}]${content}[/${shortcode}]`;
		}
	};
}
function parseIndentedBlocks(src, config, lexer) {
	var _a, _b, _c, _d;
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
			totalRaw = `${totalRaw}${currentLine}
`;
			continue;
		} else return;
		const itemData = config.extractItemData(itemMatch);
		const { indentLevel, mainContent } = itemData;
		totalRaw = `${totalRaw}${currentLine}
`;
		const itemContent = [mainContent];
		i += 1;
		while (i < lines.length) {
			const nextLine = lines[i];
			if (nextLine.trim() === "") {
				const nextNonEmptyIndex = lines.slice(i + 1).findIndex((l) => l.trim() !== "");
				if (nextNonEmptyIndex === -1) break;
				if ((((_b = (_a = lines[i + 1 + nextNonEmptyIndex].match(/^(\s*)/)) == null ? void 0 : _a[1]) == null ? void 0 : _b.length) || 0) > indentLevel) {
					itemContent.push(nextLine);
					totalRaw = `${totalRaw}${nextLine}
`;
					i += 1;
					continue;
				} else break;
			}
			if ((((_d = (_c = nextLine.match(/^(\s*)/)) == null ? void 0 : _c[1]) == null ? void 0 : _d.length) || 0) > indentLevel) {
				itemContent.push(nextLine);
				totalRaw = `${totalRaw}${nextLine}
`;
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
function renderNestedMarkdownContent(node, h2, prefixOrGenerator, ctx) {
	if (!node || !Array.isArray(node.content)) return "";
	const prefix = typeof prefixOrGenerator === "function" ? prefixOrGenerator(ctx) : prefixOrGenerator;
	const [content, ...children] = node.content;
	let output = `${prefix}${h2.renderChildren([content])}`;
	if (children && children.length > 0) children.forEach((child, index) => {
		var _a, _b;
		const childContent = (_b = (_a = h2.renderChild) == null ? void 0 : _a.call(h2, child, index + 1)) != null ? _b : h2.renderChildren([child]);
		if (childContent !== void 0 && childContent !== null) {
			const indentedChild = childContent.split("\n").map((line) => line ? h2.indent(line) : h2.indent("")).join("\n");
			output += child.type === "paragraph" ? `

${indentedChild}` : `
${indentedChild}`;
		}
	});
	return output;
}
var positionUpdateRegistries = /* @__PURE__ */ new WeakMap();
function schedulePositionCheck(editor, callback) {
	let registry = positionUpdateRegistries.get(editor);
	if (!registry) {
		const newRegistry = {
			callbacks: /* @__PURE__ */ new Set(),
			rafId: null,
			handler: () => {
				if (newRegistry.rafId !== null) cancelAnimationFrame(newRegistry.rafId);
				newRegistry.rafId = requestAnimationFrame(() => {
					newRegistry.rafId = null;
					newRegistry.callbacks.forEach((cb) => cb());
				});
			}
		};
		positionUpdateRegistries.set(editor, newRegistry);
		editor.on("update", newRegistry.handler);
		registry = newRegistry;
	}
	registry.callbacks.add(callback);
}
function cancelPositionCheck(editor, callback) {
	const registry = positionUpdateRegistries.get(editor);
	if (!registry) return;
	registry.callbacks.delete(callback);
	if (registry.callbacks.size === 0) {
		if (registry.rafId !== null) cancelAnimationFrame(registry.rafId);
		editor.off("update", registry.handler);
		positionUpdateRegistries.delete(editor);
	}
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
		if (this.dom.contains(mutation.target) && mutation.type === "childList" && (isiOS() || isAndroid()) && this.editor.isFocused) {
			if ([...Array.from(mutation.addedNodes), ...Array.from(mutation.removedNodes)].every((node) => node.isContentEditable)) return false;
		}
		if (this.contentDOM === mutation.target && mutation.type === "attributes") return true;
		if (this.contentDOM.contains(mutation.target)) return false;
		return true;
	}
};
var Node3 = class _Node extends Extendable {
	constructor() {
		super(...arguments);
		this.type = "node";
	}
	/**
	* Create a new Node instance
	* @param config - Node configuration object or a function that returns a configuration object
	*/
	static create(config = {}) {
		return new _Node(typeof config === "function" ? config() : config);
	}
	configure(options) {
		return super.configure(options);
	}
	extend(extendedConfig) {
		const resolvedConfig = typeof extendedConfig === "function" ? extendedConfig() : extendedConfig;
		return super.extend(resolvedConfig);
	}
};
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
		this.getPos = props.getPos;
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
		var _a, _b, _c, _d, _e, _f, _g;
		const { view } = this.editor;
		const target = event.target;
		const dragHandle = target.nodeType === 3 ? (_a = target.parentElement) == null ? void 0 : _a.closest("[data-drag-handle]") : target.closest("[data-drag-handle]");
		if (!this.dom || ((_b = this.contentDOM) == null ? void 0 : _b.contains(target)) || !dragHandle) return;
		let x = 0;
		let y = 0;
		if (this.dom !== dragHandle) {
			const domBox = this.dom.getBoundingClientRect();
			const handleBox = dragHandle.getBoundingClientRect();
			const offsetX = (_d = event.offsetX) != null ? _d : (_c = event.nativeEvent) == null ? void 0 : _c.offsetX;
			const offsetY = (_f = event.offsetY) != null ? _f : (_e = event.nativeEvent) == null ? void 0 : _e.offsetY;
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
			dragImageWrapper = document.createElement("div");
			dragImageWrapper.style.position = "absolute";
			dragImageWrapper.style.top = "-9999px";
			dragImageWrapper.style.left = "-9999px";
			dragImageWrapper.style.pointerEvents = "none";
			dragImageWrapper.appendChild(clonedNode);
			document.body.appendChild(dragImageWrapper);
			(_g = event.dataTransfer) == null || _g.setDragImage(clonedNode, x, y);
		} finally {
			if (dragImageWrapper) setTimeout(() => {
				try {
					dragImageWrapper?.remove();
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
		var _a;
		if (!this.dom) return false;
		if (typeof this.options.stopEvent === "function") return this.options.stopEvent({ event });
		const target = event.target;
		if (!(this.dom.contains(target) && !((_a = this.contentDOM) == null ? void 0 : _a.contains(target)))) return false;
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
		if (this.dom.contains(mutation.target) && mutation.type === "childList" && (isiOS() || isAndroid()) && this.editor.isFocused) {
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
export { getHTMLFromFragment as $, pasteRulesPlugin as $t, decodeHtmlEntities as A, isMacOS as At, findDuplicates as B, isTextSelection as Bt, createBlockMarkdownSpec as C, isAtEndOfNode as Ct, createMappablePosition as D, isFirefox as Dt, createInlineMarkdownSpec as E, isExtensionRulesEnabled as Et, encodeHtmlEntities as F, isNumber as Ft, generateHTML as G, mergeAttributes as Gt, findParentNodeClosestToPos as H, markInputRule as Ht, escapeForRegEx as I, isPlainObject as It, getAttributes as J, nodeInputRule as Jt, generateJSON as K, mergeDeep as Kt, extensions_exports as L, isRegExp as Lt, deleteProps as M, isNodeActive as Mt, dist_exports as N, isNodeEmpty as Nt, createNodeFromContent as O, isFunction as Ot, elementFromString as P, isNodeSelection as Pt, getExtensionField as Q, parseIndentedBlocks as Qt, findChildren as R, isSafari as Rt, createAtomBlockMarkdownSpec as S, isAndroid as St, createDocument as T, isEmptyObject as Tt, flattenExtensions as U, markPasteRule as Ut, findParentNode as V, isiOS as Vt, fromString as W, markdown_exports as Wt, getChangedRanges as X, objectIncludes as Xt, getAttributesFromExtensions as Y, nodePasteRule as Yt, getDebugJSON as Z, parseAttributes as Zt, callOrReturn as _, getUpdatedPosition as _t, Fragment6 as a, rewriteUnknownContent as an, getNodeAttributes as at, combineTransactionSteps as b, inputRulesPlugin as bt, Mark as c, serializeAttributes as cn, getSchema as ct, NodePos as d, textInputRule as dn, getSchemaTypeNameByName as dt, posToDOMRect as en, getMarkAttributes as et, NodeView as f, textPasteRule as fn, getSplittedAttributes as ft, Tracker as g, keydownHandler as gn, getTextSerializersFromSchema as gt, ResizableNodeview as h, wrappingInputRule as hn, getTextContentFromNodes as ht, Extension as i, resolveFocusPosition as in, getNodeAtPosition as it, defaultBlockAt as j, isMarkActive as jt, createStyleTag as k, isList as kt, MarkView as l, sortExtensions as ln, getSchemaByResolvedExtensions as lt, ResizableNodeView as m, updateMarkViewAttributes as mn, getTextBetween as mt, Editor as n, renderNestedMarkdownContent as nn, getMarkType as nt, InputRule as o, schedulePositionCheck as on, getNodeType as ot, PasteRule as p, textblockTypeInputRule as pn, getText as pt, generateText as q, minMax as qt, Extendable as r, resolveExtensions as rn, getMarksBetween as rt, MappablePosition as s, selectionToInsertionEnd as sn, getRenderedAttributes as st, CommandManager as t, removeDuplicates as tn, getMarkRange as tt, Node3 as u, splitExtensions as un, getSchemaTypeByName as ut, canInsertNode as v, h as vt, createChainableState as w, isAtStartOfNode as wt, commands_exports as x, isActive as xt, cancelPositionCheck as y, injectExtensionAttributesToParseRule as yt, findChildrenInRange as z, isString as zt };
