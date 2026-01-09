<script setup>
import { CheckboxIndicator, CheckboxRoot, useId } from 'reka-ui';
import { computed } from 'vue';
import { cva } from 'cva';

const props = defineProps({
    /** Controls the vertical alignment of the checkbox with its label. Options: `start`, `center` */
    align: { type: String, default: 'start', validator: (value) => ['start', 'center'].includes(value) },
    /** Description text to display below the label */
    description: { type: String, default: null },
    /** When `true`, disables the checkbox */
    disabled: { type: Boolean, default: false },
    /** When `true`, displays the checkbox in an indeterminate state (shows a dash) */
    indeterminate: { type: Boolean, default: false },
    /** Label text to display next to the checkbox */
    label: { type: String, default: null },
    /** The controlled value of the checkbox */
    modelValue: { type: [Boolean, null], default: null },
    /** Name attribute for the checkbox input */
    name: { type: String, default: null },
    readOnly: { type: Boolean, default: false },
    /** Controls the size of the checkbox. Options: `sm`, `base` */
    size: { type: String, default: 'base' },
    /** When `true`, hides the label and description. Use this when the checkbox is used in a context where the label is provided elsewhere, like in a table cell */
    solo: { type: Boolean, default: false },
    /** Tab index for keyboard navigation */
    tabindex: { type: Number, default: null },
    /** Value of the checkbox when used in a group */
    value: { type: [String, Number, Boolean] },
});

const emit = defineEmits(['update:modelValue', 'keydown']);

const id = useId();

const handleKeydown = (event) => {
    emit('keydown', event);

    if (event.key === 'Enter' && !event.defaultPrevented) {
        event.target.closest('form')?.requestSubmit();
    }
};

const checkboxClasses = computed(() => {
    return cva({
        base: [
            'shadow-ui-xs mt-0.5 cursor-default rounded-sm border border-gray-400/75 bg-white',
            'dark:bg-gray-500 dark:border-gray-900',
            'data-[state=checked]:border-ui-accent-bg data-[state=checked]:bg-ui-accent-bg',
            'data-[state=indeterminate]:border-ui-accent-bg data-[state=indeterminate]:bg-ui-accent-bg',
            'dark:border-none',
            'dark:data-[disabled]:bg-ui-accent-bg/60 dark:data-[disabled]:border-ui-accent-bg/70',
            'dark:data-[disabled]:text-gray-400 dark:data-[disabled]:cursor-not-allowed',
            'shrink-0'
        ],
        variants: {
            size: {
                sm: 'size-3.75',
                base: 'size-4',
            },
        },
    })({ ...props });
});

const containerClasses = computed(() => {
    return cva({
        base: 'flex gap-2',
        variants: {
            align: {
                start: 'items-start',
                center: 'items-center',
            },
        },
    })({ ...props });
});

const conditionalProps = computed(() => {
    const props_obj = {};

    if (props.modelValue !== null) {
        props_obj.modelValue = props.modelValue;
    }

    if (props.indeterminate) {
        props_obj.indeterminate = true;
    }

    // Only add aria-describedby if description exists AND it's not a solo checkbox
    if (props.description && !props.solo) {
        props_obj['aria-describedby'] = `${id}-description`;
    }

    if (props.solo && (props.label || props.value)) {
        props_obj['aria-label'] = props.label || props.value;
    }

    return props_obj;
});
</script>

<template>
    <div :class="containerClasses">
        <CheckboxRoot
            :disabled="readOnly || disabled"
            :id
            :name="name"
            :value="value"
            v-bind="conditionalProps"
            @update:modelValue="emit('update:modelValue', $event)"
            @keydown="handleKeydown"
            :class="checkboxClasses"
            :tabindex="tabindex"
        >
            <CheckboxIndicator class="relative flex h-full w-full items-center justify-center text-white">
                <!-- Checkmark icon for checked state -->
                <svg v-if="!indeterminate" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-2.5 shrink-0" aria-hidden="true"><path d="M9 1L3.5 6.5L1 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                <!-- Dash icon for indeterminate state -->
                <svg v-else viewBox="0 0 10 2" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-2.5 shrink-0" aria-hidden="true"><path d="M2 1H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" /></svg>
            </CheckboxIndicator>
            <span class="sr-only">
                {{ indeterminate ? __('Indeterminate') : (modelValue ? __('Checked') : __('Unchecked')) }}
            </span>
        </CheckboxRoot>
        <div class="flex flex-col" v-if="!solo">
            <label class="text-sm font-normal antialiased dark:text-gray-200" :for="id">
                <slot>{{ label || value }}</slot>
            </label>
            <p v-if="description" :id="`${id}-description`" class="mt-0.5 block text-xs leading-snug text-gray-500 dark:text-gray-200">{{ description }}</p>
        </div>
    </div>
</template>
