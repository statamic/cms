<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';

const props = defineProps({
    color: { type: String, default: 'default' },
    href: { type: String, default: null },
    icon: { type: String, default: null },
    pill: { type: Boolean, default: false },
    size: { type: String, default: 'default' },
    subText: { type: String, default: null },
    text: { type: String, default: null },
    variant: { type: String, default: 'default' },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const tag = computed(() => (props.href ? 'a' : 'div'));

const badgeClasses = computed(() => {
    const classes = cva({
        base: 'inline-flex items-center gap-1 font-normal antialiased whitespace-nowrap no-underline not-prose',
        variants: {
            size: {
                sm: 'text-2xs py-0 leading-normal px-1 rounded-xs [&_svg]:size-2',
                default: 'text-xs py-0.5 px-2 rounded-xs [&_svg]:size-2.5',
                lg: 'text-sm py-1 px-3 rounded-md [&_svg]:size-3 [&_svg]:me-1',
            },
            color: {
                amber: 'bg-amber-100 border-amber-400 text-amber-700 [a]:hover:bg-amber-200/70',
                black: 'bg-gray-900 border-black text-white [a]:hover:bg-black/90',
                blue: 'bg-blue-100/80 border-blue-300 text-blue-700 [a]:hover:bg-blue-200/60',
                cyan: 'bg-cyan-100/80 border-cyan-400 text-cyan-700 [a]:hover:bg-cyan-200/60',
                default: 'bg-gray-100/80 border-gray-300 text-gray-700 [a]:hover:bg-gray-200/50',
                emerald: 'bg-emerald-100/80 border-emerald-400 text-emerald-700 [a]:hover:bg-emerald-200/60',
                fuchsia: 'bg-fuchsia-100/80 border-fuchsia-300 text-fuchsia-700 [a]:hover:bg-fuchsia-200/60',
                green: 'bg-green-100/80 border-green-400 text-green-700 [a]:hover:bg-green-200/60',
                indigo: 'bg-indigo-100/80 border-indigo-300 text-indigo-700 [a]:hover:bg-indigo-200/60',
                lime: 'bg-lime-100 border-lime-400 text-lime-700 [a]:hover:bg-lime-200/80',
                orange: 'bg-orange-100 border-orange-400 text-orange-700 [a]:hover:bg-orange-200/60',
                pink: 'bg-pink-100/80 border-pink-300 text-pink-800 [a]:hover:bg-pink-200/60',
                purple: 'bg-purple-100/80 border-purple-300 text-purple-800 [a]:hover:bg-purple-200/60',
                red: 'bg-red-100/80 border-red-400/80 text-red-700 [a]:hover:bg-red-200/60',
                rose: 'bg-rose-100/80 border-rose-300 text-rose-800 [a]:hover:bg-rose-200/60',
                sky: 'bg-sky-100/80 border-sky-300 text-sky-700 [a]:hover:bg-sky-200/60',
                teal: 'bg-teal-100 border-teal-400 text-teal-700 [a]:hover:bg-teal-200/70',
                violet: 'bg-violet-100/80 border-violet-300 text-violet-700 [a]:hover:bg-violet-200/60',
                white: 'bg-white border-gray-300 text-gray-700 [a]:hover:bg-gray-200/50',
                yellow: 'bg-yellow-100 border-yellow-400 text-yellow-700 [a]:hover:bg-yellow-200/80',
            },
            variant: {
                default: 'border dark:border-none shadow-ui-sm',
                flat: 'border-0 shadow-none',
            },
            pill: { true: 'rounded-full' },
        },
    })({ ...props });

    return twMerge(classes);
});
</script>

<template>
    <component :is="tag" :class="badgeClasses" :href="props.href" data-ui-badge>
        <ui-icon v-if="icon" :name="icon" />
        <slot v-if="hasDefaultSlot" />
        <template v-else>{{ text }}</template>
        <span v-if="props.subText" class="text-[0.625rem] leading-tight font-medium opacity-70">{{ subText }}</span>
    </component>
</template>
