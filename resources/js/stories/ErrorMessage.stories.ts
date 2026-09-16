import type {Meta, StoryObj} from '@storybook/vue3';
import {ErrorMessage} from '@ui';

/**
 * @import import { ErrorMessage } from '@statamic/cms/ui';
 */
const meta = {
    title: 'Forms/ErrorMessage',
    component: ErrorMessage,
    argTypes: {},
} satisfies Meta<typeof ErrorMessage>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { ErrorMessage },
        template: `
            <ErrorMessage text="This field is required." />
        `,
    }),
};

export const _WithSlot: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { ErrorMessage },
        template: `
            <ErrorMessage>
                The file size exceeds the maximum allowed. <a href="#">Learn more</a>
            </ErrorMessage>
        `,
    }),
};