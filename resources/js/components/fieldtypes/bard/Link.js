import { Mark, getAttributes, getMarkRange, markPasteRule } from '@tiptap/core';
import { Plugin, PluginKey, TextSelection } from 'prosemirror-state';

export const Link = Mark.create({

    name: 'link',

    inclusive: false,

    addAttributes() {
        return {
            href: {
                default: null,
                parseHTML: element => element.querySelector('a')?.getAttribute('href'),
            },
            rel: {
                default: null,
                parseHTML: element => element.querySelector('a')?.getAttribute('rel'),
            },
            target: {
                default: null,
                parseHTML: element => element.querySelector('a')?.getAttribute('target'),
            },
            title: {
                default: null,
                parseHTML: element => element.querySelector('a')?.getAttribute('title'),
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'a[href]'
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['a', HTMLAttributes, 0]
    },

    addCommands() {
        return {
            setLink: attributes => ({ chain }) => {
                if (attributes.href) {
                    return chain()
                        .setMark(this.name, attributes)
                        .run()
                }

                return chain()
                    .unsetMark(this.name, { extendEmptyMarkRange: true })
                    .run()
            },
        }
    },

    addPasteRules() {
        return [
            markPasteRule({
                find: /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_+.~#?&//=]*)/g,
                type: this.type,
                getAttributes: url => ({
                    href: url[0]
                }),
            }),
        ]
    },

    addProseMirrorPlugins() {
        const vm = this.options.vm;
        return [
            new Plugin({
                key: new PluginKey('eventHandler'),
                props: {
                    handleClick(view, pos) {
                        const { schema, doc, tr } = view.state;
                        const range = getMarkRange(doc.resolve(pos), schema.marks.link);

                        if (range) {
                            const $start = doc.resolve(range.from);
                            const $end = doc.resolve(range.to);
                            const selection = new TextSelection($start, $end);
                            const transaction = tr.setSelection(selection);
                            const attrs = getAttributes(view.state, schema.marks.link);

                            view.dispatch(transaction);
                            vm.$emit('link-selected', attrs);
                        } else {
                            vm.$emit('link-deselected');
                        }
                    },
                },
            }),
        ]
    },

})
