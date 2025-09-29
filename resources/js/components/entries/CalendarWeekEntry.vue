<template>
    <a
        :href="entry.edit_url"
        :key="entry.id"
        class="block text-xs p-1 rounded-r border-l-2 mb-1 cursor-pointer hover:shadow-sm"
        :class="entryClasses"
        draggable="true"
        @dragstart="handleDragStart"
    >
        <div class="font-medium line-clamp-2">{{ entry.title }}</div>
    </a>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    entry: { type: Object, required: true }
});

const emit = defineEmits(['dragstart']);

const entryClasses = computed(() => ({
    'border-green-500 bg-green-50 dark:bg-green-900/20': props.entry.status === 'published',
    'border-gray-300 bg-gray-50 dark:bg-gray-800': props.entry.status === 'draft',
    'border-purple-500 bg-purple-50 dark:bg-purple-900/20': props.entry.status === 'scheduled'
}));

const handleDragStart = (event) => {
    emit('dragstart', event, props.entry);
};
</script>
