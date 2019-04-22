import { Extension } from 'tiptap';

export default class History extends Extension {

    get name() {
        return 'deleteSetConfirmation'
    }

    keys() {
        return {
            Backspace: backspace,
            Delete: del
        }
    }

}

function containsSet(nodes) {
    return !nodes.every(node => node.type.name !== 'set');
}

// https://github.com/ProseMirror/prosemirror-commands/blob/master/src/commands.js
function findCutBefore($pos) {
    if (!$pos.parent.type.spec.isolating) for (let i = $pos.depth - 1; i >= 0; i--) {
        if ($pos.index(i) > 0) return $pos.doc.resolve($pos.before(i + 1))
        if ($pos.node(i).type.spec.isolating) break
    }
    return null
}

function findCutAfter($pos) {
    if (!$pos.parent.type.spec.isolating) for (let i = $pos.depth - 1; i >= 0; i--) {
        let parent = $pos.node(i)
        if ($pos.index(i) + 1 < parent.childCount) return $pos.doc.resolve($pos.after(i + 1))
        if (parent.type.spec.isolating) break
    }
    return null
}

function backspace(state, dispatch, view) {
    const hasSelection = !state.selection.$cursor;
    const selectedNodes = state.selection.content().content.content;

    if (hasSelection && containsSet(selectedNodes)) {
        return !confirm('Are you sure? This will delete any selected sets.');
    }

    if (! view.endOfTextblock('backward', state)) return false;
    let cut = findCutBefore(state.selection.$cursor);
    if (! cut) return false;
    let before = cut.nodeBefore;
    if (before.type.name === 'set') {
        return !confirm('Are you sure you want to delete this set?');
    }
};

function del(state, dispatch, view) {
    const hasSelection = !state.selection.$cursor;
    const selectedNodes = state.selection.content().content.content;

    if (hasSelection && containsSet(selectedNodes)) {
        return !confirm('Are you sure? This will delete any selected sets.');
    }

    if (! view.endOfTextblock('forward', state)) return false;
    let cut = findCutAfter(state.selection.$cursor);
    if (! cut) return false;
    let after = cut.nodeAfter;
    if (after.type.name === 'set') {
        return !confirm('Are you sure you want to delete this set?');
    }
};
