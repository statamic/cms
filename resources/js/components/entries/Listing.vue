<template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            :rows="items"
            :columns="columns"
            :search="false"
            :search-query="searchQuery"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ }">
                <div class="card p-0">
                    <div class="data-list-header">
                        <data-list-toggle-all ref="toggleAll" />
                        <data-list-search v-model="searchQuery" />
                        <button class="btn btn-flat"
                            v-if="showReorderButton"
                            @click="reorder"
                            v-text="__('Reorder')"
                        />
                        <template v-if="reordering">
                            <button class="btn btn-flat mr-1" @click="saveOrder">Save Order</button>
                            <button class="btn btn-flat" @click="cancelReordering">Cancel</button>
                        </template>
                        <data-list-bulk-actions
                            :url="actionUrl"
                            :actions="entryActions"
                            @started="actionStarted"
                            @completed="actionCompleted"
                        />
                        <data-list-filters
                            :filters="filters"
                            :active-filters="activeFilters"
                            :per-page="perPage"
                            :preferences-key="preferencesKey('filters')"
                            @filters-changed="filtersChanged"
                            @per-page-changed="perPageChanged" />
                        <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                    </div>

                    <div v-show="items.length === 0" class="p-3 text-center text-grey-50" v-text="__('No results')" />

                    <data-list-table
                        v-show="items.length"
                        :loading="loading"
                        :allow-bulk-actions="true"
                        :sortable="!reordering"
                        :reorderable="reordering"
                        @sorted="sorted"
                        @reordered="reordered"
                    >
                        <template slot="cell-title" slot-scope="{ row: entry }">
                            <div class="flex items-center">
                                <div class="little-dot mr-1" :class="[entry.published ? 'bg-green' : 'bg-grey-40']" />
                                <a :href="entry.edit_url">{{ entry.title }}</a>
                            </div>
                        </template>
                        <template slot="cell-slug" slot-scope="{ row: entry }">
                            <span class="font-mono text-2xs">{{ entry.slug }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: entry, index }">
                            <dropdown-list>
                                <div class="dropdown-menu">
                                    <div class="li"><a :href="entry.permalink">{{ __('View') }}</a></div>
                                    <div class="li"><a :href="entry.edit_url">{{ __('Edit') }}</a></div>
                                    <div class="li divider" />
                                    <data-list-inline-actions
                                        :item="entry.id"
                                        :url="actionUrl"
                                        :actions="actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </div>
                            </dropdown-list>
                        </template>
                    </data-list-table>
                </div>
                <data-list-pagination
                    class="mt-3"
                    :resource-meta="meta"
                    @page-selected="page = $event"
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
    },

    data() {
        return {
            listingKey: 'entries',
            requestUrl: cp_url(`collections/${this.collection}/entries`),
            reordering: false,
            reorderingRequested: false,
            initialOrder: null,
        }
    },

    computed: {
        entryActions() {
            this.actions.forEach(action => action.context.site = data_get(this.activeFilters, 'site.value', null));

            return this.actions;
        },

        showReorderButton() {
            if (this.structureUrl) return true;

            return this.reorderable && !this.reordering;
        },

        reorderingDisabled() {
            return this.sortColumn !== 'order';
        }
    },

    methods: {
        preferencesKey(type) {
            return `collections.${this.collection}.${type}`;
        },

        afterRequestCompleted(response) {
            if (this.reorderingRequested) this.reorder();
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
            const ids = this.items.map(item => item.id);
            this.$axios.post(this.reorderUrl, { ids });
            this.reordering = false;
        },

        cancelReordering() {
            this.reordering = false;
            this.items = this.initialOrder.map(id => _.findWhere(this.items, { id }));
            this.initialOrder = null;
        },

        reordered(items) {
            this.items = items;
        }
    }

}
</script>
