<script setup>
import { PanelHeader, Heading, Subheading } from '@/components/ui';

const props = defineProps({
    heading: { type: String, default: null },
    subheading: { type: String, default: null },
});
</script>

<template>
    <div
        :class="[
            'relative bg-gray-200/50 dark:bg-gray-950 dark:inset-shadow-2xs dark:inset-shadow-black',
            'w-full rounded-2xl mb-5 p-1.5 [&:has([data-ui-panel-header])]:pt-0 focus-none',
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
