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

function field(handle, chart) {
    return {
        handle,
        display: handle,
        icon: 'fieldtype-radio',
        fieldtype: 'multi_choice',
        number: 1,
        responses: 1,
        chart: { handle: chart, component: `ui-${chart}-chart`, props: { items: [] } },
        insights: [],
    };
}

function summary(fields) {
    return {
        total: 1,
        fields,
        meta: {
            charts: [],
            fields: [{ handle: 'color', display: 'Color', icon: 'fieldtype-radio', default_chart: 'horizontal_bar' }],
        },
    };
}

async function mountEditing() {
    axios.get.mockResolvedValueOnce({ data: summary([field('color', 'horizontal_bar')]) });

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
