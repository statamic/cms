<script setup>
import { useAttrs, useSlots } from 'vue';
import Combobox from '../Combobox/Combobox.vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    clearable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    icon: { type: String, default: null },
    modelValue: { type: [Object, String, Number], default: null },
    optionLabel: { type: String, default: 'label' },
    options: { type: Array, default: null },
    optionValue: { type: String, default: 'value' },
    placeholder: { type: String, default: () => __('Select...') },
    readOnly: { type: Boolean, default: false },
    size: { type: String, default: 'base' },
    variant: { type: String, default: 'default' },
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
        :clearable
        :disabled
        :icon
        :model-value="modelValue"
        :option-label
        :option-value
        :options
        :placeholder
        :read-only
        :searchable="false"
        :size
        :variant
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
