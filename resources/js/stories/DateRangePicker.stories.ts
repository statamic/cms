import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {DateRangePicker} from '@ui';
import {parseDate} from '@internationalized/date';

const meta = {
    title: 'Forms/DateRangePicker',
    component: DateRangePicker,
    argTypes: {
        granularity: {
            control: 'select',
            options: ['day', 'hour', 'minute', 'second'],
        },
        'update:modelValue': {
            description: 'Event handler called when the date range value changes. <br><br> Returns a range object with `start` and `end` values as `@internationalized/date` instances (e.g. `CalendarDate`, `CalendarDateTime`, `ZonedDateTime`) or `null` when the selection is cleared.',
            table: {
                category: 'events',
                type: { summary: '(value: { start: DateValue | null; end: DateValue | null } | null) => void' }
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

const granularityDayCode = `
<DateRangePicker v-model="range" granularity="day" />
`;

export const _GranularityDay: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: granularityDayCode }
        }
    },
    render: () => ({
        components: { DateRangePicker },
        setup() {
            const range = ref({start: null, end: null});
            return { range };
        },
        template: granularityDayCode,
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
            const range = ref({start: null, end: null});
            return { range };
        },
        template: inlineCode,
    }),
};
