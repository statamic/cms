import { Node, Plugin } from 'tiptap'
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

    stopEvent(event) {
        const draggable = !!this.schema.draggable
        if (draggable && (event instanceof DragEvent)) {
            return false
        }

        return true;
    }

    get plugins() {
        const bard = this.options.bard;
        return [
            new Plugin({
                props: {
                    handleClick(view, pos) {
                        // Any click is not on an image, because we are stopping all events in the stopEvent method above.
                        // This is almost definitely temporary.
                        bard.$emit('image-deselected')
                    }
                }
            })
        ]
    }

}
