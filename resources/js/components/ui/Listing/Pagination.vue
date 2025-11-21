<script setup>
import { Pagination } from '@ui';
import { injectListingContext } from './listing-context.js';

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
        @page-selected="setCurrentPage"
        @per-page-changed="setPerPage"
    />
</template>
