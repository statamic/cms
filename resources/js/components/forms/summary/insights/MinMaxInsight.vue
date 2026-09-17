<script setup lang="ts">
import type { ChartMetric } from '../types';

defineProps<{
    min: number | string;
    max: number | string;
    prefix?: string;
    suffix?: string;
    metric?: ChartMetric;
}>();
</script>

<template>
    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
        <span class="text-xs font-semibold st-text-trim-cap tabular-nums">
            {{ prefix }}{{ min }}{{ suffix }}–{{ prefix }}{{ max }}{{ suffix }}
        </span>
        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('Min–Max') }}</span>
    </div>
</template>
