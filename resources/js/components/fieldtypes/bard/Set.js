import { Node } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Slice, Fragment } from '@tiptap/pm/model';
import { Decoration, DecorationSet } from "@tiptap/pm/view";
import { VueNodeViewRenderer } from '@tiptap/vue-3'
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

    addProseMirrorPlugins() {
        const bard = this.options.bard;
        const type = this.type;
        return [
            new Plugin({
                key: new PluginKey('setSelectionDecorator'),
                props: {
                    decorations(state) {
                        const { from, to } = state.selection;
                        const decorations = [];
                        state.doc.nodesBetween(from, to, (node, pos) => {
                            if (node.type === type) {
                                decorations.push(Decoration.node(pos, pos + node.nodeSize, {}, {
                                    withinSelection: true,
                                }));
                            }
                        });
                        return DecorationSet.create(state.doc, decorations);
                    }
                },
            }),
            new Plugin({
                key: new PluginKey('setPastedTransformer'),
                props: {
                    transformPasted: (slice) => {
                        const { content } = slice.content;
                        return new Slice(Fragment.fromArray(content.map(node => {
                            if (node.type === type) {
                                return node.type.create(bard.pasteSet(node.attrs));
                            }
                            return node.copy(node.content);
                        })), slice.openStart, slice.openEnd);
                    },
                },
            }),
        ]
    },

})
