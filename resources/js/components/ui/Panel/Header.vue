<script setup>
import { Heading } from '@statamic/ui';

const props = defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <header class="px-4.5 py-2 antialiased border border-black" data-ui-panel-header>
        <Heading v-text="props.title" v-if="props.title" />
        <slot v-else />
    </header>
</template>
