import type {Meta, StoryObj} from '@storybook/vue3';
import {Switch} from '@statamic/cms/ui';
import {ref} from 'vue';

const meta = {
    title: 'Forms/Switch',
    component: Switch,
    args: {
        label: 'Enabled',
    },
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

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: (args) => ({
        components: { Switch },
        setup() {
            const enabled = ref(false);
            return { args, enabled };
        },
        template: `
            <Switch v-model="enabled" :label="args.label" />
        `,
    }),
};

export const _Sizes: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Switch },
        setup() {
            const lg = ref(false);
            const base = ref(false);
            const sm = ref(false);
            const xs = ref(false);
            return { lg, base, sm, xs };
        },
        template: `
            <div class="flex items-center gap-2">
                <Switch v-model="lg" size="lg" label="Large" />
                <Switch v-model="base" label="Base" />
                <Switch v-model="sm" size="sm" label="Small" />
                <Switch v-model="xs" size="xs" label="Extra small" />
            </div>
        `,
    }),
};
