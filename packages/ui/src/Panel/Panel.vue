<script setup>
import PanelHeader from '../Panel/Header.vue';
import Heading from '../Heading.vue';
import Subheading from '../Subheading.vue';

const props = defineProps({
    heading: { type: String, default: null },
    subheading: { type: String, default: null },
});
</script>

<template>
    <div
        :class="[
            'relative bg-gray-200/50 [.bg-architectural-lines_&]:backdrop-blur-[10px] dark:bg-gray-950 dark:inset-shadow-2xs dark:inset-shadow-black',
            'w-full rounded-2xl mb-5 p-1.75 [&:has([data-ui-panel-header])]:pt-0 focus-none starting-style-transition starting-style-transition--siblings',
        ]"
        data-ui-panel
    >
        <PanelHeader v-if="heading">
            <Heading v-html="heading" />
            <Subheading v-if="subheading" v-html="subheading" />
            <slot name="header-actions" />
        </PanelHeader>
        <slot />
    </div>
</template>
