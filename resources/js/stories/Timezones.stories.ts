import type {Meta, StoryObj} from '@storybook/vue3';
import {Timezones, TimezoneHoverCard} from '@ui';

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
<TimezoneHoverCard :date="date" :side="side" :align="align">
    <span class="underline decoration-dotted">Hover the underlined text</span>
</TimezoneHoverCard>
`;

export const _HoverCard: Story = {
    tags: ['!dev'],
    args: {
        date: exampleDate,
        side: 'bottom',
        align: 'center',
    },
    argTypes: {
        side: {
            control: 'select',
            options: ['top', 'bottom', 'left', 'right'],
        },
        align: {
            control: 'select',
            options: ['start', 'center', 'end'],
        },
    },
    parameters: {
        docs: {
            source: {
                code: `<TimezoneHoverCard date="${exampleDate}">Hover the underlined text</TimezoneHoverCard>`
            }
        }
    },
    render: (args) => ({
        components: { TimezoneHoverCard },
        setup() {
            return { date: args.date, side: args.side, align: args.align };
        },
        template: `
            <div class="flex justify-center p-12">
                ${hoverCardCode}
            </div>
        `,
    }),
};

const customDateCode = `
<Timezones :date="isoString" />
<Timezones :date="dateObject" />
`;

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
