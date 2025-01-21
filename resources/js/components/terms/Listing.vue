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
                <div class="card overflow-hidden p-0 relative">
                    <div class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b dark:border-dark-900">

                        <data-list-filter-presets
                            ref="presets"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                        />

                        <data-list-search class="h-8 mt-2 min-w-[240px] w-full" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />

                        <div class="flex space-x-2 rtl:space-x-reverse mt-2">
                            <button class="btn btn-sm rtl:mr-2 ltr:ml-2" v-text="__('Reset')" v-show="isDirty" @click="$refs.presets.refreshPreset()" />
                            <button class="btn btn-sm rtl:mr-2 ltr:ml-2" v-text="__('Save')" v-show="isDirty" @click="$refs.presets.savePreset()" />
                            <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                        </div>
                    </div>

                    <data-list-filters
                        ref="filters"
                        :filters="filters"
                        :active-preset="activePreset"
                        :active-preset-payload="activePresetPayload"
                        :active-filters="activeFilters"
                        :active-filter-badges="activeFilterBadges"
                        :active-count="activeFilterCount"
                        :search-query="searchQuery"
                        :is-searching="true"
                        :saves-presets="true"
                        :preferences-prefix="preferencesPrefix"
                        @changed="filterChanged"
                        @saved="$refs.presets.setPreset($event)"
                        @deleted="$refs.presets.refreshPresets()"
                    />

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
                            <dropdown-list placement="left-start">
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
            pushQuery: true,
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
