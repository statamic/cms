import { Node } from 'tiptap'
import Image from './Image.vue';

export default class ImageNode extends Node {

    get name() {
        return 'image'
    }

    get view() {
        return Image;
    }

    get schema() {
        return {
            inline: true,
            attrs: {
                src: {},
                alt: { default: null },
            },
            group: 'inline',
            draggable: true,
            selectable: false,
            parseDOM: [
                {
                    tag: 'img[src]',
                    getAttrs: dom => {
                        return {
                            src: dom.getAttribute('data-src'),
                            alt: dom.getAttribute('alt'),
                        }
                    },
                },
            ],
            toDOM: node => {
                return ['img', {
                    ...node.attrs,
                    src: '',
                    'data-src': node.attrs.src,
                }]
            }
        }
    }

    commands({ type }) {
        return attrs => (state, dispatch) => {
            const { selection } = state
            const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos
            const node = type.create(attrs)
            const transaction = state.tr.insert(position, node)
            dispatch(transaction)
        }
    }

}
