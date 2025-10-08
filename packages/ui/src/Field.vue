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
    id: { type: String },
    instructions: { type: String, default: '' },
    instructionsBelow: { type: Boolean, default: false },
    label: { type: String },
    readOnly: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    variant: { type: String, default: 'block' },
});

const labelProps = computed(() => ({
    badge: props.badge,
    for: props.id,
    required: props.required,
    text: props.label,
}));

const inline = computed(() => props.asConfig ? true : props.variant === 'inline');

const classes = computed(() =>
    cva({
        base: [
            'min-w-0',
        ],
        variants: {
            variant: {
                block: 'w-full',
                inline: [
                    'flex justify-between gap-x-7 gap-y-1.5',
                    'has-[[data-ui-label]~[data-ui-control]]:grid-cols-[1fr_auto]',
                    'has-[[data-ui-control]~[data-ui-label]]:grid-cols-[auto_1fr]',
                    '[&>[data-ui-control]~[data-ui-description]]:row-start-2 [&>[data-ui-control]~[data-ui-description]]:col-start-2',
                    '[&>[data-ui-label]~[data-ui-control]]:row-start-1 [&>[data-ui-label]~[data-ui-control]]:col-start-2',
                ],
            },
            disabled: {
                true: 'opacity-50',
            },
            asConfig: {
                true: 'grid grid-cols-2 items-start px-4.5 py-4 gap-x-5!',
            },
        },
    })({
        ...props,
        inline: inline.value,
        asConfig: props.asConfig,
    }),
);

const instructions = computed(() => props.instructions ? markdown(__(props.instructions), { openLinksInNewTabs: true }) : null);

const errors = computed(() => {
    if (props.error) {
        return [props.error];
    }

    return props.errors;
});
</script>

<template>
    <div :class="[classes, $attrs.class]" data-ui-input-group data-marps>
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
                <Description :text="instructions" v-if="instructions && !instructionsBelow" :class="inline ? '-mt-0.5' : 'mb-2 -mt-0.5'" />
            </div>
        <slot />
        <div>
            <Description :text="instructions" v-if="instructions && instructionsBelow" class="mt-2" />
            <ErrorMessage v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" class="mt-2" />
        </div>
    </div>
</template>
