import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {TimePicker} from '@ui';

const meta = {
    title: 'Forms/TimePicker',
    component: TimePicker,
    argTypes: {
        modelValue: {
            control: 'object',
            description: 'The controlled time value.',
        },
        badge: {
            control: 'text',
            description: 'Badge text to display.',
        },
        required: { control: 'boolean' },
        granularity: {
            control: 'select',
            description: 'The granularity of the time picker. <br><br> Options: `hour`, `minute`, `second`',
            options: ['hour', 'minute', 'second'],
        },
        clearable: {
            control: 'boolean',
            description: 'When `true`, clear and "set to now" buttons are displayed.',
        },
        'update:modelValue': {
            description: 'Event handler called when the time value changes.',
            table: {
                category: 'events',
                type: { summary: '(value: TimeValue) => void' }
            }
        }
    },
} satisfies Meta<typeof TimePicker>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<TimePicker v-model="time" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { TimePicker },
        setup() {
            const time = ref(null);
            return { time };
        },
        template: defaultCode,
    }),
};

const withSecondsCode = `
<TimePicker v-model="time" granularity="second" />
`;

export const _WithSeconds: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withSecondsCode }
        }
    },
    render: () => ({
        components: { TimePicker },
        setup() {
            const time = ref(null);
            return { preciseTime: time };
        },
        template: withSecondsCode,
    }),
};
