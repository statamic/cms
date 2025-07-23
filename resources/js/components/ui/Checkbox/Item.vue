<script setup>
import { CheckboxIndicator, CheckboxRoot, useId } from 'reka-ui';
import { computed } from 'vue';

const props = defineProps({
    align: { type: String, default: 'start', validator: (value) => ['start', 'center'].includes(value) },
    description: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: null },
    modelValue: { type: [Boolean, null], default: null },
    name: { type: String, default: null },
    size: { type: String, default: 'base' },
    solo: { type: Boolean, default: false },
    value: { type: [String, Number, Boolean], required: true },
});

const emit = defineEmits(['update:modelValue']);

const id = useId();

const checkboxClasses = computed(() => {
    const sizes = {
        sm: 'size-3.75',
        base: 'size-4',
    };

    return `shadow-ui-xs mt-0.5 ${sizes[props.size]} cursor-default rounded-sm border border-gray-300 bg-white data-[state=checked]:border-gray-900 data-[state=checked]:bg-gray-900 dark:border-none dark:data-[state=checked]:bg-white data-[disabled]:bg-gray-100 data-[disabled]:border-gray-200 data-[disabled]:text-gray-400 data-[disabled]:cursor-not-allowed shrink-0`;
});

const containerClasses = computed(() => {
    return `flex items-${props.align} gap-2`;
});

const conditionalProps = computed(() => {
    if (props.modelValue === null) {
        return {};
    }

    return {
        modelValue: props.modelValue,
    };
});
</script>

<template>
    <div :class="containerClasses">
        <CheckboxRoot
            :disabled
            :id
            :name="name"
            :value="value"
            v-bind="conditionalProps"
            @update:modelValue="emit('update:modelValue', $event)"
            :class="checkboxClasses"
        >
            <CheckboxIndicator
                class="relative flex h-full w-full items-center justify-center text-white dark:text-gray-900"
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
        <div class="flex flex-col" v-if="!solo">
            <label class="text-sm font-normal antialiased dark:text-gray-400" :for="id">
                <slot>{{ label || value }}</slot>
            </label>
            <p v-if="description" class="mt-0.5 block text-xs leading-snug text-gray-500 dark:text-gray-400">{{ description }}</p>
        </div>
    </div>
</template>
