<script setup>
import { Pagination } from '@ui';
import { injectListingContext } from '../Listing/Listing.vue';

const {
    meta,
    perPage,
    setPerPage,
    setCurrentPage,
    showPaginationTotals,
    showPaginationPageLinks,
    showPaginationPerPageSelector,
} = injectListingContext();
</script>

<template>
    <Pagination
        :per-page="perPage"
        :resource-meta="meta"
        :show-totals="showPaginationTotals"
        :show-page-links="showPaginationPageLinks"
        :show-per-page-selector="showPaginationPerPageSelector"
        :per-page-selector-options="$config.get('paginationSizeOptions')"
        @page-selected="setCurrentPage"
        @per-page-changed="setPerPage"
    />
</template>
