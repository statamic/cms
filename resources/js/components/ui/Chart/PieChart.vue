<script setup lang="ts">
import { computed } from 'vue';
import Icon from '../Icon/Icon.vue';
import Metric from './Metric.vue';

type Item = {
    key?: string;
    label: string;
    count: number;
    percent: number;
    clickable?: boolean;
    icon?: string;
    image?: string;
    badge?: string;
}

const props = withDefaults(
    defineProps<{
        /** Accessible label summarizing the chart's results. */
        accessibleLabel: string;
        /** Highlights one segment while muting the others. */
        focusedIndex?: number | null;
        /** Up to four legend items. Each item supports `label`, `percent`, `count`, `clickable`, `icon`, `image`, and `badge`. */
        items?: Item[];
        /** Whether values are displayed as percentages or response counts. */
        metric?: 'percent' | 'count';
        /** Optional segment data when the legend describes a focused segment. */
        segments?: Item[] | null;
    }>(),
    {
        focusedIndex: null,
        items: () => [],
        metric: 'percent',
        segments: null,
    },
);

const emit = defineEmits<{
    select: [item: Item, index: number];
}>();

const slices = computed<Item[]>(() => props.segments ?? props.items);
const showsImageSlices = computed<boolean>(() => slices.value.length > 0 && slices.value.length <= 2 && slices.value.every((item) => item.image));

const slicePercent = (index: number): number => slices.value[index]?.percent ?? 0;

const sliceColor = (index: number): string =>
    props.focusedIndex === null || props.focusedIndex === index
        ? `var(--color-chart-${index + 1})`
        : `hsl(from var(--color-chart-${index + 1}) h s l / 0.1)`;

const chartStyle = computed(() => ({
    '--1': slicePercent(0),
    '--2': slicePercent(1),
    '--3': slicePercent(2),
    '--4': slicePercent(3),
    '--slice-1-color': sliceColor(0),
    '--slice-2-color': sliceColor(1),
    '--slice-3-color': sliceColor(2),
    '--slice-4-color': sliceColor(3),
}));
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
                    :key="item.key ?? item.label"
                    :class="`image-pie-chart__slice--${index + 1}`"
                    :style="{ '--image': `url(${item.image})` }"
                    class="image-pie-chart__slice"
                />
            </div>
            <Metric
                v-for="(item, index) in slices"
                :key="item.key ?? item.label"
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
                :key="item.key ?? item.label"
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
                <li v-for="(item, index) in items" :key="item.key ?? item.label" class="pie-chart-legend__item">
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
