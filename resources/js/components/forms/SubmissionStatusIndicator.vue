<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: false,
        default: 'complete',
        validator: (value) => ['complete', 'partial'].includes(value),
    },
    showDot: { type: Boolean, default: true },
    showLabel: { type: Boolean, default: false },
});

const statusClass = computed(() => {
    return {
        complete: 'bg-green-400',
        partial: 'bg-gray-300 dark:bg-gray-200',
    }[props.status];
});

const label = computed(() => {
    return {
        complete: __('Complete'),
        partial: __('Partial'),
    }[props.status];
});
</script>

<style>
@reference "../../../css/app.css";

.status-complete {
    @apply bg-green-200 text-green-900 dark:bg-green-300/6 dark:text-green-300;
}
</style>

<template>
    <span class="flex items-center gap-2">
        <span v-if="showDot" class="size-2 rounded-full" :class="statusClass" v-tooltip="label" />
        <span v-if="showLabel" class="status-index-field select-none" :class="`status-${status}`" v-text="label" />
    </span>
</template>
