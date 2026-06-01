<script setup>
import { computed, inject, useId } from 'vue';
import { RadioGroupIndicator, RadioGroupItem } from 'reka-ui';

const props = defineProps({
    /** Description text to display below the label */
    description: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    /** Label text to display next to the radio button */
    label: { type: String, default: null },
    readOnly: { type: Boolean, default: false },
    /** Value of the radio button */
    value: { type: [String, Number, Boolean], required: true },
});

const appearance = inject('radioAppearance', computed(() => 'default'));
const isChips = computed(() => appearance.value === 'chips');
const id = useId();

const chipClasses = [
    'inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 h-10 text-sm font-medium antialiased cursor-default',
    'border border-gray-300 bg-linear-to-b from-white to-gray-50 text-gray-900 shadow-ui-xs',
    'hover:to-gray-100 data-[state=checked]:from-gray-100 data-[state=checked]:to-gray-100 data-[state=checked]:inset-shadow-sm/10',
    'data-[disabled]:cursor-not-allowed data-[disabled]:opacity-50',
    'dark:from-gray-800 dark:to-gray-850 dark:hover:to-gray-800 dark:border-white/10 dark:text-gray-300 dark:shadow-lg',
    'dark:data-[state=checked]:from-gray-950 dark:data-[state=checked]:to-gray-850 dark:data-[state=checked]:text-white',
];
</script>

<template>
    <RadioGroupItem
        v-if="isChips"
        :id
        :value="value"
        :disabled="readOnly || disabled"
        :class="chipClasses"
        data-ui-radio-item
    >
        <slot>{{ label || value }}</slot>
    </RadioGroupItem>
    <div v-else class="flex items-start gap-1.5" data-ui-radio-item>
        <RadioGroupItem
            :id
            :value="value"
            :disabled="readOnly || disabled"
            class="
                shadow-ui-xs mt-0.5 size-4 cursor-default rounded-full
                focus:focus-outline border border-gray-400/75 dark:border-none with-contrast:border-gray-100 bg-white outline-hidden
                data-[state=checked]:border-ui-accent-bg data-[disabled]:opacity-50
                dark:bg-gray-500 dark:data-[state=checked]:border-none dark:data-[state=checked]:bg-gray-300
            "
        >
            <RadioGroupIndicator
                class="
                    relative flex h-full w-full items-center justify-center rounded-[50%]
                    border border-ui-accent-bg after:block after:h-[0.5rem] after:w-[0.5rem] after:rounded-[50%]
                    after:bg-ui-accent-bg after:content-[''] dark:border-none
                "
            />
        </RadioGroupItem>
        <label class="flex flex-col" :class="{ 'opacity-50': disabled }" :for="id">
            <span class="text-sm font-normal antialiased dark:text-gray-200">
                <slot>{{ label || value }}</slot>
            </span>
            <span v-if="description" class="mt-0.5 block text-xs leading-snug text-gray-500">{{ description }}</span>
        </label>
    </div>
</template>
