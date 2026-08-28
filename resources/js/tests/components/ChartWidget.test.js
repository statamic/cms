import { mount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import ChartWidget from '@/components/forms/summary/ChartWidget.vue';
import { PieChart } from '@ui';

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

function mountCard(props = {}) {
    return mount(ChartWidget, {
        props: { field, ...props },
        global: { components: { 'ui-pie-chart': PieChart } },
    });
}

test('it renders the chart items', () => {
    const wrapper = mountCard();

    expect(wrapper.text()).toContain('Wake me up');
    expect(wrapper.text()).toContain('Before you Go Go');
    expect(wrapper.text()).toContain('Other');
    expect(wrapper.text()).not.toContain('Bohemian Rhapsody');
});

test('the other slice drills down into the truncated items and back again', async () => {
    const wrapper = mountCard();

    await wrapper.find('.pie-chart-legend__link').trigger('click');

    expect(wrapper.text()).toContain('Bohemian Rhapsody');
    expect(wrapper.text()).not.toContain('Before you Go Go');

    await wrapper.findAll('button').find((button) => button.text() === 'Back').trigger('click');

    expect(wrapper.text()).toContain('Before you Go Go');
});

test('charts without drilldown props dont make the other item clickable', () => {
    const wrapper = mountCard({
        field: {
            ...field,
            chart: { ...field.chart, props: { items: field.chart.props.items } },
        },
    });

    expect(wrapper.find('.pie-chart-legend__link').exists()).toBe(false);
});

test('it prefixes the field number when enabled', () => {
    const wrapper = mountCard({ showNumber: true });

    expect(wrapper.text()).toContain('3. Wake me up');
});

test('the submission summary components compile', async () => {
    const summary = await import('@/components/forms/summary/Summary.vue');

    expect(summary.default).toBeTruthy();
});
