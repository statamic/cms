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
}

const props = withDefaults(
    defineProps<{
        /** Accessible label summarizing the chart's results. */
        accessibleLabel: string;
        /** Colours every row as this item, for rows that break one item down. */
        focusedIndex?: number | null;
        /** The chart rows. Each item supports `label`, `percent`, `count`, `clickable`, and `icon`. */
        items?: Item[];
        /** Whether values are displayed as percentages or response counts. */
        metric?: 'percent' | 'count';
        /** Places the metric before or after each bar. */
        metricPosition?: 'start' | 'end';
        /** Places the marker before or after the label. */
        markerPosition?: 'before-label' | 'after-label';
        /** Whether to show item labels. */
        showLabel?: boolean;
        /** Whether to show a marker before each label. */
        showMarker?: boolean;
    }>(),
    {
        focusedIndex: null,
        items: () => [],
        metric: 'percent',
        metricPosition: 'start',
        markerPosition: 'before-label',
        showLabel: true,
        showMarker: true,
    },
);

const emit = defineEmits<{
    select: [item: Item, index: number];
}>();

const columns = computed<string>(() => {
    const columns = [];

    if (props.metricPosition === 'start') columns.push('auto');
    if (props.showMarker && props.markerPosition === 'before-label') columns.push('auto');
    if (props.showLabel) columns.push('max-content');
    if (props.showMarker && props.markerPosition === 'after-label') columns.push('auto');

    columns.push('1fr');

    if (props.metricPosition === 'end') columns.push('auto');

    return columns.join(' ');
});

const chartClasses = ['bg-chart-1', 'bg-chart-2', 'bg-chart-3', 'bg-chart-4-legend', 'bg-chart-5'];
const colorIndex = (index: number): number => props.focusedIndex ?? index;
const chartClass = (index: number): string => chartClasses[Math.min(colorIndex(index), chartClasses.length - 1)];
const chartTone = (index: number): number => Math.min(colorIndex(index) + 1, 5);
</script>

<template>
    <figure class="grid p-6" role="img" :aria-label="accessibleLabel" data-ui-horizontal-bar-chart>
        <slot name="summary" />
        <ol :style="{ gridTemplateColumns: columns }" class="grid items-center list-none m-0 gap-x-2.25 [&:not(:has(>_:nth-child(5)))]:pt-4 gap-y-2.5 p-0" aria-hidden="true">
            <li v-for="(item, index) in items" :key="item.key ?? item.label" class="contents">
                <component
                    :is="item.clickable ? 'button' : 'span'"
                    :type="item.clickable ? 'button' : undefined"
                    :tabindex="item.clickable ? -1 : undefined"
                    :class="{ 'summary-bar-chart__link': item.clickable }"
                    class="contents"
                    @click="item.clickable && emit('select', item, index)"
                >
                    <Metric
                        v-if="metricPosition === 'start'"
                        :metric
                        :percent="item.percent"
                        :count="item.count"
                        class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50"
                    />
                    <span v-if="showMarker && markerPosition === 'before-label'" class="flex items-center gap-2">
                        <slot name="marker" :item="item" :index="index">
                            <Icon v-if="item.icon" :name="item.icon" :class="`summary-bar-chart__icon-stroke--${chartTone(index)}`" class="summary-bar-chart__icon-stroke size-3.5 shrink-0" />
                            <span v-else :class="chartClass(index)" class="size-2.5 rounded-xs" />
                        </slot>
                    </span>
                    <span v-if="showLabel" class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">{{ item.label }}</span>
                    <span v-if="showMarker && markerPosition === 'after-label'" class="flex items-center gap-2">
                        <slot name="marker" :item="item" :index="index">
                            <Icon v-if="item.icon" :name="item.icon" :class="`summary-bar-chart__icon-stroke--${chartTone(index)}`" class="summary-bar-chart__icon-stroke size-3.5 shrink-0" />
                            <span v-else :class="chartClass(index)" class="size-2.5 rounded-xs" />
                        </slot>
                    </span>
                    <slot name="bar" :item="item" :index="index" :width="`${item.percent}%`">
                        <div
                            :class="chartClass(index)"
                            :style="{ width: `${item.percent}%` }"
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
                </component>
            </li>
        </ol>
    </figure>
</template>
