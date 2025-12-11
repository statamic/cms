import type { Meta, StoryObj } from '@storybook/vue3';
import { Switch } from '@ui';

const meta = {
    title: 'Components/Switch',
    component: Switch,
    argTypes: {
        size: {
            control: 'select',
            options: ['lg', 'default', 'sm', 'xs'],
        },
        disabled: { control: 'boolean' },
        checked: { control: 'boolean' },
    },
} satisfies Meta<typeof Switch>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Switch />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Switch },
        template: defaultCode,
    }),
};

const sizesCode = `
<div class="flex items-center">
    <Switch size="lg" />
    <Switch />
    <Switch size="sm" />
    <Switch size="xs" />
</div>
`;

export const _Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode }
        }
    },
    render: () => ({
        components: { Switch },
        template: sizesCode,
    }),
};
