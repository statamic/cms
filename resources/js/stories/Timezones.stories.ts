import type {Meta, StoryObj} from '@storybook/vue3';
import {Timezones, TimezoneHoverCard} from '@statamic/cms/ui';

const exampleDate = '2026-05-05T12:00:00.000Z';

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
            const date = args.date;
            return { date };
        },
        template: `
            <Timezones :date="date" />
        `,
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
            const date = args.date;
            return { date };
        },
        template: `
            <div class="flex justify-center p-12">
                <TimezoneHoverCard :date="date">
                    <span class="underline decoration-dotted">Hover the underlined text</span>
                </TimezoneHoverCard>
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
            const range = { start: '2026-05-05T12:00:00.000Z', end: '2026-05-08T17:30:00.000Z' };
            return { range };
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
            const date = args.date;
            return { date };
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
            const isoString = '2026-05-05T12:00:00.000Z';
            const dateObject = new Date(isoString);
            return { isoString, dateObject };
        },
        template: `
            <div class="space-y-4">
                <Timezones :date="isoString" />
                <Timezones :date="dateObject" />
            </div>
        `,
    }),
};
