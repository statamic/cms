<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: false,
        default: 'complete',
        validator: (value) => ['complete', 'incomplete', 'spam'].includes(value),
    },
    showDot: { type: Boolean, default: true },
    showLabel: { type: Boolean, default: false },
});

const statusClass = computed(() => {
    return {
        complete: 'bg-green-400',
        incomplete: 'bg-gray-300 dark:bg-gray-200',
        spam: 'bg-amber-500',
    }[props.status];
});

const label = computed(() => {
    return {
        complete: __('Complete'),
        incomplete: __('Incomplete'),
        spam: __('Spam')
    }[props.status];
});
</script>

<style>
@reference "../../../css/app.css";

.status-complete {
    @apply bg-green-200 text-green-900 dark:bg-green-300/6 dark:text-green-300;
}

.status-spam {
    @apply bg-amber-200 text-amber-900 dark:bg-amber-300/6 dark:text-amber-300;
}
</style>

<template>
    <span class="flex items-center gap-2">
        <span v-if="showDot" class="size-2 rounded-full" :class="statusClass" v-tooltip="label" />
        <span v-if="showLabel" class="status-index-field select-none" :class="`status-${status}`" v-text="label" />
    </span>
</template>
