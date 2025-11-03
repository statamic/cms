<script setup>
import Listing from '@/components/user-groups/Listing.vue';
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout, Header, Button, CommandPaletteItem } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';
import Head from '@/pages/layout/Head.vue';

const props = defineProps({
    groups: Array,
    createUrl: String,
    editBlueprintUrl: String,
    canCreate: Boolean,
    canConfigureFields: Boolean,
});

if (props.groups.length === 0) useArchitecturalBackground();
</script>

<template>
    <Head :title="__('User Groups')" />

    <template v-if="groups.length">
        <Header :title="__('User Groups')" icon="groups">
            <CommandPaletteItem
                v-if="canConfigureFields"
                category="actions"
                :text="__('Edit User Group Blueprint')"
                :url="editBlueprintUrl"
                icon="blueprint-edit"
                v-slot="{ text, url }"
            >
                <Button :text="text" :href="url" />
            </CommandPaletteItem>

            <CommandPaletteItem
                v-if="canCreate"
                category="actions"
                prioritize
                :text="__('Create User Group')"
                :url="createUrl"
                icon="groups"
                v-slot="{ text, url }"
            >
                <Button :text="text" :href="url" variant="primary" />
            </CommandPaletteItem>
        </Header>

        <Listing :initial-rows="groups" />
    </template>

    <template v-else>
        <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
                <Icon name="groups" class="size-5 text-gray-500" />
                {{ __('User Groups') }}
            </h1>
        </header>

        <EmptyStateMenu :heading="__('statamic::messages.user_groups_intro')">
            <EmptyStateItem
                :href="createUrl"
                icon="groups"
                :heading="__('Create User Group')"
                :description="__('Get started by creating your first user group.')"
            />
        </EmptyStateMenu>
    </template>

    <DocsCallout :topic="__('User Groups')" url="users#user-groups" />
</template>
