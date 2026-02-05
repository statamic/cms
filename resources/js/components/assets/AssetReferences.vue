<template>
    <div v-if="!isLoaded" class="flex items-center justify-center">
        <ui-button @click="loadReferences" :text="__('View Usage')" variant="filled" size="sm" />
    </div>
    <div v-else>
        <Listing
            :url="url"
            :columns="columns"
            :per-page="5"
            :show-pagination-totals="false"
            :show-pagination-page-links="false"
            :show-pagination-per-page-selector="false"
            :allow-search="false"
            :allow-customizing-columns="false"
        >
            <template #initializing>
                <div class="flex flex-col gap-[8px] justify-between py-1 px-5">
                    <ui-skeleton class="h-[18px] w-full" />
                    <ui-skeleton class="h-[18px] w-full" />
                    <ui-skeleton class="h-[18px] w-full" />
                </div>
            </template>
            <template #default="{ items }">
                <ui-description v-if="items?.length" :text="__('In Use')" class="mb-3" />
                <table v-if="items?.length" class="w-full [&_td]:px-0.75 [&_td]:py-1 [&_td]:text-sm">
                    <ListingTableHead sr-only />
                    <ListingTableBody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template #cell-title="{ row: entry }">
                            <div class="flex items-center gap-2">
                                <StatusIndicator :status="entry.status" />
                                <Link :href="entry.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis">{{ entry.title }}</Link>
                            </div>
                        </template>
                        <template #cell-collection="{ row: entry }">
                            <div class="text-end">
                                <ui-badge :text="entry.collection?.title" size="sm" />
                            </div>
                        </template>
                    </ListingTableBody>
                </table>
                <ui-subheading v-else class="text-center h-full flex items-center justify-center py-4">
                    {{ __('This asset is not being used anywhere.') }}
                </ui-subheading>
            </template>
        </Listing>
    </div>
</template>

<script>
import {
    StatusIndicator,
    Listing,
    ListingTableHead,
    ListingTableBody,
} from '@/components/ui';
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        StatusIndicator,
        Listing,
        ListingTableHead,
        ListingTableBody,
        Link,
    },

    props: {
        assetId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            isLoaded: false,
        };
    },

    computed: {
        url() {
            return cp_url(`assets/${utf8btoa(this.assetId)}/references`);
        },

        columns() {
            return [
                { label: 'Title', field: 'title', visible: true },
                { label: 'Collection name', field: 'collection', visible: true },
            ];
        },
    },

    methods: {
        loadReferences() {
            this.isLoaded = true;
        },
    },
};
</script>
