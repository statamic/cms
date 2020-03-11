<template>
    <div>
        <div v-if="initializing" class="loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
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
            cols: [{ label: "Title", field: "title", visible: true }],
            listingKey: 'entries',
            requestUrl: cp_url(`collections/${this.collection}/entries`),
        }
    }

}
</script>
