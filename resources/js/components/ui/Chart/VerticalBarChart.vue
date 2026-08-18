<script setup>
import { computed } from 'vue';
import Metric from './Metric.vue';

const props = defineProps({
    /** A concise text alternative describing the chart. */
    accessibleLabel: { type: String, required: true },
    /** The chart columns. Each item supports `label`, `percent`, `count`, and `value`. */
    items: { type: Array, default: () => [] },
    /** The largest value represented by a full-height column. */
    maxValue: { type: Number, default: null },
    /** Whether values are displayed as percentages or response counts. */
    metric: {
        type: String,
        default: 'percent',
        validator: (value) => ['percent', 'count'].includes(value),
    },
});

const maximum = computed(() => props.maxValue ?? Math.max(...props.items.map((item) => item.value ?? item.percent), 1));

function height(item) {
    return `${((item.value ?? item.percent) / maximum.value) * 100}%`;
}
</script>

<template>
    <figure class="vertical-bar-chart-figure" role="img" :aria-label="accessibleLabel" data-ui-vertical-bar-chart>
        <div v-if="$slots.summary" aria-hidden="true">
            <slot name="summary" />
        </div>
        <ol class="vertical-bar-chart" aria-hidden="true">
            <li v-for="(item, index) in items" :key="item.id ?? item.label ?? index" class="vertical-bar-chart__bar">
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
