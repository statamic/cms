<script setup>
import { cva } from 'cva';
import { PopoverArrow, PopoverClose, PopoverContent, PopoverPortal, PopoverRoot, PopoverTrigger } from 'reka-ui';
import { ref, watch } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const emit = defineEmits(['update:open']);

const props = defineProps({
    /** The preferred alignment against the trigger. May change when collisions occur. Options: `start`, `center`, `end` */
    align: { type: String, default: 'center' },
    /** When `true`, an arrow is displayed near the trigger. */
    arrow: { type: Boolean, default: false },
    /** When `true`, the internal padding of the popover is removed. */
    inset: { type: Boolean, default: false },
    /** The distance in pixels from the trigger */
    offset: { type: Number, default: 5 },
    /** The preferred side of the trigger to render against when open. Options: `top`, `bottom`, `left`, `right` */
    side: { type: String, default: 'bottom' },
    /** The controlled open state of the popover. */
    open: { type: Boolean, default: false },
});

const popoverContentClasses = cva({
    base: [
        'rounded-xl w-64 bg-white dark:bg-gray-800 outline-hidden',
        'border border-gray-200 dark:border-white/10 dark:border-b-0 shadow-lg',
        'duration-100 will-change-[transform,opacity]',
        'data-[state=open]:animate-in data-[state=closed]:animate-out',
        'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
        'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
        'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2',
        'data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
    ],
    variants: {
        inset: { true: 'inset-0', false: 'p-4' },
    },
})({
    ...props,
});

const open = ref(props.open);

watch(
    () => props.open,
    (value) => open.value = value,
);

function updateOpen(value) {
    emit('update:open', value);
    open.value = value;
}
</script>

<template>
    <PopoverRoot :open @update:open="updateOpen" v-slot="slotProps">
        <PopoverTrigger data-ui-popover-trigger as-child>
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
                <slot v-bind="slotProps" />
                <PopoverClose as-child>
                    <slot name="close" v-bind="slotProps" />
                </PopoverClose>
                <PopoverArrow v-if="arrow" class="fill-white dark:fill-gray-800 stroke-gray-300 dark:stroke-gray-700" />
            </PopoverContent>
        </PopoverPortal>
    </PopoverRoot>
</template>
