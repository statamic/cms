<script>
import Listing from '../Listing.vue';
import DateFormatter from '@statamic/components/DateFormatter.js';
import { Widget } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Widget,
    },

    props: {
        form: { type: String, required: true },
        fields: { type: Array, default: () => [] },
        title: { type: String },
    },

    data() {
        return {
            cols: [
                ...this.fields.map((field) => ({ label: field, field, visible: true })),
                { label: 'Date', field: 'date', visible: true },
            ],
            listingKey: 'submissions',
            requestUrl: cp_url(`forms/${this.form}/submissions`),
        };
    },

    methods: {
        formatDate(value) {
            return DateFormatter.format(value, { relative: 'hour' }).toString();
        },
    },
};
</script>

<template>
    <Widget :title="title" icon="forms">
        <data-list v-if="!initializing && items.length" :rows="items" :columns="cols" :sort="false" class="w-full">
            <div v-if="initializing" class="loading">
                <loading-graphic />
            </div>

            <data-list-table
                v-else
                :loading="loading"
                unstyled
                class="[&_td]:px-0.5 [&_td]:py-0.75 [&_td]:text-sm [&_thead]:hidden"
            >
                <template v-for="field in fields" #[`cell-${field}`]="{ row: submission }">
                    <a
                        :href="cp_url(`forms/${form}/submissions/${submission.id}`)"
                        class="line-clamp-1 overflow-hidden text-ellipsis"
                    >
                        {{ submission[field] }}
                    </a>
                </template>
                <template #cell-date="{ row: submission }">
                    <div
                        class="text-end font-mono text-xs whitespace-nowrap text-gray-500 antialiased"
                        v-html="formatDate(submission.datestamp)"
                    />
                </template>
            </data-list-table>
        </data-list>

        <p v-if="!initializing && !items.length" class="p-3 text-center text-sm text-gray-600">
            {{ __('This form is awaiting responses') }}
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
