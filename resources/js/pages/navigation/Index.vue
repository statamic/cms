<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Button, DocsCallout, CommandPaletteItem, Icon, EmptyStateMenu, EmptyStateItem, Listing, DropdownItem } from '@ui';
import { Link, router } from '@inertiajs/vue3';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

const props = defineProps(['navs', 'columns', 'canCreate', 'createUrl', 'actionUrl']);

if (props.navs.length === 0) useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Navigation')" />

    <div class="max-w-6xl mx-auto" data-max-width-wrapper>
        <Header v-if="navs.length" :title="__('Navigation')" icon="navigation">
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
            <Listing
                :items="navs"
                :columns="columns"
                :action-url="actionUrl"
                :allow-search="false"
                :allow-customizing-columns="false"
                @refreshing="() => router.reload()"
            >
                <template #cell-title="{ row: item }">
                    <Link
                        :href="item.available_in_selected_site ? item.show_url : item.edit_url"
                        v-text="__(item.title)"
                    />
                </template>
                <template #prepended-row-actions="{ row: item }">
                    <DropdownItem :text="__('Configure')" icon="cog" :href="item.edit_url" />
                </template>
            </Listing>
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
    </div>
</template>
