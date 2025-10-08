<script setup>
import Head from '@/pages/layout/Head.vue';
import { CreateForm } from '@/components/ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['route', 'icon']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create Blueprint')" />

    <CreateForm
        :icon
        :route
        without-handle
        :title="__('Create Blueprint')"
        :subtitle="__('messages.blueprints_intro')"
        :title-instructions="__('messages.blueprints_title_instructions')"
    />
</template>
