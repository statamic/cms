import { Mark, mergeAttributes } from '@tiptap/core';

export const Small = Mark.create({
    name: 'small',

    addOptions() {
        return {
            HTMLAttributes: {},
        };
    },

    parseHTML() {
        return [
            {
                tag: 'small',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['small', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
    },

    addCommands() {
        return {
            toggleSmall:
                () =>
                ({ commands }) => {
                    return commands.toggleMark(this.name);
                },
        };
    },
});
