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
    <div class="flex items-start gap-1.5" data-ui-radio-item>
        <RadioGroupItem
            :id
            :value="value"
            class="
                shadow-ui-xs mt-0.5 size-4 cursor-default rounded-full
                focus:focus-outline border border-gray-300 dark:border-none bg-white outline-hidden
                data-[state=checked]:border-ui-accent
                dark:bg-gray-400 dark:data-[state=checked]:border-none dark:data-[state=checked]:bg-gray-300
            "
        >
            <RadioGroupIndicator
                class="
                    relative flex h-full w-full items-center justify-center rounded-[50%]
                    border border-ui-accent after:block after:h-[0.5rem] after:w-[0.5rem] after:rounded-[50%]
                    after:bg-ui-accent after:content-[''] dark:border-none dark:after:bg-dark-ui-accent
                "
            />
        </RadioGroupItem>
        <label class="flex flex-col" :for="id">
            <span class="text-sm font-normal text-gray-600 antialiased dark:text-gray-200">
                <slot>{{ label || value }}</slot>
            </span>
            <span v-if="description" class="mt-0.5 block text-xs leading-snug text-gray-500">{{ description }}</span>
        </label>
    </div>
</template>
