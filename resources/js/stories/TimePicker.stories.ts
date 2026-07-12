import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {TimePicker} from '@ui';

const meta = {
    title: 'Forms/TimePicker',
    component: TimePicker,
    argTypes: {
        granularity: {
            control: 'select',
            options: ['hour', 'minute', 'second'],
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
