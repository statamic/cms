<script setup>
import { CardPanel, Icon } from '@statamic/ui';

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
</script>

<template>
    <component
        :is="href ? 'a' : 'div'"
        :href="href"
        class="w-full flex gap-2 px-3 py-4 pt-5 pb-6.5 items-start hover:bg-gray-100 dark:hover:bg-dark-550 rounded-md group"
    >
        <div class="[&_svg]:size-9 [&_svg]:text-gray-500 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
            <Icon :name="icon" />
        </div>
        <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
            <h3 class="text-lg font-medium trim-text-start text-grey-800 mb-1.5" v-text="heading"></h3>
            <p v-if="description" class="text-gray-500 text-sm" v-text="description"></p>
            <slot />
        </div>
    </component>
</template>
