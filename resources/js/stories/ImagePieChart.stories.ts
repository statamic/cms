import type {Meta, StoryObj} from '@storybook/vue3';
import {expect, within} from 'storybook/test';
import {ImagePieChart, Widget} from '@ui';

const items = [
    {label: 'Mountains', percent: 55, count: 136, badge: 'A', image: 'https://picsum.photos/id/29/320/320'},
    {label: 'Coast', percent: 45, count: 112, badge: 'B', image: 'https://picsum.photos/id/43/320/320'},
];

const meta = {
    title: 'Charts/ImagePieChart',
    component: ImagePieChart,
    args: {
        accessibleLabel: 'Destinations: Mountains 55%, Coast 45%',
        items,
    },
} satisfies Meta<typeof ImagePieChart>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: `<ImagePieChart :items="items" accessible-label="Destinations" />`}}},
};

export const _Counts: Story = {
    tags: ['!dev'],
    args: {metric: 'count'},
};

export const _InsideAWidget: Story = {
    tags: ['!dev'],
    parameters: {docs: {source: {code: `<Widget title="Preferred destination" icon="image-select">
    <ImagePieChart :items="items" accessible-label="Preferred destinations" />
</Widget>`}}},
    render: (args) => ({
        components: {ImagePieChart, Widget},
        setup: () => ({args}),
        template: `
            <Widget title="Preferred destination" icon="image-select">
                <ImagePieChart v-bind="args" />
            </Widget>
        `,
    }),
};

export const TestRendersAccessibleChartAndImages: Story = {
    tags: ['!dev', 'test'],
    play: async ({canvasElement}) => {
        const canvas = within(canvasElement);

        expect(canvas.getByRole('img', {name: /Destinations/})).toBeVisible();
        expect(canvasElement.querySelectorAll('img')).toHaveLength(2);
    },
};
