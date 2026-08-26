<script setup>
import { computed } from 'vue';
import Icon from '../Icon/Icon.vue';
import Metric from './Metric.vue';

const props = defineProps({
    /** A concise text alternative describing the chart. */
    accessibleLabel: { type: String, required: true },
    /** Highlights one segment while muting the others. */
    focusedIndex: { type: Number, default: null },
    /** The legend items. Each item supports `label`, `percent`, `count`, `clickable`, `icon`, `image`, and `badge`. */
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
const showsImageSlices = computed(() => slices.value.length > 0 && slices.value.length <= 2 && slices.value.every((item) => item.image));

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
            v-if="showsImageSlices"
            :style="chartStyle"
            class="image-pie-chart"
            role="img"
            :aria-label="accessibleLabel"
        >
            <div class="image-pie-chart__disc" aria-hidden="true">
                <span
                    v-for="(item, index) in slices"
                    :key="item.id ?? item.label ?? index"
                    :class="`image-pie-chart__slice--${index + 1}`"
                    :style="{ '--image': `url(${item.image})` }"
                    class="image-pie-chart__slice"
                />
            </div>
            <Metric
                v-for="(item, index) in slices"
                :key="item.id ?? item.label ?? index"
                :metric
                :percent="item.percent"
                :count="item.count"
                :class="`image-pie-chart__label--${index + 1}`"
                class="image-pie-chart__label"
                aria-hidden="true"
            />
        </div>
        <div
            v-else
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
                            <template v-if="item.image">
                                <img class="size-10 shrink-0 object-cover rounded-full" :src="item.image" alt="" />
                                <span v-if="item.badge" class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">{{ item.badge }}</span>
                            </template>
                            <Icon v-else-if="item.icon" :name="item.icon" :class="`summary-bar-chart__icon-stroke--${index + 1}`" class="summary-bar-chart__icon-stroke size-3.5 shrink-0" />
                            <span v-else :class="`pie-chart-legend__swatch--${focusedIndex === null ? index + 1 : focusedIndex + 1}`" class="pie-chart-legend__swatch" />
                        </slot>
                        <span>{{ item.label }}</span>
                    </component>
                </li>
            </ol>
        </figcaption>
    </figure>
</template>
