import type {Meta, StoryObj} from '@storybook/vue3';
import {Text} from '@statamic/cms/ui';

const meta = {
    title: 'Components/Text',
    component: Text,
    argTypes: {
        size: {
            control: 'select',
            options: ['xs', 'sm', 'base', 'lg'],
        },
        variant: {
            control: 'select',
            options: ['default', 'strong', 'subtle', 'code', 'danger', 'success', 'warning'],
        },
        as: {
            control: 'select',
            options: ['span', 'p', 'div'],
        },
    },
} satisfies Meta<typeof Text>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        text: 'The quick brown fox jumps over the lazy dog.',
    },
};

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Text },
        template: `
            <div class="flex flex-wrap gap-3 items-center">
                <Text text="Default" />
                <Text variant="strong" text="Strong" />
                <Text variant="subtle" text="Subtle" />
                <Text variant="code" text="code_example" />
            </div>
        `,
    }),
};

export const Variants: Story = {
    argTypes: {
        variant: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Text variant="default" text="Default" />
                    <Text variant="strong" text="Strong" />
                    <Text variant="subtle" text="Subtle" />
                    <Text variant="code" text="code_example" />
                    <Text variant="danger" text="Danger" />
                    <Text variant="success" text="Success" />
                    <Text variant="warning" text="Warning" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Text },
        setup() {
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-3 items-center">
                <Text v-bind="args" variant="default" text="Default" />
                <Text v-bind="args" variant="strong" text="Strong" />
                <Text v-bind="args" variant="subtle" text="Subtle" />
                <Text v-bind="args" variant="code" text="code_example" />
                <Text v-bind="args" variant="danger" text="Danger" />
                <Text v-bind="args" variant="success" text="Success" />
                <Text v-bind="args" variant="warning" text="Warning" />
            </div>
        `,
    }),
};

export const Sizes: Story = {
    argTypes: {
        size: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Text size="lg" text="Large" />
                    <Text size="base" text="Base" />
                    <Text size="sm" text="Small" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Text },
        setup() {
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-3 items-center">
                <Text v-bind="args" size="lg" text="Large" />
                <Text v-bind="args" size="base" text="Base" />
                <Text v-bind="args" size="sm" text="Small" />
            </div>
        `,
    }),
};

export const _InlineDocs: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Text },
        template: `
            <Text>Default with <Text variant="strong">strong</Text> and <Text variant="subtle">subtle</Text> inline</Text>
        `,
    }),
};

export const _AsParagraph: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Text },
        template: `
            <div class="space-y-2">
                <Text as="p">This is a paragraph of default text that could appear inside a widget or table description.</Text>
                <Text as="p" variant="subtle">This is a subtle paragraph, useful for secondary information or metadata.</Text>
            </div>
        `,
    }),
};
