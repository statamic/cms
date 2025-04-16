<script>
import Listing from '../Listing.vue'

export default {

    mixins: [Listing],

    props: {
        additionalColumns: Array,
        collection: String,
        title: String,
    },

    data() {
        return {
            cols: [{ label: 'Title', field: 'title', visible: true }, ...this.additionalColumns],
            listingKey: 'entries',
            requestUrl: cp_url(`collections/${this.collection}/entries`),
        }
    },

    methods: {
        columnShowing(column) {
            return this.cols.find((c) => c.field === column)
        },
    },
}
</script>

<template>
    <ui-widget :title="title" icon="collections">
        <data-list
            v-if="!initializing && items.length"
            :rows="items"
            :columns="cols"
            :sort="false"
        >
            <div v-if="initializing" class="loading">
                <loading-graphic />
            </div>

            <data-list-table v-else :loading="loading" unstyled class="[&_td]:text-sm [&_td]:p-0.5 [&_thead]:hidden">
                <template #cell-title="{ row: entry }">
                    <div class="flex items-center gap-2">
                        <ui-status-indicator v-if="!columnShowing('status')" :status="entry.status" />
                        <a :href="entry.edit_url" class="overflow-hidden text-ellipsis line-clamp-1">{{ entry.title }}</a>
                    </div>
                </template>
                <template #cell-status="{ row: entry }">
                    <ui-status-indicator :status="entry.status" :show-dot="false" show-label />
                </template>
            </data-list-table>
        </data-list>

        <p v-if="!initializing && !items.length" class="p-3 text-center text-sm text-gray-600">
            {{ __('There are no entries in this collection') }}
        </p>

        <template #actions>
            <data-list-pagination
                v-if="meta.last_page != 1"
                :resource-meta="meta"
                @page-selected="selectPage"
                :scroll-to-top="false"
                :show-page-links="false"
            />
            <slot name="actions" />
        </template>
    </ui-widget>
</template>
