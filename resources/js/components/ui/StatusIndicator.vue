<template>
    <span class="flex items-center gap-2">
        <span v-if="showDot" class="size-2 rounded-full" :class="statusClass" v-tooltip="label" />
        <span v-if="showLabel" class="status-index-field select-none" :class="`status-${status}`" v-text="label" />
    </span>
</template>

<script setup>
import { computed } from 'vue';
import { Tooltip } from '@statamic/ui';

const props = defineProps({
    status: {
        type: String,
        required: false,
        default: 'published',
        validator: (value) => ['published', 'scheduled', 'expired', 'draft', 'hidden'].includes(value),
    },
    date: { type: String, default: null },
    showDot: { type: Boolean, default: true },
    showLabel: { type: Boolean, default: false },
    private: { type: Boolean, default: false },
});

const statusClass = computed(() => {
    if ((props.status === 'published' && props.private) || props.status === 'hidden') {
        return 'bg-transparent border border-gray-600';
    } else if (props.status === 'published') {
        return 'bg-green-400';
    }
    return 'bg-gray-300 dark:bg-gray-200';
});

const label = computed(() => {
    const labels = {
        published: __('Published'),
        scheduled: __('Scheduled'),
        expired: __('Expired'),
        draft: __('Draft'),
    };
    return labels[props.status];
});
</script>
