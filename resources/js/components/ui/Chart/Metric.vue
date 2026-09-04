<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        count?: number;
        metric?: 'percent' | 'count';
        percent?: number;
    }>(),
    {
        count: 0,
        metric: 'percent',
        percent: 0,
    },
);

const value = computed<string | number>(() => (props.metric === 'count' ? props.count : `${props.percent}%`));
</script>

<template>
    <span data-ui-chart-metric>{{ value }}</span>
</template>
