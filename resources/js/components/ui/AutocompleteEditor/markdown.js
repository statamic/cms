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

// Mentions are serialized as a sentinel rather than the real `{{ value }}`
// token. Tiptap gives no hook to escape text as it's written, so braces an
// author typed literally can only be told apart from ours once the whole
// document has been serialized. See contentToMarkdown.
export const MENTION_SENTINEL = '\u0000';

const MENTION_TOKEN = /^\{\{\s*([\w.-]+)\s*\}\}/;

// Markdown is how the fieldtype stores its value, not something the editor
// itself does, so this is declared here rather than on the shared extension.
const MarkdownAutocomplete = Autocomplete.extend({
    markdownTokenName: 'mention',

    markdownTokenizer: {
        name: 'mention',
        level: 'inline',
        start: (src) => src.indexOf('{{'),
        tokenize: (src) => {
            const match = MENTION_TOKEN.exec(src);

            if (!match) return;

            return { type: 'mention', raw: match[0], value: match[1] };
        },
    },

    parseMarkdown: (token) => ({ type: 'mention', attrs: { value: token.value } }),

    renderMarkdown: (node) => `${MENTION_SENTINEL}${node.attrs.value}${MENTION_SENTINEL}`,
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
        Paragraph,
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
    const markdown = manager.serialize({ type: 'doc', content });

    // Escape every brace pair first, so anything the author typed is treated as
    // literal text, then turn only our own sentinels into real tokens.
    return markdown
        .replaceAll('{{', '\\{\\{')
        .replace(new RegExp(`${MENTION_SENTINEL}([^${MENTION_SENTINEL}]*)${MENTION_SENTINEL}`, 'g'), '{{ $1 }}');
}

export function markdownToContent(markdown) {
    // The markdown lexer throws on anything that isn't a string, which would
    // take down the whole publish form. Treat unexpected values as empty.
    if (typeof markdown !== 'string') return [];

    return manager.parse(markdown).content;
}
