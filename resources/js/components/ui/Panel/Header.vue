<script setup>
import { Heading } from '@statamic/cms/ui';

const props = defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <!-- =Jay. If there are buttons in the header we want to trim the top padding so it doesn't look top-heavy, e.g. the pages tree hierarchy -->
    <header class="px-4.5 py-2 antialiased [&:has(button)]:pt-0.25" data-ui-panel-header>
        <Heading v-text="props.title" v-if="props.title" />
        <slot v-else />
    </header>
</template>
