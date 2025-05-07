<script setup>
import { cva } from 'cva';
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
});

const classes = cva({
    base: [
        'min-w-0',
        /* When label exists but no description follows */
        '[&>[data-ui-label]:not(:has(+[data-ui-description]))]:mb-2',
        /* When label is followed by description */
        '*:data-ui-description:mb-2',
        /* Dim label when a child input control is disabled */
        '[&:not(:has([data-ui-field])):has([data-ui-control][disabled])>[data-ui-label]]:opacity-50',
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
    },
})({ ...props });
</script>

<template>
    <div :class="[classes, $attrs.class]" data-ui-input-group>
        <div class="flex items-center justify-between">
            <Label v-if="label" :text="label" :for="id" :required="required" :badge="badge" class="flex-1" />
            <slot name="actions" />
        </div>
        <Description :text="instructions" v-if="instructions && !instructionsBelow" />
        <slot />
        <Description :text="instructions" v-if="instructions && instructionsBelow" />
        <Description v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" class="text-red-500" />
    </div>
</template>
