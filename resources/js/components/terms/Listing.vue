<template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            ref="dataList"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            @visible-columns-updated="visibleColumns = $event"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card p-0 relative">
                    <data-list-filter-presets
                        ref="presets"
                        :active-preset="activePreset"
                        :preferences-prefix="preferencesPrefix"
                        @selected="selectPreset"
                        @reset="filtersReset"
                    />
                    <div class="data-list-header">
                        <data-list-filters
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :saves-presets="true"
                            :preferences-prefix="preferencesPrefix"
                            @filter-changed="filterChanged"
                            @search-changed="searchChanged"
                            @saved="$refs.presets.setPreset($event)"
                            @deleted="$refs.presets.refreshPresets()"
                            @restore-preset="$refs.presets.viewPreset($event)"
                            @reset="filtersReset"
                        />
                    </div>

                    <div v-show="items.length === 0" class="p-3 text-center text-grey-50" v-text="__('No results')" />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        :context="actionContext"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />

                    <data-list-table
                        v-show="items.length"
                        :loading="loading"
                        :allow-bulk-actions="true"
                        :allow-column-picker="true"
                        :column-preferences-key="preferencesKey('columns')"
                        @sorted="sorted"
                    >
                        <template slot="cell-title" slot-scope="{ row: term }">
                            <div class="flex items-center">
                                <a :href="term.edit_url">{{ term.title }}</a>
                            </div>
                        </template>
                        <template slot="cell-slug" slot-scope="{ row: term }">
                            <span class="font-mono text-2xs">{{ term.slug }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: term, index }">
                            <dropdown-list>
                                <dropdown-item :text="__('View')" :redirect="term.permalink" />
                                <dropdown-item :text="__('Edit')" :redirect="term.edit_url" />
                                <div class="divider" />
                                <data-list-inline-actions
                                    :item="term.id"
                                    :url="actionUrl"
                                    :actions="term.actions"
                                    @started="actionStarted"
                                    @completed="actionCompleted"
                                />
                            </dropdown-list>
                        </template>
                    </data-list-table>
                </div>
                <data-list-pagination
                    class="mt-3"
                    :resource-meta="meta"
                    :show-totals="true"
                    @page-selected="selectPage"
                    @per-page-changed="changePerPage"
                />
            </div>
        </data-list>

    </div>
</template>

<script>
import Listing from '../Listing.vue';

export default {

    mixins: [Listing],

    props: {
        taxonomy: String,
    },

    data() {
        return {
            listingKey: 'terms',
            preferencesPrefix: `taxonomies.${this.taxonomy}`,
            requestUrl: cp_url(`taxonomies/${this.taxonomy}/terms`),
        }
    },

    computed: {
        actionContext() {
            return {taxonomy: this.taxonomy};
        },
    },

    methods: {
        preferencesKey(type) {
            return `taxonomies.${this.taxonomy}.${type}`;
        },
    }

}
</script>
