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
                        v-if="!reordering"
                        ref="presets"
                        :active-preset="activePreset"
                        :preferences-prefix="preferencesPrefix"
                        @selected="selectPreset"
                        @reset="filtersReset"
                    />
                    <div class="data-list-header" v-if="!reordering">
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
                        :url="bulkActionsUrl"
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
                                    :url="runActionUrl"
                                    :actions="entry.actions"
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
        reordering: Boolean,
        reorderUrl: String,
        site: String,
    },

    data() {
        return {
            listingKey: 'entries',
            preferencesPrefix: `collections.${this.collection}`,
            requestUrl: cp_url(`collections/${this.collection}/entries`),
        }
    },

    watch: {

        reordering(reordering, wasReordering) {
            if (reordering === wasReordering) return;
            reordering ? this.reorder() : this.cancelReordering();
        }

    },

    methods: {

        getStatusClass(entry) {
            // TODO: Replace with `entry.status` (will need to pass down)
            if (entry.published && entry.private) {
                return 'bg-transparent border border-grey-60';
            } else if (entry.published) {
                return 'bg-green';
            } else {
                return 'bg-grey-40';
            }
        },

        reorder() {
            this.filtersReset();
            this.page = 1;
            this.sortColumn = 'order';
        },

        cancelReordering() {
            this.request();
        },

        reordered(items) {
            this.items = items;
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
