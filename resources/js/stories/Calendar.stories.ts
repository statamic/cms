import type {Meta, StoryObj} from '@storybook/vue3';
import {Calendar, Card} from '@ui';

const meta = {
    title: 'Forms/Calendar',
    component: Calendar,
    argTypes: {
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
