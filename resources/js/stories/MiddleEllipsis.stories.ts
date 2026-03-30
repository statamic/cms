import type { Meta, StoryObj } from '@storybook/vue3';
import { MiddleEllipsis } from '@ui';

const meta = {
    title: 'Components/MiddleEllipsis',
    component: MiddleEllipsis,
    argTypes: {
        text: {
            control: 'text',
        },
    },
} satisfies Meta<typeof MiddleEllipsis>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        text: 'uploads/images/2024/vacation-photos/IMG_20240615_143256.jpg',
    },
    decorators: [
        () => ({
            template: '<div style="width: 250px;"><story /></div>',
        }),
    ],
};

const introCode = `
<MiddleEllipsis text="uploads/images/2024/vacation-photos/IMG_20240615_143256.jpg" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: introCode },
        },
    },
    render: () => ({
        components: { MiddleEllipsis },
        template: `
            <div style="width: 250px;">
                <MiddleEllipsis text="uploads/images/2024/vacation-photos/IMG_20240615_143256.jpg" />
            </div>
        `,
    }),
};

const widthsCode = `
<div style="width: 500px;"><MiddleEllipsis :text="text" /></div>
<div style="width: 400px;"><MiddleEllipsis :text="text" /></div>
<div style="width: 300px;"><MiddleEllipsis :text="text" /></div>
<div style="width: 200px;"><MiddleEllipsis :text="text" /></div>
`;

export const _DifferentWidths: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: widthsCode },
        },
    },
    render: () => ({
        components: { MiddleEllipsis },
        setup() {
            const text = 'uploads/images/2024/vacation-photos/IMG_20240615_143256.jpg';
            return { text };
        },
        template: `
            <div class="space-y-4">
                <div>
                    <div class="text-xs text-gray-600 mb-1">500px</div>
                    <div style="width: 500px;"><MiddleEllipsis :text="text" /></div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-1">400px</div>
                    <div style="width: 400px;"><MiddleEllipsis :text="text" /></div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-1">300px</div>
                    <div style="width: 300px;"><MiddleEllipsis :text="text" /></div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-1">200px</div>
                    <div style="width: 200px;"><MiddleEllipsis :text="text" /></div>
                </div>
            </div>
        `,
    }),
};

const resizableCode = `
<div style="resize: horizontal; overflow: auto; width: 250px;">
    <MiddleEllipsis text="uploads/images/2024/vacation-photos/IMG_20240615_143256.jpg" />
</div>
`;

export const _Resizable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: resizableCode },
        },
    },
    render: () => ({
        components: { MiddleEllipsis },
        template: `
            <div>
                <div class="text-xs text-gray-600 mb-2">Drag the corner to resize</div>
                <div class="border border-dashed rounded" style="resize: horizontal; overflow: auto; min-width: 100px; max-width: 500px; width: 250px; padding: 8px;">
                    <MiddleEllipsis text="uploads/images/2024/vacation-photos/IMG_20240615_143256.jpg" />
                </div>
            </div>
        `,
    }),
};
