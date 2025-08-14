<script setup>
import { cva } from 'cva';
import { computed, useSlots } from 'vue';
import { Icon } from '@statamic/cms/ui';

const props = defineProps({
    href: { type: [String, null], default: null },
    icon: { type: [String, null], default: null },
    level: { type: [Number, null], default: null },
    size: { type: String, default: 'base' },
    text: { type: [String, Number, Boolean, null], default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const tag = computed(() => {
    if (props.level) return `h${props.level}`;
    if (props.href) return 'a';
    return 'div';
});

const classes = cva({
    base: 'font-medium [&:has(+[data-ui-subheading])]:mb-0.5 antialiased flex items-center gap-2',
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
    base: 'text-gray-500 dark:text-gray-600',
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
