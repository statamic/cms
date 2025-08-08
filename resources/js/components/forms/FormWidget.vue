<script setup>
import DateFormatter from '@statamic/components/DateFormatter.js';
import { computed } from 'vue';
import {
    Widget,
    Icon,
    Listing,
    ListingTableHead as TableHead,
    ListingTableBody as TableBody,
    ListingPagination as Pagination,
} from '@statamic/ui';

const props = defineProps({
    form: { type: String, required: true },
    fields: { type: Array, default: () => [] },
    title: { type: String },
    initialPerPage: { type: Number, default: 5 },
});

const requestUrl = cp_url(`forms/${props.form}/submissions`);

const cols = computed(() => [
    ...props.fields.map((field) => ({ label: field, field, visible: true })),
    { label: 'Date', field: 'datestamp', visible: true },
]);

const widgetProps = computed(() => ({
    title: props.title,
    icon: 'forms',
}));

function formatDate(value) {
    return DateFormatter.format(value, { relative: 'hour' }).toString();
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
    >
        <template #initializing>
            <Widget v-bind="widgetProps"><Icon name="loading" /></Widget>
        </template>
        <template #default="{ items }">
            <Widget v-bind="widgetProps">
                <ui-description v-if="!items.length" class="flex-1 flex items-center justify-center">
                    {{ __('This form is awaiting responses') }}
                </ui-description>
                <div class="px-4 py-3">
                    <table class="w-full [&_td]:p-0.5 [&_td]:text-sm">
                        <TableHead sr-only />
                        <TableBody>
                            <template v-for="field in fields" #[`cell-${field}`]="{ row: submission }">
                                <a
                                    :href="cp_url(`forms/${form}/submissions/${submission.id}`)"
                                    class="line-clamp-1 overflow-hidden text-ellipsis"
                                >
                                    {{ submission[field] }}
                                </a>
                            </template>
                            <template #cell-datestamp="{ row: submission }">
                                <div
                                    class="text-end font-mono text-xs whitespace-nowrap text-gray-500 antialiased"
                                    v-html="formatDate(submission.datestamp)"
                                />
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
