import type {Meta, StoryObj} from '@storybook/vue3';
import {Text} from '@ui';
import {computed} from 'vue';

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

const introCode = `
<div class="flex flex-wrap gap-3 items-center">
    <Text text="Default" />
    <Text variant="strong" text="Strong" />
    <Text variant="subtle" text="Subtle" />
    <Text variant="code" text="code_example" />
</div>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: introCode },
        },
    },
    render: () => ({
        components: { Text },
        template: introCode,
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
            const sharedProps = computed(() => {
                const { variant, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-3 items-center">
                <Text variant="default" text="Default" v-bind="sharedProps" />
                <Text variant="strong" text="Strong" v-bind="sharedProps" />
                <Text variant="subtle" text="Subtle" v-bind="sharedProps" />
                <Text variant="code" text="code_example" v-bind="sharedProps" />
                <Text variant="danger" text="Danger" v-bind="sharedProps" />
                <Text variant="success" text="Success" v-bind="sharedProps" />
                <Text variant="warning" text="Warning" v-bind="sharedProps" />
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
            const sharedProps = computed(() => {
                const { size, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-3 items-center">
                <Text size="lg" text="Large" v-bind="sharedProps" />
                <Text size="base" text="Base" v-bind="sharedProps" />
                <Text size="sm" text="Small" v-bind="sharedProps" />
            </div>
        `,
    }),
};

const inlineCode = `
<Text>Default with <Text variant="strong">strong</Text> and <Text variant="subtle">subtle</Text> inline</Text>
`;

export const _InlineDocs: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: inlineCode },
        },
    },
    render: () => ({
        components: { Text },
        template: inlineCode,
    }),
};

const paragraphCode = `
<div class="space-y-2">
    <Text as="p">This is a paragraph of default text that could appear inside a widget or table description.</Text>
    <Text as="p" variant="subtle">This is a subtle paragraph, useful for secondary information or metadata.</Text>
</div>
`;

export const _AsParagraph: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: paragraphCode },
        },
    },
    render: () => ({
        components: { Text },
        template: paragraphCode,
    }),
};
