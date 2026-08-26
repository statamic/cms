<script setup lang="ts">
import { computed } from 'vue';
import Metric from './Metric.vue';

type Item = {
    key?: string;
    label: string;
    count: number;
    percent: number;
    rank?: number;
}

const props = withDefaults(
    defineProps<{
        /** Accessible label summarizing the chart's results. */
        accessibleLabel: string;
        /** The chart rows. Each item supports `label`, `percent`, `count`, and `rank`. */
        items?: Item[];
        /** Whether values are displayed as percentages or response counts. */
        metric?: 'percent' | 'count';
        /** Whether to show a marker before each label. */
        showMarker?: boolean;
    }>(),
    {
        items: () => [],
        metric: 'percent',
        showMarker: true,
    },
);

const columns = computed<string>(() => props.showMarker
    ? 'grid-cols-[auto_auto_max-content_1fr]'
    : 'grid-cols-[auto_max-content_1fr]');

const chartClasses = ['bg-chart-1', 'bg-chart-2', 'bg-chart-3', 'bg-chart-4-legend'];
const chartClass = (index: number): string => chartClasses[Math.min(index, chartClasses.length - 1)];
</script>

<template>
    <figure class="grid p-6" role="img" :aria-label="accessibleLabel" data-ui-horizontal-lollipop-chart>
        <ol :class="columns" class="grid items-center list-none m-0 gap-x-2.25 gap-y-2.5 p-0" aria-hidden="true">
            <li v-for="(item, index) in items" :key="item.key ?? item.label" class="contents">
                <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50">
                    {{ item.rank ?? index + 1 }}
                </span>
                <template v-if="showMarker">
                    <slot name="marker" :item="item" :index="index">
                        <span :class="chartClass(index)" class="size-2.5 rounded-xs" />
                    </slot>
                </template>
                <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">{{ item.label }}</span>
                <div class="flex items-center gap-1">
                    <div :style="{ width: `${item.percent}%` }" class="h-px bg-gray-200 dark:bg-gray-600" />
                    <slot name="endpoint" :item="item" :index="index">
                        <span :class="chartClass(index)" class="size-2 rounded-full" />
                    </slot>
                    <Metric
                        :metric
                        :percent="item.percent"
                        :count="item.count"
                        class="min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50"
                    />
                </div>
            </li>
        </ol>
    </figure>
</template>
