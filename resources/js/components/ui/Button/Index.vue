<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';

const props = defineProps({
    href: {type: String, default: null},
    icon: {type: String, default: null},
    iconAppend: {type: String, default: null},
    loading: {type: Boolean, default: false},
    round: {type: Boolean, default: false},
    size: {type: String, default: 'base'},
    text: {type: String, default: null},
    type: {type: String, default: 'button' },
    variant: {type: String, default: 'default' },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const tag = computed(() => props.href ? 'a' : 'button');
const iconOnly = computed(() => props.icon && !hasDefaultSlot && !props.text);

const buttonClasses = computed(() => {
    const classes = cva({
        base: 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg font-medium antialiased cursor-pointer no-underline disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none [&_svg]:shrink-0',
        variants: {
            variant: {
                default: [
                    'bg-linear-to-b from-white to-gray-50 hover:to-gray-100 text-gray-800 border border-gray-300 shadow-ui-sm',
                    'dark:from-gray-800 dark:to-gray-850 dark:hover:to-gray-800 hover:bg-gray-50 dark:hover:bg-gray-850 dark:border-b-0 dark:ring dark:ring-black dark:border-white/15 dark:text-gray-300 dark:shadow-md'
                ],
                primary: [
                    'bg-linear-to-b from-primary/90 to-primary hover:bg-primary-hover text-white border border-primary-border shadow-ui-md inset-shadow-2xs inset-shadow-white/25',
                    'dark:from-white dark:to-gray-200 dark:hover:from-gray-200 dark:text-gray-800 dark:border-0'
                ],
                danger: 'bg-linear-to-b from-red-500/90 to-red-500 hover:bg-red-500/90 text-white border border-red-600 inset-shadow-2xs inset-shadow-red-300 [&_svg]:text-red-200',
                filled: 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/80 dark:hover:bg-gray-700',
                ghost: 'bg-transparent hover:bg-gray-400/10 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700/80 dark:hover:text-gray-200',
            },
            size: {
                base: 'px-4 h-10 text-sm',
                sm: 'px-3 h-8 text-[0.8125rem]',
                xs: 'px-2 h-6.5 text-xs',
            },
            groupBorder: {
                default: '[[data-ui-button-group]_&]:border-s-0 [:is([data-ui-button-group]>&:first-child,_[data-ui-button-group]_:first-child>&)]:border-s-[1px]',
                primary: '[[data-ui-button-group]_&]:border-e-0 [:is([data-ui-button-group]>&:last-child,_[data-ui-button-group]_:last-child>&)]:border-e-[1px] [:is([data-ui-button-group]>&:not(:first-child),_[data-ui-button-group]_:not(:first-child)>&)]:border-s-primary-gap',
                danger: '[[data-ui-button-group]_&]:border-s-0 [[data-ui-button-group]_&]:border-e [:is([data-ui-button-group]>&:last-child,_[data-ui-button-group]_:last-child>&)]:border-e-0 [[data-ui-button-group]_&]:border-red-600',
                filled: '[[data-ui-button-group]_&]:border-e [:is([data-ui-button-group]>&:last-child,_[data-ui-button-group]_:last-child>&)]:border-e-0 [[data-ui-button-group]_&]:border-gray-300/70',
                ghost: '',
            },
            iconOnly: { true: 'px-0' },
            round: { true: 'rounded-full'},
        },
        compoundVariants: [
            { iconOnly: true, size: 'base', class: 'w-10 [&_svg]:size-4' },
            { iconOnly: true, size: 'sm', class: 'w-8 [&_svg]:size-3.5' },
            { iconOnly: true, size: 'xs', class: 'w-6.5 [&_svg]:size-3' },
        ],
    })({
        ...props,
        groupBorder: props.variant,
        iconOnly: iconOnly.value
    });

    return twMerge(classes);
});
</script>

<template>
    <component
        :is="tag"
        :class="buttonClasses"
        :disabled="loading"
        :data-ui-group-target="['subtle', 'ghost'].includes(props.variant) ? null : true"
        :href
        :type="props.href ? null : type"
    >
        <ui-icon v-if="icon" :name="icon" class="text-gray-400" />
        <ui-icon v-if="loading" name="loading" :size />

        <slot v-if="hasDefaultSlot" />
        <template v-else>{{ text }}</template>

        <ui-icon v-if="iconAppend" :name="iconAppend" />
    </component>
</template>
