<script setup>
import { cva } from 'cva';

const props = defineProps({
    inset: { type: Boolean, default: false },
    variant: { type: String, default: 'default' },
});

const classes = cva({
    base: 'bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700/75',
    variants: {
        variant: {
            default: 'shadow-ui-md',
            flat: 'shadow-none',
        },
        inset: { false: 'px-4.5 py-5 space-y-2' },
    },
})({ ...props });
</script>

<template>
    <div :class="classes" data-ui-card :data-inset="inset">
        <slot />
    </div>
</template>
