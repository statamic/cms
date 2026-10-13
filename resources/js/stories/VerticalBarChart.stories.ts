import type {Meta, StoryObj} from '@storybook/vue3';
import {expect, within} from 'storybook/test';
import {VerticalBarChart} from '@ui';

const items = [
    {label: '1', percent: 10, count: 25},
    {label: '2', percent: 20, count: 50},
    {label: '3', percent: 35, count: 87},
    {label: '4', percent: 25, count: 62},
    {label: '5', percent: 10, count: 25},
];

const meta = {
    title: 'Charts/VerticalBarChart',
    component: VerticalBarChart,
    args: {
        accessibleLabel: 'Rating distribution: 1: 10%, 2: 20%, 3: 35%, 4: 25%, 5: 10%',
        items,
    },
    decorators: [() => ({template: '<div style="height: 16rem"><story /></div>'})],
} satisfies Meta<typeof VerticalBarChart>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: `<VerticalBarChart :items="items" accessible-label="Rating distribution" />`}}},
};

export const _Counts: Story = {
    tags: ['!dev'],
    args: {metric: 'count'},
};

export const _FixedScale: Story = {
    tags: ['!dev'],
    args: {maxValue: 100},
    parameters: {docs: {source: {code: `<VerticalBarChart :items="items" :max-value="100" accessible-label="Rating distribution" />`}}},
};

export const TestRendersAccessibleChartAndScalesBars: Story = {
    tags: ['!dev', 'test'],
    play: async ({canvasElement}) => {
        const canvas = within(canvasElement);
        const chart = canvas.getByRole('img', {name: /Rating distribution/});
        const bars = chart.querySelector('.vertical-bar-chart') as HTMLElement;
        const fill = chart.querySelector('.vertical-bar-chart__fill') as HTMLElement;

        expect(chart).toBeVisible();
        expect(getComputedStyle(chart).display).toBe('grid');
        expect(getComputedStyle(bars).display).toBe('flex');
        expect(getComputedStyle(fill).maxWidth).toBe('32px');
        expect(fill).toHaveStyle({flexBasis: `${(10 / 35) * 100}%`});
    },
};
