<script setup>
import Head from '@/pages/layout/Head.vue';
import { CreateForm } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['submitUrl']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create Navigation')" />

    <CreateForm
        :title="__('Create Navigation')"
        :subtitle="__('statamic::messages.navigation_configure_intro')"
        icon="navigation"
        :route="submitUrl"
        :title-instructions="__('statamic::messages.navigation_configure_title_instructions')"
        :handle-instructions="__('statamic::messages.navigation_configure_handle_instructions')"
    />
</template>