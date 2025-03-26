<script setup>
import { cva } from 'cva';
import {
    DropdownMenuContent,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuTrigger
} from 'reka-ui';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    align: { type: String, default: 'start' },
    offset: { type: Number, default: 5 },
    side: { type: String, default: 'bottom' },
});

const dropdownContentClasses = cva({
    base: [
        'rounded-xl w-64 bg-gray-50 dark:bg-gray-800 outline-none overflow-hidden group',
        'border border-gray-200 dark:border-black shadow-lg popoverAnimation'
    ],
})({});
</script>

<template>
<DropdownMenuRoot>
    <DropdownMenuTrigger data-ui-dropdown-trigger>
        <slot name="trigger" />
    </DropdownMenuTrigger>
    <DropdownMenuPortal>
        <DropdownMenuContent
            data-ui-dropdown-content
            :class="[dropdownContentClasses, $attrs.class]"
            :align
            :sideOffset="offset"
            :side
        >
            <slot />
        </DropdownMenuContent>
    </DropdownMenuPortal>
</DropdownMenuRoot>
</template>
