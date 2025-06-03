<script setup>
import { cva } from 'cva';

const props = defineProps({
    index: { type: Number, required: true },
    total: { type: Number, required: true },
});

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

const getPosition = (index) => {
    if (index === 0) return 'first';
    if (index === props.total - 1) return 'last';
    return 'left';
};

const getRightPosition = (index) => {
    if (index === 0) return 'firstRight';
    if (index === props.total - 1) return 'lastRight';
    return 'right';
};
</script>

<template>
    <tr>
        <slot :position="getPosition" :rightPosition="getRightPosition" />
    </tr>
</template>
