<script setup>
import { computed } from 'vue';
import Metric from './Metric.vue';

const chartClasses = ['bg-chart-1', 'bg-chart-2', 'bg-chart-3', 'bg-chart-4-legend'];

const props = defineProps({
    /** A concise text alternative describing the chart. */
    accessibleLabel: { type: String, required: true },
    /** The chart rows. Each item supports `label`, `percent`, `count`, and `value`. */
    items: { type: Array, default: () => [] },
    /** Whether values are displayed as percentages or response counts. */
    metric: {
        type: String,
        default: 'percent',
        validator: (value) => ['percent', 'count'].includes(value),
    },
    /** Places the metric before or after each bar. */
    metricPosition: {
        type: String,
        default: 'start',
        validator: (value) => ['start', 'end'].includes(value),
    },
    /** Places the marker before or after the label. */
    markerPosition: {
        type: String,
        default: 'before-label',
        validator: (value) => ['before-label', 'after-label'].includes(value),
    },
    /** Whether to show item labels. */
    showLabel: { type: Boolean, default: true },
    /** Whether to show a marker before each label. */
    showMarker: { type: Boolean, default: true },
});

const columns = computed(() => {
    const columns = [];

    if (props.metricPosition === 'start') columns.push('auto');
    if (props.showMarker && props.markerPosition === 'before-label') columns.push('auto');
    if (props.showLabel) columns.push('max-content');
    if (props.showMarker && props.markerPosition === 'after-label') columns.push('auto');

    columns.push('1fr');

    if (props.metricPosition === 'end') columns.push('auto');

    return columns.join(' ');
});

function width(item) {
    return `${item.value ?? item.percent}%`;
}

function chartClass(index) {
    return chartClasses[Math.min(index, chartClasses.length - 1)];
}
</script>

<template>
    <figure class="grid p-6" role="img" :aria-label="accessibleLabel" data-ui-horizontal-bar-chart>
        <slot name="summary" />
        <ol :style="{ gridTemplateColumns: columns }" class="grid items-center list-none m-0 gap-x-2.25 gap-y-2.5 p-0 pt-4" aria-hidden="true">
            <li v-for="(item, index) in items" :key="item.id ?? item.label ?? index" class="contents">
                <Metric
                    v-if="metricPosition === 'start'"
                    :metric
                    :percent="item.percent"
                    :count="item.count"
                    class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50"
                />
                <span v-if="showMarker && markerPosition === 'before-label'" class="flex items-center gap-2">
                    <slot name="marker" :item="item" :index="index">
                        <span :class="chartClass(index)" class="size-2.5 rounded-xs" />
                    </slot>
                </span>
                <span v-if="showLabel" class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">{{ item.label }}</span>
                <span v-if="showMarker && markerPosition === 'after-label'" class="flex items-center gap-2">
                    <slot name="marker" :item="item" :index="index">
                        <span :class="chartClass(index)" class="size-2.5 rounded-xs" />
                    </slot>
                </span>
                <slot name="bar" :item="item" :index="index" :width="width(item)">
                    <div
                        :class="chartClass(index)"
                        :style="{ width: width(item) }"
                        class="summary-bar-chart__fill h-2.5 rounded-full"
                    />
                </slot>
                <Metric
                    v-if="metricPosition === 'end'"
                    :metric
                    :percent="item.percent"
                    :count="item.count"
                    class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400"
                />
            </li>
        </ol>
    </figure>
</template>
