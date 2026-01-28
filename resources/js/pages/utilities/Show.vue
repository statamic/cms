<script setup>
import Head from '@/pages/layout/Head.vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';

defineProps(['title', 'html']);
</script>

<template>
    <Head :title="[title, __('Utilities')]" />

    <DynamicHtmlRenderer :html="html" />
</template>