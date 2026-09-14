import type {Meta, StoryObj} from '@storybook/vue3';
import {Textarea} from '@ui';

const meta = {
    title: 'Forms/Textarea',
    component: Textarea,
    argTypes: {
        resize: {
            control: 'select',
            options: ['both', 'horizontal', 'vertical', 'none'],
        },
        'update:modelValue': {
            description: 'Event handler called when the textarea is updated.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        },
    },
} satisfies Meta<typeof Textarea>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Textarea label="Message" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Textarea },
        template: defaultCode,
    }),
};

const disabledCode = `
<Textarea disabled model-value="Can't touch this." label="Lyrics" />
`;

export const _Disabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: disabledCode }
        }
    },
    render: () => ({
        components: { Textarea },
        template: disabledCode,
    }),
};

const elasticCode = `
<Textarea elastic rows="2" model-value="If you catch a chinchilla in Chile, and cut off its beard willy-nilly, you can honestly say, you made on that day, a Chilean chinchilla's chin chilly." />
`;

export const _Elastic: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: elasticCode }
        }
    },
    render: () => ({
        components: { Textarea },
        template: elasticCode,
    }),
};

const fixedHeightCode = `
<Textarea rows="2" label="Description" />
`;

export const _FixedHeight: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: fixedHeightCode }
        }
    },
    render: () => ({
        components: { Textarea },
        template: fixedHeightCode,
    }),
};

const placeholderCode = `
<Textarea placeholder="Dear diary..." label="Message" />
`;

export const _Placeholder: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: placeholderCode }
        }
    },
    render: () => ({
        components: { Textarea },
        template: placeholderCode,
    }),
};

const resizeCode = `
<Textarea resize="horizontal" rows="1" placeholder="Resize horizontal"/>
<Textarea resize="vertical" rows="1" placeholder="Resize vertical"/>
<Textarea resize="both" rows="1" placeholder="Resize both"/>
<Textarea resize="none" rows="1" placeholder="Resize none"/>
`;

export const _ResizeControls: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: resizeCode }
        }
    },
    render: () => ({
        components: { Textarea },
        template: resizeCode,
    }),
};

const copyableCode = `
<Textarea copyable read-only model-value="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum." />
`;

export const _Copyable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: copyableCode }
        }
    },
    render: () => ({
        components: { Textarea },
        template: copyableCode,
    }),
};
