import { Node } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-2'
import SetComponent from './Set.vue';
import { TextSelection } from '@tiptap/pm/state';

export const Set = Node.create({

    name: 'set',

    addNodeView() {
        return VueNodeViewRenderer(SetComponent)
    },

    draggable: true,

    group: 'root',

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: element => element.querySelector('div')?.getAttribute('id'),
            },
            enabled: {
                default: true,
                parseHTML: element => element.querySelector('div')?.getAttribute('enabled'),
            },
            values: {
                default: null,
                parseHTML: element => element.querySelector('a')?.getAttribute('values'),
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'bard-set',
                getAttrs: dom => JSON.parse(dom.innerHTML)
            }
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'bard-set',
            {},
            JSON.stringify(HTMLAttributes)
        ]
    },

    addCommands() {
        return {
            set: (attrs) => ({ tr, dispatch }) => {
                const { selection } = tr;
                const node = this.type.create(attrs);
                if (dispatch) {
                    const transaction = selection instanceof TextSelection
                        ? tr.insert(selection.$cursor.pos - 1, node)
                        : tr.insert(selection.$head.pos, node);

                    dispatch(transaction);
                }
            },
            setAt: ({ attrs, pos }) => ({ tr, dispatch }) => {
                const node = this.type.create(attrs);
                if (dispatch) {
                    const transaction = tr.insert(pos, node);
                    dispatch(transaction);
                }
            }
        }
    },

})
