<script setup>
import { Pagination } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';

const { meta, perPage, setPerPage, setCurrentPage, showPaginationTotals, showPaginationPageLinks } =
    injectListingContext();
</script>

<template>
    <Pagination
        :per-page="perPage"
        :resource-meta="meta"
        :show-totals="showPaginationTotals"
        :show-page-links="showPaginationPageLinks"
        @page-selected="setCurrentPage"
        @per-page-changed="setPerPage"
    />
</template>
