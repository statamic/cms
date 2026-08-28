<script setup lang="ts">
import Metric from '@/components/ui/Chart/Metric.vue';
import type { ChartMetric } from '../types';

withDefaults(
    defineProps<{
        count: number;
        percent: number;
        metric?: ChartMetric;
    }>(),
    {
        metric: 'percent',
    },
);
</script>

<template>
    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
        <Metric :metric :count :percent class="text-xs font-semibold st-text-trim-cap tabular-nums" />
        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('of users checked the toggle') }}</span>
    </div>
</template>
