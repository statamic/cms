<script setup>
import { CardPanel, Icon } from '@statamic/ui';
import { useSlots } from 'vue';

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
    icon: {
        type: String,
        required: true,
    },
    heading: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});

const slots = useSlots();
const hasSlot = !!slots.default;
</script>

<template>
    <component
        :is="hasSlot ? 'div' : (href ? 'a' : 'button')"
        :href="href"
        class="w-full flex gap-2 px-3 pt-4 pb-5.5 items-start hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md group"
    >
        <Icon :name="icon" class="size-6 me-4 mt-1 text-gray-400" />
        <div class="flex-1 mb-4 md:mb-0 me-6 text-start">
            <ui-heading size="xl" :level="3" :text="heading" class="mb-1.5 font-semibold" />
            <ui-description v-if="description" :text="description" />
            <slot />
        </div>
    </component>
</template>
