<script setup>
import { cva } from 'cva';
import { computed, useSlots } from 'vue';

const props = defineProps({
    size: { type: String, default: 'base' },
    level: { type: [Number, null], default: null },
    text: { type: [String, null], default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const tag = computed(() => (props.level ? `h${props.level}` : 'div'));

const classes = cva({
    base: 'font-medium [&:has(+[data-ui-subheading])]:mb-0.5 antialiased not-prose',
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
    <component :is="tag" :class="classes" data-ui-heading>
        <span v-if="!hasDefaultSlot">{{ text }}</span>
        <slot v-else />
    </component>
</template>
