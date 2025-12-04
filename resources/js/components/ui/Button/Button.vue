<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import Icon from '../Icon/Icon.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    as: { type: String, default: null },
    href: { type: String, default: null },
    target: { type: String, default: null },
    icon: { type: String, default: null },
    iconAppend: { type: String, default: null },
    iconOnly: { type: Boolean, default: false },
    inset: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    round: { type: Boolean, default: false },
    size: { type: String, default: 'base' },
    text: { type: [String, Number, Boolean, null], default: null },
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'default' },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const tag = computed(() => {
    if (props.as) return props.as;
    if (props.href) {
        return props.target === '_blank' ? 'a' : Link;
    }
    return 'button';
});
const iconOnly = computed(() => (props.icon && !hasDefaultSlot && !props.text) || props.iconOnly);

const buttonClasses = computed(() => {
    const classes = cva({
        base: 'relative inline-flex items-center justify-center whitespace-nowrap shrink-0 font-medium antialiased cursor-pointer no-underline disabled:text-gray-400 dark:disabled:text-gray-600 disabled:[&_svg]:opacity-30 disabled:cursor-not-allowed [&_svg]:shrink-0 [&_svg]:text-black [&_svg]:opacity-60 dark:[&_svg]:text-white',
        variants: {
            variant: {
                default: [
                    'bg-linear-to-b from-white to-gray-50 hover:to-gray-100 hover:bg-gray-50 text-gray-900 border border-gray-300 shadow-ui-sm',
                    'dark:from-gray-850 dark:to-gray-900 dark:hover:to-gray-850 dark:hover:bg-gray-900 dark:border-gray-700/80 dark:text-gray-300 dark:shadow-ui-md',
                ],
                primary: [
                    'bg-linear-to-b from-primary/90 to-primary hover:bg-primary-hover text-white disabled:text-white/60 dark:disabled:text-white/50 border border-primary-border shadow-ui-md inset-shadow-2xs inset-shadow-white/25 [&_svg]:text-white [&_svg]:opacity-60',
                ],
                danger: 'bg-linear-to-b from-red-700/90 to-red-700 hover:bg-red-700/90 text-white border border-red-700 inset-shadow-xs inset-shadow-red-300 [&_svg]:text-red-200 disabled:text-red-200',
                filled: 'bg-black/5 hover:bg-black/10 hover:text-gray-900 dark:hover:text-white dark:bg-white/15 dark:hover:bg-white/20 [&_svg]:opacity-70',
                ghost: 'bg-transparent hover:bg-gray-400/10 text-gray-900 dark:text-gray-300 dark:hover:bg-white/7 dark:hover:text-gray-200',
                'ghost-pressed': 'bg-transparent hover:bg-gray-400/10 text-black dark:text-white dark:hover:bg-white/7 dark:hover:text-white [&_svg]:opacity-100',
                subtle: 'bg-transparent hover:bg-gray-400/10 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-white/7 dark:hover:text-gray-200 [&_svg]:opacity-35',
                pressed: [
                    'bg-linear-to-b from-gray-200 to-gray-150 text-gray-900 border border-gray-300 inset-shadow-sm/10',
                    'dark:from-black dark:to-black dark:text-white dark:border-gray-700/80',
                ],
            },
            size: {
                lg: 'px-6 h-12 text-base gap-2 rounded-lg text-base',
                base: 'px-4 h-10 text-sm gap-2 rounded-lg',
                sm: 'px-3 h-8 text-[0.8125rem] leading-tight gap-2 rounded-lg [&_svg]:size-3',
                xs: 'px-2 h-6 text-xs gap-1.5 rounded-md [&_svg]:size-2.5',
                '2xs': 'px-1.5 h-5 text-xs gap-1 rounded-md [&_svg]:size-2',
            },
            groupBorder: {
                danger: [
                    'in-data-ui-button-group:text-red-500 in-data-ui-button-group:bg-linear-to-b in-data-ui-button-group:from-white in-data-ui-button-group:to-red-50 in-data-ui-button-group:hover:to-gray-100 in-data-ui-button-group:hover:bg-gray-50 in-data-ui-button-group:border in-data-ui-button-group:border-gray-300 in-data-ui-button-group:shadow-ui-sm in-data-ui-button-group:inset-shadow-none',
                    'dark:in-data-ui-button-group:text-red-500 dark:in-data-ui-button-group:from-gray-850 dark:in-data-ui-button-group:to-red-900/10 dark:in-data-ui-button-group:hover:to-gray-850 dark:in-data-ui-button-group:hover:bg-gray-900 dark:in-data-ui-button-group:border-gray-700/80 dark:in-data-ui-button-group:shadow-ui-md',
                ],
                ghost: '',
                pressed: 'in-data-ui-button-group:border-s-0 [:is([data-ui-button-group]>&:first-child,_[data-ui-button-group]_:first-child>&)]:border-s-[1px]',
            },
            iconOnly: { true: 'px-0 gap-0 hover:[&_svg]:opacity-70' },
            round: { true: 'rounded-full' },
        },
        compoundVariants: [
            { iconOnly: true, size: 'lg', class: 'w-12 [&_svg]:size-5' },
            { iconOnly: true, size: 'base', class: 'w-10 [&_svg]:size-4.5' },
            { iconOnly: true, size: 'sm', class: 'w-8 [&_svg]:size-3.5' },
            { iconOnly: true, size: 'xs', class: 'size-6.5 [&_svg]:size-3' },
            { iconOnly: true, size: '2xs', class: 'size-5 [&_svg]:size-2.5' },
            { iconOnly: true, variant: 'pressed', class: '[&_svg]:!opacity-70 dark:[&_svg]:!opacity-100' },
            { iconOnly: false, iconAppend: true, class: '[&_svg]:-me-1' },
            { iconOnly: false, iconPrepend: true, class: '[&_svg]:-ms-0.5' },
            { inset: true, size: 'lg', class: '-m-1.5' },
            { inset: true, size: 'base', class: '-m-1' },
            { inset: true, size: 'sm', class: '-m-0.75' },
            { inset: true, size: 'xs', class: '-m-0.25' },
            { inset: true, size: '2xs', class: '-m-0.125' },
        ],
    })({
        ...props,
        groupBorder: props.variant,
        iconAppend: !!props.iconAppend,
        iconPrepend: !!props.icon && !iconOnly.value,
        iconOnly: iconOnly.value,
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
        :target
        :type="props.href ? null : type"
    >
        <Icon v-if="icon" :name="icon" />
        <Icon v-if="loading" name="loading" :size />

        <div :class="{ 'st-text-trim-start': size !== 'xs' && size !== 'sm' }" class="flex content-center">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>

        <Icon v-if="iconAppend" :name="iconAppend" />
    </component>
</template>
