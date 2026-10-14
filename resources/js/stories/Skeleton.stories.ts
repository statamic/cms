import type {Meta, StoryObj} from '@storybook/vue3';
import {Card, Skeleton} from '@statamic/cms/ui';

const meta = {
    title: 'Components/Skeleton',
    component: Skeleton,
} satisfies Meta<typeof Skeleton>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Skeleton },
        template: `
            <div class="space-y-4">
                <Skeleton class="h-12 w-full" />
                <Skeleton class="h-12 w-full" />
                <Skeleton class="h-12 w-3/4" />
            </div>
        `,
    }),
};

export const Default: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Skeleton },
        template: `<Skeleton class="h-12 w-64" />`,
    }),
};

export const Sizes: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Skeleton },
        template: `
            <div class="space-y-4">
                <Skeleton class="h-8 w-64" />
                <Skeleton class="h-12 w-64" />
                <Skeleton class="h-16 w-64" />
            </div>
        `,
    }),
};

export const CardExample: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Skeleton, Card },
        template: `
            <Card>
                <div class="flex items-center gap-4">
                    <Skeleton class="size-12 rounded-full" />
                    <div class="flex-1 space-y-2">
                        <Skeleton class="h-4 w-1/2" />
                        <Skeleton class="h-4 w-1/3" />
                    </div>
                </div>
            </Card>
        `,
    }),
};
