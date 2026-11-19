<script setup>
import Head from '@/pages/layout/Head.vue';
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['createUrl']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Asset Containers')" />

    <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <Icon name="assets" class="size-5 text-gray-500" />
            {{ __('Asset Containers') }}
        </h1>
    </header>

    <EmptyStateMenu :heading="__('statamic::messages.asset_container_intro')">
        <EmptyStateItem
            :href="createUrl"
            icon="assets"
            :heading="__('Create Asset Container')"
            :description="__('Get started by creating your first asset container.')"
        />
    </EmptyStateMenu>

    <DocsCallout :topic="__('Assets')" url="assets" />
</template>
