<script setup>
import NavBuilder from '@/components/nav/Builder.vue';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'title',
    'indexUrl',
    'updateUrl',
    'destroyUrl',
    'saveAsOptions',
    'nav',
]);
</script>

<template>
    <Head :title />

    <nav-builder
        :title="title"
        :index-url="indexUrl"
        :update-url="updateUrl"
        :destroy-url="destroyUrl"
        :save-as-options="saveAsOptions"
        :nav="nav"
    />
</template>
