import { ref, watch } from 'vue';

export function summaryChartMetricPreferencesKey(formHandle) {
    return `forms.${formHandle}.submissions.summary.chart-metric`;
}

/** Persisted percent/count display mode for summary charts. */
export default function useSummaryChartMetric(formHandle, { default: defaultMetric = 'percent' } = {}) {
    const preferencesKey = summaryChartMetricPreferencesKey(formHandle);
    const saved = Statamic.$preferences.get(preferencesKey, defaultMetric);
    const metric = ref(['percent', 'count'].includes(saved) ? saved : defaultMetric);

    watch(metric, (value) => {
        Statamic.$preferences.set(preferencesKey, value);
    });

    return { metric };
}
