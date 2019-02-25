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
                        <data-list-filters
                            :filters="filters"
                            :active-filters="activeFilters"
                            :per-page="perPage"
                            @filters-changed="filtersChanged"
                            @per-page-changed="perPageChanged" />
                        <data-list-column-picker :save-url="saveColumnsUrl" />
                    </div>
                    <data-list-table :loading="loading" :allow-bulk-actions="true" @sorted="sorted">
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
                                    <div class="li"><a :href="entry.permalink">View</a></div>
                                    <div class="li"><a :href="entry.edit_url">Edit</a></div>
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
                    <data-list-bulk-actions
                        class="rounded-b"
                        :url="actionUrl"
                        :actions="actions"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
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
    },

    data() {
        return {
            listingKey: 'entries',
            requestUrl: cp_url(`collections/${this.collection}/entries`),
            saveColumnsUrl: cp_url(`collections/${this.collection}/entries/columns`),
        }
    }

}
</script>
