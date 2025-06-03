<script setup>
import { cva } from 'cva';
import { computed } from 'vue';
import { Description, Label } from '@statamic/components/ui/index.js';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    variant: {
        type: String,
        default: 'block',
    },
    label: {
        type: String,
    },
    id: {
        type: String,
    },
    instructions: {
        type: String,
        default: '',
    },
    instructionsBelow: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    badge: {
        type: String,
        default: '',
    },
    error: {
        type: String,
    },
    errors: {
        type: Object,
        default: (props) => (props.error ? [props.error] : []),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const labelProps = computed(() => ({
    text: props.label,
    for: props.id,
    required: props.required,
    badge: props.badge,
}));

const classes = computed(() =>
    cva({
        base: [
            'min-w-0',
            /* When label exists but no description follows */
            '[&>[data-ui-label]:not(:has(+[data-ui-description]))]:mb-2',
            /* When label is followed by description */
            '*:data-ui-description:mb-2',
        ],
        variants: {
            variant: {
                block: 'w-full',
                inline: [
                    'grid gap-x-3 gap-y-1.5',
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
</script>

<template>
    <div :class="[classes, $attrs.class]" data-ui-input-group>
        <div v-if="$slots.actions" class="mb-2 flex items-center justify-between gap-x-1">
            <slot name="label">
                <Label v-if="label" v-bind="labelProps" class="flex-1" />
            </slot>
            <slot name="actions" />
        </div>
        <slot v-if="!$slots.actions" name="label">
            <Label v-if="label" v-bind="labelProps" class="flex-1" />
        </slot>
        <Description :text="instructions" v-if="instructions && !instructionsBelow" />
        <slot />
        <Description :text="instructions" v-if="instructions && instructionsBelow" class="mt-2" />
        <Description v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" class="mt-2 text-red-500" />
    </div>
</template>
