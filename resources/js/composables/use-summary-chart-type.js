import { ref, watch } from 'vue';

export function summaryChartTypesPreferencesKey(formHandle) {
    return `forms.${formHandle}.submissions.summary.chart-types`;
}

/** Persisted chart type for a summary widget. */
export default function useSummaryChartType(formHandle, widgetKey, { types = ['bar', 'pie'], default: defaultType = 'bar' } = {}) {
    const preferencesKey = summaryChartTypesPreferencesKey(formHandle);
    const savedTypes = Statamic.$preferences.get(preferencesKey, {});
    const saved = savedTypes[widgetKey];
    const chartType = ref(types.includes(saved) ? saved : defaultType);

    watch(chartType, (type) => {
        const current = Statamic.$preferences.get(preferencesKey, {});

        Statamic.$preferences.set(preferencesKey, {
            ...current,
            [widgetKey]: type,
        });
    });

    function setChartType(type) {
        if (! types.includes(type) || type === chartType.value) {
            return;
        }

        chartType.value = type;
    }

    return { chartType, setChartType };
}
