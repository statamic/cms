<script setup lang="ts">
import Metric from '@/components/ui/Chart/Metric.vue';
import type { ChartMetric } from '../types';
import { __ } from '@/bootstrap/globals';

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

const [textBefore, textAfter] = __('statamic::messages.form_summary_checked_insight')
    .split(':metric')
    .map((text) => text.trim());
</script>

<template>
    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
        <span v-if="textBefore" class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ textBefore }}</span>
        <Metric :metric :count :percent class="text-xs font-semibold st-text-trim-cap tabular-nums" />
        <span v-if="textAfter" class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ textAfter }}</span>
    </div>
</template>
