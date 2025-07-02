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
        :is="hasSlot ? 'div' : href ? 'a' : 'button'"
        :href="href"
        class="group flex w-full items-start gap-2 rounded-md px-3 py-4 pt-5 pb-6.5 hover:bg-gray-100 dark:hover:bg-gray-800"
    >
        <Icon :name="icon" class="me-4 mt-1 size-6 text-gray-500" />
        <div class="me-6 mb-4 flex-1 text-start md:mb-0">
            <ui-heading size="xl" :level="3" :text="heading" class="mb-1.5" />
            <ui-description v-if="description" :text="description" />
            <slot />
        </div>
    </component>
</template>
