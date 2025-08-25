<template>
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
        @request-completed="requestComplete"
    >
        <template #cell-title="{ row: term }">
            <div class="flex items-center">
                <a :href="term.edit_url">{{ term.title }}</a>
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
</template>

<script>
import { DropdownItem, Listing } from '@/components/ui';

export default {
    components: {
        Listing,
        DropdownItem,
    },

    props: {
        taxonomy: String,
        actionUrl: String,
        sortColumn: String,
        sortDirection: String,
        columns: Array,
        filters: Array,
        canCreate: Boolean,
        createUrl: String,
        taxonomyEditUrl: String,
        taxonomyBlueprintsUrl: String,
        deleteTaxonomyAction: Function, // TODO: Bleh. The resource deleter is in blade, should we have a View.vue like collections?
    },

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
        requestComplete({ items, parameters }) {
            this.items = items;
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
                when: () => Statamic.$permissions.has(`edit ${this.taxonomy} taxonomy`),
                category: Statamic.$commandPalette.category.Actions,
                text: __('Configure Taxonomy'),
                icon: 'cog',
                url: this.taxonomyEditUrl,
            });

            Statamic.$commandPalette.add({
                when: () => Statamic.$permissions.has('configure fields'),
                category: Statamic.$commandPalette.category.Actions,
                text: __('Edit Blueprints'),
                icon: 'blueprint-edit',
                url: this.taxonomyBlueprintsUrl,
            });

            Statamic.$commandPalette.add({
                when: () => Statamic.$permissions.has(`delete ${this.taxonomy} taxonomy`),
                category: Statamic.$commandPalette.category.Actions,
                text: __('Delete Taxonomy'),
                icon: 'trash',
                action: this.deleteTaxonomyAction,
            });
        },
    },
};
</script>
