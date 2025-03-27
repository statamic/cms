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
            class="shadow-ui-xs mt-0.5 size-4 cursor-default rounded-sm border border-gray-300 bg-white outline-hidden data-[state=checked]:border-gray-900 data-[state=checked]:bg-gray-900 dark:border-none dark:data-[state=checked]:bg-white"
        >
            <CheckboxIndicator
                class="relative flex h-full w-full items-center justify-center text-white dark:text-gray-800"
            >
                <svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9 1L3.5 6.5L1 4"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </CheckboxIndicator>
        </CheckboxRoot>
        <label class="flex flex-col" :for="id">
            <span class="text-sm font-normal text-gray-600 antialiased dark:text-gray-400">
                <slot>{{ label || value }}</slot>
            </span>
            <span v-if="description" class="mt-0.5 block text-xs leading-snug text-gray-500">{{ description }}</span>
        </label>
    </div>
</template>
