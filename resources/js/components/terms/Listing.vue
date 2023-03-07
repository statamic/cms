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
                    <div class="flex items-center justify-between p-2 text-sm border-b">

                        <data-list-search class="h-8" v-if="showFilters" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />

                        <data-list-filter-presets
                            ref="presets"
                            v-show="! showFilters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                            @hide-filters="filtersHide"
                            @show-filters="filtersShow"
                        />
                        <div class="flex ml-2 space-x-2">
                            <button class="btn py-1 px-2 h-8" v-text="__('Cancel')" v-show="showFilters" @click="filtersHide" />
                            <button class="btn py-1 px-2 h-8" v-text="__('Save')" v-show="showFilters && isDirty" @click="$refs.presets.savePreset()" />
                            <button class="btn flex items-center py-1 px-2 h-8" @click="showFilters = true" v-if="! showFilters" v-tooltip="__('Show Filter Controls')">
                                <svg-icon name="search" class="w-4 h-4" />
                                <svg-icon name="filter-lines" class="w-4 h-4" />
                            </button>
                            <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                        </div>
                    </div>
                    <div v-show="showFilters">
                        <data-list-filters
                            ref="filters"
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :is-searching="showFilters"
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

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

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
                    class="mt-6"
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
