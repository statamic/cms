<script setup>
import { computed } from 'vue';
import { formatTime } from './calendar.js';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    entry: { type: Object, required: true }
});

const entryClasses = computed(() => ({
    'border-green-500 hover:bg-green-50': props.entry.status === 'published',
    'border-gray-300 hover:bg-gray-50': props.entry.status === 'draft',
    'border-purple-500 hover:bg-purple-50': props.entry.status === 'scheduled'
}));
</script>

<template>
    <Link
        :href="entry.edit_url"
        :key="entry.id"
        class="text-2xs @3xl:text-xs px-2 border-s-2 rounded-e-sm cursor-pointer flex flex-col"
        :class="entryClasses"
    >
        <span class="line-clamp-2">
            {{ entry.title }}
        </span>
        <span class="hidden @4xl:block text-2xs text-gray-400 dark:text-gray-400">
            {{ formatTime(entry.date?.date || entry.date) }}
        </span>
    </Link>
</template>
