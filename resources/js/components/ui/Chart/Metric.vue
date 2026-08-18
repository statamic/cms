<script setup>
import { computed } from 'vue';

const props = defineProps({
    count: { type: Number, default: 0 },
    metric: {
        type: String,
        default: 'percent',
        validator: (value) => ['percent', 'count'].includes(value),
    },
    percent: { type: Number, default: 0 },
});

const value = computed(() => (props.metric === 'count' ? props.count : `${props.percent}%`));
</script>

<template>
    <span data-ui-chart-metric>{{ value }}</span>
</template>
