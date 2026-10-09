import type { Meta, StoryObj } from '@storybook/vue3';
import { DragHandle } from '@statamic/cms/ui';

const meta = {
    title: 'Components/DragHandle',
    component: DragHandle,
    argTypes: {
        keyboardReorder: {
            control: 'boolean',
            description:
                'When enabled, click / Space / Enter emits `keyboard-reorder` so the parent can start arrow-key reordering.',
        },
    },
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

export const KeyboardReorder: Story = {
    args: {
        keyboardReorder: true,
    },
    render: (args) => ({
        components: { DragHandle },
        setup() {
            return { args };
        },
        template: `
            <DragHandle v-bind="args" @keyboard-reorder="() => {}" />
        `,
    }),
};
