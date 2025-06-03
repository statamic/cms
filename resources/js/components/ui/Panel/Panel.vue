<script setup>
import { PanelHeader, Heading, Subheading } from '@statamic/ui';

const props = defineProps({
    heading: { type: String, default: null },
    subheading: { type: String, default: null },
});
</script>

<template>
    <div
        :class="[
            'relative bg-gray-100 dark:border-b dark:border-white/13 dark:bg-black/25 dark:inset-shadow-2xs dark:inset-shadow-black',
            'not-prose w-full rounded-[.875rem] mb-7 px-1 py-1.5',
        ]"
        data-ui-panel
    >
        <PanelHeader v-if="heading">
            <Heading v-html="heading" />
            <Subheading v-if="subheading" v-html="subheading" />
        </PanelHeader>
        <slot />
    </div>
</template>
