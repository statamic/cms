import { Mark, Plugin, TextSelection } from 'tiptap'
import { updateMark, removeMark, pasteRule } from 'tiptap-commands'
import { getMarkAttrs, getMarkRange } from 'tiptap-utils'

export default class Link extends Mark {

    get name() {
        return 'link'
    }

    get schema() {
        return {
            attrs: {
                href: { default: null },
                rel: { default: null },
                target: { default: null },
                title: { default: null },
            },
            inclusive: false,
            parseDOM: [
                {
                    tag: 'a[href]',
                    getAttrs: dom => ({
                        href: dom.getAttribute('href'),
                        rel: dom.getAttribute('rel'),
                        target: dom.getAttribute('target'),
                        title: dom.getAttribute('title'),
                    }),
                },
            ],
            toDOM: node => ['a', node.attrs, 0]
        }
    }

    commands({ type }) {
        return attrs => {
            if (attrs.href) {
                return updateMark(type, attrs)
            }

            return removeMark(type)
        }
    }

    pasteRules({ type }) {
        return [
            pasteRule(
                /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_+.~#?&//=]*)/g,
                type,
                url => ({ href: url }),
            ),
        ]
    }

    get plugins() {
        const vm = this.options.vm;
        return [
            new Plugin({
                props: {
                    handleClick(view, pos) {
                        const { schema, doc, tr } = view.state
                        const range = getMarkRange(doc.resolve(pos), schema.marks.link)

                        if (range) {
                            const $start = doc.resolve(range.from)
                            const $end = doc.resolve(range.to)
                            const selection = new TextSelection($start, $end)
                            const transaction = tr.setSelection(selection)
                            const attrs = getMarkAttrs(view.state, schema.marks.link)

                            view.dispatch(transaction)
                            vm.$emit('link-selected', attrs)
                        } else {
                            vm.$emit('link-deselected')
                        }
                    },
                },
            }),
        ]
    }
}
