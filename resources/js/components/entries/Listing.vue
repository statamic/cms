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
            <div>
                <div class="space-y-3">

                    <!-- Preset Views/Tabs -->
                    <data-list-filter-presets
                        v-if="!reordering"
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

                    <!-- <div class="flex gap-2">
                        <Button
                            size="sm"
                            v-text="__('Reset')"
                            v-show="isDirty"
                            @click="$refs.presets.refreshPreset()"
                        />
                        <Button
                            size="sm"
                            v-text="__('Save')"
                            v-show="isDirty"
                            @click="$refs.presets.savePreset()"
                        />
                    </div> -->


                    <!-- Search and Filter -->
                    <div class="flex items-center gap-3">
                        <data-list-search ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />
                        <data-list-filters
                            v-show="!reordering"
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
                        <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                    </div>

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        :context="actionContext"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                    <Panel class="relative overflow-x-auto overscroll-x-contain">
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
                            <template #cell-title="{ row: entry }">
                                <a class="title-index-field" :href="entry.edit_url" @click.stop>
                                    <StatusIndicator v-if="!columnShowing('status')" :status="entry.status" />
                                    <span v-text="entry.title" />
                                </a>
                            </template>
                            <template #cell-status="{ row: entry }">
                                <StatusIndicator :status="entry.status" show-label :show-dot="false" />
                            </template>
                            <template #cell-slug="{ row: entry }">
                                <div class="slug-index-field" :title="entry.slug">{{ entry.slug }}</div>
                            </template>
                            <template #actions="{ row: entry, index }">
                                <Dropdown placement="left-start" class="me-3">
                                    <DropdownMenu>
                                        <DropdownLabel :text="__('Actions')" />
                                        <DropdownItem
                                            :text="__('Visit URL')"
                                            :href="entry.permalink"
                                            icon="eye"
                                            v-if="entry.viewable && entry.permalink"
                                        />
                                        <DropdownItem
                                            :text="__('Edit')"
                                            :href="entry.edit_url"
                                            icon="edit"
                                            v-if="entry.editable"
                                        />
                                        <DropdownSeparator v-if="entry.actions.length" />
                                        <data-list-list-actions
                                            :item="entry.id"
                                            :url="actionUrl"
                                            :actions="entry.actions"
                                            @started="actionStarted"
                                            @completed="actionCompleted"
                                        />
                                    </DropdownMenu>
                                </Dropdown>
                            </template>
                        </data-list-table>
                    </Panel>
                </div>
                <data-list-pagination
                    class="mt-3"
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
import {
    Button,
    Panel,
    StatusIndicator,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownLabel,
    DropdownSeparator,
} from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Button,
        Panel,
        StatusIndicator,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
        DropdownSeparator,
    },

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
        };
    },

    computed: {
        actionContext() {
            return { collection: this.collection };
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
            },
        },

        site(site) {
            this.currentSite = site;
        },

        currentSite(site) {
            this.setSiteFilter(site);
            this.$emit('site-changed', site);
        },
    },

    methods: {
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
            return this.visibleColumns.find((c) => c.field === column);
        },

        reordered(items) {
            this.items = items;
        },

        setSiteFilter(site) {
            this.filterChanged({ handle: 'site', values: { site } });
        },

        saveOrder() {
            const payload = {
                ids: this.items.map((item) => item.id),
                page: this.page,
                perPage: this.perPage,
                site: this.currentSite,
            };

            this.$axios
                .post(this.reorderUrl, payload)
                .then((response) => {
                    this.$emit('reordered');
                    this.$toast.success(__('Entries successfully reordered'));
                })
                .catch((e) => {
                    console.log(e);
                    this.$toast.error(__('Something went wrong'));
                });
        },

        resetToPreviousFilters() {
            this.filtersReset();

            if (this.previousFilters) this.filtersChanged(this.previousFilters);

            this.previousFilters = null;
        },
    },
};
</script>
