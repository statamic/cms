import { Node } from 'tiptap';
import Set from './Set.vue';

export default class SetNode extends Node {

    get name() {
        return 'set';
    }

    get view() {
        return Set;
    }

    get schema() {
        return {
            group: 'root',
            attrs: {
                id: {},
                enabled: { default: true },
                values: {},
            },
            draggable: true,
            parseDOM: [{
                tag: 'bard-set',
                getAttrs: dom => JSON.parse(dom.innerHTML)
            }],
            toDOM: node => ['bard-set', {}, JSON.stringify(node.attrs)]
        }
    }

    commands({ type, schema }) {
        return {
            set: attrs => (state, dispatch) => {
                const { selection } = state;
                const node = type.create(attrs);
                const transaction = state.tr.insert(selection.$cursor.pos - 1, node);
                dispatch(transaction);
            },
            setAt: ({ attrs, pos }) => (state, dispatch) => {
                const node = type.create(attrs);
                const transaction = state.tr.insert(pos, node);
                dispatch(transaction);
            },
        };
    }

}
