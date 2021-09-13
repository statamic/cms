<template>
    <div>
        <div v-if="initializing" class="loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing && items.length"
            :rows="items"
            :columns="cols"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ }">
                <data-list-table :loading="loading">
                    <template slot="cell-title" slot-scope="{ row: entry }">
                        <div class="flex items-center">
                            <div class="little-dot mr-1" :class="[entry.published ? 'bg-green' : 'bg-grey-40']" />
                            <a :href="entry.edit_url">{{ entry.title }}</a>
                        </div>
                    </template>
                </data-list-table>
                <data-list-pagination
                    v-if="meta.last_page != 1"
                    class="py-1 border-t bg-grey-20 rounded-b-lg text-sm"
                    :resource-meta="meta"
                    @page-selected="selectPage"
                    :scroll-to-top="false"
                />
            </div>
        </data-list>

        <p v-else-if="!initializing && !items.length" class="p-2 pt-1 text-sm text-grey-50">
            {{ __('There are no entries in this site') }}
        </p>

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
            cols: [{ label: "Title", field: "title", visible: true }],
            listingKey: 'entries',
            requestUrl: cp_url(`collections/${this.collection}/entries`),
        }
    },

}
</script>
