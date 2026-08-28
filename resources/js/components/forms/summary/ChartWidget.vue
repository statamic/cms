<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Button, Widget } from '@ui';
import type { ChartItem, ChartMetric, SummaryField } from './types';

const props = withDefaults(
    defineProps<{
        field: SummaryField;
        metric?: ChartMetric;
        showNumber?: boolean;
    }>(),
    {
        metric: 'percent',
        showNumber: false,
    },
);

const showingDrilldown = ref(false);

const chart = computed(() => props.field.chart);
const drilldown = computed(() => chart.value.props.drilldown);
const hasDrilldown = computed<boolean>(() => Boolean(drilldown.value));

const title = computed((): string => {
    return props.showNumber && props.field.number
        ? `${props.field.number}. ${props.field.display}`
        : props.field.display;
});

const items = computed<ChartItem[]>(() => {
    if (showingDrilldown.value) {
        return drilldown.value!.items;
    }

    return chart.value.props.items.map((item) => (item.other && hasDrilldown.value ? { ...item, clickable: true } : item));
});

const accessibleLabel = computed(() => {
    const values = items.value
        .map((item) => `${item.label} ${props.metric === 'count' ? item.count : `${item.percent}%`}`)
        .join(', ');

    return showingDrilldown.value
        ? __(':field: Other breakdown: :values', { field: props.field.display, values })
        : `${props.field.display}: ${values}`;
});

const chartProps = computed(() => ({
    ...(showingDrilldown.value ? drilldown.value : {}),
    items: items.value,
    metric: props.metric,
    accessibleLabel: accessibleLabel.value,
}));

watch(chart, () => (showingDrilldown.value = false));
</script>

<template>
    <Widget
        :title="title"
        title-tag="h2"
        class="h-full"
        :icon="field.icon"
        icon-class="hidden @xs/widget:block size-4 text-gray-500"
    >
        <template v-if="showingDrilldown" #actions>
            <Button size="sm" icon="arrow-left" :text="__('Back')" @click="showingDrilldown = false" />
        </template>
        <div class="relative flex-1 overflow-hidden rounded-b-xl">
            <slot name="chrome" />
            <p v-if="hasDrilldown" class="sr-only" aria-live="polite">{{ accessibleLabel }}</p>
            <component :is="chart.component" v-bind="chartProps" @select="showingDrilldown = true">
                <template v-if="field.insights.length" #summary>
                    <div class="flex flex-wrap gap-2.5 pb-5 -ms-1">
                        <component
                            v-for="insight in field.insights"
                            :key="insight.handle"
                            :is="insight.component"
                            v-bind="insight.props"
                            :metric
                        />
                    </div>
                </template>
            </component>
        </div>
    </Widget>
</template>
