import type {Meta, StoryObj} from '@storybook/vue3';
import {Badge, Button, Header} from '@statamic/cms/ui';
import {icons} from "@/stories/icons";

const meta = {
    title: 'Layout/Header',
    component: Header,
    argTypes: {
        icon: {
            control: 'select',
            options: icons,
        },
    },
} satisfies Meta<typeof Header>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Header },
        template: `
            <Header icon="collections" title="Collections" />
        `,
    }),
};

export const _WithActions: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Header, Button },
        template: `
            <Header icon="collections" title="Collections">
                <Button icon="dots" variant="ghost" />
                <Button text="Create Collection" variant="primary" />
            </Header>
        `,
    }),
};

export const _WithTitleSlot: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Header, Button, Badge },
        template: `
            <Header icon="collections">
                <template #title>
                    Collections <Badge text="12" color="blue" />
                </template>
                <Button text="Create Collection" variant="primary" />
            </Header>
        `,
    }),
};
