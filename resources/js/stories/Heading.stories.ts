import type { Meta, StoryObj } from '@storybook/vue3';
import { Heading, Subheading } from '@ui';

const meta = {
    title: 'Components/Heading',
    component: Heading,
    argTypes: {
        text: { control: 'text' },
        size: {
            control: 'select',
            options: ['default', 'lg', 'xl'],
        },
        level: { control: 'text' },
        icon: { control: 'text' },
        href: { control: 'text' },
    },
} satisfies Meta<typeof Heading>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<div class="flex flex-col">
    <Heading size="lg">Create collection</Heading>
    <Subheading>Create a collection to manage a group of entries.</Subheading>
</div>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Heading, Subheading },
        template: defaultCode,
    }),
};

const sizesCode = `
<div class="space-y-2">
    <Heading>Default</Heading>
    <Heading size="lg">Large</Heading>
    <Heading size="xl">Extra Large</Heading>
</div>
`;

export const _Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode }
        }
    },
    render: () => ({
        components: { Heading },
        template: sizesCode,
    }),
};

const levelCode = `
<Heading level="3" size="xl">Create collection</Heading>
`;

export const _HeadingLevel: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: levelCode }
        }
    },
    render: () => ({
        components: { Heading },
        template: levelCode,
    }),
};

const iconCode = `
<Heading
    size="lg"
    icon="setting-slider-vertical"
    text="Manage Settings"
/>
`;

export const _Icon: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconCode }
        }
    },
    render: () => ({
        components: { Heading },
        template: iconCode,
    }),
};

const linkCode = `
<Heading size="lg" href="https://statamic.com">
    Visit statamic.com
</Heading>
`;

export const _AsLink: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: linkCode }
        }
    },
    render: () => ({
        components: { Heading },
        template: linkCode,
    }),
};
