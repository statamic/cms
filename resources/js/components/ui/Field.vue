<script setup>
import { cva } from 'cva';
import { computed } from 'vue';
import Description from './Description.vue';
import Label from './Label.vue';
import ErrorMessage from './ErrorMessage.vue';
import markdown from '@/util/markdown.js';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    asConfig: { type: Boolean, default: false },
    badge: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    error: { type: String },
    errors: { type: Object },
    fullWidthSetting: { type: Boolean, default: false },
    id: { type: String },
    instructions: { type: String, default: '' },
    instructionsBelow: { type: Boolean, default: false },
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

const inline = computed(() => props.asConfig);

const rootClasses = computed(() =>
    cva({
        base: [
            'min-w-0',
        ],
        variants: {
            disabled: {
                true: 'opacity-50',
            },
            inline: {
                true: 'grid grid-cols-2 items-start px-4.5 py-4 gap-x-5!',
            },
            fullWidthSetting: {
                true: '!grid-cols-1',
            },
        },
    })({
        ...props,
        inline: inline.value,
        fullWidthSetting: props.fullWidthSetting,
    }),
);

const descriptionClasses = computed(() =>
    cva({
        base: ['mb-2 -mt-0.5'],
        variants: {
            inline: {
                true: 'mb-0!',
            },
            fullWidth: {
                true: 'mb-3!',
            },
        },
    })({
        ...props,
        inline: inline.value,
        fullWidth: props.fullWidthSetting,
    }),
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
    <div :class="[rootClasses, $attrs.class]" data-ui-input-group :data-ui-field-has-errors="hasErrors ? '' : null">
        <div v-if="label || (instructions && !instructionsBelow) || $slots.label || $slots.actions">
            <div
                v-if="$slots.actions"
                :class="[
                    'flex items-center gap-x-1 mb-0',
                    props.label || $slots.label ? 'justify-between' : 'justify-end',
                ]"
                data-ui-field-header
            >
                <slot name="label">
                    <Label v-if="label" v-bind="labelProps" class="flex-1" />
                </slot>
                <slot name="actions" />
            </div>
            <div v-if="label || (instructions && !instructionsBelow) || ($slots.label && !$slots.actions)" data-ui-field-text :class="inline ? 'mb-0' : 'mb-1.5'">
                <slot v-if="!$slots.actions" name="label">
                    <Label v-if="label" v-bind="labelProps" class="flex-1" />
                </slot>
                <Description :text="instructions" v-if="instructions && !instructionsBelow" :class="descriptionClasses" />
            </div>
        </div>
        <slot />
        <div v-if="(instructions && instructionsBelow) || hasErrors">
            <Description :text="instructions" v-if="instructions && instructionsBelow" class="mt-2" />
            <ErrorMessage v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" class="mt-2" />
        </div>
    </div>
</template>
