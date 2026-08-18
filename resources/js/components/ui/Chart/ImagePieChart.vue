<script setup>
import { computed } from 'vue';
import Metric from './Metric.vue';

const props = defineProps({
    /** A concise text alternative describing the chart. */
    accessibleLabel: { type: String, required: true },
    /** The chart items. Each item supports `label`, `percent`, `count`, `image`, and `badge`. */
    items: {
        type: Array,
        default: () => [],
        validator: (items) => items.length <= 2,
    },
    /** Whether values are displayed as percentages or response counts. */
    metric: {
        type: String,
        default: 'percent',
        validator: (value) => ['percent', 'count'].includes(value),
    },
});

const chartStyle = computed(() => Object.fromEntries([0, 1].map((index) => [`--${index + 1}`, props.items[index]?.percent ?? 0])));
</script>

<template>
    <figure class="image-pie-chart-figure" data-ui-image-pie-chart>
        <div :style="chartStyle" class="image-pie-chart" role="img" :aria-label="accessibleLabel">
            <div class="image-pie-chart__disc" aria-hidden="true">
                <span
                    v-for="(item, index) in items"
                    :key="item.id ?? item.label ?? index"
                    :class="`image-pie-chart__slice--${index + 1}`"
                    :style="{ '--image': `url(${item.image})` }"
                    class="image-pie-chart__slice"
                />
            </div>
            <Metric
                v-for="(item, index) in items"
                :key="item.id ?? item.label ?? index"
                :metric
                :percent="item.percent"
                :count="item.count"
                :class="`image-pie-chart__label--${index + 1}`"
                class="image-pie-chart__label"
                aria-hidden="true"
            />
        </div>
        <figcaption class="image-pie-chart-legend">
            <ol class="grid grid-cols-[auto_2.5rem_auto_1fr] items-center justify-items-start list-none m-0 gap-x-2.25 gap-y-2.5 p-0 pt-3" aria-hidden="true">
                <li v-for="(item, index) in items" :key="item.id ?? item.label ?? index" class="contents">
                    <Metric :metric :percent="item.percent" :count="item.count" class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" />
                    <img class="size-10 shrink-0 object-cover rounded-full" :src="item.image" alt="" />
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">{{ item.badge }}</span>
                    <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">{{ item.label }}</span>
                </li>
            </ol>
        </figcaption>
    </figure>
</template>
