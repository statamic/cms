<script setup>
import { cva } from 'cva';
import { computed, useSlots } from 'vue';

const props = defineProps({
    href: { type: [String, null], default: null },
    icon: { type: String, default: null },
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
            base: 'text-sm text-gray-600 dark:text-white',
            lg: 'text-base text-gray-700 dark:text-white',
            xl: 'text-2xl text-gray-800 dark:text-white',
        },
    },
})({ ...props });
</script>

<template>
    <component :is="tag" :class="classes" :href="href" data-ui-heading>
        <ui-icon v-if="icon" :name="icon" class="size-5.5 text-gray-400 dark:text-gray-600" />
        <span v-if="!hasDefaultSlot">{{ text }}</span>
        <slot v-else />
    </component>
</template>
