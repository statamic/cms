<script setup>
import { useAttrs } from 'vue';
import { cva } from 'cva';
import { DropdownMenuContent, DropdownMenuPortal, DropdownMenuRoot, DropdownMenuTrigger } from 'reka-ui';
import Button from '../Button/Button.vue';

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const props = defineProps({
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: { type: String, default: 'start' },
    /** The distance in pixels from the trigger */
    offset: { type: Number, default: 5 },
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: { type: String, default: 'bottom' },
});

const dropdownContentClasses = cva({
    base: [
        'rounded-xl min-w-64 bg-gray-50 dark:bg-gray-800 outline-hidden overflow-hidden group z-50',
        'border border-gray-200 dark:border-black shadow-lg popoverAnimation',
    ],
})({});
</script>

<template>
    <DropdownMenuRoot>
        <DropdownMenuTrigger as-child data-ui-dropdown-trigger>
            <slot name="trigger">
                <Button icon="dots" variant="ghost" size="sm" v-bind="attrs" :aria-label="__('Open dropdown menu')" />
            </slot>
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
