<script setup>
import Head from '@/pages/layout/Head.vue';
import { CreateForm } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['submitUrl']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create Form')" />

    <CreateForm
        :title="__('Create Form')"
        :subtitle="__('statamic::messages.form_configure_intro')"
        icon="forms"
        :route="submitUrl"
        :title-instructions="__('statamic::messages.form_configure_title_instructions')"
        :handle-instructions="__('statamic::messages.form_configure_handle_instructions')"
    />
</template>
