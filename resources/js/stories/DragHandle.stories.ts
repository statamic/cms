import type { Meta, StoryObj } from '@storybook/vue3';
import { DragHandle } from '@ui';

/**
 * @import import { DragHandle } from '@statamic/cms/ui';
 */
const meta = {
    title: 'Components/DragHandle',
    component: DragHandle,
    argTypes: {},
} satisfies Meta<typeof DragHandle>;

export default meta;
type Story = StoryObj<typeof meta>;

const basicCode = `<DragHandle />`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: basicCode }
        }
    },
    render: () => ({
        components: { DragHandle },
        template: basicCode,
    }),
};
