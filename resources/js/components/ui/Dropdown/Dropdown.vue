<script setup>
import { useAttrs } from 'vue';
import { cva } from 'cva';
import { DropdownMenuContent, DropdownMenuPortal, DropdownMenuRoot, DropdownMenuTrigger } from 'reka-ui';
import { Button } from '@statamic/ui';

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const props = defineProps({
    align: { type: String, default: 'start' },
    offset: { type: Number, default: 5 },
    side: { type: String, default: 'bottom' },
});

const dropdownContentClasses = cva({
    base: [
        'rounded-xl w-64 bg-gray-50 dark:bg-gray-800 outline-hidden overflow-hidden group z-50',
        'border border-gray-200 dark:border-black shadow-lg popoverAnimation',
    ],
})({});
</script>

<template>
    <DropdownMenuRoot>
        <DropdownMenuTrigger as-child data-ui-dropdown-trigger>
            <slot name="trigger">
                <Button icon="ui/dots" variant="ghost" size="sm" v-bind="attrs" :aria-label="__('Open dropdown menu')" />
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
