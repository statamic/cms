import { flushPromises, shallowMount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import axios from 'axios';
import Summary from '@/components/forms/summary/Summary.vue';

vi.mock('axios', () => ({
    default: { get: vi.fn(), patch: vi.fn(), isCancel: (error) => error?.name === 'CanceledError' },
}));

vi.mock('@api', () => ({
    dirty: { state: vi.fn(), remove: vi.fn() },
    keys: { bindGlobal: () => ({ destroy: () => {} }) },
    preferences: { get: (key, fallback) => fallback, set: vi.fn() },
}));

vi.mock('@ui', () => ({ Button: {}, Skeleton: {}, ToggleGroup: {}, ToggleItem: {}, Widget: {} }));
vi.mock('@/components/sortable/Sortable.js', () => ({ SortableList: {} }));
vi.mock('@/bootstrap/globals.js', () => ({
    clone: (value) => JSON.parse(JSON.stringify(value)),
    utf8btoa: (value) => value,
}));

vi.mock('@/components/ui/Listing/Listing.vue', async () => {
    const { ref } = await import('vue');

    return {
        injectListingContext: () => ({
            activeFilters: ref({}),
            searchQuery: ref(''),
            preferencesPrefix: ref('forms.survey'),
        }),
    };
});

const toast = { error: vi.fn(), success: vi.fn() };

beforeEach(() => {
    vi.clearAllMocks();
    vi.stubGlobal('__', (key) => key);
    vi.stubGlobal('Statamic', { $toast: toast });
});

function field(handle, chart, layout = { field: handle, chart }) {
    return {
        handle,
        display: handle,
        icon: 'fieldtype-radio',
        fieldtype: 'multi_choice',
        number: 1,
        responses: 1,
        chart: { handle: chart, component: `ui-${chart}-chart`, props: { items: [] } },
        insights: [],
        layout,
    };
}

function summary(fields) {
    return {
        total: 1,
        fields,
        meta: {
            charts: [],
            fields: [
                {
                    handle: 'color',
                    display: 'Color',
                    icon: 'fieldtype-radio',
                    default_chart: 'horizontal_bar',
                    charts: ['horizontal_bar', 'pie'],
                },
            ],
        },
    };
}

async function mountEditing(fields = [field('color', 'horizontal_bar')]) {
    axios.get.mockResolvedValueOnce({ data: summary(fields) });

    const wrapper = shallowMount(Summary, {
        props: { form: 'survey', summaryUrl: '/summary', chartsUpdateUrl: '/charts' },
        global: { mocks: { __: (key) => key, $number: { format: (value) => value } } },
    });

    await flushPromises();
    wrapper.vm.startEditing();
    await flushPromises();

    return wrapper;
}

async function settle() {
    for (let i = 0; i < 10; i++) await flushPromises();
}

test('the chart dropdown only offers charts that apply to the field', async () => {
    const wrapper = await mountEditing();

    wrapper.vm.summary.meta.charts = ['horizontal_bar', 'pie', 'ranked_options'].map((handle) => ({
        handle,
        title: handle,
        icon: null,
        component: `ui-${handle}-chart`,
    }));

    expect(wrapper.vm.chartsFor('color').map((chart) => chart.handle)).toEqual(['horizontal_bar', 'pie']);
    expect(wrapper.vm.chartsFor('missing')).toEqual([]);
});

test('saving sends back the stored insights untouched', async () => {
    const insights = [{ type: 'checked' }, { type: 'average' }];
    const wrapper = await mountEditing([
        field('color', 'horizontal_bar', { field: 'color', chart: 'horizontal_bar', insights }),
    ]);

    axios.patch.mockResolvedValue({});
    axios.get.mockResolvedValue({ data: summary([field('color', 'horizontal_bar')]) });
    await wrapper.vm.save();

    expect(axios.patch.mock.calls[0][1].charts).toEqual([{ field: 'color', chart: 'horizontal_bar', insights }]);
});

test('saving after a chart change keeps the stored insights', async () => {
    const insights = [{ type: 'checked' }];
    const wrapper = await mountEditing([
        field('color', 'horizontal_bar', { field: 'color', chart: 'horizontal_bar', insights }),
    ]);

    axios.get.mockResolvedValue({ data: summary([field('color', 'pie', { field: 'color', chart: 'pie', insights })]) });
    wrapper.vm.setChart(0, 'pie');
    await settle();

    expect(axios.get.mock.calls[1][1].params.charts).toEqual([{ field: 'color', chart: 'pie', insights }]);

    axios.patch.mockResolvedValue({});
    await wrapper.vm.save();

    expect(axios.patch.mock.calls[0][1].charts).toEqual([{ field: 'color', chart: 'pie', insights }]);
});

test('a failed preview is not retried in a loop', async () => {
    const wrapper = await mountEditing();

    axios.get.mockRejectedValue({ response: { data: { message: 'Server Error' } } });
    wrapper.vm.setChart(0, 'pie');
    await settle();

    expect(axios.get).toHaveBeenCalledTimes(2);
    expect(toast.error).toHaveBeenCalledTimes(1);
});

test('a preview that comes back with a different chart is not retried in a loop', async () => {
    const wrapper = await mountEditing();

    axios.get.mockResolvedValue({ data: summary([field('color', 'horizontal_bar')]) });
    wrapper.vm.setChart(0, 'pie');
    await settle();

    expect(axios.get).toHaveBeenCalledTimes(2);
});

test('picking another chart after a failed preview fetches it', async () => {
    const wrapper = await mountEditing();

    axios.get.mockRejectedValueOnce({ response: { data: { message: 'Server Error' } } });
    wrapper.vm.setChart(0, 'pie');
    await settle();

    axios.get.mockResolvedValueOnce({ data: summary([field('color', 'lollipop')]) });
    wrapper.vm.setChart(0, 'lollipop');
    await settle();

    expect(axios.get).toHaveBeenCalledTimes(3);
    expect(axios.get.mock.calls[2][1].params.charts).toEqual([{ field: 'color', chart: 'lollipop' }]);
    expect(wrapper.vm.summary.fields[0].chart.handle).toBe('lollipop');
});

function pendingUntilAborted(signals) {
    return (url, { signal }) => {
        signals.push(signal);

        return new Promise((resolve, reject) => {
            signal.addEventListener('abort', () => reject({ name: 'CanceledError' }));
        });
    };
}

test('refetching the summary aborts in-flight previews', async () => {
    const wrapper = await mountEditing();
    const signals = [];

    axios.get.mockImplementationOnce(pendingUntilAborted(signals));
    wrapper.vm.setChart(0, 'pie');
    await flushPromises();

    axios.get.mockResolvedValue({ data: summary([field('color', 'pie')]) });
    wrapper.vm.refresh();
    await settle();

    expect(signals[0].aborted).toBe(true);
    expect(toast.error).not.toHaveBeenCalled();
    expect(wrapper.vm.summary.fields[0].chart.handle).toBe('pie');
});

test('cancelling editing aborts in-flight previews', async () => {
    const wrapper = await mountEditing();
    const signals = [];

    axios.get.mockImplementationOnce(pendingUntilAborted(signals));
    wrapper.vm.setChart(0, 'pie');
    await flushPromises();

    axios.get.mockResolvedValue({ data: summary([field('color', 'horizontal_bar')]) });
    wrapper.vm.cancelEditing();
    await settle();

    expect(signals[0].aborted).toBe(true);
    expect(toast.error).not.toHaveBeenCalled();
    expect(axios.get).toHaveBeenCalledTimes(3);
    expect(wrapper.vm.summary.fields[0].chart.handle).toBe('horizontal_bar');
});
