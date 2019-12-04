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
            <div slot-scope="{ hasSelections }">
                <div class="card p-0">
                    <div class="data-list-header">
                        <data-list-toggle-all ref="toggleAll" />
                        <data-list-search v-model="searchQuery" />
                        <data-list-bulk-actions
                            :url="actionUrl"
                            @started="actionStarted"
                            @completed="actionCompleted"
                        />
                        <template v-if="!hasSelections">
                            <data-list-filters
                                class="ml-1"
                                :filters="filters"
                                :active-filters="activeFilters"
                                :per-page="perPage"
                                :preferences-key="preferencesKey('filters')"
                                @per-page-changed="perPageChanged" />
                            <data-list-column-picker :preferences-key="preferencesKey('columns')" class="ml-1" />
                        </template>
                    </div>

                    <div v-show="items.length === 0" class="p-3 text-center text-grey-50" v-text="__('No results')" />

                    <data-list-table
                        v-show="items.length"
                        :loading="loading"
                        :allow-bulk-actions="true"
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
        taxonomy: String,
    },

    data() {
        return {
            listingKey: 'terms',
            requestUrl: cp_url(`taxonomies/${this.taxonomy}/terms`),
        }
    },

    methods: {
        preferencesKey(type) {
            return `taxonomies.${this.taxonomy}.${type}`;
        },
    }

}
</script>
