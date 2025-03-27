<script setup>
import { cva } from 'cva';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    variant: { type: String, default: 'block' },
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
        <slot />
    </div>
</template>
