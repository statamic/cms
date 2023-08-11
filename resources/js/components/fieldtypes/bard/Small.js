import { Mark } from '@tiptap/core';

export const Small = Mark.create({

    name: 'small',

    parseHTML() {
        return [
            {
                tag: 'small',
            }
        ]
    },

    renderHTML() {
        return ['small', 0]
    },

    addCommands() {
        return {
            toggleSmall: () => ({ commands }) => {
                return commands.toggleMark(this.name)
            },
        }
    },

})
