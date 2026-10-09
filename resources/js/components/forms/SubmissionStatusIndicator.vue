<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: false,
        default: 'finalized',
        validator: (value) => ['finalized', 'partial'].includes(value),
    },
    showDot: { type: Boolean, default: true },
    showLabel: { type: Boolean, default: false },
});

const statusClass = computed(() => {
    return {
        finalized: 'bg-green-400',
        partial: 'bg-gray-300 dark:bg-gray-200',
    }[props.status];
});

const label = computed(() => {
    return {
        finalized: __('Finalized'),
        partial: __('Partial'),
    }[props.status];
});
</script>

<template>
    <span class="flex items-center gap-2">
        <span v-if="showDot" class="size-2 rounded-full" :class="statusClass" v-tooltip="label" />
        <span v-if="showLabel" class="status-index-field select-none" :class="`status-${status}`" v-text="label" />
    </span>
</template>
