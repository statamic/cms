<script setup>
import { ref } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, DocsCallout, CommandPaletteItem, Icon, EmptyStateMenu, EmptyStateItem, Listing, DropdownItem } from '@ui';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['taxonomies', 'columns', 'canCreate', 'createUrl']);

const rows = ref(props.taxonomies);

function removeRow(row) {
    const i = rows.value.findIndex((r) => r.id === row.id);
    rows.value.splice(i, 1);
}
</script>

<template>
    <Head :title="__('Taxonomies')" />

    <Header :title="__('Taxonomies')" icon="taxonomies">
        <CommandPaletteItem
            v-if="canCreate"
            category="Actions"
            prioritize
            :text="__('Create Taxonomy')"
            :url="createUrl"
            icon="taxonomies"
            v-slot="{ text, url }"
        >
            <Button
                :text="text"
                :href="url"
                variant="primary"
            />
        </CommandPaletteItem>
    </Header>

    <template v-if="taxonomies.length">
        <Listing :items="rows" :columns="columns" :allow-search="false" :allow-customizing-columns="false">
            <template #cell-title="{ row: taxonomy }">
                <Link :href="taxonomy.terms_url">{{ __(taxonomy.title) }}</Link>

                <resource-deleter :ref="`deleter_${taxonomy.id}`" :resource="taxonomy" @deleted="removeRow(taxonomy)" />
            </template>

            <template #prepended-row-actions="{ row: taxonomy, index }">
                <DropdownItem :text="__('Configure')" icon="cog" :href="taxonomy.edit_url" />
                <DropdownItem :text="__('Edit Blueprints')" icon="blueprint-edit" :href="taxonomy.blueprints_url" />
                <DropdownItem
                    :text="__('Delete Taxonomy')"
                    icon="trash"
                    variant="destructive"
                    @click="$refs[`deleter_${taxonomy.id}`].confirm()"
                />
            </template>
        </Listing>
    </template>

    <template v-else>
        <header class="py-8 mt-8 text-center">
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
                <Icon name="taxonomies" class="size-5 text-gray-500" />
                {{ __('Taxonomies') }}
            </h1>
        </header>

        <EmptyStateMenu :heading="__('statamic::messages.taxonomy_configure_intro')">
            <EmptyStateItem
                :href="createUrl"
                icon="taxonomies"
                :heading="__('Create Taxonomy')"
                :description="__('Get started by creating your first taxonomy.')"
            />
        </EmptyStateMenu>
    </template>

    <DocsCallout :topic="__('Taxonomies')" url="taxonomies" />
</template>
