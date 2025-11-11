<script>
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Listing } from '@ui';
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Link,
        Head,
        Header,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        Listing,
    },

    props: [
        'taxonomy',
        'taxonomyTitle',
        'blueprints',
        'site',
        'columns',
        'filters',
        'canCreate',
        'createUrl',
        'actionUrl',
        'sortColumn',
        'sortDirection',
        'taxonomyEditUrl',
        'taxonomyBlueprintsUrl',
        'canEdit',
        'canDelete',
        'canConfigureFields',
        'deleteUrl',
        'createLabel',
    ],

    data() {
        return {
            preferencesPrefix: `taxonomies.${this.taxonomy}`,
            requestUrl: cp_url(`taxonomies/${this.taxonomy}/terms`),
        };
    },

    mounted() {
        this.addToCommandPalette();
    },

    methods: {
        deleteTaxonomy() {
            this.$refs.deleter.confirm();
        },

        addToCommandPalette() {
            Statamic.$commandPalette.add({
                when: () => this.canCreate,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Create Term'),
                icon: 'taxonomies',
                url: this.createUrl,
                prioritize: true,
            });

            Statamic.$commandPalette.add({
                when: () => this.canEdit,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Configure Taxonomy'),
                icon: 'cog',
                url: this.taxonomyEditUrl,
            });

            Statamic.$commandPalette.add({
                when: () => this.canConfigureFields,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Edit Blueprints'),
                icon: 'blueprint-edit',
                url: this.taxonomyBlueprintsUrl,
            });

            Statamic.$commandPalette.add({
                when: () => this.canDelete,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Delete Taxonomy'),
                icon: 'trash',
                action: () => this.deleteTaxonomy(),
            });
        },
    },
};
</script>

<template>
    <div>
        <Head :title="taxonomyTitle" />

        <Header :title="__(taxonomyTitle)">
            <Dropdown>
                <DropdownMenu>
                    <DropdownItem v-if="canEdit" :text="__('Configure Taxonomy')" icon="cog" :href="taxonomyEditUrl" />
                    <DropdownItem v-if="canConfigureFields" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="taxonomyBlueprintsUrl" />
                    <DropdownItem v-if="canDelete" :text="__('Delete Taxonomy')" icon="trash" variant="destructive" @click="deleteTaxonomy()" />
                </DropdownMenu>
            </Dropdown>

            <create-term-button
                v-if="canCreate"
                :url="createUrl"
                :text="createLabel"
                :blueprints="blueprints"
            />
        </Header>

        <resource-deleter
            v-if="canDelete"
            ref="deleter"
            :resource-title="taxonomyTitle"
            :route="deleteUrl"
            redirect="/cp/taxonomies"
        />

        <Listing
            ref="listing"
            :url="requestUrl"
            :columns="columns"
            :action-url="actionUrl"
            :action-context="{ taxonomy }"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            :preferences-prefix="preferencesPrefix"
            :filters="filters"
            push-query
        >
            <template #cell-title="{ row: term }">
                <div class="flex items-center">
                    <Link :href="term.edit_url">{{ term.title }}</Link>
                </div>
            </template>
            <template #cell-slug="{ row: term }">
                <span class="text-2xs font-mono">{{ term.slug }}</span>
            </template>
            <template #prepended-row-actions="{ row: term }">
                <DropdownItem :text="__('Visit URL')" :href="term.permalink" target="_blank" icon="eye" />
                <DropdownItem :text="__('Edit')" :href="term.edit_url" icon="edit" />
            </template>
        </Listing>
    </div>
</template>
