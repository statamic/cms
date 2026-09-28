<script setup>
import {
    Icon,
    EmptyStateMenu,
    EmptyStateItem,
    DocsCallout,
    Header,
    Button,
    CommandPaletteItem,
    DropdownItem,
    Listing,
} from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';
import Head from '@/pages/layout/Head.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    groups: Array,
    createUrl: String,
    editBlueprintUrl: String,
    canCreate: Boolean,
    canConfigureFields: Boolean,
});

if (props.groups.length === 0) useArchitecturalBackground();

const columns = ref([
    { label: __('Title'), field: 'title' },
    { label: __('Handle'), field: 'handle' },
    { label: __('Users'), field: 'users' },
    { label: __('Roles'), field: 'roles' },
]);

const reloadPage = () => router.reload();
</script>

<template>
    <Head :title="__('User Groups')" />

    <div class="max-w-page mx-auto">
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

            <Listing
                :items="groups"
                :columns="columns"
                :allow-search="false"
                :allow-customizing-columns="false"
                @refreshing="reloadPage"
            >
                <template #cell-title="{ row: group }">
                    <Link :href="group.show_url">{{ __(group.title) }}</Link>
                    <resource-deleter :ref="`deleter_${group.id}`" :resource="group" reload />
                </template>
                <template #cell-handle="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template #prepended-row-actions="{ row: group }">
                    <DropdownItem :text="__('View')" icon="eye" :href="group.show_url" />
                    <DropdownItem :text="__('Configure')" icon="cog" :href="group.edit_url" />
                    <DropdownItem
                        :text="__('Delete')"
                        icon="trash"
                        variant="destructive"
                        @click="$refs[`deleter_${group.id}`].confirm()"
                    />
                </template>
            </Listing>
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
    </div>
</template>
