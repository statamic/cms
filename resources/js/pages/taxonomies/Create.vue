<script setup>
import Head from '@/pages/layout/Head.vue';
import { CreateForm } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['submitUrl']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create Taxonomy')" />

    <CreateForm
        :title="__('Create Taxonomy')"
        :subtitle="__('statamic::messages.taxonomy_configure_intro')"
        icon="taxonomies"
        :route="submitUrl"
        :title-instructions="__('statamic::messages.taxonomy_configure_title_instructions')"
        :handle-instructions="__('statamic::messages.taxonomy_configure_handle_instructions')"
    />
</template>