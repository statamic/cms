<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import { Icon } from '@statamic/ui';

const props = defineProps({
    append: { type: [String, Number, Boolean, null], default: null },
    as: { type: String, default: 'div' },
    color: { type: String, default: 'default' },
    href: { type: String, default: null },
    icon: { type: String, default: null },
    iconAppend: { type: String, default: null },
    pill: { type: Boolean, default: false },
    prepend: { type: [String, Number, Boolean, null], default: null },
    size: { type: String, default: 'default' },
    text: { type: [String, Number, Boolean, null], default: null },
    variant: { type: String, default: 'default' },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const tag = computed(() => (props.href ? 'a' : props.as));

const badgeClasses = computed(() => {
    const classes = cva({
        base: 'inline-flex items-center justify-center gap-1 font-normal antialiased whitespace-nowrap no-underline not-prose [button]:cursor-pointer group [&_svg]:opacity-60 [&_svg]:group-hover:opacity-100',
        variants: {
            size: {
                sm: 'text-2xs leading-normal px-1.25 rounded-[0.1875rem] [&_svg]:size-2.5',
                default: 'text-xs leading-5.5 px-2.25 rounded-sm [&_svg]:size-3.5 gap-2',
                lg: 'font-medium text-sm leading-7 px-2.5 rounded-lg [&_svg]:size-4 gap-2',
            },
            color: {
                amber: 'bg-amber-100 border-amber-400 text-amber-700 [a]:hover:bg-amber-200/70 [button]:hover:bg-amber-200/70',
                black: 'bg-gray-900 border-black text-white [a]:hover:bg-black/90 [button]:hover:bg-black/90',
                blue: 'bg-blue-100/80 border-blue-300 text-blue-700 [a]:hover:bg-blue-200/60 [button]:hover:bg-blue-200/60',
                cyan: 'bg-cyan-100/80 border-cyan-400 text-cyan-700 [a]:hover:bg-cyan-200/60 [button]:hover:bg-cyan-200/60',
                default:
                    'bg-gray-100/80 border-gray-300 dark:bg-gray-700 dark:text-gray-100 text-gray-700 [a]:hover:bg-gray-200/50 [button]:hover:bg-gray-200/50',
                emerald:
                    'bg-emerald-100/80 border-emerald-400 text-emerald-700 [a]:hover:bg-emerald-200/60 [button]:hover:bg-emerald-200/60',
                fuchsia:
                    'bg-fuchsia-100/80 border-fuchsia-300 text-fuchsia-700 [a]:hover:bg-fuchsia-200/60 [button]:hover:bg-fuchsia-200/60',
                green: 'bg-green-100/80 border-green-400 text-green-700 [a]:hover:bg-green-200/60 [button]:hover:bg-green-200/60',
                indigo: 'bg-indigo-100/80 border-indigo-300 text-indigo-700 [a]:hover:bg-indigo-200/60 [button]:hover:bg-indigo-200/60',
                lime: 'bg-lime-100 border-lime-400 text-lime-700 [a]:hover:bg-lime-200/80 [button]:hover:bg-lime-200/80',
                orange: 'bg-orange-100 border-orange-400 text-orange-700 [a]:hover:bg-orange-200/60 [button]:hover:bg-orange-200/60',
                pink: 'bg-pink-100/80 border-pink-300 text-pink-800 [a]:hover:bg-pink-200/60 [button]:hover:bg-pink-200/60',
                purple: 'bg-purple-100/80 border-purple-300 text-purple-800 [a]:hover:bg-purple-200/60 [button]:hover:bg-purple-200/60',
                red: 'bg-red-100/80 border-red-400/80 text-red-700 [a]:hover:bg-red-200/60 [button]:hover:bg-red-200/60',
                rose: 'bg-rose-100/80 border-rose-300 text-rose-800 [a]:hover:bg-rose-200/60 [button]:hover:bg-rose-200/60',
                sky: 'bg-sky-100/80 border-sky-300 text-sky-700 [a]:hover:bg-sky-200/60 [button]:hover:bg-sky-200/60',
                teal: 'bg-teal-100 border-teal-400 text-teal-700 [a]:hover:bg-teal-200/70 [button]:hover:bg-teal-200/70',
                violet: 'bg-violet-100/80 border-violet-300 text-violet-700 [a]:hover:bg-violet-200/60 [button]:hover:bg-violet-200/60',
                white: 'bg-white border-gray-300 text-gray-700 [a]:hover:bg-gray-200/30 [button]:hover:bg-gray-200/30',
                yellow: 'bg-yellow-100 border-yellow-400 text-yellow-700 [a]:hover:bg-yellow-200/80 [button]:hover:bg-yellow-200/80',
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
        <span v-if="props.prepend" class="font-medium border-e border-inherit pe-2">{{ prepend }}</span>
        <Icon v-if="icon" :name="icon" />
        <slot v-if="hasDefaultSlot" />
        <template v-else>{{ text }}</template>
        <Icon v-if="iconAppend" :name="iconAppend" />
        <span v-if="props.append" class="font-medium border-s border-inherit ps-2">{{ append }}</span>
    </component>
</template>
