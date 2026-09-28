import type {Meta, StoryObj} from '@storybook/vue3';
import {Textarea} from '@statamic/cms/ui';

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

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Textarea },
        template: `
            <Textarea label="Message" />
        `,
    }),
};

export const _Disabled: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Textarea },
        template: `
            <Textarea disabled model-value="Can't touch this." label="Lyrics" />
        `,
    }),
};

export const _Elastic: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Textarea },
        template: `
            <Textarea elastic rows="2" model-value="If you catch a chinchilla in Chile, and cut off its beard willy-nilly, you can honestly say, you made on that day, a Chilean chinchilla's chin chilly." />
        `,
    }),
};

export const _FixedHeight: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Textarea },
        template: `
            <Textarea rows="2" label="Description" />
        `,
    }),
};

export const _Placeholder: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Textarea },
        template: `
            <Textarea placeholder="Dear diary..." label="Message" />
        `,
    }),
};

export const _ResizeControls: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Textarea },
        template: `
            <Textarea resize="horizontal" rows="1" placeholder="Resize horizontal"/>
            <Textarea resize="vertical" rows="1" placeholder="Resize vertical"/>
            <Textarea resize="both" rows="1" placeholder="Resize both"/>
            <Textarea resize="none" rows="1" placeholder="Resize none"/>
        `,
    }),
};

export const _Copyable: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Textarea },
        template: `
            <Textarea copyable read-only model-value="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum." />
        `,
    }),
};
