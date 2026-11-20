import type {Meta, StoryObj} from '@storybook/vue3';
import {Description} from '@statamic/cms/ui';

const meta = {
    title: 'Forms/Description',
    component: Description,
    argTypes: {},
} satisfies Meta<typeof Description>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Description },
        template: `
            <Description text="Enter your full name as it appears on your ID." />
        `,
    }),
};

export const _WithSlot: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Description },
        template: `
            <Description>
                This will be visible to all users. <a href="#">Learn more about privacy</a>
            </Description>
        `,
    }),
};
