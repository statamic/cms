<script setup>
import { injectListingContext } from '../Listing/Listing.vue';
import { Input } from '@ui';
import debounce from '@/util/debounce.js';
import { useId, useTemplateRef } from 'vue';

defineProps({
    /** Accessible label for the search field. Already translated, e.g. `:label="__('Search assets')"`. */
    label: { type: String, default: null },
});

const id = useId();
const { activeFilterBadgeCount, searchQuery, setSearchQuery, reorderable } = injectListingContext();
const searchQueryUpdated = debounce((value) => setSearchQuery(value), 300);

const input = useTemplateRef('input');
const focus = () => input.value.focus();

defineExpose({ focus });
</script>

<template>
    <div class="flex-1 max-w-sm" :class="{ 'max-w-60!': activeFilterBadgeCount > 2 }">
        <label :for="id" class="sr-only">{{ label || __('Search') }}</label>
        <Input
            :focus="true"
            ref="input"
            icon="magnifying-glass"
            :id="id"
            variant="light"
            clearable
            :placeholder="__('Search...')"
            :model-value="searchQuery"
            :disabled="reorderable"
            @update:model-value="searchQueryUpdated"
            @keyup.esc="setSearchQuery(null)"
        />
    </div>
</template>
