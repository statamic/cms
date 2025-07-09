<script setup>
import { computed } from 'vue';
import {
    Widget,
    StatusIndicator,
    Listing,
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

function columnShowing(column) {
    return cols.value.find((c) => c.field === column);
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
            <Widget v-bind="widgetProps"><Icon name="loading" /></Widget>
        </template>
        <template #default="{ items, loading }">
            <Widget v-bind="widgetProps">
                <ui-description v-if="!items.length" class="flex-1 flex items-center justify-center">
                    {{ __('There are no entries in this collection') }}
                </ui-description>
                <div class="px-4 py-3">
                    <table class="w-full [&_td]:p-0.5 [&_td]:text-sm " :class="{ 'opacity-50': loading }">
                        <TableBody>
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
