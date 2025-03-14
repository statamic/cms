<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
    of: {
        type: String,
    },
    options: {
        type: Object,
        default: () => ({
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
        }),
    },
});

const date = computed(() => (props.of ? new Date(props.of) : Date.now()));

const text = computed(() => Statamic.$date.of(date.value).options(props.options).toString());
</script>

<template>{{ text }}</template>
