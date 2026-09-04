<script setup lang="ts">
import { computed } from 'vue';
import { Button, Dropdown, DropdownItem, DropdownMenu } from '@ui';
import type { ChartConfig, MetaChart } from './types';

const props = defineProps<{
    config: ChartConfig;
    charts: MetaChart[];
}>();

const emit = defineEmits<{
    'update:chart': [chart: string];
    remove: [];
}>();

const currentChart = computed(() => props.charts.find((chart) => chart.handle === props.config.chart));
</script>

<template>
    <div data-summary-edit-chrome class="flex shrink-0 items-center gap-2" @mousedown.stop @click.stop>
        <Dropdown align="end">
            <template #trigger>
                <Button size="sm" :icon="currentChart?.icon" :text="currentChart?.title ?? config.chart" />
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
</template>
