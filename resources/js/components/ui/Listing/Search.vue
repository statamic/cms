<script setup>
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { Input } from '@statamic/ui';
import debounce from '@statamic/util/debounce.js';

const { searchQuery, setSearchQuery, reorderable } = injectListingContext();
const placeholder = 'Search...';
const searchQueryUpdated = debounce((event) => setSearchQuery(event.target.value), 300);
</script>

<template>
    <div class="min-w-64 lg:w-1/3">
        <label for="listings-search" class="sr-only">{{ __('Search entries') }}</label>
        <Input
            autofocus
            ref="input"
            icon="magnifying-glass"
            id="listings-search"
            :placeholder="__(placeholder)"
            :value="searchQuery"
            :disabled="reorderable"
            @input="searchQueryUpdated"
            @keyup.esc="setSearchQuery(null)"
        />
    </div>
</template>
