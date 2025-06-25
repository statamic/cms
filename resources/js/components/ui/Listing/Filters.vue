<script setup>
import { Badge, Button, Modal, ModalClose } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed } from 'vue';
import FieldFilter from '@statamic/components/data-list/FieldFilter.vue';
import DataListFilter from '@statamic/components/data-list/Filter.vue';

const { filters, activeFilters, activeFilterBadges, setFilter, reorderable } = injectListingContext();

const badgeCount = computed(() => {
    let count = Object.keys(activeFilterBadges.value).length;

    if (activeFilterBadges.value.hasOwnProperty('fields')) {
        count = count + Object.keys(activeFilterBadges.value.fields).length - 1;
    }

    return count;
});

const fieldFilter = computed(() => filters.value.find((filter) => filter.handle === 'fields'));
const standardFilters = computed(() => filters.value.filter((filter) => filter.handle !== 'fields'));
const fieldFilterBadges = computed(() => activeFilterBadges.value.fields || {});

const standardBadges = computed(() => {
    const { fields, ...badges } = activeFilterBadges.value;
    return badges;
});

function removeFieldFilter(handle) {
    const fields = { ...activeFilters.value.fields };
    delete fields[handle];
    setFilter('fields', fields);
}
</script>

<template>
    <div class="flex flex-1 items-center gap-3 overflow-x-auto">
        <Modal :title="__('Apply Filters')">
            <template #trigger>
                <Button icon="filter" class="relative" :disabled="reorderable">
                    {{ __('Filter') }}
                    <Badge
                        v-if="badgeCount"
                        :text="badgeCount"
                        pill
                        variant="filled"
                        class="absolute -top-1.5 -right-1.5"
                    />
                </Button>
            </template>
            <div class="space-y-6 py-3">
                <div class="bg-yellow p-2">
                    This is ugly and broken because we haven't decided on a new design yet. It's shoehorning the
                    existing components.
                </div>
                <FieldFilter
                    ref="fieldFilter"
                    :config="fieldFilter"
                    :values="activeFilters.fields || {}"
                    :badges="fieldFilterBadges"
                    @changed="setFilter('fields', $event)"
                />

                <data-list-filter
                    v-for="filter in standardFilters"
                    :key="filter.handle"
                    :filter="filter"
                    :values="activeFilters[filter.handle]"
                    @changed="setFilter(filter.handle, $event)"
                />
            </div>
            <template #footer>
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <ModalClose>
                        <Button text="Cancel" variant="ghost" />
                    </ModalClose>
                    <Button text="Update Filter" variant="primary" />
                </div>
            </template>
        </Modal>

        <Button
            v-for="(badge, handle) in fieldFilterBadges"
            variant="filled"
            icon-append="x"
            :text="badge"
            @click="removeFieldFilter(handle)"
        />
        <Button
            v-for="(badge, handle) in standardBadges"
            variant="filled"
            icon-append="x"
            :text="badge"
            @click="setFilter(handle, null)"
        />
    </div>
</template>
