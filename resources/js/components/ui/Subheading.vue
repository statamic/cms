<script setup>
import { cva } from 'cva';

const props = defineProps({
    size: { type: String, default: 'base' },
});

const classes = cva({
    base: 'text-gray-500 not-prose',
    variants: {
        size: {
            sm: 'text-xs',
            base: 'text-sm',
            lg: 'text-base',
            xl: 'text-lg',
        },
    },
})({ ...props });
</script>

<template>
    <div :class="classes" data-ui-subheading>
        <slot />
    </div>
</template>
