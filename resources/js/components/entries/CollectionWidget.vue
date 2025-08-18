<script setup>
import { computed } from 'vue';
import DateFormatter from '@statamic/components/DateFormatter.js';
import {
    Widget,
    StatusIndicator,
    Listing,
    ListingTableHead as TableHead,
    ListingTableBody as TableBody,
    ListingPagination as Pagination,
    Icon,
} from '@statamic/ui';

const props = defineProps({
    additionalColumns: Array,
    collection: String,
    title: String,
    initialPerPage: {
        type: Number,
        default: 5,
    },
    initialSortColumn: {
        type: String,
    },
    initialSortDirection: {
        type: String,
    },
});

const requestUrl = cp_url(`collections/${props.collection}/entries`);

const cols = computed(() => [{ label: 'Title', field: 'title', visible: true }, ...props.additionalColumns]);

const widgetProps = computed(() => ({
    title: props.title,
    icon: 'collections',
}));

function formatDate(value) {
    return DateFormatter.format(value, 'date');
}
</script>

<template>
    <Listing
        :url="requestUrl"
        :columns="cols"
        :per-page="initialPerPage"
        :show-pagination-totals="false"
        :show-pagination-page-links="false"
        :show-pagination-per-page-selector="false"
        :sort-column="initialSortColumn"
        :sort-direction="initialSortDirection"
    >
        <template #initializing>
            <Widget v-bind="widgetProps">
                <div class="flex flex-col gap-[9px] justify-between py-3 px-4">
                    <ui-skeleton class="h-[19px] w-full" />
                    <ui-skeleton class="h-[19px] w-full" />
                    <ui-skeleton class="h-[19px] w-full" />
                    <ui-skeleton class="h-[19px] w-full" />
                    <ui-skeleton class="h-[19px] w-full" />
                </div>
            </Widget>
        </template>
        <template #default="{ items, loading }">
            <Widget v-bind="widgetProps">
                <ui-description v-if="!items.length" class="flex-1 flex items-center justify-center">
                    {{ __('There are no entries in this collection') }}
                </ui-description>
                <div class="px-4 py-3">
                    <table class="w-full [&_td]:p-0.75 [&_td]:text-sm" :class="{ 'opacity-50': loading }">
                        <TableHead sr-only />
                        <TableBody>
                            <template #cell-title="{ row: entry, isColumnVisible }">
                                <div class="flex items-center gap-2">
                                    <StatusIndicator v-if="!isColumnVisible('status')" :status="entry.status" />
                                    <a :href="entry.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis">{{
                                        entry.title
                                    }}</a>
                                </div>
                            </template>
                            <template #cell-date="{ row: entry, isColumnVisible }">
                                <div
                                    class="text-end font-mono text-xs whitespace-nowrap text-gray-500 antialiased px-2"
                                    v-html="formatDate(entry.datestamp)"
                                    v-if="isColumnVisible('date')"
                                />
                            </template>
                            <template #cell-status="{ row: entry }">
                                <StatusIndicator :status="entry.status" :show-dot="false" show-label />
                            </template>
                        </TableBody>
                    </table>
                </div>
                <template #actions>
                    <Pagination />
                    <slot name="actions" />
                </template>
            </Widget>
        </template>
    </Listing>
</template>
