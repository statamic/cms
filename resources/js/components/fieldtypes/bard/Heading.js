import { Heading as TipTapHeading } from 'tiptap-extensions';

export default class Heading extends TipTapHeading {
    get schema() {
        return {
            ...super.schema,
            toDOM: node => {
                const id = this.options.vm.$slugify(node.textContent);
                return [`h${node.attrs.level}`, { id }, 0];
            },
        }
    }
}
