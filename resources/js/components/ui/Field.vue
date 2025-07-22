<script setup>
import { cva } from 'cva';
import { computed } from 'vue';
import { Description, Label, Card } from '@statamic/components/ui/index.js';
import markdown from '@statamic/util/markdown.js';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    as: { type: String, default: 'div', validator: (value) => ['div', 'card'].includes(value) },
    badge: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    readOnly: { type: Boolean, default: false },
    error: { type: String },
    errors: { type: Object, default: (props) => (props.error ? [props.error] : []) },
    id: { type: String },
    instructions: { type: String, default: '' },
    instructionsBelow: { type: Boolean, default: false },
    label: { type: String },
    required: { type: Boolean, default: false },
    variant: { type: String, default: 'block' },
});

const labelProps = computed(() => ({
    badge: props.badge,
    for: props.id,
    required: props.required,
    text: props.label,
}));

const classes = computed(() =>
    cva({
        base: [
            'min-w-0',
        ],
        variants: {
            variant: {
                block: 'w-full',
                inline: [
                    'flex justify-between gap-x-3 gap-y-1.5',
                    'has-[[data-ui-label]~[data-ui-control]]:grid-cols-[1fr_auto]',
                    'has-[[data-ui-control]~[data-ui-label]]:grid-cols-[auto_1fr]',
                    '[&>[data-ui-control]~[data-ui-description]]:row-start-2 [&>[data-ui-control]~[data-ui-description]]:col-start-2',
                    '[&>[data-ui-label]~[data-ui-control]]:row-start-1 [&>[data-ui-label]~[data-ui-control]]:col-start-2',
                ],
            },
            disabled: {
                true: 'opacity-50',
            },
        },
    })({ ...props }),
);

const instructions = computed(() => props.instructions ? markdown(props.instructions, { openLinksInNewTabs: true }) : null);
const wrapperComponent = computed(() => props.as === 'card' ? Card : 'div');
</script>

<template>
    <component :is="wrapperComponent" :class="[classes, $attrs.class]" data-ui-input-group>
        <div
            v-if="$slots.actions"
            :class="[
                'flex items-center gap-x-1',
                props.label || $slots.label ? 'justify-between' : 'justify-end',
            ]"
            data-ui-field-header
        >
            <slot name="label">
                <Label v-if="label" v-bind="labelProps" class="flex-1" />
            </slot>
            <slot name="actions" />
        </div>
        <div v-if="label || (instructions && !instructionsBelow) || ($slots.label && !$slots.actions)" data-ui-field-text class="mb-1.5">
            <slot v-if="!$slots.actions" name="label">
                <Label v-if="label" v-bind="labelProps" class="flex-1" />
            </slot>
            <Description :text="instructions" v-if="instructions && !instructionsBelow" class="mb-1.75 -mt-0.5" />
        </div>
        <slot />
        <Description :text="instructions" v-if="instructions && instructionsBelow" class="mt-2" />
        <Description v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" class="mt-2 text-red-500" />
    </component>
</template>
