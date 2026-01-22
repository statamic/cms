<script setup>
import { useAttrs } from 'vue';
import { cva } from 'cva';
import { ContextMenuContent, ContextMenuPortal, ContextMenuRoot, ContextMenuTrigger } from 'reka-ui';
import Button from '../Button/Button.vue';

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const props = defineProps({
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: { type: String, default: 'start' },
    /** The distance in pixels from the trigger. */
    offset: { type: Number, default: 5 },
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: { type: String, default: 'bottom' },
});

const contextContentClasses = cva({
    base: [
        'rounded-xl w-64 bg-gray-50 dark:bg-gray-800 outline-hidden overflow-hidden group z-50',
        'border border-gray-200 dark:border-black shadow-lg popoverAnimation',
    ],
})({});
</script>

<template>
    <ContextMenuRoot>
        <ContextMenuTrigger as-child data-ui-context-trigger>
            <slot name="trigger">
                <Button icon="dots" variant="ghost" size="sm" v-bind="attrs" :aria-label="__('Open context menu')" />
            </slot>
        </ContextMenuTrigger>
        <ContextMenuPortal>
            <ContextMenuContent
                data-ui-context-content
                :class="[contextContentClasses, $attrs.class]"
                :align
                :sideOffset="offset"
                :side
            >
                <slot />
            </ContextMenuContent>
        </ContextMenuPortal>
    </ContextMenuRoot>
</template>
