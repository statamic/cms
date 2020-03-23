<template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
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

                        <template v-if="!hasSelections">
                            <button class="btn-flat ml-1"
                                v-if="showReorderButton"
                                @click="reorder"
                                v-text="__('Reorder')"
                            />
                            <template v-if="reordering">
                                <button class="btn-flat ml-1" @click="saveOrder" v-text="__('Save Order')" />
                                <button class="btn-flat ml-1" @click="cancelReordering" v-text="__('Cancel')" />
                            </template>
                        </template>
                    </div>

                    <div v-show="items.length === 0" class="p-3 text-center text-grey-50" v-text="__('No results')" />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />

                    <data-list-table
                        v-show="items.length"
                        :allow-bulk-actions="true"
                        :loading="loading"
                        :reorderable="reordering"
                        :sortable="!reordering"
                        :toggle-selection-on-row-click="true"
                        :allow-column-picker="true"
                        :column-preferences-key="preferencesKey('columns')"
                        @sorted="sorted"
                        @reordered="reordered"
                    >
                        <template slot="cell-title" slot-scope="{ row: entry }">
                            <div class="flex items-center">
                                <div class="little-dot mr-1" :class="getStatusClass(entry)" />
                                <a :href="entry.edit_url" @click.stop>{{ entry.title }}</a>
                            </div>
                        </template>
                        <template slot="cell-slug" slot-scope="{ row: entry }">
                            <span class="font-mono text-2xs">{{ entry.slug }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: entry, index }">
                            <dropdown-list>
                                <dropdown-item :text="__('View')" :external-link="entry.permalink" v-if="entry.viewable" />
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
                    v-if="! reordering"
                    class="mt-3"
                    :resource-meta="meta"
                    :per-page="perPage"
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
        reorderable: Boolean,
        reorderUrl: String,
        structureUrl: String,
        site: String
    },

    data() {
        return {
            listingKey: 'entries',
            preferencesPrefix: `collections.${this.collection}`,
            requestUrl: cp_url(`collections/${this.collection}/entries`),
            reordering: false,
            reorderingRequested: false,
            initialOrder: null,
        }
    },

    computed: {
        showReorderButton() {
            if (this.structureUrl) return true;
            if (this.hasActiveFilters) return false;

            return this.reorderable && !this.reordering;
        },

        reorderingDisabled() {
            return this.sortColumn !== 'order';
        }
    },

    methods: {

        afterRequestCompleted(response) {
            if (this.reorderingRequested) this.reorder();
        },

        getStatusClass(entry) {
            if (entry.published && entry.private) {
                return 'bg-transparent border border-grey-60';
            } else if (entry.published) {
                return 'bg-green';
            } else {
                return 'bg-grey-40';
            }
        },

        reorder() {
            if (this.structureUrl) {
                window.location = this.structureUrl;
                return;
            }

            // If the listing isn't in order when attempting to reorder, things would get
            // all jumbled up. We'll change the sort order, which triggers an async
            // request. Once it's completed, reordering will be re-triggered.
            if (this.sortColumn !== 'order') {
                this.reorderingRequested = true;
                this.sortColumn = 'order';
                return;
            }

            this.reordering = true;
            this.initialOrder = this.items.map(item => item.id);
            this.reorderingRequested = false;
        },

        saveOrder() {
            const payload = {
                ids: this.items.map(item => item.id),
                page: this.page,
                perPage: this.perPage,
                site: this.site
            };

            this.$axios.post(this.reorderUrl, payload)
                .then(response => {
                    this.reordering = false;
                    this.$toast.success(__('Entries successfully reordered'))
                })
                .catch(e => {
                    this.$toast.error(__('Something went wrong'));
                });
        },

        cancelReordering() {
            this.reordering = false;
            this.items = this.initialOrder.map(id => _.findWhere(this.items, { id }));
            this.initialOrder = null;
        },

        reordered(items) {
            this.items = items;
        },

    }

}
</script>
