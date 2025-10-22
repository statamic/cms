<script setup>
import Head from '@/pages/layout/Head.vue';
import Scaffolder from '@/components/collections/Scaffolder.vue';

const props = defineProps({
    collection: Object,
    route: String,
});
</script>

<template>
    <Head :title="[__('Scaffold Views'), __(collection.title), __('Collections')]" />

    <Scaffolder
        :title="collection.title"
        :handle="collection.handle"
        :route="route"
    />
</template>
