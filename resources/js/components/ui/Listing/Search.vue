<script setup>
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { Input } from '@statamic/ui';
import debounce from '@statamic/util/debounce.js';
import { useTemplateRef } from 'vue';

const { searchQuery, setSearchQuery, reorderable } = injectListingContext();
const placeholder = 'Search...';
const searchQueryUpdated = debounce((event) => setSearchQuery(event.target.value), 300);

const input = useTemplateRef('input');
const focus = () => input.value.focus();

defineExpose({ focus });
</script>

<template>
    <div class="flex-1 max-w-md">
        <label for="listings-search" class="sr-only">{{ __('Search entries') }}</label>
        <Input
            autofocus
            ref="input"
            icon="magnifying-glass"
            id="listings-search"
            variant="light"
            :placeholder="__(placeholder)"
            :value="searchQuery"
            :disabled="reorderable"
            @input="searchQueryUpdated"
            @keyup.esc="setSearchQuery(null)"
        />
    </div>
</template>
