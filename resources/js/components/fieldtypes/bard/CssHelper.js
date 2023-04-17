import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from "@tiptap/pm/view";

export const CssHelper = Extension.create({

    name: 'cssHelper',

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('cssHelper'),
                props: {
                    decorations(state) {
                        const decorations = [];
                        state.doc.descendants((node, pos) => {
                            if (node.type.name !== 'text') {
                                decorations.push(Decoration.node(pos, pos + node.nodeSize, { class: 'bard-node' }));
                            } else if (node.marks.length) {
                                decorations.push(Decoration.inline(pos, pos + node.nodeSize, { class: 'bard-mark' }));
                            }
                        });
                        return DecorationSet.create(state.doc, decorations);
                    }
                },
            }),
        ]
    },

})
