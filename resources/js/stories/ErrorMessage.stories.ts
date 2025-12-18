import type {Meta, StoryObj} from '@storybook/vue3';
import {ErrorMessage} from '@ui';

const meta = {
    title: 'Components/ErrorMessage',
    component: ErrorMessage,
    argTypes: {},
} satisfies Meta<typeof ErrorMessage>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<ErrorMessage text="This field is required." />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { ErrorMessage },
        template: defaultCode,
    }),
};

const withSlotCode = `
<ErrorMessage>
    The file size exceeds the maximum allowed. <a href="#">Learn more</a>
</ErrorMessage>
`;

export const _WithSlot: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withSlotCode }
        }
    },
    render: () => ({
        components: { ErrorMessage },
        template: withSlotCode,
    }),
};