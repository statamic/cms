<script setup>
import Head from '@/pages/layout/Head.vue';
import { CreateForm } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['submitUrl']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create Global Set')" />

    <CreateForm
        :title="__('Create Global Set')"
        :subtitle="__('statamic::messages.globals_configure_intro')"
        icon="globals"
        :route="submitUrl"
        :title-instructions="__('statamic::messages.globals_configure_title_instructions')"
        :handle-instructions="__('statamic::messages.globals_configure_handle_instructions')"
    />
</template>