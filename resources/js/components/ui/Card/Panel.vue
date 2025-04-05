<script setup>
const props = defineProps({
    heading: { type: String, default: null },
    subheading: { type: String, default: null },
});
</script>

<template>
    <ui-panel>
        <ui-panel-header v-if="heading">
            <ui-heading v-html="heading" />
            <ui-subheading v-if="subheading" v-html="subheading" />
        </ui-panel-header>
        <ui-card>
            <slot />
        </ui-card>
    </ui-panel>
</template>
