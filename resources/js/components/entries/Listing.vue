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
                    <div v-if="!reordering" class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b dark:border-dark-900">

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
                    <div v-show="!reordering">
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
                    </div>

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        :context="actionContext"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                    <div class="overflow-x-auto overflow-y-hidden">
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
                                <a class="title-index-field inline-flex items-center" :href="entry.edit_url" @click.stop>
                                    <span class="little-dot rtl:ml-2 ltr:mr-2" v-tooltip="getStatusLabel(entry)" :class="getStatusClass(entry)" v-if="! columnShowing('status')" />
                                    <span v-text="entry.title" />
                                </a>
                            </template>
                            <template slot="cell-status" slot-scope="{ row: entry }">
                                <div class="status-index-field select-none" v-tooltip="getStatusTooltip(entry)" :class="`status-${entry.status}`" v-text="getStatusLabel(entry)" />
                            </template>
                            <template slot="cell-slug" slot-scope="{ row: entry }">
                                <div class="slug-index-field" :title="entry.slug">{{ entry.slug }}</div>
                            </template>
                            <template slot="actions" slot-scope="{ row: entry, index }">
                                <dropdown-list placement="left-start">
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
            pushQuery: true,
            previousFilters: null,
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
                return 'bg-green-600';
            } else {
                return 'bg-gray-400 dark:bg-dark-200';
            }
        },

        getStatusLabel(entry) {
            if (entry.status === 'published') {
                return __('Published');
            } else if (entry.status === 'scheduled') {
                return __('Scheduled');
            } else if (entry.status === 'expired') {
                return __('Expired');
            } else if (entry.status === 'draft') {
                return __('Draft');
            }
        },

        getStatusTooltip(entry) {
            if (entry.status === 'published') {
                return entry.collection.dated
                    ? __('messages.status_published_with_date', {date: entry.date})
                    : null; // The label is sufficient.
            } else if (entry.status === 'scheduled') {
                return __('messages.status_scheduled_with_date', {date: entry.date})
            } else if (entry.status === 'expired') {
                return __('messages.status_expired_with_date', {date: entry.date})
            } else if (entry.status === 'draft') {
                return null; // The label is sufficient.
            }
        },

        reorder() {
            this.previousFilters = this.activeFilters;
            this.filtersReset();

            // When reordering, we *need* a site, since mixing them up would be awkward.
            // If we're dealing with multiple sites, it's possible the user "cleared"
            // the site filter so we'll want to fall back to the initial site.
            this.setSiteFilter(this.currentSite || this.initialSite);

            this.page = 1;
            this.sortColumn = 'order';
            this.sortDirection = 'asc';
        },

        cancelReordering() {
            this.resetToPreviousFilters();

            this.request();
        },

        columnShowing(column) {
            return this.visibleColumns.find(c => c.field === column);
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

        resetToPreviousFilters() {
            this.filtersReset();

            if (this.previousFilters) this.filtersChanged(this.previousFilters);

            this.previousFilters = null;
        }
    }

}
</script>
