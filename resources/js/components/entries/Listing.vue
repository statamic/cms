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

                        <data-list-search class="h-8" @keydown.f="showFilters = true" v-if="showFilters" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />

                        <data-list-filter-presets
                            v-show="!reordering && ! showFilters"
                            ref="presets"
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
                    <div v-show="!reordering && showFilters">
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
                        :allow-bulk-actions="!reordering"
                        :loading="loading"
                        :reorderable="reordering"
                        :sortable="!reordering"
                        :toggle-selection-on-row-click="true"
                        @sorted="sorted"
                        @reordered="reordered"
                    >
                        <template slot="cell-title" slot-scope="{ row: entry }">
                            <div class="flex items-center">
                                <div class="little-dot mr-2" :class="getStatusClass(entry)" />
                                <a :href="entry.edit_url" @click.stop>{{ entry.title }}</a>
                            </div>
                        </template>
                        <template slot="cell-slug" slot-scope="{ row: entry }">
                            <span class="font-mono text-2xs">{{ entry.slug }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: entry, index }">
                            <dropdown-list>
                                <dropdown-item :text="__('View')" :external-link="entry.permalink" v-if="entry.viewable && entry.permalink" />
                                <dropdown-item :text="__('Edit')" :redirect="entry.edit_url" v-if="entry.editable" />
                                <div class="divider" v-if="entry.actions.length" />
                                <data-list-inline-actions
                                    :item="entry.id"
                                    :url="actionUrl"
                                    :actions="entry.actions"
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
                    :per-page="perPage"
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
        collection: String,
        reordering: Boolean,
        reorderUrl: String,
        site: String,
    },

    data() {
        return {
            listingKey: 'entries',
            preferencesPrefix: `collections.${this.collection}`,
            requestUrl: cp_url(`collections/${this.collection}/entries`),
            currentSite: this.site,
            initialSite: this.site,
        }
    },

    computed: {
        actionContext() {
            return {collection: this.collection};
        },
    },

    watch: {

        reordering(reordering, wasReordering) {
            if (reordering === wasReordering) return;
            reordering ? this.reorder() : this.cancelReordering();
        },

        activeFilters: {
            deep: true,
            handler(filters) {
                this.currentSite = filters.site ? filters.site.site : null;
            }
        },

        site(site) {
            this.currentSite = site;
        },

        currentSite(site) {
            this.setSiteFilter(site);
            this.$emit('site-changed', site);
        }

    },

    methods: {

        getStatusClass(entry) {
            // TODO: Replace with `entry.status` (will need to pass down)
            if (entry.published && entry.private) {
                return 'bg-transparent border border-gray-600';
            } else if (entry.published) {
                return 'bg-green';
            } else {
                return 'bg-gray-400';
            }
        },

        reorder() {
            this.filtersReset();

            // When reordering, we *need* a site, since mixing them up would be awkward.
            // If we're dealing with multiple sites, it's possible the user "cleared"
            // the site filter so we'll want to fall back to the initial site.
            this.setSiteFilter(this.currentSite || this.initialSite);

            this.page = 1;
            this.sortColumn = 'order';
        },

        cancelReordering() {
            this.request();
        },

        reordered(items) {
            this.items = items;
        },

        setSiteFilter(site) {
            this.filterChanged({ handle: 'site', values: { site }});
        },

        saveOrder() {
            const payload = {
                ids: this.items.map(item => item.id),
                page: this.page,
                perPage: this.perPage,
                site: this.currentSite
            };

            this.$axios.post(this.reorderUrl, payload)
                .then(response => {
                    this.$emit('reordered');
                    this.$toast.success(__('Entries successfully reordered'))
                })
                .catch(e => {
                    console.log(e);
                    this.$toast.error(__('Something went wrong'));
                });
        },
    }

}
</script>
