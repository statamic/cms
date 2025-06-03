<script setup>
import { useAttrs } from 'vue';
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
});

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();
</script>

<template>
    <Combobox
        v-bind="attrs"
        :size
        :clearable
        :disabled
        :option-label
        :option-value
        :options
        :searchable="false"
        :model-value="modelValue"
        @update:modelValue="emit('update:modelValue', $event)"
    />
</template>
