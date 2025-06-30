<template>
    <Listing
        ref="listing"
        :url="requestUrl"
        :columns="columns"
        :action-url="actionUrl"
        :action-context="{ collection }"
        :sort-column="sortColumn"
        :sort-direction="sortDirection"
        :preferences-prefix="preferencesPrefix"
        :filters="filters"
        :filters-for-reordering="filtersForReordering"
        :reorderable="reordering"
        push-query
        @request-completed="requestComplete"
        @reordered="reordered"
    >
        <template #cell-title="{ row: entry, isColumnVisible }">
            <a class="title-index-field" :href="entry.edit_url" @click.stop>
                <StatusIndicator v-if="!isColumnVisible('status')" :status="entry.status" />
                <span v-text="entry.title" />
            </a>
        </template>
        <template #cell-status="{ row: entry }">
            <StatusIndicator :status="entry.status" show-label :show-dot="false" />
        </template>
        <template #prepended-row-actions="{ row: entry }">
            <DropdownItem
                :text="__('Visit URL')"
                :href="entry.permalink"
                icon="eye"
                v-if="entry.viewable && entry.permalink"
            />
            <DropdownItem :text="__('Edit')" :href="entry.edit_url" icon="edit" v-if="entry.editable" />
        </template>
    </Listing>
</template>

<script>
import { StatusIndicator, DropdownItem, Listing } from '@statamic/ui';

export default {
    emits: ['reordered', 'site-changed'],

    components: {
        StatusIndicator,
        Listing,
        DropdownItem,
    },

    props: {
        collection: String,
        reordering: Boolean,
        reorderUrl: String,
        actionUrl: String,
        sortColumn: String,
        sortDirection: String,
        columns: Array,
        filters: Array,
        site: String,
    },

    data() {
        return {
            preferencesPrefix: `collections.${this.collection}`,
            requestUrl: cp_url(`collections/${this.collection}/entries`),
            currentSite: this.site,
            initialSite: this.site,
            items: null,
            page: null,
            perPage: null,
        };
    },

    watch: {
        site(site) {
            this.currentSite = site;
        },

        currentSite(site) {
            this.setSiteFilter(site);
            this.$emit('site-changed', site);
        },
    },

    methods: {
        requestComplete({ items, parameters, activeFilters }) {
            this.items = items;
            this.page = parameters.page;
            this.perPage = parameters.perPage;
            this.currentSite = activeFilters.site ? activeFilters.site.site : null;
        },

        reordered(items) {
            this.items = items;
        },

        setSiteFilter(site) {
            this.$refs.listing.setFilter('site', site ? { site } : null);
        },

        filtersForReordering() {
            return {
                site: {
                    site: this.currentSite || this.initialSite,
                },
            };
        },

        saveOrder() {
            const payload = {
                ids: this.items.map((item) => item.id),
                page: this.page,
                perPage: this.perPage,
                site: this.currentSite,
            };

            this.$axios
                .post(this.reorderUrl, payload)
                .then((response) => {
                    this.$emit('reordered');
                    this.$toast.success(__('Entries successfully reordered'));
                })
                .catch((e) => {
                    this.$toast.error(__('Something went wrong'));
                });
        },
    },
};
</script>
