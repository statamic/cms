import { ref, watch, nextTick } from 'vue';

const CHART_REVEAL_MS = 1100;

function createChartReveal() {
    const isRevealing = ref(false);
    let timeoutId;

    function trigger() {
        clearTimeout(timeoutId);
        isRevealing.value = false;

        nextTick(() => {
            isRevealing.value = true;
            timeoutId = setTimeout(() => {
                isRevealing.value = false;
            }, CHART_REVEAL_MS);
        });
    }

    return { isRevealing, trigger };
}

export function summaryChartTypesPreferencesKey(formHandle) {
    return `forms.${formHandle}.submissions.summary.chart-types`;
}

/**
 * Persisted chart type + reveal animation for a summary widget.
 * Destructure the returned refs in setup so Vue unwraps them in the template.
 */
export default function useSummaryChartType(formHandle, widgetKey, { types = ['bar', 'pie'], default: defaultType = 'bar' } = {}) {
    const preferencesKey = summaryChartTypesPreferencesKey(formHandle);
    const savedTypes = Statamic.$preferences.get(preferencesKey, {});
    const saved = savedTypes[widgetKey];
    const chartType = ref(types.includes(saved) ? saved : defaultType);
    const { isRevealing, trigger } = createChartReveal();

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
        trigger();
    }

    return { chartType, isRevealing, setChartType, types };
}
