<script setup>
import { cva } from 'cva';

const props = defineProps({
    inset: { type: Boolean, default: false },
    variant: { type: String, default: 'default' },
});

const classes = cva({
    base: 'bg-white dark:bg-gray-850 rounded-xl border border-gray-200 dark:border-x-0 dark:border-b-0 dark:border-white/15',
    variants: {
        variant: {
            default: 'shadow-ui-s',
            flat: 'shadow-none',
        },
        inset: { false: 'px-4.5 py-5' },
    },
})({ ...props });
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
