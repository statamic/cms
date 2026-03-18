<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '../Icon/Icon.vue';
import { useSlots } from 'vue';

const props = defineProps({
    /** Optional link */
    href: {
        type: String,
        default: null,
    },
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: String,
        required: true,
    },
    /** Heading text for the empty state item */
    heading: {
        type: String,
        required: true,
    },
    /** Optional description text below the heading */
    description: {
        type: String,
        default: '',
    },
});

const slots = useSlots();
const hasSlot = !!slots.default;
</script>

<template>
    <li class="w-full">
        <component
            :is="hasSlot ? 'div' : (href ? Link : 'button')"
            :href="href"
            class="w-full flex gap-2 px-3 pt-4 pb-5.5 items-start hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md group cursor-pointer"
        >
            <Icon :name="icon" class="size-6 me-4 mt-1 text-gray-400" />
            <div class="flex-1 me-6 text-start">
                <ui-heading size="xl" :level="3" :text="heading" class="mb-1.5 font-semibold" />
                <ui-description v-if="description" :text="description" />
                <slot />
            </div>
        </component>
    </li>
</template>
