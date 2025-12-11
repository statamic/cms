<script setup>
import Listing from '@/components/collections/Listing.vue';
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';
import Head from '@/pages/layout/Head.vue';

const props = defineProps({
    collections: Array,
    columns: Array,
    createUrl: String,
    actionUrl: String,
    canCreate: Boolean,
});

if (props.collections.length === 0) useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Collections')" />

    <div class="max-w-5xl mx-auto">
        <Listing
            v-if="collections.length"
            :initial-rows="collections"
            :initial-columns="columns"
            :can-create-collections="canCreate"
            :create-url="createUrl"
            :action-url="actionUrl"
        />

        <template v-else>
            <header class="py-8 pt-16 text-center">
                <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
                    <Icon name="collections" class="size-5 text-gray-500" />
                    {{ __('Collections') }}
                </h1>
            </header>

            <EmptyStateMenu :heading="__('statamic::messages.collection_configure_intro')">
                <EmptyStateItem
                    :href="createUrl"
                    icon="collections"
                    :heading="__('Create Collection')"
                    :description="__('Get started by creating your first collection.')"
                />
            </EmptyStateMenu>
        </template>

        <DocsCallout :topic="__('Collections')" url="collections" />
    </div>
</template>
