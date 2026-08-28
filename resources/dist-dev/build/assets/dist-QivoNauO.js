import { a as Mark, i as Fragment, o as MarkType, p as Slice, s as Node, u as ReplaceError } from "./dist-AY222wij.js";
//#region node_modules/prosemirror-transform/dist/index.js
var lower16 = 65535;
var factor16 = Math.pow(2, 16);
function makeRecover(index, offset) {
	return index + offset * factor16;
}
function recoverIndex(value) {
	return value & lower16;
}
function recoverOffset(value) {
	return (value - (value & lower16)) / factor16;
}
var DEL_BEFORE = 1, DEL_AFTER = 2, DEL_ACROSS = 4, DEL_SIDE = 8;
/**
An object representing a mapped position with extra
information.
*/
var MapResult = class {
	/**
	@internal
	*/
	constructor(pos, delInfo, recover) {
		this.pos = pos;
		this.delInfo = delInfo;
		this.recover = recover;
	}
	/**
	Tells you whether the position was deleted, that is, whether the
	step removed the token on the side queried (via the `assoc`)
	argument from the document.
	*/
	get deleted() {
		return (this.delInfo & DEL_SIDE) > 0;
	}
	/**
	Tells you whether the token before the mapped position was deleted.
	*/
	get deletedBefore() {
		return (this.delInfo & 5) > 0;
	}
	/**
	True when the token after the mapped position was deleted.
	*/
	get deletedAfter() {
		return (this.delInfo & 6) > 0;
	}
	/**
	Tells whether any of the steps mapped through deletes across the
	position (including both the token before and after the
	position).
	*/
	get deletedAcross() {
		return (this.delInfo & DEL_ACROSS) > 0;
	}
};
/**
A map describing the deletions and insertions made by a step, which
can be used to find the correspondence between positions in the
pre-step version of a document and the same position in the
post-step version.
*/
var StepMap = class StepMap {
	/**
	Create a position map. The modifications to the document are
	represented as an array of numbers, in which each group of three
	represents a modified chunk as `[start, oldSize, newSize]`.
	*/
	constructor(ranges, inverted = false) {
		this.ranges = ranges;
		this.inverted = inverted;
		if (!ranges.length && StepMap.empty) return StepMap.empty;
	}
	/**
	@internal
	*/
	recover(value) {
		let diff = 0, index = recoverIndex(value);
		if (!this.inverted) for (let i = 0; i < index; i++) diff += this.ranges[i * 3 + 2] - this.ranges[i * 3 + 1];
		return this.ranges[index * 3] + diff + recoverOffset(value);
	}
	mapResult(pos, assoc = 1) {
		return this._map(pos, assoc, false);
	}
	map(pos, assoc = 1) {
		return this._map(pos, assoc, true);
	}
	/**
	@internal
	*/
	_map(pos, assoc, simple) {
		let diff = 0, oldIndex = this.inverted ? 2 : 1, newIndex = this.inverted ? 1 : 2;
		for (let i = 0; i < this.ranges.length; i += 3) {
			let start = this.ranges[i] - (this.inverted ? diff : 0);
			if (start > pos) break;
			let oldSize = this.ranges[i + oldIndex], newSize = this.ranges[i + newIndex], end = start + oldSize;
			if (pos <= end) {
				let side = !oldSize ? assoc : pos == start ? -1 : pos == end ? 1 : assoc;
				let result = start + diff + (side < 0 ? 0 : newSize);
				if (simple) return result;
				let recover = pos == (assoc < 0 ? start : end) ? null : makeRecover(i / 3, pos - start);
				let del = pos == start ? DEL_AFTER : pos == end ? DEL_BEFORE : DEL_ACROSS;
				if (assoc < 0 ? pos != start : pos != end) del |= DEL_SIDE;
				return new MapResult(result, del, recover);
			}
			diff += newSize - oldSize;
		}
		return simple ? pos + diff : new MapResult(pos + diff, 0, null);
	}
	/**
	@internal
	*/
	touches(pos, recover) {
		let diff = 0, index = recoverIndex(recover);
		let oldIndex = this.inverted ? 2 : 1, newIndex = this.inverted ? 1 : 2;
		for (let i = 0; i < this.ranges.length; i += 3) {
			let start = this.ranges[i] - (this.inverted ? diff : 0);
			if (start > pos) break;
			let oldSize = this.ranges[i + oldIndex];
			if (pos <= start + oldSize && i == index * 3) return true;
			diff += this.ranges[i + newIndex] - oldSize;
		}
		return false;
	}
	/**
	Calls the given function on each of the changed ranges included in
	this map.
	*/
	forEach(f) {
		let oldIndex = this.inverted ? 2 : 1, newIndex = this.inverted ? 1 : 2;
		for (let i = 0, diff = 0; i < this.ranges.length; i += 3) {
			let start = this.ranges[i], oldStart = start - (this.inverted ? diff : 0), newStart = start + (this.inverted ? 0 : diff);
			let oldSize = this.ranges[i + oldIndex], newSize = this.ranges[i + newIndex];
			f(oldStart, oldStart + oldSize, newStart, newStart + newSize);
			diff += newSize - oldSize;
		}
	}
	/**
	Create an inverted version of this map. The result can be used to
	map positions in the post-step document to the pre-step document.
	*/
	invert() {
		return new StepMap(this.ranges, !this.inverted);
	}
	/**
	@internal
	*/
	toString() {
		return (this.inverted ? "-" : "") + JSON.stringify(this.ranges);
	}
	/**
	Create a map that moves all positions by offset `n` (which may be
	negative). This can be useful when applying steps meant for a
	sub-document to a larger document, or vice-versa.
	*/
	static offset(n) {
		return n == 0 ? StepMap.empty : new StepMap(n < 0 ? [
			0,
			-n,
			0
		] : [
			0,
			0,
			n
		]);
	}
};
/**
A StepMap that contains no changed ranges.
*/
StepMap.empty = new StepMap([]);
/**
A mapping represents a pipeline of zero or more [step
maps](https://prosemirror.net/docs/ref/#transform.StepMap). It has special provisions for losslessly
handling mapping positions through a series of steps in which some
steps are inverted versions of earlier steps. (This comes up when
‘[rebasing](https://prosemirror.net/docs/guide/#transform.rebasing)’ steps for
collaboration or history management.)
*/
var Mapping = class Mapping {
	/**
	Create a new mapping with the given position maps.
	*/
	constructor(maps, mirror, from = 0, to = maps ? maps.length : 0) {
		this.mirror = mirror;
		this.from = from;
		this.to = to;
		this._maps = maps || [];
		this.ownData = !(maps || mirror);
	}
	/**
	The step maps in this mapping.
	*/
	get maps() {
		return this._maps;
	}
	/**
	Create a mapping that maps only through a part of this one.
	*/
	slice(from = 0, to = this.maps.length) {
		return new Mapping(this._maps, this.mirror, from, to);
	}
	/**
	Add a step map to the end of this mapping. If `mirrors` is
	given, it should be the index of the step map that is the mirror
	image of this one.
	*/
	appendMap(map, mirrors) {
		if (!this.ownData) {
			this._maps = this._maps.slice();
			this.mirror = this.mirror && this.mirror.slice();
			this.ownData = true;
		}
		this.to = this._maps.push(map);
		if (mirrors != null) this.setMirror(this._maps.length - 1, mirrors);
	}
	/**
	Add all the step maps in a given mapping to this one (preserving
	mirroring information).
	*/
	appendMapping(mapping) {
		for (let i = 0, startSize = this._maps.length; i < mapping._maps.length; i++) {
			let mirr = mapping.getMirror(i);
			this.appendMap(mapping._maps[i], mirr != null && mirr < i ? startSize + mirr : void 0);
		}
	}
	/**
	Finds the offset of the step map that mirrors the map at the
	given offset, in this mapping (as per the second argument to
	`appendMap`).
	*/
	getMirror(n) {
		if (this.mirror) {
			for (let i = 0; i < this.mirror.length; i++) if (this.mirror[i] == n) return this.mirror[i + (i % 2 ? -1 : 1)];
		}
	}
	/**
	@internal
	*/
	setMirror(n, m) {
		if (!this.mirror) this.mirror = [];
		this.mirror.push(n, m);
	}
	/**
	Append the inverse of the given mapping to this one.
	*/
	appendMappingInverted(mapping) {
		for (let i = mapping.maps.length - 1, totalSize = this._maps.length + mapping._maps.length; i >= 0; i--) {
			let mirr = mapping.getMirror(i);
			this.appendMap(mapping._maps[i].invert(), mirr != null && mirr > i ? totalSize - mirr - 1 : void 0);
		}
	}
	/**
	Create an inverted version of this mapping.
	*/
	invert() {
		let inverse = new Mapping();
		inverse.appendMappingInverted(this);
		return inverse;
	}
	/**
	Map a position through this mapping.
	*/
	map(pos, assoc = 1) {
		if (this.mirror) return this._map(pos, assoc, true);
		for (let i = this.from; i < this.to; i++) pos = this._maps[i].map(pos, assoc);
		return pos;
	}
	/**
	Map a position through this mapping, returning a mapping
	result.
	*/
	mapResult(pos, assoc = 1) {
		return this._map(pos, assoc, false);
	}
	/**
	@internal
	*/
	_map(pos, assoc, simple) {
		let delInfo = 0;
		for (let i = this.from; i < this.to; i++) {
			let result = this._maps[i].mapResult(pos, assoc);
			if (result.recover != null) {
				let corr = this.getMirror(i);
				if (corr != null && corr > i && corr < this.to) {
					i = corr;
					pos = this._maps[corr].recover(result.recover);
					continue;
				}
			}
			delInfo |= result.delInfo;
			pos = result.pos;
		}
		return simple ? pos : new MapResult(pos, delInfo, null);
	}
};
var stepsByID = Object.create(null);
/**
A step object represents an atomic change. It generally applies
only to the document it was created for, since the positions
stored in it will only make sense for that document.

New steps are defined by creating classes that extend `Step`,
overriding the `apply`, `invert`, `map`, `getMap` and `fromJSON`
methods, and registering your class with a unique
JSON-serialization identifier using
[`Step.jsonID`](https://prosemirror.net/docs/ref/#transform.Step^jsonID).
*/
var Step = class {
	/**
	Get the step map that represents the changes made by this step,
	and which can be used to transform between positions in the old
	and the new document.
	*/
	getMap() {
		return StepMap.empty;
	}
	/**
	Try to merge this step with another one, to be applied directly
	after it. Returns the merged step when possible, null if the
	steps can't be merged.
	*/
	merge(other) {
		return null;
	}
	/**
	Deserialize a step from its JSON representation. Will call
	through to the step class' own implementation of this method.
	*/
	static fromJSON(schema, json) {
		if (!json || !json.stepType) throw new RangeError("Invalid input for Step.fromJSON");
		let type = stepsByID[json.stepType];
		if (!type) throw new RangeError(`No step type ${json.stepType} defined`);
		return type.fromJSON(schema, json);
	}
	/**
	To be able to serialize steps to JSON, each step needs a string
	ID to attach to its JSON representation. Use this method to
	register an ID for your step classes. Try to pick something
	that's unlikely to clash with steps from other modules.
	*/
	static jsonID(id, stepClass) {
		if (id in stepsByID) throw new RangeError("Duplicate use of step JSON ID " + id);
		stepsByID[id] = stepClass;
		stepClass.prototype.jsonID = id;
		return stepClass;
	}
};
/**
The result of [applying](https://prosemirror.net/docs/ref/#transform.Step.apply) a step. Contains either a
new document or a failure value.
*/
var StepResult = class StepResult {
	/**
	@internal
	*/
	constructor(doc, failed) {
		this.doc = doc;
		this.failed = failed;
	}
	/**
	Create a successful step result.
	*/
	static ok(doc) {
		return new StepResult(doc, null);
	}
	/**
	Create a failed step result.
	*/
	static fail(message) {
		return new StepResult(null, message);
	}
	/**
	Call [`Node.replace`](https://prosemirror.net/docs/ref/#model.Node.replace) with the given
	arguments. Create a successful result if it succeeds, and a
	failed one if it throws a `ReplaceError`.
	*/
	static fromReplace(doc, from, to, slice) {
		try {
			return StepResult.ok(doc.replace(from, to, slice));
		} catch (e) {
			if (e instanceof ReplaceError) return StepResult.fail(e.message);
			throw e;
		}
	}
};
function mapFragment(fragment, f, parent) {
	let mapped = [];
	for (let i = 0; i < fragment.childCount; i++) {
		let child = fragment.child(i);
		if (child.content.size) child = child.copy(mapFragment(child.content, f, child));
		if (child.isInline) child = f(child, parent, i);
		mapped.push(child);
	}
	return Fragment.fromArray(mapped);
}
/**
Add a mark to all inline content between two positions.
*/
var AddMarkStep = class AddMarkStep extends Step {
	/**
	Create a mark step.
	*/
	constructor(from, to, mark) {
		super();
		this.from = from;
		this.to = to;
		this.mark = mark;
	}
	apply(doc) {
		let oldSlice = doc.slice(this.from, this.to), $from = doc.resolve(this.from);
		let parent = $from.node($from.sharedDepth(this.to));
		let slice = new Slice(mapFragment(oldSlice.content, (node, parent) => {
			if (!node.isAtom || !parent.type.allowsMarkType(this.mark.type)) return node;
			return node.mark(this.mark.addToSet(node.marks));
		}, parent), oldSlice.openStart, oldSlice.openEnd);
		return StepResult.fromReplace(doc, this.from, this.to, slice);
	}
	invert() {
		return new RemoveMarkStep(this.from, this.to, this.mark);
	}
	map(mapping) {
		let from = mapping.mapResult(this.from, 1), to = mapping.mapResult(this.to, -1);
		if (from.deleted && to.deleted || from.pos >= to.pos) return null;
		return new AddMarkStep(from.pos, to.pos, this.mark);
	}
	merge(other) {
		if (other instanceof AddMarkStep && other.mark.eq(this.mark) && this.from <= other.to && this.to >= other.from) return new AddMarkStep(Math.min(this.from, other.from), Math.max(this.to, other.to), this.mark);
		return null;
	}
	toJSON() {
		return {
			stepType: "addMark",
			mark: this.mark.toJSON(),
			from: this.from,
			to: this.to
		};
	}
	/**
	@internal
	*/
	static fromJSON(schema, json) {
		if (typeof json.from != "number" || typeof json.to != "number") throw new RangeError("Invalid input for AddMarkStep.fromJSON");
		return new AddMarkStep(json.from, json.to, schema.markFromJSON(json.mark));
	}
};
Step.jsonID("addMark", AddMarkStep);
/**
Remove a mark from all inline content between two positions.
*/
var RemoveMarkStep = class RemoveMarkStep extends Step {
	/**
	Create a mark-removing step.
	*/
	constructor(from, to, mark) {
		super();
		this.from = from;
		this.to = to;
		this.mark = mark;
	}
	apply(doc) {
		let oldSlice = doc.slice(this.from, this.to);
		let slice = new Slice(mapFragment(oldSlice.content, (node) => {
			return node.mark(this.mark.removeFromSet(node.marks));
		}, doc), oldSlice.openStart, oldSlice.openEnd);
		return StepResult.fromReplace(doc, this.from, this.to, slice);
	}
	invert() {
		return new AddMarkStep(this.from, this.to, this.mark);
	}
	map(mapping) {
		let from = mapping.mapResult(this.from, 1), to = mapping.mapResult(this.to, -1);
		if (from.deleted && to.deleted || from.pos >= to.pos) return null;
		return new RemoveMarkStep(from.pos, to.pos, this.mark);
	}
	merge(other) {
		if (other instanceof RemoveMarkStep && other.mark.eq(this.mark) && this.from <= other.to && this.to >= other.from) return new RemoveMarkStep(Math.min(this.from, other.from), Math.max(this.to, other.to), this.mark);
		return null;
	}
	toJSON() {
		return {
			stepType: "removeMark",
			mark: this.mark.toJSON(),
			from: this.from,
			to: this.to
		};
	}
	/**
	@internal
	*/
	static fromJSON(schema, json) {
		if (typeof json.from != "number" || typeof json.to != "number") throw new RangeError("Invalid input for RemoveMarkStep.fromJSON");
		return new RemoveMarkStep(json.from, json.to, schema.markFromJSON(json.mark));
	}
};
Step.jsonID("removeMark", RemoveMarkStep);
/**
Add a mark to a specific node.
*/
var AddNodeMarkStep = class AddNodeMarkStep extends Step {
	/**
	Create a node mark step.
	*/
	constructor(pos, mark) {
		super();
		this.pos = pos;
		this.mark = mark;
	}
	apply(doc) {
		let node = doc.nodeAt(this.pos);
		if (!node) return StepResult.fail("No node at mark step's position");
		let updated = node.type.create(node.attrs, null, this.mark.addToSet(node.marks));
		return StepResult.fromReplace(doc, this.pos, this.pos + 1, new Slice(Fragment.from(updated), 0, node.isLeaf ? 0 : 1));
	}
	invert(doc) {
		let node = doc.nodeAt(this.pos);
		if (node) {
			let newSet = this.mark.addToSet(node.marks);
			if (newSet.length == node.marks.length) {
				for (let i = 0; i < node.marks.length; i++) if (!node.marks[i].isInSet(newSet)) return new AddNodeMarkStep(this.pos, node.marks[i]);
				return new AddNodeMarkStep(this.pos, this.mark);
			}
		}
		return new RemoveNodeMarkStep(this.pos, this.mark);
	}
	map(mapping) {
		let pos = mapping.mapResult(this.pos, 1);
		return pos.deletedAfter ? null : new AddNodeMarkStep(pos.pos, this.mark);
	}
	toJSON() {
		return {
			stepType: "addNodeMark",
			pos: this.pos,
			mark: this.mark.toJSON()
		};
	}
	/**
	@internal
	*/
	static fromJSON(schema, json) {
		if (typeof json.pos != "number") throw new RangeError("Invalid input for AddNodeMarkStep.fromJSON");
		return new AddNodeMarkStep(json.pos, schema.markFromJSON(json.mark));
	}
};
Step.jsonID("addNodeMark", AddNodeMarkStep);
/**
Remove a mark from a specific node.
*/
var RemoveNodeMarkStep = class RemoveNodeMarkStep extends Step {
	/**
	Create a mark-removing step.
	*/
	constructor(pos, mark) {
		super();
		this.pos = pos;
		this.mark = mark;
	}
	apply(doc) {
		let node = doc.nodeAt(this.pos);
		if (!node) return StepResult.fail("No node at mark step's position");
		let updated = node.type.create(node.attrs, null, this.mark.removeFromSet(node.marks));
		return StepResult.fromReplace(doc, this.pos, this.pos + 1, new Slice(Fragment.from(updated), 0, node.isLeaf ? 0 : 1));
	}
	invert(doc) {
		let node = doc.nodeAt(this.pos);
		if (!node || !this.mark.isInSet(node.marks)) return this;
		return new AddNodeMarkStep(this.pos, this.mark);
	}
	map(mapping) {
		let pos = mapping.mapResult(this.pos, 1);
		return pos.deletedAfter ? null : new RemoveNodeMarkStep(pos.pos, this.mark);
	}
	toJSON() {
		return {
			stepType: "removeNodeMark",
			pos: this.pos,
			mark: this.mark.toJSON()
		};
	}
	/**
	@internal
	*/
	static fromJSON(schema, json) {
		if (typeof json.pos != "number") throw new RangeError("Invalid input for RemoveNodeMarkStep.fromJSON");
		return new RemoveNodeMarkStep(json.pos, schema.markFromJSON(json.mark));
	}
};
Step.jsonID("removeNodeMark", RemoveNodeMarkStep);
/**
Replace a part of the document with a slice of new content.
*/
var ReplaceStep = class ReplaceStep extends Step {
	/**
	The given `slice` should fit the 'gap' between `from` and
	`to`—the depths must line up, and the surrounding nodes must be
	able to be joined with the open sides of the slice. When
	`structure` is true, the step will fail if the content between
	from and to is not just a sequence of closing and then opening
	tokens (this is to guard against rebased replace steps
	overwriting something they weren't supposed to).
	*/
	constructor(from, to, slice, structure = false) {
		super();
		this.from = from;
		this.to = to;
		this.slice = slice;
		this.structure = structure;
	}
	apply(doc) {
		if (this.structure && contentBetween(doc, this.from, this.to)) return StepResult.fail("Structure replace would overwrite content");
		return StepResult.fromReplace(doc, this.from, this.to, this.slice);
	}
	getMap() {
		return new StepMap([
			this.from,
			this.to - this.from,
			this.slice.size
		]);
	}
	invert(doc) {
		return new ReplaceStep(this.from, this.from + this.slice.size, doc.slice(this.from, this.to));
	}
	map(mapping) {
		let to = mapping.mapResult(this.to, -1);
		let from = this.from == this.to && ReplaceStep.MAP_BIAS < 0 ? to : mapping.mapResult(this.from, 1);
		if (from.deletedAcross && to.deletedAcross) return null;
		return new ReplaceStep(from.pos, Math.max(from.pos, to.pos), this.slice, this.structure);
	}
	merge(other) {
		if (!(other instanceof ReplaceStep) || other.structure || this.structure) return null;
		if (this.from + this.slice.size == other.from && !this.slice.openEnd && !other.slice.openStart) {
			let slice = this.slice.size + other.slice.size == 0 ? Slice.empty : new Slice(this.slice.content.append(other.slice.content), this.slice.openStart, other.slice.openEnd);
			return new ReplaceStep(this.from, this.to + (other.to - other.from), slice, this.structure);
		} else if (other.to == this.from && !this.slice.openStart && !other.slice.openEnd) {
			let slice = this.slice.size + other.slice.size == 0 ? Slice.empty : new Slice(other.slice.content.append(this.slice.content), other.slice.openStart, this.slice.openEnd);
			return new ReplaceStep(other.from, this.to, slice, this.structure);
		} else return null;
	}
	toJSON() {
		let json = {
			stepType: "replace",
			from: this.from,
			to: this.to
		};
		if (this.slice.size) json.slice = this.slice.toJSON();
		if (this.structure) json.structure = true;
		return json;
	}
	/**
	@internal
	*/
	static fromJSON(schema, json) {
		if (typeof json.from != "number" || typeof json.to != "number") throw new RangeError("Invalid input for ReplaceStep.fromJSON");
		return new ReplaceStep(json.from, json.to, Slice.fromJSON(schema, json.slice), !!json.structure);
	}
};
/**
By default, for backwards compatibility, an inserting step
mapped over an insertion at that same position fill move after
the inserted content. In a collaborative editing situation, that
can make redone insertions appear in unexpected places. You can
set this to -1 to make such mapping keep the step before the
insertion instead.
*/
ReplaceStep.MAP_BIAS = 1;
Step.jsonID("replace", ReplaceStep);
/**
Replace a part of the document with a slice of content, but
preserve a range of the replaced content by moving it into the
slice.
*/
var ReplaceAroundStep = class ReplaceAroundStep extends Step {
	/**
	Create a replace-around step with the given range and gap.
	`insert` should be the point in the slice into which the content
	of the gap should be moved. `structure` has the same meaning as
	it has in the [`ReplaceStep`](https://prosemirror.net/docs/ref/#transform.ReplaceStep) class.
	*/
	constructor(from, to, gapFrom, gapTo, slice, insert, structure = false) {
		super();
		this.from = from;
		this.to = to;
		this.gapFrom = gapFrom;
		this.gapTo = gapTo;
		this.slice = slice;
		this.insert = insert;
		this.structure = structure;
	}
	apply(doc) {
		if (this.structure && (contentBetween(doc, this.from, this.gapFrom) || contentBetween(doc, this.gapTo, this.to))) return StepResult.fail("Structure gap-replace would overwrite content");
		let gap = doc.slice(this.gapFrom, this.gapTo);
		if (gap.openStart || gap.openEnd) return StepResult.fail("Gap is not a flat range");
		let inserted = this.slice.insertAt(this.insert, gap.content);
		if (!inserted) return StepResult.fail("Content does not fit in gap");
		return StepResult.fromReplace(doc, this.from, this.to, inserted);
	}
	getMap() {
		return new StepMap([
			this.from,
			this.gapFrom - this.from,
			this.insert,
			this.gapTo,
			this.to - this.gapTo,
			this.slice.size - this.insert
		]);
	}
	invert(doc) {
		let gap = this.gapTo - this.gapFrom;
		return new ReplaceAroundStep(this.from, this.from + this.slice.size + gap, this.from + this.insert, this.from + this.insert + gap, doc.slice(this.from, this.to).removeBetween(this.gapFrom - this.from, this.gapTo - this.from), this.gapFrom - this.from, this.structure);
	}
	map(mapping) {
		let from = mapping.mapResult(this.from, 1), to = mapping.mapResult(this.to, -1);
		let gapFrom = this.from == this.gapFrom ? from.pos : mapping.map(this.gapFrom, -1);
		let gapTo = this.to == this.gapTo ? to.pos : mapping.map(this.gapTo, 1);
		if (from.deletedAcross && to.deletedAcross || gapFrom < from.pos || gapTo > to.pos) return null;
		return new ReplaceAroundStep(from.pos, to.pos, gapFrom, gapTo, this.slice, this.insert, this.structure);
	}
	toJSON() {
		let json = {
			stepType: "replaceAround",
			from: this.from,
			to: this.to,
			gapFrom: this.gapFrom,
			gapTo: this.gapTo,
			insert: this.insert
		};
		if (this.slice.size) json.slice = this.slice.toJSON();
		if (this.structure) json.structure = true;
		return json;
	}
	/**
	@internal
	*/
	static fromJSON(schema, json) {
		if (typeof json.from != "number" || typeof json.to != "number" || typeof json.gapFrom != "number" || typeof json.gapTo != "number" || typeof json.insert != "number") throw new RangeError("Invalid input for ReplaceAroundStep.fromJSON");
		return new ReplaceAroundStep(json.from, json.to, json.gapFrom, json.gapTo, Slice.fromJSON(schema, json.slice), json.insert, !!json.structure);
	}
};
Step.jsonID("replaceAround", ReplaceAroundStep);
function contentBetween(doc, from, to) {
	let $from = doc.resolve(from), dist = to - from, depth = $from.depth;
	while (dist > 0 && depth > 0 && $from.indexAfter(depth) == $from.node(depth).childCount) {
		depth--;
		dist--;
	}
	if (dist > 0) {
		let next = $from.node(depth).maybeChild($from.indexAfter(depth));
		while (dist > 0) {
			if (!next || next.isLeaf) return true;
			next = next.firstChild;
			dist--;
		}
	}
	return false;
}
function addMark(tr, from, to, mark) {
	let removed = [], added = [];
	let removing, adding;
	tr.doc.nodesBetween(from, to, (node, pos, parent) => {
		if (!node.isInline) return;
		let marks = node.marks;
		if (!mark.isInSet(marks) && parent.type.allowsMarkType(mark.type)) {
			let start = Math.max(pos, from), end = Math.min(pos + node.nodeSize, to);
			let newSet = mark.addToSet(marks);
			for (let i = 0; i < marks.length; i++) if (!marks[i].isInSet(newSet)) if (removing && removing.to == start && removing.mark.eq(marks[i])) removing.to = end;
			else removed.push(removing = new RemoveMarkStep(start, end, marks[i]));
			if (adding && adding.to == start) adding.to = end;
			else added.push(adding = new AddMarkStep(start, end, mark));
		}
	});
	removed.forEach((s) => tr.step(s));
	added.forEach((s) => tr.step(s));
}
function removeMark(tr, from, to, mark) {
	let matched = [], step = 0;
	tr.doc.nodesBetween(from, to, (node, pos) => {
		if (!node.isInline) return;
		step++;
		let toRemove = null;
		if (mark instanceof MarkType) {
			let set = node.marks, found;
			while (found = mark.isInSet(set)) {
				(toRemove || (toRemove = [])).push(found);
				set = found.removeFromSet(set);
			}
		} else if (mark) {
			if (mark.isInSet(node.marks)) toRemove = [mark];
		} else toRemove = node.marks;
		if (toRemove && toRemove.length) {
			let end = Math.min(pos + node.nodeSize, to);
			for (let i = 0; i < toRemove.length; i++) {
				let style = toRemove[i], found;
				for (let j = 0; j < matched.length; j++) {
					let m = matched[j];
					if (m.step == step - 1 && style.eq(matched[j].style)) found = m;
				}
				if (found) {
					found.to = end;
					found.step = step;
				} else matched.push({
					style,
					from: Math.max(pos, from),
					to: end,
					step
				});
			}
		}
	});
	matched.forEach((m) => tr.step(new RemoveMarkStep(m.from, m.to, m.style)));
}
function clearIncompatible(tr, pos, parentType, match = parentType.contentMatch, clearNewlines = true) {
	let node = tr.doc.nodeAt(pos);
	let replSteps = [], cur = pos + 1;
	for (let i = 0; i < node.childCount; i++) {
		let child = node.child(i), end = cur + child.nodeSize;
		let allowed = match.matchType(child.type);
		if (!allowed) replSteps.push(new ReplaceStep(cur, end, Slice.empty));
		else {
			match = allowed;
			for (let j = 0; j < child.marks.length; j++) if (!parentType.allowsMarkType(child.marks[j].type)) tr.step(new RemoveMarkStep(cur, end, child.marks[j]));
			if (clearNewlines && child.isText && parentType.whitespace != "pre") {
				let m, newline = /\r?\n|\r/g, slice;
				while (m = newline.exec(child.text)) {
					if (!slice) slice = new Slice(Fragment.from(parentType.schema.text(" ", parentType.allowedMarks(child.marks))), 0, 0);
					replSteps.push(new ReplaceStep(cur + m.index, cur + m.index + m[0].length, slice));
				}
			}
		}
		cur = end;
	}
	if (!match.validEnd) {
		let fill = match.fillBefore(Fragment.empty, true);
		tr.replace(cur, cur, new Slice(fill, 0, 0));
	}
	for (let i = replSteps.length - 1; i >= 0; i--) tr.step(replSteps[i]);
}
function canCut(node, start, end) {
	return (start == 0 || node.canReplace(start, node.childCount)) && (end == node.childCount || node.canReplace(0, end));
}
/**
Try to find a target depth to which the content in the given range
can be lifted. Will not go across
[isolating](https://prosemirror.net/docs/ref/#model.NodeSpec.isolating) parent nodes.
*/
function liftTarget(range) {
	let content = range.parent.content.cutByIndex(range.startIndex, range.endIndex);
	for (let depth = range.depth, contentBefore = 0, contentAfter = 0;; --depth) {
		let node = range.$from.node(depth);
		let index = range.$from.index(depth) + contentBefore, endIndex = range.$to.indexAfter(depth) - contentAfter;
		if (depth < range.depth && node.canReplace(index, endIndex, content)) return depth;
		if (depth == 0 || node.type.spec.isolating || !canCut(node, index, endIndex)) break;
		if (index) contentBefore = 1;
		if (endIndex < node.childCount) contentAfter = 1;
	}
	return null;
}
function lift(tr, range, target) {
	let { $from, $to, depth } = range;
	let gapStart = $from.before(depth + 1), gapEnd = $to.after(depth + 1);
	let start = gapStart, end = gapEnd;
	let before = Fragment.empty, openStart = 0;
	for (let d = depth, splitting = false; d > target; d--) if (splitting || $from.index(d) > 0) {
		splitting = true;
		before = Fragment.from($from.node(d).copy(before));
		openStart++;
	} else start--;
	let after = Fragment.empty, openEnd = 0;
	for (let d = depth, splitting = false; d > target; d--) if (splitting || $to.after(d + 1) < $to.end(d)) {
		splitting = true;
		after = Fragment.from($to.node(d).copy(after));
		openEnd++;
	} else end++;
	tr.step(new ReplaceAroundStep(start, end, gapStart, gapEnd, new Slice(before.append(after), openStart, openEnd), before.size - openStart, true));
}
/**
Try to find a valid way to wrap the content in the given range in a
node of the given type. May introduce extra nodes around and inside
the wrapper node, if necessary. Returns null if no valid wrapping
could be found. When `innerRange` is given, that range's content is
used as the content to fit into the wrapping, instead of the
content of `range`.
*/
function findWrapping(range, nodeType, attrs = null, innerRange = range) {
	let around = findWrappingOutside(range, nodeType);
	let inner = around && findWrappingInside(innerRange, nodeType);
	if (!inner) return null;
	return around.map(withAttrs).concat({
		type: nodeType,
		attrs
	}).concat(inner.map(withAttrs));
}
function withAttrs(type) {
	return {
		type,
		attrs: null
	};
}
function findWrappingOutside(range, type) {
	let { parent, startIndex, endIndex } = range;
	let around = parent.contentMatchAt(startIndex).findWrapping(type);
	if (!around) return null;
	let outer = around.length ? around[0] : type;
	return parent.canReplaceWith(startIndex, endIndex, outer) ? around : null;
}
function findWrappingInside(range, type) {
	let { parent, startIndex, endIndex } = range;
	let inner = parent.child(startIndex);
	let inside = type.contentMatch.findWrapping(inner.type);
	if (!inside) return null;
	let innerMatch = (inside.length ? inside[inside.length - 1] : type).contentMatch;
	for (let i = startIndex; innerMatch && i < endIndex; i++) innerMatch = innerMatch.matchType(parent.child(i).type);
	if (!innerMatch || !innerMatch.validEnd) return null;
	return inside;
}
function wrap(tr, range, wrappers) {
	let content = Fragment.empty;
	for (let i = wrappers.length - 1; i >= 0; i--) {
		if (content.size) {
			let match = wrappers[i].type.contentMatch.matchFragment(content);
			if (!match || !match.validEnd) throw new RangeError("Wrapper type given to Transform.wrap does not form valid content of its parent wrapper");
		}
		content = Fragment.from(wrappers[i].type.create(wrappers[i].attrs, content));
	}
	let start = range.start, end = range.end;
	tr.step(new ReplaceAroundStep(start, end, start, end, new Slice(content, 0, 0), wrappers.length, true));
}
function setBlockType(tr, from, to, type, attrs) {
	if (!type.isTextblock) throw new RangeError("Type given to setBlockType should be a textblock");
	let mapFrom = tr.steps.length;
	tr.doc.nodesBetween(from, to, (node, pos) => {
		let attrsHere = typeof attrs == "function" ? attrs(node) : attrs;
		if (node.isTextblock && !node.hasMarkup(type, attrsHere) && canChangeType(tr.doc, tr.mapping.slice(mapFrom).map(pos), type)) {
			let convertNewlines = null;
			if (type.schema.linebreakReplacement) {
				let pre = type.whitespace == "pre", supportLinebreak = !!type.contentMatch.matchType(type.schema.linebreakReplacement);
				if (pre && !supportLinebreak) convertNewlines = false;
				else if (!pre && supportLinebreak) convertNewlines = true;
			}
			if (convertNewlines === false) replaceLinebreaks(tr, node, pos, mapFrom);
			clearIncompatible(tr, tr.mapping.slice(mapFrom).map(pos, 1), type, void 0, convertNewlines === null);
			let mapping = tr.mapping.slice(mapFrom);
			let startM = mapping.map(pos, 1), endM = mapping.map(pos + node.nodeSize, 1);
			tr.step(new ReplaceAroundStep(startM, endM, startM + 1, endM - 1, new Slice(Fragment.from(type.create(attrsHere, null, node.marks)), 0, 0), 1, true));
			if (convertNewlines === true) replaceNewlines(tr, node, pos, mapFrom);
			return false;
		}
	});
}
function replaceNewlines(tr, node, pos, mapFrom) {
	node.forEach((child, offset) => {
		if (child.isText) {
			let m, newline = /\r?\n|\r/g;
			while (m = newline.exec(child.text)) {
				let start = tr.mapping.slice(mapFrom).map(pos + 1 + offset + m.index);
				tr.replaceWith(start, start + 1, node.type.schema.linebreakReplacement.create());
			}
		}
	});
}
function replaceLinebreaks(tr, node, pos, mapFrom) {
	node.forEach((child, offset) => {
		if (child.type == child.type.schema.linebreakReplacement) {
			let start = tr.mapping.slice(mapFrom).map(pos + 1 + offset);
			tr.replaceWith(start, start + 1, node.type.schema.text("\n"));
		}
	});
}
function canChangeType(doc, pos, type) {
	let $pos = doc.resolve(pos), index = $pos.index();
	return $pos.parent.canReplaceWith(index, index + 1, type);
}
/**
Change the type, attributes, and/or marks of the node at `pos`.
When `type` isn't given, the existing node type is preserved,
*/
function setNodeMarkup(tr, pos, type, attrs, marks) {
	let node = tr.doc.nodeAt(pos);
	if (!node) throw new RangeError("No node at given position");
	if (!type) type = node.type;
	let newNode = type.create(attrs, null, marks || node.marks);
	if (node.isLeaf) return tr.replaceWith(pos, pos + node.nodeSize, newNode);
	if (!type.validContent(node.content)) throw new RangeError("Invalid content for node type " + type.name);
	tr.step(new ReplaceAroundStep(pos, pos + node.nodeSize, pos + 1, pos + node.nodeSize - 1, new Slice(Fragment.from(newNode), 0, 0), 1, true));
}
/**
Check whether splitting at the given position is allowed.
*/
function canSplit(doc, pos, depth = 1, typesAfter) {
	let $pos = doc.resolve(pos), base = $pos.depth - depth;
	let innerType = typesAfter && typesAfter[typesAfter.length - 1] || $pos.parent;
	if (base < 0 || $pos.parent.type.spec.isolating || !$pos.parent.canReplace($pos.index(), $pos.parent.childCount) || !innerType.type.validContent($pos.parent.content.cutByIndex($pos.index(), $pos.parent.childCount))) return false;
	for (let d = $pos.depth - 1, i = depth - 2; d > base; d--, i--) {
		let node = $pos.node(d), index = $pos.index(d);
		if (node.type.spec.isolating) return false;
		let rest = node.content.cutByIndex(index, node.childCount);
		let overrideChild = typesAfter && typesAfter[i + 1];
		if (overrideChild) rest = rest.replaceChild(0, overrideChild.type.create(overrideChild.attrs));
		let after = typesAfter && typesAfter[i] || node;
		if (!node.canReplace(index + 1, node.childCount) || !after.type.validContent(rest)) return false;
	}
	let index = $pos.indexAfter(base);
	let baseType = typesAfter && typesAfter[0];
	return $pos.node(base).canReplaceWith(index, index, baseType ? baseType.type : $pos.node(base + 1).type);
}
function split(tr, pos, depth = 1, typesAfter) {
	let $pos = tr.doc.resolve(pos), before = Fragment.empty, after = Fragment.empty;
	for (let d = $pos.depth, e = $pos.depth - depth, i = depth - 1; d > e; d--, i--) {
		before = Fragment.from($pos.node(d).copy(before));
		let typeAfter = typesAfter && typesAfter[i];
		after = Fragment.from(typeAfter ? typeAfter.type.create(typeAfter.attrs, after) : $pos.node(d).copy(after));
	}
	tr.step(new ReplaceStep(pos, pos, new Slice(before.append(after), depth, depth), true));
}
/**
Test whether the blocks before and after a given position can be
joined.
*/
function canJoin(doc, pos) {
	let $pos = doc.resolve(pos), index = $pos.index();
	return joinable($pos.nodeBefore, $pos.nodeAfter) && $pos.parent.canReplace(index, index + 1);
}
function canAppendWithSubstitutedLinebreaks(a, b) {
	if (!b.content.size) a.type.compatibleContent(b.type);
	let match = a.contentMatchAt(a.childCount);
	let { linebreakReplacement } = a.type.schema;
	for (let i = 0; i < b.childCount; i++) {
		let child = b.child(i);
		let type = child.type == linebreakReplacement ? a.type.schema.nodes.text : child.type;
		match = match.matchType(type);
		if (!match) return false;
		if (!a.type.allowsMarks(child.marks)) return false;
	}
	return match.validEnd;
}
function joinable(a, b) {
	return !!(a && b && !a.isLeaf && canAppendWithSubstitutedLinebreaks(a, b));
}
/**
Find an ancestor of the given position that can be joined to the
block before (or after if `dir` is positive). Returns the joinable
point, if any.
*/
function joinPoint(doc, pos, dir = -1) {
	let $pos = doc.resolve(pos);
	for (let d = $pos.depth;; d--) {
		let before, after, index = $pos.index(d);
		if (d == $pos.depth) {
			before = $pos.nodeBefore;
			after = $pos.nodeAfter;
		} else if (dir > 0) {
			before = $pos.node(d + 1);
			index++;
			after = $pos.node(d).maybeChild(index);
		} else {
			before = $pos.node(d).maybeChild(index - 1);
			after = $pos.node(d + 1);
		}
		if (before && !before.isTextblock && joinable(before, after) && $pos.node(d).canReplace(index, index + 1)) return pos;
		if (d == 0) break;
		pos = dir < 0 ? $pos.before(d) : $pos.after(d);
	}
}
function join(tr, pos, depth) {
	let convertNewlines = null;
	let { linebreakReplacement } = tr.doc.type.schema;
	let $before = tr.doc.resolve(pos - depth), beforeType = $before.node().type;
	if (linebreakReplacement && beforeType.inlineContent) {
		let pre = beforeType.whitespace == "pre";
		let supportLinebreak = !!beforeType.contentMatch.matchType(linebreakReplacement);
		if (pre && !supportLinebreak) convertNewlines = false;
		else if (!pre && supportLinebreak) convertNewlines = true;
	}
	let mapFrom = tr.steps.length;
	if (convertNewlines === false) {
		let $after = tr.doc.resolve(pos + depth);
		replaceLinebreaks(tr, $after.node(), $after.before(), mapFrom);
	}
	if (beforeType.inlineContent) clearIncompatible(tr, pos + depth - 1, beforeType, $before.node().contentMatchAt($before.index()), convertNewlines == null);
	let mapping = tr.mapping.slice(mapFrom), start = mapping.map(pos - depth);
	tr.step(new ReplaceStep(start, mapping.map(pos + depth, -1), Slice.empty, true));
	if (convertNewlines === true) {
		let $full = tr.doc.resolve(start);
		replaceNewlines(tr, $full.node(), $full.before(), tr.steps.length);
	}
	return tr;
}
/**
Try to find a point where a node of the given type can be inserted
near `pos`, by searching up the node hierarchy when `pos` itself
isn't a valid place but is at the start or end of a node. Return
null if no position was found.
*/
function insertPoint(doc, pos, nodeType) {
	let $pos = doc.resolve(pos);
	if ($pos.parent.canReplaceWith($pos.index(), $pos.index(), nodeType)) return pos;
	if ($pos.parentOffset == 0) for (let d = $pos.depth - 1; d >= 0; d--) {
		let index = $pos.index(d);
		if ($pos.node(d).canReplaceWith(index, index, nodeType)) return $pos.before(d + 1);
		if (index > 0) return null;
	}
	if ($pos.parentOffset == $pos.parent.content.size) for (let d = $pos.depth - 1; d >= 0; d--) {
		let index = $pos.indexAfter(d);
		if ($pos.node(d).canReplaceWith(index, index, nodeType)) return $pos.after(d + 1);
		if (index < $pos.node(d).childCount) return null;
	}
	return null;
}
/**
Finds a position at or around the given position where the given
slice can be inserted. Will look at parent nodes' nearest boundary
and try there, even if the original position wasn't directly at the
start or end of that node. Returns null when no position was found.
*/
function dropPoint(doc, pos, slice) {
	let $pos = doc.resolve(pos);
	if (!slice.content.size) return pos;
	let content = slice.content;
	for (let i = 0; i < slice.openStart; i++) content = content.firstChild.content;
	for (let pass = 1; pass <= (slice.openStart == 0 && slice.size ? 2 : 1); pass++) for (let d = $pos.depth; d >= 0; d--) {
		let bias = d == $pos.depth ? 0 : $pos.pos <= ($pos.start(d + 1) + $pos.end(d + 1)) / 2 ? -1 : 1;
		let insertPos = $pos.index(d) + (bias > 0 ? 1 : 0);
		let parent = $pos.node(d), fits = false;
		if (pass == 1) fits = parent.canReplace(insertPos, insertPos, content);
		else {
			let wrapping = parent.contentMatchAt(insertPos).findWrapping(content.firstChild.type);
			fits = wrapping && parent.canReplaceWith(insertPos, insertPos, wrapping[0]);
		}
		if (fits) return bias == 0 ? $pos.pos : bias < 0 ? $pos.before(d + 1) : $pos.after(d + 1);
	}
	return null;
}
/**
‘Fit’ a slice into a given position in the document, producing a
[step](https://prosemirror.net/docs/ref/#transform.Step) that inserts it. Will return null if
there's no meaningful way to insert the slice here, or inserting it
would be a no-op (an empty slice over an empty range).
*/
function replaceStep(doc, from, to = from, slice = Slice.empty) {
	if (from == to && !slice.size) return null;
	let $from = doc.resolve(from), $to = doc.resolve(to);
	if (fitsTrivially($from, $to, slice)) return new ReplaceStep(from, to, slice);
	return new Fitter($from, $to, slice).fit();
}
function fitsTrivially($from, $to, slice) {
	return !slice.openStart && !slice.openEnd && $from.start() == $to.start() && $from.parent.canReplace($from.index(), $to.index(), slice.content);
}
var Fitter = class {
	constructor($from, $to, unplaced) {
		this.$from = $from;
		this.$to = $to;
		this.unplaced = unplaced;
		this.frontier = [];
		this.placed = Fragment.empty;
		for (let i = 0; i <= $from.depth; i++) {
			let node = $from.node(i);
			this.frontier.push({
				type: node.type,
				match: node.contentMatchAt($from.indexAfter(i))
			});
		}
		for (let i = $from.depth; i > 0; i--) this.placed = Fragment.from($from.node(i).copy(this.placed));
	}
	get depth() {
		return this.frontier.length - 1;
	}
	fit() {
		while (this.unplaced.size) {
			let fit = this.findFittable();
			if (fit) this.placeNodes(fit);
			else this.openMore() || this.dropNode();
		}
		let moveInline = this.mustMoveInline(), placedSize = this.placed.size - this.depth - this.$from.depth;
		let $from = this.$from, $to = this.close(moveInline < 0 ? this.$to : $from.doc.resolve(moveInline));
		if (!$to) return null;
		let content = this.placed, openStart = $from.depth, openEnd = $to.depth;
		while (openStart && openEnd && content.childCount == 1) {
			content = content.firstChild.content;
			openStart--;
			openEnd--;
		}
		let slice = new Slice(content, openStart, openEnd);
		if (moveInline > -1) return new ReplaceAroundStep($from.pos, moveInline, this.$to.pos, this.$to.end(), slice, placedSize);
		if (slice.size || $from.pos != this.$to.pos) return new ReplaceStep($from.pos, $to.pos, slice);
		return null;
	}
	findFittable() {
		let startDepth = this.unplaced.openStart;
		for (let cur = this.unplaced.content, d = 0, openEnd = this.unplaced.openEnd; d < startDepth; d++) {
			let node = cur.firstChild;
			if (cur.childCount > 1) openEnd = 0;
			if (node.type.spec.isolating && openEnd <= d) {
				startDepth = d;
				break;
			}
			cur = node.content;
		}
		for (let pass = 1; pass <= 2; pass++) for (let sliceDepth = pass == 1 ? startDepth : this.unplaced.openStart; sliceDepth >= 0; sliceDepth--) {
			let fragment, parent = null;
			if (sliceDepth) {
				parent = contentAt(this.unplaced.content, sliceDepth - 1).firstChild;
				fragment = parent.content;
			} else fragment = this.unplaced.content;
			let first = fragment.firstChild;
			for (let frontierDepth = this.depth; frontierDepth >= 0; frontierDepth--) {
				let { type, match } = this.frontier[frontierDepth], wrap, inject = null;
				if (pass == 1 && (first ? match.matchType(first.type) || (inject = match.fillBefore(Fragment.from(first), false)) : parent && type.compatibleContent(parent.type))) return {
					sliceDepth,
					frontierDepth,
					parent,
					inject
				};
				else if (pass == 2 && first && (wrap = match.findWrapping(first.type))) return {
					sliceDepth,
					frontierDepth,
					parent,
					wrap
				};
				if (parent && match.matchType(parent.type)) break;
			}
		}
	}
	openMore() {
		let { content, openStart, openEnd } = this.unplaced;
		let inner = contentAt(content, openStart);
		if (!inner.childCount || inner.firstChild.isLeaf) return false;
		this.unplaced = new Slice(content, openStart + 1, Math.max(openEnd, inner.size + openStart >= content.size - openEnd ? openStart + 1 : 0));
		return true;
	}
	dropNode() {
		let { content, openStart, openEnd } = this.unplaced;
		let inner = contentAt(content, openStart);
		if (inner.childCount <= 1 && openStart > 0) {
			let openAtEnd = content.size - openStart <= openStart + inner.size;
			this.unplaced = new Slice(dropFromFragment(content, openStart - 1, 1), openStart - 1, openAtEnd ? openStart - 1 : openEnd);
		} else this.unplaced = new Slice(dropFromFragment(content, openStart, 1), openStart, openEnd);
	}
	placeNodes({ sliceDepth, frontierDepth, parent, inject, wrap }) {
		while (this.depth > frontierDepth) this.closeFrontierNode();
		if (wrap) for (let i = 0; i < wrap.length; i++) this.openFrontierNode(wrap[i]);
		let slice = this.unplaced, fragment = parent ? parent.content : slice.content;
		let openStart = slice.openStart - sliceDepth;
		let taken = 0, add = [];
		let { match, type } = this.frontier[frontierDepth];
		if (inject) {
			for (let i = 0; i < inject.childCount; i++) add.push(inject.child(i));
			match = match.matchFragment(inject);
		}
		let openEndCount = fragment.size + sliceDepth - (slice.content.size - slice.openEnd);
		while (taken < fragment.childCount) {
			let next = fragment.child(taken), matches = match.matchType(next.type);
			if (!matches) break;
			taken++;
			if (taken > 1 || openStart == 0 || next.content.size) {
				match = matches;
				add.push(closeNodeStart(next.mark(type.allowedMarks(next.marks)), taken == 1 ? openStart : 0, taken == fragment.childCount ? openEndCount : -1));
			}
		}
		let toEnd = taken == fragment.childCount;
		if (!toEnd) openEndCount = -1;
		this.placed = addToFragment(this.placed, frontierDepth, Fragment.from(add));
		this.frontier[frontierDepth].match = match;
		if (toEnd && openEndCount < 0 && parent && parent.type == this.frontier[this.depth].type && this.frontier.length > 1) this.closeFrontierNode();
		for (let i = 0, cur = fragment; i < openEndCount; i++) {
			let node = cur.lastChild;
			this.frontier.push({
				type: node.type,
				match: node.contentMatchAt(node.childCount)
			});
			cur = node.content;
		}
		this.unplaced = !toEnd ? new Slice(dropFromFragment(slice.content, sliceDepth, taken), slice.openStart, slice.openEnd) : sliceDepth == 0 ? Slice.empty : new Slice(dropFromFragment(slice.content, sliceDepth - 1, 1), sliceDepth - 1, openEndCount < 0 ? slice.openEnd : sliceDepth - 1);
	}
	mustMoveInline() {
		if (!this.$to.parent.isTextblock) return -1;
		let top = this.frontier[this.depth], level;
		if (!top.type.isTextblock || !contentAfterFits(this.$to, this.$to.depth, top.type, top.match, false) || this.$to.depth == this.depth && (level = this.findCloseLevel(this.$to)) && level.depth == this.depth) return -1;
		let { depth } = this.$to, after = this.$to.after(depth);
		while (depth > 1 && after == this.$to.end(--depth)) ++after;
		return after;
	}
	findCloseLevel($to) {
		scan: for (let i = Math.min(this.depth, $to.depth); i >= 0; i--) {
			let { match, type } = this.frontier[i];
			let dropInner = i < $to.depth && $to.end(i + 1) == $to.pos + ($to.depth - (i + 1));
			let fit = contentAfterFits($to, i, type, match, dropInner);
			if (!fit) continue;
			for (let d = i - 1; d >= 0; d--) {
				let { match, type } = this.frontier[d];
				let matches = contentAfterFits($to, d, type, match, true);
				if (!matches || matches.childCount) continue scan;
			}
			return {
				depth: i,
				fit,
				move: dropInner ? $to.doc.resolve($to.after(i + 1)) : $to
			};
		}
	}
	close($to) {
		let close = this.findCloseLevel($to);
		if (!close) return null;
		while (this.depth > close.depth) this.closeFrontierNode();
		if (close.fit.childCount) this.placed = addToFragment(this.placed, close.depth, close.fit);
		$to = close.move;
		for (let d = close.depth + 1; d <= $to.depth; d++) {
			let node = $to.node(d), add = node.type.contentMatch.fillBefore(node.content, true, $to.index(d));
			this.openFrontierNode(node.type, node.attrs, add);
		}
		return $to;
	}
	openFrontierNode(type, attrs = null, content) {
		let top = this.frontier[this.depth];
		top.match = top.match.matchType(type);
		this.placed = addToFragment(this.placed, this.depth, Fragment.from(type.create(attrs, content)));
		this.frontier.push({
			type,
			match: type.contentMatch
		});
	}
	closeFrontierNode() {
		let add = this.frontier.pop().match.fillBefore(Fragment.empty, true);
		if (add.childCount) this.placed = addToFragment(this.placed, this.frontier.length, add);
	}
};
function dropFromFragment(fragment, depth, count) {
	if (depth == 0) return fragment.cutByIndex(count, fragment.childCount);
	return fragment.replaceChild(0, fragment.firstChild.copy(dropFromFragment(fragment.firstChild.content, depth - 1, count)));
}
function addToFragment(fragment, depth, content) {
	if (depth == 0) return fragment.append(content);
	return fragment.replaceChild(fragment.childCount - 1, fragment.lastChild.copy(addToFragment(fragment.lastChild.content, depth - 1, content)));
}
function contentAt(fragment, depth) {
	for (let i = 0; i < depth; i++) fragment = fragment.firstChild.content;
	return fragment;
}
function closeNodeStart(node, openStart, openEnd) {
	if (openStart <= 0) return node;
	let frag = node.content;
	if (openStart > 1) frag = frag.replaceChild(0, closeNodeStart(frag.firstChild, openStart - 1, frag.childCount == 1 ? openEnd - 1 : 0));
	if (openStart > 0) {
		frag = node.type.contentMatch.fillBefore(frag).append(frag);
		if (openEnd <= 0) frag = frag.append(node.type.contentMatch.matchFragment(frag).fillBefore(Fragment.empty, true));
	}
	return node.copy(frag);
}
function contentAfterFits($to, depth, type, match, open) {
	let node = $to.node(depth), index = open ? $to.indexAfter(depth) : $to.index(depth);
	if (index == node.childCount && !type.compatibleContent(node.type)) return null;
	let fit = match.fillBefore(node.content, true, index);
	return fit && !invalidMarks(type, node.content, index) ? fit : null;
}
function invalidMarks(type, fragment, start) {
	for (let i = start; i < fragment.childCount; i++) if (!type.allowsMarks(fragment.child(i).marks)) return true;
	return false;
}
function definesContent(type) {
	return type.spec.defining || type.spec.definingForContent;
}
function replaceRange(tr, from, to, slice) {
	if (!slice.size) return tr.deleteRange(from, to);
	let $from = tr.doc.resolve(from), $to = tr.doc.resolve(to);
	if (fitsTrivially($from, $to, slice)) return tr.step(new ReplaceStep(from, to, slice));
	let targetDepths = coveredDepths($from, $to);
	if (targetDepths[targetDepths.length - 1] == 0) targetDepths.pop();
	let preferredTarget = -($from.depth + 1);
	targetDepths.unshift(preferredTarget);
	for (let d = $from.depth, pos = $from.pos - 1; d > 0; d--, pos--) {
		let spec = $from.node(d).type.spec;
		if (spec.defining || spec.definingAsContext || spec.isolating) break;
		if (targetDepths.indexOf(d) > -1) preferredTarget = d;
		else if ($from.before(d) == pos) targetDepths.splice(1, 0, -d);
	}
	let preferredTargetIndex = targetDepths.indexOf(preferredTarget);
	let leftNodes = [], preferredDepth = slice.openStart;
	for (let content = slice.content, i = 0;; i++) {
		let node = content.firstChild;
		leftNodes.push(node);
		if (i == slice.openStart) break;
		content = node.content;
	}
	for (let d = preferredDepth - 1; d >= 0; d--) {
		let leftNode = leftNodes[d], def = definesContent(leftNode.type);
		if (def && !leftNode.sameMarkup($from.node(Math.abs(preferredTarget) - 1))) preferredDepth = d;
		else if (def || !leftNode.type.isTextblock) break;
	}
	for (let j = slice.openStart; j >= 0; j--) {
		let openDepth = (j + preferredDepth + 1) % (slice.openStart + 1);
		let insert = leftNodes[openDepth];
		if (!insert) continue;
		for (let i = 0; i < targetDepths.length; i++) {
			let targetDepth = targetDepths[(i + preferredTargetIndex) % targetDepths.length], expand = true;
			if (targetDepth < 0) {
				expand = false;
				targetDepth = -targetDepth;
			}
			let parent = $from.node(targetDepth - 1), index = $from.index(targetDepth - 1);
			if (parent.canReplaceWith(index, index, insert.type, insert.marks)) return tr.replace($from.before(targetDepth), expand ? $to.after(targetDepth) : to, new Slice(closeFragment(slice.content, 0, slice.openStart, openDepth), openDepth, slice.openEnd));
		}
	}
	let startSteps = tr.steps.length;
	for (let i = targetDepths.length - 1; i >= 0; i--) {
		tr.replace(from, to, slice);
		if (tr.steps.length > startSteps) break;
		let depth = targetDepths[i];
		if (depth < 0) continue;
		from = $from.before(depth);
		to = $to.after(depth);
	}
}
function closeFragment(fragment, depth, oldOpen, newOpen, parent) {
	if (depth < oldOpen) {
		let first = fragment.firstChild;
		fragment = fragment.replaceChild(0, first.copy(closeFragment(first.content, depth + 1, oldOpen, newOpen, first)));
	}
	if (depth > newOpen) {
		let match = parent.contentMatchAt(0);
		let start = match.fillBefore(fragment).append(fragment);
		fragment = start.append(match.matchFragment(start).fillBefore(Fragment.empty, true));
	}
	return fragment;
}
function replaceRangeWith(tr, from, to, node) {
	if (!node.isInline && from == to && tr.doc.resolve(from).parent.content.size) {
		let point = insertPoint(tr.doc, from, node.type);
		if (point != null) from = to = point;
	}
	tr.replaceRange(from, to, new Slice(Fragment.from(node), 0, 0));
}
function deleteRange(tr, from, to) {
	let $from = tr.doc.resolve(from), $to = tr.doc.resolve(to);
	if ($from.parent.isTextblock && $to.parent.isTextblock && $from.start() != $to.start() && $from.parentOffset == 0 && $to.parentOffset == 0) {
		let shared = $from.sharedDepth(to), isolated = false;
		for (let d = $from.depth; d > shared; d--) if ($from.node(d).type.spec.isolating) isolated = true;
		for (let d = $to.depth; d > shared; d--) if ($to.node(d).type.spec.isolating) isolated = true;
		if (!isolated) {
			for (let d = $from.depth; d > 0 && from == $from.start(d); d--) from = $from.before(d);
			for (let d = $to.depth; d > 0 && to == $to.start(d); d--) to = $to.before(d);
			$from = tr.doc.resolve(from);
			$to = tr.doc.resolve(to);
		}
	}
	let covered = coveredDepths($from, $to);
	for (let i = 0; i < covered.length; i++) {
		let depth = covered[i], last = i == covered.length - 1;
		if (last && depth == 0 || $from.node(depth).type.contentMatch.validEnd) return tr.delete($from.start(depth), $to.end(depth));
		if (depth > 0 && (last || $from.node(depth - 1).canReplace($from.index(depth - 1), $to.indexAfter(depth - 1)))) return tr.delete($from.before(depth), $to.after(depth));
	}
	for (let d = 1; d <= $from.depth && d <= $to.depth; d++) if (from - $from.start(d) == $from.depth - d && to > $from.end(d) && $to.end(d) - to != $to.depth - d && $from.start(d - 1) == $to.start(d - 1) && $from.node(d - 1).canReplace($from.index(d - 1), $to.index(d - 1))) return tr.delete($from.before(d), to);
	tr.delete(from, to);
}
function coveredDepths($from, $to) {
	let result = [], minDepth = Math.min($from.depth, $to.depth);
	for (let d = minDepth; d >= 0; d--) {
		let start = $from.start(d);
		if (start < $from.pos - ($from.depth - d) || $to.end(d) > $to.pos + ($to.depth - d) || $from.node(d).type.spec.isolating || $to.node(d).type.spec.isolating) break;
		if (start == $to.start(d) || d == $from.depth && d == $to.depth && $from.parent.inlineContent && $to.parent.inlineContent && d && $to.start(d - 1) == start - 1) result.push(d);
	}
	return result;
}
/**
Update an attribute in a specific node.
*/
var AttrStep = class AttrStep extends Step {
	/**
	Construct an attribute step.
	*/
	constructor(pos, attr, value) {
		super();
		this.pos = pos;
		this.attr = attr;
		this.value = value;
	}
	apply(doc) {
		let node = doc.nodeAt(this.pos);
		if (!node) return StepResult.fail("No node at attribute step's position");
		let attrs = Object.create(null);
		for (let name in node.attrs) attrs[name] = node.attrs[name];
		attrs[this.attr] = this.value;
		let updated = node.type.create(attrs, null, node.marks);
		return StepResult.fromReplace(doc, this.pos, this.pos + 1, new Slice(Fragment.from(updated), 0, node.isLeaf ? 0 : 1));
	}
	getMap() {
		return StepMap.empty;
	}
	invert(doc) {
		return new AttrStep(this.pos, this.attr, doc.nodeAt(this.pos).attrs[this.attr]);
	}
	map(mapping) {
		let pos = mapping.mapResult(this.pos, 1);
		return pos.deletedAfter ? null : new AttrStep(pos.pos, this.attr, this.value);
	}
	toJSON() {
		return {
			stepType: "attr",
			pos: this.pos,
			attr: this.attr,
			value: this.value
		};
	}
	static fromJSON(schema, json) {
		if (typeof json.pos != "number" || typeof json.attr != "string") throw new RangeError("Invalid input for AttrStep.fromJSON");
		return new AttrStep(json.pos, json.attr, json.value);
	}
};
Step.jsonID("attr", AttrStep);
/**
Update an attribute in the doc node.
*/
var DocAttrStep = class DocAttrStep extends Step {
	/**
	Construct an attribute step.
	*/
	constructor(attr, value) {
		super();
		this.attr = attr;
		this.value = value;
	}
	apply(doc) {
		let attrs = Object.create(null);
		for (let name in doc.attrs) attrs[name] = doc.attrs[name];
		attrs[this.attr] = this.value;
		let updated = doc.type.create(attrs, doc.content, doc.marks);
		return StepResult.ok(updated);
	}
	getMap() {
		return StepMap.empty;
	}
	invert(doc) {
		return new DocAttrStep(this.attr, doc.attrs[this.attr]);
	}
	map(mapping) {
		return this;
	}
	toJSON() {
		return {
			stepType: "docAttr",
			attr: this.attr,
			value: this.value
		};
	}
	static fromJSON(schema, json) {
		if (typeof json.attr != "string") throw new RangeError("Invalid input for DocAttrStep.fromJSON");
		return new DocAttrStep(json.attr, json.value);
	}
};
Step.jsonID("docAttr", DocAttrStep);
/**
@internal
*/
var TransformError = class extends Error {};
TransformError = function TransformError(message) {
	let err = Error.call(this, message);
	err.__proto__ = TransformError.prototype;
	return err;
};
TransformError.prototype = Object.create(Error.prototype);
TransformError.prototype.constructor = TransformError;
TransformError.prototype.name = "TransformError";
/**
Abstraction to build up and track an array of
[steps](https://prosemirror.net/docs/ref/#transform.Step) representing a document transformation.

Most transforming methods return the `Transform` object itself, so
that they can be chained.
*/
var Transform = class {
	/**
	Create a transform that starts with the given document.
	*/
	constructor(doc) {
		this.doc = doc;
		/**
		The steps in this transform.
		*/
		this.steps = [];
		/**
		The documents before each of the steps.
		*/
		this.docs = [];
		/**
		A mapping with the maps for each of the steps in this transform.
		*/
		this.mapping = new Mapping();
	}
	/**
	The starting document.
	*/
	get before() {
		return this.docs.length ? this.docs[0] : this.doc;
	}
	/**
	Apply a new step in this transform, saving the result. Throws an
	error when the step fails.
	*/
	step(step) {
		let result = this.maybeStep(step);
		if (result.failed) throw new TransformError(result.failed);
		return this;
	}
	/**
	Try to apply a step in this transformation, ignoring it if it
	fails. Returns the step result.
	*/
	maybeStep(step) {
		let result = step.apply(this.doc);
		if (!result.failed) this.addStep(step, result.doc);
		return result;
	}
	/**
	True when the document has been changed (when there are any
	steps).
	*/
	get docChanged() {
		return this.steps.length > 0;
	}
	/**
	Return a single range, in post-transform document positions,
	that covers all content changed by this transform. Returns null
	if no replacements are made. Note that this will ignore changes
	that add/remove marks without replacing the underlying content.
	*/
	changedRange() {
		let from = 1e9, to = -1e9;
		for (let i = 0; i < this.mapping.maps.length; i++) {
			let map = this.mapping.maps[i];
			if (i) {
				from = map.map(from, 1);
				to = map.map(to, -1);
			}
			map.forEach((_f, _t, fromB, toB) => {
				from = Math.min(from, fromB);
				to = Math.max(to, toB);
			});
		}
		return from == 1e9 ? null : {
			from,
			to
		};
	}
	/**
	@internal
	*/
	addStep(step, doc) {
		this.docs.push(this.doc);
		this.steps.push(step);
		this.mapping.appendMap(step.getMap());
		this.doc = doc;
	}
	/**
	Replace the part of the document between `from` and `to` with the
	given `slice`.
	*/
	replace(from, to = from, slice = Slice.empty) {
		let step = replaceStep(this.doc, from, to, slice);
		if (step) this.step(step);
		return this;
	}
	/**
	Replace the given range with the given content, which may be a
	fragment, node, or array of nodes.
	*/
	replaceWith(from, to, content) {
		return this.replace(from, to, new Slice(Fragment.from(content), 0, 0));
	}
	/**
	Delete the content between the given positions.
	*/
	delete(from, to) {
		return this.replace(from, to, Slice.empty);
	}
	/**
	Insert the given content at the given position.
	*/
	insert(pos, content) {
		return this.replaceWith(pos, pos, content);
	}
	/**
	Replace a range of the document with a given slice, using
	`from`, `to`, and the slice's
	[`openStart`](https://prosemirror.net/docs/ref/#model.Slice.openStart) property as hints, rather
	than fixed start and end points. This method may grow the
	replaced area or close open nodes in the slice in order to get a
	fit that is more in line with WYSIWYG expectations, by dropping
	fully covered parent nodes of the replaced region when they are
	marked [non-defining as
	context](https://prosemirror.net/docs/ref/#model.NodeSpec.definingAsContext), or including an
	open parent node from the slice that _is_ marked as [defining
	its content](https://prosemirror.net/docs/ref/#model.NodeSpec.definingForContent).
	
	This is the method, for example, to handle paste. The similar
	[`replace`](https://prosemirror.net/docs/ref/#transform.Transform.replace) method is a more
	primitive tool which will _not_ move the start and end of its given
	range, and is useful in situations where you need more precise
	control over what happens.
	*/
	replaceRange(from, to, slice) {
		replaceRange(this, from, to, slice);
		return this;
	}
	/**
	Replace the given range with a node, but use `from` and `to` as
	hints, rather than precise positions. When from and to are the same
	and are at the start or end of a parent node in which the given
	node doesn't fit, this method may _move_ them out towards a parent
	that does allow the given node to be placed. When the given range
	completely covers a parent node, this method may completely replace
	that parent node.
	*/
	replaceRangeWith(from, to, node) {
		replaceRangeWith(this, from, to, node);
		return this;
	}
	/**
	Delete the given range, expanding it to cover fully covered
	parent nodes until a valid replace is found.
	*/
	deleteRange(from, to) {
		deleteRange(this, from, to);
		return this;
	}
	/**
	Split the content in the given range off from its parent, if there
	is sibling content before or after it, and move it up the tree to
	the depth specified by `target`. You'll probably want to use
	[`liftTarget`](https://prosemirror.net/docs/ref/#transform.liftTarget) to compute `target`, to make
	sure the lift is valid.
	*/
	lift(range, target) {
		lift(this, range, target);
		return this;
	}
	/**
	Join the blocks around the given position. If depth is 2, their
	last and first siblings are also joined, and so on.
	*/
	join(pos, depth = 1) {
		join(this, pos, depth);
		return this;
	}
	/**
	Wrap the given [range](https://prosemirror.net/docs/ref/#model.NodeRange) in the given set of wrappers.
	The wrappers are assumed to be valid in this position, and should
	probably be computed with [`findWrapping`](https://prosemirror.net/docs/ref/#transform.findWrapping).
	*/
	wrap(range, wrappers) {
		wrap(this, range, wrappers);
		return this;
	}
	/**
	Set the type of all textblocks (partly) between `from` and `to` to
	the given node type with the given attributes.
	*/
	setBlockType(from, to = from, type, attrs = null) {
		setBlockType(this, from, to, type, attrs);
		return this;
	}
	/**
	Change the type, attributes, and/or marks of the node at `pos`.
	When `type` isn't given, the existing node type is preserved,
	*/
	setNodeMarkup(pos, type, attrs = null, marks) {
		setNodeMarkup(this, pos, type, attrs, marks);
		return this;
	}
	/**
	Set a single attribute on a given node to a new value.
	The `pos` addresses the document content. Use `setDocAttribute`
	to set attributes on the document itself.
	*/
	setNodeAttribute(pos, attr, value) {
		this.step(new AttrStep(pos, attr, value));
		return this;
	}
	/**
	Set a single attribute on the document to a new value.
	*/
	setDocAttribute(attr, value) {
		this.step(new DocAttrStep(attr, value));
		return this;
	}
	/**
	Add a mark to the node at position `pos`.
	*/
	addNodeMark(pos, mark) {
		this.step(new AddNodeMarkStep(pos, mark));
		return this;
	}
	/**
	Remove a mark (or all marks of the given type) from the node at
	position `pos`.
	*/
	removeNodeMark(pos, mark) {
		let node = this.doc.nodeAt(pos);
		if (!node) throw new RangeError("No node at position " + pos);
		if (mark instanceof Mark) {
			if (mark.isInSet(node.marks)) this.step(new RemoveNodeMarkStep(pos, mark));
		} else {
			let set = node.marks, found, steps = [];
			while (found = mark.isInSet(set)) {
				steps.push(new RemoveNodeMarkStep(pos, found));
				set = found.removeFromSet(set);
			}
			for (let i = steps.length - 1; i >= 0; i--) this.step(steps[i]);
		}
		return this;
	}
	/**
	Split the node at the given position, and optionally, if `depth` is
	greater than one, any number of nodes above that. By default, the
	parts split off will inherit the node type of the original node.
	This can be changed by passing an array of types and attributes to
	use after the split (with the outermost nodes coming first).
	*/
	split(pos, depth = 1, typesAfter) {
		split(this, pos, depth, typesAfter);
		return this;
	}
	/**
	Add the given mark to the inline content between `from` and `to`.
	*/
	addMark(from, to, mark) {
		addMark(this, from, to, mark);
		return this;
	}
	/**
	Remove marks from inline nodes between `from` and `to`. When
	`mark` is a single mark, remove precisely that mark. When it is
	a mark type, remove all marks of that type. When it is null,
	remove all marks of any type.
	*/
	removeMark(from, to, mark) {
		removeMark(this, from, to, mark);
		return this;
	}
	/**
	Removes all marks and nodes from the content of the node at
	`pos` that don't match the given new parent node type. Accepts
	an optional starting [content match](https://prosemirror.net/docs/ref/#model.ContentMatch) as
	third argument.
	*/
	clearIncompatible(pos, parentType, match) {
		clearIncompatible(this, pos, parentType, match);
		return this;
	}
};
//#endregion
//#region node_modules/prosemirror-state/dist/index.js
var classesById = Object.create(null);
/**
Superclass for editor selections. Every selection type should
extend this. Should not be instantiated directly.
*/
var Selection = class {
	/**
	Initialize a selection with the head and anchor and ranges. If no
	ranges are given, constructs a single range across `$anchor` and
	`$head`.
	*/
	constructor($anchor, $head, ranges) {
		this.$anchor = $anchor;
		this.$head = $head;
		this.ranges = ranges || [new SelectionRange($anchor.min($head), $anchor.max($head))];
	}
	/**
	The selection's anchor, as an unresolved position.
	*/
	get anchor() {
		return this.$anchor.pos;
	}
	/**
	The selection's head.
	*/
	get head() {
		return this.$head.pos;
	}
	/**
	The lower bound of the selection's main range.
	*/
	get from() {
		return this.$from.pos;
	}
	/**
	The upper bound of the selection's main range.
	*/
	get to() {
		return this.$to.pos;
	}
	/**
	The resolved lower  bound of the selection's main range.
	*/
	get $from() {
		return this.ranges[0].$from;
	}
	/**
	The resolved upper bound of the selection's main range.
	*/
	get $to() {
		return this.ranges[0].$to;
	}
	/**
	Indicates whether the selection contains any content.
	*/
	get empty() {
		let ranges = this.ranges;
		for (let i = 0; i < ranges.length; i++) if (ranges[i].$from.pos != ranges[i].$to.pos) return false;
		return true;
	}
	/**
	Get the content of this selection as a slice.
	*/
	content() {
		return this.$from.doc.slice(this.from, this.to, true);
	}
	/**
	Replace the selection with a slice or, if no slice is given,
	delete the selection. Will append to the given transaction.
	*/
	replace(tr, content = Slice.empty) {
		let lastNode = content.content.lastChild, lastParent = null;
		for (let i = 0; i < content.openEnd; i++) {
			lastParent = lastNode;
			lastNode = lastNode.lastChild;
		}
		let mapFrom = tr.steps.length, ranges = this.ranges;
		for (let i = 0; i < ranges.length; i++) {
			let { $from, $to } = ranges[i], mapping = tr.mapping.slice(mapFrom);
			tr.replaceRange(mapping.map($from.pos), mapping.map($to.pos), i ? Slice.empty : content);
			if (i == 0) selectionToInsertionEnd(tr, mapFrom, (lastNode ? lastNode.isInline : lastParent && lastParent.isTextblock) ? -1 : 1);
		}
	}
	/**
	Replace the selection with the given node, appending the changes
	to the given transaction.
	*/
	replaceWith(tr, node) {
		let mapFrom = tr.steps.length, ranges = this.ranges;
		for (let i = 0; i < ranges.length; i++) {
			let { $from, $to } = ranges[i], mapping = tr.mapping.slice(mapFrom);
			let from = mapping.map($from.pos), to = mapping.map($to.pos);
			if (i) tr.deleteRange(from, to);
			else {
				tr.replaceRangeWith(from, to, node);
				selectionToInsertionEnd(tr, mapFrom, node.isInline ? -1 : 1);
			}
		}
	}
	/**
	Find a valid cursor or leaf node selection starting at the given
	position and searching back if `dir` is negative, and forward if
	positive. When `textOnly` is true, only consider cursor
	selections. Will return null when no valid selection position is
	found.
	*/
	static findFrom($pos, dir, textOnly = false) {
		let inner = $pos.parent.inlineContent ? new TextSelection($pos) : findSelectionIn($pos.node(0), $pos.parent, $pos.pos, $pos.index(), dir, textOnly);
		if (inner) return inner;
		for (let depth = $pos.depth - 1; depth >= 0; depth--) {
			let found = dir < 0 ? findSelectionIn($pos.node(0), $pos.node(depth), $pos.before(depth + 1), $pos.index(depth), dir, textOnly) : findSelectionIn($pos.node(0), $pos.node(depth), $pos.after(depth + 1), $pos.index(depth) + 1, dir, textOnly);
			if (found) return found;
		}
		return null;
	}
	/**
	Find a valid cursor or leaf node selection near the given
	position. Searches forward first by default, but if `bias` is
	negative, it will search backwards first.
	*/
	static near($pos, bias = 1) {
		return this.findFrom($pos, bias) || this.findFrom($pos, -bias) || new AllSelection($pos.node(0));
	}
	/**
	Find the cursor or leaf node selection closest to the start of
	the given document. Will return an
	[`AllSelection`](https://prosemirror.net/docs/ref/#state.AllSelection) if no valid position
	exists.
	*/
	static atStart(doc) {
		return findSelectionIn(doc, doc, 0, 0, 1) || new AllSelection(doc);
	}
	/**
	Find the cursor or leaf node selection closest to the end of the
	given document.
	*/
	static atEnd(doc) {
		return findSelectionIn(doc, doc, doc.content.size, doc.childCount, -1) || new AllSelection(doc);
	}
	/**
	Deserialize the JSON representation of a selection. Must be
	implemented for custom classes (as a static class method).
	*/
	static fromJSON(doc, json) {
		if (!json || !json.type) throw new RangeError("Invalid input for Selection.fromJSON");
		let cls = classesById[json.type];
		if (!cls) throw new RangeError(`No selection type ${json.type} defined`);
		return cls.fromJSON(doc, json);
	}
	/**
	To be able to deserialize selections from JSON, custom selection
	classes must register themselves with an ID string, so that they
	can be disambiguated. Try to pick something that's unlikely to
	clash with classes from other modules.
	*/
	static jsonID(id, selectionClass) {
		if (id in classesById) throw new RangeError("Duplicate use of selection JSON ID " + id);
		classesById[id] = selectionClass;
		selectionClass.prototype.jsonID = id;
		return selectionClass;
	}
	/**
	Get a [bookmark](https://prosemirror.net/docs/ref/#state.SelectionBookmark) for this selection,
	which is a value that can be mapped without having access to a
	current document, and later resolved to a real selection for a
	given document again. (This is used mostly by the history to
	track and restore old selections.) The default implementation of
	this method just converts the selection to a text selection and
	returns the bookmark for that.
	*/
	getBookmark() {
		return TextSelection.between(this.$anchor, this.$head).getBookmark();
	}
};
Selection.prototype.visible = true;
/**
Represents a selected range in a document.
*/
var SelectionRange = class {
	/**
	Create a range.
	*/
	constructor($from, $to) {
		this.$from = $from;
		this.$to = $to;
	}
};
var warnedAboutTextSelection = false;
function checkTextSelection($pos) {
	if (!warnedAboutTextSelection && !$pos.parent.inlineContent) {
		warnedAboutTextSelection = true;
		console["warn"]("TextSelection endpoint not pointing into a node with inline content (" + $pos.parent.type.name + ")");
	}
}
/**
A text selection represents a classical editor selection, with a
head (the moving side) and anchor (immobile side), both of which
point into textblock nodes. It can be empty (a regular cursor
position).
*/
var TextSelection = class TextSelection extends Selection {
	/**
	Construct a text selection between the given points.
	*/
	constructor($anchor, $head = $anchor) {
		checkTextSelection($anchor);
		checkTextSelection($head);
		super($anchor, $head);
	}
	/**
	Returns a resolved position if this is a cursor selection (an
	empty text selection), and null otherwise.
	*/
	get $cursor() {
		return this.$anchor.pos == this.$head.pos ? this.$head : null;
	}
	map(doc, mapping) {
		let $head = doc.resolve(mapping.map(this.head));
		if (!$head.parent.inlineContent) return Selection.near($head);
		let $anchor = doc.resolve(mapping.map(this.anchor));
		return new TextSelection($anchor.parent.inlineContent ? $anchor : $head, $head);
	}
	replace(tr, content = Slice.empty) {
		super.replace(tr, content);
		if (content == Slice.empty) {
			let marks = this.$from.marksAcross(this.$to);
			if (marks) tr.ensureMarks(marks);
		}
	}
	eq(other) {
		return other instanceof TextSelection && other.anchor == this.anchor && other.head == this.head;
	}
	getBookmark() {
		return new TextBookmark(this.anchor, this.head);
	}
	toJSON() {
		return {
			type: "text",
			anchor: this.anchor,
			head: this.head
		};
	}
	/**
	@internal
	*/
	static fromJSON(doc, json) {
		if (typeof json.anchor != "number" || typeof json.head != "number") throw new RangeError("Invalid input for TextSelection.fromJSON");
		return new TextSelection(doc.resolve(json.anchor), doc.resolve(json.head));
	}
	/**
	Create a text selection from non-resolved positions.
	*/
	static create(doc, anchor, head = anchor) {
		let $anchor = doc.resolve(anchor);
		return new this($anchor, head == anchor ? $anchor : doc.resolve(head));
	}
	/**
	Return a text selection that spans the given positions or, if
	they aren't text positions, find a text selection near them.
	`bias` determines whether the method searches forward (default)
	or backwards (negative number) first. Will fall back to calling
	[`Selection.near`](https://prosemirror.net/docs/ref/#state.Selection^near) when the document
	doesn't contain a valid text position.
	*/
	static between($anchor, $head, bias) {
		let dPos = $anchor.pos - $head.pos;
		if (!bias || dPos) bias = dPos >= 0 ? 1 : -1;
		if (!$head.parent.inlineContent) {
			let found = Selection.findFrom($head, bias, true) || Selection.findFrom($head, -bias, true);
			if (found) $head = found.$head;
			else return Selection.near($head, bias);
		}
		if (!$anchor.parent.inlineContent) if (dPos == 0) $anchor = $head;
		else {
			$anchor = (Selection.findFrom($anchor, -bias, true) || Selection.findFrom($anchor, bias, true)).$anchor;
			if ($anchor.pos < $head.pos != dPos < 0) $anchor = $head;
		}
		return new TextSelection($anchor, $head);
	}
};
Selection.jsonID("text", TextSelection);
var TextBookmark = class TextBookmark {
	constructor(anchor, head) {
		this.anchor = anchor;
		this.head = head;
	}
	map(mapping) {
		return new TextBookmark(mapping.map(this.anchor), mapping.map(this.head));
	}
	resolve(doc) {
		return TextSelection.between(doc.resolve(this.anchor), doc.resolve(this.head));
	}
};
/**
A node selection is a selection that points at a single node. All
nodes marked [selectable](https://prosemirror.net/docs/ref/#model.NodeSpec.selectable) can be the
target of a node selection. In such a selection, `from` and `to`
point directly before and after the selected node, `anchor` equals
`from`, and `head` equals `to`..
*/
var NodeSelection = class NodeSelection extends Selection {
	/**
	Create a node selection. Does not verify the validity of its
	argument.
	*/
	constructor($pos) {
		let node = $pos.nodeAfter;
		let $end = $pos.node(0).resolve($pos.pos + node.nodeSize);
		super($pos, $end);
		this.node = node;
	}
	map(doc, mapping) {
		let { deleted, pos } = mapping.mapResult(this.anchor);
		let $pos = doc.resolve(pos);
		if (deleted) return Selection.near($pos);
		return new NodeSelection($pos);
	}
	content() {
		return new Slice(Fragment.from(this.node), 0, 0);
	}
	eq(other) {
		return other instanceof NodeSelection && other.anchor == this.anchor;
	}
	toJSON() {
		return {
			type: "node",
			anchor: this.anchor
		};
	}
	getBookmark() {
		return new NodeBookmark(this.anchor);
	}
	/**
	@internal
	*/
	static fromJSON(doc, json) {
		if (typeof json.anchor != "number") throw new RangeError("Invalid input for NodeSelection.fromJSON");
		return new NodeSelection(doc.resolve(json.anchor));
	}
	/**
	Create a node selection from non-resolved positions.
	*/
	static create(doc, from) {
		return new NodeSelection(doc.resolve(from));
	}
	/**
	Determines whether the given node may be selected as a node
	selection.
	*/
	static isSelectable(node) {
		return !node.isText && node.type.spec.selectable !== false;
	}
};
NodeSelection.prototype.visible = false;
Selection.jsonID("node", NodeSelection);
var NodeBookmark = class NodeBookmark {
	constructor(anchor) {
		this.anchor = anchor;
	}
	map(mapping) {
		let { deleted, pos } = mapping.mapResult(this.anchor);
		return deleted ? new TextBookmark(pos, pos) : new NodeBookmark(pos);
	}
	resolve(doc) {
		let $pos = doc.resolve(this.anchor), node = $pos.nodeAfter;
		if (node && NodeSelection.isSelectable(node)) return new NodeSelection($pos);
		return Selection.near($pos);
	}
};
/**
A selection type that represents selecting the whole document
(which can not necessarily be expressed with a text selection, when
there are for example leaf block nodes at the start or end of the
document).
*/
var AllSelection = class AllSelection extends Selection {
	/**
	Create an all-selection over the given document.
	*/
	constructor(doc) {
		super(doc.resolve(0), doc.resolve(doc.content.size));
	}
	replace(tr, content = Slice.empty) {
		if (content == Slice.empty) {
			tr.delete(0, tr.doc.content.size);
			let sel = Selection.atStart(tr.doc);
			if (!sel.eq(tr.selection)) tr.setSelection(sel);
		} else super.replace(tr, content);
	}
	toJSON() {
		return { type: "all" };
	}
	/**
	@internal
	*/
	static fromJSON(doc) {
		return new AllSelection(doc);
	}
	map(doc) {
		return new AllSelection(doc);
	}
	eq(other) {
		return other instanceof AllSelection;
	}
	getBookmark() {
		return AllBookmark;
	}
};
Selection.jsonID("all", AllSelection);
var AllBookmark = {
	map() {
		return this;
	},
	resolve(doc) {
		return new AllSelection(doc);
	}
};
function findSelectionIn(doc, node, pos, index, dir, text = false) {
	if (node.inlineContent) return TextSelection.create(doc, pos);
	for (let i = index - (dir > 0 ? 0 : 1); dir > 0 ? i < node.childCount : i >= 0; i += dir) {
		let child = node.child(i);
		if (!child.isAtom) {
			let inner = findSelectionIn(doc, child, pos + dir, dir < 0 ? child.childCount : 0, dir, text);
			if (inner) return inner;
		} else if (!text && NodeSelection.isSelectable(child)) return NodeSelection.create(doc, pos - (dir < 0 ? child.nodeSize : 0));
		pos += child.nodeSize * dir;
	}
	return null;
}
function selectionToInsertionEnd(tr, startLen, bias) {
	let last = tr.steps.length - 1;
	if (last < startLen) return;
	let step = tr.steps[last];
	if (!(step instanceof ReplaceStep || step instanceof ReplaceAroundStep)) return;
	let map = tr.mapping.maps[last], end;
	map.forEach((_from, _to, _newFrom, newTo) => {
		if (end == null) end = newTo;
	});
	tr.setSelection(Selection.near(tr.doc.resolve(end), bias));
}
var UPDATED_SEL = 1, UPDATED_MARKS = 2, UPDATED_SCROLL = 4;
/**
An editor state transaction, which can be applied to a state to
create an updated state. Use
[`EditorState.tr`](https://prosemirror.net/docs/ref/#state.EditorState.tr) to create an instance.

Transactions track changes to the document (they are a subclass of
[`Transform`](https://prosemirror.net/docs/ref/#transform.Transform)), but also other state changes,
like selection updates and adjustments of the set of [stored
marks](https://prosemirror.net/docs/ref/#state.EditorState.storedMarks). In addition, you can store
metadata properties in a transaction, which are extra pieces of
information that client code or plugins can use to describe what a
transaction represents, so that they can update their [own
state](https://prosemirror.net/docs/ref/#state.StateField) accordingly.

The [editor view](https://prosemirror.net/docs/ref/#view.EditorView) uses a few metadata
properties: it will attach a property `"pointer"` with the value
`true` to selection transactions directly caused by mouse or touch
input, a `"composition"` property holding an ID identifying the
composition that caused it to transactions caused by composed DOM
input, and a `"uiEvent"` property of that may be `"paste"`,
`"cut"`, or `"drop"`.
*/
var Transaction = class extends Transform {
	/**
	@internal
	*/
	constructor(state) {
		super(state.doc);
		this.curSelectionFor = 0;
		this.updated = 0;
		this.meta = Object.create(null);
		this.time = Date.now();
		this.curSelection = state.selection;
		this.storedMarks = state.storedMarks;
	}
	/**
	The transaction's current selection. This defaults to the editor
	selection [mapped](https://prosemirror.net/docs/ref/#state.Selection.map) through the steps in the
	transaction, but can be overwritten with
	[`setSelection`](https://prosemirror.net/docs/ref/#state.Transaction.setSelection).
	*/
	get selection() {
		if (this.curSelectionFor < this.steps.length) {
			this.curSelection = this.curSelection.map(this.doc, this.mapping.slice(this.curSelectionFor));
			this.curSelectionFor = this.steps.length;
		}
		return this.curSelection;
	}
	/**
	Update the transaction's current selection. Will determine the
	selection that the editor gets when the transaction is applied.
	*/
	setSelection(selection) {
		if (selection.$from.doc != this.doc) throw new RangeError("Selection passed to setSelection must point at the current document");
		this.curSelection = selection;
		this.curSelectionFor = this.steps.length;
		this.updated = (this.updated | UPDATED_SEL) & -3;
		this.storedMarks = null;
		return this;
	}
	/**
	Whether the selection was explicitly updated by this transaction.
	*/
	get selectionSet() {
		return (this.updated & UPDATED_SEL) > 0;
	}
	/**
	Set the current stored marks.
	*/
	setStoredMarks(marks) {
		this.storedMarks = marks;
		this.updated |= UPDATED_MARKS;
		return this;
	}
	/**
	Make sure the current stored marks or, if that is null, the marks
	at the selection, match the given set of marks. Does nothing if
	this is already the case.
	*/
	ensureMarks(marks) {
		if (!Mark.sameSet(this.storedMarks || this.selection.$from.marks(), marks)) this.setStoredMarks(marks);
		return this;
	}
	/**
	Add a mark to the set of stored marks.
	*/
	addStoredMark(mark) {
		return this.ensureMarks(mark.addToSet(this.storedMarks || this.selection.$head.marks()));
	}
	/**
	Remove a mark or mark type from the set of stored marks.
	*/
	removeStoredMark(mark) {
		return this.ensureMarks(mark.removeFromSet(this.storedMarks || this.selection.$head.marks()));
	}
	/**
	Whether the stored marks were explicitly set for this transaction.
	*/
	get storedMarksSet() {
		return (this.updated & UPDATED_MARKS) > 0;
	}
	/**
	@internal
	*/
	addStep(step, doc) {
		super.addStep(step, doc);
		this.updated = this.updated & -3;
		this.storedMarks = null;
	}
	/**
	Update the timestamp for the transaction.
	*/
	setTime(time) {
		this.time = time;
		return this;
	}
	/**
	Replace the current selection with the given slice.
	*/
	replaceSelection(slice) {
		this.selection.replace(this, slice);
		return this;
	}
	/**
	Replace the selection with the given node. When `inheritMarks` is
	true and the content is inline, it inherits the marks from the
	place where it is inserted.
	*/
	replaceSelectionWith(node, inheritMarks = true) {
		let selection = this.selection;
		if (inheritMarks) node = node.mark(this.storedMarks || (selection.empty ? selection.$from.marks() : selection.$from.marksAcross(selection.$to) || Mark.none));
		selection.replaceWith(this, node);
		return this;
	}
	/**
	Delete the selection.
	*/
	deleteSelection() {
		this.selection.replace(this);
		return this;
	}
	/**
	Replace the given range, or the selection if no range is given,
	with a text node containing the given string.
	*/
	insertText(text, from, to) {
		let schema = this.doc.type.schema;
		if (from == null) {
			if (!text) return this.deleteSelection();
			return this.replaceSelectionWith(schema.text(text), true);
		} else {
			if (to == null) to = from;
			if (!text) return this.deleteRange(from, to);
			let marks = this.storedMarks;
			if (!marks) {
				let $from = this.doc.resolve(from);
				marks = to == from ? $from.marks() : $from.marksAcross(this.doc.resolve(to));
			}
			this.replaceRangeWith(from, to, schema.text(text, marks));
			if (!this.selection.empty && this.selection.to == from + text.length) this.setSelection(Selection.near(this.selection.$to));
			return this;
		}
	}
	/**
	Store a metadata property in this transaction, keyed either by
	name or by plugin.
	*/
	setMeta(key, value) {
		this.meta[typeof key == "string" ? key : key.key] = value;
		return this;
	}
	/**
	Retrieve a metadata property for a given name or plugin.
	*/
	getMeta(key) {
		return this.meta[typeof key == "string" ? key : key.key];
	}
	/**
	Returns true if this transaction doesn't contain any metadata,
	and can thus safely be extended.
	*/
	get isGeneric() {
		for (let _ in this.meta) return false;
		return true;
	}
	/**
	Indicate that the editor should scroll the selection into view
	when updated to the state produced by this transaction.
	*/
	scrollIntoView() {
		this.updated |= UPDATED_SCROLL;
		return this;
	}
	/**
	True when this transaction has had `scrollIntoView` called on it.
	*/
	get scrolledIntoView() {
		return (this.updated & UPDATED_SCROLL) > 0;
	}
};
function bind(f, self) {
	return !self || !f ? f : f.bind(self);
}
var FieldDesc = class {
	constructor(name, desc, self) {
		this.name = name;
		this.init = bind(desc.init, self);
		this.apply = bind(desc.apply, self);
	}
};
var baseFields = [
	new FieldDesc("doc", {
		init(config) {
			return config.doc || config.schema.topNodeType.createAndFill();
		},
		apply(tr) {
			return tr.doc;
		}
	}),
	new FieldDesc("selection", {
		init(config, instance) {
			return config.selection || Selection.atStart(instance.doc);
		},
		apply(tr) {
			return tr.selection;
		}
	}),
	new FieldDesc("storedMarks", {
		init(config) {
			return config.storedMarks || null;
		},
		apply(tr, _marks, _old, state) {
			return state.selection.$cursor ? tr.storedMarks : null;
		}
	}),
	new FieldDesc("scrollToSelection", {
		init() {
			return 0;
		},
		apply(tr, prev) {
			return tr.scrolledIntoView ? prev + 1 : prev;
		}
	})
];
var Configuration = class {
	constructor(schema, plugins) {
		this.schema = schema;
		this.plugins = [];
		this.pluginsByKey = Object.create(null);
		this.fields = baseFields.slice();
		if (plugins) plugins.forEach((plugin) => {
			if (this.pluginsByKey[plugin.key]) throw new RangeError("Adding different instances of a keyed plugin (" + plugin.key + ")");
			this.plugins.push(plugin);
			this.pluginsByKey[plugin.key] = plugin;
			if (plugin.spec.state) this.fields.push(new FieldDesc(plugin.key, plugin.spec.state, plugin));
		});
	}
};
/**
The state of a ProseMirror editor is represented by an object of
this type. A state is a persistent data structure—it isn't
updated, but rather a new state value is computed from an old one
using the [`apply`](https://prosemirror.net/docs/ref/#state.EditorState.apply) method.

A state holds a number of built-in fields, and plugins can
[define](https://prosemirror.net/docs/ref/#state.PluginSpec.state) additional fields.
*/
var EditorState = class EditorState {
	/**
	@internal
	*/
	constructor(config) {
		this.config = config;
	}
	/**
	The schema of the state's document.
	*/
	get schema() {
		return this.config.schema;
	}
	/**
	The plugins that are active in this state.
	*/
	get plugins() {
		return this.config.plugins;
	}
	/**
	Apply the given transaction to produce a new state.
	*/
	apply(tr) {
		return this.applyTransaction(tr).state;
	}
	/**
	@internal
	*/
	filterTransaction(tr, ignore = -1) {
		for (let i = 0; i < this.config.plugins.length; i++) if (i != ignore) {
			let plugin = this.config.plugins[i];
			if (plugin.spec.filterTransaction && !plugin.spec.filterTransaction.call(plugin, tr, this)) return false;
		}
		return true;
	}
	/**
	Verbose variant of [`apply`](https://prosemirror.net/docs/ref/#state.EditorState.apply) that
	returns the precise transactions that were applied (which might
	be influenced by the [transaction
	hooks](https://prosemirror.net/docs/ref/#state.PluginSpec.filterTransaction) of
	plugins) along with the new state.
	*/
	applyTransaction(rootTr) {
		if (!this.filterTransaction(rootTr)) return {
			state: this,
			transactions: []
		};
		let trs = [rootTr], newState = this.applyInner(rootTr), seen = null;
		for (;;) {
			let haveNew = false;
			for (let i = 0; i < this.config.plugins.length; i++) {
				let plugin = this.config.plugins[i];
				if (plugin.spec.appendTransaction) {
					let n = seen ? seen[i].n : 0, oldState = seen ? seen[i].state : this;
					let tr = n < trs.length && plugin.spec.appendTransaction.call(plugin, n ? trs.slice(n) : trs, oldState, newState);
					if (tr && newState.filterTransaction(tr, i)) {
						tr.setMeta("appendedTransaction", rootTr);
						if (!seen) {
							seen = [];
							for (let j = 0; j < this.config.plugins.length; j++) seen.push(j < i ? {
								state: newState,
								n: trs.length
							} : {
								state: this,
								n: 0
							});
						}
						trs.push(tr);
						newState = newState.applyInner(tr);
						haveNew = true;
					}
					if (seen) seen[i] = {
						state: newState,
						n: trs.length
					};
				}
			}
			if (!haveNew) return {
				state: newState,
				transactions: trs
			};
		}
	}
	/**
	@internal
	*/
	applyInner(tr) {
		if (!tr.before.eq(this.doc)) throw new RangeError("Applying a mismatched transaction");
		let newInstance = new EditorState(this.config), fields = this.config.fields;
		for (let i = 0; i < fields.length; i++) {
			let field = fields[i];
			newInstance[field.name] = field.apply(tr, this[field.name], this, newInstance);
		}
		return newInstance;
	}
	/**
	Accessor that constructs and returns a new [transaction](https://prosemirror.net/docs/ref/#state.Transaction) from this state.
	*/
	get tr() {
		return new Transaction(this);
	}
	/**
	Create a new state.
	*/
	static create(config) {
		let $config = new Configuration(config.doc ? config.doc.type.schema : config.schema, config.plugins);
		let instance = new EditorState($config);
		for (let i = 0; i < $config.fields.length; i++) instance[$config.fields[i].name] = $config.fields[i].init(config, instance);
		return instance;
	}
	/**
	Create a new state based on this one, but with an adjusted set
	of active plugins. State fields that exist in both sets of
	plugins are kept unchanged. Those that no longer exist are
	dropped, and those that are new are initialized using their
	[`init`](https://prosemirror.net/docs/ref/#state.StateField.init) method, passing in the new
	configuration object..
	*/
	reconfigure(config) {
		let $config = new Configuration(this.schema, config.plugins);
		let fields = $config.fields, instance = new EditorState($config);
		for (let i = 0; i < fields.length; i++) {
			let name = fields[i].name;
			instance[name] = this.hasOwnProperty(name) ? this[name] : fields[i].init(config, instance);
		}
		return instance;
	}
	/**
	Serialize this state to JSON. If you want to serialize the state
	of plugins, pass an object mapping property names to use in the
	resulting JSON object to plugin objects. The argument may also be
	a string or number, in which case it is ignored, to support the
	way `JSON.stringify` calls `toString` methods.
	*/
	toJSON(pluginFields) {
		let result = {
			doc: this.doc.toJSON(),
			selection: this.selection.toJSON()
		};
		if (this.storedMarks) result.storedMarks = this.storedMarks.map((m) => m.toJSON());
		if (pluginFields && typeof pluginFields == "object") for (let prop in pluginFields) {
			if (prop == "doc" || prop == "selection") throw new RangeError("The JSON fields `doc` and `selection` are reserved");
			let plugin = pluginFields[prop], state = plugin.spec.state;
			if (state && state.toJSON) result[prop] = state.toJSON.call(plugin, this[plugin.key]);
		}
		return result;
	}
	/**
	Deserialize a JSON representation of a state. `config` should
	have at least a `schema` field, and should contain array of
	plugins to initialize the state with. `pluginFields` can be used
	to deserialize the state of plugins, by associating plugin
	instances with the property names they use in the JSON object.
	*/
	static fromJSON(config, json, pluginFields) {
		if (!json) throw new RangeError("Invalid input for EditorState.fromJSON");
		if (!config.schema) throw new RangeError("Required config field 'schema' missing");
		let $config = new Configuration(config.schema, config.plugins);
		let instance = new EditorState($config);
		$config.fields.forEach((field) => {
			if (field.name == "doc") instance.doc = Node.fromJSON(config.schema, json.doc);
			else if (field.name == "selection") instance.selection = Selection.fromJSON(instance.doc, json.selection);
			else if (field.name == "storedMarks") {
				if (json.storedMarks) instance.storedMarks = json.storedMarks.map(config.schema.markFromJSON);
			} else {
				if (pluginFields) for (let prop in pluginFields) {
					let plugin = pluginFields[prop], state = plugin.spec.state;
					if (plugin.key == field.name && state && state.fromJSON && Object.prototype.hasOwnProperty.call(json, prop)) {
						instance[field.name] = state.fromJSON.call(plugin, config, json[prop], instance);
						return;
					}
				}
				instance[field.name] = field.init(config, instance);
			}
		});
		return instance;
	}
};
function bindProps(obj, self, target) {
	for (let prop in obj) {
		let val = obj[prop];
		if (val instanceof Function) val = val.bind(self);
		else if (prop == "handleDOMEvents") val = bindProps(val, self, {});
		target[prop] = val;
	}
	return target;
}
/**
Plugins bundle functionality that can be added to an editor.
They are part of the [editor state](https://prosemirror.net/docs/ref/#state.EditorState) and
may influence that state and the view that contains it.
*/
var Plugin = class {
	/**
	Create a plugin.
	*/
	constructor(spec) {
		this.spec = spec;
		/**
		The [props](https://prosemirror.net/docs/ref/#view.EditorProps) exported by this plugin.
		*/
		this.props = {};
		if (spec.props) bindProps(spec.props, this, this.props);
		this.key = spec.key ? spec.key.key : createKey("plugin");
	}
	/**
	Extract the plugin's state field from an editor state.
	*/
	getState(state) {
		return state[this.key];
	}
};
var keys = Object.create(null);
function createKey(name) {
	if (name in keys) return name + "$" + ++keys[name];
	keys[name] = 0;
	return name + "$";
}
/**
A key is used to [tag](https://prosemirror.net/docs/ref/#state.PluginSpec.key) plugins in a way
that makes it possible to find them, given an editor state.
Assigning a key does mean only one plugin of that type can be
active in a state.
*/
var PluginKey = class {
	/**
	Create a plugin key.
	*/
	constructor(name = "key") {
		this.key = createKey(name);
	}
	/**
	Get the active plugin with this key, if any, from an editor
	state.
	*/
	get(state) {
		return state.config.pluginsByKey[this.key];
	}
	/**
	Get the plugin's state from an editor state.
	*/
	getState(state) {
		return state[this.key];
	}
};
//#endregion
export { dropPoint as _, PluginKey as a, liftTarget as b, TextSelection as c, RemoveMarkStep as d, ReplaceAroundStep as f, canSplit as g, canJoin as h, Plugin as i, Transaction as l, Transform as m, EditorState as n, Selection as o, ReplaceStep as p, NodeSelection as r, SelectionRange as s, AllSelection as t, Mapping as u, findWrapping as v, replaceStep as x, joinPoint as y };
