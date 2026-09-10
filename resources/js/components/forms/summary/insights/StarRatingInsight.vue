<script setup lang="ts">
import { Icon } from '@ui';
import type { ChartMetric } from '../types';

defineProps<{
    average: number;
    total: number;
    metric?: ChartMetric;
}>();
</script>

<template>
    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
        <Icon
            v-for="star in total"
            :key="star"
            :name="star <= Math.round(average) ? 'star-filled' : 'star'"
            class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300"
        />
        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">
            {{ __(':average/:total Average', { average, total }) }}
        </span>
    </div>
</template>
