import { MarkdownManager } from '@tiptap/markdown';
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Heading from '@tiptap/extension-heading';
import HardBreak from '@tiptap/extension-hard-break';
import { BulletList, OrderedList, ListItem } from '@tiptap/extension-list';
import { Autocomplete } from './extensions/Autocomplete';

const MENTION_TOKEN = /^\[\[\s*([\w.-]+)\s*\]\]/;

// The serializer escapes inline syntax but not what only has meaning at the
// start of a line, so plain text like "- x" or "# x" would reload as a list or
// heading. `*` and `_` are already escaped by the serializer.
function escapeBlockMarkers(line, isFirstLine) {
    const indent = line.match(/^[ \t]*/)[0];

    // Four columns of indentation is a code block, which the schema drops.
    // Leading spaces are otherwise kept by the parser, so only this needs
    // an entity, which paragraph parsing turns back into the whitespace.
    if (indent.replace(/\t/g, '    ').length >= 4) {
        return (indent[0] === '\t' ? '&#9;' : '&#32;') + line.slice(1);
    }

    const rest = line.slice(indent.length);
    const orderedList = isFirstLine ? /^\d{1,9}[.)](?=[ \t]|$)/ : /^1[.)](?=[ \t]|$)/;

    const needsEscape =
        /^#{1,6}(?=[ \t]|$)/.test(rest) ||
        /^[-+](?=[ \t]|$)/.test(rest) ||
        /^-[-\t ]*$/.test(rest) ||
        /^=+[ \t]*$/.test(rest) ||
        /^(?=[-:| \t]*\|)[-:| \t]*-[-:| \t]*$/.test(rest) ||
        orderedList.test(rest);

    if (!needsEscape) return line;

    // Escaping the first character of a numbered marker would leave the
    // digits as plain text, so the delimiter is the part that gets escaped.
    return indent + rest.replace(/^(\d{1,9})?(.)/, (_, digits = '', char) => `${digits}\\${char}`);
}

const BlockSafeParagraph = Paragraph.extend({
    parseMarkdown(token, helpers) {
        const tokens = (token.tokens || []).map((inline, i, all) => {
            const startsLine = i === 0 || all[i - 1].type === 'br';

            if (!startsLine || inline.type !== 'text') return inline;

            return { ...inline, text: inline.text.replace(/^&#(32|9);/, (_, code) => (code === '9' ? '\t' : ' ')) };
        });

        return Paragraph.config.parseMarkdown({ ...token, tokens }, helpers);
    },

    renderMarkdown(node, helpers, context) {
        const markdown = Paragraph.config.renderMarkdown(node, helpers, context);

        return markdown
            .split('\n')
            .map((line, i) => escapeBlockMarkers(line, i === 0))
            .join('\n');
    },
});

// Markdown is how the fieldtype stores its value, not something the editor
// itself does, so this is declared here rather than on the shared extension.
const MarkdownAutocomplete = Autocomplete.extend({
    markdownTokenName: 'mention',

    markdownTokenizer: {
        name: 'mention',
        level: 'inline',
        start: (src) => src.indexOf('[['),
        tokenize: (src) => {
            const match = MENTION_TOKEN.exec(src);

            if (!match) return;

            return { type: 'mention', raw: match[0], value: match[1] };
        },
    },

    parseMarkdown: (token) => ({ type: 'mention', attrs: { value: token.value } }),

    // Brackets an author typed literally are escaped by the text serializer,
    // so a mention rendering them raw is unambiguously ours.
    renderMarkdown: (node) => `[[ ${node.attrs.value} ]]`,
});

// The default two-trailing-space hard break doesn't survive whitespace
// stripping (editors, linters, pre-commit hooks). A trailing backslash does.
const BackslashHardBreak = HardBreak.extend({
    renderMarkdown: () => '\\\n',
});

// Uses the full extension set (all heading levels, both list types) rather
// than the per-field configured subset, so a document containing content
// from a since-disabled button still round-trips.
const manager = new MarkdownManager({
    extensions: [
        Document,
        BlockSafeParagraph,
        Text,
        Bold,
        Italic,
        Heading,
        BackslashHardBreak,
        BulletList,
        OrderedList,
        ListItem,
        MarkdownAutocomplete,
    ],
});

export function contentToMarkdown(content) {
    return manager.serialize({ type: 'doc', content });
}

export function markdownToContent(markdown) {
    // The markdown lexer throws on anything that isn't a string, which would
    // take down the whole publish form. Treat unexpected values as empty.
    if (typeof markdown !== 'string') return [];

    return manager.parse(markdown).content;
}
