<template>
    <Listing
        ref="listing"
        :url="requestUrl"
        :columns="columns"
        :action-url="actionUrl"
        :action-context="{ taxonomy }"
        :sort-column="sortColumn"
        :sort-direction="sortDirection"
        :preferences-prefix="preferencesPrefix"
        :filters="filters"
        push-query
        @request-completed="requestComplete"
    >
        <template #cell-title="{ row: term }">
            <div class="flex items-center">
                <a :href="term.edit_url">{{ term.title }}</a>
            </div>
        </template>
        <template #cell-slug="{ row: term }">
            <span class="text-2xs font-mono">{{ term.slug }}</span>
        </template>
        <template #prepended-row-actions="{ row: term }">
            <DropdownItem :text="__('Visit URL')" :href="term.permalink" target="_blank" icon="eye" />
            <DropdownItem :text="__('Edit')" :href="term.edit_url" icon="edit" />
        </template>
    </Listing>
</template>

<script>
import { DropdownItem, Listing } from '@/components/ui';

export default {
    components: {
        Listing,
        DropdownItem,
    },

    props: {
        taxonomy: String,
        actionUrl: String,
        sortColumn: String,
        sortDirection: String,
        columns: Array,
        filters: Array,
    },

    data() {
        return {
            preferencesPrefix: `taxonomies.${this.taxonomy}`,
            requestUrl: cp_url(`taxonomies/${this.taxonomy}/terms`),
        };
    },

    methods: {
        requestComplete({ items, parameters }) {
            this.items = items;
        },
    },
};
</script>
