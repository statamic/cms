<script setup>
import { inject } from 'vue';
import { cva } from 'cva';

const variant = inject('dataTableVariant', 'normal');

const props = defineProps({
    align: { type: String, default: 'left' },
    position: { type: Function, required: true },
    rightPosition: { type: Function, required: true },
    index: { type: Number, required: true },
});

const bodyCellClasses = cva({
    base: '',
    variants: {
        variant: {
            normal: 'px-4 py-3',
            compact: 'px-2 py-1.5',
        },
    },
})({ variant });

const tableCellVariants = cva({
    base: 'border-t border-gray-200 dark:border-white/10',
    variants: {
        position: {
            first: 'border-l rounded-tl-xl',
            firstRight: 'border-r rounded-tr-xl',
            last: 'border-l rounded-bl-xl',
            lastRight: 'border-r rounded-br-xl',
            left: 'border-l',
            right: 'border-r',
            middle: '',
        },
    },
});
</script>

<template>
    <td
        :class="[
            bodyCellClasses,
            `text-${align}`,
            tableCellVariants({
                position: position(index),
            }),
        ]"
    >
        <slot />
    </td>
</template>
