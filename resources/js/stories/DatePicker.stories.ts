import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {DatePicker} from '@ui';
import {parseDate} from '@internationalized/date';

const meta = {
    title: 'Forms/DatePicker',
    component: DatePicker,
    argTypes: {
        granularity: {
            control: 'select',
            options: ['day', 'hour', 'minute', 'second'],
        },
        'update:modelValue': {
            description: 'Event handler called when the date value changes. Returns the date as an ISO 8601 date and time string.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        }
    },
} satisfies Meta<typeof DatePicker>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<DatePicker v-model="date" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { DatePicker },
        setup() {
            const date = ref(null);
            return { date };
        },
        template: defaultCode,
    }),
};

const withTimeCode = `
<DatePicker v-model="appointment" granularity="minute" />
`;

export const _WithTime: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withTimeCode }
        }
    },
    render: () => ({
        components: { DatePicker },
        setup() {
            const appointment = ref(null);
            return { appointment };
        },
        template: withTimeCode,
    }),
};

const minMaxCode = `
<DatePicker v-model="deadline" :min="minDate" :max="maxDate" />
`;

export const _MinMax: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: minMaxCode }
        }
    },
    render: () => ({
        components: { DatePicker },
        setup() {
            const deadline = ref(null);
            const today = new Date();
            const minDate = parseDate(today.toISOString().split('T')[0]);
            const futureDate = new Date(today);
            futureDate.setDate(today.getDate() + 30);
            const maxDate = parseDate(futureDate.toISOString().split('T')[0]);
            return { deadline, minDate, maxDate };
        },
        template: minMaxCode,
    }),
};

const inlineCode = `
<DatePicker v-model="selectedDate" inline />
`;

export const _Inline: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: inlineCode }
        }
    },
    render: () => ({
        components: { DatePicker },
        setup() {
            const selectedDate = ref(null);
            return { selectedDate };
        },
        template: inlineCode,
    }),
};

const multipleMonthsCode = `
<DatePicker v-model="date" :number-of-months="2" />
`;

export const _MultipleMonths: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: multipleMonthsCode }
        }
    },
    render: () => ({
        components: { DatePicker },
        setup() {
            const date = ref(null);
            return { date };
        },
        template: multipleMonthsCode,
    }),
};
