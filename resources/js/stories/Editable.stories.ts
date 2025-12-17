import type {Meta, StoryObj} from '@storybook/vue3';
import {Editable} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Components/Editable',
    component: Editable,
    argTypes: {
        modelValue: {
            control: 'text',
            description: 'The controlled value of the editable text.',
        },
        startWithEditMode: {
            control: 'boolean',
            description: 'When `true`, the input will be automatically focused when the component mounts.',
        },
        submitMode: {
            control: 'select',
            description: 'Controls when the edit is submitted. Options: `blur`, `none` `enter`, `both`',
            options: ['blur', 'none', 'enter', 'both'],
        },
        placeholder: { control: 'text' },
        'update:modelValue': {
            description: 'Event handler called when the text is updated.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        },
        'cancel': {
            description: 'Event handler called when the edit is cancelled.',
            table: {
                category: 'events',
                type: { summary: '() => void' }
            }
        },
        'submit': {
            description: 'Event handler called when the edit is submitted.',
            table: {
                category: 'events',
                type: { summary: '() => void' }
            }
        },
        'edit': {
            description: 'Event handler called when the user starts editing the text.',
            table: {
                category: 'events',
                type: { summary: '() => void' }
            }
        },
    },
} satisfies Meta<typeof Editable>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Editable v-model="text" placeholder="Click to edit..." />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Editable },
        setup() {
            const text = ref('Click me to edit');
            return { text };
        },
        template: `<Editable v-model="text" placeholder="Click to edit..." />`,
    }),
};

const startWithEditModeCode = `
<Editable
    v-model="text"
    start-with-edit-mode
    placeholder="Start typing..."
/>
`;

export const _StartWithEditMode: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: startWithEditModeCode }
        }
    },
    render: () => ({
        components: { Editable },
        setup() {
            const text = ref('');
            return { text };
        },
        template: `<Editable v-model="text" start-with-edit-mode placeholder="Start typing..." />`,
    }),
};

const submitModesCode = `
<div class="space-y-4">
    <div>
        <div class="text-sm text-gray-600 mb-1">Submit on blur:</div>
        <Editable v-model="text1" submit-mode="blur" />
    </div>
    <div>
        <div class="text-sm text-gray-600 mb-1">Submit on enter:</div>
        <Editable v-model="text2" submit-mode="enter" />
    </div>
    <div>
        <div class="text-sm text-gray-600 mb-1">Submit on both:</div>
        <Editable v-model="text3" submit-mode="both" />
    </div>
</div>
`;

export const _SubmitModes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: submitModesCode }
        }
    },
    render: () => ({
        components: { Editable },
        setup() {
            const text1 = ref('Click away to save');
            const text2 = ref('Press Enter to save');
            const text3 = ref('Click away or press Enter');
            return { text1, text2, text3 };
        },
        template: `
            <div class="space-y-4">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Submit on blur:</div>
                    <Editable v-model="text1" submit-mode="blur" />
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Submit on enter:</div>
                    <Editable v-model="text2" submit-mode="enter" />
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Submit on both:</div>
                    <Editable v-model="text3" submit-mode="both" />
                </div>
            </div>
        `,
    }),
};

const eventsCode = `
<Editable
    v-model="text"
    @submit="onSubmit"
    @cancel="onCancel"
    @edit="onEdit"
/>
`;

export const _Events: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: eventsCode }
        }
    },
    render: () => ({
        components: { Editable },
        setup() {
            const text = ref('Edit me and watch the console');
            const onSubmit = (value) => console.log('Submitted:', value);
            const onCancel = () => console.log('Cancelled');
            const onEdit = (value) => console.log('Edit started:', value);
            return { text, onSubmit, onCancel, onEdit };
        },
        template: `<Editable v-model="text" @submit="onSubmit" @cancel="onCancel" @edit="onEdit" />`,
    }),
};

const customPlaceholderCode = `
<Editable
    v-model="text"
    placeholder="Enter your name..."
/>
`;

export const _CustomPlaceholder: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customPlaceholderCode }
        }
    },
    render: () => ({
        components: { Editable },
        setup() {
            const text = ref('');
            return { text };
        },
        template: `<Editable v-model="text" placeholder="Enter your name..." />`,
    }),
};
