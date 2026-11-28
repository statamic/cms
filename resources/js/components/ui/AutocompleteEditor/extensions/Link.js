import { Mark } from '@tiptap/core';

// Angle brackets let a destination contain spaces and parentheses, which would
// otherwise end it early. Backslashes are doubled since markdown unescapes them.
function destination(href) {
    const escaped = href.replace(/\\/g, '\\\\').replace(/[<>\n]/g, encodeURIComponent);

    return /[\s()]/.test(escaped) ? `<${escaped}>` : escaped;
}

export const Link = Mark.create({
    name: 'link',

    inclusive: false,

    addAttributes() {
        return {
            href: { default: null },
            title: { default: null },
        };
    },

    parseHTML() {
        return [{ tag: 'a[href]' }];
    },

    renderHTML({ HTMLAttributes }) {
        return ['a', HTMLAttributes, 0];
    },

    addCommands() {
        return {
            // Only text is linked. The markdown serializer can't write a link that
            // ends in a mention, so mentions inside the selection are skipped.
            setLink:
                (attributes) =>
                ({ state, tr }) => {
                    const { from, to } = state.selection;
                    const mark = this.type.create(attributes);

                    state.doc.nodesBetween(from, to, (node, pos) => {
                        if (node.isText) tr.addMark(Math.max(pos, from), Math.min(pos + node.nodeSize, to), mark);
                    });

                    return true;
                },
        };
    },

    markdownTokenName: 'link',

    // Server-side markdown doesn't turn bare URLs into links, so neither does the editor.
    parseMarkdown: (token, helpers) => {
        const content = helpers.parseInline(token.tokens || []);

        if (!/^[[<]/.test(token.raw)) return content;

        return helpers.applyMark('link', content, { href: token.href, title: token.title || null });
    },

    renderMarkdown: (node, helpers) => {
        const { href, title } = node.attrs;
        const text = helpers.renderChildren(node);

        return title
            ? `[${text}](${destination(href)} "${title.replace(/[\\"]/g, '\\$&')}")`
            : `[${text}](${destination(href)})`;
    },
});
