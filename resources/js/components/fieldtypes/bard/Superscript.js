import { Mark } from 'tiptap'
import { toggleMark } from 'tiptap-commands'

export default class Superscript extends Mark {

    get name() {
        return 'superscript'
    }

    get schema() {
        return {
            parseDOM: [
                {
                    tag: 'sup',
                },
            ],
            toDOM: () => ['sup', 0],
        }
    }

    commands({ type }) {
        return () => toggleMark(type)
    }

}
