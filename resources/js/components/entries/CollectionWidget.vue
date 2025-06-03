<script>
import Listing from '../Listing.vue';
import { Widget, StatusIndicator } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        StatusIndicator,
        Widget,
    },

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

<template>
    <Widget :title="title" icon="collections">
        <data-list v-if="!initializing && items.length" :rows="items" :columns="cols" :sort="false">
            <div v-if="initializing" class="loading">
                <loading-graphic />
            </div>

            <data-list-table v-else :loading="loading" unstyled class="[&_td]:p-0.5 [&_td]:text-sm [&_thead]:hidden">
                <template #cell-title="{ row: entry }">
                    <div class="flex items-center gap-2">
                        <StatusIndicator v-if="!columnShowing('status')" :status="entry.status" />
                        <a :href="entry.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis">{{
                            entry.title
                        }}</a>
                    </div>
                </template>
                <template #cell-status="{ row: entry }">
                    <StatusIndicator :status="entry.status" :show-dot="false" show-label />
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
    </Widget>
</template>
