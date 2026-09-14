<script setup>
import { cva } from 'cva';
import { computed, useSlots } from 'vue';
import Icon from './Icon/Icon.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    /** The URL to link to */
    href: { type: [String, null], default: null },
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: { type: String, default: null },
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: { type: [String, null], default: null },
    /** Controls the heading level */
    level: { type: [Number, null], default: null },
    /** Controls the size of the heading. Options: `base`, `lg`, `xl`, `2xl` */
    size: { type: String, default: 'base' },
    /** The heading text to display */
    text: { type: [String, Number, Boolean, null], default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const tag = computed(() => {
    if (props.level) return `h${props.level}`;
    if (props.href) return props.target === '_blank' ? 'a' : Link;
    return 'div';
});

const classes = cva({
    base: 'font-medium [&:has(+[data-ui-subheading])]:mb-0.5 [&:has(+[data-ui-subheading])]:text-gray-925 dark:[&:has(+[data-ui-subheading])]:text-white antialiased flex items-center gap-2',
    variants: {
        size: {
            base: 'text-sm tracking-tight text-gray-700 dark:text-white',
            lg: 'text-base text-gray-700 dark:text-white',
            xl: 'text-lg text-gray-900 dark:text-white',
            '2xl': 'text-2xl text-gray-900 dark:text-white',
        },
    },
})({ ...props });

const iconClasses = cva({
    base: 'text-gray-500 dark:text-gray-500',
    variants: {
        size: {
            base: 'size-4',
            lg: 'size-5',
            xl: 'size-5.5',
            '2xl': 'size-6',
        },
    },
})({ ...props });
</script>

<template>
    <component :is="tag" :class="classes" :href="href" data-ui-heading>
        <Icon v-if="icon" :name="icon" :class="iconClasses" />
        <span v-if="!hasDefaultSlot">{{ text }}</span>
        <slot v-else />
    </component>
</template>
