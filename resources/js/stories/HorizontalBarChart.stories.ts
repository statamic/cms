import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {expect, within} from 'storybook/test';
import {Button, HorizontalBarChart, Icon, Widget} from '@ui';

const items = [
    {label: 'Yes', percent: 55, count: 136},
    {label: 'No', percent: 35, count: 87},
    {label: 'Maybe', percent: 10, count: 25},
];

const meta = {
    title: 'Charts/HorizontalBarChart',
    component: HorizontalBarChart,
    args: {
        accessibleLabel: 'Responses: Yes 55%, No 35%, Maybe 10%',
        items,
    },
} satisfies Meta<typeof HorizontalBarChart>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<HorizontalBarChart
    accessible-label="Responses: Yes 55%, No 35%, Maybe 10%"
    :items="items"
/>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: defaultCode}}},
    render: (args) => ({
        components: {HorizontalBarChart},
        setup: () => ({args}),
        template: `<HorizontalBarChart v-bind="args" />`,
    }),
};

export const _Counts: Story = {
    tags: ['!dev'],
    args: {metric: 'count'},
};

const metricAtEndCode = `
<HorizontalBarChart
    :items="items"
    accessible-label="Responses: Yes 55%, No 35%, Maybe 10%"
    metric-position="end"
    :show-marker="false"
/>
`;

export const _MetricAtEnd: Story = {
    tags: ['!dev'],
    args: {
        metricPosition: 'end',
        showMarker: false,
    },
    parameters: {docs: {source: {code: metricAtEndCode}}},
};

const customMarkersCode = `
<HorizontalBarChart :items="items" accessible-label="Attendance responses">
    <template #marker="{ item }">
        <Icon :name="item.icon" class="size-4" />
    </template>
</HorizontalBarChart>
`;

const breakdownCode = `
<HorizontalBarChart
    v-if="!showBreakdown"
    :items="items"
    accessible-label="Favourite season"
    @select="showBreakdown = true"
/>
<HorizontalBarChart
    v-else
    :items="breakdown"
    :focused-index="4"
    accessible-label="Other seasons"
/>
`;

export const _Breakdown: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: breakdownCode}}},
    render: () => ({
        components: {Button, HorizontalBarChart},
        setup() {
            const showBreakdown = ref(false);
            const seasons = [
                {label: 'Summer', percent: 40, count: 99},
                {label: 'Autumn', percent: 25, count: 62},
                {label: 'Spring', percent: 15, count: 37},
                {label: 'Winter', percent: 10, count: 25},
                {label: 'Other', percent: 10, count: 25, clickable: true},
            ];
            const breakdown = [
                {label: 'Monsoon', percent: 6, count: 15},
                {label: 'Dry season', percent: 4, count: 10},
            ];

            return {breakdown, seasons, showBreakdown};
        },
        template: `
            <div class="space-y-3">
                <Button v-if="showBreakdown" text="Back to all seasons" size="sm" @click="showBreakdown = false" />
                <HorizontalBarChart
                    v-if="!showBreakdown"
                    :items="seasons"
                    accessible-label="Favourite season: Summer 40%, Autumn 25%, Spring 15%, Winter 10%, Other 10%"
                    @select="showBreakdown = true"
                />
                <HorizontalBarChart
                    v-else
                    :items="breakdown"
                    :focused-index="4"
                    accessible-label="Other seasons: Monsoon 6%, Dry season 4%"
                />
            </div>
        `,
    }),
};

export const _CustomMarkers: Story = {
    tags: ['!dev'],
    args: {
        items: [
            {...items[0], icon: 'checkmark-circle-filled'},
            {...items[1], icon: 'delete-circle-filled'},
            {...items[2], icon: 'circle'},
        ],
    },
    parameters: {docs: {source: {code: customMarkersCode}}},
    render: (args) => ({
        components: {HorizontalBarChart, Icon},
        setup: () => ({args}),
        template: `
            <HorizontalBarChart v-bind="args">
                <template #marker="{ item }">
                    <Icon :name="item.icon" class="size-4" />
                </template>
            </HorizontalBarChart>
        `,
    }),
};

const widgetCode = `
<Widget title="Have you attended before?" icon="fieldtype-select">
    <HorizontalBarChart :items="items" accessible-label="Attendance responses" />
</Widget>
`;

export const _InsideAWidget: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: widgetCode}}},
    render: (args) => ({
        components: {HorizontalBarChart, Widget},
        setup: () => ({args}),
        template: `
            <Widget title="Have you attended before?" icon="fieldtype-select">
                <HorizontalBarChart v-bind="args" />
            </Widget>
        `,
    }),
};

export const TestRendersAccessibleChartAndPercentages: Story = {
    tags: ['!dev', 'test'],
    play: async ({canvasElement}) => {
        const canvas = within(canvasElement);

        expect(canvas.getByRole('img', {name: 'Responses: Yes 55%, No 35%, Maybe 10%'})).toBeVisible();
        expect(canvas.getByText('55%')).toBeVisible();
    },
};

export const TestRendersCounts: Story = {
    tags: ['!dev', 'test'],
    args: {metric: 'count'},
    play: async ({canvasElement}) => {
        expect(within(canvasElement).getByText('136')).toBeVisible();
    },
};
