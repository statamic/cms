<script setup>
import { Icon } from '@ui';

defineProps({
    examples: { type: Array, required: true },
});

defineEmits(['pick']);
</script>

<template>
    <div class="space-y-2">
        <p class="px-1 pt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Try asking') }}</p>
        <button
            v-for="example in examples"
            :key="example.text"
            type="button"
            class="group flex w-full cursor-pointer items-start gap-2.5 rounded-xl border border-gray-200 bg-gray-50 p-3 text-start text-sm/5 text-gray-700 shadow-ui-sm transition-colors hover:border-gray-300 hover:bg-white focus-visible:focus-outline dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-900"
            @click="$emit('pick', example.text)"
        >
            <Icon :name="example.icon" class="mt-0.5 size-4 shrink-0 text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-200" />
            <span class="flex-1">{{ example.text }}</span>
            <Icon name="arrow-right" class="mt-0.5 size-3.5 shrink-0 text-gray-600 opacity-0 transition-opacity group-hover:opacity-100 dark:text-gray-400 rtl:rotate-180" />
        </button>
    </div>
</template>
