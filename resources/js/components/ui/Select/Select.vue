<script setup>
import { useAttrs, useSlots } from 'vue';
import { Combobox } from '@statamic/ui';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: { type: [Object, String, Number], default: null },
    size: { type: String, default: 'base' },
    placeholder: { type: String, default: 'Select...' },
    clearable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    optionLabel: { type: String, default: 'label' },
    optionValue: { type: String, default: 'value' },
    options: { type: Array, default: null },
    flat: { type: Boolean, default: false },
    buttonAppearance: { type: Boolean, default: true },
});

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const slots = useSlots();
const usingSelectedOptionSlot = !!slots['selected-option'];
const usingNoOptionsSlot = !!slots['no-options'];
const usingOptionSlot = !!slots['option'];
</script>

<template>
    <Combobox
        v-bind="attrs"
        :size
        :placeholder
        :clearable
        :disabled
        :option-label
        :option-value
        :options
        :flat
        :button-appearance
        :searchable="false"
        :model-value="modelValue"
        @update:modelValue="emit('update:modelValue', $event)"
    >
        <template #selected-option="{ option }" v-if="usingSelectedOptionSlot">
            <slot name="selected-option" v-bind="{ option }" />
        </template>
        <template #no-options v-if="usingNoOptionsSlot">
            <slot name="no-options" />
        </template>
        <template #option="option" v-if="usingOptionSlot">
            <slot name="option" v-bind="option" />
        </template>
    </Combobox>
</template>
