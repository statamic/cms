import { MarkdownManager } from '@tiptap/markdown';
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Heading from '@tiptap/extension-heading';
import HardBreak from '@tiptap/extension-hard-break';
import { BulletList, OrderedList, ListItem } from '@tiptap/extension-list';
import { Autocomplete, MENTION_SENTINEL } from './extensions/Autocomplete';

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
        Autocomplete,
    ],
});

export function contentToMarkdown(content) {
    const markdown = manager.serialize({ type: 'doc', content });

    // Mentions are rendered as a sentinel by Autocomplete's renderMarkdown, not
    // the real `{{ value }}` token. Escaping happens after serialize() so any
    // `{{` an author typed as literal text (which the tokenizer can't tell
    // apart from ours) is escaped first, then only our sentinels become tokens.
    return markdown
        .replaceAll('{{', '\\{\\{')
        .replace(new RegExp(`${MENTION_SENTINEL}([\\w.-]+)${MENTION_SENTINEL}`, 'g'), '{{ $1 }}');
}

export function markdownToContent(markdown) {
    return manager.parse(markdown).content;
}
