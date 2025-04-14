<template>
    <ui-card inset>
        <header class="flex items-center justify-between px-4 py-3 border-b border-ui-border">
            <div class="flex items-center gap-3">
                <ui-icon name="collections" class="size-5 text-gray-500" />
                <span v-text="title" />
            </div>
            <slot name="actions"></slot>
        </header>
        <div class="p-4">
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
                class="flex h-full flex-col justify-between"
            >
                <div>
                    <data-list-table :loading="loading">
                        <template #cell-title="{ row: entry }">
                            <div class="flex items-center">
                                <span
                                    class="little-dot ltr:mr-2 rtl:ml-2"
                                    v-tooltip="getStatusLabel(entry)"
                                    :class="getStatusClass(entry)"
                                    v-if="!columnShowing('status')"
                                />
                                <a :href="entry.edit_url">{{ entry.title }}</a>
                            </div>
                        </template>
                        <template #cell-status="{ row: entry }">
                            <div
                                class="status-index-field select-none"
                                v-tooltip="getStatusTooltip(entry)"
                                :class="`status-${entry.status}`"
                                v-text="getStatusLabel(entry)"
                            />
                        </template>
                    </data-list-table>
                    <data-list-pagination
                        v-if="meta.last_page != 1"
                        class="rounded-b-lg border-t bg-gray-200 py-2 text-sm dark:border-gray-900 dark:bg-dark-650"
                        :resource-meta="meta"
                        @page-selected="selectPage"
                        :scroll-to-top="false"
                        :show-page-links="false"
                    />
                </div>
            </data-list>

            <p v-else-if="!initializing && !items.length" class="p-4 pt-2 text-sm text-gray-600">
                {{ __('There are no entries in this collection') }}
            </p>
        </div>
    </ui-card>
</template>

<script>
import Listing from '../Listing.vue';

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
        };
    },

    methods: {
        getStatusClass(entry) {
            // TODO: Replace with `entry.status` (will need to pass down)
            if (entry.published && entry.private) {
                return 'bg-transparent border border-gray-600';
            } else if (entry.published) {
                return 'bg-green-600';
            } else {
                return 'bg-gray-400 dark:bg-dark-200';
            }
        },

        getStatusLabel(entry) {
            if (entry.status === 'published') {
                return __('Published');
            } else if (entry.status === 'scheduled') {
                return __('Scheduled');
            } else if (entry.status === 'expired') {
                return __('Expired');
            } else if (entry.status === 'draft') {
                return __('Draft');
            }
        },

        getStatusTooltip(entry) {
            if (entry.status === 'published') {
                return entry.collection.dated ? __('messages.status_published_with_date', { date: entry.date }) : null; // The label is sufficient.
            } else if (entry.status === 'scheduled') {
                return __('messages.status_scheduled_with_date', { date: entry.date });
            } else if (entry.status === 'expired') {
                return __('messages.status_expired_with_date', { date: entry.date });
            } else if (entry.status === 'draft') {
                return null; // The label is sufficient.
            }
        },

        columnShowing(column) {
            return this.cols.find((c) => c.field === column);
        },
    },
};
</script>
