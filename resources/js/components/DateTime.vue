<script setup>
import { computed } from 'vue';
import DateFormatter from '@statamic/components/DateFormatter.js';

const props = defineProps({
    of: { type: String },
    options: { type: Object },
    dateOnly: { type: Boolean },
    timeOnly: { type: Boolean },
});

const date = computed(() => (props.of ? new Date(props.of) : Date.now()));

const options = computed(() => {
    if (props.dateOnly) return 'date';
    if (props.timeOnly) return 'time';
    return props.options;
});

const text = computed(() => DateFormatter.format(date.value, options.value));
</script>

<template>{{ text }}</template>
