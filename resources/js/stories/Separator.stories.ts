import type { Meta, StoryObj } from '@storybook/vue3';
import { Separator } from '@ui';

const meta = {
    title: 'Components/Separator',
    component: Separator,
    argTypes: {
        text: { control: 'text' },
        variant: {
            control: 'select',
            options: ['default', 'dots'],
        },
        vertical: { control: 'boolean' },
    },
} satisfies Meta<typeof Separator>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Separator text="vs" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Separator },
        template: defaultCode,
    }),
};

const variantsCode = `
<div class="flex flex-col w-full">
    <Separator text="Line Separator (Default)" />
    <Separator variant="dots" text="Dotted Separator" />
</div>
`;

export const _Variants: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: variantsCode }
        }
    },
    render: () => ({
        components: { Separator },
        template: variantsCode,
    }),
};

const textCode = `
<Separator text="Breaker High" />
`;

export const _Text: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: textCode }
        }
    },
    render: () => ({
        components: { Separator },
        template: textCode,
    }),
};

const verticalCode = `
<div class="flex items-center h-6 space-x-4">
    <div>Left Content</div>
    <Separator vertical />
    <div>Right Content</div>
</div>
`;

export const _Vertical: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: verticalCode }
        }
    },
    render: () => ({
        components: { Separator },
        template: verticalCode,
    }),
};
