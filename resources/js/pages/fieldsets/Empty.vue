<script setup>
import Head from '@/pages/layout/Head.vue';
import { DocsCallout, EmptyStateMenu, EmptyStateItem, CommandPaletteItem, Icon } from '@ui';

defineProps(['createUrl']);
</script>

<template>
    <Head :title="__('Fieldsets')" />

    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <Icon name="fieldsets" class="size-5 text-gray-500" />
            {{ __('Fieldsets') }}
        </h1>
    </header>

    <EmptyStateMenu :heading="__('statamic::messages.fieldset_intro')">
        <CommandPaletteItem
            category="Actions"
            :text="__('Create Fieldset')"
            icon="fieldsets"
            :url="createUrl"
            v-slot="{ text, url, icon }"
        >
            <EmptyStateItem
                :heading="text"
                :href="url"
                :icon="icon"
                :description="__('Get started by creating your first fieldset.')"
            />
        </CommandPaletteItem>
    </EmptyStateMenu>

    <DocsCallout :topic="__('Blueprints')" url="blueprints" />
</template>
