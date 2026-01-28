<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import DateFormatter from '@/components/DateFormatter.js';

const props = defineProps({
    entry: { type: Object, required: true }
});

const entryClasses = computed(() => ({
    'border-green-500 hover:bg-green-50 dark:hover:bg-green-900': props.entry.status === 'published',
    'border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800': props.entry.status === 'draft',
    'border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900': props.entry.status === 'scheduled'
}));

const time = computed(() => DateFormatter.format(props.entry.date?.date || props.entry.date, 'time'))
</script>

<template>
    <Link
        :href="entry.edit_url"
        :key="entry.id"
        class="text-2xs @3xl:text-xs px-2 border-s-2 rounded-e-sm cursor-pointer flex flex-col group/entry"
        :class="entryClasses"
    >
        <span class="line-clamp-2" v-text="entry.title" />
        <span
            class="hidden @4xl:block text-2xs text-gray-400 dark:text-gray-400 group-hover/entry:dark:text-gray-300"
            v-text="time"
        />
    </Link>
</template>
