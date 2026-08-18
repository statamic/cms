import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {expect, fn, userEvent, within} from 'storybook/test';
import {Button, Icon, PieChart} from '@ui';

const items = [
    {label: 'Email', percent: 45, count: 112},
    {label: 'Search', percent: 30, count: 74},
    {label: 'Social', percent: 15, count: 37},
    {label: 'Other', percent: 10, count: 25},
];

const meta = {
    title: 'Charts/PieChart',
    component: PieChart,
    args: {
        accessibleLabel: 'Traffic sources: Email 45%, Search 30%, Social 15%, Other 10%',
        items,
    },
} satisfies Meta<typeof PieChart>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: `<PieChart :items="items" accessible-label="Traffic sources" />`}}},
};

export const _Counts: Story = {
    tags: ['!dev'],
    args: {metric: 'count'},
    parameters: {docs: {source: {code: `<PieChart :items="items" metric="count" accessible-label="Traffic sources" />`}}},
};

export const _FocusedSegment: Story = {
    tags: ['!dev'],
    args: {
        focusedIndex: 3,
        items: [{label: 'Direct', percent: 6, count: 15}, {label: 'Referral', percent: 4, count: 10}],
        segments: items,
    },
    parameters: {docs: {source: {code: `<PieChart :items="breakdown" :segments="items" :focused-index="3" accessible-label="Other traffic sources" />`}}},
};

const interactiveCode = `
<PieChart
    v-if="!showBreakdown"
    :items="items"
    accessible-label="Traffic sources"
    @select="showBreakdown = true"
/>
<PieChart
    v-else
    :items="breakdown"
    :segments="items"
    :focused-index="3"
    accessible-label="Other traffic sources"
/>
`;

export const _InteractiveBreakdown: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: interactiveCode}}},
    render: () => ({
        components: {Button, PieChart},
        setup() {
            const showBreakdown = ref(false);
            const drilldownItems = [...items.slice(0, 3), {...items[3], clickable: true}];
            const breakdown = [
                {label: 'Direct', percent: 6, count: 15},
                {label: 'Referral', percent: 4, count: 10},
            ];

            return {breakdown, drilldownItems, showBreakdown};
        },
        template: `
            <div class="space-y-3">
                <Button v-if="showBreakdown" text="Back to all sources" size="sm" @click="showBreakdown = false" />
                <PieChart
                    v-if="!showBreakdown"
                    :items="drilldownItems"
                    accessible-label="Traffic sources: Email 45%, Search 30%, Social 15%, Other 10%"
                    @select="showBreakdown = true"
                />
                <PieChart
                    v-else
                    :items="breakdown"
                    :segments="drilldownItems"
                    :focused-index="3"
                    accessible-label="Other traffic sources: Direct 6%, Referral 4%"
                />
            </div>
        `,
    }),
};

export const _CustomMarkers: Story = {
    tags: ['!dev'],
    args: {
        items: items.map((item, index) => ({...item, icon: ['mail', 'search-magnifying-glass', 'users', 'dots'][index]})),
    },
    parameters: {docs: {source: {code: `<PieChart :items="items" accessible-label="Traffic sources">
    <template #marker="{ item }">
        <Icon :name="item.icon" class="size-4" />
    </template>
</PieChart>`}}},
    render: (args) => ({
        components: {Icon, PieChart},
        setup: () => ({args}),
        template: `
            <PieChart v-bind="args">
                <template #marker="{ item }">
                    <Icon :name="item.icon" class="size-4" />
                </template>
            </PieChart>
        `,
    }),
};

export const TestRendersAccessibleChartAndLegend: Story = {
    tags: ['!dev', 'test'],
    play: async ({canvasElement}) => {
        const canvas = within(canvasElement);

        expect(canvas.getByRole('img', {name: /Traffic sources/})).toBeVisible();
        expect(canvas.getByText('Email')).toBeVisible();
    },
};

export const TestClickableLegendItemEmitsSelection: Story = {
    tags: ['!dev', 'test'],
    args: {
        items: [...items.slice(0, 3), {...items[3], clickable: true}],
        onSelect: fn(),
    },
    play: async ({canvasElement, args}) => {
        await userEvent.click(within(canvasElement).getByRole('button', {name: /Other/}));

        expect(args.onSelect).toHaveBeenCalled();
    },
};
