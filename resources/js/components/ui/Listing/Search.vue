<script setup>
import { injectListingContext } from '../Listing/Listing.vue';
import { Input } from '@ui';
import debounce from '@/util/debounce.js';
import { useTemplateRef } from 'vue';

const { activeFilterBadgeCount, searchQuery, setSearchQuery, reorderable } = injectListingContext();
const searchQueryUpdated = debounce((value) => setSearchQuery(value), 300);

const input = useTemplateRef('input');
const focus = () => input.value.focus();

defineExpose({ focus });
</script>

<template>
    <div class="flex-1 max-w-sm" :class="{ 'max-w-60!': activeFilterBadgeCount > 2 }">
        <label for="listings-search" class="sr-only">{{ __('Search entries') }}</label>
        <Input
            autofocus
            ref="input"
            icon="magnifying-glass"
            id="listings-search"
            variant="light"
            :clearable="true"
            :placeholder="__('Search...')"
            :model-value="searchQuery"
            :disabled="reorderable"
            @update:model-value="searchQueryUpdated"
            @keyup.esc="setSearchQuery(null)"
        />
    </div>
</template>
