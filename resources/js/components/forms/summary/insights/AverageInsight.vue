<script setup lang="ts">
import type { ChartMetric } from '../types';

defineProps<{
    average: number | string;
    prefix?: string;
    suffix?: string;
    metric?: ChartMetric;
}>();
</script>

<template>
    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
        <span class="text-xs font-semibold st-text-trim-cap tabular-nums">{{ prefix }}{{ average }}{{ suffix }}</span>
        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('Average') }}</span>
    </div>
</template>
