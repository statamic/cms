<script setup>
import { useAttrs } from 'vue';
import { cva } from 'cva';
import { ContextMenuContent, ContextMenuPortal, ContextMenuRoot, ContextMenuTrigger } from 'reka-ui';
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

const contextContentClasses = cva({
    base: [
        'rounded-xl w-64 bg-gray-50 dark:bg-gray-800 outline-hidden overflow-hidden group z-50',
        'border border-gray-200 dark:border-black shadow-lg popoverAnimation',
    ],
})({});
</script>

<template>
    <ContextMenuRoot>
        <ContextMenuTrigger data-ui-context-trigger>
            <slot name="trigger">
                <Button icon="ui/dots" variant="ghost" size="sm" v-bind="attrs" />
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
