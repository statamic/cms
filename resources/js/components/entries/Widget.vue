<template>
    <ui-card inset>
        <data-list
            v-if="!initializing && items.length"
            :rows="items"
            :columns="cols"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            class="flex h-full flex-col justify-between"
        >
            <div>
                <header class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <ui-icon name="collections" class="size-5 text-gray-500" />
                        <span v-text="title" />
                    </div>
                    <div class="flex items-center gap-4">
                        <data-list-pagination
                            v-if="meta.last_page != 1"
                            :resource-meta="meta"
                            @page-selected="selectPage"
                            :scroll-to-top="false"
                            :show-page-links="false"
                        />
                        <slot name="actions"></slot>
                    </div>
                </header>
                <div class="p-4">
                    <div v-if="initializing" class="loading">
                        <loading-graphic />
                    </div>
                    <data-list-table :loading="loading" unstyled class="[&_td]:text-sm [&_td]:p-0.5 [&_thead]:hidden">
                        <template #cell-title="{ row: entry }">
                            <div class="flex items-center gap-2">
                                <StatusIndicator v-if="!columnShowing('status')" :status="entry.status" />
                                <a :href="entry.edit_url">{{ entry.title }}</a>
                            </div>
                        </template>
                        <template #cell-status="{ row: entry }">
                            <StatusIndicator :status="entry.status" :show-dot="false" show-label />
                        </template>
                    </data-list-table>
                </div>
            </div>
        </data-list>

        <p v-if="!initializing && !items.length" class="p-3 text-center text-sm text-gray-600">
            {{ __('There are no entries in this collection') }}
        </p>
    </ui-card>
</template>

<script>
import Listing from '../Listing.vue';
import StatusIndicator from '../ui/StatusIndicator.vue';

export default {
    components: {
        StatusIndicator
    },

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
        };
    },

    methods: {
        columnShowing(column) {
            return this.cols.find((c) => c.field === column);
        },
    },
};
</script>
