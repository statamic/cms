<script setup>
    const props = defineProps({
        heading: {type: String, default: null }
    });
</script>

<template>
    <ui-panel>
        <ui-panel-header v-if="heading">
            <ui-heading>{{ heading }}</ui-heading>
        </ui-panel-header>
        <ui-card>
            <slot />
        </ui-card>
    </ui-panel>
</template>
