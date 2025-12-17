import type {Meta, StoryObj} from '@storybook/vue3';
import {Switch} from '@ui';

const meta = {
    title: 'Components/Switch',
    component: Switch,
    argTypes: {
        required: { control: 'boolean' },
        id: { control: 'text' },
        modelValue: {
            control: 'text',
            description: 'The controlled value of the switch.',
        },
        size: {
            control: 'select',
            description: 'Controls the size of the switch. <br><br> Options: `xs`, `sm`, `base`, `lg`',
            options: ['xs', 'sm', 'base', 'lg'],
        },
        'update:modelValue': {
            description: 'Event handler called when the value changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        }
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
