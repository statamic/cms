<script setup>
import Heading from '../Heading.vue';

const props = defineProps({
    /** Heading text for the panel */
    title: { type: String, default: null },
});
</script>

<template>
    <header class="px-4.5 py-3 [&:has(button)]:pr-1 [&_button]:-my-2 [&_button]:relative" data-ui-panel-header>
        <Heading v-text="props.title" v-if="props.title" />
        <slot v-else />
    </header>
</template>
