import type { Meta, StoryObj } from '@storybook/vue3';
import { Calendar, Card } from '@ui';

const meta = {
    title: 'Components/Calendar',
    component: Calendar,
    argTypes: {
        min: { control: 'text' },
        max: { control: 'text' },
        numberOfMonths: { control: 'number' },
        weekStartsOn: { control: 'number' },
        weekdayFormat: {
            control: 'select',
            options: ['narrow', 'short', 'long'],
        },
        preventDeselect: { control: 'boolean' },
        disabled: { control: 'boolean' },
        inline: { control: 'boolean' },
    },
} satisfies Meta<typeof Calendar>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Card>
    <Calendar />
</Card>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Calendar, Card },
        template: defaultCode,
    }),
};

const multipleMonthsCode = `
<Card>
    <Calendar :number-of-months="2" />
</Card>
`;

export const _MultipleMonths: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: multipleMonthsCode }
        }
    },
    render: () => ({
        components: { Calendar, Card },
        template: multipleMonthsCode,
    }),
};

const weekStartsOnCode = `
<Card>
    <Calendar week-starts-on="1" />
</Card>
`;

export const _WeekStartsOn: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: weekStartsOnCode }
        }
    },
    render: () => ({
        components: { Calendar, Card },
        template: weekStartsOnCode,
    }),
};

const weekdayFormatCode = `
<Card>
    <Calendar weekday-format="short" />
</Card>
`;

export const _WeekdayFormat: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: weekdayFormatCode }
        }
    },
    render: () => ({
        components: { Calendar, Card },
        template: weekdayFormatCode,
    }),
};
