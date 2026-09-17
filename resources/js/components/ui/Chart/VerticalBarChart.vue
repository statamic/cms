<script setup lang="ts">
import { computed } from 'vue';
import Metric from './Metric.vue';

type Item = {
    key?: string;
    label: string;
    count: number;
    percent: number;
}

const props = withDefaults(
    defineProps<{
        /** Accessible label summarizing the chart's results. */
        accessibleLabel: string;
        /** Accepted for parity with the other charts. Columns all share one colour, so it has no visual effect. */
        focusedIndex?: number | null;
        /** The chart columns. Each item supports `label`, `percent`, and `count`. */
        items?: Item[];
        /** The largest value represented by a full-height column. */
        maxValue?: number | null;
        /** Whether values are displayed as percentages or response counts. */
        metric?: 'percent' | 'count';
    }>(),
    {
        focusedIndex: null,
        items: () => [],
        maxValue: null,
        metric: 'percent',
    },
);

const height = (item: Item): string => `${(item.percent / maximum.value) * 100}%`;

const maximum = computed<number>(() => props.maxValue ?? Math.max(...props.items.map((item) => item.percent), 1));
</script>

<template>
    <figure class="vertical-bar-chart-figure" role="img" :aria-label="accessibleLabel" data-ui-vertical-bar-chart>
        <div v-if="$slots.summary" aria-hidden="true">
            <slot name="summary" />
        </div>
        <ol class="vertical-bar-chart" aria-hidden="true">
            <li v-for="item in items" :key="item.key ?? item.label" class="vertical-bar-chart__bar">
                <div class="vertical-bar-chart__plot">
                    <Metric
                        :metric
                        :percent="item.percent"
                        :count="item.count"
                        class="vertical-bar-chart__value"
                    />
                    <div :style="{ flexBasis: height(item) }" class="vertical-bar-chart__fill" />
                </div>
                <span class="vertical-bar-chart__scale-label">{{ item.label }}</span>
            </li>
        </ol>
    </figure>
</template>
