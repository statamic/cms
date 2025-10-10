<script setup>
import Head from '@/pages/layout/Head.vue';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

useArchitecturalBackground();

const props = defineProps([
    'title',
    'blueprints',
    'canEdit',
    'editUrl',
    'canEditBlueprints',
    'canCreate',
    'createLabel',
    'createEntryUrl',
    'blueprintsUrl',
    'scaffoldUrl',
])
</script>

<template>
    <Head :title="[__(title), __('Collections')]" />

    <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <ui-icon name="collections" class="size-5 text-gray-500"></ui-icon>
            <span v-text="__(title)" />
        </h1>
    </header>

    <ui-empty-state-menu :heading="__('Start designing your collection with these steps')">
        <ui-empty-state-item
            v-if="canEdit"
            :href="editUrl"
            icon="configure"
            :heading="__('Configure Collection')"
            :description="__('statamic::messages.collection_next_steps_configure_description')"
        />

        <template v-if="canCreate">
            <ui-empty-state-item
                v-if="blueprints.length > 1"
                icon="fieldtype-entries"
                :heading="createLabel"
                :description="__('statamic::messages.collection_next_steps_create_entry_description')"
            >
                <a
                    v-for="blueprint in blueprints"
                    :href="blueprint.createEntryUrl"
                    class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2"
                    v-text="blueprint.title"
                />
            </ui-empty-state-item>

            <ui-empty-state-item
                v-else
                :href="createEntryUrl"
                icon="fieldtype-entries"
                :heading="createLabel"
                :description="__('statamic::messages.collection_next_steps_create_entry_description')"
            />
        </template>

        <ui-empty-state-item
            v-if="canEditBlueprints"
            :href="blueprintsUrl"
            icon="blueprints"
            :heading="__('Configure Blueprints')"
            :description="__('statamic::messages.collection_next_steps_blueprints_description')"
        />

        <ui-empty-state-item
            v-if="canEdit"
            :href="scaffoldUrl"
            icon="scaffold"
            :heading="__('Scaffold Views')"
            :description="__('statamic::messages.collection_next_steps_scaffold_description')"
        />
    </ui-empty-state-menu>

    <ui-docs-callout :topic="__('Collections')" url="collections" />
</template>
