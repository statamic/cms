<script setup>
import Listing from '@/components/users/Listing.vue';
import { DocsCallout, Header, Button, CommandPaletteItem } from '@ui';
import Head from '@/pages/layout/Head.vue';

defineProps({
    filters: Object,
    sortColumn: String,
    sortDirection: String,
    actionUrl: String,
    createUrl: String,
    editBlueprintUrl: String,
    canCreate: Boolean,
    canConfigureFields: Boolean,
});
</script>

<template>
    <Head :title="__('Users')" />

    <Header :title="__('Users')" icon="users">
        <CommandPaletteItem
            v-if="canConfigureFields"
            category="actions"
            :text="__('Edit User Blueprint')"
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
            :text="__('Create User')"
            :url="createUrl"
            icon="users"
            v-slot="{ text, url }"
        >
            <Button :text="text" :href="url" variant="primary" />
        </CommandPaletteItem>
    </Header>

    <Listing
        :initial-sort-column="sortColumn"
        :initial-sort-direction="sortDirection"
        :filters="filters"
        :action-url="actionUrl"
    />

    <DocsCallout :topic="__('Users')" url="users" />
</template>
