<script setup>
import { computed, defineProps } from 'vue';
import DateFormatter from '@statamic/components/DateFormatter.js';

const props = defineProps({
    of: { type: String },
    options: { type: Object },
});

const date = computed(() => (props.of ? new Date(props.of) : Date.now()));

const text = computed(() => DateFormatter.format(date.value, props.options));
</script>

<template>{{ text }}</template>
