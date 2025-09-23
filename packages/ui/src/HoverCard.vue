<script setup>
import { cva } from 'cva';
import { HoverCardArrow, HoverCardContent, HoverCardPortal, HoverCardRoot, HoverCardTrigger } from 'reka-ui';
import { ref, watch } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    align: { type: String, default: 'center' },
    arrow: { type: Boolean, default: true },
    delay: { type: Number, default: 200 },
    inset: { type: Boolean, default: false },
    offset: { type: Number, default: 25 },
    side: { type: String, default: 'left' },
    open: { type: Boolean, default: false },
});

const HoverCardContentClasses = cva({
    base: [
        'rounded-xl bg-white dark:bg-gray-800 outline-hidden overflow-hidden',
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

</script>

<template>
    <HoverCardRoot v-slot="slotProps" :open-delay="delay">
        <HoverCardTrigger data-ui-hover-card-trigger as-child>
            <slot name="trigger" />
        </HoverCardTrigger>
        <HoverCardPortal>
            <HoverCardContent
                data-ui-hover-card-content
                :class="[HoverCardContentClasses, $attrs.class]"
                :align
                :sideOffset="offset"
                :side
            >
                <slot v-bind="slotProps" />
                <HoverCardArrow v-if="arrow" class="fill-white stroke-gray-300" />
            </HoverCardContent>
        </HoverCardPortal>
    </HoverCardRoot>
</template>

<style>
[data-ui-hover-card-content] {
    max-height: var(--reka-hover-card-content-available-height);
    transform-origin: var(--reka-hover-card-content-transform-origin);
}
</style>
