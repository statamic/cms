<script setup>
import { cva } from 'cva';

const props = defineProps({
    size: { type: String, default: 'base' },
    text: { type: [String, Number, Boolean, null], default: null },
    icon: { type: String, default: null },
});

const classes = cva({
    base: 'text-gray-500 text-balance tracking-tight dark:text-gray-400 [&_code]:text-xs [&_code]:bg-gray-600/10 [&_code]:rounded-sm [&_code]:px-1 [&_code]:py-0.5',
    variants: {
        size: {
            sm: 'text-xs',
            base: 'text-sm',
            lg: 'text-base',
            xl: 'text-lg',
        },
        icon: {
            true: 'flex items-center gap-2',
        },
    },
})({ ...props, icon: props.icon ? true : false });
</script>

<template>
    <div :class="classes" data-ui-subheading>
        <ui-icon v-if="icon" :name="icon" class="size-2 text-gray-400 dark:text-gray-600" />
        <slot v-if="!text" />
        <span v-else v-html="text" />
    </div>
</template>
