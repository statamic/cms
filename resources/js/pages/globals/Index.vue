<script setup>
import Head from '@/pages/layout/Head.vue';
import Listing from '@/components/globals/Listing.vue';
import { Header, CommandPaletteItem, Button, Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

const props = defineProps({
    globals: Array,
    createUrl: String,
    canCreate: Boolean,
});

if (props.globals.length === 0) useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Global Sets')" />

    <template v-if="globals.length">
        <Header :title="__('Globals')" icon="globals">
            <CommandPaletteItem
                v-if="canCreate"
                category="Actions"
                prioritize
                :text="__('Create Global Set')"
                :url="createUrl"
                icon="globals"
                v-slot="{ text, url }"
            >
                <Button
                    :text="text"
                    :href="url"
                    variant="primary"
                />
            </CommandPaletteItem>
        </Header>

        <Listing :initial-globals="globals" />
    </template>

    <template v-else>
        <header class="py-8 mt-8 text-center">
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
                <Icon name="globals" class="size-5 text-gray-500" />
                {{ __('Globals') }}
            </h1>
        </header>

        <EmptyStateMenu :heading="__('statamic::messages.global_set_config_intro')">
            <EmptyStateItem
                v-if="canCreate"
                :href="createUrl"
                icon="globals"
                :heading="__('Create Global Set')"
                :description="__('statamic::messages.global_next_steps_create_description')"
            />
        </EmptyStateMenu>
    </template>

    <DocsCallout :topic="__('Global Variables')" url="globals" />
</template>
