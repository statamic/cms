<script setup>
import { computed, inject, useId } from 'vue';
import { RadioGroupIndicator, RadioGroupItem } from 'reka-ui';

const props = defineProps({
    /** Additional classes applied when the group appearance is `chips` */
    chipsClass: { type: String, default: 'border border-gray-300 dark:border-gray-700 mb-0 p-2 py-2 pe-4 shadow-ui-xs rounded-full' },
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

const id = useId();
</script>

<template>
    <div
        class="flex items-start gap-1.5"
        :class="appearance === 'chips' ? chipsClass : null"
        data-ui-radio-item
    >
        <RadioGroupItem
            :id
            :value="value"
            :disabled="readOnly || disabled"
            class="
                shadow-ui-xs mt-0.5 size-4 cursor-default rounded-full
                focus:focus-outline border border-gray-400/75 bg-white with-contrast:border-gray-100
                data-[state=checked]:border-ui-accent-bg data-[disabled]:opacity-50
                dark:border-gray-700 dark:bg-gray-500
                dark:data-[state=checked]:border-ui-accent-bg dark:data-[state=checked]:bg-ui-accent-bg
            "
        >
            <RadioGroupIndicator
                class="
                    relative flex h-full w-full items-center justify-center rounded-[50%]
                    border border-ui-accent-bg after:block after:h-2 after:w-2 after:rounded-[50%]
                    after:bg-ui-accent-bg after:content-['']
                    dark:border-none dark:after:bg-white
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
