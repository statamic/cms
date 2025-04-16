<template>
    <div class="flex items-center gap-2">
        <span
            v-if="showDot"
            class="size-2 rounded-full"
            :class="statusClass"
            v-tooltip="tooltip"
        />
        <span
            v-if="showLabel"
            class="status-index-field select-none"
            :class="`status-${status}`"
            v-tooltip="tooltip"
            v-text="label"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: value => ['published', 'scheduled', 'expired', 'draft'].includes(value)
    },
    date: {type: String, default: null },
    showDot: {type: Boolean, default: true },
    showLabel: {type: Boolean, default: false },
    private: {type: Boolean, default: false },
    isDated: {type: Boolean, default: false }
})

const statusClass = computed(() => {
    if (props.status === 'published' && props.private) {
        return 'bg-transparent border border-gray-600'
    } else if (props.status === 'published') {
        return 'bg-green-400'
    }
    return 'bg-gray-300 dark:bg-dark-200'
})

const label = computed(() => {
    const labels = {
        published: __('Published'),
        scheduled: __('Scheduled'),
        expired: __('Expired'),
        draft: __('Draft')
    }
    return labels[props.status]
})

const tooltip = computed(() => {
    if (!props.date) return null

    if (props.status === 'published') {
        return props.isDated ? __('messages.status_published_with_date', { date: props.date }) : null
    } else if (props.status === 'scheduled') {
        return __('messages.status_scheduled_with_date', { date: props.date })
    } else if (props.status === 'expired') {
        return __('messages.status_expired_with_date', { date: props.date })
    }

    return null
})
</script>

