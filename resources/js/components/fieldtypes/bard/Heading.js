import { Heading as TipTapHeading } from "tiptap-extensions";
import { textblockTypeInputRule } from 'tiptap-commands'

/**
 * Matches following attributes: [id]
 *
 * Example:
 * ##{#my-heading} -> <h2 id="my-heading"></h2>
 */

const HEADING_INPUT_REGEX = /^(#{1,6})(?:\{#(.+)\})?\s/

export default class Heading extends TipTapHeading {
    get schema() {
        return {
            ...super.schema,
            attrs: {
                level: { default: 1 },
                id: { default: null },
            },
            parseDOM: this.options.levels
                .map(level => ({
                    tag: `h${level}`,
                    getAttrs: dom => ({
                        level: level,
                        id: dom.getAttribute('id'),
                    }),
                })),
            toDOM: node => {
                node.attrs.id = node.attrs.id;
                return [`h${node.attrs.level}`, {
                    id: node.attrs.id
                }, 0];
            }
        };
    }

  inputRules({ type }) {
    return [
      textblockTypeInputRule(HEADING_INPUT_REGEX, type, match => {
        let [, level, id] = match
        level = level.length

        return {
          level,
          id,
        }
      }),
    ]
  }
}
