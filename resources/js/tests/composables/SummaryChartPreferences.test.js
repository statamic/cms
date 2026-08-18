import { nextTick } from 'vue';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import useSummaryChartMetric from '@/composables/use-summary-chart-metric.js';
import useSummaryChartType from '@/composables/use-summary-chart-type.js';

let preferences;
let values;

beforeEach(() => {
    values = new Map();
    preferences = {
        get: vi.fn((key, fallback) => values.has(key) ? values.get(key) : fallback),
        set: vi.fn((key, value) => values.set(key, value)),
    };

    vi.stubGlobal('Statamic', { $preferences: preferences });
});

afterEach(() => {
    vi.unstubAllGlobals();
});

test('persists the selected summary chart metric', async () => {
    const { metric } = useSummaryChartMetric('contact');

    expect(metric.value).toBe('percent');

    metric.value = 'count';
    await nextTick();

    expect(preferences.set).toHaveBeenCalledWith(
        'forms.contact.submissions.summary.chart-metric',
        'count',
    );
});

test('persists each widget chart type without replacing the others', async () => {
    values.set('forms.contact.submissions.summary.chart-types', { second: 'pie' });

    const { chartType, setChartType } = useSummaryChartType('contact', 'first');

    expect(chartType.value).toBe('bar');

    setChartType('pie');
    await nextTick();

    expect(preferences.set).toHaveBeenCalledWith(
        'forms.contact.submissions.summary.chart-types',
        { first: 'pie', second: 'pie' },
    );
});

test('ignores unsupported chart preference values', () => {
    values.set('forms.contact.submissions.summary.chart-metric', 'total');
    values.set('forms.contact.submissions.summary.chart-types', { first: 'line' });

    const { metric } = useSummaryChartMetric('contact');
    const { chartType, setChartType } = useSummaryChartType('contact', 'first');

    setChartType('line');

    expect(metric.value).toBe('percent');
    expect(chartType.value).toBe('bar');
    expect(preferences.set).not.toHaveBeenCalled();
});
