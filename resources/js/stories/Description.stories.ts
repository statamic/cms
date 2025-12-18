import type {Meta, StoryObj} from '@storybook/vue3';
import {Description} from '@ui';

const meta = {
    title: 'Components/Description',
    component: Description,
    argTypes: {},
} satisfies Meta<typeof Description>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Description text="Enter your full name as it appears on your ID." />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Description },
        template: defaultCode,
    }),
};

const withSlotCode = `
<Description>
    This will be visible to all users. <a href="#">Learn more about privacy</a>
</Description>
`;

export const _WithSlot: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withSlotCode }
        }
    },
    render: () => ({
        components: { Description },
        template: withSlotCode,
    }),
};
