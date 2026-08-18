<script setup>
import { computed } from 'vue';
import Metric from './Metric.vue';

const props = defineProps({
    /** A concise text alternative describing the chart. */
    accessibleLabel: { type: String, required: true },
    /** Highlights one segment while muting the others. */
    focusedIndex: { type: Number, default: null },
    /** The legend items. Each item supports `label`, `percent`, `count`, and `clickable`. */
    items: {
        type: Array,
        default: () => [],
        validator: (items) => items.length <= 4,
    },
    /** Whether values are displayed as percentages or response counts. */
    metric: {
        type: String,
        default: 'percent',
        validator: (value) => ['percent', 'count'].includes(value),
    },
    /** Optional segment data when the legend describes a focused segment. */
    segments: {
        type: Array,
        default: null,
        validator: (items) => items === null || items.length <= 4,
    },
});

const emit = defineEmits(['select']);

const slices = computed(() => props.segments ?? props.items);
const chartStyle = computed(() => Object.fromEntries([0, 1, 2, 3].flatMap((index) => {
    const number = index + 1;
    const color = props.focusedIndex === null || props.focusedIndex === index
        ? `var(--color-chart-${number})`
        : `hsl(from var(--color-chart-${number}) h s l / 0.1)`;

    return [[`--${number}`, slices.value[index]?.percent ?? 0], [`--slice-${number}-color`, color]];
})));
</script>

<template>
    <figure class="pie-chart-figure" data-ui-pie-chart>
        <div
            :class="{ 'pie-chart--focused': focusedIndex !== null }"
            :style="chartStyle"
            class="pie-chart"
            role="img"
            :aria-label="accessibleLabel"
        >
            <div class="pie-chart__disc" aria-hidden="true" />
            <Metric
                v-for="(item, index) in slices"
                :key="item.id ?? item.label ?? index"
                :metric
                :percent="item.percent"
                :count="item.count"
                :class="`pie-chart__label--${index + 1}`"
                class="pie-chart__label"
                :data-focused="focusedIndex === index ? '' : undefined"
                aria-hidden="true"
            />
        </div>
        <figcaption class="pie-chart-legend">
            <ol class="pie-chart-legend__list">
                <li v-for="(item, index) in items" :key="item.id ?? item.label ?? index" class="pie-chart-legend__item">
                    <component
                        :is="item.clickable ? 'button' : 'span'"
                        :type="item.clickable ? 'button' : undefined"
                        :class="{ 'pie-chart-legend__link': item.clickable }"
                        :aria-hidden="item.clickable ? undefined : true"
                        class="contents"
                        @click="item.clickable && emit('select', item, index)"
                    >
                        <Metric :metric :percent="item.percent" :count="item.count" class="pie-chart-legend__value" />
                        <slot name="marker" :item="item" :index="index">
                            <span :class="`pie-chart-legend__swatch--${focusedIndex === null ? index + 1 : focusedIndex + 1}`" class="pie-chart-legend__swatch" />
                        </slot>
                        <span>{{ item.label }}</span>
                    </component>
                </li>
            </ol>
        </figcaption>
    </figure>
</template>
