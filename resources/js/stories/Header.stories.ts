import type {Meta, StoryObj} from '@storybook/vue3';
import {Badge, Button, Header} from '@ui';
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

const defaultCode = `
<Header icon="collections" title="Collections" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Header },
        template: defaultCode,
    }),
};

const withActionsCode = `
<Header icon="collections" title="Collections">
    <Button icon="dots" variant="ghost" />
    <Button text="Create Collection" variant="primary" />
</Header>
`;

export const _WithActions: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withActionsCode }
        }
    },
    render: () => ({
        components: { Header, Button },
        template: withActionsCode,
    }),
};

const withTitleSlotCode = `
<Header icon="collections">
    <template #title>
        Collections <Badge text="12" color="blue" />
    </template>
    <Button text="Create Collection" variant="primary" />
</Header>
`;

export const _WithTitleSlot: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withTitleSlotCode }
        }
    },
    render: () => ({
        components: { Header, Button, Badge },
        template: withTitleSlotCode,
    }),
};
