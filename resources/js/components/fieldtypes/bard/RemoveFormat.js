import { Extension } from 'tiptap';
import { Transform } from 'prosemirror-transform';

export default class RemoveFormat extends Extension {

    get name() {
        return 'remove_format'
    }

    commands({ type, schema }) {
        return attrs => (state, dispatch) => {
            let tr = state.tr;
            tr.removeMark(state.selection.from, state.selection.to);
            dispatch(tr);
        };
    }

}
