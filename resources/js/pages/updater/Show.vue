<script setup>
import Head from '@/pages/layout/Head.vue';
import Updater from '@/components/updater/Updater.vue';

defineProps(['slug', 'package', 'name']);
</script>

<template>
    <div class="max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="__('Updates')" />
        <Updater :slug="slug" :package="package" :name="name" />
    </div>
</template>