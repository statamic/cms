import { MENTION_TOKEN } from './mentionToken';

// Plain text is stored verbatim apart from two escapes, so that it never holds
// a backslash the author didn't type unless it's needed to disambiguate:
//
// - A literal `[[` is stored as `\[[`, so it can't be mistaken for a mention.
// - A run of backslashes directly before a `[[` (literal or mention) is doubled,
//   so its parity says whether the `[[` is escaped.
//
// A literal `[` directly before a mention needs no escape. The decoder only
// treats `[[` as a mention when a token starts there, otherwise it emits a
// single `[` and looks again from the next character.

function flatten(content) {
    const items = [];

    (content ?? []).forEach((block, i) => {
        if (i > 0) items.push('\n');

        (block.content ?? []).forEach((node) => {
            if (node.type === 'text') items.push(...node.text);
            else if (node.type === 'hardBreak') items.push('\n');
            else if (node.type === 'mention') items.push({ value: node.attrs.value });
        });
    });

    return items;
}

const isMention = (item) => typeof item === 'object';

function startsDoubleBracket(items, i) {
    return items[i] === '[' && (items[i + 1] === '[' || isMention(items[i + 1]));
}

export function contentToText(content) {
    const items = flatten(content);
    let text = '';
    let i = 0;

    while (i < items.length) {
        const item = items[i];

        if (isMention(item)) {
            text += `[[ ${item.value} ]]`;
            i++;
        } else if (item === '\\') {
            let run = 0;
            while (items[i + run] === '\\') run++;

            const next = i + run;
            const ambiguous = isMention(items[next]) || startsDoubleBracket(items, next);

            text += '\\'.repeat(ambiguous ? run * 2 : run);
            i = next;
        } else if (item === '[' && items[i + 1] === '[') {
            text += '\\[[';
            i += 2;
        } else {
            text += item;
            i++;
        }
    }

    return text;
}

function decode(text) {
    const items = [];
    let pos = 0;

    const pushText = (string) => items.push(...string);

    while (pos < text.length) {
        const at = text.indexOf('[[', pos);

        if (at === -1) {
            pushText(text.slice(pos));
            break;
        }

        let start = at;
        while (start > pos && text[start - 1] === '\\') start--;
        const run = at - start;

        pushText(text.slice(pos, start));

        if (run % 2 === 1) {
            pushText('\\'.repeat((run - 1) / 2) + '[[');
            pos = at + 2;
            continue;
        }

        pushText('\\'.repeat(run / 2));

        const match = MENTION_TOKEN.exec(text.slice(at));

        if (match) {
            items.push({ value: match[1] });
            pos = at + match[0].length;
        } else {
            items.push('[');
            pos = at + 1;
        }
    }

    return items;
}

export function textToContent(text) {
    if (typeof text !== 'string' || text === '') return [];

    const content = [];
    let buffer = '';

    const flush = () => {
        if (buffer) content.push({ type: 'text', text: buffer });
        buffer = '';
    };

    decode(text).forEach((item) => {
        if (isMention(item)) {
            flush();
            content.push({ type: 'mention', attrs: { value: item.value } });
        } else if (item === '\n') {
            flush();
            content.push({ type: 'hardBreak' });
        } else {
            buffer += item;
        }
    });

    flush();

    return [content.length ? { type: 'paragraph', content } : { type: 'paragraph' }];
}
