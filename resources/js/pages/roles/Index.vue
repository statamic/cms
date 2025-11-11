<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, CommandPaletteItem, Button, DocsCallout, DropdownItem, Listing } from '@ui';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    roles: Array,
    columns: Array,
    createUrl: String,
});

const reloadPage = () => router.reload();
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

    <Listing
        :items="roles"
        :columns="columns"
        :allow-search="false"
        :allow-customizing-columns="false"
        @refreshing="reloadPage"
    >
        <template #cell-title="{ row: role, index }">
            <Link :href="role.edit_url">{{ __(role.title) }}</Link>

            <resource-deleter
                :ref="`deleter_${role.id}`"
                :resource="role"
                requires-elevated-session
                reload
            />
        </template>
        <template #cell-handle="{ value: handle }">
            <span class="font-mono text-xs">{{ handle }}</span>
        </template>
        <template #prepended-row-actions="{ row: role }">
            <DropdownItem :text="__('Configure')" icon="cog" :href="role.edit_url" />
            <DropdownItem
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
                @click="$refs[`deleter_${role.id}`].confirm()"
            />
        </template>
    </Listing>

    <DocsCallout :topic="__('Roles & Permissions')" url="users#permissions" />
</template>
