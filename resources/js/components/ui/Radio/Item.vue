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
            class="shadow-ui-xs mt-0.5 size-4 cursor-default rounded-full border border-gray-300 bg-white outline-hidden data-[active=true]:border-gray-900 data-[active=true]:bg-gray-900 dark:border-none dark:data-[active=true]:bg-white"
        >
            <RadioGroupIndicator
                class="relative flex h-full w-full items-center justify-center after:block after:h-1.5 after:w-1.5 after:rounded-[50%] after:bg-white after:content-[''] dark:after:bg-gray-800"
            />
        </RadioGroupItem>
        <label class="flex flex-col" :for="id">
            <span class="text-sm font-normal text-gray-600 antialiased dark:text-gray-400">
                <slot>{{ label || value }}</slot>
            </span>
            <span v-if="description" class="mt-0.5 block text-xs leading-snug text-gray-500">{{ description }}</span>
        </label>
    </div>
</template>
