import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {DatePicker} from '@statamic/cms/ui';
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
            description: 'Event handler called when the date value changes. <br><br> Returns a [`DateValue` object](https://reka-ui.com/docs/guides/dates).',
            table: {
                category: 'events',
                type: { summary: '(value: DateValue) => void' }
            }
        }
    },
} satisfies Meta<typeof DatePicker>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { DatePicker },
        setup() {
            const date = ref(null);
            return { date };
        },
        template: `
            <DatePicker v-model="date" />
        `,
    }),
};

export const _WithTime: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { DatePicker },
        setup() {
            const appointment = ref(null);
            return { appointment };
        },
        template: `
            <DatePicker v-model="appointment" granularity="minute" />
        `,
    }),
};

export const _MinMax: Story = {
    tags: ['!dev'],
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
        template: `
            <DatePicker v-model="deadline" :min="minDate" :max="maxDate" />
        `,
    }),
};

export const _Inline: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { DatePicker },
        setup() {
            const selectedDate = ref(null);
            return { selectedDate };
        },
        template: `
            <DatePicker v-model="selectedDate" inline />
        `,
    }),
};

export const _MultipleMonths: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { DatePicker },
        setup() {
            const date = ref(null);
            return { date };
        },
        template: `
            <DatePicker v-model="date" :number-of-months="2" />
        `,
    }),
};
