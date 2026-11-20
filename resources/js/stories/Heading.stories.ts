import type {Meta, StoryObj} from '@storybook/vue3';
import {Heading, Subheading} from '@statamic/cms/ui';
import {icons} from "@/stories/icons";

const meta = {
    title: 'Components/Heading',
    component: Heading,
    subcomponents: {
        Subheading,
    },
    argTypes: {
        icon: {
            control: 'select',
            options: icons,
        },
        size: {
            control: 'select',
            options: ['base', 'lg', 'xl', '2xl'],
        },
    },
} satisfies Meta<typeof Heading>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Heading, Subheading },
        template: `
            <div class="flex flex-col">
                <Heading size="lg">Create collection</Heading>
                <Subheading>Create a collection to manage a group of entries.</Subheading>
            </div>
        `,
    }),
};

export const _Sizes: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Heading },
        template: `
            <div class="space-y-2">
                <Heading>Default</Heading>
                <Heading size="lg">Large</Heading>
                <Heading size="xl">Extra Large</Heading>
            </div>
        `,
    }),
};

export const _HeadingLevel: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Heading },
        template: `
            <Heading :level="3" size="xl">Create collection</Heading>
        `,
    }),
};

export const _Icon: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Heading },
        template: `
            <Heading
                size="lg"
                icon="setting-slider-vertical"
                text="Manage Settings"
            />
        `,
    }),
};

export const _AsLink: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Heading },
        template: `
            <Heading size="lg" href="https://statamic.com">
                Visit statamic.com
            </Heading>
        `,
    }),
};
