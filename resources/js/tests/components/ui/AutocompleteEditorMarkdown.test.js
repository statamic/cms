import { expect, test } from 'vitest';
import { contentToMarkdown, markdownToContent } from '@/components/ui/AutocompleteEditor/markdown';

test('a value that is not a string is treated as empty', () => {
    const proseMirror = [{ type: 'paragraph', content: [{ type: 'text', text: 'legacy' }] }];

    expect(markdownToContent(proseMirror)).toEqual([]);
    expect(markdownToContent(null)).toEqual([]);
    expect(markdownToContent(undefined)).toEqual([]);
});

test('a mention on its own round trips', () => {
    const content = [{ type: 'paragraph', content: [{ type: 'mention', attrs: { value: 'first_name' } }] }];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe('[[ first_name ]]');
    expect(markdownToContent(markdown)).toEqual(content);
});

test('inline content round trips to a plain markdown scalar', () => {
    const content = [
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'Hi ' },
                { type: 'mention', attrs: { value: 'first_name' } },
                { type: 'text', text: ', thanks!' },
            ],
        },
    ];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe('Hi [[ first_name ]], thanks!');
    expect(markdownToContent(markdown)).toEqual(content);
});

test('block content with headings, marks, lists, and mentions round trips', () => {
    const content = [
        { type: 'heading', attrs: { level: 2 }, content: [{ type: 'text', text: 'Thanks for getting in touch' }] },
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'Hi ' },
                { type: 'mention', attrs: { value: 'first_name' } },
                { type: 'text', text: ',' },
            ],
        },
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'We received your message and will reply to ' },
                { type: 'mention', attrs: { value: 'email' } },
                { type: 'text', text: ' within ' },
                { type: 'text', marks: [{ type: 'bold' }], text: 'two business days' },
                { type: 'text', text: '.' },
            ],
        },
        { type: 'paragraph', content: [{ type: 'text', text: 'Here is what you sent us:' }] },
        {
            type: 'bulletList',
            content: [
                {
                    type: 'listItem',
                    content: [
                        {
                            type: 'paragraph',
                            content: [
                                { type: 'text', text: 'Subject: ' },
                                { type: 'mention', attrs: { value: 'subject' } },
                            ],
                        },
                    ],
                },
                {
                    type: 'listItem',
                    content: [
                        {
                            type: 'paragraph',
                            content: [
                                { type: 'text', text: 'Message: ' },
                                { type: 'mention', attrs: { value: 'message' } },
                            ],
                        },
                    ],
                },
            ],
        },
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'Thanks,' },
                { type: 'hardBreak' },
                { type: 'text', text: 'The Team' },
            ],
        },
    ];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe(
        [
            '## Thanks for getting in touch',
            '',
            'Hi [[ first_name ]],',
            '',
            'We received your message and will reply to [[ email ]] within **two business days**.',
            '',
            'Here is what you sent us:',
            '',
            '- Subject: [[ subject ]]',
            '- Message: [[ message ]]',
            '',
            'Thanks,\\',
            'The Team',
        ].join('\n'),
    );
    expect(markdownToContent(markdown)).toEqual(content);
});

test('a literal [[ ]] typed by an author round trips as text, not a mention', () => {
    const content = [{ type: 'paragraph', content: [{ type: 'text', text: 'Type [[ first_name ]] literally' }] }];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe('Type \\[\\[ first\\_name \\]\\] literally');
    expect(markdownToContent(markdown)).toEqual(content);
});

test('a real mention and a literal in the same line both round trip correctly', () => {
    const content = [
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'Hi ' },
                { type: 'mention', attrs: { value: 'handle' } },
                { type: 'text', text: ', see [[ first_name ]] literally' },
            ],
        },
    ];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe('Hi [[ handle ]], see \\[\\[ first\\_name \\]\\] literally');
    expect(markdownToContent(markdown)).toEqual(content);
});

test('a literal before a real mention round trips correctly', () => {
    const content = [
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'See [[ first_name ]] then ' },
                { type: 'mention', attrs: { value: 'handle' } },
            ],
        },
    ];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe('See \\[\\[ first\\_name \\]\\] then [[ handle ]]');
    expect(markdownToContent(markdown)).toEqual(content);
});

test('escaped brackets parse to text rather than a mention', () => {
    expect(markdownToContent('\\[\\[ first\\_name \\]\\]')).toEqual([
        { type: 'paragraph', content: [{ type: 'text', text: '[[ first_name ]]' }] },
    ]);

    // Underscores don't need escaping to be part of an escaped literal.
    expect(markdownToContent('\\[\\[ first_name \\]\\]')).toEqual([
        { type: 'paragraph', content: [{ type: 'text', text: '[[ first_name ]]' }] },
    ]);
});

test('an escaped literal alongside a real mention parses in either order', () => {
    expect(markdownToContent('Hi [[ handle ]], see \\[\\[ first\\_name \\]\\] literally')).toEqual([
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'Hi ' },
                { type: 'mention', attrs: { value: 'handle' } },
                { type: 'text', text: ', see [[ first_name ]] literally' },
            ],
        },
    ]);

    expect(markdownToContent('See \\[\\[ first\\_name \\]\\] then [[ handle ]]')).toEqual([
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'See [[ first_name ]] then ' },
                { type: 'mention', attrs: { value: 'handle' } },
            ],
        },
    ]);
});

test('hard breaks survive trailing whitespace stripping', () => {
    const content = [
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'Line one' },
                { type: 'hardBreak' },
                { type: 'text', text: 'Line two' },
            ],
        },
    ];

    const markdown = contentToMarkdown(content);
    const stripped = markdown
        .split('\n')
        .map((line) => line.replace(/\s+$/, ''))
        .join('\n');

    expect(markdown).toBe(stripped);
    expect(markdownToContent(stripped)).toEqual(content);
});
