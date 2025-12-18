import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {DateRangePicker} from '@ui';
import {parseDate} from '@internationalized/date';

const meta = {
    title: 'Forms/DateRangePicker',
    component: DateRangePicker,
    argTypes: {
        modelValue: {
            control: 'object',
            description: 'The controlled date range value with `start` and `end` properties. <br><br> Each should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`)',
        },
        badge: {
            control: 'text',
            description: 'Badge text to display.',
        },
        required: { control: 'boolean' },
        min: {
            control: 'object',
            description: 'The minimum selectable date. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`)',
        },
        max: {
            control: 'object',
            description: 'The maximum selectable date. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`)',
        },
        granularity: {
            control: 'select',
            description: 'The granularity of the date range picker. <br><br> Options: `day`, `hour`, `minute`, `second`',
            options: ['day', 'hour', 'minute', 'second'],
        },
        inline: {
            control: 'boolean',
            description: 'When `true`, the calendar is always visible instead of appearing in a popover.',
        },
        clearable: {
            control: 'boolean',
            description: 'When `true`, a clear button is displayed to reset the date range.',
        },
        disabled: { control: 'boolean' },
        readOnly: { control: 'boolean' },
        'update:modelValue': {
            description: 'Event handler called when the date range value changes. <br><br> Returns an object with `start` and `end` properties, each as an ISO 8601 date and time string.',
            table: {
                category: 'events',
                type: { summary: '(value: { start: string, end: string }) => void' }
            }
        }
    },
} satisfies Meta<typeof DateRangePicker>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<DateRangePicker v-model="dateRange" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { DateRangePicker },
        setup() {
            const dateRange = ref(null);
            return { dateRange };
        },
        template: defaultCode,
    }),
};

const minMaxCode = `
<DateRangePicker v-model="vacation" :min="minDate" :max="maxDate" />
`;

export const _MinMax: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: minMaxCode }
        }
    },
    render: () => ({
        components: { DateRangePicker },
        setup() {
            const vacation = ref(null);
            const today = new Date();
            const minDate = parseDate(today.toISOString().split('T')[0]);
            const futureDate = new Date(today);
            futureDate.setDate(today.getDate() + 90);
            const maxDate = parseDate(futureDate.toISOString().split('T')[0]);
            return { vacation, minDate, maxDate };
        },
        template: minMaxCode,
    }),
};

const inlineCode = `
<DateRangePicker v-model="range" inline />
`;

export const _Inline: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: inlineCode }
        }
    },
    render: () => ({
        components: { DateRangePicker },
        setup() {
            const range = ref(null);
            return { range };
        },
        template: inlineCode,
    }),
};
