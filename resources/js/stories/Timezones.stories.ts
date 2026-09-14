import type {Meta, StoryObj} from '@storybook/vue3';
import {Timezones, TimezoneHoverCard} from '@ui';

const exampleDate = '2026-05-05T12:00:00.000Z';
const exampleRange = { start: '2026-05-05T12:00:00.000Z', end: '2026-05-08T17:30:00.000Z' };

const meta = {
    title: 'Overlays/Timezones',
    component: Timezones,
    argTypes: {
        date: {
            control: 'text',
        },
    },
} satisfies Meta<typeof Timezones>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Timezones :date="date" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    args: {
        date: exampleDate,
    },
    parameters: {
        docs: {
            source: { code: `<Timezones date="${exampleDate}" />` }
        }
    },
    render: (args) => ({
        components: { Timezones },
        setup() {
            return { date: args.date };
        },
        template: defaultCode,
    }),
};

const hoverCardCode = `
<TimezoneHoverCard :date="date">
    <span class="underline decoration-dotted">Hover the underlined text</span>
</TimezoneHoverCard>
`;

export const _HoverCard: Story = {
    tags: ['!dev'],
    args: {
        date: exampleDate,
    },
    parameters: {
        docs: {
            source: { code: hoverCardCode }
        }
    },
    render: (args) => ({
        components: { TimezoneHoverCard },
        setup() {
            return { date: args.date };
        },
        template: `
            <div class="flex justify-center p-12">
                ${hoverCardCode}
            </div>
        `,
    }),
};

const rangeCode = `
<Timezones :date="{ start, end }" />
`;

export const _Range: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: rangeCode }
        }
    },
    render: () => ({
        components: { Timezones },
        setup() {
            return { range: exampleRange };
        },
        template: `<Timezones :date="range" />`,
    }),
};

const customDateCode = `
<Timezones :date="isoString" />
<Timezones :date="dateObject" />
`;

export const _Invalid: Story = {
    args: {
        date: "not a date",
    },
    render: (args) => ({
        components: { Timezones },
        setup() {
            return { date: args.date };
        },
        template: `
            <div>
                <p class="mb-2 text-sm">Nothing should render below:</p>
                <Timezones :date="date" />
            </div>
        `,
    }),
};

export const _CustomDate: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customDateCode }
        }
    },
    render: () => ({
        components: { Timezones },
        setup() {
            return {
                isoString: exampleDate,
                dateObject: new Date(exampleDate),
            };
        },
        template: `
            <div class="space-y-4">
                ${customDateCode}
            </div>
        `,
    }),
};
