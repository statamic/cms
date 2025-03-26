<script setup>
import { cva } from 'cva';

const props = defineProps({
    text: { type: String, default: null },
    variant: { type: String, default: 'line' },
    vertical: { type: Boolean, default: false },
});

const classes = cva({
    variants: {
        text: { true: 'before:me-4 after:ms-4 antialiased' },
        vertical: {
            true: 'inline-block h-full bg-gray-300 dark:bg-gray-600 self-center w-px',
            false: 'flex w-full items-center text-center text-gray-400 dark:text-gray-500 text-sm my-4 before:flex-1 after:flex-1'
        },
        variant: {
            line: 'before:bg-gray-300 after:bg-gray-300 before:h-px after:h-px dark:before:bg-gray-600 dark:after:bg-gray-600',
            dots: 'dotted-line',
        }
    }
})({
    text: !!props.text,
    variant: props.variant,
    vertical: props.vertical,
});

</script>

<template>
    <div :class="classes" v-text="text" data-ui-separator />
</template>

<style scoped>

.dotted-line::before,
.dotted-line::after {
    background: linear-gradient(90deg, var(--color-gray-400) 1.5px, transparent 1.5px) 50% 50% / 8px 1.5px repeat-x;
    height: 1.5px;
}

.dark .dotted-line::before,
.dark .dotted-line::after {
    background: linear-gradient(90deg, var(--color-gray-600) 1.5px, transparent 1.5px) 50% 50% / 8px 1.5px repeat-x;
    height: 1.5px;
}

</style>
