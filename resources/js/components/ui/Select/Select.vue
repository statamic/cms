<script setup>
import { useAttrs, useSlots } from 'vue';
import Combobox from '../Combobox/Combobox.vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    clearable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: { type: String, default: null },
    /** The controlled value of the select. */
    modelValue: { type: [Object, String, Number], default: null },
    /** Key of the option's label in the option's object. */
    optionLabel: { type: String, default: 'label' },
    /** Array of option objects */
    options: { type: Array, default: null },
    /** Key of the option's value in the option's object. */
    optionValue: { type: String, default: 'value' },
    placeholder: { type: String, default: () => __('Select...') },
    readOnly: { type: Boolean, default: false },
    /** Controls the size of the select. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
    size: { type: String, default: 'base' },
    /** Controls the appearance of the select. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
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
