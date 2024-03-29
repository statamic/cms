<template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <slot name="no-results" v-if="!loading && !searchQuery && items.length === 0" />

        <data-list
            v-else-if="!initializing"
            :columns="columns"
            :rows="items"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            @visible-columns-updated="visibleColumns = $event"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card overflow-hidden p-0 relative">
                    <div class="flex flex-wrap items-center justify-between p-2 text-sm border-b">
                        <data-list-search class="h-8 min-w-[240px] w-full" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />
                        <data-list-column-picker class="rtl:mr-2 ltr:ml-2" :preferences-key="preferencesKey('columns')" />
                    </div>

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        :context="actionContext"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />

                    <data-list-table
                        v-if="items.length"
                        :allow-bulk-actions="true"
                        :allow-column-picker="true"
                        :column-preferences-key="preferencesKey('columns')"
                        @sorted="sorted"
                    >
                        <template slot="cell-datestamp" slot-scope="{ row: submission, value }">
                            <a :href="submission.url" class="text-blue">{{ value }}</a>
                        </template>
                        <template slot="actions" slot-scope="{ row: submission, index }">
                            <dropdown-list>
                                <dropdown-item :text="__('View')" :redirect="submission.url" />
                                <data-list-inline-actions
                                    :item="submission.id"
                                    :url="actionUrl"
                                    :actions="submission.actions"
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
        form: String
    },

    data() {
        return {
            listingKey: 'submissions',
            preferencesPrefix: `forms.${this.form}`,
            requestUrl: cp_url(`forms/${this.form}/submissions`),
        }
    },

    computed: {
        actionContext() {
            return {form: this.form};
        },
    },

}
</script>
