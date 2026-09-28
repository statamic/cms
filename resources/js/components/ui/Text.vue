<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';

const props = defineProps({
    /** The element this component should render as */
    as: { type: String, default: 'span' },
    /** Controls the size of the text. Options: `xs`, `sm`, `base`, `lg` */
    size: { type: String, default: 'base' },
    /** Text to display */
    text: { type: [String, Number, Boolean, null], default: null },
    /** Controls the appearance of the text. Options: `default`, `strong`, `subtle`, `code`, `danger`, `success`, `warning` */
    variant: { type: String, default: 'default' },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const textClasses = computed(() => {
    const classes = cva({
        base: 'antialiased',
        variants: {
            variant: {
                default: 'text-gray-900 dark:text-gray-50',
                strong: 'font-semibold text-gray-900 dark:text-gray-50',
                subtle: 'text-gray-600 dark:text-gray-600/90',
                code: 'font-mono text-[0.9em] text-gray-900 dark:text-gray-50 bg-gray-600/10 dark:bg-white/10 rounded-sm px-1 py-0.5',
                danger: 'text-red-600 dark:text-red-400',
                success: 'text-green-600 dark:text-green-400',
                warning: 'text-amber-600 dark:text-amber-400',
            },
            size: {
                xs: 'text-2xs',
                sm: 'text-xs',
                base: 'text-sm',
                lg: 'text-base',
            },
        },
    })({ ...props });

    return twMerge(classes);
});
</script>

<template>
    <component :is="as" :class="textClasses" data-ui-text>
        <slot v-if="hasDefaultSlot" />
        <template v-else>{{ text }}</template>
    </component>
</template>
