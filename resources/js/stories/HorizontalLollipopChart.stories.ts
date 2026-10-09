import type {Meta, StoryObj} from '@storybook/vue3';
import {expect, within} from 'storybook/test';
import {HorizontalLollipopChart} from '@ui';

const items = [
    {label: 'Summer', percent: 55, count: 136},
    {label: 'Autumn', percent: 25, count: 62},
    {label: 'Spring', percent: 15, count: 37},
    {label: 'Winter', percent: 10, count: 25},
];

const meta = {
    title: 'Charts/HorizontalLollipopChart',
    component: HorizontalLollipopChart,
    args: {
        accessibleLabel: 'Ranked seasons: Summer 55%, Autumn 25%, Spring 15%, Winter 10%',
        items,
    },
} satisfies Meta<typeof HorizontalLollipopChart>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: `<HorizontalLollipopChart :items="items" accessible-label="Ranked seasons" />`}}},
};

export const _Counts: Story = {
    tags: ['!dev'],
    args: {metric: 'count'},
};

const customEndpointsCode = `
<HorizontalLollipopChart
    :items="countries"
    accessible-label="Ranked countries"
    :show-marker="false"
>
    <template #endpoint="{ item }">
        <span class="text-lg">{{ item.emoji }}</span>
    </template>
</HorizontalLollipopChart>
`;

const countries = [
    {label: 'Japan', percent: 40, count: 99, emoji: '🇯🇵'},
    {label: 'Italy', percent: 35, count: 87, emoji: '🇮🇹'},
    {label: 'France', percent: 25, count: 62, emoji: '🇫🇷'},
];

export const _CustomEndpoints: Story = {
    tags: ['!dev'],
    args: {
        accessibleLabel: 'Ranked countries: Japan 40%, Italy 35%, France 25%',
        items: countries,
        showMarker: false,
    },
    parameters: {docs: {source: {code: customEndpointsCode}}},
    render: (args) => ({
        components: {HorizontalLollipopChart},
        setup: () => ({args}),
        template: `
            <HorizontalLollipopChart v-bind="args">
                <template #endpoint="{ item }">
                    <span class="text-lg">{{ item.emoji }}</span>
                </template>
            </HorizontalLollipopChart>
        `,
    }),
};

export const _ContinuingRanks: Story = {
    tags: ['!dev'],
    args: {
        accessibleLabel: 'Ranks 5 through 8',
        items: items.map((item, index) => ({...item, rank: index + 5})),
    },
    parameters: {docs: {source: {code: `<HorizontalLollipopChart :items="paginatedItems" accessible-label="Ranks 5 through 8" />`}}},
};

export const TestRendersAccessibleChartAndRanks: Story = {
    tags: ['!dev', 'test'],
    play: async ({canvasElement}) => {
        const canvas = within(canvasElement);

        expect(canvas.getByRole('img', {name: /Ranked seasons/})).toBeVisible();
        expect(canvas.getByText('1')).toBeVisible();
        expect(canvas.getByText('Summer')).toBeVisible();
    },
};
