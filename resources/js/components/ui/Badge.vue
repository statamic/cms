<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import Icon from './Icon/Icon.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    /** Appended text */
    append: { type: [String, Number, Boolean, null], default: null },
    /** The element or component this component should render as */
    as: { type: String, default: 'div' },
    /** Controls the color of the badge. <br><br> Options: `default`, `amber`, `black`, `blue`, `cyan`, `emerald`, `fuchsia`, `green`, `indigo`, `lime`, `orange`, `pink`, `purple`, `red`, `rose`, `sky`, `teal`, `violet`, `white`, `yellow` */
    color: { type: String, default: 'default' },
    /** The URL to link to */
    href: { type: String, default: null },
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: { type: String, default: null },
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: { type: String, default: null },
    /** Icon name. Will display after the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    iconAppend: { type: String, default: null },
    /** When `true`, the badge will be displayed as a pill */
    pill: { type: Boolean, default: false },
    /** Prepended text */
    prepend: { type: [String, Number, Boolean, null], default: null },
    /** Controls the size of the badge. Options: `sm`, `default`, `lg` */
    size: { type: String, default: 'default' },
    /** Text to display in the badge */
    text: { type: [String, Number, Boolean, null], default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const tag = computed(() => {
    if (props.href) {
        return props.target === '_blank' ? 'a' : Link;
    }
    return props.as;
});

const badgeClasses = computed(() => {
    const classes = cva({
        base: 'relative inline-flex items-center justify-center gap-1 border dark:border-none dark:pb-0.25 font-normal antialiased whitespace-nowrap no-underline not-prose [button]:cursor-pointer group [&_svg]:opacity-60 [&_svg]:group-hover:opacity-80 dark:[&_svg]:group-hover:opacity-70',
        variants: {
            size: {
                sm: 'text-2xs leading-normal px-1.25 rounded-[0.1875rem] [&_svg]:size-2.5',
                default: 'text-xs leading-5.5 px-2 rounded-sm [&_svg]:size-3.5 gap-2',
                lg: 'font-medium text-sm leading-7 px-2.5 rounded-lg [&_svg]:size-4 gap-2',
            },
            color: {
                amber: 'bg-amber-50 dark:bg-gray-900 border-amber-400 dark:border-amber-700 text-amber-700 dark:text-amber-300 [a]:hover:bg-amber-100 [button]:hover:bg-amber-200 dark:[button]:hover:bg-gray-800',
                black: 'bg-gray-900 dark:bg-gray-900 border-black dark:border-gray-700 text-white dark:text-gray-300 [a]:hover:bg-gray-800 [button]:hover:bg-gray-800 dark:[button]:hover:bg-gray-800',
                blue: 'bg-blue-50 dark:bg-gray-900 border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 [a]:hover:bg-blue-100 [button]:hover:bg-blue-100 dark:[button]:hover:bg-gray-800',
                cyan: 'bg-cyan-50 dark:bg-gray-900 border-cyan-400 dark:border-cyan-700 text-cyan-700 dark:text-cyan-300 [a]:hover:bg-cyan-100 [button]:hover:bg-cyan-100 dark:[button]:hover:bg-gray-800',
                default: 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 dark:text-gray-100 text-gray-700 [a]:hover:bg-gray-100 dark:[a]:hover:bg-gray-700 [button]:hover:bg-gray-200 dark:[button]:hover:bg-gray-800',
                emerald: 'bg-emerald-50 border-emerald-400 dark:border-emerald-700 text-emerald-700 dark:bg-gray-900 dark:text-emerald-300 [a]:hover:bg-emerald-100 [button]:hover:bg-emerald-100 dark:[button]:hover:bg-gray-800',
                fuchsia: 'bg-fuchsia-50 dark:bg-gray-900 border-fuchsia-300 dark:border-fuchsia-700 text-fuchsia-700 dark:text-fuchsia-300 [a]:hover:bg-fuchsia-100 dark:[a]:hover:bg-fuchsia-900 [button]:hover:bg-fuchsia-100 dark:[button]:hover:bg-gray-800',
                green: 'bg-green-50 border-green-400 dark:border-green-700 text-green-700 dark:bg-gray-900 dark:text-green-300 [a]:hover:bg-green-100 dark:[a]:hover:bg-green-900 [button]:hover:bg-green-100 dark:[button]:hover:bg-gray-800',
                indigo: 'bg-indigo-50 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:bg-gray-900 dark:text-indigo-300 [a]:hover:bg-indigo-100 dark:[a]:hover:bg-indigo-900 [button]:hover:bg-indigo-100 dark:[button]:hover:bg-gray-800',
                lime: 'bg-lime-100 dark:bg-gray-900 border-lime-400 dark:border-lime-700 text-lime-700 dark:text-lime-300 [a]:hover:bg-lime-200 dark:[a]:hover:bg-lime-900 [button]:hover:bg-lime-200 dark:[button]:hover:bg-gray-800',
                orange: 'bg-orange-100 dark:bg-gray-900 border-orange-400 dark:border-orange-700 text-orange-700 dark:text-orange-300 [a]:hover:bg-orange-100 [button]:hover:bg-orange-100 dark:[a]:hover:bg-orange-900 dark:[button]:hover:bg-gray-800',
                pink: 'bg-pink-50 dark:bg-gray-900 border-pink-300 dark:border-pink-700 text-pink-800 dark:text-pink-300 [a]:hover:bg-pink-100 dark:[a]:hover:bg-pink-900 [button]:hover:bg-pink-100 dark:[button]:hover:bg-gray-800',
                purple: 'bg-purple-50 dark:bg-gray-900 border-purple-300 dark:border-purple-700 text-purple-800 dark:text-purple-300 [a]:hover:bg-purple-100 dark:[a]:hover:bg-purple-900 [button]:hover:bg-purple-100 dark:[button]:hover:bg-gray-800',
                red: 'bg-red-50 dark:bg-gray-900 border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 [a]:hover:bg-red-100 dark:[a]:hover:bg-red-900 [button]:hover:bg-red-100 dark:[button]:hover:bg-gray-800',
                rose: 'bg-rose-50 dark:bg-gray-900 border-rose-300 dark:border-rose-700 text-rose-800 dark:text-rose-300 [a]:hover:bg-rose-100 dark:[a]:hover:bg-rose-900 [button]:hover:bg-rose-100 dark:[button]:hover:bg-gray-800',
                sky: 'bg-sky-50 dark:bg-gray-900 border-sky-300 dark:border-sky-700 text-sky-700 dark:text-sky-300 [a]:hover:bg-sky-100 dark:[a]:hover:bg-sky-900 [button]:hover:bg-sky-100 dark:[button]:hover:bg-sky-900',
                teal: 'bg-teal-100 dark:bg-gray-900 border-teal-400 dark:border-teal-700 text-teal-700 dark:text-teal-300 [a]:hover:bg-teal-200 dark:[a]:hover:bg-teal-900 [button]:hover:bg-teal-200 dark:[button]:hover:bg-gray-800',
                violet: 'bg-violet-50 dark:bg-gray-900 border-violet-300 dark:border-violet-700 text-violet-700 dark:text-violet-300 [a]:hover:bg-violet-100 [button]:hover:bg-violet-100 dark:[a]:hover:bg-violet-900 dark:[button]:hover:bg-gray-800',
                white: 'bg-white border-gray-300 dark:border-gray-700 text-gray-700 dark:bg-gray-900 dark:text-gray-300 [a]:hover:bg-gray-100 [button]:hover:bg-gray-100 dark:[a]:hover:bg-gray-800 dark:[button]:hover:bg-gray-800',
                yellow: 'bg-yellow-50 dark:bg-gray-900 border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 [a]:hover:bg-yellow-200 dark:[a]:hover:bg-yellow-900 [button]:hover:bg-yellow-200 dark:[button]:hover:bg-gray-800',
            },
            pill: { true: 'rounded-full' },
            asButton: { true: 'shadow-ui-sm no-underline!' }
        },
    })({
        ...props,
        asButton: props.href ?? props.as == 'button' ? true : false,
    });

    return twMerge(classes);
});
</script>

<template>
    <component :is="tag" :class="badgeClasses" :href="props.href" :target="target" data-ui-badge>
        <span v-if="props.prepend" class="font-medium border-e border-inherit ps-0.5 pe-1.5">{{ prepend }}</span>
        <Icon v-if="icon" :name="icon" />
        <slot v-if="hasDefaultSlot" />
        <template v-else><span :class="{ 'st-text-trim-cap': prepend || append }">{{ text }}</span></template>
        <Icon v-if="iconAppend" :name="iconAppend" />
        <span v-if="props.append" class="font-medium border-s border-inherit ps-1.5 pe-0.5">{{ append }}</span>
    </component>
</template>
