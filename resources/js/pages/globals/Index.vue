<script setup>
import Head from '@/pages/layout/Head.vue';
import { Link, router } from '@inertiajs/vue3';
import { Header, CommandPaletteItem, Button, Icon, EmptyStateMenu, EmptyStateItem, DocsCallout, Listing, DropdownItem } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';
import { computed } from 'vue';

const props = defineProps({
    globals: Array,
    columns: Array,
    createUrl: String,
    canCreate: Boolean,
    actionUrl: String,
});

if (props.globals.length === 0) useArchitecturalBackground();

const actionContext = computed(() => {
    return {
        site: Statamic.$config.get('selectedSite'),
    }
})
</script>

<template>
    <Head :title="__('Global Sets')" />

    <div class="max-w-5xl mx-auto">
        <Header v-if="globals.length" :title="__('Globals')" icon="globals">
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

        <template v-if="globals.length">
            <Listing
                :items="globals"
                :columns="columns"
                :action-url="actionUrl"
                :action-context="actionContext"
                :allow-search="false"
                :allow-customizing-columns="false"
                @refreshing="() => router.reload()"
            >
                <template #cell-title="{ row: global }">
                    <Link :href="global.edit_url" v-tooltip="global.handle">{{ __(global.title) }}</Link>
                </template>
                <template #cell-handle="{ row: global }">
                    <span class="font-mono text-2xs">{{ global.handle }}</span>
                </template>
                <template #prepended-row-actions="{ row: global }">
                    <DropdownItem :text="__('Edit')" icon="edit" :href="global.edit_url" />
                    <DropdownItem v-if="global.configurable" :text="__('Configure')" icon="cog" :href="global.configure_url" />
                </template>
            </Listing>
        </template>

        <template v-else>
            <header class="py-8 pt-16 text-center">
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
    </div>
</template>
