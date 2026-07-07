<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: false,
        default: 'open',
        validator: (value) => ['open', 'closed', 'limit_reached'].includes(value),
    },
    showDot: { type: Boolean, default: true },
    showLabel: { type: Boolean, default: false },
});

const statusClass = computed(() => {
    return props.status === 'open' ? 'bg-green-400' : 'bg-gray-300 dark:bg-gray-200';
});

const label = computed(() => {
    return {
        open: __('Open'),
        closed: __('Closed'),
        limit_reached: __('Limit Reached'),
    }[props.status];
});
</script>

<template>
    <span class="flex items-center gap-2">
        <span v-if="showDot" class="size-2 rounded-full" :class="statusClass" v-tooltip="label" />
        <span v-if="showLabel" class="status-index-field select-none" :class="`status-${status}`" v-text="label" />
    </span>
</template>
