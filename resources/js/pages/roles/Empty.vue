<script setup>
import Head from '@/pages/layout/Head.vue';
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps(['createUrl']);
useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Roles & Permissions')" />

    <header class="py-8 pt-16 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <Icon name="permissions" class="size-5 text-gray-500" />
            {{ __('Roles & Permissions') }}
        </h1>
    </header>

    <EmptyStateMenu :heading="__('statamic::messages.role_intro')">
        <EmptyStateItem
            :href="createUrl"
            icon="permissions"
            :heading="__('Create Role')"
            :description="__('Get started by creating your first role.')"
        />
    </EmptyStateMenu>

    <DocsCallout :topic="__('Roles & Permissions')" url="users#permissions" />
</template>
