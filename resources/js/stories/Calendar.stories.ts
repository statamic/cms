import type {Meta, StoryObj} from '@storybook/vue3';
import {Calendar, Card} from '@ui';

const meta = {
    title: 'Forms/Calendar',
    component: Calendar,
    argTypes: {
        modelValue: {
            control: 'text',
            description: 'The controlled value of the calendar. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`)',
        },
        min: {
            control: 'text',
            description: 'The earliest date that can be selected. Dates before this will be disabled. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`)',
        },
        max: {
            control: 'text',
            description: 'The latest date that can be selected. Dates after this will be disabled. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`)',
        },
        components: {
            control: 'object',
            description: "If necessary, you can you swap out any of the internal Calendar components by passing an object to this prop.",
        },
        numberOfMonths: {
            control: 'number',
            description: 'The number of months to display at once.',
        },
        inline: { control: 'boolean' },
        'update:modelValue': {
            description: 'Event handler called when a date is selected. Returns the date as an ISO 8601 date and time string.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        }
    },
} satisfies Meta<typeof Calendar>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `<Calendar />`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Calendar, Card },
        template: `<Card>${defaultCode}</Card>`,
    }),
};

const multipleMonthsCode = `<Calendar :number-of-months="2" />`;

export const _MultipleMonths: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: multipleMonthsCode }
        }
    },
    render: () => ({
        components: { Calendar, Card },
        template: `<Card>${multipleMonthsCode}</Card>`,
    }),
};

const weekStartsOnCode = `<Calendar week-starts-on="1" />`;

export const _WeekStartsOn: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: weekStartsOnCode }
        }
    },
    render: () => ({
        components: { Calendar, Card },
        template: `<Card>${weekStartsOnCode}</Card>`,
    }),
};
