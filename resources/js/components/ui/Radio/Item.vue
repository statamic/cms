<script setup>
import { computed, useId } from 'vue';
import { RadioGroupIndicator, RadioGroupItem } from 'reka-ui';
import { injectRadioContext } from './Group.vue';

const props = defineProps({
    /** Optional ID for the radio button */
    id: { type: String, default: () => useId() },
    /** Description text to display below the label */
    description: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    /** Label text to display next to the radio button */
    label: { type: String, default: null },
    readOnly: { type: Boolean, default: false },
    /** Value of the radio button */
    value: { type: [String, Number, Boolean], required: true },
});

const { appearance } = injectRadioContext() ?? { appearance: computed(() => 'default') };
</script>

<template>
    <div
        class="relative flex items-start gap-1.5"
        :class="appearance === 'chips' ? 'mb-0 rounded-full border border-gray-300 bg-linear-to-b from-white to-white p-2 py-2 pe-4 shadow-ui-sm transition-[background] hover:bg-gray-50 hover:to-gray-50 with-contrast:border-gray-500 dark:border-gray-700/80 dark:from-gray-850 dark:to-gray-900 dark:shadow-ui-md dark:hover:bg-gray-900 dark:hover:to-gray-850' : null"
        data-ui-radio-item
    >
        <RadioGroupItem
            :id="props.id"
            :value="value"
            :disabled="readOnly || disabled"
            :aria-describedby="description ? `${props.id}-description` : undefined"
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
        <div class="flex flex-col" :class="{ 'opacity-50': disabled }">
            <label class="text-sm font-normal antialiased cursor-pointer dark:text-gray-200 before:absolute before:inset-0 before:content-['']" :for="props.id">
                <slot>{{ label || value }}</slot>
            </label>
            <span v-if="description" :id="`${props.id}-description`" class="mt-0.5 block text-xs leading-snug text-gray-500">{{ description }}</span>
        </div>
    </div>
</template>
