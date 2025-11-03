<script setup>
import Head from '@/pages/layout/Head.vue';
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

useArchitecturalBackground();

defineProps([
    'taxonomyTitle',
    'createUrl',
    'taxonomyEditUrl',
    'taxonomyBlueprintsUrl',
    'canCreate',
    'canEdit',
    'canConfigureFields',
]);
</script>

<template>
    <Head :title="taxonomyTitle" />

    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2">
            <Icon name="taxonomies" class="size-5 text-gray-500" />
            <span>{{ __(taxonomyTitle) }}</span>
        </h1>
    </header>

    <EmptyStateMenu :heading="__('Start designing your taxonomy with these steps')">
        <EmptyStateItem
            v-if="canEdit"
            :href="taxonomyEditUrl"
            icon="configure"
            :heading="__('Configure Taxonomy')"
            :description="__('statamic::messages.taxonomy_next_steps_configure_description')"
        />
        <EmptyStateItem
            v-if="canCreate"
            :href="createUrl"
            icon="fieldtype-taxonomy"
            :heading="__('Create Term')"
            :description="__('statamic::messages.taxonomy_next_steps_create_term_description')"
        />
        <EmptyStateItem
            v-if="canConfigureFields"
            :href="taxonomyBlueprintsUrl"
            icon="blueprints"
            :heading="__('Configure Blueprints')"
            :description="__('statamic::messages.taxonomy_next_steps_blueprints_description')"
        />
    </EmptyStateMenu>

    <DocsCallout :topic="__('Taxonomies')" url="taxonomies" />
</template>