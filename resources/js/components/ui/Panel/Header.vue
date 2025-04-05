<script setup>
const props = defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <header class="px-4.5 py-2 antialiased" data-ui-panel-header>
        <ui-heading v-text="props.title" v-if="props.title" />
        <slot v-else />
    </header>
</template>
