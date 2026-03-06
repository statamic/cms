<script setup>
import { PublishForm } from '@/components/ui';
import Head from '@/pages/layout/Head.vue';

const props = defineProps({
    icon: { type: String, required: false },
    title: { type: String, required: true },
    blueprint: { type: Object, required: true },
    values: { type: Object, required: true },
    meta: { type: Object, required: true },
    submitUrl: { type: String, required: true },
    submitMethod: { type: String, required: true },
    readOnly: { type: Boolean, required: false },
    asConfig: { type: Boolean, required: false },
});
</script>

<template>
    <Head :title />

    <div :class="{ 'max-w-page mx-auto': asConfig }">
        <PublishForm
            :icon
            :title
            :blueprint
            :initial-values="values"
            :initial-meta="meta"
            :submit-url
            :submit-method
            :read-only
            :as-config
        />
    </div>
</template>
