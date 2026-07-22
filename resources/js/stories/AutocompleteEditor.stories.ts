import type { Meta, StoryObj } from '@storybook/vue3';
import { expect, fn, userEvent, waitFor, within } from 'storybook/test';
import { AutocompleteEditor } from '@ui';
import { ref } from 'vue';

const meta = {
    title: 'Components/AutocompleteEditor',
    component: AutocompleteEditor,
    argTypes: {
        'update:modelValue': {
            description: 'Event handler called when the editor content changes.',
            table: {
                category: 'events',
                type: { summary: '(value: object[]) => void' },
            },
        },
    },
} satisfies Meta<typeof AutocompleteEditor>;

export default meta;
type Story = StoryObj<typeof meta>;

const sampleOptions = [
    { value: 'jack', label: 'Jack McDade' },
    { value: 'jason', label: 'Jason Varga' },
    { value: 'jesse', label: 'Jesse Leite' },
    { value: 'duncan', label: 'Duncan McClean' },
];

const defaultCode = `
<AutocompleteEditor
    v-model="value"
    :options="options"
/>
`;

export const Default: Story = {
    render: () => ({
        components: { AutocompleteEditor },
        setup() {
            const value = ref([]);
            const options = sampleOptions;
            return { value, options };
        },
        template: defaultCode,
    }),
};

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode },
        },
    },
    render: () => ({
        components: { AutocompleteEditor },
        setup() {
            const value = ref([]);
            const options = sampleOptions;
            return { value, options };
        },
        template: defaultCode,
    }),
};

const inlineCode = `
<AutocompleteEditor
    v-model="value"
    inline
    :options="options"
    placeholder="Mention someone with @..."
/>
`;

export const _Inline: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: inlineCode },
            description: {
                story: 'Set `inline` to drop the toolbar and block-level nodes (headings, lists) for a single-line editing experience.',
            },
        },
    },
    render: () => ({
        components: { AutocompleteEditor },
        setup() {
            const value = ref([]);
            const options = sampleOptions;
            return { value, options };
        },
        template: inlineCode,
    }),
};

const inlineLineBreaksCode = `
<AutocompleteEditor
    v-model="value"
    inline
    enable-line-breaks
    :options="options"
    placeholder="Mention someone with @..."
/>
`;

export const _InlineWithLineBreaks: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: inlineLineBreaksCode },
            description: {
                story: 'Combine `inline` with `enableLineBreaks` to allow soft line breaks (Enter) without introducing paragraphs or other block nodes.',
            },
        },
    },
    render: () => ({
        components: { AutocompleteEditor },
        setup() {
            const value = ref([]);
            const options = sampleOptions;
            return { value, options };
        },
        template: inlineLineBreaksCode,
    }),
};

const readOnlyValue = [
    {
        type: 'paragraph',
        content: [
            { type: 'text', text: 'Assigned to ' },
            { type: 'mention', attrs: { value: 'jesse', label: 'Jesse Leite' } },
            { type: 'text', text: '. This field is read only.' },
        ],
    },
];

const readOnlyCode = `
<AutocompleteEditor
    v-model="value"
    read-only
    :options="options"
/>
`;

export const _ReadOnly: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: readOnlyCode },
        },
    },
    render: () => ({
        components: { AutocompleteEditor },
        setup() {
            const value = ref(readOnlyValue);
            const options = sampleOptions;
            return { value, options };
        },
        template: readOnlyCode,
    }),
};

const autocompleteValue = [
    {
        type: 'paragraph',
        content: [
            { type: 'text', text: 'Reach out to ' },
            { type: 'mention', attrs: { value: 'jason', label: 'Jason Varga' } },
            { type: 'text', text: ' for details. Type @ to mention someone else.' },
        ],
    },
];

const autocompleteCode = `
<AutocompleteEditor
    v-model="value"
    :options="options"
/>
`;

export const _Autocomplete: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: autocompleteCode },
            description: {
                story: 'Type `@` to open the autocomplete list and insert a mention chip. This example starts with a pre-populated mention already in the content.',
            },
        },
    },
    render: () => ({
        components: { AutocompleteEditor },
        setup() {
            const value = ref(autocompleteValue);
            const options = sampleOptions;
            return { value, options };
        },
        template: autocompleteCode,
    }),
};

export const TestAutocompleteSelectViaEnterInlineNoBreaks: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { AutocompleteEditor },
        setup() {
            const value = ref([]);
            const options = sampleOptions;
            return { value, options, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `
            <AutocompleteEditor
                v-model="value"
                inline
                :options="options"
                @update:modelValue="onUpdate"
            />
        `,
    }),
    play: async ({ canvasElement, args }) => {
        const editable = canvasElement.querySelector('.ProseMirror') as HTMLElement;
        await expect(editable).toBeTruthy();

        await userEvent.click(editable);
        await userEvent.keyboard('@jas');

        await waitFor(() => {
            expect(within(document.body).getByText('Jason Varga')).toBeTruthy();
        });

        await userEvent.keyboard('{Enter}');

        await waitFor(() => {
            expect(args['onUpdate:modelValue']).toHaveBeenCalled();
        });

        const lastCall = args['onUpdate:modelValue'].mock.calls.at(-1);
        expect(JSON.stringify(lastCall[0])).toContain('"type":"mention"');
        expect(editable.textContent).toContain('Jason Varga');
    },
};
