import type { Meta, StoryObj } from '@storybook/vue3';
import { Popover, Button, Heading, Textarea } from '@ui';

const meta = {
    title: 'Components/Popover',
    component: Popover,
    argTypes: {
        side: {
            control: 'select',
            options: ['top', 'bottom', 'left', 'right'],
        },
    },
} satisfies Meta<typeof Popover>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Popover>
    <template #trigger>
        <Button text="Open Popover" />
    </template>
    <div class="flex flex-col gap-2.5">
        <Heading text="Provide Feedback" />
        <Textarea placeholder="How we can make this component better?" elastic />
        <div class="flex flex-col sm:flex-row sm:justify-end">
            <Button variant="primary" size="sm" text="Submit" />
        </div>
    </div>
</Popover>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Popover, Button, Heading, Textarea },
        template: defaultCode,
    }),
};

const widthCode = `
<Popover class="w-[420px]!">
    <template #trigger>
        <Button text="Open Popover" />
    </template>
    <Heading text="I'm 420 pixels wide" />
    <img src="https://images.unsplash.com/photo-1611946258523-9c2bfabb94e3?q=80&w=2571&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="420" class="mt-2">
</Popover>
`;

export const _Width: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: widthCode }
        }
    },
    render: () => ({
        components: { Popover, Button, Heading },
        template: widthCode,
    }),
};

const directionCode = `
<div class="flex space-x-3 justify-center">
    <Popover side="left">
        <template #trigger>
            <Button text="← To the left" />
        </template>
        <p>Popped to the left</p>
    </Popover>

    <Popover side="right">
        <template #trigger>
            <Button text="To the right →" />
        </template>
        <p>Popped to the right</p>
    </Popover>
</div>
`;

export const _Direction: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: directionCode }
        }
    },
    render: () => ({
        components: { Popover, Button },
        template: directionCode,
    }),
};
