<script setup>
import { cva } from 'cva';
import { computed } from 'vue';
import Description from './Description.vue';
import Label from './Label.vue';
import ErrorMessage from './ErrorMessage.vue';
import markdown from '@/util/markdown.js';
import { twMerge } from 'tailwind-merge';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    /** When `true`, the field is styled as a configuration field with a two-column grid layout. */
    inline: { type: Boolean, default: false },
    /** Badge text to display next to the label. */
    badge: { type: String, default: '' },
    /** The reading direction of the field's label, instructions, and errors. */
    dir: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    /** Error message to display below the field. */
    error: { type: String },
    /** Object or array of error messages to display below the field. */
    errors: { type: Object },
    /** When `true`, forces the field to use full width even when `asConfig` is enabled. */
    fullWidthSetting: { type: Boolean, default: false },
    id: { type: String },
    /** Instructions text to display above or below the label. Supports Markdown. */
    instructions: { type: String, default: '' },
    /** When `true`, displays instructions below the control instead of below the label. */
    instructionsBelow: { type: Boolean, default: false },
    /** Label text for the field. */
    label: { type: String },
    readOnly: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
});

const labelProps = computed(() => ({
    badge: props.badge,
    for: props.id,
    required: props.required,
    text: props.label,
}));

const rootClasses = computed(() =>
    twMerge(cva({
        base: [
            'min-w-0',
        ],
        variants: {
            disabled: {
                true: 'opacity-50',
            },
            inline: {
                true: 'grid md:grid-cols-2 items-start px-4.5 py-4 gap-y-3 md:gap-y-0 md:gap-x-5!',
                false: 'flex flex-col gap-2',
            },
            fullWidthSetting: {
                true: 'md:grid-cols-1 md:gap-y-3',
            },
        },
    })({
        ...props,
    })),
);

const instructions = computed(() => props.instructions ? markdown(__(props.instructions), { openLinksInNewTabs: true }) : null);

const errors = computed(() => {
    if (props.error) {
        return [props.error];
    }

    return props.errors;
});

const hasErrors = computed(() => {
    if (!errors.value) return false;
    return Array.isArray(errors.value) ? errors.value.length > 0 : Object.keys(errors.value).length > 0;
});
</script>

<template>
    <div :class="[rootClasses, $attrs.class]" :dir="dir" :inert="$attrs.inert" data-ui-input-group :data-ui-field-has-errors="hasErrors ? '' : null">
        <div
            v-if="label || $slots.label || $slots.actions || (instructions && !instructionsBelow)"
            class="flex flex-col gap-1.5"
            data-ui-field-header
        >
            <div
                v-if="$slots.actions"
                class="flex items-center gap-x-1"
                :class="label || $slots.label ? 'justify-between' : 'justify-end'"
            >
                <slot name="label">
                    <Label v-if="label" v-bind="labelProps" class="flex-1" />
                </slot>
                <slot name="actions" />
            </div>
            <slot v-else name="label">
                <Label v-if="label" v-bind="labelProps" />
            </slot>
            <Description :text="instructions" v-if="instructions && !instructionsBelow" />
        </div>
        <slot />
        <div v-if="(instructions && instructionsBelow) || hasErrors" class="flex flex-col gap-2">
            <Description :text="instructions" v-if="instructions && instructionsBelow" />
            <ErrorMessage v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" />
        </div>
    </div>
</template>
