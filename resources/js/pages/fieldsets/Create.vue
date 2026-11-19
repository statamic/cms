<script setup>
import Head from '@/pages/layout/Head.vue';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['route']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create Fieldset')" />

    <ui-create-form
        :title="__('Create Fieldset')"
        :subtitle="__('messages.fields_fieldsets_description')"
        icon="fieldsets"
        :route="route"
        :title-instructions="__('messages.fieldsets_title_instructions')"
        :handle-instructions="__('messages.fieldsets_handle_instructions')"
    />
</template>
