<script setup>
import { cva } from 'cva';
import { HoverCardArrow, HoverCardContent, HoverCardPortal, HoverCardRoot, HoverCardTrigger } from 'reka-ui';
import { computed, getCurrentInstance, ref, watch } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const emit = defineEmits(['update:open']);

const props = defineProps({
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: { type: String, default: 'center' },
    /** When `true`, an arrow is displayed near the trigger. */
    arrow: { type: Boolean, default: true },
    /** The delay in milliseconds before the hover card opens. */
    delay: { type: Number, default: 200 },
    /** When `true`, the internal padding of the hover card is removed. */
    inset: { type: Boolean, default: false },
    /** The distance in pixels from the trigger. */
    offset: { type: Number, default: 25 },
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: { type: String, default: 'left' },
    /** The controlled open state of the hover card. */
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


const instance = getCurrentInstance();
const isUsingOpenProp = computed(() => instance?.vnode.props?.hasOwnProperty('open'));
const open = ref(props.open);
watch(
    () => props.open,
    (value) => open.value = value,
);
// When the parent component controls the open state, emit an update event
// so it can update its state, which eventually gets passed down as a prop.
// Otherwise, update the local state.
function updateOpen(value) {
    if (isUsingOpenProp.value) {
        emit('update:open', value);
        return;
    }

    open.value = value;
}
</script>

<template>
    <HoverCardRoot
        v-slot="slotProps"
        :open-delay="delay"
        :open="open"
        @update:open="updateOpen"
    >
        <HoverCardTrigger data-ui-hover-card-trigger as-child>
            <slot name="trigger" />
        </HoverCardTrigger>
        <HoverCardPortal v-if="$slots.default">
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
