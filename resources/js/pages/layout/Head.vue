<script setup lang="ts">
import { computed, inject } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    title?: string | string[];
}>();

const { cmsName } = inject('layout');

const title = computed(() => {
    let title = props.title;
    if (typeof title === 'string') title = [title];
    title.push(cmsName);
    const divider = Statamic.$config.get('direction') === 'ltr' ? '‹' : '›';
    return title.join(` ${divider} `);
});
</script>

<template>
    <Head :title="title">
        <slot />
    </Head>
</template>
