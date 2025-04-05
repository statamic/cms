<script setup>
import { inject } from 'vue';

import { cva } from 'cva';
const variant = inject('dataTableVariant', 'normal');

const props = defineProps({
    align: { type: String, default: 'left' },
    width: { type: String, default: null },
});

const headerCellClasses = cva({
    base: 'text-sm font-medium text-gray-800 dark:text-gray-300',
    variants: {
        variant: {
            normal: 'px-4 py-2',
            compact: 'px-2 py-1.5',
        },
    },
})({ variant });
</script>

<template>
    <th :class="[headerCellClasses, `text-${align}`, width]" :style="width ? { width } : null">
        <slot />
    </th>
</template>
