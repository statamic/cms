<script setup>
import Head from '@/pages/layout/Head.vue';
import Form from '@/components/sites/EditForm.vue';
import { DocsCallout } from '@ui';

defineProps(['blueprint', 'values', 'meta', 'updateUrl']);
</script>

<template>
    <Head :title="__('Configure Sites')" />

    <div class="max-w-5xl mx-auto">
        <Form
            :blueprint="blueprint"
            :initial-values="values"
            :meta="meta"
            :url="updateUrl"
        />

        <DocsCallout :topic="__('Multi-Site')" url="multi-site" />
    </div>
</template>
