<script setup>
import { cva } from 'cva';

const props = defineProps({
    size: { type: String, default: 'base' },
    text: { type: [String, Number, Boolean, null], default: null },
});

const classes = cva({
    base: 'text-gray-600 dark:text-gray-400 [&_code]:text-xs [&_code]:bg-gray-600/10 [&_code]:rounded-sm [&_code]:px-1 [&_code]:py-0.5',
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
        <slot v-if="!text" />
        <span v-else v-html="text" />
    </div>
</template>
