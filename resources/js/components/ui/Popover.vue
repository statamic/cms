<script setup>
import { cva } from 'cva';
import { PopoverArrow, PopoverContent, PopoverPortal, PopoverRoot, PopoverTrigger } from 'reka-ui';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    align: { type: String, default: 'center' },
    arrow: { type: Boolean, default: false },
    inset: { type: Boolean, default: false },
    offset: { type: Number, default: 5 },
    side: { type: String, default: 'bottom' },
});

const popoverContentClasses = cva({
    base: [
        'rounded-xl w-64 bg-white dark:bg-gray-800 outline-none overflow-hidden',
        'border border-gray-200 dark:border-white/15 dark:border-b-0 shadow-lg',
        'duration-100 will-change-[transform,opacity]',
        'data-[state=open]:animate-in data-[state=closed]:animate-out',
        'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
        'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
        'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2',
        'data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2'
    ],
    variants: {
        inset: { true: 'inset-0', false: 'p-4' }
    }
})({
    ...props,
});
</script>

<template>
<PopoverRoot>
    <PopoverTrigger data-ui-popover-trigger>
        <slot name="trigger" />
    </PopoverTrigger>
    <PopoverPortal>
        <PopoverContent
            data-ui-popover-content
            :class="[popoverContentClasses, $attrs.class]"
            :align
            :sideOffset="offset"
            :side
        >
            <slot />
            <PopoverArrow v-if="arrow" class="fill-white stroke-gray-300" />
        </PopoverContent>
    </PopoverPortal>
</PopoverRoot>
</template>
