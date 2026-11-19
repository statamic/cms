<script setup>
import Head from '@/pages/layout/Head.vue';
import { CreateForm } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['submitUrl']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create Collection')" />

    <CreateForm
        :title="__('Create Collection')"
        :subtitle="__('statamic::messages.collection_configure_intro')"
        icon="collections"
        :route="submitUrl"
        :title-instructions="__('statamic::messages.collection_configure_title_instructions')"
        :handle-instructions="__('statamic::messages.collection_configure_handle_instructions')"
    />
</template>