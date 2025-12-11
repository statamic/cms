<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, DocsCallout, Listing, DropdownItem, CommandPaletteItem, Subheading } from '@ui';
import FieldsetDeleter from '@/components/fieldsets/FieldsetDeleter.vue';
import FieldsetResetter from '@/components/fieldsets/FieldsetResetter.vue';

defineProps(['fieldsets', 'createUrl']);

const deleters = ref({});
const resetters = ref({});

const columns = ref([
    { label: __('Title'), field: 'title' },
    { label: __('Handle'), field: 'handle', width: '25%' },
    { label: __('Fields'), field: 'fields', width: '15%' },
]);

const reloadPage = () => router.reload();
</script>

<template>
    <Head :title="__('Fieldsets')" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('Fieldsets')" icon="fieldsets">
            <CommandPaletteItem
                category="Actions"
                :text="__('Create Fieldset')"
                icon="fieldsets"
                :url="createUrl"
                v-slot="{ text, url }"
            >
                <Button :href="url" :text="text" variant="primary" />
            </CommandPaletteItem>
        </Header>

        <section class="space-y-6 starting-style-transition-children">
            <div v-for="(rows, key) in fieldsets" :key="key" class="mb-4">
                <Subheading v-if="Object.keys(fieldsets).length > 1" size="lg" class="mb-2" :text="key" />

                <Listing
                    :items="rows"
                    :columns="columns"
                    :allow-search="false"
                    :allow-customizing-columns="false"
                    @refreshing="reloadPage"
                >
                    <template #cell-title="{ row: fieldset }">
                        <Link :href="fieldset.edit_url" v-text="__(fieldset.title)" />
                        <fieldset-resetter :ref="el => resetters[fieldset.id] = el" :resource="fieldset" reload />
                        <fieldset-deleter :ref="el => deleters[fieldset.id] = el" :resource="fieldset" reload />
                    </template>
                    <template #cell-handle="{ value }">
                        <span class="font-mono text-xs" v-text="value" />
                    </template>
                    <template #prepended-row-actions="{ row: fieldset }">
                        <DropdownItem :text="__('Edit')" icon="edit" :href="fieldset.edit_url" />
                        <DropdownItem
                            v-if="fieldset.is_resettable"
                            :text="__('Reset')"
                            icon="history"
                            variant="destructive"
                            @click="resetters[fieldset.id].confirm()"
                        />
                        <DropdownItem
                            v-if="fieldset.is_deletable"
                            :text="__('Delete')"
                            icon="trash"
                            variant="destructive"
                            @click="deleters[fieldset.id].confirm()"
                        />
                    </template>
                </Listing>
            </div>
        </section>

        <DocsCallout :topic="__('Blueprints')" url="blueprints" />
    </div>
</template>
