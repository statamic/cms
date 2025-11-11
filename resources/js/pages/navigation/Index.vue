<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Button, DocsCallout, CommandPaletteItem, Icon, EmptyStateMenu, EmptyStateItem, CardList, CardListItem, Dropdown, DropdownMenu, DropdownItem } from '@ui';
import { Link } from '@inertiajs/vue3';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

const props = defineProps(['navs', 'canCreate', 'createUrl']);

if (props.navs.length === 0) useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Navigation')" />

    <Header :title="__('Navigation')" icon="navigation">
        <CommandPaletteItem
            v-if="canCreate"
            category="Actions"
            prioritize
            :text="__('Create Navigation')"
            :url="createUrl"
            icon="navigation"
            v-slot="{ text, url }"
        >
            <Button
                :text="text"
                :href="url"
                variant="primary"
            />
        </CommandPaletteItem>
    </Header>

    <template v-if="navs.length">
        <CardList :heading="__('Title')">
            <CardListItem v-for="item in navs" :key="item.id">
                <Link
                    :href="item.available_in_selected_site ? item.show_url : item.edit_url"
                    v-text="__(item.title)"
                />
                <Dropdown placement="left-start">
                    <DropdownMenu>
                        <DropdownItem :text="__('Configure')" icon="cog" :href="item.edit_url" />
                        <DropdownItem v-if="item.deleteable" :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${item.id}`][0].confirm()" />
                    </DropdownMenu>
                </Dropdown>

                <resource-deleter :ref="`deleter_${item.id}`" :resource="item" reload />
            </CardListItem>
        </CardList>
    </template>

    <template v-else>
        <header class="py-8 pt-16 text-center">
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
                <Icon name="navigation" class="size-5 text-gray-500" />
                {{ __('Navigation') }}
            </h1>
        </header>

        <EmptyStateMenu :heading="__('statamic::messages.navigation_configure_intro')">
            <EmptyStateItem
                :href="createUrl"
                icon="navigation"
                :heading="__('Create a Navigation')"
                :description="__('Get started by creating your first navigation.')"
            />
        </EmptyStateMenu>
    </template>

    <DocsCallout :topic="__('Navigation')" url="navigation" />
</template>