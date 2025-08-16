<script setup>
import { useAttrs, useSlots } from 'vue';
import { Combobox } from '@statamic/cms/ui';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    buttonAppearance: { type: Boolean, default: true },
    clearable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    flat: { type: Boolean, default: false },
    modelValue: { type: [Object, String, Number], default: null },
    optionLabel: { type: String, default: 'label' },
    options: { type: Array, default: null },
    optionValue: { type: String, default: 'value' },
    placeholder: { type: String, default: 'Select...' },
    readOnly: { type: Boolean, default: false },
    size: { type: String, default: 'base' },
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
        :button-appearance
        :clearable
        :disabled
        :flat
        :model-value="modelValue"
        :option-label
        :option-value
        :options
        :placeholder
        :read-only
        :searchable="false"
        :size
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
