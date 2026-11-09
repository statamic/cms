<script>
import createContext from '@/util/createContext.js';

export const [injectRadioContext, provideRadioContext] = createContext('Radio');
</script>

<script setup>
import { computed, useId } from 'vue';
import { RadioGroupRoot } from 'reka-ui';

const props = defineProps({
    /** Controls how the radio group is displayed. Options: `default`, `inline`, `chips` */
    appearance: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'inline', 'chips'].includes(value),
    },
    /** @deprecated Use `appearance="inline"` instead. */
    inline: { type: Boolean, default: false },
    /** The controlled value of the radio group */
    modelValue: { type: String, default: null },
    /** Name attribute for the radio group */
    name: { type: String, default: () => useId() },
    required: { type: Boolean, default: false },
});

const appearance = computed(() => {
    if (props.appearance !== 'default') {
        return props.appearance;
    }

    return props.inline ? 'inline' : 'default';
});

const inline = computed(() => appearance.value === 'inline' || appearance.value === 'chips');

provideRadioContext({ appearance });

const focus = function () {
    console.log('focusing. todo.');
};

defineEmits(['update:modelValue']);

defineExpose({ focus });
</script>

<template>
    <RadioGroupRoot
        :modelValue="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
        :name="name"
        class="relative block w-full space-y-2"
        :class="{
            'flex flex-wrap space-y-0 gap-y-2': inline,
            'gap-x-4': appearance === 'inline',
            'gap-x-2.5': appearance === 'chips',
        }"
        data-ui-input
        data-ui-radio-group
    >
        <slot />
    </RadioGroupRoot>
</template>
