<script setup>
import { DropdownMenuItem } from 'reka-ui';
import { computed, useSlots } from 'vue';
import Icon from '../Icon/Icon.vue';
import { cva } from 'cva';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    as: { type: String, default: null },
    href: { type: String, default: null },
    target: { type: String, default: '_self' },
    icon: { type: String, default: null },
    text: { type: String, default: null },
    variant: { type: String, default: 'default' },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const tag = computed(() => {
    if (props.as) return props.as;
    if (! props.href) return 'div';
    return props.target === '_blank' ? 'a' : Link;
});

const classes = cva({
    base: 'col-span-2 grid grid-cols-subgrid items-center rounded-lg px-1 py-1.5 text-sm antialiased text-gray-900 dark:text-gray-300 not-data-disabled:cursor-pointer data-disabled:opacity-50 hover:not-data-disabled:bg-gray-100 dark:hover:not-data-disabled:bg-gray-800 outline-hidden',
    variants: {
        variant: {
            default: 'text-gray-700 dark:text-gray-300',
            destructive: 'text-red-600',
        },
    },
})({ variant: props.variant });

const iconClasses = cva({
    variants: {
        base: 'size-3.5!',
        variant: {
            default: 'text-gray-500',
            destructive: 'text-red-500!',
        },
    },
})({ variant: props.variant });

</script>

<template>
    <DropdownMenuItem
        :class="classes"
        data-ui-dropdown-item
        :as="tag"
        :href
        :target
    >
        <div v-if="icon" class="flex size-6 items-center justify-center p-1">
            <Icon :name="icon" :class="iconClasses" />
        </div>
        <div class="col-start-2 ps-2">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>
    </DropdownMenuItem>
</template>
