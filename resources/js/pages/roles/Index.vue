<script setup>
import Head from '@/pages/layout/Head.vue';
import Listing from '@/components/roles/Listing.vue';
import { Header, CommandPaletteItem, Button, DocsCallout } from '@ui';

defineProps({
    roles: Array,
    columns: Array,
    createUrl: String,
});
</script>

<template>
    <Head :title="__('Roles & Permissions')" />

    <Header :title="__('Roles & Permissions')" icon="permissions">
        <CommandPaletteItem
            category="Actions"
            :text="__('Create Role')"
            :url="createUrl"
            icon="permissions"
            prioritize
            v-slot="{ text, url }"
        >
            <Button
                :text="text"
                :href="url"
                variant="primary"
            />
        </CommandPaletteItem>
    </Header>

    <Listing :initial-rows="roles" :initial-columns="columns" />

    <DocsCallout :topic="__('Roles & Permissions')" url="users#permissions" />
</template>
