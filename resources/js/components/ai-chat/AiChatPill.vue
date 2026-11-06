<script setup>
import { Icon } from '@ui';

// A floating control for the chat, with room for a finger: an icon and a word in a white pill
defineProps({
    icon: { type: String, required: true },
    text: { type: String, required: true },
});
</script>

<template>
    <button
        type="button"
        :aria-label="text"
        class="flex h-9 cursor-pointer items-center gap-1.5 rounded-full bg-white ps-3 pe-4 text-sm font-medium whitespace-nowrap text-gray-900 shadow-ui-lg ring ring-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600"
    >
        <Icon :name="icon" class="size-4! shrink-0" />
        {{ text }}
    </button>
</template>
