<script setup>
import { CheckboxIndicator, CheckboxRoot, useId } from 'reka-ui';
import { computed } from 'vue';

const props = defineProps({
    description: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: null },
    value: { type: [String, Number, Boolean], required: true },
    solo: { type: Boolean, default: false },
    size: { type: String, default: 'base' },
    align: { type: String, default: 'start', validator: (value) => ['start', 'center'].includes(value) },
});

const id = useId();

const checkboxClasses = computed(() => {
    const sizes = {
        sm: 'size-3.75',
        base: 'size-4'
    };

    return `shadow-ui-xs mt-0.5 ${sizes[props.size]} cursor-default rounded-sm border border-gray-300 bg-white data-[state=checked]:border-gray-900 data-[state=checked]:bg-gray-900 dark:border-none dark:data-[state=checked]:bg-white data-[disabled]:bg-gray-100 data-[disabled]:border-gray-200 data-[disabled]:text-gray-400 data-[disabled]:cursor-not-allowed shrink-0`;
});

const containerClasses = computed(() => {
    return `flex items-${props.align} gap-2`;
});
</script>

<template>
    <div :class="containerClasses">
        <CheckboxRoot
            :disabled
            :id
            :value="value"
            :class="checkboxClasses"
        >
            <CheckboxIndicator
                class="relative flex h-full w-full items-center justify-center text-white dark:text-gray-800"
            >
                <svg viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-2.5">
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
        <label class="flex flex-col" :for="id" v-if="!solo">
            <span class="text-sm font-normal text-gray-600 antialiased dark:text-gray-400">
                <slot>{{ label || value }}</slot>
            </span>
            <span v-if="description" class="mt-0.5 block text-xs leading-snug text-gray-500">{{ description }}</span>
        </label>
    </div>
</template>
