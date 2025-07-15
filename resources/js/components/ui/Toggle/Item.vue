<script setup>
import { computed, inject, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import { ToggleGroupItem } from 'reka-ui';
import { Icon } from '@statamic/ui';

const props = defineProps({
    value: { type: String, required: true },
    label: { type: String, default: null },
    icon: { type: String, default: null },
    iconOnly: { type: Boolean, default: false },
});

const variant = inject('toggleVariant', 'default');
const size = inject('toggleSize', 'base');

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const iconOnly = computed(() => (props.icon && !hasDefaultSlot && !props.text) || props.iconOnly);

const toggleItemClasses = computed(() => {
    const classes = cva({
        base: 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg font-medium antialiased cursor-pointer no-underline disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none [&_svg]:text-gray-500 [&_svg]:shrink-0',
        variants: {
            variant: {
                default: [
                    'bg-linear-to-b from-white to-gray-50 hover:to-gray-100 text-gray-900 border border-gray-300 shadow-ui-sm data-[state=on]:from-gray-100 data-[state=on]:to-gray-100 data-[state=on]:text-gray-900 data-[state=on]:inset-shadow-sm/10',
                    'dark:from-gray-800 dark:to-gray-850 dark:hover:to-gray-800 hover:bg-gray-50 dark:hover:bg-gray-850 dark:border-b-0 dark:ring dark:ring-black dark:border-white/15 dark:text-gray-300 dark:shadow-md dark:data-[state=on]:from-gray-950 dark:data-[state=on]:to-gray-950 dark:data-[state=on]:text-white',
                ],
                primary: [
                    'bg-linear-to-b from-primary/90 to-primary hover:bg-primary-hover text-white border border-primary-border shadow-ui-md inset-shadow-2xs inset-shadow-white/25 data-[state=on]:bg-primary-hover',
                    'dark:from-white dark:to-gray-200 dark:hover:from-gray-200 dark:text-gray-900 dark:border-0 dark:data-[state=on]:from-gray-300 dark:data-[state=on]:to-gray-300',
                ],
                filled: 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/80 dark:hover:bg-gray-700 data-[state=on]:bg-gray-300 data-[state=on]:border-gray-500 dark:data-[state=on]:bg-gray-950',
                ghost: 'bg-transparent rounded-lg hover:bg-gray-400/10 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700/80 dark:hover:text-gray-200 data-[state=on]:bg-gray-400/20 data-[state=on]:text-gray-700 dark:data-[state=on]:bg-gray-700/80 dark:data-[state=on]:text-white',
            },
            size: {
                base: 'px-3 h-10 text-sm [&_svg]:size-3.5',
                sm: 'px-2.5 h-8 text-[0.8125rem] [&_svg]:size-3',
                xs: 'px-2 h-6.5 text-xs [&_svg]:size-2.5',
            },
            groupBorder: {
                default:
                    '[[data-ui-toggle-group]_&]:border-s-0 [:is([data-ui-toggle-group]>&:first-child,_[data-ui-toggle-group]_:first-child>&)]:border-s-[1px]',
                primary:
                    '[[data-ui-toggle-group]_&]:border-e-0 [:is([data-ui-toggle-group]>&:last-child,_[data-ui-toggle-group]_:last-child>&)]:border-e-[1px] [:is([data-ui-toggle-group]>&:not(:first-child),_[data-ui-toggle-group]_:not(:first-child)>&)]:border-s-primary-gap',
                filled: '[[data-ui-toggle-group]_&]:border-e [:is([data-ui-toggle-group]>&:last-child,_[data-ui-toggle-group]_:last-child>&)]:border-e-0 [[data-ui-toggle-group]_&]:border-gray-300/70',
                ghost: '',
            },
        },
        compoundVariants: [
            { iconOnly: true, size: 'base', class: 'w-10 [&_svg]:size-4' },
            { iconOnly: true, size: 'sm', class: 'w-8 [&_svg]:size-3.5' },
            { iconOnly: true, size: 'xs', class: 'w-6.5 [&_svg]:size-3' },
        ],
    })({
        variant,
        size,
        groupBorder: variant,
        iconOnly,
    });

    return twMerge(classes);
});
</script>

<template>
    <ToggleGroupItem :value="value" :aria-label="label" :class="toggleItemClasses" data-ui-group-target>
        <Icon v-if="icon" :name="icon" class="text-gray-400" />
        <slot>{{ label }}</slot>
    </ToggleGroupItem>
</template>
