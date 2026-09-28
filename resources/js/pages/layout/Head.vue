<script setup lang="ts">
import { computed, inject } from 'vue';
import { Head } from '@inertiajs/vue3';
import useStatamicPageProps from '@/composables/page-props.js';

const props = defineProps<{
    title?: string | string[];
}>();

const { cmsName } = useStatamicPageProps();

const title = computed(() => {
    let parts = props.title;
    if (typeof parts === 'string') parts = [parts];
    parts = [...parts, cmsName];
    const divider = Statamic.$config.get('direction') === 'ltr' ? '‹' : '›';
    return parts.join(` ${divider} `);
});
</script>

<template>
    <Head :title="title">
        <slot />
    </Head>
</template>
