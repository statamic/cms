<script setup>
import { computed, useId } from 'vue';
import { RadioGroupIndicator, RadioGroupItem } from 'reka-ui';
import { injectRadioContext } from './Group.vue';

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

const { appearance } = injectRadioContext() ?? { appearance: computed(() => 'default') };

const isChips = computed(() => appearance.value === 'chips');

const id = useId();
</script>

<template>
    <div
        class="flex items-start gap-1.5"
        :class="isChips ? 'mb-0 border border-gray-300 dark:border-gray-700 p-2 py-2 pe-4 shadow-ui-xs rounded-full' : null"
        data-ui-radio-item
    >
        <RadioGroupItem
            :id
            :value="value"
            :disabled="readOnly || disabled"
            :data-readonly="readOnly ? true : undefined"
            class="
                shadow-ui-xs mt-0.5 size-4 cursor-default rounded-full outline-hidden
                focus:focus-outline border border-gray-400/75 bg-white with-contrast:border-gray-100
                data-[state=checked]:border-ui-accent-bg data-[disabled]:opacity-50
                dark:border-gray-700 dark:bg-gray-500
                dark:data-[state=checked]:border-ui-accent-bg dark:data-[state=checked]:bg-ui-accent-bg
                data-readonly:data-[state=unchecked]:border-dashed! data-readonly:data-[state=unchecked]:border-gray-500/90 data-readonly:data-[state=unchecked]:with-contrast:border-gray-100
                data-readonly:data-[state=unchecked]:dark:border! data-readonly:data-[state=unchecked]:dark:border-dashed! data-readonly:data-[state=unchecked]:dark:bg-gray-900
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
