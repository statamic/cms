<script setup>
import { computed, defineProps } from 'vue';
import DateFormatter from '@statamic/components/DateFormatter.js';

const props = defineProps({
    of: { type: String },
    options: { type: Object },
});

const date = computed(() => (props.of ? new Date(props.of) : Date.now()));

const text = computed(() => new DateFormatter().of(date.value).options(props.options).toString());
</script>

<template>{{ text }}</template>
