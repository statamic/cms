import type {Meta, StoryObj} from '@storybook/vue3';
import {Switch} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Components/Switch',
    component: Switch,
    argTypes: {
        size: {
            control: 'select',
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
<Switch v-model="enabled" />
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
        setup() {
            const enabled = ref(false);
            return { enabled };
        },
        template: defaultCode,
    }),
};

const sizesCode = `
<div class="flex items-center gap-2">
    <Switch v-model="lg" size="lg" />
    <Switch v-model="base" />
    <Switch v-model="sm" size="sm" />
    <Switch v-model="xs" size="xs" />
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
        setup() {
            const lg = ref(false);
            const base = ref(false);
            const sm = ref(false);
            const xs = ref(false);
            return { lg, base, sm, xs };
        },
        template: sizesCode,
    }),
};
