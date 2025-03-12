<template>
    <div class="h-full">
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
                    <template #cell-datestamp="{ row: submission }">
                        <div class="flex items-center">
                            <a :href="submission.url">{{ formatDate(submission.datestamp) }}</a>
                        </div>
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
            {{ __('This form is awaiting responses') }}
        </p>
    </div>
</template>

<script>
import Listing from '../Listing.vue';

export default {
    mixins: [Listing],

    props: {
        form: String,
        additionalColumns: Array,
    },

    data() {
        return {
            cols: [{ label: 'Date', field: 'datestamp', visible: true }, ...this.additionalColumns],
            listingKey: 'forms',
            requestUrl: cp_url(`forms/${this.form}/submissions`),
        };
    },

    methods: {
        formatDate(value) {
            let date = new Date(value);

            return (
                date.toLocaleDateString(navigator.language, { year: 'numeric', month: 'numeric', day: 'numeric' }) +
                ' ' +
                date.toLocaleTimeString(navigator.language, { hour: 'numeric', minute: 'numeric' })
            );
        }
    },
};
</script>
