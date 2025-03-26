<script setup>
import { RadioGroupIndicator, RadioGroupItem, useId } from 'reka-ui';

const props = defineProps({
    description: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: null },
    value: { type: [String, Number, Boolean], required: true },
});

const id = useId();
</script>

<template>
    <div class="flex items-start gap-3">
        <RadioGroupItem
            :id
            :value="value"
            class="bg-white size-4 mt-0.5 rounded-full border border-gray-300 dark:border-none data-[active=true]:border-gray-900 data-[active=true]:bg-gray-900 dark:data-[active=true]:bg-white shadow-ui-xs outline-hidden cursor-default"
        >
            <RadioGroupIndicator
                class="flex items-center justify-center w-full h-full relative after:content-[''] after:block after:w-1.5 after:h-1.5 after:rounded-[50%] after:bg-white dark:after:bg-gray-800"
            />
        </RadioGroupItem>
        <label class="flex flex-col" :for="id">
            <span class="text-gray-600 dark:text-gray-400 font-normal antialiased text-sm">
                <slot>{{ label || value }}</slot>
            </span>
            <span v-if="description" class="block text-xs text-gray-500 mt-0.5 leading-snug">{{ description }}</span>
        </label>
    </div>
</template>
