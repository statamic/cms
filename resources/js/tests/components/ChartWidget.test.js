import { mount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import ChartWidget from '@/components/forms/summary/ChartWidget.vue';
import { HorizontalBarChart, HorizontalLollipopChart, Pagination, PieChart, VerticalBarChart } from '@ui';
import AverageInsight from '@/components/forms/summary/insights/AverageInsight.vue';

beforeEach(() => {
    vi.stubGlobal('__', (key, replacements = {}) => {
        return Object.entries(replacements).reduce((text, [search, replace]) => {
            return text.replace(`:${search}`, replace);
        }, key);
    });
});

const field = {
    handle: 'wake_me_up',
    display: 'Wake me up',
    icon: 'fieldtype-radio',
    fieldtype: 'multi_choice',
    number: 3,
    responses: 248,
    chart: {
        handle: 'pie',
        component: 'ui-pie-chart',
        props: {
            items: [
                { key: 'go_go', label: 'Before you Go Go', count: 112, percent: 45 },
                { key: 'back_to_life', label: 'Bring me Back to Life', count: 74, percent: 30 },
                { key: 'september', label: 'When September Ends', count: 37, percent: 15 },
                { key: 'other', label: 'Other', count: 25, percent: 10, other: true },
            ],
            drilldown: {
                items: [
                    { key: 'bohemian', label: 'Bohemian Rhapsody', count: 18, percent: 7 },
                    { key: 'wonderwall', label: 'Wonderwall', count: 7, percent: 3 },
                ],
                segments: [],
                focusedIndex: 3,
            },
        },
    },
    insights: [],
};

const barField = {
    ...field,
    chart: {
        handle: 'horizontal_bar',
        component: 'ui-horizontal-bar-chart',
        props: {
            items: [
                { key: 'go_go', label: 'Before you Go Go', count: 112, percent: 45 },
                { key: 'back_to_life', label: 'Bring me Back to Life', count: 74, percent: 30 },
                { key: 'september', label: 'When September Ends', count: 37, percent: 15 },
                { key: 'other', label: 'Other', count: 25, percent: 10, other: true },
            ],
            drilldown: {
                items: [
                    { key: 'bohemian', label: 'Bohemian Rhapsody', count: 18, percent: 7 },
                    { key: 'wonderwall', label: 'Wonderwall', count: 7, percent: 3 },
                ],
                focusedIndex: 3,
            },
        },
    },
};

function mountCard(props = {}) {
    return mount(ChartWidget, {
        props: { field, ...props },
        global: {
            components: {
                'ui-pie-chart': PieChart,
                'ui-horizontal-bar-chart': HorizontalBarChart,
                'ui-vertical-bar-chart': VerticalBarChart,
                'ui-horizontal-lollipop-chart': HorizontalLollipopChart,
                'form-summary-average-insight': AverageInsight,
            },
        },
    });
}

function paginationButtons(wrapper) {
    const [previous, next] = wrapper.findComponent(Pagination).findAll('button');

    return { previous, next };
}

test('it renders the chart items', () => {
    const wrapper = mountCard();

    expect(wrapper.text()).toContain('Wake me up');
    expect(wrapper.text()).toContain('Before you Go Go');
    expect(wrapper.text()).toContain('Other');
    expect(wrapper.text()).not.toContain('Bohemian Rhapsody');
});

test('the pagination pages between the items and the other breakdown', async () => {
    const wrapper = mountCard();
    const { previous, next } = paginationButtons(wrapper);

    expect(previous.attributes('disabled')).toBeDefined();

    await next.trigger('click');

    expect(wrapper.text()).toContain('Bohemian Rhapsody');
    expect(wrapper.text()).not.toContain('Before you Go Go');
    expect(next.attributes('disabled')).toBeDefined();

    await previous.trigger('click');

    expect(wrapper.text()).toContain('Before you Go Go');
});

test('the other slice drills down into the truncated items', async () => {
    const wrapper = mountCard();

    await wrapper.find('.pie-chart-legend__link').trigger('click');

    expect(wrapper.text()).toContain('Bohemian Rhapsody');
    expect(wrapper.text()).not.toContain('Before you Go Go');
});

test('the other bar drills down into the truncated items in the other colour', async () => {
    const wrapper = mountCard({ field: barField });

    await wrapper.find('.summary-bar-chart__link').trigger('click');

    expect(wrapper.text()).toContain('Bohemian Rhapsody');
    expect(wrapper.text()).not.toContain('Before you Go Go');
    expect(wrapper.findAll('.summary-bar-chart__fill').every((bar) => bar.classes('bg-chart-4-legend'))).toBe(true);
});

test('charts without drilldown props dont paginate or make the other item clickable', () => {
    const wrapper = mountCard({
        field: {
            ...field,
            chart: { ...field.chart, props: { items: field.chart.props.items } },
        },
    });

    expect(wrapper.findComponent(Pagination).exists()).toBe(false);
    expect(wrapper.find('.pie-chart-legend__link').exists()).toBe(false);
});

test('it prefixes the field number when enabled', () => {
    const wrapper = mountCard({ showNumber: true });

    expect(wrapper.text()).toContain('3. Wake me up');
});

test.each([
    'ui-horizontal-bar-chart',
    'ui-vertical-bar-chart',
    'ui-pie-chart',
    'ui-horizontal-lollipop-chart',
])('it renders insights alongside the %s component', (component) => {
    const wrapper = mountCard({
        field: {
            ...field,
            chart: { ...field.chart, component },
            insights: [{ handle: 'average', component: 'form-summary-average-insight', props: { average: 3.2 } }],
        },
    });

    expect(wrapper.findComponent(AverageInsight).exists()).toBe(true);
    expect(wrapper.text()).toContain('3.2');
    expect(wrapper.text()).toContain('Average');
});

test('insights stay visible on the other breakdown', async () => {
    const wrapper = mountCard({
        field: {
            ...field,
            insights: [{ handle: 'average', component: 'form-summary-average-insight', props: { average: 3.2 } }],
        },
    });

    await paginationButtons(wrapper).next.trigger('click');

    expect(wrapper.text()).toContain('Bohemian Rhapsody');
    expect(wrapper.findComponent(AverageInsight).exists()).toBe(true);
});

test('it renders no insights row without insights', () => {
    const wrapper = mountCard();

    expect(wrapper.find('[data-chart-insights]').exists()).toBe(false);
});

test('the submission summary components compile', async () => {
    const summary = await import('@/components/forms/summary/Summary.vue');

    expect(summary.default).toBeTruthy();
});

test('it passes the field response count to the chart on both pages', async () => {
    const wrapper = mountCard();

    expect(wrapper.findComponent(PieChart).props('responses')).toBe(248);

    await paginationButtons(wrapper).next.trigger('click');

    expect(wrapper.text()).toContain('Bohemian Rhapsody');
    expect(wrapper.findComponent(PieChart).props('responses')).toBe(248);
});

test.each([
    'ui-horizontal-bar-chart',
    'ui-vertical-bar-chart',
    'ui-pie-chart',
    'ui-horizontal-lollipop-chart',
])('the %s component does not render the response count as an attribute', (component) => {
    const wrapper = mountCard({ field: { ...field, chart: { ...field.chart, component } } });

    expect(wrapper.find('[responses]').exists()).toBe(false);
});

test('it passes a custom chart its own props, with the widget values winning', async () => {
    const CustomChart = {
        props: ['items', 'metric', 'responses', 'accessibleLabel', 'centerLabel', 'drilldown'],
        emits: ['select'],
        template: '<div>{{ items.map((item) => item.label).join(", ") }}</div>',
    };

    const wrapper = mount(ChartWidget, {
        props: {
            metric: 'count',
            field: {
                ...field,
                chart: {
                    handle: 'custom',
                    component: 'custom-chart',
                    props: {
                        ...field.chart.props,
                        centerLabel: 'Songs',
                        metric: 'percent',
                        responses: 1,
                        accessibleLabel: 'Overridden',
                    },
                },
            },
        },
        global: { components: { 'custom-chart': CustomChart } },
    });

    const chart = wrapper.findComponent(CustomChart);

    expect(chart.props('centerLabel')).toBe('Songs');
    expect(chart.props('drilldown')).toBeUndefined();
    expect(chart.props('metric')).toBe('count');
    expect(chart.props('responses')).toBe(248);
    expect(chart.props('accessibleLabel')).toContain('Wake me up: Before you Go Go 112');
    expect(chart.props('items').find((item) => item.other).clickable).toBe(true);

    await paginationButtons(wrapper).next.trigger('click');

    expect(chart.props('items').map((item) => item.key)).toEqual(['bohemian', 'wonderwall']);
    expect(chart.props('centerLabel')).toBeUndefined();
    expect(chart.vm.$attrs.focusedIndex).toBe(3);
});
