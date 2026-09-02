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
    return manager.serialize({ type: 'doc', content });
}

export function markdownToContent(markdown) {
    // The markdown lexer throws on anything that isn't a string, which would
    // take down the whole publish form. Treat unexpected values as empty.
    if (typeof markdown !== 'string') return [];

    return manager.parse(markdown).content;
}
