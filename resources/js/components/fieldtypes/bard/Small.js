import { Mark } from 'tiptap'
import { toggleMark } from 'tiptap-commands'

export default class Small extends Mark {

    get name() {
        return 'small'
    }

    get schema() {
        return {
            parseDOM: [
                {
                    tag: 'small',
                },
            ],
            toDOM: () => ['small', 0],
        }
    }

    commands({ type }) {
        // console.log(type);
        return () => toggleMark(type)
    }

}
