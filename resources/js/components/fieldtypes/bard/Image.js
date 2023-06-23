import { Node } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-2'
import ImageComponent from './Image.vue';

export const Image = Node.create({

    name: 'image',

    addNodeView() {
        return VueNodeViewRenderer(ImageComponent)
    },

    inline: true,

    group: 'inline',

    draggable: true,

    selectable: false,

    addAttributes() {
        return {
            src: {
                default: null,
                parseHTML: element => element.querySelector('img')?.getAttribute('data-src'),
            },
            alt: {
                default: null,
                parseHTML: element => element.querySelector('img')?.getAttribute('alt'),
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'img[src]',
                getAttrs: dom => {
                    return {
                        src: dom.getAttribute('data-src'),
                        alt: dom.getAttribute('alt'),
                    }
                }
            }
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'img',
            {
                ...HTMLAttributes,
                src: '',
                'data-src': HTMLAttributes.src,
            }
        ]
    },

    addCommands() {
        return {
            insertImage: (attrs) => ({ tr, dispatch }) => {
                const { selection } = tr;
                const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos;
                const node = this.type.create(attrs);
                node.isNew = true;
                if (dispatch) {
                    const transaction = tr.insert(position, node);
                    dispatch(transaction);
                }
            },
        }
    },

})
