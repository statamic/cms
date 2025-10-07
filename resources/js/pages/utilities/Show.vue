<script setup>
import Head from '@/pages/layout/Head.vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';

defineProps(['html']);
</script>

<template>
    <Head :title="__('Utility')" />

    <DynamicHtmlRenderer :html="html" />
</template>