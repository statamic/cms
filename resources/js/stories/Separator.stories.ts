import type {Meta, StoryObj} from '@storybook/vue3';
import {Separator} from '@statamic/cms/ui';

const meta = {
    title: 'Layout/Separator',
    component: Separator,
    argTypes: {
        variant: {
            control: 'select',
            options: ['line', 'dots'],
        },
    },
} satisfies Meta<typeof Separator>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Separator },
        template: `
            <Separator text="vs" />
        `,
    }),
};

export const _Variants: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Separator },
        template: `
            <div class="flex flex-col w-full">
                <Separator text="Line Separator (Default)" />
                <Separator variant="dots" text="Dotted Separator" />
            </div>
        `,
    }),
};

export const _Text: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Separator },
        template: `
            <Separator text="Breaker High" />
        `,
    }),
};

export const _Vertical: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Separator },
        template: `
            <div class="flex items-center h-6 space-x-4">
                <div>Left Content</div>
                <Separator vertical />
                <div>Right Content</div>
            </div>
        `,
    }),
};
