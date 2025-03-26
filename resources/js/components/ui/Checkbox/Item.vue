<script setup>
import { CheckboxIndicator, CheckboxRoot, useId } from 'reka-ui';

const props = defineProps({
    description: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: null },
    value: { type: [String, Number, Boolean], required: true },
});

const id = useId();
</script>

<template>
    <div class="flex items-start gap-2">
        <CheckboxRoot
            :id
            :value="value"
            class="bg-white size-4 mt-0.5 rounded-sm border border-gray-300 dark:border-none data-[state=checked]:border-gray-900 data-[state=checked]:bg-gray-900 dark:data-[state=checked]:bg-white shadow-ui-xs outline-hidden cursor-default"
        >
            <CheckboxIndicator
                class="flex items-center justify-center w-full h-full relative text-white dark:text-gray-800"
            >
                <svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 1L3.5 6.5L1 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </CheckboxIndicator>
        </CheckboxRoot>
        <label class="flex flex-col" :for="id">
            <span class="text-gray-600 dark:text-gray-400 font-normal antialiased text-sm">
                <slot>{{ label || value }}</slot>
            </span>
            <span v-if="description" class="block text-xs text-gray-500 mt-0.5 leading-snug">{{ description }}</span>
        </label>
    </div>
</template>
