import { n as __exportAll } from "./chunk-B-1-B7_t.js";
import { a as Mark, i as Fragment, n as DOMParser, p as Slice, r as DOMSerializer } from "./dist-AY222wij.js";
import { _ as dropPoint, c as TextSelection, o as Selection, r as NodeSelection, t as AllSelection } from "./dist-QivoNauO.js";
//#region node_modules/prosemirror-view/dist/index.js
var domIndex = function(node) {
	for (var index = 0;; index++) {
		node = node.previousSibling;
		if (!node) return index;
	}
};
var parentNode = function(node) {
	let parent = node.assignedSlot || node.parentNode;
	return parent && parent.nodeType == 11 ? parent.host : parent;
};
var reusedRange = null;
var textRange = function(node, from, to) {
	let range = reusedRange || (reusedRange = document.createRange());
	range.setEnd(node, to == null ? node.nodeValue.length : to);
	range.setStart(node, from || 0);
	return range;
};
var clearReusedRange = function() {
	reusedRange = null;
};
var isEquivalentPosition = function(node, off, targetNode, targetOff) {
	return targetNode && (scanFor(node, off, targetNode, targetOff, -1) || scanFor(node, off, targetNode, targetOff, 1));
};
var atomElements = /^(img|br|input|textarea|hr)$/i;
function scanFor(node, off, targetNode, targetOff, dir) {
	var _a;
	for (;;) {
		if (node == targetNode && off == targetOff) return true;
		if (off == (dir < 0 ? 0 : nodeSize(node))) {
			let parent = node.parentNode;
			if (!parent || parent.nodeType != 1 || hasBlockDesc(node) || atomElements.test(node.nodeName) || node.contentEditable == "false") return false;
			off = domIndex(node) + (dir < 0 ? 0 : 1);
			node = parent;
		} else if (node.nodeType == 1) {
			let child = node.childNodes[off + (dir < 0 ? -1 : 0)];
			if (child.nodeType == 1 && child.contentEditable == "false") if ((_a = child.pmViewDesc) === null || _a === void 0 ? void 0 : _a.ignoreForSelection) off += dir;
			else return false;
			else {
				node = child;
				off = dir < 0 ? nodeSize(node) : 0;
			}
		} else return false;
	}
}
function nodeSize(node) {
	return node.nodeType == 3 ? node.nodeValue.length : node.childNodes.length;
}
function textNodeBefore$1(node, offset) {
	for (;;) {
		if (node.nodeType == 3 && offset) return node;
		if (node.nodeType == 1 && offset > 0) {
			if (node.contentEditable == "false") return null;
			node = node.childNodes[offset - 1];
			offset = nodeSize(node);
		} else if (node.parentNode && !hasBlockDesc(node)) {
			offset = domIndex(node);
			node = node.parentNode;
		} else return null;
	}
}
function textNodeAfter$1(node, offset) {
	for (;;) {
		if (node.nodeType == 3 && offset < node.nodeValue.length) return node;
		if (node.nodeType == 1 && offset < node.childNodes.length) {
			if (node.contentEditable == "false") return null;
			node = node.childNodes[offset];
			offset = 0;
		} else if (node.parentNode && !hasBlockDesc(node)) {
			offset = domIndex(node) + 1;
			node = node.parentNode;
		} else return null;
	}
}
function isOnEdge(node, offset, parent) {
	for (let atStart = offset == 0, atEnd = offset == nodeSize(node); atStart || atEnd;) {
		if (node == parent) return true;
		let index = domIndex(node);
		node = node.parentNode;
		if (!node) return false;
		atStart = atStart && index == 0;
		atEnd = atEnd && index == nodeSize(node);
	}
}
function hasBlockDesc(dom) {
	let desc;
	for (let cur = dom; cur; cur = cur.parentNode) if (desc = cur.pmViewDesc) break;
	return desc && desc.node && desc.node.isBlock && (desc.dom == dom || desc.contentDOM == dom);
}
var selectionCollapsed = function(domSel) {
	return domSel.focusNode && isEquivalentPosition(domSel.focusNode, domSel.focusOffset, domSel.anchorNode, domSel.anchorOffset);
};
function keyEvent(keyCode, key) {
	let event = document.createEvent("Event");
	event.initEvent("keydown", true, true);
	event.keyCode = keyCode;
	event.key = event.code = key;
	return event;
}
function deepActiveElement(doc) {
	let elt = doc.activeElement;
	while (elt && elt.shadowRoot) elt = elt.shadowRoot.activeElement;
	return elt;
}
function caretFromPoint(doc, x, y) {
	if (doc.caretPositionFromPoint) try {
		let pos = doc.caretPositionFromPoint(x, y);
		if (pos) return {
			node: pos.offsetNode,
			offset: Math.min(nodeSize(pos.offsetNode), pos.offset)
		};
	} catch (_) {}
	if (doc.caretRangeFromPoint) {
		let range = doc.caretRangeFromPoint(x, y);
		if (range) return {
			node: range.startContainer,
			offset: Math.min(nodeSize(range.startContainer), range.startOffset)
		};
	}
}
var nav = typeof navigator != "undefined" ? navigator : null;
var doc = typeof document != "undefined" ? document : null;
var agent = nav && nav.userAgent || "";
var ie_edge = /Edge\/(\d+)/.exec(agent);
var ie_upto10 = /MSIE \d/.exec(agent);
var ie_11up = /Trident\/(?:[7-9]|\d{2,})\..*rv:(\d+)/.exec(agent);
var ie = !!(ie_upto10 || ie_11up || ie_edge);
var ie_version = ie_upto10 ? document.documentMode : ie_11up ? +ie_11up[1] : ie_edge ? +ie_edge[1] : 0;
var gecko = !ie && /gecko\/(\d+)/i.test(agent);
gecko && +(/Firefox\/(\d+)/.exec(agent) || [0, 0])[1];
var _chrome = !ie && /Chrome\/(\d+)/.exec(agent);
var chrome = !!_chrome;
var chrome_version = _chrome ? +_chrome[1] : 0;
var safari = !ie && !!nav && /Apple Computer/.test(nav.vendor);
var ios = safari && (/Mobile\/\w+/.test(agent) || !!nav && nav.maxTouchPoints > 2);
var mac = ios || (nav ? /Mac/.test(nav.platform) : false);
var windows = nav ? /Win/.test(nav.platform) : false;
var android = /Android \d/.test(agent);
var webkit = !!doc && "webkitFontSmoothing" in doc.documentElement.style;
var webkit_version = webkit ? +(/\bAppleWebKit\/(\d+)/.exec(navigator.userAgent) || [0, 0])[1] : 0;
function windowRect(doc) {
	let vp = doc.defaultView && doc.defaultView.visualViewport;
	if (vp) return {
		left: 0,
		right: vp.width,
		top: 0,
		bottom: vp.height
	};
	return {
		left: 0,
		right: doc.documentElement.clientWidth,
		top: 0,
		bottom: doc.documentElement.clientHeight
	};
}
function getSide(value, side) {
	return typeof value == "number" ? value : value[side];
}
function clientRect(node) {
	let rect = node.getBoundingClientRect();
	let scaleX = rect.width / node.offsetWidth || 1;
	let scaleY = rect.height / node.offsetHeight || 1;
	return {
		left: rect.left,
		right: rect.left + node.clientWidth * scaleX,
		top: rect.top,
		bottom: rect.top + node.clientHeight * scaleY
	};
}
function scrollRectIntoView(view, rect, startDOM) {
	let scrollThreshold = view.someProp("scrollThreshold") || 0, scrollMargin = view.someProp("scrollMargin") || 5;
	let doc = view.dom.ownerDocument;
	for (let parent = startDOM || view.dom;;) {
		if (!parent) break;
		if (parent.nodeType != 1) {
			parent = parentNode(parent);
			continue;
		}
		let elt = parent;
		let atTop = elt == doc.body;
		let bounding = atTop ? windowRect(doc) : clientRect(elt);
		let moveX = 0, moveY = 0;
		if (rect.top < bounding.top + getSide(scrollThreshold, "top")) moveY = -(bounding.top - rect.top + getSide(scrollMargin, "top"));
		else if (rect.bottom > bounding.bottom - getSide(scrollThreshold, "bottom")) moveY = rect.bottom - rect.top > bounding.bottom - bounding.top ? rect.top + getSide(scrollMargin, "top") - bounding.top : rect.bottom - bounding.bottom + getSide(scrollMargin, "bottom");
		if (rect.left < bounding.left + getSide(scrollThreshold, "left")) moveX = -(bounding.left - rect.left + getSide(scrollMargin, "left"));
		else if (rect.right > bounding.right - getSide(scrollThreshold, "right")) moveX = rect.right - bounding.right + getSide(scrollMargin, "right");
		if (moveX || moveY) if (atTop) doc.defaultView.scrollBy(moveX, moveY);
		else {
			let startX = elt.scrollLeft, startY = elt.scrollTop;
			if (moveY) elt.scrollTop += moveY;
			if (moveX) elt.scrollLeft += moveX;
			let dX = elt.scrollLeft - startX, dY = elt.scrollTop - startY;
			rect = {
				left: rect.left - dX,
				top: rect.top - dY,
				right: rect.right - dX,
				bottom: rect.bottom - dY
			};
		}
		let pos = atTop ? "fixed" : getComputedStyle(parent).position;
		if (/^(fixed|sticky)$/.test(pos)) break;
		parent = pos == "absolute" ? parent.offsetParent : parentNode(parent);
	}
}
function storeScrollPos(view) {
	let rect = view.dom.getBoundingClientRect(), startY = Math.max(0, rect.top);
	let refDOM, refTop;
	for (let x = (rect.left + rect.right) / 2, y = startY + 1; y < Math.min(innerHeight, rect.bottom); y += 5) {
		let dom = view.root.elementFromPoint(x, y);
		if (!dom || dom == view.dom || !view.dom.contains(dom)) continue;
		let localRect = dom.getBoundingClientRect();
		if (localRect.top >= startY - 20) {
			refDOM = dom;
			refTop = localRect.top;
			break;
		}
	}
	return {
		refDOM,
		refTop,
		stack: scrollStack(view.dom)
	};
}
function scrollStack(dom) {
	let stack = [], doc = dom.ownerDocument;
	for (let cur = dom; cur; cur = parentNode(cur)) {
		stack.push({
			dom: cur,
			top: cur.scrollTop,
			left: cur.scrollLeft
		});
		if (dom == doc) break;
	}
	return stack;
}
function resetScrollPos({ refDOM, refTop, stack }) {
	let newRefTop = refDOM ? refDOM.getBoundingClientRect().top : 0;
	restoreScrollStack(stack, newRefTop == 0 ? 0 : newRefTop - refTop);
}
function restoreScrollStack(stack, dTop) {
	for (let i = 0; i < stack.length; i++) {
		let { dom, top, left } = stack[i];
		if (dom.scrollTop != top + dTop) dom.scrollTop = top + dTop;
		if (dom.scrollLeft != left) dom.scrollLeft = left;
	}
}
var preventScrollSupported = null;
function focusPreventScroll(dom) {
	if (dom.setActive) return dom.setActive();
	if (preventScrollSupported) return dom.focus(preventScrollSupported);
	let stored = scrollStack(dom);
	dom.focus(preventScrollSupported == null ? { get preventScroll() {
		preventScrollSupported = { preventScroll: true };
		return true;
	} } : void 0);
	if (!preventScrollSupported) {
		preventScrollSupported = false;
		restoreScrollStack(stored, 0);
	}
}
function findOffsetInNode(node, coords) {
	let closest, dxClosest = 2e8, coordsClosest, offset = 0;
	let rowBot = coords.top, rowTop = coords.top;
	let firstBelow, coordsBelow;
	for (let child = node.firstChild, childIndex = 0; child; child = child.nextSibling, childIndex++) {
		let rects;
		if (child.nodeType == 1) rects = child.getClientRects();
		else if (child.nodeType == 3) rects = textRange(child).getClientRects();
		else continue;
		for (let i = 0; i < rects.length; i++) {
			let rect = rects[i];
			if (rect.top <= rowBot && rect.bottom >= rowTop) {
				rowBot = Math.max(rect.bottom, rowBot);
				rowTop = Math.min(rect.top, rowTop);
				let dx = rect.left > coords.left ? rect.left - coords.left : rect.right < coords.left ? coords.left - rect.right : 0;
				if (dx < dxClosest) {
					closest = child;
					dxClosest = dx;
					coordsClosest = dx && closest.nodeType == 3 ? {
						left: rect.right < coords.left ? rect.right : rect.left,
						top: coords.top
					} : coords;
					if (child.nodeType == 1 && dx) offset = childIndex + (coords.left >= (rect.left + rect.right) / 2 ? 1 : 0);
					continue;
				}
			} else if (rect.top > coords.top && !firstBelow && rect.left <= coords.left && rect.right >= coords.left) {
				firstBelow = child;
				coordsBelow = {
					left: Math.max(rect.left, Math.min(rect.right, coords.left)),
					top: rect.top
				};
			}
			if (!closest && (coords.left >= rect.right && coords.top >= rect.top || coords.left >= rect.left && coords.top >= rect.bottom)) offset = childIndex + 1;
		}
	}
	if (!closest && firstBelow) {
		closest = firstBelow;
		coordsClosest = coordsBelow;
		dxClosest = 0;
	}
	if (closest && closest.nodeType == 3) return findOffsetInText(closest, coordsClosest);
	if (!closest || dxClosest && closest.nodeType == 1) return {
		node,
		offset
	};
	return findOffsetInNode(closest, coordsClosest);
}
function findOffsetInText(node, coords) {
	let len = node.nodeValue.length;
	let range = document.createRange(), result;
	for (let i = 0; i < len; i++) {
		range.setEnd(node, i + 1);
		range.setStart(node, i);
		let rect = singleRect(range, 1);
		if (rect.top == rect.bottom) continue;
		if (inRect(coords, rect)) {
			result = {
				node,
				offset: i + (coords.left >= (rect.left + rect.right) / 2 ? 1 : 0)
			};
			break;
		}
	}
	range.detach();
	return result || {
		node,
		offset: 0
	};
}
function inRect(coords, rect) {
	return coords.left >= rect.left - 1 && coords.left <= rect.right + 1 && coords.top >= rect.top - 1 && coords.top <= rect.bottom + 1;
}
function targetKludge(dom, coords) {
	let parent = dom.parentNode;
	if (parent && /^li$/i.test(parent.nodeName) && coords.left < dom.getBoundingClientRect().left) return parent;
	return dom;
}
function posFromElement(view, elt, coords) {
	let { node, offset } = findOffsetInNode(elt, coords), bias = -1;
	if (node.nodeType == 1 && !node.firstChild) {
		let rect = node.getBoundingClientRect();
		bias = rect.left != rect.right && coords.left > (rect.left + rect.right) / 2 ? 1 : -1;
	}
	return view.docView.posFromDOM(node, offset, bias);
}
function posFromCaret(view, node, offset, coords) {
	let outsideBlock = -1;
	for (let cur = node, sawBlock = false;;) {
		if (cur == view.dom) break;
		let desc = view.docView.nearestDesc(cur, true), rect;
		if (!desc) return null;
		if (desc.dom.nodeType == 1 && (desc.node.isBlock && desc.parent || !desc.contentDOM) && ((rect = desc.dom.getBoundingClientRect()).width || rect.height)) {
			if (desc.node.isBlock && desc.parent && !/^T(R|BODY|HEAD|FOOT)$/.test(desc.dom.nodeName)) {
				if (!sawBlock && rect.left > coords.left || rect.top > coords.top) outsideBlock = desc.posBefore;
				else if (!sawBlock && rect.right < coords.left || rect.bottom < coords.top) outsideBlock = desc.posAfter;
				sawBlock = true;
			}
			if (!desc.contentDOM && outsideBlock < 0 && !desc.node.isText) return (desc.node.isBlock ? coords.top < (rect.top + rect.bottom) / 2 : coords.left < (rect.left + rect.right) / 2) ? desc.posBefore : desc.posAfter;
		}
		cur = desc.dom.parentNode;
	}
	return outsideBlock > -1 ? outsideBlock : view.docView.posFromDOM(node, offset, -1);
}
function elementFromPoint(element, coords, box) {
	let len = element.childNodes.length;
	if (len && box.top < box.bottom) for (let startI = Math.max(0, Math.min(len - 1, Math.floor(len * (coords.top - box.top) / (box.bottom - box.top)) - 2)), i = startI;;) {
		let child = element.childNodes[i];
		if (child.nodeType == 1) {
			let rects = child.getClientRects();
			for (let j = 0; j < rects.length; j++) {
				let rect = rects[j];
				if (inRect(coords, rect)) return elementFromPoint(child, coords, rect);
			}
		}
		if ((i = (i + 1) % len) == startI) break;
	}
	return element;
}
function posAtCoords(view, coords) {
	let doc = view.dom.ownerDocument, node, offset = 0;
	let caret = caretFromPoint(doc, coords.left, coords.top);
	if (caret) ({node, offset} = caret);
	let elt = (view.root.elementFromPoint ? view.root : doc).elementFromPoint(coords.left, coords.top);
	let pos;
	if (!elt || !view.dom.contains(elt.nodeType != 1 ? elt.parentNode : elt)) {
		let box = view.dom.getBoundingClientRect();
		if (!inRect(coords, box)) return null;
		elt = elementFromPoint(view.dom, coords, box);
		if (!elt) return null;
	}
	if (safari) {
		for (let p = elt; node && p; p = parentNode(p)) if (p.draggable) node = void 0;
	}
	elt = targetKludge(elt, coords);
	if (node) {
		if (gecko && node.nodeType == 1) {
			offset = Math.min(offset, node.childNodes.length);
			if (offset < node.childNodes.length) {
				let next = node.childNodes[offset], box;
				if (next.nodeName == "IMG" && (box = next.getBoundingClientRect()).right <= coords.left && box.bottom > coords.top) offset++;
			}
		}
		let prev;
		if (webkit && offset && node.nodeType == 1 && (prev = node.childNodes[offset - 1]).nodeType == 1 && prev.contentEditable == "false" && prev.getBoundingClientRect().top >= coords.top) offset--;
		if (node == view.dom && offset == node.childNodes.length - 1 && node.lastChild.nodeType == 1 && coords.top > node.lastChild.getBoundingClientRect().bottom) pos = view.state.doc.content.size;
		else if (offset == 0 || node.nodeType != 1 || node.childNodes[offset - 1].nodeName != "BR") pos = posFromCaret(view, node, offset, coords);
	}
	if (pos == null) pos = posFromElement(view, elt, coords);
	let desc = view.docView.nearestDesc(elt, true);
	return {
		pos,
		inside: desc ? desc.posAtStart - desc.border : -1
	};
}
function nonZero(rect) {
	return rect.top < rect.bottom || rect.left < rect.right;
}
function singleRect(target, bias) {
	let rects = target.getClientRects();
	if (rects.length) {
		let first = rects[bias < 0 ? 0 : rects.length - 1];
		if (nonZero(first)) return first;
	}
	return Array.prototype.find.call(rects, nonZero) || target.getBoundingClientRect();
}
var BIDI = /[\u0590-\u05f4\u0600-\u06ff\u0700-\u08ac]/;
function coordsAtPos(view, pos, side) {
	let { node, offset, atom } = view.docView.domFromPos(pos, side < 0 ? -1 : 1);
	let supportEmptyRange = webkit || gecko;
	if (node.nodeType == 3) if (supportEmptyRange && (BIDI.test(node.nodeValue) || (side < 0 ? !offset : offset == node.nodeValue.length))) {
		let rect = singleRect(textRange(node, offset, offset), side);
		if (gecko && offset && /\s/.test(node.nodeValue[offset - 1]) && offset < node.nodeValue.length) {
			let rectBefore = singleRect(textRange(node, offset - 1, offset - 1), -1);
			if (rectBefore.top == rect.top) {
				let rectAfter = singleRect(textRange(node, offset, offset + 1), -1);
				if (rectAfter.top != rect.top) return flattenV(rectAfter, rectAfter.left < rectBefore.left);
			}
		}
		return rect;
	} else {
		let from = offset, to = offset, takeSide = side < 0 ? 1 : -1;
		if (side < 0 && !offset) {
			to++;
			takeSide = -1;
		} else if (side >= 0 && offset == node.nodeValue.length) {
			from--;
			takeSide = 1;
		} else if (side < 0) from--;
		else to++;
		return flattenV(singleRect(textRange(node, from, to), takeSide), takeSide < 0);
	}
	if (!view.state.doc.resolve(pos - (atom || 0)).parent.inlineContent) {
		if (atom == null && offset && (side < 0 || offset == nodeSize(node))) {
			let before = node.childNodes[offset - 1];
			if (before.nodeType == 1) return flattenH(before.getBoundingClientRect(), false);
		}
		if (atom == null && offset < nodeSize(node)) {
			let after = node.childNodes[offset];
			if (after.nodeType == 1) return flattenH(after.getBoundingClientRect(), true);
		}
		return flattenH(node.getBoundingClientRect(), side >= 0);
	}
	if (atom == null && offset && (side < 0 || offset == nodeSize(node))) {
		let before = node.childNodes[offset - 1];
		let target = before.nodeType == 3 ? textRange(before, nodeSize(before) - (supportEmptyRange ? 0 : 1)) : before.nodeType == 1 && (before.nodeName != "BR" || !before.nextSibling) ? before : null;
		if (target) return flattenV(singleRect(target, 1), false);
	}
	if (atom == null && offset < nodeSize(node)) {
		let after = node.childNodes[offset];
		while (after.pmViewDesc && after.pmViewDesc.ignoreForCoords) after = after.nextSibling;
		let target = !after ? null : after.nodeType == 3 ? textRange(after, 0, supportEmptyRange ? 0 : 1) : after.nodeType == 1 ? after : null;
		if (target) return flattenV(singleRect(target, -1), true);
	}
	return flattenV(singleRect(node.nodeType == 3 ? textRange(node) : node, -side), side >= 0);
}
function flattenV(rect, left) {
	if (rect.width == 0) return rect;
	let x = left ? rect.left : rect.right;
	return {
		top: rect.top,
		bottom: rect.bottom,
		left: x,
		right: x
	};
}
function flattenH(rect, top) {
	if (rect.height == 0) return rect;
	let y = top ? rect.top : rect.bottom;
	return {
		top: y,
		bottom: y,
		left: rect.left,
		right: rect.right
	};
}
function withFlushedState(view, state, f) {
	let viewState = view.state, active = view.root.activeElement;
	if (viewState != state) view.updateState(state);
	if (active != view.dom) view.focus();
	try {
		return f();
	} finally {
		if (viewState != state) view.updateState(viewState);
		if (active != view.dom && active) active.focus();
	}
}
function endOfTextblockVertical(view, state, dir) {
	let sel = state.selection;
	let $pos = dir == "up" ? sel.$from : sel.$to;
	return withFlushedState(view, state, () => {
		let { node: dom } = view.docView.domFromPos($pos.pos, dir == "up" ? -1 : 1);
		for (;;) {
			let nearest = view.docView.nearestDesc(dom, true);
			if (!nearest) break;
			if (nearest.node.isBlock) {
				dom = nearest.contentDOM || nearest.dom;
				break;
			}
			dom = nearest.dom.parentNode;
		}
		let coords = coordsAtPos(view, $pos.pos, 1);
		for (let child = dom.firstChild; child; child = child.nextSibling) {
			let boxes;
			if (child.nodeType == 1) boxes = child.getClientRects();
			else if (child.nodeType == 3) boxes = textRange(child, 0, child.nodeValue.length).getClientRects();
			else continue;
			for (let i = 0; i < boxes.length; i++) {
				let box = boxes[i];
				if (box.bottom > box.top + 1 && (dir == "up" ? coords.top - box.top > (box.bottom - coords.top) * 2 : box.bottom - coords.bottom > (coords.bottom - box.top) * 2)) return false;
			}
		}
		return true;
	});
}
var maybeRTL = /[\u0590-\u08ac]/;
function endOfTextblockHorizontal(view, state, dir) {
	let { $head } = state.selection;
	if (!$head.parent.isTextblock) return false;
	let offset = $head.parentOffset, atStart = !offset, atEnd = offset == $head.parent.content.size;
	let sel = view.domSelection();
	if (!sel) return $head.pos == $head.start() || $head.pos == $head.end();
	if (!maybeRTL.test($head.parent.textContent) || !sel.modify) return dir == "left" || dir == "backward" ? atStart : atEnd;
	return withFlushedState(view, state, () => {
		let { focusNode: oldNode, focusOffset: oldOff, anchorNode, anchorOffset } = view.domSelectionRange();
		let oldBidiLevel = sel.caretBidiLevel;
		sel.modify("move", dir, "character");
		let parentDOM = $head.depth ? view.docView.domAfterPos($head.before()) : view.dom;
		let { focusNode: newNode, focusOffset: newOff } = view.domSelectionRange();
		let result = newNode && !parentDOM.contains(newNode.nodeType == 1 ? newNode : newNode.parentNode) || oldNode == newNode && oldOff == newOff;
		try {
			sel.collapse(anchorNode, anchorOffset);
			if (oldNode && (oldNode != anchorNode || oldOff != anchorOffset) && sel.extend) sel.extend(oldNode, oldOff);
		} catch (_) {}
		if (oldBidiLevel != null) sel.caretBidiLevel = oldBidiLevel;
		return result;
	});
}
var cachedState = null;
var cachedDir = null;
var cachedResult = false;
function endOfTextblock(view, state, dir) {
	if (cachedState == state && cachedDir == dir) return cachedResult;
	cachedState = state;
	cachedDir = dir;
	return cachedResult = dir == "up" || dir == "down" ? endOfTextblockVertical(view, state, dir) : endOfTextblockHorizontal(view, state, dir);
}
var NOT_DIRTY = 0, CHILD_DIRTY = 1, CONTENT_DIRTY = 2, NODE_DIRTY = 3;
var ViewDesc = class {
	constructor(parent, children, dom, contentDOM) {
		this.parent = parent;
		this.children = children;
		this.dom = dom;
		this.contentDOM = contentDOM;
		this.dirty = NOT_DIRTY;
		dom.pmViewDesc = this;
	}
	matchesWidget(widget) {
		return false;
	}
	matchesMark(mark) {
		return false;
	}
	matchesNode(node, outerDeco, innerDeco) {
		return false;
	}
	matchesHack(nodeName) {
		return false;
	}
	parseRule() {
		return null;
	}
	stopEvent(event) {
		return false;
	}
	get size() {
		let size = 0;
		for (let i = 0; i < this.children.length; i++) size += this.children[i].size;
		return size;
	}
	get border() {
		return 0;
	}
	destroy() {
		this.parent = void 0;
		if (this.dom.pmViewDesc == this) this.dom.pmViewDesc = void 0;
		for (let i = 0; i < this.children.length; i++) this.children[i].destroy();
	}
	posBeforeChild(child) {
		for (let i = 0, pos = this.posAtStart;; i++) {
			let cur = this.children[i];
			if (cur == child) return pos;
			pos += cur.size;
		}
	}
	get posBefore() {
		return this.parent.posBeforeChild(this);
	}
	get posAtStart() {
		return this.parent ? this.parent.posBeforeChild(this) + this.border : 0;
	}
	get posAfter() {
		return this.posBefore + this.size;
	}
	get posAtEnd() {
		return this.posAtStart + this.size - 2 * this.border;
	}
	localPosFromDOM(dom, offset, bias) {
		if (this.contentDOM && this.contentDOM.contains(dom.nodeType == 1 ? dom : dom.parentNode)) if (bias < 0) {
			let domBefore, desc;
			if (dom == this.contentDOM) domBefore = dom.childNodes[offset - 1];
			else {
				while (dom.parentNode != this.contentDOM) dom = dom.parentNode;
				domBefore = dom.previousSibling;
			}
			while (domBefore && !((desc = domBefore.pmViewDesc) && desc.parent == this)) domBefore = domBefore.previousSibling;
			return domBefore ? this.posBeforeChild(desc) + desc.size : this.posAtStart;
		} else {
			let domAfter, desc;
			if (dom == this.contentDOM) domAfter = dom.childNodes[offset];
			else {
				while (dom.parentNode != this.contentDOM) dom = dom.parentNode;
				domAfter = dom.nextSibling;
			}
			while (domAfter && !((desc = domAfter.pmViewDesc) && desc.parent == this)) domAfter = domAfter.nextSibling;
			return domAfter ? this.posBeforeChild(desc) : this.posAtEnd;
		}
		let atEnd;
		if (dom == this.dom && this.contentDOM) atEnd = offset > domIndex(this.contentDOM);
		else if (this.contentDOM && this.contentDOM != this.dom && this.dom.contains(this.contentDOM)) atEnd = dom.compareDocumentPosition(this.contentDOM) & 2;
		else if (this.dom.firstChild) {
			if (offset == 0) for (let search = dom;; search = search.parentNode) {
				if (search == this.dom) {
					atEnd = false;
					break;
				}
				if (search.previousSibling) break;
			}
			if (atEnd == null && offset == dom.childNodes.length) for (let search = dom;; search = search.parentNode) {
				if (search == this.dom) {
					atEnd = true;
					break;
				}
				if (search.nextSibling) break;
			}
		}
		return (atEnd == null ? bias > 0 : atEnd) ? this.posAtEnd : this.posAtStart;
	}
	nearestDesc(dom, onlyNodes = false) {
		for (let first = true, cur = dom; cur; cur = cur.parentNode) {
			let desc = this.getDesc(cur), nodeDOM;
			if (desc && (!onlyNodes || desc.node)) if (first && (nodeDOM = desc.nodeDOM) && !(nodeDOM.nodeType == 1 ? nodeDOM.contains(dom.nodeType == 1 ? dom : dom.parentNode) : nodeDOM == dom)) first = false;
			else return desc;
		}
	}
	getDesc(dom) {
		let desc = dom.pmViewDesc;
		for (let cur = desc; cur; cur = cur.parent) if (cur == this) return desc;
	}
	posFromDOM(dom, offset, bias) {
		for (let scan = dom; scan; scan = scan.parentNode) {
			let desc = this.getDesc(scan);
			if (desc) return desc.localPosFromDOM(dom, offset, bias);
		}
		return -1;
	}
	descAt(pos) {
		for (let i = 0, offset = 0; i < this.children.length; i++) {
			let child = this.children[i], end = offset + child.size;
			if (offset == pos && end != offset) {
				while (!child.border && child.children.length) for (let i = 0; i < child.children.length; i++) {
					let inner = child.children[i];
					if (inner.size) {
						child = inner;
						break;
					}
				}
				return child;
			}
			if (pos < end) return child.descAt(pos - offset - child.border);
			offset = end;
		}
	}
	domFromPos(pos, side) {
		if (!this.contentDOM) return {
			node: this.dom,
			offset: 0,
			atom: pos + 1
		};
		let i = 0, offset = 0;
		for (let curPos = 0; i < this.children.length; i++) {
			let child = this.children[i], end = curPos + child.size;
			if (end > pos || child instanceof TrailingHackViewDesc) {
				offset = pos - curPos;
				break;
			}
			curPos = end;
		}
		if (offset) return this.children[i].domFromPos(offset - this.children[i].border, side);
		for (let prev; i && !(prev = this.children[i - 1]).size && prev instanceof WidgetViewDesc && prev.side >= 0; i--);
		if (side <= 0) {
			let prev, enter = true;
			for (;; i--, enter = false) {
				prev = i ? this.children[i - 1] : null;
				if (!prev || prev.dom.parentNode == this.contentDOM) break;
			}
			if (prev && side && enter && !prev.border && !prev.domAtom) return prev.domFromPos(prev.size, side);
			return {
				node: this.contentDOM,
				offset: prev ? domIndex(prev.dom) + 1 : 0
			};
		} else {
			let next, enter = true;
			for (;; i++, enter = false) {
				next = i < this.children.length ? this.children[i] : null;
				if (!next || next.dom.parentNode == this.contentDOM) break;
			}
			if (next && enter && !next.border && !next.domAtom) return next.domFromPos(0, side);
			return {
				node: this.contentDOM,
				offset: next ? domIndex(next.dom) : this.contentDOM.childNodes.length
			};
		}
	}
	parseRange(from, to, base = 0) {
		if (this.children.length == 0) return {
			node: this.contentDOM,
			from,
			to,
			fromOffset: 0,
			toOffset: this.contentDOM.childNodes.length
		};
		let fromOffset = -1, toOffset = -1;
		for (let offset = base, i = 0;; i++) {
			let child = this.children[i], end = offset + child.size;
			if (fromOffset == -1 && from <= end) {
				let childBase = offset + child.border;
				if (from >= childBase && to <= end - child.border && child.node && child.contentDOM && this.contentDOM.contains(child.contentDOM)) return child.parseRange(from, to, childBase);
				from = offset;
				for (let j = i; j > 0; j--) {
					let prev = this.children[j - 1];
					if (prev.size && prev.dom.parentNode == this.contentDOM && !prev.emptyChildAt(1)) {
						fromOffset = domIndex(prev.dom) + 1;
						break;
					}
					from -= prev.size;
				}
				if (fromOffset == -1) fromOffset = 0;
			}
			if (fromOffset > -1 && (end > to || i == this.children.length - 1)) {
				to = end;
				for (let j = i + 1; j < this.children.length; j++) {
					let next = this.children[j];
					if (next.size && next.dom.parentNode == this.contentDOM && !next.emptyChildAt(-1)) {
						toOffset = domIndex(next.dom);
						break;
					}
					to += next.size;
				}
				if (toOffset == -1) toOffset = this.contentDOM.childNodes.length;
				break;
			}
			offset = end;
		}
		return {
			node: this.contentDOM,
			from,
			to,
			fromOffset,
			toOffset
		};
	}
	emptyChildAt(side) {
		if (this.border || !this.contentDOM || !this.children.length) return false;
		let child = this.children[side < 0 ? 0 : this.children.length - 1];
		return child.size == 0 || child.emptyChildAt(side);
	}
	domAfterPos(pos) {
		let { node, offset } = this.domFromPos(pos, 0);
		if (node.nodeType != 1 || offset == node.childNodes.length) throw new RangeError("No node after pos " + pos);
		return node.childNodes[offset];
	}
	setSelection(anchor, head, view, force = false) {
		let from = Math.min(anchor, head), to = Math.max(anchor, head);
		for (let i = 0, offset = 0; i < this.children.length; i++) {
			let child = this.children[i], end = offset + child.size;
			if (from > offset && to < end) return child.setSelection(anchor - offset - child.border, head - offset - child.border, view, force);
			offset = end;
		}
		let anchorDOM = this.domFromPos(anchor, anchor ? -1 : 1);
		let headDOM = head == anchor ? anchorDOM : this.domFromPos(head, head ? -1 : 1);
		let domSel = view.root.getSelection();
		let selRange = view.domSelectionRange();
		let brKludge = false;
		if ((gecko || safari) && anchor == head) {
			let { node, offset } = anchorDOM;
			if (node.nodeType == 3) {
				brKludge = !!(offset && node.nodeValue[offset - 1] == "\n");
				if (brKludge && offset == node.nodeValue.length) for (let scan = node, after; scan; scan = scan.parentNode) {
					if (after = scan.nextSibling) {
						if (after.nodeName == "BR") anchorDOM = headDOM = {
							node: after.parentNode,
							offset: domIndex(after) + 1
						};
						break;
					}
					let desc = scan.pmViewDesc;
					if (desc && desc.node && desc.node.isBlock) break;
				}
			} else {
				let prev = node.childNodes[offset - 1];
				brKludge = prev && (prev.nodeName == "BR" || prev.contentEditable == "false");
			}
		}
		if (gecko && selRange.focusNode && selRange.focusNode != headDOM.node && selRange.focusNode.nodeType == 1) {
			let after = selRange.focusNode.childNodes[selRange.focusOffset];
			if (after && after.contentEditable == "false") force = true;
		}
		if (!(force || brKludge && safari) && isEquivalentPosition(anchorDOM.node, anchorDOM.offset, selRange.anchorNode, selRange.anchorOffset) && isEquivalentPosition(headDOM.node, headDOM.offset, selRange.focusNode, selRange.focusOffset)) return;
		let domSelExtended = false;
		if ((domSel.extend || anchor == head) && !(brKludge && gecko)) {
			domSel.collapse(anchorDOM.node, anchorDOM.offset);
			try {
				if (anchor != head) domSel.extend(headDOM.node, headDOM.offset);
				domSelExtended = true;
			} catch (_) {}
		}
		if (!domSelExtended) {
			if (anchor > head) {
				let tmp = anchorDOM;
				anchorDOM = headDOM;
				headDOM = tmp;
			}
			let range = document.createRange();
			range.setEnd(headDOM.node, headDOM.offset);
			range.setStart(anchorDOM.node, anchorDOM.offset);
			domSel.removeAllRanges();
			domSel.addRange(range);
		}
	}
	ignoreMutation(mutation) {
		return !this.contentDOM && mutation.type != "selection";
	}
	get contentLost() {
		return this.contentDOM && this.contentDOM != this.dom && !this.dom.contains(this.contentDOM);
	}
	markDirty(from, to) {
		for (let offset = 0, i = 0; i < this.children.length; i++) {
			let child = this.children[i], end = offset + child.size;
			if (offset == end ? from <= end && to >= offset : from < end && to > offset) {
				let startInside = offset + child.border, endInside = end - child.border;
				if (from >= startInside && to <= endInside) {
					this.dirty = from == offset || to == end ? CONTENT_DIRTY : CHILD_DIRTY;
					if (from == startInside && to == endInside && (child.contentLost || child.dom.parentNode != this.contentDOM)) child.dirty = NODE_DIRTY;
					else child.markDirty(from - startInside, to - startInside);
					return;
				} else child.dirty = child.dom == child.contentDOM && child.dom.parentNode == this.contentDOM && !child.children.length ? CONTENT_DIRTY : NODE_DIRTY;
			}
			offset = end;
		}
		this.dirty = CONTENT_DIRTY;
	}
	markParentsDirty() {
		let level = 1;
		for (let node = this.parent; node; node = node.parent, level++) {
			let dirty = level == 1 ? CONTENT_DIRTY : CHILD_DIRTY;
			if (node.dirty < dirty) node.dirty = dirty;
		}
	}
	get domAtom() {
		return false;
	}
	get ignoreForCoords() {
		return false;
	}
	get ignoreForSelection() {
		return false;
	}
	isText(text) {
		return false;
	}
};
var WidgetViewDesc = class extends ViewDesc {
	constructor(parent, widget, view, pos) {
		let self, dom = widget.type.toDOM;
		if (typeof dom == "function") dom = dom(view, () => {
			if (!self) return pos;
			if (self.parent) return self.parent.posBeforeChild(self);
		});
		if (!widget.type.spec.raw) {
			if (dom.nodeType != 1) {
				let wrap = document.createElement("span");
				wrap.appendChild(dom);
				dom = wrap;
			}
			dom.contentEditable = "false";
			dom.classList.add("ProseMirror-widget");
		}
		super(parent, [], dom, null);
		this.widget = widget;
		this.widget = widget;
		self = this;
	}
	matchesWidget(widget) {
		return this.dirty == NOT_DIRTY && widget.type.eq(this.widget.type);
	}
	parseRule() {
		return { ignore: true };
	}
	stopEvent(event) {
		let stop = this.widget.spec.stopEvent;
		return stop ? stop(event) : false;
	}
	ignoreMutation(mutation) {
		return mutation.type != "selection" || this.widget.spec.ignoreSelection;
	}
	destroy() {
		this.widget.type.destroy(this.dom);
		super.destroy();
	}
	get domAtom() {
		return true;
	}
	get ignoreForSelection() {
		return !!this.widget.type.spec.relaxedSide;
	}
	get side() {
		return this.widget.type.side;
	}
};
var CompositionViewDesc = class extends ViewDesc {
	constructor(parent, dom, textDOM, text) {
		super(parent, [], dom, null);
		this.textDOM = textDOM;
		this.text = text;
	}
	get size() {
		return this.text.length;
	}
	localPosFromDOM(dom, offset) {
		if (dom != this.textDOM) return this.posAtStart + (offset ? this.size : 0);
		return this.posAtStart + offset;
	}
	domFromPos(pos) {
		return {
			node: this.textDOM,
			offset: pos
		};
	}
	ignoreMutation(mut) {
		return mut.type === "characterData" && mut.target.nodeValue == mut.oldValue;
	}
};
var MarkViewDesc = class MarkViewDesc extends ViewDesc {
	constructor(parent, mark, dom, contentDOM, spec) {
		super(parent, [], dom, contentDOM);
		this.mark = mark;
		this.spec = spec;
	}
	static create(parent, mark, inline, view) {
		let custom = view.nodeViews[mark.type.name];
		let spec = custom && custom(mark, view, inline);
		if (!spec || !spec.dom) spec = DOMSerializer.renderSpec(document, mark.type.spec.toDOM(mark, inline), null, mark.attrs);
		return new MarkViewDesc(parent, mark, spec.dom, spec.contentDOM || spec.dom, spec);
	}
	parseRule() {
		if (this.dirty & NODE_DIRTY || this.mark.type.spec.reparseInView) return null;
		return {
			mark: this.mark.type.name,
			attrs: this.mark.attrs,
			contentElement: this.contentDOM
		};
	}
	matchesMark(mark) {
		return this.dirty != NODE_DIRTY && this.mark.eq(mark);
	}
	markDirty(from, to) {
		super.markDirty(from, to);
		if (this.dirty != NOT_DIRTY) {
			let parent = this.parent;
			while (!parent.node) parent = parent.parent;
			if (parent.dirty < this.dirty) parent.dirty = this.dirty;
			this.dirty = NOT_DIRTY;
		}
	}
	slice(from, to, view) {
		let copy = MarkViewDesc.create(this.parent, this.mark, true, view);
		let nodes = this.children, size = this.size;
		if (to < size) nodes = replaceNodes(nodes, to, size, view);
		if (from > 0) nodes = replaceNodes(nodes, 0, from, view);
		for (let i = 0; i < nodes.length; i++) nodes[i].parent = copy;
		copy.children = nodes;
		return copy;
	}
	ignoreMutation(mutation) {
		return this.spec.ignoreMutation ? this.spec.ignoreMutation(mutation) : super.ignoreMutation(mutation);
	}
	destroy() {
		if (this.spec.destroy) this.spec.destroy();
		super.destroy();
	}
};
var NodeViewDesc = class NodeViewDesc extends ViewDesc {
	constructor(parent, node, outerDeco, innerDeco, dom, contentDOM, nodeDOM, view, pos) {
		super(parent, [], dom, contentDOM);
		this.node = node;
		this.outerDeco = outerDeco;
		this.innerDeco = innerDeco;
		this.nodeDOM = nodeDOM;
	}
	static create(parent, node, outerDeco, innerDeco, view, pos) {
		let custom = view.nodeViews[node.type.name], descObj;
		let spec = custom && custom(node, view, () => {
			if (!descObj) return pos;
			if (descObj.parent) return descObj.parent.posBeforeChild(descObj);
		}, outerDeco, innerDeco);
		let dom = spec && spec.dom, contentDOM = spec && spec.contentDOM;
		if (node.isText) {
			if (!dom) dom = document.createTextNode(node.text);
			else if (dom.nodeType != 3) throw new RangeError("Text must be rendered as a DOM text node");
		} else if (!dom) {
			let spec = DOMSerializer.renderSpec(document, node.type.spec.toDOM(node), null, node.attrs);
			({dom, contentDOM} = spec);
		}
		if (!contentDOM && !node.isText && dom.nodeName != "BR") {
			if (!dom.hasAttribute("contenteditable")) dom.contentEditable = "false";
			if (node.type.spec.draggable) dom.draggable = true;
		}
		let nodeDOM = dom;
		dom = applyOuterDeco(dom, outerDeco, node);
		if (spec) return descObj = new CustomNodeViewDesc(parent, node, outerDeco, innerDeco, dom, contentDOM || null, nodeDOM, spec, view, pos + 1);
		else if (node.isText) return new TextViewDesc(parent, node, outerDeco, innerDeco, dom, nodeDOM, view);
		else return new NodeViewDesc(parent, node, outerDeco, innerDeco, dom, contentDOM || null, nodeDOM, view, pos + 1);
	}
	parseRule() {
		if (this.node.type.spec.reparseInView) return null;
		let rule = {
			node: this.node.type.name,
			attrs: this.node.attrs
		};
		if (this.node.type.whitespace == "pre") rule.preserveWhitespace = "full";
		if (!this.contentDOM) rule.getContent = () => this.node.content;
		else if (!this.contentLost) rule.contentElement = this.contentDOM;
		else {
			for (let i = this.children.length - 1; i >= 0; i--) {
				let child = this.children[i];
				if (this.dom.contains(child.dom.parentNode)) {
					rule.contentElement = child.dom.parentNode;
					break;
				}
			}
			if (!rule.contentElement) rule.getContent = () => Fragment.empty;
		}
		return rule;
	}
	matchesNode(node, outerDeco, innerDeco) {
		return this.dirty == NOT_DIRTY && node.eq(this.node) && sameOuterDeco(outerDeco, this.outerDeco) && innerDeco.eq(this.innerDeco);
	}
	get size() {
		return this.node.nodeSize;
	}
	get border() {
		return this.node.isLeaf ? 0 : 1;
	}
	updateChildren(view, pos) {
		let inline = this.node.inlineContent, off = pos;
		let composition = view.composing ? this.localCompositionInfo(view, pos) : null;
		let localComposition = composition && composition.pos > -1 ? composition : null;
		let compositionInChild = composition && composition.pos < 0;
		let updater = new ViewTreeUpdater(this, localComposition && localComposition.node, view);
		iterDeco(this.node, this.innerDeco, (widget, i, insideNode) => {
			if (widget.spec.marks) updater.syncToMarks(widget.spec.marks, inline, view, i);
			else if (widget.type.side >= 0 && !insideNode) updater.syncToMarks(i == this.node.childCount ? Mark.none : this.node.child(i).marks, inline, view, i);
			updater.placeWidget(widget, view, off);
		}, (child, outerDeco, innerDeco, i) => {
			updater.syncToMarks(child.marks, inline, view, i);
			let compIndex;
			if (updater.findNodeMatch(child, outerDeco, innerDeco, i));
			else if (compositionInChild && view.state.selection.from > off && view.state.selection.to < off + child.nodeSize && (compIndex = updater.findIndexWithChild(composition.node)) > -1 && updater.updateNodeAt(child, outerDeco, innerDeco, compIndex, view));
			else if (updater.updateNextNode(child, outerDeco, innerDeco, view, i, off));
			else updater.addNode(child, outerDeco, innerDeco, view, off);
			off += child.nodeSize;
		});
		updater.syncToMarks([], inline, view, 0);
		if (this.node.isTextblock) updater.addTextblockHacks();
		updater.destroyRest();
		if (updater.changed || this.dirty == CONTENT_DIRTY) {
			if (localComposition) this.protectLocalComposition(view, localComposition);
			renderDescs(this.contentDOM, this.children, view);
			if (ios) iosHacks(this.dom);
		}
	}
	localCompositionInfo(view, pos) {
		let { from, to } = view.state.selection;
		if (!(view.state.selection instanceof TextSelection) || from < pos || to > pos + this.node.content.size) return null;
		let textNode = view.input.compositionNode;
		if (!textNode || !this.dom.contains(textNode.parentNode)) return null;
		if (this.node.inlineContent) {
			let text = textNode.nodeValue;
			let textPos = findTextInFragment(this.node.content, text, from - pos, to - pos);
			return textPos < 0 ? null : {
				node: textNode,
				pos: textPos,
				text
			};
		} else return {
			node: textNode,
			pos: -1,
			text: ""
		};
	}
	protectLocalComposition(view, { node, pos, text }) {
		if (this.getDesc(node)) return;
		let topNode = node;
		for (;; topNode = topNode.parentNode) {
			if (topNode.parentNode == this.contentDOM) break;
			while (topNode.previousSibling) topNode.parentNode.removeChild(topNode.previousSibling);
			while (topNode.nextSibling) topNode.parentNode.removeChild(topNode.nextSibling);
			if (topNode.pmViewDesc) topNode.pmViewDesc = void 0;
		}
		let desc = new CompositionViewDesc(this, topNode, node, text);
		view.input.compositionNodes.push(desc);
		this.children = replaceNodes(this.children, pos, pos + text.length, view, desc);
	}
	update(node, outerDeco, innerDeco, view) {
		if (this.dirty == NODE_DIRTY || !node.sameMarkup(this.node)) return false;
		this.updateInner(node, outerDeco, innerDeco, view);
		return true;
	}
	updateInner(node, outerDeco, innerDeco, view) {
		this.updateOuterDeco(outerDeco);
		this.node = node;
		this.innerDeco = innerDeco;
		if (this.contentDOM) this.updateChildren(view, this.posAtStart);
		this.dirty = NOT_DIRTY;
	}
	updateOuterDeco(outerDeco) {
		if (sameOuterDeco(outerDeco, this.outerDeco)) return;
		let needsWrap = this.nodeDOM.nodeType != 1;
		let oldDOM = this.dom;
		this.dom = patchOuterDeco(this.dom, this.nodeDOM, computeOuterDeco(this.outerDeco, this.node, needsWrap), computeOuterDeco(outerDeco, this.node, needsWrap));
		if (this.dom != oldDOM) {
			oldDOM.pmViewDesc = void 0;
			this.dom.pmViewDesc = this;
		}
		this.outerDeco = outerDeco;
	}
	selectNode() {
		if (this.nodeDOM.nodeType == 1) {
			this.nodeDOM.classList.add("ProseMirror-selectednode");
			if (this.contentDOM || !this.node.type.spec.draggable) this.nodeDOM.draggable = true;
		}
	}
	deselectNode() {
		if (this.nodeDOM.nodeType == 1) {
			this.nodeDOM.classList.remove("ProseMirror-selectednode");
			if (this.contentDOM || !this.node.type.spec.draggable) this.nodeDOM.removeAttribute("draggable");
		}
	}
	get domAtom() {
		return this.node.isAtom;
	}
};
function docViewDesc(doc, outerDeco, innerDeco, dom, view) {
	applyOuterDeco(dom, outerDeco, doc);
	let docView = new NodeViewDesc(void 0, doc, outerDeco, innerDeco, dom, dom, dom, view, 0);
	if (docView.contentDOM) docView.updateChildren(view, 0);
	return docView;
}
var TextViewDesc = class TextViewDesc extends NodeViewDesc {
	constructor(parent, node, outerDeco, innerDeco, dom, nodeDOM, view) {
		super(parent, node, outerDeco, innerDeco, dom, null, nodeDOM, view, 0);
	}
	parseRule() {
		let skip = this.nodeDOM.parentNode;
		while (skip && skip != this.dom && !skip.pmIsDeco) skip = skip.parentNode;
		return { skip: skip || true };
	}
	update(node, outerDeco, innerDeco, view) {
		if (this.dirty == NODE_DIRTY || this.dirty != NOT_DIRTY && !this.inParent() || !node.sameMarkup(this.node)) return false;
		this.updateOuterDeco(outerDeco);
		if ((this.dirty != NOT_DIRTY || node.text != this.node.text) && node.text != this.nodeDOM.nodeValue) {
			this.nodeDOM.nodeValue = node.text;
			if (view.trackWrites == this.nodeDOM) view.trackWrites = null;
		}
		this.node = node;
		this.dirty = NOT_DIRTY;
		return true;
	}
	inParent() {
		let parentDOM = this.parent.contentDOM;
		for (let n = this.nodeDOM; n; n = n.parentNode) if (n == parentDOM) return true;
		return false;
	}
	domFromPos(pos) {
		return {
			node: this.nodeDOM,
			offset: pos
		};
	}
	localPosFromDOM(dom, offset, bias) {
		if (dom == this.nodeDOM) return this.posAtStart + Math.min(offset, this.node.text.length);
		return super.localPosFromDOM(dom, offset, bias);
	}
	ignoreMutation(mutation) {
		return mutation.type != "characterData" && mutation.type != "selection";
	}
	slice(from, to, view) {
		let node = this.node.cut(from, to), dom = document.createTextNode(node.text);
		return new TextViewDesc(this.parent, node, this.outerDeco, this.innerDeco, dom, dom, view);
	}
	markDirty(from, to) {
		super.markDirty(from, to);
		if (this.dom != this.nodeDOM && (from == 0 || to == this.nodeDOM.nodeValue.length)) this.dirty = NODE_DIRTY;
	}
	get domAtom() {
		return false;
	}
	isText(text) {
		return this.node.text == text;
	}
};
var TrailingHackViewDesc = class extends ViewDesc {
	parseRule() {
		return { ignore: true };
	}
	matchesHack(nodeName) {
		return this.dirty == NOT_DIRTY && this.dom.nodeName == nodeName;
	}
	get domAtom() {
		return true;
	}
	get ignoreForCoords() {
		return this.dom.nodeName == "IMG";
	}
};
var CustomNodeViewDesc = class extends NodeViewDesc {
	constructor(parent, node, outerDeco, innerDeco, dom, contentDOM, nodeDOM, spec, view, pos) {
		super(parent, node, outerDeco, innerDeco, dom, contentDOM, nodeDOM, view, pos);
		this.spec = spec;
	}
	update(node, outerDeco, innerDeco, view) {
		if (this.dirty == NODE_DIRTY) return false;
		if (this.spec.update && (this.node.type == node.type || this.spec.multiType)) {
			let result = this.spec.update(node, outerDeco, innerDeco);
			if (result) this.updateInner(node, outerDeco, innerDeco, view);
			return result;
		} else if (!this.contentDOM && !node.isLeaf) return false;
		else return super.update(node, outerDeco, innerDeco, view);
	}
	selectNode() {
		this.spec.selectNode ? this.spec.selectNode() : super.selectNode();
	}
	deselectNode() {
		this.spec.deselectNode ? this.spec.deselectNode() : super.deselectNode();
	}
	setSelection(anchor, head, view, force) {
		this.spec.setSelection ? this.spec.setSelection(anchor, head, view.root) : super.setSelection(anchor, head, view, force);
	}
	destroy() {
		if (this.spec.destroy) this.spec.destroy();
		super.destroy();
	}
	stopEvent(event) {
		return this.spec.stopEvent ? this.spec.stopEvent(event) : false;
	}
	ignoreMutation(mutation) {
		return this.spec.ignoreMutation ? this.spec.ignoreMutation(mutation) : super.ignoreMutation(mutation);
	}
};
function renderDescs(parentDOM, descs, view) {
	let dom = parentDOM.firstChild, written = false;
	for (let i = 0; i < descs.length; i++) {
		let desc = descs[i], childDOM = desc.dom;
		if (childDOM.parentNode == parentDOM) {
			while (childDOM != dom) {
				dom = rm(dom);
				written = true;
			}
			dom = dom.nextSibling;
		} else {
			written = true;
			parentDOM.insertBefore(childDOM, dom);
		}
		if (desc instanceof MarkViewDesc) {
			let pos = dom ? dom.previousSibling : parentDOM.lastChild;
			renderDescs(desc.contentDOM, desc.children, view);
			dom = pos ? pos.nextSibling : parentDOM.firstChild;
		}
	}
	while (dom) {
		dom = rm(dom);
		written = true;
	}
	if (written && view.trackWrites == parentDOM) view.trackWrites = null;
}
var OuterDecoLevel = function(nodeName) {
	if (nodeName) this.nodeName = nodeName;
};
OuterDecoLevel.prototype = Object.create(null);
var noDeco = [new OuterDecoLevel()];
function computeOuterDeco(outerDeco, node, needsWrap) {
	if (outerDeco.length == 0) return noDeco;
	let top = needsWrap ? noDeco[0] : new OuterDecoLevel(), result = [top];
	for (let i = 0; i < outerDeco.length; i++) {
		let attrs = outerDeco[i].type.attrs;
		if (!attrs) continue;
		if (attrs.nodeName) result.push(top = new OuterDecoLevel(attrs.nodeName));
		for (let name in attrs) {
			let val = attrs[name];
			if (val == null) continue;
			if (needsWrap && result.length == 1) result.push(top = new OuterDecoLevel(node.isInline ? "span" : "div"));
			if (name == "class") top.class = (top.class ? top.class + " " : "") + val;
			else if (name == "style") top.style = (top.style ? top.style + ";" : "") + val;
			else if (name != "nodeName") top[name] = val;
		}
	}
	return result;
}
function patchOuterDeco(outerDOM, nodeDOM, prevComputed, curComputed) {
	if (prevComputed == noDeco && curComputed == noDeco) return nodeDOM;
	let curDOM = nodeDOM;
	for (let i = 0; i < curComputed.length; i++) {
		let deco = curComputed[i], prev = prevComputed[i];
		if (i) {
			let parent;
			if (prev && prev.nodeName == deco.nodeName && curDOM != outerDOM && (parent = curDOM.parentNode) && parent.nodeName.toLowerCase() == deco.nodeName) curDOM = parent;
			else {
				parent = document.createElement(deco.nodeName);
				parent.pmIsDeco = true;
				parent.appendChild(curDOM);
				prev = noDeco[0];
				curDOM = parent;
			}
		}
		patchAttributes(curDOM, prev || noDeco[0], deco);
	}
	return curDOM;
}
function patchAttributes(dom, prev, cur) {
	for (let name in prev) if (name != "class" && name != "style" && name != "nodeName" && !(name in cur)) dom.removeAttribute(name);
	for (let name in cur) if (name != "class" && name != "style" && name != "nodeName" && cur[name] != prev[name]) dom.setAttribute(name, cur[name]);
	if (prev.class != cur.class) {
		let prevList = prev.class ? prev.class.split(" ").filter(Boolean) : [];
		let curList = cur.class ? cur.class.split(" ").filter(Boolean) : [];
		for (let i = 0; i < prevList.length; i++) if (curList.indexOf(prevList[i]) == -1) dom.classList.remove(prevList[i]);
		for (let i = 0; i < curList.length; i++) if (prevList.indexOf(curList[i]) == -1) dom.classList.add(curList[i]);
		if (dom.classList.length == 0) dom.removeAttribute("class");
	}
	if (prev.style != cur.style) {
		if (prev.style) {
			let prop = /\s*([\w\-\xa1-\uffff]+)\s*:(?:"(?:\\.|[^"])*"|'(?:\\.|[^'])*'|\(.*?\)|[^;])*/g, m;
			while (m = prop.exec(prev.style)) dom.style.removeProperty(m[1]);
		}
		if (cur.style) dom.style.cssText += cur.style;
	}
}
function applyOuterDeco(dom, deco, node) {
	return patchOuterDeco(dom, dom, noDeco, computeOuterDeco(deco, node, dom.nodeType != 1));
}
function sameOuterDeco(a, b) {
	if (a.length != b.length) return false;
	for (let i = 0; i < a.length; i++) if (!a[i].type.eq(b[i].type)) return false;
	return true;
}
function rm(dom) {
	let next = dom.nextSibling;
	dom.parentNode.removeChild(dom);
	return next;
}
var ViewTreeUpdater = class {
	constructor(top, lock, view) {
		this.lock = lock;
		this.view = view;
		this.index = 0;
		this.stack = [];
		this.changed = false;
		this.top = top;
		this.preMatch = preMatch(top.node.content, top);
	}
	destroyBetween(start, end) {
		if (start == end) return;
		for (let i = start; i < end; i++) this.top.children[i].destroy();
		this.top.children.splice(start, end - start);
		this.changed = true;
	}
	destroyRest() {
		this.destroyBetween(this.index, this.top.children.length);
	}
	syncToMarks(marks, inline, view, parentIndex) {
		let keep = 0, depth = this.stack.length >> 1;
		let maxKeep = Math.min(depth, marks.length);
		while (keep < maxKeep && (keep == depth - 1 ? this.top : this.stack[keep + 1 << 1]).matchesMark(marks[keep]) && marks[keep].type.spec.spanning !== false) keep++;
		while (keep < depth) {
			this.destroyRest();
			this.top.dirty = NOT_DIRTY;
			this.index = this.stack.pop();
			this.top = this.stack.pop();
			depth--;
		}
		while (depth < marks.length) {
			this.stack.push(this.top, this.index + 1);
			let found = -1, scanTo = this.top.children.length;
			if (parentIndex < this.preMatch.index) scanTo = Math.min(this.index + 3, scanTo);
			for (let i = this.index; i < scanTo; i++) {
				let next = this.top.children[i];
				if (next.matchesMark(marks[depth]) && !this.isLocked(next.dom)) {
					found = i;
					break;
				}
			}
			if (found > -1) {
				if (found > this.index) {
					this.changed = true;
					this.destroyBetween(this.index, found);
				}
				this.top = this.top.children[this.index];
			} else {
				let markDesc = MarkViewDesc.create(this.top, marks[depth], inline, view);
				this.top.children.splice(this.index, 0, markDesc);
				this.top = markDesc;
				this.changed = true;
			}
			this.index = 0;
			depth++;
		}
	}
	findNodeMatch(node, outerDeco, innerDeco, index) {
		let found = -1, targetDesc;
		if (index >= this.preMatch.index && (targetDesc = this.preMatch.matches[index - this.preMatch.index]).parent == this.top && targetDesc.matchesNode(node, outerDeco, innerDeco)) found = this.top.children.indexOf(targetDesc, this.index);
		else for (let i = this.index, e = Math.min(this.top.children.length, i + 5); i < e; i++) {
			let child = this.top.children[i];
			if (child.matchesNode(node, outerDeco, innerDeco) && !this.preMatch.matched.has(child)) {
				found = i;
				break;
			}
		}
		if (found < 0) return false;
		this.destroyBetween(this.index, found);
		this.index++;
		return true;
	}
	updateNodeAt(node, outerDeco, innerDeco, index, view) {
		let child = this.top.children[index];
		if (child.dirty == NODE_DIRTY && child.dom == child.contentDOM) child.dirty = CONTENT_DIRTY;
		if (!child.update(node, outerDeco, innerDeco, view)) return false;
		this.destroyBetween(this.index, index);
		this.index++;
		return true;
	}
	findIndexWithChild(domNode) {
		for (;;) {
			let parent = domNode.parentNode;
			if (!parent) return -1;
			if (parent == this.top.contentDOM) {
				let desc = domNode.pmViewDesc;
				if (desc) {
					for (let i = this.index; i < this.top.children.length; i++) if (this.top.children[i] == desc) return i;
				}
				return -1;
			}
			domNode = parent;
		}
	}
	updateNextNode(node, outerDeco, innerDeco, view, index, pos) {
		for (let i = this.index; i < this.top.children.length; i++) {
			let next = this.top.children[i];
			if (next instanceof NodeViewDesc) {
				let preMatch = this.preMatch.matched.get(next);
				if (preMatch != null && preMatch != index) return false;
				let nextDOM = next.dom, updated;
				let locked = this.isLocked(nextDOM) && !(node.isText && next.node && next.node.isText && next.nodeDOM.nodeValue == node.text && next.dirty != NODE_DIRTY && sameOuterDeco(outerDeco, next.outerDeco));
				if (!locked && next.update(node, outerDeco, innerDeco, view)) {
					this.destroyBetween(this.index, i);
					if (next.dom != nextDOM) this.changed = true;
					this.index++;
					return true;
				} else if (!locked && (updated = this.recreateWrapper(next, node, outerDeco, innerDeco, view, pos))) {
					this.destroyBetween(this.index, i);
					this.top.children[this.index] = updated;
					if (updated.contentDOM) {
						updated.dirty = CONTENT_DIRTY;
						updated.updateChildren(view, pos + 1);
						updated.dirty = NOT_DIRTY;
					}
					this.changed = true;
					this.index++;
					return true;
				}
				break;
			}
		}
		return false;
	}
	recreateWrapper(next, node, outerDeco, innerDeco, view, pos) {
		if (next.dirty || node.isAtom || !next.children.length || !next.node.content.eq(node.content) || !sameOuterDeco(outerDeco, next.outerDeco) || !innerDeco.eq(next.innerDeco)) return null;
		let wrapper = NodeViewDesc.create(this.top, node, outerDeco, innerDeco, view, pos);
		if (wrapper.contentDOM) {
			wrapper.children = next.children;
			next.children = [];
			for (let ch of wrapper.children) ch.parent = wrapper;
		}
		next.destroy();
		return wrapper;
	}
	addNode(node, outerDeco, innerDeco, view, pos) {
		let desc = NodeViewDesc.create(this.top, node, outerDeco, innerDeco, view, pos);
		if (desc.contentDOM) desc.updateChildren(view, pos + 1);
		this.top.children.splice(this.index++, 0, desc);
		this.changed = true;
	}
	placeWidget(widget, view, pos) {
		let next = this.index < this.top.children.length ? this.top.children[this.index] : null;
		if (next && next.matchesWidget(widget) && (widget == next.widget || !next.widget.type.toDOM.parentNode)) this.index++;
		else {
			let desc = new WidgetViewDesc(this.top, widget, view, pos);
			this.top.children.splice(this.index++, 0, desc);
			this.changed = true;
		}
	}
	addTextblockHacks() {
		let lastChild = this.top.children[this.index - 1], parent = this.top;
		while (lastChild instanceof MarkViewDesc) {
			parent = lastChild;
			lastChild = parent.children[parent.children.length - 1];
		}
		if (!lastChild || !(lastChild instanceof TextViewDesc) || /\n$/.test(lastChild.node.text) || this.view.requiresGeckoHackNode && /\s$/.test(lastChild.node.text)) {
			if ((safari || chrome) && lastChild && lastChild.dom.contentEditable == "false") this.addHackNode("IMG", parent);
			this.addHackNode("BR", this.top);
		}
	}
	addHackNode(nodeName, parent) {
		if (parent == this.top && this.index < parent.children.length && parent.children[this.index].matchesHack(nodeName)) this.index++;
		else {
			let dom = document.createElement(nodeName);
			if (nodeName == "IMG") {
				dom.className = "ProseMirror-separator";
				dom.alt = "";
			}
			if (nodeName == "BR") dom.className = "ProseMirror-trailingBreak";
			let hack = new TrailingHackViewDesc(this.top, [], dom, null);
			if (parent != this.top) parent.children.push(hack);
			else parent.children.splice(this.index++, 0, hack);
			this.changed = true;
		}
	}
	isLocked(node) {
		return this.lock && (node == this.lock || node.nodeType == 1 && node.contains(this.lock.parentNode));
	}
};
function preMatch(frag, parentDesc) {
	let curDesc = parentDesc, descI = curDesc.children.length;
	let fI = frag.childCount, matched = /* @__PURE__ */ new Map(), matches = [];
	outer: while (fI > 0) {
		let desc;
		for (;;) if (descI) {
			let next = curDesc.children[descI - 1];
			if (next instanceof MarkViewDesc) {
				curDesc = next;
				descI = next.children.length;
			} else {
				desc = next;
				descI--;
				break;
			}
		} else if (curDesc == parentDesc) break outer;
		else {
			descI = curDesc.parent.children.indexOf(curDesc);
			curDesc = curDesc.parent;
		}
		let node = desc.node;
		if (!node) continue;
		if (node != frag.child(fI - 1)) break;
		--fI;
		matched.set(desc, fI);
		matches.push(desc);
	}
	return {
		index: fI,
		matched,
		matches: matches.reverse()
	};
}
function compareSide(a, b) {
	return a.type.side - b.type.side;
}
function iterDeco(parent, deco, onWidget, onNode) {
	let locals = deco.locals(parent), offset = 0;
	if (locals.length == 0) {
		for (let i = 0; i < parent.childCount; i++) {
			let child = parent.child(i);
			onNode(child, locals, deco.forChild(offset, child), i);
			offset += child.nodeSize;
		}
		return;
	}
	let decoIndex = 0, active = [], restNode = null;
	for (let parentIndex = 0;;) {
		let widget, widgets;
		while (decoIndex < locals.length && locals[decoIndex].to == offset) {
			let next = locals[decoIndex++];
			if (next.widget) if (!widget) widget = next;
			else (widgets || (widgets = [widget])).push(next);
		}
		if (widget) if (widgets) {
			widgets.sort(compareSide);
			for (let i = 0; i < widgets.length; i++) onWidget(widgets[i], parentIndex, !!restNode);
		} else onWidget(widget, parentIndex, !!restNode);
		let child, index;
		if (restNode) {
			index = -1;
			child = restNode;
			restNode = null;
		} else if (parentIndex < parent.childCount) {
			index = parentIndex;
			child = parent.child(parentIndex++);
		} else break;
		for (let i = 0; i < active.length; i++) if (active[i].to <= offset) active.splice(i--, 1);
		while (decoIndex < locals.length && locals[decoIndex].from <= offset && locals[decoIndex].to > offset) active.push(locals[decoIndex++]);
		let end = offset + child.nodeSize;
		if (child.isText) {
			let cutAt = end;
			if (decoIndex < locals.length && locals[decoIndex].from < cutAt) cutAt = locals[decoIndex].from;
			for (let i = 0; i < active.length; i++) if (active[i].to < cutAt) cutAt = active[i].to;
			if (cutAt < end) {
				restNode = child.cut(cutAt - offset);
				child = child.cut(0, cutAt - offset);
				end = cutAt;
				index = -1;
			}
		} else while (decoIndex < locals.length && locals[decoIndex].to < end) decoIndex++;
		let outerDeco = child.isInline && !child.isLeaf ? active.filter((d) => !d.inline) : active.slice();
		onNode(child, outerDeco, deco.forChild(offset, child), index);
		offset = end;
	}
}
function iosHacks(dom) {
	if (dom.nodeName == "UL" || dom.nodeName == "OL") {
		let oldCSS = dom.style.cssText;
		dom.style.cssText = oldCSS + "; list-style: square !important";
		window.getComputedStyle(dom).listStyle;
		dom.style.cssText = oldCSS;
	}
}
function findTextInFragment(frag, text, from, to) {
	for (let i = 0, pos = 0; i < frag.childCount && pos <= to;) {
		let child = frag.child(i++), childStart = pos;
		pos += child.nodeSize;
		if (!child.isText) continue;
		let str = child.text;
		while (i < frag.childCount) {
			let next = frag.child(i++);
			pos += next.nodeSize;
			if (!next.isText) break;
			str += next.text;
		}
		if (pos >= from) {
			if (pos >= to && str.slice(to - text.length - childStart, to - childStart) == text) return to - text.length;
			let found = childStart < to ? str.lastIndexOf(text, to - childStart - 1) : -1;
			if (found >= 0 && found + text.length + childStart >= from) return childStart + found;
			if (from == to && str.length >= to + text.length - childStart && str.slice(to - childStart, to - childStart + text.length) == text) return to;
		}
	}
	return -1;
}
function replaceNodes(nodes, from, to, view, replacement) {
	let result = [];
	for (let i = 0, off = 0; i < nodes.length; i++) {
		let child = nodes[i], start = off, end = off += child.size;
		if (start >= to || end <= from) result.push(child);
		else {
			if (start < from) result.push(child.slice(0, from - start, view));
			if (replacement) {
				result.push(replacement);
				replacement = void 0;
			}
			if (end > to) result.push(child.slice(to - start, child.size, view));
		}
	}
	return result;
}
function selectionFromDOM(view, origin = null) {
	let domSel = view.domSelectionRange(), doc = view.state.doc;
	if (!domSel.focusNode) return null;
	let nearestDesc = view.docView.nearestDesc(domSel.focusNode), inWidget = nearestDesc && nearestDesc.size == 0;
	let head = view.docView.posFromDOM(domSel.focusNode, domSel.focusOffset, 1);
	if (head < 0) return null;
	let $head = doc.resolve(head), anchor, selection;
	if (selectionCollapsed(domSel)) {
		anchor = head;
		while (nearestDesc && !nearestDesc.node) nearestDesc = nearestDesc.parent;
		let nearestDescNode = nearestDesc.node;
		if (nearestDesc && nearestDescNode.isAtom && NodeSelection.isSelectable(nearestDescNode) && nearestDesc.parent && !(nearestDescNode.isInline && isOnEdge(domSel.focusNode, domSel.focusOffset, nearestDesc.dom))) {
			let pos = nearestDesc.posBefore;
			selection = new NodeSelection(head == pos ? $head : doc.resolve(pos));
		}
	} else {
		if (domSel instanceof view.dom.ownerDocument.defaultView.Selection && domSel.rangeCount > 1) {
			let min = head, max = head;
			for (let i = 0; i < domSel.rangeCount; i++) {
				let range = domSel.getRangeAt(i);
				min = Math.min(min, view.docView.posFromDOM(range.startContainer, range.startOffset, 1));
				max = Math.max(max, view.docView.posFromDOM(range.endContainer, range.endOffset, -1));
			}
			if (min < 0) return null;
			[anchor, head] = max == view.state.selection.anchor ? [max, min] : [min, max];
			$head = doc.resolve(head);
		} else anchor = view.docView.posFromDOM(domSel.anchorNode, domSel.anchorOffset, 1);
		if (anchor < 0) return null;
	}
	let $anchor = doc.resolve(anchor);
	if (!selection) {
		let bias = origin == "pointer" || view.state.selection.head < $head.pos && !inWidget ? 1 : -1;
		selection = selectionBetween(view, $anchor, $head, bias);
	}
	return selection;
}
function editorOwnsSelection(view) {
	return view.editable ? view.hasFocus() : hasSelection(view) && document.activeElement && document.activeElement.contains(view.dom);
}
function selectionToDOM(view, force = false) {
	let sel = view.state.selection;
	syncNodeSelection(view, sel);
	if (!editorOwnsSelection(view)) return;
	if (!force && view.input.mouseDown && view.input.mouseDown.allowDefault && chrome) {
		let domSel = view.domSelectionRange(), curSel = view.domObserver.currentSelection;
		if (domSel.anchorNode && curSel.anchorNode && isEquivalentPosition(domSel.anchorNode, domSel.anchorOffset, curSel.anchorNode, curSel.anchorOffset)) {
			view.input.mouseDown.delayedSelectionSync = true;
			view.domObserver.setCurSelection();
			return;
		}
	}
	view.domObserver.disconnectSelection();
	if (view.cursorWrapper) selectCursorWrapper(view);
	else {
		let { anchor, head } = sel, resetEditableFrom, resetEditableTo;
		if (brokenSelectBetweenUneditable && !(sel instanceof TextSelection)) {
			if (!sel.$from.parent.inlineContent) resetEditableFrom = temporarilyEditableNear(view, sel.from);
			if (!sel.empty && !sel.$from.parent.inlineContent) resetEditableTo = temporarilyEditableNear(view, sel.to);
		}
		view.docView.setSelection(anchor, head, view, force);
		if (brokenSelectBetweenUneditable) {
			if (resetEditableFrom) resetEditable(resetEditableFrom);
			if (resetEditableTo) resetEditable(resetEditableTo);
		}
		if (sel.visible) view.dom.classList.remove("ProseMirror-hideselection");
		else {
			view.dom.classList.add("ProseMirror-hideselection");
			if ("onselectionchange" in document) removeClassOnSelectionChange(view);
		}
	}
	view.domObserver.setCurSelection();
	view.domObserver.connectSelection();
}
var brokenSelectBetweenUneditable = safari || chrome && chrome_version < 63;
function temporarilyEditableNear(view, pos) {
	let { node, offset } = view.docView.domFromPos(pos, 0);
	let after = offset < node.childNodes.length ? node.childNodes[offset] : null;
	let before = offset ? node.childNodes[offset - 1] : null;
	if (safari && after && after.contentEditable == "false") return setEditable(after);
	if ((!after || after.contentEditable == "false") && (!before || before.contentEditable == "false")) {
		if (after) return setEditable(after);
		else if (before) return setEditable(before);
	}
}
function setEditable(element) {
	element.contentEditable = "true";
	if (safari && element.draggable) {
		element.draggable = false;
		element.wasDraggable = true;
	}
	return element;
}
function resetEditable(element) {
	element.contentEditable = "false";
	if (element.wasDraggable) {
		element.draggable = true;
		element.wasDraggable = null;
	}
}
function removeClassOnSelectionChange(view) {
	let doc = view.dom.ownerDocument;
	doc.removeEventListener("selectionchange", view.input.hideSelectionGuard);
	let domSel = view.domSelectionRange();
	let node = domSel.anchorNode, offset = domSel.anchorOffset;
	doc.addEventListener("selectionchange", view.input.hideSelectionGuard = () => {
		if (domSel.anchorNode != node || domSel.anchorOffset != offset) {
			doc.removeEventListener("selectionchange", view.input.hideSelectionGuard);
			setTimeout(() => {
				if (!editorOwnsSelection(view) || view.state.selection.visible) view.dom.classList.remove("ProseMirror-hideselection");
			}, 20);
		}
	});
}
function selectCursorWrapper(view) {
	let domSel = view.domSelection();
	if (!domSel) return;
	let node = view.cursorWrapper.dom, img = node.nodeName == "IMG";
	if (img) domSel.collapse(node.parentNode, domIndex(node) + 1);
	else domSel.collapse(node, 0);
	if (!img && !view.state.selection.visible && ie && ie_version <= 11) {
		node.disabled = true;
		node.disabled = false;
	}
}
function syncNodeSelection(view, sel) {
	if (sel instanceof NodeSelection) {
		let desc = view.docView.descAt(sel.from);
		if (desc != view.lastSelectedViewDesc) {
			clearNodeSelection(view);
			if (desc) desc.selectNode();
			view.lastSelectedViewDesc = desc;
		}
	} else clearNodeSelection(view);
}
function clearNodeSelection(view) {
	if (view.lastSelectedViewDesc) {
		if (view.lastSelectedViewDesc.parent) view.lastSelectedViewDesc.deselectNode();
		view.lastSelectedViewDesc = void 0;
	}
}
function selectionBetween(view, $anchor, $head, bias) {
	return view.someProp("createSelectionBetween", (f) => f(view, $anchor, $head)) || TextSelection.between($anchor, $head, bias);
}
function hasFocusAndSelection(view) {
	if (view.editable && !view.hasFocus()) return false;
	return hasSelection(view);
}
function hasSelection(view) {
	let sel = view.domSelectionRange();
	if (!sel.anchorNode) return false;
	try {
		return view.dom.contains(sel.anchorNode.nodeType == 3 ? sel.anchorNode.parentNode : sel.anchorNode) && (view.editable || view.dom.contains(sel.focusNode.nodeType == 3 ? sel.focusNode.parentNode : sel.focusNode));
	} catch (_) {
		return false;
	}
}
function anchorInRightPlace(view) {
	let anchorDOM = view.docView.domFromPos(view.state.selection.anchor, 0);
	let domSel = view.domSelectionRange();
	return isEquivalentPosition(anchorDOM.node, anchorDOM.offset, domSel.anchorNode, domSel.anchorOffset);
}
function moveSelectionBlock(state, dir) {
	let { $anchor, $head } = state.selection;
	let $side = dir > 0 ? $anchor.max($head) : $anchor.min($head);
	let $start = !$side.parent.inlineContent ? $side : $side.depth ? state.doc.resolve(dir > 0 ? $side.after() : $side.before()) : null;
	return $start && Selection.findFrom($start, dir);
}
function apply(view, sel) {
	view.dispatch(view.state.tr.setSelection(sel).scrollIntoView());
	return true;
}
function selectHorizontally(view, dir, mods) {
	let sel = view.state.selection;
	if (sel instanceof TextSelection) {
		if (mods.indexOf("s") > -1) {
			let { $head } = sel, node = $head.textOffset ? null : dir < 0 ? $head.nodeBefore : $head.nodeAfter;
			if (!node || node.isText || !node.isLeaf) return false;
			let $newHead = view.state.doc.resolve($head.pos + node.nodeSize * (dir < 0 ? -1 : 1));
			return apply(view, new TextSelection(sel.$anchor, $newHead));
		} else if (!sel.empty) return false;
		else if (view.endOfTextblock(dir > 0 ? "forward" : "backward")) {
			let next = moveSelectionBlock(view.state, dir);
			if (next && next instanceof NodeSelection) return apply(view, next);
			return false;
		} else if (!(mac && mods.indexOf("m") > -1)) {
			let $head = sel.$head, node = $head.textOffset ? null : dir < 0 ? $head.nodeBefore : $head.nodeAfter, desc;
			if (!node || node.isText) return false;
			let nodePos = dir < 0 ? $head.pos - node.nodeSize : $head.pos;
			if (!(node.isAtom || (desc = view.docView.descAt(nodePos)) && !desc.contentDOM)) return false;
			if (NodeSelection.isSelectable(node)) return apply(view, new NodeSelection(dir < 0 ? view.state.doc.resolve($head.pos - node.nodeSize) : $head));
			else if (webkit) return apply(view, new TextSelection(view.state.doc.resolve(dir < 0 ? nodePos : nodePos + node.nodeSize)));
			else return false;
		}
	} else if (sel instanceof NodeSelection && sel.node.isInline) return apply(view, new TextSelection(dir > 0 ? sel.$to : sel.$from));
	else {
		let next = moveSelectionBlock(view.state, dir);
		if (next) return apply(view, next);
		return false;
	}
}
function nodeLen(node) {
	return node.nodeType == 3 ? node.nodeValue.length : node.childNodes.length;
}
function isIgnorable(dom, dir) {
	let desc = dom.pmViewDesc;
	return desc && desc.size == 0 && (dir < 0 || dom.nextSibling || dom.nodeName != "BR");
}
function skipIgnoredNodes(view, dir) {
	return dir < 0 ? skipIgnoredNodesBefore(view) : skipIgnoredNodesAfter(view);
}
function skipIgnoredNodesBefore(view) {
	let sel = view.domSelectionRange();
	let node = sel.focusNode, offset = sel.focusOffset;
	if (!node) return;
	let moveNode, moveOffset, force = false;
	if (gecko && node.nodeType == 1 && offset < nodeLen(node) && isIgnorable(node.childNodes[offset], -1)) force = true;
	for (;;) if (offset > 0) if (node.nodeType != 1) break;
	else {
		let before = node.childNodes[offset - 1];
		if (isIgnorable(before, -1)) {
			moveNode = node;
			moveOffset = --offset;
		} else if (before.nodeType == 3) {
			node = before;
			offset = node.nodeValue.length;
		} else break;
	}
	else if (isBlockNode(node)) break;
	else {
		let prev = node.previousSibling;
		while (prev && isIgnorable(prev, -1)) {
			moveNode = node.parentNode;
			moveOffset = domIndex(prev);
			prev = prev.previousSibling;
		}
		if (!prev) {
			node = node.parentNode;
			if (node == view.dom) break;
			offset = 0;
		} else {
			node = prev;
			offset = nodeLen(node);
		}
	}
	if (force) setSelFocus(view, node, offset);
	else if (moveNode) setSelFocus(view, moveNode, moveOffset);
}
function skipIgnoredNodesAfter(view) {
	let sel = view.domSelectionRange();
	let node = sel.focusNode, offset = sel.focusOffset;
	if (!node) return;
	let len = nodeLen(node);
	let moveNode, moveOffset;
	for (;;) if (offset < len) {
		if (node.nodeType != 1) break;
		let after = node.childNodes[offset];
		if (isIgnorable(after, 1)) {
			moveNode = node;
			moveOffset = ++offset;
		} else break;
	} else if (isBlockNode(node)) break;
	else {
		let next = node.nextSibling;
		while (next && isIgnorable(next, 1)) {
			moveNode = next.parentNode;
			moveOffset = domIndex(next) + 1;
			next = next.nextSibling;
		}
		if (!next) {
			node = node.parentNode;
			if (node == view.dom) break;
			offset = len = 0;
		} else {
			node = next;
			offset = 0;
			len = nodeLen(node);
		}
	}
	if (moveNode) setSelFocus(view, moveNode, moveOffset);
}
function isBlockNode(dom) {
	let desc = dom.pmViewDesc;
	return desc && desc.node && desc.node.isBlock;
}
function textNodeAfter(node, offset) {
	while (node && offset == node.childNodes.length && !hasBlockDesc(node)) {
		offset = domIndex(node) + 1;
		node = node.parentNode;
	}
	while (node && offset < node.childNodes.length) {
		let next = node.childNodes[offset];
		if (next.nodeType == 3) return next;
		if (next.nodeType == 1 && next.contentEditable == "false") break;
		node = next;
		offset = 0;
	}
}
function textNodeBefore(node, offset) {
	while (node && !offset && !hasBlockDesc(node)) {
		offset = domIndex(node);
		node = node.parentNode;
	}
	while (node && offset) {
		let next = node.childNodes[offset - 1];
		if (next.nodeType == 3) return next;
		if (next.nodeType == 1 && next.contentEditable == "false") break;
		node = next;
		offset = node.childNodes.length;
	}
}
function setSelFocus(view, node, offset) {
	if (node.nodeType != 3) {
		let before, after;
		if (after = textNodeAfter(node, offset)) {
			node = after;
			offset = 0;
		} else if (before = textNodeBefore(node, offset)) {
			node = before;
			offset = before.nodeValue.length;
		}
	}
	let sel = view.domSelection();
	if (!sel) return;
	if (selectionCollapsed(sel)) {
		let range = document.createRange();
		range.setEnd(node, offset);
		range.setStart(node, offset);
		sel.removeAllRanges();
		sel.addRange(range);
	} else if (sel.extend) sel.extend(node, offset);
	view.domObserver.setCurSelection();
	let { state } = view;
	setTimeout(() => {
		if (view.state == state) selectionToDOM(view);
	}, 50);
}
function findDirection(view, pos) {
	let $pos = view.state.doc.resolve(pos);
	if (!(chrome || windows) && $pos.parent.inlineContent) {
		let coords = view.coordsAtPos(pos);
		if (pos > $pos.start()) {
			let before = view.coordsAtPos(pos - 1);
			let mid = (before.top + before.bottom) / 2;
			if (mid > coords.top && mid < coords.bottom && Math.abs(before.left - coords.left) > 1) return before.left < coords.left ? "ltr" : "rtl";
		}
		if (pos < $pos.end()) {
			let after = view.coordsAtPos(pos + 1);
			let mid = (after.top + after.bottom) / 2;
			if (mid > coords.top && mid < coords.bottom && Math.abs(after.left - coords.left) > 1) return after.left > coords.left ? "ltr" : "rtl";
		}
	}
	return getComputedStyle(view.dom).direction == "rtl" ? "rtl" : "ltr";
}
function selectVertically(view, dir, mods) {
	let sel = view.state.selection;
	if (sel instanceof TextSelection && !sel.empty || mods.indexOf("s") > -1) return false;
	if (mac && mods.indexOf("m") > -1) return false;
	let { $from, $to } = sel;
	if (!$from.parent.inlineContent || view.endOfTextblock(dir < 0 ? "up" : "down")) {
		let next = moveSelectionBlock(view.state, dir);
		if (next && next instanceof NodeSelection) return apply(view, next);
	}
	if (!$from.parent.inlineContent) {
		let side = dir < 0 ? $from : $to;
		let beyond = sel instanceof AllSelection ? Selection.near(side, dir) : Selection.findFrom(side, dir);
		return beyond ? apply(view, beyond) : false;
	}
	return false;
}
function stopNativeHorizontalDelete(view, dir) {
	if (!(view.state.selection instanceof TextSelection)) return true;
	let { $head, $anchor, empty } = view.state.selection;
	if (!$head.sameParent($anchor)) return true;
	if (!empty) return false;
	if (view.endOfTextblock(dir > 0 ? "forward" : "backward")) return true;
	let nextNode = !$head.textOffset && (dir < 0 ? $head.nodeBefore : $head.nodeAfter);
	if (nextNode && !nextNode.isText) {
		let tr = view.state.tr;
		if (dir < 0) tr.delete($head.pos - nextNode.nodeSize, $head.pos);
		else tr.delete($head.pos, $head.pos + nextNode.nodeSize);
		view.dispatch(tr);
		return true;
	}
	return false;
}
function switchEditable(view, node, state) {
	view.domObserver.stop();
	node.contentEditable = state;
	view.domObserver.start();
}
function safariDownArrowBug(view) {
	if (!safari || view.state.selection.$head.parentOffset > 0) return false;
	let { focusNode, focusOffset } = view.domSelectionRange();
	if (focusNode && focusNode.nodeType == 1 && focusOffset == 0 && focusNode.firstChild && focusNode.firstChild.contentEditable == "false") {
		let child = focusNode.firstChild;
		switchEditable(view, child, "true");
		setTimeout(() => switchEditable(view, child, "false"), 20);
	}
	return false;
}
function getMods(event) {
	let result = "";
	if (event.ctrlKey) result += "c";
	if (event.metaKey) result += "m";
	if (event.altKey) result += "a";
	if (event.shiftKey) result += "s";
	return result;
}
function captureKeyDown(view, event) {
	let code = event.keyCode, mods = getMods(event);
	if (code == 8 || mac && code == 72 && mods == "c") return stopNativeHorizontalDelete(view, -1) || skipIgnoredNodes(view, -1);
	else if (code == 46 && !event.shiftKey || mac && code == 68 && mods == "c") return stopNativeHorizontalDelete(view, 1) || skipIgnoredNodes(view, 1);
	else if (code == 13 || code == 27) return true;
	else if (code == 37 || mac && code == 66 && mods == "c") {
		let dir = code == 37 ? findDirection(view, view.state.selection.from) == "ltr" ? -1 : 1 : -1;
		return selectHorizontally(view, dir, mods) || skipIgnoredNodes(view, dir);
	} else if (code == 39 || mac && code == 70 && mods == "c") {
		let dir = code == 39 ? findDirection(view, view.state.selection.from) == "ltr" ? 1 : -1 : 1;
		return selectHorizontally(view, dir, mods) || skipIgnoredNodes(view, dir);
	} else if (code == 38 || mac && code == 80 && mods == "c") return selectVertically(view, -1, mods) || skipIgnoredNodes(view, -1);
	else if (code == 40 || mac && code == 78 && mods == "c") return safariDownArrowBug(view) || selectVertically(view, 1, mods) || skipIgnoredNodes(view, 1);
	else if (mods == (mac ? "m" : "c") && (code == 66 || code == 73 || code == 89 || code == 90)) return true;
	return false;
}
function serializeForClipboard(view, slice) {
	view.someProp("transformCopied", (f) => {
		slice = f(slice, view);
	});
	let context = [], { content, openStart, openEnd } = slice;
	while (openStart > 1 && openEnd > 1 && content.childCount == 1 && content.firstChild.childCount == 1) {
		openStart--;
		openEnd--;
		let node = content.firstChild;
		context.push(node.type.name, node.attrs != node.type.defaultAttrs ? node.attrs : null);
		content = node.content;
	}
	let serializer = view.someProp("clipboardSerializer") || DOMSerializer.fromSchema(view.state.schema);
	let doc = detachedDoc(), wrap = doc.createElement("div");
	wrap.appendChild(serializer.serializeFragment(content, { document: doc }));
	let firstChild = wrap.firstChild, needsWrap, wrappers = 0;
	while (firstChild && firstChild.nodeType == 1 && (needsWrap = wrapMap[firstChild.nodeName.toLowerCase()])) {
		for (let i = needsWrap.length - 1; i >= 0; i--) {
			let wrapper = doc.createElement(needsWrap[i]);
			while (wrap.firstChild) wrapper.appendChild(wrap.firstChild);
			wrap.appendChild(wrapper);
			wrappers++;
		}
		firstChild = wrap.firstChild;
	}
	if (firstChild && firstChild.nodeType == 1) firstChild.setAttribute("data-pm-slice", `${openStart} ${openEnd}${wrappers ? ` -${wrappers}` : ""} ${JSON.stringify(context)}`);
	return {
		dom: wrap,
		text: view.someProp("clipboardTextSerializer", (f) => f(slice, view)) || slice.content.textBetween(0, slice.content.size, "\n\n"),
		slice
	};
}
function parseFromClipboard(view, text, html, plainText, $context) {
	let inCode = $context.parent.type.spec.code;
	let dom, slice;
	if (!html && !text) return null;
	let asText = !!text && (plainText || inCode || !html);
	if (asText) {
		view.someProp("transformPastedText", (f) => {
			text = f(text, inCode || plainText, view);
		});
		if (inCode) {
			slice = new Slice(Fragment.from(view.state.schema.text(text.replace(/\r\n?/g, "\n"))), 0, 0);
			view.someProp("transformPasted", (f) => {
				slice = f(slice, view, true);
			});
			return slice;
		}
		let parsed = view.someProp("clipboardTextParser", (f) => f(text, $context, plainText, view));
		if (parsed) slice = parsed;
		else {
			let marks = $context.marks();
			let { schema } = view.state, serializer = DOMSerializer.fromSchema(schema);
			dom = document.createElement("div");
			text.split(/(?:\r\n?|\n)+/).forEach((block) => {
				let p = dom.appendChild(document.createElement("p"));
				if (block) p.appendChild(serializer.serializeNode(schema.text(block, marks)));
			});
		}
	} else {
		view.someProp("transformPastedHTML", (f) => {
			html = f(html, view);
		});
		dom = readHTML(html);
		if (webkit) restoreReplacedSpaces(dom);
	}
	let contextNode = dom && dom.querySelector("[data-pm-slice]");
	let sliceData = contextNode && /^(\d+) (\d+)(?: -(\d+))? (.*)/.exec(contextNode.getAttribute("data-pm-slice") || "");
	if (sliceData && sliceData[3]) for (let i = +sliceData[3]; i > 0; i--) {
		let child = dom.firstChild;
		while (child && child.nodeType != 1) child = child.nextSibling;
		if (!child) break;
		dom = child;
	}
	if (!slice) slice = (view.someProp("clipboardParser") || view.someProp("domParser") || DOMParser.fromSchema(view.state.schema)).parseSlice(dom, {
		preserveWhitespace: !!(asText || sliceData),
		context: $context,
		ruleFromNode(dom) {
			if (dom.nodeName == "BR" && !dom.nextSibling && dom.parentNode && !inlineParents.test(dom.parentNode.nodeName)) return { ignore: true };
			return null;
		}
	});
	if (sliceData) slice = addContext(closeSlice(slice, +sliceData[1], +sliceData[2]), sliceData[4]);
	else {
		slice = Slice.maxOpen(normalizeSiblings(slice.content, $context), true);
		if (slice.openStart || slice.openEnd) {
			let openStart = 0, openEnd = 0;
			for (let node = slice.content.firstChild; openStart < slice.openStart && !node.type.spec.isolating; openStart++, node = node.firstChild);
			for (let node = slice.content.lastChild; openEnd < slice.openEnd && !node.type.spec.isolating; openEnd++, node = node.lastChild);
			slice = closeSlice(slice, openStart, openEnd);
		}
	}
	view.someProp("transformPasted", (f) => {
		slice = f(slice, view, asText);
	});
	return slice;
}
var inlineParents = /^(a|abbr|acronym|b|cite|code|del|em|i|ins|kbd|label|output|q|ruby|s|samp|span|strong|sub|sup|time|u|tt|var)$/i;
function normalizeSiblings(fragment, $context) {
	if (fragment.childCount < 2) return fragment;
	for (let d = $context.depth; d >= 0; d--) {
		let match = $context.node(d).contentMatchAt($context.index(d));
		let lastWrap, result = [];
		fragment.forEach((node) => {
			if (!result) return;
			let wrap = match.findWrapping(node.type), inLast;
			if (!wrap) return result = null;
			if (inLast = result.length && lastWrap.length && addToSibling(wrap, lastWrap, node, result[result.length - 1], 0)) result[result.length - 1] = inLast;
			else {
				if (result.length) result[result.length - 1] = closeRight(result[result.length - 1], lastWrap.length);
				let wrapped = withWrappers(node, wrap);
				result.push(wrapped);
				match = match.matchType(wrapped.type);
				lastWrap = wrap;
			}
		});
		if (result) return Fragment.from(result);
	}
	return fragment;
}
function withWrappers(node, wrap, from = 0) {
	for (let i = wrap.length - 1; i >= from; i--) node = wrap[i].create(null, Fragment.from(node));
	return node;
}
function addToSibling(wrap, lastWrap, node, sibling, depth) {
	if (depth < wrap.length && depth < lastWrap.length && wrap[depth] == lastWrap[depth]) {
		let inner = addToSibling(wrap, lastWrap, node, sibling.lastChild, depth + 1);
		if (inner) return sibling.copy(sibling.content.replaceChild(sibling.childCount - 1, inner));
		if (sibling.contentMatchAt(sibling.childCount).matchType(depth == wrap.length - 1 ? node.type : wrap[depth + 1])) return sibling.copy(sibling.content.append(Fragment.from(withWrappers(node, wrap, depth + 1))));
	}
}
function closeRight(node, depth) {
	if (depth == 0) return node;
	let fragment = node.content.replaceChild(node.childCount - 1, closeRight(node.lastChild, depth - 1));
	let fill = node.contentMatchAt(node.childCount).fillBefore(Fragment.empty, true);
	return node.copy(fragment.append(fill));
}
function closeRange(fragment, side, from, to, depth, openEnd) {
	let node = side < 0 ? fragment.firstChild : fragment.lastChild, inner = node.content;
	if (fragment.childCount > 1) openEnd = 0;
	if (depth < to - 1) inner = closeRange(inner, side, from, to, depth + 1, openEnd);
	if (depth >= from) inner = side < 0 ? node.contentMatchAt(0).fillBefore(inner, openEnd <= depth).append(inner) : inner.append(node.contentMatchAt(node.childCount).fillBefore(Fragment.empty, true));
	return fragment.replaceChild(side < 0 ? 0 : fragment.childCount - 1, node.copy(inner));
}
function closeSlice(slice, openStart, openEnd) {
	if (openStart < slice.openStart) slice = new Slice(closeRange(slice.content, -1, openStart, slice.openStart, 0, slice.openEnd), openStart, slice.openEnd);
	if (openEnd < slice.openEnd) slice = new Slice(closeRange(slice.content, 1, openEnd, slice.openEnd, 0, 0), slice.openStart, openEnd);
	return slice;
}
var wrapMap = {
	thead: ["table"],
	tbody: ["table"],
	tfoot: ["table"],
	caption: ["table"],
	colgroup: ["table"],
	col: ["table", "colgroup"],
	tr: ["table", "tbody"],
	td: [
		"table",
		"tbody",
		"tr"
	],
	th: [
		"table",
		"tbody",
		"tr"
	]
};
var _detachedDoc = null;
function detachedDoc() {
	return _detachedDoc || (_detachedDoc = document.implementation.createHTMLDocument("title"));
}
var _policy = null;
function maybeWrapTrusted(html) {
	let trustedTypes = window.trustedTypes;
	if (!trustedTypes) return html;
	if (!_policy) _policy = trustedTypes.defaultPolicy || trustedTypes.createPolicy("ProseMirrorClipboard", { createHTML: (s) => s });
	return _policy.createHTML(html);
}
function readHTML(html) {
	let metas = /^(\s*<meta [^>]*>)*/.exec(html);
	if (metas) html = html.slice(metas[0].length);
	let elt = detachedDoc().createElement("div");
	let firstTag = /<([a-z][^>\s]+)/i.exec(html), wrap;
	if (wrap = firstTag && wrapMap[firstTag[1].toLowerCase()]) html = wrap.map((n) => "<" + n + ">").join("") + html + wrap.map((n) => "</" + n + ">").reverse().join("");
	elt.innerHTML = maybeWrapTrusted(html);
	if (wrap) for (let i = 0; i < wrap.length; i++) elt = elt.querySelector(wrap[i]) || elt;
	return elt;
}
function restoreReplacedSpaces(dom) {
	let nodes = dom.querySelectorAll(chrome ? "span:not([class]):not([style])" : "span.Apple-converted-space");
	for (let i = 0; i < nodes.length; i++) {
		let node = nodes[i];
		if (node.childNodes.length == 1 && node.textContent == "\xA0" && node.parentNode) node.parentNode.replaceChild(dom.ownerDocument.createTextNode(" "), node);
	}
}
function addContext(slice, context) {
	if (!slice.size) return slice;
	let schema = slice.content.firstChild.type.schema, array;
	try {
		array = JSON.parse(context);
	} catch (e) {
		return slice;
	}
	let { content, openStart, openEnd } = slice;
	for (let i = array.length - 2; i >= 0; i -= 2) {
		let type = schema.nodes[array[i]];
		if (!type || type.hasRequiredAttrs()) break;
		content = Fragment.from(type.create(array[i + 1], content));
		openStart++;
		openEnd++;
	}
	return new Slice(content, openStart, openEnd);
}
var handlers = {};
var editHandlers = {};
var passiveHandlers = {
	touchstart: true,
	touchmove: true
};
var InputState = class {
	constructor() {
		this.shiftKey = false;
		this.mouseDown = null;
		this.lastKeyCode = null;
		this.lastKeyCodeTime = 0;
		this.lastClick = {
			time: 0,
			x: 0,
			y: 0,
			type: "",
			button: 0
		};
		this.lastSelectionOrigin = null;
		this.lastSelectionTime = 0;
		this.lastIOSEnter = 0;
		this.lastIOSEnterFallbackTimeout = -1;
		this.lastFocus = 0;
		this.lastTouch = 0;
		this.lastChromeDelete = 0;
		this.composing = false;
		this.compositionNode = null;
		this.composingTimeout = -1;
		this.compositionNodes = [];
		this.compositionEndedAt = -2e8;
		this.compositionID = 1;
		this.badSafariComposition = false;
		this.compositionPendingChanges = 0;
		this.domChangeCount = 0;
		this.eventHandlers = Object.create(null);
		this.hideSelectionGuard = null;
	}
};
function initInput(view) {
	for (let event in handlers) {
		let handler = handlers[event];
		view.dom.addEventListener(event, view.input.eventHandlers[event] = (event) => {
			if (eventBelongsToView(view, event) && !runCustomHandler(view, event) && (view.editable || !(event.type in editHandlers))) handler(view, event);
		}, passiveHandlers[event] ? { passive: true } : void 0);
	}
	if (safari) view.dom.addEventListener("input", () => null);
	ensureListeners(view);
}
function setSelectionOrigin(view, origin) {
	view.input.lastSelectionOrigin = origin;
	view.input.lastSelectionTime = Date.now();
}
function destroyInput(view) {
	view.domObserver.stop();
	for (let type in view.input.eventHandlers) view.dom.removeEventListener(type, view.input.eventHandlers[type]);
	clearTimeout(view.input.composingTimeout);
	clearTimeout(view.input.lastIOSEnterFallbackTimeout);
}
function ensureListeners(view) {
	view.someProp("handleDOMEvents", (currentHandlers) => {
		for (let type in currentHandlers) if (!view.input.eventHandlers[type]) view.dom.addEventListener(type, view.input.eventHandlers[type] = (event) => runCustomHandler(view, event));
	});
}
function runCustomHandler(view, event) {
	return view.someProp("handleDOMEvents", (handlers) => {
		let handler = handlers[event.type];
		return handler ? handler(view, event) || event.defaultPrevented : false;
	});
}
function eventBelongsToView(view, event) {
	if (!event.bubbles) return true;
	if (event.defaultPrevented) return false;
	for (let node = event.target; node != view.dom; node = node.parentNode) if (!node || node.nodeType == 11 || node.pmViewDesc && node.pmViewDesc.stopEvent(event)) return false;
	return true;
}
function dispatchEvent(view, event) {
	if (!runCustomHandler(view, event) && handlers[event.type] && (view.editable || !(event.type in editHandlers))) handlers[event.type](view, event);
}
editHandlers.keydown = (view, _event) => {
	let event = _event;
	view.input.shiftKey = event.keyCode == 16 || event.shiftKey;
	if (inOrNearComposition(view, event)) return;
	view.input.lastKeyCode = event.keyCode;
	view.input.lastKeyCodeTime = Date.now();
	if (android && chrome && event.keyCode == 13) return;
	if (event.keyCode != 229) view.domObserver.forceFlush();
	if (ios && event.keyCode == 13 && !event.ctrlKey && !event.altKey && !event.metaKey) {
		let now = Date.now();
		view.input.lastIOSEnter = now;
		view.input.lastIOSEnterFallbackTimeout = setTimeout(() => {
			if (view.input.lastIOSEnter == now) {
				view.someProp("handleKeyDown", (f) => f(view, keyEvent(13, "Enter")));
				view.input.lastIOSEnter = 0;
			}
		}, 200);
	} else if (view.someProp("handleKeyDown", (f) => f(view, event)) || captureKeyDown(view, event)) event.preventDefault();
	else setSelectionOrigin(view, "key");
};
editHandlers.keyup = (view, event) => {
	if (event.keyCode == 16) view.input.shiftKey = false;
};
editHandlers.keypress = (view, _event) => {
	let event = _event;
	if (inOrNearComposition(view, event) || !event.charCode || event.ctrlKey && !event.altKey || mac && event.metaKey) return;
	if (view.someProp("handleKeyPress", (f) => f(view, event))) {
		event.preventDefault();
		return;
	}
	let sel = view.state.selection;
	if (!(sel instanceof TextSelection) || !sel.$from.sameParent(sel.$to)) {
		let text = String.fromCharCode(event.charCode);
		let deflt = () => view.state.tr.insertText(text).scrollIntoView();
		if (!/[\r\n]/.test(text) && !view.someProp("handleTextInput", (f) => f(view, sel.$from.pos, sel.$to.pos, text, deflt))) view.dispatch(deflt());
		event.preventDefault();
	}
};
function eventCoords(event) {
	return {
		left: event.clientX,
		top: event.clientY
	};
}
function isNear(event, click) {
	let dx = click.x - event.clientX, dy = click.y - event.clientY;
	return dx * dx + dy * dy < 100;
}
function runHandlerOnContext(view, propName, pos, inside, event) {
	if (inside == -1) return false;
	let $pos = view.state.doc.resolve(inside);
	for (let i = $pos.depth + 1; i > 0; i--) if (view.someProp(propName, (f) => i > $pos.depth ? f(view, pos, $pos.nodeAfter, $pos.before(i), event, true) : f(view, pos, $pos.node(i), $pos.before(i), event, false))) return true;
	return false;
}
function updateSelection(view, selection, origin) {
	if (!view.focused) view.focus();
	if (view.state.selection.eq(selection)) return;
	let tr = view.state.tr.setSelection(selection);
	if (origin == "pointer") tr.setMeta("pointer", true);
	view.dispatch(tr);
}
function selectClickedLeaf(view, inside) {
	if (inside == -1) return false;
	let $pos = view.state.doc.resolve(inside), node = $pos.nodeAfter;
	if (node && node.isAtom && NodeSelection.isSelectable(node)) {
		updateSelection(view, new NodeSelection($pos), "pointer");
		return true;
	}
	return false;
}
function selectClickedNode(view, inside) {
	if (inside == -1) return false;
	let sel = view.state.selection, selectedNode, selectAt;
	if (sel instanceof NodeSelection) selectedNode = sel.node;
	let $pos = view.state.doc.resolve(inside);
	for (let i = $pos.depth + 1; i > 0; i--) {
		let node = i > $pos.depth ? $pos.nodeAfter : $pos.node(i);
		if (NodeSelection.isSelectable(node)) {
			if (selectedNode && sel.$from.depth > 0 && i >= sel.$from.depth && $pos.before(sel.$from.depth + 1) == sel.$from.pos) selectAt = $pos.before(sel.$from.depth);
			else selectAt = $pos.before(i);
			break;
		}
	}
	if (selectAt != null) {
		updateSelection(view, NodeSelection.create(view.state.doc, selectAt), "pointer");
		return true;
	} else return false;
}
function handleSingleClick(view, pos, inside, event, selectNode) {
	return runHandlerOnContext(view, "handleClickOn", pos, inside, event) || view.someProp("handleClick", (f) => f(view, pos, event)) || (selectNode ? selectClickedNode(view, inside) : selectClickedLeaf(view, inside));
}
function handleDoubleClick(view, pos, inside, event) {
	return runHandlerOnContext(view, "handleDoubleClickOn", pos, inside, event) || view.someProp("handleDoubleClick", (f) => f(view, pos, event));
}
function handleTripleClick(view, pos, inside, event) {
	return runHandlerOnContext(view, "handleTripleClickOn", pos, inside, event) || view.someProp("handleTripleClick", (f) => f(view, pos, event)) || defaultTripleClick(view, inside, event);
}
function defaultTripleClick(view, inside, event) {
	if (event.button != 0) return false;
	let doc = view.state.doc;
	if (inside == -1) {
		if (doc.inlineContent) {
			updateSelection(view, TextSelection.create(doc, 0, doc.content.size), "pointer");
			return true;
		}
		return false;
	}
	let $pos = doc.resolve(inside);
	for (let i = $pos.depth + 1; i > 0; i--) {
		let node = i > $pos.depth ? $pos.nodeAfter : $pos.node(i);
		let nodePos = $pos.before(i);
		if (node.inlineContent) updateSelection(view, TextSelection.create(doc, nodePos + 1, nodePos + 1 + node.content.size), "pointer");
		else if (NodeSelection.isSelectable(node)) updateSelection(view, NodeSelection.create(doc, nodePos), "pointer");
		else continue;
		return true;
	}
}
function forceDOMFlush(view) {
	return endComposition(view);
}
var selectNodeModifier = mac ? "metaKey" : "ctrlKey";
handlers.mousedown = (view, _event) => {
	let event = _event;
	view.input.shiftKey = event.shiftKey;
	let flushed = forceDOMFlush(view);
	let now = Date.now(), type = "singleClick";
	if (now - view.input.lastClick.time < 500 && isNear(event, view.input.lastClick) && !event[selectNodeModifier] && view.input.lastClick.button == event.button) {
		if (view.input.lastClick.type == "singleClick") type = "doubleClick";
		else if (view.input.lastClick.type == "doubleClick") type = "tripleClick";
	}
	view.input.lastClick = {
		time: now,
		x: event.clientX,
		y: event.clientY,
		type,
		button: event.button
	};
	let pos = view.posAtCoords(eventCoords(event));
	if (!pos) return;
	if (type == "singleClick") {
		if (view.input.mouseDown) view.input.mouseDown.done();
		view.input.mouseDown = new MouseDown(view, pos, event, !!flushed);
	} else if ((type == "doubleClick" ? handleDoubleClick : handleTripleClick)(view, pos.pos, pos.inside, event)) event.preventDefault();
	else setSelectionOrigin(view, "pointer");
};
var MouseDown = class {
	constructor(view, pos, event, flushed) {
		this.view = view;
		this.pos = pos;
		this.event = event;
		this.flushed = flushed;
		this.delayedSelectionSync = false;
		this.mightDrag = null;
		this.startDoc = view.state.doc;
		this.selectNode = !!event[selectNodeModifier];
		this.allowDefault = event.shiftKey;
		let targetNode, targetPos;
		if (pos.inside > -1) {
			targetNode = view.state.doc.nodeAt(pos.inside);
			targetPos = pos.inside;
		} else {
			let $pos = view.state.doc.resolve(pos.pos);
			targetNode = $pos.parent;
			targetPos = $pos.depth ? $pos.before() : 0;
		}
		const target = flushed ? null : event.target;
		const targetDesc = target ? view.docView.nearestDesc(target, true) : null;
		this.target = targetDesc && targetDesc.nodeDOM.nodeType == 1 ? targetDesc.nodeDOM : null;
		let { selection } = view.state;
		if (event.button == 0 && (targetNode.type.spec.draggable && targetNode.type.spec.selectable !== false || selection instanceof NodeSelection && selection.from <= targetPos && selection.to > targetPos)) this.mightDrag = {
			node: targetNode,
			pos: targetPos,
			addAttr: !!(this.target && !this.target.draggable),
			setUneditable: !!(this.target && gecko && !this.target.hasAttribute("contentEditable"))
		};
		if (this.target && this.mightDrag && (this.mightDrag.addAttr || this.mightDrag.setUneditable)) {
			this.view.domObserver.stop();
			if (this.mightDrag.addAttr) this.target.draggable = true;
			if (this.mightDrag.setUneditable) setTimeout(() => {
				if (this.view.input.mouseDown == this) this.target.setAttribute("contentEditable", "false");
			}, 20);
			this.view.domObserver.start();
		}
		view.root.addEventListener("mouseup", this.up = this.up.bind(this));
		view.root.addEventListener("mousemove", this.move = this.move.bind(this));
		setSelectionOrigin(view, "pointer");
	}
	done() {
		this.view.root.removeEventListener("mouseup", this.up);
		this.view.root.removeEventListener("mousemove", this.move);
		if (this.mightDrag && this.target) {
			this.view.domObserver.stop();
			if (this.mightDrag.addAttr) this.target.removeAttribute("draggable");
			if (this.mightDrag.setUneditable) this.target.removeAttribute("contentEditable");
			this.view.domObserver.start();
		}
		if (this.delayedSelectionSync) setTimeout(() => selectionToDOM(this.view));
		this.view.input.mouseDown = null;
	}
	up(event) {
		this.done();
		if (!this.view.dom.contains(event.target)) return;
		let pos = this.pos;
		if (this.view.state.doc != this.startDoc) pos = this.view.posAtCoords(eventCoords(event));
		this.updateAllowDefault(event);
		if (this.allowDefault || !pos) setSelectionOrigin(this.view, "pointer");
		else if (handleSingleClick(this.view, pos.pos, pos.inside, event, this.selectNode)) event.preventDefault();
		else if (event.button == 0 && (this.flushed || safari && this.mightDrag && !this.mightDrag.node.isAtom || chrome && !this.view.state.selection.visible && Math.min(Math.abs(pos.pos - this.view.state.selection.from), Math.abs(pos.pos - this.view.state.selection.to)) <= 2)) {
			updateSelection(this.view, Selection.near(this.view.state.doc.resolve(pos.pos)), "pointer");
			event.preventDefault();
		} else setSelectionOrigin(this.view, "pointer");
	}
	move(event) {
		this.updateAllowDefault(event);
		setSelectionOrigin(this.view, "pointer");
		if (event.buttons == 0) this.done();
	}
	updateAllowDefault(event) {
		if (!this.allowDefault && (Math.abs(this.event.x - event.clientX) > 4 || Math.abs(this.event.y - event.clientY) > 4)) this.allowDefault = true;
	}
};
handlers.touchstart = (view) => {
	view.input.lastTouch = Date.now();
	forceDOMFlush(view);
	setSelectionOrigin(view, "pointer");
};
handlers.touchmove = (view) => {
	view.input.lastTouch = Date.now();
	setSelectionOrigin(view, "pointer");
};
handlers.contextmenu = (view) => forceDOMFlush(view);
function inOrNearComposition(view, event) {
	if (view.composing) return true;
	if (safari && Math.abs(event.timeStamp - view.input.compositionEndedAt) < 500) {
		view.input.compositionEndedAt = -2e8;
		return true;
	}
	return false;
}
var timeoutComposition = android ? 5e3 : -1;
editHandlers.compositionstart = editHandlers.compositionupdate = (view) => {
	if (!view.composing) {
		view.domObserver.flush();
		let { state } = view, $pos = state.selection.$to;
		if (state.selection instanceof TextSelection && (state.storedMarks || !$pos.textOffset && $pos.parentOffset && $pos.nodeBefore.marks.some((m) => m.type.spec.inclusive === false) || chrome && windows && selectionBeforeUneditable(view))) {
			view.markCursor = view.state.storedMarks || $pos.marks();
			endComposition(view, true);
			view.markCursor = null;
		} else {
			endComposition(view, !state.selection.empty);
			if (gecko && state.selection.empty && $pos.parentOffset && !$pos.textOffset && $pos.nodeBefore.marks.length) {
				let sel = view.domSelectionRange();
				for (let node = sel.focusNode, offset = sel.focusOffset; node && node.nodeType == 1 && offset != 0;) {
					let before = offset < 0 ? node.lastChild : node.childNodes[offset - 1];
					if (!before) break;
					if (before.nodeType == 3) {
						let sel = view.domSelection();
						if (sel) sel.collapse(before, before.nodeValue.length);
						break;
					} else {
						node = before;
						offset = -1;
					}
				}
			}
		}
		view.input.composing = true;
	}
	scheduleComposeEnd(view, timeoutComposition);
};
function selectionBeforeUneditable(view) {
	let { focusNode, focusOffset } = view.domSelectionRange();
	if (!focusNode || focusNode.nodeType != 1 || focusOffset >= focusNode.childNodes.length) return false;
	let next = focusNode.childNodes[focusOffset];
	return next.nodeType == 1 && next.contentEditable == "false";
}
editHandlers.compositionend = (view, event) => {
	if (view.composing) {
		view.input.composing = false;
		view.input.compositionEndedAt = event.timeStamp;
		view.input.compositionPendingChanges = view.domObserver.pendingRecords().length ? view.input.compositionID : 0;
		view.input.compositionNode = null;
		if (view.input.badSafariComposition) view.domObserver.forceFlush();
		else if (view.input.compositionPendingChanges) Promise.resolve().then(() => view.domObserver.flush());
		view.input.compositionID++;
		scheduleComposeEnd(view, 20);
	}
};
function scheduleComposeEnd(view, delay) {
	clearTimeout(view.input.composingTimeout);
	if (delay > -1) view.input.composingTimeout = setTimeout(() => endComposition(view), delay);
}
function clearComposition(view) {
	if (view.composing) {
		view.input.composing = false;
		view.input.compositionEndedAt = timestampFromCustomEvent();
	}
	while (view.input.compositionNodes.length > 0) view.input.compositionNodes.pop().markParentsDirty();
}
function findCompositionNode(view) {
	let sel = view.domSelectionRange();
	if (!sel.focusNode) return null;
	let textBefore = textNodeBefore$1(sel.focusNode, sel.focusOffset);
	let textAfter = textNodeAfter$1(sel.focusNode, sel.focusOffset);
	if (textBefore && textAfter && textBefore != textAfter) {
		let descAfter = textAfter.pmViewDesc, lastChanged = view.domObserver.lastChangedTextNode;
		if (textBefore == lastChanged || textAfter == lastChanged) return lastChanged;
		if (!descAfter || !descAfter.isText(textAfter.nodeValue)) return textAfter;
		else if (view.input.compositionNode == textAfter) {
			let descBefore = textBefore.pmViewDesc;
			if (!(!descBefore || !descBefore.isText(textBefore.nodeValue))) return textAfter;
		}
	}
	return textBefore || textAfter;
}
function timestampFromCustomEvent() {
	let event = document.createEvent("Event");
	event.initEvent("event", true, true);
	return event.timeStamp;
}
/**
@internal
*/
function endComposition(view, restarting = false) {
	if (android && view.domObserver.flushingSoon >= 0) return;
	view.domObserver.forceFlush();
	clearComposition(view);
	if (restarting || view.docView && view.docView.dirty) {
		let sel = selectionFromDOM(view), cur = view.state.selection;
		if (sel && !sel.eq(cur)) view.dispatch(view.state.tr.setSelection(sel));
		else if ((view.markCursor || restarting) && !cur.$from.node(cur.$from.sharedDepth(cur.to)).inlineContent) view.dispatch(view.state.tr.deleteSelection());
		else view.updateState(view.state);
		return true;
	}
	return false;
}
function captureCopy(view, dom) {
	if (!view.dom.parentNode) return;
	let wrap = view.dom.parentNode.appendChild(document.createElement("div"));
	wrap.appendChild(dom);
	wrap.style.cssText = "position: fixed; left: -10000px; top: 10px";
	let sel = getSelection(), range = document.createRange();
	range.selectNodeContents(dom);
	view.dom.blur();
	sel.removeAllRanges();
	sel.addRange(range);
	setTimeout(() => {
		if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
		view.focus();
	}, 50);
}
var brokenClipboardAPI = ie && ie_version < 15 || ios && webkit_version < 604;
handlers.copy = editHandlers.cut = (view, _event) => {
	let event = _event;
	let sel = view.state.selection, cut = event.type == "cut";
	if (sel.empty) return;
	let data = brokenClipboardAPI ? null : event.clipboardData;
	let { dom, text } = serializeForClipboard(view, sel.content());
	if (data) {
		event.preventDefault();
		data.clearData();
		data.setData("text/html", dom.innerHTML);
		data.setData("text/plain", text);
	} else captureCopy(view, dom);
	if (cut) view.dispatch(view.state.tr.deleteSelection().scrollIntoView().setMeta("uiEvent", "cut"));
};
function sliceSingleNode(slice) {
	return slice.openStart == 0 && slice.openEnd == 0 && slice.content.childCount == 1 ? slice.content.firstChild : null;
}
function capturePaste(view, event) {
	if (!view.dom.parentNode) return;
	let plainText = view.input.shiftKey || view.state.selection.$from.parent.type.spec.code;
	let target = view.dom.parentNode.appendChild(document.createElement(plainText ? "textarea" : "div"));
	if (!plainText) target.contentEditable = "true";
	target.style.cssText = "position: fixed; left: -10000px; top: 10px";
	target.focus();
	let plain = view.input.shiftKey && view.input.lastKeyCode != 45;
	setTimeout(() => {
		view.focus();
		if (target.parentNode) target.parentNode.removeChild(target);
		if (plainText) doPaste(view, target.value, null, plain, event);
		else doPaste(view, target.textContent, target.innerHTML, plain, event);
	}, 50);
}
function doPaste(view, text, html, preferPlain, event) {
	let slice = parseFromClipboard(view, text, html, preferPlain, view.state.selection.$from);
	if (view.someProp("handlePaste", (f) => f(view, event, slice || Slice.empty))) return true;
	if (!slice) return false;
	let singleNode = sliceSingleNode(slice);
	let tr = singleNode ? view.state.tr.replaceSelectionWith(singleNode, preferPlain) : view.state.tr.replaceSelection(slice);
	view.dispatch(tr.scrollIntoView().setMeta("paste", true).setMeta("uiEvent", "paste"));
	return true;
}
function getText(clipboardData) {
	let text = clipboardData.getData("text/plain") || clipboardData.getData("Text");
	if (text) return text;
	let uris = clipboardData.getData("text/uri-list");
	return uris ? uris.replace(/\r?\n/g, " ") : "";
}
editHandlers.paste = (view, _event) => {
	let event = _event;
	if (view.composing && !android) return;
	let data = brokenClipboardAPI ? null : event.clipboardData;
	let plain = view.input.shiftKey && view.input.lastKeyCode != 45;
	if (data && doPaste(view, getText(data), data.getData("text/html"), plain, event)) event.preventDefault();
	else capturePaste(view, event);
};
var Dragging = class {
	constructor(slice, move, node) {
		this.slice = slice;
		this.move = move;
		this.node = node;
	}
};
var dragCopyModifier = mac ? "altKey" : "ctrlKey";
function dragMoves(view, event) {
	let copy;
	view.someProp("dragCopies", (test) => {
		copy = copy || test(event);
	});
	return copy != null ? !copy : !event[dragCopyModifier];
}
handlers.dragstart = (view, _event) => {
	let event = _event;
	let mouseDown = view.input.mouseDown;
	if (mouseDown) mouseDown.done();
	if (!event.dataTransfer) return;
	let sel = view.state.selection;
	let pos = sel.empty ? null : view.posAtCoords(eventCoords(event));
	let node;
	if (pos && pos.pos >= sel.from && pos.pos <= (sel instanceof NodeSelection ? sel.to - 1 : sel.to));
	else if (mouseDown && mouseDown.mightDrag) node = NodeSelection.create(view.state.doc, mouseDown.mightDrag.pos);
	else if (event.target && event.target.nodeType == 1) {
		let desc = view.docView.nearestDesc(event.target, true);
		if (desc && desc.node.type.spec.draggable && desc != view.docView) node = NodeSelection.create(view.state.doc, desc.posBefore);
	}
	let { dom, text, slice } = serializeForClipboard(view, (node || view.state.selection).content());
	if (!event.dataTransfer.files.length || !chrome || chrome_version > 120) event.dataTransfer.clearData();
	event.dataTransfer.setData(brokenClipboardAPI ? "Text" : "text/html", dom.innerHTML);
	event.dataTransfer.effectAllowed = "copyMove";
	if (!brokenClipboardAPI) event.dataTransfer.setData("text/plain", text);
	view.dragging = new Dragging(slice, dragMoves(view, event), node);
};
handlers.dragend = (view) => {
	let dragging = view.dragging;
	window.setTimeout(() => {
		if (view.dragging == dragging) view.dragging = null;
	}, 50);
};
editHandlers.dragover = editHandlers.dragenter = (_, e) => e.preventDefault();
editHandlers.drop = (view, event) => {
	try {
		handleDrop(view, event, view.dragging);
	} finally {
		view.dragging = null;
	}
};
function handleDrop(view, event, dragging) {
	if (!event.dataTransfer) return;
	let eventPos = view.posAtCoords(eventCoords(event));
	if (!eventPos) return;
	let $mouse = view.state.doc.resolve(eventPos.pos);
	let slice = dragging && dragging.slice;
	if (slice) view.someProp("transformPasted", (f) => {
		slice = f(slice, view, false);
	});
	else slice = parseFromClipboard(view, getText(event.dataTransfer), brokenClipboardAPI ? null : event.dataTransfer.getData("text/html"), false, $mouse);
	let move = !!(dragging && dragMoves(view, event));
	if (view.someProp("handleDrop", (f) => f(view, event, slice || Slice.empty, move))) {
		event.preventDefault();
		return;
	}
	if (!slice) return;
	event.preventDefault();
	let insertPos = slice ? dropPoint(view.state.doc, $mouse.pos, slice) : $mouse.pos;
	if (insertPos == null) insertPos = $mouse.pos;
	let tr = view.state.tr;
	if (move) {
		let { node } = dragging;
		if (node) node.replace(tr);
		else tr.deleteSelection();
	}
	let pos = tr.mapping.map(insertPos);
	let isNode = slice.openStart == 0 && slice.openEnd == 0 && slice.content.childCount == 1;
	let beforeInsert = tr.doc;
	if (isNode) tr.replaceRangeWith(pos, pos, slice.content.firstChild);
	else tr.replaceRange(pos, pos, slice);
	if (tr.doc.eq(beforeInsert)) return;
	let $pos = tr.doc.resolve(pos);
	if (isNode && NodeSelection.isSelectable(slice.content.firstChild) && $pos.nodeAfter && $pos.nodeAfter.sameMarkup(slice.content.firstChild)) tr.setSelection(new NodeSelection($pos));
	else {
		let end = tr.mapping.map(insertPos);
		tr.mapping.maps[tr.mapping.maps.length - 1].forEach((_from, _to, _newFrom, newTo) => end = newTo);
		tr.setSelection(selectionBetween(view, $pos, tr.doc.resolve(end)));
	}
	view.focus();
	view.dispatch(tr.setMeta("uiEvent", "drop"));
}
handlers.focus = (view) => {
	view.input.lastFocus = Date.now();
	if (!view.focused) {
		view.domObserver.stop();
		view.dom.classList.add("ProseMirror-focused");
		view.domObserver.start();
		view.focused = true;
		setTimeout(() => {
			if (view.docView && view.hasFocus() && !view.domObserver.currentSelection.eq(view.domSelectionRange())) selectionToDOM(view);
		}, 20);
	}
};
handlers.blur = (view, _event) => {
	let event = _event;
	if (view.focused) {
		view.domObserver.stop();
		view.dom.classList.remove("ProseMirror-focused");
		view.domObserver.start();
		if (event.relatedTarget && view.dom.contains(event.relatedTarget)) view.domObserver.currentSelection.clear();
		view.focused = false;
	}
};
handlers.beforeinput = (view, _event) => {
	if (chrome && android && _event.inputType == "deleteContentBackward") {
		view.domObserver.flushSoon();
		let { domChangeCount } = view.input;
		setTimeout(() => {
			if (view.input.domChangeCount != domChangeCount) return;
			view.dom.blur();
			view.focus();
			if (view.someProp("handleKeyDown", (f) => f(view, keyEvent(8, "Backspace")))) return;
			let { $cursor } = view.state.selection;
			if ($cursor && $cursor.pos > 0) view.dispatch(view.state.tr.delete($cursor.pos - 1, $cursor.pos).scrollIntoView());
		}, 50);
	}
};
for (let prop in editHandlers) handlers[prop] = editHandlers[prop];
function compareObjs(a, b) {
	if (a == b) return true;
	for (let p in a) if (a[p] !== b[p]) return false;
	for (let p in b) if (!(p in a)) return false;
	return true;
}
var WidgetType = class WidgetType {
	constructor(toDOM, spec) {
		this.toDOM = toDOM;
		this.spec = spec || noSpec;
		this.side = this.spec.side || 0;
	}
	map(mapping, span, offset, oldOffset) {
		let { pos, deleted } = mapping.mapResult(span.from + oldOffset, this.side < 0 ? -1 : 1);
		return deleted ? null : new Decoration(pos - offset, pos - offset, this);
	}
	valid() {
		return true;
	}
	eq(other) {
		return this == other || other instanceof WidgetType && (this.spec.key && this.spec.key == other.spec.key || this.toDOM == other.toDOM && compareObjs(this.spec, other.spec));
	}
	destroy(node) {
		if (this.spec.destroy) this.spec.destroy(node);
	}
};
var InlineType = class InlineType {
	constructor(attrs, spec) {
		this.attrs = attrs;
		this.spec = spec || noSpec;
	}
	map(mapping, span, offset, oldOffset) {
		let from = mapping.map(span.from + oldOffset, this.spec.inclusiveStart ? -1 : 1) - offset;
		let to = mapping.map(span.to + oldOffset, this.spec.inclusiveEnd ? 1 : -1) - offset;
		return from >= to ? null : new Decoration(from, to, this);
	}
	valid(_, span) {
		return span.from < span.to;
	}
	eq(other) {
		return this == other || other instanceof InlineType && compareObjs(this.attrs, other.attrs) && compareObjs(this.spec, other.spec);
	}
	static is(span) {
		return span.type instanceof InlineType;
	}
	destroy() {}
};
var NodeType = class NodeType {
	constructor(attrs, spec) {
		this.attrs = attrs;
		this.spec = spec || noSpec;
	}
	map(mapping, span, offset, oldOffset) {
		let from = mapping.mapResult(span.from + oldOffset, 1);
		if (from.deleted) return null;
		let to = mapping.mapResult(span.to + oldOffset, -1);
		if (to.deleted || to.pos <= from.pos) return null;
		return new Decoration(from.pos - offset, to.pos - offset, this);
	}
	valid(node, span) {
		let { index, offset } = node.content.findIndex(span.from), child;
		return offset == span.from && !(child = node.child(index)).isText && offset + child.nodeSize == span.to;
	}
	eq(other) {
		return this == other || other instanceof NodeType && compareObjs(this.attrs, other.attrs) && compareObjs(this.spec, other.spec);
	}
	destroy() {}
};
/**
Decoration objects can be provided to the view through the
[`decorations` prop](https://prosemirror.net/docs/ref/#view.EditorProps.decorations). They come in
several variants—see the static members of this class for details.
*/
var Decoration = class Decoration {
	/**
	@internal
	*/
	constructor(from, to, type) {
		this.from = from;
		this.to = to;
		this.type = type;
	}
	/**
	@internal
	*/
	copy(from, to) {
		return new Decoration(from, to, this.type);
	}
	/**
	@internal
	*/
	eq(other, offset = 0) {
		return this.type.eq(other.type) && this.from + offset == other.from && this.to + offset == other.to;
	}
	/**
	@internal
	*/
	map(mapping, offset, oldOffset) {
		return this.type.map(mapping, this, offset, oldOffset);
	}
	/**
	Creates a widget decoration, which is a DOM node that's shown in
	the document at the given position. It is recommended that you
	delay rendering the widget by passing a function that will be
	called when the widget is actually drawn in a view, but you can
	also directly pass a DOM node. `getPos` can be used to find the
	widget's current document position.
	*/
	static widget(pos, toDOM, spec) {
		return new Decoration(pos, pos, new WidgetType(toDOM, spec));
	}
	/**
	Creates an inline decoration, which adds the given attributes to
	each inline node between `from` and `to`.
	*/
	static inline(from, to, attrs, spec) {
		return new Decoration(from, to, new InlineType(attrs, spec));
	}
	/**
	Creates a node decoration. `from` and `to` should point precisely
	before and after a node in the document. That node, and only that
	node, will receive the given attributes.
	*/
	static node(from, to, attrs, spec) {
		return new Decoration(from, to, new NodeType(attrs, spec));
	}
	/**
	The spec provided when creating this decoration. Can be useful
	if you've stored extra information in that object.
	*/
	get spec() {
		return this.type.spec;
	}
	/**
	@internal
	*/
	get inline() {
		return this.type instanceof InlineType;
	}
	/**
	@internal
	*/
	get widget() {
		return this.type instanceof WidgetType;
	}
};
var none = [], noSpec = {};
/**
A collection of [decorations](https://prosemirror.net/docs/ref/#view.Decoration), organized in such
a way that the drawing algorithm can efficiently use and compare
them. This is a persistent data structure—it is not modified,
updates create a new value.
*/
var DecorationSet = class DecorationSet {
	/**
	@internal
	*/
	constructor(local, children) {
		this.local = local.length ? local : none;
		this.children = children.length ? children : none;
	}
	/**
	Create a set of decorations, using the structure of the given
	document. This will consume (modify) the `decorations` array, so
	you must make a copy if you want need to preserve that.
	*/
	static create(doc, decorations) {
		return decorations.length ? buildTree(decorations, doc, 0, noSpec) : empty;
	}
	/**
	Find all decorations in this set which touch the given range
	(including decorations that start or end directly at the
	boundaries) and match the given predicate on their spec. When
	`start` and `end` are omitted, all decorations in the set are
	considered. When `predicate` isn't given, all decorations are
	assumed to match.
	*/
	find(start, end, predicate) {
		let result = [];
		this.findInner(start == null ? 0 : start, end == null ? 1e9 : end, result, 0, predicate);
		return result;
	}
	findInner(start, end, result, offset, predicate) {
		for (let i = 0; i < this.local.length; i++) {
			let span = this.local[i];
			if (span.from <= end && span.to >= start && (!predicate || predicate(span.spec))) result.push(span.copy(span.from + offset, span.to + offset));
		}
		for (let i = 0; i < this.children.length; i += 3) if (this.children[i] < end && this.children[i + 1] > start) {
			let childOff = this.children[i] + 1;
			this.children[i + 2].findInner(start - childOff, end - childOff, result, offset + childOff, predicate);
		}
	}
	/**
	Map the set of decorations in response to a change in the
	document.
	*/
	map(mapping, doc, options) {
		if (this == empty || mapping.maps.length == 0) return this;
		return this.mapInner(mapping, doc, 0, 0, options || noSpec);
	}
	/**
	@internal
	*/
	mapInner(mapping, node, offset, oldOffset, options) {
		let newLocal;
		for (let i = 0; i < this.local.length; i++) {
			let mapped = this.local[i].map(mapping, offset, oldOffset);
			if (mapped && mapped.type.valid(node, mapped)) (newLocal || (newLocal = [])).push(mapped);
			else if (options.onRemove) options.onRemove(this.local[i].spec);
		}
		if (this.children.length) return mapChildren(this.children, newLocal || [], mapping, node, offset, oldOffset, options);
		else return newLocal ? new DecorationSet(newLocal.sort(byPos), none) : empty;
	}
	/**
	Add the given array of decorations to the ones in the set,
	producing a new set. Consumes the `decorations` array. Needs
	access to the current document to create the appropriate tree
	structure.
	*/
	add(doc, decorations) {
		if (!decorations.length) return this;
		if (this == empty) return DecorationSet.create(doc, decorations);
		return this.addInner(doc, decorations, 0);
	}
	addInner(doc, decorations, offset) {
		let children, childIndex = 0;
		doc.forEach((childNode, childOffset) => {
			let baseOffset = childOffset + offset, found;
			if (!(found = takeSpansForNode(decorations, childNode, baseOffset))) return;
			if (!children) children = this.children.slice();
			while (childIndex < children.length && children[childIndex] < childOffset) childIndex += 3;
			if (children[childIndex] == childOffset) children[childIndex + 2] = children[childIndex + 2].addInner(childNode, found, baseOffset + 1);
			else children.splice(childIndex, 0, childOffset, childOffset + childNode.nodeSize, buildTree(found, childNode, baseOffset + 1, noSpec));
			childIndex += 3;
		});
		let local = moveSpans(childIndex ? withoutNulls(decorations) : decorations, -offset);
		for (let i = 0; i < local.length; i++) if (!local[i].type.valid(doc, local[i])) local.splice(i--, 1);
		return new DecorationSet(local.length ? this.local.concat(local).sort(byPos) : this.local, children || this.children);
	}
	/**
	Create a new set that contains the decorations in this set, minus
	the ones in the given array.
	*/
	remove(decorations) {
		if (decorations.length == 0 || this == empty) return this;
		return this.removeInner(decorations, 0);
	}
	removeInner(decorations, offset) {
		let children = this.children, local = this.local;
		for (let i = 0; i < children.length; i += 3) {
			let found;
			let from = children[i] + offset, to = children[i + 1] + offset;
			for (let j = 0, span; j < decorations.length; j++) if (span = decorations[j]) {
				if (span.from > from && span.to < to) {
					decorations[j] = null;
					(found || (found = [])).push(span);
				}
			}
			if (!found) continue;
			if (children == this.children) children = this.children.slice();
			let removed = children[i + 2].removeInner(found, from + 1);
			if (removed != empty) children[i + 2] = removed;
			else {
				children.splice(i, 3);
				i -= 3;
			}
		}
		if (local.length) {
			for (let i = 0, span; i < decorations.length; i++) if (span = decorations[i]) {
				for (let j = 0; j < local.length; j++) if (local[j].eq(span, offset)) {
					if (local == this.local) local = this.local.slice();
					local.splice(j--, 1);
				}
			}
		}
		if (children == this.children && local == this.local) return this;
		return local.length || children.length ? new DecorationSet(local, children) : empty;
	}
	forChild(offset, node) {
		if (this == empty) return this;
		if (node.isLeaf) return DecorationSet.empty;
		let child, local;
		for (let i = 0; i < this.children.length; i += 3) if (this.children[i] >= offset) {
			if (this.children[i] == offset) child = this.children[i + 2];
			break;
		}
		let start = offset + 1, end = start + node.content.size;
		for (let i = 0; i < this.local.length; i++) {
			let dec = this.local[i];
			if (dec.from < end && dec.to > start && dec.type instanceof InlineType) {
				let from = Math.max(start, dec.from) - start, to = Math.min(end, dec.to) - start;
				if (from < to) (local || (local = [])).push(dec.copy(from, to));
			}
		}
		if (local) {
			let localSet = new DecorationSet(local.sort(byPos), none);
			return child ? new DecorationGroup([localSet, child]) : localSet;
		}
		return child || empty;
	}
	/**
	@internal
	*/
	eq(other) {
		if (this == other) return true;
		if (!(other instanceof DecorationSet) || this.local.length != other.local.length || this.children.length != other.children.length) return false;
		for (let i = 0; i < this.local.length; i++) if (!this.local[i].eq(other.local[i])) return false;
		for (let i = 0; i < this.children.length; i += 3) if (this.children[i] != other.children[i] || this.children[i + 1] != other.children[i + 1] || !this.children[i + 2].eq(other.children[i + 2])) return false;
		return true;
	}
	/**
	@internal
	*/
	locals(node) {
		return removeOverlap(this.localsInner(node));
	}
	/**
	@internal
	*/
	localsInner(node) {
		if (this == empty) return none;
		if (node.inlineContent || !this.local.some(InlineType.is)) return this.local;
		let result = [];
		for (let i = 0; i < this.local.length; i++) if (!(this.local[i].type instanceof InlineType)) result.push(this.local[i]);
		return result;
	}
	forEachSet(f) {
		f(this);
	}
};
/**
The empty set of decorations.
*/
DecorationSet.empty = new DecorationSet([], []);
/**
@internal
*/
DecorationSet.removeOverlap = removeOverlap;
var empty = DecorationSet.empty;
var DecorationGroup = class DecorationGroup {
	constructor(members) {
		this.members = members;
	}
	map(mapping, doc) {
		const mappedDecos = this.members.map((member) => member.map(mapping, doc, noSpec));
		return DecorationGroup.from(mappedDecos);
	}
	forChild(offset, child) {
		if (child.isLeaf) return DecorationSet.empty;
		let found = [];
		for (let i = 0; i < this.members.length; i++) {
			let result = this.members[i].forChild(offset, child);
			if (result == empty) continue;
			if (result instanceof DecorationGroup) found = found.concat(result.members);
			else found.push(result);
		}
		return DecorationGroup.from(found);
	}
	eq(other) {
		if (!(other instanceof DecorationGroup) || other.members.length != this.members.length) return false;
		for (let i = 0; i < this.members.length; i++) if (!this.members[i].eq(other.members[i])) return false;
		return true;
	}
	locals(node) {
		let result, sorted = true;
		for (let i = 0; i < this.members.length; i++) {
			let locals = this.members[i].localsInner(node);
			if (!locals.length) continue;
			if (!result) result = locals;
			else {
				if (sorted) {
					result = result.slice();
					sorted = false;
				}
				for (let j = 0; j < locals.length; j++) result.push(locals[j]);
			}
		}
		return result ? removeOverlap(sorted ? result : result.sort(byPos)) : none;
	}
	static from(members) {
		switch (members.length) {
			case 0: return empty;
			case 1: return members[0];
			default: return new DecorationGroup(members.every((m) => m instanceof DecorationSet) ? members : members.reduce((r, m) => r.concat(m instanceof DecorationSet ? m : m.members), []));
		}
	}
	forEachSet(f) {
		for (let i = 0; i < this.members.length; i++) this.members[i].forEachSet(f);
	}
};
function mapChildren(oldChildren, newLocal, mapping, node, offset, oldOffset, options) {
	let children = oldChildren.slice();
	for (let i = 0, baseOffset = oldOffset; i < mapping.maps.length; i++) {
		let moved = 0;
		mapping.maps[i].forEach((oldStart, oldEnd, newStart, newEnd) => {
			let dSize = newEnd - newStart - (oldEnd - oldStart);
			for (let i = 0; i < children.length; i += 3) {
				let end = children[i + 1];
				if (end < 0 || oldStart > end + baseOffset - moved) continue;
				let start = children[i] + baseOffset - moved;
				if (oldEnd >= start) children[i + 1] = oldStart <= start ? -2 : -1;
				else if (oldStart >= baseOffset && dSize) {
					children[i] += dSize;
					children[i + 1] += dSize;
				}
			}
			moved += dSize;
		});
		baseOffset = mapping.maps[i].map(baseOffset, -1);
	}
	let mustRebuild = false;
	for (let i = 0; i < children.length; i += 3) if (children[i + 1] < 0) {
		if (children[i + 1] == -2) {
			mustRebuild = true;
			children[i + 1] = -1;
			continue;
		}
		let from = mapping.map(oldChildren[i] + oldOffset), fromLocal = from - offset;
		if (fromLocal < 0 || fromLocal >= node.content.size) {
			mustRebuild = true;
			continue;
		}
		let toLocal = mapping.map(oldChildren[i + 1] + oldOffset, -1) - offset;
		let { index, offset: childOffset } = node.content.findIndex(fromLocal);
		let childNode = node.maybeChild(index);
		if (childNode && childOffset == fromLocal && childOffset + childNode.nodeSize == toLocal) {
			let mapped = children[i + 2].mapInner(mapping, childNode, from + 1, oldChildren[i] + oldOffset + 1, options);
			if (mapped != empty) {
				children[i] = fromLocal;
				children[i + 1] = toLocal;
				children[i + 2] = mapped;
			} else {
				children[i + 1] = -2;
				mustRebuild = true;
			}
		} else mustRebuild = true;
	}
	if (mustRebuild) {
		let built = buildTree(mapAndGatherRemainingDecorations(children, oldChildren, newLocal, mapping, offset, oldOffset, options), node, 0, options);
		newLocal = built.local;
		for (let i = 0; i < children.length; i += 3) if (children[i + 1] < 0) {
			children.splice(i, 3);
			i -= 3;
		}
		for (let i = 0, j = 0; i < built.children.length; i += 3) {
			let from = built.children[i];
			while (j < children.length && children[j] < from) j += 3;
			children.splice(j, 0, built.children[i], built.children[i + 1], built.children[i + 2]);
		}
	}
	return new DecorationSet(newLocal.sort(byPos), children);
}
function moveSpans(spans, offset) {
	if (!offset || !spans.length) return spans;
	let result = [];
	for (let i = 0; i < spans.length; i++) {
		let span = spans[i];
		result.push(new Decoration(span.from + offset, span.to + offset, span.type));
	}
	return result;
}
function mapAndGatherRemainingDecorations(children, oldChildren, decorations, mapping, offset, oldOffset, options) {
	function gather(set, oldOffset) {
		for (let i = 0; i < set.local.length; i++) {
			let mapped = set.local[i].map(mapping, offset, oldOffset);
			if (mapped) decorations.push(mapped);
			else if (options.onRemove) options.onRemove(set.local[i].spec);
		}
		for (let i = 0; i < set.children.length; i += 3) gather(set.children[i + 2], set.children[i] + oldOffset + 1);
	}
	for (let i = 0; i < children.length; i += 3) if (children[i + 1] == -1) gather(children[i + 2], oldChildren[i] + oldOffset + 1);
	return decorations;
}
function takeSpansForNode(spans, node, offset) {
	if (node.isLeaf) return null;
	let end = offset + node.nodeSize, found = null;
	for (let i = 0, span; i < spans.length; i++) if ((span = spans[i]) && span.from > offset && span.to < end) {
		(found || (found = [])).push(span);
		spans[i] = null;
	}
	return found;
}
function withoutNulls(array) {
	let result = [];
	for (let i = 0; i < array.length; i++) if (array[i] != null) result.push(array[i]);
	return result;
}
function buildTree(spans, node, offset, options) {
	let children = [], hasNulls = false;
	node.forEach((childNode, localStart) => {
		let found = takeSpansForNode(spans, childNode, localStart + offset);
		if (found) {
			hasNulls = true;
			let subtree = buildTree(found, childNode, offset + localStart + 1, options);
			if (subtree != empty) children.push(localStart, localStart + childNode.nodeSize, subtree);
		}
	});
	let locals = moveSpans(hasNulls ? withoutNulls(spans) : spans, -offset).sort(byPos);
	for (let i = 0; i < locals.length; i++) if (!locals[i].type.valid(node, locals[i])) {
		if (options.onRemove) options.onRemove(locals[i].spec);
		locals.splice(i--, 1);
	}
	return locals.length || children.length ? new DecorationSet(locals, children) : empty;
}
function byPos(a, b) {
	return a.from - b.from || a.to - b.to;
}
function removeOverlap(spans) {
	let working = spans;
	for (let i = 0; i < working.length - 1; i++) {
		let span = working[i];
		if (span.from != span.to) for (let j = i + 1; j < working.length; j++) {
			let next = working[j];
			if (next.from == span.from) {
				if (next.to != span.to) {
					if (working == spans) working = spans.slice();
					working[j] = next.copy(next.from, span.to);
					insertAhead(working, j + 1, next.copy(span.to, next.to));
				}
				continue;
			} else {
				if (next.from < span.to) {
					if (working == spans) working = spans.slice();
					working[i] = span.copy(span.from, next.from);
					insertAhead(working, j, span.copy(next.from, span.to));
				}
				break;
			}
		}
	}
	return working;
}
function insertAhead(array, i, deco) {
	while (i < array.length && byPos(deco, array[i]) > 0) i++;
	array.splice(i, 0, deco);
}
function viewDecorations(view) {
	let found = [];
	view.someProp("decorations", (f) => {
		let result = f(view.state);
		if (result && result != empty) found.push(result);
	});
	if (view.cursorWrapper) found.push(DecorationSet.create(view.state.doc, [view.cursorWrapper.deco]));
	return DecorationGroup.from(found);
}
var observeOptions = {
	childList: true,
	characterData: true,
	characterDataOldValue: true,
	attributes: true,
	attributeOldValue: true,
	subtree: true
};
var useCharData = ie && ie_version <= 11;
var SelectionState = class {
	constructor() {
		this.anchorNode = null;
		this.anchorOffset = 0;
		this.focusNode = null;
		this.focusOffset = 0;
	}
	set(sel) {
		this.anchorNode = sel.anchorNode;
		this.anchorOffset = sel.anchorOffset;
		this.focusNode = sel.focusNode;
		this.focusOffset = sel.focusOffset;
	}
	clear() {
		this.anchorNode = this.focusNode = null;
	}
	eq(sel) {
		return sel.anchorNode == this.anchorNode && sel.anchorOffset == this.anchorOffset && sel.focusNode == this.focusNode && sel.focusOffset == this.focusOffset;
	}
};
var DOMObserver = class {
	constructor(view, handleDOMChange) {
		this.view = view;
		this.handleDOMChange = handleDOMChange;
		this.queue = [];
		this.flushingSoon = -1;
		this.observer = null;
		this.currentSelection = new SelectionState();
		this.onCharData = null;
		this.suppressingSelectionUpdates = false;
		this.lastChangedTextNode = null;
		this.observer = window.MutationObserver && new window.MutationObserver((mutations) => {
			for (let i = 0; i < mutations.length; i++) this.queue.push(mutations[i]);
			if (ie && ie_version <= 11 && mutations.some((m) => m.type == "childList" && m.removedNodes.length || m.type == "characterData" && m.oldValue.length > m.target.nodeValue.length)) this.flushSoon();
			else if (safari && view.composing && mutations.some((m) => m.type == "childList" && m.target.nodeName == "TR")) {
				view.input.badSafariComposition = true;
				this.flushSoon();
			} else this.flush();
		});
		if (useCharData) this.onCharData = (e) => {
			this.queue.push({
				target: e.target,
				type: "characterData",
				oldValue: e.prevValue
			});
			this.flushSoon();
		};
		this.onSelectionChange = this.onSelectionChange.bind(this);
	}
	flushSoon() {
		if (this.flushingSoon < 0) this.flushingSoon = window.setTimeout(() => {
			this.flushingSoon = -1;
			this.flush();
		}, 20);
	}
	forceFlush() {
		if (this.flushingSoon > -1) {
			window.clearTimeout(this.flushingSoon);
			this.flushingSoon = -1;
			this.flush();
		}
	}
	start() {
		if (this.observer) {
			this.observer.takeRecords();
			this.observer.observe(this.view.dom, observeOptions);
		}
		if (this.onCharData) this.view.dom.addEventListener("DOMCharacterDataModified", this.onCharData);
		this.connectSelection();
	}
	stop() {
		if (this.observer) {
			let take = this.observer.takeRecords();
			if (take.length) {
				for (let i = 0; i < take.length; i++) this.queue.push(take[i]);
				window.setTimeout(() => this.flush(), 20);
			}
			this.observer.disconnect();
		}
		if (this.onCharData) this.view.dom.removeEventListener("DOMCharacterDataModified", this.onCharData);
		this.disconnectSelection();
	}
	connectSelection() {
		this.view.dom.ownerDocument.addEventListener("selectionchange", this.onSelectionChange);
	}
	disconnectSelection() {
		this.view.dom.ownerDocument.removeEventListener("selectionchange", this.onSelectionChange);
	}
	suppressSelectionUpdates() {
		this.suppressingSelectionUpdates = true;
		setTimeout(() => this.suppressingSelectionUpdates = false, 50);
	}
	onSelectionChange() {
		if (!hasFocusAndSelection(this.view)) return;
		if (this.suppressingSelectionUpdates) return selectionToDOM(this.view);
		if (ie && ie_version <= 11 && !this.view.state.selection.empty) {
			let sel = this.view.domSelectionRange();
			if (sel.focusNode && isEquivalentPosition(sel.focusNode, sel.focusOffset, sel.anchorNode, sel.anchorOffset)) return this.flushSoon();
		}
		this.flush();
	}
	setCurSelection() {
		this.currentSelection.set(this.view.domSelectionRange());
	}
	ignoreSelectionChange(sel) {
		if (!sel.focusNode) return true;
		let ancestors = /* @__PURE__ */ new Set(), container;
		for (let scan = sel.focusNode; scan; scan = parentNode(scan)) ancestors.add(scan);
		for (let scan = sel.anchorNode; scan; scan = parentNode(scan)) if (ancestors.has(scan)) {
			container = scan;
			break;
		}
		let desc = container && this.view.docView.nearestDesc(container);
		if (desc && desc.ignoreMutation({
			type: "selection",
			target: container.nodeType == 3 ? container.parentNode : container
		})) {
			this.setCurSelection();
			return true;
		}
	}
	pendingRecords() {
		if (this.observer) for (let mut of this.observer.takeRecords()) this.queue.push(mut);
		return this.queue;
	}
	flush() {
		let { view } = this;
		if (!view.docView || this.flushingSoon > -1) return;
		let mutations = this.pendingRecords();
		if (mutations.length) this.queue = [];
		let sel = view.domSelectionRange();
		let newSel = !this.suppressingSelectionUpdates && !this.currentSelection.eq(sel) && hasFocusAndSelection(view) && !this.ignoreSelectionChange(sel);
		let from = -1, to = -1, typeOver = false, added = [];
		if (view.editable) for (let i = 0; i < mutations.length; i++) {
			let result = this.registerMutation(mutations[i], added);
			if (result) {
				from = from < 0 ? result.from : Math.min(result.from, from);
				to = to < 0 ? result.to : Math.max(result.to, to);
				if (result.typeOver) typeOver = true;
			}
		}
		if (added.some((n) => n.nodeName == "BR") && (view.input.lastKeyCode == 8 || view.input.lastKeyCode == 46)) {
			for (let node of added) if (node.nodeName == "BR" && node.parentNode) {
				let after = node.nextSibling;
				while (after && after.nodeType == 1) {
					if (after.contentEditable == "false") {
						node.parentNode.removeChild(node);
						break;
					}
					after = after.firstChild;
				}
			}
		} else if (gecko && added.length) {
			let brs = added.filter((n) => n.nodeName == "BR");
			if (brs.length == 2) {
				let [a, b] = brs;
				if (a.parentNode && a.parentNode.parentNode == b.parentNode) b.remove();
				else a.remove();
			} else {
				let { focusNode } = this.currentSelection;
				for (let br of brs) {
					let parent = br.parentNode;
					if (parent && parent.nodeName == "LI" && (!focusNode || blockParent(view, focusNode) != parent)) br.remove();
				}
			}
		}
		let readSel = null;
		if (from < 0 && newSel && view.input.lastFocus > Date.now() - 200 && Math.max(view.input.lastTouch, view.input.lastClick.time) < Date.now() - 300 && selectionCollapsed(sel) && (readSel = selectionFromDOM(view)) && readSel.eq(Selection.near(view.state.doc.resolve(0), 1))) {
			view.input.lastFocus = 0;
			selectionToDOM(view);
			this.currentSelection.set(sel);
			view.scrollToSelection();
		} else if (from > -1 || newSel) {
			if (from > -1) {
				view.docView.markDirty(from, to);
				checkCSS(view);
			}
			if (view.input.badSafariComposition) {
				view.input.badSafariComposition = false;
				fixUpBadSafariComposition(view, added);
			}
			this.handleDOMChange(from, to, typeOver, added);
			if (view.docView && view.docView.dirty) view.updateState(view.state);
			else if (!this.currentSelection.eq(sel)) selectionToDOM(view);
			this.currentSelection.set(sel);
		}
	}
	registerMutation(mut, added) {
		if (added.indexOf(mut.target) > -1) return null;
		let desc = this.view.docView.nearestDesc(mut.target);
		if (mut.type == "attributes" && (desc == this.view.docView || mut.attributeName == "contenteditable" || mut.attributeName == "style" && !mut.oldValue && !mut.target.getAttribute("style"))) return null;
		if (!desc || desc.ignoreMutation(mut)) return null;
		if (mut.type == "childList") {
			for (let i = 0; i < mut.addedNodes.length; i++) {
				let node = mut.addedNodes[i];
				added.push(node);
				if (node.nodeType == 3) this.lastChangedTextNode = node;
			}
			if (desc.contentDOM && desc.contentDOM != desc.dom && !desc.contentDOM.contains(mut.target)) return {
				from: desc.posBefore,
				to: desc.posAfter
			};
			let prev = mut.previousSibling, next = mut.nextSibling;
			if (ie && ie_version <= 11 && mut.addedNodes.length) for (let i = 0; i < mut.addedNodes.length; i++) {
				let { previousSibling, nextSibling } = mut.addedNodes[i];
				if (!previousSibling || Array.prototype.indexOf.call(mut.addedNodes, previousSibling) < 0) prev = previousSibling;
				if (!nextSibling || Array.prototype.indexOf.call(mut.addedNodes, nextSibling) < 0) next = nextSibling;
			}
			let fromOffset = prev && prev.parentNode == mut.target ? domIndex(prev) + 1 : 0;
			let from = desc.localPosFromDOM(mut.target, fromOffset, -1);
			let toOffset = next && next.parentNode == mut.target ? domIndex(next) : mut.target.childNodes.length;
			return {
				from,
				to: desc.localPosFromDOM(mut.target, toOffset, 1)
			};
		} else if (mut.type == "attributes") return {
			from: desc.posAtStart - desc.border,
			to: desc.posAtEnd + desc.border
		};
		else {
			this.lastChangedTextNode = mut.target;
			return {
				from: desc.posAtStart,
				to: desc.posAtEnd,
				typeOver: mut.target.nodeValue == mut.oldValue
			};
		}
	}
};
var cssChecked = /* @__PURE__ */ new WeakMap();
var cssCheckWarned = false;
function checkCSS(view) {
	if (cssChecked.has(view)) return;
	cssChecked.set(view, null);
	if ([
		"normal",
		"nowrap",
		"pre-line"
	].indexOf(getComputedStyle(view.dom).whiteSpace) !== -1) {
		view.requiresGeckoHackNode = gecko;
		if (cssCheckWarned) return;
		console["warn"]("ProseMirror expects the CSS white-space property to be set, preferably to 'pre-wrap'. It is recommended to load style/prosemirror.css from the prosemirror-view package.");
		cssCheckWarned = true;
	}
}
function rangeToSelectionRange(view, range) {
	let anchorNode = range.startContainer, anchorOffset = range.startOffset;
	let focusNode = range.endContainer, focusOffset = range.endOffset;
	let currentAnchor = view.domAtPos(view.state.selection.anchor);
	if (isEquivalentPosition(currentAnchor.node, currentAnchor.offset, focusNode, focusOffset)) [anchorNode, anchorOffset, focusNode, focusOffset] = [
		focusNode,
		focusOffset,
		anchorNode,
		anchorOffset
	];
	return {
		anchorNode,
		anchorOffset,
		focusNode,
		focusOffset
	};
}
function safariShadowSelectionRange(view, selection) {
	if (selection.getComposedRanges) {
		let range = selection.getComposedRanges(view.root)[0];
		if (range) return rangeToSelectionRange(view, range);
	}
	let found;
	function read(event) {
		event.preventDefault();
		event.stopImmediatePropagation();
		found = event.getTargetRanges()[0];
	}
	view.dom.addEventListener("beforeinput", read, true);
	document.execCommand("indent");
	view.dom.removeEventListener("beforeinput", read, true);
	return found ? rangeToSelectionRange(view, found) : null;
}
function blockParent(view, node) {
	for (let p = node.parentNode; p && p != view.dom; p = p.parentNode) {
		let desc = view.docView.nearestDesc(p, true);
		if (desc && desc.node.isBlock) return p;
	}
	return null;
}
function fixUpBadSafariComposition(view, addedNodes) {
	var _a;
	let { focusNode, focusOffset } = view.domSelectionRange();
	for (let node of addedNodes) if (((_a = node.parentNode) === null || _a === void 0 ? void 0 : _a.nodeName) == "TR") {
		let nextCell = node.nextSibling;
		while (nextCell && nextCell.nodeName != "TD" && nextCell.nodeName != "TH") nextCell = nextCell.nextSibling;
		if (nextCell) {
			let parent = nextCell;
			for (;;) {
				let first = parent.firstChild;
				if (!first || first.nodeType != 1 || first.contentEditable == "false" || /^(BR|IMG)$/.test(first.nodeName)) break;
				parent = first;
			}
			parent.insertBefore(node, parent.firstChild);
			if (focusNode == node) view.domSelection().collapse(node, focusOffset);
		} else node.parentNode.removeChild(node);
	}
}
function parseBetween(view, from_, to_) {
	let { node: parent, fromOffset, toOffset, from, to } = view.docView.parseRange(from_, to_);
	let domSel = view.domSelectionRange();
	let find;
	let anchor = domSel.anchorNode;
	if (anchor && view.dom.contains(anchor.nodeType == 1 ? anchor : anchor.parentNode)) {
		find = [{
			node: anchor,
			offset: domSel.anchorOffset
		}];
		if (!selectionCollapsed(domSel)) find.push({
			node: domSel.focusNode,
			offset: domSel.focusOffset
		});
	}
	if (chrome && view.input.lastKeyCode === 8) for (let off = toOffset; off > fromOffset; off--) {
		let node = parent.childNodes[off - 1], desc = node.pmViewDesc;
		if (node.nodeName == "BR" && !desc) {
			toOffset = off;
			break;
		}
		if (!desc || desc.size) break;
	}
	let startDoc = view.state.doc;
	let parser = view.someProp("domParser") || DOMParser.fromSchema(view.state.schema);
	let $from = startDoc.resolve(from);
	let sel = null, doc = parser.parse(parent, {
		topNode: $from.parent,
		topMatch: $from.parent.contentMatchAt($from.index()),
		topOpen: true,
		from: fromOffset,
		to: toOffset,
		preserveWhitespace: $from.parent.type.whitespace == "pre" ? "full" : true,
		findPositions: find,
		ruleFromNode,
		context: $from
	});
	if (find && find[0].pos != null) {
		let anchor = find[0].pos, head = find[1] && find[1].pos;
		if (head == null) head = anchor;
		sel = {
			anchor: anchor + from,
			head: head + from
		};
	}
	return {
		doc,
		sel,
		from,
		to
	};
}
function ruleFromNode(dom) {
	let desc = dom.pmViewDesc;
	if (desc) return desc.parseRule();
	else if (dom.nodeName == "BR" && dom.parentNode) {
		if (safari && /^(ul|ol)$/i.test(dom.parentNode.nodeName)) {
			let skip = document.createElement("div");
			skip.appendChild(document.createElement("li"));
			return { skip };
		} else if (dom.parentNode.lastChild == dom || safari && /^(tr|table)$/i.test(dom.parentNode.nodeName)) return { ignore: true };
	} else if (dom.nodeName == "IMG" && dom.getAttribute("mark-placeholder")) return { ignore: true };
	return null;
}
var isInline = /^(a|abbr|acronym|b|bd[io]|big|br|button|cite|code|data(list)?|del|dfn|em|i|img|ins|kbd|label|map|mark|meter|output|q|ruby|s|samp|small|span|strong|su[bp]|time|u|tt|var)$/i;
function readDOMChange(view, from, to, typeOver, addedNodes) {
	let compositionID = view.input.compositionPendingChanges || (view.composing ? view.input.compositionID : 0);
	view.input.compositionPendingChanges = 0;
	if (from < 0) {
		let origin = view.input.lastSelectionTime > Date.now() - 50 ? view.input.lastSelectionOrigin : null;
		let newSel = selectionFromDOM(view, origin);
		if (newSel && !view.state.selection.eq(newSel)) {
			if (chrome && android && view.input.lastKeyCode === 13 && Date.now() - 100 < view.input.lastKeyCodeTime && view.someProp("handleKeyDown", (f) => f(view, keyEvent(13, "Enter")))) return;
			let tr = view.state.tr.setSelection(newSel);
			if (origin == "pointer") tr.setMeta("pointer", true);
			else if (origin == "key") tr.scrollIntoView();
			if (compositionID) tr.setMeta("composition", compositionID);
			view.dispatch(tr);
		}
		return;
	}
	let $before = view.state.doc.resolve(from);
	let shared = $before.sharedDepth(to);
	from = $before.before(shared + 1);
	to = view.state.doc.resolve(to).after(shared + 1);
	let sel = view.state.selection;
	let parse = parseBetween(view, from, to);
	let doc = view.state.doc, compare = doc.slice(parse.from, parse.to);
	let preferredPos, preferredSide;
	if (view.input.lastKeyCode === 8 && Date.now() - 100 < view.input.lastKeyCodeTime) {
		preferredPos = view.state.selection.to;
		preferredSide = "end";
	} else {
		preferredPos = view.state.selection.from;
		preferredSide = "start";
	}
	view.input.lastKeyCode = null;
	let change = findDiff(compare.content, parse.doc.content, parse.from, preferredPos, preferredSide);
	if (change) view.input.domChangeCount++;
	if ((ios && view.input.lastIOSEnter > Date.now() - 225 || android) && addedNodes.some((n) => n.nodeType == 1 && !isInline.test(n.nodeName)) && (!change || change.endA >= change.endB) && view.someProp("handleKeyDown", (f) => f(view, keyEvent(13, "Enter")))) {
		view.input.lastIOSEnter = 0;
		return;
	}
	if (!change) if (typeOver && sel instanceof TextSelection && !sel.empty && sel.$head.sameParent(sel.$anchor) && !view.composing && !(parse.sel && parse.sel.anchor != parse.sel.head)) change = {
		start: sel.from,
		endA: sel.to,
		endB: sel.to
	};
	else {
		if (parse.sel) {
			let sel = resolveSelection(view, view.state.doc, parse.sel);
			if (sel && !sel.eq(view.state.selection)) {
				let tr = view.state.tr.setSelection(sel);
				if (compositionID) tr.setMeta("composition", compositionID);
				view.dispatch(tr);
			}
		}
		return;
	}
	if (view.state.selection.from < view.state.selection.to && change.start == change.endB && view.state.selection instanceof TextSelection) {
		if (change.start > view.state.selection.from && change.start <= view.state.selection.from + 2 && view.state.selection.from >= parse.from) change.start = view.state.selection.from;
		else if (change.endA < view.state.selection.to && change.endA >= view.state.selection.to - 2 && view.state.selection.to <= parse.to) {
			change.endB += view.state.selection.to - change.endA;
			change.endA = view.state.selection.to;
		}
	}
	if (ie && ie_version <= 11 && change.endB == change.start + 1 && change.endA == change.start && change.start > parse.from && parse.doc.textBetween(change.start - parse.from - 1, change.start - parse.from + 1) == " \xA0") {
		change.start--;
		change.endA--;
		change.endB--;
	}
	let $from = parse.doc.resolveNoCache(change.start - parse.from);
	let $to = parse.doc.resolveNoCache(change.endB - parse.from);
	let $fromA = doc.resolve(change.start);
	let inlineChange = $from.sameParent($to) && $from.parent.inlineContent && $fromA.end() >= change.endA;
	if ((ios && view.input.lastIOSEnter > Date.now() - 225 && (!inlineChange || addedNodes.some((n) => n.nodeName == "DIV" || n.nodeName == "P")) || !inlineChange && $from.pos < parse.doc.content.size && (!$from.sameParent($to) || !$from.parent.inlineContent) && $from.pos < $to.pos && !/\S/.test(parse.doc.textBetween($from.pos, $to.pos, "", ""))) && view.someProp("handleKeyDown", (f) => f(view, keyEvent(13, "Enter")))) {
		view.input.lastIOSEnter = 0;
		return;
	}
	if (view.state.selection.anchor > change.start && looksLikeBackspace(doc, change.start, change.endA, $from, $to) && view.someProp("handleKeyDown", (f) => f(view, keyEvent(8, "Backspace")))) {
		if (android && chrome) view.domObserver.suppressSelectionUpdates();
		return;
	}
	if (chrome && change.endB == change.start) view.input.lastChromeDelete = Date.now();
	if (android && !inlineChange && $from.start() != $to.start() && $to.parentOffset == 0 && $from.depth == $to.depth && parse.sel && parse.sel.anchor == parse.sel.head && parse.sel.head == change.endA) {
		change.endB -= 2;
		$to = parse.doc.resolveNoCache(change.endB - parse.from);
		setTimeout(() => {
			view.someProp("handleKeyDown", function(f) {
				return f(view, keyEvent(13, "Enter"));
			});
		}, 20);
	}
	let chFrom = change.start, chTo = change.endA;
	let mkTr = (base) => {
		let tr = base || view.state.tr.replace(chFrom, chTo, parse.doc.slice(change.start - parse.from, change.endB - parse.from));
		if (parse.sel) {
			let sel = resolveSelection(view, tr.doc, parse.sel);
			if (sel && !(chrome && view.composing && sel.empty && (change.start != change.endB || view.input.lastChromeDelete < Date.now() - 100) && (sel.head == chFrom || sel.head == tr.mapping.map(chTo) - 1) || ie && sel.empty && sel.head == chFrom)) tr.setSelection(sel);
		}
		if (compositionID) tr.setMeta("composition", compositionID);
		return tr.scrollIntoView();
	};
	let markChange;
	if (inlineChange) if ($from.pos == $to.pos) {
		if (ie && ie_version <= 11 && $from.parentOffset == 0) {
			view.domObserver.suppressSelectionUpdates();
			setTimeout(() => selectionToDOM(view), 20);
		}
		let tr = mkTr(view.state.tr.delete(chFrom, chTo));
		let marks = doc.resolve(change.start).marksAcross(doc.resolve(change.endA));
		if (marks) tr.ensureMarks(marks);
		view.dispatch(tr);
	} else if (change.endA == change.endB && (markChange = isMarkChange($from.parent.content.cut($from.parentOffset, $to.parentOffset), $fromA.parent.content.cut($fromA.parentOffset, change.endA - $fromA.start())))) {
		let tr = mkTr(view.state.tr);
		if (markChange.type == "add") tr.addMark(chFrom, chTo, markChange.mark);
		else tr.removeMark(chFrom, chTo, markChange.mark);
		view.dispatch(tr);
	} else if ($from.parent.child($from.index()).isText && $from.index() == $to.index() - ($to.textOffset ? 0 : 1)) {
		let text = $from.parent.textBetween($from.parentOffset, $to.parentOffset);
		let deflt = () => mkTr(view.state.tr.insertText(text, chFrom, chTo));
		if (!view.someProp("handleTextInput", (f) => f(view, chFrom, chTo, text, deflt))) view.dispatch(deflt());
	} else view.dispatch(mkTr());
	else view.dispatch(mkTr());
}
function resolveSelection(view, doc, parsedSel) {
	if (Math.max(parsedSel.anchor, parsedSel.head) > doc.content.size) return null;
	return selectionBetween(view, doc.resolve(parsedSel.anchor), doc.resolve(parsedSel.head));
}
function isMarkChange(cur, prev) {
	let curMarks = cur.firstChild.marks, prevMarks = prev.firstChild.marks;
	let added = curMarks, removed = prevMarks, type, mark, update;
	for (let i = 0; i < prevMarks.length; i++) added = prevMarks[i].removeFromSet(added);
	for (let i = 0; i < curMarks.length; i++) removed = curMarks[i].removeFromSet(removed);
	if (added.length == 1 && removed.length == 0) {
		mark = added[0];
		type = "add";
		update = (node) => node.mark(mark.addToSet(node.marks));
	} else if (added.length == 0 && removed.length == 1) {
		mark = removed[0];
		type = "remove";
		update = (node) => node.mark(mark.removeFromSet(node.marks));
	} else return null;
	let updated = [];
	for (let i = 0; i < prev.childCount; i++) updated.push(update(prev.child(i)));
	if (Fragment.from(updated).eq(cur)) return {
		mark,
		type
	};
}
function looksLikeBackspace(old, start, end, $newStart, $newEnd) {
	if (end - start <= $newEnd.pos - $newStart.pos || skipClosingAndOpening($newStart, true, false) < $newEnd.pos) return false;
	let $start = old.resolve(start);
	if (!$newStart.parent.isTextblock) {
		let after = $start.nodeAfter;
		return after != null && end == start + after.nodeSize;
	}
	if ($start.parentOffset < $start.parent.content.size || !$start.parent.isTextblock) return false;
	let $next = old.resolve(skipClosingAndOpening($start, true, true));
	if (!$next.parent.isTextblock || $next.pos > end || skipClosingAndOpening($next, true, false) < end) return false;
	return $newStart.parent.content.cut($newStart.parentOffset).eq($next.parent.content);
}
function skipClosingAndOpening($pos, fromEnd, mayOpen) {
	let depth = $pos.depth, end = fromEnd ? $pos.end() : $pos.pos;
	while (depth > 0 && (fromEnd || $pos.indexAfter(depth) == $pos.node(depth).childCount)) {
		depth--;
		end++;
		fromEnd = false;
	}
	if (mayOpen) {
		let next = $pos.node(depth).maybeChild($pos.indexAfter(depth));
		while (next && !next.isLeaf) {
			next = next.firstChild;
			end++;
		}
	}
	return end;
}
function findDiff(a, b, pos, preferredPos, preferredSide) {
	let start = a.findDiffStart(b, pos);
	if (start == null) return null;
	let { a: endA, b: endB } = a.findDiffEnd(b, pos + a.size, pos + b.size);
	if (preferredSide == "end") {
		let adjust = Math.max(0, start - Math.min(endA, endB));
		preferredPos -= endA + adjust - start;
	}
	if (endA < start && a.size < b.size) {
		let move = preferredPos <= start && preferredPos >= endA ? start - preferredPos : 0;
		start -= move;
		if (start && start < b.size && isSurrogatePair(b.textBetween(start - 1, start + 1))) start += move ? 1 : -1;
		endB = start + (endB - endA);
		endA = start;
	} else if (endB < start) {
		let move = preferredPos <= start && preferredPos >= endB ? start - preferredPos : 0;
		start -= move;
		if (start && start < a.size && isSurrogatePair(a.textBetween(start - 1, start + 1))) start += move ? 1 : -1;
		endA = start + (endA - endB);
		endB = start;
	}
	return {
		start,
		endA,
		endB
	};
}
function isSurrogatePair(str) {
	if (str.length != 2) return false;
	let a = str.charCodeAt(0), b = str.charCodeAt(1);
	return a >= 56320 && a <= 57343 && b >= 55296 && b <= 56319;
}
/**
@internal
*/
var __parseFromClipboard = parseFromClipboard;
/**
@internal
*/
var __endComposition = endComposition;
/**
An editor view manages the DOM structure that represents an
editable document. Its state and behavior are determined by its
[props](https://prosemirror.net/docs/ref/#view.DirectEditorProps).
*/
var EditorView = class {
	/**
	Create a view. `place` may be a DOM node that the editor should
	be appended to, a function that will place it into the document,
	or an object whose `mount` property holds the node to use as the
	document container. If it is `null`, the editor will not be
	added to the document.
	*/
	constructor(place, props) {
		this._root = null;
		/**
		@internal
		*/
		this.focused = false;
		/**
		Kludge used to work around a Chrome bug @internal
		*/
		this.trackWrites = null;
		this.mounted = false;
		/**
		@internal
		*/
		this.markCursor = null;
		/**
		@internal
		*/
		this.cursorWrapper = null;
		/**
		@internal
		*/
		this.lastSelectedViewDesc = void 0;
		/**
		@internal
		*/
		this.input = new InputState();
		this.prevDirectPlugins = [];
		this.pluginViews = [];
		/**
		Holds `true` when a hack node is needed in Firefox to prevent the
		[space is eaten issue](https://code.haverbeke.berlin/prosemirror/prosemirror/issues/651)
		@internal
		*/
		this.requiresGeckoHackNode = false;
		/**
		When editor content is being dragged, this object contains
		information about the dragged slice and whether it is being
		copied or moved. At any other time, it is null.
		*/
		this.dragging = null;
		this._props = props;
		this.state = props.state;
		this.directPlugins = props.plugins || [];
		this.directPlugins.forEach(checkStateComponent);
		this.dispatch = this.dispatch.bind(this);
		this.dom = place && place.mount || document.createElement("div");
		if (place) {
			if (place.appendChild) place.appendChild(this.dom);
			else if (typeof place == "function") place(this.dom);
			else if (place.mount) this.mounted = true;
		}
		this.editable = getEditable(this);
		updateCursorWrapper(this);
		this.nodeViews = buildNodeViews(this);
		this.docView = docViewDesc(this.state.doc, computeDocDeco(this), viewDecorations(this), this.dom, this);
		this.domObserver = new DOMObserver(this, (from, to, typeOver, added) => readDOMChange(this, from, to, typeOver, added));
		this.domObserver.start();
		initInput(this);
		this.updatePluginViews();
	}
	/**
	Holds `true` when a
	[composition](https://w3c.github.io/uievents/#events-compositionevents)
	is active.
	*/
	get composing() {
		return this.input.composing;
	}
	/**
	The view's current [props](https://prosemirror.net/docs/ref/#view.EditorProps).
	*/
	get props() {
		if (this._props.state != this.state) {
			let prev = this._props;
			this._props = {};
			for (let name in prev) this._props[name] = prev[name];
			this._props.state = this.state;
		}
		return this._props;
	}
	/**
	Update the view's props. Will immediately cause an update to
	the DOM.
	*/
	update(props) {
		if (props.handleDOMEvents != this._props.handleDOMEvents) ensureListeners(this);
		let prevProps = this._props;
		this._props = props;
		if (props.plugins) {
			props.plugins.forEach(checkStateComponent);
			this.directPlugins = props.plugins;
		}
		this.updateStateInner(props.state, prevProps);
	}
	/**
	Update the view by updating existing props object with the object
	given as argument. Equivalent to `view.update(Object.assign({},
	view.props, props))`.
	*/
	setProps(props) {
		let updated = {};
		for (let name in this._props) updated[name] = this._props[name];
		updated.state = this.state;
		for (let name in props) updated[name] = props[name];
		this.update(updated);
	}
	/**
	Update the editor's `state` prop, without touching any of the
	other props.
	*/
	updateState(state) {
		this.updateStateInner(state, this._props);
	}
	updateStateInner(state, prevProps) {
		var _a;
		let prev = this.state, redraw = false, updateSel = false;
		if (state.storedMarks && this.composing) {
			clearComposition(this);
			updateSel = true;
		}
		this.state = state;
		let pluginsChanged = prev.plugins != state.plugins || this._props.plugins != prevProps.plugins;
		if (pluginsChanged || this._props.plugins != prevProps.plugins || this._props.nodeViews != prevProps.nodeViews) {
			let nodeViews = buildNodeViews(this);
			if (changedNodeViews(nodeViews, this.nodeViews)) {
				this.nodeViews = nodeViews;
				redraw = true;
			}
		}
		if (pluginsChanged || prevProps.handleDOMEvents != this._props.handleDOMEvents) ensureListeners(this);
		this.editable = getEditable(this);
		updateCursorWrapper(this);
		let innerDeco = viewDecorations(this), outerDeco = computeDocDeco(this);
		let scroll = prev.plugins != state.plugins && !prev.doc.eq(state.doc) ? "reset" : state.scrollToSelection > prev.scrollToSelection ? "to selection" : "preserve";
		let updateDoc = redraw || !this.docView.matchesNode(state.doc, outerDeco, innerDeco);
		if (updateDoc || !state.selection.eq(prev.selection)) updateSel = true;
		let oldScrollPos = scroll == "preserve" && updateSel && this.dom.style.overflowAnchor == null && storeScrollPos(this);
		if (updateSel) {
			this.domObserver.stop();
			let forceSelUpdate = updateDoc && (ie || chrome) && !this.composing && !prev.selection.empty && !state.selection.empty && selectionContextChanged(prev.selection, state.selection);
			if (updateDoc) {
				let chromeKludge = chrome ? this.trackWrites = this.domSelectionRange().focusNode : null;
				if (this.composing) this.input.compositionNode = findCompositionNode(this);
				if (redraw || !this.docView.update(state.doc, outerDeco, innerDeco, this)) {
					this.docView.updateOuterDeco(outerDeco);
					this.docView.destroy();
					this.docView = docViewDesc(state.doc, outerDeco, innerDeco, this.dom, this);
				}
				if (chromeKludge && (!this.trackWrites || !this.dom.contains(this.trackWrites))) forceSelUpdate = true;
			}
			if (forceSelUpdate || !(this.input.mouseDown && this.domObserver.currentSelection.eq(this.domSelectionRange()) && anchorInRightPlace(this))) selectionToDOM(this, forceSelUpdate);
			else {
				syncNodeSelection(this, state.selection);
				this.domObserver.setCurSelection();
			}
			this.domObserver.start();
		}
		this.updatePluginViews(prev);
		if (((_a = this.dragging) === null || _a === void 0 ? void 0 : _a.node) && !prev.doc.eq(state.doc)) this.updateDraggedNode(this.dragging, prev);
		if (scroll == "reset") this.dom.scrollTop = 0;
		else if (scroll == "to selection") this.scrollToSelection();
		else if (oldScrollPos) resetScrollPos(oldScrollPos);
	}
	/**
	@internal
	*/
	scrollToSelection() {
		let startDOM = this.domSelectionRange().focusNode;
		if (!startDOM || !this.dom.contains(startDOM.nodeType == 1 ? startDOM : startDOM.parentNode));
		else if (this.someProp("handleScrollToSelection", (f) => f(this)));
		else if (this.state.selection instanceof NodeSelection) {
			let target = this.docView.domAfterPos(this.state.selection.from);
			if (target.nodeType == 1) scrollRectIntoView(this, target.getBoundingClientRect(), startDOM);
		} else scrollRectIntoView(this, this.coordsAtPos(this.state.selection.head, 1), startDOM);
	}
	destroyPluginViews() {
		let view;
		while (view = this.pluginViews.pop()) if (view.destroy) view.destroy();
	}
	updatePluginViews(prevState) {
		if (!prevState || prevState.plugins != this.state.plugins || this.directPlugins != this.prevDirectPlugins) {
			this.prevDirectPlugins = this.directPlugins;
			this.destroyPluginViews();
			for (let i = 0; i < this.directPlugins.length; i++) {
				let plugin = this.directPlugins[i];
				if (plugin.spec.view) this.pluginViews.push(plugin.spec.view(this));
			}
			for (let i = 0; i < this.state.plugins.length; i++) {
				let plugin = this.state.plugins[i];
				if (plugin.spec.view) this.pluginViews.push(plugin.spec.view(this));
			}
		} else for (let i = 0; i < this.pluginViews.length; i++) {
			let pluginView = this.pluginViews[i];
			if (pluginView.update) pluginView.update(this, prevState);
		}
	}
	updateDraggedNode(dragging, prev) {
		let sel = dragging.node, found = -1;
		if (sel.from < this.state.doc.content.size && this.state.doc.nodeAt(sel.from) == sel.node) found = sel.from;
		else {
			let movedPos = sel.from + (this.state.doc.content.size - prev.doc.content.size);
			if ((movedPos > 0 && movedPos < this.state.doc.content.size && this.state.doc.nodeAt(movedPos)) == sel.node) found = movedPos;
		}
		this.dragging = new Dragging(dragging.slice, dragging.move, found < 0 ? void 0 : NodeSelection.create(this.state.doc, found));
	}
	someProp(propName, f) {
		let prop = this._props && this._props[propName], value;
		if (prop != null && (value = f ? f(prop) : prop)) return value;
		for (let i = 0; i < this.directPlugins.length; i++) {
			let prop = this.directPlugins[i].props[propName];
			if (prop != null && (value = f ? f(prop) : prop)) return value;
		}
		let plugins = this.state.plugins;
		if (plugins) for (let i = 0; i < plugins.length; i++) {
			let prop = plugins[i].props[propName];
			if (prop != null && (value = f ? f(prop) : prop)) return value;
		}
	}
	/**
	Query whether the view has focus.
	*/
	hasFocus() {
		if (ie) {
			let node = this.root.activeElement;
			if (node == this.dom) return true;
			if (!node || !this.dom.contains(node)) return false;
			while (node && this.dom != node && this.dom.contains(node)) {
				if (node.contentEditable == "false") return false;
				node = node.parentElement;
			}
			return true;
		}
		return this.root.activeElement == this.dom;
	}
	/**
	Focus the editor.
	*/
	focus() {
		this.domObserver.stop();
		if (this.editable) focusPreventScroll(this.dom);
		selectionToDOM(this);
		this.domObserver.start();
	}
	/**
	Get the document root in which the editor exists. This will
	usually be the top-level `document`, but might be a [shadow
	DOM](https://developer.mozilla.org/en-US/docs/Web/Web_Components/Shadow_DOM)
	root if the editor is inside one.
	*/
	get root() {
		let cached = this._root;
		if (cached == null) {
			for (let search = this.dom.parentNode; search; search = search.parentNode) if (search.nodeType == 9 || search.nodeType == 11 && search.host) {
				if (!search.getSelection) Object.getPrototypeOf(search).getSelection = () => search.ownerDocument.getSelection();
				return this._root = search;
			}
		}
		return cached || document;
	}
	/**
	When an existing editor view is moved to a new document or
	shadow tree, call this to make it recompute its root.
	*/
	updateRoot() {
		this._root = null;
	}
	/**
	Given a pair of viewport coordinates, return the document
	position that corresponds to them. May return null if the given
	coordinates aren't inside of the editor. When an object is
	returned, its `pos` property is the position nearest to the
	coordinates, and its `inside` property holds the position of the
	inner node that the position falls inside of, or -1 if it is at
	the top level, not in any node.
	*/
	posAtCoords(coords) {
		return posAtCoords(this, coords);
	}
	/**
	Returns the viewport rectangle at a given document position.
	`left` and `right` will be the same number, as this returns a
	flat cursor-ish rectangle. If the position is between two things
	that aren't directly adjacent, `side` determines which element
	is used. When < 0, the element before the position is used,
	otherwise the element after.
	*/
	coordsAtPos(pos, side = 1) {
		return coordsAtPos(this, pos, side);
	}
	/**
	Find the DOM position that corresponds to the given document
	position. When `side` is negative, find the position as close as
	possible to the content before the position. When positive,
	prefer positions close to the content after the position. When
	zero, prefer as shallow a position as possible.
	
	Note that you should **not** mutate the editor's internal DOM,
	only inspect it (and even that is usually not necessary).
	*/
	domAtPos(pos, side = 0) {
		return this.docView.domFromPos(pos, side);
	}
	/**
	Find the DOM node that represents the document node after the
	given position. May return `null` when the position doesn't point
	in front of a node or if the node is inside an opaque node view.
	
	This is intended to be able to call things like
	`getBoundingClientRect` on that DOM node. Do **not** mutate the
	editor DOM directly, or add styling this way, since that will be
	immediately overriden by the editor as it redraws the node.
	*/
	nodeDOM(pos) {
		let desc = this.docView.descAt(pos);
		return desc ? desc.nodeDOM : null;
	}
	/**
	Find the document position that corresponds to a given DOM
	position. (Whenever possible, it is preferable to inspect the
	document structure directly, rather than poking around in the
	DOM, but sometimes—for example when interpreting an event
	target—you don't have a choice.)
	
	The `bias` parameter can be used to influence which side of a DOM
	node to use when the position is inside a leaf node.
	*/
	posAtDOM(node, offset, bias = -1) {
		let pos = this.docView.posFromDOM(node, offset, bias);
		if (pos == null) throw new RangeError("DOM position not inside the editor");
		return pos;
	}
	/**
	Find out whether the selection is at the end of a textblock when
	moving in a given direction. When, for example, given `"left"`,
	it will return true if moving left from the current cursor
	position would leave that position's parent textblock. Will apply
	to the view's current state by default, but it is possible to
	pass a different state.
	*/
	endOfTextblock(dir, state) {
		return endOfTextblock(this, state || this.state, dir);
	}
	/**
	Run the editor's paste logic with the given HTML string. The
	`event`, if given, will be passed to the
	[`handlePaste`](https://prosemirror.net/docs/ref/#view.EditorProps.handlePaste) hook.
	*/
	pasteHTML(html, event) {
		return doPaste(this, "", html, false, event || new ClipboardEvent("paste"));
	}
	/**
	Run the editor's paste logic with the given plain-text input.
	*/
	pasteText(text, event) {
		return doPaste(this, text, null, true, event || new ClipboardEvent("paste"));
	}
	/**
	Serialize the given slice as it would be if it was copied from
	this editor. Returns a DOM element that contains a
	representation of the slice as its children, a textual
	representation, and the transformed slice (which can be
	different from the given input due to hooks like
	[`transformCopied`](https://prosemirror.net/docs/ref/#view.EditorProps.transformCopied)).
	*/
	serializeForClipboard(slice) {
		return serializeForClipboard(this, slice);
	}
	/**
	Removes the editor from the DOM and destroys all [node
	views](https://prosemirror.net/docs/ref/#view.NodeView).
	*/
	destroy() {
		if (!this.docView) return;
		destroyInput(this);
		this.destroyPluginViews();
		if (this.mounted) {
			this.docView.update(this.state.doc, [], viewDecorations(this), this);
			this.dom.textContent = "";
		} else if (this.dom.parentNode) this.dom.parentNode.removeChild(this.dom);
		this.docView.destroy();
		this.docView = null;
		clearReusedRange();
	}
	/**
	This is true when the view has been
	[destroyed](https://prosemirror.net/docs/ref/#view.EditorView.destroy) (and thus should not be
	used anymore).
	*/
	get isDestroyed() {
		return this.docView == null;
	}
	/**
	Used for testing.
	*/
	dispatchEvent(event) {
		return dispatchEvent(this, event);
	}
	/**
	@internal
	*/
	domSelectionRange() {
		let sel = this.domSelection();
		if (!sel) return {
			focusNode: null,
			focusOffset: 0,
			anchorNode: null,
			anchorOffset: 0
		};
		return safari && this.root.nodeType === 11 && deepActiveElement(this.dom.ownerDocument) == this.dom && safariShadowSelectionRange(this, sel) || sel;
	}
	/**
	@internal
	*/
	domSelection() {
		return this.root.getSelection();
	}
};
EditorView.prototype.dispatch = function(tr) {
	let dispatchTransaction = this._props.dispatchTransaction;
	if (dispatchTransaction) dispatchTransaction.call(this, tr);
	else this.updateState(this.state.apply(tr));
};
function computeDocDeco(view) {
	let attrs = Object.create(null);
	attrs.class = "ProseMirror";
	attrs.contenteditable = String(view.editable);
	view.someProp("attributes", (value) => {
		if (typeof value == "function") value = value(view.state);
		if (value) {
			for (let attr in value) if (attr == "class") attrs.class += " " + value[attr];
			else if (attr == "style") attrs.style = (attrs.style ? attrs.style + ";" : "") + value[attr];
			else if (!attrs[attr] && attr != "contenteditable" && attr != "nodeName") attrs[attr] = String(value[attr]);
		}
	});
	if (!attrs.translate) attrs.translate = "no";
	return [Decoration.node(0, view.state.doc.content.size, attrs)];
}
function updateCursorWrapper(view) {
	if (view.markCursor) {
		let dom = document.createElement("img");
		dom.className = "ProseMirror-separator";
		dom.setAttribute("mark-placeholder", "true");
		dom.setAttribute("alt", "");
		view.cursorWrapper = {
			dom,
			deco: Decoration.widget(view.state.selection.from, dom, {
				raw: true,
				marks: view.markCursor
			})
		};
	} else view.cursorWrapper = null;
}
function getEditable(view) {
	return !view.someProp("editable", (value) => value(view.state) === false);
}
function selectionContextChanged(sel1, sel2) {
	let depth = Math.min(sel1.$anchor.sharedDepth(sel1.head), sel2.$anchor.sharedDepth(sel2.head));
	return sel1.$anchor.start(depth) != sel2.$anchor.start(depth);
}
function buildNodeViews(view) {
	let result = Object.create(null);
	function add(obj) {
		for (let prop in obj) if (!Object.prototype.hasOwnProperty.call(result, prop)) result[prop] = obj[prop];
	}
	view.someProp("nodeViews", add);
	view.someProp("markViews", add);
	return result;
}
function changedNodeViews(a, b) {
	let nA = 0, nB = 0;
	for (let prop in a) {
		if (a[prop] != b[prop]) return true;
		nA++;
	}
	for (let _ in b) nB++;
	return nA != nB;
}
function checkStateComponent(plugin) {
	if (plugin.spec.state || plugin.spec.filterTransaction || plugin.spec.appendTransaction) throw new RangeError("Plugins passed directly to the view must not have a state component");
}
//#endregion
//#region node_modules/@tiptap/pm/dist/view/index.js
var view_exports = /* @__PURE__ */ __exportAll({
	Decoration: () => Decoration,
	DecorationSet: () => DecorationSet,
	EditorView: () => EditorView,
	__endComposition: () => __endComposition,
	__parseFromClipboard: () => __parseFromClipboard
});
//#endregion
export { EditorView as i, Decoration as n, DecorationSet as r, view_exports as t };
