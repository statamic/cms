<script setup>
import { Pagination } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';

const { meta, perPage, setPerPage, setCurrentPage } = injectListingContext();
</script>

<template>
    <Pagination
        :per-page="perPage"
        :resource-meta="meta"
        show-totals
        @page-selected="setCurrentPage"
        @per-page-changed="setPerPage"
    />
</template>
