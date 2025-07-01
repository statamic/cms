<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import { Icon } from '@statamic/ui';

const props = defineProps({
    href: { type: String, default: null },
    icon: { type: String, default: null },
    iconAppend: { type: String, default: null },
    iconOnly: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    round: { type: Boolean, default: false },
    size: { type: String, default: 'base' },
    text: { type: [String, Number, Boolean, null], default: null },
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'default' },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const tag = computed(() => (props.href ? 'a' : 'button'));
const iconOnly = computed(() => (props.icon && !hasDefaultSlot && !props.text) || props.iconOnly);

const buttonClasses = computed(() => {
    const classes = cva({
        base: 'inline-flex items-center justify-center whitespace-nowrap shrink-0 font-medium antialiased cursor-pointer no-underline disabled:text-gray-400 dark:disabled:text-gray-600 disabled:cursor-not-allowed [&_svg]:shrink-0 [&_svg]:text-gray-500',
        variants: {
            variant: {
                default: [
                    'bg-linear-to-b from-white to-gray-50 hover:to-gray-100 text-gray-800 border border-gray-300 shadow-ui-sm',
                    'dark:from-gray-800 dark:to-gray-850 dark:hover:to-gray-800 hover:bg-gray-50 dark:hover:bg-gray-850 dark:border-b-0 dark:ring-3 dark:ring-black dark:border-white/15 dark:text-gray-300 dark:shadow-md',
                ],
                primary: [
                    'bg-linear-to-b from-primary/90 to-primary hover:bg-primary-hover text-white border border-primary-border shadow-ui-md inset-shadow-2xs inset-shadow-white/25 [&_svg]:text-gray-400',
                    'dark:from-white dark:to-gray-200 dark:hover:from-gray-200 dark:text-gray-800 dark:border-0',
                ],
                danger: 'bg-linear-to-b from-red-500/90 to-red-500 hover:bg-red-500/90 text-white border border-red-600 inset-shadow-2xs inset-shadow-red-300 [&_svg]:text-red-200 disabled:text-red-200',
                filled: 'bg-gray-100 hover:bg-gray-200 hover:text-gray-900 dark:bg-gray-700/80 dark:hover:bg-gray-700 [&_svg]:text-gray-700 dark:[&_svg]:text-gray-300',
                ghost: 'bg-transparent hover:bg-gray-400/10 text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/80 dark:hover:text-gray-200',
                subtle: 'bg-transparent hover:bg-gray-400/10 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700/80 dark:hover:text-gray-200 [&_svg]:text-gray-400 dark:[&_svg]:text-gray-300',
            },
            size: {
                lg: 'px-6 h-12 text-base gap-2 rounded-lg text-base',
                base: 'px-4 h-10 text-sm gap-2 rounded-lg',
                sm: 'px-3 h-8 text-[0.8125rem] leading-none gap-2 rounded-lg [&_svg]:size-2.5',
                xs: 'px-2 h-6.5 text-xs gap-1.5 rounded-md [&_svg]:size-2.5',
            },
            groupBorder: {
                default:
                    'in-data-ui-button-group:border-s-0 [:is([data-ui-button-group]>&:first-child,_[data-ui-button-group]_:first-child>&)]:border-s-[1px]',
                primary:
                    'in-data-ui-button-group:border-s-0 [:is([data-ui-button-group]>&:first-child,_[data-ui-button-group]_:first-child>&)]:border-s-[1px] [:is([data-ui-button-group]>&:last-child,_[data-ui-button-group]_:last-child>&)]:border-e-[1px] [:is([data-ui-button-group]>&:not(:first-child),_[data-ui-button-group]_:not(:first-child)>&)]:border-s-primary-gap',
                danger: 'in-data-ui-button-group:border-s-0 in-data-ui-button-group:border-e [:is([data-ui-button-group]>&:last-child,_[data-ui-button-group]_:last-child>&)]:border-e-0 in-data-ui-button-group:border-red-600',
                filled: 'in-data-ui-button-group:border-e [:is([data-ui-button-group]>&:last-child,_[data-ui-button-group]_:last-child>&)]:border-e-0 in-data-ui-button-group:border-gray-300/70',
                ghost: '',
            },
            iconOnly: { true: 'px-0 gap-0' },
            round: { true: 'rounded-full' },
        },
        compoundVariants: [
            { iconOnly: true, size: 'base', class: 'w-10 [&_svg]:size-4.5' },
            { iconOnly: true, size: 'sm', class: 'w-8 [&_svg]:size-3.5' },
            { iconOnly: true, size: 'xs', class: 'w-6.5 [&_svg]:size-3' },
            { iconOnly: false, iconAppend: true, class: '[&_svg]:-me-1' },
            { iconOnly: false, iconPrepend: true, class: '[&_svg]:-ms-0.5' },
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
        :type="props.href ? null : type"
    >
        <Icon v-if="icon" :name="icon" />
        <Icon v-if="loading" name="loading" :size />

        <!-- =Jay. trim-text-start seems to make smaller buttons look worse such as the collections index "Create Entry" buttons -->
        <div :class="{ 'trim-text-start': size !== 'xs' && size !== 'sm' }">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>

        <Icon v-if="iconAppend" :name="iconAppend" />
    </component>
</template>
