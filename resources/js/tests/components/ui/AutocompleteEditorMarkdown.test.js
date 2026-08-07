import { expect, test } from 'vitest';
import { contentToMarkdown, markdownToContent, MENTION_SENTINEL } from '@/components/ui/AutocompleteEditor/markdown';

test('a mention value that cannot be tokenized never leaks the sentinel', () => {
    const markdown = contentToMarkdown([
        { type: 'paragraph', content: [{ type: 'mention', attrs: { value: 'not a slug' } }] },
    ]);

    expect(markdown).not.toContain(MENTION_SENTINEL);
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

    expect(markdown).toBe('Hi {{ first_name }}, thanks!');
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
            'Hi {{ first_name }},',
            '',
            'We received your message and will reply to {{ email }} within **two business days**.',
            '',
            'Here is what you sent us:',
            '',
            '- Subject: {{ subject }}',
            '- Message: {{ message }}',
            '',
            'Thanks,\\',
            'The Team',
        ].join('\n'),
    );
    expect(markdownToContent(markdown)).toEqual(content);
});

test('a literal {{ }} typed by an author round trips as text, not a mention', () => {
    const content = [{ type: 'paragraph', content: [{ type: 'text', text: 'Type {{ notamention }} literally' }] }];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe('Type \\{\\{ notamention }} literally');
    expect(markdownToContent(markdown)).toEqual(content);
});

test('a real mention and literal braces in the same line both round trip correctly', () => {
    const content = [
        {
            type: 'paragraph',
            content: [
                { type: 'text', text: 'Real ' },
                { type: 'mention', attrs: { value: 'email' } },
                { type: 'text', text: ' vs literal {{ fake }}' },
            ],
        },
    ];

    const markdown = contentToMarkdown(content);

    expect(markdown).toBe('Real {{ email }} vs literal \\{\\{ fake }}');
    expect(markdownToContent(markdown)).toEqual(content);
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
