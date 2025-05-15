<script setup>
import { useAttrs } from 'vue';
import { WithField, Combobox } from '@statamic/ui';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    description: { type: String, default: null },
    label: { type: String, default: null },
    modelValue: { type: [Object, String], default: null },
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
    <WithField :label :description>
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
    </WithField>
</template>
