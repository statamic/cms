<script setup>
import Head from '@/pages/layout/Head.vue';
import { Link } from '@inertiajs/vue3';
import { Header, CommandPaletteItem, Button, Icon, EmptyStateMenu, EmptyStateItem, CardList, CardListItem, Dropdown, DropdownMenu, DropdownItem, DocsCallout } from '@ui';
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

        <CardList :heading="__('Title')">
            <CardListItem v-for="global in globals" :key="global.id">
                <Link class="text-sm" :href="global.edit_url" v-tooltip="global.handle">{{ __(global.title) }}</Link>
                <Dropdown>
                    <DropdownMenu>
                        <DropdownItem :text="__('Edit')" icon="edit" :href="global.edit_url" />
                        <DropdownItem v-if="global.configurable" :text="__('Configure')" icon="cog" :href="global.configure_url" />
                        <DropdownItem v-if="global.deleteable" :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${global.id}`][0].confirm()" />
                    </DropdownMenu>
                </Dropdown>
                <resource-deleter :ref="`deleter_${global.id}`" :resource="global" reload />
            </CardListItem>
        </CardList>
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
