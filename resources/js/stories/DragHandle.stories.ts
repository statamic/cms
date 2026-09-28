import type { Meta, StoryObj } from '@storybook/vue3';
import { DragHandle } from '@statamic/cms/ui';

const meta = {
    title: 'Components/DragHandle',
    component: DragHandle,
    argTypes: {},
} satisfies Meta<typeof DragHandle>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { DragHandle },
        template: `
            <DragHandle />
        `,
    }),
};
