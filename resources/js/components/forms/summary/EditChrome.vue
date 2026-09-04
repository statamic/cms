<script setup lang="ts">
import { computed } from 'vue';
import { Button, Dropdown, DropdownItem, DropdownMenu } from '@ui';
import type { ChartConfig, MetaChart } from './types';

const props = withDefaults(
    defineProps<{
        config: ChartConfig;
        charts: MetaChart[];
        loading?: boolean;
    }>(),
    {
        loading: false,
    },
);

const emit = defineEmits<{
    'update:chart': [chart: string];
    remove: [];
}>();

const currentChart = computed(() => props.charts.find((chart) => chart.handle === props.config.chart));
</script>

<template>
    <div
        data-summary-edit-chrome
        class="summary-chart-handle absolute inset-0 z-2 flex flex-col cursor-grab items-center justify-center gap-5 bg-white/80 px-4 backdrop-blur-[4px] border border-dashed border-purple-300 dark:border-purple-300/40 shadow-sm active:cursor-grabbing dark:bg-gray-900/85"
    >
        <div class="flex shrink-0 items-center gap-2" @mousedown.stop @click.stop>
            <Dropdown align="start">
                <template #trigger>
                    <Button size="sm" :icon="currentChart?.icon" :text="currentChart?.title ?? config.chart" :loading />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-for="chart in charts"
                        :key="chart.handle"
                        :icon="chart.icon"
                        :text="chart.title"
                        @click="emit('update:chart', chart.handle)"
                    />
                </DropdownMenu>
            </Dropdown>

            <Button size="sm" icon="trash" icon-only :aria-label="__('Remove')" @click="emit('remove')" />
        </div>
    </div>
</template>
