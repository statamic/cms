import { expect, test } from 'vitest';
import { contentToText, textToContent } from '@/components/ui/AutocompleteEditor/text';

const text = (text) => ({ type: 'text', text });
const mention = (value) => ({ type: 'mention', attrs: { value } });
const hardBreak = { type: 'hardBreak' };
const paragraph = (...content) => [{ type: 'paragraph', content }];

function expectRoundTrip(content, stored) {
    expect(contentToText(content)).toBe(stored);
    expect(textToContent(stored)).toEqual(content);
}

test('the empty value round trips', () => {
    expect(contentToText([])).toBe('');
    expect(contentToText([{ type: 'paragraph' }])).toBe('');
    expect(textToContent('')).toEqual([]);
});

test('a value that is not a string is treated as empty', () => {
    expect(textToContent(null)).toEqual([]);
    expect(textToContent(undefined)).toEqual([]);
    expect(textToContent(paragraph(text('legacy')))).toEqual([]);
});

test('markdown special characters are stored verbatim', () => {
    const special = '* _ # \\ ` - 1. > & < [ ] **bold** _em_ # Heading\\';

    expectRoundTrip(paragraph(text(special)), special);
});

test('block markers at the start are stored verbatim', () => {
    expectRoundTrip(paragraph(text('- item')), '- item');
    expectRoundTrip(paragraph(text('1. item')), '1. item');
    expectRoundTrip(paragraph(text('> quote')), '> quote');
    expectRoundTrip(paragraph(text('# heading')), '# heading');
});

test('mentions are stored as tokens', () => {
    expectRoundTrip(paragraph(text('Hi '), mention('first_name'), text(', thanks!')), 'Hi [[ first_name ]], thanks!');
});

test('mentions at the start, the end, and next to each other round trip', () => {
    expectRoundTrip(paragraph(mention('a')), '[[ a ]]');
    expectRoundTrip(paragraph(mention('a'), text(' and '), mention('b')), '[[ a ]] and [[ b ]]');
    expectRoundTrip(paragraph(mention('a'), mention('b'), mention('c.d-e')), '[[ a ]][[ b ]][[ c.d-e ]]');
});

test('a literal [[ is escaped so it does not become a mention', () => {
    expectRoundTrip(paragraph(text('[[ first_name ]]')), '\\[[ first_name ]]');
    expectRoundTrip(paragraph(text('a [[ b')), 'a \\[[ b');
    expectRoundTrip(paragraph(text('[[[[')), '\\[[\\[[');
    expectRoundTrip(paragraph(text('[[[ a ]]')), '\\[[[ a ]]');
});

test('a literal [[ next to a real mention round trips', () => {
    expectRoundTrip(paragraph(text('[['), mention('a')), '\\[[[[ a ]]');
    expectRoundTrip(paragraph(text('[[ x ]] '), mention('a')), '\\[[ x ]] [[ a ]]');
    expectRoundTrip(paragraph(mention('a'), text('[[ x ]]')), '[[ a ]]\\[[ x ]]');
});

test('a single bracket before a mention needs no escape', () => {
    expectRoundTrip(paragraph(text('['), mention('a')), '[[[ a ]]');
    expectRoundTrip(paragraph(text('[[['), mention('a')), '\\[[[[[ a ]]');
    expectRoundTrip(paragraph(mention('a'), text(']')), '[[ a ]]]');
});

test('backslashes are only escaped directly before a [[', () => {
    expectRoundTrip(paragraph(text('C:\\path\\to [x]')), 'C:\\path\\to [x]');
    expectRoundTrip(paragraph(text('\\[ x ]')), '\\[ x ]');
});

test('a backslash before a literal [[ round trips', () => {
    expectRoundTrip(paragraph(text('\\[[ x ]]')), '\\\\\\[[ x ]]');
    expectRoundTrip(paragraph(text('\\\\[[ x ]]')), '\\\\\\\\\\[[ x ]]');
});

test('a backslash before a real mention round trips', () => {
    expectRoundTrip(paragraph(text('\\'), mention('a')), '\\\\[[ a ]]');
    expectRoundTrip(paragraph(text('\\\\'), mention('a')), '\\\\\\\\[[ a ]]');
    expectRoundTrip(paragraph(text('\\['), mention('a')), '\\\\[[[ a ]]');
});

test('hard breaks are stored as newlines', () => {
    expectRoundTrip(paragraph(text('one'), hardBreak, text('two'), hardBreak, mention('a')), 'one\ntwo\n[[ a ]]');
    expectRoundTrip(paragraph(text('a\\'), hardBreak, text('[[')), 'a\\\n\\[[');
});

test('content without hard breaks contains no newlines', () => {
    expect(contentToText(paragraph(text('one two'), mention('a')))).toBe('one two[[ a ]]');
});

test('separate paragraphs are joined with a newline', () => {
    expect(contentToText([...paragraph(text('one')), ...paragraph(mention('a'))])).toBe('one\n[[ a ]]');
});

test('hand written values with malformed tokens are kept as text', () => {
    expect(textToContent('[[ not a token')).toEqual(paragraph(text('[[ not a token')));
    expect(textToContent('[[ a ]')).toEqual(paragraph(text('[[ a ]')));
    expect(textToContent('[[ a b ]]')).toEqual(paragraph(text('[[ a b ]]')));
});

test('every short sequence of brackets, backslashes, mentions and breaks round trips', () => {
    const alphabet = ['[', ']', '\\', ' ', 'x', 'mention', 'break'];

    function toContent(sequence) {
        const content = [];

        sequence.forEach((item) => {
            if (item === 'mention') content.push(mention('x'));
            else if (item === 'break') content.push(hardBreak);
            else if (content.at(-1)?.type === 'text') content.at(-1).text += item;
            else content.push(text(item));
        });

        return paragraph(...content);
    }

    let sequences = [[]];

    for (let length = 1; length <= 5; length++) {
        sequences = sequences.flatMap((sequence) => alphabet.map((item) => [...sequence, item]));

        sequences.forEach((sequence) => {
            const content = toContent(sequence);

            expect(textToContent(contentToText(content))).toEqual(content);
        });
    }
});
