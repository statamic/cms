<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Pagination, Widget } from '@ui';
import type { ChartItem, ChartMetric, SummaryField } from './types';

const props = withDefaults(
    defineProps<{
        field: SummaryField;
        metric?: ChartMetric;
        showNumber?: boolean;
        editing?: boolean;
    }>(),
    {
        metric: 'percent',
        showNumber: false,
        editing: false,
    },
);

const page = ref(1);

const chart = computed(() => props.field.chart);
const drilldown = computed(() => chart.value.props.drilldown);
const hasDrilldown = computed<boolean>(() => Boolean(drilldown.value));
const showingDrilldown = computed<boolean>(() => page.value === 2);
const pagination = computed(() => ({ current_page: page.value, last_page: 2 }));

const title = computed((): string => {
    return props.showNumber && props.field.number
        ? `${props.field.number}. ${__(props.field.display)}`
        : __(props.field.display);
});

const items = computed<ChartItem[]>(() => {
    if (showingDrilldown.value) {
        return drilldown.value!.items;
    }

    if (props.editing || !hasDrilldown.value) {
        return chart.value.props.items;
    }

    return chart.value.props.items.map((item) => (item.other ? { ...item, clickable: true } : item));
});

const accessibleLabel = computed(() => {
    const values = items.value
        .map((item) => `${item.label} ${props.metric === 'count' ? item.count : `${item.percent}%`}`)
        .join(', ');

    return showingDrilldown.value
        ? __('statamic::messages.form_summary_other_breakdown', { field: __(props.field.display), values })
        : `${__(props.field.display)}: ${values}`;
});

const chartProps = computed(() => {
    const { drilldown: _, ...mainProps } = chart.value.props;

    return {
        ...(showingDrilldown.value ? drilldown.value : mainProps),
        items: items.value,
        metric: props.metric,
        responses: props.field.responses,
        accessibleLabel: accessibleLabel.value,
    };
});

watch([chart, () => props.editing], () => (page.value = 1));
</script>

<template>
    <Widget
        :title="title"
        title-tag="h2"
        class="h-full"
        :class="{ 'summary-chart-editing summary-chart-handle cursor-grab active:cursor-grabbing ring-0! shadow-none! border border-dashed border-gray-400 dark:border-gray-700': editing }"
        :icon="field.icon"
        icon-class="hidden @xs/widget:block size-4 text-gray-500"
    >
        <template v-if="editing || hasDrilldown" #actions>
            <slot v-if="editing" name="chrome" />
            <Pagination
                v-else
                :resource-meta="pagination"
                :show-totals="false"
                :show-page-links="false"
                :show-per-page-selector="false"
                :scroll-to-top="false"
                @page-selected="page = $event"
            />
        </template>
        <div class="relative flex flex-1 flex-col overflow-hidden rounded-b-xl">
            <p v-if="hasDrilldown" class="sr-only" aria-live="polite">{{ showingDrilldown ? accessibleLabel : '' }}</p>
            <div v-if="field.insights.length" class="flex flex-wrap gap-2.5 ps-5 pe-6 pt-6 -mb-1" data-chart-insights>
                <component
                    v-for="insight in field.insights"
                    :key="insight.handle"
                    :is="insight.component"
                    v-bind="insight.props"
                    :metric
                />
            </div>
            <div class="min-h-0 flex-1">
                <component :is="chart.component" v-bind="chartProps" @select="page = 2" />
            </div>
        </div>
    </Widget>
</template>
