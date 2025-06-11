<script setup>
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { Input } from '@statamic/ui';
import debounce from '@statamic/util/debounce.js';

const { searchQuery, setSearchQuery } = injectListingContext();
const placeholder = 'Search...';
const searchQueryUpdated = debounce((event) => setSearchQuery(event.target.value), 300);
</script>

<template>
    <div class="min-w-64 lg:w-1/3">
        <Input
            autofocus
            ref="input"
            icon="magnifying-glass"
            :placeholder="__(placeholder)"
            :value="searchQuery"
            @input="searchQueryUpdated"
            @keyup.esc="setSearchQuery(null)"
        />
    </div>
</template>
